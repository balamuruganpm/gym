<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-white-glow mb-1"><?= $isEdit ? 'Edit Personnel Account' : 'Add Personnel Account' ?></h3>
        <p class="text-secondary small mb-0"><?= $isEdit ? 'Update role, branch assignment, and credentials' : 'Create an account for a gym manager, front desk staff, or fitness coach' ?></p>
    </div>
    <a href="<?= BASE_URL ?>/admin/staff" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Personnel
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="fp-card p-4">
            <form action="<?= $isEdit ? BASE_URL . '/admin/staff/' . $staff['id'] . '/update' : BASE_URL . '/admin/staff' ?>" method="POST">
                <?= CSRF::getField() ?>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-12">
                        <label class="form-label fw-semibold fs-7 mb-1" for="name">Full Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= $isEdit ? htmlspecialchars($staff['name'] ?? $staff['full_name'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="e.g. Arun Kumar" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="email">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $isEdit ? htmlspecialchars($staff['email'], ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="arun@fitpulse.test" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= $isEdit ? htmlspecialchars($staff['phone'], ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="+91 98765 11223">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="role">Assigned Role *</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="trainer" <?= ($isEdit && $staff['role'] === 'trainer') ? 'selected' : '' ?>>Trainer (Fitness Coach)</option>
                            <option value="staff" <?= ($isEdit && $staff['role'] === 'staff') ? 'selected' : '' ?>>Staff (Front Desk & Check-in)</option>
                            <option value="manager" <?= ($isEdit && $staff['role'] === 'manager') ? 'selected' : '' ?>>Manager (Branch Operations)</option>
                            <option value="owner" <?= ($isEdit && $staff['role'] === 'owner') ? 'selected' : '' ?>>Owner (Full Gym Admin)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="branch_id">Assigned Branch</label>
                        <select class="form-select" id="branch_id" name="branch_id">
                            <option value="">-- All Branches / Global --</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= $branch['id'] ?>" <?= ($isEdit && $staff['branch_id'] == $branch['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($branch['name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($branch['code'], ENT_QUOTES, 'UTF-8') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="password">Password <?= $isEdit ? '<small class="text-secondary">(Leave blank to keep current password)</small>' : '*' ?></label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" <?= $isEdit ? '' : 'required' ?>>
                </div>

                <div class="mb-4 form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" <?= (!$isEdit || $staff['is_active']) ? 'checked' : '' ?>>
                    <label class="form-check-label text-white" for="is_active">Account is Active & Authorized</label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/admin/staff" class="btn btn-fp-secondary">Cancel</a>
                    <button type="submit" class="btn btn-fp-primary">
                        <i class="bi bi-check-lg me-1"></i> <?= $isEdit ? 'Update Personnel' : 'Create Account' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
