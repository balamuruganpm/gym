<?php
/**
 * Admin Controller
 * Handles owner/manager/superadmin dashboard, Coder Control Center, and dynamic feature management.
 */

class AdminController {
    public function dashboard() {
        Auth::requireLogin();
        $user = Auth::user();

        if ($user['role'] === 'member') {
            header('Location: ' . BASE_URL . '/member/dashboard');
            exit;
        }

        require_once __DIR__ . '/../modules/reports/ReportService.php';
        require_once __DIR__ . '/../modules/branches/BranchService.php';
        
        $reportService = new ReportService();
        $branchService = new BranchService();

        $selectedBranch = $_GET['branch_id'] ?? ($user['role'] === 'superadmin' ? null : $user['branch_id']);
        
        $pulse = $reportService->getTodayPulse($selectedBranch);
        $attendanceTrend = $reportService->getAttendanceTrend($selectedBranch, 7);
        $revenueTrend = $reportService->getMonthlyRevenueTrend($selectedBranch);
        $inactiveMembers = $reportService->getInactiveMembers($selectedBranch, 7, 5);
        $recentCheckins = $reportService->getRecentCheckins($selectedBranch, 6);
        $upcomingRenewals = $reportService->getUpcomingRenewals($selectedBranch, 5);
        $recentPayments = $reportService->getRecentPayments($selectedBranch, 5);
        $planDistribution = $reportService->getPlanDistribution($selectedBranch);
        $hourlyTraffic = $reportService->getHourlyTraffic($selectedBranch);
        $branches = $branchService->getActiveBranches();

        View::render('admin/dashboard', [
            'pageTitle'  => 'Dashboard - FitPulse',
            'activeMenu' => 'dashboard',
            'user'       => $user,
            'pulse'      => $pulse,
            'attendanceTrend' => $attendanceTrend,
            'revenueTrend' => $revenueTrend,
            'inactiveMembers' => $inactiveMembers,
            'recentCheckins' => $recentCheckins,
            'upcomingRenewals' => $upcomingRenewals,
            'recentPayments' => $recentPayments,
            'planDistribution' => $planDistribution,
            'hourlyTraffic' => $hourlyTraffic,
            'branches'   => $branches,
            'selectedBranch' => $selectedBranch
        ], 'layouts/app');
    }

    public function coderControl() {
        RBAC::requireRole('superadmin');
        $features = FeatureManager::getAllFeatures();

        View::render('admin/coder_control', [
            'pageTitle'  => 'Coder Control Center - FitPulse',
            'activeMenu' => 'coder_control',
            'features'   => $features
        ], 'layouts/app');
    }

    public function toggleFeature() {
        RBAC::requireRole('superadmin');
        CSRF::validateOrAbort();

        $key = isset($_POST['feature_key']) ? trim($_POST['feature_key']) : '';
        $features = FeatureManager::getAllFeatures();

        if (!isset($features[$key])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Unknown feature module.']);
            exit;
        }

        $currentStatus = (bool) $features[$key]['enabled'];
        $newStatus = !$currentStatus;

        FeatureManager::updateFeature($key, $newStatus);
        AuditLogger::log('coder_control.feature_toggle', 'feature', null, ['feature' => $key, 'enabled' => $newStatus]);

        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'new_status' => $newStatus, 'key' => $key]);
            exit;
        }

        Session::flash('success', "Feature '{$features[$key]['name']}' is now " . ($newStatus ? 'enabled' : 'disabled') . '.');
        header('Location: ' . BASE_URL . '/admin/coder-control');
        exit;
    }
}
