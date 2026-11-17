<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use PDOException;

class ChatbotRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Get active health services (Poli) with doctor and quota info
     * Strictly limits columns returned
     */
    public function getActiveHealthServices(): array
    {
        $sql = "SELECT `code`, `name`, `description`, `doctor_name`, `quota_daily`, `icon`
                FROM `health_services`
                WHERE `status` = 'active'
                ORDER BY `id` ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get operational schedules for active services
     */
    public function getActiveSchedules(): array
    {
        $sql = "SELECT hs.name AS service_name, s.day_of_week, 
                       DATE_FORMAT(s.start_time, '%H:%i') AS start_time, 
                       DATE_FORMAT(s.end_time, '%H:%i') AS end_time, 
                       s.max_quota, hs.quota_daily
                FROM `schedules` s
                JOIN `health_services` hs ON s.health_service_id = hs.id
                WHERE s.is_active = 1 AND hs.status = 'active'
                ORDER BY hs.id ASC, FIELD(s.day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user registrations (only for the authenticated user)
     */
    public function getUserRegistrations(int $userId, int $limit = 5): array
    {
        $sql = "SELECT r.registration_code, hs.name AS service_name, r.queue_number, 
                       r.registration_date, r.status, r.complaint, r.notes
                FROM `registrations` r
                JOIN `health_services` hs ON r.health_service_id = hs.id
                WHERE r.user_id = :user_id
                ORDER BY r.registration_date DESC, r.id DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get overall registration statistics across Puskesmas
     */
    public function getOverallRegistrationStatistics(): array
    {
        // Global count & status totals
        $sql = "SELECT 
                    COUNT(*) as total_all,
                    COALESCE(SUM(CASE WHEN registration_date = CURRENT_DATE() THEN 1 ELSE 0 END), 0) as total_today,
                    COALESCE(SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END), 0) as completed_count,
                    COALESCE(SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END), 0) as confirmed_count,
                    COALESCE(SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END), 0) as pending_count,
                    COALESCE(SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END), 0) as cancelled_count
                FROM `registrations`";
        $stmt = $this->db->query($sql);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_all' => 0, 'total_today' => 0, 'completed_count' => 0,
            'confirmed_count' => 0, 'pending_count' => 0, 'cancelled_count' => 0
        ];

        // Breakdown per health service (poli)
        $serviceSql = "SELECT hs.name as service_name, hs.doctor_name, COUNT(r.id) as booking_count,
                              COALESCE(SUM(CASE WHEN r.registration_date = CURRENT_DATE() THEN 1 ELSE 0 END), 0) as today_count,
                              COALESCE(SUM(CASE WHEN r.status = 'Completed' THEN 1 ELSE 0 END), 0) as completed_count
                       FROM `health_services` hs
                       LEFT JOIN `registrations` r ON hs.id = r.health_service_id
                       WHERE hs.status = 'active'
                       GROUP BY hs.id, hs.name, hs.doctor_name
                       ORDER BY booking_count DESC";
        $byService = $this->db->query($serviceSql)->fetchAll(PDO::FETCH_ASSOC);

        return [
            'summary'    => $summary,
            'by_service' => $byService,
        ];
    }

    /**
     * Get user and demographic statistics across 12 villages
     */
    public function getUsersAndDemographicsStatistics(): array
    {
        $totalUsers = (int) $this->db->query("SELECT COUNT(*) FROM `users` WHERE `role` = 'user'")->fetchColumn();
        $totalAdmin = (int) $this->db->query("SELECT COUNT(*) FROM `users` WHERE `role` = 'admin'")->fetchColumn();
        $totalVerified = (int) $this->db->query("SELECT COUNT(*) FROM `users` WHERE `phone_verified_at` IS NOT NULL OR `email_verified_at` IS NOT NULL")->fetchColumn();

        $villageSql = "SELECT v.name as village_name, COUNT(u.id) as patient_count
                       FROM `villages` v
                       LEFT JOIN `users` u ON v.id = u.village_id AND u.role = 'user'
                       GROUP BY v.id, v.name
                       ORDER BY patient_count DESC, v.name ASC";
        $villages = $this->db->query($villageSql)->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total_patients' => $totalUsers,
            'total_staff'    => $totalAdmin,
            'total_verified' => $totalVerified,
            'villages'       => $villages,
        ];
    }

    /**
     * Get recent public announcements
     */
    public function getRecentAnnouncements(int $limit = 3): array
    {
        $sql = "SELECT title, message, target_url, created_at 
                FROM `notifications` 
                WHERE `type` = 'announcement' 
                ORDER BY `created_at` DESC 
                LIMIT {$limit}";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get published health educational articles
     */
    public function getPublishedArticles(int $limit = 4): array
    {
        $sql = "SELECT a.title, a.slug, a.summary, hc.name as category_name
                FROM `health_articles` a
                JOIN `health_categories` hc ON a.category_id = hc.id
                WHERE a.status = 'published'
                ORDER BY a.created_at DESC
                LIMIT {$limit}";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get active quiz questions
     */
    public function getActiveQuizQuestions(int $limit = 5): array
    {
        $sql = "SELECT q.question_text, q.explanation, hc.name as category_name
                FROM `questions` q
                LEFT JOIN `health_categories` hc ON q.category_id = hc.id
                WHERE q.status = 'active'
                ORDER BY q.id ASC
                LIMIT {$limit}";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user WhatsApp notification status, consent, and delivery logs
     */
    public function getUserWhatsAppStatus(int $userId): array
    {
        $userSql = "SELECT phone, phone_verified_at FROM `users` WHERE `id` = :uid LIMIT 1";
        $stmt = $this->db->prepare($userSql);
        $stmt->execute(['uid' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return [];
        }

        $prefSql = "SELECT * FROM `user_notification_preferences` WHERE `user_id` = :uid LIMIT 1";
        $stmt = $this->db->prepare($prefSql);
        $stmt->execute(['uid' => $userId]);
        $pref = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'whatsapp_enabled'       => 0,
            'whatsapp_consent_at'    => null,
            'notify_registration'    => 1,
            'notify_reminder'        => 1,
            'notify_schedule_change' => 1,
            'notify_announcement'    => 1,
            'notify_education'       => 0,
        ];

        $logSql = "SELECT template_name, message_type, delivery_status, sent_at 
                   FROM `whatsapp_message_logs` 
                   WHERE `user_id` = :uid 
                   ORDER BY `sent_at` DESC 
                   LIMIT 3";
        $stmt = $this->db->prepare($logSql);
        $stmt->execute(['uid' => $userId]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'phone'                  => $user['phone'],
            'is_phone_verified'      => !empty($user['phone_verified_at']),
            'whatsapp_enabled'       => (bool)($pref['whatsapp_enabled'] ?? false),
            'whatsapp_consent_at'    => $pref['whatsapp_consent_at'] ?? null,
            'notify_registration'    => (bool)($pref['notify_registration'] ?? true),
            'notify_reminder'        => (bool)($pref['notify_reminder'] ?? true),
            'notify_schedule_change' => (bool)($pref['notify_schedule_change'] ?? true),
            'notify_announcement'    => (bool)($pref['notify_announcement'] ?? true),
            'notify_education'       => (bool)($pref['notify_education'] ?? false),
            'recent_logs'            => $logs,
        ];
    }
}
