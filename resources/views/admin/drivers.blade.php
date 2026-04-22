<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manajemen Driver - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .driver-input {
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.85rem;
            width: 100%;
            transition: all 0.2s;
        }
        .driver-input:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        .driver-pw-hint { font-size: 0.7rem; color: #94a3b8; margin-top: 3px; }
        .driver-add-form { background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 20px; }
        .driver-add-grid { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 12px; align-items: end; }
        .driver-field label { display: block; margin-bottom: 5px; font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        @media (max-width: 700px) { .driver-add-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="dash-body">
<div class="armada-shell">

    <header class="checklist-topbar" style="margin-bottom:6px">
        <div>
            <h1 class="dash-brand-title">Manajemen Driver</h1>
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

        {{-- ADD FORM --}}
        <form id="form-add-driver" class="driver-add-form">
            @csrf
            <p style="font-size:0.8rem;font-weight:700;color:#475569;margin:0 0 12px;text-transform:uppercase;letter-spacing:0.5px">Tambah Driver Baru</p>
            <div class="driver-add-grid">
                <div class="driver-field">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="driver-input" placeholder="Nama Lengkap" required>
                </div>
                <div class="driver-field">
                    <label>Username</label>
                    <input type="text" name="username" class="driver-input" placeholder="username_driver" required autocomplete="off">
                </div>
                <div class="driver-field">
                    <label>Password</label>
                    <input type="password" name="password" class="driver-input" placeholder="Min. 6 karakter" required autocomplete="new-password">
                </div>
                <div>
                    <button type="submit" class="armada-btn armada-btn-add" id="btn-add-driver">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" style="margin-right:5px">
                            <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Tambah
                    </button>
                </div>
            </div>
        </form>

        {{-- SEARCH --}}
        <form method="GET" action="{{ route('admin.drivers') }}" class="admin-toolbar" data-admin-toolbar>
            <div class="admin-search-wrap">
                <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                    <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama atau username driver..."
                    class="admin-search-input">
                @if(request('search'))
                    <a href="{{ route('admin.drivers') }}" class="admin-search-clear">&times;</a>
                @endif
            </div>
            <div class="admin-filter-row">
                <button type="submit" class="admin-filter-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.drivers') }}" class="admin-filter-reset">Reset</a>
                @endif
            </div>
        </form>

        {{-- TABLE --}}
        <div id="table-container">
            <table class="armada-table mt-4 w-full">
                <thead>
                    <tr>
                        <th style="width:44px">#</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Password Baru</th>
                        <th style="text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($drivers as $d)
                        <tr id="drow-{{ $d->id }}">
                            <td>{{ ($drivers->currentPage() - 1) * $drivers->perPage() + $loop->iteration }}</td>
                            <td>
                                <span class="view-mode" style="font-weight:600">{{ $d->name }}</span>
                                <input class="edit-mode driver-input" type="text" value="{{ $d->name }}"
                                    name="name" form="dedit-{{ $d->id }}" style="display:none">
                            </td>
                            <td>
                                <span class="view-mode" style="color:#2563eb;font-weight:600">{{ $d->username }}</span>
                                <input class="edit-mode driver-input" type="text" value="{{ $d->username }}"
                                    name="username" form="dedit-{{ $d->id }}" style="display:none">
                            </td>
                            <td>
                                <span class="view-mode" style="color:#94a3b8;font-size:0.8rem">—</span>
                                <div class="edit-mode" style="display:none">
                                    <input class="driver-input" type="password" name="password"
                                        form="dedit-{{ $d->id }}" placeholder="Kosongkan jika tidak diubah"
                                        autocomplete="new-password">
                                    <p class="driver-pw-hint">Biarkan kosong agar password tidak berubah</p>
                                </div>
                            </td>
                            <td style="text-align:center;white-space:nowrap">
                                <form id="dedit-{{ $d->id }}" action="{{ route('admin.drivers.update', $d) }}"
                                    method="POST" style="display:none"
                                    onsubmit="event.preventDefault(); submitDriverEdit({{ $d->id }})">
                                    @csrf @method('PUT')
                                </form>

                                <button type="button" class="armada-btn armada-btn-edit view-mode"
                                    onclick="toggleDriverEdit({{ $d->id }})">Edit</button>
                                <button type="button" class="armada-btn armada-btn-edit edit-mode"
                                    style="display:none;background:#22c55e;color:#fff"
                                    onclick="submitDriverEdit({{ $d->id }})">Simpan</button>
                                <button type="button" class="armada-btn armada-btn-edit edit-mode"
                                    style="display:none;background:#64748b;color:#fff"
                                    onclick="toggleDriverEdit({{ $d->id }})">Batal</button>

                                <form id="ddel-{{ $d->id }}" action="{{ route('admin.drivers.destroy', $d) }}"
                                    method="POST" style="display:inline"
                                    onsubmit="event.preventDefault(); deleteDriver({{ $d->id }}, '{{ addslashes($d->name) }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="armada-btn armada-btn-delete view-mode">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;color:#9ca3af;padding:32px">
                                Belum ada data driver.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="admin-pagination mt-4">{{ $drivers->links() }}</div>
        </div>

    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

/* ── Toggle inline edit ── */
function toggleDriverEdit(id) {
    const row = document.getElementById('drow-' + id);
    const inEdit = row.querySelector('.edit-mode').style.display !== 'none'
                && row.querySelector('.edit-mode').style.display !== '';
    row.querySelectorAll('.view-mode').forEach(el => el.style.display = inEdit ? '' : 'none');
    row.querySelectorAll('.edit-mode').forEach(el => el.style.display = inEdit ? 'none' : (el.tagName === 'DIV' ? 'block' : 'inline-block'));
    row.querySelectorAll('input.edit-mode').forEach(el => { if (!inEdit) el.style.display = 'block'; });
}

/* ── Refresh table ── */
async function refreshTable() {
    try {
        const res = await fetch(window.location.href);
        const html = await res.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        document.getElementById('table-container').innerHTML = doc.getElementById('table-container').innerHTML;
    } catch (e) { console.error(e); }
}

/* ── Add driver ── */
document.getElementById('form-add-driver').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('btn-add-driver');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    const fd = new FormData(this);
    try {
        const res = await fetch('{{ route("admin.drivers.store") }}', {
            method: 'POST', body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (res.ok && data.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1600, showConfirmButton: false });
            this.reset();
            await refreshTable();
        } else {
            const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || 'Gagal.');
            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Koneksi Bermasalah', text: 'Periksa koneksi internet.' });
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" style="margin-right:5px"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Tambah';
    }
});

/* ── Update driver ── */
async function submitDriverEdit(id) {
    const form  = document.getElementById('dedit-' + id);
    const row   = document.getElementById('drow-' + id);
    const name  = row.querySelector('input[name="name"]').value;
    const uname = row.querySelector('input[name="username"]').value;
    const pw    = row.querySelector('input[name="password"]').value;

    const fd = new FormData(form);
    fd.set('name',     name);
    fd.set('username', uname);
    if (pw) fd.set('password', pw);
    else    fd.delete('password');

    try {
        const res = await fetch(form.action, {
            method: 'POST', body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (res.ok && data.success) {
            Swal.fire({ icon: 'success', title: 'Diperbarui!', text: data.message, timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
            await refreshTable();
        } else {
            const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || 'Gagal.');
            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Koneksi Bermasalah', text: 'Periksa koneksi internet.' });
    }
}

/* ── Delete driver ── */
function deleteDriver(id, nama) {
    Swal.fire({
        title: 'Hapus Driver?',
        html: `<p>Yakin ingin menghapus driver <strong>${nama}</strong>?</p>
               <div style="margin-top:10px;padding:10px;background:#fef9c3;border:1px solid #fde68a;border-radius:8px;font-size:0.82rem;color:#92400e;text-align:left">
                   ⚠️ Data ceklist yang dibuat oleh driver ini tidak akan terhapus.
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
    }).then(async (result) => {
        if (!result.isConfirmed) return;
        const form = document.getElementById('ddel-' + id);
        try {
            const res = await fetch(form.action, {
                method: 'POST', body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (res.ok && data.success) {
                Swal.fire({ icon: 'success', title: 'Terhapus!', text: data.message, timer: 1500, showConfirmButton: false });
                await refreshTable();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Terjadi kesalahan.' });
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Koneksi Bermasalah', text: 'Periksa koneksi internet.' });
        }
    });
}
</script>
</body>
</html>
