<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MembershipPlan;
use App\Models\Member;

class MembershipService
{
    /**
     * Calculate expiry date from start date and duration.
     */
    public static function calculateExpiry(string $startDate, int $durationValue, string $durationType): string
    {
        $time = strtotime($startDate);
        if ($durationType === 'months') {
            return date('Y-m-d', strtotime("+{$durationValue} months", $time));
        }
        return date('Y-m-d', strtotime("+{$durationValue} days", $time));
    }

    /**
     * Assign a plan to a member.
     */
    public static function assignPlan(int $gymId, int $memberId, int $planId, string $startDate, float $discount = 0.0): int
    {
        $plan = MembershipPlan::find($planId, $gymId);
        if (!$plan) {
            throw new \InvalidArgumentException("Membership plan not found for this gym.");
        }

        $expiryDate = self::calculateExpiry($startDate, (int) $plan['duration_value'], $plan['duration_type']);
        $originalPrice = (float) $plan['price'];
        $discountAmount = max(0.0, $discount);
        $agreedPrice = max(0.0, $originalPrice - $discountAmount);

        return Database::transaction(function (\PDO $pdo) use ($gymId, $memberId, $plan, $startDate, $expiryDate, $originalPrice, $discountAmount, $agreedPrice) {
            $stmt = $pdo->prepare("
                INSERT INTO `memberships` (
                    `gym_id`, `member_id`, `plan_id`, `plan_name_snapshot`, 
                    `start_date`, `expiry_date`, `original_price`, `discount_amount`, 
                    `agreed_price`, `status`, `created_at`
                ) VALUES (
                    :gym_id, :member_id, :plan_id, :plan_name, 
                    :start_date, :expiry_date, :orig_price, :discount, 
                    :agreed_price, 'active', NOW()
                )
            ");

            $stmt->execute([
                ':gym_id' => $gymId,
                ':member_id' => $memberId,
                ':plan_id' => $plan['id'],
                ':plan_name' => $plan['name'],
                ':start_date' => $startDate,
                ':expiry_date' => $expiryDate,
                ':orig_price' => $originalPrice,
                ':discount' => $discountAmount,
                ':agreed_price' => $agreedPrice,
            ]);

            return (int) $pdo->lastInsertId();
        });
    }

    /**
     * Smart Renewal:
     * If the member currently has an active plan that hasn't expired yet,
     * the renewal seamlessly chains starting from the day after current expiry.
     * If expired or no plan, it starts from today.
     */
    public static function renewPlan(int $gymId, int $memberId, int $planId, float $discount = 0.0): int
    {
        $latest = Database::fetchOne("
            SELECT * FROM `memberships` 
            WHERE `gym_id` = :gym_id AND `member_id` = :member_id AND `status` = 'active'
            ORDER BY `expiry_date` DESC LIMIT 1
        ", [
            ':gym_id' => $gymId,
            ':member_id' => $memberId,
        ]);

        $today = date('Y-m-d');
        if ($latest && strtotime($latest['expiry_date']) >= strtotime($today)) {
            // Early renewal chains from current expiry + 1 day
            $startDate = date('Y-m-d', strtotime('+1 day', strtotime($latest['expiry_date'])));
        } else {
            $startDate = $today;
        }

        return self::assignPlan($gymId, $memberId, $planId, $startDate, $discount);
    }

    /**
     * Fetch all members expiring within a specific window of days (e.g. 7 or 30 days).
     */
    public static function getExpiringMembers(int $gymId, int $daysWindow = 7): array
    {
        $sql = "
            SELECT 
                m.id as member_id,
                m.member_number,
                m.first_name,
                m.last_name,
                m.phone,
                m.email,
                mem.id as membership_id,
                mem.plan_name_snapshot,
                mem.start_date,
                mem.expiry_date,
                mem.agreed_price,
                DATEDIFF(mem.expiry_date, CURRENT_DATE()) as days_left,
                COALESCE(p_sum.total_paid, 0) as total_paid,
                GREATEST(0, mem.agreed_price - COALESCE(p_sum.total_paid, 0)) as balance_due
            FROM `members` m
            INNER JOIN (
                SELECT mem1.*
                FROM `memberships` mem1
                INNER JOIN (
                    SELECT member_id, MAX(id) as max_id
                    FROM `memberships`
                    WHERE `gym_id` = :g1 AND `status` = 'active'
                    GROUP BY member_id
                ) mem2 ON mem1.id = mem2.max_id
            ) mem ON mem.member_id = m.id
            LEFT JOIN (
                SELECT membership_id, SUM(amount) as total_paid
                FROM `payments`
                WHERE `status` = 'paid'
                GROUP BY membership_id
            ) p_sum ON p_sum.membership_id = mem.id
            WHERE m.gym_id = :g2 
              AND m.is_archived = 0
              AND mem.expiry_date BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL :days DAY)
            ORDER BY mem.expiry_date ASC
        ";

        return Database::fetchAll($sql, [
            ':g1' => $gymId,
            ':g2' => $gymId,
            ':days' => $daysWindow,
        ]);
    }

    /**
     * Fetch all members whose memberships have expired.
     */
    public static function getExpiredMembers(int $gymId): array
    {
        $sql = "
            SELECT 
                m.id as member_id,
                m.member_number,
                m.first_name,
                m.last_name,
                m.phone,
                m.email,
                mem.id as membership_id,
                mem.plan_name_snapshot,
                mem.start_date,
                mem.expiry_date,
                mem.agreed_price,
                DATEDIFF(CURRENT_DATE(), mem.expiry_date) as days_overdue,
                COALESCE(p_sum.total_paid, 0) as total_paid,
                GREATEST(0, mem.agreed_price - COALESCE(p_sum.total_paid, 0)) as balance_due
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
                WHERE `status` = 'paid'
                GROUP BY membership_id
            ) p_sum ON p_sum.membership_id = mem.id
            WHERE m.gym_id = :g2 
              AND m.is_archived = 0
              AND mem.expiry_date < CURRENT_DATE()
            ORDER BY mem.expiry_date DESC
        ";

        return Database::fetchAll($sql, [
            ':g1' => $gymId,
            ':g2' => $gymId,
        ]);
    }

    /**
     * Generate 1-click WhatsApp click-to-chat reminder URL.
     */
    public static function generateWhatsAppReminder(array $gym, array $member, array $membership): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $member['phone']);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone; // default country code
        }

        $gymName = $gym['name'] ?? 'GymFlow';
        $firstName = $member['first_name'];
        $plan = $membership['plan_name_snapshot'];
        $expiryDate = date('d M Y', strtotime($membership['expiry_date']));
        $daysLeft = (int) ($membership['days_left'] ?? 0);

        if ($daysLeft > 0) {
            $msg = "Hi {$firstName}! 👋\n\nThis is a friendly reminder from *{$gymName}* that your *{$plan}* membership is expiring in *{$daysLeft} days* (on {$expiryDate}).\n\nRenew early to maintain your uninterrupted workout sessions and training streaks! 💪\n\nFeel free to reply here to renew.";
        } elseif ($daysLeft === 0) {
            $msg = "Hi {$firstName}! 👋\n\nYour *{$plan}* membership at *{$gymName}* expires *TODAY* ({$expiryDate}).\n\nPlease stop by the front desk or reply to this message to renew your membership.\n\nKeep crushing your fitness goals! 🔥";
        } else {
            $daysOverdue = abs((int) ($membership['days_overdue'] ?? abs($daysLeft)));
            $msg = "Hi {$firstName}! 👋\n\nWe miss having you at *{$gymName}*! Your *{$plan}* membership expired on {$expiryDate} ({$daysOverdue} days ago).\n\nRenew your plan today and let's get back on track! Reply to renew or ask any questions.";
        }

        return "https://wa.me/{$cleanPhone}?text=" . rawurlencode($msg);
    }
}
