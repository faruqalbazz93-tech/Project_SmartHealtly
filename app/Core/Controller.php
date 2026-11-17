<?php

namespace App\Core;

use App\Config\App;

abstract class Controller
{
    /**
     * Render a view within a layout
     */
    protected function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        // Extract data to make variables available in views
        extract($data);

        // Capture view content
        $viewPath = dirname(__DIR__) . "/Views/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View file not found: {$viewPath}");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Render layout
        $layoutPath = dirname(__DIR__) . "/Views/{$layout}.php";
        if (!file_exists($layoutPath)) {
            // Fallback to outputting content directly if layout not found
            echo $content;
            return;
        }

        require $layoutPath;
    }

    /**
     * Redirect to another URL
     */
    protected function redirect(string $path): void
    {
        $url = str_starts_with($path, 'http') ? $path : App::baseUrl($path);
        header("Location: {$url}");
        exit;
    }

    /**
     * Return JSON response
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Set flash message and redirect
     */
    protected function flashAndRedirect(string $type, string $message, string $redirectPath): void
    {
        Session::setFlash($type, $message);
        $this->redirect($redirectPath);
    }

    /**
     * Sanitize request input
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        if (is_string($value)) {
            return trim($value);
        }
        return $value;
    }

    /**
     * Get all POST/GET data
     */
    protected function allInputs(): array
    {
        return array_merge($_GET, $_POST);
    }

    /**
     * Verify CSRF token
     */
    protected function validateCsrf(): bool
    {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Session::validateCsrfToken($token)) {
            Session::setFlash('danger', 'Sesi tidak valid atau telah kedaluwarsa. Silakan coba lagi.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
            return false;
        }
        return true;
    }
}
