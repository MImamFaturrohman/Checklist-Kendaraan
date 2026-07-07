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
        <script src="https://unpkg.com/@phosphor-icons/web"></script>
        
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

            .auth-title {
                margin-top: -25px;
            }

            .auth-btn-submit {
                margin-top: -20px;
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
                        @if($isResetMode ?? false)
                            <span class="auth-subtitle" id="auth-subtitle">Atur Password Baru</span>
                        @else
                            <span class="auth-subtitle" id="auth-subtitle">Portal Kendaraan Operasional</span>
                        @endif
                    </div>
                </div>

                @if (session('status'))
                    <div class="auth-alert auth-alert-success" role="alert" id="login-status">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="auth-alert auth-alert-danger" role="alert">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="auth-alert auth-alert-success" role="alert" id="status-alert" style="display: none;">
                    <i class="bi bi-check-circle-fill"></i>
                    <span id="status-alert-text"></span>
                </div>

                @if ($isResetMode ?? false)
                    <form method="POST" action="{{ route('password.store') }}" class="auth-form" data-login-form id="login-form" data-turbo="false" data-is-reset-mode="true">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        {{-- Email --}}
                        <div class="auth-field">
                            <div class="auth-input-group">
                                <span class="auth-input-icon"><i class="bi bi-envelope-fill"></i></span>
                                <input id="email" type="email" name="email" class="auth-input" value="{{ old('email', $email) }}" placeholder="Email Terdaftar" required autofocus readonly style="opacity: 0.8; cursor: not-allowed;">
                            </div>
                        </div>

                        {{-- New Password --}}
                        <div class="auth-field">
                            <div class="auth-input-group">
                                <span class="auth-input-icon"><i class="bi bi-lock-fill"></i></span>
                                <input id="password" type="password" name="password" class="auth-input" placeholder="Password Baru" required>
                                <button class="auth-password-toggle" type="button" data-password-toggle>
                                    <i class="bi bi-eye" data-password-icon></i>
                                </button>
                            </div>
                        </div>

                        {{-- Confirm New Password --}}
                        <div class="auth-field">
                            <div class="auth-input-group">
                                <span class="auth-input-icon"><i class="bi bi-lock-fill"></i></span>
                                <input id="password_confirmation" type="password" name="password_confirmation" class="auth-input" placeholder="Konfirmasi Password Baru" required>
                            </div>
                        </div>

                        <div class="auth-field-helper">
                            <a href="{{ route('login') }}" id="back-to-login" class="auth-helper-link">Kembali ke Login</a>
                        </div>

                        <button type="submit" class="auth-btn-submit" id="login-submit">
                            <i class="ph-bold ph-key" aria-hidden="true"></i>
                            <span class="auth-btn-text">Reset Password</span>
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('login') }}" class="auth-form" data-login-form id="login-form" data-turbo="false" data-login-url="{{ route('login') }}" data-forgot-url="{{ route('password.email') }}">
                        @csrf
                        <div class="auth-alert auth-alert-danger" data-login-error role="alert" hidden></div>
                        {{-- Username --}}
                        <div class="auth-field" id="username-field-group">
                            <div class="auth-input-group" id="username-input-group">
                                <span class="auth-input-icon"><i class="bi bi-person-fill"></i></span>
                                <input id="username" type="text" name="username" class="auth-input" value="{{ old('username') }}" placeholder="Username" required autofocus>
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="auth-field" id="password-field-group">
                            <div class="auth-input-group" id="password-input-group">
                                <span class="auth-input-icon"><i class="bi bi-lock-fill"></i></span>
                                <input id="password" type="password" name="password" class="auth-input" placeholder="Password" required>
                                <button class="auth-password-toggle" type="button" data-password-toggle>
                                    <i class="bi bi-eye" data-password-icon></i>
                                </button>
                            </div>
                        </div>

                        {{-- Email (Forgot Password Mode) --}}
                        <div class="auth-field" id="email-field-group" style="display: none;">
                            <div class="auth-input-group" id="email-input-group">
                                <span class="auth-input-icon"><i class="bi bi-envelope-fill"></i></span>
                                <input id="email" type="email" name="email" class="auth-input" placeholder="Email Terdaftar">
                            </div>
                        </div>

                        <div class="auth-field-helper">
                            <a href="{{ route('landing') }}" class="auth-helper-link" id="link-landing">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                                Halaman Utama
                            </a>
                            <button type="button" id="toggle-forgot-password" class="auth-helper-link">Lupa Password?</button>
                            <button type="button" id="back-to-login" class="auth-helper-link" style="display: none;">Kembali ke Login</button>
                        </div>



                        <button type="submit" class="auth-btn-submit" id="login-submit" data-login-submit aria-busy="false">
                            <i class="ph-bold ph-sign-in" aria-hidden="true" id="submit-icon"></i>
                            <span class="auth-btn-text" id="submit-text">Log in</span>
                        </button>
                    </form>
                @endif

                <div class="auth-footer">
                    <p>&copy; {{ date('Y') }} Port Management Unit Suralaya</p>
                </div>
            </div>
        </div>

    </body>
</html>