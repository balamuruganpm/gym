<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Database;
use App\Services\TenantContext;

class Member
{
    /**
     * Generate next sequential member number for the tenant gym.
     * Format: GF-{YEAR}-{0001}
     */
    public static function generateMemberNumber(int $gymId): string
    {
        $year = date('Y');
        $prefix = "GF-{$year}-";

        $latest = Database::query("
            SELECT `member_number` FROM `members` 
            WHERE `gym_id` = :gym_id AND `member_number` LIKE :prefix 
            ORDER BY `id` DESC LIMIT 1
        ", [
            ':gym_id' => $gymId,
            ':prefix' => $prefix . '%'
        ])->fetchColumn();

        if ($latest) {
            $parts = explode('-', (string) $latest);
            $seq = isset($parts[2]) ? (int) $parts[2] + 1 : 1;
        } else {
            $seq = 1;
        }

        return sprintf('%s%04d', $prefix, $seq);
    }

    /**
     * Get paginated members list with real-time membership status and search/filter.
     */
    public static function getAll(int $gymId, array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [':gym_id' => $gymId];
        $where = ["m.gym_id = :gym_id"];

        // Handle Archive filter
        $statusFilter = $filters['status'] ?? 'all';
        if ($statusFilter === 'archived') {
            $where[] = "m.is_archived = 1";
        } else {
            $where[] = "m.is_archived = 0";
        }

        // Handle Search filter
        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $where[] = "(m.first_name LIKE :s1 OR m.last_name LIKE :s2 OR m.phone LIKE :s3 OR m.email LIKE :s4 OR m.member_number LIKE :s5)";
            $params[':s1'] = $search;
            $params[':s2'] = $search;
            $params[':s3'] = $search;
            $params[':s4'] = $search;
            $params[':s5'] = $search;
        }

        $whereClause = implode(' AND ', $where);

        // Having conditions for computed status
        $outerWhere = [];
        if ($statusFilter === 'active') {
            $outerWhere[] = "sub.computed_status = 'active'";
        } elseif ($statusFilter === 'expiring_soon') {
            $outerWhere[] = "sub.computed_status = 'expiring_soon'";
        } elseif ($statusFilter === 'expired') {
            $outerWhere[] = "sub.computed_status = 'expired'";
        } elseif ($statusFilter === 'dues_pending') {
            $outerWhere[] = "sub.total_due > 0";
        }

        $outerWhereSql = !empty($outerWhere) ? 'WHERE ' . implode(' AND ', $outerWhere) : '';

        $sql = "
            SELECT * FROM (
                SELECT 
                    m.*,
                    latest_mem.plan_name_snapshot,
                    latest_mem.start_date,
                    latest_mem.expiry_date,
                    latest_mem.agreed_price,
                    latest_mem.id as latest_membership_id,
                    COALESCE(payments_sum.total_paid, 0) as total_paid,
                    GREATEST(0, COALESCE(latest_mem.agreed_price, 0) - COALESCE(payments_sum.total_paid, 0)) as total_due,
                    CASE 
                        WHEN m.is_archived = 1 THEN 'archived'
                        WHEN latest_mem.id IS NULL THEN 'no_plan'
                        WHEN latest_mem.status = 'cancelled' THEN 'cancelled'
                        WHEN CURRENT_DATE() > latest_mem.expiry_date THEN 'expired'
                        WHEN latest_mem.expiry_date <= DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY) THEN 'expiring_soon'
                        WHEN CURRENT_DATE() BETWEEN latest_mem.start_date AND latest_mem.expiry_date THEN 'active'
                        ELSE 'pending'
                    END as computed_status,
                    DATEDIFF(latest_mem.expiry_date, CURRENT_DATE()) as days_left
                FROM `members` m
                LEFT JOIN (
                    SELECT mem1.*
                    FROM `memberships` mem1
                    INNER JOIN (
                        SELECT member_id, MAX(id) as max_id
                        FROM `memberships`
                        GROUP BY member_id
                    ) mem2 ON mem1.id = mem2.max_id
                ) latest_mem ON latest_mem.member_id = m.id
                LEFT JOIN (
                    SELECT membership_id, SUM(amount) as total_paid
                    FROM `payments`
                    WHERE `status` = 'paid'
                    GROUP BY membership_id
                ) payments_sum ON payments_sum.membership_id = latest_mem.id
                WHERE {$whereClause}
            ) sub
            {$outerWhereSql}
            ORDER BY sub.id DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";

        return Database::fetchAll($sql, $params);
    }

    /**
     * Count total members for pagination.
     */
    public static function countAll(int $gymId, array $filters = []): int
    {
        $params = [':gym_id' => $gymId];
        $where = ["m.gym_id = :gym_id"];

        $statusFilter = $filters['status'] ?? 'all';
        if ($statusFilter === 'archived') {
            $where[] = "m.is_archived = 1";
        } else {
            $where[] = "m.is_archived = 0";
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $where[] = "(m.first_name LIKE :s1 OR m.last_name LIKE :s2 OR m.phone LIKE :s3 OR m.email LIKE :s4 OR m.member_number LIKE :s5)";
            $params[':s1'] = $search;
            $params[':s2'] = $search;
            $params[':s3'] = $search;
            $params[':s4'] = $search;
            $params[':s5'] = $search;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT COUNT(*) FROM `members` m WHERE {$whereClause}";
        return (int) Database::query($sql, $params)->fetchColumn();
    }

    /**
     * Find member by ID and verify tenant ownership.
     */
    public static function find(int $id, int $gymId): ?array
    {
        return Database::fetchOne("
            SELECT * FROM `members` WHERE `id` = :id AND `gym_id` = :gym_id LIMIT 1
        ", [
            ':id' => $id,
            ':gym_id' => $gymId
        ]);
    }

    /**
     * Create a new member.
     */
    public static function create(array $data): int
    {
        $sql = "
            INSERT INTO `members` (
                `gym_id`, `member_number`, `first_name`, `last_name`, 
                `phone`, `email`, `gender`, `date_of_birth`, 
                `address`, `emergency_contact_name`, `emergency_contact_phone`, 
                `photo_path`, `notes`, `is_archived`, `created_at`
            ) VALUES (
                :gym_id, :member_number, :first_name, :last_name, 
                :phone, :email, :gender, :date_of_birth, 
                :address, :emergency_contact_name, :emergency_contact_phone, 
                :photo_path, :notes, 0, NOW()
            )
        ";

        Database::query($sql, [
            ':gym_id' => $data['gym_id'],
            ':member_number' => $data['member_number'],
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'] ?? null,
            ':phone' => $data['phone'],
            ':email' => $data['email'] ?? null,
            ':gender' => $data['gender'] ?? null,
            ':date_of_birth' => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
            ':address' => $data['address'] ?? null,
            ':emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            ':emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            ':photo_path' => $data['photo_path'] ?? null,
            ':notes' => $data['notes'] ?? null,
        ]);

        return (int) Database::getConnection()->lastInsertId();
    }

    /**
     * Update an existing member.
     */
    public static function update(int $id, int $gymId, array $data): bool
    {
        $sql = "
            UPDATE `members` SET
                `first_name` = :first_name,
                `last_name` = :last_name,
                `phone` = :phone,
                `email` = :email,
                `gender` = :gender,
                `date_of_birth` = :date_of_birth,
                `address` = :address,
                `emergency_contact_name` = :emergency_contact_name,
                `emergency_contact_phone` = :emergency_contact_phone,
                `photo_path` = COALESCE(:photo_path, `photo_path`),
                `notes` = :notes,
                `updated_at` = NOW()
            WHERE `id` = :id AND `gym_id` = :gym_id
        ";

        Database::query($sql, [
            ':id' => $id,
            ':gym_id' => $gymId,
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'] ?? null,
            ':phone' => $data['phone'],
            ':email' => $data['email'] ?? null,
            ':gender' => $data['gender'] ?? null,
            ':date_of_birth' => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
            ':address' => $data['address'] ?? null,
            ':emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            ':emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            ':photo_path' => $data['photo_path'] ?? null,
            ':notes' => $data['notes'] ?? null,
        ]);

        return true;
    }

    /**
     * Soft-archive a member (preserves historical memberships and payments).
     */
    public static function archive(int $id, int $gymId): bool
    {
        Database::query("
            UPDATE `members` SET `is_archived` = 1, `updated_at` = NOW() 
            WHERE `id` = :id AND `gym_id` = :gym_id
        ", [':id' => $id, ':gym_id' => $gymId]);
        return true;
    }

    /**
     * Restore an archived member.
     */
    public static function restore(int $id, int $gymId): bool
    {
        Database::query("
            UPDATE `members` SET `is_archived` = 0, `updated_at` = NOW() 
            WHERE `id` = :id AND `gym_id` = :gym_id
        ", [':id' => $id, ':gym_id' => $gymId]);
        return true;
    }

    /**
     * Comprehensive 360° Member Profile data fetcher.
     */
    public static function getProfile(int $id, int $gymId): ?array
    {
        $member = self::find($id, $gymId);
        if (!$member) {
            return null;
        }

        // 1. Fetch all memberships
        $memberships = Database::fetchAll("
            SELECT 
                mem.*,
                COALESCE(pay_sum.total_paid, 0) as total_paid,
                GREATEST(0, mem.agreed_price - COALESCE(pay_sum.total_paid, 0)) as balance_due
            FROM `memberships` mem
            LEFT JOIN (
                SELECT membership_id, SUM(amount) as total_paid
                FROM `payments`
                WHERE `status` = 'paid'
                GROUP BY membership_id
            ) pay_sum ON pay_sum.membership_id = mem.id
            WHERE mem.member_id = :id AND mem.gym_id = :gym_id
            ORDER BY mem.start_date DESC, mem.id DESC
        ", [':id' => $id, ':gym_id' => $gymId]);

        // 2. Fetch all payment transactions
        $payments = Database::fetchAll("
            SELECT 
                p.*,
                u.name as recorded_by_name,
                pr.reason as reversal_reason,
                pr.reversed_at,
                rev_u.name as reversed_by_name
            FROM `payments` p
            LEFT JOIN `users` u ON u.id = p.user_id
            LEFT JOIN `payment_reversals` pr ON pr.payment_id = p.id
            LEFT JOIN `users` rev_u ON rev_u.id = pr.authorized_by_user_id
            WHERE p.member_id = :id AND p.gym_id = :gym_id
            ORDER BY p.payment_date DESC, p.id DESC
        ", [':id' => $id, ':gym_id' => $gymId]);

        // Active subscription is the first active one or latest one
        $activeMembership = null;
        foreach ($memberships as $mem) {
            if ($mem['status'] === 'active' && strtotime($mem['expiry_date']) >= strtotime(date('Y-m-d'))) {
                $activeMembership = $mem;
                break;
            }
        }
        if (!$activeMembership && !empty($memberships)) {
            $activeMembership = $memberships[0];
        }

        // Total paid & dues summary
        $totalPaidOverall = 0.0;
        $totalDueOverall = 0.0;
        foreach ($memberships as $mem) {
            $totalPaidOverall += (float) $mem['total_paid'];
            $totalDueOverall += (float) $mem['balance_due'];
        }

        return [
            'member' => $member,
            'active_membership' => $activeMembership,
            'memberships' => $memberships,
            'payments' => $payments,
            'total_paid_overall' => $totalPaidOverall,
            'total_due_overall' => $totalDueOverall,
        ];
    }
}
