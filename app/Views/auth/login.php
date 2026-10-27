<div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center mb-3 d-lg-none">
        <div class="gf-brand-icon">
            <i class="bi bi-activity"></i>
        </div>
    </div>
    <h2 class="fw-bold mb-1">Welcome Back</h2>
    <p class="text-muted small">Login to your gym account to continue</p>
</div>

<!-- Render Flash Alerts -->
<?= render_component('alert') ?>

<form action="<?= url('login') ?>" method="POST" novalidate>
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold text-dark">Email Address</label>
        <div class="position-relative">
            <i class="bi bi-envelope position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
            <input 
                type="email" 
                name="email" 
                id="email" 
                class="gf-form-control ps-5" 
                placeholder="you@email.com" 
                value="<?= e(old('email')) ?>" 
                required 
                autofocus
            >
        </div>
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label small fw-semibold text-dark mb-0">Password</label>
            <a href="javascript:void(0)" onclick="alert('Please contact your gym administrator or owner to reset your password.')" class="small text-primary text-decoration-none">Forgot password?</a>
        </div>
        <div class="position-relative">
            <i class="bi bi-lock position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
            <input 
                type="password" 
                name="password" 
                id="password" 
                class="gf-form-control ps-5 pe-5" 
                placeholder="Enter your password" 
                required
            >
            <button 
                type="button" 
                class="btn border-0 position-absolute end-0 top-50 translate-middle-y me-2 text-muted" 
                data-toggle="password" 
                data-target="password"
            >
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>

    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
        <label class="form-check-label small text-muted" for="rememberMe">
            Remember me on this browser
        </label>
    </div>

    <button type="submit" class="gf-btn gf-btn-primary w-100 py-2 fs-6 mb-3">
        Login <i class="bi bi-arrow-right ms-1"></i>
    </button>

    <div class="position-relative my-4 text-center">
        <hr class="text-secondary opacity-25">
        <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 small text-muted fw-bold">OR</span>
    </div>

    <a href="<?= url('register') ?>" class="gf-btn gf-btn-outline w-100 py-2 fs-6">
        Create New Gym Account
    </a>

    <div class="text-center mt-4">
        <span class="small text-muted">Free to use <span class="mx-1">•</span> No credit card required</span>
    </div>
</form>
