<?php
/**
 * Role-Based Access Control (RBAC) Engine
 * Manages role hierarchies, capability permissions, and boundary enforcement.
 */

class RBAC {
    // Role Hierarchy Definitions
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_OWNER      = 'owner';
    const ROLE_MANAGER    = 'manager';
    const ROLE_STAFF      = 'staff';
    const ROLE_TRAINER    = 'trainer';
    const ROLE_MEMBER     = 'member';

    // Permission Matrix Mapping
    private static $rolePermissions = [
        self::ROLE_SUPERADMIN => ['*'], // Superadmin has wild-card all permissions
        
        self::ROLE_OWNER => [
            'dashboard.view', 'system.settings.view', 'system.settings.edit',
            'branches.view', 'branches.manage',
            'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
            'trainers.view', 'trainers.create', 'trainers.edit', 'trainers.delete',
            'members.view', 'members.create', 'members.edit', 'members.delete', 'members.export',
            'membership.view', 'membership.create', 'membership.renew', 'membership.freeze',
            'attendance.view', 'attendance.record', 'attendance.export',
            'payments.view', 'payments.record', 'payments.collect', 'payments.export', 'payments.refund',
            'progress.view_all', 'progress.manage',
            'reports.view', 'reports.export',
            'audit.view', 'automation.manage'
        ],

        self::ROLE_MANAGER => [
            'dashboard.view',
            'branches.view',
            'staff.view', 'trainers.view',
            'members.view', 'members.create', 'members.edit', 'members.export',
            'membership.view', 'membership.create', 'membership.renew', 'membership.freeze',
            'attendance.view', 'attendance.record', 'attendance.export',
            'payments.view', 'payments.record', 'payments.collect', 'payments.export',
            'progress.view_all', 'progress.manage',
            'reports.view', 'reports.export'
        ],

        self::ROLE_STAFF => [
            'dashboard.view',
            'members.view', 'members.create', 'members.edit',
            'membership.view', 'membership.create', 'membership.renew', 'membership.freeze',
            'attendance.view', 'attendance.record',
            'payments.view', 'payments.record', 'payments.collect',
            'progress.view_staff'
        ],

        self::ROLE_TRAINER => [
            'dashboard.view',
            'members.view_assigned',
            'attendance.view_assigned',
            'progress.view_assigned', 'progress.manage_assigned',
            'workouts.manage_assigned', 'diets.manage_assigned'
        ],

        self::ROLE_MEMBER => [
            'member.dashboard',
            'member.profile',
            'member.qr',
            'member.checkin',
            'member.attendance_history',
            'member.payments_history',
            'member.progress_manage',
            'member.workouts_view'
        ]
    ];

    /**
     * Check if a specific role has a permission
     */
    public static function roleHasPermission($role, $permission) {
        if (!isset(self::$rolePermissions[$role])) {
            return false;
        }

        $permissions = self::$rolePermissions[$role];

        // Superadmin wildcard check
        if (in_array('*', $permissions, true)) {
            return true;
        }

        return in_array($permission, $permissions, true);
    }

    /**
     * Check if currently logged in user has permission
     */
    public static function can($permission) {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return self::roleHasPermission($user['role'], $permission);
    }

    /**
     * Require permission or abort with 403
     */
    public static function requirePermission($permission) {
        Auth::requireLogin();

        if (!self::can($permission)) {
            http_response_code(403);
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Unauthorized: Insufficient privileges.']);
            } else {
                View::render('errors/403', ['permission' => $permission], 'layouts/app');
            }
            exit;
        }
    }

    /**
     * Check if user is in one of the allowed roles
     */
    public static function hasRole($roles) {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        return in_array($user['role'], $roles, true);
    }

    /**
     * Require one of the specified roles
     */
    public static function requireRole($roles) {
        Auth::requireLogin();

        if (!self::hasRole($roles)) {
            http_response_code(403);
            View::render('errors/403', ['roles' => $roles], 'layouts/app');
            exit;
        }
    }
}
