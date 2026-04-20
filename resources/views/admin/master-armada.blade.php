<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Master Armada - {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            .armada-form-row { align-items: flex-end; background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; }
            .armada-form-row input { background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 0.85rem; width: 100%; transition: all 0.2s; }
            .armada-form-row input:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
            .armada-btn-add { background: #2563eb; color: #fff; padding: 8px 16px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; transition: background 0.2s; white-space: nowrap; height: 38px; display: flex; align-items: center; justify-content: center; }
            .armada-btn-add:hover { background: #1d4ed8; }
            .armada-btn-add.is-loading { opacity: 0.7; pointer-events: none; }
        </style>
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
                {{-- Add Form --}}
                <form id="form-add-armada" action="{{ route('admin.master-armada.store') }}" method="POST">
                    @csrf
                    <div class="armada-form-row flex flex-wrap gap-4">
                        <div style="flex: 1; min-width: 200px;">
                            <label style="display:block;margin-bottom:6px;font-size:0.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px">Nomor Kendaraan</label>
                            <input type="text" name="nomor_kendaraan" placeholder="B 1234 ABC" required>
                        </div>
                        <div style="flex: 1; min-width: 200px;">
                            <label style="display:block;margin-bottom:6px;font-size:0.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px">Jenis Kendaraan</label>
                            <input type="text" name="jenis_kendaraan" placeholder="MITSUBISHI XPANDER" required>
                        </div>
                        <div style="width: 140px;">
                            <label style="display:block;margin-bottom:6px;font-size:0.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px">Set KM</label>
                            <input type="number" name="set_km" placeholder="50000" min="0">
                        </div>
                        <div style="padding-bottom: 2px;">
                            <button type="submit" class="armada-btn armada-btn-add" id="btn-add">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right:6px">
                                    <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Tambah
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Search --}}
                <form method="GET" action="{{ route('admin.master-armada') }}" class="admin-toolbar" style="margin-top:20px" data-admin-toolbar>
                    <div class="admin-search-wrap">
                        <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor atau jenis kendaraan..." class="admin-search-input" data-admin-search>
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

                <div id="table-container">
                    {{-- Table --}}
                    <table class="armada-table mt-4 w-full">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nomor Kendaraan</th>
                                <th>Jenis Kendaraan</th>
                                <th>Set KM</th>
                                <th style="text-align:center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kendaraans as $k)
                                <tr id="row-{{ $k->id }}">
                                    <td>{{ ($kendaraans->currentPage() - 1) * $kendaraans->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <span class="view-mode">{{ $k->nomor_kendaraan }}</span>
                                        <input class="edit-mode" type="text" value="{{ $k->nomor_kendaraan }}" name="nomor_kendaraan" form="edit-form-{{ $k->id }}" style="display:none;width:100%;border:1px solid #d1d5db;border-radius:6px;padding:6px 8px;font-size:0.85rem">
                                    </td>
                                    <td>
                                        <span class="view-mode">{{ $k->jenis_kendaraan }}</span>
                                        <input class="edit-mode" type="text" value="{{ $k->jenis_kendaraan }}" name="jenis_kendaraan" form="edit-form-{{ $k->id }}" style="display:none;width:100%;border:1px solid #d1d5db;border-radius:6px;padding:6px 8px;font-size:0.85rem">
                                    </td>
                                    <td>
                                        <span class="view-mode">{{ number_format($k->set_km ?? 0, 0, ',', '.') }}</span>
                                        <input class="edit-mode" type="number" value="{{ $k->set_km ?? 0 }}" name="set_km" min="0" form="edit-form-{{ $k->id }}" style="display:none;width:100px;border:1px solid #d1d5db;border-radius:6px;padding:6px 8px;font-size:0.85rem">
                                    </td>
                                    <td style="text-align:center">
                                        <form id="edit-form-{{ $k->id }}" action="{{ route('admin.master-armada.update', $k) }}" method="POST" style="display:none" onsubmit="event.preventDefault(); submitEdit({{ $k->id }});">
                                            @csrf
                                            @method('PUT')
                                        </form>
                                        <button type="button" class="armada-btn armada-btn-edit view-mode" onclick="toggleEdit({{ $k->id }})">Edit</button>
                                        <button type="submit" form="edit-form-{{ $k->id }}" class="armada-btn armada-btn-edit edit-mode" style="display:none;background:#22c55e;color:#fff">Simpan</button>
                                        <button type="button" class="armada-btn armada-btn-edit edit-mode" onclick="toggleEdit({{ $k->id }})" style="display:none;background:#64748b;color:#fff">Batal</button>
                                        <form id="delete-form-{{ $k->id }}" action="{{ route('admin.master-armada.destroy', $k) }}" method="POST" style="display:inline" onsubmit="event.preventDefault(); submitDelete({{ $k->id }}, '{{ $k->nomor_kendaraan }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="armada-btn armada-btn-delete view-mode">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center;color:#9ca3af;padding:32px">Belum ada data kendaraan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="admin-pagination mt-4">{{ $kendaraans->links() }}</div>
                </div>
            </div>
        </div>

        <script>
            function toggleEdit(id) {
                const row = document.getElementById('row-' + id);
                const isEdit = row.querySelector('.edit-mode').style.display === 'inline-block' || row.querySelector('.edit-mode').style.display === 'block' || row.querySelector('.edit-mode').style.display === '';
                row.querySelectorAll('.view-mode').forEach(el => el.style.display = isEdit ? '' : 'none');
                row.querySelectorAll('.edit-mode').forEach(el => el.style.display = isEdit ? 'none' : 'inline-block');
                
                // Fix input display to block/inline as appropriate
                row.querySelectorAll('input.edit-mode').forEach(el => {
                    if(!isEdit) el.style.display = 'block';
                });
            }

            async function refreshTable() {
                try {
                    const res = await fetch(window.location.href);
                    const html = await res.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    document.getElementById('table-container').innerHTML = doc.getElementById('table-container').innerHTML;
                } catch(err) {
                    console.error("Failed to refresh table", err);
                }
            }

            document.getElementById('form-add-armada').addEventListener('submit', async function(e) {
                e.preventDefault();
                const btn = document.getElementById('btn-add');
                btn.classList.add('is-loading');
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="margin-right:6px"></span> Menyimpan...';
                
                const formData = new FormData(this);
                formData.append('_accept', 'application/json');

                try {
                    const res = await fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await res.json();

                    if(res.ok && data.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, showConfirmButton: false, timer: 1500 });
                        this.reset();
                        await refreshTable();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Terjadi kesalahan sistem.' });
                    }
                } catch (err) {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Koneksi bermasalah atau terjadi kesalahan.' });
                } finally {
                    btn.classList.remove('is-loading');
                    btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right:6px"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Tambah';
                }
            });

            async function submitEdit(id) {
                const form = document.getElementById('edit-form-' + id);
                const formData = new FormData(form);
                
                const tr = document.getElementById('row-' + id);
                const nopol = tr.querySelector('input[name="nomor_kendaraan"]').value;
                const jenis = tr.querySelector('input[name="jenis_kendaraan"]').value;
                const set_km = tr.querySelector('input[name="set_km"]').value;
                
                formData.append('nomor_kendaraan', nopol);
                formData.append('jenis_kendaraan', jenis);
                formData.append('set_km', set_km);
                
                try {
                    const res = await fetch(form.action, {
                        method: 'POST', // POST with _method=PUT inside formData
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await res.json();

                    if(res.ok && data.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, showConfirmButton: false, timer: 1500, toast: true, position: 'top-end' });
                        await refreshTable();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Data tidak valid.' });
                    }
                } catch (err) {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Koneksi bermasalah atau terjadi kesalahan.' });
                }
            }

            function submitDelete(id, nopol) {
                Swal.fire({
                    title: 'Hapus Kendaraan?',
                    text: `Anda yakin ingin menghapus data kendaraan ${nopol}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('delete-form-' + id);
                        try {
                            const res = await fetch(form.action, {
                                method: 'POST',
                                body: new FormData(form),
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });
                            const data = await res.json();
                            if(res.ok && data.success) {
                                Swal.fire({ icon: 'success', title: 'Terhapus!', text: data.message, showConfirmButton: false, timer: 1500 });
                                await refreshTable();
                            } else {
                                Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Terjadi kesalahan sistem.' });
                            }
                        } catch (err) {
                            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Koneksi bermasalah.' });
                        }
                    }
                });
            }
        </script>
    </body>
</html>
