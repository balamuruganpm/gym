<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Member;
use App\Services\BillingService;
use App\Services\Database;
use App\Services\Session;
use App\Services\TenantContext;
use App\Validators\Validator;

class PaymentController extends Controller
{
    public function index(): string
    {
        $gymId = TenantContext::getGymId();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $filters = [
            'search' => trim((string) ($_GET['search'] ?? '')),
            'method' => trim((string) ($_GET['method'] ?? 'all')),
            'status' => trim((string) ($_GET['status'] ?? 'all')),
        ];

        $perPage = 15;
        $payments = BillingService::getPayments($gymId, $filters, $page, $perPage);
        $totalPayments = BillingService::countPayments($gymId, $filters);
        $totalPages = max(1, (int) ceil($totalPayments / $perPage));

        // Aggregate statistics for KPI pills
        $totalCollected = (float) Database::query("
            SELECT COALESCE(SUM(amount), 0) FROM `payments` 
            WHERE `gym_id` = :gym_id AND `status` = 'paid'
        ", [':gym_id' => $gymId])->fetchColumn();

        $totalVoided = (float) Database::query("
            SELECT COALESCE(SUM(amount), 0) FROM `payments` 
            WHERE `gym_id` = :gym_id AND `status` = 'voided'
        ", [':gym_id' => $gymId])->fetchColumn();

        $todayCollected = (float) Database::query("
            SELECT COALESCE(SUM(amount), 0) FROM `payments` 
            WHERE `gym_id` = :gym_id AND `status` = 'paid' AND `payment_date` = CURRENT_DATE()
        ", [':gym_id' => $gymId])->fetchColumn();

        return $this->view('payments.index', [
            'pageTitle' => 'Payments & Billing — GymFlow',
            'payments' => $payments,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalPayments,
                'per_page' => $perPage,
            ],
            'stats' => [
                'total_collected' => $totalCollected,
                'total_voided' => $totalVoided,
                'today_collected' => $todayCollected,
            ]
        ]);
    }

    public function create(): string
    {
        $gymId = TenantContext::getGymId();
        $preselectedMemberId = !empty($_GET['member_id']) ? (int) $_GET['member_id'] : null;

        // Fetch active members with their latest membership and balance due
        $members = Database::fetchAll("
            SELECT 
                m.id, m.member_number, m.first_name, m.last_name, m.phone,
                mem.id as membership_id, mem.plan_name_snapshot, mem.agreed_price,
                GREATEST(0, mem.agreed_price - COALESCE(p_sum.total_paid, 0)) as balance_due
            FROM `members` m
            INNER JOIN (
                SELECT mem1.*
                FROM `memberships` mem1
                INNER JOIN (
                    SELECT member_id, MAX(id) as max_id
                    FROM `memberships`
                    WHERE `gym_id` = :g1
                    GROUP BY member_id
                ) mem2 ON mem1.id = mem2.max_id
            ) mem ON mem.member_id = m.id
            LEFT JOIN (
                SELECT membership_id, SUM(amount) as total_paid
                FROM `payments`
                WHERE `status` = 'paid' AND `gym_id` = :g2
                GROUP BY membership_id
            ) p_sum ON p_sum.membership_id = mem.id
            WHERE m.gym_id = :g3 AND m.is_archived = 0
            ORDER BY m.first_name ASC
        ", [
            ':g1' => $gymId,
            ':g2' => $gymId,
            ':g3' => $gymId,
        ]);

        $receiptNumber = BillingService::generateReceiptNumber($gymId);

        return $this->view('payments.create', [
            'pageTitle' => 'Record Payment — GymFlow',
            'members' => $members,
            'preselectedMemberId' => $preselectedMemberId,
            'receiptNumber' => $receiptNumber,
        ]);
    }

    public function store(): void
    {
        $gymId = TenantContext::getGymId();
        $userId = (int) Session::get('user.id');

        $validator = Validator::make($_POST, [
            'member_id' => 'required',
            'membership_id' => 'required',
            'amount' => 'required',
            'payment_method' => 'required',
        ]);

        if ($validator->fails()) {
            $this->back(['errors' => $validator->errors()]);
        }

        try {
            $paymentId = BillingService::recordPayment($gymId, $userId, [
                'member_id' => (int) $_POST['member_id'],
                'membership_id' => (int) $_POST['membership_id'],
                'amount' => (float) $_POST['amount'],
                'payment_method' => $_POST['payment_method'],
                'payment_date' => $_POST['payment_date'] ?? date('Y-m-d'),
                'receipt_number' => $_POST['receipt_number'] ?? null,
                'reference_number' => $_POST['reference_number'] ?? null,
                'notes' => $_POST['notes'] ?? null,
            ]);

            $this->redirect("payments/{$paymentId}/receipt", ['success' => 'Payment recorded successfully!']);
        } catch (\Throwable $e) {
            $this->back(['error' => 'Payment failed: ' . $e->getMessage()]);
        }
    }

    public function receipt(string $id): string
    {
        $gymId = TenantContext::getGymId();
        $receipt = BillingService::getReceiptDetails($gymId, (int) $id);

        if (!$receipt) {
            http_response_code(404);
            return view('errors.404', ['message' => 'Payment receipt not found.']);
        }

        return $this->view('payments.receipt', [
            'pageTitle' => "Receipt #{$receipt['receipt_number']} — GymFlow",
            'receipt' => $receipt,
        ]);
    }
}
