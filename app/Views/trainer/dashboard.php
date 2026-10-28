<div class="container-fluid p-0">
    <!-- Welcome Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge" style="background: rgba(2, 132, 199, 0.12); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); border-radius: 9999px; font-weight: 700; padding: 4px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">
                    <i class="bi bi-person-badge me-1"></i> Trainer Workspace
                </span>
                <span class="text-muted" style="font-size: 13px;"><?= e($gym['name'] ?? 'GymFlow') ?></span>
            </div>
            <h2 class="h3 fw-bold mb-0 text-dark">Welcome back, <?= e($user['name'] ?? 'Coach') ?>!</h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('trainer/availability') ?>" class="gf-btn gf-btn-outline">
                <i class="bi bi-clock-history"></i> My Availability
            </a>
            <a href="<?= url('trainer/sessions') ?>" class="gf-btn gf-btn-primary">
                <i class="bi bi-calendar-plus"></i> Schedule Session
            </a>
        </div>
    </div>

    <!-- Trainer Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="gf-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Assigned Trainees</span>
                    <div class="gf-metric-icon blue" style="width: 38px; height: 38px; font-size: 16px;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark"><?= e((string) $assignedCount) ?></div>
                <div class="small text-muted">Exclusively assigned to you</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="gf-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Today's Sessions</span>
                    <div class="gf-metric-icon green" style="width: 38px; height: 38px; font-size: 16px;">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark"><?= e((string) $todaySessionsCount) ?></div>
                <div class="small text-muted">Sessions booked for today</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="gf-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Upcoming Sessions</span>
                    <div class="gf-metric-icon purple" style="width: 38px; height: 38px; font-size: 16px;">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark"><?= count($upcomingSessions) ?></div>
                <div class="small text-muted">Next upcoming bookings</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="gf-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Progress Logs</span>
                    <div class="gf-metric-icon amber" style="width: 38px; height: 38px; font-size: 16px;">
                        <i class="bi bi-journal-check"></i>
                    </div>
                </div>
                <div class="h3 fw-bold mb-1 text-dark"><?= count($recentProgress) ?></div>
                <div class="small text-muted">Recent workouts logged</div>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Tiles -->
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <a href="<?= url('trainer/members') ?>" class="gf-quick-action text-decoration-none">
                <div class="gf-quick-action-icon" style="background: #e0f2fe; color: #0284c7;">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
                <h6 class="fw-bold mb-1">My Trainees (<?= $assignedCount ?>)</h6>
                <p class="small text-muted mb-0">View member profiles, contacts, and progress histories</p>
            </a>
        </div>
        <div class="col-sm-4">
            <a href="<?= url('trainer/sessions') ?>" class="gf-quick-action text-decoration-none">
                <div class="gf-quick-action-icon" style="background: #ecfdf5; color: #10b981;">
                    <i class="bi bi-calendar-date"></i>
                </div>
                <h6 class="fw-bold mb-1">Schedule & Agenda</h6>
                <p class="small text-muted mb-0">Book training slots and mark session attendance</p>
            </a>
        </div>
        <div class="col-sm-4">
            <a href="<?= url('trainer/progress') ?>" class="gf-quick-action text-decoration-none">
                <div class="gf-quick-action-icon" style="background: #f5f3ff; color: #8b5cf6;">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <h6 class="fw-bold mb-1">Workout & Body Progress</h6>
                <p class="small text-muted mb-0">Log exercises, feedback, weight, and body fat</p>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Today's Sessions Agenda -->
        <div class="col-lg-7">
            <div class="gf-card h-100">
                <div class="p-3 d-flex align-items-center justify-content-between" style="background: rgba(0, 0, 0, 0.02);">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Today's Sessions Agenda</h5>
                        <p class="small text-muted mb-0"><?= date('l, F j, Y') ?></p>
                    </div>
                    <a href="<?= url('trainer/sessions') ?>" class="gf-btn gf-btn-outline btn-sm">
                        View All
                    </a>
                </div>
                <div class="p-3">
                    <?php if (empty($todaySessions)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-calendar2-check display-6 d-block mb-2 text-secondary"></i>
                            <div class="fw-semibold text-dark mb-1">No Sessions Scheduled For Today</div>
                            <span class="small">Use the schedule button to book workouts with your assigned trainees.</span>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush border-0">
                            <?php foreach ($todaySessions as $s): ?>
                                <div class="list-group-item d-flex flex-wrap align-items-center justify-content-between gap-2 px-0 py-3 border-0">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-2 bg-light border rounded-3 text-center" style="min-width: 70px;">
                                            <div class="fw-bold text-dark small"><?= e(date('h:i A', strtotime($s['start_time']))) ?></div>
                                            <div class="text-muted" style="font-size: 10px;"><?= e(date('h:i A', strtotime($s['end_time']))) ?></div>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></div>
                                            <div class="small text-muted">
                                                <code><?= e($s['member_number']) ?></code> &bull; <?= e($s['phone'] ?? 'No phone') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($s['status'] === 'scheduled'): ?>
                                            <form method="POST" action="<?= url('trainer/sessions/' . $s['id'] . '/status') ?>" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 fw-semibold">
                                                    <i class="bi bi-check-lg"></i> Complete
                                                </button>
                                            </form>
                                            <form method="POST" action="<?= url('trainer/sessions/' . $s['id'] . '/status') ?>" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="no_show">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                                    No-Show
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="gf-badge active"><?= e(ucfirst($s['status'])) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Trainees -->
        <div class="col-lg-5">
            <div class="gf-card h-100">
                <div class="p-3 d-flex align-items-center justify-content-between" style="background: rgba(0, 0, 0, 0.02);">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">My Assigned Trainees</h5>
                        <p class="small text-muted mb-0">Active members under your coaching</p>
                    </div>
                    <a href="<?= url('trainer/members') ?>" class="gf-btn gf-btn-outline btn-sm">
                        All Trainees
                    </a>
                </div>
                <div class="p-3">
                    <?php if (empty($activeMembers)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-person-x display-6 d-block mb-2 text-secondary"></i>
                            <div class="fw-semibold text-dark mb-1">No Members Assigned</div>
                            <span class="small">The gym owner will assign members to your coaching roster.</span>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush border-0">
                            <?php foreach (array_slice($activeMembers, 0, 5) as $m): ?>
                                <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-2 border-0">
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
                                    <a href="<?= url('trainer/members/' . $m['id']) ?>" class="btn btn-sm btn-light border py-1 px-2 text-primary">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
