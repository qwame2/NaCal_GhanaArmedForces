@extends('layouts.dashboard')

@section('content')
<style>
    /* Premium Visual Design Styles */
    .overdue-container {
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    .glass-card-header {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05);
    }

    .premium-stat-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.07) 0%, rgba(255, 255, 255, 0.02) 100%);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    }

    .premium-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        border-color: rgba(136, 19, 55, 0.2);
    }

    .tab-btn {
        padding: 0.75rem 1.5rem;
        font-family: 'Outfit', sans-serif;
        font-size: 0.9rem;
        font-weight: 800;
        color: var(--text-muted, #94a3b8);
        background: rgba(136, 19, 55, 0.03);
        border: 1px solid rgba(136, 19, 55, 0.1);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .tab-btn.active {
        color: #ffffff !important;
        background: linear-gradient(135deg, #881337 0%, #9f1239 100%) !important;
        border-color: rgba(255, 255, 255, 0.2) !important;
        box-shadow: 0 8px 24px rgba(136, 19, 55, 0.35);
    }

    .tab-btn:not(.active):hover {
        background: rgba(136, 19, 55, 0.08) !important;
        color: var(--primary, #881337) !important;
        border-color: rgba(136, 19, 55, 0.25) !important;
    }

    .premium-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .premium-table th {
        padding: 1.25rem 1.5rem;
        font-size: 0.78rem;
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: 800;
        letter-spacing: 0.05em;
        border-bottom: 2px solid rgba(255, 255, 255, 0.05);
    }

    .premium-table td {
        padding: 1.25rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        font-size: 0.9rem;
    }

    .premium-table tr {
        transition: all 0.25s ease;
    }

    .premium-table tr:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .overdue-blink {
        animation: subtleBlink 1.5s infinite alternate;
    }

    @keyframes subtleBlink {
        0% { opacity: 1; transform: scale(1); }
        100% { opacity: 0.7; transform: scale(0.97); }
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Glow badges */
    .glow-badge-red {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #f87171;
        box-shadow: 0 0 10px rgba(239, 68, 68, 0.1);
    }

    .glow-badge-orange {
        background: rgba(136, 19, 55, 0.1);
        border: 1px solid rgba(136, 19, 55, 0.2);
        color: #fbbf24;
        box-shadow: 0 0 10px rgba(136, 19, 55, 0.1);
    }

    .dept-badge {
        font-size: 0.75rem;
        font-weight: 800;
        padding: 0.25rem 0.65rem;
        border-radius: 8px;
        text-transform: uppercase;
    }
</style>

<div class="overdue-container">
    <!-- Header Hero Glass Card -->
    <div class="glass-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <span class="dept-badge glow-badge-red overdue-blink">
                    <i data-lucide="clock" style="width: 12px; height: 12px; display: inline-block; vertical-align: middle; margin-right: 2px;"></i>
                    Strict Asset Governance
                </span>
                <span style="color: var(--text-muted); font-size: 0.85rem;">Temporary Loan Ledger</span>
            </div>
            <h2 style="font-size: 2.25rem; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.03em;">
                Outstanding <span style="color: #ef4444;">Temporary Assets</span>
            </h2>
            <p style="color: var(--text-muted); margin: 0.5rem 0 0 0; font-size: 1rem; font-weight: 500;">
                @if($isStoresHead)
                    Monitor and coordinate recovery for all outstanding temporary requisitions across all command departments.
                @else
                    Monitor and review all active temporary assets and overdue returns borrowed by the <b>{{ auth()->user()->department }}</b> department.
                @endif
            </p>
        </div>
        
        <div>
            <button onclick="window.location.reload()" class="glass-card" style="padding: 0.75rem 1.5rem; border: 1px solid var(--border-color); border-radius: 14px; background: rgba(255,255,255,0.03); cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--text-main); transition: 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
                <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i>
                Refresh Ledger
            </button>
        </div>
    </div>

    <!-- Quick Statistics Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
        <!-- Overdue Card -->
        <div class="premium-stat-card">
            <div style="width: 50px; height: 50px; background: rgba(239, 68, 68, 0.1); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.15);">
                <i data-lucide="alert-circle" style="width: 24px; height: 24px; animation: subtleBlink 1.5s infinite alternate;"></i>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Overdue Assets</div>
                <div style="font-size: 1.75rem; font-weight: 900; color: #ef4444; margin-top: 2px;">{{ $overdueItems->count() }}</div>
            </div>
        </div>

        <!-- Active/Due Soon Card -->
        <div class="premium-stat-card">
            <div style="width: 50px; height: 50px; background: rgba(136, 19, 55, 0.1); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #881337; border: 1px solid rgba(136, 19, 55, 0.15);">
                <i data-lucide="calendar" style="width: 24px; height: 24px;"></i>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Due Soon / Active</div>
                <div style="font-size: 1.75rem; font-weight: 900; color: #881337; margin-top: 2px;">{{ $dueSoonItems->count() }}</div>
            </div>
        </div>

        <!-- Combined Total Card -->
        <div class="premium-stat-card">
            <div style="width: 50px; height: 50px; background: rgba(136, 19, 55, 0.1); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: var(--primary); border: 1px solid rgba(136, 19, 55, 0.15);">
                <i data-lucide="layers" style="width: 24px; height: 24px;"></i>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Total Active Loans</div>
                <div style="font-size: 1.75rem; font-weight: 900; color: var(--text-main); margin-top: 2px;">{{ $overdueItems->count() + $dueSoonItems->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Interactive Navigation Tabs -->
    <div style="display: flex; gap: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 2rem;">
        <button onclick="switchTab('overdue')" id="tab-overdue" class="tab-btn active">
            <i data-lucide="alert-triangle" style="width: 16px; height: 16px;"></i>
            Overdue Returns ({{ $overdueItems->count() }})
        </button>
        <button onclick="switchTab('active')" id="tab-active" class="tab-btn">
            <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
            Active Loans &amp; Due Soon ({{ $dueSoonItems->count() }})
        </button>
    </div>

    <!-- Overdue Table View -->
    <div id="view-overdue" class="glass-card" style="padding: 1.5rem; border-radius: 20px; overflow: hidden; display: block;">
        <div class="table-scroll-wrapper" style="overflow-x: auto; width: 100%;">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Requisition Ref</th>
                        <th>Borrowed Item</th>
                        <th>Category</th>
                        @if($isStoresHead)
                            <th>Department</th>
                        @endif
                        <th>Issued To</th>
                        <th style="text-align: center;">Issued Qty</th>
                        <th style="text-align: center;">Returned Qty</th>
                        <th style="text-align: center; color: #ef4444;">Outstanding</th>
                        <th>Issuance Date</th>
                        <th style="color: #ef4444;">Expected Return</th>
                        <th>Severity Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overdueItems as $item)
                        <tr>
                            <td style="font-weight: 800; color: var(--text-main);">
                                <span style="background: rgba(255,255,255,0.05); padding: 0.35rem 0.65rem; border-radius: 8px;">
                                    #{{ $item->requisition_id }}
                                </span>
                            </td>
                            <td style="font-weight: 700; color: var(--text-main);">{{ $item->description }}</td>
                            <td>
                                <span class="dept-badge" style="background: rgba(136, 19, 55, 0.08); color: var(--primary); border: 1px solid rgba(136, 19, 55, 0.15);">
                                    {{ $item->category }}
                                </span>
                            </td>
                            @if($isStoresHead)
                                <td>
                                    <span class="dept-badge" style="background: rgba(239, 68, 68, 0.08); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.15);">
                                        {{ $item->department }}
                                    </span>
                                </td>
                            @endif
                            <td style="font-weight: 600; color: var(--text-muted);">{{ $item->requester_name }}</td>
                            <td style="text-align: center; font-weight: 700;">{{ $item->qty_issued }}</td>
                            <td style="text-align: center; font-weight: 700; color: #881337;">{{ $item->qty_returned }}</td>
                            <td style="text-align: center; font-weight: 900; color: #ef4444; font-size: 1rem;">{{ $item->qty_outstanding }}</td>
                            <td style="color: var(--text-muted); font-weight: 600;">{{ $item->issuance_date }}</td>
                            <td style="color: #ef4444; font-weight: 800;">{{ $item->expected_return }}</td>
                            <td>
                                <span class="dept-badge glow-badge-red overdue-blink" style="padding: 0.35rem 0.75rem;">
                                    {{ abs($item->days_diff) }} Days Overdue
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isStoresHead ? 11 : 10 }}" style="padding: 6rem 2rem; text-align: center; vertical-align: middle;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1rem; margin: 0 auto; max-width: 450px;">
                                    <div style="background: rgba(136, 19, 55, 0.05); width: 80px; height: 80px; border-radius: 24px; display: flex; align-items: center; justify-content: center; color: #881337; border: 2px dashed rgba(136, 19, 55, 0.2);">
                                        <i data-lucide="shield-check" style="width: 38px; height: 38px;"></i>
                                    </div>
                                    <h4 style="font-size: 1.4rem; font-weight: 900; color: var(--text-main); margin: 0.5rem 0 0 0;">Ledger fully compliant</h4>
                                    <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.5; margin: 0;">
                                        Outstanding temporary returns are clean. No overdue assets were found for your department ledger.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Active / Due Soon Table View -->
    <div id="view-active" class="glass-card" style="padding: 1.5rem; border-radius: 20px; overflow: hidden; display: none;">
        <div class="table-scroll-wrapper" style="overflow-x: auto; width: 100%;">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Requisition Ref</th>
                        <th>Borrowed Item</th>
                        <th>Category</th>
                        @if($isStoresHead)
                            <th>Department</th>
                        @endif
                        <th>Issued To</th>
                        <th style="text-align: center;">Issued Qty</th>
                        <th style="text-align: center;">Returned Qty</th>
                        <th style="text-align: center; color: #881337;">Outstanding</th>
                        <th>Issuance Date</th>
                        <th style="color: #881337;">Expected Return</th>
                        <th>Status / Timeline</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dueSoonItems as $item)
                        <tr>
                            <td style="font-weight: 800; color: var(--text-main);">
                                <span style="background: rgba(255,255,255,0.05); padding: 0.35rem 0.65rem; border-radius: 8px;">
                                    #{{ $item->requisition_id }}
                                </span>
                            </td>
                            <td style="font-weight: 700; color: var(--text-main);">{{ $item->description }}</td>
                            <td>
                                <span class="dept-badge" style="background: rgba(136, 19, 55, 0.08); color: var(--primary); border: 1px solid rgba(136, 19, 55, 0.15);">
                                    {{ $item->category }}
                                </span>
                            </td>
                            @if($isStoresHead)
                                <td>
                                    <span class="dept-badge" style="background: rgba(136, 19, 55, 0.08); color: #fbbf24; border: 1px solid rgba(136, 19, 55, 0.15);">
                                        {{ $item->department }}
                                    </span>
                                </td>
                            @endif
                            <td style="font-weight: 600; color: var(--text-muted);">{{ $item->requester_name }}</td>
                            <td style="text-align: center; font-weight: 700;">{{ $item->qty_issued }}</td>
                            <td style="text-align: center; font-weight: 700; color: #881337;">{{ $item->qty_returned }}</td>
                            <td style="text-align: center; font-weight: 900; color: #881337; font-size: 1rem;">{{ $item->qty_outstanding }}</td>
                            <td style="color: var(--text-muted); font-weight: 600;">{{ $item->issuance_date }}</td>
                            <td style="color: #881337; font-weight: 800;">{{ $item->expected_return }}</td>
                            <td>
                                @if($item->days_diff === 0)
                                    <span class="dept-badge glow-badge-orange overdue-blink" style="padding: 0.35rem 0.75rem;">
                                        Due Today
                                    </span>
                                @else
                                    <span class="dept-badge glow-badge-orange" style="padding: 0.35rem 0.75rem; background: rgba(136, 19, 55, 0.08); border-color: rgba(136, 19, 55, 0.2); color: #34d399; box-shadow: none;">
                                        {{ $item->days_diff }} Days Left
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isStoresHead ? 11 : 10 }}" style="padding: 6rem 2rem; text-align: center; vertical-align: middle;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1rem; margin: 0 auto; max-width: 450px;">
                                    <div style="background: rgba(255, 255, 255, 0.02); width: 80px; height: 80px; border-radius: 24px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); border: 2px dashed rgba(255, 255, 255, 0.08);">
                                        <i data-lucide="package-search" style="width: 38px; height: 38px;"></i>
                                    </div>
                                    <h4 style="font-size: 1.4rem; font-weight: 900; color: var(--text-main); margin: 0.5rem 0 0 0;">No active loans</h4>
                                    <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.5; margin: 0;">
                                        There are no active temporary assets currently checked out.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function switchTab(tab) {
        // Toggle active button style
        document.getElementById('tab-overdue').classList.toggle('active', tab === 'overdue');
        document.getElementById('tab-active').classList.toggle('active', tab === 'active');
        
        // Toggle view containers
        document.getElementById('view-overdue').style.display = (tab === 'overdue' ? 'block' : 'none');
        document.getElementById('view-active').style.display = (tab === 'active' ? 'block' : 'none');
    }
</script>
@endsection
