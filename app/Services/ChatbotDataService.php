<?php

namespace App\Services;

use App\Config\App;
use App\Core\Auth;

class ChatbotDataService
{
    private ChatbotRepository $repository;

    public function __construct()
    {
        $this->repository = new ChatbotRepository();
    }

    /**
     * Build authorized, sanitized context data block for Gemini
     * Treats all database values as data, not instructions
     */
    public function buildGroundingContext(string $userMessage): string
    {
        $puskesmas = App::puskesmas();

        $contextLines = [];
        $contextLines[] = "<smarthealthy_data>";
        $contextLines[] = "PUSKESMAS_INFO:";
        $contextLines[] = "Nama: " . htmlspecialchars($puskesmas['name']);
        $contextLines[] = "Wilayah: " . htmlspecialchars($puskesmas['district']) . ", " . htmlspecialchars($puskesmas['regency']);
        $contextLines[] = "Alamat: " . htmlspecialchars($puskesmas['address']);
        $contextLines[] = "Telepon: " . htmlspecialchars($puskesmas['phone']);
        $contextLines[] = "Hotline/IGD/WA: " . htmlspecialchars($puskesmas['hotline']);
        $contextLines[] = "Email: " . htmlspecialchars($puskesmas['email']);
        $contextLines[] = "Sistem: SmartHealthy (Pendaftaran Poli & Antrean Online Gratis Berbasis Web)";
        $contextLines[] = "";

        // 1. Health Services & Doctors
        $services = $this->repository->getActiveHealthServices();
        $contextLines[] = "LAYANAN_POLI_AKTIF:";
        foreach ($services as $s) {
            $desc = !empty($s['description']) ? " - " . strip_tags($s['description']) : "";
            $contextLines[] = "- {$s['name']} (Kode: {$s['code']}) | Dokter/PJ: {$s['doctor_name']} | Kuota Harian: {$s['quota_daily']} pasien{$desc}";
        }
        $contextLines[] = "";

        // 2. Operational Schedules
        $schedules = $this->repository->getActiveSchedules();
        if (!empty($schedules)) {
            $contextLines[] = "JADWAL_OPERASIONAL_POLI:";
            foreach ($schedules as $sch) {
                $contextLines[] = "- {$sch['service_name']}: Hari {$sch['day_of_week']} (Jam {$sch['start_time']} - {$sch['end_time']}) | Kuota: {$sch['max_quota']}";
            }
            $contextLines[] = "";
        }

        // 3. Overall Database Statistics (Registrations & Status)
        $regStats = $this->repository->getOverallRegistrationStatistics();
        $summary = $regStats['summary'];
        $contextLines[] = "REKAPITULASI_DATABASE_DAN_STATISTIK_PENDAFTARAN:";
        $contextLines[] = "- Total Keseluruhan Pendaftaran Pasien: {$summary['total_all']} pendaftaran";
        $contextLines[] = "- Pendaftaran Hari Ini: {$summary['total_today']} pendaftaran";
        $contextLines[] = "- Status Selesai Diperiksa (Completed): {$summary['completed_count']} pasien";
        $contextLines[] = "- Status Dikonfirmasi (Confirmed): {$summary['confirmed_count']} pasien";
        $contextLines[] = "- Status Menunggu Panggilan (Pending): {$summary['pending_count']} pasien";
        $contextLines[] = "- Status Dibatalkan (Cancelled): {$summary['cancelled_count']} pendaftaran";
        $contextLines[] = "Rincian Pendaftaran per Poli Layanan:";
        foreach ($regStats['by_service'] as $bs) {
            $contextLines[] = "  * {$bs['service_name']} (Dokter: {$bs['doctor_name']}): Total {$bs['booking_count']} booking, Hari Ini {$bs['today_count']}, Selesai {$bs['completed_count']}";
        }
        $contextLines[] = "";

        // 4. User Demographics & 12 Villages
        $userStats = $this->repository->getUsersAndDemographicsStatistics();
        $contextLines[] = "STATISTIK_PENGGUNA_DAN_WILAYAH_12_DESA:";
        $contextLines[] = "- Total Pasien Terdaftar: {$userStats['total_patients']} pasien";
        $contextLines[] = "- Total Petugas Administrator: {$userStats['total_staff']} petugas";
        $contextLines[] = "- Pasien Terverifikasi (Email/WA): {$userStats['total_verified']} akun";
        $contextLines[] = "Sebaran Pasien di 12 Desa Kecamatan Astanajapura:";
        foreach ($userStats['villages'] as $vil) {
            $contextLines[] = "  * Desa {$vil['village_name']}: {$vil['patient_count']} pasien";
        }
        $contextLines[] = "";

        // 5. Authenticated User Registrations
        if (Auth::check()) {
            $user = Auth::user();
            $userId = (int)Auth::id();
            $isAdmin = Auth::isAdmin();

            if ($isAdmin) {
                $contextLines[] = "STATUS_PENGGUNA: PETUGAS ADMINISTRATOR PUSKESMAS (LOGIN SEBAGAI ADMIN)";
                $contextLines[] = "Nama: " . htmlspecialchars($user['name'] ?? 'Admin Puskesmas');
                $contextLines[] = "Peran: Administrator / Petugas Loket Puskesmas Astanajapura.";
                $contextLines[] = "Catatan Akses: Pengguna ini adalah Admin resmi dan memiliki hak untuk mengetahui rekapitulasi data pendaftaran pasien, jadwal dokter, statistik kunjungan per desa, dan operasional Puskesmas.";
            } else {
                $contextLines[] = "STATUS_PENGGUNA: TERAUTENTIKASI (PASIEN LOGIN)";
                $contextLines[] = "Nama Pasien: " . htmlspecialchars($user['name'] ?? 'Pasien');
                $contextLines[] = "Desa Domisili: " . htmlspecialchars($user['village_name'] ?? 'Astanajapura');
            }

            $registrations = $this->repository->getUserRegistrations($userId, 5);
            if (!empty($registrations)) {
                $contextLines[] = "RIWAYAT_PENDAFTARAN_PRIBADI_AKUN_INI:";
                foreach ($registrations as $r) {
                    $contextLines[] = "- Kode: {$r['registration_code']} | Poli: {$r['service_name']} | No. Antrean: #{$r['queue_number']} | Tgl: {$r['registration_date']} | Status: {$r['status']} | Keluhan: {$r['complaint']}";
                }
            } else {
                $contextLines[] = "RIWAYAT_PENDAFTARAN_PRIBADI_AKUN_INI: Akun ini belum memiliki pendaftaran antrean aktif pribadi.";
            }

            // WhatsApp Notification Status & Consent
            $waStatus = $this->repository->getUserWhatsAppStatus($userId);
            if (!empty($waStatus)) {
                $maskedPhone = \App\Models\WhatsAppMessageLog::maskPhone($waStatus['phone']);
                $verStatus = $waStatus['is_phone_verified'] ? "Terverifikasi" : "Belum Diverifikasi";
                $consentStatus = $waStatus['whatsapp_enabled'] 
                    ? "Aktif (Disetujui pada " . ($waStatus['whatsapp_consent_at'] ?: 'sebelumnya') . ")" 
                    : "Tidak Aktif (Pengguna belum mengaktifkan atau menonaktifkan persetujuan)";
                
                $activeChannels = [];
                if ($waStatus['notify_registration']) $activeChannels[] = 'Pendaftaran & Tiket';
                if ($waStatus['notify_reminder']) $activeChannels[] = 'Pengingat Kunjungan';
                if ($waStatus['notify_schedule_change']) $activeChannels[] = 'Perubahan Jadwal Layanan';
                if ($waStatus['notify_announcement']) $activeChannels[] = 'Pengumuman Puskesmas';
                if ($waStatus['notify_education']) $activeChannels[] = 'Edukasi Kesehatan';
                $channelsStr = !empty($activeChannels) ? implode(', ', $activeChannels) : 'Tidak ada jenis notifikasi yang dicentang';

                $contextLines[] = "STATUS_NOTIFIKASI_WHATSAPP_PENGGUNA:";
                $contextLines[] = "- Nomor WhatsApp Terdaftar: {$maskedPhone}";
                $contextLines[] = "- Status Verifikasi Nomor: {$verStatus}";
                $contextLines[] = "- Status Izin Notifikasi WhatsApp (Consent): {$consentStatus}";
                $contextLines[] = "- Jenis Notifikasi yang Dipilih: {$channelsStr}";
                $contextLines[] = "- Menu Pengaturan: Pengguna dapat mengaktifkan/menonaktifkan atau mengubah preferensi di tautan: /pengaturan/notifikasi";

                if (!empty($waStatus['recent_logs'])) {
                    $contextLines[] = "- Log Pengiriman WhatsApp Terakhir:";
                    foreach ($waStatus['recent_logs'] as $log) {
                        $contextLines[] = "  * Template '{$log['template_name']}' ({$log['message_type']}) pada {$log['sent_at']} - Status: {$log['delivery_status']}";
                    }
                } else {
                    $contextLines[] = "- Log Pengiriman WhatsApp Terakhir: Belum ada notifikasi yang dikirimkan.";
                }
            }
        } else {
            $contextLines[] = "STATUS_PENGGUNA: PENGUNJUNG TAMU (BELUM LOGIN)";
            $contextLines[] = "Pengguna belum login. Jelaskan bahwa untuk melihat antrean pribadi atau mendaftar poli, silakan Login di menu /login atau Register di /register.";
        }
        $contextLines[] = "";

        // 6. Recent Announcements
        $announcements = $this->repository->getRecentAnnouncements(3);
        if (!empty($announcements)) {
            $contextLines[] = "PENGUMUMAN_RESMI_TERKINI:";
            foreach ($announcements as $ann) {
                $contextLines[] = "- {$ann['title']}: {$ann['message']}";
            }
            $contextLines[] = "";
        }

        // 7. Articles
        $articles = $this->repository->getPublishedArticles(4);
        if (!empty($articles)) {
            $contextLines[] = "ARTIKEL_EDUKASI_KESEHATAN:";
            foreach ($articles as $a) {
                $contextLines[] = "- {$a['title']} ({$a['category_name']}): {$a['summary']}";
            }
            $contextLines[] = "";
        }

        // 8. Quiz Questions
        $questions = $this->repository->getActiveQuizQuestions(5);
        if (!empty($questions)) {
            $contextLines[] = "BANK_SOAL_KUIS_EDUKASI:";
            foreach ($questions as $q) {
                $contextLines[] = "- Soal: {$q['question_text']} ({$q['category_name']}) - Penjelasan: {$q['explanation']}";
            }
            $contextLines[] = "";
        }

        $contextLines[] = "</smarthealthy_data>";

        return implode("\n", $contextLines);
    }
}
