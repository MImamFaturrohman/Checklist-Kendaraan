@props(['paginator'])

@if($paginator->hasPages())
    <div class="tbl-pagination-scroll">
        <div class="tbl-pagination tbl-pagination--unified">
            {{ $paginator->onEachSide(0)->withQueryString()->links() }}
        </div>
    </div>
@endif
