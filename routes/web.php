<?php

declare(strict_types=1);

use App\Services\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RoleMiddleware;

use App\Controllers\MemberController;
use App\Controllers\PlanController;
use App\Controllers\RenewalController;
use App\Controllers\PaymentController;
use App\Controllers\PaymentReversalController;
use App\Controllers\ReportController;
use App\Controllers\ImportController;
use App\Controllers\StaffController;
use App\Controllers\AuditLogController;
use App\Controllers\SettingsController;
use App\Controllers\SupremeLeaderController;
use App\Controllers\TrainerController;
use App\Controllers\TrainerAssignmentController;
use App\Controllers\MemberPortalController;
use App\Controllers\PaymentRequestController;

// ==========================================
// 1. Guest Routes
// ==========================================
Router::group(['middleware' => GuestMiddleware::class], function () {
    Router::get('/', function () {
        header('Location: ' . url('login'));
        exit;
    });

    Router::get('/login', [AuthController::class, 'showLogin']);
    Router::post('/login', [AuthController::class, 'login']);

    // Public registration disabled in single-gym operational architecture
    Router::get('/register', [AuthController::class, 'showRegister']);
    Router::post('/register', [AuthController::class, 'register']);
});

// ==========================================
// 2. Shared Authenticated Routes
// ==========================================
Router::group(['middleware' => AuthMiddleware::class], function () {
    Router::post('/logout', [AuthController::class, 'logout']);
});

// ==========================================
// 3. SupremeLeader System Console Routes
// ==========================================
Router::group(['middleware' => [AuthMiddleware::class, new RoleMiddleware('supreme_leader')]], function () {
    Router::get('/admin/system', [SupremeLeaderController::class, 'system']);
    Router::get('/admin/provision-owner', [SupremeLeaderController::class, 'showProvisionOwner']);
    Router::post('/admin/provision-owner', [SupremeLeaderController::class, 'provisionOwner']);
    Router::post('/admin/support/{id}', [SupremeLeaderController::class, 'supportGym']);
    Router::post('/admin/exit-support', [SupremeLeaderController::class, 'exitSupport']);
    Router::get('/admin/logs', [SupremeLeaderController::class, 'logs']);
    Router::post('/admin/owners/{id}/toggle', [SupremeLeaderController::class, 'toggleOwnerStatus']);
});

// ==========================================
// 4. Trainer Workspace Routes (Phase C)
// ==========================================
Router::group(['middleware' => [AuthMiddleware::class, new RoleMiddleware('trainer')]], function () {
    Router::get('/trainer/dashboard', [TrainerController::class, 'dashboard']);
    Router::get('/trainer/members', [TrainerController::class, 'members']);
    Router::get('/trainer/members/{id}', [TrainerController::class, 'memberDetails']);

    Router::get('/trainer/availability', [TrainerController::class, 'availability']);
    Router::post('/trainer/availability', [TrainerController::class, 'storeAvailability']);
    Router::post('/trainer/availability/{id}/delete', [TrainerController::class, 'deleteAvailability']);

    Router::get('/trainer/sessions', [TrainerController::class, 'sessions']);
    Router::get('/trainer/schedule', [TrainerController::class, 'sessions']);
    Router::post('/trainer/sessions', [TrainerController::class, 'storeSession']);
    Router::post('/trainer/sessions/{id}/status', [TrainerController::class, 'updateSessionStatus']);

    Router::get('/trainer/progress', [TrainerController::class, 'progress']);
    Router::post('/trainer/progress', [TrainerController::class, 'storeProgress']);
});

// ==========================================
// 5. Member Self-Service Portal Routes (Phase D)
// ==========================================
Router::group(['middleware' => [AuthMiddleware::class, new RoleMiddleware('member')]], function () {
    Router::get('/portal/dashboard', [MemberPortalController::class, 'dashboard']);
    Router::post('/portal/payments/request', [MemberPortalController::class, 'submitPaymentRequest']);
    Router::get('/portal/receipts/{id}', [MemberPortalController::class, 'receipt']);
    Router::post('/portal/profile', [MemberPortalController::class, 'updateProfile']);
});

// ==========================================
// 6. Owner & Staff Operational Routes
// ==========================================
Router::group(['middleware' => [AuthMiddleware::class, new RoleMiddleware(['owner', 'staff'])]], function () {
    // Command Center
    Router::get('/dashboard', [DashboardController::class, 'index']);

    // Trainer Assignments & Roster (Phase C)
    Router::get('/trainers', [TrainerAssignmentController::class, 'index']);
    Router::post('/trainers/assign', [TrainerAssignmentController::class, 'assign']);
    Router::post('/trainers/unassign/{id}', [TrainerAssignmentController::class, 'unassign']);

    // Members Management (Phase 2)
    Router::get('/members', [MemberController::class, 'index']);
    Router::get('/members/create', [MemberController::class, 'create']);
    Router::post('/members', [MemberController::class, 'store']);
    Router::get('/members/{id}', [MemberController::class, 'show']);
    Router::get('/members/{id}/edit', [MemberController::class, 'edit']);
    Router::post('/members/{id}', [MemberController::class, 'update']);
    Router::post('/members/{id}/archive', [MemberController::class, 'archive']);
    Router::post('/members/{id}/restore', [MemberController::class, 'restore']);
    Router::post('/members/{id}/create-portal-account', [MemberController::class, 'createPortalAccount']);

    // Membership Plans & Renewals (Phase 3)
    Router::get('/plans', [PlanController::class, 'index']);
    Router::get('/plans/create', [PlanController::class, 'create']);
    Router::post('/plans', [PlanController::class, 'store']);
    Router::get('/plans/{id}/edit', [PlanController::class, 'edit']);
    Router::post('/plans/{id}', [PlanController::class, 'update']);
    Router::post('/plans/{id}/toggle', [PlanController::class, 'toggle']);

    Router::get('/renewals', [RenewalController::class, 'index']);
    Router::post('/renewals', [RenewalController::class, 'renew']);

    // Payments, Billing & Reversals (Phase 4)
    Router::get('/payments', [PaymentController::class, 'index']);
    Router::get('/payments/create', [PaymentController::class, 'create']);
    Router::post('/payments', [PaymentController::class, 'store']);
    Router::get('/payments/{id}/receipt', [PaymentController::class, 'receipt']);
    Router::post('/payments/{id}/void', [PaymentReversalController::class, 'void']);

    // Offline Member Payment Requests (Phase D)
    Router::get('/payment-requests', [PaymentRequestController::class, 'index']);
    Router::post('/payment-requests/{id}/approve', [PaymentRequestController::class, 'approve']);
    Router::post('/payment-requests/{id}/reject', [PaymentRequestController::class, 'reject']);

    // Reports & Analytics (Phase 5)
    Router::get('/reports', [ReportController::class, 'index']);
    Router::get('/reports/export/collections', [ReportController::class, 'exportCollections']);
    Router::get('/reports/export/dues', [ReportController::class, 'exportDues']);

    // Member Batch Imports (Phase 5)
    Router::get('/imports', [ImportController::class, 'index']);
    Router::get('/imports/template', [ImportController::class, 'template']);
    Router::post('/imports/upload', [ImportController::class, 'upload']);

    // Staff & Team Access (Phase 6)
    Router::get('/staff', [StaffController::class, 'index']);
    Router::post('/staff', [StaffController::class, 'store']);
    Router::post('/staff/{id}/toggle', [StaffController::class, 'toggle']);

    // Security Audit Trail (Phase 6)
    Router::get('/audit-logs', [AuditLogController::class, 'index']);

    // Gym Settings & Preferences (Phase 6)
    Router::get('/settings', [SettingsController::class, 'index']);
    Router::post('/settings', [SettingsController::class, 'update']);
});
