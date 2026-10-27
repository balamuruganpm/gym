<div class="d-print-none mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <a href="<?= url('payments') ?>" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left"></i> Back to Payments Ledger
            </a>
            <h2 class="fw-bold mt-1 mb-0">Receipt #<?= e($receipt['receipt_number']) ?></h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Mode Toggle -->
            <div class="btn-group border rounded-3 p-1 bg-light">
                <button type="button" class="btn btn-sm btn-white active fw-semibold shadow-sm" id="btnA4" onclick="switchReceiptMode('a4')">
                    <i class="bi bi-file-earmark-text me-1"></i> A4 Invoice
                </button>
                <button type="button" class="btn btn-sm text-muted fw-semibold" id="btnThermal" onclick="switchReceiptMode('thermal')">
                    <i class="bi bi-receipt me-1"></i> 80mm Thermal
                </button>
            </div>

            <!-- Print Action -->
            <button type="button" class="btn gf-btn-primary fw-semibold shadow-sm px-3" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Receipt
            </button>
        </div>
    </div>
</div>

<!-- Flash Alerts -->
<div class="d-print-none">
    <?= render_component('alert') ?>
</div>

<!-- A4 RECEIPT CONTAINER -->
<div id="receiptA4" class="mx-auto bg-white p-5 rounded-4 shadow-sm border receipt-a4-view" style="max-width: 800px;">
    <!-- Gym Header -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="bg-primary text-white rounded-3 p-2 d-inline-flex">
                    <i class="bi bi-activity fs-4"></i>
                </div>
                <h3 class="fw-bold mb-0 text-dark"><?= e($receipt['gym_name']) ?></h3>
            </div>
            <div class="text-muted small">
                <?php if (!empty($receipt['gym_address'])): ?>
                    <div><?= nl2br(e($receipt['gym_address'])) ?></div>
                <?php endif; ?>
                <?php if (!empty($receipt['gym_phone'])): ?>
                    <div><i class="bi bi-telephone me-1"></i> <?= e($receipt['gym_phone']) ?></div>
                <?php endif; ?>
                <?php if (!empty($receipt['gym_email'])): ?>
                    <div><i class="bi bi-envelope me-1"></i> <?= e($receipt['gym_email']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-end">
            <span class="badge bg-light text-muted font-monospace border px-3 py-2 rounded-pill fs-6 mb-2 d-inline-block">
                <?= e($receipt['receipt_number']) ?>
            </span>
            <div class="text-muted small">
                <div><strong>Date:</strong> <?= date('d M Y', strtotime($receipt['payment_date'])) ?></div>
                <div><strong>Status:</strong> 
                    <span class="<?= $receipt['status'] === 'paid' ? 'text-success fw-bold' : 'text-danger fw-bold' ?>">
                        <?= strtoupper($receipt['status']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Member Info -->
    <div class="row mb-4">
        <div class="col-6">
            <h6 class="text-uppercase text-muted fw-bold small" style="font-size: 11px; letter-spacing: 0.5px;">Billed To:</h6>
            <h5 class="fw-bold mb-1"><?= e($receipt['first_name'] . ' ' . ($receipt['last_name'] ?? '')) ?></h5>
            <div class="text-muted small">
                <div><strong>Member ID:</strong> #<?= e($receipt['member_number']) ?></div>
                <div><strong>Phone:</strong> <?= e($receipt['phone']) ?></div>
                <?php if (!empty($receipt['email'])): ?>
                    <div><strong>Email:</strong> <?= e($receipt['email']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-6 text-end">
            <h6 class="text-uppercase text-muted fw-bold small" style="font-size: 11px; letter-spacing: 0.5px;">Payment Details:</h6>
            <div class="text-muted small">
                <div><strong>Method:</strong> <span class="text-uppercase"><?= e($receipt['payment_method']) ?></span></div>
                <?php if (!empty($receipt['reference_number'])): ?>
                    <div><strong>Reference/UTR:</strong> <?= e($receipt['reference_number']) ?></div>
                <?php endif; ?>
                <div><strong>Handled By:</strong> <?= e($receipt['cashier_name'] ?? 'Staff') ?></div>
            </div>
        </div>
    </div>

    <!-- Item Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Description</th>
                    <th>Period</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="fw-bold"><?= e($receipt['plan_name_snapshot']) ?></div>
                        <div class="text-muted small">Gym Membership Access Package</div>
                    </td>
                    <td class="small">
                        <?= date('d M Y', strtotime($receipt['membership_start'])) ?> &rarr; <?= date('d M Y', strtotime($receipt['membership_expiry'])) ?>
                    </td>
                    <td class="text-end fw-bold fs-5 text-dark">
                        <?= format_currency((float) $receipt['amount']) ?>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-end">Total Amount Paid</th>
                    <th class="text-end fs-4 text-success fw-bold"><?= format_currency((float) $receipt['amount']) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <?php if ($receipt['status'] === 'voided'): ?>
        <div class="alert alert-danger border mb-4">
            <h6 class="fw-bold mb-1"><i class="bi bi-x-circle me-1"></i> This receipt has been VOIDED</h6>
            <div class="small">
                <strong>Void Reason:</strong> <?= e($receipt['void_reason'] ?? 'Not specified') ?><br>
                <strong>Authorized By:</strong> <?= e($receipt['voided_by_name'] ?? 'Owner') ?> on <?= date('d M Y, h:i A', strtotime($receipt['reversed_at'])) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer Note -->
    <div class="border-top pt-4 text-center text-muted small">
        <p class="mb-1 fw-semibold">Thank you for working out with us!</p>
        <p class="mb-0" style="font-size: 11px;">This is a computer-generated receipt. GymFlow Management System.</p>
    </div>
</div>

<!-- 80MM THERMAL RECEIPT CONTAINER -->
<div id="receiptThermal" class="mx-auto bg-white p-3 rounded shadow-sm border receipt-thermal-view" style="max-width: 320px; font-family: 'Courier New', Courier, monospace; display: none;">
    <div class="text-center mb-2">
        <div class="fw-bold fs-6"><?= strtoupper(e($receipt['gym_name'])) ?></div>
        <?php if (!empty($receipt['gym_address'])): ?>
            <div style="font-size: 11px;"><?= e($receipt['gym_address']) ?></div>
        <?php endif; ?>
        <?php if (!empty($receipt['gym_phone'])): ?>
            <div style="font-size: 11px;">Tel: <?= e($receipt['gym_phone']) ?></div>
        <?php endif; ?>
        <div>================================</div>
        <div class="fw-bold">PAYMENT RECEIPT</div>
        <div>================================</div>
    </div>

    <div style="font-size: 12px;" class="mb-2">
        <div>Rcpt #: <?= e($receipt['receipt_number']) ?></div>
        <div>Date  : <?= date('d/m/Y h:i A', strtotime($receipt['payment_date'])) ?></div>
        <div>Member: <?= e($receipt['first_name'] . ' ' . ($receipt['last_name'] ?? '')) ?></div>
        <div>ID    : #<?= e($receipt['member_number']) ?></div>
        <div>Phone : <?= e($receipt['phone']) ?></div>
    </div>

    <div>--------------------------------</div>
    <div class="d-flex justify-content-between fw-bold" style="font-size: 12px;">
        <span>ITEM</span>
        <span>AMOUNT</span>
    </div>
    <div>--------------------------------</div>

    <div style="font-size: 12px;" class="mb-2">
        <div><?= e($receipt['plan_name_snapshot']) ?></div>
        <div style="font-size: 10px;" class="text-muted">
            Valid: <?= date('d/m/Y', strtotime($receipt['membership_start'])) ?> - <?= date('d/m/Y', strtotime($receipt['membership_expiry'])) ?>
        </div>
        <div class="d-flex justify-content-between fw-bold mt-1">
            <span>TOTAL PAID:</span>
            <span><?= format_currency((float) $receipt['amount']) ?></span>
        </div>
        <div style="font-size: 11px;">
            Payment: <?= strtoupper($receipt['payment_method']) ?>
            <?php if (!empty($receipt['reference_number'])): ?>
                (Ref: <?= e($receipt['reference_number']) ?>)
            <?php endif; ?>
        </div>
    </div>

    <div>================================</div>
    <div class="text-center" style="font-size: 11px;">
        <div>THANK YOU FOR YOUR VISIT!</div>
        <div>Stay Fit, Stay Healthy!</div>
        <div class="mt-1" style="font-size: 9px;">Powered by GymFlow</div>
    </div>
</div>

<style>
@media print {
    body {
        background: #fff !important;
    }
    .gf-sidebar, .gf-topbar, .d-print-none {
        display: none !important;
    }
    .gf-main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    #receiptA4, #receiptThermal {
        box-shadow: none !important;
        border: none !important;
        margin: 0 auto !important;
    }
}
</style>

<script>
function switchReceiptMode(mode) {
    const a4 = document.getElementById('receiptA4');
    const thermal = document.getElementById('receiptThermal');
    const btnA4 = document.getElementById('btnA4');
    const btnThermal = document.getElementById('btnThermal');

    if (mode === 'thermal') {
        a4.style.display = 'none';
        thermal.style.display = 'block';
        btnA4.classList.remove('active', 'shadow-sm');
        btnA4.classList.add('text-muted');
        btnThermal.classList.add('active', 'shadow-sm');
        btnThermal.classList.remove('text-muted');
    } else {
        a4.style.display = 'block';
        thermal.style.display = 'none';
        btnThermal.classList.remove('active', 'shadow-sm');
        btnThermal.classList.add('text-muted');
        btnA4.classList.add('active', 'shadow-sm');
        btnA4.classList.remove('text-muted');
    }
}
</script>
