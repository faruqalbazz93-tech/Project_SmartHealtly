<?php

namespace App\Core;

use App\Config\Database as DbConfig;
use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = DbConfig::config();
            $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            
            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            } catch (PDOException $e) {
                // If database connection fails, catch gracefully
                throw new PDOException("Gagal terhubung ke database `{$config['database']}` di {$config['host']}:{$config['port']}. Pastikan MySQL sudah berjalan di XAMPP dan database telah dibuat via migration. Error: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    public static function isConnected(): bool
    {
        try {
            self::getConnection();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
