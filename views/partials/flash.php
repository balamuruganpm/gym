<?php if (Session::hasFlash()): ?>
    <?php $allFlash = Session::getFlash(); ?>
    <div class="flash-messages-container mb-4">
        <?php if (!empty($allFlash['success'])): ?>
            <?php foreach ($allFlash['success'] as $msg): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($allFlash['error'])): ?>
            <?php foreach ($allFlash['error'] as $msg): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($allFlash['warning'])): ?>
            <?php foreach ($allFlash['warning'] as $msg): ?>
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                    <div><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($allFlash['info'])): ?>
            <?php foreach ($allFlash['info'] as $msg): ?>
                <div class="alert alert-info alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                    <div><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
