<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="h3 fw-bold mb-1"><i class="bi bi-robot text-danger me-2"></i>Automation Engine & Workflows</h2>
        <p class="text-secondary small mb-0">Automated membership expiry sync, birthday greetings, renewal reminders, and churn retention</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <form action="<?= BASE_URL ?>/admin/automation/run-cron" method="POST" class="d-inline">
            <?= CSRF::field() ?>
            <button type="submit" class="btn btn-fp-primary fw-bold">
                <i class="bi bi-play-circle-fill me-1"></i> Run Scheduled Automations Now
            </button>
        </form>
    </div>
</div>

<!-- Automation Cron Status Banner -->
<div class="fp-card p-3 mb-4 rounded-4 border-start border-danger border-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold text-danger mb-1"><i class="bi bi-clock-history me-1"></i> Automated Cron Worker Ready</h6>
            <div class="text-secondary small">
                To run automatically in background on your server, configure a daily cron job:
                <code class="p-1 px-2 rounded ms-1 bg-secondary bg-opacity-10 text-body">php d:\Bala\laragon\www\Projects All\per\gym\cron.php</code>
            </div>
        </div>
        <div>
            <span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-check-circle me-1"></i>CRON ENGINE ACTIVE</span>
        </div>
    </div>
</div>

<!-- Automation Rules Grid -->
<h5 class="fw-bold mb-3"><i class="bi bi-gear-wide-connected me-2"></i>Active Automation Rules</h5>
<div class="row g-3 mb-4">
    <?php if (empty($rules)): ?>
        <div class="col-12">
            <div class="fp-card p-4 text-center text-muted">No automation rules configured in database.</div>
        </div>
    <?php else: ?>
        <?php foreach ($rules as $r): ?>
            <div class="col-md-6">
                <div class="fp-card p-4 rounded-4 h-100 border-start <?= $r['is_active'] ? 'border-success' : 'border-secondary' ?> border-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-secondary text-uppercase small mb-1"><?= str_replace('_', ' ', $r['trigger_event'] ?? '') ?></span>
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($r['name'] ?? '') ?></h5>
                        </div>
                        <form action="<?= BASE_URL ?>/admin/automation/toggle" method="POST">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="rule_id" value="<?= $r['id'] ?>">
                            <input type="hidden" name="status" value="<?= $r['is_active'] ? 0 : 1 ?>">
                            <button type="submit" class="btn btn-sm <?= $r['is_active'] ? 'btn-success' : 'btn-outline-secondary' ?>">
                                <?= $r['is_active'] ? '<i class="bi bi-toggle-on fs-5"></i> Active' : '<i class="bi bi-toggle-off fs-5"></i> Inactive' ?>
                            </button>
                        </form>
                    </div>

                    <div class="text-secondary small mb-3">
                        Action: <strong><?= strtoupper($r['action_type'] ?? 'NOTIFICATION') ?></strong>
                    </div>

                    <div class="p-2 bg-secondary bg-opacity-10 rounded-3 border border-secondary border-opacity-25 small font-monospace">
                        <?= htmlspecialchars($r['message_template'] ?? 'Automated system task') ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Automation Execution Logs -->
<div class="fp-card rounded-4 p-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-journal-text me-2"></i>Recent Automation Dispatch Logs</h5>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small border-bottom border-secondary">
                    <th>Timestamp</th>
                    <th>Action</th>
                    <th>Module / Entity</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">No automation logs recorded yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td class="font-monospace small text-muted"><?= !empty($l['created_at']) ? date('d M Y, h:i:s A', strtotime($l['created_at'])) : '' ?></td>
                            <td>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><?= htmlspecialchars($l['action'] ?? '') ?></span>
                            </td>
                            <td><span class="text-secondary small text-uppercase"><?= htmlspecialchars($l['entity_type'] ?? '-') ?></span></td>
                            <td class="small font-monospace">
                                <?= htmlspecialchars(substr($l['details_json'] ?? '-', 0, 120)) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

