<?php
/**
 * Payment Service Engine
 * Manages financial transactions, receipt generation, dues tracking, and revenue analytics.
 */

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/AuditLogger.php';

class PaymentService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($filters = [], $limit = 50, $offset = 0) {
        return $this->getPayments($filters, $limit, $offset);
    }

    public function getUpcomingDues($branchId = null) {
        return $this->getOutstandingDues($branchId);
    }

    /**
     * Generate unique receipt number
     */
    public function generateReceiptNumber() {
        $year = date('Y');
        $prefix = "REC-{$year}-";
        $stmt = $this->db->prepare("SELECT receipt_number FROM payments WHERE receipt_number LIKE ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$prefix . '%']);
        $last = $stmt->fetchColumn();

        if ($last) {
            $seq = intval(substr($last, strlen($prefix))) + 1;
        } else {
            $seq = 1001;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Record a new payment
     */
    public function recordPayment($data, $recordedByUserId) {
        $memberId = intval($data['member_id']);
        $subscriptionId = !empty($data['subscription_id']) ? intval($data['subscription_id']) : null;
        $amount = floatval($data['amount']);
        $method = $data['payment_method']; // cash, upi, card, bank_transfer, cheque
        $txnId = !empty($data['transaction_id']) ? trim($data['transaction_id']) : null;
        $notes = !empty($data['notes']) ? trim($data['notes']) : null;
        $paymentDate = !empty($data['payment_date']) ? $data['payment_date'] : date('Y-m-d H:i:s');
        $receiptNumber = $this->generateReceiptNumber();

        $stmt = $this->db->prepare("
            INSERT INTO payments (member_id, subscription_id, receipt_number, amount, payment_method, transaction_id, status, notes, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'completed', ?, ?)
        ");
        $stmt->execute([
            $memberId,
            $subscriptionId,
            $receiptNumber,
            $amount,
            $method,
            $txnId,
            $notes,
            $paymentDate
        ]);
        $paymentId = $this->db->lastInsertId();

        // If subscription has pending dues, update it
        if ($subscriptionId) {
            $this->recalculateSubscriptionDues($subscriptionId);
        }

        AuditLogger::log('payment_recorded', 'payments', $paymentId, [
            'member_id' => $memberId,
            'receipt_number' => $receiptNumber,
            'amount' => $amount,
            'method' => $method
        ], $recordedByUserId);

        return [
            'payment_id' => $paymentId,
            'receipt_number' => $receiptNumber
        ];
    }

    /**
     * Recalculate subscription balance after a payment
     */
    public function recalculateSubscriptionDues($subscriptionId) {
        // Fetch plan price and discount from subscription
        $subStmt = $this->db->prepare("SELECT price, discount FROM member_subscriptions WHERE id = ?");
        $subStmt->execute([$subscriptionId]);
        $sub = $subStmt->fetch();

        if ($sub) {
            $netPayable = max(0, floatval($sub['price']) - floatval($sub['discount']));
            
            $payStmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE subscription_id = ? AND status = 'completed'");
            $payStmt->execute([$subscriptionId]);
            $totalPaid = floatval($payStmt->fetchColumn());

            $dueAmount = max(0, $netPayable - $totalPaid);
            // Can be used to track subscription status or notify if fully cleared
            return $dueAmount;
        }
        return 0;
    }

    /**
     * Get paginated payments list with optional branch & date filtering
     */
    public function getPayments($filters = [], $limit = 50, $offset = 0) {
        $sql = "
            SELECT p.*, m.member_code, u.name as member_name, u.phone as member_phone, u.email as member_email,
                   b.name as branch_name, pl.name as plan_name
            FROM payments p
            JOIN members m ON p.member_id = m.id
            JOIN users u ON m.user_id = u.id
            LEFT JOIN branches b ON m.branch_id = b.id
            LEFT JOIN member_subscriptions s ON p.subscription_id = s.id
            LEFT JOIN membership_plans pl ON s.plan_id = pl.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['branch_id'])) {
            $sql .= " AND m.branch_id = ?";
            $params[] = $filters['branch_id'];
        }

        if (!empty($filters['payment_method'])) {
            $sql .= " AND p.payment_method = ?";
            $params[] = $filters['payment_method'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(p.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(p.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $searchTerm = "%{$filters['search']}%";
            $sql .= " AND (u.name LIKE ? OR u.phone LIKE ? OR m.member_code LIKE ? OR p.receipt_number LIKE ? OR p.transaction_id LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY p.id DESC LIMIT " . intval($limit) . " OFFSET " . intval($offset);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get single payment receipt with full details
     */
    public function getPaymentReceipt($paymentId) {
        $stmt = $this->db->prepare("
            SELECT p.*, m.member_code, m.created_at as joined_date,
                   u.name as member_name, u.phone as member_phone, u.email as member_email,
                   b.name as branch_name, b.address as branch_address, b.phone as branch_phone, b.email as branch_email,
                   s.start_date, s.end_date, s.price as plan_original_price, s.discount as plan_discount,
                   pl.name as plan_name, pl.duration_days, pl.duration_type
            FROM payments p
            JOIN members m ON p.member_id = m.id
            JOIN users u ON m.user_id = u.id
            LEFT JOIN branches b ON m.branch_id = b.id
            LEFT JOIN member_subscriptions s ON p.subscription_id = s.id
            LEFT JOIN membership_plans pl ON s.plan_id = pl.id
            WHERE p.id = ?
        ");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch();

        if (!$payment) {
            return null;
        }

        // Calculate total paid for this subscription if applicable
        if ($payment['subscription_id']) {
            $totalPaidStmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE subscription_id = ? AND status = 'completed'");
            $totalPaidStmt->execute([$payment['subscription_id']]);
            $payment['total_subscription_paid'] = floatval($totalPaidStmt->fetchColumn());
            $netPayable = max(0, floatval($payment['plan_original_price']) - floatval($payment['plan_discount']));
            $payment['balance_due'] = max(0, $netPayable - $payment['total_subscription_paid']);
        } else {
            $payment['total_subscription_paid'] = floatval($payment['amount']);
            $payment['balance_due'] = 0;
        }

        return $payment;
    }

    /**
     * Get outstanding dues across all members
     */
    public function getOutstandingDues($branchId = null) {
        $sql = "
            SELECT m.id as member_id, m.member_code, u.name as member_name, u.phone as member_phone,
                   b.name as branch_name, s.id as subscription_id, s.start_date, s.end_date,
                   pl.name as plan_name, s.price, s.discount,
                   (s.price - s.discount) as net_payable,
                   COALESCE(SUM(p.amount), 0) as total_paid,
                   ((s.price - s.discount) - COALESCE(SUM(p.amount), 0)) as balance_due
            FROM member_subscriptions s
            JOIN members m ON s.member_id = m.id
            JOIN users u ON m.user_id = u.id
            LEFT JOIN branches b ON m.branch_id = b.id
            LEFT JOIN membership_plans pl ON s.plan_id = pl.id
            LEFT JOIN payments p ON s.id = p.subscription_id AND p.status = 'completed'
            WHERE s.status IN ('active', 'expired')
        ";
        $params = [];

        if ($branchId) {
            $sql .= " AND m.branch_id = ?";
            $params[] = $branchId;
        }

        $sql .= " GROUP BY s.id HAVING balance_due > 0 ORDER BY balance_due DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Financial summaries (today, this month, all time, by method)
     */
    public function getFinancialMetrics($branchId = null) {
        $params = [];
        $branchCondition = "";
        if ($branchId) {
            $branchCondition = " AND m.branch_id = ?";
            $params[] = $branchId;
        }

        // Today's Collection
        $todaySql = "
            SELECT COALESCE(SUM(p.amount), 0) as today_total, COUNT(p.id) as today_count
            FROM payments p
            JOIN members m ON p.member_id = m.id
            WHERE DATE(p.created_at) = CURDATE() AND p.status = 'completed' {$branchCondition}
        ";
        $stmt = $this->db->prepare($todaySql);
        $stmt->execute($params);
        $today = $stmt->fetch();

        // This Month's Collection
        $monthSql = "
            SELECT COALESCE(SUM(p.amount), 0) as month_total, COUNT(p.id) as month_count
            FROM payments p
            JOIN members m ON p.member_id = m.id
            WHERE YEAR(p.created_at) = YEAR(CURDATE()) AND MONTH(p.created_at) = MONTH(CURDATE()) AND p.status = 'completed' {$branchCondition}
        ";
        $stmt = $this->db->prepare($monthSql);
        $stmt->execute($params);
        $month = $stmt->fetch();

        // Breakdown by Payment Method (This Month)
        $methodsSql = "
            SELECT p.payment_method, COALESCE(SUM(p.amount), 0) as total_amount, COUNT(p.id) as count
            FROM payments p
            JOIN members m ON p.member_id = m.id
            WHERE YEAR(p.created_at) = YEAR(CURDATE()) AND MONTH(p.created_at) = MONTH(CURDATE()) AND p.status = 'completed' {$branchCondition}
            GROUP BY p.payment_method
        ";
        $stmt = $this->db->prepare($methodsSql);
        $stmt->execute($params);
        $methods = $stmt->fetchAll();

        // Total Pending Dues
        $dues = $this->getOutstandingDues($branchId);
        $totalDues = 0;
        foreach ($dues as $d) {
            $totalDues += floatval($d['balance_due']);
        }

        return [
            'today_revenue' => floatval($today['today_total']),
            'today_txns' => intval($today['today_count']),
            'month_revenue' => floatval($month['month_total']),
            'month_txns' => intval($month['month_count']),
            'total_pending_dues' => $totalDues,
            'pending_dues_count' => count($dues),
            'methods_breakdown' => $methods
        ];
    }
}
