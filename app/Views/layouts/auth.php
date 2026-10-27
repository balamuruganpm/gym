<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'GymFlow — Futuristic Gym Management') ?></title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- GymFlow Custom Design Tokens -->
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="gf-auth-wrapper">
        <!-- Left Hero Banner -->
        <div class="gf-auth-banner">
            <div>
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="gf-brand-icon">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div>
                        <h2 class="gf-brand-name text-white m-0">GymFlow</h2>
                        <span class="gf-brand-subtitle text-white-50">Gym Management System</span>
                    </div>
                </div>

                <div class="my-5 py-4">
                    <h1 class="display-6 fw-bold text-white mb-3" style="line-height: 1.2;">
                        Manage Your Gym<br><span style="color: #38bdf8;">Smarter & Easier</span>
                    </h1>
                    <p class="text-white-50 fs-6 mb-5" style="max-width: 420px;">
                        Track members, memberships, payments and renewals — all in one place with real-time accuracy.
                    </p>

                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-check-circle-fill text-info fs-5"></i>
                            <span class="text-white fw-semibold">Simple & Easy to Use</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-check-circle-fill text-info fs-5"></i>
                            <span class="text-white fw-semibold">Tailored for All Gym Sizes</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-check-circle-fill text-info fs-5"></i>
                            <span class="text-white fw-semibold">Free to Get Started (No Gateway Needed)</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-check-circle-fill text-info fs-5"></i>
                            <span class="text-white fw-semibold">Access from Any Device & Mobile Responsive</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-top border-secondary border-opacity-25 d-flex align-items-center justify-content-between text-white-50 fs-7">
                <span>© <?= date('Y') ?> GymFlow Platform.</span>
                <span>Version 1.0</span>
            </div>
        </div>

        <!-- Right Form Container -->
        <div class="gf-auth-form-side">
            <div class="gf-auth-card">
                <?= $content ?? '' ?>
            </div>
        </div>
    </div>

    <!-- Core Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
