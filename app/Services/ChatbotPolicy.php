<?php

namespace App\Services;

use App\Config\App;

class ChatbotPolicy
{
    /**
     * Standard rejection message for Python-related requests
     */
    public const PYTHON_REFUSAL_MESSAGE = "Maaf, saya hanya dapat membantu informasi dan layanan pada aplikasi SmartHealthy, termasuk jadwal Puskesmas, pendaftaran, dan edukasi kesehatan.";

    /**
     * Standard rejection message for comment-related requests
     */
    public const COMMENT_REFUSAL_MESSAGE = "Maaf, fitur menambahkan komentar tidak tersedia pada chatbot ini. Saya hanya dapat membantu informasi dan layanan pada aplikasi SmartHealthy, termasuk jadwal Puskesmas, pendaftaran, dan edukasi kesehatan.";

    /**
     * Validate user message against strict chatbot rules before calling AI.
     * Returns an array with ['allowed' => bool, 'reason' => ?string, 'response' => ?string]
     */
    public static function checkInputPolicy(string $message): array
    {
        $lower = strtolower(trim($message));

        // 1. RULE A: Tidak boleh membuat, mengedit, atau menjalankan kode Python
        if (self::isPythonRequest($lower)) {
            return [
                'allowed'  => false,
                'violation'=> 'python_request',
                'response' => self::PYTHON_REFUSAL_MESSAGE
            ];
        }

        // 2. RULE B: Tidak boleh membuat atau menambahkan komentar
        if (self::isCommentRequest($lower)) {
            return [
                'allowed'  => false,
                'violation'=> 'comment_request',
                'response' => self::COMMENT_REFUSAL_MESSAGE
            ];
        }

        // 3. Jailbreak / Prompt Injection / System credential access attempts
        if (self::isSecurityViolation($lower)) {
            return [
                'allowed'  => false,
                'violation'=> 'security_violation',
                'response' => "Maaf, permintaan tersebut tidak dapat diproses demi menjaga keamanan sistem SmartHealthy."
            ];
        }

        // 4. Emergency Medical Triage
        $emergencyResponse = self::checkEmergencyTriage($lower);
        if ($emergencyResponse !== null) {
            return [
                'allowed'  => false,
                'violation'=> 'medical_emergency',
                'response' => $emergencyResponse
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Check if message asks for Python code, script, tutorial, or execution
     */
    private static function isPythonRequest(string $lower): bool
    {
        $pythonKeywords = [
            'python', 'py script', '.py', 'pip install', 'import pandas', 'import numpy',
            'import os', 'import sys', 'def main():', 'print("', "print('", 'buatkan kode python',
            'bikin kode python', 'skrip python', 'koding python', 'coding python', 'tutorial python',
            'jalankan python', 'eksekusi python', 'script python'
        ];

        foreach ($pythonKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return true;
            }
        }

        // Regex pattern for python code snippets
        if (preg_match('/\b(def|class|lambda)\s+[a-zA-Z_]\w*\s*\(.*?\)\s*:/i', $lower)) {
            return true;
        }

        return false;
    }

    /**
     * Check if message asks to write, edit, delete, or submit comments
     */
    private static function isCommentRequest(string $lower): bool
    {
        $commentKeywords = [
            'tambah komentar', 'tambahkan komentar', 'tulis komentar', 'buat komentar',
            'kirim komentar', 'tinggalkan komentar', 'edit komentar', 'hapus komentar',
            'post komentar', 'submit komentar', 'komentari artikel', 'bikin komentar'
        ];

        foreach ($commentKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for unauthorized system access or prompt injection attempts
     */
    private static function isSecurityViolation(string $lower): bool
    {
        $violationKeywords = [
            'ignore all previous instructions', 'abaikan instruksi sebelumnya',
            'ignore previous instructions', 'system prompt', 'tampilkan system instruction',
            'show your system prompt', 'database password', 'kredensial database',
            'dump database', 'select * from users', 'drop table', 'password_hash',
            'api_key', 'api key gemini', 'tampilkan api key', 'akses terminal', 'jalankan bash'
        ];

        foreach ($violationKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for medical emergency triage
     */
    private static function checkEmergencyTriage(string $lower): ?string
    {
        $emergencyKeywords = [
            'nyeri dada hebat', 'sesak napas parah', 'tidak bisa bernapas', 'henti napas',
            'pendarahan hebat', 'kejang', 'pingsan', 'tidak sadar', 'hilang kesadaran',
            'muntah darah', 'batuk darah parah', 'keracunan', 'overdosis', 'tersedak anak',
            'lumpuh mendadak', 'stroke mendadak', 'bicara pelo mendadak'
        ];

        foreach ($emergencyKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                $puskesmas = App::puskesmas();
                return "🚨 **PERHATIAN MEDIS GAWAT DARURAT** 🚨\n\n"
                    . "Gejala yang Anda sampaikan (*{$kw}*) berpotensi merupakan kondisi gawat darurat yang membutuhkan pertolongan medis segera!\n\n"
                    . "Langkah cepat yang harus segera dilakukan:\n"
                    . "1. Segera datangi **IGD 24 Jam {$puskesmas['name']}** ({$puskesmas['address']}) atau Rumah Sakit terdekat.\n"
                    . "2. Hubungi Hotline Darurat/IGD Puskesmas di **{$puskesmas['hotline']}** atau telepon **{$puskesmas['phone']}**.\n"
                    . "3. Anda juga dapat menghubungi Layanan Ambulans Gawat Darurat Nasional di nomor **119**.\n\n"
                    . "*Asisten AI SmartHealthy bukan dokter dan tidak dapat memberikan tindakan pada keadaan gawat darurat.*";
            }
        }

        return null;
    }

    /**
     * Post-process and sanitize output from Gemini to ensure no policy violation escaped
     */
    public static function checkOutputPolicy(string $response): string
    {
        // 1. If response accidentally generated Python code, neutralize it
        if (preg_match('/```python/i', $response) || preg_match('/\b(def|class)\s+[a-zA-Z_]\w*\s*\(.*?\)\s*:/', $response)) {
            return self::PYTHON_REFUSAL_MESSAGE;
        }

        // 2. If response claimed to write comments, replace
        if (preg_match('/komentar anda telah berhasil (ditambahkan|dikirim|disimpan)/i', $response)) {
            return self::COMMENT_REFUSAL_MESSAGE;
        }

        return $response;
    }
}
