@extends('layouts.dash-app')

@section('title', 'Laporan Kejadian')
@section('pageTitle', 'Laporan Kejadian')
@section('pageSubtitle', 'PT. ARTHA DAYA COALINDO')

@php $premiumBgId = 'admin_laporan_kejadian'; @endphp

@push('styles')
<style>
        /* Selaras dengan admin/peminjaman & admin/sppd */
        .lk-admin-name { font-weight: 700; color: var(--dash-text-primary, #0f172a); }
        .lk-admin-meta { font-size: 0.76rem; opacity: 0.85; color: #64748b; }
        .dash-body.dark .lk-admin-meta { color: rgba(200, 218, 255, 0.62); }
        .lk-admin-lokasi { font-size: 0.84rem; line-height: 1.45; max-width: 280px; }
        .lk-admin-waktu { font-size: 0.84rem; white-space: nowrap; }
        .peminj-pdf {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 10px; background: #002a7a; color: #fff !important;
            border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-decoration: none;
            transition: background 0.15s;
        }
        .peminj-pdf:hover { background: #0038a8; color: #fff !important; }
        .dash-body.dark .peminj-pdf {
            background: rgba(30, 64, 128, 0.95);
            border: 1px solid rgba(212, 175, 55, 0.28);
        }
        .dash-body.dark .peminj-pdf:hover { background: rgba(40, 80, 150, 0.98); }
        .peminj-pending {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 10px; background: rgba(245, 158, 11, 0.12); color: #b45309;
            border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 8px;
            font-size: 0.75rem; font-weight: 700; white-space: nowrap;
        }
        .dash-body.dark .peminj-pending {
            background: rgba(245, 158, 11, 0.18); color: #fcd34d;
            border-color: rgba(251, 191, 36, 0.35);
        }
        .lk-kat { font-size: 0.72rem; font-weight: 700; padding: 5px 12px; border-radius: 999px; }
        .lk-kat-inc { background: rgba(239, 68, 68, 0.12); color: #b91c1c; border: 1px solid rgba(239, 68, 68, 0.25); }
        .lk-kat-nm { background: rgba(245, 158, 11, 0.12); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.25); }
        .dash-body.dark .lk-kat-inc { background: rgba(239, 68, 68, 0.18); color: #fca5a5; border-color: rgba(248, 113, 113, 0.35); }
        .dash-body.dark .lk-kat-nm { background: rgba(245, 158, 11, 0.18); color: #fcd34d; border-color: rgba(251, 191, 36, 0.35); }
    </style>
@endpush

@section('content')
    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">

            <div class="portal-stats-row" data-stat-count="3">
                <x-admin-stat-card
                    title="Total Laporan"
                    :value="$stats['total']"
                    unit="laporan"
                    description="Seluruh laporan kejadian tercatat"
                    icon="bi bi-clipboard-data-fill"
                />
                <x-admin-stat-card
                    title="Incident"
                    :value="$stats['incident']"
                    unit="kejadian"
                    description="Laporan insiden yang terjadi"
                    icon="bi bi-exclamation-triangle-fill"
                    valueStyle="color:#b91c1c"
                />
                <x-admin-stat-card
                    title="Near Miss"
                    :value="$stats['nearmiss']"
                    unit="kejadian"
                    description="Hampir terjadi insiden (near miss)"
                    icon="bi bi-shield-fill-exclamation"
                    valueStyle="color:#b45309"
                />
            </div>

            <div class="portal-section" style="margin-top: 4px">
                <div class="portal-section-header">
                    <div class="portal-section-title">
                        <i class="bi bi-table"></i> Daftar Laporan Kejadian
                    </div>
                </div>

                <div class="admin-table-wrap" style="margin-top: 16px">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width:52px">#</th>
                                <th>Pelapor</th>
                                <th>Waktu</th>
                                <th>Kategori</th>
                                <th>Lokasi</th>
                                <th>Kendaraan</th>
                                <th style="width:1%; white-space:nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
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
                                    <td>
                                        @if($row->manager_approval_token)
                                            <span class="peminj-pending">
                                                <i class="bi bi-hourglass-split"></i> Pending Approval
                                            </span>
                                        @else
                                            <a href="{{ route('admin.laporan-kejadian.pdf', $row) }}" class="peminj-pdf" target="_blank" rel="noopener">
                                                <i class="bi bi-file-earmark-pdf-fill"></i> Unduh
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="portal-empty">Belum ada laporan kejadian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($laporans->hasPages())
                    <div class="sppd-pagination-scroll">
                        <div class="admin-pagination portal-pagination-wrap sppd-pagination--unified">
                            {{ $laporans->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
