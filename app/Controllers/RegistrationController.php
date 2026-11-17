<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Core\Validator;
use App\Models\HealthService;
use App\Models\Schedule;
use App\Models\Registration;
use App\Models\User;
use App\Config\App;

class RegistrationController extends Controller
{
    /**
     * Browse Active Health Services (Public & Patient)
     */
    public function indexServices(): void
    {
        $serviceModel = new HealthService();
        $scheduleModel = new Schedule();

        $services = $serviceModel->getActiveServices();
        $schedules = $scheduleModel->getAllWithServices();

        $schedulesByService = [];
        foreach ($schedules as $s) {
            $schedulesByService[$s['health_service_id']][] = $s;
        }

        $this->render('registration/services', [
            'pageTitle'          => 'Layanan Poli Kesehatan - SmartHealthy',
            'services'           => $services,
            'schedulesByService' => $schedulesByService,
            'puskesmas'          => App::puskesmas(),
        ], Auth::check() ? (Auth::isAdmin() ? 'layouts/admin' : 'layouts/user') : 'layouts/main');
    }

    /**
     * Browse Doctor Schedules (Public & Patient)
     */
    public function indexSchedules(): void
    {
        $scheduleModel = new Schedule();
        $schedules = $scheduleModel->getAllWithServices();

        $this->render('registration/schedules', [
            'pageTitle' => 'Jadwal Dokter & Jam Buka Poli - SmartHealthy',
            'schedules' => $schedules,
            'puskesmas' => App::puskesmas(),
        ], Auth::check() ? (Auth::isAdmin() ? 'layouts/admin' : 'layouts/user') : 'layouts/main');
    }

    /**
     * Step 1: Pendaftaran Form
     */
    public function create(): void
    {
        Auth::requireAuth();

        if (Auth::isAdmin()) {
            $this->redirect('/admin/pendaftaran');
            return;
        }

        $userId = Auth::id();
        $userModel = new User();
        $serviceModel = new HealthService();
        $scheduleModel = new Schedule();

        $user = $userModel->findWithVillage($userId);
        $services = $serviceModel->getActiveServices();

        $selectedServiceId = $this->input('poli') ? (int) $this->input('poli') : ($services[0]['id'] ?? 1);
        $selectedDate = $this->input('tanggal', date('Y-m-d'));

        // Calculate day name
        $dayNames = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
            5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
        ];
        $dayIndex = (int) date('N', strtotime($selectedDate));
        $dayName = $dayNames[$dayIndex] ?? 'Senin';

        $activeSchedules = $scheduleModel->getActiveByService($selectedServiceId);
        $scheduleToday = $scheduleModel->getForServiceAndDay($selectedServiceId, $dayName);

        $regModel = new Registration();
        $quotaInfo = $regModel->getQuotaInfo($selectedServiceId, $scheduleToday['id'] ?? null, $selectedDate);

        $this->render('registration/create', [
            'pageTitle'         => 'Formulir Pendaftaran Antrean - SmartHealthy',
            'user'              => $user,
            'services'          => $services,
            'selectedServiceId' => $selectedServiceId,
            'selectedDate'      => $selectedDate,
            'dayName'           => $dayName,
            'activeSchedules'   => $activeSchedules,
            'scheduleToday'     => $scheduleToday,
            'quotaInfo'         => $quotaInfo,
            'isVerified'        => Auth::isVerified(),
        ], 'layouts/user');
    }

    /**
     * AJAX endpoint to check real-time schedule & remaining quota
     */
    public function getQuotaAjax(): void
    {
        $serviceId = (int) $this->input('service_id');
        $date = $this->input('date', date('Y-m-d'));

        if (!$serviceId || !$date) {
            $this->json(['status' => false, 'message' => 'Parameter tidak lengkap.'], 400);
            return;
        }

        $dayNames = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
            5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
        ];
        $dayIndex = (int) date('N', strtotime($date));
        $dayName = $dayNames[$dayIndex] ?? 'Senin';

        $scheduleModel = new Schedule();
        $schedule = $scheduleModel->getForServiceAndDay($serviceId, $dayName);

        $regModel = new Registration();
        $quotaInfo = $regModel->getQuotaInfo($serviceId, $schedule['id'] ?? null, $date);

        $this->json([
            'status'     => true,
            'day_name'   => $dayName,
            'has_schedule' => !empty($schedule),
            'schedule'   => $schedule,
            'quota'      => $quotaInfo,
        ]);
    }

    /**
     * Step 2: Konfirmasi Pendaftaran (Preview Summary)
     */
    public function confirm(): void
    {
        Auth::requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $userId = Auth::id();
        $serviceId = (int) $this->input('health_service_id');
        $scheduleId = $this->input('schedule_id') ? (int) $this->input('schedule_id') : null;
        $regDate = $this->input('registration_date');
        $complaint = trim((string)$this->input('complaint'));

        // Basic Validation
        $validator = Validator::make([
            'health_service_id' => $serviceId,
            'registration_date' => $regDate,
            'complaint'         => $complaint,
        ], [
            'health_service_id' => 'required|exists:health_services,id',
            'registration_date' => 'required',
            'complaint'         => 'required|min:5|max:1000',
        ]);

        if ($validator->fails()) {
            $this->flashAndRedirect('danger', $validator->firstError(), '/pendaftaran/baru');
            return;
        }

        // Validate date >= today
        if ($regDate < date('Y-m-d')) {
            $this->flashAndRedirect('danger', 'Tanggal kunjungan tidak boleh di masa lalu.', '/pendaftaran/baru');
            return;
        }

        $userModel = new User();
        $serviceModel = new HealthService();
        $scheduleModel = new Schedule();
        $regModel = new Registration();

        $user = $userModel->findWithVillage($userId);
        $service = $serviceModel->find($serviceId);

        // Check duplicate booking
        if ($regModel->userHasActiveBooking($userId, $serviceId, $regDate)) {
            $this->flashAndRedirect('warning', "Anda sudah memiliki pendaftaran aktif di {$service['name']} pada tanggal {$regDate}.", '/pendaftaran/riwayat');
            return;
        }

        $dayNames = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
            5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
        ];
        $dayName = $dayNames[(int)date('N', strtotime($regDate))] ?? 'Senin';
        $schedule = $scheduleId ? $scheduleModel->find($scheduleId) : $scheduleModel->getForServiceAndDay($serviceId, $dayName);

        $quotaInfo = $regModel->getQuotaInfo($serviceId, $schedule['id'] ?? null, $regDate);

        if ($quotaInfo['is_full']) {
            $this->flashAndRedirect('danger', "Mohon maaf, kuota pendaftaran untuk {$service['name']} pada tanggal {$regDate} sudah penuh.", '/pendaftaran/baru');
            return;
        }

        $this->render('registration/confirm', [
            'pageTitle'         => 'Konfirmasi Pendaftaran Antrean - SmartHealthy',
            'user'              => $user,
            'service'           => $service,
            'schedule'          => $schedule,
            'regDate'           => $regDate,
            'dayName'           => $dayName,
            'complaint'         => $complaint,
            'quotaInfo'         => $quotaInfo,
            'puskesmas'         => App::puskesmas(),
        ], 'layouts/user');
    }

    /**
     * Step 3: Execute Transaction & Store Registration
     */
    public function store(): void
    {
        Auth::requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $userId = Auth::id();
        $serviceId = (int) $this->input('health_service_id');
        $scheduleId = $this->input('schedule_id') ? (int) $this->input('schedule_id') : null;
        $regDate = $this->input('registration_date');
        $complaint = trim((string)$this->input('complaint'));

        $regModel = new Registration();
        $result = $regModel->createRegistrationTransaction([
            'user_id'           => $userId,
            'health_service_id' => $serviceId,
            'schedule_id'       => $scheduleId,
            'registration_date' => $regDate,
            'complaint'         => $complaint,
        ]);

        if ($result['status']) {
            // Trigger in-app notification & WhatsApp ticket receipt
            \App\Services\NotificationService::notifyRegistrationCreated([
                'id'                => $result['registration_id'],
                'user_id'           => $userId,
                'registration_code' => $result['registration_code'],
                'queue_number'      => $result['queue_number'],
                'service_name'      => $result['service_name'],
                'registration_date' => $result['registration_date'],
            ]);

            Session::setFlash('success', "Pendaftaran berhasil! Nomor antrean Anda adalah <strong>#{$result['queue_number']}</strong>.");
            $this->redirect('/pendaftaran/sukses/' . $result['registration_code']);
        } else {
            $this->flashAndRedirect('danger', $result['message'], '/pendaftaran/baru');
        }
    }

    /**
     * Step 4: Success Celebration Page
     */
    public function success(string $code): void
    {
        Auth::requireAuth();

        $regModel = new Registration();
        $registration = $regModel->findByCodeWithDetails($code);

        if (!$registration || (!Auth::isAdmin() && $registration['user_id'] != Auth::id())) {
            $this->flashAndRedirect('danger', 'Data pendaftaran tidak ditemukan atau akses ditolak.', '/pendaftaran/riwayat');
            return;
        }

        $this->render('registration/success', [
            'pageTitle'    => 'Pendaftaran Berhasil - SmartHealthy',
            'reg'          => $registration,
            'puskesmas'    => App::puskesmas(),
        ], 'layouts/user');
    }

    /**
     * Digital E-Ticket & Print View
     */
    public function ticket(string $code): void
    {
        Auth::requireAuth();

        $regModel = new Registration();
        $registration = $regModel->findByCodeWithDetails($code);

        if (!$registration) {
            $this->flashAndRedirect('danger', 'Tiket pendaftaran tidak ditemukan.', '/pendaftaran/riwayat');
            return;
        }

        // Ownership authorization check
        if (!Auth::checkOwnership((int)$registration['user_id'])) {
            $this->flashAndRedirect('danger', 'Akses ditolak. Anda tidak memiliki izin mengakses tiket ini.', '/pendaftaran/riwayat');
            return;
        }

        $this->render('registration/ticket', [
            'pageTitle' => 'E-Ticket ' . $registration['registration_code'] . ' - SmartHealthy',
            'reg'       => $registration,
            'puskesmas' => App::puskesmas(),
        ], Auth::isAdmin() ? 'layouts/admin' : 'layouts/user');
    }

    /**
     * History of User Registrations with Status Tabs
     */
    public function history(): void
    {
        Auth::requireAuth();
        $userId = (int) Auth::id();

        $rawStatus = (string) $this->input('status', 'all');
        $statusMap = [
            'menunggu'     => 'Pending',
            'pending'      => 'Pending',
            'dikonfirmasi' => 'Confirmed',
            'confirmed'    => 'Confirmed',
            'selesai'      => 'Completed',
            'completed'    => 'Completed',
            'dibatalkan'   => 'Cancelled',
            'cancelled'    => 'Cancelled',
            'all'          => 'all',
            'semua'        => 'all',
        ];
        $statusFilter = $statusMap[strtolower($rawStatus)] ?? (in_array($rawStatus, ['Pending', 'Confirmed', 'Completed', 'Cancelled', 'all'], true) ? $rawStatus : 'all');

        $regModel = new Registration();
        $registrations = $regModel->getByUserFiltered($userId, $statusFilter);

        // Summary counts for filter badges
        $counts = [
            'all'       => $regModel->count("user_id = :uid", ['uid' => $userId]),
            'Pending'   => $regModel->count("user_id = :uid AND status = 'Pending'", ['uid' => $userId]),
            'Confirmed' => $regModel->count("user_id = :uid AND status = 'Confirmed'", ['uid' => $userId]),
            'Completed' => $regModel->count("user_id = :uid AND status = 'Completed'", ['uid' => $userId]),
            'Cancelled' => $regModel->count("user_id = :uid AND status = 'Cancelled'", ['uid' => $userId]),
        ];

        $this->render('registration/history', [
            'pageTitle'     => 'Riwayat Pendaftaran Saya - SmartHealthy',
            'registrations' => $registrations,
            'statusFilter'  => $statusFilter,
            'counts'        => $counts,
        ], 'layouts/user');
    }

    /**
     * Cancel a Pending Registration
     */
    public function cancel(int $id): void
    {
        Auth::requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $regModel = new Registration();
        $registration = $regModel->find($id);

        if (!$registration || !Auth::checkOwnership((int)$registration['user_id'])) {
            $this->flashAndRedirect('danger', 'Data pendaftaran tidak valid atau akses ditolak.', '/pendaftaran/riwayat');
            return;
        }

        if (!in_array($registration['status'], ['Pending', 'Confirmed'], true)) {
            $this->flashAndRedirect('danger', 'Hanya pendaftaran berstatus Pending atau Confirmed yang dapat dibatalkan.', '/pendaftaran/riwayat');
            return;
        }

        $regModel->updateStatus($id, 'Cancelled', 'Dibatalkan oleh pasien melalui portal web.');

        // Enrich registration data for notification
        if (empty($registration['service_name']) && !empty($registration['health_service_id'])) {
            $serviceModel = new HealthService();
            $srv = $serviceModel->find((int)$registration['health_service_id']);
            $registration['service_name'] = $srv['name'] ?? 'Poli Layanan';
        }
        \App\Services\NotificationService::notifyRegistrationCancelled($registration, 'user');

        $this->flashAndRedirect('success', 'Pendaftaran antrean telah berhasil dibatalkan.', '/pendaftaran/riwayat');
    }
}
