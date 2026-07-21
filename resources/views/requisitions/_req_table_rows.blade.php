@forelse($requisitions as $req)
    @if($req instanceof \App\Models\InventoryBatch)
        @php
            $sraStatus = $req->approval_status;
            
            if ($sraStatus === 'approved') {
                $badgeColor = '#10b981';
                $badgeBg = 'rgba(16, 185, 129, 0.1)';
                $badgeLabel = 'Approved';
            } elseif ($sraStatus === 'declined') {
                $badgeColor = '#ef4444';
                $badgeBg = 'rgba(239, 68, 68, 0.1)';
                $badgeLabel = 'Declined';
            } else {
                $adminApproved = ($req->admin_status === 'approved');
                $auditorApproved = ($req->auditor_status === 'approved');
                
                if ($adminApproved && $auditorApproved) {
                    $badgeColor = '#10b981';
                    $badgeBg = 'rgba(16, 185, 129, 0.1)';
                    $badgeLabel = 'Approved';
                } elseif ($adminApproved) {
                    $badgeColor = '#3b82f6';
                    $badgeBg = 'rgba(59, 130, 246, 0.1)';
                    $badgeLabel = 'Authorizer Approved (Pending Auditor)';
                } elseif ($auditorApproved) {
                    $badgeColor = '#f59e0b';
                    $badgeBg = 'rgba(245, 158, 11, 0.1)';
                    $badgeLabel = 'Auditor Approved (Pending Authorizer)';
                } else {
                    $badgeColor = '#ef4444';
                    $badgeBg = 'rgba(239, 68, 68, 0.1)';
                    $badgeLabel = 'Awaiting Review (Auditor & Authorizer)';
                }
            }
            
            $usageLabel = 'Inventory SRA';
            $usageBg = 'rgba(16, 185, 129, 0.08)';
            $usageColor = '#10b981';
            
            $reviewUrl = route('receiveditems.sra', ['id' => $req->id]);
        @endphp
        <tr class="oversight-row" style="background: rgba(16, 185, 129, 0.015);">
            <td data-label="Ref">
                <span class="history-ref" style="font-size:0.75rem; color:#10b981; font-weight:800;">
                    SRA-{{ str_pad($req->id, 5, '0', STR_PAD_LEFT) }}
                </span>
            </td>
            <td data-label="Requester & Dept">
                <div style="font-weight:800;color:var(--text-main);font-size:0.85rem;">
                    {{ $req->recorder->name ?? 'Store Officer' }}
                </div>
                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:2px;">
                    Stores
                </div>
            </td>
            <td data-label="Items Requested">
                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                    <span class="table-item-pill" title="{{ $req->supplier_name }}" style="background: rgba(16,185,129,0.06); border-color: rgba(16,185,129,0.15);">
                        <i data-lucide="truck" style="width:12px;height:12px;display:inline-block;margin-right:3px;vertical-align:middle;color:#10b981;"></i>
                        {{ Str::limit($req->supplier_name ?: ($req->donor_name ?: 'Supplier'), 25) }}
                    </span>
                </div>
            </td>
            <td data-label="Purpose">
                <div style="font-size:0.8rem;color:var(--text-muted);font-weight:600;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    @php
                        $itemsList = $req->items->pluck('description')->join(', ');
                    @endphp
                    {{ Str::limit($itemsList, 40) }}
                </div>
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
                @if($req->approval_status === 'approved')
                    <a href="{{ $reviewUrl }}" target="_blank" style="background: rgba(16, 185, 129, 0.08); color: #10b981; border: 1.5px solid rgba(16, 185, 129, 0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; white-space: nowrap; text-decoration:none;" onmouseover="this.style.background='#10b981'; this.style.color='white'; this.style.borderColor='#10b981';" onmouseout="this.style.background='rgba(16, 185, 129, 0.08)'; this.style.color='#10b981'; this.style.borderColor='rgba(16, 185, 129, 0.2)';">
                        <i data-lucide="file-text" style="width:13px;height:13px;"></i> View Receipt
                    </a>
                @elseif($req->approval_status === 'declined')
                    <button type="button" onclick="showDeclinedNotice()" style="background: rgba(239, 68, 68, 0.08); color: #ef4444; border: 1.5px solid rgba(239, 68, 68, 0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; white-space: nowrap;" onmouseover="this.style.background='#ef4444'; this.style.color='white'; this.style.borderColor='#ef4444';" onmouseout="this.style.background='rgba(239, 68, 68, 0.08)'; this.style.color='#ef4444'; this.style.borderColor='rgba(239, 68, 68, 0.2)';">
                        <i data-lucide="x-circle" style="width:13px;height:13px;"></i> Declined
                    </button>
                @else
                    <a href="{{ $reviewUrl }}" target="_blank" style="background: rgba(16, 185, 129, 0.08); color: #10b981; border: 1.5px solid rgba(16, 185, 129, 0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; white-space: nowrap; text-decoration:none;" onmouseover="this.style.background='#10b981'; this.style.color='white'; this.style.borderColor='#10b981';" onmouseout="this.style.background='rgba(16, 185, 129, 0.08)'; this.style.color='#10b981'; this.style.borderColor='rgba(16, 185, 129, 0.2)';">
                        <i data-lucide="clipboard-check" style="width:13px;height:13px;"></i> Review
                    </a>
                @endif
            </td>
        </tr>
    @elseif($req instanceof \App\Models\ServiceSra)
        @php
            $sraStatus = $req->status;
            $pendingActors = [];
            if ($req->admin_status === 'pending') $pendingActors[] = 'Authorizer';
            if ($req->auditor_status === 'pending') $pendingActors[] = 'Auditor';
            if ($req->stores_status === 'pending') $pendingActors[] = 'Stores';
            
            if (empty($pendingActors)) {
                if ($sraStatus === 'approved') {
                    $badgeColor = '#10b981';
                    $badgeBg = 'rgba(16,185,129,0.1)';
                    $badgeLabel = 'Approved';
                } else {
                    $badgeColor = '#ef4444';
                    $badgeBg = 'rgba(239,68,68,0.1)';
                    $badgeLabel = 'Declined';
                }
            } else {
                if ($sraStatus === 'declined') {
                    $badgeColor = '#ef4444';
                    $badgeBg = 'rgba(239,68,68,0.1)';
                    $badgeLabel = 'Declined';
                } else {
                    $badgeColor = '#eab308';
                    $badgeBg = 'rgba(234,179,8,0.1)';
                    $badgeLabel = 'Pending: ' . implode(', ', $pendingActors);
                }
            }
            
            $usageLabel = 'Service';
            $usageBg = 'rgba(22, 163, 74, 0.08)';
            $usageColor = '#16a34a';
            
            $currentUser = auth()->user();
            if ($currentUser->role === 'Auditor') {
                $reviewUrl = route('auditor.service-sra.review', $req->id);
            } elseif ($currentUser->isMainAdminOrSub() || in_array($currentUser->role, ['Main Admin', 'Sub Main Admin'])) {
                $reviewUrl = route('admin.service-sra.index') . '?review=' . $req->id;
            } elseif ($currentUser->role === 'Head of Stores' || strcasecmp($currentUser->department ?? '', 'Stores') === 0 || strcasecmp($currentUser->department ?? '', 'Store') === 0) {
                $reviewUrl = route('stores.service-sra.index') . '?review=' . $req->id;
            } else {
                $reviewUrl = route('admin.service-sra.index') . '?review=' . $req->id;
            }

            $isUserProcessed = false;
            if ($currentUser->role === 'Auditor') {
                $isUserProcessed = ($req->auditor_status !== 'pending');
            } elseif ($currentUser->isMainAdminOrSub() || in_array($currentUser->role, ['Main Admin', 'Sub Main Admin'])) {
                $isUserProcessed = ($req->admin_status !== 'pending');
            } elseif ($currentUser->role === 'Head of Stores' || strcasecmp($currentUser->department ?? '', 'Stores') === 0 || strcasecmp($currentUser->department ?? '', 'Store') === 0) {
                $isUserProcessed = ($req->stores_status !== 'pending');
            } else {
                $isUserProcessed = ($req->admin_status !== 'pending');
            }
        @endphp
        <tr class="oversight-row" style="background: rgba(22, 163, 74, 0.015);">
            <td data-label="Ref">
                <span class="history-ref" style="font-size:0.75rem; color:#16a34a; font-weight:800;">
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
                    <span class="table-item-pill" title="{{ $req->supplier_name }}" style="background: rgba(22,163,74,0.06); border-color: rgba(22,163,74,0.15);">
                        <i data-lucide="wrench" style="width:12px;height:12px;display:inline-block;margin-right:3px;vertical-align:middle;color:#16a34a;"></i>
                        {{ Str::limit($req->supplier_name, 25) }}
                    </span>
                </div>
            </td>
            <td data-label="Purpose">
                <div style="font-size:0.8rem;color:var(--text-muted);font-weight:600;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $req->details }}">
                    {{ $req->details }}
                </div>
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
                @if($req->status === 'approved')
                    <a href="{{ route('service-sra.receipt', $req->id) }}" target="_blank" style="background: rgba(16, 185, 129, 0.08); color: #10b981; border: 1.5px solid rgba(16, 185, 129, 0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; white-space: nowrap; text-decoration:none;" onmouseover="this.style.background='#10b981'; this.style.color='white'; this.style.borderColor='#10b981';" onmouseout="this.style.background='rgba(16, 185, 129, 0.08)'; this.style.color='#10b981'; this.style.borderColor='rgba(16, 185, 129, 0.2)';">
                        <i data-lucide="file-text" style="width:13px;height:13px;"></i> View Receipt
                    </a>
                @elseif($req->status === 'declined')
                    <button type="button" onclick="showDeclinedNotice()" style="background: rgba(239, 68, 68, 0.08); color: #ef4444; border: 1.5px solid rgba(239, 68, 68, 0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; white-space: nowrap;" onmouseover="this.style.background='#ef4444'; this.style.color='white'; this.style.borderColor='#ef4444';" onmouseout="this.style.background='rgba(239, 68, 68, 0.08)'; this.style.color='#ef4444'; this.style.borderColor='rgba(239, 68, 68, 0.2)';">
                        <i data-lucide="x-circle" style="width:13px;height:13px;"></i> Declined
                    </button>
                @elseif($isUserProcessed)
                    <button type="button" onclick="showReceiptNotice()" style="background: rgba(100, 116, 139, 0.08); color: #64748b; border: 1.5px solid rgba(100, 116, 139, 0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; white-space: nowrap;" onmouseover="this.style.background='#64748b'; this.style.color='white'; this.style.borderColor='#64748b';" onmouseout="this.style.background='rgba(100, 116, 139, 0.08)'; this.style.color='#64748b'; this.style.borderColor='rgba(100, 116, 139, 0.2)';">
                        <i data-lucide="file-text" style="width:13px;height:13px;"></i> View Receipt
                    </button>
                @else
                    @php
                        $sraModalStage = 'admin';
                        if ($currentUser->isMainAdminOrSub() || in_array($currentUser->role, ['Main Admin', 'Sub Main Admin'])) {
                            $sraModalStage = 'admin';
                        } elseif ($currentUser->role === 'Head of Stores' || strcasecmp($currentUser->department ?? '', 'Stores') === 0 || strcasecmp($currentUser->department ?? '', 'Store') === 0) {
                            $sraModalStage = 'stores';
                        } else {
                            $sraModalStage = 'admin';
                        }
                    @endphp
                    @if($currentUser->role === 'Auditor')
                        <a href="{{ $reviewUrl }}" target="_blank" style="background: rgba(22,163,74,0.08); color: #16a34a; border: 1.5px solid rgba(22,163,74,0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; white-space: nowrap; text-decoration:none;" onmouseover="this.style.background='#16a34a'; this.style.color='white'; this.style.borderColor='#16a34a';" onmouseout="this.style.background='rgba(22,163,74,0.08)'; this.style.color='#16a34a'; this.style.borderColor='rgba(22,163,74,0.2)';">
                            <i data-lucide="clipboard-check" style="width:13px;height:13px;"></i> Review
                        </a>
                    @else
                        <button type="button" onclick="openSraOversightModal({{ $req->id }}, '{{ $sraModalStage }}')" style="background: rgba(22,163,74,0.08); color: #16a34a; border: 1.5px solid rgba(22,163,74,0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; white-space: nowrap; text-decoration:none;" onmouseover="this.style.background='#16a34a'; this.style.color='white'; this.style.borderColor='#16a34a';" onmouseout="this.style.background='rgba(22,163,74,0.08)'; this.style.color='#16a34a'; this.style.borderColor='rgba(22,163,74,0.2)';">
                            <i data-lucide="clipboard-check" style="width:13px;height:13px;"></i> Review
                        </button>
                    @endif
                @endif
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

        <td data-label="Status">
            <span class="status-pill" style="background:{{ $sb['bg'] }};color:{{ $sb['color'] }};font-size:0.65rem;">
                ● {{ $sb['label'] }}
            </span>
            @if($req->status === 'pending')
                <div style="font-size:0.7rem;color:var(--text-muted);margin-top:4px;font-weight:600;">
                    Next: <span style="color:var(--text-main);font-weight:800;">{{ $req->approver_name }}</span>
                </div>
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
            @php
                $deptsMatch = function($dept1, $dept2) {
                    $d1 = strtolower(trim($dept1));
                    $d2 = strtolower(trim($dept2));
                    if ($d1 === $d2) return true;
                    
                    $hrTerms = ['hr', 'human resource', 'human resource management department', 'human resources'];
                    if (in_array($d1, $hrTerms) && in_array($d2, $hrTerms)) return true;
                    
                    $welfareTerms = ['welfare', 'welfare department'];
                    if (in_array($d1, $welfareTerms) && in_array($d2, $welfareTerms)) return true;
                    
                    $storesTerms = ['stores', 'store', 'stores department', 'store department'];
                    if (in_array($d1, $storesTerms) && in_array($d2, $storesTerms)) return true;
                    
                    return false;
                };

                $isStoresHead = (auth()->user()->isMainAdminOrSub() || auth()->user()->role === 'Head of Stores' || strcasecmp(auth()->user()->department ?? '', 'Stores') === 0 || strcasecmp(auth()->user()->department ?? '', 'Store') === 0);
                if (!$isStoresHead) {
                    $isBackup = (auth()->user()->isDepartmentHead() && in_array(auth()->user()->department, ['Human Resource Management Department', 'Welfare Department']));
                    if ($isBackup) {
                        if (!\App\Models\User::isPrimaryStoresHeadOnline()) {
                            $isStoresHead = true;
                        }
                    }
                }
                $isReqProcessed = false;
                if (in_array(auth()->user()->role, ['Main Admin', 'Sub Main Admin'])) {
                    // Authorizers (Main Admin / Sub Main Admin)
                    $isActingAsHOD = ($deptsMatch($req->department ?? '', auth()->user()->department ?? '') && $req->origin_admin_status === 'pending');
                    if ($isActingAsHOD) {
                        $isReqProcessed = ($req->origin_admin_status !== 'pending');
                    } else {
                        $isReqProcessed = ($req->main_admin_status !== 'pending' || $req->status !== 'pending');
                    }
                } elseif (auth()->user()->role === 'Head of Stores' || $isStoresHead) {
                    // Head of Stores / Logistics backup
                    $isReqProcessed = ($req->status !== 'pending');
                } else {
                    // Normal HOD / Auditor
                    $isReqProcessed = ($req->origin_admin_status !== 'pending' || $req->status !== 'pending');
                }
            @endphp
            @if($req->status === 'approved' || $req->status === 'partially_approved')
                <a href="{{ route('requisitions.receipt.print', $req->id) }}" target="_blank" style="background: rgba(16, 185, 129, 0.08); color: #10b981; border: 1.5px solid rgba(16, 185, 129, 0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; white-space: nowrap; text-decoration:none;" onmouseover="this.style.background='#10b981'; this.style.color='white'; this.style.borderColor='#10b981';" onmouseout="this.style.background='rgba(16, 185, 129, 0.08)'; this.style.color='#10b981'; this.style.borderColor='rgba(16, 185, 129, 0.2)';">
                    <i data-lucide="file-text" style="width:13px;height:13px;"></i> View Receipt
                </a>
            @elseif($req->status === 'declined')
                <button type="button" onclick="showDeclinedNotice()" style="background: rgba(239, 68, 68, 0.08); color: #ef4444; border: 1.5px solid rgba(239, 68, 68, 0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; white-space: nowrap;" onmouseover="this.style.background='#ef4444'; this.style.color='white'; this.style.borderColor='#ef4444';" onmouseout="this.style.background='rgba(239, 68, 68, 0.08)'; this.style.color='#ef4444'; this.style.borderColor='rgba(239, 68, 68, 0.2)';">
                    <i data-lucide="x-circle" style="width:13px;height:13px;"></i> Declined
                </button>
            @elseif($isReqProcessed)
                <button onclick="openRequisitionModal({{ $req->id }})" style="background: rgba(16, 185, 129, 0.08); color: #10b981; border: 1.5px solid rgba(16, 185, 129, 0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; white-space: nowrap;" onmouseover="this.style.background='#10b981'; this.style.color='white'; this.style.borderColor='#10b981';" onmouseout="this.style.background='rgba(16, 185, 129, 0.08)'; this.style.color='#10b981'; this.style.borderColor='rgba(16, 185, 129, 0.2)';">
                    <i data-lucide="check" style="width:13px;height:13px;"></i> Processed
                </button>
            @else
                <button onclick="openRequisitionModal({{ $req->id }})" style="background: rgba(22,163,74,0.08); color: #16a34a; border: 1.5px solid rgba(22,163,74,0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; white-space: nowrap;" onmouseover="this.style.background='#16a34a'; this.style.color='white'; this.style.borderColor='#16a34a';" onmouseout="this.style.background='rgba(22,163,74,0.08)'; this.style.color='#16a34a'; this.style.borderColor='rgba(22,163,74,0.2)';">
                    <i data-lucide="clipboard-check" style="width:13px;height:13px;"></i> Review
                </button>
            @endif
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
