<?php

declare(strict_types=1);

namespace App\Services;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $lifetime = (int) (Config::get('app.session_lifetime') ?? 7200);

        if (!headers_sent()) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.gc_maxlifetime', (string) $lifetime);

            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => '/',
                'domain' => '',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            session_start();
        } else if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        self::$started = true;

        // Age old input & flash data
        self::ageFlashData();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function set(string $key, mixed $value): void
    {
        // 1. Store flat key for direct lookup
        $_SESSION[$key] = $value;

        // 2. If key contains dot notation, also set nested array structure
        if (str_contains($key, '.')) {
            $segments = explode('.', $key);
            $current = &$_SESSION;
            foreach ($segments as $i => $segment) {
                if ($i === count($segments) - 1) {
                    $current[$segment] = $value;
                } else {
                    if (!isset($current[$segment]) || !is_array($current[$segment])) {
                        $current[$segment] = [];
                    }
                    $current = &$current[$segment];
                }
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        // 1. Direct key lookup first (preserves flat keys including any key with an exact match)
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        if (is_array($_SESSION ?? null) && array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }

        // 2. Dot-notation traversal if dot is present
        if (str_contains($key, '.')) {
            $segments = explode('.', $key);
            $current = $_SESSION;
            $found = true;
            foreach ($segments as $segment) {
                if (is_array($current) && (isset($current[$segment]) || array_key_exists($segment, $current))) {
                    $current = $current[$segment];
                } else {
                    $found = false;
                    break;
                }
            }
            if ($found) {
                return $current;
            }

            // Special fallback: 'user.role' -> check 'user_role' flat key if present
            if ($key === 'user.role' && isset($_SESSION['user_role'])) {
                return $_SESSION['user_role'];
            }
            // Special fallback: 'user.id' -> check 'user'['id']
            if ($key === 'user.id' && isset($_SESSION['user']['id'])) {
                return $_SESSION['user']['id'];
            }
        }

        // Fallback for flat 'user_role' -> check 'user'['role'] if present
        if ($key === 'user_role' && isset($_SESSION['user']['role'])) {
            return $_SESSION['user']['role'];
        }

        return $default;
    }

    public static function has(string $key): bool
    {
        if (isset($_SESSION[$key])) {
            return true;
        }

        if (str_contains($key, '.')) {
            $segments = explode('.', $key);
            $current = $_SESSION;
            foreach ($segments as $segment) {
                if (is_array($current) && isset($current[$segment])) {
                    $current = $current[$segment];
                } else {
                    // Check special fallbacks
                    if ($key === 'user.role' && isset($_SESSION['user_role'])) {
                        return true;
                    }
                    if ($key === 'user.id' && isset($_SESSION['user']['id'])) {
                        return true;
                    }
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flush(): void
    {
        $_SESSION = [];
    }

    public static function clear(): void
    {
        $_SESSION = [];
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash']['new'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_flash']['old'][$key] ?? $_SESSION['_flash']['new'][$key] ?? $default;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash']['old'][$key]) || isset($_SESSION['_flash']['new'][$key]);
    }

    public static function setOldInput(array $input): void
    {
        // Don't flash password fields
        unset($input['password'], $input['password_confirmation'], $input['_csrf_token']);
        $_SESSION['_old_input'] = $input;
    }

    public static function getOldInput(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (!headers_sent() && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        self::$started = false;
    }

    private static function ageFlashData(): void
    {
        $_SESSION['_flash']['old'] = $_SESSION['_flash']['new'] ?? [];
        $_SESSION['_flash']['new'] = [];
    }
}
