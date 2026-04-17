<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Buat Ceklist Baru - {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .section-banner {
    display: flex;
    align-items: center;
    gap: 10px;

    padding: 12px 16px;
    border-radius: 12px;

    /* Gradient biru seperti gambar */
    background: linear-gradient(
        90deg,
        #0b2c6b 0%,
        #123f8f 50%,
        #3b5fa8 75%,
        #dfe6f3 100%
    );

    color: white;
    font-weight: 600;
    font-size: 16px;

    position: relative;
    overflow: hidden;
    }

    /* Garis kuning di kiri */
    .section-banner::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 6px;
        background: #facc15; /* kuning */
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }

    /* Icon biar lebih kontras */
    .section-banner-icon {
        color: #facc15;
        flex-shrink: 0;
    }

    /* Text */
    .section-banner span {
        position: relative;
        z-index: 1;
    }

    background: linear-gradient(
    90deg,
    #0b2c6b 0%,
    #123f8f 40%,
    rgba(59, 95, 168, 0.6) 70%,
    rgba(223, 230, 243, 0.2) 100%
    );

    .bbm-slider {
    -webkit-appearance: none;
    width: 100%;
    height: 6px;
    border-radius: 999px;
    outline: none;

    /* Background default (akan dioverride pakai JS biar dinamis) */
    background: linear-gradient(to right, #facc15 50%, #e5e7eb 50%);
    }

    /* Track (Chrome, Safari) */
    .bbm-slider::-webkit-slider-runnable-track {
        height: 6px;
        border-radius: 999px;
    }

    /* Thumb (bulatan) */
    .bbm-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        height: 18px;
        width: 18px;
        border-radius: 50%;
        background: white;
        border: 2px solid #e5e7eb;
        margin-top: -6px; /* biar center */

        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        cursor: pointer;
    }

    /* Firefox */
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
        height: 18px;
        width: 18px;
        border-radius: 50%;
        background: white;
        border: 2px solid #e5e7eb;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        cursor: pointer;
    }
        </style>
    </head>
    <body class="dash-body">
        @php
            $userRole = $user->role ?? 'driver';
            $isDriver = $userRole === 'driver';
        @endphp

        {{-- PDF Result Modal --}}
        <div class="modal-overlay" id="pdf-modal" style="display:none">
            <div class="modal-box" id="pdf-modal-box">
                <div class="modal-icon" id="pdf-modal-icon"></div>
                <h3 id="pdf-modal-title"></h3>
                <p id="pdf-modal-message"></p>
                <div class="modal-actions" id="pdf-modal-actions"></div>
            </div>
        </div>

        <div class="checklist-shell" data-checklist-wizard>
            <header class="checklist-topbar">
                <div>
                    <h1 class="dash-brand-title">Ceklist Kendaraan</h1>
                    <p class="dash-brand-sub">PT ARTHA DAYA COALINDO</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="dash-chip {{ $isDriver ? 'dash-chip-driver' : 'dash-chip-admin' }}">
                        @if (!$isDriver)
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
                        @else
                            <i class="bi bi-person-check-fill"></i>
                        @endif
                        {{ $isDriver ? 'DRIVER' : 'ADMIN' }}
                    </span>
                    <a href="{{ route('dashboard') }}" class="checklist-icon-btn" aria-label="Kembali ke dashboard">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>
            </header>

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
                                    <input type="date" name="tanggal" required>
                                </div>
                            </label>
                            <label class="checklist-field">
                                <span>Shift</span>
                                <div class="checklist-control-wrap checklist-control-select">
                                    <select name="shift" required>
                                        <option value="">Pilih Shift</option>
                                        <option>Pagi</option>
                                        <option>Siang</option>
                                    </select>
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
                                            @php
                                                $isActiveDriver = $user->id === $d->id;
                                                $driverIcon = $isActiveDriver ? $iconNamaAktif : $iconNamaBiasa;
                                            @endphp
                                            <option value="{{ $d->name }}" data-icon="{{ $driverIcon }}" data-active="{{ $isActiveDriver ? '1' : '0' }}">{{ $d->name }}</option>
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
                                            @php
                                                $isActiveDriver = $user->id === $d->id;
                                                $driverIcon = $isActiveDriver ? $iconNamaAktif : $iconNamaBiasa;
                                            @endphp
                                            <option value="{{ $d->name }}" data-icon="{{ $driverIcon }}" data-active="{{ $isActiveDriver ? '1' : '0' }}">{{ $d->name }}</option>
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
                        <label class="checklist-field">
                            <span>Catatan Kondisi Exterior</span>
                            <textarea name="exterior_catatan" rows="3" placeholder="Isi catatan bila ada temuan..."></textarea>
                        </label>
                        <div class="checklist-field">
                            <span>Foto Bukti Exterior (Wajib 4 Sisi)</span>
                            <div class="checklist-photo-grid checklist-photo-grid-4">
                                @foreach (['depan', 'kanan', 'kiri', 'belakang'] as $side)
                                    <label class="checklist-photo-slot" data-photo-preview-slot>
                                        <input type="file" name="exterior_foto_{{ $side }}" accept="image/*" capture="environment" required data-photo-single>
                                        <div class="photo-slot-placeholder">
                                            <span class="checklist-photo-icon">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="10" r="1.4" stroke="currentColor" stroke-width="1.6"/><path d="M20 15L15.3 10.5L8 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </span>
                                            <strong>{{ strtoupper($side) }}</strong>
                                        </div>
                                        <img class="photo-slot-preview" alt="Preview {{ $side }}" style="display:none">
                                        <button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus foto">×</button>
                                    </label>
                                @endforeach
                            </div>
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
                        <label class="checklist-field"><span>Catatan Kondisi Interior</span><textarea name="interior_catatan" rows="3" placeholder="Isi catatan bila ada temuan..."></textarea></label>
                        <div class="checklist-field">
                            <span>Foto Interior (Wajib min. 1, maks 3)</span>
                            <div class="dynamic-photo-container" data-dynamic-photos data-section="interior" data-max="3" data-min-required="1">
                                <div class="dynamic-photo-grid">
                                    <label class="checklist-photo-slot" data-photo-preview-slot>
                                        <input type="file" name="interior_foto_1" accept="image/*" capture="environment" required data-photo-single>
                                        <div class="photo-slot-placeholder"><span class="checklist-photo-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="10" r="1.4" stroke="currentColor" stroke-width="1.6"/><path d="M20 15L15.3 10.5L8 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span><strong>FOTO 1</strong></div>
                                        <img class="photo-slot-preview" alt="Preview" style="display:none"><button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus foto">×</button>
                                    </label>
                                    <button type="button" class="dynamic-photo-add-btn" data-add-photo-btn aria-label="Tambah foto"><svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg></button>
                                </div>
                            </div>
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
                        <label class="checklist-field"><span>Catatan Kondisi Mesin & Operasional</span><textarea name="mesin_catatan" rows="3" placeholder="Isi catatan bila ada temuan..."></textarea></label>
                        <div class="checklist-field">
                            <span>Foto Ruang Mesin (Wajib min. 1, maks 3)</span>
                            <div class="dynamic-photo-container" data-dynamic-photos data-section="mesin" data-max="3" data-min-required="1">
                                <div class="dynamic-photo-grid">
                                    <label class="checklist-photo-slot" data-photo-preview-slot>
                                        <input type="file" name="mesin_foto_1" accept="image/*" capture="environment" required data-photo-single>
                                        <div class="photo-slot-placeholder"><span class="checklist-photo-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="10" r="1.4" stroke="currentColor" stroke-width="1.6"/><path d="M20 15L15.3 10.5L8 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span><strong>FOTO 1</strong></div>
                                        <img class="photo-slot-preview" alt="Preview" style="display:none"><button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus foto">×</button>
                                    </label>
                                    <button type="button" class="dynamic-photo-add-btn" data-add-photo-btn aria-label="Tambah foto"><svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg></button>
                                </div>
                            </div>
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
                                <input type="file" name="foto_bbm_dashboard" accept="image/*" capture="environment" required data-photo-single>
                                <div class="photo-slot-placeholder"><span class="checklist-photo-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="10" r="1.4" stroke="currentColor" stroke-width="1.6"/><path d="M20 15L15.3 10.5L8 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span><strong>FOTO INDIKATOR BBM & DASHBOARD</strong></div>
                                <img class="photo-slot-preview" alt="Preview BBM" style="display:none"><button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus foto">×</button>
                            </label>
                        </div>
                        <div class="checklist-field" style="margin-top:10px">
                            <span>PENGISIAN BBM TERAKHIR</span>
                            <div class="bbm-terakhir-row">
                                <div class="checklist-control-wrap checklist-control-date"><input type="date" name="bbm_terakhir_date" required></div>
                                <div class="checklist-control-wrap checklist-control-time"><input type="time" name="bbm_terakhir_time" required></div>
                            </div>
                        </div>
                        <div class="km-row" style="margin-top:14px">
                            <div class="km-card"><span class="km-card-label">KM AWAL (SAAT INI)</span><input type="number" name="km_awal" id="km_awal" min="0" value="0" readonly required class="km-card-value"></div>
                            <div class="km-card"><span class="km-card-label">KM AKHIR (SELESAI)</span><input type="number" name="km_akhir" id="km_akhir" min="0" required class="km-card-value km-card-editable"></div>
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
                            <span>Saya mengonfirmasi bahwa seluruh data di atas adalah benar dan valid.</span>
                        </label>

                        <div class="form-complete-alert" id="form-complete-alert" style="display:none">
                            <div class="form-complete-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/></svg></div>
                            <div><strong>Laporan Siap Dibuat!</strong><p>Tekan tombol Generate to PDF di bawah untuk mengakhiri sesi dan menyimpan dokumen.</p></div>
                        </div>
                    </section>
                </form>
            </main>

            <footer class="checklist-footer">
                <button type="button" class="checklist-nav-btn checklist-nav-back" id="wizard-prev" disabled>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="button" class="checklist-nav-btn checklist-nav-next" id="wizard-next">
                    LANJUT
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </footer>
        </div>
    </body>
</html>
