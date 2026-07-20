<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRA Receipt Review Board – {{ $sra->sra_number }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --primary: #8b5cf6;
            --primary-hover: #7c3aed;
            --bg-dark: #0f172a;
            --bg-page: #f8fafc;
            --border-color: #e2e8f0;
            --text-muted: #64748b;
        }

        body {
            font-family: "Outfit", sans-serif;
            background: var(--bg-page);
            color: #0f172a;
            margin: 0;
            padding: 0;
        }

        /* Top Bar styling */
        .review-header {
            background: var(--bg-dark);
            color: #ffffff;
            padding: 0.85rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .icon-badge {
            width: 44px;
            height: 44px;
            background: rgba(139, 92, 246, 0.15);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        .header-title-area h2 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .header-title-area p {
            margin: 2px 0 0 0;
            font-size: 0.78rem;
            color: #94a3b8;
            font-weight: 400;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.6rem 1.4rem;
            font-size: 0.85rem;
            font-weight: 700;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
        }

        .btn-approve {
            background: var(--primary);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(139, 92, 246, 0.3);
        }

        .btn-approve:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
        }

        /* Document Container */
        .document-wrapper {
            padding: 2.5rem 1.5rem;
            display: flex;
            justify-content: center;
        }

        /* Paper styles */
        .sra-container {
            width: 100%;
            max-width: 820px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02);
            padding: 30px;
            position: relative;
            font-family: "Times New Roman", Times, serif;
            box-sizing: border-box;
        }

        .coa-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 480px;
            opacity: 0.07;
            pointer-events: none;
            z-index: 0;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }

        .gov-text {
            text-align: center;
            flex-grow: 1;
        }

        .gov-text h1 {
            margin: 0;
            font-size: 21px;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 2px;
        }

        .original-text-area {
            text-align: right;
            line-height: 1.3;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
        }

        .original-text {
            font-size: 11px;
            font-style: italic;
            font-weight: bold;
        }

        .draft-badge {
            background: #ef4444;
            color: #ffffff;
            font-family: "Outfit", sans-serif;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 99px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.25);
            white-space: nowrap;
        }

        .sra-title-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            position: relative;
            z-index: 1;
        }

        .stores-service {
            border-right: 2px solid #000;
            padding-right: 12px;
            font-weight: bold;
            font-size: 19px;
            line-height: 1.1;
        }

        .received-advice {
            font-weight: bold;
            font-size: 19px;
            letter-spacing: 0.5px;
        }

        .sra-number {
            margin-left: auto;
            font-size: 26px;
            font-weight: bold;
            color: #000;
            letter-spacing: 0.5px;
        }

        .top-info-grid {
            display: grid;
            grid-template-columns: 2.2fr 1fr 1fr 1.1fr;
            border: 2px solid #000;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .info-cell {
            border-right: 2px solid #000;
            padding: 5px 8px;
            min-height: 52px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .info-cell:last-child {
            border-right: none;
        }

        .info-label {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #333;
        }

        .info-value {
            font-size: 13.5px;
            font-weight: bold;
            margin-top: 3px;
        }

        .order-delivery-grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            border: 2px solid #000;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .order-details {
            border-right: 2px solid #000;
            padding: 10px 12px;
        }

        .delivery-status {
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .order-line {
            display: flex;
            align-items: baseline;
            margin-bottom: 6px;
        }

        .order-label {
            font-weight: bold;
            font-size: 12.5px;
            white-space: nowrap;
        }

        .order-dot {
            flex-grow: 1;
            border-bottom: 1px dotted #000;
            margin: 0 6px;
        }

        .order-val {
            font-weight: bold;
            font-size: 13px;
            white-space: nowrap;
        }

        .checkbox-row {
            display: flex;
            gap: 25px;
            margin-top: 8px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .box {
            width: 32px;
            height: 32px;
            border: 2px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            font-size: 13px;
        }

        .main-table th {
            background: #fff;
            text-transform: uppercase;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000;
        }

        .qty-header {
            text-align: center !important;
        }

        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            border: 2px solid #000;
            position: relative;
            z-index: 1;
            background: #fff;
        }

        .sig-cell {
            border-right: 2px solid #000;
            padding: 10px 12px;
            min-height: 110px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sig-cell:last-child {
            border-right: none;
        }

        .sig-top-label {
            font-size: 11.5px;
            font-weight: bold;
            font-style: italic;
            line-height: 1.3;
            margin-bottom: 12px;
        }

        .sig-line {
            border-bottom: 1px solid #000;
            margin-bottom: 4px;
            margin-top: 10px;
        }

        .sig-label {
            font-size: 11px;
            text-align: center;
            font-weight: bold;
        }

        .sig-name-date {
            font-size: 12px;
            margin-top: 6px;
        }

        .sig-name-date div {
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

    <!-- Header bar exactly like the image -->
    <div class="review-header">
        <div class="header-left">
            <div class="icon-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div class="header-title-area">
                <h2>SRA Receipt Review Board</h2>
                <p>This receipt requires your verification and approval to proceed in the pipeline.</p>
            </div>
        </div>
        <div class="header-actions">
            <button onclick="handleAuditorProcess('approved')" class="btn-action btn-approve">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Approve
            </button>
        </div>
    </div>

    <!-- Paper container centered -->
    <div class="document-wrapper">
        <div class="sra-container">
            <!-- Watermark -->
            <img src="{{ asset('img/COA.svg') }}" class="coa-watermark" alt="Watermark">

            <!-- Title Header -->
            <div class="header-top">
                <div style="width: 120px;"></div>
                <div class="gov-text">
                    <h1>GHANA GOVERNMENT</h1>
                </div>
                <div class="original-text-area">
                    <div class="draft-badge">Draft: Awaiting Approval</div>
                    <div class="original-text">
                        Original<br>To Support Payment
                    </div>
                </div>
            </div>

            <div class="sra-title-row">
                <div class="stores-service">
                    STORES<br>SERVICE
                </div>
                <div class="received-advice">
                    RECEIVED ADVICE (SRA)
                </div>
                <div class="sra-number">
                    {{ ltrim(str_replace('SRA-', '', $sra->sra_number), '0') ?: '0' }}
                </div>
            </div>

            <!-- Dept station region -->
            <div class="top-info-grid">
                <div class="info-cell">
                    <span class="info-label">DEPT.</span>
                    <span class="info-value">{{ strtoupper($sra->dept ?: 'Narcotics Control Commission') }}</span>
                </div>
                <div class="info-cell">
                    <span class="info-label">STATION</span>
                    <span class="info-value">{{ $sra->station ?: 'Accra' }}</span>
                </div>
                <div class="info-cell">
                    <span class="info-label">REGION</span>
                    <span class="info-value">{{ $sra->region ?: 'Greater Accra' }}</span>
                </div>
                <div class="info-cell">
                    <span class="info-label">DATE</span>
                    <span class="info-value">{{ $sra->date_of_delivery->format('d/m/y') }}</span>
                </div>
            </div>

            <!-- Supplier Address Order details -->
            <div class="order-delivery-grid">
                <div class="order-details">
                    <span class="info-label" style="display: block; margin-bottom: 8px;">Details of Order</span>
                    
                    <div class="order-line">
                        <span class="order-label">A & E I No.</span>
                        <span class="order-dot"></span>
                        <span class="order-val">{{ $sra->ae_number ?: '-' }}</span>
                    </div>
                    <div class="order-line">
                        <span class="order-label">L.P.O. No.</span>
                        <span class="order-dot"></span>
                        <span class="order-val">{{ $sra->lpo_number ?: '-' }}</span>
                    </div>
                    <div class="order-line">
                        <span class="order-label">Vehicle Licence No.</span>
                        <span class="order-dot"></span>
                        <span class="order-val">{{ $sra->vehicle_number ?: '-' }}</span>
                    </div>
                    <div class="order-line">
                        <span class="order-label">Supplier:</span>
                        <span class="order-dot"></span>
                        <span class="order-val">{{ $sra->supplier_name }}</span>
                    </div>
                    <div class="order-line" style="margin-bottom: 0;">
                        <span class="order-label">Address:</span>
                        <span class="order-dot"></span>
                        <span class="order-val">{{ $sra->supplier_address ?: '-' }}</span>
                    </div>
                </div>

                <div class="delivery-status">
                    <div>
                        <span class="info-label" style="display: block; line-height: 1.3;">Delivery/Performance,<br>Is this a full or part delivery? (tick)</span>
                        <div class="checkbox-row">
                            <div class="checkbox-item">
                                <span class="info-label" style="margin: 0;">FULL</span>
                                <div class="box">{{ $sra->delivery_type === 'full' ? '✓' : '' }}</div>
                            </div>
                            <div class="checkbox-item">
                                <span class="info-label" style="margin: 0;">PART</span>
                                <div class="box">{{ $sra->delivery_type === 'partial' ? '✓' : '' }}</div>
                            </div>
                        </div>
                    </div>

                    @if($sra->delivery_type === 'partial')
                    <div style="margin-top: 10px; font-size: 11px;">
                        <span class="info-label" style="display: block; line-height: 1.2; margin-bottom: 4px;">If part delivery/Performance, indicate previous SRA Nos.</span>
                        @php
                            $prevNos = array_filter(array_map('trim', preg_split('/[\n,]+/', $sra->previous_sra_nos ?? '')));
                            $prevNos = array_values($prevNos);
                        @endphp
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px;">
                            <span>1. <strong>{{ $prevNos[0] ?? '_______' }}</strong></span>
                            <span>3. <strong>{{ $prevNos[2] ?? '_______' }}</strong></span>
                            <span>2. <strong>{{ $prevNos[1] ?? '_______' }}</strong></span>
                            <span>4. <strong>{{ $prevNos[3] ?? '_______' }}</strong></span>
                        </div>
                    </div>
                    @else
                    <div style="margin-top: 10px; font-size: 11px; opacity: 0.65;">
                        <span class="info-label" style="display: block; line-height: 1.2; margin-bottom: 4px;">If part delivery/Performance, indicate previous SRA Nos.</span>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px;">
                            <span>1. _______</span>
                            <span>3. _______</span>
                            <span>2. _______</span>
                            <span>4. _______</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Items Table -->
            <table class="main-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 50px; border: 1px solid #000; padding: 6px;">Item</th>
                        <th rowspan="2" style="border: 1px solid #000; padding: 6px; text-align: left;">Details of Order / Service</th>
                        <th rowspan="2" style="width: 140px; border: 1px solid #000; padding: 6px;">Stores Vocabulary No.</th>
                        <th colspan="3" style="border: 1px solid #000; padding: 6px; text-align: center;">Quantity / Service</th>
                    </tr>
                    <tr>
                        <th style="width: 70px; border: 1px solid #000; padding: 4px; text-align: center; font-size: 9px; font-weight: bold; background: #fff;">Ordered</th>
                        <th style="width: 70px; border: 1px solid #000; padding: 4px; text-align: center; font-size: 9px; font-weight: bold; background: #fff;">Received</th>
                        <th style="width: 70px; border: 1px solid #000; padding: 4px; text-align: center; font-size: 9px; font-weight: bold; background: #fff;">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $detailLines = array_values(array_filter(array_map('trim', explode("\n", $sra->details))));
                        $totalLines  = max(count($detailLines), 10);
                    @endphp
                    @for($i = 0; $i < $totalLines; $i++)
                    <tr>
                        <td style="text-align: center; font-weight: bold; vertical-align: top; padding-top: 6px; border: 1px solid #000;">
                            {{ isset($detailLines[$i]) ? ($i + 1) : '' }}
                        </td>
                        <td style="vertical-align: top; padding-top: 6px; min-height: 25px; border: 1px solid #000;">
                            <strong>{{ $detailLines[$i] ?? '' }}</strong>
                        </td>
                        <td style="text-align: center; vertical-align: top; padding-top: 6px; border: 1px solid #000;">
                            {{ isset($detailLines[$i]) ? '-' : '' }}
                        </td>
                        <td style="text-align: center; vertical-align: top; padding-top: 6px; border: 1px solid #000; width: 70px;">
                            {{ isset($detailLines[$i]) ? '-' : '' }}
                        </td>
                        <td style="text-align: center; vertical-align: top; padding-top: 6px; border: 1px solid #000; width: 70px; font-weight: bold;">
                            {{ isset($detailLines[$i]) ? '-' : '' }}
                        </td>
                        <td style="text-align: center; vertical-align: top; padding-top: 6px; border: 1px solid #000; width: 70px;">
                            {{ isset($detailLines[$i]) ? '-' : '' }}
                        </td>
                    </tr>
                    @endfor
                </tbody>
            </table>

            <!-- Signatures -->
            @php
                $adminUser = \App\Models\User::where('name', $sra->admin_approved_by)->first();
                $storesUser = \App\Models\User::where('name', $sra->stores_approved_by)->first();
            @endphp
            <div class="signatures-grid">
                <div class="sig-cell">
                    <div class="sig-top-label">I certify that the service has been performed according to order.</div>
                    <div>
                        <div class="sig-line" style="text-align: center;">
                            @if($adminUser && $adminUser->signature)
                                <img src="{{ asset('storage/' . $adminUser->signature) }}" style="max-height: 95px; object-fit: contain; vertical-align: middle; margin-bottom: -20px; transform: translateY(12px);">
                            @endif
                        </div>
                        <div class="sig-label">Officer-in-Charge</div>
                        <div class="sig-name-date">
                            <div><strong>Name:</strong> {{ $sra->admin_approved_by ?: '____________________' }}</div>
                            <div><strong>Date:</strong> {{ $sra->admin_approved_at ? $sra->admin_approved_at->format('d/m/y') : '____________________' }}</div>
                        </div>
                    </div>
                </div>

                <div class="sig-cell">
                    <div class="sig-top-label" style="text-align: center;">Taken on charge</div>
                    <div>
                        <div class="sig-line" style="text-align: center;">
                            @if($storesUser && $storesUser->signature)
                                <img src="{{ asset('storage/' . $storesUser->signature) }}" style="max-height: 95px; object-fit: contain; vertical-align: middle; margin-bottom: -20px; transform: translateY(12px);">
                            @endif
                        </div>
                        <div class="sig-label">Storekeeper/Officer-in-Charge</div>
                        <div class="sig-name-date">
                            <div><strong>Name:</strong> {{ $sra->stores_approved_by ?: '____________________' }}</div>
                            <div><strong>Date:</strong> {{ $sra->stores_approved_at ? $sra->stores_approved_at->format('d/m/y') : '____________________' }}</div>
                        </div>
                    </div>
                </div>

                <div class="sig-cell">
                    <div class="sig-top-label" style="text-align: center;">Verified by</div>
                    <div>
                        <div class="sig-line" style="text-align: center;">
                            @php
                                $auditorUser = null;
                                if ($sra->auditor_status === 'approved' && $sra->auditor_approved_by) {
                                    $auditorUser = \App\Models\User::where('name', $sra->auditor_approved_by)->first();
                                }
                            @endphp
                            @if($auditorUser && $auditorUser->signature)
                                <img src="{{ asset('storage/' . $auditorUser->signature) }}" style="max-height: 95px; object-fit: contain; vertical-align: middle; margin-bottom: -20px; transform: translateY(12px);">
                            @endif
                        </div>
                        <div class="sig-label">Internal Audit/Stores Verifier</div>
                        <div class="sig-name-date">
                            <div><strong>Name:</strong> {{ $sra->auditor_approved_by ?: '____________________' }}</div>
                            <div><strong>Date:</strong> {{ $sra->auditor_approved_at ? $sra->auditor_approved_at->format('d/m/y') : '____________________' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script to handle auditor process action -->
    <script>
        function handleAuditorProcess(action) {
            Swal.fire({
                title: 'Approve SRA?',
                text: 'This will verify and auditor-approve the Service SRA.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Approve',
                confirmButtonColor: '#8b5cf6',
                cancelButtonColor: '#64748b',
            }).then(result => {
                if (!result.isConfirmed) return;

                const notes = '';

                // Show processing loading dialog
                Swal.fire({
                    title: 'Processing SRA...',
                    html: 'Please wait a moment.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(`/auditor/service-sra/{{ $sra->id }}/process`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ action, notes }),
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Approved!',
                            text: res.message,
                            timer: 2500,
                            showConfirmButton: false,
                        }).then(() => {
                            // Close tab or redirect back
                            window.close();
                            // Fallback if window.close doesn't work (due to browser security)
                            setTimeout(() => {
                                window.location.href = '/auditor';
                            }, 500);
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({ icon: 'error', title: 'Network Error', text: 'Please try again.' });
                });
            });
        }
    </script>
</body>
</html>
