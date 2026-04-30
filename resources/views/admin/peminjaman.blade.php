<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Peminjaman Kendaraan - {{ config('app.name') }}</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .peminj-name { font-weight: 700; }
        .peminj-meta { font-size: 0.76rem; opacity: 0.85; }
        .peminj-meta-sm { font-size: 0.72rem; opacity: 0.8; }
        .peminj-bidang-nama { font-weight: 600; }
        .peminj-pdf {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 10px; background: #002a7a; color: #fff !important;
            border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-decoration: none;
            transition: background 0.15s;
        }
        .peminj-pdf:hover { background: #0038a8; color: #fff !important; }
        .dash-body.dark .peminj-pdf {
            background: rgba(30, 64, 128, 0.95);
            border: 1px solid rgba(212, 175, 55, 0.28);
        }
        .dash-body.dark .peminj-pdf:hover { background: rgba(40, 80, 150, 0.98); }
        .peminj-empty { text-align: center; color: #9ca3af; padding: 40px 12px; }
        .dash-body.dark .peminj-empty { color: rgba(200, 218, 255, 0.45); }

        .ppm-master-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .ppm-tree { font-size: 0.88rem; }
        .ppm-tree ul { list-style: none; margin: 0; padding-left: 0; }
        .ppm-tree ul ul { margin-top: 6px; padding-left: 22px; border-left: 2px solid #e2e8f0; }
        .dash-body.dark .ppm-tree ul ul { border-left-color: rgba(255,255,255,0.12); }
        .ppm-tree-row {
            display: flex; flex-wrap: wrap; align-items: center; gap: 8px 12px;
            padding: 8px 10px; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0;
            margin-bottom: 6px;
        }
        .dash-body.dark .ppm-tree-row {
            background: rgba(5, 11, 20, 0.45);
            border-color: rgba(255,255,255,0.08);
        }
        .ppm-tree-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-left: auto; }
        .ppm-btn-ghost {
            padding: 5px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 600;
            border: 1px solid #cbd5e1; background: #fff; color: #475569; cursor: pointer;
        }
        .dash-body.dark .ppm-btn-ghost {
            background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.12); color: rgba(200,218,255,0.85);
        }
        .ppm-btn-ghost:hover { border-color: #002a7a; color: #002a7a; }
        .dash-body.dark .ppm-btn-ghost:hover { border-color: #D4AF37; color: #D4AF37; }
        .ppm-btn-danger { color: #b91c1c !important; border-color: #fecaca !important; }
        .dash-body.dark .ppm-btn-danger { color: #fca5a5 !important; border-color: rgba(248,113,113,0.35) !important; }

        .ppm-modal { position: fixed; inset: 0; z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 16px; }
        .ppm-modal[hidden] { display: none !important; }
        .ppm-modal-backdrop { position: absolute; inset: 0; background: rgba(15, 23, 42, 0.55); }
        .ppm-modal-box {
            position: relative; z-index: 1; width: 100%; max-width: 440px; max-height: 90vh; overflow-y: auto;
            margin: 0; padding: 20px !important;
        }
        .ppm-modal-box h3 { margin: 0 0 14px; font-size: 1rem; color: #002a7a; }
        .dash-body.dark .ppm-modal-box h3 { color: rgba(200, 218, 255, 0.92); }
        .ppm-field { margin-bottom: 12px; }
        .ppm-field label { display: block; font-size: 0.78rem; font-weight: 600; margin-bottom: 5px; color: #64748b; }
        .dash-body.dark .ppm-field label { color: rgba(200, 218, 255, 0.55); }
        .ppm-field .admin-filter-input, .ppm-field textarea.admin-filter-input { width: 100%; box-sizing: border-box; }
        .ppm-modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }
        .ppm-pernyataan-no { font-weight: 700; text-align: center; white-space: nowrap; width: 72px; }
        .ppm-pernyataan-isi-cell {
            font-size: 0.82rem; line-height: 1.45; word-break: break-word; vertical-align: top;
            max-width: min(560px, 100%);
            white-space: pre-wrap;
        }
        .ppm-pernyataan-aksi { white-space: nowrap; width: 1%; vertical-align: middle; }

        .ppm-daftar-filters.portal-local-filters { align-items: stretch; }
        .ppm-daftar-filters .portal-search-full { flex: 1 1 200px; min-width: 0; }
        .ppm-daftar-filters .ppm-status-wrap { flex: 0 0 auto; }
        .ppm-daftar-filters .ppm-status-wrap select {
            min-width: 0; max-width: 200px; width: 100%; box-sizing: border-box;
        }
        /* Satu tombol clear saja: hilangkan “X” bawaan browser pada type=search (jika dipakai di tempat lain) */
        .ppm-daftar-filters .admin-search-input::-webkit-search-cancel-button,
        .ppm-daftar-filters .admin-search-input::-webkit-search-decoration {
            -webkit-appearance: none;
            appearance: none;
            display: none;
        }
        @media (max-width: 640px) {
            .ppm-daftar-filters.portal-local-filters {
                flex-direction: column;
                flex-wrap: nowrap;
                align-items: stretch;
                gap: 10px;
                padding: 10px 12px;
            }
            .ppm-daftar-filters .portal-search-full {
                flex: 0 0 auto;
                width: 100%;
                max-width: 100%;
                min-width: 0;
            }
            .ppm-daftar-filters .ppm-status-wrap {
                flex: 0 0 auto;
                width: 100%;
                max-width: none;
            }
            .ppm-daftar-filters .ppm-status-wrap select {
                width: 100%;
                max-width: none;
                padding: 10px 12px;
                font-size: 0.85rem;
            }
            .ppm-daftar-filters .ppm-filter-reset {
                flex: 0 0 auto;
                width: 100%;
                justify-content: center;
                text-align: center;
                padding: 10px 14px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body class="dash-body">

    <div class="dash-bg-cubes" aria-hidden="true"></div>
    <div class="dash-bg-stardust" aria-hidden="true"></div>
    <div class="dash-bg-orb-gold" aria-hidden="true"></div>
    <div class="dash-bg-orb-blue" aria-hidden="true"></div>
    <div class="dash-bg-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 400" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%" preserveAspectRatio="none">
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50 L 1440 400 L 0 400 Z" fill="url(#pp_fill)"></path>
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50" stroke="url(#pp_stroke)" stroke-width="3" stroke-linecap="round"></path>
            <defs>
                <linearGradient id="pp_fill" x1="720" y1="50" x2="720" y2="400" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#D4AF37" stop-opacity="0.12"></stop>
                    <stop offset="1" stop-color="#0A2342" stop-opacity="0"></stop>
                </linearGradient>
                <linearGradient id="pp_stroke" x1="0" y1="150" x2="1440" y2="150" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#0A2342"></stop>
                    <stop offset="0.4" stop-color="#D4AF37"></stop>
                    <stop offset="1" stop-color="#60A5FA"></stop>
                </linearGradient>
            </defs>
        </svg>
    </div>

    @include('admin.partials.dash-admin-nav', ['pageTitle' => 'Peminjaman Kendaraan'])

    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">

            <div class="portal-stats-row">
                <div class="portal-stat-card" style="--accent:#002a7a">
                    <div class="portal-stat-icon" style="background:rgba(0,42,122,.1);color:#002a7a">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/></svg>
                    </div>
                    <div>
                        <div class="portal-stat-value">{{ $stats['total'] }}</div>
                        <div class="portal-stat-label">Total Permohonan</div>
                    </div>
                </div>
                <div class="portal-stat-card" style="--accent:#d97706">
                    <div class="portal-stat-icon" style="background:rgba(217,119,6,.1);color:#d97706">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <div class="portal-stat-value" style="color:#b45309">{{ $stats['pending'] }}</div>
                        <div class="portal-stat-label">Menunggu</div>
                    </div>
                </div>
                <div class="portal-stat-card" style="--accent:#16a34a">
                    <div class="portal-stat-icon" style="background:rgba(22,163,74,.1);color:#16a34a">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div>
                        <div class="portal-stat-value" style="color:#15803d">{{ $stats['approved'] }}</div>
                        <div class="portal-stat-label">Disetujui</div>
                    </div>
                </div>
                <div class="portal-stat-card" style="--accent:#dc2626">
                    <div class="portal-stat-icon" style="background:rgba(220,38,38,.1);color:#dc2626">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <div class="portal-stat-value" style="color:#b91c1c">{{ $stats['rejected'] }}</div>
                        <div class="portal-stat-label">Ditolak</div>
                    </div>
                </div>
            </div>

            <div class="mgmt-tab-bar" style="margin-top: 4px">
                <button type="button" class="mgmt-tab" id="ppm-tab-bidang" onclick="ppmSwitchTab('bidang')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M4 12h10M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span>Bidang / Bagian</span>
                    <span class="mgmt-tab-count" id="tc-bidang">{{ $tabCounts['bidangs'] }}</span>
                </button>
                <button type="button" class="mgmt-tab" id="ppm-tab-pernyataan" onclick="ppmSwitchTab('pernyataan')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 2v6h6M8 13h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span>Pernyataan</span>
                    <span class="mgmt-tab-count" id="tc-pernyataan">{{ $tabCounts['pernyataans'] }}</span>
                </button>
                <button type="button" class="mgmt-tab active" id="ppm-tab-daftar" onclick="ppmSwitchTab('daftar')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/></svg>
                    <span>Daftar permohonan</span>
                    <span class="mgmt-tab-count" id="tc-permohonan">{{ $tabCounts['permohonan'] }}</span>
                </button>
            </div>

            {{-- A. Master Bidang / Bagian --}}
            <div id="ppm-section-bidang" class="ppm-tab-panel" style="display: none">
                <div class="portal-section" id="ppm-master-bidang" style="margin-top: 14px">
                    <div class="portal-section-header" style="margin-bottom: 8px">
                        <div class="portal-section-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M4 12h10M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Bidang / Bagian
                        </div>
                    </div>
                    <p class="peminj-meta" style="margin:0 0 12px">Struktur induk → sub (dipakai di dropdown form permohonan).</p>
                    <div class="ppm-master-actions">
                        <button type="button" class="admin-filter-btn" id="ppm-btn-bidang-root">+ Bidang utama</button>
                    </div>
                    <div id="ppm-bidang-tree" class="ppm-tree" aria-live="polite"></div>
                </div>
            </div>

            {{-- B. Pernyataan --}}
            <div id="ppm-section-pernyataan" class="ppm-tab-panel" style="display: none">
                <div class="portal-section" id="ppm-master-pernyataan" style="margin-top: 14px">
                    <div class="portal-section-header" style="margin-bottom: 8px">
                        <div class="portal-section-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 2v6h6M8 13h8M8 17h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Pernyataan peminjaman
                        </div>
                    </div>
                    <div class="ppm-master-actions">
                        <button type="button" class="admin-filter-btn" id="ppm-btn-pernyataan-add">+ Tambah pernyataan</button>
                    </div>
                    <div class="admin-table-wrap" style="margin-top: 8px">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Isi Pernyataan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="ppm-pernyataan-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Daftar permohonan --}}
            <div id="ppm-section-daftar" class="ppm-tab-panel" style="display: block">
                <div class="portal-section" style="margin-top: 14px">
                    <div class="portal-section-header" style="margin-bottom: 0">
                        <div class="portal-section-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/></svg>
                            Daftar permohonan peminjaman
                        </div>
                    </div>

                    <div class="portal-local-filters ppm-daftar-filters" style="margin-top: 16px">
                        <div class="admin-search-wrap portal-search-full">
                            <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                                <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <input type="text" id="ppm-search-live" autocomplete="off"
                                inputmode="search" enterkeyhint="search"
                                value="{{ request('search') }}"
                                placeholder="Cari nama, NIP, jabatan, bidang, kendaraan…"
                                class="admin-search-input">
                            <button type="button" id="ppm-search-clear" class="admin-search-clear" title="Hapus pencarian" style="display: {{ request('search') ? 'flex' : 'none' }}">&times;</button>
                        </div>
                        <div class="ppm-status-wrap">
                            <select id="ppm-status-live" class="admin-filter-input" aria-label="Filter status permohonan">
                                <option value="">Semua status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        <button type="button" class="portal-local-reset ppm-filter-reset" id="ppm-filter-reset" title="Reset filter" style="display: none">Reset</button>
                    </div>

                    <div class="admin-table-wrap" style="margin-top: 8px">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Pemohon</th>
                                    <th>Bidang</th>
                                    <th>Kendaraan</th>
                                    <th>Keperluan</th>
                                    <th>Status</th>
                                    <th>Catatan</th>
                                    <th>Diajukan</th>
                                    <th>Diproses</th>
                                    <th>PDF</th>
                                </tr>
                            </thead>
                            <tbody id="ppm-requests-tbody">
                                @include('admin.partials.peminjaman-request-rows')
                            </tbody>
                        </table>
                    </div>
                    <div class="admin-pagination mt-4" id="ppm-requests-pagination">{{ $requests->links() }}</div>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal Bidang --}}
    <div id="ppm-modal-bidang" class="ppm-modal" hidden>
        <div class="ppm-modal-backdrop" data-close="bidang"></div>
        <div class="ppm-modal-box portal-section">
            <h3 id="ppm-modal-bidang-title">Bidang / Bagian</h3>
            <form id="ppm-form-bidang">
                <input type="hidden" id="ppm-bidang-id" value="">
                <div class="ppm-field">
                    <label for="ppm-bidang-nama">Nama</label>
                    <input type="text" id="ppm-bidang-nama" class="admin-filter-input" required maxlength="200">
                </div>
                <div class="ppm-field" id="ppm-bidang-parent-wrap">
                    <label for="ppm-bidang-parent">Induk (kosongkan untuk bidang utama)</label>
                    <select id="ppm-bidang-parent" class="admin-filter-input">
                        <option value="">— Bidang utama —</option>
                    </select>
                </div>
                <div class="ppm-modal-actions">
                    <button type="button" class="portal-local-reset" id="ppm-bidang-cancel" data-close="bidang">Batal</button>
                    <button type="submit" class="admin-filter-btn">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Pernyataan --}}
    <div id="ppm-modal-pernyataan" class="ppm-modal" hidden>
        <div class="ppm-modal-backdrop" data-close="pernyataan"></div>
        <div class="ppm-modal-box portal-section">
            <h3 id="ppm-modal-pernyataan-title">Pernyataan</h3>
            <form id="ppm-form-pernyataan">
                <input type="hidden" id="ppm-pernyataan-id" value="">
                <div class="ppm-field">
                    <label for="ppm-pernyataan-isi">Isi pernyataan</label>
                    <textarea id="ppm-pernyataan-isi" class="admin-filter-input" rows="4" required maxlength="5000"></textarea>
                </div>
                <div class="ppm-modal-actions">
                    <button type="button" class="portal-local-reset" id="ppm-pernyataan-cancel" data-close="pernyataan">Batal</button>
                    <button type="submit" class="admin-filter-btn">Simpan</button>
                </div>
            </form>
        </div>
    </div>

<script>
window.PPM_API = {
    csrf: @json(csrf_token()),
    bidangs: @json(url('/admin/bidangs')),
    pernyataans: @json(url('/admin/pernyataans')),
};
window.PPM_LIST_URL = @json(route('admin.peminjaman'));

window.ppmSwitchTab = function (tab) {
    const tabs = ['bidang', 'pernyataan', 'daftar'];
    if (!tabs.includes(tab)) tab = 'daftar';
    tabs.forEach(t => {
        const sec = document.getElementById('ppm-section-' + t);
        const btn = document.getElementById('ppm-tab-' + t);
        if (sec) sec.style.display = t === tab ? 'block' : 'none';
        if (btn) btn.classList.toggle('active', t === tab);
    });
    try {
        const url = new URL(location.href);
        url.hash = tab;
        history.replaceState(null, '', url.pathname + url.search + '#' + tab);
    } catch (e) { /* ignore */ }
    try { localStorage.setItem('ppm-active-tab', tab); } catch (e) { /* ignore */ }
};

(function () {
    let initialTab = 'daftar';
    const h = (location.hash || '').replace(/^#/, '');
    if (['bidang', 'pernyataan', 'daftar'].includes(h)) initialTab = h;
    else {
        try {
            const s = localStorage.getItem('ppm-active-tab');
            if (['bidang', 'pernyataan', 'daftar'].includes(s)) initialTab = s;
        } catch (e) { /* ignore */ }
    }
    window.ppmSwitchTab(initialTab);
})();

(function () {
    const headers = () => ({
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': PPM_API.csrf,
        'X-Requested-With': 'XMLHttpRequest',
    });

    function showErrors(res, data) {
        if (data.errors) {
            const msg = Object.values(data.errors).flat().join('<br>');
            Swal.fire({ icon: 'warning', title: 'Validasi', html: msg, confirmButtonColor: '#002a7a' });
            return;
        }
        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || ('HTTP ' + res.status), confirmButtonColor: '#002a7a' });
    }

    let bidangTree = [];

    function renderBidangTree(data) {
        bidangTree = data;
        const el = document.getElementById('ppm-bidang-tree');
        if (!data.length) {
            el.innerHTML = '<p class="peminj-meta">Belum ada data bidang.</p>';
            const tc = document.getElementById('tc-bidang');
            if (tc) tc.textContent = '0';
            return;
        }
        el.innerHTML = '<ul>' + data.map(renderRoot).join('') + '</ul>';
    }

    function renderRoot(node) {
        const actions = `
            <div class="ppm-tree-actions">
                <button type="button" class="ppm-btn-ghost" data-act="edit-bidang" data-id="${node.id}">Edit</button>
                <button type="button" class="ppm-btn-ghost" data-act="add-sub" data-parent="${node.id}">+ Sub</button>
                <button type="button" class="ppm-btn-ghost ppm-btn-danger" data-act="del-bidang" data-id="${node.id}">Hapus</button>
            </div>`;
        const subs = (node.children && node.children.length)
            ? '<ul>' + node.children.map(ch => renderChild(ch)).join('') + '</ul>'
            : '';
        return `<li>
            <div class="ppm-tree-row">
                <strong>${escapeHtml(node.nama)}</strong>
                ${actions}
            </div>${subs}
        </li>`;
    }

    function renderChild(node) {
        const actions = `
            <div class="ppm-tree-actions">
                <button type="button" class="ppm-btn-ghost" data-act="edit-bidang" data-id="${node.id}">Edit</button>
                <button type="button" class="ppm-btn-ghost ppm-btn-danger" data-act="del-bidang" data-id="${node.id}">Hapus</button>
            </div>`;
        return `<li>
            <div class="ppm-tree-row">
                <span>${escapeHtml(node.nama)}</span>
                ${actions}
            </div>
        </li>`;
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function escapeAttr(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;');
    }

    async function loadBidangs() {
        const res = await fetch(PPM_API.bidangs, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (!res.ok) { showErrors(res, data); return; }
        renderBidangTree(data.data || []);
        const tc = document.getElementById('tc-bidang');
        if (tc) tc.textContent = String(flattenBidang(data.data || []).length);
    }

    function populateBidangParents() {
        const sel = document.getElementById('ppm-bidang-parent');
        sel.innerHTML = '<option value="">— Bidang utama —</option>';
        bidangTree.forEach(r => {
            const o = document.createElement('option');
            o.value = String(r.id);
            o.textContent = r.nama;
            sel.appendChild(o);
        });
    }

    function openBidangModal(opts) {
        const { id, nama, parent_id, lockParent } = opts;
        document.getElementById('ppm-modal-bidang-title').textContent = id ? 'Ubah bidang' : (lockParent ? 'Tambah sub-bidang' : 'Tambah bidang utama');
        document.getElementById('ppm-bidang-id').value = id || '';
        document.getElementById('ppm-bidang-nama').value = nama || '';
        populateBidangParents();
        const sel = document.getElementById('ppm-bidang-parent');
        if (lockParent) {
            sel.value = String(lockParent);
            sel.disabled = true;
        } else {
            sel.disabled = false;
            sel.value = (parent_id != null && parent_id !== '') ? String(parent_id) : '';
        }
        document.getElementById('ppm-modal-bidang').hidden = false;
    }

    function closeBidangModal() {
        document.getElementById('ppm-modal-bidang').hidden = true;
        document.getElementById('ppm-bidang-parent').disabled = false;
    }

    document.getElementById('ppm-btn-bidang-root').addEventListener('click', () => openBidangModal({}));

    document.getElementById('ppm-bidang-tree').addEventListener('click', e => {
        const btn = e.target.closest('[data-act]');
        if (!btn) return;
        const act = btn.getAttribute('data-act');
        const id = btn.getAttribute('data-id');
        const parent = btn.getAttribute('data-parent');
        if (act === 'add-sub') {
            openBidangModal({ lockParent: parent, parent_id: parent });
            return;
        }
        if (act === 'edit-bidang') {
            const flat = flattenBidang(bidangTree);
            const node = flat.find(x => String(x.id) === String(id));
            if (!node) return;
            openBidangModal({
                id: node.id,
                nama: node.nama,
                parent_id: node.parent_id,
            });
            return;
        }
        if (act === 'del-bidang') {
            Swal.fire({
                title: 'Hapus bidang?',
                text: 'Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
            }).then(async r => {
                if (!r.isConfirmed) return;
                const res = await fetch(PPM_API.bidangs + '/' + id, { method: 'DELETE', headers: headers() });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.success) { showErrors(res, data); return; }
                Swal.fire({ icon: 'success', title: 'Terhapus', timer: 1400, showConfirmButton: false });
                loadBidangs();
            });
        }
    });

    function flattenBidang(nodes, acc = []) {
        nodes.forEach(n => {
            acc.push({ id: n.id, nama: n.nama, parent_id: n.parent_id ?? null });
            if (n.children && n.children.length) flattenBidang(n.children, acc);
        });
        return acc;
    }

    document.getElementById('ppm-form-bidang').addEventListener('submit', async e => {
        e.preventDefault();
        const id = document.getElementById('ppm-bidang-id').value;
        const payload = {
            nama: document.getElementById('ppm-bidang-nama').value.trim(),
        };
        const psel = document.getElementById('ppm-bidang-parent');
        const pv = psel.value;
        payload.parent_id = pv === '' ? null : parseInt(pv, 10);

        const url = id ? (PPM_API.bidangs + '/' + id) : PPM_API.bidangs;
        const method = id ? 'PUT' : 'POST';
        const res = await fetch(url, { method, headers: headers(), body: JSON.stringify(payload) });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) { showErrors(res, data); return; }
        closeBidangModal();
        Swal.fire({ icon: 'success', title: 'Disimpan', timer: 1200, showConfirmButton: false });
        loadBidangs();
    });

    document.querySelectorAll('[data-close="bidang"]').forEach(el => el.addEventListener('click', closeBidangModal));

    /* --- Pernyataan (tabel + modal seperti Bidang, AJAX) --- */
    let pernyataanRowsCache = [];

    function openPernyataanModal(opts = {}) {
        const id = opts.id != null && opts.id !== '' ? String(opts.id) : '';
        const isi = opts.isi_pernyataan != null ? opts.isi_pernyataan : '';
        document.getElementById('ppm-modal-pernyataan-title').textContent = id ? 'Ubah pernyataan' : 'Tambah pernyataan';
        document.getElementById('ppm-pernyataan-id').value = id;
        document.getElementById('ppm-pernyataan-isi').value = isi;
        document.getElementById('ppm-modal-pernyataan').hidden = false;
    }

    function closePernyataanModal() {
        document.getElementById('ppm-modal-pernyataan').hidden = true;
    }

    async function loadPernyataans() {
        const res = await fetch(PPM_API.pernyataans, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (!res.ok) { showErrors(res, data); return; }
        const rows = data.data || [];
        pernyataanRowsCache = rows;
        const tb = document.getElementById('ppm-pernyataan-tbody');
        const tcP = document.getElementById('tc-pernyataan');
        if (!rows.length) {
            tb.innerHTML = '<tr><td colspan="3" class="peminj-empty">Belum ada pernyataan.</td></tr>';
            if (tcP) tcP.textContent = '0';
            return;
        }
        if (tcP) tcP.textContent = String(rows.length);
        tb.innerHTML = rows.map((p, i) => {
            const isiEsc = escapeHtml(p.isi_pernyataan || '');
            const titleAttr = escapeAttr(p.isi_pernyataan || '');
            return `<tr data-id="${p.id}">
                <td class="ppm-pernyataan-no">${i + 1}</td>
                <td class="ppm-pernyataan-isi-cell" title="${titleAttr}">${isiEsc}</td>
                <td class="ppm-pernyataan-aksi">
                    <button type="button" class="ppm-btn-ghost ppm-edit-p" data-id="${p.id}">Edit</button>
                    <button type="button" class="ppm-btn-ghost ppm-btn-danger ppm-del-p" data-id="${p.id}">Hapus</button>
                </td>
            </tr>`;
        }).join('');
    }

    document.getElementById('ppm-btn-pernyataan-add').addEventListener('click', () => {
        openPernyataanModal({});
    });

    document.querySelectorAll('[data-close="pernyataan"]').forEach(el => el.addEventListener('click', closePernyataanModal));

    document.getElementById('ppm-form-pernyataan').addEventListener('submit', async e => {
        e.preventDefault();
        const id = document.getElementById('ppm-pernyataan-id').value;
        const payload = {
            isi_pernyataan: document.getElementById('ppm-pernyataan-isi').value.trim(),
        };
        const url = id ? (PPM_API.pernyataans + '/' + id) : PPM_API.pernyataans;
        const method = id ? 'PUT' : 'POST';
        const res = await fetch(url, { method, headers: headers(), body: JSON.stringify(payload) });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) { showErrors(res, data); return; }
        closePernyataanModal();
        Swal.fire({ icon: 'success', title: id ? 'Diperbarui' : 'Disimpan', timer: 1200, showConfirmButton: false });
        loadPernyataans();
    });

    document.getElementById('ppm-pernyataan-tbody').addEventListener('click', e => {
        const edit = e.target.closest('.ppm-edit-p');
        if (edit) {
            const id = parseInt(edit.getAttribute('data-id'), 10);
            const p = pernyataanRowsCache.find(x => Number(x.id) === id);
            if (!p) return;
            openPernyataanModal({
                id: p.id,
                isi_pernyataan: p.isi_pernyataan,
            });
            return;
        }
        const del = e.target.closest('.ppm-del-p');
        if (!del) return;
        const id = del.getAttribute('data-id');
        Swal.fire({
            title: 'Hapus pernyataan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#b91c1c',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
        }).then(async r => {
            if (!r.isConfirmed) return;
            const res = await fetch(PPM_API.pernyataans + '/' + id, { method: 'DELETE', headers: headers() });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.success) { showErrors(res, data); return; }
            Swal.fire({ icon: 'success', title: 'Terhapus', timer: 1200, showConfirmButton: false });
            loadPernyataans();
        });
    });

    loadBidangs();
    loadPernyataans();
})();

/* ── Daftar permohonan: filter & halaman real-time ── */
(function () {
    const listUrl = window.PPM_LIST_URL;
    const searchEl = document.getElementById('ppm-search-live');
    const statusEl = document.getElementById('ppm-status-live');
    const tbody = document.getElementById('ppm-requests-tbody');
    const pagEl = document.getElementById('ppm-requests-pagination');
    const clearBtn = document.getElementById('ppm-search-clear');
    const resetBtn = document.getElementById('ppm-filter-reset');
    if (!searchEl || !statusEl || !tbody || !pagEl) return;

    function updateFilterChrome() {
        const hasSearch = searchEl.value.trim().length > 0;
        if (clearBtn) clearBtn.style.display = hasSearch ? 'flex' : 'none';
        const showReset = hasSearch || (statusEl.value && statusEl.value !== '');
        if (resetBtn) resetBtn.style.display = showReset ? '' : 'none';
    }

    async function fetchRequestsFromUrl(url) {
        const u = url instanceof URL ? url : new URL(url, location.origin);
        const res = await fetch(u.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            cache: 'no-store',
        });
        let data = {};
        try { data = await res.json(); } catch (e) { /* ignore */ }
        if (!res.ok) {
            Swal.fire({ icon: 'error', title: 'Gagal memuat data', text: data.message || ('HTTP ' + res.status), confirmButtonColor: '#002a7a' });
            return;
        }
        tbody.innerHTML = data.tbody || '';
        pagEl.innerHTML = data.pagination || '';
        searchEl.value = u.searchParams.get('search') || '';
        statusEl.value = u.searchParams.get('status') || '';
        try {
            const keepHash = location.hash || '#daftar';
            history.replaceState(null, '', u.pathname + u.search + keepHash);
        } catch (e) { /* ignore */ }
        updateFilterChrome();
    }

    function buildListUrl(overrides = {}) {
        const u = new URL(listUrl, location.origin);
        const search = overrides.search !== undefined ? overrides.search : searchEl.value.trim();
        const status = overrides.status !== undefined ? overrides.status : statusEl.value;
        if (search) u.searchParams.set('search', search); else u.searchParams.delete('search');
        if (status) u.searchParams.set('status', status); else u.searchParams.delete('status');
        const page = overrides.page;
        if (page) u.searchParams.set('page', String(page)); else u.searchParams.delete('page');
        return u;
    }

    let debounceT;
    searchEl.addEventListener('input', () => {
        updateFilterChrome();
        clearTimeout(debounceT);
        debounceT = setTimeout(() => {
            fetchRequestsFromUrl(buildListUrl({ page: null }));
        }, 320);
    });

    statusEl.addEventListener('change', () => {
        fetchRequestsFromUrl(buildListUrl({ page: null }));
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            searchEl.value = '';
            fetchRequestsFromUrl(buildListUrl({ search: '', page: null }));
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            searchEl.value = '';
            statusEl.value = '';
            fetchRequestsFromUrl(new URL(listUrl, location.origin));
        });
    }

    pagEl.addEventListener('click', e => {
        const a = e.target.closest('a[href]');
        if (!a) return;
        const u = new URL(a.getAttribute('href'), location.origin);
        if (u.pathname !== new URL(listUrl, location.origin).pathname) return;
        e.preventDefault();
        fetchRequestsFromUrl(u);
    });

    updateFilterChrome();
})();

/* ── Theme Toggle ── */
(function () {
    const body  = document.body;
    const icon  = document.getElementById('dash-theme-icon');
    const btn   = document.getElementById('dash-theme-toggle');
    const label = document.getElementById('dash-theme-label');
    function applyTheme(isDark) {
        body.classList.toggle('dark', isDark);
        if (icon)  icon.className    = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        if (label) label.textContent = isDark ? 'Light Mode' : 'Dark Mode';
    }
    const saved = localStorage.getItem('vms-theme') || localStorage.getItem('vms-dash-theme');
    applyTheme(saved === 'dark');
    if (btn) btn.addEventListener('click', function () {
        const next = !body.classList.contains('dark');
        applyTheme(next);
        localStorage.setItem('vms-theme', next ? 'dark' : 'light');
        localStorage.setItem('vms-dash-theme', next ? 'dark' : 'light');
    });
})();
/* ── Mobile hamburger ── */
(function () {
    const menuBtn    = document.getElementById('dash-mobile-menu-btn');
    const navActions = document.getElementById('dash-nav-actions');
    const menuIcon   = document.getElementById('dash-mobile-menu-icon');
    if (!menuBtn || !navActions) return;
    function closeMenu() {
        navActions.classList.remove('mobile-open');
        menuIcon.className = 'bi bi-list';
        menuBtn.setAttribute('aria-expanded', 'false');
    }
    menuBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = navActions.classList.toggle('mobile-open');
        menuIcon.className = isOpen ? 'bi bi-x-lg' : 'bi bi-list';
        menuBtn.setAttribute('aria-expanded', String(isOpen));
    });
    document.addEventListener('click', function (e) {
        if (!navActions.contains(e.target) && !menuBtn.contains(e.target)) closeMenu();
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeMenu(); });
})();
</script>
</body>
</html>
