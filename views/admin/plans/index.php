<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-white mb-1"><i class="bi bi-tags-fill me-2 text-info"></i> Membership Plans</h3>
        <p class="text-secondary small mb-0">Configure standard packages, duration, pricing, and admission fees</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/plans/create" class="btn btn-fp-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Create Plan
    </a>
</div>

<div class="row g-4">
    <?php if (empty($plans)): ?>
        <div class="col-12">
            <div class="fp-card text-center py-5">
                <i class="bi bi-tag-fill display-4 text-secondary mb-3 d-block"></i>
                <h5 class="text-white">No Plans Configured</h5>
                <p class="text-secondary small mb-3">Create your first membership plan (e.g. Monthly, Yearly).</p>
                <a href="<?= BASE_URL ?>/admin/plans/create" class="btn btn-fp-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Create Plan
                </a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($plans as $plan): ?>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="fp-card h-100 d-flex flex-column justify-content-between position-relative">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-secondary bg-opacity-25 text-light fw-bold"><?= (int)$plan['duration_days'] ?> Days</span>
                            <?php if ($plan['is_active']): ?>
                                <span class="badge-fp-active"><i class="bi bi-check-circle me-1"></i> Active</span>
                            <?php else: ?>
                                <span class="badge-fp-expired"><i class="bi bi-x-circle me-1"></i> Disabled</span>
                            <?php endif; ?>
                        </div>

                        <h5 class="fw-bold text-white mb-1"><?= htmlspecialchars($plan['name'], ENT_QUOTES, 'UTF-8') ?></h5>
                        <div class="display-6 fw-bold text-success mb-2"><?= View::currency($plan['price']) ?></div>

                        <div class="p-2 bg-dark bg-opacity-50 rounded-3 mb-3 fs-8 text-secondary">
                            <?php if ($plan['admission_fee'] > 0): ?>
                                <div><i class="bi bi-plus-circle me-1 text-warning"></i> Admission Fee: <strong><?= View::currency($plan['admission_fee']) ?></strong></div>
                            <?php else: ?>
                                <div><i class="bi bi-check me-1 text-success"></i> Zero Admission Fee</div>
                            <?php endif; ?>
                            <div><i class="bi bi-building me-1"></i> <?= htmlspecialchars($plan['branch_name'] ?: 'All Branches', ENT_QUOTES, 'UTF-8') ?></div>
                            <div><i class="bi bi-people me-1 text-info"></i> Active Members: <strong><?= (int)$plan['active_subscribers'] ?></strong></div>
                        </div>

                        <p class="text-secondary small mb-3">
                            <?= htmlspecialchars($plan['description'] ?: 'Complete equipment and fitness floor access.', ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>

                    <div class="d-flex gap-2 pt-2 border-top border-secondary border-opacity-25">
                        <a href="<?= BASE_URL ?>/admin/plans/<?= $plan['id'] ?>/edit" class="btn btn-sm btn-fp-secondary flex-grow-1">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        <form action="<?= BASE_URL ?>/admin/plans/<?= $plan['id'] ?>/toggle" method="POST" class="d-inline">
                            <?= CSRF::getField() ?>
                            <button type="submit" class="btn btn-sm <?= $plan['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="Toggle status">
                                <i class="bi <?= $plan['is_active'] ? 'bi-power' : 'bi-check-lg' ?>"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
