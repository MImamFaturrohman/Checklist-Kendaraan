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
<script src="https://unpkg.com/@phosphor-icons/web"></script>
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
        }
        .bbm-portal-stat-mom-badge--down {
            background: rgba(34, 197, 94, 0.16);
            color: #15803d;
        }
        .bbm-portal-stat-mom-badge--flat {
            background: rgba(148, 163, 184, 0.22);
            color: #64748b;
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
        /* ── Photo thumb container with overlay label ── */
        .bbm-photo-thumb-wrap {
            position: relative;
            display: block;
            border-radius: 8px;
            overflow: hidden;
        }
        .bbm-photo-overlay-label {
            position: absolute;
            top: 7px;
            left: 7px;
            z-index: 2;
            /* fluid size: scales with viewport, stays readable */
            font-size: clamp(9px, 1.5vw, 13px);
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #fff;
            background: rgba(11, 44, 107, 0.72);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            padding: 2px 7px 2px 6px;
            border-radius: 5px;
            line-height: 1.5;
            pointer-events: none;
            white-space: nowrap;
            box-shadow: 0 1px 4px rgba(0,0,0,.25);
        }
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
            border: none !important;
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

        /* Custom Premium Alert & Info Card Styles */
        .premium-alert-red, .premium-alert-gold {
            border: 1px solid #f1f5f9 !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05) !important;
            transform: none !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .premium-alert-red {
            border-left: 4px solid #ef4444 !important;
        }
        .premium-alert-gold {
            border-left: 4px solid #D4AF37 !important;
        }
        .premium-alert-red:hover, .premium-alert-gold:hover {
            transform: none !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05) !important;
        }
        .premium-text-red {
            color: #ef4444 !important;
        }
        .premium-text-gold {
            color: #D4AF37 !important;
        }
        .premium-bg-red-opacity-5 {
            background-color: rgba(239, 68, 68, 0.16) !important;
        }
        .premium-bg-gold-opacity-5 {
            background-color: rgba(212, 175, 55, 0.16) !important;
        }
        .premium-icon-hover {
            transition: color 0.2s ease;
        }

        /* Hover states */
        .premium-stat-card:hover .premium-icon-hover {
            color: #D4AF37 !important;
        }

        /* Dark Mode overrides - matching system colors from portal-stat-card */
        html.dark .premium-alert-red,
        html.dark .premium-alert-gold,
        .dark .premium-alert-red,
        .dark .premium-alert-gold {
            background: rgba(255, 255, 255, 0.08) !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
            backdrop-filter: blur(3px) saturate(150%);
            -webkit-backdrop-filter: blur(3px) saturate(150%);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
        }
        html.dark .premium-alert-red,
        .dark .premium-alert-red {
            border-left-color: #ef4444 !important;
        }
        html.dark .premium-alert-gold,
        .dark .premium-alert-gold {
            border-left-color: #D4AF37 !important;
        }
        html.dark .premium-alert-red:hover,
        html.dark .premium-alert-gold:hover,
        .dark .premium-alert-red:hover,
        .dark .premium-alert-gold:hover {
            background: rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
        }
        html.dark .premium-alert-red h4,
        html.dark .premium-alert-gold h4,
        .dark .premium-alert-red h4,
        .dark .premium-alert-gold h4 {
            color: rgba(241, 245, 249, 0.95) !important;
        }
        html.dark .premium-alert-red p,
        html.dark .premium-alert-gold p,
        .dark .premium-alert-red p,
        .dark .premium-alert-gold p {
            color: rgba(200, 218, 255, 0.72) !important;
        }
        html.dark .premium-stat-card .premium-icon-hover,
        .dark .premium-stat-card .premium-icon-hover {
            color: rgba(148, 163, 184, 0.22) !important;
        }
        html.dark .premium-stat-card:hover .premium-icon-hover,
        .dark .premium-stat-card:hover .premium-icon-hover {
            color: #D4AF37 !important;
        }

        /* Responsive Layout Overrides: 2:3 ratio for desktop */
        @media (min-width: 1025px) {
            .portal-charts-grid--bbm {
                grid-template-columns: repeat(5, 1fr) !important;
            }
            .portal-charts-grid--bbm .portal-chart-card--bbm-driver-col {
                grid-column: span 2 !important;
            }
            .portal-charts-grid--bbm .portal-chart-card--bbm-log-col {
                grid-column: span 3 !important;
            }
        }

        /* Index Rekapitulasi Subcomponents */
        .rekap-select {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            cursor: pointer;
        }
        html.dark .rekap-select,
        .dark .rekap-select {
            background: rgba(15, 23, 42, 0.4) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #fff !important;
        }
        
        .rekap-val-cur-year {
            color: #2563eb; /* blue-600 */
            font-weight: 600;
        }
        html.dark .rekap-val-cur-year,
        .dark .rekap-val-cur-year {
            color: #60a5fa !important;
        }
        .rekap-val-cur-vol {
            color: #475569; /* slate-600 */
        }
        html.dark .rekap-val-cur-vol,
        .dark .rekap-val-cur-vol {
            color: #cbd5e1 !important;
        }
        .rekap-val-cur-cost {
            color: #D4AF37;
            font-weight: bold;
        }
        
        .rekap-val-prev-year,
        .rekap-val-prev-vol,
        .rekap-val-prev-cost {
            color: #94a3b8; /* slate-400 */
        }
        html.dark .rekap-val-prev-year,
        html.dark .rekap-val-prev-vol,
        html.dark .rekap-val-prev-cost,
        .dark .rekap-val-prev-year,
        .dark .rekap-val-prev-vol,
        .dark .rekap-val-prev-cost {
            color: #64748b !important; /* slate-500 */
        }

        .rekap-summary-block {
            background: rgba(15, 23, 42, 0.04) !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px;
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            justify-content: center !important;
            gap: 3px !important;
            align-items: center !important;
            text-align: center !important;
        }
        html.dark .rekap-summary-block,
        .dark .rekap-summary-block {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .rekap-summary-title {
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: block;
        }
        html.dark .rekap-summary-title,
        .dark .rekap-summary-title {
            color: #94a3b8 !important;
        }
        .rekap-summary-value {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin-top: 0 !important;
            display: block;
            word-break: break-all;
        }
        html.dark .rekap-summary-value,
        .dark .rekap-summary-value {
            color: #f8fafc !important;
        }

        /* Selisih Biaya value color indicators */
        .rekap-selisih-saving {
            color: #15803d !important;
        }
        html.dark .rekap-selisih-saving,
        .dark .rekap-selisih-saving {
            color: #22c55e !important;
        }
        .rekap-selisih-increase {
            color: #b91c1c !important;
        }
        html.dark .rekap-selisih-increase,
        .dark .rekap-selisih-increase {
            color: #ef4444 !important;
        }

        /* Trend badge theme overrides */
        .rekap-badge-up {
            background-color: #fee2e2 !important; /* bg-red-100 */
            color: #b91c1c !important; /* text-red-700 */
        }
        html.dark .rekap-badge-up,
        .dark .rekap-badge-up {
            background-color: rgba(239, 68, 68, 0.2) !important;
            color: #fca5a5 !important;
        }

        .rekap-badge-down {
            background-color: #dcfce7 !important; /* bg-green-100 */
            color: #15803d !important; /* text-green-700 */
        }
        html.dark .rekap-badge-down,
        .dark .rekap-badge-down {
            background-color: rgba(34, 197, 94, 0.2) !important;
            color: #86efac !important;
        }

        .rekap-badge-flat {
            background-color: #f1f5f9 !important; /* bg-slate-100 */
            color: #475569 !important; /* text-slate-700 */
        }
        html.dark .rekap-badge-flat,
        .dark .rekap-badge-flat {
            background-color: rgba(148, 163, 184, 0.2) !important;
            color: #cbd5e1 !important;
        }

        .bbm-shift-badge {
            border: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">
            <!-- NOTIFIKASI INSIGHT -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="portal-stat-card premium-alert-red flex gap-3 items-start relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-24 h-24 premium-bg-red-opacity-5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <i class="ph-fill ph-warning-circle premium-text-red text-2xl"></i>
                    <div>
                        <h4 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Peringatan: Jadwal Isi BBM</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            @if($stats['overdue_vehicle'])
                                Kendaraan <strong>{{ $stats['overdue_vehicle']->nomor_kendaraan }}</strong>: {{ $stats['overdue_vehicle']->last_fill_label }}
                            @else
                                Semua kendaraan aktif melakukan pengisian BBM secara berkala.
                            @endif
                        </p>
                    </div>
                </div>
                <div class="portal-stat-card premium-alert-gold flex gap-3 items-start relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-24 h-24 premium-bg-gold-opacity-5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <i class="ph-fill ph-info premium-text-gold text-2xl"></i>
                    <div>
                        <h4 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Insight Penggunaan</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            @if($stats['boros'])
                                Volume pengisian tertinggi tahun ini oleh kendaraan <strong>{{ $stats['boros']->nomor_kendaraan }}</strong> sebesar <strong>{{ $fmtLiter($stats['boros']->liters) }} L</strong> dengan total biaya <strong>{{ $fmtRp($stats['boros']->rupiah) }}</strong>.
                            @else
                                Belum ada data pengisian BBM tahun ini.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- CARD STATISTIK -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <!-- Card 1: Total Biaya (Tahun) -->
                <div class="portal-stat-card premium-stat-card">
                    <div class="portal-stat-icon premium-icon-hover"><i class="ph-fill ph-coins"></i></div>
                    <div class="portal-stat-body">
                        <div class="portal-stat-label">Total Biaya (Tahun)</div>
                        <div class="portal-stat-value-row">
                            <span class="portal-stat-value">{{ $fmtRp($stats['year_rupiah']) }}</span>
                        </div>
                        <div class="mt-2 flex items-center gap-1.5 text-[10px] md:text-xs">
                            @if(!empty($stats['yoy_year_rupiah']['show']))
                                @php
                                    $rdir = $stats['yoy_year_rupiah']['direction'] ?? 'flat';
                                    $rpct = $stats['yoy_year_rupiah']['pct_display'] ?? '';
                                @endphp
                                <span class="bbm-portal-stat-mom-badge bbm-portal-stat-mom-badge--{{ $rdir }}">
                                    @if ($rdir === 'up')
                                        <i class="ph-bold ph-trend-up"></i>
                                    @elseif ($rdir === 'down')
                                        <i class="ph-bold ph-trend-down"></i>
                                    @else
                                        <i class="ph-bold ph-minus"></i>
                                    @endif
                                    <span>{{ $rpct }}</span>
                                </span>
                                <span class="text-slate-400 dark:text-slate-500 truncate">vs Tahun Lalu</span>
                            @else
                                <span class="text-slate-400 dark:text-slate-500 truncate">Pengeluaran tahun {{ $stats['year_label'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- Card 2: Volume (Tahun) -->
                <div class="portal-stat-card premium-stat-card">
                    <div class="portal-stat-icon premium-icon-hover"><i class="ph-fill ph-drop"></i></div>
                    <div class="portal-stat-body">
                        <div class="portal-stat-label">Volume (Tahun)</div>
                        <div class="portal-stat-value-row">
                            <span class="portal-stat-value">{{ $fmtLiter($stats['year_liter']) }} L</span>
                        </div>
                        <div class="mt-2 flex items-center gap-1.5 text-[10px] md:text-xs">
                            @if(!empty($stats['yoy_year_liter']['show']))
                                @php
                                    $ldir = $stats['yoy_year_liter']['direction'] ?? 'flat';
                                    $lpct = $stats['yoy_year_liter']['pct_display'] ?? '';
                                @endphp
                                <span class="bbm-portal-stat-mom-badge bbm-portal-stat-mom-badge--{{ $ldir }}">
                                    @if ($ldir === 'up')
                                        <i class="ph-bold ph-trend-up"></i>
                                    @elseif ($ldir === 'down')
                                        <i class="ph-bold ph-trend-down"></i>
                                    @else
                                        <i class="ph-bold ph-minus"></i>
                                    @endif
                                    <span>{{ $lpct }}</span>
                                </span>
                                <span class="text-slate-400 dark:text-slate-500 truncate">vs Tahun Lalu</span>
                            @else
                                <span class="text-slate-400 dark:text-slate-500 truncate">Volume tahun {{ $stats['year_label'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- Card 3: Unit Kendaraan -->
                <div class="portal-stat-card premium-stat-card">
                    <div class="portal-stat-icon premium-icon-hover"><i class="ph-fill ph-car-profile"></i></div>
                    <div class="portal-stat-body">
                        <div class="portal-stat-label">Unit Kendaraan</div>
                        <div class="portal-stat-value-row">
                            <span class="portal-stat-value">{{ $stats['total_vehicles'] }} Unit</span>
                        </div>
                        <div class="mt-2 flex items-center gap-1.5 text-[10px] md:text-xs">
                            <span class="text-slate-400 dark:text-slate-500 truncate">Terdaftar pada sistem</span>
                        </div>
                    </div>
                </div>
                <!-- Card 4: Total Transaksi -->
                <div class="portal-stat-card premium-stat-card">
                    <div class="portal-stat-icon premium-icon-hover"><i class="ph-fill ph-receipt"></i></div>
                    <div class="portal-stat-body">
                        <div class="portal-stat-label">Total Transaksi</div>
                        <div class="portal-stat-value-row">
                            <span class="portal-stat-value">{{ $stats['year_reports'] }}</span>
                        </div>
                        <div class="mt-2 flex items-center gap-1.5 text-[10px] md:text-xs">
                            @if(!empty($stats['yoy_year_reports']['show']))
                                @php
                                    $ydir = $stats['yoy_year_reports']['direction'] ?? 'flat';
                                    $ypct = $stats['yoy_year_reports']['pct_display'] ?? '';
                                @endphp
                                <span class="bbm-portal-stat-mom-badge bbm-portal-stat-mom-badge--{{ $ydir }}">
                                    @if ($ydir === 'up')
                                        <i class="ph-bold ph-trend-up"></i>
                                    @elseif ($ydir === 'down')
                                        <i class="ph-bold ph-trend-down"></i>
                                    @else
                                        <i class="ph-bold ph-minus"></i>
                                    @endif
                                    <span>{{ $ypct }}</span>
                                </span>
                                <span class="text-slate-400 dark:text-slate-500 truncate">vs Tahun Lalu</span>
                            @else
                                <span class="text-slate-400 dark:text-slate-500 truncate">Transaksi tahun {{ $stats['year_label'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
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
                <div class="portal-chart-card portal-chart-card--bbm-driver-col" style="min-height: 380px;">
                    <div class="flex items-center justify-between mb-4">
                        <div class="portal-chart-title flex items-center gap-2 m-0" style="font-size: 0.85rem; font-weight: 700;">
                            <i class="ph-bold ph-table text-gold text-lg"></i>
                            <span>Index Rekapitulasi</span>
                        </div>
                        <div>
                            <select id="rekap-period-select" class="admin-filter-input py-1 px-2 rounded-lg text-xs rekap-select">
                                <option value="full">1 Tahun Penuh</option>
                                <option value="s1">Semester 1 (Jan - Jun)</option>
                                <option value="s2">Semester 2 (Jul - Des)</option>
                                <option value="q1">Triwulan 1 (Jan - Mar)</option>
                                <option value="q2">Triwulan 2 (Apr - Jun)</option>
                                <option value="q3">Triwulan 3 (Jul - Sep)</option>
                                <option value="q4">Triwulan 4 (Okt - Des)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-5 overflow-x-auto">
                        <table class="w-full text-sm text-left" style="border-collapse: collapse;">
                            <thead>
                                <tr class="border-b border-slate-700/50">
                                    <th class="py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Periode Tahun</th>
                                    <th class="py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Volume BBM</th>
                                    <th class="py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Total Biaya</th>
                                </tr>
                            </thead>
                            <tbody id="rekap-table-body">
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-slate-500">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="grid grid-cols-5 gap-3">
                        <!-- Selisih Biaya -->
                        <div class="col-span-3 rekap-summary-block">
                            <span class="rekap-summary-title">Selisih Biaya</span>
                            <span id="rekap-selisih-value" class="rekap-summary-value">—</span>
                            <span id="rekap-selisih-badge" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold">
                                —
                            </span>
                        </div>
                        <!-- Rata Harga & Total TRX -->
                        <div class="col-span-2 flex flex-col gap-2.5">
                            <!-- Rata Harga -->
                            <div class="rekap-summary-block">
                                <span class="rekap-summary-title">Rata Harga</span>
                                <span id="rekap-rata-harga" class="rekap-summary-value premium-text-royal">—</span>
                            </div>
                            <!-- Total TRX -->
                            <div class="rekap-summary-block">
                                <span class="rekap-summary-title">Total TRX</span>
                                <span id="rekap-total-trx" class="rekap-summary-value">—</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="portal-chart-card portal-chart-card--bbm-log-col bbm-activity-log-card" id="bbm-activity-log-card">
                    <div class="bbm-activity-log-head">
                        <div class="bbm-activity-log-title">Log Update <span class="bbm-activity-live" title="Memperbarui otomatis">· real-time</span></div>
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
            <div class="portal-section" id="section-bbm-table">
                <div class="portal-section-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
                    <div class="portal-section-title" style="margin-bottom: 0;"><i class="bi bi-table"></i> Riwayat Pengisian</div>

                    <div class="portal-local-filters ppm-daftar-filters bbm-portal-live-filter-bar" id="bbm-portal-filter-bar" style="margin-top: 0; padding: 0; background: transparent; border: none; box-shadow: none;">
                        <div class="admin-search-wrap portal-search-full" style="width: 320px; max-width: 100%;">
                            <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            <input type="text" id="bbm-portal-filter-q" value="{{ $bbmPortalSearch ?? request('q') }}" placeholder="Cari nopol, jenis, nama pengemudi..." class="admin-search-input" autocomplete="off" aria-label="Cari laporan BBM">
                        </div>
                        <div class="ppm-status-wrap">
                            <label class="sr-only" for="bbm-portal-filter-jenis-pengisian">Filter jenis pengisian BBM</label>
                            <select id="bbm-portal-filter-jenis-pengisian" class="admin-filter-input" aria-label="Filter jenis pengisian BBM">
                                <option value="" @selected(($bbmPortalJenisPengisian ?? '') === '')>Semua jenis</option>
                                <option value="Operasional" @selected(($bbmPortalJenisPengisian ?? '') === 'Operasional')>Operasional</option>
                                <option value="Perjalanan Dinas (SPPD)" @selected(($bbmPortalJenisPengisian ?? '') === 'Perjalanan Dinas (SPPD)')>Perjalanan Dinas (SPPD)</option>
                            </select>
                        </div>
                        <div class="ppm-status-wrap">
                            <label class="sr-only" for="bbm-portal-filter-month">Filter Bulan</label>
                            <select id="bbm-portal-filter-month" class="admin-filter-input" aria-label="Filter Bulan">
                                <option value="">Semua bulan</option>
                                @for($m = 1; $m <= 12; $m++)
                                    @php
                                        $mDate = Carbon\Carbon::create()->day(1)->month($m);
                                    @endphp
                                    <option value="{{ sprintf('%02d', $m) }}" @selected(($bbmPortalMonth ?? '') === sprintf('%02d', $m))>{{ $mDate->translatedFormat('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="ppm-status-wrap">
                            <label class="sr-only" for="bbm-portal-filter-year">Filter Tahun</label>
                            <select id="bbm-portal-filter-year" class="admin-filter-input" aria-label="Filter Tahun">
                                <option value="">Semua tahun</option>
                                @foreach($yearsAvailable as $y)
                                    <option value="{{ $y }}" @selected((int) ($bbmPortalYear ?? 0) === (int) $y)>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-admin-per-page-select
                            id="bbm-portal-filter-per-page"
                            name="per_page"
                            :selected="$reports->perPage()"
                        />
                        <div class="ppm-status-wrap bbm-portal-filter-actions">
                            <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite ppm-filter-reset" id="bbm-portal-filter-reset" title="Hapus semua filter" aria-label="Hapus semua filter"><i class="bi bi-arrow-clockwise"></i></button>
                        </div>
                    </div>
                </div>

                <div id="bbm-portal-loading" class="portal-loading" style="display:none; margin: 12px 0;">
                    <span class="portal-loading-dot"></span><span class="portal-loading-dot"></span><span class="portal-loading-dot"></span>
                </div>

                <div id="bbm-portal-live-root" data-vms-bbm-portal-live>
                @fragment('bbm-portal-table-body')
                @php
                    $fmtRp = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
                    $fmtLiter = fn ($n) => number_format((float) $n, 3, ',', '.');
                    $fmtKm = fn ($n) => number_format((int) round((float) $n), 0, ',', '.');
                @endphp
                <div class="admin-table-wrap admin-table-wrap--bbm-reports">
                    <table class="admin-table admin-table--bbm-reports">
                        <thead>
                            <tr>
                                <th>No</th>
                                <x-sortable-th key="tanggal" label="Tanggal" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <x-sortable-th key="waktu" label="Waktu" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <x-sortable-th key="shift" label="Shift" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <x-sortable-th key="nomor_kendaraan" label="Kendaraan" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <th>Pengemudi</th>
                                <x-sortable-th key="odometer_sebelum" label="Km Sebelum" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <x-sortable-th key="odometer_sesudah" label="Km Sesudah" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <th>Total KM</th>
                                <x-sortable-th key="liter" label="Volume (L)" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <x-sortable-th key="total_harga" label="Total Biaya" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                                <th>Jenis BBM</th>
                                <th></th>
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
                                    <td>{{ $r->tanggal->translatedFormat('j F Y') }}</td>
                                    <td>{{ $waktuStr }}</td>
                                    <td>
                                        <span class="bbm-shift-badge {{ \App\Support\DriverShift::badgeClassFromCode($r->shift) }}">
                                            <i class="{{ \App\Support\DriverShift::iconClassFromCode($r->shift) }}" aria-hidden="true"></i>
                                            {{ \App\Support\DriverShift::tableLabelFromCode($r->shift) }}
                                        </span>
                                    </td>
                                    <td><strong>{{ $r->nomor_kendaraan }}</strong><br><span class="sppd-cell-muted">{{ $r->jenis_kendaraan }}</span></td>
                                    <td>{{ $r->user?->name ?? '—' }}<br><span class="sppd-cell-muted">{{ $r->user?->username }}</span></td>
                                    <td>{{ $fmtKm($r->odometer_sebelum) }}</td>
                                    <td>{{ $fmtKm($r->odometer_sesudah) }}</td>
                                    <td><strong>{{ $fmtKm($totalKm) }}</strong></td>
                                    <td>{{ $fmtLiter($r->liter) }}</td>
                                    <td><strong>{{ $fmtRp($r->total_harga) }}</strong></td>
                                    <td>
                                        <span class="bbm-jenis-pengisian-cell" style="white-space: wrap;">{{ $r->jenis_pengisian ?: 'Operasional' }}</span>
                                    </td>
                                    <td>
                                        <button type="button"  class="btn btn-sm sppd-icon-btn sppd-btn-primary bbm-btn-detail" data-json-url="{{ route('admin.portal-bbm-operasional.json', $r) }}" title="Detail lengkap &amp; foto" aria-label="Detail laporan BBM"><i class="bi bi-info-circle"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="12" class="portal-empty">Belum ada laporan BBM dari driver.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-admin-pagination :paginator="$reports" />
                @endfragment
                </div>
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
            
            const BBM_CHART_CURRENT_BAR = '#D4AF37';
            const BBM_CHART_PREV_BAR = '#6B7280';
            function getBBMChartBlue() {
                return isDarkTheme()
                    ? '#60a5fa'
                    : '#002a7a';
            }
            const BBM_CHART_PREV_LINE = '#a9a9a9';
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
                const colRp = BBM_CHART_CURRENT_BAR;
                const colRpPrev = BBM_CHART_PREV_BAR;
                const colLiter = getBBMChartBlue();
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
                    updateRekapitulasiIndex();
                    requestAnimationFrame(function () {
                        const c = document.getElementById('bbmChartCombined')?.closest('.portal-chart-container');
                        if (c) c.classList.add('is-ready');
                    });
                } catch (_) {}
            }

            function updateRekapitulasiIndex() {
                const select = document.getElementById('rekap-period-select');
                if (!select || !lastComparisonPayload) return;
                const period = select.value;
                const data = lastComparisonPayload;

                let months = [];
                if (period === 'full') {
                    months = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                } else if (period === 's1') {
                    months = [0, 1, 2, 3, 4, 5];
                } else if (period === 's2') {
                    months = [6, 7, 8, 9, 10, 11];
                } else if (period === 'q1') {
                    months = [0, 1, 2];
                } else if (period === 'q2') {
                    months = [3, 4, 5];
                } else if (period === 'q3') {
                    months = [6, 7, 8];
                } else if (period === 'q4') {
                    months = [9, 10, 11];
                }

                let volCur = 0, volPrev = 0;
                let costCur = 0, costPrev = 0;
                let trxCur = 0, trxPrev = 0;

                const litersCur = data.liter_current || [];
                const litersPrev = data.liter_previous || [];
                const rupiahsCur = data.rupiah_current || [];
                const rupiahsPrev = data.rupiah_previous || [];
                const reportsCur = data.reports_current || [];
                const reportsPrev = data.reports_previous || [];

                months.forEach(m => {
                    volCur += Number(litersCur[m] || 0);
                    volPrev += Number(litersPrev[m] || 0);
                    costCur += Number(rupiahsCur[m] || 0);
                    costPrev += Number(rupiahsPrev[m] || 0);
                    trxCur += Number(reportsCur[m] || 0);
                    trxPrev += Number(reportsPrev[m] || 0);
                });

                const tbody = document.getElementById('rekap-table-body');
                if (tbody) {
                    tbody.innerHTML = `
                        <tr class="border-b border-slate-700/30">
                            <td class="py-2.5 rekap-val-cur-year">Tahun ${data.year}</td>
                            <td class="py-2.5 text-right rekap-val-cur-vol">${volCur.toLocaleString('id-ID', { maximumFractionDigits: 2 })} L</td>
                            <td class="py-2.5 text-right rekap-val-cur-cost">${formatRp(costCur)}</td>
                        </tr>
                        <tr class="border-b border-slate-700/30">
                            <td class="py-2.5 rekap-val-prev-year">Tahun ${data.year_previous}</td>
                            <td class="py-2.5 text-right rekap-val-prev-vol">${volPrev.toLocaleString('id-ID', { maximumFractionDigits: 2 })} L</td>
                            <td class="py-2.5 text-right rekap-val-prev-cost">${formatRp(costPrev)}</td>
                        </tr>
                    `;
                }

                const selisihCost = costCur - costPrev;
                const selisihValEl = document.getElementById('rekap-selisih-value');
                if (selisihValEl) {
                    if (selisihCost > 0) {
                        selisihValEl.textContent = 'Rp +' + Math.abs(selisihCost).toLocaleString('id-ID');
                    } else if (selisihCost < 0) {
                        selisihValEl.textContent = 'Rp -' + Math.abs(selisihCost).toLocaleString('id-ID');
                    } else {
                        selisihValEl.textContent = 'Rp 0';
                    }
                    selisihValEl.className = "rekap-summary-value";
                    if (selisihCost < 0) {
                        selisihValEl.classList.add("rekap-selisih-saving");
                    } else if (selisihCost > 0) {
                        selisihValEl.classList.add("rekap-selisih-increase");
                    }
                }

                const badgeEl = document.getElementById('rekap-selisih-badge');
                if (badgeEl) {
                    if (costPrev <= 0) {
                        badgeEl.style.display = 'none';
                    } else {
                        badgeEl.style.display = 'inline-flex';
                        const pct = (selisihCost / costPrev) * 100;
                        const sign = pct > 0 ? '+' : '';
                        badgeEl.textContent = sign + pct.toFixed(1) + '%';
                        
                        badgeEl.className = "inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold";
                        if (pct > 0) {
                            badgeEl.classList.add("rekap-badge-up");
                        } else if (pct < 0) {
                            badgeEl.classList.add("rekap-badge-down");
                        } else {
                            badgeEl.classList.add("rekap-badge-flat");
                        }
                    }
                }

                const avgPriceEl = document.getElementById('rekap-rata-harga');
                if (avgPriceEl) {
                    const avg = volCur > 0 ? Math.round(costCur / volCur) : 0;
                    avgPriceEl.textContent = formatRp(avg);
                }

                const totalTrxEl = document.getElementById('rekap-total-trx');
                if (totalTrxEl) {
                    totalTrxEl.textContent = trxCur.toLocaleString('id-ID');
                }
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
                const iconCls = (d.shift_icon_class && String(d.shift_icon_class).replace(/[^a-z0-9 _-]/gi, '').trim()) || 'bi bi-moon-fill';
                const shiftHtml = `<span class="bbm-shift-badge ${esc(badgeCls)}"><i class="${esc(iconCls)}" aria-hidden="true"></i>${esc(d.shift_label || '—')}</span>`;
                function photoThumb(url, alt, overlayLabel) {
                    if (!url) return '<p class="portal-empty" style="padding:8px">—</p>';
                    const safe = String(url).replace(/"/g, '&quot;');
                    const imgClass =
                        alt === 'Odometer'
                            ? 'sppd-photo-thumb bbm-photo-thumb--odometer-grid'
                            : 'sppd-photo-thumb';
                    const labelHtml = overlayLabel
                        ? `<span class="bbm-photo-overlay-label" aria-hidden="true">${esc(overlayLabel)}</span>`
                        : '';
                    return `<div class="bbm-photo-thumb-wrap">${labelHtml}<button type="button" class="bbm-photo-thumb-btn" data-full-url="${safe}" aria-label="Perbesar ${esc(alt)}"><img src="${safe}" class="${imgClass}" alt="${esc(alt)}"></button></div>`;
                }
                const odo = photoThumb(d.odometer_photo_url, 'Odometer', null);
                const struk = photoThumb(d.struk_photo_url, 'Struk', null);
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

            /* ── Data Laporan BBM: filter & halaman real-time (AJAX, tanpa reload) ── */
            let _page = 1;
            let _perPage = {{ (int) $reports->perPage() }};
            let _sort = '{{ $activeSort ?? "" }}';
            let _dir = '{{ $activeDir ?? "" }}';
            let _abortBbm = null;

            const bbmSearchEl = document.getElementById('bbm-portal-filter-q');
            const bbmJenisEl = document.getElementById('bbm-portal-filter-jenis-pengisian');
            const bbmMonthEl = document.getElementById('bbm-portal-filter-month');
            const bbmYearEl = document.getElementById('bbm-portal-filter-year');
            const bbmPerPageEl = document.getElementById('bbm-portal-filter-per-page');
            const bbmLiveRoot = document.getElementById('bbm-portal-live-root');
            const bbmClearBtn = document.getElementById('bbm-portal-filter-clear');
            const bbmResetBtn = document.getElementById('bbm-portal-filter-reset');

            function showBbmLoading() { const el = document.getElementById('bbm-portal-loading'); if (el) el.style.display = 'flex'; }
            function hideBbmLoading() { const el = document.getElementById('bbm-portal-loading'); if (el) el.style.display = 'none'; }

            function buildBbmParams() {
                const obj = {
                    q:                bbmSearchEl?.value.trim() ?? '',
                    jenis_pengisian:  bbmJenisEl?.value ?? '',
                    month:            bbmMonthEl?.value ?? '',
                    year:             bbmYearEl?.value ?? '',
                    per_page:         _perPage,
                    page:             _page,
                };
                if (_sort) { obj.sort = _sort; obj.dir = _dir; }
                return new URLSearchParams(
                    Object.fromEntries(Object.entries(obj).filter(([, v]) => v !== '' && v != null))
                ).toString();
            }

            function updateBbmFilterChrome() {
                const hasSearch = bbmSearchEl && bbmSearchEl.value.trim().length > 0;
                if (bbmClearBtn) bbmClearBtn.style.display = hasSearch ? 'flex' : 'none';
                const showReset = hasSearch
                    || (bbmJenisEl && bbmJenisEl.value !== '')
                    || (bbmMonthEl && bbmMonthEl.value !== '')
                    || (bbmYearEl && bbmYearEl.value !== '')
                    || _perPage !== 25; // default is 25 in controller
                if (bbmResetBtn) bbmResetBtn.style.display = showReset ? '' : 'none';
            }

            async function fetchBbmReports(scroll = false) {
                if (BBM_PORTAL_CHARTS_ONLY) return;
                _abortBbm?.abort();
                _abortBbm = new AbortController();
                showBbmLoading();

                const q = buildBbmParams();
                const INDEX_URL = @json(route('admin.portal-bbm-operasional'));
                try {
                    const res = await fetch(`${INDEX_URL}?${q}`, {
                        headers: {
                            'Accept': 'text/html',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-VMS-BBM-Portal-Fragment': '1'
                        },
                        signal: _abortBbm.signal
                    });
                    const html = await res.text();

                    if (bbmLiveRoot) {
                        bbmLiveRoot.innerHTML = html;
                    }

                    bindBbmSorting();
                    bindBbmPagination();
                    updateBbmFilterChrome();

                    if (scroll && bbmLiveRoot) {
                        bbmLiveRoot.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                } catch (e) {
                    if (e.name !== 'AbortError') console.warn('BBM fetchBbmReports error', e);
                } finally {
                    hideBbmLoading();
                }
            }

            function bindBbmPagination() {
                if (!bbmLiveRoot) return;
                const paginationLinks = bbmLiveRoot.querySelectorAll('.tbl-pagination a[href], a[href]');
                paginationLinks.forEach(link => {
                    link.addEventListener('click', (e) => {
                        const u = new URL(link.getAttribute('href'), location.origin);
                        e.preventDefault();
                        _page = parseInt(u.searchParams.get('page') || '1', 10);
                        fetchBbmReports(true);
                    });
                });
            }

            function bindBbmSorting() {
                if (window.AdminTableSort && bbmLiveRoot) {
                    const tableWrap = bbmLiveRoot.querySelector('.admin-table-wrap');
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
                                _dir = url.searchParams.get('dir') || '';
                                _page = 1;
                                fetchBbmReports();
                            },
                        });
                    }
                }
            }

            function debounce(fn, ms = 380) {
                let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
            }

            const debouncedBbmFetch = debounce(() => { _page = 1; fetchBbmReports(); });

            if (bbmSearchEl) {
                bbmSearchEl.addEventListener('input', () => {
                    updateBbmFilterChrome();
                    debouncedBbmFetch();
                });
            }

            [bbmJenisEl, bbmMonthEl, bbmYearEl].forEach(el => {
                el?.addEventListener('change', () => {
                    _page = 1;
                    fetchBbmReports();
                });
            });

            if (bbmPerPageEl) {
                bbmPerPageEl.addEventListener('change', (e) => {
                    _perPage = parseInt(e.target.value, 10);
                    _page = 1;
                    fetchBbmReports();
                });
            }

            if (bbmClearBtn) {
                bbmClearBtn.addEventListener('click', () => {
                    if (bbmSearchEl) bbmSearchEl.value = '';
                    updateBbmFilterChrome();
                    _page = 1;
                    fetchBbmReports();
                });
            }

            if (bbmResetBtn) {
                bbmResetBtn.addEventListener('click', () => {
                    if (bbmSearchEl) bbmSearchEl.value = '';
                    if (bbmJenisEl) bbmJenisEl.selectedIndex = 0;
                    if (bbmMonthEl) bbmMonthEl.selectedIndex = 0;
                    if (bbmYearEl) bbmYearEl.selectedIndex = 0;
                    if (bbmPerPageEl) { bbmPerPageEl.value = '25'; _perPage = 25; }
                    _page = 1; _sort = ''; _dir = '';
                    updateBbmFilterChrome();
                    fetchBbmReports();
                });
            }

            // Initial binding for live log table
            bindBbmSorting();
            bindBbmPagination();
            updateBbmFilterChrome();

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

            document.getElementById('rekap-period-select')?.addEventListener('change', () => {
                updateRekapitulasiIndex();
            });

            let bbmPieResizeTimer = null;
            window.addEventListener('resize', () => {
                clearTimeout(bbmPieResizeTimer);
                bbmPieResizeTimer = setTimeout(() => {
                    redrawComparisonFromCache();
                }, 200);
            }, { passive: true });

            /* Expose rebuild fn so the single document-level theme listener always calls the latest closure */
            window._bbmPortalRebuildCharts = function () {
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

