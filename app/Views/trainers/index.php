<div class="container-fluid p-0">
    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge" style="background: rgba(2, 132, 199, 0.12); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); border-radius: 9999px; font-weight: 700; padding: 4px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">
                    <i class="bi bi-people me-1"></i> Trainer Management
                </span>
                <span class="text-muted" style="font-size: 13px;">Gym Operations</span>
            </div>
            <h2 class="h3 fw-bold mb-0 text-dark">Trainer Hub & Member Assignments</h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('staff') ?>" class="gf-btn gf-btn-outline">
                <i class="bi bi-person-gear"></i> Manage Staff Team
            </a>
            <button type="button" class="gf-btn gf-btn-primary" data-bs-toggle="modal" data-bs-target="#assignModal">
                <i class="bi bi-link-45deg"></i> Assign Member to Trainer
            </button>
        </div>
    </div>

    <!-- Active Trainers Grid -->
    <div class="mb-4">
        <h5 class="fw-bold mb-3 text-dark">Active Gym Trainers</h5>
        <?php if (empty($trainers)): ?>
            <div class="gf-card p-4 text-center">
                <div class="gf-metric-icon amber mx-auto mb-2" style="width: 48px; height: 48px; font-size: 20px;">
                    <i class="bi bi-person-exclamation"></i>
                </div>
                <h6 class="fw-bold text-dark">No Trainers On Staff Yet</h6>
                <p class="text-muted small mb-3">Add team members with the "Gym Trainer" role under Staff Access to begin assigning members.</p>
                <a href="<?= url('staff') ?>" class="gf-btn gf-btn-primary btn-sm">
                    <i class="bi bi-person-plus-fill me-1"></i> Add Trainer in Staff Settings
                </a>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($trainers as $t): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="gf-card p-3 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="gf-user-avatar">
                                        <?= strtoupper(substr($t['name'], 0, 2)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= e($t['name']) ?></div>
                                        <div class="small text-muted"><?= e($t['email']) ?></div>
                                    </div>
                                </div>
                                <span class="gf-badge active">Trainer</span>
                            </div>
                            <div class="row g-2 text-center pt-2">
                                <div class="col-6">
                                    <div class="p-2 bg-light rounded-3 border">
                                        <div class="h5 fw-bold text-dark mb-0"><?= e((string)$t['active_trainees_count']) ?></div>
                                        <div class="text-muted" style="font-size: 11px;">Active Trainees</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 bg-light rounded-3 border">
                                        <div class="h5 fw-bold text-primary mb-0"><?= e((string)$t['today_sessions_count']) ?></div>
                                        <div class="text-muted" style="font-size: 11px;">Today's Sessions</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Active Assignments Table -->
    <div class="gf-card">
        <div class="p-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="background: rgba(0, 0, 0, 0.02);">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Active Trainee Assignments</h5>
                <p class="small text-muted mb-0">Current member-to-trainer relationships within your gym</p>
            </div>
            <span class="badge bg-light text-dark border">
                <?= count($assignments) ?> Active Relationships
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th style="padding-left: 20px;">Member</th>
                        <th>Assigned Trainer</th>
                        <th>Assigned On</th>
                        <th>Assigned By</th>
                        <th>Notes</th>
                        <th class="text-end" style="padding-right: 20px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assignments)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people display-6 d-block mb-2 text-secondary"></i>
                                No active member-trainer assignments yet. Click "Assign Member to Trainer" above to create one.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assignments as $a): ?>
                            <tr>
                                <td style="padding-left: 20px;">
                                    <div class="fw-bold text-dark"><?= e($a['first_name'] . ' ' . $a['last_name']) ?></div>
                                    <div class="small text-muted"><code><?= e($a['member_number']) ?></code> &bull; <?= e($a['member_phone'] ?? 'No phone') ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-badge text-primary"></i>
                                        <strong class="text-dark"><?= e($a['trainer_name']) ?></strong>
                                    </div>
                                </td>
                                <td class="small text-muted">
                                    <?= e(date('M d, Y', strtotime($a['assigned_at']))) ?>
                                </td>
                                <td class="small text-muted">
                                    <?= e($a['assigned_by_name'] ?? 'System') ?>
                                </td>
                                <td class="small text-muted">
                                    <?= e($a['notes'] ?? '—') ?>
                                </td>
                                <td class="text-end" style="padding-right: 20px;">
                                    <form method="POST" action="<?= url('trainers/unassign/' . $a['id']) ?>" class="d-inline" onsubmit="return confirm('End this member\'s assignment to <?= e(addslashes($a['trainer_name'])) ?>?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 fw-semibold">
                                            <i class="bi bi-x-circle me-1"></i> End Assignment
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Assign Member Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: var(--gf-border); border-radius: var(--gf-radius-lg); box-shadow: var(--gf-shadow-hover);">
            <form method="POST" action="<?= url('trainers/assign') ?>">
                <?= csrf_field() ?>
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-link-45deg text-primary me-1"></i> Assign Member to Trainer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="trainer_user_id" class="form-label fw-semibold text-dark">Select Trainer <span class="text-danger">*</span></label>
                        <select name="trainer_user_id" id="trainer_user_id" class="form-select form-select-lg" required style="border: var(--gf-border); border-radius: var(--gf-radius-md); font-size: 15px;">
                            <option value="">-- Choose Trainer --</option>
                            <?php foreach ($trainers as $t): ?>
                                <option value="<?= e((string)$t['user_id']) ?>">
                                    <?= e($t['name']) ?> (<?= e($t['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="member_id" class="form-label fw-semibold text-dark">Select Member <span class="text-danger">*</span></label>
                        <select name="member_id" id="member_id" class="form-select form-select-lg" required style="border: var(--gf-border); border-radius: var(--gf-radius-md); font-size: 15px;">
                            <option value="">-- Choose Member --</option>
                            <?php foreach ($members as $m): ?>
                                <option value="<?= e((string)$m['id']) ?>">
                                    <?= e($m['first_name'] . ' ' . $m['last_name']) ?> (<?= e($m['member_number']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold text-dark">Assignment Goals / Notes <span class="text-muted fw-normal">(Optional)</span></label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="e.g., Weight loss target, specialized strength routine, injury precautions..." style="border: var(--gf-border); border-radius: var(--gf-radius-md);"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="gf-btn gf-btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="gf-btn gf-btn-primary">
                        <i class="bi bi-check-circle-fill me-1"></i> Confirm Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
