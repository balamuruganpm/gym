<?php

declare(strict_types=1);

/**
 * Migration: Phase B — Roles Model and Member User Linking
 * 
 * Non-destructive, forward-only additive schema updates:
 * 1. Add `is_supreme_leader` TINYINT(1) to `users`.
 * 2. Extend `gym_users.role` ENUM to include 'trainer' and 'member'.
 * 3. Add `user_id` INT UNSIGNED NULL to `members` with UNIQUE index and foreign key.
 */

$baseDir = dirname(__DIR__, 2);
require_once $baseDir . '/vendor/autoload.php';

use App\Services\Config;
use App\Services\Database;

Config::load($baseDir);

$pdo = Database::getConnection();

echo "Running Phase B Additive Migrations on `gym-premium`...\n";

// 1. Add `is_supreme_leader` to `users` if not exists
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'is_supreme_leader'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("
            ALTER TABLE `users` 
            ADD COLUMN `is_supreme_leader` TINYINT(1) NOT NULL DEFAULT 0 AFTER `avatar_url`,
            ADD INDEX `idx_users_supreme_leader` (`is_supreme_leader`)
        ");
        echo "  [OK] Added `is_supreme_leader` column and index to `users`.\n";
    } else {
        echo "  [SKIP] Column `is_supreme_leader` already exists on `users`.\n";
    }
} catch (\PDOException $e) {
    echo "  [FAIL] Failed adding `is_supreme_leader` to `users`: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Extend `gym_users.role` enum to include 'trainer' and 'member'
try {
    $pdo->exec("
        ALTER TABLE `gym_users` 
        MODIFY COLUMN `role` ENUM('owner', 'staff', 'trainer', 'member') NOT NULL DEFAULT 'staff'
    ");
    echo "  [OK] Extended `gym_users.role` ENUM to ('owner', 'staff', 'trainer', 'member').\n";
} catch (\PDOException $e) {
    echo "  [FAIL] Failed extending `gym_users.role` ENUM: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Add `user_id` to `members` if not exists
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM `members` LIKE 'user_id'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("
            ALTER TABLE `members` 
            ADD COLUMN `user_id` INT UNSIGNED NULL DEFAULT NULL AFTER `gym_id`,
            ADD UNIQUE KEY `uk_member_user` (`user_id`),
            ADD CONSTRAINT `fk_members_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ");
        echo "  [OK] Added `user_id` column, unique index, and foreign key to `members`.\n";
    } else {
        echo "  [SKIP] Column `user_id` already exists on `members`.\n";
    }
} catch (\PDOException $e) {
    echo "  [FAIL] Failed adding `user_id` to `members`: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✅ Phase B migrations completed successfully!\n";
