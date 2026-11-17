<?php

namespace App\Services;

use App\Core\Env;
use App\Core\Session;
use App\Core\Database;
use App\Models\User;
use App\Models\NotificationPreference;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppSetting;
use PDO;

class FonnteWhatsAppService
{
    public const FONNTE_SEND_URL = 'https://api.fonnte.com/send';
    public const FONNTE_DEVICE_URL = 'https://api.fonnte.com/device';
    public const FONNTE_GET_DEVICES_URL = 'https://api.fonnte.com/get-devices';

    /**
     * Get active Fonnte API Token (from DB settings or .env)
     */
    public static function getToken(): string
    {
        $settingModel = new WhatsAppSetting();
        return $settingModel->getFonnteToken();
    }

    /**
     * Check if Fonnte API token is configured
     */
    public static function isConfigured(): bool
    {
        $token = self::getToken();
        if (empty($token)) {
            return false;
        }

        if (str_starts_with($token, 'your_') || $token === 'mock_token') {
            return false;
        }

        return true;
    }

    /**
     * Get masked token for admin UI display (e.g. fonn****...****9x)
     */
    public static function getMaskedToken(): string
    {
        $token = self::getToken();
        if (empty($token)) {
            return 'Belum Dikonfigurasi';
        }

        $len = strlen($token);
        if ($len <= 8) {
            return '********';
        }

        return substr($token, 0, 4) . '****************' . substr($token, -4);
    }

    /**
     * Format phone number to Indonesian format standard (628xxx)
     */
    public static function formatPhone(string $phone): string
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
     * Check Fonnte device & connection status according to official documentation
     * Uses https://api.fonnte.com/device for device tokens (with fallback to /get-devices for account tokens)
     * Returns one of: 'unconfigured', 'connected', or 'failed'
     */
    public static function getConnectionStatus(?string $overrideToken = null): array
    {
        $settingModel = new WhatsAppSetting();
        $isGlobalEnabled = $settingModel->isWhatsAppEnabled();
        $rawToken = $overrideToken !== null ? $overrideToken : self::getToken();
        $token = trim(preg_replace('/\s+/', '', (string)$rawToken));

        // 1. Check if token is empty or placeholder
        if (empty($token) || str_starts_with($token, 'your_') || $token === 'mock_token') {
            return [
                'status'         => 'unconfigured',
                'connected'      => false,
                'mode_label'     => 'Belum Dikonfigurasi',
                'badge_class'    => 'bg-secondary text-white',
                'badge_icon'     => 'bi-dash-circle-fill',
                'is_enabled'     => $isGlobalEnabled,
                'masked_token'   => self::getMaskedToken(),
                'device_name'    => 'Belum Terhubung',
                'device_number'  => '-',
                'device_status'  => 'Tidak Aktif',
                'package'        => '-',
                'quota'          => '-',
                'expired'        => '-',
                'reason'         => 'API Token Fonnte belum dimasukkan. Masukkan API Token pada form konfigurasi untuk mengaktifkan gateway WhatsApp.',
            ];
        }

        // 2. Primary check: Call Fonnte Official Device Profile Endpoint (POST https://api.fonnte.com/device)
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => self::FONNTE_DEVICE_URL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                    "Authorization: {$token}",
                ],
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $res = curl_exec($ch);
            $err = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($err || $httpCode >= 400) {
                $errDetail = $err ?: ($httpCode === 401 ? 'Token Fonnte tidak valid (HTTP 401 Unauthorized)' : "Respon server HTTP {$httpCode}");
                return [
                    'status'         => 'failed',
                    'connected'      => false,
                    'mode_label'     => 'Koneksi Gagal',
                    'badge_class'    => 'bg-danger text-white',
                    'badge_icon'     => 'bi-x-circle-fill',
                    'is_enabled'     => $isGlobalEnabled,
                    'masked_token'   => self::getMaskedToken(),
                    'device_name'    => 'Unknown Device',
                    'device_number'  => '-',
                    'device_status'  => 'Disconnected',
                    'package'        => '-',
                    'quota'          => '-',
                    'expired'        => '-',
                    'reason'         => "Gagal menghubungi server Fonnte: {$errDetail}. Periksa kembali token API Anda.",
                ];
            }

            $data = json_decode((string)$res, true) ?: [];

            // Check if device profile endpoint succeeded
            if (isset($data['status']) && $data['status'] === true) {
                $rawStatus = strtolower((string)($data['device_status'] ?? ''));
                $isDeviceConnected = in_array($rawStatus, ['connect', 'connected', 'active'], true);

                if ($isDeviceConnected) {
                    return [
                        'status'         => 'connected',
                        'connected'      => true,
                        'mode_label'     => 'Terhubung',
                        'badge_class'    => 'bg-success text-white',
                        'badge_icon'     => 'bi-check-circle-fill',
                        'is_enabled'     => $isGlobalEnabled,
                        'masked_token'   => self::getMaskedToken(),
                        'device_name'    => $data['name'] ?? 'Perangkat WhatsApp SmartHealthy',
                        'device_number'  => $data['device'] ?? '-',
                        'device_status'  => 'Connected (Siap Kirim)',
                        'package'        => $data['package'] ?? 'Fonnte Package',
                        'quota'          => (string)($data['quota'] ?? ($data['messages'] ?? 'Aktif')),
                        'expired'        => $data['expired'] ?? '-',
                        'reason'         => 'Gateway Fonnte terhubung resmi dan siap mengirimkan notifikasi WhatsApp.',
                    ];
                } else {
                    return [
                        'status'         => 'failed',
                        'connected'      => false,
                        'mode_label'     => 'Koneksi Gagal (Perangkat Terputus)',
                        'badge_class'    => 'bg-warning text-dark',
                        'badge_icon'     => 'bi-exclamation-triangle-fill',
                        'is_enabled'     => $isGlobalEnabled,
                        'masked_token'   => self::getMaskedToken(),
                        'device_name'    => $data['name'] ?? 'Perangkat Fonnte',
                        'device_number'  => $data['device'] ?? '-',
                        'device_status'  => 'Disconnected (Scan QR)',
                        'package'        => $data['package'] ?? '-',
                        'quota'          => (string)($data['quota'] ?? '-'),
                        'expired'        => $data['expired'] ?? '-',
                        'reason'         => 'Token valid tetapi status perangkat terputus (Disconnect). Buka dashboard Fonnte.com untuk scan QR perangkat WhatsApp Anda.',
                    ];
                }
            }

            // 3. Fallback check: in case an Account Token was supplied instead of Device Token
            $ch2 = curl_init();
            curl_setopt_array($ch2, [
                CURLOPT_URL            => self::FONNTE_GET_DEVICES_URL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                    "Authorization: {$token}",
                ],
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $res2 = curl_exec($ch2);
            curl_close($ch2);
            $data2 = json_decode((string)$res2, true) ?: [];

            if (isset($data2['status']) && $data2['status'] === true && !empty($data2['data'])) {
                $deviceInfo = $data2['data'][0] ?? [];
                $rawStatus = strtolower((string)($deviceInfo['status'] ?? ''));
                $isDeviceConnected = in_array($rawStatus, ['connect', 'connected', 'active'], true);

                if ($isDeviceConnected) {
                    return [
                        'status'         => 'connected',
                        'connected'      => true,
                        'mode_label'     => 'Terhubung',
                        'badge_class'    => 'bg-success text-white',
                        'badge_icon'     => 'bi-check-circle-fill',
                        'is_enabled'     => $isGlobalEnabled,
                        'masked_token'   => self::getMaskedToken(),
                        'device_name'    => $deviceInfo['name'] ?? 'Perangkat WhatsApp (Account Token)',
                        'device_number'  => $deviceInfo['device'] ?? '-',
                        'device_status'  => 'Connected',
                        'package'        => $deviceInfo['package'] ?? '-',
                        'quota'          => (string)($deviceInfo['quota'] ?? ($deviceInfo['messages'] ?? '-')),
                        'expired'        => $deviceInfo['expired'] ?? '-',
                        'reason'         => 'Gateway Fonnte terhubung melalui Account Token resmi.',
                    ];
                }
            }

            // If both failed, return exact reason from Fonnte without exposing token
            $rawReason = $data['reason'] ?? ($data2['reason'] ?? 'API Token tidak valid atau akun Fonnte tidak ditemukan.');
            return [
                'status'         => 'failed',
                'connected'      => false,
                'mode_label'     => 'Koneksi Gagal',
                'badge_class'    => 'bg-danger text-white',
                'badge_icon'     => 'bi-x-circle-fill',
                'is_enabled'     => $isGlobalEnabled,
                'masked_token'   => self::getMaskedToken(),
                'device_name'    => 'Unknown Device',
                'device_number'  => '-',
                'device_status'  => 'Autentikasi Gagal',
                'package'        => '-',
                'quota'          => '-',
                'expired'        => '-',
                'reason'         => "Fonnte API: {$rawReason}",
            ];

        } catch (\Throwable $e) {
            return [
                'status'         => 'failed',
                'connected'      => false,
                'mode_label'     => 'Koneksi Gagal',
                'badge_class'    => 'bg-danger text-white',
                'badge_icon'     => 'bi-x-circle-fill',
                'is_enabled'     => $isGlobalEnabled,
                'masked_token'   => self::getMaskedToken(),
                'device_name'    => 'Unknown Device',
                'device_status'  => 'Exception Error',
                'package'        => '-',
                'quota'          => '-',
                'expired'        => '-',
                'reason'         => 'Kesalahan jaringan: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate User Consent, Phone Verification, and Admin Master Switches
     */
    public static function validatePreSend(?int $userId, string $messageType = 'general'): array
    {
        // Test messages are purely administrative to verify connectivity and bypass automatic notification switches
        if ($messageType === 'test') {
            return [
                'allowed' => true,
                'reason'  => 'Pesan uji coba admin / sistem.',
                'code'    => 'TEST_OK',
            ];
        }

        $settingModel = new WhatsAppSetting();

        // 1. Check Admin Master Switch
        if (!$settingModel->isWhatsAppEnabled()) {
            return [
                'allowed' => false,
                'reason'  => 'Pengiriman notifikasi WhatsApp dinonaktifkan oleh Administrator.',
                'code'    => 'ADMIN_MASTER_DISABLED',
            ];
        }

        // 2. Check Admin Per-Event Switch
        if (!$settingModel->isEventEnabled($messageType)) {
            return [
                'allowed' => false,
                'reason'  => "Notifikasi WhatsApp untuk jenis event '{$messageType}' dinonaktifkan oleh Administrator.",
                'code'    => 'ADMIN_EVENT_DISABLED',
            ];
        }

        // If no user bounded (system test send)
        if (!$userId) {
            return [
                'allowed' => true,
                'reason'  => 'Pesan uji coba admin / sistem.',
                'code'    => 'SYSTEM_OK',
            ];
        }

        $userModel = new User();
        $user = $userModel->find($userId);

        if (!$user) {
            return [
                'allowed' => false,
                'reason'  => 'Pengguna tidak ditemukan dalam sistem.',
                'code'    => 'USER_NOT_FOUND',
            ];
        }

        // 3. User Phone Verification Check
        if (empty($user['phone_verified_at'])) {
            return [
                'allowed' => false,
                'reason'  => 'Nomor WhatsApp pengguna belum diverifikasi (OTP). Pesan hanya dapat dikirim ke nomor terverifikasi.',
                'code'    => 'PHONE_NOT_VERIFIED',
            ];
        }

        // 4. User Explicit Consent Check
        $prefModel = new NotificationPreference();
        if (!$prefModel->hasConsent($userId, $messageType)) {
            return [
                'allowed' => false,
                'reason'  => "Pengguna belum memberikan persetujuan (opt-in) atau menonaktifkan notifikasi '{$messageType}'.",
                'code'    => 'CONSENT_REQUIRED',
            ];
        }

        return [
            'allowed' => true,
            'reason'  => 'Persetujuan pengguna dan verifikasi nomor valid.',
            'code'    => 'ALLOWED',
            'user'    => $user,
        ];
    }

    /**
     * Check idempotency to prevent duplicate message within 60 seconds
     */
    protected static function isDuplicateMessage(?int $userId, string $templateKey, string $payloadSummary): bool
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
                  AND `created_at` >= DATE_SUB(NOW(), INTERVAL 60 SECOND)
                LIMIT 1
            ");
            $stmt->execute([
                'uid'     => $userId,
                'tpl'     => $templateKey,
                'summary' => $payloadSummary,
            ]);

            return (bool)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Core Message Dispatcher via Fonnte API (with retry and simulation fallback)
     */
    public static function send(
        string $phoneNumber,
        string $message,
        string $messageType = 'general',
        string $templateKey = 'custom',
        ?int $userId = null,
        ?string $payloadSummary = null
    ): array {
        $cleanPhone = self::formatPhone($phoneNumber);
        $summary = $payloadSummary ?: ($templateKey . '|' . mb_substr($message, 0, 80));

        // 1. Pre-Send Guards (Admin switches, phone verification, user consent)
        $guard = self::validatePreSend($userId, $messageType);
        if (!$guard['allowed']) {
            $logModel = new WhatsAppMessageLog();
            $logModel->logMessage([
                'user_id'                 => $userId,
                'message_type'            => $messageType,
                'template_name'           => $templateKey,
                'recipient_phone'         => $cleanPhone,
                'recipient_number_masked' => WhatsAppMessageLog::maskPhone($cleanPhone),
                'delivery_status'         => 'rejected',
                'error_code'              => $guard['code'],
                'error_message'           => $guard['reason'],
                'payload_summary'         => $summary,
            ]);

            return [
                'status'          => false,
                'delivery_status' => 'rejected',
                'code'            => $guard['code'],
                'reason'          => $guard['reason'],
            ];
        }

        // 2. Idempotency Check (Anti-Duplicate Guard)
        if (self::isDuplicateMessage($userId, $templateKey, $summary)) {
            return [
                'status'          => true,
                'delivery_status' => 'skipped',
                'reason'          => 'Pesan duplikat diabaikan dalam rentang 60 detik (Idempotency Active).',
            ];
        }

        // 3. Dispatch Live Fonnte vs. Simulation
        if (self::isConfigured()) {
            return self::dispatchLiveFonnte($cleanPhone, $message, $messageType, $templateKey, $userId, $summary);
        }

        return self::dispatchSimulation($cleanPhone, $message, $messageType, $templateKey, $userId, $summary);
    }

    /**
     * HTTP POST to Fonnte API with 1 retry on timeout
     */
    protected static function dispatchLiveFonnte(
        string $phone,
        string $message,
        string $messageType,
        string $templateKey,
        ?int $userId,
        string $payloadSummary
    ): array {
        $token = self::getToken();
        $logModel = new WhatsAppMessageLog();
        $maxRetries = 2;
        $attempt = 0;
        $lastError = '';

        while ($attempt < $maxRetries) {
            $attempt++;

            try {
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL            => self::FONNTE_SEND_URL,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => [
                        'target'      => $phone,
                        'message'     => $message,
                        'countryCode' => '62',
                    ],
                    CURLOPT_HTTPHEADER     => [
                        "Authorization: {$token}",
                    ],
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_CONNECTTIMEOUT => 6,
                    CURLOPT_SSL_VERIFYPEER => true,
                ]);

                $response = curl_exec($curl);
                $err = curl_error($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                if ($err) {
                    $lastError = "cURL Error: {$err}";
                    if ($attempt < $maxRetries) {
                        usleep(1000000); // 1-second backoff before retry
                        continue;
                    }
                    break;
                }

                $resData = json_decode((string)$response, true) ?: [];

                // Fonnte returns {"status": true, "id": ["123456"], "process": "processing"}
                if (!empty($resData['status'])) {
                    $fonnteMsgId = is_array($resData['id'] ?? null) 
                        ? ($resData['id'][0] ?? uniqid('fonnte_')) 
                        : ($resData['id'] ?? uniqid('fonnte_'));

                    $logId = $logModel->logMessage([
                        'user_id'                 => $userId,
                        'message_type'            => $messageType,
                        'template_name'           => $templateKey,
                        'message_id'              => (string)$fonnteMsgId,
                        'recipient_phone'         => $phone,
                        'recipient_number_masked' => WhatsAppMessageLog::maskPhone($phone),
                        'delivery_status'         => 'sent',
                        'payload_summary'         => $payloadSummary,
                        'sent_at'                 => date('Y-m-d H:i:s'),
                    ]);

                    return [
                        'status'          => true,
                        'mode'            => 'fonnte',
                        'delivery_status' => 'sent',
                        'message_id'      => (string)$fonnteMsgId,
                        'log_id'          => $logId,
                        'response'        => $resData,
                        'reason'          => 'Pesan WhatsApp berhasil dikirim via Fonnte API.',
                    ];
                }

                // If Fonnte returned status false with reason
                $failReason = $resData['reason'] ?? ($resData['message'] ?? 'Fonnte Gateway error');
                $logModel->logMessage([
                    'user_id'                 => $userId,
                    'message_type'            => $messageType,
                    'template_name'           => $templateKey,
                    'recipient_phone'         => $phone,
                    'recipient_number_masked' => WhatsAppMessageLog::maskPhone($phone),
                    'delivery_status'         => 'failed',
                    'error_code'              => 'FONNTE_ERROR',
                    'error_message'           => $failReason,
                    'payload_summary'         => $payloadSummary,
                ]);

                return [
                    'status'          => false,
                    'mode'            => 'fonnte',
                    'delivery_status' => 'failed',
                    'reason'          => "Fonnte API Error: {$failReason}",
                    'response'        => $resData,
                ];

            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                if ($attempt < $maxRetries) {
                    usleep(1000000);
                    continue;
                }
            }
        }

        // Exhausted retries
        $logModel->logMessage([
            'user_id'                 => $userId,
            'message_type'            => $messageType,
            'template_name'           => $templateKey,
            'recipient_phone'         => $phone,
            'recipient_number_masked' => WhatsAppMessageLog::maskPhone($phone),
            'delivery_status'         => 'failed',
            'error_code'              => 'RETRY_EXHAUSTED',
            'error_message'           => $lastError,
            'payload_summary'         => $payloadSummary,
        ]);

        return [
            'status'          => false,
            'mode'            => 'fonnte',
            'delivery_status' => 'failed',
            'reason'          => "Gagal menghubungi Fonnte setelah {$maxRetries} percobaan: {$lastError}",
        ];
    }

    /**
     * Safe Simulation Dispatcher when Fonnte token is not configured
     */
    protected static function dispatchSimulation(
        string $phone,
        string $message,
        string $messageType,
        string $templateKey,
        ?int $userId,
        string $payloadSummary
    ): array {
        Session::start();

        $simulatedId = 'fonnte_sim_' . strtoupper(bin2hex(random_bytes(6)));
        $sentAt = date('Y-m-d H:i:s');

        $mockData = [
            'id'           => $simulatedId,
            'to'           => $phone,
            'message'      => $message,
            'message_type' => $messageType,
            'template'     => $templateKey,
            'user_id'      => $userId,
            'sent_at'      => $sentAt,
            'provider'     => 'fonnte_simulation',
        ];

        $_SESSION['mock_wa_last'] = $mockData;
        $_SESSION['mock_wa_inbox'][] = $mockData;
        if (count($_SESSION['mock_wa_inbox']) > 15) {
            array_shift($_SESSION['mock_wa_inbox']);
        }

        $logModel = new WhatsAppMessageLog();
        $logId = $logModel->logMessage([
            'user_id'                 => $userId,
            'message_type'            => $messageType,
            'template_name'           => $templateKey,
            'message_id'              => $simulatedId,
            'recipient_phone'         => $phone,
            'recipient_number_masked' => WhatsAppMessageLog::maskPhone($phone),
            'delivery_status'         => 'sent',
            'payload_summary'         => $payloadSummary,
            'sent_at'                 => $sentAt,
        ]);

        return [
            'status'          => true,
            'mode'            => 'simulation',
            'delivery_status' => 'sent',
            'message_id'      => $simulatedId,
            'log_id'          => $logId,
            'reason'          => 'Pesan diproses via Fonnte WhatsApp Simulator (Token Fonnte belum disetel).',
            'data'            => $mockData,
        ];
    }

    // =========================================================================
    // EVENT CONVENIENCE METHODS (7 SPECIFIED AUTOMATED EVENTS)
    // =========================================================================

    /**
     * Event 1 & 2: Pendaftaran Berhasil & Nomor Pendaftaran Diterbitkan
     */
    public static function sendRegistrationSuccess(array $reg, ?array $user = null): array
    {
        $userId = !empty($reg['user_id']) ? (int)$reg['user_id'] : ($user['id'] ?? null);
        $phone = $user['phone'] ?? $reg['phone'] ?? '';
        $patientName = $reg['patient_name'] ?? $user['name'] ?? 'Pasien';
        $queueNo = '#' . str_pad((string)($reg['queue_number'] ?? '1'), 3, '0', STR_PAD_LEFT);
        $code = $reg['registration_code'] ?? '-';
        $serviceName = $reg['service_name'] ?? 'Poli Layanan';
        $doctorName = $reg['doctor_name'] ?? 'Dokter Jaga Poli';
        $dateFormatted = !empty($reg['registration_date']) ? date('d F Y', strtotime($reg['registration_date'])) : date('d F Y');
        $schedule = $reg['schedule_info'] ?? '08:00 - 11:30 WIB';

        $settingModel = new WhatsAppSetting();
        $message = $settingModel->renderTemplate('template_registration', [
            'nama_pasien'      => $patientName,
            'nomor_antrean'    => $queueNo,
            'kode_booking'     => $code,
            'nama_layanan'     => $serviceName,
            'tanggal_kunjungan'=> $dateFormatted,
            'jadwal_layanan'   => $schedule,
            'nama_dokter'      => $doctorName,
        ]);

        return self::send(
            $phone,
            $message,
            'registration',
            'template_registration',
            $userId,
            "registration|{$code}|{$queueNo}"
        );
    }

    /**
     * Event 3: Status Pendaftaran Berubah
     */
    public static function sendStatusChange(array $reg, string $newStatus, ?string $notes = null, ?array $user = null): array
    {
        $userId = !empty($reg['user_id']) ? (int)$reg['user_id'] : ($user['id'] ?? null);
        $phone = $user['phone'] ?? $reg['phone'] ?? '';
        $patientName = $reg['patient_name'] ?? $user['name'] ?? 'Pasien';
        $code = $reg['registration_code'] ?? '-';
        $serviceName = $reg['service_name'] ?? 'Poli Layanan';
        $catatan = !empty($notes) ? $notes : '-';

        $settingModel = new WhatsAppSetting();
        $message = $settingModel->renderTemplate('template_status_change', [
            'nama_pasien'  => $patientName,
            'kode_booking' => $code,
            'nama_layanan' => $serviceName,
            'status_baru'  => $newStatus,
            'catatan'      => $catatan,
        ]);

        return self::send(
            $phone,
            $message,
            'status_change',
            'template_status_change',
            $userId,
            "status_change|{$code}|{$newStatus}"
        );
    }

    /**
     * Event 4A: Pengingat Kunjungan H-1 Hari
     */
    public static function sendReminderH1(array $reg, ?array $user = null): array
    {
        $userId = !empty($reg['user_id']) ? (int)$reg['user_id'] : ($user['id'] ?? null);
        $phone = $user['phone'] ?? $reg['phone'] ?? '';
        $patientName = $reg['patient_name'] ?? $user['name'] ?? 'Pasien';
        $queueNo = '#' . str_pad((string)($reg['queue_number'] ?? '1'), 3, '0', STR_PAD_LEFT);
        $serviceName = $reg['service_name'] ?? 'Poli Layanan';
        $doctorName = $reg['doctor_name'] ?? 'Dokter Poli';
        $dateFormatted = !empty($reg['registration_date']) ? date('d F Y', strtotime($reg['registration_date'])) : date('d F Y');
        $schedule = !empty($reg['start_time']) 
            ? (substr($reg['start_time'], 0, 5) . ' - ' . substr($reg['end_time'] ?? '12:00:00', 0, 5) . ' WIB')
            : '08:00 - 11:30 WIB';

        $settingModel = new WhatsAppSetting();
        $message = $settingModel->renderTemplate('template_reminder_h1', [
            'nama_pasien'      => $patientName,
            'nomor_antrean'    => $queueNo,
            'nama_layanan'     => $serviceName,
            'tanggal_kunjungan'=> $dateFormatted,
            'jadwal_layanan'   => $schedule,
            'nama_dokter'      => $doctorName,
        ]);

        return self::send(
            $phone,
            $message,
            'reminder_h1',
            'template_reminder_h1',
            $userId,
            "reminder_h1|" . ($reg['registration_code'] ?? $reg['id'])
        );
    }

    /**
     * Event 4B: Pengingat Kunjungan H-2 Jam
     */
    public static function sendReminderH2(array $reg, ?array $user = null): array
    {
        $userId = !empty($reg['user_id']) ? (int)$reg['user_id'] : ($user['id'] ?? null);
        $phone = $user['phone'] ?? $reg['phone'] ?? '';
        $patientName = $reg['patient_name'] ?? $user['name'] ?? 'Pasien';
        $queueNo = '#' . str_pad((string)($reg['queue_number'] ?? '1'), 3, '0', STR_PAD_LEFT);
        $serviceName = $reg['service_name'] ?? 'Poli Layanan';
        $dateFormatted = !empty($reg['registration_date']) ? date('d F Y', strtotime($reg['registration_date'])) : date('d F Y');
        $schedule = !empty($reg['start_time']) 
            ? (substr($reg['start_time'], 0, 5) . ' WIB') 
            : '08:00 WIB';

        $settingModel = new WhatsAppSetting();
        $message = $settingModel->renderTemplate('template_reminder_h2', [
            'nama_pasien'      => $patientName,
            'nomor_antrean'    => $queueNo,
            'nama_layanan'     => $serviceName,
            'tanggal_kunjungan'=> $dateFormatted,
            'jadwal_layanan'   => $schedule,
        ]);

        return self::send(
            $phone,
            $message,
            'reminder_h2',
            'template_reminder_h2',
            $userId,
            "reminder_h2|" . ($reg['registration_code'] ?? $reg['id'])
        );
    }

    /**
     * Event 5: Jadwal Kunjungan Berubah
     */
    public static function sendScheduleChange(
        array $reg,
        string $oldSchedule,
        string $newSchedule,
        string $reason = '',
        ?array $user = null
    ): array {
        $userId = !empty($reg['user_id']) ? (int)$reg['user_id'] : ($user['id'] ?? null);
        $phone = $user['phone'] ?? $reg['phone'] ?? '';
        $patientName = $reg['patient_name'] ?? $user['name'] ?? 'Pasien';
        $serviceName = $reg['service_name'] ?? 'Poli Layanan';
        $alasan = !empty($reason) ? $reason : 'Penyesuaian jam operasional dokter';

        $settingModel = new WhatsAppSetting();
        $message = $settingModel->renderTemplate('template_schedule_change', [
            'nama_pasien'   => $patientName,
            'nama_layanan'  => $serviceName,
            'jadwal_lama'   => $oldSchedule,
            'jadwal_baru'   => $newSchedule,
            'alasan'        => $alasan,
        ]);

        return self::send(
            $phone,
            $message,
            'schedule_change',
            'template_schedule_change',
            $userId,
            "schedule_change|" . ($reg['registration_code'] ?? $reg['id']) . "|{$newSchedule}"
        );
    }

    /**
     * Event 6: Pendaftaran Dibatalkan
     */
    public static function sendCancellation(array $reg, string $notes = '', ?array $user = null): array
    {
        $userId = !empty($reg['user_id']) ? (int)$reg['user_id'] : ($user['id'] ?? null);
        $phone = $user['phone'] ?? $reg['phone'] ?? '';
        $patientName = $reg['patient_name'] ?? $user['name'] ?? 'Pasien';
        $code = $reg['registration_code'] ?? '-';
        $serviceName = $reg['service_name'] ?? 'Poli Layanan';
        $dateFormatted = !empty($reg['registration_date']) ? date('d F Y', strtotime($reg['registration_date'])) : date('d F Y');
        $catatan = !empty($notes) ? $notes : 'Dibatalkan atas permintaan pasien / kendala kuota';

        $settingModel = new WhatsAppSetting();
        $message = $settingModel->renderTemplate('template_cancellation', [
            'nama_pasien'      => $patientName,
            'nama_layanan'     => $serviceName,
            'kode_booking'     => $code,
            'tanggal_kunjungan'=> $dateFormatted,
            'catatan'          => $catatan,
        ]);

        return self::send(
            $phone,
            $message,
            'cancellation',
            'template_cancellation',
            $userId,
            "cancellation|{$code}"
        );
    }

    /**
     * Event 7: Pengumuman Penting dari Admin Puskesmas
     */
    public static function sendAnnouncement(
        int $userId,
        string $phone,
        string $name,
        string $title,
        string $message,
        string $targetUrl = ''
    ): array {
        $settingModel = new WhatsAppSetting();
        $rendered = $settingModel->renderTemplate('template_announcement', [
            'nama_pasien'      => $name,
            'judul_pengumuman' => $title,
            'isi_pengumuman'   => $message,
            'tautan'           => !empty($targetUrl) ? \App\Config\App::baseUrl($targetUrl) : \App\Config\App::baseUrl('/layanan'),
        ]);

        return self::send(
            $phone,
            $rendered,
            'announcement',
            'template_announcement',
            $userId,
            "announcement|{$userId}|" . md5($title)
        );
    }

    /**
     * Send direct test message from Admin panel to verify WhatsApp Gateway connectivity.
     * Independent of automatic event switches and patient registration data.
     * Logs the message with message_type = 'test'.
     */
    public static function sendTestMessage(
        string $phone,
        string $patientName = 'Pasien Uji Coba',
        string $serviceName = 'Poli Layanan Uji Coba',
        string $templateKey = 'test'
    ): array {
        $cleanPhone = self::formatPhone($phone);
        $settingModel = new WhatsAppSetting();
        $now = date('d F Y, H:i');

        if ($templateKey !== 'test' && in_array($templateKey, ['registration', 'status_change', 'reminder_h1', 'reminder_h2', 'schedule_change', 'cancellation', 'announcement'], true)) {
            $message = $settingModel->renderTemplate("template_{$templateKey}", [
                'nama'              => $patientName,
                'nama_pasien'       => $patientName,
                'nomor_pendaftaran' => '#TEST-001',
                'nomor_antrean'     => '#TEST-001',
                'layanan'           => $serviceName,
                'nama_layanan'      => $serviceName,
                'tanggal'           => date('d F Y', strtotime('+1 day')),
                'tanggal_kunjungan' => date('d F Y', strtotime('+1 day')),
                'jam'               => '08:00 - 11:30 WIB',
                'jadwal_layanan'    => '08:00 - 11:30 WIB',
                'status'            => 'Terkonfirmasi (Uji Coba)',
                'status_baru'       => 'Terkonfirmasi (Uji Coba)',
                'nama_puskesmas'    => 'Puskesmas Astanajapura',
                'nama_dokter'       => 'dr. Uji Coba Gateway',
                'catatan'           => 'Pesan uji coba pengiriman Fonnte Gateway.',
                'alasan'            => 'Uji coba penyesuaian jadwal.',
                'judul_pengumuman'  => 'Uji Coba Pengumuman WhatsApp',
                'isi_pengumuman'    => 'Gateway WhatsApp SmartHealthy Fonnte API terhubung.',
                'tautan'            => \App\Config\App::baseUrl('/layanan'),
            ]);
        } else {
            $message = "🏥 *SmartHealthy - Uji Coba WhatsApp Gateway*\n\nHalo, ini adalah pesan uji coba resmi dari sistem pendaftaran SmartHealthy Puskesmas Astanajapura.\n\n✅ *Status Gateway:* Terhubung ke Fonnte API\n📅 *Waktu Kirim:* {$now} WIB\n\nJika Anda menerima pesan ini, gateway WhatsApp resmi telah berfungsi dengan baik dan siap digunakan untuk pengiriman notifikasi pendaftaran & antrean pasien.\n\nSalam sehat,\n*Tim Puskesmas Astanajapura*";
        }

        $summary = "Uji Coba Admin ({$templateKey}) ke {$cleanPhone}";

        // Dispatch test message: messageType is 'test'
        return self::send(
            $cleanPhone,
            $message,
            'test',
            "test_{$templateKey}",
            null,
            $summary
        );
    }
}

