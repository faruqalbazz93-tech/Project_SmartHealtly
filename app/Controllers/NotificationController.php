<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Notification;
use App\Models\Village;
use App\Models\HealthService;
use App\Services\NotificationService;
use App\Config\App;
use App\Models\NotificationPreference;
use App\Models\WhatsAppMessageLog;
use App\Models\User;
use App\Services\WhatsAppService;

class NotificationController extends Controller
{
    /**
     * Notification Center Index Page
     */
    public function index(): void
    {
        Auth::requireAuth();

        $userId = (int) Auth::id();
        $filter = (string) $this->input('filter', 'all');
        $validFilters = ['all', 'unread', 'registration', 'schedule', 'account', 'education', 'announcement'];
        if (!in_array($filter, $validFilters, true)) {
            $filter = 'all';
        }

        $page = max(1, (int) $this->input('page', 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $notifModel = new Notification();
        $notifications = $notifModel->getForUser($userId, $filter, $limit, $offset);
        $totalItems = $notifModel->countForUser($userId, $filter);
        $totalPages = max(1, (int) ceil($totalItems / $limit));
        $counts = $notifModel->getCountsByType($userId);

        $this->render('notification/index', [
            'pageTitle'     => 'Pusat Notifikasi - SmartHealthy',
            'notifications' => $notifications,
            'filter'        => $filter,
            'page'          => $page,
            'totalPages'    => $totalPages,
            'totalItems'    => $totalItems,
            'counts'        => $counts,
        ], Auth::isAdmin() ? 'layouts/admin' : 'layouts/user');
    }

    /**
     * AJAX endpoint to fetch unread count & recent preview for Navbar Bell
     */
    public function summaryAjax(): void
    {
        if (!Auth::check()) {
            $this->json([
                'logged_in'    => false,
                'unread_count' => 0,
                'latest'       => [],
            ]);
            return;
        }

        $userId = (int) Auth::id();
        $notifModel = new Notification();

        $unreadCount = $notifModel->countUnread($userId);
        $latest = $notifModel->getRecentForDropdown($userId, 5);

        // Format relative time and icons for each notification
        $formatted = array_map(function ($item) {
            return [
                'id'          => $item['id'],
                'type'        => $item['type'],
                'title'       => htmlspecialchars((string)$item['title']),
                'message'     => htmlspecialchars(mb_strimwidth((string)$item['message'], 0, 75, '...')),
                'target_url'  => $item['target_url'] ? App::baseUrl($item['target_url']) : null,
                'is_read'     => (bool) $item['is_read'],
                'time_ago'    => $this->timeAgo($item['created_at']),
                'icon'        => $this->getTypeIcon($item['type']),
                'badge_color' => $this->getTypeBadgeColor($item['type']),
            ];
        }, $latest);

        $this->json([
            'logged_in'    => true,
            'unread_count' => $unreadCount,
            'latest'       => $formatted,
        ]);
    }

    /**
     * Mark single notification as read and optionally redirect to target_url
     */
    public function markRead(int $id): void
    {
        Auth::requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $userId = (int) Auth::id();
        $notifModel = new Notification();
        
        $notif = $notifModel->find($id);
        if ($notif && (int)$notif['user_id'] === $userId) {
            $notifModel->markAsRead($id, $userId);
        }

        $redirectUrl = $this->input('redirect_url');
        if (!empty($redirectUrl)) {
            $this->redirect($redirectUrl);
            return;
        }

        $this->redirect('/notifikasi');
    }

    /**
     * Mark all unread notifications as read
     */
    public function markAllRead(): void
    {
        Auth::requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $userId = (int) Auth::id();
        $notifModel = new Notification();
        $notifModel->markAllAsRead($userId);

        $this->flashAndRedirect('success', 'Semua notifikasi telah ditandai sebagai sudah dibaca.', '/notifikasi');
    }

    /**
     * AJAX endpoint to mark a single notification as read
     */
    public function markReadAjax(int $id): void
    {
        if (!Auth::check()) {
            $this->json(['status' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $userId = (int) Auth::id();
        $notifModel = new Notification();
        $notif = $notifModel->find($id);

        if ($notif && (int)$notif['user_id'] === $userId) {
            $notifModel->markAsRead($id, $userId);
            $unreadCount = $notifModel->countUnread($userId);
            $this->json([
                'status'       => true,
                'message'      => 'Notifikasi berhasil ditandai dibaca.',
                'unread_count' => $unreadCount,
            ]);
            return;
        }

        $this->json(['status' => false, 'message' => 'Notifikasi tidak ditemukan atau akses ditolak.'], 403);
    }

    /**
     * AJAX endpoint to mark all unread notifications as read
     */
    public function markAllReadAjax(): void
    {
        if (!Auth::check()) {
            $this->json(['status' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $userId = (int) Auth::id();
        $notifModel = new Notification();
        $notifModel->markAllAsRead($userId);

        $this->json([
            'status'       => true,
            'message'      => 'Semua notifikasi berhasil ditandai sebagai dibaca.',
            'unread_count' => 0,
        ]);
    }

    /**
     * Web runner endpoint for Visit Reminders Scheduler
     */
    public function runRemindersCron(): void
    {
        $token = $this->input('token');
        $expectedToken = \App\Core\Env::get('CRON_SECRET_TOKEN', 'smarthealthy-cron-2026');

        if (!Auth::isAdmin() && $token !== $expectedToken) {
            $this->json([
                'status'  => false,
                'message' => 'Akses ditolak. Gunakan parameter ?token= yang valid atau masuk sebagai admin.',
            ], 403);
            return;
        }

        $result = NotificationService::runScheduledReminders();

        $this->json([
            'status'  => true,
            'message' => 'Scheduler pengingat kunjungan berhasil dijalankan.',
            'data'    => $result,
        ]);
    }

    /**
     * Delete a single notification
     */
    public function delete(int $id): void
    {
        Auth::requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $userId = (int) Auth::id();
        $notifModel = new Notification();
        $notifModel->deleteForUser($id, $userId);

        $this->flashAndRedirect('success', 'Notifikasi berhasil dihapus.', '/notifikasi');
    }

    /**
     * Delete all read notifications for user
     */
    public function deleteAllRead(): void
    {
        Auth::requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $userId = (int) Auth::id();
        $notifModel = new Notification();
        $notifModel->deleteAllReadForUser($userId);

        $this->flashAndRedirect('success', 'Semua notifikasi yang sudah dibaca berhasil dibersihkan.', '/notifikasi');
    }

    // -------------------------------------------------------------
    // Admin Announcement Actions
    // -------------------------------------------------------------

    /**
     * Admin view: Compose & send announcement
     */
    public function announcementForm(): void
    {
        Auth::requireAdmin();

        $villageModel = new Village();
        $serviceModel = new HealthService();

        $villages = $villageModel->all('name ASC');
        $services = $serviceModel->getActiveServices();

        $this->render('admin/announcement', [
            'pageTitle' => 'Kirim Pengumuman Notifikasi - Admin SmartHealthy',
            'villages'  => $villages,
            'services'  => $services,
        ], 'layouts/admin');
    }

    /**
     * Admin action: Broadcast or target announcement
     */
    public function sendAnnouncement(): void
    {
        Auth::requireAdmin();

        if (!$this->validateCsrf()) {
            return;
        }

        $title = trim((string) $this->input('title'));
        $message = trim((string) $this->input('message'));
        $targetType = $this->input('target_type', 'all');
        $targetId = $this->input('target_id') ? (int) $this->input('target_id') : null;
        $targetUrl = trim((string) $this->input('target_url', '/layanan'));

        if (empty($title) || empty($message)) {
            $this->flashAndRedirect('danger', 'Judul dan isi pengumuman tidak boleh kosong.', '/admin/pengumuman');
            return;
        }

        $sentCount = NotificationService::sendAnnouncement($title, $message, $targetType, $targetId, $targetUrl);

        $this->flashAndRedirect('success', "Pengumuman berhasil dikirimkan kepada <strong>{$sentCount}</strong> pengguna.", '/admin/pengumuman');
    }

    // -------------------------------------------------------------
    // Helper Formatters
    // -------------------------------------------------------------

    private function timeAgo(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Baru saja';
        } elseif ($diff < 3600) {
            $min = max(1, (int) floor($diff / 60));
            return "{$min} mnt lalu";
        } elseif ($diff < 86400) {
            $hour = (int) floor($diff / 3600);
            return "{$hour} jam lalu";
        } elseif ($diff < 604800) {
            $day = (int) floor($diff / 86400);
            return "{$day} hari lalu";
        } else {
            return date('d/m/Y', $timestamp);
        }
    }

    private function getTypeIcon(string $type): string
    {
        return match ($type) {
            'registration' => 'bi-ticket-perforated-fill',
            'schedule'     => 'bi-calendar-event-fill',
            'account'      => 'bi-shield-check',
            'education'    => 'bi-journal-medical',
            'announcement' => 'bi-megaphone-fill',
            default        => 'bi-bell-fill'
        };
    }

    private function getTypeBadgeColor(string $type): string
    {
        return match ($type) {
            'registration' => 'primary',
            'schedule'     => 'warning',
            'account'      => 'info',
            'education'    => 'success',
            'announcement' => 'danger',
            default        => 'secondary'
        };
    }

    // =========================================================================
    // USER WHATSAPP NOTIFICATION PREFERENCES & EXPLICIT CONSENT
    // =========================================================================

    /**
     * User view: WhatsApp notification settings and preferences
     */
    public function preferences(): void
    {
        Auth::requireAuth();

        $userId = (int) Auth::id();
        $userModel = new User();
        $user = $userModel->find($userId);

        $prefModel = new NotificationPreference();
        $preference = $prefModel->getForUser($userId);

        $logModel = new WhatsAppMessageLog();
        $logs = $logModel->getLogsForUser($userId, 10);

        $this->render('user/notification_preferences', [
            'pageTitle'  => 'Pengaturan Notifikasi WhatsApp - SmartHealthy',
            'user'       => $user,
            'preference' => $preference,
            'logs'       => $logs,
        ], Auth::isAdmin() ? 'layouts/admin' : 'layouts/user');
    }

    /**
     * Toggle WhatsApp Consent (Explicit Opt-in / Opt-out)
     */
    public function toggleConsent(): void
    {
        Auth::requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $userId = (int) Auth::id();
        $action = $this->input('action', 'enable');

        $userModel = new User();
        $user = $userModel->find($userId);

        $prefModel = new NotificationPreference();

        if ($action === 'enable') {
            // Require phone verification before consent can be activated
            if (empty($user['phone_verified_at'])) {
                $this->flashAndRedirect('warning', 'Nomor WhatsApp Anda belum diverifikasi. Silakan lakukan verifikasi OTP terlebih dahulu sebelum mengaktifkan notifikasi.', '/verifikasi/otp');
                return;
            }

            $prefModel->enableWhatsApp($userId);
            $this->flashAndRedirect('success', 'Persetujuan notifikasi WhatsApp berhasil diaktifkan. Anda kini akan menerima notifikasi resmi Puskesmas Astanajapura.', '/pengaturan/notifikasi');
        } else {
            $prefModel->disableWhatsApp($userId);
            $this->flashAndRedirect('info', 'Notifikasi WhatsApp berhasil dinonaktifkan. Anda dapat mengaktifkannya kembali kapan saja.', '/pengaturan/notifikasi');
        }
    }

    /**
     * Save user notification channel preferences
     */
    public function savePreferences(): void
    {
        Auth::requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $userId = (int) Auth::id();
        $prefModel = new NotificationPreference();

        $preferences = [
            'notify_registration'    => $this->input('notify_registration') ? 1 : 0,
            'notify_status_change'   => $this->input('notify_status_change') ? 1 : 0,
            'notify_reminder'        => $this->input('notify_reminder') ? 1 : 0,
            'notify_schedule_change' => $this->input('notify_schedule_change') ? 1 : 0,
            'notify_cancellation'    => $this->input('notify_cancellation') ? 1 : 0,
            'notify_announcement'    => $this->input('notify_announcement') ? 1 : 0,
            'notify_education'       => $this->input('notify_education') ? 1 : 0,
        ];

        $prefModel->updatePreferences($userId, $preferences);

        $this->flashAndRedirect('success', 'Preferensi notifikasi Anda berhasil diperbarui.', '/pengaturan/notifikasi');
    }
}
