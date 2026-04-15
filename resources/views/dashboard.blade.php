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
        @php
            $user = auth()->user();
            $isAdmin = $user?->role === 'admin';
            $userRoleLabel = $isAdmin ? 'ADMIN' : 'DRIVER';
            $userName = $user?->name ?? $user?->username ?? 'User';
        @endphp

        <div class="dash-shell">
            <section class="dash-top">
                <div class="dash-topbar">
                    <div>
                        <h1 class="dash-brand-title">Ceklist Kendaraan</h1>
                        <p class="dash-brand-sub">NEXUS FLEET MANAGEMENT</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="dash-chip">
                            @if ($isAdmin)
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 3L19 6V11C19 16 15.5 20 12 21C8.5 20 5 16 5 11V6L12 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @else
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M20 21C20 17.7 16.9 15 13 15H11C7.1 15 4 17.7 4 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            @endif
                            {{ $userRoleLabel }}
                        </span>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dash-logout-btn" type="submit" aria-label="Logout">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M15 3H7C5.9 3 5 3.9 5 5V19C5 20.1 5.9 21 7 21H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10 12H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M18 8L22 12L18 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="dash-hero">
                    <p class="dash-hero-kicker">{{ $isAdmin ? 'AKSES ADMINISTRATOR' : 'SELAMAT BEKERJA,' }}</p>
                    <h2 class="dash-hero-name">{{ $isAdmin ? 'Pusat Kontrol Fleet' : $userName }}</h2>
                    <div class="dash-hero-tags">
                        <span class="dash-tag dash-tag-outline">ID: {{ strtoupper($user?->username ?? 'USER-00') }}</span>
                        <span class="dash-tag dash-tag-yellow">Status: Aktif</span>
                    </div>
                </div>
            </section>

            <main class="dash-content">
                <div class="dash-desktop-grid">
                    <div class="dash-main-column">
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
                    </div>

                    @if ($isAdmin)
                        <aside class="dash-side-column">
                            <section>
                                <h3 class="dash-section-title">PANEL MANAJEMEN (ADMIN)</h3>
                                <div class="dash-admin-grid">
                                    <a href="{{ route('admin.database-sheet') }}" class="dash-admin-card dash-pressable">
                                        <span class="dash-admin-icon dash-admin-db" aria-hidden="true">
                                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                                                <ellipse cx="12" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="2"/>
                                                <path d="M5 5V19C5 20.7 8.1 22 12 22C15.9 22 19 20.7 19 19V5" stroke="currentColor" stroke-width="2"/>
                                                <path d="M5 12C5 13.7 8.1 15 12 15C15.9 15 19 13.7 19 12" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                        </span>
                                        DATABASE SHEET
                                    </a>

                                    <a href="{{ route('admin.log-foto-fisik') }}" class="dash-admin-card dash-pressable">
                                        <span class="dash-admin-icon dash-admin-log" aria-hidden="true">
                                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                                                <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
                                                <circle cx="9" cy="10" r="2" stroke="currentColor" stroke-width="2"/>
                                                <path d="M21 16L16 11L7 20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        LOG FOTO FISIK
                                    </a>

                                    <a href="{{ route('admin.arsip-pdf') }}" class="dash-admin-card dash-pressable">
                                        <span class="dash-admin-icon dash-admin-arsip" aria-hidden="true">
                                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                                                <path d="M7 3H14L19 8V21H7V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                <path d="M14 3V8H19" stroke="currentColor" stroke-width="2"/>
                                                <path d="M9 13H15M9 17H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </span>
                                        ARSIP PDF
                                    </a>

                                    <a href="{{ route('admin.master-armada') }}" class="dash-admin-card dash-pressable">
                                        <span class="dash-admin-icon dash-admin-armada" aria-hidden="true">
                                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                                                <circle cx="8" cy="17" r="2.4" stroke="currentColor" stroke-width="2"/>
                                                <circle cx="16" cy="17" r="2.4" stroke="currentColor" stroke-width="2"/>
                                                <path d="M4 17H3V12L5 9H19L21 12V17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M6 9L7.5 6H16.5L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        MASTER ARMADA
                                    </a>
                                </div>
                            </section>
                        </aside>
                    @endif
                </div>
            </main>
        </div>
    </body>
</html>
