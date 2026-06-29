@php use App\Models\Sppd; @endphp

@extends('layouts.dash-app')

@section('title', 'TransDinas')
@section('pageTitle', 'TransDinas')
@section('pageSubtitle', 'Verifikasi laporan SPPD driver')

@php $premiumBgId = 'admin_sppd_index'; @endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
@endpush

@section('content')

    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">
            <div class="portal-stats-row" data-stat-count="4">
                <x-admin-stat-card title="Total" :value="$counts['approved_completed']" unit="SPPD" description="Laporan SPPD diverifikasi & disetujui" icon="bi bi-check-circle-fill" />
                <x-admin-stat-card title="Menunggu Verifikasi" :value="$counts['pending']" unit="Dokumen" description="Belum diverifikasi admin" icon="bi bi-hourglass-top" valueStyle="color: #ffbf00"/>
                <x-admin-stat-card title="Revisi" :value="$counts['revision']" unit="Dokumen" description="Perlu perbaikan data driver" icon="bi bi-pencil-fill" />
                <x-admin-stat-card title="Menunggu Disetujui" :value="$counts['pending_manager']" unit="Dokumen" description="Menunggu persetujuan manager" icon="bi bi-person-check-fill" valueStyle="color: #FFA500" />
            </div>

            <div class="portal-section" id="section-sppd-admin">
                <div class="portal-section-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
                    <div class="portal-section-title" style="margin-bottom: 0;"><i class="bi bi-table"></i> Daftar Rekap Biaya Dinas</div>

                    <div class="portal-local-filters ppm-daftar-filters" id="admin-sppd-filter-bar" style="margin-top: 0; padding: 0; background: transparent; border: none; box-shadow: none; align-items: center; gap: 8px;">
                        <div class="admin-search-wrap portal-search-full" style="width: 280px; max-width: 100%;">
                            <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            <input type="text" id="sppd-filter-q" value="{{ $search }}" placeholder="Cari driver, keperluan, nopol…" class="admin-search-input" autocomplete="off">
                        </div>
                        <div class="ppm-status-wrap">
                            <select id="sppd-filter-status" class="admin-filter-input" aria-label="Filter status rekap dinas">
                                <option value="">Semua status</option>
                                @foreach(\App\Support\SppdStatus::adminFilterOptions() as $st)
                                    <option value="{{ $st }}" @selected($currentStatus === $st)>{{ \App\Support\SppdStatus::label($st) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-admin-per-page-select id="sppd-filter-per-page" name="per_page" :selected="$sppds->perPage()" />
                        <button type="button" class="btn btn-sm sppd-icon-btn admin-filter-reset" id="sppd-filter-reset" title="Reset filter" aria-label="Reset filter" style="display: none"><i class="bi bi-arrow-clockwise"></i></button>
                    </div>
                </div>

                <div id="sppd-loading" class="portal-loading" style="display:none; margin: 12px 0;">
                    <span class="portal-loading-dot"></span><span class="portal-loading-dot"></span><span class="portal-loading-dot"></span>
                </div>

                <div id="sppd-admin-live-root" data-vms-sppd-live>
                @fragment('sppd-admin-body')
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <x-sortable-th key="nama_driver" label="Driver" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <x-sortable-th key="keperluan_dinas" label="Ringkasan" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <x-sortable-th key="no_kendaraan" label="Kendaraan" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <x-sortable-th key="status" label="Status" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sppds as $s)
                                <tr>
                                    <td>{{ ($sppds->currentPage()-1)*$sppds->perPage()+$loop->iteration }}</td>
                                    <td>{{ $s->nama_driver }}<br><span class="sppd-cell-muted">{{ $s->user?->username }}</span></td>
                                    <td>{{ \Illuminate\Support\Str::limit($s->keperluan_dinas, 40) }}<br><span class="sppd-cell-muted">{{ $s->tanggal_dinas->format('d/m/Y') }}</span></td>
                                    <td><strong>{{ $s->no_kendaraan }}</strong><br><span class="sppd-cell-muted">{{ $s->jenis_kendaraan }}</span></td>
                                    <td><x-sppd-status-badge :status="$s->status" /></td>
                                    <td>
                                        <div class="sppd-aksi-btns">
                                            <button
                                                type="button"
                                                class="btn btn-sm sppd-icon-btn sppd-btn-primary admin-sppd-detail"
                                                data-id="{{ $s->id }}"
                                                title="Detail Laporan"
                                                aria-label="Detail Laporan"
                                            ><i class="bi bi-info-circle"></i></button>
                                            @php
                                                $admPdfOk = $s->pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($s->pdf_path);
                                            @endphp
                                            @if($admPdfOk)
                                                <a
                                                    href="{{ route('admin.sppd.pdf', $s) }}"
                                                    class="btn-view-pdf"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    title="View PDF"
                                                    aria-label="View PDF"
                                                >
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/></svg>
                                                    View PDF
                                                </a>
                                            @endif
                                            @if(($canVerifySppd ?? false) && $s->status === Sppd::STATUS_PENDING)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm sppd-icon-btn sppd-btn-success admin-sppd-ok"
                                                    data-id="{{ $s->id }}"
                                                    title="Setujui Laporan"
                                                    aria-label="Setujui Laporan"
                                                ><i class="bi bi-check-lg"></i></button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm sppd-icon-btn sppd-btn-danger admin-sppd-reject"
                                                    data-id="{{ $s->id }}"
                                                    title="Tolak / Revisi"
                                                    aria-label="Tolak / Revisi"
                                                ><i class="bi bi-x-lg"></i></button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="portal-empty">Tidak ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-admin-pagination :paginator="$sppds" />
                @endfragment
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modals')
    <div id="sppd-modal-detail-admin" class="modal-overlay" style="display:none">
        <div class="modal-box sppd-modal-box" style="max-width:min(720px,100%);text-align:left;max-height:86vh;overflow:auto">
            <h3>Detail Rekap SPPD</h3>
            <div id="sppd-admin-detail-body" class="sppd-detail-html"></div>
            <div class="ppm-modal-actions">
                <div id="sppd-admin-detail-pdf-wrap" class="sppd-detail-pdf-wrap" hidden></div>
                <button type="button" class="ppm-btn-ghost portal-local-reset" data-close-admin-sppd-modal>Tutup</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const BASE = @json(url('/'));
            const CAN_VERIFY_SPPD = @json($canVerifySppd ?? false);
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const INDEX_URL = @json(route('admin.sppd.index'));
            const detailUrl = (id) => BASE + '/admin/rekap-sppd/' + id;
            const approveUrl = (id) => BASE + '/admin/rekap-sppd/' + id + '/verify-approve';
            const rejectUrl = (id) => BASE + '/admin/rekap-sppd/' + id + '/verify-reject';

            let _page = 1;
            let _perPage = {{ (int) $sppds->perPage() }};
            let _sort = '{{ $activeSort ?? "" }}';
            let _dir = '{{ $activeDir ?? "" }}';
            let _abort = null;

            const searchEl = document.getElementById('sppd-filter-q');
            const statusEl = document.getElementById('sppd-filter-status');
            const perPageEl = document.getElementById('sppd-filter-per-page');
            const liveRoot = document.getElementById('sppd-admin-live-root');
            const clearBtn = document.getElementById('sppd-filter-clear');
            const resetBtn = document.getElementById('sppd-filter-reset');

            function showLoading() { const el = document.getElementById('sppd-loading'); if (el) el.style.display = 'flex'; }
            function hideLoading() { const el = document.getElementById('sppd-loading'); if (el) el.style.display = 'none'; }

            function buildParams() {
                const obj = {
                    q:        searchEl?.value.trim() ?? '',
                    status:   statusEl?.value ?? '',
                    per_page: _perPage,
                    page:     _page,
                };
                if (_sort) { obj.sort = _sort; obj.dir = _dir; }
                return new URLSearchParams(
                    Object.fromEntries(Object.entries(obj).filter(([, v]) => v !== '' && v != null))
                ).toString();
            }

            function updateFilterChrome() {
                const hasSearch = searchEl && searchEl.value.trim().length > 0;
                if (clearBtn) clearBtn.style.display = hasSearch ? 'flex' : 'none';
                const showReset = hasSearch
                    || (statusEl && statusEl.value !== '')
                    || _perPage !== 15;
                if (resetBtn) resetBtn.style.display = showReset ? '' : 'none';
            }

            async function fetchSppds(scroll = false) {
                _abort?.abort();
                _abort = new AbortController();
                showLoading();

                const q = buildParams();
                try {
                    const res = await fetch(`${INDEX_URL}?${q}`, {
                        headers: {
                            'Accept': 'text/html',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-VMS-SPPD-Fragment': '1'
                        },
                        signal: _abort.signal
                    });
                    const html = await res.text();

                    if (liveRoot) {
                        liveRoot.innerHTML = html;
                    }

                    bindSorting();
                    bindPagination();
                    updateFilterChrome();

                    if (scroll && liveRoot) {
                        liveRoot.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                } catch (e) {
                    if (e.name !== 'AbortError') console.warn('SPPD fetchSppds error', e);
                } finally {
                    hideLoading();
                }
            }

            function bindPagination() {
                if (!liveRoot) return;
                const paginationLinks = liveRoot.querySelectorAll('.tbl-pagination a[href], a[href]');
                paginationLinks.forEach(link => {
                    link.addEventListener('click', (e) => {
                        const u = new URL(link.getAttribute('href'), location.origin);
                        e.preventDefault();
                        _page = parseInt(u.searchParams.get('page') || '1', 10);
                        fetchSppds(true);
                    });
                });
            }

            function bindSorting() {
                if (window.AdminTableSort && liveRoot) {
                    const tableWrap = liveRoot.querySelector('.admin-table-wrap');
                    if (tableWrap) {
                        window.AdminTableSort.bindRoot(tableWrap, {
                            getUrl: () => {
                                const url = new URL(location.href);
                                if (_sort) { url.searchParams.set('sort', _sort); url.searchParams.set('dir', _dir); }
                                else { url.searchParams.delete('sort'); url.searchParams.delete('dir'); }
                                return url;
                            },
                            onNavigate: (url) => {
                                _sort = url.searchParams.get('sort') || '';
                                _dir = url.searchParams.get('dir') || '';
                                _page = 1;
                                fetchSppds();
                            },
                        });
                    }
                }
            }

            function debounce(fn, ms = 380) {
                let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
            }

            const debouncedFetch = debounce(() => { _page = 1; fetchSppds(); });

            if (searchEl) {
                searchEl.addEventListener('input', () => {
                    updateFilterChrome();
                    debouncedFetch();
                });
            }

            if (statusEl) {
                statusEl.addEventListener('change', () => {
                    _page = 1;
                    fetchSppds();
                });
            }

            if (perPageEl) {
                perPageEl.addEventListener('change', (e) => {
                    _perPage = parseInt(e.target.value, 10);
                    _page = 1;
                    fetchSppds();
                });
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    if (searchEl) searchEl.value = '';
                    updateFilterChrome();
                    _page = 1;
                    fetchSppds();
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    if (searchEl) searchEl.value = '';
                    if (statusEl) statusEl.selectedIndex = 0;
                    if (perPageEl) { perPageEl.value = '15'; _perPage = 15; }
                    _page = 1; _sort = ''; _dir = '';
                    updateFilterChrome();
                    fetchSppds();
                });
            }

            document.getElementById('section-sppd-admin')?.addEventListener('click', async (e) => {
                const detailBtn = e.target.closest('.admin-sppd-detail');
                if (detailBtn) {
                    await showDetail(detailBtn.dataset.id);
                    return;
                }
                const okBtn = e.target.closest('.admin-sppd-ok');
                if (okBtn) {
                    if (!CAN_VERIFY_SPPD) return;
                    const id = okBtn.dataset.id;
                    const c = await Swal.fire({ title: 'Verifikasi?', text: 'Laporan akan diteruskan ke Manager.', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, setujui' });
                    if (!c.isConfirmed) return;
                    const r = await fetch(approveUrl(id), { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });
                    const j = await r.json();
                    if (j.success) { await Swal.fire('Berhasil', j.message, 'success'); location.reload(); }
                    else Swal.fire('Gagal', j.message || 'Error', 'error');
                    return;
                }
                const rejBtn = e.target.closest('.admin-sppd-reject');
                if (rejBtn) {
                    if (!CAN_VERIFY_SPPD) return;
                    const id = rejBtn.dataset.id;
                    const { value: note } = await Swal.fire({
                        title: 'Alasan revisi',
                        input: 'textarea',
                        inputLabel: 'Pesan untuk driver',
                        inputPlaceholder: 'Jelaskan bagian yang perlu diperbaiki…',
                        showCancelButton: true,
                        confirmButtonText: 'Kirim revisi',
                        inputValidator: (v) => !v && 'Wajib diisi',
                    });
                    if (!note) return;
                    const r = await fetch(rejectUrl(id), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ revision_note: note }),
                    });
                    const j = await r.json();
                    if (j.success) { await Swal.fire('Berhasil', j.message, 'success'); location.reload(); }
                    else Swal.fire('Gagal', j.message || 'Error', 'error');
                }
            });

            async function showDetail(id) {
                const r = await fetch(detailUrl(id), { headers: { Accept: 'application/json' } });
                const j = await r.json();
                const d = j.sppd;
                const modal = document.getElementById('sppd-modal-detail-admin');
                const body = document.getElementById('sppd-admin-detail-body');
                const pdfWrap = document.getElementById('sppd-admin-detail-pdf-wrap');
                pdfWrap.hidden = true;
                pdfWrap.innerHTML = '';
                body.innerHTML = renderDetail(d);
                const pdfHtml = renderAdminPdfActions(d);
                if (pdfHtml) {
                    pdfWrap.innerHTML = pdfHtml;
                    pdfWrap.hidden = false;
                }
                modal.style.display = 'flex';
            }

            function renderDetail(d) {
                let tollRows = (d.tolls || []).map(t => `<tr><td>${esc(t.leg_label || '—')}</td><td>${esc(t.dari_tol)}</td><td>${esc(t.ke_tol)}</td><td>${formatRp(t.harga)}</td></tr>`).join('');
                if (!tollRows) tollRows = '<tr><td colspan="4" class="portal-empty" style="padding:8px">—</td></tr>';
                let fuelRows = (d.fuels || []).map(f => `<tr><td>${esc(f.liter)}</td><td>${formatRp(f.harga_per_liter)}</td><td>${formatRp(f.total)}</td></tr>`).join('');
                if (!fuelRows) fuelRows = '<tr><td colspan="3" class="portal-empty" style="padding:8px">—</td></tr>';
                return `
                    <table class="info-table sppd-mini-table">
                        <tr><td class="label">Driver</td><td>${esc(d.nama_driver)} (${esc(d.driver_username || '-')})</td></tr>
                        <tr><td class="label">Keperluan</td><td>${esc(d.keperluan_dinas)}</td></tr>
                        <tr><td class="label">Kendaraan</td><td>${esc(d.no_kendaraan)} — ${esc(d.jenis_kendaraan)}</td></tr>
                        <tr><td class="label">Tanggal</td><td>${esc(d.tanggal_dinas)}</td></tr>
                        <tr><td class="label">Tujuan</td><td>${esc(d.tujuan)}</td></tr>
                        <tr><td class="label">Status</td><td>${esc(d.status_label)}</td></tr>
                    </table>
                    <p class="sppd-detail-sub">Biaya Tol</p>
                    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Arah</th><th>Dari</th><th>Ke</th><th>Harga</th></tr></thead><tbody>${tollRows}</tbody></table></div>
                    <p class="sppd-detail-sub">BBM</p>
                    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Liter</th><th>Harga/L</th><th>Total</th></tr></thead><tbody>${fuelRows}</tbody></table></div>
                    <p><strong>Total Tol:</strong> ${formatRp(d.total_tol)} &nbsp;|&nbsp; <strong>Total BBM:</strong> ${formatRp(d.total_bbm)} &nbsp;|&nbsp; <strong>Grand Total:</strong> ${formatRp(d.grand_total)}</p>
                    ${d.revision_note ? `<p class="sppd-detail-sub">Catatan revisi</p><div class="sppd-revisi-inline">${esc(d.revision_note)}</div>` : ''}
                    ${d.rejection_note ? `<p class="sppd-detail-sub">Alasan penolakan</p><div class="sppd-revisi-inline">${esc(d.rejection_note)}</div>` : ''}
                `;
            }

            function formatRp(n) {
                return 'Rp ' + (Number(n) || 0).toLocaleString('id-ID');
            }

            function esc(s) {
                const d = document.createElement('div');
                d.textContent = s ?? '';
                return d.innerHTML;
            }

            function qaAttr(s) {
                return String(s ?? '').replace(/"/g, '&quot;');
            }

            function renderAdminPdfActions(d) {
                if (d.pdf_download_url && d.pdf_available) {
                    return `<a href="${qaAttr(d.pdf_download_url)}" class="btn-view-pdf" target="_blank" rel="noopener noreferrer" title="View PDF">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/></svg>
                        View PDF
                    </a>`;
                }
                return '';
            }

            document.querySelectorAll('[data-close-admin-sppd-modal]').forEach(el => {
                el.addEventListener('click', () => { document.getElementById('sppd-modal-detail-admin').style.display = 'none'; });
            });
            document.getElementById('sppd-modal-detail-admin')?.addEventListener('click', (e) => {
                if (e.target.id === 'sppd-modal-detail-admin') e.currentTarget.style.display = 'none';
            });

            bindSorting();
            bindPagination();
            updateFilterChrome();

            if (typeof window.registerTurboCleanup === 'function') {
                window.registerTurboCleanup(function () {
                    if (_abort) _abort.abort();
                });
            }
        })();
    </script>
@endpush