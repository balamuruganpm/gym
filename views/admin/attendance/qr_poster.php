<div class="fp-poster-screen">
    <!-- Screen Header (Hidden on Print) -->
    <div class="d-print-none d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 px-2 py-1 fs-8 fw-bold">Facility Asset</span>
                <span class="text-secondary fs-8">Print & mount at gym reception / entrance</span>
            </div>
            <h2 class="h3 fw-bold mb-0 text-white-glow">Universal Gym QR Poster</h2>
            <p class="text-secondary small mb-0">Members scan this universal code with their mobile phone camera to check in automatically.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-fp-primary d-flex align-items-center gap-1 shadow-sm" onclick="window.print()">
                <i class="bi bi-printer-fill"></i>
                <span>Print Poster (A4)</span>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" onclick="copyLink('<?= htmlspecialchars($scanUrl, ENT_QUOTES, 'UTF-8') ?>')">
                <i class="bi bi-clipboard"></i>
                <span>Copy Link</span>
            </button>
            <a href="<?= $scanUrl ?>" target="_blank" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>Test Live Scan</span>
            </a>
        </div>
    </div>

    <!-- Printable Wall Poster Container -->
    <div class="d-flex justify-content-center">
        <div class="gym-qr-poster fp-card p-5 text-center shadow-lg position-relative" style="max-width: 540px; width: 100%; border: 3px solid rgba(239, 68, 68, 0.4); border-radius: 24px;">
            
            <!-- Poster Header / Branding -->
            <div class="mb-4">
                <div class="auth-brand-badge mb-3">
                    <i class="bi bi-lightning-charge-fill fs-2"></i>
                </div>
                <h1 class="fw-extrabold text-gradient-red tracking-tight mb-1" style="font-size: 2.2rem; letter-spacing: -0.5px;">FITPULSE GYM</h1>
                <div class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-25 px-3 py-1 fs-7 fw-bold text-uppercase mb-2">
                    <?= htmlspecialchars($branch['name'] ?? 'Main Branch', ENT_QUOTES, 'UTF-8') ?>
                </div>
                <p class="text-secondary fs-6 mb-0 fw-semibold">OFFICIAL WORKOUT CHECK-IN</p>
            </div>

            <!-- High-Res QR Code Frame -->
            <div class="bg-white p-4 rounded-4 d-inline-block shadow-lg mb-4 border border-danger border-3">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=280x280&margin=0&color=0B0D13&data=<?= urlencode($scanUrl) ?>" 
                     alt="Gym Attendance QR Code" 
                     class="img-fluid d-block" 
                     style="width: 260px; height: 260px;">
            </div>

            <!-- 3-Step Instructions -->
            <div class="poster-instructions bg-surface p-3 rounded-3 text-start mb-4 border border-secondary border-opacity-25">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-danger text-white fw-bold rounded-circle me-2 p-0 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">1</span>
                    <span class="text-light fs-7 fw-semibold">Open your phone camera or QR scanner</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-danger text-white fw-bold rounded-circle me-2 p-0 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">2</span>
                    <span class="text-light fs-7 fw-semibold">Scan this FitPulse Wall Poster</span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success text-white fw-bold rounded-circle me-2 p-0 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">3</span>
                    <span class="text-success fs-7 fw-bold">Attendance logged instantly! Enjoy your workout 💪</span>
                </div>
            </div>

            <!-- Poster Footer -->
            <div class="text-secondary fs-8 border-top border-secondary border-opacity-25 pt-3">
                <span>Universal Attendance Portal &bull; FitPulse Gym Automation Engine</span>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body {
        background: #ffffff !important;
        color: #000000 !important;
    }
    .fp-sidebar, .fp-topbar, .d-print-none, .fp-mobile-bottom-nav {
        display: none !important;
    }
    .fp-main-content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .gym-qr-poster {
        border: 2px solid #000000 !important;
        background: #ffffff !important;
        color: #000000 !important;
        box-shadow: none !important;
        page-break-inside: avoid;
        margin: 20px auto !important;
    }
    .gym-qr-poster h1, .gym-qr-poster p, .gym-qr-poster span, .gym-qr-poster div {
        color: #000000 !important;
        -webkit-text-fill-color: initial !important;
    }
    .poster-instructions {
        background: #f4f4f5 !important;
        border: 1px solid #d4d4d8 !important;
    }
}
</style>

<script>
function copyLink(url) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            alert('Gym Check-In URL copied to clipboard:\n' + url);
        });
    } else {
        prompt('Copy this Gym Check-In URL:', url);
    }
}
</script>
