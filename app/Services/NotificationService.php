<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Registration;
use App\Models\HealthService;
use App\Models\ScheduledReminder;
use App\Models\NotificationPreference;
use App\Models\WhatsAppSetting;
use App\Services\FonnteWhatsAppService;
use App\Core\Database;
use App\Config\App;
use PDO;
use Exception;

class NotificationService
{
    /**
     * Helper to get notification model
     */
    protected static function model(): Notification
    {
        return new Notification();
    }

    /**
     * Dispatch multi-channel notification (in-app, email, whatsapp)
     */
    public static function create(int $userId, string $type, string $title, string $message, ?string $targetUrl = null, ?string $dedupKey = null, array $channels = ['app']): int|false
    {
        $notifModel = self::model();

        // 1. In-App Notification (Always Primary)
        $notifId = $notifModel->createNotification([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'target_url' => $targetUrl,
        ], $dedupKey);

        if (!$notifId) {
            // Either failed or was deduplicated
            return false;
        }

        // 2. Fetch User for External Delivery (Email/WhatsApp)
        if (in_array('email', $channels, true) || in_array('whatsapp', $channels, true)) {
            try {
                $userModel = new User();
                $user = $userModel->find($userId);

                if ($user) {
                    $appName = App::name();
                    $puskesmas = App::puskesmas();

                    // Optional Email dispatch
                    if (in_array('email', $channels, true) && !empty($user['email'])) {
                        $emailBody = "Halo {$user['name']},\n\n";
                        $emailBody .= "{$title}\n\n";
                        $emailBody .= "{$message}\n\n";
                        if ($targetUrl) {
                            $emailBody .= "Detail: " . App::baseUrl($targetUrl) . "\n\n";
                        }
                        $emailBody .= "Salam sehat,\n{$puskesmas['name']}";

                        MailService::send($user['email'], "[{$appName}] {$title}", $emailBody, [
                            'notification_id' => $notifId,
                            'type'            => $type,
                        ]);
                    }

                    // Optional WhatsApp dispatch (Enforcing Opt-in Consent and Phone Verification)
                    if (in_array('whatsapp', $channels, true) && !empty($user['phone'])) {
                        $consent = WhatsAppService::validateUserConsent($userId, $type);
                        if ($consent['allowed']) {
                            $waMsg = "*{$appName} - {$puskesmas['name']}*\n\n";
                            $waMsg .= "*{$title}*\n\n";
                            $waMsg .= "{$message}\n\n";
                            if ($targetUrl) {
                                $waMsg .= "Tautan: " . App::baseUrl($targetUrl) . "\n\n";
                            }
                            $waMsg .= "_Pesan resmi sistem layanan Puskesmas Astanajapura_";

                            WhatsAppService::sendMessage($user['phone'], $waMsg, [
                                'notification_id' => $notifId,
                                'type'            => $type,
                                'user_id'         => $userId,
                            ]);
                        } else {
                            error_log("[WA-NOTIFICATION-BLOCKED] User #{$userId} skipped: " . $consent['reason']);
                        }
                    }
                }
            } catch (Exception $e) {
                // External channel errors do not break app transaction
                error_log("External notification channel error: " . $e->getMessage());
            }
        }

        return $notifId;
    }

    // -------------------------------------------------------------
    // A. Notifikasi Pendaftaran (Registration Notifications)
    // -------------------------------------------------------------

    /**
     * Triggered when a new registration is submitted
     */
    public static function notifyRegistrationCreated(array $reg): int|false
    {
        $userId = (int) $reg['user_id'];
        $serviceName = $reg['service_name'] ?? 'Poli Layanan';
        $queueNo = str_pad((string)($reg['queue_number'] ?? '1'), 3, '0', STR_PAD_LEFT);
        $code = $reg['registration_code'] ?? '';
        $dateFormatted = !empty($reg['registration_date']) ? date('d/m/Y', strtotime($reg['registration_date'])) : date('d/m/Y');

        $title = "Pendaftaran Antrean Berhasil Disimpan";
        $message = "Pendaftaran Anda di {$serviceName} untuk tanggal {$dateFormatted} berhasil disimpan dengan Nomor Antrean #{$queueNo}. Kode Booking Anda: {$code}.";
        $targetUrl = "/pendaftaran/tiket/{$code}";
        $dedupKey = "reg_created_" . ($reg['registration_id'] ?? $reg['id'] ?? $code);

        $notifId = self::create($userId, 'registration', $title, $message, $targetUrl, $dedupKey, ['app']);

        // 1. Queue automatic visit reminders (H-1 and H-2 Hours)
        try {
            $schedModel = new ScheduledReminder();
            $schedModel->scheduleForRegistration($reg);
        } catch (\Throwable $e) {
            error_log("Failed to schedule reminders: " . $e->getMessage());
        }

        // 2. Dispatch Automated WhatsApp via Fonnte API
        try {
            FonnteWhatsAppService::sendRegistrationSuccess($reg);
        } catch (\Throwable $e) {
            error_log("Fonnte WhatsApp Registration dispatch error: " . $e->getMessage());
        }

        return $notifId;
    }

    /**
     * Triggered when admin updates registration status (Confirmed, Completed, Cancelled)
     */
    public static function notifyRegistrationStatusChanged(array $reg, string $newStatus, ?string $notes = null): int|false
    {
        $userId = (int) $reg['user_id'];
        $serviceName = $reg['service_name'] ?? 'Poli Layanan';
        $queueNo = str_pad((string)($reg['queue_number'] ?? '1'), 3, '0', STR_PAD_LEFT);
        $code = $reg['registration_code'] ?? '';
        $dateFormatted = !empty($reg['registration_date']) ? date('d/m/Y', strtotime($reg['registration_date'])) : date('d/m/Y');

        switch ($newStatus) {
            case 'Confirmed':
                $title = "Pendaftaran Antrean Dikonfirmasi";
                $message = "Pendaftaran Anda di {$serviceName} (#{$queueNo}) untuk tanggal {$dateFormatted} telah DIKONFIRMASI oleh petugas. Silakan hadir di ruang tunggu poli 15 menit sebelum layanan.";
                $targetUrl = "/pendaftaran/tiket/{$code}";
                break;

            case 'Completed':
                $title = "Pemeriksaan Selesai";
                $message = "Pemeriksaan Anda di {$serviceName} telah selesai. Terima kasih atas kunjungan Anda di Puskesmas Astanajapura. Simpan bukti e-Ticket untuk arsip kesehatan Anda.";
                $targetUrl = "/pendaftaran/tiket/{$code}";
                break;

            case 'Cancelled':
                $title = "Pendaftaran Antrean Dibatalkan";
                $message = "Pendaftaran antrean Anda di {$serviceName} pada tanggal {$dateFormatted} telah DIBATALKAN." . (!empty($notes) ? " Catatan: {$notes}" : "");
                $targetUrl = "/pendaftaran/riwayat?status=Cancelled";
                break;

            default:
                $title = "Status Pendaftaran Diperbarui: {$newStatus}";
                $message = "Status pendaftaran antrean Anda di {$serviceName} telah diperbarui menjadi {$newStatus}.";
                $targetUrl = "/pendaftaran/tiket/{$code}";
                break;
        }

        $dedupKey = "reg_status_" . ($reg['id'] ?? $code) . "_" . strtolower($newStatus);
        $notifId = self::create($userId, 'registration', $title, $message, $targetUrl, $dedupKey, ['app']);

        // Automated WhatsApp via Fonnte based on event
        if ($newStatus === 'Cancelled') {
            // Cancel pending reminders
            try {
                $schedModel = new ScheduledReminder();
                $schedModel->cancelForRegistration((int)($reg['id'] ?? 0), $notes ?: 'Status diubah ke Cancelled');
            } catch (\Throwable $e) {}

            try {
                FonnteWhatsAppService::sendCancellation($reg, $notes ?: 'Status diubah menjadi Dibatalkan oleh petugas');
            } catch (\Throwable $e) {
                error_log("Fonnte WhatsApp Cancellation dispatch error: " . $e->getMessage());
            }
        } else {
            try {
                FonnteWhatsAppService::sendStatusChange($reg, $newStatus, $notes);
            } catch (\Throwable $e) {
                error_log("Fonnte WhatsApp Status dispatch error: " . $e->getMessage());
            }
        }

        return $notifId;
    }

    /**
     * Triggered when patient cancels their own registration
     */
    public static function notifyRegistrationCancelled(array $reg, string $by = 'user', ?string $notes = null): int|false
    {
        $userId = (int) $reg['user_id'];
        $serviceName = $reg['service_name'] ?? 'Poli Layanan';
        $code = $reg['registration_code'] ?? '';
        $dateFormatted = !empty($reg['registration_date']) ? date('d/m/Y', strtotime($reg['registration_date'])) : date('d/m/Y');

        $title = "Pembatalan Pendaftaran Berhasil";
        $message = "Pendaftaran antrean Anda di {$serviceName} pada tanggal {$dateFormatted} (Kode: {$code}) telah dibatalkan atas permintaan {$by}. Kuota telah dikembalikan.";
        $targetUrl = "/pendaftaran/riwayat?status=Cancelled";
        $dedupKey = "reg_user_cancel_" . ($reg['id'] ?? $code);

        $notifId = self::create($userId, 'registration', $title, $message, $targetUrl, $dedupKey, ['app']);

        // Cancel scheduled reminders in queue
        try {
            $schedModel = new ScheduledReminder();
            $schedModel->cancelForRegistration((int)($reg['id'] ?? 0), "Dibatalkan oleh {$by}");
        } catch (\Throwable $e) {}

        // Dispatch Fonnte WhatsApp Cancellation
        try {
            FonnteWhatsAppService::sendCancellation($reg, $notes ?: "Dibatalkan oleh {$by}");
        } catch (\Throwable $e) {
            error_log("Fonnte WhatsApp Cancellation dispatch error: " . $e->getMessage());
        }

        return $notifId;
    }

    // -------------------------------------------------------------
    // B. Notifikasi Jadwal & Pengingat Kunjungan (Schedule & Reminders)
    // -------------------------------------------------------------

    /**
     * Triggered by Scheduler/Cron for visit reminder
     */
    public static function notifyScheduleReminder(array $reg, string $reminderType = 'H-1'): int|false
    {
        $userId = (int) $reg['user_id'];
        $serviceName = $reg['service_name'] ?? 'Poli Layanan';
        $queueNo = str_pad((string)($reg['queue_number'] ?? '1'), 3, '0', STR_PAD_LEFT);
        $code = $reg['registration_code'] ?? '';
        $patientName = $reg['patient_name'] ?? 'Pasien';
        $regDate = $reg['registration_date'] ?? date('Y-m-d');
        $dateFormatted = date('d F Y', strtotime($regDate));

        if ($reminderType === 'H-2_HOURS') {
            $title = "Pengingat Kunjungan: 2 Jam Lagi ({$serviceName})";
            $message = "Halo {$patientName}, pendaftaran antrean Anda di {$serviceName} (#{$queueNo}) dijadwalkan dalam 2 jam ke depan. Harap tiba 15 menit sebelum layanan.";
        } else {
            $title = "Pengingat Kunjungan Besok: {$serviceName}";
            $message = "Halo {$patientName}, besok ({$dateFormatted}) adalah jadwal kunjungan Anda di {$serviceName} dengan Nomor Antrean #{$queueNo}. Persiapkan e-Ticket dan identitas Anda.";
        }

        $targetUrl = "/pendaftaran/tiket/{$code}";
        $dedupKey = "reg_reminder_" . ($reg['id'] ?? $code) . "_{$reminderType}_{$regDate}";

        $notifId = self::create($userId, 'schedule', $title, $message, $targetUrl, $dedupKey, ['app']);

        // Dispatch via Fonnte WhatsApp
        try {
            if ($reminderType === 'H-2_HOURS') {
                FonnteWhatsAppService::sendReminderH2($reg);
            } else {
                FonnteWhatsAppService::sendReminderH1($reg);
            }
        } catch (\Throwable $e) {
            error_log("Fonnte WhatsApp Reminder dispatch error: " . $e->getMessage());
        }

        return $notifId;
    }

    /**
     * Triggered when admin updates clinic operational schedule
     */
    public static function notifyScheduleChanged(int $serviceId, array $scheduleData, string $reason = ''): int
    {
        $db = Database::getConnection();
        
        // Find users with active bookings in this service for future dates
        $sql = "SELECT DISTINCT r.id as reg_id, r.user_id, r.registration_code, r.registration_date, 
                                hs.name as service_name, hs.doctor_name, u.name as patient_name, u.phone as patient_phone
                FROM `registrations` r 
                JOIN `health_services` hs ON r.health_service_id = hs.id 
                JOIN `users` u ON r.user_id = u.id
                WHERE r.health_service_id = :sid 
                  AND r.registration_date >= CURRENT_DATE() 
                  AND r.status IN ('Pending', 'Confirmed')";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['sid' => $serviceId]);
        $affectedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sentCount = 0;
        $schedModel = new ScheduledReminder();

        foreach ($affectedUsers as $u) {
            $title = "Pemberitahuan Perubahan Jam Layanan: {$u['service_name']}";
            $newSchedule = substr($scheduleData['start_time'], 0, 5) . " - " . substr($scheduleData['end_time'], 0, 5) . " WIB";
            $message = "Terdapat penyesuaian jadwal operasional pada {$u['service_name']} untuk hari {$scheduleData['day_of_week']}. Jam baru: {$newSchedule}." . 
                       (!empty($reason) ? " Alasan: {$reason}" : "");
            
            $targetUrl = "/pendaftaran/tiket/{$u['registration_code']}";
            $dedupKey = "sched_changed_{$serviceId}_" . date('Ymd') . "_{$u['user_id']}";

            if (self::create((int)$u['user_id'], 'schedule', $title, $message, $targetUrl, $dedupKey, ['app'])) {
                $sentCount++;

                // Reschedule queued reminders
                try {
                    $schedModel->rescheduleForRegistration((int)$u['reg_id'], $u['registration_date'], $scheduleData['start_time']);
                } catch (\Throwable $e) {}

                // Dispatch WhatsApp notification
                try {
                    FonnteWhatsAppService::sendScheduleChange(
                        [
                            'user_id'           => $u['user_id'],
                            'phone'             => $u['patient_phone'],
                            'patient_name'      => $u['patient_name'],
                            'service_name'      => $u['service_name'],
                            'registration_code' => $u['registration_code'],
                        ],
                        'Jadwal sebelumnya',
                        $newSchedule,
                        $reason
                    );
                } catch (\Throwable $e) {
                    error_log("Fonnte WhatsApp Schedule Changed dispatch error: " . $e->getMessage());
                }
            }
        }

        return $sentCount;
    }

    // -------------------------------------------------------------
    // C. Notifikasi Akun (Account & Security Notifications)
    // -------------------------------------------------------------

    /**
     * Triggered on user account creation
     */
    public static function notifyAccountRegistered(int $userId, string $name): int|false
    {
        $title = "Selamat Datang di Portal SmartHealthy";
        $message = "Halo {$name}, akun Anda telah berhasil didaftarkan di sistem antrean Puskesmas Astanajapura. Silakan verifikasi WhatsApp OTP atau Email untuk akses layanan kesehatan menyeluruh.";
        $targetUrl = "/verifikasi";
        $dedupKey = "account_reg_{$userId}";

        return self::create($userId, 'account', $title, $message, $targetUrl, $dedupKey, ['app']);
    }

    /**
     * Triggered on successful email verification
     */
    public static function notifyEmailVerified(int $userId): int|false
    {
        $title = "Alamat Email Berhasil Diverifikasi";
        $message = "Alamat email akun SmartHealthy Anda telah berhasil diverifikasi. Akun Anda kini memiliki tingkat keamanan lebih tinggi.";
        $targetUrl = "/profil";
        $dedupKey = "email_verified_{$userId}";

        return self::create($userId, 'account', $title, $message, $targetUrl, $dedupKey, ['app']);
    }

    /**
     * Triggered on successful WhatsApp OTP verification
     */
    public static function notifyPhoneVerified(int $userId): int|false
    {
        $title = "Nomor WhatsApp Berhasil Diverifikasi";
        $message = "Nomor WhatsApp Anda telah terverifikasi secara resmi untuk menerima tiket digital dan pengingat antrean Puskesmas Astanajapura.";
        $targetUrl = "/profil";
        $dedupKey = "phone_verified_{$userId}";

        return self::create($userId, 'account', $title, $message, $targetUrl, $dedupKey, ['app']);
    }

    /**
     * Triggered on password change / reset
     */
    public static function notifyPasswordChanged(int $userId): int|false
    {
        $title = "Keamanan Akun: Kata Sandi Diperbarui";
        $message = "Kata sandi akun SmartHealthy Anda baru saja berhasil diperbarui. Jika Anda merasa tidak melakukan perubahan ini, segera hubungi petugas Puskesmas.";
        $targetUrl = "/profil";
        $dedupKey = "pwd_changed_{$userId}_" . date('YmdHis');

        return self::create($userId, 'account', $title, $message, $targetUrl, $dedupKey, ['app', 'email']);
    }

    // -------------------------------------------------------------
    // D. Notifikasi Edukasi (Health Education & Quiz)
    // -------------------------------------------------------------

    /**
     * Triggered when new health education article is published
     */
    public static function notifyNewArticle(array $article): int
    {
        $articleId = (int) $article['id'];
        $title = "Materi Edukasi Kesehatan Baru: " . mb_substr($article['title'], 0, 60);
        $message = "Puskesmas Astanajapura menerbitkan artikel edukasi baru mengenai " . ($article['category_name'] ?? 'kesehatan keluarga') . ": \"{$article['title']}\". Baca sekarang untuk tips hidup sehat.";
        $targetUrl = "/artikel/{$article['slug']}";
        $dedupKeyBase = "new_article_{$articleId}_";

        $db = Database::getConnection();
        // Target active patients (limit to 100 most active users to avoid spamming)
        $users = $db->query("SELECT id FROM users WHERE role = 'user' AND is_active = 1 ORDER BY id DESC LIMIT 100")->fetchAll(PDO::FETCH_COLUMN);

        $sentCount = 0;
        foreach ($users as $uid) {
            if (self::create((int)$uid, 'education', $title, $message, $targetUrl, $dedupKeyBase . $uid, ['app'])) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Triggered when a new quiz is published
     */
    public static function notifyNewQuiz(string $topic = 'PHBS'): int
    {
        $title = "Kuis Kesehatan Baru Tersedia";
        $message = "Uji wawasan Anda seputar Pola Hidup Bersih dan Sehat ({$topic}) melalui Kuis Interaktif Puskesmas Astanajapura.";
        $targetUrl = "/kuis";
        $todayStr = date('Ymd');
        $dedupKeyBase = "new_quiz_{$todayStr}_";

        $db = Database::getConnection();
        $users = $db->query("SELECT id FROM users WHERE role = 'user' AND is_active = 1 ORDER BY id DESC LIMIT 100")->fetchAll(PDO::FETCH_COLUMN);

        $sentCount = 0;
        foreach ($users as $uid) {
            if (self::create((int)$uid, 'education', $title, $message, $targetUrl, $dedupKeyBase . $uid, ['app'])) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    // -------------------------------------------------------------
    // E. Notifikasi Pengumuman Admin (Admin Announcements)
    // -------------------------------------------------------------

    /**
     * Send targeted or broadcast announcement from admin
     * $targetType: 'all', 'village', 'service'
     */
    public static function sendAnnouncement(string $title, string $message, string $targetType = 'all', ?int $targetId = null, ?string $targetUrl = '/layanan'): int
    {
        $db = Database::getConnection();
        $userIds = [];

        if ($targetType === 'village' && $targetId) {
            $stmt = $db->prepare("SELECT id FROM users WHERE village_id = :vid AND role = 'user' AND is_active = 1");
            $stmt->execute(['vid' => $targetId]);
            $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } elseif ($targetType === 'service' && $targetId) {
            $stmt = $db->prepare("SELECT DISTINCT user_id FROM registrations WHERE health_service_id = :sid AND registration_date >= CURRENT_DATE()");
            $stmt->execute(['sid' => $targetId]);
            $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            // Broadcast to all active users
            $userIds = $db->query("SELECT id FROM users WHERE role = 'user' AND is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
        }

        $sentCount = 0;
        $batchId = uniqid('ann_');

        foreach ($userIds as $uid) {
            $dedupKey = "announcement_{$batchId}_{$uid}";
            if (self::create((int)$uid, 'announcement', $title, $message, $targetUrl, $dedupKey, ['app'])) {
                $sentCount++;

                // Automated WhatsApp Broadcast via Fonnte API if user consented & phone verified
                try {
                    $uStmt = $db->prepare("SELECT name, phone, phone_verified_at FROM users WHERE id = :id LIMIT 1");
                    $uStmt->execute(['id' => $uid]);
                    $uData = $uStmt->fetch(PDO::FETCH_ASSOC);

                    if ($uData && !empty($uData['phone'])) {
                        FonnteWhatsAppService::sendAnnouncement(
                            (int)$uid,
                            $uData['phone'],
                            $uData['name'],
                            $title,
                            $message,
                            $targetUrl
                        );
                    }
                } catch (\Throwable $e) {
                    error_log("Fonnte announcement error: " . $e->getMessage());
                }
            }
        }

        return $sentCount;
    }

    // -------------------------------------------------------------
    // Automated Scheduler Engine
    // -------------------------------------------------------------

    /**
     * Run appointment reminders queue (H-1 day and H-2 hours before service)
     */
    public static function runScheduledReminders(): array
    {
        $schedModel = new ScheduledReminder();
        $dueReminders = $schedModel->getDueReminders(50);

        $sentCount = 0;
        $failedCount = 0;
        $skippedCount = 0;
        $details = [];

        foreach ($dueReminders as $r) {
            $rtype = $r['reminder_type'];
            $res = null;

            if ($rtype === 'H-1') {
                $res = FonnteWhatsAppService::sendReminderH1($r);
            } elseif ($rtype === 'H-2_HOURS') {
                $res = FonnteWhatsAppService::sendReminderH2($r);
            }

            if ($res && !empty($res['status'])) {
                if (($res['delivery_status'] ?? '') === 'skipped') {
                    $skippedCount++;
                    $schedModel->markSent((int)$r['id']);
                } else {
                    $sentCount++;
                    $schedModel->markSent((int)$r['id']);
                }
                $details[] = [
                    'id'     => $r['id'],
                    'type'   => $rtype,
                    'status' => 'success',
                    'phone'  => $r['recipient_number_masked'] ?? $r['patient_phone'],
                ];
            } else {
                $failedCount++;
                $reason = $res['reason'] ?? 'Gagal mengirim pengingat via Fonnte';
                $schedModel->markFailed((int)$r['id'], $reason);
                $details[] = [
                    'id'     => $r['id'],
                    'type'   => $rtype,
                    'status' => 'failed',
                    'reason' => $reason,
                ];
            }
        }

        return [
            'total_due' => count($dueReminders),
            'sent'      => $sentCount,
            'failed'    => $failedCount,
            'skipped'   => $skippedCount,
            'details'   => $details,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }
}
