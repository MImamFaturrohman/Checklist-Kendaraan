@extends('layouts.dash-app')

@section('title', 'Buat Ceklist Baru')

@section('pageTitle', 'Ceklist Kendaraan')
@section('pageSubtitle', 'Buat Ceklist Baru')

@push('styles')
<meta name="turbo-cache-control" content="no-cache">
<style>
    .section-banner {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 12px;
        background: linear-gradient(90deg, #0b2c6b 0%, #123f8f 40%, rgba(59,95,168,0.6) 70%, rgba(223,230,243,0.2) 100%);
        color: white;
        font-weight: 600;
        font-size: 16px;
        position: relative;
        overflow: hidden;
    }
    .section-banner::before {
        content: "";
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 6px;
        background: #facc15;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }
    .section-banner-icon { color: #facc15; flex-shrink: 0; }
    .section-banner span { position: relative; z-index: 1; }

    .bbm-slider {
        -webkit-appearance: none;
        width: 100%;
        height: 6px;
        border-radius: 999px;
        outline: none;
        background: linear-gradient(to right, #facc15 50%, #e5e7eb 50%);
    }
    .bbm-slider::-webkit-slider-runnable-track { height: 6px; border-radius: 999px; }
    .bbm-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        height: 22px; width: 22px;
        border-radius: 50%;
        background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%) !important;
        border: 1px solid rgba(0, 0, 0, 0.15) !important;
        margin-top: -8px;
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

    .bbm-slider::-moz-range-track { height: 6px; border-radius: 999px; background: #e5e7eb; }
    .bbm-slider::-moz-range-progress { background: #facc15; height: 6px; border-radius: 999px; }
    .bbm-slider::-moz-range-thumb {
        height: 22px; width: 22px;
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
</style>
@endpush

@push('styles')
<style>
/* SweetAlert2 — checklist wizard (cl-swal-*) */
.cl-swal-dialog .swal2-icon {
    box-sizing: content-box !important;
}
.cl-swal-dialog .swal2-icon * {
    box-sizing: content-box !important;
}
.swal2-popup.cl-swal-dialog .swal2-success-circular-line-left,
.swal2-popup.cl-swal-dialog .swal2-success-circular-line-right,
.swal2-popup.cl-swal-dialog .swal2-success-fix {
    background: transparent !important;
}
.swal2-popup.cl-swal-dialog {
    border-radius: 16px !important;
    padding: 1.35rem 1.25rem 1.5rem !important;
    border: 1px solid rgba(11, 44, 107, 0.12);
    background: rgba(255, 255, 255, 0.9) !important;
    width: 420px !important;
    max-width: calc(100% - 32px) !important;
}
.dash-body.dark .swal2-popup.cl-swal-dialog {
    background: rgba(16, 38, 80, 0.78) !important;
    border-color: rgba(148, 163, 184, 0.2);
    color: #f1f5f9 !important;
}
.swal2-title.cl-swal-title {
    font-size: 1.15rem !important;
    font-weight: 800 !important;
    color: #0f172a !important;
}
.dash-body.dark .swal2-title.cl-swal-title { color: #f1f5f9 !important; }
.cl-swal-dialog .swal2-actions {
    margin: 1.25rem auto 0 !important;
    gap: 10px !important;
    flex-wrap: wrap !important;
    justify-content: center !important;
}
.cl-swal-dialog button.swal2-confirm,
.cl-swal-dialog .swal2-styled.swal2-confirm {
    margin: 0 !important;
    background: linear-gradient(135deg, #0b2c6b, #123f8f) !important;
    filter: brightness(1.3);
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
.cl-swal-dialog button.swal2-confirm:hover {
    box-shadow: 0 6px 18px rgba(11, 44, 107, 0.38) !important;
    transform: translateY(-1px);
}
.cl-swal-dialog button.swal2-cancel,
.cl-swal-dialog .swal2-styled.swal2-cancel {
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
.cl-swal-dialog button.swal2-cancel:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
}
.dash-body.dark .cl-swal-dialog button.swal2-cancel,
.dash-body.dark .cl-swal-dialog .swal2-styled.swal2-cancel {
    background: rgba(30, 41, 59, 0.8) !important;
    border-color: rgba(148, 163, 184, 0.35) !important;
    color: #e2e8f0 !important;
}
.cl-swal-deny-pdf {
    margin: 0 !important;
    border-radius: 12px !important;
    font-weight: 700 !important;
    font-size: 0.88rem !important;
    padding: 0.7rem 1.5rem !important;
    min-width: 8.5rem !important;
    border: 2px solid #16a34a !important;
    background: #f0fdf4 !important;
    color: #15803d !important;
    cursor: pointer !important;
}
.dash-body.dark .cl-swal-deny-pdf {
    background: rgba(20, 83, 45, 0.3) !important;
    border-color: rgba(74, 222, 128, 0.4) !important;
    color: #86efac !important;
}
.cl-swal-error-text {
    margin-top: 0.75rem;
    padding: 10px 14px;
    border-radius: 10px;
    background: #fef2f2;
    border: 1px solid #fecaca;
}
.dash-body.dark .cl-swal-error-text {
    background: rgba(127, 29, 29, 0.25);
    border-color: rgba(248, 113, 113, 0.35);
}
.cl-swal-error-list {
    text-align: left;
    margin: 0.25rem 0 0;
    padding-left: 1.2rem;
    line-height: 1.55;
    font-size: 0.88rem;
    color: #334155;
}
.dash-body.dark .cl-swal-error-list { color: #e2e8f0; }
.cl-swal-error-list li { margin: 0.35rem 0; }
.dash-body.dark .swal2-html-container { color: #e2e8f0 !important; }

.dash-body input[type="date"]::-webkit-calendar-picker-indicator { filter: none; }
html.dark .dash-body input[type="date"]::-webkit-calendar-picker-indicator { filter: brightness(0) invert(1); }

/* ── Modal Preview Checklist ── */
#checklist-preview-overlay {
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
    animation: clOverlayIn 0.2s ease;
}
#checklist-preview-overlay.active {
    display: flex;
}
@keyframes clOverlayIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}
.checklist-preview-modal {
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
    animation: clModalIn 0.25s cubic-bezier(0.34,1.56,0.64,1);
}
html.dark .dash-body .checklist-preview-modal {
    background: #0f172a;
    border: 1px solid rgba(71,85,105,0.35);
    box-shadow: 0 24px 80px rgba(0,0,0,0.55);
}
@keyframes clModalIn {
    from { opacity: 0; transform: translateY(28px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
.checklist-preview-modal-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 18px 20px 14px;
    border-bottom: 1px solid #e2e8f0;
    flex-shrink: 0;
}
html.dark .dash-body .checklist-preview-modal-header {
    border-bottom-color: rgba(71,85,105,0.35);
}
.checklist-preview-modal-close {
    flex-shrink: 0;
}
.checklist-preview-modal-header-icon {
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
.checklist-preview-modal-title {
    flex: 1;
    min-width: 0;
}
.checklist-preview-modal-title h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.01em;
}
html.dark .dash-body .checklist-preview-modal-title h3 { color: #f1f5f9; }
.checklist-preview-modal-title p {
    margin: 2px 0 0;
    font-size: 0.75rem;
    color: #64748b;
}
html.dark .dash-body .checklist-preview-modal-title p { color: #94a3b8; }
.checklist-preview-modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 18px 20px;
    -webkit-overflow-scrolling: touch;
}
.checklist-preview-modal-footer {
    padding: 14px 20px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    flex-shrink: 0;
}
html.dark .dash-body .checklist-preview-modal-footer {
    border-top-color: rgba(71,85,105,0.35);
}
.checklist-preview-modal-footer .checklist-modal-cancel-btn {
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
.checklist-preview-modal-footer .checklist-modal-cancel-btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0f172a;
}
html.dark .dash-body .checklist-preview-modal-footer .checklist-modal-cancel-btn {
    border-color: rgba(71,85,105,0.5);
    color: #94a3b8;
}
html.dark .dash-body .checklist-preview-modal-footer .checklist-modal-cancel-btn:hover {
    background: rgba(71,85,105,0.2);
    color: #f1f5f9;
}
.checklist-submit-btn {
    width: 100%;
    min-height: 52px;
    font-size: 0.95rem;
    border-radius: 14px;
    box-shadow: 0 6px 20px rgba(5, 45, 127, 0.35);
}

/* Dark Mode overrides for Checklist Preview inside Modal */
.checklist-preview-modal-body .info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
    font-size: 0.82rem;
}
.checklist-preview-modal-body .info-table th {
    border: 1px solid #e2e8f0;
    padding: 7px 10px;
    background: #f1f5f9;
    color: #475569;
    font-weight: 700;
    text-align: left;
    font-size: 0.82rem;
}
.checklist-preview-modal-body .info-table td {
    border: 1px solid #e2e8f0;
    padding: 7px 10px;
    vertical-align: middle;
    color: #334155;
}
.checklist-preview-modal-body .info-table .label {
    font-weight: 700;
    background: #f1f5f9;
    color: #475569;
    width: 38%;
    vertical-align: top;
}
html.dark .dash-body .checklist-preview-modal-body .info-table th {
    border-color: rgba(255, 255, 255, 0.1);
    background: rgba(8, 20, 50, 0.55);
    color: #94a3b8;
}
html.dark .dash-body .checklist-preview-modal-body .info-table td {
    border-color: rgba(255, 255, 255, 0.1);
    color: #e2e8f0;
}
html.dark .dash-body .checklist-preview-modal-body .info-table .label {
    background: rgba(8, 20, 50, 0.55);
    color: #94a3b8;
}
html.dark .dash-body .pvw-catatan {
    background: rgba(30, 41, 59, 0.3);
    border-color: rgba(71, 85, 105, 0.3);
    color: #e2e8f0;
}
html.dark .dash-body .pvw-sig-img {
    background: rgba(255, 255, 255, 0.85);
    border-color: rgba(71, 85, 105, 0.35);
}
html.dark .dash-body .pvw-perlengkapan-item.ada {
    background: rgba(20, 83, 45, 0.25);
    color: #86efac;
}
html.dark .dash-body .pvw-perlengkapan-item.tidak {
    background: rgba(127, 29, 29, 0.25);
    color: #fca5a5;
}
html.dark .dash-body .pvw-photo-slot img {
    border-color: rgba(71, 85, 105, 0.35);
    background: rgba(30, 41, 59, 0.45);
}
</style>
@endpush


@section('content')
@php
    $userRole           = $user->role ?? 'driver';
    $isDriver           = $userRole === 'driver';
    $isSuperAdminRole   = $userRole === 'superadmin';
    $userRoleLabel      = $isSuperAdminRole ? 'SUPERADMIN' : 'DRIVER';
    $userName           = $user->name ?? $user->username ?? 'User';
@endphp
<div class="checklist-shell" data-checklist-wizard>
    <main class="checklist-content">
        <form id="checklist-form" class="checklist-card" action="{{ route('checklists.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="checklist-progress-head">
                <div class="checklist-progress-info">
                    <span id="checklist-step-label">LANGKAH 1 DARI 7</span>
                    <span id="checklist-progress-pct">14%</span>
                </div>
                <div class="checklist-progress-track">
                    <span id="checklist-progress-fill"></span>
                </div>
            </div>

            @php
                $iconNamaAktif = 'bi bi-person-check';
                $iconNamaBiasa = 'bi bi-person';
            @endphp

            {{-- ==================== STEP 1: IDENTITAS ==================== --}}
            <section class="wizard-step active" data-step="1">
                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M5 17h1l1-4h10l1 4h1a1 1 0 011 1v1H4v-1a1 1 0 011-1z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M7 13l1.5-5h7L17 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7.5" cy="17" r="1.5" stroke="currentColor" stroke-width="1.5"/><circle cx="16.5" cy="17" r="1.5" stroke="currentColor" stroke-width="1.5"/></svg>
                    <span>1. Identitas Unit</span>
                </div>
                <div class="checklist-grid-two">
                    <label class="checklist-field">
                        <span>Tanggal</span>
                        <div class="checklist-control-wrap checklist-control-date">
                            <input type="date" name="tanggal" id="input-tanggal" required {{ $isDriver ? 'readonly' : '' }}>
                        </div>
                    </label>
                    <label class="checklist-field">
                        <span>Shift</span>
                        <div class="checklist-control-wrap checklist-control-select">
                            <select name="{{ $isDriver ? '_shift_display' : 'shift' }}" id="input-shift" required {{ $isDriver ? 'disabled' : '' }}>
                                <option value="">Pilih Shift</option>
                                <option value="Pagi">Pagi</option>
                                <option value="Siang">Siang</option>
                            </select>
                            @if($isDriver)
                                <input type="hidden" name="shift" id="input-shift-hidden">
                            @endif
                        </div>
                    </label>
                    <label class="checklist-field">
                        <span>Jam Serah Terima</span>
                        <div class="checklist-control-wrap checklist-control-time">
                            <input type="time" name="jam_serah_terima" required>
                        </div>
                    </label>
                    <label class="checklist-field">
                        <span>Nomor Kendaraan</span>
                        <div class="checklist-control-wrap checklist-control-select">
                            <select name="nomor_kendaraan" id="nomor_kendaraan" required>
                                <option value="">Pilih Nomor Kendaraan</option>
                                @foreach ($kendaraans as $k)
                                    <option value="{{ $k->nomor_kendaraan }}" data-jenis="{{ $k->jenis_kendaraan }}">{{ $k->nomor_kendaraan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </label>
                </div>
                <label class="checklist-field">
                    <span>Jenis Kendaraan</span>
                    <input type="text" name="jenis_kendaraan" id="jenis_kendaraan" placeholder="Otomatis terisi" readonly required>
                </label>
                <label class="checklist-field">
                    <span>Pengemudi yang Menyerahkan</span>
                    @if ($isDriver)
                        <input type="hidden" name="driver_serah" value="{{ $user->name }}" required>
                        <div class="driver-static-display driver-icon-active">
                            <i class="bi bi-person-check"></i>
                            <span>{{ $user->name }}</span>
                        </div>
                    @else
                        <div class="checklist-control-wrap checklist-control-select checklist-driver-select-wrap">
                            <select name="driver_serah" id="driver_serah" data-driver-select data-placeholder="Pilih Driver" required>
                                <option value=""></option>
                                @foreach ($drivers as $d)
                                    @php $isActiveDriver = $user->id === $d->id; @endphp
                                    <option value="{{ $d->name }}" data-icon="{{ $isActiveDriver ? $iconNamaAktif : $iconNamaBiasa }}" data-active="{{ $isActiveDriver ? '1' : '0' }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </label>
                <label class="checklist-field">
                    <span>Pengemudi yang Menerima</span>
                    <div class="checklist-control-wrap checklist-control-select checklist-driver-select-wrap">
                        <select name="driver_terima" id="driver_terima" data-driver-select data-placeholder="Pilih Driver" required>
                            <option value=""></option>
                            @foreach ($drivers as $d)
                                @if (!$isDriver || $d->id !== $user->id)
                                    @php $isActiveDriver = $user->id === $d->id; @endphp
                                    <option value="{{ $d->name }}" data-icon="{{ $isActiveDriver ? $iconNamaAktif : $iconNamaBiasa }}" data-active="{{ $isActiveDriver ? '1' : '0' }}">{{ $d->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </label>
            </section>

            {{-- ==================== STEP 2: EXTERIOR ==================== --}}
            <section class="wizard-step" data-step="2">
                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><rect x="2" y="7" width="20" height="10" rx="2" stroke="currentColor" stroke-width="2"/><path d="M6 7V5a2 2 0 012-2h8a2 2 0 012 2v2" stroke="currentColor" stroke-width="2"/><path d="M2 13h20" stroke="currentColor" stroke-width="2"/></svg>
                    <span>2. Kondisi Eksterior</span>
                </div>
                <div class="checklist-item-list">
                    @foreach (['Body Kendaraan' => 'body_kendaraan', 'Kaca' => 'kaca', 'Spion' => 'spion', 'Lampu Utama' => 'lampu_utama', 'Lampu Sein' => 'lampu_sein', 'Ban' => 'ban', 'Velg' => 'velg', 'Wiper' => 'wiper'] as $label => $name)
                        <div class="checklist-condition-row">
                            <div class="checklist-condition-head">
                                <span>{{ $label }}</span>
                                <div class="checklist-radio-group">
                                    <input type="radio" id="exterior_{{ $name }}_ok" name="exterior_{{ $name }}" value="ok" required>
                                    <label for="exterior_{{ $name }}_ok">OK</label>
                                    <input type="radio" id="exterior_{{ $name }}_no" name="exterior_{{ $name }}" value="no">
                                    <label for="exterior_{{ $name }}_no">NO</label>
                                </div>
                            </div>
                            <input type="text" name="exterior_{{ $name }}_catatan" class="checklist-item-note" placeholder="Keterangan...">
                        </div>
                    @endforeach
                </div>
                <div class="checklist-field">
                    <span>Foto Bukti Exterior (Wajib 4 Sisi)</span>
                    <div class="checklist-photo-grid checklist-photo-grid-4">
                        @foreach (['depan', 'kanan', 'kiri', 'belakang'] as $side)
                            <label class="checklist-photo-slot" data-photo-preview-slot>
                                <input type="file" name="exterior_foto_{{ $side }}" accept="image/*" capture="environment" required data-photo-single data-required-photo>
                                <div class="photo-slot-placeholder">
                                    <span class="checklist-photo-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="10" r="1.4" stroke="currentColor" stroke-width="1.6"/><path d="M20 15L15.3 10.5L8 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                    <strong>{{ strtoupper($side) }}</strong>
                                </div>
                                <img class="photo-slot-preview" alt="Preview {{ $side }}" style="display:none">
                                <button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus foto">×</button>
                            </label>
                        @endforeach
                    </div>
                    <p style="margin:8px 0 0;font-size:0.78rem;line-height:1.45;color:#b45309;background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.28);padding:10px 12px;border-radius:10px;">Pastikan mengambil foto dengan kamera landscape (horizontal).</p>
                </div>
            </section>

            {{-- ==================== STEP 3: INTERIOR ==================== --}}
            <section class="wizard-step" data-step="3">
                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><rect x="3" y="8" width="18" height="10" rx="2" stroke="currentColor" stroke-width="2"/><path d="M7 8V6a2 2 0 012-2h6a2 2 0 012 2v2" stroke="currentColor" stroke-width="2"/><path d="M8 12h2M14 12h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span>3. Kondisi Interior</span>
                </div>
                <div class="checklist-item-list">
                    @foreach (['Jok / Kursi' => 'jok', 'Dashboard' => 'dashboard', 'AC' => 'ac', 'Sabuk Pengaman' => 'sabuk_pengaman', 'Audio / Head Unit' => 'audio', 'Kebersihan Interior' => 'kebersihan'] as $label => $name)
                        <div class="checklist-condition-row">
                            <div class="checklist-condition-head">
                                <span>{{ $label }}</span>
                                <div class="checklist-radio-group">
                                    <input type="radio" id="interior_{{ $name }}_ok" name="interior_{{ $name }}" value="ok" required>
                                    <label for="interior_{{ $name }}_ok">OK</label>
                                    <input type="radio" id="interior_{{ $name }}_no" name="interior_{{ $name }}" value="no">
                                    <label for="interior_{{ $name }}_no">NO</label>
                                </div>
                            </div>
                            <input type="text" name="interior_{{ $name }}_catatan" class="checklist-item-note" placeholder="Keterangan...">
                        </div>
                    @endforeach
                </div>
                <div class="checklist-field">
                    <span>Foto Interior (Wajib min. 1, maks 3)</span>
                    <div class="dynamic-photo-container" data-dynamic-photos data-min-photos="1" data-section="interior" data-max="3" data-min-required="1">
                        <div class="dynamic-photo-grid">
                            <label class="checklist-photo-slot" data-photo-preview-slot>
                                <input type="file" name="interior_foto_1" accept="image/*" capture="environment" required data-photo-single>
                                <div class="photo-slot-placeholder"><span class="checklist-photo-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="10" r="1.4" stroke="currentColor" stroke-width="1.6"/><path d="M20 15L15.3 10.5L8 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span><strong>FOTO 1</strong></div>
                                <img class="photo-slot-preview" alt="Preview" style="display:none"><button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus foto">×</button>
                            </label>
                            <button type="button" class="dynamic-photo-add-btn" data-add-photo-btn aria-label="Tambah foto"><svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg></button>
                        </div>
                    </div>
                    <p style="margin:8px 0 0;font-size:0.78rem;line-height:1.45;color:#b45309;background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.28);padding:10px 12px;border-radius:10px;">Pastikan mengambil foto dengan kamera landscape (horizontal).</p>
                </div>
            </section>

            {{-- ==================== STEP 4: MESIN ==================== --}}
            <section class="wizard-step" data-step="4">
                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94L6.7 20.27a2.12 2.12 0 01-3-3l6.8-6.73A6 6 0 0118.5 2.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>4. Kondisi Mesin</span>
                </div>
                <div class="checklist-item-list">
                    @foreach (['Mesin (Suara Normal)' => 'mesin', 'Oli Mesin' => 'oli', 'Air Radiator' => 'radiator', 'Rem' => 'rem', 'Kopling (Manual)' => 'kopling', 'Transmisi' => 'transmisi', 'Indikator Panel' => 'indikator'] as $label => $name)
                        <div class="checklist-condition-row">
                            <div class="checklist-condition-head">
                                <span>{{ $label }}</span>
                                <div class="checklist-radio-group">
                                    <input type="radio" id="mesin_{{ $name }}_ok" name="mesin_{{ $name }}" value="ok" required>
                                    <label for="mesin_{{ $name }}_ok">OK</label>
                                    <input type="radio" id="mesin_{{ $name }}_no" name="mesin_{{ $name }}" value="no">
                                    <label for="mesin_{{ $name }}_no">NO</label>
                                </div>
                            </div>
                            <input type="text" name="mesin_{{ $name }}_catatan" class="checklist-item-note" placeholder="Keterangan...">
                        </div>
                    @endforeach
                </div>
                <div class="checklist-field">
                    <span>Foto Ruang Mesin (Wajib min. 1, maks 3)</span>
                    <div class="dynamic-photo-container" data-dynamic-photos data-min-photos="1" data-section="mesin" data-max="3" data-min-required="1">
                        <div class="dynamic-photo-grid">
                            <label class="checklist-photo-slot" data-photo-preview-slot>
                                <input type="file" name="mesin_foto_1" accept="image/*" capture="environment" required data-photo-single>
                                <div class="photo-slot-placeholder"><span class="checklist-photo-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="10" r="1.4" stroke="currentColor" stroke-width="1.6"/><path d="M20 15L15.3 10.5L8 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span><strong>FOTO 1</strong></div>
                                <img class="photo-slot-preview" alt="Preview" style="display:none"><button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus foto">×</button>
                            </label>
                            <button type="button" class="dynamic-photo-add-btn" data-add-photo-btn aria-label="Tambah foto"><svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg></button>
                        </div>
                    </div>
                    <p style="margin:8px 0 0;font-size:0.78rem;line-height:1.45;color:#b45309;background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.28);padding:10px 12px;border-radius:10px;">Pastikan mengambil foto dengan kamera landscape (horizontal).</p>
                </div>
            </section>

            {{-- ==================== STEP 5: BBM & KM ==================== --}}
            <section class="wizard-step" data-step="5">
                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M3 22V5a2 2 0 012-2h8a2 2 0 012 2v17" stroke="currentColor" stroke-width="2"/><path d="M15 10h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="2"/><path d="M7 10h4M7 14h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span>5. BBM dan Kilometer</span>
                </div>
                <div class="bbm-card">
                    <div class="bbm-header"><span class="bbm-label">LEVEL BBM SAAT INI</span><span class="bbm-value" id="bbm-value-display">50<small>%</small></span></div>
                    <div class="bbm-slider-wrap"><input type="range" min="0" max="100" step="1" value="50" name="level_bbm" id="bbm-range" class="bbm-slider" required></div>
                    <div class="bbm-scale"><span>E (EMPTY)</span><span>F (FULL)</span></div>
                </div>
                <div class="checklist-field" style="margin-top:14px">
                    <label class="checklist-photo-slot checklist-photo-slot-wide" data-photo-preview-slot>
                        <input type="file" name="foto_bbm_dashboard" accept="image/*" capture="environment" required data-photo-single data-required-photo>
                        <div class="photo-slot-placeholder"><span class="checklist-photo-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="10" r="1.4" stroke="currentColor" stroke-width="1.6"/><path d="M20 15L15.3 10.5L8 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span><strong>FOTO (RATIO 16:9) INDIKATOR BBM &amp; DASHBOARD</strong></div>
                        <img class="photo-slot-preview" alt="Preview BBM" style="display:none"><button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus foto">×</button>
                    </label>
                    <p style="margin:8px 0 0;font-size:0.78rem;line-height:1.45;color:#b45309;background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.28);padding:10px 12px;border-radius:10px;">Pastikan mengambil foto dengan kamera landscape (horizontal).</p>
                </div>
                <div class="checklist-field" style="margin-top:10px">
                    <span>PENGISIAN BBM TERAKHIR</span>
                    <div class="bbm-terakhir-row">
                        <div class="checklist-control-wrap checklist-control-date"><input type="date" name="bbm_terakhir_date" required></div>
                        <div class="checklist-control-wrap checklist-control-time"><input type="time" name="bbm_terakhir_time" required></div>
                    </div>
                </div>
                <div class="km-row" style="margin-top:14px">
                    <div class="km-card"><span class="km-card-label">KM AWAL (SAAT INI)</span><input type="number" name="km_awal" id="km_awal" min="0" required class="km-card-value km-card-editable"></div>
                    <div class="km-card"><span class="km-card-label">KM AKHIR (SELESAI)</span><input type="number" name="km_akhir" id="km_akhir" min="0" required class="km-card-value km-card-editable"></div>
                </div>
                <div id="km-awal-error" class="km-error" style="display:none;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span id="km-awal-error-text"></span>
                </div>
                <div class="km-error" id="km-error" style="display:none">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span id="km-error-text"></span>
                </div>
            </section>

            {{-- ==================== STEP 6: PERLENGKAPAN ==================== --}}
            <section class="wizard-step" data-step="6">
                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 15a3 3 0 100-6 3 3 0 000 6z" stroke="currentColor" stroke-width="2"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 8.6a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>6. Perlengkapan Unit</span>
                </div>
                <div class="checklist-check-grid">
                    @foreach (['STNK' => 'stnk', 'Kartu KIR dan QR Kartu BBM' => 'kir', 'Dongkrak' => 'dongkrak', 'Toolkit' => 'toolkit', 'Segitiga Pengaman' => 'segitiga', 'APAR' => 'apar', 'Ban Cadangan' => 'ban_cadangan'] as $label => $name)
                        <label class="checklist-checkbox">
                            <input type="checkbox" name="perlengkapan[{{ $name }}]" value="1">
                            <span class="checklist-checkmark" aria-hidden="true"></span>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- ==================== STEP 7: VALIDASI AKHIR ==================== --}}
            <section class="wizard-step" data-step="7">
                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z" stroke="currentColor" stroke-width="2"/></svg>
                    <span>7. Konfirmasi Akhir</span>
                </div>
                <label class="checklist-field"><span>CATATAN TAMBAHAN / TEMUAN UMUM</span><textarea name="catatan_khusus" rows="4" placeholder="Tuliskan temuan atau catatan khusus jika ada..."></textarea></label>
                <div class="checklist-statement-box"><p><em>"Pemeriksaan kendaraan telah dilakukan sesuai kondisi aktual."</em></p></div>

                <div class="signature-row">
                    <div class="signature-block">
                        <span class="signature-label">TTD DRIVER YANG MENYERAHKAN</span>
                        <div class="signature-pad-wrap"><canvas id="sig-pad-serah" class="signature-canvas"></canvas><div class="signature-pad-hint" data-sig-hint="serah"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z" stroke="currentColor" stroke-width="2"/></svg><span>TAP TO SIGN</span></div></div>
                        <button type="button" class="signature-clear-btn" data-clear-sig="serah">Hapus TTD</button>
                        <input type="hidden" name="tanda_tangan_serah" id="sig-data-serah">
                    </div>
                    <div class="signature-block">
                        <span class="signature-label">TTD DRIVER YANG MENERIMA</span>
                        <div class="signature-pad-wrap"><canvas id="sig-pad-terima" class="signature-canvas"></canvas><div class="signature-pad-hint" data-sig-hint="terima"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z" stroke="currentColor" stroke-width="2"/></svg><span>TAP TO SIGN</span></div></div>
                        <button type="button" class="signature-clear-btn" data-clear-sig="terima">Hapus TTD</button>
                        <input type="hidden" name="tanda_tangan_terima" id="sig-data-terima">
                    </div>
                </div>

                <label class="checklist-confirm-box">
                    <input type="checkbox" name="konfirmasi_data" id="konfirmasi_data" required>
                    <span>Saya menyetujui.</span>
                </label>

                <div class="form-complete-alert" id="form-complete-alert" style="display:none">
                    <div class="form-complete-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/></svg></div>
                    <div><strong>Data Siap Dipreview!</strong><p>Tekan tombol <strong>Lihat Preview</strong> untuk memeriksa semua data sebelum menyimpan.</p></div>
                </div>
            </section>

            <footer class="checklist-footer" style="margin-top: 20px;">
                <button type="button" class="checklist-nav-btn checklist-nav-back" id="wizard-prev" disabled>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="button" class="checklist-nav-btn checklist-nav-next" id="wizard-next">
                    LANJUT
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </footer>
        </form>
    </main>
</div>
@endsection

@section('modals')
{{-- ── Modal Preview Checklist ── --}}
<div id="checklist-preview-overlay" role="dialog" aria-modal="true" aria-labelledby="checklist-preview-modal-title">
    <div class="checklist-preview-modal">
        <div class="checklist-preview-modal-header">
            <div class="checklist-preview-modal-header-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/><path d="M9 12h6M9 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <div class="checklist-preview-modal-title">
                <h3 id="checklist-preview-modal-title">Ringkasan Laporan Checklist</h3>
                <p>Periksa kembali data sebelum mengirim.</p>
            </div>
            <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite checklist-preview-modal-close" id="checklist-preview-close" title="Tutup" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="checklist-preview-modal-body sppd-detail-html">
            <div id="preview-content">
                <div style="text-align:center;padding:48px 0;color:#94a3b8">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" style="margin-bottom:12px;opacity:.4"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <p style="font-size:.88rem">Memuat ringkasan data...</p>
                </div>
            </div>
        </div>
        <div class="checklist-preview-modal-footer">
            <button type="button" class="checklist-modal-cancel-btn" id="checklist-preview-cancel">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Kembali
            </button>
            <button type="button" class="checklist-nav-btn checklist-nav-next final checklist-submit-btn" id="checklist-submit">
                <i class="bi bi-send-fill checklist-submit-icon" aria-hidden="true"></i>
                Generate PDF
            </button>
        </div>
    </div>
</div>
@endsection

@if($isDriver)
@push('scripts')
<script>
(function () {
    function detectShift(hour) {
        if (hour >= 7 && hour < 12)  return 'Pagi';
        if (hour >= 12 && hour < 16) return 'Siang';
        return '';
    }
    const now   = new Date();
    const year  = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day   = String(now.getDate()).padStart(2, '0');
    const today = `${year}-${month}-${day}`;
    const shift = detectShift(now.getHours());

    const tanggalEl   = document.getElementById('input-tanggal');
    const shiftEl     = document.getElementById('input-shift');
    const shiftHidden = document.getElementById('input-shift-hidden');

    if (tanggalEl && !tanggalEl.value) tanggalEl.value = today;
    if (shift) {
        if (shiftEl) {
            Array.from(shiftEl.options).forEach(opt => { if (opt.value === shift) opt.selected = true; });
        }
        if (shiftHidden) shiftHidden.value = shift;
    }
})();
</script>
@endpush
@endif
