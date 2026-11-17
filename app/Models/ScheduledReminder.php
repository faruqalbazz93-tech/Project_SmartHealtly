<?php

namespace App\Models;

use App\Core\Model;
use App\Models\WhatsAppSetting;
use PDO;
use DateTime;
use DateTimeZone;

class ScheduledReminder extends Model
{
    protected string $table = 'scheduled_reminders';

    /**
     * Schedule H-1 and H-2 Hours reminders for a given registration
     */
    public function scheduleForRegistration(array $reg): bool
    {
        $regId = (int)($reg['id'] ?? $reg['registration_id'] ?? 0);
        $userId = (int)($reg['user_id'] ?? 0);
        $regDate = $reg['registration_date'] ?? date('Y-m-d');
        $startTime = $reg['start_time'] ?? '08:00:00';

        if (!$regId || !$userId) {
            return false;
        }

        $settingModel = new WhatsAppSetting();
        $h1Time = $settingModel->get('reminder_h1_time', '08:00');
        $h2OffsetHours = (int)$settingModel->get('reminder_h2_offset_hours', '2');
        $tzName = $settingModel->get('timezone', 'Asia/Jakarta');

        $tz = new DateTimeZone($tzName);

        // 1. Calculate H-1 Reminder Datetime (1 day before at reminder_h1_time)
        $h1Dt = new DateTime("{$regDate} {$h1Time}:00", $tz);
        $h1Dt->modify('-1 day');
        $h1Formatted = $h1Dt->format('Y-m-d H:i:s');

        // 2. Calculate H-2 Hours Reminder Datetime (visit start time - offset hours)
        $h2Dt = new DateTime("{$regDate} {$startTime}", $tz);
        $h2Dt->modify("-{$h2OffsetHours} hours");
        $h2Formatted = $h2Dt->format('Y-m-d H:i:s');

        $sql = "INSERT INTO `{$this->table}` 
                (`registration_id`, `user_id`, `reminder_type`, `scheduled_for`, `status`, `created_at`) 
                VALUES (:rid, :uid, :rtype, :sched, 'pending', NOW())
                ON DUPLICATE KEY UPDATE 
                    `scheduled_for` = VALUES(`scheduled_for`),
                    `status` = 'pending',
                    `sent_at` = NULL,
                    `cancelled_at` = NULL,
                    `cancel_reason` = NULL,
                    `updated_at` = NOW()";

        // Insert H-1
        $this->execute($sql, [
            'rid'   => $regId,
            'uid'   => $userId,
            'rtype' => 'H-1',
            'sched' => $h1Formatted,
        ]);

        // Insert H-2_HOURS
        $this->execute($sql, [
            'rid'   => $regId,
            'uid'   => $userId,
            'rtype' => 'H-2_HOURS',
            'sched' => $h2Formatted,
        ]);

        return true;
    }

    /**
     * Cancel pending reminders when registration is cancelled
     */
    public function cancelForRegistration(int $registrationId, string $reason = 'Pendaftaran dibatalkan'): int
    {
        $sql = "UPDATE `{$this->table}` 
                SET `status` = 'cancelled', 
                    `cancelled_at` = NOW(), 
                    `cancel_reason` = :reason,
                    `updated_at` = NOW() 
                WHERE `registration_id` = :rid 
                  AND `status` = 'pending'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'rid'    => $registrationId,
            'reason' => $reason,
        ]);

        return $stmt->rowCount();
    }

    /**
     * Reschedule reminders when registration date or time changes
     */
    public function rescheduleForRegistration(int $registrationId, string $newDate, ?string $startTime = null): bool
    {
        // Cancel pending old reminders
        $this->cancelForRegistration($registrationId, 'Jadwal kunjungan disesuaikan ke tanggal baru');

        // Re-fetch registration details to rebuild
        $stmt = $this->db->prepare("
            SELECT r.*, s.start_time 
            FROM `registrations` r 
            LEFT JOIN `schedules` s ON r.schedule_id = s.id 
            WHERE r.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $registrationId]);
        $reg = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reg) {
            return false;
        }

        $reg['registration_date'] = $newDate;
        if ($startTime) {
            $reg['start_time'] = $startTime;
        }

        return $this->scheduleForRegistration($reg);
    }

    /**
     * Fetch pending reminders that are due for delivery
     */
    public function getDueReminders(int $limit = 50): array
    {
        $settingModel = new WhatsAppSetting();
        $tzName = $settingModel->get('timezone', 'Asia/Jakarta');
        date_default_timezone_set($tzName);
        $now = date('Y-m-d H:i:s');

        $sql = "SELECT sr.*, 
                       r.registration_code, r.queue_number, r.registration_date, r.status as reg_status,
                       hs.name as service_name, hs.doctor_name, 
                       s.start_time, s.end_time,
                       u.name as patient_name, u.phone as patient_phone, u.phone_verified_at
                FROM `{$this->table}` sr
                JOIN `registrations` r ON sr.registration_id = r.id
                JOIN `health_services` hs ON r.health_service_id = hs.id
                LEFT JOIN `schedules` s ON r.schedule_id = s.id
                JOIN `users` u ON sr.user_id = u.id
                WHERE sr.status = 'pending'
                  AND sr.scheduled_for <= :now
                  AND r.status IN ('Pending', 'Confirmed')
                ORDER BY sr.scheduled_for ASC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':now', $now);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mark reminder as successfully sent
     */
    public function markSent(int $id): bool
    {
        $sql = "UPDATE `{$this->table}` 
                SET `status` = 'sent', `sent_at` = NOW(), `updated_at` = NOW() 
                WHERE `id` = :id";
        return $this->execute($sql, ['id' => $id]);
    }

    /**
     * Mark reminder as failed
     */
    public function markFailed(int $id, string $reason): bool
    {
        $sql = "UPDATE `{$this->table}` 
                SET `status` = 'failed', `cancel_reason` = :reason, `updated_at` = NOW() 
                WHERE `id` = :id";
        return $this->execute($sql, ['id' => $id, 'reason' => $reason]);
    }
}
