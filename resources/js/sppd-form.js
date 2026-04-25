import SignaturePad from 'signature_pad';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-sppd-form]');
    if (!root) return;

    const form = document.getElementById('sppd-form');
    const nopol = document.getElementById('sppd-nopol');
    const jenis = document.getElementById('sppd-jenis');
    const tollsWrap = document.getElementById('sppd-tolls-wrap');
    const fuelsWrap = document.getElementById('sppd-fuels-wrap');
    const addToll = document.getElementById('sppd-add-toll');
    const addFuel = document.getElementById('sppd-add-fuel');
    const sumTol = document.getElementById('sppd-sum-tol');
    const sumBbm = document.getElementById('sppd-sum-bbm');
    const sumGrand = document.getElementById('sppd-sum-grand');
    const preview = document.getElementById('sppd-live-preview');
    const sigCanvas = document.getElementById('sppd-sig-pad');
    const sigData = document.getElementById('sppd-sig-data');
    const sigClear = document.getElementById('sppd-sig-clear');
    const sigHint = root.querySelector('[data-sppd-sig-hint]');
    const submitBtn = document.getElementById('sppd-submit');
    const stepLabel = document.getElementById('sppd-step-label');
    const progressPct = document.getElementById('sppd-progress-pct');
    const progressFill = document.getElementById('sppd-progress-fill');
    const prevBtn = document.getElementById('sppd-prev');
    const nextBtn = document.getElementById('sppd-next');
    const steps = Array.from(root.querySelectorAll('[data-sppd-step]'));
    let currentStep = 1;

    let tollIdx = tollsWrap.querySelectorAll('[data-toll-row]').length;
    let fuelIdx = fuelsWrap.querySelectorAll('[data-fuel-row]').length;

    if (nopol && jenis) {
        const syncJenis = () => {
            const opt = nopol.options[nopol.selectedIndex];
            jenis.value = opt?.dataset?.jenis || '';
        };
        nopol.addEventListener('change', syncJenis);
        syncJenis();
    }

    const formatRp = (n) => {
        const x = Number(n) || 0;
        return 'Rp ' + x.toLocaleString('id-ID');
    };

    const recalcTotals = () => {
        let tTol = 0;
        tollsWrap.querySelectorAll('.sppd-toll-harga').forEach((inp) => {
            tTol += Number(inp.value) || 0;
        });
        let tBbm = 0;
        fuelsWrap.querySelectorAll('[data-fuel-row]').forEach((row) => {
            const l = Number(row.querySelector('.sppd-fuel-liter')?.value) || 0;
            const h = Number(row.querySelector('.sppd-fuel-hpl')?.value) || 0;
            const tot = Math.round(l * h * 100) / 100;
            const disp = row.querySelector('.sppd-fuel-total-display');
            if (disp) disp.value = formatRp(tot);
            tBbm += tot;
        });
        sumTol.textContent = formatRp(tTol);
        sumBbm.textContent = formatRp(tBbm);
        sumGrand.textContent = formatRp(tTol + tBbm);
        updatePreview(tTol + tBbm);
    };

    const updatePreview = (grand) => {
        const kd = form.querySelector('[name="keperluan_dinas"]')?.value || '—';
        const tg = form.querySelector('[name="tanggal_dinas"]')?.value || '—';
        const np = nopol?.value || '—';
        const tj = form.querySelector('[name="tujuan"]')?.value || '—';
        preview.innerHTML = `
            <p><strong>${escapeHtml(kd)}</strong> · ${escapeHtml(tg)}</p>
            <p>Nopol: <strong>${escapeHtml(np)}</strong> (${escapeHtml(jenis?.value || '—')})</p>
            <p>Tujuan: ${escapeHtml(tj)}</p>
            <p class="sppd-preview-grand">${formatRp(grand)}</p>
        `;
    };

    const escapeHtml = (s) => {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    };

    const showResult = (ok, title, msg, buttons) => {
        const overlay = document.getElementById('sppd-result-modal');
        const icon = document.getElementById('sppd-result-icon');
        const act = document.getElementById('sppd-result-actions');
        const titleEl = document.getElementById('sppd-result-title');
        const msgEl = document.getElementById('sppd-result-msg');
        titleEl.textContent = title;
        const t = (msg && String(msg).trim()) || '';
        msgEl.textContent = t;
        msgEl.style.display = t ? 'block' : 'none';
        icon.className = 'modal-icon ' + (ok ? 'success' : 'error');
        icon.innerHTML = ok
            ? '<svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/></svg>'
            : '<svg width="32" height="32" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
        act.innerHTML = '';
        (buttons || []).forEach((btn) => {
            if (btn.href) {
                const a = document.createElement('a');
                a.href = btn.href;
                a.target = btn.target || '_self';
                a.className = `modal-btn ${btn.class || 'modal-btn-success'}`;
                a.textContent = btn.label;
                a.addEventListener('click', () => {
                    overlay.style.display = 'none';
                });
                act.appendChild(a);
            } else {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = `modal-btn ${btn.class || 'modal-btn-secondary'}`;
                b.textContent = btn.label;
                b.addEventListener('click', () => {
                    overlay.style.display = 'none';
                    if (btn.action === 'dashboard') {
                        window.location.href = '/dashboard';
                    } else if (typeof btn.onClick === 'function') {
                        btn.onClick();
                    }
                });
                act.appendChild(b);
            }
        });
        overlay.style.display = 'flex';
    };

    const showErrorModal = (title, message) => {
        showResult(false, title || 'Perhatian', message || '', [{ label: 'OK', class: 'modal-btn-secondary' }]);
    };

    const validateTolls = () => {
        const rows = Array.from(tollsWrap.querySelectorAll('[data-toll-row]'));
        if (rows.length === 0) {
            showErrorModal('Biaya tol', 'Tambahkan minimal satu baris biaya tol dan isi baris pertama (Dari, Ke, Harga).');
            return false;
        }
        const first = rows[0];
        const d0 = first.querySelector('input[name*="[dari_tol]"]')?.value?.trim() ?? '';
        const k0 = first.querySelector('input[name*="[ke_tol]"]')?.value?.trim() ?? '';
        const h0 = first.querySelector('.sppd-toll-harga')?.value;
        if (!d0 || !k0 || h0 === '' || h0 === null) {
            showErrorModal('Biaya tol', 'Baris tol pertama wajib diisi lengkap: Dari Tol, Ke Tol, dan Harga (Rp).');
            first.querySelector('input[name*="[dari_tol]"]')?.focus();
            return false;
        }
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const d = row.querySelector('input[name*="[dari_tol]"]')?.value?.trim() ?? '';
            const k = row.querySelector('input[name*="[ke_tol]"]')?.value?.trim() ?? '';
            const h = row.querySelector('.sppd-toll-harga')?.value;
            const any = Boolean(d || k || (h !== '' && h != null));
            if (!any) continue;
            if (!d || !k || h === '' || h == null) {
                showErrorModal(
                    'Biaya tol',
                    `Baris tol tambahan (ke-${i + 1}): jika diisi, lengkapi Dari Tol, Ke Tol, dan Harga.`
                );
                return false;
            }
        }
        return true;
    };

    const hasFuelFile = (row, kind) => {
        const isOdo = kind === 'odo';
        const fileSel = isOdo ? 'input[type="file"][name*="[odometer]"]' : 'input[type="file"][name*="[struk]"]';
        const exSel = isOdo
            ? 'input[type="hidden"][name*="[odometer_existing]"]'
            : 'input[type="hidden"][name*="[struk_existing]"]';
        const f = row.querySelector(fileSel);
        const h = row.querySelector(exSel);
        return (f?.files?.length > 0) || (h && String(h.value || '').trim() !== '');
    };

    const validateFuels = () => {
        const rows = Array.from(fuelsWrap.querySelectorAll('[data-fuel-row]'));
        if (rows.length === 0) {
            showErrorModal('BBM', 'Tambahkan minimal satu baris BBM dan isi baris pertama.');
            return false;
        }
        const first = rows[0];
        const l0 = first.querySelector('.sppd-fuel-liter')?.value;
        const hp0 = first.querySelector('.sppd-fuel-hpl')?.value;
        if (l0 === '' || l0 == null) {
            showErrorModal('BBM', 'Baris BBM pertama wajib diisi: Liter, Harga per Liter, foto odometer, dan foto struk.');
            first.querySelector('.sppd-fuel-liter')?.focus();
            return false;
        }
        if (hp0 === '' || hp0 == null) {
            showErrorModal('BBM', 'Baris BBM pertama wajib diisi: Liter, Harga per Liter, foto odometer, dan foto struk.');
            return false;
        }
        if (!hasFuelFile(first, 'odo') || !hasFuelFile(first, 'struk')) {
            showErrorModal('BBM', 'Baris BBM pertama wajib memuat foto odometer dan foto struk.');
            return false;
        }
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const l = row.querySelector('.sppd-fuel-liter')?.value;
            const hp = row.querySelector('.sppd-fuel-hpl')?.value;
            const lStr = l !== undefined && l !== null ? String(l).trim() : '';
            const hpStr = hp !== undefined && hp !== null ? String(hp).trim() : '';
            const hasL = lStr !== '';
            const hasHp = hpStr !== '';
            const hasOdo = hasFuelFile(row, 'odo');
            const hasStruk = hasFuelFile(row, 'struk');
            const any = hasL || hasHp || hasOdo || hasStruk;
            if (!any) continue;
            if (!hasL || !hasHp) {
                showErrorModal(
                    'BBM',
                    `Baris BBM tambahan (ke-${i + 1}): lengkapi Liter dan Harga per Liter jika baris ini dipakai.`
                );
                return false;
            }
            if (!hasOdo || !hasStruk) {
                showErrorModal(
                    'BBM',
                    `Baris BBM tambahan (ke-${i + 1}): sertakan foto odometer dan struk.`
                );
                return false;
            }
        }
        return true;
    };

    const validateVisibleStep = () => {
        const visible = steps.find((s) => s.classList.contains('active'));
        if (!visible) return true;
        const stepNum = parseInt(visible.getAttribute('data-sppd-step'), 10);
        if (stepNum === 2) {
            return validateTolls();
        }
        if (stepNum === 3) {
            return validateFuels();
        }
        const required = visible.querySelectorAll('input[required],select[required],textarea[required]');
        for (const field of required) {
            if (field.disabled) continue;
            if ((field.type === 'file' && !field.files?.length && !visible.querySelector(`input[name="${field.name.replace(/\]$/, '_existing]')}"]`)) ||
                (field.type !== 'file' && !String(field.value || '').trim())) {
                field.reportValidity?.();
                field.focus?.();
                return false;
            }
        }
        return true;
    };

    const updateStepUi = () => {
        const total = steps.length || 1;
        steps.forEach((step, idx) => {
            step.classList.toggle('active', idx + 1 === currentStep);
        });
        const pct = Math.round((currentStep / total) * 100);
        if (stepLabel) stepLabel.textContent = `LANGKAH ${currentStep} DARI ${total}`;
        if (progressPct) progressPct.textContent = `${pct}%`;
        if (progressFill) progressFill.style.width = `${pct}%`;
        if (prevBtn) prevBtn.disabled = currentStep === 1;
        if (nextBtn) nextBtn.style.display = currentStep >= total ? 'none' : 'inline-flex';
        if (submitBtn) submitBtn.style.display = currentStep >= total ? 'inline-flex' : 'none';
    };

    root.addEventListener('input', (e) => {
        if (e.target.matches('.sppd-toll-harga, .sppd-fuel-liter, .sppd-fuel-hpl')) recalcTotals();
    });
    root.addEventListener('change', (e) => {
        if (e.target.matches('[name="keperluan_dinas"],[name="tanggal_dinas"],[name="tujuan"]') || e.target === nopol) recalcTotals();
    });

    const reindexTolls = () => {
        tollsWrap.querySelectorAll('.sppd-toll-line').forEach((line, i) => {
            line.querySelectorAll('input[name^="tolls["]').forEach((inp) => {
                inp.name = inp.name.replace(/tolls\[\d+]/, `tolls[${i}]`);
            });
        });
        tollIdx = tollsWrap.querySelectorAll('.sppd-toll-line').length;
    };

    const reindexFuels = () => {
        fuelsWrap.querySelectorAll('.sppd-fuel-line').forEach((line, i) => {
            line.querySelectorAll('input[name]').forEach((inp) => {
                if (inp.name && inp.name.startsWith('fuels[')) {
                    inp.name = inp.name.replace(/fuels\[\d+]/, `fuels[${i}]`);
                }
            });
        });
        fuelIdx = fuelsWrap.querySelectorAll('.sppd-fuel-line').length;
    };

    tollsWrap.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-remove-toll]');
        if (!btn) return;
        if (tollsWrap.querySelectorAll('.sppd-toll-line').length <= 1) return;
        btn.closest('.sppd-toll-line')?.remove();
        reindexTolls();
        recalcTotals();
    });

    fuelsWrap.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-remove-fuel]');
        if (!btn) return;
        if (fuelsWrap.querySelectorAll('.sppd-fuel-line').length <= 1) return;
        btn.closest('.sppd-fuel-line')?.remove();
        reindexFuels();
        recalcTotals();
    });

    addToll?.addEventListener('click', () => {
        const line = document.createElement('div');
        line.className = 'sppd-toll-line';
        line.dataset.tollRow = '';
        line.innerHTML = `
            <div class="sppd-row sppd-toll-inputs">
                <label class="checklist-field"><span>Dari Tol</span><div class="checklist-control-wrap"><input type="text" name="tolls[${tollIdx}][dari_tol]"></div></label>
                <label class="checklist-field"><span>Ke Tol</span><div class="checklist-control-wrap"><input type="text" name="tolls[${tollIdx}][ke_tol]"></div></label>
                <label class="checklist-field"><span>Harga</span><div class="checklist-control-wrap"><input type="number" name="tolls[${tollIdx}][harga]" class="sppd-toll-harga" min="0" step="1"></div></label>
            </div>
            <button type="button" class="sppd-line-remove" data-remove-toll title="Hapus baris tol" aria-label="Hapus baris tol"><i class="bi bi-dash-lg"></i></button>
        `;
        tollsWrap.appendChild(line);
        tollIdx += 1;
    });

    addFuel?.addEventListener('click', () => {
        const line = document.createElement('div');
        line.className = 'sppd-fuel-line';
        line.innerHTML = `
            <div class="sppd-fuel-block" data-fuel-row>
            <div class="sppd-row">
                <label class="checklist-field"><span>Liter</span><div class="checklist-control-wrap"><input type="number" name="fuels[${fuelIdx}][liter]" class="sppd-fuel-liter" min="0" step="0.01"></div></label>
                <label class="checklist-field"><span>Harga / Liter</span><div class="checklist-control-wrap"><input type="number" name="fuels[${fuelIdx}][harga_per_liter]" class="sppd-fuel-hpl" min="0" step="1"></div></label>
                <label class="checklist-field"><span>Total</span><div class="checklist-control-wrap"><input type="text" class="sppd-fuel-total-display" readonly value="0"></div></label>
            </div>
            <div class="sppd-photo-pair">
                <label class="checklist-photo-slot" data-photo-preview-slot>
                    <input type="file" name="fuels[${fuelIdx}][odometer]" accept="image/*" data-photo-single>
                    <div class="photo-slot-placeholder"><span class="checklist-photo-icon"><i class="bi bi-camera"></i></span><strong>Foto Odometer</strong></div>
                    <img class="photo-slot-preview" alt="" style="display:none">
                    <button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus">×</button>
                </label>
                <label class="checklist-photo-slot" data-photo-preview-slot>
                    <input type="file" name="fuels[${fuelIdx}][struk]" accept="image/*" data-photo-single>
                    <div class="photo-slot-placeholder"><span class="checklist-photo-icon"><i class="bi bi-receipt"></i></span><strong>Foto Struk</strong></div>
                    <img class="photo-slot-preview" alt="" style="display:none">
                    <button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus">×</button>
                </label>
            </div>
            </div>
            <button type="button" class="sppd-line-remove sppd-line-remove--fuel" data-remove-fuel title="Hapus baris BBM" aria-label="Hapus baris BBM"><i class="bi bi-dash-lg"></i></button>
        `;
        fuelsWrap.appendChild(line);
        line.querySelectorAll('[data-photo-preview-slot]').forEach(initPhotoSlot);
        fuelIdx += 1;
        recalcTotals();
    });

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

    function initPhotoSlot(slot) {
        const input = slot.querySelector('[data-photo-single]');
        const previewImg = slot.querySelector('.photo-slot-preview');
        const placeholder = slot.querySelector('.photo-slot-placeholder');
        const removeBtn = slot.querySelector('.photo-slot-remove');
        if (!input || !previewImg) return;
        input.setAttribute('capture', 'environment');
        input.setAttribute('accept', 'image/*');

        const openCameraCapture = async () => {
            const modal = ensureCameraModal();
            const video = modal.querySelector('#camera-capture-video');
            const btnShoot = modal.querySelector('[data-camera-shoot]');
            const btnCancel = modal.querySelector('[data-camera-cancel]');
            let stream = null;
            const closeModal = () => {
                modal.style.display = 'none';
                if (stream) {
                    stream.getTracks().forEach((t) => t.stop());
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
            } catch {
                showErrorModal('Kamera', 'Kamera tidak dapat diakses. Pastikan izin kamera diaktifkan.');
                return;
            }
            btnCancel.onclick = () => closeModal();
            btnShoot.onclick = () => {
                const canvas = document.createElement('canvas');
                const w = video.videoWidth || 1280;
                const h = video.videoHeight || 720;
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, w, h);
                canvas.toBlob((blob) => {
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
                    previewImg.src = ev.target.result;
                    previewImg.style.display = 'block';
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
                previewImg.style.display = 'none';
                previewImg.src = '';
                if (placeholder) placeholder.style.display = 'flex';
                removeBtn.style.display = 'none';
                slot.classList.remove('has-file');
                slot.querySelectorAll('input[type="hidden"]').forEach((h) => {
                    if (h.name.includes('existing')) h.remove();
                });
            });
        }
    }

    root.querySelectorAll('[data-photo-preview-slot]').forEach(initPhotoSlot);

    let sigPad = null;
    if (sigCanvas) {
        const resize = () => {
            const rect = sigCanvas.getBoundingClientRect();
            if (!rect.width || !rect.height) return false;
            const r = Math.max(window.devicePixelRatio || 1, 1);
            sigCanvas.width = rect.width * r;
            sigCanvas.height = rect.height * r;
            const ctx = sigCanvas.getContext('2d');
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.scale(r, r);
            return true;
        };
        resize();
        sigPad = new SignaturePad(sigCanvas, { backgroundColor: 'rgba(255,255,255,0)', penColor: '#0f172a', minWidth: 1.5, maxWidth: 3 });
        sigPad.addEventListener('beginStroke', () => sigHint?.classList.add('hidden'));
        sigClear?.addEventListener('click', () => {
            sigPad.clear();
            sigHint?.classList.remove('hidden');
            sigData.value = '';
        });
        let rt;
        window.addEventListener('resize', () => {
            clearTimeout(rt);
            rt = setTimeout(() => {
                const data = sigPad.isEmpty() ? [] : sigPad.toData();
                if (!resize()) return;
                sigPad.clear();
                if (data.length) sigPad.fromData(data);
                else sigHint?.classList.remove('hidden');
            }, 200);
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!validateTolls() || !validateFuels()) {
            return;
        }
        if (sigPad && !sigPad.isEmpty()) {
            sigData.value = sigPad.toDataURL();
        }
        if (!sigData.value || !String(sigData.value).startsWith('data:image')) {
            showErrorModal('Tanda tangan', 'Mohon beri tanda tangan terlebih dahulu.');
            return;
        }
        const fd = new FormData(form);
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        submitBtn.disabled = true;
        const prev = submitBtn.textContent;
        submitBtn.textContent = 'Memproses…';
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: fd,
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.success) {
                const listUrl = data.redirect || `${window.location.origin}/sppd`;
                showResult(true, 'Rekap SPPD Tersimpan!', '', [
                    { href: listUrl, label: '📋 Ke Daftar Rekap', class: 'modal-btn-success' },
                ]);
            } else {
                showResult(false, 'Gagal', data.message || 'Validasi gagal.', [{ label: 'OK', class: 'modal-btn-secondary' }]);
                submitBtn.disabled = false;
                submitBtn.textContent = prev;
            }
        } catch {
            showResult(false, 'Koneksi Bermasalah', 'Terjadi kesalahan jaringan. Silakan coba lagi.', [{ label: 'OK', class: 'modal-btn-secondary' }]);
            submitBtn.disabled = false;
            submitBtn.textContent = prev;
        }
    });

    prevBtn?.addEventListener('click', () => {
        if (currentStep <= 1) return;
        currentStep -= 1;
        updateStepUi();
    });
    nextBtn?.addEventListener('click', () => {
        if (!validateVisibleStep()) return;
        if (currentStep >= steps.length) return;
        currentStep += 1;
        updateStepUi();
    });

    recalcTotals();
    updateStepUi();
});
