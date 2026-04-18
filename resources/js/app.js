import './bootstrap';
import Alpine from 'alpinejs';
import SignaturePad from 'signature_pad';
import TomSelect from 'tom-select';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'tom-select/dist/css/tom-select.bootstrap5.css';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    /* ================================================================
       LOGIN
       ================================================================ */
    const loginForm = document.querySelector('[data-login-form]');
    if (loginForm) {
        const passwordInput = loginForm.querySelector('#password');
        const passwordToggle = loginForm.querySelector('[data-password-toggle]');
        const passwordIcon = loginForm.querySelector('[data-password-icon]');
        const submitButton = loginForm.querySelector('[data-login-submit]');
        const submitLabel = submitButton ? submitButton.innerHTML : '';
        if (passwordInput && passwordToggle && passwordIcon) {
            passwordToggle.addEventListener('click', () => {
                const show = passwordInput.type === 'password';
                passwordInput.type = show ? 'text' : 'password';
                passwordIcon.classList.toggle('bi-eye', !show);
                passwordIcon.classList.toggle('bi-eye-slash', show);
            });
        }
        if (submitButton) {
            loginForm.addEventListener('submit', () => {
                submitButton.classList.add('is-loading');
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Memproses...';
                setTimeout(() => { submitButton.classList.remove('is-loading'); submitButton.disabled = false; submitButton.innerHTML = submitLabel; }, 6000);
            });
        }
    }

    /* ================================================================
       DASHBOARD
       ================================================================ */
    document.querySelectorAll('.dash-pressable').forEach(el => {
        const clear = () => el.classList.remove('dash-pressing');
        el.addEventListener('pointerdown', () => el.classList.add('dash-pressing'));
        el.addEventListener('pointerup', clear);
        el.addEventListener('pointercancel', clear);
        el.addEventListener('pointerleave', clear);
    });

    /* ================================================================
       ADMIN REAL-TIME SEARCH & FILTER
       ================================================================ */
    document.querySelectorAll('[data-admin-toolbar]').forEach(toolbar => {
        let searchTimer = null;

        // Debounced search: auto-submit after 400ms pause
        const searchInput = toolbar.querySelector('[data-admin-search]');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => toolbar.submit(), 400);
            });
        }

        // Instant filter: auto-submit on select/date change
        toolbar.querySelectorAll('[data-admin-filter]').forEach(filterEl => {
            filterEl.addEventListener('change', () => {
                clearTimeout(searchTimer);
                toolbar.submit();
            });
        });
    });

    /* ================================================================
       ADMIN TABS
       ================================================================ */
    document.querySelectorAll('[data-tab-btn]').forEach(btn => {
        btn.addEventListener('click', () => {
            const group = btn.closest('[data-tab-group]');
            if (!group) return;
            group.querySelectorAll('[data-tab-btn]').forEach(b => b.classList.remove('active'));
            group.querySelectorAll('[data-tab-panel]').forEach(p => p.style.display = 'none');
            btn.classList.add('active');
            const target = group.querySelector(`[data-tab-panel="${btn.dataset.tabBtn}"]`);
            if (target) target.style.display = 'block';
        });
    });

    /* ================================================================
       CHECKLIST WIZARD
       ================================================================ */
    const wizardRoot = document.querySelector('[data-checklist-wizard]');
    if (!wizardRoot) return;

    const form = wizardRoot.querySelector('#checklist-form');
    const steps = Array.from(wizardRoot.querySelectorAll('.wizard-step'));
    const prevButton = wizardRoot.querySelector('#wizard-prev');
    const nextButton = wizardRoot.querySelector('#wizard-next');
    const stepLabel = wizardRoot.querySelector('#checklist-step-label');
    const progressFill = wizardRoot.querySelector('#checklist-progress-fill');
    const progressPct = wizardRoot.querySelector('#checklist-progress-pct');
    if (!form || !steps.length) return;

    /* ---- Driver Select: paired filtering (serah ↔ terima) ---- */
    const driverSelectEls = wizardRoot.querySelectorAll('[data-driver-select]');
    const tomSelectInstances = {};
    const allDriverOptions = {};

    const tomSelectConfig = (selectEl) => ({
        allowEmptyOption: false,
        create: false,
        maxOptions: 100,
        placeholder: selectEl.dataset.placeholder || 'Pilih Driver',
        closeAfterSelect: true,
        render: {
            option(data, escape) {
                const iconClass = data.icon || 'bi bi-person';
                const isActive = data.active === '1';
                return `<div class="driver-option-row ${isActive ? 'is-active' : ''}"><i class="${escape(iconClass)}"></i><span>${escape(data.text)}</span></div>`;
            },
            item(data, escape) {
                const iconClass = data.icon || 'bi bi-person';
                const isActive = data.active === '1';
                return `<div class="driver-option-row ${isActive ? 'is-active' : ''}"><i class="${escape(iconClass)}"></i><span>${escape(data.text)}</span></div>`;
            },
        },
        onInitialize() {
            this.removeOption('');
            this.refreshOptions(false);
        },
        onItemAdd() {
            this.close();
            this.blur();
        },
    });

    driverSelectEls.forEach(select => {
        const ts = new TomSelect(select, tomSelectConfig(select));
        const id = select.id;
        tomSelectInstances[id] = ts;
        // Backup all original options
        allDriverOptions[id] = { ...ts.options };
    });

    // Link serah ↔ terima: exclude selected driver from the other dropdown
    const serahTS = tomSelectInstances['driver_serah'];
    const terimaTS = tomSelectInstances['driver_terima'];

    if (serahTS && terimaTS) {
        const syncDriverOptions = (changedId, selectedValue, previousValue) => {
            const otherTS = changedId === 'driver_serah' ? terimaTS : serahTS;
            const otherId = changedId === 'driver_serah' ? 'driver_terima' : 'driver_serah';

            // Restore previously excluded option
            if (previousValue && allDriverOptions[otherId][previousValue]) {
                otherTS.addOption(allDriverOptions[otherId][previousValue]);
            }
            // Remove newly selected option from the other dropdown
            if (selectedValue && otherTS.options[selectedValue]) {
                if (otherTS.getValue() === selectedValue) {
                    otherTS.clear(true);
                }
                otherTS.removeOption(selectedValue);
            }
            otherTS.refreshOptions(false);
        };

        let prevSerah = serahTS.getValue();
        let prevTerima = terimaTS.getValue();

        serahTS.on('change', (value) => {
            syncDriverOptions('driver_serah', value, prevSerah);
            prevSerah = value;
        });
        terimaTS.on('change', (value) => {
            syncDriverOptions('driver_terima', value, prevTerima);
            prevTerima = value;
        });
    }

    let currentStep = 1;
    const totalStep = steps.length;
    let refreshSignaturePads = () => { };

    const updateWizardUI = () => {
        steps.forEach(s => s.classList.toggle('active', +s.dataset.step === currentStep));
        const pct = Math.round((currentStep / totalStep) * 100);
        progressFill.style.width = `${pct}%`;
        stepLabel.textContent = `LANGKAH ${currentStep} DARI ${totalStep}`;
        if (progressPct) progressPct.textContent = `${pct}%`;
        prevButton.disabled = currentStep === 1;
        if (currentStep === totalStep) {
            nextButton.classList.add('final');
            nextButton.innerHTML = `GENERATE TO PDF <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/></svg>`;
        } else {
            nextButton.classList.remove('final');
            nextButton.innerHTML = `LANJUT <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
        }
        requestAnimationFrame(() => refreshSignaturePads());
    };

    const validateCurrentStep = () => {
        const el = steps.find(s => +s.dataset.step === currentStep);

        // VALIDASI RADIO & KETERANGAN (STEP 2,3,4)
        if ([2, 3, 4].includes(currentStep)) {
            const current = steps.find(s => +s.dataset.step === currentStep);
            if (!current) return true;

            const rows = current.querySelectorAll('.checklist-condition-row');

            for (const row of rows) {
                const radios = row.querySelectorAll('input[type="radio"]');
                const checked = Array.from(radios).find(r => r.checked);

                // 1. BELUM PILIH RADIO
                if (!checked) {
                    showModal(
                        'error',
                        'Checklist Belum Lengkap',
                        'Masih ada kondisi yang belum dipilih (OK / NO).',
                        [{ label: 'OK', class: 'modal-btn-secondary', action: 'close' }]
                    );

                    row.style.borderColor = '#ef4444';
                    return false;
                }

                // 2. JIKA PILIH NO → KETERANGAN WAJIB
                if (checked.value === 'no') {
                    const note = row.querySelector('.checklist-item-note');

                    if (!note || !note.value.trim()) {
                        showModal(
                            'error',
                            'Keterangan Wajib Diisi',
                            'Item dengan kondisi "NO" harus diberi keterangan.',
                            [{ label: 'OK', class: 'modal-btn-secondary', action: 'close' }]
                        );

                        note.style.borderColor = '#ef4444';
                        return false;
                    }
                }
            }
        }

        // Validasi foto
        if (currentStep === 2 || currentStep === 5) {
            const requiredPhotos = el.querySelectorAll('[data-required-photo]');
            let allPhotosValid = true;
            requiredPhotos.forEach(input => {
                if (!input.files || input.files.length === 0) {
                    allPhotosValid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            if (!allPhotosValid) {
                showModal(
                    'error',
                    'Foto Wajib Diisi',
                    'Harap unggah semua foto yang diperlukan sebelum melanjutkan.',
                    [{ label: 'OK', class: 'modal-btn-secondary', action: 'close' }]
                );
                return false;
            }
        }

        // VALIDASI MINIMAL FOTO (INTERIOR / MESIN)
        const dynamicContainers = steps
            .find(s => +s.dataset.step === currentStep)
            ?.querySelectorAll('[data-dynamic-photos][data-min-photos]');

        if (dynamicContainers && dynamicContainers.length) {
            for (const container of dynamicContainers) {
                const minPhotos = +(container.dataset.minPhotos) || 1;

                const inputs = container.querySelectorAll('input[type="file"]');

                let filled = 0;
                inputs.forEach(input => {
                    if (input.files && input.files.length > 0) {
                        filled++;
                    }
                });

                if (filled < minPhotos) {
                    showModal(
                        'error',
                        'Foto Belum Cukup',
                        `Minimal ${minPhotos} foto harus diupload pada bagian ini.`,
                        [{ label: 'OK', class: 'modal-btn-secondary', action: 'close' }]
                    );
                    return false;
                }
            }
        }

        // Validasi khusus step KM (Step 5)
        if (currentStep === 5) {
            if (!isKmAwalValid || !isKmAkhirValid) {
                showModal(
                    'error',
                    'Data Tidak Valid',
                    'Periksa kembali KM Awal dan KM Akhir. Data masih belum sesuai.',
                    [{ label: 'OK', class: 'modal-btn-secondary', action: 'close' }]
                );
                return false;
            }
        }

        // Validasi (step 7)
        if (currentStep === 7) {
            const sigSerah = window._sigPadSerah;
            const sigTerima = window._sigPadTerima;

            if (!sigSerah || sigSerah.isEmpty()) {
                showModal(
                    'error',
                    'Tanda Tangan Diperlukan',
                    'Tanda tangan driver yang menyerahkan belum diisi.',
                    [{ label: 'OK', class: 'modal-btn-secondary', action: 'close' }]
                );
                return false;
            }

            if (!sigTerima || sigTerima.isEmpty()) {
                showModal(
                    'error',
                    'Tanda Tangan Diperlukan',
                    'Tanda tangan driver yang menerima belum diisi.',
                    [{ label: 'OK', class: 'modal-btn-secondary', action: 'close' }]
                );
                return false;
            }

        }

        if (!el) return true;
        const fields = el.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]):not([data-no-validate]), select, textarea');
        for (const f of fields) {
            if (f.closest('.dynamic-photo-container') && !f.hasAttribute('required')) continue;
            if (!f.checkValidity()) { f.reportValidity(); return false; }
        }
        return true;
    };

    // RESET ERROR STYLE SAAT INTERAKSI
    document.querySelectorAll('.checklist-condition-row').forEach(row => {
        const radios = row.querySelectorAll('input[type="radio"]');
        const note = row.querySelector('.checklist-item-note');

        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                row.style.borderColor = '';
            });
        });

        if (note) {
            note.addEventListener('input', () => {
                note.style.borderColor = '';
            });
        }
    });

    prevButton.addEventListener('click', () => {
        if (currentStep > 1) { currentStep--; updateWizardUI(); window.scrollTo({ top: 0, behavior: 'smooth' }); }
    });

    nextButton.addEventListener('click', async () => {
        if (!validateCurrentStep()) return;
        if (currentStep < totalStep) { currentStep++; updateWizardUI(); window.scrollTo({ top: 0, behavior: 'smooth' }); return; }
        // Final step — check confirmation
        const konfirmasi = document.getElementById('konfirmasi_data');
        if (konfirmasi && !konfirmasi.checked) {
            showModal('error', 'Konfirmasi Diperlukan', 'Anda harus mencentang checkbox konfirmasi data sebelum dapat membuat laporan PDF.', [
                { label: 'OK, Saya Mengerti', class: 'modal-btn-secondary', action: 'close' }
            ]);
            return;
        }
        await submitChecklist();
    });

    /* ---- Modal System ---- */
    const showModal = (type, title, message, buttons = []) => {
        const modal = document.getElementById('pdf-modal');
        const iconEl = document.getElementById('pdf-modal-icon');
        const titleEl = document.getElementById('pdf-modal-title');
        const msgEl = document.getElementById('pdf-modal-message');
        const actionsEl = document.getElementById('pdf-modal-actions');

        iconEl.className = 'modal-icon ' + type;
        iconEl.innerHTML = type === 'success'
            ? '<svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/></svg>'
            : '<svg width="32" height="32" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
        titleEl.textContent = title;
        msgEl.textContent = message;
        actionsEl.innerHTML = '';

        buttons.forEach(btn => {
            if (btn.href) {
                const a = document.createElement('a');
                a.href = btn.href;
                a.target = btn.target || '_blank';
                a.className = `modal-btn ${btn.class}`;
                a.textContent = btn.label;
                actionsEl.appendChild(a);
            } else {
                const b = document.createElement('button');
                b.className = `modal-btn ${btn.class}`;
                b.textContent = btn.label;
                if (btn.action === 'close') b.onclick = () => modal.style.display = 'none';
                else if (btn.action === 'dashboard') b.onclick = () => window.location.href = '/dashboard';
                actionsEl.appendChild(b);
            }
        });

        modal.style.display = 'flex';
    };

    /* ---- Form Submit ---- */
    const submitChecklist = async () => {
        if (window._sigPadSerah && !window._sigPadSerah.isEmpty()) {
            document.getElementById('sig-data-serah').value = window._sigPadSerah.toDataURL();
        }
        if (window._sigPadTerima && !window._sigPadTerima.isEmpty()) {
            document.getElementById('sig-data-terima').value = window._sigPadTerima.toDataURL();
        }
        const bbmDate = form.querySelector('[name="bbm_terakhir_date"]');
        const bbmTime = form.querySelector('[name="bbm_terakhir_time"]');
        if (bbmDate && bbmDate.value) {
            let hidden = form.querySelector('[name="bbm_terakhir"]');
            if (!hidden) { hidden = document.createElement('input'); hidden.type = 'hidden'; hidden.name = 'bbm_terakhir'; form.appendChild(hidden); }
            hidden.value = bbmDate.value + (bbmTime?.value ? ' ' + bbmTime.value : '');
        }

        const formData = new FormData(form);
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        nextButton.disabled = true;
        nextButton.innerHTML = '<span style="display:inline-flex;align-items:center;gap:8px"><svg class="spin-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="31" stroke-linecap="round"></circle></svg> MEMPROSES...</span>';

        try {
            const res = await fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: formData });
            const data = await res.json();
            if (data.success) {
                showModal('success', 'PDF Berhasil Dibuat!', 'Laporan checklist kendaraan telah berhasil di-generate dan disimpan.', [
                    { label: '📄 Lihat PDF', class: 'modal-btn-success', href: data.pdf_url, target: '_blank' },
                    { label: '← Kembali ke Dashboard', class: 'modal-btn-secondary', action: 'dashboard' }
                ]);
            } else {
                showModal('error', 'Gagal Membuat PDF', data.message || 'Terjadi kesalahan saat menyimpan data.', [
                    { label: 'Coba Lagi', class: 'modal-btn-secondary', action: 'close' }
                ]);
                nextButton.disabled = false;
                updateWizardUI();
            }
        } catch (err) {
            console.error(err);
            showModal('error', 'Koneksi Bermasalah', 'Terjadi kesalahan jaringan. Silakan periksa koneksi dan coba lagi.', [
                { label: 'OK', class: 'modal-btn-secondary', action: 'close' }
            ]);
            nextButton.disabled = false;
            updateWizardUI();
        }
    };

    form.addEventListener('submit', e => e.preventDefault());

    /* ================================================================
       PHOTO PREVIEW
       ================================================================ */
    const initPhotoSlot = slot => {
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
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.72);display:none;align-items:center;justify-content:center;z-index:10000;padding:16px;';
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
                    stream.getTracks().forEach(track => track.stop());
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
                canvas.toBlob(blob => {
                    if (!blob) return;
                    const file = new File([blob], `camera_${Date.now()}.jpg`, { type: 'image/jpeg' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    closeModal();
                }, 'image/jpeg', 0.92);
            };
        };

        slot.addEventListener('click', e => {
            if (e.target.closest('.photo-slot-remove')) return;
            e.preventDefault();
            e.stopPropagation();
            openCameraCapture();
        });

        input.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
        });

        input.addEventListener('change', () => {
            if (input.files?.[0]) {
                const reader = new FileReader();
                reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; if (placeholder) placeholder.style.display = 'none'; if (removeBtn) removeBtn.style.display = 'flex'; slot.classList.add('has-file'); };
                reader.readAsDataURL(input.files[0]);
            }
        });
        if (removeBtn) removeBtn.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); input.value = ''; preview.style.display = 'none'; preview.src = ''; if (placeholder) placeholder.style.display = 'flex'; removeBtn.style.display = 'none'; slot.classList.remove('has-file'); });
    };
    wizardRoot.querySelectorAll('[data-photo-preview-slot]').forEach(initPhotoSlot);

    /* ================================================================
       DYNAMIC PHOTO SLOTS
       ================================================================ */
    wizardRoot.querySelectorAll('[data-dynamic-photos]').forEach(container => {
        const grid = container.querySelector('.dynamic-photo-grid');
        const addBtn = container.querySelector('[data-add-photo-btn]');
        const section = container.dataset.section;
        const maxSlots = +(container.dataset.max || 3);
        if (!grid || !addBtn) return;
        let slotCount = 1;
        addBtn.addEventListener('click', () => {
            if (slotCount >= maxSlots) return;
            slotCount++;
            const label = document.createElement('label');
            label.className = 'checklist-photo-slot slot-animate-in';
            label.setAttribute('data-photo-preview-slot', '');
            label.innerHTML = `<input type="file" name="${section}_foto_${slotCount}" accept="image/*" capture="environment" data-photo-single><div class="photo-slot-placeholder"><span class="checklist-photo-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="10" r="1.4" stroke="currentColor" stroke-width="1.6"/><path d="M20 15L15.3 10.5L8 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span><strong>FOTO ${slotCount}</strong></div><img class="photo-slot-preview" alt="Preview" style="display:none"><button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus foto">×</button>`;
            grid.insertBefore(label, addBtn);
            initPhotoSlot(label);
            if (slotCount >= maxSlots) addBtn.style.display = 'none';
        });
    });

    /* ================================================================
       NOMOR KENDARAAN → AUTO-FILL
       ================================================================ */
    const nomorSelect = document.getElementById('nomor_kendaraan');
    const jenisInput = document.getElementById('jenis_kendaraan');
    const kmAwalInput = document.getElementById('km_awal');
    if (nomorSelect && jenisInput) {
        nomorSelect.addEventListener('change', async () => {
            const sel = nomorSelect.options[nomorSelect.selectedIndex];
            jenisInput.value = sel?.dataset?.jenis || '';
            if (nomorSelect.value && kmAwalInput) {
                try { const r = await fetch(`/api/kendaraan/last-km?nomor=${encodeURIComponent(nomorSelect.value)}`); const d = await r.json(); /* kmAwalInput.value = d.km || 0; */ } catch { kmAwalInput.value = 0; }
            }
        });
    }

    /* ================================================================
       BBM SLIDER
       ================================================================ */
    const bbmRange = document.getElementById('bbm-range');
    const bbmDisplay = document.getElementById('bbm-value-display');
    if (bbmRange && bbmDisplay) bbmRange.addEventListener('input', () => bbmDisplay.innerHTML = `${bbmRange.value}<small>%</small>`);

    /* ================================================================
       KM VALIDATION
       ================================================================ */

    const kmAwalError = document.getElementById('km-awal-error');
    const kmAwalErrorText = document.getElementById('km-awal-error-text');

    let lastKmDatabase = 0;
    let isKmAwalValid = false;
    let isKmAkhirValid = true;

    // Ambil KM terakhir dari DB saat pilih kendaraan
    if (nomorSelect && kmAwalInput) {
        nomorSelect.addEventListener('change', async () => {
            if (nomorSelect.value) {
                try {
                    const r = await fetch(`/api/kendaraan/last-km?nomor=${encodeURIComponent(nomorSelect.value)}`);
                    const d = await r.json();
                    lastKmDatabase = d.km || 0;
                } catch {
                    lastKmDatabase = 0;
                }
            }
        });
    }

    // VALIDASI KM AWAL
    if (kmAwalInput && kmAwalError) {
        kmAwalInput.addEventListener('input', () => {
            const val = +(kmAwalInput.value) || 0;

            if (val !== lastKmDatabase) {
                kmAwalError.style.display = 'flex';
                kmAwalErrorText.textContent = `KM Awal (${val}) tidak sesuai dengan data terakhir (${lastKmDatabase}).`;
                kmAwalInput.style.borderColor = '#ef4444';
                isKmAwalValid = false;
            } else {
                kmAwalError.style.display = 'none';
                kmAwalInput.style.borderColor = '';
                isKmAwalValid = true;
            }
        });
    }

    const kmAkhirInput = document.getElementById('km_akhir');
    const kmError = document.getElementById('km-error');
    const kmErrorText = document.getElementById('km-error-text');
    if (kmAkhirInput && kmAwalInput && kmError) {
        kmAkhirInput.addEventListener('input', () => {
            const awal = +(kmAwalInput.value) || 0;
            const akhir = +(kmAkhirInput.value) || 0;

            if (akhir > 0 && akhir < awal) {
                kmError.style.display = 'flex';
                kmErrorText.textContent = `KM Akhir (${akhir}) tidak boleh lebih kecil dari KM Awal (${awal}).`;
                kmAkhirInput.style.borderColor = '#ef4444';
                isKmAkhirValid = false;
            } else {
                kmError.style.display = 'none';
                kmAkhirInput.style.borderColor = '';
                isKmAkhirValid = true;
            }
        });
    }

    /* ================================================================
       SIGNATURE PADS
       ================================================================ */
    const initSigPad = (canvasId, hintSel, clearSel, dataId) => {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;
        const hint = wizardRoot.querySelector(`[data-sig-hint="${hintSel}"]`);
        const clearBtn = wizardRoot.querySelector(`[data-clear-sig="${clearSel}"]`);
        const resize = () => {
            const rect = canvas.getBoundingClientRect();
            if (!rect.width || !rect.height) return false;
            const r = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = rect.width * r;
            canvas.height = rect.height * r;
            const context = canvas.getContext('2d');
            context.setTransform(1, 0, 0, 1, 0, 0);
            context.scale(r, r);
            return true;
        };
        resize();
        const pad = new SignaturePad(canvas, { backgroundColor: 'rgba(255,255,255,0)', penColor: '#0f172a', minWidth: 1.5, maxWidth: 3 });
        pad.addEventListener('beginStroke', () => { if (hint) hint.classList.add('hidden'); });
        if (clearBtn) clearBtn.addEventListener('click', () => { pad.clear(); if (hint) hint.classList.remove('hidden'); const di = document.getElementById(dataId); if (di) di.value = ''; });
        pad._refreshCanvas = () => {
            const data = pad.isEmpty() ? [] : pad.toData();
            if (!resize()) return;
            pad.clear();
            if (data.length) {
                pad.fromData(data);
            } else if (hint) {
                hint.classList.remove('hidden');
            }
        };
        let rt; window.addEventListener('resize', () => { clearTimeout(rt); rt = setTimeout(() => { pad._refreshCanvas(); }, 200); });
        return pad;
    };
    window._sigPadSerah = initSigPad('sig-pad-serah', 'serah', 'serah', 'sig-data-serah');
    window._sigPadTerima = initSigPad('sig-pad-terima', 'terima', 'terima', 'sig-data-terima');
    refreshSignaturePads = () => {
        window._sigPadSerah?._refreshCanvas?.();
        window._sigPadTerima?._refreshCanvas?.();
    };

    /* ================================================================
       FORM COMPLETENESS CHECK
       ================================================================ */
    const completeAlert = document.getElementById('form-complete-alert');
    const konfirmasi = document.getElementById('konfirmasi_data');
    if (konfirmasi && completeAlert) {
        konfirmasi.addEventListener('change', () => { completeAlert.style.display = konfirmasi.checked ? 'flex' : 'none'; });
    }

    /* Spinning icon */
    const st = document.createElement('style');
    st.textContent = '@keyframes spinIcon { to { transform: rotate(360deg); } } .spin-icon { animation: spinIcon 1s linear infinite; }';
    document.head.appendChild(st);

    updateWizardUI();
});

document.addEventListener("DOMContentLoaded", function () {
    const slider = document.getElementById("bbm-range");
    const display = document.getElementById("bbm-value-display");

    if (!slider) return;

    function updateSlider() {
        const value = slider.value;

        // update text
        if (display) {
            display.innerHTML = value + "<small>%</small>";
        }

        // FORCE warna (ini kuncinya)
        slider.style.background = `linear-gradient(to right, #facc15 ${value}%, #e5e7eb ${value}%)`;
    }

    // init pertama
    updateSlider();

    // saat digeser
    slider.addEventListener("input", updateSlider);
});