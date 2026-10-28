<?php

declare(strict_types=1);

/**
 * GymFlow — Owner Provisioning Command
 * 
 * Usage:
 *   Interactive:
 *     php bin/provision-owner.php
 *   Non-interactive:
 *     php bin/provision-owner.php --gym-id=1 --email="owner@goldfitness.test" --name="Gym Owner" --password="SecurePassword123" --phone="9876543210"
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden: CLI execution only.\n";
    exit(1);
}

$baseDir = dirname(__DIR__);
require_once $baseDir . '/vendor/autoload.php';

use App\Services\Config;
use App\Services\Database;
use App\Services\Logger;

Config::load($baseDir);

echo "\n=======================================================\n";
echo " GymFlow — Gym Owner Account Provisioning\n";
echo "=======================================================\n\n";

$options = getopt('', ['gym-id:', 'email:', 'name:', 'password:', 'phone:']);

$gymId = isset($options['gym-id']) ? (int) $options['gym-id'] : null;
$email = $options['email'] ?? null;
$name = $options['name'] ?? null;
$password = $options['password'] ?? null;
$phone = $options['phone'] ?? null;

function prompt(string $message): string {
    echo $message . ': ';
    $input = fgets(STDIN);
    return trim($input !== false ? $input : '');
}

$pdo = Database::getConnection();

if ($gymId === null || $gymId <= 0) {
    // List available gyms
    $gyms = Database::fetchAll("SELECT id, name, slug FROM `gyms` WHERE `status` = 'active' ORDER BY id ASC");
    if (empty($gyms)) {
        echo "❌ Error: No active gyms found in database. Create a gym first.\n";
        exit(1);
    }
    echo "Available Gyms:\n";
    foreach ($gyms as $g) {
        echo "  [ID: {$g['id']}] {$g['name']} ({$g['slug']})\n";
    }
    $gymIdInput = prompt('Enter Gym ID for Owner Assignment');
    $gymId = (int) $gymIdInput;
}

$gym = Database::fetchOne("SELECT * FROM `gyms` WHERE `id` = :id LIMIT 1", [':id' => $gymId]);
if (!$gym) {
    echo "❌ Error: Gym with ID {$gymId} does not exist.\n";
    exit(1);
}

if (empty($email)) {
    $email = prompt('Enter Owner Email Address');
}
if (empty($name)) {
    $name = prompt('Enter Owner Full Name');
}
if (empty($password)) {
    $password = prompt('Enter Owner Password (min 8 chars)');
}
$isNonInteractive = !empty($options['email']) && !empty($options['password']);
if (!$isNonInteractive && empty($phone)) {
    $phone = prompt('Enter Owner Phone (optional)');
}

$email = strtolower(trim((string) $email));
$name = trim((string) $name);
$password = (string) $password;
$phone = !empty($phone) ? trim((string) $phone) : null;

// Validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "❌ Validation Error: Invalid email format.\n";
    exit(1);
}
if (strlen($password) < 8) {
    echo "❌ Validation Error: Password must be at least 8 characters long.\n";
    exit(1);
}

try {
    $pdo->beginTransaction();

    // Check if user already exists
    $stmtUser = $pdo->prepare("SELECT * FROM `users` WHERE `email` = :email FOR UPDATE");
    $stmtUser->execute([':email' => $email]);
    $existingUser = $stmtUser->fetch(\PDO::FETCH_ASSOC);

    if ($existingUser) {
        $userId = (int) $existingUser['id'];

        // Prevent turning a SupremeLeader into a regular gym owner
        if (!empty($existingUser['is_supreme_leader'])) {
            $pdo->rollBack();
            echo "❌ Provisioning Error: The SupremeLeader account cannot be assigned as a regular gym owner.\n";
            exit(1);
        }

        // Update password if specified
        $stmtUpdateUser = $pdo->prepare("
            UPDATE `users` SET `name` = :name, `password_hash` = :pwd, `phone` = COALESCE(:phone, `phone`), `status` = 'active', `updated_at` = NOW()
            WHERE `id` = :id
        ");
        $stmtUpdateUser->execute([
            ':name' => $name,
            ':pwd' => password_hash($password, PASSWORD_DEFAULT),
            ':phone' => $phone,
            ':id' => $userId,
        ]);

        // Check gym_users assignment
        $stmtGu = $pdo->prepare("SELECT * FROM `gym_users` WHERE `gym_id` = :gym_id AND `user_id` = :user_id FOR UPDATE");
        $stmtGu->execute([':gym_id' => $gymId, ':user_id' => $userId]);
        $existingGu = $stmtGu->fetch(\PDO::FETCH_ASSOC);

        if ($existingGu) {
            $pdo->prepare("
                UPDATE `gym_users` SET `role` = 'owner', `is_active` = 1, `updated_at` = NOW()
                WHERE `id` = :id
            ")->execute([':id' => $existingGu['id']]);
        } else {
            $pdo->prepare("
                INSERT INTO `gym_users` (`gym_id`, `user_id`, `role`, `is_active`, `created_at`)
                VALUES (:gym_id, :user_id, 'owner', 1, NOW())
            ")->execute([':gym_id' => $gymId, ':user_id' => $userId]);
        }
    } else {
        // Create new user
        $stmtCreateUser = $pdo->prepare("
            INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `status`, `created_at`)
            VALUES (:name, :email, :pwd, :phone, 'active', NOW())
        ");
        $stmtCreateUser->execute([
            ':name' => $name,
            ':email' => $email,
            ':pwd' => password_hash($password, PASSWORD_DEFAULT),
            ':phone' => $phone,
        ]);
        $userId = (int) $pdo->lastInsertId();

        // Assign as owner
        $stmtGu = $pdo->prepare("
            INSERT INTO `gym_users` (`gym_id`, `user_id`, `role`, `is_active`, `created_at`)
            VALUES (:gym_id, :user_id, 'owner', 1, NOW())
        ");
        $stmtGu->execute([
            ':gym_id' => $gymId,
            ':user_id' => $userId,
        ]);
    }

    // Audit log
    $stmtAudit = $pdo->prepare("
        INSERT INTO `audit_logs` (`gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`)
        VALUES (:gym_id, :user_id, 'owner_provisioned', 'user', :entity_id, :details, NOW())
    ");
    $stmtAudit->execute([
        ':gym_id' => $gymId,
        ':user_id' => $userId,
        ':entity_id' => $userId,
        ':details' => json_encode([
            'email' => $email,
            'name' => $name,
            'gym_id' => $gymId,
            'gym_name' => $gym['name'],
        ]),
    ]);

    $pdo->commit();

    Logger::security("Owner provisioned via CLI", [
        'gym_id' => $gymId,
        'user_id' => $userId,
        'email' => $email,
    ]);

    echo "✅ Success: Gym Owner provisioned successfully!\n";
    echo "   Gym:     [ID: {$gymId}] {$gym['name']}\n";
    echo "   User ID: {$userId}\n";
    echo "   Name:    {$name}\n";
    echo "   Email:   {$email}\n";
    echo "   Role:    Owner\n\n";
    exit(0);
} catch (\Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Provisioning Failed: " . $e->getMessage() . "\n";
    exit(1);
}
