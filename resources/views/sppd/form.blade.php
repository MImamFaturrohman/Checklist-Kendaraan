@extends('layouts.dash-app')

@section('title', ($isEdit ? 'Edit' : 'Buat') . ' Rekap SPPD')
@section('bodyClass', 'sppd-form-page')
@section('pageTitle', 'TransDinas')
@section('pageSubtitle', 'Buat Rekap SPPD')

@push('styles')
<meta name="turbo-cache-control" content="no-cache">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
@vite(['resources/js/sppd-form.js'])
@endpush

@push('styles')
<style>
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

    .sppd-checklist-footer .sppd-footer-right {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        min-width: 0;
        flex-wrap: wrap;
    }
    .sppd-checklist-footer .sppd-footer-actions {
        display: flex;
        flex: 1;
        min-width: 0;
        gap: 10px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }
    .sppd-checklist-footer .sppd-footer-actions .checklist-nav-next {
        flex: 1;
        min-width: 0;
        justify-content: center;
    }
    #sppd-next.sppd-next--hidden {
        display: none !important;
    }
    #sppd-submit.sppd-submit--hidden {
        display: none !important;
    }
    .sppd-footer-cancel {
        text-decoration: none;
        text-align: center;
        white-space: nowrap;
    }
    @media (max-width: 720px) {
        .sppd-checklist-footer {
            grid-template-columns: 48px 1fr;
        }
        .sppd-checklist-footer .sppd-footer-right {
            flex-direction: column;
            align-items: stretch;
        }
        .sppd-checklist-footer .sppd-footer-actions {
            width: 100%;
            flex-direction: column;
        }
        .sppd-checklist-footer .sppd-footer-actions .checklist-nav-next,
        .sppd-checklist-footer .sppd-footer-cancel {
            width: 100%;
        }
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
@endsection

@section('content')
<div class="checklist-shell" data-sppd-form>
    <main class="checklist-content">
        <form id="sppd-form" class="checklist-card"
              action="{{ $isEdit ? route('sppd.update', $sppd) : route('sppd.store') }}"
              method="post" enctype="multipart/form-data">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="checklist-progress-head">
                <div class="checklist-progress-info">
                    <span id="sppd-step-label">LANGKAH 1 DARI 4</span>
                    <span id="sppd-progress-pct">25%</span>
                </div>
                <div class="checklist-progress-track">
                    <span id="sppd-progress-fill" style="width:25%"></span>
                </div>
            </div>

            <section class="wizard-step active" data-sppd-step="1">
                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M16 7a4 4 0 10-8 0c0 4 4 7 4 7s4-3 4-7z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="7" r="1.5" fill="currentColor"/></svg>
                    <span>Data perjalanan</span>
                </div>
                <div class="checklist-grid-two">
                    <label class="checklist-field">
                        <span>Nama Driver</span>
                        <div class="checklist-control-wrap"><input type="text" name="nama_driver_display" value="{{ $user->name ?? $user->username }}" readonly class="checklist-input-readonly"></div>
                    </label>
                    <label class="checklist-field">
                        <span>Tanggal Dinas</span>
                        <div class="checklist-control-wrap checklist-control-date">
                            <input type="date" name="tanggal_dinas" required value="{{ old('tanggal_dinas', $sppd?->tanggal_dinas?->format('Y-m-d')) }}">
                        </div>
                    </label>
                    <label class="checklist-field checklist-field-span2" style="grid-column:1/-1">
                        <span>Keperluan Dinas</span>
                        <div class="checklist-control-wrap"><input type="text" name="keperluan_dinas" required maxlength="500" value="{{ old('keperluan_dinas', $sppd?->keperluan_dinas) }}" placeholder="Contoh: Pengantaran dokumen"></div>
                    </label>
                    <label class="checklist-field">
                        <span>No Kendaraan</span>
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
                        <span>Jenis Kendaraan</span>
                        <div class="checklist-control-wrap"><input type="text" name="jenis_kendaraan" id="sppd-jenis" required readonly value="{{ old('jenis_kendaraan', $sppd?->jenis_kendaraan) }}" placeholder="Otomatis terisi…"></div>
                    </label>
                    <label class="checklist-field checklist-field-span2" style="grid-column:1/-1">
                        <span>Tujuan</span>
                        <div class="checklist-control-wrap"><textarea name="tujuan" rows="2" required maxlength="2000" placeholder="Alamat / lokasi tujuan">{{ old('tujuan', $sppd?->tujuan) }}</textarea></div>
                    </label>
                </div>
            </section>

            <section class="wizard-step" data-sppd-step="2">
                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M4 17h16M6 13l3-8h6l3 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span>Biaya Tol</span>
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
                    <h3 class="sppd-toll-leg-title">Biaya tol berangkat</h3>
                    <div id="sppd-tolls-berangkat-wrap" class="sppd-dynamic-wrap" data-tolls-leg="berangkat">
                        @foreach($tollsBer as $ti => $tr)
                        <div class="sppd-toll-line" data-toll-row>
                            <div class="sppd-row sppd-toll-inputs">
                                <label class="checklist-field"><span>Dari Tol</span><div class="checklist-control-wrap"><input type="text" name="tolls_berangkat[{{ $ti }}][dari_tol]" value="{{ $tr['dari_tol'] ?? '' }}" @if($ti === 0) required @endif></div></label>
                                <label class="checklist-field"><span>Ke Tol</span><div class="checklist-control-wrap"><input type="text" name="tolls_berangkat[{{ $ti }}][ke_tol]" value="{{ $tr['ke_tol'] ?? '' }}" @if($ti === 0) required @endif></div></label>
                                <label class="checklist-field"><span>Harga</span><div class="checklist-control-wrap"><input type="number" name="tolls_berangkat[{{ $ti }}][harga]" class="sppd-toll-harga" min="0" step="1" value="{{ $tr['harga'] ?? '' }}" @if($ti === 0) required @endif></div></label>
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
                    <h3 class="sppd-toll-leg-title">Biaya tol kembali</h3>
                    <div id="sppd-tolls-kembali-wrap" class="sppd-dynamic-wrap" data-tolls-leg="kembali">
                        @foreach($tollsKem as $ti => $tr)
                        <div class="sppd-toll-line" data-toll-row>
                            <div class="sppd-row sppd-toll-inputs">
                                <label class="checklist-field"><span>Dari Tol</span><div class="checklist-control-wrap"><input type="text" name="tolls_kembali[{{ $ti }}][dari_tol]" value="{{ $tr['dari_tol'] ?? '' }}" @if($ti === 0) required @endif></div></label>
                                <label class="checklist-field"><span>Ke Tol</span><div class="checklist-control-wrap"><input type="text" name="tolls_kembali[{{ $ti }}][ke_tol]" value="{{ $tr['ke_tol'] ?? '' }}" @if($ti === 0) required @endif></div></label>
                                <label class="checklist-field"><span>Harga</span><div class="checklist-control-wrap"><input type="number" name="tolls_kembali[{{ $ti }}][harga]" class="sppd-toll-harga" min="0" step="1" value="{{ $tr['harga'] ?? '' }}" @if($ti === 0) required @endif></div></label>
                            </div>
                            @if($ti > 0)
                                <button type="button" class="sppd-line-remove" data-remove-toll title="Hapus baris tol" aria-label="Hapus baris tol"><i class="bi bi-dash-lg"></i></button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="sppd-add-row" id="sppd-add-toll-kembali">+ Tambah tol kembali</button>
                </div>
            </section>

            <section class="wizard-step" data-sppd-step="3">
                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M4 10h16v8H4z" stroke="currentColor" stroke-width="2"/><path d="M8 10V8a4 4 0 118 0v2" stroke="currentColor" stroke-width="2"/></svg>
                    <span>BBM</span>
                </div>
                <div id="sppd-fuels-wrap" class="sppd-dynamic-wrap">
                    @php
                        $fuels = old('fuels', $sppd?->fuels?->map(fn($f) => ['liter' => $f->liter, 'harga_per_liter' => $f->harga_per_liter])->toArray() ?? [['liter' => '', 'harga_per_liter' => '']]);
                        if (empty($fuels)) $fuels = [['liter' => '', 'harga_per_liter' => '']];
                    @endphp
                    @foreach($fuels as $fi => $fr)
                    <div class="sppd-fuel-line" data-fuel-line>
                    <div class="sppd-fuel-block" data-fuel-row>
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
            </section>

            <section class="wizard-step" data-sppd-step="4">
                <div class="section-banner">
                    <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span>Ringkasan</span>
                </div>
                <p class="sppd-step4-lead">Periksa kembali seluruh data berikut. Jika sudah benar, kirim rekap SPPD.</p>
                <div id="sppd-step4-summary" class="sppd-step4-summary" aria-live="polite"></div>
                <div class="sppd-summary-grid sppd-summary-grid--step4">
                    <div><span class="sppd-sum-label">Total Tol</span><strong id="sppd-sum-tol">Rp 0</strong></div>
                    <div><span class="sppd-sum-label">Total BBM</span><strong id="sppd-sum-bbm">Rp 0</strong></div>
                    <div class="sppd-sum-grand"><span class="sppd-sum-label">Grand Total</span><strong id="sppd-sum-grand">Rp 0</strong></div>
                </div>
            </section>
        </form>
    </main>

    <footer class="checklist-footer sppd-checklist-footer">
        <button type="button" class="checklist-nav-btn checklist-nav-back" id="sppd-prev" disabled aria-label="Sebelumnya">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="sppd-footer-right">
            <div class="sppd-footer-actions">
                <button type="button" class="checklist-nav-btn checklist-nav-next" id="sppd-next">
                    LANJUT
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="submit" form="sppd-form" class="checklist-nav-btn checklist-nav-next final bbm-submit-btn sppd-submit--hidden" id="sppd-submit" aria-hidden="true">
                    <i class="bi bi-send-fill bbm-submit-icon" aria-hidden="true"></i>
                    Kirim Rekap SPPD
                </button>
            </div>
        </div>
    </footer>
</div>
@endsection
