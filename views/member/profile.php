<div class="mb-3">
    <h4 class="fw-bold mb-1"><i class="bi bi-person-circle text-primary me-2"></i>My Profile & Settings</h4>
    <p class="text-secondary small mb-0">Update contact details, emergency info & password</p>
</div>

<div class="fp-card rounded-4 p-4 mb-3">
    <form action="<?= BASE_URL ?>/member/profile/update" method="POST">
        <?= CSRF::field() ?>

        <div class="mb-3 text-center">
            <div class="p-3 bg-secondary bg-opacity-25 rounded-circle d-inline-block mb-2">
                <i class="bi bi-person fs-1 text-primary"></i>
            </div>
            <h5 class="fw-bold mb-0"><?= htmlspecialchars($member['name'] ?? 'Member') ?></h5>
            <div class="badge bg-secondary font-monospace"><?= htmlspecialchars($member['member_code'] ?? '') ?></div>
        </div>

        <div class="mb-3">
            <label class="form-label text-muted small">Full Name *</label>
            <input type="text" name="name" class="form-control border-secondary" value="<?= htmlspecialchars($member['name'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label text-muted small">Phone Number *</label>
            <input type="text" name="phone" class="form-control border-secondary" value="<?= htmlspecialchars($member['phone'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label text-muted small">Email Address</label>
            <input type="email" class="form-control border-secondary" value="<?= htmlspecialchars($member['email'] ?? '') ?>" disabled>
            <div class="text-muted small" style="font-size: 11px;">Email is managed by gym administrator.</div>
        </div>

        <div class="mb-3">
            <label class="form-label text-muted small">Residential Address</label>
            <textarea name="address" class="form-control border-secondary" rows="2"><?= htmlspecialchars($member['address'] ?? '') ?></textarea>
        </div>

        <h6 class="fw-bold text-danger mt-4 mb-3"><i class="bi bi-telephone-plus me-1"></i>Emergency Contact</h6>
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label text-muted small">Contact Name</label>
                <input type="text" name="emergency_contact_name" class="form-control border-secondary" value="<?= htmlspecialchars($member['emergency_contact_name'] ?? '') ?>" placeholder="e.g. Parent / Spouse">
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">Contact Phone</label>
                <input type="text" name="emergency_contact_phone" class="form-control border-secondary" value="<?= htmlspecialchars($member['emergency_contact_phone'] ?? '') ?>" placeholder="+91 9876543210">
            </div>
        </div>

        <h6 class="fw-bold text-warning mt-4 mb-3"><i class="bi bi-shield-lock me-1"></i>Security & Password</h6>
        <div class="mb-3">
            <label class="form-label text-muted small">New Password (leave blank to keep current)</label>
            <input type="password" name="password" class="form-control border-secondary" placeholder="Enter new password">
        </div>

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary py-2">
                <i class="bi bi-check-lg me-1"></i> Save Profile Changes
            </button>
        </div>
    </form>
</div>

<div class="text-center">
    <a href="<?= BASE_URL ?>/logout" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-box-arrow-right me-1"></i> Log Out from Gym Account
    </a>
</div>
