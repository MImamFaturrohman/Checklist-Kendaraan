@extends('layouts.dash-app')

@section('title', 'Log Pemakaian Kendaraan')
@section('pageTitle', 'Log Pemakaian Kendaraan')
@section('pageSubtitle', 'PT ARTHA DAYA COALINDO')

@php $premiumBgId = 'admin_vul'; @endphp

@push('styles')
<style>
    .vul-admin-name { font-weight: 700; color: var(--dash-text-primary, #0f172a); }
    .vul-admin-meta { font-size: 0.76rem; opacity: 0.85; color: #64748b; }
    .dash-body.dark .vul-admin-meta { color: rgba(200, 218, 255, 0.62); }
    .vul-admin-keperluan { font-size: 0.84rem; line-height: 1.45; min-width: 200px; max-width: 300px; }
    .vul-admin-kondisi { font-size: 0.8rem; line-height: 1.4; max-width: 300px; min-width: 200px; }
    .vul-admin-kondisi small { display: block; font-weight: 700; color: #64748b; margin-bottom: 2px; }
    .dash-body.dark .vul-admin-kondisi small { color: rgba(200, 218, 255, 0.55); }
    .vul-admin-time { font-size: 0.84rem; white-space: nowrap; }
    .vul-admin-mono { font-variant-numeric: tabular-nums; text-align: center; min-width: 80px }

    .vul-header-filters {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .vul-header-search {
        width: 320px;
        max-width: 100%;
    }
    .vul-month-year-selects {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    @media (max-width: 640px) {
        .vul-header-filters {
            width: 100%;
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
        }
        .vul-header-search {
            width: 100%;
        }
        .vul-month-year-selects {
            width: 100%;
        }
        .vul-month-year-selects select {
            flex: 1;
            min-width: 0;
        }
        .vul-header-filters .tbl-per-page {
            width: 100%;
            justify-content: space-between;
            padding-top: 2px;
            border-top: 1px solid rgba(148, 163, 184, 0.18);
        }
        html.dark .dash-body .vul-header-filters .tbl-per-page {
            border-top-color: rgba(255, 255, 255, 0.08);
        }
        .vul-header-filters .bbm-portal-filter-actions {
            width: 100%;
        }
        .vul-header-filters #vul-logs-reset {
            width: 100%;
            justify-content: center;
            text-align: center;
        }
    }

    /* Bulk Actions & Checkbox Styles */
    .vul-bulk-actions-wrap label {
        color: #475569;
    }
    html.dark .vul-bulk-actions-wrap label {
        color: rgba(200, 218, 255, 0.85);
    }
    html.dark .vul-bulk-actions-wrap div {
        background: rgba(255, 255, 255, 0.05) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    /* Bulk Delete Button Styling */
    #vul-btn-bulk-delete {
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
    #vul-btn-bulk-delete:hover {
        background-color: #b91c1c;
        color: #ffffff !important;
        border-color: #b91c1c;
        box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.15);
    }
    
    /* Dark mode overrides for Bulk Delete Button */
    html.dark #vul-btn-bulk-delete {
        background-color: transparent;
        color: #fca5a5;
        border-color: rgba(248, 113, 113, 0.35);
    }
    html.dark #vul-btn-bulk-delete:hover {
        background-color: #ef4444;
        color: #ffffff !important;
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25);
    }

    /* Modern Checkbox styling: slightly rounded edges & premium dark/light mode appearance */
    .vul-row-checkbox, #vul-select-all {
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

    html.dark .vul-row-checkbox, html.dark #vul-select-all {
        border-color: rgba(255, 255, 255, 0.25);
        background-color: rgba(15, 23, 42, 0.6);
    }

    .vul-row-checkbox:hover, #vul-select-all:hover {
        border-color: #002a7a;
        box-shadow: 0 0 0 3px rgba(0, 42, 122, 0.15);
    }
    html.dark .vul-row-checkbox:hover, html.dark #vul-select-all:hover {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
    }

    .vul-row-checkbox:checked, #vul-select-all:checked {
        background-color: #002a7a;
        border-color: #002a7a;
    }
    html.dark .vul-row-checkbox:checked, html.dark #vul-select-all:checked {
        background-color: #60a5fa;
        border-color: #60a5fa;
    }

    /* Checkmark icon */
    .vul-row-checkbox:checked::after, #vul-select-all:checked::after {
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
    html.dark .vul-row-checkbox:checked::after, html.dark #vul-select-all:checked::after {
        border-color: #ffffff;
    }

    /* ── SweetAlert2 custom style (admin-peminjaman) ── */
    .swal-ppm-icon-success {
        box-sizing: content-box !important;
    }
    .swal-ppm-icon-success * {
        box-sizing: content-box !important;
    }
    .swal2-popup.swal-ppm-popup .swal2-success-circular-line-left,
    .swal2-popup.swal-ppm-popup .swal2-success-circular-line-right,
    .swal2-popup.swal-ppm-popup .swal2-success-fix {
        background: transparent !important;
    }
    .swal2-popup.swal-ppm-popup {
        background: rgba(255, 255, 255, 0.9) !important;
        border-radius: 20px !important;
        width: 420px !important;
        max-width: calc(100% - 32px) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
        border: 1px solid rgba(11, 44, 107, 0.12) !important;
        padding: 1.5rem 1.25rem 1.5rem !important;
    }
    html.dark .swal2-popup.swal-ppm-popup {
        color: #f3f4f6 !important;
        background: rgba(16, 38, 80, 0.95) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }
    .swal-ppm-title {
        font-size: 1.15rem !important;
        font-weight: 800 !important;
        color: #0f172a !important;
    }
    html.dark .swal-ppm-title {
        color: #f1f5f9 !important;
    }
    html.dark .swal2-popup.swal-ppm-popup .swal2-html-container,
    html.dark .swal2-popup.swal-ppm-popup .swal2-content {
        color: #cbd5e1 !important;
    }
    html.dark .swal2-popup.swal-ppm-popup .swal2-html-container p,
    html.dark .swal2-popup.swal-ppm-popup .swal2-html-container strong {
        color: #e2e8f0 !important;
    }
    .swal2-popup.swal-ppm-popup .swal2-actions {
        margin: 1.25rem auto 0 !important;
        gap: 12px !important;
        width: 100% !important;
        max-width: 100% !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
    }
    .swal2-popup.swal-ppm-popup button.swal-ppm-confirm {
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
    .swal2-popup.swal-ppm-popup button.swal-ppm-confirm:hover {
        box-shadow: 0 6px 18px rgba(11, 44, 107, 0.38) !important;
        transform: translateY(-1px);
    }
    .swal2-popup.swal-ppm-popup button.swal-ppm-cancel {
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
    .swal2-popup.swal-ppm-popup button.swal-ppm-cancel:hover {
        background: #f1f5f9 !important;
        border-color: #94a3b8 !important;
    }
    html.dark .swal2-popup.swal-ppm-popup button.swal-ppm-cancel {
        background: rgba(30, 41, 59, 0.8) !important;
        border-color: rgba(148, 163, 184, 0.35) !important;
        color: #e2e8f0 !important;
    }
    html.dark .swal2-popup.swal-ppm-popup button.swal-ppm-cancel:hover {
        background: rgba(30, 41, 59, 0.95) !important;
        border-color: rgba(148, 163, 184, 0.5) !important;
    }
</style>
@endpush

@section('content')
    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">
            <div class="portal-section" style="margin-top: 8px">
                <div class="portal-section-header">
                    <div class="portal-section-title"><i class="bi bi-table"></i> Log Pemakaian Kendaraan</div>

                    {{-- Filter Bar (no form submit — driven by AJAX) --}}
                    <div class="vul-header-filters" id="vul-logs-filter-bar">
                        <!-- Bulk Actions Container -->
                        <div class="vul-bulk-actions-wrap" style="display: flex; align-items: center; gap: 8px;">
                            <button type="button" class="btn btn-sm" id="vul-btn-bulk-delete" style="display: none;">
                                <i class="bi bi-trash-fill"></i> Hapus (<span id="vul-bulk-select-count">0</span>)
                            </button>
                            
                            <div style="display: flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 8px; background: rgba(148, 163, 184, 0.1); border: 1px solid rgba(148, 163, 184, 0.25);">
                                <input type="checkbox" id="vul-select-all" data-total="{{ $logs->total() }}" title="Pilih Semua">
                                <label for="vul-select-all" style="font-size: 0.78rem; font-weight: 700; cursor: pointer; user-select: none; margin: 0; display: flex; align-items: center;">Pilih</label>
                            </div>
                        </div>

                        <div class="admin-search-wrap vul-header-search">
                            <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            <input type="search" id="vul-filter-q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nopol, jenis, driver, keperluan…" class="admin-search-input" autocomplete="off" aria-label="Cari log">
                        </div>
                        <div class="ppm-status-wrap vul-month-year-selects">
                            <label class="sr-only" for="vul-filter-month">Bulan</label>
                            <select id="vul-filter-month" class="admin-filter-input" aria-label="Filter Bulan">
                                <option value="">Semua Bulan</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ ($filters['month'] ?? '') == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->day(1)->month($m)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>

                            <label class="sr-only" for="vul-filter-year">Tahun</label>
                            <select id="vul-filter-year" class="admin-filter-input" aria-label="Filter Tahun">
                                <option value="">Semua Tahun</option>
                                @foreach ($years as $y)
                                    <option value="{{ $y }}" {{ ($filters['year'] ?? '') == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-admin-per-page-select id="vul-arch-per" name="per_page" :selected="$logs->perPage() ?? 25" />
                        <div class="ppm-status-wrap bbm-portal-filter-actions">
                            <button type="button" class="btn btn-sm sppd-icon-btn admin-filter-reset" id="vul-logs-reset" title="Hapus semua filter" aria-label="Hapus semua filter" style="display: none"><i class="bi bi-arrow-clockwise"></i></button>
                        </div>
                    </div>
                </div>

                {{-- Loading indicator --}}
                <div id="vul-loading" class="portal-loading" style="display:none; margin: 12px 0;">
                    <span class="portal-loading-dot"></span><span class="portal-loading-dot"></span><span class="portal-loading-dot"></span>
                </div>

                <div class="admin-table-wrap" style="margin-top: 16px">
                    <table class="admin-table" id="vul-table">
                        <thead id="vul-thead">
                            <tr>
                                <th>#</th>
                                <th style="width: 40px; text-align: center;">Pilih</th>
                                <x-sortable-th key="created_at" label="Waktu dicatat" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <th>Driver</th>
                                <x-sortable-th key="nomor_kendaraan" label="Kendaraan" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <th>BBM Awal</th>
                                <th>BBM Akhir</th>
                                <x-sortable-th key="km_awal" label="KM Awal" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <x-sortable-th key="km_akhir" label="KM Akhir" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <th>Durasi</th>
                                <x-sortable-th key="keperluan" label="Keperluan" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <th>Kondisi Sebelum</th>
                                <th>Kondisi Sesudah</th>
                            </tr>
                        </thead>
                        <tbody id="vul-tbody">
                            @forelse($logs as $row)
                                @php
                                    $bbmA = $row->level_bbm_awal;
                                    $bbmB = $row->level_bbm_akhir;
                                    $kep = $row->keperluan;
                                    $kepShort = \Illuminate\Support\Str::limit(strip_tags($kep), 80);
                                    $kSeb = $row->kondisi_sebelum_penggunaan;
                                    $kSes = $row->kondisi_setelah_penggunaan;
                                    $kSebShort = $kSeb ? \Illuminate\Support\Str::limit(strip_tags($kSeb), 120) : null;
                                    $kSesShort = $kSes ? \Illuminate\Support\Str::limit(strip_tags($kSes), 120) : null;
                                @endphp
                                <tr>
                                    <td>{{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <input type="checkbox" class="vul-row-checkbox" value="{{ $row->id }}" aria-label="Pilih log pemakaian">
                                    </td>
                                    <td class="vul-admin-time">{{ $row->created_at?->translatedFormat('d F Y H:i') }}</td>
                                    <td>
                                        <span class="vul-admin-name">{{ $row->user?->name ?? '—' }}</span><br>
                                    </td>
                                    <td>
                                        <strong>{{ $row->nomor_kendaraan }}</strong><br>
                                        <span class="vul-admin-meta">{{ $row->jenis_kendaraan }}</span>
                                    </td>
                                    <td class="vul-admin-mono">{{ $bbmA ? (int)$bbmA.'%' : '—' }}</td>
                                    <td class="vul-admin-mono">{{ $bbmB ? (int)$bbmB.'%' : '—' }}</td>
                                    <td class="vul-admin-mono">{{ $row->km_awal ? number_format((int)$row->km_awal) : '—' }}</td>
                                    <td class="vul-admin-mono">{{ $row->km_akhir ? number_format((int)$row->km_akhir) : '—' }}</td>
                                    <td class="vul-admin-time">{{ $row->durasiDeskripsi() }}</td>
                                    <td class="vul-admin-keperluan" title="{{ $kep }}">{{ $kepShort }}</td>
                                    <td class="vul-admin-kondisi">
                                        @if($kSebShort)
                                            <span title="{{ $kSeb }}">{{ $kSebShort }}</span>
                                        @else
                                            <span class="vul-admin-meta">—</span>
                                        @endif
                                    </td>
                                    <td class="vul-admin-kondisi">
                                        @if($kSesShort)
                                            <span title="{{ $kSes }}">{{ $kSesShort }}</span>
                                        @else
                                            <span class="vul-admin-meta">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="13" class="portal-empty">Belum ada log penggunaan kendaraan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div id="vul-pagination" class="tbl-pagination-mount"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
<script>
(function () {
    'use strict';

    const BASE_URL  = '{{ url("/") }}';
    const API_URL   = '{{ route("api.admin.vehicle-usage-logs") }}';

    let _page    = {{ (int) $logs->currentPage() }};
    let _perPage = {{ (int) $logs->perPage() }};
    let _sort    = '{{ $activeSort ?? "" }}';
    let _dir     = '{{ $activeDir ?? "" }}';
    let _abort   = null;
    let _isAllSelected = false;

    const selectAllCheckbox = document.getElementById('vul-select-all');
    const bulkDeleteBtn = document.getElementById('vul-btn-bulk-delete');
    const bulkSelectCount = document.getElementById('vul-bulk-select-count');

    /* ================================================================
    HELPERS
    ================================================================ */
    function buildParams() {
        const obj = {
            q:        document.getElementById('vul-filter-q')?.value ?? '',
            month:    document.getElementById('vul-filter-month')?.value ?? '',
            year:     document.getElementById('vul-filter-year')?.value ?? '',
            per_page: _perPage,
            page:     _page,
        };
        if (_sort) { obj.sort = _sort; obj.dir = _dir; }
        return new URLSearchParams(
            Object.fromEntries(Object.entries(obj).filter(([, v]) => v !== '' && v != null))
        ).toString();
    }

    function showLoading() { const el = document.getElementById('vul-loading'); if (el) el.style.display = 'flex'; }
    function hideLoading() { const el = document.getElementById('vul-loading'); if (el) el.style.display = 'none'; }

    function escHtml(s) {
        if (s == null || s === '') return '';
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function bbmLabel(v) {
        if (v === null || v === undefined || v === '') return '—';
        const n = parseFloat(v);
        return isNaN(n) ? escHtml(String(v)) : Math.round(n) + '%';
    }

    function debounce(fn, ms = 380) {
        let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
    }

    /* ================================================================
    RENDER TABLE
    ================================================================ */
    function renderTable(json) {
        const tbody = document.getElementById('vul-tbody');
        if (!tbody) return;

        const off = (json.current_page - 1) * json.per_page;

        if (!json.data || !json.data.length) {
            tbody.innerHTML = '<tr><td colspan="13" class="portal-empty">Belum ada log penggunaan kendaraan.</td></tr>';
            return;
        }

        tbody.innerHTML = json.data.map((r, i) => {
            const isChecked = _isAllSelected ? 'checked' : '';
            return `
            <tr>
                <td>${off + i + 1}</td>
                <td>
                    <input type="checkbox" class="vul-row-checkbox" value="${r.id}" ${isChecked} aria-label="Pilih log pemakaian">
                </td>
                <td class="vul-admin-time">${escHtml(r.created_at ?? '—')}</td>
                <td>
                    <span class="vul-admin-name">${escHtml(r.user_name)}</span><br>
                    <span class="vul-admin-meta">${escHtml(r.user_username)}</span>
                </td>
                <td>
                    <strong>${escHtml(r.nomor_kendaraan)}</strong><br>
                    <span class="vul-admin-meta">${escHtml(r.jenis_kendaraan ?? '')}</span>
                </td>
                <td class="vul-admin-mono">${bbmLabel(r.level_bbm_awal)}</td>
                <td class="vul-admin-mono">${bbmLabel(r.level_bbm_akhir)}</td>
                <td class="vul-admin-mono">${r.km_awal ?? '—'}</td>
                <td class="vul-admin-mono">${r.km_akhir ?? '—'}</td>
                <td class="vul-admin-time">${escHtml(r.durasi ?? '—')}</td>
                <td class="vul-admin-keperluan" title="${escHtml(r.keperluan_full)}">${escHtml(r.keperluan)}</td>
                <td class="vul-admin-kondisi">
                    ${r.kondisi_sebelum_penggunaan
                        ? `<span title="${escHtml(r.kondisi_sebelum_full)}">${escHtml(r.kondisi_sebelum_penggunaan)}</span>`
                        : '<span class="vul-admin-meta">—</span>'}
                </td>
                <td class="vul-admin-kondisi">
                    ${r.kondisi_setelah_penggunaan
                        ? `<span title="${escHtml(r.kondisi_setelah_full)}">${escHtml(r.kondisi_setelah_penggunaan)}</span>`
                        : '<span class="vul-admin-meta">—</span>'}
                </td>
            </tr>
        `;
        }).join('');
    }

    /* ================================================================
    PAGINATION
    ================================================================ */
    function mountPagination(html) {
        const el = document.getElementById('vul-pagination');
        if (!el) return;

        function _mount() {
            window.AdminPagination.mountPagination(el, html || '');
            if (!el.dataset.paginationBound) {
                el.dataset.paginationBound = '1';
                window.AdminPagination.bindPaginationLinks(el, (url) => {
                    _page = parseInt(url.searchParams.get('page') || '1', 10);
                    fetchLogs(true);
                }, { pathname: new URL(API_URL).pathname });
            }
        }

        if (window.AdminPagination) {
            _mount();
        } else {
            const iv = setInterval(() => {
                if (window.AdminPagination) { clearInterval(iv); _mount(); }
            }, 30);
        }
    }

    /* ================================================================
    FETCH
    ================================================================ */
    async function fetchLogs(scroll = false) {
        _abort?.abort();
        _abort = new AbortController();
        showLoading();

        const q = buildParams();
        try {
            const res  = await fetch(`${API_URL}?${q}`, { signal: _abort.signal });
            const json = await res.json();

            if (selectAllCheckbox && json.total !== undefined) {
                selectAllCheckbox.dataset.total = json.total;
            }

            renderTable(json);
            mountPagination(json.pagination_html);

            if (_isAllSelected) {
                const tbody = document.getElementById('vul-tbody');
                if (tbody) {
                    tbody.querySelectorAll('.vul-row-checkbox').forEach(cb => cb.checked = true);
                }
            }
            updateBulkActionState();

            if (window.AdminTableSort) {
                window.AdminTableSort.syncAria(
                    document.getElementById('vul-thead'),
                    json.sort ?? null,
                    json.dir  ?? null
                );
            }

            updateFilterChrome();

            if (scroll) {
                const section = document.querySelector('.portal-section');
                if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } catch (e) {
            if (e.name !== 'AbortError') console.warn('VUL fetchLogs error', e);
        } finally {
            hideLoading();
        }
    }

    /* ================================================================
    WIRE FILTERS
    ================================================================ */
    function updateFilterChrome() {
        const qVal = document.getElementById('vul-filter-q')?.value.trim() ?? '';
        const monthVal = document.getElementById('vul-filter-month')?.value ?? '';
        const yearVal = document.getElementById('vul-filter-year')?.value ?? '';
        const showReset = qVal.length > 0 || monthVal !== '' || yearVal !== '' || _perPage !== 25;
        const resetBtn = document.getElementById('vul-logs-reset');
        if (resetBtn) resetBtn.style.display = showReset ? '' : 'none';
    }

    const debouncedFetch = debounce(() => { _isAllSelected = false; _page = 1; fetchLogs(); });

    document.getElementById('vul-filter-q')?.addEventListener('input', () => {
        updateFilterChrome();
        debouncedFetch();
    });
    document.getElementById('vul-filter-month')?.addEventListener('change', () => {
        _isAllSelected = false;
        _page = 1;
        fetchLogs();
    });
    document.getElementById('vul-filter-year')?.addEventListener('change', () => {
        _isAllSelected = false;
        _page = 1;
        fetchLogs();
    });

    document.getElementById('vul-arch-per')?.addEventListener('change', (e) => {
        _isAllSelected = false;
        _perPage = parseInt(e.target.value, 10);
        _page = 1;
        fetchLogs();
    });

    document.getElementById('vul-logs-reset')?.addEventListener('click', () => {
        const q     = document.getElementById('vul-filter-q');
        const month = document.getElementById('vul-filter-month');
        const year  = document.getElementById('vul-filter-year');
        const pp    = document.getElementById('vul-arch-per');

        if (q)     q.value = '';
        if (month) month.selectedIndex = 0;
        if (year)  year.selectedIndex  = 0;
        if (pp) { pp.value = '25'; _perPage = 25; }

        _isAllSelected = false;
        _page = 1; _sort = ''; _dir = '';
        updateFilterChrome();
        fetchLogs();
    });

    /* ================================================================
    SORT HEADER WIRING
    ================================================================ */
    if (window.AdminTableSort) {
        const tableWrap = document.getElementById('vul-thead')?.closest('.admin-table-wrap');
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
                    _dir  = url.searchParams.get('dir')  || '';
                    _page = 1;
                    fetchLogs();
                },
            });
        }
    }

    /* ================================================================
    INITIAL PAGINATION MOUNT (from server-rendered HTML)
    ================================================================ */
    (function () {
        const initHtml = @json($paginationHtml ?? '');
        if (initHtml) mountPagination(initHtml);
        updateFilterChrome();
    })();

    function updateBulkActionState() {
        const tbody = document.getElementById('vul-tbody');
        if (!tbody || !bulkDeleteBtn || !bulkSelectCount) return;
        const checkboxes = Array.from(tbody.querySelectorAll('.vul-row-checkbox'));
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

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', () => {
            const tbody = document.getElementById('vul-tbody');
            if (!tbody) return;
            _isAllSelected = selectAllCheckbox.checked;
            const checkboxes = tbody.querySelectorAll('.vul-row-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = _isAllSelected;
            });
            updateBulkActionState();
        });
    }

    const tbody = document.getElementById('vul-tbody');
    if (tbody) {
        tbody.addEventListener('change', (e) => {
            if (e.target.classList.contains('vul-row-checkbox')) {
                if (!e.target.checked) {
                    _isAllSelected = false;
                }
                updateBulkActionState();
            }
        });
    }

    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', () => {
            const tbody = document.getElementById('vul-tbody');
            if (!tbody) return;
            
            let payload = {};
            let displayCount = 0;

            if (_isAllSelected) {
                payload = {
                    all: true,
                    search: document.getElementById('vul-filter-q')?.value.trim() ?? '',
                    month: document.getElementById('vul-filter-month')?.value ?? '',
                    year: document.getElementById('vul-filter-year')?.value ?? '',
                };
                displayCount = parseInt(selectAllCheckbox?.dataset.total, 10) || 0;
            } else {
                const selectedIds = Array.from(tbody.querySelectorAll('.vul-row-checkbox:checked'))
                    .map(cb => cb.value);
                if (selectedIds.length === 0) return;
                payload = {
                    ids: selectedIds
                };
                displayCount = selectedIds.length;
            }

            if (!window.Swal) return;

            Swal.fire({
                title: 'Hapus data log pemakaian?',
                text: `Anda yakin ingin menghapus ${displayCount} data log pemakaian terpilih? Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                buttonsStyling: false,
                customClass: {
                    popup: 'swal-ppm-popup',
                    title: 'swal-ppm-title',
                    confirmButton: 'swal-ppm-confirm',
                    cancelButton: 'swal-ppm-cancel',
                },
            }).then(async (result) => {
                if (result.isConfirmed) {
                    showLoading();
                    try {
                        const res = await fetch('/admin/log-penggunaan-kendaraan/bulk-delete', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                                customClass: {
                                    popup: 'swal-ppm-popup',
                                    title: 'swal-ppm-title',
                                }
                            });
                            return;
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: json.message || 'Data log pemakaian kendaraan terpilih berhasil dihapus.',
                            timer: 1500,
                            showConfirmButton: false,
                            customClass: {
                                popup: 'swal-ppm-popup',
                                title: 'swal-ppm-title',
                                icon: 'swal-ppm-icon-success',
                            }
                        });
                        // Reset selections
                        _isAllSelected = false;
                        if (selectAllCheckbox) selectAllCheckbox.checked = false;
                        updateBulkActionState();
                        // Refresh data
                        fetchLogs();
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

    updateBulkActionState();

})();
</script>
@endpush
