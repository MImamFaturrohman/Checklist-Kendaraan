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
            border: 1px solid rgba(185, 28, 28, 0.18);
        }
        .lk-kat-nm {
            background: rgba(180, 83, 9, 0.08);
            color: #b45309;
            border: 1px solid rgba(180, 83, 9, 0.18);
        }
        html.dark .dash-body .lk-kat-inc {
            background: rgba(185, 28, 28, 0.08) !important;
            color: #b91c1c !important;
            border-color: rgba(185, 28, 28, 0.18) !important;
        }
        html.dark .dash-body .lk-kat-nm {
            background: rgba(180, 83, 9, 0.08) !important;
            color: #b45309 !important;
            border-color: rgba(180, 83, 9, 0.18) !important;
        }
        .lk-daftar-filters.portal-local-filters { align-items: stretch; }
        .lk-daftar-filters .portal-search-full { flex: 1 1 200px; min-width: 0; }
        .lk-daftar-filters .lk-kategori-wrap { flex: 0 0 auto; }
        .lk-daftar-filters .lk-kategori-wrap select {
            min-width: 0; max-width: 200px; width: 100%; box-sizing: border-box;
        }
        @media (max-width: 640px) {
            .lk-daftar-filters.portal-local-filters {
                flex-direction: column; flex-wrap: nowrap; align-items: stretch; gap: 10px;
            }
            .lk-daftar-filters .portal-search-full,
            .lk-daftar-filters .lk-kategori-wrap {
                flex: 0 0 auto; width: 100%; max-width: none;
            }
            .lk-daftar-filters .lk-kategori-wrap select {
                width: 100%; max-width: none; padding: 10px 12px; font-size: 0.85rem;
            }
            .lk-daftar-filters .tbl-per-page {
                margin-left: 0; width: 100%; justify-content: space-between;
                padding-top: 2px; border-top: 1px solid rgba(148, 163, 184, 0.18);
            }
            html.dark .dash-body .lk-daftar-filters .tbl-per-page {
                border-top-color: rgba(255, 255, 255, 0.08);
            }
            .lk-daftar-filters .lk-filter-reset {
                width: 100%; justify-content: center; text-align: center;
            }
        }
    </style>
@endpush

@section('content')
    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">

            <div class="portal-stats-row" data-stat-count="3">
                <x-admin-stat-card
                    title="Total Laporan"
                    :value="$stats['total']"
                    unit="laporan"
                    description="Seluruh laporan kejadian tercatat"
                    icon="bi bi-clipboard-data-fill"
                />
                <x-admin-stat-card
                    title="Incident"
                    :value="$stats['incident']"
                    unit="kejadian"
                    description="Laporan insiden yang terjadi"
                    icon="bi bi-exclamation-triangle-fill"
                    valueStyle="color:#b91c1c"
                />
                <x-admin-stat-card
                    title="Near Miss"
                    :value="$stats['nearmiss']"
                    unit="kejadian"
                    description="Hampir terjadi insiden (near miss)"
                    icon="bi bi-shield-fill-exclamation"
                    valueStyle="color:#b45309"
                />
            </div>

            <div class="portal-section" style="margin-top: 4px" data-lk-admin-live>
                <div class="portal-section-header">
                    <div class="portal-section-title">
                        <i class="bi bi-table"></i> Daftar Laporan Kejadian
                    </div>
                </div>

                <div class="portal-local-filters lk-daftar-filters" style="margin-top: 16px">
                    <div class="admin-search-wrap portal-search-full">
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
                    <div class="lk-kategori-wrap">
                        <select id="lk-kategori-live" class="admin-filter-input" aria-label="Filter kategori">
                            <option value="">Semua kategori</option>
                            <option value="Incident" {{ request('kategori') === 'Incident' ? 'selected' : '' }}>Incident</option>
                            <option value="Nearmiss" {{ request('kategori') === 'Nearmiss' ? 'selected' : '' }}>Near Miss</option>
                        </select>
                    </div>
                    <x-admin-per-page-select id="lk-per-page" name="per_page" :selected="$laporans->perPage()" />
                    <button type="button" class="portal-local-reset lk-filter-reset" id="lk-filter-reset" title="Reset filter"
                        style="display: none">Reset</button>
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

    function initLkAdminLive() {
        if (liveAbort) liveAbort.abort();
        liveAbort = new AbortController();
        const { signal } = liveAbort;

        const root = document.querySelector('[data-lk-admin-live]');
        if (!root) return;

        const searchEl = document.getElementById('lk-search-live');
        const kategoriEl = document.getElementById('lk-kategori-live');
        const perPageEl = document.getElementById('lk-per-page');
        const tbody = document.getElementById('lk-tbody');
        const pagEl = document.getElementById('lk-pagination');
        const clearBtn = document.getElementById('lk-search-clear');
        const resetBtn = document.getElementById('lk-filter-reset');
        if (!searchEl || !kategoriEl || !perPageEl || !tbody || !pagEl) return;

        function updateFilterChrome() {
            const hasSearch = searchEl.value.trim().length > 0;
            if (clearBtn) clearBtn.style.display = hasSearch ? 'flex' : 'none';
            const showReset = hasSearch
                || (kategoriEl.value && kategoriEl.value !== '')
                || perPageEl.value !== DEFAULT_PER_PAGE;
            if (resetBtn) resetBtn.style.display = showReset ? '' : 'none';
        }

        function syncFiltersFromUrl(u, data) {
            searchEl.value = u.searchParams.get('search') || '';
            kategoriEl.value = u.searchParams.get('kategori') || '';
            const pp = data?.per_page ?? u.searchParams.get('per_page');
            if (pp) perPageEl.value = String(pp);
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
            const search = overrides.search !== undefined ? overrides.search : searchEl.value.trim();
            const kategori = overrides.kategori !== undefined ? overrides.kategori : kategoriEl.value;
            const perPage = overrides.per_page !== undefined ? overrides.per_page : perPageEl.value;
            if (search) u.searchParams.set('search', search); else u.searchParams.delete('search');
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

        let debounceT;
        searchEl.addEventListener('input', () => {
            updateFilterChrome();
            clearTimeout(debounceT);
            debounceT = setTimeout(() => {
                fetchListFromUrl(buildListUrl({ page: null }));
            }, 320);
        }, { signal });

        kategoriEl.addEventListener('change', () => {
            fetchListFromUrl(buildListUrl({ page: null }));
        }, { signal });

        perPageEl.addEventListener('change', () => {
            fetchListFromUrl(buildListUrl({ page: null }));
        }, { signal });

        pagEl.addEventListener('click', (e) => {
            const a = e.target.closest('.tbl-pagination a[href], a[href]');
            if (!a || !pagEl.contains(a)) return;
            const u = new URL(a.getAttribute('href'), location.origin);
            if (u.pathname !== new URL(listUrl, location.origin).pathname) return;
            e.preventDefault();
            fetchListFromUrl(u);
        }, { signal });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                searchEl.value = '';
                fetchListFromUrl(buildListUrl({ search: '', page: null }));
            }, { signal });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                searchEl.value = '';
                kategoriEl.value = '';
                perPageEl.value = DEFAULT_PER_PAGE;
                fetchListFromUrl(buildListUrl({ search: '', kategori: '', per_page: DEFAULT_PER_PAGE, sort: '', dir: '', page: null }));
            }, { signal });
        }

        // Wire sort header clicks via AdminTableSort
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
