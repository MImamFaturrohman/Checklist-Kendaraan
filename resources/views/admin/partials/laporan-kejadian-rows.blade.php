@php use App\Support\PublicStorageUrl; @endphp
@forelse($laporans as $row)
    <tr>
        <td>{{ $laporans->firstItem() + $loop->index }}</td>
        <td>
            <span class="lk-admin-name">{{ $row->nama }}</span>
            <div class="lk-admin-meta">NIP {{ $row->nip }}</div>
        </td>
        <td class="lk-admin-waktu">
            {{ $row->waktu_kejadian?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
        </td>
        <td style="min-width: 105px;">
            @if($row->kategori === 'Incident')
                <span class="lk-kat lk-kat-inc">Incident</span>
            @else
                <span class="lk-kat lk-kat-nm">Near Miss</span>
            @endif
        </td>
        <td class="lk-admin-lokasi">{{ \Illuminate\Support\Str::limit($row->lokasi_kejadian, 52) }}</td>
        <td>
            <span class="mgmt-nopol">{{ $row->nomor_kendaraan }}</span>
            <div class="lk-admin-meta">{{ $row->jenis_kendaraan }}</div>
        </td>
        <td style="width: 127px;">
            @if($row->manager_approval_token)
                <span class="lk-pending">
                    <i class="bi bi-hourglass-split"></i> Pending
                </span>
            @else
                @php
                    $lkPdfUrl = $row->pdf_path
                        ? PublicStorageUrl::resolve($row->pdf_path)
                        : route('admin.laporan-kejadian.pdf', $row);
                @endphp
                <a href="{{ $lkPdfUrl }}" target="_blank" rel="noopener noreferrer" class="btn-view-pdf">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/></svg>
                    View PDF
                </a>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="portal-empty">Belum ada laporan kejadian.</td>
    </tr>
@endforelse
