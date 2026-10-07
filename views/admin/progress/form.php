<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <a href="<?= BASE_URL ?>/admin/progress?member_id=<?= $member['id'] ?>" class="text-decoration-none small text-secondary mb-1 d-block">
            <i class="bi bi-arrow-left"></i> Back to Timeline
        </a>
        <h2 class="h3 fw-bold mb-1"><i class="bi bi-camera-fill text-primary me-2"></i>Log Transformation Checkpoint</h2>
        <p class="text-secondary small mb-0">Record photos & measurements for <span class="fw-bold"><?= htmlspecialchars($member['name']) ?></span> (<?= htmlspecialchars($member['member_code']) ?>)</p>
    </div>
</div>

<div class="fp-card p-4 p-md-5">
    <form action="<?= BASE_URL ?>/admin/progress/store" method="POST" enctype="multipart/form-data">
        <?= CSRF::getField() ?>
        <input type="hidden" name="member_id" value="<?= $member['id'] ?>">

        <h5 class="fw-bold text-primary mb-3"><i class="bi bi-calendar-event me-2"></i>1. Checkpoint Details</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Checkpoint Milestone *</label>
                <select name="checkpoint_type" class="form-select" required>
                    <option value="initial">Initial / Baseline (Day 1)</option>
                    <option value="30_days">30 Days Milestone</option>
                    <option value="60_days">60 Days Milestone</option>
                    <option value="90_days">90 Days Milestone (Quarterly)</option>
                    <option value="6_months">6 Months Milestone</option>
                    <option value="1_year">1 Year Milestone</option>
                    <option value="custom" selected>Custom Progress Check</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Recorded Date *</label>
                <input type="date" name="recorded_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Privacy & Visibility Level *</label>
                <select name="privacy_level" class="form-select" required>
                    <option value="me_staff" selected>Member + Staff + Trainer (Recommended)</option>
                    <option value="me_trainer">Member + Trainer Only (Private Coaching)</option>
                    <option value="only_me">Only Member (Strictly Confidential)</option>
                    <option value="community">Community / Hall of Fame (Public Promo)</option>
                </select>
            </div>
        </div>

        <h5 class="fw-bold text-primary mb-3"><i class="bi bi-rulers me-2"></i>2. Body Measurements</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-2 col-6">
                <label class="form-label">Weight (kg)</label>
                <input type="number" step="0.1" name="weight_kg" class="form-control" placeholder="e.g. 74.5">
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label">Chest (in)</label>
                <input type="number" step="0.1" name="chest_in" class="form-control" placeholder="e.g. 38.5">
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label">Waist (in)</label>
                <input type="number" step="0.1" name="waist_in" class="form-control" placeholder="e.g. 32.0">
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label">Arms (in)</label>
                <input type="number" step="0.1" name="arms_in" class="form-control" placeholder="e.g. 14.5">
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label">Thighs (in)</label>
                <input type="number" step="0.1" name="thighs_in" class="form-control" placeholder="e.g. 22.0">
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label">Body Fat %</label>
                <input type="number" step="0.1" name="body_fat_pct" class="form-control" placeholder="e.g. 16.5">
            </div>
        </div>

        <h5 class="fw-bold text-primary mb-3"><i class="bi bi-images me-2"></i>3. Progress Photos</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Front Angle Photo</label>
                <input type="file" name="photo_front" class="form-control" accept="image/*">
                <div class="form-text small">Front pose, standing straight</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Back Angle Photo</label>
                <input type="file" name="photo_back" class="form-control" accept="image/*">
                <div class="form-text small">Back / rear view posture</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Side Angle Photo</label>
                <input type="file" name="photo_side" class="form-control" accept="image/*">
                <div class="form-text small">Side profile / posture</div>
            </div>
        </div>

        <h5 class="fw-bold text-primary mb-3"><i class="bi bi-journal-text me-2"></i>4. Coach / Trainer Assessment Notes</h5>
        <div class="mb-4">
            <textarea name="notes" class="form-control" rows="3" placeholder="Diet observations, muscle development notes, cardio stamina remarks..."></textarea>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="<?= BASE_URL ?>/admin/progress?member_id=<?= $member['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-fp-primary px-4">
                <i class="bi bi-save me-1"></i> Save Checkpoint Record
            </button>
        </div>
    </form>
</div>
