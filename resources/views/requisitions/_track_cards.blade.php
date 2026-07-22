{{-- Partial: Track Staff Requests grid cards --}}
@forelse($requisitions as $req)
    @php
        $pb  = $req->priority_badge;
        $sb  = $req->status_badge;
        $utb = $req->usage_type_badge;
        $purposeText = trim(preg_replace('/\[Expected Return Date:\s*[^\]]+\]/i', '', $req->purpose));

        $hodStatus = $req->origin_admin_status;
        $storesStatus = $req->main_admin_status;
        $finalStatus = $req->status;
        $collected = $req->collected_at !== null;

        // Step 1: Submitted
        $step1Status = 'completed';
        $step1Icon = 'file-text';

        // Step 2: HOD Approval
        if ($hodStatus === 'declined' || ($hodStatus === 'pending' && $finalStatus === 'declined')) {
            $step2Status = 'declined';
            $step2Icon = 'x';
            $step2Label = 'HOD Declined';
        } elseif ($hodStatus === 'approved') {
            $step2Status = 'completed';
            $step2Icon = 'check';
            $step2Label = 'HOD Approved';
        } else {
            $step2Status = 'active';
            $step2Icon = 'clock';
            $step2Label = 'Awaiting HOD';
        }

        // Step 3: Stores Approval
        if ($hodStatus === 'declined' || ($hodStatus === 'pending' && $finalStatus === 'declined')) {
            $step3Status = 'bypassed';
            $step3Icon = 'minus';
            $step3Label = 'Stores Bypassed';
        } elseif ($storesStatus === 'declined' || ($storesStatus === 'pending' && $finalStatus === 'declined')) {
            $step3Status = 'declined';
            $step3Icon = 'x';
            $step3Label = 'Stores Declined';
        } elseif ($storesStatus === 'approved' || in_array($finalStatus, ['approved', 'partially_approved'])) {
            $step3Status = 'completed';
            $step3Icon = 'check';
            $step3Label = 'Stores Approved';
        } elseif ($hodStatus === 'approved') {
            $step3Status = 'active';
            $step3Icon = 'clock';
            $step3Label = ($storesStatus === 'pending') ? 'Awaiting Authorizer' : 'Awaiting Stores';
        } else {
            $step3Status = 'pending';
            $step3Icon = 'circle';
            $step3Label = 'Stores Review';
        }

        // Step 4: Collection
        if ($hodStatus === 'declined' || $storesStatus === 'declined' || $finalStatus === 'declined') {
            $step4Status = 'bypassed';
            $step4Icon = 'minus';
            $step4Label = 'No Collection';
        } elseif ($collected) {
            $step4Status = 'completed';
            $step4Icon = 'check';
            $step4Label = 'Collected';
        } elseif (in_array($finalStatus, ['approved', 'partially_approved'])) {
            $step4Status = 'active';
            $step4Icon = 'package';
            $step4Label = 'Ready to Collect';
        } else {
            $step4Status = 'pending';
            $step4Icon = 'circle';
            $step4Label = 'Collection';
        }
    @endphp

    <div class="track-card {{ $req->priority }}-priority" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease; position: relative; box-shadow: var(--shadow-premium);" data-id="{{ $req->id }}">
        
        <div>
            {{-- Top Row --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <span style="font-family: monospace; font-size: 0.78rem; font-weight: 850; background: var(--bg-main); border: 1px solid var(--border-color); padding: 4px 10px; border-radius: 8px; color: var(--text-main);">
                    {{ $req->unique_id ?: ('REQ-'.str_pad($req->id,5,'0',STR_PAD_LEFT)) }}
                </span>
                <div style="display: flex; gap: 6px; align-items: center;">
                    <span class="pill" style="background:{{ $pb['bg'] }}; color:{{ $pb['color'] }}; font-size: 0.65rem;">
                        {{ $pb['label'] }}
                    </span>
                    <span class="pill" style="background:{{ $utb['bg'] }}; color:{{ $utb['color'] }}; font-size: 0.65rem;">
                        {{ $utb['label'] }}
                    </span>
                </div>
            </div>

            {{-- Requester profile --}}
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 1.25rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(136, 19, 55, 0.08); color: var(--primary); border: 1px solid rgba(136, 19, 55, 0.15); display: flex; align-items: center; justify-content: center; font-weight: 850; font-size: 1.1rem; flex-shrink: 0;">
                    {{ strtoupper(substr($req->requester_name ?? 'R', 0, 1)) }}
                </div>
                <div style="min-width: 0; flex: 1;">
                    <h4 style="margin: 0; font-size: 0.92rem; font-weight: 850; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $req->requester_name }}">
                        {{ $req->requester_name }}
                    </h4>
                    <p style="margin: 2px 0 0; font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $req->department }}{{ $req->rank_or_title ? ' · '.$req->rank_or_title : '' }}
                    </p>
                </div>
            </div>

            {{-- Items details --}}
            <div style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.85rem 1rem; margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 0.65rem; font-weight: 850; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 4px;">
                        <i data-lucide="layers" style="width: 12px; height: 12px;"></i> Items Requested
                    </span>
                    <span style="font-size: 0.72rem; font-weight: 800; color: var(--text-main);">
                        {{ $req->items->count() }} {{ Str::plural('Item', $req->items->count()) }}
                    </span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    @foreach($req->items->take(2) as $item)
                        @php
                            $approvedVal = $item->quantity_approved !== null ? (float)$item->quantity_approved : null;
                            $altApproved = $item->alternative_quantity_approved !== null ? (float)$item->alternative_quantity_approved : 0;
                        @endphp
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem;">
                            <span style="color: var(--text-main); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;" title="{{ $item->description }}">
                                {{ $item->description }}
                            </span>
                            <span style="color: var(--text-muted); font-weight: 750;">
                                ×{{ number_format($item->quantity_requested, 0) }}
                                @if($approvedVal !== null)
                                    <span style="color: var(--success-color); font-weight: 800; margin-left: 2px;">(✓{{ number_format($approvedVal + $altApproved, 0) }})</span>
                                @endif
                            </span>
                        </div>
                    @endforeach
                    @if($req->items->count() > 2)
                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-align: right; margin-top: 2px;">
                            +{{ $req->items->count() - 2 }} more {{ Str::plural('item', $req->items->count() - 2) }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Stepper Progress Pipeline --}}
            <div class="stepper-pipeline" style="display: flex; justify-content: space-between; align-items: flex-start; position: relative; margin-bottom: 0.5rem; padding: 0 4px;">
                
                {{-- Connector segment 1-2 --}}
                <div class="stepper-connector line-1-2 {{ $step2Status === 'completed' ? 'completed' : ($step2Status === 'declined' ? 'declined' : ($step1Status === 'completed' && $step2Status === 'active' ? 'active' : '')) }}" style="position: absolute; top: 11px; left: 12.5%; width: 25%; height: 2px; background: var(--border-color); z-index: 1;"></div>
                {{-- Connector segment 2-3 --}}
                <div class="stepper-connector line-2-3 {{ $step3Status === 'completed' ? 'completed' : ($step3Status === 'declined' ? 'declined' : ($step3Status === 'bypassed' ? 'bypassed' : ($step2Status === 'completed' && $step3Status === 'active' ? 'active' : ''))) }}" style="position: absolute; top: 11px; left: 37.5%; width: 25%; height: 2px; background: var(--border-color); z-index: 1;"></div>
                {{-- Connector segment 3-4 --}}
                <div class="stepper-connector line-3-4 {{ $step4Status === 'completed' ? 'completed' : ($step4Status === 'bypassed' ? 'bypassed' : ($step3Status === 'completed' && $step4Status === 'active' ? 'active' : '')) }}" style="position: absolute; top: 11px; left: 62.5%; width: 25%; height: 2px; background: var(--border-color); z-index: 1;"></div>

                {{-- Node 1: Submitted --}}
                <div class="stepper-node completed" style="display: flex; flex-direction: column; align-items: center; width: 25%; text-align: center; z-index: 2; position: relative;">
                    <div class="stepper-dot" style="width: 24px; height: 24px; border-radius: 50%; background: var(--success-color); border: 2px solid var(--success-color); color: white; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="{{ $step1Icon }}" style="width: 12px; height: 12px;"></i>
                    </div>
                    <span class="stepper-label" style="font-size: 0.6rem; font-weight: 850; color: var(--success-color); text-transform: uppercase; margin-top: 6px; letter-spacing: 0.02em;">Submitted</span>
                </div>

                {{-- Node 2: HOD Approval --}}
                <div class="stepper-node {{ $step2Status }}" style="display: flex; flex-direction: column; align-items: center; width: 25%; text-align: center; z-index: 2; position: relative;">
                    <div class="stepper-dot" style="width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                        <i data-lucide="{{ $step2Icon }}" style="width: 12px; height: 12px;"></i>
                    </div>
                    <span class="stepper-label" style="font-size: 0.6rem; font-weight: 850; text-transform: uppercase; margin-top: 6px; letter-spacing: 0.02em;">{{ $step2Label }}</span>
                </div>

                {{-- Node 3: Stores Review --}}
                <div class="stepper-node {{ $step3Status }}" style="display: flex; flex-direction: column; align-items: center; width: 25%; text-align: center; z-index: 2; position: relative;">
                    <div class="stepper-dot" style="width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                        <i data-lucide="{{ $step3Icon }}" style="width: 12px; height: 12px;"></i>
                    </div>
                    <span class="stepper-label" style="font-size: 0.6rem; font-weight: 850; text-transform: uppercase; margin-top: 6px; letter-spacing: 0.02em;">{{ $step3Label }}</span>
                </div>

                {{-- Node 4: Collection --}}
                <div class="stepper-node {{ $step4Status }}" style="display: flex; flex-direction: column; align-items: center; width: 25%; text-align: center; z-index: 2; position: relative;">
                    <div class="stepper-dot" style="width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                        <i data-lucide="{{ $step4Icon }}" style="width: 12px; height: 12px;"></i>
                    </div>
                    <span class="stepper-label" style="font-size: 0.6rem; font-weight: 850; text-transform: uppercase; margin-top: 6px; letter-spacing: 0.02em;">{{ $step4Label }}</span>
                </div>

            </div>
        </div>

        {{-- Footer Row --}}
        <div style="border-top: 1px solid var(--border-color); margin-top: 1.25rem; padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 0.74rem; color: var(--text-muted); font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="clock" style="width: 13px; height: 13px;"></i> {{ $req->created_at->diffForHumans() }}
            </span>
            <button onclick="openRequisitionModal({{ $req->id }})" class="btn-track-details" style="background: rgba(136, 19, 55, 0.08); color: var(--primary); border: 1px solid rgba(136, 19, 55, 0.15); padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all 0.2s;">
                <span>Details</span> <i data-lucide="arrow-right" style="width: 13px; height: 13px;"></i>
            </button>
        </div>

    </div>
@empty
    <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; box-shadow: var(--shadow-premium);">
        <i data-lucide="compass" style="width: 44px; height: 44px; margin: 0 auto 1rem; display: block; opacity: .3; color: #881337; animation: pulse-compass 2.5s infinite;"></i>
        <h3 style="font-weight: 900; color: var(--text-main); margin-bottom: 6px; font-size: 1.1rem;">No Requisitions in Pipeline</h3>
        <p style="font-size: 0.86rem; color: var(--text-muted); margin: 0;">No active staff requests match the selected criteria.</p>
    </div>
@endforelse
