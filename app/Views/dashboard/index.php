<?php
$gym = current_gym();
$currency = $gym['currency_symbol'] ?? '₹';
$stats = $stats ?? [];
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1">Command Center</h2>
        <p class="text-muted small mb-0">Live operations, revenue intelligence and membership activity for <?= e($gym['name'] ?? 'your gym') ?>.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('members/create') ?>" class="btn gf-btn-primary fw-semibold shadow-sm">
            <i class="bi bi-person-plus me-1"></i> New Member
        </a>
        <a href="<?= url('payments/create') ?>" class="btn btn-outline-success fw-semibold shadow-sm">
            <i class="bi bi-cash-stack me-1"></i> Record Payment
        </a>
    </div>
</div>

<!-- Row 1: KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <?= render_component('metric_card', [
            'title' => 'Total Members',
            'value' => number_format((int) ($stats['total_members'] ?? 0)),
            'subtext' => 'Registered in gym',
            'icon' => 'bi-people',
            'color' => 'blue',
            'trend' => 'Directory',
            'trend_direction' => 'up'
        ]) ?>
    </div>
    <div class="col-xl-3 col-sm-6">
        <?= render_component('metric_card', [
            'title' => 'Active Members',
            'value' => number_format((int) ($stats['active_members'] ?? 0)),
            'subtext' => 'Valid access passes',
            'icon' => 'bi-person-check',
            'color' => 'green',
            'trend' => 'Active',
            'trend_direction' => 'up'
        ]) ?>
    </div>
    <div class="col-xl-3 col-sm-6">
        <?= render_component('metric_card', [
            'title' => 'Expiring in 7 Days',
            'value' => number_format((int) ($stats['expiring_soon'] ?? 0)),
            'subtext' => 'Action required',
            'icon' => 'bi-calendar-event',
            'color' => 'amber',
            'trend' => 'Renewals',
            'trend_direction' => 'up'
        ]) ?>
    </div>
    <div class="col-xl-3 col-sm-6">
        <?= render_component('metric_card', [
            'title' => 'Expired Members',
            'value' => number_format((int) ($stats['expired_members'] ?? 0)),
            'subtext' => 'Overdue renewals',
            'icon' => 'bi-calendar-x',
            'color' => 'rose',
            'trend' => 'Lapsed',
            'trend_direction' => 'down'
        ]) ?>
    </div>
</div>

<!-- Row 2: Financial Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-4 col-sm-6">
        <?= render_component('metric_card', [
            'title' => "Today's Collections",
            'value' => format_currency($stats['today_collections'] ?? 0, $currency),
            'subtext' => 'Verified receipts today',
            'icon' => 'bi-cash-coin',
            'color' => 'green',
            'trend' => 'Daily',
            'trend_direction' => 'up'
        ]) ?>
    </div>
    <div class="col-xl-4 col-sm-6">
        <?= render_component('metric_card', [
            'title' => "This Month's Revenue",
            'value' => format_currency($stats['month_collections'] ?? 0, $currency),
            'subtext' => date('F Y') . ' gross total',
            'icon' => 'bi-graph-up-arrow',
            'color' => 'blue',
            'trend' => 'Monthly',
            'trend_direction' => 'up'
        ]) ?>
    </div>
    <div class="col-xl-4 col-sm-6">
        <?= render_component('metric_card', [
            'title' => 'Pending Dues Balance',
            'value' => format_currency($stats['outstanding_dues'] ?? 0, $currency),
            'subtext' => 'Total uncollected fees',
            'icon' => 'bi-hourglass-split',
            'color' => 'purple',
            'trend' => 'Receivables',
            'trend_direction' => 'down'
        ]) ?>
    </div>
</div>

<!-- Row 3: Live Charts -->
<div class="row g-4 mb-4">
    <!-- Revenue Trend Chart -->
    <div class="col-lg-8">
        <div class="gf-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold m-0">Revenue Collections Trend</h5>
                    <span class="text-muted small">6-Month historical collections</span>
                </div>
                <span class="badge bg-light text-muted border px-2 py-1">Monthly</span>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="collectionsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Plan Popularity Doughnut Chart -->
    <div class="col-lg-4">
        <div class="gf-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold m-0">Plan Distribution</h5>
                <a href="<?= url('plans') ?>" class="small text-primary text-decoration-none fw-semibold">View Plans</a>
            </div>
            <div style="height: 220px; position: relative;" class="my-auto">
                <canvas id="planChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Row 4: Expiring Soonest & Recent Payments -->
<div class="row g-4 mb-4">
    <!-- Memberships Expiring Soonest -->
    <div class="col-lg-6">
        <div class="gf-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold m-0">Expiring Soonest (Action Required)</h5>
                    <span class="text-muted small">Members reaching end of plan</span>
                </div>
                <a href="<?= url('renewals') ?>" class="small text-primary text-decoration-none fw-semibold">View All Renewals</a>
            </div>

            <?php if (empty($expiringList)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-shield-check fs-2 text-success d-block mb-1"></i>
                    <p class="small mb-0">No active members expiring in the next 7 days.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="text-muted table-light">
                            <tr>
                                <th class="border-0">Member</th>
                                <th class="border-0">Plan</th>
                                <th class="border-0">Expiry</th>
                                <th class="border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expiringList as $item): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url("members/{$item['member_id']}") ?>" class="text-decoration-none text-dark fw-bold d-block">
                                            <?= e($item['first_name'] . ' ' . ($item['last_name'] ?? '')) ?>
                                        </a>
                                        <span class="text-muted" style="font-size: 11px;">#<?= e($item['member_number']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= e($item['plan_name_snapshot']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $item['days_left'] <= 2 ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' ?>">
                                            <?= $item['days_left'] == 0 ? 'Today!' : "{$item['days_left']}d left" ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= $item['whatsapp_url'] ?>" target="_blank" class="btn btn-sm btn-outline-success py-1 px-2 fw-semibold" title="WhatsApp Reminder">
                                            <i class="bi bi-whatsapp"></i> Remind
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="col-lg-6">
        <div class="gf-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold m-0">Recent Receipts</h5>
                    <span class="text-muted small">Latest billing activity</span>
                </div>
                <a href="<?= url('payments') ?>" class="small text-primary text-decoration-none fw-semibold">View Ledger</a>
            </div>

            <?php if (empty($recentPayments)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-wallet2 fs-2 text-secondary d-block mb-1"></i>
                    <p class="small mb-0">No payment receipts recorded yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="text-muted table-light">
                            <tr>
                                <th class="border-0">Receipt #</th>
                                <th class="border-0">Member</th>
                                <th class="border-0">Method</th>
                                <th class="border-0 text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentPayments as $p): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url("payments/{$p['id']}/receipt") ?>" class="font-monospace fw-bold text-primary text-decoration-none">
                                            <?= e($p['receipt_number']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= e($p['first_name'] . ' ' . ($p['last_name'] ?? '')) ?></div>
                                        <span class="text-muted" style="font-size: 11px;"><?= e($p['plan_name_snapshot']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 10px;">
                                            <?= e($p['payment_method']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold <?= $p['status'] === 'voided' ? 'text-decoration-line-through text-muted' : 'text-success' ?>">
                                        <?= format_currency((float) $p['amount']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart Initialization Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Collections Line Chart
    const ctx = document.getElementById('collectionsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($charts['trendLabels'] ?? []) ?>,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: <?= json_encode($charts['trendData'] ?? []) ?>,
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#0284c7',
                    borderWidth: 2.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 12,
                        cornerRadius: 10,
                        callbacks: {
                            label: (item) => ' ₹ ' + Number(item.raw).toLocaleString('en-IN')
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: {
                            callback: (val) => '₹' + (val >= 1000 ? (val / 1000) + 'k' : val)
                        }
                    }
                }
            }
        });
    }

    // 2. Plan Popularity Doughnut Chart
    const planCtx = document.getElementById('planChart');
    if (planCtx) {
        const planLabels = <?= json_encode($charts['planLabels'] ?? []) ?>;
        const planCounts = <?= json_encode($charts['planCounts'] ?? []) ?>;

        if (planLabels.length === 0) {
            planLabels.push('No Subscriptions Yet');
            planCounts.push(1);
        }

        new Chart(planCtx, {
            type: 'doughnut',
            data: {
                labels: planLabels,
                datasets: [{
                    data: planCounts,
                    backgroundColor: ['#0284c7', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 15, font: { size: 11 } }
                    }
                }
            }
        });
    }
});
</script>
