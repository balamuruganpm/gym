<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-white mb-1"><i class="bi bi-person-plus-fill me-2 text-success"></i> New Member Registration</h3>
        <p class="text-secondary small mb-0">Create member account, assign membership plan, generate QR ID, and record initial payment</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/members" class="btn btn-fp-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Members
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="fp-card">
            <form action="<?= BASE_URL ?>/admin/members" method="POST" id="memberRegForm">
                <?= CSRF::getField() ?>

                <!-- Section 1: Basic Information -->
                <h5 class="text-success fw-bold mb-3 border-bottom border-secondary border-opacity-25 pb-2">
                    <i class="bi bi-person me-2"></i> 1. Personal & Contact Information
                </h5>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="branch_id">Gym Branch *</label>
                        <select class="form-select" id="branch_id" name="branch_id" required>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= ($user['branch_id'] == $b['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="full_name">Full Name *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" placeholder="e.g. Rahul Sharma" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="email">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="rahul@example.com" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="phone">Mobile Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="+91 98765 43210">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="dob">Date of Birth</label>
                        <input type="date" class="form-control" id="dob" name="dob">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="gender">Gender</label>
                        <select class="form-select" id="gender" name="gender">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Section 2: Security & Emergency Contacts -->
                <h5 class="text-success fw-bold mb-3 border-bottom border-secondary border-opacity-25 pb-2">
                    <i class="bi bi-shield-check me-2"></i> 2. Emergency Contact & PIN
                </h5>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="emergency_contact_name">Emergency Contact Name</label>
                        <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" placeholder="Parent / Spouse Name">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="emergency_contact_phone">Emergency Contact Phone</label>
                        <input type="text" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" placeholder="+91 98765 00000">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="pin_code">4-Digit Check-in PIN</label>
                        <input type="text" maxlength="6" class="form-control" id="pin_code" name="pin_code" value="1234">
                    </div>
                </div>

                <!-- Section 3: Membership Plan & Initial Payment -->
                <h5 class="text-success fw-bold mb-3 border-bottom border-secondary border-opacity-25 pb-2">
                    <i class="bi bi-credit-card me-2"></i> 3. Membership Plan & Initial Payment
                </h5>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="plan_id">Select Plan *</label>
                        <select class="form-select" id="plan_id" name="plan_id" onchange="updatePrice()" required>
                            <option value="">-- Choose Plan --</option>
                            <?php foreach ($plans as $p): ?>
                                <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>" data-admission="<?= $p['admission_fee'] ?>" data-duration="<?= $p['duration_days'] ?>">
                                    <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?> - <?= View::currency($p['price']) ?> (<?= $p['duration_days'] ?>d)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="start_date">Membership Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="discount_amount">Discount (<?= APP_CURRENCY_SYMBOL ?>)</label>
                        <input type="number" step="0.01" class="form-control" id="discount_amount" name="discount_amount" value="0.00" oninput="calculateTotal()">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="paid_amount">Amount Paid Today (<?= APP_CURRENCY_SYMBOL ?>)</label>
                        <input type="number" step="0.01" class="form-control" id="paid_amount" name="paid_amount" value="0.00">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="payment_method">Payment Method</label>
                        <select class="form-select" id="payment_method" name="payment_method">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
                            <option value="card">Credit / Debit Card</option>
                            <option value="netbanking">Net Banking</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="transaction_reference">Transaction Reference / UTR</label>
                        <input type="text" class="form-control" id="transaction_reference" name="transaction_reference" placeholder="e.g. UPI/123456789">
                    </div>
                </div>

                <!-- Section 4: Notes -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="medical_notes">Medical / Injury Notes (Restricted)</label>
                        <textarea class="form-control" id="medical_notes" name="medical_notes" rows="2" placeholder="Any injuries, asthma, surgery, or dietary restrictions..."></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="general_notes">General Administrative Notes</label>
                        <textarea class="form-control" id="general_notes" name="general_notes" rows="2" placeholder="Preferred workout time, personal trainer request..."></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top border-secondary border-opacity-25">
                    <a href="<?= BASE_URL ?>/admin/members" class="btn btn-fp-secondary">Cancel</a>
                    <button type="submit" class="btn btn-fp-primary btn-lg fs-6">
                        <i class="bi bi-person-check-fill me-1"></i> Complete Registration & Activate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updatePrice() {
    const select = document.getElementById('plan_id');
    const opt = select.options[select.selectedIndex];
    if (opt && opt.value) {
        const price = parseFloat(opt.dataset.price || 0);
        const admission = parseFloat(opt.dataset.admission || 0);
        const discount = parseFloat(document.getElementById('discount_amount').value || 0);
        const finalTotal = Math.max(0, (price + admission) - discount);
        document.getElementById('paid_amount').value = finalTotal.toFixed(2);
    }
}

function calculateTotal() {
    updatePrice();
}
</script>
