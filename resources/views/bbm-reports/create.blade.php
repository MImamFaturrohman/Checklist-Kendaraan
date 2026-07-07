@extends('layouts.dash-app')

@section('title', 'Form Pengisian BBM')
@section('bodyClass', 'bbm-form-page')
@section('pageTitle', 'Form Pengisian BBM')

@push('styles')
<meta name="turbo-cache-control" content="no-cache">
@endpush


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
@vite(['resources/js/bbm-form.js'])
@endpush

@push('styles')
@php
    $bbmShiftCode = $driverShiftAtLogin['code'] ?? 'luar';
    $isPicKendaraan = ($user->role ?? null) === 'pic_kendaraan';
    $shiftLabel = match($bbmShiftCode) {
        'pagi'  => 'Pagi',
        'siang' => 'Siang',
        default => 'Di Luar Shift'
    };
@endphp
<style>
    .bbm-form-title {
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: #0b2c6b;
    }
    html.dark .dash-body .bbm-form-title { color: #D4AF37; }
    .bbm-form-title i { color: #D4AF37; }

    .bbm-form-page .section-banner {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 12px;
        background: linear-gradient(90deg, #0b2c6b 0%, #123f8f 50%, #3b5fa8 75%, #dfe6f3 100%);
        color: white;
        font-weight: 600;
        font-size: 16px;
        position: relative;
        overflow: hidden;
        box-shadow: none;
    }
    .bbm-form-page .section-banner::before {
        content: "";
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 6px;
        background: #facc15;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }
    .bbm-form-page .section-banner-icon { color: #facc15; flex-shrink: 0; position: relative; z-index: 1; }
    .bbm-form-page .section-banner span { position: relative; z-index: 1; }
    .bbm-form-page .wizard-step .section-banner:not(:first-child) { margin-top: 28px; }
    .bbm-photo-pair--struk { grid-template-columns: 1fr; }
    @media (min-width: 600px) { .bbm-photo-pair--struk { max-width: 50%; } }
    .bbm-review-photos--struk { grid-template-columns: 1fr !important; margin-top: 8px; }
    @media (min-width: 600px) { .bbm-review-photos--struk { max-width: 50%; } }
    .bbm-form-page .bbm-page-head { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
    .bbm-form-page .bbm-page-head-icon {
        width: 48px; height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #0b2c6b 0%, #123f8f 100%);
        display: flex; align-items: center; justify-content: center;
        color: #facc15; flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(11,44,107,0.25);
    }
    .bbm-form-page .bbm-page-head h1 { margin: 0; font-size: 1.35rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
    html.dark .dash-body .bbm-form-page .bbm-page-head h1 { color: #f1f5f9; }
    .bbm-form-page .bbm-page-head p { margin: 4px 0 0; font-size: 0.82rem; color: #64748b; }
    html.dark .dash-body .bbm-form-page .bbm-page-head p { color: #94a3b8; }

    .bbm-span-input-label { margin-left: 12px; }
    .dash-body input[type="date"]::-webkit-calendar-picker-indicator { filter: none; }
    html.dark .dash-body input[type="date"]::-webkit-calendar-picker-indicator { filter: brightness(0) invert(1); }

    .foto-warning {
        margin:8px 0 0;
        font-size:0.78rem;
        line-height:1.45;
        color:#b45309;
        background:rgba(245,158,11,0.12);
        border:1px solid rgba(245,158,11,0.28);
        padding:10px 12px;
        border-radius:10px;
    }
    html.dark .dash-body .foto-warning {
        color: #1e40af;
        background: rgba(30, 64, 175, 0.12);
        border: 1px solid rgba(30, 64, 175, 0.28);
        filter: brightness(1.5);
    }

    .bbm-footer-actions {
        display: flex !important;
        width: 100% !important;
        min-width: 0 !important;
        gap: 10px;
    }
    .bbm-footer-actions .checklist-nav-next {
        flex: 1 !important;
        width: 100% !important;
        min-width: 0 !important;
        justify-content: center !important;
    }
    /* Override grid footer agar tombol penuh tanpa kolom back */
    .bbm-form-page .checklist-footer {
        grid-template-columns: 1fr !important;
    }

    /* ── Modal Preview BBM ── */
    #bbm-preview-overlay {
        position: fixed;
        inset: 0;
        z-index: 1050;
        background: rgba(0, 0, 0, 0.55);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        animation: bbmOverlayIn 0.2s ease;
    }
    #bbm-preview-overlay.active {
        display: flex;
    }
    @keyframes bbmOverlayIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    .bbm-preview-modal {
        position: relative;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 24px 80px rgba(11, 44, 107, 0.22), 0 4px 20px rgba(0,0,0,0.12);
        width: 100%;
        max-width: 620px;
        max-height: 88vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        animation: bbmModalIn 0.25s cubic-bezier(0.34,1.56,0.64,1);
    }
    html.dark .dash-body .bbm-preview-modal {
        background: #0f172a;
        border: 1px solid rgba(71,85,105,0.35);
        box-shadow: 0 24px 80px rgba(0,0,0,0.55);
    }
    @keyframes bbmModalIn {
        from { opacity: 0; transform: translateY(28px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .bbm-preview-modal-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 18px 20px 14px;
        border-bottom: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    html.dark .dash-body .bbm-preview-modal-header {
        border-bottom-color: rgba(71,85,105,0.35);
    }
    .bbm-preview-modal-close {
        flex-shrink: 0;
    }
    .bbm-preview-modal-header-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg, #0b2c6b 0%, #123f8f 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #facc15;
        flex-shrink: 0;
    }
    .bbm-preview-modal-title {
        flex: 1;
        min-width: 0;
    }
    .bbm-preview-modal-title h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.01em;
    }
    html.dark .dash-body .bbm-preview-modal-title h3 { color: #f1f5f9; }
    .bbm-preview-modal-title p {
        margin: 2px 0 0;
        font-size: 0.75rem;
        color: #64748b;
    }
    html.dark .dash-body .bbm-preview-modal-title p { color: #94a3b8; }
    .bbm-preview-modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 18px 20px;
        -webkit-overflow-scrolling: touch;
    }
    .bbm-preview-modal-footer {
        padding: 14px 20px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-shrink: 0;
    }
    html.dark .dash-body .bbm-preview-modal-footer {
        border-top-color: rgba(71,85,105,0.35);
    }
    .bbm-preview-modal-footer .bbm-modal-cancel-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 18px;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: transparent;
        color: #475569;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s, color 0.15s;
    }
    .bbm-preview-modal-footer .bbm-modal-cancel-btn:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
    }
    html.dark .dash-body .bbm-preview-modal-footer .bbm-modal-cancel-btn {
        border-color: rgba(71,85,105,0.5);
        color: #94a3b8;
    }
    html.dark .dash-body .bbm-preview-modal-footer .bbm-modal-cancel-btn:hover {
        background: rgba(71,85,105,0.2);
        color: #f1f5f9;
    }

    .bbm-review {
        padding: 0;
        border-radius: 12px;
        background: transparent;
        border: none;
        font-size: 0.88rem;
    }
    html.dark .dash-body .bbm-review {
        background: transparent;
        border-color: transparent;
        color: #e2e8f0;
    }
    /* Info table di dalam modal preview — sama dengan sppd-detail-html */
    .bbm-review .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
        font-size: 0.82rem;
    }
    .bbm-review .info-table td {
        border: 1px solid #e2e8f0;
        padding: 7px 10px;
        vertical-align: top;
    }
    .bbm-review .info-table .label {
        font-weight: 700;
        background: #f1f5f9;
        color: #475569;
        width: 38%;
    }
    html.dark .dash-body .bbm-review .info-table td {
        border-color: rgba(255, 255, 255, 0.1);
        color: #e2e8f0;
    }
    html.dark .dash-body .bbm-review .info-table .label {
        background: rgba(8, 20, 50, 0.55);
        color: #94a3b8;
    }
    .bbm-review-group h4 {
        margin: 0 0 8px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
    }
    html.dark .dash-body .bbm-review-group h4 { color: #94a3b8; }
    .bbm-review-dl { margin: 0; display: grid; gap: 6px; }
    .bbm-review-dl > div { display: grid; grid-template-columns: minmax(100px, 140px) 1fr; gap: 10px; align-items: start; }
    .bbm-review-dl dt { margin: 0; font-weight: 600; color: #475569; font-size: 0.8rem; }
    html.dark .dash-body .bbm-review-dl dt { color: #cbd5e1; }
    .bbm-review-dl dd { margin: 0; color: #0f172a; line-height: 1.45; word-break: break-word; }
    html.dark .dash-body .bbm-review-dl dd { color: #f1f5f9; }
    .bbm-review-photos {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 4px;
    }
    .label-mobile {
        display: none;
    }
    .label-placeholder {
        visibility: hidden;
    }
    @media (max-width: 520px) {
        .bbm-review-photos { grid-template-columns: 1fr; }
        .label-mobile { display: block; }
        .label-placeholder { display: none; }
    }
    .bbm-review-photo-wrap {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        position: relative;
    }
    html.dark .dash-body .bbm-review-photo-wrap {
        border-color: rgba(71, 85, 105, 0.45);
        background: rgba(30, 41, 59, 0.45);
    }
    .bbm-review-photo-wrap img {
        display: block;
        width: 100%;
        height: auto;
        max-height: 180px;
        object-fit: contain;
    }
    /* Overlay label badge di pojok kiri atas foto */
    .bbm-review-photo-overlay-label {
        position: absolute;
        top: 7px;
        left: 7px;
        z-index: 2;
        font-size: clamp(9px, 1.5vw, 12px);
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
    p.bbm-step3-hint {
        margin: 0 0 12px;
        font-size: 0.85rem;
        line-height: 1.45;
        color: #64748b;
    }
    html.dark .dash-body p.bbm-step3-hint { color: #94a3b8; }
    html.dark .dash-body p.bbm-step3-hint strong { color: #facc15; }

    .swal-bbm-icon-success {
        box-sizing: content-box !important;
    }
    .swal-bbm-icon-success * {
        box-sizing: content-box !important;
    }
    .swal2-popup.swal-bbm-popup .swal2-success-circular-line-left,
    .swal2-popup.swal-bbm-popup .swal2-success-circular-line-right,
    .swal2-popup.swal-bbm-popup .swal2-success-fix {
        background: transparent !important;
    }
    .swal2-popup.swal-bbm-popup {
        background: rgba(255, 255, 255, 0.9) !important;
        border-radius: 20px !important;
        width: 420px !important;
        max-width: calc(100% - 32px) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
        border: 1px solid rgba(11, 44, 107, 0.12) !important;
        padding: 1.5rem 1.25rem 1.5rem !important;
    }
    html.dark .dash-body .swal2-popup.swal-bbm-popup {
        color: #f3f4f6 !important;
        background: rgba(16, 38, 80, 0.78) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    .swal-bbm-popup .swal2-actions {
        margin: 1.25rem auto 0 !important;
        gap: 12px !important;
        width: 100% !important;
        max-width: 100% !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
    }
    .swal-bbm-popup button.swal-bbm-confirm {
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
    .swal-bbm-popup button.swal-bbm-confirm:hover {
        box-shadow: 0 6px 18px rgba(11, 44, 107, 0.38) !important;
        transform: translateY(-1px);
    }
    .swal-bbm-popup button.swal-bbm-cancel {
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
    .swal-bbm-popup button.swal-bbm-cancel:hover {
        background: #f1f5f9 !important;
        border-color: #94a3b8 !important;
    }
    html.dark .dash-body .swal-bbm-popup button.swal-bbm-cancel {
        background: rgba(30, 41, 59, 0.8) !important;
        border-color: rgba(148, 163, 184, 0.35) !important;
        color: #e2e8f0 !important;
    }
    html.dark .dash-body .swal-bbm-popup button.swal-bbm-cancel:hover {
        background: rgba(30, 41, 59, 0.95) !important;
        border-color: rgba(148, 163, 184, 0.5) !important;
    }
    .swal-bbm-lead {
        margin: 0 0 6px;
        font-size: 0.82rem;
        color: #64748b;
        line-height: 1.45;
    }
    html.dark .dash-body .swal-bbm-lead { color: #94a3b8; }
    .swal-bbm-title {
        font-size: 1.15rem !important;
        font-weight: 800 !important;
        color: #0f172a !important;
    }

    html.dark .dash-body .swal-bbm-title { color: #f1f5f9 !important; }
    .swal-bbm-error-box {
        text-align: left;
        margin-top: 0.75rem;
        padding: 12px 14px;
        border-radius: 12px;
        background: #fef2f2;
        border: 1px solid #fecaca;
    }
    html.dark .dash-body .swal-bbm-error-box {
        background: rgba(127, 29, 29, 0.25);
        border-color: rgba(248, 113, 113, 0.35);
    }
    .swal-bbm-list {
        text-align: left;
        margin: 0.25rem 0 0;
        padding-left: 1.2rem;
        line-height: 1.55;
        font-size: 0.9rem;
        color: #334155;
    }

    html.dark .dash-body .swal-bbm-list { color: #f1f5f9; }

</style>
@endpush

@section('content')
<div class="checklist-shell" data-bbm-form>
    <main class="checklist-content">
        <form id="bbm-report-form" class="checklist-card" action="{{ route('bbm-reports.store') }}" method="post" enctype="multipart/form-data" data-dashboard-url="{{ route('dashboard') }}" data-shift-badge-class="{{ \App\Support\DriverShift::badgeClassFromCode($bbmShiftCode) }}" data-shift-icon-class="{{ \App\Support\DriverShift::iconClassFromCode($bbmShiftCode) }}" novalidate>
            @csrf
            <input type="hidden" id="bbm-shift-label" value="{{ $shiftLabel }}">

            @if ($errors->any())
                <div class="bbm-nojs-errors" role="alert">
                    <strong>Periksa kembali:</strong>
                    <ul>@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="checklist-progress-head">
                <div class="bbm-form-title">
                    <span style="font-size: 1.3em;"><i class="bi bi-fuel-pump-fill"></i> DATA PENGISIAN BBM</span>
                </div>
            </div>

            <section class="wizard-step active" data-step="1">
                <div class="checklist-grid-two">
                    <label class="checklist-field">
                        <span class="bbm-span-input-label">Data kendaraan</span>
                        <div class="checklist-control-wrap checklist-control-select">
                            <select name="nomor_kendaraan" id="bbm-nopol" required>
                                <option value="">Pilih Nomor Kendaraan</option>
                                @foreach ($kendaraans as $k)
                                    <option value="{{ $k->nomor_kendaraan }}" data-jenis="{{ $k->jenis_kendaraan }}" @if($isPicKendaraan) data-km-current="{{ $k->km_current !== null ? (int) $k->km_current : '' }}" @endif @selected(old('nomor_kendaraan') === $k->nomor_kendaraan)>{{ $k->nomor_kendaraan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </label>
                    <label class="checklist-field">
                        <span class="label-placeholder">&nbsp;</span>
                        <div class="checklist-control-wrap">
                            <input type="text" id="bbm-jenis" readonly class="checklist-input-readonly" value="" placeholder="Otomatis terisi…" autocomplete="off">
                        </div>
                    </label>
                </div>
                <div class="checklist-grid-two bbm-datetime-grid">
                    <label class="checklist-field">
                        <span class="label-mobile">Tanggal</span>
                        <div class="checklist-control-wrap bbm-input-with-icon">
                            <input type="date" name="tanggal" id="bbm-tanggal" required value="{{ old('tanggal') }}">
                        </div>
                    </label>
                    <label class="checklist-field">
                        <span class="label-mobile">Waktu</span>
                        <div class="checklist-control-wrap bbm-input-with-icon">
                            <input type="time" name="waktu" id="bbm-waktu" required value="{{ old('waktu') }}">
                        </div>
                    </label>
                </div>
                <div class="checklist-grid-two">
                    <label class="checklist-field checklist-field-span">
                        <div class="checklist-control-wrap checklist-control-select">
                            <select name="jenis_pengisian" id="bbm-jenis-pengisian" required>
                                <option value="">Pilih Keperluan</option>
                                <option value="Operasional" @selected(old('jenis_pengisian') === 'Operasional')>Operasional</option>
                                <option value="Perjalanan Dinas (SPPD)" @selected(old('jenis_pengisian') === 'Perjalanan Dinas (SPPD)')>Perjalanan Dinas (SPPD)</option>
                            </select>
                        </div>
                    </label>
                </div>
                <div class="checklist-grid-two">
                    <label class="checklist-field">
                        <span class="bbm-span-input-label">KM Odometer</span>
                        <div class="checklist-control-wrap">
                            <input type="number" name="odometer_sebelum" id="bbm-odo-sebelum" required min="0" step="1" inputmode="numeric" value="{{ old('odometer_sebelum') }}" placeholder="Sebelum" data-km-current-hint="{{ $isPicKendaraan ? '1' : '0' }}">
                        </div>
                    </label>
                    <label class="checklist-field">
                        <span class="label-placeholder">&nbsp;</span>
                        <div class="checklist-control-wrap">
                            <input type="number" name="odometer_sesudah" id="bbm-odo-sesudah" required min="0" step="1" inputmode="numeric" value="{{ old('odometer_sesudah') }}" placeholder="Sesudah">
                        </div>
                    </label>
                </div>
                <div class="checklist-grid-two">
                    <label class="checklist-field">
                        <span class="bbm-span-input-label">Detail BBM</span>
                        <div class="checklist-control-wrap">
                            <input type="number" name="liter" id="bbm-liter" required min="0.001" step="0.001" value="{{ old('liter') }}" placeholder="Liter">
                        </div>
                    </label>
                    <label class="checklist-field">
                        <span class="label-placeholder">&nbsp;</span>
                        <div class="checklist-control-wrap">
                            <input type="number" name="harga_per_liter" id="bbm-harga-per-liter" required min="0" step="1" value="{{ old('harga_per_liter') }}" placeholder="Harga">
                        </div>
                    </label>
                    <label class="checklist-field checklist-field-span2 bbm-total-field">
                        <div class="checklist-control-wrap">
                            <input type="text" id="bbm-total-display" readonly class="checklist-input-readonly bbm-total-readonly" value="Rp 0" autocomplete="off" aria-live="polite">
                        </div>
                    </label>
                </div>
                <div class="bbm-photo-pair">
                    <label class="checklist-photo-slot" data-photo-preview-slot>
                        <input type="file" name="foto_odometer_sebelum" id="bbm-foto-odometer-sebelum" accept="image/*" data-photo-single required>
                        <div class="photo-slot-placeholder"><span class="checklist-photo-icon"><i class="bi bi-speedometer2"></i></span><strong>Odometer Sebelum</strong></div>
                        <img class="photo-slot-preview" alt="" style="display:none" src="">
                        <button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus">×</button>
                    </label>
                    <label class="checklist-photo-slot" data-photo-preview-slot>
                        <input type="file" name="foto_odometer_sesudah" id="bbm-foto-odometer-sesudah" accept="image/*" data-photo-single required>
                        <div class="photo-slot-placeholder"><span class="checklist-photo-icon"><i class="bi bi-speedometer"></i></span><strong>Odometer Sesudah</strong></div>
                        <img class="photo-slot-preview" alt="" style="display:none" src="">
                        <button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus">×</button>
                    </label>
                </div>
                <div class="bbm-photo-pair bbm-photo-pair--struk">
                    <label class="checklist-photo-slot" data-photo-preview-slot>
                        <input type="file" name="foto_struk" id="bbm-foto-struk" accept="image/*" data-photo-single required>
                        <div class="photo-slot-placeholder"><span class="checklist-photo-icon"><i class="bi bi-receipt"></i></span><strong>Foto Struk</strong></div>
                        <img class="photo-slot-preview" alt="" style="display:none" src="">
                        <button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus">×</button>
                    </label>
                </div>
                <p class="foto-warning">Pastikan mengambil foto <strong>odometer</strong> dengan kamera <strong>landscape</strong> (horizontal).</p>
            </section>
            <footer class="checklist-footer" style="margin-top: 20px;">
                <div class="bbm-footer-actions">
                    <button type="button" class="checklist-nav-btn checklist-nav-next" id="bbm-next" style="width:100%">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                        Lihat Preview
                    </button>
                </div>
            </footer>
        </form>
    </main>
</div>
@endsection

@section('modals')
{{-- ── Modal Preview BBM ── --}}
<div id="bbm-preview-overlay" role="dialog" aria-modal="true" aria-labelledby="bbm-preview-modal-title">
    <div class="bbm-preview-modal">
        <div class="bbm-preview-modal-header">
            <div class="bbm-preview-modal-header-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/><path d="M9 12h6M9 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <div class="bbm-preview-modal-title">
                <h3 id="bbm-preview-modal-title">Ringkasan Laporan BBM</h3>
                <p>Periksa kembali data sebelum mengirim.</p>
            </div>
            <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite bbm-preview-modal-close" id="bbm-preview-close" title="Tutup" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="bbm-preview-modal-body sppd-detail-html">
            <div class="bbm-review" id="bbm-review-root" aria-live="polite"></div>
        </div>
        <div class="bbm-preview-modal-footer">
            <button type="button" class="bbm-modal-cancel-btn" id="bbm-preview-cancel">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Kembali
            </button>
            <button type="submit" form="bbm-report-form" class="checklist-nav-btn checklist-nav-next final bbm-submit-btn" id="bbm-submit">
                <i class="bi bi-send-fill bbm-submit-icon" aria-hidden="true"></i>
                Kirim Laporan BBM
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const dashUrl = @json(route('dashboard'));
    const bbmOk = @json(session('bbm_ok'));
    const bbmError = @json(session('bbm_error'));

    if (typeof Swal === 'undefined') return;

    if (bbmOk) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: bbmOk,
            customClass: {
                popup: 'swal-bbm-popup',
                title: 'swal-bbm-title',
                text: 'swal-bbm-text',
                confirmButton: 'swal-bbm-confirm'
            }
            confirmButtonText: 'Kembali ke Dashboard',
        }).then((r) => { if (r.isConfirmed) window.location.href = dashUrl; });
        return;
    }

    if (bbmError) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: bbmError,
            customClass: {
                popup: 'swal-bbm-popup',
                title: 'swal-bbm-title',
                text: 'swal-bbm-text',
                confirmButton: 'swal-bbm-confirm'
            }
            confirmButtonText: 'Kembali ke Dashboard',
            showCancelButton: true,
            cancelButtonText: 'Tutup',
        }).then((r) => { if (r.isConfirmed) window.location.href = dashUrl; });
    }
})();
</script>
@endpush
