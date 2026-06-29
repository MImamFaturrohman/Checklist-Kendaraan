@extends('layouts.dash-app')

@section('title', 'Portal Pemeriksaan Kendaraan')
@section('pageTitle', 'Portal Pemeriksaan Kendaraan')
@section('pageSubtitle', ($pemeriksaanInsightOnlyManager ?? false) ? 'Insight kartu & grafik pemeriksaan' : 'PT ARTHA DAYA COALINDO')

@php $premiumBgId = 'portal_pemeriksaan'; @endphp

@push('head')
<meta name="turbo-cache-control" content="no-cache">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
@endpush

@section('content')
<div class="admin-shell" style="position:relative;z-index:1">
    @php $canAccessDatabase = $canAccessDatabase ?? false; @endphp

    <div class="portal-wrapper">

        {{-- ============================================================
             STATS ROW  (PM FuelLog–style premium cards)
        ============================================================ --}}
        <div class="portal-stats-row" data-stat-count="4">
            <x-admin-stat-card
                title="Total Ceklist"
                :value="$dbStats['total']"
                unit="Laporan"
                description="Seluruh laporan pemeriksaan"
                icon="bi bi-clipboard-data-fill"
            />
            <x-admin-stat-card
                title="Ceklist Tahun Ini"
                :value="$dbStats['tahun_ini']"
                unit="Laporan"
                :description="'Laporan pemeriksaan tahun ' . $chartYear"
                icon="bi bi-calendar-check-fill"
            />
            <x-admin-stat-card
                title="Kendaraan"
                :value="$dbStats['kendaraan_unik']"
                unit="Unit"
                description="Unit kendaraan terdaftar"
                icon="bi bi-truck-front-fill"
            />
            <x-admin-stat-card
                title="Driver Aktif"
                :value="$dbStats['driver_aktif']"
                unit="Personel"
                icon="bi bi-person-fill-check"
            />
        </div>

        {{-- ============================================================
             CHARTS  (BBM + Shift atas, Ceklist Bulan + Kendaraan bawah)
        ============================================================ --}}
        <div class="portal-charts-grid portal-charts-grid--pemeriksaan" id="portal-charts-pemeriksaan" data-portal-charts="pemeriksaan">
            <div class="portal-chart-card portal-chart-card--bbm-slot">
                <div class="portal-chart-head">
                    <div class="portal-chart-title">Rata-rata Level BBM per Kendaraan (%)</div>
                    <div class="portal-chart-year-wrap">
                        <label class="portal-chart-year-label" for="chart-year-bbm">Tahun</label>
                        <select id="chart-year-bbm" class="portal-chart-year-select admin-filter-input" data-chart-key="bbm" aria-label="Tahun grafik BBM">
                            @foreach($yearsAvailable as $y)
                                <option value="{{ $y }}" @selected((int) $chartYear === (int) $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="portal-chart-container portal-chart-container--bbm">
                    <div class="portal-chart-loading"><span class="portal-chart-loading-spinner"></span></div>
                    <canvas id="chartBbm"></canvas>
                </div>
            </div>
            <div class="portal-chart-card portal-chart-card--shift-slot">
                <div class="portal-chart-head">
                    <div class="portal-chart-title">Distribusi Shift</div>
                    <div class="portal-chart-year-wrap">
                        <label class="portal-chart-year-label" for="chart-year-shift">Tahun</label>
                        <select id="chart-year-shift" class="portal-chart-year-select admin-filter-input" data-chart-key="shift" aria-label="Tahun grafik shift">
                            @foreach($yearsAvailable as $y)
                                <option value="{{ $y }}" @selected((int) $chartYear === (int) $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="portal-chart-container portal-chart-container--doughnut">
                    <div class="portal-chart-loading"><span class="portal-chart-loading-spinner"></span></div>
                    <canvas id="chartShift"></canvas>
                </div>
            </div>
            <div class="portal-chart-card portal-chart-card--ceklist-duo">
                <div class="portal-chart-head">
                    <div class="portal-chart-title">Ceklist per Bulan</div>
                    <div class="portal-chart-year-wrap">
                        <label class="portal-chart-year-label" for="chart-year-bulan">Tahun</label>
                        <select id="chart-year-bulan" class="portal-chart-year-select admin-filter-input" data-chart-key="bulan" aria-label="Tahun grafik ceklist per bulan">
                            @foreach($yearsAvailable as $y)
                                <option value="{{ $y }}" @selected((int) $chartYear === (int) $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="portal-chart-container">
                    <div class="portal-chart-loading"><span class="portal-chart-loading-spinner"></span></div>
                    <canvas id="chartBulan"></canvas>
                </div>
            </div>
            <div class="portal-chart-card portal-chart-card--ceklist-duo">
                <div class="portal-chart-head">
                    <div class="portal-chart-title">Ceklist per Kendaraan</div>
                    <div class="portal-chart-year-wrap">
                        <label class="portal-chart-year-label" for="chart-year-kendaraan">Tahun</label>
                        <select id="chart-year-kendaraan" class="portal-chart-year-select admin-filter-input" data-chart-key="kendaraan" aria-label="Tahun grafik ceklist per kendaraan">
                            @foreach($yearsAvailable as $y)
                                <option value="{{ $y }}" @selected((int) $chartYear === (int) $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="portal-chart-container">
                    <div class="portal-chart-loading"><span class="portal-chart-loading-spinner"></span></div>
                    <canvas id="chartKendaraan"></canvas>
                </div>
            </div>
        </div>





        {{-- ============================================================
             SECTION: DATABASE SHEET
        ============================================================ --}}
        <div class="portal-section" id="section-db">
            <div class="portal-section-header">
                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
                    <div class="portal-section-tabs" style="margin-bottom:0">
                        <button class="portal-section-tab active" data-section="db">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><ellipse cx="12" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="2"/><path d="M5 5V19C5 20.7 8.1 22 12 22C15.9 22 19 20.7 19 19V5" stroke="currentColor" stroke-width="2"/><path d="M5 12C5 13.7 8.1 15 12 15C15.9 15 19 13.7 19 12" stroke="currentColor" stroke-width="2"/></svg>
                            Database Sheet
                        </button>
                        <button class="portal-section-tab" data-section="foto">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="10" r="2" stroke="currentColor" stroke-width="2"/><path d="M21 16L16 11L7 20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Log Foto Fisik
                        </button>
                        <button class="portal-section-tab" data-section="pdf">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M7 3H14L19 8V21H7V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 3V8H19" stroke="currentColor" stroke-width="2"/><path d="M9 13H15M9 17H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Arsip PDF
                        </button>
                    </div>
                </div>
                @if(auth()->user()?->role === 'superadmin')
                <div class="portal-pemeriksaan-superadmin-actions" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                <button
                    type="button"
                    id="db-sync-btn"
                    data-export-url="{{ route('admin.portal-pemeriksaan.export') }}"
                    class="btn-export"
                    style="font-size:0.8rem;padding:7px 14px"
                >
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="currentColor" stroke-width="2"/><polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="2"/></svg>
                    Sinkronkan
                </button>
                </div>
                @endif
            </div>
            <div id="db-sync-alert" style="display:none;margin-bottom:12px;padding:10px 12px;border-radius:10px;font-size:.82rem;line-height:1.45"></div>

            {{-- Local filters --}}
            <div class="portal-local-filters" style="margin-bottom: 14px;">
                <div class="admin-search-wrap portal-search-full">
                    <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <input type="text" id="db-search" placeholder="Cari nopol, driver..." class="admin-search-input" autocomplete="off">
                </div>
                <input type="date" id="db-dari" class="admin-filter-input" title="Dari tanggal">
                <input type="date" id="db-sampai" class="admin-filter-input" title="Sampai tanggal">
                <select id="db-nopol" class="admin-filter-input">
                    <option value="">Semua Nopol</option>
                    @foreach($nopolList as $n)<option value="{{ $n }}">{{ $n }}</option>@endforeach
                </select>

                <x-admin-per-page-select id="db-perpage" name="per_page" :selected="$dbChecklists->perPage()" />
                <button type="button" class="btn btn-sm sppd-icon-btn admin-filter-reset" data-section-reset="db" title="Reset filter" aria-label="Reset filter" style="display: none"><i class="bi bi-arrow-clockwise"></i></button>
            </div>

            {{-- Sub-tabs --}}
            <div class="admin-tabs" style="margin-bottom:0">
                <button class="admin-tab active" data-db-tab="all">Semua Data</button>
                <button class="admin-tab" data-db-tab="exterior">Exterior</button>
                <button class="admin-tab" data-db-tab="interior">Interior</button>
                <button class="admin-tab" data-db-tab="mesin">Mesin</button>
            </div>

            <div id="db-loading" class="portal-loading" style="display:none">
                <span class="portal-loading-dot"></span><span class="portal-loading-dot"></span><span class="portal-loading-dot"></span>
            </div>

            {{-- ALL --}}
            <div class="db-tab-panel active" data-db-panel="all">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead id="db-all-thead"><tr>
                            <th>#</th>
                            <x-sortable-th key="tanggal" label="Tanggal" :activeSort="$dbActiveSort ?? null" :activeDir="$dbActiveDir ?? null" />
                            <x-sortable-th key="shift" label="Shift" :activeSort="$dbActiveSort ?? null" :activeDir="$dbActiveDir ?? null" />
                            <x-sortable-th key="nomor_kendaraan" label="Nopol" :activeSort="$dbActiveSort ?? null" :activeDir="$dbActiveDir ?? null" />
                            <th>Jenis</th>
                            <x-sortable-th key="driver_serah" label="Driver Serah" :activeSort="$dbActiveSort ?? null" :activeDir="$dbActiveDir ?? null" />
                            <x-sortable-th key="driver_terima" label="Driver Terima" :activeSort="$dbActiveSort ?? null" :activeDir="$dbActiveDir ?? null" />
                            <th>BBM</th>
                            <x-sortable-th key="km_awal" label="KM Awal" :activeSort="$dbActiveSort ?? null" :activeDir="$dbActiveDir ?? null" />
                            <x-sortable-th key="km_akhir" label="KM Akhir" :activeSort="$dbActiveSort ?? null" :activeDir="$dbActiveDir ?? null" />
                        </tr></thead>
                        <tbody id="db-tbody-all">
                            @forelse($dbChecklists as $c)
                            <tr>
                                <td>{{ ($dbChecklists->currentPage()-1)*$dbChecklists->perPage()+$loop->iteration }}</td>
                                <td>{{ $c->tanggal->format('d/m/Y') }}</td><td>{{ $c->shift }}</td>
                                <td><strong>{{ $c->nomor_kendaraan }}</strong></td><td>{{ $c->jenis_kendaraan }}</td>
                                <td>{{ $c->driver_serah }}</td><td>{{ $c->driver_terima }}</td>
                                <td>{{ $c->level_bbm }}%</td><td>{{ number_format($c->km_awal) }}</td><td>{{ number_format($c->km_akhir ?? 0) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="10" class="portal-empty">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @php
                $statusStyle = fn($v) => $v === 'ok' ? 'color:#16a34a' : (in_array($v,['no','tidak_ok'],true) ? 'color:#dc2626' : 'color:#334155');
                $statusLabel = fn($v) => $v === 'ok' ? 'OK' : (in_array($v,['no','tidak_ok'],true) ? 'NO' : strtoupper($v ?? '-'));
            @endphp

            {{-- EXTERIOR --}}
            <div class="db-tab-panel" data-db-panel="exterior" style="display:none">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Nopol</th><th>Tanggal</th><th>Body</th><th>Kaca</th><th>Spion</th><th>L.Utama</th><th>L.Sein</th><th>Ban</th><th>Velg</th><th>Wiper</th><th class="portal-db-aksi">Aksi</th></tr></thead>
                        <tbody id="db-tbody-exterior">
                            @foreach($dbChecklists as $c)
                            @if($c->exterior)
                            <tr>
                                <td><strong>{{ $c->nomor_kendaraan }}</strong></td><td>{{ $c->tanggal->format('d/m/Y') }}</td>
                                @foreach(['body_kendaraan','kaca','spion','lampu_utama','lampu_sein','ban','velg','wiper'] as $k)
                                <td style="font-weight:700;font-size:0.75rem;{{ $statusStyle($c->exterior->$k) }}">{{ $statusLabel($c->exterior->$k) }}</td>
                                @endforeach
                                <td class="portal-db-aksi">
                                    <button type="button" class="portal-db-detail-btn" data-checklist-id="{{ $c->id }}" title="Detail" aria-label="Detail pemeriksaan"><i class="bi bi-info-circle"></i></button>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- INTERIOR --}}
            <div class="db-tab-panel" data-db-panel="interior" style="display:none">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Nopol</th><th>Tanggal</th><th>Jok</th><th>Dashboard</th><th>AC</th><th>Sabuk</th><th>Audio</th><th>Kebersihan</th><th class="portal-db-aksi">Aksi</th></tr></thead>
                        <tbody id="db-tbody-interior">
                            @foreach($dbChecklists as $c)
                            @if($c->interior)
                            <tr>
                                <td><strong>{{ $c->nomor_kendaraan }}</strong></td><td>{{ $c->tanggal->format('d/m/Y') }}</td>
                                @foreach(['jok','dashboard','ac','sabuk_pengaman','audio','kebersihan'] as $k)
                                <td style="font-weight:700;font-size:0.75rem;{{ $statusStyle($c->interior->$k) }}">{{ $statusLabel($c->interior->$k) }}</td>
                                @endforeach
                                <td class="portal-db-aksi">
                                    <button type="button" class="portal-db-detail-btn" data-checklist-id="{{ $c->id }}" title="Detail" aria-label="Detail pemeriksaan"><i class="bi bi-info-circle"></i></button>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- MESIN --}}
            <div class="db-tab-panel" data-db-panel="mesin" style="display:none">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Nopol</th><th>Tanggal</th><th>Mesin</th><th>Oli</th><th>Radiator</th><th>Rem</th><th>Kopling</th><th>Transmisi</th><th>Indikator</th><th class="portal-db-aksi">Aksi</th></tr></thead>
                        <tbody id="db-tbody-mesin">
                            @foreach($dbChecklists as $c)
                            @if($c->mesin)
                            <tr>
                                <td><strong>{{ $c->nomor_kendaraan }}</strong></td><td>{{ $c->tanggal->format('d/m/Y') }}</td>
                                @foreach(['mesin','oli','radiator','rem','kopling','transmisi','indikator'] as $k)
                                <td style="font-weight:700;font-size:0.75rem;{{ $statusStyle($c->mesin->$k) }}">{{ $statusLabel($c->mesin->$k) }}</td>
                                @endforeach
                                <td class="portal-db-aksi">
                                    <button type="button" class="portal-db-detail-btn" data-checklist-id="{{ $c->id }}" title="Detail" aria-label="Detail pemeriksaan"><i class="bi bi-info-circle"></i></button>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="db-pagination" class="tbl-pagination-mount"></div>
        </div>

        {{-- ============================================================
             SECTION: LOG FOTO FISIK
        ============================================================ --}}
        <div class="portal-section" id="section-foto" style="display:none">
            <div class="portal-section-header">
                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
                    <div class="portal-section-tabs" style="margin-bottom:0">
                        <button class="portal-section-tab active" data-section="db">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><ellipse cx="12" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="2"/><path d="M5 5V19C5 20.7 8.1 22 12 22C15.9 22 19 20.7 19 19V5" stroke="currentColor" stroke-width="2"/><path d="M5 12C5 13.7 8.1 15 12 15C15.9 15 19 13.7 19 12" stroke="currentColor" stroke-width="2"/></svg>
                            Database Sheet
                        </button>
                        <button class="portal-section-tab" data-section="foto">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="10" r="2" stroke="currentColor" stroke-width="2"/><path d="M21 16L16 11L7 20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Log Foto Fisik
                        </button>
                        <button class="portal-section-tab" data-section="pdf">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M7 3H14L19 8V21H7V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 3V8H19" stroke="currentColor" stroke-width="2"/><path d="M9 13H15M9 17H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Arsip PDF
                        </button>
                    </div>
                </div>
            </div>

            {{-- Local filters --}}
            <div class="portal-local-filters" style="margin-bottom: 14px;">
                <div class="admin-search-wrap portal-search-full">
                    <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <input type="text" id="foto-search" placeholder="Cari nopol, driver..." class="admin-search-input" autocomplete="off">
                </div>
                <input type="date" id="foto-dari" class="admin-filter-input" title="Dari tanggal">
                <input type="date" id="foto-sampai" class="admin-filter-input" title="Sampai tanggal">
                <select id="foto-nopol" class="admin-filter-input">
                    <option value="">Semua Nopol</option>
                    @foreach($nopolList as $n)<option value="{{ $n }}">{{ $n }}</option>@endforeach
                </select>
                <x-admin-per-page-select id="foto-perpage" name="per_page" :selected="$fotoChecklists->perPage()" />
                <button type="button" class="btn btn-sm sppd-icon-btn admin-filter-reset" data-section-reset="foto" title="Reset filter" aria-label="Reset filter" style="display: none"><i class="bi bi-arrow-clockwise"></i></button>
            </div>

            {{-- Sub-tabs --}}
            <div class="admin-tabs" style="margin-bottom:0">
                <button class="admin-tab active" data-foto-tab="exterior">Eksterior</button>
                <button class="admin-tab" data-foto-tab="interior">Interior</button>
                <button class="admin-tab" data-foto-tab="mesin">Mesin</button>
                <button class="admin-tab" data-foto-tab="bbm">BBM</button>
            </div>

            <div id="foto-loading" class="portal-loading" style="display:none">
                <span class="portal-loading-dot"></span><span class="portal-loading-dot"></span><span class="portal-loading-dot"></span>
            </div>

            @php
                $baseUrl = url('/');
                $resolvePhotoUrl = function (?string $path) use ($baseUrl) {
                    if (!$path) return null;
                    if (str_starts_with($path, 'http')) return $path;
                    if (str_starts_with($path, '/storage/')) return $baseUrl . $path;
                    if (str_starts_with($path, 'storage/')) return $baseUrl . '/' . $path;
                    return $baseUrl . '/storage/' . ltrim($path, '/');
                };
            @endphp

            <div class="foto-tab-panel active" data-foto-panel="exterior">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Waktu</th><th>Unit</th><th>Foto</th></tr></thead>
                        <tbody id="foto-tbody-exterior">
                            @php $hasExt = false; @endphp
                            @foreach($fotoChecklists as $c)
                                @if($c->exterior && ($c->exterior->foto_depan || $c->exterior->foto_kanan || $c->exterior->foto_kiri || $c->exterior->foto_belakang))
                                @php $hasExt = true; @endphp
                                <tr>
                                    <td>{{ $c->tanggal?->format('d/m/Y') }} {{ $c->jam_serah_terima ?? '' }}</td>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                                    <td><div class="portal-thumb-row">
                                        @foreach(['foto_depan'=>'Depan','foto_kanan'=>'Kanan','foto_kiri'=>'Kiri','foto_belakang'=>'Belakang'] as $field=>$label)
                                            @if($c->exterior->$field)
                                                <a href="{{ $resolvePhotoUrl($c->exterior->$field) }}" target="_blank" rel="noopener" title="{{ $label }}">
                                                    <img src="{{ $resolvePhotoUrl($c->exterior->$field) }}" alt="{{ $label }}" loading="lazy" class="portal-thumb">
                                                </a>
                                            @endif
                                        @endforeach
                                    </div></td>
                                </tr>
                                @endif
                            @endforeach
                            @unless($hasExt)<tr><td colspan="3" class="portal-empty">Belum ada foto eksterior.</td></tr>@endunless
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="foto-tab-panel" data-foto-panel="interior" style="display:none">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Waktu</th><th>Unit</th><th>Foto</th></tr></thead>
                        <tbody id="foto-tbody-interior">
                            @php $hasInt = false; @endphp
                            @foreach($fotoChecklists as $c)
                                @if($c->interior && ($c->interior->foto_1 || $c->interior->foto_2 || $c->interior->foto_3))
                                @php $hasInt = true; @endphp
                                <tr>
                                    <td>{{ $c->tanggal?->format('d/m/Y') }} {{ $c->jam_serah_terima ?? '' }}</td>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                                    <td><div class="portal-thumb-row">
                                        @for($i=1;$i<=3;$i++) @php $f="foto_{$i}"; @endphp
                                            @if($c->interior->$f)
                                                <a href="{{ $resolvePhotoUrl($c->interior->$f) }}" target="_blank" rel="noopener">
                                                    <img src="{{ $resolvePhotoUrl($c->interior->$f) }}" alt="Interior {{ $i }}" loading="lazy" class="portal-thumb">
                                                </a>
                                            @endif
                                        @endfor
                                    </div></td>
                                </tr>
                                @endif
                            @endforeach
                            @unless($hasInt)<tr><td colspan="3" class="portal-empty">Belum ada foto interior.</td></tr>@endunless
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="foto-tab-panel" data-foto-panel="mesin" style="display:none">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Waktu</th><th>Unit</th><th>Foto</th></tr></thead>
                        <tbody id="foto-tbody-mesin">
                            @php $hasMesin = false; @endphp
                            @foreach($fotoChecklists as $c)
                                @if($c->mesin && ($c->mesin->foto_1 || $c->mesin->foto_2 || $c->mesin->foto_3))
                                @php $hasMesin = true; @endphp
                                <tr>
                                    <td>{{ $c->tanggal?->format('d/m/Y') }} {{ $c->jam_serah_terima ?? '' }}</td>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                                    <td><div class="portal-thumb-row">
                                        @for($i=1;$i<=3;$i++) @php $f="foto_{$i}"; @endphp
                                            @if($c->mesin->$f)
                                                <a href="{{ $resolvePhotoUrl($c->mesin->$f) }}" target="_blank" rel="noopener">
                                                    <img src="{{ $resolvePhotoUrl($c->mesin->$f) }}" alt="Mesin {{ $i }}" loading="lazy" class="portal-thumb">
                                                </a>
                                            @endif
                                        @endfor
                                    </div></td>
                                </tr>
                                @endif
                            @endforeach
                            @unless($hasMesin)<tr><td colspan="3" class="portal-empty">Belum ada foto mesin.</td></tr>@endunless
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="foto-tab-panel" data-foto-panel="bbm" style="display:none">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Waktu</th><th>Unit</th><th>Foto</th></tr></thead>
                        <tbody id="foto-tbody-bbm">
                            @php $hasBbm = false; @endphp
                            @foreach($fotoChecklists as $c)
                                @if($c->foto_bbm_dashboard)
                                @php $hasBbm = true; @endphp
                                <tr>
                                    <td>{{ $c->tanggal?->format('d/m/Y') }} {{ $c->jam_serah_terima ?? '' }}</td>
                                    <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                                    <td>
                                        <a href="{{ $resolvePhotoUrl($c->foto_bbm_dashboard) }}" target="_blank" rel="noopener">
                                            <img src="{{ $resolvePhotoUrl($c->foto_bbm_dashboard) }}" alt="BBM" loading="lazy" class="portal-thumb">
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                            @unless($hasBbm)<tr><td colspan="3" class="portal-empty">Belum ada foto BBM.</td></tr>@endunless
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="foto-pagination" class="tbl-pagination-mount"></div>
        </div>

        {{-- ============================================================
             SECTION: ARSIP PDF
        ============================================================ --}}
        <div class="portal-section" id="section-pdf" style="display:none">
            <div class="portal-section-header">
                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
                    <div class="portal-section-tabs" style="margin-bottom:0">
                        <button class="portal-section-tab active" data-section="db">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><ellipse cx="12" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="2"/><path d="M5 5V19C5 20.7 8.1 22 12 22C15.9 22 19 20.7 19 19V5" stroke="currentColor" stroke-width="2"/><path d="M5 12C5 13.7 8.1 15 12 15C15.9 15 19 13.7 19 12" stroke="currentColor" stroke-width="2"/></svg>
                            Database Sheet
                        </button>
                        <button class="portal-section-tab" data-section="foto">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="10" r="2" stroke="currentColor" stroke-width="2"/><path d="M21 16L16 11L7 20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Log Foto Fisik
                        </button>
                        <button class="portal-section-tab" data-section="pdf">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M7 3H14L19 8V21H7V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 3V8H19" stroke="currentColor" stroke-width="2"/><path d="M9 13H15M9 17H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Arsip PDF
                        </button>
                    </div>
                </div>
            </div>

            {{-- Local filters --}}
            <div class="portal-local-filters" style="margin-bottom: 14px;">
                <div class="admin-search-wrap portal-search-full">
                    <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <input type="text" id="pdf-search" placeholder="Cari nopol, driver..." class="admin-search-input" autocomplete="off">
                </div>
                <input type="date" id="pdf-dari" class="admin-filter-input" title="Dari tanggal">
                <input type="date" id="pdf-sampai" class="admin-filter-input" title="Sampai tanggal">
                <select id="pdf-nopol" class="admin-filter-input">
                    <option value="">Semua Nopol</option>
                    @foreach($nopolList as $n)<option value="{{ $n }}">{{ $n }}</option>@endforeach
                </select>

                <x-admin-per-page-select id="pdf-perpage" name="per_page" :selected="$pdfChecklists->perPage()" />
                <button type="button" class="btn btn-sm sppd-icon-btn admin-filter-reset" data-section-reset="pdf" title="Reset filter" aria-label="Reset filter" style="display: none"><i class="bi bi-arrow-clockwise"></i></button>
            </div>

            <div id="pdf-loading" class="portal-loading" style="display:none">
                <span class="portal-loading-dot"></span><span class="portal-loading-dot"></span><span class="portal-loading-dot"></span>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead id="pdf-thead"><tr>
                        <th>#</th>
                        <x-sortable-th key="tanggal" label="Tanggal" :activeSort="$pdfActiveSort ?? null" :activeDir="$pdfActiveDir ?? null" />
                        <x-sortable-th key="nomor_kendaraan" label="Nopol" :activeSort="$pdfActiveSort ?? null" :activeDir="$pdfActiveDir ?? null" />
                        <x-sortable-th key="driver_serah" label="Driver Serah" :activeSort="$pdfActiveSort ?? null" :activeDir="$pdfActiveDir ?? null" />
                        <x-sortable-th key="driver_terima" label="Driver Terima" :activeSort="$pdfActiveSort ?? null" :activeDir="$pdfActiveDir ?? null" />
                        <x-sortable-th key="shift" label="Shift" :activeSort="$pdfActiveSort ?? null" :activeDir="$pdfActiveDir ?? null" />
                        <th>Aksi</th>
                    </tr></thead>
                    <tbody id="pdf-tbody">
                        @forelse($pdfChecklists as $c)
                        @php
                            $resolvePdfUrl = function (?string $path) use ($baseUrl) {
                                if (!$path) return null;
                                if (str_starts_with($path, 'http')) return $path;
                                if (str_starts_with($path, '/storage/')) return $baseUrl . $path;
                                if (str_starts_with($path, 'storage/')) return $baseUrl . '/' . $path;
                                return $baseUrl . '/storage/' . ltrim($path, '/');
                            };
                        @endphp
                        <tr>
                            <td>{{ ($pdfChecklists->currentPage()-1)*$pdfChecklists->perPage()+$loop->iteration }}</td>
                            <td>{{ $c->tanggal->format('d/m/Y') }}</td>
                            <td><strong>{{ $c->nomor_kendaraan }}</strong></td>
                            <td>{{ $c->driver_serah }}</td>
                            <td>{{ $c->driver_terima }}</td>
                            <td>{{ $c->shift }}</td>
                            <td>
                                @if($c->pdf_path)
                                    <a href="{{ $resolvePdfUrl($c->pdf_path) }}" target="_blank" class="btn-view-pdf">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/></svg>
                                        View PDF
                                    </a>
                                @else
                                    <span style="color:#94a3b8;font-size:.75rem">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="portal-empty">Belum ada laporan PDF.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="pdf-pagination" class="tbl-pagination-mount"></div>
        </div>
    </div>{{-- end portal-wrapper --}}
</div>{{-- end admin-shell --}}
@endsection

@section('modals')
    @if($canAccessDatabase ?? false)
    <div id="portal-checklist-modal" class="modal-overlay" style="display:none" aria-hidden="true">
        <div class="modal-box portal-checklist-modal-box" role="dialog" aria-modal="true" aria-labelledby="portal-checklist-modal-title">
            <h3 id="portal-checklist-modal-title">Detail pemeriksaan</h3>
            <div id="portal-checklist-modal-body"></div>
            <div class="portal-checklist-modal-actions">
                <button type="button" class="portal-local-reset" id="portal-checklist-modal-close">Tutup</button>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('scripts')
    <script>
    (function () {
        'use strict';

        /* ================================================================
        CONFIG & STATE
        ================================================================ */
        const BASE_URL   = '{{ url("/") }}';
        const CHARTS_API_URL = @json(route('api.admin.portal.charts'));
        const DEFAULT_CHART_YEAR = {{ (int) $chartYear }};
        const CHART_DATA = @json($chartData);
        const CAN_ACCESS_DATABASE = @json($canAccessDatabase);
        const INIT_PAGINATION = CAN_ACCESS_DATABASE
            ? {
                db: @json($dbPaginationHtml ?? ''),
                foto: @json($fotoPaginationHtml ?? ''),
                pdf: @json($pdfPaginationHtml ?? ''),
            }
            : null;
        const PORTAL_API_PATHS = {
            db: '/api/admin/portal/database-sheet',
            foto: '/api/admin/portal/log-foto',
            pdf: '/api/admin/portal/arsip-pdf',
        };

        let dbPage   = 1, dbPerPage   = {{ (int) $dbChecklists->perPage() }};
        let fotoPage = 1, fotoPerPage = {{ (int) $fotoChecklists->perPage() }};
        let pdfPage  = 1, pdfPerPage  = {{ (int) $pdfChecklists->perPage() }};

        /* ================================================================
        CHARTS — dark-mode aware, rebuilds on theme toggle
        ================================================================ */
        const YELLOW = '#D4AF37';
        const GREEN  = '#16a34a';
        const RED    = '#dc2626';
        const SLATE  = '#94a3b8';
        const INDIGO = '#818cf8';

        let _chartInstances = {};
        let _chartDataCache = { [DEFAULT_CHART_YEAR]: CHART_DATA };

        function _isDarkTheme() {
            return document.documentElement.classList.contains('dark')
                || document.body.classList.contains('dark');
        }

        function _chartAccentColor() {
            return _isDarkTheme() ? '#D4AF37' : '#0e2a52';
        }

        function _chartAccentFill() {
            return _isDarkTheme() ? 'rgba(212, 175, 55, 0.15)' : 'rgba(10, 35, 66, 0.08)';
        }

        function _getChartYear(key) {
            const el = document.querySelector('.portal-chart-year-select[data-chart-key="' + key + '"]');
            const year = el ? parseInt(el.value, 10) : DEFAULT_CHART_YEAR;
            return Number.isNaN(year) ? DEFAULT_CHART_YEAR : year;
        }

        function _chartContainerForKey(key) {
            const map = {
                bbm: '#chartBbm',
                shift: '#chartShift',
                bulan: '#chartBulan',
                kendaraan: '#chartKendaraan',
            };
            const canvas = document.querySelector(map[key]);
            return canvas ? canvas.closest('.portal-chart-container') : null;
        }

        function _setChartLoading(key, loading) {
            const container = _chartContainerForKey(key);
            if (!container) return;
            if (loading) container.classList.remove('is-ready');
            else container.classList.add('is-ready');
        }

        async function _fetchChartData(year) {
            if (_chartDataCache[year]) return _chartDataCache[year];
            const url = new URL(CHARTS_API_URL, window.location.origin);
            url.searchParams.set('year', String(year));
            const res = await fetch(url.toString(), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) throw new Error('Gagal memuat data grafik');
            const data = await res.json();
            _chartDataCache[year] = data;
            return data;
        }

        function _destroyChart(key) {
            if (_chartInstances[key]) {
                try { _chartInstances[key].destroy(); } catch (_) {}
                delete _chartInstances[key];
            }
        }

        function _chartTheme() {
            const dark = _isDarkTheme();
            return {
                dark,
                accent: _chartAccentColor(),
                blue: dark ? '#1a3a72' : '#0e2a52',
                grid: dark ? 'rgba(200,218,255,0.1)' : 'rgba(0,0,0,0.08)',
                tick: dark ? 'rgba(200,218,255,0.65)' : '#64748b',
                lgnd: dark ? 'rgba(200,218,255,0.75)' : '#475569',
                bdr: dark ? 'rgba(200,218,255,0.12)' : 'rgba(255,255,255,0.8)',
            };
        }

        function _buildSingleChart(key, data) {
            const t = _chartTheme();
            const commonOpts = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
            };
            const xyScales = {
                y: { beginAtZero: true, ticks: { stepSize: 1, color: t.tick }, grid: { color: t.grid } },
                x: { ticks: { maxRotation: 45, font: { size: 11 }, color: t.tick }, grid: { color: t.grid } },
            };

            if (key === 'bulan') {
                const ctx = document.getElementById('chartBulan');
                if (!ctx) return;
                _chartInstances.bulan = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.perBulan.labels,
                        datasets: [{
                            data: data.perBulan.data,
                            borderColor: t.accent,
                            backgroundColor: _chartAccentFill(),
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointBackgroundColor: t.accent,
                        }],
                    },
                    options: { ...commonOpts, scales: xyScales },
                });
                return;
            }

            if (key === 'kendaraan') {
                const ctx = document.getElementById('chartKendaraan');
                if (!ctx) return;
                _chartInstances.kendaraan = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.perKendaraan.labels,
                        datasets: [{
                            data: data.perKendaraan.data,
                            backgroundColor: t.accent,
                            borderRadius: 4,
                        }],
                    },
                    options: {
                        ...commonOpts,
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1, color: t.tick }, grid: { color: t.grid } },
                            x: { ticks: { maxRotation: 45, font: { size: 10 }, color: t.tick }, grid: { color: t.grid } },
                        },
                    },
                });
                return;
            }

            if (key === 'shift') {
                const ctx = document.getElementById('chartShift');
                if (!ctx) return;
                _chartInstances.shift = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: data.perShift.labels,
                        datasets: [{
                            data: data.perShift.data,
                            backgroundColor: [t.blue, YELLOW, SLATE, GREEN, INDIGO],
                            borderWidth: 2,
                            borderColor: t.bdr,
                        }],
                    },
                    options: {
                        ...commonOpts,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: { font: { size: 11 }, padding: 10, color: t.lgnd },
                            },
                        },
                        cutout: '58%',
                    },
                });
                return;
            }

            if (key === 'bbm') {
                const ctx = document.getElementById('chartBbm');
                if (!ctx) return;
                _chartInstances.bbm = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.bbmPerKendaraan.labels,
                        datasets: [{
                            data: data.bbmPerKendaraan.data,
                            backgroundColor: data.bbmPerKendaraan.data.map(v =>
                                v >= 70 ? GREEN : v >= 40 ? YELLOW : RED
                            ),
                            borderRadius: 4,
                        }],
                    },
                    options: {
                        ...commonOpts,
                        indexAxis: 'y',
                        scales: {
                            x: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: { size: 11 }, color: t.tick }, grid: { color: t.grid } },
                            y: { ticks: { font: { size: 11 }, color: t.tick }, grid: { color: t.grid } },
                        },
                    },
                });
            }
        }

        async function _refreshChart(key) {
            const year = _getChartYear(key);
            _setChartLoading(key, true);
            try {
                const data = await _fetchChartData(year);
                _destroyChart(key);
                _buildSingleChart(key, data);
            } catch (_) {
                /* keep previous chart if fetch fails */
            } finally {
                requestAnimationFrame(function () { _setChartLoading(key, false); });
            }
        }

        function _buildCharts() {
            Object.keys(_chartInstances).forEach(_destroyChart);
            _chartInstances = {};
            ['bbm', 'shift', 'bulan', 'kendaraan'].forEach(function (key) {
                const year = _getChartYear(key);
                const data = _chartDataCache[year] || CHART_DATA;
                _buildSingleChart(key, data);
            });
        }

        // Build charts immediately — data is already server-rendered, no need to defer
        (function () {
            if (typeof Chart === 'undefined') {
                // Chart.js CDN not yet loaded — wait for it
                const waitForChart = setInterval(function () {
                    if (typeof Chart !== 'undefined') {
                        clearInterval(waitForChart);
                        _buildChartsAndReveal();
                    }
                }, 30);
            } else {
                _buildChartsAndReveal();
            }
        })();

        function _buildChartsAndReveal() {
            _buildCharts();
            requestAnimationFrame(function () {
                document.querySelectorAll('#portal-charts-pemeriksaan .portal-chart-container').forEach(function (c) {
                    c.classList.add('is-ready');
                });
            });
        }

        document.querySelectorAll('.portal-chart-year-select').forEach(function (sel) {
            sel.addEventListener('change', function () {
                const key = sel.dataset.chartKey;
                if (key) _refreshChart(key);
            });
        });

        /* Rebuild charts on theme toggle — delegated once at document level */
        if (!document._portalPemeriksaanThemeBound) {
            document._portalPemeriksaanThemeBound = true;
            document.addEventListener('click', function (e) {
                if (!e.target.closest('#dash-theme-toggle')) return;
                if (!document.getElementById('portal-charts-pemeriksaan')) return;
                requestAnimationFrame(function () {
                    requestAnimationFrame(_buildChartsAndReveal);
                });
            });
        }

        /* Register chart destroy with central Turbo cleanup registry */
        if (typeof window.registerTurboCleanup === 'function') {
            window.registerTurboCleanup(function () {
                Object.values(_chartInstances).forEach(function (c) {
                    try { c.destroy(); } catch (_) {}
                });
                _chartInstances = {};
            });
        }

        if (!CAN_ACCESS_DATABASE) return;

        /* ================================================================
        SECTION TABS
        ================================================================ */
        document.querySelectorAll('.portal-section-tab').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.section;
                document.querySelectorAll('.portal-section-tab').forEach(b => {
                    b.classList.toggle('active', b.dataset.section === target);
                });
                document.querySelectorAll('.portal-section').forEach(s => {
                    const id = s.id.replace('section-', '');
                    s.style.display = id === target ? '' : 'none';
                });
            });
        });

        /* ================================================================
        DB SUB-TABS
        ================================================================ */
        document.querySelectorAll('[data-db-tab]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('[data-db-tab]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const t = btn.dataset.dbTab;
                document.querySelectorAll('[data-db-panel]').forEach(p => {
                    p.style.display = p.dataset.dbPanel === t ? '' : 'none';
                });
            });
        });

        /* ================================================================
        FOTO SUB-TABS
        ================================================================ */
        document.querySelectorAll('[data-foto-tab]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('[data-foto-tab]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const t = btn.dataset.fotoTab;
                document.querySelectorAll('[data-foto-panel]').forEach(p => {
                    p.style.display = p.dataset.fotoPanel === t ? '' : 'none';
                });
            });
        });

        /* ================================================================
        HELPERS
        ================================================================ */
        function buildParams(obj = {}) {
            return new URLSearchParams(
                Object.fromEntries(Object.entries(obj).filter(([, v]) => v !== '' && v != null))
            ).toString();
        }

        function showLoading(id) { const el = document.getElementById(id); if (el) el.style.display = 'flex'; }
        function hideLoading(id) { const el = document.getElementById(id); if (el) el.style.display = 'none'; }

        function scrollToSection(sectionId) {
            const el = document.getElementById(sectionId);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function statusColor(v) {
            if (v === 'ok') return '#16a34a';
            if (v === 'no' || v === 'tidak_ok') return '#dc2626';
            return '#334155';
        }
        function statusLabel(v) {
            if (v === 'ok') return 'OK';
            if (v === 'no' || v === 'tidak_ok') return 'NO';
            return (v ?? '-').toUpperCase();
        }

        function escHtml(s) {
            if (s == null || s === '') return '';
            const d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }

        function renderChecklistDetailModal(d) {
            const m = d.meta || {};
            const row = (label, v) => `<dt>${escHtml(label)}</dt><dd>${escHtml(v != null && v !== '' ? String(v) : '—')}</dd>`;
            let html = '<dl class="portal-detail-dl">';
            html += row('Nopol', m.nomor_kendaraan);
            html += row('Tanggal', m.tanggal);
            html += row('Shift', m.shift);
            html += row('Jenis kendaraan', m.jenis_kendaraan);
            html += row('Driver serah', m.driver_serah);
            html += row('Driver terima', m.driver_terima);
            html += row('Jam serah terima', m.jam_serah_terima);
            html += row('Level BBM', m.level_bbm != null && m.level_bbm !== '' ? String(m.level_bbm) + '%' : null);
            html += row('KM awal', m.km_awal != null && m.km_awal !== '' ? String(m.km_awal) : null);
            html += row('KM akhir', m.km_akhir != null && m.km_akhir !== '' ? String(m.km_akhir) : null);
            html += '</dl>';

            const section = (key, title) => {
                const list = d[key];
                html += `<h4 class="portal-detail-section-title">${escHtml(title)}</h4>`;
                if (!list || !list.length) {
                    html += '<p class="portal-empty" style="padding:8px 0">Tidak ada data.</p>';
                    return;
                }
                html += '<div class="admin-table-wrap"><table class="admin-table portal-detail-table"><thead><tr><th>Bagian</th><th>Status</th><th>Keterangan</th></tr></thead><tbody>';
                list.forEach(r => {
                    const col = statusColor(r.status);
                    const lab = statusLabel(r.status);
                    const ket = (r.keterangan != null && r.keterangan !== '') ? r.keterangan : '—';
                    html += `<tr><td>${escHtml(r.label)}</td><td style="font-weight:700;color:${col}">${escHtml(lab)}</td><td>${escHtml(ket)}</td></tr>`;
                });
                html += '</tbody></table></div>';
            };
            section('exterior', 'Exterior');
            section('interior', 'Interior');
            section('mesin', 'Mesin & operasional');
            if (m.catatan_khusus) {
                html += `<div class="portal-detail-catatan"><strong>Catatan khusus</strong><br>${escHtml(m.catatan_khusus)}</div>`;
            }
            return html;
        }

        function refreshPortalOverlayOverflow() {
            const cm = document.getElementById('portal-checklist-modal');
            const checklistOpen = cm && cm.style.display === 'flex';
            document.body.style.overflow = checklistOpen ? 'hidden' : '';
        }

        async function openPortalChecklistDetail(id) {
            const modal = document.getElementById('portal-checklist-modal');
            const body = document.getElementById('portal-checklist-modal-body');
            if (!modal || !body) return;
            body.innerHTML = '<p style="padding:12px">Memuat…</p>';
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            refreshPortalOverlayOverflow();
            try {
                const r = await fetch(`${BASE_URL}/api/admin/portal/checklist/${encodeURIComponent(id)}`);
                if (!r.ok) throw new Error('fail');
                const data = await r.json();
                body.innerHTML = renderChecklistDetailModal(data);
            } catch {
                body.innerHTML = '<p class="portal-empty">Gagal memuat detail.</p>';
            }
        }

        function closePortalChecklistModal() {
            const modal = document.getElementById('portal-checklist-modal');
            if (!modal) return;
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            refreshPortalOverlayOverflow();
        }

        function debounce(fn, ms = 380) {
            let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
        }

        function mountPortalPagination(section, html) {
            const el = document.getElementById(section + '-pagination');
            if (!el) return;
            if (window.AdminPagination) {
                window.AdminPagination.mountPagination(el, html || '');
                if (!el.dataset.paginationBound) {
                    el.dataset.paginationBound = '1';
                    window.AdminPagination.bindPaginationLinks(el, (url) => {
                        const page = parseInt(url.searchParams.get('page') || '1', 10);
                        if (section === 'db') { dbPage = page; fetchDb(true); }
                        else if (section === 'foto') { fotoPage = page; fetchFoto(true); }
                        else { pdfPage = page; fetchPdf(true); }
                    }, { pathname: new URL(BASE_URL + PORTAL_API_PATHS[section]).pathname });
                }
            } else {
                const interval = setInterval(() => {
                    if (window.AdminPagination) {
                        clearInterval(interval);
                        mountPortalPagination(section, html);
                    }
                }, 30);
            }
        }

        /* ================================================================
        ABORT CONTROLLERS — cancel stale requests on rapid filter changes
        ================================================================ */
        let _abortDb   = null;
        let _abortFoto = null;
        let _abortPdf  = null;

        let dbSort = '{{ $dbActiveSort ?? "" }}', dbDir = '{{ $dbActiveDir ?? "" }}', fotoSort = '', fotoDir = '', pdfSort = '{{ $pdfActiveSort ?? "" }}', pdfDir = '{{ $pdfActiveDir ?? "" }}';

        /* ================================================================
        DATABASE SHEET AJAX
        ================================================================ */
        function getDbParams() {
            const p = {
                search:         document.getElementById('db-search')?.value ?? '',
                tanggal_dari:   document.getElementById('db-dari')?.value ?? '',
                tanggal_sampai: document.getElementById('db-sampai')?.value ?? '',
                nopol:          document.getElementById('db-nopol')?.value ?? '',
                per_page:       dbPerPage,
                page:           dbPage,
            };
            if (dbSort) { p.sort = dbSort; p.dir = dbDir; }
            return p;
        }

        async function fetchDb(scroll = false) {
            _abortDb?.abort();
            _abortDb = new AbortController();
            showLoading('db-loading');
            const q = buildParams(getDbParams());
            try {
                const r = await fetch(`${BASE_URL}/api/admin/portal/database-sheet?${q}`, { signal: _abortDb.signal });
                const json = await r.json();
                renderDbAll(json);
                renderDbExterior(json);
                renderDbInterior(json);
                renderDbMesin(json);
                mountPortalPagination('db', json.pagination_html);
                if (window.AdminTableSort) window.AdminTableSort.syncAria(document.getElementById('db-all-thead'), json.sort ?? null, json.dir ?? null);
                if (scroll) scrollToSection('section-db');
            } catch (e) {
                if (e.name !== 'AbortError') console.warn('fetchDb error', e);
            } finally { hideLoading('db-loading'); updateSectionFilterChrome('db'); }
        }

        function renderDbAll(json) {
            const tbody = document.getElementById('db-tbody-all');
            if (!tbody) return;
            const off = (json.current_page - 1) * json.per_page;
            tbody.innerHTML = json.data.length
                ? json.data.map((c, i) => `<tr>
                    <td>${off + i + 1}</td>
                    <td>${c.tanggal ?? '-'}</td><td>${c.shift ?? '-'}</td>
                    <td><strong>${c.nomor_kendaraan}</strong></td><td>${c.jenis_kendaraan ?? '-'}</td>
                    <td>${c.driver_serah ?? '-'}</td><td>${c.driver_terima ?? '-'}</td>
                    <td>${c.level_bbm ?? '-'}%</td><td>${c.km_awal ?? '-'}</td><td>${c.km_akhir ?? '-'}</td>
                </tr>`).join('')
                : '<tr><td colspan="10" class="portal-empty">Tidak ada data.</td></tr>';
        }

        function renderDbExterior(json) {
            const tbody = document.getElementById('db-tbody-exterior');
            if (!tbody) return;
            const rows = json.data.filter(c => c.exterior);
            const keys = ['body_kendaraan','kaca','spion','lampu_utama','lampu_sein','ban','velg','wiper'];
            tbody.innerHTML = rows.length
                ? rows.map(c => `<tr><td><strong>${c.nomor_kendaraan}</strong></td><td>${c.tanggal ?? '-'}</td>
                    ${keys.map(k => `<td style="font-weight:700;font-size:.75rem;color:${statusColor(c.exterior[k])}">${statusLabel(c.exterior[k])}</td>`).join('')}
                    <td class="portal-db-aksi"><button type="button" class="portal-db-detail-btn" data-checklist-id="${c.id}" title="Detail" aria-label="Detail pemeriksaan"><i class="bi bi-info-circle"></i></button></td>
                </tr>`).join('')
                : '<tr><td colspan="11" class="portal-empty">Tidak ada data.</td></tr>';
        }

        function renderDbInterior(json) {
            const tbody = document.getElementById('db-tbody-interior');
            if (!tbody) return;
            const rows = json.data.filter(c => c.interior);
            const keys = ['jok','dashboard','ac','sabuk_pengaman','audio','kebersihan'];
            tbody.innerHTML = rows.length
                ? rows.map(c => `<tr><td><strong>${c.nomor_kendaraan}</strong></td><td>${c.tanggal ?? '-'}</td>
                    ${keys.map(k => `<td style="font-weight:700;font-size:.75rem;color:${statusColor(c.interior[k])}">${statusLabel(c.interior[k])}</td>`).join('')}
                    <td class="portal-db-aksi"><button type="button" class="portal-db-detail-btn" data-checklist-id="${c.id}" title="Detail" aria-label="Detail pemeriksaan"><i class="bi bi-info-circle"></i></button></td>
                </tr>`).join('')
                : '<tr><td colspan="9" class="portal-empty">Tidak ada data.</td></tr>';
        }

        function renderDbMesin(json) {
            const tbody = document.getElementById('db-tbody-mesin');
            if (!tbody) return;
            const rows = json.data.filter(c => c.mesin);
            const keys = ['mesin','oli','radiator','rem','kopling','transmisi','indikator'];
            tbody.innerHTML = rows.length
                ? rows.map(c => `<tr><td><strong>${c.nomor_kendaraan}</strong></td><td>${c.tanggal ?? '-'}</td>
                    ${keys.map(k => `<td style="font-weight:700;font-size:.75rem;color:${statusColor(c.mesin[k])}">${statusLabel(c.mesin[k])}</td>`).join('')}
                    <td class="portal-db-aksi"><button type="button" class="portal-db-detail-btn" data-checklist-id="${c.id}" title="Detail" aria-label="Detail pemeriksaan"><i class="bi bi-info-circle"></i></button></td>
                </tr>`).join('')
                : '<tr><td colspan="10" class="portal-empty">Tidak ada data.</td></tr>';
        }

        /* ================================================================
        LOG FOTO AJAX
        ================================================================ */
        function getFotoParams() {
            const p = {
                search:         document.getElementById('foto-search')?.value ?? '',
                tanggal_dari:   document.getElementById('foto-dari')?.value ?? '',
                tanggal_sampai: document.getElementById('foto-sampai')?.value ?? '',
                nopol:          document.getElementById('foto-nopol')?.value ?? '',
                per_page:       fotoPerPage,
                page:           fotoPage,
            };
            if (fotoSort) { p.sort = fotoSort; p.dir = fotoDir; }
            return p;
        }

        function thumbHtml(url, label) {
            return `<a href="${url}" target="_blank" rel="noopener" title="${label}"><img src="${url}" alt="${label}" loading="lazy" class="portal-thumb"></a>`;
        }

        async function fetchFoto(scroll = false) {
            _abortFoto?.abort();
            _abortFoto = new AbortController();
            showLoading('foto-loading');
            const q = buildParams(getFotoParams());
            try {
                const r = await fetch(`${BASE_URL}/api/admin/portal/log-foto?${q}`, { signal: _abortFoto.signal });
                const json = await r.json();

                const extRows = json.data.filter(c => c.exterior && Object.values(c.exterior).some(Boolean));
                document.getElementById('foto-tbody-exterior').innerHTML = extRows.length
                    ? extRows.map(c => `<tr><td>${c.waktu}</td><td><strong>${c.nomor_kendaraan}</strong></td>
                        <td><div class="portal-thumb-row">
                            ${[['foto_depan','Depan'],['foto_kanan','Kanan'],['foto_kiri','Kiri'],['foto_belakang','Belakang']].map(([f,l]) => c.exterior[f] ? thumbHtml(c.exterior[f], l) : '').join('')}
                        </div></td></tr>`).join('')
                    : '<tr><td colspan="3" class="portal-empty">Belum ada foto eksterior.</td></tr>';

                const intRows = json.data.filter(c => c.interior && (c.interior.foto_1 || c.interior.foto_2 || c.interior.foto_3));
                document.getElementById('foto-tbody-interior').innerHTML = intRows.length
                    ? intRows.map(c => `<tr><td>${c.waktu}</td><td><strong>${c.nomor_kendaraan}</strong></td>
                        <td><div class="portal-thumb-row">
                            ${[1,2,3].map(i => c.interior[`foto_${i}`] ? thumbHtml(c.interior[`foto_${i}`], `Interior ${i}`) : '').join('')}
                        </div></td></tr>`).join('')
                    : '<tr><td colspan="3" class="portal-empty">Belum ada foto interior.</td></tr>';

                const mesinRows = json.data.filter(c => c.mesin && (c.mesin.foto_1 || c.mesin.foto_2 || c.mesin.foto_3));
                document.getElementById('foto-tbody-mesin').innerHTML = mesinRows.length
                    ? mesinRows.map(c => `<tr><td>${c.waktu}</td><td><strong>${c.nomor_kendaraan}</strong></td>
                        <td><div class="portal-thumb-row">
                            ${[1,2,3].map(i => c.mesin[`foto_${i}`] ? thumbHtml(c.mesin[`foto_${i}`], `Mesin ${i}`) : '').join('')}
                        </div></td></tr>`).join('')
                    : '<tr><td colspan="3" class="portal-empty">Belum ada foto mesin.</td></tr>';

                const bbmRows = json.data.filter(c => c.foto_bbm);
                document.getElementById('foto-tbody-bbm').innerHTML = bbmRows.length
                    ? bbmRows.map(c => `<tr><td>${c.waktu}</td><td><strong>${c.nomor_kendaraan}</strong></td><td>${thumbHtml(c.foto_bbm, 'BBM')}</td></tr>`).join('')
                    : '<tr><td colspan="3" class="portal-empty">Belum ada foto BBM.</td></tr>';

                mountPortalPagination('foto', json.pagination_html);
                if (scroll) scrollToSection('section-foto');
            } catch (e) {
                if (e.name !== 'AbortError') console.warn('fetchFoto error', e);
            } finally { hideLoading('foto-loading'); updateSectionFilterChrome('foto'); }
        }

        /* ================================================================
        ARSIP PDF AJAX
        ================================================================ */
        function getPdfParams() {
            const p = {
                search:         document.getElementById('pdf-search')?.value ?? '',
                tanggal_dari:   document.getElementById('pdf-dari')?.value ?? '',
                tanggal_sampai: document.getElementById('pdf-sampai')?.value ?? '',
                nopol:          document.getElementById('pdf-nopol')?.value ?? '',
                per_page:       pdfPerPage,
                page:           pdfPage,
            };
            if (pdfSort) { p.sort = pdfSort; p.dir = pdfDir; }
            return p;
        }

        async function fetchPdf(scroll = false) {
            _abortPdf?.abort();
            _abortPdf = new AbortController();
            showLoading('pdf-loading');
            const q = buildParams(getPdfParams());
            try {
                const r = await fetch(`${BASE_URL}/api/admin/portal/arsip-pdf?${q}`, { signal: _abortPdf.signal });
                const json = await r.json();
                const off = (json.current_page - 1) * json.per_page;
                const tbody = document.getElementById('pdf-tbody');
                if (tbody) {
                    tbody.innerHTML = json.data.length
                        ? json.data.map((c, i) => `<tr>
                            <td>${off + i + 1}</td>
                            <td>${c.tanggal ?? '-'}</td>
                            <td><strong>${c.nomor_kendaraan}</strong></td>
                            <td>${c.driver_serah ?? '-'}</td>
                            <td>${c.driver_terima ?? '-'}</td>
                            <td>${c.shift ?? '-'}</td>
                            <td>${c.pdf_url
                                ? `<a href="${c.pdf_url}" target="_blank" class="btn-view-pdf">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/></svg>
                                    View PDF</a>`
                                : '<span style="color:#94a3b8;font-size:.75rem">—</span>'}</td>
                        </tr>`).join('')
                        : '<tr><td colspan="7" class="portal-empty">Belum ada laporan PDF.</td></tr>';
                }
                mountPortalPagination('pdf', json.pagination_html);
                if (window.AdminTableSort) window.AdminTableSort.syncAria(document.getElementById('pdf-thead'), json.sort ?? null, json.dir ?? null);
                if (scroll) scrollToSection('section-pdf');
            } catch (e) {
                if (e.name !== 'AbortError') console.warn('fetchPdf error', e);
            } finally { hideLoading('pdf-loading'); updateSectionFilterChrome('pdf'); }
        }

        /* ================================================================
        LOCAL FILTER WIRING
        ================================================================ */
        const debouncedDb   = debounce(() => { dbPage = 1;   fetchDb(); });
        const debouncedFoto = debounce(() => { fotoPage = 1; fetchFoto(); });
        const debouncedPdf  = debounce(() => { pdfPage = 1;  fetchPdf(); });

        ['db-search','db-dari','db-sampai','db-nopol'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', debouncedDb);
        });
        ['foto-search','foto-dari','foto-sampai','foto-nopol'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', debouncedFoto);
        });
        ['pdf-search','pdf-dari','pdf-sampai','pdf-nopol'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', debouncedPdf);
        });

        // Per-page dropdowns
        document.getElementById('db-perpage')?.addEventListener('change', e => {
            dbPerPage = parseInt(e.target.value); dbPage = 1; fetchDb();
        });
        document.getElementById('foto-perpage')?.addEventListener('change', e => {
            fotoPerPage = parseInt(e.target.value); fotoPage = 1; fetchFoto();
        });
        document.getElementById('pdf-perpage')?.addEventListener('change', e => {
            pdfPerPage = parseInt(e.target.value); pdfPage = 1; fetchPdf();
        });

        function updateSectionFilterChrome(p) {
            const searchVal = document.getElementById(`${p}-search`)?.value.trim() ?? '';
            const dariVal = document.getElementById(`${p}-dari`)?.value ?? '';
            const sampaiVal = document.getElementById(`${p}-sampai`)?.value ?? '';
            const nopolVal = document.getElementById(`${p}-nopol`)?.value ?? '';
            const perpageVal = document.getElementById(`${p}-perpage`)?.value ?? '10';

            const showReset = searchVal.length > 0
                || dariVal !== ''
                || sampaiVal !== ''
                || nopolVal !== ''
                || perpageVal !== '10';

            const btn = document.querySelector(`[data-section-reset="${p}"]`);
            if (btn) btn.style.display = showReset ? '' : 'none';
        }

        // Reset buttons
        document.querySelectorAll('[data-section-reset]').forEach(btn => {
            btn.addEventListener('click', () => {
                const p = btn.dataset.sectionReset;
                [`${p}-search`,`${p}-dari`,`${p}-sampai`].forEach(id => { const el = document.getElementById(id); if(el) el.value=''; });
                [`${p}-nopol`].forEach(id => { const el = document.getElementById(id); if(el) el.selectedIndex=0; });
                const ppEl = document.getElementById(`${p}-perpage`);
                if (ppEl) { ppEl.value = '10'; }
                if (p === 'db')   { dbPerPage=10;   dbPage=1;   dbSort='';   dbDir='';   fetchDb(); }
                if (p === 'foto') { fotoPerPage=10; fotoPage=1; fotoSort=''; fotoDir=''; fetchFoto(); }
                if (p === 'pdf')  { pdfPerPage=10;  pdfPage=1;  pdfSort='';  pdfDir='';  fetchPdf(); }
                updateSectionFilterChrome(p);
            });
        });

        // Sort header wiring for DB and PDF tables
        if (window.AdminTableSort) {
            const dbWrap = document.getElementById('db-all-thead')?.closest('.admin-table-wrap');
            if (dbWrap) {
                window.AdminTableSort.bindRoot(dbWrap, {
                    getUrl: () => {
                        const url = new URL(location.href);
                        if (dbSort) {
                            url.searchParams.set('sort', dbSort);
                            url.searchParams.set('dir', dbDir);
                        } else {
                            url.searchParams.delete('sort');
                            url.searchParams.delete('dir');
                        }
                        return url;
                    },
                    onNavigate: (url) => { dbSort = url.searchParams.get('sort') || ''; dbDir = url.searchParams.get('dir') || ''; dbPage = 1; fetchDb(); },
                });
            }
            const pdfWrap = document.getElementById('pdf-thead')?.closest('.admin-table-wrap');
            if (pdfWrap) {
                window.AdminTableSort.bindRoot(pdfWrap, {
                    getUrl: () => {
                        const url = new URL(location.href);
                        if (pdfSort) {
                            url.searchParams.set('sort', pdfSort);
                            url.searchParams.set('dir', pdfDir);
                        } else {
                            url.searchParams.delete('sort');
                            url.searchParams.delete('dir');
                        }
                        return url;
                    },
                    onNavigate: (url) => { pdfSort = url.searchParams.get('sort') || ''; pdfDir = url.searchParams.get('dir') || ''; pdfPage = 1; fetchPdf(); },
                });
            }
        }



        /* ================================================================
        DATABASE SYNC (without page refresh)
        ================================================================ */
        const syncBtn = document.getElementById('db-sync-btn');
        const syncAlert = document.getElementById('db-sync-alert');

        function showSyncAlert(type, message, sheetUrl = null) {
            if (!syncAlert) return;
            const ok = type === 'success';
            syncAlert.style.display = '';
            syncAlert.style.background = ok ? '#dcfce7' : '#fee2e2';
            syncAlert.style.color = ok ? '#166534' : '#991b1b';
            syncAlert.style.border = ok ? '1px solid #86efac' : '1px solid #fca5a5';
            syncAlert.innerHTML = ok && sheetUrl
                ? `${message} <a href="${sheetUrl}" target="_blank" rel="noopener" style="font-weight:700;color:inherit;text-decoration:underline">Buka Spreadsheet</a>`
                : message;
        }

        if (syncBtn) {
            const defaultBtnHtml = syncBtn.innerHTML;
            syncBtn.addEventListener('click', async () => {
                const exportUrl = syncBtn.dataset.exportUrl;
                if (!exportUrl) return;

                syncBtn.disabled = true;
                syncBtn.innerHTML = 'Menyinkronkan...';
                showSyncAlert('success', 'Proses sinkronisasi sedang berjalan...');

                try {
                    const res = await fetch(exportUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    let data = null;
                    try { data = await res.json(); } catch (_) {}

                    if (!res.ok || !data?.success) {
                        const errMsg = data?.message || `Sinkronisasi gagal (HTTP ${res.status}).`;
                        showSyncAlert('error', errMsg);
                        return;
                    }

                    showSyncAlert('success', data.message || 'Sinkronisasi berhasil.', data.sheet_url || null);
                } catch (error) {
                    showSyncAlert('error', `Sinkronisasi gagal: ${error.message}`);
                } finally {
                    syncBtn.disabled = false;
                    syncBtn.innerHTML = defaultBtnHtml;
                }
            });
        }

        /* ================================================================
        INITIAL PAGINATION RENDER (from server-provided meta)
        ================================================================ */
        if (INIT_PAGINATION) {
            mountPortalPagination('db', INIT_PAGINATION.db);
            mountPortalPagination('foto', INIT_PAGINATION.foto);
            mountPortalPagination('pdf', INIT_PAGINATION.pdf);
        }
        ['db', 'foto', 'pdf'].forEach(updateSectionFilterChrome);

        if (CAN_ACCESS_DATABASE) {
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.portal-db-detail-btn');
                if (btn && btn.dataset.checklistId) {
                    e.preventDefault();
                    openPortalChecklistDetail(btn.dataset.checklistId);
                }
            });
            document.getElementById('portal-checklist-modal-close')?.addEventListener('click', closePortalChecklistModal);
            document.getElementById('portal-checklist-modal')?.addEventListener('click', function (e) {
                if (e.target.id === 'portal-checklist-modal') closePortalChecklistModal();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') return;
                closePortalChecklistModal();
            });
        }

        })();
    </script>
@endpush