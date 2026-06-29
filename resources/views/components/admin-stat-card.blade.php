@props([
    'title',
    'value' => null,
    'unit' => null,
    'unitBefore' => null,
    'description' => null,
    'icon' => null,
    'valueClass' => null,
    'valueStyle' => null,
    // --- Optional filter-toggle behaviour ---
    // When filterKey & filterValue are provided, the card acts as a clickable
    // filter toggle. JavaScript in the consuming view is responsible for
    // reading [data-filter-key] / [data-filter-value] and toggling the
    // `is-active` class. When neither is supplied the card is purely decorative
    // (existing behaviour, no breaking change).
    'filterKey' => null,
    'filterValue' => null,
])

@php
    $isFilterable = $filterKey !== null;
    $extraAttrs = $isFilterable
        ? ['data-filter-key' => $filterKey, 'data-filter-value' => (string) $filterValue, 'role' => 'button', 'tabindex' => '0']
        : [];
@endphp

<div {{ $attributes->merge(array_merge(['class' => 'portal-stat-card'], $extraAttrs)) }}>
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
    @elseif(isset($iconSlot))
        <div class="portal-stat-icon" aria-hidden="true">
            {{ $iconSlot }}
        </div>
    @endif
</div>
