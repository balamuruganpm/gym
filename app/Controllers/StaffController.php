<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Database;
use App\Services\Session;
use App\Services\TenantContext;
use App\Validators\Validator;

class StaffController extends Controller
{
    public function index(): string
    {
        $gymId = TenantContext::getGymId();

        $staffList = Database::fetchAll("
            SELECT 
                u.id as user_id,
                u.name,
                u.email,
                u.phone,
                u.avatar_url,
                gu.role,
                gu.is_active,
                gu.created_at as joined_at
            FROM `gym_users` gu
            INNER JOIN `users` u ON u.id = gu.user_id
            WHERE gu.gym_id = :gym_id
            ORDER BY gu.role ASC, u.name ASC
        ", [':gym_id' => $gymId]);

        return $this->view('staff.index', [
            'pageTitle' => 'Staff & Team Access — GymFlow',
            'staffList' => $staffList,
            'currentUserId' => (int) Session::get('user.id'),
        ]);
    }

    public function store(): void
    {
        $gymId = TenantContext::getGymId();
        $currentUserRole = Session::get('user.role') ?? Session::get('user_role');

        if ($currentUserRole !== 'owner') {
            $this->back(['error' => 'Only the gym owner can add new staff members.']);
        }

        $validator = Validator::make($_POST, [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            $this->back(['errors' => $validator->errors()]);
        }

        $email = strtolower(trim($_POST['email']));
        $name = trim($_POST['name']);
        $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
        $password = (string) $_POST['password'];
        $role = in_array($_POST['role'], ['owner', 'staff', 'trainer'], true) ? $_POST['role'] : 'staff';

        try {
            Database::transaction(function (\PDO $pdo) use ($gymId, $email, $name, $phone, $password, $role) {
                // Check if user already exists globally
                $existingUser = Database::fetchOne("SELECT * FROM `users` WHERE `email` = :email LIMIT 1", [':email' => $email]);

                if ($existingUser) {
                    $userId = (int) $existingUser['id'];

                    // Check if already in this gym
                    $existingGymUser = Database::fetchOne("
                        SELECT * FROM `gym_users` WHERE `gym_id` = :gym_id AND `user_id` = :user_id LIMIT 1
                    ", [':gym_id' => $gymId, ':user_id' => $userId]);

                    if ($existingGymUser) {
                        throw new \RuntimeException("This user is already a member of this gym's staff team.");
                    }
                } else {
                    // Create user
                    $stmtUser = $pdo->prepare("
                        INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `status`, `created_at`)
                        VALUES (:name, :email, :password, :phone, 'active', NOW())
                    ");
                    $stmtUser->execute([
                        ':name' => $name,
                        ':email' => $email,
                        ':password' => password_hash($password, PASSWORD_DEFAULT),
                        ':phone' => $phone,
                    ]);
                    $userId = (int) $pdo->lastInsertId();
                }

                // Link to gym
                $stmtLink = $pdo->prepare("
                    INSERT INTO `gym_users` (`gym_id`, `user_id`, `role`, `is_active`, `created_at`)
                    VALUES (:gym_id, :user_id, :role, 1, NOW())
                ");
                $stmtLink->execute([
                    ':gym_id' => $gymId,
                    ':user_id' => $userId,
                    ':role' => $role,
                ]);
            });

            $this->redirect('staff', ['success' => "Staff member {$name} added successfully!"]);
        } catch (\Throwable $e) {
            $this->back(['error' => $e->getMessage()]);
        }
    }

    public function toggle(string $id): void
    {
        $gymId = TenantContext::getGymId();
        $currentUserRole = Session::get('user.role') ?? Session::get('user_role');

        if ($currentUserRole !== 'owner') {
            $this->back(['error' => 'Only the gym owner can modify staff access.']);
        }

        $targetUserId = (int) $id;
        $currentUserId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);

        if ($targetUserId === $currentUserId) {
            $this->back(['error' => 'You cannot deactivate your own account!']);
        }

        Database::query("
            UPDATE `gym_users` 
            SET `is_active` = 1 - `is_active`, `updated_at` = NOW()
            WHERE `gym_id` = :gym_id AND `user_id` = :user_id
        ", [
            ':gym_id' => $gymId,
            ':user_id' => $targetUserId,
        ]);

        $this->redirect('staff', ['success' => 'Staff access status updated.']);
    }
}
