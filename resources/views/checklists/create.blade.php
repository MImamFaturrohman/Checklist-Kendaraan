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
            $userRole      = $user->role ?? 'driver';
            $isDriver      = $userRole === 'driver';
            $isAdminRole   = $userRole === 'admin';
            $isManagerRole = $userRole === 'manager';
            $isPicRole     = $userRole === 'pic_kendaraan';
            $userRoleLabel = $isAdminRole ? 'ADMIN' : ($isManagerRole ? 'MANAGER' : ($isPicRole ? 'PIC KENDARAAN' : 'DRIVER'));
            $userName      = $user->name ?? $user->username ?? 'User';
        @endphp

        {{-- Background decoration layers (same as dashboard) --}}
        <div class="dash-bg-cubes" aria-hidden="true"></div>
        <div class="dash-bg-stardust" aria-hidden="true"></div>
        <div class="dash-bg-orb-gold" aria-hidden="true"></div>
        <div class="dash-bg-orb-blue" aria-hidden="true"></div>
        <div class="dash-bg-wave" aria-hidden="true">
            <svg viewBox="0 0 1440 400" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%" preserveAspectRatio="none">
                <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50 L 1440 400 L 0 400 Z" fill="url(#cl_fill)"></path>
                <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50" stroke="url(#cl_stroke)" stroke-width="3" stroke-linecap="round"></path>
                <path d="M0 350 C 400 380, 500 250, 900 300 C 1200 350, 1300 200, 1440 150" stroke="rgba(255,255,255,0.06)" stroke-width="2" stroke-dasharray="8 8"></path>
                <circle cx="700" cy="200" r="4" fill="#D4AF37"></circle>
                <circle cx="1000" cy="50" r="4" fill="#D4AF37"></circle>
                <defs>
                    <linearGradient id="cl_fill" x1="720" y1="50" x2="720" y2="400" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#D4AF37" stop-opacity="0.12"></stop>
                        <stop offset="1" stop-color="#0A2342" stop-opacity="0"></stop>
                    </linearGradient>
                    <linearGradient id="cl_stroke" x1="0" y1="150" x2="1440" y2="150" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#0A2342"></stop>
                        <stop offset="0.4" stop-color="#D4AF37"></stop>
                        <stop offset="1" stop-color="#60A5FA"></stop>
                    </linearGradient>
                </defs>
            </svg>
        </div>

        {{-- ══ NAVBAR ══ --}}
        <nav class="dash-nav" id="dash-nav">
            <div class="dash-nav-inner">

                {{-- Brand --}}
                <div class="dash-nav-brand">
                    <img src="{{ asset('images/VMS.png') }}" alt="VMS" class="dash-nav-logo">
                    <div>
                        <div class="dash-nav-title">Ceklist Kendaraan</div>
                        <span class="dash-nav-sub">PT ARTHA DAYA COALINDO</span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="dash-nav-actions" id="dash-nav-actions">

                    {{-- Theme toggle --}}
                    <button class="dash-theme-btn" id="dash-theme-toggle" title="Ganti Tema" aria-label="Toggle Tema">
                        <i class="bi bi-moon-fill" id="dash-theme-icon"></i>
                        <span class="dash-theme-mode-label" id="dash-theme-label">Dark Mode</span>
                    </button>

                    {{-- Role chip --}}
                    <span class="dash-chip {{ $isAdminRole ? 'dash-chip-admin' : ($isManagerRole ? 'dash-chip-manager' : 'dash-chip-driver') }}">
                        @if ($isAdminRole)
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
                        @elseif ($isManagerRole)
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        @else
                            <i class="bi bi-person-check-fill"></i>
                        @endif
                        <span class="dash-nav-chip-label">{{ $userRoleLabel }}</span>
                    </span>

                    {{-- Back to dashboard --}}
                    <a href="{{ route('dashboard') }}" class="dash-nav-btn-glass" aria-label="Kembali ke Dashboard">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                            <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="dash-nav-btn-label">Dashboard</span>
                    </a>

                </div>

                {{-- Mobile hamburger --}}
                <button class="dash-mobile-menu-btn" id="dash-mobile-menu-btn" aria-label="Buka Menu" aria-expanded="false">
                    <i class="bi bi-list" id="dash-mobile-menu-icon"></i>
                </button>

            </div>
        </nav>

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
                                    <input type="date" name="tanggal" id="input-tanggal" required
                                        {{ $isDriver ? 'readonly' : '' }}>
                                </div>
                            </label>
                            <label class="checklist-field">
                                <span>Shift</span>
                                <div class="checklist-control-wrap checklist-control-select">
                                    <select name="{{ $isDriver ? '_shift_display' : 'shift' }}"
                                            id="input-shift" required
                                            {{ $isDriver ? 'disabled' : '' }}>
                                        <option value="">Pilih Shift</option>
                                        <option value="Pagi">Pagi</option>
                                        <option value="Siang">Siang</option>
                                    </select>
                                    {{-- Hidden input agar nilai shift tetap terkirim meski select disabled --}}
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
                                    <input type="text" name="exterior_{{ $name }}_catatan" class="checklist-item-note" placeholder="Keterangan...(Wajib jika NO)">
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
                                    <input type="text" name="interior_{{ $name }}_catatan" class="checklist-item-note" placeholder="Keterangan...(Wajib jika NO)">
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
                                    <input type="text" name="mesin_{{ $name }}_catatan" class="checklist-item-note" placeholder="Keterangan...(Wajib Jika NO)">
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

                    {{-- ==================== STEP 8: PREVIEW ==================== --}}
                    <section class="wizard-step" data-step="8">
                        <div class="section-banner">
                            <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <span>8. Preview Ringkasan Checklist</span>
                        </div>
                        <div id="preview-content">
                            <div style="text-align:center;padding:48px 0;color:#94a3b8">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" style="margin-bottom:12px;opacity:.4"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                <p style="font-size:.88rem">Memuat ringkasan data...</p>
                            </div>
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

<script>
/* ── Theme Toggle (same as dashboard) ── */
(function () {
    const body  = document.body;
    const icon  = document.getElementById('dash-theme-icon');
    const btn   = document.getElementById('dash-theme-toggle');
    const label = document.getElementById('dash-theme-label');

    function applyTheme(isDark) {
        body.classList.toggle('dark', isDark);
        if (icon)  icon.className    = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        if (label) label.textContent = isDark ? 'Light Mode' : 'Dark Mode';
    }

    const saved = localStorage.getItem('vms-theme') || localStorage.getItem('vms-dash-theme');
    applyTheme(saved === 'dark');

    if (btn) {
        btn.addEventListener('click', function () {
            const next = !body.classList.contains('dark');
            applyTheme(next);
            localStorage.setItem('vms-theme', next ? 'dark' : 'light');
            localStorage.setItem('vms-dash-theme', next ? 'dark' : 'light');
        });
    }
})();

/* ── Mobile navbar dropdown ── */
(function () {
    const menuBtn    = document.getElementById('dash-mobile-menu-btn');
    const navActions = document.getElementById('dash-nav-actions');
    const menuIcon   = document.getElementById('dash-mobile-menu-icon');

    if (!menuBtn || !navActions) return;

    function closeMenu() {
        navActions.classList.remove('mobile-open');
        menuIcon.className = 'bi bi-list';
        menuBtn.setAttribute('aria-expanded', 'false');
    }

    menuBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = navActions.classList.toggle('mobile-open');
        menuIcon.className = isOpen ? 'bi bi-x-lg' : 'bi bi-list';
        menuBtn.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', function (e) {
        if (!navActions.contains(e.target) && !menuBtn.contains(e.target)) closeMenu();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
    });
})();
</script>

@if($isDriver)
<script>
(function () {
    /* ─ Deteksi shift berdasarkan jam ─ */
    function detectShift(hour) {
        if (hour >= 7 && hour < 12)  return 'Pagi';
        if (hour >= 12 && hour < 16) return 'Siang';
        return '';
    }

    /* ─ Auto-fill tanggal & shift ─ */
    const now   = new Date();
    const year  = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day   = String(now.getDate()).padStart(2, '0');
    const today = `${year}-${month}-${day}`;
    const shift = detectShift(now.getHours());

    const tanggalEl    = document.getElementById('input-tanggal');
    const shiftEl      = document.getElementById('input-shift');
    const shiftHidden  = document.getElementById('input-shift-hidden');

    /* Tanggal (readonly, nilai tetap terkirim) */
    if (tanggalEl && !tanggalEl.value) tanggalEl.value = today;

    /* Shift: isi select display + hidden input untuk submit */
    if (shift) {
        if (shiftEl) {
            Array.from(shiftEl.options).forEach(opt => {
                if (opt.value === shift) opt.selected = true;
            });
        }
        if (shiftHidden) shiftHidden.value = shift;
    }
})();
</script>
@endif
