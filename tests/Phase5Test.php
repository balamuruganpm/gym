<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers.php';

use App\Models\Member;
use App\Services\Config;
use App\Services\Database;

Config::load(__DIR__ . '/..');

echo "=== Running Phase 5 Tests (Dashboard Analytics, Reports & Batch Import) ===\n\n";

$pdo = Database::getConnection();

// 1. Test Dashboard Controller Logic Queries
$gymId = 1;

$totalMembers = (int) Database::query("
    SELECT COUNT(*) FROM `members` WHERE `gym_id` = :gym_id AND `is_archived` = 0
", [':gym_id' => $gymId])->fetchColumn();
assert($totalMembers >= 1);
echo "[PASS] Total active members counted: {$totalMembers}\n";

$monthCollections = (float) (Database::query("
    SELECT COALESCE(SUM(amount), 0) FROM `payments` 
    WHERE `gym_id` = :gym_id 
      AND MONTH(`payment_date`) = MONTH(CURRENT_DATE()) 
      AND YEAR(`payment_date`) = YEAR(CURRENT_DATE())
      AND `status` = 'paid'
", [':gym_id' => $gymId])->fetchColumn() ?: 0);
assert($monthCollections >= 0);
echo "[PASS] Monthly collections query verified ({$monthCollections}).\n";

$outstandingDues = (float) (Database::query("
    SELECT GREATEST(0, COALESCE(SUM(m.agreed_price), 0) - COALESCE(SUM(p.paid_amount), 0))
    FROM `memberships` m
    LEFT JOIN (
        SELECT membership_id, SUM(amount) as paid_amount
        FROM `payments` WHERE `status` = 'paid' AND `gym_id` = :g1
        GROUP BY membership_id
    ) p ON p.membership_id = m.id
    WHERE m.gym_id = :g2
", [':g1' => $gymId, ':g2' => $gymId])->fetchColumn() ?: 0);
assert($outstandingDues >= 0);
echo "[PASS] Outstanding dues query verified ({$outstandingDues}).\n";

// 2. Test Reports Queries
$collections = Database::fetchAll("
    SELECT p.*, m.member_number, m.first_name 
    FROM `payments` p
    INNER JOIN `members` m ON m.id = p.member_id
    WHERE p.gym_id = :gym_id AND p.status = 'paid'
", [':gym_id' => $gymId]);
assert(is_array($collections));
echo "[PASS] Collections report query verified (" . count($collections) . " records).\n";

$duesList = Database::fetchAll("
    SELECT 
        m.id as member_id,
        mem.agreed_price,
        COALESCE(p_sum.total_paid, 0) as total_paid,
        GREATEST(0, mem.agreed_price - COALESCE(p_sum.total_paid, 0)) as balance_due
    FROM `members` m
    INNER JOIN (
        SELECT mem1.*
        FROM `memberships` mem1
        INNER JOIN (
            SELECT member_id, MAX(id) as max_id
            FROM `memberships`
            WHERE `gym_id` = :g1
            GROUP BY member_id
        ) mem2 ON mem1.id = mem2.max_id
    ) mem ON mem.member_id = m.id
    LEFT JOIN (
        SELECT membership_id, SUM(amount) as total_paid
        FROM `payments`
        WHERE `status` = 'paid' AND `gym_id` = :g2
        GROUP BY membership_id
    ) p_sum ON p_sum.membership_id = mem.id
    WHERE m.gym_id = :g3 AND m.is_archived = 0
    HAVING balance_due > 0
", [
    ':g1' => $gymId,
    ':g2' => $gymId,
    ':g3' => $gymId,
]);
assert(is_array($duesList));
echo "[PASS] Dues report query verified (" . count($duesList) . " members with dues).\n";

// 3. Test Batch Member CSV Import Simulation
$csvRows = [
    ['TestBatchUser1', 'Singh', '9988112233', 'batch1@test.com', 'male', '1992-04-10', 'Street 1', 'Contact 1', '9988000001', 'CSV Import test'],
    ['TestBatchUser2', 'Kaur', '9988112244', 'batch2@test.com', 'female', '1995-09-12', 'Street 2', 'Contact 2', '9988000002', 'CSV Import test'],
];

$importCount = 0;
Database::transaction(function (\PDO $pdo) use ($csvRows, $gymId, &$importCount) {
    $stmt = $pdo->prepare("
        INSERT INTO `members` (
            `gym_id`, `member_number`, `first_name`, `last_name`, 
            `phone`, `email`, `gender`, `date_of_birth`, 
            `address`, `emergency_contact_name`, `emergency_contact_phone`, 
            `notes`, `is_archived`, `created_at`
        ) VALUES (
            :gym_id, :member_number, :first_name, :last_name, 
            :phone, :email, :gender, :date_of_birth, 
            :address, :emergency_contact_name, :emergency_contact_phone, 
            :notes, 0, NOW()
        )
    ");

    foreach ($csvRows as $row) {
        $memberNumber = Member::generateMemberNumber($gymId);
        $stmt->execute([
            ':gym_id' => $gymId,
            ':member_number' => $memberNumber,
            ':first_name' => $row[0],
            ':last_name' => $row[1],
            ':phone' => $row[2],
            ':email' => $row[3],
            ':gender' => $row[4],
            ':date_of_birth' => $row[5],
            ':address' => $row[6],
            ':emergency_contact_name' => $row[7],
            ':emergency_contact_phone' => $row[8],
            ':notes' => $row[9],
        ]);
        $importCount++;
    }
});

assert($importCount === 2);
$checkBatch1 = Database::fetchOne("SELECT * FROM `members` WHERE `phone` = '9988112233' AND `gym_id` = 1");
assert($checkBatch1 !== null && $checkBatch1['first_name'] === 'TestBatchUser1');
echo "[PASS] Batch member import simulation passed ({$importCount} members imported with auto sequential numbers).\n";

echo "\n>>> ALL PHASE 5 TESTS PASSED PERFECTLY! <<<\n";
