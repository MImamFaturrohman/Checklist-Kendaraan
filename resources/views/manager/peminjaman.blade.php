@extends('layouts.dash-app')

@section('title', 'Persetujuan Peminjaman')
@section('pageTitle', 'Persetujuan Peminjaman')
@section('pageSubtitle', 'PT ARTHA DAYA COALINDO')

@php $premiumBgId = 'manager_peminjaman'; @endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
@endpush

@section('content')
    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">
            <div id="mgr-peminjaman-live-root">

                <div class="portal-section-header" style="margin-bottom:12px">
                    <div class="portal-section-title">Menunggu Persetujuan</div>
                </div>
                <div class="admin-table-wrap" data-sort-scope="pending">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <x-sortable-th key="nama_lengkap" label="Pemohon" :activeSort="$pendingActiveSort ?? null" :activeDir="$pendingActiveDir ?? null" scope="pending" />
                                <th>Keperluan</th>
                                <x-sortable-th key="nomor_kendaraan" label="Kendaraan" :activeSort="$pendingActiveSort ?? null" :activeDir="$pendingActiveDir ?? null" scope="pending" />
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingRequests as $req)
                                <tr id="req-{{ $req->id }}">
                                    <td>
                                        <span class="sppd-cell-title">{{ $req->nama_lengkap }}</span><br>
                                        <span class="sppd-cell-muted">{{ $req->nip }} · {{ $req->jabatan }}</span>
                                        @if($req->bidang)
                                            <br><span class="sppd-cell-muted">{{ $req->bidang->labelLengkap() }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ \Illuminate\Support\Str::limit($req->alasan, 42) }}<br>
                                        <span class="sppd-cell-muted">
                                            @if($req->tanggal_peminjaman)
                                                Tgl. Pinjam {{ \Carbon\Carbon::parse($req->tanggal_peminjaman)->translatedFormat('d M Y') }}
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $req->nomor_kendaraan }}</strong><br>
                                        <span class="sppd-cell-muted">{{ $req->jenis_kendaraan }}</span>
                                    </td>
                                    <td>
                                        <div class="sppd-aksi-btns">
                                            <button type="button"
                                                class="btn btn-sm sppd-icon-btn sppd-btn-success mgr-pem-approve"
                                                data-id="{{ $req->id }}"
                                                data-nama="{{ e($req->nama_lengkap) }}"
                                                data-nopol="{{ e($req->nomor_kendaraan) }}"
                                                title="Setujui"
                                                aria-label="Setujui permohonan">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button type="button"
                                                class="btn btn-sm sppd-icon-btn sppd-btn-danger mgr-pem-reject"
                                                data-id="{{ $req->id }}"
                                                data-nama="{{ e($req->nama_lengkap) }}"
                                                data-nopol="{{ e($req->nomor_kendaraan) }}"
                                                title="Tolak"
                                                aria-label="Tolak permohonan">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="portal-empty">Tidak ada antrian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-admin-pagination :paginator="$pendingRequests" />

                <div class="portal-section-header" style="margin:28px 0 12px">
                    <div class="portal-section-title">Riwayat</div>
                </div>
                <div class="admin-table-wrap" data-sort-scope="history">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <x-sortable-th key="nama_lengkap" label="Pemohon" :activeSort="$historyActiveSort ?? null" :activeDir="$historyActiveDir ?? null" scope="history" />
                                <x-sortable-th key="nomor_kendaraan" label="Kendaraan" :activeSort="$historyActiveSort ?? null" :activeDir="$historyActiveDir ?? null" scope="history" />
                                <th>Keperluan</th>
                                <x-sortable-th key="status" label="Status" :activeSort="$historyActiveSort ?? null" :activeDir="$historyActiveDir ?? null" scope="history" />
                                <th>Catatan</th>
                                <x-sortable-th key="updated_at" label="Diproses" :activeSort="$historyActiveSort ?? null" :activeDir="$historyActiveDir ?? null" scope="history" />
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historyRequests as $req)
                                <tr>
                                    <td class="sppd-cell-muted">{{ ($historyRequests->currentPage() - 1) * $historyRequests->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <span class="sppd-cell-title">{{ $req->nama_lengkap }}</span><br>
                                        <span class="sppd-cell-muted">{{ $req->nip }} · {{ $req->jabatan }}</span>
                                        @if($req->bidang)
                                            <br><span class="sppd-cell-muted">{{ $req->bidang->labelLengkap() }}</span>
                                        @endif
                                        @if($req->tanggal_peminjaman)
                                            <br><span class="sppd-cell-muted">{{ \Carbon\Carbon::parse($req->tanggal_peminjaman)->translatedFormat('d M Y') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $req->nomor_kendaraan }}</strong><br>
                                        <span class="sppd-cell-muted">{{ $req->jenis_kendaraan }}</span>
                                    </td>
                                    <td style="max-width:200px">
                                        <div style="font-size:0.85rem;color:#374151;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px" title="{{ $req->alasan }}">
                                            {{ $req->alasan }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($req->isApproved())
                                            <span class="status-badge status-approved">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                Disetujui
                                            </span>
                                        @elseif($req->isExpired())
                                            <span class="status-badge status-expired">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                                                Expired
                                            </span>
                                        @else
                                            <span class="status-badge status-rejected">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                Ditolak
                                            </span>
                                        @endif
                                    </td>
                                    <td class="sppd-cell-muted" style="max-width:160px">
                                        {{ $req->catatan_manager ?: '—' }}
                                    </td>
                                    <td class="sppd-cell-muted" style="white-space:nowrap">
                                        {{ $req->approved_at?->format('d/m/Y H:i') ?? '—' }}<br>
                                        <span>{{ $req->approver?->name ?? '—' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="portal-empty">Belum ada riwayat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-admin-pagination :paginator="$historyRequests" />

            </div>
        </div>
    </div>

    <script>
    (function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        async function approveRequest(id, nama, nopol) {
            const result = await Swal.fire({
                title: 'Setujui permohonan?',
                html: `<p style="color:#374151;font-size:0.92rem">Setujui peminjaman <strong>${nopol}</strong> atas nama <strong>${nama}</strong>.</p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Setujui',
                cancelButtonText: 'Batal',
            });
            if (!result.isConfirmed) return;

            try {
                const res = await fetch('/manager/peminjaman/' + id + '/approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: '{}',
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    await Swal.fire({ icon: 'success', title: 'Disetujui', text: data.message, timer: 1800, showConfirmButton: false });
                    location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Terjadi kesalahan.' });
                }
            } catch {
                Swal.fire({ icon: 'error', title: 'Koneksi bermasalah', text: 'Tidak dapat menghubungi server.' });
            }
        }

        async function rejectRequest(id, nama, nopol) {
            const { value: catatan, isConfirmed } = await Swal.fire({
                title: 'Tolak permohonan?',
                html: `<p style="color:#374151;font-size:0.92rem;margin-bottom:12px">Tolak peminjaman <strong>${nopol}</strong> atas nama <strong>${nama}</strong>.</p>`,
                input: 'textarea',
                inputLabel: 'Catatan penolakan (opsional)',
                inputPlaceholder: 'Alasan penolakan…',
                showCancelButton: true,
                confirmButtonText: 'Tolak',
                cancelButtonText: 'Batal',
            });
            if (!isConfirmed) return;

            try {
                const res = await fetch('/manager/peminjaman/' + id + '/reject', {
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
                    await Swal.fire({ icon: 'success', title: 'Ditolak', text: data.message, timer: 1800, showConfirmButton: false });
                    location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Terjadi kesalahan.' });
                }
            } catch {
                Swal.fire({ icon: 'error', title: 'Koneksi bermasalah', text: 'Tidak dapat menghubungi server.' });
            }
        }

        document.getElementById('mgr-peminjaman-live-root')?.addEventListener('click', (e) => {
            const appr = e.target.closest('.mgr-pem-approve');
            if (appr) {
                const id = appr.dataset.id;
                const nama = appr.dataset.nama || '';
                const nopol = appr.dataset.nopol || '';
                approveRequest(id, nama, nopol);
                return;
            }
            const rej = e.target.closest('.mgr-pem-reject');
            if (rej) {
                const id = rej.dataset.id;
                const nama = rej.dataset.nama || '';
                const nopol = rej.dataset.nopol || '';
                rejectRequest(id, nama, nopol);
            }
        });
    })();

    // Sort header wiring — full-page GET navigation (scoped params)
    document.addEventListener('turbo:load', function() {
        if (!window.AdminTableSort) return;
        document.querySelectorAll('.admin-table-wrap[data-sort-scope]').forEach(wrap => {
            const scope = wrap.dataset.sortScope;
            window.AdminTableSort.bindRoot(wrap, {
                getUrl: () => new URL(location.href),
                onNavigate: (url) => { window.location.href = url.toString(); },
                scope,
                pageKey: scope === 'pending' ? 'pending_page' : 'history_page',
            });
        });
    });
    </script>
@endsection
