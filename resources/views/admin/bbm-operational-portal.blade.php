@php
    $fmtRp = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
    $fmtLiter = fn ($n) => number_format((float) $n, 3, ',', '.');
    $fmtKm = fn ($n) => number_format((int) round((float) $n), 0, ',', '.');
@endphp

@extends('layouts.dash-app')

@section('title', 'Log BBM')
@section('pageTitle', 'Log BBM')
@section('pageSubtitle', ($bbmPortalChartsOnly ?? false) ? 'Ringkasan & grafik pengisian BBM' : 'PT ARTHA DAYA COALINDO')

@php $premiumBgId = 'bbm_operational'; @endphp

@push('head')
<meta name="turbo-cache-control" content="no-cache">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@endpush

@push('styles')
    <style>
        .bbm-chart-title-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px 16px;
            margin-bottom: 8px;
        }
        .bbm-chart-title-row .portal-chart-title {
            flex: 1 1 200px;
            min-width: 0;
            margin-bottom: 0;
        }
        .bbm-chart-inline-filters {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            flex: 0 0 auto;
            margin-left: auto;
        }
        .bbm-chart-inline-filters .ppm-status-wrap {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 8px;
            min-width: 0;
            flex: 0 0 auto;
        }
        .bbm-chart-inline-filters .bbm-chart-year-wrap {
            flex: 0 0 auto;
        }
        .bbm-chart-inline-filters .bbm-chart-vehicle-wrap {
            flex: 0 1 auto;
            min-width: 140px;
            max-width: 200px;
        }
        .bbm-chart-inline-filters .admin-filter-input {
            width: auto;
            min-width: 0;
            flex: 1 1 auto;
        }
        .bbm-chart-inline-filters .bbm-chart-year-wrap .admin-filter-input {
            width: 5.5rem;
            flex: 0 0 5.5rem;
        }
        .portal-chart-container--bbm-combined {
            height: 300px;
        }
        @media (max-width: 640px) {
            .bbm-chart-title-row {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }
            .bbm-chart-title-row .portal-chart-title {
                flex: 0 0 auto;
            }
            .bbm-chart-inline-filters {
                justify-content: flex-start;
                margin-left: 0;
                flex-wrap: wrap;
                width: 100%;
            }
            .bbm-chart-inline-filters .bbm-chart-vehicle-wrap {
                min-width: 0;
                max-width: none;
                flex: 1 1 auto;
            }
            .portal-chart-container--bbm-combined {
                height: 280px;
            }
            .portal-charts-grid--bbm .portal-chart-card {
                min-width: 0;
                overflow: hidden;
            }
            .portal-chart-container--bbm-driver-pie {
                height: 320px;
            }
        }
        .bbm-filter-inline-label {
            display: inline;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            margin-bottom: 0;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .dash-body.dark .bbm-filter-inline-label { color: rgba(200, 218, 255, 0.55); }
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
        .bbm-portal-date-range { display: flex; gap: 8px; flex-wrap: wrap; align-items: stretch; }
        .bbm-portal-date-range .admin-filter-input { min-width: 0; flex: 1 1 8rem; }
        .bbm-portal-filter-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .portal-stat-sublabel { font-size:0.78rem; font-weight:600; color:#64748b; }
        .dash-body.dark .portal-stat-sublabel { color:rgba(200,218,255,0.55); }

        .bbm-portal-stat-mom {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .bbm-portal-stat-mom-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 9px;
            border-radius: 8px;
            font-size: 0.74rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: 0.01em;
        }
        .bbm-portal-stat-mom-badge i { font-size: 0.78rem; }
        .bbm-portal-stat-mom-badge--up {
            background: rgba(248, 113, 113, 0.16);
            color: #dc2626;
            border: 1px solid rgba(248, 113, 113, 0.45);
        }
        .bbm-portal-stat-mom-badge--down {
            background: rgba(34, 197, 94, 0.16);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.42);
        }
        .bbm-portal-stat-mom-badge--flat {
            background: rgba(148, 163, 184, 0.22);
            color: #64748b;
            border: 1px solid rgba(148, 163, 184, 0.5);
            font-weight: 700;
        }
        .bbm-portal-stat-mom-vs {
            font-size: 0.72rem;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: 0.02em;
        }
        .dash-body.dark .bbm-portal-stat-mom-vs { color: rgba(200, 218, 255, 0.45); }
        .dash-body.dark .bbm-portal-stat-mom-badge--up {
            background: rgba(220, 38, 38, 0.2);
            color: #fecaca;
            border-color: rgba(248, 113, 113, 0.35);
        }
        .dash-body.dark .bbm-portal-stat-mom-badge--down {
            background: rgba(22, 163, 74, 0.2);
            color: #bbf7d0;
            border-color: rgba(34, 197, 94, 0.38);
        }
        .dash-body.dark .bbm-portal-stat-mom-badge--flat {
            background: rgba(148, 163, 184, 0.12);
            color: rgba(226, 232, 240, 0.8);
            border-color: rgba(148, 163, 184, 0.25);
        }

        .bbm-detail-photos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 12px;
        }
        .bbm-detail-photos figure {
            margin: 0;
        }
        .bbm-detail-photos figcaption {
            font-size: 0.78rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .dash-body.dark .bbm-detail-photos figcaption { color: rgba(200, 218, 255, 0.55); }
        .bbm-photo-thumb-btn {
            display: block;
            width: 100%;
            padding: 0;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 8px;
            overflow: hidden;
        }
        .bbm-photo-thumb-btn:focus-visible {
            outline: 2px solid #002a7a;
            outline-offset: 2px;
        }
        .bbm-photo-thumb-btn img {
            display: block;
            width: 100%;
            height: auto;
            max-height: 180px;
            object-fit: cover;
            border-radius: 8px;
            transition: transform .2s, box-shadow .2s;
        }
        .bbm-photo-thumb-btn img.bbm-photo-thumb--odometer-grid {
            max-height: 420px;
            object-fit: contain;
        }
        .bbm-photo-thumb-btn:hover img {
            transform: scale(1.02);
            box-shadow: 0 4px 14px rgba(0,0,0,.15);
        }
        @media (max-width: 560px) {
            .bbm-detail-photos { grid-template-columns: 1fr; }
        }

        .bbm-photo-lightbox {
            position: fixed;
            inset: 0;
            z-index: 10050;
            background: rgba(15, 23, 42, 0.92);
            display: flex;
            flex-direction: column;
            padding: 16px;
            box-sizing: border-box;
            pointer-events: auto;
        }
        .bbm-photo-lightbox[hidden] {
            display: none !important;
        }
        .bbm-photo-lightbox-viewport {
            flex: 1;
            min-height: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: grab;
            touch-action: none;
            position: relative;
        }
        .bbm-photo-lightbox-viewport.is-dragging {
            cursor: grabbing;
        }
        #bbm-photo-lightbox-img {
            max-width: none;
            max-height: none;
            transform-origin: center center;
            will-change: transform;
            border-radius: 6px;
            box-shadow: 0 8px 32px rgba(0,0,0,.45);
            user-select: none;
            -webkit-user-drag: none;
        }
        .bbm-photo-lightbox-toolbar {
            position: absolute;
            left: 16px;
            bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 2;
        }
        .bbm-photo-lightbox-toolbar button {
            min-width: 40px;
            height: 40px;
            padding: 0 10px;
            border: none;
            border-radius: 8px;
            background: rgba(255,255,255,0.15);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            line-height: 1;
        }
        .bbm-photo-lightbox-toolbar button:hover {
            background: rgba(255,255,255,0.25);
        }
        .bbm-photo-lightbox-toolbar button[data-bbm-zoom="reset"] {
            min-width: 56px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .bbm-photo-lightbox-close {
            position: absolute;
            top: 16px;
            right: 16px;
            z-index: 10;
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            color: #fff;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: auto;
        }
        .bbm-photo-lightbox-close:hover { background: rgba(255,255,255,0.25); }
    </style>
@endpush

@section('content')
    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">
            <div class="portal-stats-row portal-stats-row--bbm">
                <x-admin-stat-card
                    title="Total Laporan BBM"
                    :value="$stats['total_reports_all']"
                    unit="laporan"
                    description="Akumulasi seluruh waktu"
                    icon="bi bi-clipboard-data-fill"
                />
                <x-admin-stat-card
                    title="Laporan BBM Tahunan"
                    :value="$stats['year_reports']"
                    unit="laporan"
                    description="Laporan pengisian tahun {{ $stats['year_label'] }}"
                    icon="bi bi-calendar-week-fill"
                >
                    @if(!empty($stats['yoy_year_reports']['show']))
                        @php
                            $ydir = $stats['yoy_year_reports']['direction'] ?? 'flat';
                            $ypct = $stats['yoy_year_reports']['pct_display'] ?? '';
                        @endphp
                        <div class="bbm-portal-stat-mom" role="presentation">
                            <span class="bbm-portal-stat-mom-badge bbm-portal-stat-mom-badge--{{ $ydir }}">
                                @if ($ydir === 'up')
                                    <i class="bi bi-graph-up-arrow"></i>
                                @elseif ($ydir === 'down')
                                    <i class="bi bi-graph-down-arrow"></i>
                                @else
                                    <i class="bi bi-dash-lg"></i>
                                @endif
                                {{ $ypct }}
                            </span>
                            <span class="bbm-portal-stat-mom-vs">vs Tahun Lalu</span>
                        </div>
                    @endif
                </x-admin-stat-card>
                <x-admin-stat-card
                    title="Total Liter"
                    :value="$fmtLiter($stats['year_liter'])"
                    unit="L"
                    description="Volume BBM tahun berjalan"
                    icon="bi bi-droplet-fill"
                >
                    @if(!empty($stats['yoy_year_liter']['show']))
                        @php
                            $ldir = $stats['yoy_year_liter']['direction'] ?? 'flat';
                            $lpct = $stats['yoy_year_liter']['pct_display'] ?? '';
                        @endphp
                        <div class="bbm-portal-stat-mom" role="presentation">
                            <span class="bbm-portal-stat-mom-badge bbm-portal-stat-mom-badge--{{ $ldir }}">
                                @if ($ldir === 'up')
                                    <i class="bi bi-graph-up-arrow"></i>
                                @elseif ($ldir === 'down')
                                    <i class="bi bi-graph-down-arrow"></i>
                                @else
                                    <i class="bi bi-dash-lg"></i>
                                @endif
                                {{ $lpct }}
                            </span>
                            <span class="bbm-portal-stat-mom-vs">vs Tahun Lalu</span>
                        </div>
                    @endif
                </x-admin-stat-card>
                <x-admin-stat-card
                    title="Total Biaya BBM"
                    :value="number_format((float) $stats['year_rupiah'], 0, ',', '.')"
                    unitBefore="Rp"
                    description="Pengeluaran BBM tahun berjalan"
                    icon="bi bi-cash-stack"
                >
                    @if(!empty($stats['yoy_year_rupiah']['show']))
                        @php
                            $rdir = $stats['yoy_year_rupiah']['direction'] ?? 'flat';
                            $rpct = $stats['yoy_year_rupiah']['pct_display'] ?? '';
                        @endphp
                        <div class="bbm-portal-stat-mom" role="presentation">
                            <span class="bbm-portal-stat-mom-badge bbm-portal-stat-mom-badge--{{ $rdir }}">
                                @if ($rdir === 'up')
                                    <i class="bi bi-graph-up-arrow"></i>
                                @elseif ($rdir === 'down')
                                    <i class="bi bi-graph-down-arrow"></i>
                                @else
                                    <i class="bi bi-dash-lg"></i>
                                @endif
                                {{ $rpct }}
                            </span>
                            <span class="bbm-portal-stat-mom-vs">vs Tahun Lalu</span>
                        </div>
                    @endif
                </x-admin-stat-card>
                <x-admin-stat-card
                    title="Kendaraan Boros"
                    icon="bi bi-arrow-up-circle-fill"
                    description="Liter tertinggi tahun ini"
                >
                    <x-slot:statValue>
                        <div class="portal-stat-value" style="font-size:1rem;line-height:1.3">
                            @if($stats['boros'])
                                <strong>{{ $stats['boros']->nomor_kendaraan }}</strong>
                                <span class="portal-stat-sublabel">{{ $fmtLiter($stats['boros']->liters) }} L · {{ $fmtRp($stats['boros']->rupiah) }}</span>
                            @else
                                —
                            @endif
                        </div>
                    </x-slot:statValue>
                </x-admin-stat-card>
                <x-admin-stat-card
                    title="Peringatan Pengisian BBM"
                    icon="bi bi-exclamation-triangle-fill"
                    description="Kendaraan dengan pengisian BBM paling lama"
                >
                    <x-slot:statValue>
                        <div class="portal-stat-value" style="font-size:1rem;line-height:1.3">
                            @if($stats['overdue_vehicle'])
                                <strong>{{ $stats['overdue_vehicle']->nomor_kendaraan }}</strong>
                                <span class="portal-stat-sublabel">{{ $stats['overdue_vehicle']->last_fill_label }}</span>
                            @else
                                —
                            @endif
                        </div>
                    </x-slot:statValue>
                </x-admin-stat-card>
            </div>

            <div class="portal-charts-grid portal-charts-grid--bbm" id="portal-charts-bbm">
                <div class="portal-chart-card portal-chart-card--wide">
                    <div class="bbm-chart-title-row portal-chart-title-row">
                        <div class="portal-chart-title" id="bbm-combined-chart-title">Pengeluaran BBM &amp; liter per bulan</div>
                        <div class="bbm-chart-inline-filters" id="bbm-chart-global-filters">
                            <div class="ppm-status-wrap bbm-chart-year-wrap">
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
                        </div>
                    </div>
                    <div class="portal-chart-container portal-chart-container--bbm-combined">
                        <div class="portal-chart-loading"><span class="portal-chart-loading-spinner"></span></div>
                        <canvas id="bbmChartCombined"></canvas>
                    </div>
                </div>
                <div class="portal-chart-card portal-chart-card--bbm-driver-col">
                    <div class="portal-chart-title" id="bbm-driver-pie-title">Top driver — frekuensi pengisian (tahun {{ $stats['year_label'] }})</div>
                    <div class="portal-chart-container portal-chart-container--bbm-driver-pie">
                        <div class="portal-chart-loading"><span class="portal-chart-loading-spinner"></span></div>
                        <canvas id="bbmChartDriverFreq"></canvas>
                    </div>
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
                        <x-admin-per-page-select
                            id="bbm-portal-per-page"
                            name="per_page"
                            :selected="$reports->perPage()"
                        />
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
                                                {{ \App\Support\DriverShift::tableLabelFromCode($r->shift) }}
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
                    <x-admin-pagination :paginator="$reports" />
                </div>
                @endfragment
            </div>
            @endunless
        </div>
    </div>
@endsection

@section('modals')
    @unless($bbmPortalChartsOnly ?? false)
        {{-- Detail modal --}}
        <div id="bbm-modal-detail" class="modal-overlay" style="display:none;">
            <div class="modal-box profile-card sppd-modal-box" style="max-width:min(720px,100%);text-align:left;max-height:86vh;overflow:auto;">
                <h3>Detail Laporan BBM</h3>
                <div id="bbm-detail-body" class="sppd-detail-html"></div>
                <div class="ppm-modal-actions">
                    <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite" data-close-bbm-modal title="Tutup" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
                </div>
            </div>
        </div>
        <div id="bbm-photo-lightbox" class="bbm-photo-lightbox" hidden role="dialog" aria-modal="true" aria-label="Pratinjau foto">
            <button type="button" class="bbm-photo-lightbox-close" data-close-bbm-lightbox aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
            <div class="bbm-photo-lightbox-toolbar">
                <button type="button" data-bbm-zoom="out" aria-label="Perkecil">−</button>
                <button type="button" data-bbm-zoom="reset" aria-label="Reset zoom">100%</button>
                <button type="button" data-bbm-zoom="in" aria-label="Perbesar">+</button>
            </div>
            <div class="bbm-photo-lightbox-viewport" id="bbm-photo-lightbox-viewport">
                <img id="bbm-photo-lightbox-img" src="" alt="Pratinjau foto BBM" draggable="false">
            </div>
        </div>
    @endunless
@endsection

@push('scripts')
    <script>
        (function () {
            const BBM_PORTAL_CHARTS_ONLY = @json($bbmPortalChartsOnly ?? false);
            const BBM_CHART_SERIES_URL = @json(route('admin.portal-bbm-operasional.charts'));
            const BBM_ACTIVITY_LOG_URL = @json(route('admin.portal-bbm-operasional.activity-log'));

            const MONTH_LABELS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            const CHART_POLL_MS = 66000;
            const LOG_POLL_MS = 28000;

            let chartCombined, chartDrvFreq;
            let lastComparisonPayload = null;
            let lastTopDrivers = [];
            let chartPollTimer = null;
            let logPollTimer = null;

            function fmtRpShort(n) {
                const x = Number(n) || 0;
                if (x >= 1e9) return (x / 1e9).toFixed(1) + ' M';
                if (x >= 1e6) return (x / 1e6).toFixed(1) + ' jt';
                if (x >= 1e3) return (x / 1e3).toFixed(0) + ' rb';
                return String(Math.round(x));
            }

            
            function isDarkTheme() {
                return document.documentElement.classList.contains('dark')
                || document.body.classList.contains('dark');
            }
            
            function getBBMChartBlue() {
                return isDarkTheme()
                    ? '#1248a4'
                    : '#0e2a52';
            }
            const BBM_CHART_GOLD = '#D4AF37';
            const BBM_CHART_CURRENT_LINE = '#0891B2';
            const BBM_CHART_PREV_LINE = '#64748B';
            const BBM_PIE_PALETTE = [
                '#1248a4', '#D4AF37', '#1e3a5f', '#b8942e',
                '#2d4a6f', '#c9a227', '#4a6278', '#8f7620',
                '#5c7389', '#a68922', '#74899c', '#7a6518',
            ];

            function chartCommonSkin() {
                const dark = isDarkTheme();
                return {
                    dark,
                    grid: dark ? 'rgba(200,218,255,0.1)' : 'rgba(0,0,0,0.08)',
                    tick: dark ? 'rgba(200,218,255,0.65)' : '#64748b',
                    bdr: dark ? 'rgba(200,218,255,0.12)' : 'rgba(255,255,255,0.8)',
                    common: { responsive: true, maintainAspectRatio: false },
                };
            }

            function updateDriverPieTitle(year) {
                const el = document.getElementById('bbm-driver-pie-title');
                if (el) el.textContent = 'Top driver — frekuensi pengisian (tahun ' + String(year) + ')';
            }

            function updateCombinedChartTitle(year, yearPrev) {
                const el = document.getElementById('bbm-combined-chart-title');
                if (el) {
                    el.textContent = 'Pengeluaran BBM & liter per bulan';
                }
            }

            function renderComparisonCharts(data) {
                if (!data || !Array.isArray(data.month_labels)) return;
                try { chartCombined?.destroy(); } catch (_) {}
                chartCombined = null;

                const { grid, tick, common } = chartCommonSkin();
                const colRp = getBBMChartBlue();
                const colRpPrev = BBM_CHART_GOLD;
                const colLiter = BBM_CHART_CURRENT_LINE;
                const colLiterPrev = BBM_CHART_PREV_LINE;
                const yCur = data.year;
                const yPrev = data.year_previous;
                const labels = data.month_labels.length ? data.month_labels : MONTH_LABELS;
                const narrow = typeof window !== 'undefined' && window.innerWidth <= 640;

                const el = document.getElementById('bbmChartCombined');
                if (el) {
                    chartCombined = new Chart(el, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [
                                {
                                    type: 'bar',
                                    label: 'Biaya (' + String(yCur) + ')',
                                    data: (data.rupiah_current || []).map((v) => Math.round(Number(v) / 1000)),
                                    backgroundColor: colRp,
                                    borderColor: colRp,
                                    borderWidth: 1,
                                    borderRadius: 5,
                                    borderSkipped: false,
                                    yAxisID: 'y',
                                    bbmSeries: 'rupiah_current',
                                    order: 2,
                                },
                                {
                                    type: 'bar',
                                    label: 'Biaya (' + String(yPrev) + ')',
                                    data: (data.rupiah_previous || []).map((v) => Math.round(Number(v) / 1000)),
                                    backgroundColor: colRpPrev,
                                    borderColor: colRpPrev,
                                    borderWidth: 1,
                                    borderRadius: 5,
                                    borderSkipped: false,
                                    yAxisID: 'y',
                                    bbmSeries: 'rupiah_previous',
                                    order: 2,
                                },
                                {
                                    type: 'line',
                                    label: 'Liter (' + String(yCur) + ')',
                                    data: (data.liter_current || []).map((v) => Number(v)),
                                    borderColor: colLiter,
                                    backgroundColor: colLiter,
                                    pointBackgroundColor: colLiter,
                                    pointBorderColor: colLiter,
                                    borderWidth: 2,
                                    pointRadius: 3,
                                    pointHoverRadius: 5,
                                    tension: 0.25,
                                    fill: false,
                                    yAxisID: 'y1',
                                    bbmSeries: 'liter_current',
                                    order: 1,
                                },
                                {
                                    type: 'line',
                                    label: 'Liter (' + String(yPrev) + ')',
                                    data: (data.liter_previous || []).map((v) => Number(v)),
                                    borderColor: colLiterPrev,
                                    backgroundColor: colLiterPrev,
                                    pointBackgroundColor: colLiterPrev,
                                    pointBorderColor: colLiterPrev,
                                    borderWidth: 2,
                                    borderDash: [6, 4],
                                    pointRadius: 2,
                                    pointHoverRadius: 4,
                                    tension: 0.25,
                                    fill: false,
                                    yAxisID: 'y1',
                                    bbmSeries: 'liter_previous',
                                    order: 1,
                                },
                            ],
                        },
                        options: {
                            ...common,
                            interaction: { mode: 'index', intersect: false },
                            layout: {
                                padding: narrow ? { bottom: 4 } : {},
                            },
                            datasets: { bar: { maxBarThickness: narrow ? 14 : 22 } },
                            plugins: {
                                legend: { 
                                    display: true,
                                    position: narrow ? 'bottom' : 'top',
                                    labels: {
                                        color: tick,
                                        boxWidth: narrow ? 10 : 12,
                                        padding: narrow ? 8 : 10,
                                        font: { size: narrow ? 10 : 11 },
                                        generateLabels(chart) {
                                            const labels = Chart.defaults.plugins.legend.labels.generateLabels(chart);

                                            labels.forEach(label => {
                                                const ds = chart.data.datasets[label.datasetIndex];

                                                if (ds.bbmSeries === 'liter_previous') {
                                                    label.fillStyle = 'transparent';
                                                    label.strokeStyle = ds.borderColor;
                                                    label.lineWidth = 1.5;
                                                    label.lineDash = [4, 2];
                                                }
                                            });

                                            return labels;
                                        }
                                    } 
                                },
                                tooltip: {
                                    callbacks: {
                                        label(ctx) {
                                            const key = ctx.dataset.bbmSeries || '';
                                            const raw = (data[key] || [])[ctx.dataIndex] || 0;
                                            if (key.startsWith('liter_')) {
                                                return ' ' + ctx.dataset.label + ': ' + Number(raw).toLocaleString('id-ID', { maximumFractionDigits: 3 }) + ' L';
                                            }
                                            return ' ' + ctx.dataset.label + ': Rp ' + Number(raw).toLocaleString('id-ID');
                                        },
                                        labelColor(ctx) {
                                            const ds = ctx.dataset;

                                            if (ds.bbmSeries === 'liter_previous') {
                                                return {
                                                    borderColor: BBM_CHART_PREV_LINE,
                                                    backgroundColor: '#0F172A',
                                                    borderWidth: 2,
                                                    borderDash: [4, 3],
                                                };
                                            }

                                            return {
                                                borderColor: ds.borderColor,
                                                backgroundColor: ds.backgroundColor,
                                                borderWidth: 1,
                                            };
                                        }
                                    },
                                },
                            },
                            scales: {
                                y: {
                                    type: 'linear',
                                    position: 'left',
                                    beginAtZero: true,
                                    title: { display: !narrow, text: 'Rp (÷1000)', color: tick },
                                    ticks: { color: tick, callback: (v) => fmtRpShort(v * 1000), font: { size: narrow ? 9 : 11 } },
                                    grid: { color: grid },
                                },
                                y1: {
                                    type: 'linear',
                                    position: 'right',
                                    beginAtZero: true,
                                    title: { display: !narrow, text: 'Liter', color: tick },
                                    ticks: { color: tick, font: { size: narrow ? 9 : 11 } },
                                    grid: { drawOnChartArea: false },
                                },
                                x: {
                                    ticks: { color: tick, font: { size: narrow ? 9 : 11 }, maxRotation: narrow ? 45 : 0 },
                                    grid: { color: grid },
                                },
                            },
                        },
                    });
                }
                updateCombinedChartTitle(yCur, yPrev);
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
                    lastTopDrivers = Array.isArray(data.top_drivers) ? data.top_drivers : [];
                    renderComparisonCharts(data);
                    buildDriverPieChart(lastTopDrivers);
                    updateDriverPieTitle(data.year);
                    requestAnimationFrame(function () {
                        const c = document.getElementById('bbmChartCombined')?.closest('.portal-chart-container');
                        if (c) c.classList.add('is-ready');
                        const pieContainer = document.getElementById('bbmChartDriverFreq')?.closest('.portal-chart-container');
                        if (pieContainer) pieContainer.classList.add('is-ready');
                    });
                } catch (_) {}
            }

            function buildDriverPieChart(topDrivers) {
                try { chartDrvFreq?.destroy(); } catch (_) {}
                chartDrvFreq = null;
                const { tick, common } = chartCommonSkin();
                const elD = document.getElementById('bbmChartDriverFreq');
                const drivers = Array.isArray(topDrivers) ? topDrivers : [];
                if (!elD) return;
                if (!drivers.length) {
                    const container = elD.closest('.portal-chart-container');
                    if (container) container.classList.add('is-ready');
                    return;
                }
                const dark = isDarkTheme();
                const drvLabels = drivers.map((d) => d.name || d.username || 'Driver');
                const drvData = drivers.map((d) => Number(d.cnt));
                const narrow = typeof window !== 'undefined' && window.innerWidth <= 640;
                chartDrvFreq = new Chart(elD, {
                    type: 'pie',
                    data: {
                        labels: drvLabels,
                        datasets: [{
                            data: drvData,
                            backgroundColor: drivers.map((_, i) => BBM_PIE_PALETTE[i % BBM_PIE_PALETTE.length]),
                            borderColor: dark ? '#1e293b' : '#ffffff',
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
                function photoThumb(url, alt) {
                    if (!url) return '<p class="portal-empty" style="padding:8px">—</p>';
                    const safe = String(url).replace(/"/g, '&quot;');
                    const imgClass =
                        alt === 'Odometer'
                            ? 'sppd-photo-thumb bbm-photo-thumb--odometer-grid'
                            : 'sppd-photo-thumb';
                    return `<button type="button" class="bbm-photo-thumb-btn" data-full-url="${safe}" aria-label="Perbesar ${esc(alt)}"><img src="${safe}" class="${imgClass}" alt="${esc(alt)}"></button>`;
                }
                const odo = photoThumb(d.odometer_photo_url, 'Odometer');
                const struk = photoThumb(d.struk_photo_url, 'Struk');
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
                    <div class="bbm-detail-photos">
                        <figure><figcaption>Foto odometer</figcaption>${odo}</figure>
                        <figure><figcaption>Foto struk</figcaption>${struk}</figure>
                    </div>
                `;
            }

            const bbmPhotoZoom = {
                scale: 1,
                fitScale: 1,
                translateX: 0,
                translateY: 0,
                dragging: false,
                dragStartX: 0,
                dragStartY: 0,
                dragOriginX: 0,
                dragOriginY: 0,
                pinchStartDist: 0,
                pinchStartScale: 1,
                lastTap: 0,
            };

            function bbmPhotoEls() {
                return {
                    lb: document.getElementById('bbm-photo-lightbox'),
                    viewport: document.getElementById('bbm-photo-lightbox-viewport'),
                    img: document.getElementById('bbm-photo-lightbox-img'),
                };
            }

            function bbmPhotoIsOpen() {
                const { lb } = bbmPhotoEls();
                return lb && !lb.hidden;
            }

            function applyBbmPhotoTransform() {
                const { img } = bbmPhotoEls();
                if (!img) return;
                const z = bbmPhotoZoom;
                img.style.transform = 'translate(' + z.translateX + 'px,' + z.translateY + 'px) scale(' + z.scale + ')';
            }

            function bbmPhotoResetTransform() {
                bbmPhotoZoom.scale = bbmPhotoZoom.fitScale;
                bbmPhotoZoom.translateX = 0;
                bbmPhotoZoom.translateY = 0;
                applyBbmPhotoTransform();
                bbmPhotoUpdateResetLabel();
            }

            function bbmPhotoUpdateResetLabel() {
                const btn = document.querySelector('[data-bbm-zoom="reset"]');
                if (!btn) return;
                const pct = Math.round((bbmPhotoZoom.scale / bbmPhotoZoom.fitScale) * 100);
                btn.textContent = pct + '%';
            }

            function bbmPhotoComputeFitScale() {
                const { viewport, img } = bbmPhotoEls();
                if (!viewport || !img || !img.naturalWidth) return 1;
                const pad = 24;
                const vw = Math.max(1, viewport.clientWidth - pad);
                const vh = Math.max(1, viewport.clientHeight - pad);
                return Math.min(vw / img.naturalWidth, vh / img.naturalHeight, 1);
            }

            function bbmPhotoZoomAt(factor, clientX, clientY) {
                const { viewport } = bbmPhotoEls();
                if (!viewport) return;
                const z = bbmPhotoZoom;
                const rect = viewport.getBoundingClientRect();
                const cx = clientX - rect.left - rect.width / 2;
                const cy = clientY - rect.top - rect.height / 2;
                const prev = z.scale;
                const next = Math.min(Math.max(prev * factor, z.fitScale * 0.5), z.fitScale * 6);
                const ratio = next / prev;
                z.translateX = cx - (cx - z.translateX) * ratio;
                z.translateY = cy - (cy - z.translateY) * ratio;
                z.scale = next;
                applyBbmPhotoTransform();
                bbmPhotoUpdateResetLabel();
            }

            function bbmPhotoStepZoom(direction) {
                const { viewport } = bbmPhotoEls();
                if (!viewport) return;
                const rect = viewport.getBoundingClientRect();
                bbmPhotoZoomAt(direction > 0 ? 1.25 : 0.8, rect.left + rect.width / 2, rect.top + rect.height / 2);
            }

            function bbmDetailModalIsOpen() {
                const m = document.getElementById('bbm-modal-detail');
                if (!m) return false;
                return window.getComputedStyle(m).display !== 'none';
            }

            function bbmSyncBodyScrollLock() {
                const lock = bbmPhotoIsOpen() || bbmDetailModalIsOpen();
                document.body.style.overflow = lock ? 'hidden' : '';
            }

            function closeBbmDetailModal() {
                if (bbmPhotoIsOpen()) closeBbmPhotoLightbox();
                const m = document.getElementById('bbm-modal-detail');
                if (m) m.style.display = 'none';
                bbmSyncBodyScrollLock();
            }

            function openBbmDetailModal() {
                const m = document.getElementById('bbm-modal-detail');
                if (m) m.style.display = 'flex';
                bbmSyncBodyScrollLock();
            }

            function bbmPhotoMountToBody() {
                const { lb } = bbmPhotoEls();
                if (lb && lb.parentElement !== document.body) {
                    document.body.appendChild(lb);
                }
            }

            function openBbmPhotoLightbox(url) {
                const { lb, img } = bbmPhotoEls();
                if (!lb || !img || !url) return;
                bbmPhotoMountToBody();
                bbmPhotoZoom.scale = 1;
                bbmPhotoZoom.fitScale = 1;
                bbmPhotoZoom.translateX = 0;
                bbmPhotoZoom.translateY = 0;
                img.onload = function () {
                    bbmPhotoZoom.fitScale = bbmPhotoComputeFitScale();
                    bbmPhotoResetTransform();
                };
                img.src = url;
                if (img.complete && img.naturalWidth) {
                    bbmPhotoZoom.fitScale = bbmPhotoComputeFitScale();
                    bbmPhotoResetTransform();
                }
                lb.hidden = false;
                bbmSyncBodyScrollLock();
            }

            function closeBbmPhotoLightbox() {
                const { lb, img } = bbmPhotoEls();
                if (lb) lb.hidden = true;
                if (img) {
                    img.onload = null;
                    img.src = '';
                    img.style.transform = '';
                }
                bbmPhotoZoom.dragging = false;
                bbmSyncBodyScrollLock();
            }

            function initBbmPhotoLightboxZoom() {
                const { lb, viewport, img } = bbmPhotoEls();
                if (!lb || !viewport || !img || lb.dataset.bbmZoomBound === '1') return;
                lb.dataset.bbmZoomBound = '1';

                lb.querySelector('[data-close-bbm-lightbox]')?.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    closeBbmPhotoLightbox();
                });

                lb.querySelectorAll('[data-bbm-zoom]').forEach((btn) => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const action = btn.getAttribute('data-bbm-zoom');
                        if (action === 'in') bbmPhotoStepZoom(1);
                        else if (action === 'out') bbmPhotoStepZoom(-1);
                        else bbmPhotoResetTransform();
                    });
                });

                viewport.addEventListener('wheel', (e) => {
                    if (!bbmPhotoIsOpen()) return;
                    e.preventDefault();
                    const factor = e.deltaY < 0 ? 1.12 : 0.88;
                    bbmPhotoZoomAt(factor, e.clientX, e.clientY);
                }, { passive: false });

                viewport.addEventListener('mousedown', (e) => {
                    if (!bbmPhotoIsOpen() || e.button !== 0) return;
                    if (e.target.closest('.bbm-photo-lightbox-toolbar')) return;
                    e.preventDefault();
                    bbmPhotoZoom.dragging = true;
                    bbmPhotoZoom.dragStartX = e.clientX;
                    bbmPhotoZoom.dragStartY = e.clientY;
                    bbmPhotoZoom.dragOriginX = bbmPhotoZoom.translateX;
                    bbmPhotoZoom.dragOriginY = bbmPhotoZoom.translateY;
                    viewport.classList.add('is-dragging');
                });

                window.addEventListener('mousemove', (e) => {
                    if (!bbmPhotoZoom.dragging) return;
                    bbmPhotoZoom.translateX = bbmPhotoZoom.dragOriginX + (e.clientX - bbmPhotoZoom.dragStartX);
                    bbmPhotoZoom.translateY = bbmPhotoZoom.dragOriginY + (e.clientY - bbmPhotoZoom.dragStartY);
                    applyBbmPhotoTransform();
                });

                window.addEventListener('mouseup', () => {
                    if (!bbmPhotoZoom.dragging) return;
                    bbmPhotoZoom.dragging = false;
                    viewport.classList.remove('is-dragging');
                });

                viewport.addEventListener('dblclick', (e) => {
                    if (!bbmPhotoIsOpen()) return;
                    e.preventDefault();
                    const z = bbmPhotoZoom;
                    if (z.scale > z.fitScale * 1.05) {
                        bbmPhotoResetTransform();
                    } else {
                        bbmPhotoZoomAt(2, e.clientX, e.clientY);
                    }
                });

                viewport.addEventListener('touchstart', (e) => {
                    if (!bbmPhotoIsOpen()) return;
                    if (e.touches.length === 2) {
                        const t = e.touches;
                        bbmPhotoZoom.pinchStartDist = Math.hypot(t[1].clientX - t[0].clientX, t[1].clientY - t[0].clientY);
                        bbmPhotoZoom.pinchStartScale = bbmPhotoZoom.scale;
                    } else if (e.touches.length === 1) {
                        const now = Date.now();
                        if (now - bbmPhotoZoom.lastTap < 300) {
                            e.preventDefault();
                            const t = e.touches[0];
                            const z = bbmPhotoZoom;
                            if (z.scale > z.fitScale * 1.05) bbmPhotoResetTransform();
                            else bbmPhotoZoomAt(2, t.clientX, t.clientY);
                            bbmPhotoZoom.lastTap = 0;
                        } else {
                            bbmPhotoZoom.lastTap = now;
                            bbmPhotoZoom.dragging = true;
                            bbmPhotoZoom.dragStartX = e.touches[0].clientX;
                            bbmPhotoZoom.dragStartY = e.touches[0].clientY;
                            bbmPhotoZoom.dragOriginX = bbmPhotoZoom.translateX;
                            bbmPhotoZoom.dragOriginY = bbmPhotoZoom.translateY;
                            viewport.classList.add('is-dragging');
                        }
                    }
                }, { passive: false });

                viewport.addEventListener('touchmove', (e) => {
                    if (!bbmPhotoIsOpen()) return;
                    if (e.touches.length === 2) {
                        e.preventDefault();
                        const t = e.touches;
                        const dist = Math.hypot(t[1].clientX - t[0].clientX, t[1].clientY - t[0].clientY);
                        if (bbmPhotoZoom.pinchStartDist > 0) {
                            const cx = (t[0].clientX + t[1].clientX) / 2;
                            const cy = (t[0].clientY + t[1].clientY) / 2;
                            const target = bbmPhotoZoom.pinchStartScale * (dist / bbmPhotoZoom.pinchStartDist);
                            const z = bbmPhotoZoom;
                            const clamped = Math.min(Math.max(target, z.fitScale * 0.5), z.fitScale * 6);
                            const factor = clamped / z.scale;
                            bbmPhotoZoomAt(factor, cx, cy);
                        }
                    } else if (e.touches.length === 1 && bbmPhotoZoom.dragging) {
                        e.preventDefault();
                        bbmPhotoZoom.translateX = bbmPhotoZoom.dragOriginX + (e.touches[0].clientX - bbmPhotoZoom.dragStartX);
                        bbmPhotoZoom.translateY = bbmPhotoZoom.dragOriginY + (e.touches[0].clientY - bbmPhotoZoom.dragStartY);
                        applyBbmPhotoTransform();
                    }
                }, { passive: false });

                viewport.addEventListener('touchend', () => {
                    bbmPhotoZoom.dragging = false;
                    bbmPhotoZoom.pinchStartDist = 0;
                    viewport.classList.remove('is-dragging');
                });

                lb.addEventListener('click', (e) => {
                    if (e.target === lb) closeBbmPhotoLightbox();
                });
            }

            initBbmPhotoLightboxZoom();
            bbmPhotoMountToBody();

            document.addEventListener('click', (e) => {
                if (e.target.closest('[data-close-bbm-lightbox]')) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeBbmPhotoLightbox();
                    return;
                }
                const thumbBtn = e.target.closest('.bbm-photo-thumb-btn[data-full-url]');
                if (thumbBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    openBbmPhotoLightbox(thumbBtn.getAttribute('data-full-url'));
                }
            });

            document.querySelector('.admin-shell')?.addEventListener('click', async (e) => {
                if (e.target.closest('.bbm-photo-thumb-btn[data-full-url]')) return;
                const act = e.target.closest('.bbm-activity-row[data-json-url]');
                if (act) {
                    if (!document.getElementById('bbm-modal-detail')) return;
                    const url = act.getAttribute('data-json-url');
                    const bodyEl = document.getElementById('bbm-detail-body');
                    bodyEl.innerHTML = '<p>Memuat…</p>';
                    openBbmDetailModal();
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
                const bodyEl = document.getElementById('bbm-detail-body');
                bodyEl.innerHTML = '<p>Memuat…</p>';
                openBbmDetailModal();
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
                el.addEventListener('click', () => closeBbmDetailModal());
            });
            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Escape') return;
                if (bbmPhotoIsOpen()) {
                    e.stopPropagation();
                    closeBbmPhotoLightbox();
                    return;
                }
                if (bbmDetailModalIsOpen()) {
                    closeBbmDetailModal();
                }
            });
            document.getElementById('bbm-modal-detail')?.addEventListener('click', (e) => {
                if (e.target.id === 'bbm-modal-detail') closeBbmDetailModal();
            });

            document.getElementById('bbm-chart-year')?.addEventListener('change', () => { fetchComparisonCharts(); });
            document.getElementById('bbm-chart-vehicle')?.addEventListener('change', () => { fetchComparisonCharts(); });

            // Init charts immediately — no deferred IntersectionObserver
            fetchComparisonCharts();
            fetchActivityLog();
            chartPollTimer = setInterval(fetchComparisonCharts, CHART_POLL_MS);
            logPollTimer = setInterval(fetchActivityLog, LOG_POLL_MS);

            // Reveal pie chart loading overlay is handled in fetchComparisonCharts

            // Pause polling when tab is not visible — saves battery and network on mobile
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    clearInterval(chartPollTimer);
                    clearInterval(logPollTimer);
                    chartPollTimer = null;
                    logPollTimer = null;
                } else {
                    fetchComparisonCharts();
                    fetchActivityLog();
                    chartPollTimer = setInterval(fetchComparisonCharts, CHART_POLL_MS);
                    logPollTimer = setInterval(fetchActivityLog, LOG_POLL_MS);
                }
            });

            let bbmPieResizeTimer = null;
            window.addEventListener('resize', () => {
                clearTimeout(bbmPieResizeTimer);
                bbmPieResizeTimer = setTimeout(() => {
                    buildDriverPieChart(lastTopDrivers);
                    redrawComparisonFromCache();
                }, 200);
            }, { passive: true });

            /* Expose rebuild fn so the single document-level theme listener always calls the latest closure */
            window._bbmPortalRebuildCharts = function () {
                buildDriverPieChart(lastTopDrivers);
                redrawComparisonFromCache();
            };

            /* Rebuild charts on theme toggle */
            if (!document._bbmPortalThemeBound) {
                document._bbmPortalThemeBound = true;
                document.addEventListener('click', function (e) {
                    if (!e.target.closest('#dash-theme-toggle')) return;
                    if (!document.getElementById('portal-charts-bbm')) return;
                    requestAnimationFrame(function () {
                        requestAnimationFrame(function () {
                            if (typeof window._bbmPortalRebuildCharts === 'function') {
                                window._bbmPortalRebuildCharts();
                            }
                        });
                    });
                });
            }

            /* Register cleanup with central Turbo before-cache registry */
            if (typeof window.registerTurboCleanup === 'function') {
                window.registerTurboCleanup(function () {
                    document.body.style.overflow = '';
                    window._bbmPortalRebuildCharts = null;
                    if (chartPollTimer) { clearInterval(chartPollTimer); chartPollTimer = null; }
                    if (logPollTimer) { clearInterval(logPollTimer); logPollTimer = null; }
                    try { chartDrvFreq?.destroy(); } catch (_) {}
                    chartDrvFreq = null;
                    try { chartCombined?.destroy(); } catch (_) {}
                    chartCombined = null;
                });
            }

        })();

        /* ── SMOOTH SCROLL ── */
        function smoothTo(id, e) {
            e.preventDefault();
            const el = document.getElementById(id);
            if (el) el.scrollIntoView({behavior: 'smooth', block: 'start'});
        }
    </script>
@endpush

