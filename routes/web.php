<?php

declare(strict_types=1);

use App\Services\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

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

// Guest Routes
Router::group(['middleware' => GuestMiddleware::class], function () {
    Router::get('/', function () {
        header('Location: ' . url('login'));
        exit;
    });

    Router::get('/login', [AuthController::class, 'showLogin']);
    Router::post('/login', [AuthController::class, 'login']);

    Router::get('/register', [AuthController::class, 'showRegister']);
    Router::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Router::group(['middleware' => AuthMiddleware::class], function () {
    Router::get('/dashboard', [DashboardController::class, 'index']);
    Router::post('/logout', [AuthController::class, 'logout']);

    // Members Management (Phase 2)
    Router::get('/members', [MemberController::class, 'index']);
    Router::get('/members/create', [MemberController::class, 'create']);
    Router::post('/members', [MemberController::class, 'store']);
    Router::get('/members/{id}', [MemberController::class, 'show']);
    Router::get('/members/{id}/edit', [MemberController::class, 'edit']);
    Router::post('/members/{id}', [MemberController::class, 'update']);
    Router::post('/members/{id}/archive', [MemberController::class, 'archive']);
    Router::post('/members/{id}/restore', [MemberController::class, 'restore']);

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
