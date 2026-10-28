<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Member;
use App\Services\Database;
use App\Services\Session;
use App\Services\TenantContext;
use App\Validators\Validator;

class MemberController extends Controller
{
    public function index(): string
    {
        $gymId = TenantContext::getGymId();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $filters = [
            'search' => trim((string) ($_GET['search'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? 'all')),
        ];

        $perPage = 15;
        $members = Member::getAll($gymId, $filters, $page, $perPage);
        $totalMembers = Member::countAll($gymId, $filters);
        $totalPages = max(1, (int) ceil($totalMembers / $perPage));

        // Quick counts for status pills
        $activeCount = (int) Database::query("
            SELECT COUNT(DISTINCT member_id) FROM `memberships` 
            WHERE `gym_id` = :gym_id AND `status` = 'active' AND CURRENT_DATE() BETWEEN `start_date` AND `expiry_date`
        ", [':gym_id' => $gymId])->fetchColumn();

        $expiringCount = (int) Database::query("
            SELECT COUNT(DISTINCT member_id) FROM `memberships` 
            WHERE `gym_id` = :gym_id AND `status` = 'active' 
              AND `expiry_date` BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY)
        ", [':gym_id' => $gymId])->fetchColumn();

        $expiredCount = (int) Database::query("
            SELECT COUNT(DISTINCT member_id) FROM `memberships` 
            WHERE `gym_id` = :gym_id AND `status` = 'expired'
        ", [':gym_id' => $gymId])->fetchColumn();

        $archivedCount = (int) Database::query("
            SELECT COUNT(*) FROM `members` WHERE `gym_id` = :gym_id AND `is_archived` = 1
        ", [':gym_id' => $gymId])->fetchColumn();

        return $this->view('members.index', [
            'pageTitle' => 'Members Directory — GymFlow',
            'members' => $members,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalMembers,
                'per_page' => $perPage,
            ],
            'counts' => [
                'all' => Member::countAll($gymId, ['status' => 'all']),
                'active' => $activeCount,
                'expiring_soon' => $expiringCount,
                'expired' => $expiredCount,
                'archived' => $archivedCount,
            ]
        ]);
    }

    public function create(): string
    {
        $gymId = TenantContext::getGymId();
        $nextMemberNumber = Member::generateMemberNumber($gymId);

        // Fetch active plans for optional immediate plan assignment
        $plans = Database::fetchAll("
            SELECT * FROM `membership_plans` 
            WHERE `gym_id` = :gym_id AND `is_active` = 1 
            ORDER BY `price` ASC
        ", [':gym_id' => $gymId]);

        return $this->view('members.create', [
            'pageTitle' => 'Add New Member — GymFlow',
            'nextMemberNumber' => $nextMemberNumber,
            'plans' => $plans,
        ]);
    }

    public function store(): void
    {
        $gymId = TenantContext::getGymId();
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);

        $validator = Validator::make($_POST, [
            'first_name' => 'required|min:2|max:80',
            'phone' => 'required|min:8|max:20',
        ]);

        if ($validator->fails()) {
            $this->back(['errors' => $validator->errors()]);
        }

        // Handle Photo Upload
        $photoPath = $this->handlePhotoUpload();

        $memberNumber = trim($_POST['member_number'] ?? '');
        if (empty($memberNumber)) {
            $memberNumber = Member::generateMemberNumber($gymId);
        }

        try {
            $memberId = Database::transaction(function (\PDO $pdo) use ($gymId, $userId, $memberNumber, $photoPath) {
                // 1. Create Member
                $stmt = $pdo->prepare("
                    INSERT INTO `members` (
                        `gym_id`, `member_number`, `first_name`, `last_name`, 
                        `phone`, `email`, `gender`, `date_of_birth`, 
                        `address`, `emergency_contact_name`, `emergency_contact_phone`, 
                        `photo_path`, `notes`, `is_archived`, `created_at`
                    ) VALUES (
                        :gym_id, :member_number, :first_name, :last_name, 
                        :phone, :email, :gender, :date_of_birth, 
                        :address, :emergency_contact_name, :emergency_contact_phone, 
                        :photo_path, :notes, 0, NOW()
                    )
                ");

                $dob = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;

                $stmt->execute([
                    ':gym_id' => $gymId,
                    ':member_number' => $memberNumber,
                    ':first_name' => trim($_POST['first_name']),
                    ':last_name' => !empty($_POST['last_name']) ? trim($_POST['last_name']) : null,
                    ':phone' => trim($_POST['phone']),
                    ':email' => !empty($_POST['email']) ? strtolower(trim($_POST['email'])) : null,
                    ':gender' => !empty($_POST['gender']) ? $_POST['gender'] : null,
                    ':date_of_birth' => $dob,
                    ':address' => !empty($_POST['address']) ? trim($_POST['address']) : null,
                    ':emergency_contact_name' => !empty($_POST['emergency_contact_name']) ? trim($_POST['emergency_contact_name']) : null,
                    ':emergency_contact_phone' => !empty($_POST['emergency_contact_phone']) ? trim($_POST['emergency_contact_phone']) : null,
                    ':photo_path' => $photoPath,
                    ':notes' => !empty($_POST['notes']) ? trim($_POST['notes']) : null,
                ]);

                $newMemberId = (int) $pdo->lastInsertId();

                // 2. Optional: Assign Membership Plan if selected
                $planId = !empty($_POST['plan_id']) ? (int) $_POST['plan_id'] : null;
                if ($planId) {
                    $stmtPlan = $pdo->prepare("SELECT * FROM `membership_plans` WHERE `id` = :id AND `gym_id` = :gym_id LIMIT 1");
                    $stmtPlan->execute([':id' => $planId, ':gym_id' => $gymId]);
                    $plan = $stmtPlan->fetch();

                    if ($plan) {
                        $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d');
                        $durationVal = (int) $plan['duration_value'];
                        $durationType = $plan['duration_type'];

                        if ($durationType === 'months') {
                            $expiryDate = date('Y-m-d', strtotime("+{$durationVal} months", strtotime($startDate)));
                        } else {
                            $expiryDate = date('Y-m-d', strtotime("+{$durationVal} days", strtotime($startDate)));
                        }

                        $originalPrice = (float) $plan['price'];
                        $discount = max(0.0, (float) ($_POST['discount_amount'] ?? 0));
                        $agreedPrice = max(0.0, $originalPrice - $discount);

                        $stmtMem = $pdo->prepare("
                            INSERT INTO `memberships` (
                                `gym_id`, `member_id`, `plan_id`, `plan_name_snapshot`, 
                                `start_date`, `expiry_date`, `original_price`, `discount_amount`, 
                                `agreed_price`, `status`, `created_at`
                            ) VALUES (
                                :gym_id, :member_id, :plan_id, :plan_name, 
                                :start_date, :expiry_date, :orig_price, :discount, 
                                :agreed_price, 'active', NOW()
                            )
                        ");

                        $stmtMem->execute([
                            ':gym_id' => $gymId,
                            ':member_id' => $newMemberId,
                            ':plan_id' => $plan['id'],
                            ':plan_name' => $plan['name'],
                            ':start_date' => $startDate,
                            ':expiry_date' => $expiryDate,
                            ':orig_price' => $originalPrice,
                            ':discount' => $discount,
                            ':agreed_price' => $agreedPrice,
                        ]);
                        $membershipId = (int) $pdo->lastInsertId();

                        // 3. Optional Initial Payment
                        $initialPayment = (float) ($_POST['initial_payment'] ?? 0);
                        if ($initialPayment > 0) {
                            $receiptNumber = sprintf('REC-%s-%04d', date('Y'), rand(100, 9999));
                            $method = $_POST['payment_method'] ?? 'cash';
                            $stmtPay = $pdo->prepare("
                                INSERT INTO `payments` (
                                    `gym_id`, `membership_id`, `member_id`, `user_id`, 
                                    `receipt_number`, `amount`, `payment_method`, `payment_date`, 
                                    `reference_number`, `status`, `created_at`
                                ) VALUES (
                                    :gym_id, :membership_id, :member_id, :user_id, 
                                    :receipt_number, :amount, :method, :payment_date, 
                                    :ref, 'paid', NOW()
                                )
                            ");
                            $stmtPay->execute([
                                ':gym_id' => $gymId,
                                ':membership_id' => $membershipId,
                                ':member_id' => $newMemberId,
                                ':user_id' => $userId,
                                ':receipt_number' => $receiptNumber,
                                ':amount' => $initialPayment,
                                ':method' => $method,
                                ':payment_date' => $startDate,
                                ':ref' => !empty($_POST['payment_reference']) ? trim($_POST['payment_reference']) : null,
                            ]);
                        }
                    }
                }

                return $newMemberId;
            });

            $this->redirect("members/{$memberId}", ['success' => "Member {$memberNumber} registered successfully!"]);
        } catch (\Throwable $e) {
            $this->back(['error' => 'Error adding member: ' . $e->getMessage()]);
        }
    }

    public function show(string $id): string
    {
        $gymId = TenantContext::getGymId();
        $profile = Member::getProfile((int) $id, $gymId);

        if (!$profile) {
            http_response_code(404);
            return view('errors.404', ['message' => 'Member profile not found.']);
        }

        return $this->view('members.show', [
            'pageTitle' => "{$profile['member']['first_name']} {$profile['member']['last_name']} — Member Profile",
            'profile' => $profile,
            'gym' => current_gym(),
        ]);
    }

    public function edit(string $id): string
    {
        $gymId = TenantContext::getGymId();
        $member = Member::find((int) $id, $gymId);

        if (!$member) {
            http_response_code(404);
            return view('errors.404', ['message' => 'Member not found.']);
        }

        return $this->view('members.edit', [
            'pageTitle' => "Edit Member #{$member['member_number']} — GymFlow",
            'member' => $member,
        ]);
    }

    public function update(string $id): void
    {
        $gymId = TenantContext::getGymId();
        $memberId = (int) $id;

        $member = Member::find($memberId, $gymId);
        if (!$member) {
            http_response_code(404);
            exit('Member not found.');
        }

        $validator = Validator::make($_POST, [
            'first_name' => 'required|min:2|max:80',
            'phone' => 'required|min:8|max:20',
        ]);

        if ($validator->fails()) {
            $this->back(['errors' => $validator->errors()]);
        }

        $photoPath = $this->handlePhotoUpload() ?? $member['photo_path'];

        $dob = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;

        Member::update($memberId, $gymId, [
            'first_name' => trim($_POST['first_name']),
            'last_name' => !empty($_POST['last_name']) ? trim($_POST['last_name']) : null,
            'phone' => trim($_POST['phone']),
            'email' => !empty($_POST['email']) ? strtolower(trim($_POST['email'])) : null,
            'gender' => !empty($_POST['gender']) ? $_POST['gender'] : null,
            'date_of_birth' => $dob,
            'address' => !empty($_POST['address']) ? trim($_POST['address']) : null,
            'emergency_contact_name' => !empty($_POST['emergency_contact_name']) ? trim($_POST['emergency_contact_name']) : null,
            'emergency_contact_phone' => !empty($_POST['emergency_contact_phone']) ? trim($_POST['emergency_contact_phone']) : null,
            'photo_path' => $photoPath,
            'notes' => !empty($_POST['notes']) ? trim($_POST['notes']) : null,
        ]);

        $this->redirect("members/{$memberId}", ['success' => 'Member details updated successfully!']);
    }

    public function archive(string $id): void
    {
        $gymId = TenantContext::getGymId();
        $memberId = (int) $id;

        Member::archive($memberId, $gymId);
        $this->redirect('members', ['success' => 'Member archived safely. Financial history preserved.']);
    }

    public function restore(string $id): void
    {
        $gymId = TenantContext::getGymId();
        $memberId = (int) $id;

        Member::restore($memberId, $gymId);
        $this->redirect("members/{$memberId}", ['success' => 'Member restored successfully!']);
    }

    /**
     * Safely upload member avatar photo.
     */
    private function handlePhotoUpload(): ?string
    {
        if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES['photo'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxSize) {
            return null;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes, true)) {
            return null;
        }

        $ext = match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $filename = 'member_' . uniqid('', true) . '.' . $ext;
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/members';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $targetPath = $uploadDir . '/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'uploads/members/' . $filename;
        }

        return null;
    }

    /**
     * Provision a user login account for a gym member to access the Member Portal.
     */
    public function createPortalAccount(string $id): void
    {
        $gymId = TenantContext::getGymId();
        $memberId = (int) $id;

        $member = Member::getById($memberId, $gymId);
        if (!$member) {
            $this->redirect('members', ['error' => 'Member not found.']);
            return;
        }

        if (!empty($member['user_id'])) {
            $this->redirect("members/{$memberId}", ['error' => 'This member already has a portal user account linked.']);
            return;
        }

        $email = trim((string) ($_POST['email'] ?? $member['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect("members/{$memberId}", ['error' => 'A valid email address is required to create a portal account.']);
            return;
        }

        if (strlen($password) < 8) {
            $this->redirect("members/{$memberId}", ['error' => 'Password must be at least 8 characters long.']);
            return;
        }

        // Check if email already used in users
        $existingUser = Database::fetchOne("SELECT id FROM `users` WHERE `email` = :e LIMIT 1", [':e' => $email]);
        if ($existingUser) {
            $this->redirect("members/{$memberId}", ['error' => 'A user account with this email already exists.']);
            return;
        }

        try {
            Database::transaction(function (\PDO $pdo) use ($gymId, $memberId, $member, $email, $password) {
                // 1. Create user
                $name = trim($member['first_name'] . ' ' . $member['last_name']);
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `status`, `created_at`)
                    VALUES (:name, :email, :hash, :phone, 'active', NOW())
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':hash' => $hash,
                    ':phone' => $member['phone'],
                ]);

                $newUserId = (int) $pdo->lastInsertId();

                // 2. Link in gym_users with role 'member'
                $stmtGu = $pdo->prepare("
                    INSERT INTO `gym_users` (`gym_id`, `user_id`, `role`, `is_active`, `created_at`)
                    VALUES (:g, :u, 'member', 1, NOW())
                ");
                $stmtGu->execute([':g' => $gymId, ':u' => $newUserId]);

                // 3. Link user_id in members
                $stmtMem = $pdo->prepare("
                    UPDATE `members` SET `user_id` = :u, `updated_at` = NOW()
                    WHERE `id` = :id AND `gym_id` = :g
                ");
                $stmtMem->execute([':u' => $newUserId, ':id' => $memberId, ':g' => $gymId]);

                // 4. Audit Log
                $currentActorId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
                $stmtAudit = $pdo->prepare("
                    INSERT INTO `audit_logs` (`gym_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `created_at`)
                    VALUES (:g, :u, 'member.portal_account_created', 'member', :id, :details, NOW())
                ");
                $stmtAudit->execute([
                    ':g' => $gymId,
                    ':u' => $currentActorId ?: null,
                    ':id' => $memberId,
                    ':details' => json_encode(['email' => $email, 'user_id' => $newUserId]),
                ]);
            });

            $this->redirect("members/{$memberId}", ['success' => 'Portal account created successfully! The member can now log in at /login.']);
        } catch (\Throwable $e) {
            $this->redirect("members/{$memberId}", ['error' => 'Failed to create portal account: ' . $e->getMessage()]);
        }
    }
}
