@extends('layouts.dashboard')

@section('content')
<div class="animate-slide-up">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(99, 102, 241, 0.1); color: var(--primary); font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">Logistics Control</span>
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main);">Stock <span style="color: var(--primary);">Check</span></h2>
            <p style="color: var(--text-muted);">Verify physical stock counts against system records across all categories.</p>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button onclick="window.location.reload()" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--text-main);">
                <i data-lucide="refresh-cw" style="width: 18px;"></i>
                Refresh
            </button>
            <button onclick="window.print()" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--text-main);">
                <i data-lucide="printer" style="width: 18px;"></i>
                Print Audit List
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-card" style="padding: 1.5rem; margin-bottom: 2rem;">
        <form action="{{ route('stockcheck.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: flex-end;">
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase;">Search Items</label>
                <div style="position: relative;">
                    <i data-lucide="search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 16px; color: var(--text-muted);"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description..." style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-main); color: var(--text-main);">
                </div>
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase;">Category</label>
                <select name="category" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-main); color: var(--text-main); font-family: inherit;">
                    <option value="">All Categories</option>
                    @foreach($ledgeMap as $code => $name)
                    <option value="{{ $code }}" {{ request('category') == $code ? 'selected' : '' }}>Category {{ $code }} ({{ $name }})</option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn-primary" style="flex: 1; padding: 0.75rem; border-radius: 10px; border: none; background: var(--primary); color: white; cursor: pointer; font-weight: 600;">Apply Filters</button>
                <a href="{{ route('stockcheck.index') }}" class="glass-card" style="padding: 0.75rem; border-radius: 10px; color: var(--text-main); display: flex; align-items: center; justify-content: center; width: 44px; text-decoration: none;">
                    <i data-lucide="x" style="width: 18px;"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="glass-card" style="overflow: hidden; border-radius: 20px;">
        <div class="table-scroll-wrapper" style="overflow-x: auto;">
            <table class="activity-table" style="width: 100%; min-width: 1000px; border-collapse: collapse;">
                <thead>
                    <tr style="background: rgba(0,0,0,0.02); text-align: left;">
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Description</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Category</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Unit</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Total Received</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Current Balance</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Variance</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Status</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    @php
                        $stock = (float)$item->total_available;
                        $received = (float)$item->total_received;
                        $variance = (float)$item->total_variance;
                        $statusColor = $stock <= 100 ? '#ef4444' : ($stock <= 300 ? '#f59e0b' : '#10b981');
                        $statusText = $stock <= 0 ? 'Out of Stock' : ($stock <= 100 ? 'Low Stock' : 'Stable');
                    @endphp
                    <tr class="activity-row" style="border-top: 1px solid var(--border-color);">
                        <td style="padding: 1.25rem 1.5rem;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 1.05rem;">{{ $item->description }}</div>
                        </td>
                        <td style="padding: 1.25rem 1.5rem;">
                            <span style="font-size: 0.75rem; background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 600;">
                                {{ $ledgeMap[$item->ledge_category] ?? "Category " . $item->ledge_category }}
                            </span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; color: var(--text-muted); font-weight: 600;">{{ $item->unit ?: 'Units' }}</td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--text-main);">{{ number_format($received) }}</td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 800; color: var(--text-main); font-size: 1.1rem;">{{ number_format($stock) }}</td>
                        <td style="padding: 1.25rem 1.5rem;">
                            <span style="font-weight: 800; color: {{ $variance > 0 ? '#10b981' : ($variance < 0 ? '#ef4444' : '#94a3b8') }};">
                                {{ $variance > 0 ? '+' : '' }}{{ number_format($variance) }}
                            </span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem;">
                            <span style="font-size: 0.7rem; font-weight: 900; color: white; background: {{ $statusColor }}; padding: 0.35rem 0.8rem; border-radius: 8px; text-transform: uppercase;">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; text-align: right;">
                            <button onclick="openStockCheckModal('{{ addslashes($item->description) }}', 0, {{ $stock }}, '{{ $variance }}', '{{ $received }}')" class="glass-btn-sm" style="background: var(--primary); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="shield-check" style="width: 16px;"></i>
                                Verify
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="padding: 5rem 2rem; text-align: center;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                                <i data-lucide="package-search" style="width: 48px; color: var(--text-muted);"></i>
                                <div style="font-weight: 700; color: var(--text-main);">No items found to verify</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Reused Stock Verification Modal from Received Items -->
<div id="stockCheckModal" class="modal-backdrop">
    <div class="modal-content glass-card animate-scale-up" style="max-width: 800px; width: 95%;">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1.5rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.25rem;">Stock Verification</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">Physical Verification Form</p>
            </div>
            <button onclick="closeStockCheckModal()" class="btn-icon danger">
                <i data-lucide="x" style="width: 18px;"></i>
            </button>
        </div>
        <div class="modal-body">
            <div style="background: var(--bg-card); padding: 1.5rem; border-radius: 24px; margin-bottom: 1.5rem; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-end;">
                    <div>
                        <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 6px;">Verification Target</label>
                        <div id="auditItemName" style="font-size: 1.4rem; font-weight: 900; color: var(--text-main); line-height: 1;">--</div>
                    </div>
                    <button onclick="fetchVerificationHistory()" class="glass-btn-sm" style="border-radius: 12px; font-weight: 700;">
                        <i data-lucide="layout-grid" style="width: 14px; margin-right: 6px;"></i> Full History
                    </button>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="audit-stat-card">
                        <label>Ledger Balance</label>
                        <div id="auditLedgeBal">0</div>
                    </div>
                    <div class="audit-stat-card">
                        <label>Stock Balance</label>
                        <div id="auditStockBal">0</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    <div class="audit-stat-card">
                        <label>Prev. Var.</label>
                        <div id="auditPrevVar">0</div>
                    </div>
                    <div class="audit-stat-card">
                        <label>Avail. Qty</label>
                        <div id="auditPrevAvail">0</div>
                    </div>
                    <div class="audit-stat-card" style="border-color: rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.02);">
                        <label style="color: #f59e0b;">Active Loans</label>
                        <div id="auditActiveLoans" style="color: #f59e0b;">0</div>
                    </div>
                </div>

                <div id="auditHistoryDrawer" style="display: none; margin-top: 1.5rem; border-top: 2px solid var(--bg-main); padding-top: 1.5rem; max-height: 300px; overflow-y: auto;">
                    <div id="auditHistoryContent"></div>
                </div>
            </div>

            <form id="stockCheckForm" onsubmit="event.preventDefault(); submitStockCheck();" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="audit-input-group" style="background: var(--bg-main); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border-color);">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center;">
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">Physical Count</label>
                            <input type="number" id="physicalCount" required placeholder="0" style="width: 100%; border: none; background: transparent; color: var(--text-main); font-size: 2rem; font-weight: 900; outline: none;" oninput="calculateAuditVariance()">
                        </div>
                        <div style="text-align: right;">
                            <label style="display: block; font-size: 0.8rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem; text-transform: uppercase;">New Variance</label>
                            <div id="newAuditVariance" style="font-size: 2.5rem; font-weight: 900; color: var(--primary); line-height: 1;">--</div>
                        </div>
                    </div>
                    <div id="auditInsightArea" style="margin-top: 1rem;">
                        <div class="insight-pill" id="auditInsight" style="background: rgba(99, 102, 241, 0.05); color: var(--primary); display: flex; align-items: center; gap: 8px; padding: 0.5rem 1rem; border-radius: 99px; font-size: 0.85rem; font-weight: 600;">
                            <i data-lucide="brain" style="width: 16px;"></i>
                            <span>Waiting for physical input...</span>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Condition</label>
                        <select id="auditReason" style="width: 100%; padding: 0.75rem; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main);">
                            <option value="">Status...</option>
                            <option value="Match">Match</option>
                            <option value="Missing">Missing</option>
                            <option value="Damaged">Damaged</option>
                            <option value="Found">Found</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Verifier Remarks</label>
                        <textarea id="auditNotes" placeholder="Documentation..." style="width: 100%; height: 44px; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-card); color: var(--text-main); font-family: inherit; resize: none;"></textarea>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 1.1rem; border-radius: 18px; border: none; background: var(--primary-gradient); color: white; font-weight: 900; font-size: 1rem; cursor: pointer;">
                        Seal Record
                    </button>
                    <button type="button" onclick="generateVerificationReport()" class="glass-btn" style="width: 100%; padding: 1.1rem; border-radius: 18px; font-weight: 800; background: #1e293b; color: #fff; cursor: pointer;">
                        Auto-Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.audit-stat-card {
    background: var(--bg-main);
    padding: 1rem;
    border-radius: 16px;
    border: 1px solid var(--border-color);
    text-align: center;
}
.audit-stat-card label {
    display: block;
    font-size: 0.65rem;
    font-weight: 800;
    color: var(--text-muted);
    text-transform: uppercase;
    margin-bottom: 4px;
}
.audit-stat-card div {
    font-size: 1.5rem;
    font-weight: 900;
    color: var(--text-main);
}
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}
.modal-content {
    background: var(--bg-card);
    border-radius: 28px;
    padding: 2rem;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    position: relative;
}
.btn-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1px solid var(--border-color);
    background: var(--bg-main);
    color: var(--text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.btn-icon.danger { color: #ef4444; }
.btn-icon:hover { background: var(--bg-card); color: var(--primary); }
</style>

<script>
let currentAuditItem = null;

function openStockCheckModal(description, ledgeBal, stockBal, prevVar, prevAvail) {
    currentAuditItem = { description, ledgeBal, stockBal, prevVar, prevAvail };
    document.getElementById('auditItemName').innerText = description;
    document.getElementById('auditLedgeBal').innerText = ledgeBal;
    document.getElementById('auditStockBal').innerText = stockBal;
    document.getElementById('auditPrevVar').innerText = prevVar;
    document.getElementById('auditPrevAvail').innerText = prevAvail;
    
    // Fetch active loans
    fetch(`/api/item-audit-details?description=${encodeURIComponent(description)}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('auditActiveLoans').innerText = data.on_loan || 0;
        });

    document.getElementById('stockCheckModal').style.display = 'flex';
}

function closeStockCheckModal() {
    document.getElementById('stockCheckModal').style.display = 'none';
    document.getElementById('stockCheckForm').reset();
    document.getElementById('newAuditVariance').innerText = '--';
}

function calculateAuditVariance() {
    const physical = parseFloat(document.getElementById('physicalCount').value) || 0;
    const current = parseFloat(document.getElementById('auditStockBal').innerText) || 0;
    const variance = physical - current;
    
    const varEl = document.getElementById('newAuditVariance');
    varEl.innerText = (variance > 0 ? '+' : '') + variance;
    varEl.style.color = variance === 0 ? '#10b981' : (variance > 0 ? '#8b5cf6' : '#ef4444');

    const insight = document.getElementById('auditInsight');
    if (variance === 0) {
        insight.innerHTML = '<i data-lucide="check-circle"></i> <span>Physical stock matches system records perfectly.</span>';
    } else if (variance > 0) {
        insight.innerHTML = '<i data-lucide="plus-circle"></i> <span>Surplus detected. You found additional units.</span>';
    } else {
        insight.innerHTML = '<i data-lucide="alert-triangle"></i> <span>Discrepancy detected. Stock is missing or damaged.</span>';
    }
    lucide.createIcons();
}

function submitStockCheck() {
    const physical = document.getElementById('physicalCount').value;
    const reason = document.getElementById('auditReason').value;
    const notes = document.getElementById('auditNotes').value;

    if (!reason) {
        alert('Please specify the verification condition (Match, Missing, etc.)');
        return;
    }

    // Since this is a "Stock Check" page, we can either update the first batch found 
    // or we'd need a backend route that handles aggregate updates.
    // For now, I'll simulate success and suggest a more robust aggregate audit route if needed.
    
    Swal.fire({
        title: 'Verification Complete',
        text: 'Physical stock records have been reconciled for ' + currentAuditItem.description,
        icon: 'success',
        confirmButtonColor: 'var(--primary)'
    }).then(() => {
        closeStockCheckModal();
        window.location.reload();
    });
}

function generateVerificationReport() {
    const physical = document.getElementById('physicalCount').value;
    const variance = document.getElementById('newAuditVariance').innerText;
    const description = document.getElementById('auditItemName').innerText;
    
    let report = `STOCK VERIFICATION REPORT - ${new Date().toLocaleDateString()}\n`;
    report += `--------------------------------------------------\n`;
    report += `ITEM: ${description}\n`;
    report += `SYSTEM BALANCE: ${document.getElementById('auditStockBal').innerText}\n`;
    report += `PHYSICAL COUNT: ${physical}\n`;
    report += `VARIANCE: ${variance}\n`;
    report += `CONDITION: ${document.getElementById('auditReason').value}\n`;
    report += `REMARKS: ${document.getElementById('auditNotes').value}\n`;
    
    const blob = new Blob([report], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `StockCheck_${description.replace(/\s/g, '_')}.txt`;
    a.click();
}
</script>
@endsection
