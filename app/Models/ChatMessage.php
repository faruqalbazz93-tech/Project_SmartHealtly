<?php

namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;

class ChatMessage extends Model
{
    protected string $table = 'chat_messages';

    /**
     * Get messages for conversation in chronological order
     */
    public function getMessages(int $conversationId, int $limit = 50): array
    {
        $sql = "SELECT id, conversation_id, role, message, created_at 
                FROM `{$this->table}` 
                WHERE `conversation_id` = :cid 
                ORDER BY `created_at` ASC, `id` ASC 
                LIMIT {$limit}";

        return $this->query($sql, ['cid' => $conversationId]);
    }

    /**
     * Add a message with role validation
     */
    public function addMessage(int $conversationId, string $role, string $message): int|false
    {
        $role = strtolower(trim($role));
        if (!in_array($role, ['user', 'assistant', 'system'], true)) {
            throw new InvalidArgumentException("Peran pesan tidak valid: {$role}");
        }

        $id = $this->create([
            'conversation_id' => $conversationId,
            'role'            => $role,
            'message'         => $message,
        ]);

        return $id ? (int) $id : false;
    }

    /**
     * Prune messages older than given days for data retention compliance
     */
    public function pruneOldMessages(int $days = 30): int
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL :days DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['days' => $days]);
        return $stmt->rowCount();
    }
}
