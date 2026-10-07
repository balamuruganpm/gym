<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-white-glow mb-1"><i class="bi bi-person-badge-fill me-2 text-danger"></i> Staff & Trainers</h3>
        <p class="text-secondary small mb-0">Manage gym personnel, managers, front desk staff, and personal fitness coaches</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/staff/create" class="btn btn-fp-primary btn-sm">
        <i class="bi bi-person-plus-fill me-1"></i> Add Personnel
    </a>
</div>

<div class="fp-card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary fs-8 text-uppercase">
                    <th>Personnel</th>
                    <th>Role</th>
                    <th>Branch</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="fs-7">
                <?php if (empty($staffList)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary">No personnel found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($staffList as $staff): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary bg-opacity-25 rounded-circle p-2 me-2 text-center" style="width: 36px; height: 36px;">
                                        <?php if ($staff['role'] === 'trainer'): ?>
                                            <i class="bi bi-activity text-danger"></i>
                                        <?php elseif ($staff['role'] === 'manager'): ?>
                                            <i class="bi bi-briefcase text-info"></i>
                                        <?php elseif ($staff['role'] === 'owner'): ?>
                                            <i class="bi bi-person-workspace text-primary"></i>
                                        <?php else: ?>
                                            <i class="bi bi-person text-warning"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-white-glow"><?= htmlspecialchars($staff['name'] ?? $staff['full_name'] ?? 'Staff Member', ENT_QUOTES, 'UTF-8') ?></div>
                                        <small class="text-secondary"><?= htmlspecialchars($staff['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php
                                $roleBadge = 'bg-secondary';
                                if ($staff['role'] === 'owner') $roleBadge = 'bg-primary';
                                elseif ($staff['role'] === 'manager') $roleBadge = 'bg-info text-dark';
                                elseif ($staff['role'] === 'trainer') $roleBadge = 'bg-danger';
                                elseif ($staff['role'] === 'staff') $roleBadge = 'bg-warning text-dark';
                                ?>
                                <span class="badge <?= $roleBadge ?> text-uppercase fs-8"><?= htmlspecialchars($staff['role'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <span class="text-white"><?= htmlspecialchars($staff['branch_name'] ?: 'All Branches', ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <div class="small text-light"><i class="bi bi-envelope me-1 text-secondary"></i> <?= htmlspecialchars($staff['email'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if ($staff['phone']): ?>
                                    <div class="small text-secondary"><i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($staff['phone'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($staff['is_active']): ?>
                                    <span class="badge-fp-active"><i class="bi bi-check-circle me-1"></i> Active</span>
                                <?php else: ?>
                                    <span class="badge-fp-expired"><i class="bi bi-x-circle me-1"></i> Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="<?= BASE_URL ?>/admin/staff/<?= $staff['id'] ?>/edit" class="btn btn-sm btn-outline-secondary" title="Edit account">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="<?= BASE_URL ?>/admin/staff/<?= $staff['id'] ?>/toggle" method="POST" class="d-inline">
                                        <?= CSRF::getField() ?>
                                        <button type="submit" class="btn btn-sm <?= $staff['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="Toggle active status">
                                            <i class="bi <?= $staff['is_active'] ? 'bi-power' : 'bi-check-lg' ?>"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
