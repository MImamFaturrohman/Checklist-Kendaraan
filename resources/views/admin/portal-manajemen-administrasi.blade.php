@extends('layouts.dash-app')

@section('title', 'Portal Manajemen Administrasi')
@section('pageTitle', 'Portal Manajemen Administrasi')
@section('pageSubtitle', 'Master armada & manajemen user')

@php $premiumBgId = 'portal_manajemen'; @endphp

@push('styles')
<style>
.portal-bidang-root-row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 8px 12px;
    padding: 10px 12px;
    border-radius: 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #1e293b;
    transition: all 0.2s;
}

.portal-bidang-child-row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 8px 12px;
    padding: 8px 12px;
    border-radius: 8px;
    background: rgba(248, 250, 252, 0.9);
    border: 1px solid #e2e8f0;
    color: #334155;
    transition: all 0.2s;
}

.portal-bidang-badge {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 0.72rem;
    padding: 2px 7px;
    border-radius: 6px;
    margin-top: 3px;
}

.portal-bidang-mgr-badge {
    background: rgba(234, 88, 12, .08);
    color: #c2410c;
    border: 1px solid rgba(234, 88, 12, .18);
}

.portal-bidang-tl-badge {
    background: rgba(124, 58, 237, .08);
    color: #7c3aed;
    border: 1px solid rgba(124, 58, 237, .18);
}

.portal-bidang-btn-sub {
    background: rgba(124, 58, 237, .08);
    color: #7c3aed;
    border: 1px solid rgba(124, 58, 237, .2);
}

/* Dark Mode styles */
html.dark .portal-bidang-root-row {
    background: rgba(30, 41, 59, 0.45) !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
    color: rgba(241, 245, 249, 0.9) !important;
}

html.dark .portal-bidang-child-row {
    background: rgba(15, 23, 42, 0.6) !important;
    border-color: rgba(255, 255, 255, 0.06) !important;
    color: rgba(226, 232, 240, 0.8) !important;
}

html.dark .portal-bidang-root-row strong {
    color: rgba(241, 245, 249, 0.95) !important;
}

html.dark .portal-bidang-child-row span {
    color: rgba(226, 232, 240, 0.85) !important;
}

html.dark .portal-bidang-mgr-badge {
    background: rgba(251, 146, 60, 0.12) !important;
    color: #fb923c !important;
    border-color: rgba(251, 146, 60, 0.25) !important;
}

html.dark .portal-bidang-tl-badge {
    background: rgba(167, 139, 250, 0.12) !important;
    color: #a78bfa !important;
    border-color: rgba(167, 139, 250, 0.25) !important;
}

html.dark .portal-bidang-btn-sub {
    background: rgba(167, 139, 250, 0.12) !important;
    color: #a78bfa !important;
    border-color: rgba(167, 139, 250, 0.25) !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
@endpush

@section('content')
<div class="armada-shell" style="position:relative;z-index:1">

    {{-- ── STATS ROW ─────────────────────────────────────────────────────── --}}
    <div class="portal-stats-row mgmt-stats-strip" data-stat-count="4">
        <x-admin-stat-card
            title="Total Kendaraan"
            :value="$stats['total_kendaraan']"
            unit="Unit"
            description="Unit kendaraan terdaftar"
            icon="bi bi-truck-front-fill"
        />
        <x-admin-stat-card
            title="Total Driver"
            :value="$stats['total_driver']"
            unit="Personel"
            description="Driver aktif dalam sistem"
            icon="bi bi-person-fill"
        />
        <x-admin-stat-card
            title="PIC Kendaraan"
            :value="$stats['total_pic']"
            unit="Personel"
            description="Person in charge kendaraan"
            icon="bi bi-person-badge-fill"
        />
        <x-admin-stat-card
            title="Total User"
            :value="$stats['total_portal_users']"
            unit="Akun"
            description="Pengguna VMS"
            icon="bi bi-people-fill"
        />
    </div>

    {{-- ── TAB BAR ───────────────────────────────────────────────────────── --}}
    <div class="mgmt-tab-bar">
        <button class="mgmt-tab" id="tab-bidang" onclick="switchTab('bidang')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M4 12h10M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <span>Bidang / Bagian</span>
            <span class="mgmt-tab-count" id="tc-bidang">{{ \App\Models\Bidang::count() }}</span>
        </button>
        <button class="mgmt-tab active" id="tab-armada" onclick="switchTab('armada')">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><rect x="1" y="3" width="15" height="13" rx="1" stroke="currentColor" stroke-width="2"/><path d="M16 8l4 2 2 5v2h-6V8z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="5.5" cy="18.5" r="2.5" stroke="currentColor" stroke-width="2"/><circle cx="18.5" cy="18.5" r="2.5" stroke="currentColor" stroke-width="2"/></svg>
            <span>Manajemen Unit Kendaraan</span>
            <span class="mgmt-tab-count" id="tc-armada">{{ $stats['total_kendaraan'] }}</span>
        </button>
        <button class="mgmt-tab" id="tab-users" onclick="switchTab('users')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>Manajemen User</span>
            <span class="mgmt-tab-count" id="tc-users">{{ $stats['total_portal_users'] }}</span>
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- SECTION: BIDANG / BAGIAN                                          --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div id="section-bidang" style="display:none">
        <div class="mgmt-panel">
            <div class="mgmt-panel-header" style="justify-content: space-between;">
                <div class="portal-section-title" style="margin-bottom: 0;">
                    <i class="bi bi-text-left" style="color: var(--dash-gold);"></i>
                    Bidang / Bagian
                </div>
                <button type="button" class="mgmt-ph-add-btn" id="btn-open-bidang-root" style="background:rgba(124,58,237,.12);color:#7c3aed;" onclick="openBidangModal({})">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                    Bidang Utama
                </button>
            </div>

            <div id="bidang-loading" class="mgmt-loading" style="display:none">
                <span class="mgmt-dot"></span><span class="mgmt-dot"></span><span class="mgmt-dot"></span>
            </div>

            <div id="portal-bidang-tree" style="padding: 10px 0" aria-live="polite"></div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- SECTION: MASTER ARMADA                                            --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div id="section-armada">
        <div class="mgmt-panel">

            {{-- Panel header --}}
            <div class="mgmt-panel-header" style="justify-content: space-between;">
                <div class="portal-section-title" style="margin-bottom: 0;">
                    <i class="bi bi-truck-front-fill" style="color: var(--dash-gold, #ffbf00);"></i>
                    Manajemen Unit Kendaraan
                </div>
                <div class="portal-local-filters ppm-daftar-filters" style="margin-top: 0; padding: 0; background: transparent; border: none; box-shadow: none; align-items: center; gap: 8px;">
                    <div class="admin-search-wrap portal-search-full" style="width: 350px; max-width: 100%;">
                        <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <input type="text" id="armada-search" class="admin-search-input" placeholder="Cari nomor atau jenis kendaraan…">
                    </div>
                    <x-admin-per-page-select id="armada-perpage" name="per_page" :selected="$kendaraans->perPage()" />
                    <button type="button" class="btn btn-sm sppd-icon-btn admin-filter-reset" id="armada-reset-btn" onclick="resetArmadaFilters()" title="Reset filter" aria-label="Reset filter" style="display: none">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <button type="button" class="mgmt-ph-add-btn" id="btn-open-armada-modal" onclick="openArmadaAddModal()" style="background: rgba(14, 212, 196, 0.20); color: #0ed4c4 !important;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                        Tambah Kendaraan
                    </button>
                </div>
            </div>

            {{-- Loading --}}
            <div id="armada-loading" class="mgmt-loading" style="display:none">
                <span class="mgmt-dot"></span><span class="mgmt-dot"></span><span class="mgmt-dot"></span>
            </div>

            {{-- Table --}}
            <div class="mgmt-table-wrap">
                <table class="mgmt-table">
                    <thead id="armada-thead">
                        <tr>
                            <th class="w-10">#</th>
                            <x-sortable-th key="nomor_kendaraan" label="Nomor Kendaraan" :activeSort="null" :activeDir="null" />
                            <x-sortable-th key="jenis_kendaraan" label="Jenis Kendaraan" :activeSort="null" :activeDir="null" />
                            <x-sortable-th key="bidang" label="Bidang" :activeSort="null" :activeDir="null" />
                            <x-sortable-th key="km_saat_ini" label="KM Saat Ini" :activeSort="null" :activeDir="null" />
                            <th>STNK</th>
                            <th>Pajak STNK</th>
                            <th>KIR</th>
                            <x-sortable-th key="status_kendaraan" label="Status" :activeSort="null" :activeDir="null" />
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="armada-tbody">
                        @forelse($kendaraans as $k)
                            <tr id="krow-{{ $k->id }}">
                                <td class="text-muted">{{ ($kendaraans->currentPage()-1)*$kendaraans->perPage()+$loop->iteration }}</td>
                                <td><span class="mgmt-nopol">{{ $k->nomor_kendaraan }}</span></td>
                                <td>{{ $k->jenis_kendaraan }}</td>
                                <td class="text-muted">{{ $k->bidang ?: '—' }}</td>
                                <td>@if($k->km_current !== null){{ number_format((int) $k->km_current, 0, ',', '.') }} km @else<span class="text-muted">—</span>@endif</td>
                                @foreach ([$k->tanggal_stnk, $k->tanggal_pajak_stnk, $k->tanggal_kir] as $expDate)
                                    @php
                                        $formatted = \App\Models\Kendaraan::formatArmadaDateId($expDate);
                                        $state = \App\Models\Kendaraan::expiryStateForDate($expDate);
                                    @endphp
                                    <td class="mgmt-expiry-cell">
                                        @if ($formatted)
                                            <div class="mgmt-expiry-date">{{ $formatted }}</div>
                                            @if ($state)
                                                <span class="mgmt-expiry-badge {{ $state === 'AKTIF' ? 'mgmt-expiry-aktif' : 'mgmt-expiry-expired' }}">{{ $state }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                @php $stK = $k->status_kendaraan ?? 'Aktif'; @endphp
                                <td><span class="{{ \App\Models\Kendaraan::statusPillClass($stK) }}">{{ $stK }}</span></td>
                                <td class="text-center">
                                    <div class="mgmt-actions">
                                        <button type="button" class="mgmt-act-btn mgmt-act-edit js-armada-edit" data-id="{{ $k->id }}" data-nopol="{{ e($k->nomor_kendaraan) }}" data-jenis="{{ e($k->jenis_kendaraan) }}" data-bidang="{{ e($k->bidang ?? '') }}" data-km-current="{{ $k->km_current !== null ? (int) $k->km_current : '' }}" data-tanggal-stnk="{{ e($k->tanggal_stnk?->format('Y-m-d') ?? '') }}" data-tanggal-pajak-stnk="{{ e($k->tanggal_pajak_stnk?->format('Y-m-d') ?? '') }}" data-tanggal-kir="{{ e($k->tanggal_kir?->format('Y-m-d') ?? '') }}" data-status-kendaraan="{{ e($stK) }}" title="Edit">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                        <form id="kdel-{{ $k->id }}" action="{{ route('admin.portal-manajemen.kendaraan.destroy', $k) }}" method="POST" style="display:contents" onsubmit="event.preventDefault(); deleteKendaraan({{ $k->id }}, '{{ addslashes($k->nomor_kendaraan) }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="mgmt-act-btn mgmt-act-del" title="Hapus">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="mgmt-empty">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="margin:0 auto 8px;display:block;opacity:.3"><path d="M19 17H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5"/></svg>
                                Belum ada data kendaraan.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="armada-pagination" class="tbl-pagination-mount"></div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- SECTION: MANAJEMEN USER                                           --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div id="section-users" style="display:none">
        <div class="mgmt-panel">

            {{-- Panel header --}}
            <div class="mgmt-panel-header" style="justify-content: space-between;">
                <div class="portal-section-title" style="margin-bottom: 0;">
                    <i class="bi bi-people-fill" style="color: var(--dash-gold, #ffbf00);"></i>
                    Manajemen User
                </div>
                <div class="portal-local-filters ppm-daftar-filters" style="margin-top: 0; padding: 0; background: transparent; border: none; box-shadow: none; align-items: center; gap: 8px;">
                    <div class="admin-search-wrap portal-search-full" style="width: 320px; max-width: 100%;">
                        <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <input type="text" id="user-search" class="admin-search-input" placeholder="Cari nama atau username…">
                    </div>
                    <div class="ppm-status-wrap">
                        <select id="user-role-filter" class="admin-filter-input" aria-label="Filter Role">
                            <option value="">Semua Role</option>
                            <option value="driver">Driver</option>
                            <option value="pic_kendaraan">PIC Kendaraan</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <x-admin-per-page-select id="user-perpage" name="per_page" :selected="$users->perPage()" />
                    <button type="button" class="btn btn-sm sppd-icon-btn admin-filter-reset" id="user-reset-btn" onclick="resetUserFilters()" title="Reset filter" aria-label="Reset filter" style="display: none">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <button type="button" class="mgmt-ph-add-btn" id="btn-open-user-add-modal" onclick="openUserAddModal()" style="color:#2563eb; background: rgba(37, 99, 235, 0.25);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                        Tambah User
                    </button>
                </div>
            </div>

            {{-- Loading --}}
            <div id="user-loading" class="mgmt-loading" style="display:none">
                <span class="mgmt-dot"></span><span class="mgmt-dot"></span><span class="mgmt-dot"></span>
            </div>

            {{-- Table --}}
            <div class="mgmt-table-wrap">
                <table class="mgmt-table">
                    <thead id="user-thead">
                        <tr>
                            <th class="w-10">#</th>
                            <x-sortable-th key="name" label="Nama Lengkap" :activeSort="null" :activeDir="null" />
                            <x-sortable-th key="username" label="Username" :activeSort="null" :activeDir="null" />
                            <x-sortable-th key="email" label="Email" :activeSort="null" :activeDir="null" />
                            <x-sortable-th key="role" label="Role" :activeSort="null" :activeDir="null" />
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="user-tbody">
                        @forelse($users as $u)
                            @php
                                $uGrad = match ($u->role) {
                                    'pic_kendaraan' => 'linear-gradient(135deg,#7c3aed,#a78bfa)',
                                    'manager'       => 'linear-gradient(135deg,#ea580c,#fb923c)',
                                    'admin'         => 'linear-gradient(135deg,#0d9488,#2dd4bf)',
                                    default         => 'linear-gradient(135deg,#2563eb,#60a5fa)',
                                };
                            @endphp
                            <tr id="urow-server-{{ $u->id }}">
                                <td class="text-muted">{{ ($users->currentPage()-1)*$users->perPage()+$loop->iteration }}</td>
                                <td>
                                    <div class="mgmt-user-cell">
                                        <div class="mgmt-user-avatar" style="background:{{ $uGrad }}">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="mgmt-user-name">{{ $u->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="mgmt-username">{{ $u->username }}</span></td>
                                <td><span class="text-muted" style="font-size:0.85rem">{{ $u->email }}</span></td>
                                <td>
                                    @switch($u->role)
                                        @case('pic_kendaraan')
                                            <span class="mgmt-role-badge mgmt-role-pic">PIC Kendaraan</span>
                                            @break
                                        @case('manager')
                                            <span class="mgmt-role-badge mgmt-role-manager">Manager</span>
                                            @break
                                        @case('admin')
                                            <span class="mgmt-role-badge mgmt-role-admin">Admin</span>
                                            @break
                                        @default
                                            <span class="mgmt-role-badge mgmt-role-driver">Driver</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($u->isOnline())
                                        <span class="mgmt-presence mgmt-presence--online">
                                            <span class="mgmt-presence-dot" aria-hidden="true"></span>
                                            Online
                                        </span>
                                    @else
                                        <span class="mgmt-presence mgmt-presence--offline">
                                            <span class="mgmt-presence-dot" aria-hidden="true"></span>
                                            Offline
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="mgmt-actions">
                                        <button type="button" class="mgmt-act-btn mgmt-act-edit" onclick="openUserEdit({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->username) }}', '{{ addslashes($u->email) }}', '{{ $u->role }}')">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            Edit
                                        </button>
                                        <button type="button" class="mgmt-act-btn mgmt-act-del" onclick="deleteUser({{ $u->id }}, '{{ addslashes($u->name) }}')">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                            Hapus
                                        </button>
                                        <button type="button" class="mgmt-act-btn mgmt-act-edit" onclick="resetUserPassword(${u.id},'${escJs(u.name)}')">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                                            Reset
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="mgmt-empty">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="margin:0 auto 8px;display:block;opacity:.3"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.5"/></svg>
                                Belum ada data user.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="user-pagination" class="tbl-pagination-mount"></div>
        </div>
    </div>

</div>{{-- /.armada-shell --}}
@endsection

@section('modals')
{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- TAMBAH / UBAH KENDARAAN (MASTER ARMADA)                                --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="armada-form-modal" class="mgmt-modal-overlay" hidden onclick="if(event.target===this)closeArmadaModal()">
    <div class="mgmt-modal-box" onclick="event.stopPropagation()">
        <div class="mgmt-modal-header">
            <div class="mgmt-modal-avatar" id="armada-modal-avatar" style="background:rgba(15,118,110,.15);color:#0f766e;font-size:0.75rem;font-weight:800;border-radius:12px;width:44px;height:44px;display:flex;align-items:center;justify-content:center;padding:0">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M19 17H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2"/><path d="M7 17v2m10-2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="1" fill="currentColor"/></svg>
            </div>
            <div>
                <h2 class="mgmt-modal-title" id="armada-modal-title">Kendaraan</h2>
                <p class="mgmt-modal-sub" id="armada-modal-sub">Isi data kendaraan operasional</p>
            </div>
            <button type="button" class="mgmt-modal-close" onclick="closeArmadaModal()" aria-label="Tutup">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
        </div>
        <form id="form-armada-modal" onsubmit="event.preventDefault(); submitArmadaModal()">
            <input type="hidden" id="armada-modal-id" value="">
            <div class="mgmt-modal-body">
                <p class="mgmt-modal-section-label">DATA KENDARAAN</p>
                <div class="mgmt-modal-grid">
                    <div class="mgmt-field">
                        <label class="mgmt-label" for="armada-modal-nopol">Nomor Kendaraan</label>
                        <input type="text" id="armada-modal-nopol" class="mgmt-input" placeholder="B 1234 ABC" required maxlength="20" autocomplete="off">
                    </div>
                    <div class="mgmt-field">
                        <label class="mgmt-label" for="armada-modal-jenis">Jenis Kendaraan</label>
                        <input type="text" id="armada-modal-jenis" class="mgmt-input" placeholder="MITSUBISHI XPANDER" required maxlength="100" autocomplete="off">
                    </div>
                    <div class="mgmt-field">
                        <label class="mgmt-label" for="armada-modal-bidang">Bidang</label>
                        <input type="text" id="armada-modal-bidang" class="mgmt-input" placeholder="Operasional" maxlength="100" autocomplete="off">
                    </div>
                    <div class="mgmt-field">
                        <label class="mgmt-label" for="armada-modal-km" id="armada-modal-km-label">Set KM</label>
                        <input type="number" id="armada-modal-km" class="mgmt-input" placeholder="0" min="0" step="1" value="0">
                    </div>
                </div>
                <p class="mgmt-modal-section-label" style="margin-top:8px">MASA BERLAKU &amp; STATUS</p>
                <div class="mgmt-modal-grid">
                    <div class="mgmt-field">
                        <label class="mgmt-label" for="armada-modal-tanggal-stnk">STNK (berlaku s/d)</label>
                        <input type="date" id="armada-modal-tanggal-stnk" class="mgmt-input">
                    </div>
                    <div class="mgmt-field">
                        <label class="mgmt-label" for="armada-modal-tanggal-pajak">Pajak STNK (berlaku s/d)</label>
                        <input type="date" id="armada-modal-tanggal-pajak" class="mgmt-input">
                    </div>
                    <div class="mgmt-field">
                        <label class="mgmt-label" for="armada-modal-tanggal-kir">KIR (berlaku s/d)</label>
                        <input type="date" id="armada-modal-tanggal-kir" class="mgmt-input">
                    </div>
                    <div class="mgmt-field">
                        <label class="mgmt-label" for="armada-modal-status">Status kendaraan</label>
                        <select id="armada-modal-status" class="mgmt-input" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Non Aktif">Non Aktif</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mgmt-modal-footer">
                <button type="button" class="mgmt-cancel-btn" onclick="closeArmadaModal()">Batal</button>
                <button type="submit" class="mgmt-submit-btn" id="btn-save-armada">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- TAMBAH USER MODAL                                                      --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="user-add-modal" class="mgmt-modal-overlay" hidden onclick="if(event.target===this)closeUserAddModal()">
    <div class="mgmt-modal-box" onclick="event.stopPropagation()">
        <div class="mgmt-modal-header">
            <div class="mgmt-modal-avatar" style="background:linear-gradient(135deg,#2563eb,#60a5fa);color:#fff;font-weight:800;display:flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:12px;font-size:1.05rem">+</div>
            <div>
                <h2 class="mgmt-modal-title">Tambah User</h2>
                <p class="mgmt-modal-sub">Akun baru</p>
            </div>
            <button type="button" class="mgmt-modal-close" onclick="closeUserAddModal()" aria-label="Tutup">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
        </div>
        <form id="form-add-user">
            @csrf
            <div class="mgmt-modal-body">
                <p class="mgmt-modal-section-label">DATA AKUN</p>
                <div class="mgmt-modal-grid">
                    <div class="mgmt-field">
                        <label class="mgmt-label" for="add-user-name">Nama Lengkap</label>
                        <input type="text" name="name" id="add-user-name" class="mgmt-input" placeholder="Nama Lengkap" required autocomplete="off">
                    </div>
                    <div class="mgmt-field">
                        <label class="mgmt-label" for="add-user-username">Username</label>
                        <input type="text" name="username" id="add-user-username" class="mgmt-input" placeholder="username" required autocomplete="off">
                    </div>
                    <div class="mgmt-field">
                        <label class="mgmt-label" for="add-user-email">Email</label>
                        <input type="email" name="email" id="add-user-email" class="mgmt-input" placeholder="Email" required autocomplete="off">
                    </div>
                    <div class="mgmt-field">
                        <label class="mgmt-label" for="add-user-role">Role</label>
                        <select name="role" id="add-user-role" class="mgmt-input" required>
                            <option value="driver">Driver</option>
                            <option value="pic_kendaraan">PIC Kendaraan</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mgmt-field">
                        <label class="mgmt-label" for="add-user-pw">Password</label>
                        <div class="mgmt-pw-wrap">
                            <input type="password" name="password" id="add-user-pw" class="mgmt-input" value="{{ $defaultPassword }}" required autocomplete="new-password">
                            <button type="button" class="mgmt-pw-eye" onclick="toggleEye('add-user-pw', this)" tabindex="-1" aria-label="Tampilkan password">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                            </button>
                        </div>
                        <p class="mgmt-hint">Default: <code>{{ $defaultPassword }}</code></p>
                    </div>
                </div>
            </div>
            <div class="mgmt-modal-footer">
                <button type="button" class="mgmt-cancel-btn" onclick="closeUserAddModal()">Batal</button>
                <button type="submit" class="mgmt-submit-btn" id="btn-add-user">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- EDIT USER MODAL                                                        --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="user-edit-modal" class="mgmt-modal-overlay" hidden onclick="if(event.target===this)closeUserModal()">
    <div class="mgmt-modal-box">
        <div class="mgmt-modal-header">
            <div class="mgmt-modal-avatar" id="modal-avatar">U</div>
            <div>
                <h2 class="mgmt-modal-title">Edit User</h2>
                <p class="mgmt-modal-sub" id="modal-sub-text">—</p>
            </div>
            <button type="button" class="mgmt-modal-close" onclick="closeUserModal()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
        </div>
        <form id="form-edit-user" onsubmit="event.preventDefault(); submitUserEdit()">
            <input type="hidden" id="edit-user-id">
            <div class="mgmt-modal-body">
                <p class="mgmt-modal-section-label">INFORMASI AKUN</p>
                <div class="mgmt-modal-grid">
                    <div class="mgmt-field">
                        <label class="mgmt-label">Nama Lengkap</label>
                        <div class="mgmt-input-icon-wrap">
                            <svg class="mgmt-input-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                            <input type="text" id="edit-name" class="mgmt-input has-icon" placeholder="Nama Lengkap" required>
                        </div>
                    </div>
                    <div class="mgmt-field">
                        <label class="mgmt-label">Username</label>
                        <div class="mgmt-input-icon-wrap">
                            <svg class="mgmt-input-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                            <input type="text" id="edit-username" class="mgmt-input has-icon" placeholder="username" required autocomplete="off">
                        </div>
                    </div>
                    <div class="mgmt-field">
                        <label class="mgmt-label">Email</label>
                        <div class="mgmt-input-icon-wrap">
                            <svg class="mgmt-input-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <input type="email" id="edit-email" class="mgmt-input has-icon" placeholder="Alamat Email" required autocomplete="off">
                        </div>
                    </div>
                    <div class="mgmt-field">
                        <label class="mgmt-label">Role</label>
                        <select id="edit-role" class="mgmt-input" required>
                            <option value="driver">Driver</option>
                            <option value="pic_kendaraan">PIC Kendaraan</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div class="mgmt-modal-divider"></div>
                <p class="mgmt-modal-section-label">RESET PASSWORD <span style="font-weight:400;text-transform:none;color:#94a3b8">(opsional)</span></p>
                <div class="mgmt-hint-box">
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <span style="display:inline-flex;align-items:center;gap:6px;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" style="flex-shrink:0"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Password default: <code>{{ $defaultPassword }}</code>
                        </span>
                        <span>
                            Kosongkan jika tidak ingin mengubah password.
                        </span>
                    </div>
                </div>
                <div class="mgmt-field" style="max-width:320px">
                    <label class="mgmt-label">Password Baru</label>
                    <div class="mgmt-pw-wrap">
                        <input type="password" id="edit-password" class="mgmt-input" placeholder="Kosongkan jika tidak diubah" autocomplete="new-password">
                        <button type="button" class="mgmt-pw-eye" onclick="toggleEye('edit-password', this)" tabindex="-1">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="mgmt-modal-footer">
                <button type="button" class="mgmt-cancel-btn" onclick="closeUserModal()">Batal</button>
                <button type="submit" class="mgmt-submit-btn" id="btn-save-edit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('modals')

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: BIDANG / BAGIAN                                                 --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="portal-bidang-modal" class="mgmt-modal-overlay" hidden onclick="if(event.target===this)closeBidangModal()">
    <div class="mgmt-modal-box" onclick="event.stopPropagation()">
        <div class="mgmt-modal-header">
            <div class="mgmt-modal-avatar" style="background:rgba(124,58,237,.15);color:#7c3aed;display:flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:12px">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M4 12h10M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <div>
                <h2 class="mgmt-modal-title" id="portal-bidang-modal-title">Bidang / Bagian</h2>
                <p class="mgmt-modal-sub">Tambah atau ubah data bidang</p>
            </div>
            <button type="button" class="mgmt-modal-close" onclick="closeBidangModal()" aria-label="Tutup">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
        </div>
        <form id="portal-form-bidang" onsubmit="event.preventDefault(); submitBidangModal()">
            <input type="hidden" id="portal-bidang-id" value="">
            <div class="mgmt-modal-body">
                <p class="mgmt-modal-section-label">DATA BIDANG</p>
                <div class="mgmt-modal-grid">
                    <div class="mgmt-field" style="grid-column:span 2">
                        <label class="mgmt-label" for="portal-bidang-nama">Nama Bidang / Bagian <span style="color:#ef4444">*</span></label>
                        <input type="text" id="portal-bidang-nama" class="mgmt-input" required maxlength="200" placeholder="Contoh: Operasional">
                    </div>
                    <div class="mgmt-field" id="portal-bidang-parent-wrap" style="grid-column:span 2">
                        <label class="mgmt-label" for="portal-bidang-parent">Induk (kosongkan untuk bidang utama)</label>
                        <select id="portal-bidang-parent" class="mgmt-input">
                            <option value="">— Bidang utama —</option>
                        </select>
                    </div>
                </div>

                <div id="portal-bidang-leader-section" style="display:none">
                    <div class="mgmt-modal-divider"></div>
                    <p class="mgmt-modal-section-label">PIMPINAN BIDANG <span style="color:#ef4444">*</span></p>
                    <div class="mgmt-modal-grid">
                        <div class="mgmt-field" style="grid-column:span 2">
                            <label class="mgmt-label" for="portal-bidang-pimpinan-jabatan">Jabatan <span style="color:#ef4444">*</span></label>
                            <input type="text" id="portal-bidang-pimpinan-jabatan" class="mgmt-input" maxlength="150" placeholder="Contoh: Manajer, Team Leader, Supervisor">
                        </div>
                        <div class="mgmt-field">
                            <label class="mgmt-label" for="portal-bidang-pimpinan-nama">Nama Pimpinan <span style="color:#ef4444">*</span></label>
                            <input type="text" id="portal-bidang-pimpinan-nama" class="mgmt-input" maxlength="200" placeholder="Nama lengkap pimpinan">
                        </div>
                        <div class="mgmt-field">
                            <label class="mgmt-label" for="portal-bidang-pimpinan-email">Email Pimpinan <span style="color:#ef4444">*</span></label>
                            <input type="email" id="portal-bidang-pimpinan-email" class="mgmt-input" maxlength="255" placeholder="pimpinan@perusahaan.com">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mgmt-modal-footer">
                <button type="button" class="mgmt-cancel-btn" onclick="closeBidangModal()">Batal</button>
                <button type="submit" class="mgmt-submit-btn" id="btn-save-bidang">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
'use strict';

const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const BASE = window.location.origin;
const ARMADA_STORE_URL = @json(route('admin.portal-manajemen.kendaraan.store'));
const ARMADA_UPDATE_URL_TMPL = @json(url('/admin/portal-manajemen-administrasi/kendaraan/__ID__'));

const INIT_MGMT_PAGINATION = {
    armada: @json(\App\Support\AdminTablePagination::linksHtml($kendaraans, route('api.admin.portal.kendaraan'))),
    users: @json(\App\Support\AdminTablePagination::linksHtml($users, route('api.admin.portal.users'))),
};
const MGMT_API_PATHS = {
    armada: @json(route('api.admin.portal.kendaraan')),
    users: @json(route('api.admin.portal.users')),
};

function mountMgmtPagination(section, html) {
    const el = document.getElementById(section + '-pagination');
    if (!el) return;
    if (window.AdminPagination) {
        window.AdminPagination.mountPagination(el, html || '');
        if (!el.dataset.paginationBound) {
            el.dataset.paginationBound = '1';
            window.AdminPagination.bindPaginationLinks(el, (url) => {
                const page = parseInt(url.searchParams.get('page') || '1', 10);
                if (section === 'armada') { armadaPage = page; fetchArmada(true); }
                else { userPage = page; fetchUsers(true); }
            }, { pathname: new URL(MGMT_API_PATHS[section]).pathname });
        }
    } else {
        const interval = setInterval(() => {
            if (window.AdminPagination) {
                clearInterval(interval);
                mountMgmtPagination(section, html);
            }
        }, 30);
    }
}

/* ─── Helpers ──────────────────────────────────────────────────────────── */
function escHtml(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function escJs(s)   { return String(s??'').replace(/\\/g,'\\\\').replace(/'/g,"\\'"); }

/** Avatar & badge konsisten untuk role yang dikelola portal. */
function userMgmtAvatarGradient(role) {
    switch (role) {
        case 'pic_kendaraan': return 'linear-gradient(135deg,#7c3aed,#a78bfa)';
        case 'manager':       return 'linear-gradient(135deg,#ea580c,#fb923c)';
        case 'admin':         return 'linear-gradient(135deg,#0d9488,#2dd4bf)';
        default:              return 'linear-gradient(135deg,#2563eb,#60a5fa)';
    }
}
function userMgmtRoleBadge(role) {
    switch (role) {
        case 'pic_kendaraan':
            return '<span class="mgmt-role-badge mgmt-role-pic">PIC Kendaraan</span>';
        case 'manager':
            return '<span class="mgmt-role-badge mgmt-role-manager">Manager</span>';
        case 'admin':
            return '<span class="mgmt-role-badge mgmt-role-admin">Admin</span>';
        default:
            return '<span class="mgmt-role-badge mgmt-role-driver">Driver</span>';
    }
}
function userMgmtRoleLabel(role) {
    switch (role) {
        case 'pic_kendaraan': return 'PIC Kendaraan';
        case 'manager':       return 'Manager';
        case 'admin':         return 'Admin';
        default:              return 'Driver';
    }
}
function userMgmtPresenceBadge(isOnline) {
    if (isOnline) {
        return '<span class="mgmt-presence mgmt-presence--online"><span class="mgmt-presence-dot" aria-hidden="true"></span>Online</span>';
    }
    return '<span class="mgmt-presence mgmt-presence--offline"><span class="mgmt-presence-dot" aria-hidden="true"></span>Offline</span>';
}

function armadaFmtIdDate(v) {
    if (!v) return '';
    const raw = String(v).split('T')[0];
    if (!raw) return '';
    const d = new Date(raw + 'T12:00:00');
    if (Number.isNaN(d.getTime())) return '';
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}
function armadaExpiryState(v) {
    if (!v) return null;
    const raw = String(v).split('T')[0];
    const exp = new Date(raw + 'T12:00:00');
    if (Number.isNaN(exp.getTime())) return null;
    const t = new Date();
    t.setHours(0, 0, 0, 0);
    exp.setHours(0, 0, 0, 0);
    return t.getTime() <= exp.getTime() ? 'AKTIF' : 'EXPIRED';
}
function armadaExpiryCellInner(v) {
    const line = armadaFmtIdDate(v);
    if (!line) return '<span class="text-muted">—</span>';
    const st = armadaExpiryState(v);
    const badge = st ? `<span class="mgmt-expiry-badge ${st === 'AKTIF' ? 'mgmt-expiry-aktif' : 'mgmt-expiry-expired'}">${st}</span>` : '';
    return `<div class="mgmt-expiry-date">${escHtml(line)}</div>${badge}`;
}
function armadaStatusPillHtml(st) {
    const s = st || 'Aktif';
    const cls = s === 'Maintenance' ? 'mgmt-status-pill mgmt-status-maint' : (s === 'Non Aktif' ? 'mgmt-status-pill mgmt-status-off' : 'mgmt-status-pill mgmt-status-on');
    return `<span class="${cls}">${escHtml(s)}</span>`;
}
function numFmt(n)  { return Number(n).toLocaleString('id-ID'); }
function debounce(fn,ms){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a),ms); }; }

/* ─── Eye toggle ───────────────────────────────────────────────────────── */
window.toggleEye = function(inputId, btn) {
    const el = document.getElementById(inputId);
    if (!el) return;
    const isHidden = el.type === 'password';
    el.type = isHidden ? 'text' : 'password';
    btn.innerHTML = isHidden
        ? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'
        : '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>';
};

/* ─── Modal overlay lock (armada + tambah user + edit user + bidang) ─── */
function refreshMgmtModalOverflow() {
    const armada   = document.getElementById('armada-form-modal');
    const userAdd  = document.getElementById('user-add-modal');
    const userEdit = document.getElementById('user-edit-modal');
    const bidang   = document.getElementById('portal-bidang-modal');
    const anyOpen = [armada, userAdd, userEdit, bidang].some(el => el && !el.hidden);
    document.body.style.overflow = anyOpen ? 'hidden' : '';
}

window.openUserAddModal = function() {
    document.getElementById('add-user-name').value = '';
    document.getElementById('add-user-username').value = '';
    document.getElementById('add-user-email').value = '';
    document.getElementById('add-user-role').value = 'driver';
    document.getElementById('add-user-pw').value = '{{ $defaultPassword }}';
    document.getElementById('user-add-modal').hidden = false;
    refreshMgmtModalOverflow();
    setTimeout(() => document.getElementById('add-user-name').focus(), 80);
};

window.closeUserAddModal = function() {
    document.getElementById('user-add-modal').hidden = true;
    refreshMgmtModalOverflow();
};

/* ═══════════════════════════════════════════════════════════════════════ */
/* SECTION TABS                                                            */
/* ═══════════════════════════════════════════════════════════════════════ */
window.switchTab = function(tab) {
    document.getElementById('section-bidang').style.display  = tab === 'bidang'  ? '' : 'none';
    document.getElementById('section-armada').style.display  = tab === 'armada'  ? '' : 'none';
    document.getElementById('section-users').style.display   = tab === 'users'   ? '' : 'none';
    document.getElementById('tab-bidang').classList.toggle('active',  tab === 'bidang');
    document.getElementById('tab-armada').classList.toggle('active',  tab === 'armada');
    document.getElementById('tab-users').classList.toggle('active',   tab === 'users');
    if (tab === 'users')  fetchUsers();
    if (tab === 'bidang') loadPortalBidangs();
};

/* ═══════════════════════════════════════════════════════════════════════ */
/* BIDANG / BAGIAN AJAX                                                    */
/* ═══════════════════════════════════════════════════════════════════════ */
const PORTAL_BIDANG_API  = @json(url('/admin/bidangs'));
let portalBidangTree = [];

function escPB(s) { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function escPBJs(s) { return String(s ?? '').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'&quot;').replace(/\n/g,'\\n'); }

function flatPortalBidang(nodes, acc = []) {
    nodes.forEach(n => {
        acc.push({ id:n.id, nama:n.nama, parent_id:n.parent_id??null,
            pimpinan_nama:n.pimpinan_nama||null, pimpinan_email:n.pimpinan_email||null,
            jabatan:n.jabatan||null });
        if (n.children&&n.children.length) flatPortalBidang(n.children,acc);
    }); return acc;
}

async function loadPortalBidangs() {
    document.getElementById('bidang-loading').style.display = 'flex';
    try {
        const res  = await fetch(PORTAL_BIDANG_API, { headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'} });
        const data = await res.json();
        if (!res.ok) { Swal.fire({icon:'error',title:'Gagal',text:data.message||'HTTP '+res.status}); return; }
        portalBidangTree = data.data || [];
        renderPortalBidangTree(portalBidangTree);
        const tc = document.getElementById('tc-bidang');
        if (tc) tc.textContent = String(flatPortalBidang(portalBidangTree).length);
    } catch(e){ console.error(e); }
    finally { document.getElementById('bidang-loading').style.display='none'; }
}

function buildLeaderBadges(n) {
    let p=[];
    if(n.jabatan && n.pimpinan_nama) p.push(`<span class="portal-bidang-badge portal-bidang-mgr-badge">${escPB(n.jabatan)}</span>`);
    else if(n.pimpinan_nama)         p.push(`<span class="portal-bidang-badge portal-bidang-mgr-badge">Pimpinan: ${escPB(n.pimpinan_nama)}</span>`);
    return p.length?`<div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:4px">${p.join('')}</div>`:'';
}

function renderPortalBidangTree(nodes) {
    const el = document.getElementById('portal-bidang-tree');
    if (!nodes.length) { el.innerHTML='<p style="padding:12px 4px;color:#94a3b8;font-size:0.85rem;">Belum ada data bidang. Klik "+ Bidang Utama" untuk memulai.</p>'; return; }
    el.innerHTML = '<ul style="list-style:none;margin:0;padding:0">'+nodes.map(n=>renderPBRoot(n)).join('')+'</ul>';
}

function renderPBRoot(n) {
    const badges = buildLeaderBadges(n);
    const actAdd = `<button type="button" class="mgmt-act-btn portal-bidang-btn-sub" style="padding:5px 10px;font-size:0.75rem" onclick="openBidangModal({lockParent:${n.id},parent_id:${n.id}})"><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> Sub</button>`;
    const actEdit = `<button type="button" class="mgmt-act-btn mgmt-act-edit" style="padding:5px 10px;font-size:0.75rem" onclick="openBidangModal({id:${n.id},nama:'${escPBJs(n.nama)}',parent_id:null,pimpinan_nama:'${escPBJs(n.pimpinan_nama||'')}',pimpinan_email:'${escPBJs(n.pimpinan_email||'')}',jabatan:'${escPBJs(n.jabatan||'')}'})" ><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Edit</button>`;
    const actDel = `<button type="button" class="mgmt-act-btn mgmt-act-del" style="padding:5px 10px;font-size:0.75rem" onclick="deleteBidangPortal(${n.id},'${escPBJs(n.nama)}')"><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Hapus</button>`;
    const subs = (n.children&&n.children.length)?'<ul style="list-style:none;margin:4px 0 0;padding-left:22px;border-left:2px solid rgba(124,58,237,.2)">'+n.children.map(c=>renderPBChild(c)).join('')+'</ul>':'';
    return `<li style="margin-bottom:8px"><div class="portal-bidang-root-row"><div style="flex:1;min-width:0"><strong style="font-size:0.9rem">${escPB(n.nama)}</strong>${badges}</div><div style="display:flex;flex-wrap:wrap;gap:6px;margin-left:auto">${actEdit}${actAdd}${actDel}</div></div>${subs}</li>`;
}

function renderPBChild(n) {
    const badges = buildLeaderBadges(n);
    const actEdit = `<button type="button" class="mgmt-act-btn mgmt-act-edit" style="padding:5px 10px;font-size:0.75rem" onclick="openBidangModal({id:${n.id},nama:'${escPBJs(n.nama)}',parent_id:${n.parent_id},pimpinan_nama:'${escPBJs(n.pimpinan_nama||'')}',pimpinan_email:'${escPBJs(n.pimpinan_email||'')}',jabatan:'${escPBJs(n.jabatan||'')}'})" ><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Edit</button>`;
    const actDel = `<button type="button" class="mgmt-act-btn mgmt-act-del" style="padding:5px 10px;font-size:0.75rem" onclick="deleteBidangPortal(${n.id},'${escPBJs(n.nama)}')"><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Hapus</button>`;
    return `<li style="margin-bottom:6px"><div class="portal-bidang-child-row"><div style="flex:1;min-width:0"><span style="font-size:0.87rem">${escPB(n.nama)}</span>${badges}</div><div style="display:flex;flex-wrap:wrap;gap:6px;margin-left:auto">${actEdit}${actDel}</div></div></li>`;
}

function togglePBLeaderSection() {
    const sel = document.getElementById('portal-bidang-parent');
    const sec = document.getElementById('portal-bidang-leader-section');
    if (!sec) return;
    sec.style.display = sel.value ? 'block' : 'none';
    const jabatanInput = document.getElementById('portal-bidang-pimpinan-jabatan');
    const namaInput   = document.getElementById('portal-bidang-pimpinan-nama');
    const emailInput  = document.getElementById('portal-bidang-pimpinan-email');
    if (sel.value) {
        jabatanInput?.setAttribute('required','');
        namaInput?.setAttribute('required','');
        emailInput?.setAttribute('required','');
    } else {
        jabatanInput?.removeAttribute('required');
        namaInput?.removeAttribute('required');
        emailInput?.removeAttribute('required');
    }
}

function populatePBParents(lockParent) {
    const sel = document.getElementById('portal-bidang-parent');
    sel.innerHTML = '<option value="">\u2014 Bidang utama \u2014</option>';
    portalBidangTree.forEach(r => { const o=document.createElement('option'); o.value=String(r.id); o.textContent=r.nama; sel.appendChild(o); });
    if (lockParent) { sel.value=String(lockParent); sel.disabled=true; }
    else { sel.disabled=false; }
    togglePBLeaderSection();
}

window.openBidangModal = function(opts) {
    const { id, nama, parent_id, lockParent, pimpinan_nama, pimpinan_email, jabatan } = opts||{};
    document.getElementById('portal-bidang-modal-title').textContent = id?'Ubah Bidang':(lockParent?'Tambah Sub-Bidang':'Tambah Bidang Utama');
    document.getElementById('portal-bidang-id').value = id||'';
    document.getElementById('portal-bidang-nama').value = nama||'';

    const jabatanInput = document.getElementById('portal-bidang-pimpinan-jabatan');
    const namaInput   = document.getElementById('portal-bidang-pimpinan-nama');
    const emailInput  = document.getElementById('portal-bidang-pimpinan-email');
    jabatanInput.value = jabatan || '';
    namaInput.value   = pimpinan_nama || '';
    emailInput.value  = pimpinan_email || '';

    populatePBParents(lockParent||null);
    const sel = document.getElementById('portal-bidang-parent');
    if (!lockParent && parent_id!=null && parent_id!=='') sel.value=String(parent_id);
    sel.removeEventListener('change',togglePBLeaderSection);
    sel.addEventListener('change',togglePBLeaderSection);
    togglePBLeaderSection();
    document.getElementById('portal-bidang-modal').hidden=false;
    refreshMgmtModalOverflow();
    setTimeout(()=>document.getElementById('portal-bidang-nama').focus(),80);
};

window.closeBidangModal = function() {
    document.getElementById('portal-bidang-modal').hidden=true;
    document.getElementById('portal-bidang-parent').disabled=false;
    refreshMgmtModalOverflow();
};

window.submitBidangModal = async function() {
    const id  = document.getElementById('portal-bidang-id').value;
    const btn = document.getElementById('btn-save-bidang');
    const psel = document.getElementById('portal-bidang-parent');
    const pv = psel.value;
    const payload = { nama: document.getElementById('portal-bidang-nama').value.trim(), parent_id: pv===''?null:parseInt(pv,10) };
    if (pv !== '') {
        const pimpinanJabatan = document.getElementById('portal-bidang-pimpinan-jabatan').value.trim();
        const pimpinanNama    = document.getElementById('portal-bidang-pimpinan-nama').value.trim();
        const pimpinanEmail   = document.getElementById('portal-bidang-pimpinan-email').value.trim();

        if (!pimpinanNama || !pimpinanEmail) {
            Swal.fire({
                icon: 'warning',
                title: 'Validasi',
                html: 'Nama dan Email <strong>Pimpinan</strong> wajib diisi untuk sub-bidang.',
                confirmButtonColor: '#7c3aed'
            });
            return;
        }

        payload.pimpinan_nama  = pimpinanNama;
        payload.pimpinan_email = pimpinanEmail;
        payload.jabatan        = pimpinanJabatan || null;
    } else {
        payload.pimpinan_nama = null; payload.pimpinan_email = null; payload.jabatan = null;
    }
    btn.disabled=true; const prev=btn.innerHTML; btn.textContent='Menyimpan...';
    try {
        const url=id?(PORTAL_BIDANG_API+'/'+id):PORTAL_BIDANG_API, method=id?'PUT':'POST';
        const res=await fetch(url,{method,headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify(payload)});
        const data=await res.json().catch(()=>({}));
        if (!res.ok) {
            data.errors?Swal.fire({icon:'warning',title:'Validasi',html:Object.values(data.errors).flat().join('<br>'),confirmButtonColor:'#7c3aed'}):Swal.fire({icon:'error',title:'Gagal',text:data.message||'HTTP '+res.status}); return;
        }
        closeBidangModal(); Swal.fire({icon:'success',title:id?'Diperbarui':'Disimpan',timer:1300,showConfirmButton:false}); loadPortalBidangs();
    } catch { Swal.fire({icon:'error',title:'Koneksi Bermasalah',text:'Periksa koneksi internet.'}); }
    finally { btn.disabled=false; btn.innerHTML=prev; }
};

window.deleteBidangPortal = function(id,nama) {
    Swal.fire({
        title:'Hapus Bidang?',html:`<p>Yakin ingin menghapus <strong>${escPB(nama)}</strong>?</p><div style="margin-top:10px;padding:8px;background:#fef9c3;border:1px solid #fde68a;border-radius:8px;font-size:0.82rem;color:#92400e;text-align:left">⚠️ Sub-bidang di dalamnya juga akan dihapus.</div>`,
        icon:'warning',showCancelButton:true,confirmButtonColor:'#ef4444',cancelButtonColor:'#64748b',confirmButtonText:'Ya, Hapus',cancelButtonText:'Batal',
    }).then(async r => {
        if (!r.isConfirmed) return;
        try {
            const res=await fetch(PORTAL_BIDANG_API+'/'+id,{method:'DELETE',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'}});
            const data=await res.json().catch(()=>({}));
            if (!res.ok||!data.success){Swal.fire({icon:'error',title:'Gagal',text:data.message||'Terjadi kesalahan.'});return;}
            Swal.fire({icon:'success',title:'Terhapus',timer:1300,showConfirmButton:false}); loadPortalBidangs();
        } catch { Swal.fire({icon:'error',title:'Koneksi Bermasalah',text:'Periksa koneksi internet.'}); }
    });
};

/* ═══════════════════════════════════════════════════════════════════════ */
/* MASTER ARMADA                                                           */
/* ═══════════════════════════════════════════════════════════════════════ */
let armadaPage = 1, armadaPerPage = 10, armadaSort = '', armadaDir = '';

function updateArmadaFilterChrome() {
    const searchVal = document.getElementById('armada-search')?.value.trim() ?? '';
    const perpageVal = document.getElementById('armada-perpage')?.value ?? '10';
    const showReset = searchVal.length > 0 || perpageVal !== '10';
    const btn = document.getElementById('armada-reset-btn');
    if (btn) btn.style.display = showReset ? '' : 'none';
}

async function fetchArmada(scroll = false) {
    document.getElementById('armada-loading').style.display = 'flex';
    const params = new URLSearchParams({
        search:   document.getElementById('armada-search').value.trim(),
        per_page: armadaPerPage,
        page:     armadaPage,
    });
    if (armadaSort) { params.set('sort', armadaSort); params.set('dir', armadaDir); }
    try {
        const json = await fetch(`${BASE}/api/admin/portal/kendaraan?${params}`).then(r => r.json());
        renderArmadaTable(json.data, json.current_page, json.per_page);
        mountMgmtPagination('armada', json.pagination_html);
        document.getElementById('tc-armada').textContent = json.total;
        if (window.AdminTableSort) window.AdminTableSort.syncAria(document.getElementById('armada-thead'), json.sort ?? null, json.dir ?? null);
        if (scroll) document.getElementById('section-armada').scrollIntoView({behavior:'smooth', block:'start'});
    } catch(e) { console.error(e); }
    finally {
        document.getElementById('armada-loading').style.display = 'none';
        updateArmadaFilterChrome();
    }
}

function renderArmadaTable(rows, page, perPage) {
    const tbody = document.getElementById('armada-tbody');
    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="10" class="mgmt-empty">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="margin:0 auto 8px;display:block;opacity:.3"><path d="M19 17H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5"/></svg>
            Tidak ada data kendaraan.
        </td></tr>`;
        return;
    }
    const offset = (page - 1) * perPage;
    tbody.innerHTML = rows.map((k, i) => `
        <tr id="krow-${k.id}">
            <td class="text-muted">${offset + i + 1}</td>
            <td><span class="mgmt-nopol">${escHtml(k.nomor_kendaraan)}</span></td>
            <td>${escHtml(k.jenis_kendaraan)}</td>
            <td class="text-muted">${k.bidang ? escHtml(k.bidang) : '—'}</td>
            <td>${k.km_current != null && k.km_current !== '' ? numFmt(k.km_current) + ' km' : '<span class="text-muted">—</span>'}</td>
            <td class="mgmt-expiry-cell">${armadaExpiryCellInner(k.tanggal_stnk)}</td>
            <td class="mgmt-expiry-cell">${armadaExpiryCellInner(k.tanggal_pajak_stnk)}</td>
            <td class="mgmt-expiry-cell">${armadaExpiryCellInner(k.tanggal_kir)}</td>
            <td>${armadaStatusPillHtml(k.status_kendaraan)}</td>
            <td class="text-center">
                <div class="mgmt-actions">
                    <button type="button" class="mgmt-act-btn mgmt-act-edit js-armada-edit" data-id="${k.id}" data-nopol="${escHtml(k.nomor_kendaraan ?? '')}" data-jenis="${escHtml(k.jenis_kendaraan ?? '')}" data-bidang="${escHtml(k.bidang ?? '')}" data-km-current="${k.km_current != null && k.km_current !== '' ? Number(k.km_current) : ''}" data-tanggal-stnk="${escHtml(k.tanggal_stnk != null ? String(k.tanggal_stnk).split('T')[0] : '')}" data-tanggal-pajak-stnk="${escHtml(k.tanggal_pajak_stnk != null ? String(k.tanggal_pajak_stnk).split('T')[0] : '')}" data-tanggal-kir="${escHtml(k.tanggal_kir != null ? String(k.tanggal_kir).split('T')[0] : '')}" data-status-kendaraan="${escHtml(k.status_kendaraan ?? 'Aktif')}" title="Edit">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <form id="kdel-${k.id}" action="/admin/portal-manajemen-administrasi/kendaraan/${k.id}" method="POST" style="display:contents" onsubmit="event.preventDefault();deleteKendaraan(${k.id},'${escJs(k.nomor_kendaraan)}')">
                        <input type="hidden" name="_token" value="${CSRF}"><input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="mgmt-act-btn mgmt-act-del" title="Hapus">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </button>
                    </form>
                </div>
            </td>
        </tr>`).join('');
}

function armadaUpdateUrl(id) {
    return ARMADA_UPDATE_URL_TMPL.replace('__ID__', String(id));
}

window.openArmadaAddModal = function() {
    document.getElementById('armada-modal-id').value = '';
    document.getElementById('armada-modal-title').textContent = 'Tambah Kendaraan';
    document.getElementById('armada-modal-sub').textContent = 'Lengkapi data kendaraan operasional';
    document.getElementById('armada-modal-nopol').value = '';
    document.getElementById('armada-modal-jenis').value = '';
    document.getElementById('armada-modal-bidang').value = '';
    document.getElementById('armada-modal-km-label').textContent = 'Set KM';
    document.getElementById('armada-modal-km').value = '0';
    document.getElementById('armada-modal-tanggal-stnk').value = '';
    document.getElementById('armada-modal-tanggal-pajak').value = '';
    document.getElementById('armada-modal-tanggal-kir').value = '';
    document.getElementById('armada-modal-status').value = 'Aktif';
    document.getElementById('armada-form-modal').hidden = false;
    refreshMgmtModalOverflow();
    setTimeout(() => document.getElementById('armada-modal-nopol').focus(), 80);
};

window.openArmadaEditModal = function(id, nopol, jenis, bidang, kmCurrent, tanggalStnk, tanggalPajak, tanggalKir, statusKendaraan) {
    document.getElementById('armada-modal-id').value = String(id);
    document.getElementById('armada-modal-title').textContent = 'Ubah Kendaraan';
    document.getElementById('armada-modal-sub').textContent = nopol || '—';
    document.getElementById('armada-modal-nopol').value = nopol ?? '';
    document.getElementById('armada-modal-jenis').value = jenis ?? '';
    document.getElementById('armada-modal-bidang').value = bidang ?? '';
    document.getElementById('armada-modal-km-label').textContent = 'KM Saat Ini';
    document.getElementById('armada-modal-km').value = kmCurrent != null && kmCurrent !== '' ? String(kmCurrent) : '';
    document.getElementById('armada-modal-tanggal-stnk').value = tanggalStnk || '';
    document.getElementById('armada-modal-tanggal-pajak').value = tanggalPajak || '';
    document.getElementById('armada-modal-tanggal-kir').value = tanggalKir || '';
    document.getElementById('armada-modal-status').value = statusKendaraan && ['Aktif','Maintenance','Non Aktif'].includes(statusKendaraan) ? statusKendaraan : 'Aktif';
    document.getElementById('armada-form-modal').hidden = false;
    refreshMgmtModalOverflow();
    setTimeout(() => document.getElementById('armada-modal-nopol').focus(), 80);
};

window.closeArmadaModal = function() {
    document.getElementById('armada-form-modal').hidden = true;
    refreshMgmtModalOverflow();
};

window.submitArmadaModal = async function() {
    const id = document.getElementById('armada-modal-id').value.trim();
    const btn = document.getElementById('btn-save-armada');
    const nopol = document.getElementById('armada-modal-nopol').value.trim();
    const jenis = document.getElementById('armada-modal-jenis').value.trim();
    const bidang = document.getElementById('armada-modal-bidang').value.trim();
    const kmField = document.getElementById('armada-modal-km').value;
    const fd = new FormData();
    fd.append('_token', CSRF);
    fd.append('nomor_kendaraan', nopol);
    fd.append('jenis_kendaraan', jenis);
    fd.append('bidang', bidang);
    if (id) {
        fd.append('km_current', kmField === '' ? '' : kmField);
    } else {
        fd.append('set_km', kmField === '' ? '0' : kmField);
    }
    fd.append('tanggal_stnk', document.getElementById('armada-modal-tanggal-stnk').value);
    fd.append('tanggal_pajak_stnk', document.getElementById('armada-modal-tanggal-pajak').value);
    fd.append('tanggal_kir', document.getElementById('armada-modal-tanggal-kir').value);
    fd.append('status_kendaraan', document.getElementById('armada-modal-status').value);
    let url = ARMADA_STORE_URL;
    if (id) {
        fd.append('_method', 'PUT');
        url = armadaUpdateUrl(id);
    }
    btn.disabled = true;
    const prevHtml = btn.innerHTML;
    btn.textContent = 'Menyimpan...';
    try {
        const res = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
        const data = await res.json().catch(() => ({}));
        if (res.ok && data.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1600, showConfirmButton: false });
            closeArmadaModal();
            fetchArmada();
        } else {
            const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || 'Terjadi kesalahan.');
            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Koneksi Bermasalah', text: 'Periksa koneksi internet.' });
    } finally {
        btn.disabled = false;
        btn.innerHTML = prevHtml;
    }
};

window.deleteKendaraan = function(id, nopol) {
    Swal.fire({
        title:'Hapus Kendaraan?',
        html:`<p>Yakin ingin menghapus <strong>${nopol}</strong>?</p>`,
        icon:'warning',showCancelButton:true,
        confirmButtonColor:'#ef4444',cancelButtonColor:'#64748b',
        confirmButtonText:'Ya, Hapus',cancelButtonText:'Batal',
    }).then(async r => {
        if (!r.isConfirmed) return;
        const form = document.getElementById('kdel-' + id);
        try {
            const res  = await fetch(form.action,{method:'POST',body:new FormData(form),headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
            const data = await res.json();
            if (res.ok && data.success) {
                Swal.fire({icon:'success',title:'Terhapus!',text:data.message,timer:1500,showConfirmButton:false});
                fetchArmada();
            } else { Swal.fire({icon:'error',title:'Gagal',text:data.message||'Terjadi kesalahan.'}); }
        } catch { Swal.fire({icon:'error',title:'Koneksi Bermasalah',text:'Periksa koneksi internet.'}); }
    });
};

window.resetArmadaFilters = function() {
    document.getElementById('armada-search').value = '';
    document.getElementById('armada-perpage').value = '10';
    armadaPerPage = 10; armadaPage = 1; armadaSort = ''; armadaDir = '';
    fetchArmada();
};

document.getElementById('armada-search').addEventListener('input', debounce(() => { armadaPage = 1; fetchArmada(); }, 350));
document.getElementById('armada-perpage').addEventListener('change', e => { armadaPerPage = parseInt(e.target.value); armadaPage = 1; fetchArmada(); });

/* ─── Bind sortable thead (with retry until AdminTableSort is ready) ─────── */
function bindMgmtTableSort(theadId, getSortState, setSortState, fetchFn) {
    if (window.AdminTableSort) {
        const thead = document.getElementById(theadId);
        const wrap  = thead?.closest('.mgmt-table-wrap') || thead?.closest('[id^="section-"]');
        if (wrap) {
            window.AdminTableSort.bindRoot(wrap, {
                getUrl: () => {
                    const u = new URL(location.href);
                    const s = getSortState();
                    if (s.sort) {
                        u.searchParams.set('sort', s.sort);
                        u.searchParams.set('dir', s.dir);
                    }
                    return u;
                },
                onNavigate: (url) => {
                    const s = getSortState();
                    s.sort = url.searchParams.get('sort') || '';
                    s.dir  = url.searchParams.get('dir')  || '';
                    setSortState(s);
                    fetchFn();
                },
            });
        }
    } else {
        const timer = setInterval(() => {
            if (window.AdminTableSort) {
                clearInterval(timer);
                bindMgmtTableSort(theadId, getSortState, setSortState, fetchFn);
            }
        }, 30);
    }
}

bindMgmtTableSort(
    'armada-thead',
    () => ({ sort: armadaSort, dir: armadaDir }),
    (s) => { armadaSort = s.sort; armadaDir = s.dir; armadaPage = 1; },
    fetchArmada,
);

/* ═══════════════════════════════════════════════════════════════════════ */
/* MANAJEMEN USER                                                          */
/* ═══════════════════════════════════════════════════════════════════════ */
let userPage = 1, userPerPage = 5, userSort = '', userDir = '';

function updateUserFilterChrome() {
    const searchVal = document.getElementById('user-search')?.value.trim() ?? '';
    const roleVal = document.getElementById('user-role-filter')?.value ?? '';
    const perpageVal = document.getElementById('user-perpage')?.value ?? '5';
    const showReset = searchVal.length > 0 || roleVal !== '' || perpageVal !== '5';
    const btn = document.getElementById('user-reset-btn');
    if (btn) btn.style.display = showReset ? '' : 'none';
}

async function fetchUsers(scroll = false) {
    document.getElementById('user-loading').style.display = 'flex';
    const params = new URLSearchParams({
        search:      document.getElementById('user-search').value.trim(),
        role_filter: document.getElementById('user-role-filter').value,
        per_page:    userPerPage,
        page:        userPage,
    });
    if (userSort) { params.set('sort', userSort); params.set('dir', userDir); }
    try {
        const json = await fetch(`${BASE}/api/admin/portal/users?${params}`).then(r => r.json());
        renderUserTable(json.data, json.current_page, json.per_page);
        mountMgmtPagination('users', json.pagination_html);
        document.getElementById('tc-users').textContent = json.total;
        if (window.AdminTableSort) window.AdminTableSort.syncAria(document.getElementById('user-thead'), json.sort ?? null, json.dir ?? null);
        if (scroll) document.getElementById('section-users').scrollIntoView({behavior:'smooth', block:'start'});
    } catch(e) { console.error(e); }
    finally {
        document.getElementById('user-loading').style.display = 'none';
        updateUserFilterChrome();
    }
}

function renderUserTable(rows, page, perPage) {
    const tbody = document.getElementById('user-tbody');
    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="mgmt-empty">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="margin:0 auto 8px;display:block;opacity:.3"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.5"/></svg>
            Tidak ada data user.
        </td></tr>`;
        return;
    }
    const offset = (page - 1) * perPage;
    tbody.innerHTML = rows.map((u, i) => {
        const avatarBg = userMgmtAvatarGradient(u.role);
        const badge = userMgmtRoleBadge(u.role);
        const presence = userMgmtPresenceBadge(!!u.is_online);
        return `
        <tr id="urow-${u.id}">
            <td class="text-muted">${offset + i + 1}</td>
            <td>
                <div class="mgmt-user-cell">
                    <div class="mgmt-user-avatar" style="background:${avatarBg}">${escHtml(u.name.charAt(0).toUpperCase())}</div>
                    <div><p class="mgmt-user-name">${escHtml(u.name)}</p></div>
                </div>
            </td>
            <td><span class="mgmt-username">${escHtml(u.username)}</span></td>
            <td><span class="text-muted" style="font-size:0.85rem">${escHtml(u.email || '-')}</span></td>
            <td>${badge}</td>
            <td>${presence}</td>
            <td class="text-center">
                <div class="mgmt-actions">
                    <button type="button" class="mgmt-act-btn mgmt-act-edit" onclick="openUserEdit(${u.id},'${escJs(u.name)}','${escJs(u.username)}','${escJs(u.email||'')}','${u.role}')">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Edit
                    </button>
                    <button type="button" class="mgmt-act-btn mgmt-act-del" onclick="deleteUser(${u.id},'${escJs(u.name)}')">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Hapus
                    </button>
                    <button type="button" class="mgmt-act-btn mgmt-act-edit" onclick="resetUserPassword(${u.id},'${escJs(u.name)}')">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                        Reset
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

window.resetUserFilters = function() {
    document.getElementById('user-search').value = '';
    document.getElementById('user-role-filter').value = '';
    document.getElementById('user-perpage').value = '5';
    userPerPage = 5; userPage = 1; userSort = ''; userDir = '';
    fetchUsers();
};

document.getElementById('user-search').addEventListener('input', debounce(() => { userPage = 1; fetchUsers(); }, 350));
document.getElementById('user-role-filter').addEventListener('change', () => { userPage = 1; fetchUsers(); });
document.getElementById('user-perpage').addEventListener('change', e => { userPerPage = parseInt(e.target.value); userPage = 1; fetchUsers(); });

bindMgmtTableSort(
    'user-thead',
    () => ({ sort: userSort, dir: userDir }),
    (s) => { userSort = s.sort; userDir = s.dir; userPage = 1; },
    fetchUsers,
);

document.getElementById('form-add-user').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-add-user');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    try {
        const res  = await fetch('{{ route("admin.users.store") }}',{method:'POST',body:new FormData(this),headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
        const data = await res.json();
        if (res.ok && data.success) {
            Swal.fire({icon:'success',title:'Berhasil!',text:data.message,timer:1600,showConfirmButton:false});
            document.getElementById('add-user-name').value = '';
            document.getElementById('add-user-username').value = '';
            document.getElementById('add-user-email').value = '';
            document.getElementById('add-user-role').value = 'driver';
            document.getElementById('add-user-pw').value = '{{ $defaultPassword }}';
            closeUserAddModal(); fetchUsers();
        } else {
            const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message||'Gagal.');
            Swal.fire({icon:'error',title:'Gagal',text:msg});
        }
    } catch { Swal.fire({icon:'error',title:'Koneksi Bermasalah',text:'Periksa koneksi internet.'}); }
    finally {
        btn.disabled = false;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="margin-right:5px"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> Simpan';
    }
});

/* ─── Edit modal ─────────────────────────────────────────────────────── */
window.openUserEdit = function(id, name, username, email, role) {
    document.getElementById('edit-user-id').value  = id;
    document.getElementById('edit-name').value     = name;
    document.getElementById('edit-username').value = username;
    document.getElementById('edit-email').value    = email;
    document.getElementById('edit-role').value     = role;
    document.getElementById('edit-password').value = '';

    // Update modal header
    const av = document.getElementById('modal-avatar');
    av.textContent = name.charAt(0).toUpperCase();
    av.style.background = userMgmtAvatarGradient(role);
    document.getElementById('modal-sub-text').textContent = userMgmtRoleLabel(role) + ' · @' + username;

    document.getElementById('user-edit-modal').hidden = false;
    refreshMgmtModalOverflow();
    setTimeout(() => document.getElementById('edit-name').focus(), 100);
};

window.closeUserModal = function() {
    document.getElementById('user-edit-modal').hidden = true;
    refreshMgmtModalOverflow();
};

window.submitUserEdit = async function() {
    const id  = document.getElementById('edit-user-id').value;
    const btn = document.getElementById('btn-save-edit');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    const fd = new FormData();
    fd.append('_token', CSRF); fd.append('_method', 'PUT');
    fd.append('name',     document.getElementById('edit-name').value);
    fd.append('username', document.getElementById('edit-username').value);
    fd.append('email',    document.getElementById('edit-email').value);
    fd.append('role',     document.getElementById('edit-role').value);
    const pw = document.getElementById('edit-password').value;
    if (pw) fd.append('password', pw);
    try {
        const res  = await fetch(`/admin/users/${id}`,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
        const data = await res.json();
        if (res.ok && data.success) {
            Swal.fire({icon:'success',title:'Diperbarui!',text:data.message,timer:1500,showConfirmButton:false,toast:true,position:'top-end'});
            closeUserModal(); fetchUsers();
        } else {
            const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message||'Gagal.');
            Swal.fire({icon:'error',title:'Gagal',text:msg});
        }
    } catch { Swal.fire({icon:'error',title:'Koneksi Bermasalah',text:'Periksa koneksi internet.'}); }
    finally {
        btn.disabled = false;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="margin-right:5px"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Simpan Perubahan';
    }
};

window.deleteUser = function(id, nama) {
    Swal.fire({
        title:'Hapus User?',
        html:`<p>Yakin ingin menghapus <strong>${nama}</strong>?</p>
              <div style="margin-top:10px;padding:10px;background:#fef9c3;border:1px solid #fde68a;border-radius:8px;font-size:0.82rem;color:#92400e;text-align:left">
                ⚠️ Data yang dibuat oleh user ini tidak akan terhapus.
              </div>`,
        icon:'warning',showCancelButton:true,
        confirmButtonColor:'#ef4444',cancelButtonColor:'#64748b',
        confirmButtonText:'Ya, Hapus',cancelButtonText:'Batal',
    }).then(async r => {
        if (!r.isConfirmed) return;
        const fd = new FormData();
        fd.append('_token', CSRF); fd.append('_method', 'DELETE');
        try {
            const res  = await fetch(`/admin/users/${id}`,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
            const data = await res.json();
            if (res.ok && data.success) {
                Swal.fire({icon:'success',title:'Terhapus!',text:data.message,timer:1500,showConfirmButton:false});
                fetchUsers();
            } else { Swal.fire({icon:'error',title:'Gagal',text:data.message||'Terjadi kesalahan.'}); }
        } catch { Swal.fire({icon:'error',title:'Koneksi Bermasalah',text:'Periksa koneksi internet.'}); }
    });
};

window.resetUserPassword = function(id, nama) {
    Swal.fire({
        title: 'Reset Password?',
        html: `<p>Yakin ingin mereset password <strong>${nama}</strong>?</p>
              <div style="margin-top:10px;padding:10px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:0.82rem;color:#1e3a8a;text-align:left">
                  Password akan direset menjadi default: <strong>{{ $defaultPassword }}</strong>
              </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal',
    }).then(async r => {
        if (!r.isConfirmed) return;
        const fd = new FormData();
        fd.append('_token', CSRF); 
        try {
            const res  = await fetch(`/admin/users/${id}/reset-password`,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
            const data = await res.json();
            if (res.ok && data.success) {
                Swal.fire({icon:'success',title:'Berhasil!',text:data.message,timer:1500,showConfirmButton:false});
            } else { Swal.fire({icon:'error',title:'Gagal',text:data.message||'Terjadi kesalahan.'}); }
        } catch { Swal.fire({icon:'error',title:'Koneksi Bermasalah',text:'Periksa koneksi internet.'}); }
    });
};

/* ─── Init pagination ────────────────────────────────────────────────── */
mountMgmtPagination('armada', INIT_MGMT_PAGINATION.armada);
mountMgmtPagination('users', INIT_MGMT_PAGINATION.users);
updateArmadaFilterChrome();
updateUserFilterChrome();

setInterval(() => {
    if (document.getElementById('user-tbody')) fetchUsers();
}, 60000);

document.getElementById('armada-tbody').addEventListener('click', function (ev) {
    const btn = ev.target.closest('.js-armada-edit');
    if (!btn) return;
    const rawKm = btn.getAttribute('data-km-current');
    const kmCurrent = rawKm !== null && rawKm !== '' && !Number.isNaN(parseInt(rawKm, 10)) ? parseInt(rawKm, 10) : null;
    openArmadaEditModal(
        parseInt(btn.getAttribute('data-id'), 10),
        btn.getAttribute('data-nopol') || '',
        btn.getAttribute('data-jenis') || '',
        btn.getAttribute('data-bidang') || '',
        kmCurrent,
        btn.getAttribute('data-tanggal-stnk') || '',
        btn.getAttribute('data-tanggal-pajak-stnk') || '',
        btn.getAttribute('data-tanggal-kir') || '',
        btn.getAttribute('data-status-kendaraan') || 'Aktif'
    );
});

document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    const bidangM = document.getElementById('portal-bidang-modal');
    const armadaM = document.getElementById('armada-form-modal');
    const userM   = document.getElementById('user-edit-modal');
    if (bidangM && !bidangM.hidden) { closeBidangModal(); return; }
    if (!armadaM.hidden) { closeArmadaModal(); return; }
    if (!userM.hidden) closeUserModal();
});

})();
</script>
@endpush
