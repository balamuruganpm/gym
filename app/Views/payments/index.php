<div class="mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-bold mb-1">Payments & Billing</h2>
            <p class="text-muted small mb-0">Record membership fees, issue verified receipts, and audit collections.</p>
        </div>
        <a href="<?= url('payments/create') ?>" class="btn gf-btn-primary fw-semibold shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Record Payment
        </a>
    </div>
</div>

<!-- Flash Alerts -->
<?= render_component('alert') ?>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="gf-card p-3">
            <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Total Collected</span>
            <div class="fs-4 fw-bold text-success mb-1"><?= format_currency($stats['total_collected']) ?></div>
            <span class="text-muted" style="font-size: 11px;">Verified gross membership revenue</span>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="gf-card p-3">
            <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Collected Today</span>
            <div class="fs-4 fw-bold text-primary mb-1"><?= format_currency($stats['today_collected']) ?></div>
            <span class="text-muted" style="font-size: 11px;">Transactions processed today</span>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="gf-card p-3">
            <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Voided / Reversed</span>
            <div class="fs-4 fw-bold text-danger mb-1"><?= format_currency($stats['total_voided']) ?></div>
            <span class="text-muted" style="font-size: 11px;">Audited and excluded from revenue</span>
        </div>
    </div>
</div>

<!-- Search & Filters -->
<div class="gf-card p-3 mb-4">
    <form action="<?= url('payments') ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input 
                    type="text" 
                    name="search" 
                    class="gf-form-control ps-5" 
                    placeholder="Search receipt #, member name, phone or ref..." 
                    value="<?= e($filters['search']) ?>"
                >
            </div>
        </div>

        <div class="col-md-3">
            <select name="method" class="gf-form-select" onchange="this.form.submit()">
                <option value="all">All Payment Methods</option>
                <option value="cash" <?= $filters['method'] === 'cash' ? 'selected' : '' ?>>Cash</option>
                <option value="upi" <?= $filters['method'] === 'upi' ? 'selected' : '' ?>>UPI / GPay / PhonePe</option>
                <option value="card" <?= $filters['method'] === 'card' ? 'selected' : '' ?>>Debit / Credit Card</option>
                <option value="bank_transfer" <?= $filters['method'] === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="status" class="gf-form-select" onchange="this.form.submit()">
                <option value="all">All Statuses</option>
                <option value="paid" <?= $filters['status'] === 'paid' ? 'selected' : '' ?>>Paid (Valid)</option>
                <option value="voided" <?= $filters['status'] === 'voided' ? 'selected' : '' ?>>Voided</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn gf-btn-primary w-100 fw-semibold">Filter</button>
            <?php if (!empty($filters['search']) || $filters['method'] !== 'all' || $filters['status'] !== 'all'): ?>
                <a href="<?= url('payments') ?>" class="btn btn-light border text-muted">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Payments Ledger Table -->
<div class="gf-card p-0 overflow-hidden">
    <?php if (empty($payments)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary"></i>
            <h5 class="fw-bold text-dark">No Payment Transactions Found</h5>
            <p class="small text-muted mb-0">No records matching your search or filters.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th class="ps-4">Receipt #</th>
                        <th>Member</th>
                        <th>Plan</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $pay): ?>
                        <tr class="<?= $pay['status'] === 'voided' ? 'bg-light text-muted' : '' ?>">
                            <td class="ps-4">
                                <a href="<?= url("payments/{$pay['id']}/receipt") ?>" class="font-monospace fw-bold text-primary text-decoration-none">
                                    <?= e($pay['receipt_number']) ?>
                                </a>
                                <?php if (!empty($pay['reference_number'])): ?>
                                    <div class="text-muted" style="font-size: 10px;">Ref: <?= e($pay['reference_number']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= url("members/{$pay['member_id']}") ?>" class="text-decoration-none fw-semibold text-dark d-block">
                                    <?= e($pay['first_name'] . ' ' . ($pay['last_name'] ?? '')) ?>
                                </a>
                                <span class="badge bg-light text-muted font-monospace border small">
                                    #<?= e($pay['member_number']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="small fw-semibold"><?= e($pay['plan_name_snapshot']) ?></span>
                            </td>
                            <td class="small">
                                <?= date('d M Y', strtotime($pay['payment_date'])) ?>
                                <div class="text-muted" style="font-size: 10px;">by <?= e($pay['recorded_by_name'] ?? 'Staff') ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase" style="font-size: 10px;">
                                    <?= e($pay['payment_method']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold <?= $pay['status'] === 'voided' ? 'text-decoration-line-through text-muted' : 'text-success' ?>">
                                    <?= format_currency((float) $pay['amount']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($pay['status'] === 'paid'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Paid</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="Reason: <?= e($pay['void_reason'] ?? '') ?>">
                                        Voided
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <a href="<?= url("payments/{$pay['id']}/receipt") ?>" class="btn btn-sm btn-light border py-1 px-2" title="View & Print Receipt">
                                        <i class="bi bi-printer"></i>
                                    </a>

                                    <?php if ($pay['status'] === 'paid' && in_array(user_role(), ['owner', 'manager'], true)): ?>
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-outline-danger py-1 px-2" 
                                            title="Void Payment"
                                            onclick="openVoidModal(<?= $pay['id'] ?>, '<?= e($pay['receipt_number']) ?>', '<?= format_currency((float) $pay['amount']) ?>')"
                                        >
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    Showing page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?> (<?= $pagination['total_items'] ?> total payments)
                </span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                <a class="page-link" href="<?= url("payments?page={$i}&search=" . urlencode($filters['search']) . "&method={$filters['method']}&status={$filters['status']}") ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Void Payment Modal -->
<div class="modal fade" id="voidPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content gf-modal-content border">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i> Void Payment Receipt
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="voidForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="alert alert-warning small border mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Voiding will cancel this receipt, subtract the amount from collected revenue, restore the member's outstanding dues, and log an immutable audit record.
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Receipt Number</label>
                        <div id="voidReceiptNum" class="font-monospace fw-bold fs-5 text-dark"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Amount to Void</label>
                        <div id="voidAmount" class="fw-bold fs-4 text-danger"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Reason for Voiding <span class="text-danger">*</span></label>
                        <textarea 
                            name="reason" 
                            class="gf-form-control" 
                            rows="2" 
                            placeholder="e.g. Accidental duplicate entry, customer cheque bounce, incorrect amount..." 
                            required
                        ></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-light border text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-semibold px-4">Confirm Void</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openVoidModal(paymentId, receiptNumber, amount) {
    document.getElementById('voidForm').action = '<?= url('payments') ?>/' + paymentId + '/void';
    document.getElementById('voidReceiptNum').textContent = receiptNumber;
    document.getElementById('voidAmount').textContent = amount;
    const modal = new bootstrap.Modal(document.getElementById('voidPaymentModal'));
    modal.show();
}
</script>
