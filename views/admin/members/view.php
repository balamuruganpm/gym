<?php
$statusClass = 'badge-fp-active';
if ($member['current_status'] === 'expiring_soon') $statusClass = 'badge-fp-expiring';
elseif ($member['current_status'] === 'expired') $statusClass = 'badge-fp-expired';
elseif ($member['current_status'] === 'frozen') $statusClass = 'badge-fp-frozen';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-white mb-1"><?= htmlspecialchars($member['full_name'], ENT_QUOTES, 'UTF-8') ?></h3>
        <p class="text-secondary small mb-0">Member Code: <span class="badge bg-secondary"><?= htmlspecialchars($member['member_code'], ENT_QUOTES, 'UTF-8') ?></span> | Branch: <?= htmlspecialchars($member['branch_name'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-fp-primary btn-sm" data-bs-toggle="modal" data-bs-target="#renewModal">
            <i class="bi bi-arrow-repeat me-1"></i> Renew Membership
        </button>
        <button type="button" class="btn btn-fp-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#freezeModal">
            <i class="bi bi-snow me-1"></i> Freeze Membership
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Left Column: Profile Card & QR Pass -->
    <div class="col-12 col-lg-4">
        <div class="fp-card mb-4 text-center">
            <div class="bg-success bg-opacity-25 rounded-circle p-3 mx-auto mb-3 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                <i class="bi bi-person-fill text-success fs-1"></i>
            </div>
            <h4 class="fw-bold text-white mb-1"><?= htmlspecialchars($member['full_name'], ENT_QUOTES, 'UTF-8') ?></h4>
            <div class="mb-3">
                <span class="<?= $statusClass ?> text-uppercase fw-bold"><?= str_replace('_', ' ', $member['current_status']) ?></span>
            </div>

            <!-- Dynamic QR Code Representation -->
            <div class="p-3 bg-dark rounded-3 border border-secondary border-opacity-25 mb-3">
                <div class="text-secondary fs-8 text-uppercase mb-2">Member Digital Check-in QR</div>
                <div class="bg-white p-3 rounded-2 d-inline-block shadow-sm">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=<?= urlencode($member['qr_token']) ?>" alt="Member QR Code" style="width: 140px; height: 140px;" class="img-fluid">
                </div>
                <div class="mt-2 text-secondary font-monospace small">PIN: <strong><?= htmlspecialchars($member['pin_code'] ?: '1234', ENT_QUOTES, 'UTF-8') ?></strong></div>
            </div>

            <div class="text-start fs-8">
                <div class="d-flex justify-content-between py-1 border-bottom border-secondary border-opacity-25">
                    <span class="text-secondary">Email</span>
                    <span class="text-white"><?= htmlspecialchars($member['email'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom border-secondary border-opacity-25">
                    <span class="text-secondary">Phone</span>
                    <span class="text-white"><?= htmlspecialchars($member['phone'] ?: '-', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom border-secondary border-opacity-25">
                    <span class="text-secondary">Joined Date</span>
                    <span class="text-white"><?= date('d M Y', strtotime($member['joining_date'])) ?></span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom border-secondary border-opacity-25">
                    <span class="text-secondary">Gender / DOB</span>
                    <span class="text-white text-capitalize"><?= htmlspecialchars($member['gender']) ?> / <?= $member['dob'] ? date('d M Y', strtotime($member['dob'])) : '-' ?></span>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-secondary">Emergency Contact</span>
                    <span class="text-white"><?= htmlspecialchars($member['emergency_contact_name'] ?: '-') ?> (<?= htmlspecialchars($member['emergency_contact_phone'] ?: '-') ?>)</span>
                </div>
            </div>
        </div>

        <?php if (!empty($member['medical_notes'])): ?>
            <div class="fp-card border-warning border-opacity-50">
                <h6 class="fw-bold text-warning mb-2"><i class="bi bi-shield-exclamation me-1"></i> Medical / Injury Notes</h6>
                <p class="text-secondary small mb-0"><?= nl2br(htmlspecialchars($member['medical_notes'], ENT_QUOTES, 'UTF-8')) ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Membership Status & Histories -->
    <div class="col-12 col-lg-8">
        <!-- Active Subscription Summary Card -->
        <div class="fp-card mb-4 bg-gradient" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-white mb-0"><i class="bi bi-card-checklist text-success me-2"></i> Current Subscription</h5>
                <span class="<?= $statusClass ?> text-uppercase"><?= str_replace('_', ' ', $member['current_status']) ?></span>
            </div>

            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <span class="text-secondary fs-8 d-block">Plan Package</span>
                    <strong class="text-white"><?= htmlspecialchars($member['plan_name'] ?: 'None', ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-secondary fs-8 d-block">Start Date</span>
                    <strong class="text-white"><?= $member['start_date'] ? date('d M Y', strtotime($member['start_date'])) : '-' ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-secondary fs-8 d-block">Expiry Date</span>
                    <strong class="text-white"><?= $member['end_date'] ? date('d M Y', strtotime($member['end_date'])) : '-' ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-secondary fs-8 d-block">Validity Balance</span>
                    <?php if ($member['end_date']): ?>
                        <?php if ($member['days_remaining'] > 0): ?>
                            <strong class="text-success"><?= (int)$member['days_remaining'] ?> Days Left</strong>
                        <?php else: ?>
                            <strong class="text-danger">Expired</strong>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-secondary">-</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Payment History Table -->
        <div class="fp-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-white mb-0"><i class="bi bi-receipt text-primary me-2"></i> Payment History</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-secondary fs-8 text-uppercase">
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody class="fs-7">
                        <?php if (empty($payments)): ?>
                            <tr><td colspan="5" class="text-center py-3 text-secondary">No payment transactions recorded.</td></tr>
                        <?php else: ?>
                            <?php foreach ($payments as $pay): ?>
                                <tr>
                                    <td><span class="badge bg-secondary bg-opacity-25 font-monospace"><?= htmlspecialchars($pay['receipt_number']) ?></span></td>
                                    <td><?= date('d M Y', strtotime($pay['payment_date'])) ?></td>
                                    <td class="text-success fw-bold"><?= View::currency($pay['amount']) ?></td>
                                    <td><span class="badge bg-dark border border-secondary border-opacity-50 text-uppercase fs-8"><?= htmlspecialchars($pay['payment_method']) ?></span></td>
                                    <td class="text-secondary small"><?= htmlspecialchars($pay['notes'] ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Attendance Logs Table -->
        <div class="fp-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-white mb-0"><i class="bi bi-calendar2-check text-success me-2"></i> Recent Check-ins</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-secondary fs-8 text-uppercase">
                            <th>Date</th>
                            <th>Check-in Time</th>
                            <th>Check-out Time</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="fs-7">
                        <?php if (empty($attendance)): ?>
                            <tr><td colspan="5" class="text-center py-3 text-secondary">No attendance recorded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($attendance as $att): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($att['attendance_date'])) ?></td>
                                    <td class="text-white"><?= date('h:i A', strtotime($att['check_in_time'])) ?></td>
                                    <td class="text-secondary"><?= $att['check_out_time'] ? date('h:i A', strtotime($att['check_out_time'])) : '-' ?></td>
                                    <td><span class="badge bg-dark border border-secondary border-opacity-50 text-uppercase fs-8"><?= htmlspecialchars($att['method']) ?></span></td>
                                    <td><span class="badge-fp-active">Present</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Renew Membership -->
<div class="modal fade" id="renewModal" tabindex="-1" aria-labelledby="renewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title text-white" id="renewModalLabel"><i class="bi bi-arrow-repeat text-success me-2"></i> Renew Membership</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/admin/members/<?= $member['id'] ?>/renew" method="POST">
                <?= CSRF::getField() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="renew_plan_id">Select Renewal Plan *</label>
                        <select class="form-select" id="renew_plan_id" name="plan_id" onchange="updateRenewPrice()" required>
                            <?php foreach ($plans as $p): ?>
                                <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>" <?= ($member['plan_id'] == $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?> - <?= View::currency($p['price']) ?> (<?= $p['duration_days'] ?>d)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label" for="renew_start_date">Start Date</label>
                            <input type="date" class="form-control" id="renew_start_date" name="start_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="renew_discount">Discount (<?= APP_CURRENCY_SYMBOL ?>)</label>
                            <input type="number" step="0.01" class="form-control" id="renew_discount" name="discount_amount" value="0.00" oninput="updateRenewPrice()">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label" for="renew_paid_amount">Amount Paid Today (<?= APP_CURRENCY_SYMBOL ?>) *</label>
                            <input type="number" step="0.01" class="form-control" id="renew_paid_amount" name="paid_amount" value="<?= $member['plan_price'] ?: '1500.00' ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="renew_payment_method">Payment Mode</label>
                            <select class="form-select" id="renew_payment_method" name="payment_method">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="card">Card</option>
                                <option value="netbanking">Net Banking</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-fp-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-fp-primary"><i class="bi bi-check-lg me-1"></i> Confirm Renewal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Freeze Membership -->
<div class="modal fade" id="freezeModal" tabindex="-1" aria-labelledby="freezeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title text-white" id="freezeModalLabel"><i class="bi bi-snow text-info me-2"></i> Freeze Membership</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/admin/members/<?= $member['id'] ?>/freeze" method="POST">
                <?= CSRF::getField() ?>
                <div class="modal-body">
                    <p class="text-secondary small mb-3">
                        Freezing a membership will temporarily pause check-in access and automatically extend the member's expiry date forward by the frozen number of days.
                    </p>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label" for="freeze_start">Freeze Start Date *</label>
                            <input type="date" class="form-control" id="freeze_start" name="freeze_start" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="freeze_end">Freeze End Date *</label>
                            <input type="date" class="form-control" id="freeze_end" name="freeze_end" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="reason">Reason for Freeze</label>
                        <input type="text" class="form-control" id="reason" name="reason" placeholder="Vacation, medical recovery, travel, etc." required>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-fp-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold"><i class="bi bi-snow me-1"></i> Confirm Freeze</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateRenewPrice() {
    const sel = document.getElementById('renew_plan_id');
    const opt = sel.options[sel.selectedIndex];
    if (opt) {
        const price = parseFloat(opt.dataset.price || 0);
        const discount = parseFloat(document.getElementById('renew_discount').value || 0);
        document.getElementById('renew_paid_amount').value = Math.max(0, price - discount).toFixed(2);
    }
}
</script>
