<div class="mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-bold mb-1">Staff & Team Management</h2>
            <p class="text-muted small mb-0">Control staff credentials, assign roles and manage front-desk operational access.</p>
        </div>
        <?php if (user_role() === 'owner'): ?>
            <button type="button" class="btn gf-btn-primary fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                <i class="bi bi-person-plus me-1"></i> Add Team Member
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Flash Alerts -->
<?= render_component('alert') ?>

<div class="gf-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th class="ps-4">Staff Member</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staffList as $staff): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary-subtle text-primary border d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                                    <?= strtoupper(substr($staff['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark"><?= e($staff['name']) ?></div>
                                    <?php if ($staff['user_id'] === $currentUserId): ?>
                                        <span class="badge bg-light text-primary border" style="font-size: 10px;">You</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="small text-muted"><?= e($staff['email']) ?></td>
                        <td class="small"><?= e($staff['phone'] ?? 'N/A') ?></td>
                        <td>
                            <span class="badge <?= $staff['role'] === 'owner' ? 'bg-primary' : 'bg-secondary' ?> text-capitalize px-2 py-1">
                                <?= e($staff['role']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $staff['is_active'] ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' ?>">
                                <?= $staff['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td class="small text-muted">
                            <?= date('d M Y', strtotime($staff['joined_at'])) ?>
                        </td>
                        <td class="text-end pe-4">
                            <?php if (user_role() === 'owner' && $staff['user_id'] !== $currentUserId): ?>
                                <form action="<?= url("staff/{$staff['user_id']}/toggle") ?>" method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm <?= $staff['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?> py-1 px-2 fw-semibold">
                                        <?= $staff['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small fst-italic">Protected</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content gf-modal-content border">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-plus text-primary me-2"></i> Add Team Member
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('staff') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="gf-form-control" placeholder="e.g. Rahul Sharma" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="gf-form-control" placeholder="staff@gym.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Phone Number</label>
                        <input type="text" name="phone" class="gf-form-control" placeholder="9876543210">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Login Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="gf-form-control" placeholder="Minimum 6 characters" minlength="6" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">System Role <span class="text-danger">*</span></label>
                        <select name="role" class="gf-form-select" required>
                            <option value="staff" selected>Staff / Front Desk (Can register members & record payments)</option>
                            <option value="owner">Gym Co-Owner (Full administrative & reversal permissions)</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-light border text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn gf-btn-primary fw-semibold px-4">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
