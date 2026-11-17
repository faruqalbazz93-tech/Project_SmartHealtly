<?php

namespace App\Config;

use App\Core\Env;

class App
{
    public static function name(): string
    {
        return Env::get('APP_NAME', 'SmartHealthy');
    }

    public static function env(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return Env::get('APP_ENV', 'development');
        }
        return Env::get($key, $default);
    }

    public static function isDebug(): bool
    {
        return (bool) Env::get('APP_DEBUG', true);
    }

    public static function baseUrl(string $path = ''): string
    {
        $baseUrl = Env::get('APP_URL');
        if (!$baseUrl) {
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            $scriptDir = str_replace('\\', '/', $scriptDir);
            $baseUrl = rtrim("{$scheme}://{$host}{$scriptDir}", '/');
        }
        
        $baseUrl = rtrim($baseUrl, '/');
        $path = ltrim($path, '/');
        
        return $path === '' ? $baseUrl : "{$baseUrl}/{$path}";
    }

    public static function puskesmas(): array
    {
        return [
            'name' => Env::get('PUSKESMAS_NAME', 'Puskesmas Astanajapura'),
            'district' => Env::get('PUSKESMAS_DISTRICT', 'Kecamatan Astanajapura'),
            'regency' => Env::get('PUSKESMAS_REGENCY', 'Kabupaten Cirebon'),
            'address' => Env::get('PUSKESMAS_ADDRESS', 'Jl. KH. Wahid Hasyim No. 45, Astanajapura, Cirebon, Jawa Barat 45181'),
            'phone' => Env::get('PUSKESMAS_PHONE', '(0231) 635123'),
            'hotline' => Env::get('PUSKESMAS_HOTLINE', '081234567890'),
            'email' => Env::get('PUSKESMAS_EMAIL', 'puskesmas.astanajapura@cirebonkab.go.id'),
        ];
    }
}
