<x-guest-layout>
    <x-auth-brand title="{{ __('Daftar Akun') }}" :subtitle="__('Lengkapi data untuk membuat akun')" />

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label small text-muted mb-1">{{ __('Name') }}</label>
            <input
                id="name"
                type="text"
                name="name"
                class="form-control rounded-4 py-2 px-3 @error('name') is-invalid @enderror"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
            >
            @error('name')
                <div class="invalid-feedback small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="username" class="form-label small text-muted mb-1">{{ __('Username') }}</label>
            <input
                id="username"
                type="text"
                name="username"
                class="form-control rounded-4 py-2 px-3 @error('username') is-invalid @enderror"
                value="{{ old('username') }}"
                required
                autocomplete="username"
            >
            @error('username')
                <div class="invalid-feedback small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label small text-muted mb-1">{{ __('Email') }}</label>
            <input
                id="email"
                type="email"
                name="email"
                class="form-control rounded-4 py-2 px-3 @error('email') is-invalid @enderror"
                value="{{ old('email') }}"
                required
                autocomplete="email"
            >
            @error('email')
                <div class="invalid-feedback small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small text-muted mb-1">{{ __('Password') }}</label>
            <input
                id="password"
                type="password"
                name="password"
                class="form-control rounded-4 py-2 px-3 @error('password') is-invalid @enderror"
                required
                autocomplete="new-password"
            >
            @error('password')
                <div class="invalid-feedback small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label small text-muted mb-1">{{ __('Confirm Password') }}</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="form-control rounded-4 py-2 px-3"
                required
                autocomplete="new-password"
            >
        </div>

        <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-between gap-2">
            <a class="small text-decoration-none" href="{{ route('login') }}" style="color: var(--auth-navy);">
                {{ __('Already registered?') }}
            </a>
            <button type="submit" class="btn btn-primary auth-btn-primary text-white px-4">
                {{ __('Register') }}
            </button>
        </div>
    </form>
</x-guest-layout>
