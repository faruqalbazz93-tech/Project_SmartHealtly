<?php

namespace App\Services;

use App\Core\Env;
use App\Core\Session;
use App\Config\App;

class MailService
{
    /**
     * Send email verification link
     */
    public static function sendVerificationEmail(string $recipientEmail, string $recipientName, string $token): array
    {
        $verifyUrl = App::baseUrl("/verifikasi/email/{$token}");
        $appName = App::name();
        $puskesmas = App::puskesmas();

        $subject = "Verifikasi Alamat Email Akun {$appName}";
        
        $body = "Halo {$recipientName},\n\n";
        $body .= "Terima kasih telah mendaftar di sistem pendaftaran layanan kesehatan {$appName} ({$puskesmas['name']}).\n\n";
        $body .= "Silakan klik tautan berikut untuk memverifikasi alamat email Anda:\n";
        $body .= "{$verifyUrl}\n\n";
        $body .= "Tautan ini berlaku selama 24 jam.\n\n";
        $body .= "Salam sehat,\nTim {$puskesmas['name']}";

        return self::send($recipientEmail, $subject, $body, [
            'type'       => 'email_verification',
            'verify_url' => $verifyUrl,
            'token'      => $token,
        ]);
    }

    /**
     * Send password reset email
     */
    public static function sendPasswordResetEmail(string $recipientEmail, string $recipientName, string $token): array
    {
        $resetUrl = App::baseUrl("/reset-password/{$token}");
        $appName = App::name();
        $puskesmas = App::puskesmas();

        $subject = "Permintaan Reset Kata Sandi Akun {$appName}";
        
        $body = "Halo {$recipientName},\n\n";
        $body .= "Kami menerima permintaan pengaturan ulang kata sandi untuk akun {$appName} Anda.\n\n";
        $body .= "Silakan klik tautan berikut untuk membuat kata sandi baru:\n";
        $body .= "{$resetUrl}\n\n";
        $body .= "Tautan ini berlaku selama 60 menit. Jika Anda tidak merasa meminta reset kata sandi, abaikan email ini.\n\n";
        $body .= "Salam sehat,\nTim {$puskesmas['name']}";

        return self::send($recipientEmail, $subject, $body, [
            'type'      => 'password_reset',
            'reset_url' => $resetUrl,
            'token'     => $token,
        ]);
    }

    /**
     * Send email using configured driver
     */
    public static function send(string $to, string $subject, string $body, array $meta = []): array
    {
        $driver = strtolower((string) Env::get('MAIL_DRIVER', 'mock'));

        Session::start();
        $mockMail = [
            'id'      => uniqid('mail_'),
            'to'      => $to,
            'subject' => $subject,
            'body'    => $body,
            'meta'    => $meta,
            'sent_at' => date('Y-m-d H:i:s'),
        ];

        $_SESSION['mock_mail_last'] = $mockMail;
        $_SESSION['mock_mail_inbox'][] = $mockMail;

        if (count($_SESSION['mock_mail_inbox']) > 10) {
            array_shift($_SESSION['mock_mail_inbox']);
        }

        if ($driver === 'smtp') {
            // Standard mail function or SMTP
            $fromAddress = Env::get('MAIL_FROM_ADDRESS', 'no-reply@smarthealthy.id');
            $fromName = Env::get('MAIL_FROM_NAME', 'SmartHealthy Puskesmas');
            $headers = "From: {$fromName} <{$fromAddress}>\r\n" .
                       "Reply-To: {$fromAddress}\r\n" .
                       "X-Mailer: PHP/" . phpversion();

            @mail($to, $subject, $body, $headers);
        }

        return [
            'status'  => true,
            'driver'  => $driver,
            'message' => 'Email berhasil dikirim (Mock / SMTP Driver)',
            'data'    => $mockMail,
        ];
    }

    /**
     * Get last mock mail for Development testing
     */
    public static function getLastMockMail(): ?array
    {
        Session::start();
        return $_SESSION['mock_mail_last'] ?? null;
    }
}
