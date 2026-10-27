<div class="mb-4">
    <a href="<?= url('payments') ?>" class="text-muted small text-decoration-none">
        <i class="bi bi-arrow-left"></i> Back to Payments
    </a>
    <h2 class="fw-bold mt-2 mb-1">Record Payment</h2>
    <p class="text-muted small">Process fee collection and issue verified receipt.</p>
</div>

<!-- Flash Alerts -->
<?= render_component('alert') ?>

<div class="row">
    <div class="col-lg-8">
        <div class="gf-card p-4">
            <form action="<?= url('payments') ?>" method="POST" novalidate id="paymentForm">
                <?= csrf_field() ?>

                <!-- Member Selection -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Select Member <span class="text-danger">*</span></label>
                    <select name="member_select" class="gf-form-select" id="memberSelect" required>
                        <option value="">-- Choose Member --</option>
                        <?php foreach ($members as $m): ?>
                            <?php $selected = ($preselectedMemberId && $preselectedMemberId === (int)$m['id']) ? 'selected' : ''; ?>
                            <option 
                                value="<?= $m['id'] ?>" 
                                data-membership-id="<?= $m['membership_id'] ?>"
                                data-plan="<?= e($m['plan_name_snapshot']) ?>"
                                data-agreed="<?= $m['agreed_price'] ?>"
                                data-due="<?= $m['balance_due'] ?>"
                                <?= $selected ?>
                            >
                                <?= e($m['first_name'] . ' ' . ($m['last_name'] ?? '')) ?> (<?= e($m['member_number']) ?>) — Due: <?= format_currency((float) $m['balance_due']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <input type="hidden" name="member_id" id="hiddenMemberId" value="">
                <input type="hidden" name="membership_id" id="hiddenMembershipId" value="">

                <!-- Active Plan Info Card (Dynamically Updated) -->
                <div id="planInfoCard" class="p-3 bg-light rounded border mb-4" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold" style="font-size: 11px;">Active Subscription</span>
                            <div class="fw-bold fs-5 text-dark" id="displayPlanName">-</div>
                        </div>
                        <div class="text-end">
                            <span class="text-muted small text-uppercase fw-semibold" style="font-size: 11px;">Pending Due</span>
                            <div class="fw-bold fs-4 text-danger" id="displayDueAmount">₹ 0.00</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <!-- Receipt Number -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Receipt Number</label>
                        <input 
                            type="text" 
                            name="receipt_number" 
                            class="gf-form-control font-monospace fw-bold" 
                            value="<?= e($receiptNumber) ?>" 
                            required
                        >
                    </div>

                    <!-- Payment Date -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Payment Date</label>
                        <input 
                            type="date" 
                            name="payment_date" 
                            class="gf-form-control" 
                            value="<?= date('Y-m-d') ?>" 
                            required
                        >
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <!-- Amount -->
                    <div class="col-sm-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0">Amount (₹) <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small" id="payFullBtn" style="font-size: 11px;">
                                Pay Full Due
                            </button>
                        </div>
                        <div class="position-relative">
                            <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fw-bold">₹</span>
                            <input 
                                type="number" 
                                name="amount" 
                                id="amountInput"
                                step="0.01" 
                                min="1" 
                                class="gf-form-control ps-5 fw-bold fs-5 text-success" 
                                placeholder="0.00" 
                                required
                            >
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="gf-form-select" required>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI / GPay / PhonePe / Paytm</option>
                            <option value="card">Debit / Credit Card (POS)</option>
                            <option value="bank_transfer">Bank Transfer (IMPS/NEFT)</option>
                        </select>
                    </div>
                </div>

                <!-- Reference / Transaction ID -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Reference / UTR / Cheque # (Optional)</label>
                    <input 
                        type="text" 
                        name="reference_number" 
                        class="gf-form-control" 
                        placeholder="e.g. UPI-UTR-2938472918"
                    >
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <label class="form-label small fw-semibold">Internal Remarks (Optional)</label>
                    <textarea 
                        name="notes" 
                        class="gf-form-control" 
                        rows="2" 
                        placeholder="Additional transaction comments..."
                    ></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn gf-btn-primary fw-semibold px-4 py-2 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Record & Generate Receipt
                    </button>
                    <a href="<?= url('payments') ?>" class="btn btn-light border px-4 py-2 text-muted">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const memberSelect = document.getElementById('memberSelect');
    const planInfoCard = document.getElementById('planInfoCard');
    const displayPlanName = document.getElementById('displayPlanName');
    const displayDueAmount = document.getElementById('displayDueAmount');
    const amountInput = document.getElementById('amountInput');
    const payFullBtn = document.getElementById('payFullBtn');
    const hiddenMemberId = document.getElementById('hiddenMemberId');
    const hiddenMembershipId = document.getElementById('hiddenMembershipId');

    function updateSelection() {
        const selected = memberSelect.options[memberSelect.selectedIndex];
        if (selected && selected.value) {
            hiddenMemberId.value = selected.value;
            hiddenMembershipId.value = selected.getAttribute('data-membership-id');
            displayPlanName.textContent = selected.getAttribute('data-plan');
            const due = parseFloat(selected.getAttribute('data-due') || '0');
            displayDueAmount.textContent = '₹ ' + due.toLocaleString('en-IN', {minimumFractionDigits: 2});
            planInfoCard.style.display = 'block';

            if (!amountInput.value || parseFloat(amountInput.value) === 0) {
                amountInput.value = due > 0 ? due : '';
            }
        } else {
            hiddenMemberId.value = '';
            hiddenMembershipId.value = '';
            planInfoCard.style.display = 'none';
        }
    }

    memberSelect.addEventListener('change', updateSelection);

    payFullBtn.addEventListener('click', function () {
        const selected = memberSelect.options[memberSelect.selectedIndex];
        if (selected && selected.getAttribute('data-due')) {
            amountInput.value = selected.getAttribute('data-due');
        }
    });

    if (memberSelect.value) {
        updateSelection();
    }
});
</script>
