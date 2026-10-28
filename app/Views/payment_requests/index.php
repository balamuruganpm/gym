<?php
/**
 * GymFlow = Member Offline Payment Requests Review
 * Modern, component-based, strictly uniform all-around borders, soft shadows.
 */
?>
<div class="container-fluid px-4 py-4">
    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-slate-900 mb-1">Offline Payment Requests</h1>
            <p class="text-muted small mb-0">Review, verify, and approve offline payments submitted by gym members.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('payments') ?>" class="btn btn-outline-secondary rounded-pill px-3 py-2 text-decoration-none shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                <span>Official Payments</span>
            </a>
        </div>
    </div>

    <!-- KPI Metric Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border rounded-4 p-3 h-100 shadow-sm bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-medium">Pending Review</span>
                    <span class="badge rounded-pill bg-warning-subtle text-warning border px-2 py-1">Action Needed</span>
                </div>
                <h3 class="fw-bold mb-0 text-slate-900"><?= number_format($pendingCount) ?></h3>
                <span class="text-muted small mt-1">Awaiting verification</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border rounded-4 p-3 h-100 shadow-sm bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-medium">Approved Requests</span>
                    <span class="badge rounded-pill bg-success-subtle text-success border px-2 py-1">Paid</span>
                </div>
                <h3 class="fw-bold mb-0 text-slate-900"><?= number_format($approvedCount) ?></h3>
                <span class="text-muted small mt-1">Official payments booked</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border rounded-4 p-3 h-100 shadow-sm bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-medium">Rejected Requests</span>
                    <span class="badge rounded-pill bg-danger-subtle text-danger border px-2 py-1">Declined</span>
                </div>
                <h3 class="fw-bold mb-0 text-slate-900"><?= number_format($rejectedCount) ?></h3>
                <span class="text-muted small mt-1">Returned with reason</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border rounded-4 p-3 h-100 shadow-sm bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-medium">Total Submitted</span>
                    <span class="badge rounded-pill bg-primary-subtle text-primary border px-2 py-1">All Time</span>
                </div>
                <h3 class="fw-bold mb-0 text-slate-900"><?= number_format($totalCount) ?></h3>
                <span class="text-muted small mt-1">Requests recorded</span>
            </div>
        </div>
    </div>

    <!-- Filter Tabs & Requests Table Card -->
    <div class="card border rounded-4 shadow-sm bg-white overflow-hidden">
        <!-- Tabs Header -->
        <div class="card-header bg-white border-bottom p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <ul class="nav nav-pills gap-2">
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 small <?= $currentFilter === 'pending' ? 'active bg-warning text-dark fw-bold' : 'text-muted' ?>" 
                       href="<?= url('payment-requests?status=pending') ?>">
                        Pending (<?= $pendingCount ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 small <?= $currentFilter === 'approved' ? 'active bg-success text-white fw-bold' : 'text-muted' ?>" 
                       href="<?= url('payment-requests?status=approved') ?>">
                        Approved (<?= $approvedCount ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 small <?= $currentFilter === 'rejected' ? 'active bg-danger text-white fw-bold' : 'text-muted' ?>" 
                       href="<?= url('payment-requests?status=rejected') ?>">
                        Rejected (<?= $rejectedCount ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 small <?= $currentFilter === 'all' ? 'active bg-primary text-white fw-bold' : 'text-muted' ?>" 
                       href="<?= url('payment-requests?status=all') ?>">
                        All Requests (<?= $totalCount ?>)
                    </a>
                </li>
            </ul>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Request #</th>
                        <th>Member</th>
                        <th>Membership Plan</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Payment Date</th>
                        <th>Reference / UTR</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                                <span class="fw-medium">No payment requests found for the selected filter.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $req): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-light text-dark border rounded-pill px-2 py-1">
                                        #<?= (int) $req['id'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-slate-900"><?= htmlspecialchars($req['first_name'] . ' ' . $req['last_name']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($req['member_number']) ?> &bull; <?= htmlspecialchars($req['member_phone'] ?? 'No phone') ?></div>
                                </td>
                                <td>
                                    <div class="fw-medium"><?= htmlspecialchars($req['plan_name_snapshot']) ?></div>
                                    <div class="text-muted small">Agreed: <?= htmlspecialchars($gym['currency_symbol'] ?? '₹') ?><?= number_format((float) $req['agreed_price'], 2) ?></div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success fs-6">
                                        <?= htmlspecialchars($gym['currency_symbol'] ?? '₹') ?><?= number_format((float) $req['amount'], 2) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-2 py-1 text-uppercase">
                                        <?= htmlspecialchars($req['payment_method']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small fw-medium"><?= htmlspecialchars(date('M d, Y', strtotime($req['payment_date']))) ?></div>
                                    <div class="text-muted" style="font-size: 11px;">Rec: <?= htmlspecialchars(date('M d, H:i', strtotime($req['created_at']))) ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($req['reference_number'])): ?>
                                        <code class="text-primary small fw-semibold"><?= htmlspecialchars($req['reference_number']) ?></code>
                                    <?php else: ?>
                                        <span class="text-muted small">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($req['status'] === 'pending'): ?>
                                        <span class="badge bg-warning-subtle text-warning border px-2 py-1 rounded-pill">
                                            <i class="bi bi-hourglass-split me-1"></i> Pending
                                        </span>
                                    <?php elseif ($req['status'] === 'approved'): ?>
                                        <span class="badge bg-success-subtle text-success border px-2 py-1 rounded-pill">
                                            <i class="bi bi-check-circle-fill me-1"></i> Approved
                                        </span>
                                        <?php if (!empty($req['approved_receipt_number'])): ?>
                                            <div class="mt-1">
                                                <a href="<?= url("payments/{$req['payment_id']}/receipt") ?>" target="_blank" class="text-decoration-none small text-primary d-inline-flex align-items-center gap-1">
                                                    <i class="bi bi-receipt"></i> <?= htmlspecialchars($req['approved_receipt_number']) ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($req['status'] === 'rejected'): ?>
                                        <span class="badge bg-danger-subtle text-danger border px-2 py-1 rounded-pill" title="<?= htmlspecialchars($req['rejection_reason'] ?? '') ?>">
                                            <i class="bi bi-x-circle-fill me-1"></i> Rejected
                                        </span>
                                        <?php if (!empty($req['rejection_reason'])): ?>
                                            <div class="text-danger small mt-1" style="max-width: 180px; font-size: 11px;">
                                                <?= htmlspecialchars($req['rejection_reason']) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <?php if ($req['status'] === 'pending'): ?>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Approve Button -->
                                            <button type="button" class="btn btn-outline-success rounded-pill px-3 py-1 me-1" 
                                                    data-bs-toggle="modal" data-bs-target="#approveModal<?= $req['id'] ?>">
                                                <i class="bi bi-check-lg me-1"></i> Approve
                                            </button>
                                            <!-- Reject Button -->
                                            <button type="button" class="btn btn-outline-danger rounded-pill px-3 py-1" 
                                                    data-bs-toggle="modal" data-bs-target="#rejectModal<?= $req['id'] ?>">
                                                <i class="bi bi-x-lg me-1"></i> Reject
                                            </button>
                                        </div>

                                        <!-- Approve Modal -->
                                        <div class="modal fade" id="approveModal<?= $req['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-start">
                                                <div class="modal-content border rounded-4 shadow">
                                                    <form method="POST" action="<?= url("payment-requests/{$req['id']}/approve") ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                        <div class="modal-header border-bottom px-4 py-3">
                                                            <h5 class="modal-title fw-bold">Approve Payment Request #<?= (int) $req['id'] ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body px-4 py-3">
                                                            <div class="alert alert-success-subtle border text-success-emphasis rounded-3 p-3 mb-3">
                                                                <i class="bi bi-info-circle-fill me-2"></i>
                                                                Approving this request will immediately record an official payment of 
                                                                <strong><?= htmlspecialchars($gym['currency_symbol'] ?? '₹') ?><?= number_format((float) $req['amount'], 2) ?></strong>
                                                                and generate a formal receipt.
                                                            </div>
                                                            <ul class="list-group list-group-flush border rounded-3 mb-3">
                                                                <li class="list-group-item d-flex justify-content-between">
                                                                    <span class="text-muted">Member</span>
                                                                    <span class="fw-bold"><?= htmlspecialchars($req['first_name'] . ' ' . $req['last_name']) ?></span>
                                                                </li>
                                                                <li class="list-group-item d-flex justify-content-between">
                                                                    <span class="text-muted">Membership</span>
                                                                    <span><?= htmlspecialchars($req['plan_name_snapshot']) ?></span>
                                                                </li>
                                                                <li class="list-group-item d-flex justify-content-between">
                                                                    <span class="text-muted">Payment Method</span>
                                                                    <span class="text-uppercase fw-semibold"><?= htmlspecialchars($req['payment_method']) ?></span>
                                                                </li>
                                                                <li class="list-group-item d-flex justify-content-between">
                                                                    <span class="text-muted">Reference / UTR</span>
                                                                    <code><?= htmlspecialchars($req['reference_number'] ?? 'N/A') ?></code>
                                                                </li>
                                                                <li class="list-group-item d-flex justify-content-between">
                                                                    <span class="text-muted">Payment Date</span>
                                                                    <span><?= htmlspecialchars($req['payment_date']) ?></span>
                                                                </li>
                                                            </ul>
                                                            <?php if (!empty($req['notes'])): ?>
                                                                <div class="mb-2">
                                                                    <label class="form-label text-muted small">Member Note:</label>
                                                                    <div class="p-2 bg-light border rounded-3 small"><?= htmlspecialchars($req['notes']) ?></div>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="modal-footer border-top px-4 py-3">
                                                            <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success rounded-pill px-4">
                                                                <i class="bi bi-check2-circle me-1"></i> Confirm & Create Payment
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal<?= $req['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-start">
                                                <div class="modal-content border rounded-4 shadow">
                                                    <form method="POST" action="<?= url("payment-requests/{$req['id']}/reject") ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                        <div class="modal-header border-bottom px-4 py-3">
                                                            <h5 class="modal-title fw-bold text-danger">Decline Payment Request #<?= (int) $req['id'] ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body px-4 py-3">
                                                            <p class="text-muted small mb-3">
                                                                Please specify the reason for declining this request. The member will see this explanation in their portal.
                                                            </p>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold small">Rejection Reason <span class="text-danger">*</span></label>
                                                                <textarea name="rejection_reason" class="form-control rounded-3" rows="3" required 
                                                                          placeholder="e.g. UTR number not found in bank statement, incorrect amount, duplicate submission..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top px-4 py-3">
                                                            <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger rounded-pill px-4">
                                                                <i class="bi bi-x-circle me-1"></i> Decline Request
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">
                                            Reviewed by <?= htmlspecialchars($req['reviewer_name'] ?? 'Staff') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
