<div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.75rem;">
    <i data-lucide="clock" style="color: #f59e0b; width: 20px;"></i>
    <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-main);">Awaiting Your Authorization ({{ $pending->total() }})</h3>
</div>

@if($pending->isEmpty())
    <div style="padding: 4rem 2rem; text-align: center; color: var(--text-muted);">
        <i data-lucide="check-circle" style="width: 48px; height: 48px; opacity: 0.3; display: block; margin: 0 auto 1rem;"></i>
        <p style="margin: 0; font-weight: 700; font-size: 1rem;">All caught up! No item entries awaiting authorization.</p>
    </div>
@else
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: var(--bg-main); border-bottom: 1.5px solid var(--border-color);">
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Request ID</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Date Submitted</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Entered By</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Supplier</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Category</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Item Description</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pending as $req)
                    @php
                        $payload = json_decode($req->payload, true) ?? [];
                        $isRemainder = ($req->request_type === 'remainder_submission');
                        
                        $supplier = !empty(trim($payload['supplier_name'] ?? '')) 
                            ? $payload['supplier_name'] 
                            : (!empty(trim($payload['donor_name'] ?? '')) ? $payload['donor_name'] : 'N/A');
                        
                        $itemsList = $payload['items'] ?? [];

                        if ($isRemainder) {
                            $firstUpdate = ($payload['updates'] ?? [])[0] ?? null;
                            $firstInvItem = $firstUpdate ? \App\Models\InventoryItem::find($firstUpdate['item_id']) : null;
                            $batchObj = $firstInvItem ? \App\Models\InventoryBatch::find($firstInvItem->batch_id) : null;
                            
                            if ($batchObj) {
                                $supplier = !empty(trim($batchObj->supplier_name)) ? $batchObj->supplier_name : (!empty(trim($batchObj->donor_name)) ? $batchObj->donor_name : 'N/A');
                                $payload['ledge_category'] = $batchObj->ledge_category;
                            }

                            if (empty($itemsList) && !empty($payload['updates'])) {
                                $itemsList = collect($payload['updates'])->map(function($u) {
                                    $invItem = \App\Models\InventoryItem::find($u['item_id']);
                                    return [
                                        'description' => ($invItem ? $invItem->description : 'Item') . ' (+' . floatval($u['incoming_qty']) . ')',
                                        'store_location' => $invItem->store_location ?? 'Store A'
                                    ];
                                })->toArray();
                            }
                        }
                    @endphp
                    <tr class="sra-table-row" style="cursor: pointer;" onclick="window.location.href='{{ route('sra.preview', $req->id) }}'">
                        <td style="padding: 1.25rem 1.5rem; font-weight: 800; color: #059669;">
                            REQ-{{ str_pad($req->id, 5, '0', STR_PAD_LEFT) }}
                            @if($isRemainder)
                                <span style="font-size: 0.65rem; background: rgba(245, 158, 11, 0.12); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3); padding: 2px 7px; border-radius: 6px; font-weight: 850; margin-left: 4px; text-transform: uppercase;">REMAINDER</span>
                            @elseif($req->status === 'resubmitted')
                                <span style="font-size: 0.65rem; background: rgba(5, 150, 105, 0.12); color: #059669; border: 1px solid rgba(5, 150, 105, 0.3); padding: 2px 7px; border-radius: 6px; font-weight: 850; margin-left: 4px; text-transform: uppercase;">CORRECTED</span>
                            @endif
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
                            {{ $req->created_at->format('d M Y, h:i A') }}
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--text-main);">
                            {{ $req->user->name ?? 'Unknown' }}
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 600; color: var(--text-main);">
                            {{ $supplier }}
                            @if(($payload['acquisition_type'] ?? '') === 'Donor')
                                <span style="font-size: 0.65rem; background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-weight: 800; margin-left: 4px; text-transform: uppercase;">Donor</span>
                            @endif
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-size: 0.82rem; font-weight: 800;">
                            @php
                                $firstItem = ($itemsList)[0] ?? [];
                                $rawLocPending = $firstItem['store_location'] ?? ($firstItem['location'] ?? 'Store A');
                                $stLocPending = str_replace('Stores', 'Store', $rawLocPending);
                                $isPendingB = str_contains($stLocPending, 'B');
                            @endphp
                            <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                <span style="background: rgba(5, 150, 105, 0.08); color: var(--primary); padding: 4px 10px; border-radius: 6px; text-transform: uppercase;">
                                    Cat {{ $payload['ledge_category'] ?? '—' }}
                                </span>
                                <span style="font-size: 0.7rem; font-weight: 800; color: {{ $isPendingB ? '#3b82f6' : '#059669' }}; background: {{ $isPendingB ? 'rgba(59, 130, 246, 0.1)' : 'rgba(5, 150, 105, 0.1)' }}; padding: 2px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;">
                                    <i data-lucide="map-pin" style="width: 10px; height: 10px;"></i>
                                    {{ $stLocPending }}
                                </span>
                            </div>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--text-main);">
                            @if(count($itemsList) === 1)
                                {{ $itemsList[0]['description'] ?? 'N/A' }}
                            @else
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    @foreach($itemsList as $itm)
                                        <span style="display: block; font-weight: 600; font-size: 0.85rem;">• {{ $itm['description'] ?? 'N/A' }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td style="padding: 1.25rem 1.5rem; text-align: center;">
                            <button onclick="event.stopPropagation(); window.location.href='{{ route('sra.preview', $req->id) }}'" style="background: rgba(5,150,105,0.08); color: #059669; border: 1px solid rgba(5,150,105,0.2); border-radius: 10px; padding: 0.5rem 1.25rem; font-size: 0.78rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <i data-lucide="eye" style="width: 14px;"></i> Review Entry
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- Advanced Pagination Footer -->
    <div class="pagination-footer">
        <div class="pagination-container">
            <div class="pagination-info">
                <span class="pagination-info-badge">
                    <i data-lucide="layers" style="width: 14px; height: 14px; display: inline; margin-right: 6px;"></i>
                    {{ $pending->total() }} Total {{ \Illuminate\Support\Str::plural('Record', $pending->total()) }}
                </span>
                <span class="pagination-stats">
                    Showing {{ $pending->firstItem() ?? 0 }} - {{ $pending->lastItem() ?? 0 }} of {{ $pending->total() }} entries
                </span>
            </div>

            <div class="pagination-nav">
                @if ($pending->onFirstPage())
                    <span class="pagination-arrow disabled" title="Previous Page">
                        <i data-lucide="chevron-left" style="width: 20px;"></i>
                    </span>
                @else
                    <a href="{{ $pending->previousPageUrl() }}" class="pagination-arrow" title="Previous Page">
                        <i data-lucide="chevron-left" style="width: 20px;"></i>
                    </a>
                @endif

                <div class="pagination-numbers">
                    @php
                        $currentPage = $pending->currentPage();
                        $lastPage = $pending->lastPage();
                        $start = max($currentPage - 2, 1);
                        $end = min($currentPage + 2, $lastPage);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $pending->url(1) }}" class="pagination-number">1</a>
                        @if($start > 2)
                            <span class="pagination-dots">...</span>
                        @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <a href="{{ $pending->url($i) }}"
                           class="pagination-number {{ $currentPage == $i ? 'active' : '' }}">
                            {{ $i }}
                        </a>
                    @endfor

                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="pagination-dots">...</span>
                        @endif
                        <a href="{{ $pending->url($lastPage) }}" class="pagination-number">{{ $lastPage }}</a>
                    @endif
                </div>

                @if ($pending->hasMorePages())
                    <a href="{{ $pending->nextPageUrl() }}" class="pagination-arrow" title="Next Page">
                        <i data-lucide="chevron-right" style="width: 20px;"></i>
                    </a>
                @else
                    <span class="pagination-arrow disabled" title="Next Page">
                        <i data-lucide="chevron-right" style="width: 20px;"></i>
                    </span>
                @endif
            </div>

            <div class="pagination-per-page modern-pagination-select">
                <div class="select-icon-left">
                    <i data-lucide="sliders-horizontal"></i>
                </div>
                <select class="per-page-select" onchange="window.location.href=this.value" title="Entries per page">
                    @foreach([5, 10, 25, 50, 100] as $perPageOption)
                        <option value="{{ request()->fullUrlWithQuery(['pending_per_page' => $perPageOption, 'pending_page' => 1, 'tab' => 'pending']) }}"
                            {{ (request('pending_per_page', request('per_page', 10)) == $perPageOption) ? 'selected' : '' }}>
                            Show {{ $perPageOption }} entries
                        </option>
                    @endforeach
                </select>
                <div class="select-icon-right">
                    <i data-lucide="chevron-down"></i>
                </div>
            </div>
        </div>
    </div>
@endif
