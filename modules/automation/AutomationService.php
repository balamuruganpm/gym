<?php
/**
 * Automation Service Engine
 * Manages automated triggers, expiry sync, birthday greetings, churn warnings, and scheduled cron jobs.
 */

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/AuditLogger.php';
require_once __DIR__ . '/../notifications/NotificationService.php';

class AutomationService {
    private $db;
    private $notificationService;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->notificationService = new NotificationService();
    }

    public static function runScheduledTasks() {
        $inst = new self();
        $res = $inst->runScheduledCron();
        $res['seven_day_reminders'] = $res['reminders_7d'] ?? 0;
        return $res;
    }

    public function getAll() {
        return $this->getRules();
    }

    /**
     * Get all automation rules
     */
    public function getRules() {
        $stmt = $this->db->query("SELECT * FROM automation_rules ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    /**
     * Toggle active status of a rule
     */
    public function toggleRule($ruleId, $status) {
        $stmt = $this->db->prepare("UPDATE automation_rules SET is_active = ? WHERE id = ?");
        $stmt->execute([$status ? 1 : 0, $ruleId]);
        
        AuditLogger::log('automation_rule_toggled', 'automation_rules', $ruleId, ['is_active' => $status]);
        return true;
    }

    /**
     * Sync Expired Memberships
     */
    public function runExpiredSync() {
        $count = 0;
        // 1. Mark subscriptions as expired
        $stmt = $this->db->prepare("
            UPDATE member_subscriptions 
            SET status = 'expired' 
            WHERE status = 'active' AND end_date < CURDATE()
        ");
        $stmt->execute();
        $count += $stmt->rowCount();

        // 2. Mark members as expired if they have no active subscription
        $stmt2 = $this->db->prepare("
            UPDATE members m
            SET status = 'expired'
            WHERE m.status = 'active' 
              AND NOT EXISTS (
                  SELECT 1 FROM member_subscriptions s 
                  WHERE s.member_id = m.id AND s.status = 'active' AND s.end_date >= CURDATE()
              )
        ");
        $stmt2->execute();

        AuditLogger::log('cron_expired_sync', 'automation', null, ['expired_subscriptions_updated' => $count]);
        return $count;
    }

    /**
     * Check for members expiring in $days days and trigger reminders
     */
    public function runExpiringSoonCheck($days = 7) {
        $targetDate = date('Y-m-d', strtotime("+{$days} days"));

        $stmt = $this->db->prepare("
            SELECT m.id as member_id, m.member_code, u.name, u.phone, u.email, pl.name as plan_name, s.end_date
            FROM member_subscriptions s
            JOIN members m ON s.member_id = m.id
            JOIN users u ON m.user_id = u.id
            JOIN membership_plans pl ON s.plan_id = pl.id
            WHERE s.status = 'active' AND s.end_date = ?
        ");
        $stmt->execute([$targetDate]);
        $expiring = $stmt->fetchAll();

        foreach ($expiring as $mem) {
            $msg = "Hi {$mem['name']}, your {$mem['plan_name']} plan at IronForge Gym expires in {$days} days (on " . date('d M Y', strtotime($mem['end_date'])) . "). Renew early to avoid interruption in gym workouts!";
            $this->notificationService->dispatch('system', $mem, "Membership Expiring in {$days} Days", $msg, [
                'type' => 'renewal_reminder',
                'days' => $days
            ]);
        }

        return count($expiring);
    }

    /**
     * Check for members having a birthday today
     */
    public function runBirthdayGreetings() {
        $todayMonthDay = date('m-d');

        $stmt = $this->db->prepare("
            SELECT m.id, m.member_code, u.name, u.phone, u.email
            FROM members m
            JOIN users u ON m.user_id = u.id
            WHERE DATE_FORMAT(m.date_of_birth, '%m-%d') = ? AND m.status != 'cancelled'
        ");
        $stmt->execute([$todayMonthDay]);
        $birthdayMembers = $stmt->fetchAll();

        foreach ($birthdayMembers as $mem) {
            $msg = "🎉 Happy Birthday {$mem['name']}! Wishing you a strong, healthy and powerful year ahead from all of us at IronForge Gym! 🎂💪";
            $this->notificationService->dispatch('system', $mem, "Happy Birthday from IronForge Gym!", $msg, [
                'type' => 'birthday_greeting'
            ]);
        }

        return count($birthdayMembers);
    }

    /**
     * Check for inactive members (no attendance in 7+ days)
     */
    public function runInactiveMemberAlerts($days = 7) {
        $sql = "
            SELECT m.id as member_id, u.name, u.phone, u.email, MAX(a.check_in_time) as last_seen,
                   DATEDIFF(CURDATE(), MAX(a.check_in_time)) as days_inactive
            FROM members m
            JOIN users u ON m.user_id = u.id
            LEFT JOIN attendance a ON m.id = a.member_id
            WHERE m.status = 'active'
            GROUP BY m.id
            HAVING (last_seen IS NULL OR days_inactive >= ?)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days]);
        $inactives = $stmt->fetchAll();

        foreach ($inactives as $mem) {
            $msg = "Hey {$mem['name']}! We noticed you haven't visited IronForge Gym in the last {$days} days. Don't lose your gains! Come in today for an energizing workout!";
            $this->notificationService->dispatch('system', $mem, "We miss you at the gym!", $msg, [
                'type' => 'churn_retention_alert',
                'inactive_days' => $mem['days_inactive']
            ]);
        }

        return count($inactives);
    }

    /**
     * Run all scheduled automations (Master Cron Runner)
     */
    public function runScheduledCron() {
        $summary = [
            'timestamp' => date('Y-m-d H:i:s'),
            'expired_synced' => $this->runExpiredSync(),
            'reminders_7d' => $this->runExpiringSoonCheck(7),
            'reminders_1d' => $this->runExpiringSoonCheck(1),
            'birthdays' => $this->runBirthdayGreetings(),
            'inactive_alerts' => $this->runInactiveMemberAlerts(7)
        ];

        AuditLogger::log('cron_executed_successfully', 'automation', null, $summary);
        return $summary;
    }
}
