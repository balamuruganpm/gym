<?php

declare(strict_types=1);

/**
 * Phase C — Trainer Management, Scheduling & Trainee Isolation Test Suite
 * 
 * Verifies:
 * 1. Database schema additions for Phase C (assignments, availabilities, sessions, progress).
 * 2. Trainer assignment creation, duplicate prevention, cross-gym isolation, termination, auditing.
 * 3. Strict trainer-to-member data access isolation.
 * 4. Trainer recurring and exception availability management.
 * 5. Session scheduling, date/time validation, and atomic conflict detection.
 * 6. Session status progression (scheduled -> completed / no_show / cancelled).
 * 7. Workout progress recording, measurement boundaries, and trainee ownership checks.
 * 8. Controller and View presence and sanity.
 */

$baseDir = dirname(__DIR__);
require_once $baseDir . '/vendor/autoload.php';

use App\Services\Config;
use App\Services\Database;
use App\Services\Session;
use App\Services\TrainerService;
use App\Controllers\TrainerController;
use App\Controllers\TrainerAssignmentController;

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
echo " GymFlow — Phase C Verification Test Suite\n";
echo "=======================================================\n\n";

$pdo = Database::getConnection();

// -----------------------------------------------------------------------------
// Test Group 1: Database Schema Additions
// -----------------------------------------------------------------------------
echo "1. Testing Database Schema Additions...\n";

$phaseCTables = [
    'trainer_member_assignments',
    'trainer_availabilities',
    'training_sessions',
    'training_progress_records'
];

foreach ($phaseCTables as $tbl) {
    $exists = (bool) Database::fetchOne("
        SELECT COUNT(*) as c FROM information_schema.tables 
        WHERE table_schema = DATABASE() AND table_name = :t
    ", [':t' => $tbl])['c'];
    assertCondition("Table `{$tbl}` exists in InnoDB", $exists);
}

// -----------------------------------------------------------------------------
// Setup Fixtures for Testing (Gym 1, Test Trainer, Test Member)
// -----------------------------------------------------------------------------
$gymId = 1;

// Ensure a test trainer exists in Gym 1
$testTrainerEmail = 'trainer.test@gymflow.local';
$testTrainerUser = Database::fetchOne("SELECT * FROM `users` WHERE `email` = :e", [':e' => $testTrainerEmail]);
if (!$testTrainerUser) {
    $pdo->prepare("
        INSERT INTO `users` (`name`, `email`, `password_hash`, `status`, `created_at`)
        VALUES ('Coach Test', :e, :p, 'active', NOW())
    ")->execute([
        ':e' => $testTrainerEmail,
        ':p' => password_hash('TrainerPass123!', PASSWORD_DEFAULT),
    ]);
    $trainerUserId = (int) $pdo->lastInsertId();
} else {
    $trainerUserId = (int) $testTrainerUser['id'];
}

// Ensure gym_users link with trainer role
$gu = Database::fetchOne("
    SELECT id FROM `gym_users` WHERE `gym_id` = :g AND `user_id` = :u
", [':g' => $gymId, ':u' => $trainerUserId]);
if (!$gu) {
    $pdo->prepare("
        INSERT INTO `gym_users` (`gym_id`, `user_id`, `role`, `is_active`, `created_at`)
        VALUES (:g, :u, 'trainer', 1, NOW())
    ")->execute([':g' => $gymId, ':u' => $trainerUserId]);
} else {
    $pdo->prepare("UPDATE `gym_users` SET `role` = 'trainer', `is_active` = 1 WHERE `id` = :id")
        ->execute([':id' => $gu['id']]);
}

// Create two test members in Gym 1
$memberA = Database::fetchOne("SELECT * FROM `members` WHERE `gym_id` = :g AND `member_number` = 'TR-MEMBER-A'", [':g' => $gymId]);
if (!$memberA) {
    $pdo->prepare("
        INSERT INTO `members` (`gym_id`, `member_number`, `first_name`, `last_name`, `phone`, `is_archived`, `created_at`)
        VALUES (:g, 'TR-MEMBER-A', 'Alice', 'Athlete', '9876500001', 0, NOW())
    ")->execute([':g' => $gymId]);
    $memberAId = (int) $pdo->lastInsertId();
} else {
    $memberAId = (int) $memberA['id'];
}

$memberB = Database::fetchOne("SELECT * FROM `members` WHERE `gym_id` = :g AND `member_number` = 'TR-MEMBER-B'", [':g' => $gymId]);
if (!$memberB) {
    $pdo->prepare("
        INSERT INTO `members` (`gym_id`, `member_number`, `first_name`, `last_name`, `phone`, `is_archived`, `created_at`)
        VALUES (:g, 'TR-MEMBER-B', 'Bob', 'Bodybuilder', '9876500002', 0, NOW())
    ")->execute([':g' => $gymId]);
    $memberBId = (int) $pdo->lastInsertId();
} else {
    $memberBId = (int) $memberB['id'];
}

// Clean up previous test assignments for these test members
$pdo->prepare("DELETE FROM `training_progress_records` WHERE `gym_id` = :g AND `trainer_user_id` = :t")
    ->execute([':g' => $gymId, ':t' => $trainerUserId]);
$pdo->prepare("DELETE FROM `training_sessions` WHERE `gym_id` = :g AND `trainer_user_id` = :t")
    ->execute([':g' => $gymId, ':t' => $trainerUserId]);
$pdo->prepare("DELETE FROM `trainer_availabilities` WHERE `gym_id` = :g AND `trainer_user_id` = :t")
    ->execute([':g' => $gymId, ':t' => $trainerUserId]);
$pdo->prepare("DELETE FROM `trainer_member_assignments` WHERE `gym_id` = :g AND `trainer_user_id` = :t")
    ->execute([':g' => $gymId, ':t' => $trainerUserId]);

// -----------------------------------------------------------------------------
// Test Group 2: Trainer Assignment Workflow
// -----------------------------------------------------------------------------
echo "\n2. Testing Trainer Assignment Workflow...\n";

// 2.1 Assign Member A to Trainer
$ownerId = 1;
$assignmentId = TrainerService::assignMember($gymId, $trainerUserId, $memberAId, $ownerId, 'Initial coaching program');
assertCondition('TrainerService::assignMember creates assignment ID', $assignmentId > 0);

$savedAssignment = Database::fetchOne("SELECT * FROM `trainer_member_assignments` WHERE `id` = :id", [':id' => $assignmentId]);
assertCondition('Assignment status is active', $savedAssignment['status'] === 'active');
assertCondition('Assignment notes match', $savedAssignment['notes'] === 'Initial coaching program');

// 2.2 Audit log verification
$audit = Database::fetchOne("
    SELECT * FROM `audit_logs` 
    WHERE `gym_id` = :g AND `action` = 'trainer_member_assigned' AND `entity_id` = :id 
    ORDER BY id DESC LIMIT 1
", [':g' => $gymId, ':id' => $assignmentId]);
assertCondition('Trainer assignment written to audit trail', !empty($audit));

// 2.3 Prevent duplicate active assignment
$duplicateFailed = false;
try {
    TrainerService::assignMember($gymId, $trainerUserId, $memberAId, $ownerId, 'Duplicate assignment');
} catch (\InvalidArgumentException $e) {
    $duplicateFailed = true;
}
assertCondition('Duplicate active assignment rejected with InvalidArgumentException', $duplicateFailed);

// 2.4 Reject assignment of non-existent member
$invalidMemberFailed = false;
try {
    TrainerService::assignMember($gymId, $trainerUserId, 999999, $ownerId);
} catch (\InvalidArgumentException $e) {
    $invalidMemberFailed = true;
}
assertCondition('Assigning non-existent member rejected', $invalidMemberFailed);

// 2.5 Cross-gym isolation: cannot assign member belonging to another gym
$crossGymFailed = false;
try {
    // Member in Gym 1 assigned under Gym 2 context
    TrainerService::assignMember(2, $trainerUserId, $memberAId, $ownerId);
} catch (\InvalidArgumentException $e) {
    $crossGymFailed = true;
}
assertCondition('Cross-gym member assignment rejected', $crossGymFailed);

// -----------------------------------------------------------------------------
// Test Group 3: Strict Trainer-Member Isolation
// -----------------------------------------------------------------------------
echo "\n3. Testing Trainer-Member Isolation...\n";

assertCondition('Member A is assigned to Coach Test', TrainerService::isMemberAssigned($gymId, $trainerUserId, $memberAId) === true);
assertCondition('Member B is NOT assigned to Coach Test', TrainerService::isMemberAssigned($gymId, $trainerUserId, $memberBId) === false);

$assignedMembers = TrainerService::getAssignedMembers($gymId, $trainerUserId);
assertCondition('getAssignedMembers returns exactly 1 member', count($assignedMembers) === 1);
assertCondition('getAssignedMembers returns Member A', (int)$assignedMembers[0]['id'] === $memberAId);

// Progress history isolation: Trainer querying Member B progress history returns empty
$memberBHistory = TrainerService::getMemberProgressHistory($gymId, $memberBId, $trainerUserId);
assertCondition('Unassigned Member B progress history is isolated (empty array)', empty($memberBHistory));

// -----------------------------------------------------------------------------
// Test Group 4: Trainer Availability Setup
// -----------------------------------------------------------------------------
echo "\n4. Testing Trainer Availability...\n";

// 4.1 Recurring Weekly Slot (e.g. Tuesday 08:00 - 17:00)
$recurringId = TrainerService::setAvailability($gymId, $trainerUserId, [
    'type' => 'recurring',
    'day_of_week' => 2, // Tuesday
    'start_time' => '08:00',
    'end_time' => '17:00',
    'notes' => 'Tuesday standard shift',
]);
assertCondition('setAvailability creates recurring slot', $recurringId > 0);

// 4.2 Specific date exception (e.g. Next Monday custom hours)
$exceptionDate = date('Y-m-d', strtotime('+7 days'));
$exceptionId = TrainerService::setAvailability($gymId, $trainerUserId, [
    'type' => 'exception',
    'specific_date' => $exceptionDate,
    'start_time' => '10:00',
    'end_time' => '14:00',
    'notes' => 'Half-day coaching',
]);
assertCondition('setAvailability creates date-specific exception', $exceptionId > 0);

// 4.3 Time ordering validation (start >= end rejected)
$invalidTimeFailed = false;
try {
    TrainerService::setAvailability($gymId, $trainerUserId, [
        'type' => 'recurring',
        'day_of_week' => 3,
        'start_time' => '18:00',
        'end_time' => '09:00',
    ]);
} catch (\InvalidArgumentException $e) {
    $invalidTimeFailed = true;
}
assertCondition('Availability with start_time >= end_time rejected', $invalidTimeFailed);

// 4.4 Retrieve all availability slots
$allAvail = TrainerService::getTrainerAvailability($gymId, $trainerUserId);
assertCondition('getTrainerAvailability retrieves 2 slots', count($allAvail) === 2);

// 4.5 Delete availability slot
$deleted = TrainerService::deleteAvailability($gymId, $trainerUserId, $exceptionId);
assertCondition('deleteAvailability removes slot', $deleted === true);
$afterDelete = TrainerService::getTrainerAvailability($gymId, $trainerUserId);
assertCondition('Slot count decreased to 1 after deletion', count($afterDelete) === 1);

// -----------------------------------------------------------------------------
// Test Group 5: Session Scheduling & Conflict Detection
// -----------------------------------------------------------------------------
echo "\n5. Testing Session Scheduling & Conflict Prevention...\n";

$sessionDate = date('Y-m-d', strtotime('+1 day'));

// 5.1 Schedule 1st session: 10:00 to 11:00
$session1Id = TrainerService::scheduleSession(
    $gymId,
    $trainerUserId,
    $memberAId,
    $sessionDate,
    '10:00:00',
    '11:00:00',
    $trainerUserId,
    'Hypertrophy focus'
);
assertCondition('Session 1 scheduled successfully', $session1Id > 0);

$sess1 = Database::fetchOne("SELECT * FROM `training_sessions` WHERE `id` = :id", [':id' => $session1Id]);
assertCondition('Session 1 status is scheduled', $sess1['status'] === 'scheduled');

// 5.2 Conflict detection: Attempt exact overlapping session (10:00 to 11:00)
$exactConflictFailed = false;
try {
    TrainerService::scheduleSession(
        $gymId,
        $trainerUserId,
        $memberAId,
        $sessionDate,
        '10:00:00',
        '11:00:00',
        $trainerUserId
    );
} catch (\InvalidArgumentException $e) {
    $exactConflictFailed = true;
}
assertCondition('Exact overlapping session rejected due to conflict', $exactConflictFailed);

// 5.3 Conflict detection: Attempt partial overlapping session (10:30 to 11:30)
$partialConflictFailed = false;
try {
    TrainerService::scheduleSession(
        $gymId,
        $trainerUserId,
        $memberAId,
        $sessionDate,
        '10:30:00',
        '11:30:00',
        $trainerUserId
    );
} catch (\InvalidArgumentException $e) {
    $partialConflictFailed = true;
}
assertCondition('Partial overlapping session (10:30-11:30) rejected due to conflict', $partialConflictFailed);

// 5.4 Conflict detection: Attempt encompassing session (09:30 to 11:30)
$encompassingConflictFailed = false;
try {
    TrainerService::scheduleSession(
        $gymId,
        $trainerUserId,
        $memberAId,
        $sessionDate,
        '09:30:00',
        '11:30:00',
        $trainerUserId
    );
} catch (\InvalidArgumentException $e) {
    $encompassingConflictFailed = true;
}
assertCondition('Encompassing session (09:30-11:30) rejected due to conflict', $encompassingConflictFailed);

// 5.5 Non-overlapping adjacent session (11:00 to 12:00) succeeds
$adjacentSessionId = TrainerService::scheduleSession(
    $gymId,
    $trainerUserId,
    $memberAId,
    $sessionDate,
    '11:00:00',
    '12:00:00',
    $trainerUserId,
    'Adjacent block'
);
assertCondition('Adjacent non-overlapping session (11:00-12:00) scheduled successfully', $adjacentSessionId > 0);

// 5.6 Scheduling with unassigned member rejected
$unassignedScheduleFailed = false;
try {
    TrainerService::scheduleSession(
        $gymId,
        $trainerUserId,
        $memberBId, // Member B is not assigned to Coach Test
        $sessionDate,
        '14:00:00',
        '15:00:00',
        $trainerUserId
    );
} catch (\InvalidArgumentException $e) {
    $unassignedScheduleFailed = true;
}
assertCondition('Scheduling session for unassigned member rejected', $unassignedScheduleFailed);

// -----------------------------------------------------------------------------
// Test Group 6: Session Status Lifecycle
// -----------------------------------------------------------------------------
echo "\n6. Testing Session Status Lifecycle...\n";

// Mark session 1 as completed
$statusUpdated = TrainerService::updateSessionStatus($gymId, $session1Id, $trainerUserId, 'completed', 'Completed with excellent form');
assertCondition('updateSessionStatus sets completed', $statusUpdated === true);

$sess1After = Database::fetchOne("SELECT * FROM `training_sessions` WHERE `id` = :id", [':id' => $session1Id]);
assertCondition('Session status in DB is completed', $sess1After['status'] === 'completed');
assertCondition('Session notes updated with completion remarks', str_contains($sess1After['session_notes'], 'excellent form'));

// Mark adjacent session as no_show
TrainerService::updateSessionStatus($gymId, $adjacentSessionId, $trainerUserId, 'no_show', 'Trainee did not attend');
$sess2After = Database::fetchOne("SELECT * FROM `training_sessions` WHERE `id` = :id", [':id' => $adjacentSessionId]);
assertCondition('Session status in DB is no_show', $sess2After['status'] === 'no_show');

// -----------------------------------------------------------------------------
// Test Group 7: Workout Progress Recording & Validation
// -----------------------------------------------------------------------------
echo "\n7. Testing Workout Progress & Measurements...\n";

// 7.1 Record valid progress
$progressId = TrainerService::recordProgress($gymId, $trainerUserId, $memberAId, [
    'record_date' => date('Y-m-d'),
    'workout_summary' => "Bench Press 4x10 (70kg)\nIncline Dumbbell Press 3x12 (22kg)\nDips 3x15",
    'trainer_feedback' => 'Consistent strength gain, keep elbow angle tight.',
    'weight_kg' => 75.4,
    'body_fat_percentage' => 16.8,
    'session_id' => $session1Id,
]);
assertCondition('TrainerService::recordProgress creates record ID', $progressId > 0);

$savedProgress = Database::fetchOne("SELECT * FROM `training_progress_records` WHERE `id` = :id", [':id' => $progressId]);
assertCondition('Recorded weight is 75.40', (float)$savedProgress['weight_kg'] === 75.40);
assertCondition('Recorded body fat is 16.8', (float)$savedProgress['body_fat_percentage'] === 16.8);
assertCondition('Progress record linked to completed session', (int)$savedProgress['session_id'] === $session1Id);

// 7.2 Range validation: weight < 20 or > 350 rejected
$invalidWeightFailed = false;
try {
    TrainerService::recordProgress($gymId, $trainerUserId, $memberAId, [
        'workout_summary' => 'Invalid weight test',
        'weight_kg' => 999.0,
    ]);
} catch (\InvalidArgumentException $e) {
    $invalidWeightFailed = true;
}
assertCondition('Weight > 350kg rejected with InvalidArgumentException', $invalidWeightFailed);

// 7.3 Range validation: body fat < 2% or > 70% rejected
$invalidBfFailed = false;
try {
    TrainerService::recordProgress($gymId, $trainerUserId, $memberAId, [
        'workout_summary' => 'Invalid body fat test',
        'body_fat_percentage' => 95.0,
    ]);
} catch (\InvalidArgumentException $e) {
    $invalidBfFailed = true;
}
assertCondition('Body fat > 70% rejected with InvalidArgumentException', $invalidBfFailed);

// 7.4 Record progress for unassigned member rejected
$unassignedProgressFailed = false;
try {
    TrainerService::recordProgress($gymId, $trainerUserId, $memberBId, [
        'workout_summary' => 'Trainee isolation test',
    ]);
} catch (\InvalidArgumentException $e) {
    $unassignedProgressFailed = true;
}
assertCondition('Recording progress for unassigned member rejected', $unassignedProgressFailed);

// 7.5 Query progress history for assigned Member A
$historyA = TrainerService::getMemberProgressHistory($gymId, $memberAId, $trainerUserId);
assertCondition('Progress history for Member A returns recorded entry', count($historyA) >= 1);
assertCondition('Progress history entry contains workout summary', str_contains($historyA[0]['workout_summary'], 'Bench Press'));

// -----------------------------------------------------------------------------
// Test Group 8: Assignment Termination Workflow
// -----------------------------------------------------------------------------
echo "\n8. Testing Assignment Termination...\n";

$unassigned = TrainerService::unassignMember($gymId, $assignmentId, $ownerId);
assertCondition('TrainerService::unassignMember returns true', $unassigned === true);

$endedAssignment = Database::fetchOne("SELECT * FROM `trainer_member_assignments` WHERE `id` = :id", [':id' => $assignmentId]);
assertCondition('Assignment status changed to inactive', $endedAssignment['status'] === 'inactive');
assertCondition('Assignment ended_at timestamp recorded', !empty($endedAssignment['ended_at']));
assertCondition('isMemberAssigned returns false after termination', TrainerService::isMemberAssigned($gymId, $trainerUserId, $memberAId) === false);

// -----------------------------------------------------------------------------
// Test Group 9: Controllers & Views Sanity
// -----------------------------------------------------------------------------
echo "\n9. Testing Controllers & Views Sanity...\n";

assertCondition('TrainerService class exists', class_exists(TrainerService::class));
assertCondition('TrainerAssignmentController class exists', class_exists(TrainerAssignmentController::class));
assertCondition('TrainerController class exists', class_exists(TrainerController::class));

$viewsToCheck = [
    'trainers/index.php',
    'trainer/dashboard.php',
    'trainer/members.php',
    'trainer/member_details.php',
    'trainer/availability.php',
    'trainer/sessions.php',
    'trainer/progress.php',
];

foreach ($viewsToCheck as $viewFile) {
    $fullPath = $baseDir . '/app/Views/' . $viewFile;
    assertCondition("View file `{$viewFile}` exists on disk", file_exists($fullPath));
}

// -----------------------------------------------------------------------------
// Summary
// -----------------------------------------------------------------------------
echo "\n=======================================================\n";
echo " Phase C Test Results: {$passed}/{$total} assertions passed\n";
if ($failed > 0) {
    echo " \e[31mFAILURES: {$failed}\e[0m\n";
    exit(1);
} else {
    echo " \e[32mALL PHASE C TESTS PASSED (100%)\e[0m\n";
    echo "=======================================================\n\n";
    exit(0);
}
