@extends('layouts.dashboard')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<div class="animate-slide-up">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                @if(auth()->user()->role === 'Auditor')
                    <span style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">Audit Oversight Clearance</span>
                @elseif(in_array(auth()->user()->role, ['Main Admin', 'Department Head']))
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
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Stock Balance</th>
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
                        $statusColor = $stock <= 0 ? '#ef4444' : ($stock <= $threshold ? '#f59e0b' : '#10b981');
                        $statusText = $stock <= 0 ? 'Out of Stock' : ($stock <= $threshold ? 'Low Stock' : 'In Stock');
                    @endphp
                    <tr class="activity-row" style="border-top: 1px solid var(--border-color);">
                        <td data-label="Select" style="padding: 1.25rem 1.5rem; text-align: center; vertical-align: middle;">
                            <input type="checkbox" class="item-checkbox" data-description="{{ $item->description }}" data-stock="{{ $stock }}" data-received="{{ $received }}" data-variance="{{ $variance }}" data-unit="{{ $item->unit ?: 'Package Types' }}" onchange="updateBatchSelection()" style="width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer; border-radius: 4px;">
                        </td>
                        <td data-label="Description" style="padding: 1.25rem 1.5rem;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 1.05rem;">{{ $item->description }}</div>
                        </td>
                        <td data-label="Category" style="padding: 1.25rem 1.5rem;">
                            <span style="font-size: 0.75rem; background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 600;">
                                {{ $ledgeMap[$item->ledge_category] ?? "Category " . $item->ledge_category }}
                            </span>
                        </td>
                        <td data-label="Package Type" style="padding: 1.25rem 1.5rem; color: var(--text-muted); font-weight: 600;">{{ $item->unit ?: 'Package Types' }}</td>
                        <td data-label="Total Received" style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--text-main);">{{ number_format($received) }}</td>
                        <td data-label="Stock Balance" style="padding: 1.25rem 1.5rem; font-weight: 800; color: var(--text-main); font-size: 1.1rem;">{{ number_format($stock) }}</td>
                        <td data-label="Variance" style="padding: 1.25rem 1.5rem;">
                            <span style="font-weight: 800; color: {{ $variance > 0 ? '#10b981' : ($variance < 0 ? '#ef4444' : '#94a3b8') }};">
                                {{ $variance > 0 ? '+' : '' }}{{ number_format($variance) }}
                            </span>
                        </td>
                        <td data-label="Status" style="padding: 1.25rem 1.5rem;">
                            <span style="font-size: 0.7rem; font-weight: 900; color: white; background: {{ $statusColor }}; padding: 0.35rem 0.8rem; border-radius: 8px; text-transform: uppercase;">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td data-label="Action" style="padding: 1.25rem 1.5rem; text-align: right;">
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
<div id="stockCheckModal" class="modal-backdrop" style="display: none; align-items: center; justify-content: center; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(10px); z-index: 2000; transition: opacity 0.3s ease;">
    <div class="modal-content glass-card animate-scale-up" style="max-width: 800px; width: 95%; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 28px; box-shadow: 0 30px 70px rgba(0, 0, 0, 0.25); padding: 2rem; position: relative;">
        
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1.25rem;">
            <div>
                <h3 style="font-size: 1.65rem; font-weight: 950; color: var(--text-main); letter-spacing: -0.03em; margin: 0 0 4px 0;">Stock Verification</h3>
                <p style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600; margin: 0;">Physical inventory reconciliation panel</p>
            </div>
            <button onclick="closeStockCheckModal()" class="btn-icon danger" style="width: 38px; height: 38px; border-radius: 12px; background: rgba(239, 68, 68, 0.05); border: 1.5px solid rgba(239, 68, 68, 0.15); color: #ef4444; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.15)';" onmouseout="this.style.background='rgba(239, 68, 68, 0.05)';">
                <i data-lucide="x" style="width: 20px;"></i>
            </button>
        </div>

        <div class="modal-body" style="display: flex; flex-direction: column; gap: 1.5rem;">
            {{-- Target Item Card --}}
            <div style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.02) 0%, rgba(139, 92, 246, 0.02) 100%); padding: 1.5rem; border-radius: 24px; border: 1.5px solid var(--border-color); box-shadow: var(--shadow-premium);">
                <div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.65rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Audit Target Asset</label>
                        <div id="auditItemName" style="font-size: 1.45rem; font-weight: 950; color: var(--text-main); letter-spacing: -0.02em; line-height: 1.1;">--</div>
                    </div>
                    <button onclick="fetchVerificationHistory()" class="glass-card" style="padding: 0.6rem 1.1rem; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-card); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-weight: 800; font-size: 0.8rem; color: var(--text-main); transition: all 0.2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
                        <i data-lucide="history" style="width: 15px; color: var(--primary);"></i> Verification History
                    </button>
                </div>

                {{-- Status Stats Grid --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 1rem;">
                    <div class="audit-stat-card" style="background: rgba(99, 102, 241, 0.04); border-color: rgba(99, 102, 241, 0.18); border-radius: 16px; padding: 1rem; border-width: 1.5px; text-align: center;">
                        <label style="color: var(--primary); font-size: 0.65rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px;">Stock Balance</label>
                        <div id="auditStockBal" style="font-size: 1.6rem; font-weight: 950; color: var(--primary); line-height: 1.1;">0</div>
                    </div>
                    <div class="audit-stat-card" style="border-radius: 16px; padding: 1rem; border-color: var(--border-color); background: var(--bg-card); text-align: center; border: 1px solid var(--border-color);">
                        <label style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px; color: var(--text-muted);">Prev. Variance</label>
                        <div id="auditPrevVar" style="font-size: 1.6rem; font-weight: 950; color: var(--text-main); line-height: 1.1;">0</div>
                    </div>
                    <div class="audit-stat-card" style="border-radius: 16px; padding: 1rem; border-color: var(--border-color); background: var(--bg-card); text-align: center; border: 1px solid var(--border-color);">
                        <label style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px; color: var(--text-muted);">Available Qty</label>
                        <div id="auditPrevAvail" style="font-size: 1.6rem; font-weight: 950; color: var(--text-main); line-height: 1.1;">0</div>
                    </div>
                    <div class="audit-stat-card" style="border-radius: 16px; padding: 1rem; border: 1px solid rgba(245, 158, 11, 0.22); background: rgba(245, 158, 11, 0.02); text-align: center;">
                        <label style="color: #f59e0b; font-size: 0.65rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px;">Active Loans</label>
                        <div id="auditActiveLoans" style="color: #f59e0b; font-size: 1.6rem; font-weight: 950; line-height: 1.1;">0</div>
                    </div>
                </div>

                <div id="auditHistoryDrawer" style="display: none; margin-top: 1.5rem; border-top: 1.5px solid var(--border-color); padding-top: 1.5rem; max-height: 250px; overflow-y: auto;">
                    <div id="auditHistoryContent"></div>
                </div>

                <div id="activeLoansDrawer" style="display: none; margin-top: 1.5rem; border-top: 1.5px solid var(--border-color); padding-top: 1.5rem; max-height: 250px; overflow-y: auto;">
                    <h4 style="font-size: 0.95rem; font-weight: 800; color: #f59e0b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="info" style="width: 16px; height: 16px;"></i>
                        Active Temporary Loans Details
                    </h4>
                    <div id="activeLoansContent"></div>
                </div>
            </div>

            <form id="stockCheckForm" onsubmit="event.preventDefault(); {{ auth()->user()->role === 'Auditor' ? 'return false;' : 'submitStockCheck();' }}" style="display: flex; flex-direction: column; gap: 1.5rem;">
                {{-- Input Area --}}
                <div class="audit-input-group" style="background: rgba(0, 0, 0, 0.01); padding: 1.75rem; border-radius: 24px; border: 1.5px solid var(--border-color);">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center;">
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.6rem; text-transform: uppercase; letter-spacing: 0.03em;">Physical Counted Qty</label>
                            <div style="display: flex; align-items: center; border: 2px solid var(--border-color); border-radius: 14px; padding: 0.5rem 1rem; background: var(--bg-card); transition: border-color 0.2s;" onfocusin="this.style.borderColor='var(--primary)'" onfocusout="this.style.borderColor='var(--border-color)'">
                                <input type="number" id="physicalCount" required placeholder="0" style="width: 100%; border: none; background: transparent; color: var(--text-main); font-size: 1.8rem; font-weight: 950; outline: none; margin: 0; padding: 0;" oninput="calculateAuditVariance()">
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <label style="display: block; font-size: 0.8rem; font-weight: 900; color: var(--primary); margin-bottom: 0.6rem; text-transform: uppercase; letter-spacing: 0.03em;">New Discrepancy</label>
                            <div id="newAuditVariance" style="font-size: 2.2rem; font-weight: 950; color: var(--primary); line-height: 1; letter-spacing: -0.03em;">--</div>
                        </div>
                    </div>
                    <div id="auditInsightArea" style="margin-top: 1.25rem;">
                        <div class="insight-pill" id="auditInsight" style="background: rgba(99, 102, 241, 0.05); color: var(--primary); display: flex; align-items: center; gap: 8px; padding: 0.6rem 1.1rem; border-radius: 12px; font-size: 0.85rem; font-weight: 750; border: 1px solid rgba(99, 102, 241, 0.15);">
                            <i data-lucide="info" style="width: 16px;"></i>
                            <span>Waiting for physical input...</span>
                        </div>
                    </div>
                </div>

                {{-- Status & Notes fields --}}
                <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.25rem; flex-wrap: wrap;">
                    <div>
                        <label style="display: block; font-size: 0.72rem; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">Verification Condition</label>
                        <select id="auditReason" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 2px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-weight: 750; font-size: 0.85rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'">
                            <option value="">Select Condition...</option>
                            <option value="Match">Perfect Match</option>
                            <option value="Missing">Missing Stock</option>
                            <option value="Damaged">Damaged Asset</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.72rem; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">Auditing Notes & Remarks</label>
                        <textarea id="auditNotes" required placeholder="Describe condition details, serial numbers, or variance details..." style="width: 100%; height: 44px; padding: 0.75rem 1rem; border: 2px solid var(--border-color); border-radius: 12px; background: var(--bg-card); color: var(--text-main); font-family: inherit; font-size: 0.85rem; font-weight: 600; outline: none; resize: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'"></textarea>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div style="display: grid; grid-template-columns: {{ auth()->user()->role === 'Auditor' ? '1fr' : '1.2fr 1fr' }}; gap: 1rem; margin-top: 1.5rem; align-items: center;">
                    @if(auth()->user()->role !== 'Auditor')
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 1.1rem; border-radius: 18px; border: none; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; font-weight: 900; font-size: 1rem; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.25);" onmouseover="this.style.transform='translateY(-1.5px)'; this.style.boxShadow='0 15px 30px rgba(99, 102, 241, 0.35)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px rgba(99, 102, 241, 0.25)';" >
                        <i data-lucide="shield-check" style="width: 18px; color: white !important;"></i>
                        <span style="color: white !important;">
                            @if(auth()->user()->is_admin)
                                Seal Verification Record
                            @else
                                Submit Verification to Admin
                            @endif
                        </span>
                    </button>
                    @endif
                    <button type="button" onclick="generateVerificationReport()" class="glass-btn" style="width: 100%; padding: 1.1rem; border-radius: 18px; border: 1.5px solid var(--border-color); font-weight: 800; background: var(--bg-card); color: var(--text-main); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.02)';" onmouseout="this.style.background='var(--bg-card)';">
                        <i data-lucide="printer" style="width: 18px; color: var(--primary);"></i>
                        Print Draft PDF
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
    <button onclick="openBatchVerifyPage()" class="btn-primary" style="padding: 0.75rem 1.5rem; border-radius: 12px; border: none; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important; color: white !important; cursor: pointer; font-weight: 800; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);">
        <i data-lucide="shield-check" style="width: 18px; color: white !important;"></i>
        <span style="color: white !important;">Verify Selected</span>
    </button>
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

/* Premium Responsive Table to Mobile Cards */
@media (max-width: 768px) {
    .table-scroll-wrapper {
        overflow-x: visible !important;
        padding: 0.5rem !important;
    }
    .activity-table {
        min-width: 100% !important;
        border-spacing: 0 1rem !important;
        border-collapse: separate !important;
    }
    .activity-table thead {
        display: none;
    }
    .activity-table tbody {
        display: block;
    }
    .activity-table tr {
        display: block;
        margin-bottom: 2rem;
        padding: 1.75rem !important;
        background: var(--bg-card) !important;
        border-radius: 32px !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06) !important;
        border: 1px solid var(--border-color) !important;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    /* Samsung-style left accent bar */
    .activity-table tr::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 6px;
        background: var(--primary);
    }
    .activity-table td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0 !important;
        border-bottom: 1px solid rgba(0,0,0,0.03) !important;
        border-radius: 0 !important;
        width: 100% !important;
    }
    .activity-table td[data-label="Select"] {
        justify-content: space-between !important;
    }
    .activity-table td:last-child {
        border-bottom: none !important;
        padding-top: 1.5rem !important;
        justify-content: center !important;
    }
    .activity-table td::before {
        content: attr(data-label);
        font-weight: 800;
        color: var(--text-muted);
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }
    .activity-table td > div,
    .activity-table td > span {
        text-align: right;
        font-size: 0.95rem;
        font-weight: 700;
    }
    .activity-table td[data-label="Description"] > div:first-child {
        font-size: 1.1rem;
        font-weight: 900;
        color: var(--primary);
        text-align: right;
    }
}
</style>

<script>
const isAdmin = {{ auth()->user()->is_admin ? 'true' : 'false' }};
let currentAuditItem = null;

function openStockCheckModal(description, ledgeBal, stockBal, prevVar, prevAvail) {
    currentAuditItem = { description, ledgeBal, stockBal, prevVar, prevAvail };
    document.getElementById('auditItemName').innerText = description;
    document.getElementById('auditStockBal').innerText = stockBal;
    document.getElementById('auditPrevVar').innerText = prevVar;
    document.getElementById('auditPrevAvail').innerText = prevAvail;

    // Fetch active loans
    fetch(`{{ url('/api/item-audit-details') }}?description=${encodeURIComponent(description)}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('auditActiveLoans').innerText = data.on_loan || 0;

            const drawer = document.getElementById('activeLoansDrawer');
            const content = document.getElementById('activeLoansContent');
            
            if (data.loans && data.loans.length > 0) {
                drawer.style.display = 'block';
                let html = `
                    <div style="overflow-x: auto; border-radius: 12px; border: 1.5px solid var(--border-color); background: var(--bg-main);">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8rem;">
                            <thead>
                                <tr style="background: rgba(245, 158, 11, 0.04); border-bottom: 1px solid var(--border-color);">
                                    <th style="padding: 0.6rem 0.8rem; font-weight: 700; color: var(--text-muted);">Borrower</th>
                                    <th style="padding: 0.6rem 0.8rem; font-weight: 700; color: var(--text-muted); text-align: center;">Qty</th>
                                    <th style="padding: 0.6rem 0.8rem; font-weight: 700; color: var(--text-muted);">Return Date</th>
                                    <th style="padding: 0.6rem 0.8rem; font-weight: 700; color: var(--text-muted); text-align: center;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                data.loans.forEach(loan => {
                    const overdueBadge = loan.is_overdue 
                        ? `<span style="font-size: 0.65rem; font-weight: 800; color: #ef4444; background: rgba(239, 68, 68, 0.08); padding: 0.15rem 0.4rem; border-radius: 4px; border: 1px solid rgba(239, 68, 68, 0.15);">Overdue</span>`
                        : `<span style="font-size: 0.65rem; font-weight: 800; color: #10b981; background: rgba(16, 185, 129, 0.08); padding: 0.15rem 0.4rem; border-radius: 4px; border: 1px solid rgba(16, 185, 129, 0.15);">Active</span>`;
                    html += `
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 0.6rem 0.8rem; font-weight: 600; color: var(--text-main);">${loan.beneficiary} <span style="font-size: 0.72rem; color: var(--text-muted);">(${loan.department || 'N/A'})</span></td>
                                    <td style="padding: 0.6rem 0.8rem; text-align: center; font-weight: 800; color: var(--text-main);">${loan.quantity} ${loan.unit}</td>
                                    <td style="padding: 0.6rem 0.8rem; font-weight: 700; color: ${loan.is_overdue ? '#ef4444' : 'var(--text-main)'};">${loan.expected_return_date}</td>
                                    <td style="padding: 0.6rem 0.8rem; text-align: center;">${overdueBadge}</td>
                                </tr>
                    `;
                });
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                content.innerHTML = html;
            } else {
                drawer.style.display = 'none';
                content.innerHTML = '';
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });

    document.getElementById('stockCheckModal').style.display = 'flex';
}

function closeStockCheckModal() {
    document.getElementById('stockCheckModal').style.display = 'none';
    document.getElementById('stockCheckForm').reset();
    document.getElementById('newAuditVariance').innerText = '--';
    document.getElementById('activeLoansDrawer').style.display = 'none';
    document.getElementById('activeLoansContent').innerHTML = '';
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
    const originalHtml = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<div class="loader" style="width: 14px; height: 14px; border-width: 2px; border-color: white;"></div> ${isAdmin ? 'Sealing Record...' : 'Submitting Request...'}`;
    }

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
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        }
    });
}

function generateVerificationReport() {
    const physical = document.getElementById('physicalCount').value || '0';
    const variance = document.getElementById('newAuditVariance').innerText || '0';
    const description = document.getElementById('auditItemName').innerText;
    const sysBalance = document.getElementById('auditStockBal').innerText;
    const prevVar = document.getElementById('auditPrevVar').innerText;
    const availQty = document.getElementById('auditPrevAvail').innerText;
    const activeLoans = document.getElementById('auditActiveLoans').innerText;
    const condition = document.getElementById('auditReason').value || 'N/A';
    const remarks = document.getElementById('auditNotes').value || 'No remarks provided.';
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

        // Brand Colors (Navy Blue & Slate)
        const primaryColor = [30, 58, 138]; // #1e3a8a (Navy Blue)
        const secondaryColor = [59, 130, 246]; // #3b82f6 (Blue)
        const slateDark = [15, 23, 42]; // #0f172a (Navy Slate)
        const textMuted = [100, 116, 139]; // #64748b (Muted Slate)
        const bgLight = [248, 250, 252]; // #f8fafc (Ice Blue/Grey)
        const borderLight = [226, 232, 240]; // #e2e8f0

        // 1. Page Outline Border (Premium design feel)
        doc.setDrawColor(...borderLight);
        doc.setLineWidth(0.3);
        doc.rect(10, 6, 190, 280);

        // 2. Top Accent Line
        doc.setFillColor(...primaryColor);
        doc.rect(10, 6, 190, 3, 'F');

        // 3. Logo Placement (top right)
        if (imgEl) {
            // Position: X=168, Y=12, Width=22, Height=22
            doc.addImage(imgEl, 'PNG', 168, 12, 22, 22);
        }

        // 4. Header Section
        doc.setFillColor(...primaryColor);
        doc.rect(15, 12, 2, 22, 'F'); // Left accent bar

        doc.setFont("Helvetica", "bold");
        doc.setFontSize(8.5);
        doc.setTextColor(...primaryColor);
        doc.text("NARCOTICS CONTROL COMMISSION · NACOC", 20, 16);

        // Document Title
        doc.setFontSize(18);
        doc.setTextColor(...slateDark);
        doc.text("Stock Verification Report", 20, 24);
        
        doc.setFont("Helvetica", "normal");
        doc.setFontSize(9);
        doc.setTextColor(...textMuted);
        doc.text("NaCal Stores Logistics & Audit Control Hub", 20, 30);

        // Horizontal Separator
        doc.setDrawColor(...borderLight);
        doc.setLineWidth(0.5);
        doc.line(15, 38, 195, 38);

        // Section 1: Verifier Details
        doc.setFont("Helvetica", "bold");
        doc.setFontSize(9.5);
        doc.setTextColor(...primaryColor);
        doc.text("1. VERIFIER DETAILS", 15, 45);

        // Info Card Box
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
        doc.text(`SVR-${Math.floor(100000 + Math.random() * 900000)}`, 135, 54);

        doc.setFont("Helvetica", "bold");
        doc.text("Report Status:", 110, 60);
        doc.setFont("Helvetica", "normal");
        doc.text("Pending Approval", 135, 60);

        // Section 2: Stock in System
        doc.setFont("Helvetica", "bold");
        doc.setFontSize(9.5);
        doc.setTextColor(...primaryColor);
        doc.text("2. STOCK IN SYSTEM", 15, 83);

        // Table Header
        doc.setFillColor(...primaryColor);
        doc.rect(15, 87, 180, 7, 'F');
        doc.setFontSize(8.5);
        doc.setTextColor(255, 255, 255);
        doc.setFont("Helvetica", "bold");
        doc.text("Description", 20, 91.5);
        doc.text("System Count", 120, 91.5);

        // Table Rows
        const assetData = [
            ["Item Description", description],
            ["Total System Stock", `${sysBalance} units`],
            ["Previous Difference", `${prevVar} units`],
            ["Available in Stock", `${availQty} units`],
            ["Out on Loan", `${activeLoans} units`]
        ];

        let currentY = 94;
        doc.setTextColor(...slateDark);
        assetData.forEach((row, i) => {
            currentY = 94 + (i * 7.5);
            // Zebra striping
            if (i % 2 === 1) {
                doc.setFillColor(...bgLight);
                doc.rect(15, currentY, 180, 7.5, 'F');
            }
            // Border
            doc.setDrawColor(...borderLight);
            doc.rect(15, currentY, 180, 7.5, 'S');

            doc.setFont("Helvetica", "bold");
            doc.text(row[0], 20, currentY + 5);
            doc.setFont("Helvetica", "normal");
            doc.text(String(row[1]), 120, currentY + 5);
        });

        // Section 3: Physical Count Results
        currentY += 16;
        doc.setFont("Helvetica", "bold");
        doc.setFontSize(9.5);
        doc.setTextColor(...primaryColor);
        doc.text("3. PHYSICAL CHECK RESULTS", 15, currentY);

        // Grid Layout
        currentY += 4;
        
        // Box 1: Physical Count
        doc.setFillColor(...bgLight);
        doc.roundedRect(15, currentY, 56, 20, 2, 2, 'F');
        doc.setDrawColor(...borderLight);
        doc.roundedRect(15, currentY, 56, 20, 2, 2, 'S');
        doc.setFontSize(7.5);
        doc.setTextColor(...textMuted);
        doc.text("PHYSICAL COUNT", 20, currentY + 5.5);
        doc.setFontSize(13);
        doc.setTextColor(...slateDark);
        doc.setFont("Helvetica", "bold");
        doc.text(String(physical), 20, currentY + 14);

        // Box 2: Difference (Variance)
        const varNum = parseFloat(variance) || 0;
        let boxBg = [248, 250, 252];
        let boxBorder = borderLight;
        let boxText = slateDark;
        if (varNum === 0) {
            boxBg = [240, 253, 244]; // Greenish
            boxBorder = [187, 247, 208];
            boxText = [22, 101, 52];
        } else if (varNum < 0) {
            boxBg = [254, 242, 242]; // Reddish
            boxBorder = [254, 202, 202];
            boxText = [153, 27, 27];
        } else {
            boxBg = [245, 243, 255]; // Purplish
            boxBorder = [233, 213, 255];
            boxText = [107, 33, 168];
        }

        doc.setFillColor(...boxBg);
        doc.roundedRect(77, currentY, 56, 20, 2, 2, 'F');
        doc.setDrawColor(...boxBorder);
        doc.roundedRect(77, currentY, 56, 20, 2, 2, 'S');
        doc.setFontSize(7.5);
        doc.setTextColor(...textMuted);
        doc.text("DIFFERENCE", 82, currentY + 5.5);
        doc.setFontSize(13);
        doc.setTextColor(...boxText);
        doc.setFont("Helvetica", "bold");
        doc.text(variance, 82, currentY + 14);

        // Box 3: Condition
        doc.setFillColor(...bgLight);
        doc.roundedRect(139, currentY, 56, 20, 2, 2, 'F');
        doc.setDrawColor(...borderLight);
        doc.roundedRect(139, currentY, 56, 20, 2, 2, 'S');
        doc.setFontSize(7.5);
        doc.setTextColor(...textMuted);
        doc.text("ITEM CONDITION", 144, currentY + 5.5);
        doc.setFontSize(10.5);
        doc.setTextColor(...slateDark);
        doc.setFont("Helvetica", "bold");
        doc.text(condition, 144, currentY + 13.5);

        // Section 4: Remarks
        currentY += 28;
        doc.setFont("Helvetica", "bold");
        doc.setFontSize(9);
        doc.setTextColor(...slateDark);
        doc.text("Verification Notes:", 15, currentY);
        
        currentY += 4;
        // Callout card
        doc.setFillColor(...bgLight);
        doc.rect(15, currentY, 180, 24, 'F');
        doc.setDrawColor(...borderLight);
        doc.rect(15, currentY, 180, 24, 'S');
        
        doc.setFillColor(...primaryColor);
        doc.rect(15, currentY, 1.5, 24, 'F');

        doc.setFont("Helvetica", "normal");
        doc.setFontSize(8.5);
        doc.setTextColor(...textMuted);
        
        // Wrap notes text
        const splitRemarks = doc.splitTextToSize(remarks, 172);
        doc.text(splitRemarks, 20, currentY + 6);

        // Signature Section
        currentY += 40;
        doc.setDrawColor(...borderLight);
        doc.line(15, currentY, 80, currentY);
        doc.line(130, currentY, 195, currentY);

        currentY += 5;
        doc.setFont("Helvetica", "bold");
        doc.setFontSize(8.5);
        doc.setTextColor(...slateDark);
        doc.text("Auditor Signature", 15, currentY);
        doc.text("Admin Approval Signature", 130, currentY);

        currentY += 4;
        doc.setFont("Helvetica", "normal");
        doc.setFontSize(8);
        doc.setTextColor(...textMuted);
        doc.text(`Rank/Name: ${verifierRank} ${verifierName}`, 15, currentY);
        doc.text("Date: ________________________", 130, currentY);

        // Page Footer
        doc.setFont("Helvetica", "normal");
        doc.setFontSize(7.5);
        doc.setTextColor(...textMuted);
        doc.text("Page 1 of 1  ·  NACOC Stores Logistics & Inventory Control System", 105, 282, { align: "center" });

        // Print PDF
        doc.autoPrint();
        const blobUrl = doc.output('bloburl');
        window.open(blobUrl, '_blank');
    };

    logoImg.onload = () => buildPdf(logoImg);
    logoImg.onerror = () => buildPdf(null);
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

function openBatchVerifyPage() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    if (checkedBoxes.length === 0) return;

    const descriptions = [];
    checkedBoxes.forEach(cb => {
        descriptions.push(cb.getAttribute('data-description'));
    });

    const params = new URLSearchParams();
    descriptions.forEach(desc => {
        params.append('descriptions[]', desc);
    });

    window.location.href = `{{ route('stockcheck.batch') }}?${params.toString()}`;
}
</script>
@endsection
