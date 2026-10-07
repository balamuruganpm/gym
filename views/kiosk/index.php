<div class="kiosk-container text-center py-4" style="max-width: 580px; margin: 0 auto;">
    <!-- Kiosk Header -->
    <div class="mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-25 rounded-circle p-3 mb-2" style="width: 72px; height: 72px;">
            <i class="bi bi-lightning-charge-fill text-success fs-1"></i>
        </div>
        <h2 class="fw-bold text-white mb-1">FitPulse Check-In Terminal</h2>
        <p class="text-secondary small mb-0">Scan your Personal QR Code or Enter Member ID / Phone</p>
    </div>

    <!-- Live Status Result Card -->
    <div id="kioskResult" class="mb-4 d-none">
        <div class="fp-card p-4" id="resultCard">
            <div id="resultIcon" class="mb-2"></div>
            <h4 id="resultTitle" class="fw-bold mb-1"></h4>
            <p id="resultMessage" class="small mb-0"></p>
        </div>
    </div>

    <!-- Input Form Card -->
    <div class="fp-card shadow-lg p-4">
        <form id="kioskForm" onsubmit="handleKioskSubmit(event)">
            <div class="mb-4 text-start">
                <label class="form-label" for="kioskInput">Member QR Token, ID or Mobile</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-dark border-secondary border-opacity-50 text-success">
                        <i class="bi bi-qr-code-scan"></i>
                    </span>
                    <input type="text" class="form-control form-control-lg text-center fw-bold text-success fs-5" id="kioskInput" placeholder="Scan QR or type GYM-1001" autocomplete="off" autofocus required>
                </div>
            </div>

            <!-- PIN Input (Optional / Accordion) -->
            <div class="mb-4 text-start">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0" for="kioskPin">Security PIN (If Configured)</label>
                    <small class="text-secondary fs-8">Optional</small>
                </div>
                <input type="password" maxlength="6" class="form-control text-center font-monospace fs-5" id="kioskPin" placeholder="••••">
            </div>

            <div class="d-grid gap-2">
                <button type="submit" id="submitBtn" class="btn btn-fp-primary btn-lg py-3 fw-bold fs-5">
                    <i class="bi bi-check2-circle me-1"></i> CHECK IN NOW
                </button>
            </div>
        </form>

        <!-- Demo Member Shortcut -->
        <div class="pt-3 border-top border-secondary border-opacity-25 mt-4">
            <span class="text-secondary fs-8 d-block mb-2">Test Terminal with Sample Member:</span>
            <button type="button" class="btn btn-sm btn-outline-secondary px-3 fs-8" onclick="fillKioskDemo('GYM-1001', '1234')">
                <i class="bi bi-person-fill text-success me-1"></i> Fill Rahul Kumar (GYM-1001)
            </button>
        </div>
    </div>

    <div class="mt-4 text-secondary small">
        <i class="bi bi-shield-lock me-1"></i> Continuous Terminal Mode &bull; Auto-resets in 5 seconds
    </div>
</div>

<script>
function fillKioskDemo(code, pin) {
    document.getElementById('kioskInput').value = code;
    document.getElementById('kioskPin').value = pin;
}

async function handleKioskSubmit(e) {
    e.preventDefault();
    const input = document.getElementById('kioskInput');
    const pin = document.getElementById('kioskPin');
    const submitBtn = document.getElementById('submitBtn');
    const resultBox = document.getElementById('kioskResult');
    const resultCard = document.getElementById('resultCard');
    const resultIcon = document.getElementById('resultIcon');
    const resultTitle = document.getElementById('resultTitle');
    const resultMessage = document.getElementById('resultMessage');

    const tokenOrCode = input.value.trim();
    if (!tokenOrCode) return;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verifying...';

    try {
        const response = await fetch('<?= BASE_URL ?>/api/attendance/checkin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                token_or_code: tokenOrCode,
                pin: pin.value.trim() || null,
                method: 'kiosk',
                branch_id: 1
            })
        });

        const data = await response.json();
        resultBox.classList.remove('d-none');

        if (data.success) {
            resultCard.className = 'fp-card p-4 border-success border-2 bg-success bg-opacity-10';
            resultIcon.innerHTML = '<i class="bi bi-check-circle-fill text-success display-4"></i>';
            resultTitle.className = 'fw-bold text-success mb-1';
            resultTitle.innerText = 'ACCESS GRANTED';
            resultMessage.className = 'text-light mb-0';
            resultMessage.innerText = data.message;
        } else {
            resultCard.className = 'fp-card p-4 border-danger border-2 bg-danger bg-opacity-10';
            resultIcon.innerHTML = '<i class="bi bi-x-circle-fill text-danger display-4"></i>';
            resultTitle.className = 'fw-bold text-danger mb-1';
            resultTitle.innerText = 'ACCESS DENIED';
            resultMessage.className = 'text-light mb-0';
            resultMessage.innerText = data.message;
        }

        // Clear input and focus for next member
        input.value = '';
        pin.value = '';
        input.focus();

        // Auto hide result card after 5 seconds
        setTimeout(() => {
            resultBox.classList.add('d-none');
        }, 5000);

    } catch (err) {
        resultBox.classList.remove('d-none');
        resultCard.className = 'fp-card p-4 border-danger';
        resultIcon.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-danger display-4"></i>';
        resultTitle.className = 'fw-bold text-danger mb-1';
        resultTitle.innerText = 'System Error';
        resultMessage.innerText = 'Unable to connect to attendance engine.';
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> CHECK IN NOW';
    }
}
</script>
