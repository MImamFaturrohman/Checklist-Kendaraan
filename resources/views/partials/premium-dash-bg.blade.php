@php
    $premiumBgId = preg_replace('/[^a-zA-Z0-9_-]/', '', $premiumBgId ?? 'vmsdash');
@endphp
<div class="dash-bg-cubes" aria-hidden="true"></div>
<div class="dash-bg-sparkle" aria-hidden="true"></div>
<div class="dash-bg-royal-tint" aria-hidden="true"></div>
<div class="dash-bg-stardust" aria-hidden="true"></div>
<div class="dash-bg-orb-gold" aria-hidden="true"></div>
<div class="dash-bg-orb-blue" aria-hidden="true"></div>
<div class="dash-bg-wave" aria-hidden="true">
    <svg viewBox="0 0 1440 400" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%" preserveAspectRatio="none">
        <path class="vms-wave-line-solid" d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50" stroke="url(#{{ $premiumBgId }}_stroke)" stroke-width="2" stroke-linecap="round" fill="none"></path>
        <path class="vms-wave-line-dashed" d="M0 360 C 250 380, 450 180, 750 220 C 1050 260, 1200 80, 1440 120" stroke="url(#{{ $premiumBgId }}_stroke)" stroke-width="1.5" stroke-dasharray="6 12" stroke-linecap="round" fill="none" style="opacity:0.5"></path>
        <defs>
            <linearGradient id="{{ $premiumBgId }}_stroke" x1="0" y1="150" x2="1440" y2="150" gradientUnits="userSpaceOnUse">
                <stop stop-color="#0A2342"></stop>
                <stop offset="0.5" stop-color="#D4AF37"></stop>
                <stop offset="1" stop-color="#60A5FA"></stop>
            </linearGradient>
        </defs>
    </svg>
</div>
