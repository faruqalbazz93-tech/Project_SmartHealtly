<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Env;
use PDO;

class WhatsAppSetting extends Model
{
    protected string $table = 'whatsapp_settings';

    /**
     * Cache for settings
     */
    private static ?array $cachedSettings = null;

    /**
     * Get all settings as key => value dictionary
     */
    public function getAll(): array
    {
        if (self::$cachedSettings !== null) {
            return self::$cachedSettings;
        }

        $stmt = $this->db->query("SELECT setting_key, setting_value FROM `{$this->table}`");
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        self::$cachedSettings = $results;
        return $results;
    }

    /**
     * Get single setting value by key
     */
    public function get(string $key, ?string $default = null): ?string
    {
        $all = $this->getAll();
        return $all[$key] ?? $default;
    }

    /**
     * Set / update single setting
     */
    public function set(string $key, ?string $value): bool
    {
        $sql = "INSERT INTO `{$this->table}` (`setting_key`, `setting_value`, `updated_at`) 
                VALUES (:k, :v, NOW())
                ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`), `updated_at` = NOW()";
        
        $ok = $this->execute($sql, ['k' => $key, 'v' => $value]);
        self::$cachedSettings = null; // bust cache
        return $ok;
    }

    /**
     * Set multiple settings at once
     */
    public function setMultiple(array $settings): bool
    {
        $sql = "INSERT INTO `{$this->table}` (`setting_key`, `setting_value`, `updated_at`) 
                VALUES (:k, :v, NOW())
                ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`), `updated_at` = NOW()";
        
        $stmt = $this->db->prepare($sql);
        foreach ($settings as $key => $val) {
            $stmt->execute(['k' => (string)$key, 'v' => (string)$val]);
        }

        self::$cachedSettings = null; // bust cache
        return true;
    }

    /**
     * Check if master WhatsApp delivery is enabled
     */
    public function isWhatsAppEnabled(): bool
    {
        return (bool)(int)$this->get('whatsapp_enabled', '1');
    }

    /**
     * Check if a specific notification event type is enabled by Admin
     */
    public function isEventEnabled(string $eventType): bool
    {
        if (!$this->isWhatsAppEnabled()) {
            return false;
        }

        $key = match ($eventType) {
            'registration'    => 'notify_registration_enabled',
            'ticket'          => 'notify_ticket_enabled',
            'status_change'   => 'notify_status_change_enabled',
            'reminder_h1'     => 'notify_reminder_h1_enabled',
            'reminder_h2'     => 'notify_reminder_h2_enabled',
            'reminder'        => 'notify_reminder_h1_enabled', // fallback
            'schedule_change' => 'notify_schedule_change_enabled',
            'cancellation'    => 'notify_cancellation_enabled',
            'announcement'    => 'notify_announcement_enabled',
            default           => null,
        };

        if ($key === null) {
            return true;
        }

        return (bool)(int)$this->get($key, '1');
    }

    /**
     * Get configured Fonnte API Token (from DB setting or .env fallback)
     */
    public function getFonnteToken(): string
    {
        $token = $this->get('fonnte_token');
        if (!empty($token)) {
            return trim($token);
        }

        $envToken = Env::get('FONNTE_TOKEN', Env::get('WA_API_KEY', ''));
        return trim((string)$envToken);
    }

    /**
     * Get dynamic template string by key
     */
    public function getTemplate(string $templateKey): string
    {
        $fullKey = str_starts_with($templateKey, 'template_') ? $templateKey : "template_{$templateKey}";
        $tpl = $this->get($fullKey);
        if (!empty($tpl)) {
            return $tpl;
        }

        // Default fallbacks if empty
        return match ($templateKey) {
            'registration' => "Halo {nama_pasien}, pendaftaran layanan kesehatan Anda di SmartHealthy Puskesmas Astanajapura berhasil disetujui.\n\n*Nomor Antrean:* {nomor_antrean}\n*Kode Booking:* {kode_booking}\n*Layanan Poli:* {nama_layanan}\n*Tanggal Kunjungan:* {tanggal_kunjungan}\n*Jadwal Layanan:* {jadwal_layanan}\n*Dokter Penanggung Jawab:* {nama_dokter}\n\nSilakan datang 15 menit sebelum layanan dimulai dan tunjukkan e-Ticket Anda ke petugas loket.\n\nTerima kasih,\nTim Puskesmas Astanajapura",
            'status_change' => "Halo {nama_pasien}, status pendaftaran antrean Anda di SmartHealthy telah diperbarui oleh petugas Puskesmas Astanajapura.\n\n*Kode Booking:* {kode_booking}\n*Layanan Poli:* {nama_layanan}\n*Status Terbaru:* *{status_baru}*\n*Catatan Petugas:* {catatan}\n\nBuka aplikasi SmartHealthy untuk melihat e-Ticket terbaru Anda.",
            'reminder_h1' => "Halo {nama_pasien}, ini pengingat resmi jadwal kunjungan Anda BESOK di Puskesmas Astanajapura.\n\n*Layanan Poli:* {nama_layanan}\n*Nomor Antrean:* {nomor_antrean}\n*Tanggal Kunjungan:* {tanggal_kunjungan}\n*Jam Operasional:* {jadwal_layanan}\n*Dokter:* {nama_dokter}\n\nHarap membawa kartu identitas (KTP/KK) dan e-Ticket digital Anda.",
            'reminder_h2' => "Halo {nama_pasien}, pengingat kunjungan antrean Puskesmas Astanajapura: Jadwal layanan Anda di *{nama_layanan}* akan dimulai dalam 2 JAM ke depan.\n\n*Nomor Antrean:* {nomor_antrean}\n*Hari Ini:* {tanggal_kunjungan}\n*Waktu:* {jadwal_layanan}\n\nHarap hadir di ruang tunggu poli 15 menit sebelum layanan dimulai.",
            'schedule_change' => "Halo {nama_pasien}, terdapat penyesuaian jadwal pada layanan *{nama_layanan}* di Puskesmas Astanajapura.\n\n*Jadwal Sebelumnya:* {jadwal_lama}\n*Jadwal Terbaru:* {jadwal_baru}\n*Keterangan Petugas:* {alasan}\n\nSilakan periksa detail tiket Anda di aplikasi SmartHealthy.",
            'cancellation' => "Halo {nama_pasien}, pendaftaran antrean Anda di *{nama_layanan}* untuk tanggal {tanggal_kunjungan} (Kode Booking: {kode_booking}) telah DIBATALKAN.\n\n*Alasan Pembatalan:* {catatan}\n\nKuota antrean telah dikembalikan. Anda dapat mendaftar kembali di jadwal lain melalui portal SmartHealthy.",
            'announcement' => "Halo {nama_pasien}, terdapat pengumuman resmi dari Puskesmas Astanajapura:\n\n*{judul_pengumuman}*\n\n{isi_pengumuman}\n\nInformasi lebih lanjut: {tautan}\n\nSalam sehat,\nTim Puskesmas Astanajapura",
            default => "Pemberitahuan dari Puskesmas Astanajapura.",
        };
    }

    /**
     * Render template string by replacing placeholders {key} with actual values
     */
    public function renderTemplate(string $templateKey, array $placeholders): string
    {
        $template = $this->getTemplate($templateKey);

        // Normalize placeholders to support both standard & specific tags
        $patientName = $placeholders['nama'] ?? $placeholders['nama_pasien'] ?? 'Pasien';
        $regNumber = $placeholders['nomor_pendaftaran'] ?? $placeholders['nomor_antrean'] ?? $placeholders['kode_booking'] ?? '-';
        $serviceName = $placeholders['layanan'] ?? $placeholders['nama_layanan'] ?? $placeholders['poli'] ?? 'Poli Layanan';
        $visitDate = $placeholders['tanggal'] ?? $placeholders['tanggal_kunjungan'] ?? date('d F Y');
        $visitTime = $placeholders['jam'] ?? $placeholders['jadwal_layanan'] ?? '08:00 WIB';
        $status = $placeholders['status'] ?? $placeholders['status_baru'] ?? 'Terkonfirmasi';
        $puskesmas = $placeholders['nama_puskesmas'] ?? 'Puskesmas Astanajapura';
        $doctor = $placeholders['nama_dokter'] ?? 'Dokter Jaga Poli';
        $notes = $placeholders['catatan'] ?? $placeholders['alasan'] ?? '-';
        $oldSched = $placeholders['jadwal_lama'] ?? '-';
        $newSched = $placeholders['jadwal_baru'] ?? $visitTime;
        $annTitle = $placeholders['judul_pengumuman'] ?? 'Pengumuman Puskesmas';
        $annContent = $placeholders['isi_pengumuman'] ?? '';
        $link = $placeholders['tautan'] ?? '';

        $replacements = [
            '{nama}'               => (string)$patientName,
            '{nama_pasien}'        => (string)$patientName,
            '{nomor_pendaftaran}'  => (string)$regNumber,
            '{nomor_antrean}'      => (string)$regNumber,
            '{kode_booking}'       => (string)$regNumber,
            '{kode_pendaftaran}'   => (string)$regNumber,
            '{layanan}'            => (string)$serviceName,
            '{nama_layanan}'       => (string)$serviceName,
            '{nama_poli}'          => (string)$serviceName,
            '{poli}'               => (string)$serviceName,
            '{tanggal}'            => (string)$visitDate,
            '{tanggal_kunjungan}'  => (string)$visitDate,
            '{jam}'                => (string)$visitTime,
            '{jadwal_layanan}'     => (string)$visitTime,
            '{jam_layanan}'        => (string)$visitTime,
            '{jam_kunjungan}'      => (string)$visitTime,
            '{status}'             => (string)$status,
            '{status_baru}'        => (string)$status,
            '{nama_puskesmas}'     => (string)$puskesmas,
            '{nama_dokter}'        => (string)$doctor,
            '{catatan}'            => (string)$notes,
            '{alasan}'             => (string)$notes,
            '{jadwal_lama}'        => (string)$oldSched,
            '{jadwal_baru}'        => (string)$newSched,
            '{judul_pengumuman}'   => (string)$annTitle,
            '{isi_pengumuman}'     => (string)$annContent,
            '{tautan}'             => (string)$link,
        ];

        // Also merge any extra custom key-value pairs passed
        foreach ($placeholders as $k => $val) {
            $tag = '{' . trim($k, '{}') . '}';
            if (!isset($replacements[$tag])) {
                $replacements[$tag] = (string)$val;
            }
        }

        return strtr($template, $replacements);
    }
}

