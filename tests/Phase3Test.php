<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers.php';

use App\Models\MembershipPlan;
use App\Models\Member;
use App\Services\Config;
use App\Services\Database;
use App\Services\MembershipService;

Config::load(__DIR__ . '/..');

echo "=== Running Phase 3 Tests (Plans, Renewals & WhatsApp) ===\n\n";

$pdo = Database::getConnection();

// 1. Create a Plan for Gym 1
$planName = 'Test Plan ' . rand(100, 999);
$planId = MembershipPlan::create(1, [
    'name' => $planName,
    'description' => 'Test plan description',
    'duration_value' => 3,
    'duration_type' => 'months',
    'price' => 4500.00,
    'is_active' => 1,
]);
assert($planId > 0);
echo "[PASS] Plan created successfully with ID {$planId}.\n";

// 2. Multi-Tenant Isolation: Gym 2 cannot find Gym 1's plan
$notFoundGym2 = MembershipPlan::find($planId, 2);
assert($notFoundGym2 === null, "SECURITY VIOLATION: Gym 2 accessed Gym 1's plan!");
echo "[PASS] Multi-tenant isolation verified on membership plans.\n";

// 3. Update Plan
MembershipPlan::update($planId, 1, [
    'name' => $planName . ' Updated',
    'description' => 'Updated description',
    'duration_value' => 6,
    'duration_type' => 'months',
    'price' => 8000.00,
    'is_active' => 1,
]);
$updatedPlan = MembershipPlan::find($planId, 1);
assert($updatedPlan['name'] === $planName . ' Updated');
assert((float) $updatedPlan['price'] === 8000.00);
echo "[PASS] Plan update verified.\n";

// 4. Toggle Status
MembershipPlan::toggleStatus($planId, 1);
$toggled = MembershipPlan::find($planId, 1);
assert((int) $toggled['is_active'] === 0);
MembershipPlan::toggleStatus($planId, 1);
$reToggled = MembershipPlan::find($planId, 1);
assert((int) $reToggled['is_active'] === 1);
echo "[PASS] Plan status toggle verified.\n";

// 5. Test Expiry Calculation
$expiry3M = MembershipService::calculateExpiry('2026-01-01', 3, 'months');
assert($expiry3M === '2026-04-01');

$expiry30D = MembershipService::calculateExpiry('2026-01-01', 30, 'days');
assert($expiry30D === '2026-01-31');
echo "[PASS] Membership expiry calculation verified (months & days).\n";

// 6. Test Member Subscription Assignment & Smart Renewal Chaining
// Create test member
$memNum = 'MEM-' . rand(1000, 9999);
$memberId = Member::create([
    'gym_id' => 1,
    'member_number' => $memNum,
    'first_name' => 'Kavita',
    'last_name' => 'Patel',
    'phone' => '9876543210',
]);

// Initial assignment: Start Today
$memSub1 = MembershipService::assignPlan(1, $memberId, $planId, '2026-05-01');
$subRecord1 = Database::fetchOne("SELECT * FROM `memberships` WHERE `id` = :id", [':id' => $memSub1]);
assert($subRecord1['start_date'] === '2026-05-01');
assert($subRecord1['expiry_date'] === '2026-11-01');
echo "[PASS] Initial plan assignment verified.\n";

// Smart renewal test:
// If member currently has an active plan expiring 2026-11-01,
// calling renewPlan should start the next membership on 2026-11-02!
$memSub2 = MembershipService::renewPlan(1, $memberId, $planId, 500.00);
$subRecord2 = Database::fetchOne("SELECT * FROM `memberships` WHERE `id` = :id", [':id' => $memSub2]);

assert((float) $subRecord2['discount_amount'] === 500.00);
assert((float) $subRecord2['agreed_price'] === 7500.00);
assert($subRecord2['start_date'] === '2026-11-02', "Expected start_date 2026-11-02, got {$subRecord2['start_date']}");
echo "[PASS] Smart renewal chaining verified (seamless extension from current expiry + 1 day).\n";

// 7. Test Expiring & Expired queries
$expiring7 = MembershipService::getExpiringMembers(1, 7);
$expiredList = MembershipService::getExpiredMembers(1);
assert(is_array($expiring7));
assert(is_array($expiredList));
echo "[PASS] Expiring & Expired queries executed without errors.\n";

// 8. Test WhatsApp reminder generator
$waUrl = MembershipService::generateWhatsAppReminder(
    ['name' => 'Gold Fitness Gym'],
    ['first_name' => 'Kavita', 'phone' => '9876543210'],
    ['plan_name_snapshot' => 'Annual Gold', 'expiry_date' => '2026-11-01', 'days_left' => 5]
);
assert(str_starts_with($waUrl, 'https://wa.me/919876543210?text='));
assert(str_contains(urldecode($waUrl), 'Gold Fitness Gym'));
assert(str_contains(urldecode($waUrl), 'Annual Gold'));
echo "[PASS] WhatsApp 1-click reminder link generation verified.\n";

echo "\n>>> ALL PHASE 3 TESTS PASSED PERFECTLY! <<<\n";
