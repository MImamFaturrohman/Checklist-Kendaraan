<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Master Armada - {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dash-body">
        <div class="armada-shell">
            <header class="checklist-topbar" style="margin-bottom:6px">
                <div>
                    <h1 class="dash-brand-title">Master Armada</h1>
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

            <div class="armada-card">
                @if(session('success'))
                    <div class="armada-alert armada-alert-success">
                        ✅ {{ session('success') }}
                    </div>
                @endif

                {{-- Add Form --}}
                <form action="{{ route('admin.master-armada.store') }}" method="POST">
                    @csrf
                    <div class="armada-form-row">
                        <div>
                            <label style="font-size:0.72rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px">Nomor Kendaraan</label>
                            <input type="text" name="nomor_kendaraan" placeholder="B 1234 ABC" required value="{{ old('nomor_kendaraan') }}">
                            @error('nomor_kendaraan')
                                <small style="color:#dc2626">{{ $message }}</small>
                            @enderror
                        </div>
                        <div>
                            <label style="font-size:0.72rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px">Jenis Kendaraan</label>
                            <input type="text" name="jenis_kendaraan" placeholder="MITSUBISHI XPANDER" required value="{{ old('jenis_kendaraan') }}">
                            @error('jenis_kendaraan')
                                <small style="color:#dc2626">{{ $message }}</small>
                            @enderror
                        </div>
                        <button type="submit" class="armada-btn armada-btn-add">+ Tambah</button>
                    </div>
                </form>

                {{-- Search --}}
                <form method="GET" action="{{ route('admin.master-armada') }}" class="admin-toolbar" style="margin-top:16px">
                    <div class="admin-search-wrap">
                        <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor atau jenis kendaraan..." class="admin-search-input">
                        @if(request('search'))
                            <a href="{{ route('admin.master-armada') }}" class="admin-search-clear" title="Hapus pencarian">&times;</a>
                        @endif
                    </div>
                    <div class="admin-filter-row">
                        <button type="submit" class="admin-filter-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Cari
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.master-armada') }}" class="admin-filter-reset">Reset</a>
                        @endif
                    </div>
                </form>

                {{-- Table --}}
                <table class="armada-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nomor Kendaraan</th>
                            <th>Jenis Kendaraan</th>
                            <th style="text-align:center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kendaraans as $k)
                            <tr id="row-{{ $k->id }}">
                                <td>{{ ($kendaraans->currentPage() - 1) * $kendaraans->perPage() + $loop->iteration }}</td>
                                <td>
                                    <span class="view-mode">{{ $k->nomor_kendaraan }}</span>
                                    <input class="edit-mode" type="text" value="{{ $k->nomor_kendaraan }}" name="nomor_kendaraan" form="edit-form-{{ $k->id }}" style="display:none;width:100%;border:1px solid #d1d5db;border-radius:8px;padding:6px 8px;font-size:0.85rem">
                                </td>
                                <td>
                                    <span class="view-mode">{{ $k->jenis_kendaraan }}</span>
                                    <input class="edit-mode" type="text" value="{{ $k->jenis_kendaraan }}" name="jenis_kendaraan" form="edit-form-{{ $k->id }}" style="display:none;width:100%;border:1px solid #d1d5db;border-radius:8px;padding:6px 8px;font-size:0.85rem">
                                </td>
                                <td style="text-align:center">
                                    <form id="edit-form-{{ $k->id }}" action="{{ route('admin.master-armada.update', $k) }}" method="POST" style="display:inline">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                    <button type="button" class="armada-btn armada-btn-edit view-mode" onclick="toggleEdit({{ $k->id }})">Edit</button>
                                    <button type="submit" form="edit-form-{{ $k->id }}" class="armada-btn armada-btn-edit edit-mode" style="display:none;background:#dcfce7;color:#15803d">Simpan</button>
                                    <form action="{{ route('admin.master-armada.destroy', $k) }}" method="POST" style="display:inline" onsubmit="return confirm('Hapus kendaraan {{ $k->nomor_kendaraan }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="armada-btn armada-btn-delete">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center;color:#9ca3af;padding:24px">Belum ada data kendaraan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="admin-pagination">{{ $kendaraans->links() }}</div>
            </div>
        </div>

        <script>
            function toggleEdit(id) {
                const row = document.getElementById('row-' + id);
                row.querySelectorAll('.view-mode').forEach(el => el.style.display = 'none');
                row.querySelectorAll('.edit-mode').forEach(el => el.style.display = '');
            }
        </script>
    </body>
</html>
