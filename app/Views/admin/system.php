<div class="container-fluid p-0">
    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge" style="background: rgba(139, 92, 246, 0.15); color: #7c3aed; border: 1px solid rgba(139, 92, 246, 0.25); border-radius: 9999px; font-weight: 700; padding: 4px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">
                    <i class="bi bi-shield-lock me-1"></i> SupremeLeader Console
                </span>
                <span class="text-muted" style="font-size: 13px;">Single-Gym Dedicated Platform</span>
            </div>
            <h2 class="h3 fw-bold mb-0 text-dark">System Health & Operations</h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('admin/logs') ?>" class="gf-btn gf-btn-outline">
                <i class="bi bi-file-earmark-text text-primary"></i> Application Logs
            </a>
            <a href="<?= url('admin/provision-owner') ?>" class="gf-btn gf-btn-primary">
                <i class="bi bi-person-plus-fill"></i> Provision Gym Owner
            </a>
        </div>
    </div>

    <!-- Health Metrics Grid -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="gf-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">PHP Environment</span>
                    <div class="gf-metric-icon blue" style="width: 36px; height: 36px; font-size: 16px;">
                        <i class="bi bi-cpu"></i>
                    </div>
                </div>
                <div class="h4 fw-bold mb-1 text-dark">v<?= e($phpVersion) ?></div>
                <div class="small text-muted">Memory Limit: <?= e($memoryLimit) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="gf-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Database Engine</span>
                    <div class="gf-metric-icon purple" style="width: 36px; height: 36px; font-size: 16px;">
                        <i class="bi bi-database"></i>
                    </div>
                </div>
                <div class="h4 fw-bold mb-1 text-dark">MySQL Active</div>
                <div class="small text-muted text-truncate" title="<?= e($dbVersion) ?>"><?= e(explode('-', $dbVersion)[0] ?? $dbVersion) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="gf-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Operational Gyms</span>
                    <div class="gf-metric-icon green" style="width: 36px; height: 36px; font-size: 16px;">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
                <div class="h4 fw-bold mb-1 text-dark"><?= e((string) $gymsCount) ?> In Database</div>
                <div class="small text-muted"><?= e((string) $membersCount) ?> total enrolled members</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="gf-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Log Storage</span>
                    <div class="gf-metric-icon amber" style="width: 36px; height: 36px; font-size: 16px;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
                <div class="h4 fw-bold mb-1 text-dark"><?= e($totalLogSize) ?></div>
                <div class="small text-muted">Disk Free: <?= e($diskFree ?? 'N/A') ?> &bull; <?= count($logFiles) ?> daily logs</div>
            </div>
        </div>
    </div>

    <!-- Operational Gyms List & Support Mode -->
    <div class="gf-card mb-4">
        <div class="p-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="background: rgba(0, 0, 0, 0.02);">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Configured Gym Installations</h5>
                <p class="small text-muted mb-0">Access any operational gym in audited support mode with explicit reasoning.</p>
            </div>
            <span class="badge bg-light text-dark border">
                <i class="bi bi-info-circle text-primary me-1"></i> Strict Audited Access
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th style="padding-left: 20px;">Gym ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end" style="padding-right: 20px;">Support Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($gyms)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No gyms found in database.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($gyms as $g): ?>
                            <tr>
                                <td style="padding-left: 20px;" class="fw-semibold">#<?= e((string)$g['id']) ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= e($g['name']) ?></div>
                                    <div class="small text-muted"><?= e($g['email'] ?? 'No contact email') ?></div>
                                </td>
                                <td><code><?= e($g['slug']) ?></code></td>
                                <td>
                                    <?php if (($g['status'] ?? 'active') === 'active'): ?>
                                        <span class="gf-badge active"><i class="bi bi-check-circle-fill"></i> Active</span>
                                    <?php else: ?>
                                        <span class="gf-badge expired"><?= e(ucfirst($g['status'] ?? 'inactive')) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted"><?= e(date('M d, Y', strtotime($g['created_at'] ?? 'now'))) ?></td>
                                <td class="text-end" style="padding-right: 20px;">
                                    <button type="button" class="gf-btn gf-btn-outline btn-sm py-1 px-3" data-bs-toggle="modal" data-bs-target="#supportModal<?= e((string)$g['id']) ?>">
                                        <i class="bi bi-box-arrow-in-right text-primary me-1"></i> Enter Support
                                    </button>

                                    <!-- Support Mode Modal -->
                                    <div class="modal fade text-start" id="supportModal<?= e((string)$g['id']) ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content" style="border: var(--gf-border); border-radius: var(--gf-radius-lg); box-shadow: var(--gf-shadow-hover);">
                                                <form method="POST" action="<?= url('admin/support/' . $g['id']) ?>">
                                                    <?= csrf_field() ?>
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold">
                                                            <i class="bi bi-shield-shaded text-primary me-1"></i> Support Access Confirmation
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="alert alert-info border d-flex gap-2 mb-3" style="border-radius: var(--gf-radius-md);">
                                                            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                                                            <div class="small">
                                                                You are accessing <strong><?= e($g['name']) ?></strong> (ID: <?= e((string)$g['id']) ?>) as SupremeLeader. Your SupremeLeader identity will be preserved, and this session will be logged to the security audit trail.
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small text-muted">Mandatory Support Reason <span class="text-danger">*</span></label>
                                                            <textarea name="reason" class="form-control" rows="3" placeholder="e.g., Investigating payment reversal issue reported by owner..." required minlength="6" style="border: var(--gf-border); border-radius: var(--gf-radius-md);"></textarea>
                                                            <div class="form-text small">This reason is permanently recorded in the security audit log.</div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="gf-btn gf-btn-outline" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="gf-btn gf-btn-primary">
                                                            <i class="bi bi-box-arrow-in-right me-1"></i> Confirm & Enter Support
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Security Audit Trail Preview -->
    <div class="gf-card">
        <div class="p-3 d-flex align-items-center justify-content-between" style="background: rgba(0, 0, 0, 0.02);">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Security Events & Audit Stream</h5>
                <p class="small text-muted mb-0">Live security logging stream for today (<?= date('Y-m-d') ?>)</p>
            </div>
            <a href="<?= url('admin/logs?file=security-' . date('Y-m-d') . '.log') ?>" class="gf-btn gf-btn-outline btn-sm">
                <i class="bi bi-shield-check me-1"></i> Full Security Logs
            </a>
        </div>
        <div class="p-3">
            <?php if (empty($recentSecLogs)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-shield-check text-success display-6 d-block mb-2"></i>
                    No security alerts or violations recorded today. System is operating normally.
                </div>
            <?php else: ?>
                <div style="background: #0f172a; color: #e2e8f0; border: var(--gf-border); border-radius: var(--gf-radius-md); padding: 16px; font-family: monospace; font-size: 13px; max-height: 360px; overflow-y: auto;">
                    <?php foreach ($recentSecLogs as $log): ?>
                        <div class="mb-2 pb-2">
                            <span class="text-muted"><?= e($log['timestamp'] ?? '') ?></span>
                            <span class="badge bg-secondary ms-1 me-1"><?= e($log['level'] ?? 'INFO') ?></span>
                            <span class="text-info"><?= e($log['requestId'] ?? '') ?></span>
                            <span class="text-warning"><?= e($log['context'] ?? '') ?></span>:
                            <span class="text-light"><?= e($log['message'] ?? '') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
