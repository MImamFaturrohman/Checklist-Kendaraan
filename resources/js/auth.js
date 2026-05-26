/* ── Theme toggle ─────────────────────────────────────────────────── */
(function () {
    const btn  = document.getElementById('theme-toggle');
    const icon = document.getElementById('theme-icon');
    const body = document.body;
    const root = document.documentElement;

    if (!btn || !icon) return;

    const DARK  = { g1: '#0A2342', g2: '#0f172a', g3: '#050B14' };
    const LIGHT = { g1: '#f1f5f9', g2: '#f8fafc', g3: '#e2e8f0' };

    function applyTheme(isLight) {
        const g = isLight ? LIGHT : DARK;
        root.style.setProperty('--grad-1', g.g1);
        root.style.setProperty('--grad-2', g.g2);
        root.style.setProperty('--grad-3', g.g3);
        body.classList.toggle('light-mode', isLight);
        icon.className = isLight ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
    }

    applyTheme(localStorage.getItem('vms-theme') === 'light');

    btn.addEventListener('click', function () {
        const next = !body.classList.contains('light-mode');
        applyTheme(next);
        localStorage.setItem('vms-theme', next ? 'light' : 'dark');
    });
})();

/* ── Login form: password toggle + submit loading ─────────────────── */
(function () {
    const loginForm = document.querySelector('[data-login-form]');
    if (!loginForm) return;

    const passwordInput  = loginForm.querySelector('#password');
    const passwordToggle = loginForm.querySelector('[data-password-toggle]');
    const passwordIcon   = loginForm.querySelector('[data-password-icon]');
    const submitButton   = loginForm.querySelector('[data-login-submit]');

    if (passwordInput && passwordToggle && passwordIcon) {
        passwordToggle.addEventListener('click', function () {
            const show = passwordInput.type === 'password';
            passwordInput.type = show ? 'text' : 'password';
            passwordIcon.classList.toggle('bi-eye', !show);
            passwordIcon.classList.toggle('bi-eye-slash', show);
        });
    }

    if (submitButton) {
        loginForm.addEventListener('submit', function () {
            submitButton.classList.add('is-loading');
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            loginForm.classList.add('auth-form--loading');
            submitButton.innerHTML =
                '<span class="auth-login-spinner" role="status" aria-label="Memuat"></span>'
                + '<span class="auth-btn-text">Memvalidasi\u2026</span>';
        });
    }
})();
