{{-- Search + Filter toolbar for admin pages --}}
@props(['route', 'nopolList' => collect(), 'showShift' => false, 'showDate' => true])

<form method="GET" action="{{ route($route) }}" class="admin-toolbar" data-admin-toolbar>
    {{-- Search --}}
    <div class="admin-search-wrap">
        <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nopol, driver, jenis..." class="admin-search-input" data-admin-search>
        @if(request('search'))
            <a href="{{ route($route) }}" class="admin-search-clear" title="Hapus pencarian">&times;</a>
        @endif
    </div>

    {{-- Filters --}}
    <div class="admin-filter-row">
        @if($showDate)
        <div class="admin-filter-item">
            <label>Dari</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="admin-filter-input" data-admin-filter>
        </div>
        <div class="admin-filter-item">
            <label>Sampai</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="admin-filter-input" data-admin-filter>
        </div>
        @endif

        @if($nopolList->isNotEmpty())
        <div class="admin-filter-item">
            <label>Nopol</label>
            <select name="nopol" class="admin-filter-input" data-admin-filter>
                <option value="">Semua</option>
                @foreach($nopolList as $n)
                    <option value="{{ $n }}" {{ request('nopol') === $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        @endif

        @if($showShift)
        <div class="admin-filter-item">
            <label>Shift</label>
            <select name="shift" class="admin-filter-input" data-admin-filter>
                <option value="">Semua</option>
                <option value="Pagi" {{ request('shift') === 'Pagi' ? 'selected' : '' }}>Pagi</option>
                <option value="Siang" {{ request('shift') === 'Siang' ? 'selected' : '' }}>Siang</option>
            </select>
        </div>
        @endif

        <button type="submit" class="admin-filter-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><polygon points="22,3 2,3 10,12.46 10,19 14,21 14,12.46" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
            Filter
        </button>

        @if(request()->hasAny(['search', 'tanggal_dari', 'tanggal_sampai', 'nopol', 'shift']))
            <a href="{{ route($route) }}" class="admin-filter-reset">Reset</a>
        @endif
    </div>
</form>
