<!-- Member Greeting & Streak Card -->
<div class="fp-card p-3 mb-3 rounded-4 border-start border-danger border-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <div class="text-secondary small">WELCOME BACK</div>
            <h4 class="fw-bold mb-1"><?= htmlspecialchars($member['name'] ?? 'Member') ?></h4>
            <div class="text-muted small">Member ID: <span class="badge bg-secondary font-monospace"><?= htmlspecialchars($member['member_code'] ?? '') ?></span></div>
        </div>
        <div class="text-end">
            <div class="streak-badge mb-1">
                <i class="bi bi-fire text-warning fs-5"></i> <?= $stats['streak_days'] ?? 0 ?> DAY STREAK
            </div>
            <div class="text-muted small" style="font-size: 11px;"><?= $stats['month_attendance_count'] ?? 0 ?> workouts this month</div>
        </div>
    </div>
</div>

<!-- Active Membership Status Card -->
<div class="fp-card p-3 mb-3 rounded-4">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <span class="text-secondary small fw-bold text-uppercase">CURRENT PLAN</span>
            <h5 class="fw-bold text-primary mb-0"><?= htmlspecialchars($member['plan_name'] ?? 'No Active Plan') ?></h5>
        </div>
        <div>
            <?php 
            $statusBadge = 'bg-success';
            if (($member['status'] ?? '') === 'expired') $statusBadge = 'bg-danger';
            elseif (($member['status'] ?? '') === 'expiring_soon') $statusBadge = 'bg-warning text-dark';
            elseif (($member['status'] ?? '') === 'frozen') $statusBadge = 'bg-info text-dark';
            ?>
            <span class="badge <?= $statusBadge ?> px-3 py-2 text-uppercase"><?= str_replace('_', ' ', $member['status'] ?? 'Active') ?></span>
        </div>
    </div>

    <div class="row g-2 text-muted small mt-2 pt-2 border-top border-secondary border-opacity-25">
        <div class="col-6">
            <span>Valid Until:</span>
            <div class="fw-bold"><?= !empty($member['end_date']) ? date('d M Y', strtotime($member['end_date'])) : 'N/A' ?></div>
        </div>
        <div class="col-6 text-end">
            <span>Days Remaining:</span>
            <div class="fw-bold <?= (($member['days_remaining'] ?? 0) < 7) ? 'text-danger' : 'text-success' ?>">
                <?= isset($member['days_remaining']) && $member['days_remaining'] !== null ? $member['days_remaining'] . ' days' : 'N/A' ?>
            </div>
        </div>
    </div>

    <?php if (($member['status'] ?? '') === 'expired' || ($member['status'] ?? '') === 'expiring_soon'): ?>
        <div class="mt-3 p-2 bg-danger bg-opacity-10 border border-danger rounded-3 text-center">
            <span class="text-danger small fw-bold">Your plan needs renewal! Visit front desk or contact admin.</span>
        </div>
    <?php endif; ?>
</div>

<!-- Quick Actions Grid -->
<div class="row g-2 mb-3">
    <div class="col-4">
        <a href="<?= BASE_URL ?>/member/scan-gym-qr" class="fp-card p-3 text-center text-decoration-none rounded-4 h-100 d-block border-warning border-opacity-50">
            <i class="bi bi-camera-fill text-warning fs-2 mb-1"></i>
            <span class="fw-bold d-block fs-7">Scan Gym QR</span>
            <span class="text-secondary fs-8" style="font-size: 10px;">Wall Poster</span>
        </a>
    </div>
    <div class="col-4">
        <form action="<?= BASE_URL ?>/member/checkin" method="POST" class="h-100">
            <?= CSRF::field() ?>
            <button type="submit" class="fp-card p-3 w-100 text-center text-decoration-none rounded-4 h-100 border-0">
                <i class="bi bi-check2-circle text-success fs-2 mb-1"></i>
                <span class="fw-bold d-block fs-7">1-Tap Checkin</span>
                <span class="text-secondary fs-8" style="font-size: 10px;">Instant Log</span>
            </button>
        </form>
    </div>
    <div class="col-4">
        <a href="<?= BASE_URL ?>/member/qr" class="fp-card p-3 text-center text-decoration-none rounded-4 h-100 d-block">
            <i class="bi bi-qr-code text-info fs-2 mb-1"></i>
            <span class="fw-bold d-block fs-7">Member Pass</span>
            <span class="text-secondary fs-8" style="font-size: 10px;">Front Desk</span>
        </a>
    </div>
</div>

<!-- Latest Transformation Milestone Preview -->
<?php if (!empty($latestCheckpoint)): ?>
    <div class="fp-card p-3 mb-3 rounded-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold"><i class="bi bi-activity text-info me-1"></i> Latest Progress Check</span>
            <a href="<?= BASE_URL ?>/member/progress" class="text-decoration-none small text-info">View All <i class="bi bi-chevron-right"></i></a>
        </div>
        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($latestCheckpoint['photo_front']) && file_exists(__DIR__ . '/../../../' . $latestCheckpoint['photo_front'])): ?>
                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($latestCheckpoint['photo_front']) ?>" class="rounded-3" style="width: 60px; height: 60px; object-fit: cover;">
            <?php else: ?>
                <div class="bg-secondary bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-person fs-3 text-muted"></i>
                </div>
            <?php endif; ?>
            <div>
                <div class="fw-bold text-uppercase small text-primary"><?= str_replace('_', ' ', $latestCheckpoint['checkpoint_type'] ?? '') ?></div>
                <div class="text-muted small">Weight: <span class="fw-bold"><?= $latestCheckpoint['weight_kg'] ?? 'N/A' ?> kg</span> | Recorded: <?= !empty($latestCheckpoint['recorded_date']) ? date('d M', strtotime($latestCheckpoint['recorded_date'])) : 'N/A' ?></div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Recent Workouts List -->
<div class="fp-card p-3 rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-bold"><i class="bi bi-clock-history text-warning me-1"></i> Recent Workouts</span>
        <a href="<?= BASE_URL ?>/member/attendance" class="text-decoration-none small text-warning">History <i class="bi bi-chevron-right"></i></a>
    </div>

    <?php if (empty($recentWorkouts)): ?>
        <div class="text-center py-3 text-muted small">No check-ins recorded yet. Tap "One-Tap Checkin" above to start!</div>
    <?php else: ?>
        <div class="list-group list-group-flush">
            <?php foreach ($recentWorkouts as $w): ?>
                <div class="list-group-item bg-transparent px-0 py-2 border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold small"><?= date('l, d M Y', strtotime($w['check_in_time'])) ?></div>
                        <div class="text-muted" style="font-size: 11px;">Method: <?= ucfirst(str_replace('_', ' ', $w['method'] ?? 'manual')) ?></div>
                    </div>
                    <span class="badge bg-secondary font-monospace"><?= date('h:i A', strtotime($w['check_in_time'])) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
