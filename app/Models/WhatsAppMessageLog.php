<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class WhatsAppMessageLog extends Model
{
    protected string $table = 'whatsapp_message_logs';

    /**
     * Mask phone number for privacy preservation in audit logs (e.g. 62857****5678)
     */
    public static function maskPhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        $len = strlen($clean);
        if ($len <= 6) {
            return $clean;
        }
        $start = substr($clean, 0, 5);
        $end = substr($clean, -4);
        return $start . '****' . $end;
    }

    /**
     * Record an outgoing WhatsApp message log
     */
    public function logMessage(array $data): int|false
    {
        $userId = !empty($data['user_id']) ? (int)$data['user_id'] : null;
        $messageType = $data['message_type'] ?? 'general';
        $templateName = $data['template_name'] ?? null;
        $messageId = $data['message_id'] ?? null;
        $phone = $data['recipient_phone'] ?? '';
        $maskedPhone = !empty($data['recipient_number_masked']) 
            ? $data['recipient_number_masked'] 
            : self::maskPhone($phone);
        $status = $data['delivery_status'] ?? 'sent';
        $errorCode = $data['error_code'] ?? null;
        $errorMessage = $data['error_message'] ?? null;
        $payloadSummary = $data['payload_summary'] ?? null;
        $sentAt = $data['sent_at'] ?? date('Y-m-d H:i:s');

        $sql = "INSERT INTO `{$this->table}` 
                (`user_id`, `message_type`, `template_name`, `message_id`, 
                 `recipient_number_masked`, `recipient_phone`, `delivery_status`, 
                 `error_code`, `error_message`, `payload_summary`, `sent_at`, `created_at`) 
                VALUES 
                (:user_id, :message_type, :template_name, :message_id, 
                 :recipient_number_masked, :recipient_phone, :delivery_status, 
                 :error_code, :error_message, :payload_summary, :sent_at, NOW())";

        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            'user_id'                 => $userId,
            'message_type'            => $messageType,
            'template_name'           => $templateName,
            'message_id'              => $messageId,
            'recipient_number_masked' => $maskedPhone,
            'recipient_phone'         => $phone,
            'delivery_status'         => $status,
            'error_code'              => $errorCode,
            'error_message'           => $errorMessage,
            'payload_summary'         => $payloadSummary,
            'sent_at'                 => $sentAt,
        ]);

        return $ok ? (int)$this->db->lastInsertId() : false;
    }

    /**
     * Update delivery status by Meta Message ID (wamid) from webhook
     */
    public function updateDeliveryStatus(string $messageId, string $status, array $extra = []): bool
    {
        $validStatuses = ['sent', 'delivered', 'read', 'failed'];
        if (!in_array($status, $validStatuses, true)) {
            return false;
        }

        $sets = ["`delivery_status` = :status"];
        $params = [
            'status' => $status,
            'mid'    => $messageId,
        ];

        if ($status === 'delivered') {
            $sets[] = "`delivered_at` = COALESCE(:delivered_at, NOW())";
            $params['delivered_at'] = $extra['timestamp'] ?? date('Y-m-d H:i:s');
        } elseif ($status === 'read') {
            $sets[] = "`read_at` = COALESCE(:read_at, NOW())";
            $params['read_at'] = $extra['timestamp'] ?? date('Y-m-d H:i:s');
        } elseif ($status === 'failed') {
            if (!empty($extra['error_code'])) {
                $sets[] = "`error_code` = :error_code";
                $params['error_code'] = (string)$extra['error_code'];
            }
            if (!empty($extra['error_message'])) {
                $sets[] = "`error_message` = :error_message";
                $params['error_message'] = (string)$extra['error_message'];
            }
        }

        $setSql = implode(', ', $sets);
        $sql = "UPDATE `{$this->table}` SET {$setSql} WHERE `message_id` = :mid";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get recent logs for a specific user (safe for user dashboard)
     */
    public function getLogsForUser(int $userId, int $limit = 10): array
    {
        $sql = "SELECT id, message_type, template_name, message_id, 
                       recipient_number_masked, delivery_status, payload_summary, 
                       sent_at, delivered_at, read_at, created_at 
                FROM `{$this->table}` 
                WHERE `user_id` = :uid 
                ORDER BY `created_at` DESC 
                LIMIT {$limit}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get latest logs for admin panel with pagination, status, type, and date filter
     */
    public function getLatestForAdmin(int $limit = 20, int $offset = 0, ?string $statusFilter = null, ?string $typeFilter = null, ?string $dateFilter = null): array
    {
        $sql = "SELECT l.*, u.name as user_name, u.email as user_email
                FROM `{$this->table}` l
                LEFT JOIN `users` u ON l.user_id = u.id";
        
        $where = [];
        $params = [];

        if (!empty($statusFilter)) {
            $where[] = "l.delivery_status = :status";
            $params['status'] = $statusFilter;
        }

        if (!empty($typeFilter)) {
            $where[] = "l.message_type = :type";
            $params['type'] = $typeFilter;
        }

        if (!empty($dateFilter)) {
            $where[] = "DATE(l.created_at) = :date";
            $params['date'] = $dateFilter;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count total logs for admin pagination with filters
     */
    public function countForAdmin(?string $statusFilter = null, ?string $typeFilter = null, ?string $dateFilter = null): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}` l";
        $where = [];
        $params = [];

        if (!empty($statusFilter)) {
            $where[] = "l.delivery_status = :status";
            $params['status'] = $statusFilter;
        }

        if (!empty($typeFilter)) {
            $where[] = "l.message_type = :type";
            $params['type'] = $typeFilter;
        }

        if (!empty($dateFilter)) {
            $where[] = "DATE(l.created_at) = :date";
            $params['date'] = $dateFilter;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get delivery statistics for admin dashboard
     */
    public function getDeliveryStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_all,
                    SUM(CASE WHEN delivery_status = 'sent' THEN 1 ELSE 0 END) as total_sent,
                    SUM(CASE WHEN delivery_status = 'delivered' THEN 1 ELSE 0 END) as total_delivered,
                    SUM(CASE WHEN delivery_status = 'read' THEN 1 ELSE 0 END) as total_read,
                    SUM(CASE WHEN delivery_status = 'failed' THEN 1 ELSE 0 END) as total_failed,
                    SUM(CASE WHEN delivery_status = 'rejected' THEN 1 ELSE 0 END) as total_rejected,
                    SUM(CASE WHEN delivery_status = 'skipped' THEN 1 ELSE 0 END) as total_skipped,
                    SUM(CASE WHEN DATE(created_at) = CURRENT_DATE() THEN 1 ELSE 0 END) as total_today
                FROM `{$this->table}`";

        $res = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);

        return [
            'total'     => (int)($res['total_all'] ?? 0),
            'sent'      => (int)($res['total_sent'] ?? 0),
            'delivered' => (int)($res['total_delivered'] ?? 0),
            'read'      => (int)($res['total_read'] ?? 0),
            'failed'    => (int)($res['total_failed'] ?? 0),
            'rejected'  => (int)($res['total_rejected'] ?? 0),
            'skipped'   => (int)($res['total_skipped'] ?? 0),
            'today'     => (int)($res['total_today'] ?? 0),
        ];
    }
}

