@php
    $layout = auth()->user()->isMainAdminOrSub() ? 'layouts.dashboard' : 'layouts.admin';
@endphp
@extends($layout)

@section('title', 'Head of Stores Dashboard')

@section('content')
<div class="animate-slide-up">
    {{-- Page Header --}}
    <div class="page-header dashboard-header-mobile" style="position: relative; overflow: hidden; background: var(--bg-card); padding: 2rem; border-radius: 20px; border: 1px solid var(--border-color); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
        <div style="position: absolute; top: -50px; right: -50px; width: 180px; height: 180px; background: rgba(5, 150, 105, 0.05); border-radius: 50%;"></div>
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                
                <span style="color: var(--text-muted); font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                    <i data-lucide="calendar" style="width: 14px; height: 14px;"></i>
                    {{ date('d M Y') }}
                </span>
            </div>
            <h2 style="font-size: 2.1rem; font-weight: 900; letter-spacing: -0.04em; color: var(--text-main); margin: 0 0 0.25rem 0;">Head of Stores <span style="color: var(--primary);">Dashboard</span></h2>
            <p style="color: var(--text-muted); font-size: 1rem; font-weight: 500; margin: 0;">Enables easy monitoring and control of store requests, items, and inventory.</p>
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <button onclick="window.location.reload()" class="btn-secondary" style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 0.75rem 1.25rem; border-radius: 12px; color: var(--text-main); font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; transition: all 0.2s ease;">
                <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i> Refresh
            </button>
            
        </div>
    </div>

    {{-- Metrics Cards Grid --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
        {{-- Card 1: Pending Requisitions --}}
        <div class="stat-card" style="background: var(--bg-card); border-radius: 18px; padding: 1.5rem; border: 1px solid var(--border-color); position: relative; overflow: hidden; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div style="width: 46px; height: 46px; border-radius: 14px; background: rgba(5, 150, 105, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="file-text" style="width: 22px; height: 22px;"></i>
                </div>
                @if($pendingRequisitionsCount > 0)
                <span style="background: #ef4444; color: white; font-size: 0.7rem; font-weight: 800; padding: 3px 10px; border-radius: 99px;">
                    Action Needed
                </span>
                @else
                <span style="background: #10b981; color: white; font-size: 0.7rem; font-weight: 800; padding: 3px 10px; border-radius: 99px;">
                    Up to Date
                </span>
                @endif
            </div>
            <p style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.2rem 0;">Pending Requisitions</p>
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.4rem;">Awaiting Head of Stores Approvals</div>
            <h3 id="pendingRequisitionsCountVal" style="font-size: 2.25rem; font-weight: 900; color: var(--text-main); margin: 0 0 0.5rem 0; letter-spacing: -0.03em;">{{ number_format($pendingRequisitionsCount) }}</h3>
            <a href="{{ route('admin.requisitions') }}" style="font-size: 0.8rem; font-weight: 700; color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                Review Requisitions &rarr;
            </a>
        </div>

        {{-- Card 2: Item Entry Approvals --}}
        <div class="stat-card" style="background: var(--bg-card); border-radius: 18px; padding: 1.5rem; border: 1px solid var(--border-color); position: relative; overflow: hidden; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div style="width: 46px; height: 46px; border-radius: 14px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="check-square" style="width: 22px; height: 22px;"></i>
                </div>
                @if($pendingItemEntryCount > 0)
                <span style="background: #f59e0b; color: white; font-size: 0.7rem; font-weight: 800; padding: 3px 10px; border-radius: 99px;">
                    Pending
                </span>
                @else
                <span style="background: #10b981; color: white; font-size: 0.7rem; font-weight: 800; padding: 3px 10px; border-radius: 99px;">
                    Cleared
                </span>
                @endif
            </div>
            <p style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.35rem 0;">Item Entry Requests</p>
            <h3 id="pendingItemEntryCountVal" style="font-size: 2.25rem; font-weight: 900; color: var(--text-main); margin: 0 0 0.5rem 0; letter-spacing: -0.03em;">{{ number_format($pendingItemEntryCount) }}</h3>
            <a href="{{ route('stores.item-entry-approval') }}" style="font-size: 0.8rem; font-weight: 700; color: #3b82f6; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                Review Entry Approvals &rarr;
            </a>
        </div>

        {{-- Card 3: Service SRA Approvals --}}
        <div class="stat-card" style="background: var(--bg-card); border-radius: 18px; padding: 1.5rem; border: 1px solid var(--border-color); position: relative; overflow: hidden; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div style="width: 46px; height: 46px; border-radius: 14px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="clipboard-check" style="width: 22px; height: 22px;"></i>
                </div>
                @if($pendingServiceSraCount > 0)
                <span style="background: #3b82f6; color: white; font-size: 0.7rem; font-weight: 800; padding: 3px 10px; border-radius: 99px;">
                    In Workflow
                </span>
                @else
                <span style="background: #10b981; color: white; font-size: 0.7rem; font-weight: 800; padding: 3px 10px; border-radius: 99px;">
                    All Done
                </span>
                @endif
            </div>
            <p style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.35rem 0;">Service SRA Queue</p>
            <h3 id="pendingServiceSraCountVal" style="font-size: 2.25rem; font-weight: 900; color: var(--text-main); margin: 0 0 0.5rem 0; letter-spacing: -0.03em;">{{ number_format($pendingServiceSraCount) }}</h3>
            <a href="{{ route('stores.service-sra.index') }}" style="font-size: 0.8rem; font-weight: 700; color: #10b981; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                Manage Service SRAs &rarr;
            </a>
        </div>

        {{-- Card 4: Low Stock Alerts --}}
        <div class="stat-card" style="background: var(--bg-card); border-radius: 18px; padding: 1.5rem; border: 1px solid var(--border-color); position: relative; overflow: hidden; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div style="width: 46px; height: 46px; border-radius: 14px; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="alert-triangle" style="width: 22px; height: 22px;"></i>
                </div>
                @if($lowStockCount > 0)
                <span style="background: #ef4444; color: white; font-size: 0.7rem; font-weight: 800; padding: 3px 10px; border-radius: 99px;">
                    Low Level
                </span>
                @else
                <span style="background: #10b981; color: white; font-size: 0.7rem; font-weight: 800; padding: 3px 10px; border-radius: 99px;">
                    Optimal
                </span>
                @endif
            </div>
            <p style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.35rem 0;">Low Stock Alerts</p>
            <h3 id="lowStockCountVal" style="font-size: 2.25rem; font-weight: 900; color: var(--text-main); margin: 0 0 0.5rem 0; letter-spacing: -0.03em;">{{ number_format($lowStockCount) }}</h3>
            <a href="{{ route('admin.inventory') }}" style="font-size: 0.8rem; font-weight: 700; color: #ef4444; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                View Live Stock &rarr;
            </a>
        </div>
    </div>

    {{-- Quick Access Action Hub --}}
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="layout-grid" style="width: 18px; color: var(--primary);"></i> Management Hub
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
            <a href="{{ route('admin.requisitions') }}" style="background: var(--bg-card); padding: 1.25rem; border-radius: 16px; border: 1px solid var(--border-color); text-decoration: none; color: var(--text-main); display: flex; align-items: center; gap: 1rem; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(5, 150, 105, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; shrink: 0;">
                    <i data-lucide="file-check-2" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 0.95rem; font-weight: 800;">Review Requests</h4>
                    <p style="margin: 2px 0 0 0; font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">Approve & authorize requisitions</p>
                </div>
            </a>

            <a href="{{ route('stores.item-entry-approval') }}" style="background: var(--bg-card); padding: 1.25rem; border-radius: 16px; border: 1px solid var(--border-color); text-decoration: none; color: var(--text-main); display: flex; align-items: center; gap: 1rem; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; shrink: 0;">
                    <i data-lucide="package-plus" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 0.95rem; font-weight: 800;">Item Entry Approvals</h4>
                    <p style="margin: 2px 0 0 0; font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">Authorize new stock entries</p>
                </div>
            </a>

            <a href="{{ route('stores.service-sra.index') }}" style="background: var(--bg-card); padding: 1.25rem; border-radius: 16px; border: 1px solid var(--border-color); text-decoration: none; color: var(--text-main); display: flex; align-items: center; gap: 1rem; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; shrink: 0;">
                    <i data-lucide="wrench" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 0.95rem; font-weight: 800;">Service SRA Approvals</h4>
                    <p style="margin: 2px 0 0 0; font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">Finalize service receipts</p>
                </div>
            </a>

            <a href="{{ route('admin.inventory') }}" style="background: var(--bg-card); padding: 1.25rem; border-radius: 16px; border: 1px solid var(--border-color); text-decoration: none; color: var(--text-main); display: flex; align-items: center; gap: 1rem; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); color: #7c3aed; display: flex; align-items: center; justify-content: center; shrink: 0;">
                    <i data-lucide="boxes" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 0.95rem; font-weight: 800;">Live Inventory</h4>
                    <p style="margin: 2px 0 0 0; font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">Inspect stock balances</p>
                </div>
            </a>

            <a href="{{ route('reports.index') }}" style="background: var(--bg-card); padding: 1.25rem; border-radius: 16px; border: 1px solid var(--border-color); text-decoration: none; color: var(--text-main); display: flex; align-items: center; gap: 1rem; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(236, 72, 153, 0.1); color: #ec4899; display: flex; align-items: center; justify-content: center; shrink: 0;">
                    <i data-lucide="bar-chart-3" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 0.95rem; font-weight: 800;">Reports & Analytics</h4>
                    <p style="margin: 2px 0 0 0; font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">Export ledgers & stats</p>
                </div>
            </a>
        </div>
    </div>

    {{-- Tables Preview Section --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
        {{-- Recent Requisitions Table --}}
        <div id="recentRequisitionsSection" style="background: var(--bg-card); border-radius: 18px; border: 1px solid var(--border-color); padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="clock" style="width: 18px; color: var(--primary);"></i> Recent Pending Requisitions
                </h3>
                <a href="{{ route('admin.requisitions') }}" style="font-size: 0.8rem; font-weight: 700; color: var(--primary); text-decoration: none;">View All &rarr;</a>
            </div>

            @if($recentRequisitions->isEmpty())
                <div style="text-align: center; padding: 2rem 1rem; color: var(--text-muted);">
                    <i data-lucide="check-circle-2" style="width: 32px; height: 32px; color: #10b981; margin-bottom: 0.5rem;"></i>
                    <p style="margin: 0; font-weight: 600; font-size: 0.9rem;">No pending requisitions awaiting review.</p>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead>
                            <tr style="border-bottom: 1.5px solid var(--border-color); text-align: left; color: var(--text-muted);">
                                <th style="padding: 0.75rem 0.5rem; font-weight: 700;">Req #</th>
                                <th style="padding: 0.75rem 0.5rem; font-weight: 700;">Requester</th>
                                <th style="padding: 0.75rem 0.5rem; font-weight: 700;">Dept</th>
                                <th style="padding: 0.75rem 0.5rem; font-weight: 700; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentRequisitions as $req)
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 0.85rem 0.5rem; font-weight: 800; color: var(--primary);">#REQ-{{ str_pad($req->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td style="padding: 0.85rem 0.5rem; font-weight: 600; color: var(--text-main);">{{ $req->requester_name ?? optional($req->requester)->name ?? 'Staff' }}</td>
                                <td style="padding: 0.85rem 0.5rem; color: var(--text-muted);">{{ $req->department }}</td>
                                <td style="padding: 0.85rem 0.5rem; text-align: right;">
                                    <a href="{{ route('admin.requisitions', ['open_id' => $req->id]) }}" style="background: rgba(5, 150, 105, 0.1); color: var(--primary); padding: 0.35rem 0.75rem; border-radius: 8px; font-weight: 800; font-size: 0.75rem; text-decoration: none;">
                                        Review
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Recent Item Entry Requests Table --}}
        <div id="recentItemEntriesSection" style="background: var(--bg-card); border-radius: 18px; border: 1px solid var(--border-color); padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="package-search" style="width: 18px; color: #3b82f6;"></i> Recent Item Entry Requests
                </h3>
                <a href="{{ route('stores.item-entry-approval') }}" style="font-size: 0.8rem; font-weight: 700; color: #3b82f6; text-decoration: none;">View All &rarr;</a>
            </div>

            @if($recentItemEntries->isEmpty())
                <div style="text-align: center; padding: 2rem 1rem; color: var(--text-muted);">
                    <i data-lucide="shield-check" style="width: 32px; height: 32px; color: #10b981; margin-bottom: 0.5rem;"></i>
                    <p style="margin: 0; font-weight: 600; font-size: 0.9rem;">No pending item entry approvals.</p>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead>
                            <tr style="border-bottom: 1.5px solid var(--border-color); text-align: left; color: var(--text-muted);">
                                <th style="padding: 0.75rem 0.5rem; font-weight: 700;">Item Batch</th>
                                <th style="padding: 0.75rem 0.5rem; font-weight: 700;">Submitted By</th>
                                <th style="padding: 0.75rem 0.5rem; font-weight: 700;">Date</th>
                                <th style="padding: 0.75rem 0.5rem; font-weight: 700; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentItemEntries as $entry)
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 0.85rem 0.5rem; font-weight: 700; color: var(--text-main);">{{ optional($entry->batch)->supplier_name ?? 'Batch #' . str_pad($entry->item_id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td style="padding: 0.85rem 0.5rem; color: var(--text-muted);">{{ optional($entry->user)->name ?? 'Store Officer' }}</td>
                                <td style="padding: 0.85rem 0.5rem; color: var(--text-muted);">{{ $entry->created_at->format('d/m/Y') }}</td>
                                <td style="padding: 0.85rem 0.5rem; text-align: right;">
                                    <a href="{{ route('sra.preview', $entry->id) }}" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 0.35rem 0.75rem; border-radius: 8px; font-weight: 800; font-size: 0.75rem; text-decoration: none;">
                                        Authorize
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    let lastItemEntriesHtml = null;
    let lastRequisitionsHtml = null;

    async function pollStoresDashboardSilently() {
        if (document.hidden) return;

        try {
            const response = await fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) return;
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // 1. Silent update Recent Item Entry Requests section if changed
            const curItemEntries = document.getElementById('recentItemEntriesSection');
            const newItemEntries = doc.getElementById('recentItemEntriesSection');
            if (curItemEntries && newItemEntries) {
                const newHtml = newItemEntries.innerHTML.trim();
                if (lastItemEntriesHtml === null) {
                    lastItemEntriesHtml = curItemEntries.innerHTML.trim();
                }
                if (lastItemEntriesHtml !== newHtml) {
                    lastItemEntriesHtml = newHtml;
                    curItemEntries.innerHTML = newItemEntries.innerHTML;
                    if (window.lucide) lucide.createIcons();
                }
            }

            // 2. Silent update Recent Pending Requisitions section if changed
            const curReqs = document.getElementById('recentRequisitionsSection');
            const newReqs = doc.getElementById('recentRequisitionsSection');
            if (curReqs && newReqs) {
                const newHtml = newReqs.innerHTML.trim();
                if (lastRequisitionsHtml === null) {
                    lastRequisitionsHtml = curReqs.innerHTML.trim();
                }
                if (lastRequisitionsHtml !== newHtml) {
                    lastRequisitionsHtml = newHtml;
                    curReqs.innerHTML = newReqs.innerHTML;
                    if (window.lucide) lucide.createIcons();
                }
            }

            // 3. Silent update stat card numbers
            const countIds = ['pendingRequisitionsCountVal', 'pendingItemEntryCountVal', 'pendingServiceSraCountVal', 'lowStockCountVal'];
            countIds.forEach(id => {
                const curVal = document.getElementById(id);
                const newVal = doc.getElementById(id);
                if (curVal && newVal && curVal.textContent.trim() !== newVal.textContent.trim()) {
                    curVal.textContent = newVal.textContent.trim();
                }
            });
        } catch(e) {
            // silent catch
        }
    }

    // Auto silent refresh every 7 seconds
    setInterval(pollStoresDashboardSilently, 7000);
</script>
@endsection
