-- ==========================================================
-- FitPulse Gym Management Platform - Seed Data
-- Default password for all seed users is: password (or 123)
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM `audit_logs`;
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

-- 2. SEED USERS (password is hashed 'password' -> $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
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
(1, 1, DATE_SUB(NOW(), INTERVAL 5 DAY), 'qr', 'present', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 1, DATE_SUB(NOW(), INTERVAL 4 DAY), 'qr', 'present', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 1, DATE_SUB(NOW(), INTERVAL 3 DAY), 'pin', 'present', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 1, DATE_SUB(NOW(), INTERVAL 2 DAY), 'kiosk', 'present', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 1, DATE_SUB(NOW(), INTERVAL 1 DAY), 'member_app', 'present', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 1, NOW(), 'qr', 'present', NOW()),
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

SET FOREIGN_KEY_CHECKS = 1;
