<div class="container-fluid p-0">
    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge" style="background: rgba(2, 132, 199, 0.12); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); border-radius: 9999px; font-weight: 700; padding: 4px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">
                    <i class="bi bi-people me-1"></i> Roster
                </span>
                <span class="text-muted" style="font-size: 13px;">Trainer Workspace</span>
            </div>
            <h2 class="h3 fw-bold mb-0 text-dark">My Assigned Trainees</h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('trainer/sessions') ?>" class="gf-btn gf-btn-outline">
                <i class="bi bi-calendar-event"></i> View Schedule
            </a>
            <a href="<?= url('trainer/progress') ?>" class="gf-btn gf-btn-primary">
                <i class="bi bi-pencil-square"></i> Record Workout
            </a>
        </div>
    </div>

    <!-- Trainees Table / List -->
    <div class="gf-card">
        <div class="p-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="background: rgba(0, 0, 0, 0.02);">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Active Member Roster</h5>
                <p class="small text-muted mb-0">You have coaching responsibility for <?= count($members) ?> member(s)</p>
            </div>
            <span class="badge bg-light text-dark border">
                <?= count($members) ?> Assigned
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th style="padding-left: 20px;">Member</th>
                        <th>Contact Phone</th>
                        <th>Active Plan</th>
                        <th>Plan Expiry</th>
                        <th>Assigned On</th>
                        <th class="text-end" style="padding-right: 20px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($members)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x display-6 d-block mb-2 text-secondary"></i>
                                No members currently assigned to you. The gym owner assigns trainees from the Owner Command Center.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($members as $m): ?>
                            <tr>
                                <td style="padding-left: 20px;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="gf-user-avatar">
                                            <?= strtoupper(substr($m['first_name'], 0, 1) . substr($m['last_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <a href="<?= url('trainer/members/' . $m['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                                <?= e($m['first_name'] . ' ' . $m['last_name']) ?>
                                            </a>
                                            <div class="small text-muted">
                                                <code><?= e($m['member_number']) ?></code>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="small">
                                    <?= e($m['phone'] ?? '—') ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= e($m['plan_name_snapshot'] ?? 'No Plan') ?>
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    <?= !empty($m['plan_expiry_date']) ? e(date('M d, Y', strtotime($m['plan_expiry_date']))) : '—' ?>
                                </td>
                                <td class="small text-muted">
                                    <?= e(date('M d, Y', strtotime($m['assigned_at']))) ?>
                                </td>
                                <td class="text-end" style="padding-right: 20px;">
                                    <a href="<?= url('trainer/members/' . $m['id']) ?>" class="btn btn-sm btn-light border py-1 px-3 fw-semibold text-primary">
                                        <i class="bi bi-eye me-1"></i> Trainee Profile
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
