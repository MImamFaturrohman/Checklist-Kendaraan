@php
    /* Nav menu (dash drawer — shared vars with former sidebar) */
    $sbUser          = $sbUser          ?? auth()->user();
    $sbIsSuperAdmin  = $sbIsSuperAdmin  ?? ($sbUser?->role === 'superadmin');
    $sbIsAdmin       = $sbIsAdmin       ?? ($sbUser?->role === 'admin');
    $sbIsManager     = $sbIsManager     ?? ($sbUser?->role === 'manager');
    $sbIsPic         = $sbIsPic         ?? ($sbUser?->role === 'pic_kendaraan');
    $sbIsDriver      = $sbIsDriver      ?? ($sbUser?->role === 'driver' || $sbIsPic);

    $sbPendingCount  = $sbPendingCount ?? 0;
    $sbSppdPending   = $sbSppdPending  ?? 0;

    $sbUserName      = $sbUserName ?? ($sbUser?->name ?? $sbUser?->username ?? 'User');
    $sbUserRoleLabel = $sbIsSuperAdmin ? 'SUPERADMIN' : ($sbIsAdmin ? 'ADMIN' : ($sbIsManager ? 'MANAGER' : ($sbIsPic ? 'PIC KENDARAAN' : 'DRIVER')));

    $sbSuperadminNotifications = $sbSuperadminNotifications ?? collect();
    $sbSuperadminUnreadCount   = $sbSuperadminUnreadCount   ?? 0;

    $navNavClass = $navNavClass ?? 'dash-sidebar-nav dash-nav-drawer-inner-nav';
@endphp

<div class="dash-nav-drawer-account" aria-label="Akun">
    <span class="dash-sidebar-group-label dash-nav-drawer-group-label dash-nav-drawer-account-label">AKUN</span>

    <button type="button"
            class="dash-nav-drawer-profile-btn"
            id="dash-nav-profile-open-btn"
            onclick="openProfileDrawer()"
            aria-label="Profil Saya"
            title="Profil Saya">
        <span class="dash-nav-drawer-profile-avatar">{{ strtoupper(substr($sbUserName, 0, 1)) }}</span>
        <span class="dash-nav-drawer-profile-meta">
            <span class="dash-nav-drawer-profile-name">{{ $sbUserName }}</span>
            <span class="dash-nav-drawer-profile-hint">Profil Saya</span>
        </span>
    </button>

    <div class="dash-nav-drawer-account-chip-notif-wrap">
        <span class="dash-chip dash-nav-drawer-account-chip {{ ($sbIsAdmin || $sbIsSuperAdmin) ? 'dash-chip-admin' : ($sbIsManager ? 'dash-chip-manager' : 'dash-chip-driver') }}">
            @if ($sbIsAdmin || $sbIsSuperAdmin)
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
            @elseif ($sbIsManager)
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            @else
                <i class="bi bi-person-check-fill"></i>
            @endif
            <span class="dash-nav-chip-label">{{ $sbUserRoleLabel }}</span>
        </span>
    
        @if($sbIsSuperAdmin)
        <div class="dash-notif-wrap dash-nav-notif-in-drawer" id="dash-nav-notif-wrap">
            <button type="button"
                    class="dash-notif-btn"
                    id="dash-nav-notif-toggle"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-controls="dash-nav-notif-panel"
                    title="Notifikasi">
                <i class="bi bi-bell-fill" aria-hidden="true"></i>
                @if($sbSuperadminUnreadCount > 0)
                    <span class="dash-notif-badge">{{ $sbSuperadminUnreadCount > 99 ? '99+' : $sbSuperadminUnreadCount }}</span>
                @endif
            </button>
            <div class="dash-notif-panel dash-notif-panel--drawer" id="dash-nav-notif-panel" role="menu" hidden>
                <div class="dash-notif-panel-head">Notifikasi</div>
                <ul class="dash-notif-list">
                    @forelse($sbSuperadminNotifications as $n)
                        @php $d = $n->data; @endphp
                        <li class="dash-notif-item{{ $n->read_at ? '' : ' is-unread' }}">
                            <a href="{{ \App\Support\SuperadminNotificationLink::href($d['url'] ?? null) }}"
                               class="dash-notif-link"
                               data-notification-id="{{ $n->id }}"
                               role="menuitem">
                                <strong class="dash-notif-title">{{ $d['title'] ?? 'Notifikasi' }}</strong>
                                <span class="dash-notif-body">{{ $d['body'] ?? '' }}</span>
                                <time class="dash-notif-time" datetime="{{ $n->created_at?->toIso8601String() }}">
                                    {{ $n->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                </time>
                            </a>
                        </li>
                    @empty
                        <li class="dash-notif-empty">Belum ada notifikasi.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>

<nav class="{{ $navNavClass }}" aria-label="Menu navigasi">

    {{-- ─ MENU UTAMA (semua role) ─ --}}
    <div class="dash-sidebar-group dash-nav-drawer-group">
        <span class="dash-sidebar-group-label dash-nav-drawer-group-label">MENU UTAMA</span>

        <a href="{{ route('dashboard') }}"
           class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
            <span class="dash-sidebar-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/></svg>
            </span>
            <span class="dash-sidebar-label">Dashboard</span>
        </a>
    </div>

    @if($sbIsSuperAdmin || $sbIsAdmin)
    {{-- ─ ADMINISTRASI (admin / superadmin) ─ --}}
    <div class="dash-sidebar-group dash-nav-drawer-group">
        <span class="dash-sidebar-group-label dash-nav-drawer-group-label">ADMINISTRASI</span>

        <a href="{{ route('admin.portal-pemeriksaan') }}"
           class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('admin.portal-pemeriksaan*') ? 'is-active' : '' }}">
            <span class="dash-sidebar-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <span class="dash-sidebar-label">Portal Pemeriksaan</span>
        </a>

        @if($sbIsSuperAdmin)
        <a href="{{ route('admin.peminjaman') }}"
           class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('admin.peminjaman*') ? 'is-active' : '' }}"
           style="position:relative">
            <span class="dash-sidebar-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/><path d="M9 14h6M9 18h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </span>
            <span class="dash-sidebar-label">Peminjaman</span>
            @if($sbPendingCount > 0)
                <span class="dash-sidebar-badge">{{ $sbPendingCount > 99 ? '99+' : $sbPendingCount }}</span>
            @endif
        </a>
        @endif

        <a href="{{ route('admin.sppd.index') }}"
           class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('admin.sppd.*') ? 'is-active' : '' }}">
            <span class="dash-sidebar-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/><line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </span>
            <span class="dash-sidebar-label">TransDinas</span>
        </a>

        @if($sbIsSuperAdmin)
        <a href="{{ route('admin.laporan-kejadian.index') }}"
           class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('admin.laporan-kejadian.*') ? 'is-active' : '' }}">
            <span class="dash-sidebar-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </span>
            <span class="dash-sidebar-label">Laporan Kejadian</span>
        </a>
        @endif
    </div>

        @if($sbIsSuperAdmin)
        {{-- ─ OPERASIONAL ─ --}}
        <div class="dash-sidebar-group dash-nav-drawer-group">
            <span class="dash-sidebar-group-label dash-nav-drawer-group-label">OPERASIONAL</span>

            <a href="{{ route('admin.portal-bbm-operasional') }}"
               class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('admin.portal-bbm-operasional*') ? 'is-active' : '' }}">
                <span class="dash-sidebar-icon">
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
                <span class="dash-sidebar-label">BBM Operasional</span>
            </a>

            <a href="{{ route('admin.vehicle-usage-logs.index') }}"
               class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('admin.vehicle-usage-logs.*') ? 'is-active' : '' }}">
                <span class="dash-sidebar-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><rect x="1" y="3" width="15" height="13" rx="1" stroke="currentColor" stroke-width="2"/><path d="M16 8l4 2 2 5v2h-6V8z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="5.5" cy="18.5" r="2.5" stroke="currentColor" stroke-width="2"/><circle cx="18.5" cy="18.5" r="2.5" stroke="currentColor" stroke-width="2"/></svg>
                </span>
                <span class="dash-sidebar-label">Log Kendaraan</span>
            </a>

            <a href="{{ route('admin.portal-manajemen') }}"
               class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('admin.portal-manajemen*') ? 'is-active' : '' }}">
                <span class="dash-sidebar-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M12 15a3 3 0 100-6 3 3 0 000 6z" stroke="currentColor" stroke-width="2"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" stroke="currentColor" stroke-width="2"/></svg>
                </span>
                <span class="dash-sidebar-label">Manajemen Administrasi</span>
            </a>

            <a href="{{ route('checklists.create') }}"
                class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('checklists.*') ? 'is-active' : '' }}">
                <span class="dash-sidebar-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span class="dash-sidebar-label">Checklist Kendaraan</span>
            </a>
        </div>
        @endif
    @endif

    @if($sbIsManager)
    {{-- ─ TUGAS MANAGER ─ --}}
    <div class="dash-sidebar-group dash-nav-drawer-group">
        <span class="dash-sidebar-group-label dash-nav-drawer-group-label">TUGAS UTAMA</span>

        <a href="{{ route('manager.peminjaman') }}"
           class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('manager.peminjaman*') ? 'is-active' : '' }}">
            <span class="dash-sidebar-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <span class="dash-sidebar-label">Persetujuan Peminjaman</span>
            @if($sbPendingCount > 0)
                <span class="dash-sidebar-badge">{{ $sbPendingCount > 99 ? '99+' : $sbPendingCount }}</span>
            @endif
        </a>

        <a href="{{ route('manager.sppd.index') }}"
           class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('manager.sppd.*') ? 'is-active' : '' }}">
            <span class="dash-sidebar-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/></svg>
            </span>
            <span class="dash-sidebar-label">TransDinas</span>
            @if($sbSppdPending > 0)
                <span class="dash-sidebar-badge">{{ $sbSppdPending }}</span>
            @endif
        </a>

        <a href="{{ route('admin.portal-bbm-operasional') }}"
           class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('admin.portal-bbm-operasional*') ? 'is-active' : '' }}">
            <span class="dash-sidebar-icon">
                <svg width="17" height="17" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
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
            <span class="dash-sidebar-label">BBM Operasional</span>
        </a>

        <a href="{{ route('admin.portal-pemeriksaan') }}"
           class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('admin.portal-pemeriksaan*') ? 'is-active' : '' }}">
            <span class="dash-sidebar-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <span class="dash-sidebar-label">Portal Pemeriksaan</span>
        </a>
    </div>
    @endif

    @if($sbIsDriver)
    {{-- ─ DRIVER / PIC ─ --}}
    <div class="dash-sidebar-group dash-nav-drawer-group">
        <span class="dash-sidebar-group-label dash-nav-drawer-group-label">TUGAS SAYA</span>

        <a href="{{ route('checklists.create') }}"
           class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('checklists.*') ? 'is-active' : '' }}">
            <span class="dash-sidebar-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <span class="dash-sidebar-label">Checklist Kendaraan</span>
        </a>

        <a href="{{ route('sppd.index') }}"
           class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('sppd.*') && !request()->routeIs('admin.sppd.*') && !request()->routeIs('manager.sppd.*') ? 'is-active' : '' }}">
            <span class="dash-sidebar-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/></svg>
            </span>
            <span class="dash-sidebar-label">TransDinas</span>
        </a>

        <a href="{{ route('bbm-reports.create') }}"
           class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('bbm-reports.*') ? 'is-active' : '' }}">
            <span class="dash-sidebar-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M6 20V10M18 20V10M4 20h16M8 10V6a2 2 0 012-2h4a2 2 0 012 2v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </span>
            <span class="dash-sidebar-label">Laporan BBM</span>
        </a>

        <a href="{{ route('vehicle-usage-logs.create') }}"
           class="dash-sidebar-link dash-nav-drawer-link {{ request()->routeIs('vehicle-usage-logs.*') ? 'is-active' : '' }}">
            <span class="dash-sidebar-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><rect x="1" y="3" width="15" height="13" rx="1" stroke="currentColor" stroke-width="2"/><path d="M16 8l4 2 2 5v2h-6V8z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="5.5" cy="18.5" r="2.5" stroke="currentColor" stroke-width="2"/><circle cx="18.5" cy="18.5" r="2.5" stroke="currentColor" stroke-width="2"/></svg>
            </span>
            <span class="dash-sidebar-label">Log Penggunaan Kendaraan</span>
        </a>
    </div>
    @endif

</nav>
