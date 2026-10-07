<div class="auth-card shadow-lg position-relative">
    <!-- Theme Toggle at top-right -->
    <div class="position-absolute top-0 end-0 p-3">
        <button type="button" class="btn btn-sm btn-outline-secondary theme-toggle-btn border-0 p-1" onclick="toggleTheme()" title="Toggle Light/Dark Theme">
            <i class="bi bi-sun-fill text-warning fs-5"></i>
        </button>
    </div>

    <!-- Brand Header (Red Theme) -->
    <div class="text-center mb-4">
        <div class="auth-brand-badge mb-3">
            <i class="bi bi-lightning-charge-fill fs-2"></i>
        </div>
        <h3 class="fw-bold mb-1 tracking-tight">FitPulse <span class="text-gradient-red">Gym</span></h3>
        <p class="text-secondary small mb-0 fw-medium">Management & Automation Platform</p>
    </div>

    <!-- Login Form -->
    <form action="<?= BASE_URL ?>/login" method="POST" id="loginForm">
        <?= CSRF::getField() ?>

        <div class="mb-3">
            <label for="loginInput" class="form-label fw-semibold fs-7 mb-1">Username, Email, Phone or Member ID</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-person text-secondary"></i>
                </span>
                <input type="text" class="form-control" id="loginInput" name="login" placeholder="e.g. admin, owner@gym.com or GYM-1001" required autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label for="passwordInput" class="form-label fw-semibold fs-7 mb-1">Password</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-lock text-secondary"></i>
                </span>
                <input type="password" class="form-control" id="passwordInput" name="password" placeholder="••••••••" required>
            </div>
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-fp-primary btn-lg fs-6 py-2">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Platform
            </button>
        </div>
    </form>

    <!-- Demo Role Fast-Fill Pills for Local Testing -->
    <div class="pt-3 border-top border-secondary border-opacity-25 mt-3">
        <div class="text-secondary text-uppercase fw-bold fs-8 text-center mb-2 tracking-wider">Demo Quick-Fill Accounts</div>
        <div class="d-flex flex-wrap gap-1 justify-content-center">
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 fs-8 fw-semibold" onclick="fillCreds('admin@gym.com', 'password123')">
                <i class="bi bi-cpu text-danger me-1"></i>Superadmin
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 fs-8 fw-semibold" onclick="fillCreds('owner@gym.com', 'password123')">
                <i class="bi bi-person-workspace text-primary me-1"></i>Owner
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 fs-8 fw-semibold" onclick="fillCreds('manager@gym.com', 'password123')">
                <i class="bi bi-briefcase text-info me-1"></i>Manager
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 fs-8 fw-semibold" onclick="fillCreds('staff@gym.com', 'password123')">
                <i class="bi bi-person-badge text-warning me-1"></i>Staff
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 fs-8 fw-semibold" onclick="fillCreds('trainer@gym.com', 'password123')">
                <i class="bi bi-activity text-danger me-1"></i>Trainer
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 fs-8 fw-semibold" onclick="fillCreds('member@gym.com', 'password123')">
                <i class="bi bi-person-check text-success me-1"></i>Member
            </button>
        </div>
    </div>
</div>

<script>
function fillCreds(username, password) {
    document.getElementById('loginInput').value = username;
    document.getElementById('passwordInput').value = password;
}
</script>
