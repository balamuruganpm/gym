<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="h3 fw-bold mb-1"><i class="bi bi-graph-up text-primary me-2"></i>Gym Reports & Analytics Hub</h2>
        <p class="text-secondary small mb-0">Detailed business analytics, member trends, plan distributions, and churn risks</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="<?= BASE_URL ?>/admin/system-health" class="btn btn-outline-info me-2">
            <i class="bi bi-cpu me-1"></i> System Health Monitor
        </a>
        <button onclick="window.print();" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Print Analytics
        </button>
    </div>
</div>

<!-- Key Highlights Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="fp-card p-3 rounded-3">
            <div class="text-secondary small">ACTIVE SUBSCRIBERS</div>
            <div class="fs-3 fw-bold text-primary"><?= $pulse['active_members'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fp-card p-3 rounded-3">
            <div class="text-secondary small">TODAY'S WORKOUT CHECKINS</div>
            <div class="fs-3 fw-bold text-success"><?= $pulse['today_checkins'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fp-card p-3 rounded-3">
            <div class="text-secondary small">TODAY'S CASH & UPI REVENUE</div>
            <div class="fs-3 fw-bold text-info">₹<?= number_format($pulse['today_revenue'] ?? 0, 2) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fp-card p-3 rounded-3">
            <div class="text-secondary small">RENEWALS DUE (7 DAYS)</div>
            <div class="fs-3 fw-bold text-warning"><?= $pulse['expiring_soon'] ?? 0 ?></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Plan Popularity Breakdown -->
    <div class="col-md-6">
        <div class="fp-card rounded-4 p-4 h-100">
            <h5 class="fw-bold text-primary mb-3"><i class="bi bi-pie-chart-fill me-2"></i>Membership Plan Distribution</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-secondary small border-bottom border-secondary">
                            <th>PLAN NAME</th>
                            <th class="text-end">ACTIVE MEMBERS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($plans)): ?>
                            <tr><td colspan="2" class="text-center text-muted py-3">No active plans found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($plans as $pl): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($pl['plan_name'] ?? '') ?></td>
                                    <td class="text-end">
                                        <span class="badge bg-primary fs-6 px-3"><?= $pl['subscriber_count'] ?? 0 ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Monthly Revenue History -->
    <div class="col-md-6">
        <div class="fp-card rounded-4 p-4 h-100">
            <h5 class="fw-bold text-success mb-3"><i class="bi bi-cash-stack me-2"></i>Last 6 Months Revenue Ledger</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-secondary small border-bottom border-secondary">
                            <th>MONTH</th>
                            <th class="text-end">COLLECTION (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($revenueTrend ?? []) as $rt): ?>
                            <tr>
                                <td class="fw-bold"><?= $rt['label'] ?? '' ?></td>
                                <td class="text-end fw-bold text-success">₹<?= number_format($rt['amount'] ?? 0, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- All Inactive Members Full Retention Table -->
<div class="fp-card rounded-4 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-1 text-danger">
                <i class="bi bi-person-x-fill text-danger me-2"></i>Inactive Members List (No Attendance in 7+ Days)
            </h5>
            <p class="text-secondary small mb-0">Proactively re-engage members to maintain high retention rates</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small border-bottom border-secondary">
                    <th>Member ID</th>
                    <th>Member Name</th>
                    <th>Branch</th>
                    <th>Phone</th>
                    <th>Plan Expiry</th>
                    <th>Last Check-in</th>
                    <th>Days Inactive</th>
                    <th class="text-end">Direct WhatsApp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($inactiveMembers)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-emoji-smile fs-2 d-block mb-1 text-success"></i>
                            Zero inactive members! Everyone is hitting their workout goals.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($inactiveMembers as $im): ?>
                        <tr>
                            <td><span class="badge bg-secondary font-monospace"><?= htmlspecialchars($im['member_code'] ?? '') ?></span></td>
                            <td class="fw-bold"><?= htmlspecialchars($im['name'] ?? '') ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($im['branch_name'] ?? 'Main Branch') ?></td>
                            <td><?= htmlspecialchars($im['phone'] ?? '') ?></td>
                            <td>
                                <?= !empty($im['end_date']) ? date('d M Y', strtotime($im['end_date'])) : '-' ?>
                            </td>
                            <td>
                                <?= !empty($im['last_attendance']) ? date('d M Y, h:i A', strtotime($im['last_attendance'])) : '<span class="badge bg-danger">Never</span>' ?>
                            </td>
                            <td>
                                <span class="badge bg-danger bg-opacity-25 text-danger fw-bold">
                                    <?= !empty($im['days_inactive']) ? $im['days_inactive'] . ' days' : 'N/A' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $im['phone'] ?? '') ?>?text=<?= urlencode("Hello " . ($im['name'] ?? 'Member') . "! We noticed you haven't visited FitPulse Gym in a few days. Don't break your momentum! Let's get that workout in today!") ?>" target="_blank" class="btn btn-sm btn-success">
                                    <i class="bi bi-whatsapp me-1"></i> Send Encouragement
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
