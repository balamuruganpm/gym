<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <a href="<?= BASE_URL ?>/admin/progress?member_id=<?= $member['id'] ?>" class="text-decoration-none small text-secondary mb-1 d-block">
            <i class="bi bi-arrow-left"></i> Back to Timeline
        </a>
        <h2 class="h3 fw-bold mb-1"><i class="bi bi-symmetry-vertical text-primary me-2"></i>Interactive Before / After Transformation</h2>
        <p class="text-secondary small mb-0">Comparing Milestones for <span class="fw-bold"><?= htmlspecialchars($member['name']) ?></span></p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <button onclick="window.print();" class="btn btn-outline-secondary me-2">
            <i class="bi bi-printer me-1"></i> Print Card
        </button>
        <a href="<?= BASE_URL ?>/admin/progress/create?member_id=<?= $member['id'] ?>" class="btn btn-fp-primary">
            <i class="bi bi-plus-circle me-1"></i> New Checkpoint
        </a>
    </div>
</div>

<?php 
$base = $comparison['baseline'] ?? null;
$latest = $comparison['latest'] ?? null;
$deltas = $comparison['deltas'] ?? null;
$all = $comparison['all_checkpoints'] ?? [];
?>

<?php if (!$base || !$latest): ?>
    <div class="fp-card p-5 text-center">
        <h5 class="text-secondary">At least 2 checkpoints are required to generate a Before / After comparison.</h5>
        <div class="mt-3">
            <a href="<?= BASE_URL ?>/admin/progress/create?member_id=<?= $member['id'] ?>" class="btn btn-fp-primary">Add Checkpoint</a>
        </div>
    </div>
<?php else: ?>
    <!-- Checkpoint Selection Selector -->
    <div class="fp-card p-3 mb-4">
        <form method="GET" action="<?= BASE_URL ?>/admin/progress/compare" class="row g-3 align-items-center">
            <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
            
            <div class="col-md-5">
                <label class="form-label small">"Before" Milestone:</label>
                <select name="base_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($all as $item): ?>
                        <option value="<?= $item['id'] ?>" <?= ($item['id'] == $base['id']) ? 'selected' : '' ?>>
                            <?= strtoupper(str_replace('_', ' ', $item['checkpoint_type'])) ?> (<?= date('d M Y', strtotime($item['recorded_date'])) ?>) - <?= $item['weight_kg'] ?>kg
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 text-center pt-3">
                <i class="bi bi-arrow-left-right fs-3 text-primary"></i>
            </div>

            <div class="col-md-5">
                <label class="form-label small">"After" Milestone:</label>
                <select name="latest_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($all as $item): ?>
                        <option value="<?= $item['id'] ?>" <?= ($item['id'] == $latest['id']) ? 'selected' : '' ?>>
                            <?= strtoupper(str_replace('_', ' ', $item['checkpoint_type'])) ?> (<?= date('d M Y', strtotime($item['recorded_date'])) ?>) - <?= $item['weight_kg'] ?>kg
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- Transformation Comparison Display -->
    <div class="row g-4 mb-4">
        <!-- Interactive Split Slider Container -->
        <div class="col-lg-7">
            <div class="fp-card p-4">
                <h5 class="fw-bold mb-3 text-center">
                    <i class="bi bi-sliders me-2 text-primary"></i>Interactive Split Slider
                </h5>

                <div class="comparison-slider-wrapper position-relative mx-auto rounded-3 overflow-hidden border border-secondary border-opacity-25" style="max-width: 480px; height: 480px; user-select: none;">
                    <!-- After Image (Background) -->
                    <?php 
                    $afterImg = (!empty($latest['photo_front']) && file_exists(BASE_PATH . '/' . ltrim($latest['photo_front'], '/'))) ? BASE_URL . '/' . ltrim($latest['photo_front'], '/') : BASE_URL . '/assets/img/placeholder_body.jpg';
                    $beforeImg = (!empty($base['photo_front']) && file_exists(BASE_PATH . '/' . ltrim($base['photo_front'], '/'))) ? BASE_URL . '/' . ltrim($base['photo_front'], '/') : BASE_URL . '/assets/img/placeholder_body.jpg';
                    ?>
                    
                    <div class="after-image position-absolute top-0 start-0 w-100 h-100" style="background-image: url('<?= $afterImg ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                        <span class="badge bg-success position-absolute bottom-0 end-0 m-3 px-3 py-2 fs-7">AFTER (<?= date('d M Y', strtotime($latest['recorded_date'])) ?>)</span>
                    </div>

                    <!-- Before Image (Overlay with adjustable clip/width) -->
                    <div id="beforeImageLayer" class="before-image position-absolute top-0 start-0 h-100 overflow-hidden" style="width: 50%; background-image: url('<?= $beforeImg ?>'); background-size: 480px 480px; background-position: top left; background-repeat: no-repeat; border-right: 3px solid #10b981;">
                        <span class="badge bg-danger position-absolute bottom-0 start-0 m-3 px-3 py-2 fs-7">BEFORE (<?= date('d M Y', strtotime($base['recorded_date'])) ?>)</span>
                    </div>

                    <!-- Draggable Handle line -->
                    <div id="sliderHandle" class="position-absolute top-0 h-100" style="left: 50%; width: 4px; background: #fff; cursor: ew-resize; transform: translateX(-50%); box-shadow: 0 0 10px rgba(0,0,0,0.5);">
                        <div class="position-absolute top-50 start-50 translate-middle rounded-circle bg-white text-dark d-flex align-items-center justify-content-center shadow" style="width: 36px; height: 36px;">
                            <i class="bi bi-arrow-left-right fw-bold text-success"></i>
                        </div>
                    </div>
                </div>

                <!-- Range Control fallback -->
                <div class="mt-3 px-4">
                    <label class="form-label small text-center d-block">Drag the handle above or adjust slider below:</label>
                    <input type="range" class="form-range" id="splitRange" min="0" max="100" value="50">
                </div>
            </div>
        </div>

        <!-- Metrics & Measurements Delta Table -->
        <div class="col-lg-5">
            <div class="fp-card p-4 h-100">
                <h5 class="fw-bold text-primary mb-3">
                    <i class="bi bi-graph-up-arrow me-2"></i>Measurement Progress
                </h5>

                <div class="p-3 bg-secondary bg-opacity-10 rounded-3 mb-4 text-center">
                    <div class="text-secondary small">TIME DURATION</div>
                    <div class="fs-4 fw-bold text-info"><?= $deltas['days_between'] ?> Days Apart</div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="text-secondary small">
                                <th>METRIC</th>
                                <th>BEFORE</th>
                                <th>AFTER</th>
                                <th class="text-end">CHANGE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold">Weight</td>
                                <td><?= $base['weight_kg'] ?? '-' ?> kg</td>
                                <td><?= $latest['weight_kg'] ?? '-' ?> kg</td>
                                <td class="text-end fw-bold <?= ($deltas['weight_diff'] <= 0) ? 'text-success' : 'text-primary' ?>">
                                    <?= ($deltas['weight_diff'] > 0 ? '+' : '') . $deltas['weight_diff'] ?> kg
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Chest</td>
                                <td><?= $base['chest_in'] ?? '-' ?> in</td>
                                <td><?= $latest['chest_in'] ?? '-' ?> in</td>
                                <td class="text-end fw-bold text-info">
                                    <?= ($deltas['chest_diff'] > 0 ? '+' : '') . $deltas['chest_diff'] ?> in
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Waist</td>
                                <td><?= $base['waist_in'] ?? '-' ?> in</td>
                                <td><?= $latest['waist_in'] ?? '-' ?> in</td>
                                <td class="text-end fw-bold <?= ($deltas['waist_diff'] <= 0) ? 'text-success' : 'text-warning' ?>">
                                    <?= ($deltas['waist_diff'] > 0 ? '+' : '') . $deltas['waist_diff'] ?> in
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Arms</td>
                                <td><?= $base['arms_in'] ?? '-' ?> in</td>
                                <td><?= $latest['arms_in'] ?? '-' ?> in</td>
                                <td class="text-end fw-bold text-info">
                                    <?= ($deltas['arms_diff'] > 0 ? '+' : '') . $deltas['arms_diff'] ?> in
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Thighs</td>
                                <td><?= $base['thighs_in'] ?? '-' ?> in</td>
                                <td><?= $latest['thighs_in'] ?? '-' ?> in</td>
                                <td class="text-end fw-bold text-info">
                                    <?= ($deltas['thighs_diff'] > 0 ? '+' : '') . $deltas['thighs_diff'] ?> in
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Body Fat</td>
                                <td><?= $base['body_fat_pct'] ?? '-' ?> %</td>
                                <td><?= $latest['body_fat_pct'] ?? '-' ?> %</td>
                                <td class="text-end fw-bold <?= ($deltas['fat_diff'] <= 0) ? 'text-success' : 'text-danger' ?>">
                                    <?= ($deltas['fat_diff'] > 0 ? '+' : '') . $deltas['fat_diff'] ?> %
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var range = document.getElementById('splitRange');
    var beforeLayer = document.getElementById('beforeImageLayer');
    var handle = document.getElementById('sliderHandle');

    if (range && beforeLayer && handle) {
        function updateSlider(val) {
            beforeLayer.style.width = val + '%';
            handle.style.left = val + '%';
        }

        range.addEventListener('input', function(e) {
            updateSlider(e.target.value);
        });

        var container = document.querySelector('.comparison-slider-wrapper');
        var isDragging = false;

        container.addEventListener('mousedown', function(e) {
            isDragging = true;
            dragMove(e);
        });
        window.addEventListener('mouseup', function() {
            isDragging = false;
        });
        container.addEventListener('mousemove', function(e) {
            if (isDragging) dragMove(e);
        });

        container.addEventListener('touchstart', function(e) {
            isDragging = true;
            dragMove(e.touches[0]);
        });
        window.addEventListener('touchend', function() {
            isDragging = false;
        });
        container.addEventListener('touchmove', function(e) {
            if (isDragging) dragMove(e.touches[0]);
        });

        function dragMove(e) {
            var rect = container.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var pct = Math.max(0, Math.min(100, (x / rect.width) * 100));
            range.value = pct;
            updateSlider(pct);
        }
    }
});
</script>
