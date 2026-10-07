<div class="row align-items-center mb-4 d-print-none">
    <div class="col-md-6">
        <a href="<?= BASE_URL ?>/admin/payments" class="btn btn-outline-secondary me-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Ledger
        </a>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="<?= BASE_URL ?>/admin/payments/thermal?id=<?= $payment['id'] ?>" target="_blank" class="btn btn-outline-info me-2">
            <i class="bi bi-receipt me-1"></i> 80mm POS Slip
        </a>
        <button onclick="window.print();" class="btn btn-primary">
            <i class="bi bi-printer-fill me-1"></i> Print / Save PDF
        </button>
    </div>
</div>

<!-- Printable Receipt Document -->
<div class="card p-4 p-md-5 border-0 shadow rounded-4 bg-white text-dark mx-auto" style="max-width: 800px;">
    <!-- Header -->
    <div class="row border-bottom pb-4 mb-4 align-items-center">
        <div class="col-sm-6">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-lightning-charge-fill fs-2 text-danger"></i>
                <h3 class="fw-bold mb-0 text-dark">FITPULSE GYM</h3>
            </div>
            <div class="text-muted small"><?= htmlspecialchars($payment['branch_name'] ?? 'Main Branch') ?></div>
            <div class="text-muted small"><?= htmlspecialchars($payment['branch_address'] ?? 'Fitness Blvd, Central City') ?></div>
            <div class="text-muted small">Ph: <?= htmlspecialchars($payment['branch_phone'] ?? '+91 9876543210') ?></div>
        </div>
        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
            <div class="badge bg-primary fs-6 px-3 py-2 mb-2">OFFICIAL RECEIPT</div>
            <div class="fs-5 fw-bold text-dark"><?= htmlspecialchars($payment['receipt_number']) ?></div>
            <div class="text-muted small">Date: <?= date('d F Y, h:i A', strtotime($payment['created_at'])) ?></div>
            <?php if (!empty($payment['transaction_id'])): ?>
                <div class="text-muted small">Txn Ref: <?= htmlspecialchars($payment['transaction_id']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Member Details -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <div class="text-secondary small fw-bold text-uppercase">Billed To:</div>
            <div class="fs-5 fw-bold text-dark"><?= htmlspecialchars($payment['member_name']) ?></div>
            <div class="text-muted small">Member Code: <span class="fw-bold text-dark font-monospace"><?= htmlspecialchars($payment['member_code']) ?></span></div>
            <div class="text-muted small">Phone: <?= htmlspecialchars($payment['member_phone']) ?></div>
            <?php if (!empty($payment['member_email'])): ?>
                <div class="text-muted small">Email: <?= htmlspecialchars($payment['member_email']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
            <div class="text-secondary small fw-bold text-uppercase">Payment Info:</div>
            <div class="fw-bold text-dark text-uppercase"><?= htmlspecialchars($payment['payment_method']) ?></div>
            <div class="text-muted small">Status: <span class="badge bg-success text-white">COMPLETED</span></div>
            <?php if (!empty($payment['start_date']) && !empty($payment['end_date'])): ?>
                <div class="text-muted small">Validity: <?= date('d M Y', strtotime($payment['start_date'])) ?> – <?= date('d M Y', strtotime($payment['end_date'])) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Itemized Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Item / Description</th>
                    <th>Plan Period</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($payment['plan_name'] ?? 'Gym Membership / Fee') ?></div>
                        <div class="text-muted small">
                            <?= !empty($payment['notes']) ? htmlspecialchars($payment['notes']) : 'Gym access, equipment & facility maintenance' ?>
                        </div>
                    </td>
                    <td>
                        <?php if (!empty($payment['duration_days'])): ?>
                            <?= $payment['duration_days'] ?> Days (<?= ucfirst($payment['duration_type']) ?>)
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td class="text-end fw-bold">₹<?= number_format($payment['amount'], 2) ?></td>
                </tr>
            </tbody>
            <tfoot>
                <?php if (!empty($payment['plan_original_price'])): ?>
                    <tr>
                        <th colspan="2" class="text-end">Plan Original Fee:</th>
                        <td class="text-end">₹<?= number_format($payment['plan_original_price'], 2) ?></td>
                    </tr>
                    <?php if (floatval($payment['plan_discount']) > 0): ?>
                        <tr>
                            <th colspan="2" class="text-end text-success">Discount:</th>
                            <td class="text-end text-success">- ₹<?= number_format($payment['plan_discount'], 2) ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endif; ?>
                <tr class="table-light fs-5">
                    <th colspan="2" class="text-end fw-bold">Amount Paid (This Receipt):</th>
                    <th class="text-end fw-bold text-success">₹<?= number_format($payment['amount'], 2) ?></th>
                </tr>
                <?php if (floatval($payment['balance_due']) > 0): ?>
                    <tr class="text-danger">
                        <th colspan="2" class="text-end fw-bold">Remaining Due Balance:</th>
                        <th class="text-end fw-bold">₹<?= number_format($payment['balance_due'], 2) ?></th>
                    </tr>
                <?php else: ?>
                    <tr class="text-success">
                        <th colspan="2" class="text-end">Balance Due:</th>
                        <th class="text-end">₹0.00 (Fully Paid)</th>
                    </tr>
                <?php endif; ?>
            </tfoot>
        </table>
    </div>

    <!-- Terms & Footer -->
    <div class="row pt-3 border-top align-items-center">
        <div class="col-8">
            <div class="small text-muted">
                <strong>Terms & Conditions:</strong><br>
                1. Fees once paid are non-refundable & non-transferable.<br>
                2. Gym rules and safety guidelines must be respected at all times.<br>
                3. This is a computer generated receipt, authorized by IronForge Management.
            </div>
        </div>
        <div class="col-4 text-end">
            <div class="p-2 border border-secondary rounded d-inline-block text-center">
                <i class="bi bi-qr-code fs-1"></i>
                <div style="font-size: 8px;" class="text-muted">VERIFIED RECEIPT</div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body {
        background: #fff !important;
        color: #000 !important;
    }
    .sidebar, .navbar, footer, .d-print-none {
        display: none !important;
    }
    .card {
        box-shadow: none !important;
        border: none !important;
        width: 100% !important;
        max-width: 100% !important;
    }
}
</style>
