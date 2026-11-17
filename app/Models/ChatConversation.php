<?php

namespace App\Models;

use App\Core\Model;

class ChatConversation extends Model
{
    protected string $table = 'chat_conversations';

    /**
     * Get or create active conversation for authenticated user
     */
    public function getOrCreateForUser(int $userId): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `user_id` = :uid ORDER BY `updated_at` DESC LIMIT 1";
        $conv = $this->queryOne($sql, ['uid' => $userId]);

        if ($conv) {
            return $conv;
        }

        $id = $this->create([
            'user_id' => $userId,
            'session_id' => null,
            'title' => 'Percakapan Asisten AI',
        ]);

        return $this->find($id) ?: [
            'id' => $id,
            'user_id' => $userId,
            'session_id' => null,
            'title' => 'Percakapan Asisten AI',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get or create active conversation for guest session
     */
    public function getOrCreateForSession(string $sessionId): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `session_id` = :sid AND `user_id` IS NULL ORDER BY `updated_at` DESC LIMIT 1";
        $conv = $this->queryOne($sql, ['sid' => $sessionId]);

        if ($conv) {
            return $conv;
        }

        $id = $this->create([
            'user_id' => null,
            'session_id' => $sessionId,
            'title' => 'Percakapan Asisten AI (Tamu)',
        ]);

        return $this->find($id) ?: [
            'id' => $id,
            'user_id' => null,
            'session_id' => $sessionId,
            'title' => 'Percakapan Asisten AI (Tamu)',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Delete conversation and related messages for authenticated user
     */
    public function clearUserConversation(int $userId): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `user_id` = :uid";
        return $this->execute($sql, ['uid' => $userId]);
    }

    /**
     * Delete conversation and related messages for guest session
     */
    public function clearSessionConversation(string $sessionId): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `session_id` = :sid AND `user_id` IS NULL";
        return $this->execute($sql, ['sid' => $sessionId]);
    }

    /**
     * Touch updated_at timestamp
     */
    public function touchConversation(int $id): bool
    {
        $sql = "UPDATE `{$this->table}` SET `updated_at` = NOW() WHERE `id` = :id";
        return $this->execute($sql, ['id' => $id]);
    }
}
