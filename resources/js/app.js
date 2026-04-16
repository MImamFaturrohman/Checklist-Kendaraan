import './bootstrap';

import Alpine from 'alpinejs';
import SignaturePad from 'signature_pad';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    /* ================================================================
       LOGIN FORM (unchanged)
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
                const showPassword = passwordInput.type === 'password';
                passwordInput.type = showPassword ? 'text' : 'password';
                passwordIcon.classList.toggle('bi-eye', !showPassword);
                passwordIcon.classList.toggle('bi-eye-slash', showPassword);
            });
        }

        if (submitButton) {
            loginForm.addEventListener('submit', () => {
                submitButton.classList.add('is-loading');
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
                setTimeout(() => {
                    submitButton.classList.remove('is-loading');
                    submitButton.disabled = false;
                    submitButton.innerHTML = submitLabel;
                }, 6000);
            });
        }
    }

    /* ================================================================
       DASHBOARD PRESSABLE BUTTONS
       ================================================================ */
    document.querySelectorAll('.dash-pressable').forEach((element) => {
        const clearPressState = () => element.classList.remove('dash-pressing');
        element.addEventListener('pointerdown', () => element.classList.add('dash-pressing'));
        element.addEventListener('pointerup', clearPressState);
        element.addEventListener('pointercancel', clearPressState);
        element.addEventListener('pointerleave', clearPressState);
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

    if (!form || !steps.length || !prevButton || !nextButton || !stepLabel || !progressFill) return;

    let currentStep = 1;
    const totalStep = steps.length;

    /* ---- Wizard UI Update ---- */
    const updateWizardUI = () => {
        steps.forEach(step => {
            step.classList.toggle('active', Number(step.dataset.step) === currentStep);
        });

        const progress = Math.round((currentStep / totalStep) * 100);
        progressFill.style.width = `${progress}%`;
        stepLabel.textContent = `LANGKAH ${currentStep} DARI ${totalStep}`;
        if (progressPct) progressPct.textContent = `${progress}%`;

        prevButton.disabled = currentStep === 1;

        if (currentStep === totalStep) {
            nextButton.classList.add('final');
            nextButton.innerHTML = `GENERATE TO PDF <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/></svg>`;
        } else {
            nextButton.classList.remove('final');
            nextButton.innerHTML = `LANJUT <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
        }
    };

    /* ---- Step Validation ---- */
    const validateCurrentStep = () => {
        const currentStepEl = steps.find(s => Number(s.dataset.step) === currentStep);
        if (!currentStepEl) return true;

        const fields = currentStepEl.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]):not([data-no-validate]), select, textarea');
        for (const field of fields) {
            if (field.closest('.dynamic-photo-container') && !field.hasAttribute('required')) continue;
            if (!field.checkValidity()) {
                field.reportValidity();
                return false;
            }
        }
        return true;
    };

    /* ---- Navigation ---- */
    prevButton.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep -= 1;
            updateWizardUI();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    nextButton.addEventListener('click', async () => {
        if (!validateCurrentStep()) return;

        if (currentStep < totalStep) {
            currentStep += 1;
            updateWizardUI();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        // Final step - submit form
        await submitChecklist();
    });

    /* ---- Form Submit ---- */
    const submitChecklist = async () => {
        // Update signature data
        if (window._sigPadSerah && !window._sigPadSerah.isEmpty()) {
            document.getElementById('sig-data-serah').value = window._sigPadSerah.toDataURL();
        }
        if (window._sigPadTerima && !window._sigPadTerima.isEmpty()) {
            document.getElementById('sig-data-terima').value = window._sigPadTerima.toDataURL();
        }

        // Combine bbm_terakhir date + time
        const bbmDate = form.querySelector('[name="bbm_terakhir_date"]');
        const bbmTime = form.querySelector('[name="bbm_terakhir_time"]');
        if (bbmDate && bbmTime && bbmDate.value) {
            let bbmVal = bbmDate.value;
            if (bbmTime.value) bbmVal += ' ' + bbmTime.value;
            let hidden = form.querySelector('[name="bbm_terakhir"]');
            if (!hidden) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'bbm_terakhir';
                form.appendChild(hidden);
            }
            hidden.value = bbmVal;
        }

        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        nextButton.disabled = true;
        nextButton.innerHTML = '<span style="display:inline-flex;align-items:center;gap:8px"><svg class="spin-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="31" stroke-linecap="round"></circle></svg> MEMPROSES...</span>';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                // Download PDF
                window.open(data.pdf_url, '_blank');
                alert('✅ Checklist berhasil disimpan! PDF telah di-generate.');
                window.location.href = '/dashboard';
            } else {
                alert('❌ Terjadi kesalahan: ' + (data.message || 'Gagal menyimpan data.'));
                nextButton.disabled = false;
                updateWizardUI();
            }
        } catch (err) {
            console.error(err);
            alert('❌ Terjadi kesalahan jaringan. Silakan coba lagi.');
            nextButton.disabled = false;
            updateWizardUI();
        }
    };

    form.addEventListener('submit', (e) => e.preventDefault());

    /* ================================================================
       PHOTO PREVIEW IN SLOTS
       ================================================================ */
    const initPhotoSlot = (slot) => {
        const input = slot.querySelector('[data-photo-single]');
        const preview = slot.querySelector('.photo-slot-preview');
        const placeholder = slot.querySelector('.photo-slot-placeholder');
        const removeBtn = slot.querySelector('.photo-slot-remove');

        if (!input || !preview) return;

        input.addEventListener('change', () => {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.src = e.target.result;
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

    wizardRoot.querySelectorAll('[data-photo-preview-slot]').forEach(initPhotoSlot);

    /* ================================================================
       DYNAMIC PHOTO SLOTS (Interior & Mesin)
       ================================================================ */
    wizardRoot.querySelectorAll('[data-dynamic-photos]').forEach(container => {
        const grid = container.querySelector('.dynamic-photo-grid');
        const addBtn = container.querySelector('[data-add-photo-btn]');
        const section = container.dataset.section;
        const maxSlots = Number(container.dataset.max || 3);

        if (!grid || !addBtn) return;

        let slotCount = 1;

        const createSlot = (num) => {
            const label = document.createElement('label');
            label.className = 'checklist-photo-slot slot-animate-in';
            label.setAttribute('data-photo-preview-slot', '');
            label.innerHTML = `
                <input type="file" name="${section}_foto_${num}" accept="image/*" data-photo-single>
                <div class="photo-slot-placeholder">
                    <span class="checklist-photo-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="10" r="1.4" stroke="currentColor" stroke-width="1.6"/><path d="M20 15L15.3 10.5L8 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <strong>FOTO ${num}</strong>
                </div>
                <img class="photo-slot-preview" alt="Preview" style="display:none">
                <button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus foto">×</button>
            `;
            return label;
        };

        addBtn.addEventListener('click', () => {
            if (slotCount >= maxSlots) return;
            slotCount++;

            const newSlot = createSlot(slotCount);
            grid.insertBefore(newSlot, addBtn);
            initPhotoSlot(newSlot);

            if (slotCount >= maxSlots) {
                addBtn.style.display = 'none';
            }
        });
    });

    /* ================================================================
       NOMOR KENDARAAN DROPDOWN → AUTO-FILL JENIS + KM AWAL
       ================================================================ */
    const nomorSelect = document.getElementById('nomor_kendaraan');
    const jenisInput = document.getElementById('jenis_kendaraan');
    const kmAwalInput = document.getElementById('km_awal');

    if (nomorSelect && jenisInput) {
        nomorSelect.addEventListener('change', async () => {
            const selected = nomorSelect.options[nomorSelect.selectedIndex];
            const jenis = selected?.dataset?.jenis || '';
            jenisInput.value = jenis;

            // Fetch last KM
            if (nomorSelect.value && kmAwalInput) {
                try {
                    const resp = await fetch(`/api/kendaraan/last-km?nomor=${encodeURIComponent(nomorSelect.value)}`);
                    const data = await resp.json();
                    kmAwalInput.value = data.km || 0;
                } catch (e) {
                    kmAwalInput.value = 0;
                }
            }
        });
    }

    /* ================================================================
       BBM SLIDER
       ================================================================ */
    const bbmRange = document.getElementById('bbm-range');
    const bbmDisplay = document.getElementById('bbm-value-display');

    if (bbmRange && bbmDisplay) {
        bbmRange.addEventListener('input', () => {
            bbmDisplay.innerHTML = `${bbmRange.value}<small>%</small>`;
        });
    }

    /* ================================================================
       KM AKHIR VALIDATION
       ================================================================ */
    const kmAkhirInput = document.getElementById('km_akhir');
    const kmError = document.getElementById('km-error');
    const kmErrorText = document.getElementById('km-error-text');

    if (kmAkhirInput && kmAwalInput && kmError && kmErrorText) {
        kmAkhirInput.addEventListener('input', () => {
            const awal = Number(kmAwalInput.value) || 0;
            const akhir = Number(kmAkhirInput.value) || 0;

            if (akhir > 0 && akhir < awal) {
                kmError.style.display = 'flex';
                kmErrorText.textContent = `ERROR: KM Akhir (${akhir}) tidak boleh lebih kecil dari KM Awal (${awal}). Angka tidak sesuai!`;
                kmAkhirInput.style.borderColor = '#ef4444';
            } else {
                kmError.style.display = 'none';
                kmAkhirInput.style.borderColor = '';
            }
        });
    }

    /* ================================================================
       SIGNATURE PADS
       ================================================================ */
    const initSignaturePad = (canvasId, hintSelector, clearSelector, dataInputId) => {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        const hint = wizardRoot.querySelector(`[data-sig-hint="${hintSelector}"]`);
        const clearBtn = wizardRoot.querySelector(`[data-clear-sig="${clearSelector}"]`);

        // Set canvas actual size for high DPI
        const resizeCanvas = () => {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
        };

        resizeCanvas();

        const pad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 0)',
            penColor: '#0f172a',
            minWidth: 1.5,
            maxWidth: 3,
        });

        pad.addEventListener('beginStroke', () => {
            if (hint) hint.classList.add('hidden');
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                pad.clear();
                if (hint) hint.classList.remove('hidden');
                const dataInput = document.getElementById(dataInputId);
                if (dataInput) dataInput.value = '';
            });
        }

        // Debounced resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                const data = pad.toData();
                resizeCanvas();
                pad.fromData(data);
            }, 200);
        });

        return pad;
    };

    window._sigPadSerah = initSignaturePad('sig-pad-serah', 'serah', 'serah', 'sig-data-serah');
    window._sigPadTerima = initSignaturePad('sig-pad-terima', 'terima', 'terima', 'sig-data-terima');

    /* ================================================================
       FORM COMPLETENESS CHECK (Green Alert)
       ================================================================ */
    const completeAlert = document.getElementById('form-complete-alert');
    const konfirmasiCheckbox = document.getElementById('konfirmasi_data');

    const checkFormCompleteness = () => {
        if (!completeAlert || !konfirmasiCheckbox) return;

        const isConfirmed = konfirmasiCheckbox.checked;
        if (isConfirmed) {
            completeAlert.style.display = 'flex';
        } else {
            completeAlert.style.display = 'none';
        }
    };

    if (konfirmasiCheckbox) {
        konfirmasiCheckbox.addEventListener('change', checkFormCompleteness);
    }

    /* ---- Spinning icon animation ---- */
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spinIcon {
            to { transform: rotate(360deg); }
        }
        .spin-icon {
            animation: spinIcon 1s linear infinite;
        }
    `;
    document.head.appendChild(style);

    /* ---- Initialize ---- */
    updateWizardUI();
});
