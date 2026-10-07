<div class="fp-card text-center py-5 my-4 mx-auto" style="max-width: 600px;">
    <div class="mb-3 text-warning">
        <i class="bi bi-compass display-1"></i>
    </div>
    <h2 class="fw-bold text-white mb-2">404 Page Not Found</h2>
    <p class="text-secondary mb-4"><?= isset($message) ? htmlspecialchars($message, ENT_QUOTES, 'UTF-8') : 'The requested page or endpoint could not be found.' ?></p>
    <div>
        <a href="<?= BASE_URL ?>/" class="btn btn-fp-primary me-2">
            <i class="bi bi-house-door me-1"></i> Return Home
        </a>
        <a href="javascript:history.back()" class="btn btn-fp-secondary">
            <i class="bi bi-arrow-left me-1"></i> Go Back
        </a>
    </div>
</div>
