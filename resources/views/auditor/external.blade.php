@extends('layouts.dashboard')
@section('content')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    :root {
        --audit-primary: #059669;
        --audit-primary-hover: #047857;
        --audit-slate: #0f172a;
        --audit-slate-light: #1e293b;
        --audit-danger-glow: rgba(239, 68, 68, 0.08);
        --audit-warning-glow: rgba(5, 150, 105, 0.08);
        --audit-info-glow: rgba(59, 130, 246, 0.08);
        --audit-success-glow: rgba(5, 150, 105, 0.08);
        --shadow-premium: 0 20px 40px -15px rgba(15, 23, 42, 0.05), 0 0 0 1px rgba(15, 23, 42, 0.03);
    }

    .auditor-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: var(--shadow-premium);
        transition: transform 0.25s, box-shadow 0.25s;
    }

    .auditor-card:hover {
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

    /* Stepper/Tabs navigation */
    .audit-tabs-container {
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

    .audit-tab-btn {
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

    .audit-tab-btn.active {
        background: var(--bg-card);
        color: var(--audit-primary);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05), 0 0 0 1px rgba(5, 150, 105, 0.1);
    }

    .audit-tab-panel {
        display: none;
        animation: fadeInPanel 0.4s ease;
    }

    .audit-tab-panel.active {
        display: block;
    }

    @keyframes fadeInPanel {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .log-row {
        border-bottom: 1px solid var(--border-color);
        transition: background 0.2s;
    }

    .log-row:hover {
        background: rgba(5, 150, 105, 0.01);
    }

    .log-badge {
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

    .log-badge.danger { background: var(--audit-danger-glow); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    .log-badge.warning { background: var(--audit-warning-glow); color: #059669; border: 1px solid rgba(5, 150, 105, 0.2); }
    .log-badge.info { background: var(--audit-info-glow); color: #059669; border: 1px solid rgba(59, 130, 246, 0.2); }
    .log-badge.success { background: var(--audit-success-glow); color: #059669; border: 1px solid rgba(5, 150, 105, 0.2); }

    .audit-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .audit-table th {
        padding: 1rem 1.25rem;
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        background: rgba(0, 0, 0, 0.01);
        border-bottom: 1px solid var(--border-color);
    }

    .audit-table td {
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.85rem;
        color: var(--text-main);
        vertical-align: middle;
    }

    .audit-table tr:last-child td {
        border-bottom: none;
    }

    .filter-card-audit {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        box-shadow: var(--shadow-premium);
    }

    .filter-controls-grid {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
        width: 100%;
    }

    .filter-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-group .filter-icon {
        position: absolute;
        left: 14px;
        width: 18px;
        height: 18px;
        color: var(--text-muted);
        pointer-events: none;
        z-index: 5;
    }

    .search-group .filter-control-audit {
        padding-left: 2.75rem !important;
    }

    .date-group {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-main);
        padding: 0 12px;
        height: 42px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .date-group .date-label {
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .date-group .filter-control-audit {
        border: none !important;
        background: transparent !important;
        padding: 0 !important;
        min-width: auto !important;
        height: 100% !important;
        font-size: 0.85rem !important;
    }

    .filter-control-audit {
        padding: 0.65rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-main);
        color: var(--text-main);
        font-size: 0.85rem;
        font-weight: 600;
        outline: none;
        transition: all 0.2s;
        height: 42px;
        width: 100%;
        box-sizing: border-box;
    }

    .filter-control-audit:focus {
        border-color: var(--audit-primary);
        box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.1);
        background: var(--bg-card);
    }

    .filter-btn-clear {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 42px;
        padding: 0 1.25rem;
        background: rgba(239, 68, 68, 0.06);
        color: #ef4444;
        border: 1.5px solid rgba(239, 68, 68, 0.2);
        border-radius: 12px;
        text-decoration: none;
        font-weight: 800;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .filter-btn-clear:hover {
        background: rgba(239, 68, 68, 0.1);
        border-color: #ef4444;
        transform: translateY(-1px);
    }

    .external-badge-banner {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
    }

    .external-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(5, 150, 105, 0.18);
        border: 1px solid rgba(5, 150, 105, 0.4);
        border-radius: 99px;
        padding: 6px 16px;
        font-size: 0.7rem;
        font-weight: 900;
        color: #34d399;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        margin-bottom: 1rem;
    }

    /* Precision Pagination Module */
    .audit-pagination-container {
        padding: 1.25rem 1.75rem;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        box-shadow: var(--shadow-premium);
        margin-top: 1.25rem;
    }

    .audit-pagination-info {
        font-size: 0.82rem;
        color: var(--text-muted);
        font-weight: 700;
    }

    .audit-pagination-info span {
        color: var(--text-main);
        font-weight: 800;
    }

    .audit-pagination-buttons {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .audit-page-btn {
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

    .audit-page-btn:hover:not(.disabled) {
        background: var(--audit-primary);
        color: white;
        border-color: var(--audit-primary);
        transform: translateY(-1.5px);
        box-shadow: 0 8px 16px rgba(5, 150, 105, 0.2);
    }

    .audit-page-btn.active-page {
        background: var(--audit-primary);
        color: white;
        border-color: var(--audit-primary);
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
    }

    .audit-page-btn.disabled {
        background: var(--bg-main);
        color: var(--text-muted);
        border-color: var(--border-color);
        cursor: not-allowed;
        box-shadow: none;
        opacity: 0.6;
    }

    /* Select2 Theme Customization for External Audit */
    .select2-container--default .select2-selection--single {
        background-color: var(--bg-main) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 12px !important;
        height: 42px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--text-main) !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        padding-left: 12px !important;
        padding-right: 28px !important;
        line-height: 40px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }
    .select2-dropdown {
        background-color: var(--bg-card) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
        z-index: 9999 !important;
    }
    .select2-results__option {
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        padding: 8px 12px !important;
        color: var(--text-main) !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--audit-primary) !important;
        color: white !important;
    }
    .select2-search--dropdown .select2-search__field {
        border: 1px solid var(--border-color) !important;
        border-radius: 8px !important;
        padding: 6px 10px !important;
        background: var(--bg-main) !important;
        color: var(--text-main) !important;
        outline: none !important;
    }

    /* Skeleton Loader Shimmer */
    @keyframes skeletonShimmer {
        0% { background-position: -200px 0; }
        100% { background-position: 200px 0; }
    }
    .skeleton-box {
        display: inline-block;
        height: 14px;
        width: 85%;
        border-radius: 6px;
        background: linear-gradient(90deg, rgba(0,0,0,0.05) 25%, rgba(0,0,0,0.12) 37%, rgba(0,0,0,0.05) 63%);
        background-size: 400px 100%;
        animation: skeletonShimmer 1.4s ease-in-out infinite;
    }
</style>

<div style="padding: 2rem;">

    {{-- External Auditor Banner --}}
    <div class="external-badge-banner">
        
        <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1.5rem;">
            <div>
                <h1 style="font-size: 2rem; font-weight: 950; letter-spacing: -0.04em; margin: 0; color: white;">
                    External Audit
                </h1>
                <p style="font-size: 0.95rem; color: #94a3b8; margin: 8px 0 0; max-width: 650px; line-height: 1.5;">
                    Independent inspection ledger for external auditing officers and oversight authorities. Access verified system audit trails, inventory batches, asset loans, and requisition records.
                </p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('stockcheck.index') }}" class="glass-card" style="padding: 0.75rem 1.25rem; text-decoration: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 800; color: #34d399; border-radius: 12px; border: 1.5px solid #059669; background: rgba(5, 150, 105,0.2); transition: all 0.2s;">
                    <i data-lucide="clipboard-check" style="width: 18px;"></i>
                    Stock Check
                </a>
                <a id="print-ledger-btn" href="{{ route('auditor.print', array_filter([
                    'date_from' => request('date_from'),
                    'date_to' => request('date_to'),
                    'search_query' => request('search_query'),
                    'log_severity' => request('log_severity'),
                    'log_event' => request('log_event'),
                    'user_id' => request('user_id')
                ], fn($val) => !is_null($val) && $val !== '')) }}" target="_blank" class="glass-card" style="padding: 0.75rem 1.25rem; text-decoration: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 800; color: white; border-radius: 12px; border: 1.5px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.08); transition: all 0.2s;">
                    <i data-lucide="printer" style="width: 18px;"></i>
                    Print External Audit Ledger
                </a>
                <button onclick="window.location.reload()" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: white; border-radius: 12px; border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.05);">
                    <i data-lucide="refresh-cw" style="width: 18px;"></i>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    {{-- Summary Metrics Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
        <div class="auditor-card">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                <div style="width: 32px; height: 32px; background: rgba(5, 150, 105,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="database" style="color: var(--audit-primary); width: 16px;"></i></div>
                System Audit Trail Logs
            </div>
            <div class="stat-number" id="stat-total-logs">{{ number_format($totalLogsCount) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Total archived system events</div>
        </div>

        <div class="auditor-card">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                <div style="width: 32px; height: 32px; background: rgba(239,68,68,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="scale" style="color: #ef4444; width: 16px;"></i></div>
                Inventory Variance
            </div>
            <div class="stat-number" id="stat-total-variance" style="color: {{ $totalVariance > 0 ? '#ef4444' : 'var(--text-main)' }};">{{ number_format($totalVariance) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Physical vs ledger discrepancy logs</div>
        </div>

        <div class="auditor-card">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                <div style="width: 32px; height: 32px; background: rgba(5, 150, 105,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="clock" style="color: #059669; width: 16px;"></i></div>
                Active Temporary Loans
            </div>
            <div class="stat-number" id="stat-active-loans">{{ number_format($activeLoansCount) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Unreturned temporary equipment</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form action="{{ route('external-auditor.dashboard') }}" method="GET" class="filter-card-audit">
        <input type="hidden" name="active_tab" id="active_tab_input" value="{{ request('active_tab', 'audit_trail') }}">
        <div style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="sliders-horizontal" style="width: 14px; color: var(--audit-primary);"></i>
            External Search &amp; Compliance Filter
        </div>
        <div class="filter-controls-grid">
            <div class="filter-group search-group" style="flex: 2; min-width: 300px;">
                <i data-lucide="search" class="filter-icon"></i>
                <input type="text" name="search_query" class="filter-control-audit" placeholder="Search by description, action, supplier, or user..." value="{{ request('search_query') }}">
            </div>

            <div class="filter-group select-group" style="flex: 1.5; min-width: 240px;">
                <select name="user_id" id="audit-user-select" class="filter-control-audit select2" style="width: 100%;">
                    <option value="">-- Audit User --</option>
                    @foreach($auditUsers as $u)
                        @php
                            $roleLabel = $u->role;
                            if ($u->role === 'Main Admin') $roleLabel = 'Head of Admin(Authorizer)';
                            elseif ($u->role === 'Officer') $roleLabel = 'Store Officer';
                            elseif ($u->role === 'Department Head') $roleLabel = 'Department Head';
                        @endphp
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $roleLabel }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group date-group">
                <span class="date-label">From:</span>
                <input type="date" name="date_from" class="filter-control-audit" value="{{ request('date_from') }}">
            </div>

            <div class="filter-group date-group">
                <span class="date-label">To:</span>
                <input type="date" name="date_to" class="filter-control-audit" value="{{ request('date_to') }}">
            </div>

            <div style="display: flex; gap: 8px; align-items: center;">
                <a href="{{ route('external-auditor.dashboard') }}" id="clear-filters-btn" class="filter-btn-clear" style="display: {{ request()->hasAny(['search_query', 'date_from', 'date_to', 'log_severity', 'log_event', 'user_id']) ? 'inline-flex' : 'none' }};">
                    <i data-lucide="x" style="width: 14px;"></i> Clear
                </a>
            </div>
        </div>
    </form>

    @php
        $activeTab = request('active_tab', 'audit_trail');
    @endphp

    {{-- Tabs Container --}}
    <div class="audit-tabs-container">
        <button class="audit-tab-btn {{ $activeTab === 'audit_trail' ? 'active' : '' }}" onclick="switchAuditTab('audit_trail', this)">
            <i data-lucide="shield-alert" style="width: 16px;"></i> System Audit Trail ({{ $systemLogs->total() }})
        </button>
        <button class="audit-tab-btn {{ $activeTab === 'received_items' ? 'active' : '' }}" onclick="switchAuditTab('received_items', this)">
            <i data-lucide="package-check" style="width: 16px;"></i> Received Items ({{ $receivedItems->total() }})
        </button>
        <button class="audit-tab-btn {{ $activeTab === 'issued_items' ? 'active' : '' }}" onclick="switchAuditTab('issued_items', this)">
            <i data-lucide="package-minus" style="width: 16px;"></i> Issued Items ({{ $issuedItems->total() }})
        </button>
        <button class="audit-tab-btn {{ $activeTab === 'returned_items' ? 'active' : '' }}" onclick="switchAuditTab('returned_items', this)">
            <i data-lucide="rotate-ccw" style="width: 16px;"></i> Returned Items ({{ $returnedItems->total() }})
        </button>
        <button class="audit-tab-btn {{ $activeTab === 'requisitions' ? 'active' : '' }}" onclick="switchAuditTab('requisitions', this)">
            <i data-lucide="clipboard-list" style="width: 16px;"></i> Requisitions Log ({{ $requisitions->total() }})
        </button>
    </div>

    {{-- Tab 1: Audit Trail --}}
    <div id="tab-audit_trail" class="audit-tab-panel {{ $activeTab === 'audit_trail' ? 'active' : '' }}">
        <div class="auditor-card" style="padding: 0; overflow: hidden;">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User / Officer</th>
                        <th>Event Type</th>
                        <th>Severity</th>
                        <th>Description / Action</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody id="tbody-audit_trail">
                    @include('auditor._tab_audit_trail', ['systemLogs' => $systemLogs])
                </tbody>
            </table>
        </div>
        <div id="pager-audit_trail" style="margin-top: 1.5rem;">
            @include('auditor._tab_pager', ['items' => $systemLogs, 'param' => 'logs_page'])
        </div>
    </div>

    {{-- Tab 2: Received Items --}}
    <div id="tab-received_items" class="audit-tab-panel {{ $activeTab === 'received_items' ? 'active' : '' }}">
        <div class="auditor-card" style="padding: 0; overflow: hidden;">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Entry Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Acquisition</th>
                        <th>Supplier / Donor</th>
                        <th>Delivery Person</th>
                    </tr>
                </thead>
                <tbody id="tbody-received_items">
                    @include('auditor._tab_received_items', ['receivedItems' => $receivedItems, 'ledgeMap' => $ledgeMap])
                </tbody>
            </table>
        </div>
        <div id="pager-received_items" style="margin-top: 1.5rem;">
            @include('auditor._tab_pager', ['items' => $receivedItems, 'param' => 'received_page'])
        </div>
    </div>

    {{-- Tab 3: Issued Items --}}
    <div id="tab-issued_items" class="audit-tab-panel {{ $activeTab === 'issued_items' ? 'active' : '' }}">
        <div class="auditor-card" style="padding: 0; overflow: hidden;">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Issuance Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Beneficiary / Department</th>
                        <th>Quantity Issued</th>
                        <th>Returned Qty</th>
                        <th>Issuance Type</th>
                    </tr>
                </thead>
                <tbody id="tbody-issued_items">
                    @include('auditor._tab_issued_items', ['issuedItems' => $issuedItems, 'ledgeMap' => $ledgeMap])
                </tbody>
            </table>
        </div>
        <div id="pager-issued_items" style="margin-top: 1.5rem;">
            @include('auditor._tab_pager', ['items' => $issuedItems, 'param' => 'issued_page'])
        </div>
    </div>

    {{-- Tab 4: Returned Items --}}
    <div id="tab-returned_items" class="audit-tab-panel {{ $activeTab === 'returned_items' ? 'active' : '' }}">
        <div class="auditor-card" style="padding: 0; overflow: hidden;">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Return Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Beneficiary</th>
                        <th>Returned Qty</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody id="tbody-returned_items">
                    @include('auditor._tab_returned_items', ['returnedItems' => $returnedItems, 'ledgeMap' => $ledgeMap])
                </tbody>
            </table>
        </div>
        <div id="pager-returned_items" style="margin-top: 1.5rem;">
            @include('auditor._tab_pager', ['items' => $returnedItems, 'param' => 'returned_page'])
        </div>
    </div>

    {{-- Tab 5: Requisitions Log --}}
    <div id="tab-requisitions" class="audit-tab-panel {{ $activeTab === 'requisitions' ? 'active' : '' }}">
        <div class="auditor-card" style="padding: 0; overflow: hidden;">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Req #</th>
                        <th>Date Requested</th>
                        <th>Requester</th>
                        <th>Department</th>
                        <th>Purpose</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tbody-requisitions">
                    @include('auditor._tab_requisitions', ['requisitions' => $requisitions])
                </tbody>
            </table>
        </div>
        <div id="pager-requisitions" style="margin-top: 1.5rem;">
            @include('auditor._tab_pager', ['items' => $requisitions, 'param' => 'requisitions_page'])
        </div>
    </div>

</div>

<script>
    function switchAuditTab(tabId, btn) {
        document.querySelectorAll('.audit-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.audit-tab-panel').forEach(p => p.classList.remove('active'));

        if (!btn && typeof tabId === 'string') {
            btn = document.querySelector(`.audit-tab-btn[onclick*="'${tabId}'"]`);
        }

        if (btn) btn.classList.add('active');
        const panel = document.getElementById('tab-' + tabId);
        if (panel) panel.classList.add('active');

        const activeInput = document.getElementById('active_tab_input');
        if (activeInput) activeInput.value = tabId;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTabParam = urlParams.get('active_tab');
        if (activeTabParam && document.getElementById('tab-' + activeTabParam)) {
            switchAuditTab(activeTabParam);
        }
    });

    function toggleSupplierPopover(btn, event) {
        event.stopPropagation();
        
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        const acq = btn.getAttribute('data-acquisition');
        const delPerson = btn.getAttribute('data-delivery-person');
        const delPhone = btn.getAttribute('data-delivery-phone');
        const icon = btn.querySelector('svg') || btn.querySelector('i');
        
        // Remove existing popover if open
        const existingPopover = document.getElementById('active-supplier-popover');
        if (existingPopover) {
            const existingId = existingPopover.getAttribute('data-trigger-id');
            existingPopover.remove();
            
            document.querySelectorAll('.btn-toggle-supplier-details svg, .btn-toggle-supplier-details i').forEach(el => {
                el.style.transform = 'rotate(0deg)';
            });
            
            if (existingId === id) {
                return;
            }
        }
        
        if (icon) icon.style.transform = 'rotate(180deg)';
        
        const popover = document.createElement('div');
        popover.id = 'active-supplier-popover';
        popover.setAttribute('data-trigger-id', id);
        
        popover.style.position = 'fixed';
        popover.style.backgroundColor = 'var(--bg-card)';
        popover.style.border = '1px solid var(--border-color)';
        popover.style.borderRadius = '16px';
        popover.style.padding = '1.25rem';
        popover.style.boxShadow = '0 10px 30px rgba(0,0,0,0.15), 0 0 1px rgba(0,0,0,0.1)';
        popover.style.zIndex = '10000';
        popover.style.maxHeight = 'min(420px, 80vh)';
        popover.style.overflowY = 'auto';
        
        const rect = btn.getBoundingClientRect();
        const width = Math.min(320, window.innerWidth - 24);
        popover.style.width = width + 'px';
        
        let left = rect.left - (width / 2) + (rect.width / 2);
        left = Math.max(12, Math.min(window.innerWidth - width - 12, left));
        popover.style.left = left + 'px';
        
        const popoverHeight = 380;
        let top = rect.bottom + 8;
        if (top + popoverHeight > window.innerHeight && rect.top - popoverHeight - 8 > 0) {
            top = rect.top - popoverHeight - 8;
        }
        popover.style.top = top + 'px';
        
        popover.innerHTML = `
            <div style="font-size: 0.85rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 8px; margin-bottom: 10px;">
                    <span style="font-weight: 900; color: var(--text-main);">Entity Details</span>
                    <span style="background: rgba(5, 150, 105, 0.1); color: var(--audit-primary); font-size: 0.65rem; font-weight: 800; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">
                        ${acq}
                    </span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;" id="popover-registry-details">
                    <div style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); font-size: 0.85rem; padding: 1rem 0;">
                        <span class="animate-spin" style="display: inline-block;">⚙</span>
                        <span>Querying logs & registry...</span>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(popover);
        
        fetch("{{ route('auditor.supplier_info') }}?name=" + encodeURIComponent(name))
            .then(res => res.json())
            .then(data => {
                const s = data.supplier || {};
                const detailsContainer = document.getElementById('popover-registry-details');
                if (detailsContainer) {
                    detailsContainer.innerHTML = `
                        <div>
                            <span style="font-size: 0.68rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 2px;">Name</span>
                            <span style="font-weight: 750; color: var(--text-main);">${name}</span>
                        </div>
                        <div>
                            <span style="font-size: 0.68rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 2px;">Registry Phone</span>
                            <span style="font-weight: 700; color: var(--text-main);">${s.phone || '-'}</span>
                        </div>
                        <div>
                            <span style="font-size: 0.68rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 2px;">Email Address</span>
                            <span style="font-weight: 700; color: var(--text-main);">${s.email || '-'}</span>
                        </div>
                        <div>
                            <span style="font-size: 0.68rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 2px;">Physical Address</span>
                            <span style="font-weight: 700; color: var(--text-main);">${s.address || '-'}</span>
                        </div>
                        <div>
                            <span style="font-size: 0.68rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 2px;">Contact Person</span>
                            <span style="font-weight: 750; color: var(--text-main);">${s.contact_person || '-'}</span>
                            <span style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-top: 2px;">Phone: ${s.contact_phone || '-'}</span>
                        </div>
                        <div style="border-top: 1px dashed var(--border-color); padding-top: 8px; margin-top: 4px;">
                            <span style="font-size: 0.68rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 2px;">Delivery Representative</span>
                            <span style="font-weight: 750; color: var(--text-main);">${delPerson}</span>
                            <span style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-top: 2px;">Phone: ${delPhone}</span>
                        </div>
                        <div style="display: flex; gap: 12px; border-top: 1px dashed var(--border-color); padding-top: 8px;">
                            <div style="flex: 1;">
                                <span style="font-size: 0.68rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 2px;">First Delivery</span>
                                <span style="font-weight: 800; color: #059669; font-size: 0.75rem;">${data.first_delivery || '-'}</span>
                            </div>
                            <div style="flex: 1;">
                                <span style="font-size: 0.68rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 2px;">Last Delivery</span>
                                <span style="font-weight: 800; color: var(--audit-primary); font-size: 0.75rem;">${data.last_delivery || '-'}</span>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(err => {
                const detailsContainer = document.getElementById('popover-registry-details');
                if (detailsContainer) {
                    detailsContainer.innerHTML = '<span style="color: #ef4444;">Error loading details</span>';
                }
            });
    }

    document.addEventListener('click', function(e) {
        const activePopover = document.getElementById('active-supplier-popover');
        if (activePopover && !activePopover.contains(e.target)) {
            activePopover.remove();
            document.querySelectorAll('.btn-toggle-supplier-details svg, .btn-toggle-supplier-details i').forEach(el => {
                el.style.transform = 'rotate(0deg)';
            });
        }
    });
</script>

<!-- jQuery & Select2 JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    function getSkeletonRowsHtml(columnCount, rowCount = 5) {
        let rows = '';
        for (let r = 0; r < rowCount; r++) {
            let cols = '';
            for (let c = 0; c < columnCount; c++) {
                const width = Math.floor(Math.random() * 35) + 55;
                cols += `<td style="padding: 1.25rem 1rem;"><div class="skeleton-box" style="width: ${width}%;"></div></td>`;
            }
            rows += `<tr class="log-row">${cols}</tr>`;
        }
        return rows;
    }

    let fetchTimeout = null;

    function performLiveAuditFetch() {
        clearTimeout(fetchTimeout);

        const tabCols = {
            'audit_trail': 6,
            'received_items': 6,
            'issued_items': 7,
            'returned_items': 6,
            'requisitions': 6
        };

        for (const [tabKey, cols] of Object.entries(tabCols)) {
            const tbody = document.getElementById('tbody-' + tabKey);
            if (tbody) {
                tbody.innerHTML = getSkeletonRowsHtml(cols, 5);
            }
        }

        const form = document.querySelector('.filter-card-audit');
        if (!form) return;
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.set('format', 'json');

        const clearBtn = document.getElementById('clear-filters-btn');
        let hasFilters = false;
        for (let [k, v] of formData.entries()) {
            if (k !== 'active_tab' && v.trim() !== '') {
                hasFilters = true;
                break;
            }
        }
        if (clearBtn) clearBtn.style.display = hasFilters ? 'inline-flex' : 'none';

        fetch("{{ route('external-auditor.dashboard') }}?" + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data || !data.tabs) return;

            if (data.total_logs !== undefined) {
                const el = document.getElementById('stat-total-logs');
                if (el) el.textContent = data.total_logs;
            }
            if (data.total_variance !== undefined) {
                const el = document.getElementById('stat-total-variance');
                if (el) el.textContent = data.total_variance;
            }
            if (data.active_loans !== undefined) {
                const el = document.getElementById('stat-active-loans');
                if (el) el.textContent = data.active_loans;
            }

            const titleMap = {
                'audit_trail': 'System Audit Trail',
                'received_items': 'Received Items',
                'issued_items': 'Issued Items',
                'returned_items': 'Returned Items',
                'requisitions': 'Requisitions Log'
            };

            const iconMap = {
                'audit_trail': '<i data-lucide="shield-alert" style="width: 16px;"></i>',
                'received_items': '<i data-lucide="package-check" style="width: 16px;"></i>',
                'issued_items': '<i data-lucide="package-minus" style="width: 16px;"></i>',
                'returned_items': '<i data-lucide="rotate-ccw" style="width: 16px;"></i>',
                'requisitions': '<i data-lucide="clipboard-list" style="width: 16px;"></i>'
            };

            for (const [tabKey, tabData] of Object.entries(data.tabs)) {
                const tbody = document.getElementById('tbody-' + tabKey);
                if (tbody && tabData.tbody !== undefined) {
                    tbody.innerHTML = tabData.tbody;
                }
                const pager = document.getElementById('pager-' + tabKey);
                if (pager && tabData.pager !== undefined) {
                    pager.innerHTML = tabData.pager;
                }

                const tabBtn = document.querySelector(`.audit-tab-btn[onclick*="'${tabKey}'"]`);
                if (tabBtn && tabData.total !== undefined) {
                    const label = titleMap[tabKey] || tabKey;
                    const icon = iconMap[tabKey] || '';
                    tabBtn.innerHTML = `${icon} ${label} (${tabData.total.toLocaleString()})`;
                }
            }

            if (window.lucide) lucide.createIcons();

            params.delete('format');
            const cleanQuery = params.toString();
            const newUrl = window.location.pathname + (cleanQuery ? '?' + cleanQuery : '');
            history.replaceState(null, '', newUrl);
        })
        .catch(err => {
            console.error('Error in live audit fetch:', err);
        });
    }

    function debouncedLiveFetch() {
        clearTimeout(fetchTimeout);
        fetchTimeout = setTimeout(performLiveAuditFetch, 300);
    }

    $(document).ready(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('#audit-user-select').select2({
                placeholder: "-- Audit User --",
                allowClear: true,
                width: '100%'
            });
            $('#audit-user-select').on('change', function() {
                debouncedLiveFetch();
            });
        }

        const filterForm = document.querySelector('.filter-card-audit');
        if (filterForm) {
            filterForm.querySelectorAll('input[name="search_query"], input[name="date_from"], input[name="date_to"]').forEach(input => {
                input.addEventListener('input', debouncedLiveFetch);
                input.addEventListener('change', debouncedLiveFetch);
            });
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                performLiveAuditFetch();
            });
        }
    });
</script>
@endsection
