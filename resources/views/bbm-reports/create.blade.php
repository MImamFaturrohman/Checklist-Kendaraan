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
    $shiftLabel = match($bbmShiftCode) {
        'pagi'  => 'Pagi',
        'siang' => 'Siang',
        default => 'Di Luar Shift'
    };
@endphp
<style>
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
    .dash-body.dark .bbm-form-page .bbm-page-head h1 { color: #f1f5f9; }
    .bbm-form-page .bbm-page-head p { margin: 4px 0 0; font-size: 0.82rem; color: #64748b; }
    .dash-body.dark .bbm-form-page .bbm-page-head p { color: #94a3b8; }

    .bbm-footer-actions {
        display: flex;
        width: 100%;
        min-width: 0;
        gap: 10px;
    }
    #bbm-submit.bbm-submit--hidden {
        display: none !important;
    }
    #bbm-next.bbm-next--hidden {
        display: none !important;
    }
    .bbm-footer-actions .checklist-nav-next {
        flex: 1;
        min-width: 0;
        justify-content: center;
    }

    .bbm-review {
        display: grid;
        gap: 14px;
        padding: 16px;
        border-radius: 12px;
        background: rgba(248, 250, 252, 0.9);
        border: 1px solid #e2e8f0;
        font-size: 0.88rem;
    }
    .dash-body.dark .bbm-review {
        background: rgba(15, 23, 42, 0.5);
        border-color: rgba(71, 85, 105, 0.35);
        color: #e2e8f0;
    }
    .bbm-review-group h4 {
        margin: 0 0 8px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
    }
    .dash-body.dark .bbm-review-group h4 { color: #94a3b8; }
    .bbm-review-dl { margin: 0; display: grid; gap: 6px; }
    .bbm-review-dl > div { display: grid; grid-template-columns: minmax(100px, 140px) 1fr; gap: 10px; align-items: start; }
    .bbm-review-dl dt { margin: 0; font-weight: 600; color: #475569; font-size: 0.8rem; }
    .dash-body.dark .bbm-review-dl dt { color: #cbd5e1; }
    .bbm-review-dl dd { margin: 0; color: #0f172a; line-height: 1.45; word-break: break-word; }
    .dash-body.dark .bbm-review-dl dd { color: #f1f5f9; }
    .bbm-review-photos {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 4px;
    }
    @media (max-width: 520px) {
        .bbm-review-photos { grid-template-columns: 1fr; }
    }
    .bbm-review-photo-wrap {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .dash-body.dark .bbm-review-photo-wrap {
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
    .bbm-review-photo-caption {
        font-size: 0.72rem;
        padding: 6px 8px;
        color: #64748b;
        font-weight: 600;
    }
    .dash-body.dark .bbm-review-photo-caption { color: #94a3b8; }
    p.bbm-step3-hint {
        margin: 0 0 12px;
        font-size: 0.85rem;
        line-height: 1.45;
        color: #64748b;
    }
    .dash-body.dark p.bbm-step3-hint { color: #94a3b8; }
    .dash-body.dark p.bbm-step3-hint strong { color: #facc15; }
</style>
@endpush

@section('content')
<div class="checklist-shell" data-bbm-form>
    <main class="checklist-content">
        <form id="bbm-report-form" class="checklist-card" action="{{ route('bbm-reports.store') }}" method="post" enctype="multipart/form-data" data-dashboard-url="{{ route('dashboard') }}" novalidate>
            @csrf

            @if ($errors->any())
                <div class="bbm-nojs-errors" role="alert">
                    <strong>Periksa kembali:</strong>
                    <ul>@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="checklist-progress-head">
                <div class="checklist-progress-info">
                    <span id="bbm-step-label">LANGKAH 1 DARI 2</span>
                    <span id="bbm-progress-pct">50%</span>
                </div>
                <div class="checklist-progress-track">
                    <span id="bbm-progress-fill"></span>
                </div>
            </div>

            <section class="wizard-step active" data-step="1">
                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M5 17h1l1-4h10l1 4h1a1 1 0 011 1v1H4v-1a1 1 0 011-1z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M7 13l1.5-5h7L17 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Data Kendaraan</span>
                </div>
                <div class="checklist-grid-two">
                    <label class="checklist-field">
                        <span>Kendaraan</span>
                        <div class="checklist-control-wrap checklist-control-select">
                            <select name="nomor_kendaraan" id="bbm-nopol" required>
                                <option value="">Pilih Nomor Kendaraan</option>
                                @foreach ($kendaraans as $k)
                                    <option value="{{ $k->nomor_kendaraan }}" data-jenis="{{ $k->jenis_kendaraan }}" @selected(old('nomor_kendaraan') === $k->nomor_kendaraan)>{{ $k->nomor_kendaraan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </label>
                    <label class="checklist-field">
                        <span>Jenis Kendaraan</span>
                        <div class="checklist-control-wrap">
                            <input type="text" id="bbm-jenis" readonly class="checklist-input-readonly" value="" placeholder="Otomatis terisi…" autocomplete="off">
                        </div>
                    </label>
                    <label class="checklist-field checklist-field-span">
                        <span>Keperluan Pengisian BBM</span>
                        <div class="checklist-control-wrap checklist-control-select">
                            <select name="jenis_pengisian" id="bbm-jenis-pengisian" required>
                                <option value="">Pilih Keperluan</option>
                                <option value="Operasional" @selected(old('jenis_pengisian') === 'Operasional')>Operasional</option>
                                <option value="Perjalanan Dinas (SPPD)" @selected(old('jenis_pengisian') === 'Perjalanan Dinas (SPPD)')>Perjalanan Dinas (SPPD)</option>
                            </select>
                        </div>
                    </label>
                    <label class="checklist-field checklist-field-span">
                        <span><i class="bi bi-brightness-high bbm-field-icon" aria-hidden="true"></i> Shift</span>
                        <div class="checklist-control-wrap">
                            <input type="text" id="bbm-shift-label" value="{{ $shiftLabel }}" readonly>
                        </div>
                    </label>
                </div>
                <div class="checklist-grid-two bbm-datetime-grid">
                    <label class="checklist-field">
                        <span><i class="bi bi-calendar3 bbm-field-icon" aria-hidden="true"></i> Tanggal</span>
                        <div class="checklist-control-wrap bbm-input-with-icon">
                            <input type="date" name="tanggal" id="bbm-tanggal" required value="{{ old('tanggal') }}">
                        </div>
                    </label>
                    <label class="checklist-field">
                        <span><i class="bi bi-clock bbm-field-icon" aria-hidden="true"></i> Waktu</span>
                        <div class="checklist-control-wrap bbm-input-with-icon">
                            <input type="time" name="waktu" id="bbm-waktu" required value="{{ old('waktu') }}">
                        </div>
                    </label>
                </div>

                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/><path d="M4 10h16v6H4z" stroke="currentColor" stroke-width="2"/><path d="M8 10V8a4 4 0 118 0v2" stroke="currentColor" stroke-width="2"/></svg>
                    <span>Data Pengisian BBM</span>
                </div>
                <div class="checklist-grid-two">
                    <label class="checklist-field">
                        <span>KM Sebelum</span>
                        <div class="checklist-control-wrap">
                            <input type="number" name="odometer_sebelum" id="bbm-odo-sebelum" required min="0" step="1" inputmode="numeric" value="{{ old('odometer_sebelum') }}" placeholder="0">
                        </div>
                    </label>
                    <label class="checklist-field">
                        <span>KM Sesudah</span>
                        <div class="checklist-control-wrap">
                            <input type="number" name="odometer_sesudah" id="bbm-odo-sesudah" required min="0" step="1" inputmode="numeric" value="{{ old('odometer_sesudah') }}" placeholder="0">
                        </div>
                    </label>
                </div>
                <div class="checklist-grid-two">
                    <label class="checklist-field">
                        <span>Liter</span>
                        <div class="checklist-control-wrap">
                            <input type="number" name="liter" id="bbm-liter" required min="0.001" step="0.001" value="{{ old('liter') }}" placeholder="0">
                        </div>
                    </label>
                    <label class="checklist-field">
                        <span>Harga/L</span>
                        <div class="checklist-control-wrap">
                            <input type="number" name="harga_per_liter" id="bbm-harga-per-liter" required min="0" step="1" value="{{ old('harga_per_liter') }}" placeholder="0">
                        </div>
                    </label>
                    <label class="checklist-field checklist-field-span2 bbm-total-field">
                        <span>Total Harga</span>
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
                <p style="margin:8px 0 0;font-size:0.78rem;line-height:1.45;color:#b45309;background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.28);padding:10px 12px;border-radius:10px;">Pastikan mengambil foto dengan kamera landscape (horizontal).</p>
            </section>

            <section class="wizard-step" data-step="2">
                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/></svg>
                    <span>Ringkasan</span>
                </div>
                <p class="bbm-step3-hint">Periksa kembali semua data. Tekan <strong>Kirim Laporan BBM</strong> di bawah jika data sudah sesuai.</p>
                <div class="bbm-review" id="bbm-review-root" aria-live="polite"></div>
            </section>
        </form>
    </main>

    <footer class="checklist-footer">
        <button type="button" class="checklist-nav-btn checklist-nav-back" id="bbm-prev" disabled>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="bbm-footer-actions">
            <button type="button" class="checklist-nav-btn checklist-nav-next" id="bbm-next">
                LANJUT
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <button type="submit" form="bbm-report-form" class="checklist-nav-btn checklist-nav-next final bbm-submit-btn bbm-submit--hidden" id="bbm-submit" aria-hidden="true">
                <i class="bi bi-send-fill bbm-submit-icon" aria-hidden="true"></i>
                Kirim Laporan BBM
            </button>
        </div>
    </footer>
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
            confirmButtonText: 'Kembali ke Dashboard',
        }).then((r) => { if (r.isConfirmed) window.location.href = dashUrl; });
        return;
    }

    if (bbmError) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: bbmError,
            confirmButtonText: 'Kembali ke Dashboard',
            showCancelButton: true,
            cancelButtonText: 'Tutup',
        }).then((r) => { if (r.isConfirmed) window.location.href = dashUrl; });
    }
})();
</script>
@endpush
