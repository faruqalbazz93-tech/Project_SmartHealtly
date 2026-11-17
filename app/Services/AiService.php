<?php

namespace App\Services;

use App\Config\App;
use App\Models\AiLog;
use App\Core\Auth;
use Exception;

class AiService
{
    private string $apiKey;
    private string $provider;
    private string $model;

    public function __construct()
    {
        $this->apiKey = App::env('GEMINI_API_KEY', '') ?: App::env('AI_API_KEY', '');
        $this->provider = App::env('AI_PROVIDER', 'gemini');
        $this->model = App::env('GEMINI_MODEL', '') ?: App::env('AI_MODEL', 'gemini-3.5-flash');
    }

    /**
     * Check if a real AI provider is configured with valid API key
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && $this->provider !== 'mock';
    }

    /**
     * Get current provider name
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Send chat prompt to AI provider
     *
     * @param string $systemInstruction Base instructions and grounding data
     * @param array $conversationHistory Previous messages [['role' => 'user'|'assistant', 'message' => '...']]
     * @param string $userMessage Latest user message
     * @return string
     */
    public function chat(string $systemInstruction, array $conversationHistory, string $userMessage): string
    {
        $startTime = microtime(true);

        if (!$this->isConfigured()) {
            $response = $this->fallbackMockResponse($userMessage);
            $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);
            $this->logInteraction('chatbot', $userMessage, $response, 0, $responseTimeMs);
            return $response;
        }

        try {
            if ($this->provider === 'gemini') {
                $responseStr = $this->callGeminiChat($systemInstruction, $conversationHistory, $userMessage);
            } else {
                throw new Exception("Provider AI '{$this->provider}' belum didukung.");
            }
        } catch (Exception $e) {
            $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);
            $this->logInteraction('chatbot', $userMessage, 'ERROR: ' . $e->getMessage(), 0, $responseTimeMs);
            throw $e;
        }

        $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);
        $this->logInteraction('chatbot', $userMessage, $responseStr, 0, $responseTimeMs);

        return $responseStr;
    }

    /**
     * Multi-turn chat call to Google Gemini API
     */
    private function callGeminiChat(string $systemInstruction, array $conversationHistory, string $userMessage): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key=" . urlencode($this->apiKey);

        $contents = [];

        // Add recent conversation history (max 8 turns to avoid token exhaustion)
        $recentHistory = array_slice($conversationHistory, -8);
        foreach ($recentHistory as $msg) {
            $role = ($msg['role'] === 'assistant') ? 'model' : 'user';
            $text = trim((string)($msg['message'] ?? ''));
            if ($text !== '') {
                $contents[] = [
                    'role' => $role,
                    'parts' => [
                        ['text' => $text]
                    ]
                ];
            }
        }

        // Add current user prompt
        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $userMessage]
            ]
        ];

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 800,
                'topP' => 0.8,
                'thinkingConfig' => [
                    'thinkingBudget' => 0
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            throw new Exception("Koneksi ke AI Provider terputus atau timeout: " . $err);
        }

        $decoded = json_decode((string)$response, true);
        if ($httpCode !== 200) {
            $msg = $decoded['error']['message'] ?? "Kode HTTP {$httpCode}";
            throw new Exception("Layanan AI Gemini melaporkan kesalahan: {$msg}");
        }

        if (!empty($decoded['candidates'][0]['content']['parts'])) {
            $fullText = '';
            foreach ($decoded['candidates'][0]['content']['parts'] as $part) {
                if (!empty($part['text'])) {
                    $fullText .= $part['text'];
                }
            }
            if (trim($fullText) !== '') {
                return trim($fullText);
            }
        }

        if (isset($decoded['candidates'][0]['finishReason']) && $decoded['candidates'][0]['finishReason'] === 'SAFETY') {
            return "Maaf, pertanyaan Anda menyentuh batasan keamanan konten medis otomatis. Silakan konsultasikan langsung dengan petugas kesehatan kami.";
        }

        throw new Exception("Format respons dari penyedia AI tidak dapat diproses.");
    }

    /**
     * Fallback mock response for development when AI API Key is not set
     */
    public function fallbackMockResponse(string $userMessage): string
    {
        return "[Simulasi AI Development - API Key Belum Disetel]\n\n"
            . "Halo! SmartHealthy AI Assistant sedang berjalan dalam mode pengujian lokal. "
            . "Untuk mengaktifkan kecerdasan buatan penuh, silakan masukkan AI_API_KEY (Google Gemini) pada file .env.\n\n"
            . "Meskipun dalam mode simulasi, sistem SmartHealthy tetap dapat melayani pendaftaran antrean gratis, pengecekan nomor tiket secara langsung, dan kuis edukasi kesehatan.";
    }

    /**
     * Generate quiz questions using AI
     */
    public function generateQuestions(string $topic, string $categoryName, int $count, string $difficulty): array
    {
        if (empty($this->apiKey)) {
            throw new Exception("AI API Key is not configured.");
        }

        $systemInstruction = "Anda adalah asisten AI medis untuk Puskesmas. Buatlah soal kuis kesehatan objektif (pilihan ganda) berbahasa Indonesia yang sangat akurat secara medis. Tingkat kesulitan: {$difficulty}. Jangan berikan instruksi pengobatan berisiko. Anda harus mengembalikan JSON array murni tanpa format markdown code blocks.";
        $prompt = "Buatlah {$count} soal kuis tentang '{$topic}' (kategori: {$categoryName}).\n\nFormat output WAJIB JSON array dengan struktur persis seperti ini (tanpa backticks ```json):\n[\n  {\n    \"question_text\": \"Teks pertanyaan\",\n    \"options\": [\n      {\"text\": \"Pilihan 1\", \"is_correct\": true},\n      {\"text\": \"Pilihan 2\", \"is_correct\": false},\n      {\"text\": \"Pilihan 3\", \"is_correct\": false},\n      {\"text\": \"Pilihan 4\", \"is_correct\": false}\n    ],\n    \"explanation\": \"Penjelasan medis singkat mengapa jawaban tersebut benar.\"\n  }\n]";

        $startTime = microtime(true);
        
        try {
            if ($this->provider === 'gemini') {
                $responseStr = $this->callGemini($systemInstruction, $prompt);
            } else {
                throw new Exception("Provider AI {$this->provider} belum didukung.");
            }
        } catch (Exception $e) {
            $this->logInteraction('generate_questions', $prompt, 'ERROR: ' . $e->getMessage(), 0, (int)((microtime(true) - $startTime) * 1000));
            throw $e;
        }

        $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);

        // Clean up markdown block if AI still outputs it
        $responseStr = preg_replace('/```json/i', '', $responseStr);
        $responseStr = preg_replace('/```/i', '', $responseStr);
        $responseStr = trim($responseStr);

        $parsed = json_decode($responseStr, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($parsed)) {
            $this->logInteraction('generate_questions', $prompt, $responseStr, 0, $responseTimeMs);
            throw new Exception("Format output AI tidak valid (Bukan JSON). " . json_last_error_msg());
        }

        // Basic schema validation
        foreach ($parsed as $idx => $q) {
            if (!isset($q['question_text']) || !isset($q['options']) || !is_array($q['options']) || count($q['options']) !== 4) {
                throw new Exception("Format output AI tidak valid: Struktur opsi salah pada indeks $idx.");
            }
            $hasCorrect = false;
            foreach ($q['options'] as $opt) {
                if (isset($opt['is_correct']) && $opt['is_correct'] === true) {
                    $hasCorrect = true;
                }
            }
            if (!$hasCorrect) {
                throw new Exception("Format output AI tidak valid: Tidak ada jawaban benar pada indeks $idx.");
            }
        }

        $this->logInteraction('generate_questions', $prompt, $responseStr, 0, $responseTimeMs);

        return $parsed;
    }

    /**
     * Explain a quiz answer
     */
    public function explainAnswer(string $question, string $userAnswer, string $correctAnswer): string
    {
        if (empty($this->apiKey)) {
            return "Mohon maaf, layanan AI saat ini sedang tidak tersedia (API Key belum diatur).";
        }

        $systemInstruction = "Anda adalah asisten AI edukasi Puskesmas. Berikan penjelasan singkat, edukatif, berbahasa Indonesia ramah. \n\nATURAN MUTLAK:\n1. DILARANG MELAKUKAN DIAGNOSIS MEDIS.\n2. DILARANG MEMBERIKAN INSTRUKSI PENGOBATAN.\n3. INGATKAN BAHWA ANDA BUKAN DOKTER.\n4. FORMAT OUTPUT TEKS HTML SEDERHANA (<p>, <strong>, <ul>).";
        $prompt = "Tolong jelaskan secara edukatif tentang pertanyaan ini:\nPertanyaan: {$question}\nJawaban User: {$userAnswer}\nJawaban Benar: {$correctAnswer}\n\nJelaskan mengapa jawaban benar adalah {$correctAnswer} dan berikan fakta medis dasar.";

        $startTime = microtime(true);
        
        try {
            if ($this->provider === 'gemini') {
                $responseStr = $this->callGemini($systemInstruction, $prompt);
            } else {
                return "Provider AI tidak didukung.";
            }
        } catch (Exception $e) {
            $this->logInteraction('explain_answer', $prompt, 'ERROR: ' . $e->getMessage(), 0, (int)((microtime(true) - $startTime) * 1000));
            return "Terjadi kesalahan saat menghubungi layanan AI: " . $e->getMessage();
        }

        $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);
        $this->logInteraction('explain_answer', $prompt, $responseStr, 0, $responseTimeMs);

        return $responseStr;
    }

    private function callGemini(string $systemInstruction, string $prompt): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key=" . urlencode($this->apiKey);

        $payload = [
            "system_instruction" => [
                "parts" => [
                    ["text" => $systemInstruction]
                ]
            ],
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.4,
                "thinkingConfig" => [
                    "thinkingBudget" => 0
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            throw new Exception("cURL Error: " . $err);
        }

        $decoded = json_decode((string)$response, true);
        if ($httpCode !== 200) {
            $msg = $decoded['error']['message'] ?? 'Unknown Error';
            throw new Exception("Gemini API Error ($httpCode): $msg");
        }

        if (!empty($decoded['candidates'][0]['content']['parts'])) {
            $fullText = '';
            foreach ($decoded['candidates'][0]['content']['parts'] as $part) {
                if (!empty($part['text'])) {
                    $fullText .= $part['text'];
                }
            }
            if (trim($fullText) !== '') {
                return trim($fullText);
            }
        }

        throw new Exception("Invalid response structure from Gemini.");
    }

    private function logInteraction(string $featureType, string $prompt, string $response, int $tokensUsed, int $responseTimeMs): void
    {
        try {
            $logModel = new AiLog();
            $userId = Auth::id() ?: null;

            $logModel->create([
                'user_id' => $userId,
                'feature_type' => $featureType,
                'prompt_payload' => $prompt,
                'response_payload' => $response,
                'tokens_used' => $tokensUsed,
                'response_time_ms' => $responseTimeMs
            ]);
        } catch (\Throwable $e) {
            // Non-blocking logging failure
        }
    }
}
