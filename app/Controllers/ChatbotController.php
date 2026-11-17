<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Auth;
use App\Core\RateLimiter;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatbotPolicy;
use App\Services\ChatbotDataService;
use App\Services\GeminiService;
use Throwable;

class ChatbotController extends Controller
{
    private ChatConversation $conversationModel;
    private ChatMessage $messageModel;
    private ChatbotDataService $dataService;
    private GeminiService $geminiService;

    public function __construct()
    {
        Session::start();
        $this->conversationModel = new ChatConversation();
        $this->messageModel = new ChatMessage();
        $this->dataService = new ChatbotDataService();
        $this->geminiService = new GeminiService();
    }

    /**
     * Process Chatbot message following strict 7-step flow
     * POST /api/chat/send or /api/chatbot/send
     */
    public function send(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // STEP 1: Pengguna mengirim pesan
        // STEP 2: Backend memvalidasi sesi, CSRF, input, dan batas penggunaan (Rate Limiting)
        $csrfToken = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Session::validateCsrfToken($csrfToken)) {
            $this->json([
                'success' => false,
                'message' => 'Token keamanan (CSRF) tidak valid atau sesi telah kedaluwarsa. Silakan muat ulang halaman.'
            ], 403);
            return;
        }

        // Rate limiting: max 20 pesan per 2 menit
        $rateKey = Auth::check() 
            ? 'chatbot_user_' . Auth::id() 
            : 'chatbot_ip_' . md5($_SERVER['REMOTE_ADDR'] ?? 'guest') . '_' . session_id();

        if (RateLimiter::tooManyAttempts($rateKey, 20)) {
            $this->json([
                'success' => false,
                'message' => 'Anda terlalu cepat mengirim pesan. Demi kenyamanan bersama, silakan tunggu 1-2 menit sebelum mengirim pertanyaan kembali.'
            ], 429);
            return;
        }

        RateLimiter::hit($rateKey, 120);

        // Input extraction & validation
        $rawMessage = $this->input('message');
        if ($rawMessage === null) {
            $body = file_get_contents('php://input');
            $data = json_decode($body, true);
            $rawMessage = $data['message'] ?? null;
        }

        $userMessage = trim((string)$rawMessage);

        if ($userMessage === '' || mb_strlen($userMessage) < 2) {
            $this->json([
                'success' => false,
                'message' => 'Pesan tidak boleh kosong (minimal 2 karakter).'
            ], 422);
            return;
        }

        if (mb_strlen($userMessage) > 500) {
            $this->json([
                'success' => false,
                'message' => 'Pesan terlalu panjang (maksimal 500 karakter). Mohon persingkat pertanyaan Anda.'
            ], 422);
            return;
        }

        // Get or create conversation record
        $sessionId = session_id();
        $conversation = Auth::check() 
            ? $this->conversationModel->getOrCreateForUser((int)Auth::id())
            : $this->conversationModel->getOrCreateForSession($sessionId);
        $convId = (int)$conversation['id'];

        // Save incoming user message
        $this->messageModel->addMessage($convId, 'user', $userMessage);
        $this->conversationModel->touchConversation($convId);

        // STEP 3: Sistem memeriksa apakah permintaan termasuk topik yang diizinkan (ChatbotPolicy)
        $policyCheck = ChatbotPolicy::checkInputPolicy($userMessage);
        if (!$policyCheck['allowed']) {
            $policyReply = $policyCheck['response'] ?? ChatbotPolicy::PYTHON_REFUSAL_MESSAGE;
            $this->messageModel->addMessage($convId, 'assistant', $policyReply);
            $this->conversationModel->touchConversation($convId);

            $this->json([
                'success' => true,
                'role'    => 'assistant',
                'message' => $policyReply,
                'time'    => date('H:i'),
                'is_policy_response' => true
            ], 200);
            return;
        }

        // STEP 4: Backend mengambil data yang sesuai dengan izin pengguna (ChatbotDataService)
        try {
            $groundingData = $this->dataService->buildGroundingContext($userMessage);

            // Load recent history (max 6 messages)
            $history = $this->messageModel->getMessages($convId, 6);

            // STEP 5: Backend mengirim pesan dan konteks minimum yang diperlukan ke Gemini (GeminiService)
            $rawResponse = $this->geminiService->generateResponse($userMessage, $groundingData, $history);

            // STEP 6: Backend memeriksa hasil respons agar tidak melanggar kebijakan
            $finalResponse = ChatbotPolicy::checkOutputPolicy($rawResponse);

            // Save assistant message to database
            $this->messageModel->addMessage($convId, 'assistant', $finalResponse);
            $this->conversationModel->touchConversation($convId);

            // STEP 7: Respons dikirim ke frontend
            $this->json([
                'success'  => true,
                'role'     => 'assistant',
                'message'  => $finalResponse,
                'time'     => date('H:i'),
                'provider' => $this->geminiService->isAvailable() ? 'gemini' : 'mock'
            ], 200);

        } catch (Throwable $e) {
            $grounding = $groundingData ?? '';
            $friendlyResponse = $this->geminiService->generateMockResponse($userMessage, $grounding, false);
            
            $this->messageModel->addMessage($convId, 'assistant', $friendlyResponse);
            $this->conversationModel->touchConversation($convId);

            $this->json([
                'success' => true,
                'role'    => 'assistant',
                'message' => $friendlyResponse,
                'time'    => date('H:i'),
                'is_fallback' => true
            ], 200);
        }
    }

    /**
     * Get conversation history
     * GET /api/chat/history or /api/chatbot/history
     */
    public function history(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $sessionId = session_id();
            $conversation = Auth::check() 
                ? $this->conversationModel->getOrCreateForUser((int)Auth::id())
                : $this->conversationModel->getOrCreateForSession($sessionId);

            $rawMessages = $this->messageModel->getMessages((int)$conversation['id'], 50);

            $messages = array_map(function ($msg) {
                return [
                    'id'             => (int)$msg['id'],
                    'role'           => $msg['role'],
                    'message'        => $msg['message'],
                    'created_at'     => $msg['created_at'],
                    'time_formatted' => date('H:i', strtotime($msg['created_at']))
                ];
            }, $rawMessages);

            $this->json([
                'success'      => true,
                'messages'     => $messages,
                'is_logged_in' => Auth::check(),
                'user_name'    => Auth::check() ? (Auth::user()['name'] ?? 'Pasien') : 'Tamu',
                'csrf_token'   => Session::csrfToken()
            ]);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Gagal memuat riwayat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear conversation history for user or session
     * POST /api/chat/clear or /api/chatbot/clear
     */
    public function clear(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $csrfToken = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Session::validateCsrfToken($csrfToken)) {
            $this->json([
                'success' => false,
                'message' => 'Token keamanan (CSRF) tidak valid.'
            ], 403);
            return;
        }

        try {
            $sessionId = session_id();
            if (Auth::check()) {
                $this->conversationModel->clearUserConversation((int)Auth::id());
            } else {
                $this->conversationModel->clearSessionConversation($sessionId);
            }

            $this->json([
                'success' => true,
                'message' => 'Riwayat percakapan Anda telah berhasil dibersihkan.'
            ]);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Gagal membersihkan riwayat: ' . $e->getMessage()
            ], 500);
        }
    }
}
