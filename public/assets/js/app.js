/**
 * GymFlow — Core Frontend Interactivity
 */

document.addEventListener('DOMContentLoaded', () => {
    // Password visibility toggle
    document.querySelectorAll('[data-toggle="password"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = btn.querySelector('i');
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) {
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    }
                } else {
                    input.type = 'password';
                    if (icon) {
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                }
            }
        });
    });

    // Mobile sidebar toggle
    const sidebarToggle = document.getElementById('gfSidebarToggle');
    const sidebar = document.getElementById('gfSidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Close when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 992 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    }

    // Auto-dismiss alert toasts after 5s
    document.querySelectorAll('.gf-alert[data-auto-dismiss]').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.4s ease';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });

    // Global keyboard shortcut: Ctrl+K or / to focus search
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey && e.key === 'k') || (e.key === '/' && document.activeElement.tagName !== 'INPUT')) {
            const searchInput = document.querySelector('.gf-search-input');
            if (searchInput) {
                e.preventDefault();
                searchInput.focus();
            }
        }
    });
});
