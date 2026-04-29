document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-sppd-form]');
    if (!root) return;

    const form = document.getElementById('sppd-form');
    const nopol = document.getElementById('sppd-nopol');
    const jenis = document.getElementById('sppd-jenis');
    const tollsBerWrap = document.getElementById('sppd-tolls-berangkat-wrap');
    const tollsKemWrap = document.getElementById('sppd-tolls-kembali-wrap');
    const fuelsWrap = document.getElementById('sppd-fuels-wrap');
    const addTollBer = document.getElementById('sppd-add-toll-berangkat');
    const addTollKem = document.getElementById('sppd-add-toll-kembali');
    const addFuel = document.getElementById('sppd-add-fuel');
    const sumTol = document.getElementById('sppd-sum-tol');
    const sumBbm = document.getElementById('sppd-sum-bbm');
    const sumGrand = document.getElementById('sppd-sum-grand');
    const step4Summary = document.getElementById('sppd-step4-summary');
    const submitBtn = document.getElementById('sppd-submit');
    const stepLabel = document.getElementById('sppd-step-label');
    const progressPct = document.getElementById('sppd-progress-pct');
    const progressFill = document.getElementById('sppd-progress-fill');
    const prevBtn = document.getElementById('sppd-prev');
    const nextBtn = document.getElementById('sppd-next');
    const steps = Array.from(root.querySelectorAll('[data-sppd-step]'));
    let currentStep = 1;

    let tollBerIdx = tollsBerWrap.querySelectorAll('[data-toll-row]').length;
    let tollKemIdx = tollsKemWrap.querySelectorAll('[data-toll-row]').length;
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

    const escapeHtml = (s) => {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    };

    const formatTanggalId = (ymd) => {
        if (!ymd || !/^\d{4}-\d{2}-\d{2}$/.test(String(ymd))) {
            return ymd ? escapeHtml(String(ymd)) : '—';
        }
        const [y, m, d] = String(ymd).split('-').map(Number);
        const dt = new Date(y, m - 1, d);
        if (Number.isNaN(dt.getTime())) return escapeHtml(String(ymd));
        return escapeHtml(
            dt.toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            })
        );
    };

    const collectTollsFromWrap = (wrap) =>
        Array.from(wrap.querySelectorAll('[data-toll-row]'))
            .map((row) => {
                const dari = row.querySelector('input[name*="[dari_tol]"]')?.value?.trim() ?? '';
                const ke = row.querySelector('input[name*="[ke_tol]"]')?.value?.trim() ?? '';
                const harga = Number(row.querySelector('.sppd-toll-harga')?.value) || 0;
                return { dari, ke, harga };
            })
            .filter((t) => t.dari || t.ke || t.harga > 0);

    const collectFuels = () =>
        Array.from(fuelsWrap.querySelectorAll('[data-fuel-row]'))
            .map((row) => {
                const lStr = row.querySelector('.sppd-fuel-liter')?.value?.trim() ?? '';
                const hStr = row.querySelector('.sppd-fuel-hpl')?.value?.trim() ?? '';
                const liter = Number(lStr) || 0;
                const hpl = Number(hStr) || 0;
                const sub = Math.round(liter * hpl * 100) / 100;
                return { lStr, hStr, liter, hpl, sub };
            })
            .filter((f) => f.lStr !== '' || f.hStr !== '');

    const renderStep4Summary = () => {
        if (!step4Summary) return;

        const nama = form.querySelector('[name="nama_driver_display"]')?.value?.trim() || '—';
        const tgRaw = form.querySelector('[name="tanggal_dinas"]')?.value || '';
        const keperluan = form.querySelector('[name="keperluan_dinas"]')?.value?.trim() || '—';
        const np = nopol?.value?.trim() || '—';
        const jn = jenis?.value?.trim() || '—';
        const tujuan = form.querySelector('[name="tujuan"]')?.value?.trim() || '—';

        const tollsBer = collectTollsFromWrap(tollsBerWrap);
        const tollsKem = collectTollsFromWrap(tollsKemWrap);
        const rowsBer =
            tollsBer.length === 0
                ? `<tr><td colspan="4" class="sppd-summary-table-empty">Tidak ada rincian.</td></tr>`
                : tollsBer
                      .map(
                          (t, i) =>
                              `<tr><td>${i + 1}</td><td>${escapeHtml(t.dari) || '—'}</td><td>${escapeHtml(t.ke) || '—'}</td><td class="sppd-summary-num">${formatRp(t.harga)}</td></tr>`
                      )
                      .join('');
        const rowsKem =
            tollsKem.length === 0
                ? `<tr><td colspan="4" class="sppd-summary-table-empty">Tidak ada rincian.</td></tr>`
                : tollsKem
                      .map(
                          (t, i) =>
                              `<tr><td>${i + 1}</td><td>${escapeHtml(t.dari) || '—'}</td><td>${escapeHtml(t.ke) || '—'}</td><td class="sppd-summary-num">${formatRp(t.harga)}</td></tr>`
                      )
                      .join('');

        const fuels = collectFuels();
        const fuelRows =
            fuels.length === 0
                ? `<tr><td colspan="4" class="sppd-summary-table-empty">—</td></tr>`
                : fuels
                      .map(
                          (f, i) =>
                              `<tr><td>${i + 1}</td><td class="sppd-summary-num">${escapeHtml(f.lStr) || '—'}</td><td class="sppd-summary-num">${formatRp(f.hpl)}</td><td class="sppd-summary-num">${formatRp(f.sub)}</td></tr>`
                      )
                      .join('');

        const tujuanHtml = String(tujuan)
            .split('\n')
            .map((ln) => escapeHtml(ln))
            .join('<br>');

        step4Summary.innerHTML = `
            <div class="sppd-summary-card">
                <h3 class="sppd-summary-card-title">Data perjalanan</h3>
                <dl class="sppd-summary-dl">
                    <div><dt>Nama driver</dt><dd>${escapeHtml(nama)}</dd></div>
                    <div><dt>Tanggal dinas</dt><dd>${formatTanggalId(tgRaw)}</dd></div>
                    <div class="sppd-summary-dl-span2"><dt>Keperluan dinas</dt><dd>${escapeHtml(keperluan)}</dd></div>
                    <div><dt>Nomor kendaraan</dt><dd>${escapeHtml(np)}</dd></div>
                    <div><dt>Jenis kendaraan</dt><dd>${escapeHtml(jn)}</dd></div>
                    <div class="sppd-summary-dl-span2"><dt>Tujuan</dt><dd class="sppd-summary-multiline">${tujuanHtml}</dd></div>
                </dl>
            </div>
            <div class="sppd-summary-card">
                <h3 class="sppd-summary-card-title">Biaya tol</h3>
                <p class="sppd-summary-subhead">Berangkat</p>
                <div class="sppd-summary-table-wrap">
                    <table class="sppd-summary-table">
                        <thead><tr><th>#</th><th>Dari tol</th><th>Ke tol</th><th>Harga</th></tr></thead>
                        <tbody>${rowsBer}</tbody>
                    </table>
                </div>
                <p class="sppd-summary-subhead sppd-summary-subhead--spaced">Kembali</p>
                <div class="sppd-summary-table-wrap">
                    <table class="sppd-summary-table">
                        <thead><tr><th>#</th><th>Dari tol</th><th>Ke tol</th><th>Harga</th></tr></thead>
                        <tbody>${rowsKem}</tbody>
                    </table>
                </div>
            </div>
            <div class="sppd-summary-card">
                <h3 class="sppd-summary-card-title">BBM</h3>
                <div class="sppd-summary-table-wrap">
                    <table class="sppd-summary-table">
                        <thead><tr><th>#</th><th>Liter</th><th>Harga / L</th><th>Subtotal</th></tr></thead>
                        <tbody>${fuelRows}</tbody>
                    </table>
                </div>
            </div>
        `;
    };

    const recalcTotals = () => {
        let tTol = 0;
        root.querySelectorAll('.sppd-toll-harga').forEach((inp) => {
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
        if (currentStep === 4) {
            renderStep4Summary();
        }
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

    const validateTollSection = (wrap, labelHuman) => {
        const rows = Array.from(wrap.querySelectorAll('[data-toll-row]'));
        if (rows.length === 0) {
            showErrorModal('Biaya tol', `Tambahkan minimal satu baris biaya tol ${labelHuman}.`);
            return false;
        }
        const first = rows[0];
        const d0 = first.querySelector('input[name*="[dari_tol]"]')?.value?.trim() ?? '';
        const k0 = first.querySelector('input[name*="[ke_tol]"]')?.value?.trim() ?? '';
        const h0 = first.querySelector('.sppd-toll-harga')?.value;
        if (!d0 || !k0 || h0 === '' || h0 === null) {
            showErrorModal(
                'Biaya tol',
                `Baris pertama biaya tol ${labelHuman} wajib diisi lengkap: Dari Tol, Ke Tol, dan Harga (Rp).`
            );
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
                    `Baris tol ${labelHuman} tambahan (ke-${i + 1}): jika diisi, lengkapi Dari Tol, Ke Tol, dan Harga.`
                );
                return false;
            }
        }
        return true;
    };

    const validateTolls = () => validateTollSection(tollsBerWrap, 'berangkat') && validateTollSection(tollsKemWrap, 'kembali');

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
            showErrorModal('BBM', 'Baris BBM pertama wajib diisi: Liter dan Harga per Liter.');
            first.querySelector('.sppd-fuel-liter')?.focus();
            return false;
        }
        if (hp0 === '' || hp0 == null) {
            showErrorModal('BBM', 'Baris BBM pertama wajib diisi: Liter dan Harga per Liter.');
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
            const any = hasL || hasHp;
            if (!any) continue;
            if (!hasL || !hasHp) {
                showErrorModal(
                    'BBM',
                    `Baris BBM tambahan (ke-${i + 1}): lengkapi Liter dan Harga per Liter jika baris ini dipakai.`
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
            if (
                (field.type === 'file' &&
                    !field.files?.length &&
                    !visible.querySelector(`input[name="${field.name.replace(/\]$/, '_existing]')}"]`)) ||
                (field.type !== 'file' && !String(field.value || '').trim())
            ) {
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
        if (currentStep === 4) {
            renderStep4Summary();
        }
    };

    root.addEventListener('input', (e) => {
        if (e.target.matches('.sppd-toll-harga, .sppd-fuel-liter, .sppd-fuel-hpl')) {
            recalcTotals();
            return;
        }
        if (e.target.matches('[name="keperluan_dinas"],[name="tujuan"]') && currentStep === 4) {
            renderStep4Summary();
        }
    });
    root.addEventListener('change', (e) => {
        if (e.target.matches('[name="keperluan_dinas"],[name="tanggal_dinas"],[name="tujuan"]') || e.target === nopol) {
            recalcTotals();
        }
    });

    const reindexTollSection = (wrap, baseName) => {
        const esc = baseName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        wrap.querySelectorAll('.sppd-toll-line').forEach((line, i) => {
            line.querySelectorAll('input[name]').forEach((inp) => {
                if (inp.name && inp.name.startsWith(`${baseName}[`)) {
                    inp.name = inp.name.replace(new RegExp(`^${esc}\\[\\d+]`), `${baseName}[${i}]`);
                }
            });
        });
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

    const bindTollRemove = (wrap, baseName) => {
        wrap.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-remove-toll]');
            if (!btn || !wrap.contains(btn)) return;
            if (wrap.querySelectorAll('[data-toll-row]').length <= 1) return;
            btn.closest('.sppd-toll-line')?.remove();
            reindexTollSection(wrap, baseName);
            recalcTotals();
        });
    };
    bindTollRemove(tollsBerWrap, 'tolls_berangkat');
    bindTollRemove(tollsKemWrap, 'tolls_kembali');

    fuelsWrap.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-remove-fuel]');
        if (!btn) return;
        if (fuelsWrap.querySelectorAll('.sppd-fuel-line').length <= 1) return;
        btn.closest('.sppd-fuel-line')?.remove();
        reindexFuels();
        recalcTotals();
    });

    addTollBer?.addEventListener('click', () => {
        const line = document.createElement('div');
        line.className = 'sppd-toll-line';
        line.dataset.tollRow = '';
        line.innerHTML = `
            <div class="sppd-row sppd-toll-inputs">
                <label class="checklist-field"><span>Dari Tol</span><div class="checklist-control-wrap"><input type="text" name="tolls_berangkat[${tollBerIdx}][dari_tol]"></div></label>
                <label class="checklist-field"><span>Ke Tol</span><div class="checklist-control-wrap"><input type="text" name="tolls_berangkat[${tollBerIdx}][ke_tol]"></div></label>
                <label class="checklist-field"><span>Harga</span><div class="checklist-control-wrap"><input type="number" name="tolls_berangkat[${tollBerIdx}][harga]" class="sppd-toll-harga" min="0" step="1"></div></label>
            </div>
            <button type="button" class="sppd-line-remove" data-remove-toll title="Hapus baris tol" aria-label="Hapus baris tol"><i class="bi bi-dash-lg"></i></button>
        `;
        tollsBerWrap.appendChild(line);
        tollBerIdx += 1;
    });

    addTollKem?.addEventListener('click', () => {
        const line = document.createElement('div');
        line.className = 'sppd-toll-line';
        line.dataset.tollRow = '';
        line.innerHTML = `
            <div class="sppd-row sppd-toll-inputs">
                <label class="checklist-field"><span>Dari Tol</span><div class="checklist-control-wrap"><input type="text" name="tolls_kembali[${tollKemIdx}][dari_tol]"></div></label>
                <label class="checklist-field"><span>Ke Tol</span><div class="checklist-control-wrap"><input type="text" name="tolls_kembali[${tollKemIdx}][ke_tol]"></div></label>
                <label class="checklist-field"><span>Harga</span><div class="checklist-control-wrap"><input type="number" name="tolls_kembali[${tollKemIdx}][harga]" class="sppd-toll-harga" min="0" step="1"></div></label>
            </div>
            <button type="button" class="sppd-line-remove" data-remove-toll title="Hapus baris tol" aria-label="Hapus baris tol"><i class="bi bi-dash-lg"></i></button>
        `;
        tollsKemWrap.appendChild(line);
        tollKemIdx += 1;
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
            </div>
            <button type="button" class="sppd-line-remove sppd-line-remove--fuel" data-remove-fuel title="Hapus baris BBM" aria-label="Hapus baris BBM"><i class="bi bi-dash-lg"></i></button>
        `;
        fuelsWrap.appendChild(line);
        fuelIdx += 1;
        recalcTotals();
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!validateTolls() || !validateFuels()) {
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
