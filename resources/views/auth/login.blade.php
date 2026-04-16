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
        <div class="auth-floating-shapes">
            <div class="auth-shape auth-shape-1"></div>
            <div class="auth-shape auth-shape-2"></div>
            <div class="auth-shape auth-shape-3"></div>
        </div>

        <div class="auth-container">
            {{-- LEFT: Car visual --}}
            <div class="auth-visual">
                <img src="{{ asset('images/mobil pick up.jpeg') }}" alt="Kendaraan Operasional" class="auth-car-img">
                <div class="auth-visual-overlay">
                    <p class="auth-visual-text">FLEET MONITORING & INSPECTION</p>
                </div>
            </div>

            {{-- RIGHT: Login form --}}
            <div class="auth-form-side">
                <div class="auth-form-inner">
                    <div class="text-center mb-4">
                        <div class="auth-brand-box py-3 px-2">
                            <img src="{{ asset('images/ADCPM Landscape NEW.png') }}" alt="Logo ADC PM" class="img-fluid" style="max-height: 70px;">
                        </div>
                        <h1 class="auth-title h4 fw-bold mt-3 mb-1">CEKLIST KENDARAAN</h1>
                        <p class="auth-subtitle small mb-0">Sistem Serah Terima Operasional</p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success small py-2 mb-3" role="alert">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-2" data-login-form>
                        @csrf
                        <div class="mb-3">
                            <label for="username" class="visually-hidden">{{ __('Username') }}</label>
                            <div class="input-group auth-input-group rounded-4 overflow-hidden shadow-sm">
                                <span class="input-group-text ps-3"><i class="bi bi-person" aria-hidden="true"></i></span>
                                <input id="username" type="text" name="username" class="form-control py-3 @error('username') is-invalid @enderror" value="{{ old('username') }}" placeholder="{{ __('Username') }}" required autofocus autocomplete="username">
                            </div>
                            @error('username')
                                <div class="invalid-feedback d-block small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="password" class="visually-hidden">{{ __('Password') }}</label>
                            <div class="input-group auth-input-group rounded-4 overflow-hidden shadow-sm">
                                <span class="input-group-text ps-3"><i class="bi bi-lock" aria-hidden="true"></i></span>
                                <input id="password" type="password" name="password" class="form-control py-3 @error('password') is-invalid @enderror" placeholder="{{ __('Password') }}" required autocomplete="current-password">
                                <button class="input-group-text auth-password-toggle px-3" type="button" data-password-toggle aria-label="Toggle password visibility">
                                    <i class="bi bi-eye" data-password-icon aria-hidden="true"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary auth-btn-primary text-white" data-login-submit>
                                {{ __('Masuk Aplikasi') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
