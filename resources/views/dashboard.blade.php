@extends('layouts.dashboard')

@section('content')
<div class="animate-slide-up">
    <div class="page-header dashboard-header-mobile">
        <!-- Background Accent -->
        <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: rgba(99, 102, 241, 0.05); border-radius: 50%;"></div>

        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: var(--bg-main); color: var(--primary); font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em;">System Live</span>
                <span style="color: var(--text-muted); font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem;">
                    <i data-lucide="calendar" style="width: 14px;"></i>
                    {{ date('d/m/y') }}
                </span>
            </div>
            <h2 style="font-size: 2.25rem; font-weight: 900; letter-spacing: -0.04em; color: var(--text-main); margin-bottom: 0.25rem;">Dashboard <span style="color: var(--primary);">Overview</span></h2>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Monitor your inventory and stock movement in real-time.</p>
        </div>

        <div class="dashboard-actions-mobile" style="display: flex; gap: 1rem;">
            <button class="btn-secondary" onclick="window.location.href='{{ route('dashboard', ['refresh' => 1]) }}'" style="background: var(--bg-main); border: none; padding: 0.875rem 1.5rem; border-radius: 1rem; color: var(--text-main); font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; transition: var(--transition);">
                <i data-lucide="refresh-cw" style="width: 18px;"></i>
                Refresh
            </button>
            <a href="{{ auth()->user()->can_add_inventory ? route('inventory.create') : '#' }}"
                @if(!auth()->user()->can_add_inventory)
                style="padding: 0.85rem 1.75rem; border-radius: 12px; border: none; background: #cbd5e1; color: white; display: flex; align-items: center; gap: 0.75rem; cursor: not-allowed; transition: var(--transition); text-decoration: none;"
                disabled title="Unauthorized: Permission Required"
                @else
                class="btn-primary"
                style="padding: 0.85rem 1.75rem; border-radius: 12px; border: none; background: var(--primary); color: white; display: flex; align-items: center; gap: 0.75rem; cursor: pointer; transition: var(--transition); text-decoration: none; box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.3);"
                @endif>
                <i data-lucide="plus" style="width: 20px;"></i>
                New Entry
            </a>
            <a href="{{ auth()->user()->can_add_inventory ? route('inventory.discrepancy') : '#' }}"
                @if(!auth()->user()->can_add_inventory)
                style="padding: 0.85rem 1.75rem; border-radius: 12px; border: none; background: #cbd5e1; color: white; display: flex; align-items: center; gap: 0.75rem; cursor: not-allowed; transition: var(--transition); text-decoration: none;"
                disabled title="Unauthorized: Permission Required"
                @else
                class="btn-primary"
                style="padding: 0.85rem 1.75rem; border-radius: 12px; border: none; background: #ef4444; color: white; display: flex; align-items: center; gap: 0.75rem; cursor: pointer; transition: var(--transition); text-decoration: none; box-shadow: 0 10px 20px -5px rgba(239, 68, 68, 0.3);"
                @endif>
                <i data-lucide="file-text" style="width: 20px;"></i>
                Record Existing Item
            </a>
        </div>
    </div>

    <style>
        @media (max-width: 768px) {
            .dashboard-header-mobile {
                padding: 1.75rem !important;
                background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-main) 100%) !important;
                border-radius: 0 0 32px 32px !important;
                margin: -24px -24px 2rem -24px !important;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.04) !important;
                border: none !important;
                border-bottom: 1px solid var(--border-color) !important;
                position: relative;
                overflow: hidden;
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 1.5rem !important;
            }

            .dashboard-header-mobile h2 {
                font-size: 1.85rem !important;
                font-weight: 900 !important;
            }

            .dashboard-header-mobile p {
                font-size: 0.95rem !important;
                line-height: 1.4 !important;
            }

            .dashboard-actions-mobile {
                display: grid !important;
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)) !important;
                gap: 0.75rem !important;
                width: 100% !important;
                margin-top: 0.5rem !important;
            }

            .dashboard-actions-mobile button {
                width: 100% !important;
                padding: 0.85rem !important;
                border-radius: 14px !important;
                font-size: 0.8rem !important;
                justify-content: center !important;
            }

            /* Modal Header Beauty */
            .modal-header-sticky .modal-header {
                flex-direction: column !important;
                align-items: center !important;
                text-align: center !important;
                gap: 1.25rem !important;
                position: relative !important;
            }

            .modal-header-sticky .modal-header>div:first-child {
                flex-direction: column !important;
                gap: 0.75rem !important;
            }

            .modal-header-sticky .modal-header h3 {
                font-size: 1.5rem !important;
                font-weight: 900 !important;
            }

            #closeModal {
                position: absolute !important;
                top: 0 !important;
                right: 0 !important;
                background: rgba(0, 0, 0, 0.05) !important;
            }
        }

        /* Premium iOS-style Toggle Switch */
        .premium-switch input:checked + .slider {
            background-color: var(--primary) !important;
        }
        .premium-switch .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }
        .premium-switch input:checked + .slider:before {
            transform: translateX(20px);
        }


    </style>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card glass-card pop-in float-card" style="border-top: 4px solid #6366f1; animation-delay: 0.1s;">
            <div class="stat-icon" style="background: rgba(99, 102, 241, 0.15); color: #6366f1;">
                <i data-lucide="layers"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Inventory</span>
                <span class="stat-value">{{ number_format($totalInventory) }}</span>
                <div class="stat-trend" style="color: {{ $trendValue >= 0 ? '#10b981' : '#ef4444' }};">
                    <i data-lucide="{{ $trendValue >= 0 ? 'arrow-up-right' : 'arrow-down-right' }}" style="width: 14px;"></i>
                    {{ number_format(abs($trendValue), 1) }}% vs last month
                </div>
            </div>
        </div>

        <div class="stat-card glass-card pop-in float-card" style="border-top: 4px solid #10b981; animation-delay: 0.2s;">
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                <i data-lucide="trending-up"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Daily Issuance</span>
                <span class="stat-value">{{ number_format($dailyIssuance) }}</span>
                <div class="stat-trend" style="color: #64748b;">
                    <i data-lucide="clock" style="width: 14px;"></i>
                    Recorded today
                </div>
            </div>
        </div>

        <div class="stat-card glass-card pop-in float-card" style="border-top: 4px solid #f59e0b; animation-delay: 0.3s;">
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                <i data-lucide="activity"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Variance</span>
                <span class="stat-value">{{ $totalVariance > 0 ? '+' : '' }}{{ number_format($totalVariance) }}</span>
                <div class="stat-trend" style="color: {{ $totalVariance > 0 ? '#10b981' : ($totalVariance < 0 ? '#ef4444' : '#3b82f6') }};">
                    <i data-lucide="{{ $totalVariance > 0 ? 'trending-up' : ($totalVariance < 0 ? 'trending-down' : 'minus') }}" style="width: 14px;"></i>
                    {{ $totalVariance > 0 ? 'Net Surplus' : ($totalVariance < 0 ? 'Net Shortage' : 'Balanced') }}
                </div>
            </div>
        </div>

        @php
        $isLedgeCritical = count($lowStockLedges) > 0;
        $ledgeAlertMsg = '';
        if (count($lowStockLedges) === 1) {
        $ledgeAlertMsg = "Category {$lowStockLedges[0]['code']} ({$lowStockLedges[0]['name']}) is at {$lowStockLedges[0]['percentage']}%";
        } elseif (count($lowStockLedges) > 1) {
        $ledgeAlertMsg = count($lowStockLedges) . " Categories are below 50%";
        }
        @endphp
        <div class="stat-card glass-card {{ $isLedgeCritical ? 'alert-blink' : '' }}"
            style="border-top: 4px solid #ef4444; cursor: pointer; position: relative; overflow: visible;"
            onclick="toggleLowStockPopover(event)">

            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">
                <i data-lucide="alert-triangle"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Low Stock Monitor</span>
                <div class="stat-trend" style="color: {{ ($isLedgeCritical || $lowStockCount > 0) ? '#ef4444' : '#10b981' }}; margin-top: 0.5rem;">
                    <i data-lucide="{{ ($isLedgeCritical || $lowStockCount > 0) ? 'bell' : 'check-circle' }}" style="width: 14px;"></i>
                    @if($lowStockCount > 0 && $isLedgeCritical)
                        {{ $lowStockCount }} items & Categories critical
                    @elseif($lowStockCount > 0)
                        {{ $lowStockCount }} items below threshold
                    @elseif($isLedgeCritical)
                        {{ $ledgeAlertMsg }}
                    @else
                        No critical stock detected
                    @endif
                </div>
            </div>

            <style>
                .no-scrollbar::-webkit-scrollbar {
                    display: none;
                }

                .no-scrollbar {
                    -ms-overflow-style: none;
                    scrollbar-width: none;
                }
            </style>
            <div id="lowStockPopover" class="low-stock-popover glass-card no-scrollbar">
                <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; font-weight: 800; display: flex; justify-content: space-between;">
                    <span>Category Monitor</span>
                </h4>

                @if($isLedgeCritical)
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid rgba(239, 68, 68, 0.1); padding-bottom: 0.75rem;">
                        <span style="font-size: 0.65rem; color: var(--danger); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Alerts: {{ count($lowStockLedges) }} Categories</span>
                    </div>

                    @foreach($lowStockLedges as $l)
                    @php
                        $isCritical = $l['percentage'] <= 50 || ($l['is_override'] ?? false);
                        $statusLabel = $isCritical ? 'CRITICAL DEPLETION' : 'WATCHLIST';
                        $statusColor = $isCritical ? '#ef4444' : '#f59e0b';
                    @endphp
                    <div class="popover-item" style="display: block; padding: 0.75rem 0.5rem; border-bottom: 1px solid rgba(0,0,0,0.03);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                            <div>
                                <div style="font-weight: 800; color: var(--text-main); font-size: 0.9rem; line-height: 1.2;">Category {{ $l['code'] }}</div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600;">{{ $l['name'] }}</div>
                            </div>
                            <span style="font-size: 0.55rem; font-weight: 900; color: white; background: {{ $statusColor }}; padding: 0.2rem 0.6rem; border-radius: 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">{{ $statusLabel }}</span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="flex-grow: 1; height: 6px; background: var(--bg-main); border-radius: 10px; overflow: hidden; border: 1px solid rgba(0,0,0,0.02);">
                                <div style="width: {{ $l['percentage'] }}%; height: 100%; background: {{ $statusColor }}; box-shadow: 0 0 10px {{ $statusColor }}4d;"></div>
                            </div>
                            <span style="font-size: 0.8rem; font-weight: 900; color: {{ $statusColor }}; min-width: 35px; text-align: right;">{{ $l['percentage'] }}%</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                @if($lowStockCount > 0)
                <div style="margin-top: 2rem;">
                    <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; font-weight: 800; display: flex; justify-content: space-between;">
                        <span>Item Alerts ({{ $lowStockCount }})</span>
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @foreach($lowStockItems as $item)
                        <div class="popover-item" style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: rgba(0,0,0,0.01); border-radius: 12px; border: 1px solid rgba(0,0,0,0.03);">
                            <div style="overflow: hidden; flex: 1; padding-right: 1rem;">
                                <div style="font-weight: 800; color: var(--text-main); font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $item->description }}</div>
                                <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 600;">Category: {{ $item->ledge_category }}</div>
                            </div>
                            <div style="text-align: right; flex-shrink: 0;">
                                <div style="font-weight: 900; color: #ef4444; font-size: 1rem;">{{ number_format($item->stock_balance) }}</div>
                                <div style="font-size: 0.6rem; color: #94a3b8; font-weight: 800; text-transform: uppercase;">Stock Bal</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!$isLedgeCritical && $lowStockCount == 0)
                <div style="text-align: center; padding: 2.5rem 0; color: var(--text-muted);">
                    <i data-lucide="shield-check" style="width: 32px; height: 32px; margin-bottom: 0.75rem; color: #10b981; opacity: 0.8;"></i>
                    <p style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">All Systems Healthy</p>
                    <p style="font-size: 0.7rem;">Inventory levels meet all administrative thresholds.</p>
                </div>
                @endif

            <div style="font-size: 0.65rem; color: var(--text-muted); text-align: center; margin-top: 1rem; font-style: italic; border-top: 1px solid rgba(0,0,0,0.02); padding-top: 0.75rem;">Tap anywhere else to close</div>
        </div>
    </div>
</div>



<!-- Charts Section -->
<div class="charts-grid">
    <div class="glass-card pop-in float-card" style="border-left: 4px solid var(--secondary); animation-delay: 0.5s;">
        <div class="card-title">
            <span>Stock Performance</span>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <span id="btnDaily" style="font-size: 0.75rem; background: var(--bg-main); color: var(--text-main); padding: 0.25rem 0.5rem; border-radius: 6px; cursor: pointer; transition: var(--transition);">Daily</span>
                <span id="btnWeekly" style="font-size: 0.75rem; background: var(--bg-main); color: var(--text-main); padding: 0.25rem 0.5rem; border-radius: 6px; cursor: pointer; transition: var(--transition);">Weekly</span>
                <span id="btnMonthly" style="font-size: 0.75rem; background: var(--primary); color: white; padding: 0.25rem 0.5rem; border-radius: 6px; cursor: pointer; transition: var(--transition);">Monthly</span>
            </div>
        </div>
        <div id="advancedAreaChart"></div>
    </div>
    <div class="glass-card special-card pop-in float-card" style="border-left: 4px solid var(--primary); background: var(--bg-card); animation-delay: 0.6s;">
        <div class="card-title">
            <span>Stock Distribution</span>
            <i data-lucide="pie-chart" style="color: var(--primary); width: 20px;"></i>
        </div>
        <div id="enhancedDonutChart" style="margin-top: -1.5rem; margin-inline: -1rem; min-height: 400px;"></div>
        <div class="distribution-details">
            <div class="dist-item">
                <div class="dist-label">TOP CATEGORY</div>
                <div class="dist-value" style="color: var(--primary);">{{ $topCategory }}</div>
            </div>
            <div class="dist-item">
                <div class="dist-label">AVG. STOCK / CAT</div>
                <div class="dist-value" style="color: var(--secondary);">{{ number_format($avgStock, 0) }}</div>
            </div>
        </div>
    </div>
</div>



<!-- Recent Activity Table -->
<div class="glass-card pop-in float-card" style="border-bottom: 4px solid var(--accent); animation-delay: 0.7s; overflow: visible;">
    <div class="card-title" style="flex-wrap: wrap; gap: 1rem;">
        <span>Recent Stock Received</span>
        <button onclick="window.location.href='{{ route('receiveditems') }}'" class="btn-secondary" style="border: none; background: var(--bg-main); color: var(--primary); padding: 0.5rem 1rem; border-radius: 10px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">View All Received Items</button>
    </div>

    <style>
        /* ── Scrollable wrapper on small screens ── */
        .activity-table-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 768px) {

            /* Hide the wrapper's horizontal scroll; we go card-style instead */
            .activity-table-wrap {
                overflow-x: visible;
            }

            .activity-table thead {
                display: none;
            }

            .activity-table,
            .activity-table tbody,
            .activity-table tr,
            .activity-table td {
                display: block;
                width: 100%;
            }

            .activity-row {
                margin-bottom: 1rem;
                border-radius: 14px;
                background: var(--bg-main) !important;
                padding: 1rem 1.1rem;
                border: 1px solid var(--border-color);
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .activity-row:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 24px rgba(99, 102, 241, 0.08);
            }

            /* Every cell: label on left, value on right */
            .activity-row td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.45rem 0 !important;
                border: none !important;
                font-size: 0.85rem;
                gap: 0.5rem;
            }

            /* Left-side label via data-label attribute */
            .activity-row td::before {
                content: attr(data-label);
                flex-shrink: 0;
                font-weight: 800;
                color: var(--text-muted);
                font-size: 0.68rem;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                min-width: 110px;
            }

            /* ── Column 1 – Entry Date: acts as card header ── */
            .activity-row td:nth-child(1) {
                border-bottom: 1px dashed var(--border-color) !important;
                margin-bottom: 0.5rem;
                padding-bottom: 0.8rem !important;
                font-weight: 700;
                color: var(--text-main);
            }

            /* ── Column 2 – Received Date ── */
            .activity-row td:nth-child(2) {
                font-weight: 700;
                color: var(--primary);
            }

            /* ── Column 3 – Product: prominent ── */
            .activity-row td:nth-child(3) {
                font-weight: 700;
                font-size: 0.9rem;
                color: var(--text-main);
            }

            /* ── Column 8 – Qty/Variance: align badge right ── */
            .activity-row td:nth-child(8) {
                font-weight: 700;
            }

            /* ── Column 10 – Stock Balance: bottom divider ── */
            .activity-row td:nth-child(10) {
                border-top: 1px dashed var(--border-color) !important;
                margin-top: 0.4rem;
                padding-top: 0.7rem !important;
                font-weight: 800;
                font-size: 0.9rem;
                color: var(--text-main);
            }

            /* Right-side value wraps nicely */
            .activity-row td > *:last-child,
            .activity-row td > span:last-child {
                text-align: right;
                flex-shrink: 1;
                word-break: break-word;
            }
        }
    </style>

    <div class="activity-table-wrap">
    <table class="activity-table">
        <thead>
            <tr>
                <th>Entry Date</th>
                <th>Received Date</th>
                <th>Product</th>
                <th>Category</th>
                <th>Supplier / Donor</th>
                <th>Delivery Status</th>
                <th>Qty Received</th>
                <th>Qty (Var)</th>
                <th>Type</th>
                <th>Stock Bal.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentTransactions as $transaction)
            <tr class="activity-row">
                <td data-label="Entry Date">{{ \Carbon\Carbon::parse($transaction->entry_date)->format('d/m/y H:i') }}</td>
                <td data-label="Received Date" style="color: var(--primary); font-weight: 700;">{{ $transaction->arrival_date ? \Carbon\Carbon::parse($transaction->arrival_date)->format('d/m/y') : '-' }}</td>
                <td data-label="Product">{{ $transaction->description }} <span style="font-size: 0.65rem; color: var(--primary); font-weight: 800;">({{ $transaction->unit ?? 'Package Types' }})</span></td>
                <td data-label="Category"><span style="font-size: 0.75rem; background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 600;">{{ $ledgeMap[$transaction->ledge_category] ?? "Category " . $transaction->ledge_category }}</span></td>
                @php
                $rawSup = $transaction->supplier_name;
                $acqType = $transaction->acquisition_type ?? 'Supplier';
                $dName = $transaction->donor_name ?? '-';

                // Legacy Fallback
                if ($acqType === 'Supplier' && preg_match('/\[(Donor Action|Donation)\]/', $rawSup)) {
                    $acqType = 'Donor';
                    $dName = preg_replace('/\s\[.*\]$/', '', $rawSup);
                }

                $cleanSupDisplay = preg_replace('/\s\[.*\]$/', '', $rawSup);
                
                $isIssuedOut = $transaction->hasActiveTemporaryLoan();
                if ($isIssuedOut) {
                    $supStatusDisplay = 'ISSUED OUT';
                    $supColor = '#f59e0b';
                } else {
                    $supStatusDisplay = $transaction->supplier_status ?? 'N/A';
                    if ($acqType === 'Donor') {
                        $supStatusDisplay = 'Donor';
                        $supColor = '#8b5cf6';
                    } else {
                        $supColor = '#94a3b8';
                        if (str_contains(strtolower($supStatusDisplay), 'full delivery')) $supColor = '#10b981';
                        elseif (str_contains(strtolower($supStatusDisplay), 'partial delivery')) $supColor = '#ef4444';
                    }
                }
                @endphp
                <td data-label="Supplier / Donor" style="color: var(--text-main);">
                    @if($acqType === 'Donor')
                        <div style="font-weight: 800; color: #8b5cf6;">{{ $dName }}</div>
                    @else
                        <div>{{ $cleanSupDisplay ?: '-' }}</div>
                    @endif
                </td>
                <td data-label="Delivery Status">
                    <span style="font-size: 0.65rem; font-weight: 800; color: white; background: {{ $supColor }}; padding: 0.25rem 0.6rem; border-radius: 6px; text-transform: uppercase;">
                        {{ $supStatusDisplay }}
                    </span>
                </td>
                <td data-label="Qty Received" style="font-weight: 600;">{{ is_numeric($transaction->qty) ? number_format($transaction->qty, 0) : ($transaction->qty ?? '0') }}</td>
                <td data-label="Qty/Variance" style="font-weight: 700; color: {{ is_numeric($transaction->variance) && (float)$transaction->variance > 0 ? 'var(--secondary)' : (is_numeric($transaction->variance) && (float)$transaction->variance < 0 ? 'var(--danger)' : 'inherit') }}">
                    {{ is_numeric($transaction->variance) && (float)$transaction->variance > 0 ? '+' : '' }}{{ is_numeric($transaction->variance) ? number_format($transaction->variance, 0) : $transaction->variance }}
                </td>
                <td data-label="Transaction Type">
                    @php
                    $v = (float)$transaction->variance;
                    $stClass = 'status-success';
                    $stText = 'Received';
                    if (!is_null($transaction->book_qty)) {
                        if ($v != 0) {
                            $stClass = 'status-warning';
                            $stText = 'Discrepancy';
                        }
                    } else {
                        if ($v < 0) {
                            $stClass = 'status-warning';
                            $stText = 'Issued';
                        } elseif ($transaction->variance == 'Expired') {
                            $stClass = 'status-danger';
                            $stText = 'Expired';
                        }
                    }
                    @endphp
                    <span class="status-badge {{ $stClass }}">{{ $stText }}</span>
                </td>
                <td data-label="Stock Bal." style="font-weight: 700;">{{ is_numeric($transaction->stock_balance) ? number_format($transaction->stock_balance, 0) : $transaction->stock_balance }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="padding: 5rem 2rem; text-align: center;">
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 1.25rem;">
                        <div style="background: var(--bg-main); width: 72px; height: 72px; border-radius: 20px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); box-shadow: 0 10px 20px rgba(0,0,0,0.02); border: 1px solid var(--border-color);">
                            <i data-lucide="clipboard-list" style="width: 32px; opacity: 0.5;"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">No Transactions Recorded</h4>
                            <p style="color: var(--text-muted); font-size: 0.9rem;">Your recent stock movements and activity logs will appear here.</p>
                        </div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>{{-- /.activity-table-wrap --}}
</div>
</div>
@endsection

@push('scripts')
<script>
    // Advanced Area Chart
    var areaOptions = {
        series: [{
            name: 'Received',
            data: @json($receivedSeries)
        }, {
            name: 'Net Variance',
            data: @json($varianceSeries)
        }],
        chart: {
            type: 'area',
            height: 350,
            toolbar: {
                show: false
            },
            sparkline: {
                enabled: false
            },
            fontFamily: 'Outfit, sans-serif',
            background: 'transparent'
        },
        theme: {
            mode: document.documentElement.getAttribute('data-theme') || 'light'
        },
        colors: ['#6366f1', '#ef4444'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [20, 100, 100, 100]
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        grid: {
            borderColor: 'var(--border-color)',
            strokeDashArray: 4,
            yaxis: {
                lines: {
                    show: true
                }
            }
        },
        xaxis: {
            categories: @json($chartMonths),
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            markers: {
                radius: 12,
                width: 12,
                height: 12
            }
        },
        tooltip: {
            theme: document.documentElement.getAttribute('data-theme') || 'light'
        },
        responsive: [{
            breakpoint: 768,
            options: {
                chart: {
                    height: 280
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center'
                }
            }
        }]
    };

    if (document.querySelector("#advancedAreaChart")) {
        var areaChart = new ApexCharts(document.querySelector("#advancedAreaChart"), areaOptions);
        areaChart.render();
    }

    // Chart Timeframe Toggling Logic
    const dailyData = {
        labels: @json($dayLabels),
        received: @json($dayReceived),
        variance: @json($dayVariance)
    };

    const weeklyData = {
        labels: @json($weekLabels),
        received: @json($weekReceived),
        variance: @json($weekVariance)
    };

    const monthlyData = {
        labels: @json($chartMonths),
        received: @json($receivedSeries),
        variance: @json($varianceSeries)
    };

    document.getElementById('btnDaily').addEventListener('click', function() {
        areaChart.updateOptions({
            xaxis: {
                categories: dailyData.labels
            }
        });
        areaChart.updateSeries([{
                name: 'Received',
                data: dailyData.received
            },
            {
                name: 'Net Variance',
                data: dailyData.variance
            }
        ]);
        this.style.background = 'var(--primary)';
        this.style.color = 'white';
        document.getElementById('btnWeekly').style.background = 'var(--bg-main)';
        document.getElementById('btnWeekly').style.color = 'var(--text-main)';
        document.getElementById('btnMonthly').style.background = 'var(--bg-main)';
        document.getElementById('btnMonthly').style.color = 'var(--text-main)';
    });

    document.getElementById('btnWeekly').addEventListener('click', function() {
        areaChart.updateOptions({
            xaxis: {
                categories: weeklyData.labels
            }
        });
        areaChart.updateSeries([{
                name: 'Received',
                data: weeklyData.received
            },
            {
                name: 'Net Variance',
                data: weeklyData.variance
            }
        ]);
        this.style.background = 'var(--primary)';
        this.style.color = 'white';
        document.getElementById('btnDaily').style.background = 'var(--bg-main)';
        document.getElementById('btnDaily').style.color = 'var(--text-main)';
        document.getElementById('btnMonthly').style.background = 'var(--bg-main)';
        document.getElementById('btnMonthly').style.color = 'var(--text-main)';
    });

    document.getElementById('btnMonthly').addEventListener('click', function() {
        areaChart.updateOptions({
            xaxis: {
                categories: monthlyData.labels
            }
        });
        areaChart.updateSeries([{
                name: 'Received',
                data: monthlyData.received
            },
            {
                name: 'Net Variance',
                data: monthlyData.variance
            }
        ]);
        this.style.background = 'var(--primary)';
        this.style.color = 'white';
        document.getElementById('btnDaily').style.background = 'var(--bg-main)';
        document.getElementById('btnDaily').style.color = 'var(--text-main)';
        document.getElementById('btnWeekly').style.background = 'var(--bg-main)';
        document.getElementById('btnWeekly').style.color = 'var(--text-main)';
    });

    // Enhanced Donut Chart
    var donutOptions = {
        series: @json($distSeries),
        chart: {
            type: 'donut',
            height: 400,
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 1200,
                animateGradually: {
                    enabled: true,
                    delay: 400
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 600
                }
            },
            dropShadow: {
                enabled: true,
                top: 10,
                left: 0,
                blur: 10,
                opacity: 0.1
            },
            background: 'transparent'
        },
        theme: {
            mode: document.documentElement.getAttribute('data-theme') || 'light'
        },
        labels: @json($distLabels),
        colors: @json($isEmptyDist) ? ['#cbd5e1'] : ['#6366f1', '#10b981', '#f59e0b', '#db2777', '#8b5cf6', '#06b6d4', '#ec4899', '#f97316', '#14b8a6'],
        dataLabels: {
            enabled: false
        },
        legend: {
            position: 'bottom',
            fontFamily: 'Outfit',
            fontSize: '12px',
            markers: {
                radius: 12,
                width: 12,
                height: 12
            }
        },
        tooltip: {
            theme: document.documentElement.getAttribute('data-theme') || 'light',
            y: {
                formatter: function(val) {
                    return @json($isEmptyDist) ? "No inventory recorded" : val.toLocaleString() + " units"
                }
            }
        },
        stroke: {
            show: true,
            width: 8,
            colors: [document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#ffffff']
        },
        plotOptions: {
            pie: {
                expandOnClick: true,
                donut: {
                    size: '72%',
                    borderRadius: 10,
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: @json($isEmptyDist) ? 'Awaiting Records' : 'Total Package Types',
                            fontSize: '14px',
                            fontWeight: 600,
                            color: '#64748b',
                            formatter: function(w) {
                                if (@json($isEmptyDist)) return "0";
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                            }
                        },
                        value: {
                            show: true,
                            fontSize: '28px',
                            fontWeight: 800,
                            color: 'var(--text-main)',
                            formatter: function(val) {
                                return @json($isEmptyDist) ? '0' : val.toLocaleString()
                            }
                        }
                    }
                }
            }
        }
    };

    if (document.querySelector("#enhancedDonutChart")) {
        var donutChart = new ApexCharts(document.querySelector("#enhancedDonutChart"), donutOptions);
        donutChart.render();
    }
</script>

<script>
    // Listen for theme changes to update charts
    window.addEventListener('themeChanged', (e) => {
        const newTheme = e.detail.theme;
        areaChart.updateOptions({
            theme: {
                mode: newTheme
            },
            tooltip: {
                theme: newTheme
            }
        });
        donutChart.updateOptions({
            theme: {
                mode: newTheme
            },
            tooltip: {
                theme: newTheme
            },
            stroke: {
                colors: [newTheme === 'dark' ? '#1e293b' : '#ffffff']
            }
        });
    });
</script>


@endpush

@push('scripts')
<script>
    function toggleLowStockPopover(event) {
        event.stopPropagation();
        const popover = document.getElementById('lowStockPopover');
        if (popover) {
            popover.style.display = popover.style.display === 'block' ? 'none' : 'block';
        }
    }

    // Close popover when clicking anywhere else
    document.addEventListener('click', function(event) {
        const popover = document.getElementById('lowStockPopover');
        const card = event.target.closest('.stat-card');
        if (popover && !card) {
            popover.style.display = 'none';
        }
    });
</script>
@endpush
