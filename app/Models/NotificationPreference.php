<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class NotificationPreference extends Model
{
    protected string $table = 'user_notification_preferences';

    /**
     * Get or create notification preferences for a user
     */
    public function getForUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `user_id` = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $pref = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pref) {
            // Default unconsented preferences (opt-in requires explicit user action)
            $default = [
                'user_id'                     => $userId,
                'whatsapp_enabled'            => 0,
                'notify_registration'         => 1,
                'notify_reminder'             => 1,
                'notify_schedule_change'      => 1,
                'notify_announcement'         => 1,
                'notify_education'            => 0,
                'whatsapp_consent_at'         => null,
                'whatsapp_consent_revoked_at' => null,
            ];

            $insertSql = "INSERT INTO `{$this->table}` 
                          (`user_id`, `whatsapp_enabled`, `notify_registration`, `notify_reminder`, 
                           `notify_schedule_change`, `notify_announcement`, `notify_education`, 
                           `whatsapp_consent_at`, `whatsapp_consent_revoked_at`, `created_at`) 
                          VALUES 
                          (:user_id, :whatsapp_enabled, :notify_registration, :notify_reminder, 
                           :notify_schedule_change, :notify_announcement, :notify_education, 
                           :whatsapp_consent_at, :whatsapp_consent_revoked_at, NOW())";
            $this->execute($insertSql, $default);

            $stmt->execute(['uid' => $userId]);
            $pref = $stmt->fetch(PDO::FETCH_ASSOC) ?: $default;
        }

        return $pref;
    }

    /**
     * Check if user has granted active consent for WhatsApp notifications
     */
    public function hasConsent(int $userId, string $type = 'any'): bool
    {
        $pref = $this->getForUser($userId);

        if (empty($pref['whatsapp_enabled']) || (int)$pref['whatsapp_enabled'] !== 1) {
            return false;
        }

        if (empty($pref['whatsapp_consent_at'])) {
            return false;
        }

        // Check specific notification type preference
        return match ($type) {
            'registration'    => (bool)($pref['notify_registration'] ?? 1),
            'status_change'   => (bool)($pref['notify_status_change'] ?? 1),
            'reminder', 'reminder_h1', 'reminder_h2' => (bool)($pref['notify_reminder'] ?? 1),
            'schedule_change' => (bool)($pref['notify_schedule_change'] ?? 1),
            'cancellation'    => (bool)($pref['notify_cancellation'] ?? 1),
            'announcement'    => (bool)($pref['notify_announcement'] ?? 1),
            'education'       => (bool)($pref['notify_education'] ?? 0),
            default           => true,
        };
    }

    /**
     * Grant explicit user consent to receive WhatsApp notifications
     */
    public function enableWhatsApp(int $userId): bool
    {
        $sql = "UPDATE `{$this->table}` 
                SET `whatsapp_enabled` = 1, 
                    `whatsapp_consent_at` = NOW(), 
                    `whatsapp_consent_revoked_at` = NULL,
                    `updated_at` = NOW() 
                WHERE `user_id` = :uid";
        
        $this->getForUser($userId); // ensure record exists
        return $this->execute($sql, ['uid' => $userId]);
    }

    /**
     * Revoke user consent (opt-out of WhatsApp notifications)
     */
    public function disableWhatsApp(int $userId): bool
    {
        $sql = "UPDATE `{$this->table}` 
                SET `whatsapp_enabled` = 0, 
                    `whatsapp_consent_revoked_at` = NOW(),
                    `updated_at` = NOW() 
                WHERE `user_id` = :uid";
        
        $this->getForUser($userId); // ensure record exists
        return $this->execute($sql, ['uid' => $userId]);
    }

    /**
     * Update individual notification channel preferences
     */
    public function updatePreferences(int $userId, array $data): bool
    {
        $this->getForUser($userId); // ensure record exists

        $allowedKeys = [
            'notify_registration',
            'notify_status_change',
            'notify_reminder',
            'notify_schedule_change',
            'notify_cancellation',
            'notify_announcement',
            'notify_education'
        ];

        $sets = [];
        $params = ['uid' => $userId];

        foreach ($allowedKeys as $key) {
            if (isset($data[$key])) {
                $sets[] = "`{$key}` = :{$key}";
                $params[$key] = (int)(bool)$data[$key];
            }
        }

        if (empty($sets)) {
            return true;
        }

        $sets[] = "`updated_at` = NOW()";
        $setStr = implode(', ', $sets);

        $sql = "UPDATE `{$this->table}` SET {$setStr} WHERE `user_id` = :uid";
        return $this->execute($sql, $params);
    }
}

