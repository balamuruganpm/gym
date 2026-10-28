<div class="container-fluid p-0">
    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge" style="background: rgba(2, 132, 199, 0.12); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); border-radius: 9999px; font-weight: 700; padding: 4px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">
                    <i class="bi bi-calendar-week me-1"></i> Sessions
                </span>
                <span class="text-muted" style="font-size: 13px;">Training Agenda</span>
            </div>
            <h2 class="h3 fw-bold mb-0 text-dark">Training Sessions & Schedule</h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="gf-btn gf-btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleSessionModal">
                <i class="bi bi-calendar-plus me-1"></i> Book New Session
            </button>
        </div>
    </div>

    <!-- Sessions List Card -->
    <div class="gf-card">
        <div class="p-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="background: rgba(0, 0, 0, 0.02);">
            <div>
                <h5 class="fw-bold mb-0 text-dark">All Scheduled & Past Sessions</h5>
                <p class="small text-muted mb-0">Track completed workouts and mark trainee attendance</p>
            </div>
            <span class="badge bg-light text-dark border">
                <?= count($sessions) ?> Sessions on Record
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th style="padding-left: 20px;">Date & Time</th>
                        <th>Trainee Member</th>
                        <th>Contact</th>
                        <th>Notes</th>
                        <th>Status</th>
                        <th class="text-end" style="padding-right: 20px;">Attendance Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sessions)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar2-x display-6 d-block mb-2 text-secondary"></i>
                                <div class="fw-semibold text-dark mb-1">No Training Sessions Found</div>
                                <span class="small">Click "Book New Session" above to schedule a workout with an assigned trainee.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sessions as $s): ?>
                            <tr>
                                <td style="padding-left: 20px;">
                                    <div class="fw-bold text-dark"><?= date('M d, Y', strtotime($s['session_date'])) ?></div>
                                    <div class="small text-muted">
                                        <i class="bi bi-clock me-1 text-primary"></i>
                                        <?= date('h:i A', strtotime($s['start_time'])) ?> - <?= date('h:i A', strtotime($s['end_time'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></div>
                                    <div class="small text-muted"><code><?= e($s['member_number']) ?></code></div>
                                </td>
                                <td class="small">
                                    <?= e($s['phone'] ?? '—') ?>
                                </td>
                                <td class="small text-muted">
                                    <?= e($s['session_notes'] ?? '—') ?>
                                </td>
                                <td>
                                    <?php if ($s['status'] === 'completed'): ?>
                                        <span class="gf-badge active"><i class="bi bi-check-circle-fill"></i> Completed</span>
                                    <?php elseif ($s['status'] === 'scheduled'): ?>
                                        <span class="gf-badge expiring"><i class="bi bi-clock-fill"></i> Scheduled</span>
                                    <?php elseif ($s['status'] === 'no_show'): ?>
                                        <span class="gf-badge neutral"><i class="bi bi-person-x-fill"></i> No-Show</span>
                                    <?php else: ?>
                                        <span class="gf-badge expired"><i class="bi bi-x-circle-fill"></i> Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end" style="padding-right: 20px;">
                                    <?php if ($s['status'] === 'scheduled'): ?>
                                        <div class="btn-group btn-group-sm">
                                            <form method="POST" action="<?= url('trainer/sessions/' . $s['id'] . '/status') ?>" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 fw-semibold" title="Mark Completed">
                                                    <i class="bi bi-check-lg"></i> Complete
                                                </button>
                                            </form>
                                            <form method="POST" action="<?= url('trainer/sessions/' . $s['id'] . '/status') ?>" class="d-inline ms-1">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="no_show">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Mark No-Show">
                                                    No-Show
                                                </button>
                                            </form>
                                            <form method="POST" action="<?= url('trainer/sessions/' . $s['id'] . '/status') ?>" class="d-inline ms-1" onsubmit="return confirm('Cancel this session?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Cancel Session">
                                                    Cancel
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Archived</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Schedule Session Modal -->
<div class="modal fade" id="scheduleSessionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: var(--gf-border); border-radius: var(--gf-radius-lg); box-shadow: var(--gf-shadow-hover);">
            <form method="POST" action="<?= url('trainer/sessions') ?>">
                <?= csrf_field() ?>
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-calendar-plus text-primary me-1"></i> Book Training Session
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="member_id" class="form-label small fw-semibold text-dark">Assigned Trainee <span class="text-danger">*</span></label>
                        <select name="member_id" id="member_id" class="form-select form-select-lg" required style="border: var(--gf-border); border-radius: var(--gf-radius-md); font-size: 15px;">
                            <option value="">-- Choose Assigned Member --</option>
                            <?php foreach ($assignedMembers as $m): ?>
                                <option value="<?= e((string)$m['id']) ?>">
                                    <?= e($m['first_name'] . ' ' . $m['last_name']) ?> (<?= e($m['member_number']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small">Only members actively assigned to you are available.</div>
                    </div>

                    <div class="mb-3">
                        <label for="session_date" class="form-label small fw-semibold text-dark">Session Date <span class="text-danger">*</span></label>
                        <input type="date" name="session_date" id="session_date" class="form-control" value="<?= date('Y-m-d') ?>" required style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="start_time" class="form-label small fw-semibold text-dark">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" id="start_time" class="form-control" value="10:00" required style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                        </div>
                        <div class="col-6">
                            <label for="end_time" class="form-label small fw-semibold text-dark">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" id="end_time" class="form-control" value="11:00" required style="border: var(--gf-border); border-radius: var(--gf-radius-md);">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label small fw-semibold text-dark">Session Focus / Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="e.g., Upper body hypertrophy, HIIT cardio..." style="border: var(--gf-border); border-radius: var(--gf-radius-md);"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="gf-btn gf-btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="gf-btn gf-btn-primary">
                        <i class="bi bi-calendar-check-fill me-1"></i> Confirm Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
