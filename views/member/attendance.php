<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-calendar2-check-fill text-danger me-2"></i>Workout Log</h4>
        <p class="text-secondary small mb-0">Track your gym check-ins & streaks</p>
    </div>
    <div>
        <form action="<?= BASE_URL ?>/member/checkin" method="POST">
            <?= CSRF::field() ?>
            <button type="submit" class="btn btn-sm btn-success">
                <i class="bi bi-check-lg me-1"></i> Check In
            </button>
        </form>
    </div>
</div>

<!-- Streak & Monthly Badges -->
<div class="row g-2 mb-3">
    <div class="col-6">
        <div class="fp-card p-3 rounded-4 text-center border-start border-danger border-4">
            <div class="text-secondary small">ACTIVE STREAK</div>
            <div class="fs-2 fw-bold text-danger"><i class="bi bi-fire text-warning me-1"></i><?= $stats['streak_days'] ?? 0 ?></div>
            <div class="text-muted small">Days Consecutive</div>
        </div>
    </div>
    <div class="col-6">
        <div class="fp-card p-3 rounded-4 text-center border-start border-primary border-4">
            <div class="text-secondary small">THIS MONTH</div>
            <div class="fs-2 fw-bold text-primary"><?= $stats['month_attendance_count'] ?? 0 ?></div>
            <div class="text-muted small">Workouts Finished</div>
        </div>
    </div>
</div>

<!-- Attendance History List -->
<div class="fp-card rounded-4 p-3">
    <h6 class="fw-bold mb-3"><i class="bi bi-clock-history text-secondary me-2"></i>Last 30 Check-ins</h6>
    
    <?php if (empty($history)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
            No workout check-ins found yet.
        </div>
    <?php else: ?>
        <div class="list-group list-group-flush">
            <?php foreach ($history as $h): ?>
                <div class="list-group-item bg-transparent px-0 py-2 border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold small"><?= date('l, d M Y', strtotime($h['check_in_time'])) ?></div>
                        <div class="text-muted" style="font-size: 11px;">
                            <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($h['branch_name'] ?? 'Main Gym') ?> • <?= ucfirst(str_replace('_', ' ', $h['method'] ?? 'manual')) ?>
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-secondary font-monospace"><?= date('h:i A', strtotime($h['check_in_time'])) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
