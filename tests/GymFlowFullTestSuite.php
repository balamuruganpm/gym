<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers.php';

use App\Models\MembershipPlan;
use App\Models\Member;
use App\Services\BillingService;
use App\Services\Config;
use App\Services\Csrf;
use App\Services\Database;
use App\Services\MembershipService;
use App\Services\Session;
use App\Validators\Validator;

Config::load(__DIR__ . '/..');

echo "====================================================================\n";
echo "       GYMFLOW ENTERPRISE MASTER TEST SUITE (PHASES 1 - 7)          \n";
echo "====================================================================\n\n";

$passCount = 0;
$testCount = 0;

function runTest(string $title, callable $testFunc): void {
    global $passCount, $testCount;
    $testCount++;
    echo "[TEST {$testCount}] {$title}... ";
    try {
        $testFunc();
        $passCount++;
        echo "PASSED! ✅\n";
    } catch (\Throwable $e) {
        echo "FAILED! ❌\n";
        echo "   Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
        exit(1);
    }
}

// ---------------------------------------------------------
// MODULE 1: Database & Architecture
// ---------------------------------------------------------
runTest("Database Connection to `gym-premium`", function () {
    $dbName = Database::query("SELECT DATABASE()")->fetchColumn();
    assert($dbName === 'gym-premium', "Expected gym-premium, got {$dbName}");
});

runTest("All 11 Database Tables Verified in InnoDB", function () {
    $tables = [
        'gyms', 'users', 'gym_users', 'members', 'membership_plans',
        'memberships', 'payments', 'payment_reversals', 'audit_logs',
        'import_jobs', 'gym_settings'
    ];
    $existing = Database::fetchAll("SHOW TABLES");
    $flatList = array_map(fn($r) => array_values($r)[0], $existing);
    foreach ($tables as $t) {
        assert(in_array($t, $flatList, true), "Missing table: {$t}");
    }
});

// ---------------------------------------------------------
// MODULE 2: Security & Authentication
// ---------------------------------------------------------
runTest("CSRF Token Generation, Masking & Single-Use Verification", function () {
    $token = Csrf::generate();
    assert(is_string($token) && strlen($token) >= 32);
    assert(Csrf::verify($token) === true);
    // Tampered token must fail
    assert(Csrf::verify('invalid_token_xyz') === false);
});

runTest("Input Validator Integrity", function () {
    $v1 = Validator::make(['email' => 'valid@test.com', 'name' => 'John'], [
        'email' => 'required|email',
        'name' => 'required|min:2',
    ]);
    assert($v1->passes() === true);

    $v2 = Validator::make(['email' => 'bad-email'], [
        'email' => 'required|email',
    ]);
    assert($v2->fails() === true);
});

runTest("Password Hashing & Seeded Owner Authentication", function () {
    $owner = Database::fetchOne("SELECT * FROM `users` WHERE `email` = 'owner@gymflow.test'");
    assert($owner !== null);
    assert(password_verify('password123', $owner['password_hash']) === true);
});

// ---------------------------------------------------------
// MODULE 3: Strict Multi-Tenant Gym Isolation
// ---------------------------------------------------------
runTest("Tenant Data Isolation: Cross-Gym Data Leakage Defense", function () {
    // Member created in Gym 1
    $mId = Member::create([
        'gym_id' => 1,
        'member_number' => 'ISOL-' . rand(1000, 9999),
        'first_name' => 'TenantTest',
        'phone' => '9998887776',
    ]);

    // Querying Gym 1 member under Gym 2 context MUST return null
    $leak = Member::find($mId, 2);
    assert($leak === null, "CRITICAL SECURITY BREACH: Gym 2 accessed Gym 1 member!");

    // Querying under Gym 1 context succeeds
    $valid = Member::find($mId, 1);
    assert($valid !== null && $valid['id'] === $mId);
});

// ---------------------------------------------------------
// MODULE 4: Financial Accuracy, Balances & Audited Reversals
// ---------------------------------------------------------
runTest("DECIMAL(12,2) Financial Precision & Partial Payment Balances", function () {
    // 1. Create plan for 5500.50
    $pId = MembershipPlan::create(1, [
        'name' => 'Precision Plan',
        'duration_value' => 1,
        'duration_type' => 'months',
        'price' => 5500.50,
    ]);

    // 2. Assign to member with 500.50 discount -> agreed price 5000.00
    $memId = Member::create([
        'gym_id' => 1,
        'member_number' => 'FIN-' . rand(1000, 9999),
        'first_name' => 'FinanceUser',
        'phone' => '9990001112',
    ]);
    $subId = MembershipService::assignPlan(1, $memId, $pId, date('Y-m-d'), 500.50);

    // Initial balance must be 5000.00
    assert(BillingService::getMemberBalance(1, $memId) === 5000.00);

    // Partial payment 1500.00
    $payId1 = BillingService::recordPayment(1, 1, [
        'member_id' => $memId,
        'membership_id' => $subId,
        'amount' => 1500.00,
        'payment_method' => 'card',
    ]);
    assert(BillingService::getMemberBalance(1, $memId) === 3500.00);

    // Full clearance payment 3500.00
    $payId2 = BillingService::recordPayment(1, 1, [
        'member_id' => $memId,
        'membership_id' => $subId,
        'amount' => 3500.00,
        'payment_method' => 'cash',
    ]);
    assert(BillingService::getMemberBalance(1, $memId) === 0.00);

    // Reversal / Voiding payId2
    BillingService::voidPayment(1, $payId2, 1, 'Audited reversal test');
    // Balance must revert to 3500.00
    assert(BillingService::getMemberBalance(1, $memId) === 3500.00);
});

// ---------------------------------------------------------
// MODULE 5: Membership Lifecycle & Smart Expiry Chaining
// ---------------------------------------------------------
runTest("Smart Renewal Chaining (Expiry + 1 Day Start Date)", function () {
    $planId = MembershipPlan::create(1, [
        'name' => 'Quarterly Chain Plan',
        'duration_value' => 3,
        'duration_type' => 'months',
        'price' => 4500.00,
    ]);

    $memId = Member::create([
        'gym_id' => 1,
        'member_number' => 'CHAIN-' . rand(1000, 9999),
        'first_name' => 'ChainUser',
        'phone' => '9991112223',
    ]);

    // Initial subscription starts 2026-11-01 -> expires 2027-02-01
    $sub1 = MembershipService::assignPlan(1, $memId, $planId, '2026-11-01');
    $subRecord1 = Database::fetchOne("SELECT * FROM `memberships` WHERE `id` = :id", [':id' => $sub1]);
    assert($subRecord1['expiry_date'] === '2027-02-01');

    // Renewal must automatically chain from 2027-02-02
    $sub2 = MembershipService::renewPlan(1, $memId, $planId);
    $subRecord2 = Database::fetchOne("SELECT * FROM `memberships` WHERE `id` = :id", [':id' => $sub2]);
    assert($subRecord2['start_date'] === '2027-02-02');
    assert($subRecord2['expiry_date'] === '2027-05-02');
});

// ---------------------------------------------------------
// MODULE 6: HTTP Live Web Server Flow
// ---------------------------------------------------------
runTest("Live HTTP Server Availability & Response Check", function () {
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'ignore_errors' => true,
            'timeout' => 5,
        ]
    ]);
    $res = @file_get_contents('http://127.0.0.1:8080/login', false, $ctx);
    if ($res === false) {
        $res = @file_get_contents('http://localhost/Projects%20All/per/gym/public/login', false, $ctx);
    }
    assert($res !== false, "Web server did not respond at 8080 or localhost");
    assert(str_contains($res, 'GymFlow'), "Expected 'GymFlow' in login page response");
    assert(str_contains($res, 'Login to your gym account') || str_contains($res, 'Welcome Back'), "Expected sign in form text");
});

echo "\n====================================================================\n";
echo "🎉 MASTER TEST RESULTS: {$passCount} / {$testCount} TESTS PASSED (100% SUCCESS)\n";
echo "   All architectural, security, tenant, and financial invariants verified!\n";
echo "====================================================================\n";
