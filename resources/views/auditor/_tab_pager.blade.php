@if($items->hasPages())
<div class="audit-pagination-container">
    <div class="audit-pagination-info">
        Showing <span>{{ $items->firstItem() ?? 0 }}</span> to <span>{{ $items->lastItem() ?? 0 }}</span> of <span>{{ $items->total() }}</span> records
    </div>
    <div class="audit-pagination-buttons">
        @if ($items->onFirstPage())
            <span class="audit-page-btn disabled"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</span>
        @else
            <a href="{{ $items->appends(request()->query())->previousPageUrl() }}" class="audit-page-btn"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</a>
        @endif

        @if ($items->hasMorePages())
            <a href="{{ $items->appends(request()->query())->nextPageUrl() }}" class="audit-page-btn">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></a>
        @else
            <span class="audit-page-btn disabled">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></span>
        @endif
    </div>
</div>
@endif
