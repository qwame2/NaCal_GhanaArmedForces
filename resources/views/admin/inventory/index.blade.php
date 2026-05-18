@extends('layouts.admin')

@section('title', 'Inventory Oversight')

@section('content')
<style>
    /* Responsive Layout Overrides */
    @media (max-width: 1200px) {
        .filter-pill { flex-direction: column !important; height: auto !important; padding: 1.5rem !important; border-radius: 24px !important; margin: 0 1rem 2rem 1rem !important; gap: 1rem !important; }
        .filter-segment { border-right: none !important; border-bottom: 1px solid #f1f5f9 !important; padding: 1rem !important; width: 100% !important; flex: none !important; max-width: none !important; }
        .filter-actions { padding: 1rem 0 0 0 !important; width: 100% !important; justify-content: center !important; }
    }
    @media (max-width: 768px) {
        .page-header { flex-direction: column !important; align-items: flex-start !important; gap: 1.5rem !important; margin-bottom: 2rem !important; }
        .stats-grid { grid-template-columns: 1fr !important; gap: 1rem !important; }
        .timeline-wrapper { flex-direction: column !important; width: 100% !important; gap: 1rem !important; }
        .timeline-pill { width: 100% !important; min-width: 0 !important; }
        .tab-trigger { padding: 0.8rem 1rem !important; font-size: 0.7rem !important; gap: 8px !important; flex: 1 !important; justify-content: center !important; }
        .tab-trigger i { width: 14px !important; }
    }
</style>
<div class="animate-slide-up">
    <div class="page-header" style="margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 style="font-size: 2.25rem; font-weight: 900; letter-spacing: -0.04em; color: var(--text-heading); margin: 0;">Inventory <span style="color: var(--primary);">Overview</span></h2>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin-top: 0.5rem;">See all items that were received, issued, or returned.</p>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            @if($partialCount > 0)
            <div onclick="togglePartials()" id="partialFilterBtn" style="background: #fff1f2; border: 1px solid #fecaca; padding: 10px 20px; border-radius: 14px; box-shadow: var(--shadow-luxe); cursor: pointer; display: flex; align-items: center; gap: 10px; transition: 0.3s;">
                <div style="width: 8px; height: 8px; background: #ef4444; border-radius: 50%; animation: pulse 2s infinite;"></div>
                <span style="font-size: 0.75rem; font-weight: 800; color: #e11d48;">{{ $partialCount }} PARTIAL DELIVERIES PENDING</span>
                <i data-lucide="filter" style="width: 14px; color: #e11d48;"></i>
            </div>
            @endif
        </div>
    </div>

    <!-- Luxury Oversight Pill Bar -->
    <div class="filter-pill" style="margin: -1.5rem 2rem 2.5rem 2rem; background: white; padding: 8px; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.06); border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 8px; position: relative; z-index: 10;">
        <form action="{{ route('admin.inventory') }}" method="GET" onsubmit="event.preventDefault(); performLiveUpdate();" style="display: flex; align-items: center; width: 100%; gap: 4px; flex-wrap: inherit;">
            <!-- Segment 1: Search -->
            <div class="filter-segment" style="flex: 1.2; max-width: 380px; min-width: 0; position: relative; display: flex; align-items: center; padding: 0 1.5rem; border-right: 1px solid #f1f5f9;">
                <i data-lucide="search" style="width: 18px; color: #94a3b8; margin-right: 12px;"></i>
                <input type="text" name="search" id="admin-search-input" value="{{ request('search') }}" placeholder="Find items or batches..." 
                    style="width: 100%; border: none; font-size: 0.95rem; font-weight: 600; color: #1e293b; outline: none; background: transparent;"
                    oninput="debounceSearch()">
            </div>

            <script>
                let searchTimeout;
                function debounceSearch() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        performLiveUpdate();
                    }, 400); // 400ms debounce
                }

                function performLiveUpdate() {
                    const searchInput = document.getElementById('admin-search-input');
                    const search = searchInput.value;
                    const cat = document.getElementById('category-input').value;
                    const from = document.getElementsByName('date_from')[0].value;
                    const to = document.getElementsByName('date_to')[0].value;
                    
                    const perPage = document.getElementsByName('per_page')[0].value;
                    
                    const container = document.getElementById('oversight-container');
                    if (!container) return;

                    // Add loading state
                    container.style.opacity = '0.5';
                    container.style.pointerEvents = 'none';

                    const url = new URL(window.location.href);
                    url.searchParams.set('search', search);
                    url.searchParams.set('category', cat);
                    url.searchParams.set('date_from', from);
                    url.searchParams.set('date_to', to);
                    url.searchParams.set('per_page', perPage);

                    fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        // Replace container content
                        const newContainer = doc.getElementById('oversight-container');
                        if (newContainer) {
                            container.innerHTML = newContainer.innerHTML;
                        }

                        // Replace stats grid
                        const oldStats = document.querySelector('.stats-grid');
                        const newStats = doc.querySelector('.stats-grid');
                        if (oldStats && newStats) {
                            oldStats.innerHTML = newStats.innerHTML;
                        }
                        
                        // Re-initialize Lucide icons
                        if (window.lucide) lucide.createIcons();
                        
                        container.style.opacity = '1';
                        container.style.pointerEvents = 'auto';
                        
                        // Push state to URL
                        window.history.pushState({}, '', url);

                        // Restore and re-trigger active tab state
                        const currentTab = new URL(window.location.href).searchParams.get('tab') || 'received';
                        switchTab(currentTab);
                    })
                    .catch(error => {
                        console.error('Live Update Error:', error);
                        container.style.opacity = '1';
                        container.style.pointerEvents = 'auto';
                    });
                }



                // Update date inputs to trigger live update
                document.querySelectorAll('input[type="date"]').forEach(el => {
                    el.onchange = performLiveUpdate;
                });
            </script>

            <!-- Segment 2: Custom Luxury Category Dropdown -->
            <div style="flex: 1; position: relative; display: flex; align-items: center; padding: 0 1.5rem; border-right: 1px solid #f1f5f9; cursor: pointer; height: 50px;" id="cat-trigger" onclick="toggleCatMenu(event)">
                <i data-lucide="layers" style="width: 18px; color: #94a3b8; margin-right: 12px;"></i>
                <div style="flex: 1; font-size: 0.85rem; font-weight: 800; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <span id="selected-cat-label">{{ $ledgeMap[request('category')] ?? 'All Categories' }}</span>
                </div>
                <i data-lucide="chevron-down" id="cat-chevron" style="width: 14px; color: #cbd5e1; transition: 0.3s;"></i>
                
                <!-- Hidden Input for Form -->
                <input type="hidden" name="category" id="category-input" value="{{ request('category') }}">

                <!-- Luxury Dropdown Menu -->
                <div id="cat-menu" style="display: none; position: absolute; top: calc(100% + 15px); left: 0; width: 280px; background: white; border-radius: 18px; box-shadow: 0 20px 50px rgba(0,0,0,0.15); border: 1px solid #f1f5f9; padding: 8px; z-index: 100; animation: slideDown 0.3s ease;">
                    <div style="padding: 10px 15px; font-size: 0.7rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; border-bottom: 1px solid #f8fafc; margin-bottom: 5px;">Select Category</div>
                    
                    <div onclick="selectCat(event, '', 'All Categories')" class="cat-opt" style="padding: 12px 15px; border-radius: 12px; font-size: 0.85rem; font-weight: 700; color: #64748b; transition: 0.2s;">
                        All Categories
                    </div>
                    
                    @foreach($ledgeMap as $code => $name)
                    <div onclick="selectCat(event, '{{ $code }}', '{{ $name }}')" class="cat-opt" style="padding: 12px 15px; border-radius: 12px; font-size: 0.85rem; font-weight: 700; color: #1e293b; transition: 0.2s; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 24px; height: 24px; background: #f8fafc; color: var(--primary); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.65rem;">{{ $code }}</span>
                        {{ $name }}
                    </div>
                    @endforeach
                </div>
            </div>

            <style>
                .cat-opt:hover { background: #f8fafc; color: var(--primary) !important; padding-left: 20px !important; }
                @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
            </style>

            <script>
                function toggleCatMenu(e) {
                    e.stopPropagation();
                    const menu = document.getElementById('cat-menu');
                    const chevron = document.getElementById('cat-chevron');
                    const isVisible = menu.style.display === 'block';
                    
                    // Close all other menus if any
                    menu.style.display = isVisible ? 'none' : 'block';
                    chevron.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
                }

                function selectCat(e, value, label) {
                    if (e) e.stopPropagation();
                    document.getElementById('category-input').value = value;
                    document.getElementById('selected-cat-label').innerText = label;
                    document.getElementById('cat-menu').style.display = 'none';
                    document.getElementById('cat-chevron').style.transform = 'rotate(0deg)';
                    
                    // Trigger Live Update
                    if (typeof performLiveUpdate === 'function') {
                        performLiveUpdate();
                    }
                }

                window.onclick = function() {
                    document.getElementById('cat-menu').style.display = 'none';
                    document.getElementById('cat-chevron').style.transform = 'rotate(0deg)';
                };
            </script>

            <!-- Segment 3: Luxury Timeline Selector -->
            <div class="filter-segment timeline-wrapper" style="flex: 1.2; max-width: 320px; min-width: 250px; display: flex; align-items: center; padding: 0 1.5rem; gap: 15px;">
                <i data-lucide="calendar-range" style="width: 18px; color: #94a3b8;"></i>
                <div style="display: flex; align-items: center; gap: 6px; flex: 1;">
                    <!-- From Pill -->
                    <div class="timeline-pill" style="background: #f8fafc; border: 1px solid #f1f5f9; padding: 4px 12px; border-radius: 10px; display: flex; flex-direction: column; min-width: 110px; transition: 0.3s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='white'" onmouseout="this.style.borderColor='#f1f5f9'; this.style.background='#f8fafc'">
                        <span style="font-size: 0.6rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: -2px;">Date From</span>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" oninput="performLiveUpdate()"
                            style="border: none; font-size: 0.8rem; font-weight: 800; color: #1e293b; background: transparent; outline: none; width: 100%; cursor: pointer;">
                    </div>

                    <div style="color: #cbd5e1; font-weight: 900; padding: 0 4px;">&rarr;</div>

                    <!-- To Pill -->
                    <div class="timeline-pill" style="background: #f8fafc; border: 1px solid #f1f5f9; padding: 4px 12px; border-radius: 10px; display: flex; flex-direction: column; min-width: 110px; transition: 0.3s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='white'" onmouseout="this.style.borderColor='#f1f5f9'; this.style.background='#f8fafc'">
                        <span style="font-size: 0.6rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: -2px;">Date To</span>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" oninput="performLiveUpdate()"
                            style="border: none; font-size: 0.8rem; font-weight: 800; color: #1e293b; background: transparent; outline: none; width: 100%; cursor: pointer;">
                    </div>
                </div>
            </div>

            <style>
                /* Remove default browser date icon to keep it clean */
                input[type="date"]::-webkit-calendar-picker-indicator {
                    opacity: 0.4;
                    cursor: pointer;
                    filter: invert(0.5);
                }
                input[type="date"]::-webkit-inner-spin-button, 
                input[type="date"]::-webkit-clear-button {
                    display: none;
                }
            </style>

            <!-- Segment 4: Actions -->
            <div class="filter-actions" style="padding-left: 1rem; display: flex; gap: 8px;">
                <button type="submit" class="audit-btn" style="background: var(--primary); color: white; border: none; padding: 12px 24px; border-radius: 14px; font-weight: 800; font-size: 0.85rem; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 8px; box-shadow: 0 8px 16px rgba(79, 70, 229, 0.2);">
                    <i data-lucide="filter" style="width: 16px;"></i> Filter
                </button>
                <a href="{{ route('admin.inventory') }}" style="width: 44px; height: 44px; background: #f8fafc; color: #94a3b8; border: 1px solid #f1f5f9; border-radius: 12px; display: flex; align-items: center; justify-content: center; transition: 0.3s; margin-right: 8px;" title="Reset Ledger">
                    <i data-lucide="rotate-ccw" style="width: 18px;"></i>
                </a>

                <!-- Per Page Dropdown -->
                <div style="display: flex; align-items: center; gap: 10px; border-left: 1px solid #f1f5f9; padding-left: 1.5rem;">
                    <div class="per-page-capsule" style="display: flex; align-items: center; gap: 6px; background: white; padding: 6px 14px; border-radius: 14px; border: 1.5px solid #eef2ff; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.04); transition: all 0.3s ease;">
                        <div style="width: 24px; height: 24px; background: #eef2ff; color: #4f46e5; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="layers" style="width: 14px;"></i>
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-size: 0.55rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; line-height: 1;">Show</span>
                            <select name="per_page" onchange="performLiveUpdate()" 
                                style="background: transparent; border: none; font-size: 0.85rem; font-weight: 900; color: #1e293b; outline: none; cursor: pointer; padding: 0 18px 0 0; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%234f46e5%22%20stroke-width%3D%223%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right center; background-size: 10px;">
                                @foreach([15, 30, 50, 100] as $cp)
                                    <option value="{{ $cp }}" {{ request('per_page', 15) == $cp ? 'selected' : '' }}>{{ $cp }} Records</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Oversight Tabs -->
    <div class="glass-card" id="oversight-container" style="padding: 0; border-radius: 24px; overflow: hidden; background: white; border: 1px solid rgba(0,0,0,0.02); box-shadow: 0 20px 50px rgba(0,0,0,0.03);">
        <div style="padding: 1rem 2rem; background: #fff; border-bottom: 1px solid #f1f5f9; display: flex; gap: 2.5rem;">
            <button class="tab-trigger active" onclick="switchTab('received')" id="tab-received">
                <i data-lucide="download"></i>
                Received Items
            </button>
            <button class="tab-trigger" onclick="switchTab('issued')" id="tab-issued">
                <i data-lucide="upload"></i>
                Issuance Logs
            </button>
            <button class="tab-trigger" onclick="switchTab('returned')" id="tab-returned">
                <i data-lucide="rotate-ccw"></i>
                Returns Registry
            </button>
        </div>

        <!-- Received Items Tab -->
        <div id="content-received" class="tab-content active">
            <div style="padding: 1.5rem; overflow-x: auto;">
                <table class="audit-table" style="min-width: 1400px;">
                    <thead>
                        <tr>
                            <th>Entry / Arrival</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            <th>Donor</th>
                            <th>Status</th>
                            <th>Received Qty</th>
                            <th>Stock Balance</th>
                            <th>Variance</th>
                            <th>System Health</th>
                            <th>Item Health</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receivedItems as $item)
                        @php
                            $agg = $itemAggregates[$item->description] ?? null;
                            $totalQty = $agg ? (float)$agg->total_received_qty : 0;
                            $totalStock = $agg ? (float)$agg->total_available : 0;
                            $percentage = ($totalQty > 0) ? ($totalStock / $totalQty) * 100 : 0;

                            $hStatus = 'IN STOCK';
                            $hColor = '#10b981';
                            if ($totalStock <= 0 || $totalQty <= 0) {
                                $hStatus = 'OUT OF STOCK';
                                $hColor = '#ef4444';
                            } elseif ($percentage <= 50) {
                                $hStatus = 'LOW STOCK';
                                $hColor = '#ef4444';
                            } elseif ($percentage <= 70) {
                                $hStatus = 'WARNING';
                                $hColor = '#f59e0b';
                            }

                            $isPartial = str_contains(strtolower($item->supplier_status), 'partial') || str_contains(strtolower($item->supplier_name), '[partial deliv');
                            $cleanSupplier = $item->supplier_name;
                        @endphp
                        <tr class="item-row inventory-row" data-item-id="{{ $item->id }}" data-batch-id="{{ $item->batch_id }}" data-is-partial="{{ $isPartial ? 'true' : 'false' }}">
                            <td>
                                <div style="font-weight: 800; color: #0f172a;">{{ \Carbon\Carbon::parse($item->entry_date)->format('d/m/y') }}</div>
                                <div style="font-size: 0.75rem; color: var(--primary); font-weight: 700;">Arr: {{ $item->arrival_date ? \Carbon\Carbon::parse($item->arrival_date)->format('d/m/y') : 'N/A' }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #1e293b;">{{ $item->description }}</div>
                                <div style="font-size: 0.7rem; color: #64748b; font-weight: 800;">BATCH #{{ $item->batch_id }}</div>
                            </td>
                            <td>
                                <span class="category-tag">
                                    {{ $ledgeMap[$item->ledge_category] ?? "Category " . $item->ledge_category }}
                                </span>
                            </td>
                            <td><div style="font-weight: 600; color: #475569;">{{ $cleanSupplier ?: '-' }}</div></td>
                            <td><div style="font-weight: 600; color: #475569;">{{ $item->donor_name ?: '-' }}</div></td>
                            <td>
                                @if($isPartial)
                                    <span style="font-size: 0.65rem; font-weight: 900; color: white; background: #ef4444; padding: 4px 8px; border-radius: 6px; text-transform: uppercase;">PARTIAL</span>
                                @else
                                    <span style="font-size: 0.65rem; font-weight: 900; color: white; background: #10b981; padding: 4px 8px; border-radius: 6px; text-transform: uppercase;">FULL</span>
                                @endif
                            </td>
                            <td style="font-weight: 800; color: #0f172a;">{{ $item->qty }}</td>
                            <td style="font-weight: 800; color: #0f172a;">{{ $item->stock_balance }}</td>
                            <td>
                                <span style="font-weight: 800; color: {{ (float)$item->variance < 0 ? '#ef4444' : ((float)$item->variance > 0 ? '#10b981' : '#94a3b8') }};">
                                    {{ (float)$item->variance > 0 ? '+' : '' }}{{ $item->variance }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="font-size: 0.6rem; font-weight: 900; color: white; background: {{ $hColor }}; padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">{{ $hStatus }}</span>
                                        <span style="font-size: 0.75rem; font-weight: 800; color: {{ $hColor }};">{{ round($percentage) }}%</span>
                                    </div>
                                    <div style="font-size: 0.7rem; font-weight: 800; color: #64748b;">{{ number_format($totalQty) }} Avail</div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $threshold = \App\Models\Setting::getItemThreshold($item->description, $item->ledge_category);
                                    $isItemLow = $totalStock <= $threshold;
                                    $itemHealthColor = $isItemLow ? '#ef4444' : '#10b981';
                                @endphp
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <span style="font-size: 0.6rem; font-weight: 900; color: white; background: {{ $itemHealthColor }}; padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">{{ $isItemLow ? 'LOW' : 'GOOD' }}</span>
                                    <i data-lucide="{{ $isItemLow ? 'alert-triangle' : 'check-circle' }}" style="width: 14px; color: {{ $itemHealthColor }};"></i>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" style="padding: 4rem; text-align: center; color: #94a3b8;">No inventory records discovered in the master ledger.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Module -->
            <div style="padding: 1.5rem 2rem; background: #fafcff; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 0.85rem; color: #64748b; font-weight: 700;">
                    Showing <span style="color: #0f172a; font-weight: 900;">{{ $receivedItems->firstItem() ?? 0 }}</span> to 
                    <span style="color: #0f172a; font-weight: 900;">{{ $receivedItems->lastItem() ?? 0 }}</span> of 
                    <span style="color: #0f172a; font-weight: 900;">{{ $receivedItems->total() }}</span> Records
                </div>
                <div class="custom-pagination">
                    @if ($receivedItems->onFirstPage())
                        <span class="page-btn disabled">Previous</span>
                    @else
                        <a href="{{ $receivedItems->appends(request()->query())->previousPageUrl() }}" class="page-btn">Previous</a>
                    @endif

                    @if ($receivedItems->hasMorePages())
                        <a href="{{ $receivedItems->appends(request()->query())->nextPageUrl() }}" class="page-btn">Next</a>
                    @else
                        <span class="page-btn disabled">Next</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Issued Items Tab -->
        <div id="content-issued" class="tab-content">
            <div style="padding: 1.5rem; overflow-x: auto;">
                <table class="audit-table" style="min-width: 1200px;">
                    <thead>
                        <tr>
                            <th>Issuance Date</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Beneficiary</th>
                            <th>Auth. Authority</th>
                            <th>Type</th>
                            <th>Qty Issued</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issuances as $item)
                        <tr>
                            <td>
                                <div style="font-weight: 800; color: #0f172a;">{{ \Carbon\Carbon::parse($item->issuance_date)->format('d/m/y') }}</div>
                                <div style="font-size: 0.7rem; color: #94a3b8; font-weight: 600;">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #1e293b;">{{ $item->description }}</div>
                                <span class="id-badge" style="font-size: 0.6rem;">#ISS-{{ str_pad($item->issuance_id, 5, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td>
                                <span class="category-tag">
                                    {{ $ledgeMap[$item->ledge_category] ?? "Category " . $item->ledge_category }}
                                </span>
                            </td>
                            <td><div style="font-weight: 600; color: #475569;">{{ $item->beneficiary }}</div></td>
                            <td><div style="font-weight: 600; color: #475569;">{{ $item->authority }}</div></td>
                            <td><span class="type-tag {{ strtolower($item->issuance_type) }}">{{ $item->issuance_type }}</span></td>
                            <td>
                                <div style="font-size: 1rem; font-weight: 900; color: var(--primary);">{{ $item->quantity }}</div>
                                <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700;">{{ $item->unit ?: 'Units' }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="padding: 4rem; text-align: center; color: #94a3b8;">No issuance records found in the audit trail.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Returned Items Tab -->
        <div id="content-returned" class="tab-content">
            <div style="padding: 2rem;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Return Date</th>
                            <th>Identity / Item</th>
                            <th>Beneficiary</th>
                            <th>Returned Qty</th>
                            <th>Remarks / documentation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returnedItems as $return)
                        <tr>
                            <td>
                                <div style="font-weight: 800; color: #0f172a;">{{ \Carbon\Carbon::parse($return->return_date)->format('d/m/y') }}</div>
                                <div style="font-size: 0.7rem; color: #94a3b8; font-weight: 600;">{{ \Carbon\Carbon::parse($return->created_at)->format('H:i') }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #1e293b;">{{ $return->issuedItem->description ?? 'N/A' }}</div>
                                <div style="display: flex; gap: 6px; align-items: center; margin-top: 4px;">
                                    <span class="id-badge" style="font-size: 0.6rem;">#RET-{{ str_pad($return->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    <span style="font-size: 0.7rem; color: #64748b; font-weight: 600;">From Issuance #{{ $return->issuedItem->issuance_id ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #475569;">{{ $return->issuedItem->issuance->beneficiary ?? 'N/A' }}</div>
                                <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">{{ $return->issuedItem->issuance->authority ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div style="font-size: 1.1rem; font-weight: 900; color: var(--primary);">{{ $return->returned_qty }}</div>
                                <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Units Returned</div>
                            </td>
                            <td style="max-width: 300px;">
                                <div style="background: #f8fafc; padding: 10px; border-radius: 10px; border-left: 3px solid #e2e8f0;">
                                    <div style="font-size: 0.8rem; color: #64748b; font-style: italic; line-height: 1.4;">"{{ $return->remarks ?: 'No remarks recorded' }}"</div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="padding: 4rem; text-align: center; color: #94a3b8;">No return records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .tab-trigger {
        background: transparent;
        border: none;
        padding: 1rem 0;
        font-size: 0.9rem;
        font-weight: 800;
        color: #94a3b8;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        transition: 0.3s;
    }
    .tab-trigger i { width: 18px; opacity: 0.6; }
    .tab-trigger:hover { color: #475569; }
    .tab-trigger.active { color: var(--primary); }
    .tab-trigger.active::after {
        content: '';
        position: absolute;
        bottom: -1.5rem;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--primary);
        border-radius: 3px 3px 0 0;
    }

    .tab-content { display: none; }
    .tab-content.active { display: block; animation: fadeIn 0.4s ease; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .audit-table { width: 100%; border-collapse: collapse; text-align: left; }
    .audit-table th { 
        padding: 1.25rem 1.5rem; 
        font-size: 0.7rem; 
        font-weight: 900; 
        color: #94a3b8; 
        text-transform: uppercase; 
        letter-spacing: 0.1em;
        border-bottom: 1px solid #f1f5f9;
    }
    .audit-table td { padding: 1.5rem; border-bottom: 1px solid #f8fafc; vertical-align: top; }
    .audit-table tr:hover { background: #fcfdfe; }

    .id-badge { background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px; font-weight: 800; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; }
    .category-tag { background: rgba(79, 70, 229, 0.05); color: var(--primary); padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 800; }
    
    .type-tag { padding: 4px 10px; border-radius: 20px; font-size: 0.65rem; font-weight: 900; text-transform: uppercase; }
    .type-tag.permanent { background: #eef2ff; color: #4f46e5; }
    .type-tag.temporary { background: #fffbeb; color: #f59e0b; }
    .type-tag.consumption { background: #ecfdf5; color: #10b981; }

    /* Custom Pagination Styling */
    .custom-pagination {
        display: flex;
        gap: 8px;
    }
    .page-btn {
        padding: 0.5rem 1.25rem;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        color: var(--primary);
        font-weight: 800;
        font-size: 0.8rem;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .page-btn:hover:not(.disabled) {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }
    .page-btn.disabled {
        background: #f8fafc;
        color: #94a3b8;
        border-color: #e2e8f0;
        cursor: not-allowed;
        box-shadow: none;
    }

    /* Modal Architecture */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(2, 6, 23, 0.7);
        backdrop-filter: blur(12px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 2rem;
    }

    .modal-content {
        width: 100%;
        max-width: 800px;
        max-height: 90vh;
        background: #ffffff !important;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4) !important;
        border-radius: 28px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .custom-premium-select {
        width: 100%;
        padding: 0.85rem 2.5rem 0.85rem 1rem !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        background-color: #f8fafc !important;
        background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E') !important;
        background-repeat: no-repeat !important;
        background-position: right 1rem center !important;
        background-size: 16px !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02) !important;
    }
    .custom-premium-select:hover {
        border-color: #cbd5e1 !important;
        background-color: #f1f5f9 !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.04) !important;
    }
    .custom-premium-select:focus {
        outline: none !important;
        border-color: #4f46e5 !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1), 0 4px 10px rgba(0, 0, 0, 0.05) !important;
    }
    
    [data-theme='dark'] .custom-premium-select {
        border-color: #334155 !important;
        color: #f8fafc !important;
        background-color: #1e293b !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
        background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2394a3b8%22%20stroke-width%3D%222.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E') !important;
    }
    [data-theme='dark'] .custom-premium-select:hover {
        border-color: #475569 !important;
        background-color: #334155 !important;
    }
    [data-theme='dark'] .custom-premium-select:focus {
        border-color: #6366f1 !important;
        background-color: #0f172a !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2), 0 4px 10px rgba(0, 0, 0, 0.3) !important;
    }

    .modal-body {
        padding: 2.5rem;
        overflow-y: auto;
        flex: 1;
        scrollbar-width: none;
    }

    .modal-body::-webkit-scrollbar {
        display: none;
    }
</style>

<script>
    let partialsOnly = false;
    function togglePartials() {
        partialsOnly = !partialsOnly;
        const btn = document.getElementById('partialFilterBtn');
        const rows = document.querySelectorAll('.inventory-row');
        
        if (partialsOnly) {
            btn.style.background = '#ef4444';
            btn.style.borderColor = '#dc2626';
            btn.querySelector('span').style.color = '#fff';
            btn.querySelector('i').style.color = '#fff';
            
            rows.forEach(row => {
                if (row.getAttribute('data-is-partial') !== 'true') {
                    row.style.display = 'none';
                }
            });
        } else {
            btn.style.background = '#fff1f2';
            btn.style.borderColor = '#fecaca';
            btn.querySelector('span').style.color = '#e11d48';
            btn.querySelector('i').style.color = '#e11d48';
            
            rows.forEach(row => row.style.display = '');
        }
    }

    function switchTab(tabId) {
        document.querySelectorAll('.tab-trigger').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        const activeBtn = document.getElementById(`tab-${tabId}`);
        const activeContent = document.getElementById(`content-${tabId}`);
        
        if (activeBtn) activeBtn.classList.add('active');
        if (activeContent) activeContent.classList.add('active');
        
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
    }

    window.addEventListener('DOMContentLoaded', () => {
        const currentTab = new URL(window.location.href).searchParams.get('tab') || 'received';
        switchTab(currentTab);
    });
</script>

<!-- Edit Batch Modal -->
<div id="editBatchModal" class="modal-backdrop">
    <div class="modal-content glass-card animate-scale-up" style="max-width: 1000px; width: 95%; padding: 0; overflow: hidden; border: none; background: #ffffff; display: flex; flex-direction: column; max-height: 90vh;">
        <!-- Premium Modal Header -->
        <div style="padding: 1.5rem 2.5rem; background: linear-gradient(to right, #4f46e5, #6366f1); display: flex; justify-content: space-between; align-items: center; color: white;">
            <div>
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px;">
                    <div style="background: rgba(255, 255, 255, 0.2); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    </div>
                    <h3 id="editModalTitle" style="font-size: 1.25rem; font-weight: 800; margin: 0; color: white; letter-spacing: -0.02em;">Modify Record</h3>
                </div>
                <p id="editModalSubtitle" style="color: rgba(255, 255, 255, 0.8); font-size: 0.85rem; font-weight: 600; margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">#BATCH-0000</p>
            </div>
            <button onclick="closeEditBatchModal()" style="background: rgba(255, 255, 255, 0.1); border: none; color: white; width: 40px; height: 40px; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s;" onmouseover="this.style.background='rgba(255, 255, 255, 0.2)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <form id="editBatchForm" onsubmit="event.preventDefault(); submitEditBatch();" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden;">
            <div class="modal-body" id="editModalBody" style="padding: 2.5rem; max-height: 75vh; overflow-y: auto; flex: 1; min-height: 0;">
                <div class="loader-container" id="editModalLoader">
                    <div class="loader"></div>
                    <p style="font-weight: 700; color: #475569; margin-top: 1rem;">Decrypting Batch Data...</p>
                </div>
                
                <div id="editModalContent" style="display: none;">
                    <!-- Metadata Section -->
                    <div style="margin-bottom: 2.5rem;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 1.5rem;">
                            <span style="font-size: 0.75rem; font-weight: 900; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.1em;">Record Details</span>
                            <div style="flex: 1; height: 1px; background: #f1f5f9;"></div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                            <div class="input-group">
                                <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Arrival Date</label>
                                <div style="position: relative;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
                                    <input type="date" name="arrival_date" id="editArrivalDate" required style="width: 100%; padding: 0.85rem 1rem 0.85rem 2.5rem; border: 1.5px solid #e2e8f0; border-radius: 12px; font-weight: 700; color: #1e293b; background: #f8fafc;">
                                </div>
                            </div>
                            
                            <div class="input-group">
                                <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Asset Category</label>
                                <select name="ledge_category" id="editCategory" required class="custom-premium-select">
                                    @foreach($ledgeMap as $code => $name)
                                    <option value="{{ $code }}">CAT {{ $code }} | {{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="input-group">
                                <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Sourcing Method</label>
                                <select name="acquisition_status" id="editAcquisitionStatus" required onchange="toggleEditSourceFields()" class="custom-premium-select">
                                    <option value="Full Delivery">Full Delivery</option>
                                    <option value="Partial Delivery">Partial Delivery</option>
                                    <option value="Donor">Donor Acquisition</option>
                                </select>
                            </div>
                            
                            <div id="editSupplierField" class="input-group">
                                <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Origin Entity (Supplier)</label>
                                <select name="supplier_name" id="editSupplierName" style="width: 100%;" class="select2-edit">
                                    <option value="">Select Origin...</option>
                                    @foreach($allSuppliers as $supplier)
                                    <option value="{{ $supplier }}">{{ $supplier }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div id="editDonorField" class="input-group" style="display: none;">
                                <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Origin Entity (Donor)</label>
                                <select name="donor_name" id="editDonorName" style="width: 100%;" class="select2-edit">
                                    <option value="">Select Donor...</option>
                                    @foreach($allDonors as $donor)
                                    <option value="{{ $donor }}">{{ $donor }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Batch Contents Section -->
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                            <div style="display: flex; align-items: center; gap: 8px; flex: 1;">
                                <span style="font-size: 0.75rem; font-weight: 900; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.1em;">Batch Contents</span>
                                <div style="flex: 1; height: 1px; background: #f1f5f9; margin-right: 1.5rem;"></div>
                            </div>
                            <span id="editItemsCountLabel" style="background: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800;">0 Items Identified</span>
                        </div>
                        
                        <div id="editItemsList" style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                            <!-- Items injected here via JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 1.5rem 2.5rem; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 1rem;">
                <button type="button" onclick="closeEditBatchModal()" style="padding: 0.85rem 2rem; border-radius: 12px; font-weight: 800; font-size: 0.9rem; background: #ffffff; border: 1.5px solid #e2e8f0; color: #475569; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg> Discard
                </button>
                <button type="submit" id="saveEditBtn" style="padding: 0.85rem 2.5rem; border-radius: 12px; font-weight: 800; font-size: 0.9rem; background: #4f46e5; border: none; color: white; cursor: pointer; display: flex; align-items: center; gap: 10px; box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2); transition: 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 15px 30px rgba(79, 70, 229, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 20px rgba(79, 70, 229, 0.2)'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg> Commit Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Continue Delivery Modal -->
<div id="continueDeliveryModal" class="modal-backdrop">
    <div class="modal-content glass-card animate-scale-up" style="max-width: 800px; width: 95%;">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1.5rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.25rem;">Resolve Partial Delivery</h3>
                <p id="continueModalSubtitle" style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">#BATCH-0000</p>
            </div>
            <button onclick="closeContinueDeliveryModal()" class="btn-icon danger" title="Close">
                <i data-lucide="x" style="width: 18px;"></i>
            </button>
        </div>

        <div class="modal-body" style="padding: 1.5rem;">
            <div id="continueModalLoader" class="loader-container">
                <div class="loader"></div>
                <p>Loading remainder data...</p>
            </div>
            
            <div id="continueModalContent" style="display: none;">
                <div style="background: rgba(16, 185, 129, 0.05); border: 1px dashed rgba(16, 185, 129, 0.3); border-radius: 16px; padding: 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 12px;">
                    <i data-lucide="truck" style="color: #10b981; width: 24px;"></i>
                    <div style="font-size: 0.85rem; color: #1e293b; line-height: 1.5;">
                        <span style="font-weight: 800; display: block; color: #10b981; text-transform: uppercase; font-size: 0.7rem; margin-bottom: 2px;">Protocol: Remainder Entry</span>
                        Update the quantities for the remaining items received in this shipment.
                    </div>
                </div>

                <div id="continueItemsList" style="display: flex; flex-direction: column; gap: 1rem;"></div>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border-color); padding: 1.25rem 2rem; display: flex; justify-content: flex-end; gap: 1rem; background: var(--bg-card); border-radius: 0 0 28px 28px;">
            <button type="button" onclick="closeContinueDeliveryModal()" style="padding: 0.85rem 1.5rem; border-radius: 12px; font-weight: 800; font-size: 0.95rem; background: transparent; border: 1px solid var(--border-color); color: var(--text-main); cursor: pointer;">Cancel</button>
            <button type="button" id="submitContinueBtn" onclick="submitContinueDelivery()" style="padding: 0.85rem 1.5rem; border-radius: 12px; font-weight: 800; font-size: 0.95rem; background: #10b981; border: none; color: white; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.2);">
                <i data-lucide="check-circle" style="width: 18px;"></i> Complete Delivery
            </button>
        </div>
    </div>
</div>

<script>
let currentEditBatchId = null;
let currentContinueBatchId = null;

function openEditBatchModal(batchId) {
    currentEditBatchId = batchId;
    const modal = document.getElementById('editBatchModal');
    const loader = document.getElementById('editModalLoader');
    const content = document.getElementById('editModalContent');
    const title = document.getElementById('editModalSubtitle');
    
    title.innerText = `#BATCH-${batchId.toString().padStart(4, '0')}`;
    modal.style.display = 'flex';
    loader.style.display = 'flex';
    content.style.display = 'none';

    $('.select2-edit').select2({
        dropdownParent: $('#editBatchModal'),
        tags: true,
        width: '100%'
    });

    fetch(`{{ url('/received-items') }}/${batchId}?json=1`)
        .then(res => res.json())
        .then(data => {
            const batch = data.batch;
            document.getElementById('editArrivalDate').value = batch.arrival_date ? batch.arrival_date.split(' ')[0] : '';
            document.getElementById('editCategory').value = batch.ledge_category;
            document.getElementById('editAcquisitionStatus').value = batch.supplier_status || 'Full Delivery';
            
            if (batch.supplier_status === 'Donor') {
                $('#editDonorName').val(batch.donor_name).trigger('change');
            } else {
                $('#editSupplierName').val(batch.supplier_name).trigger('change');
            }
            
            toggleEditSourceFields();

            const itemsList = document.getElementById('editItemsList');
            itemsList.innerHTML = '';
            document.getElementById('editItemsCountLabel').innerText = `${batch.items.length} Items`;

            batch.items.forEach((item, index) => {
                const itemHtml = `
                    <div class="edit-item-card" data-id="${item.id}" style="background: #ffffff; padding: 1.5rem; border: 1.5px solid #f1f5f9; border-radius: 16px; transition: 0.3s; position: relative;">
                        <input type="hidden" class="item-id" value="${item.id}">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 1.25rem;">
                            <div style="background: #eff6ff; color: #3b82f6; width: 24px; height: 24px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 900;">${index + 1}</div>
                            <input type="text" class="item-description" value="${item.description}" placeholder="Asset Description" style="flex: 1; border: none; background: transparent; font-size: 0.95rem; font-weight: 800; color: #1e293b; outline: none; padding: 4px 0; border-bottom: 2px solid transparent; transition: 0.3s;" onfocus="this.style.borderBottomColor='#4f46e5'">
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                            <div>
                                <label style="display: block; font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Unit Measure</label>
                                <input type="text" class="item-unit" value="${item.unit}" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 0.85rem; font-weight: 700; color: #475569;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Qty Received</label>
                                <input type="number" class="item-qty" value="${item.qty}" oninput="recalcEditVariance(this)" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 0.85rem; font-weight: 900; color: #1e293b;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Ledger Balance</label>
                                <input type="number" class="item-stock-balance" value="${item.stock_balance}" oninput="recalcEditVariance(this)" style="width: 100%; padding: 0.75rem; border: 1.5px solid #4f46e5; border-radius: 10px; font-size: 0.85rem; font-weight: 900; color: #4f46e5; background: #f5f3ff;">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 100px 1fr; gap: 1rem; align-items: flex-end;">
                            <div>
                                <label style="display: block; font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Variance</label>
                                <input type="number" class="item-variance" value="${item.variance}" readonly style="width: 100%; padding: 0.75rem; border: none; background: #f8fafc; border-radius: 10px; font-size: 0.85rem; font-weight: 900; color: ${item.variance < 0 ? '#ef4444' : '#10b981'}; text-align: center;">
                            </div>
                            <div style="position: relative;">
                                <label style="display: block; font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Registry Remarks</label>
                                <textarea class="item-remarks" style="width: 100%; height: 42px; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 10px; resize: none; font-size: 0.8rem; font-weight: 600; color: #64748b; background: #f8fafc;">${item.remarks || ''}</textarea>
                            </div>
                        </div>
                    </div>
                `;
                itemsList.insertAdjacentHTML('beforeend', itemHtml);
            });

            loader.style.display = 'none';
            content.style.display = 'block';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
}

function closeEditBatchModal() {
    document.getElementById('editBatchModal').style.display = 'none';
}

function toggleEditSourceFields() {
    const status = document.getElementById('editAcquisitionStatus').value;
    document.getElementById('editSupplierField').style.display = status === 'Donor' ? 'none' : 'block';
    document.getElementById('editDonorField').style.display = status === 'Donor' ? 'block' : 'none';
}

function recalcEditVariance(input) {
    const row = input.closest('.edit-item-card');
    const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
    const stock = parseFloat(row.querySelector('.item-stock-balance').value) || 0;
    row.querySelector('.item-variance').value = stock - qty;
}

function submitEditBatch() {
    const saveBtn = document.getElementById('saveEditBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i data-lucide="loader" class="animate-spin"></i> Processing...';
    if (typeof lucide !== 'undefined') lucide.createIcons();

    const items = [];
    document.querySelectorAll('.edit-item-card').forEach(row => {
        items.push({
            id: row.querySelector('.item-id').value,
            description: row.querySelector('.item-description').value,
            unit: row.querySelector('.item-unit').value,
            qty: row.querySelector('.item-qty').value,
            stock_balance: row.querySelector('.item-stock-balance').value,
            variance: row.querySelector('.item-variance').value,
            remarks: row.querySelector('.item-remarks').value
        });
    });

    const payload = {
        arrival_date: document.getElementById('editArrivalDate').value,
        ledge_category: document.getElementById('editCategory').value,
        acquisition_type: document.getElementById('editAcquisitionStatus').value === 'Donor' ? 'Donor' : 'Supplier',
        supplier_name: $('#editSupplierName').val(),
        supplier_status: document.getElementById('editAcquisitionStatus').value,
        donor_name: $('#editDonorName').val(),
        items: items,
        _token: '{{ csrf_token() }}'
    };

    fetch(`{{ url('/received-items') }}/${currentEditBatchId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Updated', 'Inventory record has been successfully modified.', 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i data-lucide="save"></i> Save Changes';
        }
    });
}

function continueDelivery(batchId) {
    currentContinueBatchId = batchId;
    const modal = document.getElementById('continueDeliveryModal');
    const loader = document.getElementById('continueModalLoader');
    const content = document.getElementById('continueModalContent');
    
    document.getElementById('continueModalSubtitle').innerText = `#BATCH-${batchId.toString().padStart(4, '0')}`;
    modal.style.display = 'flex';
    loader.style.display = 'flex';
    content.style.display = 'none';

    fetch(`{{ url('/received-items') }}/${batchId}?json=1`)
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('continueItemsList');
            list.innerHTML = '';
            data.batch.items.forEach(item => {
                const html = `
                    <div class="continue-item-row" data-id="${item.id}" style="background: var(--bg-main); padding: 1rem; border-radius: 12px; display: grid; grid-template-columns: 1fr 120px 120px; gap: 1rem; align-items: center;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 0.9rem;">${item.description}</div>
                        <div>
                            <label style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Remaining</label>
                            <input type="number" class="rem-qty" value="0" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 8px; font-weight: 800;">
                        </div>
                        <div style="text-align: right;">
                            <label style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">In Stock</label>
                            <span style="font-weight: 900; color: var(--primary);">${item.stock_balance}</span>
                        </div>
                    </div>
                `;
                list.insertAdjacentHTML('beforeend', html);
            });
            loader.style.display = 'none';
            content.style.display = 'block';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
}

function closeContinueDeliveryModal() {
    document.getElementById('continueDeliveryModal').style.display = 'none';
}

function submitContinueDelivery() {
    const btn = document.getElementById('submitContinueBtn');
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader" class="animate-spin"></i> Saving...';
    if (typeof lucide !== 'undefined') lucide.createIcons();

    const updates = [];
    document.querySelectorAll('.continue-item-row').forEach(row => {
        updates.push({
            id: row.getAttribute('data-id'),
            additional_qty: row.querySelector('.rem-qty').value
        });
    });

    fetch('{{ url("/api/inventory/receive-remainder") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ updates })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Inventory Updated', 'The remainder quantities have been added to stock.', 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="check-circle"></i> Complete Delivery';
        }
    });
}
</script>
@endsection
