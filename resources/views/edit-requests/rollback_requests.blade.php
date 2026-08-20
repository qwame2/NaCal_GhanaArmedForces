@extends('layouts.dashboard')

@section('title', 'Rollback Requests')

@section('content')
<style>
    .main-wrapper > *:not(header) {
        max-width: 2000px !important;
    }

    /* Blinking Pulse Animation for Live Sync Indicator */
    @keyframes rbBlinkPulse {
        0%, 100% {
            opacity: 1;
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
        }
        50% {
            opacity: 0.35;
            transform: scale(1.2);
            box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
        }
    }

    /* Silent Refresh Flash Animation for Table Wrapper */
    @keyframes rbTableFlash {
        0% { background-color: rgba(239, 68, 68, 0.08); }
        100% { background-color: transparent; }
    }

    .rb-flash-sync {
        animation: rbTableFlash 1s ease-out;
    }

    /* Table Hover & Row Styling */
    .rb-table-row {
        border-bottom: 1px solid var(--border-color, #f1f5f9);
        transition: all 0.2s ease;
    }
    .rb-table-row:hover {
        background: rgba(239, 68, 68, 0.03) !important;
    }
    .rb-table-row:last-child {
        border-bottom: none;
    }

    /* Badge & Button Styling */
    .rb-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 0.7rem;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .rb-field-tag {
        background: #fff1f2;
        border: 1px solid #fecaca;
        color: #9f1239;
        font-size: 0.76rem;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s ease;
    }
    .rb-field-tag:hover {
        background: #ffe4e6;
        border-color: #fda4af;
    }

    .rb-action-btn {
        background: linear-gradient(135deg, var(--primary, #6366f1) 0%, var(--primary-dark, #4f46e5) 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 12px;
        padding: 0.65rem 1.25rem;
        font-size: 0.82rem;
        font-weight: 800;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .rb-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(99, 102, 241, 0.4);
    }

    /* Responsive Mobile Cards */
    @media (max-width: 768px) {
        .rb-table-wrapper table, 
        .rb-table-wrapper thead, 
        .rb-table-wrapper tbody, 
        .rb-table-wrapper th, 
        .rb-table-wrapper td, 
        .rb-table-wrapper tr { 
            display: block; 
        }
        .rb-table-wrapper thead {
            display: none;
        }
        .rb-table-wrapper tr {
            margin-bottom: 1.25rem;
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--danger, #ef4444);
            border-radius: 18px;
            padding: 1.25rem;
            background: var(--bg-card);
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }
        .rb-table-wrapper td {
            padding: 0.65rem 0 !important;
            border-bottom: 1px dashed var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .rb-table-wrapper td:last-child {
            border-bottom: none;
            padding-top: 1rem !important;
            justify-content: flex-end;
        }
        .rb-table-wrapper td::before {
            content: attr(data-label);
            font-size: 0.7rem;
            font-weight: 850;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    }

    /* Pagination */
    .custom-pagination nav { display: flex; justify-content: center; }
    .custom-pagination ul.pagination { display: flex; gap: 0.5rem; list-style: none; padding: 0; margin: 0; align-items: center; }
    .custom-pagination .page-item .page-link {
        display: flex; align-items: center; justify-content: center;
        min-width: 42px; height: 42px; border-radius: 12px;
        background: var(--bg-card, #ffffff); border: 1.5px solid var(--border-color, #edf2f7);
        color: var(--text-main, #0f172a); font-weight: 800; text-decoration: none;
        transition: all 0.2s ease; font-size: 0.88rem;
    }
    .custom-pagination .page-item.active .page-link {
        background: var(--danger, #ef4444); color: white;
        border-color: var(--danger, #ef4444);
        box-shadow: 0 6px 16px rgba(239,68,68,0.3);
    }
</style>

<div class="animate-slide-up" style="padding: 2rem; width: 100%; box-sizing: border-box;">
    
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(239, 68, 68, 0.1); color: var(--danger, #ef4444); font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em;">
                    STORE OFFICER · ENTRY CORRECTIONS
                </span>
                
                <!-- Live Blinking Auto-Sync Indicator -->
                <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(239, 68, 68, 0.08); padding: 0.2rem 0.65rem; border-radius: 99px; border: 1px solid rgba(239, 68, 68, 0.2);" title="Live Auto-Sync Active">
                    <span style="width: 7px; height: 7px; background: #ef4444; border-radius: 50%; display: inline-block; animation: rbBlinkPulse 1.5s infinite;" id="liveSyncDot"></span>
                    <span style="font-size: 0.68rem; font-weight: 800; color: #ef4444; text-transform: uppercase; letter-spacing: 0.04em;">Live Auto-Sync</span>
                </div>
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main, #0f172a); margin: 0;">
                Rollback <span style="color: var(--danger, #ef4444);">Requests</span>
            </h2>
            <p style="color: var(--text-muted, #64748b); margin: 0.5rem 0 0;">
                Review inventory entry correction requests returned by the Head of Stores.
            </p>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button onclick="pollRollbackQueueSilently(true)" class="glass-card" style="padding: 0.75rem 1.25rem; border: 1px solid var(--border-color); border-radius: 12px; background: var(--bg-card); cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--text-main); transition: all 0.2s;" onmouseover="this.style.background='var(--bg-main)'" onmouseout="this.style.background='var(--bg-card)'">
                <i data-lucide="refresh-cw" style="width: 16px; height: 16px; color: var(--text-muted);" id="refreshBtnIcon"></i>
                Refresh List
            </button>
        </div>
    </div>

    <!-- Quick Stat Cards -->
    <div id="rollbackCardStats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        
        <div class="glass-card" style="padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border-color); background: var(--bg-card); display: flex; align-items: center; gap: 1.25rem; box-shadow: var(--shadow-premium);">
            <div style="width: 52px; height: 52px; background: rgba(239, 68, 68, 0.1); color: var(--danger, #ef4444); border-radius: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="rotate-ccw" style="width: 26px; height: 26px;"></i>
            </div>
            <div>
                <div style="font-size: 0.82rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;">Total Pending</div>
                <div style="font-size: 1.75rem; font-weight: 900; color: var(--text-main); line-height: 1.1; margin-top: 2px;">{{ $rollbacks->total() }}</div>
            </div>
        </div>

        @php
            $flaggedCount = 0;
            $notesCount = 0;
            foreach ($rollbacks as $rbItem) {
                $rbData = json_decode($rbItem->rollback_fields ?? '{}', true) ?? [];
                if (!empty($rbData['flagged'])) $flaggedCount++;
                if (!empty($rbData['note'])) $notesCount++;
            }
        @endphp

        <div class="glass-card" style="padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border-color); background: var(--bg-card); display: flex; align-items: center; gap: 1.25rem; box-shadow: var(--shadow-premium);">
            <div style="width: 52px; height: 52px; background: rgba(245, 158, 11, 0.1); color: var(--accent, #f59e0b); border-radius: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="alert-triangle" style="width: 26px; height: 26px;"></i>
            </div>
            <div>
                <div style="font-size: 0.82rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;">Flagged Submissions</div>
                <div style="font-size: 1.75rem; font-weight: 900; color: var(--text-main); line-height: 1.1; margin-top: 2px;">{{ $flaggedCount }}</div>
            </div>
        </div>

        <div class="glass-card" style="padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border-color); background: var(--bg-card); display: flex; align-items: center; gap: 1.25rem; box-shadow: var(--shadow-premium);">
            <div style="width: 52px; height: 52px; background: rgba(99, 102, 241, 0.1); color: var(--primary, #6366f1); border-radius: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="message-square" style="width: 26px; height: 26px;"></i>
            </div>
            <div>
                <div style="font-size: 0.82rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;">HOD Remarks</div>
                <div style="font-size: 1.75rem; font-weight: 900; color: var(--text-main); line-height: 1.1; margin-top: 2px;">{{ $notesCount }}</div>
            </div>
        </div>

    </div>

    <!-- Main Content Panel Wrapper -->
    <div id="rollbackQueueWrapper" class="glass-card rb-table-wrapper" style="border-radius: 24px; overflow: hidden; padding: 0; margin-bottom: 2rem; border: 1px solid var(--border-color); background: var(--bg-card); box-shadow: var(--shadow-luxe); transition: background-color 0.4s ease;">
        
        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 36px; height: 36px; background: rgba(239, 68, 68, 0.1); color: var(--danger); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="rotate-ccw" style="width: 18px; height: 18px;"></i>
                </div>
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--text-main);">Rollback Queue</h3>
            </div>
            <span id="totalItemsBadge" style="font-size: 0.78rem; font-weight: 850; color: var(--text-muted); background: var(--bg-main); padding: 5px 14px; border-radius: 99px; border: 1px solid var(--border-color);">
                {{ $rollbacks->total() }} Item(s)
            </span>
        </div>

        @if($rollbacks->isEmpty())
            <div style="padding: 4.5rem 2rem; text-align: center; color: var(--text-muted);">
                <div style="width: 72px; height: 72px; background: rgba(16, 185, 129, 0.1); color: #10b981; border: 2px solid rgba(16, 185, 129, 0.2); border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                    <i data-lucide="check-circle-2" style="width: 38px; height: 38px;"></i>
                </div>
                <h4 style="font-size: 1.2rem; font-weight: 900; color: var(--text-main); margin: 0 0 0.5rem 0;">No Rollback Requests Pending</h4>
                <p style="margin: 0 auto; font-size: 0.9rem; font-weight: 500; max-width: 460px; line-height: 1.6;">
                    All inventory submissions are verified and cleared by the Head of Stores.
                </p>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: var(--bg-main); border-bottom: 1.5px solid var(--border-color);">
                            <th style="padding: 1.1rem 1.5rem; font-size: 0.74rem; font-weight: 850; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em;">Request ID</th>
                            <th style="padding: 1.1rem 1.5rem; font-size: 0.74rem; font-weight: 850; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em;">Flagged Date</th>
                            <th style="padding: 1.1rem 1.5rem; font-size: 0.74rem; font-weight: 850; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em;">Supplier / Source</th>
                            <th style="padding: 1.1rem 1.5rem; font-size: 0.74rem; font-weight: 850; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em;">Correction Details</th>
                            <th style="padding: 1.1rem 1.5rem; font-size: 0.74rem; font-weight: 850; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rollbacks as $req)
                            @php
                                $rollbackData = json_decode($req->rollback_fields ?? '{}', true) ?? [];
                                $flaggedFields = $rollbackData['flagged'] ?? [];
                                $generalNote = $rollbackData['note'] ?? '';
                                $flaggedItems = $rollbackData['items'] ?? [];
                                
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

                                $resumeUrl = route('inventory.create') . '?rollback=' . $req->id;
                            @endphp
                            <tr class="rb-table-row">
                                
                                <!-- ID & Status -->
                                <td data-label="Request ID" style="padding: 1.25rem 1.5rem;">
                                    <div style="font-weight: 900; font-size: 0.95rem; color: var(--text-main);">
                                        RB-{{ str_pad($req->id, 5, '0', STR_PAD_LEFT) }}
                                    </div>
                                    @if($req->status === 'resubmitted')
                                        <span class="rb-badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; margin-top: 4px;">
                                            <i data-lucide="check-circle-2" style="width: 12px; height: 12px;"></i> Resubmitted
                                        </span>
                                    @else
                                        <span class="rb-badge" style="background: rgba(239, 68, 68, 0.1); color: var(--danger, #ef4444); margin-top: 4px;">
                                            <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i> Rollback
                                        </span>
                                    @endif
                                </td>

                                <!-- Date -->
                                <td data-label="Flagged Date" style="padding: 1.25rem 1.5rem; font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
                                    {{ $req->updated_at->format('d M Y, h:i A') }}
                                </td>

                                <!-- Supplier / Category -->
                                <td data-label="Supplier / Source" style="padding: 1.25rem 1.5rem;">
                                    <div style="font-weight: 800; color: var(--text-main); font-size: 0.92rem;">
                                        {{ $supplier }}
                                    </div>
                                    @if(isset($payload['ledge_category']))
                                        <span style="font-size: 0.72rem; color: var(--primary); font-weight: 800; background: var(--primary-glow); padding: 2px 8px; border-radius: 6px; display: inline-block; margin-top: 4px;">
                                            Category {{ $payload['ledge_category'] }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Correction Notes -->
                                <td data-label="Correction Details" style="padding: 1.25rem 1.5rem;">
                                    @if(!empty($flaggedFields))
                                        <div style="margin-bottom: 6px;">
                                            <div style="font-size: 0.7rem; font-weight: 850; text-transform: uppercase; color: var(--danger); letter-spacing: 0.04em; margin-bottom: 4px; display: flex; align-items: center; gap: 4px;">
                                                <i data-lucide="alert-triangle" style="width: 12px; height: 12px;"></i> Flagged Fields
                                            </div>
                                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                                @foreach($flaggedFields as $fKey => $fNote)
                                                    <div class="rb-field-tag">
                                                        <strong style="color: #881337;">{{ $fieldLabels[$fKey] ?? ucwords(str_replace('_', ' ', $fKey)) }}:</strong> {{ $fNote }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if($generalNote)
                                        <div style="font-size: 0.82rem; color: #78350f; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 10px; padding: 8px 12px; margin-top: 4px; display: flex; gap: 8px; align-items: flex-start;">
                                            <i data-lucide="message-square" style="width: 14px; height: 14px; color: #b45309; flex-shrink: 0; margin-top: 2px;"></i>
                                            <div><strong>HOD Remarks:</strong> {{ $generalNote }}</div>
                                        </div>
                                    @endif

                                    @if(empty($flaggedFields) && !$generalNote)
                                        <span style="font-size: 0.82rem; color: var(--text-muted); font-style: italic;">
                                            General correction requested by Head of Stores.
                                        </span>
                                    @endif
                                </td>

                                <!-- Action -->
                                <td data-label="Action" style="padding: 1.25rem 1.5rem; text-align: right;">
                                    @if($req->status === 'resubmitted')
                                        <button disabled style="background: #e2e8f0; color: #64748b; border: 1px solid #cbd5e1; border-radius: 12px; padding: 0.65rem 1.25rem; font-size: 0.82rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; cursor: not-allowed; opacity: 0.85;" title="Correction has been submitted for approval">
                                            <i data-lucide="check-circle" style="width: 14px; height: 14px; color: #10b981;"></i>
                                            <span>Correction Submitted</span>
                                        </button>
                                    @else
                                        <a href="{{ $resumeUrl }}" class="rb-action-btn">
                                            <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                                            <span>Resume &amp; Correct Entry</span>
                                        </a>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($rollbacks->hasPages())
                <div class="custom-pagination" style="padding: 1.5rem 2rem; border-top: 1px solid var(--border-color); background: var(--bg-main);">
                    {{ $rollbacks->links('pagination::bootstrap-4') }}
                </div>
            @endif
        @endif

    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') lucide.createIcons();

    let isPollingRollback = false;

    window.pollRollbackQueueSilently = function(manual = false) {
        if (isPollingRollback) return;
        if (!manual && document.hidden) return;

        isPollingRollback = true;

        const refreshIcon = document.getElementById('refreshBtnIcon');
        if (manual && refreshIcon) {
            refreshIcon.classList.add('animate-spin');
        }

        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(htmlText => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlText, 'text/html');

            // Update Stats Bar
            const newStats = doc.getElementById('rollbackCardStats');
            const currentStats = document.getElementById('rollbackCardStats');
            if (newStats && currentStats) {
                currentStats.innerHTML = newStats.innerHTML;
            }

            // Update Table Queue Wrapper
            const newWrapper = doc.getElementById('rollbackQueueWrapper');
            const currentWrapper = document.getElementById('rollbackQueueWrapper');
            if (newWrapper && currentWrapper) {
                currentWrapper.innerHTML = newWrapper.innerHTML;
            }

            if (typeof lucide !== 'undefined') lucide.createIcons();
        })
        .catch(err => {
            console.error("Rollback refresh error:", err);
        })
        .finally(() => {
            isPollingRollback = false;
            if (manual && refreshIcon) {
                refreshIcon.classList.remove('animate-spin');
            }
        });
    };
});
</script>
@endpush
