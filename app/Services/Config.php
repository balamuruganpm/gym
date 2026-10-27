<?php

declare(strict_types=1);

namespace App\Services;

class Config
{
    private static array $items = [];
    private static bool $loaded = false;

    public static function load(string $baseDir): void
    {
        if (self::$loaded) {
            return;
        }

        // Load .env if present
        $envFile = $baseDir . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '#')) {
                    continue;
                }
                if (str_contains($line, '=')) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    // Remove surrounding quotes if present
                    if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                        (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                        $value = substr($value, 1, -1);
                    }
                    $_ENV[$key] = $value;
                    putenv("{$key}={$value}");
                }
            }
        }

        // Load config files
        $configDir = $baseDir . '/config';
        if (is_dir($configDir)) {
            foreach (glob($configDir . '/*.php') as $file) {
                $name = basename($file, '.php');
                self::$items[$name] = require $file;
            }
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $data = self::$items;

        foreach ($parts as $part) {
            if (!is_array($data) || !array_key_exists($part, $data)) {
                // Check $_ENV fallback
                $envKey = strtoupper(str_replace('.', '_', $key));
                return $_ENV[$envKey] ?? $default;
            }
            $data = $data[$part];
        }

        return $data;
    }

    public static function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}
