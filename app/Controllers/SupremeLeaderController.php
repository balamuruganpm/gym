<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Database;
use App\Services\Logger;
use App\Services\Session;
use App\Validators\Validator;

class SupremeLeaderController extends Controller
{
    /**
     * System Console Dashboard.
     */
    public function system(): string
    {
        $pdo = Database::getConnection();

        // 1. Database & Environment Health
        $dbVersion = Database::fetchOne("SELECT VERSION() as v")['v'] ?? 'Unknown';
        $gymsCount = (int) (Database::fetchOne("SELECT COUNT(*) as c FROM `gyms`")['c'] ?? 0);
        $usersCount = (int) (Database::fetchOne("SELECT COUNT(*) as c FROM `users`")['c'] ?? 0);
        $membersCount = (int) (Database::fetchOne("SELECT COUNT(*) as c FROM `members`")['c'] ?? 0);

        // 2. Storage & Logs Status
        $logFiles = Logger::getLogFiles();
        $totalLogSize = array_sum(array_column($logFiles, 'size'));

        // 3. Recent Security Events
        $recentSecLogs = Logger::readLog('security-' . date('Y-m-d') . '.log', 15);

        // 4. Gyms list for Support Context or Inspection
        $gyms = Database::fetchAll("SELECT * FROM `gyms` ORDER BY id ASC");

        // 5. Disk storage availability
        $diskFree = @disk_free_space(__DIR__);
        $diskTotal = @disk_total_space(__DIR__);
        $diskFreeFormatted = $diskFree !== false ? round($diskFree / (1024 * 1024 * 1024), 2) . ' GB' : 'Available';
        $diskTotalFormatted = $diskTotal !== false ? round($diskTotal / (1024 * 1024 * 1024), 2) . ' GB' : 'Available';

        return $this->view('admin.system', [
            'pageTitle' => 'SupremeLeader System Console - GymFlow',
            'phpVersion' => PHP_VERSION,
            'dbVersion' => $dbVersion,
            'serverSoftware' => $_SERVER['SERVER_SOFTWARE'] ?? 'PHP ' . PHP_VERSION,
            'memoryLimit' => ini_get('memory_limit'),
            'uploadMax' => ini_get('upload_max_filesize'),
            'diskFree' => $diskFreeFormatted,
            'diskTotal' => $diskTotalFormatted,
            'gymsCount' => $gymsCount,
            'usersCount' => $usersCount,
            'membersCount' => $membersCount,
            'logFiles' => $logFiles,
            'totalLogSize' => round($totalLogSize / 1024, 2) . ' KB',
            'recentSecLogs' => $recentSecLogs,
            'gyms' => $gyms,
        ]);
    }

    /**
     * Show Provision Owner Screen.
     */
    public function showProvisionOwner(): string
    {
        $gyms = Database::fetchAll("SELECT id, name, slug, email FROM `gyms` WHERE `status` = 'active' ORDER BY id ASC");

        return $this->view('admin.provision_owner', [
            'pageTitle' => 'Provision Gym Owner - GymFlow Platform',
            'gyms' => $gyms,
        ]);
    }

    /**
     * Process Owner Provisioning.
     */
    public function provisionOwner(): void
    {
        $validator = Validator::make($_POST, [
            'gym_id' => 'required',
            'name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            $this->back(['errors' => $validator->errors()]);
        }

        $gymId = (int) $_POST['gym_id'];
        $name = trim($_POST['name']);
        $email = strtolower(trim($_POST['email']));
        $password = (string) $_POST['password'];
        $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;

        $gym = Database::fetchOne("SELECT * FROM `gyms` WHERE `id` = :id LIMIT 1", [':id' => $gymId]);
        if (!$gym) {
            $this->back(['error' => 'Selected gym does not exist.']);
        }

        try {
            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            $existingUser = Database::fetchOne("SELECT * FROM `users` WHERE `email` = :email FOR UPDATE", [':email' => $email]);

            if ($existingUser) {
                if (!empty($existingUser['is_supreme_leader'])) {
                    $pdo->rollBack();
                    $this->back(['error' => 'The SupremeLeader account cannot be assigned as a gym owner.']);
                }

                $userId = (int) $existingUser['id'];
                $pdo->prepare("
                    UPDATE `users` SET `name` = :name, `password_hash` = :pwd, `phone` = COALESCE(:phone, `phone`), `status` = 'active', `updated_at` = NOW()
                    WHERE `id` = :id
                ")->execute([
                    ':name' => $name,
                    ':pwd' => password_hash($password, PASSWORD_DEFAULT),
                    ':phone' => $phone,
                    ':id' => $userId,
                ]);

                $existingGu = Database::fetchOne("
                    SELECT id FROM `gym_users` WHERE `gym_id` = :gym_id AND `user_id` = :user_id FOR UPDATE
                ", [':gym_id' => $gymId, ':user_id' => $userId]);

                if ($existingGu) {
                    $pdo->prepare("
                        UPDATE `gym_users` SET `role` = 'owner', `is_active` = 1, `updated_at` = NOW() WHERE `id` = :id
                    ")->execute([':id' => $existingGu['id']]);
                } else {
                    $pdo->prepare("
                        INSERT INTO `gym_users` (`gym_id`, `user_id`, `role`, `is_active`, `created_at`)
                        VALUES (:gym_id, :user_id, 'owner', 1, NOW())
                    ")->execute([':gym_id' => $gymId, ':user_id' => $userId]);
                }
            } else {
                $stmtUser = $pdo->prepare("
                    INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `status`, `created_at`)
                    VALUES (:name, :email, :pwd, :phone, 'active', NOW())
                ");
                $stmtUser->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':pwd' => password_hash($password, PASSWORD_DEFAULT),
                    ':phone' => $phone,
                ]);
                $userId = (int) $pdo->lastInsertId();

                $pdo->prepare("
                    INSERT INTO `gym_users` (`gym_id`, `user_id`, `role`, `is_active`, `created_at`)
                    VALUES (:gym_id, :user_id, 'owner', 1, NOW())
                ")->execute([':gym_id' => $gymId, ':user_id' => $userId]);
            }

            // Audit
            $actorId = Session::get('user.id') ?? Session::get('user')['id'] ?? 0;
            $pdo->prepare("
                INSERT INTO `audit_logs` (`gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`)
                VALUES (:gym_id, :user_id, 'owner_provisioned_by_admin', 'user', :entity_id, :details, NOW())
            ")->execute([
                ':gym_id' => $gymId,
                ':user_id' => $actorId,
                ':entity_id' => $userId,
                ':details' => json_encode([
                    'owner_email' => $email,
                    'owner_name' => $name,
                    'gym_name' => $gym['name'],
                ]),
            ]);

            $pdo->commit();

            Logger::security('Owner provisioned by SupremeLeader', [
                'admin_id' => $actorId,
                'target_user_id' => $userId,
                'gym_id' => $gymId,
                'email' => $email,
            ]);

            $this->redirect('admin/system', ['success' => "Owner {$name} provisioned successfully for {$gym['name']}!"]);
        } catch (\Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->back(['error' => 'Provisioning failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Enter Audited Support Mode for a specific gym.
     */
    public function supportGym(string $id): void
    {
        $gymId = (int) $id;
        $reason = trim((string) ($_POST['reason'] ?? ''));

        if (empty($reason)) {
            $this->back(['error' => 'An explicit reason is mandatory to enter Support Mode.']);
        }

        $gym = Database::fetchOne("SELECT * FROM `gyms` WHERE `id` = :id LIMIT 1", [':id' => $gymId]);
        if (!$gym) {
            $this->back(['error' => 'Gym not found.']);
        }

        $adminId = Session::get('user.id') ?? Session::get('user')['id'] ?? 0;

        Session::set('support_mode', true);
        Session::set('support_reason', $reason);
        Session::set('gym_id', $gym['id']);
        Session::set('gym', [
            'id' => (int) $gym['id'],
            'name' => $gym['name'],
            'slug' => $gym['slug'],
            'currency_symbol' => $gym['currency_symbol'] ?? '₹',
            'currency_code' => $gym['currency_code'] ?? 'INR',
            'timezone' => $gym['timezone'] ?? 'Asia/Kolkata',
        ]);

        // Audit Log
        Database::query("
            INSERT INTO `audit_logs` (`gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`)
            VALUES (:gym_id, :user_id, 'support_mode_entered', 'gym', :gym_id, :details, NOW())
        ", [
            ':gym_id' => $gym['id'],
            ':user_id' => $adminId,
            ':details' => json_encode(['reason' => $reason]),
        ]);

        Logger::security("SupremeLeader entered Support Mode", [
            'admin_id' => $adminId,
            'target_gym_id' => $gym['id'],
            'reason' => $reason,
        ]);

        $this->redirect('dashboard', ['success' => "Entered Support Mode for {$gym['name']}. You can inspect operational records."]);
    }

    /**
     * Exit Support Mode and return to System Console.
     */
    public function exitSupport(): void
    {
        $adminId = Session::get('user.id') ?? Session::get('user')['id'] ?? 0;
        $supportedGymId = Session::get('gym_id');

        Session::remove('support_mode');
        Session::remove('support_reason');
        Session::remove('gym_id');
        Session::remove('gym');

        if ($supportedGymId) {
            Database::query("
                INSERT INTO `audit_logs` (`gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`)
                VALUES (:gym_id, :user_id, 'support_mode_exited', 'gym', :gym_id, :details, NOW())
            ", [
                ':gym_id' => $supportedGymId,
                ':user_id' => $adminId,
                ':details' => json_encode(['action' => 'exit_support']),
            ]);

            Logger::security("SupremeLeader exited Support Mode", [
                'admin_id' => $adminId,
                'exited_gym_id' => $supportedGymId,
            ]);
        }

        $this->redirect('admin/system', ['info' => 'Exited Support Mode. Returned to System Console.']);
    }

    /**
     * View Sanitized Logs.
     */
    public function logs(): string
    {
        $logFiles = Logger::getLogFiles();
        $selectedFile = trim((string) ($_GET['file'] ?? ($logFiles[0]['filename'] ?? '')));
        $level = !empty($_GET['level']) ? trim($_GET['level']) : null;
        $search = !empty($_GET['search']) ? trim($_GET['search']) : null;

        $lines = !empty($selectedFile) ? Logger::readLog($selectedFile, 200, $level, $search) : [];

        return $this->view('admin.logs', [
            'pageTitle' => 'System & Application Logs — GymFlow',
            'logFiles' => $logFiles,
            'selectedFile' => $selectedFile,
            'currentLevel' => $level,
            'searchQuery' => $search,
            'lines' => $lines,
        ]);
    }

    /**
     * Protected workflow to activate or deactivate an operational gym owner account.
     * Prevents modifying SupremeLeader accounts.
     */
    public function toggleOwnerStatus(string $id): void
    {
        $targetUserId = (int) $id;
        $adminId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);

        $user = Database::fetchOne("SELECT * FROM `users` WHERE `id` = :id LIMIT 1", [':id' => $targetUserId]);
        if (!$user) {
            $this->redirect('admin/system', ['error' => 'Target user account not found.']);
            return;
        }

        if (!empty($user['is_supreme_leader'])) {
            $this->redirect('admin/system', ['error' => 'Security Error: SupremeLeader accounts cannot be modified or disabled through this action.']);
            return;
        }

        $newStatus = ($user['status'] === 'active') ? 'inactive' : 'active';
        $newIsActive = ($newStatus === 'active') ? 1 : 0;

        Database::transaction(function (\PDO $pdo) use ($targetUserId, $adminId, $newStatus, $newIsActive, $user) {
            $pdo->prepare("UPDATE `users` SET `status` = :st, `updated_at` = NOW() WHERE `id` = :id")
                ->execute([':st' => $newStatus, ':id' => $targetUserId]);

            $pdo->prepare("UPDATE `gym_users` SET `is_active` = :act, `updated_at` = NOW() WHERE `user_id` = :id")
                ->execute([':act' => $newIsActive, ':id' => $targetUserId]);

            // Audit Trail
            $pdo->prepare("
                INSERT INTO `audit_logs` (`gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`)
                VALUES (1, :admin, :action, 'user', :target, :details, NOW())
            ")->execute([
                ':admin' => $adminId,
                ':action' => 'owner_status_toggled',
                ':target' => $targetUserId,
                ':details' => json_encode([
                    'old_status' => $user['status'],
                    'new_status' => $newStatus,
                    'target_email' => $user['email'],
                ]),
            ]);
        });

        Logger::security("Owner status toggled by SupremeLeader", [
            'admin_id' => $adminId,
            'target_user_id' => $targetUserId,
            'new_status' => $newStatus,
        ]);

        $this->redirect('admin/system', ['success' => "Owner account {$user['name']} is now {$newStatus}."]);
    }
}
