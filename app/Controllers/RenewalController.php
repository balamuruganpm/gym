<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\MembershipPlan;
use App\Models\Member;
use App\Services\Database;
use App\Services\MembershipService;
use App\Services\Session;
use App\Services\TenantContext;

class RenewalController extends Controller
{
    public function index(): string
    {
        $gymId = TenantContext::getGymId();
        $gym = current_gym() ?? ['name' => 'GymFlow'];

        $tab = $_GET['tab'] ?? 'expiring_7';

        if ($tab === 'expiring_30') {
            $members = MembershipService::getExpiringMembers($gymId, 30);
        } elseif ($tab === 'expired') {
            $members = MembershipService::getExpiredMembers($gymId);
        } else {
            $tab = 'expiring_7';
            $members = MembershipService::getExpiringMembers($gymId, 7);
        }

        // Attach WhatsApp link for each member
        foreach ($members as &$m) {
            $m['whatsapp_url'] = MembershipService::generateWhatsAppReminder($gym, [
                'first_name' => $m['first_name'],
                'phone' => $m['phone'],
            ], [
                'plan_name_snapshot' => $m['plan_name_snapshot'],
                'expiry_date' => $m['expiry_date'],
                'days_left' => $m['days_left'] ?? null,
                'days_overdue' => $m['days_overdue'] ?? null,
            ]);
        }
        unset($m);

        // Fetch counts for tabs
        $count7 = count(MembershipService::getExpiringMembers($gymId, 7));
        $count30 = count(MembershipService::getExpiringMembers($gymId, 30));
        $countExpired = count(MembershipService::getExpiredMembers($gymId));

        // Available active plans for quick renew modal
        $plans = MembershipPlan::getAll($gymId, true);

        return $this->view('renewals.index', [
            'pageTitle' => 'Renewals & Reminders — GymFlow',
            'tab' => $tab,
            'members' => $members,
            'plans' => $plans,
            'counts' => [
                'expiring_7' => $count7,
                'expiring_30' => $count30,
                'expired' => $countExpired,
            ]
        ]);
    }

    public function renew(): void
    {
        $gymId = TenantContext::getGymId();
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);

        $memberId = (int) ($_POST['member_id'] ?? 0);
        $planId = (int) ($_POST['plan_id'] ?? 0);
        $discount = max(0.0, (float) ($_POST['discount_amount'] ?? 0));
        $initialPayment = max(0.0, (float) ($_POST['initial_payment'] ?? 0));

        if (!$memberId || !$planId) {
            $this->back(['error' => 'Please select a member and membership plan.']);
        }

        try {
            $membershipId = Database::transaction(function (\PDO $pdo) use ($gymId, $userId, $memberId, $planId, $discount, $initialPayment) {
                // 1. Assign/Renew Plan
                $newMemId = MembershipService::renewPlan($gymId, $memberId, $planId, $discount);

                // 2. Optional immediate payment
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
                            :receipt_number, :amount, :method, CURRENT_DATE(), 
                            :ref, 'paid', NOW()
                        )
                    ");
                    $stmtPay->execute([
                        ':gym_id' => $gymId,
                        ':membership_id' => $newMemId,
                        ':member_id' => $memberId,
                        ':user_id' => $userId,
                        ':receipt_number' => $receiptNumber,
                        ':amount' => $initialPayment,
                        ':method' => $method,
                        ':ref' => !empty($_POST['payment_reference']) ? trim($_POST['payment_reference']) : null,
                    ]);
                }

                return $newMemId;
            });

            $this->redirect("members/{$memberId}", ['success' => 'Membership renewed successfully!']);
        } catch (\Throwable $e) {
            $this->back(['error' => 'Renewal failed: ' . $e->getMessage()]);
        }
    }
}
