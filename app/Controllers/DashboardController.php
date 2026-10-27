<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Database;
use App\Services\MembershipService;
use App\Services\TenantContext;

class DashboardController extends Controller
{
    public function index(): string
    {
        $gymId = TenantContext::getGymId();
        $gym = current_gym() ?? ['name' => 'GymFlow'];

        // 1. Core KPIs
        $totalMembers = (int) Database::query("
            SELECT COUNT(*) FROM `members` WHERE `gym_id` = :gym_id AND `is_archived` = 0
        ", [':gym_id' => $gymId])->fetchColumn();

        $activeMembers = (int) Database::query("
            SELECT COUNT(DISTINCT member_id) FROM `memberships` 
            WHERE `gym_id` = :gym_id AND `status` = 'active' AND CURRENT_DATE() BETWEEN `start_date` AND `expiry_date`
        ", [':gym_id' => $gymId])->fetchColumn();

        $expiringSoon = (int) Database::query("
            SELECT COUNT(DISTINCT member_id) FROM `memberships` 
            WHERE `gym_id` = :gym_id AND `status` = 'active' 
              AND `expiry_date` BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY)
        ", [':gym_id' => $gymId])->fetchColumn();

        $expiredMembers = (int) Database::query("
            SELECT COUNT(DISTINCT member_id) FROM `memberships` 
            WHERE `gym_id` = :gym_id AND `expiry_date` < CURRENT_DATE()
        ", [':gym_id' => $gymId])->fetchColumn();

        $todayCollections = (float) (Database::query("
            SELECT COALESCE(SUM(amount), 0) FROM `payments` 
            WHERE `gym_id` = :gym_id AND `payment_date` = CURRENT_DATE() AND `status` = 'paid'
        ", [':gym_id' => $gymId])->fetchColumn() ?: 0);

        $monthCollections = (float) (Database::query("
            SELECT COALESCE(SUM(amount), 0) FROM `payments` 
            WHERE `gym_id` = :gym_id 
              AND MONTH(`payment_date`) = MONTH(CURRENT_DATE()) 
              AND YEAR(`payment_date`) = YEAR(CURRENT_DATE())
              AND `status` = 'paid'
        ", [':gym_id' => $gymId])->fetchColumn() ?: 0);

        $outstandingDues = (float) (Database::query("
            SELECT GREATEST(0, COALESCE(SUM(m.agreed_price), 0) - COALESCE(SUM(p.paid_amount), 0))
            FROM `memberships` m
            LEFT JOIN (
                SELECT membership_id, SUM(amount) as paid_amount
                FROM `payments` WHERE `status` = 'paid' AND `gym_id` = :g1
                GROUP BY membership_id
            ) p ON p.membership_id = m.id
            WHERE m.gym_id = :g2
        ", [':g1' => $gymId, ':g2' => $gymId])->fetchColumn() ?: 0);

        // 2. Recent Payments (Latest 6)
        $recentPayments = Database::fetchAll("
            SELECT 
                p.*,
                m.member_number,
                m.first_name,
                m.last_name,
                mem.plan_name_snapshot
            FROM `payments` p
            INNER JOIN `members` m ON m.id = p.member_id
            INNER JOIN `memberships` mem ON mem.id = p.membership_id
            WHERE p.gym_id = :gym_id
            ORDER BY p.id DESC
            LIMIT 6
        ", [':gym_id' => $gymId]);

        // 3. Expiring Soonest List (Top 5) with WhatsApp Links
        $expiringList = MembershipService::getExpiringMembers($gymId, 7);
        $expiringList = array_slice($expiringList, 0, 5);
        foreach ($expiringList as &$exp) {
            $exp['whatsapp_url'] = MembershipService::generateWhatsAppReminder($gym, [
                'first_name' => $exp['first_name'],
                'phone' => $exp['phone'],
            ], [
                'plan_name_snapshot' => $exp['plan_name_snapshot'],
                'expiry_date' => $exp['expiry_date'],
                'days_left' => $exp['days_left'],
            ]);
        }
        unset($exp);

        // 4. Monthly Revenue Trend (Last 6 Months) for Chart.js
        $trendLabels = [];
        $trendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthTime = strtotime("-{$i} months");
            $monthNum = (int) date('m', $monthTime);
            $yearNum = (int) date('Y', $monthTime);
            $label = date('M Y', $monthTime);

            $sum = (float) Database::query("
                SELECT COALESCE(SUM(amount), 0) FROM `payments`
                WHERE `gym_id` = :gym_id 
                  AND MONTH(`payment_date`) = :m 
                  AND YEAR(`payment_date`) = :y
                  AND `status` = 'paid'
            ", [
                ':gym_id' => $gymId,
                ':m' => $monthNum,
                ':y' => $yearNum,
            ])->fetchColumn();

            $trendLabels[] = $label;
            $trendData[] = $sum;
        }

        // 5. Popular Plans Breakdown
        $planBreakdown = Database::fetchAll("
            SELECT 
                plan_name_snapshot,
                COUNT(*) as subscriber_count
            FROM `memberships`
            WHERE `gym_id` = :gym_id
            GROUP BY `plan_name_snapshot`
            ORDER BY subscriber_count DESC
            LIMIT 5
        ", [':gym_id' => $gymId]);

        $planLabels = array_column($planBreakdown, 'plan_name_snapshot');
        $planCounts = array_map('intval', array_column($planBreakdown, 'subscriber_count'));

        return $this->view('dashboard.index', [
            'pageTitle' => 'Command Center — GymFlow',
            'gym' => $gym,
            'stats' => [
                'total_members' => $totalMembers,
                'active_members' => $activeMembers,
                'expiring_soon' => $expiringSoon,
                'expired_members' => $expiredMembers,
                'today_collections' => $todayCollections,
                'month_collections' => $monthCollections,
                'outstanding_dues' => $outstandingDues,
            ],
            'recentPayments' => $recentPayments,
            'expiringList' => $expiringList,
            'charts' => [
                'trendLabels' => $trendLabels,
                'trendData' => $trendData,
                'planLabels' => $planLabels,
                'planCounts' => $planCounts,
            ]
        ]);
    }
}
