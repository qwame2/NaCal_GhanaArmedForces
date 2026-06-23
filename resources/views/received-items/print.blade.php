<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher #{{ $batch->id }} - NACOC Inventory</title>
    
    <!-- Local Fonts for Premium Offline Look -->
    <link href="{{ asset('css/css2.css') }}" rel="stylesheet">

    <style>
        :root {
            --print-primary: #0f172a;
            --print-secondary: #475569;
            --print-border: #e2e8f0;
            --print-accent: #4f46e5;
            --print-bg-subtle: #f8fafc;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            color: var(--print-primary);
            line-height: 1.5;
            padding: 40px;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Prevent dangling headings */
        h1, h2, h3, h4, h5, h6 {
            page-break-after: avoid;
            margin-top: 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 3px solid var(--print-primary);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .brand-section h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: var(--print-primary);
        }

        .brand-section p {
            margin: 4px 0 0;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--print-accent);
            font-weight: 800;
        }

        .voucher-meta {
            text-align: right;
        }

        .voucher-meta div {
            font-size: 11px;
            font-weight: 700;
            color: var(--print-secondary);
            letter-spacing: 1px;
        }

        .voucher-id {
            font-size: 18px;
            font-weight: 900;
            color: var(--print-primary);
            margin-top: 2px;
        }

        .doc-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .doc-title h2 {
            display: inline-block;
            font-size: 20px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 3px;
            border-bottom: 3px solid var(--print-accent);
            padding-bottom: 8px;
            color: var(--print-primary);
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .info-card {
            background: var(--print-bg-subtle);
            border-radius: 10px;
            padding: 18px;
            border: 1px solid var(--print-border);
            border-left: 4px solid var(--print-accent);
        }

        .info-card h3 {
            margin: 0 0 12px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--print-secondary);
            font-weight: 800;
        }

        .info-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 13px;
            border-bottom: 1px dashed rgba(226, 232, 240, 0.6);
            padding-bottom: 6px;
        }

        .info-line:last-child {
            margin-bottom: 0;
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: var(--print-secondary);
        }

        .info-value {
            font-weight: 700;
            color: var(--print-primary);
        }

        /* High Fidelity Table Styles */
        .table-section {
            margin-bottom: 35px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--print-border);
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th {
            background: #f1f5f9;
            padding: 10px 14px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-align: left;
            color: var(--print-secondary);
            border-bottom: 2px solid var(--print-border);
        }

        td {
            padding: 10px 14px;
            font-size: 12px;
            border-top: 1px solid var(--print-border);
            color: var(--print-primary);
        }

        tr:nth-child(even) td {
            background: rgba(248, 250, 252, 0.5);
        }

        .row-sn {
            font-weight: 800;
            color: #94a3b8;
            text-align: center;
        }

        .row-desc {
            font-weight: 700;
            color: var(--print-primary);
        }

        .remarks-box {
            margin-top: 25px;
            padding: 16px 20px;
            border-left: 4px solid var(--print-accent);
            background: #fdf2f8; /* subtle rose tint */
            font-size: 13px;
            line-height: 1.6;
            color: var(--print-primary);
            border-radius: 0 8px 8px 0;
            page-break-inside: avoid;
        }

        /* Signatures block */
        .footer {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 40px;
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .sign-box {
            text-align: center;
        }

        .sign-line {
            border-top: 1.5px solid var(--print-primary);
            margin-top: 50px;
            padding-top: 8px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--print-primary);
            letter-spacing: 0.5px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm 15mm 15mm 15mm;
            }

            html, body {
                height: auto !important;
                overflow: visible !important;
                padding: 0 !important;
                margin: 0 !important;
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-btn {
                display: none !important;
            }

            .info-card {
                background: var(--print-bg-subtle) !important;
            }

        }

        /* Screen Only Floating Print Button */
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 14px 28px;
            background: var(--print-accent);
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: 800;
            font-size: 12px;
            border-radius: 50px;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.25s ease;
            z-index: 9999;
        }

        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(79, 70, 229, 0.5);
            background: #4338ca;
        }
    </style>
</head>

<body onload="window.print();">
    <button class="print-btn" onclick="window.print()">Print Document</button>

    <div class="header">
        <div class="brand-section" style="display: flex; align-items: center; gap: 15px;">
            <img src="{{ asset('img/NACOC1.png') }}" alt="NACOC Logo" style="height: 60px; object-fit: contain;">
            <div>
                <h1>NACOC INVENTORY</h1>
                <p>Management System(NSIMs)</p>
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
            <h3>Receiving Parameters</h3>
            <div class="info-line">
                <span class="info-label">Transaction ID</span>
                <span class="info-value">{{ str_pad($batch->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="info-line">
                <span class="info-label">Entry Date (Auto)</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($batch->entry_date)->format('d/m/y') }}</span>
            </div>
            <div class="info-line">
                <span class="info-label">Received Date (Manual)</span>
                <span class="info-value">{{ $batch->arrival_date ? \Carbon\Carbon::parse($batch->arrival_date)->format('d/m/y') : 'N/A' }}</span>
            </div>

        </div>
        <div class="info-card">
            <h3>Logistics Entity</h3>
            @php
                $acqType = $batch->acquisition_type ?? 'Supplier';
                $donorName = $batch->donor_name;
                $supplierName = $batch->supplier_name;
                $supplierNameStr = $supplierName ?? '';
                
                // Legacy Fallback
                $isDonor = ($acqType === 'Donor' || str_contains($supplierNameStr, '[Donor Action]') || str_contains($supplierNameStr, '[Donation]'));
                
                if ($isDonor && !$donorName) {
                    $donorName = trim(preg_replace('/\[.*?\]/', '', $supplierNameStr));
                }
                
                $entityDisplay = $isDonor ? $donorName : trim(preg_replace('/\[.*?\]/', '', $supplierNameStr));

                $deliveryPerson = $batch->delivery_person ?? '';
                $deliveryPhone = $batch->delivery_phone ?? '';
                if (empty($deliveryPerson)) {
                    $suppliersRegistry = \App\Models\Setting::get('suppliers_registry', []);
                    foreach ($suppliersRegistry as $k => $v) {
                        if (strcasecmp(trim($k), trim($entityDisplay)) === 0 || ($supplierName && strcasecmp(trim($k), trim($supplierName)) === 0)) {
                            $entityDisplay = $k;
                            $deliveryPerson = $v['delivery_person'] ?? '';
                            $deliveryPhone = $v['delivery_phone'] ?? '';
                            break;
                        }
                    }
                }

                $isBatchIssuedOut = false;
                foreach ($batch->items as $it) {
                    if ($it->hasActiveTemporaryLoan()) {
                        $isBatchIssuedOut = true;
                        break;
                    }
                }
            @endphp

            <div class="info-line">
                <span class="info-label">{{ $isDonor ? 'Donor Name' : 'Supplier Name' }}</span>
                <span class="info-value" style="font-size: 15px;">{{ $entityDisplay }}</span>
            </div>
            <div class="info-line">
                <span class="info-label">Contact Person</span>
                <span class="info-value">{{ $deliveryPerson ?: 'N/A' }}</span>
            </div>
            <div class="info-line">
                <span class="info-label">Contact Person Number</span>
                <span class="info-value">{{ $deliveryPhone ?: 'N/A' }}</span>
            </div>
            <div class="info-line">
                <span class="info-label">Transaction Type</span>
                <span class="info-value">{{ $isDonor ? 'Gift / Donation' : 'Standard Delivery' }}</span>
            </div>
            <div class="info-line">
                <span class="info-label">Supply Status</span>
                @if($isBatchIssuedOut)
                    <span class="info-value" style="color: #f59e0b; text-transform: uppercase;">Issued Out</span>
                @else
                    <span class="info-value">{{ $batch->supplier_status ?: 'Full Delivery' }}</span>
                @endif
            </div>
            <div class="info-line">
                <span class="info-label">Verification ID</span>
                <span class="info-value">#{{ rand(1000,9999) }}</span>
            </div>
        </div>
    </div>

    <!-- Itemized Verification Ledger Table -->
    <div class="table-section">
        <h3 style="font-size: 14px; font-weight: 800; text-transform: uppercase; border-bottom: 2px solid var(--print-primary); padding-bottom: 6px; margin-bottom: 15px; letter-spacing: 1.5px;">Itemized Verification Ledger</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">S/N</th>
                    <th>Item Description</th>
                    <th style="width: 100px; text-align: center;">Unit</th>
                    <th style="width: 100px; text-align: center;">Expected Qty</th>
                    <th style="width: 100px; text-align: center;">Verified Qty</th>
                    <th style="width: 100px; text-align: center;">Variance</th>
                    <th style="width: 140px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($batch->items as $index => $item)
                @php
                    $expected = floatval($item->qty);
                    $verified = floatval($item->stock_balance);
                    $variance = floatval($item->variance);

                    $statusText = 'Verified Match';
                    $statusColor = '#059669'; // Green
                    $statusBg = 'rgba(5, 150, 105, 0.06)';

                    if ($variance < 0) {
                        $statusText = 'Shortage (' . $variance . ')';
                        $statusColor = '#dc2626'; // Red
                        $statusBg = 'rgba(220, 38, 38, 0.06)';
                    } elseif ($variance > 0) {
                        $statusText = 'Surplus (+' . $variance . ')';
                        $statusColor = '#2563eb'; // Blue
                        $statusBg = 'rgba(37, 99, 235, 0.06)';
                    }
                @endphp
                <tr>
                    <td class="row-sn">{{ $index + 1 }}</td>
                    <td class="row-desc">
                        {{ $item->description }}
                        @if(!empty($item->serial_number))
                            <div style="margin-top: 4px; font-size: 10px; font-weight: 700; color: var(--print-secondary);">
                                S/N: {{ $item->serial_number }}
                            </div>
                        @endif
                    </td>
                    <td style="text-align: center; color: var(--print-secondary); font-weight: 500;">{{ $item->unit ?? 'Pcs' }}</td>
                    <td style="text-align: center; font-weight: 600; color: var(--print-secondary);">{{ number_format($expected) }}</td>
                    <td style="text-align: center; font-weight: 700;">{{ number_format($verified) }}</td>
                    <td style="text-align: center; font-weight: 700; color: {{ $variance < 0 ? '#dc2626' : ($variance > 0 ? '#2563eb' : '#059669') }};">
                        {{ $variance > 0 ? '+' : '' }}{{ number_format($variance) }}
                    </td>
                    <td style="text-align: center;">
                        <span style="display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 10px; font-weight: 800; text-transform: uppercase; color: {{ $statusColor }}; background: {{ $statusBg }}; border: 1px solid {{ $statusColor }}33;">
                            {{ $statusText }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Selective Field Notes & Forensic Audit Log -->
    @php
        $hasRemarksOrHistory = false;
        foreach($batch->items as $item) {
            $itemHistory = $history->filter(function($log) use ($item) {
                return isset($log->metadata['item_changes'][$item->id]);
            });
            if ($item->remarks || $itemHistory->count() > 0) {
                $hasRemarksOrHistory = true;
                break;
            }
        }
    @endphp

    @if($hasRemarksOrHistory)
    <div style="margin-bottom: 30px; page-break-inside: avoid;">
        <h3 style="font-size: 14px; font-weight: 800; text-transform: uppercase; border-bottom: 2px solid var(--print-primary); padding-bottom: 6px; margin-bottom: 15px; letter-spacing: 1.5px;">Field Inspection & Revision Log</h3>
        
        @foreach($batch->items as $index => $item)
        @php
            $itemHistory = $history->filter(function($log) use ($item) {
                return isset($log->metadata['item_changes'][$item->id]);
            });
        @endphp

        @if($item->remarks || $itemHistory->count() > 0)
        <div style="margin-bottom: 15px; padding: 14px 18px; background: var(--print-bg-subtle); border: 1px solid var(--print-border); border-left: 4px solid var(--print-accent); border-radius: 8px; page-break-inside: avoid;">
            <div style="font-weight: 800; font-size: 12px; margin-bottom: 8px; color: var(--print-primary); display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px;">
                <span>Line {{ $index + 1 }} &bull; {{ strtoupper($item->description) }}</span>
                <span style="color: var(--print-secondary); font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Verification Field Note</span>
            </div>
            
            @if($item->remarks)
            <div style="margin-bottom: 8px; font-size: 12px; color: var(--print-secondary); line-height: 1.5;">
                <strong>Remarks:</strong> <span style="font-style: italic; color: var(--print-primary);">"{{ $item->remarks }}"</span>
            </div>
            @endif

            @if($itemHistory->count() > 0)
            <div style="margin-top: 10px; padding: 10px 14px; background: #fffbeb; border: 1px dashed #d97706; border-radius: 6px;">
                <div style="font-size: 10px; font-weight: 800; color: #b45309; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">
                    Forensic Revision Log
                </div>
                @foreach($itemHistory as $log)
                <div style="font-size: 11px; color: #475569; margin-bottom: 4px; border-bottom: 1px dashed rgba(217, 119, 6, 0.1); padding-bottom: 4px;">
                    <span style="font-weight: 700; color: #92400e;">[{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/y H:i') }}]:</span>
                    @foreach($log->metadata['item_changes'][$item->id] as $field => $vals)
                    <span style="margin-right: 12px; display: inline-block;">
                        <strong style="text-transform: capitalize;">{{ str_replace('_', ' ', $field) }}:</strong>
                        <span style="color: #ef4444; text-decoration: line-through;">{{ $vals['old'] }}</span> &rarr;
                        <span style="color: #059669; font-weight: 700;">{{ $vals['new'] }}</span>
                    </span>
                    @endforeach
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endif
        @endforeach
    </div>
    @endif

    <div class="remarks-box">
        <strong>FINAL VERIFICATION DECLARATION:</strong><br>
        The items itemized in this narrative report have been formally processed, completely verified by standard operational protocols, and legally transitioned into the jurisdiction of the facility's category.
    </div>

    <div class="footer">
        <div class="sign-box">
            <div class="sign-line">Logistics Officer</div>
            <p style="font-size: 10px; margin-top: 5px;">(Signature & Stamp)</p>
        </div>
        <div class="sign-box">
            <div class="sign-line">Independent Verifier</div>
            <p style="font-size: 10px; margin-top: 5px;">(Signature & Stamp)</p>
        </div>
        <div class="sign-box">
            <div class="sign-line">Approval</div>
            <p style="font-size: 10px; margin-top: 5px;">(Signature & Date)</p>
        </div>
    </div>


</body>

</html>
