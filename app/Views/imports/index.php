<div class="mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-bold mb-1">Batch Member Import</h2>
            <p class="text-muted small mb-0">Import hundreds of gym members from existing spreadsheets or legacy systems in one click.</p>
        </div>
        <a href="<?= url('imports/template') ?>" class="btn btn-outline-primary fw-semibold shadow-sm">
            <i class="bi bi-download me-1"></i> Download CSV Template
        </a>
    </div>
</div>

<!-- Flash Alerts -->
<?= render_component('alert') ?>

<div class="row g-4">
    <!-- Left Column: Upload Form -->
    <div class="col-lg-7">
        <div class="gf-card p-4">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-cloud-arrow-up text-primary"></i> Upload Member Spreadsheet
            </h5>

            <form action="<?= url('imports/upload') ?>" method="POST" enctype="multipart/form-data" novalidate>
                <?= csrf_field() ?>

                <div class="p-4 border border-2 border-dashed rounded-4 text-center bg-light mb-4" style="border-color: rgba(2, 132, 199, 0.3) !important;">
                    <i class="bi bi-file-earmark-excel fs-1 text-primary d-block mb-2"></i>
                    <h6 class="fw-bold mb-1">Choose CSV file or drag and drop here</h6>
                    <p class="text-muted small mb-3">Accepts .csv files up to 10MB.</p>
                    <input type="file" name="csv_file" class="form-control d-inline-block w-auto mx-auto" accept=".csv" required>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2">
                    <span class="text-muted small">
                        <i class="bi bi-shield-check text-success me-1"></i> All rows validated in safe database transaction.
                    </span>
                    <button type="submit" class="btn gf-btn-primary fw-semibold px-4 py-2 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Begin Batch Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Instructions & Column Specs -->
    <div class="col-lg-5">
        <div class="gf-card p-4">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-info-circle text-info"></i> Column Guidelines
            </h5>

            <p class="small text-muted mb-3">
                Ensure your CSV matches these column headers in order:
            </p>

            <ul class="list-unstyled small mb-0">
                <li class="py-2 border-bottom d-flex justify-content-between">
                    <span><strong>First Name</strong></span>
                    <span class="badge bg-danger-subtle text-danger">Required</span>
                </li>
                <li class="py-2 border-bottom d-flex justify-content-between">
                    <span><strong>Phone</strong></span>
                    <span class="badge bg-danger-subtle text-danger">Required</span>
                </li>
                <li class="py-2 border-bottom d-flex justify-content-between">
                    <span>Last Name</span>
                    <span class="badge bg-light text-muted border">Optional</span>
                </li>
                <li class="py-2 border-bottom d-flex justify-content-between">
                    <span>Email</span>
                    <span class="badge bg-light text-muted border">Optional</span>
                </li>
                <li class="py-2 border-bottom d-flex justify-content-between">
                    <span>Gender (male/female/other)</span>
                    <span class="badge bg-light text-muted border">Optional</span>
                </li>
                <li class="py-2 border-bottom d-flex justify-content-between">
                    <span>Date of Birth (YYYY-MM-DD)</span>
                    <span class="badge bg-light text-muted border">Optional</span>
                </li>
                <li class="py-2 border-bottom d-flex justify-content-between">
                    <span>Address</span>
                    <span class="badge bg-light text-muted border">Optional</span>
                </li>
                <li class="py-2 border-bottom d-flex justify-content-between">
                    <span>Emergency Contact Name</span>
                    <span class="badge bg-light text-muted border">Optional</span>
                </li>
                <li class="py-2 d-flex justify-content-between">
                    <span>Emergency Contact Phone</span>
                    <span class="badge bg-light text-muted border">Optional</span>
                </li>
            </ul>

            <div class="mt-4 p-3 bg-light rounded border small text-muted">
                <i class="bi bi-lightbulb text-warning me-1"></i>
                <strong>Pro Tip:</strong> Members will have automated sequential member IDs generated (e.g. GF-2026-XXXX) seamlessly during import.
            </div>
        </div>
    </div>
</div>
