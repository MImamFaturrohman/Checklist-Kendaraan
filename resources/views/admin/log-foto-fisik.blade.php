<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log Foto Fisik - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dash-body">
    <div class="admin-page-shell">
        <header class="checklist-topbar">
            <div>
                <h1 class="dash-brand-title">Log Foto Fisik</h1>
                <p class="dash-brand-sub">PT ARTHA DAYA COALINDO</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="dash-chip dash-chip-admin">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                    ADMIN
                </span>
                <a href="{{ route('dashboard') }}" class="checklist-icon-btn"><svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            </div>
        </header>

        {{-- Tab navigation --}}
        <div class="foto-tabs fade-in-up">
            <button class="foto-tab active" data-foto-tab="eksterior">Eksterior</button>
            <button class="foto-tab" data-foto-tab="interior">Interior</button>
            <button class="foto-tab" data-foto-tab="mesin">Mesin</button>
            <button class="foto-tab" data-foto-tab="bbm">BBM</button>
        </div>

        {{-- Eksterior --}}
        <div class="admin-card foto-panel active fade-in-up" data-foto-panel="eksterior">
            <h2 class="admin-card-title">Foto Eksterior</h2>
            @php $hasExtPhotos = false; @endphp
            <div class="foto-log-grid">
                @foreach($checklists as $c)
                    @if($c->exterior)
                        @foreach(['foto_depan' => 'Depan', 'foto_kanan' => 'Kanan', 'foto_kiri' => 'Kiri', 'foto_belakang' => 'Belakang'] as $field => $label)
                            @if($c->exterior->$field)
                                @php $hasExtPhotos = true; @endphp
                                <div class="foto-log-item">
                                    <img src="{{ asset('storage/' . $c->exterior->$field) }}" alt="{{ $label }}" loading="lazy">
                                    <div class="foto-log-info">
                                        <strong>{{ $c->nomor_kendaraan }}</strong>
                                        <span>{{ $label }} · {{ $c->tanggal->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>
            @unless($hasExtPhotos)
                <div class="admin-empty"><p>Belum ada foto eksterior.</p></div>
            @endunless
        </div>

        {{-- Interior --}}
        <div class="admin-card foto-panel fade-in-up" data-foto-panel="interior" style="display:none">
            <h2 class="admin-card-title">Foto Interior</h2>
            @php $hasIntPhotos = false; @endphp
            <div class="foto-log-grid">
                @foreach($checklists as $c)
                    @if($c->interior)
                        @for($i = 1; $i <= 3; $i++)
                            @php $f = "foto_{$i}"; @endphp
                            @if($c->interior->$f)
                                @php $hasIntPhotos = true; @endphp
                                <div class="foto-log-item">
                                    <img src="{{ asset('storage/' . $c->interior->$f) }}" alt="Interior {{ $i }}" loading="lazy">
                                    <div class="foto-log-info">
                                        <strong>{{ $c->nomor_kendaraan }}</strong>
                                        <span>Foto {{ $i }} · {{ $c->tanggal->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            @endif
                        @endfor
                    @endif
                @endforeach
            </div>
            @unless($hasIntPhotos)
                <div class="admin-empty"><p>Belum ada foto interior.</p></div>
            @endunless
        </div>

        {{-- Mesin --}}
        <div class="admin-card foto-panel fade-in-up" data-foto-panel="mesin" style="display:none">
            <h2 class="admin-card-title">Foto Mesin</h2>
            @php $hasMesinPhotos = false; @endphp
            <div class="foto-log-grid">
                @foreach($checklists as $c)
                    @if($c->mesin)
                        @for($i = 1; $i <= 3; $i++)
                            @php $f = "foto_{$i}"; @endphp
                            @if($c->mesin->$f)
                                @php $hasMesinPhotos = true; @endphp
                                <div class="foto-log-item">
                                    <img src="{{ asset('storage/' . $c->mesin->$f) }}" alt="Mesin {{ $i }}" loading="lazy">
                                    <div class="foto-log-info">
                                        <strong>{{ $c->nomor_kendaraan }}</strong>
                                        <span>Foto {{ $i }} · {{ $c->tanggal->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            @endif
                        @endfor
                    @endif
                @endforeach
            </div>
            @unless($hasMesinPhotos)
                <div class="admin-empty"><p>Belum ada foto mesin.</p></div>
            @endunless
        </div>

        {{-- BBM --}}
        <div class="admin-card foto-panel fade-in-up" data-foto-panel="bbm" style="display:none">
            <h2 class="admin-card-title">Foto BBM & Dashboard</h2>
            @php $hasBbmPhotos = false; @endphp
            <div class="foto-log-grid">
                @foreach($checklists as $c)
                    @if($c->foto_bbm_dashboard)
                        @php $hasBbmPhotos = true; @endphp
                        <div class="foto-log-item">
                            <img src="{{ asset('storage/' . $c->foto_bbm_dashboard) }}" alt="BBM Dashboard" loading="lazy">
                            <div class="foto-log-info">
                                <strong>{{ $c->nomor_kendaraan }}</strong>
                                <span>BBM {{ $c->level_bbm }}% · {{ $c->tanggal->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            @unless($hasBbmPhotos)
                <div class="admin-empty"><p>Belum ada foto BBM.</p></div>
            @endunless
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-foto-tab]').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.foto-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.foto-panel').forEach(p => { p.style.display = 'none'; p.classList.remove('active'); });
                tab.classList.add('active');
                const panel = document.querySelector(`[data-foto-panel="${tab.dataset.fotoTab}"]`);
                if (panel) { panel.style.display = ''; panel.classList.add('active'); }
            });
        });
    </script>
</body>
</html>
