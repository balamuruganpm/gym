<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\BillingService;
use App\Services\Csrf;
use App\Services\Database;
use App\Services\PaymentRequestService;
use App\Services\Session;
use App\Services\TrainerService;

class MemberPortalController extends Controller
{
    /**
     * Member Self-Service Portal Dashboard.
     */
    public function dashboard(): string
    {
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);
        $user = Session::get('user') ?? [];
        $gym = Session::get('gym') ?? ['name' => 'GymFlow', 'currency_symbol' => '₹'];

        // Find linked member record
        $member = Database::fetchOne("
            SELECT * FROM `members` 
            WHERE `user_id` = :user_id AND `gym_id` = :gym_id 
            LIMIT 1
        ", [':user_id' => $userId, ':gym_id' => $gymId]);

        $activeMembership = null;
        $allMemberships = [];
        $recentPayments = [];
        $outstandingBalance = 0.00;
        $totalPaid = 0.00;
        $upcomingSessions = [];
        $progressRecords = [];
        $assignedTrainer = null;
        $paymentRequests = [];
        $pendingRequestsCount = 0;

        if ($member) {
            $memberId = (int) $member['id'];

            // All memberships for this member
            $allMemberships = Database::fetchAll("
                SELECT * FROM `memberships` 
                WHERE `member_id` = :member_id AND `gym_id` = :gym_id 
                ORDER BY `id` DESC
            ", [':member_id' => $memberId, ':gym_id' => $gymId]);

            // Latest or Active Membership
            $activeMembership = Database::fetchOne("
                SELECT * FROM `memberships` 
                WHERE `member_id` = :member_id AND `gym_id` = :gym_id 
                ORDER BY CASE WHEN `status` = 'active' THEN 0 ELSE 1 END, `id` DESC 
                LIMIT 1
            ", [':member_id' => $memberId, ':gym_id' => $gymId]);

            // Total overall member balance across memberships
            $outstandingBalance = BillingService::getMemberBalance($gymId, $memberId);

            // Total paid across all valid payments
            $totalPaid = (float) (Database::fetchOne("
                SELECT COALESCE(SUM(amount), 0) as paid 
                FROM `payments` 
                WHERE `member_id` = :member_id AND `status` = 'paid' AND `gym_id` = :gym_id
            ", [':member_id' => $memberId, ':gym_id' => $gymId])['paid'] ?? 0.00);

            // Recent official payments
            $recentPayments = Database::fetchAll("
                SELECT p.*, mem.plan_name_snapshot
                FROM `payments` p
                LEFT JOIN `memberships` mem ON mem.id = p.membership_id
                WHERE p.member_id = :member_id AND p.gym_id = :gym_id 
                ORDER BY p.payment_date DESC, p.id DESC 
                LIMIT 10
            ", [':member_id' => $memberId, ':gym_id' => $gymId]);

            // Assigned Trainer
            $assignment = Database::fetchOne("
                SELECT tma.*, u.name as trainer_name, u.email as trainer_email, u.phone as trainer_phone
                FROM `trainer_member_assignments` tma
                INNER JOIN `users` u ON u.id = tma.trainer_user_id
                WHERE tma.member_id = :member_id AND tma.gym_id = :gym_id AND tma.status = 'active'
                LIMIT 1
            ", [':member_id' => $memberId, ':gym_id' => $gymId]);

            if ($assignment) {
                $assignedTrainer = $assignment;
            }

            // Training sessions (scheduled / upcoming)
            try {
                $upcomingSessions = Database::fetchAll("
                    SELECT ts.*, u.name as trainer_name
                    FROM `training_sessions` ts
                    LEFT JOIN `users` u ON u.id = ts.trainer_user_id
                    WHERE ts.member_id = :member_id AND ts.gym_id = :gym_id 
                      AND ts.session_date >= CURRENT_DATE() AND ts.status = 'scheduled'
                    ORDER BY ts.session_date ASC, ts.start_time ASC
                    LIMIT 5
                ", [':member_id' => $memberId, ':gym_id' => $gymId]);
            } catch (\Throwable $e) {
                $upcomingSessions = [];
            }

            // Training Progress History
            try {
                $progressRecords = Database::fetchAll("
                    SELECT tpr.*, u.name as trainer_name, ts.session_date
                    FROM `training_progress_records` tpr
                    INNER JOIN `users` u ON u.id = tpr.trainer_user_id
                    LEFT JOIN `training_sessions` ts ON ts.id = tpr.session_id
                    WHERE tpr.member_id = :member_id AND tpr.gym_id = :gym_id
                    ORDER BY tpr.recorded_date DESC, tpr.id DESC
                    LIMIT 10
                ", [':member_id' => $memberId, ':gym_id' => $gymId]);
            } catch (\Throwable $e) {
                $progressRecords = [];
            }

            // Payment Requests
            try {
                $paymentRequests = PaymentRequestService::getMemberRequests($gymId, $memberId);
                foreach ($paymentRequests as $req) {
                    if ($req['status'] === 'pending') {
                        $pendingRequestsCount++;
                    }
                }
            } catch (\Throwable $e) {
                $paymentRequests = [];
                $pendingRequestsCount = 0;
            }
        }

        return $this->view('portal.dashboard', [
            'pageTitle' => 'My Gym Portal — GymFlow',
            'user' => $user,
            'gym' => $gym,
            'member' => $member,
            'activeMembership' => $activeMembership,
            'allMemberships' => $allMemberships,
            'recentPayments' => $recentPayments,
            'outstandingBalance' => $outstandingBalance,
            'totalPaid' => $totalPaid,
            'assignedTrainer' => $assignedTrainer,
            'upcomingSessions' => $upcomingSessions,
            'progressRecords' => $progressRecords,
            'paymentRequests' => $paymentRequests,
            'pendingRequestsCount' => $pendingRequestsCount,
            'csrf_token' => Csrf::generate(),
        ]);
    }

    /**
     * Submit an offline payment request.
     */
    public function submitPaymentRequest(): void
    {
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);

        if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Security validation failed. Please try again.');
            $this->redirect('/portal/dashboard');
            return;
        }

        $member = Database::fetchOne("
            SELECT id FROM `members` 
            WHERE `user_id` = :user_id AND `gym_id` = :gym_id 
            LIMIT 1
        ", [':user_id' => $userId, ':gym_id' => $gymId]);

        if (!$member) {
            Session::flash('error', 'No member profile is linked to your user account.');
            $this->redirect('/portal/dashboard');
            return;
        }

        $memberId = (int) $member['id'];

        $membershipId = (int) ($_POST['membership_id'] ?? 0);
        $amount = (float) ($_POST['amount'] ?? 0);
        $paymentMethod = trim((string) ($_POST['payment_method'] ?? 'upi'));
        $paymentDate = !empty($_POST['payment_date']) ? trim((string) $_POST['payment_date']) : date('Y-m-d');
        $referenceNumber = !empty($_POST['reference_number']) ? trim((string) $_POST['reference_number']) : null;
        $notes = !empty($_POST['notes']) ? trim((string) $_POST['notes']) : null;

        try {
            $requestId = PaymentRequestService::submitRequest($gymId, $memberId, [
                'membership_id' => $membershipId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentDate,
                'reference_number' => $referenceNumber,
                'notes' => $notes,
            ]);

            Session::flash('success', "Payment request #{$requestId} submitted successfully! Your gym owner will verify and approve it shortly.");
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to submit payment request: ' . $e->getMessage());
        }

        $this->redirect('/portal/dashboard');
    }

    /**
     * View/Print payment receipt.
     */
    public function receipt(string $id): string
    {
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);
        $paymentId = (int) $id;

        $member = Database::fetchOne("
            SELECT id FROM `members` 
            WHERE `user_id` = :user_id AND `gym_id` = :gym_id 
            LIMIT 1
        ", [':user_id' => $userId, ':gym_id' => $gymId]);

        if (!$member) {
            $this->redirect('/portal/dashboard');
            return '';
        }

        $receipt = BillingService::getReceiptDetails($gymId, $paymentId);

        // Security check: ensure payment belongs to this member!
        if (!$receipt || (int) $receipt['member_id'] !== (int) $member['id']) {
            Session::flash('error', 'Receipt not found or you are not authorized to view it.');
            $this->redirect('/portal/dashboard');
            return '';
        }

        return $this->view('receipts.thermal', [
            'payment' => $receipt,
        ]);
    }

    /**
     * Update member personal contact profile (phone, address, emergency contact).
     * Strictly protects name, member number, membership dates, and financial data.
     */
    public function updateProfile(): void
    {
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);

        if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Security validation failed. Please try again.');
            $this->redirect('/portal/dashboard');
            return;
        }

        $member = Database::fetchOne("
            SELECT id FROM `members` 
            WHERE `user_id` = :user_id AND `gym_id` = :gym_id 
            LIMIT 1
        ", [':user_id' => $userId, ':gym_id' => $gymId]);

        if (!$member) {
            Session::flash('error', 'No member profile is linked to your user account.');
            $this->redirect('/portal/dashboard');
            return;
        }

        $memberId = (int) $member['id'];
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));
        $emergencyName = trim((string) ($_POST['emergency_contact_name'] ?? ''));
        $emergencyPhone = trim((string) ($_POST['emergency_contact_phone'] ?? ''));

        Database::execute("
            UPDATE `members`
            SET `phone` = :phone,
                `address` = :address,
                `emergency_contact_name` = :em_name,
                `emergency_contact_phone` = :em_phone,
                `updated_at` = NOW()
            WHERE `id` = :id AND `gym_id` = :gym_id
        ", [
            ':phone' => $phone,
            ':address' => $address,
            ':em_name' => $emergencyName,
            ':em_phone' => $emergencyPhone,
            ':id' => $memberId,
            ':gym_id' => $gymId,
        ]);

        Session::flash('success', 'Profile contact details updated successfully.');
        $this->redirect('/portal/dashboard');
    }
}
