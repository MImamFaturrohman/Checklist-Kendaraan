@extends('layouts.dash-app')

@section('title', ($isEdit ? 'Edit' : 'Buat') . ' Laporan SPPD')
@section('bodyClass', 'sppd-form-page')
@section('pageTitle', 'TransDinas')
@section('pageSubtitle', 'Buat Laporan SPPD')

@push('styles')
<meta name="turbo-cache-control" content="no-cache">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
@vite(['resources/js/sppd-form.js'])
@endpush

@push('styles')
<style>
    .sppd-form-title {
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: #0b2c6b;
    }
    html.dark .dash-body .sppd-form-title { color: #D4AF37; }
    .sppd-form-title i { color: #D4AF37; }

    .sppd-form-page .section-banner {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 16px;
        background: linear-gradient(90deg, #0b2c6b 0%, #123f8f 50%, #3b5fa8 75%, #dfe6f3 100%);
        color: white;
        font-weight: 600;
        font-size: 16px;
        position: relative;
        overflow: hidden;
        box-shadow: none;
    }
    .sppd-form-page .section-banner::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 6px;
        background: #facc15;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }
    .sppd-form-page .section-banner-icon { color: #facc15; flex-shrink: 0; }
    .sppd-form-page .section-banner span { position: relative; z-index: 1; }
    .sppd-form-page .wizard-step .section-banner:not(:first-child) { margin-top: 28px; }

    .dash-body input[type="date"]::-webkit-calendar-picker-indicator { filter: none; }
    html.dark .dash-body input[type="date"]::-webkit-calendar-picker-indicator { filter: brightness(0) invert(1); }

    .sppd-checklist-footer .sppd-footer-right {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        width: 100% !important;
        min-width: 0 !important;
    }
    .sppd-checklist-footer .sppd-footer-actions {
        display: flex !important;
        width: 100% !important;
        min-width: 0 !important;
        gap: 10px;
    }
    .sppd-checklist-footer .sppd-footer-actions .checklist-nav-next {
        flex: 1 !important;
        width: 100% !important;
        min-width: 0 !important;
        justify-content: center !important;
    }
    .sppd-form-page .checklist-footer {
        grid-template-columns: 1fr !important;
    }

    /* ── Modal Preview SPPD ── */
    #sppd-preview-overlay {
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
        animation: sppdOverlayIn 0.2s ease;
    }
    #sppd-preview-overlay.active {
        display: flex;
    }
    @keyframes sppdOverlayIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    .sppd-preview-modal {
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
        animation: sppdModalIn 0.25s cubic-bezier(0.34,1.56,0.64,1);
    }
    html.dark .dash-body .sppd-preview-modal {
        background: #0f172a;
        border: 1px solid rgba(71,85,105,0.35);
        box-shadow: 0 24px 80px rgba(0,0,0,0.55);
    }
    @keyframes sppdModalIn {
        from { opacity: 0; transform: translateY(28px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .sppd-preview-modal-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 18px 20px 14px;
        border-bottom: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    html.dark .dash-body .sppd-preview-modal-header {
        border-bottom-color: rgba(71,85,105,0.35);
    }
    .sppd-preview-modal-close {
        flex-shrink: 0;
    }
    .sppd-preview-modal-header-icon {
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
    .sppd-preview-modal-title {
        flex: 1;
        min-width: 0;
    }
    .sppd-preview-modal-title h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.01em;
    }
    html.dark .dash-body .sppd-preview-modal-title h3 { color: #f1f5f9; }
    .sppd-preview-modal-title p {
        margin: 2px 0 0;
        font-size: 0.75rem;
        color: #64748b;
    }
    html.dark .dash-body .sppd-preview-modal-title p { color: #94a3b8; }
    .sppd-preview-modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 18px 20px;
        -webkit-overflow-scrolling: touch;
    }
    .sppd-preview-modal-footer {
        padding: 14px 20px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-shrink: 0;
    }
    html.dark .dash-body .sppd-preview-modal-footer {
        border-top-color: rgba(71,85,105,0.35);
    }
    .sppd-preview-modal-footer .sppd-modal-cancel-btn {
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
    .sppd-submit-btn {
        width: 100%;
        min-height: 52px;
        font-size: 0.95rem;
        border-radius: 14px;
        box-shadow: 0 6px 20px rgba(5, 45, 127, 0.35);
    }
    .sppd-preview-modal-footer .sppd-modal-cancel-btn:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
    }
    html.dark .dash-body .sppd-preview-modal-footer .sppd-modal-cancel-btn {
        border-color: rgba(71,85,105,0.5);
        color: #94a3b8;
    }
    html.dark .dash-body .sppd-preview-modal-footer .sppd-modal-cancel-btn:hover {
        background: rgba(71,85,105,0.2);
        color: #f1f5f9;
    }

    .swal-sppd-icon-success {
        box-sizing: content-box !important;
    }
    .swal-sppd-icon-success * {
        box-sizing: content-box !important;
    }
    .swal2-popup.swal-sppd-popup .swal2-success-circular-line-left,
    .swal2-popup.swal-sppd-popup .swal2-success-circular-line-right,
    .swal2-popup.swal-sppd-popup .swal2-success-fix {
        background: transparent !important;
    }
    .swal2-popup.swal-sppd-popup {
        background: rgba(255, 255, 255, 0.9) !important;
        border-radius: 20px !important;
        width: 420px !important;
        max-width: calc(100% - 32px) !important;
        border: 1px solid rgba(11, 44, 107, 0.12) !important;
        padding: 1.5rem 1.25rem 1.5rem !important;
    }
    html.dark .dash-body .swal2-popup.swal-sppd-popup {
        color: #f3f4f6 !important;
        background: rgba(16, 38, 80, 0.78) !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
</style>
@endpush

@section('modals')
<div class="modal-overlay" id="sppd-result-modal" style="display:none">
    <div class="modal-box" id="sppd-result-modal-box">
        <div class="modal-icon" id="sppd-result-icon"></div>
        <h3 id="sppd-result-title"></h3>
        <p id="sppd-result-msg" class="sppd-result-msg"></p>
        <div class="modal-actions" id="sppd-result-actions"></div>
    </div>
</div>

{{-- ── Modal Preview SPPD ── --}}
<div id="sppd-preview-overlay" role="dialog" aria-modal="true" aria-labelledby="sppd-preview-modal-title">
    <div class="sppd-preview-modal">
        <div class="sppd-preview-modal-header">
            <div class="sppd-preview-modal-header-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/><path d="M9 12h6M9 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <div class="sppd-preview-modal-title">
                <h3 id="sppd-preview-modal-title">Ringkasan Laporan SPPD</h3>
                <p>Periksa kembali data sebelum mengirim.</p>
            </div>
            <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite sppd-preview-modal-close" id="sppd-preview-close" title="Tutup" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="sppd-preview-modal-body sppd-detail-html">
            <div class="sppd-review" id="sppd-review-root" aria-live="polite"></div>
            <div class="sppd-summary-grid sppd-summary-grid--step4" style="margin-top: 18px;">
                <div><span class="sppd-sum-label">Total Tol</span><strong id="sppd-sum-tol">Rp 0</strong></div>
                <div><span class="sppd-sum-label">Total BBM</span><strong id="sppd-sum-bbm">Rp 0</strong></div>
                <div class="sppd-sum-grand"><span class="sppd-sum-label">Grand Total</span><strong id="sppd-sum-grand">Rp 0</strong></div>
            </div>
        </div>
        <div class="sppd-preview-modal-footer">
            <button type="button" class="sppd-modal-cancel-btn" id="sppd-preview-cancel">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Kembali
            </button>
            <button type="submit" form="sppd-form" class="checklist-nav-btn checklist-nav-next final sppd-submit-btn" id="sppd-submit">
                <i class="bi bi-send-fill sppd-submit-icon" aria-hidden="true"></i>
                Submit Laporan
            </button>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="checklist-shell" data-sppd-form data-dashboard-url="{{ route('dashboard') }}" data-sppd-list-url="{{ route('sppd.index') }}">
    <main class="checklist-content">
        <form id="sppd-form" class="checklist-card"
              action="{{ $isEdit ? route('sppd.update', $sppd) : route('sppd.store') }}"
              method="post" enctype="multipart/form-data">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="checklist-progress-head">
                <div class="sppd-form-title">
                    <span style="font-size: 1.3em;" id="sppd-step-label"><i class="bi bi-geo-alt-fill"></i> DATA LAPORAN SPPD</span>
                </div>
            </div>

            <section class="wizard-step active" data-sppd-step="1">
                <div class="checklist-grid-two">
                    <label class="checklist-field">
                        <div class="checklist-control-wrap"><input type="text" name="nama_driver_display" value="{{ $user->name ?? $user->username }}" readonly class="checklist-input-readonly"></div>
                    </label>
                    <label class="checklist-field">
                        <div class="checklist-control-wrap checklist-control-date">
                            <input type="date" name="tanggal_dinas" required value="{{ old('tanggal_dinas', $sppd?->tanggal_dinas?->format('Y-m-d')) }}">
                        </div>
                    </label>
                    <label class="checklist-field checklist-field-span2" style="grid-column:1/-1">
                        <span>Keperluan Dinas</span>
                        <div class="checklist-control-wrap"><input type="text" name="keperluan_dinas" required maxlength="500" value="{{ old('keperluan_dinas', $sppd?->keperluan_dinas) }}" placeholder="Contoh: Pengantaran dokumen"></div>
                    </label>
                    <label class="checklist-field">
                        <div class="checklist-control-wrap checklist-control-select">
                            <select name="no_kendaraan" id="sppd-nopol" required>
                                <option value="">Pilih Nomor Kendaraan</option>
                                @foreach ($kendaraans as $k)
                                    <option value="{{ $k->nomor_kendaraan }}" data-jenis="{{ $k->jenis_kendaraan }}"
                                        @selected(old('no_kendaraan', $sppd?->no_kendaraan) === $k->nomor_kendaraan)>{{ $k->nomor_kendaraan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </label>
                    <label class="checklist-field">
                        <div class="checklist-control-wrap"><input type="text" name="jenis_kendaraan" id="sppd-jenis" required readonly value="{{ old('jenis_kendaraan', $sppd?->jenis_kendaraan) }}" placeholder="Otomatis terisi…"></div>
                    </label>
                    <label class="checklist-field checklist-field-span2" style="grid-column:1/-1">
                        <div class="checklist-control-wrap"><textarea name="tujuan" rows="2" required maxlength="2000" placeholder="Alamat / lokasi tujuan">{{ old('tujuan', $sppd?->tujuan) }}</textarea></div>
                    </label>
                </div>
                @php
                    $tollEmpty = ['dari_tol' => '', 'ke_tol' => '', 'harga' => ''];
                    $tollsBer = old('tolls_berangkat', null);
                    if ($tollsBer === null) {
                        $tollsBer = $sppd?->tolls
                            ? $sppd->tolls->where('leg', 'berangkat')->values()->map(fn ($t) => ['dari_tol' => $t->dari_tol, 'ke_tol' => $t->ke_tol, 'harga' => $t->harga])->all()
                            : [$tollEmpty];
                    }
                    if (! is_array($tollsBer) || $tollsBer === []) { $tollsBer = [$tollEmpty]; }
                    $tollsKem = old('tolls_kembali', null);
                    if ($tollsKem === null) {
                        $tollsKem = $sppd?->tolls
                            ? $sppd->tolls->where('leg', 'kembali')->values()->map(fn ($t) => ['dari_tol' => $t->dari_tol, 'ke_tol' => $t->ke_tol, 'harga' => $t->harga])->all()
                            : [$tollEmpty];
                    }
                    if (! is_array($tollsKem) || $tollsKem === []) { $tollsKem = [$tollEmpty]; }
                @endphp
                <div class="sppd-toll-leg-block">
                    <h3 class="sppd-toll-leg-title">Biaya Tol - Berangkat</h3>
                    <div id="sppd-tolls-berangkat-wrap" class="sppd-dynamic-wrap" data-tolls-leg="berangkat">
                        @foreach($tollsBer as $ti => $tr)
                        <div class="sppd-toll-line" data-toll-row>
                            <div class="sppd-row sppd-toll-inputs">
                                <label class="checklist-field"><div class="checklist-control-wrap"><input type="text" name="tolls_berangkat[{{ $ti }}][dari_tol]" value="{{ $tr['dari_tol'] ?? '' }}" @if($ti === 0) required @endif placeholder="Dari Tol"></div></label>
                                <label class="checklist-field"><div class="checklist-control-wrap"><input type="text" name="tolls_berangkat[{{ $ti }}][ke_tol]" value="{{ $tr['ke_tol'] ?? '' }}" @if($ti === 0) required @endif placeholder="Ke Tol"></div></label>
                                <label class="checklist-field"><div class="checklist-control-wrap"><input type="number" name="tolls_berangkat[{{ $ti }}][harga]" class="sppd-toll-harga" min="0" step="1" value="{{ $tr['harga'] ?? '' }}" @if($ti === 0) required @endif placeholder="Harga"></div></label>
                            </div>
                            @if($ti > 0)
                                <button type="button" class="sppd-line-remove" data-remove-toll title="Hapus baris tol" aria-label="Hapus baris tol"><i class="bi bi-dash-lg"></i></button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="sppd-add-row" id="sppd-add-toll-berangkat">+ Tambah tol berangkat</button>
                </div>
                <div class="sppd-toll-leg-block">
                    <h3 class="sppd-toll-leg-title">Biaya Tol - Kembali</h3>
                    <div id="sppd-tolls-kembali-wrap" class="sppd-dynamic-wrap" data-tolls-leg="kembali">
                        @foreach($tollsKem as $ti => $tr)
                        <div class="sppd-toll-line" data-toll-row>
                            <div class="sppd-row sppd-toll-inputs">
                                <label class="checklist-field"><div class="checklist-control-wrap"><input type="text" name="tolls_kembali[{{ $ti }}][dari_tol]" value="{{ $tr['dari_tol'] ?? '' }}" @if($ti === 0) required @endif placeholder="Dari Tol"></div></label>
                                <label class="checklist-field"><div class="checklist-control-wrap"><input type="text" name="tolls_kembali[{{ $ti }}][ke_tol]" value="{{ $tr['ke_tol'] ?? '' }}" @if($ti === 0) required @endif placeholder="Ke Tol"></div></label>
                                <label class="checklist-field"><div class="checklist-control-wrap"><input type="number" name="tolls_kembali[{{ $ti }}][harga]" class="sppd-toll-harga" min="0" step="1" value="{{ $tr['harga'] ?? '' }}" @if($ti === 0) required @endif placeholder="Harga"></div></label>
                            </div>
                            @if($ti > 0)
                                <button type="button" class="sppd-line-remove" data-remove-toll title="Hapus baris tol" aria-label="Hapus baris tol"><i class="bi bi-dash-lg"></i></button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="sppd-add-row" id="sppd-add-toll-kembali">+ Tambah tol kembali</button>
                </div>
                <div id="sppd-fuels-wrap" class="sppd-dynamic-wrap">
                    @php
                        $fuels = old('fuels', $sppd?->fuels?->map(fn($f) => ['liter' => $f->liter, 'harga_per_liter' => $f->harga_per_liter])->toArray() ?? [['liter' => '', 'harga_per_liter' => '']]);
                        if (empty($fuels)) $fuels = [['liter' => '', 'harga_per_liter' => '']];
                    @endphp
                    @foreach($fuels as $fi => $fr)
                    <div class="sppd-fuel-line" data-fuel-line>
                    <div class="sppd-fuel-block" data-fuel-row>
                        <h3 class="sppd-toll-leg-title">BBM</h3>
                        <div class="sppd-row">
                            <label class="checklist-field"><span>Liter</span><div class="checklist-control-wrap"><input type="number" name="fuels[{{ $fi }}][liter]" class="sppd-fuel-liter" min="0" step="0.01" @if($fi === 0) required @endif value="{{ $fr['liter'] ?? '' }}"></div></label>
                            <label class="checklist-field"><span>Harga / Liter</span><div class="checklist-control-wrap"><input type="number" name="fuels[{{ $fi }}][harga_per_liter]" class="sppd-fuel-hpl" min="0" step="1" @if($fi === 0) required @endif value="{{ $fr['harga_per_liter'] ?? '' }}"></div></label>
                            <label class="checklist-field"><span>Total</span><div class="checklist-control-wrap"><input type="text" class="sppd-fuel-total-display" readonly value="0"></div></label>
                        </div>
                    </div>
                    @if($fi > 0)
                        <button type="button" class="sppd-line-remove sppd-line-remove--fuel" data-remove-fuel title="Hapus baris BBM" aria-label="Hapus baris BBM"><i class="bi bi-dash-lg"></i></button>
                    @endif
                    </div>
                    @endforeach
                </div>
                <button type="button" class="sppd-add-row" id="sppd-add-fuel">+ Tambah baris BBM</button>
                <footer class="checklist-footer sppd-checklist-footer" style="margin-top: 20px;">
                    <div class="sppd-footer-right">
                        <div class="sppd-footer-actions">
                            <button type="button" class="checklist-nav-btn checklist-nav-next" id="sppd-next" style="width:100%">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                                Lihat Preview
                            </button>
                        </div>
                    </div>
                </footer>
            </section>
        </form>
    </main>
</div>
@endsection
