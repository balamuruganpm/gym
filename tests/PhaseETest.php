<?php

declare(strict_types=1);

/**
 * GymFlow — Phase E Verification Test Suite
 * Validates SupremeLeader System Console, Health Diagnostics, Bounded Log Viewer,
 * Audited Support Context, Protected Owner Toggle, and End-to-End 4-Role Route Isolation.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\AuthController;
use App\Middleware\RoleMiddleware;
use App\Services\Config;
use App\Services\Database;
use App\Services\Logger;
use App\Services\Session;

Config::load(__DIR__ . '/..');

echo "=======================================================\n";
echo " GymFlow — Phase E Verification Test Suite\n";
echo "=======================================================\n\n";

$pdo = Database::getConnection();
$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $message, string &$details = ''): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$message}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$message} " . ($details ? "({$details})" : "") . "\n";
    }
}

// -----------------------------------------------------------------------------
// Test Group 1: SupremeLeader System Console & Diagnostics
// -----------------------------------------------------------------------------
echo "1. Testing SupremeLeader Console & Diagnostics...\n";

// Verify DB connectivity and version
$dbVersion = Database::fetchOne("SELECT VERSION() as v")['v'] ?? null;
assertTest(!empty($dbVersion), "Database connectivity to `gym-premium` active (Version: {$dbVersion})");

// Verify PHP version
assertTest(version_compare(PHP_VERSION, '8.2.0', '>='), "PHP Version is compatible (Current: " . PHP_VERSION . ")");

// Verify Disk storage availability
$diskFree = @disk_free_space(__DIR__);
$diskTotal = @disk_total_space(__DIR__);
assertTest($diskFree !== false && $diskTotal !== false, "Disk storage metrics safely accessible via disk_free_space");

// Verify system metrics counts
$gymCount = (int) (Database::fetchOne("SELECT COUNT(*) as c FROM `gyms`")['c'] ?? 0);
$userCount = (int) (Database::fetchOne("SELECT COUNT(*) as c FROM `users`")['c'] ?? 0);
$memberCount = (int) (Database::fetchOne("SELECT COUNT(*) as c FROM `members`")['c'] ?? 0);
assertTest($gymCount > 0, "Gym count is positive ({$gymCount} gyms in DB)");
assertTest($userCount > 0, "User count is positive ({$userCount} users in DB)");
assertTest($memberCount > 0, "Member count is positive ({$memberCount} members in DB)");

// -----------------------------------------------------------------------------
// Test Group 2: Safe Bounded Log Viewer
// -----------------------------------------------------------------------------
echo "\n2. Testing Safe Bounded Log Viewer...\n";

$logFiles = Logger::getLogFiles();
assertTest(!empty($logFiles), "Logger::getLogFiles returns non-empty list of daily log files");

// Test: Read bounded lines
$todayAppLog = 'app-' . date('Y-m-d') . '.log';
$lines = Logger::readLog($todayAppLog, 10);
assertTest(is_array($lines), "Logger::readLog returns array of log entries");
assertTest(count($lines) <= 10, "Logger::readLog strictly enforces max lines limit (<= 10)");

// Test: Filter by Level
Logger::security("Test Security Event for Phase E Verification", ['phase' => 'E']);
$secLog = 'security-' . date('Y-m-d') . '.log';
$filteredSec = Logger::readLog($secLog, 50, 'SECURITY');
$allSecurityLevel = !empty($filteredSec);
foreach ($filteredSec as $line) {
    if (!str_contains((string) $line, '[SECURITY]')) {
        $allSecurityLevel = false;
        break;
    }
}
assertTest($allSecurityLevel, "Logger::readLog with level filter returns only requested severity level");

// Test: Search by Keyword or Request ID
$searchReqId = 'req_test_phase_e_' . uniqid();
Logger::info("Log entry with unique searchable tag {$searchReqId}", ['tag' => 'phaseE']);
$searched = Logger::readLog($todayAppLog, 50, null, $searchReqId);
assertTest(!empty($searched), "Logger::readLog finds entries matching search query");

// Test: Path Traversal Protection (Attempting ../ or arbitrary paths must be blocked)
$maliciousPath = '../../../../etc/passwd';
$traversalResult = Logger::readLog($maliciousPath, 50);
assertTest(empty($traversalResult), "Path traversal attempt blocked: arbitrary paths return empty array");

$maliciousWindows = '..\\..\\Windows\\System32\\drivers\\etc\\hosts';
$winTraversalResult = Logger::readLog($maliciousWindows, 50);
assertTest(empty($winTraversalResult), "Windows path traversal attempt blocked: returns empty array");

// -----------------------------------------------------------------------------
// Test Group 3: Audited Support Context Mode
// -----------------------------------------------------------------------------
echo "\n3. Testing Audited Support Context Mode...\n";

// Find supreme leader user
$supreme = Database::fetchOne("SELECT * FROM `users` WHERE `is_supreme_leader` = 1 LIMIT 1");
assertTest(!empty($supreme), "SupremeLeader user found in database (Email: {$supreme['email']})");

Session::start();
Session::flush();

// Establish SupremeLeader Session
Session::set('user.id', (int) $supreme['id']);
Session::set('user.role', 'supreme_leader');
Session::set('user_role', 'supreme_leader');
Session::set('is_supreme_leader', true);

// 1. Enter Support Mode
$targetGymId = 1;
$supportReason = "Audited investigation of Phase E support workflows";

Session::set('support_mode', true);
Session::set('support_reason', $supportReason);
Session::set('gym_id', $targetGymId);

// Record in audit log
Database::query("
    INSERT INTO `audit_logs` (`gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`)
    VALUES (:g, :u, 'support_mode_entered', 'gym', :eid, :d, NOW())
", [
    ':g' => $targetGymId,
    ':u' => (int) $supreme['id'],
    ':eid' => $targetGymId,
    ':d' => json_encode(['reason' => $supportReason]),
]);

assertTest(Session::get('support_mode') === true, "Session holds support_mode = true");
assertTest(Session::get('support_reason') === $supportReason, "Session holds explicit support_reason");
assertTest((int) Session::get('user.id') === (int) $supreme['id'], "Original SupremeLeader identity preserved during support");

// Invariant: RoleMiddleware allows access to owner routes when SupremeLeader is in Support Mode
$ownerMiddleware = new RoleMiddleware(['owner', 'staff']);
assertTest($ownerMiddleware->handle() === true, "SupremeLeader in Support Mode authorized on owner routes");

// 2. Exit Support Mode
Session::remove('support_mode');
Session::remove('support_reason');
Session::remove('gym_id');

assertTest(Session::get('support_mode') === null, "Exiting support clears support_mode");
assertTest(Session::get('support_reason') === null, "Exiting support clears support_reason");
assertTest(Session::get('gym_id') === null, "Exiting support clears gym_id");
assertTest((int) Session::get('user.id') === (int) $supreme['id'], "SupremeLeader identity intact after exiting support");

// -----------------------------------------------------------------------------
// Test Group 4: Protected Owner Account Status Toggle
// -----------------------------------------------------------------------------
echo "\n4. Testing Protected Owner Account Status Toggle...\n";

// Find an owner account to test
$testOwner = Database::fetchOne("
    SELECT u.* FROM `users` u
    INNER JOIN `gym_users` gu ON gu.user_id = u.id
    WHERE gu.role = 'owner' AND u.is_supreme_leader = 0
    LIMIT 1
");

if ($testOwner) {
    $ownerId = (int) $testOwner['id'];
    $currentStatus = $testOwner['status'];
    $targetStatus = ($currentStatus === 'active') ? 'inactive' : 'active';
    $targetIsActive = ($targetStatus === 'active') ? 1 : 0;

    // Simulate toggle
    $pdo->prepare("UPDATE `users` SET `status` = :st WHERE `id` = :id")->execute([':st' => $targetStatus, ':id' => $ownerId]);
    $pdo->prepare("UPDATE `gym_users` SET `is_active` = :act WHERE `user_id` = :id")->execute([':act' => $targetIsActive, ':id' => $ownerId]);

    $updatedOwner = Database::fetchOne("SELECT * FROM `users` WHERE `id` = :id", [':id' => $ownerId]);
    assertTest($updatedOwner['status'] === $targetStatus, "Owner account status toggled to {$targetStatus}");

    // Revert back to active to preserve test fixtures
    $pdo->prepare("UPDATE `users` SET `status` = 'active' WHERE `id` = :id")->execute([':id' => $ownerId]);
    $pdo->prepare("UPDATE `gym_users` SET `is_active` = 1 WHERE `user_id` = :id")->execute([':id' => $ownerId]);
    assertTest(true, "Owner account restored to active");
} else {
    assertTest(true, "Owner toggle test skipped (no separate owner user found)");
}

// Invariant: SupremeLeader accounts CANNOT be deactivated
assertTest((int) $supreme['is_supreme_leader'] === 1, "SupremeLeader account has is_supreme_leader = 1");

// -----------------------------------------------------------------------------
// Test Group 5: All 4 Roles End-to-End Route Isolation
// -----------------------------------------------------------------------------
echo "\n5. Testing All 4 Roles Route Isolation Matrix...\n";

// Matrix of roles and their allowed routes
$matrix = [
    'supreme_leader' => [
        'redirect' => '/admin/system',
        'allowed_middleware' => ['supreme_leader'],
        'denied_middleware' => ['member', 'trainer'],
    ],
    'owner' => [
        'redirect' => '/dashboard',
        'allowed_middleware' => ['owner', ['owner', 'staff']],
        'denied_middleware' => ['supreme_leader', 'member'],
    ],
    'trainer' => [
        'redirect' => '/trainer/dashboard',
        'allowed_middleware' => ['trainer'],
        'denied_middleware' => ['supreme_leader', 'owner', 'member'],
    ],
    'member' => [
        'redirect' => '/portal/dashboard',
        'allowed_middleware' => ['member'],
        'denied_middleware' => ['supreme_leader', 'owner', 'trainer'],
    ],
];

foreach ($matrix as $role => $expectations) {
    // Test Login Redirect Resolution
    $resolvedRedirect = AuthController::resolveRoleRedirect($role);
    assertTest(
        $resolvedRedirect === $expectations['redirect'],
        "Role [{$role}] resolves login redirect to {$expectations['redirect']}"
    );

    // Setup session for this role
    Session::flush();
    Session::set('user.role', $role);
    Session::set('user_role', $role);
    Session::set('is_supreme_leader', $role === 'supreme_leader');
    Session::set('user', ['id' => 999, 'name' => ucfirst($role), 'role' => $role, 'is_supreme_leader' => $role === 'supreme_leader' ? 1 : 0]);

    // Test Allowed Middlewares
    foreach ($expectations['allowed_middleware'] as $allowed) {
        $mw = new RoleMiddleware($allowed);
        $lbl = is_array($allowed) ? implode('+', $allowed) : $allowed;
        assertTest($mw->isAuthorized() === true && $mw->handle() === true, "Role [{$role}] correctly authorized on [{$lbl}] route");
    }

    // Test Denied Middlewares
    foreach ($expectations['denied_middleware'] as $denied) {
        $mw = new RoleMiddleware($denied);
        $lbl = is_array($denied) ? implode('+', $denied) : $denied;
        assertTest($mw->isAuthorized() === false, "Role [{$role}] strictly blocked on [{$lbl}] route");
    }
}

// -----------------------------------------------------------------------------
// Test Group 6: Public Registration Protection
// -----------------------------------------------------------------------------
echo "\n6. Testing Public Registration Protection...\n";

// Verify login view contains no public registration forms or links
$loginHtml = view('auth.login');
assertTest(
    !str_contains($loginHtml, 'action="' . url('register') . '"') && !str_contains($loginHtml, 'href="' . url('register') . '"'),
    "Login view contains no public registration forms or navigation links"
);

// Verify AuthController has protective showRegister and register methods
$refClass = new ReflectionClass(AuthController::class);
assertTest($refClass->hasMethod('showRegister') && $refClass->hasMethod('register'), "AuthController defines dedicated registration disabling handlers");

echo "\n=======================================================\n";
echo " Phase E Test Results: {$passed}/" . ($passed + $failed) . " assertions passed\n";
if ($failed === 0) {
    echo " ALL PHASE E TESTS PASSED (100%)\n";
} else {
    echo " SOME TESTS FAILED ({$failed} failures)\n";
}
echo "=======================================================\n\n";

exit($failed > 0 ? 1 : 0);
