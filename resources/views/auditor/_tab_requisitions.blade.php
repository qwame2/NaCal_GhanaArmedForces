@forelse($requisitions as $req)
    <tr class="log-row">
        <td style="font-weight: 900; font-family: monospace; color: var(--audit-primary);">
            {{ $req->unique_id ?: ('REQ-'.str_pad($req->id,5,'0',STR_PAD_LEFT)) }}
        </td>
        <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
            {{ $req->created_at->format('d/m/Y H:i') }}
        </td>
        <td style="font-weight: 800; color: var(--text-main);">{{ $req->requester_name }}</td>
        <td style="font-weight: 700; color: var(--text-muted);">{{ $req->department }}</td>
        <td style="max-width: 250px; line-height: 1.4; color: var(--text-main); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $req->purpose }}">
            {{ $req->purpose }}
        </td>
        <td>
            @php $s = $req->status_badge; @endphp
            <span class="log-badge" style="background: {{ $s['bg'] }}; color: {{ $s['color'] }}; border: 1px solid {{ $s['color'] }}30; font-size: 0.65rem;">
                {{ $s['label'] }}
            </span>
            @if($req->status === 'pending')
                <div style="font-size:0.7rem;color:var(--text-muted);margin-top:4px;font-weight:600;">
                    Next: <span style="color:var(--text-main);font-weight:800;">{{ $req->approver_name }}</span>
                </div>
            @endif
        </td>
        <td style="text-align: center; vertical-align: middle;">
            @if($req->collected_at)
                <a href="{{ route('requisitions.receipt.print', $req->id) }}"
                   target="_blank"
                   class="btn-view-receipt"
                   style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; background: rgba(22, 163, 74, 0.08); color: var(--audit-primary); font-size: 0.72rem; font-weight: 800; text-decoration: none; border: 1px solid transparent; transition: all 0.2s;"
                   onmouseover="this.style.background='var(--audit-primary)'; this.style.color='white';"
                   onmouseout="this.style.background='rgba(22, 163, 74, 0.08)'; this.style.color='var(--audit-primary)';"
                   title="Print Requisition Receipt">
                    <i data-lucide="receipt" style="width: 13px; height: 13px;"></i>
                    <span>Receipt</span>
                </a>
            @else
                <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 800;">Awaiting Collection</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
            <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No store requisitions logged.</p>
        </td>
    </tr>
@endforelse
