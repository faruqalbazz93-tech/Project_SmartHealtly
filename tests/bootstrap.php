<?php

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Autoloader for App and Tests namespaces
spl_autoload_register(function (string $class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    if (strncmp('Tests\\', $class, 6) === 0) {
        $file = ROOT_PATH . '/tests/' . str_replace('\\', '/', substr($class, 6)) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load environment variables (.env if available, fallback to mock testing values)
if (file_exists(ROOT_PATH . '/.env')) {
    \App\Core\Env::load(ROOT_PATH . '/.env');
} else {
    putenv('APP_ENV=testing');
    putenv('DB_CONNECTION=mysql');
    putenv('DB_HOST=127.0.0.1');
    putenv('DB_PORT=3306');
    putenv('DB_DATABASE=smarthealthy');
    putenv('DB_USERNAME=root');
    putenv('DB_PASSWORD=');
}

// Start PHP session for testing stateful components if needed
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
