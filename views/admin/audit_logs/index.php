<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-shield-lock-fill me-2 text-danger"></i> System Audit Trail</h3>
        <p class="text-secondary small mb-0">Immutable chronological log of administrative, financial, and access security events</p>
    </div>
</div>

<div class="fp-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary fs-8 text-uppercase">
                    <th>Timestamp</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>User</th>
                    <th>Branch</th>
                    <th>Details</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody class="fs-7">
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">No audit records found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-secondary small">
                                <?= date('d M Y, h:i:s A', strtotime($log['created_at'])) ?>
                            </td>
                            <td>
                                <?php
                                $badge = 'bg-secondary';
                                if (strpos($log['action'], 'create') !== false) $badge = 'bg-success';
                                elseif (strpos($log['action'], 'delete') !== false || strpos($log['action'], 'block') !== false) $badge = 'bg-danger';
                                elseif (strpos($log['action'], 'update') !== false || strpos($log['action'], 'toggle') !== false) $badge = 'bg-warning text-dark';
                                elseif (strpos($log['action'], 'auth') !== false) $badge = 'bg-info text-dark';
                                ?>
                                <span class="badge <?= $badge ?> font-monospace fs-8"><?= htmlspecialchars($log['action'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-25 border border-secondary border-opacity-25 text-body text-uppercase fs-8"><?= htmlspecialchars($log['entity_type'] ?? '', ENT_QUOTES, 'UTF-8') ?> #<?= $log['entity_id'] ?: '-' ?></span>
                            </td>
                            <td>
                                <span class="fw-semibold text-body"><?= htmlspecialchars($log['user_name'] ?: 'System', ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if (!empty($log['user_role'])): ?>
                                    <small class="text-secondary d-block fs-8">(<?= htmlspecialchars($log['user_role'], ENT_QUOTES, 'UTF-8') ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="text-secondary small"><?= htmlspecialchars($log['branch_name'] ?: 'Global', ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <?php if (!empty($log['details_json'])): ?>
                                    <code class="text-danger small"><?= htmlspecialchars($log['details_json'], ENT_QUOTES, 'UTF-8') ?></code>
                                <?php else: ?>
                                    <span class="text-secondary">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="text-secondary font-monospace small"><?= htmlspecialchars($log['ip_address'] ?: '127.0.0.1', ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

