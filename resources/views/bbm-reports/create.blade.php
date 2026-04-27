<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Form Pengisian BBM — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/bbm-form.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Sama dengan resources/views/checklists/create.blade.php — banner per section */
        .bbm-form-page .section-banner {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
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
            box-shadow: none;
        }
        .bbm-form-page .section-banner::before {
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
        .bbm-form-page .section-banner-icon {
            color: #facc15;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }
        .bbm-form-page .section-banner span {
            position: relative;
            z-index: 1;
        }

        .bbm-form-page .bbm-page-head {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }
        .bbm-form-page .bbm-page-head-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #0b2c6b 0%, #123f8f 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #facc15;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(11, 44, 107, 0.25);
        }
        .bbm-form-page .bbm-page-head h1 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }
        .dash-body.dark .bbm-form-page .bbm-page-head h1 { color: #f1f5f9; }
        .bbm-form-page .bbm-page-head p {
            margin: 4px 0 0;
            font-size: 0.82rem;
            color: #64748b;
        }
        .dash-body.dark .bbm-form-page .bbm-page-head p { color: #94a3b8; }
    </style>
</head>
<body class="dash-body bbm-form-page">
    <div class="dash-bg-cubes" aria-hidden="true"></div>
    <div class="dash-bg-stardust" aria-hidden="true"></div>
    <div class="dash-bg-orb-gold" aria-hidden="true"></div>
    <div class="dash-bg-orb-blue" aria-hidden="true"></div>
    <div class="dash-bg-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 400" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%" preserveAspectRatio="none">
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50 L 1440 400 L 0 400 Z" fill="url(#bbm_fill)"></path>
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50" stroke="url(#bbm_stroke)" stroke-width="3" stroke-linecap="round"></path>
            <defs>
                <linearGradient id="bbm_fill" x1="720" y1="50" x2="720" y2="400" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#D4AF37" stop-opacity="0.12"></stop>
                    <stop offset="1" stop-color="#0A2342" stop-opacity="0"></stop>
                </linearGradient>
                <linearGradient id="bbm_stroke" x1="0" y1="150" x2="1440" y2="150" gradientUnits="userSpaceOnUse">
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
                    <div class="dash-nav-title">Laporan BBM</div>
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
                <a href="{{ route('dashboard') }}" class="dash-nav-btn-glass" aria-label="Dashboard">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="dash-nav-btn-label">Dashboard</span>
                </a>
            </div>
            <button class="dash-mobile-menu-btn" id="dash-mobile-menu-btn" aria-label="Buka Menu" aria-expanded="false">
                <i class="bi bi-list" id="dash-mobile-menu-icon"></i>
            </button>
        </div>
    </nav>

    <div class="checklist-shell" data-bbm-form>
        <main class="checklist-content">
            <form id="bbm-report-form" class="checklist-card" action="{{ route('bbm-reports.store') }}" method="post" enctype="multipart/form-data" data-dashboard-url="{{ route('dashboard') }}">
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
                        <span>Data Kendaraan</span>
                    </div>
                    <div class="checklist-grid-two">
                        <label class="checklist-field">
                            <span>Pilih Kendaraan</span>
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
                    </div>
                    <div class="checklist-grid-two bbm-datetime-grid">
                        <label class="checklist-field">
                            <span><i class="bi bi-calendar3 bbm-field-icon" aria-hidden="true"></i> Tanggal</span>
                            <div class="checklist-control-wrap bbm-input-with-icon">
                                <input type="date" name="tanggal" required value="">
                            </div>
                        </label>
                        <label class="checklist-field">
                            <span><i class="bi bi-clock bbm-field-icon" aria-hidden="true"></i> Waktu</span>
                            <div class="checklist-control-wrap bbm-input-with-icon">
                                <input type="time" name="waktu" required value="">
                            </div>
                        </label>
                    </div>
                </div>

                <div class="bbm-form-section">
                    <div class="section-banner">
                        <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <span>KM Odometer</span>
                    </div>
                    <div class="checklist-grid-two">
                        <label class="checklist-field">
                            <span>Sebelum</span>
                            <div class="checklist-control-wrap">
                                <input type="number" name="odometer_sebelum" required min="0" step="1" inputmode="numeric" value="{{ old('odometer_sebelum') }}" placeholder="0">
                            </div>
                        </label>
                        <label class="checklist-field">
                            <span>Sesudah</span>
                            <div class="checklist-control-wrap">
                                <input type="number" name="odometer_sesudah" required min="0" step="1" inputmode="numeric" value="{{ old('odometer_sesudah') }}" placeholder="0">
                            </div>
                        </label>
                    </div>
                </div>

                <div class="bbm-form-section">
                    <div class="section-banner">
                        <svg class="section-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M4 10h16v8H4z" stroke="currentColor" stroke-width="2"/><path d="M8 10V8a4 4 0 118 0v2" stroke="currentColor" stroke-width="2"/></svg>
                        <span>Detail BBM</span>
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
                    <div class="sppd-photo-pair bbm-photo-pair">
                        <label class="checklist-photo-slot" data-photo-preview-slot>
                            <input type="file" name="foto_odometer" accept="image/*" data-photo-single required>
                            <div class="photo-slot-placeholder"><span class="checklist-photo-icon"><i class="bi bi-camera"></i></span><strong>Foto Odometer</strong></div>
                            <img class="photo-slot-preview" alt="" style="display:none" src="">
                            <button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus">×</button>
                        </label>
                        <label class="checklist-photo-slot" data-photo-preview-slot>
                            <input type="file" name="foto_struk" accept="image/*" data-photo-single required>
                            <div class="photo-slot-placeholder"><span class="checklist-photo-icon"><i class="bi bi-receipt"></i></span><strong>Foto Struk</strong></div>
                            <img class="photo-slot-preview" alt="" style="display:none" src="">
                            <button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus">×</button>
                        </label>
                    </div>
                </div>

                <div class="bbm-submit-row">
                    <button type="submit" class="checklist-nav-btn checklist-nav-next bbm-submit-btn">
                        <i class="bi bi-send-fill bbm-submit-icon" aria-hidden="true"></i>
                        Kirim Laporan BBM
                    </button>
                </div>
            </form>
        </main>
    </div>

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
            }).then((r) => {
                if (r.isConfirmed) window.location.href = dashUrl;
            });
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
            }).then((r) => {
                if (r.isConfirmed) window.location.href = dashUrl;
            });
        }
    })();
    </script>
</body>
</html>
