{{-- Partial: requisition table rows + pagination (rendered for AJAX requests) --}}
@forelse($requisitions as $req)
    @php
        $pb  = $req->priority_badge;
        $sb  = $req->status_badge;
        $utb = $req->usage_type_badge;
        $purposeText = trim(preg_replace('/\[Expected Return Date:\s*[^\]]+\]/i', '', $req->purpose));
    @endphp
    <tr class="oversight-row">
        <td data-label="Ref">
            <span class="history-ref" style="font-size:0.75rem;">
                {{ $req->unique_id ?: ('REQ-'.str_pad($req->id,5,'0',STR_PAD_LEFT)) }}
            </span>
        </td>
        <td data-label="Requester & Dept">
            <div style="font-weight:800;color:var(--text-main);font-size:0.85rem;">
                {{ $req->requester_name }}{{ $req->rank_or_title ? ' ('.$req->rank_or_title.')' : '' }}
            </div>
            <div style="font-size:0.75rem;color:var(--text-muted);margin-top:2px;">
                {{ $req->department }}
            </div>
        </td>
        <td data-label="Items Requested">
            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                @foreach($req->items as $item)
                    @php
                        $approvedVal = $item->quantity_approved !== null ? (float)$item->quantity_approved : null;
                        $altApproved = $item->alternative_quantity_approved !== null ? (float)$item->alternative_quantity_approved : 0;
                    @endphp
                    <span class="table-item-pill" title="{{ $item->description }}">
                        {{ Str::limit($item->description, 20) }}
                        <span class="table-item-qty">×{{ number_format($item->quantity_requested,0) }}</span>
                        @if($approvedVal !== null)
                            <span class="table-item-approved">(✓{{ number_format($approvedVal+$altApproved,0) }})</span>
                        @endif
                    </span>
                @endforeach
            </div>
        </td>
        <td data-label="Purpose">
            <div style="font-size:0.8rem;color:var(--text-muted);font-weight:600;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $purposeText }}">
                {{ $purposeText }}
            </div>
        </td>
        <td data-label="Priority">
            <span class="status-pill" style="background:{{ $pb['bg'] }};color:{{ $pb['color'] }};font-size:0.65rem;">
                {{ $pb['label'] }}
            </span>
        </td>
        <td data-label="Status">
            <span class="status-pill" style="background:{{ $sb['bg'] }};color:{{ $sb['color'] }};font-size:0.65rem;">
                ● {{ $sb['label'] }}
            </span>
        </td>
        <td data-label="Usage">
            <span class="status-pill" style="background:{{ $utb['bg'] }};color:{{ $utb['color'] }};font-size:0.65rem;">
                {{ $utb['label'] }}
            </span>
        </td>
        <td data-label="Submitted">
            <div style="font-size:0.78rem;color:var(--text-muted);font-weight:600;white-space:nowrap;">
                {{ $req->created_at->format('d/m/Y') }}
            </div>
            <div style="font-size:0.7rem;color:var(--text-muted);">
                {{ $req->created_at->format('H:i') }}
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" style="text-align:center;padding:4rem 2rem;border-bottom:none;">
            <i data-lucide="inbox" style="width:36px;height:36px;margin:0 auto 0.75rem;display:block;opacity:.25;color:#10b981;"></i>
            <div style="font-weight:900;color:var(--text-main);margin-bottom:4px;">All Caught Up!</div>
            <div style="font-size:.82rem;color:var(--text-muted);">No requisitions match your current filter.</div>
        </td>
    </tr>
@endforelse
