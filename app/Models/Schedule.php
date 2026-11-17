<?php

namespace App\Models;

use App\Core\Model;

class Schedule extends Model
{
    protected string $table = 'schedules';

    public function getActiveByService(int $serviceId): array
    {
        $sql = "SELECT s.*, hs.name as service_name, hs.doctor_name 
                FROM `{$this->table}` s 
                JOIN `health_services` hs ON s.health_service_id = hs.id 
                WHERE s.health_service_id = :service_id 
                  AND s.is_active = 1
                ORDER BY FIELD(s.day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')";
        return $this->query($sql, ['service_id' => $serviceId]);
    }

    public function getAllWithServices(): array
    {
        $sql = "SELECT s.*, hs.name as service_name, hs.doctor_name, hs.icon, hs.status as service_status 
                FROM `{$this->table}` s 
                JOIN `health_services` hs ON s.health_service_id = hs.id 
                ORDER BY hs.name ASC, FIELD(s.day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')";
        return $this->query($sql);
    }

    public function getForServiceAndDay(int $serviceId, string $dayName): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE health_service_id = :service_id 
                  AND day_of_week = :day_name 
                  AND is_active = 1 
                LIMIT 1";
        return $this->queryOne($sql, [
            'service_id' => $serviceId,
            'day_name'   => $dayName
        ]);
    }

    public function toggleActive(int $id, int $isActive): bool
    {
        return $this->update($id, ['is_active' => $isActive ? 1 : 0]);
    }
}
