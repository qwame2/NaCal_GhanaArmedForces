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
            <button class="btn-secondary" style="background: var(--bg-main); border: none; padding: 0.875rem 1.5rem; border-radius: 1rem; color: var(--text-main); font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; transition: var(--transition);">
                <i data-lucide="refresh-cw" style="width: 18px;"></i>
                Refresh
            </button>
            <button id="openNewEntry"
                @if(auth()->user()->can_add_inventory)
                onclick="openModal()"
                @else
                disabled title="Unauthorized: Permission Required"
                @endif
                class="btn-primary"
                style="padding: 0.85rem 1.75rem; border-radius: 12px; border: none; background: {{ auth()->user()->can_add_inventory ? 'var(--primary)' : '#cbd5e1' }}; color: white; display: flex; align-items: center; gap: 0.75rem; cursor: {{ auth()->user()->can_add_inventory ? 'pointer' : 'not-allowed' }}; transition: var(--transition); box-shadow: {{ auth()->user()->can_add_inventory ? '0 10px 20px -5px rgba(99, 102, 241, 0.3)' : 'none' }};">
                <i data-lucide="plus" style="width: 20px;"></i>
                New Entry
            </button>
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
                grid-template-columns: 1fr 1fr !important;
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

        /* Ensure all text inside the Add New Inventory Item modal is clear and solid black in light mode */
        html:not([data-theme='dark']) #newEntryModal {
            --text-main: #000000 !important;
            --text-muted: #000000 !important;
        }

        html:not([data-theme='dark']) #newEntryModal .modal-content,
        html:not([data-theme='dark']) #newEntryModal label,
        html:not([data-theme='dark']) #newEntryModal h3,
        html:not([data-theme='dark']) #newEntryModal p:not(.unassigned-warning-hint p),
        html:not([data-theme='dark']) #newEntryModal span:not(.existing-indicator):not(.badge):not(.select2-results__option--highlighted span):not(.required-asterisk):not(:last-child),
        html:not([data-theme='dark']) #newEntryModal input:not([type="submit"]):not([type="button"]):not(.row-variance),
        html:not([data-theme='dark']) #newEntryModal select,
        html:not([data-theme='dark']) #newEntryModal .select2-container--default .select2-selection--single .select2-selection__rendered,
        html:not([data-theme='dark']) #newEntryModal .select2-results__option:not(.select2-results__option--highlighted),
        html:not([data-theme='dark']) #newEntryModal .select2-search__field {
            color: #000000 !important;
        }

        /* Ensure placeholder texts are clear, readable gray/slate and distinct from actual inputs */
        html:not([data-theme='dark']) #newEntryModal input::placeholder,
        html:not([data-theme='dark']) #newEntryModal textarea::placeholder,
        html:not([data-theme='dark']) #newEntryModal .select2-selection__placeholder {
            color: #475569 !important;
            opacity: 0.8 !important;
            font-weight: 500 !important;
        }

        /* Ensure warning messages and variance indicators remain colored rather than overridden to black */
        html:not([data-theme='dark']) #newEntryModal .unassigned-warning-hint,
        html:not([data-theme='dark']) #newEntryModal .unassigned-warning-hint * {
            color: #ef4444 !important;
        }

        /* Ensure required field asterisks remain red in all modes */
        #newEntryModal .required-asterisk,
        #newEntryModal label > span:last-child {
            color: #ef4444 !important;
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
                <td data-label="Qty Received" style="font-weight: 600;">{{ $transaction->qty ?? '0' }}</td>
                <td data-label="Qty/Variance" style="font-weight: 700; color: {{ is_numeric($transaction->variance) && (float)$transaction->variance > 0 ? 'var(--secondary)' : (is_numeric($transaction->variance) && (float)$transaction->variance < 0 ? 'var(--danger)' : 'inherit') }}">
                    {{ is_numeric($transaction->variance) && (float)$transaction->variance > 0 ? '+' : '' }}{{ $transaction->variance }}
                </td>
                <td data-label="Transaction Type">
                    @php
                    $v = (float)$transaction->variance;
                    $stClass = 'status-success';
                    $stText = 'Received';
                    if ($v < 0) {
                        $stClass='status-warning' ;
                        $stText='Issued' ;
                        } elseif ($transaction->variance == 'Expired') {
                        $stClass = 'status-danger';
                        $stText = 'Expired';
                        }
                        @endphp
                        <span class="status-badge {{ $stClass }}">{{ $stText }}</span>
                </td>
                <td data-label="Stock Bal." style="font-weight: 700;">{{ $transaction->stock_balance }}</td>
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

<script id="inventory-data" type="application/json">
    @json($existingItems)
</script>

<script>
    // Global Modal Controls for legacy onclick attributes
    window.openModal = function() {
            const modal = jQuery('#newEntryModal');
            if (modal.length) {
                modal.css('display', 'flex');

                // Set current date/time to MySQL format (YYYY-MM-DD HH:MM:SS)
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

                // Reset Form
                jQuery('#newEntryForm')[0].reset();

                // Reset submit button state
                const submitBtn = jQuery('#newEntryForm button[type="submit"]');
                submitBtn.prop('disabled', false).css({
                    'opacity': '1',
                    'cursor': 'pointer',
                    'pointer-events': 'auto'
                }).removeAttr('title');

                jQuery('#entryDate').val(formattedDate);
                jQuery('#arrivalDate').val(new Date().toISOString().split('T')[0]);

                jQuery('#ledgeSelect').val('').trigger('change');
                jQuery('#supplierNameSelect').val('').trigger('change');
                jQuery('#deliveryPersonInput').val('');
                jQuery('#deliveryPersonGroup').hide();
                jQuery('#supplierStatusSelect').val('').trigger('change');
                jQuery('#isDonorCheckbox').prop('checked', false);
                jQuery('#deliveryStatusGroup').show();
                jQuery('#itemsContainer').empty();
                jQuery('#itemDetails').hide();
                jQuery('#qtyControl, #supplierControl, #dateControl').hide().css('opacity', 0);
                jQuery('#modalFooter').hide();
            }
        };

        window.closeModal = function() {
            const modal = jQuery('#newEntryModal');
            if (modal.length) {
                const content = modal.find('.modal-content');
                content.addClass('slide-out');
                setTimeout(() => {
                    modal.hide();
                    content.removeClass('slide-out');
                }, 600);
            }
        };
    </script>
    <script>
    // New Entry Modal Logic
    jQuery(document).ready(function($) {
        window.originalRollbackPayload = null;

        function hasRollbackChanges(newPayload, origPayload) {
            if (!origPayload) return true;

            function normalizeName(name) {
                if (!name) return '';
                return name.replace(/\s\[.*\]$/, '').trim().toLowerCase();
            }

            if (newPayload.ledge_category !== origPayload.ledge_category) {
                /* console print removed */
                return true;
            }

            if (normalizeName(newPayload.supplier_name) !== normalizeName(origPayload.supplier_name)) {
                /* console print removed */
                return true;
            }

            if (normalizeName(newPayload.donor_name) !== normalizeName(origPayload.donor_name)) {
                /* console print removed */
                return true;
            }

            if (newPayload.supplier_status !== origPayload.supplier_status) {
                /* console print removed */
                return true;
            }

            if (newPayload.acquisition_type !== origPayload.acquisition_type) {
                /* console print removed */
                return true;
            }

            if (newPayload.arrival_date !== origPayload.arrival_date) {
                /* console print removed */
                return true;
            }

            const newItems = newPayload.items || [];
            const origItems = origPayload.items || [];
            if (newItems.length !== origItems.length) {
                /* console print removed */
                return true;
            }

            for (let i = 0; i < newItems.length; i++) {
                const ni = newItems[i];
                const oi = origItems[i] || {};
                
                if ((ni.description || '').trim().toUpperCase() !== (oi.description || '').trim().toUpperCase()) {
                    /* console print removed */
                    return true;
                }
                if ((ni.unit || '').trim().toUpperCase() !== (oi.unit || '').trim().toUpperCase()) {
                    /* console print removed */
                    return true;
                }
                if ((ni.location || '').trim().toUpperCase() !== (oi.location || '').trim().toUpperCase()) {
                    /* console print removed */
                    return true;
                }
                if (parseFloat(ni.qty || 0) !== parseFloat(oi.qty || 0)) {
                    /* console print removed */
                    return true;
                }
                if (parseFloat(ni.stock_balance || 0) !== parseFloat(oi.stock_balance || 0)) {
                    /* console print removed */
                    return true;
                }
                if ((ni.remarks || '').trim().toUpperCase() !== (oi.remarks || '').trim().toUpperCase()) {
                    /* console print removed */
                    return true;
                }
            }

            return false;
        }

        const modal = $('#newEntryModal');
        const openBtn = $('#openNewEntry');
        const closeBtn = $('#closeModal');
        const ledgeSelect = $('#ledgeSelect');
        const itemDetails = $('#itemDetails');

        // Database Items from Backend (All item descriptions, units, and ledge categories mapped to UPPERCASE/trimmed)
        let existingDBItems = [];
        try {
            const inventoryDataEl = document.getElementById('inventory-data');
            if (inventoryDataEl) {
                const parsed = JSON.parse(inventoryDataEl.textContent || '[]');
                if (Array.isArray(parsed)) {
                    existingDBItems = parsed.filter(Boolean).map(item => {
                        return {
                            ...item,
                            description: (item.description || '').toUpperCase().trim(),
                            unit: (item.unit || '').toUpperCase().trim(),
                            location: (item.location || '').toUpperCase().trim(),
                            ledge_category: (item.ledge_category || '').toUpperCase().trim()
                        };
                    });
                }
            }
        } catch (e) {
            console.error("Error parsing inventory-data:", e);
        }
        const globalTotalStock = {{ (int)($totalInventory ?? 0) }};

        // Load admin-defined unit rules for auto-fill
        window._unitRules = {};
        fetch('{{ route("api.unit-rules") }}')
            .then(r => r.json())
            .then(rules => {
                const upperRules = {};
                Object.entries(rules || {}).forEach(([keyword, rule]) => {
                    const upperKeyword = keyword.toUpperCase().trim();
                    if (typeof rule === 'object' && rule !== null) {
                        upperRules[upperKeyword] = {
                            category: (rule.category || '').toUpperCase().trim(),
                            unit: (rule.unit || '').toUpperCase().trim(),
                            location: (rule.location || 'Not Specified').toUpperCase().trim()
                        };
                    } else {
                        upperRules[upperKeyword] = {
                            unit: (rule || '').toUpperCase().trim(),
                            location: 'NOT SPECIFIED'
                        };
                    }
                });
                window._unitRules = upperRules;
            })
            .catch(() => {});

        // Initialize Select2
        ledgeSelect.select2({
            placeholder: 'Search and Select Category',
            width: '100%',
            dropdownParent: $('#newEntryModal')
        });

        // Open Modal Listener (jQuery way)
        openBtn.on('click', window.openModal);

        // Final Submit Logic
        $('#newEntryForm').on('submit', function(e) {
            e.preventDefault();

            // Client-side validation for required fields
            const missingFields = [];
            
            // Check Category
            if (!$('#ledgeSelect').val()) {
                missingFields.push("Category Section");
            }
            
            // Check Supplier/Donor Name
            if (!$('#supplierNameSelect').val()) {
                missingFields.push("Supplier/Donor Name");
            }
            
            // Check Delivery Person if visible
            if ($('#deliveryPersonGroup').is(':visible') && !$('#deliveryPersonInput').val().trim()) {
                missingFields.push("Delivery Person Name");
            }
            
            // Check Delivery Status (only if not donor)
            const isDonor = $('#isDonorCheckbox').is(':checked');
            if (!isDonor && !$('#supplierStatusSelect').val()) {
                missingFields.push("Delivery Status");
            }
            
            // Check Received Date
            if (!$('#arrivalDate').val()) {
                missingFields.push("Received Date");
            }
            
            // Check dynamic item rows
            $('.item-entry-row').each(function(index) {
                const itemIdx = index + 1;
                const desc = ($(this).find('.item-select-dynamic').val() || '').trim();
                const qty = ($(this).find('.row-qty').val() || '').trim();
                const physQty = ($(this).find('.row-stock-balance').val() || '').trim();
                const status = $('#supplierStatusSelect').val();
                
                if (!desc) {
                    missingFields.push(`Item Type #${itemIdx}: Description`);
                }
                if (!qty) {
                    missingFields.push(`Item Type #${itemIdx}: Received Qty`);
                }
                if (status === 'Partial Delivery' && !physQty) {
                    missingFields.push(`Item Type #${itemIdx}: Physically Received Qty`);
                }
            });
            
            if (missingFields.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required Fields Missing',
                    html: `<div style="text-align: left; font-size: 0.9rem; font-weight: 600; color: var(--text-main);">
                            Please complete the following required fields before submitting:
                            <ul style="margin-top: 10px; padding-left: 20px; color: #ef4444; line-height: 1.6;">
                                ${missingFields.map(f => `<li>${f}</li>`).join('')}
                            </ul>
                           </div>`,
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            const btn = $(this).find('button[type="submit"]');
            const originalHtml = btn.html();

            // Gather Items & Validate Package Type Rules
            const items = [];
            let validationFailed = false;
            let invalidItemName = '';

            $('.item-entry-row').each(function() {
                const desc = ($(this).find('.item-select-dynamic').val() || '').trim();
                const unit = ($(this).find('.row-unit').val() || '').trim();
                const location = ($(this).find('.row-location').val() || '').trim();

                if (unit.indexOf("Confront Admin") !== -1 || unit.indexOf("not assigned") !== -1 || !unit || location.indexOf("Confront Admin") !== -1 || location.indexOf("Confront the Admin") !== -1 || location.indexOf("not assigned") !== -1 || !location) {
                    validationFailed = true;
                    invalidItemName = desc || 'Unnamed Item';
                }

                items.push({
                    description: desc,
                    unit: unit,
                    stock_balance: $(this).find('.row-stock-balance').val(),
                    qty: $(this).find('.row-qty').val(),
                    variance: $(this).find('.row-variance').val() || '0',
                    remarks: $(this).find('.row-remarks').val(),
                    location: location
                });
            });

            if (validationFailed) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Package Type or Location Missing',
                    text: `The item "${invalidItemName}" does not have a valid package type or store location assigned. Please enter or select them before submitting.`,
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            const supplierStatus = isDonor ? 'Donor' : $('#supplierStatusSelect').val();
            const acquisitionType = isDonor ? 'Donor' : 'Supplier';
            const supplierOrDonorName = ($('#supplierNameSelect').val() || '').trim();
            const donorName = isDonor ? supplierOrDonorName : null;
            const supplierName = isDonor ? null : supplierOrDonorName;

            const payload = {
                _token: '{{ csrf_token() }}',
                ledge_category: ledgeSelect.val(),
                supplier_name: supplierName || null,
                supplier_status: supplierStatus,
                donor_name: donorName || null,
                acquisition_type: acquisitionType,
                delivery_person: $('#deliveryPersonGroup').is(':visible') ? ($('#deliveryPersonInput').val() || '').trim() : null,
                entry_date: $('#entryDate').val(),
                arrival_date: $('#arrivalDate').val(),
                items: items
            };

            if (window.originalRollbackPayload) {
                if (!hasRollbackChanges(payload, window.originalRollbackPayload)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Corrections Made',
                        text: 'You have not made any changes to the flagged fields. Please update the incorrect information before resubmitting.',
                        confirmButtonColor: '#b91c1c'
                    });
                    return;
                }
            }

            // Loading State
            btn.html('<i class="animate-spin" data-lucide="loader-2"></i> Saving...').prop('disabled', true);
            if (typeof lucide !== 'undefined') lucide.createIcons();

            $.ajax({
                url: '{{ route("inventory.store", [], false) }}',
                method: 'POST',
                data: payload,
                success: function(response) {
                    if (response.success) {
                        if (response.is_pending) {
                            window.closeModal();
                            Swal.fire({
                                icon: 'info',
                                title: 'REQUEST SUBMITTED',
                                text: 'Your entry is currently pending administrative approval. You will receive a notification once the request is authorized.',
                                confirmButtonColor: '#4f46e5',
                                confirmButtonText: 'Great, Thank you!'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            showToast('Success', 'Inventory records saved successfully!', 'success');



                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        }
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'A security or protocol error occurred. Please refresh and try again.';
                    window.closeModal();
                    Swal.fire({
                        icon: 'error',
                        title: 'Submission Failed',
                        text: errorMsg,
                        confirmButtonColor: '#ef4444'
                    });
                    btn.html(originalHtml).prop('disabled', false);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            });
        });
        closeBtn.on('click', window.closeModal);
        $(window).on('click', (e) => {
            if (e.target == modal[0]) closeModal();
        });

        // Handle Ledge Selection
        ledgeSelect.on('change select2:select', function() {
            try {
                const selectedLedge = ($(this).val() || '').toUpperCase().trim();
                if (selectedLedge) {
                    // Keep Ledge on Top, Show Batch Controls below it
                    $('#qtyControl').show().animate({
                        opacity: 1
                    }, 400);
                    $('#supplierControl').show().animate({
                        opacity: 1
                    }, 400);
                    $('#dateControl').show().animate({
                        opacity: 1
                    }, 400);

                    itemDetails.slideDown(400);
                    $('#modalFooter').fadeIn(400);

                    // Update any existing item dropdowns to reflect the new ledge category
                    $('.item-select-dynamic').each(function() {
                        const $select = $(this);
                        const currentVal = $select.val();
                        const filtered = (existingDBItems || []).filter(item => item && (item.ledge_category || '').toUpperCase().trim() === selectedLedge);

                        let optionsHtml = '<option value=""></option>';
                        filtered.forEach(item => {
                            optionsHtml += `<option value="${item.description}">${item.description}</option>`;
                        });

                        $select.html(optionsHtml);
                        $select.val(null).trigger('change.select2').trigger('change');
                    });

                    if ($('#itemsContainer').children().length === 0) {
                        renderItemRows(1);
                    }
                } else {
                    $('#qtyControl').hide().css('opacity', 0);
                    $('#supplierControl').hide().css('opacity', 0);
                    $('#dateControl').hide().css('opacity', 0);
                    itemDetails.slideUp(400);
                    $('#modalFooter').fadeOut(400);
                }
            } catch (err) {
                console.error("Error in ledge selection handler:", err);
            }
        });

        function renderItemRows(count, append = false) {
            const container = $('#itemsContainer');
            if (!append) container.empty();

            const selectedLedge = ($('#ledgeSelect').val() || '').toUpperCase().trim();
            const filteredItems = (existingDBItems || []).filter(item => item && (item.ledge_category || '').toUpperCase().trim() === selectedLedge);

            for (let i = 0; i < count; i++) {
                const currentRows = container.children('.item-entry-row').length;
                const itemIdx = currentRows + 1;
                const rowHtml = `
                    <div class="item-entry-row" style="margin-bottom: 2rem; padding: 2rem 1.5rem 1.5rem 1.5rem; border: 1px solid var(--border-color); border-radius: 16px; background: var(--bg-card); position: relative;">
                        <div class="row-badge" style="position: absolute; top: -12px; left: 1rem; background: var(--primary); color: white; padding: 2px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 800;">ITEM TYPE #${itemIdx}</div>

                        <button type="button" class="remove-row-btn" style="position: absolute; top: 1.25rem; right: 1.25rem; background: rgba(239, 68, 68, 0.1); color: var(--danger); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition);">
                            <i data-lucide="trash-2" style="width: 16px;"></i>
                        </button>

                        <div class="form-grid">
                            <div class="form-group full-width">
                                 <label style="display: flex; align-items: center; gap: 6px;">
                                     <i data-lucide="search" style="width: 12px; color: var(--primary);"></i>
                                     Item Description (Search & Select) <span style="color: #ef4444; margin-left: 2px;">*</span>
                                 </label>
                                 <select class="item-select-dynamic" style="width: 100%;" required>
                                     <option value=""></option>
                                     ${filteredItems.map(item => `<option value="${item.description}">${item.description}</option>`).join('')}
                                 </select>
                                <div class="existing-stats" style="display: none; margin-top: 0.85rem; padding: 1rem; background: var(--bg-main); border-radius: 14px; border: 1px dashed var(--border-color); animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 32px; height: 32px; background: rgba(99, 102, 241, 0.15); color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.15);">
                                                <i data-lucide="layers" style="width: 16px;"></i>
                                            </div>
                                            <div>
                                                <div style="font-size: 0.6rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Total items</div>
                                                <div class="stat-stock-balance" style="font-size: 0.95rem; font-weight: 800; color: var(--text-main);">0</div>
                                            </div>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.15); color: var(--secondary); border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.15);">
                                                <i data-lucide="package" style="width: 16px;"></i>
                                            </div>
                                            <div>
                                                <div style="font-size: 0.6rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Previously Received</div>
                                                <div class="stat-received-qty" style="font-size: 0.95rem; font-weight: 800; color: var(--text-main);">0</div>
                                            </div>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 32px; height: 32px; background: rgba(59, 130, 246, 0.15); color: #3b82f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1);">
                                                <i data-lucide="plus-circle" style="width: 16px;"></i>
                                            </div>
                                            <div>
                                                <div class="lbl-dynamic-stock-balance" style="font-size: 0.6rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Stock Balance</div>
                                                <div class="stat-dynamic-stock-balance" style="font-size: 0.95rem; font-weight: 800; color: #3b82f6;">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div>
                                        <label style="display: flex; align-items: center; gap: 6px;">
                                            <i data-lucide="tag" style="width: 12px; color: var(--primary);"></i>
                                            Package Types
                                        </label>
                                        <div class="unit-container-sleek" style="position: relative; display: flex; align-items: center; width: 100%;">
                                            <input type="text" class="row-unit" value="" placeholder="Auto-determined" readonly style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 1px solid var(--border-color); border-radius: 12px; background: rgba(99, 102, 241, 0.03); color: var(--text-main); cursor: not-allowed; font-weight: 800; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.05em; transition: all 0.3s ease; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                                            <div style="position: absolute; left: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary); opacity: 0.8; pointer-events: none;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><circle cx="7" cy="7" r="1"/></svg>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label style="display: flex; align-items: center; gap: 6px;">
                                            <i data-lucide="map-pin" style="width: 12px; color: var(--primary);"></i>
                                            Store Location
                                        </label>
                                        <div class="location-container-sleek" style="position: relative; display: flex; align-items: center; width: 100%;">
                                            <input type="text" class="row-location" value="" placeholder="Auto-determined" readonly style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 1px solid var(--border-color); border-radius: 12px; background: rgba(99, 102, 241, 0.03); color: var(--text-main); cursor: not-allowed; font-weight: 800; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.05em; transition: all 0.3s ease; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                                            <div style="position: absolute; left: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary); opacity: 0.8; pointer-events: none;">
                                                <i data-lucide="map-pin" style="width: 16px; height: 16px;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div>
                                        <label class="lbl-received-qty" style="display: flex; align-items: center; gap: 6px;">
                                            <i data-lucide="plus-circle" style="width: 12px; color: var(--primary);"></i>
                                            <span class="lbl-text">Received Qty</span> <span style="color: #ef4444; margin-left: 2px;">*</span>
                                        </label>
                                        <input type="number" class="row-qty" style="border-color: var(--primary-light); width: 100%;" required>
                                    </div>
                                    <div class="actual-qty-group" style="display: none;">
                                        <label style="display: flex; align-items: center; gap: 6px;">
                                            <i data-lucide="check-circle" style="width: 12px; color: #10b981;"></i>
                                            <span style="color: #10b981; font-weight: 800;">Physically Received Qty</span> <span style="color: #ef4444; margin-left: 2px;">*</span>
                                        </label>
                                        <input type="number" class="row-stock-balance" value="0" style="border-color: #10b981; width: 100%;" required>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" class="row-variance" value="0">

                            <div class="form-group full-width">
                                <label style="display: flex; align-items: center; gap: 6px;">
                                    <i data-lucide="message-square" style="width: 12px; color: var(--primary);"></i>
                                    Situation Remarks / Notes
                                </label>
                                <input type="text" class="row-remarks" placeholder="Briefly describe the current situation or condition...">
                            </div>
                        </div>
                    </div>
                `;
                const $row = $(rowHtml);
                container.append($row);

                const stockInput = $row.find('.row-stock-balance');
                const qtyInput = $row.find('.row-qty');
                const varianceInput = $row.find('.row-variance');
                const statsPanel = $row.find('.existing-stats');

                // Initialize Select2 to allow DB items and new items with auto-capitalized tags
                $row.find('.item-select-dynamic').select2({
                    placeholder: "Search, select, or type new item...",
                    width: '100%',
                    tags: true, // Allow new tags
                    dropdownParent: $('#newEntryModal'),
                    createTag: function (params) {
                        var term = $.trim(params.term).toUpperCase();
                        if (term === '') {
                            return null;
                        }
                        return {
                            id: term,
                            text: term,
                            newTag: true
                        };
                    }
                });


                // Handle Item Selection to show previous data explicitly
                $row.on('change', '.item-select-dynamic', function() {
                    const selectedDesc = ($(this).val() || '').trim().toUpperCase();
                    const prevData = (existingDBItems || []).find(item => item && item.description === selectedDesc);

                    // Auto-fill unit and location based on admin-defined unit rules or existing item data
                    const $unitInput = $row.find('.row-unit');
                    const $locationInput = $row.find('.row-location');

                    const resetUnitStyle = () => {
                        $unitInput.css({
                            'color': 'var(--text-main)',
                            'border-color': 'var(--border-color)',
                            'background': 'rgba(99, 102, 241, 0.03)',
                            'box-shadow': 'inset 0 2px 4px rgba(0,0,0,0.02)',
                            'font-style': 'normal',
                            'font-size': '0.95rem',
                            'letter-spacing': '0.05em'
                        });
                        $locationInput.css({
                            'color': 'var(--text-main)',
                            'border-color': 'var(--border-color)',
                            'background': 'rgba(99, 102, 241, 0.03)',
                            'box-shadow': 'inset 0 2px 4px rgba(0,0,0,0.02)',
                            'font-style': 'normal',
                            'font-size': '0.95rem',
                            'letter-spacing': '0.05em'
                        });
                    };

                    const setErrorUnitStyle = () => {
                        $unitInput.css({
                            'color': '#ef4444',
                            'border-color': '#fca5a5',
                            'background': 'rgba(239, 68, 68, 0.06)',
                            'box-shadow': '0 0 0 3px rgba(239, 68, 68, 0.15)',
                            'font-style': 'italic',
                            'font-size': '0.82rem',
                            'letter-spacing': 'normal'
                        });
                        $locationInput.css({
                            'color': '#ef4444',
                            'border-color': '#fca5a5',
                            'background': 'rgba(239, 68, 68, 0.06)',
                            'box-shadow': '0 0 0 3px rgba(239, 68, 68, 0.15)',
                            'font-style': 'italic',
                            'font-size': '0.82rem',
                            'letter-spacing': 'normal'
                        });
                    };

                    const setEditableStyle = () => {
                        $unitInput.prop('readonly', false).attr('placeholder', 'Enter package type...').css({
                            'color': 'var(--text-main)',
                            'border-color': 'var(--primary)',
                            'background': 'var(--bg-card)',
                            'box-shadow': '0 0 0 3px rgba(99, 102, 241, 0.1)',
                            'font-style': 'normal',
                            'font-size': '0.95rem',
                            'letter-spacing': 'normal',
                            'cursor': 'text'
                        });
                        $locationInput.prop('readonly', false).attr('placeholder', 'Enter store location...').css({
                            'color': 'var(--text-main)',
                            'border-color': 'var(--primary)',
                            'background': 'var(--bg-card)',
                            'box-shadow': '0 0 0 3px rgba(99, 102, 241, 0.1)',
                            'font-style': 'normal',
                            'font-size': '0.95rem',
                            'letter-spacing': 'normal',
                            'cursor': 'text'
                        });
                    };

                    const showWarningHint = () => {
                        removeWarningHint();
                        const warningHtml = `<div class="unassigned-warning-hint" style="margin-top:8px; font-size:0.75rem; font-weight:700; color:#ef4444; display:flex; align-items:center; gap:6px; line-height:1.4; padding: 6px 12px; background: rgba(239, 68, 68, 0.05); border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.15);">
                            <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>This item is not yet saved/assigned by the Admin. Please contact the Admin to register this item's package type and store location.</span>
                        </div>`;
                        $unitInput.closest('.form-group').append(warningHtml);
                    };

                    const removeWarningHint = () => {
                        $row.find('.unassigned-warning-hint').remove();
                    };

                    const setReadonlyStyle = () => {
                        $unitInput.prop('readonly', true).attr('placeholder', 'Auto-determined').css({
                            'cursor': 'not-allowed'
                        });
                        $locationInput.prop('readonly', true).attr('placeholder', 'Auto-determined').css({
                            'cursor': 'not-allowed'
                        });
                        resetUnitStyle();
                        removeWarningHint();
                    };

                    const setWarningDisabledStyle = () => {
                        $unitInput.prop('readonly', true).attr('placeholder', 'Auto-determined').css({
                            'cursor': 'not-allowed'
                        });
                        $locationInput.prop('readonly', true).attr('placeholder', 'Auto-determined').css({
                            'cursor': 'not-allowed'
                        });
                        setErrorUnitStyle();
                        showWarningHint();
                    };

                    if (!selectedDesc) {
                        $unitInput.val('');
                        $locationInput.val('');
                        setReadonlyStyle();
                    } else if (window._unitRules) {
                        const descUpper = selectedDesc.toUpperCase().trim();
                        const currentCat = ($('#ledgeSelect').val() || '').toUpperCase().trim();

                        const matchedUnit = Object.entries(window._unitRules).find(([kw, rule]) => {
                            const upperKw = kw.toUpperCase().trim();
                            if (!descUpper.includes(upperKw)) return false;

                            if (typeof rule === 'object' && rule !== null) {
                                // Must match category if rule specifies one
                                const ruleCat = (rule.category || '').toUpperCase().trim();
                                return ruleCat === currentCat;
                            }
                            return true; // Fallback for old string rules
                        });

                        if (matchedUnit) {
                            const ruleValue = matchedUnit[1];
                            const unitVal = typeof ruleValue === 'object' ? ruleValue.unit : ruleValue;
                            const locVal = typeof ruleValue === 'object' ? (ruleValue.location || 'Not Specified') : 'Not Specified';
                            $unitInput.val(unitVal);
                            $locationInput.val(locVal);

                            if (unitVal.includes("Confront Admin") || locVal.includes("Confront Admin") || unitVal.includes("not assigned") || locVal.includes("not assigned")) {
                                setWarningDisabledStyle();
                            } else {
                                setReadonlyStyle();
                            }
                        } else if (prevData && prevData.unit) {
                            $unitInput.val(prevData.unit);
                            $locationInput.val(prevData.location || 'Not Specified');

                            const unitVal = prevData.unit;
                            const locVal = prevData.location || '';
                            if (unitVal.includes("Confront Admin") || locVal.includes("Confront Admin") || unitVal.includes("not assigned") || locVal.includes("not assigned")) {
                                setWarningDisabledStyle();
                            } else {
                                setReadonlyStyle();
                            }
                        } else {
                            $unitInput.val('Package type not assigned.');
                            $locationInput.val('Confront Admin!');
                            setWarningDisabledStyle();
                        }
                    } else if (prevData && prevData.unit) {
                        $unitInput.val(prevData.unit);
                        $locationInput.val(prevData.location || 'Not Specified');

                        const unitVal = prevData.unit;
                        const locVal = prevData.location || '';
                        if (unitVal.includes("Confront Admin") || locVal.includes("Confront Admin") || unitVal.includes("not assigned") || locVal.includes("not assigned")) {
                            setWarningDisabledStyle();
                        } else {
                            setReadonlyStyle();
                        }
                    } else {
                        $unitInput.val('Package type not assigned.');
                        $locationInput.val('Confront Admin!');
                        setWarningDisabledStyle();
                    }

                    const updateStatsPanel = () => {
                        const unitLabel = ($unitInput.val() || '').toUpperCase();
                        const isUnitValid = unitLabel && !unitLabel.includes('CONFRONT ADMIN');
                        const finalUnit = isUnitValid ? unitLabel : 'PACKAGE TYPES';

                        if (prevData) {
                            $row.find('.stat-stock-balance').text(parseFloat(prevData.stock_balance).toLocaleString() + ' ' + finalUnit);
                            $row.find('.stat-received-qty').text(parseFloat(prevData.qty).toLocaleString() + ' ' + finalUnit);
                            $row.find('.stat-dynamic-stock-balance').text(parseFloat(prevData.stock_balance).toLocaleString());
                            statsPanel.slideDown(300);

                            // Visual cue for existing item
                            if (!$row.find('.existing-indicator').length) {
                                $row.find('.row-badge').append(' <span class="existing-indicator" style="font-size: 0.6rem; opacity: 0.8; background: rgba(255,255,255,0.2); padding: 1px 6px; border-radius: 4px; margin-left: 4px;">(Restocking)</span>');
                            }
                        } else if (selectedDesc) {
                            $row.find('.stat-stock-balance').text('0 ' + finalUnit);
                            $row.find('.stat-received-qty').text('0 ' + finalUnit);
                            $row.find('.stat-dynamic-stock-balance').text('0');
                            statsPanel.slideDown(300);
                            $row.find('.existing-indicator').remove();
                        } else {
                            statsPanel.slideUp(300);
                        }
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    };

                    if (selectedDesc) {
                        // Sync initial values and clear placeholders
                        stockInput.val(qtyInput.val() || 0);
                        qtyInput.removeAttr('placeholder');
                        varianceInput.attr('placeholder', '0');
                        updateStatsPanel();
                    } else {
                        statsPanel.slideUp(300);
                    }
                    if (typeof updateSubmitButtonState === 'function') {
                        updateSubmitButtonState();
                    }
                });

                // Auto-Calculation Logic: Sync Stock Balance to follow Received Qty
                $row.on('input', '.row-qty, .row-stock-balance', function() {
                    const status = $('#supplierStatusSelect').val();
                    const qtyVal = parseFloat(qtyInput.val()) || 0;
                    
                    if (status !== 'Partial Delivery') {
                        stockInput.val(qtyVal);
                    }

                    const stockVal = parseFloat(stockInput.val()) || 0;
                    const result = stockVal - qtyVal;
                    varianceInput.val(result);

                    if (result > 0) {
                        varianceInput.css('color', '#10b981'); // Green for positive (Surplus)
                    } else if (result < 0) {
                        varianceInput.css('color', '#ef4444'); // Red for negative (Shortage)
                    } else {
                        varianceInput.css('color', '#3b82f6'); // Blue for zero (Balanced)
                    }
                });

                // Apply initial labels based on current status
                const initStatus = $('#supplierStatusSelect').val();
                if (initStatus === 'Partial Delivery') {
                    $row.find('.lbl-received-qty .lbl-text').text('Expected / Invoice Qty');
                    $row.find('.row-qty').css({'border-color': '#f59e0b', 'background': 'var(--bg-card)'}).prop('readonly', false);
                    $row.find('.actual-qty-group').show();
                } else {
                    $row.find('.lbl-received-qty .lbl-text').text('Received Qty');
                    $row.find('.row-qty').css({'border-color': 'var(--primary-light)', 'background': 'var(--bg-main)'}).prop('readonly', false);
                    $row.find('.actual-qty-group').hide();
                }
            }
            if (typeof lucide !== 'undefined') lucide.createIcons(); // Re-init icons
            updateSubmitButtonState();
        }

        function updateSubmitButtonState() {
            let disabled = false;
            $('#itemsContainer .item-entry-row').each(function() {
                const unitVal = ($(this).find('.row-unit').val() || '').trim();
                const locationVal = ($(this).find('.row-location').val() || '').trim();

                if (unitVal.indexOf("Package type not assigned") !== -1 ||
                    unitVal.indexOf("Confront Admin") !== -1 ||
                    locationVal.indexOf("Confront Admin") !== -1 ||
                    locationVal.indexOf("Confront the Admin") !== -1 ||
                    !unitVal ||
                    !locationVal) {
                    disabled = true;
                }
            });

            const submitBtn = $('#newEntryForm button[type="submit"]');
            if (disabled) {
                submitBtn.prop('disabled', true);
                submitBtn.css({
                    'opacity': '0.5',
                    'cursor': 'not-allowed',
                    'pointer-events': 'none'
                });
                submitBtn.attr('title', 'Please assign a package type and store location before submitting.');
            } else {
                submitBtn.prop('disabled', false);
                submitBtn.css({
                    'opacity': '1',
                    'cursor': 'pointer',
                    'pointer-events': 'auto'
                });
                submitBtn.removeAttr('title');
            }
        }

        $(document).on('input', '.row-unit, .row-location', function() {
            updateSubmitButtonState();
        });

        // Initialize Supplier Status with Nice Templates
        function formatStatus(opt) {
            if (!opt.id) return opt.text;
            const icon = $(opt.element).data('icon');
            const color = $(opt.element).data('color');
            return $(`
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: ${color}; box-shadow: 0 0 8px ${color}80;"></div>
                    <span style="font-weight: 600; font-size: 0.9rem;">${opt.text}</span>
                </div>
            `);
        }

        $('#supplierStatusSelect').select2({
            placeholder: "Select Status",
            width: '100%',
            minimumResultsForSearch: -1,
            templateResult: formatStatus,
            templateSelection: formatStatus,
            dropdownParent: $('#newEntryModal')
        });

        const suppliersList = @json($allSuppliers ?? []);
        const donorsList = @json($allDonors ?? []);

        // Toggle Donor Acquisition state
        $('#isDonorCheckbox').on('change', function() {
            const isDonor = $(this).is(':checked');
            const list = isDonor ? donorsList : suppliersList;
            
            const select = $('#supplierNameSelect');
            const currentVal = select.val();
            
            select.empty();
            select.append('<option value=""></option>');
            list.forEach(name => {
                select.append(new Option(name, name));
            });
            
            if (currentVal && !list.includes(currentVal)) {
                select.append(new Option(currentVal, currentVal));
                select.val(currentVal).trigger('change');
            } else if (list.includes(currentVal)) {
                select.val(currentVal).trigger('change');
            } else {
                select.val(null).trigger('change');
            }

            $('#supplierStatusSelect').trigger('change');
        });

        // Toggle Delivery Status and Update Labels
        $('#supplierStatusSelect').on('change', function() {
            const status = $(this).val();

            // Update Labels & Fields for Partial Delivery UI
            if (status === 'Partial Delivery') {
                $('.item-entry-row').each(function() {
                    $(this).find('.lbl-received-qty .lbl-text').text('Expected / Invoice Qty');
                    $(this).find('.row-qty').css({'border-color': '#f59e0b', 'background': 'var(--bg-card)'}).prop('readonly', false);
                    $(this).find('.actual-qty-group').slideDown(300);
                });
            } else {
                $('.item-entry-row').each(function() {
                    $(this).find('.lbl-received-qty .lbl-text').text('Received Qty');
                    $(this).find('.row-qty').css({'border-color': 'var(--primary-light)', 'background': 'var(--bg-main)'}).prop('readonly', false);
                    $(this).find('.actual-qty-group').slideUp(300);
                    
                    // Sync stock back to expected qty
                    const qtyVal = parseFloat($(this).find('.row-qty').val()) || 0;
                    $(this).find('.row-stock-balance').val(qtyVal);
                    $(this).find('.row-variance').val(0);
                });
            }
        });

        // Initialize Supplier Name Search/Type
        $('#supplierNameSelect').select2({
            placeholder: "Search or type new supplier/donor...",
            width: '100%',
            tags: true,
            dropdownParent: $('#newEntryModal')
        });

        // Dynamic Supplier Details display
        const suppliersRegistry = @json(\App\Models\Setting::get('suppliers_registry', []));
        $('#supplierNameSelect').on('change', function() {
            const name = $(this).val();
            const deliveryGroup = $('#deliveryPersonGroup');
            const deliveryInput = $('#deliveryPersonInput');
            
            let details = null;
            if (name) {
                const searchKey = name.toLowerCase().trim();
                for (const key in suppliersRegistry) {
                    if (key.toLowerCase().trim() === searchKey) {
                        details = suppliersRegistry[key];
                        break;
                    }
                }
            }
            
            if (details) {
                // Show the delivery person input box and prefill it
                deliveryInput.val(details.delivery_person || '');
                deliveryGroup.slideDown(250);
            } else {
                deliveryGroup.slideUp(200);
                deliveryInput.val('');
            }
        });

        const multiQtyInput = $('#multiQty');

        // Automatic Generation on Input
        multiQtyInput.on('input', function() {
            const qty = parseInt($(this).val()) || 0;
            if (qty > 0 && qty <= 50) { // Limit to 50 for safety
                renderItemRows(qty);
            } else if (qty === 0) {
                $('#itemsContainer').empty();
            }
        });

        // Remove Row Logic
        $(document).on('click', '.remove-row-btn', function() {
            $(this).closest('.item-entry-row').fadeOut(300, function() {
                $(this).remove();
                // Update quantity display
                const currentCount = $('#itemsContainer').children('.item-entry-row').length;
                multiQtyInput.val(currentCount);
                // Re-index all badges correctly
                $('#itemsContainer .item-entry-row').each(function(index) {
                    $(this).find('.row-badge').text('ITEM TYPE #' + (index + 1));
                });
                if (typeof updateSubmitButtonState === 'function') {
                    updateSubmitButtonState();
                }
            });
        });

        // Check for continue_batch parameter
        const urlParams = new URLSearchParams(window.location.search);
        const continueBatchId = urlParams.get('continue_batch');

        if (continueBatchId) {
            $.ajax({
                url: `/received-items/${continueBatchId}?json=1`,
                method: 'GET',
                success: function(response) {
                    const batch = response.batch;
                    if (batch) {
                        // Open Modal
                        modal.css('display', 'flex');

                        // Set Date
                        const now = new Date();
                        const year = now.getFullYear();
                        const month = String(now.getMonth() + 1).padStart(2, '0');
                        const day = String(now.getDate()).padStart(2, '0');
                        const hours = String(now.getHours()).padStart(2, '0');
                        const minutes = String(now.getMinutes()).padStart(2, '0');
                        const seconds = String(now.getSeconds()).padStart(2, '0');
                        const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                        $('#entryDate').val(formattedDate);
                        $('#arrivalDate').val(batch.arrival_date || new Date().toISOString().split('T')[0]);

                        // Set Ledge
                        ledgeSelect.val(batch.ledge_category).trigger('change');

                        // Set Supplier/Donor (clean name)
                        const isDonor = batch.acquisition_type === 'Donor';
                        const rawSupplier = isDonor ? batch.donor_name : batch.supplier_name;
                        const cleanSupplier = rawSupplier ? rawSupplier.replace(/\s\[.*\]$/, '') : '';

                        // We need to wait for Select2 to be ready or just set values if they exist
                        setTimeout(() => {
                            $('#isDonorCheckbox').prop('checked', isDonor).trigger('change');
                            $('#supplierNameSelect').val(cleanSupplier).trigger('change');
                            if (batch.delivery_person) {
                                $('#deliveryPersonInput').val(batch.delivery_person);
                            }
                            if (!isDonor) {
                                $('#supplierStatusSelect').val(batch.supplier_status || 'Full Delivery').trigger('change');
                            }

                            // Render Rows for items
                            if (batch.items && batch.items.length > 0) {
                                $('#multiQty').val(batch.items.length);
                                renderItemRows(batch.items.length);

                                // Fill row data
                                $('.item-entry-row').each(function(index) {
                                    const item = batch.items[index];
                                    const $row = $(this);

                                    $row.find('.item-select-dynamic').val(item.description).trigger('change');

                                    $row.find('.row-qty').focus();
                                });
                            }
                        }, 500);

                        // Clean URL
                        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                        window.history.replaceState({}, '', cleanUrl);
                    }
                }
            });
        }

        // ─────────────────────────────────────────────────────────────
        // Rollback Pre-Fill: ?rollback={edit_request_id}
        // Opens the form pre-filled with the original submission data
        // and highlights admin-flagged fields with red borders + hints
        // ─────────────────────────────────────────────────────────────
        const rollbackId = urlParams.get('rollback');

        if (rollbackId) {
            fetch(`/api/sra-rollback/${rollbackId}`)
                .then(res => {
                    if (!res.ok) throw new Error('Could not load rollback data');
                    return res.json();
                })
                .then(data => {
                    const payload       = data.payload || {};
                    const flaggedFields = data.flagged_fields || {};
                    const generalNote   = data.general_note || '';

                    if (!payload || Object.keys(payload).length === 0) return;
                    window.originalRollbackPayload = JSON.parse(JSON.stringify(payload));

                    // Open Modal
                    modal.css('display', 'flex');

                    // Add a red alert banner above the form controls
                    const bannerHtml = `
                        <div id="rollback-alert-banner" style="margin-bottom: 1rem; border-radius: 14px; overflow: hidden; border: 2px solid #fca5a5; box-shadow: 0 4px 16px rgba(239,68,68,0.1);">
                            <div style="background: linear-gradient(135deg, #ef4444, #dc2626); padding: 0.85rem 1.1rem; display: flex; align-items: center; gap: 10px;">
                                <svg style="width: 18px; height: 18px; color: white; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span style="font-size: 0.82rem; font-weight: 900; color: white; text-transform: uppercase; letter-spacing: 0.06em;">Correction Required — Admin Rollback</span>
                            </div>
                            <div style="background: #fff5f5; padding: 0.75rem 1.1rem; font-size: 0.82rem; color: #7f1d1d; line-height: 1.6;">
                                Fields highlighted in <b style="color:#ef4444;">red</b> need to be corrected per the Admin's instructions.
                                ${generalNote ? `<div style="margin-top: 6px; padding: 8px 12px; background: white; border-radius: 8px; border: 1px solid #fecaca;"><b>Admin Note:</b> ${generalNote}</div>` : ''}
                            </div>
                        </div>`;

                    const controlsArea = document.querySelector('.modal-controls-sticky .form-grid');
                    if (controlsArea && !document.getElementById('rollback-alert-banner')) {
                        controlsArea.insertAdjacentHTML('beforebegin', bannerHtml);
                    }

                    // Set timestamp
                    const now = new Date();
                    const formattedDate = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}-${String(now.getDate()).padStart(2,'0')} ${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}:${String(now.getSeconds()).padStart(2,'0')}`;
                    $('#entryDate').val(formattedDate);

                    if (payload.arrival_date) {
                        $('#arrivalDate').val(payload.arrival_date);
                    }

                    // Set ledge/category
                    if (payload.ledge_category) {
                        ledgeSelect.val(payload.ledge_category).trigger('change');
                    }

                    setTimeout(() => {
                        // Set supplier/donor
                        const isDonor    = payload.acquisition_type === 'Donor';
                        const supplierVal = isDonor ? (payload.donor_name || '') : (payload.supplier_name || '');
                        const cleanVal   = supplierVal.replace(/\s\[.*\]$/, '');

                        $('#isDonorCheckbox').prop('checked', isDonor).trigger('change');

                        if (cleanVal && $('#supplierNameSelect option[value="' + cleanVal + '"]').length === 0) {
                            $('#supplierNameSelect').append(new Option(cleanVal, cleanVal, true, true));
                        }
                        $('#supplierNameSelect').val(cleanVal).trigger('change');
                        if (payload.delivery_person) {
                            $('#deliveryPersonInput').val(payload.delivery_person);
                        }

                        if (!isDonor && payload.supplier_status) {
                            $('#supplierStatusSelect').val(payload.supplier_status).trigger('change');
                        }

                        // Render item rows from payload.items
                        const items = payload.items || [];
                        if (items.length > 0) {
                            $('#multiQty').val(items.length);
                            renderItemRows(items.length);

                            setTimeout(() => {
                                $('.item-entry-row').each(function(idx) {
                                    const itm  = items[idx];
                                    const $row = $(this);
                                    if (!itm) return;

                                    const descSel = $row.find('.item-select-dynamic');
                                    if (descSel.find('option[value="' + itm.description + '"]').length === 0) {
                                        descSel.append(new Option(itm.description, itm.description, true, true));
                                    }
                                    descSel.val(itm.description).trigger('change');
                                    $row.find('.row-qty').val(itm.qty || '');
                                    if (itm.unit) {
                                        $row.find('.row-unit').val(itm.unit);
                                    }
                                    if (itm.location) {
                                        $row.find('.row-location').val(itm.location);
                                    }
                                    $row.find('.row-stock-balance').val(itm.stock_balance || '');
                                    $row.find('.row-variance').val(itm.variance || 0);
                                    $row.find('.row-remarks').val(itm.remarks || '');
                                });

                                _applyRollbackHighlights(flaggedFields);
                            }, 300);
                        } else {
                            setTimeout(() => _applyRollbackHighlights(flaggedFields), 300);
                        }

                    }, 500);

                    // Clean URL after loading
                    window.history.replaceState({}, '', window.location.protocol + '//' + window.location.host + window.location.pathname);
                })
                .catch(err => {
                    /* console print removed */
                    if (typeof showToast === 'function') {
                        showToast('Error', 'Could not load your previous submission data.', 'error');
                    }
                });
        }

        /**
         * Highlight admin-flagged fields with a red border and a correction hint.
         * @param {Object} flaggedFields  { field_key: "admin note" }
         */
        function _applyRollbackHighlights(flaggedFields) {
            if (!flaggedFields || Object.keys(flaggedFields).length === 0) return;

            const RED_BORDER = '2px solid #b91c1c';
            const RED_SHADOW = '0 0 0 4px rgba(185,28,28,0.15)';

            // Maps field keys → functions that return jQuery elements to highlight
            const FIELD_MAP = {
                supplier_name:    () => [$('#supplierNameSelect').parent().find('.select2-selection').length ? $('#supplierNameSelect').parent().find('.select2-selection') : $('#supplierNameSelect').closest('.select2-container')],
                supplier_status:  () => [$('#supplierStatusSelect').parent().find('.select2-selection').length ? $('#supplierStatusSelect').parent().find('.select2-selection') : $('#supplierStatusSelect')],
                arrival_date:     () => [$('#arrivalDate')],
                entry_date:       () => [$('#arrivalDate')], // entry_date maps to same visible input
                ledge_category:   () => [$('#ledgeSelect').parent().find('.select2-selection').length ? $('#ledgeSelect').parent().find('.select2-selection') : $('#ledgeSelect').closest('.select2-container')],
                acquisition_type: () => [$('#isDonorCheckbox').closest('label')],
                item_description: () => $('.item-select-dynamic').closest('.select2-container').toArray().map(el => $(el)),
                item_qty:         () => $('.row-qty').toArray().map(el => $(el)),
                item_unit:        () => $('.row-unit').toArray().map(el => $(el)),
                item_remarks:     () => $('.row-remarks').toArray().map(el => $(el)),
            };

            Object.keys(flaggedFields).forEach(key => {
                const note   = flaggedFields[key];
                const getEls = FIELD_MAP[key];
                if (!getEls) return;

                const els = getEls();
                els.forEach(($el, i) => {
                    if (!$el || !$el.length) return;

                    // Apply red border
                    $el.css({ 'border': RED_BORDER, 'box-shadow': RED_SHADOW, 'border-radius': '8px', 'transition': 'all 0.3s' });

                    // Inject hint below — avoid duplicates per element
                    const hintClass = 'rb-hint-' + key;
                    const parentContainer = $el.closest('.form-group, .select2-container, div').parent();
                    if (parentContainer.find('.' + hintClass).length === 0) {
                        const hintHtml = `<div class="${hintClass}" style="margin-top:5px; font-size:0.76rem; font-weight:700; color:#ef4444; display:flex; align-items:flex-start; gap:5px; line-height:1.4;">
                            <svg style="width:12px;height:12px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                            <span><b>Admin:</b> ${$('<div>').text(note).html()}</span>
                        </div>`;
                        $el.parent().after(hintHtml);
                    }
                });
            });
        }

    });

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

<!-- New Entry Modal -->
<div id="newEntryModal" class="modal-overlay">
    <div class="modal-content">
        <!-- Sticky Header Section -->
        <div class="modal-header-sticky">
            <div class="modal-header" style="margin-bottom: 0; padding: 0; border: none;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="background: var(--primary); color: white; width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.25);">
                        <i data-lucide="package-plus" style="width: 24px;"></i>
                    </div>
                    <div>
                        <h3 style="margin-bottom: 0.15rem;">Add New Inventory Item</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">Categorize and record stock balance accurately</p>
                    </div>
                </div>
                <button id="closeModal" style="background: var(--bg-main); border: none; width: 40px; height: 40px; border-radius: 12px; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; transition: var(--transition);">
                    <i data-lucide="x" style="width: 20px;"></i>
                </button>
            </div>
        </div>

        <form id="newEntryForm" novalidate>
            <!-- Sticky Batch Controls -->
            <div class="modal-controls-sticky">
                <div class="form-grid">
                    <div id="ledgeContainer" class="form-group full-width">
                        <label style="display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="layers" style="width: 12px; color: var(--primary);"></i>
                            Category Section (Search & Select) <span style="color: #ef4444; margin-left: 2px;">*</span>
                        </label>
                        <select id="ledgeSelect" class="select2-input" style="width: 100%;" required>
                            <option value=""></option>
                            @foreach($ledgeMap as $code => $name)
                                <option value="{{ $code }}">Category {{ $code }} - {{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="qtyControl" class="form-group" style="display: none; opacity: 0;">
                        <label style="display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="hash" style="width: 12px; color: var(--primary);"></i>
                            Number of different items
                        </label>
                        <input type="number" id="multiQty" min="1" value="1" placeholder="Qty">
                    </div>
                    <div id="supplierControl" class="form-group full-width" style="display: none; opacity: 0; margin-top: 0.75rem;">
                        <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="display: flex; align-items: center; gap: 6px;">
                                    <i data-lucide="truck" style="width: 12px; color: var(--primary);"></i>
                                    Supplier/Donor Name (Search or Type) <span style="color: #ef4444; margin-left: 2px;">*</span>
                                </label>
                                <select id="supplierNameSelect" style="width: 100%;" required>
                                    <option value=""></option>
                                    @foreach($allSuppliers as $supplier)
                                    <option value="{{ $supplier }}">{{ $supplier }}</option>
                                    @endforeach
                                </select>
                                <div id="deliveryPersonGroup" style="display: none; margin-top: 0.75rem;">
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                        <i data-lucide="user" style="width: 14px; color: var(--primary);"></i>
                                        Delivery Person Name <span style="color: #ef4444; margin-left: 2px;">*</span>
                                    </label>
                                    <input type="text" id="deliveryPersonInput" class="form-control" placeholder="Enter delivery person's name" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                </div>
                                <div style="margin-top: 0.75rem; display: flex; align-items: center; justify-content: space-between; background: var(--bg-main); border: 1px solid var(--border-color); padding: 0.75rem 1rem; border-radius: 12px; transition: all 0.3s ease;">
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
                                            <i data-lucide="gift" style="width: 12px; color: var(--primary);"></i>
                                            Mark as Donor
                                        </span>
                                        <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: none; font-weight: 600; padding-left: 18px;">Check if this is a donation</span>
                                    </div>
                                    <label class="premium-switch" style="position: relative; display: inline-block; width: 44px; height: 24px; margin-bottom: 0; cursor: pointer; user-select: none;">
                                        <input type="checkbox" id="isDonorCheckbox" style="opacity: 0; width: 0; height: 0;">
                                        <span class="slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--border-color); transition: .4s; border-radius: 24px;"></span>
                                    </label>
                                </div>
                            </div>
                            <div id="deliveryStatusGroup">
                                <label style="display: flex; align-items: center; gap: 6px;">
                                    <i data-lucide="activity" style="width: 12px; color: var(--primary);"></i>
                                    Delivery Status <span style="color: #ef4444; margin-left: 2px;">*</span>
                                </label>
                                <select id="supplierStatusSelect" style="width: 100%;" required>
                                    <option value="">Select Status</option>
                                    <option value="Full Delivery" data-icon="check-circle" data-color="#10b981">Full Delivery</option>
                                    <option value="Partial Delivery" data-icon="alert-circle" data-color="#f59e0b">Partial Delivery</option>
                                </select>

                                <div id="dateControl" class="form-group" style="display: none; opacity: 0; margin-top: 0.75rem;">
                                    <label style="display: flex; align-items: center; gap: 6px;">
                                        <i data-lucide="calendar" style="width: 12px; color: var(--primary);"></i>
                                        Received Date (Manual) <span style="color: #ef4444; margin-left: 2px;">*</span>
                                    </label>
                                    <input type="date" id="arrivalDate" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit;" required>
                                    <input type="hidden" id="entryDate">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scrollable Body -->
            <div class="modal-body-scroll">
                <div id="itemDetails" style="display: none;">
                    <div id="itemsContainer">
                        <!-- Item Rows will be injected here -->
                    </div>
                </div>
            </div>

            <!-- Sticky Footer -->
            <div class="modal-footer-sticky" id="modalFooter" style="display: none;">
                <div class="form-group full-width">
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 1.15rem; border: none; border-radius: 14px; cursor: pointer; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; gap: 0.75rem; box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);">
                        <i data-lucide="save" style="width: 20px;"></i>
                        Submit for approval
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
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
