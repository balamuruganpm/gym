<div class="mb-4">
    <a href="<?= url('plans') ?>" class="text-muted small text-decoration-none">
        <i class="bi bi-arrow-left"></i> Back to Plans
    </a>
    <h2 class="fw-bold mt-2 mb-1">Edit Membership Plan</h2>
    <p class="text-muted small">Update pricing or duration details for "<?= e($plan['name']) ?>".</p>
</div>

<!-- Flash Alerts -->
<?= render_component('alert') ?>

<div class="row">
    <div class="col-lg-7">
        <div class="gf-card p-4">
            <form action="<?= url("plans/{$plan['id']}") ?>" method="POST" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Plan Name <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        class="gf-form-control" 
                        value="<?= e(old('name', $plan['name'])) ?>" 
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Description (Optional)</label>
                    <textarea 
                        name="description" 
                        class="gf-form-control" 
                        rows="3"
                    ><?= e(old('description', $plan['description'] ?? '')) ?></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Duration Value <span class="text-danger">*</span></label>
                        <input 
                            type="number" 
                            name="duration_value" 
                            class="gf-form-control" 
                            min="1" 
                            value="<?= e(old('duration_value', (string)$plan['duration_value'])) ?>" 
                            required
                        >
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Duration Type <span class="text-danger">*</span></label>
                        <select name="duration_type" class="gf-form-select" required>
                            <option value="months" <?= old('duration_type', $plan['duration_type']) === 'months' ? 'selected' : '' ?>>Months</option>
                            <option value="days" <?= old('duration_type', $plan['duration_type']) === 'days' ? 'selected' : '' ?>>Days</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Plan Price (₹) <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fw-bold">₹</span>
                        <input 
                            type="number" 
                            name="price" 
                            step="0.01" 
                            min="0" 
                            class="gf-form-control ps-5 fw-bold" 
                            value="<?= e(old('price', (string)$plan['price'])) ?>" 
                            required
                        >
                    </div>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" <?= $plan['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label small fw-semibold" for="isActive">
                        Plan is active and available for member assignments
                    </label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn gf-btn-primary fw-semibold px-4 py-2 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Save Changes
                    </button>
                    <a href="<?= url('plans') ?>" class="btn btn-light border px-4 py-2 text-muted">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
