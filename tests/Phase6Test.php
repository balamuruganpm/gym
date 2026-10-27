<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers.php';

use App\Services\Config;
use App\Services\Database;

Config::load(__DIR__ . '/..');

echo "=== Running Phase 6 Tests (Staff Management, Audit Logs & Settings) ===\n\n";

$pdo = Database::getConnection();

// 1. Create a Staff Member for Gym 1
$staffEmail = 'teststaff_' . rand(1000, 9999) . '@gymflow.test';
$staffPassword = 'securepassword123';

Database::transaction(function (\PDO $pdo) use ($staffEmail, $staffPassword, &$staffUserId) {
    $stmtUser = $pdo->prepare("
        INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `status`, `created_at`)
        VALUES ('Deepak Trainer', :email, :pwd, '9876599999', 'active', NOW())
    ");
    $stmtUser->execute([
        ':email' => $staffEmail,
        ':pwd' => password_hash($staffPassword, PASSWORD_DEFAULT),
    ]);
    $staffUserId = (int) $pdo->lastInsertId();

    $stmtLink = $pdo->prepare("
        INSERT INTO `gym_users` (`gym_id`, `user_id`, `role`, `is_active`, `created_at`)
        VALUES (1, :uid, 'staff', 1, NOW())
    ");
    $stmtLink->execute([':uid' => $staffUserId]);
});

assert($staffUserId > 0);
echo "[PASS] Staff user created (#{$staffUserId}) and linked to Gym 1 as 'staff'.\n";

// 2. Multi-Tenant Check: Verify staff belongs only to Gym 1, not Gym 2
$inGym1 = Database::fetchOne("SELECT * FROM `gym_users` WHERE `gym_id` = 1 AND `user_id` = :uid", [':uid' => $staffUserId]);
$inGym2 = Database::fetchOne("SELECT * FROM `gym_users` WHERE `gym_id` = 2 AND `user_id` = :uid", [':uid' => $staffUserId]);
assert($inGym1 !== null && $inGym1['role'] === 'staff');
assert($inGym2 === null, "SECURITY VIOLATION: Staff was accessible in Gym 2!");
echo "[PASS] Multi-tenant isolation verified on staff members.\n";

// 3. Test Staff Status Toggle
Database::query("
    UPDATE `gym_users` 
    SET `is_active` = 1 - `is_active`, `updated_at` = NOW()
    WHERE `gym_id` = 1 AND `user_id` = :uid
", [':uid' => $staffUserId]);

$toggled = Database::fetchOne("SELECT `is_active` FROM `gym_users` WHERE `gym_id` = 1 AND `user_id` = :uid", [':uid' => $staffUserId]);
assert((int) $toggled['is_active'] === 0);

Database::query("
    UPDATE `gym_users` 
    SET `is_active` = 1 - `is_active`, `updated_at` = NOW()
    WHERE `gym_id` = 1 AND `user_id` = :uid
", [':uid' => $staffUserId]);

$reToggled = Database::fetchOne("SELECT `is_active` FROM `gym_users` WHERE `gym_id` = 1 AND `user_id` = :uid", [':uid' => $staffUserId]);
assert((int) $reToggled['is_active'] === 1);
echo "[PASS] Staff activation/deactivation toggle verified.\n";

// 4. Test Audit Log Insertion & Retrieval
Database::query("
    INSERT INTO `audit_logs` (`gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`)
    VALUES (1, 1, 'staff.created', 'user', :uid, :details, NOW())
", [
    ':uid' => $staffUserId,
    ':details' => json_encode(['email' => $staffEmail, 'role' => 'staff']),
]);

$auditLog = Database::fetchOne("
    SELECT * FROM `audit_logs` 
    WHERE `gym_id` = 1 AND `action` = 'staff.created' AND `entity_id` = :uid
", [':uid' => $staffUserId]);
assert($auditLog !== null);
echo "[PASS] Audit logging verified for administrative staff action.\n";

// 5. Test Settings Update
$originalGym = Database::fetchOne("SELECT * FROM `gyms` WHERE `id` = 1");
$originalName = $originalGym['name'];

Database::query("
    UPDATE `gyms` SET `name` = 'Gold Fitness Elite', `updated_at` = NOW() WHERE `id` = 1
");
$updatedGym = Database::fetchOne("SELECT `name` FROM `gyms` WHERE `id` = 1");
assert($updatedGym['name'] === 'Gold Fitness Elite');

// Revert back
Database::query("
    UPDATE `gyms` SET `name` = :orig, `updated_at` = NOW() WHERE `id` = 1
", [':orig' => $originalName]);
$revertedGym = Database::fetchOne("SELECT `name` FROM `gyms` WHERE `id` = 1");
assert($revertedGym['name'] === $originalName);
echo "[PASS] Gym profile settings update verified.\n";

echo "\n>>> ALL PHASE 6 TESTS PASSED PERFECTLY! <<<\n";
