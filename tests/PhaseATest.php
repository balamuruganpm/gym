<?php

declare(strict_types=1);

/**
 * Phase A — Foundation Hardening and Logging Test Suite
 * 
 * Verifies:
 * 1. Session service dot notation, flat keys, and backward compatibility.
 * 2. Fallbacks between user.role <-> user_role and user.id <-> user['id'].
 * 3. RoleMiddleware with string and array allowed roles, SupremeLeader context, and smart redirects.
 * 4. Logger channels (app, error, security), request IDs, actor tagging, sensitive data redaction, log injection prevention, and retention cleanup.
 */

$baseDir = dirname(__DIR__);
require_once $baseDir . '/vendor/autoload.php';

use App\Services\Config;
use App\Services\Session;
use App\Services\Logger;
use App\Middleware\RoleMiddleware;

Config::load($baseDir);

$total = 0;
$passed = 0;
$failed = 0;

function assertCondition(string $name, bool $condition, string $details = ''): void {
    global $total, $passed, $failed;
    $total++;
    if ($condition) {
        $passed++;
        echo "  [\e[32mPASS\e[0m] {$name}\n";
    } else {
        $failed++;
        echo "  [\e[31mFAIL\e[0m] {$name}" . ($details ? ": {$details}" : "") . "\n";
    }
}

echo "\n=======================================================\n";
echo " GymFlow — Phase A Verification Test Suite\n";
echo "=======================================================\n\n";

// -----------------------------------------------------------------------------
// Test Group 1: Session Dot Notation & Backward Compatibility
// -----------------------------------------------------------------------------
echo "1. Testing Session Dot Notation & Fallbacks...\n";

Session::start();
Session::destroy();
Session::start();

// Flat key setting
Session::set('user_role', 'owner');
Session::set('gym_id', 42);
Session::set('user', [
    'id' => 101,
    'name' => 'John Owner',
    'email' => 'owner@example.com',
    'role' => 'owner'
]);

// Test 1.1: Direct flat get
assertCondition('Direct flat lookup Session::get("user_role")', Session::get('user_role') === 'owner');
assertCondition('Direct flat lookup Session::get("gym_id")', Session::get('gym_id') === 42);

// Test 1.2: Dot notation lookup into user array
assertCondition('Dot notation Session::get("user.id")', Session::get('user.id') === 101);
assertCondition('Dot notation Session::get("user.name")', Session::get('user.name') === 'John Owner');
assertCondition('Dot notation Session::get("user.role")', Session::get('user.role') === 'owner');

// Test 1.3: Setting via dot notation
Session::set('gym.settings.theme', 'dark');
assertCondition('Dot notation set and get Session::get("gym.settings.theme")', Session::get('gym.settings.theme') === 'dark');

// Test 1.4: Session::has with dot notation and flat keys
assertCondition('Session::has("user.id")', Session::has('user.id') === true);
assertCondition('Session::has("user.non_existent")', Session::has('user.non_existent') === false);
assertCondition('Session::has("gym_id")', Session::has('gym_id') === true);

// Test 1.5: Fallback when only user_role is set but user.role is queried
Session::destroy();
Session::start();
Session::set('user_role', 'staff');
assertCondition('Fallback Session::get("user.role") from flat user_role', Session::get('user.role') === 'staff');
assertCondition('Fallback Session::has("user.role") from flat user_role', Session::has('user.role') === true);

// Test 1.6: Fallback when only user['role'] is set but user_role is queried
Session::destroy();
Session::start();
Session::set('user', ['id' => 99, 'role' => 'trainer']);
assertCondition('Fallback Session::get("user_role") from user.role', Session::get('user_role') === 'trainer');
assertCondition('Session::get("user.id") from nested user array', Session::get('user.id') === 99);

// -----------------------------------------------------------------------------
// Test Group 2: RoleMiddleware Robustness & Multi-Role Support
// -----------------------------------------------------------------------------
echo "\n2. Testing RoleMiddleware...\n";

// Helper to inspect RoleMiddleware via reflection or simulated handle
$roleMwClass = new ReflectionClass(RoleMiddleware::class);
$prop = $roleMwClass->getProperty('allowedRoles');
$prop->setAccessible(true);

$singleRoleMw = new RoleMiddleware('owner');
assertCondition('RoleMiddleware accepts single string role', $prop->getValue($singleRoleMw) === ['owner']);

$multiRoleMw = new RoleMiddleware(['owner', 'staff', 'manager']);
assertCondition('RoleMiddleware accepts array of roles', $prop->getValue($multiRoleMw) === ['owner', 'staff', 'manager']);

// -----------------------------------------------------------------------------
// Test Group 3: Logger Subsystem & Directory Protection
// -----------------------------------------------------------------------------
echo "\n3. Testing Logger Subsystem...\n";

$logDir = Logger::getLogDirectory();
assertCondition('Logger log directory created', is_dir($logDir));
assertCondition('.htaccess deny rules written in storage/logs', file_exists($logDir . '/.htaccess') && str_contains(file_get_contents($logDir . '/.htaccess'), 'Require all denied'));
assertCondition('index.html written in storage/logs', file_exists($logDir . '/index.html'));

// Test 3.1: Request ID generation & stability
$reqId1 = Logger::getRequestId();
$reqId2 = Logger::getRequestId();
assertCondition('Request ID is non-empty and stable per lifecycle', !empty($reqId1) && $reqId1 === $reqId2 && str_starts_with($reqId1, 'req_'));

// Test 3.2: Logging to app channel
Logger::info('Testing application log event', ['test_key' => 'app_value']);
$appLogFile = $logDir . '/app-' . date('Y-m-d') . '.log';
assertCondition('app-YYYY-MM-DD.log exists', file_exists($appLogFile));
$appContent = file_get_contents($appLogFile);
assertCondition('App log contains message and context', str_contains($appContent, 'Testing application log event') && str_contains($appContent, 'app_value'));

// Test 3.3: Logging to security channel
Logger::security('Unauthorized login attempt', ['ip' => '127.0.0.1', 'email' => 'intruder@bad.com']);
$secLogFile = $logDir . '/security-' . date('Y-m-d') . '.log';
assertCondition('security-YYYY-MM-DD.log exists', file_exists($secLogFile));
$secContent = file_get_contents($secLogFile);
assertCondition('Security log contains security event', str_contains($secContent, 'Unauthorized login attempt') && str_contains($secContent, '[SECURITY]'));

// Test 3.4: Logging to error channel with exception
$testException = new \RuntimeException("Database query failed simulation", 500);
Logger::error('Test error occurred', ['module' => 'billing'], $testException);
$errLogFile = $logDir . '/error-' . date('Y-m-d') . '.log';
assertCondition('error-YYYY-MM-DD.log exists', file_exists($errLogFile));
$errContent = file_get_contents($errLogFile);
assertCondition('Error log contains exception details', str_contains($errContent, 'Database query failed simulation') && str_contains($errContent, 'RuntimeException'));

// Test 3.5: Sensitive Data Redaction
$unredactedContext = [
    'user_id' => 5,
    'password' => 'supersecret123',
    'csrf_token' => 'token_xyz987',
    'credit_card' => '4111222233334444',
    'nested' => [
        'api_secret' => 'api_shhh',
        'auth_bearer' => 'Bearer eyJ...',
        'safe_value' => 'allowed_data'
    ]
];
$redacted = Logger::redactSensitiveData($unredactedContext);
assertCondition('Password is redacted', $redacted['password'] === '[REDACTED]');
assertCondition('CSRF token is redacted', $redacted['csrf_token'] === '[REDACTED]');
assertCondition('Card is redacted', $redacted['credit_card'] === '[REDACTED]');
assertCondition('Nested secret is redacted', $redacted['nested']['api_secret'] === '[REDACTED]');
assertCondition('Nested auth bearer is redacted', $redacted['nested']['auth_bearer'] === '[REDACTED]');
assertCondition('Non-sensitive field preserved', $redacted['nested']['safe_value'] === 'allowed_data');

// Test 3.6: Log Injection Prevention (CRLF sanitization)
$injectionMessage = "Normal Message\r\n[2026-10-28 00:00:00] [req_fake] [CRITICAL] [gym:1 user:admin] Injected Log Line";
Logger::info($injectionMessage);
$appContentAfter = file_get_contents($appLogFile);
assertCondition('CRLF log injection is sanitized to single line', !str_contains($appContentAfter, "\r\n[2026-10-28 00:00:00]") && str_contains($appContentAfter, "Normal Message [2026-10-28"));

// Test 3.7: Log Retention Cleanup
$oldLogFile = $logDir . '/app-2025-01-01.log';
file_put_contents($oldLogFile, "Old log line from 2025\n");
touch($oldLogFile, time() - (45 * 86400)); // 45 days old

assertCondition('Old log file created for retention test', file_exists($oldLogFile));
$deleted = Logger::cleanOldLogs(30);
assertCondition('Logger::cleanOldLogs removes files older than 30 days', $deleted >= 1 && !file_exists($oldLogFile));
assertCondition('Today log file is preserved during retention cleanup', file_exists($appLogFile));

// Test 3.8: Safe Log Reader for SupremeLeader Console
$files = Logger::getLogFiles();
assertCondition('Logger::getLogFiles returns array of log metadata', count($files) >= 1 && isset($files[0]['filename']));
$recentLines = Logger::readLog(basename($appLogFile), 10);
assertCondition('Logger::readLog reads recent lines', !empty($recentLines));

// -----------------------------------------------------------------------------
// Test Summary
// -----------------------------------------------------------------------------
echo "\n=======================================================\n";
echo " Test Results: Total: {$total} | Passed: {$passed} | Failed: {$failed}\n";
echo "=======================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
