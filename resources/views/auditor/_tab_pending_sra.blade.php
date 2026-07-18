{{-- Pending Inventory SRAs --}}
@forelse($pendingSras as $batch)
    @php
        $cleanSupplier = trim(preg_replace('/\[.*?\]/', '', ($batch->acquisition_type === 'Donor' ? ($batch->donor_name ?: $batch->supplier_name) : $batch->supplier_name) ?? 'N/A'));
    @endphp
    <tr class="log-row">
        <td style="font-weight: 900; font-family: monospace; color: var(--audit-primary);">
            SRA-{{ str_pad($batch->id, 6, '0', STR_PAD_LEFT) }}
        </td>
        <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
            {{ \Carbon\Carbon::parse($batch->entry_date)->format('d/m/Y') }}
        </td>
        <td style="font-weight: 800; color: var(--text-main);">
            {{ $ledgeMap[$batch->ledge_category] ?? $batch->ledge_category }}
        </td>
        <td style="font-weight: 700; color: var(--text-muted);">{{ $cleanSupplier }}</td>
        <td>
            <span class="log-badge info" style="font-size: 0.65rem;">{{ $batch->acquisition_type }}</span>
        </td>
        <td style="font-weight: 800; color: var(--text-main);">
            {{ $batch->storesApprover->name ?? 'N/A' }}
        </td>
        <td style="text-align: center; vertical-align: middle;">
            <a href="{{ route('receiveditems.sra', $batch->id) }}"
               target="_blank"
               class="btn-view-receipt"
               style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; background: rgba(22, 163, 74, 0.08); color: var(--audit-primary); font-size: 0.72rem; font-weight: 800; text-decoration: none; border: 1px solid transparent; transition: all 0.2s;"
               onmouseover="this.style.background='var(--audit-primary)'; this.style.color='white';"
               onmouseout="this.style.background='rgba(22, 163, 74, 0.08)'; this.style.color='var(--audit-primary)';"
               title="Review SRA Receipt">
                <i data-lucide="file-signature" style="width: 13px; height: 13px;"></i>
                <span>Review &amp; Approve</span>
            </a>
        </td>
    </tr>
@empty
@endforelse

{{-- Pending Service SRAs --}}
@foreach($pendingServiceSras as $sra)
    <tr class="log-row">
        <td style="font-weight: 900; font-family: monospace; color: #8b5cf6;">
            {{ $sra->sra_number }}
        </td>
        <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
            {{ \Carbon\Carbon::parse($sra->created_at)->format('d/m/Y') }}
        </td>
        <td style="font-weight: 800; color: var(--text-main);">Service SRA</td>
        <td style="font-weight: 700; color: var(--text-muted);">{{ $sra->supplier_name ?? '—' }}</td>
        <td>
            <span class="log-badge info" style="font-size: 0.65rem;">Service</span>
        </td>
        <td style="font-weight: 800; color: var(--text-main);">{{ $sra->submitter->name ?? 'N/A' }}</td>
        <td style="text-align: center; vertical-align: middle;">
            <button type="button"
                    onclick="openServiceSraAuditModal({{ $sra->id }})"
                    style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; background: rgba(139,92,246,0.08); color: #8b5cf6; font-size: 0.72rem; font-weight: 800; border: 1px solid transparent; cursor: pointer; transition: all 0.2s;"
                    onmouseover="this.style.background='#8b5cf6'; this.style.color='white';"
                    onmouseout="this.style.background='rgba(139,92,246,0.08)'; this.style.color='#8b5cf6';"
                    title="Review Service SRA">
                <i data-lucide="file-signature" style="width: 13px; height: 13px;"></i>
                <span>Review &amp; Approve</span>
            </button>
        </td>
    </tr>
@endforeach

@if($pendingSras->count() + $pendingServiceSras->count() === 0)
    <tr>
        <td colspan="7" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
            <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No SRA receipts pending verification.</p>
        </td>
    </tr>
@endif
