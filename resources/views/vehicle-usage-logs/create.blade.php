<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Log Penggunaan Kendaraan — {{ config('app.name') }}</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/vehicle-usage-log.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            left: 0;
            top: 0;
            bottom: 0;
            width: 6px;
            background: #facc15;
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }
        .bbm-form-page .section-banner-icon { color: #facc15; flex-shrink: 0; position: relative; z-index: 1; }
        .bbm-form-page .section-banner span { position: relative; z-index: 1; }
        .bbm-form-page .bbm-page-head { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
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
        .bbm-form-page .bbm-page-head h1 { margin: 0; font-size: 1.35rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
        .dash-body.dark .bbm-form-page .bbm-page-head h1 { color: #f1f5f9; }
        .bbm-form-page .bbm-page-head p { margin: 4px 0 0; font-size: 0.82rem; color: #64748b; }
        .dash-body.dark .bbm-form-page .bbm-page-head p { color: #94a3b8; }
    </style>
</head>
<body class="dash-body bbm-form-page">
    @include('partials.premium-dash-bg', ['premiumBgId' => 'vul_form'])

    <nav class="dash-nav" id="dash-nav">
        <div class="dash-nav-inner">
            <div class="dash-nav-brand">
                <img src="{{ asset('images/VMS.png') }}" alt="VMS" class="dash-nav-logo">
                <div>
                    <div class="dash-nav-title">Log Penggunaan Kendaraan</div>
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
</body>
</html>
