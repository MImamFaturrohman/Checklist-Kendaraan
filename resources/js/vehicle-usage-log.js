/**
 * Log Penggunaan Kendaraan — wizard 1 langkah + modal preview.
 * Menggunakan initVulForm() + guard double-bind agar aman dengan Turbo Drive.
 */

function initVulForm() {
    const root = document.querySelector('[data-vehicle-usage-form]');
    if (!root || root.dataset.vulBound) return;
    root.dataset.vulBound = '1';

    /* ── Jenis kendaraan auto-fill ── */
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

    const form = document.getElementById('vehicle-usage-log-form');
    if (!form) return;

    const esc = (s) => {
        const d = document.createElement('div');
        d.textContent = s ?? '';
        return d.innerHTML;
    };

    const swalDialog = () => ({
        customClass: {
            popup: 'vul-swal-dialog',
            title: 'vul-swal-title',
            confirmButton: 'vul-swal-confirm',
            cancelButton: 'vul-swal-cancel',
        },
        buttonsStyling: false,
    });

    /* ── Slider BBM ── */
    const bindBbmPctSlider = (sliderId, hiddenId, displayId) => {
        const slider = document.getElementById(sliderId);
        const hidden = document.getElementById(hiddenId);
        const display = document.getElementById(displayId);
        if (!slider || !hidden) return;
        const sync = () => {
            const v = String(slider.value);
            hidden.value = v;
            if (display) display.innerHTML = `${v}<small>%</small>`;
            slider.style.background = `linear-gradient(to right, #facc15 ${v}%, #e5e7eb ${v}%)`;
        };
        slider.addEventListener('input', sync);
        sync();
    };
    bindBbmPctSlider('vul-bbm-slider-awal', 'vul-bbm-awal', 'vul-bbm-display-awal');
    bindBbmPctSlider('vul-bbm-slider-akhir', 'vul-bbm-akhir', 'vul-bbm-display-akhir');

    const submitBtn = document.getElementById('vul-submit');
    const reviewRoot = document.getElementById('vul-review-root');
    const submitHtml = submitBtn?.innerHTML ?? '';

    const val = (id) => {
        const el = document.getElementById(id);
        return el && 'value' in el ? String(el.value ?? '').trim() : '';
    };

    /* Semua field input ada di step 1, validasi sekaligus sebelum preview */
    const validateStep1 = () => {
        const errors = [];

        /* Data Penggunaan */
        if (!nomorSel?.value) errors.push('Pilih nomor kendaraan.');
        const ja = val('vul-jam-awal');
        const jb = val('vul-jam-akhir');
        if (!ja) errors.push('Jam awal wajib diisi.');
        if (!jb) errors.push('Jam selesai wajib diisi.');
        if (ja && jb && ja >= jb) errors.push('Jam selesai harus setelah jam awal (hari yang sama).');
        if (!val('vul-keperluan')) errors.push('Keperluan wajib diisi.');

        /* Level BBM & KM */
        const a = parseInt(val('vul-bbm-awal'), 10);
        const b = parseInt(val('vul-bbm-akhir'), 10);
        if (Number.isNaN(a) || a < 0 || a > 100) errors.push('Level BBM awal wajib antara 0% dan 100%.');
        if (Number.isNaN(b) || b < 0 || b > 100) errors.push('Level BBM akhir wajib antara 0% dan 100%.');
        const kmAwal = parseInt(val('vul-km-awal'), 10);
        const kmAkhir = parseInt(val('vul-km-akhir'), 10);
        if (Number.isNaN(kmAwal) || kmAwal < 0) errors.push('KM awal wajib diisi (angka valid).');
        if (Number.isNaN(kmAkhir) || kmAkhir < 0) errors.push('KM akhir wajib diisi (angka valid).');
        if (!Number.isNaN(kmAwal) && !Number.isNaN(kmAkhir) && kmAkhir < kmAwal) {
            errors.push('KM akhir harus lebih besar atau sama dengan KM awal.');
        }

        /* Kondisi Kendaraan */
        if (!val('vul-kondisi-sebelum')) errors.push('Kondisi sebelum penggunaan wajib diisi.');
        if (!val('vul-kondisi-sesudah')) errors.push('Kondisi setelah penggunaan wajib diisi.');

        return errors;
    };

    const validateAll = () => validateStep1();

    const buildReviewHtml = () => {
        const nama = val('vul-nama') || '—';
        const nopol = nomorSel?.options[nomorSel.selectedIndex]?.text?.trim() || '—';
        const jenis = jenisInp?.value || '—';
        const bbmA = val('vul-bbm-awal');
        const bbmB = val('vul-bbm-akhir');
        return `
<table class="info-table sppd-mini-table">
  <tr><td class="label">Nama</td><td>${esc(nama)}</td></tr>
  <tr><td class="label">No. Kendaraan</td><td>${esc(nopol)}</td></tr>
  <tr><td class="label">Jenis</td><td>${esc(jenis)}</td></tr>
  <tr><td class="label">Jam Penggunaan</td><td>${esc(val('vul-jam-awal'))} – ${esc(val('vul-jam-akhir'))}</td></tr>
  <tr><td class="label">Keperluan</td><td>${esc(val('vul-keperluan'))}</td></tr>
  <tr><td class="label">Level BBM Awal</td><td>${esc(bbmA)}%</td></tr>
  <tr><td class="label">Level BBM Akhir</td><td>${esc(bbmB)}%</td></tr>
  <tr><td class="label">KM Awal</td><td>${esc(val('vul-km-awal'))} KM</td></tr>
  <tr><td class="label">KM Akhir</td><td>${esc(val('vul-km-akhir'))} KM</td></tr>
  <tr><td class="label">Kondisi Sebelum</td><td>${esc(val('vul-kondisi-sebelum'))}</td></tr>
  <tr><td class="label">Kondisi Setelah</td><td>${esc(val('vul-kondisi-sesudah'))}</td></tr>
</table>`;
    };

    const refreshReview = () => {
        if (reviewRoot) reviewRoot.innerHTML = buildReviewHtml();
    };

    /* ── Modal Preview ── */
    const openPreviewModal = () => {
        refreshReview();
        const overlay = document.getElementById('vul-preview-overlay');
        if (!overlay) return;
        overlay.style.display = 'flex';
        // Trigger CSS animation
        overlay.classList.remove('active');
        void overlay.offsetWidth;
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        // Scroll modal body ke atas
        const body = overlay.querySelector('.vul-preview-modal-body');
        if (body) body.scrollTop = 0;
    };

    const closePreviewModal = () => {
        const overlay = document.getElementById('vul-preview-overlay');
        if (!overlay) return;
        overlay.classList.remove('active');
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    };

    const showErrors = (messages) => {
        const list = (Array.isArray(messages) ? messages : [messages]).filter(Boolean);
        if (typeof Swal === 'undefined') {
            window.alert(list.join('\n'));
            return Promise.resolve();
        }
        return Swal.fire({
            icon: 'error',
            iconColor: '#dc2626',
            title: 'Formulir belum valid',
            html:
                '<p class="vul-swal-lead">Mohon lengkapi isian terlebih dahulu:</p>' +
                '<div class="vul-swal-error-box"><ul class="vul-swal-list" style="margin:0">' +
                list.map((e) => '<li>' + esc(String(e)) + '</li>').join('') +
                '</ul></div>',
            confirmButtonText: 'Mengerti',
            allowEnterKey: false,
            returnFocus: false,
            ...swalDialog(),
        });
    };

    // Tombol "Lihat Preview" — validasi dulu, lalu buka modal
    const btnNext = document.getElementById('vul-next');
    btnNext?.addEventListener('click', async () => {
        const err = validateStep1();
        if (err.length) {
            await showErrors(err);
            return;
        }
        openPreviewModal();
    });

    // Tombol X di pojok kanan atas modal
    document.getElementById('vul-preview-close')?.addEventListener('click', closePreviewModal);

    // Tombol "Kembali ke Form" di footer modal
    document.getElementById('vul-preview-cancel')?.addEventListener('click', closePreviewModal);

    // Klik backdrop overlay (di luar modal)
    document.getElementById('vul-preview-overlay')?.addEventListener('click', (e) => {
        if (e.target.id === 'vul-preview-overlay') closePreviewModal();
    });

    // Tombol ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const overlay = document.getElementById('vul-preview-overlay');
            if (overlay && overlay.style.display !== 'none') closePreviewModal();
        }
    });

    // Progress bar — single step, selalu penuh
    const progressFill = document.getElementById('vul-progress-fill');
    const stepLabel    = document.getElementById('vul-step-label');
    const progressPct  = document.getElementById('vul-progress-pct');
    if (progressFill) progressFill.style.width = '100%';
    if (stepLabel) stepLabel.textContent = 'LENGKAPI SEMUA DATA';
    if (progressPct) progressPct.textContent = '';

    /* ── Submit (dari tombol di dalam modal) ── */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const allErr = validateAll();
        if (allErr.length) {
            await showErrors(allErr);
            return;
        }

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrf || typeof Swal === 'undefined') {
            form.submit();
            return;
        }

        const confirm = await Swal.fire({
            icon: 'question',
            iconColor: '#0b2c6b',
            title: 'Kirim log penggunaan?',
            text: 'Log akan disimpan dan dikirim ke admin. Lanjutkan?',
            showCancelButton: true,
            confirmButtonText: 'Ya, kirim',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            focusCancel: true,
            width: Math.min(420, window.innerWidth - 32),
            ...swalDialog(),
        });
        if (!confirm.isConfirmed) return;

        const dashUrl = form.dataset.dashboardUrl || '/dashboard';
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
            try { data = await res.json(); } catch { data = {}; }

            if (res.status === 422 && data.errors) {
                const msgs = Object.values(data.errors).flat();
                closePreviewModal();
                await showErrors(msgs.length ? msgs : [data.message || 'Data tidak valid.']);
                return;
            }

            if (res.status === 419) {
                closePreviewModal();
                await showErrors(['Sesi kedaluwarsa. Muat ulang halaman lalu kirim lagi.']);
                return;
            }

            if (res.ok && data.success) {
                closePreviewModal();
                await Swal.fire({
                    icon: 'success',
                    iconColor: '#16a34a',
                    title: 'Berhasil dikirim',
                    text: data.message || 'Log pemakaian kendaraan Anda sudah tersimpan.',
                    confirmButtonText: 'Kembali ke Dashboard',
                    ...swalDialog(),
                }).then((r) => {
                    if (r.isConfirmed) window.location.href = dashUrl;
                });
                return;
            }

            await Swal.fire({
                icon: 'error',
                title: 'Gagal mengirim',
                text: data.message || 'Tidak dapat menyimpan log. Coba lagi.',
                confirmButtonText: 'Tutup',
                ...swalDialog(),
            });
        } catch {
            await Swal.fire({
                icon: 'error',
                title: 'Koneksi bermasalah',
                text: 'Periksa jaringan lalu coba lagi.',
                confirmButtonText: 'Tutup',
                ...swalDialog(),
            });
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitHtml;
            }
        }
    });
}

document.addEventListener('turbo:load', initVulForm);
initVulForm();
