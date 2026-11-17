<?php
/**
 * SmartHealthy Database Seeder Runner (CLI)
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Core/Env.php';
\App\Core\Env::load(ROOT_PATH . '/.env');

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'smarthealthy';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

echo "=== SmartHealthy Database Seeder ===" . PHP_EOL;
echo "Connecting to MySQL database `{$database}` at {$host}:{$port}..." . PHP_EOL;

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $seedFile = ROOT_PATH . '/database/seeds/seeder.sql';
    if (!file_exists($seedFile)) {
        throw new RuntimeException("Seed file not found at: {$seedFile}");
    }

    echo "Executing seeder from seeder.sql..." . PHP_EOL;
    $sql = file_get_contents($seedFile);
    $pdo->exec($sql);

    echo "✅ Seeder executed successfully!" . PHP_EOL;
    echo "Data awal telah dimasukkan:" . PHP_EOL;
    
    $counts = [
        'Desa (Villages)' => $pdo->query("SELECT COUNT(*) FROM villages")->fetchColumn(),
        'Pengguna (Users)' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'Layanan Poli (Health Services)' => $pdo->query("SELECT COUNT(*) FROM health_services")->fetchColumn(),
        'Jadwal Poli (Schedules)' => $pdo->query("SELECT COUNT(*) FROM schedules")->fetchColumn(),
        'Kategori Artikel (Categories)' => $pdo->query("SELECT COUNT(*) FROM health_categories")->fetchColumn(),
        'Artikel Edukasi (Articles)' => $pdo->query("SELECT COUNT(*) FROM health_articles")->fetchColumn(),
        'Soal Kuis (Questions)' => $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn(),
        'Pendaftaran Demo (Registrations)' => $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn(),
        'Notifikasi Pasien (Notifications)' => $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn(),
        'Deduplikasi Notifikasi (Dedup Keys)' => $pdo->query("SELECT COUNT(*) FROM notification_dedup")->fetchColumn(),
    ];

    foreach ($counts as $name => $count) {
        echo "  - {$name}: {$count} data" . PHP_EOL;
    }

} catch (PDOException $e) {
    echo "❌ Database Seeder Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
