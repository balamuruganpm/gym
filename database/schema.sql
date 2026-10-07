-- ==========================================================
-- FitPulse Gym Management & Automation Platform Database Schema
-- Standard Universal Schema
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. BRANCHES
DROP TABLE IF EXISTS `branches`;
CREATE TABLE `branches` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(30) UNIQUE NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `address` TEXT NULL,
  `phone` VARCHAR(30) NULL,
  `email` VARCHAR(120) NULL,
  `opening_time` TIME DEFAULT '06:00:00',
  `closing_time` TIME DEFAULT '22:00:00',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. USERS
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `branch_id` INT UNSIGNED NULL,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(120) UNIQUE NOT NULL,
  `phone` VARCHAR(30) UNIQUE NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('superadmin', 'owner', 'manager', 'staff', 'trainer', 'member') NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_users_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. MEMBERS
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `branch_id` INT UNSIGNED NOT NULL,
  `member_code` VARCHAR(40) UNIQUE NOT NULL,
  `qr_token` VARCHAR(64) UNIQUE NOT NULL,
  `pin_code` VARCHAR(10) NULL,
  `date_of_birth` DATE NULL,
  `gender` ENUM('male', 'female', 'other') NOT NULL DEFAULT 'male',
  `blood_group` VARCHAR(10) NULL,
  `address` TEXT NULL,
  `emergency_contact_name` VARCHAR(100) NULL,
  `emergency_contact_phone` VARCHAR(30) NULL,
  `medical_conditions` TEXT NULL,
  `status` ENUM('active', 'expiring_soon', 'expired', 'frozen', 'cancelled') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_members_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_members_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. MEMBERSHIP PLANS
DROP TABLE IF EXISTS `membership_plans`;
CREATE TABLE `membership_plans` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `branch_id` INT UNSIGNED NULL,
  `name` VARCHAR(100) NOT NULL,
  `duration_days` INT UNSIGNED NOT NULL,
  `duration_type` ENUM('days', 'months', 'years') DEFAULT 'months',
  `price` DECIMAL(10,2) NOT NULL,
  `admission_fee` DECIMAL(10,2) DEFAULT 0.00,
  `description` TEXT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_plans_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. MEMBER SUBSCRIPTIONS
DROP TABLE IF EXISTS `member_subscriptions`;
CREATE TABLE `member_subscriptions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT UNSIGNED NOT NULL,
  `plan_id` INT UNSIGNED NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `discount` DECIMAL(10,2) DEFAULT 0.00,
  `status` ENUM('active', 'expiring_soon', 'expired', 'frozen', 'cancelled') DEFAULT 'active',
  `notes` VARCHAR(255) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_sub_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sub_plan` FOREIGN KEY (`plan_id`) REFERENCES `membership_plans` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. MEMBER FREEZES
DROP TABLE IF EXISTS `member_freezes`;
CREATE TABLE `member_freezes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT UNSIGNED NOT NULL,
  `subscription_id` INT UNSIGNED NOT NULL,
  `freeze_start_date` DATE NOT NULL,
  `freeze_end_date` DATE NOT NULL,
  `days_frozen` INT UNSIGNED NOT NULL,
  `reason` VARCHAR(255) NULL,
  `status` ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_freezes_sub` FOREIGN KEY (`subscription_id`) REFERENCES `member_subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. ATTENDANCE
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `check_in_time` DATETIME NOT NULL,
  `method` ENUM('qr', 'qr_scanner', 'kiosk', 'pin', 'pin_pad', 'member_app', 'staff_manual', 'phone', 'rfid_card', 'face_recognition') NOT NULL DEFAULT 'qr',
  `status` ENUM('present', 'late', 'rejected') DEFAULT 'present',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_attendance_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_attendance_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT,
  INDEX `idx_attendance_time` (`check_in_time`),
  INDEX `idx_attendance_member_time` (`member_id`, `check_in_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. PAYMENTS
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT UNSIGNED NOT NULL,
  `subscription_id` INT UNSIGNED NULL,
  `receipt_number` VARCHAR(50) UNIQUE NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('cash', 'upi', 'card', 'bank_transfer', 'cheque') NOT NULL DEFAULT 'cash',
  `transaction_id` VARCHAR(100) NULL,
  `status` ENUM('completed', 'pending', 'refunded', 'failed') DEFAULT 'completed',
  `notes` VARCHAR(255) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_payments_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payments_sub` FOREIGN KEY (`subscription_id`) REFERENCES `member_subscriptions` (`id`) ON DELETE SET NULL,
  INDEX `idx_payments_receipt` (`receipt_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. MEMBER PROGRESS & TRANSFORMATION
DROP TABLE IF EXISTS `member_progress`;
CREATE TABLE `member_progress` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT UNSIGNED NOT NULL,
  `checkpoint_type` VARCHAR(50) DEFAULT 'custom',
  `recorded_date` DATE NOT NULL,
  `weight_kg` DECIMAL(5,2) NULL,
  `chest_in` DECIMAL(5,2) NULL,
  `waist_in` DECIMAL(5,2) NULL,
  `arms_in` DECIMAL(5,2) NULL,
  `thighs_in` DECIMAL(5,2) NULL,
  `body_fat_pct` DECIMAL(4,2) NULL,
  `photo_front` VARCHAR(255) NULL,
  `photo_back` VARCHAR(255) NULL,
  `photo_side` VARCHAR(255) NULL,
  `privacy_level` ENUM('only_me', 'me_trainer', 'me_staff', 'community') DEFAULT 'me_staff',
  `notes` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_progress_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  INDEX `idx_progress_member` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. AUTOMATION RULES
DROP TABLE IF EXISTS `automation_rules`;
CREATE TABLE `automation_rules` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `trigger_event` VARCHAR(60) NOT NULL,
  `action_type` VARCHAR(60) NOT NULL,
  `message_template` TEXT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. AUDIT LOGS
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `branch_id` INT UNSIGNED NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(60) NOT NULL,
  `entity_id` INT UNSIGNED NULL,
  `details_json` TEXT NULL,
  `ip_address` VARCHAR(50) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_audit_action` (`action`),
  INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
