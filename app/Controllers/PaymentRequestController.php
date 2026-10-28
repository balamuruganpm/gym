<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Csrf;
use App\Services\PaymentRequestService;
use App\Services\Session;

class PaymentRequestController extends Controller
{
    /**
     * Display offline payment requests for Owner/Staff review.
     */
    public function index(): string
    {
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);
        $user = Session::get('user') ?? [];
        $gym = Session::get('gym') ?? [];

        $filter = $_GET['status'] ?? 'pending';
        if (!in_array($filter, ['all', 'pending', 'approved', 'rejected'], true)) {
            $filter = 'pending';
        }

        $requests = PaymentRequestService::getAllRequests(
            $gymId,
            $filter === 'all' ? null : $filter
        );

        $pendingCount = PaymentRequestService::countRequests($gymId, 'pending');
        $approvedCount = PaymentRequestService::countRequests($gymId, 'approved');
        $rejectedCount = PaymentRequestService::countRequests($gymId, 'rejected');
        $totalCount = PaymentRequestService::countRequests($gymId);

        return $this->view('payment_requests.index', [
            'pageTitle' => 'Member Payment Requests — GymFlow',
            'user' => $user,
            'gym' => $gym,
            'requests' => $requests,
            'currentFilter' => $filter,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'totalCount' => $totalCount,
            'csrf_token' => Csrf::generate(),
        ]);
    }

    /**
     * Approve an offline payment request.
     */
    public function approve(string $id): void
    {
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $requestId = (int) $id;

        if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Security validation failed. Please try again.');
            $this->redirect('/payment-requests');
            return;
        }

        try {
            $paymentId = PaymentRequestService::approveRequest($gymId, $requestId, $userId);
            Session::flash('success', "Payment request #{$requestId} approved successfully! Official payment #{$paymentId} created.");
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to approve payment request: ' . $e->getMessage());
        }

        $this->redirect('/payment-requests');
    }

    /**
     * Reject an offline payment request.
     */
    public function reject(string $id): void
    {
        $gymId = (int) (Session::get('gym_id') ?? Session::get('gym')['id'] ?? 0);
        $userId = (int) (Session::get('user.id') ?? Session::get('user')['id'] ?? 0);
        $requestId = (int) $id;

        if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Security validation failed. Please try again.');
            $this->redirect('/payment-requests');
            return;
        }

        $reason = trim((string) ($_POST['rejection_reason'] ?? ''));
        if (empty($reason)) {
            Session::flash('error', 'A rejection reason is mandatory to reject a payment request.');
            $this->redirect('/payment-requests');
            return;
        }

        try {
            PaymentRequestService::rejectRequest($gymId, $requestId, $userId, $reason);
            Session::flash('success', "Payment request #{$requestId} has been rejected.");
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to reject payment request: ' . $e->getMessage());
        }

        $this->redirect('/payment-requests');
    }
}
