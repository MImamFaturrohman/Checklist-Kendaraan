@props([
    'title' => null,
    'subtitle' => null,
])

<div class="text-center mb-4">
    <div class="auth-brand-box py-3 px-2">
    <img src="{{ asset('images/ADCPM Landscape NEW.png') }}" 
             alt="Logo ADC PM" 
             class="img-fluid"
             style="max-height: 80px;">
    </div>
    @if ($title)
        <h1 class="auth-title h4 fw-bold mt-4 mb-1">{{ $title }}</h1>
    @endif
    @if ($subtitle)
        <p class="text-muted small mb-0">{{ $subtitle }}</p>
    @endif
</div>
