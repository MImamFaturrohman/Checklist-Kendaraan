@php
    $fmtRp = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
    $fmtLiter = fn ($n) => number_format((float) $n, 3, ',', '.');
    $fmtKm = fn ($n) => number_format((int) round((float) $n), 0, ',', '.');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Log BBM — {{ config('app.name') }}</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
</head>
<body class="dash-body">
    @include('partials.premium-dash-bg', ['premiumBgId' => 'bbm_operational'])

    @include('admin.partials.dash-admin-nav', [
        'pageTitle' => 'Log BBM',
        'pageSubtitle' => ($bbmPortalChartsOnly ?? false)
            ? 'Ringkasan & grafik pengisian BBM (akses terbatas)'
            : 'PT ARTHA DAYA COALINDO',
        'navChipLabel' => ($bbmPortalChartsOnly ?? false) ? 'MANAGER' : 'SUPERADMIN',
        'navChipClass' => ($bbmPortalChartsOnly ?? false) ? 'dash-chip-manager' : 'dash-chip-admin',
    ])

    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">
            <p class="bbm-portal-month-kicker">
                Data bulan: <span class="bbm-portal-month-kicker__highlight">{{ $stats['month_label'] }}</span>
            </p>

            <div class="portal-stats-row portal-stats-row--bbm">
                <div class="portal-stat-card" style="--accent:#002a7a">
                    <div class="portal-stat-icon" style="background:rgba(0,42,122,.1);color:#002a7a"><i class="bi bi-clipboard-data"></i></div>
                    <div><div class="portal-stat-value">{{ $stats['total_reports_all'] }}</div><div class="portal-stat-label">Total Laporan BBM (keseluruhan)</div></div>
                </div>
                <div class="portal-stat-card" style="--accent:#0d9488">
                    <div class="portal-stat-icon" style="background:rgba(13,148,136,.1);color:#0d9488"><i class="bi bi-calendar-week"></i></div>
                    <div><div class="portal-stat-value">{{ $stats['month_reports'] }}</div><div class="portal-stat-label">Total Laporan BBM (bulanan)</div></div>
                </div>
                <div class="portal-stat-card" style="--accent:#d97706">
                    <div class="portal-stat-icon" style="background:rgba(217,119,6,.1);color:#d97706"><i class="bi bi-droplet-half"></i></div>
                    <div><div class="portal-stat-value" style="font-size: 0.89rem;">{{ $fmtLiter($stats['month_liter']) }} L</div><div class="portal-stat-label">Total Liter (bulanan)</div></div>
                </div>
                <div class="portal-stat-card" style="--accent:#16a34a">
                    <div class="portal-stat-icon" style="background:rgba(22,163,74,.1);color:#16a34a"><i class="bi bi-currency-exchange"></i></div>
                    <div><div class="portal-stat-value" style="font-size: 0.89rem;">{{ $fmtRp($stats['month_rupiah']) }}</div><div class="portal-stat-label">Total Biaya BBM (bulanan)</div></div>
                </div>
                <div class="portal-stat-card" style="--accent:#dc2626">
                    <div class="portal-stat-icon" style="background:rgba(220,38,38,.1);color:#dc2626"><i class="bi bi-arrow-up-circle"></i></div>
                    <div>
                        <div class="portal-stat-value" style="font-size:1rem;line-height:1.3">
                            @if($stats['boros'])
                                <strong>{{ $stats['boros']->nomor_kendaraan }}</strong><br>
                                <span class="portal-stat-sublabel">{{ $fmtLiter($stats['boros']->liters) }} L · {{ $fmtRp($stats['boros']->rupiah) }}</span>
                            @else
                                —
                            @endif
                        </div>
                        <div class="portal-stat-label">Kendaraan paling boros (Jumlah liter tertinggi, bulan ini)</div>
                    </div>
                </div>
                <div class="portal-stat-card" style="--accent:#2563eb">
                    <div class="portal-stat-icon" style="background:rgba(37,99,235,.1);color:#2563eb"><i class="bi bi-arrow-down-circle"></i></div>
                    <div>
                        <div class="portal-stat-value" style="font-size:1rem;line-height:1.3">
                            @if($stats['efisien'])
                                <strong>{{ $stats['efisien']->nomor_kendaraan }}</strong><br>
                                <span class="portal-stat-sublabel">{{ $fmtLiter($stats['efisien']->liters) }} L · {{ $fmtRp($stats['efisien']->rupiah) }}</span>
                            @else
                                —
                            @endif
                        </div>
                        <div class="portal-stat-label">Kendaraan paling efisien (Jumlah liter terendah, bulan ini)</div>
                    </div>
                </div>
            </div>

            <div class="bbm-chart-global-filters portal-local-filters" id="bbm-chart-global-filters">
                <div class="ppm-status-wrap">
                    <label class="bbm-filter-inline-label" for="bbm-chart-year">Tahun</label>
                    <select id="bbm-chart-year" class="admin-filter-input" aria-label="Tahun perbandingan">
                        @foreach($yearsAvailable as $y)
                            <option value="{{ $y }}" @selected((int) ($bbmDefaultChartYear ?? now()->year) === (int) $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ppm-status-wrap bbm-chart-vehicle-wrap">
                    <label class="bbm-filter-inline-label" for="bbm-chart-vehicle">Kendaraan</label>
                    <select id="bbm-chart-vehicle" class="admin-filter-input" aria-label="Filter kendaraan di grafik">
                        <option value="">Semua kendaraan</option>
                        @foreach($bbmVehicleNopolList ?? [] as $nopol)
                            <option value="{{ $nopol }}">{{ $nopol }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="bbm-chart-filters-hint">Membandingkan <strong id="bbm-chart-year-label">{{ $bbmDefaultChartYear }}</strong> dengan tahun sebelumnya (<span id="bbm-chart-prev-year-label">{{ (int) ($bbmDefaultChartYear ?? now()->year) - 1 }}</span>). Data diperbarui otomatis.</p>
            </div>

            <div class="portal-charts-grid portal-charts-grid--bbm">
                <div class="portal-chart-card portal-chart-card--wide">
                    <div class="portal-chart-title-row">
                        <div class="portal-chart-title">Pengeluaran BBM per bulan (Jan–Des)</div>
                    </div>
                    <div class="portal-chart-container" style="height:260px"><canvas id="bbmChartRupiahYear"></canvas></div>
                </div>
                <div class="portal-chart-card portal-chart-card--wide">
                    <div class="portal-chart-title">Total liter BBM per bulan (Jan–Des)</div>
                    <div class="portal-chart-container" style="height:280px"><canvas id="bbmChartLiterMonthly"></canvas></div>
                </div>
                <div class="portal-chart-card portal-chart-card--bbm-driver-col">
                    <div class="portal-chart-title">Top driver — frekuensi pengisian (bulan berjalan)</div>
                    <div class="portal-chart-container portal-chart-container--bbm-driver-pie"><canvas id="bbmChartDriverFreq"></canvas></div>
                </div>
                <div class="portal-chart-card portal-chart-card--bbm-log-col bbm-activity-log-card" id="bbm-activity-log-card">
                    <div class="bbm-activity-log-head">
                        <div class="bbm-activity-log-title">Log Pengisian BBM <span class="bbm-activity-live" title="Memperbarui otomatis">· real-time</span></div>
                        @unless($bbmPortalChartsOnly ?? false)
                            <a href="#section-bbm-table" onClick="smoothTo('section-bbm-table', event)" class="bbm-activity-log-all">Lihat Semua</a>
                        @else
                            <span class="bbm-activity-log-all bbm-activity-log-all--disabled" title="Akses tabel penuh pada akun admin">Lihat Semua</span>
                        @endunless
                    </div>
                    <div class="bbm-activity-log-scroll" id="bbm-activity-log-root" role="list" aria-live="polite" aria-busy="false">
                        <p class="bbm-activity-placeholder">Memuat log…</p>
                    </div>
                </div>
            </div>

            @unless($bbmPortalChartsOnly ?? false)
            <div id="bbm-portal-live-root" data-vms-bbm-portal-live>
                @fragment('bbm-portal-table-body')
                @php
                    $fmtRp = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
                    $fmtLiter = fn ($n) => number_format((float) $n, 3, ',', '.');
                    $fmtKm = fn ($n) => number_format((int) round((float) $n), 0, ',', '.');
                @endphp
                <div class="portal-section" id="section-bbm-table">
                    <div class="portal-section-header">
                        <div class="portal-section-title"><i class="bi bi-table"></i> Data Laporan BBM</div>
                    </div>
                    <form method="get" action="{{ route('admin.portal-bbm-operasional') }}" class="portal-local-filters ppm-daftar-filters bbm-portal-live-filter-bar" id="bbm-portal-filter-form">
                        <div class="admin-search-wrap portal-search-full">
                            <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            <input type="search" name="q" value="{{ $bbmPortalSearch ?? request('q') }}" placeholder="Cari nopol, jenis, nama pengemudi…" class="admin-search-input" autocomplete="off" aria-label="Cari laporan BBM">
                        </div>
                        <div class="ppm-status-wrap">
                            <label class="sr-only" for="bbm-portal-shift">Filter shift</label>
                            <select name="shift" id="bbm-portal-shift" class="admin-filter-input" aria-label="Filter shift">
                                <option value="" @selected(($bbmPortalShift ?? '') === '')>Semua shift</option>
                                <option value="pagi" @selected(($bbmPortalShift ?? '') === 'pagi')>Pagi</option>
                                <option value="siang" @selected(($bbmPortalShift ?? '') === 'siang')>Siang</option>
                                <option value="luar" @selected(($bbmPortalShift ?? '') === 'luar')>Di Luar Shift</option>
                            </select>
                        </div>
                        <div class="ppm-status-wrap">
                            <label class="sr-only" for="bbm-portal-jenis-pengisian">Filter jenis pengisian BBM</label>
                            <select name="jenis_pengisian" id="bbm-portal-jenis-pengisian" class="admin-filter-input" aria-label="Filter jenis pengisian BBM">
                                <option value="" @selected(($bbmPortalJenisPengisian ?? '') === '')>Semua jenis</option>
                                <option value="Operasional" @selected(($bbmPortalJenisPengisian ?? '') === 'Operasional')>Operasional</option>
                                <option value="Perjalanan Dinas (SPPD)" @selected(($bbmPortalJenisPengisian ?? '') === 'Perjalanan Dinas (SPPD)')>Perjalanan Dinas (SPPD)</option>
                            </select>
                        </div>
                        <div class="ppm-status-wrap bbm-portal-date-range">
                            <label class="sr-only" for="bbm-portal-date-from">Tanggal mulai</label>
                            <input type="date" name="date_from" id="bbm-portal-date-from" class="admin-filter-input" value="{{ $bbmPortalDateFrom ?? '' }}" title="Dari tanggal" aria-label="Dari tanggal">
                            <label class="sr-only" for="bbm-portal-date-to">Tanggal akhir</label>
                            <input type="date" name="date_to" id="bbm-portal-date-to" class="admin-filter-input" value="{{ $bbmPortalDateTo ?? '' }}" title="Sampai tanggal" aria-label="Sampai tanggal">
                        </div>
                        <div class="portal-perpage-wrap sppd-per-page-wrap">
                            <span class="portal-perpage-label" id="bbm-portal-per-page-label">Per halaman</span>
                            <label class="sr-only" for="bbm-portal-per-page">Jumlah data per halaman</label>
                            <select name="per_page" id="bbm-portal-per-page" class="admin-filter-input sppd-per-page-select" aria-labelledby="bbm-portal-per-page-label">
                                @foreach([5, 10, 25, 50, 100] as $n)
                                    <option value="{{ $n }}" @selected(($reports->perPage() ?? 25) === $n)>{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ppm-status-wrap bbm-portal-filter-actions">
                            <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite ppm-filter-reset" data-bbm-portal-reset title="Hapus semua filter" aria-label="Hapus semua filter"><i class="bi bi-arrow-clockwise"></i></button>
                        </div>
                    </form>
                    <div class="admin-table-wrap admin-table-wrap--bbm-reports">
                        <table class="admin-table admin-table--bbm-reports">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Shift</th>
                                    <th>Jenis BBM</th>
                                    <th>Kendaraan</th>
                                    <th>Pengemudi</th>
                                    <th>Km Sebelum</th>
                                    <th>Km Sesudah</th>
                                    <th>Total KM</th>
                                    <th>Volume (L)</th>
                                    <th>Total Biaya</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $r)
                                    @php
                                        $waktuStr = is_string($r->waktu) ? substr($r->waktu, 0, 5) : optional($r->waktu)->format('H:i') ?? '—';
                                        $totalKm = max(0, (int) $r->odometer_sesudah - (int) $r->odometer_sebelum);
                                    @endphp
                                    <tr>
                                        <td>{{ ($reports->currentPage() - 1) * $reports->perPage() + $loop->iteration }}</td>
                                        <td>{{ $r->tanggal->format('d F Y') }}</td>
                                        <td>{{ $waktuStr }}</td>
                                        <td>
                                            <span class="bbm-shift-badge {{ \App\Support\DriverShift::badgeClassFromCode($r->shift) }}">
                                                {{ \App\Support\DriverShift::labelFromCode($r->shift) }}
                                            </span>
                                        </td>
                                        <td><span class="bbm-jenis-pengisian-cell">{{ $r->jenis_pengisian ?: 'Operasional' }}</span></td>
                                        <td><strong>{{ $r->nomor_kendaraan }}</strong><br><span class="sppd-cell-muted">{{ $r->jenis_kendaraan }}</span></td>
                                        <td>{{ $r->user?->name ?? '—' }}<br><span class="sppd-cell-muted">{{ $r->user?->username }}</span></td>
                                        <td>{{ $fmtKm($r->odometer_sebelum) }}</td>
                                        <td>{{ $fmtKm($r->odometer_sesudah) }}</td>
                                        <td><strong>{{ $fmtKm($totalKm) }}</strong></td>
                                        <td>{{ $fmtLiter($r->liter) }}</td>
                                        <td><strong>{{ $fmtRp($r->total_harga) }}</strong></td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-sm sppd-icon-btn sppd-btn-primary bbm-btn-detail"
                                                data-json-url="{{ route('admin.portal-bbm-operasional.json', $r) }}"
                                                title="Detail lengkap &amp; foto"
                                                aria-label="Detail laporan BBM"
                                            ><i class="bi bi-eye-fill"></i> </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="13" class="portal-empty">Belum ada laporan BBM dari driver.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="sppd-pagination-scroll">
                        <div class="admin-pagination portal-pagination-wrap sppd-pagination--unified">{{ $reports->links() }}</div>
                    </div>
                </div>
                @endfragment
            </div>
            @endunless
        </div>
    </div>

    @unless($bbmPortalChartsOnly ?? false)
    {{-- Detail modal (pola mirip sppd/index) --}}
    <div id="bbm-modal-detail" class="modal-overlay" style="display:none">
        <div class="modal-box profile-card sppd-modal-box" style="max-width:min(720px,100%);text-align:left;max-height:86vh;overflow:auto">
            <h3>Detail Laporan BBM</h3>
            <div id="bbm-detail-body" class="sppd-detail-html"></div>
            <div class="ppm-modal-actions">
                <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite" data-close-bbm-modal title="Tutup" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
    </div>
    @endunless

    <style>
        .bbm-chart-global-filters.portal-local-filters {
            align-items: flex-end;
            gap: 12px 16px;
            margin-top: 18px;
            margin-bottom: 12px;
        }
        .bbm-filter-inline-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            margin-bottom: 4px;
        }
        .dash-body.dark .bbm-filter-inline-label { color: rgba(200, 218, 255, 0.55); }
        .bbm-chart-vehicle-wrap { min-width: min(100%, 220px); flex: 1 1 180px; }
        .bbm-chart-filters-hint {
            flex: 1 1 200px;
            margin: 0;
            font-size: 0.78rem;
            color: #64748b;
            line-height: 1.45;
        }
        .dash-body.dark .bbm-chart-filters-hint { color: rgba(200, 218, 255, 0.55); }
        .bbm-chart-filters-hint strong { color: #0f172a; font-weight: 700; }
        .dash-body.dark .bbm-chart-filters-hint strong { color: #e8f0fe; }

        .bbm-activity-log-card { padding: 16px 16px 12px; }
        .dash-body.dark .bbm-activity-log-card {
            background: linear-gradient(165deg, #0f172a 0%, #1e293b 55%, #172554 100%);
            border: 1px solid rgba(99, 102, 241, 0.25);
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.35);
        }
        .dash-body:not(.dark) .bbm-activity-log-card .portal-chart-title { color: #475569; }
        .bbm-activity-log-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }
        .bbm-activity-log-title {
            font-weight: 800;
            font-size: 1.05rem;
            letter-spacing: -0.02em;
            color: #0f172a;
        }
        .dash-body.dark .bbm-activity-log-title { color: #f8fafc; }
        .bbm-activity-live {
            font-weight: 600;
            font-size: 0.78rem;
            color: #0284c7;
            text-transform: none;
            letter-spacing: 0;
        }
        .dash-body.dark .bbm-activity-live { color: #38bdf8; }
        .bbm-activity-log-all {
            font-size: 0.82rem;
            font-weight: 600;
            color: #0369a1;
            text-decoration: none;
            white-space: nowrap;
        }
        .bbm-activity-log-all:hover { text-decoration: underline; color: #0c4a6e; }
        .dash-body.dark .bbm-activity-log-all { color: #7dd3fc; }
        .dash-body.dark .bbm-activity-log-all:hover { color: #bae6fd; }
        .bbm-activity-log-all--disabled {
            opacity: 0.55;
            cursor: default;
            pointer-events: none;
        }
        .bbm-activity-log-scroll {
            max-height: 320px;
            overflow-y: auto;
            padding-right: 6px;
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.5) transparent;
        }
        .bbm-activity-log-scroll::-webkit-scrollbar { width: 6px; }
        .bbm-activity-log-scroll::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.45);
            border-radius: 99px;
        }
        .bbm-activity-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 14px;
            margin-bottom: 8px;
            border-radius: 12px;
            background: rgba(15, 23, 42, 0.04);
            border: 1px solid rgba(148, 163, 184, 0.22);
            transition: background 0.15s ease, border-color 0.15s ease;
        }
        .dash-body.dark .bbm-activity-row {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(148, 163, 184, 0.12);
        }
        .bbm-activity-row:last-child { margin-bottom: 0; }
        .bbm-activity-row.is-clickable { cursor: pointer; }
        .bbm-activity-row.is-clickable:hover {
            background: rgba(2, 132, 199, 0.08);
            border-color: rgba(2, 132, 199, 0.28);
        }
        .dash-body.dark .bbm-activity-row.is-clickable:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(125, 211, 252, 0.35);
        }
        .bbm-activity-badge {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            background: linear-gradient(145deg, #1d4ed8 0%, #2563eb 100%);
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }
        .bbm-activity-main { flex: 1; min-width: 0; }
        .bbm-activity-nopol {
            font-weight: 800;
            font-size: 0.95rem;
            color: #0f172a;
            line-height: 1.25;
        }
        .dash-body.dark .bbm-activity-nopol { color: #f8fafc; }
        .bbm-activity-meta {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 3px;
        }
        .dash-body.dark .bbm-activity-meta { color: rgba(226, 232, 240, 0.62); }
        .bbm-activity-side { text-align: right; flex-shrink: 0; }
        .bbm-activity-liter {
            font-weight: 800;
            font-size: 0.95rem;
            color: #0f172a;
        }
        .dash-body.dark .bbm-activity-liter { color: #f8fafc; }
        .bbm-activity-rp {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 3px;
        }
        .dash-body.dark .bbm-activity-rp { color: rgba(226, 232, 240, 0.62); }
        .bbm-activity-placeholder {
            margin: 0;
            padding: 20px 8px;
            text-align: center;
            color: #64748b;
            font-size: 0.88rem;
        }
        .dash-body.dark .bbm-activity-placeholder { color: rgba(226, 232, 240, 0.55); }
        .portal-chart-title-row { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px; margin-bottom:6px; }
        .bbm-portal-date-range { display: flex; gap: 8px; flex-wrap: wrap; align-items: stretch; }
        .bbm-portal-date-range .admin-filter-input { min-width: 0; flex: 1 1 8rem; }
        .bbm-portal-filter-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .portal-stat-sublabel { font-size:0.78rem; font-weight:600; color:#64748b; }
        .dash-body.dark .portal-stat-sublabel { color:rgba(200,218,255,0.55); }
    </style>

    <script>
    (function () {
        const BBM_PORTAL_CHARTS_ONLY = @json($bbmPortalChartsOnly ?? false);
        const BBM_CHART_SERIES_URL = @json(route('admin.portal-bbm-operasional.charts'));
        const BBM_ACTIVITY_LOG_URL = @json(route('admin.portal-bbm-operasional.activity-log'));
        const TOP_DRIVERS_MONTH = @json($topDriversMonth);

        const MONTH_LABELS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        const CHART_POLL_MS = 66000;
        const LOG_POLL_MS = 28000;

        let chartRupiah, chartLiterMonthly, chartDrvFreq;
        let lastComparisonPayload = null;
        let chartPollTimer = null;
        let logPollTimer = null;

        function fmtRpShort(n) {
            const x = Number(n) || 0;
            if (x >= 1e9) return (x / 1e9).toFixed(1) + ' M';
            if (x >= 1e6) return (x / 1e6).toFixed(1) + ' jt';
            if (x >= 1e3) return (x / 1e3).toFixed(0) + ' rb';
            return String(Math.round(x));
        }

        const palette = ['#002a7a', '#16a34a', '#d97706', '#7c3aed', '#dc2626', '#0891b2', '#ca8a04', '#64748b'];

        function barFill(hex, alpha) {
            const m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            if (!m) return hex;
            return 'rgba(' + parseInt(m[1], 16) + ',' + parseInt(m[2], 16) + ',' + parseInt(m[3], 16) + ',' + alpha + ')';
        }

        function chartCommonSkin() {
            const dark = document.body.classList.contains('dark');
            return {
                dark,
                grid: dark ? 'rgba(200,218,255,0.1)' : 'rgba(0,0,0,0.08)',
                tick: dark ? 'rgba(200,218,255,0.65)' : '#64748b',
                bdr: dark ? 'rgba(200,218,255,0.12)' : 'rgba(255,255,255,0.8)',
                common: { responsive: true, maintainAspectRatio: false },
            };
        }

        function updateYearHint(y, yPrev) {
            const a = document.getElementById('bbm-chart-year-label');
            const b = document.getElementById('bbm-chart-prev-year-label');
            if (a) a.textContent = String(y);
            if (b) b.textContent = String(yPrev);
        }

        function renderComparisonCharts(data) {
            if (!data || !Array.isArray(data.month_labels)) return;
            try { chartRupiah?.destroy(); } catch (_) {}
            try { chartLiterMonthly?.destroy(); } catch (_) {}
            chartRupiah = chartLiterMonthly = null;

            const { dark, grid, tick, common } = chartCommonSkin();
            const fillA = dark ? 0.62 : 0.78;
            const colCur = palette[0];
            const colPrev = palette[1];
            const yCur = data.year;
            const yPrev = data.year_previous;
            const labels = data.month_labels.length ? data.month_labels : MONTH_LABELS;

            const elR = document.getElementById('bbmChartRupiahYear');
            if (elR) {
                chartRupiah = new Chart(elR, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: String(yCur),
                                data: (data.rupiah_current || []).map((v) => Math.round(Number(v) / 1000)),
                                backgroundColor: barFill(colCur, fillA),
                                borderColor: colCur,
                                borderWidth: 1,
                                borderRadius: 5,
                                borderSkipped: false,
                            },
                            {
                                label: String(yPrev),
                                data: (data.rupiah_previous || []).map((v) => Math.round(Number(v) / 1000)),
                                backgroundColor: barFill(colPrev, fillA),
                                borderColor: colPrev,
                                borderWidth: 1,
                                borderRadius: 5,
                                borderSkipped: false,
                            },
                        ],
                    },
                    options: {
                        ...common,
                        interaction: { mode: 'index', intersect: false },
                        datasets: { bar: { maxBarThickness: 26 } },
                        plugins: {
                            legend: { display: true, position: 'top', labels: { color: tick, boxWidth: 12 } },
                            tooltip: {
                                callbacks: {
                                    label(ctx) {
                                        const arr = ctx.datasetIndex === 0 ? data.rupiah_current : data.rupiah_previous;
                                        const raw = (arr || [])[ctx.dataIndex] || 0;
                                        return ' ' + ctx.dataset.label + ': Rp ' + Number(raw).toLocaleString('id-ID');
                                    },
                                },
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Rp (÷1000)', color: tick },
                                ticks: { color: tick, callback: (v) => fmtRpShort(v * 1000) },
                                grid: { color: grid },
                            },
                            x: { ticks: { color: tick, font: { size: 11 } }, grid: { color: grid } },
                        },
                    },
                });
            }

            const elL = document.getElementById('bbmChartLiterMonthly');
            if (elL) {
                chartLiterMonthly = new Chart(elL, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: String(yCur) + ' (L)',
                                data: (data.liter_current || []).map((v) => Number(v)),
                                backgroundColor: barFill(colCur, fillA),
                                borderColor: colCur,
                                borderWidth: 1,
                                borderRadius: 5,
                                borderSkipped: false,
                            },
                            {
                                label: String(yPrev) + ' (L)',
                                data: (data.liter_previous || []).map((v) => Number(v)),
                                backgroundColor: barFill(colPrev, fillA),
                                borderColor: colPrev,
                                borderWidth: 1,
                                borderRadius: 5,
                                borderSkipped: false,
                            },
                        ],
                    },
                    options: {
                        ...common,
                        interaction: { mode: 'index', intersect: false },
                        datasets: { bar: { maxBarThickness: 26 } },
                        plugins: {
                            legend: { display: true, position: 'top', labels: { color: tick, boxWidth: 12 } },
                            tooltip: {
                                callbacks: {
                                    label(ctx) {
                                        return ' ' + ctx.dataset.label + ': ' + Number(ctx.raw).toLocaleString('id-ID', { maximumFractionDigits: 3 }) + ' L';
                                    },
                                },
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Liter', color: tick },
                                ticks: { color: tick },
                                grid: { color: grid },
                            },
                            x: { ticks: { color: tick, font: { size: 11 } }, grid: { color: grid } },
                        },
                    },
                });
            }
        }

        async function fetchComparisonCharts() {
            const yearEl = document.getElementById('bbm-chart-year');
            const vehEl = document.getElementById('bbm-chart-vehicle');
            if (!yearEl) return;
            const year = parseInt(yearEl.value, 10);
            if (Number.isNaN(year)) return;
            const nopol = vehEl && vehEl.value ? String(vehEl.value) : '';
            const u = new URL(BBM_CHART_SERIES_URL, window.location.origin);
            u.searchParams.set('year', String(year));
            if (nopol) u.searchParams.set('nomor_kendaraan', nopol);
            try {
                const res = await fetch(u.toString(), { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                if (!res.ok) return;
                const data = await res.json();
                lastComparisonPayload = data;
                renderComparisonCharts(data);
                updateYearHint(data.year, data.year_previous);
            } catch (_) {}
        }

        function buildDriverPieChart() {
            try { chartDrvFreq?.destroy(); } catch (_) {}
            chartDrvFreq = null;
            const { tick, bdr, common } = chartCommonSkin();
            const elD = document.getElementById('bbmChartDriverFreq');
            if (!elD || !TOP_DRIVERS_MONTH.length) return;
            const dark = document.body.classList.contains('dark');
            const pieFill = dark ? 0.88 : 0.92;
            const drvLabels = TOP_DRIVERS_MONTH.map((d) => d.name || d.username || 'Driver');
            const drvData = TOP_DRIVERS_MONTH.map((d) => Number(d.cnt));
            const narrow = typeof window !== 'undefined' && window.innerWidth <= 640;
            chartDrvFreq = new Chart(elD, {
                type: 'pie',
                data: {
                    labels: drvLabels,
                    datasets: [{
                        data: drvData,
                        backgroundColor: TOP_DRIVERS_MONTH.map((_, i) => barFill(palette[i % palette.length], pieFill)),
                        borderColor: bdr,
                        borderWidth: 2,
                        hoverOffset: 6,
                    }],
                },
                options: {
                    ...common,
                    layout: {
                        padding: narrow ? { left: 6, right: 6, top: 4, bottom: 4 } : { left: 4, right: 8, top: 4, bottom: 4 },
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: narrow ? 'bottom' : 'right',
                            align: narrow ? 'center' : 'center',
                            labels: {
                                color: tick,
                                boxWidth: narrow ? 10 : 12,
                                padding: narrow ? 8 : 10,
                                font: { size: narrow ? 10 : 11 },
                            },
                        },
                        tooltip: {
                            callbacks: {
                                label(ctx) {
                                    const v = Number(ctx.raw) || 0;
                                    const total = drvData.reduce((a, b) => a + Number(b), 0);
                                    const pct = total ? ((v / total) * 100).toFixed(1) : '0';
                                    return ' ' + v + ' kali isi BBM (' + pct + '%)';
                                },
                            },
                        },
                    },
                },
            });
        }

        function esc(s) {
            const d = document.createElement('div');
            d.textContent = s ?? '';
            return d.innerHTML;
        }
        function formatRp(n) {
            const x = Number(n) || 0;
            return 'Rp ' + x.toLocaleString('id-ID');
        }

        function renderActivityLog(items) {
            const root = document.getElementById('bbm-activity-log-root');
            if (!root) return;
            root.setAttribute('aria-busy', 'false');
            if (!items || !items.length) {
                root.innerHTML = '<p class="bbm-activity-placeholder">Belum ada pengisian BBM.</p>';
                return;
            }
            root.innerHTML = items.map((it) => {
                const liter = Number(it.liter || 0).toLocaleString('id-ID', { maximumFractionDigits: 3 });
                const rp = formatRp(it.total_harga);
                const clickable = it.detail_json_url ? ' is-clickable' : '';
                const dataUrl = it.detail_json_url ? ` data-json-url="${String(it.detail_json_url).replace(/"/g, '&quot;')}"` : '';
                return `
                <div class="bbm-activity-row${clickable}" role="listitem"${dataUrl}>
                    <div class="bbm-activity-badge">${esc(it.badge)}</div>
                    <div class="bbm-activity-main">
                        <div class="bbm-activity-nopol">${esc(it.nomor_kendaraan)}</div>
                        <div class="bbm-activity-meta">${esc(it.driver_name)} · ${esc(it.waktu_label)} · ${esc(it.tanggal_label)}</div>
                    </div>
                    <div class="bbm-activity-side">
                        <div class="bbm-activity-liter">${liter} L</div>
                        <div class="bbm-activity-rp">${rp}</div>
                    </div>
                </div>`;
            }).join('');
        }

        async function fetchActivityLog() {
            const root = document.getElementById('bbm-activity-log-root');
            if (!root) return;
            root.setAttribute('aria-busy', 'true');
            const u = new URL(BBM_ACTIVITY_LOG_URL, window.location.origin);
            u.searchParams.set('limit', '22');
            try {
                const res = await fetch(u.toString(), { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                if (!res.ok) throw new Error('log');
                const j = await res.json();
                renderActivityLog(j.items || []);
            } catch (_) {
                const r = document.getElementById('bbm-activity-log-root');
                if (r) {
                    r.setAttribute('aria-busy', 'false');
                    r.innerHTML = '<p class="bbm-activity-placeholder">Gagal memuat log.</p>';
                }
            }
        }

        function redrawComparisonFromCache() {
            if (lastComparisonPayload) renderComparisonCharts(lastComparisonPayload);
            else fetchComparisonCharts();
        }

        function renderBbmDetail(d) {
            const badgeCls = (d.shift_badge_class && String(d.shift_badge_class).replace(/[^a-z0-9_-]/gi, '')) || 'bbm-shift-luar';
            const shiftHtml = `<span class="bbm-shift-badge ${esc(badgeCls)}">${esc(d.shift_label || '—')}</span>`;
            const odo = d.odometer_photo_url
                ? `<a href="${String(d.odometer_photo_url).replace(/"/g, '&quot;')}" target="_blank" rel="noopener"><img src="${String(d.odometer_photo_url).replace(/"/g, '&quot;')}" class="sppd-photo-thumb" alt="Odometer"></a>`
                : '<p class="portal-empty" style="padding:8px">—</p>';
            const struk = d.struk_photo_url
                ? `<a href="${String(d.struk_photo_url).replace(/"/g, '&quot;')}" target="_blank" rel="noopener"><img src="${String(d.struk_photo_url).replace(/"/g, '&quot;')}" class="sppd-photo-thumb" alt="Struk"></a>`
                : '<p class="portal-empty" style="padding:8px">—</p>';
            return `
                <table class="info-table sppd-mini-table">
                    <tr><td class="label">Driver</td><td>${esc(d.driver_name)} (${esc(d.driver_username || '—')})</td></tr>
                    <tr><td class="label">Kendaraan</td><td>${esc(d.nomor_kendaraan)} — ${esc(d.jenis_kendaraan)}</td></tr>
                    <tr><td class="label">Jenis pengisian BBM</td><td>${esc(d.jenis_pengisian ?? '—')}</td></tr>
                    <tr><td class="label">Tanggal</td><td>${esc(d.tanggal)}</td></tr>
                    <tr><td class="label">Waktu</td><td>${esc(d.waktu)}</td></tr>
                    <tr><td class="label">Shift</td><td>${shiftHtml}</td></tr>
                    <tr><td class="label">KM sebelum</td><td>${esc(d.odometer_sebelum)}</td></tr>
                    <tr><td class="label">KM sesudah</td><td>${esc(d.odometer_sesudah)}</td></tr>
                    <tr><td class="label">Total KM</td><td><strong>${esc(String(d.total_km ?? '—'))}</strong></td></tr>
                    <tr><td class="label">Volume (Liter)</td><td>${esc(String(d.liter))}</td></tr>
                    <tr><td class="label">Harga / L</td><td>${formatRp(d.harga_per_liter)}</td></tr>
                    <tr><td class="label">Total biaya</td><td><strong>${formatRp(d.total_harga)}</strong></td></tr>
                </table>
                <p class="sppd-detail-sub">Foto odometer</p>
                <div class="sppd-photo-grid">${odo}</div>
                <p class="sppd-detail-sub">Foto struk</p>
                <div class="sppd-photo-grid">${struk}</div>
            `;
        }

        document.querySelector('.admin-shell')?.addEventListener('click', async (e) => {
            const act = e.target.closest('.bbm-activity-row[data-json-url]');
            if (act) {
                if (!document.getElementById('bbm-modal-detail')) return;
                const url = act.getAttribute('data-json-url');
                const modal = document.getElementById('bbm-modal-detail');
                const bodyEl = document.getElementById('bbm-detail-body');
                bodyEl.innerHTML = '<p>Memuat…</p>';
                modal.style.display = 'flex';
                try {
                    const res = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const j = await res.json();
                    if (!j.report) throw new Error('Invalid payload');
                    bodyEl.innerHTML = renderBbmDetail(j.report);
                } catch (err) {
                    bodyEl.innerHTML = '<p>Gagal memuat data.</p>';
                }
                return;
            }
            if (BBM_PORTAL_CHARTS_ONLY) return;
            const btn = e.target.closest('.bbm-btn-detail');
            if (!btn) return;
            const url = btn.getAttribute('data-json-url');
            const modal = document.getElementById('bbm-modal-detail');
            const bodyEl = document.getElementById('bbm-detail-body');
            bodyEl.innerHTML = '<p>Memuat…</p>';
            modal.style.display = 'flex';
            try {
                const res = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const j = await res.json();
                if (!j.report) throw new Error('Invalid payload');
                bodyEl.innerHTML = renderBbmDetail(j.report);
            } catch (err) {
                bodyEl.innerHTML = '<p>Gagal memuat data.</p>';
            }
        });
        document.querySelectorAll('[data-close-bbm-modal]').forEach((el) => {
            el.addEventListener('click', () => {
                const m = document.getElementById('bbm-modal-detail');
                if (m) m.style.display = 'none';
            });
        });
        document.getElementById('bbm-modal-detail')?.addEventListener('click', (e) => {
            if (e.target.id === 'bbm-modal-detail') e.currentTarget.style.display = 'none';
        });

        const body = document.body;
        const themeBtn = document.getElementById('dash-theme-toggle');
        const themeIcon = document.getElementById('dash-theme-icon');
        const themeLabel = document.getElementById('dash-theme-label');
        const navActions = document.getElementById('dash-nav-actions');
        const menuBtn = document.getElementById('dash-mobile-menu-btn');
        const menuIcon = document.getElementById('dash-mobile-menu-icon');

        const applyTheme = (isDark) => {
            body.classList.toggle('dark', isDark);
            if (themeIcon) themeIcon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            if (themeLabel) themeLabel.textContent = isDark ? 'Light Mode' : 'Dark Mode';
            buildDriverPieChart();
            redrawComparisonFromCache();
        };
        const saved = localStorage.getItem('vms-theme') || localStorage.getItem('vms-dash-theme');
        themeBtn?.addEventListener('click', () => {
            const next = !body.classList.contains('dark');
            applyTheme(next);
            localStorage.setItem('vms-theme', next ? 'dark' : 'light');
            localStorage.setItem('vms-dash-theme', next ? 'dark' : 'light');
        });
        applyTheme(saved === 'dark');

        document.getElementById('bbm-chart-year')?.addEventListener('change', () => { fetchComparisonCharts(); });
        document.getElementById('bbm-chart-vehicle')?.addEventListener('change', () => { fetchComparisonCharts(); });

        buildDriverPieChart();
        fetchComparisonCharts();
        fetchActivityLog();
        chartPollTimer = setInterval(fetchComparisonCharts, CHART_POLL_MS);
        logPollTimer = setInterval(fetchActivityLog, LOG_POLL_MS);

        let bbmPieResizeTimer = null;
        window.addEventListener('resize', () => {
            clearTimeout(bbmPieResizeTimer);
            bbmPieResizeTimer = setTimeout(() => buildDriverPieChart(), 200);
        });

        const closeMobileMenu = () => {
            navActions?.classList.remove('mobile-open');
            if (menuIcon) menuIcon.className = 'bi bi-list';
            menuBtn?.setAttribute('aria-expanded', 'false');
        };
        menuBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            const opened = navActions?.classList.toggle('mobile-open');
            if (menuIcon) menuIcon.className = opened ? 'bi bi-x-lg' : 'bi bi-list';
            menuBtn?.setAttribute('aria-expanded', String(!!opened));
        });
        document.addEventListener('click', (e) => {
            if (!navActions?.contains(e.target) && !menuBtn?.contains(e.target)) closeMobileMenu();
        });
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) closeMobileMenu();
        });
    })();

    /* ── SMOOTH SCROLL ── */
    function smoothTo(id, e) {
        e.preventDefault();
        const el = document.getElementById(id);
        if (el) el.scrollIntoView({behavior: 'smooth', block: 'start'});
    }
    </script>
</body>
</html>
