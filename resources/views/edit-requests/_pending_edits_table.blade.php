<div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.75rem;">
    <i data-lucide="edit-3" style="color: var(--primary); width: 20px;"></i>
    <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-main);">Pending Edit Approvals ({{ $pendingEdits->total() }})</h3>
</div>

@if($pendingEdits->isEmpty())
    <div style="padding: 4rem 2rem; text-align: center; color: var(--text-muted);">
        <i data-lucide="check-circle" style="width: 48px; height: 48px; opacity: 0.3; display: block; margin: 0 auto 1rem;"></i>
        <p style="margin: 0; font-weight: 700; font-size: 1rem;">No edit requests awaiting authorization.</p>
    </div>
@else
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: var(--bg-main); border-bottom: 1.5px solid var(--border-color);">
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Request ID</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Date Submitted</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Requested By</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Original Batch ID</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Explanation / Reason</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingEdits as $req)
                    @php
                        $payload = json_decode($req->payload, true) ?? [];
                    @endphp
                    <tr class="sra-table-row" style="cursor: pointer;" onclick="window.location.href='{{ route('sra.preview', $req->id) }}'">
                        <td style="padding: 1.25rem 1.5rem; font-weight: 800; color: #059669;">
                            REQ-{{ str_pad($req->id, 5, '0', STR_PAD_LEFT) }}
                            @if($req->status === 'resubmitted')
                                <span style="font-size: 0.65rem; background: rgba(5, 150, 105, 0.12); color: #059669; border: 1px solid rgba(5, 150, 105, 0.3); padding: 2px 7px; border-radius: 6px; font-weight: 850; margin-left: 4px; text-transform: uppercase;">CORRECTED</span>
                            @endif
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
                            {{ $req->created_at->format('d M Y, h:i A') }}
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--text-main);">
                            {{ $req->user->name ?? 'Unknown' }}
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--text-main);">
                            Batch #{{ $req->item_id }}
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 600; color: var(--text-main);">
                            {{ $req->reason }}
                        </td>
                        <td style="padding: 1.25rem 1.5rem; text-align: center;">
                            <button onclick="event.stopPropagation(); window.location.href='{{ route('sra.preview', $req->id) }}'" style="background: rgba(5,150,105,0.08); color: #059669; border: 1px solid rgba(5,150,105,0.2); border-radius: 10px; padding: 0.5rem 1.25rem; font-size: 0.78rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <i data-lucide="eye" style="width: 14px;"></i> Review Edit
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($pendingEdits->hasPages())
        <div class="custom-pagination" style="padding: 1.5rem 2rem; border-top: 1px solid var(--border-color); background: var(--bg-main);">
            {{ $pendingEdits->appends(['pending_page' => request('pending_page'), 'history_page' => request('history_page')])->links('pagination::bootstrap-4') }}
        </div>
    @endif
@endif
