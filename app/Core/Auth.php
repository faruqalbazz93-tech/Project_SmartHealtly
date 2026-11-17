<?php

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        $userId = self::id();
        $user = (new User())->findWithVillage($userId);
        return $user ?: null;
    }

    public static function role(): ?string
    {
        return Session::get('user_role');
    }

    public static function isAdmin(): bool
    {
        return self::check() && self::role() === 'admin';
    }

    public static function isUser(): bool
    {
        return self::check() && self::role() === 'user';
    }

    public static function isEmailVerified(): bool
    {
        $user = self::user();
        return !empty($user['email_verified_at']);
    }

    public static function isPhoneVerified(): bool
    {
        $user = self::user();
        return !empty($user['phone_verified_at']);
    }

    public static function isVerified(): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        // Admins are always verified, users require at least phone or email verification
        if ($user['role'] === 'admin') {
            return true;
        }
        return !empty($user['phone_verified_at']) || !empty($user['email_verified_at']);
    }

    public static function isFullyVerified(): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        if ($user['role'] === 'admin') {
            return true;
        }
        return !empty($user['phone_verified_at']) && !empty($user['email_verified_at']);
    }

    public static function login(array $user): void
    {
        Session::start();
        if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        Session::set('user_id', (int) $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role']);
        Session::set('user_phone', $user['phone'] ?? '');
        Session::set('user_nik', $user['nik'] ?? '');
        Session::set('last_activity', time());
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            Session::setFlash('danger', 'Silakan masuk ke akun Anda terlebih dahulu.');
            header('Location: ' . \App\Config\App::baseUrl('/login'));
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (!self::isAdmin()) {
            Session::setFlash('danger', 'Akses ditolak. Halaman ini hanya untuk Administrator Petugas Puskesmas.');
            header('Location: ' . \App\Config\App::baseUrl('/dashboard'));
            exit;
        }
    }

    public static function requireGuest(): void
    {
        if (self::check()) {
            $target = self::isAdmin() ? '/admin' : '/dashboard';
            header('Location: ' . \App\Config\App::baseUrl($target));
            exit;
        }
    }

    public static function requireVerified(): void
    {
        self::requireAuth();
        if (!self::isVerified()) {
            Session::setFlash('warning', 'Akun Anda belum terverifikasi. Silakan lakukan verifikasi WhatsApp OTP atau Email terlebih dahulu.');
            header('Location: ' . \App\Config\App::baseUrl('/verifikasi'));
            exit;
        }
    }

    /**
     * Check if current user is owner of a resource, or is admin
     */
    public static function checkOwnership(int $resourceOwnerId): bool
    {
        if (self::isAdmin()) {
            return true;
        }

        return self::id() === $resourceOwnerId;
    }
}
