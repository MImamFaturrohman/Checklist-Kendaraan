@extends('layouts.dash-app')

@php
    $user          = auth()->user();
    $isSuperAdmin  = $user?->role === 'superadmin';
    $isAdmin       = $user?->role === 'admin';
    $isManager     = $user?->role === 'manager';
    $isPic         = $user?->role === 'pic_kendaraan';
    $isDriver      = $user?->role === 'driver' || $isPic;
    $userRoleLabel = $isSuperAdmin ? 'SUPERADMIN' : ($isAdmin ? 'ADMIN' : ($isManager ? 'MANAGER' : ($isPic ? 'PIC KENDARAAN' : 'DRIVER')));
    $userName      = $user?->name ?? $user?->username ?? 'User';

    if (!isset($pendingCount)) {
        $pendingCount = 0;
        if ($isManager || $isAdmin || $isSuperAdmin) {
            $pendingCount = \App\Models\PeminjamanRequest::where('status', 'pending')->count();
        }
    }
    if (!isset($sppdPendingManager)) {
        $sppdPendingManager = 0;
        if ($isManager) {
            $sppdPendingManager = \App\Models\Sppd::where('status', 'pending_manager')->count();
        }
    }

    $premiumBgId = 'dashmain';
@endphp

@section('title', 'Dashboard')

@section('pageTitle', 'Vehicle Management system')
@section('pageSubtitle', 'Port Management')

{{-- ══ HERO SECTION ══ --}}
@section('hero')
<section class="dash-hero-section">
    <div class="dash-hero-inner">
        <div class="dash-hero-left">
            @if($isSuperAdmin)
                <p class="dash-hero-kicker">
                    <span class="dash-hero-kicker-dot"></span>
                    AKSES SUPERADMIN
                </p>
                <h2 class="dash-hero-name">Fleet Hub</h2>
            @elseif($isAdmin)
                <p class="dash-hero-kicker">
                    <span class="dash-hero-kicker-dot"></span>
                    AKSES ADMIN
                </p>
                <h2 class="dash-hero-name">Portal Pemeriksaan</h2>
            @elseif($isManager)
                <p class="dash-hero-kicker">
                    <span class="dash-hero-kicker-dot"></span>
                    AKSES MANAGER
                </p>
                <h2 class="dash-hero-name">Panel Persetujuan</h2>
            @else
                <p class="dash-hero-kicker" style="text-transform: none;">
                    <span class="dash-hero-kicker-dot"></span>
                    Selamat bertugas,
                </p>
                <h2 class="dash-hero-name">{{ $userName }}</h2>
            @endif

            <div class="dash-hero-tags">
                <span class="dash-tag dash-tag-outline">
                    {{ 'ID: ' . str($user?->username ?? 'USER-00') }}
                </span>
                @if(!$isDriver)
                <span class="mgmt-presence mgmt-presence--online" id="dash-presence-status">
                    <span class="mgmt-presence-dot" aria-hidden="true"></span>
                    <span id="dash-presence-label">Online</span>
                </span>
                @endif
                @if($isDriver)
                    <span class="dash-hero-shift dash-clock-shift" id="dash-hero-shift">—</span>
                @endif
                @if(($isSuperAdmin || $isManager) && $pendingCount > 0)
                    <span class="dash-tag" style="background:rgba(239,68,68,0.18);color:#fca5a5;border:1px solid rgba(239,68,68,0.35)">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" style="display:inline;vertical-align:middle;margin-right:3px"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        {{ $pendingCount }} Request Menunggu
                    </span>
                @endif
            </div>
        </div>

        @if($isDriver)
        <div class="dash-clock-widget" id="dash-clock-widget">
            <div class="dash-clock-date-row">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <div class="dash-clock-date" id="dash-clock-date">—</div>
            </div>
            <div class="dash-clock-time-row">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 7v5l3 3"></path>
                </svg>
                <span class="dash-clock-time" id="dash-clock-time">00:00 WIB</span>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

{{-- ══ MAIN CONTENT ══ --}}
@section('content')
<div class="dash-shell">
    <main class="dash-content">
        <div class="dash-desktop-grid {{ ($isAdmin || $isSuperAdmin || $isManager || $isDriver) ? 'dash-desktop-grid--single' : '' }}">
            <div class="dash-main-column">

                @if($isManager)
                <section>
                    <h3 class="dash-section-title">TUGAS UTAMA</h3>
                    <div class="dash-main-grid-admin">
                        <a href="{{ route('manager.peminjaman') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">Persetujuan Peminjaman</p>
                                <p class="dash-main-sub">
                                    @if($pendingCount > 0)
                                        {{ $pendingCount }} request menunggu persetujuan Anda
                                    @else
                                        Semua request sudah diproses
                                    @endif
                                </p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true" style="position:relative">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                @if($pendingCount > 0)<span class="dash-pending-dot"></span>@endif
                            </span>
                        </a>
                        <a href="{{ route('manager.sppd.index') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">TransDinas</p>
                                <p class="dash-main-sub">
                                    @if($sppdPendingManager > 0)
                                        {{ $sppdPendingManager }} laporan menunggu persetujuan
                                    @else
                                        Tidak ada antrian rekap SPPD
                                    @endif
                                </p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true" style="position:relative">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/><path d="M9 14h6M9 18h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                @if($sppdPendingManager > 0)<span class="dash-pending-dot"></span>@endif
                            </span>
                        </a>
                        <a href="{{ route('admin.portal-bbm-operasional') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">Insight BBM Operasional</p>
                                <p class="dash-main-sub">Ringkasan kartu &amp; grafik liter / biaya pengisian</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M6 20V10M18 20V10M4 20h16M8 10V6a2 2 0 012-2h4a2 2 0 012 2v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M9 14h.01M15 14h.01" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                            </span>
                        </a>
                        <a href="{{ route('admin.portal-pemeriksaan') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">Insight Pemeriksaan Kendaraan</p>
                                <p class="dash-main-sub">Ringkasan ceklist, unit, shift &amp; grafik BBM</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><ellipse cx="12" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="2"/><path d="M5 5V19C5 20.7 8.1 22 12 22C15.9 22 19 20.7 19 19V5" stroke="currentColor" stroke-width="2"/><path d="M5 12C5 13.7 8.1 15 12 15C15.9 15 19 13.7 19 12" stroke="currentColor" stroke-width="2"/></svg>
                            </span>
                        </a>
                    </div>
                </section>

                @elseif($isSuperAdmin)
                <section>
                    <h3 class="dash-section-title">TUGAS UTAMA</h3>
                    <div class="dash-main-grid-admin">
                        <a href="{{ route('admin.portal-pemeriksaan') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">Portal Pemeriksaan Kendaraan</p>
                                <p class="dash-main-sub">Database, foto fisik &amp; arsip PDF</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><ellipse cx="12" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="2"/><path d="M5 5V19C5 20.7 8.1 22 12 22C15.9 22 19 20.7 19 19V5" stroke="currentColor" stroke-width="2"/><path d="M5 12C5 13.7 8.1 15 12 15C15.9 15 19 13.7 19 12" stroke="currentColor" stroke-width="2"/></svg>
                            </span>
                        </a>
                        <a href="{{ route('admin.peminjaman') }}" class="dash-main-card dash-pressable" style="position:relative">
                            <div>
                                <p class="dash-main-title">Peminjaman Kendaraan</p>
                                <p class="dash-main-sub">Daftar permohonan &amp; unduh PDF</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/><path d="M9 12h6M9 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                            @if($pendingCount > 0)<span class="dash-pending-dot" style="top:18px;right:18px"></span>@endif
                        </a>
                        <a href="{{ route('admin.laporan-kejadian.index') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">Laporan Kejadian</p>
                                <p class="dash-main-sub">Daftar laporan incident / near miss &amp; unduh PDF</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                        </a>
                        <a href="{{ route('admin.vehicle-usage-logs.index') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">Log Pemakaian Kendaraan</p>
                                <p class="dash-main-sub">Riwayat jam pakai &amp; keperluan dari driver</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                        </a>
                        <a href="{{ route('admin.portal-manajemen') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">Portal Manajemen Administrasi</p>
                                <p class="dash-main-sub">Master armada &amp; Manajemen user</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                        </a>
                        <a href="{{ route('admin.portal-bbm-operasional') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">Log BBM</p>
                                <p class="dash-main-sub">Insight liter, biaya &amp; laporan pengisian BBM</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M18 56V14C18 10.6863 20.6863 8 24 8H38C41.3137 8 44 10.6863 44 14V56" 
                                            stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                    <rect x="24" y="16" width="14" height="12" rx="2" 
                                            stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
                                    <path d="M14 56H48" 
                                            stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    <path d="M44 20H50C52.2091 20 54 21.7909 54 24V42C54 44.2091 52.2091 46 50 46C47.7909 46 46 44.2091 46 42V34" 
                                            stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M44 34H54" 
                                            stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    <path d="M31 35C31 35 25 42 25 47C25 50.3137 27.6863 53 31 53C34.3137 53 37 50.3137 37 47C37 42 31 35 31 35Z" 
                                            stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </a>
                        <a href="{{ route('checklists.create') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">Buat Ceklist Baru</p>
                                <p class="dash-main-sub">Mulai inspeksi unit hari ini</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><rect x="5" y="4" width="14" height="16" rx="2" stroke="currentColor" stroke-width="2"/><path d="M9 2H15V6H9V2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M9 12L11.2 14.2L15 10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </a>
                        <a href="{{ route('admin.sppd.index') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">TransDinas</p>
                                <p class="dash-main-sub">Verifikasi biaya dinas driver</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/><path d="M9 14h6M9 18h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                        </a>
                    </div>
                </section>

                @elseif($isAdmin)
                <section>
                    <h3 class="dash-section-title">TUGAS UTAMA</h3>
                    <div class="dash-main-grid-admin">
                        <a href="{{ route('admin.portal-pemeriksaan') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">Portal Pemeriksaan Kendaraan</p>
                                <p class="dash-main-sub">Lihat info ringkas dan chart pemeriksaan</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><ellipse cx="12" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="2"/><path d="M5 5V19C5 20.7 8.1 22 12 22C15.9 22 19 20.7 19 19V5" stroke="currentColor" stroke-width="2"/><path d="M5 12C5 13.7 8.1 15 12 15C15.9 15 19 13.7 19 12" stroke="currentColor" stroke-width="2"/></svg>
                            </span>
                        </a>
                        <a href="{{ route('admin.sppd.index') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">TransDinas</p>
                                <p class="dash-main-sub">Verifikasi laporan biaya dinas driver</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/><path d="M9 14h6M9 18h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                        </a>
                    </div>
                </section>

                @else
                {{-- Driver / PIC --}}
                <section>
                    <h3 class="dash-section-title">TUGAS UTAMA</h3>
                    <div class="dash-main-grid-admin">
                        <a href="{{ route('checklists.create') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">Buat Ceklist Baru</p>
                                <p class="dash-main-sub">Mulai inspeksi unit hari ini</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><rect x="5" y="4" width="14" height="16" rx="2" stroke="currentColor" stroke-width="2"/><path d="M9 2H15V6H9V2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M9 12L11.2 14.2L15 10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </a>
                        <a href="{{ route('sppd.index') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">TransDinas</p>
                                <p class="dash-main-sub">Laporan tol, dan BBM</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/><path d="M9 14h6M9 18h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                        </a>
                        @if ($user?->role === 'driver' || $user?->role === 'pic_kendaraan')
                        <a href="{{ route('bbm-reports.create') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">Form Pengisian BBM</p>
                                <p class="dash-main-sub">Laporan liter, struk &amp; foto odometer</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M18 56V14C18 10.6863 20.6863 8 24 8H38C41.3137 8 44 10.6863 44 14V56" 
                                            stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                    <rect x="24" y="16" width="14" height="12" rx="2" 
                                            stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
                                    <path d="M14 56H48" 
                                            stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    <path d="M44 20H50C52.2091 20 54 21.7909 54 24V42C54 44.2091 52.2091 46 50 46C47.7909 46 46 44.2091 46 42V34" 
                                            stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M44 34H54" 
                                            stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    <path d="M31 35C31 35 25 42 25 47C25 50.3137 27.6863 53 31 53C34.3137 53 37 50.3137 37 47C37 42 31 35 31 35Z" 
                                            stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </a>
                        @endif
                        @if ($user?->role === 'driver')
                        <a href="{{ route('vehicle-usage-logs.create') }}" class="dash-main-card dash-pressable">
                            <div>
                                <p class="dash-main-title">Log Penggunaan Kendaraan</p>
                                <p class="dash-main-sub">Catat jam pakai unit &amp; keperluan</p>
                            </div>
                            <span class="dash-main-icon" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><rect x="1" y="3" width="15" height="13" rx="1" stroke="currentColor" stroke-width="2"/><path d="M16 8l4 2 2 5v2h-6V8z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="5.5" cy="18.5" r="2.5" stroke="currentColor" stroke-width="2"/><circle cx="18.5" cy="18.5" r="2.5" stroke="currentColor" stroke-width="2"/></svg>
                            </span>
                        </a>
                        @endif
                    </div>
                </section>
                @endif

            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')

<script>
/* Pressable feedback is handled by app.js (turbo:load) */

@if($isDriver)
/* ── Live clock (driver / PIC) ── */
(function () {
    /* Guard against multiple intervals on Turbo back-navigation */
    if (window._dashClockInterval) {
        clearInterval(window._dashClockInterval);
        window._dashClockInterval = null;
    }
    const DAYS   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const MONTHS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    function getShift(hour) {
        if (hour >= 7  && hour < 12) return { label: 'Shift Pagi',  cls: 'shift-pagi' };
        if (hour >= 12 && hour < 16) return { label: 'Shift Siang', cls: 'shift-siang' };
        return { label: 'Di Luar Shift', cls: 'shift-none' };
    }
    function tick() {
        const now   = new Date();
        const hh    = String(now.getHours()).padStart(2, '0');
        const mm    = String(now.getMinutes()).padStart(2, '0');
        const shift = getShift(now.getHours());
        const dateEl  = document.getElementById('dash-clock-date');
        const timeEl  = document.getElementById('dash-clock-time');
        const shiftEl = document.getElementById('dash-hero-shift');
        if (dateEl) dateEl.textContent = `${DAYS[now.getDay()]}, ${now.getDate()} ${MONTHS[now.getMonth()]} ${now.getFullYear()}`;
        if (timeEl) timeEl.textContent = `${hh}:${mm} WIB`;
        if (shiftEl) { shiftEl.textContent = shift.label; shiftEl.className = 'dash-hero-shift dash-clock-shift ' + shift.cls; }
    }
    tick();
    window._dashClockInterval = setInterval(tick, 1000);

    /* Clean up before Turbo caches this page — use central registry to avoid stacking */
    if (typeof window.registerTurboCleanup === 'function') {
        window.registerTurboCleanup(function () {
            if (window._dashClockInterval) { clearInterval(window._dashClockInterval); window._dashClockInterval = null; }
        });
    } else {
        document.addEventListener('turbo:before-cache', function () {
            if (window._dashClockInterval) { clearInterval(window._dashClockInterval); window._dashClockInterval = null; }
        }, { once: true });
    }
})();
@endif

/* ── Spin icon keyframe (injected once) ── */
if (!document.getElementById('vms-spin-style')) {
    const _st = document.createElement('style');
    _st.id = 'vms-spin-style';
    _st.textContent = '@keyframes spinIcon{to{transform:rotate(360deg)}}.spin-icon{animation:spinIcon 1s linear infinite}';
    document.head.appendChild(_st);
}
</script>
@endpush
