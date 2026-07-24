@php
    $layout = auth()->user()->isMainAdminOrSub() ? 'layouts.dashboard' : 'layouts.admin';

    $uRole = auth()->user()->role ?? '';
    $uDept = auth()->user()->department ?? '';
    if (auth()->user()->isMainAdminOrSub() || in_array($uRole, ['Main Admin', 'Sub Main Admin', 'Head of Admin', 'Director General'])) {
        $sraNavRoute = 'admin.service-sra.index';
    } elseif (in_array($uRole, ['Head of Stores', 'Dept. Head (Stores)']) || strcasecmp($uDept, 'Stores') === 0 || strcasecmp($uDept, 'Store') === 0 || auth()->user()->isDelegatedApprover()) {
        $sraNavRoute = 'stores.service-sra.index';
    } elseif ($uRole === 'Auditor' || strcasecmp($uDept, 'Audit') === 0) {
        $sraNavRoute = 'auditor.service-sra.index';
    } else {
        $sraNavRoute = 'stores.service-sra.index';
    }
@endphp

@extends($layout)

@section('title', 'Item Entry Approval')

@section('content')
<style>
    .main-wrapper > *:not(header) {
        max-width: 2000px !important;
    }
    .sra-table-row { border-bottom: 1px solid var(--border-color); transition: background 0.15s; }
    .sra-table-row:hover { background: rgba(14,165,233,0.04); }
    .sra-table-row:last-child { border-bottom: none; }
    .sra-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 99px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }
    
    .sra-tabs-container { display: flex; gap: 0.5rem; border-bottom: 2px solid var(--border-color); margin-bottom: 1.5rem; padding-bottom: 2px; }
    .sra-tab-btn { padding: 0.75rem 1.5rem; font-weight: 700; font-size: 0.88rem; color: var(--text-muted); text-decoration: none; border-bottom: 3px solid transparent; transition: all 0.2s ease; display: flex; align-items: center; gap: 8px; cursor: pointer; }
    .sra-tab-btn:hover { color: var(--primary); }
    .sra-tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
    .sra-tab-badge { font-size: 0.72rem; font-weight: 800; padding: 2px 8px; border-radius: 99px; background: #f1f5f9; color: #475569; }
    .sra-tab-btn.active .sra-tab-badge { background: var(--primary-glow); color: var(--primary); }

    /* Premium Pagination Styling */
    .custom-pagination nav { display: flex; justify-content: center; }
    .custom-pagination ul.pagination { display: flex; gap: 0.5rem; list-style: none; padding: 0; margin: 0; align-items: center; }
    .custom-pagination .page-item .page-link {
        display: flex; align-items: center; justify-content: center;
        min-width: 48px; height: 48px; border-radius: 16px;
        background: white; border: 1.5px solid #edf2f7;
        color: var(--text-main); font-weight: 900; text-decoration: none;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.95rem; box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    }
    .custom-pagination .page-item.active .page-link {
        background: var(--primary); color: white;
        border-color: var(--primary);
        box-shadow: 0 10px 25px rgba(5,150,105,0.25);
        transform: scale(1.1);
        z-index: 10;
    }
    .custom-pagination .page-item:not(.active):not(.disabled) .page-link:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-4px);
        background: rgba(14,165,233,0.06);
        box-shadow: 0 8px 20px rgba(14,165,233,0.15);
    }
    .custom-pagination .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8fafc;
    }
    .custom-pagination .page-item:first-child .page-link,
    .custom-pagination .page-item:last-child .page-link {
        padding: 0 1.25rem;
        min-width: auto;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
</style>

<div class="animate-slide-up" style="padding: 2rem; width: 100%; box-sizing: border-box;">
    
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(5,150,105,0.1); color: #059669; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em;">Head of Stores — Entry Authorization</span>
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main); margin: 0;">Item Entry <span style="background: linear-gradient(135deg, #059669, #0ea5e9); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Approval Panel</span></h2>
            <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Review, authorize, or rollback new stock entries entered by Store Officers.</p>
        </div>
    </div>

    @if(isset($pendingServiceSras) && $pendingServiceSras->count() > 0)
        <div style="background: linear-gradient(135deg, rgba(14,165,233,0.08) 0%, rgba(14,165,233,0.12) 100%); border: 1.5px solid #0ea5e9; border-radius: 20px; padding: 1.25rem 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; box-shadow: 0 4px 20px rgba(14,165,233,0.12);">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 44px; height: 44px; background: #0ea5e9; color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 12px rgba(14,165,233,0.3);">
                    <i data-lucide="receipt" style="width: 22px; height: 22px;"></i>
                </div>
                <div>
                    <div style="font-size: 0.95rem; font-weight: 900; color: #0284c7; margin-bottom: 2px;">
                        Pending Service SRA Approvals ({{ $pendingServiceSras->count() }})
                    </div>
                    <div style="font-size: 0.83rem; color: #475569; font-weight: 600;">
                        Store Officers have submitted Service Stores Received Advice (SRA) requests awaiting authorization.
                    </div>
                </div>
            </div>
            <a href="{{ route($sraNavRoute) }}" style="background: #0ea5e9; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 800; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(14,165,233,0.3); transition: all 0.2s;">
                <span>Review Service SRAs</span>
                <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
            </a>
        </div>
    @endif

    <!-- Tabs Header -->
    <div class="sra-tabs-container">
        <div onclick="switchTab('pending')" id="tab-btn-pending" class="sra-tab-btn active">
            Pending Approval <span id="tab-pending-count" class="sra-tab-badge" style="background: #ef4444; color: white;">{{ $pending->total() }}</span>
        </div>
        <div onclick="switchTab('history')" id="tab-btn-history" class="sra-tab-btn">
            Decision History <span class="sra-tab-badge">{{ $history->total() }}</span>
        </div>
    </div>

    <!-- Pending Queue Table Wrapper -->
    <div id="section-pending" class="glass-card" style="border-radius: 24px; overflow: hidden; padding: 0; margin-bottom: 2rem; display: block; border: 1px solid var(--border-color); background: var(--bg-card); box-shadow: var(--shadow-luxe);">
        @include('edit-requests._pending_table')
    </div>

    <!-- History Queue Table Wrapper -->
    <div id="section-history" class="glass-card" style="border-radius: 24px; overflow: hidden; padding: 0; margin-bottom: 2rem; display: none; border: 1px solid var(--border-color); background: var(--bg-card); box-shadow: var(--shadow-luxe);">
        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.75rem;">
            <i data-lucide="history" style="color: var(--text-muted); width: 20px;"></i>
            <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-main);">Completed Decisions (Last 50)</h3>
        </div>
        
        @if($history->isEmpty())
            <div style="padding: 3rem 2rem; text-align: center; color: var(--text-muted);">
                <p style="margin: 0; font-weight: 600;">No historic item entry decisions found.</p>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: var(--bg-main); border-bottom: 1.5px solid var(--border-color);">
                            <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Request ID</th>
                            <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Date Processed</th>
                            <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Entered By</th>
                            <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Supplier</th>
                            <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">Decision</th>
                            <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); text-align: right;">Oversight</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $req)
                            @php
                                $payload = json_decode($req->payload, true) ?? [];
                                $supplier = !empty(trim($payload['supplier_name'] ?? '')) 
                                    ? $payload['supplier_name'] 
                                    : (!empty(trim($payload['donor_name'] ?? '')) ? $payload['donor_name'] : 'N/A');
                                $badgeColor = $req->status === 'approved' || $req->status === 'completed' ? 'rgba(5,150,105,0.1)' : 'rgba(239,68,68,0.1)';
                                $badgeTextColor = $req->status === 'approved' || $req->status === 'completed' ? '#059669' : '#ef4444';
                                $decisionText = $req->status === 'approved' || $req->status === 'completed' ? 'Authorized' : 'Rejected / Declined';
                            @endphp
                            @php
                                $isApproved = in_array($req->status, ['approved', 'completed']);
                                $batchId = $req->item_id;
                                if ($isApproved && !$batchId) {
                                    $batchId = \App\Models\InventoryBatch::where('recorded_by', $req->user_id)
                                        ->where('supplier_name', $supplier)
                                        ->orderBy('id', 'desc')
                                        ->value('id');
                                }
                                $rowClickUrl = ($isApproved && $batchId) ? route('receiveditems.sra', $batchId) : route('sra.preview', $req->id);
                            @endphp
                            <tr class="sra-table-row" style="cursor: pointer;" onclick="@if($isApproved && $batchId) window.open('{{ $rowClickUrl }}', '_blank'); @else window.location.href='{{ $rowClickUrl }}'; @endif">
                                <td style="padding: 1.25rem 1.5rem; font-weight: 800; color: #4b5563;">REQ-{{ str_pad($req->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td style="padding: 1.25rem 1.5rem; font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
                                    {{ $req->updated_at->format('d M Y, h:i A') }}
                                </td>
                                <td style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--text-main);">
                                    {{ $req->user->name ?? 'Unknown' }}
                                </td>
                                <td style="padding: 1.25rem 1.5rem; font-weight: 600; color: var(--text-main);">
                                    {{ $supplier }}
                                </td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    <span class="sra-badge" style="background: {{ $badgeColor }}; color: {{ $badgeTextColor }};">
                                        {{ $decisionText }}
                                    </span>
                                </td>
                                <td style="padding: 1.25rem 1.5rem; text-align: right;">
                                    @if($isApproved && $batchId)
                                        <a href="{{ route('receiveditems.sra', $batchId) }}" target="_blank" onclick="event.stopPropagation();" style="background: rgba(5,150,105,0.08); color: #059669; border: 1px solid rgba(5,150,105,0.2); border-radius: 10px; padding: 0.5rem 1.25rem; font-size: 0.78rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#059669'; this.style.color='white'; this.style.borderColor='#059669';" onmouseout="this.style.background='rgba(5,150,105,0.08)'; this.style.color='#059669'; this.style.borderColor='rgba(5,150,105,0.2)';">
                                            <i data-lucide="receipt" style="width: 14px; height: 14px;"></i> Receipt
                                        </a>
                                    @else
                                        <button onclick="event.stopPropagation(); window.location.href='{{ route('sra.preview', $req->id) }}'" style="background: transparent; color: var(--text-muted); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.4rem 0.85rem; font-size: 0.75rem; font-weight: 700; cursor: pointer;">
                                            View Details
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($history->hasPages())
                <div class="custom-pagination" style="padding: 1.5rem 2rem; border-top: 1px solid var(--border-color); background: var(--bg-main);">
                    {{ $history->appends(['pending_page' => request('pending_page')])->links('pagination::bootstrap-4') }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
function switchTab(tab) {
    if (tab === 'pending') {
        document.getElementById('tab-btn-pending').classList.add('active');
        document.getElementById('tab-btn-history').classList.remove('active');
        document.getElementById('section-pending').style.display = 'block';
        document.getElementById('section-history').style.display = 'none';
    } else {
        document.getElementById('tab-btn-pending').classList.remove('active');
        document.getElementById('tab-btn-history').classList.add('active');
        document.getElementById('section-pending').style.display = 'none';
        document.getElementById('section-history').style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') lucide.createIcons();
    
    // Auto switch to history tab if history pagination page parameter is set
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('history_page')) {
        switchTab('history');
    }
});

let lastPendingHtml = null;

function pollPendingApprovalsSilently() {
    const activeModal = document.querySelector('.modal-overlay:not([style*="display: none"]), .swal2-container, #signature-warning-overlay:not([style*="display: none"])');
    if (activeModal) return;

    const pageParam = new URLSearchParams(window.location.search).get('pending_page') || 1;
    const fetchUrl = window.location.pathname + '?pending_page=' + pageParam;

    fetch(fetchUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => {
        if (!res.ok) return null;
        return res.json();
    })
    .then(data => {
        if (!data || typeof data.pending_html === 'undefined') return;

        const badgeEl = document.getElementById('tab-pending-count');
        if (badgeEl && badgeEl.innerText !== String(data.pending_count)) {
            badgeEl.innerText = data.pending_count;
        }

        const sidebarBadge = document.getElementById('sidebar-badge-item-entry-approval');
        if (sidebarBadge) {
            sidebarBadge.innerText = data.pending_count;
            sidebarBadge.style.display = data.pending_count > 0 ? 'inline-block' : 'none';
        }

        const pendingContainer = document.getElementById('section-pending');
        if (pendingContainer) {
            if (lastPendingHtml === null) {
                lastPendingHtml = pendingContainer.innerHTML.trim();
            }

            const newHtmlTrimmed = data.pending_html.trim();
            if (lastPendingHtml !== newHtmlTrimmed) {
                lastPendingHtml = newHtmlTrimmed;
                pendingContainer.innerHTML = data.pending_html;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        }
    })
    .catch(() => {});
}

setInterval(pollPendingApprovalsSilently, 15000);

</script>
@endpush
