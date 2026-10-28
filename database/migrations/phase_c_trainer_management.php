<?php

declare(strict_types=1);

/**
 * Phase C Migration: Trainer Management, Availability, Scheduling & Progress
 *
 * Additive, non-destructive migration creating:
 * 1. `trainer_member_assignments`
 * 2. `trainer_availabilities`
 * 3. `training_sessions`
 * 4. `training_progress_records`
 */

return function (\PDO $pdo): void {
    // 1. trainer_member_assignments
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `trainer_member_assignments` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `gym_id` INT UNSIGNED NOT NULL,
            `trainer_user_id` INT UNSIGNED NOT NULL,
            `member_id` INT UNSIGNED NOT NULL,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `assigned_by_user_id` INT UNSIGNED NOT NULL,
            `notes` TEXT NULL,
            `assigned_at` DATETIME NOT NULL,
            `ended_at` DATETIME NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NULL,
            KEY `idx_tma_gym` (`gym_id`),
            KEY `idx_tma_trainer` (`trainer_user_id`),
            KEY `idx_tma_member` (`member_id`),
            KEY `idx_tma_status` (`status`),
            CONSTRAINT `fk_tma_gym` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_tma_trainer` FOREIGN KEY (`trainer_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_tma_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_tma_assigned_by` FOREIGN KEY (`assigned_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. trainer_availabilities
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `trainer_availabilities` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `gym_id` INT UNSIGNED NOT NULL,
            `trainer_user_id` INT UNSIGNED NOT NULL,
            `type` ENUM('recurring', 'exception', 'unavailable') NOT NULL DEFAULT 'recurring',
            `day_of_week` TINYINT UNSIGNED NULL COMMENT '0=Sunday, 1=Monday... 6=Saturday',
            `specific_date` DATE NULL,
            `start_time` TIME NOT NULL,
            `end_time` TIME NOT NULL,
            `is_available` TINYINT(1) NOT NULL DEFAULT 1,
            `slot_duration_minutes` INT UNSIGNED NOT NULL DEFAULT 60,
            `notes` VARCHAR(255) NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NULL,
            KEY `idx_ta_gym` (`gym_id`),
            KEY `idx_ta_trainer` (`trainer_user_id`),
            KEY `idx_ta_type` (`type`),
            KEY `idx_ta_day` (`day_of_week`),
            KEY `idx_ta_date` (`specific_date`),
            CONSTRAINT `fk_ta_gym` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_ta_trainer` FOREIGN KEY (`trainer_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 3. training_sessions
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `training_sessions` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `gym_id` INT UNSIGNED NOT NULL,
            `trainer_user_id` INT UNSIGNED NOT NULL,
            `member_id` INT UNSIGNED NOT NULL,
            `session_date` DATE NOT NULL,
            `start_time` TIME NOT NULL,
            `end_time` TIME NOT NULL,
            `status` ENUM('scheduled', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'scheduled',
            `session_notes` TEXT NULL,
            `created_by_user_id` INT UNSIGNED NOT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NULL,
            KEY `idx_ts_gym` (`gym_id`),
            KEY `idx_ts_trainer` (`trainer_user_id`),
            KEY `idx_ts_member` (`member_id`),
            KEY `idx_ts_date` (`session_date`),
            KEY `idx_ts_status` (`status`),
            CONSTRAINT `fk_ts_gym` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_ts_trainer` FOREIGN KEY (`trainer_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_ts_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_ts_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 4. training_progress_records
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `training_progress_records` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `gym_id` INT UNSIGNED NOT NULL,
            `trainer_user_id` INT UNSIGNED NOT NULL,
            `member_id` INT UNSIGNED NOT NULL,
            `session_id` INT UNSIGNED NULL,
            `record_date` DATE NOT NULL,
            `workout_summary` TEXT NOT NULL,
            `trainer_feedback` TEXT NULL,
            `weight_kg` DECIMAL(5,2) NULL,
            `body_fat_percentage` DECIMAL(4,1) NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NULL,
            KEY `idx_tpr_gym` (`gym_id`),
            KEY `idx_tpr_trainer` (`trainer_user_id`),
            KEY `idx_tpr_member` (`member_id`),
            KEY `idx_tpr_date` (`record_date`),
            KEY `idx_tpr_session` (`session_id`),
            CONSTRAINT `fk_tpr_gym` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_tpr_trainer` FOREIGN KEY (`trainer_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_tpr_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_tpr_session` FOREIGN KEY (`session_id`) REFERENCES `training_sessions` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
};
