-- ==========================================================
-- FitPulse Gym Management & Automation Platform Database
-- Complete SQL Schema & Seed Data
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------
-- 1. TABLE: branches
-- ----------------------------------------------------------
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

-- ----------------------------------------------------------
-- 2. TABLE: users
-- ----------------------------------------------------------
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

-- ----------------------------------------------------------
-- 3. TABLE: members
-- ----------------------------------------------------------
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

-- ----------------------------------------------------------
-- 4. TABLE: membership_plans
-- ----------------------------------------------------------
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

-- ----------------------------------------------------------
-- 5. TABLE: member_subscriptions
-- ----------------------------------------------------------
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

-- ----------------------------------------------------------
-- 6. TABLE: member_freezes
-- ----------------------------------------------------------
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

-- ----------------------------------------------------------
-- 7. TABLE: attendance
-- ----------------------------------------------------------
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

-- ----------------------------------------------------------
-- 8. TABLE: payments
-- ----------------------------------------------------------
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

-- ----------------------------------------------------------
-- 9. TABLE: member_progress
-- ----------------------------------------------------------
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

-- ----------------------------------------------------------
-- 10. TABLE: automation_rules
-- ----------------------------------------------------------
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

-- ----------------------------------------------------------
-- 11. TABLE: audit_logs
-- ----------------------------------------------------------
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

-- ----------------------------------------------------------
-- 12. TABLE: system_features (Coder Control Center)
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `system_features`;
CREATE TABLE `system_features` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `feature_key` VARCHAR(60) UNIQUE NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `category` VARCHAR(60) NOT NULL,
  `is_enabled` TINYINT(1) DEFAULT 1,
  `visible_roles_json` TEXT NULL,
  `branch_overrides_json` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- SEED DATA
-- Default password for all seed users is: password (or 123)
-- ==========================================================

DELETE FROM `audit_logs`;
DELETE FROM `system_features`;
DELETE FROM `automation_rules`;
DELETE FROM `member_progress`;
DELETE FROM `payments`;
DELETE FROM `attendance`;
DELETE FROM `member_freezes`;
DELETE FROM `member_subscriptions`;
DELETE FROM `members`;
DELETE FROM `membership_plans`;
DELETE FROM `users`;
DELETE FROM `branches`;

-- 1. SEED BRANCHES
INSERT INTO `branches` (`id`, `code`, `name`, `address`, `phone`, `email`, `opening_time`, `closing_time`, `is_active`) VALUES
(1, 'BR-MAIN', 'IronForge Main Arena', '101 Fitness Boulevard, Downtown', '+91 9876543210', 'main@ironforgegym.com', '05:30:00', '22:30:00', 1),
(2, 'BR-NORTH', 'IronForge North Elite', '45 Powerlifting Avenue, Northside', '+91 9876543211', 'north@ironforgegym.com', '06:00:00', '22:00:00', 1),
(3, 'BR-WEST', 'IronForge West Hub', '88 Crossfit Way, West City', '+91 9876543212', 'west@ironforgegym.com', '06:00:00', '21:30:00', 1);

-- 2. SEED USERS (password is hashed 'password')
INSERT INTO `users` (`id`, `branch_id`, `name`, `email`, `phone`, `password`, `role`, `is_active`) VALUES
(1, 1, 'Dev Super Admin', 'admin@gym.com', '9999999991', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', 1),
(2, 1, 'Vikram Singhania (Owner)', 'owner@gym.com', '9999999992', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 1),
(3, 1, 'Rajesh Sharma (Manager)', 'manager@gym.com', '9999999993', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 1),
(4, 1, 'Pooja Verma (Front Desk)', 'staff@gym.com', '9999999994', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 1),
(5, 1, 'Arjun Rao (Head Trainer)', 'trainer@gym.com', '9999999995', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer', 1),
(6, 1, 'Rohan Mehta (Active Member)', 'member@gym.com', '9876500001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1),
(7, 1, 'Ananya Patel (Expiring Member)', 'ananya@gmail.com', '9876500002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1),
(8, 1, 'Karan Malhotra (Expired Member)', 'karan@gmail.com', '9876500003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1);

-- 3. SEED MEMBERSHIP PLANS
INSERT INTO `membership_plans` (`id`, `branch_id`, `name`, `duration_days`, `duration_type`, `price`, `admission_fee`, `description`, `is_active`) VALUES
(1, NULL, 'Monthly Power Pass', 30, 'months', 1500.00, 500.00, 'Standard 1-month full gym access + cardio zone', 1),
(2, NULL, 'Quarterly Transformation Pack', 90, 'months', 3999.00, 0.00, '3 Months gym access + 1 free personal fitness assessment', 1),
(3, NULL, 'Half-Yearly Muscle Builder', 180, 'months', 6999.00, 0.00, '6 Months access + diet chart & locker facility', 1),
(4, NULL, 'Annual Elite Champion', 365, 'years', 11999.00, 0.00, '12 Months unlimited cross-branch access + sauna & free guest passes', 1);

-- 4. SEED MEMBERS
INSERT INTO `members` (`id`, `user_id`, `branch_id`, `member_code`, `qr_token`, `pin_code`, `date_of_birth`, `gender`, `blood_group`, `emergency_contact_name`, `emergency_contact_phone`, `status`) VALUES
(1, 6, 1, 'GYM-1001', 'QR_GYM1001_ACTIVE_TOKEN_SECURE', '1234', '1995-06-15', 'male', 'O+', 'Sunita Mehta', '9876509999', 'active'),
(2, 7, 1, 'GYM-1002', 'QR_GYM1002_EXPIRING_TOKEN_SECURE', '4321', '1998-10-04', 'female', 'B+', 'Kishore Patel', '9876508888', 'expiring_soon'),
(3, 8, 1, 'GYM-1003', 'QR_GYM1003_EXPIRED_TOKEN_SECURE', '5678', '1992-03-22', 'male', 'A+', 'Ritu Malhotra', '9876507777', 'expired');

-- 5. SEED MEMBER SUBSCRIPTIONS
INSERT INTO `member_subscriptions` (`id`, `member_id`, `plan_id`, `start_date`, `end_date`, `price`, `discount`, `status`, `notes`) VALUES
(1, 1, 4, DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_ADD(CURDATE(), INTERVAL 305 DAY), 11999.00, 1000.00, 'active', 'Annual pass active'),
(2, 2, 1, DATE_SUB(CURDATE(), INTERVAL 26 DAY), DATE_ADD(CURDATE(), INTERVAL 4 DAY), 1500.00, 0.00, 'active', 'Expiring in 4 days'),
(3, 3, 1, DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_SUB(CURDATE(), INTERVAL 30 DAY), 1500.00, 0.00, 'expired', 'Expired 30 days ago');

-- 6. SEED PAYMENTS
INSERT INTO `payments` (`id`, `member_id`, `subscription_id`, `receipt_number`, `amount`, `payment_method`, `transaction_id`, `status`, `notes`, `created_at`) VALUES
(1, 1, 1, 'REC-2026-1001', 10999.00, 'upi', 'UPI/2026/8947291', 'completed', 'Annual Elite Plan initial payment', DATE_SUB(NOW(), INTERVAL 60 DAY)),
(2, 2, 2, 'REC-2026-1002', 1500.00, 'cash', NULL, 'completed', 'Monthly Power Pass payment', DATE_SUB(NOW(), INTERVAL 26 DAY)),
(3, 3, 3, 'REC-2026-1003', 1500.00, 'card', 'POS/CARD/99812', 'completed', 'Past membership fee', DATE_SUB(NOW(), INTERVAL 60 DAY));

-- 7. SEED ATTENDANCE
INSERT INTO `attendance` (`member_id`, `branch_id`, `check_in_time`, `method`, `status`, `created_at`) VALUES
(1, 1, DATE_SUB(NOW(), INTERVAL 6 DAY), 'qr', 'present', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(1, 1, DATE_SUB(NOW(), INTERVAL 5 DAY), 'qr', 'present', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 1, DATE_SUB(NOW(), INTERVAL 4 DAY), 'qr', 'present', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 1, DATE_SUB(NOW(), INTERVAL 3 DAY), 'qr', 'present', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 1, DATE_SUB(NOW(), INTERVAL 2 DAY), 'qr', 'present', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 1, DATE_SUB(NOW(), INTERVAL 1 DAY), 'qr', 'present', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 1, DATE_SUB(NOW(), INTERVAL 2 DAY), 'qr', 'present', DATE_SUB(NOW(), INTERVAL 2 DAY));

-- 8. SEED MEMBER PROGRESS
INSERT INTO `member_progress` (`id`, `member_id`, `checkpoint_type`, `recorded_date`, `weight_kg`, `chest_in`, `waist_in`, `arms_in`, `thighs_in`, `body_fat_pct`, `privacy_level`, `notes`, `created_at`) VALUES
(1, 1, 'initial', DATE_SUB(CURDATE(), INTERVAL 60 DAY), 82.50, 40.00, 36.00, 13.50, 23.00, 22.50, 'me_staff', 'Baseline assessment. Target: lean down to 75kg.', DATE_SUB(NOW(), INTERVAL 60 DAY)),
(2, 1, '30_days', DATE_SUB(CURDATE(), INTERVAL 30 DAY), 78.00, 40.50, 34.00, 14.00, 22.50, 19.50, 'me_staff', 'Noticeable fat loss around midsection. Good endurance.', DATE_SUB(NOW(), INTERVAL 30 DAY)),
(3, 1, '60_days', CURDATE(), 75.20, 41.50, 32.50, 14.80, 22.00, 16.20, 'me_staff', 'Goal achieved! Sub-17% body fat reached.', NOW());

-- 9. SEED AUTOMATION RULES
INSERT INTO `automation_rules` (`id`, `name`, `trigger_event`, `action_type`, `message_template`, `is_active`) VALUES
(1, 'Upcoming Renewal Reminder (7 Days)', 'membership_expiring_soon', 'whatsapp', 'Hi {member_name}, your {plan_name} plan at IronForge Gym expires in 7 days (on {expiry_date}). Renew early to avoid workout interruption!', 1),
(2, 'Urgent Expiry Warning (1 Day)', 'membership_expiring_tomorrow', 'email', 'Urgent: Your IronForge gym membership expires tomorrow! Renew today to stay active.', 1),
(3, 'Expired Membership Access Blocker', 'membership_expired', 'system', 'Membership has expired. Front desk & kiosk entry blocked until renewed.', 1),
(4, 'Member Birthday Celebrations', 'member_birthday', 'whatsapp', '🎉 Happy Birthday {member_name}! Wishing you maximum health and strength from IronForge Gym! 🎂💪', 1),
(5, 'Inactive Member Retention Outreach (7+ Days)', 'inactive_churn_risk', 'whatsapp', 'Hey {member_name}! We missed you at IronForge Gym this week! 💪 Come in today for a quick session!', 1);

-- 10. SEED AUDIT LOGS
INSERT INTO `audit_logs` (`user_id`, `branch_id`, `action`, `entity_type`, `entity_id`, `details_json`, `ip_address`, `created_at`) VALUES
(1, 1, 'system_initialized', 'system', 1, '{"message":"Platform seed data loaded successfully"}', '127.0.0.1', NOW());

-- 11. SEED SYSTEM FEATURES (Coder Control Center)
INSERT INTO `system_features` (`feature_key`, `name`, `category`, `is_enabled`, `visible_roles_json`, `branch_overrides_json`) VALUES
('multi_branch', 'Multi-Branch Support', 'Core Platform', 1, '["superadmin","owner"]', '{}'),
('billing', 'Billing & Financial Ledger', 'Finance', 1, '["superadmin","owner","manager"]', '{}'),
('qr_attendance', 'QR Code Attendance', 'Access Control', 1, '["superadmin","owner","manager","staff","member"]', '{}'),
('kiosk_mode', 'Self-Service Kiosk Check-In', 'Access Control', 1, '["superadmin","owner","manager","staff"]', '{}'),
('expiry_blocking', 'Automated Expiry Access Blocker', 'Access Control', 1, '["superadmin","owner","manager","staff"]', '{}'),
('progress_photos', 'Transformation & Photo Timeline', 'Fitness', 1, '["superadmin","owner","manager","trainer","member"]', '{}'),
('privacy_engine', 'Granular Photo Privacy Controls', 'Fitness', 1, '["superadmin","owner","manager","trainer","member"]', '{}'),
('analytics_hub', 'Executive Analytics & Pulse', 'Reporting', 1, '["superadmin","owner"]', '{}'),
('health_diagnostics', 'Server Health Diagnostics', 'System', 1, '["superadmin","owner"]', '{}'),
('member_portal', 'Mobile-First Member Portal', 'Member Experience', 1, '["superadmin","owner","manager","staff","trainer","member"]', '{}'),
('whatsapp_alerts', 'WhatsApp Notification Dispatcher', 'Automation', 1, '["superadmin","owner","manager"]', '{}'),
('email_alerts', 'Email Notification Dispatcher', 'Automation', 1, '["superadmin","owner","manager"]', '{}'),
('cron_automations', 'Automated Nightly Maintenance Cron', 'Automation', 1, '["superadmin"]', '{}'),
('audit_trail', 'Security & Financial Audit Trail', 'Security', 1, '["superadmin","owner"]', '{}');

SET FOREIGN_KEY_CHECKS = 1;