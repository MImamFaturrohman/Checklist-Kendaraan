<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $isEdit ? 'Edit' : 'Buat' }} Rekap SPPD — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/sppd-form.js'])
    {{-- Section banner: sama dengan resources/views/checklists/create.blade.php --}}
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
        .sppd-form-page .section-banner-icon {
            color: #facc15;
            flex-shrink: 0;
        }
        .sppd-form-page .section-banner span {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="dash-body sppd-form-page">
    <div class="dash-bg-cubes" aria-hidden="true"></div>
    <div class="dash-bg-stardust" aria-hidden="true"></div>
    <div class="dash-bg-orb-gold" aria-hidden="true"></div>
    <div class="dash-bg-orb-blue" aria-hidden="true"></div>
    <div class="dash-bg-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 400" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%" preserveAspectRatio="none">
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50 L 1440 400 L 0 400 Z" fill="url(#sppdf_fill)"></path>
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50" stroke="url(#sppdf_stroke)" stroke-width="3" stroke-linecap="round"></path>
            <defs>
                <linearGradient id="sppdf_fill" x1="720" y1="50" x2="720" y2="400" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#D4AF37" stop-opacity="0.12"></stop>
                    <stop offset="1" stop-color="#0A2342" stop-opacity="0"></stop>
                </linearGradient>
                <linearGradient id="sppdf_stroke" x1="0" y1="150" x2="1440" y2="150" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#0A2342"></stop>
                    <stop offset="0.4" stop-color="#D4AF37"></stop>
                    <stop offset="1" stop-color="#60A5FA"></stop>
                </linearGradient>
            </defs>
        </svg>
    </div>

    <nav class="dash-nav" id="dash-nav">
        <div class="dash-nav-inner">
            <div class="dash-nav-brand">
                <img src="{{ asset('images/VMS.png') }}" alt="VMS" class="dash-nav-logo">
                <div>
                    <div class="dash-nav-title">Rekap SPPD</div>
                    <span class="dash-nav-sub">PT ARTHA DAYA COALINDO</span>
                </div>
            </div>
            <div class="dash-nav-actions" id="dash-nav-actions">
                <button class="dash-theme-btn" id="dash-theme-toggle" title="Ganti Tema" aria-label="Toggle Tema">
                    <i class="bi bi-moon-fill" id="dash-theme-icon"></i>
                    <span class="dash-theme-mode-label" id="dash-theme-label">Dark Mode</span>
                </button>
                <span class="dash-chip dash-chip-driver">
                    <i class="bi bi-person-check-fill"></i>
                    <span class="dash-nav-chip-label">DRIVER</span>
                </span>
                <a href="{{ route('sppd.index') }}" class="dash-nav-btn-glass" aria-label="Kembali">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="dash-nav-btn-label">Daftar</span>
                </a>
            </div>
            <button class="dash-mobile-menu-btn" id="dash-mobile-menu-btn" aria-label="Buka Menu" aria-expanded="false">
                <i class="bi bi-list" id="dash-mobile-menu-icon"></i>
            </button>
        </div>
    </nav>

    <div class="modal-overlay" id="sppd-result-modal" style="display:none">
        <div class="modal-box" id="sppd-result-modal-box">
            <div class="modal-icon" id="sppd-result-icon"></div>
            <h3 id="sppd-result-title"></h3>
            <p id="sppd-result-msg" class="sppd-result-msg"></p>
            <div class="modal-actions" id="sppd-result-actions"></div>
        </div>
    </div>

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
                            <div class="checklist-control-wrap"><input type="text" name="keperluan_dinas" required maxlength="500" value="{{ old('keperluan_dinas', $sppd?->keperluan_dinas) }}" placeholder="Contoh: Pengantaran dokumen ke site"></div>
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
                            <div class="checklist-control-wrap"><input type="text" name="jenis_kendaraan" id="sppd-jenis" required readonly value="{{ old('jenis_kendaraan', $sppd?->jenis_kendaraan) }}"></div>
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
                    <div id="sppd-tolls-wrap" class="sppd-dynamic-wrap">
                        @php
                            $tolls = old('tolls', $sppd?->tolls?->map(fn($t) => ['dari_tol' => $t->dari_tol, 'ke_tol' => $t->ke_tol, 'harga' => $t->harga])->toArray() ?? [['dari_tol' => '', 'ke_tol' => '', 'harga' => '']]);
                            if (empty($tolls)) $tolls = [['dari_tol' => '', 'ke_tol' => '', 'harga' => '']];
                        @endphp
                        @foreach($tolls as $ti => $tr)
                        <div class="sppd-toll-line" data-toll-row>
                            <div class="sppd-row sppd-toll-inputs">
                                <label class="checklist-field"><span>Dari Tol</span><div class="checklist-control-wrap"><input type="text" name="tolls[{{ $ti }}][dari_tol]" value="{{ $tr['dari_tol'] ?? '' }}" @if($ti === 0) required @endif></div></label>
                                <label class="checklist-field"><span>Ke Tol</span><div class="checklist-control-wrap"><input type="text" name="tolls[{{ $ti }}][ke_tol]" value="{{ $tr['ke_tol'] ?? '' }}" @if($ti === 0) required @endif></div></label>
                                <label class="checklist-field"><span>Harga</span><div class="checklist-control-wrap"><input type="number" name="tolls[{{ $ti }}][harga]" class="sppd-toll-harga" min="0" step="1" value="{{ $tr['harga'] ?? '' }}" @if($ti === 0) required @endif></div></label>
                            </div>
                            @if($ti > 0)
                                <button type="button" class="sppd-line-remove" data-remove-toll title="Hapus baris tol" aria-label="Hapus baris tol"><i class="bi bi-dash-lg"></i></button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="sppd-add-row" id="sppd-add-toll">+ Tambah baris tol</button>
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
                            $fuelModels = $sppd?->fuels ?? collect();
                        @endphp
                        @foreach($fuels as $fi => $fr)
                        @php $fm = $fuelModels->get($fi); @endphp
                        <div class="sppd-fuel-line" data-fuel-line>
                        <div class="sppd-fuel-block" data-fuel-row>
                            <div class="sppd-row">
                                <label class="checklist-field"><span>Liter</span><div class="checklist-control-wrap"><input type="number" name="fuels[{{ $fi }}][liter]" class="sppd-fuel-liter" min="0" step="0.01" @if($fi === 0) required @endif value="{{ $fr['liter'] ?? '' }}"></div></label>
                                <label class="checklist-field"><span>Harga / Liter</span><div class="checklist-control-wrap"><input type="number" name="fuels[{{ $fi }}][harga_per_liter]" class="sppd-fuel-hpl" min="0" step="1" @if($fi === 0) required @endif value="{{ $fr['harga_per_liter'] ?? '' }}"></div></label>
                                <label class="checklist-field"><span>Total</span><div class="checklist-control-wrap"><input type="text" class="sppd-fuel-total-display" readonly value="0"></div></label>
                            </div>
                            <div class="sppd-photo-pair">
                                <label class="checklist-photo-slot" data-photo-preview-slot>
                                    <input type="file" name="fuels[{{ $fi }}][odometer]" accept="image/*" data-photo-single @if($fi === 0 && (!$isEdit || !$fm?->odometer_path)) required @endif>
                                    @if($fm?->odometer_path)
                                        <input type="hidden" name="fuels[{{ $fi }}][odometer_existing]" value="{{ $fm->odometer_path }}">
                                    @endif
                                    <div class="photo-slot-placeholder"><span class="checklist-photo-icon"><i class="bi bi-camera"></i></span><strong>Foto Odometer</strong></div>
                                    <img class="photo-slot-preview" alt="" style="{{ $fm?->odometer_path ? 'display:block' : 'display:none' }}" src="{{ $fm?->odometer_path ? \Illuminate\Support\Facades\Storage::url($fm->odometer_path) : '' }}">
                                    <button type="button" class="photo-slot-remove" style="{{ $fm?->odometer_path ? 'display:flex' : 'display:none' }}" aria-label="Hapus">×</button>
                                </label>
                                <label class="checklist-photo-slot" data-photo-preview-slot>
                                    <input type="file" name="fuels[{{ $fi }}][struk]" accept="image/*" data-photo-single @if($fi === 0 && (!$isEdit || !$fm?->struk_path)) required @endif>
                                    @if($fm?->struk_path)
                                        <input type="hidden" name="fuels[{{ $fi }}][struk_existing]" value="{{ $fm->struk_path }}">
                                    @endif
                                    <div class="photo-slot-placeholder"><span class="checklist-photo-icon"><i class="bi bi-receipt"></i></span><strong>Foto Struk</strong></div>
                                    <img class="photo-slot-preview" alt="" style="{{ $fm?->struk_path ? 'display:block' : 'display:none' }}" src="{{ $fm?->struk_path ? \Illuminate\Support\Facades\Storage::url($fm->struk_path) : '' }}">
                                    <button type="button" class="photo-slot-remove" style="{{ $fm?->struk_path ? 'display:flex' : 'display:none' }}" aria-label="Hapus">×</button>
                                </label>
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
                        <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 2v20M17 7H9.5a3.5 3.5 0 000 7H14a3.5 3.5 0 010 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <span>Ringkasan & Tanda Tangan</span>
                    </div>
                    <div class="sppd-summary-grid">
                        <div><span class="sppd-sum-label">Total Tol</span><strong id="sppd-sum-tol">Rp 0</strong></div>
                        <div><span class="sppd-sum-label">Total BBM</span><strong id="sppd-sum-bbm">Rp 0</strong></div>
                        <div class="sppd-sum-grand"><span class="sppd-sum-label">Grand Total</span><strong id="sppd-sum-grand">Rp 0</strong></div>
                    </div>
                    <p class="sppd-preview-label">Preview input</p>
                    <div id="sppd-live-preview" class="sppd-live-preview"></div>
                    <div class="signature-row">
                        <div class="signature-block">
                            <span class="signature-label">TTD DRIVER</span>
                            <div class="signature-pad-wrap">
                                <canvas id="sppd-sig-pad" class="signature-canvas"></canvas>
                                <div class="signature-pad-hint" data-sppd-sig-hint><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z" stroke="currentColor" stroke-width="2"/></svg><span>TAP TO SIGN</span></div>
                            </div>
                            <button type="button" class="signature-clear-btn" id="sppd-sig-clear">Hapus TTD</button>
                        </div>
                    </div>
                    <input type="hidden" name="tanda_tangan" id="sppd-sig-data" value="">
                </section>

                <div class="checklist-nav-row sppd-form-footer">
                    <a href="{{ route('sppd.index') }}" class="checklist-nav-btn checklist-nav-back sppd-footer-cancel">Batal</a>
                    <div class="sppd-footer-actions">
                        <button type="button" class="checklist-nav-btn checklist-nav-back" id="sppd-prev" disabled>Sebelumnya</button>
                        <button type="button" class="checklist-nav-btn checklist-nav-next" id="sppd-next">Selanjutnya</button>
                        <button type="submit" class="checklist-nav-btn checklist-nav-next final" id="sppd-submit" style="display:none">Submit Rekap SPPD</button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script>
    (function () {
        const body = document.body;
        const themeBtn = document.getElementById('dash-theme-toggle');
        const themeIcon = document.getElementById('dash-theme-icon');
        const themeLabel = document.getElementById('dash-theme-label');
        const navActions = document.getElementById('dash-nav-actions');
        const menuBtn = document.getElementById('dash-mobile-menu-btn');
        const menuIcon = document.getElementById('dash-mobile-menu-icon');

        const applyTheme = (isDark) => {
            body.classList.toggle('dark', isDark);
            if (themeIcon) themeIcon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            if (themeLabel) themeLabel.textContent = isDark ? 'Light Mode' : 'Dark Mode';
        };
        const saved = localStorage.getItem('vms-theme') || localStorage.getItem('vms-dash-theme');
        applyTheme(saved === 'dark');
        themeBtn?.addEventListener('click', () => {
            const next = !body.classList.contains('dark');
            applyTheme(next);
            localStorage.setItem('vms-theme', next ? 'dark' : 'light');
            localStorage.setItem('vms-dash-theme', next ? 'dark' : 'light');
        });

        const closeMobileMenu = () => {
            navActions?.classList.remove('mobile-open');
            if (menuIcon) menuIcon.className = 'bi bi-list';
            menuBtn?.setAttribute('aria-expanded', 'false');
        };
        menuBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = navActions?.classList.toggle('mobile-open');
            if (menuIcon) menuIcon.className = isOpen ? 'bi bi-x-lg' : 'bi bi-list';
            menuBtn.setAttribute('aria-expanded', String(!!isOpen));
        });
        document.addEventListener('click', (e) => {
            if (!navActions?.contains(e.target) && !menuBtn?.contains(e.target)) closeMobileMenu();
        });
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) closeMobileMenu();
        });
    })();
    </script>
</body>
</html>
