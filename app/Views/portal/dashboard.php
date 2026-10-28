<?php
/**
 * GymFlow — Member Self-Service Portal Dashboard
 * Modern, component-based, strictly uniform all-around borders, soft shadows.
 */
?>
<div class="container-fluid px-4 py-4">
    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge rounded-pill bg-success-subtle text-success border px-2 py-1 fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.05em;">
                    <i class="bi bi-person-circle me-1"></i> Member Portal
                </span>
                <span class="text-muted small">&bull; <?= htmlspecialchars($gym['name'] ?? 'GymFlow') ?></span>
            </div>
            <h1 class="h3 fw-bold text-slate-900 mb-0">
                Welcome, <?= htmlspecialchars($member ? ($member['first_name'] . ' ' . $member['last_name']) : ($user['name'] ?? 'Member')) ?>!
            </h1>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <?php if ($member): ?>
                <span class="badge bg-white text-dark border rounded-pill px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="bi bi-upc-scan text-primary"></i>
                    <span>ID: <strong><?= htmlspecialchars($member['member_number']) ?></strong></span>
                </span>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2"
                        data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="bi bi-person-gear"></i>
                    <span>Edit Profile</span>
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2"
                        data-bs-toggle="modal" data-bs-target="#submitPaymentModal">
                    <i class="bi bi-wallet2"></i>
                    <span>Submit Payment</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$member): ?>
        <!-- Account Linking Notice if not linked to a member profile -->
        <div class="card border rounded-4 p-5 text-center mb-4 shadow-sm bg-white">
            <div class="bg-warning-subtle text-warning rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px; font-size: 28px;">
                <i class="bi bi-person-exclamation"></i>
            </div>
            <h3 class="fw-bold text-slate-900 mb-2">Member Profile Pending Linking</h3>
            <p class="text-muted mx-auto mb-4" style="max-width: 540px;">
                Your portal account is active, but it has not been linked to an enrolled member profile at <strong><?= htmlspecialchars($gym['name'] ?? 'GymFlow') ?></strong>.
                Please contact the gym management and share your registered email (<code><?= htmlspecialchars($user['email'] ?? '') ?></code>).
            </p>
        </div>
    <?php else: ?>
        <!-- KPI Metric Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border rounded-4 p-3 h-100 shadow-sm bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Current Plan</span>
                        <span class="badge rounded-pill <?= ($activeMembership && $activeMembership['status'] === 'active') ? 'bg-success-subtle text-success border' : 'bg-secondary-subtle text-secondary border' ?> px-2 py-1">
                            <?= htmlspecialchars(ucfirst($activeMembership['status'] ?? 'None')) ?>
                        </span>
                    </div>
                    <h3 class="fw-bold mb-1 text-slate-900 text-truncate" title="<?= htmlspecialchars($activeMembership['plan_name_snapshot'] ?? 'No Active Plan') ?>">
                        <?= htmlspecialchars($activeMembership['plan_name_snapshot'] ?? 'No Plan') ?>
                    </h3>
                    <span class="text-muted small">
                        <?php if ($activeMembership): ?>
                            Agreed: <?= htmlspecialchars($gym['currency_symbol'] ?? '₹') ?><?= number_format((float) $activeMembership['agreed_price'], 2) ?>
                        <?php else: ?>
                            Enrolled membership required
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border rounded-4 p-3 h-100 shadow-sm bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Pass Expiry</span>
                        <span class="badge rounded-pill bg-light text-dark border px-2 py-1">Membership</span>
                    </div>
                    <?php if ($activeMembership && !empty($activeMembership['expiry_date'])): ?>
                        <?php 
                            $expiryTime = strtotime($activeMembership['expiry_date']);
                            $daysLeft = (int) ceil(($expiryTime - time()) / 86400);
                        ?>
                        <h3 class="fw-bold mb-1 text-slate-900"><?= htmlspecialchars(date('M d, Y', $expiryTime)) ?></h3>
                        <span class="small <?= $daysLeft <= 7 ? 'text-warning fw-semibold' : 'text-muted' ?>">
                            <?= $daysLeft > 0 ? "{$daysLeft} days remaining" : 'Pass Expired' ?>
                        </span>
                    <?php else: ?>
                        <h3 class="fw-bold mb-1 text-slate-900">&mdash;</h3>
                        <span class="text-muted small">No active expiry date</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border rounded-4 p-3 h-100 shadow-sm bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Outstanding Dues</span>
                        <span class="badge rounded-pill <?= $outstandingBalance > 0 ? 'bg-danger-subtle text-danger border' : 'bg-success-subtle text-success border' ?> px-2 py-1">
                            <?= $outstandingBalance > 0 ? 'Due' : 'Cleared' ?>
                        </span>
                    </div>
                    <h3 class="fw-bold mb-1 <?= $outstandingBalance > 0 ? 'text-danger' : 'text-success' ?>">
                        <?= htmlspecialchars($gym['currency_symbol'] ?? '₹') ?><?= number_format($outstandingBalance, 2) ?>
                    </h3>
                    <span class="text-muted small">
                        <?= $outstandingBalance > 0 ? 'Pending payment balance' : 'All fees settled in full' ?>
                    </span>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border rounded-4 p-3 h-100 shadow-sm bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-medium">Total Paid</span>
                        <span class="badge rounded-pill bg-primary-subtle text-primary border px-2 py-1">Receipts</span>
                    </div>
                    <h3 class="fw-bold mb-1 text-slate-900">
                        <?= htmlspecialchars($gym['currency_symbol'] ?? '₹') ?><?= number_format($totalPaid, 2) ?>
                    </h3>
                    <span class="text-muted small">Total verified transactions</span>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Left Column: Trainer & Training Sessions -->
            <div class="col-12 col-xl-5">
                <!-- Assigned Trainer Card -->
                <div class="card border rounded-4 p-4 shadow-sm bg-white mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-slate-900 mb-0">Personal Trainer</h5>
                        <span class="badge rounded-pill <?= $assignedTrainer ? 'bg-success-subtle text-success border' : 'bg-secondary-subtle text-secondary border' ?> px-2 py-1">
                            <?= $assignedTrainer ? 'Assigned' : 'Unassigned' ?>
                        </span>
                    </div>
                    <?php if ($assignedTrainer): ?>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 18px;">
                                <?= htmlspecialchars(strtoupper(substr($assignedTrainer['trainer_name'], 0, 2))) ?>
                            </div>
                            <div>
                                <h6 class="fw-bold text-slate-900 mb-0"><?= htmlspecialchars($assignedTrainer['trainer_name']) ?></h6>
                                <div class="text-muted small"><?= htmlspecialchars($assignedTrainer['trainer_phone'] ?? 'No phone listed') ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($assignedTrainer['trainer_email']) ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-person-x fs-2 d-block mb-1 text-muted"></i>
                            <span class="small">No dedicated trainer assigned. Speak with front desk to book a coach.</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Upcoming Sessions Schedule -->
                <div class="card border rounded-4 p-4 shadow-sm bg-white mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-slate-900 mb-0">Upcoming Training Sessions</h5>
                        <span class="badge rounded-pill bg-light text-dark border px-2 py-1 small">Schedule</span>
                    </div>
                    <?php if (empty($upcomingSessions)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-calendar-event fs-3 d-block mb-2 text-muted"></i>
                            <span class="small">No upcoming sessions scheduled right now.</span>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                            <?php foreach ($upcomingSessions as $sess): ?>
                                <div class="list-group-item d-flex align-items-center justify-content-between py-3 px-3">
                                    <div>
                                        <div class="fw-bold text-slate-900"><?= htmlspecialchars(date('D, M d, Y', strtotime($sess['session_date']))) ?></div>
                                        <div class="text-muted small">
                                            <i class="bi bi-clock me-1"></i> <?= htmlspecialchars(date('h:i A', strtotime($sess['start_time']))) ?> &ndash; <?= htmlspecialchars(date('h:i A', strtotime($sess['end_time']))) ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary-subtle text-primary border rounded-pill px-2 py-1">Scheduled</span>
                                        <div class="text-muted small mt-1">Coach <?= htmlspecialchars($sess['trainer_name'] ?? 'Trainer') ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Workout Progress & Coach Feedback -->
                <div class="card border rounded-4 p-4 shadow-sm bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-slate-900 mb-0">Trainer Feedback & Progress</h5>
                        <span class="badge rounded-pill bg-light text-dark border px-2 py-1 small">History</span>
                    </div>
                    <?php if (empty($progressRecords)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-graph-up fs-3 d-block mb-2 text-muted"></i>
                            <span class="small">No workout progress logged yet.</span>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                            <?php foreach ($progressRecords as $pr): ?>
                                <div class="list-group-item py-3 px-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-slate-900"><?= htmlspecialchars(date('M d, Y', strtotime($pr['recorded_date']))) ?></span>
                                        <span class="text-muted small">By <?= htmlspecialchars($pr['trainer_name']) ?></span>
                                    </div>
                                    <p class="mb-2 small text-dark"><?= nl2br(htmlspecialchars($pr['workout_summary'])) ?></p>
                                    <?php if (!empty($pr['trainer_feedback'])): ?>
                                        <div class="p-2 bg-light border rounded-3 small text-muted mb-2">
                                            <i class="bi bi-chat-quote-fill me-1 text-primary"></i> <?= htmlspecialchars($pr['trainer_feedback']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="d-flex gap-3 small text-muted">
                                        <?php if ($pr['weight_kg']): ?>
                                            <span><i class="bi bi-speedometer2 me-1"></i> Weight: <strong><?= number_format((float) $pr['weight_kg'], 1) ?> kg</strong></span>
                                        <?php endif; ?>
                                        <?php if ($pr['body_fat_pct']): ?>
                                            <span><i class="bi bi-percent me-1"></i> Body Fat: <strong><?= number_format((float) $pr['body_fat_pct'], 1) ?>%</strong></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Offline Payment Requests & Official Payments -->
            <div class="col-12 col-xl-7">
                <!-- Offline Payment Requests Tracker -->
                <div class="card border rounded-4 shadow-sm bg-white mb-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="fw-bold text-slate-900 mb-0">Offline Payment Requests</h5>
                            <span class="text-muted small">Track requests submitted for cash/UPI/bank payments</span>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1"
                                data-bs-toggle="modal" data-bs-target="#submitPaymentModal">
                            <i class="bi bi-plus-lg me-1"></i> New Request
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-3">Req #</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Details / Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($paymentRequests)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-3 d-block mb-1 text-muted"></i>
                                            <span class="small">No offline payment requests submitted yet.</span>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($paymentRequests as $req): ?>
                                        <tr>
                                            <td class="ps-3">
                                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1">
                                                    #<?= (int) $req['id'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-slate-900">
                                                    <?= htmlspecialchars($gym['currency_symbol'] ?? '₹') ?><?= number_format((float) $req['amount'], 2) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1 text-uppercase">
                                                    <?= htmlspecialchars($req['payment_method']) ?>
                                                </span>
                                            </td>
                                            <td class="small text-muted">
                                                <?= htmlspecialchars(date('M d, Y', strtotime($req['payment_date']))) ?>
                                            </td>
                                            <td>
                                                <?php if ($req['status'] === 'pending'): ?>
                                                    <span class="badge bg-warning-subtle text-warning border px-2 py-1 rounded-pill">
                                                        <i class="bi bi-hourglass-split me-1"></i> Pending Review
                                                    </span>
                                                <?php elseif ($req['status'] === 'approved'): ?>
                                                    <span class="badge bg-success-subtle text-success border px-2 py-1 rounded-pill">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Approved
                                                    </span>
                                                <?php elseif ($req['status'] === 'rejected'): ?>
                                                    <span class="badge bg-danger-subtle text-danger border px-2 py-1 rounded-pill">
                                                        <i class="bi bi-x-circle-fill me-1"></i> Declined
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-3 small">
                                                <?php if ($req['status'] === 'approved' && !empty($req['approved_receipt_number'])): ?>
                                                    <a href="<?= url("portal/receipts/{$req['payment_id']}") ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1 text-decoration-none">
                                                        <i class="bi bi-receipt me-1"></i> Receipt
                                                    </a>
                                                <?php elseif ($req['status'] === 'rejected' && !empty($req['rejection_reason'])): ?>
                                                    <span class="text-danger small" title="<?= htmlspecialchars($req['rejection_reason']) ?>">
                                                        <?= htmlspecialchars($req['rejection_reason']) ?>
                                                    </span>
                                                <?php elseif ($req['status'] === 'pending'): ?>
                                                    <span class="text-muted small">Awaiting Owner verification</span>
                                                <?php else: ?>
                                                    <span class="text-muted">&mdash;</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Verified Official Payments & Receipts -->
                <div class="card border rounded-4 shadow-sm bg-white overflow-hidden">
                    <div class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="fw-bold text-slate-900 mb-0">Official Receipts & Payments</h5>
                            <span class="text-muted small">Official receipts registered by <?= htmlspecialchars($gym['name'] ?? 'GymFlow') ?></span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-3">Receipt #</th>
                                    <th>Date</th>
                                    <th>Plan</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentPayments)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-receipt fs-3 d-block mb-1 text-muted"></i>
                                            <span class="small">No payment receipts on record yet.</span>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentPayments as $p): ?>
                                        <tr>
                                            <td class="ps-3">
                                                <code class="fw-bold text-primary"><?= htmlspecialchars($p['receipt_number']) ?></code>
                                            </td>
                                            <td class="small text-muted"><?= htmlspecialchars(date('M d, Y', strtotime($p['payment_date']))) ?></td>
                                            <td class="small fw-medium"><?= htmlspecialchars($p['plan_name_snapshot'] ?? 'Membership') ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1 text-uppercase">
                                                    <?= htmlspecialchars($p['payment_method']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    <?= htmlspecialchars($gym['currency_symbol'] ?? '₹') ?><?= number_format((float) $p['amount'], 2) ?>
                                                </strong>
                                            </td>
                                            <td class="text-end pe-3">
                                                <?php if ($p['status'] === 'paid'): ?>
                                                    <a href="<?= url("portal/receipts/{$p['id']}") ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1 text-decoration-none">
                                                        <i class="bi bi-printer me-1"></i> View Receipt
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill">
                                                        <?= htmlspecialchars(ucfirst($p['status'])) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Offline Payment Request Modal -->
        <div class="modal fade" id="submitPaymentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border rounded-4 shadow">
                    <form method="POST" action="<?= url('portal/payments/request') ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <div class="modal-header border-bottom px-4 py-3">
                            <h5 class="modal-title fw-bold">Submit Offline Payment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body px-4 py-3">
                            <p class="text-muted small mb-3">
                                Paid via UPI, cash, or bank transfer? Submit the details below for gym owner verification and receipt generation.
                            </p>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Membership Subscription <span class="text-danger">*</span></label>
                                <select name="membership_id" class="form-select rounded-3" required>
                                    <?php if (empty($allMemberships)): ?>
                                        <option value="">No memberships found</option>
                                    <?php else: ?>
                                        <?php foreach ($allMemberships as $m): ?>
                                            <option value="<?= (int) $m['id'] ?>">
                                                <?= htmlspecialchars($m['plan_name_snapshot']) ?> &bull; (<?= htmlspecialchars($m['start_date']) ?> to <?= htmlspecialchars($m['expiry_date']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Amount (<?= htmlspecialchars($gym['currency_symbol'] ?? '₹') ?>) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="1" name="amount" class="form-control rounded-3" required 
                                           placeholder="e.g. 1500" value="<?= $outstandingBalance > 0 ? htmlspecialchars((string) $outstandingBalance) : '' ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Payment Method <span class="text-danger">*</span></label>
                                    <select name="payment_method" class="form-select rounded-3" required>
                                        <option value="upi" selected>UPI (GPay, PhonePe, Paytm)</option>
                                        <option value="cash">Cash at Desk</option>
                                        <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
                                        <option value="other">Other Method</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control rounded-3" required value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Transaction / UTR #</label>
                                    <input type="text" name="reference_number" class="form-control rounded-3" placeholder="e.g. UPI Ref: 312384992">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold small">Notes / Remarks</label>
                                <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="Optional notes for the gym owner..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top px-4 py-3">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-send-check me-1"></i> Submit Payment Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Profile Modal -->
        <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border rounded-4 shadow">
                    <form method="POST" action="<?= url('portal/profile') ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <div class="modal-header border-bottom px-4 py-3">
                            <h5 class="modal-title fw-bold">Update Contact Information</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body px-4 py-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Full Name</label>
                                <input type="text" class="form-control rounded-3 bg-light" disabled value="<?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>">
                                <span class="text-muted" style="font-size: 11px;">Name changes must be verified at the gym front desk.</span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Phone Number</label>
                                <input type="text" name="phone" class="form-control rounded-3" value="<?= htmlspecialchars($member['phone'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Home Address</label>
                                <textarea name="address" class="form-control rounded-3" rows="2"><?= htmlspecialchars($member['address'] ?? '') ?></textarea>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Emergency Contact Name</label>
                                    <input type="text" name="emergency_contact_name" class="form-control rounded-3" value="<?= htmlspecialchars($member['emergency_contact_name'] ?? '') ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Emergency Contact Phone</label>
                                    <input type="text" name="emergency_contact_phone" class="form-control rounded-3" value="<?= htmlspecialchars($member['emergency_contact_phone'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top px-4 py-3">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-save me-1"></i> Save Contact Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
