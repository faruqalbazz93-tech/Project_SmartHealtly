<?php

namespace App\Core;

use App\Config\App as AppConfig;

class App
{
    private static array $routes = [];

    /**
     * Register a GET route
     */
    public static function get(string $path, array|callable $handler): void
    {
        self::addRoute('GET', $path, $handler);
    }

    /**
     * Register a POST route
     */
    public static function post(string $path, array|callable $handler): void
    {
        self::addRoute('POST', $path, $handler);
    }

    /**
     * Register a route for both GET and POST
     */
    public static function any(string $path, array|callable $handler): void
    {
        self::addRoute('GET', $path, $handler);
        self::addRoute('POST', $path, $handler);
    }

    private static function addRoute(string $method, string $path, array|callable $handler): void
    {
        $path = '/' . trim($path, '/');
        self::$routes[] = [
            'method'  => strtoupper($method),
            'path'    => $path,
            'handler' => $handler,
        ];
    }

    /**
     * Resolve and dispatch current HTTP request
     */
    public function run(): void
    {
        Session::start();

        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = $this->parseUri();

        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod && $route['method'] !== 'ANY') {
                continue;
            }

            $pattern = $this->convertRouteToRegex($route['path']);
            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Remove full match

                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func_array($handler, $matches);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$controllerClass, $action] = $handler;
                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();
                        if (method_exists($controller, $action)) {
                            call_user_func_array([$controller, $action], $matches);
                            return;
                        }
                    }
                }
            }
        }

        // 404 Not Found
        $this->handleNotFound($requestUri);
    }

    /**
     * Parse URI from request, handling subfolders and query strings
     */
    private function parseUri(): string
    {
        // 1. If explicit query parameter 'url' is used (e.g. index.php?url=/dashboard)
        if (isset($_GET['url'])) {
            return '/' . trim($_GET['url'], '/');
        }

        // 2. Parse from REQUEST_URI
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query parameters
        if (false !== ($pos = strpos($uri, '?'))) {
            $uri = substr($uri, 0, $pos);
        }

        // Strip project subfolder if running in subdirectory (e.g., /SmartHealty/public or /SmartHealty)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = dirname($scriptName);
        $baseDir = str_replace('\\', '/', $baseDir);

        if ($baseDir !== '/' && str_starts_with($uri, $baseDir)) {
            $uri = substr($uri, strlen($baseDir));
        }

        // Also check parent folder if public is inside
        $parentDir = dirname($baseDir);
        if ($parentDir !== '/' && $parentDir !== '\\' && str_starts_with($uri, $parentDir)) {
            $uri = substr($uri, strlen($parentDir));
        }

        // Clean up
        $uri = '/' . trim($uri, '/');
        return $uri;
    }

    /**
     * Convert route template with {param} into regex
     */
    private function convertRouteToRegex(string $routePath): string
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $routePath);
        return '#^' . $pattern . '$#';
    }

    /**
     * 404 Not Found Response
     */
    private function handleNotFound(string $uri): void
    {
        http_response_code(404);
        
        // If request expects JSON
        if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'  => 'error',
                'code'    => 404,
                'message' => "Halaman atau endpoint `{$uri}` tidak ditemukan."
            ]);
            exit;
        }

        $layoutFile = dirname(__DIR__) . '/Views/layouts/main.php';
        $content = '
        <div class="container py-5 text-center my-5">
            <div class="card border-0 shadow-sm p-5 mx-auto" style="max-width: 600px; border-radius: 1.25rem;">
                <div class="display-1 text-primary fw-bold mb-3"><i class="bi bi-exclamation-triangle"></i> 404</div>
                <h2 class="fw-bold mb-3 text-dark">Halaman Tidak Ditemukan</h2>
                <p class="text-muted mb-4">Mohon maaf, halaman <code>' . htmlspecialchars($uri) . '</code> yang Anda tuju tidak tersedia atau telah dipindahkan.</p>
                <div>
                    <a href="' . AppConfig::baseUrl('/') . '" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-house-door me-1"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>';

        if (file_exists($layoutFile)) {
            $pageTitle = '404 - Halaman Tidak Ditemukan | ' . AppConfig::name();
            require $layoutFile;
        } else {
            echo $content;
        }
    }
}
