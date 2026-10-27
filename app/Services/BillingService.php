<?php

declare(strict_types=1);

namespace App\Services;

class BillingService
{
    /**
     * Generate unique sequential/random receipt number per gym.
     */
    public static function generateReceiptNumber(int $gymId): string
    {
        $year = date('Y');
        $random = rand(10000, 99999);
        return "REC-{$year}-{$random}";
    }

    /**
     * Record a new payment transaction with transaction safety.
     */
    public static function recordPayment(int $gymId, int $userId, array $data): int
    {
        $amount = (float) ($data['amount'] ?? 0);
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Payment amount must be greater than zero.");
        }

        $memberId = (int) ($data['member_id'] ?? 0);
        $membershipId = (int) ($data['membership_id'] ?? 0);

        // Verify membership belongs to member and tenant gym
        $membership = Database::fetchOne("
            SELECT * FROM `memberships` 
            WHERE `id` = :id AND `member_id` = :member_id AND `gym_id` = :gym_id LIMIT 1
        ", [
            ':id' => $membershipId,
            ':member_id' => $memberId,
            ':gym_id' => $gymId,
        ]);

        if (!$membership) {
            throw new \InvalidArgumentException("Invalid membership subscription for this member.");
        }

        $receiptNumber = !empty($data['receipt_number']) ? trim($data['receipt_number']) : self::generateReceiptNumber($gymId);
        $paymentDate = !empty($data['payment_date']) ? $data['payment_date'] : date('Y-m-d');
        $paymentMethod = $data['payment_method'] ?? 'cash';
        $referenceNumber = !empty($data['reference_number']) ? trim($data['reference_number']) : null;
        $notes = !empty($data['notes']) ? trim($data['notes']) : null;

        return Database::transaction(function (\PDO $pdo) use ($gymId, $membershipId, $memberId, $userId, $receiptNumber, $amount, $paymentMethod, $paymentDate, $referenceNumber, $notes) {
            $stmt = $pdo->prepare("
                INSERT INTO `payments` (
                    `gym_id`, `membership_id`, `member_id`, `user_id`, 
                    `receipt_number`, `amount`, `payment_method`, `payment_date`, 
                    `reference_number`, `notes`, `status`, `created_at`
                ) VALUES (
                    :gym_id, :membership_id, :member_id, :user_id, 
                    :receipt_number, :amount, :payment_method, :payment_date, 
                    :reference_number, :notes, 'paid', NOW()
                )
            ");

            $stmt->execute([
                ':gym_id' => $gymId,
                ':membership_id' => $membershipId,
                ':member_id' => $memberId,
                ':user_id' => $userId,
                ':receipt_number' => $receiptNumber,
                ':amount' => $amount,
                ':payment_method' => $paymentMethod,
                ':payment_date' => $paymentDate,
                ':reference_number' => $referenceNumber,
                ':notes' => $notes,
            ]);

            $paymentId = (int) $pdo->lastInsertId();

            // Insert audit log
            $stmtAudit = $pdo->prepare("
                INSERT INTO `audit_logs` (
                    `gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`
                ) VALUES (
                    :gym_id, :user_id, 'payment.created', 'payment', :entity_id, :details, NOW()
                )
            ");

            $details = json_encode([
                'receipt_number' => $receiptNumber,
                'amount' => $amount,
                'method' => $paymentMethod,
                'member_id' => $memberId,
                'membership_id' => $membershipId,
            ]);

            $stmtAudit->execute([
                ':gym_id' => $gymId,
                ':user_id' => $userId,
                ':entity_id' => $paymentId,
                ':details' => $details,
            ]);

            return $paymentId;
        });
    }

    /**
     * Void/Reverse an existing payment with mandatory reason and immutable audit log.
     * Voided payments are never counted towards revenue or member balances.
     */
    public static function voidPayment(int $gymId, int $paymentId, int $actorUserId, string $reason): bool
    {
        $reason = trim($reason);
        if (empty($reason)) {
            throw new \InvalidArgumentException("A valid reason is required to void a payment.");
        }

        $payment = Database::fetchOne("
            SELECT * FROM `payments` 
            WHERE `id` = :id AND `gym_id` = :gym_id LIMIT 1
        ", [
            ':id' => $paymentId,
            ':gym_id' => $gymId,
        ]);

        if (!$payment) {
            throw new \InvalidArgumentException("Payment not found or does not belong to this gym.");
        }

        if ($payment['status'] === 'voided') {
            throw new \InvalidArgumentException("This payment is already voided.");
        }

        return Database::transaction(function (\PDO $pdo) use ($gymId, $payment, $paymentId, $actorUserId, $reason) {
            // 1. Mark payment as voided
            $stmtUpdate = $pdo->prepare("
                UPDATE `payments` 
                SET `status` = 'voided', `updated_at` = NOW() 
                WHERE `id` = :id AND `gym_id` = :gym_id
            ");
            $stmtUpdate->execute([':id' => $paymentId, ':gym_id' => $gymId]);

            // 2. Insert reversal record
            $stmtRev = $pdo->prepare("
                INSERT INTO `payment_reversals` (
                    `gym_id`, `payment_id`, `authorized_by_user_id`, `amount`, `reason`, `reversed_at`
                ) VALUES (
                    :gym_id, :payment_id, :auth_user, :amount, :reason, NOW()
                )
            ");
            $stmtRev->execute([
                ':gym_id' => $gymId,
                ':payment_id' => $paymentId,
                ':auth_user' => $actorUserId,
                ':amount' => $payment['amount'],
                ':reason' => $reason,
            ]);

            // 3. Log into audit trail
            $stmtAudit = $pdo->prepare("
                INSERT INTO `audit_logs` (
                    `gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`
                ) VALUES (
                    :gym_id, :user_id, 'payment.voided', 'payment', :entity_id, :details, NOW()
                )
            ");
            $details = json_encode([
                'payment_id' => $paymentId,
                'receipt_number' => $payment['receipt_number'],
                'amount' => $payment['amount'],
                'reason' => $reason,
            ]);
            $stmtAudit->execute([
                ':gym_id' => $gymId,
                ':user_id' => $actorUserId,
                ':entity_id' => $paymentId,
                ':details' => $details,
            ]);

            return true;
        });
    }

    /**
     * Calculate outstanding balance for a member:
     * balance = agreed_price - SUM(valid_payments)
     */
    public static function getMemberBalance(int $gymId, int $memberId): float
    {
        $sql = "
            SELECT 
                GREATEST(0, COALESCE(SUM(m.agreed_price), 0) - COALESCE(SUM(p.paid_amount), 0)) as total_due
            FROM `memberships` m
            LEFT JOIN (
                SELECT membership_id, SUM(amount) as paid_amount
                FROM `payments`
                WHERE `status` = 'paid' AND `gym_id` = :g1
                GROUP BY membership_id
            ) p ON p.membership_id = m.id
            WHERE m.gym_id = :g2 AND m.member_id = :member_id
        ";

        $res = Database::fetchOne($sql, [
            ':g1' => $gymId,
            ':g2' => $gymId,
            ':member_id' => $memberId,
        ]);

        return max(0.0, (float) ($res['total_due'] ?? 0));
    }

    /**
     * Get paginated payment transactions with search and filters.
     */
    public static function getPayments(int $gymId, array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [':gym_id' => $gymId];
        $where = ["p.gym_id = :gym_id"];

        if (!empty($filters['method']) && $filters['method'] !== 'all') {
            $where[] = "p.payment_method = :method";
            $params[':method'] = $filters['method'];
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where[] = "p.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $where[] = "(p.receipt_number LIKE :s1 OR m.first_name LIKE :s2 OR m.last_name LIKE :s3 OR m.member_number LIKE :s4 OR p.reference_number LIKE :s5)";
            $params[':s1'] = $search;
            $params[':s2'] = $search;
            $params[':s3'] = $search;
            $params[':s4'] = $search;
            $params[':s5'] = $search;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT 
                p.*,
                m.member_number,
                m.first_name,
                m.last_name,
                m.phone,
                mem.plan_name_snapshot,
                u.name as recorded_by_name,
                pr.reason as void_reason,
                pr.reversed_at,
                rev_u.name as voided_by_name
            FROM `payments` p
            INNER JOIN `members` m ON m.id = p.member_id
            INNER JOIN `memberships` mem ON mem.id = p.membership_id
            LEFT JOIN `users` u ON u.id = p.user_id
            LEFT JOIN `payment_reversals` pr ON pr.payment_id = p.id
            LEFT JOIN `users` rev_u ON rev_u.id = pr.authorized_by_user_id
            WHERE {$whereClause}
            ORDER BY p.payment_date DESC, p.id DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";

        return Database::fetchAll($sql, $params);
    }

    /**
     * Count total payments for pagination.
     */
    public static function countPayments(int $gymId, array $filters = []): int
    {
        $params = [':gym_id' => $gymId];
        $where = ["p.gym_id = :gym_id"];

        if (!empty($filters['method']) && $filters['method'] !== 'all') {
            $where[] = "p.payment_method = :method";
            $params[':method'] = $filters['method'];
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where[] = "p.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $where[] = "(p.receipt_number LIKE :s1 OR m.first_name LIKE :s2 OR m.last_name LIKE :s3 OR m.member_number LIKE :s4 OR p.reference_number LIKE :s5)";
            $params[':s1'] = $search;
            $params[':s2'] = $search;
            $params[':s3'] = $search;
            $params[':s4'] = $search;
            $params[':s5'] = $search;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT COUNT(*) 
            FROM `payments` p
            INNER JOIN `members` m ON m.id = p.member_id
            WHERE {$whereClause}
        ";

        return (int) Database::query($sql, $params)->fetchColumn();
    }

    /**
     * Full receipt details for thermal or A4 view.
     */
    public static function getReceiptDetails(int $gymId, int $paymentId): ?array
    {
        $sql = "
            SELECT 
                p.*,
                m.member_number,
                m.first_name,
                m.last_name,
                m.phone,
                m.email,
                m.address as member_address,
                mem.plan_name_snapshot,
                mem.start_date as membership_start,
                mem.expiry_date as membership_expiry,
                mem.agreed_price as membership_price,
                u.name as cashier_name,
                g.name as gym_name,
                g.phone as gym_phone,
                g.email as gym_email,
                g.address as gym_address,
                g.currency_symbol,
                g.currency_code,
                pr.reason as void_reason,
                pr.reversed_at,
                rev_u.name as voided_by_name
            FROM `payments` p
            INNER JOIN `members` m ON m.id = p.member_id
            INNER JOIN `memberships` mem ON mem.id = p.membership_id
            INNER JOIN `gyms` g ON g.id = p.gym_id
            LEFT JOIN `users` u ON u.id = p.user_id
            LEFT JOIN `payment_reversals` pr ON pr.payment_id = p.id
            LEFT JOIN `users` rev_u ON rev_u.id = pr.authorized_by_user_id
            WHERE p.id = :id AND p.gym_id = :gym_id LIMIT 1
        ";

        return Database::fetchOne($sql, [
            ':id' => $paymentId,
            ':gym_id' => $gymId,
        ]);
    }
}
