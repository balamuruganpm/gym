<div class="mb-4">
    <a href="<?= url("members/{$member['id']}") ?>" class="text-muted small text-decoration-none">
        <i class="bi bi-arrow-left"></i> Back to Member Profile
    </a>
    <div class="d-flex align-items-center justify-content-between mt-2">
        <div>
            <h2 class="fw-bold mb-1">Edit Member #<?= e($member['member_number']) ?></h2>
            <p class="text-muted small mb-0">Update contact details, personal information and emergency contacts.</p>
        </div>
        <span class="badge bg-light text-dark font-monospace border px-3 py-2 rounded-pill">
            ID: <?= e($member['member_number']) ?>
        </span>
    </div>
</div>

<!-- Flash Error Alert -->
<?= render_component('alert') ?>

<form action="<?= url("members/{$member['id']}") ?>" method="POST" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>

    <div class="row g-4">
        <!-- Left Column: Member Personal Info -->
        <div class="col-lg-8">
            <div class="gf-card p-4 mb-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-person-badge text-primary"></i> Personal Details
                </h5>

                <div class="row g-3">
                    <!-- Photo Preview & Upload -->
                    <div class="col-12 mb-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle overflow-hidden d-flex align-items-center justify-content-center bg-light border" style="width: 70px; height: 70px; flex-shrink: 0;">
                                <?php if (!empty($member['photo_path'])): ?>
                                    <img src="<?= asset($member['photo_path']) ?>" alt="Avatar" class="w-100 h-100 object-fit-cover">
                                <?php else: ?>
                                    <span class="fw-bold text-primary fs-4">
                                        <?= strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'] ?? '', 0, 1)) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label small fw-semibold mb-1">Change Profile Photo</label>
                                <input type="file" name="photo" class="gf-form-control" accept="image/jpeg,image/png,image/webp">
                                <span class="text-muted" style="font-size: 11px;">Max 2MB. JPG, PNG or WebP.</span>
                            </div>
                        </div>
                    </div>

                    <!-- First Name -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            name="first_name" 
                            class="gf-form-control" 
                            value="<?= e(old('first_name', $member['first_name'])) ?>" 
                            required
                        >
                    </div>

                    <!-- Last Name -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Last Name</label>
                        <input 
                            type="text" 
                            name="last_name" 
                            class="gf-form-control" 
                            value="<?= e(old('last_name', $member['last_name'] ?? '')) ?>"
                        >
                    </div>

                    <!-- Phone Number -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <i class="bi bi-telephone position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input 
                                type="text" 
                                name="phone" 
                                class="gf-form-control ps-5" 
                                value="<?= e(old('phone', $member['phone'])) ?>" 
                                required
                            >
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Email Address</label>
                        <div class="position-relative">
                            <i class="bi bi-envelope position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input 
                                type="email" 
                                name="email" 
                                class="gf-form-control ps-5" 
                                value="<?= e(old('email', $member['email'] ?? '')) ?>"
                            >
                        </div>
                    </div>

                    <!-- Gender -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Gender</label>
                        <select name="gender" class="gf-form-select">
                            <option value="">Select Gender</option>
                            <option value="male" <?= ($member['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($member['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($member['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <!-- Date of Birth -->
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Date of Birth</label>
                        <input 
                            type="date" 
                            name="date_of_birth" 
                            class="gf-form-control" 
                            value="<?= e(old('date_of_birth', $member['date_of_birth'] ?? '')) ?>"
                        >
                    </div>

                    <!-- Address -->
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Home / Work Address</label>
                        <textarea 
                            name="address" 
                            class="gf-form-control" 
                            rows="2" 
                            placeholder="Residential address..."
                        ><?= e(old('address', $member['address'] ?? '')) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="gf-card p-4 mb-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-plus text-danger"></i> Emergency Contact
                </h5>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Contact Person Name</label>
                        <input 
                            type="text" 
                            name="emergency_contact_name" 
                            class="gf-form-control" 
                            value="<?= e(old('emergency_contact_name', $member['emergency_contact_name'] ?? '')) ?>"
                        >
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Contact Person Phone</label>
                        <input 
                            type="text" 
                            name="emergency_contact_phone" 
                            class="gf-form-control" 
                            value="<?= e(old('emergency_contact_phone', $member['emergency_contact_phone'] ?? '')) ?>"
                        >
                    </div>
                </div>
            </div>

            <!-- Internal Staff Notes -->
            <div class="gf-card p-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-journal-text text-secondary"></i> Internal Notes & Medical History
                </h5>
                <textarea 
                    name="notes" 
                    class="gf-form-control" 
                    rows="3" 
                    placeholder="Medical alerts, injuries, trainer preferences..."
                ><?= e(old('notes', $member['notes'] ?? '')) ?></textarea>
            </div>
        </div>

        <!-- Right Column: Meta & Actions -->
        <div class="col-lg-4">
            <div class="gf-card p-4 mb-4">
                <h6 class="fw-bold mb-3 text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                    Account Meta
                </h6>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted small">Registered Since</span>
                    <span class="small fw-semibold"><?= date('d M Y', strtotime($member['created_at'])) ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted small">Status</span>
                    <span class="badge <?= $member['is_archived'] ? 'bg-secondary' : 'bg-success' ?>">
                        <?= $member['is_archived'] ? 'Archived' : 'Active Account' ?>
                    </span>
                </div>
            </div>

            <div class="gf-card p-4">
                <button type="submit" class="btn gf-btn-primary w-100 py-2 fw-semibold mb-2 shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> Save Changes
                </button>
                <a href="<?= url("members/{$member['id']}") ?>" class="btn btn-light w-100 py-2 border text-muted">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>
