@forelse($requisitions as $req)
    @if($req instanceof \App\Models\ServiceSra)
        @php
            $sraStatus = $req->status;
            $badgeColor = '#6366f1';
            $badgeBg = 'rgba(99,102,241,0.1)';
            $badgeLabel = 'Awaiting Admin Review';
            if ($sraStatus === 'admin_approved') {
                $badgeColor = '#f59e0b';
                $badgeBg = 'rgba(245,158,11,0.1)';
                $badgeLabel = 'Awaiting Stores Review';
            } elseif ($sraStatus === 'approved') {
                $badgeColor = '#10b981';
                $badgeBg = 'rgba(16,185,129,0.1)';
                $badgeLabel = 'Approved';
            } elseif ($sraStatus === 'declined') {
                $badgeColor = '#ef4444';
                $badgeBg = 'rgba(239,68,68,0.1)';
                $badgeLabel = 'Declined';
            }
            
            $usageLabel = 'Service';
            $usageBg = 'rgba(79, 70, 229, 0.08)';
            $usageColor = '#4f46e5';
            
            $isStoresHead = (auth()->user()->role === 'Main Admin' || auth()->user()->role === 'Head of Stores' || strcasecmp(auth()->user()->department ?? '', 'Stores') === 0 || strcasecmp(auth()->user()->department ?? '', 'Store') === 0);
            if ($isStoresHead && $sraStatus === 'admin_approved') {
                $reviewUrl = route('stores.service-sra.index') . '?review=' . $req->id;
            } else {
                $reviewUrl = route('admin.service-sra.index') . '?review=' . $req->id;
            }
        @endphp
        <tr class="oversight-row" style="background: rgba(99, 102, 241, 0.015);">
            <td data-label="Ref">
                <span class="history-ref" style="font-size:0.75rem; color:#4f46e5; font-weight:800;">
                    {{ $req->sra_number }}
                </span>
            </td>
            <td data-label="Requester & Dept">
                <div style="font-weight:800;color:var(--text-main);font-size:0.85rem;">
                    {{ $req->submitter->name ?? 'Store Officer' }}
                </div>
                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:2px;">
                    {{ $req->dept ?? 'Stores' }}
                </div>
            </td>
            <td data-label="Items Requested">
                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                    <span class="table-item-pill" title="{{ $req->supplier_name }}" style="background: rgba(99,102,241,0.06); border-color: rgba(99,102,241,0.15);">
                        <i data-lucide="wrench" style="width:12px;height:12px;display:inline-block;margin-right:3px;vertical-align:middle;color:#4f46e5;"></i>
                        {{ Str::limit($req->supplier_name, 25) }}
                    </span>
                </div>
            </td>
            <td data-label="Purpose">
                <div style="font-size:0.8rem;color:var(--text-muted);font-weight:600;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $req->details }}">
                    {{ $req->details }}
                </div>
            </td>
            <td data-label="Priority">
                <span class="status-pill" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; font-size:0.65rem;">
                    SRA
                </span>
            </td>
            <td data-label="Status">
                <span class="status-pill" style="background:{{ $badgeBg }};color:{{ $badgeColor }};font-size:0.65rem;">
                    ● {{ $badgeLabel }}
                </span>
            </td>
            <td data-label="Usage">
                <span class="status-pill" style="background:{{ $usageBg }};color:{{ $usageColor }};font-size:0.65rem;">
                    {{ $usageLabel }}
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
            <td data-label="Actions">
                <button type="button" onclick="openSraOversightModal({{ $req->id }}, '{{ $sraStatus === 'admin_approved' ? 'stores' : 'admin' }}')" style="background: rgba(99,102,241,0.08); color: #4f46e5; border: 1.5px solid rgba(99,102,241,0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; white-space: nowrap; text-decoration:none;" onmouseover="this.style.background='#4f46e5'; this.style.color='white'; this.style.borderColor='#4f46e5';" onmouseout="this.style.background='rgba(99,102,241,0.08)'; this.style.color='#4f46e5'; this.style.borderColor='rgba(99,102,241,0.2)';">
                    <i data-lucide="clipboard-check" style="width:13px;height:13px;"></i> Review
                </button>
            </td>
        </tr>
    @else
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
            @if(auth()->user()->role === 'Main Admin' || auth()->user()->role === 'Head of Stores' || strcasecmp(auth()->user()->department ?? '', 'Stores') === 0 || strcasecmp(auth()->user()->department ?? '', 'Store') === 0)
                @if($req->status === 'pending')
                    <div style="font-size:0.7rem;color:var(--text-muted);margin-top:4px;font-weight:600;">
                        Next: <span style="color:var(--text-main);font-weight:800;">{{ $req->approver_name }}</span>
                    </div>
                @endif
            @endif
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
        <td data-label="Actions">
            <button onclick="openRequisitionModal({{ $req->id }})" style="background: rgba(99,102,241,0.08); color: #4f46e5; border: 1.5px solid rgba(99,102,241,0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; white-space: nowrap;" onmouseover="this.style.background='#4f46e5'; this.style.color='white'; this.style.borderColor='#4f46e5';" onmouseout="this.style.background='rgba(99,102,241,0.08)'; this.style.color='#4f46e5'; this.style.borderColor='rgba(99,102,241,0.2);'">
                <i data-lucide="clipboard-check" style="width:13px;height:13px;"></i> Review
            </button>
        </td>
    </tr>
    @endif
@empty
    <tr>
        <td colspan="9" style="text-align:center;padding:4rem 2rem;border-bottom:none;">
            <i data-lucide="inbox" style="width:36px;height:36px;margin:0 auto 0.75rem;display:block;opacity:.25;color:#10b981;"></i>
            <div style="font-weight:900;color:var(--text-main);margin-bottom:4px;">All Caught Up!</div>
            <div style="font-size:.82rem;color:var(--text-muted);">No requisitions match your current filter.</div>
        </td>
    </tr>
@endforelse
