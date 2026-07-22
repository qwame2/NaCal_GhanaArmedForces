@forelse($receivedItems as $item)
    <tr class="log-row">
        <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
            {{ $item->entry_date ? \Carbon\Carbon::parse($item->entry_date)->format('d/m/Y') : '-' }}
        </td>
        <td style="font-weight: 900; font-family: monospace; color: var(--audit-primary);">
            #{{ $item->batch_id }}
        </td>
        <td style="font-weight: 800; color: var(--text-main);">
            {{ $item->description }}
        </td>
        <td>
            <span class="badge-event">{{ $ledgeMap[$item->ledge_category] ?? $item->ledge_category }}</span>
        </td>
        <td style="font-weight: 800; text-align: center;">
            {{ number_format($item->qty) }} <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $item->unit }}</span>
        </td>
        <td style="font-weight: 800; text-align: center; color: var(--audit-primary);">
            {{ number_format($item->stock_balance) }} <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $item->unit }}</span>
        </td>
        <td style="font-weight: 800; text-align: center; color: {{ $item->variance > 0 ? '#ef4444' : 'var(--text-main)' }};">
            {{ number_format($item->variance) }}
        </td>
        <td style="font-weight: 700;">{{ $item->acquisition_type }}</td>
        <td style="font-weight: 700; color: var(--text-muted);">
            <div style="display: flex; align-items: center; justify-content: flex-start; gap: 8px;">
                <span>{{ $item->supplier_name ?: ($item->donor_name ?: 'System') }}</span>
                @if($item->supplier_name || $item->donor_name)
                    <button type="button" class="btn-toggle-supplier-details"
                            data-id="{{ $item->id }}"
                            data-name="{{ $item->supplier_name ?: $item->donor_name }}"
                            data-acquisition="{{ $item->acquisition_type }}"
                            data-delivery-person="{{ $item->delivery_person ?: '-' }}"
                            data-delivery-phone="{{ $item->delivery_phone ?: '-' }}"
                            style="border: none; background: rgba(136, 19, 55, 0.08); cursor: pointer; color: var(--audit-primary); width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;"
                            onmouseover="this.style.background='rgba(136, 19, 55, 0.18)';"
                            onmouseout="this.style.background='rgba(136, 19, 55, 0.08)';"
                            onclick="toggleSupplierPopover(this, event)">
                        <i data-lucide="chevron-down" style="width: 14px; height: 14px; transition: transform 0.2s;"></i>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
            <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No received items logged.</p>
        </td>
    </tr>
@endforelse
