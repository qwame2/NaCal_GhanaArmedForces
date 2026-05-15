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
        <form action="{{ route('admin.inventory') }}" method="GET" style="display: flex; align-items: center; width: 100%; gap: 4px; flex-wrap: inherit;">
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

                        // If there was a specific tab active, we might need to re-trigger it
                        // But since we replace the WHOLE container including triggers,
                        // we need to make sure the triggers reflect the current state.
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

                            $isPartial = str_contains(strtolower($item->supplier_status), 'partial');
                            $cleanSupplier = $item->supplier_name;
                        @endphp
                        <tr class="inventory-row" data-is-partial="{{ $isPartial ? 'true' : 'false' }}">
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
        // Update triggers
        document.querySelectorAll('.tab-trigger').forEach(btn => btn.classList.remove('active'));
        document.getElementById('tab-' + tabId).classList.add('active');

        // Update content
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        document.getElementById('content-' + tabId).classList.add('active');
        
        // Re-init icons for dynamic content if needed
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
</script>
@endsection
