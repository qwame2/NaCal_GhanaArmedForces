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
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 50px;
        }
        .sig-block {
            text-align: center;
        }
        .sig-line {
            border-bottom: 1px solid #000;
            margin-bottom: 8px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
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
            .print-btn, .review-banner, .swal2-container { display: none !important; }
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
                <div style="font-weight: 800; font-size: 0.95rem; letter-spacing: -0.01em;">Donation SRA Review Board</div>
                <div style="font-size: 0.78rem; color: #94a3b8; font-weight: 500; margin-top: 2px;">
                    @if($hasApproved)
                        <span style="color: #881337; font-weight: bold;">✓ You have approved this Donation.</span> Waiting for the other review authority.
                    @elseif($hasDeclined)
                        <span style="color: #ef4444; font-weight: bold;">✗ You have declined this Donation.</span>
                    @else
                        This certificate requires your verification and approval to become active stock.
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
                <strong>{{ \Carbon\Carbon::parse($batch->arrival_date ?: $batch->entry_date)->format('d/m/y') }}</strong>
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
                    <th style="width: 100px;">PACKAGE TYPE</th>
                    <th style="width: 120px;">QUANTITY</th>
                </tr>
            </thead>
            <tbody>
                @foreach($batch->items as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->description }}</strong>
                        @if(!empty($item->serial_number))
                            <br><span style="font-size: 11px; font-weight: bold; color: #444;">S/N: {{ $item->serial_number }}</span>
                        @endif
                        @if($item->remarks)
                        <br><span style="font-size: 11px; font-style: italic;">Condition Note: {{ $item->remarks }}</span>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $item->unit }}</td>
                    <td style="text-align: center; font-weight: bold; font-size: 16px;">{{ number_format((float)($item->qty ?? 0)) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="value-disclaimer">
            NOTICE: As a philanthropic contribution, these items are recorded with zero commercial liability to the Ghana Government. 
            The valuation is based on institutional utility and recorded for inventory transparency and audit compliance only.
        </div>

        @php
            $storesUser = $batch->storesApprover ?? $batch->approver ?? $batch->recorder;
            $storesDate = $batch->stores_approved_at ?: $batch->approved_at ?: $batch->created_at;
            
            $adminUser = $batch->adminApprover;
            $adminApproved = $batch->admin_status === 'approved' && $adminUser;
            
            $auditorUser = $batch->auditorApprover;
            $auditorApproved = $batch->auditor_status === 'approved' && $auditorUser;
        @endphp
        <div class="signatures">
            <div class="sig-block">
                <div class="sig-line" style="height: auto; min-height: 40px; text-align: center;">
                    @if($adminApproved && $adminUser->signature)
                        <img src="{{ asset('storage/' . $adminUser->signature) }}" style="max-height: 50px; object-fit: contain; vertical-align: middle; margin-bottom: -10px; transform: translateY(-5px);">
                    @endif
                </div>
                <div class="sig-label">OFFICER-IN-CHARGE</div>
                <div class="sig-sub">(Acceptance Authority)</div>
                <div style="margin-top: 10px; font-weight: bold;">{{ $adminApproved ? $adminUser->name : '________________' }}</div>
                <div style="font-size: 11px; margin-top: 4px;">Date: {{ $adminApproved && $batch->admin_approved_at ? \Carbon\Carbon::parse($batch->admin_approved_at)->format('d/m/y') : '________________' }}</div>
            </div>
            <div class="sig-block">
                <div class="sig-line" style="height: auto; min-height: 40px; text-align: center;">
                    @if($storesUser && $storesUser->signature)
                        <img src="{{ asset('storage/' . $storesUser->signature) }}" style="max-height: 50px; object-fit: contain; vertical-align: middle; margin-bottom: -10px; transform: translateY(-5px);">
                    @endif
                </div>
                <div class="sig-label">STOREKEEPER / VERIFIER</div>
                <div class="sig-sub">(Inventory Registry)</div>
                <div style="margin-top: 10px; font-weight: bold;">{{ $storesUser->name ?? '________________' }}</div>
                <div style="font-size: 11px; margin-top: 4px;">Date: {{ $storesDate ? \Carbon\Carbon::parse($storesDate)->format('d/m/y') : '________________' }}</div>
            </div>
            <div class="sig-block">
                <div class="sig-line" style="height: auto; min-height: 40px; text-align: center;">
                    @if($auditorApproved && $auditorUser->signature)
                        <img src="{{ asset('storage/' . $auditorUser->signature) }}" style="max-height: 50px; object-fit: contain; vertical-align: middle; margin-bottom: -10px; transform: translateY(-5px);">
                    @endif
                </div>
                <div class="sig-label">INTERNAL AUDIT</div>
                <div class="sig-sub">(Audit Verification)</div>
                <div style="margin-top: 10px; font-weight: bold;">{{ $auditorApproved ? $auditorUser->name : '________________' }}</div>
                <div style="font-size: 11px; margin-top: 4px;">Date: {{ $auditorApproved && $batch->auditor_approved_at ? \Carbon\Carbon::parse($batch->auditor_approved_at)->format('d/m/y') : '________________' }}</div>
            </div>
        </div>

        <div style="margin-top: 40px; text-align: center; font-size: 11px; border-top: 1px solid #eee; padding-top: 20px;">
            OFFICIAL STAMP & DATE OF RECORDING: {{ date('d/m/y H:i') }}
        </div>
    </div>

    <div style="text-align: center; margin-top: 20px; font-size: 10px; color: #777;">
        System Generated Donation Acceptance Receipt - Narcotics Control Commission CORE
    </div>
</body>
</html>
