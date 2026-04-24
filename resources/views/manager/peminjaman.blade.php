<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Persetujuan Peminjaman - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="dash-body">
    <div class="admin-shell">

        <header class="checklist-topbar" style="margin-bottom:6px">
            <div>
                <h1 class="dash-brand-title">Persetujuan Peminjaman</h1>
                <p class="dash-brand-sub">PT ARTHA DAYA COALINDO</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="dash-chip dash-chip-manager">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    MANAGER
                </span>
                <a href="{{ route('dashboard') }}" class="checklist-icon-btn" aria-label="Kembali ke dashboard">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none">
                        <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </header>

        {{-- TAB BUTTONS --}}
        <div class="admin-tabs" style="margin-top:16px">
            <button class="admin-tab active" onclick="switchTab('pending', this)">
                Menunggu Persetujuan
                @if($pendingRequests->total() > 0)
                    <span class="manager-badge-count">{{ $pendingRequests->total() }}</span>
                @endif
            </button>
            <button class="admin-tab" onclick="switchTab('history', this)">
                Riwayat
            </button>
        </div>

        {{-- PENDING TAB --}}
        <div id="tab-pending" class="manager-tab-content">
            @if($pendingRequests->total() === 0)
                <div class="admin-card" style="text-align:center;padding:48px 24px;color:#9ca3af">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" style="margin:0 auto 12px;display:block;opacity:0.35">
                        <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p style="font-weight:700;font-size:1rem;color:#6b7280">Tidak ada request yang menunggu persetujuan.</p>
                    <p style="font-size:0.85rem;margin-top:4px">Semua request sudah diproses.</p>
                </div>
            @else
                <div class="manager-request-list">
                    @foreach($pendingRequests as $req)
                        <div class="manager-request-card" id="req-{{ $req->id }}">
                            <div class="manager-request-top">
                                <div class="manager-request-info">
                                    <div class="manager-request-name">{{ $req->nama_lengkap }}</div>
                                    <div class="manager-request-meta">
                                        <span>NIP: {{ $req->nip }}</span>
                                        <span class="meta-sep">&bull;</span>
                                        <span>{{ $req->jabatan }}</span>
                                        <span class="meta-sep">&bull;</span>
                                        @if($req->tanggal_peminjaman)
                                            <span>{{ \Carbon\Carbon::parse($req->tanggal_peminjaman)->translatedFormat('d M Y') }}</span>
                                            <span class="meta-sep">&bull;</span>
                                        @endif
                                        <span>{{ $req->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                </div>
                                <span class="status-badge status-pending">
                                    <svg width="8" height="8" viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg>
                                    Menunggu
                                </span>
                            </div>

                            <div class="manager-vehicle-block">
                                <div class="manager-vehicle-row">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="color:#2563eb;flex-shrink:0">
                                        <path d="M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v6a2 2 0 01-2 2h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="9" cy="17" r="2" stroke="currentColor" stroke-width="2"/>
                                        <circle cx="17" cy="17" r="2" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                    <div>
                                        <div class="manager-vehicle-nopol">{{ $req->nomor_kendaraan }}</div>
                                        <div class="manager-vehicle-jenis">{{ $req->jenis_kendaraan }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="manager-alasan-block">
                                <span class="manager-alasan-label">Keperluan:</span>
                                <p class="manager-alasan-text">{{ $req->alasan }}</p>
                            </div>

                            <div class="manager-actions">
                                <button class="manager-btn-approve"
                                    onclick="approveRequest({{ $req->id }}, '{{ addslashes($req->nama_lengkap) }}', '{{ $req->nomor_kendaraan }}')">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                        <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Setujui
                                </button>
                                <button class="manager-btn-reject"
                                    onclick="rejectRequest({{ $req->id }}, '{{ addslashes($req->nama_lengkap) }}', '{{ $req->nomor_kendaraan }}')">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Tolak
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="admin-pagination mt-4">{{ $pendingRequests->links() }}</div>
            @endif
        </div>

        {{-- HISTORY TAB --}}
        <div id="tab-history" class="manager-tab-content" style="display:none">
            @if($historyRequests->total() === 0)
                <div class="admin-card" style="text-align:center;padding:48px 24px;color:#9ca3af">
                    <p style="font-weight:700;font-size:1rem;color:#6b7280">Belum ada riwayat persetujuan.</p>
                </div>
            @else
                <div class="admin-card" style="margin-top:0">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama / NIP / Jabatan</th>
                                    <th>Kendaraan</th>
                                    <th>Keperluan</th>
                                    <th>Status</th>
                                    <th>Catatan</th>
                                    <th>Diproses</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historyRequests as $req)
                                    <tr>
                                        <td>{{ ($historyRequests->currentPage() - 1) * $historyRequests->perPage() + $loop->iteration }}</td>
                                        <td>
                                            <div style="font-weight:700;color:#0f172a">{{ $req->nama_lengkap }}</div>
                                            <div style="font-size:0.76rem;color:#64748b">{{ $req->nip }} &bull; {{ $req->jabatan }}</div>
                                            @if($req->tanggal_peminjaman)
                                                <div style="font-size:0.72rem;color:#94a3b8">{{ \Carbon\Carbon::parse($req->tanggal_peminjaman)->translatedFormat('d M Y') }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="landing-nopol-badge">{{ $req->nomor_kendaraan }}</span>
                                            <div style="font-size:0.76rem;color:#64748b;margin-top:2px">{{ $req->jenis_kendaraan }}</div>
                                        </td>
                                        <td style="max-width:200px">
                                            <div style="font-size:0.82rem;color:#374151;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px" title="{{ $req->alasan }}">
                                                {{ $req->alasan }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge {{ $req->isApproved() ? 'status-approved' : 'status-rejected' }}">
                                                @if($req->isApproved())
                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    Disetujui
                                                @else
                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    Ditolak
                                                @endif
                                            </span>
                                        </td>
                                        <td style="font-size:0.82rem;color:#64748b;max-width:160px">
                                            {{ $req->catatan_manager ?: '-' }}
                                        </td>
                                        <td style="font-size:0.78rem;color:#94a3b8;white-space:nowrap">
                                            {{ $req->approved_at?->format('d M Y') }}<br>
                                            <span style="color:#94a3b8">{{ $req->approver?->name ?? '-' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="admin-pagination mt-4">{{ $historyRequests->appends(['pending_page' => request('pending_page')])->links() }}</div>
                </div>
            @endif
        </div>

    </div>

    <script>
        function switchTab(tab, btn) {
            document.querySelectorAll('.manager-tab-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.admin-tab').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tab).style.display = '';
            btn.classList.add('active');
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        async function approveRequest(id, nama, nopol) {
            const result = await Swal.fire({
                title: 'Setujui Permintaan?',
                html: `<p style="color:#374151;font-size:0.92rem">Anda akan menyetujui request peminjaman <strong>${nopol}</strong> atas nama <strong>${nama}</strong>.</p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal',
            });

            if (!result.isConfirmed) return;

            try {
                const res = await fetch(`/manager/peminjaman/${id}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({}),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    Swal.fire({ icon: 'success', title: 'Disetujui!', text: data.message, showConfirmButton: false, timer: 1800 });
                    setTimeout(() => location.reload(), 1900);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Terjadi kesalahan.' });
                }
            } catch {
                Swal.fire({ icon: 'error', title: 'Koneksi Bermasalah', text: 'Tidak dapat menghubungi server.' });
            }
        }

        async function rejectRequest(id, nama, nopol) {
            const { value: catatan, isConfirmed } = await Swal.fire({
                title: 'Tolak Permintaan?',
                html: `<p style="color:#374151;font-size:0.92rem;margin-bottom:12px">Tolak request <strong>${nopol}</strong> atas nama <strong>${nama}</strong>.</p>`,
                input: 'textarea',
                inputLabel: 'Catatan penolakan (opsional)',
                inputPlaceholder: 'Masukkan alasan penolakan...',
                inputAttributes: { style: 'font-size:0.88rem;border-radius:10px;border:1px solid #d1d5db;padding:10px' },
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal',
            });

            if (!isConfirmed) return;

            try {
                const res = await fetch(`/manager/peminjaman/${id}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ catatan_manager: catatan || '' }),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    Swal.fire({ icon: 'success', title: 'Ditolak', text: data.message, showConfirmButton: false, timer: 1800 });
                    setTimeout(() => location.reload(), 1900);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Terjadi kesalahan.' });
                }
            } catch {
                Swal.fire({ icon: 'error', title: 'Koneksi Bermasalah', text: 'Tidak dapat menghubungi server.' });
            }
        }
    </script>
</body>
</html>
