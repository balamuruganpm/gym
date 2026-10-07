<?php
$user = Auth::user();
$role = $user ? $user['role'] : 'guest';
$branchName = $user && isset($user['branch_name']) ? $user['branch_name'] : 'FitPulse Gym';
$userName = $user ? ($user['name'] ?? $user['full_name'] ?? 'Admin') : 'Admin';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') : 'Admin Portal - FitPulse Gym' ?></title>
    <?= CSRF::getMeta() ?>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    
    <!-- Inline Theme Init -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('fp_theme') || 'dark';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>
</head>
<body class="fp-app-body">
    <!-- Sidebar Navigation (DreamsGYM Grouped Style) -->
    <aside class="fp-sidebar d-flex flex-column">
        <!-- Brand Header -->
        <div class="p-3 border-bottom border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
            <a href="<?= BASE_URL ?>/admin/dashboard" class="d-flex align-items-center text-decoration-none">
                <div class="fp-brand-icon bg-danger bg-opacity-15 text-danger p-2 rounded-3 me-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-lightning-charge-fill fs-5"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold fp-sidebar-brand-title tracking-tight text-gradient-red">FitPulse</h5>
                    <small class="text-secondary fs-8 fw-semibold"><?= htmlspecialchars($branchName, ENT_QUOTES, 'UTF-8') ?></small>
                </div>
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary theme-toggle-btn border-0 p-1 rounded-circle" onclick="toggleTheme()" title="Toggle Light/Dark Theme">
                <i class="bi bi-sun-fill text-warning"></i>
            </button>
        </div>

        <!-- Grouped Navigation Items -->
        <div class="py-3 flex-grow-1 overflow-auto fp-sidebar-nav">
            <!-- SECTION 1: OVERVIEW -->
            <div class="px-3 mb-1 text-uppercase text-secondary fw-bold fs-8 tracking-wider">Overview</div>
            
            <a href="<?= BASE_URL ?>/admin/dashboard" class="fp-nav-link <?= (isset($activeMenu) && $activeMenu === 'dashboard') ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/checkin" class="fp-nav-link <?= (isset($activeMenu) && ($activeMenu === 'quick_desk' || $activeMenu === 'checkin')) ? 'active' : '' ?>">
                <i class="bi bi-person-check-fill text-danger"></i>
                <span>Fast Desk Check-In</span>
                <span class="badge bg-danger text-white ms-auto fs-8 fw-bold">Live</span>
            </a>

            <!-- SECTION 2: MEMBERS & WORKOUT -->
            <div class="px-3 mt-3 mb-1 text-uppercase text-secondary fw-bold fs-8 tracking-wider">Members & Fitness</div>

            <?php if (FeatureManager::isVisibleToUser('member_management', $role)): ?>
                <a href="<?= BASE_URL ?>/admin/members" class="fp-nav-link <?= (isset($activeMenu) && $activeMenu === 'members') ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Member Directory</span>
                </a>
                <a href="<?= BASE_URL ?>/admin/members/create" class="fp-nav-link <?= (isset($activeMenu) && $activeMenu === 'members_create') ? 'active' : '' ?>">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Add New Member</span>
                </a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/admin/attendance" class="fp-nav-link <?= (isset($activeMenu) && $activeMenu === 'attendance') ? 'active' : '' ?>">
                <i class="bi bi-calendar-check-fill"></i>
                <span>Attendance Log</span>
            </a>

            <?php if (FeatureManager::isVisibleToUser('progress_photos', $role)): ?>
                <a href="<?= BASE_URL ?>/admin/progress" class="fp-nav-link <?= (isset($activeMenu) && $activeMenu === 'progress') ? 'active' : '' ?>">
                    <i class="bi bi-camera-fill"></i>
                    <span>Transformations</span>
                </a>
            <?php endif; ?>

            <!-- SECTION 3: SALES & BILLING -->
            <div class="px-3 mt-3 mb-1 text-uppercase text-secondary fw-bold fs-8 tracking-wider">Sales & Plans</div>

            <?php if (FeatureManager::isVisibleToUser('payments_tracking', $role)): ?>
                <a href="<?= BASE_URL ?>/admin/payments" class="fp-nav-link <?= (isset($activeMenu) && $activeMenu === 'payments') ? 'active' : '' ?>">
                    <i class="bi bi-credit-card-2-front-fill"></i>
                    <span>Payments & Invoices</span>
                </a>
            <?php endif; ?>

            <?php if (FeatureManager::isVisibleToUser('membership_plans', $role)): ?>
                <a href="<?= BASE_URL ?>/admin/plans" class="fp-nav-link <?= (isset($activeMenu) && $activeMenu === 'plans') ? 'active' : '' ?>">
                    <i class="bi bi-tags-fill"></i>
                    <span>Plans & Packages</span>
                </a>
            <?php endif; ?>

            <?php if (RBAC::can('reports.view')): ?>
                <a href="<?= BASE_URL ?>/admin/reports" class="fp-nav-link <?= (isset($activeMenu) && $activeMenu === 'reports') ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart-line-fill"></i>
                    <span>Financial Analytics</span>
                </a>
            <?php endif; ?>

            <!-- SECTION 4: FACILITY & CONTROLS -->
            <div class="px-3 mt-3 mb-1 text-uppercase text-secondary fw-bold fs-8 tracking-wider">Facility & Controls</div>

            <a href="<?= BASE_URL ?>/admin/attendance/qr-poster" class="fp-nav-link <?= (isset($activeMenu) && $activeMenu === 'qr_poster') ? 'active' : '' ?>">
                <i class="bi bi-qr-code-scan text-success"></i>
                <span>Gym Wall QR Poster</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/staff" class="fp-nav-link <?= (isset($activeMenu) && $activeMenu === 'staff') ? 'active' : '' ?>">
                <i class="bi bi-person-badge-fill"></i>
                <span>Staff & Trainers</span>
            </a>

            <?php if ($role === 'superadmin' || $role === 'owner'): ?>
                <?php if (FeatureManager::isVisibleToUser('multi_branch', $role)): ?>
                    <a href="<?= BASE_URL ?>/admin/branches" class="fp-nav-link <?= (isset($activeMenu) && $activeMenu === 'branches') ? 'active' : '' ?>">
                        <i class="bi bi-building"></i>
                        <span>Branch Manager</span>
                    </a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/admin/automation" class="fp-nav-link <?= (isset($activeMenu) && $activeMenu === 'automation') ? 'active' : '' ?>">
                    <i class="bi bi-robot"></i>
                    <span>Automation Engine</span>
                </a>

                <?php if ($role === 'superadmin'): ?>
                    <a href="<?= BASE_URL ?>/admin/coder-control" class="fp-nav-link <?= (isset($activeMenu) && $activeMenu === 'coder_control') ? 'active' : '' ?>">
                        <i class="bi bi-sliders2 text-danger"></i>
                        <span>Coder Control Center</span>
                    </a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/admin/audit-logs" class="fp-nav-link <?= (isset($activeMenu) && $activeMenu === 'audit') ? 'active' : '' ?>">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Audit Trail</span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Sidebar Profile Footer -->
        <div class="p-3 border-top border-secondary border-opacity-25 bg-surface-subtle">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center overflow-hidden">
                    <div class="bg-danger bg-opacity-15 text-danger rounded-circle p-2 me-2 text-center d-flex align-items-center justify-content-center fw-bold fs-7" style="width: 36px; height: 36px;">
                        <?= strtoupper(substr($userName, 0, 1)) ?>
                    </div>
                    <div class="overflow-hidden">
                        <div class="fw-bold fs-7 text-truncate"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></div>
                        <span class="badge bg-secondary bg-opacity-25 text-secondary text-uppercase fs-8"><?= htmlspecialchars($role ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/logout" class="btn btn-outline-danger btn-sm p-1 px-2 rounded-2" title="Sign Out">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content Body -->
    <main class="fp-main-content">
        <!-- Top App Header -->
        <header class="fp-topbar d-flex align-items-center justify-content-between p-3 mb-4 fp-card">
            <!-- Left: Search & Mobile Toggle -->
            <div class="d-flex align-items-center gap-3 flex-grow-1 max-w-md">
                <div class="d-lg-none d-flex align-items-center">
                    <i class="bi bi-lightning-charge-fill text-danger fs-4 me-1"></i>
                    <span class="fw-bold">FitPulse</span>
                </div>
                <div class="input-group input-group-sm fp-search-bar d-none d-md-flex">
                    <span class="input-group-text bg-transparent border-end-0 text-secondary">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control bg-transparent border-start-0 ps-0" id="globalSearchInput" placeholder="Search members, plans, payments..." autocomplete="off">
                </div>
            </div>

            <!-- Right: Action Buttons, Branch Badge, Theme Switcher & Profile Dropdown -->
            <div class="d-flex align-items-center gap-2">
                <!-- Quick Actions Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-fp-primary dropdown-toggle d-flex align-items-center gap-1 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-plus-lg"></i>
                        <span class="d-none d-sm-inline">Quick Action</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-secondary border-opacity-25">
                        <li><h6 class="dropdown-header text-uppercase fs-8 text-secondary">Shortcuts</h6></li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/members/create"><i class="bi bi-person-plus text-danger me-2"></i>New Member</a></li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/checkin"><i class="bi bi-person-check text-success me-2"></i>Fast Desk Check-In</a></li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/payments/create"><i class="bi bi-credit-card text-info me-2"></i>Record Payment</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/attendance/qr-poster"><i class="bi bi-qr-code-scan text-primary me-2"></i>Universal QR Poster</a></li>
                    </ul>
                </div>

                <!-- Live Desk Shortcut -->
                <a href="<?= BASE_URL ?>/admin/checkin" class="btn btn-sm btn-outline-danger d-none d-md-flex align-items-center gap-1" title="Fast Front-Desk Check-In">
                    <i class="bi bi-qr-code"></i>
                    <span>Check-In</span>
                </a>

                <!-- Theme Toggle Button -->
                <button type="button" class="btn btn-sm btn-outline-secondary theme-toggle-btn rounded-3" onclick="toggleTheme()" title="Toggle Light/Dark Theme">
                    <i class="bi bi-sun-fill text-warning"></i>
                </button>

                <!-- Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2 border-0" type="button" data-bs-toggle="dropdown">
                        <div class="bg-danger bg-opacity-20 text-danger rounded-circle d-flex align-items-center justify-content-center fw-bold fs-8" style="width: 28px; height: 28px;">
                            <?= strtoupper(substr($userName, 0, 1)) ?>
                        </div>
                        <span class="d-none d-lg-inline fs-7 fw-semibold"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-secondary border-opacity-25">
                        <li class="px-3 py-2 border-bottom border-secondary border-opacity-25">
                            <div class="fw-bold fs-7"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></div>
                            <small class="text-secondary"><?= htmlspecialchars($branchName, ENT_QUOTES, 'UTF-8') ?></small>
                        </li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/profile"><i class="bi bi-person me-2"></i>My Profile</a></li>
                        <?php if ($role === 'superadmin'): ?>
                            <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/coder-control"><i class="bi bi-sliders2 me-2 text-danger"></i>Coder Control Center</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>/logout"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Flash Notifications -->
        <?php View::partial('flash'); ?>

        <!-- Page Yield Content -->
        <?= $content ?>
    </main>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="fp-mobile-bottom-nav d-lg-none d-flex justify-content-around py-2 border-top border-secondary border-opacity-25 bg-surface">
        <a href="<?= BASE_URL ?>/admin/dashboard" class="text-center text-decoration-none <?= (isset($activeMenu) && $activeMenu === 'dashboard') ? 'text-danger' : 'text-secondary' ?>">
            <i class="bi bi-grid-1x2-fill fs-5 d-block"></i>
            <span class="fs-8">Home</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/checkin" class="text-center text-decoration-none <?= (isset($activeMenu) && $activeMenu === 'checkin') ? 'text-danger' : 'text-secondary' ?>">
            <i class="bi bi-qr-code-scan fs-5 d-block"></i>
            <span class="fs-8">Check-In</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/members" class="text-center text-decoration-none <?= (isset($activeMenu) && $activeMenu === 'members') ? 'text-danger' : 'text-secondary' ?>">
            <i class="bi bi-people-fill fs-5 d-block"></i>
            <span class="fs-8">Members</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/payments" class="text-center text-decoration-none <?= (isset($activeMenu) && $activeMenu === 'payments') ? 'text-danger' : 'text-secondary' ?>">
            <i class="bi bi-credit-card-2-front-fill fs-5 d-block"></i>
            <span class="fs-8">Sales</span>
        </a>
    </nav>

    <!-- Bootstrap 5 Bundle & Client Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
