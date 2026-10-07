<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <a href="<?= BASE_URL ?>/admin/members/<?= $member['id'] ?>" class="text-decoration-none small text-secondary mb-1 d-block">
            <i class="bi bi-arrow-left"></i> Back to <?= htmlspecialchars($member['name']) ?>'s Profile
        </a>
        <h2 class="h3 fw-bold mb-1"><i class="bi bi-activity text-primary me-2"></i>Transformation & Progress Timeline</h2>
        <p class="text-secondary small mb-0">Member: <span class="fw-bold"><?= htmlspecialchars($member['name']) ?></span> (<?= htmlspecialchars($member['member_code']) ?>)</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <?php if (!empty($checkpoints) && count($checkpoints) >= 2): ?>
            <a href="<?= BASE_URL ?>/admin/progress/compare?member_id=<?= $member['id'] ?>" class="btn btn-info me-2">
                <i class="bi bi-symmetry-vertical me-1"></i> Before / After Comparison
            </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/admin/progress/create?member_id=<?= $member['id'] ?>" class="btn btn-fp-primary">
            <i class="bi bi-plus-circle me-1"></i> Log New Checkpoint
        </a>
    </div>
</div>

<!-- Delta Summary Card (if comparison available) -->
<?php if (!empty($comparison) && !empty($comparison['deltas'])): ?>
    <?php $d = $comparison['deltas']; ?>
    <div class="fp-card p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="fw-bold text-primary"><i class="bi bi-award-fill me-1"></i> Transformation Journey Summary</div>
            <div class="badge bg-secondary bg-opacity-25 text-secondary"><?= $d['days_between'] ?> Days Transformed</div>
        </div>
        <div class="row g-3 text-center">
            <div class="col-md-2 col-4">
                <div class="text-secondary small">Weight</div>
                <div class="fs-5 fw-bold <?= ($d['weight_diff'] <= 0) ? 'text-success' : 'text-primary' ?>">
                    <?= ($d['weight_diff'] > 0 ? '+' : '') . $d['weight_diff'] ?> kg
                </div>
            </div>
            <div class="col-md-2 col-4">
                <div class="text-secondary small">Chest</div>
                <div class="fs-5 fw-bold text-info">
                    <?= ($d['chest_diff'] > 0 ? '+' : '') . $d['chest_diff'] ?> in
                </div>
            </div>
            <div class="col-md-2 col-4">
                <div class="text-secondary small">Waist</div>
                <div class="fs-5 fw-bold <?= ($d['waist_diff'] <= 0) ? 'text-success' : 'text-warning' ?>">
                    <?= ($d['waist_diff'] > 0 ? '+' : '') . $d['waist_diff'] ?> in
                </div>
            </div>
            <div class="col-md-2 col-4">
                <div class="text-secondary small">Arms</div>
                <div class="fs-5 fw-bold text-info">
                    <?= ($d['arms_diff'] > 0 ? '+' : '') . $d['arms_diff'] ?> in
                </div>
            </div>
            <div class="col-md-2 col-4">
                <div class="text-secondary small">Thighs</div>
                <div class="fs-5 fw-bold text-info">
                    <?= ($d['thighs_diff'] > 0 ? '+' : '') . $d['thighs_diff'] ?> in
                </div>
            </div>
            <div class="col-md-2 col-4">
                <div class="text-secondary small">Body Fat %</div>
                <div class="fs-5 fw-bold <?= ($d['fat_diff'] <= 0) ? 'text-success' : 'text-danger' ?>">
                    <?= ($d['fat_diff'] > 0 ? '+' : '') . $d['fat_diff'] ?> %
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Checkpoints Timeline -->
<?php if (empty($checkpoints)): ?>
    <div class="fp-card p-5 text-center">
        <i class="bi bi-camera-fill fs-1 text-secondary opacity-50 mb-3"></i>
        <h4 class="fw-bold">No Transformation Checkpoints Yet</h4>
        <p class="text-secondary">Start tracking <?= htmlspecialchars($member['name']) ?>'s fitness journey by uploading a baseline checkpoint.</p>
        <div>
            <a href="<?= BASE_URL ?>/admin/progress/create?member_id=<?= $member['id'] ?>" class="btn btn-fp-primary">
                <i class="bi bi-plus-circle me-1"></i> Log Initial / Baseline Checkpoint
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($checkpoints as $index => $cp): ?>
            <div class="col-12">
                <div class="fp-card p-0 overflow-hidden">
                    <div class="p-3 border-bottom border-secondary border-opacity-15 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary fs-7 text-uppercase"><?= str_replace('_', ' ', $cp['checkpoint_type']) ?></span>
                            <span class="text-secondary small">| Recorded on <?= date('d F Y', strtotime($cp['recorded_date'])) ?></span>
                        </div>
                        <div>
                            <?php 
                            $privBadge = 'bg-secondary bg-opacity-25 text-secondary';
                            if ($cp['privacy_level'] === 'only_me') $privBadge = 'bg-danger bg-opacity-25 text-danger';
                            elseif ($cp['privacy_level'] === 'me_trainer') $privBadge = 'bg-warning bg-opacity-25 text-warning';
                            elseif ($cp['privacy_level'] === 'community') $privBadge = 'bg-success bg-opacity-25 text-success';
                            ?>
                            <span class="badge <?= $privBadge ?> small">
                                <i class="bi bi-shield-lock me-1"></i> <?= strtoupper(str_replace('_', ' ', $cp['privacy_level'])) ?>
                            </span>
                        </div>
                    </div>
                    <div class="p-4">
                        <!-- Measurements Bar -->
                        <div class="row g-2 text-center p-3 mb-3 bg-secondary bg-opacity-10 rounded-3">
                            <div class="col-md-2 col-4">
                                <div class="text-secondary small">Weight</div>
                                <div class="fw-bold fs-6"><?= $cp['weight_kg'] ? $cp['weight_kg'] . ' kg' : '-' ?></div>
                            </div>
                            <div class="col-md-2 col-4">
                                <div class="text-secondary small">Chest</div>
                                <div class="fw-bold fs-6"><?= $cp['chest_in'] ? $cp['chest_in'] . ' in' : '-' ?></div>
                            </div>
                            <div class="col-md-2 col-4">
                                <div class="text-secondary small">Waist</div>
                                <div class="fw-bold fs-6"><?= $cp['waist_in'] ? $cp['waist_in'] . ' in' : '-' ?></div>
                            </div>
                            <div class="col-md-2 col-4">
                                <div class="text-secondary small">Arms</div>
                                <div class="fw-bold fs-6"><?= $cp['arms_in'] ? $cp['arms_in'] . ' in' : '-' ?></div>
                            </div>
                            <div class="col-md-2 col-4">
                                <div class="text-secondary small">Thighs</div>
                                <div class="fw-bold fs-6"><?= $cp['thighs_in'] ? $cp['thighs_in'] . ' in' : '-' ?></div>
                            </div>
                            <div class="col-md-2 col-4">
                                <div class="text-secondary small">Body Fat %</div>
                                <div class="fw-bold fs-6"><?= $cp['body_fat_pct'] ? $cp['body_fat_pct'] . ' %' : '-' ?></div>
                            </div>
                        </div>

                        <!-- Photos Grid -->
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-secondary small mb-1 fw-bold text-center">FRONT ANGLE</div>
                                <?php if (!empty($cp['photo_front']) && file_exists(BASE_PATH . '/' . ltrim($cp['photo_front'], '/'))): ?>
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars(ltrim($cp['photo_front'], '/')) ?>" class="img-fluid rounded-3 border border-secondary border-opacity-25 w-100" style="height: 320px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-secondary bg-opacity-10 rounded-3 d-flex flex-column align-items-center justify-content-center text-secondary border border-secondary border-opacity-25" style="height: 320px;">
                                        <i class="bi bi-image fs-1 mb-2 opacity-50"></i>
                                        <span>No Front Photo</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4">
                                <div class="text-secondary small mb-1 fw-bold text-center">BACK ANGLE</div>
                                <?php if (!empty($cp['photo_back']) && file_exists(BASE_PATH . '/' . ltrim($cp['photo_back'], '/'))): ?>
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars(ltrim($cp['photo_back'], '/')) ?>" class="img-fluid rounded-3 border border-secondary border-opacity-25 w-100" style="height: 320px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-secondary bg-opacity-10 rounded-3 d-flex flex-column align-items-center justify-content-center text-secondary border border-secondary border-opacity-25" style="height: 320px;">
                                        <i class="bi bi-image fs-1 mb-2 opacity-50"></i>
                                        <span>No Back Photo</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4">
                                <div class="text-secondary small mb-1 fw-bold text-center">SIDE ANGLE</div>
                                <?php if (!empty($cp['photo_side']) && file_exists(BASE_PATH . '/' . ltrim($cp['photo_side'], '/'))): ?>
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars(ltrim($cp['photo_side'], '/')) ?>" class="img-fluid rounded-3 border border-secondary border-opacity-25 w-100" style="height: 320px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-secondary bg-opacity-10 rounded-3 d-flex flex-column align-items-center justify-content-center text-secondary border border-secondary border-opacity-25" style="height: 320px;">
                                        <i class="bi bi-image fs-1 mb-2 opacity-50"></i>
                                        <span>No Side Photo</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($cp['notes'])): ?>
                            <div class="mt-3 p-3 bg-secondary bg-opacity-10 rounded-3 border border-secondary border-opacity-25 small text-secondary">
                                <strong class="text-body">Trainer / Member Notes:</strong> <?= htmlspecialchars($cp['notes']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
