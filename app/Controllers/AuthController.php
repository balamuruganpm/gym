<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Database;
use App\Services\Session;
use App\Validators\Validator;

class AuthController extends Controller
{
    public function showLogin(): string
    {
        return $this->view('auth.login', [
            'pageTitle' => 'Login — GymFlow'
        ], 'layouts.auth');
    }

    public function login(): void
    {
        $validator = Validator::make($_POST, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $this->back(['errors' => $validator->errors()]);
        }

        $email = trim($_POST['email']);
        $password = (string) $_POST['password'];

        $user = Database::fetchOne("SELECT * FROM `users` WHERE `email` = :email LIMIT 1", [
            ':email' => $email
        ]);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            // Increment failed login if user exists
            if ($user) {
                Database::query("UPDATE `users` SET `failed_logins` = `failed_logins` + 1 WHERE `id` = :id", [
                    ':id' => $user['id']
                ]);
            }
            $this->back(['error' => 'Invalid email address or password.']);
        }

        if ($user['status'] !== 'active') {
            $this->back(['error' => 'Your account is currently inactive or locked. Please contact support.']);
        }

        // Fetch user's gym membership and role
        $gymUser = Database::fetchOne("
            SELECT gu.role, gu.is_active, g.* 
            FROM `gym_users` gu
            JOIN `gyms` g ON g.id = gu.gym_id
            WHERE gu.user_id = :user_id AND gu.is_active = 1 AND g.status = 'active'
            ORDER BY gu.id ASC LIMIT 1
        ", [':user_id' => $user['id']]);

        if (!$gymUser) {
            $this->back(['error' => 'No active gym associated with this account.']);
        }

        // Update login metadata
        Database::query("UPDATE `users` SET `failed_logins` = 0, `last_login_at` = NOW() WHERE `id` = :id", [
            ':id' => $user['id']
        ]);

        // Secure session regeneration
        Session::regenerate();

        // Strip password hash from session storage
        unset($user['password_hash']);

        Session::set('user', $user);
        Session::set('user_role', $gymUser['role']);
        Session::set('gym_id', (int) $gymUser['id']);
        Session::set('gym', [
            'id' => (int) $gymUser['id'],
            'name' => $gymUser['name'],
            'slug' => $gymUser['slug'],
            'currency_symbol' => $gymUser['currency_symbol'] ?? '₹',
            'currency_code' => $gymUser['currency_code'] ?? 'INR',
            'timezone' => $gymUser['timezone'] ?? 'Asia/Kolkata',
        ]);

        $this->redirect('dashboard', ['success' => "Welcome back, {$user['name']}!"]);
    }

    public function showRegister(): string
    {
        return $this->view('auth.register', [
            'pageTitle' => 'Create Gym Account — GymFlow'
        ], 'layouts.auth');
    }

    public function register(): void
    {
        $validator = Validator::make($_POST, [
            'gym_name' => 'required|min:3|max:150',
            'owner_name' => 'required|min:2|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'phone' => 'required|min:8|max:20',
        ]);

        if ($validator->fails()) {
            $this->back(['errors' => $validator->errors()]);
        }

        $gymName = trim($_POST['gym_name']);
        $ownerName = trim($_POST['owner_name']);
        $email = strtolower(trim($_POST['email']));
        $phone = trim($_POST['phone']);
        $password = (string) $_POST['password'];

        // Generate URL-friendly slug
        $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $gymName), '-'));
        $slug = $baseSlug . '-' . substr(bin2hex(random_bytes(2)), 0, 4);

        try {
            $result = Database::transaction(function (\PDO $pdo) use ($gymName, $slug, $ownerName, $email, $phone, $password) {
                // 1. Create Gym
                $stmt = $pdo->prepare("
                    INSERT INTO `gyms` (`slug`, `name`, `phone`, `email`, `currency_symbol`, `status`, `created_at`) 
                    VALUES (:slug, :name, :phone, :email, '₹', 'active', NOW())
                ");
                $stmt->execute([
                    ':slug' => $slug,
                    ':name' => $gymName,
                    ':phone' => $phone,
                    ':email' => $email,
                ]);
                $gymId = (int) $pdo->lastInsertId();

                // 2. Create User
                $pwdHash = password_hash($password, PASSWORD_DEFAULT);
                $stmtUser = $pdo->prepare("
                    INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `status`, `created_at`)
                    VALUES (:name, :email, :pwd, :phone, 'active', NOW())
                ");
                $stmtUser->execute([
                    ':name' => $ownerName,
                    ':email' => $email,
                    ':pwd' => $pwdHash,
                    ':phone' => $phone,
                ]);
                $userId = (int) $pdo->lastInsertId();

                // 3. Bind Owner to Gym
                $stmtGu = $pdo->prepare("
                    INSERT INTO `gym_users` (`gym_id`, `user_id`, `role`, `is_active`, `created_at`)
                    VALUES (:gym_id, :user_id, 'owner', 1, NOW())
                ");
                $stmtGu->execute([
                    ':gym_id' => $gymId,
                    ':user_id' => $userId,
                ]);

                // 4. Seed default starter plans
                $defaultPlans = [
                    ['name' => '1 Month Basic', 'type' => 'months', 'val' => 1, 'price' => 1500.00],
                    ['name' => '3 Months Pro', 'type' => 'months', 'val' => 3, 'price' => 3800.00],
                    ['name' => '6 Months Premium', 'type' => 'months', 'val' => 6, 'price' => 6800.00],
                    ['name' => '1 Year Elite', 'type' => 'months', 'val' => 12, 'price' => 12000.00],
                ];

                $stmtPlan = $pdo->prepare("
                    INSERT INTO `membership_plans` (`gym_id`, `name`, `duration_type`, `duration_value`, `price`, `is_active`, `created_at`)
                    VALUES (:gym_id, :name, :type, :val, :price, 1, NOW())
                ");

                foreach ($defaultPlans as $p) {
                    $stmtPlan->execute([
                        ':gym_id' => $gymId,
                        ':name' => $p['name'],
                        ':type' => $p['type'],
                        ':val' => $p['val'],
                        ':price' => $p['price'],
                    ]);
                }

                // 5. Seed default settings
                $stmtSetting = $pdo->prepare("
                    INSERT INTO `gym_settings` (`gym_id`, `setting_key`, `setting_value`, `created_at`)
                    VALUES (:gym_id, :key, :val, NOW())
                ");
                $stmtSetting->execute([':gym_id' => $gymId, ':key' => 'expiry_warning_days', ':val' => '7']);
                $stmtSetting->execute([':gym_id' => $gymId, ':key' => 'currency_symbol', ':val' => '₹']);

                return [
                    'gymId' => $gymId,
                    'userId' => $userId,
                    'gymName' => $gymName,
                    'slug' => $slug,
                    'ownerName' => $ownerName,
                    'email' => $email,
                ];
            });

            // Auto-login
            Session::regenerate();
            Session::set('user', [
                'id' => $result['userId'],
                'name' => $result['ownerName'],
                'email' => $result['email'],
                'status' => 'active'
            ]);
            Session::set('user_role', 'owner');
            Session::set('gym_id', $result['gymId']);
            Session::set('gym', [
                'id' => $result['gymId'],
                'name' => $result['gymName'],
                'slug' => $result['slug'],
                'currency_symbol' => '₹',
                'currency_code' => 'INR',
                'timezone' => 'Asia/Kolkata',
            ]);

            $this->redirect('dashboard', ['success' => "🎉 Gym registered successfully! Welcome to GymFlow."]);
        } catch (\Throwable $e) {
            $this->back(['error' => 'Registration error: ' . $e->getMessage()]);
        }
    }

    public function logout(): void
    {
        Session::destroy();
        $this->redirect('login', ['success' => 'You have been safely logged out.']);
    }
}
