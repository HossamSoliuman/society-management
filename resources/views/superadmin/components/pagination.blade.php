@if($items->hasPages())
<div class="pagination-wrapper">
    <div class="pagination-info">Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} entries</div>
    <div class="pagination">
        @if($items->onFirstPage())
            <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
        @else
            <a href="{{ $items->previousPageUrl() }}" class="page-link"><i class="fas fa-chevron-left"></i></a>
        @endif

        @foreach($items->getUrlRange(1, $items->lastPage()) as $page => $url)
            @if($page == $items->currentPage())
                <span class="page-link active">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="page-link">{{ $page }}</a>
            @endif
        @endforeach

        @if($items->hasMorePages())
            <a href="{{ $items->nextPageUrl() }}" class="page-link"><i class="fas fa-chevron-right"></i></a>
        @else
            <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>
        @endif
    </div>
    <div>
        <select class="form-control" style="width: auto; min-width: 80px;">
            <option>10 / page</option>
            <option>25 / page</option>
            <option>50 / page</option>
        </select>
    </div>
</div>
@endif
