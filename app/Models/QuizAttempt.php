<?php

namespace App\Models;

use App\Core\Model;

class QuizAttempt extends Model
{
    protected string $table = 'quiz_attempts';

    public function getByUser(int $userId): array
    {
        return $this->where('user_id', $userId, 'created_at DESC');
    }

    public function getLeaderboard(int $limit = 10): array
    {
        $sql = "SELECT qa.*, u.name as user_name, v.name as village_name
                FROM `{$this->table}` qa
                JOIN `users` u ON qa.user_id = u.id
                LEFT JOIN `villages` v ON u.village_id = v.id
                ORDER BY qa.score DESC, qa.completed_at ASC LIMIT {$limit}";
        return $this->query($sql);
    }
}
