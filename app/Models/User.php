<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';

    public function findByNik(?string $nik): ?array
    {
        if (empty($nik)) {
            return null;
        }
        return $this->findBy('nik', $nik);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    public function findByPhone(string $phone): ?array
    {
        // Try exact match or cleaned match
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `phone` = :p1 
                   OR `phone` = :p2 
                   OR REPLACE(REPLACE(`phone`, '-', ''), ' ', '') = :p3 
                LIMIT 1";

        return $this->queryOne($sql, [
            'p1' => $phone,
            'p2' => $cleanPhone,
            'p3' => $cleanPhone
        ]);
    }

    public function findWithVillage(int $id): ?array
    {
        $sql = "SELECT u.*, v.name as village_name, v.district as district_name, v.postal_code 
                FROM `{$this->table}` u 
                LEFT JOIN `villages` v ON u.village_id = v.id 
                WHERE u.id = :id LIMIT 1";
        return $this->queryOne($sql, ['id' => $id]);
    }

    public function markEmailVerified(int $userId): bool
    {
        $sql = "UPDATE `{$this->table}` SET `email_verified_at` = NOW() WHERE `id` = :id";
        return $this->execute($sql, ['id' => $userId]);
    }

    public function markPhoneVerified(int $userId): bool
    {
        $sql = "UPDATE `{$this->table}` SET `phone_verified_at` = NOW() WHERE `id` = :id";
        return $this->execute($sql, ['id' => $userId]);
    }

    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        $sql = "UPDATE `{$this->table}` SET `password` = :pwd, `updated_at` = NOW() WHERE `id` = :id";
        return $this->execute($sql, ['pwd' => $hashedPassword, 'id' => $userId]);
    }

    public function getAllPatientsWithVillage(): array
    {
        $sql = "SELECT u.*, v.name as village_name, 
                       (SELECT COUNT(*) FROM registrations r WHERE r.user_id = u.id) as total_registrations
                FROM `{$this->table}` u 
                LEFT JOIN `villages` v ON u.village_id = v.id 
                WHERE u.role = 'user'
                ORDER BY u.created_at DESC";
        return $this->query($sql);
    }

    public function getDemographicsByVillage(): array
    {
        $sql = "SELECT v.name as village_name, COUNT(u.id) as total_users
                FROM `villages` v
                LEFT JOIN `users` u ON u.village_id = v.id AND u.role = 'user'
                GROUP BY v.id, v.name
                ORDER BY total_users DESC";
        return $this->query($sql);
    }
}
