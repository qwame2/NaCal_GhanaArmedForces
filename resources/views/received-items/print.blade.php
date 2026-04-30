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
                <span class="info-label">Entry Date (Auto)</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($batch->entry_date)->format('M d, Y') }}</span>
            </div>
            <div class="info-line">
                <span class="info-label">Arrival Date (Manual)</span>
                <span class="info-value">{{ $batch->arrival_date ? \Carbon\Carbon::parse($batch->arrival_date)->format('M d, Y') : 'N/A' }}</span>
            </div>

        </div>
        <div class="info-card">
            <h3>Logistics Entity</h3>
            @php
                $acqType = $batch->acquisition_type ?? 'Supplier';
                $donorName = $batch->donor_name;
                $supplierName = $batch->supplier_name;
                
                // Legacy Fallback
                $isDonor = ($acqType === 'Donor' || str_contains($supplierName, '[Donor Action]') || str_contains($supplierName, '[Donation]'));
                
                if ($isDonor && !$donorName) {
                    $donorName = trim(preg_replace('/\[.*?\]/', '', $supplierName));
                }
                
                $entityDisplay = $isDonor ? $donorName : trim(preg_replace('/\[.*?\]/', '', $supplierName));
            @endphp

            <div class="info-line">
                <span class="info-label">{{ $isDonor ? 'Donor / Organization' : 'Logistics Source' }}</span>
                <span class="info-value" style="font-size: 15px;">{{ $entityDisplay }}</span>
            </div>
            <div class="info-line">
                <span class="info-label">Transaction Type</span>
                <span class="info-value">{{ $isDonor ? 'Gift / Donation' : 'Standard Delivery' }}</span>
            </div>
            <div class="info-line">
                <span class="info-label">Verification ID</span>
                <span class="info-value">#{{ rand(1000,9999) }}</span>
            </div>
        </div>
    </div>

    <div class="narrative-section" style="margin-bottom: 40px;">
        <h3 style="font-size: 15px; font-weight: 900; text-transform: uppercase; border-bottom: 2px solid var(--print-primary); padding-bottom: 8px; margin-bottom: 20px; letter-spacing: 2px;">Itemized Verification Narrative</h3>
        
        @foreach($batch->items as $index => $item)
        @php
            $expected = floatval($item->qty);
            $variance = floatval($item->variance);
            
            $varText = 'no variance';
            if ($variance < 0) {
                $varText = 'a shortage of ' . abs($variance);
            } elseif ($variance > 0) {
                $varText = 'a surplus of ' . $variance;
            }

            $remarks = $item->remarks ?: 'No extraordinary conditions or operational remarks were documented for this line item during the inspection.';
        @endphp
        <div style="margin-bottom: 25px; padding: 20px; background: #f8fafc; border: 1px solid var(--print-border); border-radius: 12px; page-break-inside: avoid;">
            <div style="font-weight: 900; font-size: 14px; margin-bottom: 12px; color: var(--print-primary); border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                Scope {{ $index + 1 }} &bull; {{ strtoupper($item->description) }} ({{ $item->unit ?? 'Units' }})
            </div>
            <p style="margin: 0 0 15px; font-size: 13px; color: #334155; line-height: 1.8; text-align: justify;">
                The logistics personnel formally presented <strong>{{ $item->qty ?? '0' }} units</strong> for reception. 
                A rigorous count conducted on the floor yielded <strong>{{ $item->stock_balance }} units</strong> formally registered into the active stock baseline in verified condition.
                This operation culminated in <strong>{{ $varText }}</strong> units when matched against the authorized delivered quantity.
            </p>
            <div style="background: rgba(99, 102, 241, 0.05); padding: 12px 15px; border-radius: 8px; border-left: 3px solid var(--print-accent); font-size: 12px; color: #475569; font-style: italic;">
                <strong>Verification Field Note:</strong> {{ $remarks }}
            </div>
        </div>
        @endforeach
    </div>

    <div class="remarks-box">
        <strong>FINAL VERIFICATION DECLARATION:</strong><br>
        The items itemized in this narrative report have been formally processed, completely verified by standard operational protocols, and legally transitioned into the jurisdiction of the facility's ledger.
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

    <div style="position: fixed; bottom: 20px; left: 40px; font-size: 10px; color: #999;">
        Generated by NACOC Inventory System on {{ date('d-M-Y H:i:s') }}
    </div>

</body>

</html>
