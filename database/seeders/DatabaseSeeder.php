<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\Config;
use App\Services\Database;

Config::load(__DIR__ . '/../..');

echo "🌱 Running Database Seeder on `gym-premium`...\n";

$pdo = Database::getConnection();

// 1. Seed Demo Gym
$stmt = $pdo->prepare("SELECT id FROM `gyms` WHERE `slug` = 'gold-fitness-gym' LIMIT 1");
$stmt->execute();
$gymId = $stmt->fetchColumn();

if (!$gymId) {
    $stmt = $pdo->prepare("
        INSERT INTO `gyms` (`slug`, `name`, `phone`, `email`, `address`, `currency_symbol`, `currency_code`, `status`, `created_at`)
        VALUES ('gold-fitness-gym', 'Gold Fitness Gym', '+91 98765 43210', 'contact@goldfitness.test', '100 Feet Road, Indiranagar, Bengaluru', '₹', 'INR', 'active', NOW())
    ");
    $stmt->execute();
    $gymId = (int) $pdo->lastInsertId();
    echo "  [OK] Created Gym: Gold Fitness Gym (ID: {$gymId})\n";
} else {
    $gymId = (int) $gymId;
    echo "  [INFO] Gym Gold Fitness Gym already exists (ID: {$gymId})\n";
}

// 2. Seed Gym Owner
$ownerEmail = 'owner@gymflow.test';
$stmt = $pdo->prepare("SELECT id FROM `users` WHERE `email` = :email LIMIT 1");
$stmt->execute([':email' => $ownerEmail]);
$ownerId = $stmt->fetchColumn();

if (!$ownerId) {
    $pwd = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `status`, `created_at`)
        VALUES ('Admin User', :email, :pwd, '+91 98765 00001', 'active', NOW())
    ");
    $stmt->execute([':email' => $ownerEmail, ':pwd' => $pwd]);
    $ownerId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO `gym_users` (`gym_id`, `user_id`, `role`, `is_active`, `created_at`)
        VALUES (:gym_id, :user_id, 'owner', 1, NOW())
    ");
    $stmt->execute([':gym_id' => $gymId, ':user_id' => $ownerId]);
    echo "  [OK] Created Owner: {$ownerEmail} (password: password123)\n";
} else {
    $ownerId = (int) $ownerId;
    echo "  [INFO] Owner {$ownerEmail} already exists.\n";
}

// 3. Seed Gym Staff
$staffEmail = 'staff@gymflow.test';
$stmt = $pdo->prepare("SELECT id FROM `users` WHERE `email` = :email LIMIT 1");
$stmt->execute([':email' => $staffEmail]);
$staffId = $stmt->fetchColumn();

if (!$staffId) {
    $pwd = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `status`, `created_at`)
        VALUES ('Kavitha Trainer', :email, :pwd, '+91 98765 00002', 'active', NOW())
    ");
    $stmt->execute([':email' => $staffEmail, ':pwd' => $pwd]);
    $staffId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO `gym_users` (`gym_id`, `user_id`, `role`, `is_active`, `created_at`)
        VALUES (:gym_id, :user_id, 'staff', 1, NOW())
    ");
    $stmt->execute([':gym_id' => $gymId, ':user_id' => $staffId]);
    echo "  [OK] Created Staff: {$staffEmail} (password: password123)\n";
}

// 4. Seed Standard Plans
$plans = [
    ['name' => 'Gym 1 Month', 'type' => 'months', 'val' => 1, 'price' => 2000.00],
    ['name' => 'Gym 3 Months', 'type' => 'months', 'val' => 3, 'price' => 5000.00],
    ['name' => 'Gym 6 Months', 'type' => 'months', 'val' => 6, 'price' => 8500.00],
    ['name' => 'Gym 1 Year Annual', 'type' => 'months', 'val' => 12, 'price' => 15000.00],
];

$planIds = [];
foreach ($plans as $p) {
    $stmt = $pdo->prepare("SELECT id FROM `membership_plans` WHERE `gym_id` = :gym_id AND `name` = :name LIMIT 1");
    $stmt->execute([':gym_id' => $gymId, ':name' => $p['name']]);
    $pId = $stmt->fetchColumn();

    if (!$pId) {
        $stmt = $pdo->prepare("
            INSERT INTO `membership_plans` (`gym_id`, `name`, `duration_type`, `duration_value`, `price`, `is_active`, `created_at`)
            VALUES (:gym_id, :name, :type, :val, :price, 1, NOW())
        ");
        $stmt->execute([
            ':gym_id' => $gymId,
            ':name' => $p['name'],
            ':type' => $p['type'],
            ':val' => $p['val'],
            ':price' => $p['price'],
        ]);
        $pId = (int) $pdo->lastInsertId();
    }
    $planIds[$p['name']] = (int) $pId;
}
echo "  [OK] Seeded/Verified membership plans.\n";

// 5. Seed Sample Members & Memberships
$members = [
    [
        'num' => 'GF-2026-0001',
        'first' => 'Rajesh',
        'last' => 'Kumar',
        'phone' => '9845112233',
        'email' => 'rajesh.kumar@example.com',
        'gender' => 'male',
        'plan' => 'Gym 3 Months',
        'start' => date('Y-m-d', strtotime('-88 days')),
        'expiry' => date('Y-m-d', strtotime('+2 days')),
        'price' => 5000.00,
        'paid' => 5000.00,
        'method' => 'upi',
    ],
    [
        'num' => 'GF-2026-0002',
        'first' => 'Priya',
        'last' => 'Sharma',
        'phone' => '9845223344',
        'email' => 'priya.sharma@example.com',
        'gender' => 'female',
        'plan' => 'Gym 6 Months',
        'start' => date('Y-m-d', strtotime('-176 days')),
        'expiry' => date('Y-m-d', strtotime('+4 days')),
        'price' => 8500.00,
        'paid' => 8500.00,
        'method' => 'cash',
    ],
    [
        'num' => 'GF-2026-0003',
        'first' => 'Arjun',
        'last' => 'Mehta',
        'phone' => '9845334455',
        'email' => 'arjun.mehta@example.com',
        'gender' => 'male',
        'plan' => 'Gym 1 Month',
        'start' => date('Y-m-d', strtotime('-24 days')),
        'expiry' => date('Y-m-d', strtotime('+6 days')),
        'price' => 2000.00,
        'paid' => 2000.00,
        'method' => 'bank_transfer',
    ],
    [
        'num' => 'GF-2026-0004',
        'first' => 'Sneha',
        'last' => 'Patel',
        'phone' => '9845445566',
        'email' => 'sneha.patel@example.com',
        'gender' => 'female',
        'plan' => 'Gym 3 Months',
        'start' => date('Y-m-d', strtotime('-81 days')),
        'expiry' => date('Y-m-d', strtotime('+9 days')),
        'price' => 5000.00,
        'paid' => 2500.00, // Partial payment
        'method' => 'upi',
    ],
    [
        'num' => 'GF-2026-0005',
        'first' => 'Vikram',
        'last' => 'Singh',
        'phone' => '9845556677',
        'email' => 'vikram.singh@example.com',
        'gender' => 'male',
        'plan' => 'Gym 6 Months',
        'start' => date('Y-m-d', strtotime('-169 days')),
        'expiry' => date('Y-m-d', strtotime('+11 days')),
        'price' => 8500.00,
        'paid' => 8500.00,
        'method' => 'cash',
    ],
    [
        'num' => 'GF-2026-0006',
        'first' => 'Ananya',
        'last' => 'Rao',
        'phone' => '9845667788',
        'email' => 'ananya.rao@example.com',
        'gender' => 'female',
        'plan' => 'Gym 1 Year Annual',
        'start' => date('Y-m-d', strtotime('-30 days')),
        'expiry' => date('Y-m-d', strtotime('+335 days')),
        'price' => 15000.00,
        'paid' => 15000.00,
        'method' => 'card',
    ],
    [
        'num' => 'GF-2026-0007',
        'first' => 'Karthik',
        'last' => 'Subramanian',
        'phone' => '9845778899',
        'email' => 'karthik.sub@example.com',
        'gender' => 'male',
        'plan' => 'Gym 1 Month',
        'start' => date('Y-m-d', strtotime('-40 days')),
        'expiry' => date('Y-m-d', strtotime('-10 days')), // Expired
        'price' => 2000.00,
        'paid' => 2000.00,
        'method' => 'cash',
    ],
];

$recCounter = 1;
foreach ($members as $m) {
    $stmt = $pdo->prepare("SELECT id FROM `members` WHERE `gym_id` = :gym_id AND `member_number` = :num LIMIT 1");
    $stmt->execute([':gym_id' => $gymId, ':num' => $m['num']]);
    $memberId = $stmt->fetchColumn();

    if (!$memberId) {
        $stmt = $pdo->prepare("
            INSERT INTO `members` (`gym_id`, `member_number`, `first_name`, `last_name`, `phone`, `email`, `gender`, `is_archived`, `created_at`)
            VALUES (:gym_id, :num, :first, :last, :phone, :email, :gender, 0, NOW())
        ");
        $stmt->execute([
            ':gym_id' => $gymId,
            ':num' => $m['num'],
            ':first' => $m['first'],
            ':last' => $m['last'],
            ':phone' => $m['phone'],
            ':email' => $m['email'],
            ':gender' => $m['gender'],
        ]);
        $memberId = (int) $pdo->lastInsertId();

        // Assign Membership
        $planId = $planIds[$m['plan']] ?? null;
        $status = (strtotime($m['expiry']) < time()) ? 'expired' : 'active';
        $stmtMem = $pdo->prepare("
            INSERT INTO `memberships` (`gym_id`, `member_id`, `plan_id`, `plan_name_snapshot`, `start_date`, `expiry_date`, `original_price`, `discount_amount`, `agreed_price`, `status`, `created_at`)
            VALUES (:gym_id, :member_id, :plan_id, :plan_name, :start_date, :expiry_date, :orig_price, 0.00, :agreed_price, :status, NOW())
        ");
        $stmtMem->execute([
            ':gym_id' => $gymId,
            ':member_id' => $memberId,
            ':plan_id' => $planId,
            ':plan_name' => $m['plan'],
            ':start_date' => $m['start'],
            ':expiry_date' => $m['expiry'],
            ':orig_price' => $m['price'],
            ':agreed_price' => $m['price'],
            ':status' => $status,
        ]);
        $membershipId = (int) $pdo->lastInsertId();

        // Record Payment
        if ($m['paid'] > 0) {
            $recNum = sprintf('REC-%s-%04d', date('Y'), $recCounter++);
            $stmtPay = $pdo->prepare("
                INSERT INTO `payments` (`gym_id`, `membership_id`, `member_id`, `user_id`, `receipt_number`, `amount`, `payment_method`, `payment_date`, `status`, `created_at`)
                VALUES (:gym_id, :membership_id, :member_id, :user_id, :receipt, :amount, :method, :pay_date, 'paid', NOW())
            ");
            $stmtPay->execute([
                ':gym_id' => $gymId,
                ':membership_id' => $membershipId,
                ':member_id' => $memberId,
                ':user_id' => $ownerId,
                ':receipt' => $recNum,
                ':amount' => $m['paid'],
                ':method' => $m['method'],
                ':pay_date' => $m['start'],
            ]);
        }
    }
}
echo "  [OK] Seeded/Verified sample members with memberships & transactions.\n";

// 6. Seed Gym Settings
$settings = [
    'expiry_warning_days' => '7',
    'currency_symbol' => '₹',
    'currency_code' => 'INR',
    'whatsapp_reminder_template' => "Hi {name}, your membership at {gym_name} expires on {expiry_date}. Please renew to continue your workout streak!",
];

foreach ($settings as $k => $v) {
    $stmt = $pdo->prepare("
        INSERT INTO `gym_settings` (`gym_id`, `setting_key`, `setting_value`, `created_at`)
        VALUES (:gym_id, :k, :v, NOW())
        ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)
    ");
    $stmt->execute([':gym_id' => $gymId, ':k' => $k, ':v' => $v]);
}
echo "  [OK] Seeded/Verified gym settings.\n";

echo "✨ Seeding completed successfully for `gym-premium`!\n";
