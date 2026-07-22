<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRA #{{ str_pad($batch->id, 6, '0', STR_PAD_LEFT) }} - Received Advice</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            color: #000;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .sra-container {
            max-width: 900px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .coa-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            opacity: 0.08;
            pointer-events: none;
            z-index: 0;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .gov-text {
            text-align: center;
            flex-grow: 1;
        }
        .gov-text h1 {
            margin: 0;
            font-size: 18px;
            text-decoration: underline;
            letter-spacing: 2px;
        }
        .original-text {
            font-size: 12px;
            font-style: italic;
            text-align: right;
            width: 150px;
        }
        .sra-title-row {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        .stores-service {
            border-right: 2px solid #000;
            padding-right: 15px;
            font-weight: bold;
            font-size: 20px;
            line-height: 1;
        }
        .received-advice {
            font-weight: bold;
            font-size: 20px;
        }
        .sra-number {
            margin-left: auto;
            font-size: 24px;
            font-weight: bold;
            color: #000;
        }
        
        .top-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            border: 1px solid #000;
            margin-bottom: 20px;
        }
        .info-cell {
            border-right: 1px solid #000;
            padding: 8px;
            min-height: 50px;
        }
        .info-cell:last-child {
            border-right: none;
        }
        .info-label {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            display: block;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 16px;
            font-weight: bold;
        }

        .order-delivery-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            border: 1px solid #000;
            margin-bottom: 20px;
        }
        .order-details {
            border-right: 1px solid #000;
            padding: 10px;
        }
        .delivery-status {
            padding: 10px;
        }
        .order-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            border-bottom: 1px dotted #ccc;
            font-size: 13px;
        }
        .order-label {
            font-weight: bold;
        }
        
        .checkbox-row {
            display: flex;
            gap: 40px;
            margin-top: 15px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .box {
            width: 40px;
            height: 40px;
            border: 2px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 10px;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-size: 13px;
        }
        .main-table th {
            background: #f2f2f2;
            text-transform: uppercase;
            font-size: 11px;
            text-align: center;
        }
        .qty-header {
            text-align: center !important;
        }
        .qty-sub-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            text-align: center;
            margin: -8px;
        }
        .qty-sub-cell {
            padding: 8px;
            border-right: 1px solid #000;
        }
        .qty-sub-cell:last-child {
            border-right: none;
        }

        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            border: 1px solid #000;
        }
        .sig-cell {
            border-right: 1px solid #000;
            padding: 12px;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sig-cell:last-child {
            border-right: none;
        }
        .sig-top-label {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .sig-line {
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            height: 40px;
            text-align: center;
            font-family: "Dancing Script", cursive;
            font-size: 20px;
        }
        .sig-label {
            font-size: 11px;
            text-align: center;
            font-weight: bold;
        }
        .sig-name-date {
            font-size: 12px;
            margin-top: 10px;
        }
        .sig-name-date div {
            margin-bottom: 5px;
        }

        @media print {
            @page {
                size: A4;
                margin: 5mm;
            }
            body { 
                padding: 5mm; 
                margin: 0;
            }
            .print-btn, .review-banner, .swal2-container { display: none !important; }
            .sra-container { 
                border: 2px solid #000; 
                padding: 15px;
            }
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<body>
@if(auth()->check() && (auth()->user()->role === 'Auditor' || auth()->user()->role === 'Main Admin' || auth()->user()->role === 'Sub Main Admin') && $batch->approval_status === 'pending_auditor_admin')
    @php
        $role = auth()->user()->role;
        $hasApproved = ($role === 'Auditor' && $batch->auditor_status === 'approved') || (($role === 'Main Admin' || $role === 'Sub Main Admin') && $batch->admin_status === 'approved');
        $hasDeclined = ($role === 'Auditor' && $batch->auditor_status === 'declined') || (($role === 'Main Admin' || $role === 'Sub Main Admin') && $batch->admin_status === 'declined');
    @endphp
    
    <div class="review-banner" style="position: sticky; top: 0; left: 0; right: 0; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); color: #fff; padding: 15px 30px; display: flex; align-items: center; justify-content: space-between; z-index: 10000; box-shadow: 0 4px 20px rgba(0,0,0,0.3); border-bottom: 2px solid #334155; font-family: system-ui, -apple-system, sans-serif; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 42px; height: 42px; background: rgba(136, 19, 55, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(136, 19, 55, 0.4);">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9f1239" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-alert"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
            </div>
            <div style="text-align: left;">
                <div style="font-weight: 800; font-size: 0.95rem; letter-spacing: -0.01em;">SRA Receipt Review Board</div>
                <div style="font-size: 0.78rem; color: #94a3b8; font-weight: 500; margin-top: 2px;">
                    @if($hasApproved)
                        <span style="color: #881337; font-weight: bold;">✓ You have approved this SRA.</span> Waiting for the other review authority.
                    @elseif($hasDeclined)
                        <span style="color: #ef4444; font-weight: bold;">✗ You have declined this SRA.</span>
                    @else
                        This receipt requires your verification and approval to become active stock.
                    @endif
                </div>
            </div>
        </div>
        
        @if(!$hasApproved && !$hasDeclined)
            <div style="display: flex; gap: 12px;">
                <button onclick="processSraReviewAction('approve')" style="background: #881337; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 750; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(136, 19, 55, 0.25); transition: all 0.2s;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#881337'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Accept & Approve
                </button>
            </div>
        @endif
    </div>

    <!-- Include SweetAlert2 for beautiful alerts if not loaded -->
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>

    <script>
        function processSraReviewAction(action) {
            const batchId = "{{ $batch->id }}";
            const actionText = action === 'approve' ? 'approve' : 'decline';
            const actionColor = action === 'approve' ? '#881337' : '#ef4444';
            const confirmBtnText = action === 'approve' ? 'Yes, Approve' : 'Yes, Decline';
            
            Swal.fire({
                title: action === 'approve' ? 'Approve SRA Receipt?' : 'Decline SRA Receipt?',
                text: action === 'approve' 
                    ? 'Confirming this receipt will log your digital signature and move the items to live stock.' 
                    : 'Decline this receipt if there are discrepancies or issues. A reason is required.',
                icon: 'warning',
                input: action === 'decline' ? 'textarea' : null,
                inputPlaceholder: action === 'decline' ? 'Enter decline reason...' : null,
                inputValidator: action === 'decline' ? (value) => {
                    if (!value) {
                        return 'You must enter a reason to decline!';
                    }
                } : null,
                showCancelButton: true,
                confirmButtonColor: actionColor,
                cancelButtonColor: '#94a3b8',
                confirmButtonText: confirmBtnText
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing SRA...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    fetch(`/received-items/${batchId}/process-sra-review`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            action: action,
                            reason: action === 'decline' ? result.value : null
                        })
                    })
                    .then(async res => {
                        const isJson = res.headers.get('content-type')?.includes('application/json');
                        const data = isJson ? await res.json() : null;
                        
                        if (!res.ok) {
                            const errorMsg = data?.message || 'Server returned an error status';
                            throw new Error(errorMsg);
                        }
                        return data;
                    })
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'SRA Processed!',
                                text: data.message,
                                confirmButtonColor: '#881337'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Action Failed', data.message || 'Error processing review', 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', err.message || 'A network error occurred. Please check system logs.', 'error');
                    });
                }
            });
        }
    </script>
@endif

    @if($batch->id !== 'DRAFT')
    <button class="print-btn" onclick="window.print()">PRINT SRA</button>
    @endif

    <div class="sra-container">
        @if($batch->id === 'DRAFT' || $batch->approval_status !== 'approved')
        <div style="position: absolute; top: 10px; right: 10px; background: #ef4444; color: white; padding: 5px 15px; border-radius: 6px; font-weight: 800; font-size: 0.75rem; z-index: 100;">
            DRAFT: AWAITING APPROVAL
        </div>
        @endif
        <img src="{{ asset('img/COA.svg') }}" class="coa-watermark" alt="Watermark">
        <div class="header-top">
            <div style="width: 150px;"></div>
            <div class="gov-text">
                <h1>GHANA GOVERNMENT</h1>
            </div>
            <div class="original-text">
                Original<br>To Support Payment
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
                {{ str_pad($batch->id, 6, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <div class="top-info-grid">
            <div class="info-cell">
                <span class="info-label">DEPT.</span>
                <span class="info-value">Narcotics Control Commission</span>
            </div>
            <div class="info-cell">
                <span class="info-label">STATION</span>
                <span class="info-value">Accra</span>
            </div>
            <div class="info-cell">
                <span class="info-label">REGION</span>
                <span class="info-value">Greater Accra</span>
            </div>
            <div class="info-cell">
                <span class="info-label">DATE</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($batch->arrival_date ?: $batch->entry_date)->format('d/m/y') }}</span>
            </div>
        </div>

        <div class="order-delivery-grid">
            <div class="order-details">
                <span class="info-label">Details of Order</span>
                <div class="order-line">
                    <span class="order-label">A & E I No.</span>
                    <span>NACOC/INV/{{ $batch->id }}</span>
                </div>
                <div class="order-line">
                    <span class="order-label">L.P.O. No.</span>
                    <span>-</span>
                </div>
                <div class="order-line">
                    <span class="order-label">{{ $batch->acquisition_type === 'Donor' ? 'Donor:' : 'Supplier:' }}</span>
                    <span>{{ trim(preg_replace('/\[.*?\]/', '', ($batch->acquisition_type === 'Donor' ? ($batch->donor_name ?: $batch->supplier_name) : $batch->supplier_name) ?? 'N/A')) }}</span>
                </div>
                @if($batch->supplier_number ?? $batch->supplier_phone ?? null)
                <div class="order-line">
                    <span class="order-label">Supplier No.:</span>
                    <span>{{ $batch->supplier_number ?? $batch->supplier_phone ?? '—' }}</span>
                </div>
                @endif
                <div class="order-line">
                    <span class="order-label">Address:</span>
                    <span>NACOC Headquarters, Accra</span>
                </div>
                <div class="order-line">
                    <span class="order-label">Delivery Person:</span>
                    <span>{{ $batch->delivery_person ?: '______________________' }}</span>
                </div>
                <div class="order-line" style="border: none;">
                    <span class="order-label">Delivery No.:</span>
                    <span>{{ $batch->delivery_phone ?: '______________________' }}</span>
                </div>
            </div>
            <div class="delivery-status">
                <span class="info-label">Delivery/Performance,</span>
                <span class="info-label">Is this a full or part delivery? (tick)</span>
                
                @php
                    $isPartial = str_contains(strtolower($batch->supplier_status), 'partial');
                @endphp

                <div class="checkbox-row">
                    <div class="checkbox-item">
                        <span class="info-label" style="margin: 0;">FULL</span>
                        <div class="box">{{ !$isPartial ? '✓' : '' }}</div>
                    </div>
                    <div class="checkbox-item">
                        <span class="info-label" style="margin: 0;">PART</span>
                        <div class="box">{{ $isPartial ? '✓' : '' }}</div>
                    </div>
                </div>

                @if($isPartial)
                <div style="margin-top: 15px; font-size: 11px;">
                    <span class="info-label">If part delivery/Performance, indicate previous SRA Nos.</span>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px; margin-top: 5px;">
                        <span>1. __________</span>
                        <span>3. __________</span>
                        <span>2. __________</span>
                        <span>4. __________</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 50px;">Item</th>
                    <th>Details of Order / Service</th>
                    <th style="width: 120px;">Stores Vocabulary No.</th>
                    <th style="width: 210px;">
                        Quantity / Service
                        <div class="qty-sub-grid" style="margin-top: 5px; border-top: 1px solid #000;">
                            <div class="qty-sub-cell" style="font-size: 9px;">Ordered</div>
                            <div class="qty-sub-cell" style="font-size: 9px;">Received</div>
                            <div class="qty-sub-cell" style="font-size: 9px;">Balance</div>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($batch->items as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->description }}</strong>
                        @if(!empty($item->serial_number))
                        <div style="font-size: 11px; font-weight: bold; margin-top: 2px; color: #333;">
                            S/N: {{ $item->serial_number }}
                        </div>
                        @endif
                        @if($item->remarks)
                        <div style="font-size: 11px; font-style: italic; margin-top: 4px; color: #555;">
                            Note: {{ $item->remarks }}
                        </div>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $item->ledger_id ?? '-' }}</td>
                    <td>
                        <div class="qty-sub-grid">
                            <div class="qty-sub-cell">{{ number_format((float)$item->stock_balance - (float)$item->variance) }}</div>
                            <div class="qty-sub-cell" style="font-weight: bold;">{{ number_format((float)$item->stock_balance) }}</div>
                            <div class="qty-sub-cell">{{ $item->variance == 0 ? '-' : number_format(abs((float)$item->variance)) }}</div>
                        </div>
                    </td>
                </tr>
                @endforeach
                {{-- Fill empty rows --}}
                @for($i = count($batch->items); $i < 6; $i++)
                <tr>
                    <td style="height: 25px;"></td>
                    <td></td>
                    <td></td>
                    <td>
                        <div class="qty-sub-grid">
                            <div class="qty-sub-cell" style="height: 25px;"></div>
                            <div class="qty-sub-cell"></div>
                            <div class="qty-sub-cell"></div>
                        </div>
                    </td>
                </tr>
                @endfor
            </tbody>
        </table>

        @php
            $storesUser = $batch->storesApprover ?? $batch->approver ?? $batch->recorder;
            $storesDate = $batch->stores_approved_at ?: $batch->approved_at ?: $batch->created_at;
            
            $adminUser = $batch->adminApprover;
            $adminApproved = $batch->admin_status === 'approved' && $adminUser;
            
            $auditorUser = $batch->auditorApprover;
            $auditorApproved = $batch->auditor_status === 'approved' && $auditorUser;
        @endphp
        <div class="signatures-grid">
            <div class="sig-cell">
                <div class="sig-top-label">I certify that the service has been performed according to order.</div>
                <div class="sig-line" style="height: auto; min-height: 40px; text-align: center;">
                    @if($adminApproved && $adminUser->signature)
                        <img src="{{ asset('storage/' . $adminUser->signature) }}" style="max-height: 80px; object-fit: contain; vertical-align: middle; margin-bottom: -10px; transform: translateY(-5px);">
                    @else
                        &nbsp;
                    @endif
                </div>
                <div class="sig-label">Officer-in-Charge</div>
                <div class="sig-name-date">
                    <div>Name: {{ $adminApproved ? $adminUser->name : '______________________' }}</div>
                    <div>Date: {{ $adminApproved && $batch->admin_approved_at ? \Carbon\Carbon::parse($batch->admin_approved_at)->format('d/m/y') : '______________________' }}</div>
                </div>
            </div>
            <div class="sig-cell">
                <div class="sig-top-label">Taken on charge</div>
                <div class="sig-line" style="height: auto; min-height: 40px; text-align: center;">
                    @if($storesUser && $storesUser->signature)
                        <img src="{{ asset('storage/' . $storesUser->signature) }}" style="max-height: 80px; object-fit: contain; vertical-align: middle; margin-bottom: -10px; transform: translateY(-5px);">
                    @else
                        &nbsp;
                    @endif
                </div>
                <div class="sig-label">Storekeeper/Officer-in-Charge</div>
                <div class="sig-name-date">
                    <div>Name: {{ $storesUser->name ?? '______________________' }}</div>
                    <div>Date: {{ $storesDate ? \Carbon\Carbon::parse($storesDate)->format('d/m/y') : '______________________' }}</div>
                </div>
            </div>
            <div class="sig-cell">
                <div class="sig-top-label">Verified by</div>
                <div class="sig-line" style="height: auto; min-height: 40px; text-align: center;">
                    @if($auditorApproved && $auditorUser->signature)
                        <img src="{{ asset('storage/' . $auditorUser->signature) }}" style="max-height: 80px; object-fit: contain; vertical-align: middle; margin-bottom: -10px; transform: translateY(-5px);">
                    @else
                        &nbsp;
                    @endif
                </div>
                <div class="sig-label">Internal Audit/Stores Verifier</div>
                <div class="sig-name-date">
                    <div>Name: {{ $auditorApproved ? $auditorUser->name : '______________________' }}</div>
                    <div>Date: {{ $auditorApproved && $batch->auditor_approved_at ? \Carbon\Carbon::parse($batch->auditor_approved_at)->format('d/m/y') : '______________________' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin-top: 20px; font-size: 10px; color: #777;">
        System Generated Document - NACOC Inventory Management Core
    </div>
</body>
</html>
