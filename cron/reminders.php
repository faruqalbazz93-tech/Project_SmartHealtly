<?php
/**
 * SmartHealthy Scheduled Visit Reminders (CLI Cron Runner)
 * Jalankan via Windows Task Scheduler atau Linux Crontab:
 * Contoh: C:\xampp\php\php.exe cron/reminders.php
 * Crontab Linux: 0 7 * * * php /var/www/html/SmartHealty/cron/reminders.php
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

use App\Services\NotificationService;

echo "============================================================" . PHP_EOL;
echo " SmartHealthy - Scheduler Pengingat Jadwal Kunjungan Pasien " . PHP_EOL;
echo " Waktu Eksekusi: " . date('Y-m-d H:i:s') . " WIB" . PHP_EOL;
echo "============================================================" . PHP_EOL;

try {
    $stats = NotificationService::runScheduledReminders();

    echo "Status        : Berhasil" . PHP_EOL;
    echo "Total Booking : " . $stats['total_checked'] . " antrean" . PHP_EOL;
    echo "Terkirim H-0  : " . $stats['sent_today'] . " notifikasi (kunjungan hari ini)" . PHP_EOL;
    echo "Terkirim H-1  : " . $stats['sent_tomorrow'] . " notifikasi (kunjungan besok)" . PHP_EOL;
    echo "Dilewati      : " . $stats['skipped_dedup'] . " (sudah dikirim / deduplikasi)" . PHP_EOL;
    echo "============================================================" . PHP_EOL;
    exit(0);
} catch (\Throwable $e) {
    echo "Status        : GAGAL" . PHP_EOL;
    echo "Pesan Kesalahan: " . $e->getMessage() . PHP_EOL;
    echo "============================================================" . PHP_EOL;
    exit(1);
}

