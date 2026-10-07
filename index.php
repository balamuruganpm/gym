<?php
/**
 * FitPulse Gym Management & Automation Platform
 * Master Entry Point / Front Controller
 */

// 1. Load Configurations
require_once __DIR__ . '/config/app.php';

// 2. Load Core Framework Engines
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Session.php';
require_once __DIR__ . '/core/CSRF.php';
require_once __DIR__ . '/core/RBAC.php';
require_once __DIR__ . '/core/FeatureManager.php';
require_once __DIR__ . '/core/AuditLogger.php';
require_once __DIR__ . '/core/Validator.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/View.php';
require_once __DIR__ . '/core/Router.php';

// 3. Load Domain Module Services
require_once __DIR__ . '/modules/branches/BranchService.php';
require_once __DIR__ . '/modules/staff/StaffService.php';
require_once __DIR__ . '/modules/membership/PlanService.php';
require_once __DIR__ . '/modules/members/MemberService.php';
require_once __DIR__ . '/modules/attendance/AttendanceService.php';

// 4. Initialize Secure Session
Session::start();

// 5. Register Application Routes

// Home redirect
Router::get('/', function () {
    if (Auth::check()) {
        $role = Auth::role();
        if ($role === 'member') {
            header('Location: ' . BASE_URL . '/member/dashboard');
        } elseif ($role === 'superadmin') {
            header('Location: ' . BASE_URL . '/admin/coder-control');
        } else {
            header('Location: ' . BASE_URL . '/admin/dashboard');
        }
    } else {
        header('Location: ' . BASE_URL . '/login');
    }
    exit;
});

// Authentication Routes
Router::get('/login', 'AuthController@showLogin');
Router::post('/login', 'AuthController@processLogin');
Router::get('/logout', 'AuthController@logout');

// Universal Gym QR Scan Public Entrypoint
Router::get('/checkin/gym-qr', 'AttendanceController@scanGymQr');
Router::post('/checkin/gym-qr', 'AttendanceController@scanGymQr');

// Admin & Staff Dashboard Routes
Router::get('/admin/dashboard', 'AdminController@dashboard', ['auth']);

// Coder Control Center Routes (Superadmin)
Router::get('/admin/coder-control', 'AdminController@coderControl', ['auth', 'superadmin']);
Router::post('/admin/coder-control/toggle', 'AdminController@toggleFeature', ['auth', 'superadmin', 'csrf']);

// Multi-Branch Management Routes
Router::get('/admin/branches', 'BranchController@index', ['auth']);
Router::get('/admin/branches/create', 'BranchController@create', ['auth']);
Router::post('/admin/branches', 'BranchController@store', ['auth', 'csrf']);
Router::get('/admin/branches/:id/edit', 'BranchController@edit', ['auth']);
Router::post('/admin/branches/:id/update', 'BranchController@update', ['auth', 'csrf']);
Router::post('/admin/branches/:id/toggle', 'BranchController@toggleStatus', ['auth', 'csrf']);

// Staff & Trainer Management Routes
Router::get('/admin/staff', 'StaffController@index', ['auth']);
Router::get('/admin/staff/create', 'StaffController@create', ['auth']);
Router::post('/admin/staff', 'StaffController@store', ['auth', 'csrf']);
Router::get('/admin/staff/:id/edit', 'StaffController@edit', ['auth']);
Router::post('/admin/staff/:id/update', 'StaffController@update', ['auth', 'csrf']);
Router::post('/admin/staff/:id/toggle', 'StaffController@toggleStatus', ['auth', 'csrf']);

// Membership Plans Management Routes
Router::get('/admin/plans', 'PlanController@index', ['auth']);
Router::get('/admin/plans/create', 'PlanController@create', ['auth']);
Router::post('/admin/plans', 'PlanController@store', ['auth', 'csrf']);
Router::get('/admin/plans/:id/edit', 'PlanController@edit', ['auth']);
Router::post('/admin/plans/:id/update', 'PlanController@update', ['auth', 'csrf']);
Router::post('/admin/plans/:id/toggle', 'PlanController@toggleStatus', ['auth', 'csrf']);

// Member Management Routes
Router::get('/admin/members', 'MemberAdminController@index', ['auth']);
Router::get('/admin/members/create', 'MemberAdminController@create', ['auth']);
Router::post('/admin/members', 'MemberAdminController@store', ['auth', 'csrf']);
Router::get('/admin/members/:id', 'MemberAdminController@show', ['auth']);
Router::post('/admin/members/:id/freeze', 'MemberAdminController@freeze', ['auth', 'csrf']);
Router::post('/admin/members/:id/renew', 'MemberAdminController@renew', ['auth', 'csrf']);

// Attendance & Check-In Routes (DreamsGym Style)
Router::get('/admin/attendance', 'AttendanceController@index', ['auth']);
Router::get('/admin/checkin', 'AttendanceController@quickDesk', ['auth']);
Router::get('/admin/attendance/quick', 'AttendanceController@quickDesk', ['auth']);
Router::get('/admin/attendance/qr-poster', 'AttendanceController@qrPoster', ['auth']);
Router::post('/admin/attendance/checkin', 'AttendanceController@processCheckin', ['auth', 'csrf']);
Router::get('/api/attendance/search-members', 'AttendanceController@apiSearchMembers', ['auth']);
Router::post('/api/attendance/checkin', 'AttendanceController@apiCheckin');

// Payment & Financial Ledger Routes
Router::get('/admin/payments', 'PaymentController@index', ['auth']);
Router::get('/admin/payments/dues', 'PaymentController@dues', ['auth']);
Router::post('/admin/payments/collect', 'PaymentController@collect', ['auth', 'csrf']);
Router::get('/admin/payments/receipt', 'PaymentController@receipt', ['auth']);
Router::get('/admin/payments/thermal', 'PaymentController@thermal', ['auth']);

// Member Progress & Transformation Routes
Router::get('/admin/progress', 'ProgressController@index', ['auth']);
Router::get('/admin/progress/create', 'ProgressController@create', ['auth']);
Router::post('/admin/progress/store', 'ProgressController@store', ['auth', 'csrf']);
Router::get('/admin/progress/compare', 'ProgressController@compare', ['auth']);

// Reports & Analytics Hub Routes
Router::get('/admin/reports', 'ReportController@index', ['auth']);
Router::get('/admin/system-health', 'ReportController@systemHealth', ['auth']);

// Automation Engine Routes
Router::get('/admin/automation', 'AutomationController@index', ['auth']);
Router::post('/admin/automation/toggle', 'AutomationController@toggle', ['auth', 'csrf']);
Router::post('/admin/automation/run-cron', 'AutomationController@runCron', ['auth', 'csrf']);

// System Audit Logs
Router::get('/admin/audit-logs', 'AuditController@index', ['auth']);

// Member Portal Routes
Router::get('/member/dashboard', 'MemberController@dashboard', ['auth']);
Router::get('/member/qr', 'MemberController@qr', ['auth']);
Router::get('/member/scan-gym-qr', 'MemberController@scanGymQr', ['auth']);
Router::get('/member/attendance', 'MemberController@attendance', ['auth']);
Router::post('/member/checkin', 'MemberController@checkin', ['auth', 'csrf']);
Router::get('/member/progress', 'MemberController@progress', ['auth']);
Router::post('/member/progress/store', 'MemberController@storeProgress', ['auth', 'csrf']);
Router::get('/member/payments', 'MemberController@payments', ['auth']);
Router::get('/member/profile', 'MemberController@profile', ['auth']);
Router::post('/member/profile/update', 'MemberController@updateProfile', ['auth', 'csrf']);

// 6. Dispatch Request
Router::dispatch();
