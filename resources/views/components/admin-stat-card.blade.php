@props([
    'title',
    'value' => null,
    'unit' => null,
    'unitBefore' => null,
    'description' => null,
    'icon' => null,
    'valueClass' => null,
    'valueStyle' => null,
])

<div {{ $attributes->merge(['class' => 'portal-stat-card']) }}>
    <div class="portal-stat-body">
        <div class="portal-stat-label">{{ $title }}</div>
        @if(isset($value))
            <div class="portal-stat-value-row">
                @if($unitBefore)
                    <span class="portal-stat-unit">{{ $unitBefore }}</span>
                @endif
                <span @class(['portal-stat-value', $valueClass]) @if($valueStyle) style="{{ $valueStyle }}" @endif>{{ $value }}</span>
                @if($unit)
                    <span class="portal-stat-unit">{{ $unit }}</span>
                @endif
            </div>
        @elseif(isset($statValue))
            {{ $statValue }}
        @endif
        {{ $slot }}
        @if($description)
            <div class="portal-stat-desc">{{ $description }}</div>
        @endif
    </div>
    @if($icon)
        <div class="portal-stat-icon" aria-hidden="true">
            <i class="{{ $icon }}"></i>
        </div>
    @endif
</div>
