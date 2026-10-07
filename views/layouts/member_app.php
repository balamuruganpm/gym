<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($pageTitle ?? 'Member Portal - FitPulse') ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom Fitness CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    
    <!-- Inline Theme Init -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('fp_theme') || 'dark';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>
    <style>
        body {
            padding-bottom: 75px; /* Space for bottom nav */
        }
        .member-header {
            background: var(--fp-card-bg);
            border-bottom: 1px solid var(--fp-card-border);
            padding: 12px 16px;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: var(--fp-transition);
        }
        .member-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--fp-card-bg);
            border-top: 1px solid var(--fp-card-border);
            display: flex;
            justify-content: space-around;
            padding: 8px 0;
            z-index: 1000;
            transition: var(--fp-transition);
        }
        .nav-tab-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: var(--fp-text-muted);
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .nav-tab-item i {
            font-size: 20px;
            margin-bottom: 2px;
        }
        .nav-tab-item.active {
            color: #10b981;
            font-weight: 700;
        }
        .nav-tab-item.active i {
            transform: scale(1.1);
        }
        .streak-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>
<body>

    <!-- Sticky Member Top Bar -->
    <header class="member-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-lightning-charge-fill text-success fs-4"></i>
            <span class="fw-bold fs-6 tracking-wide">FITPULSE PORTAL</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary theme-toggle-btn border-0 p-1" onclick="toggleTheme()" title="Toggle Theme">
                <i class="bi bi-sun-fill text-warning"></i>
            </button>
            <a href="<?= BASE_URL ?>/member/qr" class="btn btn-sm btn-outline-success">
                <i class="bi bi-qr-code"></i> ID Pass
            </a>
            <a href="<?= BASE_URL ?>/logout" class="btn btn-sm btn-outline-danger" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </header>

    <!-- Main Member Content Container -->
    <main class="container py-3" style="max-width: 650px;">
        <?php if ($success = Session::flash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 bg-success bg-opacity-25 text-success mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error = Session::flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger bg-opacity-25 text-danger mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Content Injection -->
        <?php include $viewPath; ?>
    </main>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="member-bottom-nav">
        <a href="<?= BASE_URL ?>/member/dashboard" class="nav-tab-item <?= ($activeTab ?? '') === 'home' ? 'active' : '' ?>">
            <i class="bi bi-house-door-fill"></i>
            <span>Home</span>
        </a>
        <a href="<?= BASE_URL ?>/member/qr" class="nav-tab-item <?= ($activeTab ?? '') === 'qr' ? 'active' : '' ?>">
            <i class="bi bi-qr-code-scan"></i>
            <span>QR Pass</span>
        </a>
        <a href="<?= BASE_URL ?>/member/attendance" class="nav-tab-item <?= ($activeTab ?? '') === 'attendance' ? 'active' : '' ?>">
            <i class="bi bi-calendar2-check-fill"></i>
            <span>Workouts</span>
        </a>
        <a href="<?= BASE_URL ?>/member/progress" class="nav-tab-item <?= ($activeTab ?? '') === 'progress' ? 'active' : '' ?>">
            <i class="bi bi-activity"></i>
            <span>Progress</span>
        </a>
        <a href="<?= BASE_URL ?>/member/payments" class="nav-tab-item <?= ($activeTab ?? '') === 'payments' ? 'active' : '' ?>">
            <i class="bi bi-receipt"></i>
            <span>Receipts</span>
        </a>
        <a href="<?= BASE_URL ?>/member/profile" class="nav-tab-item <?= ($activeTab ?? '') === 'profile' ? 'active' : '' ?>">
            <i class="bi bi-person-circle"></i>
            <span>Profile</span>
        </a>
    </nav>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
