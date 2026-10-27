<?php

declare(strict_types=1);

use App\Services\Session;
use App\Services\Csrf;

if (!function_exists('view')) {
    /**
     * Render a view file with optional data.
     */
    function view(string $path, array $data = []): string
    {
        extract($data);
        $file = __DIR__ . '/Views/' . str_replace('.', '/', $path) . '.php';
        if (!file_exists($file)) {
            throw new RuntimeException("View file not found: {$file}");
        }
        ob_start();
        include $file;
        return (string) ob_get_clean();
    }
}

if (!function_exists('render_component')) {
    /**
     * Render a component partial from app/Views/components/
     */
    function render_component(string $name, array $props = []): string
    {
        return view('components.' . $name, $props);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return Session::getOldInput($key, $default);
    }
}

if (!function_exists('flash')) {
    function flash(string $key): mixed
    {
        return Session::getFlash($key);
    }
}

if (!function_exists('has_flash')) {
    function has_flash(string $key): bool
    {
        return Session::hasFlash($key);
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        return Session::get('user');
    }
}

if (!function_exists('current_gym')) {
    function current_gym(): ?array
    {
        return Session::get('gym');
    }
}

if (!function_exists('user_role')) {
    function user_role(): ?string
    {
        return Session::get('user_role');
    }
}

if (!function_exists('is_owner')) {
    function is_owner(): bool
    {
        return Session::get('user_role') === 'owner';
    }
}

if (!function_exists('e')) {
    function e(mixed $val): string
    {
        return htmlspecialchars((string) ($val ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float|int|string $amount, string $symbol = '₹'): string
    {
        $val = (float) $amount;
        return $symbol . ' ' . number_format($val, 2);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Compute base directory from current script
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = ($scriptDir === '/' || $scriptDir === '\\') ? '' : str_replace('\\', '/', $scriptDir);
        
        $base = rtrim($scheme . '://' . $host . $scriptDir, '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}
