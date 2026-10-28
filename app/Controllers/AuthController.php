<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Config;
use App\Services\Database;
use App\Services\Logger;
use App\Services\Session;
use App\Validators\Validator;

class AuthController extends Controller
{
    public function showLogin(): string
    {
        return $this->view('auth.login', [
            'pageTitle' => 'Login - GymFlow'
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

        // 1. SupremeLeader Platform Administrator Authentication
        if (!empty($user['is_supreme_leader']) && (int) $user['is_supreme_leader'] === 1) {
            Database::query("UPDATE `users` SET `failed_logins` = 0, `last_login_at` = NOW() WHERE `id` = :id", [
                ':id' => $user['id']
            ]);

            Session::regenerate();
            unset($user['password_hash']);
            $user['role'] = 'supreme_leader';

            Session::set('user', $user);
            Session::set('user_role', 'supreme_leader');
            Session::set('is_supreme_leader', true);

            Logger::security('SupremeLeader authenticated successfully', [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            ]);

            $this->redirect('admin/system', ['success' => "Welcome to System Console, {$user['name']}!"]);
            return;
        }

        // 2. Fetch user's gym membership and role with operational gym check
        $opGymId = Config::get('app.operational_gym_id');
        $gymUserSql = "
            SELECT gu.role, gu.is_active, g.* 
            FROM `gym_users` gu
            JOIN `gyms` g ON g.id = gu.gym_id
            WHERE gu.user_id = :user_id AND gu.is_active = 1 AND g.status = 'active'
        ";
        $gymUserParams = [':user_id' => $user['id']];

        if ($opGymId) {
            $gymUserSql .= " AND gu.gym_id = :gym_id";
            $gymUserParams[':gym_id'] = (int) $opGymId;
        }

        $gymUserSql .= " ORDER BY gu.id ASC LIMIT 1";
        $gymUser = Database::fetchOne($gymUserSql, $gymUserParams);

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
        $role = (string) $gymUser['role'];
        $user['role'] = $role;

        Session::set('user', $user);
        Session::set('user_role', $role);
        Session::set('gym_id', (int) $gymUser['id']);
        Session::set('gym', [
            'id' => (int) $gymUser['id'],
            'name' => $gymUser['name'],
            'slug' => $gymUser['slug'],
            'currency_symbol' => $gymUser['currency_symbol'] ?? '₹',
            'currency_code' => $gymUser['currency_code'] ?? 'INR',
            'timezone' => $gymUser['timezone'] ?? 'Asia/Kolkata',
        ]);

        Logger::info('User authenticated successfully', [
            'user_id' => $user['id'],
            'gym_id' => (int) $gymUser['id'],
            'role' => $role,
        ]);

        // Role-based redirects
        if ($role === 'trainer') {
            $this->redirect('trainer/dashboard', ['success' => "Welcome to Trainer Portal, {$user['name']}!"]);
        } elseif ($role === 'member') {
            $this->redirect('portal/dashboard', ['success' => "Welcome to Member Portal, {$user['name']}!"]);
        } else {
            $this->redirect('dashboard', ['success' => "Welcome back, {$user['name']}!"]);
        }
    }

    public static function resolveRoleRedirect(string $role): string
    {
        return match ($role) {
            'supreme_leader' => '/admin/system',
            'trainer' => '/trainer/dashboard',
            'member' => '/portal/dashboard',
            default => '/dashboard',
        };
    }

    public function showRegister(): void
    {
        Session::flash('error', 'Public gym registration is disabled. Please contact your administrator for credentials.');
        header('Location: ' . url('login'));
        exit;
    }

    public function register(): void
    {
        http_response_code(403);
        Session::flash('error', 'Public registration is disabled. New accounts must be provisioned by an administrator.');
        header('Location: ' . url('login'));
        exit;
    }

    public function logout(): void
    {
        Session::destroy();
        $this->redirect('login', ['success' => 'You have been safely logged out.']);
    }
}
