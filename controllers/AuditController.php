<?php
/**
 * Audit Controller
 * Displays the security and administrative audit trail.
 */

class AuditController {
    public function index() {
        RBAC::requireRole(['superadmin', 'owner']);

        $user = Auth::user();
        $branchId = ($user['role'] === 'superadmin') ? null : $user['branch_id'];

        $logs = AuditLogger::getRecentLogs(100, $branchId);

        View::render('admin/audit_logs/index', [
            'pageTitle'  => 'System Audit Trail - FitPulse',
            'activeMenu' => 'audit',
            'logs'       => $logs
        ], 'layouts/app');
    }
}
