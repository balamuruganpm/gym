<?php
/**
 * CSRF Protection Core Engine
 * Generates and validates cryptographic CSRF tokens for all state-changing requests.
 */

class CSRF {
    private static $tokenKey = '_csrf_token';

    public static function generate() {
        Session::start();
        $token = Session::get(self::$tokenKey);
        if (!$token) {
            if (function_exists('random_bytes')) {
                $token = bin2hex(random_bytes(32));
            } elseif (function_exists('openssl_random_pseudo_bytes')) {
                $token = bin2hex(openssl_random_pseudo_bytes(32));
            } else {
                $token = md5(uniqid(mt_rand(), true));
            }
            Session::set(self::$tokenKey, $token);
        }
        return $token;
    }

    public static function getToken() {
        return self::generate();
    }

    public static function getField() {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function getInput() {
        return self::getField();
    }

    public static function field() {
        return self::getField();
    }

    public static function validateToken() {
        return self::validateOrAbort();
    }

    public static function getMeta() {
        $token = self::generate();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verify token from POST or headers
     */
    public static function verify($token = null) {
        Session::start();
        $sessionToken = Session::get(self::$tokenKey);

        if (!$sessionToken) {
            return false;
        }

        if ($token === null) {
            if (isset($_POST['csrf_token'])) {
                $token = $_POST['csrf_token'];
            } elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
            }
        }

        if (empty($token)) {
            return false;
        }

        if (function_exists('hash_equals')) {
            return hash_equals($sessionToken, $token);
        }

        return $sessionToken === $token;
    }

    /**
     * Require valid CSRF token or abort with 403 Forbidden
     */
    public static function validateOrAbort() {
        if (!self::verify()) {
            http_response_code(403);
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token. Please refresh the page.']);
            } else {
                die("<div style='font-family:sans-serif;padding:30px;text-align:center;'>
                    <h2>403 Forbidden</h2>
                    <p>Security validation failed (Invalid or Expired CSRF Token).</p>
                    <p><a href='javascript:history.back()'>Go back and try again</a></p>
                </div>");
            }
            exit;
        }
    }
}
