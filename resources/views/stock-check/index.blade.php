@extends('layouts.dashboard')

@section('content')
<div class="animate-slide-up">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                @if(in_array(auth()->user()->role, ['Main Admin', 'Department Head']))
                    <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">{{ strtoupper(auth()->user()->department) }} · Department Head Hub</span>
                @else
                    <span style="background: rgba(99, 102, 241, 0.1); color: var(--primary); font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">Logistics Control</span>
                @endif
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
                        <th style="padding: 1.25rem 1.5rem; width: 50px; text-align: center; vertical-align: middle;">
                            <input type="checkbox" id="selectAllItems" onchange="toggleSelectAllItems(this)" style="width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer; border-radius: 4px;">
                        </th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Description</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Category</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Package Type</th>
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
                        $threshold = \App\Models\Setting::getItemThreshold($item->description, $item->ledge_category);
                        $stock = (float)$item->total_available;
                        $received = (float)$item->total_received;
                        $variance = (float)$item->total_variance;
                        $statusColor = $stock <= $threshold ? '#ef4444' : ($stock <= ($threshold * 3) ? '#f59e0b' : '#10b981');
                        $statusText = $stock <= 0 ? 'Out of Stock' : ($stock <= $threshold ? 'Low Stock' : 'Stable');
                    @endphp
                    <tr class="activity-row" style="border-top: 1px solid var(--border-color);">
                        <td style="padding: 1.25rem 1.5rem; text-align: center; vertical-align: middle;">
                            <input type="checkbox" class="item-checkbox" data-description="{{ $item->description }}" data-stock="{{ $stock }}" data-received="{{ $received }}" data-variance="{{ $variance }}" data-unit="{{ $item->unit ?: 'Package Types' }}" onchange="updateBatchSelection()" style="width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer; border-radius: 4px;">
                        </td>
                        <td style="padding: 1.25rem 1.5rem;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 1.05rem;">{{ $item->description }}</div>
                        </td>
                        <td style="padding: 1.25rem 1.5rem;">
                            <span style="font-size: 0.75rem; background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 600;">
                                {{ $ledgeMap[$item->ledge_category] ?? "Category " . $item->ledge_category }}
                            </span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; color: var(--text-muted); font-weight: 600;">{{ $item->unit ?: 'Package Types' }}</td>
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
                        <td colspan="9" style="padding: 5rem 2rem; text-align: center;">
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
                            <!-- <option value="Found">Found</option>
                            <option value="Other">Other</option> -->
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Verifier Remarks</label>
                        <textarea id="auditNotes" placeholder="Documentation..." style="width: 100%; height: 44px; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-card); color: var(--text-main); font-family: inherit; resize: none;"></textarea>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 1.1rem; border-radius: 18px; border: none; background: var(--primary-gradient); color: white; font-weight: 900; font-size: 1rem; cursor: pointer;">
                        @if(auth()->user()->is_admin)
                            Seal Record
                        @else
                            Submit for Admin Approval
                        @endif
                    </button>
                    <button type="button" onclick="generateVerificationReport()" class="glass-btn" style="width: 100%; padding: 1.1rem; border-radius: 18px; font-weight: 800; background: #1e293b; color: #fff; cursor: pointer;">
                        Download Draft Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Floating Batch Action Bar -->
<div id="batchActionBar" style="position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%) translateY(150%); background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px); border: 1px solid rgba(0, 0, 0, 0.1); padding: 1rem 2rem; border-radius: 20px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.05); display: flex; align-items: center; gap: 2rem; z-index: 1000; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); width: max-content; max-width: 90%; opacity: 0;">
    <div style="color: #0f172a; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
        <span id="selectedCountBadge" style="background: var(--primary); color: white; padding: 0.2rem 0.6rem; border-radius: 99px; font-size: 0.8rem; font-weight: 900;">0</span>
        <span>Items Selected</span>
    </div>
    <button onclick="openBatchVerifyModal()" class="btn-primary" style="padding: 0.75rem 1.5rem; border-radius: 12px; border: none; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important; color: white !important; cursor: pointer; font-weight: 800; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);">
        <i data-lucide="shield-check" style="width: 18px; color: white !important;"></i>
        <span style="color: white !important;">Verify Selected</span>
    </button>
</div>

<!-- Batch Verification Modal -->
<div id="batchVerifyModal" class="modal-backdrop" style="display: none; align-items: center; justify-content: center; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 2000;">
    <div class="modal-content glass-card animate-scale-up" style="max-width: 950px; width: 95%; max-height: 90vh; overflow-y: auto; border-radius: 28px; background: var(--bg-card); border: 1px solid var(--border-color); padding: 2rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
            <div>
                <h3 style="font-size: 1.6rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.25rem;">Batch Stock Verification</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">Record physical inventory checks for multiple items simultaneously.</p>
            </div>
            <button type="button" onclick="closeBatchVerifyModal()" class="btn-icon danger" style="background: transparent; border: none; color: #ef4444; cursor: pointer; font-size: 1.5rem;">
                <i data-lucide="x" style="width: 24px; height: 24px;"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="batchVerifyForm" onsubmit="event.preventDefault(); submitBatchStockCheck();" style="display: flex; flex-direction: column; gap: 1.5rem;">

                <div style="overflow-x: auto; max-height: 50vh; border-radius: 16px; border: 1px solid var(--border-color); background: var(--bg-main); margin-bottom: 1rem;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="background: rgba(0,0,0,0.03); border-bottom: 2px solid var(--border-color);">
                                <th style="padding: 1rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Item Description</th>
                                <th style="padding: 1rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; width: 120px;">Stock Balance</th>
                                <th style="padding: 1rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; width: 140px;">Physical Count</th>
                                <th style="padding: 1rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; width: 100px;">Variance</th>
                                <th style="padding: 1rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; width: 160px;">Condition</th>
                                <th style="padding: 1rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Verifier Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="batchVerifyTableBody">
                            <!-- Selected items injected dynamically here -->
                        </tbody>
                    </table>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 1.1rem; border-radius: 18px; border: none; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important; color: white !important; font-weight: 900 !important; font-size: 1.05rem; cursor: pointer; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2); display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i data-lucide="shield-check" style="width: 20px; color: white !important;"></i>
                        <span style="color: white !important;">
                            @if(auth()->user()->is_admin)
                                Seal Batch Records
                            @else
                                Submit Batch for Admin Approval
                            @endif
                        </span>
                    </button>
                    <button type="button" onclick="closeBatchVerifyModal()" class="glass-btn" style="width: 100%; padding: 1.1rem; border-radius: 18px; font-weight: 800; background: #1e293b; color: #fff; cursor: pointer; border: none; text-align: center;">
                        Cancel Batch
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
const isAdmin = {{ auth()->user()->is_admin ? 'true' : 'false' }};
let currentAuditItem = null;

function openStockCheckModal(description, ledgeBal, stockBal, prevVar, prevAvail) {
    currentAuditItem = { description, ledgeBal, stockBal, prevVar, prevAvail };
    document.getElementById('auditItemName').innerText = description;
    document.getElementById('auditLedgeBal').innerText = ledgeBal;
    document.getElementById('auditStockBal').innerText = stockBal;
    document.getElementById('auditPrevVar').innerText = prevVar;
    document.getElementById('auditPrevAvail').innerText = prevAvail;

    // Fetch active loans
    fetch(`{{ url('/api/item-audit-details') }}?description=${encodeURIComponent(description)}`)
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
        insight.innerHTML = '<i data-lucide="plus-circle"></i> <span>Surplus detected. You found additional package types.</span>';
    } else {
        insight.innerHTML = '<i data-lucide="alert-triangle"></i> <span>Discrepancy detected. Stock is missing or damaged.</span>';
    }
    lucide.createIcons();
}

function submitStockCheck() {
    const description = document.getElementById('auditItemName').innerText;
    const physicalCount = document.getElementById('physicalCount').value;
    const condition = document.getElementById('auditReason').value;
    const remarks = document.getElementById('auditNotes').value;

    if (!physicalCount || physicalCount === '') {
        showToast('Verification Failed', 'Please specify a physical count first.', 'error');
        return;
    }

    if (!condition) {
        showToast('Verification Failed', 'Please select a verification condition (Status).', 'error');
        return;
    }

    const submitBtn = document.querySelector('#stockCheckForm button[type="submit"]');
    const originalHtml = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<div class="loader" style="width: 14px; height: 14px; border-width: 2px; border-color: white;"></div> ${isAdmin ? 'Sealing Record...' : 'Submitting Request...'}`;

    fetch("{{ route('stockcheck.verify') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            description: description,
            physical_count: physicalCount,
            condition: condition,
            remarks: remarks
        })
    })
    .then(res => {
        if (!res.ok) throw new Error('Server protocol violation');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            closeStockCheckModal();
            Swal.fire({
                title: 'Verification Complete',
                text: data.message,
                icon: 'success',
                confirmButtonColor: 'var(--primary)'
            }).then(() => {
                window.location.reload();
            });
        } else {
            showToast('Verification Failed', data.message || 'An unexpected error occurred.', 'error');
        }
    })
    .catch(err => {
        /* console print removed */
        showToast('Network Error', 'Could not establish connection to logistics servers.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
    });
}

function generateVerificationReport() {
    const physical = document.getElementById('physicalCount').value;
    const variance = document.getElementById('newAuditVariance').innerText;
    const description = document.getElementById('auditItemName').innerText;

    let report = `STOCK VERIFICATION REPORT - ${new Date().toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: '2-digit' })}\n`;
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

function toggleSelectAllItems(masterCb) {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = masterCb.checked;
    });
    updateBatchSelection();
}

function updateBatchSelection() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const bar = document.getElementById('batchActionBar');
    const badge = document.getElementById('selectedCountBadge');

    if (checkedBoxes.length > 0) {
        badge.innerText = checkedBoxes.length;
        bar.style.transform = 'translateX(-50%) translateY(0)';
        bar.style.opacity = '1';
    } else {
        bar.style.transform = 'translateX(-50%) translateY(150%)';
        bar.style.opacity = '0';
        document.getElementById('selectAllItems').checked = false;
    }
}

function openBatchVerifyModal() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    if (checkedBoxes.length === 0) return;

    const tbody = document.getElementById('batchVerifyTableBody');
    tbody.innerHTML = '';

    checkedBoxes.forEach((cb, index) => {
        const desc = cb.getAttribute('data-description');
        const stock = parseFloat(cb.getAttribute('data-stock'));
        const unit = cb.getAttribute('data-unit');

        const tr = document.createElement('tr');
        tr.style.borderBottom = '1px solid var(--border-color)';
        tr.className = 'batch-verify-row';
        tr.setAttribute('data-desc', desc);

        tr.innerHTML = `
            <td style="padding: 1rem; font-weight: 700; color: var(--text-main); font-size: 0.95rem;">
                ${desc}
            </td>
            <td style="padding: 1rem; font-weight: 800; color: var(--text-main); font-size: 1rem;">
                ${stock} <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">${unit}</span>
            </td>
            <td style="padding: 1rem;">
                <input type="number" class="batch-physical-count" required min="0" placeholder="0" oninput="calculateBatchRowVariance(this, ${stock})" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-card); color: var(--text-main); font-weight: 800; font-size: 1.1rem; text-align: center; outline: none;">
            </td>
            <td style="padding: 1rem;">
                <div class="batch-variance-display" style="font-size: 1.1rem; font-weight: 900; color: #94a3b8; text-align: center;">--</div>
            </td>
            <td style="padding: 1rem;">
                <select class="batch-condition" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-card); color: var(--text-main); font-family: inherit; font-size: 0.85rem; font-weight: 600;">
                    <option value="">Select...</option>
                    <option value="Match">Match</option>
                    <option value="Missing">Missing</option>
                    <option value="Damaged">Damaged</option>
                    <option value="Found">Found</option>
                    <option value="Other">Other</option>
                </select>
            </td>
            <td style="padding: 1rem;">
                <textarea class="batch-remarks" placeholder="Remarks..." style="width: 100%; height: 38px; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-card); color: var(--text-main); font-family: inherit; font-size: 0.85rem; resize: none; outline: none;"></textarea>
            </td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('batchVerifyModal').style.display = 'flex';
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

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
    varDisplay.style.color = variance === 0 ? '#10b981' : (variance > 0 ? '#8b5cf6' : '#ef4444');
}

function closeBatchVerifyModal() {
    document.getElementById('batchVerifyModal').style.display = 'none';
    document.getElementById('batchVerifyForm').reset();
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
    const originalHtml = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<div class="loader" style="width: 14px; height: 14px; border-width: 2px; border-color: white;"></div> ${isAdmin ? 'Sealing Batch...' : 'Submitting Request...'}`;

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
            closeBatchVerifyModal();
            Swal.fire({
                title: 'Batch Verification Submitted',
                text: data.message,
                icon: 'success',
                confirmButtonColor: 'var(--primary)'
            }).then(() => {
                window.location.reload();
            });
        } else {
            showToast('Batch Verification Failed', data.message || 'An unexpected error occurred.', 'error');
        }
    })
    .catch(err => {
        /* console print removed */
        showToast('Network Error', 'Could not establish connection to logistics servers.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
    });
}
</script>
@endsection
