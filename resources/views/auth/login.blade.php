<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/auth.css', 'resources/js/app.js'])
    </head>
    <body class="auth-page-body">
        <div class="auth-scene">
            {{-- Floating particles --}}
            <div class="auth-particles">
                <span></span><span></span><span></span><span></span><span></span>
            </div>

            <div class="auth-card-new">
                {{-- Car image header --}}
                <div class="auth-car-header">
                    <img src="{{ asset('images/mobil pick up.jpeg') }}" alt="Kendaraan Operasional" class="auth-car-img">
                    <div class="auth-car-overlay"></div>
                </div>

                {{-- Content --}}
                <div class="auth-card-body">
                    <div class="auth-logo-row">
                        <img src="{{ asset('images/ADCPM Landscape NEW.png') }}" alt="Logo ADC PM" class="auth-logo-img">
                    </div>
                    <h1 class="auth-main-title">CEKLIST KENDARAAN</h1>
                    <p class="auth-main-sub">SISTEM SERAH TERIMA OPERASIONAL</p>

                    @if (session('status'))
                        <div class="auth-alert-success">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="auth-form" data-login-form>
                        @csrf
                        <div class="auth-input-row">
                            <span class="auth-input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/><path d="M20 21c0-3.3-3.1-6-8-6s-8 2.7-8 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                            <input id="username" type="text" name="username" value="{{ old('username') }}" placeholder="Username" required autofocus autocomplete="username">
                        </div>
                        @error('username')
                            <p class="auth-field-error">{{ $message }}</p>
                        @enderror

                        <div class="auth-input-row">
                            <span class="auth-input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/><path d="M8 11V7a4 4 0 018 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                            <input id="password" type="password" name="password" placeholder="Password" required autocomplete="current-password">
                            <button class="auth-password-eye" type="button" data-password-toggle aria-label="Toggle password">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" data-password-icon-svg><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="auth-field-error">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="auth-submit-btn" data-login-submit>
                            Sign In
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
