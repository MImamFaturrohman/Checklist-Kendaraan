@extends('layouts.dash-app')

@section('title', 'Laporan Kejadian')
@section('pageTitle', 'Laporan Kejadian')
@section('pageSubtitle', 'PT. ARTHA DAYA COALINDO')

@php $premiumBgId = 'admin_laporan_kejadian'; @endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
@endpush

@push('styles')
<style>
        .lk-admin-name { font-weight: 700; color: var(--dash-text-primary, #0f172a); }
        .lk-admin-meta { font-size: 0.76rem; opacity: 0.85; color: #64748b; }
        .dash-body.dark .lk-admin-meta { color: rgba(200, 218, 255, 0.62); }
        .lk-admin-lokasi { font-size: 0.84rem; line-height: 1.45; max-width: 280px; }
        .lk-admin-waktu { font-size: 0.84rem; white-space: nowrap; }
        .lk-pending {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 10px; background: rgba(245, 158, 11, 0.08); color: #b45309;
            border: 1px solid rgba(180, 83, 9, 0.18); border-radius: 8px;
            font-size: 0.75rem; font-weight: 700; white-space: nowrap;
        }
        html.dark .dash-body .lk-pending {
            background: rgba(180, 83, 9, 0.08);
            color: rgba(180, 83, 9, 0.88);
            border-color: rgba(180, 83, 9, 0.16);
        }
        .lk-kat { font-size: 0.72rem; font-weight: 700; padding: 5px 12px; border-radius: 999px; white-space: nowrap; }
        .lk-kat-inc {
            background: rgba(185, 28, 28, 0.08);
            color: #b91c1c;
        }
        .lk-kat-nm {
            background: rgba(158, 73, 8, 0.08);
            color: #ffbf00;
        }
        html.dark .dash-body .lk-kat-inc {
            background: rgba(185, 28, 28, 0.08) !important;
            color: #b91c1c !important;
        }
        html.dark .dash-body .lk-kat-nm {
            background: rgba(180, 83, 9, 0.08) !important;
            color: #ffbf00 !important;
        }
        .lk-header-filters {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .lk-header-search {
            width: 360px;
            max-width: 100%;
        }
        @media (max-width: 640px) {
            .lk-header-filters {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }
            .lk-header-search {
                width: 100%;
            }
            .lk-header-filters .tbl-per-page {
                width: 100%;
                justify-content: space-between;
                padding-top: 2px;
                border-top: 1px solid rgba(148, 163, 184, 0.18);
            }
            html.dark .dash-body .lk-header-filters .tbl-per-page {
                border-top-color: rgba(255, 255, 255, 0.08);
            }
            .lk-header-filters .lk-filter-reset {
                width: 100%;
                justify-content: center;
                text-align: center;
            }
        }


        /* ── Filterable stat card: clickable cursor ── */
        .portal-stat-card[data-filter-key] {
            cursor: pointer;
            user-select: none;
        }
        .portal-stat-card[data-filter-key]:focus-visible {
            outline: 2px solid rgba(212, 175, 55, 0.7);
            outline-offset: 2px;
        }

        /* ── Active state — light mode (glassmorphism + gold glow) ── */
        html:not(.dark) .dash-body .portal-stat-card[data-filter-key].is-active {
            background: linear-gradient(
                135deg,
                rgba(212, 175, 55, 0.15) 0%,
                rgba(240, 247, 255, 0.55) 100%
            );
            border-color: rgba(212, 175, 55, 0.55);
            box-shadow:
                0 12px 40px rgba(31, 38, 135, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.9),
                0 0 0 2px rgba(212, 175, 55, 0.25),
                0 0 18px 2px rgba(212, 175, 55, 0.18);
        }
        html:not(.dark) .dash-body .portal-stat-card[data-filter-key].is-active .portal-stat-icon {
            color: #d4af37 !important;
        }

        /* ── Active state — dark mode (glassmorphism + gold glow) ── */
        html.dark .dash-body .portal-stat-card[data-filter-key].is-active {
            background: rgba(212, 175, 55, 0.08);
            border-color: rgba(212, 175, 55, 0.4);
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.35),
                inset 0 1px 0 rgba(255, 255, 255, 0.12),
                0 0 0 2px rgba(212, 175, 55, 0.2),
                0 0 22px 4px rgba(212, 175, 55, 0.14);
        }
        html.dark .dash-body .portal-stat-card[data-filter-key].is-active .portal-stat-icon {
            color: #d4af37 !important;
        }

        /* Bulk Actions & Checkbox Styles */
        .lk-bulk-actions-wrap label {
            color: #475569;
        }
        html.dark .lk-bulk-actions-wrap label {
            color: rgba(200, 218, 255, 0.85);
        }
        html.dark .lk-bulk-actions-wrap div {
            background: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* Bulk Delete Button Styling */
        #lk-btn-bulk-delete {
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
        #lk-btn-bulk-delete:hover {
            background-color: #b91c1c;
            color: #ffffff !important;
            border-color: #b91c1c;
            box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.15);
        }
        
        /* Dark mode overrides for Bulk Delete Button */
        html.dark #lk-btn-bulk-delete {
            background-color: transparent;
            color: #fca5a5;
            border-color: rgba(248, 113, 113, 0.35);
        }
        html.dark #lk-btn-bulk-delete:hover {
            background-color: #ef4444;
            color: #ffffff !important;
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25);
        }

        /* Modern Checkbox styling: slightly rounded edges & premium dark/light mode appearance */
        .lk-row-checkbox, #lk-select-all {
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

        html.dark .lk-row-checkbox, html.dark #lk-select-all {
            border-color: rgba(255, 255, 255, 0.25);
            background-color: rgba(15, 23, 42, 0.6);
        }

        .lk-row-checkbox:hover, #lk-select-all:hover {
            border-color: #002a7a;
            box-shadow: 0 0 0 3px rgba(0, 42, 122, 0.15);
        }
        html.dark .lk-row-checkbox:hover, html.dark #lk-select-all:hover {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
        }

        .lk-row-checkbox:checked, #lk-select-all:checked {
            background-color: #002a7a;
            border-color: #002a7a;
        }
        html.dark .lk-row-checkbox:checked, html.dark #lk-select-all:checked {
            background-color: #60a5fa;
            border-color: #60a5fa;
        }

        /* Checkmark icon */
        .lk-row-checkbox:checked::after, #lk-select-all:checked::after {
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
        html.dark .lk-row-checkbox:checked::after, html.dark #lk-select-all:checked::after {
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

            <div class="portal-stats-row" data-stat-count="3" id="lk-stat-filter-group">
                <x-admin-stat-card
                    title="Total Laporan"
                    :value="$stats['total']"
                    unit="Laporan"
                    description="Seluruh laporan kejadian tercatat"
                    icon="bi bi-clipboard-data-fill"
                    filterKey="kategori"
                    filterValue=""
                />
                <x-admin-stat-card
                    title="Incident"
                    :value="$stats['incident']"
                    unit="Kejadian"
                    description="Laporan insiden yang terjadi"
                    icon="bi bi-exclamation-triangle-fill"
                    valueStyle="color:#b91c1c"
                    filterKey="kategori"
                    filterValue="Incident"
                />
                <x-admin-stat-card
                    title="Near Miss"
                    :value="$stats['nearmiss']"
                    unit="Kejadian"
                    description="Hampir terjadi insiden (near miss)"
                    icon="bi bi-shield-fill-exclamation"
                    valueStyle="color:#ffbf00"
                    filterKey="kategori"
                    filterValue="Nearmiss"
                />
            </div>

            <div class="portal-section" style="margin-top: 4px" data-lk-admin-live>
                <div class="portal-section-header">
                    <div class="portal-section-title">
                        <i class="bi bi-table"></i> Daftar Laporan Kejadian
                    </div>

                    <div class="lk-header-filters">
                        <!-- Bulk Actions Container -->
                        <div class="lk-bulk-actions-wrap" style="display: flex; align-items: center; gap: 8px;">
                            <button type="button" id="lk-btn-bulk-delete" style="display: none;">
                                <i class="bi bi-trash-fill"></i> Hapus (<span id="lk-bulk-select-count">0</span>)
                            </button>
                            
                            <div style="display: flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 8px; background: rgba(148, 163, 184, 0.1); border: 1px solid rgba(148, 163, 184, 0.25);">
                                <input type="checkbox" id="lk-select-all" data-total="{{ $laporans->total() }}" title="Pilih Semua">
                                <label for="lk-select-all" style="font-size: 0.78rem; font-weight: 700; cursor: pointer; user-select: none; margin: 0; display: flex; align-items: center;">Pilih</label>
                            </div>
                        </div>

                        <div class="admin-search-wrap lk-header-search">
                            <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                                <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <input type="text" id="lk-search-live" autocomplete="off"
                                inputmode="search" enterkeyhint="search"
                                value="{{ request('search') }}"
                                placeholder="Cari nama, NIP, lokasi, kendaraan…"
                                class="admin-search-input">
                        </div>
                        <x-admin-per-page-select id="lk-per-page" name="per_page" :selected="$laporans->perPage()" />
                        <button type="button" class="btn btn-sm sppd-icon-btn admin-filter-reset" id="lk-filter-reset" title="Reset filter" aria-label="Reset filter" style="display: none"><i class="bi bi-arrow-clockwise"></i></button>
                    </div>
                </div>

                <div class="admin-table-wrap" style="margin-top: 16px">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width:52px">#</th>
                                <th style="width: 40px; text-align: center;">Pilih</th>
                                <x-sortable-th key="nama" label="Pelapor" :activeSort="$activeSort" :activeDir="$activeDir" />
                                <x-sortable-th key="created_at" label="Waktu" :activeSort="$activeSort" :activeDir="$activeDir" />
                                <x-sortable-th key="kategori" label="Kategori" :activeSort="$activeSort" :activeDir="$activeDir" />
                                <x-sortable-th key="lokasi_kejadian" label="Lokasi" :activeSort="$activeSort" :activeDir="$activeDir" />
                                <x-sortable-th key="nomor_kendaraan" label="Kendaraan" :activeSort="$activeSort" :activeDir="$activeDir" />
                                <th style="width: 125px; white-space:nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="lk-tbody">
                            @include('admin.partials.laporan-kejadian-rows')
                        </tbody>
                    </table>
                </div>

                <div id="lk-pagination" class="tbl-pagination-mount">
                    <x-admin-pagination :paginator="$laporans" />
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
window.LK_LIST_URL = @json(route('admin.laporan-kejadian.index'));

(function () {
    const listUrl = window.LK_LIST_URL;
    const DEFAULT_PER_PAGE = '10';
    let liveAbort = null;

    // Internal state for the active kategori filter (driven by stat card clicks)
    let activeKategori = '{{ request('kategori', '') }}';

    function initLkAdminLive() {
        if (liveAbort) liveAbort.abort();
        liveAbort = new AbortController();
        const { signal } = liveAbort;

        const root = document.querySelector('[data-lk-admin-live]');
        if (!root) return;

        const searchEl   = document.getElementById('lk-search-live');
        const perPageEl  = document.getElementById('lk-per-page');
        const tbody      = document.getElementById('lk-tbody');
        const pagEl      = document.getElementById('lk-pagination');
        const clearBtn   = document.getElementById('lk-search-clear');
        const resetBtn   = document.getElementById('lk-filter-reset');
        const statGroup  = document.getElementById('lk-stat-filter-group');
        if (!searchEl || !perPageEl || !tbody || !pagEl) return;

        let _isAllSelected = false;

        const selectAllCheckbox = document.getElementById('lk-select-all');
        const bulkDeleteBtn = document.getElementById('lk-btn-bulk-delete');
        const bulkSelectCount = document.getElementById('lk-bulk-select-count');

        // Collect all filterable stat cards in the group
        const filterCards = statGroup
            ? Array.from(statGroup.querySelectorAll('[data-filter-key]'))
            : [];

        // ── Sync active-card visual state ──────────────────────────────
        function syncCardActiveState() {
            filterCards.forEach(card => {
                const cardVal = card.dataset.filterValue ?? '';
                card.classList.toggle('is-active', activeKategori !== '' && cardVal === activeKategori);
            });
        }

        // ── Filter chrome (clear button + reset button) ────────────────
        function updateFilterChrome() {
            const hasSearch = searchEl.value.trim().length > 0;
            if (clearBtn) clearBtn.style.display = hasSearch ? 'flex' : 'none';
            const showReset = hasSearch
                || activeKategori !== ''
                || perPageEl.value !== DEFAULT_PER_PAGE;
            if (resetBtn) resetBtn.style.display = showReset ? '' : 'none';
            syncCardActiveState();
        }

        function updateBulkActionState() {
            if (!tbody || !bulkDeleteBtn || !bulkSelectCount) return;
            const checkboxes = Array.from(tbody.querySelectorAll('.lk-row-checkbox'));
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

        function syncFiltersFromUrl(u, data) {
            searchEl.value = u.searchParams.get('search') || '';
            const pp = data?.per_page ?? u.searchParams.get('per_page');
            if (pp) perPageEl.value = String(pp);
            // kategori is managed by activeKategori; update it from URL on navigation
            const kat = u.searchParams.get('kategori') || '';
            activeKategori = kat;
        }

        async function fetchListFromUrl(url) {
            const u = url instanceof URL ? url : new URL(url, location.origin);
            try {
                const res = await fetch(u.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    cache: 'no-store',
                    signal,
                });
                let data = {};
                try { data = await res.json(); } catch (e) { /* ignore */ }
                if (!res.ok) {
                    if (window.Swal) {
                        Swal.fire({ icon: 'error', title: 'Gagal memuat data', text: data.message || ('HTTP ' + res.status), confirmButtonColor: '#002a7a' });
                    }
                    return;
                }
                tbody.innerHTML = data.tbody || '';
                if (window.AdminPagination) {
                    window.AdminPagination.mountPagination(pagEl, data.pagination_html || '');
                } else {
                    pagEl.innerHTML = data.pagination_html || '';
                }
                if (window.AdminTableSort) {
                    const table = tbody.closest('table');
                    window.AdminTableSort.syncAria(table, data.sort ?? null, data.dir ?? null);
                }
                syncFiltersFromUrl(u, data);

                if (selectAllCheckbox && data.total !== undefined) {
                    selectAllCheckbox.dataset.total = data.total;
                }

                if (_isAllSelected) {
                    tbody.querySelectorAll('.lk-row-checkbox').forEach(cb => cb.checked = true);
                }
                updateBulkActionState();

                try {
                    history.replaceState(null, '', u.pathname + u.search);
                } catch (e) { /* ignore */ }
                updateFilterChrome();
            } catch (err) {
                if (err?.name === 'AbortError') return;
                throw err;
            }
        }

        function buildListUrl(overrides = {}) {
            const u = new URL(listUrl, location.origin);
            const cur = new URL(location.href);
            const search  = overrides.search   !== undefined ? overrides.search   : searchEl.value.trim();
            const kategori = overrides.kategori !== undefined ? overrides.kategori : activeKategori;
            const perPage  = overrides.per_page !== undefined ? overrides.per_page : perPageEl.value;
            if (search)  u.searchParams.set('search',  search);  else u.searchParams.delete('search');
            if (kategori) u.searchParams.set('kategori', kategori); else u.searchParams.delete('kategori');
            if (perPage) u.searchParams.set('per_page', String(perPage)); else u.searchParams.delete('per_page');
            if (Object.prototype.hasOwnProperty.call(overrides, 'page')) {
                if (overrides.page) u.searchParams.set('page', String(overrides.page));
                else u.searchParams.delete('page');
            } else {
                u.searchParams.delete('page');
            }
            // preserve active sort (unless explicitly overridden)
            const sortVal = Object.prototype.hasOwnProperty.call(overrides, 'sort') ? overrides.sort : cur.searchParams.get('sort');
            const dirVal  = Object.prototype.hasOwnProperty.call(overrides, 'dir')  ? overrides.dir  : cur.searchParams.get('dir');
            if (sortVal) u.searchParams.set('sort', sortVal); else u.searchParams.delete('sort');
            if (dirVal)  u.searchParams.set('dir', dirVal);   else u.searchParams.delete('dir');
            return u;
        }

        // ── Stat card filter toggle ────────────────────────────────────
        filterCards.forEach(card => {
            const activate = () => {
                _isAllSelected = false;
                const cardVal = card.dataset.filterValue ?? '';
                // Toggle off if already active (except "Total" which is always a reset)
                if (cardVal !== '' && activeKategori === cardVal) {
                    activeKategori = '';
                } else {
                    activeKategori = cardVal; // '' = all (Total card)
                }
                updateFilterChrome();
                fetchListFromUrl(buildListUrl({ page: null }));
            };
            card.addEventListener('click', activate, { signal });
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); activate(); }
            }, { signal });
        });

        // ── Search input ───────────────────────────────────────────────
        let debounceT;
        searchEl.addEventListener('input', () => {
            _isAllSelected = false;
            updateFilterChrome();
            clearTimeout(debounceT);
            debounceT = setTimeout(() => {
                fetchListFromUrl(buildListUrl({ page: null }));
            }, 320);
        }, { signal });

        // ── Per page ──────────────────────────────────────────────────
        perPageEl.addEventListener('change', () => {
            _isAllSelected = false;
            fetchListFromUrl(buildListUrl({ page: null }));
        }, { signal });

        // ── Pagination links ──────────────────────────────────────────
        pagEl.addEventListener('click', (e) => {
            const a = e.target.closest('.tbl-pagination a[href], a[href]');
            if (!a || !pagEl.contains(a)) return;
            const u = new URL(a.getAttribute('href'), location.origin);
            if (u.pathname !== new URL(listUrl, location.origin).pathname) return;
            e.preventDefault();
            fetchListFromUrl(u);
        }, { signal });

        // ── Clear search ──────────────────────────────────────────────
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                _isAllSelected = false;
                searchEl.value = '';
                fetchListFromUrl(buildListUrl({ search: '', page: null }));
            }, { signal });
        }

        // ── Reset all filters ─────────────────────────────────────────
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                _isAllSelected = false;
                searchEl.value = '';
                activeKategori = '';
                perPageEl.value = DEFAULT_PER_PAGE;
                fetchListFromUrl(buildListUrl({ search: '', kategori: '', per_page: DEFAULT_PER_PAGE, sort: '', dir: '', page: null }));
            }, { signal });
        }

        // ── Sort headers ──────────────────────────────────────────────
        if (window.AdminTableSort) {
            window.AdminTableSort.bindRoot(root, {
                getUrl: () => new URL(location.href),
                onNavigate: (url) => {
                    _isAllSelected = false;
                    return fetchListFromUrl(url);
                },
            });
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', () => {
                if (!tbody) return;
                _isAllSelected = selectAllCheckbox.checked;
                const checkboxes = tbody.querySelectorAll('.lk-row-checkbox');
                checkboxes.forEach(cb => {
                    cb.checked = _isAllSelected;
                });
                updateBulkActionState();
            }, { signal });
        }

        tbody.addEventListener('change', (e) => {
            if (e.target.classList.contains('lk-row-checkbox')) {
                if (!e.target.checked) {
                    _isAllSelected = false;
                }
                updateBulkActionState();
            }
        }, { signal });

        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', () => {
                if (!tbody) return;
                
                let payload = {};
                let displayCount = 0;

                if (_isAllSelected) {
                    payload = {
                        all: true,
                        search: searchEl.value.trim(),
                        kategori: activeKategori,
                    };
                    displayCount = parseInt(selectAllCheckbox?.dataset.total, 10) || 0;
                } else {
                    const selectedIds = Array.from(tbody.querySelectorAll('.lk-row-checkbox:checked'))
                        .map(cb => cb.value);
                    if (selectedIds.length === 0) return;
                    payload = {
                        ids: selectedIds
                    };
                    displayCount = selectedIds.length;
                }

                if (!window.Swal) return;

                Swal.fire({
                    title: 'Hapus laporan kejadian?',
                    text: `Anda yakin ingin menghapus ${displayCount} data laporan kejadian terpilih? Tindakan ini tidak dapat dibatalkan.`,
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
                        try {
                            const res = await fetch('/admin/laporan-kejadian/bulk-delete', {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': @json(csrf_token()),
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
                                text: json.message || 'Data laporan kejadian terpilih berhasil dihapus.',
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
                            fetchListFromUrl(buildListUrl());
                        } catch (err) {
                            console.error(err);
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem.' });
                        }
                    }
                });
            }, { signal });
        }

        updateFilterChrome();
        updateBulkActionState();
    }

    function scheduleLkInit() {
        requestAnimationFrame(initLkAdminLive);
    }

    document.addEventListener('turbo:load', scheduleLkInit);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scheduleLkInit);
    } else {
        scheduleLkInit();
    }

    if (typeof window.registerTurboCleanup === 'function') {
        window.registerTurboCleanup(function () {
            if (liveAbort) liveAbort.abort();
        });
    }
})();
</script>
@endpush
