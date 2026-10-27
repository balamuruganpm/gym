<?php
$currentStatus = $filters['status'] ?? 'all';
$searchQuery = $filters['search'] ?? '';
$currency = current_gym()['currency_symbol'] ?? '₹';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h2 class="fw-bold m-0">Members</h2>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-7">
                <?= number_format((int) ($pagination['total_items'] ?? 0)) ?> total
            </span>
        </div>
        <p class="text-muted small mb-0">Manage gym memberships, member profiles, and renewal status.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('imports') ?>" class="gf-btn gf-btn-outline">
            <i class="bi bi-cloud-arrow-up"></i> Import
        </a>
        <a href="<?= url('members/create') ?>" class="gf-btn gf-btn-primary">
            <i class="bi bi-person-plus-fill"></i> Add Member
        </a>
    </div>
</div>

<!-- Filter Bar & Search -->
<div class="gf-card p-3 mb-4">
    <div class="row g-3 align-items-center justify-content-between">
        <!-- Status Filter Pills -->
        <div class="col-lg-8">
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= url('members?status=all' . ($searchQuery ? '&search=' . urlencode($searchQuery) : '')) ?>" 
                   class="gf-btn py-1 px-3 fs-7 <?= $currentStatus === 'all' ? 'gf-btn-primary' : 'gf-btn-outline' ?>">
                    All (<?= $counts['all'] ?? 0 ?>)
                </a>
                <a href="<?= url('members?status=active' . ($searchQuery ? '&search=' . urlencode($searchQuery) : '')) ?>" 
                   class="gf-btn py-1 px-3 fs-7 <?= $currentStatus === 'active' ? 'gf-btn-primary' : 'gf-btn-outline' ?>">
                    Active (<?= $counts['active'] ?? 0 ?>)
                </a>
                <a href="<?= url('members?status=expiring_soon' . ($searchQuery ? '&search=' . urlencode($searchQuery) : '')) ?>" 
                   class="gf-btn py-1 px-3 fs-7 <?= $currentStatus === 'expiring_soon' ? 'gf-btn-primary' : 'gf-btn-outline' ?>">
                    Expiring (<?= $counts['expiring_soon'] ?? 0 ?>)
                </a>
                <a href="<?= url('members?status=expired' . ($searchQuery ? '&search=' . urlencode($searchQuery) : '')) ?>" 
                   class="gf-btn py-1 px-3 fs-7 <?= $currentStatus === 'expired' ? 'gf-btn-primary' : 'gf-btn-outline' ?>">
                    Expired (<?= $counts['expired'] ?? 0 ?>)
                </a>
                <a href="<?= url('members?status=dues_pending' . ($searchQuery ? '&search=' . urlencode($searchQuery) : '')) ?>" 
                   class="gf-btn py-1 px-3 fs-7 <?= $currentStatus === 'dues_pending' ? 'gf-btn-primary' : 'gf-btn-outline' ?>">
                    Dues Pending
                </a>
                <a href="<?= url('members?status=archived' . ($searchQuery ? '&search=' . urlencode($searchQuery) : '')) ?>" 
                   class="gf-btn py-1 px-3 fs-7 <?= $currentStatus === 'archived' ? 'gf-btn-primary' : 'gf-btn-outline' ?>">
                    Archived (<?= $counts['archived'] ?? 0 ?>)
                </a>
            </div>
        </div>

        <!-- Search Form -->
        <div class="col-lg-4">
            <form action="<?= url('members') ?>" method="GET" class="position-relative">
                <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input 
                    type="text" 
                    name="search" 
                    class="gf-form-control ps-5 py-2 fs-7" 
                    placeholder="Search by name, phone, or ID..." 
                    value="<?= e($searchQuery) ?>"
                >
            </form>
        </div>
    </div>
</div>

<!-- Members Table Card -->
<div class="gf-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
            <thead class="table-light text-muted">
                <tr>
                    <th class="border-0 ps-4">Member</th>
                    <th class="border-0">Phone / Email</th>
                    <th class="border-0">Current Plan</th>
                    <th class="border-0">Expiry Date</th>
                    <th class="border-0">Due Balance</th>
                    <th class="border-0">Status</th>
                    <th class="border-0 text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <h6 class="fw-bold mb-1">No members found</h6>
                            <p class="small mb-3">Try adjusting your filters or search term, or add a new gym member.</p>
                            <a href="<?= url('members/create') ?>" class="gf-btn gf-btn-primary btn-sm">
                                <i class="bi bi-plus-lg"></i> Add First Member
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($members as $m): ?>
                        <tr>
                            <!-- Member Name & ID -->
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <?php if (!empty($m['photo_path'])): ?>
                                        <img src="<?= url($m['photo_path']) ?>" alt="<?= e($m['first_name']) ?>" 
                                             class="rounded-circle border" style="width: 38px; height: 38px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="gf-user-avatar" style="width: 38px; height: 38px; font-size: 14px;">
                                            <?= strtoupper(substr($m['first_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <a href="<?= url("members/{$m['id']}") ?>" class="fw-bold text-dark text-decoration-none d-block hover-primary">
                                            <?= e($m['first_name'] . ' ' . ($m['last_name'] ?? '')) ?>
                                        </a>
                                        <span class="small text-muted font-monospace" style="font-size: 11px;">
                                            <?= e($m['member_number']) ?>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact -->
                            <td>
                                <div><i class="bi bi-telephone text-muted me-1"></i> <?= e($m['phone']) ?></div>
                                <?php if (!empty($m['email'])): ?>
                                    <div class="small text-muted"><i class="bi bi-envelope text-muted me-1"></i> <?= e($m['email']) ?></div>
                                <?php endif; ?>
                            </td>

                            <!-- Current Plan -->
                            <td>
                                <?php if (!empty($m['plan_name_snapshot'])): ?>
                                    <span class="badge bg-light text-dark border">
                                        <?= e($m['plan_name_snapshot']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">No active plan</span>
                                <?php endif; ?>
                            </td>

                            <!-- Expiry & Days Left -->
                            <td>
                                <?php if (!empty($m['expiry_date'])): ?>
                                    <div><?= date('d M Y', strtotime($m['expiry_date'])) ?></div>
                                    <?php if ($m['days_left'] !== null): ?>
                                        <?php if ($m['days_left'] < 0): ?>
                                            <span class="badge bg-danger-subtle text-danger" style="font-size: 10px;">
                                                Expired <?= abs((int) $m['days_left']) ?>d ago
                                            </span>
                                        <?php elseif ($m['days_left'] <= 7): ?>
                                            <span class="badge bg-warning-subtle text-warning" style="font-size: 10px;">
                                                <?= (int) $m['days_left'] ?> days left
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small" style="font-size: 11px;">
                                                <?= (int) $m['days_left'] ?> days left
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- Due Balance -->
                            <td>
                                <?php if ((float) ($m['total_due'] ?? 0) > 0): ?>
                                    <span class="fw-bold text-danger">
                                        <?= format_currency($m['total_due'], $currency) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-success small fw-semibold">
                                        <i class="bi bi-check2-circle"></i> Clear
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Status Badge -->
                            <td>
                                <?= render_component('status_badge', [
                                    'status' => $m['computed_status']
                                ]) ?>
                            </td>

                            <!-- Action Dropdown -->
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border-0 rounded-circle" type="button" data-bs-toggle="dropdown" style="width: 32px; height: 32px;">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border">
                                        <li>
                                            <a class="dropdown-item py-2" href="<?= url("members/{$m['id']}") ?>">
                                                <i class="bi bi-person-lines-fill me-2 text-primary"></i> View Profile
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="<?= url("members/{$m['id']}/edit") ?>">
                                                <i class="bi bi-pencil-square me-2 text-warning"></i> Edit Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="<?= url("payments?member_id={$m['id']}") ?>">
                                                <i class="bi bi-cash-stack me-2 text-success"></i> Payment History
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <?php if ((int) $m['is_archived'] === 1): ?>
                                            <li>
                                                <form action="<?= url("members/{$m['id']}/restore") ?>" method="POST" onsubmit="return confirm('Restore this member?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="dropdown-item py-2 text-success">
                                                        <i class="bi bi-arrow-counterclockwise me-2"></i> Restore Member
                                                    </button>
                                                </form>
                                            </li>
                                        <?php else: ?>
                                            <li>
                                                <form action="<?= url("members/{$m['id']}/archive") ?>" method="POST" onsubmit="return confirm('Archive this member? Financial history will be preserved.');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="dropdown-item py-2 text-danger">
                                                        <i class="bi bi-archive me-2"></i> Archive Member
                                                    </button>
                                                </form>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
        <div class="d-flex align-items-center justify-content-between p-3 border-top">
            <span class="small text-muted">
                Showing page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?>
            </span>
            <ul class="pagination pagination-sm m-0">
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url("members?page={$i}&status={$currentStatus}&search=" . urlencode($searchQuery)) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
