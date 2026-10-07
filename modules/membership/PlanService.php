<?php
/**
 * Plan Service - Membership Plans Management Engine
 */

class PlanService {
    public function getAll($branchId = null, $activeOnly = false) {
        return self::getAllPlans($branchId, $activeOnly);
    }

    /**
     * Get all plans (optionally filtered by branch and active status)
     */
    public static function getAllPlans($branchId = null, $activeOnly = false) {
        $query = "SELECT p.*, b.name as branch_name,
                         (SELECT COUNT(*) FROM member_subscriptions m WHERE m.plan_id = p.id AND m.status = 'active') as active_subscribers
                  FROM membership_plans p
                  LEFT JOIN branches b ON p.branch_id = b.id";
        
        $params = [];
        $where = [];

        if ($branchId !== null) {
            $where[] = "p.branch_id = :branch_id";
            $params[':branch_id'] = (int)$branchId;
        }

        if ($activeOnly) {
            $where[] = "p.is_active = 1";
        }

        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }

        $query .= " ORDER BY p.duration_days ASC, p.price ASC";

        try {
            return Database::fetchAll($query, $params);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Find plan by ID
     */
    public static function findById($id) {
        $query = "SELECT p.*, b.name as branch_name 
                  FROM membership_plans p 
                  LEFT JOIN branches b ON p.branch_id = b.id 
                  WHERE p.id = :id LIMIT 1";
        return Database::fetchOne($query, [':id' => (int)$id]);
    }

    /**
     * Create a new membership plan
     */
    public static function create($data) {
        $query = "INSERT INTO membership_plans (branch_id, name, duration_days, price, admission_fee, description, is_active, created_at)
                  VALUES (:branch_id, :name, :duration_days, :price, :admission_fee, :description, :is_active, NOW())";

        $params = [
            ':branch_id'     => (int)$data['branch_id'],
            ':name'          => trim($data['name']),
            ':duration_days' => (int)$data['duration_days'],
            ':price'         => (float)$data['price'],
            ':admission_fee' => !empty($data['admission_fee']) ? (float)$data['admission_fee'] : 0.00,
            ':description'   => isset($data['description']) ? trim($data['description']) : null,
            ':is_active'     => isset($data['is_active']) ? 1 : 0
        ];

        $planId = Database::insert($query, $params);
        if ($planId) {
            AuditLogger::log('plan.created', 'plan', $planId, ['name' => $data['name'], 'price' => $data['price'], 'duration' => $data['duration_days']], null, $data['branch_id']);
        }
        return $planId;
    }

    /**
     * Update an existing plan
     */
    public static function update($id, $data) {
        $query = "UPDATE membership_plans SET 
                    branch_id = :branch_id,
                    name = :name,
                    duration_days = :duration_days,
                    price = :price,
                    admission_fee = :admission_fee,
                    description = :description,
                    is_active = :is_active
                  WHERE id = :id";

        $params = [
            ':id'            => (int)$id,
            ':branch_id'     => (int)$data['branch_id'],
            ':name'          => trim($data['name']),
            ':duration_days' => (int)$data['duration_days'],
            ':price'         => (float)$data['price'],
            ':admission_fee' => !empty($data['admission_fee']) ? (float)$data['admission_fee'] : 0.00,
            ':description'   => isset($data['description']) ? trim($data['description']) : null,
            ':is_active'     => isset($data['is_active']) ? 1 : 0
        ];

        $result = Database::execute($query, $params);
        AuditLogger::log('plan.updated', 'plan', $id, ['name' => $data['name'], 'price' => $data['price']]);
        return $result;
    }

    /**
     * Toggle plan status
     */
    public static function toggleStatus($id) {
        $plan = self::findById($id);
        if (!$plan) {
            return false;
        }

        $newStatus = $plan['is_active'] ? 0 : 1;
        $query = "UPDATE membership_plans SET is_active = :status WHERE id = :id";
        $result = Database::execute($query, [':status' => $newStatus, ':id' => (int)$id]);

        AuditLogger::log('plan.status_toggle', 'plan', $id, ['status' => $newStatus]);
        return $newStatus;
    }
}
