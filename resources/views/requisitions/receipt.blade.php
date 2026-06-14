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
            --accent: #4f46e5;
            --accent-light: rgba(79, 70, 229, 0.06);
            --success: #059669;
            --success-light: rgba(5, 150, 105, 0.06);
            --warning: #d97706;
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
            border: 1.5px solid rgba(79, 70, 229, 0.2);
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
            padding: 0.85rem 1rem;
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
            height: 65px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            margin-bottom: -28px;
            position: relative;
            z-index: 5;
            pointer-events: none;
        }

        .signature-img {
            max-height: 80px;
            max-width: 180px;
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
            body {
                background: white;
                padding: 0;
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
                    <img src="{{ asset('img/NACOC1.png') }}" alt="NACOC Logo" class="org-logo">
                    <div class="org-details">
                        <h1>{{ \App\Models\Setting::get('organization_name', 'NACOC') }}</h1>
                        <p>Inventory Logistics Department</p>
                    </div>
                </div>
                <div class="voucher-title-box">
                    <div class="voucher-badge">Issue & Release Voucher</div>
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
                        REQUISITIONER PARAMETERS
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Officer:</span>
                        <span class="dashboard-value" title="{{ $req->requester_name }}">{{ $req->requester_name }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Service No:</span>
                        <span class="dashboard-value">{{ $req->requester?->service_number ?? 'N/A' }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Rank/Title:</span>
                        <span class="dashboard-value">{{ $req->rank_or_title ?? 'N/A' }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Department:</span>
                        <span class="dashboard-value">{{ $req->department }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Contact No:</span>
                        <span class="dashboard-value">{{ $req->requester?->phone ?? 'N/A' }}</span>
                    </div>
                </div>

                <!-- Column 2: Release Authorization -->
                <div class="dashboard-card">
                    <div class="dashboard-card-title">
                        LOGISTICS AUTHORIZATION
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Requisition Ref:</span>
                        <span class="dashboard-value">{{ $req->unique_id }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Requested Date:</span>
                        <span class="dashboard-value">{{ $req->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Priority Level:</span>
                        <span class="dashboard-value">
                            @if($req->priority === 'urgent')
                                <span style="color: var(--danger); font-weight: 900;">URGENT</span>
                            @elseif($req->priority === 'low')
                                <span style="color: var(--text-muted); font-weight: 900;">LOW</span>
                            @else
                                <span style="color: var(--accent); font-weight: 900;">NORMAL</span>
                            @endif
                        </span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Allocation Limit:</span>
                        <span class="dashboard-value">
                            @if($req->usage_type === 'temporary')
                                <span style="color: var(--warning); font-weight: 800;">Temporary Loan</span>
                            @else
                                <span style="color: var(--success); font-weight: 800;">Permanent Release</span>
                            @endif
                        </span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Released By:</span>
                        <span class="dashboard-value" title="{{ $receipt->issuer?->name ?? $req->processor?->name ?? 'Store Officer' }}">{{ $receipt->issuer?->name ?? $req->processor?->name ?? 'Store Officer' }}</span>
                    </div>
                </div>

                <!-- Column 3: Physical Release Log -->
                <div class="dashboard-card">
                    <div class="dashboard-card-title">
                        PHYSICAL COLLECTION LOG
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Collector:</span>
                        <span class="dashboard-value" title="{{ $receipt->collector_name }}">{{ $receipt->collector_name }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Contact No:</span>
                        <span class="dashboard-value">{{ $receipt->collector_contact }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Destination:</span>
                        <span class="dashboard-value" title="{{ $receipt->collector_location }}">{{ $receipt->collector_location }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Released Date:</span>
                        <span class="dashboard-value">{{ $receipt->collected_at ? $receipt->collected_at->format('d/m/Y H:i') : 'N/A' }}</span>
                    </div>
                    <div class="dashboard-row">
                        <span class="dashboard-label">Verify Code:</span>
                        <span class="dashboard-value" style="font-family: monospace;">#{{ str_pad($receipt->id ?? $req->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>
            </div>

            <!-- Narratives / Purposes -->
            <div class="text-details-container">
                <!-- Requisition Narrative -->
                <div class="text-block-card">
                    <div class="text-block-title">
                        REQUISITION PURPOSE / JUSTIFICATION NARRATIVE
                    </div>
                    <p class="text-block-content">
                        "{{ $req->purpose }}"
                    </p>
                </div>

                <!-- Stores Remarks -->
                <div class="text-block-card" style="border-left-color: var(--success);">
                    <div class="text-block-title">
                        LOGISTICS DECISION EVALUATION & STORES REMARKS
                    </div>
                    <p class="text-block-content">
                        @if(!empty($req->admin_notes))
                            "{{ $req->admin_notes }}"
                        @else
                            "Requisition evaluation complete. Requested items were validated against central inventory stock balances and issued out under formal guidelines."
                        @endif
                    </p>
                </div>
            </div>

            <!-- Table breakdown -->
            <div class="table-section-title">
                Itemized Store Release & Allocation Breakdown
            </div>

            <table class="voucher-table">
                <thead>
                    <tr>
                        <th style="width: 7%; text-align: center;">S/N</th>
                        <th style="width: 15%;">Store Category</th>
                        <th style="width: 48%;">Line Item & Release Specifications</th>
                        <th style="width: 15%; text-align: right;">Qty Requested</th>
                        <th style="width: 15%; text-align: right;">Qty Released</th>
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
                            <td style="text-align: right; font-weight: 700;">
                                <div class="qty-val">{{ number_format($requested, 2) }}</div>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $item['unit'] ?? 'units' }}</div>
                            </td>
                            <td style="text-align: right;">
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

            <!-- Legal Verification Statement -->
            <div class="legal-declaration">
                <strong>INVENTORY RELEASE STATEMENT & CERTIFICATION:</strong><br>
                I hereby certify that the store items detailed in this voucher have been verified, approved, and released from the central logistics inventory database. The receiving department representative has inspected and verified the stock items as listed and confirmed physical receipt. These actions have been fully transacted and logged under formal procedures of the Logistics and Stores Division.
            </div>

            <!-- Signatures Section -->
            @php
                // Fetch originating department head user to get their signature
                $deptHeadUser = null;
                if (!empty($receipt->approved_by_dept_head)) {
                    $deptHeadUser = \App\Models\User::where('name', $receipt->approved_by_dept_head)->first();
                }

                // Fetch stores department head user
                $storesDeptHead = \App\Models\User::whereIn('role', ['Main Admin', 'Department Head'])
                    ->where(function($q) {
                        $q->where('department', 'Stores')->orWhere('department', 'Store');
                    })
                    ->where('is_active', true)
                    ->first();

                // Fetch head of stores user
                $storesHeadUser = null;
                if (!empty($receipt->approved_by_stores_head)) {
                    $storesHeadUser = \App\Models\User::where('name', $receipt->approved_by_stores_head)->first();
                }
                if (!$storesHeadUser) {
                    $storesHeadUser = \App\Models\User::where('role', 'Main Admin')->first();
                }
            @endphp
            <div class="signatures-section">
                <!-- Column 1: Originating Department -->
                <div class="signature-block">
                    <div class="signature-space">
                        <div style="height: 45px;"></div>
                    </div>
                    <div class="signature-line"></div>
                    <div class="signature-title">{{ $receipt->approved_by_dept_head }}</div>
                    <div class="signature-sub" style="font-weight: 800; margin-top: 4px;">Dept. Head ({{ $req->department }})</div>
                    <div class="signature-sub" style="font-size: 0.58rem; color: var(--text-muted); margin-top: 2px;">ORIGINATING DEPARTMENT (REQUESTER / RECEIVER)</div>
                </div>

                <!-- Column 2: Department Head (Stores) -->
                <div class="signature-block">
                    <div class="signature-space">
                        <div style="height: 45px;"></div>
                    </div>
                    <div class="signature-line"></div>
                    <div class="signature-title">{{ $storesDeptHead ? $storesDeptHead->name : '........................................' }}</div>
                    <div class="signature-sub" style="font-weight: 800; margin-top: 4px;">DEPT. HEAD (STORES)</div>
                    <div class="signature-sub" style="font-size: 0.58rem; color: var(--text-muted); margin-top: 2px;">AUTHORIZED STORES DIRECTOR</div>
                </div>

                <!-- Column 3: Head of Stores -->
                <div class="signature-block">
                    <div class="signature-space">
                        <div style="height: 45px;"></div>
                    </div>
                    <div class="signature-line"></div>
                    <div class="signature-title">{{ $receipt->approved_by_stores_head ?? 'Head of Stores' }}</div>
                    <div class="signature-sub" style="font-weight: 800; margin-top: 4px;">HEAD OF STORES</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Bar -->
    <div class="action-bar">
        <button onclick="window.history.back()" class="btn btn-secondary">
            Go Back
        </button>
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