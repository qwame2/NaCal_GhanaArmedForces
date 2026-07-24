<table style="width:100%;border-collapse:collapse;">
    <thead style="background:var(--bg-main);">
        <tr>
            <th style="padding:1rem 1.5rem;text-align:center;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;width:10%;">Ref</th>
            <th style="padding:1rem 1.5rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;width:20%;">Requester & Dept</th>
            <th style="padding:1rem 1.5rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;width:20%;">Items Requested</th>
            <th style="padding:1rem 1.5rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;width:15%;">Purpose</th>
            <th style="padding:1rem 1.5rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;width:12%;">Status</th>
            <th style="padding:1rem 1.5rem;text-align:center;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;width:8%;">Usage</th>
            <th style="padding:1rem 1.5rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;width:10%;">Submitted</th>
            <th style="padding:1rem 1.5rem;text-align:center;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;width:8%;">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($requisitions as $req)
        @if($req instanceof \App\Models\ServiceSra)
            @php
                $sraStatus = $req->status;
                $badgeColor = '#059669';
                $badgeBg = 'rgba(5,150,105,0.1)';
                $badgeLabel = 'Awaiting Authorizer Review';
                if ($sraStatus === 'auditor_pending') {
                    $badgeColor = '#047857';
                    $badgeBg = 'rgba(139,92,246,0.1)';
                    $badgeLabel = 'Awaiting Auditor Review';
                } elseif ($sraStatus === 'admin_approved') {
                    $badgeColor = '#059669';
                    $badgeBg = 'rgba(5,150,105,0.1)';
                    $badgeLabel = 'Awaiting Stores Review';
                } elseif ($sraStatus === 'approved') {
                    $badgeColor = '#059669';
                    $badgeBg = 'rgba(5,150,105,0.1)';
                    $badgeLabel = 'Approved';
                } elseif ($sraStatus === 'declined') {
                    $badgeColor = '#ef4444';
                    $badgeBg = 'rgba(239,68,68,0.1)';
                    $badgeLabel = 'Declined';
                }
                
                $usageLabel = 'Service';
                $usageBg = 'rgba(5, 150, 105, 0.08)';
                $usageColor = '#059669';
            @endphp
            <tr class="req-table-row" data-type="sra" data-id="{{ $req->id }}" data-status="{{ $sraStatus }}" style="background: rgba(5, 150, 105, 0.015);">
                <td style="padding:1rem 1.5rem; text-align:center;">
                    <span style="background: rgba(5, 150, 105, 0.08); color: #059669; font-size: 0.75rem; font-weight: 800; padding: 5px 12px; border-radius: 99px; display: inline-block; white-space: nowrap;">
                        {{ $req->sra_number }}
                    </span>
                </td>
                <td style="padding:1rem 1.5rem;">
                    <div style="font-weight: 800; color: var(--text-main); font-size: 0.85rem;">{{ $req->submitter->name ?? 'Store Officer' }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">{{ $req->dept ?? 'Stores' }}</div>
                </td>
                <td style="padding:1rem 1.5rem;">
                    <div style="display:flex; flex-wrap:wrap; gap:6px;">
                        <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-main); background: rgba(5,150,105,0.06); border: 1px solid rgba(5,150,105,0.15); padding: 4px 10px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                            <i data-lucide="wrench" style="width:12px;height:12px;color:#059669;"></i>
                            {{ Str::limit($req->supplier_name, 25) }}
                        </span>
                    </div>
                </td>
                <td style="padding:1rem 1.5rem;">
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $req->details }}">
                        {{ $req->details }}
                    </div>
                </td>

                <td style="padding:1rem 1.5rem; text-align:left;">
                    <span class="pill" style="background:{{ $badgeBg }}; color:{{ $badgeColor }}; font-size: 0.68rem; padding: 4px 10px; border-radius: 99px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                        ● {{ $badgeLabel }}
                    </span>
                </td>
                <td style="padding:1rem 1.5rem; text-align:center;">
                    <span class="pill" style="background:{{ $usageBg }}; color:{{ $usageColor }}; font-size: 0.68rem; padding: 4px 10px; border-radius: 99px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ $usageLabel }}
                    </span>
                </td>
                <td style="padding:1rem 1.5rem; font-size:.78rem; color:var(--text-muted); font-weight:600; line-height: 1.4;">
                    {{ $req->created_at->format('d/m/Y') }}<br>
                    <span style="font-size: 0.72rem; color: var(--text-muted);">{{ $req->created_at->format('H:i') }}</span>
                </td>
                <td style="padding:1rem 1.5rem; text-align:center;">
                    @if($req->status === 'approved' || $req->status === 'declined')
                        <button onclick="openSraOversightModal({{ $req->id }}, '{{ $sraStatus === 'admin_approved' ? 'stores' : 'admin' }}')"
                            style="background: rgba(5, 150, 105, 0.08); color: #059669; border: 1.5px solid rgba(5, 150, 105, 0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; white-space: nowrap;"
                            onmouseover="this.style.background='#059669'; this.style.color='white'; this.style.borderColor='#059669';"
                            onmouseout="this.style.background='rgba(5, 150, 105, 0.08)'; this.style.color='#059669'; this.style.borderColor='rgba(5, 150, 105, 0.2)';">
                            <i data-lucide="check" style="width:14px; height:14px;"></i> PROCESSED
                        </button>
                    @else
                        <button onclick="openSraOversightModal({{ $req->id }}, '{{ $sraStatus === 'admin_approved' ? 'stores' : 'admin' }}')"
                            style="background: rgba(5, 150, 105, 0.08); color: #059669; border: 1.5px solid rgba(5, 150, 105, 0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; white-space: nowrap;"
                            onmouseover="this.style.background='#059669'; this.style.color='white'; this.style.borderColor='#059669';"
                            onmouseout="this.style.background='rgba(5, 150, 105, 0.08)'; this.style.color='#059669'; this.style.borderColor='rgba(5, 150, 105, 0.2)';">
                            <i data-lucide="clipboard-check" style="width:14px; height:14px;"></i> REVIEW
                        </button>
                    @endif
                </td>
            </tr>
        @else
            @php $sb = $req->status_badge; $pb = $req->priority_badge; @endphp
            <tr class="req-table-row" data-type="req" data-id="{{ $req->id }}" data-req-id="{{ $req->id }}" data-status="{{ $req->status }}" data-collected="{{ $req->collected_at ? '1' : '0' }}">
            <td style="padding:1rem 1.5rem; text-align:center;">
                <span style="background: rgba(234, 88, 12, 0.08); color: #065f46; font-size: 0.75rem; font-weight: 800; padding: 5px 12px; border-radius: 99px; display: inline-block; white-space: nowrap;">
                    {{ $req->unique_id ?: ('REQ-'.str_pad($req->id, 5, '0', STR_PAD_LEFT)) }}
                </span>
            </td>
            <td style="padding:1rem 1.5rem;">
                <div style="font-weight: 800; color: var(--text-main); font-size: 0.85rem;">{{ $req->requester_name }}</div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">{{ $req->department }}{{ $req->rank_or_title ? ' · '.$req->rank_or_title : '' }}</div>
            </td>
            <td style="padding:1rem 1.5rem;">
                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                    @foreach($req->items->take(3) as $item)
                    <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-main); background: var(--bg-main); border: 1px solid var(--border-color); padding: 4px 10px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                        {{ Str::limit($item->description, 20) }} <span style="color:#065f46; font-weight:800;">×{{ number_format($item->quantity_requested, 0) }}</span>
                    </span>
                    @endforeach
                    @if($req->items->count() > 3)
                    <span style="font-size: 0.72rem; font-weight: 700; color: #059669; background: rgba(5, 150, 105, 0.1); padding: 4px 10px; border-radius: 8px; white-space: nowrap;">+{{ $req->items->count() - 3 }} more</span>
                    @endif
                </div>
            </td>
            <td style="padding:1rem 1.5rem;">
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $req->purpose }}">
                    {{ $req->purpose }}
                </div>
            </td>

            <td style="padding:1rem 1.5rem; text-align:left;">
                <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
                    <span class="pill" style="background:{{ $sb['bg'] }}; color:{{ $sb['color'] }}; font-size: 0.68rem; padding: 4px 10px; border-radius: 99px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                        ● {{ $sb['label'] }}
                    </span>
                    @if(auth()->user()->role === 'Head of Stores')
                        @php
                            $pipeline = $req->tracking_pipeline;
                            $step1 = $pipeline['hod'] ?? null;
                            $step2 = $pipeline['dg'] ?? null;
                            $step3 = $pipeline['stores_hod'] ?? null;
                            $step4 = $pipeline['head_of_stores'] ?? null;
                            $nextReviewer = 'N/A';
                            if ($req->status === 'pending') {
                                if ($step1 && $step1['status'] === 'active') {
                                    $nextReviewer = $step1['user'] ?: 'Department Head';
                                } elseif ($step2 && $step2['status'] === 'active') {
                                    $nextReviewer = 'Director General';
                                } elseif ($step3 && $step3['status'] === 'active') {
                                    $nextReviewer = 'Authorizer';
                                } elseif ($step4 && $step4['status'] === 'active') {
                                    $nextReviewer = 'Head of Stores';
                                }
                            }
                        @endphp
                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600; margin-top: 2px;">
                            Next: <span style="color: var(--text-main); font-weight: 800;">{{ $nextReviewer }}</span>
                        </div>
                    @endif
                </div>
            </td>
            <td style="padding:1rem 1.5rem; text-align:center;">
                @php $utb = $req->usage_type_badge; @endphp
                <span class="pill" style="background:{{ $utb['bg'] }}; color:{{ $utb['color'] }}; font-size: 0.68rem; padding: 4px 10px; border-radius: 99px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                    {{ $utb['label'] }}
                </span>
            </td>
            <td style="padding:1rem 1.5rem; font-size:.78rem; color:var(--text-muted); font-weight:600; line-height: 1.4;">
                {{ $req->created_at->format('d/m/Y') }}<br>
                <span style="font-size: 0.72rem; color: var(--text-muted);">{{ $req->created_at->format('H:i') }}</span>
            </td>
            <td style="padding:1rem 1.5rem; text-align:center;">
                @php
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
                if ($isStoresHead) {
                    $isReqProcessed = ($req->status !== 'pending');
                } else {
                    $isReqProcessed = ($req->origin_admin_status === 'approved' || $req->origin_admin_status === 'declined' || $req->status === 'approved' || $req->status === 'partially_approved' || $req->status === 'declined');
                }
                @endphp
                @if($isReqProcessed)
                    <button onclick="openRequisitionModal({{ $req->id }})"
                        style="background: rgba(5, 150, 105, 0.08); color: #059669; border: 1.5px solid rgba(5, 150, 105, 0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; white-space: nowrap;"
                        onmouseover="this.style.background='#059669'; this.style.color='white'; this.style.borderColor='#059669';"
                        onmouseout="this.style.background='rgba(5, 150, 105, 0.08)'; this.style.color='#059669'; this.style.borderColor='rgba(5, 150, 105, 0.2)';">
                        <i data-lucide="check" style="width:14px; height:14px;"></i> PROCESSED
                    </button>
                @else
                    <button onclick="openRequisitionModal({{ $req->id }})"
                        style="background: rgba(5, 150, 105, 0.08); color: #059669; border: 1.5px solid rgba(5, 150, 105, 0.2); padding: 0.45rem 1rem; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; white-space: nowrap;"
                        onmouseover="this.style.background='#059669'; this.style.color='white'; this.style.borderColor='#059669';"
                        onmouseout="this.style.background='rgba(5, 150, 105, 0.08)'; this.style.color='#059669'; this.style.borderColor='rgba(5, 150, 105, 0.2)';">
                        <i data-lucide="clipboard-check" style="width:14px; height:14px;"></i> REVIEW
                    </button>
                @endif
            </td>
        </tr>
        @endif
        @empty
        <tr>
            <td colspan="9" style="padding:3rem;text-align:center;color:var(--text-muted);">
                <i data-lucide="inbox" style="width:32px;margin-bottom:.75rem;opacity:.3;"></i>
                <p style="font-weight:700;color:var(--text-main);">No requisitions found</p>
                <p style="font-size:.85rem;">Department requests will appear here.</p>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
@if($requisitions->hasPages())
<div style="padding:1rem 1.5rem; border-top:1px solid var(--border-color); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem;">
    <div style="font-size:.75rem; font-weight:700; color:var(--text-muted);">
        Showing
        <span style="font-weight:900; color:var(--text-main);">{{ $requisitions->firstItem() }}</span>
        &ndash;
        <span style="font-weight:900; color:var(--text-main);">{{ $requisitions->lastItem() }}</span>
        of
        <span style="font-weight:900; color:var(--text-main);">{{ $requisitions->total() }}</span>
        requisitions
    </div>
    <div style="display:flex; align-items:center; gap:.35rem;">
        {{-- Previous --}}
        @if($requisitions->onFirstPage())
        <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:10px;background:var(--bg-main);border:1.5px solid var(--border-color);color:var(--text-muted);opacity:.45;cursor:not-allowed;">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </span>
        @else
        <a href="{{ $requisitions->previousPageUrl() }}" class="ajax-req-page-btn" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:10px;background:var(--bg-card);border:1.5px solid var(--border-color);color:var(--text-main);text-decoration:none;transition:.15s;" onmouseover="this.style.background='var(--primary)';this.style.color='white';this.style.borderColor='var(--primary)';" onmouseout="this.style.background='var(--bg-card)';this.style.color='var(--text-main)';this.style.borderColor='var(--border-color)';">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        @endif

        {{-- Page Numbers --}}
        @foreach($requisitions->getUrlRange(max(1, $requisitions->currentPage()-2), min($requisitions->lastPage(), $requisitions->currentPage()+2)) as $page => $url)
            @if($page == $requisitions->currentPage())
            <span style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 10px;border-radius:10px;background:var(--primary);color:white;font-weight:900;font-size:.82rem;border:1.5px solid var(--primary);box-shadow:0 4px 12px rgba(5,150,105,.3);">{{ $page }}</span>
            @else
            <a href="{{ $url }}" class="ajax-req-page-btn" style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 10px;border-radius:10px;background:var(--bg-card);color:var(--text-main);font-weight:700;font-size:.82rem;border:1.5px solid var(--border-color);text-decoration:none;transition:.15s;" onmouseover="this.style.background='rgba(5,150,105,.08)';this.style.borderColor='rgba(5,150,105,.3)';this.style.color='var(--primary)';" onmouseout="this.style.background='var(--bg-card)';this.style.borderColor='var(--border-color)';this.style.color='var(--text-main)';">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Next --}}
        @if($requisitions->hasMorePages())
        <a href="{{ $requisitions->nextPageUrl() }}" class="ajax-req-page-btn" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:10px;background:var(--bg-card);border:1.5px solid var(--border-color);color:var(--text-main);text-decoration:none;transition:.15s;" onmouseover="this.style.background='var(--primary)';this.style.color='white';this.style.borderColor='var(--primary)';" onmouseout="this.style.background='var(--bg-card)';this.style.color='var(--text-main)';this.style.borderColor='var(--border-color)';">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </a>
        @else
        <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:10px;background:var(--bg-main);border:1.5px solid var(--border-color);color:var(--text-muted);opacity:.45;cursor:not-allowed;">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </span>
        @endif
    </div>
</div>
@endif
