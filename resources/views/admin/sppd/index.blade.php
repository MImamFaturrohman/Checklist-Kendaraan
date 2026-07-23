@php use App\Models\Sppd; @endphp

@extends('layouts.dash-app')

@section('title', 'TransDinas')
@section('pageTitle', 'TransDinas')
@section('pageSubtitle', 'Verifikasi laporan SPPD driver')

@php $premiumBgId = 'admin_sppd_index'; @endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
@endpush

@push('styles')
<style>
/* ── SweetAlert2 custom style (admin-sppd) ── */
.swal-adm-sppd-icon-success {
    box-sizing: content-box !important;
}
.swal-adm-sppd-icon-success * {
    box-sizing: content-box !important;
}
.swal2-popup.swal-adm-sppd-popup .swal2-success-circular-line-left,
.swal2-popup.swal-adm-sppd-popup .swal2-success-circular-line-right,
.swal2-popup.swal-adm-sppd-popup .swal2-success-fix {
    background: transparent !important;
}
.swal2-popup.swal-adm-sppd-popup {
    background: rgba(255, 255, 255, 0.9) !important;
    border-radius: 20px !important;
    width: 420px !important;
    max-width: calc(100% - 32px) !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
    border: 1px solid rgba(11, 44, 107, 0.12) !important;
    padding: 1.5rem 1.25rem 1.5rem !important;
}
html.dark .swal2-popup.swal-adm-sppd-popup {
    color: #f3f4f6 !important;
    background: rgba(16, 38, 80, 0.95) !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
}
.swal-adm-sppd-title {
    font-size: 1.15rem !important;
    font-weight: 800 !important;
    color: #0f172a !important;
}
html.dark .swal-adm-sppd-title {
    color: #f1f5f9 !important;
}
html.dark .swal2-popup.swal-adm-sppd-popup .swal2-html-container,
html.dark .swal2-popup.swal-adm-sppd-popup .swal2-content {
    color: #cbd5e1 !important;
}
html.dark .swal2-popup.swal-adm-sppd-popup .swal2-html-container p,
html.dark .swal2-popup.swal-adm-sppd-popup .swal2-html-container strong {
    color: #e2e8f0 !important;
}
.swal2-popup.swal-adm-sppd-popup .swal2-actions {
    margin: 1.25rem auto 0 !important;
    gap: 12px !important;
    width: 100% !important;
    max-width: 100% !important;
    flex-wrap: wrap !important;
    justify-content: center !important;
}
.swal2-popup.swal-adm-sppd-popup button.swal-adm-sppd-confirm {
    margin: 0 !important;
    background: linear-gradient(135deg, #0b2c6b, #123f8f) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 12px !important;
    font-weight: 700 !important;
    font-size: 0.88rem !important;
    padding: 0.7rem 1.5rem !important;
    min-width: 8.5rem !important;
    box-shadow: 0 4px 14px rgba(11, 44, 107, 0.3) !important;
    cursor: pointer !important;
    transition: transform 0.15s ease, box-shadow 0.15s ease !important;
}
.swal2-popup.swal-adm-sppd-popup button.swal-adm-sppd-confirm:hover {
    box-shadow: 0 6px 18px rgba(11, 44, 107, 0.38) !important;
    transform: translateY(-1px);
}
.swal2-popup.swal-adm-sppd-popup button.swal-adm-sppd-cancel {
    margin: 0 !important;
    border-radius: 12px !important;
    font-weight: 600 !important;
    font-size: 0.88rem !important;
    padding: 0.7rem 1.35rem !important;
    min-width: 7rem !important;
    border: 2px solid #cbd5e1 !important;
    background: #f8fafc !important;
    color: #475569 !important;
    cursor: pointer !important;
}
.swal2-popup.swal-adm-sppd-popup button.swal-adm-sppd-cancel:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
}
html.dark .swal2-popup.swal-adm-sppd-popup button.swal-adm-sppd-cancel {
    background: rgba(30, 41, 59, 0.8) !important;
    border-color: rgba(148, 163, 184, 0.45) !important;
    color: #f1f5f9 !important;
}
html.dark .swal2-popup.swal-adm-sppd-popup button.swal-adm-sppd-cancel:hover {
    background: rgba(51, 65, 85, 0.95) !important;
    border-color: rgba(148, 163, 184, 0.65) !important;
}
/* Input dan textarea di dalam popup */
html.dark .swal2-popup.swal-adm-sppd-popup .swal2-input,
html.dark .swal2-popup.swal-adm-sppd-popup .swal2-textarea {
    background: rgba(30, 41, 59, 0.7) !important;
    border-color: rgba(148, 163, 184, 0.3) !important;
    color: #f1f5f9 !important;
}
html.dark .swal2-popup.swal-adm-sppd-popup .swal2-input-label {
    color: #94a3b8 !important;
}

/* Bulk Actions & Checkbox Styles */
.sppd-bulk-actions-wrap label {
    color: #475569;
}
html.dark .sppd-bulk-actions-wrap label {
    color: rgba(200, 218, 255, 0.85);
}
html.dark .sppd-bulk-actions-wrap div {
    background: rgba(255, 255, 255, 0.05) !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
}

/* Bulk Delete Button Styling */
#sppd-btn-bulk-delete {
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    border: 1.5px solid #fecaca;
    cursor: pointer;
    background-color: transparent;
    color: #b91c1c;
    transition: all 0.15s ease-in-out;
}
#sppd-btn-bulk-delete:hover {
    background-color: #b91c1c;
    color: #ffffff !important;
    border-color: #b91c1c;
    box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.15);
}

/* Dark mode overrides for Bulk Delete Button */
html.dark #sppd-btn-bulk-delete {
    background-color: transparent;
    color: #fca5a5;
    border-color: rgba(248, 113, 113, 0.35);
}
html.dark #sppd-btn-bulk-delete:hover {
    background-color: #ef4444;
    color: #ffffff !important;
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25);
}

/* Modern Checkbox styling: slightly rounded edges & premium dark/light mode appearance */
.sppd-row-checkbox, #sppd-select-all {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border: 2px solid #cbd5e1;
    border-radius: 5px; /* rounded slightly / tumpul edgenya */
    outline: none;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: #fff;
    vertical-align: middle;
    margin: 0;
}

html.dark .sppd-row-checkbox, html.dark #sppd-select-all {
    border-color: rgba(255, 255, 255, 0.25);
    background-color: rgba(15, 23, 42, 0.6);
}

.sppd-row-checkbox:hover, #sppd-select-all:hover {
    border-color: #002a7a;
    box-shadow: 0 0 0 3px rgba(0, 42, 122, 0.15);
}
html.dark .sppd-row-checkbox:hover, html.dark #sppd-select-all:hover {
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
}

.sppd-row-checkbox:checked, #sppd-select-all:checked {
    background-color: #002a7a;
    border-color: #002a7a;
}
html.dark .sppd-row-checkbox:checked, html.dark #sppd-select-all:checked {
    background-color: #60a5fa;
    border-color: #60a5fa;
}

/* Checkmark icon */
.sppd-row-checkbox:checked::after, #sppd-select-all:checked::after {
    content: "";
    position: absolute;
    left: 5px;
    top: 1px;
    width: 5px;
    height: 9px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}
html.dark .sppd-row-checkbox:checked::after, html.dark #sppd-select-all:checked::after {
    border-color: #ffffff;
}
</style>
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
                        <!-- Bulk Actions Container -->
                        <div class="sppd-bulk-actions-wrap" style="display: flex; align-items: center; gap: 8px;">
                            <button type="button" id="sppd-btn-bulk-delete" style="display: none;">
                                <i class="bi bi-trash-fill"></i> <span>Hapus (<span id="sppd-bulk-select-count">0</span>)</span>
                            </button>
                            
                            <div style="display: flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 8px; background: rgba(148, 163, 184, 0.1); border: 1px solid rgba(148, 163, 184, 0.25);">
                                <input type="checkbox" id="sppd-select-all" data-total="{{ $sppds->total() }}" title="Pilih Semua">
                                <label for="sppd-select-all" style="font-size: 0.78rem; font-weight: 700; cursor: pointer; user-select: none; margin: 0; display: flex; align-items: center;">Pilih</label>
                            </div>
                        </div>

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

                <div id="sppd-admin-live-root">
                @fragment('sppd-admin-body')
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th style="width: 40px; text-align: center;">Pilih</th>
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
                                    <td>
                                        <input type="checkbox" class="sppd-row-checkbox" value="{{ $s->id }}" aria-label="Pilih rekap SPPD">
                                    </td>
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
                                <tr><td colspan="7" class="portal-empty">Tidak ada data.</td></tr>
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
            const BASE = window._appBase;
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
            let _isAllSelected = false;

            const searchEl = document.getElementById('sppd-filter-q');
            const statusEl = document.getElementById('sppd-filter-status');
            const perPageEl = document.getElementById('sppd-filter-per-page');
            const liveRoot = document.getElementById('sppd-admin-live-root');
            const clearBtn = document.getElementById('sppd-filter-clear');
            const resetBtn = document.getElementById('sppd-filter-reset');

            const selectAllCheckbox = document.getElementById('sppd-select-all');
            const bulkDeleteBtn = document.getElementById('sppd-btn-bulk-delete');
            const bulkSelectCount = document.getElementById('sppd-bulk-select-count');

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

            function updateBulkActionState() {
                if (!liveRoot || !bulkDeleteBtn || !bulkSelectCount) return;
                const checkboxes = Array.from(liveRoot.querySelectorAll('.sppd-row-checkbox'));
                const checkedCheckboxes = checkboxes.filter(cb => cb.checked);
                
                let displayCount = 0;
                if (_isAllSelected) {
                    const totalDbCount = parseInt(selectAllCheckbox?.dataset.total, 10) || 0;
                    displayCount = totalDbCount;
                } else {
                    displayCount = checkedCheckboxes.length;
                }

                bulkSelectCount.textContent = String(displayCount);
                bulkDeleteBtn.style.display = displayCount > 0 ? 'inline-flex' : 'none';

                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = _isAllSelected;
                }
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
                    const totalHeader = res.headers.get('X-VMS-SPPD-Total');

                    if (liveRoot) {
                        liveRoot.innerHTML = html;
                    }

                    if (selectAllCheckbox && totalHeader !== null) {
                        selectAllCheckbox.dataset.total = totalHeader;
                    }

                    bindSorting();
                    bindPagination();
                    updateFilterChrome();

                    if (_isAllSelected) {
                        liveRoot.querySelectorAll('.sppd-row-checkbox').forEach(cb => cb.checked = true);
                    }
                    updateBulkActionState();

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
                const paginationLinks = liveRoot.querySelectorAll('.tbl-pagination a[href]');
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
                                _isAllSelected = false;
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
                    _isAllSelected = false;
                    updateFilterChrome();
                    debouncedFetch();
                });
            }

            if (statusEl) {
                statusEl.addEventListener('change', () => {
                    _isAllSelected = false;
                    _page = 1;
                    fetchSppds();
                });
            }

            if (perPageEl) {
                perPageEl.addEventListener('change', (e) => {
                    _isAllSelected = false;
                    _perPage = parseInt(e.target.value, 10);
                    _page = 1;
                    fetchSppds();
                });
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    _isAllSelected = false;
                    if (searchEl) searchEl.value = '';
                    updateFilterChrome();
                    _page = 1;
                    fetchSppds();
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    _isAllSelected = false;
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
                    const c = await Swal.fire({
                        title: 'Verifikasi?',
                        text: 'Laporan akan diteruskan ke Manager.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, setujui',
                        cancelButtonText: 'Batal',
                        buttonsStyling: false,
                        customClass: { popup: 'swal-adm-sppd-popup', title: 'swal-adm-sppd-title', confirmButton: 'swal-adm-sppd-confirm', cancelButton: 'swal-adm-sppd-cancel' }
                    });
                    if (!c.isConfirmed) return;
                    const r = await fetch(approveUrl(id), { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });
                    const j = await r.json();
                    if (j.success) { await Swal.fire({title:'Berhasil', text:j.message, icon:'success', customClass: { popup: 'swal-adm-sppd-popup', title: 'swal-adm-sppd-title', icon: 'swal-adm-sppd-icon-success', confirmButton: 'swal-adm-sppd-confirm' }, buttonsStyling: false}); location.reload(); }
                    else Swal.fire({title:'Gagal', text:j.message || 'Error', icon:'error', customClass: { popup: 'swal-adm-sppd-popup', title: 'swal-adm-sppd-title', confirmButton: 'swal-adm-sppd-confirm' }, buttonsStyling: false});
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
                        cancelButtonText: 'Batal',
                        buttonsStyling: false,
                        customClass: { popup: 'swal-adm-sppd-popup', title: 'swal-adm-sppd-title', confirmButton: 'swal-adm-sppd-confirm', cancelButton: 'swal-adm-sppd-cancel' },
                        inputValidator: (v) => !v && 'Wajib diisi',
                    });
                    if (!note) return;
                    const r = await fetch(rejectUrl(id), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ revision_note: note }),
                    });
                    const j = await r.json();
                    if (j.success) { await Swal.fire({title:'Berhasil', text:j.message, icon:'success', customClass: { popup: 'swal-adm-sppd-popup', title: 'swal-adm-sppd-title', icon: 'swal-adm-sppd-icon-success', confirmButton: 'swal-adm-sppd-confirm' }, buttonsStyling: false}); location.reload(); }
                    else Swal.fire({title:'Gagal', text:j.message || 'Error', icon:'error', customClass: { popup: 'swal-adm-sppd-popup', title: 'swal-adm-sppd-title', confirmButton: 'swal-adm-sppd-confirm' }, buttonsStyling: false});
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

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', () => {
                    if (!liveRoot) return;
                    _isAllSelected = selectAllCheckbox.checked;
                    const checkboxes = liveRoot.querySelectorAll('.sppd-row-checkbox');
                    checkboxes.forEach(cb => {
                        cb.checked = _isAllSelected;
                    });
                    updateBulkActionState();
                });
            }

            if (liveRoot) {
                liveRoot.addEventListener('change', (e) => {
                    if (e.target.classList.contains('sppd-row-checkbox')) {
                        if (!e.target.checked) {
                            _isAllSelected = false;
                        }
                        updateBulkActionState();
                    }
                });
            }

            if (bulkDeleteBtn) {
                bulkDeleteBtn.addEventListener('click', () => {
                    if (!liveRoot) return;
                    
                    let payload = {};
                    let displayCount = 0;

                    if (_isAllSelected) {
                        payload = {
                            all: true,
                            search: searchEl?.value.trim() ?? '',
                            status: statusEl?.value ?? '',
                        };
                        displayCount = parseInt(selectAllCheckbox?.dataset.total, 10) || 0;
                    } else {
                        const selectedIds = Array.from(liveRoot.querySelectorAll('.sppd-row-checkbox:checked'))
                            .map(cb => cb.value);
                        if (selectedIds.length === 0) return;
                        payload = {
                            ids: selectedIds
                        };
                        displayCount = selectedIds.length;
                    }

                    Swal.fire({
                        title: 'Hapus rekap SPPD?',
                        text: `Anda yakin ingin menghapus ${displayCount} data rekap SPPD terpilih? Tindakan ini tidak dapat dibatalkan.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'swal-adm-sppd-popup',
                            title: 'swal-adm-sppd-title',
                            confirmButton: 'swal-adm-sppd-confirm',
                            cancelButton: 'swal-adm-sppd-cancel',
                        },
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            showLoading();
                            try {
                                const res = await fetch(appBase('/admin/rekap-sppd/bulk-delete'), {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrf,
                                        'X-Requested-With': 'XMLHttpRequest',
                                    },
                                    body: JSON.stringify(payload),
                                });
                                const json = await res.json().catch(() => ({}));
                                if (!res.ok) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: json.message || ('HTTP ' + res.status),
                                        customClass: { popup: 'swal-adm-sppd-popup', title: 'swal-adm-sppd-title', confirmButton: 'swal-adm-sppd-confirm' },
                                        buttonsStyling: false
                                    });
                                    return;
                                }
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: json.message || 'Data rekap SPPD terpilih berhasil dihapus.',
                                    timer: 1500,
                                    showConfirmButton: false,
                                    customClass: {
                                        popup: 'swal-adm-sppd-popup',
                                        title: 'swal-adm-sppd-title',
                                        icon: 'swal-adm-sppd-icon-success',
                                    }
                                });
                                // Reset selections
                                _isAllSelected = false;
                                if (selectAllCheckbox) selectAllCheckbox.checked = false;
                                updateBulkActionState();
                                // Refresh data
                                fetchSppds();
                            } catch (err) {
                                console.error(err);
                                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem.' });
                            } finally {
                                hideLoading();
                            }
                        }
                    });
                });
            }

            bindSorting();
            bindPagination();
            updateFilterChrome();
            updateBulkActionState();

            if (typeof window.registerTurboCleanup === 'function') {
                window.registerTurboCleanup(function () {
                    if (_abort) _abort.abort();
                });
            }
        })();
    </script>
@endpush