<?php

namespace App\Models;

use App\Core\Model;

class HealthService extends Model
{
    protected string $table = 'health_services';

    public function getActiveServices(): array
    {
        return $this->where('status', 'active', 'id ASC');
    }

    public function toggleStatus(int $id, string $status): bool
    {
        $status = ($status === 'active') ? 'active' : 'inactive';
        return $this->update($id, ['status' => $status]);
    }

    public function getWithTodayQueueStats(string $date): array
    {
        $sql = "SELECT hs.*, 
                       (SELECT COUNT(*) FROM registrations r 
                        WHERE r.health_service_id = hs.id 
                          AND r.registration_date = :reg_date 
                          AND r.status != 'Cancelled') as registered_today,
                       (SELECT COUNT(*) FROM registrations r 
                        WHERE r.health_service_id = hs.id 
                          AND r.registration_date = :reg_date2 
                          AND r.status = 'Confirmed') as currently_calling_count,
                       (SELECT queue_number FROM registrations r 
                        WHERE r.health_service_id = hs.id 
                          AND r.registration_date = :reg_date3 
                          AND r.status = 'Confirmed' 
                        ORDER BY queue_number DESC LIMIT 1) as current_calling_number
                FROM `{$this->table}` hs
                WHERE hs.status = 'active'
                ORDER BY hs.id ASC";

        return $this->query($sql, [
            'reg_date'  => $date,
            'reg_date2' => $date,
            'reg_date3' => $date
        ]);
    }
}
