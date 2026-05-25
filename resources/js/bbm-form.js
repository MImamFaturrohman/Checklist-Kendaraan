/**
 * Form laporan BBM (driver): wizard 3 langkah, preview foto, kalkulasi total, AJAX submit.
 */
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-bbm-form]');
    if (!root) return;

    const applyTheme = (isDark) => {
        document.body.classList.toggle('dark', isDark);
        const themeIcon = document.getElementById('dash-theme-icon');
        const themeLabel = document.getElementById('dash-theme-label');
        if (themeIcon) themeIcon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        if (themeLabel) themeLabel.textContent = isDark ? 'Light Mode' : 'Dark Mode';
    };

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
                            {
                                type: 'image/jpeg',
                                lastModified: Date.now(),
                            },
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

            const originalFile = input.files[0];

            const compressedFile = await compressImage(originalFile);

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
        if (removeBtn)
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
    };
    root.querySelectorAll('[data-photo-preview-slot]').forEach(initPhotoSlot);

    const nomorSel = document.getElementById('bbm-nopol');
    const jenisInp = document.getElementById('bbm-jenis');
    if (nomorSel && jenisInp) {
        const syncJenis = () => {
            const opt = nomorSel.options[nomorSel.selectedIndex];
            jenisInp.value = opt?.dataset?.jenis || '';
        };
        nomorSel.addEventListener('change', syncJenis);
        syncJenis();
    }

    const literInp = document.getElementById('bbm-liter');
    const hplInp = document.getElementById('bbm-harga-per-liter');
    const totalOut = document.getElementById('bbm-total-display');

    const formatRp = (n) =>
        'Rp ' +
        (Number.isFinite(n) ? n : 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });

    const recalcTotal = () => {
        if (!totalOut) return;
        const L = parseFloat(literInp?.value) || 0;
        const p = parseFloat(hplInp?.value) || 0;
        totalOut.value = formatRp(Math.round(L * p));
    };

    literInp?.addEventListener('input', recalcTotal);
    hplInp?.addEventListener('input', recalcTotal);
    recalcTotal();

    const body = document.body;
    const navActions = document.getElementById('dash-nav-actions');
    const menuBtn = document.getElementById('dash-mobile-menu-btn');
    const menuIcon = document.getElementById('dash-mobile-menu-icon');

    const saved = localStorage.getItem('vms-theme') || localStorage.getItem('vms-dash-theme');
    applyTheme(saved === 'dark');

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
    }, { passive: true });

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

    const validateStep1 = () => {
        const errors = [];
        if (!nomorSel?.value) errors.push('Pilih nomor kendaraan.');
        if (!val('bbm-jenis-pengisian')) errors.push('Pilih keperluan pengisian BBM.');
        if (!val('bbm-tanggal')) errors.push('Tanggal wajib diisi.');
        if (!val('bbm-waktu')) errors.push('Waktu wajib diisi.');
        return errors;
    };

    const validateStep2 = () => {
        const errors = [];
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

        const fotoOdo = document.getElementById('bbm-foto-odometer');
        const fotoStruk = document.getElementById('bbm-foto-struk');
        if (!fotoOdo?.files?.length) errors.push('Foto odometer wajib diunggah.');
        if (!fotoStruk?.files?.length) errors.push('Foto struk pembelian wajib diunggah.');
        return errors;
    };

    const validateAll = () => [...validateStep1(), ...validateStep2()];

    const reviewRoot = document.getElementById('bbm-review-root');

    const previewSrcForInput = (inputId) => {
        const inp = document.getElementById(inputId);
        if (!inp) return '';
        const slot = inp.closest('[data-photo-preview-slot]');
        const img = slot?.querySelector('.photo-slot-preview');
        if (img && img.style.display !== 'none' && img.src) return img.src;
        return '';
    };

    const buildReviewHtml = () => {
        const nopol = nomorSel?.options[nomorSel.selectedIndex]?.text?.trim() || '—';
        const jenis = jenisInp?.value || '—';
        const keperluanSel = document.getElementById('bbm-jenis-pengisian');
        const keperluan =
            keperluanSel?.options[keperluanSel.selectedIndex]?.text?.trim() || val('bbm-jenis-pengisian') || '—';
        const shift = val('bbm-shift-label') || '—';
        const tanggal = val('bbm-tanggal') || '—';
        const waktu = val('bbm-waktu') || '—';
        const totalText = totalOut?.value || 'Rp 0';

        const srcOdo = previewSrcForInput('bbm-foto-odometer');
        const srcStruk = previewSrcForInput('bbm-foto-struk');
        const imgOdo =
            srcOdo ?
                `<div class="bbm-review-photo-wrap"><img src="${srcOdo}" alt="Foto odometer"><div class="bbm-review-photo-caption">Foto odometer</div></div>`
            :   '<p style="margin:0;font-size:0.82rem;color:#94a3b8">Foto odometer belum dipilih.</p>';
        const imgStruk =
            srcStruk ?
                `<div class="bbm-review-photo-wrap"><img src="${srcStruk}" alt="Foto struk"><div class="bbm-review-photo-caption">Foto struk</div></div>`
            :   '<p style="margin:0;font-size:0.82rem;color:#94a3b8">Foto struk belum dipilih.</p>';

        return `
<div class="bbm-review-group">
  <h4>Data kendaraan &amp; waktu</h4>
  <dl class="bbm-review-dl">
    <div><dt>Kendaraan</dt><dd>${esc(nopol)}</dd></div>
    <div><dt>Jenis</dt><dd>${esc(jenis)}</dd></div>
    <div><dt>Keperluan pengisian</dt><dd>${esc(keperluan)}</dd></div>
    <div><dt>Shift</dt><dd>${esc(shift)}</dd></div>
    <div><dt>Tanggal</dt><dd>${esc(tanggal)}</dd></div>
    <div><dt>Waktu</dt><dd>${esc(waktu)}</dd></div>
  </dl>
</div>
<div class="bbm-review-group">
  <h4>Pengisian BBM</h4>
  <dl class="bbm-review-dl">
    <div><dt>KM sebelum</dt><dd>${esc(val('bbm-odo-sebelum') || '—')}</dd></div>
    <div><dt>KM sesudah</dt><dd>${esc(val('bbm-odo-sesudah') || '—')}</dd></div>
    <div><dt>Liter</dt><dd>${esc(val('bbm-liter') || '—')}</dd></div>
    <div><dt>Harga / L</dt><dd>${esc(val('bbm-harga-per-liter') || '—')}</dd></div>
    <div><dt>Total harga</dt><dd>${esc(totalText)}</dd></div>
  </dl>
  <div class="bbm-review-photos">${imgOdo}${imgStruk}</div>
</div>`;
    };

    const refreshReview = () => {
        if (reviewRoot) reviewRoot.innerHTML = buildReviewHtml();
    };

    const steps = bbmForm ? Array.from(bbmForm.querySelectorAll('.wizard-step[data-step]')) : [];
    const btnPrev = document.getElementById('bbm-prev');
    const btnNext = document.getElementById('bbm-next');
    const progressFill = document.getElementById('bbm-progress-fill');
    const stepLabel = document.getElementById('bbm-step-label');
    const progressPct = document.getElementById('bbm-progress-pct');

    let currentStep = 1;
    const totalSteps = steps.length || 3;

    const showStep = (n) => {
        currentStep = n;
        steps.forEach((s) => {
            s.classList.toggle('active', +s.dataset.step === n);
        });
        const pct = Math.round((n / totalSteps) * 100);
        if (progressFill) progressFill.style.width = `${pct}%`;
        if (stepLabel) stepLabel.textContent = `LANGKAH ${n} DARI ${totalSteps}`;
        if (progressPct) progressPct.textContent = `${pct}%`;
        if (btnPrev) btnPrev.disabled = n <= 1;
        if (btnNext) {
            const hideNext = n >= totalSteps;
            btnNext.classList.toggle('bbm-next--hidden', hideNext);
            btnNext.setAttribute('aria-hidden', hideNext ? 'true' : 'false');
        }
        if (bbmSubmitBtn) {
            const hideSubmit = n !== totalSteps;
            bbmSubmitBtn.classList.toggle('bbm-submit--hidden', hideSubmit);
            bbmSubmitBtn.setAttribute('aria-hidden', hideSubmit ? 'true' : 'false');
        }
        if (n === totalSteps) refreshReview();
    };

    btnNext?.addEventListener('click', async () => {
        let err = [];
        if (currentStep === 1) err = validateStep1();
        else if (currentStep === 2) err = validateStep2();
        if (err.length) {
            await showBbmValidationErrors(err);
            return;
        }
        showStep(currentStep + 1);
    });

    btnPrev?.addEventListener('click', () => {
        if (currentStep > 1) showStep(currentStep - 1);
    });

    showStep(1);

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
            try {
                data = await res.json();
            } catch {
                data = {};
            }

            if (res.status === 422 && data.errors) {
                const msgs = Object.values(data.errors).flat();
                await showBbmValidationErrors(msgs.length ? msgs : [data.message || 'Data tidak valid.']);
                return;
            }

            if (res.status === 419) {
                await showBbmValidationErrors(['Sesi kedaluwarsa. Muat ulang halaman lalu kirim lagi.']);
                return;
            }

            if (res.ok && data.success) {
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
});
