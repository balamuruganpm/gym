<?php
$userRole = Auth::role();
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1"><i class="bi bi-wallet2 text-primary me-2"></i>Payment & Financial Ledger</h2>
        <p class="text-secondary small mb-0">Track all member payments, invoices, receipts, and payment methods</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= BASE_URL ?>/admin/payments/dues" class="btn btn-warning text-dark fw-bold">
            <i class="bi bi-exclamation-triangle me-1"></i> Due Payments (₹<?= number_format($metrics['total_pending_dues'], 2) ?>)
        </a>
        <button type="button" class="btn btn-fp-primary" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
            <i class="bi bi-plus-circle me-1"></i> Record Payment
        </button>
    </div>
</div>

<!-- Revenue Analytics Badges -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="fp-card p-3 h-100 border-start border-success border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-bold text-uppercase">TODAY'S REVENUE</div>
                    <div class="fs-4 fw-bold text-success">₹<?= number_format($metrics['today_revenue'], 2) ?></div>
                    <div class="text-secondary small"><?= $metrics['today_txns'] ?> transaction(s) today</div>
                </div>
                <div class="fs-2 text-success opacity-75"><i class="bi bi-cash-stack"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fp-card p-3 h-100 border-start border-primary border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-bold text-uppercase">THIS MONTH</div>
                    <div class="fs-4 fw-bold text-primary">₹<?= number_format($metrics['month_revenue'], 2) ?></div>
                    <div class="text-secondary small"><?= $metrics['month_txns'] ?> transaction(s) this month</div>
                </div>
                <div class="fs-2 text-primary opacity-75"><i class="bi bi-graph-up-arrow"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fp-card p-3 h-100 border-start border-warning border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-bold text-uppercase">PENDING DUES</div>
                    <div class="fs-4 fw-bold text-warning">₹<?= number_format($metrics['total_pending_dues'], 2) ?></div>
                    <div class="text-secondary small"><?= $metrics['pending_dues_count'] ?> pending member(s)</div>
                </div>
                <div class="fs-2 text-warning opacity-75"><i class="bi bi-clock-history"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fp-card p-3 h-100">
            <div class="text-secondary small fw-bold text-uppercase mb-2">PAYMENT MODES (THIS MONTH)</div>
            <div class="d-flex flex-wrap gap-1">
                <?php if (empty($metrics['methods_breakdown'])): ?>
                    <span class="text-secondary small">No data yet</span>
                <?php else: ?>
                    <?php foreach ($metrics['methods_breakdown'] as $mb): ?>
                        <span class="badge bg-secondary bg-opacity-25 text-secondary p-2">
                            <?= strtoupper($mb['payment_method']) ?>: ₹<?= number_format($mb['total_amount']) ?>
                        </span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="fp-card p-3 mb-4">
    <form method="GET" action="<?= BASE_URL ?>/admin/payments" class="row g-2 align-items-center">
        <?php if ($userRole === 'superadmin'): ?>
            <div class="col-md-3">
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">All Branches</option>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= ($filters['branch_id'] == $b['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="col-md-2">
            <select name="payment_method" class="form-select form-select-sm">
                <option value="">All Payment Modes</option>
                <option value="cash" <?= ($filters['payment_method'] === 'cash') ? 'selected' : '' ?>>Cash</option>
                <option value="upi" <?= ($filters['payment_method'] === 'upi') ? 'selected' : '' ?>>UPI / QR</option>
                <option value="card" <?= ($filters['payment_method'] === 'card') ? 'selected' : '' ?>>Card</option>
                <option value="bank_transfer" <?= ($filters['payment_method'] === 'bank_transfer') ? 'selected' : '' ?>>Bank Transfer</option>
                <option value="cheque" <?= ($filters['payment_method'] === 'cheque') ? 'selected' : '' ?>>Cheque</option>
            </select>
        </div>

        <div class="col-md-2">
            <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_from']) ?>" placeholder="From Date">
        </div>

        <div class="col-md-2">
            <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_to']) ?>" placeholder="To Date">
        </div>

        <div class="col-md-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search receipt, member..." value="<?= htmlspecialchars($filters['search']) ?>">
        </div>

        <div class="col-md-1">
            <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="bi bi-filter"></i></button>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="fp-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Receipt No.</th>
                    <th>Date & Time</th>
                    <th>Member</th>
                    <th>Plan / Purpose</th>
                    <th>Mode</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-secondary">
                            <i class="bi bi-wallet-fill fs-1 d-block mb-2 opacity-50"></i>
                            No payment transactions found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/payments/receipt?id=<?= $p['id'] ?>" class="fw-bold text-decoration-none">
                                    <i class="bi bi-file-earmark-text text-primary me-1"></i><?= htmlspecialchars($p['receipt_number']) ?>
                                </a>
                                <?php if (!empty($p['transaction_id'])): ?>
                                    <div class="text-secondary small font-monospace">Txn: <?= htmlspecialchars($p['transaction_id']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= date('d M Y', strtotime($p['created_at'])) ?></div>
                                <small class="text-secondary"><?= date('h:i A', strtotime($p['created_at'])) ?></small>
                            </td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($p['member_name']) ?></div>
                                <small class="text-secondary font-monospace"><?= htmlspecialchars($p['member_code']) ?></small>
                            </td>
                            <td>
                                <span><?= htmlspecialchars($p['plan_name'] ?? 'Custom Payment') ?></span>
                                <?php if ($p['branch_name']): ?>
                                    <div class="text-secondary fs-8"><?= htmlspecialchars($p['branch_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-25 text-secondary text-uppercase">
                                    <?= htmlspecialchars($p['payment_method']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="fs-6 fw-bold text-success">₹<?= number_format($p['amount'], 2) ?></span>
                            </td>
                            <td>
                                <span class="badge-fp-active text-uppercase fs-8"><?= htmlspecialchars($p['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/admin/payments/receipt?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="A4 Receipt">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/payments/thermal?id=<?= $p['id'] ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Thermal POS Slip">
                                    <i class="bi bi-receipt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Quick Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content fp-card border-0">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>Record New Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/admin/payments/collect" method="POST">
                <?= CSRF::getField() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Member ID or Phone *</label>
                        <input type="text" name="member_id" class="form-control" placeholder="Enter Member ID (e.g. GYM-1001) or Member DB ID" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount Paid (₹) *</label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="e.g. 1500" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Mode *</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash" selected>Cash</option>
                            <option value="upi">UPI / QR Transfer</option>
                            <option value="card">Credit / Debit Card</option>
                            <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Transaction Reference / UTR (Optional)</label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="UPI Reference or Card Txn Number">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes / Remarks (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Partial payment / Admission fee balance"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-fp-primary">Confirm & Print Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>
