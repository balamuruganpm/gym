<?php
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$user = auth_user();
$gym = current_gym();
$role = user_role();
$isSupreme = is_supreme_leader();
$isSupportMode = (bool) \App\Services\Session::get('support_mode', false);

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
            <span class="gf-brand-subtitle">
                <?php if ($isSupreme): ?>
                    System Administration
                <?php elseif ($role === 'trainer'): ?>
                    Trainer Portal
                <?php elseif ($role === 'member'): ?>
                    Member Self-Service
                <?php else: ?>
                    Gym Management
                <?php endif; ?>
            </span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <ul class="gf-sidebar-menu">
        <?php if ($isSupreme && !$isSupportMode): ?>
            <!-- ================= SupremeLeader Platform Admin Navigation ================= -->
            <li class="mt-2 mb-1 px-3">
                <span class="small text-uppercase fw-bold text-muted" style="font-size: 10px; letter-spacing: 0.05em;">System Operations</span>
            </li>
            <li>
                <a href="<?= url('admin/system') ?>" class="gf-nav-link <?= is_active_route('admin/system', $currentUri) ?>">
                    <i class="bi bi-cpu-fill"></i>
                    <span>System Console</span>
                </a>
            </li>
            <li>
                <a href="<?= url('admin/logs') ?>" class="gf-nav-link <?= is_active_route('admin/logs', $currentUri) ?>">
                    <i class="bi bi-journal-code"></i>
                    <span>Application Logs</span>
                </a>
            </li>
            <li>
                <a href="<?= url('admin/provision-owner') ?>" class="gf-nav-link <?= is_active_route('admin/provision-owner', $currentUri) ?>">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Provision Owner</span>
                </a>
            </li>

        <?php elseif ($role === 'trainer'): ?>
            <!-- ================= Trainer Portal Navigation ================= -->
            <li class="mt-2 mb-1 px-3">
                <span class="small text-uppercase fw-bold text-muted" style="font-size: 10px; letter-spacing: 0.05em;">Training Center</span>
            </li>
            <li>
                <a href="<?= url('trainer/dashboard') ?>" class="gf-nav-link <?= is_active_route('trainer/dashboard', $currentUri) ?>">
                    <i class="bi bi-grid-fill"></i>
                    <span>Trainer Hub</span>
                </a>
            </li>
            <li>
                <a href="<?= url('trainer/members') ?>" class="gf-nav-link <?= is_active_route('trainer/members', $currentUri) ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Assigned Members</span>
                </a>
            </li>
            <li>
                <a href="<?= url('trainer/sessions') ?>" class="gf-nav-link <?= is_active_route('trainer/sessions', $currentUri) || is_active_route('trainer/schedule', $currentUri) ? 'active' : '' ?>">
                    <i class="bi bi-calendar-check-fill"></i>
                    <span>Session Calendar</span>
                </a>
            </li>
            <li>
                <a href="<?= url('trainer/availability') ?>" class="gf-nav-link <?= is_active_route('trainer/availability', $currentUri) ?>">
                    <i class="bi bi-clock-history"></i>
                    <span>My Availability</span>
                </a>
            </li>
            <li>
                <a href="<?= url('trainer/progress') ?>" class="gf-nav-link <?= is_active_route('trainer/progress', $currentUri) ?>">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Progress Tracker</span>
                </a>
            </li>

        <?php elseif ($role === 'member'): ?>
            <!-- ================= Member Self-Service Portal Navigation ================= -->
            <li class="mt-2 mb-1 px-3">
                <span class="small text-uppercase fw-bold text-muted" style="font-size: 10px; letter-spacing: 0.05em;">My Membership</span>
            </li>
            <li>
                <a href="<?= url('portal/dashboard') ?>" class="gf-nav-link <?= is_active_route('portal/dashboard', $currentUri) ?>">
                    <i class="bi bi-grid-fill"></i>
                    <span>Member Home</span>
                </a>
            </li>
            <li>
                <a href="<?= url('portal/membership') ?>" class="gf-nav-link <?= is_active_route('portal/membership', $currentUri) ?>">
                    <i class="bi bi-card-heading"></i>
                    <span>Pass & Benefits</span>
                </a>
            </li>
            <li>
                <a href="<?= url('portal/payments') ?>" class="gf-nav-link <?= is_active_route('portal/payments', $currentUri) ?>">
                    <i class="bi bi-receipt"></i>
                    <span>Receipts & Dues</span>
                </a>
            </li>
            <li>
                <a href="<?= url('portal/schedule') ?>" class="gf-nav-link <?= is_active_route('portal/schedule', $currentUri) ?>">
                    <i class="bi bi-calendar-event"></i>
                    <span>Training Sessions</span>
                </a>
            </li>
            <li>
                <a href="<?= url('portal/payment-request') ?>" class="gf-nav-link <?= is_active_route('portal/payment-request', $currentUri) ?>">
                    <i class="bi bi-wallet2"></i>
                    <span>Submit Payment</span>
                </a>
            </li>

        <?php else: ?>
            <!-- ================= Owner / Staff Gym Navigation ================= -->
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

            <?php if ($role === 'owner' || $isSupportMode): ?>
                <li class="mt-3 mb-1 px-3">
                    <span class="small text-uppercase fw-bold text-muted" style="font-size: 10px; letter-spacing: 0.05em;">Management</span>
                </li>
                <li>
                    <a href="<?= url('trainers') ?>" class="gf-nav-link <?= is_active_route('trainers', $currentUri) ?>">
                        <i class="bi bi-award-fill"></i>
                        <span>Trainer Hub</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('payment-requests') ?>" class="gf-nav-link <?= is_active_route('payment-requests', $currentUri) ?>">
                        <i class="bi bi-credit-card-2-front-fill"></i>
                        <span>Payment Requests</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('staff') ?>" class="gf-nav-link <?= is_active_route('staff', $currentUri) ?>">
                        <i class="bi bi-person-badge"></i>
                        <span>Staff Access</span>
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
        <?php endif; ?>
    </ul>

    <!-- Sidebar Footer with User Profile -->
    <div class="gf-sidebar-footer">
        <?php if ($isSupportMode): ?>
            <div class="p-2 mb-2 bg-warning-subtle text-warning-emphasis rounded-3 border border-warning small text-center">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Support Mode
                <form action="<?= url('admin/exit-support') ?>" method="POST" class="mt-1">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-xs btn-outline-danger w-100 py-1" style="font-size:11px;">Exit Support</button>
                </form>
            </div>
        <?php endif; ?>

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
                        <?php if ($isSupreme): ?>
                            SupremeLeader
                        <?php else: ?>
                            <?= ucfirst($role ?? 'Staff') ?>
                        <?php endif; ?>
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
