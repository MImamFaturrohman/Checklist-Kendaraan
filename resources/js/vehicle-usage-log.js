document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-vehicle-usage-form]');
    if (!root) return;

    const nomorSel = document.getElementById('vul-nopol');
    const jenisInp = document.getElementById('vul-jenis');
    if (nomorSel && jenisInp) {
        const syncJenis = () => {
            const opt = nomorSel.options[nomorSel.selectedIndex];
            jenisInp.value = opt?.dataset?.jenis || '';
        };
        nomorSel.addEventListener('change', syncJenis);
        syncJenis();
    }

    const body = document.body;
    const themeBtn = document.getElementById('dash-theme-toggle');
    const themeIcon = document.getElementById('dash-theme-icon');
    const themeLabel = document.getElementById('dash-theme-label');
    const navActions = document.getElementById('dash-nav-actions');
    const menuBtn = document.getElementById('dash-mobile-menu-btn');
    const menuIcon = document.getElementById('dash-mobile-menu-icon');

    const applyTheme = (isDark) => {
        body.classList.toggle('dark', isDark);
        if (themeIcon) themeIcon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        if (themeLabel) themeLabel.textContent = isDark ? 'Light Mode' : 'Dark Mode';
    };
    const saved = localStorage.getItem('vms-theme') || localStorage.getItem('vms-dash-theme');
    applyTheme(saved === 'dark');
    themeBtn?.addEventListener('click', () => {
        const next = !body.classList.contains('dark');
        applyTheme(next);
        localStorage.setItem('vms-theme', next ? 'dark' : 'light');
        localStorage.setItem('vms-dash-theme', next ? 'dark' : 'light');
    });

    const closeMobileMenu = () => {
        navActions?.classList.remove('mobile-open');
        if (menuIcon) menuIcon.className = 'bi bi-list';
        menuBtn?.setAttribute('aria-expanded', 'false');
    };
    menuBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        const opened = navActions?.classList.toggle('mobile-open');
        if (menuIcon) menuIcon.className = opened ? 'bi bi-x-lg' : 'bi bi-list';
        menuBtn?.setAttribute('aria-expanded', String(!!opened));
    });
    document.addEventListener('click', (e) => {
        if (!navActions?.contains(e.target) && !menuBtn?.contains(e.target)) closeMobileMenu();
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) closeMobileMenu();
    });

    const form = document.getElementById('vehicle-usage-log-form');
    const submitBtn = form?.querySelector('button[type="submit"]');
    const submitHtml = submitBtn?.innerHTML ?? '';

    const showErrors = (messages) => {
        const esc = (s) => {
            const d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        };
        const list = (Array.isArray(messages) ? messages : [messages]).filter(Boolean);
        if (typeof Swal === 'undefined') {
            window.alert(list.join('\n'));
            return Promise.resolve();
        }
        return Swal.fire({
            icon: 'error',
            title: 'Data belum valid',
            html:
                '<ul style="text-align:left;margin:0;padding-left:1.2rem">' +
                list.map((e) => '<li>' + esc(String(e)) + '</li>').join('') +
                '</ul>',
            confirmButtonText: 'Perbaiki',
            allowEnterKey: false,
            returnFocus: false,
        });
    };

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrf || typeof Swal === 'undefined') {
            form.submit();
            return;
        }

        const dashUrl = form.dataset.dashboardUrl || '/dashboard';

        const confirm = await Swal.fire({
            icon: 'question',
            title: 'Kirim log penggunaan?',
            text: 'Pastikan no. kendaraan, jam, dan keperluan sudah benar.',
            showCancelButton: true,
            confirmButtonText: 'Ya, kirim',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            focusCancel: true,
        });
        if (!confirm.isConfirmed) return;

        const fd = new FormData(form);
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Memproses…</span>';
        }

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
                body: fd,
            });

            let data = {};
            try {
                data = await res.json();
            } catch {
                data = {};
            }

            if (res.status === 422 && data.errors) {
                const msgs = Object.values(data.errors).flat();
                await showErrors(msgs.length ? msgs : [data.message || 'Data tidak valid.']);
                return;
            }

            if (res.status === 419) {
                await showErrors(['Sesi kedaluwarsa. Muat ulang halaman lalu kirim lagi.']);
                return;
            }

            if (res.ok && data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: data.message || 'Log tersimpan.',
                    confirmButtonText: 'Kembali ke Dashboard',
                }).then((r) => {
                    if (r.isConfirmed) window.location.href = dashUrl;
                });
                return;
            }

            await Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.message || 'Tidak dapat menyimpan log.',
                confirmButtonText: 'Tutup',
            });
        } catch {
            await Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Koneksi bermasalah. Periksa jaringan lalu coba lagi.',
                confirmButtonText: 'Tutup',
            });
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitHtml;
            }
        }
    });
});
