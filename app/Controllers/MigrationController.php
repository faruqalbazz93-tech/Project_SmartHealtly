<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;
use PDO;
use Exception;

class MigrationController extends Controller
{
    public function index(): void
    {
        $dbStatus = false;
        $tables = [];
        $error = null;
        $stats = [];

        try {
            if (Database::isConnected()) {
                $dbStatus = true;
                $pdo = Database::getConnection();
                $tablesStmt = $pdo->query("SHOW TABLES;");
                $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($tables)) {
                    $stats = [
                        'villages'        => (int) $pdo->query("SELECT COUNT(*) FROM villages")->fetchColumn(),
                        'users'           => (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                        'health_services' => (int) $pdo->query("SELECT COUNT(*) FROM health_services")->fetchColumn(),
                        'schedules'       => (int) $pdo->query("SELECT COUNT(*) FROM schedules")->fetchColumn(),
                        'registrations'   => (int) $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn(),
                        'health_articles' => (int) $pdo->query("SELECT COUNT(*) FROM health_articles")->fetchColumn(),
                        'questions'       => (int) $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn(),
                        'chat_conversations' => (int) $pdo->query("SELECT COUNT(*) FROM chat_conversations")->fetchColumn(),
                        'chat_messages'   => (int) $pdo->query("SELECT COUNT(*) FROM chat_messages")->fetchColumn(),
                    ];
                }
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->render('migration/index', [
            'pageTitle' => 'Status & Migrasi Database - SmartHealthy',
            'dbStatus'  => $dbStatus,
            'tables'    => $tables,
            'stats'     => $stats,
            'error'     => $error,
        ]);
    }

    public function runMigration(): void
    {
        $action = $this->input('action', 'migrate'); // 'migrate' or 'seed' or 'all'

        try {
            $host = \App\Core\Env::get('DB_HOST', '127.0.0.1');
            $port = \App\Core\Env::get('DB_PORT', '3306');
            $database = \App\Core\Env::get('DB_DATABASE', 'smarthealthy');
            $username = \App\Core\Env::get('DB_USERNAME', 'root');
            $password = \App\Core\Env::get('DB_PASSWORD', '');

            $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $pdo->exec("USE `{$database}`;");

            $schemaPath = dirname(__DIR__, 2) . '/database/migrations/schema.sql';
            $seedPath = dirname(__DIR__, 2) . '/database/seeds/seeder.sql';

            if ($action === 'migrate' || $action === 'all') {
                $schemaSql = file_get_contents($schemaPath);
                $pdo->exec($schemaSql);
            }

            if ($action === 'seed' || $action === 'all') {
                $seedSql = file_get_contents($seedPath);
                $pdo->exec($seedSql);
            }

            $this->flashAndRedirect('success', 'Migrasi & Seeding database berhasil dieksekusi!', '/migrate');

        } catch (Exception $e) {
            $this->flashAndRedirect('danger', 'Gagal mengeksekusi migrasi: ' . $e->getMessage(), '/migrate');
        }
    }
}
