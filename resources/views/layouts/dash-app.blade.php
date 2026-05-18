<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') — @endif{{ config('app.name', 'VMS') }}</title>
    @include('partials.favicon')

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

    <!-- Vite assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="dash-body @yield('bodyClass')">

@php
    /* ── Shared role state (passed from views or computed here) ── */
    $layoutUser           = auth()->user();
    $layoutIsSuperAdmin   = $layoutUser?->role === 'superadmin';
    $layoutIsAdmin        = $layoutUser?->role === 'admin';
    $layoutIsManager      = $layoutUser?->role === 'manager';
    $layoutIsPic          = $layoutUser?->role === 'pic_kendaraan';
    $layoutIsDriver       = $layoutUser?->role === 'driver' || $layoutIsPic;
    $layoutUserName       = $layoutUser?->name ?? $layoutUser?->username ?? 'User';

    /* Role label and @username for profile drawer */
    $layoutRoleLabel = match($layoutUser?->role) {
        'superadmin'    => 'SUPERADMIN',
        'admin'         => 'ADMIN',
        'manager'       => 'MANAGER',
        'pic_kendaraan' => 'PIC KENDARAAN',
        'driver'        => 'DRIVER',
        default         => strtoupper($layoutUser?->role ?? 'USER'),
    };
    $layoutUsernameHandle = '@' . ($layoutUser?->username ?? '');

    $layoutRoleChipClass = match ($layoutUser?->role) {
        'driver'        => 'profile-drawer-role-chip--driver',
        'pic_kendaraan' => 'profile-drawer-role-chip--driver',
        'admin'         => 'profile-drawer-role-chip--admin',
        'superadmin'    => 'profile-drawer-role-chip--admin',
        'manager'       => 'profile-drawer-role-chip--manager',
        default         => 'profile-drawer-role-chip--default',
    };

    /* Pending counts — use view-passed values when available */
    $layoutPendingCount   = $pendingCount ?? 0;
    $layoutSppdPending    = $sppdPendingManager ?? 0;

    /* Notifications — view-passed or empty */
    $layoutNotifications  = $superadminNotifications ?? collect();
    $layoutUnreadCount    = $superadminUnreadCount ?? 0;

    $layoutIsDashboard    = request()->routeIs('dashboard');
@endphp

{{-- ══ MAIN CONTENT AREA ══ --}}
<div class="dash-layout-main" id="dash-layout-main">

    {{-- Scoped premium background layers --}}
    @include('partials.premium-dash-bg', [
        'premiumBgId' => $premiumBgId ?? 'vmsdash'
    ])

    {{-- ── Topbar ── --}}
    @include('partials.dash-topbar', [
        'tbUser'                     => $layoutUser,
        'tbIsSuperAdmin'             => $layoutIsSuperAdmin,
        'tbIsAdmin'                  => $layoutIsAdmin,
        'tbIsManager'                => $layoutIsManager,
        'tbIsPic'                    => $layoutIsPic,
        'tbIsDriver'                 => $layoutIsDriver,
        'tbUserName'                 => $layoutUserName,
        'tbPageTitle'                => $pageTitle ?? null,
        'tbPageSubtitle'             => $pageSubtitle ?? null,
        'tbSuperadminNotifications'  => $layoutNotifications,
        'tbSuperadminUnreadCount'    => $layoutUnreadCount,
        'tbIsDashboard'              => $layoutIsDashboard,
    ])

    {{-- ── Optional hero section (views can override) ── --}}
    @yield('hero')

    {{-- ── Page content ── --}}
    <main class="dash-layout-content">
        @yield('content')
    </main>

</div>

{{-- ══ NAV DRAWER (kanan — akun/notifikasi pada mobile, menu, keluar) ══ --}}
<div class="dash-nav-overlay" id="dash-nav-overlay" style="display:none" onclick="closeDashNavDrawer()" aria-hidden="true"></div>
<aside class="dash-nav-drawer" id="dash-nav-drawer" aria-label="Menu aplikasi">
    <div class="dash-nav-drawer-heading">
        <h2 class="dash-nav-drawer-title">Navigasi</h2>
        <button type="button" class="dash-nav-drawer-close" onclick="closeDashNavDrawer()" aria-label="Tutup menu navigasi">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
        </button>
    </div>
    <div class="dash-nav-drawer-body">
        <div class="dash-nav-glass-card">
            @include('partials.dash-nav-menu', [
                'sbUser'                     => $layoutUser,
                'sbIsSuperAdmin'             => $layoutIsSuperAdmin,
                'sbIsAdmin'                  => $layoutIsAdmin,
                'sbIsManager'                => $layoutIsManager,
                'sbIsPic'                    => $layoutIsPic,
                'sbIsDriver'                 => $layoutIsDriver,
                'sbPendingCount'             => $layoutPendingCount,
                'sbSppdPending'              => $layoutSppdPending,
                'sbUserName'                 => $layoutUserName,
                'sbSuperadminNotifications'  => $layoutNotifications,
                'sbSuperadminUnreadCount'    => $layoutUnreadCount,
            ])
        </div>
    </div>
    <div class="dash-nav-drawer-footer">
        <form method="POST" action="{{ route('logout') }}" class="dash-nav-drawer-logout-form">
            @csrf
            <button type="submit" class="dash-nav-drawer-logout-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="16,17 21,12 16,7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Keluar dari akun
            </button>
        </form>
    </div>
</aside>

{{-- ══ PROFILE DRAWER (global — available on all pages) ══ --}}
<div class="profile-overlay" id="profile-overlay" style="display:none" onclick="closeProfileDrawer()"></div>
<aside class="profile-drawer" id="profile-drawer" aria-label="Profil Pengguna">
    <div class="profile-drawer-header">
        <button class="profile-drawer-close" onclick="closeProfileDrawer()" aria-label="Tutup profil">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
        </button>
        <div class="profile-drawer-avatar-outer">
            <div class="profile-drawer-avatar-ring">
                <div class="profile-drawer-avatar">{{ strtoupper(substr($layoutUserName, 0, 1)) }}</div>
            </div>
            <span class="profile-drawer-avatar-badge" title="Online"></span>
        </div>
        <h2 class="profile-drawer-name" id="profile-display-name">{{ $layoutUserName }}</h2>
        <div class="profile-drawer-meta">
            <span class="profile-drawer-role-chip {{ $layoutRoleChipClass }}">
                @if ($layoutIsAdmin || $layoutIsSuperAdmin)
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
                @elseif ($layoutIsManager)
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @else
                    <i class="bi bi-person-check-fill"></i>
                @endif
                {{ $layoutRoleLabel }}
            </span>
            <span class="profile-drawer-username-handle">{{ $layoutUsernameHandle }}</span>
        </div>
    </div>
    <div class="profile-drawer-body">
        <div id="profile-alert" class="profile-alert" style="display:none"></div>

        <div class="profile-card">
            <div class="profile-card-header">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                Informasi Profil
            </div>
            <div class="profile-card-body">
                <div class="profile-field">
                    <label class="profile-label" for="profile-name">Nama Lengkap</label>
                    <div class="profile-input-wrap">
                        <svg class="profile-input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                        <input type="text" id="profile-name" class="profile-input has-icon" value="{{ $layoutUserName }}" placeholder="Nama Lengkap">
                    </div>
                </div>
                <div class="profile-field">
                    <label class="profile-label" for="profile-username-field">Username</label>
                    <div class="profile-input-wrap">
                        <svg class="profile-input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <input type="text" id="profile-username-field" class="profile-input has-icon" value="{{ $layoutUser?->username }}" disabled>
                    </div>
                    <p class="profile-hint">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" style="display:inline;vertical-align:middle;margin-right:2px"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Username tidak dapat diubah.
                    </p>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <button type="button" class="profile-pw-accordion" id="profile-pw-toggle" onclick="togglePwSection()">
                <div style="display:flex;align-items:center;gap:8px">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/><path d="M7 11V7a5 5 0 0110 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Ganti Password
                </div>
                <svg id="pw-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" style="transition:transform .25s;flex-shrink:0"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div id="profile-pw-section" class="profile-pw-body" style="display:none">
                <div class="profile-field">
                    <label class="profile-label" for="profile-current-pw">Password Saat Ini</label>
                    <div class="profile-input-wrap">
                        <svg class="profile-input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/><path d="M7 11V7a5 5 0 0110 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <input type="password" id="profile-current-pw" class="profile-input has-icon" placeholder="Masukkan password lama" autocomplete="current-password">
                        <button type="button" class="profile-eye-btn" onclick="profileToggleEye('profile-current-pw', this)" tabindex="-1">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                        </button>
                    </div>
                </div>
                <div class="profile-field">
                    <label class="profile-label" for="profile-new-pw">Password Baru</label>
                    <div class="profile-input-wrap">
                        <svg class="profile-input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
                        <input type="password" id="profile-new-pw" class="profile-input has-icon" placeholder="Min. 6 karakter" autocomplete="new-password" oninput="updatePwStrength(this.value)">
                        <button type="button" class="profile-eye-btn" onclick="profileToggleEye('profile-new-pw', this)" tabindex="-1">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                        </button>
                    </div>
                    <div id="profile-pw-strength-wrap" class="profile-pw-strength-wrap" style="display:none">
                        <div class="profile-pw-strength">
                            <div class="profile-pw-strength-bar" id="pw-strength-bar"></div>
                        </div>
                        <p class="profile-hint" id="pw-strength-label" style="margin-top:3px"></p>
                    </div>
                </div>
                <div class="profile-field">
                    <label class="profile-label" for="profile-confirm-pw">Konfirmasi Password Baru</label>
                    <div class="profile-input-wrap">
                        <svg class="profile-input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
                        <input type="password" id="profile-confirm-pw" class="profile-input has-icon" placeholder="Ulangi password baru" autocomplete="new-password">
                        <button type="button" class="profile-eye-btn" onclick="profileToggleEye('profile-confirm-pw', this)" tabindex="-1">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="profile-drawer-footer">
        <button type="button" class="profile-btn-cancel" onclick="closeProfileDrawer()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Tutup
        </button>
        <button type="button" class="profile-btn-save" id="profile-save-btn" onclick="saveProfile()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Simpan Perubahan
        </button>
    </div>
</aside>

{{-- Additional modals / overlays from views --}}
@yield('modals')

{{-- FOOTER --}}
@include('partials.footer')

@push('scripts')
<script>
/* ── Nav drawer (kanan — semua halaman; blok akun di dalam menu untuk ≤991px) ── */
function closeDashNavDrawer() {
    const overlay = document.getElementById('dash-nav-overlay');
    const drawer  = document.getElementById('dash-nav-drawer');
    const openBtn = document.getElementById('dash-nav-drawer-open');
    if (!overlay || !drawer) return;
    overlay.style.display = 'none';
    overlay.setAttribute('aria-hidden', 'true');
    drawer.classList.remove('open');
    drawer.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
}
function openDashNavDrawer() {
    const overlay = document.getElementById('dash-nav-overlay');
    const drawer  = document.getElementById('dash-nav-drawer');
    const openBtn = document.getElementById('dash-nav-drawer-open');
    if (!overlay || !drawer) return;
    closeProfileDrawer();
    overlay.style.display = 'block';
    overlay.setAttribute('aria-hidden', 'false');
    drawer.classList.add('open');
    drawer.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
}
document.addEventListener('DOMContentLoaded', function () {
    const drawer = document.getElementById('dash-nav-drawer');
    if (!drawer) return;
    drawer.querySelectorAll('a.dash-nav-drawer-link').forEach(function (link) {
        link.addEventListener('click', function () {
            closeDashNavDrawer();
        });
    });
});

/* ── Profile Drawer (global) ── */
function openProfileDrawer() {
    closeDashNavDrawer();
    document.getElementById('profile-overlay').style.display = 'block';
    document.getElementById('profile-drawer').classList.add('open');
    document.body.style.overflow = 'hidden';
    document.getElementById('profile-alert').style.display = 'none';
    const np = document.getElementById('profile-new-pw');
    updatePwStrength(np ? np.value : '');
    setTimeout(() => document.getElementById('profile-name')?.focus(), 280);
}
function closeProfileDrawer() {
    document.getElementById('profile-overlay').style.display = 'none';
    document.getElementById('profile-drawer').classList.remove('open');
    document.body.style.overflow = '';
}
function togglePwSection() {
    const sec = document.getElementById('profile-pw-section');
    const chv = document.getElementById('pw-chevron');
    const open = sec.style.display === 'none';
    sec.style.display = open ? 'block' : 'none';
    chv.style.transform = open ? 'rotate(180deg)' : '';
}
function profileToggleEye(inputId, btn) {
    const el = document.getElementById(inputId);
    const hide = el.type === 'password';
    el.type = hide ? 'text' : 'password';
    btn.innerHTML = hide
        ? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'
        : '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>';
}
function updatePwStrength(pw) {
    const wrap  = document.getElementById('profile-pw-strength-wrap');
    const bar   = document.getElementById('pw-strength-bar');
    const label = document.getElementById('pw-strength-label');
    if (!bar || !wrap) return;
    if (!pw) {
        bar.style.width = '0%';
        if (label) label.textContent = '';
        wrap.style.display = 'none';
        return;
    }
    wrap.style.display = 'block';
    let score = 0;
    if (pw.length >= 6)        score++;
    if (pw.length >= 10)       score++;
    if (/[A-Z]/.test(pw))     score++;
    if (/[0-9]/.test(pw))     score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    const levels = [
        { w: '20%', c: '#ef4444', t: 'Sangat Lemah' },
        { w: '40%', c: '#f97316', t: 'Lemah' },
        { w: '60%', c: '#eab308', t: 'Sedang' },
        { w: '80%', c: '#22c55e', t: 'Kuat' },
        { w: '100%', c: '#15803d', t: 'Sangat Kuat' },
    ];
    const lv = levels[Math.min(score - 1, 4)];
    bar.style.width      = lv.w;
    bar.style.background = lv.c;
    label.textContent    = lv.t;
    label.style.color    = lv.c;
}
async function saveProfile() {
    const btn     = document.getElementById('profile-save-btn');
    const alertEl = document.getElementById('profile-alert');
    const name    = document.getElementById('profile-name').value.trim();
    const curPw   = document.getElementById('profile-current-pw').value;
    const newPw   = document.getElementById('profile-new-pw').value;
    const confPw  = document.getElementById('profile-confirm-pw').value;
    alertEl.style.display = 'none';
    if (!name)              { showProfileAlert('error', 'Nama tidak boleh kosong.'); return; }
    if (newPw && newPw.length < 6) { showProfileAlert('error', 'Password baru minimal 6 karakter.'); return; }
    if (newPw && newPw !== confPw) { showProfileAlert('error', 'Konfirmasi password tidak cocok.'); return; }
    if (newPw && !curPw)   { showProfileAlert('error', 'Masukkan password lama terlebih dahulu.'); return; }
    btn.disabled = true;
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" class="spin-icon"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> Menyimpan...';
    const fd = new FormData();
    fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    fd.append('name', name);
    if (newPw) {
        fd.append('current_password', curPw);
        fd.append('new_password', newPw);
        fd.append('new_password_confirmation', confPw);
    }
    try {
        const res  = await fetch('{{ route("profile.api.update") }}', {
            method: 'POST', body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (res.ok && data.success) {
            showProfileAlert('success', data.message);
            document.getElementById('profile-display-name').textContent = data.name;
            document.getElementById('profile-name').value = data.name;
            document.getElementById('profile-current-pw').value = '';
            document.getElementById('profile-new-pw').value = '';
            document.getElementById('profile-confirm-pw').value = '';
            updatePwStrength('');
        } else {
            showProfileAlert('error', data.message || 'Gagal menyimpan.');
        }
    } catch {
        showProfileAlert('error', 'Koneksi bermasalah. Coba lagi.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg> Simpan Perubahan';
    }
}
function showProfileAlert(type, msg) {
    const el = document.getElementById('profile-alert');
    const iconSuccess = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    const iconError   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
    el.innerHTML = (type === 'success' ? iconSuccess : iconError) + msg;
    el.className = 'profile-alert profile-alert-' + type;
    el.style.display = 'flex';
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    const prof = document.getElementById('profile-drawer');
    if (prof && prof.classList.contains('open')) {
        closeProfileDrawer();
        return;
    }
    closeDashNavDrawer();
});

/* ── SMOOTH SCROLL ── */
(function () {
    const STORAGE_KEY = 'dash_pending_smooth_scroll';

    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    function currentPageUrl() {
        return window.location.origin + window.location.pathname + window.location.search;
    }

    function pageUrlWithoutHash(url) {
        return url.origin + url.pathname + url.search;
    }

    function getTargetId(hash) {
        return decodeURIComponent(hash.replace(/^#/, ''));
    }

    function smoothScrollToHash(hash, attempt = 0) {
        const id = getTargetId(hash);
        const target = document.getElementById(id);

        if (!target) {
            if (attempt < 20) {
                setTimeout(() => {
                    smoothScrollToHash(hash, attempt + 1);
                }, 100);
            }

            return;
        }

        target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        history.replaceState(
            null,
            '',
            window.location.pathname + window.location.search + hash
        );
    }

    document.addEventListener('click', function (e) {
        const link = e.target.closest('a.dash-notif-link');

        if (!link) return;

        const href = link.getAttribute('href');

        if (!href) return;

        const url = new URL(href, window.location.href);

        if (!url.hash) return;

        e.preventDefault();

        const targetPage = pageUrlWithoutHash(url);
        const activePage = currentPageUrl();

        if (targetPage === activePage) {
            smoothScrollToHash(url.hash);
            return;
        }

        sessionStorage.setItem(STORAGE_KEY, url.hash);

        window.location.href = targetPage;
    });

    window.addEventListener('load', function () {
        const pendingHash = sessionStorage.getItem(STORAGE_KEY);

        if (!pendingHash) return;

        sessionStorage.removeItem(STORAGE_KEY);

        if (window.location.hash) {
            history.replaceState(
                null,
                '',
                window.location.pathname + window.location.search
            );
        }

        window.scrollTo(0, 0);

        setTimeout(() => {
            smoothScrollToHash(pendingHash);
        }, 300);
    });
})();

</script>
@endpush

@stack('scripts')

</body>
</html>
