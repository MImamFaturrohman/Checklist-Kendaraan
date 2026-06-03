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
    /* Ringkasan langkah 4 + dialog SweetAlert selaras tema checklist */
    .vul-review {
        display: grid;
        gap: 14px;
        padding: 16px;
        border-radius: 12px;
        background: rgba(248, 250, 252, 0.9);
        border: 1px solid #e2e8f0;
        font-size: 0.88rem;
    }
    .dash-body.dark .vul-review {
        background: rgba(15, 23, 42, 0.5);
        border-color: rgba(71, 85, 105, 0.35);
        color: #e2e8f0;
    }
    .vul-review-group h4 {
        margin: 0 0 8px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
    }
    .dash-body.dark .vul-review-group h4 { color: #94a3b8; }
    .vul-review-dl { margin: 0; display: grid; gap: 6px; }
    .vul-review-dl > div { display: grid; grid-template-columns: minmax(100px, 140px) 1fr; gap: 10px; align-items: start; }
    .vul-review-dl dt { margin: 0; font-weight: 600; color: #475569; font-size: 0.8rem; }
    .dash-body.dark .vul-review-dl dt { color: #cbd5e1; }
    .vul-review-dl dd { margin: 0; color: #0f172a; line-height: 1.45; word-break: break-word; }
    .dash-body.dark .vul-review-dl dd { color: #f1f5f9; }

    .vehicle-usage-form-page .wizard-step .section-banner:not(:first-child) { margin-top: 28px; }
    .vul-bbm-stack { display: grid; gap: 1rem; }
    @media (min-width: 640px) {
        .vul-bbm-stack { grid-template-columns: 1fr 1fr; gap: 1rem 1.25rem; }
    }

    .swal2-popup.vul-swal-dialog {
        border-radius: 16px !important;
        padding: 1.35rem 1.25rem 1.5rem !important;
        border: 1px solid rgba(11, 44, 107, 0.12);
    }
    .dash-body.dark .swal2-popup.vul-swal-dialog {
        background: #1e293b !important;
        border-color: rgba(148, 163, 184, 0.2);
        color: #f1f5f9 !important;
    }
    .swal2-title.vul-swal-title {
        font-size: 1.15rem !important;
        font-weight: 800 !important;
        color: #0f172a !important;
    }
    .dash-body.dark .swal2-title.vul-swal-title { color: #f1f5f9 !important; }
    .vul-swal-list {
        text-align: left;
        margin: 0.25rem 0 0;
        padding-left: 1.2rem;
        line-height: 1.55;
        font-size: 0.9rem;
        color: #334155;
    }
    .dash-body.dark .vul-swal-list { color: #e2e8f0; }
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
    .dash-body.dark .vul-swal-dialog button.swal2-cancel,
    .dash-body.dark .vul-swal-dialog .swal2-styled.swal2-cancel {
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
    .dash-body.dark .vul-swal-error-box {
        background: rgba(127, 29, 29, 0.25);
        border-color: rgba(248, 113, 113, 0.35);
    }
    .vul-swal-lead {
        margin: 0 0 6px;
        font-size: 0.82rem;
        color: #64748b;
        line-height: 1.45;
    }
    .dash-body.dark .vul-swal-lead {
        color: #94a3b8;
    }

    .vul-footer-actions {
        display: flex;
        width: 100%;
        min-width: 0;
        gap: 10px;
    }
    /* Kalahkan .checklist-nav-btn { display: inline-flex } — kirim hanya langkah 4 */
    #vul-submit.vul-submit--hidden {
        display: none !important;
    }
    #vul-next.vul-next--hidden {
        display: none !important;
    }
    .vul-footer-actions .checklist-nav-next {
        flex: 1;
        min-width: 0;
        justify-content: center;
    }
    .dash-body.dark .swal2-html-container {
        color: #e2e8f0 !important;
    }
    .dash-body.dark .vul-step4-hint strong {
        color: #facc15 !important;
    }
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
                    <span id="vul-step-label">LANGKAH 1 DARI 2</span>
                    <span id="vul-progress-pct">50%</span>
                </div>
                <div class="checklist-progress-track">
                    <span id="vul-progress-fill"></span>
                </div>
            </div>

            {{-- Langkah 1 — Data Penggunaan + Level BBM & Kilometer + Kondisi Kendaraan --}}
            <section class="wizard-step active" data-step="1">
                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M5 17h1l1-4h10l1 4h1a1 1 0 011 1v1H4v-1a1 1 0 011-1z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M7 13l1.5-5h7L17 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Data Penggunaan</span>
                </div>
                <div class="checklist-grid-two">
                    <label class="checklist-field">
                        <span>Nama</span>
                        <div class="checklist-control-wrap">
                            <input type="text" readonly class="checklist-input-readonly" value="{{ $user->name ?? $user->username }}" autocomplete="name">
                        </div>
                    </label>
                    <label class="checklist-field">
                        <span>No. Kendaraan</span>
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
                        <span>Jenis Kendaraan</span>
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
                        <span>Keperluan</span>
                        <div class="checklist-control-wrap">
                            <textarea name="keperluan" id="vul-keperluan" rows="4" placeholder="Jelaskan keperluan penggunaan kendaraan…" maxlength="10000">{{ old('keperluan') }}</textarea>
                        </div>
                    </label>
                </div>

                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M3 22V5a2 2 0 012-2h8a2 2 0 012 2v17" stroke="currentColor" stroke-width="2"/><path d="M15 10h2a2 2 0 012 2v3" stroke="currentColor" stroke-width="2"/><path d="M7 10h4M7 14h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span>Level BBM &amp; Kilometer</span>
                </div>
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

                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
                    <span>Kondisi Kendaraan</span>
                </div>
                <div class="checklist-grid-two">
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

            {{-- Langkah 2 — Ringkasan --}}
            <section class="wizard-step" data-step="2">
                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/></svg>
                    <span>Ringkasan</span>
                </div>
                <p class="vul-step4-hint" style="margin:0 0 12px;font-size:0.85rem;line-height:1.45;color:#64748b">Periksa kembali semua data. Tekan <strong>Kirim log</strong> di bawah untuk mengonfirmasi.</p>
                <div class="vul-review" id="vul-review-root" aria-live="polite"></div>
            </section>
        </form>
    </main>

    <footer class="checklist-footer">
        <button type="button" class="checklist-nav-btn checklist-nav-back" id="vul-prev" disabled>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="vul-footer-actions">
            <button type="button" class="checklist-nav-btn checklist-nav-next" id="vul-next">
                LANJUT
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <button type="submit" form="vehicle-usage-log-form" class="checklist-nav-btn checklist-nav-next final vul-submit--hidden" id="vul-submit" aria-hidden="true">
                <i class="bi bi-send-fill" aria-hidden="true"></i>
                Kirim log
            </button>
        </div>
    </footer>
</div>
@endsection

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
