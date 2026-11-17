<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use Exception;

class Notification extends Model
{
    protected string $table = 'notifications';

    /**
     * Get paginated notifications for a specific user with optional filters
     */
    public function getForUser(int $userId, string $filter = 'all', int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `user_id` = :user_id";
        $params = ['user_id' => $userId];

        if ($filter === 'unread') {
            $sql .= " AND `is_read` = 0";
        } elseif (in_array($filter, ['registration', 'schedule', 'account', 'education', 'announcement'], true)) {
            $sql .= " AND `type` = :filter_type";
            $params['filter_type'] = $filter;
        }

        $sql .= " ORDER BY `created_at` DESC LIMIT {$limit} OFFSET {$offset}";

        return $this->query($sql, $params);
    }

    /**
     * Count notifications for a specific user with filter
     */
    public function countForUser(int $userId, string $filter = 'all'): int
    {
        $where = "user_id = :uid";
        $params = ['uid' => $userId];

        if ($filter === 'unread') {
            $where .= " AND is_read = 0";
        } elseif (in_array($filter, ['registration', 'schedule', 'account', 'education', 'announcement'], true)) {
            $where .= " AND type = :filter_type";
            $params['filter_type'] = $filter;
        }

        return $this->count($where, $params);
    }

    /**
     * Count unread notifications for navbar badge
     */
    public function countUnread(int $userId): int
    {
        return $this->count("user_id = :uid AND is_read = 0", ['uid' => $userId]);
    }

    /**
     * Get recent notifications for dropdown preview
     */
    public function getRecentForDropdown(int $userId, int $limit = 5): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `user_id` = :uid ORDER BY `created_at` DESC LIMIT {$limit}";
        return $this->query($sql, ['uid' => $userId]);
    }

    /**
     * Get category breakdown counts for tabs
     */
    public function getCountsByType(int $userId): array
    {
        $all = $this->count("user_id = :uid", ['uid' => $userId]);
        $unread = $this->count("user_id = :uid AND is_read = 0", ['uid' => $userId]);
        $registration = $this->count("user_id = :uid AND type = 'registration'", ['uid' => $userId]);
        $schedule = $this->count("user_id = :uid AND type = 'schedule'", ['uid' => $userId]);
        $account = $this->count("user_id = :uid AND type = 'account'", ['uid' => $userId]);
        $education = $this->count("user_id = :uid AND type = 'education'", ['uid' => $userId]);
        $announcement = $this->count("user_id = :uid AND type = 'announcement'", ['uid' => $userId]);

        return [
            'all'          => $all,
            'unread'       => $unread,
            'registration' => $registration,
            'schedule'     => $schedule,
            'account'      => $account,
            'education'    => $education,
            'announcement' => $announcement,
        ];
    }

    /**
     * Mark a single notification as read (with user isolation)
     */
    public function markAsRead(int $id, int $userId): bool
    {
        $sql = "UPDATE `{$this->table}` SET `is_read` = 1, `read_at` = NOW() WHERE `id` = :id AND `user_id` = :uid";
        return $this->execute($sql, ['id' => $id, 'uid' => $userId]);
    }

    /**
     * Mark all unread notifications as read for a user
     */
    public function markAllAsRead(int $userId): bool
    {
        $sql = "UPDATE `{$this->table}` SET `is_read` = 1, `read_at` = NOW() WHERE `user_id` = :uid AND `is_read` = 0";
        return $this->execute($sql, ['uid' => $userId]);
    }

    /**
     * Delete a single notification (with user isolation)
     */
    public function deleteForUser(int $id, int $userId): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `id` = :id AND `user_id` = :uid";
        return $this->execute($sql, ['id' => $id, 'uid' => $userId]);
    }

    /**
     * Delete all read notifications for a user
     */
    public function deleteAllReadForUser(int $userId): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `user_id` = :uid AND `is_read` = 1";
        return $this->execute($sql, ['uid' => $userId]);
    }

    /**
     * Check if a deduplication key exists for this user
     */
    public function isDeduplicated(int $userId, string $dedupKey): bool
    {
        $sql = "SELECT COUNT(*) FROM `notification_dedup` WHERE `user_id` = :uid AND `dedup_key` = :dkey";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId, 'dkey' => $dedupKey]);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    /**
     * Create notification with optional deduplication check
     */
    public function createNotification(array $data, ?string $dedupKey = null): int|false
    {
        $userId = (int) ($data['user_id'] ?? 0);
        if ($userId <= 0) {
            return false;
        }

        // Check deduplication
        if ($dedupKey !== null && $this->isDeduplicated($userId, $dedupKey)) {
            // Already sent, prevent duplicate
            return false;
        }

        $type = $data['type'] ?? 'announcement';
        $title = trim((string)($data['title'] ?? ''));
        $message = trim((string)($data['message'] ?? ''));
        $targetUrl = !empty($data['target_url']) ? trim((string)$data['target_url']) : null;

        $insertSql = "INSERT INTO `{$this->table}` 
                      (`user_id`, `type`, `title`, `message`, `target_url`, `is_read`, `created_at`, `read_at`) 
                      VALUES 
                      (:user_id, :type, :title, :message, :target_url, 0, NOW(), NULL)";

        $stmt = $this->db->prepare($insertSql);
        $success = $stmt->execute([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'target_url' => $targetUrl,
        ]);

        if (!$success) {
            return false;
        }

        $notifId = (int) $this->db->lastInsertId();

        // Record deduplication key if specified
        if ($dedupKey !== null) {
            $dedupSql = "INSERT IGNORE INTO `notification_dedup` (`user_id`, `dedup_key`, `channel`, `created_at`) 
                         VALUES (:uid, :dkey, 'app', NOW())";
            $this->execute($dedupSql, ['uid' => $userId, 'dkey' => $dedupKey]);
        }

        return $notifId;
    }
}
