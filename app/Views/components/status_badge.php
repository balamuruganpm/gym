<?php
/**
 * Status Badge Component
 * Props:
 * - status: 'active' | 'expiring' | 'expired' | 'paid' | 'voided' | 'pending'
 * - label: ?string
 */
$status = strtolower($status ?? 'active');
$class = match ($status) {
    'active', 'paid' => 'active',
    'expiring', 'expiring_soon', 'pending' => 'expiring',
    'expired', 'voided', 'cancelled' => 'expired',
    default => 'neutral',
};

$label = $label ?? match ($status) {
    'active' => 'Active',
    'expiring', 'expiring_soon' => 'Expiring Soon',
    'expired' => 'Expired',
    'paid' => 'Paid',
    'voided' => 'Voided',
    'pending' => 'Pending',
    default => ucfirst($status),
};
?>
<span class="gf-badge <?= $class ?>">
    <span class="rounded-circle" style="width: 6px; height: 6px; background-color: currentColor;"></span>
    <?= e($label) ?>
</span>
