<!-- Member Universal Gym QR Check-in Celebration Result -->
<div class="fp-checkin-result-container max-w-md mx-auto py-4 text-center">
    <div class="fp-card p-5 shadow-lg border-2 <?= $success ? 'border-success' : 'border-danger' ?>">
        
        <?php if ($success): ?>
            <!-- Success Icon & Celebration -->
            <div class="mb-4">
                <div class="bg-success text-white rounded-circle p-3 d-inline-flex align-items-center justify-content-center shadow-lg animate-bounce mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-check-lg fs-1 fw-bold"></i>
                </div>
                <h2 class="h3 fw-extrabold text-white-glow mb-1">Workout Checked In!</h2>
                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-3 py-1 fs-7 fw-bold">
                    <i class="bi bi-patch-check-fill me-1"></i> Attendance Verified
                </span>
            </div>

            <!-- Member & Branch Summary Card -->
            <div class="bg-surface p-3 rounded-3 text-start mb-4 border border-secondary border-opacity-25">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-secondary border-opacity-15">
                    <span class="text-secondary fs-8">Facility Branch</span>
                    <strong class="text-light fs-7"><?= htmlspecialchars($branch['name'] ?? 'FitPulse Gym') ?></strong>
                </div>
                <?php if (isset($member)): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-secondary border-opacity-15">
                        <span class="text-secondary fs-8">Member Code</span>
                        <code class="text-warning fs-7"><?= htmlspecialchars($member['member_code'] ?? '') ?></code>
                    </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-secondary border-opacity-15">
                    <span class="text-secondary fs-8">Time Logged</span>
                    <strong class="text-light fs-7"><?= date('h:i:s A, d M Y') ?></strong>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-secondary fs-8">Check-In Method</span>
                    <span class="badge bg-warning text-dark fs-8 fw-bold">Universal Gym QR</span>
                </div>
            </div>

            <p class="text-secondary fs-7 mb-4">Crush your workout today! Drink plenty of water and stay consistent. 💪🔥</p>

            <div class="d-grid gap-2">
                <a href="<?= BASE_URL ?>/member/dashboard" class="btn btn-warning text-dark fw-bold py-2">
                    <i class="bi bi-speedometer2 me-1"></i> Go to Member Portal
                </a>
            </div>

        <?php else: ?>
            <!-- Error State -->
            <div class="mb-4">
                <div class="bg-danger text-white rounded-circle p-3 d-inline-flex align-items-center justify-content-center shadow-lg mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                </div>
                <h2 class="h3 fw-extrabold text-danger mb-1">Check-In Failed</h2>
                <p class="text-secondary fs-7"><?= htmlspecialchars($message ?? 'Unable to process attendance check-in.') ?></p>
            </div>

            <div class="d-grid gap-2">
                <a href="<?= BASE_URL ?>/member/scan-gym-qr" class="btn btn-outline-warning fw-bold py-2">
                    <i class="bi bi-camera me-1"></i> Try Scanning Again
                </a>
                <a href="<?= BASE_URL ?>/member/dashboard" class="btn btn-outline-secondary py-2">
                    Back to Member Portal
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>
