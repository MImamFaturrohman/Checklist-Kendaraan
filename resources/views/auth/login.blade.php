<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Login - VMS Vehicle Management System Portal Kendaraan Operasional">
        <title>Login - {{ config('app.name', 'VMS') }}</title>
        @vite(['resources/css/auth.css', 'resources/js/app.js'])
        
        <style>
            /* Gradient tokens — diubah JS saat toggle tema */
            :root {
                --grad-1: #0A2342;
                --grad-2: #0e1f3a;
                --grad-3: #050B14;
            }

            .auth-page-body {
                margin: 0;
                padding: 0;
                min-height: 100vh;
                background: linear-gradient(135deg, var(--grad-1) 0%, var(--grad-2) 50%, var(--grad-3) 100%);
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.5s ease;
            }

            .auth-card {
                position: relative;
                z-index: 10;
            }
        </style>
    </head>
    <body class="auth-page-body">

        {{-- Premium Background Layers --}}
        <div class="auth-bg-cubes" aria-hidden="true"></div>
        <div class="auth-bg-stardust" aria-hidden="true"></div>
        <div class="auth-bg-orb-gold" aria-hidden="true"></div>
        <div class="auth-bg-orb-blue" aria-hidden="true"></div>
        <div class="auth-bg-wave" aria-hidden="true">
            <svg viewBox="0 0 1440 400" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%" preserveAspectRatio="none">
                <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50 L 1440 400 L 0 400 Z" fill="url(#auth_fill)"></path>
                <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50" stroke="url(#auth_stroke)" stroke-width="3" stroke-linecap="round"></path>
                <path d="M0 350 C 400 380, 500 250, 900 300 C 1200 350, 1300 200, 1440 150" stroke="rgba(255,255,255,0.08)" stroke-width="2" stroke-dasharray="8 8"></path>
                <circle cx="700" cy="200" r="4" fill="#D4AF37"></circle>
                <circle cx="1000" cy="50" r="4" fill="#D4AF37"></circle>
                <defs>
                    <linearGradient id="auth_fill" x1="720" y1="50" x2="720" y2="400" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#D4AF37" stop-opacity="0.12"></stop>
                        <stop offset="1" stop-color="#0A2342" stop-opacity="0"></stop>
                    </linearGradient>
                    <linearGradient id="auth_stroke" x1="0" y1="150" x2="1440" y2="150" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#0A2342"></stop>
                        <stop offset="0.4" stop-color="#D4AF37"></stop>
                        <stop offset="1" stop-color="#60A5FA"></stop>
                    </linearGradient>
                </defs>
            </svg>
        </div>

        {{-- Theme toggle button --}}
        <button class="auth-theme-toggle" id="theme-toggle" title="Ganti Tema" aria-label="Toggle tema">
            <i class="bi bi-sun-fill" id="theme-icon"></i>
        </button>

        {{-- Login card --}}
        <div class="auth-card" id="login-card">
            <div class="auth-card-image">
                <img src="{{ asset('images/VMS.png') }}" alt="VMS - Vehicle Management System" class="auth-hero-img">
            </div>

            <div class="auth-card-body">

                {{-- ── Title ─────────────────────────────────────────── --}}
                <div class="auth-card-header">
                    <h1 class="auth-title">Vehicle Management System</h1>
                    <div class="auth-subtitle-divider">
                        <span class="auth-subtitle">Portal Kendaraan Operasional</span>
                    </div>
                </div>

                @if (session('status'))
                    <div class="auth-alert auth-alert-success" role="alert" id="login-status">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="auth-form" data-login-form id="login-form">
                    @csrf
                    {{-- Username --}}
                    <div class="auth-field">
                        <div class="auth-input-group @error('username') has-error @enderror">
                            <span class="auth-input-icon"><i class="bi bi-person-fill"></i></span>
                            <input id="username" type="text" name="username" class="auth-input" value="{{ old('username') }}" placeholder="Username" required autofocus>
                        </div>
                        @error('username')
                            <div class="auth-error-msg"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="auth-field">
                        <div class="auth-input-group @error('password') has-error @enderror">
                            <span class="auth-input-icon"><i class="bi bi-lock-fill"></i></span>
                            <input id="password" type="password" name="password" class="auth-input" placeholder="Password" required>
                            <button class="auth-password-toggle" type="button" data-password-toggle>
                                <i class="bi bi-eye" data-password-icon></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="auth-error-msg"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="auth-btn-submit" id="login-submit">
                        <span class="auth-btn-text">Sign in</span>
                        <i class="bi bi-box-arrow-in-right"></i>
                    </button>
                </form>

                <div class="auth-back-wrap">
                    <a href="{{ route('landing') }}" class="auth-back-link">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        Kembali ke Halaman Utama
                    </a>
                </div>

                <div class="auth-footer">
                    <p>&copy; {{ date('Y') }} Port Management Unit Suralaya</p>
                </div>
            </div>
        </div>

        <script>
        /* ── Theme toggle ── */
        (function () {
            const btn  = document.getElementById('theme-toggle');
            const icon = document.getElementById('theme-icon');
            const body = document.body;
            const root = document.documentElement;

            const DARK  = { g1: '#0A2342', g2: '#0e1f3a', g3: '#050B14' };
            const LIGHT = { g1: '#c7d9f8', g2: '#dbeafe', g3: '#e8f0fe' };

            function applyTheme(isLight) {
                const g = isLight ? LIGHT : DARK;
                root.style.setProperty('--grad-1', g.g1);
                root.style.setProperty('--grad-2', g.g2);
                root.style.setProperty('--grad-3', g.g3);
                body.classList.toggle('light-mode', isLight);
                icon.className = isLight ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
            }

            /* Restore saved preference */
            applyTheme(localStorage.getItem('vms-theme') === 'light');

            btn.addEventListener('click', function () {
                const next = !body.classList.contains('light-mode');
                applyTheme(next);
                localStorage.setItem('vms-theme', next ? 'light' : 'dark');
            });
        })();
        </script>
    </body>
</html>