<?php
/**
 * FitPulse Comprehensive Deep Error & Consistency Audit
 * Validates syntax, class definitions, route targets, and view templates.
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/features.php';

$phpBinary = 'D:\Bala\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe';
$rootDir = dirname(__DIR__);

echo "=======================================================\n";
echo "   FITPULSE COMPREHENSIVE DEEP AUDIT SUITE             \n";
echo "=======================================================\n\n";

// --- 1. FULL WORKSPACE SYNTAX LINT ---
echo "[STEP 1] Scanning and Linting All PHP Files...\n";
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$filesChecked = 0;
$syntaxErrors = [];

foreach ($iterator as $path => $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filesChecked++;
        $escapedPath = escapeshellarg($path);
        $output = [];
        $returnCode = 0;
        exec("\"{$phpBinary}\" -l {$escapedPath}", $output, $returnCode);

        if ($returnCode !== 0) {
            $syntaxErrors[] = [
                'file' => str_replace($rootDir, '', $path),
                'output' => implode("\n", $output)
            ];
            echo "  [FAIL] " . str_replace($rootDir, '', $path) . "\n";
        }
    }
}

if (empty($syntaxErrors)) {
    echo "  [PASS] All {$filesChecked} PHP files passed syntax checks with 0 errors.\n\n";
} else {
    echo "  [FAIL] Syntax errors detected in " . count($syntaxErrors) . " files!\n";
    foreach ($syntaxErrors as $e) {
        echo "    - {$e['file']}: {$e['output']}\n";
    }
    echo "\n";
}

// --- 2. CORE & SERVICE CLASS EXISTENCE & INSTANTIATION ---
echo "[STEP 2] Testing Core Classes & Module Services...\n";
$classesToTest = [
    'Database' => __DIR__ . '/../core/Database.php',
    'CSRF' => __DIR__ . '/../core/CSRF.php',
    'Session' => __DIR__ . '/../core/Session.php',
    'RBAC' => __DIR__ . '/../core/RBAC.php',
    'FeatureManager' => __DIR__ . '/../core/FeatureManager.php',
    'Router' => __DIR__ . '/../core/Router.php',
    'View' => __DIR__ . '/../core/View.php',
    'AuditLogger' => __DIR__ . '/../core/AuditLogger.php',
    'Validator' => __DIR__ . '/../core/Validator.php',
    'Auth' => __DIR__ . '/../core/Auth.php',
    'BranchService' => __DIR__ . '/../modules/branches/BranchService.php',
    'StaffService' => __DIR__ . '/../modules/staff/StaffService.php',
    'PlanService' => __DIR__ . '/../modules/membership/PlanService.php',
    'MemberService' => __DIR__ . '/../modules/members/MemberService.php',
    'AttendanceService' => __DIR__ . '/../modules/attendance/AttendanceService.php',
    'PaymentService' => __DIR__ . '/../modules/payments/PaymentService.php',
    'ProgressService' => __DIR__ . '/../modules/progress/ProgressService.php',
    'ReportService' => __DIR__ . '/../modules/reports/ReportService.php',
    'AutomationService' => __DIR__ . '/../modules/automation/AutomationService.php',
    'NotificationService' => __DIR__ . '/../modules/notifications/NotificationService.php',
];

$classErrors = [];
foreach ($classesToTest as $className => $filePath) {
    if (!file_exists($filePath)) {
        $classErrors[] = "Missing file for {$className}: {$filePath}";
        echo "  [FAIL] File missing: {$filePath}\n";
        continue;
    }
    require_once $filePath;
    if (!class_exists($className)) {
        $classErrors[] = "Class {$className} not defined in {$filePath}";
        echo "  [FAIL] Class missing: {$className}\n";
    } else {
        echo "  [PASS] Class {$className} loaded cleanly.\n";
    }
}
echo "\n";

// --- 3. CONTROLLERS & ROUTE TARGET VERIFICATION ---
echo "[STEP 3] Verifying Controllers & Action Handlers...\n";
$controllersToTest = [
    'AuthController' => __DIR__ . '/../controllers/AuthController.php',
    'AdminController' => __DIR__ . '/../controllers/AdminController.php',
    'BranchController' => __DIR__ . '/../controllers/BranchController.php',
    'StaffController' => __DIR__ . '/../controllers/StaffController.php',
    'PlanController' => __DIR__ . '/../controllers/PlanController.php',
    'MemberAdminController' => __DIR__ . '/../controllers/MemberAdminController.php',
    'AttendanceController' => __DIR__ . '/../controllers/AttendanceController.php',
    'PaymentController' => __DIR__ . '/../controllers/PaymentController.php',
    'ProgressController' => __DIR__ . '/../controllers/ProgressController.php',
    'ReportController' => __DIR__ . '/../controllers/ReportController.php',
    'AutomationController' => __DIR__ . '/../controllers/AutomationController.php',
    'AuditController' => __DIR__ . '/../controllers/AuditController.php',
    'MemberController' => __DIR__ . '/../controllers/MemberController.php',
];

$controllerErrors = [];
foreach ($controllersToTest as $ctrlName => $ctrlPath) {
    if (!file_exists($ctrlPath)) {
        $controllerErrors[] = "Missing controller: {$ctrlPath}";
        echo "  [FAIL] Missing controller file: {$ctrlPath}\n";
        continue;
    }
    require_once $ctrlPath;
    if (!class_exists($ctrlName)) {
        $controllerErrors[] = "Controller class {$ctrlName} not defined in {$ctrlPath}";
        echo "  [FAIL] Controller class missing: {$ctrlName}\n";
    } else {
        echo "  [PASS] Controller {$ctrlName} verified.\n";
    }
}
echo "\n";

// --- 4. VIEW TEMPLATE EXISTENCE CHECK ---
echo "[STEP 4] Checking Required View Templates...\n";
$viewsToCheck = [
    'layouts/app',
    'layouts/auth',
    'layouts/member_app',
    'partials/flash',
    'auth/login',
    'admin/dashboard',
    'admin/coder_control',
    'admin/attendance/index',
    'admin/attendance/quick_desk',
    'admin/attendance/qr_poster',
    'admin/members/index',
    'admin/members/form',
    'admin/members/view',
    'admin/plans/index',
    'admin/payments/index',
    'admin/payments/receipt',
    'admin/payments/thermal',
    'admin/payments/dues',
    'admin/progress/index',
    'admin/progress/compare',
    'admin/reports/index',
    'admin/staff/index',
    'admin/branches/index',
    'admin/automation/index',
    'admin/audit_logs/index',
    'member/dashboard',
    'member/attendance',
    'member/progress',
    'member/payments',
    'member/qr',
    'member/scan_gym_qr',
    'member/profile',
    'errors/403',
    'errors/404',
    'errors/500',
];

$viewErrors = [];
foreach ($viewsToCheck as $view) {
    $viewPath = __DIR__ . '/../views/' . $view . '.php';
    if (!file_exists($viewPath)) {
        $viewErrors[] = "Missing view: views/{$view}.php";
        echo "  [FAIL] Missing view: views/{$view}.php\n";
    } else {
        echo "  [PASS] View exists: views/{$view}.php\n";
    }
}
echo "\n";

// --- FINAL SUMMARY ---
echo "=======================================================\n";
echo "                   FINAL AUDIT REPORT                  \n";
echo "=======================================================\n";
echo "Total PHP Files Checked : {$filesChecked}\n";
echo "Syntax Errors           : " . count($syntaxErrors) . "\n";
echo "Core / Service Errors   : " . count($classErrors) . "\n";
echo "Controller Errors       : " . count($controllerErrors) . "\n";
echo "View Template Errors    : " . count($viewErrors) . "\n";

$totalErrors = count($syntaxErrors) + count($classErrors) + count($controllerErrors) + count($viewErrors);
echo "Total Issues Detected   : {$totalErrors}\n";
echo "=======================================================\n\n";

if ($totalErrors === 0) {
    echo "🎉 ALL CHECKS PASSED PERFECTLY WITH ZERO ERRORS!\n\n";
    exit(0);
} else {
    echo "❌ AUDIT COMPLETED WITH ISSUES.\n\n";
    exit(1);
}
