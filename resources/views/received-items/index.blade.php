@extends('layouts.dashboard')

@section('content')
<div class="animate-slide-up">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                @if(in_array(auth()->user()->role, ['Main Admin', 'Department Head']))
                    <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">{{ strtoupper(auth()->user()->department) }} · Department Head Hub</span>
                @else
                    <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">Item Records</span>
                @endif
                <span style="color: var(--text-muted); font-size: 0.85rem;"></span>
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main);">Received <span style="color: var(--primary);">Items</span></h2>
            <p style="color: var(--text-muted);">View all items received into the inventory system.</p>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button onclick="window.location.reload()" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--text-main);">
                <i data-lucide="refresh-cw" style="width: 18px;"></i>
                Refresh
            </button>

        </div>
    </div>

    <!-- Quick Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="glass-card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 48px; height: 48px; background: rgba(99, 102, 241, 0.1); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="package" style="width: 24px;"></i>
            </div>
            <div>
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Total Batches</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);">{{ $totalReceived }}</div>
            </div>
        </div>
        <div class="glass-card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="layers" style="width: 24px;"></i>
            </div>
            <div>
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Total Items Recorded</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);">{{ $totalItemsCount }}</div>
            </div>
        </div>
        <div class="glass-card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="trending-up" style="width: 24px;"></i>
            </div>
            <div>
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Recent Batches</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);">{{ $recentReceived }}</div>
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
                <input type="date" name="date_from" id="dateFromInput" value="{{ request('date_from') }}" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-main); color: var(--text-main);">
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase;">Date To</label>
                <input type="date" name="date_to" id="dateToInput" value="{{ request('date_to') }}" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-main); color: var(--text-main);">
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
    <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 3rem; flex-wrap: wrap;">
        <div style="display: flex; gap: 0.5rem; flex-shrink: 0;">
            <!-- Static Pending Partials Button -->
            <a href="{{ request()->fullUrlWithQuery(['status' => request('status') === 'partial' ? null : 'partial']) }}" style="padding: 0.65rem 1.4rem; border-radius: 999px; border: {{ request('status') === 'partial' ? '1.5px solid #f59e0b' : '1.5px solid var(--border-color)' }}; background: {{ request('status') === 'partial' ? 'rgba(245, 158, 11, 0.1)' : 'var(--bg-card)' }}; color: {{ request('status') === 'partial' ? '#f59e0b' : 'var(--text-main)' }}; font-weight: 800; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: all 0.3s; font-size: 0.85rem;">
                <i data-lucide="{{ request('status') === 'partial' ? 'x-circle' : 'alert-circle' }}" style="width: 16px;"></i>
                {{ request('status') === 'partial' ? 'Clear Partials' : 'Pending Partials' }}
            </a>

            <!-- Pending Approvals Button -->
            <a href="{{ request()->fullUrlWithQuery(['status' => request('status') === 'pending_approval' ? null : 'pending_approval']) }}" style="padding: 0.65rem 1.4rem; border-radius: 999px; border: {{ request('status') === 'pending_approval' ? '1.5px solid #4f46e5' : '1.5px solid var(--border-color)' }}; background: {{ request('status') === 'pending_approval' ? 'rgba(79, 70, 229, 0.1)' : 'var(--bg-card)' }}; color: {{ request('status') === 'pending_approval' ? '#4f46e5' : 'var(--text-main)' }}; font-weight: 800; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: all 0.3s; font-size: 0.85rem;">
                <i data-lucide="{{ request('status') === 'pending_approval' ? 'x-circle' : 'clock' }}" style="width: 16px;"></i>
                {{ request('status') === 'pending_approval' ? 'Clear Approvals' : 'Pending Approvals' }}
            </a>
        </div>

        <!-- Vertical Divider -->
        <div style="width: 2px; height: 32px; background: var(--border-color); flex-shrink: 0; border-radius: 2px; opacity: 0.5; display: none;"></div>

        <div class="category-carousel-wrapper" style="position: relative; flex-grow: 1; margin-bottom: 0; min-width: 0;">
            <!-- Left Nav Arrow -->
            <button type="button" id="prevLedge" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; border-radius: 50%; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); display: flex; align-items: center; justify-content: center; z-index: 10; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.3s;">
                <i data-lucide="chevron-left" style="width: 18px;"></i>
            </button>

            <div class="quick-filters-container" id="ledgeScroll" style="display: flex; gap: 0.75rem; overflow-x: auto; padding: 0.5rem 0.25rem; white-space: nowrap; scroll-behavior: smooth;">
                <button type="button" class="quick-ledge-btn {{ !request('ledge_category') ? 'active' : '' }}" data-ledge="" style="padding: 0.65rem 1.4rem; border-radius: 999px; border: 1.5px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-weight: 800; cursor: pointer; transition: all 0.3s; font-size: 0.85rem;">All Groups</button>
                @foreach($ledgeMap as $code => $name)
                <button type="button" class="quick-ledge-btn {{ request('ledge_category') == $code ? 'active' : '' }}" data-ledge="{{ $code }}" style="padding: 0.65rem 1.4rem; border-radius: 999px; border: 1.5px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-weight: 700; cursor: pointer; transition: all 0.3s; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                    <span style="width: 8px; height: 8px; background: #94a3b8; border-radius: 50%;"></span> Category {{ $code }} ({{ $name }})
                </button>
                @endforeach
            </div>

            <!-- Right Nav Arrow -->
            <button type="button" id="nextLedge" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; border-radius: 50%; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); display: flex; align-items: center; justify-content: center; z-index: 10; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.3s;">
                <i data-lucide="chevron-right" style="width: 18px;"></i>
            </button>
        </div>
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

        /* Mobile Card View for Stock Receipts Log */
        @media (max-width: 768px) {
            .table-scroll-wrapper {
                overflow-x: visible !important;
                padding: 0 !important;
            }
            .activity-table {
                min-width: 100% !important;
            }
            .activity-table thead {
                display: none;
            }
            .activity-table tbody {
                display: block;
            }
            .activity-table tr {
                display: block;
                margin-bottom: 1.5rem;
                padding: 1.5rem !important;
                background: var(--bg-card) !important;
                border-radius: 24px !important;
                box-shadow: 0 8px 25px rgba(0,0,0,0.04) !important;
                border: 1px solid var(--border-color) !important;
                position: relative;
            }
            .activity-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.85rem 0 !important;
                border-bottom: 1px dashed var(--border-color) !important;
                border-radius: 0 !important;
                width: 100% !important;
                text-align: right;
            }
            .activity-table td:last-child {
                border-bottom: none !important;
                padding-top: 1.25rem !important;
            }
            .activity-table td::before {
                content: attr(data-label);
                font-weight: 850;
                color: var(--text-muted);
                font-size: 0.7rem;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }
            .activity-table td > div,
            .activity-table td > span {
                text-align: right;
                max-width: 60%;
            }
        }
        /* PREMIUM SAMSUNG CARD VIEW - STOCK RECEIPTS LOG */
        @media (max-width: 768px) {
            .table-scroll-wrapper {
                overflow-x: visible !important;
                padding: 0.5rem !important;
            }
            .activity-table {
                min-width: 100% !important;
                border-spacing: 0 1rem !important;
                border-collapse: separate !important;
            }
            .activity-table thead {
                display: none;
            }
            .activity-table tbody {
                display: block;
            }
            .activity-table tr {
                display: block;
                margin-bottom: 2rem;
                padding: 1.75rem !important;
                background: var(--bg-card) !important;
                border-radius: 32px !important;
                box-shadow: 0 10px 30px rgba(0,0,0,0.06) !important;
                border: 1px solid var(--border-color) !important;
                position: relative;
                overflow: hidden;
            }
            /* Samsung-style left accent bar */
            .activity-table tr::before {
                content: '';
                position: absolute;
                left: 0;
                top: 0;
                bottom: 0;
                width: 6px;
                background: var(--primary);
            }
            .activity-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem 0 !important;
                border-bottom: 1px solid rgba(0,0,0,0.03) !important;
                border-radius: 0 !important;
                width: 100% !important;
            }
            .activity-table td:last-child {
                border-bottom: none !important;
                padding-top: 1.5rem !important;
                justify-content: center !important;
            }
            .activity-table td::before {
                content: attr(data-label);
                font-weight: 800;
                color: var(--text-muted);
                font-size: 0.65rem;
                text-transform: uppercase;
                letter-spacing: 0.1em;
            }
            .activity-table td > div,
            .activity-table td > span {
                text-align: right;
                font-size: 0.95rem;
                font-weight: 700;
            }
            .activity-table td[data-label="Description"] > div:first-child {
                font-size: 1.1rem;
                font-weight: 900;
                color: var(--primary);
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes blink-bg-red {
            0% {
                background-color: rgba(239, 68, 68, 0.15);
                border-color: rgba(239, 68, 68, 0.3);
                color: #ef4444;
            }
            50% {
                background-color: #ef4444;
                border-color: #ef4444;
                color: #ffffff;
            }
            100% {
                background-color: rgba(239, 68, 68, 0.15);
                border-color: rgba(239, 68, 68, 0.3);
                color: #ef4444;
            }
        }
    </style>

    <!-- Results Table -->
    <div id="resultsContainer" class="glass-card" style="overflow: visible; position: relative; border-radius: 20px;">

        <!-- Dynamic Search Analytics Dashboard -->
        @if(isset($isSearching) && $isSearching && request('search'))
        <div style="background: var(--bg-card); border-radius: 20px 20px 0 0; border-bottom: 2px solid var(--primary); padding: 1.5rem 2rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; box-shadow: 0 4px 20px rgba(99, 102, 241, 0.05); z-index: 10;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div style="width: 50px; height: 50px; background: rgba(99, 102, 241, 0.1); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="search" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--text-main);">Search: <span style="color: var(--primary);">"{{ request('search') }}"</span></h3>
                    <p style="margin: 0.25rem 0 0; color: var(--text-muted); font-size: 0.9rem; font-weight: 600;">Found {{ $receivedItems->total() }} matching system records</p>
                </div>
            </div>
            <div style="display: flex; gap: 2rem; align-items: center;">
                <div style="text-align: right;">
                    <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-weight: 800; margin-bottom: 0.25rem;">Total System Sum</div>
                    <div style="font-size: 2rem; font-weight: 900; color: var(--text-main); line-height: 1;">
                        <span style="color: var(--primary);">{{ number_format((float)($searchQtySum ?? 0)) }}</span> <span style="font-size: 1rem; color: var(--text-muted);">Total</span>
                        <span style="color: rgba(0,0,0,0.1); margin: 0 0.5rem;">|</span>
                        <span>{{ number_format((float)($searchSum ?? 0)) }}</span> <span style="font-size: 1rem; color: var(--text-muted);">Stock</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div style="display: flex; justify-content: flex-end; margin-bottom: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; background: rgba(99, 102, 241, 0.08); padding: 0.25rem 0.5rem; border-radius: 10px; border: 1px dashed rgba(99, 102, 241, 0.3);">
                <button type="button" onclick="document.querySelector('.table-scroll-wrapper').scrollBy({left: -400, behavior: 'smooth'})" style="background: transparent; border: none; cursor: pointer; padding: 0.35rem; border-radius: 6px; display: flex; align-items: center; transition: background 0.3s;" onmouseover="this.style.background='rgba(99, 102, 241, 0.15)'" onmouseout="this.style.background='transparent'" title="Scroll Left">
                    <i data-lucide="chevron-left" style="width: 16px; color: var(--primary);"></i>
                </button>
                <div style="display: flex; align-items: center; gap: 6px; padding: 0 0.5rem; user-select: none;">
                    <i data-lucide="move-horizontal" style="width: 14px; color: var(--primary); opacity: 0.8;"></i>
                    <span style="font-size: 0.7rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px;">Fast Navigate</span>
                </div>
                <button type="button" onclick="document.querySelector('.table-scroll-wrapper').scrollBy({left: 400, behavior: 'smooth'})" style="background: transparent; border: none; cursor: pointer; padding: 0.35rem; border-radius: 6px; display: flex; align-items: center; transition: background 0.3s;" onmouseover="this.style.background='rgba(99, 102, 241, 0.15)'" onmouseout="this.style.background='transparent'" title="Scroll Right">
                    <i data-lucide="chevron-right" style="width: 16px; color: var(--primary);"></i>
                </button>
            </div>
        </div>

        <div class="table-scroll-wrapper">
            <table class="activity-table" style="width: 100%; min-width: 1500px; border-collapse: collapse;">
                <thead>
                    <tr style="background: rgba(0,0,0,0.02); text-align: left;">
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Entry Date</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Received Date</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Description</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Category</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Supplier / Donor</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Delivery Status</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Return Date</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Received Qty</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Stock Bal.</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Variance</th>

                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Stock Level</th>
                        @if(!in_array(auth()->user()->role, ['Main Admin', 'Department Head']))
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; text-align: right;">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($receivedItems as $item)
                    @php
                    $agg = $itemAggregates[$item->description] ?? null;
                    $totalQty = $agg ? (float)$agg->total_received_qty : 0;
                    $totalStock = $agg ? (float)$agg->total_available : 0;

                    @endphp
                        <tr class="activity-row" data-item-id="{{ $item->id }}" data-batch-id="{{ $item->batch_id }}" style="border-top: 1px solid var(--border-color);">
                        <td data-label="Entry Date" style="padding: 1.25rem 1.5rem; color: var(--text-muted); text-transform: uppercase; font-size: 0.75rem; font-weight: 700;">{{ \Carbon\Carbon::parse($item->entry_date)->format('d/m/y H:i') }}</td>
                        <td data-label="Received Date" style="padding: 1.25rem 1.5rem; color: var(--primary); font-weight: 700;">{{ $item->arrival_date ? \Carbon\Carbon::parse($item->arrival_date)->format('d/m/y') : '-' }}</td>
                        <td data-label="Description" style="padding: 1.25rem 1.5rem;">
                            <div style="font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                                <span>{{ $item->description }}</span>
                                <span style="font-size: 0.65rem; color: var(--primary); font-weight: 800;">({{ $item->unit ?? 'Package Types' }})</span>
                            </div>
                            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Batch #{{ $item->batch_id }}</div>
                        </td>
                        <td data-label="Category" style="padding: 1.25rem 1.5rem;">
                            <span style="font-size: 0.75rem; background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 600;">
                                {{ $ledgeMap[$item->ledge_category] ?? "Category " . $item->ledge_category }}
                            </span>
                        </td>
                        @php
                        $cleanSupplier = $item->supplier_name;
                        $acquisitionType = $item->acquisition_type ?? 'Supplier';
                        $donorName = $item->donor_name ?? '-';

                        $dbStatus = strtoupper($item->supplier_status ?: 'FULL DELIVERY');
                        $isDbPartialDelivery = ($dbStatus === 'PARTIAL DELIVERY' || str_contains($dbStatus, 'PARTIAL'));

                        $isIssuedOut = $item->hasActiveTemporaryLoan();
                        if ($isIssuedOut) {
                            $displayStatus = 'ISSUED OUT';
                            $statusColor = '#f59e0b';
                        } else {
                            $displayStatus = $dbStatus;
                            $statusColor = '#94a3b8';
                            if ($acquisitionType === 'Donor' || $displayStatus === 'DONOR') {
                                $statusColor = '#8b5cf6';
                                $displayStatus = 'DONOR';
                            } elseif ($displayStatus === 'FULL DELIVERY' || str_contains($displayStatus, 'FULL')) {
                                $statusColor = '#10b981';
                                $displayStatus = 'FULL DELIVERY';
                            } elseif ($displayStatus === 'PARTIAL DELIVERY' || str_contains($displayStatus, 'PARTIAL')) {
                                $statusColor = '#ef4444';
                                $displayStatus = 'PARTIAL DELIVERY';
                            }
                        }
                        @endphp
                        <td data-label="Supplier / Donor" style="padding: 1.25rem 1.5rem; color: var(--text-main);">
                            @if($acquisitionType === 'Donor')
                                <div style="font-weight: 800; color: #8b5cf6;">{{ $donorName }}</div>
                            @else
                                <div>{{ $cleanSupplier ?: '-' }}</div>
                            @endif
                        </td>
                        <td data-label="Delivery Status" style="padding: 1.25rem 1.5rem;">
                            <span style="font-size: 0.7rem; font-weight: 900; color: white; background: {{ $statusColor }}; padding: 0.35rem 0.8rem; border-radius: 8px; text-transform: uppercase; box-shadow: 0 4px 10px {{ $statusColor }}30; letter-spacing: 0.5px;">
                                {{ $displayStatus }}
                            </span>
                        </td>
                        <td data-label="Return Date" style="padding: 1.25rem 1.5rem;">
                            @php
                                $expectedDates = $item->getExpectedReturnDates();
                            @endphp
                            @if($expectedDates->isNotEmpty())
                                <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                    @foreach($expectedDates as $ed)
                                        @php
                                            $todayStr = \Carbon\Carbon::now()->format('Y-m-d');
                                            $isPastDue = $todayStr >= $ed['date_str'];
                                        @endphp
                                        @if($isPastDue)
                                            <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                                <span style="font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 6px; text-transform: uppercase; white-space: nowrap; border: 1.5px solid rgba(239,68,68,0.3); animation: blink-bg-red 1.5s infinite; display: inline-flex; align-items: center; gap: 4px;" title="Overdue / Due for Return">
                                                    <i data-lucide="alert-circle" style="width: 10px; height: 10px;"></i>
                                                    {{ $ed['formatted'] }}
                                                </span>
                                                @if(!empty($ed['department']))
                                                    <span style="font-size: 0.65rem; font-weight: 800; color: #ef4444; background: rgba(239, 68, 68, 0.08); padding: 2px 6px; border-radius: 4px; text-transform: uppercase; border: 1px solid rgba(239,68,68,0.15);">
                                                        Dept: {{ $ed['department'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                                <span style="font-size: 0.7rem; font-weight: 800; color: #15803d; background: #f0fdf4; border: 1.5px solid #bbf7d0; padding: 0.25rem 0.6rem; border-radius: 6px; text-transform: uppercase; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;" title="Expected Return Date">
                                                    <i data-lucide="clock" style="width: 10px; height: 10px;"></i>
                                                    {{ $ed['formatted'] }}
                                                </span>
                                                @if(!empty($ed['department']))
                                                    <span style="font-size: 0.65rem; font-weight: 800; color: #15803d; background: rgba(21, 128, 61, 0.08); padding: 2px 6px; border-radius: 4px; text-transform: uppercase; border: 1px solid rgba(21, 128, 61, 0.15);">
                                                        Dept: {{ $ed['department'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600;">-</span>
                            @endif
                        </td>
                        <td data-label="Received Qty" style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--text-main);">{{ $item->qty ?? '0' }}</td>
                        <td data-label="Stock Balance" style="padding: 1.25rem 1.5rem; color: var(--text-main); font-weight: 700;">{{ $item->stock_balance }}</td>
                        <td data-label="Variance" style="padding: 1.25rem 1.5rem;">
                            <span style="font-weight: 800; color: {{ is_numeric($item->variance) && (float)$item->variance > 0 ? '#10b981' : (is_numeric($item->variance) && (float)$item->variance < 0 ? '#ef4444' : '#94a3b8') }};">
                                {{ is_numeric($item->variance) && (float)$item->variance > 0 ? '+' : '' }}{{ $item->variance }}
                            </span>
                        </td>


                        <td data-label="Stock Level" style="padding: 1.25rem 1.5rem;">
                            @php
                                $threshold = \App\Models\Setting::getItemThreshold($item->description, $item->ledge_category);
                                $isItemLow = $totalStock <= $threshold;
                                $itemHealthStatus = $isItemLow ? 'LOW STOCK' : 'IN STOCK';
                                $itemHealthColor = $isItemLow ? '#ef4444' : '#10b981';
                            @endphp
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 0.6rem; font-weight: 900; color: white; background: {{ $itemHealthColor }}; padding: 0.2rem 0.5rem; border-radius: 4px; display: inline-block; width: fit-content; text-transform: uppercase;">{{ $itemHealthStatus }}</span>
                                    <i data-lucide="{{ $isItemLow ? 'alert-circle' : 'check-circle' }}" style="width: 14px; color: {{ $itemHealthColor }};"></i>
                                </div>
                                <div style="font-size: 0.85rem; font-weight: 800; color: var(--text-main);">{{ number_format($totalStock) }} <span style="font-size: 0.65rem; color: var(--text-muted);">Available</span></div>
                            </div>
                        </td>

                        @if(!in_array(auth()->user()->role, ['Main Admin', 'Department Head']))
                        <td data-label="Action" style="padding: 1.25rem 1.5rem; text-align: right;">
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                                    @if($isDbPartialDelivery && is_numeric($item->variance) && (float)$item->variance < 0)
                                        @php
                                            $pendingRemainder = \App\Models\EditRequest::where('item_id', $item->batch_id)
                                                ->where('item_type', 'batch')
                                                ->where('request_type', 'remainder_submission')
                                                ->where('status', 'pending')
                                                ->exists();
                                        @endphp
                                        @if($pendingRemainder)
                                            <div title="Awaiting Approval" style="width: 38px; height: 38px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 10px; display: flex; align-items: center; justify-content: center; opacity: 0.7;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                            </div>
                                        @else
                                            <button @if(!auth()->user()->is_admin && !auth()->user()->can_add_inventory) disabled title="Permission Denied" style="opacity: 0.4; cursor: not-allowed; width: 38px; height: 38px; border-radius: 10px; color: #111827; background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.1);" @else onclick="continueDelivery('{{ $item->batch_id }}')" class="action-icon-btn" title="Continue Delivery" style="width: 38px; height: 38px; border-radius: 10px; color: #111827; background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.1);" @endif>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 16h6"/><path d="M19 13v6"/><path d="M21 10V8a2 2 0 0 0-2-2h-6l-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7"/><circle cx="12" cy="12" r="3"/></svg>
                                            </button>
                                        @endif
                                    @endif

                                    @php
                                        $pendingEdit = \App\Models\EditRequest::where('item_id', $item->batch_id)
                                            ->where('item_type', 'batch')
                                            ->whereIn('request_type', ['edit', 'edit_submission'])
                                            ->where('status', 'pending')
                                            ->exists();
                                    @endphp
                                    @if($pendingEdit)
                                        <div title="Edit Pending Approval" style="width: 38px; height: 38px; background: rgba(99, 102, 241, 0.08); color: #6366f1; border-radius: 10px; border: 1px dashed rgba(99, 102, 241, 0.3); display: flex; align-items: center; justify-content: center; opacity: 0.8; cursor: default;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        </div>
                                    @else
                                        <button @if(!auth()->user()->is_admin && !auth()->user()->can_add_inventory) disabled title="Permission Denied" style="opacity: 0.4; cursor: not-allowed; width: 38px; height: 38px; border-radius: 10px; color: #111827; background: rgba(99, 102, 241, 0.05); border: 1px solid rgba(99, 102, 241, 0.1);" @else onclick="openEditBatchModal('{{ $item->batch_id }}')" class="action-icon-btn" title="Edit Entry" style="width: 38px; height: 38px; border-radius: 10px; color: #111827; background: rgba(99, 102, 241, 0.05); border: 1px solid rgba(99, 102, 241, 0.1);" @endif>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                    @endif
                            </div>
                        </td>
                        @endif
        </tr>
        @empty
        <tr>
            <td colspan="{{ in_array(auth()->user()->role, ['Main Admin', 'Department Head']) ? 11 : 12 }}" style="padding: 10rem 2rem; text-align: center; vertical-align: middle;">
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1.5rem; margin: 0 auto;">
                    <div style="background: rgba(99, 102, 241, 0.05); width: 100px; height: 100px; border-radius: 30px; display: flex; align-items: center; justify-content: center; color: var(--primary); border: 2px dashed rgba(99, 102, 241, 0.2); animation: pulse 2s infinite;">
                        <i data-lucide="package-search" style="width: 44px; stroke-width: 1.5px;"></i>
                    </div>
                    <div style="max-width: 500px; text-align: center;">
                        <h4 style="font-size: 1.75rem; font-weight: 950; color: var(--text-main); margin-bottom: 0.75rem; letter-spacing: -0.04em;">No Records Discovered</h4>
                        <p style="color: var(--text-muted); font-size: 1.1rem; line-height: 1.6; font-weight: 500;">Your inventory ledger is currently empty or no items match your current search filters. Try broadening your criteria or record a new batch.</p>

                        <div style="margin-top: 2.5rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                            <a href="{{ route('receiveditems') }}" class="glass-card" style="padding: 0.85rem 1.75rem; border-radius: 14px; text-decoration: none; font-size: 0.95rem; color: var(--text-main); font-weight: 800; transition: all 0.3s; border: 1.5px solid var(--border-color); display: flex; align-items: center; gap: 0.75rem; background: var(--bg-card);">
                                <i data-lucide="refresh-ccw" style="width: 18px;"></i>
                                Reset Filters
                            </a>
                            <button onclick="window.location.href='/'" class="btn-primary" style="padding: 0.85rem 1.75rem; border-radius: 14px; border: none; font-size: 0.95rem; background: var(--primary-gradient); color: white; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 0.75rem; box-shadow: 0 12px 24px -6px rgba(99, 102, 241, 0.4); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                <i data-lucide="plus-circle" style="width: 20px;"></i>
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

    <!-- Bottom Fast Navigate Control Pad -->
    <div style="display: flex; justify-content: flex-end; margin-top: 0.75rem; margin-bottom: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; background: rgba(99, 102, 241, 0.08); padding: 0.25rem 0.5rem; border-radius: 10px; border: 1px dashed rgba(99, 102, 241, 0.3);">
            <button type="button" onclick="document.querySelector('.table-scroll-wrapper').scrollBy({left: -400, behavior: 'smooth'})" style="background: transparent; border: none; cursor: pointer; padding: 0.35rem; border-radius: 6px; display: flex; align-items: center; transition: background 0.3s;" onmouseover="this.style.background='rgba(99, 102, 241, 0.15)'" onmouseout="this.style.background='transparent'" title="Scroll Left">
                <i data-lucide="chevron-left" style="width: 16px; color: var(--primary);"></i>
            </button>
            <div style="display: flex; align-items: center; gap: 6px; padding: 0 0.5rem; user-select: none;">
                <i data-lucide="move-horizontal" style="width: 14px; color: var(--primary); opacity: 0.8;"></i>
                <span style="font-size: 0.7rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px;">Fast Navigate</span>
            </div>
            <button type="button" onclick="document.querySelector('.table-scroll-wrapper').scrollBy({left: 400, behavior: 'smooth'})" style="background: transparent; border: none; cursor: pointer; padding: 0.35rem; border-radius: 6px; display: flex; align-items: center; transition: background 0.3s;" onmouseover="this.style.background='rgba(99, 102, 241, 0.15)'" onmouseout="this.style.background='transparent'" title="Scroll Right">
                <i data-lucide="chevron-right" style="width: 16px; color: var(--primary);"></i>
            </button>
        </div>
    </div>    <!-- Advanced Pagination Footer -->
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

            <div class="pagination-per-page modern-pagination-select">
                <div class="select-icon-left">
                    <i data-lucide="sliders-horizontal"></i>
                </div>
                <select class="per-page-select" onchange="window.location.href=this.value">
                    @foreach([10, 25, 50, 100] as $perPage)
                    <option value="{{ request()->fullUrlWithQuery(['per_page' => $perPage]) }}"
                        {{ request('per_page', 10) == $perPage ? 'selected' : '' }}>
                        Show {{ $perPage }} entries
                    </option>
                    @endforeach
                </select>
                <div class="select-icon-right">
                    <i data-lucide="chevron-down"></i>
                </div>
            </div>
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

<!-- Stock Check / Verification Modal -->
<div id="stockCheckModal" class="modal-backdrop">
    <div class="modal-content glass-card animate-scale-up" style="max-width: 800px; width: 95%;">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1.5rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.25rem;">Stock Verification</h3>
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
                        <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 6px;">Verification Target</label>
                        <div id="auditItemName" style="font-size: 1.4rem; font-weight: 900; color: var(--text-main); line-height: 1;">Broom</div>
                    </div>
                        <button onclick="fetchVerificationHistory()" class="glass-btn-sm" style="border-radius: 12px; font-weight: 700;">
                            <i data-lucide="layout-grid" style="width: 14px; margin-right: 6px;"></i> Full History
                        </button>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="audit-stat-card" onclick="toggleVerificationBreakdown('balance')" title="Drill down into Ledger record">
                        <label>Ledger Balance</label>
                        <div id="auditLedgeBal">0</div>
                    </div>
                    <div class="audit-stat-card" onclick="toggleVerificationBreakdown('stock')" title="Drill down into System record">
                        <label>Stock Balance</label>
                        <div id="auditStockBal">0</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    <div class="audit-stat-card" onclick="toggleVerificationBreakdown('variance')" title="Review historical discrepancies">
                        <label>Prev. Var.</label>
                        <div id="auditPrevVar">0</div>
                    </div>
                    <div class="audit-stat-card" onclick="toggleVerificationBreakdown('avail')" title="Audit current availability trail">
                        <label>Avail. Qty</label>
                        <div id="auditPrevAvail">0</div>
                    </div>
                    <div class="audit-stat-card" style="border-color: rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.02);" title="Package Types currently out on temporary loan">
                        <label style="color: #f59e0b;">Active Loans</label>
                        <div id="auditActiveLoans" style="color: #f59e0b;">0</div>
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
                            <option value="Donor" data-icon="heart" data-color="#8b5cf6">Donation</option>
                            <option value="Missing">Missing</option>
                            <option value="Damaged">Damaged</option>
                            <option value="Found">Found</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div style="grid-column: span 2;">
                        <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Verifier Remarks / Variance Documentation</label>
                        <textarea id="auditNotes" placeholder="Describe the situation, location, or damage details in depth..." style="width: 100%; height: 80px; padding: 0.85rem; border: 1px solid var(--border-color); border-radius: 12px; background: var(--bg-card); color: var(--text-main); font-family: inherit; resize: none; outline: none; transition: all 0.3s;" onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(99, 102, 241, 0.1)'" onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'"></textarea>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 1.1rem; border-radius: 18px; border: none; background: var(--primary-gradient); color: white; font-weight: 900; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2); transition: all 0.3s;">
                        <i data-lucide="shield-check" style="width: 18px;"></i>
                        Seal Record
                    </button>

                        <button type="button" onclick="generateVerificationReport()" id="genRepBtn" class="glass-btn audit-report-btn" style="width: 100%; padding: 1.1rem; border-radius: 18px; display: flex; align-items: center; justify-content: center; gap: 10px; font-weight: 800; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); color: #ffffff; cursor: pointer; transition: all 0.3s; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
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
                        <span style="font-size: 0.65rem; color: var(--text-main); font-weight: 800; background: rgba(99, 102, 241, 0.1); padding: 4px 10px; border-radius: 100px;">Internal Verification Mode</span>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.7rem; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Editable Verifier Narrative</label>
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

    /* Modern Pagination Dropdown Design */
    .modern-pagination-select {
        position: relative;
        display: inline-flex;
        align-items: center;
        background: rgba(99, 102, 241, 0.04);
        border: 1.5px solid rgba(99, 102, 241, 0.15);
        border-radius: 999px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .modern-pagination-select:hover {
        background: rgba(99, 102, 241, 0.08);
        border-color: rgba(99, 102, 241, 0.3);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(99, 102, 241, 0.1);
    }

    .modern-pagination-select:focus-within {
        background: var(--bg-main);
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }

    .modern-pagination-select .select-icon-left {
        position: absolute;
        left: 16px;
        color: var(--primary);
        pointer-events: none;
        display: flex;
        align-items: center;
        opacity: 0.9;
    }

    .modern-pagination-select .select-icon-left i {
        width: 14px;
        height: 14px;
    }

    .modern-pagination-select select {
        appearance: none;
        -webkit-appearance: none;
        background: transparent;
        border: none;
        color: var(--text-main);
        font-weight: 800;
        font-size: 0.8rem;
        padding: 0.65rem 3rem 0.65rem 2.5rem;
        cursor: pointer;
        outline: none;
        width: 100%;
        font-family: inherit;
        letter-spacing: 0.2px;
    }

    .modern-pagination-select select option {
        background: var(--bg-main);
        color: var(--text-main);
        font-weight: 600;
    }

    .modern-pagination-select .select-icon-right {
        position: absolute;
        right: 16px;
        background: var(--bg-card);
        width: 20px;
        height: 20px;
        border-radius: 50%;
        color: var(--text-muted);
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
    }

    .modern-pagination-select .select-icon-right i {
        width: 12px;
        height: 12px;
    }

    .modern-pagination-select:focus-within .select-icon-right {
        transform: rotate(180deg);
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
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
        overflow-x: auto !important;
        margin-bottom: 1.5rem;
        border-radius: 18px;
        padding-bottom: 15px; /* Space for visible scrollbar */
    }

    /* Style the scrollbar for visibility */
    .table-scroll-wrapper::-webkit-scrollbar {
        height: 10px;
        display: block;
    }
    .table-scroll-wrapper::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.02);
        border-radius: 10px;
    }
    .table-scroll-wrapper::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 10px;
        opacity: 0.5;
    }

    /* Modal Architecture */
    .modal-backdrop {
        position: fixed;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(2, 6, 23, 0.7);
        backdrop-filter: blur(12px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 99999 !important;
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

    /* Icon-only action buttons — keep icon dark on hover */
    .action-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .action-icon-btn:hover {
        transform: translateY(-2px) scale(1.08);
        box-shadow: 0 6px 16px rgba(99, 102, 241, 0.18);
        color: #111827 !important;
        background: rgba(99, 102, 241, 0.12) !important;
        border-color: rgba(99, 102, 241, 0.25) !important;
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


    let continueBatchData = null;

    function continueDelivery(batchId) {
        // Hide standard detail modal if open
        // Hide any active portals
        const portal = document.getElementById('action-menu-portal');
        if (portal) portal.remove();

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

        fetch(`{{ url('/received-items') }}/${batchId}?json=true`)
            .then(response => {
                if (!response.ok) throw new Error(`Server error: ${response.status}`);
                return response.json();
            })
            .then(data => {
                try {
                    continueBatchData = data.batch;
                    subtitle.innerText = `Original Transaction: #${continueBatchData.id} • ${new Date(continueBatchData.entry_date).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: '2-digit' })}`;

                    const sourceName = (continueBatchData.supplier_name || continueBatchData.donor_name || 'Unknown Source').replace(/\[.*?\]/g, '').trim();

                    let html = `
                        <div style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 16px; padding: 1.5rem; margin-bottom: 0.5rem;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800; margin-bottom: 4px;">Supplier Name</div>
                                    <div style="font-weight: 800; color: var(--text-main);">${sourceName}</div>
                                </div>
                                <div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800; margin-bottom: 4px;">Category</div>
                                    <div style="font-weight: 800; color: var(--primary);">${ledgeMap[continueBatchData.ledge_category] || continueBatchData.ledge_category}</div>
                                </div>
                            </div>
                        </div>
                    `;

                    let hasPending = false;

                    continueBatchData.items.forEach((item, index) => {
                        const variance = parseFloat(item.variance) || 0;
                        const outstanding = -variance; // negative variance = shortfall = outstanding

                        if (outstanding <= 0) return; // Full quantity has been brought

                        hasPending = true;
                        const stockBalance = parseFloat(item.stock_balance) || 0;
                        const expected = stockBalance + outstanding;

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
                                    <span style="font-weight: 900; color: #10b981; font-size: 1rem;">${stockBalance}</span>
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800;">Pending Deficit</span>
                                    <span style="font-weight: 900; color: #ef4444; font-size: 1rem;">${outstanding}</span>
                                </div>
                            </div>

                            <div style="margin-top: 0.5rem;">
                                <label style="font-size: 0.75rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-bottom: 8px; display: block;">Newly Received Quantity</label>
                                <input type="number" class="continue-input" data-item-id="${item.id}" max="${outstanding}" min="0" placeholder="Enter additional package types received..."
                                    style="width: 100%; padding: 1.15rem; border-radius: 12px; border: 2px solid var(--border-color); font-size: 1rem; font-weight: 700; background: var(--bg-card); color: var(--text-main); transition: all 0.3s;"
                                    oninput="previewRecalculation(${item.id}, ${expected}, ${item.stock_balance}, ${stockBalance}, ${outstanding})">
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
                } catch(parseErr) {
                    /* console print removed */
                    body.innerHTML = `<div style="padding: 2rem; text-align: center; color: #ef4444;">
                        <strong>Error loading batch data.</strong><br>
                        <small style="color: #94a3b8;">${parseErr.message}</small>
                    </div>`;
                }
            })
            .catch(err => {
                /* console print removed */
                subtitle.innerText = `Error loading Batch #${batchId}`;
                body.innerHTML = `<div style="padding: 2rem; text-align: center; color: #ef4444;">
                    <strong>Failed to retrieve batch data.</strong><br>
                    <small style="color: #94a3b8;">${err.message}</small>
                </div>`;
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
            const response = await fetch('{{ url("/api/inventory/receive-remainder") }}', {
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
                if (result.is_pending) {
                    Swal.fire({
                        title: 'Request Submitted',
                        text: result.message || 'Waiting for administrator approval.',
                        icon: 'info',
                        confirmButtonColor: '#f59e0b'
                    });
                } else {
                    performSearch(true); // Silent refresh to show updated values on UI
                    Swal.fire({
                        title: 'Inventory Updated',
                        text: 'Remainder items have been added to stock.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            } else {
                alert('Execution Error: ' + (result.message || 'Unknown error occurred.'));
            }
        } catch (error) {
            /* console print removed */
            alert('A critical system error occurred during submission.');
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
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
                /* console print removed */
                if (!isSilent) resultsContainer.style.opacity = '1';
            });
    }

    // Background Sync Engine (Silent Pulse) removed as per user request

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

            // Show prevBtn only if pills are hidden to the left
            prevBtn.style.opacity = scrollLeft > 2 ? '1' : '0';
            prevBtn.style.pointerEvents = scrollLeft > 2 ? 'auto' : 'none';

            // Show nextBtn only if pills are hidden to the right
            nextBtn.style.opacity = maxScroll > 2 && scrollLeft < maxScroll - 2 ? '1' : '0';
            nextBtn.style.pointerEvents = maxScroll > 2 && scrollLeft < maxScroll - 2 ? 'auto' : 'none';
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
            if (!event.target.closest('#action-menu-portal') && !event.target.closest('.glass-btn-sm')) {
                const portal = document.getElementById('action-menu-portal');
                if (portal) portal.remove();
            }
        });

        // Ensure menus close when scrolling inside scrollable areas
        window.addEventListener('scroll', function() {
            const portal = document.getElementById('action-menu-portal');
            if (portal) portal.remove();
        }, true);
    });

    // Toggle Action Menu Logic (Bulletproof Portal)
    function toggleActionMenu(menuId, button, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const btn = button;
        if (!btn) return;

        // Clean up any existing deployed menu
        const existingPortal = document.getElementById('action-menu-portal');
        if (existingPortal) {
            const isSame = existingPortal.getAttribute('data-menu-id') === String(menuId);
            existingPortal.remove();
            if (isSame) return; // Toggle off if same
        }

        // Find the original menu template in the table
        const templateMenu = document.getElementById(`actionMenu-${menuId}`);
        if (!templateMenu) return;

        // Clone and deploy to body to escape table overflow and backdrop-filter containing blocks
        const portalMenu = templateMenu.cloneNode(true);
        portalMenu.id = 'action-menu-portal';
        portalMenu.setAttribute('data-menu-id', menuId);

        // Force visibility
        portalMenu.style.display = 'flex';
        portalMenu.classList.add('active');

        // Ensure menu closes when an option is selected
        portalMenu.addEventListener('click', (e) => {
            if (e.target.closest('.menu-item')) {
                setTimeout(() => portalMenu.remove(), 50);
            }
        });

        // Append to body FIRST so we can accurately measure/position it if needed
        document.body.appendChild(portalMenu);

        // Positioning relative to viewport
        const rect = btn.getBoundingClientRect();
        portalMenu.style.position = 'fixed';
        portalMenu.style.top = (rect.bottom + 8) + 'px';
        portalMenu.style.left = 'auto';

        // Avoid using innerWidth due to scrollbar width inconsistencies.
        // Calculate left position based on the right edge of the button minus the menu width.
        const menuWidth = portalMenu.offsetWidth || 200;
        let leftPos = rect.right - menuWidth;

        // Ensure it doesn't go off the left edge of the screen
        if (leftPos < 10) leftPos = 10;

        portalMenu.style.left = leftPos + 'px';
        portalMenu.style.right = 'auto'; // Clear right
        portalMenu.style.zIndex = '999999';
    }

    async function deleteBatch(batchId) {
        // Strict Authorization: Check for administrative approval or role
        try {
            const checkRes = await fetch(`{{ url('/edit-requests/status') }}/${batchId}?type=delete`);
            const checkData = await checkRes.json();

            if (!checkData.allowed) {
                if (checkData.status === 'pending') {
                    Swal.fire('Session Pending', 'A deletion request for this record is currently awaiting administrative authorization.', 'info');
                    return;
                } else if (checkData.status === 'expired') {
                    Swal.fire({
                        title: 'Clearance Expired',
                        text: 'Your 62-second security clearance window has closed. You must re-authenticate with the head.',
                        icon: 'error',
                        showCancelButton: true,
                        confirmButtonText: 'Request New Clearance'
                    }).then((result) => {
                        if (result.isConfirmed) promptActionReason(batchId, 'delete');
                    });
                    return;
                } else if (checkData.status === 'canceled') {
                    Swal.fire({
                        title: 'Clearance Denied',
                        text: 'Your previous request to purge this record was denied by the head. Do you wish to re-submit with new justification?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Re-submit Request'
                    }).then((result) => {
                        if (result.isConfirmed) promptActionReason(batchId, 'delete');
                    });
                    return;
                } else {
                    // No request exists yet
                    promptActionReason(batchId, 'delete');
                    return;
                }
            }
        } catch (err) {
            /* console print removed */
        }

        // Proceed if authorized
        if (!confirm('⚠️ SYSTEM CAUTION:\n\nYou are about to permanently purge this batch record and all its associated product logs from the database.\n\nThis action is forensic and IRREVERSIBLE. Do you wish to proceed?')) {
            return;
        }

        try {
            const response = await fetch(`{{ url('/received-items') }}/${batchId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                // Success: record was purged
                performSearch(true);
                // Also clear any approved requests as they are now used
                Swal.fire('Purged', 'Batch record has been forensically removed from the registry.', 'success');
            } else {
                alert('Purge Error: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            /* console print removed */
            alert('A critical system error occurred during the transaction purge.');
        }
    }

    function openStockCheckModal(description, ledgeBal, stockBal, prevVar, prevAvail) {
        document.getElementById('auditItemName').innerText = description;
        document.getElementById('auditLedgeBal').innerText = ledgeBal;
        document.getElementById('auditStockBal').innerText = stockBal;
        document.getElementById('auditPrevVar').innerText = prevVar || '0';
        document.getElementById('auditPrevAvail').innerText = prevAvail || '0';
        document.getElementById('auditActiveLoans').innerText = '...';

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
            const response = await fetch(`{{ url('/api/item-audit-details') }}?description=${encodeURIComponent(description)}`);
            const data = await response.json();

            auditHistoryData = data.batches;
            document.getElementById('auditActiveLoans').innerText = data.on_loan || '0';

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
        const onLoan = parseFloat(document.getElementById('auditActiveLoans').innerText) || 0;
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

        // Smart Insight Engine (factor in active loans)
        const totalAccounted = physical + onLoan;
        if (variance === 0) {
            display.style.color = '#10b981';
            indicatorFill.style.background = '#10b981';
            insight.style.background = 'rgba(16, 185, 129, 0.1)';
            insight.style.color = '#10b981';
            insight.innerHTML = `<i data-lucide="check-sparkles"></i> <span>Perfect Audit! Physical matches System.</span>`;
        } else if (totalAccounted === stockBal) {
            display.style.color = '#f59e0b';
            indicatorFill.style.background = '#f59e0b';
            insight.style.background = 'rgba(245, 158, 11, 0.1)';
            insight.style.color = '#d97706';
            insight.innerHTML = `<i data-lucide="info"></i> <span>Technically Balanced: ${physical} present + ${onLoan} on loan = ${stockBal} expected.</span>`;
        } else if (variance < 0) {
            display.style.color = '#ef4444';
            indicatorFill.style.background = '#ef4444';
            insight.style.background = 'rgba(239, 68, 68, 0.1)';
            insight.style.color = '#ef4444';
            insight.innerHTML = `<i data-lucide="alert-triangle"></i> <span>Shortage: Even with ${onLoan} on loan, ${Math.abs(stockBal - totalAccounted)} package types are still missing.</span>`;
        } else {
            display.style.color = '#3b82f6';
            indicatorFill.style.background = '#3b82f6';
            insight.style.background = 'rgba(59, 130, 246, 0.1)';
            insight.style.color = '#3b82f6';
            insight.innerHTML = `<i data-lucide="package-plus"></i> <span>Surplus Noted: ${variance} extra package types identified beyond system expectations.</span>`;
        }
        insight.style.display = 'flex';

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function renderAuditBreakdown(type) {
        const container = document.getElementById('auditHistoryContent');
        const title = document.getElementById('breakdownTitle');

        let label = "";
        let key = "";
        let icon = "list";

        switch (type) {

            case 'stock':
                label = "Category Trail";
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
        const description = document.getElementById('auditItemName').innerText;
        const physicalCount = document.getElementById('physicalCount').value;
        const condition = document.getElementById('auditReason').value;
        const remarks = document.getElementById('auditNotes').value;

        if (!physicalCount || physicalCount === '') {
            showToast('Verification Failed', 'Please specify a physical count first.', 'error');
            return;
        }

        if (!condition) {
            showToast('Verification Failed', 'Please select a verification condition (Status).', 'error');
            return;
        }

        const submitBtn = document.querySelector('#stockCheckForm button[type="submit"]');
        const originalHtml = submitBtn.innerHTML;
        const isAdmin = {{ auth()->user()->is_admin ? 'true' : 'false' }};
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<div class="loader" style="width: 14px; height: 14px; border-width: 2px; border-color: white;"></div> ${isAdmin ? 'Sealing Record...' : 'Submitting Request...'}`;

        fetch("{{ route('stockcheck.verify') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                description: description,
                physical_count: physicalCount,
                condition: condition,
                remarks: remarks
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('Server protocol violation');
            return res.json();
        })
        .then(data => {
            if (data.success) {
                closeStockCheckModal();
                Swal.fire({
                    title: 'Verification Complete',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: 'var(--primary)'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                showToast('Verification Failed', data.message || 'An unexpected error occurred.', 'error');
            }
        })
        .catch(err => {
            /* console print removed */
            showToast('Network Error', 'Could not establish connection to logistics servers.', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        });
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
        const loanDisplay = document.getElementById('auditActiveLoans').innerText;

        if (auditHistoryData.length === 0 || loanDisplay === '...') {
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

        const onLoan = parseFloat(document.getElementById('auditActiveLoans').innerText) || 0;

        // Editable Narrative Body
        let narrative = `This audit was conducted to verify the physical existence and condition of the inventory item: ${item.toUpperCase()}.\n\n`;
        narrative += `Based on the physical verification conducted on ${dateStr} at exactly ${timeStr}, the following observations were recorded regarding the inventory state:\n\n`;

        if (onLoan > 0) {
            narrative += `[NOTICE] System records indicate ${onLoan} package types are currently out on temporary loan. This active allocation explains why the physical count (${physical}) is lower than the system stock (${stockBal}).\n\n`;
        }

        narrative += `[AUDITOR NOTES]\n`;
        narrative += `${remarks}\n\n`;
        narrative += `The condition of the items has been categorized as "${reason}". This assessment reflects the current physical quality and storage situation for this batch.`;

        // Uneditable Conclusion Content
        let conclusion = `<div style="color: #000; font-weight: 800; font-size: 1.1rem; margin-bottom: 0.5rem;">[AUDIT CONCLUSION]</div>`;
        const varValue = parseFloat(variance);
        const totalAccounted = parseFloat(physical) + onLoan;

        if (varValue === 0) {
            conclusion += `<div style="color: #10b981;">SUCCESS: No variance detected. Physical stock perfectly aligns with system ledger records.</div>`;
        } else if (totalAccounted === parseFloat(stockBal)) {
            conclusion += `<div style="color: #f59e0b;">BALANCED: System registry is balanced. Although physical stock is lower, all ${Math.abs(varValue)} missing package types are accounted for via active temporary loans.</div>`;
        } else if (totalAccounted < parseFloat(stockBal)) {
            const netShortage = parseFloat(stockBal) - totalAccounted;
            conclusion += `<div style="color: #ef4444;">WARNING: A shortage of ${netShortage} package types has been identified. Even after accounting for ${onLoan} package types on loan, the system remains unreconciled. Immediate investigation required.</div>`;
        } else {
            conclusion += `<div style="color: #3b82f6;">NOTICE: A surplus of ${varValue} package types has been discovered. Update requested for system ledger records to incorporate identified surplus.</div>`;
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
                    <title>Narcotics Control Commission | Transaction Log</title>
                    <link href="{{ asset('css/css2.css') }}" rel="stylesheet">
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

                    <div class="meta-grid" style="grid-template-columns: repeat(5, 1fr);">
                        <div class="meta-item"><label>Subject Material</label><div>${item}</div></div>
                        <div class="meta-item"><label>Date of Audit</label><div>${dateStr}</div></div>
                        <div class="meta-item"><label>Time of Verification</label><div>${timeStr}</div></div>
                        <div class="meta-item"><label>Active Loans (Temp)</label><div style="color: #f59e0b;">${document.getElementById('auditActiveLoans').innerText}</div></div>
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
                                    <span style="font-weight: 900; color: #000;">${physical} Package Types</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                    <span style="font-size: 0.7rem; font-weight: 800;">ACTIVE LOANS (TEMP):</span>
                                    <span style="font-weight: 900; color: #f59e0b;">${document.getElementById('auditActiveLoans').innerText} Package Types</span>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="font-size: 0.7rem; font-weight: 800;">AUDIT VARIANCE:</span>
                                    <span style="font-weight: 900; color: ${parseFloat(variance) < 0 ? '#ef4444' : '#10b981'};">${variance} Package Types</span>
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
                    <h3 id="editModalTitle" style="font-size: 1.25rem; font-weight: 800; margin: 0; color: white; letter-spacing: -0.02em;">Edit Item</h3>
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
                    <div id="editBatchInfo"></div>
                    <!-- Metadata Section -->
                    <div style="margin-bottom: 2.5rem;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 1.5rem;">
                            <span style="font-size: 0.75rem; font-weight: 900; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.1em;">Received Details</span>
                            <div style="flex: 1; height: 1px; background: #f1f5f9;"></div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                            <div class="input-group">
                                <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Received Date</label>
                                <div style="position: relative;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
                                    <input type="date" name="arrival_date" id="editArrivalDate" required style="width: 100%; padding: 0.85rem 1rem 0.85rem 2.5rem; border: 1.5px solid #e2e8f0; border-radius: 12px; font-weight: 700; color: #1e293b; background: #f8fafc;">
                                </div>
                            </div>

                            <div class="input-group">
                                <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Category</label>
                                <select disabled name="ledge_category" id="editCategory" required class="custom-premium-select">
                                    @foreach($ledgeMap as $code => $name)
                                    <option value="{{ $code }}">CAT {{ $code }} | {{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="input-group">
                                <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Delivery Status</label>
                                <select name="acquisition_status" id="editAcquisitionStatus" required onchange="toggleEditSourceFields()" class="custom-premium-select">
                                    <option value="Full Delivery">Full Delivery</option>
                                    <option value="Partial Delivery">Partial Delivery</option>
                                    <option value="Donor">Donation</option>
                                </select>
                            </div>

                            <div id="editSupplierField" class="input-group">
                                <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Supplier/Donor Name</label>
                                <select name="supplier_name" id="editSupplierName" style="width: 100%;" class="select2-edit">
                                    <option value="">Select supplier/donor...</option>
                                    @foreach($allSuppliers as $supplier)
                                    <option value="{{ $supplier }}">{{ $supplier }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="editDonorField" class="input-group" style="display: none;">
                                <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Supplier/Donor Name</label>
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
                                <span style="font-size: 0.75rem; font-weight: 900; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.1em;">Item Information</span>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19 7-7-7-7"/><path d="M5 19l7-7-7-7"/></svg> {{ auth()->user()->is_admin ? 'Commit Changes' : 'Submit for Approval' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentEditBatchId = null;
let originalBatchData = null;

function openEditBatchModal(batchId) {
    // AS PER USER REQUEST: Allow personnel to edit first, then send for approval.
    // We no longer block the modal opening for personnel.
    _openEditBatchModal(batchId);
}

function promptEditReason(batchId) {
    promptActionReason(batchId, 'edit');
}

function promptActionReason(batchId, type = 'edit') {
    const title = type === 'edit' ? 'Request Edit Access' : 'Request Deletion Clearance';
    const label = type === 'edit' ? 'Justification for modification' : 'Justification for record purging';

    Swal.fire({
        title: title,
        input: 'textarea',
        inputLabel: label,
        inputPlaceholder: 'Type your detailed reason here...',
        showCancelButton: true,
        confirmButtonText: 'Submit Request',
        confirmButtonColor: type === 'delete' ? '#ef4444' : '#4f46e5',
        inputValidator: (value) => {
            if (!value) {
                return 'Justification is mandatory for audit trails!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit request
            fetch('{{ url("/edit-requests") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    item_id: batchId,
                    reason: result.value,
                    request_type: type
                })
            })
            .then(res => {
                if (!res.ok) throw new Error('Secure line failure');
                return res.json();
            })
            .then(data => {
                if(data.success) {
                    Swal.fire('Transmission Successful', `Your ${type} request has been synchronized with the head's terminal.`, 'success');
                } else {
                    Swal.fire('System Rejection', data.message || 'Failed to transmit request.', 'error');
                }
            })
            .catch(err => {
                /* console print removed */
                Swal.fire('Protocol Error', 'Failed to establish connection with the approval server.', 'error');
            });
        }
    });
}

function _openEditBatchModal(batchId, expiresIn = 62) {
    currentEditBatchId = batchId;

    // Timer stopped as per user request once editor is accessed
    const saveBtn = document.getElementById('saveEditBtn');
    saveBtn.disabled = false;
    saveBtn.style.background = 'var(--primary)';
    const isAdmin = {{ auth()->user()->is_admin ? 'true' : 'false' }};
    saveBtn.innerHTML = isAdmin ? '<i data-lucide="save" style="width: 18px;"></i> Save Changes' : '<i data-lucide="send" style="width: 18px;"></i> Submit for Approval';

    const modal = document.getElementById('editBatchModal');
    const loader = document.getElementById('editModalLoader');
    const content = document.getElementById('editModalContent');
    const title = document.getElementById('editModalSubtitle');

    title.innerText = `#BATCH-${batchId.toString().padStart(4, '0')}`;
    modal.style.display = 'flex';
    loader.style.display = 'flex';
    content.style.display = 'none';

    // Initialize Select2 with tags
    $('.select2-edit').select2({
        dropdownParent: $('#editBatchModal'),
        tags: true,
        width: '100%'
    });

    fetch(`{{ url('/received-items') }}/${batchId}?json=1`)
        .then(res => {
            if (!res.ok) throw new Error('Data retrieval failed');
            return res.json();
        })
        .then(data => {
            const batch = data.batch;
            originalBatchData = JSON.parse(JSON.stringify(batch));
            document.getElementById('editArrivalDate').value = batch.arrival_date ? batch.arrival_date.split(' ')[0] : '';
            document.getElementById('editCategory').value = batch.ledge_category;

            let supplierName = batch.supplier_name || '';
            let status = batch.supplier_status || 'Full Delivery';

            document.getElementById('editAcquisitionStatus').value = status;

            // Set Select2 values
            if (status === 'Donor') {
                $('#editDonorName').val(batch.donor_name).trigger('change');
            } else {
                $('#editSupplierName').val(supplierName).trigger('change');
            }

            toggleEditSourceFields();

            const itemsList = document.getElementById('editItemsList');
            itemsList.innerHTML = '';

            document.getElementById('editItemsCountLabel').innerText = `${batch.items.length} Items Identified`;
            batch.items.forEach((item, index) => {
                const stock = parseFloat(item.stock_balance);
                const threshold = {{ \App\Models\Setting::get('low_stock_threshold', 100) }};
                const healthColor = stock <= 0 ? '#ef4444' : (stock <= threshold ? '#f59e0b' : '#10b981');

                const itemHtml = `
                    <div class="edit-item-card" data-id="${item.id}" style="background: #ffffff; padding: 1.5rem; border: 1.5px solid #f1f5f9; border-radius: 16px; transition: 0.3s; position: relative;">
                        <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: ${healthColor};"></div>
                        <input type="hidden" class="item-id" value="${item.id}">
                        <input type="hidden" class="item-stock-balance" value="${item.stock_balance}">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 1.25rem;">
                            <div style="background: #eff6ff; color: #3b82f6; width: 24px; height: 24px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 900;">${index + 1}</div>
                            <input type="text" class="item-description" value="${item.description}" placeholder="Asset Description" style="flex: 1; border: none; background: transparent; font-size: 0.95rem; font-weight: 800; color: #1e293b; outline: none; padding: 4px 0; border-bottom: 2px solid transparent; transition: 0.3s;" onfocus="this.style.borderBottomColor='#4f46e5'">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                            <div>
                                <label style="display: block; font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Package Type</label>
                                <input type="text" class="item-unit" value="${item.unit}" disabled style="width: 100%; padding: 0.85rem; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.85rem; font-weight: 700; color: #94a3b8; background: #f8fafc; cursor: not-allowed;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Qty Received</label>
                                <input type="number" class="item-qty" value="${item.qty}" oninput="recalcEditVariance(this)" style="width: 100%; padding: 0.85rem; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.85rem; font-weight: 900; color: #1e293b;">
                            </div>

                        </div>

                        <div style="display: grid; grid-template-columns: 100px 1fr; gap: 1rem; align-items: flex-end;">
                            <div>
                                <label style="display: block; font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Variance</label>
                                <input type="number" class="item-variance" value="${item.variance}" readonly style="width: 100%; padding: 0.85rem; border: none; background: #f8fafc; border-radius: 12px; font-size: 0.85rem; font-weight: 900; color: ${item.variance < 0 ? '#ef4444' : '#10b981'}; text-align: center;">
                            </div>
                            <div style="position: relative;">
                                <label style="display: block; font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">User Remarks</label>
                                <textarea class="item-remarks" style="width: 100%; height: 42px; padding: 0.85rem; border: 1px solid #e2e8f0; border-radius: 12px; resize: none; font-size: 0.8rem; font-weight: 600; color: #64748b; background: #f8fafc;">${item.remarks || ''}</textarea>
                            </div>
                        </div>
                    </div>
                `;
                itemsList.insertAdjacentHTML('beforeend', itemHtml);
            });

            // Update Entry Info Panel
            const infoPanel = document.getElementById('editBatchInfo');
            infoPanel.innerHTML = `
                <div style="margin-bottom: 1.5rem; padding: 1.25rem; background: rgba(99, 102, 241, 0.03); border: 1px dashed rgba(99, 102, 241, 0.2); border-radius: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i data-lucide="info" style="width: 20px; color: var(--primary);"></i>
                        <div>
                            <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Entry Date:</span>
                            <span style="font-size: 0.85rem; font-weight: 800; color: var(--text-main); margin-left: 8px;">${new Date(batch.entry_date).toLocaleString()}</span>
                        </div>
                    </div>

                </div>
            `;

            loader.style.display = 'none';
            content.style.display = 'block';
            lucide.createIcons();
        });
}

function closeEditBatchModal() {
    if (currentEditBatchId) {
        fetch(`{{ url('/edit-requests/complete') }}/${currentEditBatchId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
    }
    document.getElementById('editBatchModal').style.display = 'none';
}

function toggleEditSourceFields() {
    const status = document.getElementById('editAcquisitionStatus').value;
    const supplier = document.getElementById('editSupplierField');
    const donor = document.getElementById('editDonorField');

    if (status === 'Donor') {
        supplier.style.display = 'none';
        donor.style.display = 'block';
    } else {
        supplier.style.display = 'block';
        donor.style.display = 'none';
    }
}

function recalcEditVariance(input) {
    const row = input.closest('.edit-item-card');
    const qty = parseFloat(row.querySelector('.item-qty').value) || 0;

    // We update both the variance and the hidden stock balance
    // because usually an edit to an entry is to correct the actual quantity received
    const stockInput = row.querySelector('.item-stock-balance');
    if (stockInput) {
        stockInput.value = qty;
    }

    const stock = parseFloat(stockInput.value) || 0;
    const variance = stock - qty;

    const varInput = row.querySelector('.item-variance');
    varInput.value = variance;

    // Visual feedback for variance
    if (variance > 0) {
        varInput.style.color = '#10b981'; // Green for surplus
        varInput.style.background = 'rgba(16, 185, 129, 0.1)';
    } else if (variance < 0) {
        varInput.style.color = '#ef4444'; // Red for shortage
        varInput.style.background = 'rgba(239, 68, 68, 0.1)';
    } else {
        varInput.style.color = '#4f46e5';
        varInput.style.background = 'rgba(99, 102, 241, 0.05)';
    }
}

function hasModifiedAnything(currentPayload, original) {
    if (!original) return true;

    // 1. Compare batch level metadata
    const origArrivalDate = original.arrival_date ? original.arrival_date.split(' ')[0] : '';
    if (currentPayload.arrival_date !== origArrivalDate) return true;
    if (currentPayload.ledge_category !== original.ledge_category) return true;
    if (currentPayload.supplier_status !== (original.supplier_status || 'Full Delivery')) return true;

    if (currentPayload.supplier_status === 'Donor') {
        if ((currentPayload.donor_name || '') !== (original.donor_name || '')) return true;
    } else {
        if ((currentPayload.supplier_name || '') !== (original.supplier_name || '')) return true;
    }

    // 2. Compare items length
    if (currentPayload.items.length !== original.items.length) return true;

    // 3. Compare each item
    for (let i = 0; i < currentPayload.items.length; i++) {
        const currItem = currentPayload.items[i];
        const origItem = original.items.find(item => item.id == currItem.id);
        if (!origItem) return true;

        if (currItem.description.trim() !== (origItem.description || '').trim()) return true;
        if (currItem.unit.trim() !== (origItem.unit || '').trim()) return true;
        if (parseFloat(currItem.qty) !== parseFloat(origItem.qty || 0)) return true;
        if (parseFloat(currItem.stock_balance) !== parseFloat(origItem.stock_balance || 0)) return true;
        if ((currItem.remarks || '').trim() !== (origItem.remarks || '').trim()) return true;
    }

    return false;
}

async function submitEditBatch() {
    const saveBtn = document.getElementById('saveEditBtn');
    const isAdmin = {{ auth()->user()->is_admin ? 'true' : 'false' }};

    const items = [];
    const itemsContainer = document.getElementById('editItemsList');
    itemsContainer.querySelectorAll('.edit-item-card').forEach(row => {
        const itemId = row.querySelector('.item-id').value;
        const itemQty = row.querySelector('.item-qty').value;
        const itemStock = row.querySelector('.item-stock-balance')?.value || itemQty;

        items.push({
            id: itemId,
            description: row.querySelector('.item-description').value,
            unit: row.querySelector('.item-unit').value,
            qty: itemQty,
            stock_balance: itemStock,
            variance: row.querySelector('.item-variance').value,
            remarks: row.querySelector('.item-remarks').value
        });
    });

    const supplierStatus = document.getElementById('editAcquisitionStatus').value;
    let acqType = supplierStatus === 'Donor' ? 'Donor' : 'Supplier';
    let supplierName = $('#editSupplierName').val() || '';

    const payload = {
        arrival_date: document.getElementById('editArrivalDate').value,
        ledge_category: document.getElementById('editCategory').value,
        acquisition_type: acqType,
        supplier_name: supplierName || null,
        supplier_status: supplierStatus,
        donor_name: supplierStatus === 'Donor' ? $('#editDonorName').val() : '',
        items: items,
        _token: '{{ csrf_token() }}'
    };

    if (originalBatchData && !hasModifiedAnything(payload, originalBatchData)) {
        Swal.fire({
            title: 'No Changes Detected',
            text: 'You have not modified anything. Please make at least one change before submitting.',
            icon: 'warning',
            confirmButtonColor: '#4f46e5'
        });
        return;
    }

    // IF NOT ADMIN, ASK FOR REASON FIRST
    let reason = "Direct administrative modification.";
    if (!isAdmin) {
        const { value: text, isConfirmed } = await Swal.fire({
            html: `
                <div style="text-align: left;">
                    <div style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); margin: -1.25em -1.25em 1.5em; padding: 2rem 2rem 1.5rem; border-radius: 4px 4px 0 0; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: rgba(255,255,255,0.06); border-radius: 50%;"></div>
                        <div style="position: absolute; bottom: -30px; left: -10px; width: 80px; height: 80px; background: rgba(255,255,255,0.04); border-radius: 50%;"></div>
                        <div style="display: flex; align-items: center; gap: 14px; position: relative;">
                            <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.15); border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg style="width: 26px; height: 26px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <div>
                                <div style="font-size: 0.7rem; font-weight: 800; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 3px;">Oversight Protocol</div>
                                <div style="font-size: 1.3rem; font-weight: 900; color: white; letter-spacing: -0.02em;">Edit Justification</div>
                            </div>
                        </div>
                    </div>

                    <p style="font-size: 0.9rem; color: #64748b; line-height: 1.6; margin-bottom: 1.25rem; padding: 0 0.25rem;">
                        Explain why you are modifying this record. Your justification will be reviewed by an administrator before changes take effect.
                    </p>

                    <textarea id="swal-edit-justification" placeholder="e.g., Correcting a quantity typo, updating supplier info after delivery confirmation..." style="width: 100%; min-height: 110px; font-size: 0.9rem; border-radius: 14px; border: 2px solid #f1f5f9; padding: 1rem 1.25rem; font-family: inherit; resize: vertical; outline: none; transition: all 0.3s; box-sizing: border-box; color: #0f172a; background: #f8fafc;" onfocus="this.style.borderColor='#4f46e5'; this.style.boxShadow='0 0 0 4px rgba(79,70,229,0.08)'" onblur="this.style.borderColor='#f1f5f9'; this.style.boxShadow='none'"></textarea>

                    <div style="margin-top: 1rem; padding: 10px 14px; background: rgba(245, 158, 11, 0.07); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 10px; display: flex; align-items: center; gap: 10px;">
                        <svg style="width: 16px; height: 16px; color: #d97706; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span style="font-size: 0.78rem; font-weight: 700; color: #92400e;">This justification is mandatory and will be permanently logged in the audit trail.</span>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '&#10003; &nbsp;Submit for Approval',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#94a3b8',
            focusConfirm: false,
            customClass: {
                popup: 'swal-decline-popup',
                confirmButton: 'swal-decline-confirm-btn',
                cancelButton: 'swal-decline-cancel-btn',
            },
            didOpen: () => {
                if (!document.getElementById('swal-decline-styles')) {
                    const style = document.createElement('style');
                    style.id = 'swal-decline-styles';
                    style.textContent = `.swal-decline-popup { border-radius: 24px !important; overflow: hidden !important; padding: 1.25em !important; } .swal-decline-confirm-btn { border-radius: 10px !important; font-weight: 800 !important; padding: 12px 24px !important; font-size: 0.9rem !important; } .swal-decline-cancel-btn { border-radius: 10px !important; font-weight: 700 !important; padding: 12px 24px !important; font-size: 0.9rem !important; } .swal2-actions { gap: 10px !important; margin-top: 1.5rem !important; }`;
                    document.head.appendChild(style);
                }
            },
            preConfirm: () => {
                const val = document.getElementById('swal-edit-justification').value.trim();
                if (!val) {
                    Swal.showValidationMessage('<span style="font-size:0.85rem;">⚠ Justification is mandatory for audit trails!</span>');
                    return false;
                }
                return val;
            }
        });

        if (!isConfirmed) return;
        reason = text;
    }

    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i data-lucide="loader" class="animate-spin" style="width: 18px;"></i> Processing...';
    if (typeof lucide !== 'undefined') lucide.createIcons();

    const url = isAdmin ? `{{ url('/received-items') }}/${currentEditBatchId}` : `{{ url('/edit-requests') }}`;
    const method = isAdmin ? 'PUT' : 'POST';
    const finalPayload = isAdmin ? payload : {
        item_id: currentEditBatchId,
        request_type: 'edit_submission',
        reason: reason,
        payload: JSON.stringify(payload)
    };

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(finalPayload)
    })
    .then(res => {
        if (!res.ok) throw new Error('Update failed');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            if (!isAdmin) {
                Swal.fire('Request Submitted', 'Your changes have been sent to the head for approval.', 'success');
                closeEditBatchModal();
                return;
            }
            // Real-time DOM update for admins...
            payload.items.forEach(item => {
                const row = document.querySelector(`tr[data-item-id="${item.id}"]`);
                if (row) {
                    // Update Batch level fields in all rows of this batch
                    const allBatchRows = document.querySelectorAll(`tr[data-batch-id="${currentEditBatchId}"]`);
                    allBatchRows.forEach(r => {
                        const arrivalDateTd = r.querySelector('td[data-label="Received Date"]');
                        if (arrivalDateTd) {
                            const date = new Date(payload.arrival_date);
                            arrivalDateTd.innerText = date.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: '2-digit' });
                        }

                        const supplierDonorTd = r.querySelector('td[data-label="Supplier / Donor"]');
                        if (supplierDonorTd) {
                            if (payload.acquisition_type === 'Donor') {
                                supplierDonorTd.innerHTML = `
                                    <div style="font-weight: 800; color: #8b5cf6;">${payload.donor_name || '-'}</div>
                                `;
                            } else {
                                let cleanSupplier = payload.supplier_name || '-';
                                if (cleanSupplier !== '-') {
                                    cleanSupplier = cleanSupplier.replace(/\s*\[.*\]\s*$/, '');
                                }
                                supplierDonorTd.innerHTML = `<div>${cleanSupplier}</div>`;
                            }
                        }

                        const statusTd = r.querySelector('td[data-label="Delivery Status"] span');
                        if (statusTd) {
                            let status = 'FULL DELIVERY';
                            let color = '#10b981';
                            if (payload.supplier_name.toLowerCase().includes('partial')) {
                                status = 'PARTIAL DELIVERY';
                                color = '#ef4444';
                            } else if (payload.acquisition_type === 'Donor') {
                                status = 'DONOR';
                                color = '#8b5cf6';
                            }
                            statusTd.innerText = status;
                            statusTd.style.background = color;
                        }
                    });

                    // Update Item specific fields
                    const descDiv = row.querySelector('td[data-label="Description"] div:first-child');
                    if (descDiv) descDiv.innerHTML = `${item.description} <span style="font-size: 0.65rem; color: var(--primary); font-weight: 800;">(${item.unit})</span>`;

                    const qtyTd = row.querySelector('td[data-label="Received Qty"]');
                    if (qtyTd) qtyTd.innerText = item.qty;

                    const stockTd = row.querySelector('td[data-label="Stock Balance"]');
                    if (stockTd) stockTd.innerText = item.stock_balance;

                    const varianceSpan = row.querySelector('td[data-label="Variance"] span');
                    if (varianceSpan) {
                        const v = parseFloat(item.variance);
                        varianceSpan.innerText = (v > 0 ? '+' : '') + v;
                        varianceSpan.style.color = v > 0 ? '#10b981' : (v < 0 ? '#ef4444' : '#94a3b8');
                    }
                }
            });

            Swal.fire({
                title: 'Live Update Success',
                text: 'The record has been updated and reflected in real-time.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
            closeEditBatchModal();
        } else {
            Swal.fire({
                title: 'Update Failed',
                text: data.message,
                icon: 'error'
            });
        }
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i data-lucide="save" style="width: 18px;"></i> Save Changes';
        lucide.createIcons();
    })
    .catch(error => {
        /* console print removed */
        Swal.fire({
            title: 'Critical Error',
            text: 'System communication failed.',
            icon: 'error'
        });
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i data-lucide="save" style="width: 18px;"></i> Save Changes';
        lucide.createIcons();
    });
}

// Auto-open editor if directed from messages
(function() {
    const params = new URLSearchParams(window.location.search);
        const editId = params.get('edit_batch');
        const deleteId = params.get('delete_batch');

        if (editId || deleteId) {
            // Clear params immediately for clean UX
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({path:cleanUrl}, '', cleanUrl);

            if (editId) {
                /* console print removed */
                let attempts = 0;
                const loader = setInterval(() => {
                    if (typeof window.openEditBatchModal === 'function') {
                        clearInterval(loader);
                        window.openEditBatchModal(editId);
                    } else if (attempts > 50) {
                        clearInterval(loader);
                    }
                    attempts++;
                }, 100);
            }

            if (deleteId) {
                /* console print removed */
                let attempts = 0;
                const loader = setInterval(() => {
                    if (typeof window.deleteBatch === 'function') {
                        clearInterval(loader);
                        window.deleteBatch(deleteId);
                    } else if (attempts > 50) {
                        clearInterval(loader);
                    }
                    attempts++;
                }, 100);
            }
        }
})();
</script>
@endsection
