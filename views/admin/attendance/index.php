<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-white mb-1"><i class="bi bi-calendar-check-fill me-2 text-success"></i> Daily Attendance Register</h3>
        <p class="text-secondary small mb-0"><?= count($records) ?> check-ins recorded on <?= date('d F Y', strtotime($selectedDate)) ?></p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-fp-primary btn-sm" data-bs-toggle="modal" data-bs-target="#quickCheckinModal">
            <i class="bi bi-qr-code-scan me-1"></i> Quick Check-In
        </button>
        <a href="<?= BASE_URL ?>/kiosk" target="_blank" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-display me-1"></i> Open Kiosk Mode
        </a>
    </div>
</div>

<!-- Date Filter Bar -->
<div class="fp-card mb-4 p-3">
    <form action="<?= BASE_URL ?>/admin/attendance" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-dark border-secondary border-opacity-50 text-secondary">
                    <i class="bi bi-calendar3"></i>
                </span>
                <input type="date" class="form-control form-control-sm" name="date" value="<?= htmlspecialchars($selectedDate, ENT_QUOTES, 'UTF-8') ?>" onchange="this.form.submit()">
            </div>
        </div>
        <div class="col-12 col-md-8 text-md-end">
            <span class="badge bg-success bg-opacity-25 text-success me-2 fs-8"><i class="bi bi-check2"></i> Present: <?= count($records) ?></span>
            <span class="badge bg-dark border border-secondary border-opacity-50 text-secondary fs-8">Date: <?= date('D, d M Y', strtotime($selectedDate)) ?></span>
        </div>
    </form>
</div>

<!-- Attendance Register Table -->
<div class="fp-card">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary fs-8 text-uppercase">
                    <th>Time</th>
                    <th>Member</th>
                    <th>Plan</th>
                    <th>Branch</th>
                    <th>Method</th>
                    <th>Recorded By</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody class="fs-7">
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="bi bi-calendar-x display-5 d-block mb-2 text-secondary opacity-50"></i>
                            No attendance records found for this date.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $r): ?>
                        <tr>
                            <td class="fw-bold text-white">
                                <i class="bi bi-clock me-1 text-secondary"></i> <?= date('h:i A', strtotime($r['check_in_time'])) ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary bg-opacity-25 rounded-circle p-2 me-2 text-center" style="width: 32px; height: 32px;">
                                        <i class="bi bi-person text-light"></i>
                                    </div>
                                    <div>
                                        <a href="<?= BASE_URL ?>/admin/members/<?= $r['member_id'] ?>" class="fw-bold text-white text-decoration-none hover-emerald">
                                            <?= htmlspecialchars($r['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                        <small class="text-secondary d-block"><?= htmlspecialchars($r['member_code'], ENT_QUOTES, 'UTF-8') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-light"><?= htmlspecialchars($r['plan_name'] ?: 'Standard', ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <span class="text-secondary small"><?= htmlspecialchars($r['branch_name'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <?php
                                $methodIcon = 'bi-qr-code-scan';
                                if ($r['method'] === 'kiosk') $methodIcon = 'bi-display';
                                elseif ($r['method'] === 'pin') $methodIcon = 'bi-keypad';
                                elseif ($r['method'] === 'phone') $methodIcon = 'bi-phone';
                                ?>
                                <span class="badge bg-dark border border-secondary border-opacity-50 text-uppercase fs-8">
                                    <i class="bi <?= $methodIcon ?> me-1 text-success"></i> <?= str_replace('_', ' ', $r['method']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-secondary small"><?= htmlspecialchars($r['staff_name'] ?: 'Self / Kiosk', ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <span class="badge-fp-active"><i class="bi bi-check-circle me-1"></i> Present</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Quick Check-in -->
<div class="modal fade" id="quickCheckinModal" tabindex="-1" aria-labelledby="quickCheckinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title text-white" id="quickCheckinModalLabel"><i class="bi bi-qr-code-scan text-success me-2"></i> Fast Member Check-In</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/admin/attendance/checkin" method="POST">
                <?= CSRF::getField() ?>
                <input type="hidden" name="method" value="staff_manual">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="token_or_code">Member ID, Phone, or QR Token *</label>
                        <input type="text" class="form-control form-control-lg" id="token_or_code" name="token_or_code" placeholder="e.g. GYM-1001 or 9999999995" autofocus required>
                        <small class="text-secondary fs-8">Enter Member Code (GYM-1001), phone number, or scan member barcode/QR.</small>
                    </div>

                    <div class="p-3 bg-dark bg-opacity-50 rounded-3 border border-secondary border-opacity-25 small text-secondary">
                        <i class="bi bi-shield-check text-success me-1"></i>
                        The server will verify membership validity, branch access, frozen status, and duplicate check-in rules instantly.
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-fp-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-fp-primary btn-lg"><i class="bi bi-check2-circle me-1"></i> Record Check-In</button>
                </div>
            </form>
        </div>
    </div>
</div>
