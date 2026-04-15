<x-guest-layout>
    <x-auth-brand title="CEKLIST KENDARAAN" subtitle="Sistem Serah Terima Operasional" />

    @if (session('status'))
        <div class="alert alert-success small py-2 mb-3" role="alert">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-2" data-login-form>
        @csrf

        <div class="mb-3">
            <label for="username" class="visually-hidden">{{ __('Username') }}</label>
            <div class="input-group auth-input-group rounded-4 overflow-hidden shadow-sm">
                <span class="input-group-text ps-3"><i class="bi bi-person" aria-hidden="true"></i></span>
                <input
                    id="username"
                    type="text"
                    name="username"
                    class="form-control py-3 @error('username') is-invalid @enderror"
                    value="{{ old('username') }}"
                    placeholder="{{ __('Username') }}"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>
            @error('username')
                <div class="invalid-feedback d-block small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="visually-hidden">{{ __('Password') }}</label>
            <div class="input-group auth-input-group rounded-4 overflow-hidden shadow-sm">
                <span class="input-group-text ps-3"><i class="bi bi-lock" aria-hidden="true"></i></span>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control py-3 @error('password') is-invalid @enderror"
                    placeholder="{{ __('Password') }}"
                    required
                    autocomplete="current-password"
                >
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

        @if (Route::has('password.request'))
            <div class="text-center mt-3">
                <a class="small text-decoration-none auth-link" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            </div>
        @endif
    </form>
</x-guest-layout>
