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
               style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; background: rgba(5, 150, 105, 0.08); color: var(--audit-primary); font-size: 0.72rem; font-weight: 800; text-decoration: none; border: 1px solid transparent; transition: all 0.2s;"
               onmouseover="this.style.background='var(--audit-primary)'; this.style.color='white';"
               onmouseout="this.style.background='rgba(5, 150, 105, 0.08)'; this.style.color='var(--audit-primary)';"
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
        <td style="font-weight: 900; font-family: monospace; color: #059669;">
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
            <a href="{{ route('auditor.service-sra.review', $sra->id) }}"
               target="_blank"
               style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; background: rgba(139,92,246,0.08); color: #059669; font-size: 0.72rem; font-weight: 800; border: 1px solid transparent; text-decoration: none; cursor: pointer; transition: all 0.2s;"
               onmouseover="this.style.background='#059669'; this.style.color='white';"
               onmouseout="this.style.background='rgba(139,92,246,0.08)'; this.style.color='#059669';"
               title="Review Service SRA">
                <i data-lucide="file-signature" style="width: 13px; height: 13px;"></i>
                <span>Review &amp; Approve</span>
            </a>
        </td>
    </tr>
@endforeach

{{-- Pending Department Store Requisitions (Audit Department) --}}
@if(isset($pendingDeptRequisitions))
    @foreach($pendingDeptRequisitions as $req)
        @php
            $reqPayload = [
                'id' => $req->id,
                'req_number' => 'REQ-' . str_pad($req->id, 6, '0', STR_PAD_LEFT),
                'requester_name' => $req->requester_name ?: ($req->requester->name ?? 'Staff'),
                'requester_username' => $req->requester->username ?? '',
                'requester_phone' => $req->requester->phone ?? '',
                'requester_service' => $req->requester->service_number ?? '',
                'rank_or_title' => $req->rank_or_title ?? ($req->requester->rank ?? ''),
                'department' => $req->department,
                'purpose' => $req->purpose,
                'priority' => ucfirst($req->priority ?? 'normal'),
                'usage_type' => ucfirst($req->usage_type ?? 'permanent'),
                'created_at' => \Carbon\Carbon::parse($req->created_at)->format('d/m/Y h:i A'),
                'items' => $req->items->map(function($i) {
                    return [
                        'description' => $i->description,
                        'category' => $i->category ?? 'N/A',
                        'unit' => $i->unit ?? 'units',
                        'quantity' => (float)$i->quantity_requested,
                        'remarks' => $i->remarks ?? '-'
                    ];
                })->toArray()
            ];
        @endphp
        <tr class="log-row">
            <td style="font-weight: 900; font-family: monospace; color: #6366f1;">
                REQ-{{ str_pad($req->id, 6, '0', STR_PAD_LEFT) }}
            </td>
            <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
                {{ \Carbon\Carbon::parse($req->created_at)->format('d/m/Y') }}
            </td>
            <td style="font-weight: 800; color: var(--text-main);">Store Requisition</td>
            <td style="font-weight: 700; color: var(--text-muted);">
                {{ $req->requester_name ?: ($req->requester->name ?? 'Staff') }}
            </td>
            <td>
                <span class="log-badge warning" style="font-size: 0.65rem; background: rgba(245, 158, 11, 0.1); color: #d97706;">
                    Pending HOD Review
                </span>
            </td>
            <td style="font-weight: 800; color: var(--text-main);">
                {{ $req->department }}
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <button onclick='reviewAuditDeptRequisition(@json($reqPayload))'
                        class="btn-view-receipt"
                        style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 14px; border-radius: 8px; background: rgba(5, 150, 105, 0.08); color: var(--audit-primary); font-size: 0.72rem; font-weight: 800; border: 1px solid transparent; text-decoration: none; cursor: pointer; transition: all 0.2s;"
                        onmouseover="this.style.background='var(--audit-primary)'; this.style.color='white';"
                        onmouseout="this.style.background='rgba(5, 150, 105, 0.08)'; this.style.color='var(--audit-primary)';"
                        title="Review Store Requisition Details">
                    <i data-lucide="file-signature" style="width: 13px; height: 13px;"></i>
                    <span>Review</span>
                </button>
            </td>
        </tr>
    @endforeach
@endif

@php
    $totalPendingCount = $pendingSras->count() + $pendingServiceSras->count() + (isset($pendingDeptRequisitions) ? $pendingDeptRequisitions->count() : 0);
@endphp

@if($totalPendingCount === 0)
    <tr>
        <td colspan="7" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
            <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No SRA receipts or requisitions pending verification.</p>
        </td>
    </tr>
@endif
