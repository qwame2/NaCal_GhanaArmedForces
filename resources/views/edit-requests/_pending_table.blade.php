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
                        $supplier = !empty(trim($payload['supplier_name'] ?? '')) 
                            ? $payload['supplier_name'] 
                            : (!empty(trim($payload['donor_name'] ?? '')) ? $payload['donor_name'] : 'N/A');
                        $itemsCount = count($payload['items'] ?? []);
                    @endphp
                    <tr class="sra-table-row" style="cursor: pointer;" onclick="window.location.href='{{ route('sra.preview', $req->id) }}'">
                        <td style="padding: 1.25rem 1.5rem; font-weight: 800; color: #881337;">REQ-{{ str_pad($req->id, 5, '0', STR_PAD_LEFT) }}</td>
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
                                $firstItem = ($payload['items'] ?? [])[0] ?? [];
                                $rawLocPending = $firstItem['store_location'] ?? ($firstItem['location'] ?? 'Store A');
                                $stLocPending = str_replace('Stores', 'Store', $rawLocPending);
                                $isPendingB = str_contains($stLocPending, 'B');
                            @endphp
                            <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                <span style="background: rgba(136, 19, 55, 0.08); color: var(--primary); padding: 4px 10px; border-radius: 6px; text-transform: uppercase;">
                                    Cat {{ $payload['ledge_category'] ?? '—' }}
                                </span>
                                <span style="font-size: 0.7rem; font-weight: 800; color: {{ $isPendingB ? '#3b82f6' : '#881337' }}; background: {{ $isPendingB ? 'rgba(59, 130, 246, 0.1)' : 'rgba(136, 19, 55, 0.1)' }}; padding: 2px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;">
                                    <i data-lucide="map-pin" style="width: 10px; height: 10px;"></i>
                                    {{ $stLocPending }}
                                </span>
                            </div>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--text-main);">
                            @php
                                $itemsList = $payload['items'] ?? [];
                            @endphp
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
                            <button onclick="event.stopPropagation(); window.location.href='{{ route('sra.preview', $req->id) }}'" style="background: rgba(136,19,55,0.08); color: #881337; border: 1px solid rgba(136,19,55,0.2); border-radius: 10px; padding: 0.5rem 1.25rem; font-size: 0.78rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <i data-lucide="eye" style="width: 14px;"></i> Review Entry
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($pending->hasPages())
        <div class="custom-pagination" style="padding: 1.5rem 2rem; border-top: 1px solid var(--border-color); background: var(--bg-main);">
            {{ $pending->appends(['history_page' => request('history_page')])->links('pagination::bootstrap-4') }}
        </div>
    @endif
@endif
