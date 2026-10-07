<?php
/**
 * Session Core Handler & Flash Messaging Engine
 */

class Session {
    private static $started = false;

    public static function start() {
        if (!self::$started && session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                @ini_set('session.cookie_httponly', 1);
                @ini_set('session.use_only_cookies', 1);
                if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                    @ini_set('session.cookie_secure', 1);
                }
            }
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            self::$started = true;
        }
    }

    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        self::start();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove($key) {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function regenerate() {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            return @session_regenerate_id(true);
        }
        return false;
    }

    public static function destroy() {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        self::$started = false;
    }

    /**
     * Flash Message Helper (Success, Error, Warning, Info)
     */
    public static function flash($type, $message) {
        self::start();
        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
        $_SESSION['_flash'][$type][] = $message;
    }

    public static function getFlash($type = null) {
        self::start();
        if (!isset($_SESSION['_flash'])) {
            return $type ? [] : [];
        }

        if ($type) {
            $messages = isset($_SESSION['_flash'][$type]) ? $_SESSION['_flash'][$type] : [];
            unset($_SESSION['_flash'][$type]);
            return $messages;
        }

        $allFlash = $_SESSION['_flash'];
        unset($_SESSION['_flash']);
        return $allFlash;
    }

    public static function hasFlash() {
        self::start();
        return !empty($_SESSION['_flash']);
    }
}
