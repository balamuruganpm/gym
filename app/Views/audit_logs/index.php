<div class="mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-bold mb-1">Security Audit Trail</h2>
            <p class="text-muted small mb-0">Immutable chronological log of all administrative and financial actions.</p>
        </div>
        <div>
            <form action="<?= url('audit-logs') ?>" method="GET" class="d-flex gap-2">
                <select name="action" class="gf-form-select" onchange="this.form.submit()">
                    <option value="all">All System Actions</option>
                    <?php foreach ($actions as $act): ?>
                        <option value="<?= e($act) ?>" <?= $currentAction === $act ? 'selected' : '' ?>>
                            <?= e($act) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($currentAction !== 'all'): ?>
                    <a href="<?= url('audit-logs') ?>" class="btn btn-sm btn-light border text-muted">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<div class="gf-card p-0 overflow-hidden">
    <?php if (empty($logs)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-shield-check fs-1 text-success d-block mb-2"></i>
            <h5 class="fw-bold text-dark">No Audit Records Found</h5>
            <p class="small text-muted mb-0">No actions recorded matching this filter.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th class="ps-4">Timestamp</th>
                        <th>Action</th>
                        <th>Actor</th>
                        <th>Entity</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <?php 
                            $details = json_decode($log['details'] ?? '{}', true);
                            $badgeColor = match ($log['action']) {
                                'payment.voided' => 'bg-danger-subtle text-danger border-danger-subtle',
                                'payment.created' => 'bg-success-subtle text-success border-success-subtle',
                                'member.created' => 'bg-primary-subtle text-primary border-primary-subtle',
                                default => 'bg-light text-dark border',
                            };
                        ?>
                        <tr>
                            <td class="ps-4 small text-muted">
                                <div><?= date('d M Y', strtotime($log['created_at'])) ?></div>
                                <div style="font-size: 11px;"><?= date('h:i:s A', strtotime($log['created_at'])) ?></div>
                            </td>
                            <td>
                                <span class="badge <?= $badgeColor ?> border font-monospace text-lowercase px-2 py-1">
                                    <?= e($log['action']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small"><?= e($log['actor_name'] ?? 'System') ?></div>
                                <div class="text-muted" style="font-size: 11px;"><?= e($log['actor_email'] ?? 'cli/system') ?></div>
                            </td>
                            <td class="small">
                                <span class="badge bg-light text-muted border text-capitalize">
                                    <?= e($log['entity_type']) ?> #<?= e($log['entity_id'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="small">
                                <code class="text-muted d-block" style="font-size: 11px;">
                                    <?= e(json_encode($details, JSON_UNESCAPED_SLASHES)) ?>
                                </code>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
