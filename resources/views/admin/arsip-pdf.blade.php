<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Arsip PDF - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dash-body">
    <div class="admin-shell">
        <header class="checklist-topbar">
            <div><h1 class="dash-brand-title">Arsip PDF</h1><p class="dash-brand-sub">PT ARTHA DAYA COALINDO</p></div>
            <div class="flex items-center gap-2">
                <span class="dash-chip dash-chip-admin">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
                    ADMIN
                </span>
                <a href="{{ route('dashboard') }}" class="checklist-icon-btn"><svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            </div>
        </header>
        <div class="admin-card">
            @php
                $baseUrl = url('/');
                $resolvePdfUrl = function (?string $path) use ($baseUrl) {
                    if (!$path) return null;
                    if (str_starts_with($path, 'http')) return $path;
                    if (str_starts_with($path, '/storage/')) return $baseUrl . $path;
                    if (str_starts_with($path, 'storage/')) return $baseUrl . '/' . $path;
                    return $baseUrl . '/storage/' . ltrim($path, '/');
                };
            @endphp

            <div class="stat-grid">
                <div class="stat-card"><div class="stat-value">{{ $stats['total'] }}</div><div class="stat-label">Total Laporan</div></div>
                <div class="stat-card"><div class="stat-value">{{ $stats['bulan_ini'] }}</div><div class="stat-label">Bulan Ini</div></div>
            </div>

            {{-- Search & Filter --}}
            <x-admin-toolbar route="admin.arsip-pdf" :nopolList="$nopolList" />

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr><th>#</th><th>Tanggal</th><th>Nopol</th><th>Driver Serah</th><th>Driver Terima</th><th>Shift</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse($checklists as $c)
                        <tr>
                            <td>{{ ($checklists->currentPage() - 1) * $checklists->perPage() + $loop->iteration }}</td>
                            <td>{{ $c->tanggal->format('d/m/Y') }}</td>
                            <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                            <td>{{ $c->driver_serah }}</td>
                            <td>{{ $c->driver_terima }}</td>
                            <td>{{ $c->shift }}</td>
                            <td>
                                @if($c->pdf_path)
                                    <a href="{{ $resolvePdfUrl($c->pdf_path) }}" target="_blank" class="btn-view-pdf">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/></svg>
                                        View PDF
                                    </a>
                                @else
                                    <span style="color:#94a3b8;font-size:0.75rem">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:24px">Belum ada laporan PDF.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination">{{ $checklists->links() }}</div>
        </div>
    </div>
</body>
</html>
