/* ── Theme toggle ─────────────────────────────────────────────────── */
(function () {
    const btn  = document.getElementById('theme-toggle');
    const icon = document.getElementById('theme-icon');
    const body = document.body;
    const html = document.documentElement;

    if (!btn || !icon) return;

    const DARK  = { g1: '#0A2342', g2: '#0f172a', g3: '#050B14' };
    const LIGHT = { g1: '#f1f5f9', g2: '#f8fafc', g3: '#e2e8f0' };

    function applyTheme(isLight) {
        const g = isLight ? LIGHT : DARK;
        html.style.setProperty('--grad-1', g.g1);
        html.style.setProperty('--grad-2', g.g2);
        html.style.setProperty('--grad-3', g.g3);
        body.classList.toggle('light-mode', isLight);
        /* sync html.dark so the FOUC rule stays consistent */
        html.classList.toggle('dark', !isLight);
        icon.className = isLight ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
    }

    applyTheme(localStorage.getItem('vms-theme') === 'light');

    btn.addEventListener('click', function () {
        const next = !body.classList.contains('light-mode');
        applyTheme(next);
        localStorage.setItem('vms-theme', next ? 'light' : 'dark');
    });
})();

/* ── Login form: password toggle + AJAX submit + inline error ────── */
(function () {
    const loginForm = document.querySelector('[data-login-form]');
    if (!loginForm) return;

    const passwordInput    = loginForm.querySelector('#password');
    const passwordToggle   = loginForm.querySelector('[data-password-toggle]');
    const passwordIcon     = loginForm.querySelector('[data-password-icon]');
    const submitButton     = loginForm.querySelector('[data-login-submit]');
    const errorSlot        = loginForm.querySelector('[data-login-error]');
    const usernameGroup    = loginForm.querySelector('#username-input-group');
    const passwordGroup    = loginForm.querySelector('#password-input-group');

    const submitOriginalHTML = submitButton
        ? '<span class="auth-btn-text">Sign in</span><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>'
        : '';

    if (passwordInput && passwordToggle && passwordIcon) {
        passwordToggle.addEventListener('click', function () {
            const show = passwordInput.type === 'password';
            passwordInput.type = show ? 'text' : 'password';
            passwordIcon.classList.toggle('bi-eye', !show);
            passwordIcon.classList.toggle('bi-eye-slash', show);
        });
    }

    function clearLoginError() {
        if (errorSlot) { errorSlot.hidden = true; errorSlot.textContent = ''; }
        usernameGroup?.classList.remove('has-error');
        passwordGroup?.classList.remove('has-error');
    }

    function showLoginError(message) {
        if (errorSlot) {
            errorSlot.innerHTML = '<i class="bi bi-exclamation-circle"></i> ' + message;
            errorSlot.hidden = false;
        }
        passwordGroup?.classList.add('has-error');
    }

    function setLoading(loading) {
        if (!submitButton) return;
        if (loading) {
            submitButton.classList.add('is-loading');
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            loginForm.classList.add('auth-form--loading');
            submitButton.innerHTML =
                '<span class="auth-login-spinner" role="status" aria-label="Memuat"></span>'
                + '<span class="auth-btn-text">Memvalidasi\u2026</span>';
        } else {
            submitButton.classList.remove('is-loading');
            submitButton.disabled = false;
            submitButton.setAttribute('aria-busy', 'false');
            loginForm.classList.remove('auth-form--loading');
            submitButton.innerHTML = submitOriginalHTML;
        }
    }

    /* Clear errors on input so feedback disappears the moment user types */
    loginForm.querySelector('#username')?.addEventListener('input', clearLoginError);
    loginForm.querySelector('#password')?.addEventListener('input', clearLoginError);

    if (submitButton) {
        loginForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            clearLoginError();
            setLoading(true);

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

            try {
                const res = await fetch(loginForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf || '',
                    },
                    body: new FormData(loginForm),
                });

                if (res.status === 422) {
                    const data = await res.json();
                    const msg =
                        (data.errors?.username?.[0]) ||
                        (data.errors?.password?.[0]) ||
                        data.message ||
                        'Username atau password yang Anda masukkan tidak sesuai.';
                    showLoginError(msg);
                    return;
                }

                if (res.status === 419) {
                    showLoginError('Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.');
                    return;
                }

                if (res.ok) {
                    const data = await res.json().catch(() => ({}));
                    window.location.href = data.redirect || '/dashboard';
                    return;
                }

                showLoginError('Terjadi kesalahan. Coba lagi.');
            } catch {
                showLoginError('Tidak dapat terhubung ke server. Periksa koneksi Anda.');
            } finally {
                setLoading(false);
            }
        });
    }
})();
