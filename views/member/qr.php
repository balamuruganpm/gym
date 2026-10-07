<div class="text-center mb-3">
    <h4 class="fw-bold mb-1"><i class="bi bi-person-badge text-danger me-2"></i>Digital Member Pass</h4>
    <p class="text-secondary small mb-0">Present this QR code at gym kiosk or reception</p>
</div>

<!-- Digital Pass Card -->
<div class="fp-card p-4 rounded-4 text-center mx-auto position-relative overflow-hidden shadow-lg" style="max-width: 380px;">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-lightning-charge-fill text-danger fs-4"></i>
            <span class="fw-bold fs-6">FITPULSE</span>
        </div>
        <span class="badge bg-secondary font-monospace"><?= htmlspecialchars($member['branch_name'] ?? 'Main Branch') ?></span>
    </div>

    <!-- QR Code Display -->
    <div class="p-3 bg-white rounded-4 d-inline-block mx-auto mb-3 shadow">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= urlencode($member['qr_token'] ?? $member['member_code'] ?? 'MEMBER') ?>" 
             alt="Member QR Code" 
             class="img-fluid rounded-3" 
             style="width: 220px; height: 220px;">
    </div>

    <!-- Member Details -->
    <h4 class="fw-bold mb-1"><?= htmlspecialchars($member['name'] ?? 'Member') ?></h4>
    <div class="badge bg-primary bg-opacity-25 text-primary fs-6 font-monospace mb-3">
        ID: <?= htmlspecialchars($member['member_code'] ?? '') ?>
    </div>

    <!-- Membership Validity Box -->
    <div class="p-3 bg-secondary bg-opacity-10 rounded-3 border border-secondary border-opacity-25 text-start">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-secondary small">PLAN:</span>
            <span class="fw-bold small"><?= htmlspecialchars($member['plan_name'] ?? 'No Active Plan') ?></span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-secondary small">VALID UNTIL:</span>
            <span class="fw-bold small"><?= !empty($member['end_date']) ? date('d M Y', strtotime($member['end_date'])) : 'N/A' ?></span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-secondary small">STATUS:</span>
            <?php 
            $statusBadge = 'bg-success';
            if (($member['status'] ?? '') === 'expired') $statusBadge = 'bg-danger';
            elseif (($member['status'] ?? '') === 'expiring_soon') $statusBadge = 'bg-warning text-dark';
            elseif (($member['status'] ?? '') === 'frozen') $statusBadge = 'bg-info text-dark';
            ?>
            <span class="badge <?= $statusBadge ?> small text-uppercase"><?= str_replace('_', ' ', $member['status'] ?? 'Active') ?></span>
        </div>
    </div>

    <div class="mt-3 text-muted small" style="font-size: 11px;">
        <i class="bi bi-shield-check text-success me-1"></i> Authenticated Digital ID Token
    </div>
</div>
