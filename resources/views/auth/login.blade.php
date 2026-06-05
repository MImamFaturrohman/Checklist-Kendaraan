<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Login - VMS Vehicle Management System Portal Kendaraan Operasional">
        <title>Login - {{ config('app.name', 'VMS') }}</title>
        @include('partials.favicon')
        @vite(['resources/css/auth.css', 'resources/js/auth.js'])
        
        <style>
            /* Gradient tokens — diubah JS saat toggle tema */
            :root {
                --grad-1: #0A2342;
                --grad-2: #0f172a;
                --grad-3: #050B14;
            }

            .auth-page-body {
                margin: 0;
                padding: 0;
                min-height: 100vh;
                background: linear-gradient(135deg, var(--grad-1) 0%, var(--grad-2) 50%, var(--grad-3) 100%);
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

        @include('partials.premium-auth-bg')

        {{-- Theme toggle button --}}
        <button class="auth-theme-toggle" id="theme-toggle" title="Ganti Tema" aria-label="Toggle tema">
            <i class="bi bi-sun-fill" id="theme-icon"></i>
        </button>

        {{-- Login card --}}
        <div class="auth-card" id="login-card">
            <div class="auth-card-image">
                <img src="{{ asset('images/VMS.png') }}" alt="VMS - Vehicle Management System" class="auth-hero-img" width="300" height="120" decoding="async">
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

                <form method="POST" action="{{ route('login') }}" class="auth-form" data-login-form id="login-form" data-turbo="false">
                    @csrf
                    {{-- Username --}}
                    <div class="auth-field">
                        <div class="auth-input-group" id="username-input-group">
                            <span class="auth-input-icon"><i class="bi bi-person-fill"></i></span>
                            <input id="username" type="text" name="username" class="auth-input" value="{{ old('username') }}" placeholder="Username" required autofocus>
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="auth-field">
                        <div class="auth-input-group" id="password-input-group">
                            <span class="auth-input-icon"><i class="bi bi-lock-fill"></i></span>
                            <input id="password" type="password" name="password" class="auth-input" placeholder="Password" required>
                            <button class="auth-password-toggle" type="button" data-password-toggle>
                                <i class="bi bi-eye" data-password-icon></i>
                            </button>
                        </div>
                        <div class="auth-error-msg" data-login-error role="alert" hidden></div>
                    </div>

                    <button type="submit" class="auth-btn-submit" id="login-submit" data-login-submit aria-busy="false">
                        <span class="auth-btn-text">Sign in</span>
                        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
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

    </body>
</html>