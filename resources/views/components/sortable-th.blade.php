@props([
    'key',          // string  — matches data-sort attr and ?sort= param value
    'label',        // string  — visible text
    'activeSort' => null,
    'activeDir'  => null,
    'scope'      => null,  // e.g. 'pending' → ?pending_sort=
    'class'      => '',
    'style'      => '',
])

@php
    $isActive   = $activeSort === $key;
    $sortState  = $isActive ? ($activeDir === 'desc' ? 'desc' : 'asc') : 'none';
    $ariaSort   = $isActive ? ($activeDir === 'asc' ? 'ascending' : 'descending') : null;
    $iconClass  = match ($sortState) {
        'asc'  => 'bi bi-arrow-up',
        'desc' => 'bi bi-arrow-down',
        default => 'bi bi-arrow-down-up',
    };
    $thClass    = trim('th-sortable ' . $class);
@endphp

<th
    data-sort="{{ $key }}"
    data-sort-state="{{ $sortState }}"
    @if($ariaSort) aria-sort="{{ $ariaSort }}" @endif
    class="{{ $thClass }}"
    @if($style) style="{{ $style }}" @endif
><span class="th-sortable__inner"><span class="th-sortable__label">{{ $label }}</span><i class="th-sortable__icon {{ $iconClass }}" aria-hidden="true"></i></span></th>
