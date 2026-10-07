<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-activity text-info me-2"></i>My Transformation</h4>
        <p class="text-secondary small mb-0">Track photos & body measurements</p>
    </div>
    <div>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadCheckpointModal">
            <i class="bi bi-plus-circle me-1"></i> Add Checkpoint
        </button>
    </div>
</div>

<!-- Delta Summary (if 2+ checkpoints) -->
<?php if (!empty($comparison) && !empty($comparison['deltas'])): ?>
    <?php $d = $comparison['deltas']; ?>
    <div class="fp-card p-3 mb-3 rounded-4 border-start border-info border-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold text-info small"><i class="bi bi-award-fill me-1"></i> Overall Progress</span>
            <span class="badge bg-secondary"><?= $d['days_between'] ?? 0 ?> Days Apart</span>
        </div>
        <div class="row g-2 text-center">
            <div class="col-4">
                <div class="text-secondary small" style="font-size: 11px;">Weight</div>
                <div class="fw-bold <?= (($d['weight_diff'] ?? 0) <= 0) ? 'text-success' : 'text-primary' ?>">
                    <?= (($d['weight_diff'] ?? 0) > 0 ? '+' : '') . ($d['weight_diff'] ?? 0) ?> kg
                </div>
            </div>
            <div class="col-4">
                <div class="text-secondary small" style="font-size: 11px;">Waist</div>
                <div class="fw-bold <?= (($d['waist_diff'] ?? 0) <= 0) ? 'text-success' : 'text-warning' ?>">
                    <?= (($d['waist_diff'] ?? 0) > 0 ? '+' : '') . ($d['waist_diff'] ?? 0) ?> in
                </div>
            </div>
            <div class="col-4">
                <div class="text-secondary small" style="font-size: 11px;">Arms</div>
                <div class="fw-bold text-info">
                    <?= (($d['arms_diff'] ?? 0) > 0 ? '+' : '') . ($d['arms_diff'] ?? 0) ?> in
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Checkpoints List -->
<?php if (empty($checkpoints)): ?>
    <div class="fp-card p-4 text-center rounded-4">
        <i class="bi bi-camera fs-1 text-muted mb-2"></i>
        <h6 class="fw-bold">No Milestones Logged Yet</h6>
        <p class="text-muted small">Upload your day-1 baseline photos to begin tracking your transformation journey!</p>
        <div>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadCheckpointModal">
                <i class="bi bi-cloud-upload me-1"></i> Log Baseline Checkpoint
            </button>
        </div>
    </div>
<?php else: ?>
    <div class="d-flex flex-column gap-3">
        <?php foreach ($checkpoints as $cp): ?>
            <div class="fp-card rounded-4 overflow-hidden">
                <div class="card-header bg-secondary bg-opacity-25 py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="badge bg-primary text-uppercase small"><?= str_replace('_', ' ', $cp['checkpoint_type'] ?? '') ?></span>
                    <span class="text-muted small"><?= !empty($cp['recorded_date']) ? date('d M Y', strtotime($cp['recorded_date'])) : '' ?></span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 text-center mb-3 bg-secondary bg-opacity-10 p-2 rounded-3">
                        <div class="col-4">
                            <div class="text-muted" style="font-size: 10px;">WEIGHT</div>
                            <div class="fw-bold small"><?= !empty($cp['weight_kg']) ? $cp['weight_kg'] . ' kg' : '-' ?></div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted" style="font-size: 10px;">WAIST</div>
                            <div class="fw-bold small"><?= !empty($cp['waist_in']) ? $cp['waist_in'] . ' in' : '-' ?></div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted" style="font-size: 10px;">CHEST</div>
                            <div class="fw-bold small"><?= !empty($cp['chest_in']) ? $cp['chest_in'] . ' in' : '-' ?></div>
                        </div>
                    </div>

                    <!-- Photo Gallery (Horizontal Scroll) -->
                    <div class="d-flex gap-2 overflow-auto pb-2">
                        <?php if (!empty($cp['photo_front']) && file_exists(__DIR__ . '/../../../' . $cp['photo_front'])): ?>
                            <div class="text-center">
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($cp['photo_front']) ?>" class="rounded-3 border border-secondary" style="width: 140px; height: 180px; object-fit: cover;">
                                <div class="text-muted small mt-1" style="font-size: 10px;">Front</div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($cp['photo_back']) && file_exists(__DIR__ . '/../../../' . $cp['photo_back'])): ?>
                            <div class="text-center">
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($cp['photo_back']) ?>" class="rounded-3 border border-secondary" style="width: 140px; height: 180px; object-fit: cover;">
                                <div class="text-muted small mt-1" style="font-size: 10px;">Back</div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($cp['photo_side']) && file_exists(__DIR__ . '/../../../' . $cp['photo_side'])): ?>
                            <div class="text-center">
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($cp['photo_side']) ?>" class="rounded-3 border border-secondary" style="width: 140px; height: 180px; object-fit: cover;">
                                <div class="text-muted small mt-1" style="font-size: 10px;">Side</div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($cp['notes'])): ?>
                        <div class="text-muted small mt-2 p-2 bg-secondary bg-opacity-10 rounded">
                            <i class="bi bi-chat-left-text me-1"></i> <?= htmlspecialchars($cp['notes']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal: Upload Checkpoint -->
<div class="modal fade" id="uploadCheckpointModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-camera-fill text-primary me-2"></i>Log Progress Checkpoint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/member/progress/store" method="POST" enctype="multipart/form-data">
                <?= CSRF::field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Milestone Name *</label>
                        <select name="checkpoint_type" class="form-select border-secondary" required>
                            <option value="initial">Initial / Baseline (Day 1)</option>
                            <option value="30_days">30 Days Progress</option>
                            <option value="60_days">60 Days Progress</option>
                            <option value="90_days">90 Days Progress</option>
                            <option value="custom" selected>Custom Progress</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">Weight (kg)</label>
                            <input type="number" step="0.1" name="weight_kg" class="form-control border-secondary" placeholder="e.g. 72.5">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">Waist (inches)</label>
                            <input type="number" step="0.1" name="waist_in" class="form-control border-secondary" placeholder="e.g. 32.0">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">Chest (inches)</label>
                            <input type="number" step="0.1" name="chest_in" class="form-control border-secondary" placeholder="e.g. 38.0">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">Arms (inches)</label>
                            <input type="number" step="0.1" name="arms_in" class="form-control border-secondary" placeholder="e.g. 14.5">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Front Angle Photo</label>
                        <input type="file" name="photo_front" class="form-control border-secondary" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Privacy Setting *</label>
                        <select name="privacy_level" class="form-select border-secondary" required>
                            <option value="me_staff" selected>Member + Staff + Trainer</option>
                            <option value="me_trainer">Member + Trainer Only</option>
                            <option value="only_me">Only Me (Confidential)</option>
                            <option value="community">Community / Hall of Fame</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Personal Notes / Workout Reflection</label>
                        <textarea name="notes" class="form-control border-secondary" rows="2" placeholder="Feeling energetic, hit new PR on bench press!"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-upload me-1"></i> Save Milestone</button>
                </div>
            </form>
        </div>
    </div>
</div>
