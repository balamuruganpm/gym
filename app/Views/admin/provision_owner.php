<div class="container-fluid p-0">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <a href="<?= url('admin/system') ?>" class="text-muted small fw-semibold d-inline-flex align-items-center gap-1 mb-2 text-decoration-none">
                        <i class="bi bi-arrow-left"></i> Back to System Console
                    </a>
                    <h2 class="h3 fw-bold mb-0 text-dark">Provision Gym Owner</h2>
                </div>
                <span class="badge" style="background: rgba(139, 92, 246, 0.15); color: #7c3aed; border: 1px solid rgba(139, 92, 246, 0.25); border-radius: 9999px; font-weight: 700; padding: 6px 12px; font-size: 12px;">
                    <i class="bi bi-shield-check me-1"></i> Admin Operation
                </span>
            </div>

            <!-- Card -->
            <div class="gf-card p-4 p-md-5">
                <div class="alert alert-light border mb-4 d-flex gap-3" style="border-radius: var(--gf-radius-md);">
                    <i class="bi bi-info-circle-fill text-primary fs-5 mt-1"></i>
                    <div class="small text-muted">
                        <strong class="text-dark d-block mb-1">GymFlow Single-Gym Operational Model</strong>
                        Gym owners possess full administrative control over their gym, including member management, membership plans, financial reports, trainer assignments, and offline payment approvals. SupremeLeader credentials must never be shared with gym owners.
                    </div>
                </div>

                <form method="POST" action="<?= url('admin/provision-owner') ?>">
                    <?= csrf_field() ?>

                    <!-- Target Gym Selection -->
                    <div class="mb-3">
                        <label for="gym_id" class="form-label fw-semibold text-dark">Target Operational Gym <span class="text-danger">*</span></label>
                        <select name="gym_id" id="gym_id" class="form-select form-select-lg" required style="border: var(--gf-border); border-radius: var(--gf-radius-md); font-size: 15px;">
                            <option value="">-- Select Gym Installation --</option>
                            <?php foreach ($gyms as $g): ?>
                                <option value="<?= e((string)$g['id']) ?>" <?= old('gym_id') == $g['id'] ? 'selected' : '' ?>>
                                    <?= e($g['name']) ?> (ID: <?= e((string)$g['id']) ?>, Slug: <?= e($g['slug']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small">Select the operational gym record to bind this owner account to.</div>
                    </div>

                    <!-- Owner Full Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold text-dark">Owner Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control form-control-lg" placeholder="e.g., Alex Johnson" value="<?= e(old('name')) ?>" required minlength="2" maxlength="100" style="border: var(--gf-border); border-radius: var(--gf-radius-md); font-size: 15px;">
                    </div>

                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold text-dark">Owner Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control form-control-lg" placeholder="owner@gymdomain.com" value="<?= e(old('email')) ?>" required style="border: var(--gf-border); border-radius: var(--gf-radius-md); font-size: 15px;">
                        <div class="form-text small">This email will be used for authentication.</div>
                    </div>

                    <!-- Contact Phone (Optional) -->
                    <div class="mb-3">
                        <label for="phone" class="form-label fw-semibold text-dark">Contact Phone <span class="text-muted fw-normal">(Optional)</span></label>
                        <input type="text" name="phone" id="phone" class="form-control form-control-lg" placeholder="+91 98765 43210" value="<?= e(old('phone')) ?>" style="border: var(--gf-border); border-radius: var(--gf-radius-md); font-size: 15px;">
                    </div>

                    <!-- Secure Password -->
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold text-dark">Initial Secure Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="Minimum 8 characters" required minlength="8" style="border: var(--gf-border); border-radius: var(--gf-radius-md); font-size: 15px;">
                        <div class="form-text small">The password will be safely hashed with bcrypt/Argon2. It is never logged in plaintext.</div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center justify-content-end gap-3 pt-2">
                        <a href="<?= url('admin/system') ?>" class="gf-btn gf-btn-outline">
                            Cancel
                        </a>
                        <button type="submit" class="gf-btn gf-btn-primary px-4">
                            <i class="bi bi-person-check-fill me-1"></i> Provision & Activate Owner
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
