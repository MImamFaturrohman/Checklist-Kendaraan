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
                                <tr><td colspan="12" class="portal-empty">Belum ada log penggunaan kendaraan.</td></tr>
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
            tbody.innerHTML = '<tr><td colspan="12" class="portal-empty">Belum ada log penggunaan kendaraan.</td></tr>';
            return;
        }

        tbody.innerHTML = json.data.map((r, i) => `
            <tr>
                <td>${off + i + 1}</td>
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
        `).join('');
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

            renderTable(json);
            mountPagination(json.pagination_html);

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

    const debouncedFetch = debounce(() => { _page = 1; fetchLogs(); });

    document.getElementById('vul-filter-q')?.addEventListener('input', () => {
        updateFilterChrome();
        debouncedFetch();
    });
    document.getElementById('vul-filter-month')?.addEventListener('change', () => {
        _page = 1;
        fetchLogs();
    });
    document.getElementById('vul-filter-year')?.addEventListener('change', () => {
        _page = 1;
        fetchLogs();
    });

    document.getElementById('vul-arch-per')?.addEventListener('change', (e) => {
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

})();
</script>
@endpush
