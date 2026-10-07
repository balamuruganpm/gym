<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-people-fill me-2 text-danger"></i> Member Directory</h3>
        <p class="text-secondary small mb-0">Search, filter, manage subscriptions, renewals, and member profiles</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/members/create" class="btn btn-fp-primary btn-sm">
        <i class="bi bi-person-plus-fill me-1"></i> Register Member
    </a>
</div>

<!-- Search & Status Filter Bar -->
<div class="fp-card mb-4 p-3">
    <form action="<?= BASE_URL ?>/admin/members" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text border-secondary border-opacity-25 text-secondary">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control form-control-sm" name="search" value="<?= htmlspecialchars($searchQuery ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Search by name, member code (GYM-1001), phone, or email...">
            </div>
        </div>
        <div class="col-6 col-md-4">
            <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                <option value="">-- All Statuses --</option>
                <option value="active" <?= ($selectedStatus === 'active') ? 'selected' : '' ?>>● Active</option>
                <option value="expiring_soon" <?= ($selectedStatus === 'expiring_soon') ? 'selected' : '' ?>>● Expiring Soon (&lt;= 7 days)</option>
                <option value="expired" <?= ($selectedStatus === 'expired') ? 'selected' : '' ?>>● Expired</option>
                <option value="frozen" <?= ($selectedStatus === 'frozen') ? 'selected' : '' ?>>● Frozen</option>
                <option value="cancelled" <?= ($selectedStatus === 'cancelled') ? 'selected' : '' ?>>● Cancelled</option>
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-fp-secondary btn-sm flex-grow-1">Filter</button>
            <?php if (!empty($searchQuery) || !empty($selectedStatus)): ?>
                <a href="<?= BASE_URL ?>/admin/members" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Member List Table -->
<div class="fp-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary fs-8 text-uppercase">
                    <th>Member Profile</th>
                    <th>Branch</th>
                    <th>Current Plan</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    <th>Last Check-in</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="fs-7">
                <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="bi bi-people display-5 d-block mb-2 text-secondary opacity-50"></i>
                            No members found matching the specified filters.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($members as $m): ?>
                        <?php
                        $currentStatus = $m['current_status'] ?? $m['status'] ?? 'active';
                        $statusClass = 'badge-fp-active';
                        if ($currentStatus === 'expiring_soon') $statusClass = 'badge-fp-expiring';
                        elseif ($currentStatus === 'expired') $statusClass = 'badge-fp-expired';
                        elseif ($currentStatus === 'frozen') $statusClass = 'badge-fp-frozen';
                        elseif ($currentStatus === 'cancelled') $statusClass = 'badge-fp-expired';
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary bg-opacity-25 rounded-circle p-2 me-2 text-center" style="width: 38px; height: 38px;">
                                        <i class="bi bi-person-fill text-secondary"></i>
                                    </div>
                                    <div>
                                        <a href="<?= BASE_URL ?>/admin/members/<?= $m['id'] ?>" class="fw-bold text-body text-decoration-none">
                                            <?= htmlspecialchars($m['full_name'] ?? $m['name'] ?? 'Member', ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                        <div class="text-secondary fs-8">
                                            <span class="badge bg-secondary bg-opacity-25"><?= htmlspecialchars($m['member_code'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                            <?= htmlspecialchars($m['phone'] ?? $m['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-secondary small"><?= htmlspecialchars($m['branch_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <span class="fw-semibold text-body"><?= htmlspecialchars($m['plan_name'] ?? 'No Active Plan', ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <?php if (!empty($m['end_date'])): ?>
                                    <div class="text-body"><?= date('d M Y', strtotime($m['end_date'])) ?></div>
                                    <?php if (isset($m['days_remaining']) && (int)$m['days_remaining'] > 0): ?>
                                        <small class="text-success fs-8"><?= (int)$m['days_remaining'] ?> days left</small>
                                    <?php elseif (isset($m['days_remaining'])): ?>
                                        <small class="text-danger fs-8">Expired <?= abs((int)$m['days_remaining']) ?>d ago</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-secondary">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="<?= $statusClass ?> text-uppercase fs-8"><?= htmlspecialchars(str_replace('_', ' ', $currentStatus), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <span class="text-secondary small"><?= !empty($m['last_visit_date']) ? date('d M Y', strtotime($m['last_visit_date'])) : 'Never' ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/admin/members/<?= $m['id'] ?>" class="btn btn-sm btn-fp-secondary" title="View Full Profile">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
