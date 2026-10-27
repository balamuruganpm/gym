<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Database;
use App\Services\TenantContext;

class ReportController extends Controller
{
    public function index(): string
    {
        $gymId = TenantContext::getGymId();
        $tab = $_GET['tab'] ?? 'collections';

        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // Collections Report Data
        $collections = Database::fetchAll("
            SELECT 
                p.*,
                m.member_number,
                m.first_name,
                m.last_name,
                m.phone,
                mem.plan_name_snapshot
            FROM `payments` p
            INNER JOIN `members` m ON m.id = p.member_id
            INNER JOIN `memberships` mem ON mem.id = p.membership_id
            WHERE p.gym_id = :gym_id 
              AND p.payment_date BETWEEN :start AND :end
              AND p.status = 'paid'
            ORDER BY p.payment_date DESC, p.id DESC
        ", [
            ':gym_id' => $gymId,
            ':start' => $startDate,
            ':end' => $endDate,
        ]);

        $collectionsTotal = array_sum(array_column($collections, 'amount'));

        // Outstanding Dues Report Data
        $duesList = Database::fetchAll("
            SELECT 
                m.id as member_id,
                m.member_number,
                m.first_name,
                m.last_name,
                m.phone,
                m.email,
                mem.plan_name_snapshot,
                mem.agreed_price,
                COALESCE(p_sum.total_paid, 0) as total_paid,
                GREATEST(0, mem.agreed_price - COALESCE(p_sum.total_paid, 0)) as balance_due,
                mem.expiry_date
            FROM `members` m
            INNER JOIN (
                SELECT mem1.*
                FROM `memberships` mem1
                INNER JOIN (
                    SELECT member_id, MAX(id) as max_id
                    FROM `memberships`
                    WHERE `gym_id` = :g1
                    GROUP BY member_id
                ) mem2 ON mem1.id = mem2.max_id
            ) mem ON mem.member_id = m.id
            LEFT JOIN (
                SELECT membership_id, SUM(amount) as total_paid
                FROM `payments`
                WHERE `status` = 'paid' AND `gym_id` = :g2
                GROUP BY membership_id
            ) p_sum ON p_sum.membership_id = mem.id
            WHERE m.gym_id = :g3 AND m.is_archived = 0
            HAVING balance_due > 0
            ORDER BY balance_due DESC
        ", [
            ':g1' => $gymId,
            ':g2' => $gymId,
            ':g3' => $gymId,
        ]);

        $duesTotal = array_sum(array_column($duesList, 'balance_due'));

        // Plan Distribution Data
        $planDistribution = Database::fetchAll("
            SELECT 
                p.id,
                p.name,
                p.price,
                p.duration_value,
                p.duration_type,
                COUNT(m.id) as total_subscribers,
                COALESCE(SUM(pay.total_collected), 0) as total_revenue
            FROM `membership_plans` p
            LEFT JOIN `memberships` m ON m.plan_id = p.id AND m.gym_id = :g1
            LEFT JOIN (
                SELECT membership_id, SUM(amount) as total_collected
                FROM `payments`
                WHERE `status` = 'paid' AND `gym_id` = :g2
                GROUP BY membership_id
            ) pay ON pay.membership_id = m.id
            WHERE p.gym_id = :g3
            GROUP BY p.id
            ORDER BY total_subscribers DESC
        ", [
            ':g1' => $gymId,
            ':g2' => $gymId,
            ':g3' => $gymId,
        ]);

        return $this->view('reports.index', [
            'pageTitle' => 'Reports & Analytics — GymFlow',
            'tab' => $tab,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'collections' => $collections,
            'collectionsTotal' => $collectionsTotal,
            'duesList' => $duesList,
            'duesTotal' => $duesTotal,
            'planDistribution' => $planDistribution,
        ]);
    }

    public function exportCollections(): void
    {
        $gymId = TenantContext::getGymId();
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $rows = Database::fetchAll("
            SELECT 
                p.receipt_number,
                m.member_number,
                CONCAT(m.first_name, ' ', COALESCE(m.last_name, '')) as member_name,
                m.phone,
                mem.plan_name_snapshot,
                p.payment_method,
                COALESCE(p.reference_number, 'N/A') as reference_number,
                p.payment_date,
                p.amount,
                p.status
            FROM `payments` p
            INNER JOIN `members` m ON m.id = p.member_id
            INNER JOIN `memberships` mem ON mem.id = p.membership_id
            WHERE p.gym_id = :gym_id 
              AND p.payment_date BETWEEN :start AND :end
            ORDER BY p.payment_date DESC
        ", [
            ':gym_id' => $gymId,
            ':start' => $startDate,
            ':end' => $endDate,
        ]);

        $filename = "collections_report_{$startDate}_to_{$endDate}.csv";
        $headers = ['Receipt Number', 'Member #', 'Member Name', 'Phone', 'Plan', 'Method', 'Reference #', 'Date', 'Amount', 'Status'];

        $this->outputCsv($filename, $headers, $rows);
    }

    public function exportDues(): void
    {
        $gymId = TenantContext::getGymId();

        $rows = Database::fetchAll("
            SELECT 
                m.member_number,
                m.first_name,
                COALESCE(m.last_name, '') as last_name,
                m.phone,
                COALESCE(m.email, '') as email,
                mem.plan_name_snapshot,
                mem.agreed_price,
                COALESCE(p_sum.total_paid, 0) as total_paid,
                GREATEST(0, mem.agreed_price - COALESCE(p_sum.total_paid, 0)) as balance_due,
                mem.expiry_date
            FROM `members` m
            INNER JOIN (
                SELECT mem1.*
                FROM `memberships` mem1
                INNER JOIN (
                    SELECT member_id, MAX(id) as max_id
                    FROM `memberships`
                    WHERE `gym_id` = :g1
                    GROUP BY member_id
                ) mem2 ON mem1.id = mem2.max_id
            ) mem ON mem.member_id = m.id
            LEFT JOIN (
                SELECT membership_id, SUM(amount) as total_paid
                FROM `payments`
                WHERE `status` = 'paid' AND `gym_id` = :g2
                GROUP BY membership_id
            ) p_sum ON p_sum.membership_id = mem.id
            WHERE m.gym_id = :g3 AND m.is_archived = 0
            HAVING balance_due > 0
            ORDER BY balance_due DESC
        ", [
            ':g1' => $gymId,
            ':g2' => $gymId,
            ':g3' => $gymId,
        ]);

        $filename = "outstanding_dues_report_" . date('Y-m-d') . ".csv";
        $headers = ['Member #', 'First Name', 'Last Name', 'Phone', 'Email', 'Plan', 'Agreed Price', 'Total Paid', 'Balance Due', 'Plan Expiry'];

        $this->outputCsv($filename, $headers, $rows);
    }

    private function outputCsv(string $filename, array $headers, array $data): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);

        foreach ($data as $row) {
            fputcsv($output, array_values((array) $row));
        }

        fclose($output);
        exit;
    }
}
