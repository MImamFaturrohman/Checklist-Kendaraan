@extends('layouts.dash-app')

@section('title', 'Log Pemakaian Kendaraan')
@section('pageTitle', 'Log Pemakaian Kendaraan')
@section('pageSubtitle', 'PT ARTHA DAYA COALINDO')

@php $premiumBgId = 'admin_vul'; @endphp

@push('styles')
<style>
    .vul-admin-name { font-weight: 700; color: var(--dash-text-primary, #0f172a); }
    .vul-admin-meta { font-size: 0.76rem; opacity: 0.85; color: #64748b; }
    .dash-body.dark .vul-admin-meta { color: rgba(200, 218, 255, 0.62); }
    .vul-admin-keperluan { font-size: 0.84rem; line-height: 1.45; min-width: 200px; max-width: 300px; }
    .vul-admin-kondisi { font-size: 0.8rem; line-height: 1.4; max-width: 300px; min-width: 200px; }
    .vul-admin-kondisi small { display: block; font-weight: 700; color: #64748b; margin-bottom: 2px; }
    .dash-body.dark .vul-admin-kondisi small { color: rgba(200, 218, 255, 0.55); }
    .vul-admin-time { font-size: 0.84rem; white-space: nowrap; }
    .vul-admin-mono { font-variant-numeric: tabular-nums; text-align: center; min-width: 80px }
</style>
@endpush

@section('content')
    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">
            <div class="portal-stats-row" data-stat-count="1">
                <x-admin-stat-card
                    title="Total Entri Log"
                    :value="$totalAll"
                    unit="entri"
                    description="Seluruh catatan pemakaian kendaraan"
                    icon="bi bi-journal-bookmark-fill"
                />
            </div>

            <div id="vul-logs-live-root" data-vms-vul-logs-live>
                @fragment('vul-logs-live-fragment')
                @php
                    $perPageOpts = [5, 10, 25, 50, 100];
                @endphp
                <div class="portal-section" style="margin-top: 8px">
                    <div class="portal-section-header">
                        <div class="portal-section-title"><i class="bi bi-table"></i> Daftar log</div>
                    </div>

                    <form method="get" action="{{ route('admin.vehicle-usage-logs.index') }}" class="portal-local-filters ppm-daftar-filters bbm-portal-live-filter-bar vul-logs-filter-form" id="vul-logs-filter-form" style="margin-top:14px">
                        <div class="admin-search-wrap portal-search-full">
                            <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nopol, jenis, nama driver, keperluan…" class="admin-search-input" autocomplete="off" aria-label="Cari log">
                        </div>
                        <div class="ppm-status-wrap bbm-portal-date-range">
                            <label class="sr-only" for="vul-arch-date-from">Tanggal mulai</label>
                            <input type="date" name="date_from" id="vul-arch-date-from" class="admin-filter-input" value="{{ $filters['date_from'] ?? '' }}" title="Dari tanggal (dicatat)" aria-label="Dari tanggal">
                            <label class="sr-only" for="vul-arch-date-to">Tanggal akhir</label>
                            <input type="date" name="date_to" id="vul-arch-date-to" class="admin-filter-input" value="{{ $filters['date_to'] ?? '' }}" title="Sampai tanggal" aria-label="Sampai tanggal">
                        </div>
                        <div class="portal-perpage-wrap sppd-per-page-wrap">
                            <span class="portal-perpage-label" id="vul-arch-per-label">Per halaman</span>
                            <label class="sr-only" for="vul-arch-per">Per halaman</label>
                            <select name="per_page" id="vul-arch-per" class="admin-filter-input sppd-per-page-select" aria-labelledby="vul-arch-per-label">
                                @foreach($perPageOpts as $n)
                                    <option value="{{ $n }}" @selected(($logs->perPage() ?? 25) === $n)>{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ppm-status-wrap bbm-portal-filter-actions">
                            <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite ppm-filter-reset" data-vul-logs-reset title="Hapus semua filter" aria-label="Hapus semua filter"><i class="bi bi-arrow-clockwise"></i></button>
                        </div>
                    </form>

                    <div class="admin-table-wrap" style="margin-top: 16px">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Waktu dicatat</th>
                                    <th>Driver</th>
                                    <th>Kendaraan</th>
                                    <th>BBM Awal</th>
                                    <th>BBM Akhir</th>
                                    <th>KM Awal</th>    
                                    <th>KM Akhir</th>
                                    <th>Durasi</th>
                                    <th>Keperluan</th>
                                    <th>Kondisi Sebelum</th>
                                    <th>Kondisi Sesudah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $row)
                                    @php
                                        $fmtT = fn ($t) => is_string($t) ? substr($t, 0, 5) : (\Illuminate\Support\Carbon::parse($t)->format('H:i'));
                                        $wAwal = $row->jam_awal;
                                        $wAkhir = $row->jam_akhir;
                                        $tAwal = $fmtT($wAwal);
                                        $tAkhir = $fmtT($wAkhir);
                                        $kep = $row->keperluan;
                                        $kepShort = \Illuminate\Support\Str::limit(strip_tags($kep), 80);
                                        $bbmA = $row->level_bbm_awal;
                                        $bbmB = $row->level_bbm_akhir;
                                        if (($bbmA !== null && $bbmA !== '' && is_numeric($bbmA)) && ($bbmB !== null && $bbmB !== '' && is_numeric($bbmB))) {
                                            $bbmLine = (int) $bbmA.'% → '.(int) $bbmB.'%';
                                        } elseif ($bbmA || $bbmB) {
                                            $bbmLine = trim(($bbmA ?: '—').' → '.($bbmB ?: '—'));
                                        } else {
                                            $bbmLine = '—';
                                        }
                                        $kmA = $row->km_awal;
                                        $kmB = $row->km_akhir;
                                        $kmLine = ($kmA !== null && $kmB !== null)
                                            ? number_format((int) $kmA).' → '.number_format((int) $kmB)
                                            : '—';
                                        $kSeb = $row->kondisi_sebelum_penggunaan;
                                        $kSes = $row->kondisi_setelah_penggunaan;
                                        $kSebShort = $kSeb ? \Illuminate\Support\Str::limit(strip_tags($kSeb), 120) : null;
                                        $kSesShort = $kSes ? \Illuminate\Support\Str::limit(strip_tags($kSes), 120) : null;
                                    @endphp
                                    <tr>
                                        <td>{{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}</td>
                                        <td class="vul-admin-time">{{ $row->created_at?->translatedFormat('d F Y H:i') }}</td>
                                        <td>
                                            <span class="vul-admin-name">{{ $row->user?->name ?? '—' }}</span><br>
                                            <span class="vul-admin-meta">{{ $row->user?->username ?? '' }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $row->nomor_kendaraan }}</strong><br>
                                            <span class="vul-admin-meta">{{ $row->jenis_kendaraan }}</span>
                                        </td>
                                        
                                        <td class="vul-admin-mono">{{ $row->level_bbm_awal ? (int)$row->level_bbm_awal.'%' : '—' }}</td>
                                        <td class="vul-admin-mono">{{ $row->level_bbm_akhir ? (int)$row->level_bbm_akhir.'%' : '—' }}</td>
                                        
                                        <td class="vul-admin-mono">{{ $row->km_awal ? number_format((int)$row->km_awal) : '—' }}</td>
                                        <td class="vul-admin-mono">{{ $row->km_akhir ? number_format((int)$row->km_akhir) : '—' }}</td>
                                        
                                        <td class="vul-admin-time">{{ $row->durasiDeskripsi() }}</td>
                                        <td class="vul-admin-keperluan" title="{{ $kep }}">{{ $kepShort }}</td>
                                        
                                        <td class="vul-admin-kondisi">
                                            @if($kSebShort)
                                                <span title="{{ $kSeb }}">{{ $kSebShort }}</span>
                                            @else
                                                <span class="vul-admin-meta">—</span>
                                            @endif
                                        </td>
                                        <td class="vul-admin-kondisi">
                                            @if($kSesShort)
                                                <span title="{{ $kSes }}">{{ $kSesShort }}</span>
                                            @else
                                                <span class="vul-admin-meta">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="11" class="portal-empty">Belum ada log penggunaan kendaraan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="sppd-pagination-scroll" style="margin-top: 12px">
                        <div class="admin-pagination portal-pagination-wrap sppd-pagination--unified">{{ $logs->links() }}</div>
                    </div>
                </div>
                @endfragment
            </div>
        </div>
    </div>

@endsection
