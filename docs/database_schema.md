# Database Schema Design Document

## 1. Relational Entity Overview

```text
gym_branches
    │
    ├── users (Superadmin, Owner, Manager, Staff, Trainer, Member)
    │     ├── user_roles & permissions
    │     └── audit_logs
    │
    ├── members (Member profile, QR code, Emergency contact, Medical notes)
    │     │
    │     ├── memberships (Plan, Start Date, End Date, Status, Freeze Counter)
    │     │     └── membership_plans (Monthly, Quarterly, Yearly, etc.)
    │     │
    │     ├── membership_freezes (Freeze Start, Freeze End, Reason, Adjusted Days)
    │     │
    │     ├── attendance (Date, Check-in Time, Check-out Time, Method, Status)
    │     │
    │     ├── payments (Amount, Payment Method, Tax, Discount, Receipt No, Status)
    │     │
    │     ├── progress_checkpoints (Checkpoint Date, Milestone Tag, Weight)
    │     │     ├── progress_photos (Image Path, View Angle, Privacy Level)
    │     │     └── body_measurements (Chest, Waist, Arms, Thighs, Custom Fields)
    │     │
    │     └── assigned_trainers
    │
    ├── system_features (Feature Key, Name, Global Enabled, Branch Overrides)
    ├── system_settings (Key, Value, Category)
    └── automation_rules (Trigger, Conditions, Action Payload, Status)
```

---

## 2. Table Definitions & Specifications

### 2.1 `gym_branches`
- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `code` VARCHAR(30) UNIQUE (e.g., `BR-MAIN`, `BR-SOUTH`)
- `name` VARCHAR(150) NOT NULL
- `address` TEXT
- `phone` VARCHAR(30)
- `email` VARCHAR(100)
- `opening_time` TIME DEFAULT '06:00:00'
- `closing_time` TIME DEFAULT '22:00:00'
- `is_active` TINYINT(1) DEFAULT 1
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP

### 2.2 `users`
- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `branch_id` INT UNSIGNED NULL (FK -> `gym_branches.id`)
- `username` VARCHAR(60) UNIQUE NOT NULL
- `email` VARCHAR(120) UNIQUE NOT NULL
- `phone` VARCHAR(30) UNIQUE NULL
- `password_hash` VARCHAR(255) NOT NULL
- `role` ENUM('superadmin', 'owner', 'manager', 'staff', 'trainer', 'member') NOT NULL
- `full_name` VARCHAR(120) NOT NULL
- `avatar_url` VARCHAR(255) NULL
- `is_active` TINYINT(1) DEFAULT 1
- `last_login_at` DATETIME NULL
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
- `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP

### 2.3 `members`
- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id` INT UNSIGNED NOT NULL UNIQUE (FK -> `users.id`)
- `branch_id` INT UNSIGNED NOT NULL (FK -> `gym_branches.id`)
- `member_code` VARCHAR(40) UNIQUE NOT NULL (e.g., `GYM-1001`)
- `qr_token` VARCHAR(64) UNIQUE NOT NULL
- `pin_code` VARCHAR(10) NULL
- `dob` DATE NULL
- `gender` ENUM('male', 'female', 'other') NOT NULL
- `blood_group` VARCHAR(10) NULL
- `emergency_contact_name` VARCHAR(100) NULL
- `emergency_contact_phone` VARCHAR(30) NULL
- `joining_date` DATE NOT NULL
- `medical_notes` TEXT NULL (Encrypted / restricted access)
- `general_notes` TEXT NULL
- `cross_branch_access` TINYINT(1) DEFAULT 0
- `current_status` ENUM('active', 'expiring_soon', 'expired', 'frozen', 'cancelled') DEFAULT 'active'
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP

### 2.4 `membership_plans`
- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `branch_id` INT UNSIGNED NOT NULL (FK -> `gym_branches.id`)
- `name` VARCHAR(100) NOT NULL (e.g., 'Monthly Standard', 'Yearly VIP')
- `duration_days` INT UNSIGNED NOT NULL (e.g., 30, 90, 180, 365)
- `price` DECIMAL(10, 2) NOT NULL
- `admission_fee` DECIMAL(10, 2) DEFAULT 0.00
- `description` TEXT NULL
- `is_active` TINYINT(1) DEFAULT 1
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP

### 2.5 `memberships`
- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `member_id` INT UNSIGNED NOT NULL (FK -> `members.id`)
- `plan_id` INT UNSIGNED NOT NULL (FK -> `membership_plans.id`)
- `start_date` DATE NOT NULL
- `end_date` DATE NOT NULL
- `original_end_date` DATE NOT NULL
- `status` ENUM('active', 'expiring_soon', 'expired', 'frozen', 'cancelled') DEFAULT 'active'
- `total_amount` DECIMAL(10, 2) NOT NULL
- `discount_amount` DECIMAL(10, 2) DEFAULT 0.00
- `final_amount` DECIMAL(10, 2) NOT NULL
- `paid_amount` DECIMAL(10, 2) DEFAULT 0.00
- `is_current` TINYINT(1) DEFAULT 1
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP

### 2.6 `membership_freezes`
- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `membership_id` INT UNSIGNED NOT NULL (FK -> `memberships.id`)
- `freeze_start` DATE NOT NULL
- `freeze_end` DATE NOT NULL
- `days_frozen` INT UNSIGNED NOT NULL
- `reason` VARCHAR(255) NULL
- `approved_by` INT UNSIGNED NOT NULL (FK -> `users.id`)
- `status` ENUM('pending', 'active', 'completed', 'cancelled') DEFAULT 'active'
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP

### 2.7 `attendance`
- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `member_id` INT UNSIGNED NOT NULL (FK -> `members.id`)
- `branch_id` INT UNSIGNED NOT NULL (FK -> `gym_branches.id`)
- `attendance_date` DATE NOT NULL
- `check_in_time` TIME NOT NULL
- `check_out_time` TIME NULL
- `method` ENUM('qr', 'kiosk', 'pin', 'member_app', 'staff_manual', 'phone') NOT NULL
- `status` ENUM('present', 'late', 'rejected') DEFAULT 'present'
- `recorded_by` INT UNSIGNED NULL (FK -> `users.id`)
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
- INDEX `idx_member_date` (`member_id`, `attendance_date`)

### 2.8 `payments`
- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `member_id` INT UNSIGNED NOT NULL (FK -> `members.id`)
- `membership_id` INT UNSIGNED NULL (FK -> `memberships.id`)
- `branch_id` INT UNSIGNED NOT NULL (FK -> `gym_branches.id`)
- `receipt_number` VARCHAR(50) UNIQUE NOT NULL
- `amount` DECIMAL(10, 2) NOT NULL
- `payment_method` ENUM('cash', 'upi', 'card', 'netbanking', 'cheque', 'online') NOT NULL
- `transaction_reference` VARCHAR(100) NULL
- `payment_date` DATE NOT NULL
- `notes` VARCHAR(255) NULL
- `collected_by` INT UNSIGNED NOT NULL (FK -> `users.id`)
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP

### 2.9 `progress_checkpoints`
- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `member_id` INT UNSIGNED NOT NULL (FK -> `members.id`)
- `checkpoint_date` DATE NOT NULL
- `milestone_tag` VARCHAR(50) NOT NULL (e.g., 'Baseline', '30 Days', '60 Days', '90 Days', '1 Year')
- `weight_kg` DECIMAL(5, 2) NULL
- `body_fat_pct` DECIMAL(4, 1) NULL
- `notes` TEXT NULL
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP

### 2.10 `progress_photos`
- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `checkpoint_id` INT UNSIGNED NOT NULL (FK -> `progress_checkpoints.id`)
- `photo_path` VARCHAR(255) NOT NULL
- `angle` ENUM('front', 'back', 'left', 'right', 'flexed', 'other') NOT NULL
- `privacy` ENUM('only_me', 'me_trainer', 'me_staff', 'community') DEFAULT 'only_me'
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP

### 2.11 `body_measurements`
- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `checkpoint_id` INT UNSIGNED NOT NULL (FK -> `progress_checkpoints.id`)
- `chest_inch` DECIMAL(5, 2) NULL
- `waist_inch` DECIMAL(5, 2) NULL
- `arms_inch` DECIMAL(5, 2) NULL
- `thighs_inch` DECIMAL(5, 2) NULL
- `hips_inch` DECIMAL(5, 2) NULL
- `shoulders_inch` DECIMAL(5, 2) NULL
- `custom_metrics_json` JSON NULL
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP

### 2.12 `system_features` & `system_settings`
- `feature_key` VARCHAR(50) PRIMARY KEY
- `name` VARCHAR(100) NOT NULL
- `category` VARCHAR(50) NOT NULL
- `is_enabled` TINYINT(1) DEFAULT 1
- `visible_roles_json` JSON NOT NULL
- `branch_overrides_json` JSON NULL

### 2.13 `audit_logs`
- `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id` INT UNSIGNED NULL
- `branch_id` INT UNSIGNED NULL
- `action` VARCHAR(80) NOT NULL
- `entity_type` VARCHAR(50) NOT NULL
- `entity_id` INT UNSIGNED NULL
- `details_json` JSON NULL
- `ip_address` VARCHAR(45) NULL
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
