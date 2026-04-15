import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
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
                passwordToggle.setAttribute(
                    'aria-label',
                    showPassword ? 'Hide password' : 'Show password',
                );
            });
        }

        if (submitButton) {
            loginForm.addEventListener('submit', () => {
                submitButton.classList.add('is-loading');
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';

                // Safety fallback in case request fails before redirect.
                setTimeout(() => {
                    submitButton.classList.remove('is-loading');
                    submitButton.disabled = false;
                    submitButton.innerHTML = submitLabel;
                }, 6000);
            });
        }
    }

    document.querySelectorAll('.dash-pressable').forEach((element) => {
        const clearPressState = () => element.classList.remove('dash-pressing');

        element.addEventListener('pointerdown', () => {
            element.classList.add('dash-pressing');
        });

        element.addEventListener('pointerup', clearPressState);
        element.addEventListener('pointercancel', clearPressState);
        element.addEventListener('pointerleave', clearPressState);
    });

    const wizardRoot = document.querySelector('[data-checklist-wizard]');
    if (!wizardRoot) {
        return;
    }

    const form = wizardRoot.querySelector('#checklist-form');
    const steps = Array.from(wizardRoot.querySelectorAll('.wizard-step'));
    const prevButton = wizardRoot.querySelector('#wizard-prev');
    const nextButton = wizardRoot.querySelector('#wizard-next');
    const stepLabel = wizardRoot.querySelector('#checklist-step-label');
    const progressFill = wizardRoot.querySelector('#checklist-progress-fill');
    const signatureUploadWrap = wizardRoot.querySelector('#signature-upload-wrap');
    const signatureManualWrap = wizardRoot.querySelector('#signature-manual-wrap');
    const signatureModeInputs = wizardRoot.querySelectorAll('input[name="signature_mode"]');

    if (!form || !steps.length || !prevButton || !nextButton || !stepLabel || !progressFill) {
        return;
    }

    let currentStep = 1;
    const totalStep = steps.length;

    const updateSignatureMode = () => {
        const modeInput = wizardRoot.querySelector('input[name="signature_mode"]:checked');
        const isDigital = modeInput?.value !== 'basah';
        const signatureFileInput = wizardRoot.querySelector('input[name="tanda_tangan"]');

        if (signatureUploadWrap) {
            signatureUploadWrap.hidden = !isDigital;
        }

        if (signatureManualWrap) {
            signatureManualWrap.hidden = isDigital;
        }

        if (signatureFileInput) {
            signatureFileInput.required = isDigital;
            if (!isDigital) {
                signatureFileInput.value = '';
            }
        }
    };

    const updateWizardUI = () => {
        steps.forEach((step) => {
            const isActive = Number(step.dataset.step) === currentStep;
            step.classList.toggle('active', isActive);
        });

        const progress = (currentStep / totalStep) * 100;
        progressFill.style.width = `${progress}%`;
        stepLabel.textContent = `LANGKAH ${currentStep}`;

        prevButton.disabled = currentStep === 1;

        if (currentStep === totalStep) {
            nextButton.classList.add('final');
            nextButton.innerHTML = 'Kirim Data';
        } else {
            nextButton.classList.remove('final');
            nextButton.innerHTML = `Lanjut
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>`;
        }
    };

    const validateCurrentStep = () => {
        const currentStepEl = steps.find((step) => Number(step.dataset.step) === currentStep);
        if (!currentStepEl) {
            return true;
        }

        const fields = currentStepEl.querySelectorAll('input, select, textarea');
        for (const field of fields) {
            if (!field.checkValidity()) {
                field.reportValidity();
                return false;
            }
        }

        return true;
    };

    prevButton.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep -= 1;
            updateWizardUI();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    nextButton.addEventListener('click', () => {
        if (!validateCurrentStep()) {
            return;
        }

        if (currentStep < totalStep) {
            currentStep += 1;
            updateWizardUI();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        // Final submit is kept on-page; backend save endpoint will be added next.
        const formData = new FormData(form);
        console.info('Checklist form data prepared:', Object.fromEntries(formData.entries()));
        alert('Data checklist siap dikirim. Endpoint simpan data akan kita sambungkan di tahap berikutnya.');
    });

    signatureModeInputs.forEach((input) => {
        input.addEventListener('change', updateSignatureMode);
    });

    wizardRoot.querySelectorAll('[data-photo-single]').forEach((input) => {
        input.addEventListener('change', () => {
            const wrapper = input.closest('.checklist-photo-slot');
            const fileNameEl = wrapper?.querySelector('[data-file-name]');
            const hasFile = Boolean(input.files && input.files.length);

            if (fileNameEl) {
                fileNameEl.textContent = hasFile ? input.files[0].name : 'Belum dipilih';
            }

            wrapper?.classList.toggle('has-file', hasFile);
        });
    });

    wizardRoot.querySelectorAll('[data-photo-multi]').forEach((input) => {
        input.addEventListener('change', () => {
            const maxFiles = Number(input.dataset.maxFiles || 3);
            const wrap = input.closest('.checklist-photo-multi');
            const info = wrap?.querySelector('[data-file-multi-info]');
            const totalFile = input.files?.length ?? 0;

            if (totalFile > maxFiles && input.files) {
                const dt = new DataTransfer();
                Array.from(input.files).slice(0, maxFiles).forEach((file) => dt.items.add(file));
                input.files = dt.files;
            }

            const finalTotal = input.files?.length ?? 0;
            if (info) {
                info.textContent = finalTotal
                    ? `${finalTotal} file dipilih (maks ${maxFiles}).`
                    : `Pilih maksimal ${maxFiles} gambar.`;
            }

            wrap?.classList.toggle('invalid', finalTotal > maxFiles);
        });
    });

    const bbmRange = wizardRoot.querySelector('[data-bbm-range]');
    const bbmNumber = wizardRoot.querySelector('[data-bbm-number]');
    if (bbmRange && bbmNumber) {
        bbmRange.addEventListener('input', () => {
            bbmNumber.value = bbmRange.value;
        });

        bbmNumber.addEventListener('input', () => {
            const value = Math.min(100, Math.max(0, Number(bbmNumber.value || 0)));
            bbmNumber.value = String(value);
            bbmRange.value = String(value);
        });
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
    });

    updateSignatureMode();
    updateWizardUI();
});
