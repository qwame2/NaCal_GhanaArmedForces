@extends('layouts.dashboard')

@section('content')
<div class="animate-slide-up">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">Inventory Log</span>
                <span style="color: var(--text-muted); font-size: 0.85rem;">Historical Records</span>
            </div>
            <h2 style="font-size: 1.75rem; font-weight: 900; color: var(--text-main);">Received <span style="color: var(--primary);">Items</span></h2>
            <p style="color: var(--text-muted); font-size: 0.95rem;">View all items received into the inventory system.</p>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button onclick="window.location.reload()" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--text-main);">
                <i data-lucide="refresh-cw" style="width: 18px;"></i>
                Refresh
            </button>
            <button onclick="window.print()" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--text-main);">
                <i data-lucide="printer" style="width: 18px;"></i>
                Export Report
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="glass-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
            <div style="width: 44px; height: 44px; background: rgba(99, 102, 241, 0.1); color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="package" style="width: 22px;"></i>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Total Batches</div>
                <div style="font-size: 1.3rem; font-weight: 800; color: var(--text-main);">{{ $totalReceived }}</div>
            </div>
        </div>
        <div class="glass-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
            <div style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="layers" style="width: 22px;"></i>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Total Items Recorded</div>
                <div style="font-size: 1.3rem; font-weight: 800; color: var(--text-main);">{{ $totalItemsCount }}</div>
            </div>
        </div>
        <div class="glass-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
            <div style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="trending-up" style="width: 22px;"></i>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Recent Batches</div>
                <div style="font-size: 1.3rem; font-weight: 800; color: var(--text-main);">{{ $recentReceived }}</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-card search-container-mobile" style="padding: 1.5rem; margin-bottom: 2rem;">
        <form id="filterForm" action="{{ route('receiveditems') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: flex-end;">
            <input type="hidden" name="ledge_category" id="ledgeCategoryInput" value="{{ request('ledge_category') }}">
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase;">Search Items</label>
                <div style="position: relative;">
                    <i data-lucide="search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 16px; color: var(--text-muted);"></i>
                    <input type="text" name="search" id="searchInput" value="{{ request('search') }}" placeholder="Description or Batch ID..." style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-main); color: var(--text-main);">
                </div>
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase;">Supplier</label>
                <input type="text" name="supplier" id="supplierInput" value="{{ request('supplier') }}" placeholder="Supplier name..." style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-main); color: var(--text-main);">
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase;">Date From</label>
                <input type="date" name="date_from" id="dateInput" value="{{ request('date_from') }}" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-main); color: var(--text-main);">
            </div>
            <div class="filter-buttons-mobile" style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn-primary" style="flex: 1; padding: 0.75rem; border-radius: 10px; border: none; background: var(--primary); color: white; cursor: pointer; font-weight: 600;">Filter</button>
                <a href="{{ route('receiveditems') }}" class="glass-card" style="padding: 0.75rem; border-radius: 10px; color: var(--text-main); display: flex; align-items: center; justify-content: center; width: 44px; text-decoration: none;">
                    <i data-lucide="x" style="width: 18px;"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Quick Ledge Filters Carousel -->
    <div class="category-carousel-wrapper" style="position: relative; margin-bottom: 3rem;">
        <!-- Left Nav Arrow -->
        <button type="button" id="prevLedge" style="position: absolute; left: -10px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; border-radius: 50%; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); display: flex; align-items: center; justify-content: center; z-index: 10; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.1); opacity: 0; transition: all 0.3s; pointer-events: none;">
            <i data-lucide="chevron-left" style="width: 18px;"></i>
        </button>

        <div class="quick-filters-container" id="ledgeScroll" style="display: flex; gap: 0.75rem; overflow-x: auto; padding: 0.5rem 0.25rem; white-space: nowrap; scroll-behavior: smooth;">
            <button type="button" class="quick-ledge-btn {{ !request('ledge_category') ? 'active' : '' }}" data-ledge="" style="padding: 0.65rem 1.4rem; border-radius: 999px; border: 1.5px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-weight: 800; cursor: pointer; transition: all 0.3s; font-size: 0.85rem;">All Groups</button>
            @foreach($ledgeMap as $code => $name)
            <button type="button" class="quick-ledge-btn {{ request('ledge_category') == $code ? 'active' : '' }}" data-ledge="{{ $code }}" style="padding: 0.65rem 1.4rem; border-radius: 999px; border: 1.5px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-weight: 700; cursor: pointer; transition: all 0.3s; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                <span style="width: 8px; height: 8px; background: #94a3b8; border-radius: 50%;"></span> Ledge {{ $code }} ({{ $name }})
            </button>
            @endforeach
        </div>

        <!-- Right Nav Arrow -->
        <button type="button" id="nextLedge" style="position: absolute; right: -10px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; border-radius: 50%; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); display: flex; align-items: center; justify-content: center; z-index: 10; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.3s; margin-bottom: 2rem;">
            <i data-lucide="chevron-right" style="width: 18px;"></i>
        </button>
    </div>

    <style>
        .quick-filters-container {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }

        .quick-filters-container::-webkit-scrollbar {
            display: none !important;
        }

        .quick-ledge-btn.active {
            border-color: var(--primary) !important;
            background: rgba(99, 102, 241, 0.1) !important;
            color: var(--primary) !important;
        }

        .quick-ledge-btn.active span {
            background: var(--primary) !important;
        }
    </style>

    <!-- Results Table -->
    <div id="resultsContainer" class="glass-card" style="overflow: visible; position: relative; border-radius: 20px;">

        <!-- Dynamic Search Analytics Dashboard -->
        @if(isset($isSearching) && $isSearching && request('search'))
        <div style="background: var(--bg-card); border-radius: 20px 20px 0 0; border-bottom: 2px solid var(--primary); padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; box-shadow: 0 4px 20px rgba(99, 102, 241, 0.05); z-index: 10;">
            <div style="display: flex; align-items: center; gap: 1.25rem;">
                <div style="width: 44px; height: 44px; background: rgba(99, 102, 241, 0.1); color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="search" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--text-main);">Search: <span style="color: var(--primary);">"{{ request('search') }}"</span></h3>
                    <p style="margin: 0.15rem 0 0; color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Found {{ $receivedItems->total() }} matching system records</p>
                </div>
            </div>
            <div style="display: flex; gap: 1.5rem; align-items: center;">
                <div style="text-align: right;">
                    <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-weight: 800; margin-bottom: 0.2rem;">Total System Sum</div>
                    <div style="font-size: 1.55rem; font-weight: 900; color: var(--text-main); line-height: 1;">
                        <span style="color: var(--primary);">{{ number_format((float)($searchQtySum ?? 0)) }}</span> <span style="font-size: 0.85rem; color: var(--text-muted);">Qty</span>
                        <span style="color: rgba(0,0,0,0.1); margin: 0 0.4rem;">|</span>
                        <span>{{ number_format((float)($searchSum ?? 0)) }}</span> <span style="font-size: 0.85rem; color: var(--text-muted);">Stock</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="table-scroll-wrapper">
            <table class="activity-table" style="width: 100%; min-width: 1500px; border-collapse: collapse;">
                <thead>
                    <tr style="background: rgba(0,0,0,0.02); text-align: left;">
                        <th style="padding: 1rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Date</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Description</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Category</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Supplier</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Donor</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Status</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Avail. Qty</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Ledge</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Stock</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Variance</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Health</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Item Health</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receivedItems as $item)
                    @php
                    $agg = $itemAggregates[$item->description] ?? null;
                    $totalQty = $agg ? (float)$agg->total_received_qty : 0;
                    $totalStock = $agg ? (float)$agg->total_available : 0;
                    $totalLedge = $agg ? (float)$agg->total_book : 0;

                    // Calculation: (Available Qty / Stock Balance) * 100
                    $percentage = ($totalStock > 0) ? ($totalQty / $totalStock) * 100 : 0;

                    $hStatus = 'IN STOCK';
                    $hColor = '#10b981';

                    // Status Override: IF stock_balance == 0 OR available_qty == 0
                    if ($totalStock <= 0 || $totalQty <=0) {
                        $hStatus='OUT OF STOCK' ;
                        $hColor='#ef4444' ;
                        } elseif ($percentage <=50) {
                        $hStatus='LOW STOCK' ;
                        $hColor='#ef4444' ;
                        } elseif ($percentage <=70) {
                        $hStatus='WARNING' ;
                        $hColor='#f59e0b' ;
                        }
                        @endphp
                        <tr class="activity-row" style="border-top: 1px solid var(--border-color);">
                        <td data-label="Date" style="padding: 1rem 1.25rem; color: var(--text-muted); font-size: 0.85rem;">{{ \Carbon\Carbon::parse($item->entry_date)->format('M d, Y') }}</td>
                        <td data-label="Description" style="padding: 1rem 1.25rem;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">{{ $item->description }}</div>
                            <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Batch #{{ $item->batch_id }}</div>
                        </td>
                        <td data-label="Category" style="padding: 1.25rem 1.5rem;">
                            <span style="font-size: 0.75rem; background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 600;">
                                {{ $ledgeMap[$item->ledge_category] ?? "Ledge " . $item->ledge_category }}
                            </span>
                        </td>
                        @php
                        $rawSupplier = $item->supplier_name;
                        $acquisitionType = $item->acquisition_type ?? 'Supplier';
                        $donorName = $item->donor_name ?? '-';
                        if ($acquisitionType === 'Supplier' && preg_match('/\[Donor Action\]/', $rawSupplier)) {
                        $acquisitionType = 'Donor';
                        $donorName = preg_replace('/\s\[.*\]$/', '', $rawSupplier);
                        }

                        $cleanSupplier = preg_replace('/\s\[.*\]$/', '', $rawSupplier);
                        $displayStatus = 'N/A';
                        if ($acquisitionType === 'Donor') {
                        $displayStatus = 'DONOR';
                        } else {
                        if (preg_match('/\[(.*)\]/', $rawSupplier, $matches)) {
                        $displayStatus = $matches[1];
                        }
                        }

                        $statusColor = '#94a3b8';
                        if ($acquisitionType === 'Donor') {
                        $statusColor = '#8b5cf6';
                        } elseif ($displayStatus === 'Full Delivery' || $displayStatus === 'Full Deliv') {
                        $statusColor = '#10b981';
                        $displayStatus = 'FULL DELIV';
                        } elseif ($displayStatus === 'Partial Delivery' || $displayStatus === 'Partial Deliv') {
                        $statusColor = '#ef4444';
                        $displayStatus = 'PARTIAL DELIV';
                        }
                        @endphp
                        <td data-label="Supplier" style="padding: 1.25rem 1.5rem; color: var(--text-main); font-weight: 500;">{{ $cleanSupplier ?: '-' }}</td>
                        <td data-label="Donor" style="padding: 1.25rem 1.5rem; color: var(--text-main); font-weight: {{ $acquisitionType === 'Donor' ? '800' : '400' }};">{{ $donorName }}</td>
                        <td data-label="Status" style="padding: 1.25rem 1.5rem;">
                            <span style="font-size: 0.7rem; font-weight: 900; color: white; background: {{ $statusColor }}; padding: 0.35rem 0.8rem; border-radius: 8px; text-transform: uppercase; box-shadow: 0 4px 10px {{ $statusColor }}30; letter-spacing: 0.5px;">
                                {{ $displayStatus }}
                            </span>
                        </td>
                        <td data-label="Avail. Qty" style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--text-main);">{{ $item->qty ?? '0' }}</td>
                        <td data-label="ledge_balance" style="padding: 1.25rem 1.5rem;">
                            <span style="font-weight: 800; color: {{ (float)$item->ledge_balance > 0 ? '#3b82f6' : '#1e40af' }};">
                                {{ $item->ledge_balance }}
                            </span>
                        </td>
                        <td data-label="Balance" style="padding: 1.25rem 1.5rem; color: var(--text-main); font-weight: 700;">{{ $item->stock_balance }}</td>
                        <td data-label="Qty" style="padding: 1.25rem 1.5rem;">
                            <span style="font-weight: 800; color: {{ is_numeric($item->variance) && (float)$item->variance > 0 ? '#10b981' : (is_numeric($item->variance) && (float)$item->variance < 0 ? '#ef4444' : '#94a3b8') }};">
                                {{ is_numeric($item->variance) && (float)$item->variance > 0 ? '+' : '' }}{{ $item->variance }}
                            </span>
                        </td>
                        <td data-label="System Health" style="padding: 1.25rem 1.5rem;">
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 0.6rem; font-weight: 900; color: white; background: {{ $hColor }}; padding: 0.2rem 0.5rem; border-radius: 4px; display: inline-block; width: fit-content; text-transform: uppercase;">{{ $hStatus }}</span>
                                    <span style="font-size: 0.75rem; font-weight: 800; color: {{ $hColor }};">{{ round($percentage) }}%</span>
                                </div>
                                <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-main);">{{ number_format($totalQty) }} <span style="font-size: 0.65rem; color: var(--text-muted);">Avl</span></div>
                            </div>
                        </td>

                        <td data-label="Available Item Health" style="padding: 1rem 1.25rem;">
                            @php
                            $isItemLow = $totalQty <= 100;
                                $itemHealthStatus=$isItemLow ? 'LOW' : 'GOOD' ;
                                $itemHealthColor=$isItemLow ? '#ef4444' : '#10b981' ;
                                @endphp
                                <div style="display: flex; align-items: center; gap: 4px;">
                                <span style="font-size: 0.55rem; font-weight: 900; color: white; background: {{ $itemHealthColor }}; padding: 0.15rem 0.4rem; border-radius: 4px; display: inline-block; width: fit-content; text-transform: uppercase;">{{ $itemHealthStatus }}</span>
         </div>
         </td>

        <td data-label="Action" style="padding: 1rem 1.25rem; text-align: right;">
            <div class="action-dropdown-wrapper">
                <button type="button" class="glass-btn-sm" title="Actions" onclick="toggleActionMenu('{{ $item->batch_id }}')" style="padding: 0.5rem; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="more-vertical" style="width: 18px;"></i>
                </button>
                <div id="actionMenu-{{ $item->batch_id }}" class="action-menu">
                    @if($displayStatus === 'Partial Delivery' || $displayStatus === 'PARTIAL DELIV')
                    <button onclick="continueDelivery('{{ $item->batch_id }}')" class="menu-item" style="color: #f59e0b;">
                        <i data-lucide="package-plus"></i>
                        Continue Delivery
                    </button>
                    @endif
                    <button onclick="openModal('{{ $item->batch_id }}')" class="menu-item">
                        <i data-lucide="eye"></i>
                        View Details
                    </button>
                    <button onclick="openStockCheckModal('{{ addslashes($item->description) }}', {{ $totalLedge }}, {{ $totalStock }}, '{{ $agg->total_variance ?? 0 }}', '{{ $totalQty }}')" class="menu-item" style="color: var(--primary);">
                        <i data-lucide="shield-check"></i>
                        Stock Check
                    </button>
                    <div style="height: 1px; background: var(--border-color); margin: 4px 10px; opacity: 0.5;"></div>
                    <button onclick="deleteBatch('{{ $item->batch_id }}')" class="menu-item" style="color: #ef4444;">
                        <i data-lucide="trash-2"></i>
                        Delete Record
                    </button>
                </div>
            </div>
        </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" style="padding: 7rem 2rem; text-align: center;">
                <div style="display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
                    <div style="background: var(--bg-main); width: 84px; height: 84px; border-radius: 24px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); box-shadow: 0 10px 40px rgba(0,0,0,0.04); border: 1px solid var(--border-color);">
                        <i data-lucide="package-search" style="width: 38px; opacity: 0.6;"></i>
                    </div>
                    <div style="max-width: 450px; margin: 0 auto;">
                        <h4 style="font-size: 1.35rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.75rem; letter-spacing: -0.02em;">No Records Discovered</h4>
                        <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.6;">Your inventory ledger is currently empty or no items match your current search filters. Try broadening your criteria or record a new batch.</p>

                        <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                            <a href="{{ route('receiveditems') }}" class="glass-card" style="padding: 0.75rem 1.5rem; border-radius: 12px; text-decoration: none; font-size: 0.9rem; color: var(--text-main); font-weight: 700; transition: all 0.3s; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.5rem;">
                                <i data-lucide="refresh-ccw" style="width: 16px;"></i>
                                Reset Filters
                            </a>
                            <button onclick="window.location.href='/'" class="btn-primary" style="padding: 0.75rem 1.5rem; border-radius: 12px; border: none; font-size: 0.9rem; background: var(--primary); color: white; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);">
                                <i data-lucide="plus-circle" style="width: 18px;"></i>
                                New Inventory Entry
                            </button>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        @endforelse
        </tbody>
        </table>
    </div>

    <!-- Advanced Pagination Footer -->
    <div class="pagination-footer">
        <div class="pagination-container">
            <div class="pagination-info">
                <span class="pagination-info-badge">
                    <i data-lucide="layers" style="width: 14px; height: 14px; display: inline; margin-right: 6px;"></i>
                    {{ $receivedItems->total() }} Total Records
                </span>
                <span class="pagination-stats">
                    Showing {{ $receivedItems->firstItem() ?? 0 }} - {{ $receivedItems->lastItem() ?? 0 }}
                </span>
            </div>

            <div class="pagination-nav">
                @if ($receivedItems->onFirstPage())
                <span class="pagination-arrow disabled">
                    <i data-lucide="chevron-left" style="width: 20px;"></i>
                </span>
                @else
                <a href="{{ $receivedItems->previousPageUrl() }}" class="pagination-arrow">
                    <i data-lucide="chevron-left" style="width: 20px;"></i>
                </a>
                @endif

                <div class="pagination-numbers">
                    @php
                    $currentPage = $receivedItems->currentPage();
                    $lastPage = $receivedItems->lastPage();
                    $start = max($currentPage - 2, 1);
                    $end = min($currentPage + 2, $lastPage);
                    @endphp

                    @if($start > 1)
                    <a href="{{ $receivedItems->url(1) }}" class="pagination-number">1</a>
                    @if($start > 2)
                    <span class="pagination-dots">...</span>
                    @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <a href="{{ $receivedItems->url($i) }}"
                        class="pagination-number {{ $currentPage == $i ? 'active' : '' }}">
                        {{ $i }}
                        </a>
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                            <span class="pagination-dots">...</span>
                            @endif
                            <a href="{{ $receivedItems->url($lastPage) }}" class="pagination-number">{{ $lastPage }}</a>
                            @endif
                </div>

                @if ($receivedItems->hasMorePages())
                <a href="{{ $receivedItems->nextPageUrl() }}" class="pagination-arrow">
                    <i data-lucide="chevron-right" style="width: 20px;"></i>
                </a>
                @else
                <span class="pagination-arrow disabled">
                    <i data-lucide="chevron-right" style="width: 20px;"></i>
                </span>
                @endif
            </div>

            <div class="pagination-per-page">
                <select class="per-page-select" onchange="window.location.href=this.value">
                    @foreach([10, 25, 50, 100] as $perPage)
                    <option value="{{ request()->fullUrlWithQuery(['per_page' => $perPage]) }}"
                        {{ request('per_page', 15) == $perPage ? 'selected' : '' }}>
                        {{ $perPage }} per page
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Advanced Detail Modal -->
<div id="detailModal" class="modal-backdrop">
    <div class="modal-content glass-card animate-scale-up">
        <div class="modal-header">
            <div>
                <h3 id="modalTitle" style="font-size: 1.5rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.25rem;">Batch Details</h3>
                <p id="modalSubtitle" style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">#BATCH-0000</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <button onclick="printModal()" class="btn-icon" title="Print Details">
                    <i data-lucide="printer" style="width: 18px;"></i>
                </button>
                <button onclick="closeModal()" class="btn-icon danger" title="Close">
                    <i data-lucide="x" style="width: 18px;"></i>
                </button>
            </div>
        </div>

        <div class="modal-body" id="modalBody">
            <div class="loader-container">
                <div class="loader"></div>
                <p>Retrieving transaction data...</p>
            </div>
        </div>
    </div>
</div>

<!-- Continue Delivery Modal -->
<div id="continueDeliveryModal" class="modal-backdrop">
    <div class="modal-content glass-card animate-scale-up" style="max-width: 800px; width: 95%;">
        <div class="modal-header">
            <div>
                <h3 style="margin: 0; font-size: 1.5rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                    <span style="width: 32px; height: 32px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="package-plus" style="width: 18px;"></i>
                    </span>
                    Receive Pending Remainder
                </h3>
                <p id="continueModalSubtitle" style="margin: 4px 0 0; font-size: 0.9rem; color: var(--text-muted); font-weight: 600;">Loading batch details...</p>
            </div>
            <button onclick="closeContinueDeliveryModal()" class="btn-icon danger">
                <i data-lucide="x" style="width: 18px;"></i>
            </button>
        </div>
        <div class="modal-body" id="continueModalBody" style="background: var(--bg-card); display: flex; flex-direction: column; gap: 1.5rem; max-height: 70vh; overflow-y: auto;">
            <!-- Content dynamically generated -->
        </div>
        <div style="border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 1rem; padding: 1.5rem; background: var(--bg-card); border-radius: 0 0 28px 28px;">
            <button onclick="closeContinueDeliveryModal()" style="padding: 0.85rem 1.5rem; border-radius: 12px; font-weight: 800; font-size: 0.95rem; background: transparent; border: 1px solid var(--border-color); color: var(--text-main); cursor: pointer; transition: all 0.3s; opacity: 0.8;">Cancel</button>
            <button onclick="submitContinueDelivery()" id="submitContinueBtn" style="padding: 0.85rem 1.5rem; border-radius: 12px; font-weight: 800; font-size: 0.95rem; background: #f59e0b; border: none; color: white; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3); transition: all 0.3s;">
                <i data-lucide="check-circle" style="width: 18px;"></i> Process Remaining Delivery
            </button>
        </div>
    </div>
</div>

<!-- Stock Check / Audit Modal -->
<div id="stockCheckModal" class="modal-backdrop">
    <div class="modal-content glass-card animate-scale-up" style="max-width: 800px; width: 95%;">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1.5rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.25rem;">Stock Audit</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">Physical Verification Form</p>
            </div>
            <button onclick="closeStockCheckModal()" class="btn-icon danger">
                <i data-lucide="x" style="width: 18px;"></i>
            </button>
        </div>
        <div class="modal-body">
            <!-- Modern Stat Grid -->
            <div style="background: var(--bg-card); padding: 1.5rem; border-radius: 24px; margin-bottom: 1.5rem; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-end;">
                    <div>
                        <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 6px;">Audit Target</label>
                        <div id="auditItemName" style="font-size: 1.4rem; font-weight: 900; color: var(--text-main); line-height: 1;">Broom</div>
                    </div>
                    <button onclick="fetchAuditHistory()" class="glass-btn-sm" style="border-radius: 12px; font-weight: 700;">
                        <i data-lucide="layout-grid" style="width: 14px; margin-right: 6px;"></i> Full History
                    </button>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="audit-stat-card" onclick="toggleAuditBreakdown('balance')" title="Drill down into Ledger record">
                        <label>Ledger Balance</label>
                        <div id="auditLedgeBal">0</div>
                    </div>
                    <div class="audit-stat-card" onclick="toggleAuditBreakdown('stock')" title="Drill down into System record">
                        <label>Stock Balance</label>
                        <div id="auditStockBal">0</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="audit-stat-card" onclick="toggleAuditBreakdown('variance')" title="Review historical discrepancies">
                        <label>Prev. Variance</label>
                        <div id="auditPrevVar">0</div>
                    </div>
                    <div class="audit-stat-card" onclick="toggleAuditBreakdown('avail')" title="Audit current availability trail">
                        <label>Available Qty</label>
                        <div id="auditPrevAvail">0</div>
                    </div>
                </div>

                <!-- History Breakdown Drawer (Modernized) -->
                <div id="auditHistoryDrawer" style="display: none; margin-top: 1.5rem; border-top: 2px solid var(--bg-main); padding-top: 1.5rem; max-height: 300px; overflow-y: auto; scrollbar-width: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h4 id="breakdownTitle" style="font-size: 0.8rem; font-weight: 900; text-transform: uppercase; color: var(--primary); display: flex; align-items: center; gap: 8px;">
                            History Breakdown
                        </h4>
                        <span style="font-size: 0.65rem; color: var(--text-muted); font-weight: 700;">Recent Batches First</span>
                    </div>
                    <div id="auditHistoryContent"></div>
                </div>
            </div>

            <!-- Enhanced Input Form -->
            <form id="stockCheckForm" onsubmit="event.preventDefault(); submitStockCheck();" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="audit-input-group" style="background: var(--bg-main); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border-color);">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center;">
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">Physical Count</label>
                            <input type="number" id="physicalCount" required placeholder="0" style="width: 100%; border: none; background: transparent; color: var(--text-main); font-size: 2rem; font-weight: 900; outline: none;" oninput="calculateAuditVariance()">
                        </div>
                        <div style="text-align: right;">
                            <label style="display: block; font-size: 0.8rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem; text-transform: uppercase;">New Variance</label>
                            <div id="newAuditVariance" style="font-size: 2.5rem; font-weight: 900; color: var(--primary); line-height: 1;">--</div>
                        </div>
                    </div>

                    <div class="variance-indicator">
                        <div id="varianceIndicatorFill" class="variance-indicator-fill"></div>
                    </div>

                    <div id="auditInsightArea">
                        <div class="insight-pill" id="auditInsight" style="background: rgba(99, 102, 241, 0.05); color: var(--primary);">
                            <i data-lucide="brain"></i>
                            <span>Waiting for physical input...</span>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 1rem;">
                    <div class="custom-select-wrapper">
                        <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Condition</label>
                        <select id="auditReason">
                            <option value="">Status...</option>
                            <option value="Good">Good Condition</option>
                            <option value="Missing">Missing</option>
                            <option value="Damaged">Damaged</option>
                            <option value="Found">Found</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div style="grid-column: span 2;">
                        <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Auditor Remarks / Variance Documentation</label>
                        <textarea id="auditNotes" placeholder="Describe the situation, location, or damage details in depth..." style="width: 100%; height: 80px; padding: 0.85rem; border: 1px solid var(--border-color); border-radius: 12px; background: var(--bg-card); color: var(--text-main); font-family: inherit; resize: none; outline: none; transition: all 0.3s;" onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(99, 102, 241, 0.1)'" onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'"></textarea>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 1.1rem; border-radius: 18px; border: none; background: var(--primary-gradient); color: white; font-weight: 900; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2); transition: all 0.3s;">
                        <i data-lucide="shield-check" style="width: 18px;"></i>
                        Seal Record
                    </button>

                    <button type="button" onclick="generateAuditReport()" id="genRepBtn" class="glass-btn audit-report-btn" style="width: 100%; padding: 1.1rem; border-radius: 18px; display: flex; align-items: center; justify-content: center; gap: 10px; font-weight: 800; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); color: #ffffff; cursor: pointer; transition: all 0.3s; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                        <i data-lucide="receipt-text" style="width: 18px;"></i>
                        Auto-Report
                    </button>
                </div>

                <!-- Report Composer Drawer (Formal) -->
                <div id="reportComposerDrawer" style="display: none; margin-top: 2rem; border-top: 3px solid var(--primary); padding-top: 1.5rem; background: var(--bg-card); border-radius: 20px; padding: 1.5rem; border: 1px solid var(--border-color); box-shadow: 0 10px 40px rgba(0,0,0,0.05); animation: slideInUp 0.4s ease;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 32px; height: 32px; background: var(--primary); color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i data-lucide="file-edit" style="width: 18px;"></i>
                            </div>
                            <h4 style="font-size: 0.9rem; font-weight: 900; color: var(--text-main); text-transform: uppercase; margin:0;">Report Composer</h4>
                        </div>
                        <span style="font-size: 0.65rem; color: var(--primary); font-weight: 800; background: rgba(99, 102, 241, 0.1); padding: 4px 10px; border-radius: 100px;">Internal Audit Mode</span>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.7rem; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Editable Auditor Narrative</label>
                        <textarea id="finalReportBody" style="width: 100%; height: 180px; padding: 1.25rem; border-radius: 14px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-family: 'Inter', sans-serif; font-size: 0.85rem; line-height: 1.6; resize: vertical; outline: none; transition: all 0.3s;" onfocus="this.style.borderColor='var(--primary)'"></textarea>
                    </div>

                    <div style="margin-bottom: 1.5rem; opacity: 0.8;">
                        <label style="display: block; font-size: 0.7rem; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">System-Generated Conclusion (Locked)</label>
                        <div id="lockedConclusionArea" style="width: 100%; padding: 1rem; background: var(--bg-main); border: 1px dashed var(--border-color); border-radius: 14px; font-size: 0.8rem; font-weight: 700; color: #ef4444; position: relative;">
                            [Locked Content] Waiting for generation...
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 1rem;">
                        <button type="button" onclick="document.getElementById('reportComposerDrawer').style.display='none'" class="glass-btn" style="padding: 1rem; border-radius: 14px; font-weight: 700; border: 1px solid var(--border-color); background: transparent; cursor: pointer; color: var(--text-muted);">Discard</button>
                        <button type="button" onclick="printFinalAudit()" class="btn-primary" style="padding: 1rem; border-radius: 14px; font-weight: 900; background: #0c0e12; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                            <i data-lucide="printer"></i> Finalize & Print Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Premium Design System */
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    [data-theme='dark'] {
        --glass-bg: rgba(30, 41, 59, 0.7);
        --glass-border: rgba(255, 255, 255, 0.1);
    }

    /* Stock Audit Command Center Styles */
    .audit-report-btn:hover {
        background: #0f172a !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2) !important;
    }

    .custom-select-wrapper {
        position: relative;
        width: 100%;
    }

    .custom-select-wrapper select {
        width: 100%;
        height: 50px;
        /* Fixed height for precision */
        padding: 0 40px 0 15px;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-card);
        color: var(--text-main);
        font-weight: 700;
        font-size: 0.9rem;
        outline: none;
        appearance: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .custom-select-wrapper::after {
        content: "";
        position: absolute;
        right: 15px;
        bottom: 18px;
        /* Position relative to baseline */
        width: 12px;
        height: 12px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='3'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: center;
        pointer-events: none;
        transition: transform 0.3s ease, filter 0.3s ease;
    }

    .custom-select-wrapper:focus-within::after {
        transform: rotate(180deg);
        filter: sepia(1) saturate(5) hue-rotate(200deg);
        /* Shift to primary color */
    }

    .custom-select-wrapper select:hover {
        border-color: var(--primary);
        background: var(--bg-main);
    }

    .custom-select-wrapper select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }

    .audit-stat-card {
        background: var(--bg-card);
        padding: 1rem;
        border-radius: 18px;
        border: 1px solid var(--border-color);
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }

    .audit-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--primary);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .audit-stat-card:hover {
        transform: translateY(-5px) scale(1.02);
        background: var(--bg-main);
        border-color: var(--primary);
        box-shadow: 0 15px 35px -10px rgba(99, 102, 241, 0.2);
    }

    .audit-stat-card:hover::before {
        opacity: 1;
    }

    .audit-stat-card label {
        display: block;
        font-size: 0.65rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .audit-stat-card div {
        font-size: 1.4rem;
        font-weight: 900;
        color: var(--text-main);
        letter-spacing: -0.5px;
    }

    .insight-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.6rem 1.2rem;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: 700;
        margin-top: 1rem;
        animation: slideInUp 0.4s ease;
    }

    .batch-audit-card {
        background: var(--bg-main);
        border-radius: 14px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border: 1px solid var(--border-color);
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 15px;
        align-items: center;
        transition: all 0.3s;
    }

    .batch-audit-card:hover {
        border-color: var(--primary);
        background: var(--bg-card);
        transform: translateX(5px);
    }

    .batch-icon-box {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: rgba(99, 102, 241, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
    }

    .variance-indicator {
        height: 6px;
        border-radius: 3px;
        background: var(--border-color);
        margin-top: 12px;
        overflow: hidden;
        position: relative;
    }

    .variance-indicator-fill {
        height: 100%;
        width: 0%;
        transition: width 0.6s cubic-bezier(0.19, 1, 0.22, 1);
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    [data-theme='dark'] {
        --bg-main: #020617;
    }

    .glass-card {
        background: var(--glass-bg) !important;
        backdrop-filter: blur(16px) saturate(180%);
        border: 1px solid var(--glass-border) !important;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05) !important;
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    [data-theme='dark'] .glass-card {
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3) !important;
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.8)) !important;
    }

    .glass-card:hover {
        transform: translateY(-5px);
    }

    .table-scroll-wrapper {
        width: 100%;
        overflow: visible;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .table-scroll-wrapper::-webkit-scrollbar {
        display: none;
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
        background: var(--bg-card) !important;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4) !important;
        border-radius: 28px;
        overflow: hidden;
        border: 1px solid var(--glass-border);
        transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    [data-theme='dark'] .modal-content {
        background: linear-gradient(135deg, #0f172a, #020617) !important;
    }

    .modal-header {
        padding: 1.75rem 2.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-body {
        padding: 2.5rem;
        overflow-y: auto;
        flex: 1;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .modal-body::-webkit-scrollbar {
        display: none;
    }

    @keyframes slideUpIn {
        from {
            transform: translateY(100%);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes slideDownOut {
        from {
            transform: translateY(0);
            opacity: 1;
        }

        to {
            transform: translateY(100%);
            opacity: 0;
        }
    }

    .modal-content.slide-in {
        animation: slideUpIn 0.6s cubic-bezier(0.32, 1, 0.23, 1) forwards;
    }

    .modal-content.slide-out {
        animation: slideDownOut 0.6s cubic-bezier(0.32, 1, 0.23, 1) forwards;
    }

    @media (max-width: 768px) {
        .modal-backdrop {
            align-items: flex-end !important;
            justify-content: flex-end !important;
            padding: 0 !important;
        }

        .modal-content {
            width: 100% !important;
            height: auto !important;
            max-height: 92vh !important;
            border-radius: 32px 32px 0 0 !important;
            margin: 0 !important;
            border: none !important;
        }

        .modal-content::before {
            content: '';
            position: sticky;
            top: 12px;
            left: 50%;
            transform: translateX(-50%);
            width: 44px;
            height: 5px;
            background: var(--text-muted);
            opacity: 0.2;
            border-radius: 10px;
            z-index: 1000;
            display: block;
            margin: 0 auto -5px auto;
        }

        .modal-header {
            padding: 3rem 1.5rem 1.25rem 1.5rem !important;
            flex-direction: column !important;
            align-items: center !important;
            text-align: center !important;
            gap: 1.5rem !important;
            position: relative !important;
        }

        .modal-header>div:last-child {
            position: absolute !important;
            top: 1rem !important;
            right: 1rem !important;
        }

        .modal-body {
            padding: 1.5rem !important;
        }
    }

    .btn-icon {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-main);
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-icon:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-3px) rotate(8deg);
        border-color: var(--primary);
    }

    .glass-btn-sm {
        display: inline-flex;
        align-items: center;
        padding: 0.6rem 1.25rem;
        background: var(--bg-card);
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        color: var(--primary);
        font-weight: 800;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .glass-btn-sm:hover {
        background: var(--primary);
        color: white !important;
        border-color: var(--primary);
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
    }

    /* Advanced Pagination */
    .pagination-footer {
        padding: 2rem 2.5rem;
        background: rgba(0, 0, 0, 0.02);
        border-top: 1px solid var(--border-color);
    }

    .pagination-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .pagination-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .pagination-info-badge {
        background: var(--primary-gradient);
        color: white;
        padding: 0.6rem 1.25rem;
        border-radius: 14px;
        font-size: 0.85rem;
        font-weight: 800;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
    }

    .pagination-nav {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .pagination-arrow {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: var(--bg-card);
        border: 1.5px solid var(--border-color);
        color: var(--text-main);
        transition: all 0.3s;
        text-decoration: none;
    }

    .pagination-arrow:hover {
        background: var(--primary);
        color: white;
        transform: scale(1.1);
        border-color: var(--primary);
    }

    .pagination-number {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        height: 48px;
        border-radius: 16px;
        background: var(--bg-card);
        border: 1.5px solid var(--border-color);
        color: var(--text-main);
        font-weight: 800;
        text-decoration: none;
        transition: all 0.3s;
    }

    .pagination-number.active {
        background: var(--primary-gradient);
        color: white;
        border-color: transparent;
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
    }

    .per-page-select {
        padding: 0.75rem 2.5rem 0.75rem 1.25rem;
        border-radius: 14px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-main);
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
    }

    [data-theme='dark'] .per-page-select {
        background-color: #1e293b;
    }

    /* Table Logic */
    .activity-row {
        position: relative;
        transition: all 0.3s ease;
    }

    .activity-row:hover {
        background: rgba(99, 102, 241, 0.05) !important;
        box-shadow: inset 4px 0 0 var(--primary);
    }

    /* Action Dropdown Styles */
    .action-dropdown-wrapper {
        position: relative;
        display: inline-block;
    }

    .action-menu {
        position: absolute;
        right: 0;
        top: calc(100% + 10px);
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        min-width: 200px;
        z-index: 1000;
        display: none;
        flex-direction: column;
        padding: 0.75rem;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(12px);
        animation: dropdownIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    .action-menu.active {
        display: flex;
    }

    .menu-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-radius: 10px;
        color: var(--text-main);
        font-weight: 700;
        font-size: 0.85rem;
        text-align: left;
        background: transparent;
        border: none;
        width: 100%;
        cursor: pointer;
        transition: all 0.2s;
    }

    .menu-item:hover {
        background: rgba(99, 102, 241, 0.1);
        color: var(--primary) !important;
        transform: translateX(5px);
    }

    .menu-item i {
        width: 18px;
        height: 18px;
    }

    @keyframes dropdownIn {
        from {
            opacity: 0;
            transform: translateY(15px) scale(0.95);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @media (max-width: 1024px) {
        .activity-table {
            min-width: unset !important;
        }

        .activity-table thead {
            display: none !important;
        }

        .activity-table,
        .activity-table tbody,
        .activity-table tr,
        .activity-table td {
            display: block !important;
            width: 100% !important;
        }

        .activity-row {
            margin-bottom: 2rem !important;
            border-radius: 20px !important;
            background: var(--bg-card) !important;
            padding: 1.5rem !important;
            border: 1px solid var(--border-color) !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
            position: relative;
        }

        .activity-row td {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 0.75rem 0 !important;
            border: none !important;
            background: transparent !important;
        }

        .activity-row td::before {
            content: attr(data-label);
            font-weight: 800;
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .activity-row td[data-label="Description"] {
            display: block !important;
            border-bottom: 2px solid var(--border-color) !important;
            padding-bottom: 1.5rem !important;
            margin-bottom: 1.25rem !important;
        }

        .activity-row td[data-label="Description"]::before {
            display: inline-block !important;
            margin-bottom: 0.75rem !important;
            margin-right: 1rem !important;
            /* Added margin right */
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            padding: 0.35rem 0.85rem;
            border-radius: 8px;
            font-size: 0.65rem !important;
            font-weight: 900 !important;
            letter-spacing: 1.5px !important;
            border: 1px solid rgba(99, 102, 241, 0.15);
            content: 'PRODUCT SPECIFICATION' !important;
        }

        .activity-row td[data-label="Action"] {
            border-top: 1px solid var(--border-color) !important;
            margin-top: 1rem !important;
            padding-top: 1.25rem !important;
            text-align: center !important;
            justify-content: center !important;
            flex-direction: row !important;
            flex-wrap: wrap !important;
            gap: 0.75rem !important;
            height: auto !important;
        }

        .desktop-only {
            display: none !important;
        }

        .glass-btn-sm {
            width: 100%;
            justify-content: center;
        }
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes scaleUp {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .animate-scale-up {
        animation: scaleUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    .loader {
        width: 50px;
        height: 50px;
        border: 4px solid var(--border-color);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    /* Mobile Responsive Overrides */
    @media (max-width: 1024px) {
        .page-header {
            flex-direction: column;
            align-items: stretch !important;
            gap: 1.5rem;
            padding: 1.75rem !important;
            background: linear-gradient(180deg, var(--bg-card) 0%, var(--bg-main) 100%) !important;
            border-radius: 0 0 28px 28px !important;
            margin: -24px -24px 2.5rem -24px !important;
            /* Bleed to edges */
            border: none !important;
            border-bottom: 1px solid var(--border-color) !important;
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.03) !important;
        }

        .page-header h2 {
            font-size: 1.75rem !important;
            margin-top: 0.5rem !important;
        }

        .page-header p {
            font-size: 0.95rem !important;
            opacity: 0.8;
        }

        .page-header>div:last-child {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 0.75rem !important;
            width: 100% !important;
        }

        .page-header>div:last-child button {
            width: 100% !important;
            justify-content: center !important;
            padding: 0.85rem !important;
            font-size: 0.85rem !important;
        }

        .stats-grid {
            grid-template-columns: 1fr !important;
        }
    }

    @media (max-width: 768px) {

        /* Stats stacking */
        div[style*="grid-template-columns: repeat(auto-fit"] {
            grid-template-columns: repeat(2, 1fr) !important;
        }

        div[style*="grid-template-columns: repeat(auto-fit"]>div {
            padding: 1rem !important;
            gap: 0.75rem !important;
        }

        /* Filter stacking */
        form[style*="display: grid;"] {
            grid-template-columns: 1fr !important;
            gap: 1.25rem !important;
        }

        #filterForm input {
            height: 50px !important;
            font-size: 0.95rem !important;
            border-color: var(--border-color) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02) !important;
        }

        #filterForm label {
            color: var(--primary) !important;
            font-size: 0.7rem !important;
            margin-left: 0.25rem !important;
        }

        .search-container-mobile {
            border-radius: 24px !important;
            padding: 1.25rem !important;
            background: var(--bg-card) !important;
            border: 1px solid var(--primary) !important;
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.1) !important;
        }

        .filter-buttons-mobile {
            display: grid !important;
            grid-template-columns: 1fr 60px !important;
            gap: 0.75rem !important;
            margin-top: 0.5rem !important;
        }

        .filter-buttons-mobile button {
            height: 50px !important;
            border-radius: 12px !important;
        }

        /* Transforming Table into Cards for Mobile */
        .activity-table thead {
            display: none;
            /* Hide headers on mobile */
        }

        .activity-table,
        .activity-table tbody,
        .activity-table tr,
        .activity-table td {
            display: block;
            width: 100%;
        }

        .activity-table tr {
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            background: var(--glass-bg);
            padding: 1rem;
            position: relative;
        }

        .activity-table td {
            text-align: right;
            padding: 0.75rem 0.5rem !important;
            border-top: 1px solid rgba(0, 0, 0, 0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-table td:first-child {
            border-top: none;
        }

        /* Add labels before data */
        .activity-table td::before {
            content: attr(data-label);
            float: left;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        .activity-table td[data-label="Description"] {
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }

        .activity-table td[data-label="Description"]::before {
            margin-bottom: 0.5rem;
        }

        .desktop-only {
            display: none !important;
        }

        .glass-btn-sm {
            padding: 0.6rem !important;
            border-radius: 12px;
        }

        /* Pagination adjustments */
        .pagination-container {
            flex-direction: column;
            align-items: stretch;
            padding: 1.5rem 1rem;
        }

        .pagination-info {
            justify-content: center;
            flex-direction: column;
            text-align: center;
        }

        .pagination-nav {
            justify-content: center;
            margin: 1rem 0;
        }

        .pagination-number {
            min-width: 38px;
            height: 38px;
            font-size: 0.85rem;
        }

        .pagination-per-page {
            justify-content: center;
            width: 100%;
        }

        .per-page-select {
            width: 100%;
            text-align: center;
        }
    }

    @media print {

        .sidebar,
        .top-nav,
        .glass-card form,
        .page-header button,
        .top-nav-actions,
        .pagination-footer {
            display: none !important;
        }

        .main-wrapper {
            margin-left: 0 !important;
        }

        body {
            background: white !important;
        }

        .glass-card {
            box-shadow: none !important;
            border: 1px solid #eee !important;
        }
    }
</style>

<script>
    const ledgeMap = @json($ledgeMap);

    let currentBatchId = null;

    function openModal(batchId) {
        currentBatchId = batchId;
        const modal = document.getElementById('detailModal');
        const modalContent = modal.querySelector('.modal-content');
        const modalBody = document.getElementById('modalBody');
        const modalSubtitle = document.getElementById('modalSubtitle');

        modal.style.display = 'flex';
        modalContent.classList.remove('slide-out');
        modalContent.classList.add('slide-in');
        modalSubtitle.innerText = `#BATCH-${batchId}`;

        // Show loader
        modalBody.innerHTML = `
            <div class="loader-container">
                <div class="loader"></div>
                <p>Retrieving transaction data...</p>
            </div>
        `;

        // Fetch data
        fetch(`/received-items/${batchId}?json=true`)
            .then(response => response.json())
            .then(data => {
                const batch = data.batch;
                let itemsHtml = '';

                batch.items.forEach((item, index) => {
                    const expected = (parseFloat(item.stock_balance) - parseFloat(item.variance)).toFixed(0);
                    const isLast = index === batch.items.length - 1;
                    const varianceColor = parseFloat(item.variance) < 0 ? '#ef4444' : (parseFloat(item.variance) > 0 ? '#3b82f6' : '#10b981');
                    const varianceSign = parseFloat(item.variance) > 0 ? '+' : '';

                    itemsHtml += `
                        <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem; ${!isLast ? 'border-bottom: 1px solid var(--border-color);' : ''}">
                            <div style="font-weight: 800; color: var(--text-main); font-size: 1rem; margin-bottom: 0.25rem;">${item.description}</div>
                            
                            <div style="background: rgba(0,0,0,0.02); padding: 1rem; border-radius: 12px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; text-align: center;">
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800;">Expected</span>
                                    <span style="font-weight: 700; color: var(--text-main); font-size: 1rem;">${expected}</span>
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800;">Brought</span>
                                    <span style="font-weight: 700; color: var(--text-main); font-size: 1rem;">${item.qty || '0'}</span>
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800;">Recorded</span>
                                    <span style="font-weight: 900; color: #10b981; font-size: 1rem;">${item.stock_balance}</span>
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800;">Variance</span>
                                    <span style="font-weight: 900; color: ${varianceColor}; font-size: 1rem;">${varianceSign}${item.variance}</span>
                                </div>
                            </div>
                            
                            <div style="background: rgba(99, 102, 241, 0.03); padding: 0.75rem 1rem; border-radius: 12px; border-left: 2px solid var(--primary); font-size: 0.8rem; color: var(--text-muted); display: flex; align-items: flex-start; gap: 8px;">
                                <i data-lucide="message-circle" style="width: 14px; min-width: 14px; margin-top: 2px;"></i>
                                <span style="font-style: italic;">${item.remarks ? item.remarks : 'No remarks provided'}</span>
                            </div>
                        </div>
                    `;
                });

                modalBody.innerHTML = `
                    <!-- Inset Grouped Style: Batch Info -->
                    <div style="background: var(--bg-main); border-radius: 20px; border: 1px solid var(--border-color); overflow: hidden; margin-bottom: 2rem;">
                        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted); font-weight: 700; font-size: 0.8rem; text-transform: uppercase;">Logistics Source</span>
                            <span style="color: var(--text-main); font-weight: 800;">${batch.supplier_name.replace(/\[.*?\]/g, '').trim()}</span>
                        </div>
                        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted); font-weight: 700; font-size: 0.8rem; text-transform: uppercase;">Transaction Date</span>
                            <span style="color: var(--text-main); font-weight: 700;">${new Date(batch.entry_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</span>
                        </div>
                        <div style="padding: 1.15rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted); font-weight: 700; font-size: 0.8rem; text-transform: uppercase;">Allocation</span>
                            <span style="color: var(--primary); font-weight: 900;">${ledgeMap[batch.ledge_category] || 'Ledge ' + batch.ledge_category}</span>
                        </div>
                    </div>

                    <!-- Inset Grouped Style: Items -->
                    <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); margin-left: 1rem; margin-bottom: 0.75rem; font-weight: 800; letter-spacing: 0.5px;">Product Specification</h4>
                    <div style="background: var(--bg-main); border-radius: 20px; border: 1px solid var(--border-color); overflow: hidden; margin-bottom: 2rem;">
                        ${itemsHtml}
                    </div>


                    <div style="margin-top: 2.5rem; display: flex; flex-direction: column; gap: 1rem;">
                        <button onclick="printModal()" style="width: 100%; background: var(--primary); color: white; border: none; padding: 1.15rem; border-radius: 20px; font-weight: 800; font-size: 1rem; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);">
                            <i data-lucide="printer" style="width: 20px;"></i>
                            Print Official Voucher
                        </button>
                        <div style="text-align: center; color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">
                            Certified for System Audit • ${batch.items.length} Items
                        </div>
                    </div>
                `;

                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(error => {
                modalBody.innerHTML = `
                    <div class="loader-container">
                        <i data-lucide="alert-circle" style="width: 48px; color: #ef4444;"></i>
                        <p style="color: #ef4444;">Failed to load data. Please try again.</p>
                    </div>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
    }

    function closeModal() {
        const modal = document.getElementById('detailModal');
        const modalContent = modal.querySelector('.modal-content');

        modalContent.classList.remove('slide-in');
        modalContent.classList.add('slide-out');

        setTimeout(() => {
            modal.style.display = 'none';
            modalContent.classList.remove('slide-out');
            currentBatchId = null;
        }, 600);
    }

    function printModal() {
        if (currentBatchId) {
            window.open(`/received-items/${currentBatchId}/print`, '_blank');
        }
    }

    let continueBatchData = null;

    function continueDelivery(batchId) {
        // Hide standard detail modal if open
        const actionMenu = document.getElementById('actionMenu-' + batchId);
        if (actionMenu) {
            actionMenu.classList.remove('active');
        }
        
        const modal = document.getElementById('continueDeliveryModal');
        const subtitle = document.getElementById('continueModalSubtitle');
        const body = document.getElementById('continueModalBody');
        const submitBtn = document.getElementById('submitContinueBtn');
        
        modal.style.display = 'flex';
        subtitle.innerText = `Fetching pending details for Batch #${batchId}...`;
        submitBtn.style.display = 'none';
        
        body.innerHTML = `
            <div class="loader-container">
                <div class="loader"></div>
                <p>Retrieving transaction data...</p>
            </div>
        `;

        fetch(`/received-items/${batchId}?json=true`)
            .then(response => response.json())
            .then(data => {
                continueBatchData = data.batch;
                subtitle.innerText = `Original Transaction: #${continueBatchData.id} • ${new Date(continueBatchData.entry_date).toLocaleDateString()}`;
                
                let html = `
                    <div style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 16px; padding: 1.5rem; margin-bottom: 0.5rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800; margin-bottom: 4px;">Logistics Source</div>
                                <div style="font-weight: 800; color: var(--text-main);">${continueBatchData.supplier_name.replace(/\[.*?\]/g, '').trim()}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800; margin-bottom: 4px;">Ledge Category</div>
                                <div style="font-weight: 800; color: var(--primary);">${ledgeMap[continueBatchData.ledge_category] || continueBatchData.ledge_category}</div>
                            </div>
                        </div>
                    </div>
                `;

                let hasPending = false;

                continueBatchData.items.forEach((item, index) => {
                    const expected = parseFloat(item.stock_balance) - parseFloat(item.variance);
                    const qty = parseFloat(item.qty) || 0;
                    const outstanding = expected - qty;
                    
                    if (outstanding <= 0) return; // Full quantity has been brought
                    
                    hasPending = true;
                    
                    html += `
                    <div style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 16px; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div style="font-weight: 800; font-size: 1.1rem; color: var(--text-main);">${item.description}</div>
                            <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 0.4rem 0.8rem; border-radius: 8px; font-weight: 800; font-size: 0.8rem;">
                                ${outstanding} Outstanding
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; text-align: center; background: rgba(0,0,0,0.02); border-radius: 12px; padding: 1rem;">
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800;">Expected</span>
                                <span style="font-weight: 700; color: var(--text-main); font-size: 1rem;">${expected}</span>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800;">Already Brought</span>
                                <span style="font-weight: 900; color: #10b981; font-size: 1rem;">${qty}</span>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800;">Pending Deficit</span>
                                <span style="font-weight: 900; color: #ef4444; font-size: 1rem;">${outstanding}</span>
                            </div>
                        </div>
                        
                        <div style="margin-top: 0.5rem;">
                            <label style="font-size: 0.75rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-bottom: 8px; display: block;">Newly Arrived Quantity</label>
                            <input type="number" class="continue-input" data-item-id="${item.id}" max="${outstanding}" min="0" placeholder="Enter additional units received..." 
                                style="width: 100%; padding: 1.15rem; border-radius: 12px; border: 2px solid var(--border-color); font-size: 1rem; font-weight: 700; background: var(--bg-card); color: var(--text-main); transition: all 0.3s;"
                                oninput="previewRecalculation(${item.id}, ${expected}, ${item.stock_balance}, ${qty}, ${outstanding})">
                        </div>

                        <div id="recalc_preview_${item.id}" style="display: none; background: rgba(16, 185, 129, 0.05); border-left: 3px solid #10b981; padding: 0.75rem 1rem; border-radius: 8px; margin-top: 0.5rem; font-size: 0.85rem;">
                            <!-- Preview injected here -->
                        </div>
                    </div>
                    `;
                });

                
                if (!hasPending) {
                    html += `
                        <div style="text-align: center; padding: 3rem 1rem;">
                            <i data-lucide="check-circle" style="width: 48px; height: 48px; color: #10b981; margin-bottom: 1rem;"></i>
                            <h3 style="margin: 0 0 0.5rem; font-weight: 900; color: var(--text-main);">Fully Delivered</h3>
                            <p style="margin: 0; color: var(--text-muted);">There are no outstanding items to receive for this transaction.</p>
                        </div>
                    `;
                } else {
                    submitBtn.style.display = 'flex';
                }

                body.innerHTML = html;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
    }

    function closeContinueDeliveryModal() {
        document.getElementById('continueDeliveryModal').style.display = 'none';
        continueBatchData = null;
    }

    function previewRecalculation(itemId, expected, currentStock, currentQty, outstanding) {
        const input = document.querySelector(`.continue-input[data-item-id="${itemId}"]`);
        const preview = document.getElementById('recalc_preview_' + itemId);
        
        let incoming = parseFloat(input.value);
        
        if (isNaN(incoming) || incoming <= 0) {
            preview.style.display = 'none';
            return;
        }

        // Prevent exceeding outstanding
        if (incoming > outstanding) {
            incoming = outstanding;
            input.value = incoming;
        }
        
        const newStock = parseFloat(currentStock) + incoming;
        const newVariance = newStock - expected;
        const newQty = parseFloat(currentQty) + incoming;
        
        // Let's determine variance reason - if newQty == expected but the newStock != expected due to past damage
        let varText = '';
        if (newVariance == 0) {
            varText = '<span style="color:#10b981">Perfect match (No variance)</span>';
        } else if (newVariance < 0) {
            if (newQty >= expected) {
                varText = `<span style="color:#f59e0b">Variance of ${newVariance} due to previously declared faults</span>`;
            } else {
                varText = `<span style="color:#ef4444">Still missing ${Math.abs(newVariance)}</span>`;
            }
        } else {
            varText = `<span style="color:#3b82f6">Surplus of ${newVariance}</span>`;
        }

        preview.style.display = 'block';
        preview.innerHTML = `
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <div style="font-weight: 800; color: var(--text-main);">Projected System Updates:</div>
                <div style="color: var(--text-muted);">New Recorded Balance: <strong>${newStock}</strong></div>
                <div style="color: var(--text-muted);">New Total Brought: <strong>${newQty}</strong></div>
                <div style="color: var(--text-muted);">Status: <strong>${varText}</strong></div>
            </div>
        `;
    }

    async function submitContinueDelivery() {
        const inputs = document.querySelectorAll('.continue-input');
        const updates = [];
        
        inputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val) && val > 0) {
                updates.push({
                    item_id: input.getAttribute('data-item-id'),
                    incoming_qty: val
                });
            }
        });
        
        if (updates.length === 0) {
            alert('Please enter at least one quantity to receive.');
            return;
        }
        
        const btn = document.getElementById('submitContinueBtn');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = `<div class="loader" style="width: 18px; height: 18px; border-width: 2px;"></div> Processing...`;
        btn.disabled = true;

        try {
            const response = await fetch('/api/inventory/receive-remainder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ updates })
            });
            
            const result = await response.json();
            
            if (result.success) {
                closeContinueDeliveryModal();
                performSearch(true); // Silent refresh to show updated values on UI
            } else {
                alert('Execution Error: ' + (result.message || 'Unknown error occurred.'));
            }
        } catch (error) {
            console.error('Submission Failed:', error);
            alert('A critical system error occurred during submission.');
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    }

    // Close on backdrop click
    window.onclick = function(event) {
        const modal = document.getElementById('detailModal');
        if (event.target == modal) {
            closeModal();
        }
    }

    // Real-time Search Logic
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const supplierInput = document.getElementById('supplierInput');
    const dateInput = document.getElementById('dateInput');
    const resultsContainer = document.getElementById('resultsContainer');

    let debounceTimer;

    function performSearch(isSilent = false) {
        // Skip history updates and UI dimming during silent background sync
        if (!isSilent) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({}, '', cleanUrl);
            resultsContainer.style.opacity = '0.6';
        }

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData).toString();
        const url = `${window.location.pathname}?${params}`;

        fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newBody = doc.getElementById('resultsContainer');

                if (newBody) {
                    const newContent = newBody.innerHTML;
                    // Only update DOM if content actually changed to save cycles
                    if (resultsContainer.innerHTML !== newContent) {
                        resultsContainer.innerHTML = newContent;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                }

                if (!isSilent) resultsContainer.style.opacity = '1';
            })
            .catch(error => {
                console.error('Background Sync Error:', error);
                if (!isSilent) resultsContainer.style.opacity = '1';
            });
    }

    // High-Fidelity Background Sync Engine (Silent Pulse)
    // Refreshes the system every 4 seconds if the auditor is idle
    setInterval(() => {
        const isDetailOpen = document.getElementById('detailModal')?.style.display === 'flex';
        const isAuditOpen = document.getElementById('stockCheckModal')?.style.display === 'flex';
        const isMenuOpen = document.querySelector('.action-menu.active') !== null;
        const isTyping = document.activeElement.matches('input, textarea, select');

        if (!isDetailOpen && !isAuditOpen && !isMenuOpen && !isTyping) {
            performSearch(true);
        }
    }, 4000);

    if (searchInput) {
        [searchInput, supplierInput, dateInput].forEach(input => {
            if (input) {
                input.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(performSearch, 400);
                });
            }
        });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            performSearch();
        });
    }

    // Quick Ledge Filter Logic
    const ledgeBtns = document.querySelectorAll('.quick-ledge-btn');
    const ledgeInput = document.getElementById('ledgeCategoryInput');

    ledgeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            ledgeBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            ledgeInput.value = btn.dataset.ledge;
            performSearch();
        });
    });

    // Carousel Navigation Logic
    const ledgeScroll = document.getElementById('ledgeScroll');
    const prevBtn = document.getElementById('prevLedge');
    const nextBtn = document.getElementById('nextLedge');

    if (ledgeScroll && prevBtn && nextBtn) {
        const updateArrows = () => {
            const scrollLeft = ledgeScroll.scrollLeft;
            const maxScroll = ledgeScroll.scrollWidth - ledgeScroll.clientWidth;

            prevBtn.style.opacity = scrollLeft > 10 ? '1' : '0';
            prevBtn.style.pointerEvents = scrollLeft > 10 ? 'auto' : 'none';

            nextBtn.style.opacity = scrollLeft < maxScroll - 10 ? '1' : '0';
            nextBtn.style.pointerEvents = scrollLeft < maxScroll - 10 ? 'auto' : 'none';
        };

        prevBtn.addEventListener('click', () => {
            ledgeScroll.scrollBy({
                left: -250,
                behavior: 'smooth'
            });
        });

        nextBtn.addEventListener('click', () => {
            ledgeScroll.scrollBy({
                left: 250,
                behavior: 'smooth'
            });
        });

        ledgeScroll.addEventListener('scroll', updateArrows);
        window.addEventListener('resize', updateArrows);
        // Initial check
        setTimeout(updateArrows, 500);
    }

    // Initialize Lucide icons after page load
    document.addEventListener('DOMContentLoaded', function() {
        // Instant Clean URL on load if params exist
        if (window.location.search) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({}, '', cleanUrl);
        }

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Action Dropdown Close Listener
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.action-dropdown-wrapper')) {
                document.querySelectorAll('.action-menu.active').forEach(menu => {
                    menu.classList.remove('active');
                    const row = menu.closest('.activity-row');
                    if (row) row.style.zIndex = '';
                });
            }
        });
    });

    // Toggle Action Menu Logic
    function toggleActionMenu(batchId) {
        // Close all other active menus
        document.querySelectorAll('.action-menu.active').forEach(menu => {
            if (menu.id !== `actionMenu-${batchId}`) {
                menu.classList.remove('active');
                const row = menu.closest('.activity-row');
                if (row) row.style.zIndex = '';
            }
        });

        const menu = document.getElementById(`actionMenu-${batchId}`);
        if (menu) {
            menu.classList.toggle('active');
            const row = menu.closest('.activity-row');
            if (row) {
                if (menu.classList.contains('active')) {
                    row.style.zIndex = '50';
                } else {
                    row.style.zIndex = '';
                }
            }
        }
    }

    async function deleteBatch(batchId) {
        if (!confirm('⚠️ SYSTEM CAUTION:\n\nYou are about to permanently purge this batch record and all its associated product logs from the database.\n\nThis action is forensic and IRREVERSIBLE. Do you wish to proceed?')) {
            return;
        }

        try {
            const response = await fetch(`/received-items/${batchId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                // Silent refresh using the existing background sync engine
                performSearch(true);
            } else {
                alert('Purge Error: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Purge Transaction Error:', error);
            alert('A critical system error occurred during the transaction purge.');
        }
    }

    function openStockCheckModal(description, ledgeBal, stockBal, prevVar, prevAvail) {
        document.getElementById('auditItemName').innerText = description;
        document.getElementById('auditLedgeBal').innerText = ledgeBal;
        document.getElementById('auditStockBal').innerText = stockBal;
        document.getElementById('auditPrevVar').innerText = prevVar || '0';
        document.getElementById('auditPrevAvail').innerText = prevAvail || '0';

        document.getElementById('physicalCount').value = '';
        document.getElementById('auditReason').value = '';
        document.getElementById('auditNotes').value = '';
        const varianceDisplay = document.getElementById('newAuditVariance');
        varianceDisplay.innerText = '--';
        varianceDisplay.style.color = 'var(--primary)';

        // Reset Visual Gauge and Insight Pill
        const indicatorFill = document.getElementById('varianceIndicatorFill');
        if (indicatorFill) {
            indicatorFill.style.width = '0%';
            indicatorFill.style.background = 'var(--primary-gradient)';
        }

        const insightPill = document.getElementById('auditInsight');
        if (insightPill) {
            insightPill.innerHTML = '<i data-lucide="info"></i> <span>Enter count to begin analysis...</span>';
            insightPill.className = 'insight-pill';
            insightPill.style.background = 'rgba(99, 102, 241, 0.05)';
            insightPill.style.color = 'var(--text-muted)';
        }

        // Comprehensive Report Reset (Clear previous audit artifacts)
        const reportDrawer = document.getElementById('reportComposerDrawer');
        if (reportDrawer) {
            reportDrawer.style.display = 'none';
            document.getElementById('finalReportBody').value = '';
            document.getElementById('lockedConclusionArea').innerHTML = '<div style="color: var(--text-muted); font-style: italic; font-size: 0.75rem;">Waiting for generation...</div>';
        }

        // Proactively Prefetch Audit History Data for Reporting
        auditHistoryData = [];
        fetchAuditHistory().catch(() => {});

        document.getElementById('auditHistoryDrawer').style.display = 'none';

        const modal = document.getElementById('stockCheckModal');
        modal.style.display = 'flex';

        // Close all menus
        document.querySelectorAll('.action-menu.active').forEach(m => m.classList.remove('active'));
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    let auditHistoryData = [];

    async function fetchAuditHistory() {
        const description = document.getElementById('auditItemName').innerText;
        const container = document.getElementById('auditHistoryContent');

        container.innerHTML = `
            <div class="loader-container" style="padding: 1rem;">
                <div class="loader" style="width: 20px; height: 20px; border-width: 2px;"></div>
                <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 8px;">Loading batch trail...</p>
            </div>
        `;

        try {
            const response = await fetch(`/api/item-audit-details?description=${encodeURIComponent(description)}`);
            auditHistoryData = await response.json();
            return auditHistoryData;
        } catch (error) {
            container.innerHTML = `<p style="color: #ef4444; font-size: 0.7rem;">Failed to retrieve batch details: ${error.message}</p>`;
            throw error;
        }
    }

    function toggleAuditBreakdown(type) {
        const drawer = document.getElementById('auditHistoryDrawer');
        const isCurrentlyOpen = drawer.style.display === 'block';

        if (isCurrentlyOpen) {
            drawer.style.display = 'none';
        } else {
            drawer.style.display = 'block';
            if (auditHistoryData.length === 0) {
                fetchAuditHistory().then(() => renderAuditBreakdown(type)).catch(() => {});
            } else {
                renderAuditBreakdown(type);
            }
        }
    }

    function calculateAuditVariance() {
        const stockBal = parseFloat(document.getElementById('auditStockBal').innerText) || 0;
        const physical = parseFloat(document.getElementById('physicalCount').value);
        const display = document.getElementById('newAuditVariance');
        const insight = document.getElementById('auditInsight');
        const indicatorFill = document.getElementById('varianceIndicatorFill');

        if (isNaN(physical)) {
            display.innerText = '--';
            display.style.color = 'var(--primary)';
            insight.innerHTML = `<i data-lucide="brain"></i> <span>Waiting for physical input...</span>`;
            indicatorFill.style.width = '0%';
            if (typeof lucide !== 'undefined') lucide.createIcons();
            return;
        }

        const variance = physical - stockBal;
        display.innerText = (variance > 0 ? '+' : '') + variance;

        // Progress Indicator Logic
        const diffPercent = Math.min(100, Math.abs((variance / (stockBal || 1)) * 100));
        indicatorFill.style.width = `${diffPercent}%`;

        // Smart Insight Engine
        if (variance === 0) {
            display.style.color = '#10b981';
            indicatorFill.style.background = '#10b981';
            insight.className = 'insight-pill';
            insight.style.background = 'rgba(16, 185, 129, 0.1)';
            insight.style.color = '#10b981';
            insight.innerHTML = `<i data-lucide="check-sparkles"></i> <span>Perfect Audit! Physical matches System.</span>`;
        } else if (variance < 0) {
            display.style.color = '#ef4444';
            indicatorFill.style.background = '#ef4444';
            insight.className = 'insight-pill';
            insight.style.background = 'rgba(239, 68, 68, 0.1)';
            insight.style.color = '#ef4444';
            insight.innerHTML = `<i data-lucide="alert-triangle"></i> <span>Shortage Detected: System Expects ${stockBal} units.</span>`;
        } else {
            display.style.color = '#3b82f6';
            indicatorFill.style.background = '#3b82f6';
            insight.className = 'insight-pill';
            insight.style.background = 'rgba(59, 130, 246, 0.1)';
            insight.style.color = '#3b82f6';
            insight.innerHTML = `<i data-lucide="package-plus"></i> <span>Surplus Noted: ${variance} extra units identified.</span>`;
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function renderAuditBreakdown(type) {
        const container = document.getElementById('auditHistoryContent');
        const title = document.getElementById('breakdownTitle');

        let label = "";
        let key = "";
        let icon = "list";

        switch (type) {
            case 'balance':
                label = "Ledger Trail";
                key = "ledge_balance";
                icon = "book";
                break;
            case 'stock':
                label = "System Trail";
                key = "stock_balance";
                icon = "layers";
                break;
            case 'variance':
                label = "Variance Trail";
                key = "variance";
                icon = "git-commit";
                break;
            case 'avail':
                label = "Available Trail";
                key = "qty";
                icon = "check-circle";
                break;
            default:
                label = "Batch History";
                key = "qty";
        }

        title.innerHTML = `<i data-lucide="${icon}" style="width: 14px;"></i> ${label}`;

        if (auditHistoryData.length === 0) {
            container.innerHTML = `<div style="text-align: center; padding: 2rem;"><p style="color: var(--text-muted); font-size: 0.75rem;">No historical trail found.</p></div>`;
            return;
        }

        let html = '';
        auditHistoryData.forEach(item => {
            const date = new Date(item.entry_date).toLocaleDateString('en-GB', {
                day: 'numeric',
                month: 'short'
            });
            const val = item[key] ?? 0;
            const sign = val > 0 ? '+' : '';
            const valColor = val < 0 ? '#ef4444' : (val > 0 ? '#10b981' : 'var(--text-main)');

            html += `
                <div class="batch-audit-card" style="display: grid; grid-template-columns: auto 1fr auto; gap: 12px; align-items: start;">
                    <div class="batch-icon-box" style="margin-top: 4px;">
                        <i data-lucide="database" style="width: 18px;"></i>
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 0.85rem; color: var(--text-main);">${date} - ${item.supplier_name.split(' [')[0]}</div>
                        <div style="font-family: monospace; font-size: 0.65rem; color: var(--text-muted); margin-bottom: 4px;">BATCH ID: #${item.batch_id} | ORIG QTY: ${item.qty}</div>

                        ${item.remarks ? `
                            <div style="background: rgba(99, 102, 241, 0.03); padding: 0.5rem; border-radius: 8px; border-left: 2px solid var(--primary); font-size: 0.7rem; color: var(--text-muted); display: flex; align-items: flex-start; gap: 6px; margin-top: 4px;">
                                <i data-lucide="message-circle" style="width: 12px; min-width: 12px; margin-top: 1px;"></i>
                                <span style="font-style: italic;">${item.remarks}</span>
                            </div>
                        ` : ''}
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 900; font-size: 1.1rem; color: ${valColor};">${sign}${val}</div>
                        <div style="font-size: 0.6rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Result</div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeStockCheckModal() {
        document.getElementById('stockCheckModal').style.display = 'none';
    }

    function submitStockCheck() {
        const item = document.getElementById('auditItemName').innerText;
        const count = document.getElementById('physicalCount').value;
        const reason = document.getElementById('auditReason').value;
        const notes = document.getElementById('auditNotes').value;

        closeStockCheckModal();

        showToast(
            'Audit Submitted',
            `Stock audit for ${item} completed. Variance: ${document.getElementById('newAuditVariance').innerText}`,
            'success'
        );
    }
    async function generateAuditReport() {
        const item = document.getElementById('auditItemName').innerText;
        const ledgeBal = document.getElementById('auditLedgeBal').innerText;
        const stockBal = document.getElementById('auditStockBal').innerText;
        const prevVar = document.getElementById('auditPrevVar').innerText;
        const physical = document.getElementById('physicalCount').value;
        const variance = document.getElementById('newAuditVariance').innerText;
        const reason = document.getElementById('auditReason').value || 'General Stock Audit';
        const remarks = document.getElementById('auditNotes').value || 'No specific situation remarks noted.';

        if (!physical) {
            showToast('Execution Halt', 'Please enter a physical count to initialize the formal report.', 'error');
            return;
        }

        // Logic to ensure history data is loaded before synthesis
        const genBtn = document.getElementById('genRepBtn');
        const originalHtml = genBtn.innerHTML;

        if (auditHistoryData.length === 0) {
            genBtn.innerHTML = `<div class="loader" style="width: 14px; height: 14px; border-width: 2px;"></div> Synchronizing Trail...`;
            genBtn.style.opacity = '0.7';
            try {
                await fetchAuditHistory();
            } catch (e) {
                showToast('Synthesis Warning', 'Could not retrieve historical trail for the report.', 'warning');
            }
            genBtn.innerHTML = originalHtml;
            genBtn.style.opacity = '1';
        }

        const now = new Date();
        const dateStr = now.toLocaleDateString('en-GB', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
        const timeStr = now.toLocaleTimeString('en-GB', {
            hour: '2-digit',
            minute: '2-digit'
        });

        // Editable Narrative Body
        let narrative = `This audit was conducted to verify the physical existence and condition of the inventory item: ${item.toUpperCase()}.\n\n`;
        narrative += `Based on the physical verification conducted on ${dateStr} at exactly ${timeStr}, the following observations were recorded regarding the inventory state:\n\n`;
        narrative += `[AUDITOR NOTES]\n`;
        narrative += `${remarks}\n\n`;
        narrative += `The condition of the items has been categorized as "${reason}". This assessment reflects the current physical quality and storage situation for this batch.`;

        // Uneditable Conclusion Content
        let conclusion = `<div style="color: #000; font-weight: 800; font-size: 1.1rem; margin-bottom: 0.5rem;">[AUDIT CONCLUSION]</div>`;
        const varValue = parseFloat(variance);
        if (varValue === 0) {
            conclusion += `<div style="color: #10b981;">SUCCESS: No variance detected. Physical stock perfectly aligns with system ledger records.</div>`;
        } else if (varValue < 0) {
            conclusion += `<div style="color: #ef4444;">WARNING: A shortage of ${Math.abs(varValue)} units has been identified. Immediate reconciliation or loss investigation is required.</div>`;
        } else {
            conclusion += `<div style="color: #3b82f6;">NOTICE: A surplus of ${varValue} units has been discovered. Update requested for system ledger records to incorporate identified surplus.</div>`;
        }

        document.getElementById('finalReportBody').value = narrative;
        document.getElementById('lockedConclusionArea').innerHTML = conclusion;
        document.getElementById('reportComposerDrawer').style.display = 'block';

        showToast('Certificate Synthesized', 'Professional audit document is ready for review.', 'success');
        document.getElementById('reportComposerDrawer').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function printFinalAudit() {
        const logoUrl = "{{ asset('img/NACOC.png') }}";
        const item = document.getElementById('auditItemName').innerText;
        const physical = document.getElementById('physicalCount').value;
        const variance = document.getElementById('newAuditVariance').innerText;
        const narrative = document.getElementById('finalReportBody').value;
        const conclusion = document.getElementById('lockedConclusionArea').innerHTML;

        const now = new Date();
        const dateStr = now.toLocaleDateString('en-GB', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
        const timeStr = now.toLocaleTimeString('en-GB', {
            hour: '2-digit',
            minute: '2-digit'
        });

        // Build Batch History Table for Report
        let historyHtml = `
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.75rem;">
                <thead>
                    <tr style="background: #f3f4f6; text-align: left; border-bottom: 2px solid #000;">
                        <th style="padding: 10px;">Receipt Date</th>
                        <th style="padding: 10px;">Batch ID</th>
                        <th style="padding: 10px;">Source</th>
                        <th style="padding: 10px; text-align: center;">Ledge</th>
                        <th style="padding: 10px; text-align: center;">Stock</th>
                        <th style="padding: 10px; text-align: center;">Var.</th>
                    </tr>
                </thead>
                <tbody>
        `;

        auditHistoryData.forEach(batch => {
            const bDate = new Date(batch.entry_date).toLocaleDateString('en-GB', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
            historyHtml += `
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 8px;">${bDate}</td>
                    <td style="padding: 8px; font-family: monospace;">#${batch.batch_id}</td>
                    <td style="padding: 8px;">${batch.supplier_name.split(' [')[0]}</td>
                    <td style="padding: 8px; text-align: center;">${batch.ledge_balance}</td>
                    <td style="padding: 8px; text-align: center;">${batch.stock_balance}</td>
                    <td style="padding: 8px; text-align: center; font-weight: 700;">${batch.variance > 0 ? '+' : ''}${batch.variance}</td>
                </tr>
            `;
        });
        historyHtml += `</tbody></table>`;

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>NACOC Audit - ${item}</title>
                    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
                    <style>
                        @page { size: A4; margin: 20mm 15mm 20mm 15mm; }
                        body { padding: 0; margin: 0; font-family: 'Inter', sans-serif; color: #1a1a1a; line-height: 1.4; }
                        .page-wrapper { padding: 10px; }
                        .header { display: flex; justify-content: space-between; border-bottom: 4px solid #000; padding-bottom: 15px; margin-bottom: 30px; }
                        .logo-box { display: flex; align-items: center; gap: 20px; }
                        .logo-img { height: 75px; width: auto; object-fit: contain; }
                        .company-info h1 { margin: 0; font-size: 1.6rem; font-weight: 900; color: #000; }
                        .company-info p { margin: 0; font-size: 0.8rem; color: #444; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
                        .report-title { text-align: center; margin-bottom: 30px; }
                        .report-title h2 { font-size: 1.5rem; font-weight: 900; margin: 0; padding: 10px; border: 2px solid #000; display: inline-block; }
                        .meta-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px; background: #fafafa; padding: 15px; border: 1px solid #ddd; border-radius: 8px; page-break-inside: avoid; }
                        .meta-item label { display: block; font-size: 0.6rem; color: #666; font-weight: 800; text-transform: uppercase; margin-bottom: 2px; }
                        .meta-item div { font-weight: 800; font-size: 0.9rem; }
                        .section-title { font-weight: 900; font-size: 1rem; background: #000; color: #fff; padding: 6px 15px; margin: 25px 0 15px 0; display: inline-block; page-break-after: avoid; }
                        .audit-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
                        .narrative { font-size: 0.85rem; white-space: pre-wrap; }
                        .conclusion-inner { background: #fff1f2; border: 2px solid #ef4444; padding: 15px; border-radius: 6px; margin-top: 10px; }
                        .avoid-break { page-break-inside: avoid; break-inside: avoid; }
                        .sig-line { border-top: 1px solid #000; padding-top: 8px; font-weight: 800; text-align: center; font-size: 0.7rem; }
                    </style>
                </head>
                <body>
                    <div class="page-wrapper">
                        <div class="logo-box">
                            <img src="${logoUrl}" class="logo-img" alt="NACOC">
                            <div class="company-info">
                                <h1>NACOC</h1>
                                <p>Narcotics Control Commission</p>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 900; font-size: 1.2rem;">AUDIT CERTIFICATE</div>
                            <div style="font-size: 0.65rem; color: #666; font-weight: 700;">REG: NC/AUD-LOG/${new Date().getFullYear()}/${Math.floor(Math.random()*10000)}</div>
                        </div>
                    </div>

                    <div class="report-title">
                        <h2>VERIFIED STOCK AUDIT REPORT</h2>
                    </div>

                    <div class="meta-grid">
                        <div class="meta-item"><label>Subject Material</label><div>${item}</div></div>
                        <div class="meta-item"><label>Date of Audit</label><div>${dateStr}</div></div>
                        <div class="meta-item"><label>Time of Verification</label><div>${timeStr}</div></div>
                        <div class="meta-item"><label>Audit Status</label><div style="color: #ef4444;">FORMAL</div></div>
                    </div>

                    <div class="section-title">I. HISTORICAL RECEIPT FORENSICS</div>
                    <p style="font-size: 0.7rem; color: #666; font-style: italic; margin-bottom: 10px;">Chronological timeline of all item batches received prior to this audit.</p>
                    ${historyHtml}

                    <div class="audit-grid" style="margin-top: 20px;">
                        <div>
                            <div class="section-title">II. AUDITOR OBSERVATIONS</div>
                            <div class="narrative">${narrative}</div>
                        </div>
                        <div>
                            <div class="section-title">III. PHYSICAL VERIFICATION DATA</div>
                            <div style="background: #f0fdf4; border: 1px solid #10b981; padding: 15px; border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                    <span style="font-size: 0.7rem; font-weight: 800;">PHYSICAL QUANTITY:</span>
                                    <span style="font-weight: 900; color: #000;">${physical} Units</span>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="font-size: 0.7rem; font-weight: 800;">AUDIT VARIANCE:</span>
                                    <span style="font-weight: 900; color: ${parseFloat(variance) < 0 ? '#ef4444' : '#10b981'};">${variance} Units</span>
                                </div>
                            </div>

                            <div class="avoid-break">
                                <div class="section-title" style="background: #ef4444;">IV. FINAL DETERMINATION</div>
                                <div class="conclusion-inner">${conclusion}</div>
                            </div>
                        </div>
                    </div>

                    <div class="footer" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; margin-top: 70px;">
                        <div class="sig-line">REPORTING AUDITOR</div>
                        <div class="sig-line">FACILITY MANAGER</div>
                        <div class="sig-line">QUALITY CONTROL OFFICE</div>
                    </div>

                    <script>window.onload = function() { window.print(); window.close(); }<\/script>
                </body>
            </html>
        `);
        printWindow.document.close();
    }
</script>
@endsection