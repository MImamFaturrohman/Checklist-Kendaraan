<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Sheet - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dash-body">
    <div class="admin-shell">
        <header class="checklist-topbar">
            <div><h1 class="dash-brand-title">Database Sheet</h1><p class="dash-brand-sub">PT ARTHA DAYA COALINDO</p></div>
            <div class="flex items-center gap-2">
                <span class="dash-chip dash-chip-admin">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
                    ADMIN
                </span>
                <a href="{{ route('dashboard') }}" class="checklist-icon-btn"><svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            </div>
        </header>
        <div class="admin-card">
            @if (session('success'))
                <div class="admin-alert admin-alert-success">
                    {{ session('success') }}
                    @if (session('sheet_url'))
                        <a href="{{ session('sheet_url') }}" target="_blank" rel="noopener" style="margin-left:8px;color:#166534;text-decoration:underline;font-weight:700">Buka Spreadsheet</a>
                    @endif
                </div>
            @endif
            @if (session('error'))
                <div class="admin-alert" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Stats --}}
            <div class="stat-grid">
                <div class="stat-card"><div class="stat-value">{{ $stats['total'] }}</div><div class="stat-label">Total Ceklist</div></div>
                <div class="stat-card"><div class="stat-value">{{ $stats['kendaraan_unik'] }}</div><div class="stat-label">Kendaraan Unik</div></div>
                <div class="stat-card"><div class="stat-value">{{ $stats['driver_aktif'] }}</div><div class="stat-label">Driver Aktif</div></div>
                <div class="stat-card"><div class="stat-value">{{ $stats['bulan_ini'] }}</div><div class="stat-label">Bulan Ini</div></div>
            </div>

            {{-- Export --}}
            <div style="margin-bottom:16px">
                <a href="{{ route('admin.database-sheet.export') }}" class="btn-export">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="currentColor" stroke-width="2"/><polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="2"/></svg>
                    Sinkronkan ke Spreadsheet
                </a>
            </div>

            {{-- Search & Filter --}}
            <x-admin-toolbar route="admin.database-sheet" :nopolList="$nopolList" :showShift="true" />

            {{-- Tabs --}}
            <div data-tab-group>
                <div class="admin-tabs">
                    <button class="admin-tab active" data-tab-btn="all">Semua Data</button>
                    <button class="admin-tab" data-tab-btn="exterior">Exterior</button>
                    <button class="admin-tab" data-tab-btn="interior">Interior</button>
                    <button class="admin-tab" data-tab-btn="mesin">Mesin</button>
                    <button class="admin-tab" data-tab-btn="perlengkapan">Perlengkapan</button>
                </div>

                {{-- ALL --}}
                <div data-tab-panel="all">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead><tr><th>#</th><th>Tanggal</th><th>Shift</th><th>Nopol</th><th>Jenis</th><th>Driver Serah</th><th>Driver Terima</th><th>BBM</th><th>KM Awal</th><th>KM Akhir</th></tr></thead>
                            <tbody>
                                @forelse($checklists as $c)
                                <tr>
                                    <td>{{ ($checklists->currentPage() - 1) * $checklists->perPage() + $loop->iteration }}</td>
                                    <td>{{ $c->tanggal->format('d/m/Y') }}</td><td>{{ $c->shift }}</td>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td><td>{{ $c->jenis_kendaraan }}</td>
                                    <td>{{ $c->driver_serah }}</td><td>{{ $c->driver_terima }}</td>
                                    <td>{{ $c->level_bbm }}%</td><td>{{ number_format($c->km_awal) }}</td><td>{{ number_format($c->km_akhir ?? 0) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="10" style="text-align:center;color:#94a3b8;padding:24px">Belum ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="admin-pagination">{{ $checklists->links() }}</div>
                </div>

                @php
                    $statusClass = fn($value) => $value === 'ok' ? 'status-ok' : (in_array($value, ['no', 'tidak_ok'], true) ? 'status-nok' : '');
                    $statusLabel = fn($value) => $value === 'ok' ? 'OK' : (in_array($value, ['no', 'tidak_ok'], true) ? 'NO' : strtoupper($value ?? '-'));
                @endphp

                {{-- EXTERIOR --}}
                <div data-tab-panel="exterior" style="display:none">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead><tr><th>Nopol</th><th>Tanggal</th><th>Body</th><th>Kaca</th><th>Spion</th><th>Lampu Utama</th><th>Lampu Sein</th><th>Ban</th><th>Velg</th><th>Wiper</th></tr></thead>
                            <tbody>
                                @foreach($checklists as $c)
                                @if($c->exterior)
                                <tr>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td><td>{{ $c->tanggal->format('d/m/Y') }}</td>
                                    @foreach(['body_kendaraan','kaca','spion','lampu_utama','lampu_sein','ban','velg','wiper'] as $k)
                                    <td class="{{ $statusClass($c->exterior->$k) }}" style="font-weight:700;font-size:0.75rem;color:{{ $statusClass($c->exterior->$k) === 'status-ok' ? '#16a34a' : ($statusClass($c->exterior->$k) === 'status-nok' ? '#dc2626' : '#334155') }}">{{ $statusLabel($c->exterior->$k) }}</td>
                                    @endforeach
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- INTERIOR --}}
                <div data-tab-panel="interior" style="display:none">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead><tr><th>Nopol</th><th>Tanggal</th><th>Jok</th><th>Dashboard</th><th>AC</th><th>Sabuk</th><th>Audio</th><th>Kebersihan</th></tr></thead>
                            <tbody>
                                @foreach($checklists as $c)
                                @if($c->interior)
                                <tr>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td><td>{{ $c->tanggal->format('d/m/Y') }}</td>
                                    @foreach(['jok','dashboard','ac','sabuk_pengaman','audio','kebersihan'] as $k)
                                    <td class="{{ $statusClass($c->interior->$k) }}" style="font-weight:700;font-size:0.75rem;color:{{ $statusClass($c->interior->$k) === 'status-ok' ? '#16a34a' : ($statusClass($c->interior->$k) === 'status-nok' ? '#dc2626' : '#334155') }}">{{ $statusLabel($c->interior->$k) }}</td>
                                    @endforeach
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- MESIN --}}
                <div data-tab-panel="mesin" style="display:none">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead><tr><th>Nopol</th><th>Tanggal</th><th>Mesin</th><th>Oli</th><th>Radiator</th><th>Rem</th><th>Kopling</th><th>Transmisi</th><th>Indikator</th></tr></thead>
                            <tbody>
                                @foreach($checklists as $c)
                                @if($c->mesin)
                                <tr>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td><td>{{ $c->tanggal->format('d/m/Y') }}</td>
                                    @foreach(['mesin','oli','radiator','rem','kopling','transmisi','indikator'] as $k)
                                    <td class="{{ $statusClass($c->mesin->$k) }}" style="font-weight:700;font-size:0.75rem;color:{{ $statusClass($c->mesin->$k) === 'status-ok' ? '#16a34a' : ($statusClass($c->mesin->$k) === 'status-nok' ? '#dc2626' : '#334155') }}">{{ $statusLabel($c->mesin->$k) }}</td>
                                    @endforeach
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- PERLENGKAPAN --}}
                <div data-tab-panel="perlengkapan" style="display:none">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead><tr><th>Nopol</th><th>Tanggal</th><th>STNK</th><th>KIR & QR BBM</th><th>Dongkrak</th><th>Toolkit</th><th>Segitiga</th><th>APAR</th><th>Ban Cad.</th></tr></thead>
                            <tbody>
                                @foreach($checklists as $c)
                                @if($c->perlengkapan)
                                <tr>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td><td>{{ $c->tanggal->format('d/m/Y') }}</td>
                                    @foreach(['stnk','kir','dongkrak','toolkit','segitiga','apar','ban_cadangan'] as $k)
                                    <td style="font-weight:700;font-size:0.75rem;color:{{ $c->perlengkapan->$k === 'ada' ? '#16a34a' : '#dc2626' }}">{{ strtoupper($c->perlengkapan->$k ?? '-') }}</td>
                                    @endforeach
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
