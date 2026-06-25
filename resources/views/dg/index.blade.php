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
</style>

<div style="padding: 2rem;">

    {{-- Header --}}
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <div style="font-size: .7rem; font-weight: 800; color: var(--dg-primary); text-transform: uppercase; letter-spacing: .12em;">
                    Director General Command Center
                </div>
                <span style="background: rgba(99, 102, 241, 0.15); color: var(--dg-primary); font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; padding: 2px 8px; border-radius: 9999px; border: 1px solid rgba(99, 102, 241, 0.25);">
                    DG ONLY
                </span>
            </div>
            <h1 style="font-size: 1.75rem; font-weight: 900; color: var(--text-main); letter-spacing: -.03em; margin: 0;">
                Director General Oversight Terminal
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
                <div style="width: 32px; height: 32px; background: rgba(99,102,241,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="shield-alert" style="color: var(--dg-primary); width: 16px;"></i></div>
                System Audit Logs
            </div>
            <div class="stat-number">{{ number_format($totalLogsCount) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Audit events captured in database</div>
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
                <div style="width: 32px; height: 32px; background: rgba(245,158,11,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="clock" style="color: #f59e0b; width: 16px;"></i></div>
                Active Loans (Temp)
            </div>
            <div class="stat-number">{{ number_format($activeLoansCount) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Unreturned temporary checkouts</div>
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
                Approved Personnel
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

            <select id="dg-log-severity" name="log_severity" class="filter-control-dg">
                <option value="">-- Severity (Logs) --</option>
                <option value="info" {{ request('log_severity') === 'info' ? 'selected' : '' }}>Info</option>
                <option value="warning" {{ request('log_severity') === 'warning' ? 'selected' : '' }}>Warning</option>
                <option value="danger" {{ request('log_severity') === 'danger' ? 'selected' : '' }}>Danger</option>
                <option value="critical" {{ request('log_severity') === 'critical' ? 'selected' : '' }}>Critical</option>
            </select>

            <select id="dg-req-status" name="req_status" class="filter-control-dg">
                <option value="">-- Status (Reqs) --</option>
                <option value="pending" {{ request('req_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('req_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="partially_approved" {{ request('req_status') === 'partially_approved' ? 'selected' : '' }}>Partially Approved</option>
                <option value="declined" {{ request('req_status') === 'declined' ? 'selected' : '' }}>Declined</option>
            </select>

            @if(request()->anyFilled(['search_query', 'date_from', 'date_to', 'log_severity', 'req_status']))
                <a id="btn-dg-clear" href="{{ route('dg.dashboard') }}" class="filter-control-dg" style="background: rgba(239, 68, 68, 0.05); color: #ef4444; border: 1.5px solid #ef4444; text-decoration: none; text-align: center; font-weight: 800; min-width: 100px; display: inline-flex; align-items: center; justify-content: center;">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- Tabs Menu --}}
    <div class="dg-tabs-container">
        <button id="tab-btn-audit-trail" class="dg-tab-btn active" onclick="switchDGTab('dg-audit-trail-tab', this)">
            <i data-lucide="shield-alert" style="width: 16px;"></i>
            System Audit Trail
        </button>
        <button id="tab-btn-stock-oversight" class="dg-tab-btn" onclick="switchDGTab('dg-stock-oversight-tab', this)">
            <i data-lucide="archive" style="width: 16px;"></i>
            Stock Balance Registry
        </button>
        <button id="tab-btn-staff-reqs" class="dg-tab-btn" onclick="switchDGTab('dg-staff-reqs-tab', this)">
            <i data-lucide="file-text" style="width: 16px;"></i>
            Staff Requisitions Registry
        </button>
        <button id="tab-btn-user-presence" class="dg-tab-btn" onclick="switchDGTab('dg-user-presence-tab', this)">
            <i data-lucide="users" style="width: 16px;"></i>
            User Presence Overview
        </button>
    </div>

    {{-- Tab Panels --}}

    {{-- PANEL 1: SYSTEM AUDIT TRAIL --}}
    <div id="dg-audit-trail-tab" class="dg-tab-panel active">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-premium);">
            <div style="overflow-x: auto;">
                <table class="dg-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Category</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Severity</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($systemLogs as $log)
                            <tr class="dg-row">
                                <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem; white-space: nowrap;">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td style="font-weight: 800;">
                                    {{ $log->user ? $log->user->name : 'System Automated' }}
                                    <div style="font-size: 0.75rem; color: var(--dg-primary); font-weight: 800; margin-top: 2px;">
                                        {{ $log->user ? $log->user->role : 'Automated' }}
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; margin-top: 1px;">
                                        {{ $log->user ? '@' . $log->user->username : '' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-event">{{ $log->event_type }}</span>
                                </td>
                                <td style="font-weight: 700; font-family: monospace; color: var(--dg-primary);">
                                    {{ $log->action }}
                                </td>
                                <td style="max-width: 320px; line-height: 1.4; color: var(--text-main); font-weight: 500;">
                                    {{ $log->description }}
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
                                    <span class="dg-badge {{ $sevClass }}">
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
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($systemLogs->hasPages())
                <div class="dg-pagination-container">
                    <div class="dg-pagination-info">
                        Showing <span>{{ $systemLogs->firstItem() ?? 0 }}</span> to <span>{{ $systemLogs->lastItem() ?? 0 }}</span> of <span>{{ $systemLogs->total() }}</span> events
                    </div>
                    <div class="dg-pagination-buttons">
                        @if ($systemLogs->onFirstPage())
                            <span class="dg-page-btn disabled"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</span>
                        @else
                            <a href="{{ $systemLogs->appends(request()->query())->previousPageUrl() }}" class="dg-page-btn"><i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i> Previous</a>
                        @endif

                        @if ($systemLogs->hasMorePages())
                            <a href="{{ $systemLogs->appends(request()->query())->nextPageUrl() }}" class="dg-page-btn">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></a>
                        @else
                            <span class="dg-page-btn disabled">Next <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- PANEL 2: STOCK BALANCE REGISTRY --}}
    <div id="dg-stock-oversight-tab" class="dg-tab-panel">
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
                            <th>Requester</th>
                            <th>Department</th>
                            <th>Purpose</th>
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
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span>{{ $req->unique_id }}</span>
                                        <button onclick="toggleRequisitionItems(this)" style="background: rgba(99, 102, 241, 0.1); border: 1.5px solid rgba(99, 102, 241, 0.2); cursor: pointer; padding: 4px; color: var(--dg-primary); display: inline-flex; align-items: center; justify-content: center; border-radius: 9999px; transition: all 0.2s; outline: none;" class="req-items-toggle-btn" onmouseover="this.style.background='rgba(99, 102, 241, 0.2)'; this.style.borderColor='rgba(99, 102, 241, 0.3)';" onmouseout="this.style.background='rgba(99, 102, 241, 0.1)'; this.style.borderColor='rgba(99, 102, 241, 0.2)';" type="button">
                                            <i data-lucide="chevron-down" style="width: 12px; height: 12px; stroke-width: 3.5;"></i>
                                        </button>
                                    </div>
                                    <div class="req-items-container" style="position: absolute; top: calc(100% - 4px); left: 12px; z-index: 999; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); width: 280px; display: none; flex-direction: column; gap: 6px; line-height: 1.3; text-align: left; font-family: sans-serif;">
                                         <div style="font-size: 0.65rem; font-weight: 800; color: var(--dg-primary); text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid var(--border-color); padding-bottom: 4px; margin-bottom: 4px;">
                                             Requested Items
                                         </div>
                                         <div style="display: flex; flex-direction: column; gap: 6px; max-height: 180px; overflow-y: auto;" class="custom-scrollbar">
                                             @foreach($req->items as $item)
                                                 <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed var(--border-color); padding-bottom: 4px; font-size: 0.75rem;">
                                                     <div style="font-weight: 700; color: var(--text-main); flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $item->description }}">
                                                         {{ $item->description }}
                                                     </div>
                                                     <div style="font-weight: 800; color: var(--dg-primary); margin-left: 8px; flex-shrink: 0;">
                                                         {{ $item->quantity_requested }} {{ $item->unit }}
                                                     </div>
                                                 </div>
                                             @endforeach
                                         </div>
                                    </div>
                                </td>
                                <td style="font-weight: 800;">
                                    {{ $req->requester_name }}
                                    @if($req->rank_or_title)
                                        <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600;">{{ $req->rank_or_title }}</div>
                                    @endif
                                </td>
                                <td style="font-weight: 700; color: var(--text-muted);">
                                    {{ $req->department }}
                                </td>
                                <td style="max-width: 250px; line-height: 1.4; color: var(--text-main); font-weight: 500;">
                                    {{ $req->purpose }}
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
                                    @if($req->requires_dg_approval && ($req->dg_status ?? 'pending') === 'pending' && $req->main_admin_status === 'approved')
                                        <div style="display: flex; gap: 6px; align-items: center;">
                                            <button onclick="approveRequisition({{ $req->id }}, this)" class="dg-action-btn approve" style="padding: 6px 12px; background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; border-radius: 8px; font-size: 0.72rem; font-weight: 800; cursor: pointer; transition: transform 0.15s, box-shadow 0.15s; display: inline-flex; align-items: center; gap: 4px;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                                                <i data-lucide="check-circle" style="width: 13px; height: 13px;"></i> Approve
                                            </button>
                                            <button onclick="openDeclineModal({{ $req->id }}, this)" class="dg-action-btn decline" style="padding: 6px 12px; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; border-radius: 8px; font-size: 0.72rem; font-weight: 800; cursor: pointer; transition: transform 0.15s, box-shadow 0.15s; display: inline-flex; align-items: center; gap: 4px;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                                                <i data-lucide="x-circle" style="width: 13px; height: 13px;"></i> Decline
                                            </button>
                                        </div>
                                    @elseif($req->requires_dg_approval)
                                        @if(($req->dg_status ?? 'pending') === 'approved')
                                            <span class="dg-badge success" style="font-size: 0.65rem;">
                                                <i data-lucide="check" style="width: 10px; height: 10px;"></i> DG Approved
                                            </span>
                                        @elseif(($req->dg_status ?? 'pending') === 'declined')
                                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                                <span class="dg-badge danger" style="font-size: 0.65rem;">
                                                    <i data-lucide="x" style="width: 10px; height: 10px;"></i> DG Declined
                                                </span>
                                                @if($req->dg_decline_reason)
                                                    <div style="font-size: 0.65rem; color: var(--text-muted); font-style: italic; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $req->dg_decline_reason }}">
                                                        Reason: {{ $req->dg_decline_reason }}
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">-</span>
                                        @endif
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">-</span>
                                    @endif
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

</div>

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
        const container = btn.closest('td').querySelector('.req-items-container');
        if (container) {
            const isHidden = window.getComputedStyle(container).display === 'none';
            // Hide all other requisition items detail containers first
            document.querySelectorAll('.req-items-container').forEach(c => {
                if (c !== container) {
                    c.style.display = 'none';
                    const parentBtn = c.closest('td').querySelector('.req-items-toggle-btn');
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

    function switchDGTab(panelId, btn) {
        // Toggle Buttons
        document.querySelectorAll('.dg-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Toggle Panels
        document.querySelectorAll('.dg-tab-panel').forEach(p => p.classList.remove('active'));
        document.getElementById(panelId).classList.add('active');

        // Store active tab in localStorage
        localStorage.setItem('active_dg_tab', panelId);

        // Dynamically show/hide Severity and Status dropdowns
        const severitySelect = document.getElementById('dg-log-severity');
        const statusSelect = document.getElementById('dg-req-status');
        
        if (severitySelect) {
            severitySelect.style.display = (panelId === 'dg-audit-trail-tab') ? 'inline-block' : 'none';
        }
        
        if (statusSelect) {
            statusSelect.style.display = (panelId === 'dg-staff-reqs-tab') ? 'inline-block' : 'none';
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
        const inputsToCheck = ['search_query', 'date_from', 'date_to', 'log_severity', 'req_status'];
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
                const tabPanels = ['dg-audit-trail-tab', 'dg-stock-oversight-tab', 'dg-staff-reqs-tab', 'dg-user-presence-tab'];
                tabPanels.forEach(panelId => {
                    const oldPanel = document.getElementById(panelId);
                    const newPanel = doc.getElementById(panelId);
                    if (oldPanel && newPanel) {
                        oldPanel.innerHTML = newPanel.innerHTML;
                    }
                });

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
                            const tabPanels = ['dg-audit-trail-tab', 'dg-stock-oversight-tab', 'dg-staff-reqs-tab', 'dg-user-presence-tab'];
                            tabPanels.forEach(panelId => {
                                const oldPanel = document.getElementById(panelId);
                                const newPanel = doc.getElementById(panelId);
                                if (oldPanel && newPanel) {
                                    oldPanel.innerHTML = newPanel.innerHTML;
                                }
                            });
                            
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
        const savedTab = localStorage.getItem('active_dg_tab') || 'dg-audit-trail-tab';
        const btn = Array.from(document.querySelectorAll('.dg-tab-btn')).find(b => b.getAttribute('onclick').includes(savedTab));
        if (btn) {
            btn.click();
        } else {
            const defaultBtn = document.getElementById('tab-btn-audit-trail');
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
            if (!e.target.closest('.req-items-toggle-btn') && !e.target.closest('.req-items-container')) {
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
    });

    let currentDeclineId = null;

    function openDeclineModal(id, btn) {
        currentDeclineId = id;
        const modal = document.getElementById('dg-decline-modal');
        const content = document.getElementById('dg-decline-modal-content');
        const idLabel = document.getElementById('decline-modal-req-id');
        const textInput = document.getElementById('decline-reason-input');

        if (idLabel) idLabel.textContent = `#${id}`;
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
            const res = await fetch(`/dg/requisitions/${currentDeclineId}/process`, {
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

    async function approveRequisition(id, btn) {
        const confirmResult = await Swal.fire({
            title: 'Approve Requisition?',
            text: `Are you sure you want to approve store requisition #${id}?`,
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
            const res = await fetch(`/dg/requisitions/${id}/process`, {
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
</script>
@endsection
