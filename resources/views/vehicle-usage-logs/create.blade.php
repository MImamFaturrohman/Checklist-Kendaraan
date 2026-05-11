@extends('layouts.dash-app')

@section('title', 'Log Penggunaan Kendaraan')
@section('bodyClass', 'bbm-form-page')
@section('pageTitle', 'Log Penggunaan Kendaraan')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@vite(['resources/js/vehicle-usage-log.js'])
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
</style>
@endpush

@section('content')
<div class="checklist-shell" data-vehicle-usage-form>
    <main class="checklist-content">
        <form id="vehicle-usage-log-form" class="checklist-card" action="{{ route('vehicle-usage-logs.store') }}" method="post" data-dashboard-url="{{ route('dashboard') }}">
            @csrf

            @if ($errors->any())
                <div class="bbm-nojs-errors" role="alert">
                    <strong>Periksa kembali:</strong>
                    <ul>@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bbm-form-section">
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
                            <select name="nomor_kendaraan" id="vul-nopol" required>
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
                        <div class="checklist-control-wrap bbm-input-with-icon">
                            <input type="time" name="jam_awal" id="vul-jam-awal" required value="{{ old('jam_awal') }}">
                        </div>
                    </label>
                    <label class="checklist-field">
                        <span><i class="bi bi-clock-history bbm-field-icon" aria-hidden="true"></i> Jam Selesai</span>
                        <div class="checklist-control-wrap bbm-input-with-icon">
                            <input type="time" name="jam_akhir" id="vul-jam-akhir" required value="{{ old('jam_akhir') }}">
                        </div>
                    </label>
                    <label class="checklist-field checklist-field-span2">
                        <span>Keperluan</span>
                        <div class="checklist-control-wrap">
                            <textarea name="keperluan" rows="4" required placeholder="Jelaskan keperluan penggunaan kendaraan…" maxlength="10000">{{ old('keperluan') }}</textarea>
                        </div>
                    </label>
                </div>
            </div>

            <div class="bbm-submit-row">
                <button type="submit" class="checklist-nav-btn checklist-nav-next bbm-submit-btn" id="vul-submit">
                    <i class="bi bi-send-fill bbm-submit-icon" aria-hidden="true"></i>
                    Kirim log
                </button>
            </div>
        </form>
    </main>
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
            title: 'Berhasil',
            text: vulOk,
            confirmButtonText: 'Kembali ke Dashboard',
        }).then((r) => { if (r.isConfirmed) window.location.href = dashUrl; });
    }
})();
</script>
@endpush
