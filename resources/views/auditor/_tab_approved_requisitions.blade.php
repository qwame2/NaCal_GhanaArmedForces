@forelse($approvedRequisitions as $entry)
    @php
        $type = $entry['type'];
        $item = $entry['item'];
    @endphp

    {{-- Inventory SRA --}}
    @if($type === 'inventory_sra')
        @php
            $batch = $item;
            $cleanSupplier = trim(preg_replace('/\[.*?\]/', '', ($batch->acquisition_type === 'Donor' ? ($batch->donor_name ?: $batch->supplier_name) : $batch->supplier_name) ?? 'N/A'));
        @endphp
        <tr class="log-row">
            <td style="font-weight: 900; font-family: monospace; color: var(--audit-primary);">
                SRA-{{ str_pad($batch->id, 6, '0', STR_PAD_LEFT) }}
            </td>
            <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
                {{ \Carbon\Carbon::parse($batch->entry_date)->format('d/m/Y') }}
            </td>
            <td style="font-weight: 800; color: var(--text-main);">Inventory SRA</td>
            <td style="font-weight: 700; color: var(--text-muted);">{{ $cleanSupplier }}</td>
            <td>
                <span class="badge-event">{{ $ledgeMap[$batch->ledge_category] ?? $batch->ledge_category }}</span>
            </td>
            <td>
                <span class="log-badge" style="background: rgba(5, 150, 105, 0.1); color: #059669; border: 1px solid rgba(5, 150, 105, 0.3); font-size: 0.65rem;">
                    Approved ({{ $batch->acquisition_type }})
                </span>
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <a href="{{ route('receiveditems.sra', $batch->id) }}"
                   target="_blank"
                   class="btn-view-receipt"
                   style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; background: rgba(5, 150, 105, 0.08); color: var(--audit-primary); font-size: 0.72rem; font-weight: 800; text-decoration: none; border: 1px solid transparent; transition: all 0.2s;"
                   onmouseover="this.style.background='var(--audit-primary)'; this.style.color='white';"
                   onmouseout="this.style.background='rgba(5, 150, 105, 0.08)'; this.style.color='var(--audit-primary)';"
                   title="Print SRA Receipt">
                    <i data-lucide="printer" style="width: 13px; height: 13px;"></i>
                    <span>Print SRA</span>
                </a>
            </td>
        </tr>

    {{-- Service SRA --}}
    @elseif($type === 'service_sra')
        @php $sra = $item; @endphp
        <tr class="log-row">
            <td style="font-weight: 900; font-family: monospace; color: #059669;">
                {{ $sra->sra_number }}
            </td>
            <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
                {{ \Carbon\Carbon::parse($sra->created_at)->format('d/m/Y') }}
            </td>
            <td style="font-weight: 800; color: var(--text-main);">Service SRA</td>
            <td style="font-weight: 700; color: var(--text-muted);">{{ $sra->supplier_name ?? '—' }}</td>
            <td style="max-width: 250px; line-height: 1.4; color: var(--text-main); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $sra->details }}">
                {{ $sra->details }}
            </td>
            <td>
                <span class="log-badge" style="background: rgba(5, 150, 105, 0.1); color: #059669; border: 1px solid rgba(5, 150, 105, 0.3); font-size: 0.65rem;">
                    Approved
                </span>
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <a href="{{ route('service-sra.receipt', $sra->id) }}"
                   target="_blank"
                   class="btn-view-receipt"
                   style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; background: rgba(5, 150, 105, 0.08); color: var(--audit-primary); font-size: 0.72rem; font-weight: 800; text-decoration: none; border: 1px solid transparent; transition: all 0.2s;"
                   onmouseover="this.style.background='var(--audit-primary)'; this.style.color='white';"
                   onmouseout="this.style.background='rgba(5, 150, 105, 0.08)'; this.style.color='var(--audit-primary)';"
                   title="Print Service SRA Receipt">
                    <i data-lucide="printer" style="width: 13px; height: 13px;"></i>
                    <span>Print SRA</span>
                </a>
            </td>
        </tr>

    {{-- Department Store Requisition --}}
    @elseif($type === 'dept_req')
        @php $req = $item; @endphp
        <tr class="log-row">
            <td style="font-weight: 900; font-family: monospace; color: #6366f1;">
                {{ $req->unique_id ?: ('REQ-'.str_pad($req->id,5,'0',STR_PAD_LEFT)) }}
            </td>
            <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
                {{ $req->created_at->format('d/m/Y H:i') }}
            </td>
            <td style="font-weight: 800; color: var(--text-main);">Store Requisition</td>
            <td style="font-weight: 700; color: var(--text-muted);">{{ $req->requester_name }}</td>
            <td style="max-width: 250px; line-height: 1.4; color: var(--text-main); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $req->purpose }}">
                {{ $req->purpose }}
            </td>
            <td>
                @php
                    $sBg = match($req->status) {
                        'approved'           => 'rgba(5, 150, 105, 0.1)',
                        'partially_approved' => 'rgba(245, 158, 11, 0.1)',
                        'declined'           => 'rgba(239, 68, 68, 0.1)',
                        default              => 'rgba(107, 114, 128, 0.1)',
                    };
                    $sColor = match($req->status) {
                        'approved'           => '#059669',
                        'partially_approved' => '#f59e0b',
                        'declined'           => '#ef4444',
                        default              => '#6b7280',
                    };
                    $sLabel = match($req->status) {
                        'approved'           => 'Approved',
                        'partially_approved' => 'Partially Approved',
                        'declined'           => 'Declined',
                        default              => ucfirst(str_replace('_', ' ', $req->status)),
                    };
                @endphp
                <span class="log-badge" style="background: {{ $sBg }}; color: {{ $sColor }}; border: 1px solid {{ $sColor }}30; font-size: 0.65rem;">
                    {{ $sLabel }}
                </span>
            </td>
            <td style="text-align: center; vertical-align: middle;">
                @if($req->collected_at)
                    <a href="{{ route('requisitions.receipt.print', $req->id) }}"
                       target="_blank"
                       class="btn-view-receipt"
                       style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; background: rgba(5, 150, 105, 0.08); color: var(--audit-primary); font-size: 0.72rem; font-weight: 800; text-decoration: none; border: 1px solid transparent; transition: all 0.2s;"
                       onmouseover="this.style.background='var(--audit-primary)'; this.style.color='white';"
                       onmouseout="this.style.background='rgba(5, 150, 105, 0.08)'; this.style.color='var(--audit-primary)';"
                       title="Print Requisition Receipt">
                        <i data-lucide="receipt" style="width: 13px; height: 13px;"></i>
                        <span>Receipt</span>
                    </a>
                @else
                    <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 800;">Awaiting Collection</span>
                @endif
            </td>
        </tr>
    @endif
@empty
    <tr>
        <td colspan="7" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
            <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No approved requests logged.</p>
        </td>
    </tr>
@endforelse
