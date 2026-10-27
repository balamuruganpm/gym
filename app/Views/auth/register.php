<div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center mb-3 d-lg-none">
        <div class="gf-brand-icon">
            <i class="bi bi-activity"></i>
        </div>
    </div>
    <h2 class="fw-bold mb-1">Get Started with GymFlow</h2>
    <p class="text-muted small">Register your gym and create your owner account</p>
</div>

<!-- Render Flash Alerts -->
<?= render_component('alert') ?>

<form action="<?= url('register') ?>" method="POST" novalidate>
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="gym_name" class="form-label small fw-semibold text-dark">Gym / Fitness Center Name</label>
        <div class="position-relative">
            <i class="bi bi-building position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
            <input 
                type="text" 
                name="gym_name" 
                id="gym_name" 
                class="gf-form-control ps-5" 
                placeholder="e.g. Gold Fitness Club" 
                value="<?= e(old('gym_name')) ?>" 
                required 
                autofocus
            >
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-sm-6">
            <label for="owner_name" class="form-label small fw-semibold text-dark">Owner Full Name</label>
            <div class="position-relative">
                <i class="bi bi-person position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                <input 
                    type="text" 
                    name="owner_name" 
                    id="owner_name" 
                    class="gf-form-control ps-5" 
                    placeholder="Balamurugan" 
                    value="<?= e(old('owner_name')) ?>" 
                    required
                >
            </div>
        </div>
        <div class="col-sm-6">
            <label for="phone" class="form-label small fw-semibold text-dark">Phone Number</label>
            <div class="position-relative">
                <i class="bi bi-telephone position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                <input 
                    type="text" 
                    name="phone" 
                    id="phone" 
                    class="gf-form-control ps-5" 
                    placeholder="9876543210" 
                    value="<?= e(old('phone')) ?>" 
                    required
                >
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold text-dark">Login Email Address</label>
        <div class="position-relative">
            <i class="bi bi-envelope position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
            <input 
                type="email" 
                name="email" 
                id="email" 
                class="gf-form-control ps-5" 
                placeholder="owner@yourgym.com" 
                value="<?= e(old('email')) ?>" 
                required
            >
        </div>
    </div>

    <div class="row g-2 mb-4">
        <div class="col-sm-6">
            <label for="password" class="form-label small fw-semibold text-dark">Password (min 6)</label>
            <div class="position-relative">
                <i class="bi bi-lock position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                <input 
                    type="password" 
                    name="password" 
                    id="password" 
                    class="gf-form-control ps-5" 
                    placeholder="Create password" 
                    required
                >
            </div>
        </div>
        <div class="col-sm-6">
            <label for="password_confirmation" class="form-label small fw-semibold text-dark">Confirm Password</label>
            <div class="position-relative">
                <i class="bi bi-shield-check position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    id="password_confirmation" 
                    class="gf-form-control ps-5" 
                    placeholder="Repeat password" 
                    required
                >
            </div>
        </div>
    </div>

    <button type="submit" class="gf-btn gf-btn-primary w-100 py-2 fs-6 mb-3">
        Create Gym & Start Free <i class="bi bi-arrow-right ms-1"></i>
    </button>

    <div class="text-center mt-3">
        <span class="small text-muted">Already have a gym account?</span>
        <a href="<?= url('login') ?>" class="small text-primary fw-semibold ms-1">Login here</a>
    </div>
</form>
