<?php

namespace App\Models;

use App\Core\Model;

class AiLog extends Model
{
    protected string $table = 'ai_logs';

    public function getRecentLogs(int $limit = 50): array
    {
        $sql = "SELECT l.*, u.name as user_name, u.email as user_email
                FROM `{$this->table}` l
                LEFT JOIN `users` u ON l.user_id = u.id
                ORDER BY l.created_at DESC LIMIT {$limit}";
        return $this->query($sql);
    }
}
