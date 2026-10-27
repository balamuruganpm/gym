<div class="mb-4">
    <h2 class="fw-bold mb-1">Gym Settings & Preferences</h2>
    <p class="text-muted small mb-0">Configure your gym identity, contact details, currency and operational defaults.</p>
</div>

<!-- Flash Alerts -->
<?= render_component('alert') ?>

<div class="row g-4">
    <!-- Left Column: Settings Form -->
    <div class="col-lg-8">
        <div class="gf-card p-4">
            <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-building text-primary"></i> Gym Profile
            </h5>

            <form action="<?= url('settings') ?>" method="POST" novalidate>
                <?= csrf_field() ?>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Gym Name <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            name="name" 
                            class="gf-form-control fw-bold" 
                            value="<?= e(old('name', $gym['name'])) ?>" 
                            required
                        >
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            name="phone" 
                            class="gf-form-control" 
                            value="<?= e(old('phone', $gym['phone'] ?? '')) ?>" 
                            required
                        >
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Official Email</label>
                        <input 
                            type="email" 
                            name="email" 
                            class="gf-form-control" 
                            value="<?= e(old('email', $gym['email'] ?? '')) ?>"
                        >
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Timezone</label>
                        <select name="timezone" class="gf-form-select">
                            <option value="Asia/Kolkata" <?= ($gym['timezone'] ?? '') === 'Asia/Kolkata' ? 'selected' : '' ?>>Asia/Kolkata (IST +5:30)</option>
                            <option value="UTC" <?= ($gym['timezone'] ?? '') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                            <option value="America/New_York" <?= ($gym['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' ?>>America/New_York (EST)</option>
                            <option value="Europe/London" <?= ($gym['timezone'] ?? '') === 'Europe/London' ? 'selected' : '' ?>>Europe/London (GMT)</option>
                            <option value="Asia/Dubai" <?= ($gym['timezone'] ?? '') === 'Asia/Dubai' ? 'selected' : '' ?>>Asia/Dubai (GST)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Gym Address</label>
                    <textarea 
                        name="address" 
                        class="gf-form-control" 
                        rows="2" 
                        placeholder="Plot #, Street, City, Pincode"
                    ><?= e(old('address', $gym['address'] ?? '')) ?></textarea>
                </div>

                <hr class="my-4">

                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-currency-exchange text-success"></i> Financial & Currency Configuration
                </h5>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Currency Symbol</label>
                        <input 
                            type="text" 
                            name="currency_symbol" 
                            class="gf-form-control font-monospace fw-bold fs-5" 
                            value="<?= e(old('currency_symbol', $gym['currency_symbol'] ?? '₹')) ?>" 
                            maxlength="10"
                        >
                        <span class="text-muted" style="font-size: 11px;">Symbol displayed across receipts and invoices (e.g. ₹, $, €).</span>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Currency ISO Code</label>
                        <input 
                            type="text" 
                            name="currency_code" 
                            class="gf-form-control font-monospace text-uppercase fw-semibold" 
                            value="<?= e(old('currency_code', $gym['currency_code'] ?? 'INR')) ?>" 
                            maxlength="10"
                        >
                        <span class="text-muted" style="font-size: 11px;">Standard 3-letter currency code (e.g. INR, USD, EUR).</span>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn gf-btn-primary fw-semibold px-4 py-2 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: System Meta -->
    <div class="col-lg-4">
        <div class="gf-card p-4 mb-4">
            <h6 class="fw-bold mb-3 text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                Tenant Identification
            </h6>

            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted small">Gym Tenant ID</span>
                <span class="font-monospace fw-bold small">#<?= e($gym['id']) ?></span>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted small">Unique Slug</span>
                <span class="font-monospace small"><?= e($gym['slug']) ?></span>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted small">Account Status</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle text-capitalize">
                    <?= e($gym['status']) ?>
                </span>
            </div>
            <div class="d-flex justify-content-between py-2">
                <span class="text-muted small">Registered Since</span>
                <span class="small"><?= date('d M Y', strtotime($gym['created_at'])) ?></span>
            </div>
        </div>

        <div class="gf-card p-4">
            <h6 class="fw-bold mb-3 text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                WhatsApp Click-to-Chat
            </h6>
            <div class="small text-muted mb-2">
                GymFlow integrates with the official WhatsApp Web and Mobile Click-to-Chat protocol (wa.me) requiring zero API subscription costs.
            </div>
            <div class="badge bg-light text-success border p-2 w-100 text-start">
                <i class="bi bi-check-circle me-1"></i> Pre-configured & ready to send 1-click reminders.
            </div>
        </div>
    </div>
</div>
