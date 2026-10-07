<?php
/**
 * Reports & Analytics Controller
 * Handles business analytics, churn risk alerts, revenue charts, and server health diagnostics.
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../modules/reports/ReportService.php';
require_once __DIR__ . '/../modules/branches/BranchService.php';

class ReportController {
    private $reportService;
    private $branchService;

    public function __construct() {
        Auth::requireLogin();
        $this->reportService = new ReportService();
        $this->branchService = new BranchService();
    }

    /**
     * Reports & Analytics Hub
     */
    public function index() {
        Auth::requirePermission('reports.view');

        $user = Auth::user();
        $selectedBranch = $_GET['branch_id'] ?? ($user['role'] === 'superadmin' ? null : $user['branch_id']);

        $pulse = $this->reportService->getTodayPulse($selectedBranch);
        $attendanceTrend = $this->reportService->getAttendanceTrend($selectedBranch, 7);
        $revenueTrend = $this->reportService->getMonthlyRevenueTrend($selectedBranch);
        $plans = $this->reportService->getPlanDistribution($selectedBranch);
        $inactiveMembers = $this->reportService->getInactiveMembers($selectedBranch, 7, 10);
        $branches = $this->branchService->getActiveBranches();

        View::render('admin/reports/index', [
            'pulse' => $pulse,
            'attendanceTrend' => $attendanceTrend,
            'revenueTrend' => $revenueTrend,
            'plans' => $plans,
            'inactiveMembers' => $inactiveMembers,
            'branches' => $branches,
            'selectedBranch' => $selectedBranch
        ]);
    }

    /**
     * System Health & Server Diagnostics
     */
    public function systemHealth() {
        Auth::requireRole(['superadmin', 'owner']);

        $diagnostics = $this->reportService->getSystemDiagnostics();

        View::render('admin/reports/health', [
            'diagnostics' => $diagnostics
        ]);
    }
}
