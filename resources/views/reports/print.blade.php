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
            margin: 0 auto;
            max-width: 1120px;
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
            color: #16a34a;
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

        .visualization-container {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        #received-bar-chart, #issued-bar-chart {
            width: 100%;
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
            color: #10b981;
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
            border: 1px solid #10b981;
            color: #b45309;
            background: #ecfdf5;
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
            border: 1px solid #16a34a;
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
            background: #ecfdf5;
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
                max-width: 100% !important;
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print-btn {
                display: none !important;
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
            <div class="stat-col-value"><strong>{{ number_format((float)$totalReceivedQty) }}</strong> <span>Item(s)</span></div>
        </div>
        <div class="stat-col">
            <div class="stat-col-label">Total Issued</div>
            <div class="stat-col-value"><strong>{{ number_format((float)$totalIssuedQty) }}</strong> <span>Item(s)</span></div>
        </div>
        <div class="stat-col">
            <div class="stat-col-label">Stock Balance</div>
            <div class="stat-col-value"><strong>{{ number_format(max(0, (float)$totalReceivedQty - (float)$totalIssuedQty)) }}</strong> <span>Item(s)</span></div>
        </div>
    </div>

    {{-- Narrative Report Breakdown --}}
    @php
        $essay = "";
        
        // 1. Introduction
        if (!empty($selectedItems)) {
            $itemsLimit = 4;
            $itemsDisplay = array_slice($selectedItems, 0, $itemsLimit);
            $itemsStr = implode(", ", $itemsDisplay);
            if (count($selectedItems) > $itemsLimit) {
                $itemsStr .= ", and " . (count($selectedItems) - $itemsLimit) . " other items";
            }
            $essay .= "This report details the activity of specific items, namely: <strong>" . e($itemsStr) . "</strong>, during the <strong>" . e($dateLabel) . "</strong> period. ";
        } else {
            $essay .= "This report provides a general overview of all inventory activity within the system during the <strong>" . e($dateLabel) . "</strong> period. ";
        }

        // 2. Incoming inventory (Receivals)
        if ($totalReceivedQty > 0) {
            $essay .= "On the incoming side, we successfully received a total of <strong>" . number_format($totalReceivedQty) . "</strong> Item(s) of stock, which arrived in <strong>" . $totalReceivedBatches . "</strong> separate batches. ";
            
            if (count($selectedItems) !== 1 && $receivedDistribution->count() > 0) {
                $topReceived = $receivedDistribution->first();
                $essay .= "Among the received items, the highest volume was for <strong>" . e($topReceived->description) . "</strong>, with a total of <strong>" . number_format($topReceived->total_qty) . "</strong> Item(s) incoming. ";
            }
            
            $totalVariance = 0;
            foreach($recentReceivals as $rr) {
                if (is_numeric($rr->variance)) {
                    $totalVariance += (float)$rr->variance;
                }
            }
            if ($totalVariance != 0) {
                $essay .= "During receipt inspection, a total variance of <strong>" . ($totalVariance > 0 ? '+' : '') . number_format($totalVariance) . "</strong> Item(s) was recorded, representing the difference between the supplier invoice quantities and the physical quantities received in our warehouse. ";
            } else {
                $essay .= "All incoming shipments matched their expected quantities perfectly, with zero variance recorded. ";
            }
        } else {
            $essay .= "No new stock was received or recorded in the system during this timeframe. ";
        }

        // 3. Outgoing inventory (Issues)
        if ($totalIssuedQty > 0) {
            $essay .= "Regarding stock distributions, we issued out a total of <strong>" . number_format($totalIssuedQty) . "</strong> Item(s) to meet operational requests. ";
            
            $deptCounts = [];
            $tempCount = 0;
            $permCount = 0;
            foreach ($recentIssues as $ri) {
                if ($ri->issuance_type === 'Temporary') {
                    $tempCount += $ri->quantity;
                } else {
                    $permCount += $ri->quantity;
                }
                $dept = $ri->department ?: 'Unassigned Departments';
                $deptCounts[$dept] = ($deptCounts[$dept] ?? 0) + $ri->quantity;
            }
            
            if (!empty($deptCounts)) {
                arsort($deptCounts);
                $topDept = key($deptCounts);
                $topDeptQty = current($deptCounts);
                $essay .= "The primary requestor was the <strong>" . e($topDept) . "</strong>, which received <strong>" . number_format($topDeptQty) . "</strong> Item(s). ";
                if (count($deptCounts) > 1) {
                    $otherDepts = array_slice(array_keys($deptCounts), 1, 2);
                    $essay .= "Other notable quantities were distributed to: " . implode(', ', $otherDepts) . ". ";
                }
            }
            
            if ($tempCount > 0) {
                $essay .= "Of the total outgoing items, <strong>" . number_format($tempCount) . "</strong> Item(s) were issued on a temporary basis (loans that must be returned to the store), while <strong>" . number_format($permCount) . "</strong> Item(s) were issued permanently. ";
            } else {
                $essay .= "All distributions made during this period were permanent issuances to the respective departments. ";
            }
        } else {
            $essay .= "No items were issued out or distributed during this reporting period. ";
        }

        // 4. Summary / Net
        $netMovement = (float)$totalReceivedQty - (float)$totalIssuedQty;
        if ($totalReceivedQty > 0 || $totalIssuedQty > 0) {
            $essay .= "In summary, the stock balance shows that we " . ($netMovement >= 0 ? "retained" : "reduced") . " our inventory levels by <strong>" . number_format(abs($netMovement)) . "</strong> Item(s). ";
            if ($netMovement < 0) {
                $essay .= "This indicates that our consumption rate exceeded our replenishment rate for this period. ";
            } else {
                $essay .= "This indicates a healthy stock buildup to meet future operational demands. ";
            }
        }
    @endphp

    <div class="narrative-breakdown" style="margin-bottom: 25px; padding: 20px; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 4px; line-height: 1.6; page-break-inside: avoid;">
        <h3 style="margin-top: 0; color: #1e3a8a; font-family: 'Arial', sans-serif; font-size: 12px; font-weight: bold; border-bottom: 1.5px solid #cbd5e1; padding-bottom: 8px; text-transform: uppercase;">Report Overview</h3>
        <p style="font-size: 11px; color: #0f172a; margin-bottom: 0; text-align: justify; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            {!! $essay !!}
        </p>
    </div>

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
        <div id="received-bar-chart"></div>
    </div>
    @endif

    {{-- Issuance Visualization --}}
    @if($totalIssuedQty > 0 && $issuedDistribution->count() > 0)
    <div class="visualization-container">
        <div class="visualization-header">
            <span class="vis-icon-wrap" style="background: #10b981;">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            </span>
            <h2>Issuance Visualization</h2>
        </div>
        <div class="vis-sub-label orange-label">● ISSUANCE — TOP {{ $issuedDistribution->count() }} ITEMS</div>
        <div id="issued-bar-chart"></div>
    </div>
    @endif

    {{-- Unified Ledger --}}
    @php
        // Fetch all unique item descriptions to construct the supplier/donor mapping
        $uniqueDescs = collect()
            ->concat($recentReceivals->pluck('description'))
            ->concat($recentIssues->pluck('description'))
            ->unique()
            ->filter()
            ->toArray();

        $itemSources = [];
        if (!empty($uniqueDescs)) {
            $rawSources = \DB::table('inventory_items')
                ->join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                ->whereIn('inventory_items.description', $uniqueDescs)
                ->select('inventory_items.description', 'inventory_batches.supplier_name', 'inventory_batches.donor_name', 'inventory_batches.acquisition_type')
                ->get();
            foreach ($rawSources as $rs) {
                $desc = trim($rs->description);
                $src = $rs->acquisition_type === 'Donor' ? ($rs->donor_name ?: $rs->supplier_name) : $rs->supplier_name;
                $src = preg_replace('/\s\[.*\]$/', '', $src ?: '');
                if ($src && strtolower($src) !== 'system') {
                    if (!isset($itemSources[$desc])) {
                        $itemSources[$desc] = [];
                    }
                    if (!in_array($src, $itemSources[$desc])) {
                        $itemSources[$desc][] = $src;
                    }
                }
            }
            $itemSources = array_map(function($srcs) {
                return implode(', ', $srcs);
            }, $itemSources);
        }

        // Merge both collections, normalise fields, then sort by date desc
        $allTransactions = $recentReceivals->map(function($r) use ($ledgeMap) {
            $source = $r->acquisition_type === 'Donor' ? ($r->donor_name ?: $r->supplier_name) : $r->supplier_name;
            return [
                'date_received' => $r->entry_date,
                'date_issued'   => null,
                'type'          => 'Received',
                'category'      => $ledgeMap[$r->ledge_category] ?? ('Category ' . $r->ledge_category),
                'description'   => $r->description,
                'serial_number' => $r->serial_number,
                'ref'           => preg_replace('/\s\[.*\]$/', '', $source ?: 'System'),
                'ref_label'     => 'Supplier / Source',
                'quantity'      => $r->qty ?? 0,
                'stock_bal'     => !is_null($r->book_qty) ? $r->book_qty : ($r->stock_balance ?? '—'),
                'variance'      => $r->variance ?? '—',
                'status'        => '—',
                'department'    => '—',
                'sources'       => null,
            ];
        })->merge($recentIssues->map(function($i) use ($ledgeMap, $itemSources) {
            return [
                'date_received' => $i->received_date,
                'date_issued'   => $i->entry_date,
                'type'          => 'Issued',
                'category'      => $ledgeMap[$i->ledge_category] ?? ('Category ' . $i->ledge_category),
                'description'   => $i->description,
                'serial_number' => null,
                'ref'           => $i->beneficiary ?? '—',
                'ref_label'     => 'Beneficiary / Dept.',
                'quantity'      => $i->original_quantity ?? $i->quantity ?? 0,
                'stock_bal'     => '—',
                'variance'      => '—',
                'status'        => $i->issuance_type ?? 'Permanent',
                'department'    => $i->department ?? '—',
                'sources'       => $itemSources[trim($i->description)] ?? null,
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
                    <td style="font-weight: 700; text-transform: uppercase;">
                        {{ $row['description'] }}
                        @if(!empty($row['serial_number']))
                            @php
                                $snList = array_filter(array_map('trim', explode(',', $row['serial_number'])));
                                $count = count($snList);
                            @endphp
                            @if($count > 0)
                                <div style="font-size: 8px; color: #16a34a; font-weight: bold; margin-top: 3px; text-transform: none; display: flex; align-items: center; gap: 3px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle;"><path d="M3 5v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2z"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg>
                                    S/N: {{ implode(', ', $snList) }}
                                </div>
                            @endif
                        @endif
                    </td>
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
                            @if(!empty($row['sources']))
                                <span style="color: #475569; font-style: italic;">{{ $row['sources'] }}</span>
                            @else
                                <span style="color: #94a3b8;">—</span>
                            @endif
                        @endif
                    </td>
                    <td style="font-weight: 700;">
                        {{ is_numeric($row['quantity']) ? number_format((float)$row['quantity']) : $row['quantity'] }}
                    </td>
                    <td style="font-weight: 700;">
                        {{ is_numeric($row['stock_bal']) ? number_format((float)$row['stock_bal']) : $row['stock_bal'] }}
                    </td>
                    <td style="font-weight: 700; color: {{ is_numeric($row['variance']) && $row['variance'] > 0 ? '#ef4444' : (is_numeric($row['variance']) && $row['variance'] < 0 ? '#16a34a' : '#94a3b8') }};">
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

    {{-- Signature Sign-Off Block --}}
    @php
        $isAdmin = auth()->user()->is_admin;
        $headOfStores = $isAdmin ? auth()->user() : \App\Models\User::where('is_admin', true)->where('role', 'Head of Stores')->where('is_active', true)->first();
    @endphp
    <div class="signature-block" style="{{ $isAdmin ? 'grid-template-columns: 1fr; max-width: 300px; margin-left: auto; margin-right: auto;' : '' }}">
        @if(!$isAdmin)
            <div class="sig-col">
                <div class="sig-line" style="margin-top: 60px;">{{ auth()->user()->name }}</div>
                <div class="sig-subtitle">Prepared By ({{ auth()->user()->role }})</div>
            </div>
        @endif
        <div class="sig-col" style="position: relative;">
            @if($headOfStores && $headOfStores->signature)
                <div style="height: 50px; display: flex; align-items: flex-end; justify-content: center; margin-bottom: -30px; position: relative; z-index: 5; pointer-events: none;">
                    <img src="{{ Storage::url($headOfStores->signature) }}" style="max-height: 65px; max-width: 180px; object-fit: contain; mix-blend-mode: multiply;" alt="Head of Stores Signature">
                </div>
            @else
                <div style="height: 50px;"></div>
            @endif
            <div class="sig-line">{{ $headOfStores ? $headOfStores->name : 'Head of Stores' }}</div>
            <div class="sig-subtitle">Approved By (Head of Stores)</div>
        </div>
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
                    width: '100%',
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
                    yaxis: { lines: { show: false } },
                    padding: {
                        left: 20,
                        right: 20
                    }
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
                const recColors = ['#16a34a','#4ade80','#16a34a','#15803d','#4ade80','#a78bfa','#15803d','#3730a3','#06b6d4','#0ea5e9'];
                
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
                const issColors = ['#10b981','#fbbf24','#047857','#b45309','#ef4444','#f87171','#dc2626','#10b981','#06b6d4','#ec4899'];
                
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
