@extends('layouts.dash-app')

@section('title', 'Peminjaman Kendaraan')
@section('pageTitle', 'Peminjaman Kendaraan')
@section('pageSubtitle', 'Daftar permohonan & lihat PDF')

@php $premiumBgId = 'admin_peminjaman'; @endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
@endpush

@push('styles')
<style>
        .peminj-name { font-weight: 700; }
        .peminj-meta { font-size: 0.76rem; opacity: 0.85; }
        .peminj-meta-sm { font-size: 0.72rem; opacity: 0.8; }
        .peminj-bidang-nama { font-weight: 600; }
        /* Status badge — dark mode: latar lebih transparan, warna lebih tipis */
        html.dark .dash-body .ppm-requests-table .status-pending {
            background: rgba(234, 179, 8, 0.1);
            color: rgba(255, 191, 0, 0.88);
            backdrop-filter: blur(5px) saturate(180%);
        }
        html.dark .dash-body .ppm-requests-table .status-approved {
            background: rgba(34, 197, 94, 0.1);
            border-color: rgba(34, 197, 94, 0.22);
            backdrop-filter: blur(5px) saturate(180%);
            filter: brightness(1.5);
        }
        html.dark .dash-body .ppm-requests-table .status-rejected {
            background: rgba(248, 113, 113, 0.1);
            border-color: rgba(248, 113, 113, 0.22);
            backdrop-filter: blur(5px) saturate(180%);
        }
        html.dark .dash-body .ppm-requests-table .status-expired {
            background: rgba(148, 163, 184, 0.08);
            border-color: rgba(148, 163, 184, 0.18);
            backdrop-filter: blur(5px) saturate(180%);
        }
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
            backdrop-filter: blur(5px);
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
                0 0 0 2px rgba(212, 175, 55, 0.25),
                0 0 22px 4px rgba(212, 175, 55, 0.14);
        }
        html.dark .dash-body .portal-stat-card[data-filter-key].is-active .portal-stat-icon {
            color: #d4af37 !important;
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

            <div id="ppm-stat-filter-group">
                <div class="portal-stats-row" data-stat-count="3">
                    <x-admin-stat-card
                        title="Pending"
                        :value="$stats['pending']"
                        unit="Permohonan"
                        icon="bi bi-hourglass-split"
                        valueStyle="color:#ffbf00"
                        filterKey="status"
                        filterValue="pending"
                    />
                    <x-admin-stat-card
                        title="Approved"
                        :value="$stats['approved']"
                        unit="Permohonan"
                        icon="bi bi-check-circle-fill"
                        valueStyle="color:#15803d"
                        filterKey="status"
                        filterValue="approved"
                    />
                    <x-admin-stat-card
                        title="Rejected"
                        :value="$stats['rejected']"
                        unit="Permohonan"
                        icon="bi bi-x-circle-fill"
                        valueStyle="color:#b91c1c"
                        filterKey="status"
                        filterValue="rejected"
                    />
                </div>
                <div class="portal-stats-row" data-stat-count="2">
                    <x-admin-stat-card
                        title="Total"
                        :value="$stats['total']"
                        unit="Permohonan"
                        description="Seluruh permohonan peminjaman kendaraan"
                        icon="bi bi-clipboard-data-fill"
                        filterKey="status"
                        filterValue=""
                    />
                    <x-admin-stat-card
                        title="Expired"
                        :value="$stats['expired']"
                        unit="Permohonan"
                        description="Melewati batas waktu berlaku"
                        icon="bi bi-clock-fill"
                        valueStyle="color:#6b7280"
                        filterKey="status"
                        filterValue="expired"
                    />
                </div>
            </div>

            <div class="mgmt-tab-bar" style="margin-top: 4px">
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

            {{-- B. Pernyataan --}}
            <div id="ppm-section-pernyataan" class="ppm-tab-panel" style="display: none">
                <div class="portal-section" id="ppm-master-pernyataan" style="margin-top: 14px">
                    <div class="portal-section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <div class="portal-section-title" style="margin-bottom: 0;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 2v6h6M8 13h8M8 17h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Pernyataan peminjaman
                        </div>
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
            <div id="ppm-section-daftar" class="ppm-tab-panel" data-ppm-daftar-live style="display: block">
                <div class="portal-section" style="margin-top: 14px">
                    <div class="portal-section-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
                        <div class="portal-section-title" style="margin-bottom: 0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard-fill" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M10 1.5a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5zm-5 0A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5v1A1.5 1.5 0 0 1 9.5 4h-3A1.5 1.5 0 0 1 5 2.5zm-2 0h1v1A2.5 2.5 0 0 0 6.5 5h3A2.5 2.5 0 0 0 12 2.5v-1h1a2 2 0 0 1 2 2V14a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V3.5a2 2 0 0 1 2-2"/>
                            </svg>
                            Daftar permohonan peminjaman
                        </div>
                        <div class="portal-local-filters ppm-daftar-filters" style="margin-top: 0; padding: 0; background: transparent; border: none; box-shadow: none; align-items: center; gap: 8px;">
                            <div class="admin-search-wrap portal-search-full" style="width: 380px; max-width: 100%;">
                                <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                                    <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <input type="text" id="ppm-search-live" autocomplete="off"
                                    inputmode="search" enterkeyhint="search"
                                    value="{{ request('search') }}"
                                    placeholder="Cari nama, NIP, jabatan, bidang, kendaraan…"
                                    class="admin-search-input">
                            </div>
                            <x-admin-per-page-select id="ppm-per-page" name="per_page" :selected="$requests->perPage()" />
                            <button type="button" class="btn btn-sm sppd-icon-btn admin-filter-reset" id="ppm-filter-reset" title="Reset filter" aria-label="Reset filter" style="display: none"><i class="bi bi-arrow-clockwise"></i></button>
                        </div>
                    </div>

                    <div id="ppm-loading" class="portal-loading" style="display:none; margin: 12px 0;">
                        <span class="portal-loading-dot"></span><span class="portal-loading-dot"></span><span class="portal-loading-dot"></span>
                    </div>

                    <div class="admin-table-wrap" style="margin-top: 8px">
                        <table class="admin-table ppm-requests-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <x-sortable-th key="nama_lengkap" label="Pemohon" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                    <th>Bidang</th>
                                    <x-sortable-th key="nomor_kendaraan" label="Kendaraan" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                    <th>Keperluan</th>
                                    <x-sortable-th key="status" label="Status" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                    <th>Catatan</th>
                                    <x-sortable-th key="created_at" label="Diajukan" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                    <x-sortable-th key="updated_at" label="Diproses" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                    <th>PDF</th>
                                </tr>
                            </thead>
                            <tbody id="ppm-requests-tbody">
                                @include('admin.partials.peminjaman-request-rows')
                            </tbody>
                        </table>
                    </div>
                    <div id="ppm-requests-pagination" class="tbl-pagination-mount">
                        <x-admin-pagination :paginator="$requests" />
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('modals')


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
@endsection

@push('scripts')
    <script>
    window.PPM_API = {
        csrf: @json(csrf_token()),
        pernyataans: @json(url('/admin/pernyataans')),
    };
    window.PPM_LIST_URL = @json(route('admin.peminjaman'));

    window.ppmSwitchTab = function (tab) {
        const tabs = ['pernyataan', 'daftar'];
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
        if (['pernyataan', 'daftar'].includes(h)) initialTab = h;
        else {
            try {
                const s = localStorage.getItem('ppm-active-tab');
                if (['pernyataan', 'daftar'].includes(s)) initialTab = s;
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
                Swal.fire({ icon: 'warning', title: 'Validasi', html: msg, customClass: { popup: 'swal-ppm-popup', title: 'swal-ppm-title' } });
                return;
            }
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || ('HTTP ' + res.status), customClass: { popup: 'swal-ppm-popup', title: 'swal-ppm-title' } });
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
            Swal.fire({ icon: 'success', title: id ? 'Diperbarui' : 'Disimpan', timer: 1200, showConfirmButton: false, customClass: { popup: 'swal-ppm-popup', title: 'swal-ppm-title', icon: 'swal-ppm-icon-success' } });
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
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                buttonsStyling: false,
                customClass: {
                    popup: 'swal-ppm-popup',
                    title: 'swal-ppm-title',
                    confirmButton: 'swal-ppm-confirm',
                    cancelButton: 'swal-ppm-cancel',
                },
            }).then(async r => {
                if (!r.isConfirmed) return;
                const res = await fetch(PPM_API.pernyataans + '/' + id, { method: 'DELETE', headers: headers() });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.success) { showErrors(res, data); return; }
                Swal.fire({ icon: 'success', title: 'Terhapus', timer: 1200, showConfirmButton: false, customClass: { popup: 'swal-ppm-popup', title: 'swal-ppm-title', icon: 'swal-ppm-icon-success' } });
                loadPernyataans();
            });
        });

        loadPernyataans();
    })();

    /* ── Daftar permohonan: filter & halaman real-time (AJAX, tanpa reload) ── */
    (function () {
        const API_URL = window.PPM_LIST_URL;
        let _page = 1;
        let _perPage = 10;
        let _sort = '';
        let _dir = '';
        let _abort = null;
        let _activeStatus = '';

        const searchEl = document.getElementById('ppm-search-live');
        const perPageEl = document.getElementById('ppm-per-page');
        const tbody = document.getElementById('ppm-requests-tbody');
        const pagEl = document.getElementById('ppm-requests-pagination');
        const clearBtn = document.getElementById('ppm-search-clear');
        const resetBtn = document.getElementById('ppm-filter-reset');
        const statGroup = document.getElementById('ppm-stat-filter-group');

        if (!searchEl || !perPageEl || !tbody || !pagEl) return;

        const filterCards = statGroup
            ? Array.from(statGroup.querySelectorAll('[data-filter-key]'))
            : [];

        // Initialize state from elements
        _page = 1;
        _perPage = parseInt(perPageEl.value, 10) || 10;

        function showLoading() { const el = document.getElementById('ppm-loading'); if (el) el.style.display = 'flex'; }
        function hideLoading() { const el = document.getElementById('ppm-loading'); if (el) el.style.display = 'none'; }

        function buildParams() {
            const obj = {
                search:   searchEl.value.trim(),
                status:   _activeStatus,
                per_page: _perPage,
                page:     _page,
            };
            if (_sort) { obj.sort = _sort; obj.dir = _dir; }
            return new URLSearchParams(
                Object.fromEntries(Object.entries(obj).filter(([, v]) => v !== '' && v != null))
            ).toString();
        }

        function syncCardActiveState() {
            filterCards.forEach(card => {
                const cardVal = card.dataset.filterValue ?? '';
                card.classList.toggle('is-active', _activeStatus !== '' && cardVal === _activeStatus);
            });
        }

        function updateFilterChrome() {
            const hasSearch = searchEl.value.trim().length > 0;
            if (clearBtn) clearBtn.style.display = hasSearch ? 'flex' : 'none';
            const showReset = hasSearch
                || _activeStatus !== ''
                || _perPage !== 10;
            if (resetBtn) resetBtn.style.display = showReset ? '' : 'none';
            syncCardActiveState();
        }

        async function fetchRequests(scroll = false) {
            _abort?.abort();
            _abort = new AbortController();
            showLoading();

            const q = buildParams();
            try {
                const res = await fetch(`${API_URL}?${q}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: _abort.signal
                });
                const json = await res.json();

                tbody.innerHTML = json.tbody || '';
                mountPagination(json.pagination_html);

                if (window.AdminTableSort) {
                    window.AdminTableSort.syncAria(tbody.closest('table'), json.sort ?? null, json.dir ?? null);
                }

                updateFilterChrome();

                if (scroll) {
                    const section = document.getElementById('ppm-master-pernyataan')?.closest('.portal-section') || tbody.closest('.portal-section');
                    if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } catch (e) {
                if (e.name !== 'AbortError') console.warn('Peminjaman fetchRequests error', e);
            } finally {
                hideLoading();
            }
        }

        function mountPagination(html) {
            // Wait for dependencies if necessary
            function _mount() {
                window.AdminPagination.mountPagination(pagEl, html || '');
                if (!pagEl.dataset.paginationBound) {
                    pagEl.dataset.paginationBound = '1';
                    window.AdminPagination.bindPaginationLinks(pagEl, (url) => {
                        _page = parseInt(url.searchParams.get('page') || '1', 10);
                        fetchRequests(true);
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

        function debounce(fn, ms = 380) {
            let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
        }

        const debouncedFetch = debounce(() => { _page = 1; fetchRequests(); });

        searchEl.addEventListener('input', () => {
            updateFilterChrome();
            debouncedFetch();
        });

        perPageEl.addEventListener('change', (e) => {
            _perPage = parseInt(e.target.value, 10);
            _page = 1;
            fetchRequests();
        });

        filterCards.forEach(card => {
            const activate = () => {
                const cardVal = card.dataset.filterValue ?? '';
                if (cardVal !== '' && _activeStatus === cardVal) {
                    _activeStatus = '';
                } else {
                    _activeStatus = cardVal;
                }
                updateFilterChrome();
                _page = 1;
                fetchRequests();
            };
            card.addEventListener('click', activate);
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); activate(); }
            });
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                searchEl.value = '';
                updateFilterChrome();
                _page = 1;
                fetchRequests();
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                searchEl.value = '';
                _activeStatus = '';
                perPageEl.value = '10';
                _perPage = 10;
                _page = 1;
                _sort = '';
                _dir = '';
                updateFilterChrome();
                fetchRequests();
            });
        }

        // Table sorting
        if (window.AdminTableSort) {
            const ppmRoot = document.querySelector('[data-ppm-daftar-live]');
            if (ppmRoot) {
                window.AdminTableSort.bindRoot(ppmRoot, {
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
                        fetchRequests();
                    },
                });
            }
        }

        // Initial setup
        updateFilterChrome();

        // Init pagination mount on load
        mountPagination(pagEl.innerHTML);

        if (typeof window.registerTurboCleanup === 'function') {
            window.registerTurboCleanup(function () {
                if (_abort) _abort.abort();
            });
        }
    })();

    </script>
@endpush
