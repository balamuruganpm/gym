<?php
$member = $profile['member'];
$currentSub = $profile['active_membership'] ?? null;
$subscriptions = $profile['memberships'] ?? [];
$payments = $profile['payments'] ?? [];
$totalPaid = (float) ($profile['total_paid_overall'] ?? 0);
$outstandingBalance = (float) ($profile['total_due_overall'] ?? 0);

$fullName = trim($member['first_name'] . ' ' . ($member['last_name'] ?? ''));
$initials = strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'] ?? '', 0, 1));

// WhatsApp link generator
$cleanPhone = preg_replace('/[^0-9]/', '', $member['phone']);
if (strlen($cleanPhone) === 10) {
    $cleanPhone = '91' . $cleanPhone; // default to country code
}
$whatsappMsg = rawurlencode("Hello {$member['first_name']}, greeting from " . ($gym['name'] ?? 'GymFlow') . "! We hope your fitness journey is going well.");
$whatsappUrl = "https://wa.me/{$cleanPhone}?text={$whatsappMsg}";
?>

<div class="mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= url('members') ?>" class="btn btn-sm btn-light border rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h2 class="fw-bold mb-0"><?= e($fullName) ?></h2>
                    <span class="badge bg-light text-dark font-monospace border px-2 py-1 rounded-pill">
                        #<?= e($member['member_number']) ?>
                    </span>
                    <?php if ($member['is_archived']): ?>
                        <span class="badge bg-secondary">Archived</span>
                    <?php endif; ?>
                </div>
                <div class="text-muted small mt-1 d-flex flex-wrap gap-3">
                    <span><i class="bi bi-telephone text-primary me-1"></i> <?= e($member['phone']) ?></span>
                    <?php if (!empty($member['email'])): ?>
                        <span><i class="bi bi-envelope text-info me-1"></i> <?= e($member['email']) ?></span>
                    <?php endif; ?>
                    <span><i class="bi bi-calendar3 me-1"></i> Member since <?= date('M Y', strtotime($member['created_at'])) ?></span>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- WhatsApp Chat -->
            <a href="<?= $whatsappUrl ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success fw-semibold shadow-sm">
                <i class="bi bi-whatsapp me-1"></i> WhatsApp
            </a>

            <!-- Edit Member -->
            <a href="<?= url("members/{$member['id']}/edit") ?>" class="btn btn-light border fw-semibold shadow-sm">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>

            <!-- Archive / Restore -->
            <?php if ($member['is_archived']): ?>
                <form action="<?= url("members/{$member['id']}/restore") ?>" method="POST" class="d-inline" onsubmit="return confirm('Restore this member account?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                    </button>
                </form>
            <?php else: ?>
                <form action="<?= url("members/{$member['id']}/archive") ?>" method="POST" class="d-inline" onsubmit="return confirm('Archive this member? Financial history is preserved.');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-archive me-1"></i> Archive
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Flash Alerts -->
<?= render_component('alert') ?>

<!-- Top KPI Summary Cards -->
<div class="row g-3 mb-4">
    <!-- Current Plan Status -->
    <div class="col-md-4">
        <div class="gf-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Active Plan</span>
                <?php if ($currentSub): ?>
                    <?= render_component('status_badge', [
                        'status' => $currentSub['status'],
                        'label' => ucfirst($currentSub['status'])
                    ]) ?>
                <?php else: ?>
                    <span class="badge bg-secondary">No Active Plan</span>
                <?php endif; ?>
            </div>
            <div class="fw-bold fs-5 text-dark mb-1">
                <?= $currentSub ? e($currentSub['plan_name_snapshot']) : 'Unsubscribed' ?>
            </div>
            <?php if ($currentSub): ?>
                <?php 
                    $daysLeft = (int) ceil((strtotime($currentSub['expiry_date']) - time()) / 86400);
                ?>
                <div class="small <?= $daysLeft < 0 ? 'text-danger fw-semibold' : ($daysLeft <= 7 ? 'text-warning fw-semibold' : 'text-muted') ?>">
                    <i class="bi bi-clock-history me-1"></i>
                    <?php if ($daysLeft < 0): ?>
                        Expired <?= abs($daysLeft) ?> days ago (<?= date('d M Y', strtotime($currentSub['expiry_date'])) ?>)
                    <?php elseif ($daysLeft === 0): ?>
                        Expires Today!
                    <?php else: ?>
                        <?= $daysLeft ?> days left (Expires <?= date('d M Y', strtotime($currentSub['expiry_date'])) ?>)
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <span class="text-muted small">Ready for subscription assignment</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Outstanding Dues -->
    <div class="col-md-4">
        <div class="gf-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Pending Balance</span>
                <span class="badge <?= $outstandingBalance > 0 ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle' ?>">
                    <?= $outstandingBalance > 0 ? 'Due' : 'Cleared' ?>
                </span>
            </div>
            <div class="fw-bold fs-4 <?= $outstandingBalance > 0 ? 'text-danger' : 'text-success' ?> mb-1">
                <?= format_currency($outstandingBalance) ?>
            </div>
            <span class="text-muted small">Agreed total minus all verified receipts</span>
        </div>
    </div>

    <!-- Total Lifetime Revenue -->
    <div class="col-md-4">
        <div class="gf-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Total Paid</span>
                <span class="badge bg-light text-muted border">Verified</span>
            </div>
            <div class="fw-bold fs-4 text-primary mb-1">
                <?= format_currency($totalPaid) ?>
            </div>
            <span class="text-muted small">Across <?= count($payments) ?> processed transactions</span>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Main Content: Memberships & Payments -->
    <div class="col-lg-8">
        <!-- Membership Subscriptions History -->
        <div class="gf-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-card-checklist text-primary"></i> Membership History
                </h5>
                <a href="<?= url("renewals?member_id={$member['id']}") ?>" class="btn btn-sm gf-btn-primary fw-semibold">
                    <i class="bi bi-arrow-repeat me-1"></i> Renew / Extend
                </a>
            </div>

            <?php if (empty($subscriptions)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-2 mb-2 d-block"></i>
                    No membership plans recorded yet.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Plan</th>
                                <th>Duration</th>
                                <th>Cost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscriptions as $sub): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= e($sub['plan_name_snapshot']) ?></div>
                                    </td>
                                    <td class="small">
                                        <?= date('d M Y', strtotime($sub['start_date'])) ?> &rarr; <?= date('d M Y', strtotime($sub['expiry_date'])) ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= format_currency((float) $sub['agreed_price']) ?></div>
                                        <?php if ((float)$sub['discount_amount'] > 0): ?>
                                            <span class="text-muted" style="font-size: 11px;">Disc: <?= format_currency((float)$sub['discount_amount']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= render_component('status_badge', [
                                            'status' => $sub['status'],
                                            'label' => ucfirst($sub['status'])
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Payments Ledger -->
        <div class="gf-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-receipt text-success"></i> Payment Ledger
                </h5>
                <a href="<?= url("payments/create?member_id={$member['id']}") ?>" class="btn btn-sm btn-outline-success fw-semibold">
                    <i class="bi bi-plus-circle me-1"></i> Record Payment
                </a>
            </div>

            <?php if (empty($payments)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-wallet2 fs-2 mb-2 d-block"></i>
                    No payment transactions recorded for this member.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Receipt</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $pay): ?>
                                <tr>
                                    <td class="font-monospace fw-bold small">
                                        <?= e($pay['receipt_number']) ?>
                                    </td>
                                    <td class="small">
                                        <?= date('d M Y', strtotime($pay['payment_date'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 10px;">
                                            <?= e($pay['payment_method']) ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold <?= $pay['status'] === 'voided' ? 'text-decoration-line-through text-muted' : 'text-success' ?>">
                                        <?= format_currency((float) $pay['amount']) ?>
                                    </td>
                                    <td>
                                        <?php if ($pay['status'] === 'paid'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Voided</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= url("payments/{$pay['id']}/receipt") ?>" target="_blank" class="btn btn-sm btn-light border py-1 px-2" title="Print Receipt">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Sidebar: Personal Details Card -->
    <div class="col-lg-4">
        <!-- Personal Info Card -->
        <div class="gf-card p-4 mb-4">
            <div class="text-center pb-3 border-bottom mb-3">
                <div class="rounded-circle overflow-hidden d-inline-flex align-items-center justify-content-center bg-light border mb-2" style="width: 80px; height: 80px;">
                    <?php if (!empty($member['photo_path'])): ?>
                        <img src="<?= asset($member['photo_path']) ?>" alt="Avatar" class="w-100 h-100 object-fit-cover">
                    <?php else: ?>
                        <span class="fw-bold text-primary fs-3"><?= e($initials) ?></span>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold mb-0"><?= e($fullName) ?></h5>
                <span class="text-muted small">#<?= e($member['member_number']) ?></span>
            </div>

            <h6 class="fw-bold mb-2 text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Details</h6>
            
            <div class="d-flex justify-content-between py-2 border-bottom small">
                <span class="text-muted">Gender</span>
                <span class="fw-semibold text-capitalize"><?= e($member['gender'] ?? 'Not set') ?></span>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom small">
                <span class="text-muted">Date of Birth</span>
                <span class="fw-semibold">
                    <?= !empty($member['date_of_birth']) ? date('d M Y', strtotime($member['date_of_birth'])) : 'Not set' ?>
                </span>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom small">
                <span class="text-muted">Emergency Contact</span>
                <span class="fw-semibold text-end">
                    <?= !empty($member['emergency_contact_name']) ? e($member['emergency_contact_name']) : 'None' ?>
                    <?php if (!empty($member['emergency_contact_phone'])): ?>
                        <br><a href="tel:<?= e($member['emergency_contact_phone']) ?>" class="text-decoration-none text-muted small"><?= e($member['emergency_contact_phone']) ?></a>
                    <?php endif; ?>
                </span>
            </div>
            <?php if (!empty($member['address'])): ?>
                <div class="py-2 border-bottom small">
                    <span class="text-muted d-block mb-1">Address</span>
                    <span><?= nl2br(e($member['address'])) ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($member['notes'])): ?>
                <div class="py-2 small">
                    <span class="text-muted d-block mb-1">Notes</span>
                    <div class="p-2 bg-light rounded text-muted"><?= nl2br(e($member['notes'])) ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Member Portal Access Card (Phase D) -->
        <div class="gf-card p-4 mb-4">
            <h6 class="fw-bold mb-3 d-flex align-items-center justify-content-between">
                <span><i class="bi bi-shield-lock text-primary me-1"></i> Member Portal Access</span>
                <?php if (!empty($member['user_id'])): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary border">Not Linked</span>
                <?php endif; ?>
            </h6>

            <?php if (!empty($member['user_id'])): ?>
                <div class="p-3 bg-light rounded-3 border small mb-2">
                    <div class="text-muted mb-1">Linked Login Account:</div>
                    <strong class="text-dark"><i class="bi bi-person-check me-1"></i> User ID #<?= (int) $member['user_id'] ?></strong>
                    <div class="text-muted mt-1" style="font-size: 11px;">Member can sign in at <code>/login</code> to access self-service portal.</div>
                </div>
            <?php else: ?>
                <p class="text-muted small mb-3">
                    Provision a login account so this member can view pass expiry, track dues, download receipts, and submit offline payment requests.
                </p>
                <button type="button" class="btn btn-outline-primary btn-sm w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#createPortalModal">
                    <i class="bi bi-person-plus me-1"></i> Create Portal Account
                </button>

                <!-- Modal to create portal account -->
                <div class="modal fade" id="createPortalModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border rounded-4 shadow">
                            <form method="POST" action="<?= url("members/{$member['id']}/create-portal-account") ?>">
                                <?= csrf_field() ?>
                                <div class="modal-header border-bottom px-4 py-3">
                                    <h5 class="modal-title fw-bold">Create Member Portal Account</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body px-4 py-3">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Member Name</label>
                                        <input type="text" class="form-control rounded-3 bg-light" disabled value="<?= e($fullName) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Login Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control rounded-3" required value="<?= e($member['email'] ?? '') ?>" placeholder="member@example.com">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Initial Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password" class="form-control rounded-3" required minlength="8" placeholder="At least 8 characters">
                                    </div>
                                </div>
                                <div class="modal-footer border-top px-4 py-3">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                        <i class="bi bi-key me-1"></i> Provision Account
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
