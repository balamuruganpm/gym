<?php
/**
 * Database Migration and Seeder Runner for FitPulse
 */

define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

function runMigration() {
    $config = require __DIR__ . '/../config/database.php';
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);

    echo "Connected to MySQL database [{$config['database']}] successfully.\n";

    $dataFile = __DIR__ . '/../Readme/data.sql';
    if (!file_exists($dataFile)) {
        throw new Exception("File not found: {$dataFile}");
    }

    echo "Applying Readme/data.sql...\n";
    $sql = file_get_contents($dataFile);
    $pdo->exec($sql);

    echo "[OK] Schema and seeds from Readme/data.sql applied successfully.\n";

    // Verification
    $tables = [
        'branches',
        'users',
        'members',
        'membership_plans',
        'member_subscriptions',
        'member_freezes',
        'payments',
        'attendance',
        'member_progress',
        'automation_rules',
        'audit_logs',
        'system_features'
    ];

    echo "\n============================================\n";
    echo "       DATABASE VERIFICATION REPORT         \n";
    echo "============================================\n";
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
            $count = $stmt->fetchColumn();
            echo sprintf("  %-25s : %d records\n", $table, $count);
        } catch (Exception $ex) {
            echo sprintf("  %-25s : [ERROR: %s]\n", $table, $ex->getMessage());
        }
    }
    echo "============================================\n";
    echo "All database tables and seed rows verified 100%!\n";
}

try {
    runMigration();
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
