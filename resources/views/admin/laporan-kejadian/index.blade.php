@extends('layouts.dash-app')

@section('title', 'Laporan Kejadian')
@section('pageTitle', 'Laporan Kejadian')
@section('pageSubtitle', 'PT. ARTHA DAYA COALINDO')

@php $premiumBgId = 'admin_laporan_kejadian'; @endphp

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
                            <button type="button" id="lk-search-clear" class="admin-search-clear" title="Hapus pencarian"
                                style="display: {{ request('search') ? 'flex' : 'none' }}">&times;</button>
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
            updateFilterChrome();
            clearTimeout(debounceT);
            debounceT = setTimeout(() => {
                fetchListFromUrl(buildListUrl({ page: null }));
            }, 320);
        }, { signal });

        // ── Per page ──────────────────────────────────────────────────
        perPageEl.addEventListener('change', () => {
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
                searchEl.value = '';
                fetchListFromUrl(buildListUrl({ search: '', page: null }));
            }, { signal });
        }

        // ── Reset all filters ─────────────────────────────────────────
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
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
                onNavigate: (url) => fetchListFromUrl(url),
            });
        }

        updateFilterChrome();
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
