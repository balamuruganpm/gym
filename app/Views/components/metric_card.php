<?php
/**
 * Metric Card Component
 * Props:
 * - title: string
 * - value: string|number
 * - subtext: string
 * - icon: string (e.g. 'bi-people')
 * - color: string ('blue', 'green', 'amber', 'rose', 'purple')
 * - trend: ?string (e.g. '+12%')
 * - trend_direction: ?string ('up', 'down')
 */
$title = $title ?? 'Metric';
$value = $value ?? '0';
$subtext = $subtext ?? '';
$icon = $icon ?? 'bi-bar-chart';
$color = $color ?? 'blue';
$trend = $trend ?? null;
$trendDir = $trend_direction ?? 'up';
?>
<div class="gf-metric-card">
    <div class="gf-metric-header">
        <div class="gf-metric-icon <?= e($color) ?>">
            <i class="bi <?= e($icon) ?>"></i>
        </div>
        <?php if ($trend): ?>
            <span class="gf-badge <?= $trendDir === 'up' ? 'active' : 'expired' ?>">
                <i class="bi <?= $trendDir === 'up' ? 'bi-arrow-up-short' : 'bi-arrow-down-short' ?>"></i>
                <?= e($trend) ?>
            </span>
        <?php endif; ?>
    </div>
    <div class="gf-metric-title"><?= e($title) ?></div>
    <div class="gf-metric-value"><?= e($value) ?></div>
    <?php if ($subtext): ?>
        <div class="gf-metric-subtext"><?= e($subtext) ?></div>
    <?php endif; ?>
</div>
