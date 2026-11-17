<?php
/**
 * SmartHealthy Database Migration Runner (CLI)
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

echo "=== SmartHealthy Database Migration ===" . PHP_EOL;
echo "Connecting to MySQL server at {$host}:{$port}..." . PHP_EOL;

try {
    // 1. Connect to MySQL without specifying database first to create it if not exists
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Creating database `{$database}` if not exists..." . PHP_EOL;
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `{$database}`;");

    $schemaFile = ROOT_PATH . '/database/migrations/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new RuntimeException("Schema file not found at: {$schemaFile}");
    }

    echo "Executing schema migration from schema.sql..." . PHP_EOL;
    $sql = file_get_contents($schemaFile);
    
    // Execute SQL queries
    $pdo->exec($sql);

    echo "✅ Migration completed successfully!" . PHP_EOL;
    echo "Tabel yang telah dibuat:" . PHP_EOL;
    $tablesStmt = $pdo->query("SHOW TABLES;");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $index => $table) {
        echo "  " . ($index + 1) . ". {$table}" . PHP_EOL;
    }

} catch (PDOException $e) {
    echo "❌ Database Migration Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
