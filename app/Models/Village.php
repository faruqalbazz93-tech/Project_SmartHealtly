<?php

namespace App\Models;

use App\Core\Model;

class Village extends Model
{
    protected string $table = 'villages';

    public function allOrdered(): array
    {
        return $this->all('name ASC');
    }

    public function getWithStats(): array
    {
        $sql = "SELECT v.*, 
                       COUNT(DISTINCT u.id) as total_residents,
                       COUNT(DISTINCT r.id) as total_registrations
                FROM `villages` v
                LEFT JOIN `users` u ON u.village_id = v.id
                LEFT JOIN `registrations` r ON r.user_id = u.id
                GROUP BY v.id
                ORDER BY v.name ASC";
        return $this->query($sql);
    }
}
