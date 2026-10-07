<div class="fp-coder-control">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 px-2 py-1 fs-8 fw-bold">Superadmin Only</span>
                <span class="text-secondary fs-8">Dynamic Feature Architecture</span>
            </div>
            <h2 class="h3 fw-bold mb-0 text-white-glow">Coder Control Center</h2>
            <p class="text-secondary small mb-0">Toggle authentication methods, attendance engines, and modules in real time.</p>
        </div>
        <span class="badge bg-danger text-white px-3 py-2 fs-7 fw-bold shadow-sm">
            <i class="bi bi-cpu-fill me-1"></i> Engine Active
        </span>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="fp-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-white-glow">
                        <i class="bi bi-sliders2 text-danger me-2"></i>Dynamic Feature Modules
                    </h5>
                    <span class="text-secondary fs-8">Total: <?= count($features) ?> modules</span>
                </div>
                <p class="text-secondary fs-8 mb-3">Enable or disable system functions dynamically. Changes take effect across all routes and views immediately.</p>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-secondary fs-8 text-uppercase">
                                <th>Module Key</th>
                                <th>Feature Name</th>
                                <th>Category</th>
                                <th>Roles</th>
                                <th class="text-end">Status & Action</th>
                            </tr>
                        </thead>
                        <tbody class="fs-7">
                            <?php foreach ($features as $key => $feat): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></code></td>
                                    <td>
                                        <div class="fw-bold text-light"><?= htmlspecialchars($feat['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-25 text-light fs-8">
                                            <?= htmlspecialchars(ucfirst($feat['category'] ?? 'core'), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-secondary fs-8"><?= implode(', ', $feat['visible_roles'] ?? []) ?></small>
                                    </td>
                                    <td class="text-end">
                                        <form action="<?= BASE_URL ?>/admin/coder-control/toggle" method="POST" class="d-inline">
                                            <?= CSRF::getField() ?>
                                            <input type="hidden" name="feature_key" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn btn-sm <?= $feat['enabled'] ? 'btn-success' : 'btn-outline-danger' ?> fw-bold px-3">
                                                <i class="bi <?= $feat['enabled'] ? 'bi-toggle-on' : 'bi-toggle-off' ?> me-1"></i>
                                                <?= $feat['enabled'] ? 'Enabled' : 'Disabled' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <!-- System Diagnostics -->
            <div class="fp-card p-4 mb-4">
                <h6 class="fw-bold mb-3 text-white-glow">
                    <i class="bi bi-hdd-network text-info me-2"></i>System Diagnostics
                </h6>
                <ul class="list-unstyled mb-0 fs-7">
                    <li class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-15">
                        <span class="text-secondary">PHP Engine</span>
                        <span class="fw-bold text-light"><?= PHP_VERSION ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-15">
                        <span class="text-secondary">Environment</span>
                        <span class="badge bg-info bg-opacity-25 text-info"><?= APP_ENV ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-15">
                        <span class="text-secondary">Timezone</span>
                        <span class="text-light"><?= date_default_timezone_get() ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-15">
                        <span class="text-secondary">Database PDO</span>
                        <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Connected</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-15">
                        <span class="text-secondary">CSRF Engine</span>
                        <span class="text-success fw-bold"><i class="bi bi-shield-fill-check"></i> Enforced</span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-secondary">Theme Engine</span>
                        <span class="text-warning fw-bold"><i class="bi bi-palette-fill"></i> Dual Mode</span>
                    </li>
                </ul>
            </div>

            <!-- Architecture Info -->
            <div class="fp-card p-4">
                <h6 class="fw-bold mb-2 text-white-glow">
                    <i class="bi bi-info-circle text-warning me-2"></i>Architecture Note
                </h6>
                <p class="text-secondary fs-8 mb-0">
                    Feature toggles enable modular isolation. When a feature is disabled, its navigation links, controller actions, and API endpoints are gracefully gated with 403 / redirect protections.
                </p>
            </div>
        </div>
    </div>
</div>
