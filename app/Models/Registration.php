<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use Exception;

class Registration extends Model
{
    protected string $table = 'registrations';

    /**
     * Create a health service registration inside an ACID Database Transaction
     * with atomic quota check and race-condition locking.
     */
    public function createRegistrationTransaction(array $params): array
    {
        $userId = (int) ($params['user_id'] ?? 0);
        $serviceId = (int) ($params['health_service_id'] ?? 0);
        $scheduleId = !empty($params['schedule_id']) ? (int)$params['schedule_id'] : null;
        $regDate = $params['registration_date'] ?? date('Y-m-d');
        $complaint = trim((string)($params['complaint'] ?? ''));

        // 1. Validation of date
        $today = date('Y-m-d');
        if ($regDate < $today) {
            return [
                'status'  => false,
                'message' => 'Tanggal kunjungan tidak boleh di masa lalu.',
                'code'    => 'PAST_DATE'
            ];
        }

        // 2. Start Transaction
        $this->beginTransaction();

        try {
            // 3. Lock & check service status
            $serviceStmt = $this->db->prepare("SELECT * FROM `health_services` WHERE `id` = :sid FOR UPDATE");
            $serviceStmt->execute(['sid' => $serviceId]);
            $service = $serviceStmt->fetch(PDO::FETCH_ASSOC);

            if (!$service || $service['status'] !== 'active') {
                $this->rollBack();
                return [
                    'status'  => false,
                    'message' => 'Layanan poli yang dipilih sedang tidak aktif atau tidak ditemukan.',
                    'code'    => 'SERVICE_INACTIVE'
                ];
            }

            // 4. Validate schedule & operational day
            $dayNames = [
                1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
                5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
            ];
            $dayOfWeek = $dayNames[(int)date('N', strtotime($regDate))] ?? 'Senin';

            if ($dayOfWeek === 'Minggu') {
                $this->rollBack();
                return [
                    'status'  => false,
                    'message' => 'Puskesmas Astanajapura tutup pada hari Minggu untuk layanan poli reguler.',
                    'code'    => 'CLOSED_SUNDAY'
                ];
            }

            $maxQuota = (int) $service['quota_daily'];

            if ($scheduleId) {
                $scheduleStmt = $this->db->prepare("SELECT * FROM `schedules` WHERE `id` = :sch_id AND `health_service_id` = :sid AND `is_active` = 1 FOR UPDATE");
                $scheduleStmt->execute(['sch_id' => $scheduleId, 'sid' => $serviceId]);
                $schedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);

                if (!$schedule) {
                    $this->rollBack();
                    return [
                        'status'  => false,
                        'message' => 'Jadwal dokter yang dipilih tidak aktif atau tidak sesuai.',
                        'code'    => 'SCHEDULE_INVALID'
                    ];
                }

                if ($schedule['day_of_week'] !== $dayOfWeek) {
                    $this->rollBack();
                    return [
                        'status'  => false,
                        'message' => "Jadwal ini hanya beroperasi pada hari {$schedule['day_of_week']}. Tanggal yang Anda pilih adalah hari {$dayOfWeek}.",
                        'code'    => 'DAY_MISMATCH'
                    ];
                }

                $maxQuota = (int) $schedule['max_quota'];
            }

            // 5. Check Duplicate Booking for this user on the same date and service
            $dupStmt = $this->db->prepare("SELECT COUNT(*) FROM `registrations` 
                                           WHERE `user_id` = :uid 
                                             AND `health_service_id` = :sid 
                                             AND `registration_date` = :rdate 
                                             AND `status` IN ('Pending', 'Confirmed')");
            $dupStmt->execute([
                'uid'   => $userId,
                'sid'   => $serviceId,
                'rdate' => $regDate
            ]);
            $existingCount = (int) $dupStmt->fetchColumn();

            if ($existingCount > 0) {
                $this->rollBack();
                return [
                    'status'  => false,
                    'message' => "Anda sudah memiliki pendaftaran aktif di {$service['name']} pada tanggal " . date('d M Y', strtotime($regDate)) . ". Tidak dapat mendaftar ganda.",
                    'code'    => 'DUPLICATE_BOOKING'
                ];
            }

            // 6. Check Quota Atomically (Count active bookings with row lock)
            $countSql = "SELECT COUNT(*) FROM `registrations` 
                         WHERE `health_service_id` = :sid 
                           AND `registration_date` = :rdate 
                           AND `status` != 'Cancelled'";
            $countParams = ['sid' => $serviceId, 'rdate' => $regDate];

            if ($scheduleId) {
                $countSql .= " AND `schedule_id` = :sch_id";
                $countParams['sch_id'] = $scheduleId;
            }

            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($countParams);
            $currentBooked = (int) $countStmt->fetchColumn();

            if ($currentBooked >= $maxQuota) {
                $this->rollBack();
                return [
                    'status'  => false,
                    'message' => "Mohon maaf, kuota pendaftaran untuk {$service['name']} pada tanggal " . date('d M Y', strtotime($regDate)) . " sudah penuh ({$currentBooked}/{$maxQuota}). Silakan pilih tanggal atau poli lain.",
                    'code'    => 'QUOTA_FULL'
                ];
            }

            // 7. Calculate Next Queue Number Atomically
            $queueSql = "SELECT COALESCE(MAX(`queue_number`), 0) + 1 FROM `registrations` 
                         WHERE `health_service_id` = :sid AND `registration_date` = :rdate";
            $queueStmt = $this->db->prepare($queueSql);
            $queueStmt->execute(['sid' => $serviceId, 'rdate' => $regDate]);
            $nextQueue = (int) $queueStmt->fetchColumn();

            // 8. Generate Unique Registration Code
            $dateStr = date('Ymd', strtotime($regDate));
            $randomSuffix = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            $regCode = "REG-AST-{$dateStr}-{$randomSuffix}";

            // 9. Insert Registration Record
            $insertSql = "INSERT INTO `registrations` 
                          (`registration_code`, `user_id`, `health_service_id`, `schedule_id`, `registration_date`, `queue_number`, `status`, `complaint`, `notes`, `created_at`) 
                          VALUES 
                          (:code, :uid, :sid, :sch_id, :rdate, :qnum, 'Pending', :complaint, NULL, NOW())";
            
            $insertStmt = $this->db->prepare($insertSql);
            $insertStmt->execute([
                'code'      => $regCode,
                'uid'       => $userId,
                'sid'       => $serviceId,
                'sch_id'    => $scheduleId,
                'rdate'     => $regDate,
                'qnum'      => $nextQueue,
                'complaint' => $complaint,
            ]);

            $newId = (int) $this->db->lastInsertId();

            // 10. Commit Transaction
            $this->commit();

            return [
                'status'            => true,
                'message'           => 'Pendaftaran antrean Puskesmas berhasil dibuat!',
                'registration_id'   => $newId,
                'registration_code' => $regCode,
                'queue_number'      => $nextQueue,
                'service_name'      => $service['name'],
                'doctor_name'       => $service['doctor_name'],
                'registration_date' => $regDate,
            ];

        } catch (Exception $e) {
            $this->rollBack();
            return [
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat memproses transaksi pendaftaran: ' . $e->getMessage(),
                'code'    => 'TRANSACTION_ERROR'
            ];
        }
    }

    /**
     * Get Real-time Quota Breakdown for Service/Schedule on Date
     */
    public function getQuotaInfo(int $serviceId, ?int $scheduleId, string $date): array
    {
        $serviceModel = new HealthService();
        $service = $serviceModel->find($serviceId);

        if (!$service) {
            return ['total_quota' => 0, 'used_quota' => 0, 'available_quota' => 0, 'is_full' => true];
        }

        $totalQuota = (int) $service['quota_daily'];

        if ($scheduleId) {
            $scheduleModel = new Schedule();
            $schedule = $scheduleModel->find($scheduleId);
            if ($schedule) {
                $totalQuota = (int) $schedule['max_quota'];
            }
        }

        $sql = "SELECT COUNT(*) FROM `{$this->table}` 
                WHERE `health_service_id` = :sid 
                  AND `registration_date` = :rdate 
                  AND `status` != 'Cancelled'";
        $params = ['sid' => $serviceId, 'rdate' => $date];

        if ($scheduleId) {
            $sql .= " AND `schedule_id` = :sch_id";
            $params['sch_id'] = $scheduleId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $usedQuota = (int) $stmt->fetchColumn();

        $availableQuota = max(0, $totalQuota - $usedQuota);

        return [
            'total_quota'     => $totalQuota,
            'used_quota'      => $usedQuota,
            'available_quota' => $availableQuota,
            'is_full'         => $availableQuota <= 0,
        ];
    }

    /**
     * Check if user already has an active booking on date for service
     */
    public function userHasActiveBooking(int $userId, int $serviceId, string $date): bool
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}` 
                WHERE `user_id` = :uid 
                  AND `health_service_id` = :sid 
                  AND `registration_date` = :rdate 
                  AND `status` IN ('Pending', 'Confirmed')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId, 'sid' => $serviceId, 'rdate' => $date]);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    /**
     * Find registration with full details by unique code
     */
    public function findByCodeWithDetails(string $code): ?array
    {
        $sql = "SELECT r.*, 
                       u.name as patient_name, u.nik as patient_nik, u.phone as patient_phone, 
                       u.gender as patient_gender, u.birth_date as patient_birth_date, u.email as patient_email,
                       v.name as patient_village, v.district as patient_district,
                       hs.name as service_name, hs.doctor_name, hs.code as service_code, hs.icon as service_icon,
                       s.day_of_week as schedule_day, s.start_time as schedule_start, s.end_time as schedule_end
                FROM `{$this->table}` r
                JOIN `users` u ON r.user_id = u.id
                LEFT JOIN `villages` v ON u.village_id = v.id
                JOIN `health_services` hs ON r.health_service_id = hs.id
                LEFT JOIN `schedules` s ON r.schedule_id = s.id
                WHERE r.registration_code = :code LIMIT 1";

        return $this->queryOne($sql, ['code' => $code]);
    }

    /**
     * Get user registrations with optional status filter
     */
    public function getByUserFiltered(int $userId, ?string $status = null): array
    {
        $sql = "SELECT r.*, 
                       hs.name as service_name, hs.doctor_name, hs.icon as service_icon,
                       s.start_time, s.end_time
                FROM `{$this->table}` r
                JOIN `health_services` hs ON r.health_service_id = hs.id
                LEFT JOIN `schedules` s ON r.schedule_id = s.id
                WHERE r.user_id = :user_id";

        $params = ['user_id' => $userId];

        if (!empty($status) && $status !== 'all') {
            $sql .= " AND r.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY r.registration_date DESC, r.queue_number DESC";

        return $this->query($sql, $params);
    }

    /**
     * Get today's active registration for user
     */
    public function getActiveByUser(int $userId): ?array
    {
        $sql = "SELECT r.*, 
                       hs.name as service_name, hs.doctor_name, hs.icon as service_icon,
                       s.start_time, s.end_time
                FROM `{$this->table}` r
                JOIN `health_services` hs ON r.health_service_id = hs.id
                LEFT JOIN `schedules` s ON r.schedule_id = s.id
                WHERE r.user_id = :user_id 
                  AND r.registration_date >= CURRENT_DATE()
                  AND r.status IN ('Pending', 'Confirmed')
                ORDER BY r.registration_date ASC, r.queue_number ASC LIMIT 1";

        return $this->queryOne($sql, ['user_id' => $userId]);
    }

    /**
     * Admin Filtered Registrations
     */
    /**
     * Admin Filtered Registrations (Mendukung Seluruh Riwayat: Sebelumnya, Hari Ini, dan Seterusnya)
     */
    public function getFilteredForAdmin(
        string $date = '',
        ?int $serviceId = null,
        string $status = '',
        string $search = '',
        string $timeframe = 'all'
    ): array {
        $sql = "SELECT r.*, 
                       u.name as patient_name, u.nik as patient_nik, u.phone as patient_phone,
                       v.name as patient_village,
                       hs.name as service_name, hs.doctor_name, hs.icon as service_icon,
                       s.start_time, s.end_time
                FROM `{$this->table}` r
                JOIN `users` u ON r.user_id = u.id
                LEFT JOIN `villages` v ON u.village_id = v.id
                JOIN `health_services` hs ON r.health_service_id = hs.id
                LEFT JOIN `schedules` s ON r.schedule_id = s.id
                WHERE 1=1";

        $params = [];
        $today = date('Y-m-d');

        // Filter berdasarkan timeframe waktu
        if ($timeframe === 'today' || $date === 'today') {
            $sql .= " AND r.registration_date = :reg_today";
            $params['reg_today'] = $today;
        } elseif ($timeframe === 'past' || $date === 'past') {
            $sql .= " AND r.registration_date < :reg_past";
            $params['reg_past'] = $today;
        } elseif ($timeframe === 'upcoming' || $date === 'upcoming') {
            $sql .= " AND r.registration_date > :reg_upcoming";
            $params['reg_upcoming'] = $today;
        } elseif (!empty($date) && $date !== 'all') {
            $sql .= " AND r.registration_date = :reg_date";
            $params['reg_date'] = $date;
        }
        // Jika timeframe === 'all' atau date === 'all' / kosong, tidak membatasi tanggal sehingga semua riwayat (sebelumnya, sekarang, seterusnya) ditampilkan.

        if (!empty($serviceId)) {
            $sql .= " AND r.health_service_id = :service_id";
            $params['service_id'] = $serviceId;
        }

        if (!empty($status) && $status !== 'all') {
            $sql .= " AND r.status = :status";
            $params['status'] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :search OR u.nik LIKE :search2 OR u.phone LIKE :search3 OR r.registration_code LIKE :search4)";
            $params['search']  = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
            $params['search4'] = "%{$search}%";
        }

        // Urutkan riwayat pendaftaran dari yang terbaru (tanggal kunjungan terbaru & waktu pendaftaran terbaru)
        $sql .= " ORDER BY r.registration_date DESC, r.created_at DESC, r.queue_number ASC";

        return $this->query($sql, $params);
    }

    /**
     * Update Registration Status
     */
    public function updateStatus(int $id, string $status, ?string $notes = null): bool
    {
        $validStatuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];
        if (!in_array($status, $validStatuses, true)) {
            return false;
        }

        $data = ['status' => $status];
        if ($notes !== null) {
            $data['notes'] = $notes;
        }

        return $this->update($id, $data);
    }

    /**
     * Admin Dashboard Summary Stats (Menyimpan & Menghitung Riwayat Akumulatif & Harian)
     */
    public function getDashboardStats(): array
    {
        $today = date('Y-m-d');
        
        // Statistik Keseluruhan (Tidak hilang saat berganti hari)
        $totalAll = $this->count();
        $pendingAll = $this->count("status = 'Pending'");
        $confirmedAll = $this->count("status = 'Confirmed'");
        $completedAll = $this->count("status = 'Completed'");
        $cancelledAll = $this->count("status = 'Cancelled'");

        // Statistik Waktu
        $totalPast = $this->count("registration_date < :today", ['today' => $today]);
        $totalToday = $this->count("registration_date = :today", ['today' => $today]);
        $totalUpcoming = $this->count("registration_date > :today", ['today' => $today]);

        // Statistik Khusus Hari Ini
        $pendingToday = $this->count("registration_date = :today AND status = 'Pending'", ['today' => $today]);
        $confirmedToday = $this->count("registration_date = :today AND status = 'Confirmed'", ['today' => $today]);
        $completedToday = $this->count("registration_date = :today AND status = 'Completed'", ['today' => $today]);
        $cancelledToday = $this->count("registration_date = :today AND status = 'Cancelled'", ['today' => $today]);

        return [
            // Seluruh Riwayat (All-Time)
            'all_total'       => $totalAll,
            'all_pending'     => $pendingAll,
            'all_confirmed'   => $confirmedAll,
            'all_completed'   => $completedAll,
            'all_cancelled'   => $cancelledAll,

            // Waktu
            'past_total'      => $totalPast,
            'today_total'     => $totalToday,
            'upcoming_total'  => $totalUpcoming,

            // Khusus Hari Ini
            'today_pending'   => $pendingToday,
            'today_confirmed' => $confirmedToday,
            'today_completed' => $completedToday,
            'today_cancelled' => $cancelledToday,
            'all_time'        => $totalAll,
        ];
    }
}
