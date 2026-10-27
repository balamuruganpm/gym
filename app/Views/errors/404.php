<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found — GymFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="text-center p-5 gf-card" style="max-width: 480px;">
        <div class="mb-4">
            <div class="gf-brand-icon mx-auto" style="width: 64px; height: 64px; font-size: 32px;">
                <i class="bi bi-compass"></i>
            </div>
        </div>
        <h1 class="display-5 fw-bold mb-2">404</h1>
        <h4 class="mb-3">Page Not Found</h4>
        <p class="text-muted mb-4"><?= e($message ?? 'The page you are looking for does not exist or has been moved.') ?></p>
        <a href="<?= url('dashboard') ?>" class="gf-btn gf-btn-primary">
            <i class="bi bi-arrow-left"></i> Return to Dashboard
        </a>
    </div>
</body>
</html>
