<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Sheet - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dash-body">
    <div class="admin-page-shell">
        <header class="checklist-topbar">
            <div>
                <h1 class="dash-brand-title">Database Sheet</h1>
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

        {{-- Statistics --}}
        <div class="stats-grid fade-in-up">
            <div class="stat-card stat-card-blue">
                <span class="stat-number">{{ $totalChecklists }}</span>
                <span class="stat-label">Total Ceklist</span>
            </div>
            <div class="stat-card stat-card-green">
                <span class="stat-number">{{ $thisMonth }}</span>
                <span class="stat-label">Bulan Ini</span>
            </div>
            <div class="stat-card stat-card-yellow">
                <span class="stat-number">{{ $totalVehicles }}</span>
                <span class="stat-label">Kendaraan</span>
            </div>
            <div class="stat-card stat-card-purple">
                <span class="stat-number">{{ $totalDrivers }}</span>
                <span class="stat-label">Driver</span>
            </div>
        </div>

        {{-- Export Button --}}
        <div class="admin-card fade-in-up" style="margin-top:14px">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                <h2 class="admin-card-title" style="margin:0">Data Ceklist Kendaraan</h2>
                <a href="{{ route('admin.database-sheet.export') }}" class="admin-btn admin-btn-export">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Export ke Excel
                </a>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="admin-card fade-in-up" style="margin-top:10px">
            @if($checklists->isEmpty())
                <div class="admin-empty"><p>Belum ada data ceklist.</p></div>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table admin-table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Shift</th>
                                <th>Nopol</th>
                                <th>Jenis</th>
                                <th>Driver Serah</th>
                                <th>Driver Terima</th>
                                <th>BBM</th>
                                <th>KM Awal</th>
                                <th>KM Akhir</th>
                                <th>Ext</th>
                                <th>Int</th>
                                <th>Mesin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($checklists as $i => $c)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $c->tanggal->format('d/m/Y') }}</td>
                                <td>{{ $c->shift }}</td>
                                <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                                <td>{{ $c->jenis_kendaraan }}</td>
                                <td>{{ $c->driver_serah }}</td>
                                <td>{{ $c->driver_terima }}</td>
                                <td>{{ $c->level_bbm }}%</td>
                                <td>{{ number_format($c->km_awal) }}</td>
                                <td>{{ number_format($c->km_akhir ?? 0) }}</td>
                                <td>
                                    @php
                                        $extOk = 0; $extTotal = 0;
                                        foreach(['body_kendaraan','kaca','spion','lampu_utama','lampu_sein','ban','velg','wiper'] as $item) {
                                            if($c->exterior?->$item) { $extTotal++; if($c->exterior->$item === 'ok') $extOk++; }
                                        }
                                    @endphp
                                    <span class="{{ $extOk === $extTotal && $extTotal > 0 ? 'text-green-600' : 'text-red-500' }} font-bold">{{ $extOk }}/{{ $extTotal }}</span>
                                </td>
                                <td>
                                    @php
                                        $intOk = 0; $intTotal = 0;
                                        foreach(['jok','dashboard','ac','sabuk_pengaman','audio','kebersihan'] as $item) {
                                            if($c->interior?->$item) { $intTotal++; if($c->interior->$item === 'ok') $intOk++; }
                                        }
                                    @endphp
                                    <span class="{{ $intOk === $intTotal && $intTotal > 0 ? 'text-green-600' : 'text-red-500' }} font-bold">{{ $intOk }}/{{ $intTotal }}</span>
                                </td>
                                <td>
                                    @php
                                        $mOk = 0; $mTotal = 0;
                                        foreach(['mesin','oli','radiator','rem','kopling','transmisi','indikator'] as $item) {
                                            if($c->mesin?->$item) { $mTotal++; if($c->mesin->$item === 'ok') $mOk++; }
                                        }
                                    @endphp
                                    <span class="{{ $mOk === $mTotal && $mTotal > 0 ? 'text-green-600' : 'text-red-500' }} font-bold">{{ $mOk }}/{{ $mTotal }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
