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
            @if(auth()->user()->role === 'Head of Stores')
                @php
                    $pipeline = $req->tracking_pipeline;
                    $step1 = $pipeline['hod'];
                    $step2 = $pipeline['stores_hod'];
                    $step3 = $pipeline['dg'];
                    $step4 = $pipeline['head_of_stores'];
                @endphp
                <div class="mini-tracker" style="max-width: 190px; gap: 2px; margin-top: 8px;">
                    <!-- Step 1: HOD -->
                    <div class="mini-step {{ $step1['status'] }}" title="{{ $step1['label'] }} (Reviewer: {{ $step1['user'] }})">
                        <div class="mini-dot">
                            <i data-lucide="{{ $step1['icon'] }}" style="width: 10px; height: 10px;"></i>
                        </div>
                        <span class="mini-label">HOD</span>
                    </div>
                    
                    <div class="mini-line {{ in_array($step2['status'], ['completed', 'active', 'declined']) && $step2['status'] !== 'bypassed' ? 'completed' : '' }}"></div>
                    
                    <!-- Step 2: Stores HOD -->
                    <div class="mini-step {{ $step2['status'] }}" title="{{ $step2['label'] }} (Reviewer: {{ $step2['user'] }})">
                        <div class="mini-dot">
                            <i data-lucide="{{ $step2['icon'] }}" style="width: 10px; height: 10px;"></i>
                        </div>
                        <span class="mini-label">Stores HOD</span>
                    </div>
                    
                    <div class="mini-line {{ in_array($step3['status'], ['completed', 'active', 'declined']) && $step3['status'] !== 'bypassed' ? 'completed' : '' }}"></div>
                    
                    <!-- Step 3: DG -->
                    <div class="mini-step {{ $step3['status'] }}" title="{{ $step3['label'] }} (Reviewer: {{ $step3['user'] }})">
                        <div class="mini-dot">
                            <i data-lucide="{{ $step3['icon'] }}" style="width: 10px; height: 10px;"></i>
                        </div>
                        <span class="mini-label">DG</span>
                    </div>
                    
                    <div class="mini-line {{ in_array($step4['status'], ['completed', 'active', 'declined']) && $step4['status'] !== 'bypassed' ? 'completed' : '' }}"></div>
                    
                    <!-- Step 4: Stores Final -->
                    <div class="mini-step {{ $step4['status'] }}" title="{{ $step4['label'] }} (Reviewer: {{ $step4['user'] }})">
                        <div class="mini-dot">
                            <i data-lucide="{{ $step4['icon'] }}" style="width: 10px; height: 10px;"></i>
                        </div>
                        <span class="mini-label">Stores Final</span>
                    </div>
                </div>
                @if($req->status === 'pending')
                    <div style="font-size:0.7rem;color:var(--text-muted);margin-top:6px;font-weight:600;">
                        Next: <span style="color:var(--text-main);font-weight:800;">{{ $req->approver_name }}</span>
                    </div>
                @endif
            @else
                @if(auth()->user()->role === 'Main Admin' || strcasecmp(auth()->user()->department ?? '', 'Stores') === 0 || strcasecmp(auth()->user()->department ?? '', 'Store') === 0)
                    @if($req->status === 'pending')
                        <div style="font-size:0.7rem;color:var(--text-muted);margin-top:4px;font-weight:600;">
                            Next: <span style="color:var(--text-main);font-weight:800;">{{ $req->approver_name }}</span>
                        </div>
                    @endif
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
@empty
    <tr>
        <td colspan="9" style="text-align:center;padding:4rem 2rem;border-bottom:none;">
            <i data-lucide="inbox" style="width:36px;height:36px;margin:0 auto 0.75rem;display:block;opacity:.25;color:#10b981;"></i>
            <div style="font-weight:900;color:var(--text-main);margin-bottom:4px;">All Caught Up!</div>
            <div style="font-size:.82rem;color:var(--text-muted);">No requisitions match your current filter.</div>
        </td>
    </tr>
@endforelse
