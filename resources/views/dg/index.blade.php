@extends('layouts.dashboard')

@section('content')
<style>
    :root {
        --dg-primary: #6366f1;
        --dg-primary-hover: #4f46e5;
        --dg-slate: #0f172a;
        --dg-slate-light: #1e293b;
        --dg-danger-glow: rgba(239, 68, 68, 0.08);
        --dg-warning-glow: rgba(245, 158, 11, 0.08);
        --dg-info-glow: rgba(59, 130, 246, 0.08);
        --dg-success-glow: rgba(16, 185, 129, 0.08);
        --shadow-premium: 0 20px 40px -15px rgba(15, 23, 42, 0.05), 0 0 0 1px rgba(15, 23, 42, 0.03);
    }

    .dg-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: var(--shadow-premium);
        transition: transform 0.25s, box-shadow 0.25s;
    }

    .dg-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 30px 60px -15px rgba(15, 23, 42, 0.08);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 950;
        letter-spacing: -0.05em;
        line-height: 1;
        margin-top: 4px;
        color: var(--text-main);
    }

    /* Tabs navigation */
    .dg-tabs-container {
        display: flex;
        background: rgba(0, 0, 0, 0.02);
        border: 1px solid var(--border-color);
        padding: 6px;
        border-radius: 16px;
        gap: 6px;
        margin-bottom: 2rem;
        width: fit-content;
        max-width: 100%;
        overflow-x: auto;
    }

    .dg-tab-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        border: none;
        background: transparent;
        color: var(--text-muted);
        font-weight: 800;
        font-size: 0.82rem;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dg-tab-btn.active {
        background: var(--bg-card);
        color: var(--dg-primary);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05), 0 0 0 1px rgba(99, 102, 241, 0.1);
    }

    .dg-tab-panel {
        display: none;
        animation: fadeInPanel 0.4s ease;
    }

    .dg-tab-panel.active {
        display: block;
    }

    @keyframes fadeInPanel {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Rows layout */
    .dg-row {
        border-bottom: 1px solid var(--border-color);
        transition: background 0.2s;
    }

    .dg-row:hover {
        background: rgba(99, 102, 241, 0.01);
    }

    .dg-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 0.68rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .dg-badge.danger { background: var(--dg-danger-glow); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    .dg-badge.warning { background: var(--dg-warning-glow); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
    .dg-badge.info { background: var(--dg-info-glow); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }
    .dg-badge.success { background: var(--dg-success-glow); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }

    .dg-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .dg-table th {
        padding: 1rem 1.25rem;
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        background: rgba(0, 0, 0, 0.01);
        border-bottom: 1px solid var(--border-color);
    }

    .dg-table td {
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.85rem;
        color: var(--text-main);
        vertical-align: middle;
    }

    .dg-table tr:last-child td {
        border-bottom: none;
    }

    .filter-card-dg {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 18px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        box-shadow: var(--shadow-premium);
    }

    .filter-control-dg {
        padding: 0.65rem 1rem;
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-main);
        color: var(--text-main);
        font-size: 0.85rem;
        font-weight: 600;
        outline: none;
        transition: all 0.2s;
        min-width: 160px;
    }

    .filter-control-dg:focus {
        border-color: var(--dg-primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        background: white;
    }

    .badge-event {
        background: var(--bg-main);
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        font-size: 0.7rem;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: 6px;
        text-transform: uppercase;
    }

    /* Precision Pagination Module */
    .dg-pagination-container {
        padding: 1.5rem 1.75rem;
        background: rgba(0, 0, 0, 0.01);
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .dg-pagination-info {
        font-size: 0.82rem;
        color: var(--text-muted);
        font-weight: 700;
    }

    .dg-pagination-info span {
        color: var(--text-main);
        font-weight: 800;
    }

    .dg-pagination-buttons {
        display: flex;
        gap: 8px;
    }

    .dg-page-btn {
        padding: 0.55rem 1.1rem;
        background: var(--bg-card);
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        color: var(--text-main);
        font-weight: 800;
        font-size: 0.8rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        cursor: pointer;
    }

    .dg-page-btn:hover:not(.disabled) {
        background: var(--dg-primary);
        color: white;
        border-color: var(--dg-primary);
        transform: translateY(-1.5px);
        box-shadow: 0 8px 16px rgba(99, 102, 241, 0.2);
    }

    .dg-page-btn.disabled {
        background: var(--bg-main);
        color: var(--text-muted);
        border-color: var(--border-color);
        cursor: not-allowed;
        box-shadow: none;
        opacity: 0.6;
    }

    .online-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .online-indicator::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .online-indicator.online {
        color: #10b981;
    }

    .online-indicator.online::before {
        background: #10b981;
        box-shadow: 0 0 8px #10b981;
        animation: pulse-online 2s infinite;
    }

    .online-indicator.offline {
        color: var(--text-muted);
    }

    .online-indicator.offline::before {
        background: #94a3b8;
    }

    @keyframes pulse-online {
        0% { transform: scale(0.9); opacity: 0.8; }
        50% { transform: scale(1.1); opacity: 1; }
        100% { transform: scale(0.9); opacity: 0.8; }
    }

    @media(max-width: 1024px) {
        .workflow-info-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* ── Workflow Redesign ── */
    .workflow-card-modern {
        background: white;
        border-radius: 28px;
        border: 1.5px solid var(--border-color);
        box-shadow: 0 10px 30px rgba(79, 70, 229, 0.03);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .workflow-card-modern:hover {
        border-color: #c7d2fe;
        box-shadow: 0 16px 40px rgba(79, 70, 229, 0.06);
    }

    .workflow-cat-grid-modern {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1.25rem;
    }

    .workflow-cat-card-modern {
        background: var(--bg-main);
        border: 2px solid var(--border-color);
        border-radius: 20px;
        padding: 1.25rem 1.5rem;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 14px;
        position: relative;
        overflow: hidden;
        user-select: none;
    }

    .workflow-cat-card-modern:hover {
        border-color: #cbd5e1;
        transform: translateY(-2px);
        background: #ffffff;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.02);
    }

    .workflow-cat-card-modern.active {
        background: linear-gradient(145deg, #f5f7ff 0%, #edf1ff 100%);
        border-color: #8b5cf6;
        box-shadow: 0 8px 24px rgba(139, 92, 246, 0.06);
    }

    .workflow-cat-card-modern.active:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(139, 92, 246, 0.1);
    }

    .workflow-cat-card-modern .corner-glow {
        position: absolute;
        top: -20px;
        right: -20px;
        width: 50px;
        height: 50px;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.2) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.25s ease;
        pointer-events: none;
    }

    .workflow-cat-card-modern.active .corner-glow {
        opacity: 1;
    }

    .workflow-cat-card-modern .cat-circle {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: #ffffff;
        color: #8b5cf6;
        font-weight: 900;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #e2e8f0;
        transition: all 0.25s ease;
        flex-shrink: 0;
    }

    .workflow-cat-card-modern.active .cat-circle {
        background: linear-gradient(135deg, #8b5cf6, #6d28d9);
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 4px 8px rgba(139, 92, 246, 0.18);
    }

    .workflow-cat-card-modern .status-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: #64748b;
        margin-top: 2px;
        transition: color 0.25s;
    }

    .workflow-cat-card-modern.active .status-label {
        color: #8b5cf6;
    }

    .workflow-cat-card-modern .indicator-dot {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s ease;
        flex-shrink: 0;
        margin-left: auto;
    }

    .workflow-cat-card-modern.active .indicator-dot {
        background: #8b5cf6;
        border-color: #8b5cf6;
        box-shadow: 0 2px 6px rgba(139, 92, 246, 0.25);
    }

    .flow-line {
        flex: 1;
        height: 3px;
        transition: all 0.4s ease;
        background: #cbd5e1;
        margin-top: -20px;
    }

    .flow-line.active {
        background: #4f46e5;
        box-shadow: 0 0 8px rgba(79, 70, 229, 0.25);
    }

    .flow-line.dashed {
        background: repeating-linear-gradient(to right, #cbd5e1 0px, #cbd5e1 6px, transparent 6px, transparent 12px);
    }

    .flow-node-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .flow-node-badge {
        font-size: 0.6rem;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 30px;
        transition: all 0.3s ease;
    }
</style>

<div style="padding: 2rem;">

    {{-- Header --}}
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <div style="font-size: .7rem; font-weight: 800; color: var(--dg-primary); text-transform: uppercase; letter-spacing: .12em;">
                    Director General Command Center
                </div>
                
            </div>
            <h1 style="font-size: 1.75rem; font-weight: 900; color: var(--text-main); letter-spacing: -.03em; margin: 0;">
                Director General's Oversight
            </h1>

        </div>
        <div style="display: flex; gap: 10px;">
            <a id="btn-print-dg-report" href="{{ route('dg.print') }}?date_from={{ request('date_from') }}&date_to={{ request('date_to') }}" target="_blank" class="glass-card" style="padding: 0.75rem 1.25rem; text-decoration: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 800; color: var(--dg-primary); border-radius: 12px; border: 1.5px solid var(--dg-primary); background: rgba(99,102,241,0.05); transition: all 0.2s;" onmouseover="this.style.background='rgba(99,102,241,0.1)'" onmouseout="this.style.background='rgba(99,102,241,0.05)'">
                <i data-lucide="printer" style="width: 18px;"></i>
                Print Consolidated Ledger
            </a>
            <button id="btn-refresh-dg" onclick="window.location.reload()" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--text-main); border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-card);">
                <i data-lucide="refresh-cw" style="width: 18px;"></i>
                Refresh
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
        <div class="dg-card">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                <div style="width: 32px; height: 32px; background: rgba(99,102,241,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="layers" style="color: var(--dg-primary); width: 16px;"></i></div>
                Total Store Items
            </div>
            <div class="stat-number">{{ number_format($totalItemsCount) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Total unique items in stores inventory</div>
        </div>

        <div class="dg-card">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                <div style="width: 32px; height: 32px; background: rgba(239,68,68,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="scale" style="color: #ef4444; width: 16px;"></i></div>
                Stock Variance
            </div>
            <div class="stat-number" style="color: {{ $totalVariance > 0 ? '#ef4444' : 'var(--text-main)' }};">{{ number_format($totalVariance) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Active inventory discrepancies</div>
        </div>

        <div class="dg-card">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                <div style="width: 32px; height: 32px; background: rgba(139,92,246,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="shopping-bag" style="color: #8b5cf6; width: 16px;"></i></div>
                Total Items Issued
            </div>
            <div class="stat-number">{{ number_format($totalItemsIssued) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Total number of items disbursed</div>
        </div>

        <div class="dg-card">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                <div style="width: 32px; height: 32px; background: rgba(16,185,129,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="file-text" style="color: #10b981; width: 16px;"></i></div>
                Pending Requisitions
            </div>
            <div class="stat-number" style="color: {{ $pendingRequisitionsCount > 0 ? '#ea580c' : 'var(--text-main)' }}">{{ number_format($pendingRequisitionsCount) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Requisitions awaiting approval</div>
        </div>

        <div class="dg-card">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                <div style="width: 32px; height: 32px; background: rgba(59,130,246,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="users" style="color: #3b82f6; width: 16px;"></i></div>
                Approved Officers
            </div>
            <div class="stat-number">{{ number_format($totalActiveUsers) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Active accounts in the system</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form id="dg-filter-form" action="{{ route('dg.dashboard') }}" method="GET" class="filter-card-dg">
        <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">
            Oversight Filters & Search
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <input type="text" id="dg-search-query" name="search_query" class="filter-control-dg" placeholder="Search entries, descriptions, names..." value="{{ request('search_query') }}" style="flex: 1; min-width: 240px;">

            <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">From:</span>
            <input type="date" id="dg-date-from" name="date_from" class="filter-control-dg" title="From Date" value="{{ request('date_from') }}">

            <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">To:</span>
            <input type="date" id="dg-date-to" name="date_to" class="filter-control-dg" title="To Date" value="{{ request('date_to') }}">

            <select id="dg-req-status" name="req_status" class="filter-control-dg">
                <option value="">-- Status (Reqs) --</option>
                <option value="pending" {{ request('req_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('req_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="partially_approved" {{ request('req_status') === 'partially_approved' ? 'selected' : '' }}>Partially Approved</option>
                <option value="declined" {{ request('req_status') === 'declined' ? 'selected' : '' }}>Declined</option>
            </select>

            @if(request()->anyFilled(['search_query', 'date_from', 'date_to', 'req_status']))
                <a id="btn-dg-clear" href="{{ route('dg.dashboard') }}" class="filter-control-dg" style="background: rgba(239, 68, 68, 0.05); color: #ef4444; border: 1.5px solid #ef4444; text-decoration: none; text-align: center; font-weight: 800; min-width: 100px; display: inline-flex; align-items: center; justify-content: center;">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- Tabs Menu --}}
    <div class="dg-tabs-container">
        <button id="tab-btn-stock-oversight" class="dg-tab-btn active" onclick="switchDGTab('dg-stock-oversight-tab', this)">
            <i data-lucide="archive" style="width: 16px;"></i>
            Items Received
        </button>
        <button id="tab-btn-staff-reqs" class="dg-tab-btn" onclick="switchDGTab('dg-staff-reqs-tab', this)">
            <i data-lucide="file-text" style="width: 16px;"></i>
            Staff Requisitions
            @php
                $dgPendingCount = \App\Models\StoreRequisition::get()->filter(function($r) {
                    return $r->is_ready_for_dg_approval;
                })->count();
            @endphp
            @if($dgPendingCount > 0)
                <span id="dg-staff-reqs-badge" style="background: #ef4444; color: white; border-radius: 999px; padding: 2px 6px; min-width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 900; line-height: 1; margin-left: 4px; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);">
                    {{ $dgPendingCount }}
                </span>
            @endif
        </button>
        <button id="tab-btn-user-presence" class="dg-tab-btn" onclick="switchDGTab('dg-user-presence-tab', this)">
            <i data-lucide="users" style="width: 16px;"></i>
            Approved Officers Overview
        </button>
        <button id="tab-btn-issued-returned" class="dg-tab-btn" onclick="switchDGTab('dg-issued-returned-tab', this)">
            <i data-lucide="clipboard-list" style="width: 16px;"></i>
            Issued &amp; Returned Items
        </button>
        <button id="tab-btn-workflow-config" class="dg-tab-btn" onclick="switchDGTab('dg-workflow-config-tab', this)">
            <i data-lucide="user-cog" style="width: 16px;"></i>
            Approval Workflow Configuration
        </button>
    </div>

    {{-- Tab Panels --}}

    {{-- PANEL 1: SYSTEM AUDIT TRAIL --}}
    {{-- PANEL 2: STOCK BALANCE REGISTRY --}}
    <div id="dg-stock-oversight-tab" class="dg-tab-panel active">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; box-shadow: var(--shadow-premium); position: relative;">
            <div style="position: relative;">
                <table class="dg-table" style="border-radius: 20px;">
                    <thead>
                        <tr>
                            <th>Entry Date</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Qty Received</th>
                            <th>Stock Bal.</th>
                            <th>Variance</th>
                            <th>Acquisition</th>
                            <th>Supplier / Donor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventoryItems as $item)
                            <tr class="dg-row">
                                <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
                                    {{ $item->entry_date ? \Carbon\Carbon::parse($item->entry_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td style="color: var(--text-main);">
                                    <div style="font-weight: 800;">{{ $item->description }}</div>
                                    @if(!empty($item->serial_number))
                                        <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 3px;">
                                            <span style="font-weight: 700;">S/N:</span> <code style="font-family: monospace; background: rgba(0,0,0,0.05); padding: 1px 4px; border-radius: 4px;">{{ $item->serial_number }}</code>
                                        </div>
                                    @endif
                                    @if(!empty($item->remarks))
                                        <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 3px; font-style: italic;">
                                            {{ $item->remarks }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge-event">{{ $ledgeMap[$item->ledge_category] ?? $item->ledge_category }}</span>
                                </td>
                                <td style="font-weight: 800;">
                                    {{ number_format($item->qty) }} <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $item->unit }}</span>
                                </td>
                                <td style="font-weight: 800; color: var(--dg-primary);">
                                    {{ number_format($item->stock_balance) }} <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $item->unit }}</span>
                                </td>
                                <td style="font-weight: 800; color: {{ $item->variance > 0 ? '#ef4444' : 'var(--text-main)' }};">
                                    {{ number_format($item->variance) }}
                                </td>
                                <td style="font-weight: 700;">
                                    {{ $item->acquisition_type }}
                                </td>
                                <td style="position: relative;">
                                    @if($item->supplier_name)
                                        @php
                                            $registry = \App\Models\Setting::get('suppliers_registry', []);
                                            $supDetails = $registry[$item->supplier_name] ?? null;
                                        @endphp
                                         <div style="display: flex; align-items: center; gap: 6px;">
                                             <div style="font-weight: 800; color: var(--text-main);">{{ $item->supplier_name }}</div>
                                             @if($supDetails)
                                                 <button onclick="toggleSupplierDetails(this)" style="background: rgba(99, 102, 241, 0.1); border: 1.5px solid rgba(99, 102, 241, 0.2); cursor: pointer; padding: 4px; color: var(--dg-primary); display: inline-flex; align-items: center; justify-content: center; border-radius: 9999px; transition: all 0.2s; outline: none; margin-left: 4px;" class="supplier-toggle-btn" onmouseover="this.style.background='rgba(99, 102, 241, 0.2)'; this.style.borderColor='rgba(99, 102, 241, 0.3)';" onmouseout="this.style.background='rgba(99, 102, 241, 0.1)'; this.style.borderColor='rgba(99, 102, 241, 0.2)';">
                                                     <i data-lucide="chevron-down" style="width: 12px; height: 12px; stroke-width: 3.5;"></i>
                                                 </button>
                                             @endif
                                         </div>
                                         @if($supDetails)
                                             <div class="supplier-details-container" style="position: absolute; top: calc(100% - 4px); right: 12px; z-index: 999; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); width: 240px; display: none; flex-direction: column; gap: 6px; line-height: 1.3; text-align: left;">
                                                 <div style="font-size: 0.65rem; font-weight: 800; color: var(--dg-primary); text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid var(--border-color); padding-bottom: 4px; margin-bottom: 4px;">
                                                     Supplier Details
                                                 </div>
                                                 @if(!empty($supDetails['phone']))
                                                     <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.72rem; color: var(--text-main); font-weight: 600;">
                                                         <i data-lucide="phone" style="width: 12px; height: 12px; stroke-width: 2.5; color: var(--dg-primary);"></i> {{ $supDetails['phone'] }}
                                                     </span>
                                                 @endif
                                                 @if(!empty($supDetails['email']))
                                                     <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.72rem; color: var(--text-main); font-weight: 600; word-break: break-all;">
                                                         <i data-lucide="mail" style="width: 12px; height: 12px; stroke-width: 2.5; color: var(--dg-primary);"></i> {{ $supDetails['email'] }}
                                                     </span>
                                                 @endif
                                                 @if(!empty($supDetails['address']))
                                                     <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.72rem; color: var(--text-main); font-weight: 600;">
                                                         <i data-lucide="map-pin" style="width: 12px; height: 12px; stroke-width: 2.5; color: var(--dg-primary);"></i> {{ $supDetails['address'] }}
                                                     </span>
                                                 @endif
                                                 @if(!empty($supDetails['delivery_person']))
                                                     <div style="border-top: 1px solid var(--border-color); margin-top: 4px; padding-top: 6px; display: flex; flex-direction: column; gap: 3px;">
                                                         <div style="font-size: 0.6rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.03em;">Representative</div>
                                                         <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.72rem; color: var(--text-main); font-weight: 600;">
                                                             <i data-lucide="user" style="width: 12px; height: 12px; stroke-width: 2.5; color: var(--dg-primary);"></i> {{ $supDetails['delivery_person'] }}
                                                         </span>
                                                         @if(!empty($supDetails['delivery_phone']))
                                                             <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.7rem; color: var(--text-muted); font-weight: 500; padding-left: 18px;">
                                                                 {{ $supDetails['delivery_phone'] }}
                                                             </span>
                                                         @endif
                                                     </div>
                                                 @endif
                                             </div>
                                         @endif
                                    @elseif($item->donor_name)
                                        <div style="font-weight: 800; color: var(--text-main);">{{ $item->donor_name }}</div>
                                        <span style="font-size: 0.65rem; color: #a1a1aa; text-transform: uppercase; font-weight: 800;">Donor Contribution</span>
                                    @else
                                        <span style="color: var(--text-muted); font-weight: 500;">System</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
                                    <i data-lucide="archive" style="width: 40px; height: 40px; margin-bottom: 1rem; opacity: 0.25;"></i>
                                    <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No stock balance items registered.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($inventoryItems->hasPages())
                <div class="dg-pagination-container">
                    <div class="dg-pagination-info">
                        Showing <span>{{ $inventoryItems->firstItem() ?? 0 }}</span> to <span>{{ $inventoryItems->lastItem() ?? 0 }}</span> of <span>{{ $inventoryItems->total() }}</span> records
                    </div>
                    <div class="dg-pagination-buttons">
                        @if ($inventoryItems->onFirstPage())
                            <span class="dg-page-btn disabled"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</span>
                        @else
                            <a href="{{ $inventoryItems->appends(request()->query())->previousPageUrl() }}" class="dg-page-btn"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</a>
                        @endif

                        @if ($inventoryItems->hasMorePages())
                            <a href="{{ $inventoryItems->appends(request()->query())->nextPageUrl() }}" class="dg-page-btn">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></a>
                        @else
                            <span class="dg-page-btn disabled">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- PANEL 3: STAFF REQUISITIONS REGISTRY --}}
    <div id="dg-staff-reqs-tab" class="dg-tab-panel">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-premium);">
            <div style="overflow-x: auto;">
                <table class="dg-table">
                    <thead>
                        <tr>
                            <th>Requisition ID</th>
                            <th>Department</th>
                            <th>Items</th>
                            <th>Priority</th>
                            <th>Usage</th>
                            <th>Status</th>
                            <th>Date Requested</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requisitions as $req)
                            <tr class="dg-row">
                                <td style="font-weight: 900; font-family: monospace; color: var(--dg-primary); position: relative;">
                                    {{ $req->unique_id }}
                                </td>
                                <td style="font-weight: 700; color: var(--text-muted);">
                                    {{ $req->department }}
                                </td>
                                <td>
                                    <div style="display: flex; flex-wrap: wrap; gap: 4px; max-width: 280px; align-items: center;">
                                        @foreach($req->items->take(3) as $item)
                                        <span style="font-size: .7rem; font-weight: 700; color: var(--text-main); background: var(--bg-main); border: 1px solid var(--border-color); padding: 2px 8px; border-radius: 6px; white-space: nowrap;">
                                            {{ Str::limit($item->description, 20) }} ({{ number_format($item->quantity_requested,0) }})
                                        </span>
                                        @endforeach
                                        @if($req->items->count() > 3)
                                        <span style="font-size: .7rem; font-weight: 700; color: var(--dg-primary); background: rgba(99, 102, 241, 0.1); padding: 2px 8px; border-radius: 6px; white-space: nowrap;">+{{ $req->items->count() - 3 }} more</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php $p = $req->priority_badge; @endphp
                                    <span class="dg-badge" style="background: {{ $p['bg'] }}; color: {{ $p['color'] }}; border: 1px solid {{ $p['color'] }}40;">
                                        {{ $p['label'] }}
                                    </span>
                                </td>
                                <td>
                                    @php $u = $req->usage_type_badge; @endphp
                                    <span class="dg-badge" style="background: {{ $u['bg'] }}; color: {{ $u['color'] }}; border: 1px solid {{ $u['color'] }}40;">
                                        {{ $u['label'] }}
                                    </span>
                                </td>
                                <td>
                                    @php $s = $req->status_badge; @endphp
                                    <span class="dg-badge" style="background: {{ $s['bg'] }}; color: {{ $s['color'] }}; border: 1px solid {{ $s['color'] }}40;">
                                        {{ $s['label'] }}
                                    </span>
                                </td>
                                <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
                                    {{ $req->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                     <div style="display: flex; gap: 6px; align-items: center; justify-content: flex-start; flex-wrap: wrap;">
                                          @if(($req->dg_status ?? 'pending') !== 'pending')
                                              <button onclick="openRequisitionDetailsModal({!! htmlspecialchars(json_encode([
                                                  'id' => $req->id,
                                                  'unique_id' => $req->unique_id,
                                                  'requester_name' => $req->requester_name,
                                                  'staff_id' => $req->requester ? $req->requester->service_number : ($req->collector_staff_id ?: 'N/A'),
                                                  'department' => $req->department,
                                                  'purpose' => $req->purpose,
                                                  'date_time' => $req->created_at->format('d/m/Y H:i'),
                                                  'is_ready' => $req->is_ready_for_dg_approval,
                                                  'requires_dg' => $req->requires_dg_approval,
                                                  'dg_status' => $req->dg_status ?? 'pending',
                                                  'dg_decline_reason' => $req->dg_decline_reason,
                                                  'items' => $req->items->map(fn($i) => [
                                                      'description' => $i->description,
                                                      'quantity' => number_format($i->quantity_requested, 0),
                                                      'unit' => $i->unit
                                                  ])->toArray()
                                              ]), ENT_QUOTES, 'UTF-8') !!}, this)" class="dg-action-btn view-details" style="padding: 6px 12px; background: rgba(16, 185, 129, 0.08); border: 1.5px solid rgba(16, 185, 129, 0.2); color: #10b981; border-radius: 8px; font-size: 0.72rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: transform 0.15s;" onmouseover="this.style.background='#10b981'; this.style.color='white'; this.style.borderColor='#10b981';" onmouseout="this.style.background='rgba(16, 185, 129, 0.08)'; this.style.color='#10b981'; this.style.borderColor='rgba(16, 185, 129, 0.2)'" type="button">
                                                  <i data-lucide="check" style="width: 13px; height: 13px;"></i> Processed
                                              </button>
                                          @else
                                              <button onclick="openRequisitionDetailsModal({!! htmlspecialchars(json_encode([
                                                  'id' => $req->id,
                                                  'unique_id' => $req->unique_id,
                                                  'requester_name' => $req->requester_name,
                                                  'staff_id' => $req->requester ? $req->requester->service_number : ($req->collector_staff_id ?: 'N/A'),
                                                  'department' => $req->department,
                                                  'purpose' => $req->purpose,
                                                  'date_time' => $req->created_at->format('d/m/Y H:i'),
                                                  'is_ready' => $req->is_ready_for_dg_approval,
                                                  'requires_dg' => $req->requires_dg_approval,
                                                  'dg_status' => $req->dg_status ?? 'pending',
                                                  'dg_decline_reason' => $req->dg_decline_reason,
                                                  'items' => $req->items->map(fn($i) => [
                                                      'description' => $i->description,
                                                      'quantity' => number_format($i->quantity_requested, 0),
                                                      'unit' => $i->unit
                                                  ])->toArray()
                                              ]), ENT_QUOTES, 'UTF-8') !!}, this)" class="dg-action-btn view-details" style="padding: 6px 12px; background: rgba(99, 102, 241, 0.1); border: 1.5px solid rgba(99, 102, 241, 0.25); color: var(--dg-primary); border-radius: 8px; font-size: 0.72rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: transform 0.15s;" onmouseover="this.style.background='rgba(99,102,241,0.2)'" onmouseout="this.style.background='rgba(99,102,241,0.1)'" type="button">
                                                  <i data-lucide="eye" style="width: 13px; height: 13px;"></i> View Details
                                              </button>
                                          @endif

                                         @if($req->requires_dg_approval)
                                             @if(($req->dg_status ?? 'pending') === 'approved')
                                                 <span class="dg-badge success" style="font-size: 0.65rem;">
                                                     <i data-lucide="check" style="width: 10px; height: 10px;"></i> Approved
                                                 </span>
                                             @elseif(($req->dg_status ?? 'pending') === 'declined')
                                                 <span class="dg-badge danger" style="font-size: 0.65rem;">
                                                     <i data-lucide="x" style="width: 10px; height: 10px;"></i> Declined
                                                 </span>
                                             @elseif($req->is_ready_for_dg_approval)
                                                 <span class="dg-badge warning" style="font-size: 0.65rem; background: rgba(245, 158, 11, 0.08); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);">
                                                     <i data-lucide="clock" style="width: 10px; height: 10px;"></i> Pending
                                                 </span>
                                             @endif
                                         @endif
                                     </div>
                                 </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
                                    <i data-lucide="file-text" style="width: 40px; height: 40px; margin-bottom: 1rem; opacity: 0.25;"></i>
                                    <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No staff requisitions registered.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requisitions->hasPages())
                <div class="dg-pagination-container">
                    <div class="dg-pagination-info">
                        Showing <span>{{ $requisitions->firstItem() ?? 0 }}</span> to <span>{{ $requisitions->lastItem() ?? 0 }}</span> of <span>{{ $requisitions->total() }}</span> requisitions
                    </div>
                    <div class="dg-pagination-buttons">
                        @if ($requisitions->onFirstPage())
                            <span class="dg-page-btn disabled"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</span>
                        @else
                            <a href="{{ $requisitions->appends(request()->query())->previousPageUrl() }}" class="dg-page-btn"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</a>
                        @endif

                        @if ($requisitions->hasMorePages())
                            <a href="{{ $requisitions->appends(request()->query())->nextPageUrl() }}" class="dg-page-btn">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></a>
                        @else
                            <span class="dg-page-btn disabled">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- PANEL 4: USER PRESENCE OVERVIEW --}}
    <div id="dg-user-presence-tab" class="dg-tab-panel">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-premium);">
            <div style="overflow-x: auto;">
                <table class="dg-table">
                    <thead>
                        <tr>
                            <th>Personnel Name</th>
                            <th>Username</th>
                            <th>System Role</th>
                            <th>Department</th>
                            <th>Rank / Title</th>
                            <th>Account Status</th>
                            <th>Activity Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $usr)
                            <tr class="dg-row">
                                <td style="font-weight: 800; color: var(--text-main);">
                                    {{ $usr->name }}
                                </td>
                                <td style="font-weight: 700; color: var(--dg-primary); font-family: monospace;">
                                    {{ '@' . $usr->username }}
                                </td>
                                <td style="font-weight: 700;">
                                    {{ $usr->role }}
                                </td>
                                <td style="font-weight: 700; color: var(--text-muted);">
                                    {{ $usr->department ?: 'Unassigned' }}
                                </td>
                                <td style="font-weight: 600;">
                                    {{ $usr->rank ?: 'None' }}
                                </td>
                                <td>
                                    <span class="dg-badge {{ $usr->is_active ? 'success' : 'danger' }}">
                                        {{ $usr->is_active ? 'Active' : 'Suspended' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="online-indicator {{ $usr->is_online ? 'online' : 'offline' }}">
                                        {{ $usr->is_online ? 'Online' : 'Offline' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
                                    <i data-lucide="users" style="width: 40px; height: 40px; margin-bottom: 1rem; opacity: 0.25;"></i>
                                    <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No approved personnel registered in the database.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="dg-pagination-container">
                    <div class="dg-pagination-info">
                        Showing <span>{{ $users->firstItem() ?? 0 }}</span> to <span>{{ $users->lastItem() ?? 0 }}</span> of <span>{{ $users->total() }}</span> accounts
                    </div>
                    <div class="dg-pagination-buttons">
                        @if ($users->onFirstPage())
                            <span class="dg-page-btn disabled"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</span>
                        @else
                            <a href="{{ $users->appends(request()->query())->previousPageUrl() }}" class="dg-page-btn"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</a>
                        @endif

                        @if ($users->hasMorePages())
                            <a href="{{ $users->appends(request()->query())->nextPageUrl() }}" class="dg-page-btn">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></a>
                        @else
                            <span class="dg-page-btn disabled">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- PANEL 4b: ISSUED & RETURNED ITEMS REGISTRY --}}
    <div id="dg-issued-returned-tab" class="dg-tab-panel">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-premium); margin-bottom: 2rem;">
            <div style="padding: 1.5rem 1.75rem; border-bottom: 1px solid var(--border-color); background: rgba(0,0,0,0.01);">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 850; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="arrow-up-right" style="width: 18px; color: #ef4444;"></i>
                    Issued Items Registry
                </h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="dg-table">
                    <thead>
                        <tr>
                            <th>Date Issued</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Issuance Type</th>
                            <th>Beneficiary</th>
                            <th>Authorized By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issuedItems as $item)
                            <tr class="dg-row">
                                <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
                                    {{ $item->issuance_date ? \Carbon\Carbon::parse($item->issuance_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td style="font-weight: 800; color: var(--text-main);">
                                    {{ $item->description }}
                                </td>
                                <td>
                                    <span class="badge-event">{{ $ledgeMap[$item->ledge_category] ?? $item->ledge_category }}</span>
                                </td>
                                <td style="font-weight: 800;">
                                    {{ number_format($item->original_quantity) }} <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $item->unit }}</span>
                                </td>
                                <td>
                                    <span class="dg-badge {{ $item->issuance_type === 'Temporary' ? 'warning' : 'success' }}">
                                        {{ $item->issuance_type }}
                                    </span>
                                </td>
                                <td style="font-weight: 700; color: var(--text-main);">
                                    {{ $item->beneficiary }}
                                </td>
                                <td style="font-weight: 600; color: var(--text-muted);">
                                    {{ $item->authority }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
                                    <i data-lucide="arrow-up-right" style="width: 40px; height: 40px; margin-bottom: 1rem; opacity: 0.25;"></i>
                                    <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No issued items registered.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($issuedItems->hasPages())
                <div class="dg-pagination-container">
                    <div class="dg-pagination-info">
                        Showing <span>{{ $issuedItems->firstItem() ?? 0 }}</span> to <span>{{ $issuedItems->lastItem() ?? 0 }}</span> of <span>{{ $issuedItems->total() }}</span> records
                    </div>
                    <div class="dg-pagination-buttons">
                        @if ($issuedItems->onFirstPage())
                            <span class="dg-page-btn disabled"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</span>
                        @else
                            <a href="{{ $issuedItems->appends(request()->query())->previousPageUrl() }}" class="dg-page-btn"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</a>
                        @endif

                        @if ($issuedItems->hasMorePages())
                            <a href="{{ $issuedItems->appends(request()->query())->nextPageUrl() }}" class="dg-page-btn">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></a>
                        @else
                            <span class="dg-page-btn disabled">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-premium);">
            <div style="padding: 1.5rem 1.75rem; border-bottom: 1px solid var(--border-color); background: rgba(0,0,0,0.01);">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 850; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="arrow-down-left" style="width: 18px; color: #10b981;"></i>
                    Returned Items Registry
                </h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="dg-table">
                    <thead>
                        <tr>
                            <th>Date Returned</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Returned Qty</th>
                            <th>Returned By</th>
                            <th>Remarks / Condition</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returnedItems as $item)
                            <tr class="dg-row">
                                <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
                                    {{ $item->return_date ? \Carbon\Carbon::parse($item->return_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td style="font-weight: 800; color: var(--text-main);">
                                    {{ $item->description }}
                                </td>
                                <td>
                                    <span class="badge-event">{{ $ledgeMap[$item->ledge_category] ?? $item->ledge_category }}</span>
                                </td>
                                <td style="font-weight: 800; color: #10b981;">
                                    {{ number_format($item->returned_qty) }}
                                </td>
                                <td style="font-weight: 700; color: var(--text-main);">
                                    {{ $item->beneficiary }}
                                </td>
                                <td style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">
                                    {{ $item->remarks ?: 'No remarks' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
                                    <i data-lucide="arrow-down-left" style="width: 40px; height: 40px; margin-bottom: 1rem; opacity: 0.25;"></i>
                                    <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No returned items registered.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($returnedItems->hasPages())
                <div class="dg-pagination-container">
                    <div class="dg-pagination-info">
                        Showing <span>{{ $returnedItems->firstItem() ?? 0 }}</span> to <span>{{ $returnedItems->lastItem() ?? 0 }}</span> of <span>{{ $returnedItems->total() }}</span> records
                    </div>
                    <div class="dg-pagination-buttons">
                        @if ($returnedItems->onFirstPage())
                            <span class="dg-page-btn disabled"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</span>
                        @else
                            <a href="{{ $returnedItems->appends(request()->query())->previousPageUrl() }}" class="dg-page-btn"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</a>
                        @endif

                        @if ($returnedItems->hasMorePages())
                            <a href="{{ $returnedItems->appends(request()->query())->nextPageUrl() }}" class="dg-page-btn">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></a>
                        @else
                            <span class="dg-page-btn disabled">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- PANEL 5: APPROVAL WORKFLOW CONFIGURATION --}}
    <div id="dg-workflow-config-tab" class="dg-tab-panel">
        @php
            $dgSelectedCats = \App\Models\Setting::get('dg_approval_categories', []);
            if (!is_array($dgSelectedCats)) {
                $dgSelectedCats = json_decode($dgSelectedCats, true) ?? [];
            }
            $selectedCats = \App\Models\Setting::get('stores_dept_head_approval_categories', []);
            if (!is_array($selectedCats)) {
                $selectedCats = json_decode($selectedCats, true) ?? [];
            }
        @endphp
        <div class="workflow-card-modern dg-workflow-container">
            <div class="cfg-card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); padding: 2.25rem 2.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 1.25rem;">
                    <div class="cfg-icon-box" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); box-shadow: 0 8px 20px rgba(139, 92, 246, 0.15); width: 50px; height: 50px; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white;">
                        <i data-lucide="user-cog" style="width: 24px; height: 24px; color: white;"></i>
                    </div>
                    <div>
                        <h3 style="font-weight: 955; font-size: 1.25rem; color: #0f172a; margin: 0; letter-spacing: -0.03em;">Director General's Approval Workflow</h3>
                        <p style="color: #64748b; font-weight: 600; font-size: 0.82rem; margin: 4px 0 0;">Select the specific item categories that require intermediate review and sign-off by you (Director General).</p>
                    </div>
                </div>
                <span id="dg-workflow-active-badge" style="background: rgba(139,92,246,0.08); color: #8b5cf6; font-size: 0.72rem; font-weight: 800; padding: 6px 14px; border-radius: 30px; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(139,92,246,0.15); box-shadow: 0 2px 4px rgba(139,92,246,0.02); transition: all 0.3s ease;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #8b5cf6; transition: all 0.3s ease;" id="dg-workflow-badge-dot"></span>
                    <span id="dg-workflow-badge-text" style="letter-spacing: 0.02em;">Active Categories: {{ count($dgSelectedCats) }}</span>
                </span>
            </div>
            <div class="cfg-card-body" style="padding: 2.5rem; background: #ffffff;">
                <form action="{{ route('admin.settings.update') }}" method="POST" id="dg-configs-dashboard">
                    @csrf
                    <input type="hidden" name="settings_form" value="1">
                    <input type="hidden" name="dg_approval_categories_present" value="1">

                    <!-- Hidden real multi-select to preserve native settings submission -->
                    <select name="dg_approval_categories[]" id="dg_approval_categories" multiple="multiple" style="display: none;">
                        @foreach($ledgeMap ?? [] as $code => $name)
                        <option value="{{ $code }}" {{ in_array($code, $dgSelectedCats) ? 'selected' : '' }}>{{ $code }}</option>
                        @endforeach
                    </select>

                    <!-- Hidden real multi-select for Stores categories to avoid JS failures (Read-Only) -->
                    <select id="stores_dept_head_approval_categories" multiple="multiple" style="display: none;">
                        @foreach($ledgeMap ?? [] as $code => $name)
                        <option value="{{ $code }}" {{ in_array($code, $selectedCats) ? 'selected' : '' }}>{{ $code }}</option>
                        @endforeach
                    </select>

                    <div style="display: flex; flex-direction: column; gap: 2rem;">

                        <!-- Premium Interactive Card Selection Grid -->
                        <div class="workflow-cat-grid-modern">
                            @foreach($ledgeMap ?? [] as $code => $name)
                            @php $isActive = in_array($code, $dgSelectedCats); @endphp
                            <div class="workflow-cat-card-modern {{ $isActive ? 'active' : '' }}"
                                onclick="toggleDGWorkflowCategory('{{ $code }}', this)">

                                <!-- Glowing corner accent for active state -->
                                <div class="corner-glow"></div>

                                <!-- Category Code Circle -->
                                <div class="cat-circle">
                                    {{ $code }}
                                </div>

                                <!-- Name & Status -->
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 855; font-size: 0.88rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $name }}</div>
                                    <div class="status-label">
                                        {{ $isActive ? 'Requires DG' : 'Bypasses DG' }}
                                    </div>
                                </div>

                                <!-- Indicator Circle -->
                                <div class="indicator-dot">
                                    <i data-lucide="check" style="width: 11px; height: 11px; color: white; display: {{ $isActive ? 'block' : 'none' }};"></i>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Workflow Explainer Graphic and Logic Info Card -->
                        <div style="display: grid; grid-template-columns: 1fr 480px; gap: 2rem; align-items: stretch; margin-top: 0.5rem;" class="workflow-info-grid">

                            <!-- Sleek Gradient Alert Card -->
                            <div style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.03) 0%, rgba(99, 102, 241, 0.01) 100%);
                                            border: 1.5px solid #edf2f7;
                                            border-radius: 24px;
                                            padding: 1.75rem 2rem;
                                            display: flex;
                                            gap: 1.25rem;
                                            align-items: flex-start;">
                                <div style="width: 42px; height: 42px; background: rgba(139,92,246,0.06); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #8b5cf6; flex-shrink: 0; margin-top: 2px;">
                                    <i data-lucide="info" style="width: 20px; height: 20px;"></i>
                                </div>
                                <div style="flex: 1;">
                                    <h5 style="margin: 0 0 6px 0; font-size: 0.95rem; font-weight: 855; color: #1e293b; letter-spacing: -0.010em;">DG Smart Routing Protocol Active</h5>
                                    <p style="margin: 0; font-size: 0.8rem; color: #475569; line-height: 1.6; font-weight: 600;">
                                        When item categories are configured above, any submitted requisition containing matching items will be routed for manual review by you (Director General) prior to final confirmation and stock deduction. Requisitions consisting solely of bypassed categories skip the DG approval stage completely.
                                    </p>
                                </div>
                            </div>

                            <!-- Dynamic Mini Infographic Visualizer Card -->
                            <div style="background: linear-gradient(to bottom, #fafbff, #ffffff); border: 1.5px solid #edf2f7; border-radius: 24px; padding: 1.75rem 2rem; display: flex; flex-direction: column; justify-content: center; gap: 1.25rem; box-shadow: 0 4px 20px rgba(0,0,0,0.015);">
                                <div style="font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; text-align: center; margin-bottom: 0.25rem;">Live Approval Routing Pathway</div>

                                <div style="display: flex; align-items: center; justify-content: space-between; position: relative; width: 100%; padding: 0.5rem 0;" class="flow-nodes-container">

                                    <!-- Origin Node -->
                                    <div class="flow-node" style="display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 2; position: relative; width: 68px;">
                                        <div class="flow-node-icon" style="background: linear-gradient(135deg, #4f46e5, #3730a3); color: white; box-shadow: 0 4px 12px rgba(79,70,229,0.15); width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                            <i data-lucide="user-check" style="width: 15px; height: 15px;"></i>
                                        </div>
                                        <span style="font-size: 0.65rem; font-weight: 855; color: #1e293b; white-space: nowrap;">Dept. Head</span>
                                        <span class="flow-node-badge" style="background: #e0e7ff; color: #4f46e5; font-size: 0.55rem; font-weight: 800; padding: 1px 6px; border-radius: 30px; transition: all 0.3s ease;">Required</span>
                                    </div>

                                    <!-- Connector 1 (Now connects to DG Node, so controlled by DG active state) -->
                                    <div class="flow-line flow-line-2" style="flex: 1; height: 3px; transition: all 0.4s ease; background: #cbd5e1; margin-top: -16px;"></div>

                                    <!-- DG Node (Director Gen.) -->
                                    <div class="flow-node flow-node-dg" style="display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 2; position: relative; width: 68px;">
                                        <div class="flow-node-icon" style="width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                            <i data-lucide="user-cog" style="width: 15px; height: 15px;"></i>
                                        </div>
                                        <span class="flow-node-label" style="font-size: 0.65rem; font-weight: 855; white-space: nowrap;">Director Gen.</span>
                                        <span class="flow-node-badge" style="font-size: 0.55rem; font-weight: 800; padding: 1px 6px; border-radius: 30px; transition: all 0.3s ease;"></span>
                                    </div>

                                    <!-- Connector 2 (Now connects to Stores Head, so controlled by Stores Head active state) -->
                                    <div class="flow-line flow-line-1" style="flex: 1; height: 3px; transition: all 0.4s ease; background: #cbd5e1; margin-top: -16px;"></div>

                                    <!-- Stores Head Node (Head of Admin) -->
                                    <div class="flow-node flow-node-stores" style="display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 2; position: relative; width: 68px;">
                                        <div class="flow-node-icon" style="width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                            <i data-lucide="package" style="width: 15px; height: 15px;"></i>
                                        </div>
                                        <span class="flow-node-label" style="font-size: 0.65rem; font-weight: 855; white-space: nowrap;">Head of Admin(Authorizer)</span>
                                        <span class="flow-node-badge" style="font-size: 0.55rem; font-weight: 800; padding: 1px 6px; border-radius: 30px; transition: all 0.3s ease;"></span>
                                    </div>

                                    <!-- Connector 3 (Connects to Head of Stores, always active) -->
                                    <div class="flow-line flow-line-3" style="flex: 1; height: 3px; transition: all 0.4s ease; background: #cbd5e1; margin-top: -16px;"></div>

                                    <!-- Head of Stores Node -->
                                    <div class="flow-node" style="display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 2; position: relative; width: 68px;">
                                        <div class="flow-node-icon" style="background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 4px 12px rgba(16,185,129,0.15); width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                            <i data-lucide="shield-check" style="width: 15px; height: 15px;"></i>
                                        </div>
                                        <span style="font-size: 0.65rem; font-weight: 855; color: #1e293b; white-space: nowrap;">Head of Stores</span>
                                        <span class="flow-node-badge" style="background: #d1fae5; color: #065f46; font-size: 0.55rem; font-weight: 800; padding: 1px 6px; border-radius: 30px; transition: all 0.3s ease;">Final Sign</span>
                                    </div>

                                </div>

                                <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; line-height: 1.45; text-align: center; background: #f8fafc; border-radius: 12px; padding: 8px 12px; border: 1px solid #f1f5f9; transition: all 0.3s ease;" class="workflow-helper-hint">
                                </div>
                            </div>

                        </div>

                        <!-- Submit trigger -->
                        <div style="display: flex; justify-content: flex-end; margin-top: 1rem; margin-bottom: 1.5rem;">
                            <button type="submit" style="padding: 0.75rem 2rem; border-radius: 12px; border: none; background: #8b5cf6; color: white; font-weight: 800; font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;" onmouseover="this.style.background='#6d28d9'" onmouseout="this.style.background='#8b5cf6'">
                                <i data-lucide="save" style="width: 18px; height: 18px;"></i> Save DG Workflow Changes
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

</div>

{{-- Requisition Details Popover Modal --}}
<div id="dg-details-modal" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); z-index: 99998; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);">
    <div style="background: linear-gradient(145deg, #ffffff, #fafbfc); border-radius: 28px; padding: 0; width: 820px; max-width: 94%; max-height: 90vh; box-shadow: 0 60px 140px -40px rgba(15, 23, 42, 0.7), 0 0 0 1px rgba(99, 102, 241, 0.08); display: flex; flex-direction: column; transform: scale(0.92) translateY(10px); transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s ease; overflow: hidden;" id="dg-details-modal-content">
        
        <!-- Header with Gradient Accent -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 28px 36px 22px 36px; border-bottom: 1px solid #eef2f6; position: relative; background: linear-gradient(180deg, rgba(99, 102, 241, 0.02) 0%, transparent 100%);">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #6366f1, #818cf8, #a78bfa, #818cf8, #6366f1); background-size: 200% 100%; animation: shimmer 3s ease-in-out infinite;"></div>
            
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1, #818cf8); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 8px 24px rgba(99, 102, 241, 0.25);">
                    <i data-lucide="file-text" style="width: 24px; height: 24px; stroke-width: 2px;"></i>
                </div>
                <div>
                    <h3 style="font-weight: 800; font-size: 1.3rem; color: #0f172a; margin: 0; letter-spacing: -0.02em;" id="details-modal-title">Requisition Details</h3>
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 2px;">
                        <span style="font-size: 0.7rem; color: #94a3b8; font-weight: 500;" id="details-modal-id">#REQ-00024</span>
                        <span style="width: 4px; height: 4px; background: #94a3b8; border-radius: 50%; display: inline-block;"></span>
                        <span style="font-size: 0.65rem; font-weight: 600; background: rgba(234, 179, 8, 0.12); color: #ca8a04; padding: 2px 14px; border-radius: 20px; border: 1px solid rgba(234, 179, 8, 0.15);">Pending</span>
                    </div>
                </div>
            </div>
            <button onclick="closeRequisitionDetailsModal()" style="background: rgba(241, 245, 249, 0.8); border: 1px solid #e2e8f0; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #64748b; cursor: pointer; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);" onmouseover="this.style.background='rgba(239, 68, 68, 0.1)'; this.style.borderColor='rgba(239, 68, 68, 0.3)'; this.style.color='#ef4444'; this.style.transform='rotate(90deg)'" onmouseout="this.style.background='rgba(241, 245, 249, 0.8)'; this.style.borderColor='#e2e8f0'; this.style.color='#64748b'; this.style.transform='rotate(0deg)'">
                <i data-lucide="x" style="width: 20px; height: 20px; stroke-width: 2px;"></i>
            </button>
        </div>

        <!-- Body - All Information with Enhanced Cards -->
        <div style="padding: 28px 36px 24px 36px; overflow-y: auto; flex: 1;">
            
            <!-- Requester Row - Modern Card Design -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px; padding: 20px 24px; background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius: 16px; border: 1px solid #e2e8f0;">
                <div>
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 0.6rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px;">
                        <i data-lucide="user" style="width: 12px; height: 12px;"></i>
                        Requester
                    </div>
                    <div style="font-weight: 700; color: #0f172a; font-size: 1rem;" id="details-modal-requester">John Mensah</div>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 0.6rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px;">
                        <i data-lucide="badge" style="width: 12px; height: 12px;"></i>
                        Staff ID
                    </div>
                    <div style="font-weight: 600; color: #0f172a; font-size: 0.95rem; font-family: 'SF Mono', 'Monaco', monospace; background: rgba(99, 102, 241, 0.06); padding: 2px 12px; border-radius: 6px; display: inline-block;" id="details-modal-staff-id">646545</div>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 0.6rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px;">
                        <i data-lucide="building" style="width: 12px; height: 12px;"></i>
                        Department
                    </div>
                    <div style="font-weight: 600; color: #0f172a; font-size: 0.95rem;" id="details-modal-department">Intelligence Department</div>
                </div>
            </div>

            <!-- Purpose Row - Enhanced -->
            <div style="margin-bottom: 24px; padding: 18px 24px; background: linear-gradient(135deg, rgba(99, 102, 241, 0.03), rgba(99, 102, 241, 0.01)); border-radius: 16px; border: 1px solid #e2e8f0; border-left: 4px solid #6366f1;">
                <div style="display: flex; align-items: center; gap: 6px; font-size: 0.6rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px;">
                    <i data-lucide="target" style="width: 14px; height: 14px; color: #6366f1;"></i>
                    Purpose
                </div>
                <div style="font-weight: 500; color: #0f172a; font-size: 0.95rem; line-height: 1.6;" id="details-modal-purpose">nataraj</div>
            </div>

            <!-- Date & Items Row - Enhanced -->
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
                <div style="padding: 16px 20px; background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 0.6rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px;">
                        <i data-lucide="clock" style="width: 14px; height: 14px;"></i>
                        Submitted
                    </div>
                    <div style="font-weight: 600; color: #0f172a; font-size: 0.95rem;" id="details-modal-date-time">09/07/2026 17:31</div>
                </div>
                <div style="padding: 16px 20px; background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 6px; font-size: 0.6rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em;">
                            <i data-lucide="package" style="width: 14px; height: 14px;"></i>
                            Requested Items
                        </div>
                        <span style="background: #6366f1; color: white; font-size: 0.55rem; font-weight: 700; padding: 1px 12px; border-radius: 20px;" id="details-modal-item-count">1</span>
                    </div>
                    <div id="details-modal-items" style="display: flex; flex-direction: column; gap: 8px; max-height: 150px; overflow-y: auto; padding-right: 4px;" class="custom-scrollbar">
                        <!-- Items populated via JS -->
                        <div style="background: white; border-radius: 10px; padding: 10px 16px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e2e8f0; transition: all 0.2s;" onmouseover="this.style.borderColor='#6366f1'; this.style.background='#fafbfc'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='white'">
                            <div style="display: flex; flex-direction: column; gap: 1px;">
                                <span style="font-weight: 600; color: #0f172a; font-size: 0.9rem;">PEN</span>
                                <span style="font-size: 0.6rem; color: #94a3b8; font-weight: 500;">Unit: PIECE(S)</span>
                            </div>
                            <span style="font-weight: 700; color: #6366f1; font-size: 0.95rem; background: rgba(99, 102, 241, 0.08); padding: 2px 14px; border-radius: 8px;">×5</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer - Enhanced Buttons -->
        <div style="padding: 18px 36px 26px 36px; border-top: 1px solid #eef2f6; background: linear-gradient(0deg, rgba(255,255,255,0.8) 0%, transparent 100%);">
            <div id="details-modal-actions" style="display: flex; gap: 12px; justify-content: flex-end; align-items: center;">
                <!-- Actions populated via JS -->
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes shimmer {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }
</style>

{{-- Decline Requisition Modal --}}
<div id="dg-decline-modal" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(8px); z-index: 99999; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s ease;">
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 24px; padding: 2rem; width: 440px; max-width: 90%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; gap: 1.25rem; transform: scale(0.95); transition: transform 0.3s ease;" id="dg-decline-modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; background: rgba(239, 68, 68, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #ef4444;">
                    <i data-lucide="x-circle" style="width: 20px; height: 20px;"></i>
                </div>
                <h3 style="font-weight: 900; font-size: 1.15rem; color: var(--text-main); margin: 0; letter-spacing: -0.02em;">Decline Requisition</h3>
            </div>
            <button onclick="closeDeclineModal()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='var(--text-muted)'">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
        </div>
        <div style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600; line-height: 1.5;">
            Please state the official reason for declining requisition <strong style="color: var(--text-main);" id="decline-modal-req-id">#00000</strong>. This description will be sent back to the requester.
        </div>
        <div>
            <textarea id="decline-reason-input" class="filter-control-dg" placeholder="Enter details on why this request is declined..." style="width: 100%; min-height: 120px; font-family: inherit; resize: vertical; box-sizing: border-box;" required></textarea>
        </div>
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button onclick="closeDeclineModal()" style="padding: 10px 18px; background: var(--bg-main); border: 1.5px solid var(--border-color); color: var(--text-main); border-radius: 12px; font-weight: 800; font-size: 0.8rem; cursor: pointer; transition: background 0.2s;">
                Cancel
            </button>
            <button id="decline-submit-btn" onclick="submitDecline()" style="padding: 10px 20px; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; border-radius: 12px; font-weight: 900; font-size: 0.8rem; cursor: pointer; transition: transform 0.25s, box-shadow 0.25s;">
                Decline Requisition
            </button>
        </div>
    </div>
</div>

<script>
    let dgSearchTimeout = null;

    function toggleSupplierDetails(btn) {
        const container = btn.closest('td').querySelector('.supplier-details-container');
        if (container) {
            const isHidden = window.getComputedStyle(container).display === 'none';
            // Hide all other supplier detail containers first
            document.querySelectorAll('.supplier-details-container').forEach(c => {
                if (c !== container) {
                    c.style.display = 'none';
                    const parentBtn = c.closest('td').querySelector('.supplier-toggle-btn');
                    if (parentBtn) parentBtn.style.transform = 'rotate(0deg)';
                }
            });

            if (isHidden) {
                container.style.display = 'flex';
                btn.style.transform = 'rotate(180deg)';
            } else {
                container.style.display = 'none';
                btn.style.transform = 'rotate(0deg)';
            }
        }
    }

    function toggleRequisitionItems(btn) {
        toggleActionRequisitionItems(btn);
    }

    function toggleActionRequisitionItems(btn) {
        const container = btn.closest('td').querySelector('.req-items-container');
        if (container) {
            const isHidden = window.getComputedStyle(container).display === 'none';
            // Hide all other requisition items detail containers first
            document.querySelectorAll('.req-items-container').forEach(c => {
                if (c !== container) {
                    c.style.display = 'none';
                }
            });

            if (isHidden) {
                container.style.display = 'flex';
            } else {
                container.style.display = 'none';
            }
        }
    }

    function switchDGTab(panelId, btn) {
        // Toggle Buttons
        document.querySelectorAll('.dg-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Toggle Panels
        document.querySelectorAll('.dg-tab-panel').forEach(p => p.classList.remove('active'));
        document.getElementById(panelId).classList.add('active');

        // Store active tab in localStorage
        localStorage.setItem('active_dg_tab', panelId);

        // Dynamically show/hide Status dropdown
        const statusSelect = document.getElementById('dg-req-status');
        
        if (statusSelect) {
            statusSelect.style.display = (panelId === 'dg-staff-reqs-tab') ? 'inline-block' : 'none';
        }

        const filterForm = document.getElementById('dg-filter-form');
        if (filterForm) {
            filterForm.style.display = (panelId === 'dg-workflow-config-tab') ? 'none' : 'block';
        }

        const printBtn = document.getElementById('btn-print-dg-report');
        if (printBtn) {
            printBtn.style.display = (panelId === 'dg-workflow-config-tab') ? 'none' : 'inline-flex';
        }
    }

    function reloadDGData() {
        const form = document.getElementById('dg-filter-form');
        if (!form) return;

        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        
        // Remove empty params
        for (const [key, value] of [...params.entries()]) {
            if (!value) {
                params.delete(key);
            }
        }
        
        const queryString = params.toString();
        const fetchUrl = `{{ route('dg.dashboard') }}${queryString ? '?' + queryString : ''}`;
        
        // Update browser URL
        history.pushState(null, '', fetchUrl);
        
        // Update print button href
        const printBtn = document.getElementById('btn-print-dg-report');
        if (printBtn) {
            const dateFrom = params.get('date_from') || '';
            const dateTo = params.get('date_to') || '';
            printBtn.setAttribute('href', `{{ route('dg.print') }}?date_from=${encodeURIComponent(dateFrom)}&date_to=${encodeURIComponent(dateTo)}`);
        }

        // Show/hide Clear button dynamically
        let anyFilled = false;
        const inputsToCheck = ['search_query', 'date_from', 'date_to', 'req_status'];
        inputsToCheck.forEach(name => {
            if (params.get(name)) anyFilled = true;
        });

        // Update clear button container or handle its existence
        const clearBtn = document.getElementById('btn-dg-clear');
        if (anyFilled) {
            if (!clearBtn) {
                const btnContainer = form.querySelector('div[style*="display: flex"]');
                const clearLink = document.createElement('a');
                clearLink.id = 'btn-dg-clear';
                clearLink.href = '{{ route("dg.dashboard") }}';
                clearLink.className = 'filter-control-dg';
                clearLink.style.cssText = 'background: rgba(239, 68, 68, 0.05); color: #ef4444; border: 1.5px solid #ef4444; text-decoration: none; text-align: center; font-weight: 800; min-width: 100px; display: inline-flex; align-items: center; justify-content: center;';
                clearLink.innerText = 'Clear';
                clearLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    form.reset();
                    reloadDGData();
                });
                btnContainer.appendChild(clearLink);
            }
        } else {
            if (clearBtn) {
                clearBtn.remove();
            }
        }

        fetch(fetchUrl)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // 1. Update stats cards numbers
                const statNumbers = document.querySelectorAll('.stat-number');
                const newStatNumbers = doc.querySelectorAll('.stat-number');
                statNumbers.forEach((stat, idx) => {
                    if (newStatNumbers[idx]) {
                        stat.innerHTML = newStatNumbers[idx].innerHTML;
                    }
                });

                // 2. Update all tab panels
                const tabPanels = ['dg-stock-oversight-tab', 'dg-staff-reqs-tab', 'dg-user-presence-tab', 'dg-issued-returned-tab', 'dg-workflow-config-tab'];
                tabPanels.forEach(panelId => {
                    const oldPanel = document.getElementById(panelId);
                    const newPanel = doc.getElementById(panelId);
                    if (oldPanel && newPanel) {
                        oldPanel.innerHTML = newPanel.innerHTML;
                    }
                });

                updateStaffRequisitionsTabBadge(doc);

                // Re-initialize lucide icons on dynamically loaded contents
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
                
                // Intercept new pagination links
                bindDGPaginationLinks();
            })
            .catch(err => console.error('Error fetching filtered data:', err));
    }

    function bindDGPaginationLinks() {
        document.querySelectorAll('.dg-page-btn').forEach(link => {
            if (link.tagName.toLowerCase() === 'a') {
                // Remove existing listeners by cloning or just clean check
                const newLink = link.cloneNode(true);
                link.parentNode.replaceChild(newLink, link);
                newLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = newLink.getAttribute('href');
                    
                    history.pushState(null, '', url);
                    
                    fetch(url)
                        .then(res => res.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            
                            // Only update tab panels and pagination
                            const tabPanels = ['dg-stock-oversight-tab', 'dg-staff-reqs-tab', 'dg-user-presence-tab', 'dg-issued-returned-tab', 'dg-workflow-config-tab'];
                            tabPanels.forEach(panelId => {
                                const oldPanel = document.getElementById(panelId);
                                const newPanel = doc.getElementById(panelId);
                                if (oldPanel && newPanel) {
                                    oldPanel.innerHTML = newPanel.innerHTML;
                                }
                            });
                            
                            updateStaffRequisitionsTabBadge(doc);
                            
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                            
                            bindDGPaginationLinks();
                            
                            // Scroll panel back to top
                            const activePanel = document.querySelector('.dg-tab-panel.active');
                            if (activePanel) {
                                activePanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                            }
                        });
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Restore tab
        const savedTab = localStorage.getItem('active_dg_tab') || 'dg-stock-oversight-tab';
        const btn = Array.from(document.querySelectorAll('.dg-tab-btn')).find(b => b.getAttribute('onclick').includes(savedTab));
        if (btn) {
            btn.click();
        } else {
            const defaultBtn = document.getElementById('tab-btn-stock-oversight');
            if (defaultBtn) defaultBtn.click();
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Dismiss supplier/requisition popups when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.supplier-toggle-btn') && !e.target.closest('.supplier-details-container')) {
                document.querySelectorAll('.supplier-details-container').forEach(c => {
                    c.style.display = 'none';
                    const parentBtn = c.closest('td').querySelector('.supplier-toggle-btn');
                    if (parentBtn) parentBtn.style.transform = 'rotate(0deg)';
                });
            }
            if (!e.target.closest('.req-items-toggle-btn') && !e.target.closest('.view-details') && !e.target.closest('.req-items-container')) {
                document.querySelectorAll('.req-items-container').forEach(c => {
                    c.style.display = 'none';
                    const parentBtn = c.closest('td').querySelector('.req-items-toggle-btn');
                    if (parentBtn) parentBtn.style.transform = 'rotate(0deg)';
                });
            }
        });

        // Close decline modal when clicking outside content area
        document.getElementById('dg-decline-modal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('dg-decline-modal')) {
                closeDeclineModal();
            }
        });

        // Close details modal when clicking outside content area
        const detailsModal = document.getElementById('dg-details-modal');
        if (detailsModal) {
            detailsModal.addEventListener('click', (e) => {
                if (e.target === detailsModal) {
                    closeRequisitionDetailsModal();
                }
            });
        }

        // Setup filter input listeners
        const form = document.getElementById('dg-filter-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                reloadDGData();
            });

            // Bind change events to select and date inputs
            form.querySelectorAll('select, input[type="date"]').forEach(input => {
                input.addEventListener('change', () => {
                    reloadDGData();
                });
            });

            // Bind input events to search text input
            const searchInput = document.getElementById('dg-search-query');
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(dgSearchTimeout);
                    dgSearchTimeout = setTimeout(() => {
                        reloadDGData();
                    }, 500);
                });
            }

            // Clear button click listener
            const clearBtn = document.getElementById('btn-dg-clear');
            if (clearBtn) {
                clearBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    form.reset();
                    reloadDGData();
                });
            }
        }

        // Bind initially loaded pagination links
        bindDGPaginationLinks();

        if (document.getElementById('dg_approval_categories')) {
            updateWorkflowFlowchart();
        }

        // Silent auto-refresh Staff Requisitions tab every 15 seconds
        setInterval(silentRefreshStaffRequisitions, 15000);
    });

    let currentDeclineId = null;

    function openDeclineModal(id, btn, uniqueId = '', requesterName = '', itemsSummary = '') {
        document.querySelectorAll('.req-items-container').forEach(c => c.style.display = 'none');
        currentDeclineId = id;
        const modal = document.getElementById('dg-decline-modal');
        const content = document.getElementById('dg-decline-modal-content');
        const idLabel = document.getElementById('decline-modal-req-id');
        const textInput = document.getElementById('decline-reason-input');

        const displayName = uniqueId ? `${uniqueId} (${itemsSummary || 'Requisition'})` : `#${id}`;
        if (idLabel) idLabel.textContent = displayName;
        if (textInput) textInput.value = '';

        if (modal && content) {
            modal.style.opacity = '1';
            modal.style.pointerEvents = 'auto';
            content.style.transform = 'scale(1)';
        }
    }

    function closeDeclineModal() {
        currentDeclineId = null;
        const modal = document.getElementById('dg-decline-modal');
        const content = document.getElementById('dg-decline-modal-content');

        if (modal && content) {
            modal.style.opacity = '0';
            modal.style.pointerEvents = 'none';
            content.style.transform = 'scale(0.95)';
        }
    }

    async function submitDecline() {
        if (!currentDeclineId) return;
        const reasonInput = document.getElementById('decline-reason-input');
        const reason = reasonInput ? reasonInput.value.trim() : '';

        if (!reason) {
            Swal.fire({
                icon: 'warning',
                title: 'Reason Required',
                text: 'Please provide a reason for declining this requisition.',
                confirmButtonColor: '#6366f1'
            });
            return;
        }

        const submitBtn = document.getElementById('decline-submit-btn');
        const originalHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-lucide="loader-2" class="animate-spin" style="width:16px;"></i> Processing...';
        if (window.lucide) lucide.createIcons();

        try {
            const url = `{{ route('dg.requisitions.process', ['id' => ':id']) }}`.replace(':id', currentDeclineId);
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: 'declined',
                    decline_reason: reason
                })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                closeDeclineModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Requisition Declined',
                    text: data.message,
                    confirmButtonColor: '#10b981'
                });
                reloadDGData();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed',
                    text: data.message || 'Could not decline requisition.',
                    confirmButtonColor: '#ef4444'
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Could not communicate with the server.',
                confirmButtonColor: '#ef4444'
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
            if (window.lucide) lucide.createIcons();
        }
    }

    async function approveRequisition(id, btn, uniqueId = '', requesterName = '', itemsSummary = '') {
        const displayName = uniqueId ? `${uniqueId} (${itemsSummary || 'Requisition'})` : `requisition #${id}`;
        const confirmResult = await Swal.fire({
            title: 'Approve Requisition?',
            text: `Are you sure you want to approve store requisition ${displayName}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Approve'
        });

        if (!confirmResult.isConfirmed) return;

        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin" style="width:13px; height:13px;"></i> Approving...';
        if (window.lucide) lucide.createIcons();

        try {
            const url = `{{ route('dg.requisitions.process', ['id' => ':id']) }}`.replace(':id', id);
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: 'approved'
                })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Approved Successfully',
                    text: data.message,
                    confirmButtonColor: '#10b981'
                });
                reloadDGData();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed',
                    text: data.message || 'Could not approve requisition.',
                    confirmButtonColor: '#ef4444'
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Could not communicate with the server.',
                confirmButtonColor: '#ef4444'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            if (window.lucide) lucide.createIcons();
        }
    }

    function toggleDGWorkflowCategory(code, card) {
        const select = document.getElementById('dg_approval_categories');
        if (!select) return;
        const option = select.querySelector(`option[value="${code}"]`);

        if (!option) return;

        const isCurrentlyActive = card.classList.contains('active');

        if (isCurrentlyActive) {
            // Deactivate
            card.classList.remove('active');

            const label = card.querySelector('.status-label');
            if (label) {
                label.textContent = 'Bypasses DG';
            }

            const dot = card.querySelector('.indicator-dot');
            if (dot) {
                const checkIcon = dot.querySelector('i, svg');
                if (checkIcon) checkIcon.style.display = 'none';
            }

            option.selected = false;
        } else {
            // Activate
            card.classList.add('active');

            const label = card.querySelector('.status-label');
            if (label) {
                label.textContent = 'Requires DG';
            }

            const dot = card.querySelector('.indicator-dot');
            if (dot) {
                const checkIcon = dot.querySelector('i, svg');
                if (checkIcon) checkIcon.style.display = 'block';
            }

            option.selected = true;
        }

        // Trigger change event on select to ensure any listeners match
        select.dispatchEvent(new Event('change'));

        // Update the visual flowchart in real-time
        updateWorkflowFlowchart();
    }

    function updateWorkflowFlowchart() {
        const selectStores = document.getElementById('stores_dept_head_approval_categories');
        if (!selectStores) return;
        const activeCountStores = 1; // Head of Admin is always required

        const selectDG = document.getElementById('dg_approval_categories');
        const activeCountDG = selectDG ? Array.from(selectDG.selectedOptions).length : 0;

        // Update DG header badge
        const badgeTextDG = document.getElementById('dg-workflow-badge-text');
        const badgeDotDG = document.getElementById('dg-workflow-badge-dot');
        const badgeContainerDG = document.getElementById('dg-workflow-active-badge');
        if (badgeTextDG) badgeTextDG.textContent = `Active Categories: ${activeCountDG}`;
        if (activeCountDG > 0) {
            if (badgeDotDG) badgeDotDG.style.background = '#8b5cf6';
            if (badgeContainerDG) {
                badgeContainerDG.style.background = 'rgba(139, 92, 246, 0.08)';
                badgeContainerDG.style.color = '#8b5cf6';
                badgeContainerDG.style.borderColor = 'rgba(139, 92, 246, 0.2)';
            }
        } else {
            if (badgeDotDG) badgeDotDG.style.background = '#64748b';
            if (badgeContainerDG) {
                badgeContainerDG.style.background = 'rgba(100, 116, 139, 0.08)';
                badgeContainerDG.style.color = '#64748b';
                badgeContainerDG.style.borderColor = 'rgba(100, 116, 139, 0.2)';
            }
        }

        // Update ALL Stores Head flow nodes
        document.querySelectorAll('.flow-node-stores').forEach(node => {
            const iconBox = node.querySelector('.flow-node-icon');
            const label = node.querySelector('.flow-node-label');
            const badge = node.querySelector('.flow-node-badge');

            if (activeCountStores > 0) {
                node.className = 'flow-node flow-node-stores active';
                if (iconBox) {
                    iconBox.style.background = 'linear-gradient(135deg, #4f46e5, #3730a3)';
                    iconBox.style.color = '#ffffff';
                    iconBox.style.borderColor = 'transparent';
                    iconBox.style.boxShadow = '0 6px 15px rgba(79,70,229,0.2)';
                }
                if (label) {
                    label.style.color = '#1e293b';
                    label.style.textDecoration = 'none';
                }
                if (badge) {
                    badge.textContent = 'Required';
                    badge.style.background = 'rgba(79, 70, 229, 0.1)';
                    badge.style.color = '#4f46e5';
                    badge.style.borderColor = 'transparent';
                }
            } else {
                node.className = 'flow-node flow-node-stores bypass';
                if (iconBox) {
                    iconBox.style.background = '#f8fafc';
                    iconBox.style.color = '#64748b';
                    iconBox.style.borderColor = '#cbd5e1';
                    iconBox.style.boxShadow = 'none';
                }
                if (label) {
                    label.style.color = '#94a3b8';
                    label.style.textDecoration = 'line-through';
                }
                if (badge) {
                    badge.textContent = 'Bypassed';
                    badge.style.background = '#fef2f2';
                    badge.style.color = '#ef4444';
                    badge.style.borderColor = 'rgba(239, 68, 68, 0.1)';
                }
            }
        });

        // Update ALL DG flow nodes
        document.querySelectorAll('.flow-node-dg').forEach(node => {
            const iconBox = node.querySelector('.flow-node-icon');
            const label = node.querySelector('.flow-node-label');
            const badge = node.querySelector('.flow-node-badge');

            if (activeCountDG > 0) {
                node.className = 'flow-node flow-node-dg active';
                if (iconBox) {
                    iconBox.style.background = 'linear-gradient(135deg, #8b5cf6, #6d28d9)';
                    iconBox.style.color = '#ffffff';
                    iconBox.style.borderColor = 'transparent';
                    iconBox.style.boxShadow = '0 6px 15px rgba(139,92,246,0.2)';
                }
                if (label) {
                    label.style.color = '#1e293b';
                    label.style.textDecoration = 'none';
                }
                if (badge) {
                    badge.textContent = 'Required';
                    badge.style.background = 'rgba(139, 92, 246, 0.1)';
                    badge.style.color = '#8b5cf6';
                    badge.style.borderColor = 'transparent';
                }
            } else {
                node.className = 'flow-node flow-node-dg bypass';
                if (iconBox) {
                    iconBox.style.background = '#f8fafc';
                    iconBox.style.color = '#64748b';
                    iconBox.style.borderColor = '#cbd5e1';
                    iconBox.style.boxShadow = 'none';
                }
                if (label) {
                    label.style.color = '#94a3b8';
                    label.style.textDecoration = 'line-through';
                }
                if (badge) {
                    badge.textContent = 'Bypassed';
                    badge.style.background = '#fef2f2';
                    badge.style.color = '#ef4444';
                    badge.style.borderColor = 'rgba(239, 68, 68, 0.1)';
                }
            }
        });

        // Update lines
        document.querySelectorAll('.flow-line-1').forEach(line => {
            if (activeCountStores > 0) {
                line.className = 'flow-line flow-line-1 active';
                line.style.background = '#4f46e5';
            } else {
                line.className = 'flow-line flow-line-1 dashed';
                line.style.background = '';
            }
        });

        document.querySelectorAll('.flow-line-2').forEach(line => {
            if (activeCountDG > 0) {
                line.className = 'flow-line flow-line-2 active';
                line.style.background = '#8b5cf6';
            } else {
                line.className = 'flow-line flow-line-2 dashed';
                line.style.background = '';
            }
        });

        document.querySelectorAll('.flow-line-3').forEach(line => {
            line.className = 'flow-line flow-line-3 active';
            line.style.background = '#10b981';
        });

        // Update hints
        document.querySelectorAll('.workflow-helper-hint').forEach(hint => {
            if (activeCountDG > 0) {
                hint.innerHTML = `Routing through <strong>Director General</strong> for <strong style="color: #8b5cf6;">${activeCountDG}</strong> selected category${activeCountDG == 1 ? '' : 'ies'}.`;
            } else {
                hint.innerHTML = 'Currently bypassing intermediate Director General step due to settings configuration.';
            }
        });
    }

    function openRequisitionDetailsModal(req) {
        const modal = document.getElementById('dg-details-modal');
        const content = document.getElementById('dg-details-modal-content');
        
        // Fill Text elements
        document.getElementById('details-modal-title').textContent = `Requisition Details`;
        document.getElementById('details-modal-id').textContent = req.unique_id;
        document.getElementById('details-modal-requester').textContent = req.requester_name || 'N/A';
        document.getElementById('details-modal-staff-id').textContent = req.staff_id || 'N/A';
        document.getElementById('details-modal-department').textContent = req.department || 'N/A';
        document.getElementById('details-modal-date-time').textContent = req.date_time || 'N/A';
        document.getElementById('details-modal-purpose').textContent = req.purpose || 'No purpose specified.';
        
        // Fill item count
        const itemCountEl = document.getElementById('details-modal-item-count');
        if (itemCountEl) {
            itemCountEl.textContent = req.items ? req.items.length : 0;
        }

        // Fill status badge dynamically
        const statusEl = document.getElementById('details-modal-status');
        if (statusEl) {
            statusEl.textContent = req.dg_status.charAt(0).toUpperCase() + req.dg_status.slice(1);
            if (req.dg_status === 'approved') {
                statusEl.style.background = 'rgba(16, 185, 129, 0.12)';
                statusEl.style.color = '#10b981';
                statusEl.style.borderColor = 'rgba(16, 185, 129, 0.15)';
            } else if (req.dg_status === 'declined') {
                statusEl.style.background = 'rgba(239, 68, 68, 0.12)';
                statusEl.style.color = '#ef4444';
                statusEl.style.borderColor = 'rgba(239, 68, 68, 0.15)';
            } else {
                statusEl.style.background = 'rgba(234, 179, 8, 0.12)';
                statusEl.style.color = '#ca8a04';
                statusEl.style.borderColor = 'rgba(234, 179, 8, 0.15)';
            }
        }

        // Fill items list
        const itemsContainer = document.getElementById('details-modal-items');
        itemsContainer.innerHTML = '';
        const itemsList = [];
        if (req.items && req.items.length > 0) {
            req.items.forEach(item => {
                itemsList.push(item.description);
                const itemRow = document.createElement('div');
                itemRow.style.cssText = 'display: flex; justify-content: space-between; align-items: center; background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 16px; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s;';
                itemRow.innerHTML = `
                    <div style="display: flex; flex-direction: column; gap: 1px;">
                        <span style="font-weight: 600; color: #0f172a; font-size: 0.9rem;">${item.description}</span>
                        <span style="font-size: 0.6rem; color: #94a3b8; font-weight: 500;">Unit: ${item.unit}</span>
                    </div>
                    <span style="font-weight: 700; color: #6366f1; font-size: 0.95rem; background: rgba(99, 102, 241, 0.08); padding: 2px 14px; border-radius: 8px;">×${item.quantity}</span>
                `;
                itemsContainer.appendChild(itemRow);
            });
        } else {
            itemsContainer.innerHTML = '<p style="color: var(--text-muted); font-size: 0.8rem; text-align: center; margin: 10px 0;">No items in this requisition.</p>';
        }
        
        const itemsSummary = itemsList.join(', ');

        // Fill Actions
        const actionsContainer = document.getElementById('details-modal-actions');
        const footerContainer = actionsContainer.parentElement;
        actionsContainer.innerHTML = '';
        
        let buttonsHtml = '';

        if (req.is_ready) {
            // Render Approve & Decline buttons (Close button is removed as requested)
            buttonsHtml = `
                <button onclick="declineRequisitionFromModal(${req.id}, this, '${req.unique_id}', '${req.requester_name.replace(/'/g, "\\'")}', '${itemsSummary.replace(/'/g, "\\'")}')" style="padding: 10px 30px; background: linear-gradient(135deg, #ef4444, #dc2626); border: none; border-radius: 12px; color: white; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 16px rgba(239, 68, 68, 0.25);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 24px rgba(239, 68, 68, 0.35)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 16px rgba(239, 68, 68, 0.25)'">
                    <i data-lucide="thumbs-down" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 6px;"></i> Decline
                </button>
                <button onclick="approveRequisitionFromModal(${req.id}, this, '${req.unique_id}', '${req.requester_name.replace(/'/g, "\\'")}', '${itemsSummary.replace(/'/g, "\\'")}')" style="padding: 10px 36px; background: linear-gradient(135deg, #6366f1, #4f46e5); border: none; border-radius: 12px; color: white; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 30px rgba(99, 102, 241, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(99, 102, 241, 0.3)'">
                    <i data-lucide="check-circle" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 6px;"></i> Approve
                </button>
            `;
        } else if (req.requires_dg) {
            if (req.dg_status === 'approved') {
                buttonsHtml = `
                    <span class="dg-badge success" style="font-size: 0.82rem; display: inline-flex; justify-content: center; padding: 8px 16px; border-radius: 10px;">
                        <i data-lucide="check" style="width: 14px; height: 14px; margin-right: 6px;"></i> Approved
                    </span>
                `;
            } else if (req.dg_status === 'declined') {
                buttonsHtml = `
                    <span class="dg-badge danger" style="font-size: 0.82rem; display: inline-flex; justify-content: center; padding: 8px 16px; border-radius: 10px;">
                        <i data-lucide="x" style="width: 14px; height: 14px; margin-right: 6px;"></i> Declined
                    </span>
                `;
            }
        }
        
        if (buttonsHtml) {
            actionsContainer.innerHTML = buttonsHtml;
            if (footerContainer) footerContainer.style.display = 'block';
        } else {
            actionsContainer.innerHTML = '';
            if (footerContainer) footerContainer.style.display = 'none';
        }
        
        // Show Modal
        if (modal && content) {
            modal.style.opacity = '1';
            modal.style.pointerEvents = 'auto';
            content.style.transform = 'scale(1)';
        }
        
        if (window.lucide) {
            window.lucide.createIcons({
                node: modal
            });
        }
    }

    function closeRequisitionDetailsModal() {
        const modal = document.getElementById('dg-details-modal');
        const content = document.getElementById('dg-details-modal-content');
        if (modal && content) {
            modal.style.opacity = '0';
            modal.style.pointerEvents = 'none';
            content.style.transform = 'scale(0.95)';
        }
    }

    function approveRequisitionFromModal(id, btn, uniqueId, requesterName, itemsSummary) {
        closeRequisitionDetailsModal();
        approveRequisition(id, btn, uniqueId, requesterName, itemsSummary);
    }

    function declineRequisitionFromModal(id, btn, uniqueId, requesterName, itemsSummary) {
        closeRequisitionDetailsModal();
        openDeclineModal(id, btn, uniqueId, requesterName, itemsSummary);
    }

    function silentRefreshStaffRequisitions() {
        if (document.visibilityState !== 'visible') {
            return;
        }

        // 1. Check if details modal or decline modal is open
        const detailsModal = document.getElementById('dg-details-modal');
        const declineModal = document.getElementById('dg-decline-modal');
        const isDetailsOpen = detailsModal && (window.getComputedStyle(detailsModal).opacity === '1');
        const isDeclineOpen = declineModal && (window.getComputedStyle(declineModal).opacity === '1');

        if (isDetailsOpen || isDeclineOpen) {
            return; // Don't refresh if user is interacting with modal
        }

        // 2. Check if user is active in an input field (search query, dates, etc.)
        if (document.activeElement && ['INPUT', 'SELECT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
            return; // Don't refresh if user is currently typing/selecting
        }

        // 3. Get current filters
        const form = document.getElementById('dg-filter-form');
        let fetchUrl = "{{ route('dg.dashboard') }}";
        if (form) {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            for (const [key, value] of [...params.entries()]) {
                if (!value) params.delete(key);
            }
            const queryString = params.toString();
            if (queryString) fetchUrl += '?' + queryString;
        }

        // 4. Fetch silently
        fetch(fetchUrl)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Update stats cards numbers
                const statNumbers = document.querySelectorAll('.stat-number');
                const newStatNumbers = doc.querySelectorAll('.stat-number');
                statNumbers.forEach((stat, idx) => {
                    if (newStatNumbers[idx]) {
                        stat.innerHTML = newStatNumbers[idx].innerHTML;
                    }
                });

                // Update Staff Requisitions panel
                const oldPanel = document.getElementById('dg-staff-reqs-tab');
                const newPanel = doc.getElementById('dg-staff-reqs-tab');
                if (oldPanel && newPanel) {
                    const scrollTop = oldPanel.scrollTop;
                    oldPanel.innerHTML = newPanel.innerHTML;
                    oldPanel.scrollTop = scrollTop;
                }

                // Update tab badge
                updateStaffRequisitionsTabBadge(doc);

                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
                bindDGPaginationLinks();
            })
            .catch(err => {
                // Ignore silent refresh errors
            });
    }

    function updateStaffRequisitionsTabBadge(doc) {
        const oldBadge = document.getElementById('dg-staff-reqs-badge');
        const newBadge = doc.getElementById('dg-staff-reqs-badge');
        const btn = document.getElementById('tab-btn-staff-reqs');
        if (btn) {
            if (oldBadge) {
                oldBadge.remove();
            }
            if (newBadge) {
                btn.appendChild(newBadge.cloneNode(true));
            }
        }
    }
</script>
@endsection
