<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-white mb-1"><?= $isEdit ? 'Edit Membership Plan' : 'Create Membership Plan' ?></h3>
        <p class="text-secondary small mb-0"><?= $isEdit ? 'Modify plan duration, cost, and perks' : 'Define a new subscription package for members' ?></p>
    </div>
    <a href="<?= BASE_URL ?>/admin/plans" class="btn btn-fp-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Plans
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="fp-card">
            <form action="<?= $isEdit ? BASE_URL . '/admin/plans/' . $plan['id'] . '/update' : BASE_URL . '/admin/plans' ?>" method="POST">
                <?= CSRF::getField() ?>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="branch_id">Gym Branch *</label>
                        <select class="form-select" id="branch_id" name="branch_id" required>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= $branch['id'] ?>" <?= ($isEdit && $plan['branch_id'] == $branch['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($branch['name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($branch['code'], ENT_QUOTES, 'UTF-8') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="name">Plan Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= $isEdit ? htmlspecialchars($plan['name'], ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="e.g. Quarterly Pro VIP" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="duration_days">Duration (in Days) *</label>
                        <input type="number" class="form-control" id="duration_days" name="duration_days" value="<?= $isEdit ? (int)$plan['duration_days'] : '30' ?>" min="1" max="3650" required>
                        <small class="text-secondary fs-8">e.g. 30 (1 Mo), 90 (3 Mo), 365 (1 Yr)</small>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="price">Plan Price (<?= APP_CURRENCY_SYMBOL ?>) *</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= $isEdit ? $plan['price'] : '' ?>" placeholder="1500.00" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="admission_fee">Admission / Reg Fee (<?= APP_CURRENCY_SYMBOL ?>)</label>
                        <input type="number" step="0.01" class="form-control" id="admission_fee" name="admission_fee" value="<?= $isEdit ? $plan['admission_fee'] : '0.00' ?>" placeholder="0.00">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">Plan Description / Perks</label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Access to cardio, strength area, locker access, free orientation session..."><?= $isEdit ? htmlspecialchars($plan['description'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                </div>

                <div class="mb-4 form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" <?= (!$isEdit || $plan['is_active']) ? 'checked' : '' ?>>
                    <label class="form-check-label text-white" for="is_active">Plan is Active & Available for Registration</label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/admin/plans" class="btn btn-fp-secondary">Cancel</a>
                    <button type="submit" class="btn btn-fp-primary">
                        <i class="bi bi-check-lg me-1"></i> <?= $isEdit ? 'Update Plan' : 'Save Plan' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
