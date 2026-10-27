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
    <!-- GymFlow Futuristic CSS Tokens -->
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="gf-layout-wrapper">
        <!-- Reusable Sidebar Component -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <!-- Main Workspace Area -->
        <div class="gf-main-wrapper">
            <!-- Reusable Topbar Component -->
            <?php include __DIR__ . '/../partials/topbar.php'; ?>

            <!-- Page Body -->
            <main class="gf-content">
                <!-- Flash Alerts Toast Component -->
                <?= render_component('alert') ?>

                <!-- Dynamic Page Content -->
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <!-- Core Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
