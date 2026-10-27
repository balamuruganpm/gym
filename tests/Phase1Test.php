<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\Config;
use App\Services\Database;
use App\Validators\Validator;
use App\Services\Csrf;
use App\Services\Session;

Config::load(__DIR__ . '/..');

echo "🧪 Starting Phase 1 Integration Tests...\n";

$pdo = Database::getConnection();

// Test 1: Database Connection
echo "  [Test 1] Testing PDO connection to `gym-premium`...";
$res = Database::query("SELECT DATABASE()")->fetchColumn();
assert($res === 'gym-premium', "Database name must be gym-premium, got: {$res}");
echo " PASSED! (DB: {$res})\n";

// Test 2: Verify All 11 Tables Exist
echo "  [Test 2] Verifying table schemas...";
$requiredTables = [
    'gyms', 'users', 'gym_users', 'members', 'membership_plans',
    'memberships', 'payments', 'payment_reversals', 'audit_logs',
    'import_jobs', 'gym_settings'
];
$existingTables = Database::query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
foreach ($requiredTables as $t) {
    assert(in_array($t, $existingTables, true), "Missing expected table: {$t}");
}
echo " PASSED! (All 11 tables verified)\n";

// Test 3: Password Hash Verification
echo "  [Test 3] Verifying seeded owner authentication...";
$owner = Database::fetchOne("SELECT * FROM `users` WHERE `email` = 'owner@gymflow.test' LIMIT 1");
assert(!empty($owner), "Seeded owner must exist");
assert(password_verify('password123', $owner['password_hash']), "Owner password must verify with password123");
echo " PASSED! (Owner verified)\n";

// Test 4: Validator Test
echo "  [Test 4] Testing form Validator...";
$v = Validator::make([
    'email' => 'invalid-email',
    'password' => '123'
], [
    'email' => 'required|email',
    'password' => 'required|min:6'
]);
assert($v->fails() === true, "Validator should fail for bad email and short password");
assert(count($v->errors()) === 2, "Should have 2 validation errors");
echo " PASSED!\n";

// Test 5: CSRF Token Generation & Validation
echo "  [Test 5] Testing CSRF Token Service...";
Session::start();
$token = Csrf::token();
assert(!empty($token), "CSRF token must not be empty");
assert(Csrf::validate($token) === true, "Valid CSRF token must pass");
assert(Csrf::validate('fake-token') === false, "Fake CSRF token must fail");
echo " PASSED!\n";

// Test 6: Registration Transaction Test
echo "  [Test 6] Testing Gym Registration Workflow...";
$testEmail = 'newtestowner_' . time() . '@example.test';
$newGymName = 'Iron Pulse Gym ' . time();
$slug = 'iron-pulse-' . time();

$regResult = Database::transaction(function (\PDO $pdo) use ($newGymName, $slug, $testEmail) {
    $stmt = $pdo->prepare("INSERT INTO `gyms` (`slug`, `name`, `status`) VALUES (:slug, :name, 'active')");
    $stmt->execute([':slug' => $slug, ':name' => $newGymName]);
    $gId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `password_hash`, `status`) VALUES ('Test Owner', :email, :pwd, 'active')");
    $stmt->execute([':email' => $testEmail, ':pwd' => password_hash('pass123', PASSWORD_DEFAULT)]);
    $uId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO `gym_users` (`gym_id`, `user_id`, `role`, `is_active`) VALUES (:gId, :uId, 'owner', 1)");
    $stmt->execute([':gId' => $gId, ':uId' => $uId]);

    return ['gym_id' => $gId, 'user_id' => $uId];
});

assert($regResult['gym_id'] > 0, "Gym ID must be created");
assert($regResult['user_id'] > 0, "User ID must be created");

// Clean up test records
Database::query("DELETE FROM `gyms` WHERE `id` = :id", [':id' => $regResult['gym_id']]);
Database::query("DELETE FROM `users` WHERE `id` = :id", [':id' => $regResult['user_id']]);
echo " PASSED! (Registration transaction & cleanup verified)\n";

echo "\n🎉 All Phase 1 Integration Tests Passed Successfully!\n";
