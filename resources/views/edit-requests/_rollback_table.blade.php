<div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <i data-lucide="rotate-ccw" style="color: #ef4444; width: 20px;"></i>
        <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-main);">Rollback List ({{ $rollbacks->total() }})</h3>
    </div>
</div>

@if($rollbacks->isEmpty())
    <div style="padding: 4rem 2rem; text-align: center; color: var(--text-muted);">
        <i data-lucide="check-circle" style="width: 48px; height: 48px; opacity: 0.3; display: block; margin: 0 auto 1rem; color: #10b981;"></i>
        <p style="margin: 0; font-weight: 700; font-size: 1rem;">No active rollbacks. All entry correction requests are cleared.</p>
    </div>
@else
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: var(--bg-main); border-bottom: 1.5px solid var(--border-color);">
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Request ID</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Date Flagged</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Submitted By</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Supplier / Source</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Flagged Notes / Details</th>
                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rollbacks as $req)
                    @php
                        $rollbackData = json_decode($req->rollback_fields ?? '{}', true) ?? [];
                        $flaggedFields = $rollbackData['flagged'] ?? [];
                        $generalNote = $rollbackData['note'] ?? '';
                        
                        $payload = json_decode($req->payload ?? '{}', true) ?? [];
                        $supplier = !empty(trim($payload['supplier_name'] ?? '')) 
                            ? $payload['supplier_name'] 
                            : (!empty(trim($payload['donor_name'] ?? '')) ? $payload['donor_name'] : 'N/A');
                        
                        $fieldLabels = [
                            'arrival_date' => 'Received Date',
                            'entry_date' => 'Entry Date',
                            'ledge_category' => 'Category Section',
                            'supplier_name' => 'Supplier Name',
                            'donor_name' => 'Donor Name',
                            'acquisition_type' => 'Acquisition Type',
                            'supplier_status' => 'Delivery Status',
                        ];
                    @endphp
                    <tr class="sra-table-row">
                        <td style="padding: 1.25rem 1.5rem; font-weight: 800; color: #ef4444;">
                            RB-{{ str_pad($req->id, 5, '0', STR_PAD_LEFT) }}
                            @if($req->status === 'resubmitted')
                                <span style="font-size: 0.65rem; background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 2px 7px; border-radius: 6px; font-weight: 850; margin-left: 4px; text-transform: uppercase;">RESUBMITTED</span>
                            @else
                                <span style="font-size: 0.65rem; background: rgba(239, 68, 68, 0.12); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); padding: 2px 7px; border-radius: 6px; font-weight: 850; margin-left: 4px; text-transform: uppercase;">ROLLBACK</span>
                            @endif
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
                            {{ $req->updated_at->format('d M Y, h:i A') }}
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--text-main);">
                            {{ $req->user->name ?? 'Unknown' }}
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 600; color: var(--text-main);">
                            {{ $supplier }}
                            @if(isset($payload['ledge_category']))
                                <span style="font-size: 0.72rem; color: var(--primary); font-weight: 800; background: var(--primary-glow); padding: 2px 8px; border-radius: 6px; display: inline-block; margin-top: 4px;">
                                    Cat {{ $payload['ledge_category'] }}
                                </span>
                            @endif
                        </td>
                        <td style="padding: 1.25rem 1.5rem;">
                            @if(!empty($flaggedFields))
                                <div style="display: flex; flex-direction: column; gap: 3px; margin-bottom: 4px;">
                                    @foreach($flaggedFields as $fKey => $fNote)
                                        <span style="font-size: 0.75rem; background: #fff1f2; color: #9f1239; border: 1px solid #fecaca; padding: 2px 8px; border-radius: 6px; font-weight: 700;">
                                            <strong>{{ $fieldLabels[$fKey] ?? ucwords(str_replace('_', ' ', $fKey)) }}:</strong> {{ $fNote }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            @if($generalNote)
                                <div style="font-size: 0.78rem; color: #78350f; background: #fff7ed; border: 1px solid #fed7aa; padding: 4px 8px; border-radius: 6px;">
                                    <strong>HOD Note:</strong> {{ $generalNote }}
                                </div>
                            @endif
                            @if(empty($flaggedFields) && !$generalNote)
                                <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">General correction requested.</span>
                            @endif
                        </td>
                        <td style="padding: 1.25rem 1.5rem; text-align: center; white-space: nowrap;">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <button onclick="window.location.href='{{ route('sra.preview', $req->id) }}'" style="background: rgba(5,150,105,0.08); color: #059669; border: 1px solid rgba(5,150,105,0.2); border-radius: 10px; padding: 0.5rem 1rem; font-size: 0.78rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;" title="View Details">
                                    <i data-lucide="eye" style="width: 14px; height: 14px;"></i> Details
                                </button>

                                <button type="button" onclick="cancelRollback({{ $req->id }}, '{{ addslashes($req->user->name ?? 'Personnel') }}')" style="background: rgba(239, 68, 68, 0.08); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); width: 34px; height: 34px; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#ef4444'; this.style.color='#ffffff';" onmouseout="this.style.background='rgba(239, 68, 68, 0.08)'; this.style.color='#ef4444';" title="Cancel / Delete Rollback Request">
                                    <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($rollbacks->hasPages())
        <div class="custom-pagination" style="padding: 1.5rem 2rem; border-top: 1px solid var(--border-color); background: var(--bg-main);">
            {{ $rollbacks->appends(['pending_page' => request('pending_page'), 'edits_page' => request('edits_page'), 'history_page' => request('history_page')])->links('pagination::bootstrap-4') }}
        </div>
    @endif
@endif
