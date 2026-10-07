<!-- Quick 1-Step Member Login & Attendance Check-In on Poster Scan -->
<div class="fp-auth-container d-flex align-items-center justify-content-center min-vh-100 py-4">
    <div class="fp-auth-card fp-card p-4 p-md-5 shadow-lg max-w-md w-100">
        <!-- Gym Brand Header -->
        <div class="text-center mb-4">
            <div class="bg-warning text-dark d-inline-flex align-items-center justify-content-center p-3 rounded-circle mb-2 shadow-warning" style="width: 58px; height: 58px;">
                <i class="bi bi-qr-code-scan fs-3"></i>
            </div>
            <h3 class="fw-bold mb-1 text-white-glow">Gym Check-In Login</h3>
            <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-25 px-2 py-1 fs-8">
                <?= htmlspecialchars($branch['name'] ?? 'FitPulse Gym') ?>
            </span>
            <p class="text-secondary fs-8 mt-2 mb-0">Sign in to instantly record your gym attendance today.</p>
        </div>

        <?php View::partial('flash'); ?>

        <form method="POST" action="<?= BASE_URL ?>/login">
            <?= CSRF::getInput() ?>
            <input type="hidden" name="post_login_redirect" value="<?= BASE_URL ?>/checkin/gym-qr?branch_token=<?= urlencode($branchToken) ?>">

            <div class="mb-3">
                <label for="username" class="form-label fs-8 text-secondary fw-semibold">Username, Email or Phone</label>
                <div class="input-group">
                    <span class="input-group-text bg-surface border-secondary border-opacity-25 text-secondary">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" class="form-control bg-surface border-secondary border-opacity-25 text-light" id="username" name="username" placeholder="e.g. rahul@fitpulse.in or 9876543210" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label fs-8 text-secondary fw-semibold mb-0">Password</label>
                    <a href="<?= BASE_URL ?>/forgot-password" class="fs-8 text-warning text-decoration-none">Forgot?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-surface border-secondary border-opacity-25 text-secondary">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" class="form-control bg-surface border-secondary border-opacity-25 text-light" id="password" name="password" placeholder="Enter password" required>
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-warning text-dark fw-bold py-2 shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> Sign In & Check In
                </button>
            </div>

            <div class="text-center">
                <a href="<?= BASE_URL ?>/login" class="text-secondary fs-8 text-decoration-none">
                    Standard Admin / Staff Login &rarr;
                </a>
            </div>
        </form>
    </div>
</div>
