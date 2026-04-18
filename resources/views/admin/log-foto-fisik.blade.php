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
            @php
                $baseUrl = url('/');
                $resolvePhotoUrl = function (?string $path) use ($baseUrl) {
                    if (!$path) return null;
                    if (str_starts_with($path, 'http')) return $path;
                    if (str_starts_with($path, '/storage/')) return $baseUrl . $path;
                    if (str_starts_with($path, 'storage/')) return $baseUrl . '/' . $path;
                    return $baseUrl . '/storage/' . ltrim($path, '/');
                };
            @endphp

            {{-- Search & Filter --}}
            <x-admin-toolbar route="admin.log-foto-fisik" :nopolList="$nopolList" />

            <div class="admin-tabs">
                <button class="admin-tab active" data-tab-btn="exterior">Eksterior</button>
                <button class="admin-tab" data-tab-btn="interior">Interior</button>
                <button class="admin-tab" data-tab-btn="mesin">Mesin</button>
                <button class="admin-tab" data-tab-btn="bbm">BBM</button>
            </div>

            {{-- Exterior photos --}}
            <div data-tab-panel="exterior">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Waktu</th><th>Nomor Unit</th><th>Foto</th></tr></thead>
                        <tbody>
                            @php $hasExterior = false; @endphp
                            @foreach($checklists as $c)
                                @if($c->exterior && ($c->exterior->foto_depan || $c->exterior->foto_kanan || $c->exterior->foto_kiri || $c->exterior->foto_belakang))
                                @php $hasExterior = true; @endphp
                                <tr>
                                    <td>{{ $c->tanggal?->format('d/m/Y') }} {{ $c->jam_serah_terima ?? '' }}</td>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                                    <td>
                                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                                            @foreach(['foto_depan' => 'Depan', 'foto_kanan' => 'Kanan', 'foto_kiri' => 'Kiri', 'foto_belakang' => 'Belakang'] as $field => $label)
                                                @if($c->exterior->$field)
                                                    <a href="{{ $resolvePhotoUrl($c->exterior->$field) }}" target="_blank" rel="noopener" title="{{ $label }}">
                                                        <img src="{{ $resolvePhotoUrl($c->exterior->$field) }}" alt="{{ $label }}" loading="lazy" style="width:110px;height:82px;object-fit:cover;border-radius:6px;display:block;">
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                            @unless($hasExterior)
                                <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:24px">Belum ada foto eksterior.</td></tr>
                            @endunless
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Interior photos --}}
            <div data-tab-panel="interior" style="display:none">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Waktu</th><th>Nomor Unit</th><th>Foto</th></tr></thead>
                        <tbody>
                            @php $hasInterior = false; @endphp
                            @foreach($checklists as $c)
                                @if($c->interior && ($c->interior->foto_1 || $c->interior->foto_2 || $c->interior->foto_3))
                                @php $hasInterior = true; @endphp
                                <tr>
                                    <td>{{ $c->tanggal?->format('d/m/Y') }} {{ $c->jam_serah_terima ?? '' }}</td>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                                    <td>
                                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                                            @for($i = 1; $i <= 3; $i++)
                                                @php $f = "foto_{$i}"; @endphp
                                                @if($c->interior->$f)
                                                    <a href="{{ $resolvePhotoUrl($c->interior->$f) }}" target="_blank" rel="noopener" title="Interior {{ $i }}">
                                                        <img src="{{ $resolvePhotoUrl($c->interior->$f) }}" alt="Interior {{ $i }}" loading="lazy" style="width:110px;height:82px;object-fit:cover;border-radius:6px;display:block;">
                                                    </a>
                                                @endif
                                            @endfor
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                            @unless($hasInterior)
                                <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:24px">Belum ada foto interior.</td></tr>
                            @endunless
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mesin photos --}}
            <div data-tab-panel="mesin" style="display:none">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Waktu</th><th>Nomor Unit</th><th>Foto</th></tr></thead>
                        <tbody>
                            @php $hasMesin = false; @endphp
                            @foreach($checklists as $c)
                                @if($c->mesin && ($c->mesin->foto_1 || $c->mesin->foto_2 || $c->mesin->foto_3))
                                @php $hasMesin = true; @endphp
                                <tr>
                                    <td>{{ $c->tanggal?->format('d/m/Y') }} {{ $c->jam_serah_terima ?? '' }}</td>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                                    <td>
                                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                                            @for($i = 1; $i <= 3; $i++)
                                                @php $f = "foto_{$i}"; @endphp
                                                @if($c->mesin->$f)
                                                    <a href="{{ $resolvePhotoUrl($c->mesin->$f) }}" target="_blank" rel="noopener" title="Mesin {{ $i }}">
                                                        <img src="{{ $resolvePhotoUrl($c->mesin->$f) }}" alt="Mesin {{ $i }}" loading="lazy" style="width:110px;height:82px;object-fit:cover;border-radius:6px;display:block;">
                                                    </a>
                                                @endif
                                            @endfor
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                            @unless($hasMesin)
                                <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:24px">Belum ada foto mesin.</td></tr>
                            @endunless
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- BBM photos --}}
            <div data-tab-panel="bbm" style="display:none">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Waktu</th><th>Nomor Unit</th><th>Foto</th></tr></thead>
                        <tbody>
                            @php $hasBbm = false; @endphp
                            @foreach($checklists as $c)
                                @if($c->foto_bbm_dashboard)
                                @php $hasBbm = true; @endphp
                                <tr>
                                    <td>{{ $c->tanggal?->format('d/m/Y') }} {{ $c->jam_serah_terima ?? '' }}</td>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                                    <td>
                                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                                            <a href="{{ $resolvePhotoUrl($c->foto_bbm_dashboard) }}" target="_blank" rel="noopener" title="BBM">
                                                <img src="{{ $resolvePhotoUrl($c->foto_bbm_dashboard) }}" alt="BBM" loading="lazy" style="width:110px;height:82px;object-fit:cover;border-radius:6px;display:block;">
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                            @unless($hasBbm)
                                <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:24px">Belum ada foto BBM.</td></tr>
                            @endunless
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="admin-pagination">{{ $checklists->links() }}</div>
        </div>
    </div>
</body>
</html>
