<?php

namespace App\Models;

use App\Core\Model;

class PasswordReset extends Model
{
    protected string $table = 'password_resets';

    /**
     * Create new password reset token (valid for 60 minutes)
     */
    public function createResetToken(int $userId, string $email): string
    {
        // Invalidate older unused tokens for this user
        $this->execute(
            "DELETE FROM `{$this->table}` WHERE `user_id` = :uid AND `used_at` IS NULL",
            ['uid' => $userId]
        );

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+60 minutes'));

        $this->create([
            'user_id'    => $userId,
            'email'      => $email,
            'token'      => $token,
            'expires_at' => $expiresAt,
            'used_at'    => null,
        ]);

        return $token;
    }

    /**
     * Validate reset token
     */
    public function validateToken(string $token): ?array
    {
        $sql = "SELECT pr.*, u.name as user_name, u.email as user_email 
                FROM `{$this->table}` pr
                JOIN `users` u ON pr.user_id = u.id
                WHERE pr.token = :tok 
                  AND pr.used_at IS NULL 
                  AND pr.expires_at > NOW() 
                LIMIT 1";

        return $this->queryOne($sql, ['tok' => $token]);
    }

    /**
     * Mark token as used
     */
    public function markAsUsed(int $id): bool
    {
        return $this->update($id, ['used_at' => date('Y-m-d H:i:s')]);
    }
}
