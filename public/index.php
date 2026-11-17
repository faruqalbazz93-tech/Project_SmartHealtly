<?php
/**
 * SmartHealthy - Sistem Pendaftaran Puskesmas Astanajapura
 * Front Controller & Routing Dispatcher
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

// Autoloader for App namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = ROOT_PATH . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load environment variables
\App\Core\Env::load(ROOT_PATH . '/.env');

// Start router
use App\Core\App;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\RegistrationController;
use App\Controllers\ArticleController;
use App\Controllers\QuizController;
use App\Controllers\AdminController;
use App\Controllers\MigrationController;
use App\Controllers\ChatController;
use App\Controllers\ChatbotController;
use App\Controllers\NotificationController;
use App\Controllers\WhatsAppAdminController;
use App\Controllers\WebhookController;

// -------------------------------------------------------------
// Public Routes
// -------------------------------------------------------------
App::get('/', [HomeController::class, 'index']);
App::get('/layanan', [HomeController::class, 'services']);
App::get('/artikel', [ArticleController::class, 'index']);
App::get('/artikel/{slug}', [ArticleController::class, 'detail']);
App::get('/cek-antrean', [HomeController::class, 'checkQueue']);
App::get('/kontak', [HomeController::class, 'contact']);

// -------------------------------------------------------------
// Authentication & Account Verification Routes
// -------------------------------------------------------------
App::get('/login', [AuthController::class, 'showLogin']);
App::post('/login', [AuthController::class, 'login']);
App::get('/register', [AuthController::class, 'showRegister']);
App::post('/register', [AuthController::class, 'register']);
App::get('/logout', [AuthController::class, 'logout']);

// Verification (Email & WhatsApp OTP)
App::get('/verifikasi', [AuthController::class, 'showVerificationHub']);
App::get('/verifikasi/otp', [AuthController::class, 'showVerifyOtp']);
App::post('/verifikasi/otp', [AuthController::class, 'verifyOtp']);
App::post('/verifikasi/otp/resend', [AuthController::class, 'resendOtp']);
App::get('/verifikasi/email/{token}', [AuthController::class, 'verifyEmail']);
App::post('/verifikasi/email/resend', [AuthController::class, 'resendEmail']);

// Password Recovery
App::get('/lupa-password', [AuthController::class, 'showForgotPassword']);
App::post('/lupa-password', [AuthController::class, 'sendResetLink']);
App::get('/reset-password/{token}', [AuthController::class, 'showResetPassword']);
App::post('/reset-password', [AuthController::class, 'resetPassword']);

// -------------------------------------------------------------
// Patient / User Dashboard Routes
// -------------------------------------------------------------
App::get('/dashboard', [DashboardController::class, 'index']);
App::get('/profil', [DashboardController::class, 'profile']);
App::post('/profil/update', [DashboardController::class, 'updateProfile']);

// Registrations & E-Ticket (View/History)
App::get('/pendaftaran', [RegistrationController::class, 'history']);
App::get('/pendaftaran/baru', [RegistrationController::class, 'create']);
App::post('/pendaftaran/konfirmasi', [RegistrationController::class, 'confirm']);
App::post('/pendaftaran/simpan', [RegistrationController::class, 'store']);
App::get('/pendaftaran/sukses/{code}', [RegistrationController::class, 'success']);
App::get('/pendaftaran/riwayat', [RegistrationController::class, 'history']);
App::get('/pendaftaran/layanan', [RegistrationController::class, 'indexServices']);
App::get('/pendaftaran/jadwal', [RegistrationController::class, 'indexSchedules']);
App::get('/pendaftaran/tiket/{code}', [RegistrationController::class, 'ticket']);
App::post('/pendaftaran/batal/{id}', [RegistrationController::class, 'cancel']);
App::get('/api/kuota', [RegistrationController::class, 'getQuotaAjax']);

// Interactive Quiz
App::get('/kuis', [QuizController::class, 'index']);
App::get('/kuis/mulai', [QuizController::class, 'start']);
App::post('/kuis/kirim', [QuizController::class, 'submit']);
App::get('/kuis/hasil/{id}', [QuizController::class, 'result']);
App::post('/kuis/jelaskan', [QuizController::class, 'explainAjax']);

// -------------------------------------------------------------
// Chatbot AI SmartHealthy API Routes (Google Gemini)
// -------------------------------------------------------------
App::get('/api/chat/history', [ChatbotController::class, 'history']);
App::post('/api/chat/send', [ChatbotController::class, 'send']);
App::post('/api/chat/clear', [ChatbotController::class, 'clear']);
App::get('/api/chatbot/history', [ChatbotController::class, 'history']);
App::post('/api/chatbot/send', [ChatbotController::class, 'send']);
App::post('/api/chatbot/clear', [ChatbotController::class, 'clear']);

// -------------------------------------------------------------
// Notification System & WhatsApp Preferences Routes
// -------------------------------------------------------------
App::get('/notifikasi', [NotificationController::class, 'index']);
App::get('/notifications', [NotificationController::class, 'index']);
App::post('/notifikasi/baca/{id}', [NotificationController::class, 'markRead']);
App::post('/notifications/read/{id}', [NotificationController::class, 'markRead']);
App::post('/notifikasi/baca-semua', [NotificationController::class, 'markAllRead']);
App::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
App::post('/notifikasi/hapus/{id}', [NotificationController::class, 'delete']);
App::post('/notifikasi/hapus-semua-dibaca', [NotificationController::class, 'deleteAllRead']);

// User WhatsApp Notification Settings & Explicit Opt-in Consent
App::get('/pengaturan/notifikasi', [NotificationController::class, 'preferences']);
App::post('/pengaturan/notifikasi/consent', [NotificationController::class, 'toggleConsent']);
App::post('/pengaturan/notifikasi/simpan', [NotificationController::class, 'savePreferences']);

// Webhook for Meta WhatsApp Cloud API (Verification & Status Updates)
App::get('/webhook/whatsapp', [WebhookController::class, 'verify']);
App::post('/webhook/whatsapp', [WebhookController::class, 'handle']);

// AJAX Notification Summary & Dynamic Actions
App::get('/api/notifikasi/summary', [NotificationController::class, 'summaryAjax']);
App::get('/api/notifications/summary', [NotificationController::class, 'summaryAjax']);
App::post('/api/notifikasi/baca/{id}', [NotificationController::class, 'markReadAjax']);
App::post('/api/notifikasi/baca-semua', [NotificationController::class, 'markAllReadAjax']);

// Admin Announcement Routes
App::get('/admin/pengumuman', [NotificationController::class, 'announcementForm']);
App::post('/admin/pengumuman/kirim', [NotificationController::class, 'sendAnnouncement']);

// Automated Appointment Reminders Cron Runner
App::get('/cron/reminders', [NotificationController::class, 'runRemindersCron']);



// -------------------------------------------------------------
// Admin Panel Routes
// -------------------------------------------------------------
App::get('/admin', [AdminController::class, 'dashboard']);
App::get('/admin/pendaftaran', [AdminController::class, 'registrations']);
App::post('/admin/pendaftaran/status', [AdminController::class, 'updateRegistrationStatus']);
App::get('/admin/layanan', [AdminController::class, 'services']);
App::post('/admin/layanan/simpan', [AdminController::class, 'saveService']);
App::get('/admin/jadwal', [AdminController::class, 'schedules']);
App::post('/admin/jadwal/simpan', [AdminController::class, 'saveSchedule']);
App::post('/admin/jadwal/hapus/{id}', [AdminController::class, 'deleteSchedule']);
App::get('/admin/desa', [AdminController::class, 'villages']);
App::get('/admin/kategori', [AdminController::class, 'categories']);
App::post('/admin/kategori/simpan', [AdminController::class, 'saveCategory']);
App::post('/admin/kategori/hapus/{id}', [AdminController::class, 'deleteCategory']);

App::get('/admin/artikel', [AdminController::class, 'articles']);
App::post('/admin/artikel/simpan', [AdminController::class, 'saveArticle']);

App::get('/admin/kuis', [AdminController::class, 'questions']);
App::post('/admin/kuis/simpan', [AdminController::class, 'saveQuestion']);
App::post('/admin/kuis/hapus/{id}', [AdminController::class, 'deleteQuestion']);

App::get('/admin/ai-pembuat-soal', [AdminController::class, 'aiGenerator']);
App::post('/admin/ai-pembuat-soal/generate', [AdminController::class, 'generateAiQuestions']);
App::post('/admin/ai-pembuat-soal/simpan', [AdminController::class, 'saveAiQuestions']);

App::get('/admin/ai-logs', [AdminController::class, 'aiLogs']);

// Admin WhatsApp Fonnte API & Automated Notifications
App::get('/admin/whatsapp', [WhatsAppAdminController::class, 'index']);
App::get('/admin/pengaturan/whatsapp', [WhatsAppAdminController::class, 'index']);
App::post('/admin/whatsapp/settings', [WhatsAppAdminController::class, 'saveSettings']);
App::post('/admin/pengaturan/whatsapp/settings', [WhatsAppAdminController::class, 'saveSettings']);
App::post('/admin/whatsapp/test-connection', [WhatsAppAdminController::class, 'testConnection']);
App::post('/admin/pengaturan/whatsapp/test-connection', [WhatsAppAdminController::class, 'testConnection']);
App::post('/admin/whatsapp/test-send', [WhatsAppAdminController::class, 'testSend']);
App::post('/admin/pengaturan/whatsapp/test-send', [WhatsAppAdminController::class, 'testSend']);
App::get('/admin/whatsapp/reminders-run', [WhatsAppAdminController::class, 'triggerReminders']);
App::get('/admin/pengaturan/whatsapp/reminders-run', [WhatsAppAdminController::class, 'triggerReminders']);

// -------------------------------------------------------------
// Database Migration & Seeder Web Runner
// -------------------------------------------------------------
App::get('/migrate', [MigrationController::class, 'index']);
App::post('/migrate/run', [MigrationController::class, 'runMigration']);

// Run Application
$app = new App();
$app->run();
