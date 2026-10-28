<?php

declare(strict_types=1);

/**
 * GymFlow — SupremeLeader CLI Provisioning Command
 * 
 * Usage:
 *   Interactive:
 *     php bin/provision-supreme-leader.php
 *   Non-interactive (for deployment scripts / CI tests):
 *     php bin/provision-supreme-leader.php --email="admin@gymflow.local" --name="Supreme Leader" --password="SuperSecurePassword123"
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
echo " GymFlow — SupremeLeader Account Provisioning\n";
echo "=======================================================\n\n";

// Parse CLI options
$options = getopt('', ['email:', 'name:', 'password:']);

$email = $options['email'] ?? null;
$name = $options['name'] ?? null;
$password = $options['password'] ?? null;

// Helper to prompt securely
function prompt(string $message): string {
    echo $message . ': ';
    $input = fgets(STDIN);
    return trim($input !== false ? $input : '');
}

if (empty($email)) {
    $email = prompt('Enter SupremeLeader Email Address');
}

if (empty($name)) {
    $name = prompt('Enter SupremeLeader Full Name');
    if (empty($name)) {
        $name = 'Supreme Leader';
    }
}

if (empty($password)) {
    // Hidden or regular CLI password prompt
    $password = prompt('Enter Secure Password (min 8 chars, 1 letter, 1 number)');
}

$email = strtolower(trim((string) $email));
$name = trim((string) $name);
$password = (string) $password;

// 1. Validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "❌ Validation Error: Invalid email address format.\n";
    exit(1);
}

if (strlen($password) < 8) {
    echo "❌ Validation Error: Password must be at least 8 characters long.\n";
    exit(1);
}

if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    echo "❌ Validation Error: Password must contain at least one letter and one number.\n";
    exit(1);
}

$pdo = Database::getConnection();

// 2. Transactional Single-Account Enforcement
try {
    $pdo->beginTransaction();

    // Check existing active SupremeLeader
    $stmt = $pdo->query("SELECT COUNT(*) FROM `users` WHERE `is_supreme_leader` = 1 FOR UPDATE");
    $count = (int) $stmt->fetchColumn();

    if ($count > 0) {
        $pdo->rollBack();
        echo "❌ Provisioning Denied: Exactly one SupremeLeader account is allowed on this installation.\n";
        echo "   An active SupremeLeader account is already registered in the system.\n";
        exit(1);
    }

    // Check if email already used by regular user
    $stmtUser = $pdo->prepare("SELECT id FROM `users` WHERE `email` = :email FOR UPDATE");
    $stmtUser->execute([':email' => $email]);
    if ($stmtUser->fetch()) {
        $pdo->rollBack();
        echo "❌ Provisioning Error: A user account with email '{$email}' already exists.\n";
        exit(1);
    }

    // Hash password using standard PHP password_hash
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert SupremeLeader
    $stmtInsert = $pdo->prepare("
        INSERT INTO `users` (`name`, `email`, `password_hash`, `status`, `is_supreme_leader`, `created_at`)
        VALUES (:name, :email, :password_hash, 'active', 1, NOW())
    ");
    $stmtInsert->execute([
        ':name' => $name,
        ':email' => $email,
        ':password_hash' => $passwordHash,
    ]);

    $supremeLeaderId = (int) $pdo->lastInsertId();

    // Audit in audit_logs table (gym_id = 1 or default operational gym)
    $stmtAudit = $pdo->prepare("
        INSERT INTO `audit_logs` (`gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`)
        VALUES (1, :user_id, 'supreme_leader_provisioned', 'user', :entity_id, :details, NOW())
    ");
    $stmtAudit->execute([
        ':user_id' => $supremeLeaderId,
        ':entity_id' => $supremeLeaderId,
        ':details' => json_encode([
            'email' => $email,
            'name' => $name,
            'channel' => 'cli_provisioning'
        ]),
    ]);

    $pdo->commit();

    // Write to security log
    Logger::security("SupremeLeader account successfully provisioned via CLI", [
        'user_id' => $supremeLeaderId,
        'email' => $email,
    ]);

    echo "✅ Success: SupremeLeader account provisioned successfully!\n";
    echo "   User ID: {$supremeLeaderId}\n";
    echo "   Name:    {$name}\n";
    echo "   Email:   {$email}\n";
    echo "   Role:    SupremeLeader (Platform Administrator)\n\n";
    exit(0);
} catch (\Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Provisioning Failed: " . $e->getMessage() . "\n";
    exit(1);
}
