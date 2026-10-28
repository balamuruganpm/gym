<div class="container-fluid p-0">
    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge" style="background: rgba(139, 92, 246, 0.12); color: #7c3aed; border: 1px solid rgba(139, 92, 246, 0.25); border-radius: 9999px; font-weight: 700; padding: 4px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">
                    <i class="bi bi-graph-up me-1"></i> Measurements & Performance
                </span>
                <span class="text-muted" style="font-size: 13px;">Trainee Progress Log</span>
            </div>
            <h2 class="h3 fw-bold mb-0 text-dark">Workout & Training Progress</h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="gf-btn gf-btn-primary" data-bs-toggle="modal" data-bs-target="#newProgressModal">
                <i class="bi bi-plus-circle me-1"></i> Log Workout
            </button>
        </div>
    </div>

    <!-- Progress Records Table -->
    <div class="gf-card">
        <div class="p-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="background: rgba(0, 0, 0, 0.02);">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Recent Workout & Measurement Entries</h5>
                <p class="small text-muted mb-0">Chronological history of recorded workouts and body stats</p>
            </div>
            <span class="badge bg-light text-dark border">
                <?= count($records) ?> Records
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th style="padding-left: 20px;">Date</th>
                        <th>Trainee</th>
                        <th>Workout Summary</th>
                        <th>Coach Feedback</th>
                        <th>Measurements</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-text display-6 d-block mb-2 text-secondary"></i>
                                <div class="fw-semibold text-dark mb-1">No Progress Records Logged</div>
                                <span class="small">Click "Log Workout" above to record a trainee's workout routine and body stats.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $r): ?>
                            <tr>
                                <td style="padding-left: 20px;">
                                    <strong class="text-dark"><?= date('M d, Y', strtotime($r['record_date'])) ?></strong>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></div>
                                    <div class="small text-muted"><code><?= e($r['member_number']) ?></code></div>
                                </td>
                                <td>
                                    <div class="small text-dark" style="max-width: 380px; white-space: pre-line;"><?= e($r['workout_summary']) ?></div>
                                </td>
                                <td>
                                    <div class="small text-muted fst-italic" style="max-width: 280px;"><?= e($r['trainer_feedback'] ?? '—') ?></div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php if (!empty($r['weight_kg'])): ?>
                                            <span class="badge" style="background: rgba(2, 132, 199, 0.12); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25);">
                                                <?= e($r['weight_kg']) ?> kg
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($r['body_fat_percentage'])): ?>
                                            <span class="badge" style="background: rgba(139, 92, 246, 0.12); color: #7c3aed; border: 1px solid rgba(139, 92, 246, 0.25);">
                                                <?= e($r['body_fat_percentage']) ?>% BF
                                            </span>
                                        <?php endif; ?>
                                        <?php if (empty($r['weight_kg']) && empty($r['body_fat_percentage'])): ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Log Workout Modal -->
<div class="modal fade" id="newProgressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: var(--gf-border); border-radius: var(--gf-radius-lg); box-shadow: var(--gf-shadow-hover);">
            <form method="POST" action="<?= url('trainer/progress') ?>">
                <?= csrf_field() ?>
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square text-primary me-1"></i> Log Trainee Workout
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="p_member_id" class="form-label small fw-semibold text-dark">Assigned Trainee <span class="text-danger">*</span></label>
                        <select name="member_id" id="p_member_id" class="form-select form-select-lg" required style="border: var(--gf-border); border-radius: var(--gf-radius-md); font-size: 15px;">
                            <option value="">-- Choose Assigned Member --</option>
                            <?php foreach ($assignedMembers as $m): ?>
                                <option value="<?= e((string)$m['id']) ?>">
                                    <?= e($m['first_name'] . ' ' . $m['last_name']) ?> (<?= e($m['member_number']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="p_record_date" class="form-label small fw-semibold text-dark">Workout Date <span class="text-danger">*</span></label>
                        <input type="date" name="record_date" id="p_record_date" class="form-control" value="<?= date('Y-m-d') ?>" required style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                    </div>

                    <div class="mb-3">
                        <label for="p_workout_summary" class="form-label small fw-semibold text-dark">Workout Summary & Details <span class="text-danger">*</span></label>
                        <textarea name="workout_summary" id="p_workout_summary" class="form-control" rows="3" placeholder="e.g., Leg Day: Squats 4x10 @ 80kg, Leg Press 3x12, Calf Raises..." required style="border: var(--gf-border); border-radius: var(--gf-radius-md);"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="p_trainer_feedback" class="form-label small fw-semibold text-dark">Coach Feedback & Notes</label>
                        <textarea name="trainer_feedback" id="p_trainer_feedback" class="form-control" rows="2" placeholder="e.g., Good depth, keep core tight during heavy lifts..." style="border: var(--gf-border); border-radius: var(--gf-radius-md);"></textarea>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label for="p_weight" class="form-label small fw-semibold text-dark">Weight (kg) <span class="text-muted fw-normal">(Optional)</span></label>
                            <input type="number" step="0.1" min="20" max="350" name="weight_kg" id="p_weight" class="form-control" placeholder="e.g. 72.5" style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                        </div>
                        <div class="col-6">
                            <label for="p_bf" class="form-label small fw-semibold text-dark">Body Fat % <span class="text-muted fw-normal">(Optional)</span></label>
                            <input type="number" step="0.1" min="2" max="70" name="body_fat_percentage" id="p_bf" class="form-control" placeholder="e.g. 17.5" style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="gf-btn gf-btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="gf-btn gf-btn-primary">
                        <i class="bi bi-save me-1"></i> Save Workout Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
