<x-guest-layout>
    <x-auth-brand title="{{ __('Lupa Password') }}" :subtitle="__('Masukkan email untuk menerima tautan reset')" />

    @if (session('status'))
        <div class="alert alert-success small py-2 mb-3" role="alert">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="form-label small text-muted mb-1">{{ __('Email') }}</label>
            <input
                id="email"
                type="email"
                name="email"
                class="form-control rounded-4 py-2 px-3 @error('email') is-invalid @enderror"
                value="{{ old('email') }}"
                required
                autofocus
            >
            @error('email')
                <div class="invalid-feedback small">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary auth-btn-primary text-white">
                {{ __('Email Password Reset Link') }}
            </button>
        </div>
    </form>
</x-guest-layout>
