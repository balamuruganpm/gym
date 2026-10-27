<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers.php';

use App\Models\Member;
use App\Services\Config;
use App\Services\Database;
use App\Services\Session;
use App\Services\TenantContext;

Config::load(__DIR__ . '/..');

echo "=== Running Phase 2 Tests (Member Management & Tenancy) ===\n\n";

// 1. Setup Database Connection
$pdo = Database::getConnection();
echo "[PASS] Database connected successfully.\n";

// Ensure gym 1 and gym 2 exist
$gym1 = Database::fetchOne("SELECT * FROM `gyms` WHERE `id` = 1");
$gym2 = Database::fetchOne("SELECT * FROM `gyms` WHERE `id` = 2");

if (!$gym2) {
    Database::execute("
        INSERT INTO `gyms` (`id`, `name`, `slug`, `phone`, `email`, `currency_code`, `created_at`)
        VALUES (2, 'Iron Gym Branch 2', 'iron', '9876543211', 'iron@gym.test', 'INR', NOW())
    ");
    $gym2 = Database::fetchOne("SELECT * FROM `gyms` WHERE `id` = 2");
}

assert($gym1 !== null && $gym2 !== null);
echo "[PASS] Multi-tenant test gyms verified (Gym 1 & Gym 2).\n";

// 2. Test Member Number Generation
$num1 = Member::generateMemberNumber(1);
$num2 = Member::generateMemberNumber(1);
assert(!empty($num1));
echo "[PASS] Member number generation: {$num1}\n";

// 3. Test Member Creation for Gym 1
$memberNumber = 'TEST-' . rand(10000, 99999);
$stmt = $pdo->prepare("
    INSERT INTO `members` (
        `gym_id`, `member_number`, `first_name`, `last_name`, 
        `phone`, `email`, `gender`, `is_archived`, `created_at`
    ) VALUES (
        1, :num, 'Vikram', 'Sharma', '9988776655', 'vikram@test.com', 'male', 0, NOW()
    )
");
$stmt->execute([':num' => $memberNumber]);
$memberId = (int) $pdo->lastInsertId();
assert($memberId > 0);
echo "[PASS] Member created in Gym 1 with ID {$memberId} (#{$memberNumber}).\n";

// 4. Test Multi-Tenant Isolation
// Attempt to find Gym 1's member while scoped to Gym 2 -> MUST RETURN NULL
$notFoundInGym2 = Member::find($memberId, 2);
assert($notFoundInGym2 === null, "SECURITY VIOLATION: Gym 2 was able to view Gym 1 member!");
echo "[PASS] Strict Multi-Tenant Isolation: Gym 2 cannot view Gym 1's member.\n";

// Found in Gym 1
$foundInGym1 = Member::find($memberId, 1);
assert($foundInGym1 !== null && $foundInGym1['member_number'] === $memberNumber);
echo "[PASS] Member successfully retrieved in Gym 1 context.\n";

// 5. Test Search & Listing Filters
$list = Member::getAll(1, ['search' => 'Vikram', 'status' => 'all'], 1, 10);
assert(count($list) >= 1);
assert($list[0]['first_name'] === 'Vikram');
echo "[PASS] Member search filter working correctly.\n";

// 6. Test Member Update
Member::update($memberId, 1, [
    'first_name' => 'Vikram Updated',
    'last_name' => 'Sharma',
    'phone' => '9988776655',
    'email' => 'vikram.updated@test.com',
    'gender' => 'male',
    'date_of_birth' => '1995-05-15',
    'address' => '42 Test Street, Mumbai',
    'emergency_contact_name' => 'Anil Sharma',
    'emergency_contact_phone' => '9988776600',
    'photo_path' => null,
    'notes' => 'Testing note',
]);

$updated = Member::find($memberId, 1);
assert($updated['first_name'] === 'Vikram Updated');
assert($updated['email'] === 'vikram.updated@test.com');
echo "[PASS] Member update working correctly.\n";

// 7. Test Member Archive and Restore
Member::archive($memberId, 1);
$archived = Member::find($memberId, 1);
assert((int) $archived['is_archived'] === 1);
echo "[PASS] Member archive working correctly.\n";

Member::restore($memberId, 1);
$restored = Member::find($memberId, 1);
assert((int) $restored['is_archived'] === 0);
echo "[PASS] Member restore working correctly.\n";

// 8. Test Member 360 Profile Retrieval
$profile = Member::getProfile($memberId, 1);
assert(isset($profile['member']));
assert(isset($profile['memberships']));
assert(isset($profile['payments']));
assert(isset($profile['total_due_overall']));
echo "[PASS] Member 360 profile structure verified.\n";

echo "\n>>> ALL PHASE 2 TESTS PASSED PERFECTLY! <<<\n";
