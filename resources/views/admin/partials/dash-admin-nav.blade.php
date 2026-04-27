{{-- Expects: $pageTitle, $pageSubtitle (optional), $navChipLabel + $navChipClass (optional) --}}
@php
    $navChipLabel = $navChipLabel ?? 'ADMIN';
    $navChipClass = $navChipClass ?? 'dash-chip-admin';
@endphp
<nav class="dash-nav" id="dash-nav">
    <div class="dash-nav-inner">
        <div class="dash-nav-brand">
            <img src="{{ asset('images/VMS.png') }}" alt="VMS" class="dash-nav-logo">
            <div>
                <div class="dash-nav-title">{{ $pageTitle }}</div>
                <span class="dash-nav-sub">{{ $pageSubtitle ?? 'PT ARTHA DAYA COALINDO' }}</span>
            </div>
        </div>
        <div class="dash-nav-actions" id="dash-nav-actions">
            <button class="dash-theme-btn" id="dash-theme-toggle" title="Ganti Tema" aria-label="Toggle Tema">
                <i class="bi bi-moon-fill" id="dash-theme-icon"></i>
                <span class="dash-theme-mode-label" id="dash-theme-label">Dark Mode</span>
            </button>
            <span class="dash-chip {{ $navChipClass }}">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
                <span class="dash-nav-chip-label">{{ $navChipLabel }}</span>
            </span>
            <a href="{{ route('dashboard') }}" class="dash-nav-btn-glass" aria-label="Dashboard">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="dash-nav-btn-label">Dashboard</span>
            </a>
        </div>
        <button class="dash-mobile-menu-btn" id="dash-mobile-menu-btn" aria-label="Buka Menu" aria-expanded="false">
            <i class="bi bi-list" id="dash-mobile-menu-icon"></i>
        </button>
    </div>
</nav>
