@php
    /**
     * @var \Illuminate\Pagination\LengthAwarePaginator $items
     * @var bool   $firstLast  show « » first/last chevrons (default false)
     * @var int    $side       pages shown on each side of current (default 2)
     * @var string $unit       label word for the info text (default "entries")
     */
    $firstLast = $firstLast ?? false;
    $side = $side ?? 2;
    $unit = $unit ?? 'entries';
    $last = $items->lastPage();
    $current = $items->currentPage();

    $pages = collect([1]);
    for ($i = $current - $side; $i <= $current + $side; $i++) {
        if ($i > 1 && $i < $last) {
            $pages->push($i);
        }
    }
    if ($last > 1) {
        $pages->push($last);
    }
    $pages = $pages->unique()->sort()->values();
@endphp
<div class="pagination-wrapper">
    <div class="pagination-info">Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} {{ $unit }}</div>
    <div class="pagination">
        @if($firstLast)
            @if($items->onFirstPage())
                <span class="page-link disabled"><i class="fas fa-angles-left"></i></span>
            @else
                <a href="{{ $items->url(1) }}" class="page-link"><i class="fas fa-angles-left"></i></a>
            @endif
        @endif

        @if($items->onFirstPage())
            <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
        @else
            <a href="{{ $items->previousPageUrl() }}" class="page-link"><i class="fas fa-chevron-left"></i></a>
        @endif

        @php $prev = 0; @endphp
        @foreach($pages as $page)
            @if($page - $prev > 1)
                <span class="page-link disabled">&hellip;</span>
            @endif
            @if($page == $current)
                <span class="page-link active">{{ $page }}</span>
            @else
                <a href="{{ $items->url($page) }}" class="page-link">{{ $page }}</a>
            @endif
            @php $prev = $page; @endphp
        @endforeach

        @if($items->hasMorePages())
            <a href="{{ $items->nextPageUrl() }}" class="page-link"><i class="fas fa-chevron-right"></i></a>
        @else
            <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>
        @endif

        @if($firstLast)
            @if($current == $last)
                <span class="page-link disabled"><i class="fas fa-angles-right"></i></span>
            @else
                <a href="{{ $items->url($last) }}" class="page-link"><i class="fas fa-angles-right"></i></a>
            @endif
        @endif
    </div>
</div>
