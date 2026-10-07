<?php
/**
 * Branch Service - Multi-Branch Management Engine
 */

class BranchService {
    public function getAll($activeOnly = false) {
        return self::getAllBranches($activeOnly);
    }

    public function getActiveBranches() {
        return self::getAllBranches(true);
    }

    /**
     * Get all branches (optionally active only)
     */
    public static function getAllBranches($activeOnly = false) {
        $query = "SELECT b.*, 
                         COUNT(DISTINCT m.id) as total_members,
                         COUNT(DISTINCT u.id) as total_staff
                  FROM branches b
                  LEFT JOIN members m ON b.id = m.branch_id
                  LEFT JOIN users u ON b.id = u.branch_id AND u.role IN ('manager', 'staff', 'trainer')";
        
        if ($activeOnly) {
            $query .= " WHERE b.is_active = 1";
        }

        $query .= " GROUP BY b.id ORDER BY b.name ASC";

        try {
            return Database::fetchAll($query);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Find branch by ID
     */
    public static function findById($id) {
        $query = "SELECT * FROM branches WHERE id = :id LIMIT 1";
        return Database::fetchOne($query, [':id' => (int)$id]);
    }

    /**
     * Create a new branch
     */
    public static function create($data) {
        $query = "INSERT INTO branches (code, name, address, phone, email, opening_time, closing_time, is_active, created_at)
                  VALUES (:code, :name, :address, :phone, :email, :opening_time, :closing_time, :is_active, NOW())";
        
        $params = [
            ':code'         => strtoupper(trim($data['code'])),
            ':name'         => trim($data['name']),
            ':address'      => isset($data['address']) ? trim($data['address']) : null,
            ':phone'        => isset($data['phone']) ? trim($data['phone']) : null,
            ':email'        => isset($data['email']) ? trim($data['email']) : null,
            ':opening_time' => !empty($data['opening_time']) ? $data['opening_time'] : '06:00:00',
            ':closing_time' => !empty($data['closing_time']) ? $data['closing_time'] : '22:00:00',
            ':is_active'    => isset($data['is_active']) ? 1 : 0
        ];

        $branchId = Database::insert($query, $params);
        if ($branchId) {
            AuditLogger::log('branch.created', 'branch', $branchId, ['name' => $data['name'], 'code' => $data['code']]);
        }
        return $branchId;
    }

    /**
     * Update an existing branch
     */
    public static function update($id, $data) {
        $query = "UPDATE branches SET 
                    code = :code,
                    name = :name,
                    address = :address,
                    phone = :phone,
                    email = :email,
                    opening_time = :opening_time,
                    closing_time = :closing_time,
                    is_active = :is_active
                  WHERE id = :id";
        
        $params = [
            ':id'           => (int)$id,
            ':code'         => strtoupper(trim($data['code'])),
            ':name'         => trim($data['name']),
            ':address'      => isset($data['address']) ? trim($data['address']) : null,
            ':phone'        => isset($data['phone']) ? trim($data['phone']) : null,
            ':email'        => isset($data['email']) ? trim($data['email']) : null,
            ':opening_time' => !empty($data['opening_time']) ? $data['opening_time'] : '06:00:00',
            ':closing_time' => !empty($data['closing_time']) ? $data['closing_time'] : '22:00:00',
            ':is_active'    => isset($data['is_active']) ? 1 : 0
        ];

        $result = Database::execute($query, $params);
        AuditLogger::log('branch.updated', 'branch', $id, ['name' => $data['name'], 'code' => $data['code']]);
        return $result;
    }

    /**
     * Get or create branch QR token for Universal Poster
     */
    public static function getBranchQrToken($branchId) {
        $branch = self::findById($branchId);
        if (!$branch) {
            return null;
        }

        // Check if qr_token column exists
        try {
            $colExists = Database::fetchOne("SHOW COLUMNS FROM branches LIKE 'qr_token'");
            if (!$colExists) {
                Database::execute("ALTER TABLE branches ADD COLUMN qr_token VARCHAR(64) UNIQUE NULL");
            }
        } catch (Exception $e) {
            // ignore
        }

        if (!empty($branch['qr_token'])) {
            return $branch['qr_token'];
        }

        $token = 'GYM-' . strtoupper($branch['code']) . '-' . substr(md5($branch['id'] . $branch['name'] . 'SECRET_SALT'), 0, 10);
        try {
            Database::execute("UPDATE branches SET qr_token = :token WHERE id = :id", [
                ':token' => $token,
                ':id'    => (int)$branchId
            ]);
        } catch (Exception $e) {
            // ignore
        }

        return $token;
    }

    /**
     * Find branch by QR Token
     */
    public static function findByQrToken($token) {
        $token = trim($token);
        if (empty($token)) {
            return null;
        }

        try {
            $colExists = Database::fetchOne("SHOW COLUMNS FROM branches LIKE 'qr_token'");
            if ($colExists) {
                $branch = Database::fetchOne("SELECT * FROM branches WHERE qr_token = :token LIMIT 1", [':token' => $token]);
                if ($branch) return $branch;
            }
        } catch (Exception $e) {
            // ignore
        }

        // Fallback matching by code or generated token
        $branches = self::getAllBranches(true);
        foreach ($branches as $b) {
            $expectedToken = 'GYM-' . strtoupper($b['code']) . '-' . substr(md5($b['id'] . $b['name'] . 'SECRET_SALT'), 0, 10);
            if ($token === $expectedToken || $token === $b['code'] || $token === (string)$b['id']) {
                return $b;
            }
        }

        return null;
    }

    /**
     * Toggle branch active status
     */
    public static function toggleStatus($id) {
        $branch = self::findById($id);
        if (!$branch) {
            return false;
        }

        $newStatus = $branch['is_active'] ? 0 : 1;
        $query = "UPDATE branches SET is_active = :status WHERE id = :id";
        $result = Database::execute($query, [':status' => $newStatus, ':id' => (int)$id]);

        AuditLogger::log('branch.status_toggle', 'branch', $id, ['status' => $newStatus]);
        return $newStatus;
    }
}
