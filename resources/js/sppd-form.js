function initSppdForm() {
    const root = document.querySelector('[data-sppd-form]');
    if (!root || root.dataset.sppdBound) return;
    root.dataset.sppdBound = '1';

    const form = document.getElementById('sppd-form');
    const nopol = document.getElementById('sppd-nopol');
    const jenis = document.getElementById('sppd-jenis');
    const tollsBerWrap = document.getElementById('sppd-tolls-berangkat-wrap');
    const tollsKemWrap = document.getElementById('sppd-tolls-kembali-wrap');
    const parkingsWrap = document.getElementById('sppd-parkings-wrap');
    const addTollBer = document.getElementById('sppd-add-toll-berangkat');
    const addTollKem = document.getElementById('sppd-add-toll-kembali');
    const addParking = document.getElementById('sppd-add-parking');
    const sumTol = document.getElementById('sppd-sum-tol');
    const sumBbm = document.getElementById('sppd-sum-bbm');
    const sumGrand = document.getElementById('sppd-sum-grand');
    const step4Summary = document.getElementById('sppd-review-root');

    const swalFormTheme = () =>
        document.body.classList.contains('dark')
            ? {
                  background: '#0f172a',
                  color: '#e2e8f0',
                  iconColor: '#38bdf8',
                  confirmButtonColor: '#2563eb',
                  cancelButtonColor: '#475569',
              }
            : {};

    const stepLabel = document.getElementById('sppd-step-label');
    const progressPct = document.getElementById('sppd-progress-pct');
    const progressFill = document.getElementById('sppd-progress-fill');
    const nextBtn = document.getElementById('sppd-next');
    const submitBtn = document.getElementById('sppd-submit');

    let tollBerIdx = tollsBerWrap.querySelectorAll('[data-toll-row]').length;
    let tollKemIdx = tollsKemWrap.querySelectorAll('[data-toll-row]').length;
    let parkingIdx = parkingsWrap.querySelectorAll('[data-parking-row]').length;

    if (nopol && jenis) {
        const syncJenis = () => {
            const opt = nopol.options[nopol.selectedIndex];
            jenis.value = opt?.dataset?.jenis || '';
        };
        nopol.addEventListener('change', syncJenis);

        // Gunakan requestAnimationFrame + setTimeout sebagai fallback untuk memastikan
        // browser sudah selesai merender selectedIndex yang benar sebelum kita baca,
        // terutama saat halaman di-restore dari Turbo cache atau saat mode edit.
        const triggerSync = () => {
            syncJenis();
            if (!jenis.value) {
                setTimeout(syncJenis, 0);
            }
        };
        nopol.addEventListener('change', syncJenis);
        requestAnimationFrame(triggerSync);
    }

    const formatRp = (val) => 'Rp ' + (Number(val) || 0).toLocaleString('id-ID');

    const escapeHtml = (str) => {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    };

    const formatIndoDate = (val) => {
        if (!val || !/^\d{4}-\d{2}-\d{2}$/.test(String(val))) {
            return val ? escapeHtml(String(val)) : '—';
        }
        const [year, month, day] = String(val).split('-').map(Number);
        const date = new Date(year, month - 1, day);
        if (Number.isNaN(date.getTime())) {
            return escapeHtml(String(val));
        }
        return escapeHtml(
            date.toLocaleDateString('id-ID', {
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

    const collectParkings = () =>
        Array.from(parkingsWrap.querySelectorAll('[data-parking-row]'))
            .map((row) => {
                const lokasi = row.querySelector('.sppd-parking-lokasi')?.value?.trim() ?? '';
                const hStr = row.querySelector('.sppd-parking-biaya')?.value?.trim() ?? '';
                const biaya = Number(hStr) || 0;
                return { lokasi, hStr, biaya };
            })
            .filter((f) => f.lokasi !== '' || f.hStr !== '');

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

        const parkings = collectParkings();
        const totalParkir = parkings.reduce((sum, f) => sum + f.biaya, 0);
        const parkingRowsSummary =
            parkings.length === 0
                ? `<tr><td colspan="3" class="sppd-summary-table-empty">—</td></tr>`
                : parkings
                      .map(
                          (f, i) =>
                              `<tr><td>${i + 1}</td><td>${escapeHtml(f.lokasi) || '—'}</td><td class="sppd-summary-num">${formatRp(f.biaya)}</td></tr>`
                      )
                      .join('');

        const tujuanHtml = String(tujuan)
            .split('\n')
            .map((ln) => escapeHtml(ln))
            .join('<br>');

        step4Summary.innerHTML = `
            <div class="sppd-summary-card">
                <div class="sppd-summary-header">
                    <span class="sppd-summary-badge sppd-badge-info">Preview Data</span>
                </div>
                <div class="sppd-summary-grid">
                    <div class="sppd-summary-item">
                        <span class="sppd-summary-label">Driver</span>
                        <span class="sppd-summary-value">${escapeHtml(nama)}</span>
                    </div>
                    <div class="sppd-summary-item">
                        <span class="sppd-summary-label">Tanggal Dinas</span>
                        <span class="sppd-summary-value">${formatIndoDate(tgRaw)}</span>
                    </div>
                    <div class="sppd-summary-item sppd-summary-item-full">
                        <span class="sppd-summary-label">Keperluan Dinas</span>
                        <span class="sppd-summary-value">${escapeHtml(keperluan)}</span>
                    </div>
                    <div class="sppd-summary-item">
                        <span class="sppd-summary-label">No. Kendaraan</span>
                        <span class="sppd-summary-value">${escapeHtml(np)}</span>
                    </div>
                    <div class="sppd-summary-item">
                        <span class="sppd-summary-label">Jenis Kendaraan</span>
                        <span class="sppd-summary-value">${escapeHtml(jn)}</span>
                    </div>
                    <div class="sppd-summary-item sppd-summary-item-full">
                        <span class="sppd-summary-label">Lokasi Tujuan</span>
                        <span class="sppd-summary-value">${tujuanHtml}</span>
                    </div>
                </div>

                <div class="sppd-summary-section">
                    <h4 class="sppd-summary-section-title">Rincian Tol Berangkat</h4>
                    <table class="sppd-summary-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Dari Tol</th>
                                <th>Ke Tol</th>
                                <th style="width: 120px;" class="sppd-summary-num">Harga</th>
                            </tr>
                        </thead>
                        <tbody>${rowsBer}</tbody>
                    </table>
                </div>

                <div class="sppd-summary-section">
                    <h4 class="sppd-summary-section-title">Rincian Tol Kembali</h4>
                    <table class="sppd-summary-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Dari Tol</th>
                                <th>Ke Tol</th>
                                <th style="width: 120px;" class="sppd-summary-num">Harga</th>
                            </tr>
                        </thead>
                        <tbody>${rowsKem}</tbody>
                    </table>
                </div>

                <div class="sppd-summary-section">
                    <h4 class="sppd-summary-section-title">Rincian Parkir</h4>
                    <table class="sppd-summary-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Lokasi</th>
                                <th style="width: 150px;" class="sppd-summary-num">Biaya Parkir</th>
                            </tr>
                        </thead>
                        <tbody>${parkingRowsSummary}</tbody>
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
        parkingsWrap.querySelectorAll('[data-parking-row]').forEach((row) => {
            const h = Number(row.querySelector('.sppd-parking-biaya')?.value) || 0;
            tBbm += h;
        });
        const totalParkirDisp = document.getElementById('sppd-total-parkir-display');
        if (totalParkirDisp) totalParkirDisp.value = formatRp(tBbm);
        sumTol.textContent = formatRp(tTol);
        sumBbm.textContent = formatRp(tBbm);
        sumGrand.textContent = formatRp(tTol + tBbm);
        renderStep4Summary();
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
                        window.location.href = root.dataset.dashboardUrl || appBase('/dashboard');
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

    const validateParkings = () => {
        const rows = Array.from(parkingsWrap.querySelectorAll('[data-parking-row]'));
        if (rows.length === 0) {
            showErrorModal('Biaya Parkir', 'Tambahkan minimal satu baris parkir dan isi baris pertama.');
            return false;
        }
        const first = rows[0];
        const l0 = first.querySelector('.sppd-parking-lokasi')?.value;
        const hp0 = first.querySelector('.sppd-parking-biaya')?.value;
        if (l0 === '' || l0 == null || String(l0).trim() === '') {
            showErrorModal('Biaya Parkir', 'Baris pertama wajib diisi: Lokasi dan Biaya Parkir.');
            first.querySelector('.sppd-parking-lokasi')?.focus();
            return false;
        }
        if (hp0 === '' || hp0 == null) {
            showErrorModal('Biaya Parkir', 'Baris pertama wajib diisi: Lokasi dan Biaya Parkir.');
            return false;
        }
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const l = row.querySelector('.sppd-parking-lokasi')?.value;
            const hp = row.querySelector('.sppd-parking-biaya')?.value;
            const lStr = l !== undefined && l !== null ? String(l).trim() : '';
            const hpStr = hp !== undefined && hp !== null ? String(hp).trim() : '';
            const hasL = lStr !== '';
            const hasHp = hpStr !== '';
            const any = hasL || hasHp;
            if (!any) continue;
            if (!hasL || !hasHp) {
                showErrorModal(
                    'Biaya Parkir',
                    `Baris parkir tambahan (ke-${i + 1}): lengkapi Lokasi dan Biaya Parkir jika baris ini dipakai.`
                );
                return false;
            }
        }
        return true;
    };

    const validateRequiredFields = (container) => {
        const required = container.querySelectorAll('input[required],select[required],textarea[required]');
        for (const field of required) {
            if (field.disabled) continue;
            if (
                (field.type === 'file' &&
                    !field.files?.length &&
                    !container.querySelector(`input[name="${field.name.replace(/\]$/, '_existing]')}"]`)) ||
                (field.type !== 'file' && !String(field.value || '').trim())
            ) {
                field.reportValidity?.();
                field.focus?.();
                return false;
            }
        }
        return true;
    };

    const validateVisibleStep = () => {
        if (!validateRequiredFields(form)) return false;
        if (!validateTolls()) return false;
        return validateParkings();
    };

    /* ── Modal Preview SPPD ── */
    const openPreviewModal = () => {
        renderStep4Summary();
        const overlay = document.getElementById('sppd-preview-overlay');
        if (!overlay) return;
        overlay.style.display = 'flex';
        overlay.classList.remove('active');
        void overlay.offsetWidth;
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        const body = overlay.querySelector('.sppd-preview-modal-body');
        if (body) body.scrollTop = 0;
    };

    const closePreviewModal = () => {
        const overlay = document.getElementById('sppd-preview-overlay');
        if (!overlay) return;
        overlay.classList.remove('active');
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    };

    nextBtn?.addEventListener('click', () => {
        if (!validateVisibleStep()) return;
        openPreviewModal();
    });

    document.getElementById('sppd-preview-close')?.addEventListener('click', closePreviewModal);
    document.getElementById('sppd-preview-cancel')?.addEventListener('click', closePreviewModal);
    document.getElementById('sppd-preview-overlay')?.addEventListener('click', (e) => {
        if (e.target.id === 'sppd-preview-overlay') closePreviewModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const overlay = document.getElementById('sppd-preview-overlay');
            if (overlay && overlay.style.display !== 'none') closePreviewModal();
        }
    });

    root.addEventListener('input', (e) => {
        if (e.target.matches('.sppd-toll-harga, .sppd-parking-biaya')) {
            recalcTotals();
            return;
        }
        if (e.target.matches('.sppd-parking-lokasi')) {
            renderStep4Summary();
            return;
        }
        if (e.target.matches('[name="keperluan_dinas"],[name="tujuan"]')) {
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

    const reindexParkings = () => {
        parkingsWrap.querySelectorAll('.sppd-fuel-line').forEach((line, i) => {
            line.querySelectorAll('input[name]').forEach((inp) => {
                if (inp.name && inp.name.startsWith('parkings[')) {
                    inp.name = inp.name.replace(/parkings\[\d+]/, `parkings[${i}]`);
                }
            });
        });
        parkingIdx = parkingsWrap.querySelectorAll('.sppd-fuel-line').length;
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

    parkingsWrap.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-remove-fuel]');
        if (!btn) return;
        if (parkingsWrap.querySelectorAll('.sppd-fuel-line').length <= 1) return;
        btn.closest('.sppd-fuel-line')?.remove();
        reindexParkings();
        recalcTotals();
    });

    addTollBer?.addEventListener('click', () => {
        const line = document.createElement('div');
        line.className = 'sppd-toll-line';
        line.dataset.tollRow = '';
        line.innerHTML = `
            <div class="sppd-row sppd-toll-inputs">
                <label class="checklist-field"><div class="checklist-control-wrap"><input type="text" name="tolls_berangkat[${tollBerIdx}][dari_tol]" placeholder="Dari Tol"></div></label>
                <label class="checklist-field"><div class="checklist-control-wrap"><input type="text" name="tolls_berangkat[${tollBerIdx}][ke_tol]" placeholder="Ke Tol"></div></label>
                <label class="checklist-field"><div class="checklist-control-wrap"><input type="number" name="tolls_berangkat[${tollBerIdx}][harga]" class="sppd-toll-harga" min="0" step="1" placeholder="Harga"></div></label>
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
                <label class="checklist-field"><div class="checklist-control-wrap"><input type="text" name="tolls_kembali[${tollKemIdx}][dari_tol]" placeholder="Dari Tol"></div></label>
                <label class="checklist-field"><div class="checklist-control-wrap"><input type="text" name="tolls_kembali[${tollKemIdx}][ke_tol]" placeholder="Ke Tol"></div></label>
                <label class="checklist-field"><div class="checklist-control-wrap"><input type="number" name="tolls_kembali[${tollKemIdx}][harga]" class="sppd-toll-harga" min="0" step="1" placeholder="Harga"></div></label>
            </div>
            <button type="button" class="sppd-line-remove" data-remove-toll title="Hapus baris tol" aria-label="Hapus baris tol"><i class="bi bi-dash-lg"></i></button>
        `;
        tollsKemWrap.appendChild(line);
        tollKemIdx += 1;
    });

    addParking?.addEventListener('click', () => {
        const line = document.createElement('div');
        line.className = 'sppd-fuel-line';
        line.innerHTML = `
            <div class="sppd-fuel-block" data-parking-row>
                <div class="sppd-row sppd-toll-inputs sppd-parking-row">
                    <label class="checklist-field"><div class="checklist-control-wrap"><input type="text" name="parkings[${parkingIdx}][lokasi]" class="sppd-parking-lokasi" placeholder="Lokasi"></div></label>
                    <label class="checklist-field"><div class="checklist-control-wrap"><input type="number" name="parkings[${parkingIdx}][biaya_parkir]" class="sppd-parking-biaya" min="0" step="1" placeholder="Biaya Parkir"></div></label>
                </div>
            </div>
            <button type="button" class="sppd-line-remove sppd-line-remove--fuel" data-remove-fuel title="Hapus baris parkir" aria-label="Hapus baris parkir"><i class="bi bi-dash-lg"></i></button>
        `;
        parkingsWrap.appendChild(line);
        parkingIdx += 1;
        recalcTotals();
    });

    const submitReport = async () => {
        if (!submitBtn) return;
        const fd = new FormData(form);
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        submitBtn.disabled = true;
        const prevHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span>Memproses…</span>';
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: fd,
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.success) {
                const listUrl = data.redirect || root.dataset.sppdListUrl || appBase('/sppd');
                closePreviewModal();
                if (typeof Swal !== 'undefined') {
                    await Swal.fire({
                        customClass: {
                            icon: 'swal-sppd-icon-success',
                            popup: 'swal-sppd-popup',
                            title: 'swal-sppd-title',
                            text: 'swal-sppd-text',
                            confirm: 'swal-sppd-confirm',
                        },
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Rekap SPPD berhasil dikirim.',
                        confirmButtonText: 'Ke Daftar Rekap',
                        ...swalFormTheme(),
                    });
                    window.location.href = listUrl;
                } else {
                    showResult(true, 'Rekap SPPD berhasil dikirim', '', [
                        { href: listUrl, label: '📋 Ke Daftar Rekap', class: 'modal-btn-success' },
                    ]);
                }
            } else {
                closePreviewModal();
                showResult(false, 'Gagal', data.message || 'Validasi gagal.', [{ label: 'OK', class: 'modal-btn-secondary' }]);
                submitBtn.disabled = false;
                submitBtn.innerHTML = prevHtml;
            }
        } catch {
            closePreviewModal();
            showResult(false, 'Koneksi Bermasalah', 'Terjadi kesalahan jaringan. Silakan coba lagi.', [{ label: 'OK', class: 'modal-btn-secondary' }]);
            submitBtn.disabled = false;
            submitBtn.innerHTML = prevHtml;
        }
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!validateTolls() || !validateParkings()) {
            return;
        }
        if (typeof Swal !== 'undefined') {
            const r = await Swal.fire({
                customClass: {
                    popup: 'swal-sppd-popup',
                    title: 'swal-sppd-title',
                    text: 'swal-sppd-text',
                    confirm: 'swal-sppd-confirm',
                    cancel: 'swal-sppd-cancel',
                },
                icon: 'question',
                title: 'Submit Laporan?',
                text: 'Pastikan data sudah benar.',
                showCancelButton: true,
                confirmButtonText: 'Kirim',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                ...swalFormTheme(),
            });
            if (!r.isConfirmed) return;
        }
        await submitReport();
    });

    recalcTotals();
}

document.addEventListener('turbo:load', initSppdForm);
initSppdForm();
