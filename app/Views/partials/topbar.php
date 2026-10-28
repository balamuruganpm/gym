<?php
$gym = current_gym();
$user = auth_user();
$isSupreme = is_supreme_leader();
$isSupportMode = (bool) \App\Services\Session::get('support_mode', false);
?>
<header class="gf-topbar">
    <div class="d-flex align-items-center gap-3">
        <!-- Mobile Toggle Button -->
        <button type="button" class="gf-icon-btn d-lg-none" id="gfSidebarToggle" aria-label="Toggle Navigation">
            <i class="bi bi-list fs-4"></i>
        </button>

        <!-- Global Search Box -->
        <div class="gf-search-box d-none d-md-block">
            <i class="bi bi-search gf-search-icon"></i>
            <input 
                type="text" 
                class="gf-search-input" 
                placeholder="Search records, phone, or ID... (Ctrl+K)" 
                id="globalSearchInput"
            >
        </div>
    </div>

    <div class="gf-topbar-actions">
        <?php if ($isSupportMode): ?>
            <!-- Audited Support Mode Pill -->
            <div class="d-flex align-items-center gap-2 px-3 py-1 bg-warning-subtle text-warning-emphasis border border-warning rounded-pill small fw-semibold shadow-sm">
                <i class="bi bi-eye-fill"></i>
                <span>Support Mode: <?= e($gym['name'] ?? 'Gym #' . \App\Services\Session::get('gym_id')) ?></span>
                <form action="<?= url('admin/exit-support') ?>" method="POST" class="d-inline m-0 p-0">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger py-0 px-2 rounded-pill ms-2" style="font-size: 11px;">
                        Exit
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Current Context / Gym Badge -->
        <div class="gf-gym-badge">
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width: 24px; height: 24px; font-size: 11px;">
                <?php if ($isSupreme && !$isSupportMode): ?>
                    <i class="bi bi-shield-shaded"></i>
                <?php elseif (user_role() === 'trainer'): ?>
                    <i class="bi bi-person-workspace"></i>
                <?php elseif (user_role() === 'member'): ?>
                    <i class="bi bi-person"></i>
                <?php else: ?>
                    <i class="bi bi-building"></i>
                <?php endif; ?>
            </div>
            <span>
                <?php if ($isSupreme && !$isSupportMode): ?>
                    System Console
                <?php else: ?>
                    <?= e($gym['name'] ?? 'GymFlow Hub') ?>
                <?php endif; ?>
            </span>
        </div>
    </div>
</header>
