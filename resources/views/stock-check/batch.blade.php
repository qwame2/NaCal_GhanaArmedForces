@extends('layouts.dashboard')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<div class="animate-slide-up">
    <!-- Page Header -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(22, 163, 74, 0.1); color: #16a34a; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">Logistics Audit</span>
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main);">Batch Stock <span style="color: var(--primary);">Verification</span></h2>
            <p style="color: var(--text-muted);">Perform physical inventory checks for multiple items simultaneously in a full-screen ledger.</p>
        </div>

        <div>
            <a href="{{ route('stockcheck.index') }}" class="glass-card" style="padding: 0.75rem 1.25rem; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--text-main);">
                <i data-lucide="arrow-left" style="width: 18px;"></i>
                Back to Stock Check
            </a>
        </div>
    </div>

    <!-- Batch Verification Sheet -->
    <div class="glass-card" style="padding: 2rem; border-radius: 28px; background: var(--bg-card); border: 1px solid var(--border-color); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15); margin-bottom: 2rem;">
        <form id="batchVerifyForm" onsubmit="event.preventDefault(); submitBatchStockCheck();" style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <div style="overflow-x: auto; border-radius: 16px; border: 1px solid var(--border-color); background: var(--bg-main); margin-bottom: 1.5rem;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: rgba(0,0,0,0.03); border-bottom: 2px solid var(--border-color); height: 50px;">
                            <th style="padding: 1rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Item Description</th>
                            <th style="padding: 1rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; width: 150px;">Stock Balance</th>
                            <th style="padding: 1rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; width: 160px;">On Temp Loan</th>
                            <th style="padding: 1rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; width: 160px;">Physical Count</th>
                            <th style="padding: 1rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; width: 120px;">Variance</th>
                            <th style="padding: 1rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; width: 180px;">Condition</th>
                            <th style="padding: 1rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Verifier Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="batchVerifyTableBody">
                        @foreach($items as $item)
                        @php
                            $onLoanCount = $loans->where('item_desc', $item->description)->sum('quantity');
                        @endphp
                        <tr class="batch-verify-row" data-desc="{{ $item->description }}" style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1.25rem 1rem; font-weight: 700; color: var(--text-main); font-size: 0.95rem;">
                                {{ $item->description }}
                            </td>
                            <td style="padding: 1.25rem 1rem; font-weight: 800; color: var(--text-main); font-size: 1rem;">
                                {{ number_format($item->total_available) }} <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">{{ $item->unit ?: 'units' }}</span>
                            </td>
                            <td style="padding: 1.25rem 1rem; vertical-align: middle;">
                                @if($onLoanCount > 0)
                                    <span style="font-size: 0.75rem; font-weight: 800; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 0.25rem 0.6rem; border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.2);">
                                        {{ number_format($onLoanCount) }} on loan
                                    </span>
                                @else
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">None</span>
                                @endif
                            </td>
                            <td style="padding: 0.5rem 1rem;">
                                <input type="number" class="batch-physical-count" required min="0" placeholder="0" oninput="calculateBatchRowVariance(this, {{ $item->total_available }})" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-card); color: var(--text-main); font-weight: 800; font-size: 1.1rem; text-align: center; outline: none;">
                            </td>
                            <td style="padding: 1.25rem 1rem;">
                                <div class="batch-variance-display" style="font-size: 1.1rem; font-weight: 900; color: #94a3b8; text-align: center;">--</div>
                            </td>
                            <td style="padding: 0.5rem 1rem;">
                                <select class="batch-condition" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-card); color: var(--text-main); font-family: inherit; font-size: 0.85rem; font-weight: 600; outline: none;">
                                    <option value="">Select...</option>
                                    <option value="Match">Match</option>
                                    <option value="Missing">Missing</option>
                                    <option value="Damaged">Damaged</option>
                                    <option value="Found">Found</option>
                                    <option value="Other">Other</option>
                                </select>
                            </td>
                            <td style="padding: 0.5rem 1rem;">
                                <textarea class="batch-remarks" placeholder="Remarks..." style="width: 100%; height: 38px; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-card); color: var(--text-main); font-family: inherit; font-size: 0.85rem; resize: none; outline: none;"></textarea>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Active Loans details -->
            @if($loans->count() > 0)
            <div id="batchLoansContainer" style="margin-top: 1rem;">
                <h4 style="font-size: 1.1rem; font-weight: 800; color: #10b981; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="info" style="width: 18px; height: 18px;"></i>
                    Active Temporary Loans for Selected Assets
                </h4>
                <div style="overflow-x: auto; max-height: 350px; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-main); margin-bottom: 1.5rem;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.82rem;">
                        <thead>
                            <tr style="background: rgba(16, 185, 129, 0.05); border-bottom: 1.5px solid var(--border-color); height: 40px;">
                                <th style="padding: 0.75rem 1rem; color: var(--text-muted); font-weight: 700;">Asset Item</th>
                                <th style="padding: 0.75rem 1rem; color: var(--text-muted); font-weight: 700;">Borrower / Beneficiary</th>
                                <th style="padding: 0.75rem 1rem; color: var(--text-muted); font-weight: 700;">Qty Out</th>
                                <th style="padding: 0.75rem 1rem; color: var(--text-muted); font-weight: 700;">Date Issued</th>
                                <th style="padding: 0.75rem 1rem; color: var(--text-muted); font-weight: 700;">Expected Return Date</th>
                                <th style="padding: 0.75rem 1rem; color: var(--text-muted); font-weight: 700;">Authority</th>
                                <th style="padding: 0.75rem 1rem; color: var(--text-muted); font-weight: 700; text-align: center;">Overdue Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loans as $loan)
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-main);">{{ $loan->item_desc }}</td>
                                <td style="padding: 0.75rem 1rem; color: var(--text-main); font-weight: 600;">{{ $loan->beneficiary }} <span style="font-size: 0.75rem; color: var(--text-muted);">({{ $loan->department ?? 'N/A' }})</span></td>
                                <td style="padding: 0.75rem 1rem; font-weight: 800; color: var(--text-main);">{{ $loan->quantity }} <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">{{ $loan->unit }}</span></td>
                                <td style="padding: 0.75rem 1rem; color: var(--text-muted);">{{ \Carbon\Carbon::parse($loan->issuance_date)->format('d/m/Y') }}</td>
                                <td style="padding: 0.75rem 1rem; color: {{ $loan->is_overdue ? '#ef4444' : 'var(--text-main)' }}; font-weight: 700;">{{ $loan->expected_return_date }}</td>
                                <td style="padding: 0.75rem 1rem; color: var(--text-muted); font-size: 0.78rem;">{{ $loan->authority ?? 'N/A' }}</td>
                                <td style="padding: 0.75rem 1rem; text-align: center;">
                                    @if($loan->is_overdue)
                                        <span style="font-size: 0.7rem; font-weight: 800; color: #ef4444; background: rgba(239, 68, 68, 0.1); padding: 0.2rem 0.5rem; border-radius: 4px; border: 1px solid rgba(239, 68, 68, 0.2); text-transform: uppercase;">Overdue</span>
                                    @else
                                        <span style="font-size: 0.7rem; font-weight: 800; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 0.2rem 0.5rem; border-radius: 4px; border: 1px solid rgba(16, 185, 129, 0.2); text-transform: uppercase;">Active</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Submit / Action buttons -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                @if(auth()->user()->role === 'Auditor')
                <button type="button" onclick="generateBatchVerificationReport()" class="btn-primary" style="width: 100%; padding: 1.1rem; border-radius: 18px; border: none; background: linear-gradient(135deg, #16a34a 0%, #16a34a 100%) !important; color: white !important; font-weight: 900 !important; font-size: 1.05rem; cursor: pointer; box-shadow: 0 10px 20px rgba(22, 163, 74, 0.2); display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i data-lucide="printer" style="width: 20px; color: white !important;"></i>
                    <span style="color: white !important;">Print Batch PDF</span>
                </button>
                @else
                <button type="submit" class="btn-primary" style="width: 100%; padding: 1.1rem; border-radius: 18px; border: none; background: linear-gradient(135deg, #16a34a 0%, #16a34a 100%) !important; color: white !important; font-weight: 900 !important; font-size: 1.05rem; cursor: pointer; box-shadow: 0 10px 20px rgba(22, 163, 74, 0.2); display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i data-lucide="shield-check" style="width: 20px; color: white !important;"></i>
                    <span style="color: white !important;">
                        @if(auth()->user()->is_admin)
                            Seal Batch Records
                        @else
                            Submit Batch for Admin Approval
                        @endif
                    </span>
                </button>
                @endif
                <a href="{{ route('stockcheck.index') }}" class="glass-btn" style="width: 100%; padding: 1.1rem; border-radius: 18px; font-weight: 800; background: #1e293b; color: #fff; border: none; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                    Cancel Batch
                </a>
            </div>

        </form>
    </div>
</div>

<script>
const isAdmin = {{ auth()->user()->is_admin ? 'true' : 'false' }};

function calculateBatchRowVariance(inputEl, stock) {
    const physical = parseFloat(inputEl.value);
    const varDisplay = inputEl.closest('tr').querySelector('.batch-variance-display');

    if (isNaN(physical)) {
        varDisplay.innerText = '--';
        varDisplay.style.color = '#94a3b8';
        return;
    }

    const variance = physical - stock;
    varDisplay.innerText = (variance > 0 ? '+' : '') + variance;
    varDisplay.style.color = variance === 0 ? '#10b981' : (variance > 0 ? '#4ade80' : '#ef4444');
}

function submitBatchStockCheck() {
    const rows = document.querySelectorAll('.batch-verify-row');
    const batchData = [];
    let isValid = true;

    rows.forEach(row => {
        const description = row.getAttribute('data-desc');
        const physical_count = row.querySelector('.batch-physical-count').value;
        const condition = row.querySelector('.batch-condition').value;
        const remarks = row.querySelector('.batch-remarks').value;

        if (!physical_count || physical_count === '') {
            showToast('Validation Error', `Please specify physical count for ${description}.`, 'error');
            isValid = false;
            return;
        }

        if (!condition) {
            showToast('Validation Error', `Please select verification condition for ${description}.`, 'error');
            isValid = false;
            return;
        }

        batchData.push({
            description: description,
            physical_count: parseInt(physical_count, 10),
            condition: condition,
            remarks: remarks
        });
    });

    if (!isValid) return;

    const submitBtn = document.querySelector('#batchVerifyForm button[type="submit"]');
    const originalHtml = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<div class="loader" style="width: 14px; height: 14px; border-width: 2px; border-color: white;"></div> ${isAdmin ? 'Sealing Batch...' : 'Submitting Request...'}`;
    }

    fetch("{{ route('stockcheck.verify-batch') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ items: batchData })
    })
    .then(res => {
        if (!res.ok) throw new Error('Server protocol violation');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Batch Verification Submitted',
                text: data.message,
                icon: 'success',
                confirmButtonColor: 'var(--primary)'
            }).then(() => {
                window.location.href = "{{ route('stockcheck.index') }}";
            });
        } else {
            showToast('Batch Verification Failed', data.message || 'An unexpected error occurred.', 'error');
        }
    })
    .catch(err => {
        showToast('Network Error', 'Could not establish connection to logistics servers.', 'error');
    })
    .finally(() => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        }
    });
}

function generateBatchVerificationReport() {
    const rows = document.querySelectorAll('.batch-verify-row');
    const batchItems = [];
    rows.forEach(row => {
        const desc = row.getAttribute('data-desc');
        const stockText = row.querySelector('td:nth-child(2)').innerText;
        const stock = parseFloat(stockText) || 0;
        const physical = parseFloat(row.querySelector('.batch-physical-count').value) || 0;
        const variance = row.querySelector('.batch-variance-display').innerText || '0';
        const condition = row.querySelector('.batch-condition').value || 'Match';
        const remarks = row.querySelector('.batch-remarks').value || '';
        batchItems.push({ desc, stock, physical, variance, condition, remarks });
    });

    const verifierName = @json(auth()->user()->name);
    const verifierRole = @json(auth()->user()->role);
    const verifierRank = @json(auth()->user()->rank ?: 'N/A');
    const dateStr = new Date().toLocaleString('en-GB', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });

    const logoImg = new Image();
    logoImg.src = "{{ asset('img/NACOC.png') }}";

    const buildPdf = (imgEl) => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');

        // Brand Colors
        const primaryColor = [30, 58, 138];
        const slateDark = [15, 23, 42];
        const textMuted = [100, 116, 139];
        const bgLight = [248, 250, 252];
        const borderLight = [226, 232, 240];

        // Outline Border
        doc.setDrawColor(...borderLight);
        doc.setLineWidth(0.3);
        doc.rect(10, 6, 190, 280);

        // Top Accent Line
        doc.setFillColor(...primaryColor);
        doc.rect(10, 6, 190, 3, 'F');

        // Logo
        if (imgEl) {
            doc.addImage(imgEl, 'PNG', 168, 12, 22, 22);
        }

        // Header Section
        doc.setFillColor(...primaryColor);
        doc.rect(15, 12, 2, 22, 'F');

        doc.setFont("Helvetica", "bold");
        doc.setFontSize(8.5);
        doc.setTextColor(...primaryColor);
        doc.text("NARCOTICS CONTROL COMMISSION · NACOC", 20, 16);

        doc.setFontSize(18);
        doc.setTextColor(...slateDark);
        doc.text("Batch Stock Verification Report", 20, 24);
        
        doc.setFont("Helvetica", "normal");
        doc.setFontSize(9);
        doc.setTextColor(...textMuted);
        doc.text("NaCal Stores Logistics & Audit Control Hub", 20, 30);

        // Horizontal Separator
        doc.setDrawColor(...borderLight);
        doc.setLineWidth(0.5);
        doc.line(15, 38, 195, 38);

        // Verifier Details
        doc.setFont("Helvetica", "bold");
        doc.setFontSize(9.5);
        doc.setTextColor(...primaryColor);
        doc.text("1. VERIFIER DETAILS", 15, 45);

        doc.setFillColor(...bgLight);
        doc.roundedRect(15, 49, 180, 24, 2, 2, 'F');
        doc.setDrawColor(...borderLight);
        doc.roundedRect(15, 49, 180, 24, 2, 2, 'S');

        doc.setFontSize(8.5);
        doc.setTextColor(...slateDark);
        doc.setFont("Helvetica", "bold");
        doc.text("Verifier Name:", 20, 54);
        doc.setFont("Helvetica", "normal");
        doc.text(`${verifierRank} ${verifierName}`, 42, 54);

        doc.setFont("Helvetica", "bold");
        doc.text("Verifier Role:", 20, 60);
        doc.setFont("Helvetica", "normal");
        doc.text(verifierRole, 42, 60);

        doc.setFont("Helvetica", "bold");
        doc.text("Date & Time:", 20, 66);
        doc.setFont("Helvetica", "normal");
        doc.text(dateStr, 42, 66);

        doc.setFont("Helvetica", "bold");
        doc.text("Report Code:", 110, 54);
        doc.setFont("Helvetica", "normal");
        doc.text(`BVR-${Math.floor(100000 + Math.random() * 900000)}`, 135, 54);

        doc.setFont("Helvetica", "bold");
        doc.text("Report Status:", 110, 60);
        doc.setFont("Helvetica", "normal");
        doc.text("Pending Review", 135, 60);

        // Batch Items Section
        doc.setFont("Helvetica", "bold");
        doc.setFontSize(9.5);
        doc.setTextColor(...primaryColor);
        doc.text("2. BATCH VERIFICATION RESULTS", 15, 83);

        // Table Header
        doc.setFillColor(...primaryColor);
        doc.rect(15, 87, 180, 7, 'F');
        doc.setFontSize(8);
        doc.setTextColor(255, 255, 255);
        doc.setFont("Helvetica", "bold");
        doc.text("Item Description", 18, 91.5);
        doc.text("Sys Bal", 90, 91.5);
        doc.text("Phys Count", 110, 91.5);
        doc.text("Variance", 130, 91.5);
        doc.text("Condition", 150, 91.5);

        let currentY = 94;
        doc.setTextColor(...slateDark);
        batchItems.forEach((row, i) => {
            currentY = 94 + (i * 8.5);
            // Zebra striping
            if (i % 2 === 1) {
                doc.setFillColor(...bgLight);
                doc.rect(15, currentY, 180, 8.5, 'F');
            }
            // Border
            doc.setDrawColor(...borderLight);
            doc.rect(15, currentY, 180, 8.5, 'S');

            doc.setFont("Helvetica", "bold");
            doc.setFontSize(8);
            doc.text(row.desc, 18, currentY + 5.5);
            doc.setFont("Helvetica", "normal");
            doc.text(String(row.stock), 90, currentY + 5.5);
            doc.text(String(row.physical), 110, currentY + 5.5);
            
            // Variance formatting
            const varNum = parseFloat(row.variance);
            if (varNum > 0) {
                doc.setTextColor(16, 185, 129);
            } else if (varNum < 0) {
                doc.setTextColor(239, 68, 68);
            } else {
                doc.setTextColor(148, 163, 184);
            }
            doc.text(row.variance, 130, currentY + 5.5);
            doc.setTextColor(...slateDark);

            doc.text(row.condition, 150, currentY + 5.5);
        });

        // Signatures
        currentY += 45;
        if (currentY > 260) {
            doc.addPage();
            currentY = 40;
            doc.setDrawColor(...borderLight);
            doc.setLineWidth(0.3);
            doc.rect(10, 6, 190, 280);
            doc.setFillColor(...primaryColor);
            doc.rect(10, 6, 190, 3, 'F');
        }

        doc.setDrawColor(...borderLight);
        doc.line(15, currentY, 80, currentY);
        doc.line(130, currentY, 195, currentY);

        currentY += 5;
        doc.setFont("Helvetica", "bold");
        doc.setFontSize(8.5);
        doc.text("Auditor Signature", 15, currentY);
        doc.text("Admin Approval Signature", 130, currentY);

        currentY += 4;
        doc.setFont("Helvetica", "normal");
        doc.setFontSize(8);
        doc.setTextColor(...textMuted);
        doc.text(`Rank/Name: ${verifierRank} ${verifierName}`, 15, currentY);
        doc.text("Date: ________________________", 130, currentY);

        // Footer
        doc.text("Page 1 of 1  ·  NACOC Stores Logistics & Inventory Control System", 105, 282, { align: "center" });

        doc.autoPrint();
        const blobUrl = doc.output('bloburl');
        window.open(blobUrl, '_blank');
    };

    logoImg.onload = () => buildPdf(logoImg);
    logoImg.onerror = () => buildPdf(null);
}
</script>
@endsection
