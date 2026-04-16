<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Arsip PDF - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dash-body">
    <div class="admin-page-shell">
        <header class="checklist-topbar">
            <div>
                <h1 class="dash-brand-title">Arsip PDF</h1>
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

        <div class="admin-card fade-in-up">
            <h2 class="admin-card-title">History Laporan PDF</h2>
            <p class="admin-card-desc">Daftar seluruh laporan ceklist yang telah di-generate menjadi PDF.</p>

            @if($checklists->isEmpty())
                <div class="admin-empty">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none"><path d="M7 3H14L19 8V21H7V3Z" stroke="#94a3b8" stroke-width="2" stroke-linejoin="round"/><path d="M14 3V8H19" stroke="#94a3b8" stroke-width="2"/></svg>
                    <p>Belum ada laporan PDF yang tersedia.</p>
                </div>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Nopol</th>
                                <th>Driver Serah</th>
                                <th>Driver Terima</th>
                                <th>Shift</th>
                                <th style="text-align:center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($checklists as $i => $c)
                            <tr class="fade-in-up" style="animation-delay:{{ $i * 30 }}ms">
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $c->tanggal->format('d/m/Y') }}</td>
                                <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                                <td>{{ $c->driver_serah }}</td>
                                <td>{{ $c->driver_terima }}</td>
                                <td>{{ $c->shift }}</td>
                                <td style="text-align:center">
                                    <a href="{{ asset('storage/' . $c->pdf_path) }}" target="_blank" class="admin-btn admin-btn-view">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                                        View PDF
                                    </a>
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
