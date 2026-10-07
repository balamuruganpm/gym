<?php
/**
 * Audit Logger Core Engine
 * Captures all security, financial, member lifecycle, and administrative events into audit_logs.
 */

class AuditLogger {
    /**
     * Record an audit event
     * @param string $action (e.g. 'member.registered', 'payment.collected', 'attendance.blocked_expiry')
     * @param string $entityType (e.g. 'member', 'payment', 'attendance', 'system')
     * @param int|null $entityId
     * @param array|null $details
     * @param int|null $userId
     * @param int|null $branchId
     */
    public static function log($action, $entityType, $entityId = null, $details = null, $userId = null, $branchId = null) {
        try {
            if (!Database::tableExists('audit_logs')) {
                return false;
            }

            if ($userId === null && class_exists('Auth')) {
                $user = Auth::user();
                $userId = $user ? $user['id'] : null;
                if ($branchId === null && $user) {
                    $branchId = isset($user['branch_id']) ? $user['branch_id'] : null;
                }
            }

            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
            $detailsJson = !empty($details) ? json_encode($details) : null;

            $query = "INSERT INTO audit_logs (user_id, branch_id, action, entity_type, entity_id, details_json, ip_address, created_at) 
                      VALUES (:user_id, :branch_id, :action, :entity_type, :entity_id, :details_json, :ip, NOW())";

            return Database::insert($query, [
                ':user_id'     => $userId,
                ':branch_id'   => $branchId,
                ':action'      => $action,
                ':entity_type' => $entityType,
                ':entity_id'   => $entityId,
                ':details_json'=> $detailsJson,
                ':ip'          => $ip
            ]);
        } catch (Exception $e) {
            error_log("AuditLogger Error: " . $e->getMessage());
            return false;
        }
    }

    public static function getRecent($limit = 50, $branchId = null) {
        return self::getRecentLogs($limit, $branchId);
    }

    /**
     * Fetch recent audit logs with user and branch info
     */
    public static function getRecentLogs($limit = 50, $branchId = null) {
        try {
            $query = "SELECT a.*, u.name as user_name, u.role as user_role, b.name as branch_name 
                      FROM audit_logs a 
                      LEFT JOIN users u ON a.user_id = u.id 
                      LEFT JOIN branches b ON a.branch_id = b.id";
            
            $params = [];
            if ($branchId !== null) {
                $query .= " WHERE a.branch_id = :branch_id";
                $params[':branch_id'] = $branchId;
            }

            $query .= " ORDER BY a.created_at DESC LIMIT " . (int)$limit;
            return Database::fetchAll($query, $params);
        } catch (Exception $e) {
            return [];
        }
    }
}
