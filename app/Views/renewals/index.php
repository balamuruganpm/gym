<div class="mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-bold mb-1">Renewals & Reminders</h2>
            <p class="text-muted small mb-0">Track upcoming membership expiries, send 1-click WhatsApp reminders, and process instant renewals.</p>
        </div>
        <button type="button" class="btn gf-btn-primary fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#quickRenewModal">
            <i class="bi bi-arrow-repeat me-1"></i> Quick Renew Member
        </button>
    </div>
</div>

<!-- Flash Alerts -->
<?= render_component('alert') ?>

<!-- Filter Tabs -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="<?= url('renewals?tab=expiring_7') ?>" class="btn btn-sm <?= $tab === 'expiring_7' ? 'gf-btn-primary shadow-sm' : 'btn-light border text-muted' ?> fw-semibold px-3 py-2 rounded-pill">
        <i class="bi bi-exclamation-triangle me-1"></i> Expiring in 7 Days
        <span class="badge <?= $tab === 'expiring_7' ? 'bg-light text-primary' : 'bg-secondary' ?> ms-1 rounded-pill">
            <?= $counts['expiring_7'] ?>
        </span>
    </a>

    <a href="<?= url('renewals?tab=expiring_30') ?>" class="btn btn-sm <?= $tab === 'expiring_30' ? 'gf-btn-primary shadow-sm' : 'btn-light border text-muted' ?> fw-semibold px-3 py-2 rounded-pill">
        <i class="bi bi-calendar-event me-1"></i> Expiring in 30 Days
        <span class="badge <?= $tab === 'expiring_30' ? 'bg-light text-primary' : 'bg-secondary' ?> ms-1 rounded-pill">
            <?= $counts['expiring_30'] ?>
        </span>
    </a>

    <a href="<?= url('renewals?tab=expired') ?>" class="btn btn-sm <?= $tab === 'expired' ? 'gf-btn-primary shadow-sm' : 'btn-light border text-muted' ?> fw-semibold px-3 py-2 rounded-pill">
        <i class="bi bi-x-octagon me-1"></i> Already Expired
        <span class="badge <?= $tab === 'expired' ? 'bg-light text-primary' : 'bg-danger' ?> ms-1 rounded-pill">
            <?= $counts['expired'] ?>
        </span>
    </a>
</div>

<!-- Renewals Table -->
<div class="gf-card p-0 overflow-hidden">
    <?php if (empty($members)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-check2-all fs-1 text-success d-block mb-2"></i>
            <h5 class="fw-bold text-dark">All Clear!</h5>
            <p class="small text-muted mb-0">
                <?php if ($tab === 'expired'): ?>
                    There are no expired members in this category.
                <?php else: ?>
                    No members expiring in this timeframe. Excellent retention!
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th class="ps-4">Member</th>
                        <th>Contact</th>
                        <th>Current Plan</th>
                        <th>Expiry Status</th>
                        <th>Due Balance</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $m): ?>
                        <tr>
                            <td class="ps-4">
                                <a href="<?= url("members/{$m['member_id']}") ?>" class="text-decoration-none text-dark fw-bold d-block">
                                    <?= e($m['first_name'] . ' ' . ($m['last_name'] ?? '')) ?>
                                </a>
                                <span class="badge bg-light text-muted font-monospace border small">
                                    #<?= e($m['member_number']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="tel:<?= e($m['phone']) ?>" class="text-decoration-none text-muted small d-block">
                                    <i class="bi bi-telephone text-primary me-1"></i> <?= e($m['phone']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="fw-semibold small"><?= e($m['plan_name_snapshot']) ?></span>
                            </td>
                            <td>
                                <?php if ($tab === 'expired'): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        Expired <?= $m['days_overdue'] ?> days ago
                                    </span>
                                    <div class="text-muted" style="font-size: 11px;">
                                        <?= date('d M Y', strtotime($m['expiry_date'])) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge <?= $m['days_left'] <= 3 ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' ?>">
                                        <?= $m['days_left'] == 0 ? 'Expires Today!' : "{$m['days_left']} days left" ?>
                                    </span>
                                    <div class="text-muted" style="font-size: 11px;">
                                        <?= date('d M Y', strtotime($m['expiry_date'])) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((float) $m['balance_due'] > 0): ?>
                                    <span class="fw-bold text-danger"><?= format_currency((float) $m['balance_due']) ?></span>
                                <?php else: ?>
                                    <span class="text-success small fw-semibold"><i class="bi bi-check-circle me-1"></i> Nil</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <!-- WhatsApp 1-Click Reminder -->
                                    <a 
                                        href="<?= $m['whatsapp_url'] ?>" 
                                        target="_blank" 
                                        rel="noopener noreferrer" 
                                        class="btn btn-sm btn-outline-success fw-semibold shadow-sm"
                                        title="Send WhatsApp Reminder"
                                    >
                                        <i class="bi bi-whatsapp me-1"></i> Remind
                                    </a>

                                    <!-- Quick Renew Button -->
                                    <button 
                                        type="button" 
                                        class="btn btn-sm gf-btn-primary fw-semibold shadow-sm"
                                        onclick="openRenewModal(<?= $m['member_id'] ?>, '<?= e(addslashes($m['first_name'] . ' ' . ($m['last_name'] ?? ''))) ?>', '<?= e($m['member_number']) ?>')"
                                    >
                                        <i class="bi bi-arrow-repeat me-1"></i> Renew
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Quick Renew Modal -->
<div class="modal fade" id="quickRenewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content gf-modal-content border">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-arrow-repeat text-primary me-2"></i> Renew Membership Plan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('renewals') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <!-- Member Details -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Member</label>
                        <div id="modalMemberDisplay" class="p-2 bg-light rounded border fw-bold small text-muted">
                            Select or enter member below
                        </div>
                        <input type="hidden" name="member_id" id="modalMemberId" value="">
                    </div>

                    <!-- Plan Selection -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Renewal Plan <span class="text-danger">*</span></label>
                        <select name="plan_id" class="gf-form-select" required id="modalPlanSelect">
                            <option value="">Choose plan...</option>
                            <?php foreach ($plans as $p): ?>
                                <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>">
                                    <?= e($p['name']) ?> — <?= format_currency((float) $p['price']) ?> (<?= $p['duration_value'] ?> <?= $p['duration_type'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Discount -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Discount Amount (₹)</label>
                        <input type="number" name="discount_amount" step="0.01" min="0" class="gf-form-control" placeholder="0.00">
                    </div>

                    <!-- Immediate Payment Option -->
                    <div class="p-3 bg-light rounded border mb-3">
                        <h6 class="fw-bold mb-2 small text-uppercase text-muted" style="font-size: 11px;">Immediate Payment Collection</h6>
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold">Amount Paid Now (₹)</label>
                                <input type="number" name="initial_payment" step="0.01" min="0" class="gf-form-control" placeholder="0.00">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold">Payment Mode</label>
                                <select name="payment_method" class="gf-form-select">
                                    <option value="cash">Cash</option>
                                    <option value="upi">UPI / GPay</option>
                                    <option value="card">Debit / Credit Card</option>
                                    <option value="transfer">Bank Transfer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-light border text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn gf-btn-primary fw-semibold px-4">Confirm Renewal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRenewModal(memberId, memberName, memberNumber) {
    document.getElementById('modalMemberId').value = memberId;
    document.getElementById('modalMemberDisplay').textContent = memberName + ' (#' + memberNumber + ')';
    const modal = new bootstrap.Modal(document.getElementById('quickRenewModal'));
    modal.show();
}
</script>
