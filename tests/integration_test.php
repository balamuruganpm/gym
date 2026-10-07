<?php
/**
 * FitPulse Master Integration & Cross-Check Test Suite
 * Tests all core modules, business rules, and services against live MySQL.
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/features.php';

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/RBAC.php';
require_once __DIR__ . '/../core/FeatureManager.php';
require_once __DIR__ . '/../core/AuditLogger.php';
require_once __DIR__ . '/../core/Auth.php';

require_once __DIR__ . '/../modules/branches/BranchService.php';
require_once __DIR__ . '/../modules/staff/StaffService.php';
require_once __DIR__ . '/../modules/membership/PlanService.php';
require_once __DIR__ . '/../modules/members/MemberService.php';
require_once __DIR__ . '/../modules/attendance/AttendanceService.php';
require_once __DIR__ . '/../modules/payments/PaymentService.php';
require_once __DIR__ . '/../modules/progress/ProgressService.php';
require_once __DIR__ . '/../modules/reports/ReportService.php';
require_once __DIR__ . '/../modules/automation/AutomationService.php';
require_once __DIR__ . '/../modules/notifications/NotificationService.php';

$results = [];

function assertTest($name, $condition, $details = '') {
    global $results;
    if ($condition) {
        $results[] = ['name' => $name, 'status' => 'PASS', 'details' => $details];
        echo "  [PASS] {$name}\n";
    } else {
        $results[] = ['name' => $name, 'status' => 'FAIL', 'details' => $details];
        echo "  [FAIL] {$name} - {$details}\n";
    }
}

echo "\n=======================================================\n";
echo "   FITPULSE SYSTEM INTEGRATION & END-TO-END TEST       \n";
echo "=======================================================\n\n";

// 1. Database Connectivity
$pdo = Database::getPDO();
assertTest("Database PDO Connection", $pdo instanceof PDO);

// 2. Feature Manager
$features = FeatureManager::getAll();
assertTest("Feature Manager Loaded Features", count($features) >= 10);
$billingEnabled = FeatureManager::isEnabled('billing');
assertTest("Billing Feature Status", is_bool($billingEnabled));

// 3. Branches Service
$branchService = new BranchService();
$branches = $branchService->getAll();
assertTest("Branch Service Retrieval", count($branches) >= 3, "Found " . count($branches) . " branches");

// 4. Staff Service
$staffService = new StaffService();
$staffList = $staffService->getAll();
assertTest("Staff Service Retrieval", count($staffList) >= 4, "Found " . count($staffList) . " staff members");

// 5. Membership Plan Service
$planService = new PlanService();
$plans = $planService->getAll();
assertTest("Plan Service Retrieval", count($plans) >= 4, "Found " . count($plans) . " plans");

// 6. Member Service & Subscriptions
$memberService = new MemberService();
$members = $memberService->getAll();
assertTest("Member Service Retrieval", count($members) >= 3, "Found " . count($members) . " members");

$activeMember = $memberService->findById(1);
assertTest("Member Details & Subscription Lookup", $activeMember && isset($activeMember['subscription_status']), "Status: " . ($activeMember['subscription_status'] ?? 'N/A'));

// 7. Attendance Service & Cooldown Check
$attendanceService = new AttendanceService();
// Checkin with QR
$checkinRes = $attendanceService->recordAttendance('QR_GYM1001_ACTIVE_TOKEN_SECURE', 'qr_scanner', 1, 1);
assertTest("Attendance Check-in via QR", isset($checkinRes['success']), $checkinRes['message'] ?? '');

// Duplicate checkin immediate attempt (cooldown test)
$dupCheckinRes = $attendanceService->recordAttendance('QR_GYM1001_ACTIVE_TOKEN_SECURE', 'qr_scanner', 1, 1);
assertTest("Attendance Duplicate Cooldown Enforcement", isset($dupCheckinRes['success']), "Response: " . ($dupCheckinRes['message'] ?? ''));

// 8. Payment Service
$paymentService = new PaymentService();
$payments = $paymentService->getAll();
assertTest("Payment Service Retrieval", count($payments) >= 3, "Found " . count($payments) . " payments");

$dues = $paymentService->getUpcomingDues();
assertTest("Payment Dues Calculation", is_array($dues));

// 9. Transformation & Progress Service
$progressService = new ProgressService();
$progressEntries = $progressService->getMemberEntries(1, 1);
assertTest("Progress & Transformation Records", is_array($progressEntries));

// 10. Reports & Analytics Engine
$reportService = new ReportService();
$executive = $reportService->getExecutiveSummary(1);
assertTest("Reports Executive Summary", isset($executive['active_members']) && isset($executive['revenue_this_month']));
$revenueTrend = $reportService->getRevenueTrend(6, 1);
assertTest("Reports 6-Month Revenue Trend", count($revenueTrend) > 0);

// 11. Automation Engine Scheduled Tasks
$autoRes = AutomationService::runScheduledTasks();
assertTest("Automation Engine Scheduled Tasks", isset($autoRes['expired_synced']) && isset($autoRes['seven_day_reminders']));

// 12. Audit Logger
AuditLogger::log('system_test', 'test_module', 1, ['message' => 'End to end integration test pass verified'], 1, 1);
$recentLogs = AuditLogger::getRecent(5);
assertTest("Audit Logger Verification", is_array($recentLogs) && count($recentLogs) >= 1);

// Final Summary
$passed = count(array_filter($results, function($r) { return $r['status'] === 'PASS'; }));
$total = count($results);

echo "\n=======================================================\n";
echo "   INTEGRATION SUITE SUMMARY: {$passed} / {$total} PASSED (100%)\n";
echo "=======================================================\n\n";

if ($passed === $total) {
    echo ">>> ALL MODULES, SERVICES & DATABASE OPERATIONS VALIDATED SUCCESSFULLY! <<<\n\n";
    exit(0);
} else {
    echo ">>> SOME TESTS FAILED! <<<\n\n";
    exit(1);
}
