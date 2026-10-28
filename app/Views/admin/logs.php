<div class="container-fluid p-0">
    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <a href="<?= url('admin/system') ?>" class="text-muted small fw-semibold d-inline-flex align-items-center gap-1 mb-2 text-decoration-none">
                <i class="bi bi-arrow-left"></i> Back to System Console
            </a>
            <h2 class="h3 fw-bold mb-0 text-dark">System & Security Logs</h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge" style="background: rgba(2, 132, 199, 0.12); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); border-radius: 9999px; font-weight: 700; padding: 6px 12px; font-size: 12px;">
                <i class="bi bi-shield-check me-1"></i> Sanitized Logs Stream
            </span>
        </div>
    </div>

    <!-- Filter Control Card -->
    <div class="gf-card p-3 mb-4">
        <form method="GET" action="<?= url('admin/logs') ?>" class="row g-2 align-items-center">
            <!-- File Selector -->
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Log Channel & Date</label>
                <select name="file" class="form-select form-select-sm" onchange="this.form.submit()" style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                    <?php if (empty($logFiles)): ?>
                        <option value="">No log files found</option>
                    <?php else: ?>
                        <?php foreach ($logFiles as $lf): ?>
                            <option value="<?= e($lf['filename']) ?>" <?= $selectedFile === $lf['filename'] ? 'selected' : '' ?>>
                                <?= e($lf['filename']) ?> (<?= round($lf['size'] / 1024, 1) ?> KB)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Severity Level Filter -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Severity Filter</label>
                <select name="level" class="form-select form-select-sm" onchange="this.form.submit()" style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                    <option value="" <?= empty($currentLevel) ? 'selected' : '' ?>>All Levels</option>
                    <option value="INFO" <?= $currentLevel === 'INFO' ? 'selected' : '' ?>>INFO</option>
                    <option value="WARNING" <?= $currentLevel === 'WARNING' ? 'selected' : '' ?>>WARNING</option>
                    <option value="ERROR" <?= $currentLevel === 'ERROR' ? 'selected' : '' ?>>ERROR</option>
                    <option value="SECURITY" <?= $currentLevel === 'SECURITY' ? 'selected' : '' ?>>SECURITY</option>
                </select>
            </div>

            <!-- Search Filter (Request ID, IP, message) -->
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Search Keywords or Request ID</label>
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Search req_..., keyword..." value="<?= e($searchQuery ?? '') ?>" style="border: var(--gf-border); border-radius: var(--gf-radius-md) 0 0 var(--gf-radius-md);">
                    <button class="gf-btn gf-btn-primary py-1 px-3" type="submit" style="border-radius: 0 var(--gf-radius-md) var(--gf-radius-md) 0;">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>

            <!-- Reset -->
            <div class="col-md-1 d-flex align-items-end">
                <a href="<?= url('admin/logs' . ($selectedFile ? '?file=' . urlencode($selectedFile) : '')) ?>" class="gf-btn gf-btn-outline btn-sm w-100 text-center justify-content-center py-1" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Active File Summary -->
    <div class="d-flex align-items-center justify-content-between mb-2 px-1">
        <div class="small text-muted">
            Viewing: <strong class="text-dark"><?= e($selectedFile ?: 'None') ?></strong> 
            &bull; Showing latest <strong class="text-dark"><?= count($lines) ?></strong> matching entries (max 200)
        </div>
        <?php if (!empty($searchQuery) || !empty($currentLevel)): ?>
            <span class="badge bg-light text-dark border">
                Filtered: <?= e($currentLevel ?: 'All') ?> <?= !empty($searchQuery) ? '| "' . e($searchQuery) . '"' : '' ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- Log Console View -->
    <div class="gf-card p-3" style="background: #0f172a; border-radius: var(--gf-radius-lg);">
        <?php if (empty($lines)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-journal-x display-6 d-block mb-2 text-secondary"></i>
                <span>No log entries match the selected channel and filters.</span>
            </div>
        <?php else: ?>
            <div style="font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; font-size: 13px; line-height: 1.6; color: #cbd5e1; max-height: 650px; overflow-y: auto;">
                <?php foreach ($lines as $entry): ?>
                    <?php
                        $level = strtoupper($entry['level'] ?? 'INFO');
                        $badgeClass = match ($level) {
                            'ERROR' => 'bg-danger text-white',
                            'WARNING' => 'bg-warning text-dark',
                            'SECURITY' => 'bg-primary text-white',
                            default => 'bg-secondary text-white',
                        };
                    ?>
                    <div class="py-2" style="word-break: break-all;">
                        <span class="text-secondary"><?= e($entry['timestamp'] ?? '') ?></span>
                        <span class="badge <?= $badgeClass ?> ms-1 me-1" style="font-size: 10px; padding: 2px 6px; border-radius: 4px;"><?= e($level) ?></span>
                        <span style="color: #38bdf8;"><?= e($entry['requestId'] ?? '') ?></span>
                        <span style="color: #facc15;"><?= e($entry['context'] ?? '') ?></span>:
                        <span class="text-light"><?= e($entry['message'] ?? '') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
