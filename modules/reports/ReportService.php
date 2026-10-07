<?php
/**
 * Report & Analytics Service Engine
 * Provides gym health statistics, attendance trends, revenue analytics, churn risk alerts, and system diagnostics.
 */

require_once __DIR__ . '/../../core/Database.php';

class ReportService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getExecutiveSummary($branchId = null) {
        $pulse = $this->getTodayPulse($branchId);
        $mBranchCondition = $branchId ? " AND m.branch_id = " . intval($branchId) : "";
        $monthStmt = $this->db->query("
            SELECT COALESCE(SUM(p.amount), 0) 
            FROM payments p 
            JOIN members m ON p.member_id = m.id 
            WHERE DATE_FORMAT(p.created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') AND p.status = 'completed' {$mBranchCondition}
        ");
        $pulse['revenue_this_month'] = floatval($monthStmt->fetchColumn());
        return $pulse;
    }

    public function getRevenueTrend($months = 6, $branchId = null) {
        return $this->getMonthlyRevenueTrend($branchId);
    }

    /**
     * "Today at a Glance" Pulse Metrics
     */
    public function getTodayPulse($branchId = null) {
        $branchCondition = $branchId ? " AND branch_id = " . intval($branchId) : "";
        $mBranchCondition = $branchId ? " AND m.branch_id = " . intval($branchId) : "";

        // Total Active Members
        $stmt = $this->db->query("SELECT COUNT(*) FROM members WHERE status = 'active' {$branchCondition}");
        $activeMembers = intval($stmt->fetchColumn());

        // Total Members in gym
        $stmt = $this->db->query("SELECT COUNT(*) FROM members WHERE status != 'cancelled' {$branchCondition}");
        $totalMembers = intval($stmt->fetchColumn());

        // Today's Checkins
        $stmt = $this->db->query("
            SELECT COUNT(DISTINCT a.member_id) 
            FROM attendance a 
            JOIN members m ON a.member_id = m.id
            WHERE DATE(a.check_in_time) = CURDATE() {$mBranchCondition}
        ");
        $todayCheckins = intval($stmt->fetchColumn());

        // Today's Revenue
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(p.amount), 0) 
            FROM payments p 
            JOIN members m ON p.member_id = m.id
            WHERE DATE(p.created_at) = CURDATE() AND p.status = 'completed' {$mBranchCondition}
        ");
        $todayRevenue = floatval($stmt->fetchColumn());

        // Expiring in next 7 days
        $stmt = $this->db->query("
            SELECT COUNT(*) 
            FROM member_subscriptions s
            JOIN members m ON s.member_id = m.id
            WHERE s.status = 'active' 
              AND s.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
              {$mBranchCondition}
        ");
        $expiring7Days = intval($stmt->fetchColumn());

        // Expired count
        $stmt = $this->db->query("SELECT COUNT(*) FROM members WHERE status = 'expired' {$branchCondition}");
        $expiredMembers = intval($stmt->fetchColumn());

        return [
            'active_members' => $activeMembers,
            'total_members' => $totalMembers,
            'today_checkins' => $todayCheckins,
            'today_revenue' => $todayRevenue,
            'expiring_soon' => $expiring7Days,
            'expired_members' => $expiredMembers
        ];
    }

    /**
     * Inactive Members (Active subscription, but no attendance in past 7+ days)
     */
    public function getInactiveMembers($branchId = null, $days = 7, $limit = 10) {
        $branchCondition = $branchId ? " AND m.branch_id = " . intval($branchId) : "";

        $sql = "
            SELECT m.id, m.member_code, u.name, u.phone, u.email, b.name as branch_name, MAX(s.end_date) as end_date,
                   MAX(a.check_in_time) as last_attendance,
                   DATEDIFF(CURDATE(), MAX(a.check_in_time)) as days_inactive
            FROM members m
            JOIN users u ON m.user_id = u.id
            LEFT JOIN branches b ON m.branch_id = b.id
            LEFT JOIN member_subscriptions s ON m.id = s.member_id AND s.status = 'active'
            LEFT JOIN attendance a ON m.id = a.member_id
            WHERE m.status = 'active' {$branchCondition}
            GROUP BY m.id, m.member_code, u.name, u.phone, u.email, b.name
            HAVING (last_attendance IS NULL OR days_inactive >= ?)
            ORDER BY days_inactive DESC, m.id DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, intval($days), PDO::PARAM_INT);
        $stmt->bindValue(2, intval($limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * 7-Day Attendance Trend
     */
    public function getAttendanceTrend($branchId = null, $days = 7) {
        $branchCondition = $branchId ? " AND m.branch_id = " . intval($branchId) : "";
        $trend = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dayName = date('D, d M', strtotime($date));

            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT a.member_id)
                FROM attendance a
                JOIN members m ON a.member_id = m.id
                WHERE DATE(a.check_in_time) = ? {$branchCondition}
            ");
            $stmt->execute([$date]);
            $count = intval($stmt->fetchColumn());

            $trend[] = [
                'date' => $date,
                'label' => $dayName,
                'count' => $count
            ];
        }

        return $trend;
    }

    /**
     * 6-Month Revenue Trend
     */
    public function getMonthlyRevenueTrend($branchId = null) {
        $branchCondition = $branchId ? " AND m.branch_id = " . intval($branchId) : "";
        $trend = [];

        for ($i = 5; $i >= 0; $i--) {
            $yearMonth = date('Y-m', strtotime("-{$i} months"));
            $label = date('M Y', strtotime("-{$i} months"));

            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(p.amount), 0)
                FROM payments p
                JOIN members m ON p.member_id = m.id
                WHERE DATE_FORMAT(p.created_at, '%Y-%m') = ? 
                  AND p.status = 'completed' {$branchCondition}
            ");
            $stmt->execute([$yearMonth]);
            $amount = floatval($stmt->fetchColumn());

            $trend[] = [
                'month' => $yearMonth,
                'label' => $label,
                'amount' => $amount
            ];
        }

        return $trend;
    }

    /**
     * Plan Popularity Breakdown
     */
    public function getPlanDistribution($branchId = null) {
        $branchCondition = $branchId ? " AND m.branch_id = " . intval($branchId) : "";
        $sql = "
            SELECT pl.name as plan_name, COUNT(s.id) as subscriber_count
            FROM member_subscriptions s
            JOIN membership_plans pl ON s.plan_id = pl.id
            JOIN members m ON s.member_id = m.id
            WHERE s.status = 'active' {$branchCondition}
            GROUP BY pl.id, pl.name
            ORDER BY subscriber_count DESC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Today's Recent Check-ins
     */
    public function getRecentCheckins($branchId = null, $limit = 6) {
        $branchCondition = $branchId ? " AND m.branch_id = " . intval($branchId) : "";
        $sql = "
            SELECT a.id, a.check_in_time, a.status, a.method,
                   m.id as member_id, m.member_code, u.name, u.phone,
                   b.name as branch_name
            FROM attendance a
            JOIN members m ON a.member_id = m.id
            JOIN users u ON m.user_id = u.id
            LEFT JOIN branches b ON m.branch_id = b.id
            WHERE DATE(a.check_in_time) = CURDATE() {$branchCondition}
            ORDER BY a.check_in_time DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, intval($limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Upcoming Renewals (Within next 7-14 days)
     */
    public function getUpcomingRenewals($branchId = null, $limit = 5) {
        $branchCondition = $branchId ? " AND m.branch_id = " . intval($branchId) : "";
        $sql = "
            SELECT m.id, m.member_code, u.name, u.phone,
                   s.end_date, DATEDIFF(s.end_date, CURDATE()) as days_left,
                   pl.name as plan_name, pl.price
            FROM member_subscriptions s
            JOIN members m ON s.member_id = m.id
            JOIN users u ON m.user_id = u.id
            JOIN membership_plans pl ON s.plan_id = pl.id
            WHERE s.status = 'active'
              AND s.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
              {$branchCondition}
            ORDER BY s.end_date ASC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, intval($limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Recent Completed Payments Ledger
     */
    public function getRecentPayments($branchId = null, $limit = 5) {
        $branchCondition = $branchId ? " AND m.branch_id = " . intval($branchId) : "";
        $sql = "
            SELECT p.id, p.amount, p.payment_method, p.status, p.created_at, p.receipt_number,
                   m.id as member_id, m.member_code, u.name
            FROM payments p
            JOIN members m ON p.member_id = m.id
            JOIN users u ON m.user_id = u.id
            WHERE 1=1 {$branchCondition}
            ORDER BY p.created_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, intval($limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Today's Hourly Traffic Distribution (for Desk/Dashboard traffic bars)
     */
    public function getHourlyTraffic($branchId = null) {
        $branchCondition = $branchId ? " AND m.branch_id = " . intval($branchId) : "";
        $hours = [6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22];
        $result = [];

        foreach ($hours as $h) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM attendance a
                JOIN members m ON a.member_id = m.id
                WHERE DATE(a.check_in_time) = CURDATE()
                  AND HOUR(a.check_in_time) = ?
                  {$branchCondition}
            ");
            $stmt->execute([$h]);
            $count = intval($stmt->fetchColumn());
            $label = ($h <= 12 ? $h : $h - 12) . ($h < 12 ? 'am' : 'pm');
            $result[] = ['hour' => $h, 'label' => $label, 'count' => $count];
        }
        return $result;
    }

    /**
     * System Diagnostics & Server Health Check
     */
    public function getSystemDiagnostics() {
        $dbVer = $this->db->query("SELECT VERSION()")->fetchColumn();
        $dbName = $this->db->query("SELECT DATABASE()")->fetchColumn();
        
        $uploadsDir = __DIR__ . '/../../uploads';
        $isUploadsWritable = is_dir($uploadsDir) && is_writable($uploadsDir);

        $diskFree = function_exists('disk_free_space') ? round(@disk_free_space('.') / (1024 * 1024 * 1024), 2) : 'N/A';
        $diskTotal = function_exists('disk_total_space') ? round(@disk_total_space('.') / (1024 * 1024 * 1024), 2) : 'N/A';

        return [
            'php_version' => PHP_VERSION,
            'mysql_version' => $dbVer,
            'database_name' => $dbName,
            'pdo_driver' => $this->db->getAttribute(PDO::ATTR_DRIVER_NAME),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Local CLI',
            'memory_limit' => ini_get('memory_limit'),
            'memory_usage' => round(memory_get_usage(true) / (1024 * 1024), 2) . ' MB',
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'uploads_writable' => $isUploadsWritable,
            'disk_free_gb' => $diskFree,
            'disk_total_gb' => $diskTotal,
            'session_status' => (session_status() === PHP_SESSION_ACTIVE) ? 'Active' : 'Inactive',
            'server_time' => date('Y-m-d H:i:s T')
        ];
    }
}
