<div class="mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-bold mb-1">Membership Plans</h2>
            <p class="text-muted small mb-0">Configure pricing packages, durations and access tiers for your members.</p>
        </div>
        <a href="<?= url('plans/create') ?>" class="btn gf-btn-primary fw-semibold shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Create Plan
        </a>
    </div>
</div>

<!-- Flash Alerts -->
<?= render_component('alert') ?>

<div class="row g-4">
    <?php if (empty($plans)): ?>
        <div class="col-12">
            <div class="gf-card p-5 text-center text-muted">
                <i class="bi bi-box-seam fs-1 mb-3 text-secondary d-block"></i>
                <h5 class="fw-bold text-dark">No Membership Plans Yet</h5>
                <p class="small text-muted mb-3">Create membership options like Monthly, Quarterly, or Annual packages.</p>
                <a href="<?= url('plans/create') ?>" class="btn btn-sm gf-btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add First Plan
                </a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($plans as $plan): ?>
            <div class="col-md-6 col-lg-4">
                <div class="gf-card p-4 h-100 d-flex flex-column justify-content-between position-relative">
                    <div>
                        <!-- Header & Active Badge -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-light text-dark font-monospace border px-2 py-1 rounded-pill small">
                                <i class="bi bi-clock me-1 text-primary"></i>
                                <?= (int) $plan['duration_value'] ?> <?= ucfirst($plan['duration_type']) ?>
                            </span>

                            <form action="<?= url("plans/{$plan['id']}/toggle") ?>" method="POST" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm p-0 border-0" title="Click to toggle status">
                                    <span class="badge <?= $plan['is_active'] ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' ?>">
                                        <?= $plan['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </button>
                            </form>
                        </div>

                        <!-- Plan Name & Description -->
                        <h4 class="fw-bold mb-2 text-dark"><?= e($plan['name']) ?></h4>
                        <?php if (!empty($plan['description'])): ?>
                            <p class="text-muted small mb-3"><?= nl2br(e($plan['description'])) ?></p>
                        <?php else: ?>
                            <p class="text-muted small mb-3 fst-italic">Standard fitness membership package.</p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <!-- Price -->
                        <div class="pt-3 border-top mb-3 d-flex align-items-baseline">
                            <span class="fs-2 fw-bold text-primary"><?= format_currency((float) $plan['price']) ?></span>
                            <span class="text-muted small ms-2">/ <?= $plan['duration_value'] == 1 ? rtrim($plan['duration_type'], 's') : "{$plan['duration_value']} {$plan['duration_type']}" ?></span>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex gap-2">
                            <a href="<?= url("plans/{$plan['id']}/edit") ?>" class="btn btn-sm btn-light border w-100 fw-semibold text-muted">
                                <i class="bi bi-pencil me-1"></i> Edit Plan
                            </a>
                            <form action="<?= url("plans/{$plan['id']}/toggle") ?>" method="POST" class="w-100">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm <?= $plan['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?> w-100 fw-semibold">
                                    <?= $plan['is_active'] ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
