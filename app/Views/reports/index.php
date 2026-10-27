<div class="mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-bold mb-1">Reports & Analytics</h2>
            <p class="text-muted small mb-0">Financial statements, uncollected fee ledgers and membership package intelligence.</p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($tab === 'collections'): ?>
                <a href="<?= url("reports/export/collections?start_date={$startDate}&end_date={$endDate}") ?>" class="btn btn-outline-success fw-semibold shadow-sm">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Collections (CSV)
                </a>
            <?php elseif ($tab === 'dues'): ?>
                <a href="<?= url("reports/export/dues") ?>" class="btn btn-outline-danger fw-semibold shadow-sm">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Dues (CSV)
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Report Navigation Tabs -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="<?= url('reports?tab=collections') ?>" class="btn btn-sm <?= $tab === 'collections' ? 'gf-btn-primary shadow-sm' : 'btn-light border text-muted' ?> fw-semibold px-3 py-2 rounded-pill">
        <i class="bi bi-cash-stack me-1"></i> Fee Collections
    </a>
    <a href="<?= url('reports?tab=dues') ?>" class="btn btn-sm <?= $tab === 'dues' ? 'gf-btn-primary shadow-sm' : 'btn-light border text-muted' ?> fw-semibold px-3 py-2 rounded-pill">
        <i class="bi bi-hourglass-split me-1"></i> Outstanding Dues
        <span class="badge <?= $tab === 'dues' ? 'bg-light text-primary' : 'bg-danger' ?> ms-1 rounded-pill">
            <?= count($duesList) ?>
        </span>
    </a>
    <a href="<?= url('reports?tab=plans') ?>" class="btn btn-sm <?= $tab === 'plans' ? 'gf-btn-primary shadow-sm' : 'btn-light border text-muted' ?> fw-semibold px-3 py-2 rounded-pill">
        <i class="bi bi-pie-chart me-1"></i> Plan Popularity
    </a>
</div>

<!-- TAB 1: COLLECTIONS REPORT -->
<?php if ($tab === 'collections'): ?>
    <!-- Date Range Filter -->
    <div class="gf-card p-3 mb-4">
        <form action="<?= url('reports') ?>" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="tab" value="collections">
            <div class="col-sm-4">
                <label class="form-label small fw-semibold">Start Date</label>
                <input type="date" name="start_date" class="gf-form-control" value="<?= e($startDate) ?>">
            </div>
            <div class="col-sm-4">
                <label class="form-label small fw-semibold">End Date</label>
                <input type="date" name="end_date" class="gf-form-control" value="<?= e($endDate) ?>">
            </div>
            <div class="col-sm-4 d-flex gap-2">
                <button type="submit" class="btn gf-btn-primary w-100 fw-semibold">Apply Filter</button>
                <a href="<?= url('reports?tab=collections') ?>" class="btn btn-light border text-muted">Current Month</a>
            </div>
        </form>
    </div>

    <!-- Summary Plaque -->
    <div class="gf-card p-4 mb-4 bg-light border">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Verified Period Collections</span>
                <div class="fs-2 fw-bold text-success mb-0"><?= format_currency($collectionsTotal) ?></div>
                <span class="text-muted small">Total collected between <?= date('d M Y', strtotime($startDate)) ?> and <?= date('d M Y', strtotime($endDate)) ?></span>
            </div>
            <div class="text-end">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
                    <?= count($collections) ?> Receipts
                </span>
            </div>
        </div>
    </div>

    <!-- Collections Table -->
    <div class="gf-card p-0 overflow-hidden">
        <?php if (empty($collections)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                <h5 class="fw-bold text-dark">No Collections Found</h5>
                <p class="small text-muted mb-0">No payment receipts were recorded for this date range.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-4">Receipt</th>
                            <th>Date</th>
                            <th>Member</th>
                            <th>Plan</th>
                            <th>Method</th>
                            <th class="text-end pe-4">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($collections as $c): ?>
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-primary">
                                    <a href="<?= url("payments/{$c['id']}/receipt") ?>" class="text-decoration-none">
                                        <?= e($c['receipt_number']) ?>
                                    </a>
                                </td>
                                <td class="small"><?= date('d M Y', strtotime($c['payment_date'])) ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></div>
                                    <span class="text-muted" style="font-size: 11px;">#<?= e($c['member_number']) ?></span>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= e($c['plan_name_snapshot']) ?></span></td>
                                <td><span class="badge bg-light text-muted border text-uppercase" style="font-size: 10px;"><?= e($c['payment_method']) ?></span></td>
                                <td class="text-end pe-4 fw-bold text-success fs-6"><?= format_currency((float) $c['amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- TAB 2: OUTSTANDING DUES REPORT -->
<?php if ($tab === 'dues'): ?>
    <div class="gf-card p-4 mb-4 bg-light border">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Total Uncollected Dues</span>
                <div class="fs-2 fw-bold text-danger mb-0"><?= format_currency($duesTotal) ?></div>
                <span class="text-muted small">Across <?= count($duesList) ?> members with pending payments</span>
            </div>
            <div>
                <a href="<?= url('reports/export/dues') ?>" class="btn btn-sm btn-outline-danger fw-semibold">
                    <i class="bi bi-download me-1"></i> Download Dues CSV
                </a>
            </div>
        </div>
    </div>

    <div class="gf-card p-0 overflow-hidden">
        <?php if (empty($duesList)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                <h5 class="fw-bold text-dark">Zero Outstanding Dues!</h5>
                <p class="small text-muted mb-0">All active subscriptions are 100% paid and cleared.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-4">Member</th>
                            <th>Contact</th>
                            <th>Plan</th>
                            <th>Total Cost</th>
                            <th>Paid So Far</th>
                            <th>Outstanding Due</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($duesList as $due): ?>
                            <tr>
                                <td class="ps-4">
                                    <a href="<?= url("members/{$due['member_id']}") ?>" class="text-decoration-none fw-bold text-dark">
                                        <?= e($due['first_name'] . ' ' . ($due['last_name'] ?? '')) ?>
                                    </a>
                                    <div class="text-muted" style="font-size: 11px;">#<?= e($due['member_number']) ?></div>
                                </td>
                                <td>
                                    <a href="tel:<?= e($due['phone']) ?>" class="text-decoration-none text-muted small">
                                        <?= e($due['phone']) ?>
                                    </a>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= e($due['plan_name_snapshot']) ?></span></td>
                                <td class="small fw-semibold"><?= format_currency((float) $due['agreed_price']) ?></td>
                                <td class="small text-success fw-semibold"><?= format_currency((float) $due['total_paid']) ?></td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-6">
                                        <?= format_currency((float) $due['balance_due']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="<?= url("payments/create?member_id={$due['member_id']}") ?>" class="btn btn-sm btn-outline-success fw-semibold">
                                        Collect Fee
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- TAB 3: PLAN DISTRIBUTION -->
<?php if ($tab === 'plans'): ?>
    <div class="row g-4">
        <?php foreach ($planDistribution as $plan): ?>
            <div class="col-md-6 col-lg-4">
                <div class="gf-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-light text-dark border font-monospace">
                            <?= $plan['duration_value'] ?> <?= ucfirst($plan['duration_type']) ?>
                        </span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            <?= $plan['total_subscribers'] ?> Subscriptions
                        </span>
                    </div>

                    <h4 class="fw-bold mb-1"><?= e($plan['name']) ?></h4>
                    <div class="text-muted small mb-3">Base Price: <?= format_currency((float) $plan['price']) ?></div>

                    <div class="pt-3 border-top mt-auto">
                        <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Gross Revenue</span>
                        <div class="fs-4 fw-bold text-success"><?= format_currency((float) $plan['total_revenue']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
