@php
    $tbUser         = $tbUser         ?? auth()->user();
    $tbIsSuperAdmin = $tbIsSuperAdmin ?? ($tbUser?->role === 'superadmin');
    $tbIsAdmin      = $tbIsAdmin      ?? ($tbUser?->role === 'admin');
    $tbIsManager    = $tbIsManager    ?? ($tbUser?->role === 'manager');
    $tbIsPic        = $tbIsPic        ?? ($tbUser?->role === 'pic_kendaraan');
    $tbIsDriver     = $tbIsDriver     ?? ($tbUser?->role === 'driver' || $tbIsPic);
    $tbUserName     = $tbUserName     ?? ($tbUser?->name ?? $tbUser?->username ?? 'User');
    $tbUserRoleLabel = $tbIsSuperAdmin ? 'SUPERADMIN' : ($tbIsAdmin ? 'ADMIN' : ($tbIsManager ? 'MANAGER' : ($tbIsPic ? 'PIC KENDARAAN' : 'DRIVER')));
    $tbRoleChipClass = ($tbIsAdmin || $tbIsSuperAdmin) ? 'dash-chip-admin' : ($tbIsManager ? 'dash-chip-manager' : 'dash-chip-driver');

    $tbPageTitle    = $tbPageTitle    ?? null;
    $tbPageSubtitle = $tbPageSubtitle ?? null;
    $tbIsDashboard  = $tbIsDashboard ?? request()->routeIs('dashboard');

    $tbSuperadminNotifications = $tbSuperadminNotifications ?? collect();
    $tbSuperadminUnreadCount   = $tbSuperadminUnreadCount   ?? 0;
@endphp

<header class="dash-topbar" id="dash-topbar">
    <div class="dash-topbar-left">
        <div class="dash-topbar-brand-block">
            <img src="{{ asset('images/VMS.png') }}" alt="VMS" class="dash-topbar-brand-logo" width="120" height="48" fetchpriority="high">
        </div>

        <div class="dash-topbar-title-wrap">
            @hasSection('pageTitle')
                <h1 class="dash-topbar-title">@yield('pageTitle')</h1>
                @hasSection('pageSubtitle')
                    <span class="dash-topbar-subtitle">@yield('pageSubtitle')</span>
                @endif
            @elseif(!empty($tbPageTitle))
                <h1 class="dash-topbar-title">{{ $tbPageTitle }}</h1>
                @if(!empty($tbPageSubtitle))
                    <span class="dash-topbar-subtitle">{{ $tbPageSubtitle }}</span>
                @endif
            @endif
        </div>
    </div>

    <div class="dash-topbar-right">
        {{-- Notifikasi (superadmin only — desktop ≥992px) --}}
        @if($tbIsSuperAdmin)
        <div class="dash-notif-wrap dash-topbar-desktop-only" id="dash-notif-wrap">
            <button type="button"
                    class="dash-notif-btn dash-topbar-icon-btn"
                    id="dash-notif-toggle"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-controls="dash-notif-panel"
                    title="Notifikasi">
                <i class="bi bi-bell-fill" aria-hidden="true"></i>
                @if($tbSuperadminUnreadCount > 0)
                    <span class="dash-notif-badge">{{ $tbSuperadminUnreadCount > 99 ? '99+' : $tbSuperadminUnreadCount }}</span>
                @endif
            </button>
            <div class="dash-notif-panel" id="dash-notif-panel" role="menu" hidden>
                <div class="dash-notif-panel-head">Notifikasi</div>
                <ul class="dash-notif-list">
                    @forelse($tbSuperadminNotifications as $n)
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

        {{-- Theme toggle — semua breakpoints --}}
        <button type="button"
                class="dash-theme-btn"
                id="dash-theme-toggle"
                title="Ganti Tema"
                aria-label="Toggle Tema">
            <i class="bi bi-moon-fill" id="dash-theme-icon"></i>
        </button>

        {{-- Profile button --}}
        <button type="button"
                class="dash-topbar-profile-btn dash-chip {{ $tbRoleChipClass }} dash-topbar-desktop-only"
                id="profile-open-btn"
                onclick="openProfileDrawer()"
                aria-label="Profil Saya"
                title="Profil Saya">
            @include('partials.dash-role-icon', [
                'isAdmin'      => $tbIsAdmin,
                'isSuperAdmin' => $tbIsSuperAdmin,
                'isManager'    => $tbIsManager,
            ])
            <span class="dash-topbar-profile-meta">
                <span class="dash-topbar-profile-name">{{ $tbUserName }}</span>
                <span class="dash-topbar-profile-role">{{ $tbUserRoleLabel }}</span>
            </span>
        </button>

        @if($tbIsDashboard)
        <form method="POST" action="{{ route('logout') }}" class="dash-topbar-logout-form dash-topbar-logout-desktop-only js-logout-form">
            @csrf
            <button type="submit" class="dash-topbar-logout" title="Logout" aria-label="Keluar">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="16,17 21,12 16,7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="dash-topbar-logout-label">Logout</span>
            </button>
        </form>
        @endif

        <button type="button"
                class="dash-topbar-dash-drawer-btn {{ $tbIsDashboard ? 'dash-topbar-drawer-mobile-only' : '' }}"
                id="dash-nav-drawer-open"
                onclick="openDashNavDrawer()"
                title="Dashboard &amp; menu"
                aria-label="Buka menu navigasi"
                aria-expanded="false"
                aria-controls="dash-nav-drawer">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/></svg>
            <span class="dash-topbar-dash-drawer-btn-label">Dashboard</span>
        </button>

    </div>
</header>
