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
        html.style.colorScheme = isLight ? 'light' : 'dark';
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
    /* Resolve base URL dynamically from meta tag (same logic as app.js window.appBase) */
    const _authBase = (function () {
        const meta = document.querySelector('meta[name="app-base-url"]');
        return (meta ? meta.content : window.location.origin).replace(/\/$/, '');
    })();
    function appBase(path) {
        return _authBase + (path.startsWith('/') ? path : '/' + path);
    }

    const loginForm = document.querySelector('[data-login-form]');
    if (!loginForm) return;

    const passwordInput    = loginForm.querySelector('#password');
    const passwordToggle   = loginForm.querySelector('[data-password-toggle]');
    const passwordIcon     = loginForm.querySelector('[data-password-icon]');
    const submitButton     = loginForm.querySelector('[data-login-submit]');
    const errorSlot        = loginForm.querySelector('[data-login-error]');
    const usernameGroup    = loginForm.querySelector('#username-input-group');
    const passwordGroup    = loginForm.querySelector('#password-input-group');
    const emailGroup       = loginForm.querySelector('#email-input-group');

    // Toggle fields & buttons
    const usernameFieldGroup = loginForm.querySelector('#username-field-group');
    const passwordFieldGroup = loginForm.querySelector('#password-field-group');
    const emailFieldGroup    = loginForm.querySelector('#email-field-group');
    const toggleForgotBtn    = document.getElementById('toggle-forgot-password');
    const backToLoginBtn     = document.getElementById('back-to-login');
    const linkLanding        = document.getElementById('link-landing');
    
    // Inputs
    const usernameInput      = loginForm.querySelector('#username');
    const emailInput         = loginForm.querySelector('#email');

    // UI elements
    const subtitle           = document.getElementById('auth-subtitle');
    const statusAlert        = document.getElementById('status-alert');
    const statusAlertText    = document.getElementById('status-alert-text');
    const loginStatus        = document.getElementById('login-status');

    let currentMode = 'login'; // 'login' or 'forgot'

    function getSubmitOriginalHTML() {
        if (currentMode === 'login') {
            return '<i class="ph-bold ph-sign-in" aria-hidden="true" id="submit-icon"></i><span class="auth-btn-text" id="submit-text">Log in</span>';
        } else {
            return '<i class="ph-bold ph-paper-plane-tilt" aria-hidden="true" id="submit-icon"></i><span class="auth-btn-text" id="submit-text">Kirim Link Reset</span>';
        }
    }

    if (passwordInput && passwordToggle && passwordIcon) {
        passwordToggle.addEventListener('click', function () {
            const show = passwordInput.type === 'password';
            passwordInput.type = show ? 'text' : 'password';
            passwordIcon.classList.toggle('bi-eye', !show);
            passwordIcon.classList.toggle('bi-eye-slash', show);
        });
    }

    if (loginForm.dataset.isResetMode === 'true') {
        loginForm.addEventListener('submit', function () {
            const submitBtn = loginForm.querySelector('#login-submit');
            if (submitBtn) {
                submitBtn.classList.add('is-loading');
                submitBtn.disabled = true;
                submitBtn.setAttribute('aria-busy', 'true');
                loginForm.classList.add('auth-form--loading');
                submitBtn.innerHTML =
                    '<i class="ph-bold ph-spinner auth-login-spinner" role="status" aria-label="Memuat"></i>'
                    + '<span class="auth-btn-text">Memproses\u2026</span>';
            }
        });
        return;
    }

    function clearErrors() {
        if (errorSlot) { errorSlot.hidden = true; errorSlot.textContent = ''; }
        usernameGroup?.classList.remove('has-error');
        passwordGroup?.classList.remove('has-error');
        emailGroup?.classList.remove('has-error');
    }

    function showFormError(message) {
        if (errorSlot) {
            errorSlot.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> <span>' + message + '</span>';
            errorSlot.hidden = false;
        }
        if (currentMode === 'login') {
            passwordGroup?.classList.add('has-error');
        } else {
            emailGroup?.classList.add('has-error');
        }
    }

    function setLoading(loading) {
        if (!submitButton) return;
        if (loading) {
            submitButton.classList.add('is-loading');
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            loginForm.classList.add('auth-form--loading');
            
            const btnText = currentMode === 'login' ? 'Memverifikasi\u2026' : 'Mengirim\u2026';
            submitButton.innerHTML =
                '<i class="ph-bold ph-spinner auth-login-spinner" role="status" aria-label="Memuat"></i>'
                + '<span class="auth-btn-text">' + btnText + '</span>';
        } else {
            submitButton.classList.remove('is-loading');
            submitButton.disabled = false;
            submitButton.setAttribute('aria-busy', 'false');
            loginForm.classList.remove('auth-form--loading');
            submitButton.innerHTML = getSubmitOriginalHTML();
        }
    }

    function switchMode(mode) {
        currentMode = mode;
        clearErrors();
        
        // Hide standard session alert if present
        if (loginStatus) loginStatus.style.display = 'none';
        if (statusAlert) statusAlert.style.display = 'none';

        if (mode === 'forgot') {
            // Change form attributes
            loginForm.action = loginForm.dataset.forgotUrl;
            
            // Show/Hide fields
            usernameFieldGroup.style.display = 'none';
            passwordFieldGroup.style.display = 'none';
            emailFieldGroup.style.display = 'block';

            // Show/Hide helpers
            toggleForgotBtn.style.display = 'none';
            if (linkLanding) linkLanding.style.display = 'none';
            backToLoginBtn.style.display = 'inline-block';

            // Adjust input requirement
            usernameInput.required = false;
            passwordInput.required = false;
            emailInput.required = true;
            emailInput.focus();

            // Set UI text
            subtitle.textContent = 'Masukkan Email Reset Password';
            submitButton.innerHTML = getSubmitOriginalHTML();
        } else {
            // Change form attributes
            loginForm.action = loginForm.dataset.loginUrl;

            // Show/Hide fields
            usernameFieldGroup.style.display = 'block';
            passwordFieldGroup.style.display = 'block';
            emailFieldGroup.style.display = 'none';

            // Show/Hide helpers
            toggleForgotBtn.style.display = 'inline-block';
            if (linkLanding) linkLanding.style.display = 'inline-block';
            backToLoginBtn.style.display = 'none';

            // Adjust input requirement
            usernameInput.required = true;
            passwordInput.required = true;
            emailInput.required = false;
            usernameInput.focus();

            // Set UI text
            subtitle.textContent = 'Portal Kendaraan Operasional';
            submitButton.innerHTML = getSubmitOriginalHTML();
        }
    }

    if (toggleForgotBtn) {
        toggleForgotBtn.addEventListener('click', () => switchMode('forgot'));
    }

    if (backToLoginBtn) {
        backToLoginBtn.addEventListener('click', () => switchMode('login'));
    }

    /* Clear errors on input so feedback disappears the moment user types */
    usernameInput?.addEventListener('input', clearErrors);
    passwordInput?.addEventListener('input', clearErrors);
    emailInput?.addEventListener('input', clearErrors);

    if (submitButton) {
        loginForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            clearErrors();
            setLoading(true);

            if (statusAlert) statusAlert.style.display = 'none';

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
                    let msg = 'Terjadi kesalahan validasi.';
                    if (currentMode === 'login') {
                        msg = (data.errors?.username?.[0]) || (data.errors?.password?.[0]) || data.message || 'Username atau password yang Anda masukkan tidak sesuai.';
                    } else {
                        msg = (data.errors?.email?.[0]) || data.message || 'Email yang Anda masukkan tidak terdaftar.';
                    }
                    showFormError(msg);
                    return;
                }

                if (res.status === 419) {
                    showFormError('Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.');
                    return;
                }

                if (res.ok) {
                    const data = await res.json().catch(() => ({}));
                    if (currentMode === 'login') {
                        window.location.href = data.redirect || appBase('/dashboard');
                    } else {
                        // Success forgot password
                        if (statusAlert && statusAlertText) {
                            statusAlertText.textContent = data.message || 'Tautan reset password telah dikirim ke email Anda.';
                            statusAlert.style.display = 'flex';
                            emailInput.value = '';
                        }
                    }
                    return;
                }

                showFormError('Terjadi kesalahan. Coba lagi.');
            } catch {
                showFormError('Tidak dapat terhubung ke server. Periksa koneksi Anda.');
            } finally {
                setLoading(false);
            }
        });
    }
})();
