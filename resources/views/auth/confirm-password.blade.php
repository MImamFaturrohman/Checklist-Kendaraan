<x-guest-layout>
    <x-auth-brand title="{{ __('Konfirmasi Password') }}" :subtitle="__('Area aman — masukkan password untuk melanjutkan')" />

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-4">
            <label for="password" class="form-label small text-muted mb-1">{{ __('Password') }}</label>
            <input
                id="password"
                type="password"
                name="password"
                class="form-control rounded-4 py-2 px-3 @error('password') is-invalid @enderror"
                required
                autocomplete="current-password"
            >
            @error('password')
                <div class="invalid-feedback small">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary auth-btn-primary text-white">
                {{ __('Confirm') }}
            </button>
        </div>
    </form>
</x-guest-layout>
