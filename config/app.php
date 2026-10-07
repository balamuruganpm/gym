<?php
/**
 * Application Configuration
 */

// Environment settings
defined('APP_NAME') or define('APP_NAME', 'FitPulse Gym Management & Automation Platform');
defined('APP_ENV') or define('APP_ENV', 'development'); // 'development' or 'production'
defined('APP_DEBUG') or define('APP_DEBUG', true);

// Base URLs and Paths
defined('BASE_PATH') or define('BASE_PATH', dirname(__DIR__));
defined('UPLOADS_PATH') or define('UPLOADS_PATH', BASE_PATH . '/uploads');

// Timezone and Locale
date_default_timezone_set('Asia/Kolkata');
defined('APP_TIMEZONE') or define('APP_TIMEZONE', 'Asia/Kolkata');
defined('APP_CURRENCY_SYMBOL') or define('APP_CURRENCY_SYMBOL', '₹');
defined('APP_CURRENCY_CODE') or define('APP_CURRENCY_CODE', 'INR');

// Security Settings
defined('SESSION_LIFETIME') or define('SESSION_LIFETIME', 86400); // 24 hours
defined('PASSWORD_MIN_LENGTH') or define('PASSWORD_MIN_LENGTH', 6);
defined('ATTENDANCE_COOLDOWN_MINUTES') or define('ATTENDANCE_COOLDOWN_MINUTES', 30); // Duplicate check-in threshold

// Dynamic Base URL detection for Laragon and Apache virtual hosts
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$scriptName = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
$baseUrl = rtrim($protocol . $host . str_replace('\\', '/', $scriptName), '/');
defined('BASE_URL') or define('BASE_URL', $baseUrl);

return [
    'name' => APP_NAME,
    'env' => APP_ENV,
    'debug' => APP_DEBUG,
    'base_url' => BASE_URL,
    'timezone' => APP_TIMEZONE,
    'currency_symbol' => APP_CURRENCY_SYMBOL,
    'currency_code' => APP_CURRENCY_CODE,
];
