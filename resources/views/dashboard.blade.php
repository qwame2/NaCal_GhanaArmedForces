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
                    {{ date('F d, Y') }}
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
            <button id="openNewEntry" onclick="openModal()" class="btn-primary" style="padding: 0.85rem 1.75rem; border-radius: 12px; border: none; background: var(--primary); color: white; display: flex; align-items: center; gap: 0.75rem; cursor: pointer; transition: var(--transition); box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.3);">
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

        <div class="stat-card glass-card pop-in float-card" style="border-top: 4px solid #ef4444; animation-delay: 0.4s;">
            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">
                <i data-lucide="alert-octagon"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Expired Items</span>
                <span class="stat-value">{{ number_format($expiredCount) }}</span>
                <div class="stat-trend" style="color: {{ $expiredCount > 0 ? '#ef4444' : '#10b981' }};">
                    <i data-lucide="{{ $expiredCount > 0 ? 'alert-circle' : 'check-circle' }}" style="width: 14px;"></i>
                    {{ $expiredCount > 0 ? 'Immediate action required' : 'Inventory healthy' }}
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
                <div class="stat-trend" style="color: {{ $isLedgeCritical ? '#ef4444' : '#10b981' }}; margin-top: 0.5rem;">
                    <i data-lucide="{{ $isLedgeCritical ? 'bell' : 'check-circle' }}" style="width: 14px;"></i>
                    {{ $isLedgeCritical ? $ledgeAlertMsg : 'No critical categories detected' }}
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
                        <span style="font-size: 0.65rem; color: var(--danger); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Alerts: {{ count($lowStockLedges) }} Ledges</span>
                    </div>

                    @foreach($lowStockLedges as $l)
                    @php
                    $isCritical = $l['percentage'] <= 50 || ($l['is_override'] ?? false);
                        $statusLabel=$isCritical ? 'CRITICAL DEPLETION' : 'WATCHLIST' ;
                        $statusColor=$isCritical ? '#ef4444' : '#f59e0b' ;
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
            @else
            <div style="text-align: center; padding: 2.5rem 0; color: var(--text-muted);">
                <i data-lucide="shield-check" style="width: 32px; height: 32px; margin-bottom: 0.75rem; color: #10b981; opacity: 0.8;"></i>
                <p style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">All Ledges Healthy</p>
                <p style="font-size: 0.7rem;">Stock levels are currently safe.</p>
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
        <span>Recent Stock Transactions</span>
        <button onclick="window.location.href='{{ route('receiveditems') }}'" class="btn-secondary" style="border: none; background: var(--bg-main); color: var(--primary); padding: 0.5rem 1rem; border-radius: 10px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">View All Transactions</button>
    </div>

    <style>
        @media (max-width: 768px) {
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
                border-radius: 12px;
                background: var(--bg-main) !important;
                padding: 1rem;
                border: 1px solid var(--border-color);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            }

            .activity-row:hover {
                transform: translateY(-2px);
            }

            .activity-row td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.4rem 0 !important;
                border: none !important;
                font-size: 0.85rem;
            }

            .activity-row td::before {
                content: attr(data-label);
                font-weight: 800;
                color: var(--text-muted);
                font-size: 0.7rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .activity-row td:first-child {
                border-bottom: 1px dashed var(--border-color) !important;
                margin-bottom: 0.5rem;
                padding-bottom: 0.75rem !important;
            }

            .activity-row td:first-child::before {
                content: 'Transaction Date';
            }

            .activity-row td:nth-child(2) {
                font-size: 1.1rem;
                font-weight: 900;
                color: var(--primary);
                display: block;
                text-align: left;
            }

            .activity-row td:nth-child(2)::before {
                display: block;
                margin-bottom: 0.2rem;
            }
        }
    </style>

    <table class="activity-table">
        <thead>
            <tr>
                <th>Entry Date</th>
                <th>Arrival Date</th>
                <th>Product</th>
                <th>Category</th>
                <th>Supplier</th>
                <th>Donor Name</th>
                <th>S. Status</th>
                <th>Qty Received</th>
                <th>Qty (Var)</th>
                <th>Type</th>
                <th>Stock Bal.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentTransactions as $transaction)
            <tr class="activity-row">
                <td data-label="Entry Date">{{ \Carbon\Carbon::parse($transaction->entry_date)->format('M d, H:i') }}</td>
                <td data-label="Arrival Date" style="color: var(--primary); font-weight: 700;">{{ $transaction->arrival_date ? \Carbon\Carbon::parse($transaction->arrival_date)->format('M d, Y') : '-' }}</td>
                <td data-label="Product">{{ $transaction->description }} <span style="font-size: 0.65rem; color: var(--primary); font-weight: 800;">({{ $transaction->unit ?? 'Units' }})</span></td>
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
                $supStatusDisplay = 'N/A';

                if ($acqType === 'Donor') {
                $supStatusDisplay = 'Donor';
                $supColor = '#8b5cf6';
                } else {
                if (preg_match('/\[(.*)\]/', $rawSup, $matches)) {
                $supStatusDisplay = $matches[1];
                }
                $supColor = '#94a3b8';
                if ($supStatusDisplay === 'Full Delivery') $supColor = '#10b981';
                elseif ($supStatusDisplay === 'Partial Delivery') $supColor = '#ef4444';
                }
                @endphp
                <td data-label="Supplier" style="color: var(--text-main);">{{ $cleanSupDisplay ?: '-' }}</td>
                <td data-label="Donor Name" style="color: var(--text-main); font-weight: {{ $acqType === 'Donor' ? '800' : '400' }};">{{ $dName }}</td>
                <td data-label="Supp. Status">
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
                <td colspan="9" style="padding: 5rem 2rem; text-align: center;">
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
            fontFamily: 'Plus Jakarta Sans, sans-serif',
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

    $('#btnDaily').on('click', function() {
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
        $(this).css({
            background: 'var(--primary)',
            color: 'white'
        });
        $('#btnWeekly, #btnMonthly').css({
            background: 'var(--bg-main)',
            color: 'var(--text-main)'
        });
    });

    $('#btnWeekly').on('click', function() {
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
        $(this).css({
            background: 'var(--primary)',
            color: 'white'
        });
        $('#btnDaily, #btnMonthly').css({
            background: 'var(--bg-main)',
            color: 'var(--text-main)'
        });
    });

    $('#btnMonthly').on('click', function() {
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
        $(this).css({
            background: 'var(--primary)',
            color: 'white'
        });
        $('#btnDaily, #btnWeekly').css({
            background: 'var(--bg-main)',
            color: 'var(--text-main)'
        });
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
            fontFamily: 'Plus Jakarta Sans',
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
                            label: @json($isEmptyDist) ? 'Awaiting Records' : 'Total Units',
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
            const modal = $('#newEntryModal');
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
                $('#newEntryForm')[0].reset();

                $('#entryDate').val(formattedDate);
                $('#arrivalDate').val(new Date().toISOString().split('T')[0]);

                $('#ledgeSelect').val('').trigger('change');
                $('#itemsContainer').empty();
                $('#itemDetails').hide();
                $('#qtyControl, #supplierControl, #dateControl').hide().css('opacity', 0);
                $('#modalFooter').hide();
            }
        };

        window.closeModal = function() {
            const modal = $('#newEntryModal');
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
    $(document).ready(function() {
        const modal = $('#newEntryModal');
        const openBtn = $('#openNewEntry');
        const closeBtn = $('#closeModal');
        const ledgeSelect = $('#ledgeSelect');
        const itemDetails = $('#itemDetails');

        // Database Items from Backend
        const existingDBItems = JSON.parse(document.getElementById('inventory-data').textContent || '[]');

        // Initialize Select2
        ledgeSelect.select2({
            placeholder: 'Search and Select Category',
            width: '100%',
            dropdownParent: modal
        });

        // Open Modal Listener (jQuery way)
        openBtn.on('click', window.openModal);

        // Final Submit Logic
        $('#newEntryForm').on('submit', function(e) {
            e.preventDefault();

            const btn = $(this).find('button[type="submit"]');
            const originalHtml = btn.html();

            // Gather Items
            const items = [];
            $('.item-entry-row').each(function() {
                items.push({
                    description: $(this).find('.item-select-dynamic').val(),
                    unit: $(this).find('.row-unit').val(),
                    stock_balance: $(this).find('.row-stock-balance').val(),
                    qty: $(this).find('.row-qty').val(),
                    variance: $(this).find('.row-variance').val() || '0',
                    remarks: $(this).find('.row-remarks').val()
                });
            });

            const supplierStatus = $('#supplierStatusSelect').val(); // Can be Full/Partial or Donor
            const acquisitionType = supplierStatus === 'Donor' ? 'Donor' : 'Supplier';

            // Capture both independently. They are different things!
            const donorName = $('#donorNameInput').val() ? $('#donorNameInput').val().trim() : null;
            const baseSupplier = $('#supplierNameSelect').val() || '';

            // Still append status to supplier name for legacy compatibility if it's not Donor status
            const supplierName = (supplierStatus && supplierStatus !== 'Donor') ?
                `${baseSupplier} [${supplierStatus}]` :
                baseSupplier;

            const payload = {
                _token: '{{ csrf_token() }}',
                ledge_category: ledgeSelect.val(),
                supplier_name: supplierName || null,
                donor_name: donorName,
                acquisition_type: acquisitionType,
                entry_date: $('#entryDate').val(),
                arrival_date: $('#arrivalDate').val(),
                items: items
            };

            // Loading State
            btn.html('<i class="animate-spin" data-lucide="loader-2"></i> Saving...').prop('disabled', true);
            lucide.createIcons();

            $.ajax({
                url: '{{ route("inventory.store") }}',
                method: 'POST',
                data: payload,
                success: function(response) {
                    if (response.success) {
                        showToast('Success', 'Inventory records saved successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Something went wrong';
                    showToast('Action Failed', errorMsg, 'error');
                    btn.html(originalHtml).prop('disabled', false);
                    lucide.createIcons();
                }
            });
        });
        closeBtn.on('click', window.closeModal);
        $(window).on('click', (e) => {
            if (e.target == modal[0]) closeModal();
        });

        // Handle Ledge Selection
        ledgeSelect.on('change', function() {
            const selectedLedge = $(this).val();
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
                    const filtered = existingDBItems.filter(item => item.ledge_category === selectedLedge);

                    let optionsHtml = '<option value=""></option>';
                    filtered.forEach(item => {
                        optionsHtml += `<option value="${item.description}">${item.description}</option>`;
                    });

                    $select.html(optionsHtml);
                    if (currentVal) {
                        // If the previous value exists in the new list, keep it, otherwise clear
                        if (filtered.some(i => i.description === currentVal)) {
                            $select.val(currentVal).trigger('change.select2');
                        } else {
                            $select.val(null).trigger('change.select2');
                        }
                    }
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
        });

        function renderItemRows(count, append = false) {
            const container = $('#itemsContainer');
            if (!append) container.empty();

            const selectedLedge = $('#ledgeSelect').val();
            const filteredItems = existingDBItems.filter(item => item.ledge_category === selectedLedge);

            for (let i = 0; i < count; i++) {
                const currentRows = container.children('.item-entry-row').length;
                const itemIdx = currentRows + 1;
                const rowHtml = `
                    <div class="item-entry-row" style="margin-bottom: 2rem; padding: 2rem 1.5rem 1.5rem 1.5rem; border: 1px solid var(--border-color); border-radius: 16px; background: var(--bg-card); position: relative;">
                        <div class="row-badge" style="position: absolute; top: -12px; left: 1rem; background: var(--primary); color: white; padding: 2px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 800;">ITEM #${itemIdx}</div>

                        <button type="button" class="remove-row-btn" style="position: absolute; top: 1.25rem; right: 1.25rem; background: rgba(239, 68, 68, 0.1); color: var(--danger); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition);">
                            <i data-lucide="trash-2" style="width: 16px;"></i>
                        </button>

                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label>Item Description (Search & Select)</label>
                                <select class="item-select-dynamic" style="width: 100%;">
                                    <option value=""></option>
                                    ${filteredItems.map(item => `<option value="${item.description}">${item.description}</option>`).join('')}
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Units</label>
                                <select class="row-unit" style="width: 100%;">
                                    <option value="Piece(s)">Piece(s)</option>
                                    <option value="Pack">Pack</option>
                                    <option value="Boxes">Boxes</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="lbl-stock-balance">Stock Balance</label>
                                <input type="number" class="row-stock-balance" placeholder="0">
                            </div>
                            <div class="form-group">
                                <label class="lbl-received-qty">Received Qty</label>
                                <input type="number" class="row-qty" placeholder="0" style="border-color: var(--primary-light);">
                            </div>
                            <div class="form-group">
                                <label>Variance Status</label>
                                <input type="text" class="row-variance" value="0" readonly style="color: var(--danger); font-weight: 700; background: var(--bg-main);">
                            </div>

                            <div class="form-group full-width">
                                <label>Situation Remarks / Notes</label>
                                <input type="text" class="row-remarks" placeholder="Briefly describe the current situation or condition...">
                            </div>
                        </div>
                    </div>
                `;
                const $row = $(rowHtml);
                container.append($row);

                // Initialize Select2 to allow DB items and new items
                $row.find('.item-select-dynamic').select2({
                    placeholder: "Search, select, or type new item...",
                    width: '100%',
                    tags: true, // Allow new tags
                    dropdownParent: $('#newEntryModal')
                });

                // Initialize Units Select2
                $row.find('.row-unit').select2({
                    placeholder: "Select or type unit...",
                    width: '100%',
                    tags: true, // Allow new units
                    dropdownParent: $('#newEntryModal')
                });

                // Handle Item Selection to show previous data as placeholders
                $row.on('change', '.item-select-dynamic', function() {
                    const selectedDesc = $(this).val();
                    const prevData = existingDBItems.find(item => item.description === selectedDesc);

                    const stockInput = $row.find('.row-stock-balance');
                    const qtyInput = $row.find('.row-qty');
                    const varianceInput = $row.find('.row-variance');

                    if (prevData) {
                        stockInput.attr('placeholder', `Prev: ${prevData.stock_balance}`);
                        qtyInput.attr('placeholder', `Prev: ${prevData.qty}`);
                        varianceInput.attr('placeholder', `Prev: ${prevData.variance}`);

                        // Visual cue for existing item
                        if (!$row.find('.existing-indicator').length) {
                            $row.find('.row-badge').append(' <span class="existing-indicator" style="font-size: 0.6rem; opacity: 0.8;">(Restocking)</span>');
                        }
                    } else {
                        stockInput.attr('placeholder', '0');
                        qtyInput.attr('placeholder', '0');
                        varianceInput.attr('placeholder', '0');
                        $row.find('.existing-indicator').remove();
                    }
                });

                // Auto-Calculation Logic: Variance = Stock Balance - Received Qty
                $row.on('input', '.row-stock-balance, .row-qty', function() {
                    const status = $('#supplierStatusSelect').val();
                    const stockVal = parseFloat($row.find('.row-stock-balance').val()) || 0;
                    
                    // Auto-sync qty if not a partial delivery
                    if (status !== 'Partial Delivery' && $(this).hasClass('row-stock-balance')) {
                        $row.find('.row-qty').val($row.find('.row-stock-balance').val());
                    }

                    const qtyVal = parseFloat($row.find('.row-qty').val()) || 0;
                    const varianceInput = $row.find('.row-variance');

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
                    $row.find('.lbl-stock-balance').text('Physically Received');
                    $row.find('.lbl-received-qty').text('Expected / Invoice Qty');
                    $row.find('.row-qty').css('border-color', '#f59e0b');
                } else {
                    $row.find('.lbl-stock-balance').text('Stock Balance');
                    $row.find('.lbl-received-qty').text('Received Qty');
                    $row.find('.row-qty').css('border-color', 'var(--primary-light)');
                    // Auto-hide the received qty if it's full delivery to reduce confusion? 
                    // No, keeping it visible but read-only or auto-synced is better.
                    if (initStatus === 'Full Delivery' || initStatus === 'Donor') {
                        $row.find('.row-qty').prop('readonly', true).css('background', 'var(--bg-main)');
                    }
                }
            }
            lucide.createIcons(); // Re-init icons
        }

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

        // Toggle Donor Name Input and Update Labels
        $('#supplierStatusSelect').on('change', function() {
            const status = $(this).val();
            
            if (status === 'Donor') {
                $('#donorNameWrapper').slideDown(300);
            } else {
                $('#donorNameWrapper').slideUp(300);
            }

            // Update Labels & Fields for Partial Delivery UI
            if (status === 'Partial Delivery') {
                $('.item-entry-row').each(function() {
                    $(this).find('.lbl-stock-balance').text('Physically Received');
                    $(this).find('.lbl-received-qty').text('Expected / Invoice Qty');
                    $(this).find('.row-qty').css({'border-color': '#f59e0b', 'background': 'var(--bg-card)'}).prop('readonly', false);
                });
            } else {
                $('.item-entry-row').each(function() {
                    $(this).find('.lbl-stock-balance').text('Stock Balance');
                    $(this).find('.lbl-received-qty').text('Received Qty');
                    $(this).find('.row-qty').css({'border-color': 'var(--primary-light)', 'background': 'var(--bg-main)'}).prop('readonly', true);
                    
                    // Auto-sync existing values if changed back to full
                    const stockVal = $(this).find('.row-stock-balance').val();
                    if (stockVal) {
                        $(this).find('.row-qty').val(stockVal).trigger('input');
                    }
                });
            }
        });

        // Initialize Supplier Name Search/Type
        $('#supplierNameSelect').select2({
            placeholder: "Search or type new supplier...",
            width: '100%',
            tags: true,
            dropdownParent: $('#newEntryModal')
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
                    $(this).find('.row-badge').text('ITEM #' + (index + 1));
                });
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

                        // Set Supplier (clean name)
                        const rawSupplier = batch.supplier_name;
                        const cleanSupplier = rawSupplier.replace(/\s\[.*\]$/, '');

                        // We need to wait for Select2 to be ready or just set values if they exist
                        setTimeout(() => {
                            $('#supplierNameSelect').val(cleanSupplier).trigger('change');
                            $('#supplierStatusSelect').val('Full Delivery').trigger('change');

                            // Render Rows for items
                            if (batch.items && batch.items.length > 0) {
                                $('#multiQty').val(batch.items.length);
                                renderItemRows(batch.items.length);

                                // Fill row data
                                $('.item-entry-row').each(function(index) {
                                    const item = batch.items[index];
                                    const $row = $(this);

                                    $row.find('.item-select-dynamic').val(item.description).trigger('change');

                                    $row.find('.row-stock-balance').focus();
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

        <form id="newEntryForm">
            <!-- Sticky Batch Controls -->
            <div class="modal-controls-sticky">
                <div class="form-grid">
                    <div id="ledgeContainer" class="form-group full-width">
                        <label>Category Section (Search & Select)</label>
                        <select id="ledgeSelect" class="select2-input" style="width: 100%;">
                            <option value=""></option>
                            <option value="A">Category A - Stationary</option>
                            <option value="B">Category B - Cleaning</option>
                            <option value="C">Category C - IT and Accessories</option>
                            <option value="D">Category D - Transport Items</option>
                            <option value="E">Category E - Safety and Hygiene</option>
                            <option value="G">Category G - Test Kits and Pharmaceutical Products</option>
                            <option value="J">Category J - Furniture, Fixture, Fittings, Tools and Equipment</option>
                        </select>
                    </div>
                    <div id="qtyControl" class="form-group" style="display: none; opacity: 0;">
                        <label>Items to record?</label>
                        <input type="number" id="multiQty" min="1" value="1" placeholder="Qty">
                    </div>
                    <div id="supplierControl" class="form-group full-width" style="display: none; opacity: 0; margin-top: 0.75rem;">
                        <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label>Supplier Name (Search or Type)</label>
                                <select id="supplierNameSelect" style="width: 100%;">
                                    <option value=""></option>
                                    @foreach($allSuppliers as $supplier)
                                    <option value="{{ $supplier }}">{{ $supplier }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label>Delivery Status</label>
                                <select id="supplierStatusSelect" style="width: 100%;">
                                    <option value="">Select Status</option>
                                    <option value="Donor" data-icon="heart" data-color="#8b5cf6">Donation</option>
                                    <option value="Full Delivery" data-icon="check-circle" data-color="#10b981">Full Delivery</option>
                                    <option value="Partial Delivery" data-icon="alert-circle" data-color="#f59e0b">Partial Delivery</option>
                                </select>
                            </div>
                        </div>
                        <div id="donorNameWrapper" style="display: none; margin-top: 1rem;">
                            <label>Donor's Name *</label>
                            <input type="text" id="donorNameInput" placeholder="Enter the donor's full name..." style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit;">
                        </div>
                    </div>

                    <div id="dateControl" class="form-group full-width" style="display: none; opacity: 0; margin-top: 1rem;">
                        <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label>Arrival Date (Manual)</label>
                                <input type="date" id="arrivalDate" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit;">
                            </div>
                            <div class="form-group">
                                <label>Entry Date (Automatic)</label>
                                <input type="text" id="entryDate" readonly style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; cursor: not-allowed; opacity: 1;">
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
                        Submit All Records
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