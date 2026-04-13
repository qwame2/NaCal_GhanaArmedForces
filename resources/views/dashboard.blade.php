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
                box-shadow: 0 10px 25px rgba(0,0,0,0.04) !important;
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

            .modal-header-sticky .modal-header > div:first-child {
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
                background: rgba(0,0,0,0.05) !important;
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
                $ledgeAlertMsg = "Ledge {$lowStockLedges[0]['code']} ({$lowStockLedges[0]['name']}) is at {$lowStockLedges[0]['percentage']}%";
            } elseif (count($lowStockLedges) > 1) {
                $ledgeAlertMsg = count($lowStockLedges) . " Ledges are below 50%";
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
                    {{ $isLedgeCritical ? $ledgeAlertMsg : 'No critical ledges detected' }}
                </div>
            </div>

            <style>
                .no-scrollbar::-webkit-scrollbar { display: none; }
                .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
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
                            $statusLabel = $isCritical ? 'CRITICAL DEPLETION' : 'WATCHLIST';
                            $statusColor = $isCritical ? '#ef4444' : '#f59e0b';
                        @endphp
                        <div class="popover-item" style="display: block; padding: 0.75rem 0.5rem; border-bottom: 1px solid rgba(0,0,0,0.03);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                                <div>
                                    <div style="font-weight: 800; color: var(--text-main); font-size: 0.9rem; line-height: 1.2;">Ledge {{ $l['code'] }}</div>
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
            <div id="enhancedDonutChart" style="margin-top: -1.5rem; margin-inline: -1rem;"></div>
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
                .activity-table thead { display: none; }
                .activity-table, .activity-table tbody, .activity-table tr, .activity-table td { display: block; width: 100%; }
                .activity-row {
                    margin-bottom: 1rem;
                    border-radius: 12px;
                    background: var(--bg-main) !important;
                    padding: 1rem;
                    border: 1px solid var(--border-color);
                    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
                }
                .activity-row:hover { transform: translateY(-2px); }
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
                .activity-row td:first-child::before { content: 'Transaction Date'; }
                .activity-row td:nth-child(2) { font-size: 1.1rem; font-weight: 900; color: var(--primary); display: block; text-align: left; }
                .activity-row td:nth-child(2)::before { display: block; margin-bottom: 0.2rem; }
            }
        </style>

        <table class="activity-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Donor Name</th>
                    <th>S. Status</th>
                    <th>Avail. Qty</th>
                    <th>Qty (Var)</th>
                    <th>Type</th>
                    <th>Bal.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions as $transaction)
                <tr class="activity-row">
                    <td data-label="Date">{{ \Carbon\Carbon::parse($transaction->entry_date)->format('M d, H:i') }}</td>
                    <td data-label="Product">{{ $transaction->description }}</td>
                    <td data-label="Category"><span style="font-size: 0.75rem; background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 600;">{{ $ledgeMap[$transaction->ledge_category] ?? "Ledge " . $transaction->ledge_category }}</span></td>
                    @php
                        $rawSup = $transaction->supplier_name;
                        $cleanSup = preg_replace('/\s\[.*\]$/', '', $rawSup);
                        $supStatus = 'N/A';
                        if (preg_match('/\[(.*)\]/', $rawSup, $matches)) {
                            $supStatus = $matches[1];
                        }
                        $supColor = '#94a3b8';
                        if ($supStatus === 'Donor Action') $supColor = '#3b82f6'; 
                        elseif ($supStatus === 'Full Delivery') $supColor = '#10b981';
                        elseif ($supStatus === 'Partial Delivery') $supColor = '#ef4444';
                    @endphp
                    <td data-label="Supplier" style="color: {{ $supStatus === 'Donor Action' ? 'var(--text-muted)' : 'inherit' }}">{{ $supStatus === 'Donor Action' ? '-' : $cleanSup }}</td>
                    <td data-label="Donor Name" style="color: var(--text-main); font-weight: {{ $supStatus === 'Donor Action' ? '700' : '400' }};">{{ $supStatus === 'Donor Action' ? $cleanSup : '-' }}</td>
                    <td data-label="Supp. Status">
                        <span style="font-size: 0.65rem; font-weight: 800; color: white; background: {{ $supColor }}; padding: 0.25rem 0.6rem; border-radius: 6px; text-transform: uppercase;">
                            {{ $supStatus }}
                        </span>
                    </td>
                    <td data-label="Avail. Qty" style="font-weight: 600;">{{ $transaction->qty ?? '0' }}</td>
                    <td data-label="Qty/Variance" style="font-weight: 700; color: {{ is_numeric($transaction->variance) && (float)$transaction->variance > 0 ? 'var(--secondary)' : (is_numeric($transaction->variance) && (float)$transaction->variance < 0 ? 'var(--danger)' : 'inherit') }}">
                        {{ is_numeric($transaction->variance) && (float)$transaction->variance > 0 ? '+' : '' }}{{ $transaction->variance }}
                    </td>
                    <td data-label="Transaction Type">
                        @php
                            $v = (float)$transaction->variance;
                            $stClass = 'status-success';
                            $stText = 'Received';
                            if ($v < 0) {
                                $stClass = 'status-warning';
                                $stText = 'Issued';
                            } elseif ($transaction->variance == 'Expired') {
                                $stClass = 'status-danger';
                                $stText = 'Expired';
                            }
                        @endphp
                        <span class="status-badge {{ $stClass }}">{{ $stText }}</span>
                    </td>
                    <td data-label="Balance" style="font-weight: 700;">{{ $transaction->stock_balance }}</td>
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
<script id="inventory-data" type="application/json">
    {!! json_encode($existingItems ?? []) !!}
</script>
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

    var areaChart = new ApexCharts(document.querySelector("#advancedAreaChart"), areaOptions);
    areaChart.render();

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
            xaxis: { categories: dailyData.labels }
        });
        areaChart.updateSeries([
            { name: 'Received', data: dailyData.received },
            { name: 'Net Variance', data: dailyData.variance }
        ]);
        $(this).css({background: 'var(--primary)', color: 'white'});
        $('#btnWeekly, #btnMonthly').css({background: 'var(--bg-main)', color: 'var(--text-main)'});
    });

    $('#btnWeekly').on('click', function() {
        areaChart.updateOptions({
            xaxis: { categories: weeklyData.labels }
        });
        areaChart.updateSeries([
            { name: 'Received', data: weeklyData.received },
            { name: 'Net Variance', data: weeklyData.variance }
        ]);
        $(this).css({background: 'var(--primary)', color: 'white'});
        $('#btnDaily, #btnMonthly').css({background: 'var(--bg-main)', color: 'var(--text-main)'});
    });

    $('#btnMonthly').on('click', function() {
        areaChart.updateOptions({
            xaxis: { categories: monthlyData.labels }
        });
        areaChart.updateSeries([
            { name: 'Received', data: monthlyData.received },
            { name: 'Net Variance', data: monthlyData.variance }
        ]);
        $(this).css({background: 'var(--primary)', color: 'white'});
        $('#btnDaily, #btnWeekly').css({background: 'var(--bg-main)', color: 'var(--text-main)'});
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
        colors: @json($isEmptyDist) ? ['var(--border-color)'] : ['#6366f1', '#10b981', '#f59e0b', '#db2777', '#8b5cf6', '#06b6d4', '#ec4899'],
        dataLabels: {
            enabled: false
        },
        legend: {
            position: 'bottom',
            fontFamily: 'Plus Jakarta Sans',
            fontSize: '14px',
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
                    return @json($isEmptyDist) ? "0 units" : val + "%"
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
                    size: '60%',
                    borderRadius: 10,
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: @json($isEmptyDist) ? 'Awaiting Records' : 'Total Percentage',
                            fontSize: '14px',
                            fontWeight: 600,
                            color: '#64748b',
                            formatter: function(w) {
                                return @json($isEmptyDist) ? "0" : "100%";
                            }
                        },
                        value: {
                            show: true,
                            fontSize: '32px',
                            fontWeight: 800,
                            color: 'var(--text-main)',
                            formatter: function(val) {
                                return @json($isEmptyDist) ? '0%' : val + '%'
                            }
                        }
                    }
                }
            }
        },
        states: {
            hover: {
                filter: {
                    type: 'darken',
                    value: 0.9
                }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    height: 300
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    var donutChart = new ApexCharts(document.querySelector("#enhancedDonutChart"), donutOptions);
    donutChart.render();

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
            placeholder: 'Search and Select Ledge Category',
            width: '100%',
            dropdownParent: modal
        });

        // Open Modal
        openBtn.on('click', function() {
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

            $('#entryDate').val(formattedDate);
        });

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
                    ledge_balance: $(this).find('.row-ledge-balance').val() || '0',
                    stock_balance: $(this).find('.row-stock-balance').val() || '0',
                    qty: $(this).find('.row-qty').val() || '',
                    variance: $(this).find('.row-variance').val() || '0',
                    remarks: $(this).find('.row-remarks').val() || ''
                });
            });

            let finalSupplierName = $('#supplierNameSelect').val() || '';
            const supplierStatus = $('#supplierStatusSelect').val();

            if (supplierStatus === 'Donor' && $('#donorNameInput').val().trim() !== '') {
                finalSupplierName = $('#donorNameInput').val().trim();
            }

            const mergedSupplier = supplierStatus ? `${finalSupplierName} [${supplierStatus}]` : finalSupplierName;

            const payload = {
                _token: '{{ csrf_token() }}',
                ledge_category: ledgeSelect.val(),
                supplier_name: mergedSupplier,
                entry_date: $('#entryDate').val(),
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
        // Close Modal with Animation
        const closeModal = () => {
            const content = modal.find('.modal-content');
            content.addClass('slide-out');
            setTimeout(() => {
                modal.hide();
                content.removeClass('slide-out');
            }, 600);
        };

        closeBtn.on('click', closeModal);
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
                                <label>Ledge Balance</label>
                                <input type="text" class="row-ledge-balance" placeholder="0">
                            </div>
                            <div class="form-group">
                                <label>Stock Balance</label>
                                <input type="text" class="row-stock-balance" placeholder="0">
                            </div>
                            <div class="form-group">
                                <label>Available Qty</label>
                                <input type="text" class="row-qty" placeholder="0" style="border-color: var(--primary-light);">
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

                // Auto-Calculation Logic
                $row.on('input', '.row-ledge-balance, .row-stock-balance', function() {
                    const ledgeVal = parseFloat($row.find('.row-ledge-balance').val()) || 0;
                    const stockVal = parseFloat($row.find('.row-stock-balance').val()) || 0;
                    const varianceInput = $row.find('.row-variance');

                    // "Expired" special logic: Stock is 0 AND Ledge is 1 or more
                    if (stockVal === 0 && ledgeVal >= 1) {
                        varianceInput.val('Expired').css('color', '#ef4444'); // Red for Expired
                    } else {
                        const result = stockVal - ledgeVal; // Balanced = Stock - Ledge
                        varianceInput.val(result);

                        if (result > 0) {
                            varianceInput.css('color', '#10b981'); // Green for positive (Surplus)
                        } else if (result < 0) {
                            varianceInput.css('color', '#ef4444'); // Red for negative (Shortage)
                        } else {
                            varianceInput.css('color', '#3b82f6'); // Blue for zero (Balanced)
                        }
                    }
                });
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

        // Toggle Donor Name Input
        $('#supplierStatusSelect').on('change', function() {
            if ($(this).val() === 'Donor') {
                $('#donorNameWrapper').slideDown(300);
            } else {
                $('#donorNameWrapper').slideUp(300);
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
                                    $row.find('.row-ledge-balance').val(item.stock_balance).trigger('input');
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
                        <label>Ledge Section (Search & Select)</label>
                        <select id="ledgeSelect" class="select2-input" style="width: 100%;">
                            <option value=""></option>
                            <option value="A">Ledge A - Stationary</option>
                            <option value="B">Ledge B - Cleaning</option>
                            <option value="C">Ledge C - IT and Accessories</option>
                            <option value="D">Ledge D - Transport Items</option>
                            <option value="E">Ledge E - Safety and Hygiene</option>
                            <option value="G">Ledge G - TEST KITS AND PHARMACEUTICAL PRODUCTS</option>
                            <option value="J">Ledge J - FURNITURE, FIXTURE, FITTINGS, TOOLS AND EQUIPMENT</option>
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
                                    <option value="Donor" data-icon="heart" data-color="#8b5cf6">Donor Action</option>
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
                </div>
            </div>

            <!-- Scrollable Body -->
            <div class="modal-body-scroll">
                <div id="itemDetails" style="display: none;">
                    <div id="itemsContainer">
                        <!-- Item Rows will be injected here -->
                    </div>

                    <div class="form-group full-width" style="margin-top: 2rem;">
                        <label>Common Entry Date & Time (Automatic)</label>
                        <input type="text" id="entryDate" readonly style="background: var(--bg-main); cursor: not-allowed; opacity: 0.8;">
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

    // Existing scripts continue below or were here...
</script>
@endpush