@extends('layouts.dash-app')

@section('title', 'Log Penggunaan Kendaraan')
@section('bodyClass', 'vehicle-usage-form-page')
@section('pageTitle', 'Log Penggunaan Kendaraan')
@section('pageSubtitle', 'Isi log pemakaian kendaraan')

@push('styles')
<meta name="turbo-cache-control" content="no-cache">
@endpush

@php
    $bbmAwalOld = old('level_bbm_awal');
    $bbmAwalNum = is_numeric($bbmAwalOld) ? max(0, min(100, (int) $bbmAwalOld)) : 50;
    $bbmAkhirOld = old('level_bbm_akhir');
    $bbmAkhirNum = is_numeric($bbmAkhirOld) ? max(0, min(100, (int) $bbmAkhirOld)) : 50;
@endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
@vite(['resources/js/vehicle-usage-log.js'])
@endpush

@push('styles')
<style>
    /* Ringkasan langkah + dialog SweetAlert selaras tema checklist */
    .vul-review {
        padding: 0;
        border-radius: 12px;
        background: transparent;
        border: none;
        font-size: 0.88rem;
    }
    html.dark .dash-body .vul-review {
        background: transparent;
        border-color: transparent;
        color: #e2e8f0;
    }
    
    /* Info table di dalam modal preview */
    .vul-review .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
        font-size: 0.82rem;
    }
    .vul-review .info-table td {
        border: 1px solid #e2e8f0;
        padding: 7px 10px;
        vertical-align: top;
    }
    .vul-review .info-table .label {
        font-weight: 700;
        background: #f1f5f9;
        color: #475569;
        width: 38%;
    }
    html.dark .dash-body .vul-review .info-table td {
        border-color: rgba(255, 255, 255, 0.1);
        color: #e2e8f0;
    }
    html.dark .dash-body .vul-review .info-table .label {
        background: rgba(8, 20, 50, 0.55);
        color: #94a3b8;
    }

    .vul-review-group h4 {
        margin: 0 0 8px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
    }
    html.dark .dash-body .vul-review-group h4 { color: #94a3b8; }
    .vul-review-dl { margin: 0; display: grid; gap: 6px; }
    .vul-review-dl > div { display: grid; grid-template-columns: minmax(100px, 140px) 1fr; gap: 10px; align-items: start; }
    .vul-review-dl dt { margin: 0; font-weight: 600; color: #475569; font-size: 0.8rem; }
    html.dark .dash-body .vul-review-dl dt { color: #cbd5e1; }
    .vul-review-dl dd { margin: 0; color: #0f172a; line-height: 1.45; word-break: break-word; }
    html.dark .dash-body .vul-review-dl dd { color: #f1f5f9; }

    .vehicle-usage-form-page .wizard-step .section-banner:not(:first-child) { margin-top: 28px; }
    .vul-bbm-stack { display: grid; gap: 1rem; }
    @media (min-width: 640px) {
        .vul-bbm-stack { grid-template-columns: 1fr 1fr; gap: 1rem 1.25rem; }
    }

    .vul-swal-dialog .swal2-icon {
        box-sizing: content-box !important;
    }
    .vul-swal-dialog .swal2-icon * {
        box-sizing: content-box !important;
    }
    .swal2-popup.vul-swal-dialog .swal2-success-circular-line-left,
    .swal2-popup.vul-swal-dialog .swal2-success-circular-line-right,
    .swal2-popup.vul-swal-dialog .swal2-success-fix {
        background: transparent !important;
    }
    .swal2-popup.vul-swal-dialog {
        border-radius: 16px !important;
        padding: 1.35rem 1.25rem 1.5rem !important;
        border: 1px solid rgba(11, 44, 107, 0.12);
        background: rgba(255, 255, 255, 0.9) !important;
        width: 420px !important;
        max-width: calc(100% - 32px) !important;
    }
    html.dark .dash-body .swal2-popup.vul-swal-dialog {
        color: #f3f4f6 !important;
        background: rgba(16, 38, 80, 0.78) !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    .swal2-title.vul-swal-title {
        font-size: 1.15rem !important;
        font-weight: 800 !important;
        color: #0f172a !important;
    }
    html.dark .dash-body .swal2-title.vul-swal-title { color: #f1f5f9 !important; }
    .vul-swal-list {
        text-align: left;
        margin: 0.25rem 0 0;
        padding-left: 1.2rem;
        line-height: 1.55;
        font-size: 0.9rem;
        color: #334155;
    }
    html.dark .dash-body .vul-swal-list { color: #e2e8f0; }
    .vul-swal-list li { margin: 0.4rem 0; }
    /* Tombol SweetAlert2 — Swal 11 memakai .swal2-confirm / .swal2-cancel (bukan selalu .swal2-styled) */
    .vul-swal-dialog .swal2-actions {
        margin: 1.25rem auto 0 !important;
        gap: 12px !important;
        width: 100% !important;
        max-width: 100% !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
    }
    .vul-swal-dialog button.swal2-confirm,
    .vul-swal-dialog .swal2-styled.swal2-confirm {
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
    .vul-swal-dialog button.swal2-confirm:hover,
    .vul-swal-dialog .swal2-styled.swal2-confirm:hover {
        box-shadow: 0 6px 18px rgba(11, 44, 107, 0.38) !important;
        transform: translateY(-1px);
    }
    .vul-swal-dialog button.swal2-cancel,
    .vul-swal-dialog .swal2-styled.swal2-cancel {
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
    .vul-swal-dialog button.swal2-cancel:hover,
    .vul-swal-dialog .swal2-styled.swal2-cancel:hover {
        background: #f1f5f9 !important;
        border-color: #94a3b8 !important;
    }
    html.dark .dash-body .vul-swal-dialog button.swal2-cancel,
    html.dark .dash-body .vul-swal-dialog .swal2-styled.swal2-cancel {
        background: rgba(30, 41, 59, 0.8) !important;
        border-color: rgba(148, 163, 184, 0.35) !important;
        color: #e2e8f0 !important;
    }
    .vul-swal-error-box {
        text-align: left;
        margin-top: 0.75rem;
        padding: 12px 14px;
        border-radius: 12px;
        background: #fef2f2;
        border: 1px solid #fecaca;
    }
    html.dark .dash-body .vul-swal-error-box {
        background: rgba(127, 29, 29, 0.25);
        border-color: rgba(248, 113, 113, 0.35);
    }
    .vul-swal-lead {
        margin: 0 0 6px;
        font-size: 0.82rem;
        color: #64748b;
        line-height: 1.45;
    }
    html.dark .dash-body .vul-swal-lead {
        color: #94a3b8;
    }

    .vul-footer-actions {
        display: flex !important;
        width: 100% !important;
        min-width: 0 !important;
        gap: 10px;
    }
    .vul-footer-actions .checklist-nav-next {
        flex: 1 !important;
        width: 100% !important;
        min-width: 0 !important;
        justify-content: center !important;
    }
    /* Override grid footer agar tombol penuh tanpa kolom back */
    .vehicle-usage-form-page .checklist-footer {
        grid-template-columns: 1fr !important;
    }
    html.dark .dash-body .swal2-html-container {
        color: #e2e8f0 !important;
    }
    html.dark .dash-body .vul-step4-hint strong {
        color: #facc15 !important;
    }

    /* ── Modal Preview Log Penggunaan ── */
    #vul-preview-overlay {
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
        animation: vulOverlayIn 0.2s ease;
    }
    #vul-preview-overlay.active {
        display: flex;
    }
    @keyframes vulOverlayIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    .vul-preview-modal {
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
        animation: vulModalIn 0.25s cubic-bezier(0.34,1.56,0.64,1);
    }
    html.dark .dash-body .vul-preview-modal {
        background: #0f172a;
        border: 1px solid rgba(71,85,105,0.35);
        box-shadow: 0 24px 80px rgba(0,0,0,0.55);
    }
    @keyframes vulModalIn {
        from { opacity: 0; transform: translateY(28px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .vul-preview-modal-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 18px 20px 14px;
        border-bottom: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    html.dark .dash-body .vul-preview-modal-header {
        border-bottom-color: rgba(71,85,105,0.35);
    }
    .vul-preview-modal-close {
        flex-shrink: 0;
    }
    .vul-preview-modal-header-icon {
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
    .vul-preview-modal-title {
        flex: 1;
        min-width: 0;
    }
    .vul-preview-modal-title h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.01em;
    }
    html.dark .dash-body .vul-preview-modal-title h3 { color: #f1f5f9; }
    .vul-preview-modal-title p {
        margin: 2px 0 0;
        font-size: 0.75rem;
        color: #64748b;
    }
    html.dark .dash-body .vul-preview-modal-title p { color: #94a3b8; }
    .vul-preview-modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 18px 20px;
        -webkit-overflow-scrolling: touch;
    }
    .vul-preview-modal-footer {
        padding: 14px 20px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-shrink: 0;
    }
    html.dark .dash-body .vul-preview-modal-footer {
        border-top-color: rgba(71,85,105,0.35);
    }
    .vul-preview-modal-footer .vul-modal-cancel-btn {
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
    .vul-preview-modal-footer .vul-modal-cancel-btn:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
    }
    .vul-submit-btn {
        width: 100%;
        min-height: 52px;
        font-size: 0.95rem;
        border-radius: 14px;
        box-shadow: 0 6px 20px rgba(5, 45, 127, 0.35);
    }

    html.dark .dash-body .vul-preview-modal-footer .vul-modal-cancel-btn {
        border-color: rgba(71,85,105,0.5);
        color: #94a3b8;
    }
    html.dark .dash-body .vul-preview-modal-footer .vul-modal-cancel-btn:hover {
        background: rgba(71,85,105,0.2);
        color: #f1f5f9;
    }

    /* ── 3D White Slider Thumb for BBM Level ── */
    .bbm-slider {
        -webkit-appearance: none;
        width: 90%;
        height: 6px;
        border-radius: 999px;
        outline: none;
        background: #e5e7eb;
    }
    .bbm-slider::-webkit-slider-runnable-track {
        height: 6px;
        border-radius: 999px;
    }
    .bbm-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        height: 19px;
        width: 19px;
        border-radius: 50%;
        background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%) !important;
        border: 1px solid rgba(0, 0, 0, 0.15) !important;
        margin-top: -7px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.22), 0 4px 10px rgba(0,0,0,0.12), inset 0 -2px 4px rgba(0,0,0,0.08), inset 0 2px 3px rgba(255,255,255,1) !important;
        cursor: pointer;
        transition: transform 0.1s ease, box-shadow 0.1s ease;
    }
    .bbm-slider::-webkit-slider-thumb:hover {
        transform: scale(1.08);
        box-shadow: 0 2px 4px rgba(0,0,0,0.24), 0 5px 12px rgba(0,0,0,0.16), inset 0 -2px 4px rgba(0,0,0,0.08), inset 0 2px 3px rgba(255,255,255,1) !important;
    }
    .bbm-slider::-webkit-slider-thumb:active {
        transform: scale(0.96);
    }
    
    .bbm-slider::-moz-range-track {
        height: 6px;
        border-radius: 999px;
        background: #e5e7eb;
    }
    .bbm-slider::-moz-range-progress {
        background: #facc15;
        height: 6px;
        border-radius: 999px;
    }
    .bbm-slider::-moz-range-thumb {
        height: 22px;
        width: 22px;
        border-radius: 50%;
        background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%) !important;
        border: 1px solid rgba(0, 0, 0, 0.15) !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.22), 0 4px 10px rgba(0,0,0,0.12), inset 0 -2px 4px rgba(0,0,0,0.08), inset 0 2px 3px rgba(255,255,255,1) !important;
        cursor: pointer;
        transition: transform 0.1s ease, box-shadow 0.1s ease;
    }
    .bbm-slider::-moz-range-thumb:hover {
        transform: scale(1.08);
        box-shadow: 0 2px 4px rgba(0,0,0,0.24), 0 5px 12px rgba(0,0,0,0.16), inset 0 -2px 4px rgba(0,0,0,0.08), inset 0 2px 3px rgba(255,255,255,1) !important;
    }
    .bbm-slider::-moz-range-thumb:active {
        transform: scale(0.96);
    }
    .bbm-value {
        color: #1e3a8a;
    }
    html.dark .dash-body .bbm-value { color: #3b82f6; }
</style>
@endpush

@section('content')
<div class="checklist-shell" data-vehicle-usage-form>
    <main class="checklist-content">
        <form id="vehicle-usage-log-form" class="checklist-card" action="{{ route('vehicle-usage-logs.store') }}" method="post" data-dashboard-url="{{ route('dashboard') }}" novalidate>
            @csrf

            @if ($errors->any())
                <div class="bbm-nojs-errors" role="alert">
                    <strong>Periksa kembali:</strong>
                    <ul>@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="checklist-progress-head">
                <div class="checklist-progress-info">
                    <span id="vul-step-label" style="font-size: 1.3em;"><i class="bi bi-truck-front-fill banner-icon"></i> DATA PENGGUNAAN KENDARAAN</span>
                </div>
            </div>

            {{-- Langkah 1 — Data Penggunaan + Level BBM & Kilometer + Kondisi Kendaraan --}}
            <section class="wizard-step active" data-step="1">
                <div class="checklist-grid-two">
                    <label class="checklist-field">
                        <div class="checklist-control-wrap">
                            <input type="text" id="vul-nama" readonly class="checklist-input-readonly" value="{{ $user->name ?? $user->username }}" autocomplete="name">
                        </div>
                    </label>
                    <label class="checklist-field">
                        <div class="checklist-control-wrap checklist-control-select">
                            <select name="nomor_kendaraan" id="vul-nopol">
                                <option value="">Pilih nomor kendaraan</option>
                                @foreach ($kendaraans as $k)
                                    <option value="{{ $k->nomor_kendaraan }}" data-jenis="{{ $k->jenis_kendaraan }}" @selected(old('nomor_kendaraan') === $k->nomor_kendaraan)>{{ $k->nomor_kendaraan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </label>
                    <label class="checklist-field checklist-field-span2">
                        <div class="checklist-control-wrap">
                            <input type="text" id="vul-jenis" readonly class="checklist-input-readonly" value="" placeholder="Otomatis dari no. kendaraan" autocomplete="off">
                        </div>
                    </label>
                </div>
                <div class="checklist-grid-two">
                    <label class="checklist-field">
                        <span><i class="bi bi-clock bbm-field-icon" aria-hidden="true"></i> Jam Penggunaan</span>
                        <div class="checklist-control-wrap checklist-control-time">
                            <input type="time" name="jam_awal" id="vul-jam-awal" value="{{ old('jam_awal') }}">
                        </div>
                    </label>
                    <label class="checklist-field">
                        <span><i class="bi bi-clock-history bbm-field-icon" aria-hidden="true"></i> Jam Selesai</span>
                        <div class="checklist-control-wrap checklist-control-time">
                            <input type="time" name="jam_akhir" id="vul-jam-akhir" value="{{ old('jam_akhir') }}">
                        </div>
                    </label>
                    <label class="checklist-field checklist-field-span2">
                        <div class="checklist-control-wrap">
                            <textarea name="keperluan" id="vul-keperluan" rows="4" placeholder="Jelaskan keperluan penggunaan kendaraan…" maxlength="10000">{{ old('keperluan') }}</textarea>
                        </div>
                    </label>
                </div>

                <label class="checklist-field checklist-field-span2">
                    <span>Level BBM &amp; KM Odometer</span>
                </label>
                <div class="vul-bbm-stack">
                    <div class="bbm-card">
                        <div class="bbm-header"><span class="bbm-label">LEVEL BBM AWAL</span><span class="bbm-value" id="vul-bbm-display-awal">{{ $bbmAwalNum }}<small>%</small></span></div>
                        <div class="bbm-slider-wrap">
                            <input type="range" min="0" max="100" step="1" value="{{ $bbmAwalNum }}" id="vul-bbm-slider-awal" class="bbm-slider" aria-label="Level BBM awal persen">
                        </div>
                        <div class="bbm-scale"><span>E (EMPTY)</span><span>F (FULL)</span></div>
                        <input type="hidden" name="level_bbm_awal" id="vul-bbm-awal" value="{{ $bbmAwalNum }}">
                    </div>
                    <div class="bbm-card">
                        <div class="bbm-header"><span class="bbm-label">LEVEL BBM AKHIR</span><span class="bbm-value" id="vul-bbm-display-akhir">{{ $bbmAkhirNum }}<small>%</small></span></div>
                        <div class="bbm-slider-wrap">
                            <input type="range" min="0" max="100" step="1" value="{{ $bbmAkhirNum }}" id="vul-bbm-slider-akhir" class="bbm-slider" aria-label="Level BBM akhir persen">
                        </div>
                        <div class="bbm-scale"><span>E (EMPTY)</span><span>F (FULL)</span></div>
                        <input type="hidden" name="level_bbm_akhir" id="vul-bbm-akhir" value="{{ $bbmAkhirNum }}">
                    </div>
                </div>
                <div class="km-row" style="margin-top:14px">
                    <div class="km-card">
                        <span class="km-card-label">KM AWAL</span>
                        <input type="number" name="km_awal" id="vul-km-awal" min="0" step="1" inputmode="numeric" value="{{ old('km_awal') }}" placeholder="0" class="km-card-value km-card-editable">
                    </div>
                    <div class="km-card">
                        <span class="km-card-label">KM AKHIR</span>
                        <input type="number" name="km_akhir" id="vul-km-akhir" min="0" step="1" inputmode="numeric" value="{{ old('km_akhir') }}" placeholder="0" class="km-card-value km-card-editable">
                    </div>
                </div>

                <div class="checklist-grid-two" style="margin-top: 10px;">
                    <label class="checklist-field checklist-field-span2">
                        <span>Kondisi sebelum penggunaan</span>
                        <div class="checklist-control-wrap">
                            <textarea name="kondisi_sebelum_penggunaan" id="vul-kondisi-sebelum" rows="5" maxlength="10000" placeholder="Catat kondisi body, ban, interior, dan hal penting lain sebelum berangkat…">{{ old('kondisi_sebelum_penggunaan') }}</textarea>
                        </div>
                    </label>
                    <label class="checklist-field checklist-field-span2">
                        <span>Kondisi setelah penggunaan</span>
                        <div class="checklist-control-wrap">
                            <textarea name="kondisi_setelah_penggunaan" id="vul-kondisi-sesudah" rows="5" maxlength="10000" placeholder="Catat perubahan atau kerusakan setelah pemakaian…">{{ old('kondisi_setelah_penggunaan') }}</textarea>
                        </div>
                    </label>
                </div>
            </section>
            <footer class="checklist-footer" style="margin-top: 20px;">
                <div class="vul-footer-actions">
                    <button type="button" class="checklist-nav-btn checklist-nav-next" id="vul-next" style="width:100%">
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
{{-- ── Modal Preview Log Penggunaan ── --}}
<div id="vul-preview-overlay" role="dialog" aria-modal="true" aria-labelledby="vul-preview-modal-title">
    <div class="vul-preview-modal">
        <div class="vul-preview-modal-header">
            <div class="vul-preview-modal-header-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/><path d="M9 12h6M9 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <div class="vul-preview-modal-title">
                <h3 id="vul-preview-modal-title">Ringkasan Laporan Penggunaan</h3>
                <p>Periksa kembali data sebelum mengirim.</p>
            </div>
            <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite vul-preview-modal-close" id="vul-preview-close" title="Tutup" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="vul-preview-modal-body sppd-detail-html">
            <div class="vul-review" id="vul-review-root" aria-live="polite"></div>
        </div>
        <div class="vul-preview-modal-footer">
            <button type="button" class="vul-modal-cancel-btn" id="vul-preview-cancel">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Kembali
            </button>
            <button type="submit" form="vehicle-usage-log-form" class="checklist-nav-btn checklist-nav-next final vul-submit-btn" id="vul-submit">
                <i class="bi bi-send-fill vul-submit-icon" aria-hidden="true"></i>
                Kirim log
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const dashUrl = @json(route('dashboard'));
    const vulOk = @json(session('vul_ok'));

    if (typeof Swal === 'undefined') return;

    if (vulOk) {
        Swal.fire({
            icon: 'success',
            iconColor: '#16a34a',
            title: 'Berhasil',
            text: vulOk,
            confirmButtonText: 'Kembali ke Dashboard',
            customClass: {
                popup: 'vul-swal-dialog',
                title: 'vul-swal-title',
                confirmButton: 'vul-swal-confirm',
            },
            buttonsStyling: false,
        }).then((r) => { if (r.isConfirmed) window.location.href = dashUrl; });
    }
})();
</script>
@endpush
