@forelse($returnedItems as $item)
    <tr class="log-row">
        <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
            {{ \Carbon\Carbon::parse($item->return_date)->format('d/m/Y') }}
        </td>
        <td style="font-weight: 800; color: var(--text-main);">{{ $item->description }}</td>
        <td>
            <span class="badge-event">{{ $ledgeMap[$item->ledge_category] ?? $item->ledge_category }}</span>
        </td>
        <td style="font-weight: 800; text-align: center; color: #881337;">
            {{ number_format($item->returned_qty) }}
        </td>
        <td style="font-weight: 800;">{{ $item->beneficiary }}</td>
        <td style="color: var(--text-muted); font-weight: 600; line-height: 1.4;">
            {{ $item->remarks ?: 'Returned and verified clean.' }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
            <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No returned assets logs.</p>
        </td>
    </tr>
@endforelse
