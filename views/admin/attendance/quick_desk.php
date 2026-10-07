<!-- DreamsGYM / SweatIQ Fast Desk & Live Check-In Station -->
<div class="fp-checkin-station">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 px-2 py-1 fs-8 fw-bold">Front Desk Terminal</span>
                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 fs-8">
                    <i class="bi bi-broadcast me-1"></i> Live Reception
                </span>
            </div>
            <h2 class="h3 fw-bold mb-0 text-white-glow">Fast Desk Check-In Station</h2>
            <p class="text-secondary small mb-0">Multi-Method Check-In: Scanner, RFID, Member Code, Phone & Universal Gym QR</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="<?= BASE_URL ?>/admin/attendance/qr-poster" class="btn btn-outline-danger btn-sm fw-bold d-flex align-items-center gap-1">
                <i class="bi bi-qr-code-scan"></i>
                <span>Gym Wall Poster</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/attendance" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-calendar-check"></i>
                <span>Daily Log</span>
            </a>
        </div>
    </div>

    <!-- Main 2-Column Desk Grid -->
    <div class="row g-4">
        <!-- Left Column: Input & Scanner Terminal -->
        <div class="col-lg-7">
            <!-- Search & 1-Click Checkin Card -->
            <div class="fp-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-white-glow">
                        <i class="bi bi-search text-danger me-2"></i>Quick Member Lookup
                    </h5>
                    <span class="text-secondary fs-8">Type & Press Enter or Click Check-In</span>
                </div>

                <!-- Instant Search Input -->
                <div class="input-group input-group-lg mb-3">
                    <span class="input-group-text bg-surface border-secondary border-opacity-25 text-danger">
                        <i class="bi bi-person-badge fs-4"></i>
                    </span>
                    <input type="text" id="deskSearchInput" class="form-control bg-surface border-secondary border-opacity-25 text-light fs-6" 
                           placeholder="Type Name, Phone (10-digits), or Member Code (e.g. FP-1001)..." autofocus autocomplete="off">
                    <button class="btn btn-fp-primary fw-bold px-4" type="button" id="btnManualSearch">
                        <i class="bi bi-search me-1"></i> Find
                    </button>
                </div>

                <!-- Live Search Results Container -->
                <div id="searchResultsContainer" class="mt-3" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary fs-8 fw-bold text-uppercase">Matching Members</span>
                        <button type="button" class="btn btn-sm btn-link text-secondary text-decoration-none p-0 fs-8" id="btnClearResults">Clear</button>
                    </div>
                    <div id="searchResultsList" class="list-group">
                        <!-- Injected dynamically via JS -->
                    </div>
                </div>

                <!-- Fast Keypad / Direct Code Entry -->
                <div class="pt-3 border-top border-secondary border-opacity-15 mt-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-sm-8">
                            <input type="text" id="directCodeInput" class="form-control bg-surface border-secondary border-opacity-25 text-light text-center font-monospace fs-5 fw-bold" placeholder="Or Enter Member Code / PIN" maxlength="20">
                        </div>
                        <div class="col-sm-4">
                            <button type="button" id="btnDirectCheckin" class="btn btn-success fw-bold w-100 py-2">
                                <i class="bi bi-check2-circle me-1"></i> Quick Check-In
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Optical Camera Scanner Card -->
            <div class="fp-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0 text-white-glow">
                            <i class="bi bi-camera-video-fill text-info me-2"></i>Desk Optical QR / Barcode Scanner
                        </h5>
                        <p class="text-secondary fs-8 mb-0">Hold member mobile QR or barcode card up to the reception webcam</p>
                    </div>
                    <button class="btn btn-sm btn-outline-info" id="btnToggleCamera">
                        <i class="bi bi-camera me-1"></i> <span id="cameraBtnText">Start Camera</span>
                    </button>
                </div>

                <!-- Interactive Scanner Viewport -->
                <div class="scanner-viewport-box bg-dark bg-opacity-50 rounded-3 p-3 position-relative text-center border border-secondary border-opacity-25" style="min-height: 240px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <div id="cameraPlaceholder" class="py-4">
                        <div class="bg-info bg-opacity-10 text-info rounded-circle p-3 d-inline-flex mb-3">
                            <i class="bi bi-qr-code-scan fs-1"></i>
                        </div>
                        <p class="text-light fw-semibold mb-1">Optical Scanner Standby</p>
                        <small class="text-secondary">Click "Start Camera" or scan barcode using USB reader</small>
                    </div>
                    <video id="webcamVideo" class="rounded-3 w-100" style="max-height: 300px; display: none; object-fit: cover;" autoplay playsinline></video>
                    
                    <!-- Scanner Laser Overlay -->
                    <div id="scannerOverlay" class="scanner-laser-line" style="display: none;"></div>
                </div>

                <!-- Scan Status Alert -->
                <div id="scanFeedback" class="mt-3 alert alert-secondary bg-surface border-secondary border-opacity-25 text-light fs-8 d-flex align-items-center gap-2 mb-0">
                    <i class="bi bi-info-circle text-info"></i>
                    <span>Ready to process check-ins. Scanner supports QR codes, barcodes, and direct numeric input.</span>
                </div>
            </div>
        </div>

        <!-- Right Column: Live Stats, Capacity & Real-Time Feed -->
        <div class="col-lg-5">
            <!-- Gym Capacity & Pulse Meter -->
            <div class="fp-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-white-glow">
                        <i class="bi bi-speedometer2 text-success me-2"></i>Gym Live Occupancy
                    </h5>
                    <span class="badge bg-success bg-opacity-20 text-success fs-8">Today's Total: <?= $pulse['today_checkins'] ?? 0 ?></span>
                </div>

                <div class="row align-items-center g-3">
                    <div class="col-sm-5 text-center">
                        <div class="radial-progress-box position-relative d-inline-block">
                            <div class="fs-1 fw-extrabold text-warning"><?= $pulse['today_checkins'] ?? 0 ?></div>
                            <span class="text-secondary fs-8 fw-semibold text-uppercase">Members In</span>
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="mb-2">
                            <div class="d-flex justify-content-between text-secondary fs-8 mb-1">
                                <span>Estimated Floor Load</span>
                                <strong class="text-light"><?= min(100, round((($pulse['today_checkins'] ?? 0) / 100) * 100)) ?>%</strong>
                            </div>
                            <div class="progress bg-secondary bg-opacity-20" style="height: 8px;">
                                <div class="progress-bar bg-warning rounded-pill" style="width: <?= min(100, round((($pulse['today_checkins'] ?? 0) / 100) * 100)) ?>%;"></div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between fs-8 text-secondary">
                            <span>Active Members: <strong class="text-light"><?= $pulse['active_members'] ?? 0 ?></strong></span>
                            <span>Expiring: <strong class="text-danger"><?= $pulse['expiring_soon'] ?? 0 ?></strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Check-In Stream -->
            <div class="fp-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-white-glow">
                        <i class="bi bi-clock-history text-warning me-2"></i>Recent Check-Ins
                    </h5>
                    <span class="text-secondary fs-8">Today</span>
                </div>

                <div class="list-group list-group-flush bg-transparent" id="liveAttendanceStream">
                    <?php if (empty($recentCheckins)): ?>
                        <div class="text-center py-4 text-secondary fs-7" id="emptyAttendanceMsg">
                            <i class="bi bi-person-check fs-2 d-block mb-1 opacity-50 text-warning"></i>
                            No check-ins yet today. Ready for the first member!
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentCheckins as $rc): ?>
                            <div class="list-group-item bg-transparent px-0 py-2 border-bottom border-secondary border-opacity-15 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning bg-opacity-15 text-warning rounded-circle p-2 me-3 d-flex align-items-center justify-content-center fw-bold fs-8" style="width: 34px; height: 34px;">
                                        <?= strtoupper(substr($rc['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-7 text-light"><?= htmlspecialchars($rc['name']) ?></div>
                                        <small class="text-secondary fs-8"><code><?= htmlspecialchars($rc['member_code']) ?></code> &bull; <?= ucfirst($rc['method'] ?? 'desk') ?></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success bg-opacity-20 text-success fs-8"><?= date('h:i A', strtotime($rc['check_in_time'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audio Feedback (Synthetic Web Audio Beep) -->
<script>
function playSuccessBeep() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, audioCtx.currentTime); // High pitch A5
        gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.2);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.2);
    } catch(e) {}
}

function playErrorBeep() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(220, audioCtx.currentTime); // Low buzz
        gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.3);
    } catch(e) {}
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('deskSearchInput');
    const directCodeInput = document.getElementById('directCodeInput');
    const resultsContainer = document.getElementById('searchResultsContainer');
    const resultsList = document.getElementById('searchResultsList');
    const feedbackBox = document.getElementById('scanFeedback');
    const liveStream = document.getElementById('liveAttendanceStream');
    let searchDebounceTimer = null;

    // Search Member Functionality
    function performSearch(query) {
        if (!query || query.trim().length < 2) {
            resultsContainer.style.display = 'none';
            return;
        }

        fetch('<?= BASE_URL ?>/api/attendance/search-members?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.members || data.members.length === 0) {
                    resultsList.innerHTML = '<div class="list-group-item bg-surface text-secondary text-center py-3 fs-8">No matching active members found.</div>';
                    resultsContainer.style.display = 'block';
                    return;
                }

                let html = '';
                data.members.forEach(m => {
                    const isExp = m.status === 'expired';
                    const badgeClass = isExp ? 'bg-danger' : 'bg-success';
                    html += `
                        <div class="list-group-item bg-surface border-secondary border-opacity-25 p-3 d-flex justify-content-between align-items-center mb-1 rounded-2">
                            <div>
                                <div class="fw-bold text-light fs-7">${m.name}</div>
                                <div class="text-secondary fs-8">
                                    <code class="text-warning">${m.member_code}</code> &bull; ${m.phone || 'No phone'}
                                    <span class="badge ${badgeClass} bg-opacity-20 text-${isExp ? 'danger' : 'success'} ms-2">${m.status.toUpperCase()}</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-success fw-bold px-3 btn-do-checkin" data-member-id="${m.id}" data-member-name="${m.name}" ${isExp ? 'disabled' : ''}>
                                <i class="bi bi-check2"></i> Check In
                            </button>
                        </div>
                    `;
                });
                resultsList.innerHTML = html;
                resultsContainer.style.display = 'block';

                // Attach click handlers
                document.querySelectorAll('.btn-do-checkin').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const mId = this.getAttribute('data-member-id');
                        const mName = this.getAttribute('data-member-name');
                        executeCheckin({ member_id: mId, name: mName, method: 'desk' });
                    });
                });
            })
            .catch(err => {
                console.error(err);
            });
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            performSearch(this.value);
        }, 300);
    });

    document.getElementById('btnManualSearch').addEventListener('click', function() {
        performSearch(searchInput.value);
    });

    document.getElementById('btnClearResults').addEventListener('click', function() {
        resultsContainer.style.display = 'none';
        searchInput.value = '';
    });

    // Direct Code Check-In
    function executeDirectCheckin() {
        const val = directCodeInput.value.trim();
        if (!val) return;
        executeCheckin({ member_code: val, method: 'manual_code' });
        directCodeInput.value = '';
    }

    document.getElementById('btnDirectCheckin').addEventListener('click', executeDirectCheckin);
    directCodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            executeDirectCheckin();
        }
    });

    // Execute API Check-In
    function executeCheckin(payload) {
        feedbackBox.className = 'mt-3 alert alert-info bg-surface border-info text-info fs-8 d-flex align-items-center gap-2';
        feedbackBox.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> <span>Processing check-in...</span>';

        fetch('<?= BASE_URL ?>/api/checkin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                playSuccessBeep();
                feedbackBox.className = 'mt-3 alert alert-success bg-success bg-opacity-15 border-success text-success fs-8 d-flex align-items-center gap-2';
                feedbackBox.innerHTML = `<i class="bi bi-check-circle-fill fs-5"></i> <span><strong>Success!</strong> ${res.message || 'Check-in recorded.'}</span>`;
                
                // Clear search
                resultsContainer.style.display = 'none';
                searchInput.value = '';

                // Prepend to live stream
                const emptyMsg = document.getElementById('emptyAttendanceMsg');
                if (emptyMsg) emptyMsg.remove();

                const item = document.createElement('div');
                item.className = 'list-group-item bg-transparent px-0 py-2 border-bottom border-secondary border-opacity-15 d-flex justify-content-between align-items-center animate-fade-in';
                item.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-20 text-success rounded-circle p-2 me-3 d-flex align-items-center justify-content-center fw-bold fs-8" style="width: 34px; height: 34px;">
                            ${(res.member_name || payload.name || 'M').charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="fw-bold fs-7 text-light">${res.member_name || payload.name || 'Member'}</div>
                            <small class="text-secondary fs-8"><code>${res.member_code || payload.member_code || ''}</code> &bull; ${payload.method || 'desk'}</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success bg-opacity-20 text-success fs-8">Just Now</span>
                    </div>
                `;
                liveStream.insertBefore(item, liveStream.firstChild);
            } else {
                playErrorBeep();
                feedbackBox.className = 'mt-3 alert alert-danger bg-danger bg-opacity-15 border-danger text-danger fs-8 d-flex align-items-center gap-2';
                feedbackBox.innerHTML = `<i class="bi bi-exclamation-triangle-fill fs-5"></i> <span><strong>Failed:</strong> ${res.error || 'Unable to check in.'}</span>`;
            }
        })
        .catch(err => {
            playErrorBeep();
            feedbackBox.className = 'mt-3 alert alert-danger bg-danger bg-opacity-15 border-danger text-danger fs-8 d-flex align-items-center gap-2';
            feedbackBox.innerHTML = `<i class="bi bi-exclamation-octagon fs-5"></i> <span>Network error during checkin request.</span>`;
        });
    }

    // Camera Toggle (Standard WebRTC MediaDevices)
    let videoStream = null;
    const btnToggleCamera = document.getElementById('btnToggleCamera');
    const webcamVideo = document.getElementById('webcamVideo');
    const cameraPlaceholder = document.getElementById('cameraPlaceholder');
    const scannerOverlay = document.getElementById('scannerOverlay');
    const cameraBtnText = document.getElementById('cameraBtnText');

    btnToggleCamera.addEventListener('click', function() {
        if (videoStream) {
            // Stop Camera
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
            webcamVideo.style.display = 'none';
            scannerOverlay.style.display = 'none';
            cameraPlaceholder.style.display = 'block';
            cameraBtnText.textContent = 'Start Camera';
        } else {
            // Start Camera
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
                .then(stream => {
                    videoStream = stream;
                    webcamVideo.srcObject = stream;
                    webcamVideo.style.display = 'block';
                    scannerOverlay.style.display = 'block';
                    cameraPlaceholder.style.display = 'none';
                    cameraBtnText.textContent = 'Stop Camera';
                })
                .catch(err => {
                    alert('Could not access camera: ' + err.message);
                });
        }
    });
});
</script>
