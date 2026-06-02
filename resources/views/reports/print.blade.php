<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Head of Stores | Strategic Registry</title>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            background: white;
            font-size: 12px;
            margin: 0;
            padding: 20px 30px;
            line-height: 1.4;
        }

        /* ── Header ── */
        .print-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 4px solid #1e3a8a;
            padding-bottom: 15px;
        }
        .print-header img {
            width: 80px;
            height: auto;
            margin-bottom: 8px;
        }
        .print-header h1 {
            font-size: 20pt;
            font-family: 'Times New Roman', serif;
            font-weight: 800;
            margin: 0;
            letter-spacing: 1.5px;
            color: #1e3a8a;
        }
        .print-header h3 {
            font-size: 12pt;
            font-family: 'Arial', sans-serif;
            font-weight: bold;
            margin: 5px 0 0;
            color: #3b82f6;
            text-transform: uppercase;
        }
        .print-header p {
            font-size: 10pt;
            font-family: 'Times New Roman', serif;
            margin: 8px 0 0;
            color: #475569;
            font-weight: bold;
            text-transform: uppercase;
        }
        .print-meta-bar {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 12px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #334155;
        }
        .print-meta-bar strong {
            color: #0f172a;
        }

        /* ── Stats Summary Bar ── */
        .stats-summary-bar {
            display: flex;
            justify-content: space-around;
            background: #f8fafc;
            border-top: 3.5px solid #1e3a8a;
            border-bottom: 3.5px solid #1e3a8a;
            padding: 12px 0;
            margin-bottom: 20px;
        }
        .stat-col {
            text-align: center;
            flex: 1;
        }
        .stat-col-label {
            font-size: 11px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .stat-col-value {
            font-size: 20px;
            font-weight: 900;
            color: #1e3a8a;
            margin-top: 2px;
        }
        .stat-col-value span {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
        }

        /* ── Visualization ── */
        .visualizations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .visualization-container {
            page-break-inside: avoid;
        }
        .visualization-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }
        .vis-icon-wrap {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .visualization-header h2 {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }
        .vis-sub-label {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.05em;
            margin-left: 32px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .green-label {
            color: #10b981;
        }
        .orange-label {
            color: #f59e0b;
        }

        /* ── Tables ── */
        .table-card {
            border: 1.5px solid #cbd5e1;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 20px;
            margin-bottom: 25px;
            page-break-inside: auto;
        }
        .table-card-header {
            background: #1e3b8b;
            padding: 8px 12px;
            color: white;
        }
        .table-title {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.02em;
        }
        .table-subtitle {
            font-size: 11px;
            color: #93c5fd;
            margin-top: 1px;
        }
        .unified-ledger-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .unified-ledger-table th {
            background: #e0e7ff;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            color: #1e3a8a;
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            text-align: left;
        }
        .unified-ledger-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            color: #0f172a;
            vertical-align: middle;
        }
        .unified-ledger-table tr:nth-child(even) td {
            background: #fdfefe;
        }
        .unified-ledger-table tr:nth-child(odd) td {
            background: #fafafd;
        }
        
        /* Alignments */
        .unified-ledger-table th:nth-child(3), 
        .unified-ledger-table td:nth-child(3), 
        .unified-ledger-table th:nth-child(6), 
        .unified-ledger-table td:nth-child(6) {
            text-align: center;
        }
        .unified-ledger-table th:nth-child(9), 
        .unified-ledger-table td:nth-child(9), 
        .unified-ledger-table th:nth-child(10), 
        .unified-ledger-table td:nth-child(10), 
        .unified-ledger-table th:nth-child(11), 
        .unified-ledger-table td:nth-child(11) {
            text-align: right;
        }

        /* Pills & Badges */
        .badge-received {
            border: 1px solid #10b981;
            color: #047857;
            background: #f0fdf4;
            border-radius: 99px;
            padding: 2px 6px;
            font-weight: 800;
            font-size: 10px;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
        .badge-issued {
            border: 1px solid #f59e0b;
            color: #b45309;
            background: #fffbeb;
            border-radius: 99px;
            padding: 2px 6px;
            font-weight: 800;
            font-size: 10px;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
        .badge-category {
            border: 1px solid #3b82f6;
            color: #1d4ed8;
            background: #eff6ff;
            border-radius: 4px;
            padding: 2px 6px;
            font-weight: 800;
            font-size: 10px;
            text-transform: uppercase;
        }
        .status-pill-permanent {
            border: 1px solid #1e3a8a;
            color: #1e3a8a;
            background: #eff6ff;
            border-radius: 4px;
            padding: 2px 6px;
            font-weight: 800;
            font-size: 10px;
            text-transform: uppercase;
        }
        .status-pill-temporary {
            border: 1px solid #b45309;
            color: #b45309;
            background: #fffbeb;
            border-radius: 4px;
            padding: 2px 6px;
            font-weight: 800;
            font-size: 10px;
            text-transform: uppercase;
        }

        /* ── Signature Block ── */
        .signature-block {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .sig-col { text-align: center; }
        .sig-line {
            border-top: 1.5px solid #0f172a;
            margin-top: 35px;
            padding-top: 5px;
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            color: #334155;
        }
        .sig-subtitle { font-size: 7.5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; margin-top: 2px; }

        /* ── Page Break ── */
        .page-break {
            page-break-before: always;
            break-before: page;
            height: 0;
            margin: 0;
            padding: 0;
        }

        /* ── Floating Screen Button ── */
        .no-print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 10px 20px;
            background: #1e3a8a;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm 12mm;
            }

            html, body {
                height: auto !important;
                overflow: visible !important;
                padding: 0 !important;
                margin: 0 !important;
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print-btn {
                display: none !important;
            }

            .visualizations-grid {
                page-break-inside: avoid;
            }
            .visualization-container {
                page-break-inside: avoid;
            }

            .table-card {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            .signature-block {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    {{-- Floating Screen-Only Print Button --}}
    <button class="no-print-btn" onclick="window.print()">🖨️ Print Report</button>

    {{-- Header --}}
    <div class="print-header">
        <img src="{{ asset('img/NACOC1.png') }}" alt="Logo">
        <h1>{{ \App\Models\Setting::get('organization_name', 'NACOC') }}</h1>
        <h3>Official Inventory Operations Report</h3>
        <p>{{ $dateLabel }}</p>

        <div class="print-meta-bar">
            <div><strong>REF NO:</strong> NACOC/INV/{{ date('Y/m') }}/{{ str_pad(rand(1,999), 3, '0', STR_PAD_LEFT) }}</div>
            <div><strong>DATE PRINTED:</strong> {{ date('d Jun Y') }}</div>
            <div><strong>STATUS:</strong> <span style="color: #dc2626; font-weight: bold;">CERTIFIED CLASSIFIED</span></div>
        </div>
    </div>

    {{-- Stats Summary Bar --}}
    <div class="stats-summary-bar">
        <div class="stat-col">
            <div class="stat-col-label">Total Received</div>
            <div class="stat-col-value"><strong>{{ number_format((float)$totalReceivedQty) }}</strong> <span>Units</span></div>
        </div>
        <div class="stat-col">
            <div class="stat-col-label">Total Issued</div>
            <div class="stat-col-value"><strong>{{ number_format((float)$totalIssuedQty) }}</strong> <span>Units</span></div>
        </div>
        <div class="stat-col">
            <div class="stat-col-label">Net Movement</div>
            <div class="stat-col-value"><strong>{{ number_format(max(0, (float)$totalReceivedQty - (float)$totalIssuedQty)) }}</strong> <span>Units</span></div>
        </div>
    </div>

    {{-- Visualizations Grid --}}
    @if(($totalReceivedQty > 0 && $receivedDistribution->count() > 0) || ($totalIssuedQty > 0 && $issuedDistribution->count() > 0))
    <div class="visualizations-grid">
        {{-- Stock Receipts Visualization --}}
        @if($totalReceivedQty > 0 && $receivedDistribution->count() > 0)
        <div class="visualization-container">
            <div class="visualization-header">
                <span class="vis-icon-wrap" style="background: #10b981;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                </span>
                <h2>Stock Receipts Visualization</h2>
            </div>
            <div class="vis-sub-label green-label">● STOCK RECEIPTS — TOP {{ $receivedDistribution->count() }} ITEMS</div>
            <div id="received-bar-chart" style="margin-left: 20px; width: calc(100% - 20px);"></div>
        </div>
        @endif

        {{-- Issuance Visualization --}}
        @if($totalIssuedQty > 0 && $issuedDistribution->count() > 0)
        <div class="visualization-container">
            <div class="visualization-header">
                <span class="vis-icon-wrap" style="background: #f59e0b;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                </span>
                <h2>Issuance Visualization</h2>
            </div>
            <div class="vis-sub-label orange-label">● ISSUANCE — TOP {{ $issuedDistribution->count() }} ITEMS</div>
            <div id="issued-bar-chart" style="margin-left: 20px; width: calc(100% - 20px);"></div>
        </div>
        @endif
    </div>
    @endif

    {{-- Unified Ledger --}}
    @php
        // Merge both collections, normalise fields, then sort by date desc
        $allTransactions = $recentReceivals->map(function($r) use ($ledgeMap) {
            return [
                'date_received' => $r->entry_date,
                'date_issued'   => null,
                'type'          => 'Received',
                'category'      => $ledgeMap[$r->ledge_category] ?? ('Category ' . $r->ledge_category),
                'description'   => $r->description,
                'ref'           => preg_replace('/\s\[.*\]$/', '', $r->supplier_name ?: 'System'),
                'ref_label'     => 'Supplier / Source',
                'quantity'      => $r->qty ?? 0,
                'stock_bal'     => $r->stock_balance ?? '—',
                'variance'      => $r->variance ?? '—',
                'status'        => '—',
                'department'    => '—',
            ];
        })->merge($recentIssues->map(function($i) use ($ledgeMap) {
            return [
                'date_received' => $i->received_date,
                'date_issued'   => $i->entry_date,
                'type'          => 'Issued',
                'category'      => $ledgeMap[$i->ledge_category] ?? ('Category ' . $i->ledge_category),
                'description'   => $i->description,
                'ref'           => $i->beneficiary ?? '—',
                'ref_label'     => 'Beneficiary / Dept.',
                'quantity'      => $i->quantity ?? 0,
                'stock_bal'     => '—',
                'variance'      => '—',
                'status'        => $i->issuance_type ?? 'Permanent',
                'department'    => $i->department ?? '—',
            ];
        }))->sortByDesc(function($item) {
            return $item['date_received'] ?: $item['date_issued'];
        })->values();
    @endphp

    @if($allTransactions->count() > 0)
    <div class="table-card">
        <div class="table-card-header">
            <div class="table-title">Item(s) Report</div>
            <div class="table-subtitle">Received &amp; Issued items in order of date</div>
        </div>
        <table class="unified-ledger-table">
            <thead>
                <tr>
                    <th style="width: 110px;">Date Received</th>
                    <th style="width: 110px;">Date Issued</th>
                    <th style="width: 95px; text-align: center;">Type</th>
                    <th style="width: 115px;">Category</th>
                    <th>Item(s)</th>
                    <th style="width: 95px; text-align: center;">Status</th>
                    <th style="width: 130px;">Department</th>
                    <th style="width: 130px;">Supplier</th>
                    <th style="text-align: right; width: 70px;">Qty</th>
                    <th style="text-align: right; width: 90px;">Stock Bal.</th>
                    <th style="text-align: right; width: 80px;">Variance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allTransactions as $row)
                <tr>
                    <td>{{ $row['date_received'] ? \Carbon\Carbon::parse($row['date_received'])->format('d M Y') : '—' }}</td>
                    <td>{{ $row['date_issued'] ? \Carbon\Carbon::parse($row['date_issued'])->format('d M Y') : '—' }}</td>
                    <td>
                        @if($row['type'] === 'Received')
                            <span class="badge-received">
                                <svg width="7" height="7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; margin-right:2px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>Received
                            </span>
                        @else
                            <span class="badge-issued">
                                <svg width="7" height="7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; margin-right:2px;"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line></svg>Issued
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="badge-category">{{ $row['category'] }}</span>
                    </td>
                    <td style="font-weight: 700; text-transform: uppercase;">{{ $row['description'] }}</td>
                    <td>
                        @if($row['type'] === 'Issued')
                            @if($row['status'] === 'Temporary')
                                <span class="status-pill-temporary">Temporary</span>
                            @else
                                <span class="status-pill-permanent">Permanent</span>
                            @endif
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($row['type'] === 'Issued' && $row['department'] !== '—')
                            <span style="font-weight: 800; text-transform: uppercase; color: #0f172a;">{{ $row['department'] }}</span>
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($row['type'] === 'Received')
                            {{ $row['ref'] }}
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                    <td style="font-weight: 700;">
                        {{ is_numeric($row['quantity']) ? number_format((float)$row['quantity']) : $row['quantity'] }}
                    </td>
                    <td style="font-weight: 700;">
                        {{ is_numeric($row['stock_bal']) ? number_format((float)$row['stock_bal']) : $row['stock_bal'] }}
                    </td>
                    <td style="font-weight: 700; color: {{ is_numeric($row['variance']) && $row['variance'] > 0 ? '#ef4444' : (is_numeric($row['variance']) && $row['variance'] < 0 ? '#3b82f6' : '#94a3b8') }};">
                        {{ is_numeric($row['variance']) && $row['variance'] > 0 ? '+' : '' }}{{ is_numeric($row['variance']) ? number_format((float)$row['variance']) : $row['variance'] }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="ledger-totals-row">
                    <td colspan="8" style="font-weight: 800; font-size: 9px; text-transform: uppercase; color: #1e3b8b; background: #e0e7ff; border-top: 2px solid #1e3b8b; padding: 8px 12px;">
                        PERIOD TOTALS
                    </td>
                    <td style="text-align: right; font-weight: 800; color: #1e3b8b; background: #e0e7ff; border-top: 2px solid #1e3b8b; padding: 8px 12px;" colspan="3">
                        ↓ {{ number_format((float)$totalReceivedQty) }} received &nbsp;|&nbsp;
                        ↑ {{ number_format((float)$totalIssuedQty) }} issued
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- ApexCharts scripts --}}
    <script src="{{ asset('js/apexcharts.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const theme = 'light';
            const textColor = '#0f172a';
            
            // Shared bar chart defaults
            const barDefaults = {
                chart: {
                    type: 'bar',
                    background: 'transparent',
                    foreColor: textColor,
                    toolbar: { show: false },
                    animations: { enabled: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 5,
                        borderRadiusApplication: 'end',
                        barHeight: '55%',
                        distributed: true,
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: { fontSize: '10px', fontWeight: 800, fontFamily: 'Arial, sans-serif', colors: ['#fff'] },
                    formatter: (val) => val.toLocaleString(),
                    dropShadow: { enabled: false }
                },
                grid: {
                    borderColor: 'rgba(0,0,0,0.06)',
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } }
                },
                xaxis: {
                    labels: {
                        style: { fontSize: '9px', fontWeight: 700, fontFamily: 'Arial, sans-serif' },
                        formatter: (val) => val.toLocaleString()
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: { fontSize: '9px', fontWeight: 700, fontFamily: 'Arial, sans-serif', colors: textColor },
                        maxWidth: 180
                    }
                },
                legend: { show: false },
                tooltip: { enabled: false }
            };

            let chartsLoaded = 0;
            let expectedCharts = 0;

            @if($totalReceivedQty > 0 && $receivedDistribution->count() > 0)
                expectedCharts++;
                const recLabels = @json($receivedDistribution->pluck('description'));
                const recData   = @json($receivedDistribution->pluck('total_qty')->map(fn($q) => (float)$q));
                const recBarH   = Math.max(140, recLabels.length * 36);
                const recColors = ['#6366f1','#818cf8','#4f46e5','#7c3aed','#8b5cf6','#a78bfa','#4338ca','#3730a3','#06b6d4','#0ea5e9'];
                
                const recOptions = Object.assign({}, barDefaults, {
                    chart: Object.assign({}, barDefaults.chart, { 
                        height: recBarH,
                        events: {
                            mounted: () => { checkPrint(); }
                        }
                    }),
                    series: [{ name: 'Received', data: recData }],
                    xaxis: Object.assign({}, barDefaults.xaxis, { categories: recLabels }),
                    colors: recColors.slice(0, recLabels.length),
                });
                const recChart = new ApexCharts(document.querySelector('#received-bar-chart'), recOptions);
                recChart.render();
            @endif

            @if($totalIssuedQty > 0 && $issuedDistribution->count() > 0)
                expectedCharts++;
                const issLabels = @json($issuedDistribution->pluck('description'));
                const issData   = @json($issuedDistribution->pluck('total_qty')->map(fn($q) => (float)$q));
                const issBarH   = Math.max(140, issLabels.length * 36);
                const issColors = ['#f59e0b','#fbbf24','#d97706','#b45309','#ef4444','#f87171','#dc2626','#10b981','#06b6d4','#ec4899'];
                
                const issOptions = Object.assign({}, barDefaults, {
                    chart: Object.assign({}, barDefaults.chart, { 
                        height: issBarH,
                        events: {
                            mounted: () => { checkPrint(); }
                        }
                    }),
                    series: [{ name: 'Issued', data: issData }],
                    xaxis: Object.assign({}, barDefaults.xaxis, { categories: issLabels }),
                    colors: issColors.slice(0, issLabels.length),
                });
                const issChart = new ApexCharts(document.querySelector('#issued-bar-chart'), issOptions);
                issChart.render();
            @endif

            function checkPrint() {
                chartsLoaded++;
                if (chartsLoaded >= expectedCharts) {
                    setTimeout(() => {
                        window.print();
                    }, 1200);
                }
            }

            // Fallback print in case rendering hangs
            setTimeout(() => {
                if (chartsLoaded < expectedCharts) {
                    window.print();
                }
            }, 3000);

            if (expectedCharts === 0) {
                window.print();
            }
        });
    </script>

</body>
</html>
