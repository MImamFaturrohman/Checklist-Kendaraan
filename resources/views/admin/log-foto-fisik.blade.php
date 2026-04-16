<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log Foto Fisik - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dash-body">
    <div class="admin-shell">
        <header class="checklist-topbar">
            <div><h1 class="dash-brand-title">Log Foto Fisik</h1><p class="dash-brand-sub">PT ARTHA DAYA COALINDO</p></div>
            <div class="flex items-center gap-2">
                <span class="dash-chip dash-chip-admin">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
                    ADMIN
                </span>
                <a href="{{ route('dashboard') }}" class="checklist-icon-btn"><svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            </div>
        </header>
        <div class="admin-card" data-tab-group>
            <div class="admin-tabs">
                <button class="admin-tab active" data-tab-btn="exterior">Eksterior</button>
                <button class="admin-tab" data-tab-btn="interior">Interior</button>
                <button class="admin-tab" data-tab-btn="mesin">Mesin</button>
                <button class="admin-tab" data-tab-btn="bbm">BBM</button>
            </div>

            {{-- Exterior photos --}}
            <div data-tab-panel="exterior">
                @php $extCount = 0; @endphp
                @foreach($checklists as $c)
                    @foreach(['foto_depan','foto_kanan','foto_kiri','foto_belakang'] as $f)
                        @if($c->exterior?->$f) @php $extCount++; @endphp @endif
                    @endforeach
                @endforeach
                <p style="font-size:0.78rem;color:#64748b;margin-bottom:10px;font-weight:600">{{ $extCount }} foto eksterior ditemukan</p>
                <div class="photo-log-grid">
                    @foreach($checklists as $c)
                        @foreach(['foto_depan'=>'Depan','foto_kanan'=>'Kanan','foto_kiri'=>'Kiri','foto_belakang'=>'Belakang'] as $f => $label)
                            @if($c->exterior?->$f)
                            <div class="photo-log-item">
                                <img src="{{ Storage::disk('public')->url($c->exterior->$f) }}" alt="{{ $label }}" loading="lazy">
                                <span class="photo-log-badge">{{ $c->nomor_kendaraan }} · {{ $label }}</span>
                            </div>
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </div>

            {{-- Interior photos --}}
            <div data-tab-panel="interior" style="display:none">
                @php $intCount = 0; @endphp
                @foreach($checklists as $c)
                    @foreach(['foto_1','foto_2','foto_3'] as $f)
                        @if($c->interior?->$f) @php $intCount++; @endphp @endif
                    @endforeach
                @endforeach
                <p style="font-size:0.78rem;color:#64748b;margin-bottom:10px;font-weight:600">{{ $intCount }} foto interior ditemukan</p>
                <div class="photo-log-grid">
                    @foreach($checklists as $c)
                        @foreach(['foto_1','foto_2','foto_3'] as $idx => $f)
                            @if($c->interior?->$f)
                            <div class="photo-log-item">
                                <img src="{{ Storage::disk('public')->url($c->interior->$f) }}" alt="Interior" loading="lazy">
                                <span class="photo-log-badge">{{ $c->nomor_kendaraan }} · Int {{ $idx+1 }}</span>
                            </div>
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </div>

            {{-- Mesin photos --}}
            <div data-tab-panel="mesin" style="display:none">
                @php $mCount = 0; @endphp
                @foreach($checklists as $c)
                    @foreach(['foto_1','foto_2','foto_3'] as $f)
                        @if($c->mesin?->$f) @php $mCount++; @endphp @endif
                    @endforeach
                @endforeach
                <p style="font-size:0.78rem;color:#64748b;margin-bottom:10px;font-weight:600">{{ $mCount }} foto mesin ditemukan</p>
                <div class="photo-log-grid">
                    @foreach($checklists as $c)
                        @foreach(['foto_1','foto_2','foto_3'] as $idx => $f)
                            @if($c->mesin?->$f)
                            <div class="photo-log-item">
                                <img src="{{ Storage::disk('public')->url($c->mesin->$f) }}" alt="Mesin" loading="lazy">
                                <span class="photo-log-badge">{{ $c->nomor_kendaraan }} · Mesin {{ $idx+1 }}</span>
                            </div>
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </div>

            {{-- BBM photos --}}
            <div data-tab-panel="bbm" style="display:none">
                @php $bCount = $checklists->filter(fn($c) => $c->foto_bbm_dashboard)->count(); @endphp
                <p style="font-size:0.78rem;color:#64748b;margin-bottom:10px;font-weight:600">{{ $bCount }} foto BBM ditemukan</p>
                <div class="photo-log-grid">
                    @foreach($checklists as $c)
                        @if($c->foto_bbm_dashboard)
                        <div class="photo-log-item">
                            <img src="{{ Storage::disk('public')->url($c->foto_bbm_dashboard) }}" alt="BBM" loading="lazy">
                            <span class="photo-log-badge">{{ $c->nomor_kendaraan }} · BBM</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</body>
</html>
