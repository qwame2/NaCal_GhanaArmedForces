<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection Voucher - {{ str_replace('-LEGACY', '', $receipt->receipt_number) }}</title>
    <!-- Local Fonts for Premium Offline Look -->
    <link href="{{ asset('css/css2.css') }}" rel="stylesheet">
    
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-light: #475569;
            --accent: #881337;
            --accent-light: rgba(136, 19, 55, 0.06);
            --success: #881337;
            --success-light: rgba(5, 150, 105, 0.06);
            --warning: #047857;
            --warning-light: rgba(217, 119, 6, 0.06);
            --danger: #dc2626;
            --border-color: #e2e8f0;
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #475569;
        }

        /* Base styles reset */
        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            background-color: var(--bg-main);
            margin: 0;
            padding: 3rem 1.5rem;
            line-height: 1.6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .voucher-card {
            max-width: 900px;
            margin: 0 auto;
            background: var(--bg-card);
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.04);
            padding: 3rem;
            position: relative;
            box-sizing: border-box;
        }

        /* Watermark Background */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 7.5rem;
            font-weight: 900;
            color: rgba(241, 245, 249, 0.65);
            font-family: 'Outfit', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            pointer-events: none;
            z-index: 0;
            white-space: nowrap;
        }

        .voucher-content {
            position: relative;
            z-index: 1;
        }

        /* Top Header Metadata bar */
        .meta-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.72rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .meta-status {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--success);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: var(--success);
            border-radius: 50%;
            display: inline-block;
        }

        /* Header Layout */
        .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid var(--accent);
            padding-bottom: 1.75rem;
            margin-bottom: 2.25rem;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .org-logo {
            width: 75px;
            height: 75px;
            object-fit: contain;
        }

        .org-details h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.65rem;
            font-weight: 900;
            margin: 0;
            letter-spacing: -0.03em;
            color: var(--primary);
            text-transform: uppercase;
            line-height: 1.1;
        }

        .org-details p {
            font-size: 0.75rem;
            color: var(--accent);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin: 0.35rem 0 0 0;
        }

        .voucher-title-box {
            text-align: right;
        }

        .voucher-badge {
            display: inline-block;
            background: var(--accent-light);
            color: var(--accent);
            border: 1.5px solid rgba(136, 19, 55, 0.2);
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 0.85rem;
            padding: 0.45rem 1rem;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .voucher-number {
            font-size: 1.25rem;
            font-weight: 900;
            color: var(--primary);
            margin-top: 0.5rem;
            font-family: monospace;
        }

        /* 3-Column Detailed Parameters Dashboard */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.25rem;
            box-sizing: border-box;
        }

        .dashboard-card-title {
            font-size: 0.68rem;
            font-weight: 900;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.85rem;
            border-bottom: 1.5px solid var(--border-color);
            padding-bottom: 0.45rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .dashboard-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.6rem;
            font-size: 0.82rem;
            line-height: 1.4;
        }

        .dashboard-row:last-child {
            margin-bottom: 0;
        }

        .dashboard-label {
            color: var(--text-muted);
            font-weight: 600;
        }

        .dashboard-value {
            font-weight: 800;
            color: var(--text-main);
            text-align: right;
            max-width: 140px;
            word-wrap: break-word;
        }

        /* Text Block Details */
        .text-details-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .text-block-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent);
            border-radius: 12px;
            padding: 1.25rem;
            box-sizing: border-box;
        }

        .text-block-title {
            font-size: 0.68rem;
            font-weight: 900;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.5rem;
        }

        .text-block-content {
            font-size: 0.82rem;
            color: var(--text-main);
            margin: 0;
            text-align: justify;
            line-height: 1.5;
            font-style: italic;
        }

        /* Table Design */
        .table-section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            font-weight: 900;
            color: var(--accent);
            margin-top: 2.25rem;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            border-bottom: 2px dashed var(--border-color);
            padding-bottom: 0.5rem;
        }

        .voucher-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 3rem;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
        }

        .voucher-table th {
            background: #f1f5f9;
            color: var(--text-main);
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.06em;
            padding: 1rem;
            text-align: left;
            border-bottom: 1.5px solid var(--border-color);
        }

        .voucher-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.82rem;
            vertical-align: top;
            background: #ffffff;
        }

        .voucher-table tr:last-child td {
            border-bottom: none;
        }

        .item-sn {
            font-weight: 900;
            color: var(--text-muted);
            text-align: center;
        }

        .item-title {
            font-weight: 800;
            color: var(--text-main);
        }

        .item-meta-info {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.35rem;
            line-height: 1.4;
        }

        .badge-fulfillment {
            display: inline-block;
            font-size: 0.65rem;
            font-weight: 900;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .badge-full {
            background-color: var(--success-light);
            color: var(--success);
        }

        .badge-partial {
            background-color: var(--warning-light);
            color: var(--warning);
        }

        .badge-none {
            background-color: rgba(220, 38, 38, 0.06);
            color: var(--danger);
        }

        /* Certificate Declarations */
        .legal-declaration {
            background: rgba(30, 41, 59, 0.02);
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            padding: 1.25rem;
            font-size: 0.78rem;
            color: var(--text-muted);
            text-align: justify;
            line-height: 1.6;
            margin-bottom: 3.5rem;
        }

        /* Signatures Grid */
        .signatures-section {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 2.5rem;
            margin-top: 4rem;
        }

        .signature-block {
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            position: relative;
        }

        .signature-space {
            height: 110px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            margin-bottom: -45px;
            position: relative;
            z-index: 5;
            pointer-events: none;
        }

        .signature-img {
            max-height: 120px;
            max-width: 250px;
            object-fit: contain;
            transform: rotate(-1.5deg);
            mix-blend-mode: multiply;
            filter: contrast(1.05) brightness(0.98);
        }

        .signature-line {
            border-top: 1.5px solid var(--border-color);
            margin-top: 2rem;
            padding-top: 0.6rem;
        }

        .signature-title {
            font-size: 0.78rem;
            font-weight: 900;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .signature-sub {
            font-size: 0.65rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
            text-transform: uppercase;
            font-weight: 700;
        }

        /* Actions Bar */
        .action-bar {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            max-width: 900px;
            margin: 1.5rem auto 0 auto;
        }

        .btn {
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #000000;
        }

        .btn-secondary {
            background: white;
            color: var(--text-main);
            border-color: var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--bg-main);
        }

        /* Print Override */
        @media print {
            @page {
                margin: 0;
            }
            body {
                background: white;
                padding: 1.5cm 1.2cm;
            }

            .voucher-card {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }

            .action-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="voucher-card">
        <!-- Background watermark -->
        <div class="watermark">RELEASE VOUCHER</div>

        <div class="voucher-content">
            <!-- Top Meta Info Bar -->
            <div class="meta-bar">
                <span>SECURITY CLASSIFICATION: OFFICIAL / RESTRICTED</span>
                <span class="meta-status">
                    <span class="status-dot"></span>
                    STATUS: TRANSACTED & VERIFIED
                </span>
            </div>

            <!-- Header -->
            <div class="voucher-header">
                <div class="logo-section">
                    <img src="{{ asset('img/NACOC.png') }}" alt="NACOC Logo" class="org-logo">
                    <div class="org-details">
                        <h1>{{ trim(str_ireplace('(nsims)', '', \App\Models\Setting::get('organization_name', 'NACOC'))) }}</h1>
                        <p>Stores Inventory Management System</p>
                    </div>
                </div>
                <div class="voucher-title-box">
                    <div class="voucher-badge">Issuance Receipt</div>
                    <div class="voucher-number">{{ str_replace('-LEGACY', '', $receipt->receipt_number) }}</div>
                    <div class="receipt-date">
                        Date Released: {{ $receipt->collected_at ? $receipt->collected_at->format('d M Y, H:i') : now()->format('d M Y, H:i') }}
                    </div>
                </div>
            </div>

            <!-- 3-Column Parameters Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Column 1: Requester Profile -->
                <div class="dashboard-card">
                    <div class="dashboard-card-title">
                        REQUISITIONER INFO
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Name:</span>
                        <span class="dashboard-value" title="{{ $req->requester_name }}">{{ $req->requester_name }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Role:</span>
                        <span class="dashboard-value">{{ $req->requester?->role ?? ($req->rank_or_title ?: 'Requisitioner') }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Department:</span>
                        <span class="dashboard-value">{{ $req->department }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Service No:</span>
                        <span class="dashboard-value">{{ $req->requester?->service_number ?? 'N/A' }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Date &amp; Time:</span>
                        <span class="dashboard-value">{{ $req->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <!-- Column 2: Release Authorization -->
                <div class="dashboard-card">
                    <div class="dashboard-card-title">
                        STORE ISSUER INFO
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Name:</span>
                        <span class="dashboard-value" title="{{ $receipt->issuer?->name ?? $req->processor?->name ?? 'Store Officer' }}">{{ $receipt->issuer?->name ?? $req->processor?->name ?? 'Store Officer' }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Role:</span>
                        <span class="dashboard-value">
                            @php
                                $issuerRole = $receipt->issuer?->role ?? $req->processor?->role ?? 'Head of Stores';
                                if (in_array($issuerRole, ['Main Admin', 'Sub Main Admin'])) {
                                    $issuerRole = 'Head of Admin';
                                }
                            @endphp
                            {{ $issuerRole }}
                        </span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Department:</span>
                        <span class="dashboard-value">{{ $receipt->issuer?->department ?? 'Stores Department' }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Requisition Ref:</span>
                        <span class="dashboard-value">{{ $req->unique_id }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Date &amp; Time:</span>
                        <span class="dashboard-value">{{ $req->processed_at ? \Carbon\Carbon::parse($req->processed_at)->format('d/m/Y H:i') : ($receipt->created_at ? $receipt->created_at->format('d/m/Y H:i') : $req->updated_at->format('d/m/Y H:i')) }}</span>
                    </div>
                </div>

                <!-- Column 3: Physical Release Log -->
                <div class="dashboard-card">
                    <div class="dashboard-card-title">
                        PHYSICAL COLLECTION INFO
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Name:</span>
                        <span class="dashboard-value" title="{{ $receipt->collector_name }}">{{ $receipt->collector_name }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Role / Staff ID:</span>
                        <span class="dashboard-value">{{ $receipt->collector_staff_id ? 'ID: ' . $receipt->collector_staff_id : 'Collector' }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Department:</span>
                        <span class="dashboard-value" title="{{ $receipt->collector_location }}">{{ $receipt->collector_location ?: $req->department }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Contact No:</span>
                        <span class="dashboard-value">{{ $receipt->collector_contact }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Date &amp; Time:</span>
                        <span class="dashboard-value">{{ $receipt->collected_at ? $receipt->collected_at->format('d/m/Y H:i') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Narratives / Purposes -->
            <div class="text-details-container">
            </div>

            <!-- Table breakdown -->
            <div class="table-section-title">
                Itemized Breakdown
            </div>

             <table class="voucher-table">
                <thead>
                     <tr>
                        <th style="width: 6%; text-align: center;">S/N</th>
                        <th style="width: 23.5%;">Category</th>
                        <th style="width: 23.5%;">Item Details</th>
                        <th style="width: 23.5%;">Qty Requested</th>
                        <th style="width: 23.5%;">Qty Released</th>
                     </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                        @php
                            $requested = (float)($item['quantity_requested'] ?? 0);
                            $approved = (float)($item['quantity_approved'] ?? 0);
                            $altApproved = (float)($item['alternative_quantity_approved'] ?? 0);
                            $totalApproved = $approved + $altApproved;
                            
                            $ratio = $requested > 0 ? ($totalApproved / $requested) : 0;
                            
                            if ($totalApproved === 0.0) {
                                $badgeClass = 'badge-none';
                                $badgeLabel = 'Declined';
                            } elseif ($ratio >= 1.0) {
                                $badgeClass = 'badge-full';
                                $badgeLabel = '100% Release';
                            } else {
                                $badgeClass = 'badge-partial';
                                $badgeLabel = round($ratio * 100) . '% Reduced';
                            }
                        @endphp
                        <tr>
                            <td class="item-sn">{{ $index + 1 }}</td>
                            <td>
                                <div class="item-category" style="font-weight: 800; font-size: 0.78rem;">
                                    Category {{ $item['category'] ?? 'A' }}
                                </div>
                                <div style="font-size: 0.68rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">
                                    {{ $ledgeMap[$item['category'] ?? 'A'] ?? 'General' }}
                                </div>
                            </td>
                            <td>
                                <div class="item-title">{{ $item['description'] }}</div>
                                @if(!empty($item['alternative_description']) && $altApproved > 0)
                                    <div class="item-alternative">
                                        <strong>Alternative Approved:</strong> {{ $item['alternative_description'] }} 
                                        ({{ $altApproved }} {{ $item['unit'] ?? 'units' }})
                                    </div>
                                @endif
                                @if(!empty($item['remarks']))
                                    <div class="item-remarks">
                                        <strong>Remarks:</strong> "{{ $item['remarks'] }}"
                                    </div>
                                @endif
                            </td>
                            <td style="font-weight: 700;">
                                <div class="qty-val">{{ number_format($requested, 2) }}</div>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $item['unit'] ?? 'units' }}</div>
                            </td>
                            <td>
                                <div class="qty-val" style="color: var(--success); font-weight: 900; font-size: 0.9rem;">
                                    {{ number_format($totalApproved, 2) }}
                                </div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700; margin-bottom: 4px;">{{ $item['unit'] ?? 'units' }}</div>
                                <span class="badge-fulfillment {{ $badgeClass }}">{{ $badgeLabel }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @php
                $approvals = [];
                
                // 1. Department HOD
                if ($req->origin_approved_by && stripos($req->origin_approved_by, 'bypassed') === false) {
                    $user = \App\Models\User::where('name', $req->origin_approved_by)->first();
                    $hodRole = $user?->role ?? 'Department Head';
                    if (in_array($hodRole, ['Main Admin', 'Sub Main Admin'])) {
                        $hodRole = 'Head of Admin';
                    }
                    $approvals[] = [
                        'stage' => 'HOD Approval',
                        'name' => $req->origin_approved_by,
                        'role' => $hodRole,
                        'department' => $req->department ?: ($user?->department ?? 'Originating Department'),
                        'date_time' => $req->origin_approved_at ? \Carbon\Carbon::parse($req->origin_approved_at)->format('d M Y, h:i A') : $req->created_at->format('d M Y, h:i A')
                    ];
                }
                
                // 2. Stores HOD / Head of Admin
                if ($req->stores_approved_by && stripos($req->stores_approved_by, 'bypassed') === false) {
                    $user = \App\Models\User::where('name', $req->stores_approved_by)->first();
                    $adminRole = $user?->role ?? 'Head of Admin';
                    if (in_array($adminRole, ['Main Admin', 'Sub Main Admin'])) {
                        $adminRole = 'Head of Admin';
                    }
                    $approvals[] = [
                        'stage' => 'Head of Admin Review',
                        'name' => $req->stores_approved_by,
                        'role' => $adminRole,
                        'department' => $user?->department ?? 'Stores Department',
                        'date_time' => $req->stores_approved_at ? \Carbon\Carbon::parse($req->stores_approved_at)->format('d M Y, h:i A') : $req->created_at->format('d M Y, h:i A')
                    ];
                }
                
                // 3. Director General
                if ($req->dg_approved_by && stripos($req->dg_approved_by, 'bypassed') === false) {
                    $user = \App\Models\User::where('name', $req->dg_approved_by)->first();
                    $dgRole = $user?->role ?? 'Director General';
                    if (in_array($dgRole, ['Main Admin', 'Sub Main Admin'])) {
                        $dgRole = 'Head of Admin';
                    }
                    $approvals[] = [
                        'stage' => 'Director General Approval',
                        'name' => $req->dg_approved_by,
                        'role' => $dgRole,
                        'department' => $user?->department ?? 'Executive Directorate',
                        'date_time' => $req->dg_approved_at ? \Carbon\Carbon::parse($req->dg_approved_at)->format('d M Y, h:i A') : $req->created_at->format('d M Y, h:i A')
                    ];
                }
                
                // 4. Head of Stores (Processor)
                if ($req->processor?->name && stripos($req->processor->name, 'bypassed') === false) {
                    $processorRole = $req->processor->role ?? 'Head of Stores';
                    if (in_array($processorRole, ['Main Admin', 'Sub Main Admin'])) {
                        $processorRole = 'Head of Admin';
                    }
                    $approvals[] = [
                        'stage' => 'Stores Processing & Release',
                        'name' => $req->processor->name,
                        'role' => $processorRole,
                        'department' => $req->processor->department ?? 'Stores Department',
                        'date_time' => $req->processed_at ? \Carbon\Carbon::parse($req->processed_at)->format('d M Y, h:i A') : $req->created_at->format('d M Y, h:i A')
                    ];
                }
            @endphp

            @if(count($approvals) > 0)
                <div class="table-section-title" style="margin-top: 2rem;">
                    Requisition Approvals &amp; Authorizations Log
                </div>
                <div class="signatures-section" style="grid-template-columns: repeat({{ min(count($approvals), 4) }}, 1fr); margin-top: 1rem; margin-bottom: 2rem; gap: 1.25rem; display: grid;">
                    @foreach($approvals as $appr)
                        @php
                            $displayRole = $appr['role'];
                            if (in_array($displayRole, ['Main Admin', 'Sub Main Admin'])) {
                                $displayRole = 'Head of Admin';
                            }
                        @endphp
                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; padding: 1rem; text-align: left; box-sizing: border-box;">
                            <div style="font-size: 0.68rem; font-weight: 900; color: var(--accent); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                                {{ $appr['stage'] }}
                            </div>
                            <div style="font-size: 0.92rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.25rem;">
                                {{ $appr['name'] }}
                            </div>
                            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.2rem;">
                                <strong>Role:</strong> {{ $displayRole }}
                            </div>
                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-bottom: 0.2rem;">
                                <strong>Dept:</strong> {{ $appr['department'] }}
                            </div>
                            <div style="font-size: 0.7rem; font-weight: 800; color: var(--primary); margin-top: 0.5rem; padding-top: 0.4rem; border-top: 1px dashed var(--border-color);">
                                <strong>Date &amp; Time:</strong> {{ $appr['date_time'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Legal Verification Statement -->
            <div class="legal-declaration" style="margin-top: 3rem;">
                <strong>INVENTORY RELEASE STATEMENT & CERTIFICATION:</strong><br>
                I certify that the items on this receipt are verified, approved, and released from NACOC Stores Department. The receiving representative has inspected and confirmed physical receipt. All transactions are logged per Stores Division procedures.
            </div>
            <div style="text-align: center; font-size: 0.68rem; color: var(--text-muted); margin-top: 3rem; border-top: 1px solid var(--border-color); padding-top: 1rem; font-style: italic; font-weight: 600; letter-spacing: 0.02em;">
                This voucher was generated through using the NACOC STORES INVENTORY MANAGEMENT SYSTEM (NSIMS).
            </div>
        </div>
    </div>

    <!-- Quick Action Bar -->
    <div class="action-bar">
        <button onclick="window.print()" class="btn btn-primary">
            Print Official Voucher
        </button>
    </div>

    <!-- Auto Print Script -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>

</body>
</html>