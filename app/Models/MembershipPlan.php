<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Database;

class MembershipPlan
{
    /**
     * Get all plans for the tenant gym.
     */
    public static function getAll(int $gymId, bool $activeOnly = false): array
    {
        $sql = "SELECT * FROM `membership_plans` WHERE `gym_id` = :gym_id";
        if ($activeOnly) {
            $sql .= " AND `is_active` = 1";
        }
        $sql .= " ORDER BY `price` ASC";

        return Database::fetchAll($sql, [':gym_id' => $gymId]);
    }

    /**
     * Find plan by ID scoped to tenant.
     */
    public static function find(int $id, int $gymId): ?array
    {
        return Database::fetchOne("
            SELECT * FROM `membership_plans` 
            WHERE `id` = :id AND `gym_id` = :gym_id 
            LIMIT 1
        ", [
            ':id' => $id,
            ':gym_id' => $gymId,
        ]);
    }

    /**
     * Create a new membership plan.
     */
    public static function create(int $gymId, array $data): int
    {
        $sql = "
            INSERT INTO `membership_plans` (
                `gym_id`, `name`, `description`, `duration_value`, 
                `duration_type`, `price`, `is_active`, `created_at`
            ) VALUES (
                :gym_id, :name, :description, :duration_value, 
                :duration_type, :price, :is_active, NOW()
            )
        ";

        Database::query($sql, [
            ':gym_id' => $gymId,
            ':name' => trim($data['name']),
            ':description' => !empty($data['description']) ? trim($data['description']) : null,
            ':duration_value' => (int) $data['duration_value'],
            ':duration_type' => $data['duration_type'],
            ':price' => (float) $data['price'],
            ':is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ]);

        return (int) Database::getConnection()->lastInsertId();
    }

    /**
     * Update an existing plan.
     */
    public static function update(int $id, int $gymId, array $data): bool
    {
        $sql = "
            UPDATE `membership_plans` SET 
                `name` = :name,
                `description` = :description,
                `duration_value` = :duration_value,
                `duration_type` = :duration_type,
                `price` = :price,
                `is_active` = :is_active,
                `updated_at` = NOW()
            WHERE `id` = :id AND `gym_id` = :gym_id
        ";

        Database::query($sql, [
            ':id' => $id,
            ':gym_id' => $gymId,
            ':name' => trim($data['name']),
            ':description' => !empty($data['description']) ? trim($data['description']) : null,
            ':duration_value' => (int) $data['duration_value'],
            ':duration_type' => $data['duration_type'],
            ':price' => (float) $data['price'],
            ':is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ]);

        return true;
    }

    /**
     * Toggle active/inactive status.
     */
    public static function toggleStatus(int $id, int $gymId): bool
    {
        Database::query("
            UPDATE `membership_plans` 
            SET `is_active` = 1 - `is_active`, `updated_at` = NOW()
            WHERE `id` = :id AND `gym_id` = :gym_id
        ", [
            ':id' => $id,
            ':gym_id' => $gymId,
        ]);

        return true;
    }
}
