<div class="container-fluid p-0">
    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <a href="<?= url('trainer/members') ?>" class="text-muted small fw-semibold d-inline-flex align-items-center gap-1 mb-2 text-decoration-none">
                <i class="bi bi-arrow-left"></i> Back to Trainees Roster
            </a>
            <h2 class="h3 fw-bold mb-0 text-dark"><?= e($member['first_name'] . ' ' . $member['last_name']) ?></h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="gf-btn gf-btn-primary" data-bs-toggle="modal" data-bs-target="#logProgressModal">
                <i class="bi bi-plus-circle me-1"></i> Log Workout Progress
            </button>
        </div>
    </div>

    <!-- Member Overview Header Card -->
    <div class="gf-card p-4 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-auto">
                <div class="gf-user-avatar" style="width: 64px; height: 64px; font-size: 24px;">
                    <?= strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)) ?>
                </div>
            </div>
            <div class="col">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h4 class="fw-bold text-dark mb-0"><?= e($member['first_name'] . ' ' . $member['last_name']) ?></h4>
                    <span class="badge bg-light text-dark border"><code><?= e($member['member_number']) ?></code></span>
                    <?php if ($activeMembership): ?>
                        <span class="gf-badge active">Active Member</span>
                    <?php else: ?>
                        <span class="gf-badge neutral">No Active Pass</span>
                    <?php endif; ?>
                </div>
                <div class="d-flex flex-wrap gap-3 small text-muted">
                    <span><i class="bi bi-telephone text-primary me-1"></i> <?= e($member['phone'] ?? 'No phone') ?></span>
                    <span><i class="bi bi-envelope text-primary me-1"></i> <?= e($member['email'] ?? 'No email') ?></span>
                    <span><i class="bi bi-calendar3 text-primary me-1"></i> Joined: <?= date('M d, Y', strtotime($member['created_at'])) ?></span>
                </div>
            </div>
            <div class="col-md-auto text-md-end">
                <div class="small text-muted">Current Plan</div>
                <div class="fw-bold text-dark"><?= e($activeMembership['plan_name_snapshot'] ?? 'None') ?></div>
                <?php if ($activeMembership): ?>
                    <div class="small text-muted">Expires: <?= date('M d, Y', strtotime($activeMembership['expiry_date'])) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Progress History -->
        <div class="col-lg-7">
            <div class="gf-card h-100">
                <div class="p-3 d-flex align-items-center justify-content-between" style="background: rgba(0, 0, 0, 0.02);">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Workout & Progress History</h5>
                        <p class="small text-muted mb-0">Logged workouts, coach feedback, and measurements</p>
                    </div>
                    <span class="badge bg-light text-dark border">
                        <?= count($progressHistory) ?> Records
                    </span>
                </div>
                <div class="p-3">
                    <?php if (empty($progressHistory)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-graph-up-arrow display-6 d-block mb-2 text-secondary"></i>
                            <div class="fw-semibold text-dark mb-1">No Progress Records Yet</div>
                            <span class="small">Click "Log Workout Progress" above to record this trainee's first session notes.</span>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($progressHistory as $p): ?>
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-white text-dark border fw-semibold">
                                                <i class="bi bi-calendar-check text-primary me-1"></i> <?= date('M d, Y', strtotime($p['record_date'])) ?>
                                            </span>
                                            <?php if (!empty($p['weight_kg'])): ?>
                                                <span class="badge" style="background: rgba(2, 132, 199, 0.12); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25);">
                                                    <i class="bi bi-speedometer2 me-1"></i> <?= e($p['weight_kg']) ?> kg
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($p['body_fat_percentage'])): ?>
                                                <span class="badge" style="background: rgba(139, 92, 246, 0.12); color: #7c3aed; border: 1px solid rgba(139, 92, 246, 0.25);">
                                                    <?= e($p['body_fat_percentage']) ?>% Body Fat
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <strong class="text-dark small d-block mb-1">Workout Summary:</strong>
                                        <p class="text-dark small mb-0" style="white-space: pre-line;"><?= e($p['workout_summary']) ?></p>
                                    </div>
                                    <?php if (!empty($p['trainer_feedback'])): ?>
                                        <div class="pt-2">
                                            <strong class="text-muted small d-block mb-1"><i class="bi bi-chat-left-quote text-primary me-1"></i> Coach Feedback:</strong>
                                            <p class="text-muted small mb-0 fst-italic"><?= e($p['trainer_feedback']) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Training Sessions with this member -->
        <div class="col-lg-5">
            <div class="gf-card h-100">
                <div class="p-3 d-flex align-items-center justify-content-between" style="background: rgba(0, 0, 0, 0.02);">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Scheduled Sessions</h5>
                        <p class="small text-muted mb-0">Sessions booked with this trainee</p>
                    </div>
                </div>
                <div class="p-3">
                    <?php if (empty($sessions)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x display-6 d-block mb-2 text-secondary"></i>
                            <div class="fw-semibold text-dark mb-1">No Booked Sessions</div>
                            <span class="small">Schedule a training slot from the Sessions screen.</span>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush border-0">
                            <?php foreach ($sessions as $s): ?>
                                <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-2 border-0">
                                    <div>
                                        <div class="fw-bold text-dark small">
                                            <?= date('M d, Y', strtotime($s['session_date'])) ?>
                                        </div>
                                        <div class="text-muted small">
                                            <?= date('h:i A', strtotime($s['start_time'])) ?> - <?= date('h:i A', strtotime($s['end_time'])) ?>
                                        </div>
                                    </div>
                                    <span class="gf-badge <?= $s['status'] === 'completed' ? 'active' : ($s['status'] === 'scheduled' ? 'expiring' : 'neutral') ?>">
                                        <?= e(ucfirst($s['status'])) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Progress Modal -->
<div class="modal fade" id="logProgressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: var(--gf-border); border-radius: var(--gf-radius-lg); box-shadow: var(--gf-shadow-hover);">
            <form method="POST" action="<?= url('trainer/progress') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="member_id" value="<?= e((string)$member['id']) ?>">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square text-primary me-1"></i> Log Workout Progress
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Workout Date</label>
                        <input type="date" name="record_date" class="form-control" value="<?= date('Y-m-d') ?>" required style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Workout Summary & Sets <span class="text-danger">*</span></label>
                        <textarea name="workout_summary" class="form-control" rows="3" placeholder="e.g., Chest & Triceps: 4x10 Bench Press (60kg), Incline Dumbbell Press 3x12, Tricep Dips..." required style="border: var(--gf-border); border-radius: var(--gf-radius-md);"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Coach Feedback & Notes</label>
                        <textarea name="trainer_feedback" class="form-control" rows="2" placeholder="e.g., Improved posture on bench, focus on slow eccentric descent..." style="border: var(--gf-border); border-radius: var(--gf-radius-md);"></textarea>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">Weight (kg) <span class="text-muted fw-normal">(Optional)</span></label>
                            <input type="number" step="0.1" min="20" max="350" name="weight_kg" class="form-control" placeholder="e.g. 74.5" style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">Body Fat % <span class="text-muted fw-normal">(Optional)</span></label>
                            <input type="number" step="0.1" min="2" max="70" name="body_fat_percentage" class="form-control" placeholder="e.g. 18.2" style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="gf-btn gf-btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="gf-btn gf-btn-primary">
                        <i class="bi bi-save me-1"></i> Save Workout Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
