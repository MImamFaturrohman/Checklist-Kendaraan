@php use App\Support\PublicStorageUrl; @endphp
@forelse($requests as $req)
    <tr>
        <td>{{ ($requests->currentPage() - 1) * $requests->perPage() + $loop->iteration }}</td>
        <td style="min-width: 150px; max-width: 200px;">
            <div class="peminj-name">{{ $req->nama_lengkap }}</div>
            <div class="peminj-meta">{{ $req->nip }}</div>
            <div class="peminj-meta">{{ $req->jabatan }}</div>
            @if($req->tanggal_peminjaman)
                <div class="peminj-meta-sm">{{ \Carbon\Carbon::parse($req->tanggal_peminjaman)->translatedFormat('d M Y') }}</div>
            @endif
        </td>
        <td style="min-width: 217px; max-width: 250px;">
            @if($req->bidang)
                <span class="peminj-bidang-nama">{{ $req->bidang->nama }}</span>
                @if($req->bidang->parent)
                    <div class="peminj-meta-sm">{{ $req->bidang->parent->nama }}</div>
                @endif
            @else
                <span class="peminj-meta" style="opacity:0.5">—</span>
            @endif
        </td>
        <td style="min-width: 150px; max-width: 200px;">
            <span class="landing-nopol-badge">{{ $req->nomor_kendaraan }}</span>
            <div class="peminj-meta" style="margin-top:3px">{{ $req->jenis_kendaraan }}</div>
        </td>
        <td style="min-width: 200px; max-width:250px">
            <div class="peminj-meta" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px" title="{{ $req->alasan }}">
                {{ $req->alasan }}
            </div>
        </td>
        <td>
            @if($req->isPending())
                <span class="status-badge status-pending">
                    <svg width="8" height="8" viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg>
                    Pending
                </span>
            @elseif($req->isApproved())
                <span class="status-badge status-approved">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Approved
                </span>
            @elseif($req->isExpired())
                <span class="status-badge status-expired">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                    Expired
                </span>
            @else
                <span class="status-badge status-rejected">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Rejected
                </span>
            @endif
        </td>
        <td class="peminj-meta" style="max-width: 220px;">
            {{ $req->catatan_manager ?: '-' }}
        </td>
        <td class="peminj-meta" style="white-space:nowrap;font-size:0.78rem">
            {{ $req->created_at->format('d M Y') }}<br>
            {{ $req->created_at->format('H:i') }}
        </td>
        <td class="peminj-meta" style="white-space:nowrap;font-size:0.78rem">
            @if($req->approved_at)
                {{ $req->approved_at->format('d M Y') }}<br>
                <span>{{ $req->approver?->name ?? '-' }}</span>
            @else
                <span style="opacity:0.45">—</span>
            @endif
        </td>
        <td style="white-space:nowrap">
            @if($req->isApproved())
                @php
                    $ppmPdfUrl = $req->pdf_path
                        ? PublicStorageUrl::resolve($req->pdf_path)
                        : route('admin.peminjaman.pdf', $req);
                @endphp
                <a href="{{ $ppmPdfUrl }}" target="_blank" rel="noopener noreferrer" class="btn-view-pdf">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/></svg>
                    View PDF
                </a>
            @else
                <span class="peminj-meta" style="opacity:0.45">—</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="peminj-empty">
            Tidak ada data peminjaman.
        </td>
    </tr>
@endforelse
