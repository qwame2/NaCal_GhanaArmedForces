@php
    $layout = auth()->user()->isMainAdminOrSub() ? 'layouts.dashboard' : 'layouts.admin';
@endphp
@extends($layout)
@section('content')
@section('title', 'Service SRA – Head of Stores Final Approval')

<style>
    .sra-table-row { border-bottom: 1px solid var(--border-color); transition: background 0.15s; }
    .sra-table-row:hover { background: rgba(14,165,233,0.04); }
    .sra-table-row:last-child { border-bottom: none; }
    .sra-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 99px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }
    .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.5); backdrop-filter: blur(8px); z-index: 10000; display: none; align-items: center; justify-content: center; }
    .modal-box { background: var(--bg-card); border-radius: 24px; padding: 2.5rem; max-width: 680px; width: 95%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 60px rgba(0,0,0,0.2); animation: slideUp 0.3s cubic-bezier(0.34,1.56,0.64,1); }
    @keyframes slideUp { from { opacity:0; transform: translateY(30px); } to { opacity:1; transform: translateY(0); } }
    @keyframes skeleton-shimmer {
        0% { background-position: -200px 0; opacity: 0.6; }
        50% { opacity: 1; }
        100% { background-position: 200px 0; opacity: 0.6; }
    }
    .skeleton-line {
        display: inline-block;
        height: 14px;
        border-radius: 6px;
        background: linear-gradient(90deg, rgba(148, 163, 184, 0.15) 25%, rgba(148, 163, 184, 0.32) 50%, rgba(148, 163, 184, 0.15) 75%);
        background-size: 400px 100%;
        animation: skeleton-shimmer 1.4s ease-in-out infinite;
    }
    .skeleton-badge {
        display: inline-block;
        height: 24px;
        width: 85px;
        border-radius: 99px;
        background: linear-gradient(90deg, rgba(148, 163, 184, 0.15) 25%, rgba(148, 163, 184, 0.32) 50%, rgba(148, 163, 184, 0.15) 75%);
        background-size: 400px 100%;
        animation: skeleton-shimmer 1.4s ease-in-out infinite;
    }
</style>

<div class="animate-slide-up">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main); margin: 0;">Service SRA <span style="background: linear-gradient(135deg, #059669, #0ea5e9); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Final Approval</span></h2>
        </div>
    </div>

    {{-- Tab Buttons --}}
    <div style="display: flex; gap: 0.5rem; border-bottom: 2px solid var(--border-color); margin-bottom: 1.5rem; padding-bottom: 2px;">
        <button onclick="switchStoresTab('pending')" id="tab-btn-pending" style="padding: 0.75rem 1.5rem; font-weight: 700; font-size: 0.88rem; border: none; background: transparent; cursor: pointer; border-bottom: 3px solid var(--primary); color: var(--primary); display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
            Awaiting Final Approval 
            <span id="tab-pending-count" style="background: rgba(5,150,105,0.12); color: #059669; padding: 2px 8px; border-radius: 99px; font-size: 0.72rem; font-weight: 800;">{{ $pending->total() }}</span>
        </button>
        <button onclick="switchStoresTab('history')" id="tab-btn-history" style="padding: 0.75rem 1.5rem; font-weight: 700; font-size: 0.88rem; border: none; background: transparent; cursor: pointer; border-bottom: 3px solid transparent; color: var(--text-muted); display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
            Approved SRA 
            <span id="tab-history-count" style="background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 99px; font-size: 0.72rem; font-weight: 800;">{{ $history->total() }}</span>
        </button>
    </div>

    {{-- Pending Table Pane --}}
    <div id="stores-pane-pending" style="display: block;">
        <div class="glass-card" style="border-radius: 24px; overflow: hidden; padding: 0; margin-bottom: 2rem;">
            <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="shield-check" style="color: #059669; width: 20px;"></i>
                <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-main);">Awaiting Final Approval ({{ $pending->count() }})</h3>
            </div>
            @if($pending->isEmpty())
                <div style="padding: 3rem 2rem; text-align: center; color: var(--text-muted);">
                    <i data-lucide="check-circle" style="width: 48px; height: 48px; opacity: 0.3; display: block; margin: 0 auto 1rem;"></i>
                    <p style="margin: 0; font-weight: 600;">No SRAs awaiting final approval.</p>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--bg-main);">
                                <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">SRA No.</th>
                                <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Submitted By</th>
                                <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Supplier</th>
                                <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Admin Approved By</th>
                                <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Date</th>
                                <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pending as $sra)
                            <tr class="sra-table-row" style="cursor: pointer;" onclick="window.open('{{ route('stores.service-sra.review', $sra->id) }}', '_blank')">
                                <td style="padding: 1rem 1.5rem; font-weight: 800; color: #059669;">{{ $sra->sra_number }}</td>
                                <td style="padding: 1rem 1.5rem;">
                                    <div style="font-weight: 700; color: var(--text-main);">{{ $sra->submitter->name ?? '—' }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $sra->dept }}</div>
                                </td>
                                <td style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text-main);">{{ $sra->supplier_name }}</td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">{{ $sra->admin_approved_by ?? '—' }}</td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">{{ $sra->date_of_delivery->format('d M Y') }}</td>
                                <td style="padding: 1rem 1.5rem; text-align: center;">
                                    <a href="{{ route('stores.service-sra.review', $sra->id) }}" target="_blank" onclick="event.stopPropagation();" style="background: rgba(14,165,233,0.1); color: #0284c7; border: 1px solid rgba(14,165,233,0.25); border-radius: 10px; padding: 0.5rem 1rem; font-size: 0.78rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s;" onmouseover="this.style.background='#0ea5e9';this.style.color='white';this.style.borderColor='#0ea5e9';" onmouseout="this.style.background='rgba(14,165,233,0.1)';this.style.color='#0284c7';this.style.borderColor='rgba(14,165,233,0.25)';">
                                        <i data-lucide="shield-check" style="width: 13px;"></i> Final Review
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($pending->hasPages())
                <div style="padding: 1.25rem 2rem; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                    <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted);">
                        Showing
                        <span style="font-weight: 900; color: var(--text-main);">{{ $pending->firstItem() }}</span>
                        &ndash;
                        <span style="font-weight: 900; color: var(--text-main);">{{ $pending->lastItem() }}</span>
                        of
                        <span style="font-weight: 900; color: var(--text-main);">{{ $pending->total() }}</span>
                        SRAs
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        {{-- Previous --}}
                        @if($pending->onFirstPage())
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: var(--bg-main); border: 1.5px solid var(--border-color); color: var(--text-muted); opacity: 0.45; cursor: not-allowed;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            </span>
                        @else
                            <a href="{{ $pending->appends(['history_page' => request('history_page')])->previousPageUrl() }}" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: var(--bg-card); border: 1.5px solid var(--border-color); color: var(--text-main); text-decoration: none; transition: 0.15s;" onmouseover="this.style.background='#059669';this.style.color='white';this.style.borderColor='#059669';" onmouseout="this.style.background='var(--bg-card)';this.style.color='var(--text-main)';this.style.borderColor='var(--border-color)';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach($pending->appends(['history_page' => request('history_page')])->getUrlRange(max(1, $pending->currentPage()-2), min($pending->lastPage(), $pending->currentPage()+2)) as $page => $url)
                            @if($page == $pending->currentPage())
                                <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border-radius: 10px; background: #059669; color: white; font-weight: 900; font-size: 0.82rem; border: 1.5px solid #059669; box-shadow: 0 4px 12px rgba(5,150,105,0.3);">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border-radius: 10px; background: var(--bg-card); color: var(--text-main); font-weight: 700; font-size: 0.82rem; border: 1.5px solid var(--border-color); text-decoration: none; transition: 0.15s;" onmouseover="this.style.background='rgba(5,150,105,0.08)';this.style.borderColor='rgba(5,150,105,0.3)';this.style.color='#059669';" onmouseout="this.style.background='var(--bg-card)';this.style.borderColor='var(--border-color)';this.style.color='var(--text-main)';">{{ $page }}</a>
                            @endif
                        @endforeach

                        {{-- Next --}}
                        @if($pending->hasMorePages())
                            <a href="{{ $pending->appends(['history_page' => request('history_page')])->nextPageUrl() }}" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: var(--bg-card); border: 1.5px solid var(--border-color); color: var(--text-main); text-decoration: none; transition: 0.15s;" onmouseover="this.style.background='#059669';this.style.color='white';this.style.borderColor='#059669';" onmouseout="this.style.background='var(--bg-card)';this.style.color='var(--text-main)';this.style.borderColor='var(--border-color)';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
                        @else
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: var(--bg-main); border: 1.5px solid var(--border-color); color: var(--text-muted); opacity: 0.45; cursor: not-allowed;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </span>
                        @endif
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>

    {{-- History Table Pane --}}
    <div id="stores-pane-history" style="display: none;">
        <div class="glass-card" style="border-radius: 24px; overflow: hidden; padding: 0;">
            <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i data-lucide="history" style="color: var(--text-muted); width: 20px;"></i>
                    <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-main);">Approved SRA ({{ $history->total() }})</h3>
                </div>
                {{-- SRA Type Filter --}}
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="filter" style="width: 14px; height: 14px; color: var(--text-muted);"></i>
                    <select id="sra-type-filter" class="filter-control" style="padding: 0.45rem 0.85rem; border-radius: 10px; font-size: 0.82rem; font-weight: 700; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);" onchange="filterApprovedSra(this.value)">
                        <option value="" {{ empty(request('sra_type')) ? 'selected' : '' }}>All Approved SRAs</option>
                        <option value="inventory_sra" {{ request('sra_type') === 'inventory_sra' ? 'selected' : '' }}>Inventory SRA</option>
                        <option value="service_sra" {{ request('sra_type') === 'service_sra' ? 'selected' : '' }}>Service SRA</option>
                    </select>
                </div>
            </div>
            @if($history->isEmpty())
                <div style="padding: 2rem; text-align: center; color: var(--text-muted);"><p style="margin: 0; font-weight: 600;">No decisions made yet.</p></div>
            @else
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--bg-main);">
                                <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">SRA No.</th>
                                <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Submitted By</th>
                                <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Supplier</th>
                                <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Decision</th>
                                <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Decided At</th>
                                <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $sra)
                                @if($sra instanceof \App\Models\InventoryBatch)
                                    @php
                                        $refNo = 'SRA-' . str_pad($sra->id, 5, '0', STR_PAD_LEFT);
                                        $submittedBy = $sra->recorder->name ?? 'Store Officer';
                                        $supplierName = $sra->supplier_name ?: ($sra->donor_name ?: 'Supplier');
                                        $decidedAt = $sra->auditor_approved_at ? \Carbon\Carbon::parse($sra->auditor_approved_at)->format('d M Y H:i') : ($sra->updated_at ? $sra->updated_at->format('d M Y H:i') : '—');
                                    @endphp
                                    <tr class="sra-table-row">
                                        <td style="padding: 1rem 1.5rem; font-weight: 800; color: #059669;">
                                            {{ $refNo }}
                                            <span class="sra-badge" style="background: rgba(5,150,105,0.08); color: #059669; margin-left: 6px; font-size: 0.65rem;">Inventory SRA</span>
                                        </td>
                                        <td style="padding: 1rem 1.5rem;">
                                            <div style="font-weight: 600; color: var(--text-main);">{{ $submittedBy }}</div>
                                            <div style="font-size: 0.72rem; color: var(--text-muted);">Stores</div>
                                        </td>
                                        <td style="padding: 1rem 1.5rem; color: var(--text-muted);">{{ $supplierName }}</td>
                                        <td style="padding: 1rem 1.5rem;">
                                            <span class="sra-badge" style="background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1.5px solid #10b981; font-weight: 800;">
                                                Approved
                                            </span>
                                        </td>
                                        <td style="padding: 1rem 1.5rem; font-size: 0.82rem; color: var(--text-muted);">{{ $decidedAt }}</td>
                                        <td style="padding: 1rem 1.5rem; text-align: center;">
                                            <a href="{{ route('receiveditems.sra', $sra->id) }}" target="_blank" style="background: rgba(5,150,105,0.08); color: #059669; border: 1px solid rgba(5,150,105,0.2); border-radius: 10px; padding: 0.5rem 1rem; font-size: 0.78rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                                <i data-lucide="file-text" style="width: 13px;"></i> View Receipt
                                            </a>
                                        </td>
                                    </tr>
                                @else
                                    <tr class="sra-table-row">
                                        <td style="padding: 1rem 1.5rem; font-weight: 800; color: #059669;">
                                            {{ $sra->sra_number }}
                                            <span class="sra-badge" style="background: rgba(14,165,233,0.1); color: #0284c7; border: 1px solid rgba(14,165,233,0.2); margin-left: 6px; font-size: 0.65rem;">Service SRA</span>
                                        </td>
                                        <td style="padding: 1rem 1.5rem;">
                                            <div style="font-weight: 600; color: var(--text-main);">{{ $sra->submitter->name ?? '—' }}</div>
                                            <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $sra->dept }}</div>
                                        </td>
                                        <td style="padding: 1rem 1.5rem; color: var(--text-muted);">{{ $sra->supplier_name }}</td>
                                        <td style="padding: 1rem 1.5rem;">
                                            <span class="sra-badge" style="background: {{ $sra->stores_status === 'approved' ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.1)' }}; color: {{ $sra->stores_status === 'approved' ? '#10b981' : '#ef4444' }}; border: {{ $sra->stores_status === 'approved' ? '1.5px solid #10b981' : '1px solid rgba(239,68,68,0.2)' }}; font-weight: 800;">
                                                {{ ucfirst($sra->stores_status) }}
                                            </span>
                                        </td>
                                        <td style="padding: 1rem 1.5rem; font-size: 0.82rem; color: var(--text-muted);">{{ $sra->stores_approved_at?->format('d M Y H:i') ?? ($sra->updated_at ? $sra->updated_at->format('d M Y H:i') : '—') }}</td>
                                        <td style="padding: 1rem 1.5rem; text-align: center;">
                                            @if($sra->stores_status === 'approved')
                                                <a href="{{ route('service-sra.receipt', $sra->id) }}" target="_blank" style="background: rgba(5,150,105,0.08); color: #059669; border: 1px solid rgba(5,150,105,0.2); border-radius: 10px; padding: 0.5rem 1rem; font-size: 0.78rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                                    <i data-lucide="file-text" style="width: 13px;"></i> View Receipt
                                                </a>
                                            @else
                                                <span style="color: var(--text-muted); font-size: 0.78rem; font-weight: 600;">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($history->hasPages())
                <div style="padding: 1.25rem 2rem; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                    <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted);">
                        Showing
                        <span style="font-weight: 900; color: var(--text-main);">{{ $history->firstItem() }}</span>
                        &ndash;
                        <span style="font-weight: 900; color: var(--text-main);">{{ $history->lastItem() }}</span>
                        of
                        <span style="font-weight: 900; color: var(--text-main);">{{ $history->total() }}</span>
                        approved SRAs
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        {{-- Previous --}}
                        @if($history->onFirstPage())
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: var(--bg-main); border: 1.5px solid var(--border-color); color: var(--text-muted); opacity: 0.45; cursor: not-allowed;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            </span>
                        @else
                            <a href="{{ $history->appends(['pending_page' => request('pending_page'), 'sra_type' => request('sra_type'), 'tab' => 'history'])->previousPageUrl() }}" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: var(--bg-card); border: 1.5px solid var(--border-color); color: var(--text-main); text-decoration: none; transition: 0.15s;" onmouseover="this.style.background='#059669';this.style.color='white';this.style.borderColor='#059669';" onmouseout="this.style.background='var(--bg-card)';this.style.color='var(--text-main)';this.style.borderColor='var(--border-color)';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach($history->appends(['pending_page' => request('pending_page'), 'sra_type' => request('sra_type'), 'tab' => 'history'])->getUrlRange(max(1, $history->currentPage()-2), min($history->lastPage(), $history->currentPage()+2)) as $page => $url)
                            @if($page == $history->currentPage())
                                <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border-radius: 10px; background: #059669; color: white; font-weight: 900; font-size: 0.82rem; border: 1.5px solid #059669; box-shadow: 0 4px 12px rgba(5,150,105,0.3);">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border-radius: 10px; background: var(--bg-card); color: var(--text-main); font-weight: 700; font-size: 0.82rem; border: 1.5px solid var(--border-color); text-decoration: none; transition: 0.15s;" onmouseover="this.style.background='rgba(5,150,105,0.08)';this.style.borderColor='rgba(5,150,105,0.3)';this.style.color='#059669';" onmouseout="this.style.background='var(--bg-card)';this.style.borderColor='var(--border-color)';this.style.color='var(--text-main)';">{{ $page }}</a>
                            @endif
                        @endforeach

                        {{-- Next --}}
                        @if($history->hasMorePages())
                            <a href="{{ $history->appends(['pending_page' => request('pending_page'), 'sra_type' => request('sra_type'), 'tab' => 'history'])->nextPageUrl() }}" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: var(--bg-card); border: 1.5px solid var(--border-color); color: var(--text-main); text-decoration: none; transition: 0.15s;" onmouseover="this.style.background='#059669';this.style.color='white';this.style.borderColor='#059669';" onmouseout="this.style.background='var(--bg-card)';this.style.color='var(--text-main)';this.style.borderColor='var(--border-color)';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
                        @else
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: var(--bg-main); border: 1.5px solid var(--border-color); color: var(--text-muted); opacity: 0.45; cursor: not-allowed;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </span>
                        @endif
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showStoresHistorySkeleton() {
        const tbody = document.querySelector('#stores-pane-history tbody');
        if (!tbody) return;
        let skeletonHtml = '';
        for (let i = 0; i < 5; i++) {
            skeletonHtml += `
                <tr class="sra-table-row">
                    <td style="padding: 1rem 1.5rem;"><div class="skeleton-line" style="width: 110px;"></div></td>
                    <td style="padding: 1rem 1.5rem;">
                        <div class="skeleton-line" style="width: 130px; display: block; margin-bottom: 6px;"></div>
                        <div class="skeleton-line" style="width: 55px; height: 10px; display: block;"></div>
                    </td>
                    <td style="padding: 1rem 1.5rem;"><div class="skeleton-line" style="width: 150px;"></div></td>
                    <td style="padding: 1rem 1.5rem;"><div class="skeleton-badge"></div></td>
                    <td style="padding: 1rem 1.5rem;"><div class="skeleton-line" style="width: 120px;"></div></td>
                    <td style="padding: 1rem 1.5rem; text-align: center;"><div class="skeleton-badge" style="width: 100px;"></div></td>
                </tr>
            `;
        }
        tbody.innerHTML = skeletonHtml;
    }

    async function filterApprovedSra(val, page) {
        const historyPane = document.getElementById('stores-pane-history');
        if (!historyPane) return;

        showStoresHistorySkeleton();

        const url = new URL(window.location.href);
        if (val !== undefined && val !== null) {
            if (val) {
                url.searchParams.set('sra_type', val);
            } else {
                url.searchParams.delete('sra_type');
            }
        }
        if (page) {
            url.searchParams.set('history_page', page);
        } else {
            url.searchParams.delete('history_page');
        }
        url.searchParams.set('tab', 'history');

        history.replaceState(null, '', url.toString());

        try {
            const res = await fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                const html = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newHistoryPane = doc.getElementById('stores-pane-history');
                if (newHistoryPane) {
                    historyPane.innerHTML = newHistoryPane.innerHTML;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    bindStoresHistoryPagination();
                }
            }
        } catch (err) {
            console.error('Failed to filter approved SRAs:', err);
        }
    }

    function bindStoresHistoryPagination() {
        const historyPane = document.getElementById('stores-pane-history');
        if (!historyPane) return;

        historyPane.querySelectorAll('a[href*="history_page"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                if (href) {
                    const u = new URL(href, window.location.origin);
                    const page = u.searchParams.get('history_page');
                    const sraType = u.searchParams.get('sra_type') || document.getElementById('sra-type-filter')?.value || '';
                    filterApprovedSra(sraType, page);
                }
            });
        });
    }

    function switchStoresTab(tab) {
        if (tab === 'pending') {
            document.getElementById('stores-pane-pending').style.display = 'block';
            document.getElementById('stores-pane-history').style.display = 'none';
            
            document.getElementById('tab-btn-pending').style.borderBottomColor = 'var(--primary)';
            document.getElementById('tab-btn-pending').style.color = 'var(--primary)';
            
            document.getElementById('tab-btn-history').style.borderBottomColor = 'transparent';
            document.getElementById('tab-btn-history').style.color = 'var(--text-muted)';
        } else {
            document.getElementById('stores-pane-pending').style.display = 'none';
            document.getElementById('stores-pane-history').style.display = 'block';
            
            document.getElementById('tab-btn-history').style.borderBottomColor = 'var(--primary)';
            document.getElementById('tab-btn-history').style.color = 'var(--primary)';
            
            document.getElementById('tab-btn-pending').style.borderBottomColor = 'transparent';
            document.getElementById('tab-btn-pending').style.color = 'var(--text-muted)';
        }
    }

    function getNormalizedHTML(element) {
        if (!element) return '';
        const clone = element.cloneNode(true);
        clone.querySelectorAll('svg, i, [data-lucide]').forEach(el => el.remove());
        return clone.innerHTML.replace(/\s+/g, ' ').trim();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('history_page') || urlParams.has('sra_type') || urlParams.get('tab') === 'history') {
            switchStoresTab('history');
        }
        bindStoresHistoryPagination();

        // Silent auto-refresh every 30 seconds (paused when tab is hidden)
        let _storesSilentRefreshPaused = document.hidden;
        document.addEventListener('visibilitychange', () => {
            _storesSilentRefreshPaused = document.hidden;
        });

        setInterval(async () => {
            if (_storesSilentRefreshPaused) return;
            if (typeof Swal !== 'undefined' && Swal.isVisible()) return;

            try {
                const res = await fetch(window.location.href);
                if (!res.ok) return;
                const html = await res.text();
                
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                let updated = false;

                // 1. Update Header Badge Container
                const newHeaderBadge = doc.getElementById('header-badge-container');
                const curHeaderBadge = document.getElementById('header-badge-container');
                if (newHeaderBadge && curHeaderBadge) {
                    if (getNormalizedHTML(newHeaderBadge) !== getNormalizedHTML(curHeaderBadge)) {
                        curHeaderBadge.innerHTML = newHeaderBadge.innerHTML;
                        updated = true;
                    }
                }

                // 2. Update Tab Count: Pending
                const newTabPending = doc.getElementById('tab-pending-count');
                const curTabPending = document.getElementById('tab-pending-count');
                if (newTabPending && curTabPending) {
                    if (newTabPending.textContent !== curTabPending.textContent) {
                        curTabPending.textContent = newTabPending.textContent;
                    }
                }

                // 3. Update Tab Count: History
                const newTabHistory = doc.getElementById('tab-history-count');
                const curTabHistory = document.getElementById('tab-history-count');
                if (newTabHistory && curTabHistory) {
                    if (newTabHistory.textContent !== curTabHistory.textContent) {
                        curTabHistory.textContent = newTabHistory.textContent;
                    }
                }

                // 4. Update Pending Table Pane
                const newPendingPane = doc.getElementById('stores-pane-pending');
                const curPendingPane = document.getElementById('stores-pane-pending');
                if (newPendingPane && curPendingPane) {
                    if (getNormalizedHTML(newPendingPane) !== getNormalizedHTML(curPendingPane)) {
                        newPendingPane.querySelectorAll('.animate-slide-up').forEach(el => el.classList.remove('animate-slide-up'));
                        curPendingPane.innerHTML = newPendingPane.innerHTML;
                        updated = true;
                    }
                }

                // 5. Update History Table Pane
                const newHistoryPane = doc.getElementById('stores-pane-history');
                const curHistoryPane = document.getElementById('stores-pane-history');
                if (newHistoryPane && curHistoryPane) {
                    if (getNormalizedHTML(newHistoryPane) !== getNormalizedHTML(curHistoryPane)) {
                        newHistoryPane.querySelectorAll('.animate-slide-up').forEach(el => el.classList.remove('animate-slide-up'));
                        curHistoryPane.innerHTML = newHistoryPane.innerHTML;
                        updated = true;
                    }
                }

                if (updated && typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            } catch (e) {
                console.error('Silent refresh failed:', e);
            }
        }, 30000);
    });
</script>
@endpush
@endsection
