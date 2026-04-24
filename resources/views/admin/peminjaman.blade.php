<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Request Peminjaman - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dash-body">
    <div class="admin-shell">

        <header class="checklist-topbar" style="margin-bottom:6px">
            <div>
                <h1 class="dash-brand-title">Request Peminjaman Kendaraan</h1>
                <p class="dash-brand-sub">PT ARTHA DAYA COALINDO</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="dash-chip">ADMIN</span>
                <a href="{{ route('dashboard') }}" class="checklist-icon-btn" aria-label="Kembali ke dashboard">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none">
                        <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </header>

        {{-- STATS --}}
        <div class="stat-grid" style="margin-top:16px">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Request</div>
            </div>
            <div class="stat-card" style="background:linear-gradient(135deg,#fefce8,#fef9c3);border-color:#fde68a">
                <div class="stat-value" style="color:#a16207">{{ $stats['pending'] }}</div>
                <div class="stat-label">Menunggu</div>
            </div>
            <div class="stat-card" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border-color:#86efac">
                <div class="stat-value" style="color:#15803d">{{ $stats['approved'] }}</div>
                <div class="stat-label">Disetujui</div>
            </div>
            <div class="stat-card" style="background:linear-gradient(135deg,#fef2f2,#fee2e2);border-color:#fca5a5">
                <div class="stat-value" style="color:#b91c1c">{{ $stats['rejected'] }}</div>
                <div class="stat-label">Ditolak</div>
            </div>
        </div>

        {{-- FILTERS --}}
        <form method="GET" action="{{ route('admin.peminjaman') }}" class="admin-toolbar" style="margin-top:16px">
            <div class="admin-search-wrap">
                <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                    <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama, NIP, jabatan, atau kendaraan..."
                    class="admin-search-input">
                @if(request('search'))
                    <a href="{{ route('admin.peminjaman', ['status' => request('status')]) }}"
                        class="admin-search-clear" title="Hapus pencarian">&times;</a>
                @endif
            </div>
            <div class="admin-filter-row">
                <div class="admin-filter-item">
                    <label>Status</label>
                    <select name="status" class="admin-filter-input">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <button type="submit" class="admin-filter-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Cari
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.peminjaman') }}" class="admin-filter-reset">Reset</a>
                @endif
            </div>
        </form>

        {{-- TABLE --}}
        <div class="admin-card" style="margin-top:0">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pemohon</th>
                            <th>Kendaraan</th>
                            <th>Keperluan</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Diajukan</th>
                            <th>Diproses</th>
                            <th>PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td>{{ ($requests->currentPage() - 1) * $requests->perPage() + $loop->iteration }}</td>
                                <td>
                                    <div style="font-weight:700;color:#0f172a">{{ $req->nama_lengkap }}</div>
                                    <div style="font-size:0.76rem;color:#64748b">{{ $req->nip }}</div>
                                    <div style="font-size:0.76rem;color:#94a3b8">{{ $req->jabatan }}</div>
                                    @if($req->tanggal_peminjaman)
                                        <div style="font-size:0.72rem;color:#94a3b8">{{ \Carbon\Carbon::parse($req->tanggal_peminjaman)->translatedFormat('d M Y') }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="landing-nopol-badge">{{ $req->nomor_kendaraan }}</span>
                                    <div style="font-size:0.76rem;color:#64748b;margin-top:3px">{{ $req->jenis_kendaraan }}</div>
                                </td>
                                <td style="max-width:200px">
                                    <div style="font-size:0.82rem;color:#374151;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px" title="{{ $req->alasan }}">
                                        {{ $req->alasan }}
                                    </div>
                                </td>
                                <td>
                                    @if($req->isPending())
                                        <span class="status-badge status-pending">
                                            <svg width="8" height="8" viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg>
                                            Menunggu
                                        </span>
                                    @elseif($req->isApproved())
                                        <span class="status-badge status-approved">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            Disetujui
                                        </span>
                                    @else
                                        <span class="status-badge status-rejected">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td style="font-size:0.82rem;color:#64748b;max-width:160px">
                                    {{ $req->catatan_manager ?: '-' }}
                                </td>
                                <td style="font-size:0.78rem;color:#94a3b8;white-space:nowrap">
                                    {{ $req->created_at->format('d M Y') }}<br>
                                    {{ $req->created_at->format('H:i') }}
                                </td>
                                <td style="font-size:0.78rem;color:#94a3b8;white-space:nowrap">
                                    @if($req->approved_at)
                                        {{ $req->approved_at->format('d M Y') }}<br>
                                        <span style="color:#94a3b8">{{ $req->approver?->name ?? '-' }}</span>
                                    @else
                                        <span style="color:#d1d5db">—</span>
                                    @endif
                                </td>
                                <td style="white-space:nowrap">
                                    @if($req->isApproved())
                                        <a href="{{ route('admin.peminjaman.pdf', $req) }}"
                                           target="_blank"
                                           style="display:inline-flex;align-items:center;gap:5px;padding:5px 10px;background:#002a7a;color:#fff;border-radius:8px;font-size:0.75rem;font-weight:700;text-decoration:none;transition:background .15s"
                                           onmouseover="this.style.background='#0038a8'" onmouseout="this.style.background='#002a7a'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                                <path d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a1 1 0 001 1h16a1 1 0 001-1v-3M3 12l9-9 9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            {{ $req->pdf_path ? 'Unduh PDF' : 'Cetak PDF' }}
                                        </a>
                                    @else
                                        <span style="color:#d1d5db;font-size:0.76rem">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align:center;color:#9ca3af;padding:40px 12px">
                                    Tidak ada data request peminjaman.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination mt-4">{{ $requests->links() }}</div>
        </div>

    </div>
</body>
</html>
