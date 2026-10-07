<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <a href="<?= BASE_URL ?>/admin/reports" class="text-decoration-none small text-muted mb-1 d-block">
            <i class="bi bi-arrow-left"></i> Back to Analytics Hub
        </a>
        <h2 class="h3 fw-bold mb-1"><i class="bi bi-cpu text-info me-2"></i>System Health & Diagnostics Monitor</h2>
        <p class="text-secondary small mb-0">Real-time server runtime, database integrity, file storage, and memory status</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <button onclick="location.reload();" class="btn btn-outline-info">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh Diagnostics
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Server & Runtime Specs -->
    <div class="col-md-6">
        <div class="fp-card rounded-4 p-4 h-100">
            <h5 class="fw-bold text-primary mb-3"><i class="bi bi-hdd-rack me-2"></i>Server & PHP Runtime</h5>
            
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0">
                    <tbody>
                        <tr class="border-bottom border-secondary border-opacity-25">
                            <td class="text-muted">PHP Version</td>
                            <td class="text-end fw-bold text-success"><i class="bi bi-check-circle me-1"></i><?= $diagnostics['php_version'] ?? PHP_VERSION ?></td>
                        </tr>
                        <tr class="border-bottom border-secondary border-opacity-25">
                            <td class="text-muted">Database Engine</td>
                            <td class="text-end fw-bold text-info"><?= $diagnostics['mysql_version'] ?? 'MySQL' ?> (<?= $diagnostics['pdo_driver'] ?? 'PDO' ?>)</td>
                        </tr>
                        <tr class="border-bottom border-secondary border-opacity-25">
                            <td class="text-muted">Active Database</td>
                            <td class="text-end font-monospace text-warning"><?= $diagnostics['database_name'] ?? 'gym_management' ?></td>
                        </tr>
                        <tr class="border-bottom border-secondary border-opacity-25">
                            <td class="text-muted">Server Software</td>
                            <td class="text-end small"><?= htmlspecialchars($diagnostics['server_software'] ?? '') ?></td>
                        </tr>
                        <tr class="border-bottom border-secondary border-opacity-25">
                            <td class="text-muted">Server Timestamp</td>
                            <td class="text-end small"><?= $diagnostics['server_time'] ?? date('Y-m-d H:i:s') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Session Status</td>
                            <td class="text-end fw-bold text-success"><i class="bi bi-shield-check me-1"></i><?= $diagnostics['session_status'] ?? 'Active' ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Storage & Memory Allocation -->
    <div class="col-md-6">
        <div class="fp-card rounded-4 p-4 h-100">
            <h5 class="fw-bold text-success mb-3"><i class="bi bi-memory me-2"></i>Memory & Storage Allocations</h5>
            
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0">
                    <tbody>
                        <tr class="border-bottom border-secondary border-opacity-25">
                            <td class="text-muted">PHP Memory Usage</td>
                            <td class="text-end fw-bold text-primary"><?= $diagnostics['memory_usage'] ?? '10 MB' ?> / <?= $diagnostics['memory_limit'] ?? '512M' ?></td>
                        </tr>
                        <tr class="border-bottom border-secondary border-opacity-25">
                            <td class="text-muted">Max Execution Time</td>
                            <td class="text-end"><?= $diagnostics['max_execution_time'] ?? '120s' ?></td>
                        </tr>
                        <tr class="border-bottom border-secondary border-opacity-25">
                            <td class="text-muted">Upload Max Filesize</td>
                            <td class="text-end"><?= $diagnostics['upload_max_filesize'] ?? '20M' ?></td>
                        </tr>
                        <tr class="border-bottom border-secondary border-opacity-25">
                            <td class="text-muted">Uploads Directory Writable</td>
                            <td class="text-end">
                                <?php if (!empty($diagnostics['uploads_writable'])): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Writable</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Not Writable</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Disk Free Space</td>
                            <td class="text-end fw-bold"><?= $diagnostics['disk_free_gb'] ?? '0' ?> GB (of <?= $diagnostics['disk_total_gb'] ?? '0' ?> GB)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
