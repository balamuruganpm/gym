<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-white mb-1"><?= $isEdit ? 'Edit Branch' : 'Add New Branch' ?></h3>
        <p class="text-secondary small mb-0"><?= $isEdit ? 'Update branch address, hours, and contact information' : 'Create a new location in your gym network' ?></p>
    </div>
    <a href="<?= BASE_URL ?>/admin/branches" class="btn btn-fp-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Branches
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="fp-card">
            <form action="<?= $isEdit ? BASE_URL . '/admin/branches/' . $branch['id'] . '/update' : BASE_URL . '/admin/branches' ?>" method="POST">
                <?= CSRF::getField() ?>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="code">Branch Code *</label>
                        <input type="text" class="form-control" id="code" name="code" value="<?= $isEdit ? htmlspecialchars($branch['code'], ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="e.g. BR-NORTH" required>
                        <small class="text-secondary fs-8">Unique identifier (e.g. BR-MAIN)</small>
                    </div>
                    <div class="col-12 col-md-8">
                        <label class="form-label" for="name">Branch Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= $isEdit ? htmlspecialchars($branch['name'], ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="e.g. FitPulse Downtown Arena" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="address">Full Address</label>
                    <textarea class="form-control" id="address" name="address" rows="2" placeholder="Street, landmark, city, postal code"><?= $isEdit ? htmlspecialchars($branch['address'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="phone">Contact Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= $isEdit ? htmlspecialchars($branch['phone'], ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="+91 98765 43210">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="email">Branch Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $isEdit ? htmlspecialchars($branch['email'], ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="branch@fitpulse.test">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="opening_time">Opening Time</label>
                        <input type="time" class="form-control" id="opening_time" name="opening_time" value="<?= $isEdit ? $branch['opening_time'] : '05:30' ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="closing_time">Closing Time</label>
                        <input type="time" class="form-control" id="closing_time" name="closing_time" value="<?= $isEdit ? $branch['closing_time'] : '22:30' ?>" required>
                    </div>
                </div>

                <div class="mb-4 form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" <?= (!$isEdit || $branch['is_active']) ? 'checked' : '' ?>>
                    <label class="form-check-label text-white" for="is_active">Branch is Active & Accepting Check-ins</label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/admin/branches" class="btn btn-fp-secondary">Cancel</a>
                    <button type="submit" class="btn btn-fp-primary">
                        <i class="bi bi-check-lg me-1"></i> <?= $isEdit ? 'Update Branch' : 'Save Branch' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
