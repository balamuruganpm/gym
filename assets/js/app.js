/**
 * FitPulse Client-Side JavaScript Helpers & Theme Controller
 */

// Initialize theme immediately to prevent flashing
(function() {
    const savedTheme = localStorage.getItem('fp_theme') || 'dark';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
})();

document.addEventListener('DOMContentLoaded', function () {
    // Auto attach CSRF token to all fetch / AJAX requests
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;

    if (csrfToken) {
        window.csrfToken = csrfToken;
    }

    // Auto dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });

    // Update Theme Toggle UI Icons if present
    updateThemeToggleButtons();
});

/**
 * Toggle Light / Dark Theme
 */
window.toggleTheme = function () {
    const current = document.documentElement.getAttribute('data-bs-theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-bs-theme', next);
    localStorage.setItem('fp_theme', next);
    updateThemeToggleButtons();
};

function updateThemeToggleButtons() {
    const current = document.documentElement.getAttribute('data-bs-theme') || 'dark';
    const toggles = document.querySelectorAll('.theme-toggle-btn');
    
    toggles.forEach(btn => {
        const hasTextClass = btn.classList.contains('has-text') || btn.querySelector('.theme-text');
        if (current === 'dark') {
            if (hasTextClass) {
                btn.innerHTML = '<i class="bi bi-sun-fill text-warning me-1"></i><span class="theme-text">Light Mode</span>';
            } else {
                btn.innerHTML = '<i class="bi bi-sun-fill text-warning"></i>';
            }
            btn.setAttribute('title', 'Switch to Light Theme');
            btn.setAttribute('aria-label', 'Switch to Light Theme');
        } else {
            if (hasTextClass) {
                btn.innerHTML = '<i class="bi bi-moon-stars-fill text-danger me-1"></i><span class="theme-text">Dark Mode</span>';
            } else {
                btn.innerHTML = '<i class="bi bi-moon-stars-fill text-danger"></i>';
            }
            btn.setAttribute('title', 'Switch to Dark Theme');
            btn.setAttribute('aria-label', 'Switch to Dark Theme');
        }
    });
}

/**
 * Universal AJAX helper with built-in CSRF
 */
window.fpFetch = async function (url, options = {}) {
    options.headers = options.headers || {};
    if (window.csrfToken) {
        options.headers['X-CSRF-TOKEN'] = window.csrfToken;
    }
    options.headers['Accept'] = 'application/json';
    return fetch(url, options);
};
