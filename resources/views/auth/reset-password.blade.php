<x-guest-layout>
    <x-auth-brand title="{{ __('Reset Password') }}" :subtitle="__('Atur password baru untuk akun Anda')" />

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="form-label small text-muted mb-1">{{ __('Email') }}</label>
            <input
                id="email"
                type="email"
                name="email"
                class="form-control rounded-4 py-2 px-3 @error('email') is-invalid @enderror"
                value="{{ old('email', $request->email) }}"
                required
                autofocus
                autocomplete="username"
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

        <div class="d-grid">
            <button type="submit" class="btn btn-primary auth-btn-primary text-white">
                {{ __('Reset Password') }}
            </button>
        </div>
    </form>
</x-guest-layout>
