@props(['status'])
@php
    $meta = \App\Support\SppdStatus::meta($status);
@endphp
<span class="{{ $meta['badge'] }}">{{ $meta['label'] }}</span>
