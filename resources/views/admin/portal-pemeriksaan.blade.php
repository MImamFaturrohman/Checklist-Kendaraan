<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Pemeriksaan Kendaraan - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
</head>
<body class="dash-body">

    {{-- Background decoration layers --}}
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

    {{-- ══ NAVBAR ══ --}}
    <nav class="dash-nav" id="dash-nav">
        <div class="dash-nav-inner">
            <div class="dash-nav-brand">
                <img src="{{ asset('images/VMS.png') }}" alt="VMS" class="dash-nav-logo">
                <div>
                    <div class="dash-nav-title">Portal Pemeriksaan Kendaraan</div>
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

<div class="admin-shell" style="position:relative;z-index:1">
    @php $canAccessDatabase = $canAccessDatabase ?? false; @endphp

    <div class="portal-wrapper">

        {{-- ============================================================
             STATS ROW  (white card, left-accent border)
        ============================================================ --}}
        <div class="portal-stats-row">
            <div class="portal-stat-card" style="--accent:#002a7a">
                <div class="portal-stat-icon" style="background:rgba(0,42,122,.1);color:#002a7a">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/></svg>
                </div>
                <div>
                    <div class="portal-stat-value">{{ $dbStats['total'] }}</div>
                    <div class="portal-stat-label">Total Ceklist</div>
                </div>
            </div>
            <div class="portal-stat-card" style="--accent:#4f46e5">
                <div class="portal-stat-icon" style="background:rgba(79,70,229,.1);color:#4f46e5">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><ellipse cx="12" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="2"/><path d="M5 5V19C5 20.7 8.1 22 12 22C15.9 22 19 20.7 19 19V5" stroke="currentColor" stroke-width="2"/><path d="M5 12C5 13.7 8.1 15 12 15C15.9 15 19 13.7 19 12" stroke="currentColor" stroke-width="2"/></svg>
                </div>
                <div>
                    <div class="portal-stat-value">{{ $dbStats['kendaraan_unik'] }}</div>
                    <div class="portal-stat-label">Kendaraan Unik</div>
                </div>
            </div>
            <div class="portal-stat-card" style="--accent:#16a34a">
                <div class="portal-stat-icon" style="background:rgba(22,163,74,.1);color:#16a34a">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M3 21v-2a4 4 0 014-4h4a4 4 0 014 4v2" stroke="currentColor" stroke-width="2"/></svg>
                </div>
                <div>
                    <div class="portal-stat-value">{{ $dbStats['driver_aktif'] }}</div>
                    <div class="portal-stat-label">Driver Aktif</div>
                </div>
            </div>
            <div class="portal-stat-card" style="--accent:#d97706">
                <div class="portal-stat-icon" style="background:rgba(217,119,6,.1);color:#d97706">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <div>
                    <div class="portal-stat-value">{{ $dbStats['bulan_ini'] }}</div>
                    <div class="portal-stat-label">Ceklist Bulan Ini</div>
                </div>
            </div>
            <div class="portal-stat-card" style="--accent:#dc2626">
                <div class="portal-stat-icon" style="background:rgba(220,38,38,.1);color:#dc2626">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M7 3H14L19 8V21H7V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 3V8H19" stroke="currentColor" stroke-width="2"/><path d="M9 13H15M9 17H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <div>
                    <div class="portal-stat-value">{{ $pdfStats['total'] }}</div>
                    <div class="portal-stat-label">Total Arsip PDF</div>
                </div>
            </div>
            <div class="portal-stat-card" style="--accent:#0891b2">
                <div class="portal-stat-icon" style="background:rgba(8,145,178,.1);color:#0891b2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M7 3H14L19 8V21H7V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 3V8H19" stroke="currentColor" stroke-width="2"/><path d="M9 13H15M9 17H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <div>
                    <div class="portal-stat-value">{{ $pdfStats['bulan_ini'] }}</div>
                    <div class="portal-stat-label">PDF Bulan Ini</div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             CHARTS  (3 small + 1 wide BBM horizontal)
        ============================================================ --}}
        <div class="portal-charts-grid">
            <div class="portal-chart-card">
                <div class="portal-chart-title">Ceklist per Bulan</div>
                <div class="portal-chart-container"><canvas id="chartBulan"></canvas></div>
            </div>
            <div class="portal-chart-card">
                <div class="portal-chart-title">Ceklist per Kendaraan</div>
                <div class="portal-chart-container"><canvas id="chartKendaraan"></canvas></div>
            </div>
            <div class="portal-chart-card">
                <div class="portal-chart-title">Distribusi Shift</div>
                <div class="portal-chart-container portal-chart-container--doughnut"><canvas id="chartShift"></canvas></div>
            </div>
            <div class="portal-chart-card portal-chart-card--wide">
                <div class="portal-chart-title">Rata-rata Level BBM per Kendaraan (%)</div>
                <div class="portal-chart-container portal-chart-container--bbm"><canvas id="chartBbm"></canvas></div>
            </div>
        </div>

        @if($canAccessDatabase)
        {{-- ============================================================
             GLOBAL SEARCH & FILTER
        ============================================================ --}}
        <div class="portal-global-search-wrap" id="portal-global-bar">
            <div class="portal-global-label">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/></svg>
                <span>Global Search</span>
                <span class="portal-global-hint">mencari di Database, Foto, dan PDF sekaligus</span>
            </div>
            <div class="portal-global-inputs">
                <div class="portal-global-search-field">
                    <input type="text" id="global-search" placeholder="Cari nopol, driver, jenis..." class="portal-global-input" autocomplete="off">
                </div>
                <input type="date" id="global-dari" class="portal-global-date" title="Tanggal dari">
                <input type="date" id="global-sampai" class="portal-global-date" title="Tanggal sampai">
                <select id="global-nopol" class="portal-global-select">
                    <option value="">Semua Nopol</option>
                    @foreach($nopolList as $n)
                        <option value="{{ $n }}">{{ $n }}</option>
                    @endforeach
                </select>
                <select id="global-shift" class="portal-global-select">
                    <option value="">Semua Shift</option>
                    <option value="Pagi">Pagi</option>
                    <option value="Siang">Siang</option>
                </select>
                <button type="button" id="global-reset" class="portal-global-reset">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                    Reset
                </button>
            </div>
        </div>

        {{-- ============================================================
             SECTION TABS
        ============================================================ --}}
        <div class="portal-section-tabs">
            <button class="portal-section-tab active" data-section="db">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><ellipse cx="12" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="2"/><path d="M5 5V19C5 20.7 8.1 22 12 22C15.9 22 19 20.7 19 19V5" stroke="currentColor" stroke-width="2"/><path d="M5 12C5 13.7 8.1 15 12 15C15.9 15 19 13.7 19 12" stroke="currentColor" stroke-width="2"/></svg>
                Database Sheet
            </button>
            <button class="portal-section-tab" data-section="foto">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="10" r="2" stroke="currentColor" stroke-width="2"/><path d="M21 16L16 11L7 20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Log Foto Fisik
            </button>
            <button class="portal-section-tab" data-section="pdf">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M7 3H14L19 8V21H7V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 3V8H19" stroke="currentColor" stroke-width="2"/><path d="M9 13H15M9 17H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Arsip PDF
            </button>
        </div>

        {{-- ============================================================
             SECTION: DATABASE SHEET
        ============================================================ --}}
        <div class="portal-section" id="section-db">
            <div class="portal-section-header">
                <div class="portal-section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><ellipse cx="12" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="2"/><path d="M5 5V19C5 20.7 8.1 22 12 22C15.9 22 19 20.7 19 19V5" stroke="currentColor" stroke-width="2"/><path d="M5 12C5 13.7 8.1 15 12 15C15.9 15 19 13.7 19 12" stroke="currentColor" stroke-width="2"/></svg>
                    Database Sheet
                </div>
                <button
                    type="button"
                    id="db-sync-btn"
                    data-export-url="{{ route('admin.portal-pemeriksaan.export') }}"
                    class="btn-export"
                    style="font-size:0.8rem;padding:7px 14px"
                >
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="currentColor" stroke-width="2"/><polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="2"/></svg>
                    Sinkronkan
                </button>
            </div>
            <div id="db-sync-alert" style="display:none;margin-bottom:12px;padding:10px 12px;border-radius:10px;font-size:.82rem;line-height:1.45"></div>

            {{-- Local filters --}}
            <div class="portal-local-filters">
                <div class="admin-search-wrap portal-search-full">
                    <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <input type="text" id="db-search" placeholder="Cari nopol, driver..." class="admin-search-input" autocomplete="off">
                </div>
                <input type="date" id="db-dari" class="admin-filter-input" title="Dari tanggal">
                <input type="date" id="db-sampai" class="admin-filter-input" title="Sampai tanggal">
                <select id="db-nopol" class="admin-filter-input">
                    <option value="">Semua Nopol</option>
                    @foreach($nopolList as $n)<option value="{{ $n }}">{{ $n }}</option>@endforeach
                </select>
                <select id="db-shift" class="admin-filter-input">
                    <option value="">Semua Shift</option>
                    <option value="Pagi">Pagi</option>
                    <option value="Siang">Siang</option>
                </select>
                <div class="portal-perpage-wrap">
                    <label class="portal-perpage-label">Tampilkan</label>
                    <select id="db-perpage" class="admin-filter-input portal-perpage-select">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <button type="button" class="portal-local-reset" data-section-reset="db">Reset</button>
            </div>

            {{-- Sub-tabs --}}
            <div class="admin-tabs" style="margin-bottom:0">
                <button class="admin-tab active" data-db-tab="all">Semua Data</button>
                <button class="admin-tab" data-db-tab="exterior">Exterior</button>
                <button class="admin-tab" data-db-tab="interior">Interior</button>
                <button class="admin-tab" data-db-tab="mesin">Mesin</button>
            </div>

            <div id="db-loading" class="portal-loading" style="display:none">
                <span class="portal-loading-dot"></span><span class="portal-loading-dot"></span><span class="portal-loading-dot"></span>
            </div>

            {{-- ALL --}}
            <div class="db-tab-panel active" data-db-panel="all">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>#</th><th>Tanggal</th><th>Shift</th><th>Nopol</th><th>Jenis</th><th>Driver Serah</th><th>Driver Terima</th><th>BBM</th><th>KM Awal</th><th>KM Akhir</th></tr></thead>
                        <tbody id="db-tbody-all">
                            @forelse($dbChecklists as $c)
                            <tr>
                                <td>{{ ($dbChecklists->currentPage()-1)*$dbChecklists->perPage()+$loop->iteration }}</td>
                                <td>{{ $c->tanggal->format('d/m/Y') }}</td><td>{{ $c->shift }}</td>
                                <td><strong>{{ $c->nomor_kendaraan }}</strong></td><td>{{ $c->jenis_kendaraan }}</td>
                                <td>{{ $c->driver_serah }}</td><td>{{ $c->driver_terima }}</td>
                                <td>{{ $c->level_bbm }}%</td><td>{{ number_format($c->km_awal) }}</td><td>{{ number_format($c->km_akhir ?? 0) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="10" class="portal-empty">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @php
                $statusStyle = fn($v) => $v === 'ok' ? 'color:#16a34a' : (in_array($v,['no','tidak_ok'],true) ? 'color:#dc2626' : 'color:#334155');
                $statusLabel = fn($v) => $v === 'ok' ? 'OK' : (in_array($v,['no','tidak_ok'],true) ? 'NO' : strtoupper($v ?? '-'));
            @endphp

            {{-- EXTERIOR --}}
            <div class="db-tab-panel" data-db-panel="exterior" style="display:none">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Nopol</th><th>Tanggal</th><th>Body</th><th>Kaca</th><th>Spion</th><th>L.Utama</th><th>L.Sein</th><th>Ban</th><th>Velg</th><th>Wiper</th></tr></thead>
                        <tbody id="db-tbody-exterior">
                            @foreach($dbChecklists as $c)
                            @if($c->exterior)
                            <tr>
                                <td><strong>{{ $c->nomor_kendaraan }}</strong></td><td>{{ $c->tanggal->format('d/m/Y') }}</td>
                                @foreach(['body_kendaraan','kaca','spion','lampu_utama','lampu_sein','ban','velg','wiper'] as $k)
                                <td style="font-weight:700;font-size:0.75rem;{{ $statusStyle($c->exterior->$k) }}">{{ $statusLabel($c->exterior->$k) }}</td>
                                @endforeach
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- INTERIOR --}}
            <div class="db-tab-panel" data-db-panel="interior" style="display:none">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Nopol</th><th>Tanggal</th><th>Jok</th><th>Dashboard</th><th>AC</th><th>Sabuk</th><th>Audio</th><th>Kebersihan</th></tr></thead>
                        <tbody id="db-tbody-interior">
                            @foreach($dbChecklists as $c)
                            @if($c->interior)
                            <tr>
                                <td><strong>{{ $c->nomor_kendaraan }}</strong></td><td>{{ $c->tanggal->format('d/m/Y') }}</td>
                                @foreach(['jok','dashboard','ac','sabuk_pengaman','audio','kebersihan'] as $k)
                                <td style="font-weight:700;font-size:0.75rem;{{ $statusStyle($c->interior->$k) }}">{{ $statusLabel($c->interior->$k) }}</td>
                                @endforeach
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- MESIN --}}
            <div class="db-tab-panel" data-db-panel="mesin" style="display:none">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Nopol</th><th>Tanggal</th><th>Mesin</th><th>Oli</th><th>Radiator</th><th>Rem</th><th>Kopling</th><th>Transmisi</th><th>Indikator</th></tr></thead>
                        <tbody id="db-tbody-mesin">
                            @foreach($dbChecklists as $c)
                            @if($c->mesin)
                            <tr>
                                <td><strong>{{ $c->nomor_kendaraan }}</strong></td><td>{{ $c->tanggal->format('d/m/Y') }}</td>
                                @foreach(['mesin','oli','radiator','rem','kopling','transmisi','indikator'] as $k)
                                <td style="font-weight:700;font-size:0.75rem;{{ $statusStyle($c->mesin->$k) }}">{{ $statusLabel($c->mesin->$k) }}</td>
                                @endforeach
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="db-pagination" class="portal-pagination-wrap"></div>
        </div>

        {{-- ============================================================
             SECTION: LOG FOTO FISIK
        ============================================================ --}}
        <div class="portal-section" id="section-foto" style="display:none">
            <div class="portal-section-header">
                <div class="portal-section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="10" r="2" stroke="currentColor" stroke-width="2"/><path d="M21 16L16 11L7 20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Log Foto Fisik
                </div>
            </div>

            {{-- Local filters --}}
            <div class="portal-local-filters">
                <div class="admin-search-wrap portal-search-full">
                    <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <input type="text" id="foto-search" placeholder="Cari nopol, driver..." class="admin-search-input" autocomplete="off">
                </div>
                <input type="date" id="foto-dari" class="admin-filter-input" title="Dari tanggal">
                <input type="date" id="foto-sampai" class="admin-filter-input" title="Sampai tanggal">
                <select id="foto-nopol" class="admin-filter-input">
                    <option value="">Semua Nopol</option>
                    @foreach($nopolList as $n)<option value="{{ $n }}">{{ $n }}</option>@endforeach
                </select>
                <div class="portal-perpage-wrap">
                    <label class="portal-perpage-label">Tampilkan</label>
                    <select id="foto-perpage" class="admin-filter-input portal-perpage-select">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <button type="button" class="portal-local-reset" data-section-reset="foto">Reset</button>
            </div>

            {{-- Sub-tabs --}}
            <div class="admin-tabs" style="margin-bottom:0">
                <button class="admin-tab active" data-foto-tab="exterior">Eksterior</button>
                <button class="admin-tab" data-foto-tab="interior">Interior</button>
                <button class="admin-tab" data-foto-tab="mesin">Mesin</button>
                <button class="admin-tab" data-foto-tab="bbm">BBM</button>
            </div>

            <div id="foto-loading" class="portal-loading" style="display:none">
                <span class="portal-loading-dot"></span><span class="portal-loading-dot"></span><span class="portal-loading-dot"></span>
            </div>

            @php
                $baseUrl = url('/');
                $resolvePhotoUrl = function (?string $path) use ($baseUrl) {
                    if (!$path) return null;
                    if (str_starts_with($path, 'http')) return $path;
                    if (str_starts_with($path, '/storage/')) return $baseUrl . $path;
                    if (str_starts_with($path, 'storage/')) return $baseUrl . '/' . $path;
                    return $baseUrl . '/storage/' . ltrim($path, '/');
                };
            @endphp

            <div class="foto-tab-panel active" data-foto-panel="exterior">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Waktu</th><th>Unit</th><th>Foto</th></tr></thead>
                        <tbody id="foto-tbody-exterior">
                            @php $hasExt = false; @endphp
                            @foreach($fotoChecklists as $c)
                                @if($c->exterior && ($c->exterior->foto_depan || $c->exterior->foto_kanan || $c->exterior->foto_kiri || $c->exterior->foto_belakang))
                                @php $hasExt = true; @endphp
                                <tr>
                                    <td>{{ $c->tanggal?->format('d/m/Y') }} {{ $c->jam_serah_terima ?? '' }}</td>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                                    <td><div class="portal-thumb-row">
                                        @foreach(['foto_depan'=>'Depan','foto_kanan'=>'Kanan','foto_kiri'=>'Kiri','foto_belakang'=>'Belakang'] as $field=>$label)
                                            @if($c->exterior->$field)
                                                <a href="{{ $resolvePhotoUrl($c->exterior->$field) }}" target="_blank" rel="noopener" title="{{ $label }}">
                                                    <img src="{{ $resolvePhotoUrl($c->exterior->$field) }}" alt="{{ $label }}" loading="lazy" class="portal-thumb">
                                                </a>
                                            @endif
                                        @endforeach
                                    </div></td>
                                </tr>
                                @endif
                            @endforeach
                            @unless($hasExt)<tr><td colspan="3" class="portal-empty">Belum ada foto eksterior.</td></tr>@endunless
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="foto-tab-panel" data-foto-panel="interior" style="display:none">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Waktu</th><th>Unit</th><th>Foto</th></tr></thead>
                        <tbody id="foto-tbody-interior">
                            @php $hasInt = false; @endphp
                            @foreach($fotoChecklists as $c)
                                @if($c->interior && ($c->interior->foto_1 || $c->interior->foto_2 || $c->interior->foto_3))
                                @php $hasInt = true; @endphp
                                <tr>
                                    <td>{{ $c->tanggal?->format('d/m/Y') }} {{ $c->jam_serah_terima ?? '' }}</td>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                                    <td><div class="portal-thumb-row">
                                        @for($i=1;$i<=3;$i++) @php $f="foto_{$i}"; @endphp
                                            @if($c->interior->$f)
                                                <a href="{{ $resolvePhotoUrl($c->interior->$f) }}" target="_blank" rel="noopener">
                                                    <img src="{{ $resolvePhotoUrl($c->interior->$f) }}" alt="Interior {{ $i }}" loading="lazy" class="portal-thumb">
                                                </a>
                                            @endif
                                        @endfor
                                    </div></td>
                                </tr>
                                @endif
                            @endforeach
                            @unless($hasInt)<tr><td colspan="3" class="portal-empty">Belum ada foto interior.</td></tr>@endunless
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="foto-tab-panel" data-foto-panel="mesin" style="display:none">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Waktu</th><th>Unit</th><th>Foto</th></tr></thead>
                        <tbody id="foto-tbody-mesin">
                            @php $hasMesin = false; @endphp
                            @foreach($fotoChecklists as $c)
                                @if($c->mesin && ($c->mesin->foto_1 || $c->mesin->foto_2 || $c->mesin->foto_3))
                                @php $hasMesin = true; @endphp
                                <tr>
                                    <td>{{ $c->tanggal?->format('d/m/Y') }} {{ $c->jam_serah_terima ?? '' }}</td>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                                    <td><div class="portal-thumb-row">
                                        @for($i=1;$i<=3;$i++) @php $f="foto_{$i}"; @endphp
                                            @if($c->mesin->$f)
                                                <a href="{{ $resolvePhotoUrl($c->mesin->$f) }}" target="_blank" rel="noopener">
                                                    <img src="{{ $resolvePhotoUrl($c->mesin->$f) }}" alt="Mesin {{ $i }}" loading="lazy" class="portal-thumb">
                                                </a>
                                            @endif
                                        @endfor
                                    </div></td>
                                </tr>
                                @endif
                            @endforeach
                            @unless($hasMesin)<tr><td colspan="3" class="portal-empty">Belum ada foto mesin.</td></tr>@endunless
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="foto-tab-panel" data-foto-panel="bbm" style="display:none">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Waktu</th><th>Unit</th><th>Foto</th></tr></thead>
                        <tbody id="foto-tbody-bbm">
                            @php $hasBbm = false; @endphp
                            @foreach($fotoChecklists as $c)
                                @if($c->foto_bbm_dashboard)
                                @php $hasBbm = true; @endphp
                                <tr>
                                    <td>{{ $c->tanggal?->format('d/m/Y') }} {{ $c->jam_serah_terima ?? '' }}</td>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                                    <td>
                                        <a href="{{ $resolvePhotoUrl($c->foto_bbm_dashboard) }}" target="_blank" rel="noopener">
                                            <img src="{{ $resolvePhotoUrl($c->foto_bbm_dashboard) }}" alt="BBM" loading="lazy" class="portal-thumb">
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                            @unless($hasBbm)<tr><td colspan="3" class="portal-empty">Belum ada foto BBM.</td></tr>@endunless
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="foto-pagination" class="portal-pagination-wrap"></div>
        </div>

        {{-- ============================================================
             SECTION: ARSIP PDF
        ============================================================ --}}
        <div class="portal-section" id="section-pdf" style="display:none">
            <div class="portal-section-header">
                <div class="portal-section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M7 3H14L19 8V21H7V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 3V8H19" stroke="currentColor" stroke-width="2"/><path d="M9 13H15M9 17H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Arsip PDF
                </div>
            </div>

            {{-- Local filters --}}
            <div class="portal-local-filters">
                <div class="admin-search-wrap portal-search-full">
                    <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <input type="text" id="pdf-search" placeholder="Cari nopol, driver..." class="admin-search-input" autocomplete="off">
                </div>
                <input type="date" id="pdf-dari" class="admin-filter-input" title="Dari tanggal">
                <input type="date" id="pdf-sampai" class="admin-filter-input" title="Sampai tanggal">
                <select id="pdf-nopol" class="admin-filter-input">
                    <option value="">Semua Nopol</option>
                    @foreach($nopolList as $n)<option value="{{ $n }}">{{ $n }}</option>@endforeach
                </select>
                <select id="pdf-shift" class="admin-filter-input">
                    <option value="">Semua Shift</option>
                    <option value="Pagi">Pagi</option>
                    <option value="Siang">Siang</option>
                </select>
                <div class="portal-perpage-wrap">
                    <label class="portal-perpage-label">Tampilkan</label>
                    <select id="pdf-perpage" class="admin-filter-input portal-perpage-select">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <button type="button" class="portal-local-reset" data-section-reset="pdf">Reset</button>
            </div>

            <div id="pdf-loading" class="portal-loading" style="display:none">
                <span class="portal-loading-dot"></span><span class="portal-loading-dot"></span><span class="portal-loading-dot"></span>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>#</th><th>Tanggal</th><th>Nopol</th><th>Driver Serah</th><th>Driver Terima</th><th>Shift</th><th>Aksi</th></tr></thead>
                    <tbody id="pdf-tbody">
                        @forelse($pdfChecklists as $c)
                        @php
                            $resolvePdfUrl = function (?string $path) use ($baseUrl) {
                                if (!$path) return null;
                                if (str_starts_with($path, 'http')) return $path;
                                if (str_starts_with($path, '/storage/')) return $baseUrl . $path;
                                if (str_starts_with($path, 'storage/')) return $baseUrl . '/' . $path;
                                return $baseUrl . '/storage/' . ltrim($path, '/');
                            };
                        @endphp
                        <tr>
                            <td>{{ ($pdfChecklists->currentPage()-1)*$pdfChecklists->perPage()+$loop->iteration }}</td>
                            <td>{{ $c->tanggal->format('d/m/Y') }}</td>
                            <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                            <td>{{ $c->driver_serah }}</td>
                            <td>{{ $c->driver_terima }}</td>
                            <td>{{ $c->shift }}</td>
                            <td>
                                @if($c->pdf_path)
                                    <a href="{{ $resolvePdfUrl($c->pdf_path) }}" target="_blank" class="btn-view-pdf">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/></svg>
                                        View PDF
                                    </a>
                                @else
                                    <span style="color:#94a3b8;font-size:.75rem">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="portal-empty">Belum ada laporan PDF.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="pdf-pagination" class="portal-pagination-wrap"></div>
        </div>
        @else
        <div class="portal-section">
            <div class="portal-empty" style="padding: 20px 24px;">
                Akses data detail database, foto fisik, dan arsip PDF hanya tersedia untuk Superadmin.
            </div>
        </div>
        @endif

    </div>{{-- end portal-wrapper --}}
</div>{{-- end admin-shell --}}

<script>
(function () {
    'use strict';

    /* ================================================================
       CONFIG & STATE
    ================================================================ */
    const BASE_URL   = '{{ url("/") }}';
    const CHART_DATA = @json($chartData);
    const CAN_ACCESS_DATABASE = @json($canAccessDatabase);
    const INIT_META  = CAN_ACCESS_DATABASE
        ? {
            db:   @json($dbMeta),
            foto: @json($fotoMeta),
            pdf:  @json($pdfMeta),
        }
        : null;

    let dbPage   = 1, dbPerPage   = 10;
    let fotoPage = 1, fotoPerPage = 10;
    let pdfPage  = 1, pdfPerPage  = 10;

    /* ================================================================
       CHARTS — dark-mode aware, rebuilds on theme toggle
    ================================================================ */
    const YELLOW = '#ffd700';
    const GREEN  = '#16a34a';
    const RED    = '#dc2626';
    const SLATE  = '#94a3b8';
    const INDIGO = '#818cf8';

    let _chartInstances = {};

    function _buildCharts() {
        Object.values(_chartInstances).forEach(c => { try { c.destroy(); } catch(e){} });
        _chartInstances = {};

        const dark  = document.body.classList.contains('dark');
        const blue  = dark ? '#60a5fa' : '#002a7a';
        const grid  = dark ? 'rgba(200,218,255,0.1)' : 'rgba(0,0,0,0.08)';
        const tick  = dark ? 'rgba(200,218,255,0.65)' : '#64748b';
        const lgnd  = dark ? 'rgba(200,218,255,0.75)' : '#475569';
        const bdr   = dark ? 'rgba(200,218,255,0.12)' : 'rgba(255,255,255,0.8)';

        const commonOpts = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
        };
        const xyScales = {
            y: { beginAtZero: true, ticks: { stepSize: 1, color: tick }, grid: { color: grid } },
            x: { ticks: { maxRotation: 45, font: { size: 11 }, color: tick }, grid: { color: grid } },
        };

        // Ceklist per bulan — line
        const ctxBulan = document.getElementById('chartBulan');
        if (ctxBulan) {
            _chartInstances.bulan = new Chart(ctxBulan, {
                type: 'line',
                data: {
                    labels: CHART_DATA.perBulan.labels,
                    datasets: [{
                        data: CHART_DATA.perBulan.data,
                        borderColor: blue,
                        backgroundColor: dark ? 'rgba(96,165,250,0.1)' : 'rgba(0,42,122,0.08)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: blue,
                    }],
                },
                options: { ...commonOpts, scales: xyScales },
            });
        }

        // Ceklist per kendaraan — bar
        const ctxKendaraan = document.getElementById('chartKendaraan');
        if (ctxKendaraan) {
            _chartInstances.kendaraan = new Chart(ctxKendaraan, {
                type: 'bar',
                data: {
                    labels: CHART_DATA.perKendaraan.labels,
                    datasets: [{
                        data: CHART_DATA.perKendaraan.data,
                        backgroundColor: blue,
                        borderRadius: 4,
                    }],
                },
                options: {
                    ...commonOpts,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, color: tick }, grid: { color: grid } },
                        x: { ticks: { maxRotation: 45, font: { size: 10 }, color: tick }, grid: { color: grid } },
                    },
                },
            });
        }

        // Distribusi shift — doughnut
        const ctxShift = document.getElementById('chartShift');
        if (ctxShift) {
            _chartInstances.shift = new Chart(ctxShift, {
                type: 'doughnut',
                data: {
                    labels: CHART_DATA.perShift.labels,
                    datasets: [{
                        data: CHART_DATA.perShift.data,
                        backgroundColor: [blue, YELLOW, SLATE, GREEN, INDIGO],
                        borderWidth: 2,
                        borderColor: bdr,
                    }],
                },
                options: {
                    ...commonOpts,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: { font: { size: 11 }, padding: 10, color: lgnd },
                        },
                    },
                    cutout: '58%',
                },
            });
        }

        // Rata-rata BBM — horizontal bar
        const ctxBbm = document.getElementById('chartBbm');
        if (ctxBbm) {
            _chartInstances.bbm = new Chart(ctxBbm, {
                type: 'bar',
                data: {
                    labels: CHART_DATA.bbmPerKendaraan.labels,
                    datasets: [{
                        data: CHART_DATA.bbmPerKendaraan.data,
                        backgroundColor: CHART_DATA.bbmPerKendaraan.data.map(v =>
                            v >= 70 ? GREEN : v >= 40 ? YELLOW : RED
                        ),
                        borderRadius: 4,
                    }],
                },
                options: {
                    ...commonOpts,
                    indexAxis: 'y',
                    scales: {
                        x: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: { size: 11 }, color: tick }, grid: { color: grid } },
                        y: { ticks: { font: { size: 11 }, color: tick }, grid: { color: grid } },
                    },
                },
            });
        }
    }

    _buildCharts();
    if (!CAN_ACCESS_DATABASE) return;

    /* ================================================================
       SECTION TABS
    ================================================================ */
    document.querySelectorAll('.portal-section-tab').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.portal-section-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const target = btn.dataset.section;
            document.querySelectorAll('.portal-section').forEach(s => {
                const id = s.id.replace('section-', '');
                s.style.display = id === target ? '' : 'none';
            });
        });
    });

    /* ================================================================
       DB SUB-TABS
    ================================================================ */
    document.querySelectorAll('[data-db-tab]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('[data-db-tab]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const t = btn.dataset.dbTab;
            document.querySelectorAll('[data-db-panel]').forEach(p => {
                p.style.display = p.dataset.dbPanel === t ? '' : 'none';
            });
        });
    });

    /* ================================================================
       FOTO SUB-TABS
    ================================================================ */
    document.querySelectorAll('[data-foto-tab]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('[data-foto-tab]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const t = btn.dataset.fotoTab;
            document.querySelectorAll('[data-foto-panel]').forEach(p => {
                p.style.display = p.dataset.fotoPanel === t ? '' : 'none';
            });
        });
    });

    /* ================================================================
       HELPERS
    ================================================================ */
    function buildParams(obj = {}) {
        return new URLSearchParams(
            Object.fromEntries(Object.entries(obj).filter(([, v]) => v !== '' && v != null))
        ).toString();
    }

    function showLoading(id) { const el = document.getElementById(id); if (el) el.style.display = 'flex'; }
    function hideLoading(id) { const el = document.getElementById(id); if (el) el.style.display = 'none'; }

    function scrollToSection(sectionId) {
        const el = document.getElementById(sectionId);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function statusColor(v) {
        if (v === 'ok') return '#16a34a';
        if (v === 'no' || v === 'tidak_ok') return '#dc2626';
        return '#334155';
    }
    function statusLabel(v) {
        if (v === 'ok') return 'OK';
        if (v === 'no' || v === 'tidak_ok') return 'NO';
        return (v ?? '-').toUpperCase();
    }

    function debounce(fn, ms = 380) {
        let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
    }

    /* ================================================================
       PAGINATION BUILDER
    ================================================================ */
    function buildPagination(wrap, meta, onPage) {
        if (!wrap) return;
        if (!meta || meta.last_page <= 1) {
            wrap.innerHTML = meta
                ? `<div class="portal-page-info">${meta.total} data ditemukan</div>`
                : '';
            return;
        }
        const { current_page: cur, last_page: last, total, per_page } = meta;

        const pages = new Set();
        for (let i = 1; i <= Math.min(2, last); i++) pages.add(i);
        for (let i = Math.max(1, cur - 1); i <= Math.min(last, cur + 1); i++) pages.add(i);
        for (let i = Math.max(last - 1, 1); i <= last; i++) pages.add(i);
        const sorted = [...pages].sort((a, b) => a - b);

        let html = '<div class="portal-page-btns">';
        if (cur > 1) html += `<button class="portal-page-btn" data-p="${cur - 1}" title="Prev">‹</button>`;

        let prev = 0;
        sorted.forEach(p => {
            if (prev && p - prev > 1) html += `<span class="portal-page-dots">…</span>`;
            html += `<button class="portal-page-btn${p === cur ? ' active' : ''}" data-p="${p}">${p}</button>`;
            prev = p;
        });

        if (cur < last) html += `<button class="portal-page-btn" data-p="${cur + 1}" title="Next">›</button>`;
        html += `</div><div class="portal-page-info">Hal. ${cur}/${last} · ${total} data · ${per_page}/hal</div>`;

        wrap.innerHTML = html;
        wrap.querySelectorAll('[data-p]').forEach(btn => {
            btn.addEventListener('click', () => onPage(parseInt(btn.dataset.p)));
        });
    }

    /* ================================================================
       DATABASE SHEET AJAX
    ================================================================ */
    function getDbParams() {
        return {
            search:         document.getElementById('db-search')?.value ?? '',
            tanggal_dari:   document.getElementById('db-dari')?.value ?? '',
            tanggal_sampai: document.getElementById('db-sampai')?.value ?? '',
            nopol:          document.getElementById('db-nopol')?.value ?? '',
            shift:          document.getElementById('db-shift')?.value ?? '',
            per_page:       dbPerPage,
            page:           dbPage,
        };
    }

    async function fetchDb(scroll = false) {
        showLoading('db-loading');
        const q = buildParams(getDbParams());
        try {
            const json = await fetch(`${BASE_URL}/api/admin/portal/database-sheet?${q}`).then(r => r.json());
            renderDbAll(json);
            renderDbExterior(json);
            renderDbInterior(json);
            renderDbMesin(json);
            buildPagination(
                document.getElementById('db-pagination'),
                { current_page: json.current_page, last_page: json.last_page, total: json.total, per_page: json.per_page },
                p => { dbPage = p; fetchDb(true); }
            );
            if (scroll) scrollToSection('section-db');
        } finally { hideLoading('db-loading'); }
    }

    function renderDbAll(json) {
        const tbody = document.getElementById('db-tbody-all');
        if (!tbody) return;
        const off = (json.current_page - 1) * json.per_page;
        tbody.innerHTML = json.data.length
            ? json.data.map((c, i) => `<tr>
                <td>${off + i + 1}</td>
                <td>${c.tanggal ?? '-'}</td><td>${c.shift ?? '-'}</td>
                <td><strong>${c.nomor_kendaraan}</strong></td><td>${c.jenis_kendaraan ?? '-'}</td>
                <td>${c.driver_serah ?? '-'}</td><td>${c.driver_terima ?? '-'}</td>
                <td>${c.level_bbm ?? '-'}%</td><td>${c.km_awal ?? '-'}</td><td>${c.km_akhir ?? '-'}</td>
            </tr>`).join('')
            : '<tr><td colspan="10" class="portal-empty">Tidak ada data.</td></tr>';
    }

    function renderDbExterior(json) {
        const tbody = document.getElementById('db-tbody-exterior');
        if (!tbody) return;
        const rows = json.data.filter(c => c.exterior);
        const keys = ['body_kendaraan','kaca','spion','lampu_utama','lampu_sein','ban','velg','wiper'];
        tbody.innerHTML = rows.length
            ? rows.map(c => `<tr><td><strong>${c.nomor_kendaraan}</strong></td><td>${c.tanggal ?? '-'}</td>
                ${keys.map(k => `<td style="font-weight:700;font-size:.75rem;color:${statusColor(c.exterior[k])}">${statusLabel(c.exterior[k])}</td>`).join('')}
            </tr>`).join('')
            : '<tr><td colspan="10" class="portal-empty">Tidak ada data.</td></tr>';
    }

    function renderDbInterior(json) {
        const tbody = document.getElementById('db-tbody-interior');
        if (!tbody) return;
        const rows = json.data.filter(c => c.interior);
        const keys = ['jok','dashboard','ac','sabuk_pengaman','audio','kebersihan'];
        tbody.innerHTML = rows.length
            ? rows.map(c => `<tr><td><strong>${c.nomor_kendaraan}</strong></td><td>${c.tanggal ?? '-'}</td>
                ${keys.map(k => `<td style="font-weight:700;font-size:.75rem;color:${statusColor(c.interior[k])}">${statusLabel(c.interior[k])}</td>`).join('')}
            </tr>`).join('')
            : '<tr><td colspan="8" class="portal-empty">Tidak ada data.</td></tr>';
    }

    function renderDbMesin(json) {
        const tbody = document.getElementById('db-tbody-mesin');
        if (!tbody) return;
        const rows = json.data.filter(c => c.mesin);
        const keys = ['mesin','oli','radiator','rem','kopling','transmisi','indikator'];
        tbody.innerHTML = rows.length
            ? rows.map(c => `<tr><td><strong>${c.nomor_kendaraan}</strong></td><td>${c.tanggal ?? '-'}</td>
                ${keys.map(k => `<td style="font-weight:700;font-size:.75rem;color:${statusColor(c.mesin[k])}">${statusLabel(c.mesin[k])}</td>`).join('')}
            </tr>`).join('')
            : '<tr><td colspan="9" class="portal-empty">Tidak ada data.</td></tr>';
    }

    /* ================================================================
       LOG FOTO AJAX
    ================================================================ */
    function getFotoParams() {
        return {
            search:         document.getElementById('foto-search')?.value ?? '',
            tanggal_dari:   document.getElementById('foto-dari')?.value ?? '',
            tanggal_sampai: document.getElementById('foto-sampai')?.value ?? '',
            nopol:          document.getElementById('foto-nopol')?.value ?? '',
            per_page:       fotoPerPage,
            page:           fotoPage,
        };
    }

    function thumbHtml(url, label) {
        return `<a href="${url}" target="_blank" rel="noopener" title="${label}"><img src="${url}" alt="${label}" loading="lazy" class="portal-thumb"></a>`;
    }

    async function fetchFoto(scroll = false) {
        showLoading('foto-loading');
        const q = buildParams(getFotoParams());
        try {
            const json = await fetch(`${BASE_URL}/api/admin/portal/log-foto?${q}`).then(r => r.json());

            const extRows = json.data.filter(c => c.exterior && Object.values(c.exterior).some(Boolean));
            document.getElementById('foto-tbody-exterior').innerHTML = extRows.length
                ? extRows.map(c => `<tr><td>${c.waktu}</td><td><strong>${c.nomor_kendaraan}</strong></td>
                    <td><div class="portal-thumb-row">
                        ${[['foto_depan','Depan'],['foto_kanan','Kanan'],['foto_kiri','Kiri'],['foto_belakang','Belakang']].map(([f,l]) => c.exterior[f] ? thumbHtml(c.exterior[f], l) : '').join('')}
                    </div></td></tr>`).join('')
                : '<tr><td colspan="3" class="portal-empty">Belum ada foto eksterior.</td></tr>';

            const intRows = json.data.filter(c => c.interior && (c.interior.foto_1 || c.interior.foto_2 || c.interior.foto_3));
            document.getElementById('foto-tbody-interior').innerHTML = intRows.length
                ? intRows.map(c => `<tr><td>${c.waktu}</td><td><strong>${c.nomor_kendaraan}</strong></td>
                    <td><div class="portal-thumb-row">
                        ${[1,2,3].map(i => c.interior[`foto_${i}`] ? thumbHtml(c.interior[`foto_${i}`], `Interior ${i}`) : '').join('')}
                    </div></td></tr>`).join('')
                : '<tr><td colspan="3" class="portal-empty">Belum ada foto interior.</td></tr>';

            const mesinRows = json.data.filter(c => c.mesin && (c.mesin.foto_1 || c.mesin.foto_2 || c.mesin.foto_3));
            document.getElementById('foto-tbody-mesin').innerHTML = mesinRows.length
                ? mesinRows.map(c => `<tr><td>${c.waktu}</td><td><strong>${c.nomor_kendaraan}</strong></td>
                    <td><div class="portal-thumb-row">
                        ${[1,2,3].map(i => c.mesin[`foto_${i}`] ? thumbHtml(c.mesin[`foto_${i}`], `Mesin ${i}`) : '').join('')}
                    </div></td></tr>`).join('')
                : '<tr><td colspan="3" class="portal-empty">Belum ada foto mesin.</td></tr>';

            const bbmRows = json.data.filter(c => c.foto_bbm);
            document.getElementById('foto-tbody-bbm').innerHTML = bbmRows.length
                ? bbmRows.map(c => `<tr><td>${c.waktu}</td><td><strong>${c.nomor_kendaraan}</strong></td><td>${thumbHtml(c.foto_bbm, 'BBM')}</td></tr>`).join('')
                : '<tr><td colspan="3" class="portal-empty">Belum ada foto BBM.</td></tr>';

            buildPagination(
                document.getElementById('foto-pagination'),
                { current_page: json.current_page, last_page: json.last_page, total: json.total, per_page: json.per_page },
                p => { fotoPage = p; fetchFoto(true); }
            );
            if (scroll) scrollToSection('section-foto');
        } finally { hideLoading('foto-loading'); }
    }

    /* ================================================================
       ARSIP PDF AJAX
    ================================================================ */
    function getPdfParams() {
        return {
            search:         document.getElementById('pdf-search')?.value ?? '',
            tanggal_dari:   document.getElementById('pdf-dari')?.value ?? '',
            tanggal_sampai: document.getElementById('pdf-sampai')?.value ?? '',
            nopol:          document.getElementById('pdf-nopol')?.value ?? '',
            shift:          document.getElementById('pdf-shift')?.value ?? '',
            per_page:       pdfPerPage,
            page:           pdfPage,
        };
    }

    async function fetchPdf(scroll = false) {
        showLoading('pdf-loading');
        const q = buildParams(getPdfParams());
        try {
            const json = await fetch(`${BASE_URL}/api/admin/portal/arsip-pdf?${q}`).then(r => r.json());
            const off = (json.current_page - 1) * json.per_page;
            const tbody = document.getElementById('pdf-tbody');
            if (tbody) {
                tbody.innerHTML = json.data.length
                    ? json.data.map((c, i) => `<tr>
                        <td>${off + i + 1}</td>
                        <td>${c.tanggal ?? '-'}</td>
                        <td><strong>${c.nomor_kendaraan}</strong></td>
                        <td>${c.driver_serah ?? '-'}</td>
                        <td>${c.driver_terima ?? '-'}</td>
                        <td>${c.shift ?? '-'}</td>
                        <td>${c.pdf_url
                            ? `<a href="${c.pdf_url}" target="_blank" class="btn-view-pdf">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/></svg>
                                View PDF</a>`
                            : '<span style="color:#94a3b8;font-size:.75rem">—</span>'}</td>
                    </tr>`).join('')
                    : '<tr><td colspan="7" class="portal-empty">Belum ada laporan PDF.</td></tr>';
            }
            buildPagination(
                document.getElementById('pdf-pagination'),
                { current_page: json.current_page, last_page: json.last_page, total: json.total, per_page: json.per_page },
                p => { pdfPage = p; fetchPdf(true); }
            );
            if (scroll) scrollToSection('section-pdf');
        } finally { hideLoading('pdf-loading'); }
    }

    /* ================================================================
       LOCAL FILTER WIRING
    ================================================================ */
    const debouncedDb   = debounce(() => { dbPage = 1;   fetchDb(); });
    const debouncedFoto = debounce(() => { fotoPage = 1; fetchFoto(); });
    const debouncedPdf  = debounce(() => { pdfPage = 1;  fetchPdf(); });

    ['db-search','db-dari','db-sampai','db-nopol','db-shift'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', debouncedDb);
    });
    ['foto-search','foto-dari','foto-sampai','foto-nopol'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', debouncedFoto);
    });
    ['pdf-search','pdf-dari','pdf-sampai','pdf-nopol','pdf-shift'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', debouncedPdf);
    });

    // Per-page dropdowns
    document.getElementById('db-perpage')?.addEventListener('change', e => {
        dbPerPage = parseInt(e.target.value); dbPage = 1; fetchDb();
    });
    document.getElementById('foto-perpage')?.addEventListener('change', e => {
        fotoPerPage = parseInt(e.target.value); fotoPage = 1; fetchFoto();
    });
    document.getElementById('pdf-perpage')?.addEventListener('change', e => {
        pdfPerPage = parseInt(e.target.value); pdfPage = 1; fetchPdf();
    });

    // Reset buttons
    document.querySelectorAll('[data-section-reset]').forEach(btn => {
        btn.addEventListener('click', () => {
            const p = btn.dataset.sectionReset;
            [`${p}-search`,`${p}-dari`,`${p}-sampai`].forEach(id => { const el = document.getElementById(id); if(el) el.value=''; });
            [`${p}-nopol`,`${p}-shift`].forEach(id => { const el = document.getElementById(id); if(el) el.selectedIndex=0; });
            const ppEl = document.getElementById(`${p}-perpage`);
            if (ppEl) { ppEl.value = '10'; }
            if (p === 'db')   { dbPerPage=10;   dbPage=1;   fetchDb(); }
            if (p === 'foto') { fotoPerPage=10; fotoPage=1; fetchFoto(); }
            if (p === 'pdf')  { pdfPerPage=10;  pdfPage=1;  fetchPdf(); }
        });
    });

    /* ================================================================
       GLOBAL SEARCH & FILTER
    ================================================================ */
    function syncGlobalToLocal(params) {
        ['db','foto','pdf'].forEach(pfx => {
            const s = document.getElementById(`${pfx}-search`);
            const d = document.getElementById(`${pfx}-dari`);
            const u = document.getElementById(`${pfx}-sampai`);
            const n = document.getElementById(`${pfx}-nopol`);
            const h = document.getElementById(`${pfx}-shift`);
            if (s) s.value = params.search;
            if (d) d.value = params.tanggal_dari;
            if (u) u.value = params.tanggal_sampai;
            if (n) n.value = params.nopol;
            if (h) h.value = params.shift ?? '';
        });
    }

    const debouncedGlobal = debounce(() => {
        const params = {
            search:         document.getElementById('global-search')?.value ?? '',
            tanggal_dari:   document.getElementById('global-dari')?.value ?? '',
            tanggal_sampai: document.getElementById('global-sampai')?.value ?? '',
            nopol:          document.getElementById('global-nopol')?.value ?? '',
            shift:          document.getElementById('global-shift')?.value ?? '',
        };
        syncGlobalToLocal(params);
        dbPage=1; fotoPage=1; pdfPage=1;
        fetchDb(); fetchFoto(); fetchPdf();
    });

    ['global-search','global-dari','global-sampai','global-nopol','global-shift'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', debouncedGlobal);
    });

    document.getElementById('global-reset')?.addEventListener('click', () => {
        ['global-search','global-dari','global-sampai'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
        ['global-nopol','global-shift'].forEach(id => { const el=document.getElementById(id); if(el) el.selectedIndex=0; });
        syncGlobalToLocal({ search:'', tanggal_dari:'', tanggal_sampai:'', nopol:'', shift:'' });
        dbPage=1; fotoPage=1; pdfPage=1;
        fetchDb(); fetchFoto(); fetchPdf();
    });

    /* ================================================================
       DATABASE SYNC (without page refresh)
    ================================================================ */
    const syncBtn = document.getElementById('db-sync-btn');
    const syncAlert = document.getElementById('db-sync-alert');

    function showSyncAlert(type, message, sheetUrl = null) {
        if (!syncAlert) return;
        const ok = type === 'success';
        syncAlert.style.display = '';
        syncAlert.style.background = ok ? '#dcfce7' : '#fee2e2';
        syncAlert.style.color = ok ? '#166534' : '#991b1b';
        syncAlert.style.border = ok ? '1px solid #86efac' : '1px solid #fca5a5';
        syncAlert.innerHTML = ok && sheetUrl
            ? `${message} <a href="${sheetUrl}" target="_blank" rel="noopener" style="font-weight:700;color:inherit;text-decoration:underline">Buka Spreadsheet</a>`
            : message;
    }

    if (syncBtn) {
        const defaultBtnHtml = syncBtn.innerHTML;
        syncBtn.addEventListener('click', async () => {
            const exportUrl = syncBtn.dataset.exportUrl;
            if (!exportUrl) return;

            syncBtn.disabled = true;
            syncBtn.innerHTML = 'Menyinkronkan...';
            showSyncAlert('success', 'Proses sinkronisasi sedang berjalan...');

            try {
                const res = await fetch(exportUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                let data = null;
                try { data = await res.json(); } catch (_) {}

                if (!res.ok || !data?.success) {
                    const errMsg = data?.message || `Sinkronisasi gagal (HTTP ${res.status}).`;
                    showSyncAlert('error', errMsg);
                    return;
                }

                showSyncAlert('success', data.message || 'Sinkronisasi berhasil.', data.sheet_url || null);
            } catch (error) {
                showSyncAlert('error', `Sinkronisasi gagal: ${error.message}`);
            } finally {
                syncBtn.disabled = false;
                syncBtn.innerHTML = defaultBtnHtml;
            }
        });
    }

    /* ================================================================
       INITIAL PAGINATION RENDER (from server-provided meta)
    ================================================================ */
    buildPagination(
        document.getElementById('db-pagination'),
        INIT_META.db,
        p => { dbPage = p; fetchDb(true); }
    );
    buildPagination(
        document.getElementById('foto-pagination'),
        INIT_META.foto,
        p => { fotoPage = p; fetchFoto(true); }
    );
    buildPagination(
        document.getElementById('pdf-pagination'),
        INIT_META.pdf,
        p => { pdfPage = p; fetchPdf(true); }
    );

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
        // Rebuild charts so axis/grid colours match the new theme
        setTimeout(_buildCharts, 60);
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
