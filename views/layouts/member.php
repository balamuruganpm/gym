<?php
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') : 'My Portal - FitPulse' ?></title>
    <?= CSRF::getMeta() ?>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-dark text-light pb-5 mb-4">
    <!-- Member Header -->
    <header class="p-3 border-bottom border-secondary border-opacity-25 bg-dark sticky-top">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="bi bi-lightning-charge-fill text-success fs-3 me-2"></i>
                <div>
                    <h6 class="mb-0 fw-bold text-white">FitPulse</h6>
                    <small class="text-secondary fs-8"><?= htmlspecialchars($user ? $user['full_name'] : 'Member', ENT_QUOTES, 'UTF-8') ?></small>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/logout" class="btn btn-sm btn-outline-danger" title="Sign Out">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container py-3">
        <?php View::partial('flash'); ?>
        <?= $content ?>
    </main>

    <!-- Bottom Navigation Bar for Mobile -->
    <nav class="fp-bottom-nav">
        <a href="<?= BASE_URL ?>/member/dashboard" class="<?= (isset($activeNav) && $activeNav === 'home') ? 'active' : '' ?>">
            <i class="bi bi-house-door-fill"></i>
            <span>Home</span>
        </a>
        <a href="<?= BASE_URL ?>/member/qr" class="<?= (isset($activeNav) && $activeNav === 'qr') ? 'active' : '' ?>">
            <i class="bi bi-qr-code-scan"></i>
            <span>My QR</span>
        </a>
        <a href="<?= BASE_URL ?>/member/progress" class="<?= (isset($activeNav) && $activeNav === 'progress') ? 'active' : '' ?>">
            <i class="bi bi-images"></i>
            <span>Progress</span>
        </a>
        <a href="<?= BASE_URL ?>/member/payments" class="<?= (isset($activeNav) && $activeNav === 'payments') ? 'active' : '' ?>">
            <i class="bi bi-receipt"></i>
            <span>Receipts</span>
        </a>
        <a href="<?= BASE_URL ?>/member/profile" class="<?= (isset($activeNav) && $activeNav === 'profile') ? 'active' : '' ?>">
            <i class="bi bi-person-fill"></i>
            <span>Profile</span>
        </a>
    </nav>

    <!-- Bootstrap 5 Bundle & Client Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
