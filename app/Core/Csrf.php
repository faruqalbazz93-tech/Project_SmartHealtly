<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Cross-Site Request Forgery (CSRF) Protection Handler
 * SmartHealthy Puskesmas Astanajapura
 */
class Csrf
{
    public const TOKEN_KEY = 'csrf_token';
    public const FIELD_NAME = '_csrf_token';
    public const HEADER_NAME = 'X-CSRF-TOKEN';

    /**
     * Retrieve the current CSRF token, generating one if not yet present in session.
     * The token remains stable across view renders within the same session.
     */
    public static function token(): string
    {
        return Session::csrfToken();
    }

    /**
     * Generate an HTML hidden input field for form submissions.
     *
     * @param string $name Form field name (defaults to _csrf_token)
     * @return string Sanitized HTML hidden input
     */
    public static function field(string $name = self::FIELD_NAME): string
    {
        $escapedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $escapedToken = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');

        return '<input type="hidden" name="' . $escapedName . '" value="' . $escapedToken . '" autocomplete="off">';
    }

    /**
     * Generate an HTML meta tag for AJAX / fetch requests.
     *
     * @return string HTML meta tag
     */
    public static function meta(): string
    {
        $escapedToken = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<meta name="csrf-token" content="' . $escapedToken . '">';
    }

    /**
     * Validate a submitted CSRF token against the session token.
     * Uses timing-attack safe comparison (hash_equals).
     *
     * @param string|null $token Submitted token from POST body or HTTP header
     * @return bool True if valid, false otherwise
     */
    public static function validate(?string $token): bool
    {
        return Session::validateCsrfToken($token);
    }

    /**
     * Regenerate the CSRF token in session (e.g., after login/logout/privilege escalation).
     *
     * @return string Newly generated CSRF token
     */
    public static function regenerate(): string
    {
        Session::start();
        $newToken = bin2hex(random_bytes(32));
        Session::set(self::TOKEN_KEY, $newToken);
        return $newToken;
    }
}

