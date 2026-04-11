<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher #{{ $batch->id }} - NACOC Inventory</title>
    <style>
        :root {
            --print-primary: #1e293b;
            --print-border: #e2e8f0;
            --print-accent: #6366f1;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            color: var(--print-primary);
            line-height: 1.6;
            padding: 50px;
            background: #fff;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 4px solid var(--print-primary);
            padding-bottom: 25px;
            margin-bottom: 40px;
        }

        .brand-section h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 900;
            letter-spacing: -1px;
            color: var(--print-primary);
        }

        .brand-section p {
            margin: 5px 0 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--print-accent);
            font-weight: 800;
        }

        .voucher-meta {
            text-align: right;
        }

        .voucher-meta div {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
        }

        .voucher-id {
            font-size: 18px;
            font-weight: 900;
            color: var(--print-primary);
        }

        .doc-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .doc-title h2 {
            display: inline-block;
            font-size: 20px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 4px;
            border-bottom: 2px solid var(--print-primary);
            padding-bottom: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .info-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--print-border);
        }

        .info-card h3 {
            margin: 0 0 15px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
        }

        .info-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .info-label {
            font-weight: 600;
            color: #475569;
        }

        .info-value {
            font-weight: 800;
            color: var(--print-primary);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 50px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--print-border);
        }

        th {
            background: #f1f5f9;
            padding: 12px 6px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-align: left;
            color: #475569;
        }

        td {
            padding: 12px 6px;
            font-size: 11px;
            border-top: 1px solid var(--print-border);
        }

        .row-sn {
            font-weight: 900;
            color: #94a3b8;
            text-align: center;
        }

        .row-desc {
            font-weight: 700;
            color: var(--print-primary);
        }

        .row-qty {
            font-weight: 800;
            color: #059669;
            text-align: center;
        }

        .row-bal {
            font-weight: 800;
            color: var(--print-primary);
            text-align: center;
        }

        .footer {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 40px;
            margin-top: 80px;
        }

        .sign-box {
            text-align: center;
        }

        .sign-line {
            border-top: 2px solid var(--print-primary);
            margin-top: 60px;
            padding-top: 10px;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .remarks-box {
            margin-top: 40px;
            padding: 20px;
            border-left: 4px solid var(--print-accent);
            background: #fdf2f8;
            /* Very subtle tint */
            font-size: 14px;
            font-style: italic;
        }

        @media print {
            body {
                padding: 0;
            }

            .print-btn {
                display: none;
            }

            .info-card {
                background: #fff !important;
            }
        }

        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 15px 30px;
            background: var(--print-accent);
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: 900;
            border-radius: 50px;
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }

        .print-btn:hover {
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <button class="print-btn" onclick="window.print()">Print Document</button>

    <div class="header">
        <div class="brand-section" style="display: flex; align-items: center; gap: 15px;">
            <img src="{{ asset('img/NACOC1.png') }}" alt="NACOC Logo" style="height: 60px; object-fit: contain;">
            <div>
                <h1>NACOC INVENTORY</h1>
                <p>Management System</p>
            </div>
        </div>
        <div class="voucher-meta">
            <div>DOCUMENT REFERENCE</div>
            <div class="voucher-id">#V{{ $batch->id }}-{{ date('Ymd') }}</div>
        </div>
    </div>

    <div class="doc-title">
        <h2>Certified Transaction Voucher</h2>
    </div>

    <div class="info-grid">
        <div class="info-card">
            <h3>Shipment Parameters</h3>
            <div class="info-line">
                <span class="info-label">Transaction ID</span>
                <span class="info-value">{{ str_pad($batch->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="info-line">
                <span class="info-label">Entry Date</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($batch->entry_date)->format('M d, Y') }}</span>
            </div>
            <div class="info-line">
                <span class="info-label">Stock Ledge</span>
                <span class="info-value">{{ $ledgeMap[$batch->ledge_category] ?? $batch->ledge_category }}</span>
            </div>
        </div>
        <div class="info-card">
            <h3>Logistics Entity</h3>
            @php
                $isDonor = str_contains($batch->supplier_name, '[Donor Action]');
                $entityName = trim(preg_replace('/\[.*?\]/', '', $batch->supplier_name));
            @endphp

            <div class="info-line">
                <span class="info-label">{{ $isDonor ? 'Donor / Organization' : 'Logistics Source' }}</span>
                <span class="info-value" style="font-size: 15px;">{{ $entityName }}</span>
            </div>
            <div class="info-line">
                <span class="info-label">Transaction Type</span>
                <span class="info-value">{{ $isDonor ? 'Gift / Donation' : 'Standard Delivery' }}</span>
            </div>
            <div class="info-line">
                <span class="info-label">Audit Line</span>
                <span class="info-value">#{{ rand(1000,9999) }}</span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Category</th>
                <th>Supplier</th>
                <th>Donor</th>
                <th>Status</th>
                <th style="text-align: center;">Avail. Qty</th>
                <th style="text-align: center;">Ledge</th>
                <th style="text-align: center;">Stock</th>
                <th style="text-align: center;">Variance</th>
            </tr>
        </thead>
        <tbody>
            @php
            $supStatus = preg_match('/\[(.*?)\]/', $batch->supplier_name, $m) ? $m[1] : 'Delivered';
            $formattedDate = \Carbon\Carbon::parse($batch->entry_date)->format('M d, Y');
            $categoryName = $ledgeMap[$batch->ledge_category] ?? $batch->ledge_category;
            $supplierText = $isDonor ? '-' : $entityName;
            $donorText = $isDonor ? $entityName : '-';
            @endphp
            @foreach($batch->items as $item)
            <tr>
                <td style="color: #64748b; white-space: nowrap;">{{ $formattedDate }}</td>
                <td class="row-desc" style="max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $item->description }}">{{ $item->description }}</td>
                <td style="color: #475569;">{{ $categoryName }}</td>
                <td style="max-width: 90px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $supplierText }}</td>
                <td style="max-width: 90px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $donorText }}</td>
                <td style="font-weight: 800; color: var(--print-accent);">{{ $supStatus }}</td>
                <td style="text-align: center; font-weight: 800;">{{ $item->qty ?? '0' }}</td>
                <td style="text-align: center; color: #64748b;">{{ $item->ledge_balance }}</td>
                <td class="row-bal" style="text-align: center;">{{ $item->stock_balance }}</td>
                <td class="row-qty" style="text-align: center;">{{ is_numeric($item->variance) && $item->variance > 0 ? '+' : '' }}{{ $item->variance }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="remarks-box">
        <strong>AUDIT REMARKS:</strong><br>
        {{ request('remarks') ?: 'The items described above have been formally received, inspected against the accompanying dispatch documents, and found to be in full compliance with the reported quantities.' }}
    </div>

    <div class="footer">
        <div class="sign-box">
            <div class="sign-line">Logistics Officer</div>
            <p style="font-size: 10px; margin-top: 5px;">(Signature & Stamp)</p>
        </div>
        <div class="sign-box">
            <div class="sign-line">Independent Auditor</div>
            <p style="font-size: 10px; margin-top: 5px;">(Signature & Stamp)</p>
        </div>
        <div class="sign-box">
            <div class="sign-line">Approval Authority</div>
            <p style="font-size: 10px; margin-top: 5px;">(Signature & Date)</p>
        </div>
    </div>

    <div style="position: fixed; bottom: 20px; left: 40px; font-size: 10px; color: #999;">
        Generated by NACOC Inventory System on {{ date('d-M-Y H:i:s') }}
    </div>

</body>

</html>
