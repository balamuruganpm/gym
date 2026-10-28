<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\Config;
use App\Services\Database;

Config::load(__DIR__ . '/..');

echo "🚀 Running Database Migrations on `gym-premium`...\n";

$pdo = Database::getConnection();

$tables = [
    'gyms' => "
        CREATE TABLE IF NOT EXISTS `gyms` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `slug` VARCHAR(80) NOT NULL UNIQUE,
            `name` VARCHAR(150) NOT NULL,
            `phone` VARCHAR(25) NULL,
            `email` VARCHAR(150) NULL,
            `address` TEXT NULL,
            `currency_symbol` VARCHAR(10) DEFAULT '₹',
            `currency_code` VARCHAR(10) DEFAULT 'INR',
            `timezone` VARCHAR(60) DEFAULT 'Asia/Kolkata',
            `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_gyms_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'users' => "
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(120) NOT NULL,
            `email` VARCHAR(180) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(25) NULL,
            `avatar_url` VARCHAR(255) NULL,
            `status` ENUM('active', 'inactive', 'locked') DEFAULT 'active',
            `failed_logins` TINYINT UNSIGNED DEFAULT 0,
            `last_login_at` DATETIME NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_users_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'gym_users' => "
        CREATE TABLE IF NOT EXISTS `gym_users` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `gym_id` INT UNSIGNED NOT NULL,
            `user_id` INT UNSIGNED NOT NULL,
            `role` ENUM('owner', 'staff') NOT NULL DEFAULT 'staff',
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_gym_user` (`gym_id`, `user_id`),
            CONSTRAINT `fk_gym_users_gym` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_gym_users_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'members' => "
        CREATE TABLE IF NOT EXISTS `members` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `gym_id` INT UNSIGNED NOT NULL,
            `member_number` VARCHAR(50) NOT NULL,
            `first_name` VARCHAR(80) NOT NULL,
            `last_name` VARCHAR(80) NULL,
            `phone` VARCHAR(25) NOT NULL,
            `email` VARCHAR(150) NULL,
            `gender` ENUM('male', 'female', 'other') NULL,
            `date_of_birth` DATE NULL,
            `address` TEXT NULL,
            `emergency_contact_name` VARCHAR(100) NULL,
            `emergency_contact_phone` VARCHAR(25) NULL,
            `photo_path` VARCHAR(255) NULL,
            `notes` TEXT NULL,
            `is_archived` TINYINT(1) DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_gym_member_number` (`gym_id`, `member_number`),
            INDEX `idx_members_gym_phone` (`gym_id`, `phone`),
            INDEX `idx_members_gym_archived` (`gym_id`, `is_archived`),
            CONSTRAINT `fk_members_gym` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'membership_plans' => "
        CREATE TABLE IF NOT EXISTS `membership_plans` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `gym_id` INT UNSIGNED NOT NULL,
            `name` VARCHAR(120) NOT NULL,
            `duration_type` ENUM('days', 'months') NOT NULL DEFAULT 'months',
            `duration_value` INT UNSIGNED NOT NULL,
            `price` DECIMAL(12,2) NOT NULL,
            `description` TEXT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_plans_gym_active` (`gym_id`, `is_active`),
            CONSTRAINT `fk_plans_gym` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'memberships' => "
        CREATE TABLE IF NOT EXISTS `memberships` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `gym_id` INT UNSIGNED NOT NULL,
            `member_id` INT UNSIGNED NOT NULL,
            `plan_id` INT UNSIGNED NULL,
            `plan_name_snapshot` VARCHAR(120) NOT NULL,
            `start_date` DATE NOT NULL,
            `expiry_date` DATE NOT NULL,
            `original_price` DECIMAL(12,2) NOT NULL,
            `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `agreed_price` DECIMAL(12,2) NOT NULL,
            `status` ENUM('active', 'expired', 'cancelled', 'renewed') NOT NULL DEFAULT 'active',
            `renewal_of_id` INT UNSIGNED NULL,
            `notes` TEXT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_memberships_gym_dates` (`gym_id`, `start_date`, `expiry_date`),
            INDEX `idx_memberships_status` (`gym_id`, `status`),
            INDEX `idx_memberships_member` (`member_id`),
            CONSTRAINT `fk_memberships_gym` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_memberships_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_memberships_plan` FOREIGN KEY (`plan_id`) REFERENCES `membership_plans` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_memberships_parent` FOREIGN KEY (`renewal_of_id`) REFERENCES `memberships` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'payments' => "
        CREATE TABLE IF NOT EXISTS `payments` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `gym_id` INT UNSIGNED NOT NULL,
            `membership_id` INT UNSIGNED NOT NULL,
            `member_id` INT UNSIGNED NOT NULL,
            `user_id` INT UNSIGNED NOT NULL,
            `receipt_number` VARCHAR(60) NOT NULL,
            `amount` DECIMAL(12,2) NOT NULL,
            `payment_method` ENUM('cash', 'upi', 'card', 'bank_transfer', 'other') NOT NULL DEFAULT 'cash',
            `payment_date` DATE NOT NULL,
            `reference_number` VARCHAR(100) NULL,
            `notes` TEXT NULL,
            `status` ENUM('paid', 'voided', 'refunded') NOT NULL DEFAULT 'paid',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_gym_receipt` (`gym_id`, `receipt_number`),
            INDEX `idx_payments_gym_date` (`gym_id`, `payment_date`),
            INDEX `idx_payments_membership` (`membership_id`),
            INDEX `idx_payments_status` (`status`),
            CONSTRAINT `fk_payments_gym` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_payments_membership` FOREIGN KEY (`membership_id`) REFERENCES `memberships` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_payments_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'payment_reversals' => "
        CREATE TABLE IF NOT EXISTS `payment_reversals` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `gym_id` INT UNSIGNED NOT NULL,
            `payment_id` INT UNSIGNED NOT NULL,
            `authorized_by_user_id` INT UNSIGNED NOT NULL,
            `amount` DECIMAL(12,2) NOT NULL,
            `reason` VARCHAR(255) NOT NULL,
            `reversed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_reversal_payment` (`payment_id`),
            CONSTRAINT `fk_reversals_gym` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_reversals_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_reversals_user` FOREIGN KEY (`authorized_by_user_id`) REFERENCES `users` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'audit_logs' => "
        CREATE TABLE IF NOT EXISTS `audit_logs` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `gym_id` INT UNSIGNED NOT NULL,
            `user_id` INT UNSIGNED NULL,
            `action` VARCHAR(80) NOT NULL,
            `entity_type` VARCHAR(60) NOT NULL,
            `entity_id` INT UNSIGNED NULL,
            `details` JSON NULL,
            `ip_address` VARCHAR(45) NULL,
            `user_agent` VARCHAR(255) NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_audit_gym_action` (`gym_id`, `action`),
            INDEX `idx_audit_created` (`created_at`),
            CONSTRAINT `fk_audit_gym` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'import_jobs' => "
        CREATE TABLE IF NOT EXISTS `import_jobs` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `gym_id` INT UNSIGNED NOT NULL,
            `user_id` INT UNSIGNED NOT NULL,
            `original_filename` VARCHAR(255) NOT NULL,
            `stored_filename` VARCHAR(255) NOT NULL,
            `total_rows` INT UNSIGNED DEFAULT 0,
            `imported_rows` INT UNSIGNED DEFAULT 0,
            `failed_rows` INT UNSIGNED DEFAULT 0,
            `error_summary` JSON NULL,
            `status` ENUM('pending', 'previewed', 'completed', 'failed') DEFAULT 'pending',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `completed_at` DATETIME NULL,
            CONSTRAINT `fk_import_gym` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_import_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'gym_settings' => "
        CREATE TABLE IF NOT EXISTS `gym_settings` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `gym_id` INT UNSIGNED NOT NULL,
            `setting_key` VARCHAR(100) NOT NULL,
            `setting_value` TEXT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_gym_setting` (`gym_id`, `setting_key`),
            CONSTRAINT `fk_settings_gym` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
];

foreach ($tables as $name => $ddl) {
    try {
        $pdo->exec($ddl);
        echo "  [OK] Table `{$name}` created or already exists.\n";
    } catch (\PDOException $e) {
        echo "  [FAIL] Error creating `{$name}`: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Run forward-only additive migrations
$migrationsDir = __DIR__ . '/migrations';
if (is_dir($migrationsDir)) {
    foreach (glob($migrationsDir . '/*.php') as $migrationFile) {
        $result = require $migrationFile;
        if (is_callable($result)) {
            $result($pdo);
        }
    }
}

echo "✅ All database migrations finished successfully on `gym-premium`!\n";
