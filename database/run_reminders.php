<?php

declare(strict_types=1);

/**
 * SmartHealthy - Appointment Visit Reminder Scheduler CLI Runner
 * Can be executed via Cron Job / Windows Task Scheduler:
 * C:\xampp\php\php.exe database/run_reminders.php
 */

require_once __DIR__ . '/../app/Core/Env.php';
\App\Core\Env::load(__DIR__ . '/../.env');

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require_once $file;
});

use App\Services\NotificationService;
use App\Models\WhatsAppSetting;

$settingModel = new WhatsAppSetting();
$tz = $settingModel->get('timezone', 'Asia/Jakarta');
date_default_timezone_set($tz);

echo "=========================================================\n";
echo " SmartHealthy - Scheduler Pengingat Kunjungan Pasien\n";
echo " Waktu Server: " . date('d F Y, H:i:s') . " ({$tz})\n";
echo "=========================================================\n\n";

$result = NotificationService::runScheduledReminders();

echo "Hasil Eksekusi Scheduler:\n";
echo "- Total Antrean Jatuh Tempo Diperiksa: " . ($result['total_due'] ?? 0) . "\n";
echo "- Pengingat Berhasil Terkirim        : " . ($result['sent'] ?? 0) . "\n";
echo "- Diabaikan (Idempotency Anti-Spam)  : " . ($result['skipped'] ?? 0) . "\n";
echo "- Gagal Dikirim                      : " . ($result['failed'] ?? 0) . "\n\n";

if (!empty($result['details'])) {
    echo "Rincian Pengiriman:\n";
    foreach ($result['details'] as $item) {
        $st = $item['status'] === 'success' ? '[OK]' : '[GAGAL]';
        echo "{$st} ID #{$item['id']} ({$item['type']}) -> " . ($item['phone'] ?? '-') . " " . ($item['reason'] ?? '') . "\n";
    }
} else {
    echo "Tidak ada antrean pengingat yang jatuh tempo saat ini.\n";
}

echo "\nScheduler selesai dijalankan.\n";

