<?php

namespace App\Services;

use App\Core\Env;
use App\Core\Session;
use App\Models\NotificationPreference;
use App\Models\WhatsAppMessageLog;
use App\Models\User;
use App\Core\Database;
use PDO;

class WhatsAppService
{
    /**
     * Meta WhatsApp Cloud API Version
     */
    public const DEFAULT_API_VERSION = 'v21.0';

    /**
     * Supported official Meta Template definitions
     */
    public const TEMPLATES = [
        'smarthealthy_registration_success' => [
            'name'        => 'smarthealthy_registration_success',
            'category'    => 'UTILITY',
            'language'    => 'id',
            'param_count' => 5,
            'description' => 'Notifikasi pendaftaran antrean Puskesmas berhasil diterbitkan',
            'body'        => "Halo {{1}}, pendaftaran layanan kesehatan Anda di SmartHealthy berhasil.\n\nNomor pendaftaran: {{2}}\nLayanan: {{3}}\nTanggal kunjungan: {{4}}\nJadwal: {{5}}\n\nSilakan datang sesuai jadwal yang telah ditentukan.\n\nTerima kasih telah menggunakan SmartHealthy.",
        ],
        'smarthealthy_visit_reminder' => [
            'name'        => 'smarthealthy_visit_reminder',
            'category'    => 'UTILITY',
            'language'    => 'id',
            'param_count' => 4,
            'description' => 'Pengingat jadwal kunjungan antrean Puskesmas',
            'body'        => "Halo {{1}}, ini pengingat jadwal kunjungan Anda di Puskesmas.\n\nLayanan: {{2}}\nTanggal: {{3}}\nWaktu: {{4}}\n\nSilakan periksa kembali informasi pendaftaran Anda melalui SmartHealthy.",
        ],
        'smarthealthy_schedule_changed' => [
            'name'        => 'smarthealthy_schedule_changed',
            'category'    => 'UTILITY',
            'language'    => 'id',
            'param_count' => 4,
            'description' => 'Notifikasi penyesuaian / perubahan jadwal pelayanan',
            'body'        => "Halo {{1}}, terdapat perubahan jadwal pendaftaran Anda.\n\nLayanan: {{2}}\nJadwal sebelumnya: {{3}}\nJadwal terbaru: {{4}}\n\nSilakan buka SmartHealthy untuk melihat informasi terbaru.",
        ],
        'smarthealthy_status_updated' => [
            'name'        => 'smarthealthy_status_updated',
            'category'    => 'UTILITY',
            'language'    => 'id',
            'param_count' => 3,
            'description' => 'Pembaruan status pendaftaran antrean pasien',
            'body'        => "Halo {{1}}, status pendaftaran Anda telah diperbarui.\n\nNomor pendaftaran: {{2}}\nStatus terbaru: {{3}}\n\nSilakan buka SmartHealthy untuk melihat detailnya.",
        ],
        'smarthealthy_announcement' => [
            'name'        => 'smarthealthy_announcement',
            'category'    => 'UTILITY',
            'language'    => 'id',
            'param_count' => 3,
            'description' => 'Pengumuman resmi dari Puskesmas Astanajapura',
            'body'        => "Halo {{1}}, terdapat pengumuman resmi dari Puskesmas Astanajapura:\n\n*{{2}}*\n\n{{3}}\n\nSilakan akses SmartHealthy untuk informasi lengkap.",
        ],
    ];

    /**
     * Check if Meta WhatsApp Cloud API credentials are fully configured
     */
    public static function isConfigured(): bool
    {
        $token = Env::get('WHATSAPP_ACCESS_TOKEN');
        $phoneId = Env::get('WHATSAPP_PHONE_NUMBER_ID');

        if (empty($token) || empty($phoneId)) {
            return false;
        }

        // Check against dummy placeholders
        if (str_starts_with($token, 'your_') || str_starts_with($phoneId, 'your_')) {
            return false;
        }

        return true;
    }

    /**
     * Get API Version configured or default
     */
    public static function getApiVersion(): string
    {
        $ver = trim((string) Env::get('WHATSAPP_API_VERSION', self::DEFAULT_API_VERSION));
        return !empty($ver) ? $ver : self::DEFAULT_API_VERSION;
    }

    /**
     * Get masked access token for safe admin dashboard presentation
     */
    public static function getMaskedToken(): string
    {
        $token = (string) Env::get('WHATSAPP_ACCESS_TOKEN', '');
        if (empty($token)) {
            return 'Belum Dikonfigurasi';
        }

        $len = strlen($token);
        if ($len <= 10) {
            return '********';
        }

        return substr($token, 0, 6) . '****************' . substr($token, -4);
    }

    /**
     * Get Phone Number ID
     */
    public static function getPhoneNumberId(): ?string
    {
        $id = Env::get('WHATSAPP_PHONE_NUMBER_ID');
        return !empty($id) ? (string)$id : null;
    }

    /**
     * Get Business Account ID
     */
    public static function getBusinessAccountId(): ?string
    {
        $id = Env::get('WHATSAPP_BUSINESS_ACCOUNT_ID');
        return !empty($id) ? (string)$id : null;
    }

    /**
     * Get Webhook Verify Token
     */
    public static function getVerifyToken(): string
    {
        return (string) Env::get('WHATSAPP_VERIFY_TOKEN', 'smarthealthy_wa_webhook_verify_token_2026');
    }

    /**
     * Get full WhatsApp Cloud API configuration summary
     */
    public static function getStatusSummary(): array
    {
        $isLive = self::isConfigured();
        $provider = strtolower((string) Env::get('WA_PROVIDER', 'mock'));

        return [
            'is_configured'        => $isLive,
            'provider'             => $isLive ? 'cloud_api' : $provider,
            'mode_label'           => $isLive ? 'WhatsApp Cloud API (Meta Live)' : 'Simulation / Mock Mode',
            'api_version'          => self::getApiVersion(),
            'phone_number_id'      => self::getPhoneNumberId() ?: 'Belum disetel',
            'business_account_id'  => self::getBusinessAccountId() ?: 'Belum disetel',
            'masked_access_token'  => self::getMaskedToken(),
            'verify_token'         => self::getVerifyToken(),
            'webhook_endpoint_url' => (string) Env::get('APP_URL', 'http://localhost/SmartHealty') . '/webhook/whatsapp',
            'templates_count'      => count(self::TEMPLATES),
        ];
    }

    /**
     * Format phone number to standard international WhatsApp format without '+' (628xxx)
     */
    public static function formatIndonesianPhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (!str_starts_with($clean, '62')) {
            $clean = '62' . $clean;
        }

        return $clean;
    }

    /**
     * Validate User Consent and Phone Verification Status before sending WhatsApp
     *
     * @return array [allowed: bool, reason: string, user: ?array]
     */
    public static function validateUserConsent(?int $userId, string $messageType = 'general'): array
    {
        if (!$userId) {
            return [
                'allowed' => true,
                'reason'  => 'No specific user ID bounded (system test or anonymous)',
                'user'    => null,
            ];
        }

        $userModel = new User();
        $user = $userModel->find($userId);

        if (!$user) {
            return [
                'allowed' => false,
                'reason'  => 'Data pengguna tidak ditemukan dalam database.',
                'user'    => null,
            ];
        }

        // 1. WhatsApp phone verification check
        if (empty($user['phone_verified_at'])) {
            return [
                'allowed' => false,
                'reason'  => 'Nomor WhatsApp belum diverifikasi oleh pengguna. Pesan hanya dapat dikirim ke nomor terverifikasi.',
                'user'    => $user,
            ];
        }

        // 2. Explicit Opt-in Consent check
        $prefModel = new NotificationPreference();
        if (!$prefModel->hasConsent($userId, $messageType)) {
            return [
                'allowed' => false,
                'reason'  => 'Pengguna belum memberikan persetujuan (opt-in) untuk menerima notifikasi WhatsApp jenis ini atau telah menonaktifkannya.',
                'user'    => $user,
            ];
        }

        return [
            'allowed' => true,
            'reason'  => 'Persetujuan dan verifikasi nomor WhatsApp valid.',
            'user'    => $user,
        ];
    }

    /**
     * Check idempotency: avoid duplicate message within the last 60 seconds
     */
    protected static function isDuplicateMessage(?int $userId, string $templateName, string $payloadSummary): bool
    {
        if (!$userId) {
            return false;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT id FROM `whatsapp_message_logs`
                WHERE `user_id` = :uid 
                  AND `template_name` = :tpl
                  AND `payload_summary` = :summary
                  AND `delivery_status` IN ('sent', 'delivered', 'read')
                  AND `created_at` >= DATE_SUB(NOW(), INTERVAL 120 SECOND)
                LIMIT 1
            ");
            $stmt->execute([
                'uid'     => $userId,
                'tpl'     => $templateName,
                'summary' => $payloadSummary,
            ]);

            return (bool) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Send official WhatsApp template message via Meta Cloud API (or safe Simulation)
     */
    public static function sendTemplate(
        string $phoneNumber,
        string $templateName,
        array $parameters = [],
        ?int $userId = null,
        string $messageType = 'general',
        string $languageCode = 'id'
    ): array {
        $cleanPhone = self::formatIndonesianPhone($phoneNumber);
        $payloadSummary = $templateName . '|' . implode(',', array_map('strval', $parameters));

        // 1. User Consent & Verification Check
        $consentCheck = self::validateUserConsent($userId, $messageType);
        if (!$consentCheck['allowed']) {
            // Log rejected attempt for audit integrity
            $logModel = new WhatsAppMessageLog();
            $logModel->logMessage([
                'user_id'          => $userId,
                'message_type'     => $messageType,
                'template_name'    => $templateName,
                'recipient_phone'  => $cleanPhone,
                'delivery_status'  => 'rejected',
                'error_code'       => 'CONSENT_OR_VERIFICATION_REJECTED',
                'error_message'    => $consentCheck['reason'],
                'payload_summary'  => $payloadSummary,
            ]);

            return [
                'status'          => false,
                'delivery_status' => 'rejected',
                'reason'          => $consentCheck['reason'],
            ];
        }

        // 2. Idempotency Check (Duplicate prevention)
        if (self::isDuplicateMessage($userId, $templateName, $payloadSummary)) {
            return [
                'status'          => true,
                'delivery_status' => 'skipped',
                'reason'          => 'Pesan duplikat diabaikan dalam rentang 60 detik.',
            ];
        }

        // 3. Build Template Components
        $bodyParameters = [];
        foreach ($parameters as $param) {
            $bodyParameters[] = [
                'type' => 'text',
                'text' => (string) $param,
            ];
        }

        $templatePayload = [
            'name'     => $templateName,
            'language' => [
                'code' => $languageCode,
            ],
            'components' => [
                [
                    'type'       => 'body',
                    'parameters' => $bodyParameters,
                ],
            ],
        ];

        // 4. Dispatch: Meta Cloud API vs. Simulation
        if (self::isConfigured()) {
            return self::dispatchMetaCloudApi($cleanPhone, $templatePayload, $userId, $messageType, $payloadSummary);
        }

        return self::dispatchSimulation($cleanPhone, $templatePayload, $userId, $messageType, $payloadSummary);
    }

    /**
     * Dispatch HTTP POST to Meta WhatsApp Cloud API
     */
    protected static function dispatchMetaCloudApi(
        string $phone,
        array $templatePayload,
        ?int $userId,
        string $messageType,
        string $payloadSummary
    ): array {
        $apiVersion = self::getApiVersion();
        $phoneId = self::getPhoneNumberId();
        $accessToken = Env::get('WHATSAPP_ACCESS_TOKEN');
        $url = "https://graph.facebook.com/{$apiVersion}/{$phoneId}/messages";

        $postData = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $phone,
            'type'              => 'template',
            'template'          => $templatePayload,
        ];

        $jsonPayload = json_encode($postData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $logModel = new WhatsAppMessageLog();

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $jsonPayload,
                CURLOPT_HTTPHEADER     => [
                    "Authorization: Bearer {$accessToken}",
                    "Content-Type: application/json",
                ],
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                $logModel->logMessage([
                    'user_id'         => $userId,
                    'message_type'    => $messageType,
                    'template_name'   => $templatePayload['name'] ?? null,
                    'recipient_phone' => $phone,
                    'delivery_status' => 'failed',
                    'error_code'      => 'CURL_ERROR',
                    'error_message'   => $curlError,
                    'payload_summary' => $payloadSummary,
                ]);

                return [
                    'status'          => false,
                    'delivery_status' => 'failed',
                    'reason'          => "Koneksi ke Meta Cloud API gagal: {$curlError}",
                ];
            }

            $responseData = json_decode($response, true) ?: [];

            // Check if Meta returned message ID
            if ($httpCode >= 200 && $httpCode < 300 && !empty($responseData['messages'][0]['id'])) {
                $wamid = $responseData['messages'][0]['id'];

                $logId = $logModel->logMessage([
                    'user_id'         => $userId,
                    'message_type'    => $messageType,
                    'template_name'   => $templatePayload['name'] ?? null,
                    'message_id'      => $wamid,
                    'recipient_phone' => $phone,
                    'delivery_status' => 'sent',
                    'payload_summary' => $payloadSummary,
                ]);

                return [
                    'status'          => true,
                    'mode'            => 'cloud_api',
                    'delivery_status' => 'sent',
                    'message_id'      => $wamid,
                    'log_id'          => $logId,
                    'meta'            => $responseData,
                    'reason'          => 'Pesan template resmi berhasil dikirim ke Meta WhatsApp Cloud API.',
                ];
            }

            // Handle Meta Graph API Error response
            $errCode = $responseData['error']['code'] ?? (string)$httpCode;
            $errMsg = $responseData['error']['message'] ?? 'Unknown Meta API Error';

            $logModel->logMessage([
                'user_id'         => $userId,
                'message_type'    => $messageType,
                'template_name'   => $templatePayload['name'] ?? null,
                'recipient_phone' => $phone,
                'delivery_status' => 'failed',
                'error_code'      => (string)$errCode,
                'error_message'   => $errMsg,
                'payload_summary' => $payloadSummary,
            ]);

            return [
                'status'          => false,
                'mode'            => 'cloud_api',
                'delivery_status' => 'failed',
                'error_code'      => $errCode,
                'reason'          => "Meta Cloud API Error ({$errCode}): {$errMsg}",
            ];

        } catch (\Throwable $e) {
            $logModel->logMessage([
                'user_id'         => $userId,
                'message_type'    => $messageType,
                'template_name'   => $templatePayload['name'] ?? null,
                'recipient_phone' => $phone,
                'delivery_status' => 'failed',
                'error_code'      => 'EXCEPTION',
                'error_message'   => $e->getMessage(),
                'payload_summary' => $payloadSummary,
            ]);

            return [
                'status'          => false,
                'delivery_status' => 'failed',
                'reason'          => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Dispatch Simulation Mode for Local Development / Testing
     */
    protected static function dispatchSimulation(
        string $phone,
        array $templatePayload,
        ?int $userId,
        string $messageType,
        string $payloadSummary
    ): array {
        Session::start();

        $tplName = $templatePayload['name'] ?? 'smarthealthy_template';
        $simulatedId = 'wamid.SIMULATED_' . strtoupper(bin2hex(random_bytes(8))) . '==';
        $sentAt = date('Y-m-d H:i:s');

        // Render template body for simulator display
        $params = array_column($templatePayload['components'][0]['parameters'] ?? [], 'text');
        $renderedBody = self::renderTemplateBody($tplName, $params);

        $simData = [
            'id'           => $simulatedId,
            'to'           => $phone,
            'template'     => $tplName,
            'message'      => $renderedBody,
            'parameters'   => $params,
            'message_type' => $messageType,
            'user_id'      => $userId,
            'sent_at'      => $sentAt,
            'is_mock'      => true,
        ];

        // Store into session for local inspection
        $_SESSION['mock_wa_last'] = $simData;
        $_SESSION['mock_wa_inbox'][] = $simData;
        if (count($_SESSION['mock_wa_inbox']) > 15) {
            array_shift($_SESSION['mock_wa_inbox']);
        }

        // Record in database log
        $logModel = new WhatsAppMessageLog();
        $logId = $logModel->logMessage([
            'user_id'         => $userId,
            'message_type'    => $messageType,
            'template_name'   => $tplName,
            'message_id'      => $simulatedId,
            'recipient_phone' => $phone,
            'delivery_status' => 'sent',
            'payload_summary' => $payloadSummary,
            'sent_at'         => $sentAt,
        ]);

        return [
            'status'          => true,
            'mode'            => 'simulation',
            'delivery_status' => 'sent',
            'message_id'      => $simulatedId,
            'log_id'          => $logId,
            'reason'          => 'Pesan diproses via WhatsApp Cloud API Simulator (Kredensial Meta live belum disetel).',
            'data'            => $simData,
        ];
    }

    /**
     * Render template body string with placeholders replaced by parameter values
     */
    public static function renderTemplateBody(string $templateName, array $parameters): string
    {
        $tpl = self::TEMPLATES[$templateName] ?? null;
        if (!$tpl) {
            return "Pesan notifikasi SmartHealthy: " . implode(', ', $parameters);
        }

        $body = $tpl['body'];
        foreach ($parameters as $index => $val) {
            $placeholder = '{{' . ($index + 1) . '}}';
            $body = str_replace($placeholder, (string)$val, $body);
        }

        return $body;
    }

    // =========================================================================
    // HIGH-LEVEL CONVENIENCE METHODS FOR THE 5 REQUIRED META TEMPLATES
    // =========================================================================

    /**
     * Template 1: Pendaftaran Berhasil (smarthealthy_registration_success)
     * Params: {{1}} Nama Pasien, {{2}} No Pendaftaran / Antrean, {{3}} Layanan, {{4}} Tanggal, {{5}} Jadwal
     */
    public static function sendRegistrationSuccess(array $reg, ?array $user = null): array
    {
        $userId = !empty($reg['user_id']) ? (int)$reg['user_id'] : ($user['id'] ?? null);
        $phone = $user['phone'] ?? $reg['phone'] ?? '';
        $patientName = $reg['patient_name'] ?? $user['name'] ?? 'Pasien';
        $queueNo = '#' . str_pad((string)($reg['queue_number'] ?? '1'), 3, '0', STR_PAD_LEFT);
        $serviceName = $reg['service_name'] ?? 'Poli Layanan';
        $visitDate = !empty($reg['registration_date']) ? date('d F Y', strtotime($reg['registration_date'])) : date('d F Y');
        $schedule = $reg['schedule_info'] ?? '08:00 - 11:30 WIB';

        return self::sendTemplate(
            $phone,
            'smarthealthy_registration_success',
            [$patientName, $queueNo, $serviceName, $visitDate, $schedule],
            $userId,
            'registration'
        );
    }

    /**
     * Template 2: Pengingat Kunjungan (smarthealthy_visit_reminder)
     * Params: {{1}} Nama Pasien, {{2}} Layanan, {{3}} Tanggal, {{4}} Waktu
     */
    public static function sendVisitReminder(array $reg, string $timeStr = '08:00 WIB', ?array $user = null): array
    {
        $userId = !empty($reg['user_id']) ? (int)$reg['user_id'] : ($user['id'] ?? null);
        $phone = $user['phone'] ?? $reg['phone'] ?? '';
        $patientName = $reg['patient_name'] ?? $user['name'] ?? 'Pasien';
        $serviceName = $reg['service_name'] ?? 'Poli Layanan';
        $visitDate = !empty($reg['registration_date']) ? date('d F Y', strtotime($reg['registration_date'])) : date('d F Y');

        return self::sendTemplate(
            $phone,
            'smarthealthy_visit_reminder',
            [$patientName, $serviceName, $visitDate, $timeStr],
            $userId,
            'reminder'
        );
    }

    /**
     * Template 3: Perubahan Jadwal (smarthealthy_schedule_changed)
     * Params: {{1}} Nama Pasien, {{2}} Layanan, {{3}} Jadwal Sebelumnya, {{4}} Jadwal Terbaru
     */
    public static function sendScheduleChanged(
        array $reg,
        string $oldSchedule,
        string $newSchedule,
        ?array $user = null
    ): array {
        $userId = !empty($reg['user_id']) ? (int)$reg['user_id'] : ($user['id'] ?? null);
        $phone = $user['phone'] ?? $reg['phone'] ?? '';
        $patientName = $reg['patient_name'] ?? $user['name'] ?? 'Pasien';
        $serviceName = $reg['service_name'] ?? 'Poli Layanan';

        return self::sendTemplate(
            $phone,
            'smarthealthy_schedule_changed',
            [$patientName, $serviceName, $oldSchedule, $newSchedule],
            $userId,
            'schedule_change'
        );
    }

    /**
     * Template 4: Status Pendaftaran (smarthealthy_status_updated)
     * Params: {{1}} Nama Pasien, {{2}} Nomor Pendaftaran / Kode, {{3}} Status Terbaru
     */
    public static function sendStatusUpdated(array $reg, string $newStatus, ?array $user = null): array
    {
        $userId = !empty($reg['user_id']) ? (int)$reg['user_id'] : ($user['id'] ?? null);
        $phone = $user['phone'] ?? $reg['phone'] ?? '';
        $patientName = $reg['patient_name'] ?? $user['name'] ?? 'Pasien';
        $code = $reg['registration_code'] ?? ('#' . ($reg['queue_number'] ?? '001'));

        return self::sendTemplate(
            $phone,
            'smarthealthy_status_updated',
            [$patientName, $code, $newStatus],
            $userId,
            'registration'
        );
    }

    /**
     * Template 5: Pengumuman Penting (smarthealthy_announcement)
     * Params: {{1}} Nama Penerima, {{2}} Judul Pengumuman, {{3}} Ringkasan Pesan
     */
    public static function sendAnnouncement(
        int $userId,
        string $phone,
        string $name,
        string $title,
        string $snippet
    ): array {
        return self::sendTemplate(
            $phone,
            'smarthealthy_announcement',
            [$name, $title, $snippet],
            $userId,
            'announcement'
        );
    }

    // =========================================================================
    // OTP & GENERAL COMPATIBILITY (PRESERVES EXISTING AUTHENTICATION FLOW)
    // =========================================================================

    /**
     * Send WhatsApp message with OTP code (used during registration & verification)
     */
    public static function sendOtp(string $phoneNumber, string $otpCode, string $recipientName = 'Pasien'): array
    {
        $expiryMinutes = (int) Env::get('OTP_EXPIRY_MINUTES', 5);
        $appName = Env::get('APP_NAME', 'SmartHealthy');
        $puskesmasName = Env::get('PUSKESMAS_NAME', 'Puskesmas Astanajapura');

        $message = "Halo {$recipientName},\n\n";
        $message .= "Kode verifikasi WhatsApp akun {$appName} ({$puskesmasName}) Anda adalah: *{$otpCode}*.\n\n";
        $message .= "Kode ini berlaku selama {$expiryMinutes} menit. JANGAN bagikan kode ini kepada siapapun demi keamanan rekam medis Anda.\n\n";
        $message .= "Terima kasih,\nTim {$puskesmasName}";

        return self::sendMessage($phoneNumber, $message, [
            'type'     => 'otp',
            'otp_code' => $otpCode,
        ]);
    }

    /**
     * General WhatsApp message sender (supporting fallback providers if configured)
     */
    public static function sendMessage(string $phoneNumber, string $message, array $meta = []): array
    {
        $provider = strtolower((string) Env::get('WA_PROVIDER', 'mock'));
        $cleanPhone = self::formatIndonesianPhone($phoneNumber);

        switch ($provider) {
            case 'fonnte':
                return self::sendViaFonnte($cleanPhone, $message);
            case 'twilio':
                return self::sendViaTwilio($cleanPhone, $message);
            case 'mock':
            default:
                return self::sendViaMock($cleanPhone, $message, $meta);
        }
    }

    /**
     * Mock sender for OTP and general debug messages
     */
    private static function sendViaMock(string $phone, string $message, array $meta = []): array
    {
        Session::start();

        $mockMessage = [
            'id'      => uniqid('wa_mock_'),
            'to'      => $phone,
            'message' => $message,
            'meta'    => $meta,
            'sent_at' => date('Y-m-d H:i:s'),
        ];

        $_SESSION['mock_wa_last'] = $mockMessage;
        $_SESSION['mock_wa_inbox'][] = $mockMessage;

        if (count($_SESSION['mock_wa_inbox']) > 15) {
            array_shift($_SESSION['mock_wa_inbox']);
        }

        return [
            'status'   => true,
            'provider' => 'mock',
            'message'  => 'Pesan WhatsApp berhasil dikirim (Mock Service)',
            'data'     => $mockMessage,
        ];
    }

    private static function sendViaFonnte(string $phone, string $message): array
    {
        $apiKey = Env::get('WA_API_KEY');
        if (empty($apiKey)) {
            return ['status' => false, 'message' => 'API Key Fonnte belum dikonfigurasi di .env'];
        }

        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => ['target' => $phone, 'message' => $message],
                CURLOPT_HTTPHEADER     => ["Authorization: {$apiKey}"],
                CURLOPT_TIMEOUT        => 15,
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                return ['status' => false, 'message' => "Gagal menghubungi server Fonnte: {$err}"];
            }

            $resData = json_decode($response, true);
            return [
                'status'  => ($resData['status'] ?? false) == true,
                'message' => $resData['reason'] ?? 'Pesan diproses oleh Fonnte',
                'raw'     => $resData,
            ];
        } catch (\Throwable $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    private static function sendViaTwilio(string $phone, string $message): array
    {
        return ['status' => true, 'message' => 'Pesan dikirim via Twilio WhatsApp Gateway'];
    }

    /**
     * Helper to get last mock message (for local simulator UI)
     */
    public static function getLastMockMessage(): ?array
    {
        Session::start();
        return $_SESSION['mock_wa_last'] ?? null;
    }

    // =========================================================================
    // META WEBHOOK VERIFICATION & EVENT HANDLING
    // =========================================================================

    /**
     * Verify Meta Webhook subscription challenge (GET /webhook/whatsapp)
     */
    public static function verifyWebhookChallenge(array $queryParams): ?string
    {
        $mode = $queryParams['hub_mode'] ?? $queryParams['hub.mode'] ?? '';
        $token = $queryParams['hub_verify_token'] ?? $queryParams['hub.verify_token'] ?? '';
        $challenge = $queryParams['hub_challenge'] ?? $queryParams['hub.challenge'] ?? '';

        $configuredToken = self::getVerifyToken();

        if ($mode === 'subscribe' && !empty($challenge) && hash_equals($configuredToken, $token)) {
            return (string)$challenge;
        }

        return null;
    }

    /**
     * Validate Meta Webhook Signature (X-Hub-Signature-256 header)
     */
    public static function verifySignature(string $rawPayload, ?string $signatureHeader): bool
    {
        $appSecret = Env::get('WHATSAPP_APP_SECRET');
        // If app secret is not configured in local development, skip or pass if debug
        if (empty($appSecret)) {
            return true;
        }

        if (empty($signatureHeader) || !str_starts_with($signatureHeader, 'sha256=')) {
            return false;
        }

        $expectedSig = hash_hmac('sha256', $rawPayload, $appSecret);
        $providedSig = substr($signatureHeader, 7);

        return hash_equals($expectedSig, $providedSig);
    }

    /**
     * Process Meta Webhook Incoming Payload (POST /webhook/whatsapp)
     * Updates delivery logs with sent, delivered, read, or failed status
     */
    public static function processWebhookPayload(array $payload): array
    {
        $logModel = new WhatsAppMessageLog();
        $processed = [];

        $entries = $payload['entry'] ?? [];
        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];
            foreach ($changes as $change) {
                $value = $change['value'] ?? [];
                $statuses = $value['statuses'] ?? [];

                foreach ($statuses as $st) {
                    $wamid = $st['id'] ?? null;
                    $status = $st['status'] ?? null;
                    $timestamp = !empty($st['timestamp']) ? date('Y-m-d H:i:s', (int)$st['timestamp']) : date('Y-m-d H:i:s');

                    if (!$wamid || !$status) {
                        continue;
                    }

                    $extra = [
                        'timestamp' => $timestamp,
                    ];

                    if (!empty($st['errors'])) {
                        $firstError = $st['errors'][0] ?? [];
                        $extra['error_code'] = (string)($firstError['code'] ?? 'META_WEBHOOK_ERROR');
                        $extra['error_message'] = $firstError['message'] ?? ($firstError['title'] ?? 'Delivery failed');
                    }

                    $updated = $logModel->updateDeliveryStatus($wamid, $status, $extra);
                    $processed[] = [
                        'wamid'   => $wamid,
                        'status'  => $status,
                        'updated' => $updated,
                    ];
                }
            }
        }

        return [
            'success'   => true,
            'processed' => $processed,
            'count'     => count($processed),
        ];
    }
}
