<?php

declare(strict_types=1);

/**
 * Phase D Migration: Member Payment Requests Table
 * Forward-only additive migration for offline member payment requests and owner reviews.
 */

return function (\PDO $pdo): void {
    echo "  -> Running Phase D Migration: Member Payment Requests...\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `member_payment_requests` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `gym_id` INT UNSIGNED NOT NULL,
            `member_id` INT UNSIGNED NOT NULL,
            `membership_id` INT UNSIGNED NOT NULL,
            `amount` DECIMAL(12, 2) NOT NULL,
            `payment_method` ENUM('cash', 'upi', 'bank_transfer', 'other') NOT NULL DEFAULT 'upi',
            `payment_date` DATE NOT NULL,
            `reference_number` VARCHAR(100) NULL,
            `notes` TEXT NULL,
            `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
            `reviewed_by_user_id` INT UNSIGNED NULL,
            `reviewed_at` DATETIME NULL,
            `rejection_reason` VARCHAR(255) NULL,
            `payment_id` INT UNSIGNED NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NULL,
            INDEX `idx_mpr_gym_status` (`gym_id`, `status`),
            INDEX `idx_mpr_member` (`member_id`),
            INDEX `idx_mpr_membership` (`membership_id`),
            INDEX `idx_mpr_payment` (`payment_id`),
            CONSTRAINT `fk_mpr_gym` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_mpr_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_mpr_membership` FOREIGN KEY (`membership_id`) REFERENCES `memberships` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_mpr_reviewer` FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_mpr_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "     [OK] Table `member_payment_requests` verified.\n";
};
