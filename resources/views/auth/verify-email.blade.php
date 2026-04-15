<x-guest-layout>
    <x-auth-brand title="{{ __('Verifikasi Email') }}" :subtitle="__('Klik tautan di email Anda untuk mengaktifkan akun')" />

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success small py-2 mb-3" role="alert">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between align-items-stretch align-items-sm-center">
        <form method="POST" action="{{ route('verification.send') }}" class="d-grid flex-grow-1">
            @csrf
            <button type="submit" class="btn btn-primary auth-btn-primary text-white">
                {{ __('Resend Verification Email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100 rounded-pill">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
