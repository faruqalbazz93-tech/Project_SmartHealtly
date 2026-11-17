<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppSetting;
use App\Models\ScheduledReminder;
use App\Services\FonnteWhatsAppService;
use App\Services\NotificationService;
use App\Config\App;

class WhatsAppAdminController extends Controller
{
    public function __construct()
    {
        Auth::requireAdmin();
    }

    /**
     * Display WhatsApp Fonnte Management Dashboard for Admin
     */
    public function index(): void
    {
        $settingModel = new WhatsAppSetting();
        $settings = $settingModel->getAll();

        $connectionStatus = FonnteWhatsAppService::getConnectionStatus();

        $logModel = new WhatsAppMessageLog();
        $stats = $logModel->getDeliveryStats();

        // Filters & Pagination
        $statusFilter = $this->input('status');
        $typeFilter = $this->input('type');
        $dateFilter = $this->input('date');
        $page = max(1, (int) $this->input('page', 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $totalLogs = $logModel->countForAdmin($statusFilter, $typeFilter, $dateFilter);
        $totalPages = max(1, (int) ceil($totalLogs / $limit));
        $logs = $logModel->getLatestForAdmin($limit, $offset, $statusFilter, $typeFilter, $dateFilter);

        // Reminder queue statistics
        $db = \App\Core\Database::getConnection();
        $schedStats = $db->query("
            SELECT 
                COUNT(*) as total_queue,
                COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending_count,
                COALESCE(SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END), 0) as sent_count,
                COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) as cancelled_count
            FROM `scheduled_reminders`
        ")->fetch(\PDO::FETCH_ASSOC) ?: ['total_queue' => 0, 'pending_count' => 0, 'sent_count' => 0, 'cancelled_count' => 0];

        $this->render('admin/whatsapp_dashboard', [
            'pageTitle'        => 'WhatsApp Gateway - Fonnte API - Admin SmartHealthy',
            'connectionStatus' => $connectionStatus,
            'settings'         => $settings,
            'stats'            => $stats,
            'schedStats'       => $schedStats,
            'logs'             => $logs,
            'page'             => $page,
            'totalPages'       => $totalPages,
            'totalLogs'        => $totalLogs,
            'statusFilter'     => $statusFilter,
            'typeFilter'       => $typeFilter,
            'dateFilter'       => $dateFilter,
        ], 'layouts/admin');
    }

    /**
     * Test connection to Fonnte API and get device information
     */
    public function testConnection(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $inputToken = trim((string) $this->input('fonnte_token', ''));
        $override = (!empty($inputToken) && !str_contains($inputToken, '***')) ? $inputToken : null;

        $status = FonnteWhatsAppService::getConnectionStatus($override);

        $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') 
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

        if ($isAjax) {
            $this->json([
                'success' => $status['connected'] ?? false,
                'data'    => $status,
            ]);
            return;
        }

        if ($status['status'] === 'connected') {
            $device = htmlspecialchars($status['device_name'] . ' (' . ($status['device_number'] ?? '-') . ')');
            $this->flashAndRedirect('success', "<strong>Koneksi Berhasil!</strong> Gateway Fonnte terhubung dengan perangkat <code>{$device}</code>. Status: <strong>{$status['device_status']}</strong>.", '/admin/pengaturan/whatsapp');
        } elseif ($status['status'] === 'unconfigured') {
            $this->flashAndRedirect('warning', '<strong>API Token Belum Dikonfigurasi:</strong> Masukkan API Token Fonnte terlebih dahulu untuk menguji koneksi.', '/admin/pengaturan/whatsapp');
        } else {
            $reason = htmlspecialchars($status['reason'] ?? 'Gagal menghubungi server Fonnte.');
            $this->flashAndRedirect('danger', "<strong>Koneksi Gagal:</strong> {$reason}", '/admin/pengaturan/whatsapp');
        }
    }

    /**
     * Save / update all WhatsApp Fonnte configurations & templates
     */
    public function saveSettings(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $settingModel = new WhatsAppSetting();

        $tokenInput = trim((string) $this->input('fonnte_token', ''));

        $updates = [
            'whatsapp_enabled'               => $this->input('whatsapp_enabled') ? '1' : '0',
            'notify_registration_enabled'    => $this->input('notify_registration_enabled') ? '1' : '0',
            'notify_ticket_enabled'          => $this->input('notify_ticket_enabled') ? '1' : '0',
            'notify_status_change_enabled'   => $this->input('notify_status_change_enabled') ? '1' : '0',
            'notify_reminder_h1_enabled'     => $this->input('notify_reminder_h1_enabled') ? '1' : '0',
            'notify_reminder_h2_enabled'     => $this->input('notify_reminder_h2_enabled') ? '1' : '0',
            'notify_schedule_change_enabled' => $this->input('notify_schedule_change_enabled') ? '1' : '0',
            'notify_cancellation_enabled'    => $this->input('notify_cancellation_enabled') ? '1' : '0',
            'notify_announcement_enabled'    => $this->input('notify_announcement_enabled') ? '1' : '0',
            'reminder_h1_time'               => trim((string)$this->input('reminder_h1_time', '08:00')),
            'reminder_h2_offset_hours'       => max(1, (int)$this->input('reminder_h2_offset_hours', 2)),
            'timezone'                       => trim((string)$this->input('timezone', 'Asia/Jakarta')),

            // Dynamic Templates
            'template_registration'          => trim((string)$this->input('template_registration')),
            'template_status_change'         => trim((string)$this->input('template_status_change')),
            'template_reminder_h1'           => trim((string)$this->input('template_reminder_h1')),
            'template_reminder_h2'           => trim((string)$this->input('template_reminder_h2')),
            'template_schedule_change'       => trim((string)$this->input('template_schedule_change')),
            'template_cancellation'          => trim((string)$this->input('template_cancellation')),
            'template_announcement'          => trim((string)$this->input('template_announcement')),
        ];

        // Only update token if not left blank (or if explicitly changed)
        $cleanToken = preg_replace('/\s+/', '', $tokenInput);
        if (!empty($cleanToken) && !str_contains($cleanToken, '***')) {
            $updates['fonnte_token'] = $cleanToken;
        }

        $settingModel->setMultiple($updates);

        $this->flashAndRedirect('success', 'Konfigurasi WhatsApp Gateway Fonnte dan template pesan berhasil disimpan.', '/admin/pengaturan/whatsapp');
    }

    /**
     * Send test WhatsApp message from Admin panel to verify connectivity.
     * Fully independent of automatic event switches and user registration data.
     */
    public function testSend(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $phone = trim((string) $this->input('phone'));
        $eventType = trim((string) $this->input('event_type', 'test'));
        $patientName = trim((string) $this->input('patient_name', 'Pasien Uji Coba'));
        $serviceName = trim((string) $this->input('service_name', 'Poli Layanan Uji Coba'));

        if (empty($phone)) {
            $this->flashAndRedirect('danger', 'Nomor WhatsApp tujuan wajib diisi.', '/admin/pengaturan/whatsapp');
            return;
        }

        // Send test message directly without touching registration data or automatic event switches
        $result = FonnteWhatsAppService::sendTestMessage($phone, $patientName, $serviceName, $eventType);

        if (!empty($result['status'])) {
            $modeLabel = ($result['mode'] ?? '') === 'fonnte' ? 'Fonnte Gateway Live' : 'Simulasi Lokal';
            $msgId = htmlspecialchars((string)($result['message_id'] ?? '-'));
            $this->flashAndRedirect(
                'success',
                "<strong>Uji Coba Pengiriman Berhasil ({$modeLabel})!</strong><br>Message ID: <code>{$msgId}</code> ke nomor " . htmlspecialchars($phone),
                '/admin/pengaturan/whatsapp'
            );
        } else {
            $reason = htmlspecialchars((string)($result['reason'] ?? 'Gagal mengirim pesan uji coba'));
            $this->flashAndRedirect(
                'danger',
                "<strong>Gagal Mengirim Pesan Uji Coba:</strong> {$reason}",
                '/admin/pengaturan/whatsapp'
            );
        }
    }

    /**
     * Manually trigger the scheduled visit reminder queue
     */
    public function triggerReminders(): void
    {
        $result = NotificationService::runScheduledReminders();
        $processed = $result['total_due'] ?? 0;
        $sent = $result['sent'] ?? 0;
        $skipped = $result['skipped'] ?? 0;
        $failed = $result['failed'] ?? 0;

        $msg = "<strong>Eksekusi Scheduler Pengingat Selesai:</strong> {$processed} antrean diperiksa &bull; <strong>{$sent}</strong> terkirim &bull; {$skipped} diabaikan (duplikat) &bull; {$failed} gagal.";
        $this->flashAndRedirect('info', $msg, '/admin/pengaturan/whatsapp');
    }
}
