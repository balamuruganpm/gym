<?php

declare(strict_types=1);

/**
 * GymFlow — Phase D Verification Test Suite
 * Validates Member Portal, Account Linking, Offline Payment Requests, Owner Approval/Rejection,
 * Double-Approval Prevention, Balance Invariants, and Payment Reversals.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\BillingService;
use App\Services\Config;
use App\Services\Database;
use App\Services\PaymentRequestService;

Config::load(__DIR__ . '/..');

echo "=======================================================\n";
echo " GymFlow — Phase D Verification Test Suite\n";
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
// Test Group 1: Database Schema Additions
// -----------------------------------------------------------------------------
echo "1. Testing Database Schema Additions...\n";

$tables = $pdo->query("SHOW TABLES LIKE 'member_payment_requests'")->fetchAll(PDO::FETCH_COLUMN);
assertTest(!empty($tables), "Table `member_payment_requests` exists in database");

$engine = $pdo->query("
    SELECT ENGINE FROM information_schema.tables 
    WHERE table_schema = DATABASE() AND table_name = 'member_payment_requests'
")->fetchColumn();
assertTest(strtoupper((string) $engine) === 'INNODB', "Table `member_payment_requests` is in InnoDB engine");

$cols = $pdo->query("DESCRIBE member_payment_requests")->fetchAll(PDO::FETCH_COLUMN);
$expectedCols = ['id', 'gym_id', 'member_id', 'membership_id', 'amount', 'payment_method', 'payment_date', 'reference_number', 'notes', 'status', 'reviewed_by_user_id', 'reviewed_at', 'rejection_reason', 'payment_id', 'created_at', 'updated_at'];
$hasAllCols = count(array_intersect($expectedCols, $cols)) === count($expectedCols);
assertTest($hasAllCols, "All 16 expected columns exist in `member_payment_requests`");

// -----------------------------------------------------------------------------
// Setup Test Fixtures: Operational Gym, Owner, Plan, Member, Membership
// -----------------------------------------------------------------------------
$gym = Database::fetchOne("SELECT * FROM `gyms` WHERE `status` = 'active' ORDER BY `id` ASC LIMIT 1");
$gymId = (int) ($gym['id'] ?? 1);

// Owner User
$owner = Database::fetchOne("
    SELECT u.* FROM `users` u
    INNER JOIN `gym_users` gu ON gu.user_id = u.id
    WHERE gu.gym_id = :g AND gu.role = 'owner' LIMIT 1
", [':g' => $gymId]);
$ownerUserId = (int) $owner['id'];

// Plan
$plan = Database::fetchOne("SELECT * FROM `membership_plans` WHERE `gym_id` = :g LIMIT 1", [':g' => $gymId]);
if (!$plan) {
    $pdo->prepare("
        INSERT INTO `membership_plans` (`gym_id`, `name`, `duration_days`, `price`, `is_active`, `created_at`)
        VALUES (:g, 'Quarterly Fitness Pass', 90, 4500.00, 1, NOW())
    ")->execute([':g' => $gymId]);
    $planId = (int) $pdo->lastInsertId();
    $planPrice = 4500.00;
} else {
    $planId = (int) $plan['id'];
    $planPrice = (float) $plan['price'];
}

// Test Member User
$testMemberEmail = 'member.test.portal@gymflow.local';
$memberUser = Database::fetchOne("SELECT * FROM `users` WHERE `email` = :e", [':e' => $testMemberEmail]);
if (!$memberUser) {
    $pdo->prepare("
        INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `status`, `created_at`)
        VALUES ('Test Portal Member', :e, :h, '9876543210', 'active', NOW())
    ")->execute([':e' => $testMemberEmail, ':h' => password_hash('MemberSecret123', PASSWORD_DEFAULT)]);
    $memberUserId = (int) $pdo->lastInsertId();
} else {
    $memberUserId = (int) $memberUser['id'];
}

// Link in gym_users
$gu = Database::fetchOne("SELECT * FROM `gym_users` WHERE `gym_id` = :g AND `user_id` = :u", [':g' => $gymId, ':u' => $memberUserId]);
if (!$gu) {
    $pdo->prepare("
        INSERT INTO `gym_users` (`gym_id`, `user_id`, `role`, `is_active`, `created_at`)
        VALUES (:g, :u, 'member', 1, NOW())
    ")->execute([':g' => $gymId, ':u' => $memberUserId]);
}

// Test Member Record in `members`
$memberRec = Database::fetchOne("SELECT * FROM `members` WHERE `gym_id` = :g AND `member_number` = 'TEST-PORTAL-01'", [':g' => $gymId]);
if (!$memberRec) {
    $pdo->prepare("
        INSERT INTO `members` (`gym_id`, `member_number`, `first_name`, `last_name`, `email`, `phone`, `user_id`, `is_archived`, `created_at`)
        VALUES (:g, 'TEST-PORTAL-01', 'Portal', 'Tester', :e, '9876543210', :u, 0, NOW())
    ")->execute([':g' => $gymId, ':e' => $testMemberEmail, ':u' => $memberUserId]);
    $memberId = (int) $pdo->lastInsertId();
} else {
    $memberId = (int) $memberRec['id'];
    $pdo->prepare("UPDATE `members` SET `user_id` = :u WHERE `id` = :id")->execute([':u' => $memberUserId, ':id' => $memberId]);
}

// Create an active Membership for this member with agreed_price = 3000.00
$membership = Database::fetchOne("
    SELECT * FROM `memberships` 
    WHERE `gym_id` = :g AND `member_id` = :m AND `plan_name_snapshot` = 'Phase D Test Pass'
", [':g' => $gymId, ':m' => $memberId]);

if (!$membership) {
    $pdo->prepare("
        INSERT INTO `memberships` (
            `gym_id`, `member_id`, `plan_id`, `plan_name_snapshot`,
            `start_date`, `expiry_date`, `original_price`, `discount_amount`, `agreed_price`, `status`, `created_at`
        ) VALUES (
            :g, :m, :p, 'Phase D Test Pass',
            CURRENT_DATE(), DATE_ADD(CURRENT_DATE(), INTERVAL 60 DAY), 3000.00, 0.00, 3000.00, 'active', NOW()
        )
    ")->execute([':g' => $gymId, ':m' => $memberId, ':p' => $planId]);
    $membershipId = (int) $pdo->lastInsertId();
} else {
    $membershipId = (int) $membership['id'];
}

// Clean up previous test payment requests for this test member
$pdo->prepare("DELETE FROM `member_payment_requests` WHERE `gym_id` = :g AND `member_id` = :m")
    ->execute([':g' => $gymId, ':m' => $memberId]);

// -----------------------------------------------------------------------------
// Test Group 2: Account Linking & Portal Access Invariants
// -----------------------------------------------------------------------------
echo "\n2. Testing Member Account Linking Invariants...\n";

$linkedMember = Database::fetchOne("SELECT * FROM `members` WHERE `id` = :id", [':id' => $memberId]);
assertTest((int) $linkedMember['user_id'] === $memberUserId, "Member record is linked to users ID #{$memberUserId}");

// Test Unique constraint: Attempting to link the same user_id to another member must fail
try {
    $pdo->prepare("
        INSERT INTO `members` (`gym_id`, `member_number`, `first_name`, `last_name`, `phone`, `user_id`, `is_archived`, `created_at`)
        VALUES (:g, 'TEST-DUP-USER', 'Dup', 'User', '9000000000', :u, 0, NOW())
    ")->execute([':g' => $gymId, ':u' => $memberUserId]);
    assertTest(false, "Duplicate user_id link in members was unexpectedly permitted");
} catch (\PDOException $e) {
    assertTest(true, "Duplicate user_id linking rejected by unique constraint: uk_member_user");
}

// -----------------------------------------------------------------------------
// Test Group 3: Offline Payment Request Submission
// -----------------------------------------------------------------------------
echo "\n3. Testing Offline Payment Request Submission...\n";

// Baseline balance before request
$initialBalance = BillingService::getMemberBalance($gymId, $memberId);

$requestId1 = PaymentRequestService::submitRequest($gymId, $memberId, [
    'membership_id' => $membershipId,
    'amount' => 1500.00,
    'payment_method' => 'upi',
    'payment_date' => date('Y-m-d'),
    'reference_number' => 'UPI-TEST-123456',
    'notes' => 'GooglePay payment submitted for verification',
]);

assertTest($requestId1 > 0, "PaymentRequestService::submitRequest returns valid request ID #{$requestId1}");

$req1 = Database::fetchOne("SELECT * FROM `member_payment_requests` WHERE `id` = :id", [':id' => $requestId1]);
assertTest($req1['status'] === 'pending', "Initial payment request status is 'pending'");
assertTest((float) $req1['amount'] === 1500.00, "Requested amount is 1500.00");
assertTest($req1['payment_method'] === 'upi', "Payment method is upi");
assertTest($req1['reference_number'] === 'UPI-TEST-123456', "Reference number matches");
assertTest($req1['payment_id'] === null, "payment_id is NULL while pending");

// Invariant: Submitting a request does NOT modify balance or revenue
$balanceAfterSubmission = BillingService::getMemberBalance($gymId, $memberId);
assertTest(
    abs($balanceAfterSubmission - $initialBalance) < 0.001,
    "Financial Invariant: Outstanding member balance unchanged by pending request ({$initialBalance} == {$balanceAfterSubmission})"
);

// Verify audit trail entry
$audit = Database::fetchOne("
    SELECT * FROM `audit_logs` 
    WHERE `gym_id` = :g AND `action` = 'payment_request.submitted' AND `entity_id` = :id
", [':g' => $gymId, ':id' => $requestId1]);
assertTest(!empty($audit), "Audit event `payment_request.submitted` logged in audit_logs");

// Test Validation: Invalid payment method
try {
    PaymentRequestService::submitRequest($gymId, $memberId, [
        'membership_id' => $membershipId,
        'amount' => 500.00,
        'payment_method' => 'bitcoin', // invalid
    ]);
    assertTest(false, "Invalid payment method unexpectedly accepted");
} catch (\InvalidArgumentException $e) {
    assertTest(true, "Invalid payment method rejected with InvalidArgumentException");
}

// Test Validation: Amount exceeding remaining balance
try {
    PaymentRequestService::submitRequest($gymId, $memberId, [
        'membership_id' => $membershipId,
        'amount' => 999999.00, // exceeds balance
    ]);
    assertTest(false, "Excessive amount exceeding balance unexpectedly accepted");
} catch (\InvalidArgumentException $e) {
    assertTest(true, "Request exceeding remaining balance rejected with InvalidArgumentException");
}

// -----------------------------------------------------------------------------
// Test Group 4: Owner Review — Approval Workflow
// -----------------------------------------------------------------------------
echo "\n4. Testing Owner Review — Approval Workflow...\n";

$createdPaymentId = PaymentRequestService::approveRequest($gymId, $requestId1, $ownerUserId);
assertTest($createdPaymentId > 0, "PaymentRequestService::approveRequest returns valid created payment ID #{$createdPaymentId}");

// Verify request record updated
$req1After = Database::fetchOne("SELECT * FROM `member_payment_requests` WHERE `id` = :id", [':id' => $requestId1]);
assertTest($req1After['status'] === 'approved', "Payment request status changed to 'approved'");
assertTest((int) $req1After['reviewed_by_user_id'] === $ownerUserId, "Reviewer user ID matches owner ID");
assertTest(!empty($req1After['reviewed_at']), "reviewed_at timestamp recorded");
assertTest((int) $req1After['payment_id'] === $createdPaymentId, "Request linked to created payment ID");

// Verify official payment record created in `payments`
$paymentRec = Database::fetchOne("SELECT * FROM `payments` WHERE `id` = :id", [':id' => $createdPaymentId]);
assertTest(!empty($paymentRec), "Official payment record exists in `payments` table");
assertTest($paymentRec['status'] === 'paid', "Payment status is 'paid'");
assertTest((float) $paymentRec['amount'] === 1500.00, "Payment amount is 1500.00");
assertTest($paymentRec['payment_method'] === 'upi', "Payment method is upi");
assertTest($paymentRec['reference_number'] === 'UPI-TEST-123456', "Payment reference number matches");
assertTest(!empty($paymentRec['receipt_number']), "Official receipt number generated: {$paymentRec['receipt_number']}");

// Financial Invariant: Approved payment must decrease member outstanding balance
$balanceAfterApproval = BillingService::getMemberBalance($gymId, $memberId);
$expectedBalance = $initialBalance - 1500.00;
assertTest(
    abs($balanceAfterApproval - $expectedBalance) < 0.001,
    "Financial Invariant: Member balance decreased by approved amount (New balance: {$balanceAfterApproval})"
);

// Verify audit trail for approval and payment creation
$auditApprove = Database::fetchOne("
    SELECT * FROM `audit_logs` 
    WHERE `gym_id` = :g AND `action` = 'payment_request.approved' AND `entity_id` = :id
", [':g' => $gymId, ':id' => $requestId1]);
assertTest(!empty($auditApprove), "Audit event `payment_request.approved` logged");

// Concurrency & Double-Approval Defense:
try {
    PaymentRequestService::approveRequest($gymId, $requestId1, $ownerUserId);
    assertTest(false, "Double approval of already approved request was unexpectedly permitted");
} catch (\InvalidArgumentException $e) {
    assertTest(true, "Double approval strictly blocked: " . $e->getMessage());
}

// -----------------------------------------------------------------------------
// Test Group 5: Owner Review — Rejection Workflow
// -----------------------------------------------------------------------------
echo "\n5. Testing Owner Review — Rejection Workflow...\n";

// Submit a second request to test rejection
$requestId2 = PaymentRequestService::submitRequest($gymId, $memberId, [
    'membership_id' => $membershipId,
    'amount' => 500.00,
    'payment_method' => 'bank_transfer',
    'payment_date' => date('Y-m-d'),
    'reference_number' => 'NEFT-FAKE-999',
    'notes' => 'Attempting fake transaction',
]);

// Test: Rejection requires a reason
try {
    PaymentRequestService::rejectRequest($gymId, $requestId2, $ownerUserId, "   ");
    assertTest(false, "Rejection without reason unexpectedly allowed");
} catch (\InvalidArgumentException $e) {
    assertTest(true, "Rejection without reason strictly rejected: " . $e->getMessage());
}

// Reject with valid reason
$rejectSuccess = PaymentRequestService::rejectRequest(
    $gymId,
    $requestId2,
    $ownerUserId,
    "UTR reference number not found in bank statement"
);
assertTest($rejectSuccess === true, "PaymentRequestService::rejectRequest returns true");

$req2After = Database::fetchOne("SELECT * FROM `member_payment_requests` WHERE `id` = :id", [':id' => $requestId2]);
assertTest($req2After['status'] === 'rejected', "Payment request status is 'rejected'");
assertTest($req2After['rejection_reason'] === "UTR reference number not found in bank statement", "Rejection reason saved");
assertTest((int) $req2After['reviewed_by_user_id'] === $ownerUserId, "Reviewed by user ID recorded");
assertTest($req2After['payment_id'] === null, "payment_id remains NULL for rejected request");

// Financial Invariant: Rejection does NOT change balance
$balanceAfterRejection = BillingService::getMemberBalance($gymId, $memberId);
assertTest(
    abs($balanceAfterRejection - $balanceAfterApproval) < 0.001,
    "Financial Invariant: Member balance unchanged by rejected request"
);

// Prevent re-processing rejected request
try {
    PaymentRequestService::approveRequest($gymId, $requestId2, $ownerUserId);
    assertTest(false, "Approving rejected request was unexpectedly allowed");
} catch (\InvalidArgumentException $e) {
    assertTest(true, "Processing already rejected request strictly blocked: " . $e->getMessage());
}

// -----------------------------------------------------------------------------
// Test Group 6: Payment Reversal Preservation
// -----------------------------------------------------------------------------
echo "\n6. Testing Payment Reversal Preservation...\n";

// Void the payment that was created from request 1
$voidSuccess = BillingService::voidPayment($gymId, $createdPaymentId, $ownerUserId, "Member cheque bounced or reversed");
assertTest($voidSuccess === true, "BillingService::voidPayment succeeded");

// Verify payment request record is preserved with payment_id link
$req1AfterVoid = Database::fetchOne("SELECT * FROM `member_payment_requests` WHERE `id` = :id", [':id' => $requestId1]);
assertTest($req1AfterVoid['status'] === 'approved', "Payment request history preserved as 'approved'");
assertTest((int) $req1AfterVoid['payment_id'] === $createdPaymentId, "Payment request link to payment_id #{$createdPaymentId} preserved");

// Verify reversal record exists in payment_reversals
$rev = Database::fetchOne("SELECT * FROM `payment_reversals` WHERE `payment_id` = :p", [':p' => $createdPaymentId]);
assertTest(!empty($rev), "Payment reversal record exists in `payment_reversals`");
assertTest($rev['reason'] === "Member cheque bounced or reversed", "Reversal reason matches");

// Financial Invariant: Voided payment restores outstanding balance
$balanceAfterVoid = BillingService::getMemberBalance($gymId, $memberId);
assertTest(
    abs($balanceAfterVoid - $initialBalance) < 0.001,
    "Financial Invariant: Member balance restored after payment void ({$balanceAfterVoid} == {$initialBalance})"
);

// -----------------------------------------------------------------------------
// Test Group 7: Member Portal Queries & Isolation
// -----------------------------------------------------------------------------
echo "\n7. Testing Member Portal Queries & Isolation...\n";

$memberRequests = PaymentRequestService::getMemberRequests($gymId, $memberId);
assertTest(count($memberRequests) >= 2, "PaymentRequestService::getMemberRequests returns member's requests");

// Verify member cannot see other members' requests
$otherMemberId = 999999;
$otherRequests = PaymentRequestService::getMemberRequests($gymId, $otherMemberId);
assertTest(empty($otherRequests), "Unrelated member ID returns 0 requests (data isolation verified)");

// -----------------------------------------------------------------------------
// Test Group 8: Controllers & Views Sanity
// -----------------------------------------------------------------------------
echo "\n8. Testing Controllers & Views Sanity...\n";

assertTest(class_exists('App\Services\PaymentRequestService'), "PaymentRequestService class exists");
assertTest(class_exists('App\Controllers\PaymentRequestController'), "PaymentRequestController class exists");
assertTest(class_exists('App\Controllers\MemberPortalController'), "MemberPortalController class exists");

assertTest(file_exists(__DIR__ . '/../app/Views/payment_requests/index.php'), "View file `payment_requests/index.php` exists on disk");
assertTest(file_exists(__DIR__ . '/../app/Views/portal/dashboard.php'), "View file `portal/dashboard.php` exists on disk");

echo "\n=======================================================\n";
echo " Phase D Test Results: {$passed}/" . ($passed + $failed) . " assertions passed\n";
if ($failed === 0) {
    echo " ALL PHASE D TESTS PASSED (100%)\n";
} else {
    echo " SOME TESTS FAILED ({$failed} failures)\n";
}
echo "=======================================================\n\n";

exit($failed > 0 ? 1 : 0);
