<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'GymFlow',
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => $_ENV['DEFAULT_TIMEZONE'] ?? 'Asia/Kolkata',
    'currency' => $_ENV['DEFAULT_CURRENCY'] ?? '₹',
    'session_lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 7200),
    'csrf_secret' => $_ENV['CSRF_SECRET'] ?? 'default_secret_key_change_in_prod',
];
