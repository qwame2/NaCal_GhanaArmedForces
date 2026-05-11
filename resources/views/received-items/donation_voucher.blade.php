<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Certificate #{{ str_pad($batch->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            color: #000;
            margin: 0;
            padding: 40px;
            background: #fff;
            line-height: 1.4;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 30px;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px double #000;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            text-decoration: underline;
            letter-spacing: 3px;
        }
        .header h2 {
            margin: 10px 0 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .certificate-title {
            text-align: center;
            margin: 20px 0;
            font-size: 22px;
            font-weight: bold;
            background: #000;
            color: #fff;
            padding: 10px;
            letter-spacing: 1px;
        }
        .doc-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .meta-box {
            border: 1px solid #000;
            padding: 10px;
            min-width: 200px;
        }
        .meta-label {
            font-size: 10px;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        
        .donation-narrative {
            margin-bottom: 30px;
            font-size: 16px;
            text-align: justify;
        }
        .donor-highlight {
            font-weight: bold;
            text-decoration: underline;
            font-size: 18px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 12px;
            text-align: left;
        }
        .items-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        
        .value-disclaimer {
            font-style: italic;
            font-size: 12px;
            margin-bottom: 30px;
            border: 1px dashed #000;
            padding: 10px;
            text-align: center;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 50px;
        }
        .sig-block {
            text-align: center;
        }
        .sig-line {
            border-bottom: 1px solid #000;
            margin-bottom: 8px;
            height: 50px;
        }
        .sig-label {
            font-weight: bold;
            font-size: 13px;
        }
        .sig-sub {
            font-size: 11px;
            color: #444;
        }

        @media print {
            body { padding: 0; }
            .print-btn { display: none; }
            .container { border-width: 3px; }
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 25px;
            background: #222;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
            border-radius: 6px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .coa-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            opacity: 0.05;
            pointer-events: none;
            z-index: 0;
        }
    </style>
</head>
<body>
    @if($batch->id !== 'DRAFT')
    <button class="print-btn" onclick="window.print()">PRINT CERTIFICATE</button>
    @endif

    <div class="container">
        @if($batch->id === 'DRAFT' || $batch->approval_status !== 'approved')
        <div style="position: absolute; top: 20px; right: 20px; background: #ef4444; color: white; padding: 8px 20px; border-radius: 8px; font-weight: 800; font-size: 0.9rem; z-index: 100; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">
            DRAFT: AWAITING APPROVAL
        </div>
        @endif
        <img src="{{ asset('img/COA.svg') }}" class="coa-watermark" alt="Watermark">
        
        <div class="header">
            <h1>GHANA GOVERNMENT</h1>
            <h2>STORES SERVICE - Narcotics Control Commission</h2>
        </div>

        <div class="certificate-title">
            CERTIFICATE OF DONATION ACCEPTANCE
        </div>

        <div class="doc-meta">
            <div class="meta-box">
                <span class="meta-label">CERTIFICATE NO.</span>
                <strong>DON-{{ str_pad($batch->id, 6, '0', STR_PAD_LEFT) }}</strong>
            </div>
            <div class="meta-box">
                <span class="meta-label">DATE OF RECEIPT</span>
                <strong>{{ \Carbon\Carbon::parse($batch->arrival_date ?: $batch->entry_date)->format('d F, Y') }}</strong>
            </div>
            <div class="meta-box">
                <span class="meta-label">STATION / REGION</span>
                <strong>ACCRA / GREATER ACCRA</strong>
            </div>
        </div>

        <div class="donation-narrative">
            This is to formally certify and record the receipt of a voluntary gift/donation from 
            <span class="donor-highlight">{{ $batch->donor_name ?: 'Private Benefactor' }}</span>. 
            The following assets have been inspected, verified, and officially taken on charge into the Commission's Inventory Category 
            for the purpose of institutional support and operational utility.
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 60px;">NO.</th>
                    <th>DESCRIPTION OF GIFTED ASSETS</th>
                    <th style="width: 100px;">UNIT</th>
                    <th style="width: 120px;">QUANTITY</th>
                </tr>
            </thead>
            <tbody>
                @foreach($batch->items as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->description }}</strong>
                        @if($item->remarks)
                        <br><span style="font-size: 11px; font-style: italic;">Condition Note: {{ $item->remarks }}</span>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $item->unit }}</td>
                    <td style="text-align: center; font-weight: bold; font-size: 16px;">{{ $item->qty }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="value-disclaimer">
            NOTICE: As a philanthropic contribution, these items are recorded with zero commercial liability to the Ghana Government. 
            The valuation is based on institutional utility and recorded for inventory transparency and audit compliance only.
        </div>

        <div class="signatures">
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">OFFICER-IN-CHARGE</div>
                <div class="sig-sub">(Acceptance Authority)</div>
                <div style="margin-top: 10px; font-weight: bold;">{{ $admin->name ?? '________________' }}</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">STOREKEEPER / VERIFIER</div>
                <div class="sig-sub">(Inventory Registry)</div>
                <div style="margin-top: 10px; font-weight: bold;">{{ $batch->recorder->name ?? (auth()->user()->name ?? '________________') }}</div>
            </div>
        </div>

        <div style="margin-top: 40px; text-align: center; font-size: 11px; border-top: 1px solid #eee; padding-top: 20px;">
            OFFICIAL STAMP & DATE OF RECORDING: {{ date('d/m/Y H:i') }}
        </div>
    </div>

    <div style="text-align: center; margin-top: 20px; font-size: 10px; color: #777;">
        System Generated Donation Acceptance Receipt - Narcotics Control Commission CORE
    </div>
</body>
</html>
