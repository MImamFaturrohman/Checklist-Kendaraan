@props([
    'id',
    'name' => 'per_page',
    'selected' => 10,
    'options' => [5, 10, 25, 50, 100],
])

@php $labelId = $id . '-label'; @endphp

<div {{ $attributes->merge(['class' => 'tbl-per-page']) }}>
    <span class="tbl-per-page__label" id="{{ $labelId }}">Per halaman</span>
    <label class="sr-only" for="{{ $id }}">Jumlah data per halaman</label>
    <select
        name="{{ $name }}"
        id="{{ $id }}"
        class="admin-filter-input tbl-per-page__select"
        aria-labelledby="{{ $labelId }}"
    >
        @foreach($options as $n)
            <option value="{{ $n }}" @selected((int) $selected === (int) $n)>{{ $n }}</option>
        @endforeach
    </select>
</div>
