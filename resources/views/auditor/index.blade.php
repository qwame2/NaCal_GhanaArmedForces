@extends('layouts.dashboard')

@section('content')
<style>
    :root {
        --audit-primary: #6366f1;
        --audit-primary-hover: #4f46e5;
        --audit-slate: #0f172a;
        --audit-slate-light: #1e293b;
        --audit-danger-glow: rgba(239, 68, 68, 0.08);
        --audit-warning-glow: rgba(245, 158, 11, 0.08);
        --audit-info-glow: rgba(59, 130, 246, 0.08);
        --audit-success-glow: rgba(16, 185, 129, 0.08);
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
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05), 0 0 0 1px rgba(99, 102, 241, 0.1);
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

    /* Logs view layout */
    .log-row {
        border-bottom: 1px solid var(--border-color);
        transition: background 0.2s;
    }

    .log-row:hover {
        background: rgba(99, 102, 241, 0.01);
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
    .log-badge.warning { background: var(--audit-warning-glow); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
    .log-badge.info { background: var(--audit-info-glow); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }
    .log-badge.success { background: var(--audit-success-glow); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }

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
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
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
    .audit-pagination-container {
        padding: 1.5rem 1.75rem;
        background: rgba(0, 0, 0, 0.01);
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
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
        gap: 8px;
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
        box-shadow: 0 8px 16px rgba(99, 102, 241, 0.2);
    }

    .audit-page-btn.disabled {
        background: var(--bg-main);
        color: var(--text-muted);
        border-color: var(--border-color);
        cursor: not-allowed;
        box-shadow: none;
        opacity: 0.6;
    }

    /* ── Select2 overrides for Audit User ── */
    #audit-user-select + .select2-container {
        min-width: 220px !important;
    }
    .select2-container--default .select2-selection--single {
        background: var(--bg-main) !important;
        border: 1px solid var(--border-color) !important;
        height: 42px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        transition: border-color 0.2s, box-shadow 0.2s !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--audit-primary) !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
        background: var(--bg-card) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--text-main) !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        padding-left: 14px !important;
        padding-right: 24px !important;
        line-height: 42px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: var(--text-muted) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 10px !important;
    }
    .select2-dropdown {
        border: 1px solid var(--border-color) !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
        background: var(--bg-card) !important;
        padding: 4px !important;
    }
    .select2-results__option {
        padding: 8px 12px !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        color: var(--text-main) !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: var(--audit-primary) !important;
        color: white !important;
    }
    @keyframes blink-danger-pulse {
        0% {
            opacity: 1;
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
        }
        70% {
            opacity: 0.8;
            box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
        }
        100% {
            opacity: 1;
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
        }
    }
    .blinking-danger-badge {
        background: #ef4444 !important;
        color: white !important;
        animation: blink-danger-pulse 1.5s infinite;
        font-weight: 900;
        display: inline-block;
    }
    @keyframes blink-text-red {
        0%, 100% { color: #ef4444; }
        50% { color: rgba(239, 68, 68, 0.4); }
    }
    .audit-tab-btn.pending-sras-active {
        animation: blink-text-red 1.5s infinite;
        font-weight: 900 !important;
        border: 1.5px dashed rgba(239, 68, 68, 0.3) !important;
        background: rgba(239, 68, 68, 0.02) !important;
    }
    .audit-tab-btn.pending-sras-active.active {
        background: var(--bg-card) !important;
        border-color: #ef4444 !important;
        box-shadow: 0 10px 25px rgba(239, 68, 68, 0.06), 0 0 0 1px rgba(239, 68, 68, 0.15) !important;
    }
</style>

<div style="padding: 2rem;">

    {{-- Header --}}
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="font-size: .7rem; font-weight: 800; color: var(--audit-primary); text-transform: uppercase; letter-spacing: .12em; margin-bottom: 4px;">
                Internal Audit Oversight Terminal
            </div>
            <h1 style="font-size: 1.75rem; font-weight: 900; color: var(--text-main); letter-spacing: -.03em; margin: 0;">
                Oversight Ledger & Registry Audit
            </h1>
            <p style="font-size: .9rem; color: var(--text-muted); margin: 6px 0 0;">
                System-wide audit trail and real-time transaction verification checks.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('stockcheck.index') }}" class="glass-card" style="padding: 0.75rem 1.25rem; text-decoration: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 800; color: #10b981; border-radius: 12px; border: 1.5px solid #10b981; background: rgba(16,185,129,0.05); transition: all 0.2s;" onmouseover="this.style.background='rgba(16,185,129,0.1)'" onmouseout="this.style.background='rgba(16,185,129,0.05)'">
                <i data-lucide="clipboard-check" style="width: 18px;"></i>
                Perform Stock Check
            </a>
            <a id="print-ledger-btn" href="{{ route('auditor.print', array_filter([
                'date_from' => request('date_from'),
                'date_to' => request('date_to'),
                'search_query' => request('search_query'),
                'log_severity' => request('log_severity'),
                'log_event' => request('log_event'),
                'user_id' => request('user_id')
            ], fn($val) => !is_null($val) && $val !== '')) }}" target="_blank" class="glass-card" style="padding: 0.75rem 1.25rem; text-decoration: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 800; color: var(--audit-primary); border-radius: 12px; border: 1.5px solid var(--audit-primary); background: rgba(99,102,241,0.05); transition: all 0.2s;" onmouseover="this.style.background='rgba(99,102,241,0.1)'" onmouseout="this.style.background='rgba(99,102,241,0.05)'">
                <i data-lucide="printer" style="width: 18px;"></i>
                Print Audit Ledger
            </a>
            <button onclick="window.location.reload()" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--text-main); border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-card);">
                <i data-lucide="refresh-cw" style="width: 18px;"></i>
                Refresh
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
        <div class="auditor-card">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                <div style="width: 32px; height: 32px; background: rgba(99,102,241,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="database" style="color: var(--audit-primary); width: 16px;"></i></div>
                Audit Trail Logs
            </div>
            <div class="stat-number" id="stat-total-logs">{{ number_format($totalLogsCount) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Total events archived in database</div>
        </div>

        <div class="auditor-card">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                <div style="width: 32px; height: 32px; background: rgba(239,68,68,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="scale" style="color: #ef4444; width: 16px;"></i></div>
                System Variance
            </div>
            <div class="stat-number" id="stat-total-variance" style="color: {{ $totalVariance > 0 ? '#ef4444' : 'var(--text-main)' }};">{{ number_format($totalVariance) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Cumulative discrepancy logs</div>
        </div>

        <div class="auditor-card">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                <div style="width: 32px; height: 32px; background: rgba(245,158,11,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="clock" style="color: #f59e0b; width: 16px;"></i></div>
                Active Loans (Temp)
            </div>
            <div class="stat-number" id="stat-active-loans">{{ number_format($activeLoansCount) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Unreturned temporary assets</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form action="{{ route('auditor.dashboard') }}" method="GET" class="filter-card-audit">
        <div style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 6px;">
            <i data-lucide="sliders-horizontal" style="width: 14px; color: var(--audit-primary);"></i>
            Search & Filter Controls
        </div>
        <div class="filter-controls-grid">
            {{-- Search Query --}}
            <div class="filter-group search-group" style="flex: 2; min-width: 300px;">
                <i data-lucide="search" class="filter-icon"></i>
                <input type="text" name="search_query" class="filter-control-audit" placeholder="Search logs by description, action, or event..." value="{{ request('search_query') }}">
            </div>

            {{-- Audit User (Select2) --}}
            <div class="filter-group select-group" style="flex: 1.5; min-width: 240px;">
                <select name="user_id" id="audit-user-select" class="filter-control-audit" style="width: 100%;">
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

            {{-- Severity --}}
            <div class="filter-group select-group" style="flex: 1; min-width: 150px;">
                <select name="log_severity" class="filter-control-audit" style="width: 100%;">
                    <option value="">-- Severity --</option>
                    <option value="info" {{ request('log_severity') === 'info' ? 'selected' : '' }}>Info</option>
                    <option value="warning" {{ request('log_severity') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="danger" {{ request('log_severity') === 'danger' ? 'selected' : '' }}>Danger</option>
                    <option value="critical" {{ request('log_severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>

            {{-- Event Type --}}
            <div class="filter-group select-group" style="flex: 1; min-width: 150px;">
                <select name="log_event" class="filter-control-audit" style="width: 100%;">
                    <option value="">-- Event --</option>
                    <option value="SECURITY" {{ request('log_event') === 'SECURITY' ? 'selected' : '' }}>Security</option>
                    <option value="AUTH" {{ request('log_event') === 'AUTH' ? 'selected' : '' }}>Auth</option>
                    <option value="INVENTORY" {{ request('log_event') === 'INVENTORY' ? 'selected' : '' }}>Inventory</option>
                    <option value="REQUISITION" {{ request('log_event') === 'REQUISITION' ? 'selected' : '' }}>Requisition</option>
                </select>
            </div>

            {{-- Date From --}}
            <div class="filter-group date-group" style="flex: 1.2; min-width: 190px;">
                <span class="date-label">From</span>
                <input type="date" name="date_from" class="filter-control-audit" title="From Date" value="{{ request('date_from') }}">
            </div>

            {{-- Date To --}}
            <div class="filter-group date-group" style="flex: 1.2; min-width: 190px;">
                <span class="date-label">To</span>
                <input type="date" name="date_to" class="filter-control-audit" title="To Date" value="{{ request('date_to') }}">
            </div>

            {{-- Clear Filters Button --}}
            <a href="{{ route('auditor.dashboard') }}" id="clear-filters-btn" class="filter-btn-clear" style="display: {{ request()->anyFilled(['search_query', 'date_from', 'date_to', 'log_severity', 'log_event', 'user_id']) ? 'inline-flex' : 'none' }};">
                <i data-lucide="x" style="width: 16px;"></i>
                Clear
            </a>
        </div>
    </form>

    {{-- Tabs Menu --}}
    <div class="audit-tabs-container">
        <button class="audit-tab-btn active" onclick="switchAuditTab('audit-trail-tab', this)">
            <i data-lucide="shield-alert" style="width: 16px;"></i>
            System Audit Trail
        </button>
        <button class="audit-tab-btn" onclick="switchAuditTab('received-items-tab', this)">
            <i data-lucide="download" style="width: 16px;"></i>
            Received Items Log
        </button>
        <button class="audit-tab-btn" onclick="switchAuditTab('issued-items-tab', this)">
            <i data-lucide="upload" style="width: 16px;"></i>
            Issued Items Log
        </button>
        <button class="audit-tab-btn" onclick="switchAuditTab('returned-items-tab', this)">
            <i data-lucide="undo-2" style="width: 16px;"></i>
            Returned Items Log
        </button>
        <button class="audit-tab-btn" onclick="switchAuditTab('requisitions-tab', this)">
            <i data-lucide="file-text" style="width: 16px;"></i>
            Requisitions Log
        </button>
        <button class="audit-tab-btn @if($pendingSras->count() + $pendingServiceSras->count() > 0) pending-sras-active @endif" onclick="switchAuditTab('pending-sra-tab', this)" style="position: relative;">
            <i data-lucide="file-check" style="width: 16px;"></i>
            Pending SRA Approvals
            @if($pendingSras->count() + $pendingServiceSras->count() > 0)
                <span class="badge blinking-danger-badge" style="position: absolute; top: -8px; right: -8px; padding: 2.5px 6.5px; border-radius: 99px; font-size: 0.65rem; font-weight: 900; z-index: 10;">{{ $pendingSras->count() + $pendingServiceSras->count() }}</span>
            @endif
        </button>

    </div>

    {{-- Tab Panels --}}

    {{-- PANEL 1: SYSTEM AUDIT TRAIL --}}
    <div id="audit-trail-tab" class="audit-tab-panel active">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-premium);">
            <div style="overflow-x: auto;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Event Category</th>
                            <th>Security Action</th>
                            <th>Description</th>
                            <th>Severity</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($systemLogs as $log)
                            <tr class="log-row">
                                <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem; white-space: nowrap;">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td style="font-weight: 800;">
                                    @php
                                        $roleDisplay = 'N/A';
                                        if ($log->user) {
                                            if ($log->user->role === 'Main Admin') {
                                                $roleDisplay = 'Head of Admin(Authorizer)';
                                            } elseif ($log->user->role === 'Head of Stores') {
                                                $roleDisplay = 'Head of Stores';
                                            } elseif ($log->user->is_admin) {
                                                $roleDisplay = 'Head of Stores';
                                            } elseif ($log->user->role === 'Department Head') {
                                                if ($log->user->department === 'Human Resource Management Department') {
                                                    $roleDisplay = 'Dept Head(HR)';
                                                } elseif ($log->user->department === 'Welfare Department') {
                                                    $roleDisplay = 'Head of Welfare';
                                                } else {
                                                    $roleDisplay = 'Dept Head(' . $log->user->department . ')';
                                                }
                                            } elseif ($log->user->role === 'Officer') {
                                                $roleDisplay = 'Store Officer';
                                            } else {
                                                $roleDisplay = $log->user->role;
                                            }
                                            if ($log->user->rank) {
                                                $roleDisplay .= ' (' . $log->user->rank . ')';
                                            }
                                        } else {
                                            $roleDisplay = 'System Automated';
                                        }
                                    @endphp
                                    {{ $log->user ? $log->user->name : 'System Automated' }}
                                    <div style="font-size: 0.75rem; color: var(--audit-primary); font-weight: 800; margin-top: 2px;">{{ $roleDisplay }}</div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; margin-top: 1px;">{{ $log->user ? '@' . $log->user->username : '' }}</div>
                                </td>
                                <td>
                                    <span class="badge-event">{{ $log->event_type }}</span>
                                </td>
                                <td style="font-weight: 700; font-family: monospace; color: var(--audit-primary);">
                                    {{ $log->action }}
                                </td>
                                <td style="max-width: 320px; line-height: 1.4; color: var(--text-main); font-weight: 500;">
                                    {{ $log->friendly_description }}
                                </td>
                                <td>
                                    @php
                                        $sevClass = 'info';
                                        if (in_array(strtolower($log->severity), ['danger', 'critical'])) {
                                            $sevClass = 'danger';
                                        } elseif (strtolower($log->severity) === 'warning') {
                                            $sevClass = 'warning';
                                        } elseif (strtolower($log->severity) === 'success') {
                                            $sevClass = 'success';
                                        }
                                    @endphp
                                    <span class="log-badge {{ $sevClass }}">
                                        {{ $log->severity }}
                                    </span>
                                </td>
                                <td style="font-family: monospace; color: var(--text-muted); font-size: 0.78rem;">
                                    {{ $log->ip_address ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
                                    <i data-lucide="search" style="width: 40px; height: 40px; margin-bottom: 1rem; opacity: 0.25;"></i>
                                    <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No system log events archived.</p>
                                    <p style="font-size: 0.8rem;">Try clearing your filters or adjusting your date range.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($systemLogs->hasPages())
                <div class="audit-pagination-container">
                    <div class="audit-pagination-info">
                        Showing <span>{{ $systemLogs->firstItem() ?? 0 }}</span> to <span>{{ $systemLogs->lastItem() ?? 0 }}</span> of <span>{{ $systemLogs->total() }}</span> events
                    </div>
                    <div class="audit-pagination-buttons">
                        @if ($systemLogs->onFirstPage())
                            <span class="audit-page-btn disabled"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</span>
                        @else
                            <a href="{{ $systemLogs->appends(request()->query())->previousPageUrl() }}" class="audit-page-btn"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</a>
                        @endif

                        @if ($systemLogs->hasMorePages())
                            <a href="{{ $systemLogs->appends(request()->query())->nextPageUrl() }}" class="audit-page-btn">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></a>
                        @else
                            <span class="audit-page-btn disabled">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- PANEL 2: RECEIVED ITEMS LOG --}}
    <div id="received-items-tab" class="audit-tab-panel">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-premium);">
            <div style="overflow-x: auto;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Entry Date</th>
                            <th>Batch ID</th>
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
                        @forelse($receivedItems as $item)
                            <tr class="log-row">
                                <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
                                    {{ $item->entry_date ? \Carbon\Carbon::parse($item->entry_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td style="font-weight: 900; font-family: monospace; color: var(--audit-primary);">
                                    #{{ $item->batch_id }}
                                </td>
                                <td style="font-weight: 800; color: var(--text-main);">
                                    {{ $item->description }}
                                </td>
                                <td>
                                    <span class="badge-event">{{ $ledgeMap[$item->ledge_category] ?? $item->ledge_category }}</span>
                                </td>
                                <td style="font-weight: 800; text-align: center;">
                                    {{ number_format($item->qty) }} <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $item->unit }}</span>
                                </td>
                                <td style="font-weight: 800; text-align: center; color: var(--audit-primary);">
                                    {{ number_format($item->stock_balance) }} <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $item->unit }}</span>
                                </td>
                                <td style="font-weight: 800; text-align: center; color: {{ $item->variance > 0 ? '#ef4444' : 'var(--text-main)' }};">
                                    {{ number_format($item->variance) }}
                                </td>
                                <td style="font-weight: 700;">
                                    {{ $item->acquisition_type }}
                                </td>
                                <td style="font-weight: 700; color: var(--text-muted);">
                                                                    <div style="display: flex; align-items: center; justify-content: flex-start; gap: 8px;">
                                        <span>{{ $item->supplier_name ?: ($item->donor_name ?: 'System') }}</span>
                                        @if($item->supplier_name || $item->donor_name)
                                            <button type="button" class="btn-toggle-supplier-details" 
                                                    data-id="{{ $item->id }}"
                                                    data-name="{{ $item->supplier_name ?: $item->donor_name }}"
                                                    data-acquisition="{{ $item->acquisition_type }}"
                                                    data-delivery-person="{{ $item->delivery_person ?: '-' }}"
                                                    data-delivery-phone="{{ $item->delivery_phone ?: '-' }}"
                                                    style="border: none; background: rgba(99, 102, 241, 0.08); cursor: pointer; color: var(--audit-primary); width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;"
                                                    onmouseover="this.style.background='rgba(99, 102, 241, 0.18)';"
                                                    onmouseout="this.style.background='rgba(99, 102, 241, 0.08)';"
                                                    onclick="toggleSupplierPopover(this, event)">
                                                <i data-lucide="chevron-down" style="width: 14px; height: 14px; transition: transform 0.2s;"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
                                    <i data-lucide="download" style="width: 40px; height: 40px; margin-bottom: 1rem; opacity: 0.25;"></i>
                                    <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No received items logged.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($receivedItems->hasPages())
                <div class="audit-pagination-container">
                    <div class="audit-pagination-info">
                        Showing <span>{{ $receivedItems->firstItem() ?? 0 }}</span> to <span>{{ $receivedItems->lastItem() ?? 0 }}</span> of <span>{{ $receivedItems->total() }}</span> records
                    </div>
                    <div class="audit-pagination-buttons">
                        @if ($receivedItems->onFirstPage())
                            <span class="audit-page-btn disabled"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</span>
                        @else
                            <a href="{{ $receivedItems->appends(request()->query())->previousPageUrl() }}" class="audit-page-btn"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</a>
                        @endif

                        @if ($receivedItems->hasMorePages())
                            <a href="{{ $receivedItems->appends(request()->query())->nextPageUrl() }}" class="audit-page-btn">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></a>
                        @else
                            <span class="audit-page-btn disabled">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- PANEL 3: ISSUED ITEMS LOG --}}
    <div id="issued-items-tab" class="audit-tab-panel">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-premium);">
            <div style="overflow-x: auto;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Date Issued</th>
                            <th>Item Description</th>
                            <th>Category</th>
                            <th>Qty Issued</th>
                            <th>Beneficiary</th>
                            <th>Issuance Type</th>
                            <th>Authority</th>
                            <th style="text-align: center;">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issuedItems as $item)
                            <tr class="log-row">
                                <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
                                    {{ \Carbon\Carbon::parse($item->issuance_date)->format('d/m/Y') }}
                                </td>
                                <td style="font-weight: 800; color: var(--text-main);">
                                    {{ $item->description }}
                                </td>
                                <td>
                                    <span class="badge-event">{{ $ledgeMap[$item->ledge_category] ?? $item->ledge_category }}</span>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    @if($item->issuance_type === 'Temporary' && $item->total_returned > 0)
                                        @if($item->quantity == 0)
                                            <div style="font-weight: 800; color: #10b981;">
                                                {{ number_format($item->total_returned) }} <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $item->unit }}</span>
                                            </div>
                                            <div style="margin-top: 4px; display: flex; justify-content: center;">
                                                <span class="log-badge success" style="padding: 2px 6px; font-size: 0.6rem; font-weight: 900;">Returned</span>
                                            </div>
                                        @else
                                            <div style="font-weight: 800; color: #ea580c;">
                                                {{ number_format($item->quantity + $item->total_returned) }} <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $item->unit }}</span>
                                            </div>
                                            <div style="margin-top: 4px; display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                                <span class="log-badge warning" style="padding: 2px 6px; font-size: 0.6rem; font-weight: 900; line-height: 1;">Partial Return</span>
                                                <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800;">({{ number_format($item->quantity) }} outstanding)</span>
                                            </div>
                                        @endif
                                    @else
                                        <div style="font-weight: 800; color: #ea580c; text-align: center;">
                                            {{ number_format($item->quantity) }} <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $item->unit }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td style="font-weight: 800;">
                                    {{ $item->beneficiary }}
                                </td>
                                <td>
                                    <span class="log-badge {{ $item->issuance_type === 'Temporary' ? 'warning' : 'info' }}">
                                        {{ $item->issuance_type }}
                                    </span>
                                </td>
                                <td style="font-weight: 700; color: var(--text-muted); font-size: 0.8rem; line-height: 1.4;">
                                    @if($item->origin_approved_by || $item->stores_approved_by || $item->dg_approved_by || $item->final_approved_by || $item->store_officer_name)
                                        @if($item->origin_approved_by)
                                            <div>{{ $item->origin_approved_by }} <span style="font-size: 0.68rem; color: var(--audit-primary); font-weight: 800;">(Dept Head)</span></div>
                                        @endif
                                        @if($item->stores_approved_by)
                                            <div style="margin-top: 2px;">{{ $item->stores_approved_by }} <span style="font-size: 0.68rem; color: #f59e0b; font-weight: 800;">(Head of Admin(Authorizer))</span></div>
                                        @endif
                                        @if($item->dg_approved_by)
                                            <div style="margin-top: 2px;">{{ $item->dg_approved_by }} <span style="font-size: 0.68rem; color: #8b5cf6; font-weight: 800;">(Director General)</span></div>
                                        @endif
                                        @if($item->final_approved_by)
                                            <div style="margin-top: 2px;">{{ $item->final_approved_by }} <span style="font-size: 0.68rem; color: #10b981; font-weight: 800;">(Head of Stores)</span></div>
                                        @endif
                                        @if($item->store_officer_name)
                                            <div style="margin-top: 2px;">{{ $item->store_officer_name }} <span style="font-size: 0.68rem; color: #6366f1; font-weight: 800;">(Store Officer)</span></div>
                                        @endif
                                    @else
                                        {{ $item->authority ?: 'N/A' }}
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    @if($item->requisition_id)
                                        <a href="{{ route('requisitions.receipt.print', $item->requisition_id) }}" 
                                           target="_blank" 
                                           class="btn-view-receipt" 
                                           style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; background: rgba(99, 102, 241, 0.08); color: var(--audit-primary); font-size: 0.72rem; font-weight: 800; text-decoration: none; border: 1px solid transparent; transition: all 0.2s;"
                                           onmouseover="this.style.background='var(--audit-primary)'; this.style.color='white';"
                                           onmouseout="this.style.background='rgba(99, 102, 241, 0.08)'; this.style.color='var(--audit-primary)';"
                                           title="Print Requisition Receipt">
                                            <i data-lucide="receipt" style="width: 13px; height: 13px;"></i>
                                            <span>Receipt</span>
                                        </a>
                                    @else
                                        <span style="font-size: 0.72rem; color: var(--text-muted); font-style: italic;">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
                                    <i data-lucide="upload" style="width: 40px; height: 40px; margin-bottom: 1rem; opacity: 0.25;"></i>
                                    <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No issued items logged.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($issuedItems->hasPages())
                <div class="audit-pagination-container">
                    <div class="audit-pagination-info">
                        Showing <span>{{ $issuedItems->firstItem() ?? 0 }}</span> to <span>{{ $issuedItems->lastItem() ?? 0 }}</span> of <span>{{ $issuedItems->total() }}</span> records
                    </div>
                    <div class="audit-pagination-buttons">
                        @if ($issuedItems->onFirstPage())
                            <span class="audit-page-btn disabled"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</span>
                        @else
                            <a href="{{ $issuedItems->appends(request()->query())->previousPageUrl() }}" class="audit-page-btn"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</a>
                        @endif

                        @if ($issuedItems->hasMorePages())
                            <a href="{{ $issuedItems->appends(request()->query())->nextPageUrl() }}" class="audit-page-btn">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></a>
                        @else
                            <span class="audit-page-btn disabled">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- PANEL 4: Returned ITEMS LOG --}}
    <div id="returned-items-tab" class="audit-tab-panel">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-premium);">
            <div style="overflow-x: auto;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Returned Date</th>
                            <th>Item Description</th>
                            <th>Category</th>
                            <th>Qty Returned</th>
                            <th>Borrowing Beneficiary</th>
                            <th>Auditor Verification Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returnedItems as $item)
                            <tr class="log-row">
                                <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
                                    {{ \Carbon\Carbon::parse($item->return_date)->format('d/m/Y') }}
                                </td>
                                <td style="font-weight: 800; color: var(--text-main);">
                                    {{ $item->description }}
                                </td>
                                <td>
                                    <span class="badge-event">{{ $ledgeMap[$item->ledge_category] ?? $item->ledge_category }}</span>
                                </td>
                                <td style="font-weight: 800; text-align: center; color: #10b981;">
                                    {{ number_format($item->returned_qty) }}
                                </td>
                                <td style="font-weight: 800;">
                                    {{ $item->beneficiary }}
                                </td>
                                <td style="color: var(--text-muted); font-weight: 600; line-height: 1.4;">
                                    {{ $item->remarks ?: 'Returned and verified clean.' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
                                    <i data-lucide="undo-2" style="width: 40px; height: 40px; margin-bottom: 1rem; opacity: 0.25;"></i>
                                    <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No returned assets logs.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($returnedItems->hasPages())
                <div class="audit-pagination-container">
                    <div class="audit-pagination-info">
                        Showing <span>{{ $returnedItems->firstItem() ?? 0 }}</span> to <span>{{ $returnedItems->lastItem() ?? 0 }}</span> of <span>{{ $returnedItems->total() }}</span> records
                    </div>
                    <div class="audit-pagination-buttons">
                        @if ($returnedItems->onFirstPage())
                            <span class="audit-page-btn disabled"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</span>
                        @else
                            <a href="{{ $returnedItems->appends(request()->query())->previousPageUrl() }}" class="audit-page-btn"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</a>
                        @endif

                        @if ($returnedItems->hasMorePages())
                            <a href="{{ $returnedItems->appends(request()->query())->nextPageUrl() }}" class="audit-page-btn">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></a>
                        @else
                            <span class="audit-page-btn disabled">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- PANEL 5: REQUISITIONS LOG --}}
    <div id="requisitions-tab" class="audit-tab-panel">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-premium);">
            <div style="overflow-x: auto;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Requisition ID</th>
                            <th>Date Requested</th>
                            <th>Requester Name</th>
                            <th>Department</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th style="text-align: center;">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requisitions as $req)
                            <tr class="log-row">
                                <td style="font-weight: 900; font-family: monospace; color: var(--audit-primary);">
                                    {{ $req->unique_id ?: ('REQ-'.str_pad($req->id,5,'0',STR_PAD_LEFT)) }}
                                </td>
                                <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
                                    {{ $req->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td style="font-weight: 800; color: var(--text-main);">
                                    {{ $req->requester_name }}
                                </td>
                                <td style="font-weight: 700; color: var(--text-muted);">
                                    {{ $req->department }}
                                </td>
                                <td style="max-width: 250px; line-height: 1.4; color: var(--text-main); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $req->purpose }}">
                                    {{ $req->purpose }}
                                </td>
                                <td>
                                    @php $s = $req->status_badge; @endphp
                                    <span class="log-badge" style="background: {{ $s['bg'] }}; color: {{ $s['color'] }}; border: 1px solid {{ $s['color'] }}30; font-size: 0.65rem;">
                                        {{ $s['label'] }}
                                    </span>
                                    @if($req->status === 'pending')
                                        <div style="font-size:0.7rem;color:var(--text-muted);margin-top:4px;font-weight:600;">
                                            Next: <span style="color:var(--text-main);font-weight:800;">{{ $req->approver_name }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    @if($req->collected_at)
                                        <a href="{{ route('requisitions.receipt.print', $req->id) }}" 
                                           target="_blank" 
                                           class="btn-view-receipt" 
                                           style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; background: rgba(99, 102, 241, 0.08); color: var(--audit-primary); font-size: 0.72rem; font-weight: 800; text-decoration: none; border: 1px solid transparent; transition: all 0.2s;"
                                           onmouseover="this.style.background='var(--audit-primary)'; this.style.color='white';"
                                           onmouseout="this.style.background='rgba(99, 102, 241, 0.08)'; this.style.color='var(--audit-primary)';"
                                           title="Print Requisition Receipt">
                                            <i data-lucide="receipt" style="width: 13px; height: 13px;"></i>
                                            <span>Receipt</span>
                                        </a>
                                    @else
                                        <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 800;">Awaiting Collection</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
                                    <i data-lucide="file-text" style="width: 40px; height: 40px; margin-bottom: 1rem; opacity: 0.25;"></i>
                                    <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No store requisitions logged.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requisitions->hasPages())
                <div class="audit-pagination-container">
                    <div class="audit-pagination-info">
                        Showing <span>{{ $requisitions->firstItem() ?? 0 }}</span> to <span>{{ $requisitions->lastItem() ?? 0 }}</span> of <span>{{ $requisitions->total() }}</span> records
                    </div>
                    <div class="audit-pagination-buttons">
                        @if ($requisitions->onFirstPage())
                            <span class="audit-page-btn disabled"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</span>
                        @else
                            <a href="{{ $requisitions->appends(request()->query())->previousPageUrl() }}" class="audit-page-btn"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</a>
                        @endif

                        @if ($requisitions->hasMorePages())
                            <a href="{{ $requisitions->appends(request()->query())->nextPageUrl() }}" class="audit-page-btn">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></a>
                        @else
                            <span class="audit-page-btn disabled">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- PANEL 6: PENDING SRA APPROVALS --}}
    <div id="pending-sra-tab" class="audit-tab-panel">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-premium);">
            <div style="overflow-x: auto;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>SRA ID</th>
                            <th>Entry Date</th>
                            <th>Ledge Category</th>
                            <th>Supplier Name</th>
                            <th>Acquisition Type</th>
                            <th>Stores Approver</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingSras as $batch)
                            @php
                                $cleanSupplier = trim(preg_replace('/\[.*?\]/', '', ($batch->acquisition_type === 'Donor' ? ($batch->donor_name ?: $batch->supplier_name) : $batch->supplier_name) ?? 'N/A'));
                            @endphp
                            <tr class="log-row">
                                <td style="font-weight: 900; font-family: monospace; color: var(--audit-primary);">
                                    SRA-{{ str_pad($batch->id, 6, '0', STR_PAD_LEFT) }}
                                </td>
                                <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem;">
                                    {{ \Carbon\Carbon::parse($batch->entry_date)->format('d/m/Y') }}
                                </td>
                                <td style="font-weight: 800; color: var(--text-main);">
                                    {{ $ledgeMap[$batch->ledge_category] ?? $batch->ledge_category }}
                                </td>
                                <td style="font-weight: 700; color: var(--text-muted);">
                                    {{ $cleanSupplier }}
                                </td>
                                <td>
                                    <span class="log-badge info" style="font-size: 0.65rem;">
                                        {{ $batch->acquisition_type }}
                                    </span>
                                </td>
                                <td style="font-weight: 800; color: var(--text-main);">
                                    {{ $batch->storesApprover->name ?? 'N/A' }}
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <a href="{{ route('receiveditems.sra', $batch->id) }}" 
                                       target="_blank" 
                                       class="btn-view-receipt" 
                                       style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; background: rgba(99, 102, 241, 0.08); color: var(--audit-primary); font-size: 0.72rem; font-weight: 800; text-decoration: none; border: 1px solid transparent; transition: all 0.2s;"
                                       onmouseover="this.style.background='var(--audit-primary)'; this.style.color='white';"
                                       onmouseout="this.style.background='rgba(99, 102, 241, 0.08)'; this.style.color='var(--audit-primary)';"
                                       title="Review SRA Receipt">
                                        <i data-lucide="file-signature" style="width: 13px; height: 13px;"></i>
                                        <span>Review & Approve</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
                                    <i data-lucide="file-check" style="width: 40px; height: 40px; margin-bottom: 1rem; opacity: 0.25;"></i>
                                    <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No SRA receipts pending verification.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Service SRA Audit Approval Modal --}}
<div id="service-sra-audit-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(6px); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:20px; padding:2.5rem; max-width:750px; width:90%; box-shadow:0 25px 60px rgba(0,0,0,0.25); position:relative;">
        <button onclick="closeServiceSraAuditModal()" style="position:absolute; top:1.25rem; right:1.25rem; background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:1.5rem;">&times;</button>
        <div style="display:flex; align-items:center; gap:16px; margin-bottom:1.75rem;">
            <div style="width:48px; height:48px; background:rgba(139,92,246,0.1); border-radius:14px; display:flex; align-items:center; justify-content:center;">
                <i data-lucide="file-signature" style="width:24px; height:24px; color:#8b5cf6;"></i>
            </div>
            <div>
                <div style="font-size:0.75rem; font-weight:800; color:#8b5cf6; text-transform:uppercase; letter-spacing:0.1em; margin-bottom: 2px;">Auditor Review</div>
                <h3 style="margin:0; font-size:1.35rem; font-weight:900; color:var(--text-main);">Service SRA Verification</h3>
            </div>
        </div>
        <div id="ssra-modal-info" style="background:rgba(139,92,246,0.04); border:1px solid rgba(139,92,246,0.12); border-radius:14px; padding:1.5rem; margin-bottom:1.5rem; font-size:0.9rem; color:var(--text-muted); line-height:1.7; border-left: 4px solid #8b5cf6;"></div>
        <div style="margin-bottom:1.75rem;">
            <label style="display:block; font-size:0.78rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:10px;">Auditor Notes (Optional)</label>
            <textarea id="ssra-audit-notes" rows="4" placeholder="Add any verification notes or comments..." style="width:100%; padding:1rem; border:1.5px solid var(--border-color); border-radius:12px; background:var(--bg-main); color:var(--text-main); font-family:inherit; font-size:0.9rem; resize:vertical; outline:none; box-sizing:border-box; line-height: 1.5;"></textarea>
        </div>
        <div>
            <button id="ssra-approve-btn" onclick="submitServiceSraAudit('approved')" style="width:100%; padding:0.95rem; background:linear-gradient(135deg,#10b981,#059669); color:white; border:none; border-radius:12px; font-weight:900; font-size:0.95rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                <i data-lucide="check-circle" style="width:18px; height:18px;"></i> Approve & Forward to Head of Stores
            </button>
        </div>
    </div>
</div>
<script>
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
            
            // Reset all icons transform
            document.querySelectorAll('.btn-toggle-supplier-details svg, .btn-toggle-supplier-details i').forEach(el => {
                el.style.transform = 'rotate(0deg)';
            });
            
            if (existingId === id) {
                return; // just close
            }
        }
        
        // Rotate arrow icon
        if (icon) icon.style.transform = 'rotate(180deg)';
        
        // Create popover element
        const popover = document.createElement('div');
        popover.id = 'active-supplier-popover';
        popover.setAttribute('data-trigger-id', id);
        
        // Styling popover
        popover.style.position = 'fixed';
        popover.style.backgroundColor = 'var(--bg-card)';
        popover.style.border = '1px solid var(--border-color)';
        popover.style.borderRadius = '16px';
        popover.style.padding = '1.25rem';
        popover.style.boxShadow = '0 10px 30px rgba(0,0,0,0.15), 0 0 1px rgba(0,0,0,0.1)';
        popover.style.zIndex = '10000';
        popover.style.maxHeight = 'min(420px, 80vh)';
        popover.style.overflowY = 'auto';
        
        // Position popover next to the button responsively
        const rect = btn.getBoundingClientRect();
        const width = Math.min(320, window.innerWidth - 24);
        popover.style.width = width + 'px';
        
        // Calculate horizontal position
        let left = rect.left - (width / 2) + (rect.width / 2);
        left = Math.max(12, Math.min(window.innerWidth - width - 12, left));
        popover.style.left = left + 'px';
        
        // Calculate vertical position
        const popoverHeight = 380; // Estimated max height
        let top = rect.bottom + 8;
        if (top + popoverHeight > window.innerHeight && rect.top - popoverHeight - 8 > 0) {
            top = rect.top - popoverHeight - 8;
        }
        popover.style.top = top + 'px';
        
        // Initial loader inside popover
        popover.innerHTML = `
            <div style="font-size: 0.85rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 8px; margin-bottom: 10px;">
                    <span style="font-weight: 900; color: var(--text-main);">Entity Details</span>
                    <span style="background: rgba(99, 102, 241, 0.1); color: var(--audit-primary); font-size: 0.65rem; font-weight: 800; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">
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
        
        // Fetch registry and delivery timeline details
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
                                <span style="font-weight: 800; color: #10b981; font-size: 0.75rem;">${data.first_delivery || '-'}</span>
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

    // Dismiss popover on clicking outside
    document.addEventListener('click', function(e) {
        const activePopover = document.getElementById('active-supplier-popover');
        if (activePopover && !activePopover.contains(e.target)) {
            activePopover.remove();
            // Reset rotated arrows
            document.querySelectorAll('.btn-toggle-supplier-details svg, .btn-toggle-supplier-details i').forEach(el => {
                el.style.transform = 'rotate(0deg)';
            });
        }
    });

    function switchAuditTab(panelId, btn) {
        // Toggle Buttons
        document.querySelectorAll('.audit-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Toggle Panels
        document.querySelectorAll('.audit-tab-panel').forEach(p => p.classList.remove('active'));
        document.getElementById(panelId).classList.add('active');

        // Store active tab in localStorage
        localStorage.setItem('active_auditor_tab', panelId);
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Clean up empty parameters from the address bar on load
        const urlObj = new URL(window.location.href);
        let hasEmpty = false;
        const cleanParams = new URLSearchParams();
        for (const [key, val] of urlObj.searchParams.entries()) {
            if (val.trim() === '') {
                hasEmpty = true;
            } else {
                cleanParams.append(key, val);
            }
        }
        if (hasEmpty) {
            const cleanQuery = cleanParams.toString();
            const cleanUrl = urlObj.pathname + (cleanQuery ? '?' + cleanQuery : '');
            window.history.replaceState(null, '', cleanUrl);
        }

        // Restore tab
        const savedTab = localStorage.getItem('active_auditor_tab');
        if (savedTab) {
            const btn = Array.from(document.querySelectorAll('.audit-tab-btn')).find(b => b.getAttribute('onclick').includes(savedTab));
            if (btn) btn.click();
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();

        const form = document.querySelector('.filter-card-audit');
        if (form) {
            const selects = form.querySelectorAll('select');
            const dates = form.querySelectorAll('input[type="date"]');
            const searchInput = form.querySelector('input[name="search_query"]');

            function performAuditAjaxFilter(url) {
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Swap stats
                    const stats = ['stat-total-logs', 'stat-total-variance', 'stat-active-loans'];
                    stats.forEach(id => {
                        const newStatEl = doc.getElementById(id);
                        const currentStatEl = document.getElementById(id);
                        if (newStatEl && currentStatEl) {
                            currentStatEl.innerHTML = newStatEl.innerHTML;
                            if (newStatEl.getAttribute('style')) {
                                currentStatEl.setAttribute('style', newStatEl.getAttribute('style'));
                            }
                        }
                    });

                    // Swap print button href
                    const newPrintBtn = doc.getElementById('print-ledger-btn');
                    const currentPrintBtn = document.getElementById('print-ledger-btn');
                    if (newPrintBtn && currentPrintBtn) {
                        currentPrintBtn.setAttribute('href', newPrintBtn.getAttribute('href'));
                    }

                    // Swap panels
                    const panels = ['audit-trail-tab', 'received-items-tab', 'issued-items-tab', 'returned-items-tab', 'requisitions-tab', 'pending-sra-tab'];
                    panels.forEach(id => {
                        const newPanel = doc.getElementById(id);
                        const currentPanel = document.getElementById(id);
                        if (newPanel && currentPanel) {
                            currentPanel.innerHTML = newPanel.innerHTML;
                        }
                    });

                    // Update Clear button visibility
                    const clearBtn = document.getElementById('clear-filters-btn');
                    if (clearBtn) {
                        const hasFilter = Array.from(selects).some(s => s.value) || 
                                          Array.from(dates).some(d => d.value) || 
                                          (searchInput && searchInput.value.trim() !== '');
                        clearBtn.style.display = hasFilter ? 'inline-flex' : 'none';
                    }

                    // Re-initialize lucide icons
                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    // Update URL
                    history.pushState(null, '', url);
                })
                .catch(error => {
                    console.error('Audit filter fetch error:', error);
                });
            }

            function triggerFilterSubmit() {
                const formData = new FormData(form);
                const params = new URLSearchParams();
                for (const [key, val] of formData.entries()) {
                    if (val !== null && val !== undefined && val.trim() !== '') {
                        params.append(key, val);
                    }
                }
                const queryString = params.toString();
                const url = form.getAttribute('action') + (queryString ? '?' + queryString : '');
                performAuditAjaxFilter(url);
            }

            // Initialize Select2 on the audit user select
            if (window.jQuery && jQuery().select2) {
                $('#audit-user-select').select2({
                    placeholder: '-- Audit User --',
                    allowClear: true
                }).on('change', triggerFilterSubmit);
            }

            selects.forEach(select => {
                if (select.id !== 'audit-user-select') {
                    select.addEventListener('change', triggerFilterSubmit);
                }
            });

            dates.forEach(date => {
                date.addEventListener('change', triggerFilterSubmit);
            });

            if (searchInput) {
                let debounceTimeout;
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimeout);
                    debounceTimeout = setTimeout(triggerFilterSubmit, 500); // 500ms debounce
                });
            }

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                triggerFilterSubmit();
            });

            // Intercept clear button click
            const clearBtn = document.getElementById('clear-filters-btn');
            if (clearBtn) {
                clearBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    form.reset();
                    selects.forEach(s => s.value = '');
                    dates.forEach(d => d.value = '');
                    if (searchInput) searchInput.value = '';
                    if (window.jQuery && jQuery().select2) {
                        $('#audit-user-select').val(null).trigger('change.select2');
                    }
                    performAuditAjaxFilter(clearBtn.getAttribute('href'));
                });
            }

            // Intercept pagination clicks using event delegation
            document.addEventListener('click', (e) => {
                const link = e.target.closest('.audit-page-btn');
                if (link && link.tagName === 'A' && !link.classList.contains('disabled')) {
                    e.preventDefault();
                    performAuditAjaxFilter(link.getAttribute('href'));
                }
            });
        }

        // Load staff provisioning lists
        if (typeof loadTempAccounts === 'function') {
            loadTempAccounts(true);
        }
        if (typeof loadPendingRegistrations === 'function') {
            loadPendingRegistrations(true);
        }

        // Auto-refresh every 10 seconds for real-time responsiveness
        setInterval(() => {
            if (typeof loadTempAccounts === 'function') {
                loadTempAccounts(true);
            }
            if (typeof loadPendingRegistrations === 'function') {
                loadPendingRegistrations(true);
            }
        }, 10000);
    });

    // ── Service SRA Audit Modal ────────────────────────────────────────────────
    let currentServiceSraId = null;

    function openServiceSraAuditModal(btn) {
        const id = btn.getAttribute('data-id');
        const sraNumber = btn.getAttribute('data-sra-number');
        const supplier = btn.getAttribute('data-supplier');
        const details = btn.getAttribute('data-details');

        currentServiceSraId = id;
        document.getElementById('ssra-modal-info').innerHTML =
            `<strong style="color:var(--text-main);">${sraNumber}</strong> &mdash; ${supplier}<br>
            <span style="font-size:0.78rem;">${details}</span>`;
        document.getElementById('ssra-audit-notes').value = '';
        const modal = document.getElementById('service-sra-audit-modal');
        modal.style.display = 'flex';
        if (window.lucide) window.lucide.createIcons({ node: modal });
    }

    function closeServiceSraAuditModal() {
        document.getElementById('service-sra-audit-modal').style.display = 'none';
        currentServiceSraId = null;
    }

    async function submitServiceSraAudit(action) {
        if (!currentServiceSraId) return;

        const notes  = document.getElementById('ssra-audit-notes').value.trim();
        const approveBtn = document.getElementById('ssra-approve-btn');

        approveBtn.disabled = true;
        approveBtn.style.opacity = '0.6';

        try {
            const res = await fetch(`/auditor/service-sra/${currentServiceSraId}/process`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ action, notes }),
            });

            const data = await res.json();

            if (data.success) {
                closeServiceSraAuditModal();
                // Remove the row from the table
                const rows = document.querySelectorAll('#service-sra-pending-section tbody tr');
                rows.forEach(row => {
                    if (row.querySelector(`button[onclick*="${currentServiceSraId}"]`)) {
                        row.remove();
                    }
                });

                // Display success SweetAlert
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Approved!',
                        text: 'Service SRA receipt verified and forwarded to Head of Stores.',
                        timer: 2000,
                        showConfirmButton: false,
                        background: 'var(--bg-card)',
                        color: 'var(--text-main)'
                    });
                } else {
                    alert('Service SRA receipt verified and forwarded to Head of Stores.');
                }

                // Reload page after short delay so counts update
                setTimeout(() => window.location.reload(), 2000);
            } else {
                alert('Error: ' + (data.message || 'Could not process request.'));
            }
        } catch (err) {
            console.error(err);
            alert('A network error occurred. Please try again.');
        } finally {
            if (approveBtn) {
                approveBtn.disabled = false;
                approveBtn.style.opacity = '1';
            }
        }
    }

    // ── Staff Provisioning (AJAX wrappers) ──────────────────────────────────────
    async function loadTempAccounts(isSilent = false) {
        const container = document.getElementById('tempAccountsList');
        if (!container) return;

        if (!isSilent) {
            container.innerHTML = `
                <div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.85rem;">
                    <i data-lucide="loader" style="width:18px;height:18px;display:inline-block;margin-bottom:6px;opacity:.5;"></i><br>Loading department staff directory...
                </div>`;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        try {
            const res = await fetch('{{ route("dept-head.temp-requisitioners.index") }}');
            const data = await res.json();

            if (!data.success || !data.accounts || data.accounts.length === 0) {
                const emptyHtml = `
                    <div style="text-align:center;padding:1.5rem 1rem;border:1px dashed var(--border-color);border-radius:12px;">
                        <div style="font-size:1.75rem;margin-bottom:.4rem;">👥</div>
                        <div style="font-size:.82rem;font-weight:700;color:var(--text-muted);">No department staff found</div>
                        <div style="font-size:.73rem;color:var(--text-muted);margin-top:.2rem;">Any registered staff in your department will appear here.</div>
                    </div>`;
                if (container.innerHTML !== emptyHtml) {
                    container.innerHTML = emptyHtml;
                }
                window._lastStaffDataString = '';
                return;
            }

            const currentDataString = JSON.stringify(data.accounts);
            if (isSilent && window._lastStaffDataString === currentDataString) {
                return;
            }
            window._lastStaffDataString = currentDataString;

            let rows = data.accounts.map(acc => {
                const isAccessActive = acc.can_make_requisition;
                const badgeStyle = isAccessActive 
                    ? 'background:rgba(16,185,129,.1);color:#10b981;' 
                    : 'background:rgba(239,68,68,.1);color:#ef4444;';
                const badgeText = isAccessActive ? 'Active Access' : 'Access Suspended';
                
                const btnStyle = isAccessActive
                    ? 'background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#ef4444;'
                    : 'background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#10b981;';
                const btnText = isAccessActive ? 'Suspend Access' : 'Grant Access';
                const btnIcon = isAccessActive ? 'user-minus' : 'user-check';

                return `
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.9rem 1rem;border-bottom:1px solid var(--border-color);gap:1rem;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:.75rem;">
                        <div style="width:38px;height:38px;border-radius:10px;background:rgba(99,102,241,0.1);display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:800;color:#6366f1;">
                            ${(acc.name || acc.username).charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div style="font-size:.85rem;font-weight:700;color:var(--text-main);">${acc.name || '@' + acc.username}</div>
                            <div style="font-size:.7rem;color:var(--text-muted);">${acc.role} · @${acc.username}</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                        <span style="font-size:.65rem;font-weight:800;padding:3px 8px;border-radius:99px;background:${acc.is_online ? 'rgba(16,185,129,.1)' : 'rgba(100,116,139,.1)'};color:${acc.is_online ? '#10b981' : '#64748b'};">
                            ${acc.is_online ? '● ONLINE' : '○ OFFLINE'}
                        </span>
                        <span style="font-size:.65rem;font-weight:800;padding:3px 8px;border-radius:99px;${badgeStyle}">
                            ${badgeText}
                        </span>
                        <button onclick="toggleStaffAccess(${acc.id}, '${acc.username}', ${isAccessActive})" style="padding:.4rem .7rem;border-radius:8px;font-size:.72rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.3rem;transition:all 0.2s;${btnStyle}">
                            <i data-lucide="${btnIcon}" style="width:13px;height:13px;"></i> ${btnText}
                        </button>
                    </div>
                </div>
                `;
            }).join('');

            container.innerHTML = `<div style="border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">${rows}</div>`;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (e) {
            if (!isSilent) {
                container.innerHTML = `<div style="text-align:center;padding:1rem;color:var(--text-muted);font-size:.8rem;">Failed to load staff list.</div>`;
            }
        }
    }

    async function toggleStaffAccess(id, username, isCurrentlyActive) {
        const actionWord = isCurrentlyActive ? 'Suspend' : 'Grant';
        const actionColor = isCurrentlyActive ? '#ef4444' : '#10b981';
        
        const confirm = await Swal.fire({
            title: `${actionWord} Requisition Access?`,
            text: `Are you sure you want to ${actionWord.toLowerCase()} requisition privileges for @${username}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: actionColor,
            cancelButtonColor: '#64748b',
            confirmButtonText: `Yes, ${actionWord}`,
            cancelButtonText: 'Cancel'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`/dept-head/staff/${id}/toggle-request-access`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ 
                    title: 'Updated!', 
                    text: data.message, 
                    icon: 'success', 
                    timer: 2000, 
                    showConfirmButton: false 
                });
                loadTempAccounts();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Network error while updating access privileges.', 'error');
        }
    }

    async function loadPendingRegistrations(isSilent = false) {
        const container = document.getElementById('pendingRegistrationsList');
        if (!container) return;

        if (!isSilent) {
            container.innerHTML = `
                <div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.85rem;">
                    <i data-lucide="loader" style="width:18px;height:18px;display:inline-block;margin-bottom:6px;opacity:.5;"></i><br>Loading pending registrations...
                </div>`;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        try {
            const res = await fetch('{{ route("dept-head.pending-registrations") }}');
            const data = await res.json();

            if (!data.success || !data.pending || data.pending.length === 0) {
                const emptyHtml = `
                    <div style="text-align:center;padding:1.5rem 1rem;border:1px dashed var(--border-color);border-radius:12px;">
                        <div style="font-size:1.75rem;margin-bottom:.4rem;">👥</div>
                        <div style="font-size:.82rem;font-weight:700;color:var(--text-muted);">No pending registrations</div>
                        <div style="font-size:.73rem;color:var(--text-muted);margin-top:.2rem;">Any pending staff registrations in your department will appear here.</div>
                    </div>`;
                if (container.innerHTML !== emptyHtml) {
                    container.innerHTML = emptyHtml;
                }
                window._lastPendingRegsString = '';
                return;
            }

            const currentDataString = JSON.stringify(data.pending);
            if (isSilent && window._lastPendingRegsString === currentDataString) {
                return;
            }
            window._lastPendingRegsString = currentDataString;

            let rows = data.pending.map(reg => {
                return `
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.9rem 1rem;border-bottom:1px solid var(--border-color);gap:1rem;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:.75rem;">
                        <div style="width:38px;height:38px;border-radius:10px;background:rgba(99,102,241,0.1);display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:800;color:#6366f1;">
                            ${(reg.name || reg.username).charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div style="font-size:.85rem;font-weight:700;color:var(--text-main);">${reg.name}</div>
                            <div style="font-size:.7rem;color:var(--text-muted);">Requisitioner · @${reg.username} · Phone: ${reg.phone} · Staff ID: ${reg.service_number}</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                        <span style="font-size:.65rem;font-weight:800;padding:3px 8px;border-radius:99px;background:rgba(245,158,11,.1);color:#d97706;">
                            PENDING HOD APPROVAL
                        </span>
                        <button onclick="approveRegistration(${reg.id}, '${reg.username}')" style="padding:.4rem .7rem;border-radius:8px;font-size:.72rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.3rem;transition:all 0.2s;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#10b981;">
                            <i data-lucide="user-check" style="width:13px;height:13px;"></i> Approve
                        </button>
                        <button onclick="rejectRegistration(${reg.id}, '${reg.username}')" style="padding:.4rem .7rem;border-radius:8px;font-size:.72rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.3rem;transition:all 0.2s;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#ef4444;">
                            <i data-lucide="user-x" style="width:13px;height:13px;"></i> Decline
                        </button>
                    </div>
                </div>
                `;
            }).join('');

            container.innerHTML = `<div style="border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">${rows}</div>`;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (e) {
            if (!isSilent) {
                container.innerHTML = `<div style="text-align:center;padding:1rem;color:var(--text-muted);font-size:.8rem;">Failed to load pending registrations list.</div>`;
            }
        }
    }

    async function approveRegistration(id, username) {
        const confirm = await Swal.fire({
            title: 'Approve Registration?',
            text: `Are you sure you want to approve requisitioner privileges for @${username}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`/dept-head/registration/${id}/approve`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ 
                    title: 'Approved!', 
                    text: data.message, 
                    icon: 'success', 
                    timer: 2000, 
                    showConfirmButton: false 
                });
                loadPendingRegistrations();
                if (typeof loadTempAccounts === 'function') {
                    loadTempAccounts();
                }
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Network error while approving registration.', 'error');
        }
    }

    async function rejectRegistration(id, username) {
        const confirm = await Swal.fire({
            title: 'Decline Registration?',
            text: `Are you sure you want to decline and remove @${username}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Decline',
            cancelButtonText: 'Cancel'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`/dept-head/registration/${id}/reject`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ 
                    title: 'Declined!', 
                    text: data.message, 
                    icon: 'success', 
                    timer: 2000, 
                    showConfirmButton: false 
                });
                loadPendingRegistrations();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Network error while declining registration.', 'error');
        }
    }

    // Close modal on backdrop click
    document.getElementById('service-sra-audit-modal').addEventListener('click', function(e) {
        if (e.target === this) closeServiceSraAuditModal();
    });
</script>
@endsection
