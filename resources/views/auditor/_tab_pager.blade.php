@if(isset($items) && $items)
<div class="audit-pagination-container" @if(isset($id) && $id) id="{{ $id }}" @endif>
    <div class="audit-pagination-info">
        @if($items->total() > 0)
            Showing <span>{{ number_format($items->firstItem() ?? 0) }}</span> to <span>{{ number_format($items->lastItem() ?? 0) }}</span> of <span>{{ number_format($items->total()) }}</span> records
        @else
            <span>0</span> records found
        @endif
    </div>
    <div class="audit-pagination-buttons">
        @if ($items->total() > 0 && !$items->onFirstPage() && $items->hasPages())
            <a href="{{ $items->appends(request()->query())->previousPageUrl() }}" class="audit-page-btn"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</a>
        @else
            <span class="audit-page-btn disabled"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</span>
        @endif

        @php
            $currentPage = $items->currentPage() ?: 1;
            $lastPage = $items->lastPage() ?: 1;
            $start = max(1, $currentPage - 2);
            $end = min($lastPage, $currentPage + 2);
        @endphp
        @if($start > 1)
            <a href="{{ $items->appends(request()->query())->url(1) }}" class="audit-page-btn {{ $currentPage == 1 ? 'active-page' : '' }}">1</a>
            @if($start > 2)
                <span class="audit-page-btn disabled" style="border: none; background: transparent; padding: 0 4px;">...</span>
            @endif
        @endif
        @for ($p = $start; $p <= $end; $p++)
            @if($items->total() > 0 && $items->hasPages())
                <a href="{{ $items->appends(request()->query())->url($p) }}" class="audit-page-btn {{ $p == $currentPage ? 'active-page' : '' }}">{{ $p }}</a>
            @else
                <span class="audit-page-btn {{ $p == $currentPage ? 'active-page' : '' }}">{{ $p }}</span>
            @endif
        @endfor
        @if($end < $lastPage)
            @if($end < $lastPage - 1)
                <span class="audit-page-btn disabled" style="border: none; background: transparent; padding: 0 4px;">...</span>
            @endif
            <a href="{{ $items->appends(request()->query())->url($lastPage) }}" class="audit-page-btn {{ $currentPage == $lastPage ? 'active-page' : '' }}">{{ $lastPage }}</a>
        @endif

        @if ($items->total() > 0 && $items->hasMorePages())
            <a href="{{ $items->appends(request()->query())->nextPageUrl() }}" class="audit-page-btn">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></a>
        @else
            <span class="audit-page-btn disabled">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></span>
        @endif
    </div>
</div>
@endif
