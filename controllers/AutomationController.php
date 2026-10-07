<?php
/**
 * Automation Controller
 * Manages gym automation rules, triggers, cron runner, and notification dispatch logs.
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/RBAC.php';
require_once __DIR__ . '/../core/FeatureManager.php';
require_once __DIR__ . '/../modules/automation/AutomationService.php';

class AutomationController {
    private $automationService;

    public function __construct() {
        Auth::requireLogin();
        $this->automationService = new AutomationService();
    }

    /**
     * Automation Rules list & dispatch logs
     */
    public function index() {
        RBAC::requireRole(['superadmin', 'owner']);
        FeatureManager::requireFeature('automation_engine');

        $rules = $this->automationService->getRules();
        
        // Fetch recent automation & system logs
        $logs = Database::fetchAll("
            SELECT a.*, u.name as user_name 
            FROM audit_logs a 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE a.entity_type IN ('automation', 'automation_rules', 'notifications', 'cron', 'system')
               OR a.action LIKE '%automation%' 
               OR a.action LIKE '%cron%' 
               OR a.action LIKE '%expiry%'
            ORDER BY a.id DESC LIMIT 30
        ");

        View::render('admin/automation/index', [
            'pageTitle'  => 'Automation Engine - FitPulse',
            'activeMenu' => 'automation',
            'rules'      => $rules,
            'logs'       => $logs
        ], 'layouts/app');
    }

    /**
     * Toggle Rule status
     */
    public function toggle() {
        RBAC::requireRole(['superadmin', 'owner']);
        FeatureManager::requireFeature('automation_engine');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateOrAbort();
            $ruleId = intval($_POST['rule_id'] ?? 0);
            $status = intval($_POST['status'] ?? 0);

            $this->automationService->toggleRule($ruleId, $status);
            Session::flash('success', 'Automation rule updated successfully.');
            header('Location: ' . BASE_URL . '/admin/automation');
            exit;
        }
    }

    /**
     * Trigger manual Cron run from UI
     */
    public function runCron() {
        RBAC::requireRole(['superadmin', 'owner']);
        FeatureManager::requireFeature('automation_engine');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateOrAbort();

            $summary = $this->automationService->runScheduledCron();

            $msg = "Automation Cron executed successfully! Synced {$summary['expired_synced']} expired plan(s), sent {$summary['reminders_7d']} 7-day renewal reminder(s), {$summary['birthdays']} birthday greeting(s), and {$summary['inactive_alerts']} retention alert(s).";
            Session::flash('success', $msg);
            header('Location: ' . BASE_URL . '/admin/automation');
            exit;
        }
    }
}

