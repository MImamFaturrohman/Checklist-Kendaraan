/**
 * Form laporan BBM (driver): preview foto + kalkulasi total.
 */
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-bbm-form]');
    if (!root) return;

    const initPhotoSlot = (slot) => {
        const input = slot.querySelector('[data-photo-single]');
        const preview = slot.querySelector('.photo-slot-preview');
        const placeholder = slot.querySelector('.photo-slot-placeholder');
        const removeBtn = slot.querySelector('.photo-slot-remove');
        if (!input || !preview) return;
        input.setAttribute('capture', 'environment');
        input.setAttribute('accept', 'image/*');

        const ensureCameraModal = () => {
            let modal = document.getElementById('camera-capture-modal');
            if (modal) return modal;

            modal = document.createElement('div');
            modal.id = 'camera-capture-modal';
            modal.style.cssText =
                'position:fixed;inset:0;background:rgba(0,0,0,.72);display:none;align-items:center;justify-content:center;z-index:10000;padding:16px;';
            modal.innerHTML = `
                <div style="width:min(520px,100%);background:#0f172a;border-radius:16px;padding:12px;box-shadow:0 20px 36px rgba(0,0,0,.4);">
                    <video id="camera-capture-video" autoplay playsinline style="width:100%;max-height:60vh;border-radius:12px;background:#000;"></video>
                    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:10px;">
                        <button type="button" data-camera-cancel style="border:none;border-radius:10px;padding:8px 12px;background:#334155;color:#fff;font-weight:700;cursor:pointer;">Batal</button>
                        <button type="button" data-camera-shoot style="border:none;border-radius:10px;padding:8px 12px;background:#16a34a;color:#fff;font-weight:700;cursor:pointer;">Ambil Foto</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            return modal;
        };

        const openCameraCapture = async () => {
            const modal = ensureCameraModal();
            const video = modal.querySelector('#camera-capture-video');
            const btnShoot = modal.querySelector('[data-camera-shoot]');
            const btnCancel = modal.querySelector('[data-camera-cancel]');
            let stream = null;

            const closeModal = () => {
                modal.style.display = 'none';
                if (stream) {
                    stream.getTracks().forEach((track) => track.stop());
                    stream = null;
                }
                video.srcObject = null;
                btnShoot.onclick = null;
                btnCancel.onclick = null;
            };

            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } },
                    audio: false,
                });
                video.srcObject = stream;
                modal.style.display = 'flex';
            } catch (error) {
                console.error(error);
                window.alert('Kamera tidak dapat diakses. Pastikan izin kamera diaktifkan.');
                return;
            }

            btnCancel.onclick = () => closeModal();
            btnShoot.onclick = () => {
                const canvas = document.createElement('canvas');
                const width = video.videoWidth || 1280;
                const height = video.videoHeight || 720;
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, width, height);
                canvas.toBlob(
                    (blob) => {
                        if (!blob) return;
                        const file = new File([blob], `camera_${Date.now()}.jpg`, { type: 'image/jpeg' });
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        input.files = dt.files;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                        closeModal();
                    },
                    'image/jpeg',
                    0.92,
                );
            };
        };

        slot.addEventListener('click', (e) => {
            if (e.target.closest('.photo-slot-remove')) return;
            e.preventDefault();
            e.stopPropagation();
            openCameraCapture();
        });

        input.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
        });

        input.addEventListener('change', () => {
            if (input.files?.[0]) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    preview.src = ev.target.result;
                    preview.style.display = 'block';
                    if (placeholder) placeholder.style.display = 'none';
                    if (removeBtn) removeBtn.style.display = 'flex';
                    slot.classList.add('has-file');
                };
                reader.readAsDataURL(input.files[0]);
            }
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

    /* Kirim form via AJAX: validasi gagal tanpa reload, isian & file tetap di browser */
    const bbmForm = document.getElementById('bbm-report-form');
    const bbmSubmitBtn = bbmForm?.querySelector('button[type="submit"]');
    const bbmSubmitHtml = bbmSubmitBtn?.innerHTML ?? '';

    const showBbmValidationErrors = (messages) => {
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
            confirmButtonText: 'Perbaiki form',
            allowEnterKey: false,
            returnFocus: false,
            didOpen: (popup) => {
                const btn = popup.querySelector('.swal2-confirm');
                if (btn) btn.setAttribute('type', 'button');
            },
        });
    };

    bbmForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrf || typeof Swal === 'undefined') {
            bbmForm.submit();
            return;
        }

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
