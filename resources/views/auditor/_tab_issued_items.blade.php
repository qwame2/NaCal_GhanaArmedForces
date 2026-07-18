@forelse($issuedItems as $item)
    <tr class="log-row">
        <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
            {{ \Carbon\Carbon::parse($item->issuance_date)->format('d/m/Y') }}
        </td>
        <td style="font-weight: 800; color: var(--text-main);">{{ $item->description }}</td>
        <td>
            <span class="badge-event">{{ $ledgeMap[$item->ledge_category] ?? $item->ledge_category }}</span>
        </td>
        <td style="text-align: center; vertical-align: middle;">
            @if($item->issuance_type === 'Temporary' && $item->total_returned > 0)
                @if($item->quantity == 0)
                    <div style="font-weight: 800; color: #10b981;">
                        {{ number_format($item->total_returned) }} <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $item->unit }}</span>
                    </div>
                    <div style="margin-top: 4px; display: flex; justify-content: center;">
                        <span class="log-badge success" style="padding: 2px 6px; font-size: 0.6rem; font-weight: 900;">Returned</span>
                    </div>
                @else
                    <div style="font-weight: 800; color: #15803d;">
                        {{ number_format($item->quantity + $item->total_returned) }} <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $item->unit }}</span>
                    </div>
                    <div style="margin-top: 4px; display: flex; flex-direction: column; align-items: center; gap: 2px;">
                        <span class="log-badge warning" style="padding: 2px 6px; font-size: 0.6rem; font-weight: 900; line-height: 1;">Partial Return</span>
                        <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800;">({{ number_format($item->quantity) }} outstanding)</span>
                    </div>
                @endif
            @else
                <div style="font-weight: 800; color: #15803d; text-align: center;">
                    {{ number_format($item->quantity) }} <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $item->unit }}</span>
                </div>
            @endif
        </td>
        <td style="font-weight: 800;">{{ $item->beneficiary }}</td>
        <td>
            <span class="log-badge {{ $item->issuance_type === 'Temporary' ? 'warning' : 'info' }}">
                {{ $item->issuance_type }}
            </span>
        </td>
        <td style="font-weight: 700; color: var(--text-muted); font-size: 0.8rem; line-height: 1.4;">
            @if($item->origin_approved_by || $item->stores_approved_by || $item->dg_approved_by || $item->final_approved_by || $item->store_officer_name)
                @if($item->origin_approved_by)
                    <div>{{ $item->origin_approved_by }} <span style="font-size: 0.68rem; color: var(--audit-primary); font-weight: 800;">(Dept Head)</span></div>
                @endif
                @if($item->stores_approved_by)
                    <div style="margin-top: 2px;">{{ $item->stores_approved_by }} <span style="font-size: 0.68rem; color: #10b981; font-weight: 800;">(Head of Admin(Authorizer))</span></div>
                @endif
                @if($item->dg_approved_by)
                    <div style="margin-top: 2px;">{{ $item->dg_approved_by }} <span style="font-size: 0.68rem; color: #4ade80; font-weight: 800;">(Director General)</span></div>
                @endif
                @if($item->final_approved_by)
                    <div style="margin-top: 2px;">{{ $item->final_approved_by }} <span style="font-size: 0.68rem; color: #10b981; font-weight: 800;">(Head of Stores)</span></div>
                @endif
                @if($item->store_officer_name)
                    <div style="margin-top: 2px;">{{ $item->store_officer_name }} <span style="font-size: 0.68rem; color: #16a34a; font-weight: 800;">(Store Officer)</span></div>
                @endif
            @else
                {{ $item->authority ?: 'N/A' }}
            @endif
        </td>
        <td style="text-align: center; vertical-align: middle;">
            @if($item->requisition_id)
                <a href="{{ route('requisitions.receipt.print', $item->requisition_id) }}"
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
                <span style="font-size: 0.72rem; color: var(--text-muted); font-style: italic;">N/A</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
            <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No issued items logged.</p>
        </td>
    </tr>
@endforelse
