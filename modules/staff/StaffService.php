<?php
/**
 * Staff Service - Staff, Managers & Trainer Management Engine
 */

class StaffService {
    public function getAll($branchId = null, $role = null) {
        return self::getAllStaff($branchId, $role);
    }

    /**
     * Get all staff, managers and trainers (optionally filtered by branch and role)
     */
    public static function getAllStaff($branchId = null, $role = null) {
        $query = "SELECT u.*, b.name as branch_name, b.code as branch_code,
                         (SELECT COUNT(*) FROM members m WHERE m.branch_id = u.branch_id) as total_branch_members
                  FROM users u
                  LEFT JOIN branches b ON u.branch_id = b.id
                  WHERE u.role IN ('owner', 'manager', 'staff', 'trainer')";
        
        $params = [];
        if ($branchId !== null) {
            $query .= " AND u.branch_id = :branch_id";
            $params[':branch_id'] = (int)$branchId;
        }

        if ($role !== null) {
            $query .= " AND u.role = :role";
            $params[':role'] = $role;
        }

        $query .= " ORDER BY u.role ASC, u.name ASC";

        try {
            return Database::fetchAll($query, $params);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Find staff user by ID
     */
    public static function findById($id) {
        $query = "SELECT u.*, b.name as branch_name 
                  FROM users u 
                  LEFT JOIN branches b ON u.branch_id = b.id 
                  WHERE u.id = :id AND u.role IN ('owner', 'manager', 'staff', 'trainer') 
                  LIMIT 1";
        return Database::fetchOne($query, [':id' => (int)$id]);
    }

    /**
     * Create staff/manager/trainer account
     */
    public static function create($data) {
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        $name = !empty($data['name']) ? trim($data['name']) : (!empty($data['full_name']) ? trim($data['full_name']) : '');

        $query = "INSERT INTO users (branch_id, name, email, phone, password, role, is_active, created_at)
                  VALUES (:branch_id, :name, :email, :phone, :password, :role, :is_active, NOW())";

        $params = [
            ':branch_id'     => !empty($data['branch_id']) ? (int)$data['branch_id'] : null,
            ':name'          => $name,
            ':email'         => strtolower(trim($data['email'])),
            ':phone'         => isset($data['phone']) ? trim($data['phone']) : null,
            ':password'      => $passwordHash,
            ':role'          => $data['role'],
            ':is_active'     => isset($data['is_active']) ? 1 : 0
        ];

        $userId = Database::insert($query, $params);
        if ($userId) {
            AuditLogger::log('staff.created', 'user', $userId, ['name' => $name, 'role' => $data['role']], null, $data['branch_id']);
        }
        return $userId;
    }

    /**
     * Update staff account
     */
    public static function update($id, $data) {
        $name = !empty($data['name']) ? trim($data['name']) : (!empty($data['full_name']) ? trim($data['full_name']) : '');
        $params = [
            ':id'        => (int)$id,
            ':branch_id' => !empty($data['branch_id']) ? (int)$data['branch_id'] : null,
            ':name'      => $name,
            ':email'     => strtolower(trim($data['email'])),
            ':phone'     => isset($data['phone']) ? trim($data['phone']) : null,
            ':role'      => $data['role'],
            ':is_active' => isset($data['is_active']) ? 1 : 0
        ];

        $query = "UPDATE users SET 
                    branch_id = :branch_id,
                    name = :name,
                    email = :email,
                    phone = :phone,
                    role = :role,
                    is_active = :is_active";

        // Update password only if provided
        if (!empty($data['password'])) {
            $query .= ", password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $query .= " WHERE id = :id";

        $result = Database::execute($query, $params);
        AuditLogger::log('staff.updated', 'user', $id, ['name' => $name, 'role' => $data['role']]);
        return $result;
    }

    /**
     * Toggle staff status
     */
    public static function toggleStatus($id) {
        $user = self::findById($id);
        if (!$user) {
            return false;
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        $query = "UPDATE users SET is_active = :status WHERE id = :id";
        $result = Database::execute($query, [':status' => $newStatus, ':id' => (int)$id]);

        AuditLogger::log('staff.status_toggle', 'user', $id, ['status' => $newStatus]);
        return $newStatus;
    }
}
