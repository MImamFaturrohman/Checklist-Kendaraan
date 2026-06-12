/**
 * Form laporan BBM 
 */

function initBbmForm() {
    const root = document.querySelector('[data-bbm-form]');
    if (!root || root.dataset.bbmBound) return;
    root.dataset.bbmBound = '1';

    async function compressImage(file, quality = 0.8, maxWidth = 1920) {
        return new Promise((resolve) => {
            const img = new Image();
            const reader = new FileReader();

            reader.onload = (e) => {
                img.src = e.target.result;
            };

            img.onload = () => {
                let width = img.width;
                let height = img.height;

                if (width > maxWidth) {
                    height *= maxWidth / width;
                    width = maxWidth;
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(
                    (blob) => {
                        const compressedFile = new File(
                            [blob],
                            file.name.replace(/\.\w+$/, '.jpg'),
                            { type: 'image/jpeg', lastModified: Date.now() },
                        );
                        resolve(compressedFile);
                    },
                    'image/jpeg',
                    quality,
                );
            };

            reader.readAsDataURL(file);
        });
    }

    /** Normalisasi foto odometer: max lebar 1280px, max tinggi 960px, ratio asli. */
    async function compressOdometerImage(file, quality = 0.82, maxWidth = 1280, maxHeight = 960) {
        return new Promise((resolve) => {
            const img = new Image();
            const reader = new FileReader();

            reader.onload = (e) => {
                img.src = e.target.result;
            };

            img.onload = () => {
                let width = img.width;
                let height = img.height;

                const scale = Math.min(maxWidth / width, maxHeight / height, 1);
                width = Math.round(width * scale);
                height = Math.round(height * scale);

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(
                    (blob) => {
                        const compressedFile = new File(
                            [blob],
                            file.name.replace(/\.\w+$/, '.jpg'),
                            { type: 'image/jpeg', lastModified: Date.now() },
                        );
                        resolve(compressedFile);
                    },
                    'image/jpeg',
                    quality,
                );
            };

            reader.readAsDataURL(file);
        });
    }

    const initPhotoSlot = (slot) => {
        const input = slot.querySelector('[data-photo-single]');
        const preview = slot.querySelector('.photo-slot-preview');
        const placeholder = slot.querySelector('.photo-slot-placeholder');
        const removeBtn = slot.querySelector('.photo-slot-remove');
        if (!input || !preview) return;
        input.setAttribute('capture', 'environment');
        input.setAttribute('accept', 'image/*');

        input.addEventListener('change', async () => {
            if (!input.files?.[0]) return;

            const isOdometer =
                input.id === 'bbm-foto-odometer-sebelum' || input.id === 'bbm-foto-odometer-sesudah';
            const compressedFile = isOdometer
                ? await compressOdometerImage(input.files[0])
                : await compressImage(input.files[0]);

            const dt = new DataTransfer();
            dt.items.add(compressedFile);
            input.files = dt.files;

            const reader = new FileReader();
            reader.onload = () => {
                preview.src = reader.result;
                preview.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
                if (removeBtn) removeBtn.style.display = 'flex';
                slot.classList.add('has-file');
            };
            reader.readAsDataURL(compressedFile);
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                input.value = '';
                preview.style.display = 'none';
                preview.src = '';
                if (placeholder) placeholder.style.display = 'flex';
                removeBtn.style.display = 'none';
                slot.classList.remove('has-file');
            });
        }
    };
    root.querySelectorAll('[data-photo-preview-slot]').forEach(initPhotoSlot);

    /* ── Jenis kendaraan auto-fill ── */
    const nomorSel = document.getElementById('bbm-nopol');
    const jenisInp = document.getElementById('bbm-jenis');
    const odoSebelumInp = document.getElementById('bbm-odo-sebelum');
    if (nomorSel && jenisInp) {
        const syncJenis = () => {
            const opt = nomorSel.options[nomorSel.selectedIndex];
            jenisInp.value = opt?.dataset?.jenis || '';
            if (odoSebelumInp?.dataset?.kmCurrentHint === '1') {
                const kmCurrent = opt?.dataset?.kmCurrent || '';
                odoSebelumInp.placeholder = kmCurrent ? `${kmCurrent}` : 'Sebelum';
            }
        };
        nomorSel.addEventListener('change', syncJenis);
        syncJenis();
    }

    /* ── Total harga ── */
    const literInp = document.getElementById('bbm-liter');
    const hplInp = document.getElementById('bbm-harga-per-liter');
    const totalOut = document.getElementById('bbm-total-display');

    const formatRp = (n) =>
        'Rp ' + (Number.isFinite(n) ? n : 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });

    const recalcTotal = () => {
        if (!totalOut) return;
        const L = parseFloat(literInp?.value) || 0;
        const p = parseFloat(hplInp?.value) || 0;
        totalOut.value = formatRp(Math.round(L * p));
    };

    literInp?.addEventListener('input', recalcTotal);
    hplInp?.addEventListener('input', recalcTotal);
    recalcTotal();

    /* ── Form + submit ── */
    const bbmForm = document.getElementById('bbm-report-form');
    const bbmSubmitBtn = document.getElementById('bbm-submit');
    const bbmSubmitHtml = bbmSubmitBtn?.innerHTML ?? '';

    const esc = (s) => {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    };

    const val = (id) => {
        const el = document.getElementById(id);
        return el && 'value' in el ? String(el.value ?? '').trim() : '';
    };

    const showBbmValidationErrors = (messages) => {
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
            confirmButtonText: 'Perbaiki form',
            allowEnterKey: false,
            returnFocus: false,
            didOpen: (popup) => {
                const btn = popup.querySelector('.swal2-confirm');
                if (btn) btn.setAttribute('type', 'button');
            },
        });
    };

    /* Validasi semua field sebelum membuka preview */
    const validateStep1 = () => {
        const errors = [];

        /* Data Kendaraan */
        if (!nomorSel?.value) errors.push('Pilih nomor kendaraan.');
        if (!val('bbm-jenis-pengisian')) errors.push('Pilih keperluan pengisian BBM.');
        if (!val('bbm-tanggal')) errors.push('Tanggal wajib diisi.');
        if (!val('bbm-waktu')) errors.push('Waktu wajib diisi.');

        /* Data Pengisian BBM */
        const seb = val('bbm-odo-sebelum');
        const ses = val('bbm-odo-sesudah');
        const sebN = parseInt(seb, 10);
        const sesN = parseInt(ses, 10);
        if (seb === '' || Number.isNaN(sebN) || sebN < 0) errors.push('KM sebelum wajib diisi (bilangan bulat ≥ 0).');
        if (ses === '' || Number.isNaN(sesN) || sesN < 0) errors.push('KM sesudah wajib diisi (bilangan bulat ≥ 0).');
        if (!Number.isNaN(sebN) && !Number.isNaN(sesN) && sesN < sebN) {
            errors.push('KM sesudah harus sama atau lebih besar daripada KM sebelum.');
        }
        const L = parseFloat(val('bbm-liter'));
        const h = parseFloat(val('bbm-harga-per-liter'));
        if (!Number.isFinite(L) || L < 0.001) errors.push('Liter wajib diisi (minimal 0,001 L).');
        if (!Number.isFinite(h) || h < 0) errors.push('Harga per liter wajib diisi (tidak boleh negatif).');

        /* Foto */
        const fotoOdoSeb = document.getElementById('bbm-foto-odometer-sebelum');
        const fotoOdoSes = document.getElementById('bbm-foto-odometer-sesudah');
        const fotoStruk = document.getElementById('bbm-foto-struk');
        if (!fotoOdoSeb?.files?.length) errors.push('Foto odometer sebelum pengisian wajib diunggah.');
        if (!fotoOdoSes?.files?.length) errors.push('Foto odometer sesudah pengisian wajib diunggah.');
        if (!fotoStruk?.files?.length) errors.push('Foto struk pembelian wajib diunggah.');

        return errors;
    };

    const validateAll = () => validateStep1();

    /* ── Review (ringkasan) ── */
    const reviewRoot = document.getElementById('bbm-review-root');

    const previewSrcForInput = (inputId) => {
        const inp = document.getElementById(inputId);
        if (!inp) return '';
        const slot = inp.closest('[data-photo-preview-slot]');
        const img = slot?.querySelector('.photo-slot-preview');
        if (img && img.style.display !== 'none' && img.src) return img.src;
        return '';
    };

    const photoCard = (src, caption) =>
        src
            ? `<div class="bbm-review-photo-wrap"><img src="${src}" alt="${esc(caption)}"><div class="bbm-review-photo-caption">${esc(caption)}</div></div>`
            : `<p style="margin:0;font-size:0.82rem;color:#94a3b8">${esc(caption)} belum dipilih.</p>`;

    const buildReviewHtml = () => {
        const nopol = nomorSel?.options[nomorSel.selectedIndex]?.text?.trim() || '—';
        const jenis = jenisInp?.value || '—';
        const keperluanSel = document.getElementById('bbm-jenis-pengisian');
        const keperluan =
            keperluanSel?.options[keperluanSel.selectedIndex]?.text?.trim() || val('bbm-jenis-pengisian') || '—';
        const tanggal = val('bbm-tanggal') || '—';
        const waktu = val('bbm-waktu') || '—';
        const totalText = totalOut?.value || 'Rp 0';

        const srcOdoSeb = previewSrcForInput('bbm-foto-odometer-sebelum');
        const srcOdoSes = previewSrcForInput('bbm-foto-odometer-sesudah');
        const srcStruk = previewSrcForInput('bbm-foto-struk');

        return `
<table class="info-table sppd-mini-table">
  <tr><td class="label">Kendaraan</td><td>${esc(nopol)}</td></tr>
  <tr><td class="label">Jenis</td><td>${esc(jenis)}</td></tr>
  <tr><td class="label">Keperluan</td><td>${esc(keperluan)}</td></tr>
  <tr><td class="label">Tanggal</td><td>${esc(tanggal)}</td></tr>
  <tr><td class="label">Waktu</td><td>${esc(waktu)}</td></tr>
  <tr><td class="label">KM Sebelum</td><td>${esc(val('bbm-odo-sebelum') || '—')}</td></tr>
  <tr><td class="label">KM Sesudah</td><td>${esc(val('bbm-odo-sesudah') || '—')}</td></tr>
  <tr><td class="label">Liter</td><td>${esc(val('bbm-liter') || '—')} L</td></tr>
  <tr><td class="label">Harga / Liter</td><td>${esc(val('bbm-harga-per-liter') || '—')}</td></tr>
  <tr><td class="label">Total Harga</td><td><strong>${esc(totalText)}</strong></td></tr>
</table>
<div class="bbm-review-photos" style="margin-top:14px">
  ${photoCard(srcOdoSeb, 'Odometer sebelum')}${photoCard(srcOdoSes, 'Odometer sesudah')}
</div>
<div class="bbm-review-photos bbm-review-photos--struk">
  ${photoCard(srcStruk, 'Struk pembelian')}
</div>`;
    };

    const refreshReview = () => {
        if (reviewRoot) reviewRoot.innerHTML = buildReviewHtml();
    };

    /* ── Modal Preview ── */
    const openPreviewModal = () => {
        refreshReview();
        const overlay = document.getElementById('bbm-preview-overlay');
        if (!overlay) return;
        overlay.style.display = 'flex';
        // Trigger CSS animation
        overlay.classList.remove('active');
        void overlay.offsetWidth;
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        // Scroll modal body ke atas
        const body = overlay.querySelector('.bbm-preview-modal-body');
        if (body) body.scrollTop = 0;
    };

    const closePreviewModal = () => {
        const overlay = document.getElementById('bbm-preview-overlay');
        if (!overlay) return;
        overlay.classList.remove('active');
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    };

    // Tombol "Lihat Preview" — validasi dulu, lalu buka modal
    const btnNext = document.getElementById('bbm-next');
    btnNext?.addEventListener('click', async () => {
        const err = validateStep1();
        if (err.length) {
            await showBbmValidationErrors(err);
            return;
        }
        openPreviewModal();
    });

    // Tombol X di pojok kanan atas modal
    document.getElementById('bbm-preview-close')?.addEventListener('click', closePreviewModal);

    // Tombol "Kembali ke Form" di footer modal
    document.getElementById('bbm-preview-cancel')?.addEventListener('click', closePreviewModal);

    // Klik backdrop overlay (di luar modal)
    document.getElementById('bbm-preview-overlay')?.addEventListener('click', (e) => {
        if (e.target.id === 'bbm-preview-overlay') closePreviewModal();
    });

    // Tombol ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const overlay = document.getElementById('bbm-preview-overlay');
            if (overlay && overlay.style.display !== 'none') closePreviewModal();
        }
    });

    // Progress bar — single step, selalu penuh
    const progressFill = document.getElementById('bbm-progress-fill');
    const stepLabel    = document.getElementById('bbm-step-label');
    const progressPct  = document.getElementById('bbm-progress-pct');
    if (progressFill) progressFill.style.width = '100%';
    if (stepLabel) stepLabel.textContent = 'LENGKAPI SEMUA DATA';
    if (progressPct) progressPct.textContent = '';

    /* ── Submit (dari tombol di dalam modal) ── */
    bbmForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const errs = validateAll();
        if (errs.length) {
            await showBbmValidationErrors(errs);
            return;
        }

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrf || typeof Swal === 'undefined') {
            bbmForm.submit();
            return;
        }

        const confirm = await Swal.fire({
            icon: 'question',
            iconColor: '#0b2c6b',
            title: 'Kirim laporan BBM?',
            text: 'Laporan akan disimpan dan dikirim ke admin. Lanjutkan?',
            showCancelButton: true,
            confirmButtonText: 'Ya, kirim',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            focusCancel: true,
            width: Math.min(420, window.innerWidth - 32),
        });
        if (!confirm.isConfirmed) return;

        const dashUrl = bbmForm.dataset.dashboardUrl || '/dashboard';
        const fd = new FormData(bbmForm);

        if (bbmSubmitBtn) {
            bbmSubmitBtn.disabled = true;
            bbmSubmitBtn.innerHTML = '<span>Memproses…</span>';
        }

        try {
            const res = await fetch(bbmForm.action, {
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
                await showBbmValidationErrors(msgs.length ? msgs : [data.message || 'Data tidak valid.']);
                return;
            }

            if (res.status === 419) {
                closePreviewModal();
                await showBbmValidationErrors(['Sesi kedaluwarsa. Muat ulang halaman lalu kirim lagi.']);
                return;
            }

            if (res.ok && data.success) {
                closePreviewModal();
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: data.message || 'Laporan tersimpan.',
                    confirmButtonText: 'Kembali ke Dashboard',
                }).then((r) => {
                    if (r.isConfirmed) window.location.href = dashUrl;
                });
                return;
            }

            await Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.message || 'Tidak dapat menyimpan laporan.',
                confirmButtonText: 'Tutup',
                allowEnterKey: false,
                returnFocus: false,
            });
        } catch {
            await Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Koneksi bermasalah. Periksa jaringan lalu coba lagi.',
                confirmButtonText: 'Tutup',
                allowEnterKey: false,
                returnFocus: false,
            });
        } finally {
            if (bbmSubmitBtn) {
                bbmSubmitBtn.disabled = false;
                bbmSubmitBtn.innerHTML = bbmSubmitHtml;
            }
        }
    });
}

document.addEventListener('turbo:load', initBbmForm);
initBbmForm();
