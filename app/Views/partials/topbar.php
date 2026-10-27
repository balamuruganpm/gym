<?php
$gym = current_gym();
$user = auth_user();
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
                placeholder="Search members, phone, or ID... (Ctrl+K)" 
                id="globalSearchInput"
            >
        </div>
    </div>

    <div class="gf-topbar-actions">
        <!-- Quick Notification Bell -->
        <button type="button" class="gf-icon-btn position-relative" title="Notifications">
            <i class="bi bi-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                <span class="visually-hidden">New alerts</span>
            </span>
        </button>

        <!-- Current Gym Pill -->
        <div class="gf-gym-badge">
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width: 24px; height: 24px; font-size: 11px;">
                <i class="bi bi-building"></i>
            </div>
            <span><?= e($gym['name'] ?? 'Gold Fitness Gym') ?></span>
            <i class="bi bi-chevron-down text-muted" style="font-size: 11px;"></i>
        </div>
    </div>
</header>
