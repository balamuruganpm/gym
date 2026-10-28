<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\BillingService;
use App\Services\Session;
use App\Services\TenantContext;

class PaymentReversalController extends Controller
{
    /**
     * Reversal/Void endpoint strictly restricted to gym owner and manager roles.
     */
    public function void(string $id): void
    {
        $gymId = TenantContext::getGymId();
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $userRole = (string) (Session::get('user.role') ?? Session::get('user_role') ?? '');

        if (!in_array($userRole, ['owner', 'manager'], true)) {
            $this->back(['error' => 'Unauthorized. Only Gym Owners and Managers can void payment receipts.']);
        }

        $paymentId = (int) $id;
        $reason = trim((string) ($_POST['reason'] ?? ''));

        if (empty($reason)) {
            $this->back(['error' => 'A valid reason is mandatory to void a payment.']);
        }

        try {
            BillingService::voidPayment($gymId, $paymentId, $userId, $reason);
            $this->redirect('payments', ['success' => "Payment #{$paymentId} was successfully voided. Audit log generated."]);
        } catch (\Throwable $e) {
            $this->back(['error' => 'Void operation failed: ' . $e->getMessage()]);
        }
    }
}
