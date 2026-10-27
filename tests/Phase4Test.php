<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers.php';

use App\Models\MembershipPlan;
use App\Models\Member;
use App\Services\BillingService;
use App\Services\Config;
use App\Services\Database;
use App\Services\MembershipService;

Config::load(__DIR__ . '/..');

echo "=== Running Phase 4 Tests (Payments, Balances, Receipts & Audited Reversals) ===\n\n";

$pdo = Database::getConnection();

// 1. Create Member and Plan for Gym 1
$memberId = Member::create([
    'gym_id' => 1,
    'member_number' => 'BILL-' . rand(1000, 9999),
    'first_name' => 'Arjun',
    'last_name' => 'Kapoor',
    'phone' => '9876500001',
]);

$planId = MembershipPlan::create(1, [
    'name' => 'Billing Test Plan',
    'duration_value' => 1,
    'duration_type' => 'months',
    'price' => 3000.00,
    'is_active' => 1,
]);

$membershipId = MembershipService::assignPlan(1, $memberId, $planId, date('Y-m-d'));
assert($membershipId > 0);

// Check initial balance: Should be 3000.00
$initialBalance = BillingService::getMemberBalance(1, $memberId);
assert($initialBalance === 3000.00, "Initial balance should be 3000, got: {$initialBalance}");
echo "[PASS] Initial balance computed accurately (3000.00).\n";

// 2. Partial Payment Test: Pay 1000.00
$pay1 = BillingService::recordPayment(1, 1, [
    'member_id' => $memberId,
    'membership_id' => $membershipId,
    'amount' => 1000.00,
    'payment_method' => 'upi',
    'reference_number' => 'UPI-TEST-1234',
]);
assert($pay1 > 0);

$balanceAfterPay1 = BillingService::getMemberBalance(1, $memberId);
assert($balanceAfterPay1 === 2000.00, "Balance after partial payment should be 2000, got: {$balanceAfterPay1}");
echo "[PASS] Partial payment recorded and remaining balance updated to 2000.00.\n";

// 3. Second Payment Test: Pay remaining 2000.00
$pay2 = BillingService::recordPayment(1, 1, [
    'member_id' => $memberId,
    'membership_id' => $membershipId,
    'amount' => 2000.00,
    'payment_method' => 'cash',
]);
assert($pay2 > 0);

$balanceAfterPay2 = BillingService::getMemberBalance(1, $memberId);
assert($balanceAfterPay2 === 0.00, "Balance after full payment should be 0, got: {$balanceAfterPay2}");
echo "[PASS] Full clearance payment recorded; outstanding balance is now 0.00.\n";

// 4. Receipt Details Verification
$receipt = BillingService::getReceiptDetails(1, $pay1);
assert($receipt !== null);
assert($receipt['member_number'] !== null);
assert((float) $receipt['amount'] === 1000.00);
assert($receipt['plan_name_snapshot'] === 'Billing Test Plan');
assert($receipt['gym_name'] !== null);
echo "[PASS] Receipt details verified with complete member and gym metadata.\n";

// 5. Multi-Tenant Isolation: Gym 2 cannot view or void Gym 1's payment
$receiptGym2 = BillingService::getReceiptDetails(2, $pay1);
assert($receiptGym2 === null, "SECURITY VIOLATION: Gym 2 accessed Gym 1's payment receipt!");
echo "[PASS] Multi-tenant isolation verified on payments.\n";

// 6. Audited Reversal / Voiding Test:
// Void pay2 (2000.00). Balance should return to 2000.00!
$voidSuccess = BillingService::voidPayment(1, $pay2, 1, 'Customer payment cheque bounced during verification');
assert($voidSuccess === true);

// Verify payment status is voided
$pay2Record = Database::fetchOne("SELECT * FROM `payments` WHERE `id` = :id", [':id' => $pay2]);
assert($pay2Record['status'] === 'voided');

// Verify reversal record exists
$reversal = Database::fetchOne("SELECT * FROM `payment_reversals` WHERE `payment_id` = :id", [':id' => $pay2]);
assert($reversal !== null);
assert((float) $reversal['amount'] === 2000.00);
assert($reversal['reason'] === 'Customer payment cheque bounced during verification');

// Verify audit log exists
$audit = Database::fetchOne("SELECT * FROM `audit_logs` WHERE `action` = 'payment.voided' AND `entity_id` = :id", [':id' => $pay2]);
assert($audit !== null);

// Check balance after void: MUST be 2000.00 again!
$balanceAfterVoid = BillingService::getMemberBalance(1, $memberId);
assert($balanceAfterVoid === 2000.00, "Balance after void should revert to 2000, got: {$balanceAfterVoid}");
echo "[PASS] Audited payment voiding verified: balance reverted from 0 to 2000.00 with audit trail.\n";

echo "\n>>> ALL PHASE 4 TESTS PASSED PERFECTLY! <<<\n";
