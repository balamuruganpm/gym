<?php
/**
 * IronForge Gym - Master CLI Cron Job Runner
 * Execute via CLI or Server Task Scheduler:
 * php cron.php
 */

if (php_sapi_name() !== 'cli') {
    die("Access denied. CLI execution only.");
}

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/features.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/AuditLogger.php';
require_once __DIR__ . '/modules/automation/AutomationService.php';

echo "====================================================\n";
echo " IronForge Gym - Automation Engine CLI Cron Runner\n";
echo " Time: " . date('Y-m-d H:i:s T') . "\n";
echo "====================================================\n\n";

try {
    $automation = new AutomationService();
    echo "[*] Running automated membership expiry sync...\n";
    $expired = $automation->runExpiredSync();
    echo "    -> Expired memberships synced: {$expired}\n\n";

    echo "[*] Checking 7-day upcoming renewals...\n";
    $rem7 = $automation->runExpiringSoonCheck(7);
    echo "    -> 7-day renewal notifications dispatched: {$rem7}\n\n";

    echo "[*] Checking 1-day urgent renewal warnings...\n";
    $rem1 = $automation->runExpiringSoonCheck(1);
    echo "    -> 1-day urgent reminders dispatched: {$rem1}\n\n";

    echo "[*] Checking today's member birthdays...\n";
    $bday = $automation->runBirthdayGreetings();
    echo "    -> Birthday greetings sent: {$bday}\n\n";

    echo "[*] Checking for inactive member churn alerts (7+ days)...\n";
    $inact = $automation->runInactiveMemberAlerts(7);
    echo "    -> Inactive member alerts logged: {$inact}\n\n";

    echo "====================================================\n";
    echo " [✓] Cron Execution Finished Successfully!\n";
    echo "====================================================\n";
} catch (Exception $e) {
    echo "\n[ERROR] Cron execution failed: " . $e->getMessage() . "\n";
    exit(1);
}
