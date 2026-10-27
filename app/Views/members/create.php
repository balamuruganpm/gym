<div class="mb-4">
    <a href="<?= url('members') ?>" class="text-muted small text-decoration-none">
        <i class="bi bi-arrow-left"></i> Back to Members Directory
    </a>
    <h2 class="fw-bold mt-2 mb-1">Add New Member</h2>
    <p class="text-muted small">Register a member and optionally assign their starter membership plan.</p>
</div>

<!-- Flash Error Alert -->
<?= render_component('alert') ?>

<form action="<?= url('members') ?>" method="POST" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>

    <div class="row g-4">
        <!-- Left Column: Member Personal Info -->
        <div class="col-lg-7">
            <div class="gf-card p-4 mb-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-person-badge text-primary"></i> Personal Details
                </h5>

                <div class="row g-3">
                    <!-- Member Number -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Member Number</label>
                        <input 
                            type="text" 
                            name="member_number" 
                            class="gf-form-control bg-light font-monospace fw-bold" 
                            value="<?= e(old('member_number', $nextMemberNumber)) ?>" 
                            readonly
                        >
                        <span class="text-muted" style="font-size: 11px;">Auto-generated sequential identifier.</span>
                    </div>

                    <!-- Photo Upload -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Profile Photo (Optional)</label>
                        <input type="file" name="photo" class="gf-form-control" accept="image/jpeg,image/png,image/webp">
                    </div>

                    <!-- First Name -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            name="first_name" 
                            class="gf-form-control" 
                            placeholder="e.g. Rahul" 
                            value="<?= e(old('first_name')) ?>" 
                            required
                        >
                    </div>

                    <!-- Last Name -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Last Name</label>
                        <input 
                            type="text" 
                            name="last_name" 
                            class="gf-form-control" 
                            placeholder="e.g. Verma" 
                            value="<?= e(old('last_name')) ?>"
                        >
                    </div>

                    <!-- Phone Number -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <i class="bi bi-telephone position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input 
                                type="text" 
                                name="phone" 
                                class="gf-form-control ps-5" 
                                placeholder="9876543210" 
                                value="<?= e(old('phone')) ?>" 
                                required
                            >
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Email Address (Optional)</label>
                        <div class="position-relative">
                            <i class="bi bi-envelope position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input 
                                type="email" 
                                name="email" 
                                class="gf-form-control ps-5" 
                                placeholder="member@example.com" 
                                value="<?= e(old('email')) ?>"
                            >
                        </div>
                    </div>

                    <!-- Gender -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Gender</label>
                        <select name="gender" class="gf-form-control">
                            <option value="">Select gender...</option>
                            <option value="male" <?= old('gender') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= old('gender') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <!-- Date of Birth -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="gf-form-control" value="<?= e(old('date_of_birth')) ?>">
                    </div>

                    <!-- Address -->
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Residential Address</label>
                        <textarea name="address" class="gf-form-control" rows="2" placeholder="Street, locality, city..."><?= e(old('address')) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="gf-card p-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-plus text-danger"></i> Emergency Contact (Optional)
                </h5>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Contact Person Name</label>
                        <input type="text" name="emergency_contact_name" class="gf-form-control" placeholder="e.g. Ramesh Verma" value="<?= e(old('emergency_contact_name')) ?>">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Emergency Phone</label>
                        <input type="text" name="emergency_contact_phone" class="gf-form-control" placeholder="9876543210" value="<?= e(old('emergency_contact_phone')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Fitness / Medical Notes</label>
                        <textarea name="notes" class="gf-form-control" rows="2" placeholder="Any allergies, injuries, or personal training goals..."><?= e(old('notes')) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Optional Initial Membership Plan & Payment -->
        <div class="col-lg-5">
            <div class="gf-card p-4 sticky-top" style="top: 90px;">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-card-checklist text-success"></i> Assign Initial Plan
                </h5>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Select Membership Plan</label>
                    <select name="plan_id" id="planSelect" class="gf-form-control">
                        <option value="">-- No plan now (assign later) --</option>
                        <?php foreach ($plans as $p): ?>
                            <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>" data-type="<?= $p['duration_type'] ?>" data-val="<?= $p['duration_value'] ?>">
                                <?= e($p['name']) ?> — ₹<?= number_format((float) $p['price'], 2) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="planDetailsBox" class="p-3 bg-light rounded-3 border mb-3 d-none">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Start Date</label>
                            <input type="date" name="start_date" id="startDateInput" class="gf-form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Discount (₹)</label>
                            <input type="number" step="0.01" name="discount_amount" id="discountInput" class="gf-form-control" value="0.00">
                        </div>
                    </div>

                    <div class="p-2 bg-white rounded border d-flex justify-content-between align-items-center mb-3">
                        <span class="small text-muted">Agreed Price:</span>
                        <span class="fw-bold fs-5 text-dark" id="agreedPriceDisplay">₹0.00</span>
                    </div>

                    <hr class="text-secondary opacity-25">

                    <h6 class="fw-bold small mb-2 text-dark">Initial Payment (Optional)</h6>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Amount Paid Now (₹)</label>
                        <input type="number" step="0.01" name="initial_payment" id="initialPaymentInput" class="gf-form-control" placeholder="0.00">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Method</label>
                            <select name="payment_method" class="gf-form-control">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Reference / UTR</label>
                            <input type="text" name="payment_reference" class="gf-form-control" placeholder="UPI Ref / Txn ID">
                        </div>
                    </div>
                </div>

                <button type="submit" class="gf-btn gf-btn-primary w-100 py-3 mt-3 fs-6">
                    <i class="bi bi-check-circle-fill"></i> Save Member & Register
                </button>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const planSelect = document.getElementById('planSelect');
    const planBox = document.getElementById('planDetailsBox');
    const discountInput = document.getElementById('discountInput');
    const agreedDisplay = document.getElementById('agreedPriceDisplay');
    const initialPaymentInput = document.getElementById('initialPaymentInput');

    function calculateAgreed() {
        const selected = planSelect.options[planSelect.selectedIndex];
        if (!selected || !selected.value) {
            planBox.classList.add('d-none');
            return;
        }
        planBox.classList.remove('d-none');
        const price = parseFloat(selected.getAttribute('data-price')) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const agreed = Math.max(0, price - discount);
        agreedDisplay.textContent = '₹ ' + agreed.toLocaleString('en-IN', { minimumFractionDigits: 2 });
    }

    planSelect.addEventListener('change', calculateAgreed);
    discountInput.addEventListener('input', calculateAgreed);
});
</script>
