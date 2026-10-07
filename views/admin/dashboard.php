<!-- DreamsGYM / SweatIQ Modern Executive Dashboard -->
<div class="fp-dashboard-container">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 px-2 py-1 fs-8 fw-bold">Live Pulse</span>
                <span class="text-secondary fs-8">Updated in real-time</span>
            </div>
            <h2 class="h3 fw-bold mb-0 text-white-glow">Gym Command Dashboard</h2>
            <p class="text-secondary small mb-0">Overview for <span class="fw-bold text-light"><?= htmlspecialchars($user['name'] ?? $user['full_name'] ?? 'Admin') ?></span> &bull; <?= htmlspecialchars($user['branch_name'] ?? 'Main Headquarters') ?></p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Branch Filter (if superadmin) -->
            <?php if ($user['role'] === 'superadmin' && !empty($branches)): ?>
                <form method="GET" action="<?= BASE_URL ?>/admin/dashboard" class="d-inline-block">
                    <select name="branch_id" class="form-select form-select-sm bg-surface border-secondary border-opacity-25 text-light fw-semibold" onchange="this.form.submit()">
                        <option value="">All Branches</option>
                        <?php foreach ($branches as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= ($selectedBranch == $b['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/admin/checkin" class="btn btn-sm btn-outline-danger fw-bold d-flex align-items-center gap-1">
                <i class="bi bi-qr-code-scan"></i>
                <span>Fast Desk</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/members/create" class="btn btn-sm btn-fp-primary d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-person-plus-fill"></i>
                <span>+ New Member</span>
            </a>
        </div>
    </div>

    <!-- ROW 1: DreamsGYM 4-Card Hero Metric Grid -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Active Members (Red Theme) -->
        <div class="col-xl-3 col-md-6">
            <div class="fp-card p-3 h-100 position-relative overflow-hidden border-top border-4 border-danger">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-secondary fs-8 fw-bold text-uppercase tracking-wider">Active Members</span>
                        <div class="d-flex align-items-baseline gap-2 mt-1">
                            <span class="fs-2 fw-extrabold text-danger"><?= number_format($pulse['active_members'] ?? 0) ?></span>
                            <span class="badge bg-success bg-opacity-20 text-success fs-8 fw-bold"><i class="bi bi-arrow-up-short"></i> Active</span>
                        </div>
                    </div>
                    <!-- Radial / Icon Box -->
                    <div class="bg-danger bg-opacity-15 text-danger rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between text-secondary fs-8 pt-2 border-top border-secondary border-opacity-15">
                    <span>Total Registered: <strong class="text-light"><?= $pulse['total_members'] ?? 0 ?></strong></span>
                    <a href="<?= BASE_URL ?>/admin/members" class="text-danger text-decoration-none fw-semibold">View All &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Card 2: Today's Revenue -->
        <div class="col-xl-3 col-md-6">
            <div class="fp-card p-3 h-100 position-relative overflow-hidden border-top border-4 border-success">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-secondary fs-8 fw-bold text-uppercase tracking-wider">Today's Collections</span>
                        <div class="d-flex align-items-baseline gap-2 mt-1">
                            <span class="fs-2 fw-extrabold text-success">₹<?= number_format($pulse['today_revenue'] ?? 0, 0) ?></span>
                            <span class="badge bg-success bg-opacity-20 text-success fs-8 fw-bold">Settled</span>
                        </div>
                    </div>
                    <div class="bg-success bg-opacity-15 text-success rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between text-secondary fs-8 pt-2 border-top border-secondary border-opacity-15">
                    <span>Cash, Card & UPI</span>
                    <a href="<?= BASE_URL ?>/admin/payments" class="text-success text-decoration-none fw-semibold">Ledger &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Card 3: Today's Check-ins -->
        <div class="col-xl-3 col-md-6">
            <div class="fp-card p-3 h-100 position-relative overflow-hidden border-top border-4 border-info">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-secondary fs-8 fw-bold text-uppercase tracking-wider">Today's Check-Ins</span>
                        <div class="d-flex align-items-baseline gap-2 mt-1">
                            <span class="fs-2 fw-extrabold text-info"><?= number_format($pulse['today_checkins'] ?? 0) ?></span>
                            <span class="badge bg-info bg-opacity-20 text-info fs-8 fw-bold">In Gym</span>
                        </div>
                    </div>
                    <div class="bg-info bg-opacity-15 text-info rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-person-check-fill fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between text-secondary fs-8 pt-2 border-top border-secondary border-opacity-15">
                    <!-- Recent Avatar Stack -->
                    <div class="avatar-stack d-flex">
                        <?php if (!empty($recentCheckins)): ?>
                            <?php foreach (array_slice($recentCheckins, 0, 3) as $rc): ?>
                                <span class="badge rounded-circle bg-secondary bg-opacity-50 text-light border border-dark p-1 fs-8 fw-bold" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; margin-right: -6px;">
                                    <?= strtoupper(substr($rc['name'], 0, 1)) ?>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span>Live Scanner</span>
                        <?php endif; ?>
                    </div>
                    <a href="<?= BASE_URL ?>/admin/checkin" class="text-info text-decoration-none fw-semibold">Live Desk &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Card 4: Expiring Soon -->
        <div class="col-xl-3 col-md-6">
            <div class="fp-card p-3 h-100 position-relative overflow-hidden border-top border-4 border-danger">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-secondary fs-8 fw-bold text-uppercase tracking-wider">Expiring (7-14 Days)</span>
                        <div class="d-flex align-items-baseline gap-2 mt-1">
                            <span class="fs-2 fw-extrabold text-danger"><?= number_format($pulse['expiring_soon'] ?? 0) ?></span>
                            <span class="badge bg-danger bg-opacity-20 text-danger fs-8 fw-bold">Action Needed</span>
                        </div>
                    </div>
                    <div class="bg-danger bg-opacity-15 text-danger rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between text-secondary fs-8 pt-2 border-top border-secondary border-opacity-15">
                    <span>Expired: <strong class="text-danger"><?= $pulse['expired_members'] ?? 0 ?></strong></span>
                    <a href="<?= BASE_URL ?>/admin/plans" class="text-danger text-decoration-none fw-semibold">Renewals &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 2: DreamsGYM 8-Tile Quick Action Grid -->
    <div class="fp-card p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-uppercase fs-8 text-secondary tracking-wider">
                <i class="bi bi-grid-fill text-warning me-2"></i>Quick Command Hub
            </h6>
            <span class="text-secondary fs-8">1-Click Shortcuts</span>
        </div>

        <div class="row g-2">
            <div class="col-6 col-md-3 col-xl-custom">
                <a href="<?= BASE_URL ?>/admin/checkin" class="quick-action-tile text-decoration-none d-flex flex-column align-items-center p-3 rounded-3 text-center transition-all">
                    <div class="tile-icon bg-warning bg-opacity-15 text-warning p-2 rounded-circle mb-2">
                        <i class="bi bi-person-check-fill fs-5"></i>
                    </div>
                    <span class="fw-bold fs-7 text-light">Fast Check-In</span>
                    <small class="text-secondary fs-8">Live Desk & RFID</small>
                </a>
            </div>

            <div class="col-6 col-md-3 col-xl-custom">
                <a href="<?= BASE_URL ?>/admin/members/create" class="quick-action-tile text-decoration-none d-flex flex-column align-items-center p-3 rounded-3 text-center transition-all">
                    <div class="tile-icon bg-primary bg-opacity-15 text-primary p-2 rounded-circle mb-2">
                        <i class="bi bi-person-plus-fill fs-5"></i>
                    </div>
                    <span class="fw-bold fs-7 text-light">New Member</span>
                    <small class="text-secondary fs-8">Onboard & KYC</small>
                </a>
            </div>

            <div class="col-6 col-md-3 col-xl-custom">
                <a href="<?= BASE_URL ?>/admin/payments/create" class="quick-action-tile text-decoration-none d-flex flex-column align-items-center p-3 rounded-3 text-center transition-all">
                    <div class="tile-icon bg-success bg-opacity-15 text-success p-2 rounded-circle mb-2">
                        <i class="bi bi-credit-card-2-front-fill fs-5"></i>
                    </div>
                    <span class="fw-bold fs-7 text-light">Record Payment</span>
                    <small class="text-secondary fs-8">Instant Receipt</small>
                </a>
            </div>

            <div class="col-6 col-md-3 col-xl-custom">
                <a href="<?= BASE_URL ?>/admin/attendance/qr-poster" class="quick-action-tile text-decoration-none d-flex flex-column align-items-center p-3 rounded-3 text-center transition-all">
                    <div class="tile-icon bg-info bg-opacity-15 text-info p-2 rounded-circle mb-2">
                        <i class="bi bi-qr-code-scan fs-5"></i>
                    </div>
                    <span class="fw-bold fs-7 text-light">Gym QR Poster</span>
                    <small class="text-secondary fs-8">Wall Printable</small>
                </a>
            </div>

            <div class="col-6 col-md-3 col-xl-custom">
                <a href="<?= BASE_URL ?>/admin/progress" class="quick-action-tile text-decoration-none d-flex flex-column align-items-center p-3 rounded-3 text-center transition-all">
                    <div class="tile-icon bg-danger bg-opacity-15 text-danger p-2 rounded-circle mb-2">
                        <i class="bi bi-camera-fill fs-5"></i>
                    </div>
                    <span class="fw-bold fs-7 text-light">Transformations</span>
                    <small class="text-secondary fs-8">Before & After</small>
                </a>
            </div>

            <div class="col-6 col-md-3 col-xl-custom">
                <a href="<?= BASE_URL ?>/admin/plans" class="quick-action-tile text-decoration-none d-flex flex-column align-items-center p-3 rounded-3 text-center transition-all">
                    <div class="tile-icon bg-warning bg-opacity-15 text-warning p-2 rounded-circle mb-2">
                        <i class="bi bi-tags-fill fs-5"></i>
                    </div>
                    <span class="fw-bold fs-7 text-light">Plans & Rates</span>
                    <small class="text-secondary fs-8">Packages & Offer</small>
                </a>
            </div>

            <div class="col-6 col-md-3 col-xl-custom">
                <a href="<?= BASE_URL ?>/admin/staff" class="quick-action-tile text-decoration-none d-flex flex-column align-items-center p-3 rounded-3 text-center transition-all">
                    <div class="tile-icon bg-secondary bg-opacity-25 text-light p-2 rounded-circle mb-2">
                        <i class="bi bi-person-badge-fill fs-5"></i>
                    </div>
                    <span class="fw-bold fs-7 text-light">Trainers & Staff</span>
                    <small class="text-secondary fs-8">Roster & Shifts</small>
                </a>
            </div>

            <?php if ($user['role'] === 'superadmin'): ?>
                <div class="col-6 col-md-3 col-xl-custom">
                    <a href="<?= BASE_URL ?>/admin/coder-control" class="quick-action-tile text-decoration-none d-flex flex-column align-items-center p-3 rounded-3 text-center transition-all">
                        <div class="tile-icon bg-success bg-opacity-15 text-success p-2 rounded-circle mb-2">
                            <i class="bi bi-sliders2 fs-5"></i>
                        </div>
                        <span class="fw-bold fs-7 text-light">Coder Control</span>
                        <small class="text-secondary fs-8">Feature Toggles</small>
                    </a>
                </div>
            <?php else: ?>
                <div class="col-6 col-md-3 col-xl-custom">
                    <a href="<?= BASE_URL ?>/admin/reports" class="quick-action-tile text-decoration-none d-flex flex-column align-items-center p-3 rounded-3 text-center transition-all">
                        <div class="tile-icon bg-info bg-opacity-15 text-info p-2 rounded-circle mb-2">
                            <i class="bi bi-bar-chart-line-fill fs-5"></i>
                        </div>
                        <span class="fw-bold fs-7 text-light">Analytics</span>
                        <small class="text-secondary fs-8">Audit & Exports</small>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ROW 3: Trends & Plan Distribution -->
    <div class="row g-4 mb-4">
        <!-- 7-Day Attendance Trend -->
        <div class="col-lg-7">
            <div class="fp-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0 text-white-glow">
                            <i class="bi bi-bar-chart-line-fill text-warning me-2"></i>Weekly Attendance Flow
                        </h5>
                        <p class="text-secondary fs-8 mb-0">Daily unique check-in count over past 7 days</p>
                    </div>
                    <a href="<?= BASE_URL ?>/admin/attendance" class="btn btn-sm btn-outline-secondary fs-8">Full Log &rarr;</a>
                </div>

                <div class="d-flex align-items-end justify-content-between pt-4 pb-2" style="height: 180px;">
                    <?php 
                    $maxAtt = 1;
                    foreach ($attendanceTrend as $t) {
                        if ($t['count'] > $maxAtt) $maxAtt = $t['count'];
                    }
                    foreach ($attendanceTrend as $t): 
                        $pct = max(12, round(($t['count'] / $maxAtt) * 100));
                        $isToday = (date('Y-m-d') === $t['date']);
                    ?>
                        <div class="text-center flex-fill mx-1">
                            <div class="small fw-bold <?= $isToday ? 'text-warning' : 'text-secondary' ?> mb-1"><?= $t['count'] ?></div>
                            <div class="w-100 bg-secondary bg-opacity-15 rounded-3 position-relative" style="height: 120px;">
                                <div class="<?= $isToday ? 'bg-warning shadow-warning' : 'bg-primary' ?> rounded-3 position-absolute bottom-0 start-0 w-100 transition-all" style="height: <?= $pct ?>%;"></div>
                            </div>
                            <div class="text-secondary mt-2 font-monospace fs-8"><?= substr($t['label'], 0, 3) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Plan Breakdown & Subscription Mix -->
        <div class="col-lg-5">
            <div class="fp-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0 text-white-glow">
                            <i class="bi bi-pie-chart-fill text-success me-2"></i>Plan Popularity
                        </h5>
                        <p class="text-secondary fs-8 mb-0">Active members by package type</p>
                    </div>
                    <a href="<?= BASE_URL ?>/admin/plans" class="btn btn-sm btn-outline-secondary fs-8">Manage Plans</a>
                </div>

                <div class="mt-3">
                    <?php if (empty($planDistribution)): ?>
                        <div class="text-center py-4 text-secondary fs-7">
                            <i class="bi bi-tags fs-3 d-block mb-2 text-warning opacity-50"></i>
                            No plan statistics available yet.
                        </div>
                    <?php else: ?>
                        <?php 
                        $totalSubscribers = array_sum(array_column($planDistribution, 'subscriber_count'));
                        foreach ($planDistribution as $pd): 
                            $pct = $totalSubscribers > 0 ? round(($pd['subscriber_count'] / $totalSubscribers) * 100) : 0;
                        ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold fs-7"><?= htmlspecialchars($pd['plan_name']) ?></span>
                                    <span class="fs-8 text-secondary"><strong><?= $pd['subscriber_count'] ?></strong> members (<?= $pct ?>%)</span>
                                </div>
                                <div class="progress bg-secondary bg-opacity-15" style="height: 8px;">
                                    <div class="progress-bar bg-warning rounded-pill" role="progressbar" style="width: <?= $pct ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 4: Live Activity Feeds & Upcoming Renewals -->
    <div class="row g-4 mb-4">
        <!-- Live Check-Ins Feed -->
        <div class="col-lg-6">
            <div class="fp-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-white-glow">
                        <i class="bi bi-clock-history text-info me-2"></i>Today's Live Check-Ins
                    </h5>
                    <a href="<?= BASE_URL ?>/admin/attendance" class="text-secondary fs-8 text-decoration-none">View All &rarr;</a>
                </div>

                <div class="list-group list-group-flush bg-transparent">
                    <?php if (empty($recentCheckins)): ?>
                        <div class="text-center py-4 text-secondary fs-7">
                            <i class="bi bi-qr-code-scan fs-3 d-block mb-1 text-info opacity-50"></i>
                            No check-ins logged yet today.
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentCheckins as $rc): ?>
                            <div class="list-group-item bg-transparent px-0 py-2 border-bottom border-secondary border-opacity-15 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="bg-info bg-opacity-15 text-info rounded-circle p-2 me-3 d-flex align-items-center justify-content-center fw-bold fs-8" style="width: 36px; height: 36px;">
                                        <?= strtoupper(substr($rc['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-7"><?= htmlspecialchars($rc['name']) ?></div>
                                        <small class="text-secondary fs-8"><code><?= htmlspecialchars($rc['member_code']) ?></code> &bull; <?= ucfirst($rc['method'] ?? 'qr') ?></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success bg-opacity-20 text-success fs-8"><?= date('h:i A', strtotime($rc['check_in_time'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Upcoming Renewals & WhatsApp Action -->
        <div class="col-lg-6">
            <div class="fp-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-white-glow">
                        <i class="bi bi-hourglass-split text-danger me-2"></i>Upcoming Renewals
                    </h5>
                    <span class="badge bg-danger bg-opacity-20 text-danger fs-8">Next 14 Days</span>
                </div>

                <div class="list-group list-group-flush bg-transparent">
                    <?php if (empty($upcomingRenewals)): ?>
                        <div class="text-center py-4 text-secondary fs-7">
                            <i class="bi bi-check2-circle fs-3 d-block mb-1 text-success opacity-50"></i>
                            No memberships expiring soon!
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcomingRenewals as $ur): ?>
                            <div class="list-group-item bg-transparent px-0 py-2 border-bottom border-secondary border-opacity-15 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold fs-7"><?= htmlspecialchars($ur['name']) ?></div>
                                    <small class="text-secondary fs-8">Plan: <?= htmlspecialchars($ur['plan_name']) ?> &bull; Expires: <?= date('d M Y', strtotime($ur['end_date'])) ?></small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge <?= $ur['days_left'] <= 3 ? 'bg-danger' : 'bg-warning text-dark' ?> fs-8 fw-bold">
                                        <?= $ur['days_left'] ?> <?= $ur['days_left'] == 1 ? 'day' : 'days' ?> left
                                    </span>
                                    <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $ur['phone']) ?>?text=<?= urlencode("Hello " . $ur['name'] . "! Your " . $ur['plan_name'] . " membership at FitPulse Gym is expiring on " . date('d M Y', strtotime($ur['end_date'])) . ". Renew today to continue your workout streak! 💪") ?>" target="_blank" class="btn btn-sm btn-success p-1 px-2" title="Send WhatsApp Renewal Reminder">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 5: Inactive Members Retention Alert -->
    <div class="fp-card p-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
            <div>
                <h5 class="fw-bold mb-1 text-danger">
                    <i class="bi bi-bell-fill text-danger me-2"></i>Retention Radar: Inactive Members (7+ Days)
                </h5>
                <p class="text-secondary fs-8 mb-0">Active members who haven't worked out this week — send quick motivation to prevent churn.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/reports" class="btn btn-sm btn-outline-secondary fs-8">View Inactive Report</a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-secondary fs-8 text-uppercase">
                        <th>Member Code</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Last Attended</th>
                        <th>Inactive Days</th>
                        <th class="text-end">Retention Outreach</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inactiveMembers)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-secondary fs-7">
                                <i class="bi bi-emoji-sunglasses fs-2 d-block mb-1 text-success"></i>
                                Excellent! All active members are attending workouts regularly.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inactiveMembers as $im): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($im['member_code']) ?></code></td>
                                <td class="fw-bold"><?= htmlspecialchars($im['name']) ?></td>
                                <td><?= htmlspecialchars($im['phone']) ?></td>
                                <td>
                                    <?= $im['last_attendance'] ? date('d M Y, h:i A', strtotime($im['last_attendance'])) : '<span class="badge bg-danger">Never attended</span>' ?>
                                </td>
                                <td>
                                    <span class="badge bg-danger bg-opacity-15 text-danger fw-bold">
                                        <?= $im['days_inactive'] ? $im['days_inactive'] . ' days' : 'N/A' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $im['phone']) ?>?text=<?= urlencode("Hey " . $im['name'] . "! We missed you at FitPulse Gym this week! 💪 How is your workout progress going? Drop in today for a quick session!") ?>" target="_blank" class="btn btn-sm btn-success">
                                        <i class="bi bi-whatsapp me-1"></i> Motivate on WhatsApp
                                    </a>
                                </div>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
