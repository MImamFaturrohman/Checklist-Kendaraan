<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Dashboard - {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dash-body">

        {{-- Style background decoration layers (dark mode) --}}
        <div class="dash-bg-cubes" aria-hidden="true"></div>
        <div class="dash-bg-stardust" aria-hidden="true"></div>
        <div class="dash-bg-orb-gold" aria-hidden="true"></div>
        <div class="dash-bg-orb-blue" aria-hidden="true"></div>
        <div class="dash-bg-wave" aria-hidden="true">
            <svg viewBox="0 0 1440 400" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%" preserveAspectRatio="none">
                <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50 L 1440 400 L 0 400 Z" fill="url(#dash_fill)"></path>
                <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50" stroke="url(#dash_stroke)" stroke-width="3" stroke-linecap="round"></path>
                <path d="M0 350 C 400 380, 500 250, 900 300 C 1200 350, 1300 200, 1440 150" stroke="rgba(255,255,255,0.06)" stroke-width="2" stroke-dasharray="8 8"></path>
                <circle cx="700" cy="200" r="4" fill="#D4AF37"></circle>
                <circle cx="1000" cy="50" r="4" fill="#D4AF37"></circle>
                <defs>
                    <linearGradient id="dash_fill" x1="720" y1="50" x2="720" y2="400" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#D4AF37" stop-opacity="0.12"></stop>
                        <stop offset="1" stop-color="#0A2342" stop-opacity="0"></stop>
                    </linearGradient>
                    <linearGradient id="dash_stroke" x1="0" y1="150" x2="1440" y2="150" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#0A2342"></stop>
                        <stop offset="0.4" stop-color="#D4AF37"></stop>
                        <stop offset="1" stop-color="#60A5FA"></stop>
                    </linearGradient>
                </defs>
            </svg>
        </div>

        @php
            $user          = auth()->user();
            $isSuperAdmin  = $user?->role === 'superadmin';
            $isAdmin       = $user?->role === 'admin';
            $isManager     = $user?->role === 'manager';
            $isPic         = $user?->role === 'pic_kendaraan';
            $isDriver      = $user?->role === 'driver' || $isPic;
            $userRoleLabel = $isSuperAdmin ? 'SUPERADMIN' : ($isAdmin ? 'ADMIN' : ($isManager ? 'MANAGER' : ($isPic ? 'PIC KENDARAAN' : 'DRIVER')));
            $userName      = $user?->name ?? $user?->username ?? 'User';

            $pendingCount = 0;
            if ($isManager || $isAdmin || $isSuperAdmin) {
                $pendingCount = \App\Models\PeminjamanRequest::where('status', 'pending')->count();
            }
        @endphp

        {{-- ══ NAVBAR — full-width sticky (like landing page) ══ --}}
        <nav class="dash-nav" id="dash-nav">
            <div class="dash-nav-inner">

                {{-- Brand --}}
                <div class="dash-nav-brand">
                    <img src="{{ asset('images/VMS.png') }}" alt="VMS" class="dash-nav-logo">
                    <div>
                        <div class="dash-nav-title">Vehicle Management System</div>
                        <span class="dash-nav-sub">PT ARTHA DAYA COALINDO</span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="dash-nav-actions" id="dash-nav-actions">

                    {{-- Theme toggle --}}
                    <button class="dash-theme-btn" id="dash-theme-toggle" title="Ganti Tema" aria-label="Toggle Tema">
                        <i class="bi bi-moon-fill" id="dash-theme-icon"></i>
                        <span class="dash-theme-mode-label" id="dash-theme-label">Dark Mode</span>
                    </button>

                    {{-- Role chip --}}
                    <span class="dash-chip {{ ($isAdmin || $isSuperAdmin) ? 'dash-chip-admin' : ($isManager ? 'dash-chip-manager' : 'dash-chip-driver') }}">
                        @if ($isAdmin || $isSuperAdmin)
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
                        @elseif ($isManager)
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        @else
                            <i class="bi bi-person-check-fill"></i>
                        @endif
                        <span class="dash-nav-chip-label">{{ $userRoleLabel }}</span>
                    </span>

                    {{-- Profile button --}}
                    <button class="dash-nav-btn-glass" id="profile-open-btn" onclick="openProfileDrawer()" aria-label="Edit Profil" title="Profil Saya">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        <span class="dash-nav-btn-label">Profil</span>
                    </button>

                    {{-- Logout --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dash-nav-btn-gold" type="submit" aria-label="Logout">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="dash-nav-btn-label">Keluar</span>
                        </button>
                    </form>

                </div>

                {{-- Mobile hamburger (shown only on small screens via CSS) --}}
                <button class="dash-mobile-menu-btn" id="dash-mobile-menu-btn" aria-label="Buka Menu" aria-expanded="false">
                    <i class="bi bi-list" id="dash-mobile-menu-icon"></i>
                </button>

            </div>
        </nav>

        {{-- ══ HERO SECTION — separate, modern banner ══ --}}
        <section class="dash-hero-section">
            <div class="dash-hero-inner">

                <div class="dash-hero-left">
                    @if($isSuperAdmin)
                        <p class="dash-hero-kicker">
                            <span class="dash-hero-kicker-dot"></span>
                            AKSES SUPERADMIN
                        </p>
                        <h2 class="dash-hero-name">Pusat Kontrol Fleet</h2>
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
                        <p class="dash-hero-kicker">
                            <span class="dash-hero-kicker-dot"></span>
                            SELAMAT BEKERJA,
                        </p>
                        <h2 class="dash-hero-name">{{ $userName }}</h2>
                    @endif

                    <div class="dash-hero-tags">
                        <span class="dash-tag dash-tag-outline">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" style="display:inline;vertical-align:middle;margin-right:3px"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            ID: {{ strtoupper($user?->username ?? 'USER-00') }}
                        </span>
                        <span class="dash-tag dash-tag-yellow">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" style="display:inline;vertical-align:middle;margin-right:3px"><circle cx="12" cy="12" r="5"/></svg>
                            Status: Aktif
                        </span>
                        @if(($isSuperAdmin || $isManager) && $pendingCount > 0)
                            <span class="dash-tag" style="background:rgba(239,68,68,0.18);color:#fca5a5;border:1px solid rgba(239,68,68,0.35)">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" style="display:inline;vertical-align:middle;margin-right:3px"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                {{ $pendingCount }} Request Menunggu
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Clock widget — hanya untuk driver & pic --}}
                @if($isDriver)
                <div class="dash-clock-widget" id="dash-clock-widget">
                    <div class="dash-clock-date" id="dash-clock-date">—</div>
                    <div class="dash-clock-time-row">
                        <span class="dash-clock-time" id="dash-clock-time">00:00:00</span>
                        <span class="dash-clock-divider">|</span>
                        <span class="dash-clock-shift" id="dash-clock-shift">—</span>
                    </div>
                </div>
                @endif

            </div>
        </section>

        <div class="dash-shell">
            <main class="dash-content">
                <div class="dash-desktop-grid {{ ($isAdmin || $isSuperAdmin) ? 'dash-desktop-grid--single' : '' }}">

                    {{-- MAIN COLUMN --}}
                    <div class="dash-main-column">

                        @if($isManager)
                            <section>
                                <h3 class="dash-section-title">TUGAS UTAMA</h3>
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
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                            <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        @if($pendingCount > 0)
                                            <span class="dash-pending-dot"></span>
                                        @endif
                                    </span>
                                </a>
                            </section>

                        @elseif($isSuperAdmin)
                            {{-- Superadmin: akses penuh fitur admin --}}
                            <section>
                                <h3 class="dash-section-title">TUGAS UTAMA</h3>
                                <div class="dash-main-grid-admin">
                                    {{-- Portal Pemeriksaan Kendaraan --}}
                                    <a href="{{ route('admin.portal-pemeriksaan') }}" class="dash-main-card dash-pressable">
                                        <div>
                                            <p class="dash-main-title">Portal Pemeriksaan Kendaraan</p>
                                            <p class="dash-main-sub">Database, foto fisik &amp; arsip PDF</p>
                                        </div>
                                        <span class="dash-main-icon" aria-hidden="true">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                                <ellipse cx="12" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="2"/>
                                                <path d="M5 5V19C5 20.7 8.1 22 12 22C15.9 22 19 20.7 19 19V5" stroke="currentColor" stroke-width="2"/>
                                                <path d="M5 12C5 13.7 8.1 15 12 15C15.9 15 19 13.7 19 12" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                        </span>
                                    </a>

                                    {{-- Peminjaman Kendaraan --}}
                                    <a href="{{ route('admin.peminjaman') }}" class="dash-main-card dash-pressable" style="position:relative">
                                        <div>
                                            <p class="dash-main-title">Peminjaman Kendaraan</p>
                                            <p class="dash-main-sub">Daftar permohonan &amp; unduh PDF</p>
                                        </div>
                                        <span class="dash-main-icon" aria-hidden="true">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/>
                                                <path d="M9 12h6M9 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </span>
                                        @if($pendingCount > 0)
                                            <span class="dash-pending-dot" style="top:18px;right:18px"></span>
                                        @endif
                                    </a>

                                    {{-- Portal Manajemen Administrasi --}}
                                    <a href="{{ route('admin.portal-manajemen') }}" class="dash-main-card dash-pressable">
                                        <div>
                                            <p class="dash-main-title">Portal Manajemen Administrasi</p>
                                            <p class="dash-main-sub">Master armada &amp; Manajemen user</p>
                                        </div>
                                        <span class="dash-main-icon" aria-hidden="true">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                                                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </span>
                                    </a>

                                    {{-- Buat Ceklist --}}
                                    <a href="{{ route('checklists.create') }}" class="dash-main-card dash-pressable">
                                        <div>
                                            <p class="dash-main-title">Buat Ceklist Baru</p>
                                            <p class="dash-main-sub">Mulai inspeksi unit hari ini</p>
                                        </div>
                                        <span class="dash-main-icon" aria-hidden="true">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                                <rect x="5" y="4" width="14" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                                                <path d="M9 2H15V6H9V2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                <path d="M9 12L11.2 14.2L15 10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </a>
                                </div>
                            </section>
                        @elseif($isAdmin)
                            {{-- Admin: hanya portal pemeriksaan --}}
                            <section>
                                <h3 class="dash-section-title">TUGAS UTAMA</h3>
                                <a href="{{ route('admin.portal-pemeriksaan') }}" class="dash-main-card dash-pressable">
                                    <div>
                                        <p class="dash-main-title">Portal Pemeriksaan Kendaraan</p>
                                        <p class="dash-main-sub">Lihat info ringkas dan chart pemeriksaan</p>
                                    </div>
                                    <span class="dash-main-icon" aria-hidden="true">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                            <ellipse cx="12" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="2"/>
                                            <path d="M5 5V19C5 20.7 8.1 22 12 22C15.9 22 19 20.7 19 19V5" stroke="currentColor" stroke-width="2"/>
                                            <path d="M5 12C5 13.7 8.1 15 12 15C15.9 15 19 13.7 19 12" stroke="currentColor" stroke-width="2"/>
                                        </svg>
                                    </span>
                                </a>
                            </section>

                        @else
                            {{-- Driver / PIC: Ceklist card --}}
                            <section>
                                <h3 class="dash-section-title">TUGAS UTAMA</h3>
                                <a href="{{ route('checklists.create') }}" class="dash-main-card dash-pressable">
                                    <div>
                                        <p class="dash-main-title">Buat Ceklist Baru</p>
                                        <p class="dash-main-sub">Mulai inspeksi unit hari ini</p>
                                    </div>
                                    <span class="dash-main-icon" aria-hidden="true">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                            <rect x="5" y="4" width="14" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                                            <path d="M9 2H15V6H9V2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                            <path d="M9 12L11.2 14.2L15 10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </a>
                            </section>
                        @endif

                    </div>

                </div>
            </main>
        </div>

        {{-- ════════════════════════════════════════════════════════════ --}}
        {{-- PROFILE DRAWER (semua user)                                  --}}
        {{-- ════════════════════════════════════════════════════════════ --}}
        <div id="profile-overlay" class="profile-overlay" onclick="closeProfileDrawer()" style="display:none"></div>
        <aside id="profile-drawer" class="profile-drawer" aria-label="Profil Saya">

            {{-- Gradient header — centered layout --}}
            <div class="profile-drawer-header">
                <button class="profile-drawer-close" onclick="closeProfileDrawer()" aria-label="Tutup">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                </button>
                <div class="profile-drawer-avatar-outer">
                    <div class="profile-drawer-avatar-ring">
                        <div class="profile-drawer-avatar">{{ strtoupper(substr($userName, 0, 1)) }}</div>
                    </div>
                    <span class="profile-drawer-avatar-badge" aria-hidden="true"></span>
                </div>
                <p class="profile-drawer-name" id="profile-display-name">{{ $userName }} <span class="profile-drawer-username">{{ '#'.$user?->username }}</span></p>
                <span class="dash-chip {{ ($isAdmin || $isSuperAdmin) ? 'dash-chip-admin' : ($isManager ? 'dash-chip-manager' : 'dash-chip-driver') }}">
                    @if ($isAdmin || $isSuperAdmin)
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
                    @elseif ($isManager)
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    @else
                        <i class="bi bi-person-check-fill"></i>
                    @endif
                    {{ $userRoleLabel }}
                </span>
            </div>

            <div class="profile-drawer-body">
                {{-- Alert --}}
                <div id="profile-alert" class="profile-alert" style="display:none"></div>

                {{-- ── Informasi Profil ── --}}
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
                                <input type="text" id="profile-name" class="profile-input has-icon" value="{{ $userName }}" placeholder="Nama Lengkap">
                            </div>
                        </div>
                        <div class="profile-field">
                            <label class="profile-label" for="profile-username-field">Username</label>
                            <div class="profile-input-wrap">
                                <svg class="profile-input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                <input type="text" id="profile-username-field" class="profile-input has-icon" value="{{ $user?->username }}" disabled>
                            </div>
                            <p class="profile-hint">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" style="display:inline;vertical-align:middle;margin-right:2px"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                Username tidak dapat diubah.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ── Ganti Password ── --}}
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
                            <div class="profile-pw-strength">
                                <div class="profile-pw-strength-bar" id="pw-strength-bar"></div>
                            </div>
                            <p class="profile-hint" id="pw-strength-label" style="margin-top:3px"></p>
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

            </div>{{-- /.profile-drawer-body --}}

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

        <script>
        /* ── Dashboard Theme Toggle ── */
        (function () {
            const body  = document.body;
            const icon  = document.getElementById('dash-theme-icon');
            const btn   = document.getElementById('dash-theme-toggle');
            const label = document.getElementById('dash-theme-label');

            function applyTheme(isDark) {
                body.classList.toggle('dark', isDark);
                if (icon)  icon.className    = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
                if (label) label.textContent = isDark ? 'Light Mode' : 'Dark Mode';
            }

            // Sync with login page preference (vms-theme key)
            const saved = localStorage.getItem('vms-theme') || localStorage.getItem('vms-dash-theme');
            applyTheme(saved === 'dark');

            if (btn) {
                btn.addEventListener('click', function () {
                    const next = !body.classList.contains('dark');
                    applyTheme(next);
                    localStorage.setItem('vms-theme', next ? 'dark' : 'light');
                    localStorage.setItem('vms-dash-theme', next ? 'dark' : 'light');
                });
            }
        })();

        /* ── Mobile navbar dropdown ── */
        (function () {
            const menuBtn    = document.getElementById('dash-mobile-menu-btn');
            const navActions = document.getElementById('dash-nav-actions');
            const menuIcon   = document.getElementById('dash-mobile-menu-icon');

            if (!menuBtn || !navActions) return;

            function closeMenu() {
                navActions.classList.remove('mobile-open');
                menuIcon.className = 'bi bi-list';
                menuBtn.setAttribute('aria-expanded', 'false');
            }

            menuBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = navActions.classList.toggle('mobile-open');
                menuIcon.className = isOpen ? 'bi bi-x-lg' : 'bi bi-list';
                menuBtn.setAttribute('aria-expanded', String(isOpen));
            });

            document.addEventListener('click', function (e) {
                if (!navActions.contains(e.target) && !menuBtn.contains(e.target)) {
                    closeMenu();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeMenu();
            });
        })();

        /* ── Profile drawer ─────────────────────────────────────────── */
        function openProfileDrawer() {
            document.getElementById('profile-overlay').style.display = 'block';
            document.getElementById('profile-drawer').classList.add('open');
            document.body.style.overflow = 'hidden';
            document.getElementById('profile-alert').style.display = 'none';
            setTimeout(() => document.getElementById('profile-name').focus(), 280);
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
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeProfileDrawer(); });

        async function saveProfile() {
            const btn      = document.getElementById('profile-save-btn');
            const alertEl  = document.getElementById('profile-alert');
            const name     = document.getElementById('profile-name').value.trim();
            const curPw    = document.getElementById('profile-current-pw').value;
            const newPw    = document.getElementById('profile-new-pw').value;
            const confPw   = document.getElementById('profile-confirm-pw').value;

            alertEl.style.display = 'none';

            if (!name) { showAlert('error', 'Nama tidak boleh kosong.'); return; }
            if (newPw && newPw.length < 6) { showAlert('error', 'Password baru minimal 6 karakter.'); return; }
            if (newPw && newPw !== confPw) { showAlert('error', 'Konfirmasi password tidak cocok.'); return; }
            if (newPw && !curPw) { showAlert('error', 'Masukkan password lama terlebih dahulu.'); return; }

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
                    showAlert('success', data.message);
                    document.getElementById('profile-display-name').textContent = data.name;
                    document.getElementById('profile-name').value = data.name;
                    document.getElementById('profile-current-pw').value = '';
                    document.getElementById('profile-new-pw').value     = '';
                    document.getElementById('profile-confirm-pw').value = '';
                    updatePwStrength('');
                } else {
                    showAlert('error', data.message || 'Gagal menyimpan.');
                }
            } catch {
                showAlert('error', 'Koneksi bermasalah. Coba lagi.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg> Simpan Perubahan';
            }
        }

        function showAlert(type, msg) {
            const el = document.getElementById('profile-alert');
            const iconSuccess = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            const iconError   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
            el.innerHTML = (type === 'success' ? iconSuccess : iconError) + msg;
            el.className = 'profile-alert profile-alert-' + type;
            el.style.display = 'flex';
            el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function updatePwStrength(pw) {
            const bar   = document.getElementById('pw-strength-bar');
            const label = document.getElementById('pw-strength-label');
            if (!bar) return;
            if (!pw) { bar.style.width = '0%'; label.textContent = ''; return; }
            let score = 0;
            if (pw.length >= 6)  score++;
            if (pw.length >= 10) score++;
            if (/[A-Z]/.test(pw)) score++;
            if (/[0-9]/.test(pw)) score++;
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

        @if($isDriver)
        /* ── Clock widget ───────────────────────────────────────────── */
        (function () {
            const DAYS   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            const MONTHS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

            function getShift(hour) {
                if (hour >= 7 && hour < 12)  return { label: 'Shift Pagi',  cls: 'shift-pagi' };
                if (hour >= 12 && hour < 16) return { label: 'Shift Siang', cls: 'shift-siang' };
                return { label: 'Di Luar Shift', cls: 'shift-none' };
            }

            function tick() {
                const now   = new Date();
                const hh    = String(now.getHours()).padStart(2, '0');
                const mm    = String(now.getMinutes()).padStart(2, '0');
                const ss    = String(now.getSeconds()).padStart(2, '0');
                const shift = getShift(now.getHours());

                document.getElementById('dash-clock-date').textContent =
                    `${DAYS[now.getDay()]}, ${now.getDate()} ${MONTHS[now.getMonth()]} ${now.getFullYear()}`;
                document.getElementById('dash-clock-time').textContent = `${hh}:${mm}:${ss}`;

                const shiftEl = document.getElementById('dash-clock-shift');
                shiftEl.textContent = shift.label;
                shiftEl.className   = 'dash-clock-shift ' + shift.cls;
            }

            tick();
            setInterval(tick, 1000);
        })();
        @endif
        </script>
    </body>
</html>
