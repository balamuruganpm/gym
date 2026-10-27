<?php
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$user = auth_user();
$gym = current_gym();
$role = user_role();

function is_active_route(string $keyword, string $uri): string {
    return str_contains($uri, $keyword) ? 'active' : '';
}
?>
<aside class="gf-sidebar" id="gfSidebar">
    <!-- Brand Header -->
    <div class="gf-sidebar-brand">
        <div class="gf-brand-icon">
            <i class="bi bi-activity"></i>
        </div>
        <div>
            <h1 class="gf-brand-name">GymFlow</h1>
            <span class="gf-brand-subtitle">Gym Management</span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <ul class="gf-sidebar-menu">
        <li>
            <a href="<?= url('dashboard') ?>" class="gf-nav-link <?= is_active_route('dashboard', $currentUri) ?>">
                <i class="bi bi-grid-fill"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="<?= url('members') ?>" class="gf-nav-link <?= is_active_route('members', $currentUri) ?>">
                <i class="bi bi-people-fill"></i>
                <span>Members</span>
            </a>
        </li>
        <li>
            <a href="<?= url('plans') ?>" class="gf-nav-link <?= is_active_route('plans', $currentUri) ?>">
                <i class="bi bi-card-checklist"></i>
                <span>Membership Plans</span>
            </a>
        </li>
        <li>
            <a href="<?= url('payments') ?>" class="gf-nav-link <?= is_active_route('payments', $currentUri) ?>">
                <i class="bi bi-wallet2"></i>
                <span>Payments</span>
            </a>
        </li>
        <li>
            <a href="<?= url('renewals') ?>" class="gf-nav-link <?= is_active_route('renewals', $currentUri) ?>">
                <i class="bi bi-clock-history"></i>
                <span>Renewals</span>
            </a>
        </li>
        <li>
            <a href="<?= url('reports') ?>" class="gf-nav-link <?= is_active_route('reports', $currentUri) ?>">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>Reports</span>
            </a>
        </li>
        <li>
            <a href="<?= url('imports') ?>" class="gf-nav-link <?= is_active_route('imports', $currentUri) ?>">
                <i class="bi bi-cloud-arrow-up-fill"></i>
                <span>Import / Export</span>
            </a>
        </li>

        <?php if ($role === 'owner'): ?>
            <li class="mt-3 mb-1 px-3">
                <span class="small text-uppercase fw-bold text-muted" style="font-size: 10px; letter-spacing: 0.05em;">Management</span>
            </li>
            <li>
                <a href="<?= url('staff') ?>" class="gf-nav-link <?= is_active_route('staff', $currentUri) ?>">
                    <i class="bi bi-person-badge"></i>
                    <span>Staff</span>
                </a>
            </li>
            <li>
                <a href="<?= url('audit-logs') ?>" class="gf-nav-link <?= is_active_route('audit-logs', $currentUri) ?>">
                    <i class="bi bi-shield-check"></i>
                    <span>Audit Logs</span>
                </a>
            </li>
            <li>
                <a href="<?= url('settings') ?>" class="gf-nav-link <?= is_active_route('settings', $currentUri) ?>">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <!-- Sidebar Footer with User Profile -->
    <div class="gf-sidebar-footer">
        <div class="gf-user-card">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <div class="gf-user-avatar">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="overflow-hidden">
                    <div class="fw-bold text-dark text-truncate small" style="max-width: 130px;">
                        <?= e($user['name'] ?? 'User') ?>
                    </div>
                    <div class="text-muted small text-truncate" style="font-size: 11px;">
                        <?= ucfirst($role ?? 'Staff') ?>
                    </div>
                </div>
            </div>
            
            <form action="<?= url('logout') ?>" method="POST" class="m-0 p-0">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-link text-muted p-1" title="Log out" style="text-decoration: none;">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
