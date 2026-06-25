{{-- Partial: pagination controls (rendered for AJAX requests) --}}
@if($requisitions->hasPages())
<div id="req-pagination" style="padding:1.25rem 1.5rem;border:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;background:var(--bg-card);border-radius:16px;box-shadow:0 4px 20px rgba(15,23,42,0.04);margin-top:1rem;">
    <div style="font-size:0.82rem;font-weight:700;color:var(--text-muted);">
        Showing
        <span style="color:var(--text-main);font-weight:900;">{{ $requisitions->firstItem() ?? 0 }}</span>–<span style="color:var(--text-main);font-weight:900;">{{ $requisitions->lastItem() ?? 0 }}</span>
        of <span style="color:var(--text-main);font-weight:900;">{{ $requisitions->total() }}</span> entries
    </div>
    <div style="display:flex;align-items:center;gap:0.4rem;">
        @if($requisitions->onFirstPage())
            <span style="padding:0.45rem 0.9rem;border-radius:8px;background:var(--bg-main);color:var(--text-muted);font-size:0.78rem;font-weight:800;border:1px solid var(--border-color);opacity:.5;cursor:not-allowed;">‹ Prev</span>
        @else
            <a href="#" data-page="{{ $requisitions->currentPage() - 1 }}" class="ajax-page-btn" style="padding:0.45rem 0.9rem;border-radius:8px;background:var(--bg-card);color:var(--text-main);font-size:0.78rem;font-weight:800;border:1px solid var(--border-color);text-decoration:none;transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-main)'">‹ Prev</a>
        @endif
        @foreach($requisitions->getUrlRange(max(1,$requisitions->currentPage()-2),min($requisitions->lastPage(),$requisitions->currentPage()+2)) as $page => $url)
            @if($page == $requisitions->currentPage())
                <span style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:8px;background:var(--primary);color:white;font-size:0.82rem;font-weight:900;">{{ $page }}</span>
            @else
                <a href="#" data-page="{{ $page }}" class="ajax-page-btn" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:8px;background:var(--bg-card);color:var(--text-main);font-size:0.82rem;font-weight:800;border:1px solid var(--border-color);text-decoration:none;transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-main)'">{{ $page }}</a>
            @endif
        @endforeach
        @if($requisitions->hasMorePages())
            <a href="#" data-page="{{ $requisitions->currentPage() + 1 }}" class="ajax-page-btn" style="padding:0.45rem 0.9rem;border-radius:8px;background:var(--bg-card);color:var(--text-main);font-size:0.78rem;font-weight:800;border:1px solid var(--border-color);text-decoration:none;transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-main)'">Next ›</a>
        @else
            <span style="padding:0.45rem 0.9rem;border-radius:8px;background:var(--bg-main);color:var(--text-muted);font-size:0.78rem;font-weight:800;border:1px solid var(--border-color);opacity:.5;cursor:not-allowed;">Next ›</span>
        @endif
    </div>
</div>
@endif
