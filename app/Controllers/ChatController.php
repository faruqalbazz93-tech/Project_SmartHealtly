<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Auth;
use App\Core\RateLimiter;
use App\Services\ChatService;
use Throwable;

class ChatController extends Controller
{
    private ChatService $chatService;

    public function __construct()
    {
        Session::start();
        $this->chatService = new ChatService();
    }

    /**
     * Send message endpoint
     * POST /api/chat/send
     */
    public function send(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // 1. Verify CSRF Token
        $csrfToken = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Session::validateCsrfToken($csrfToken)) {
            $this->json([
                'success' => false,
                'message' => 'Token keamanan (CSRF) tidak valid atau sesi telah kedaluwarsa. Silakan muat ulang halaman.'
            ], 403);
            return;
        }

        // 2. Rate Limiting Check (Max 20 requests per 2 minutes)
        $rateKey = Auth::check() 
            ? 'chat_user_' . Auth::id() 
            : 'chat_ip_' . md5($_SERVER['REMOTE_ADDR'] ?? 'guest') . '_' . session_id();

        if (RateLimiter::tooManyAttempts($rateKey, 20)) {
            $this->json([
                'success' => false,
                'message' => 'Anda terlalu cepat mengirim pesan. Demi kenyamanan bersama, silakan tunggu 1-2 menit sebelum mengirim pertanyaan kembali.'
            ], 429);
            return;
        }

        RateLimiter::hit($rateKey, 120);

        // 3. Extract and validate message input
        $rawMessage = $this->input('message');
        if ($rawMessage === null) {
            // Check raw JSON body if sent as application/json
            $body = file_get_contents('php://input');
            $data = json_decode($body, true);
            $rawMessage = $data['message'] ?? null;
        }

        $message = trim((string)$rawMessage);

        if ($message === '' || mb_strlen($message) < 2) {
            $this->json([
                'success' => false,
                'message' => 'Pesan tidak boleh kosong (minimal 2 karakter).'
            ], 422);
            return;
        }

        if (mb_strlen($message) > 500) {
            $this->json([
                'success' => false,
                'message' => 'Pesan terlalu panjang (maksimal 500 karakter). Mohon persingkat pertanyaan Anda.'
            ], 422);
            return;
        }

        // 4. Process through ChatService
        try {
            $sessionId = session_id();
            $result = $this->chatService->processMessage($sessionId, $message);
            $this->json($result, 200);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Terjadi kendala saat memproses percakapan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get conversation history
     * GET /api/chat/history
     */
    public function history(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $sessionId = session_id();
            $messages = $this->chatService->getConversationHistory($sessionId, 50);

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
                'message' => 'Gagal memuat riwayat percakapan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear conversation history
     * POST /api/chat/clear
     */
    public function clear(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $csrfToken = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Session::validateCsrfToken($csrfToken)) {
            $this->json([
                'success' => false,
                'message' => 'Token keamanan (CSRF) tidak valid. Silakan muat ulang halaman.'
            ], 403);
            return;
        }

        try {
            $sessionId = session_id();
            $this->chatService->clearConversation($sessionId);

            $this->json([
                'success' => true,
                'message' => 'Riwayat percakapan Anda telah berhasil dihapus.'
            ]);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Gagal menghapus riwayat percakapan: ' . $e->getMessage()
            ], 500);
        }
    }
}
