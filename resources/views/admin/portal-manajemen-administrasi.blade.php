<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Manajemen Administrasi – {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="dash-body">

    {{-- Background decoration layers --}}
    <div class="dash-bg-cubes" aria-hidden="true"></div>
    <div class="dash-bg-stardust" aria-hidden="true"></div>
    <div class="dash-bg-orb-gold" aria-hidden="true"></div>
    <div class="dash-bg-orb-blue" aria-hidden="true"></div>
    <div class="dash-bg-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 400" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%" preserveAspectRatio="none">
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50 L 1440 400 L 0 400 Z" fill="url(#am_fill)"></path>
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50" stroke="url(#am_stroke)" stroke-width="3" stroke-linecap="round"></path>
            <defs>
                <linearGradient id="am_fill" x1="720" y1="50" x2="720" y2="400" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#D4AF37" stop-opacity="0.12"></stop>
                    <stop offset="1" stop-color="#0A2342" stop-opacity="0"></stop>
                </linearGradient>
                <linearGradient id="am_stroke" x1="0" y1="150" x2="1440" y2="150" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#0A2342"></stop>
                    <stop offset="0.4" stop-color="#D4AF37"></stop>
                    <stop offset="1" stop-color="#60A5FA"></stop>
                </linearGradient>
            </defs>
        </svg>
    </div>

    {{-- ══ NAVBAR ══ --}}
    <nav class="dash-nav" id="dash-nav">
        <div class="dash-nav-inner">
            <div class="dash-nav-brand">
                <img src="{{ asset('images/VMS.png') }}" alt="VMS" class="dash-nav-logo">
                <div>
                    <div class="dash-nav-title">Portal Manajemen Administrasi</div>
                    <span class="dash-nav-sub">PT ARTHA DAYA COALINDO</span>
                </div>
            </div>
            <div class="dash-nav-actions" id="dash-nav-actions">
                <button class="dash-theme-btn" id="dash-theme-toggle" title="Ganti Tema" aria-label="Toggle Tema">
                    <i class="bi bi-moon-fill" id="dash-theme-icon"></i>
                    <span class="dash-theme-mode-label" id="dash-theme-label">Dark Mode</span>
                </button>
                <span class="dash-chip dash-chip-admin">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
                    <span class="dash-nav-chip-label">ADMIN</span>
                </span>
                <a href="{{ route('dashboard') }}" class="dash-nav-btn-glass" aria-label="Dashboard">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="dash-nav-btn-label">Dashboard</span>
                </a>
            </div>
            <button class="dash-mobile-menu-btn" id="dash-mobile-menu-btn" aria-label="Buka Menu" aria-expanded="false">
                <i class="bi bi-list" id="dash-mobile-menu-icon"></i>
            </button>
        </div>
    </nav>

<div class="armada-shell" style="position:relative;z-index:1">

    {{-- ── STATS ROW ─────────────────────────────────────────────────────── --}}
    <div class="mgmt-stats-strip">
        <div class="mgmt-stat-card" style="--sc:#0f766e;--scbg:rgba(15,118,110,.08)">
            <div class="mgmt-stat-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M19 17H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2"/>
                    <path d="M7 17v2m10-2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="12" cy="12" r="1" fill="currentColor"/>
                </svg>
            </div>
            <div class="mgmt-stat-body">
                <span class="mgmt-stat-value">{{ $stats['total_kendaraan'] }}</span>
                <span class="mgmt-stat-label">Total Kendaraan</span>
            </div>
            <div class="mgmt-stat-accent"></div>
        </div>

        <div class="mgmt-stat-card" style="--sc:#2563eb;--scbg:rgba(37,99,235,.08)">
            <div class="mgmt-stat-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                </svg>
            </div>
            <div class="mgmt-stat-body">
                <span class="mgmt-stat-value">{{ $stats['total_driver'] }}</span>
                <span class="mgmt-stat-label">Total Driver</span>
            </div>
            <div class="mgmt-stat-accent"></div>
        </div>

        <div class="mgmt-stat-card" style="--sc:#7c3aed;--scbg:rgba(124,58,237,.08)">
            <div class="mgmt-stat-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="mgmt-stat-body">
                <span class="mgmt-stat-value">{{ $stats['total_pic'] }}</span>
                <span class="mgmt-stat-label">PIC Kendaraan</span>
            </div>
            <div class="mgmt-stat-accent"></div>
        </div>

        <div class="mgmt-stat-card" style="--sc:#d97706;--scbg:rgba(217,119,6,.08)">
            <div class="mgmt-stat-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                    <path d="M21 15l-3-3m0 0l-3 3m3-3v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="mgmt-stat-body">
                <span class="mgmt-stat-value">{{ $stats['total_driver'] + $stats['total_pic'] }}</span>
                <span class="mgmt-stat-label">Total User Aktif</span>
            </div>
            <div class="mgmt-stat-accent"></div>
        </div>
    </div>

    {{-- ── TAB BAR ───────────────────────────────────────────────────────── --}}
    <div class="mgmt-tab-bar">
        <button class="mgmt-tab active" id="tab-armada" onclick="switchTab('armada')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M19 17H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2"/>
                <path d="M7 17v2m10-2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>Master Armada</span>
            <span class="mgmt-tab-count" id="tc-armada">{{ $stats['total_kendaraan'] }}</span>
        </button>
        <button class="mgmt-tab" id="tab-users" onclick="switchTab('users')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>Manajemen User</span>
            <span class="mgmt-tab-count" id="tc-users">{{ $stats['total_driver'] + $stats['total_pic'] }}</span>
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- SECTION: MASTER ARMADA                                            --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div id="section-armada">
        <div class="mgmt-panel">

            {{-- Panel header --}}
            <div class="mgmt-panel-header" style="--ph:#0f766e">
                <div class="mgmt-ph-icon" style="background:rgba(15,118,110,.12);color:#0f766e">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M19 17H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2"/>
                        <path d="M7 17v2m10-2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="12" cy="12" r="1" fill="currentColor"/>
                    </svg>
                </div>
                <div class="mgmt-ph-text">
                    <p class="mgmt-ph-title">Master Armada</p>
                    <p class="mgmt-ph-sub">Kelola data kendaraan operasional</p>
                </div>
                <button class="mgmt-ph-add-btn" id="btn-toggle-armada-form" onclick="toggleAddForm('armada')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                    Tambah Kendaraan
                </button>
            </div>

            {{-- Collapsible add form --}}
            <div id="armada-add-panel" class="mgmt-add-panel">
                <div class="mgmt-add-inner">
                    <p class="mgmt-add-heading">Data Kendaraan Baru</p>
                    <form id="form-add-armada">
                        @csrf
                        <div class="mgmt-form-grid5">
                            <div class="mgmt-field">
                                <label class="mgmt-label">Nomor Kendaraan</label>
                                <input type="text" name="nomor_kendaraan" class="mgmt-input" placeholder="B 1234 ABC" required>
                            </div>
                            <div class="mgmt-field">
                                <label class="mgmt-label">Jenis Kendaraan</label>
                                <input type="text" name="jenis_kendaraan" class="mgmt-input" placeholder="MITSUBISHI XPANDER" required>
                            </div>
                            <div class="mgmt-field">
                                <label class="mgmt-label">Bidang</label>
                                <input type="text" name="bidang" class="mgmt-input" placeholder="Operasional">
                            </div>
                            <div class="mgmt-field">
                                <label class="mgmt-label">Set KM</label>
                                <input type="number" name="set_km" class="mgmt-input" placeholder="50000" min="0">
                            </div>
                            <div class="mgmt-field-action">
                                <button type="submit" class="mgmt-submit-btn" id="btn-add-armada">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    Simpan
                                </button>
                                <button type="button" class="mgmt-cancel-btn" onclick="toggleAddForm('armada')">Batal</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Filter bar --}}
            <div class="mgmt-filter-bar">
                <div class="mgmt-search-wrap">
                    <svg class="mgmt-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <input type="text" id="armada-search" class="mgmt-search-input" placeholder="Cari nomor atau jenis kendaraan…">
                </div>
                <div class="mgmt-perpage-wrap">
                    <span class="mgmt-perpage-label">Tampilkan</span>
                    <select id="armada-perpage" class="mgmt-perpage-select">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="mgmt-perpage-label">data</span>
                </div>
                <button type="button" class="mgmt-reset-btn" onclick="resetArmadaFilters()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M3 12a9 9 0 109-9 9 9 0 00-9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 3v5h5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Reset
                </button>
            </div>

            {{-- Loading --}}
            <div id="armada-loading" class="mgmt-loading" style="display:none">
                <span class="mgmt-dot"></span><span class="mgmt-dot"></span><span class="mgmt-dot"></span>
            </div>

            {{-- Table --}}
            <div class="mgmt-table-wrap">
                <table class="mgmt-table">
                    <thead>
                        <tr>
                            <th class="w-10">#</th>
                            <th>Nomor Kendaraan</th>
                            <th>Jenis Kendaraan</th>
                            <th>Bidang</th>
                            <th>Set KM</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="armada-tbody">
                        @forelse($kendaraans as $k)
                            <tr id="krow-{{ $k->id }}">
                                <td class="text-muted">{{ ($kendaraans->currentPage()-1)*$kendaraans->perPage()+$loop->iteration }}</td>
                                <td>
                                    <span class="view-mode mgmt-nopol">{{ $k->nomor_kendaraan }}</span>
                                    <input class="edit-mode mgmt-input" type="text" value="{{ $k->nomor_kendaraan }}" name="nomor_kendaraan" form="kedit-{{ $k->id }}" style="display:none">
                                </td>
                                <td>
                                    <span class="view-mode">{{ $k->jenis_kendaraan }}</span>
                                    <input class="edit-mode mgmt-input" type="text" value="{{ $k->jenis_kendaraan }}" name="jenis_kendaraan" form="kedit-{{ $k->id }}" style="display:none">
                                </td>
                                <td>
                                    <span class="view-mode text-muted">{{ $k->bidang ?: '—' }}</span>
                                    <input class="edit-mode mgmt-input" type="text" value="{{ $k->bidang }}" name="bidang" form="kedit-{{ $k->id }}" style="display:none" placeholder="Bidang...">
                                </td>
                                <td>
                                    <span class="view-mode">{{ number_format($k->set_km ?? 0,0,',','.') }} km</span>
                                    <input class="edit-mode mgmt-input" type="number" value="{{ $k->set_km ?? 0 }}" name="set_km" min="0" form="kedit-{{ $k->id }}" style="display:none;width:100px">
                                </td>
                                <td class="text-center">
                                    <form id="kedit-{{ $k->id }}" action="{{ route('admin.portal-manajemen.kendaraan.update', $k) }}" method="POST" style="display:none" onsubmit="event.preventDefault(); submitKendaraanEdit({{ $k->id }})">
                                        @csrf @method('PUT')
                                    </form>
                                    <div class="mgmt-actions view-mode">
                                        <button type="button" class="mgmt-act-btn mgmt-act-edit" onclick="toggleKEdit({{ $k->id }})" title="Edit">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            Edit
                                        </button>
                                        <form id="kdel-{{ $k->id }}" action="{{ route('admin.portal-manajemen.kendaraan.destroy', $k) }}" method="POST" style="display:inline" onsubmit="event.preventDefault(); deleteKendaraan({{ $k->id }}, '{{ addslashes($k->nomor_kendaraan) }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="mgmt-act-btn mgmt-act-del" title="Hapus">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                    <div class="mgmt-actions edit-mode" style="display:none">
                                        <button type="button" class="mgmt-act-btn mgmt-act-save" onclick="submitKendaraanEdit({{ $k->id }})">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            Simpan
                                        </button>
                                        <button type="button" class="mgmt-act-btn mgmt-act-cancel" onclick="toggleKEdit({{ $k->id }})">Batal</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="mgmt-empty">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="margin:0 auto 8px;display:block;opacity:.3"><path d="M19 17H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5"/></svg>
                                Belum ada data kendaraan.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mgmt-pagination" id="armada-pagination"></div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- SECTION: MANAJEMEN USER                                           --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div id="section-users" style="display:none">
        <div class="mgmt-panel">

            {{-- Panel header --}}
            <div class="mgmt-panel-header" style="--ph:#2563eb">
                <div class="mgmt-ph-icon" style="background:rgba(37,99,235,.12);color:#2563eb">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="mgmt-ph-text">
                    <p class="mgmt-ph-title">Manajemen User</p>
                    <p class="mgmt-ph-sub">Driver &amp; PIC Kendaraan</p>
                </div>
                <button class="mgmt-ph-add-btn" id="btn-toggle-user-form" onclick="toggleAddForm('user')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                    Tambah User
                </button>
            </div>

            {{-- Collapsible add form --}}
            <div id="user-add-panel" class="mgmt-add-panel">
                <div class="mgmt-add-inner">
                    <p class="mgmt-add-heading">Data User Baru</p>
                    <form id="form-add-user">
                        @csrf
                        <div class="mgmt-form-grid5">
                            <div class="mgmt-field">
                                <label class="mgmt-label">Nama Lengkap</label>
                                <input type="text" name="name" class="mgmt-input" placeholder="Nama Lengkap" required>
                            </div>
                            <div class="mgmt-field">
                                <label class="mgmt-label">Username</label>
                                <input type="text" name="username" class="mgmt-input" placeholder="username" required autocomplete="off">
                            </div>
                            <div class="mgmt-field">
                                <label class="mgmt-label">Role</label>
                                <select name="role" class="mgmt-input" required>
                                    <option value="driver">Driver</option>
                                    <option value="pic_kendaraan">PIC Kendaraan</option>
                                </select>
                            </div>
                            <div class="mgmt-field">
                                <label class="mgmt-label">Password</label>
                                <div class="mgmt-pw-wrap">
                                    <input type="password" name="password" id="add-user-pw" class="mgmt-input" value="{{ $defaultPassword }}" required autocomplete="new-password">
                                    <button type="button" class="mgmt-pw-eye" onclick="toggleEye('add-user-pw', this)" tabindex="-1">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                                    </button>
                                </div>
                                <p class="mgmt-hint">Default: <code>{{ $defaultPassword }}</code></p>
                            </div>
                            <div class="mgmt-field-action">
                                <button type="submit" class="mgmt-submit-btn" id="btn-add-user">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    Simpan
                                </button>
                                <button type="button" class="mgmt-cancel-btn" onclick="toggleAddForm('user')">Batal</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Filter bar --}}
            <div class="mgmt-filter-bar">
                <div class="mgmt-search-wrap" style="flex:2">
                    <svg class="mgmt-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <input type="text" id="user-search" class="mgmt-search-input" placeholder="Cari nama atau username…">
                </div>
                <div class="mgmt-select-wrap">
                    <svg class="mgmt-filter-select-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                    <select id="user-role-filter" class="mgmt-filter-select">
                        <option value="">Semua Role</option>
                        <option value="driver">Driver</option>
                        <option value="pic_kendaraan">PIC Kendaraan</option>
                    </select>
                </div>
                <div class="mgmt-perpage-wrap">
                    <span class="mgmt-perpage-label">Tampilkan</span>
                    <select id="user-perpage" class="mgmt-perpage-select">
                        <option value="5">5</option>
                        <option value="15" selected>15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="mgmt-perpage-label">data</span>
                </div>
                <button type="button" class="mgmt-reset-btn" onclick="resetUserFilters()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M3 12a9 9 0 109-9 9 9 0 00-9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 3v5h5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Reset
                </button>
            </div>

            {{-- Loading --}}
            <div id="user-loading" class="mgmt-loading" style="display:none">
                <span class="mgmt-dot"></span><span class="mgmt-dot"></span><span class="mgmt-dot"></span>
            </div>

            {{-- Table --}}
            <div class="mgmt-table-wrap">
                <table class="mgmt-table">
                    <thead>
                        <tr>
                            <th class="w-10">#</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="user-tbody">
                        @forelse($users as $u)
                            <tr id="urow-server-{{ $u->id }}">
                                <td class="text-muted">{{ ($users->currentPage()-1)*$users->perPage()+$loop->iteration }}</td>
                                <td>
                                    <div class="mgmt-user-cell">
                                        <div class="mgmt-user-avatar" style="{{ $u->role === 'pic_kendaraan' ? 'background:linear-gradient(135deg,#7c3aed,#a78bfa)' : 'background:linear-gradient(135deg,#2563eb,#60a5fa)' }}">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="mgmt-user-name">{{ $u->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="mgmt-username">{{ $u->username }}</span></td>
                                <td>
                                    @if($u->role === 'pic_kendaraan')
                                        <span class="mgmt-role-badge mgmt-role-pic">PIC Kendaraan</span>
                                    @else
                                        <span class="mgmt-role-badge mgmt-role-driver">Driver</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="mgmt-actions">
                                        <button type="button" class="mgmt-act-btn mgmt-act-edit" onclick="openUserEdit({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->username) }}', '{{ $u->role }}')">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            Edit
                                        </button>
                                        <button type="button" class="mgmt-act-btn mgmt-act-del" onclick="deleteUser({{ $u->id }}, '{{ addslashes($u->name) }}')">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="mgmt-empty">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="margin:0 auto 8px;display:block;opacity:.3"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.5"/></svg>
                                Belum ada data user.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mgmt-pagination" id="user-pagination"></div>
        </div>
    </div>

</div>{{-- /.armada-shell --}}

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- EDIT USER MODAL                                                        --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="user-edit-modal" class="mgmt-modal-overlay" style="display:none" onclick="if(event.target===this)closeUserModal()">
    <div class="mgmt-modal-box">
        <div class="mgmt-modal-header">
            <div class="mgmt-modal-avatar" id="modal-avatar">U</div>
            <div>
                <h2 class="mgmt-modal-title">Edit User</h2>
                <p class="mgmt-modal-sub" id="modal-sub-text">—</p>
            </div>
            <button type="button" class="mgmt-modal-close" onclick="closeUserModal()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
        </div>
        <form id="form-edit-user" onsubmit="event.preventDefault(); submitUserEdit()">
            <input type="hidden" id="edit-user-id">
            <div class="mgmt-modal-body">
                <p class="mgmt-modal-section-label">INFORMASI AKUN</p>
                <div class="mgmt-modal-grid">
                    <div class="mgmt-field">
                        <label class="mgmt-label">Nama Lengkap</label>
                        <div class="mgmt-input-icon-wrap">
                            <svg class="mgmt-input-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                            <input type="text" id="edit-name" class="mgmt-input has-icon" placeholder="Nama Lengkap" required>
                        </div>
                    </div>
                    <div class="mgmt-field">
                        <label class="mgmt-label">Username</label>
                        <div class="mgmt-input-icon-wrap">
                            <svg class="mgmt-input-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                            <input type="text" id="edit-username" class="mgmt-input has-icon" placeholder="username" required autocomplete="off">
                        </div>
                    </div>
                    <div class="mgmt-field">
                        <label class="mgmt-label">Role</label>
                        <select id="edit-role" class="mgmt-input" required>
                            <option value="driver">Driver</option>
                            <option value="pic_kendaraan">PIC Kendaraan</option>
                        </select>
                    </div>
                </div>

                <div class="mgmt-modal-divider"></div>
                <p class="mgmt-modal-section-label">RESET PASSWORD <span style="font-weight:400;text-transform:none;color:#94a3b8">(opsional)</span></p>
                <div class="mgmt-hint-box">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" style="flex-shrink:0"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Password default: <code>{{ $defaultPassword }}</code> — Kosongkan jika tidak ingin mengubah password.
                </div>
                <div class="mgmt-field" style="max-width:320px">
                    <label class="mgmt-label">Password Baru</label>
                    <div class="mgmt-pw-wrap">
                        <input type="password" id="edit-password" class="mgmt-input" placeholder="Kosongkan jika tidak diubah" autocomplete="new-password">
                        <button type="button" class="mgmt-pw-eye" onclick="toggleEye('edit-password', this)" tabindex="-1">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="mgmt-modal-footer">
                <button type="button" class="mgmt-cancel-btn" onclick="closeUserModal()">Batal</button>
                <button type="submit" class="mgmt-submit-btn" id="btn-save-edit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
'use strict';

const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const BASE = window.location.origin;

/* ─── Helpers ──────────────────────────────────────────────────────────── */
function escHtml(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function escJs(s)   { return String(s??'').replace(/\\/g,'\\\\').replace(/'/g,"\\'"); }
function numFmt(n)  { return Number(n).toLocaleString('id-ID'); }
function debounce(fn,ms){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a),ms); }; }

/* ─── Eye toggle ───────────────────────────────────────────────────────── */
window.toggleEye = function(inputId, btn) {
    const el = document.getElementById(inputId);
    if (!el) return;
    const isHidden = el.type === 'password';
    el.type = isHidden ? 'text' : 'password';
    btn.innerHTML = isHidden
        ? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'
        : '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>';
};

/* ─── Collapsible add forms ───────────────────────────────────────────── */
window.toggleAddForm = function(section) {
    const panel = document.getElementById(section + '-add-panel');
    const btn   = document.getElementById('btn-toggle-' + section + '-form');
    const open  = panel.classList.toggle('open');
    btn.classList.toggle('active', open);
};

/* ─── Pagination builder ───────────────────────────────────────────────── */
function buildPagination(wrap, meta, onPage) {
    if (!wrap) return;
    wrap.innerHTML = '';
    if (meta.last_page <= 1) return;
    const add = (label, page, disabled, active) => {
        const btn = document.createElement('button');
        btn.className = 'mgmt-pag-btn' + (active ? ' active' : '');
        btn.disabled  = disabled;
        btn.innerHTML = label;
        btn.addEventListener('click', () => { if (!disabled) onPage(page); });
        wrap.appendChild(btn);
    };
    add('‹', meta.current_page - 1, meta.current_page === 1, false);
    for (let p = 1; p <= meta.last_page; p++) {
        if (p === 1 || p === meta.last_page || Math.abs(p - meta.current_page) <= 2) {
            add(p, p, false, p === meta.current_page);
        } else if (Math.abs(p - meta.current_page) === 3) {
            const s = document.createElement('span');
            s.textContent = '…'; s.style.cssText = 'padding:0 6px;color:#94a3b8;align-self:center;font-size:0.85rem';
            wrap.appendChild(s);
        }
    }
    add('›', meta.current_page + 1, meta.current_page === meta.last_page, false);
}

/* ═══════════════════════════════════════════════════════════════════════ */
/* SECTION TABS                                                            */
/* ═══════════════════════════════════════════════════════════════════════ */
window.switchTab = function(tab) {
    document.getElementById('section-armada').style.display = tab === 'armada' ? '' : 'none';
    document.getElementById('section-users').style.display  = tab === 'users'  ? '' : 'none';
    document.getElementById('tab-armada').classList.toggle('active', tab === 'armada');
    document.getElementById('tab-users').classList.toggle('active',  tab === 'users');
    if (tab === 'users') fetchUsers();
};

/* ═══════════════════════════════════════════════════════════════════════ */
/* MASTER ARMADA                                                           */
/* ═══════════════════════════════════════════════════════════════════════ */
let armadaPage = 1, armadaPerPage = 10;

async function fetchArmada(scroll = false) {
    document.getElementById('armada-loading').style.display = 'flex';
    const params = new URLSearchParams({
        search:   document.getElementById('armada-search').value.trim(),
        per_page: armadaPerPage,
        page:     armadaPage,
    });
    try {
        const json = await fetch(`${BASE}/api/admin/portal/kendaraan?${params}`).then(r => r.json());
        renderArmadaTable(json.data, json.current_page, json.per_page);
        buildPagination(document.getElementById('armada-pagination'), json, p => { armadaPage = p; fetchArmada(true); });
        document.getElementById('tc-armada').textContent = json.total;
        if (scroll) document.getElementById('section-armada').scrollIntoView({behavior:'smooth', block:'start'});
    } catch(e) { console.error(e); }
    finally { document.getElementById('armada-loading').style.display = 'none'; }
}

function renderArmadaTable(rows, page, perPage) {
    const tbody = document.getElementById('armada-tbody');
    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="mgmt-empty">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="margin:0 auto 8px;display:block;opacity:.3"><path d="M19 17H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5"/></svg>
            Tidak ada data kendaraan.
        </td></tr>`;
        return;
    }
    const offset = (page - 1) * perPage;
    tbody.innerHTML = rows.map((k, i) => `
        <tr id="krow-${k.id}">
            <td class="text-muted">${offset + i + 1}</td>
            <td>
                <span class="view-mode mgmt-nopol">${escHtml(k.nomor_kendaraan)}</span>
                <input class="edit-mode mgmt-input" type="text" value="${escHtml(k.nomor_kendaraan)}" name="nomor_kendaraan" form="kedit-${k.id}" style="display:none">
            </td>
            <td>
                <span class="view-mode">${escHtml(k.jenis_kendaraan)}</span>
                <input class="edit-mode mgmt-input" type="text" value="${escHtml(k.jenis_kendaraan)}" name="jenis_kendaraan" form="kedit-${k.id}" style="display:none">
            </td>
            <td>
                <span class="view-mode text-muted">${k.bidang ? escHtml(k.bidang) : '—'}</span>
                <input class="edit-mode mgmt-input" type="text" value="${k.bidang ? escHtml(k.bidang) : ''}" name="bidang" form="kedit-${k.id}" style="display:none" placeholder="Bidang...">
            </td>
            <td>
                <span class="view-mode">${numFmt(k.set_km ?? 0)} km</span>
                <input class="edit-mode mgmt-input" type="number" value="${k.set_km ?? 0}" name="set_km" min="0" form="kedit-${k.id}" style="display:none;width:100px">
            </td>
            <td class="text-center">
                <form id="kedit-${k.id}" action="/admin/portal-manajemen-administrasi/kendaraan/${k.id}" method="POST" style="display:none" onsubmit="event.preventDefault();submitKendaraanEdit(${k.id})">
                    <input type="hidden" name="_token" value="${CSRF}"><input type="hidden" name="_method" value="PUT">
                </form>
                <div class="mgmt-actions view-mode">
                    <button type="button" class="mgmt-act-btn mgmt-act-edit" onclick="toggleKEdit(${k.id})">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Edit
                    </button>
                    <form id="kdel-${k.id}" action="/admin/portal-manajemen-administrasi/kendaraan/${k.id}" method="POST" style="display:inline" onsubmit="event.preventDefault();deleteKendaraan(${k.id},'${escJs(k.nomor_kendaraan)}')">
                        <input type="hidden" name="_token" value="${CSRF}"><input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="mgmt-act-btn mgmt-act-del">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Hapus
                        </button>
                    </form>
                </div>
                <div class="mgmt-actions edit-mode" style="display:none">
                    <button type="button" class="mgmt-act-btn mgmt-act-save" onclick="submitKendaraanEdit(${k.id})">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Simpan
                    </button>
                    <button type="button" class="mgmt-act-btn mgmt-act-cancel" onclick="toggleKEdit(${k.id})">Batal</button>
                </div>
            </td>
        </tr>`).join('');
}

window.toggleKEdit = function(id) {
    const row = document.getElementById('krow-' + id);
    if (!row) return;
    const inEdit = row.querySelector('.edit-mode')?.style.display !== 'none';
    row.querySelectorAll('.view-mode').forEach(el => el.style.display = inEdit ? '' : 'none');
    row.querySelectorAll('.edit-mode').forEach(el => el.style.display = inEdit ? 'none' : (el.tagName==='DIV'?'flex':'inline-block'));
    row.querySelectorAll('input.edit-mode').forEach(el => { if (!inEdit) el.style.display = 'block'; });
};

window.submitKendaraanEdit = async function(id) {
    const form = document.getElementById('kedit-' + id);
    const row  = document.getElementById('krow-' + id);
    const fd   = new FormData(form);
    fd.set('nomor_kendaraan', row.querySelector('input[name="nomor_kendaraan"]').value);
    fd.set('jenis_kendaraan', row.querySelector('input[name="jenis_kendaraan"]').value);
    fd.set('bidang',          row.querySelector('input[name="bidang"]').value);
    fd.set('set_km',          row.querySelector('input[name="set_km"]').value);
    try {
        const res  = await fetch(form.action, {method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
        const data = await res.json();
        if (res.ok && data.success) {
            Swal.fire({icon:'success',title:'Diperbarui!',text:data.message,timer:1500,showConfirmButton:false,toast:true,position:'top-end'});
            fetchArmada();
        } else { Swal.fire({icon:'error',title:'Gagal',text:data.message||'Terjadi kesalahan.'}); }
    } catch { Swal.fire({icon:'error',title:'Koneksi Bermasalah',text:'Periksa koneksi internet.'}); }
};

window.deleteKendaraan = function(id, nopol) {
    Swal.fire({
        title:'Hapus Kendaraan?',
        html:`<p>Yakin ingin menghapus <strong>${nopol}</strong>?</p>`,
        icon:'warning',showCancelButton:true,
        confirmButtonColor:'#ef4444',cancelButtonColor:'#64748b',
        confirmButtonText:'Ya, Hapus',cancelButtonText:'Batal',
    }).then(async r => {
        if (!r.isConfirmed) return;
        const form = document.getElementById('kdel-' + id);
        try {
            const res  = await fetch(form.action,{method:'POST',body:new FormData(form),headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
            const data = await res.json();
            if (res.ok && data.success) {
                Swal.fire({icon:'success',title:'Terhapus!',text:data.message,timer:1500,showConfirmButton:false});
                fetchArmada();
            } else { Swal.fire({icon:'error',title:'Gagal',text:data.message||'Terjadi kesalahan.'}); }
        } catch { Swal.fire({icon:'error',title:'Koneksi Bermasalah',text:'Periksa koneksi internet.'}); }
    });
};

window.resetArmadaFilters = function() {
    document.getElementById('armada-search').value = '';
    document.getElementById('armada-perpage').value = '10';
    armadaPerPage = 10; armadaPage = 1;
    fetchArmada();
};

document.getElementById('form-add-armada').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-add-armada');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    try {
        const res  = await fetch('{{ route("admin.portal-manajemen.kendaraan.store") }}',{method:'POST',body:new FormData(this),headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
        const data = await res.json();
        if (res.ok && data.success) {
            Swal.fire({icon:'success',title:'Berhasil!',text:data.message,timer:1600,showConfirmButton:false});
            this.reset(); toggleAddForm('armada'); fetchArmada();
        } else {
            const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message||'Gagal.');
            Swal.fire({icon:'error',title:'Gagal',text:msg});
        }
    } catch { Swal.fire({icon:'error',title:'Koneksi Bermasalah',text:'Periksa koneksi internet.'}); }
    finally {
        btn.disabled = false;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="margin-right:5px"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> Simpan';
    }
});

document.getElementById('armada-search').addEventListener('input', debounce(() => { armadaPage = 1; fetchArmada(); }, 350));
document.getElementById('armada-perpage').addEventListener('change', e => { armadaPerPage = parseInt(e.target.value); armadaPage = 1; fetchArmada(); });

/* ═══════════════════════════════════════════════════════════════════════ */
/* MANAJEMEN USER                                                          */
/* ═══════════════════════════════════════════════════════════════════════ */
let userPage = 1, userPerPage = 15;

async function fetchUsers(scroll = false) {
    document.getElementById('user-loading').style.display = 'flex';
    const params = new URLSearchParams({
        search:      document.getElementById('user-search').value.trim(),
        role_filter: document.getElementById('user-role-filter').value,
        per_page:    userPerPage,
        page:        userPage,
    });
    try {
        const json = await fetch(`${BASE}/api/admin/portal/users?${params}`).then(r => r.json());
        renderUserTable(json.data, json.current_page, json.per_page);
        buildPagination(document.getElementById('user-pagination'), json, p => { userPage = p; fetchUsers(true); });
        document.getElementById('tc-users').textContent = json.total;
        if (scroll) document.getElementById('section-users').scrollIntoView({behavior:'smooth', block:'start'});
    } catch(e) { console.error(e); }
    finally { document.getElementById('user-loading').style.display = 'none'; }
}

function renderUserTable(rows, page, perPage) {
    const tbody = document.getElementById('user-tbody');
    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="mgmt-empty">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="margin:0 auto 8px;display:block;opacity:.3"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.5"/></svg>
            Tidak ada data user.
        </td></tr>`;
        return;
    }
    const offset = (page - 1) * perPage;
    tbody.innerHTML = rows.map((u, i) => {
        const isPic = u.role === 'pic_kendaraan';
        const avatarBg = isPic ? 'linear-gradient(135deg,#7c3aed,#a78bfa)' : 'linear-gradient(135deg,#2563eb,#60a5fa)';
        const badge = isPic
            ? '<span class="mgmt-role-badge mgmt-role-pic">PIC Kendaraan</span>'
            : '<span class="mgmt-role-badge mgmt-role-driver">Driver</span>';
        return `
        <tr id="urow-${u.id}">
            <td class="text-muted">${offset + i + 1}</td>
            <td>
                <div class="mgmt-user-cell">
                    <div class="mgmt-user-avatar" style="background:${avatarBg}">${escHtml(u.name.charAt(0).toUpperCase())}</div>
                    <p class="mgmt-user-name">${escHtml(u.name)}</p>
                </div>
            </td>
            <td><span class="mgmt-username">${escHtml(u.username)}</span></td>
            <td>${badge}</td>
            <td class="text-center">
                <div class="mgmt-actions">
                    <button type="button" class="mgmt-act-btn mgmt-act-edit" onclick="openUserEdit(${u.id},'${escJs(u.name)}','${escJs(u.username)}','${u.role}')">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Edit
                    </button>
                    <button type="button" class="mgmt-act-btn mgmt-act-del" onclick="deleteUser(${u.id},'${escJs(u.name)}')">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Hapus
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

window.resetUserFilters = function() {
    document.getElementById('user-search').value = '';
    document.getElementById('user-role-filter').value = '';
    document.getElementById('user-perpage').value = '15';
    userPerPage = 15; userPage = 1;
    fetchUsers();
};

document.getElementById('user-search').addEventListener('input', debounce(() => { userPage = 1; fetchUsers(); }, 350));
document.getElementById('user-role-filter').addEventListener('change', () => { userPage = 1; fetchUsers(); });
document.getElementById('user-perpage').addEventListener('change', e => { userPerPage = parseInt(e.target.value); userPage = 1; fetchUsers(); });

document.getElementById('form-add-user').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-add-user');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    try {
        const res  = await fetch('{{ route("admin.users.store") }}',{method:'POST',body:new FormData(this),headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
        const data = await res.json();
        if (res.ok && data.success) {
            Swal.fire({icon:'success',title:'Berhasil!',text:data.message,timer:1600,showConfirmButton:false});
            this.reset();
            document.getElementById('add-user-pw').value = '{{ $defaultPassword }}';
            toggleAddForm('user'); fetchUsers();
        } else {
            const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message||'Gagal.');
            Swal.fire({icon:'error',title:'Gagal',text:msg});
        }
    } catch { Swal.fire({icon:'error',title:'Koneksi Bermasalah',text:'Periksa koneksi internet.'}); }
    finally {
        btn.disabled = false;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="margin-right:5px"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> Simpan';
    }
});

/* ─── Edit modal ─────────────────────────────────────────────────────── */
window.openUserEdit = function(id, name, username, role) {
    document.getElementById('edit-user-id').value  = id;
    document.getElementById('edit-name').value     = name;
    document.getElementById('edit-username').value = username;
    document.getElementById('edit-role').value     = role;
    document.getElementById('edit-password').value = '';

    // Update modal header
    const isPic = role === 'pic_kendaraan';
    const av    = document.getElementById('modal-avatar');
    av.textContent = name.charAt(0).toUpperCase();
    av.style.background = isPic ? 'linear-gradient(135deg,#7c3aed,#a78bfa)' : 'linear-gradient(135deg,#2563eb,#60a5fa)';
    document.getElementById('modal-sub-text').textContent = isPic ? 'PIC Kendaraan · @' + username : 'Driver · @' + username;

    document.getElementById('user-edit-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('edit-name').focus(), 100);
};

window.closeUserModal = function() {
    document.getElementById('user-edit-modal').style.display = 'none';
    document.body.style.overflow = '';
};

window.submitUserEdit = async function() {
    const id  = document.getElementById('edit-user-id').value;
    const btn = document.getElementById('btn-save-edit');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    const fd = new FormData();
    fd.append('_token', CSRF); fd.append('_method', 'PUT');
    fd.append('name',     document.getElementById('edit-name').value);
    fd.append('username', document.getElementById('edit-username').value);
    fd.append('role',     document.getElementById('edit-role').value);
    const pw = document.getElementById('edit-password').value;
    if (pw) fd.append('password', pw);
    try {
        const res  = await fetch(`/admin/users/${id}`,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
        const data = await res.json();
        if (res.ok && data.success) {
            Swal.fire({icon:'success',title:'Diperbarui!',text:data.message,timer:1500,showConfirmButton:false,toast:true,position:'top-end'});
            closeUserModal(); fetchUsers();
        } else {
            const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message||'Gagal.');
            Swal.fire({icon:'error',title:'Gagal',text:msg});
        }
    } catch { Swal.fire({icon:'error',title:'Koneksi Bermasalah',text:'Periksa koneksi internet.'}); }
    finally {
        btn.disabled = false;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="margin-right:5px"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Simpan Perubahan';
    }
};

window.deleteUser = function(id, nama) {
    Swal.fire({
        title:'Hapus User?',
        html:`<p>Yakin ingin menghapus <strong>${nama}</strong>?</p>
              <div style="margin-top:10px;padding:10px;background:#fef9c3;border:1px solid #fde68a;border-radius:8px;font-size:0.82rem;color:#92400e;text-align:left">
                ⚠️ Data ceklist yang dibuat oleh user ini tidak akan terhapus.
              </div>`,
        icon:'warning',showCancelButton:true,
        confirmButtonColor:'#ef4444',cancelButtonColor:'#64748b',
        confirmButtonText:'Ya, Hapus',cancelButtonText:'Batal',
    }).then(async r => {
        if (!r.isConfirmed) return;
        const fd = new FormData();
        fd.append('_token', CSRF); fd.append('_method', 'DELETE');
        try {
            const res  = await fetch(`/admin/users/${id}`,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
            const data = await res.json();
            if (res.ok && data.success) {
                Swal.fire({icon:'success',title:'Terhapus!',text:data.message,timer:1500,showConfirmButton:false});
                fetchUsers();
            } else { Swal.fire({icon:'error',title:'Gagal',text:data.message||'Terjadi kesalahan.'}); }
        } catch { Swal.fire({icon:'error',title:'Koneksi Bermasalah',text:'Periksa koneksi internet.'}); }
    });
};

/* ─── Init pagination ────────────────────────────────────────────────── */
buildPagination(document.getElementById('armada-pagination'),
    { current_page:{{ $kendaraans->currentPage() }}, last_page:{{ $kendaraans->lastPage() }}, total:{{ $kendaraans->total() }}, per_page:{{ $kendaraans->perPage() }} },
    p => { armadaPage = p; fetchArmada(true); });

buildPagination(document.getElementById('user-pagination'),
    { current_page:{{ $users->currentPage() }}, last_page:{{ $users->lastPage() }}, total:{{ $users->total() }}, per_page:{{ $users->perPage() }} },
    p => { userPage = p; fetchUsers(true); });

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeUserModal(); });

})();
</script>

<script>
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
