<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Received Items Report - NACOC Inventory</title>

    <!-- Local Fonts -->
    <link href="{{ asset('css/css2.css') }}" rel="stylesheet">

    <style>
        :root {
            --print-primary: #0f172a;
            --print-secondary: #475569;
            --print-border: #cbd5e1;
            --print-accent: #059669;
            --print-bg-subtle: #f8fafc;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            color: var(--print-primary);
            line-height: 1.4;
            padding: 30px;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Prevent page break inside cards and rows */
        h1, h2, h3, h4 {
            page-break-after: avoid;
            margin-top: 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 3px solid var(--print-primary);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .brand-section h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: var(--print-primary);
        }

        .brand-section p {
            margin: 3px 0 0;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--print-accent);
            font-weight: 800;
        }

        .report-meta {
            text-align: right;
        }

        .report-meta div {
            font-size: 10px;
            font-weight: 700;
            color: var(--print-secondary);
            letter-spacing: 1px;
        }

        .report-id {
            font-size: 15px;
            font-weight: 900;
            color: var(--print-primary);
            margin-top: 2px;
        }

        .doc-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .doc-title h2 {
            display: inline-block;
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 3px solid var(--print-accent);
            padding-bottom: 6px;
            color: var(--print-primary);
            margin-bottom: 4px;
        }

        .filter-tags {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .filter-badge {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .stat-card {
            background: var(--print-bg-subtle);
            border-radius: 8px;
            padding: 12px 16px;
            border: 1px solid var(--print-border);
            border-left: 4px solid var(--print-accent);
        }

        .stat-card .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--print-secondary);
            font-weight: 800;
        }

        .stat-card .value {
            font-size: 18px;
            font-weight: 900;
            color: var(--print-primary);
            margin-top: 4px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--print-border);
            margin-bottom: 25px;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th {
            background: #f1f5f9;
            padding: 8px 10px;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-align: left;
            color: var(--print-secondary);
            border-bottom: 2px solid var(--print-border);
        }

        td {
            padding: 8px 10px;
            font-size: 11px;
            border-top: 1px solid var(--print-border);
            color: var(--print-primary);
            vertical-align: middle;
        }

        tr:nth-child(even) td {
            background: rgba(248, 250, 252, 0.6);
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }

        .footer {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .sign-box {
            text-align: center;
        }

        .sign-line {
            border-top: 1.5px solid var(--print-primary);
            margin-top: 45px;
            padding-top: 6px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--print-primary);
            letter-spacing: 0.5px;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm 10mm 10mm 10mm;
            }

            html, body {
                padding: 0 !important;
                margin: 0 !important;
                background: #fff !important;
            }

            .print-btn {
                display: none !important;
            }
        }

        .print-btn {
            position: fixed;
            bottom: 25px;
            right: 25px;
            padding: 12px 24px;
            background: var(--print-accent);
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: 800;
            font-size: 12px;
            border-radius: 50px;
            box-shadow: 0 8px 20px rgba(5, 150, 105, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.25s ease;
            z-index: 9999;
        }

        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(5, 150, 105, 0.5);
            background: #065f46;
        }
    </style>
</head>

<body onload="window.print();">
    <button class="print-btn" onclick="window.print()">Print Document</button>

    <div class="header">
        <div class="brand-section" style="display: flex; align-items: center; gap: 12px;">
            <img src="{{ asset('img/NACOC1.png') }}" alt="NACOC Logo" style="height: 50px; object-fit: contain;">
            <div>
                <h1>NACOC INVENTORY</h1>
                <p>Management System (NSIMs)</p>
            </div>
        </div>
        <div class="report-meta">
            <div>REPORT GENERATED</div>
            <div class="report-id">{{ date('d M Y, H:i') }}</div>
            <div style="margin-top: 2px; color: var(--print-accent); font-weight: 800;">USER: {{ auth()->user()->name ?? 'Officer' }}</div>
        </div>
    </div>

    <div class="doc-title">
        <h2>Received Items</h2>
        <div class="filter-tags">
            @php $hasFilter = false; @endphp
            @if(request('search'))
                <span class="filter-badge">Search: "{{ request('search') }}"</span>
                @php $hasFilter = true; @endphp
            @endif
            @if(request('supplier'))
                <span class="filter-badge">Supplier: {{ request('supplier') }}</span>
                @php $hasFilter = true; @endphp
            @endif
            @if(request('donor'))
                <span class="filter-badge">Donor: {{ request('donor') }}</span>
                @php $hasFilter = true; @endphp
            @endif
            @if(request('ledge_category'))
                <span class="filter-badge">Category: {{ $ledgeMap[request('ledge_category')] ?? 'Category ' . request('ledge_category') }}</span>
                @php $hasFilter = true; @endphp
            @endif
            @if(request('store_location'))
                <span class="filter-badge">Location: {{ request('store_location') }}</span>
                @php $hasFilter = true; @endphp
            @endif
            @if(request('status'))
                <span class="filter-badge">Status: {{ ucfirst(str_replace('_', ' ', request('status'))) }}</span>
                @php $hasFilter = true; @endphp
            @endif
            @if(request('date_from') || request('date_to'))
                <span class="filter-badge">Date: {{ request('date_from', 'Start') }} to {{ request('date_to', 'Present') }}</span>
                @php $hasFilter = true; @endphp
            @endif
            @if(!$hasFilter)
                <span class="filter-badge" style="background: #f1f5f9; color: #475569; border-color: #cbd5e1;">All Received Items</span>
            @endif
        </div>
    </div>

    @php
        $totalItemsCount = $receivedItems->count();
        $totalReceivedQty = 0;
        $totalStockBalance = 0;
        foreach($receivedItems as $item) {
            $expectedQty = !is_null($item->book_qty) ? (float)$item->book_qty : (float)($item->qty ?? 0);
            $totalReceivedQty += ($expectedQty + (float)($item->variance ?? 0));
            $totalStockBalance += (float)($item->stock_balance ?? 0);
        }
    @endphp

    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">Total Records</div>
            <div class="value">{{ number_format($totalItemsCount) }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Total Qty Received</div>
            <div class="value">{{ number_format($totalReceivedQty) }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Total Stock Balance</div>
            <div class="value">{{ number_format($totalStockBalance) }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Filtered View</div>
            <div class="value" style="font-size: 14px; text-transform: uppercase; color: var(--print-accent);">
                {{ $hasFilter ? 'Custom Filter' : 'Full Inventory' }}
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">#</th>
                <th style="width: 80px;">Entry Date</th>
                <th style="width: 80px;">Received Date</th>
                <th>Item Description</th>
                <th style="width: 110px;">Category</th>
                <th style="width: 130px;">Supplier / Donor</th>
                <th style="width: 100px;">Location</th>
                <th style="width: 100px;">Delivery Status</th>
                <th style="width: 75px;" class="text-right">Rec. Qty</th>
                <th style="width: 75px;" class="text-right">Stock Bal.</th>
                <th style="width: 65px;" class="text-right">Variance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receivedItems as $index => $item)
            @php
                $cleanSupplier = $item->supplier_name;
                $acquisitionType = $item->acquisition_type ?? 'Supplier';
                $donorName = $item->donor_name ?? '-';

                $dbStatus = strtoupper($item->supplier_status ?: 'FULL DELIVERY');
                $isDbPartialDelivery = ($dbStatus === 'PARTIAL DELIVERY' || str_contains($dbStatus, 'PARTIAL'));

                $isIssuedOut = method_exists($item, 'hasActiveTemporaryLoan') && $item->hasActiveTemporaryLoan();
                if ($isIssuedOut) {
                    $displayStatus = 'ISSUED OUT';
                    $statusColor = '#059669';
                } elseif ($isDbPartialDelivery) {
                    $statusColor = '#ef4444';
                    $displayStatus = 'PARTIAL DELIVERY';
                } else {
                    $statusColor = '#059669';
                    $displayStatus = 'FULL DELIVERY';
                }

                $expectedQty = !is_null($item->book_qty) ? (float)$item->book_qty : (float)($item->qty ?? 0);
                $receivedQtyDisplay = $expectedQty + (float)($item->variance ?? 0);
            @endphp
            <tr>
                <td class="text-center font-bold" style="color: #64748b;">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($item->entry_date)->format('d/m/y H:i') }}</td>
                <td style="color: var(--print-accent); font-weight: 700;">{{ $item->arrival_date ? \Carbon\Carbon::parse($item->arrival_date)->format('d/m/y') : '-' }}</td>
                <td>
                    <div class="font-bold">{{ $item->description }}</div>
                    <div style="font-size: 9px; color: var(--print-secondary);">Batch #{{ $item->batch_id }} &bull; {{ $item->unit ?? 'Unit' }}</div>
                    @if(!empty($item->serial_number))
                        <div style="font-size: 9px; color: #059669; font-weight: 700; margin-top: 2px;">
                            S/N: {{ $item->serial_number }}
                        </div>
                    @endif
                    @if(is_numeric($item->variance) && (float)$item->variance != 0 && !empty($item->remarks))
                        <div style="font-size: 9px; color: #ef4444; font-weight: 700; margin-top: 2px;">
                            Expl: {{ $item->remarks }}
                        </div>
                    @endif
                </td>
                <td>
                    <span style="font-weight: 600;">
                        {{ $ledgeMap[$item->ledge_category] ?? "Category " . $item->ledge_category }}
                    </span>
                </td>
                <td>
                    @if($acquisitionType === 'Donor' || !empty($item->donor_name))
                        <div><strong>{{ $donorName !== '-' ? $donorName : ($cleanSupplier ?: '-') }}</strong> <span style="font-size: 8px; background: #e0e7ff; color: #3730a3; padding: 1px 4px; border-radius: 3px;">DONOR</span></div>
                    @else
                        <div>{{ $cleanSupplier ?: '-' }}</div>
                    @endif
                </td>
                <td>
                    <span style="font-weight: 700; color: {{ str_contains($item->store_location ?? 'Store A', 'B') ? '#2563eb' : '#059669' }};">
                        {{ str_replace('Stores', 'Store', $item->store_location ?? 'Store A') }}
                    </span>
                </td>
                <td>
                    <span style="font-size: 9px; font-weight: 800; color: {{ $statusColor }}; text-transform: uppercase;">
                        {{ $displayStatus }}
                    </span>
                </td>
                <td class="text-right font-bold">{{ number_format($receivedQtyDisplay) }}</td>
                <td class="text-right font-bold">{{ number_format((float)($item->stock_balance ?? 0)) }}</td>
                <td class="text-right font-bold" style="color: {{ is_numeric($item->variance) && (float)$item->variance > 0 ? '#059669' : (is_numeric($item->variance) && (float)$item->variance < 0 ? '#ef4444' : '#64748b') }};">
                    {{ is_numeric($item->variance) && (float)$item->variance > 0 ? '+' : '' }}{{ number_format((float)($item->variance ?? 0)) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center" style="padding: 20px; color: var(--print-secondary);">
                    No received items match the selected filter criteria.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
