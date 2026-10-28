<?php

declare(strict_types=1);

namespace App\Services;

class Logger
{
    private static ?string $requestId = null;
    private static bool $handlersRegistered = false;
    private static bool $retentionChecked = false;

    /**
     * Get or generate the unique Request ID for this lifecycle.
     */
    public static function getRequestId(): string
    {
        if (self::$requestId === null) {
            self::$requestId = 'req_' . bin2hex(random_bytes(6));
        }
        return self::$requestId;
    }

    /**
     * Set a custom request ID (useful for testing or sub-contexts).
     */
    public static function setRequestId(string $id): void
    {
        self::$requestId = $id;
    }

    /**
     * Get the absolute path to the log storage directory.
     */
    public static function getLogDirectory(): string
    {
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0750, true);
            // Write protection files
            @file_put_contents($logDir . '/.htaccess', "Require all denied\nDeny from all\n");
            @file_put_contents($logDir . '/index.html', "<!-- Access Denied -->");
        }
        return $logDir;
    }

    /**
     * Log an informational message to the app channel.
     */
    public static function info(string $message, array $context = []): void
    {
        self::write('app', 'INFO', $message, $context);
    }

    /**
     * Log a warning to both app and error channels.
     */
    public static function warning(string $message, array $context = []): void
    {
        self::write('app', 'WARNING', $message, $context);
        self::write('error', 'WARNING', $message, $context);
    }

    /**
     * Log an error to the error channel.
     */
    public static function error(string $message, array $context = [], ?\Throwable $exception = null): void
    {
        if ($exception !== null) {
            $context['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
            if (Config::get('app.debug', false)) {
                $context['exception']['trace'] = $exception->getTraceAsString();
            }
        }
        self::write('error', 'ERROR', $message, $context);
    }

    /**
     * Log a security or audit event to the security channel.
     */
    public static function security(string $message, array $context = []): void
    {
        self::write('security', 'SECURITY', $message, $context);
    }

    /**
     * Log a debug message (only when debug is enabled).
     */
    public static function debug(string $message, array $context = []): void
    {
        if (Config::get('app.debug', false)) {
            self::write('app', 'DEBUG', $message, $context);
        }
    }

    /**
     * Explicit log to any channel with custom level.
     */
    public static function log(string $channel, string $level, string $message, array $context = []): void
    {
        self::write($channel, strtoupper($level), $message, $context);
    }

    /**
     * Core writing engine.
     */
    private static function write(string $channel, string $level, string $message, array $context = []): void
    {
        try {
            // Run automatic retention cleanup once per request lifecycle
            if (!self::$retentionChecked) {
                self::$retentionChecked = true;
                // Opportunistic cleanup check: 1 in 10 requests or first run
                if (random_int(1, 10) === 1) {
                    self::cleanOldLogs();
                }
            }

            $logDir = self::getLogDirectory();
            $date = date('Y-m-d');
            $filePath = $logDir . '/' . preg_replace('/[^a-z0-9_-]/i', '', strtolower($channel)) . '-' . $date . '.log';

            $timestamp = date('Y-m-d H:i:s T');
            $reqId = self::getRequestId();

            // Actor tag: [gym:X user:Y]
            $actorTag = self::resolveActorTag();

            // Sanitize message to prevent CRLF log injection
            $sanitizedMessage = str_replace(["\r\n", "\r", "\n"], ' ', $message);

            // Redact sensitive context data
            $sanitizedContext = self::redactSensitiveData($context);
            $contextJson = !empty($sanitizedContext) ? ' | Context: ' . json_encode($sanitizedContext, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';

            $entry = sprintf("[%s] [%s] [%s] %s %s%s\n", $timestamp, $reqId, $level, $actorTag, $sanitizedMessage, $contextJson);

            @file_put_contents($filePath, $entry, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // Silently absorb failure to ensure visitor requests are never broken
            @error_log("GymFlow Logger failed: " . $e->getMessage());
        }
    }

    /**
     * Resolve actor context tag [gym:X user:Y].
     */
    private static function resolveActorTag(): string
    {
        if (PHP_SAPI === 'cli') {
            return '[cli]';
        }

        $userId = 'guest';
        $gymId = 'guest';

        try {
            if (class_exists(Session::class)) {
                $user = Session::get('user');
                if (is_array($user) && !empty($user['id'])) {
                    $userId = (string) $user['id'];
                } elseif (Session::has('user.id')) {
                    $userId = (string) Session::get('user.id');
                }

                $gym = Session::get('gym');
                if (is_array($gym) && !empty($gym['id'])) {
                    $gymId = (string) $gym['id'];
                } elseif (Session::has('gym_id')) {
                    $gymId = (string) Session::get('gym_id');
                }
            }
        } catch (\Throwable) {
            // Session not initialized or inaccessible
        }

        return sprintf('[gym:%s user:%s]', $gymId, $userId);
    }

    /**
     * Recursively redact sensitive keys from context array.
     */
    public static function redactSensitiveData(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        $redacted = [];
        $sensitivePatterns = [
            'password', 'passwd', 'pwd', 'secret', 'token', 'csrf', 'api_key', 'apikey',
            'auth', 'authorization', 'bearer', 'cookie', 'session', 'card', 'cvv', 'cvc',
            'pan', 'ssn', 'hash', 'private_key'
        ];

        foreach ($data as $key => $val) {
            $keyStr = strtolower((string) $key);
            $isSensitive = false;

            foreach ($sensitivePatterns as $pattern) {
                if (str_contains($keyStr, $pattern)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $redacted[$key] = '[REDACTED]';
            } elseif (is_array($val)) {
                $redacted[$key] = self::redactSensitiveData($val);
            } elseif (is_string($val)) {
                // Prevent multi-line injection within context string values
                $redacted[$key] = str_replace(["\r\n", "\r", "\n"], ' ', $val);
            } else {
                $redacted[$key] = $val;
            }
        }

        return $redacted;
    }

    /**
     * Clean up log files older than the configured retention days.
     * Returns count of deleted files.
     */
    public static function cleanOldLogs(?int $retentionDays = null): int
    {
        $days = $retentionDays ?? (int) Config::get('app.log_retention_days', 30);
        if ($days <= 0) {
            return 0;
        }

        $logDir = self::getLogDirectory();
        $files = glob($logDir . '/*.log');
        if (!$files) {
            return 0;
        }

        $deletedCount = 0;
        $cutoffTime = time() - ($days * 86400);

        foreach ($files as $file) {
            // Also attempt to parse YYYY-MM-DD from filename
            if (preg_match('/-(\d{4}-\d{2}-\d{2})\.log$/', basename($file), $matches)) {
                $fileDate = strtotime($matches[1]);
                if ($fileDate !== false && $fileDate < $cutoffTime) {
                    if (@unlink($file)) {
                        $deletedCount++;
                        continue;
                    }
                }
            }

            // Fallback to filemtime check
            if (filemtime($file) < $cutoffTime) {
                if (@unlink($file)) {
                    $deletedCount++;
                }
            }
        }

        return $deletedCount;
    }

    /**
     * Get list of all log files in storage/logs/ (for SupremeLeader console).
     */
    public static function getLogFiles(): array
    {
        $logDir = self::getLogDirectory();
        $files = glob($logDir . '/*.log');
        if (!$files) {
            return [];
        }

        $result = [];
        foreach ($files as $filePath) {
            $base = basename($filePath);
            $parts = explode('-', $base);
            $channel = $parts[0] ?? 'app';

            $result[] = [
                'filename' => $base,
                'path' => $filePath,
                'channel' => $channel,
                'size' => filesize($filePath),
                'size_formatted' => round(filesize($filePath) / 1024, 2) . ' KB',
                'modified_at' => date('Y-m-d H:i:s', filemtime($filePath)),
            ];
        }

        // Sort latest first
        usort($result, fn($a, $b) => strcmp($b['filename'], $a['filename']));

        return $result;
    }

    /**
     * Read and filter log lines safely from a specific log file.
     * Prevents directory traversal attacks.
     */
    public static function readLog(string $fileName, int $maxLines = 200, ?string $level = null, ?string $search = null): array
    {
        $safeName = basename($fileName);
        if (!str_ends_with($safeName, '.log')) {
            return [];
        }

        $filePath = self::getLogDirectory() . '/' . $safeName;
        if (!file_exists($filePath)) {
            return [];
        }

        $lines = @file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }

        $filtered = [];
        $levelUpper = $level ? strtoupper($level) : null;
        $searchLower = $search ? strtolower($search) : null;

        // Iterate backwards from most recent lines
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $line = $lines[$i];

            if ($levelUpper && !str_contains($line, "[{$levelUpper}]")) {
                continue;
            }

            if ($searchLower && !str_contains(strtolower($line), $searchLower)) {
                continue;
            }

            $filtered[] = $line;

            if (count($filtered) >= $maxLines) {
                break;
            }
        }

        return $filtered;
    }

    /**
     * Register global PHP error, exception, and shutdown handlers.
     */
    public static function registerHandlers(): void
    {
        if (self::$handlersRegistered) {
            return;
        }
        self::$handlersRegistered = true;

        // 1. Error Handler
        set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
            if (!(error_reporting() & $errno)) {
                return false;
            }

            $level = match ($errno) {
                E_USER_ERROR, E_RECOVERABLE_ERROR => 'ERROR',
                E_WARNING, E_USER_WARNING => 'WARNING',
                E_NOTICE, E_USER_NOTICE => 'NOTICE',
                default => 'INFO',
            };

            self::write('error', $level, $errstr, [
                'file' => $errfile,
                'line' => $errline,
                'errno' => $errno,
            ]);

            return false;
        });

        // 2. Exception Handler
        set_exception_handler(function (\Throwable $exception) {
            self::error('Uncaught Exception: ' . $exception->getMessage(), [], $exception);

            if (!headers_sent()) {
                http_response_code(500);
            }

            $isDebug = (bool) Config::get('app.debug', false);

            if ($isDebug) {
                echo "<div style='font-family:sans-serif;padding:30px;background:#fef2f2;color:#991b1b;border:1px solid #f87171;border-radius:12px;margin:20px;'>";
                echo "<h2>Application Exception (Debug Mode)</h2>";
                echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile(), ENT_QUOTES, 'UTF-8') . ":" . $exception->getLine() . "</p>";
                echo "<pre style='background:#fff;padding:15px;border-radius:8px;overflow:auto;'>" . htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES, 'UTF-8') . "</pre>";
                echo "</div>";
            } else {
                if (function_exists('view')) {
                    try {
                        echo view('errors.404', ['message' => 'A system error occurred. Request ID: ' . self::getRequestId()]);
                        exit;
                    } catch (\Throwable) {
                        // Fallback
                    }
                }
                echo "<div style='font-family:sans-serif;text-align:center;padding:50px;'><h1>500 Internal Server Error</h1><p>An unexpected error occurred. Request ID: " . htmlspecialchars(self::getRequestId(), ENT_QUOTES, 'UTF-8') . "</p></div>";
            }
            exit;
        });

        // 3. Fatal Shutdown Handler
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
                self::write('error', 'CRITICAL', 'Fatal Error: ' . $error['message'], [
                    'file' => $error['file'],
                    'line' => $error['line'],
                    'type' => $error['type'],
                ]);
            }
        });
    }
}
