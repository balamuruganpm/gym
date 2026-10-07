<!-- In-App Camera Scanner for Member Mobile (Scanning Gym Wall Poster) -->
<div class="fp-member-scanner-container max-w-md mx-auto py-2">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <a href="<?= BASE_URL ?>/member/dashboard" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-chevron-left me-1"></i> Back
        </a>
        <h5 class="fw-bold mb-0 text-white-glow">Scan Gym QR Code</h5>
        <div style="width: 60px;"></div>
    </div>

    <!-- Scanner Card -->
    <div class="fp-card p-4 text-center">
        <div class="mb-3">
            <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-25 px-3 py-1 fs-8 fw-bold">Live Camera Check-In</span>
        </div>

        <p class="text-secondary fs-7 mb-3">Point your phone camera directly at the FitPulse Wall Poster at the gym entrance.</p>

        <!-- Camera Frame Viewport -->
        <div class="scanner-viewport-box bg-dark rounded-4 p-2 position-relative overflow-hidden mb-3 border border-warning border-2" style="height: 300px; display: flex; align-items: center; justify-content: center;">
            <video id="memberScannerVideo" class="rounded-3 w-100 h-100" style="object-fit: cover;" autoplay playsinline></video>
            
            <!-- Scanning Reticle & Laser Line -->
            <div class="scanner-reticle position-absolute" style="width: 200px; height: 200px; border: 2px dashed rgba(245, 158, 11, 0.8); border-radius: 16px; pointer-events: none;">
                <div class="scanner-laser-line"></div>
            </div>

            <!-- Standby Overlay -->
            <div id="cameraStandbyPrompt" class="position-absolute text-center p-3" style="display: none;">
                <i class="bi bi-camera-video-off fs-1 text-secondary d-block mb-2"></i>
                <p class="text-light fs-7 mb-2">Camera access is paused or permission was not granted.</p>
                <button type="button" class="btn btn-sm btn-warning text-dark fw-bold" id="btnRetryCamera">Enable Camera</button>
            </div>
        </div>

        <!-- Scanner Status Feedback -->
        <div id="scannerStatusText" class="alert alert-secondary bg-surface border-secondary border-opacity-25 text-light fs-8 py-2 mb-3">
            <i class="bi bi-camera me-1 text-warning"></i> Align QR code within the frame...
        </div>

        <!-- Fallback Manual Entry -->
        <div class="pt-3 border-top border-secondary border-opacity-25">
            <p class="text-secondary fs-8 mb-2">Can't scan with camera? Enter the Gym Token or Member Code:</p>
            <form action="<?= BASE_URL ?>/checkin/gym-qr" method="GET" class="d-flex gap-2">
                <input type="text" name="branch_token" class="form-control form-control-sm bg-surface text-light border-secondary border-opacity-25" placeholder="Gym Token (e.g. FP-MAIN-001)" required>
                <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold px-3">Check In</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('memberScannerVideo');
    const standbyPrompt = document.getElementById('cameraStandbyPrompt');
    const statusText = document.getElementById('scannerStatusText');
    const btnRetry = document.getElementById('btnRetryCamera');
    let scanning = true;
    let canvas = document.createElement('canvas');
    let canvasCtx = canvas.getContext('2d');

    function startScanner() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            statusText.textContent = 'Camera is not supported on this browser. Please use manual check-in below.';
            standbyPrompt.style.display = 'block';
            return;
        }

        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(stream => {
                video.srcObject = stream;
                video.setAttribute('playsinline', true);
                video.play();
                standbyPrompt.style.display = 'none';
                requestAnimationFrame(tick);
            })
            .catch(err => {
                standbyPrompt.style.display = 'block';
                statusText.textContent = 'Camera permission denied or camera unavailable.';
            });
    }

    function tick() {
        if (!scanning) return;

        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.height = video.videoHeight;
            canvas.width = video.videoWidth;
            canvasCtx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = canvasCtx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "dontInvert",
            });

            if (code && code.data) {
                // If scanned a valid URL or token
                scanning = false;
                statusText.className = 'alert alert-success bg-success bg-opacity-20 text-success fs-8 py-2 mb-3';
                statusText.innerHTML = '<i class="bi bi-check-circle me-1"></i> QR Code detected! Verifying check-in...';

                let targetUrl = code.data;
                // If scanned url contains checkin
                if (targetUrl.includes('checkin') || targetUrl.includes('branch_token')) {
                    window.location.href = targetUrl;
                } else {
                    // Fallback to checkin with scanned raw token
                    window.location.href = '<?= BASE_URL ?>/checkin/gym-qr?branch_token=' + encodeURIComponent(targetUrl);
                }
                return;
            }
        }
        requestAnimationFrame(tick);
    }

    startScanner();
    if (btnRetry) {
        btnRetry.addEventListener('click', startScanner);
    }
});
</script>
