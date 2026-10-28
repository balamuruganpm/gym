<?php

declare(strict_types=1);

namespace App\Services;

class PaymentRequestService
{
    /**
     * Supported offline payment methods.
     */
    public const SUPPORTED_METHODS = ['cash', 'upi', 'bank_transfer', 'other'];

    /**
     * Submit a new offline payment request by a member.
     * Invariant: Does not alter official member balance or revenue.
     */
    public static function submitRequest(int $gymId, int $memberId, array $data): int
    {
        $membershipId = (int) ($data['membership_id'] ?? 0);
        $amount = (float) ($data['amount'] ?? 0);
        $paymentMethod = trim((string) ($data['payment_method'] ?? 'upi'));
        $paymentDate = !empty($data['payment_date']) ? trim((string) $data['payment_date']) : date('Y-m-d');
        $referenceNumber = !empty($data['reference_number']) ? trim((string) $data['reference_number']) : null;
        $notes = !empty($data['notes']) ? trim((string) $data['notes']) : null;

        if ($amount <= 0) {
            throw new \InvalidArgumentException("Payment request amount must be greater than zero.");
        }

        if (!in_array($paymentMethod, self::SUPPORTED_METHODS, true)) {
            throw new \InvalidArgumentException("Invalid payment method. Supported methods: " . implode(', ', self::SUPPORTED_METHODS));
        }

        // Validate date format (YYYY-MM-DD)
        $d = \DateTime::createFromFormat('Y-m-d', $paymentDate);
        if (!$d || $d->format('Y-m-d') !== $paymentDate) {
            throw new \InvalidArgumentException("Invalid payment date format. Use YYYY-MM-DD.");
        }

        // Verify member belongs to gym
        $member = Database::fetchOne("
            SELECT id FROM `members` WHERE `id` = :id AND `gym_id` = :g LIMIT 1
        ", [':id' => $memberId, ':g' => $gymId]);

        if (!$member) {
            throw new \InvalidArgumentException("Member does not belong to this gym.");
        }

        // Verify membership belongs to member and gym
        $membership = Database::fetchOne("
            SELECT * FROM `memberships` WHERE `id` = :id AND `member_id` = :m AND `gym_id` = :g LIMIT 1
        ", [':id' => $membershipId, ':m' => $memberId, ':g' => $gymId]);

        if (!$membership) {
            throw new \InvalidArgumentException("Invalid membership selected for payment request.");
        }

        // Verify requested amount does not exceed remaining membership balance
        $paidSum = (float) (Database::fetchOne("
            SELECT COALESCE(SUM(amount), 0) as paid 
            FROM `payments` 
            WHERE `membership_id` = :membership_id AND `status` = 'paid' AND `gym_id` = :gym_id
        ", [':membership_id' => $membershipId, ':gym_id' => $gymId])['paid'] ?? 0.00);

        $remainingBalance = max(0.00, (float) $membership['agreed_price'] - $paidSum);

        if ($amount > ($remainingBalance + 0.01)) {
            $formattedBalance = number_format($remainingBalance, 2);
            throw new \InvalidArgumentException("Requested amount ({$amount}) exceeds the remaining balance ({$formattedBalance}) for this membership.");
        }

        return Database::transaction(function (\PDO $pdo) use ($gymId, $memberId, $membershipId, $amount, $paymentMethod, $paymentDate, $referenceNumber, $notes) {
            $stmt = $pdo->prepare("
                INSERT INTO `member_payment_requests` (
                    `gym_id`, `member_id`, `membership_id`, `amount`, `payment_method`,
                    `payment_date`, `reference_number`, `notes`, `status`, `created_at`
                ) VALUES (
                    :gym_id, :member_id, :membership_id, :amount, :payment_method,
                    :payment_date, :reference_number, :notes, 'pending', NOW()
                )
            ");

            $stmt->execute([
                ':gym_id' => $gymId,
                ':member_id' => $memberId,
                ':membership_id' => $membershipId,
                ':amount' => $amount,
                ':payment_method' => $paymentMethod,
                ':payment_date' => $paymentDate,
                ':reference_number' => $referenceNumber,
                ':notes' => $notes,
            ]);

            $requestId = (int) $pdo->lastInsertId();

            // Audit Trail
            $stmtAudit = $pdo->prepare("
                INSERT INTO `audit_logs` (
                    `gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`
                ) VALUES (
                    :gym_id, NULL, 'payment_request.submitted', 'payment_request', :entity_id, :details, NOW()
                )
            ");

            $stmtAudit->execute([
                ':gym_id' => $gymId,
                ':entity_id' => $requestId,
                ':details' => json_encode([
                    'member_id' => $memberId,
                    'membership_id' => $membershipId,
                    'amount' => $amount,
                    'method' => $paymentMethod,
                    'reference' => $referenceNumber,
                ]),
            ]);

            return $requestId;
        });
    }

    /**
     * Approve an offline payment request.
     * Invariants:
     * 1. Concurrency safe using row lock (`FOR UPDATE`).
     * 2. Prevents double-approvals.
     * 3. Validates amount against outstanding membership balance.
     * 4. Creates official payment record inside same atomic transaction.
     * 5. Links created payment_id to member_payment_requests.
     * 6. Audits approval.
     */
    public static function approveRequest(int $gymId, int $requestId, int $reviewerUserId): int
    {
        return Database::transaction(function (\PDO $pdo) use ($gymId, $requestId, $reviewerUserId) {
            // 1. Lock the payment request row to prevent race conditions & double-approval
            $stmtReq = $pdo->prepare("
                SELECT * FROM `member_payment_requests` 
                WHERE `id` = :id AND `gym_id` = :gym_id 
                FOR UPDATE
            ");
            $stmtReq->execute([':id' => $requestId, ':gym_id' => $gymId]);
            $request = $stmtReq->fetch(\PDO::FETCH_ASSOC);

            if (!$request) {
                throw new \InvalidArgumentException("Payment request not found or does not belong to this gym.");
            }

            if ($request['status'] !== 'pending') {
                throw new \InvalidArgumentException("This payment request has already been processed with status: " . $request['status']);
            }

            $membershipId = (int) $request['membership_id'];
            $memberId = (int) $request['member_id'];
            $amount = (float) $request['amount'];

            // 2. Lock and verify membership
            $stmtMem = $pdo->prepare("
                SELECT * FROM `memberships` 
                WHERE `id` = :id AND `gym_id` = :gym_id 
                FOR UPDATE
            ");
            $stmtMem->execute([':id' => $membershipId, ':gym_id' => $gymId]);
            $membership = $stmtMem->fetch(\PDO::FETCH_ASSOC);

            if (!$membership) {
                throw new \InvalidArgumentException("Associated membership subscription not found.");
            }

            // 3. Re-verify outstanding balance at moment of approval
            $stmtPaid = $pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as paid 
                FROM `payments` 
                WHERE `membership_id` = :membership_id AND `status` = 'paid' AND `gym_id` = :gym_id
            ");
            $stmtPaid->execute([':membership_id' => $membershipId, ':gym_id' => $gymId]);
            $paidSum = (float) ($stmtPaid->fetchColumn() ?? 0.00);

            $currentBalance = max(0.00, (float) $membership['agreed_price'] - $paidSum);

            if ($amount > ($currentBalance + 0.01)) {
                $formattedBal = number_format($currentBalance, 2);
                throw new \InvalidArgumentException("Cannot approve: Requested amount ({$amount}) exceeds current remaining balance ({$formattedBal}).");
            }

            // 4. Generate unique receipt number
            $receiptNumber = BillingService::generateReceiptNumber($gymId);
            $notes = "Approved from Offline Payment Request #{$requestId}. " . ($request['notes'] ?? '');

            // 5. Insert official payment record
            $stmtPay = $pdo->prepare("
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

            $stmtPay->execute([
                ':gym_id' => $gymId,
                ':membership_id' => $membershipId,
                ':member_id' => $memberId,
                ':user_id' => $reviewerUserId,
                ':receipt_number' => $receiptNumber,
                ':amount' => $amount,
                ':payment_method' => $request['payment_method'],
                ':payment_date' => $request['payment_date'],
                ':reference_number' => $request['reference_number'],
                ':notes' => trim($notes),
            ]);

            $paymentId = (int) $pdo->lastInsertId();

            // 6. Update payment request status to approved
            $stmtUpdate = $pdo->prepare("
                UPDATE `member_payment_requests`
                SET `status` = 'approved',
                    `reviewed_by_user_id` = :reviewer_id,
                    `reviewed_at` = NOW(),
                    `payment_id` = :payment_id,
                    `updated_at` = NOW()
                WHERE `id` = :id AND `gym_id` = :gym_id
            ");

            $stmtUpdate->execute([
                ':reviewer_id' => $reviewerUserId,
                ':payment_id' => $paymentId,
                ':id' => $requestId,
                ':gym_id' => $gymId,
            ]);

            // 7. Audit Log for Payment Creation
            $stmtAuditPay = $pdo->prepare("
                INSERT INTO `audit_logs` (
                    `gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`
                ) VALUES (
                    :gym_id, :user_id, 'payment.created', 'payment', :entity_id, :details, NOW()
                )
            ");

            $stmtAuditPay->execute([
                ':gym_id' => $gymId,
                ':user_id' => $reviewerUserId,
                ':entity_id' => $paymentId,
                ':details' => json_encode([
                    'receipt_number' => $receiptNumber,
                    'amount' => $amount,
                    'method' => $request['payment_method'],
                    'member_id' => $memberId,
                    'membership_id' => $membershipId,
                    'payment_request_id' => $requestId,
                ]),
            ]);

            // 8. Audit Log for Request Approval
            $stmtAuditReq = $pdo->prepare("
                INSERT INTO `audit_logs` (
                    `gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`
                ) VALUES (
                    :gym_id, :user_id, 'payment_request.approved', 'payment_request', :entity_id, :details, NOW()
                )
            ");

            $stmtAuditReq->execute([
                ':gym_id' => $gymId,
                ':user_id' => $reviewerUserId,
                ':entity_id' => $requestId,
                ':details' => json_encode([
                    'payment_id' => $paymentId,
                    'receipt_number' => $receiptNumber,
                    'amount' => $amount,
                ]),
            ]);

            return $paymentId;
        });
    }

    /**
     * Reject an offline payment request with a mandatory reason.
     */
    public static function rejectRequest(int $gymId, int $requestId, int $reviewerUserId, string $reason): bool
    {
        $reason = trim($reason);
        if (empty($reason)) {
            throw new \InvalidArgumentException("A rejection reason is required.");
        }

        return Database::transaction(function (\PDO $pdo) use ($gymId, $requestId, $reviewerUserId, $reason) {
            $stmtReq = $pdo->prepare("
                SELECT * FROM `member_payment_requests` 
                WHERE `id` = :id AND `gym_id` = :gym_id 
                FOR UPDATE
            ");
            $stmtReq->execute([':id' => $requestId, ':gym_id' => $gymId]);
            $request = $stmtReq->fetch(\PDO::FETCH_ASSOC);

            if (!$request) {
                throw new \InvalidArgumentException("Payment request not found or does not belong to this gym.");
            }

            if ($request['status'] !== 'pending') {
                throw new \InvalidArgumentException("This payment request has already been processed with status: " . $request['status']);
            }

            // Update to rejected
            $stmtUpdate = $pdo->prepare("
                UPDATE `member_payment_requests`
                SET `status` = 'rejected',
                    `reviewed_by_user_id` = :reviewer_id,
                    `reviewed_at` = NOW(),
                    `rejection_reason` = :reason,
                    `updated_at` = NOW()
                WHERE `id` = :id AND `gym_id` = :gym_id
            ");

            $stmtUpdate->execute([
                ':reviewer_id' => $reviewerUserId,
                ':reason' => $reason,
                ':id' => $requestId,
                ':gym_id' => $gymId,
            ]);

            // Audit Log
            $stmtAudit = $pdo->prepare("
                INSERT INTO `audit_logs` (
                    `gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`
                ) VALUES (
                    :gym_id, :user_id, 'payment_request.rejected', 'payment_request', :entity_id, :details, NOW()
                )
            ");

            $stmtAudit->execute([
                ':gym_id' => $gymId,
                ':user_id' => $reviewerUserId,
                ':entity_id' => $requestId,
                ':details' => json_encode([
                    'member_id' => $request['member_id'],
                    'amount' => $request['amount'],
                    'reason' => $reason,
                ]),
            ]);

            return true;
        });
    }

    /**
     * Get pending payment requests for the gym with member and plan details.
     */
    public static function getPendingRequests(int $gymId): array
    {
        return self::getAllRequests($gymId, 'pending');
    }

    /**
     * Get all payment requests with optional status filtering.
     */
    public static function getAllRequests(int $gymId, ?string $statusFilter = null, int $limit = 50, int $offset = 0): array
    {
        $params = [':gym_id' => $gymId];
        $where = ["r.gym_id = :gym_id"];

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'], true)) {
            $where[] = "r.status = :status";
            $params[':status'] = $statusFilter;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT 
                r.*,
                m.member_number,
                m.first_name,
                m.last_name,
                m.phone as member_phone,
                m.email as member_email,
                mem.plan_name_snapshot,
                mem.agreed_price,
                u.name as reviewer_name,
                p.receipt_number as approved_receipt_number,
                p.status as payment_status
            FROM `member_payment_requests` r
            INNER JOIN `members` m ON m.id = r.member_id
            INNER JOIN `memberships` mem ON mem.id = r.membership_id
            LEFT JOIN `users` u ON u.id = r.reviewed_by_user_id
            LEFT JOIN `payments` p ON p.id = r.payment_id
            WHERE {$whereClause}
            ORDER BY CASE WHEN r.status = 'pending' THEN 0 ELSE 1 END, r.id DESC
            LIMIT {$limit} OFFSET {$offset}
        ";

        return Database::fetchAll($sql, $params);
    }

    /**
     * Count payment requests.
     */
    public static function countRequests(int $gymId, ?string $statusFilter = null): int
    {
        $params = [':gym_id' => $gymId];
        $where = ["r.gym_id = :gym_id"];

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'], true)) {
            $where[] = "r.status = :status";
            $params[':status'] = $statusFilter;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT COUNT(*) FROM `member_payment_requests` r WHERE {$whereClause}";
        return (int) Database::query($sql, $params)->fetchColumn();
    }

    /**
     * Get single request details by ID.
     */
    public static function getRequestById(int $gymId, int $requestId): ?array
    {
        $sql = "
            SELECT 
                r.*,
                m.member_number,
                m.first_name,
                m.last_name,
                m.phone as member_phone,
                m.email as member_email,
                mem.plan_name_snapshot,
                mem.agreed_price,
                u.name as reviewer_name,
                p.receipt_number as approved_receipt_number,
                p.status as payment_status
            FROM `member_payment_requests` r
            INNER JOIN `members` m ON m.id = r.member_id
            INNER JOIN `memberships` mem ON mem.id = r.membership_id
            LEFT JOIN `users` u ON u.id = r.reviewed_by_user_id
            LEFT JOIN `payments` p ON p.id = r.payment_id
            WHERE r.id = :id AND r.gym_id = :gym_id
            LIMIT 1
        ";

        return Database::fetchOne($sql, [
            ':id' => $requestId,
            ':gym_id' => $gymId,
        ]);
    }

    /**
     * Get all payment requests submitted by a specific member.
     */
    public static function getMemberRequests(int $gymId, int $memberId): array
    {
        $sql = "
            SELECT 
                r.*,
                mem.plan_name_snapshot,
                mem.agreed_price,
                u.name as reviewer_name,
                p.receipt_number as approved_receipt_number,
                p.status as payment_status
            FROM `member_payment_requests` r
            INNER JOIN `memberships` mem ON mem.id = r.membership_id
            LEFT JOIN `users` u ON u.id = r.reviewed_by_user_id
            LEFT JOIN `payments` p ON p.id = r.payment_id
            WHERE r.member_id = :member_id AND r.gym_id = :gym_id
            ORDER BY r.id DESC
        ";

        return Database::fetchAll($sql, [
            ':member_id' => $memberId,
            ':gym_id' => $gymId,
        ]);
    }
}
