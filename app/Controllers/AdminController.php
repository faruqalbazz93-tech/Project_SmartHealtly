<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\User;
use App\Models\Registration;
use App\Models\HealthService;
use App\Models\Schedule;
use App\Models\Village;
use App\Models\HealthArticle;
use App\Models\HealthCategory;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\AiLog;
use App\Config\App;

class AdminController extends Controller
{
    public function __construct()
    {
        Auth::requireAdmin();
    }

    public function dashboard(): void
    {
        $regModel = new Registration();
        $serviceModel = new HealthService();
        $userModel = new User();
        $villageModel = new Village();
        $aiLogModel = new AiLog();

        $today = date('Y-m-d');
        $stats = $regModel->getDashboardStats();
        $stats['total_users'] = $userModel->count("role = 'user'");
        $stats['total_services'] = $serviceModel->count("status = 'active'");
        $stats['total_villages'] = $villageModel->count();
        $stats['total_ai_logs'] = $aiLogModel->count();

        // Parameter filter timeframe (default: 'all' agar seluruh riwayat terdata dan tidak hilang saat berganti hari)
        $timeframe = (string)$this->input('timeframe', 'all');
        $statusFilter = (string)$this->input('status', 'all');
        $search = trim((string)$this->input('q', ''));

        // Query seluruh riwayat pendaftaran (sebelumnya, hari ini, dan seterusnya)
        $registrations = $regModel->getFilteredForAdmin('', null, $statusFilter, $search, $timeframe);
        $serviceStats = $serviceModel->getWithTodayQueueStats($today);
        $villageStats = $userModel->getDemographicsByVillage();

        $this->render('admin/dashboard', [
            'pageTitle'     => 'Dashboard Admin - SmartHealthy Puskesmas Astanajapura',
            'stats'         => $stats,
            'registrations' => $registrations,
            'timeframe'     => $timeframe,
            'statusFilter'  => $statusFilter,
            'search'        => $search,
            'serviceStats'  => $serviceStats,
            'villageStats'  => $villageStats,
            'today'         => $today,
        ], 'layouts/admin');
    }

    public function registrations(): void
    {
        $regModel = new Registration();
        $serviceModel = new HealthService();

        // Default tanggal kosong / all agar seluruh riwayat pendaftaran dapat dilihat tanpa batasan tanggal
        $date = (string)$this->input('tanggal', '');
        $timeframe = (string)$this->input('timeframe', empty($date) ? 'all' : '');
        $serviceId = $this->input('poli') ? (int) $this->input('poli') : null;
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
        $status = $statusMap[strtolower($rawStatus)] ?? (in_array($rawStatus, ['Pending', 'Confirmed', 'Completed', 'Cancelled', 'all'], true) ? $rawStatus : 'all');
        $search = trim((string)$this->input('q', ''));

        $registrations = $regModel->getFilteredForAdmin($date, $serviceId, $status, $search, $timeframe);
        $services = $serviceModel->getActiveServices();

        $today = date('Y-m-d');
        if (!empty($date) && $date !== 'all') {
            $countDateCondition = "registration_date = :cdate";
            $countParams = ['cdate' => $date];
        } elseif ($timeframe === 'today') {
            $countDateCondition = "registration_date = :cdate";
            $countParams = ['cdate' => $today];
        } elseif ($timeframe === 'past') {
            $countDateCondition = "registration_date < :cdate";
            $countParams = ['cdate' => $today];
        } elseif ($timeframe === 'upcoming') {
            $countDateCondition = "registration_date > :cdate";
            $countParams = ['cdate' => $today];
        } else {
            $countDateCondition = "1=1";
            $countParams = [];
        }

        $statusCounts = [
            'all'       => $regModel->count($countDateCondition, $countParams),
            'Pending'   => $regModel->count("{$countDateCondition} AND status = 'Pending'", $countParams),
            'Confirmed' => $regModel->count("{$countDateCondition} AND status = 'Confirmed'", $countParams),
            'Completed' => $regModel->count("{$countDateCondition} AND status = 'Completed'", $countParams),
            'Cancelled' => $regModel->count("{$countDateCondition} AND status = 'Cancelled'", $countParams),
        ];

        $this->render('admin/registrations', [
            'pageTitle'     => 'Kelola Antrean & Pendaftaran Pasien - Admin',
            'registrations' => $registrations,
            'services'      => $services,
            'filterDate'    => $date,
            'timeframe'     => $timeframe,
            'filterService' => $serviceId,
            'filterStatus'  => $status,
            'search'        => $search,
            'statusCounts'  => $statusCounts,
        ], 'layouts/admin');
    }

    public function updateRegistrationStatus(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $id = (int) $this->input('id');
        $status = $this->input('status');
        $notes = $this->input('notes');

        $allowedStatuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];
        if (!in_array($status, $allowedStatuses, true)) {
            $this->flashAndRedirect('danger', 'Status tidak valid.', '/admin/pendaftaran');
            return;
        }

        $regModel = new Registration();
        $registration = $regModel->find($id);
        if ($registration) {
            $serviceModel = new HealthService();
            $srv = $serviceModel->find((int)$registration['health_service_id']);
            $registration['service_name'] = $srv['name'] ?? 'Layanan Poli';
        }

        $regModel->updateStatus($id, $status, $notes);

        if ($registration) {
            \App\Services\NotificationService::notifyRegistrationStatusChanged($registration, $status, $notes);
        }

        $this->flashAndRedirect('success', "Status pendaftaran berhasil diperbarui menjadi: <strong>{$status}</strong>.", '/admin/pendaftaran');
    }

    public function services(): void
    {
        $serviceModel = new HealthService();
        $services = $serviceModel->all('id ASC');

        $this->render('admin/services', [
            'pageTitle' => 'Kelola Layanan Poli - Admin',
            'services'  => $services,
        ], 'layouts/admin');
    }

    public function saveService(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $id = $this->input('id');
        $code = strtoupper((string)$this->input('code'));
        $name = $this->input('name');
        $doctorName = $this->input('doctor_name');
        $quota = (int) $this->input('quota_daily', 50);
        $icon = $this->input('icon', 'bi-heart-pulse');
        $status = $this->input('status', 'active');
        $description = $this->input('description');

        $serviceModel = new HealthService();
        $data = [
            'code'        => $code,
            'name'        => $name,
            'doctor_name' => $doctorName,
            'quota_daily' => $quota,
            'icon'        => $icon,
            'status'      => $status,
            'description' => $description,
        ];

        if ($id) {
            $serviceModel->update((int) $id, $data);
            $this->flashAndRedirect('success', 'Data layanan poli berhasil diperbarui.', '/admin/layanan');
        } else {
            $serviceModel->create($data);
            $this->flashAndRedirect('success', 'Layanan poli baru berhasil ditambahkan.', '/admin/layanan');
        }
    }

    public function toggleServiceStatus(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $serviceModel = new HealthService();
        $service = $serviceModel->find($id);

        if ($service) {
            $newStatus = ($service['status'] === 'active') ? 'inactive' : 'active';
            $serviceModel->toggleStatus($id, $newStatus);
            $this->flashAndRedirect('success', "Status layanan {$service['name']} berhasil diubah menjadi {$newStatus}.", '/admin/layanan');
        } else {
            $this->flashAndRedirect('danger', 'Layanan poli tidak ditemukan.', '/admin/layanan');
        }
    }

    public function schedules(): void
    {
        $scheduleModel = new Schedule();
        $serviceModel = new HealthService();

        $schedules = $scheduleModel->getAllWithServices();
        $services = $serviceModel->getActiveServices();

        $this->render('admin/schedules', [
            'pageTitle' => 'Kelola Jadwal Dokter & Kuota Poli - Admin',
            'schedules' => $schedules,
            'services'  => $services,
        ], 'layouts/admin');
    }

    public function saveSchedule(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $id = $this->input('id');
        $serviceId = (int) $this->input('health_service_id');
        $dayOfWeek = $this->input('day_of_week');
        $startTime = $this->input('start_time');
        $endTime = $this->input('end_time');
        $maxQuota = (int) $this->input('max_quota', 50);
        $isActive = (int) $this->input('is_active', 1);

        $scheduleModel = new Schedule();
        $data = [
            'health_service_id' => $serviceId,
            'day_of_week'       => $dayOfWeek,
            'start_time'        => $startTime,
            'end_time'          => $endTime,
            'max_quota'         => $maxQuota,
            'is_active'         => $isActive,
        ];

        if ($id) {
            $scheduleModel->update((int) $id, $data);
            \App\Services\NotificationService::notifyScheduleChanged($serviceId, $data, 'Penyesuaian jadwal dokter & kuota layanan');
            $this->flashAndRedirect('success', 'Jadwal dokter berhasil diperbarui.', '/admin/jadwal');
        } else {
            $scheduleModel->create($data);
            $this->flashAndRedirect('success', 'Jadwal baru berhasil ditambahkan.', '/admin/jadwal');
        }
    }

    public function deleteSchedule(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $scheduleModel = new Schedule();
        $scheduleModel->delete($id);
        $this->flashAndRedirect('success', 'Jadwal dokter berhasil dihapus.', '/admin/jadwal');
    }

    public function villages(): void
    {
        $villageModel = new Village();
        $villages = $villageModel->getWithStats();

        $this->render('admin/villages', [
            'pageTitle' => 'Wilayah Desa Astanajapura - Admin',
            'villages'  => $villages,
        ], 'layouts/admin');
    }

    public function categories(): void
    {
        $categoryModel = new HealthCategory();
        $categories = $categoryModel->allOrdered('name ASC');

        $this->render('admin/categories', [
            'pageTitle'  => 'Kelola Kategori Edukasi - Admin',
            'categories' => $categories,
        ], 'layouts/admin');
    }

    public function saveCategory(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $id = $this->input('id');
        $name = $this->input('name');
        $description = $this->input('description');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $categoryModel = new HealthCategory();
        $data = [
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
        ];

        if ($id) {
            $categoryModel->update((int) $id, $data);
            $this->flashAndRedirect('success', 'Kategori berhasil diperbarui.', '/admin/kategori');
        } else {
            $categoryModel->create($data);
            $this->flashAndRedirect('success', 'Kategori baru berhasil ditambahkan.', '/admin/kategori');
        }
    }

    public function deleteCategory(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $categoryModel = new HealthCategory();
        $categoryModel->delete($id);
        $this->flashAndRedirect('success', 'Kategori berhasil dihapus.', '/admin/kategori');
    }

    public function articles(): void
    {
        $articleModel = new HealthArticle();
        $categoryModel = new HealthCategory();

        $articles = $articleModel->getAllWithCategory();
        $categories = $categoryModel->allOrdered('name ASC');

        $this->render('admin/articles', [
            'pageTitle'  => 'Kelola Artikel Edukasi Kesehatan - Admin',
            'articles'   => $articles,
            'categories' => $categories,
        ], 'layouts/admin');
    }

    public function saveArticle(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $id = $this->input('id');
        $title = $this->input('title');
        $categoryId = (int) $this->input('category_id');
        $summary = $this->input('summary');
        $content = $this->input('content');
        $status = $this->input('status', 'published');

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

        $articleModel = new HealthArticle();
        $data = [
            'title'       => $title,
            'slug'        => $slug,
            'category_id' => $categoryId,
            'summary'     => $summary,
            'content'     => $content,
            'status'      => $status,
        ];

        if ($id) {
            $articleModel->update((int) $id, $data);
            $this->flashAndRedirect('success', 'Artikel berhasil diperbarui.', '/admin/artikel');
        } else {
            $newArticleId = $articleModel->create($data);
            if ($newArticleId && $status === 'published') {
                $category = $categoryModel->find($categoryId);
                \App\Services\NotificationService::notifyNewArticle([
                    'id'            => $newArticleId,
                    'title'         => $title,
                    'slug'          => $slug,
                    'category_name' => $category['name'] ?? 'Edukasi Kesehatan',
                ]);
            }
            $this->flashAndRedirect('success', 'Artikel baru berhasil dipublikasikan.', '/admin/artikel');
        }
    }

    public function questions(): void
    {
        $questionModel = new Question();
        $categoryModel = new HealthCategory();
        $optionModel = new QuestionOption();

        $questions = $questionModel->getActiveQuestionsWithCategory();
        foreach ($questions as &$q) {
            $q['options'] = $optionModel->getByQuestion((int) $q['id']);
        }

        $categories = $categoryModel->allOrdered('name ASC');

        $this->render('admin/questions', [
            'pageTitle'  => 'Bank Soal Kuis Kesehatan - Admin',
            'questions'  => $questions,
            'categories' => $categories,
        ], 'layouts/admin');
    }

    public function saveQuestion(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $categoryId = (int) $this->input('category_id');
        $questionText = $this->input('question_text');
        $explanation = $this->input('explanation');
        $options = $_POST['options'] ?? [];
        $correctOptionIndex = (int) $this->input('correct_option');

        if (empty($questionText) || count($options) !== 4) {
            $this->flashAndRedirect('danger', 'Data soal tidak lengkap. Pastikan mengisi 4 opsi jawaban.', '/admin/kuis');
            return;
        }

        $questionModel = new Question();
        $optionModel = new QuestionOption();

        // Transaction handling could be done inside model, but let's do simple consecutive queries
        $qData = [
            'category_id'   => $categoryId,
            'question_text' => $questionText,
            'explanation'   => $explanation,
            'points'        => 10,
            'status'        => 'active'
        ];

        $qId = $questionModel->create($qData);

        if ($qId) {
            foreach ($options as $index => $optText) {
                $isCorrect = ($index === $correctOptionIndex) ? 1 : 0;
                $optionModel->create([
                    'question_id' => $qId,
                    'option_text' => $optText,
                    'is_correct'  => $isCorrect
                ]);
            }

            \App\Services\NotificationService::notifyNewQuiz('Edukasi PHBS & Promkes');
            $this->flashAndRedirect('success', 'Soal kuis baru berhasil ditambahkan.', '/admin/kuis');
        } else {
            $this->flashAndRedirect('danger', 'Gagal menyimpan soal kuis.', '/admin/kuis');
        }
    }

    public function deleteQuestion(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $questionModel = new Question();
        $questionModel->delete($id);
        $this->flashAndRedirect('success', 'Soal kuis berhasil dihapus.', '/admin/kuis');
    }

    public function aiGenerator(): void
    {
        $categoryModel = new HealthCategory();
        $categories = $categoryModel->allOrdered('name ASC');

        $this->render('admin/ai_generator', [
            'pageTitle'  => 'AI Pembuat Soal Otomatis - Admin',
            'categories' => $categories,
            'generated'  => Session::getFlash('ai_generated_questions'),
            'topic'      => Session::getFlash('ai_topic'),
            'category'   => Session::getFlash('ai_category_id'),
            'error'      => Session::getFlash('ai_error')
        ], 'layouts/admin');
    }

    public function generateAiQuestions(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $topic = $this->input('topic');
        $categoryId = (int) $this->input('category_id');
        $count = (int) $this->input('count', 3);
        $difficulty = $this->input('difficulty', 'sedang');

        if (empty($topic) || $categoryId <= 0) {
            Session::setFlash('ai_error', 'Topik dan Kategori wajib diisi.');
            $this->redirect('/admin/ai-pembuat-soal');
            return;
        }

        if ($count > 10) $count = 10; // Max 10 per request

        $categoryModel = new HealthCategory();
        $cat = $categoryModel->find($categoryId);
        $catName = $cat ? $cat['name'] : 'Umum';

        try {
            $aiService = new \App\Services\AiService();
            $questions = $aiService->generateQuestions($topic, $catName, $count, $difficulty);
            
            Session::setFlash('ai_generated_questions', json_encode($questions));
            Session::setFlash('ai_topic', $topic);
            Session::setFlash('ai_category_id', $categoryId);
            
            $this->redirect('/admin/ai-pembuat-soal');
        } catch (\Exception $e) {
            Session::setFlash('ai_error', $e->getMessage());
            $this->redirect('/admin/ai-pembuat-soal');
        }
    }

    public function saveAiQuestions(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $categoryId = (int) $this->input('category_id');
        $questionsJson = $this->input('questions_json');
        
        if (empty($questionsJson)) {
            $this->flashAndRedirect('danger', 'Data soal AI tidak ditemukan.', '/admin/ai-pembuat-soal');
            return;
        }

        $questions = json_decode(html_entity_decode($questionsJson), true);
        if (!is_array($questions)) {
            $this->flashAndRedirect('danger', 'Data JSON tidak valid.', '/admin/ai-pembuat-soal');
            return;
        }

        $questionModel = new Question();
        $optionModel = new QuestionOption();

        $savedCount = 0;
        foreach ($questions as $q) {
            if (!isset($q['question_text']) || empty($q['options'])) continue;

            $qId = $questionModel->create([
                'category_id'   => $categoryId,
                'question_text' => $q['question_text'],
                'explanation'   => $q['explanation'] ?? '',
                'points'        => 10,
                'status'        => 'active'
            ]);

            if ($qId) {
                foreach ($q['options'] as $opt) {
                    $optionModel->create([
                        'question_id' => $qId,
                        'option_text' => $opt['text'],
                        'is_correct'  => !empty($opt['is_correct']) ? 1 : 0
                    ]);
                }
                $savedCount++;
            }
        }

        $this->flashAndRedirect('success', "$savedCount soal dari AI berhasil disimpan ke Bank Soal.", '/admin/kuis');
    }

    public function aiLogs(): void
    {
        $aiLogModel = new AiLog();
        $logs = $aiLogModel->getRecentLogs(50);

        $this->render('admin/ai_logs', [
            'pageTitle' => 'Log Aktivitas AI & Integrasi - Admin',
            'logs'      => $logs,
        ], 'layouts/admin');
    }
}
