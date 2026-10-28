<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Database;
use App\Services\Session;
use App\Services\TenantContext;

class AuditLogController extends Controller
{
    public function index(): string
    {
        $gymId = TenantContext::getGymId();
        $userRole = (string) (Session::get('user.role') ?? Session::get('user_role') ?? '');

        if ($userRole !== 'owner') {
            http_response_code(403);
            return view('errors.404', ['message' => 'Access Denied. Only Gym Owners can inspect security audit logs.']);
        }

        $actionFilter = trim((string) ($_GET['action'] ?? 'all'));

        $params = [':gym_id' => $gymId];
        $where = ["a.gym_id = :gym_id"];

        if (!empty($actionFilter) && $actionFilter !== 'all') {
            $where[] = "a.action = :action";
            $params[':action'] = $actionFilter;
        }

        $whereSql = implode(' AND ', $where);

        $logs = Database::fetchAll("
            SELECT 
                a.*,
                u.name as actor_name,
                u.email as actor_email
            FROM `audit_logs` a
            LEFT JOIN `users` u ON u.id = a.user_id
            WHERE {$whereSql}
            ORDER BY a.id DESC
            LIMIT 100
        ", $params);

        // Fetch distinct actions for dropdown
        $actions = Database::fetchAll("
            SELECT DISTINCT `action` FROM `audit_logs` WHERE `gym_id` = :gym_id ORDER BY `action` ASC
        ", [':gym_id' => $gymId]);

        return $this->view('audit_logs.index', [
            'pageTitle' => 'Security Audit Trail — GymFlow',
            'logs' => $logs,
            'actions' => array_column($actions, 'action'),
            'currentAction' => $actionFilter,
        ]);
    }
}
