<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-white mb-1"><i class="bi bi-building me-2 text-primary"></i> Branch Management</h3>
        <p class="text-secondary small mb-0">Configure gym locations, operating hours, and multi-branch access</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/branches/create" class="btn btn-fp-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Add New Branch
    </a>
</div>

<div class="row g-4">
    <?php if (empty($branches)): ?>
        <div class="col-12">
            <div class="fp-card text-center py-5">
                <i class="bi bi-building-slash display-4 text-secondary mb-3 d-block"></i>
                <h5 class="text-white">No Branches Found</h5>
                <p class="text-secondary small mb-3">Add your first gym branch to begin registering members and staff.</p>
                <a href="<?= BASE_URL ?>/admin/branches/create" class="btn btn-fp-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Add Branch
                </a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($branches as $branch): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="fp-card h-100 d-flex flex-column justify-content-between position-relative">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-secondary bg-opacity-25 text-light fw-bold"><?= htmlspecialchars($branch['code'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if ($branch['is_active']): ?>
                                <span class="badge-fp-active"><i class="bi bi-check-circle me-1"></i> Active</span>
                            <?php else: ?>
                                <span class="badge-fp-expired"><i class="bi bi-x-circle me-1"></i> Inactive</span>
                            <?php endif; ?>
                        </div>

                        <h5 class="fw-bold text-white mb-1"><?= htmlspecialchars($branch['name'], ENT_QUOTES, 'UTF-8') ?></h5>
                        <p class="text-secondary small mb-3">
                            <i class="bi bi-geo-alt me-1 text-danger"></i> <?= htmlspecialchars($branch['address'] ?: 'No address specified', ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <div class="p-3 bg-dark bg-opacity-50 rounded-3 mb-3 fs-8">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-secondary"><i class="bi bi-telephone me-1"></i> Phone:</span>
                                <span class="text-white"><?= htmlspecialchars($branch['phone'] ?: '-', ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-secondary"><i class="bi bi-clock me-1"></i> Hours:</span>
                                <span class="text-white"><?= date('h:i A', strtotime($branch['opening_time'])) ?> - <?= date('h:i A', strtotime($branch['closing_time'])) ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-secondary"><i class="bi bi-people me-1"></i> Members / Staff:</span>
                                <span class="text-success fw-bold"><?= (int)$branch['total_members'] ?> / <?= (int)$branch['total_staff'] ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-2 border-top border-secondary border-opacity-25">
                        <a href="<?= BASE_URL ?>/admin/branches/<?= $branch['id'] ?>/edit" class="btn btn-sm btn-fp-secondary flex-grow-1">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        <form action="<?= BASE_URL ?>/admin/branches/<?= $branch['id'] ?>/toggle" method="POST" class="d-inline">
                            <?= CSRF::getField() ?>
                            <button type="submit" class="btn btn-sm <?= $branch['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="Toggle status">
                                <i class="bi <?= $branch['is_active'] ? 'bi-power' : 'bi-check-lg' ?>"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
