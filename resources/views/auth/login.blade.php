<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Login - Ceklist Kendaraan Sistem Serah Terima Operasional">
        <title>Login - {{ config('app.name', 'Ceklist Kendaraan') }}</title>
        @vite(['resources/css/auth.css', 'resources/js/app.js'])
    </head>
    <body class="auth-page-body">
        {{-- Animated background particles --}}
        <div class="auth-particles" id="auth-particles"></div>

        {{-- Main login card --}}
        <div class="auth-card" id="login-card">
            {{-- Vehicle image header with logo overlay --}}
            <div class="auth-card-image">
                <img src="{{ asset('images/mobil pick up.jpeg') }}" alt="Kendaraan Operasional" class="auth-hero-img">
                <div class="auth-card-image-overlay"></div>
                <div class="auth-logo-overlay">
                    <img src="{{ asset('images/ADCPM Landscape NEW.png') }}" alt="Logo PT Artha" class="auth-logo-img">
                </div>
            </div>

            {{-- Card body --}}
            <div class="auth-card-body">
                {{-- Title section --}}
                <div class="auth-card-header">
                    <h1 class="auth-title" id="login-title">CEKLIST KENDARAAN</h1>
                    <p class="auth-subtitle" id="login-subtitle">SISTEM SERAH TERIMA OPERASIONAL</p>
                </div>

                {{-- Status message --}}
                @if (session('status'))
                    <div class="auth-alert auth-alert-success" role="alert" id="login-status">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Login form --}}
                <form method="POST" action="{{ route('login') }}" class="auth-form" data-login-form id="login-form">
                    @csrf

                    {{-- Username field --}}
                    <div class="auth-field" id="username-field">
                        <div class="auth-input-group @error('username') has-error @enderror">
                            <span class="auth-input-icon">
                                <i class="bi bi-person-fill" aria-hidden="true"></i>
                            </span>
                            <input
                                id="username"
                                type="text"
                                name="username"
                                class="auth-input"
                                value="{{ old('username') }}"
                                placeholder="{{ __('Username') }}"
                                required
                                autofocus
                                autocomplete="username"
                            >
                        </div>
                        @error('username')
                            <div class="auth-error-msg" id="username-error">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Password field --}}
                    <div class="auth-field" id="password-field">
                        <div class="auth-input-group @error('password') has-error @enderror">
                            <span class="auth-input-icon">
                                <i class="bi bi-lock-fill" aria-hidden="true"></i>
                            </span>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="auth-input"
                                placeholder="{{ __('Password') }}"
                                required
                                autocomplete="current-password"
                            >
                            <button class="auth-password-toggle" type="button" data-password-toggle aria-label="Toggle password visibility" id="password-toggle">
                                <i class="bi bi-eye" data-password-icon aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="auth-error-msg" id="password-error">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Submit button --}}
                    <button type="submit" class="auth-btn-submit" data-login-submit id="login-submit">
                        <span class="auth-btn-text">Sign In</span>
                        <span class="auth-btn-arrow">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Inline script for particles --}}
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const container = document.getElementById('auth-particles');
                if (!container) return;
                for (let i = 0; i < 30; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'auth-particle';
                    const size = Math.random() * 4 + 2;
                    particle.style.width = size + 'px';
                    particle.style.height = size + 'px';
                    particle.style.left = Math.random() * 100 + '%';
                    particle.style.top = Math.random() * 100 + '%';
                    particle.style.animationDelay = Math.random() * 6 + 's';
                    particle.style.animationDuration = (Math.random() * 8 + 6) + 's';
                    particle.style.opacity = Math.random() * 0.3 + 0.1;
                    container.appendChild(particle);
                }
            });
        </script>
    </body>
</html>
