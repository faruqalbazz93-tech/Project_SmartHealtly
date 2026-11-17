<?php

namespace App\Config;

use App\Core\Env;

class Database
{
    public static function config(): array
    {
        return [
            'driver'   => Env::get('DB_CONNECTION', 'mysql'),
            'host'     => Env::get('DB_HOST', '127.0.0.1'),
            'port'     => Env::get('DB_PORT', '3306'),
            'database' => Env::get('DB_DATABASE', 'smarthealthy'),
            'username' => Env::get('DB_USERNAME', 'root'),
            'password' => Env::get('DB_PASSWORD', ''),
            'charset'  => 'utf8mb4',
            'options'  => [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        ];
    }
}
