<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Env;

class VerificationToken extends Model
{
    protected string $table = 'user_verifications';

    /**
     * Generate and store a secure Email Verification Token (24 hours)
     */
    public function createEmailToken(int $userId): string
    {
        // Invalidate old unverified email tokens for this user
        $this->execute(
            "DELETE FROM `{$this->table}` WHERE `user_id` = :uid AND `type` = 'email' AND `verified_at` IS NULL",
            ['uid' => $userId]
        );

        $token = bin2hex(random_bytes(32)); // 64 hex characters
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->create([
            'user_id'       => $userId,
            'type'          => 'email',
            'token_or_code' => $token,
            'attempts'      => 0,
            'max_attempts'  => 10,
            'expires_at'    => $expiresAt,
            'verified_at'   => null,
        ]);

        return $token;
    }

    /**
     * Generate and store 6-digit WhatsApp OTP (5 minutes, max 3 attempts)
     */
    public function createWhatsAppOtp(int $userId): string
    {
        // Invalidate old unverified OTPs for this user
        $this->execute(
            "DELETE FROM `{$this->table}` WHERE `user_id` = :uid AND `type` = 'whatsapp_otp' AND `verified_at` IS NULL",
            ['uid' => $userId]
        );

        $otp = (string) random_int(100000, 999999);
        $expiryMinutes = (int) Env::get('OTP_EXPIRY_MINUTES', 5);
        $maxAttempts = (int) Env::get('OTP_MAX_ATTEMPTS', 3);
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiryMinutes} minutes"));

        $this->create([
            'user_id'       => $userId,
            'type'          => 'whatsapp_otp',
            'token_or_code' => $otp,
            'attempts'      => 0,
            'max_attempts'  => $maxAttempts,
            'expires_at'    => $expiresAt,
            'verified_at'   => null,
        ]);

        return $otp;
    }

    /**
     * Verify email token
     */
    public function verifyEmailToken(string $token): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `token_or_code` = :token 
                  AND `type` = 'email' 
                  AND `verified_at` IS NULL 
                  AND `expires_at` > NOW() 
                LIMIT 1";

        $record = $this->queryOne($sql, ['token' => $token]);
        if (!$record) {
            return null;
        }

        // Mark as verified
        $this->update($record['id'], ['verified_at' => date('Y-m-d H:i:s')]);

        return $record;
    }

    /**
     * Verify WhatsApp OTP code
     */
    public function verifyWhatsAppOtp(int $userId, string $inputOtp): array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `user_id` = :uid 
                  AND `type` = 'whatsapp_otp' 
                  AND `verified_at` IS NULL 
                ORDER BY `id` DESC LIMIT 1";

        $record = $this->queryOne($sql, ['uid' => $userId]);

        if (!$record) {
            return [
                'status'  => false,
                'message' => 'Tidak ditemukan kode OTP aktif. Silakan kirim ulang kode OTP.',
                'code'    => 'NOT_FOUND'
            ];
        }

        // Check if expired
        if (strtotime($record['expires_at']) < time()) {
            return [
                'status'  => false,
                'message' => 'Kode OTP telah kedaluwarsa. Silakan minta kode OTP baru.',
                'code'    => 'EXPIRED'
            ];
        }

        // Check if max attempts exceeded
        if ((int)$record['attempts'] >= (int)$record['max_attempts']) {
            return [
                'status'  => false,
                'message' => 'Batas percobaan memasukkan OTP telah habis (maksimal ' . $record['max_attempts'] . ' kali). Silakan minta kode baru.',
                'code'    => 'MAX_ATTEMPTS'
            ];
        }

        // Increment attempt
        $newAttempts = (int)$record['attempts'] + 1;
        $this->update($record['id'], ['attempts' => $newAttempts]);

        // Check matching OTP
        if (hash_equals(trim($record['token_or_code']), trim($inputOtp))) {
            // Success
            $this->update($record['id'], ['verified_at' => date('Y-m-d H:i:s')]);
            return [
                'status'  => true,
                'message' => 'Nomor WhatsApp berhasil diverifikasi!',
                'code'    => 'SUCCESS'
            ];
        }

        $remaining = max(0, (int)$record['max_attempts'] - $newAttempts);
        return [
            'status'         => false,
            'message'        => "Kode OTP tidak cocok. Sisa kesempatan: {$remaining} kali.",
            'attempts_left'  => $remaining,
            'code'           => 'MISMATCH'
        ];
    }

    /**
     * Get active OTP record for user
     */
    public function getLatestActiveOtp(int $userId): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `user_id` = :uid 
                  AND `type` = 'whatsapp_otp' 
                  AND `verified_at` IS NULL 
                  AND `expires_at` > NOW() 
                ORDER BY `id` DESC LIMIT 1";

        return $this->queryOne($sql, ['uid' => $userId]);
    }
}
