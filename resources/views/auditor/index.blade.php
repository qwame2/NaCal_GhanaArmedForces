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
        border-radius: 18px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        box-shadow: var(--shadow-premium);
    }

    .filter-control-audit {
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

    .filter-control-audit:focus {
        border-color: var(--audit-primary);
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
            <a href="{{ route('auditor.print') }}?date_from={{ request('date_from') }}&date_to={{ request('date_to') }}" target="_blank" class="glass-card" style="padding: 0.75rem 1.25rem; text-decoration: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 800; color: var(--audit-primary); border-radius: 12px; border: 1.5px solid var(--audit-primary); background: rgba(99,102,241,0.05); transition: all 0.2s;" onmouseover="this.style.background='rgba(99,102,241,0.1)'" onmouseout="this.style.background='rgba(99,102,241,0.05)'">
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
            <div class="stat-number">{{ number_format($totalLogsCount) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Total events archived in database</div>
        </div>

        <div class="auditor-card">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                <div style="width: 32px; height: 32px; background: rgba(239,68,68,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="scale" style="color: #ef4444; width: 16px;"></i></div>
                System Variance
            </div>
            <div class="stat-number" style="color: {{ $totalVariance > 0 ? '#ef4444' : 'var(--text-main)' }};">{{ number_format($totalVariance) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Cumulative discrepancy logs</div>
        </div>

        <div class="auditor-card">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                <div style="width: 32px; height: 32px; background: rgba(245,158,11,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="clock" style="color: #f59e0b; width: 16px;"></i></div>
                Active Loans (Temp)
            </div>
            <div class="stat-number">{{ number_format($activeLoansCount) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Unreturned temporary assets</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form action="{{ route('auditor.dashboard') }}" method="GET" class="filter-card-audit">
        <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">
            Search & Filter Controls
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <input type="text" name="search_query" class="filter-control-audit" placeholder="Search logs, items, names..." value="{{ request('search_query') }}" style="flex: 1; min-width: 240px;">

            <input type="date" name="date_from" class="filter-control-audit" title="From Date" value="{{ request('date_from') }}">
            <input type="date" name="date_to" class="filter-control-audit" title="To Date" value="{{ request('date_to') }}">

            <select name="log_severity" class="filter-control-audit">
                <option value="">-- Severity --</option>
                <option value="info" {{ request('log_severity') === 'info' ? 'selected' : '' }}>Info</option>
                <option value="warning" {{ request('log_severity') === 'warning' ? 'selected' : '' }}>Warning</option>
                <option value="danger" {{ request('log_severity') === 'danger' ? 'selected' : '' }}>Danger</option>
                <option value="critical" {{ request('log_severity') === 'critical' ? 'selected' : '' }}>Critical</option>
            </select>

            <select name="log_event" class="filter-control-audit">
                <option value="">-- Event --</option>
                <option value="SECURITY" {{ request('log_event') === 'SECURITY' ? 'selected' : '' }}>Security</option>
                <option value="AUTH" {{ request('log_event') === 'AUTH' ? 'selected' : '' }}>Auth</option>
                <option value="INVENTORY" {{ request('log_event') === 'INVENTORY' ? 'selected' : '' }}>Inventory</option>
                <option value="REQUISITION" {{ request('log_event') === 'REQUISITION' ? 'selected' : '' }}>Requisition</option>
            </select>

            <button type="submit" class="filter-control-audit" style="background: var(--audit-primary); color: white; border: none; cursor: pointer; font-weight: 800; min-width: 100px;">
                Filter
            </button>
            @if(request()->anyFilled(['search_query', 'date_from', 'date_to', 'log_severity', 'log_event']))
                <a href="{{ route('auditor.dashboard') }}" class="filter-control-audit" style="background: rgba(239, 68, 68, 0.05); color: #ef4444; border: 1.5px solid #ef4444; text-decoration: none; text-align: center; font-weight: 800; min-width: 100px; display: inline-flex; align-items: center; justify-content: center;">
                    Clear
                </a>
            @endif
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
                                            if ($log->user->is_admin) {
                                                $roleDisplay = 'Head of Stores';
                                            } elseif ($log->user->role === 'Main Admin') {
                                                $roleDisplay = 'Dept Head(Stores)';
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
                                    {{ $item->supplier_name ?: ($item->donor_name ?: 'System') }}
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
                                    @if($item->origin_approved_by || $item->stores_approved_by)
                                        @if($item->origin_approved_by)
                                            <div>{{ $item->origin_approved_by }} <span style="font-size: 0.68rem; color: var(--audit-primary); font-weight: 800;">(Dept Head)</span></div>
                                        @endif
                                        @if($item->stores_approved_by)
                                            <div style="margin-top: 2px;">{{ $item->stores_approved_by }} <span style="font-size: 0.68rem; color: #f59e0b; font-weight: 800;">(Stores Dept Head)</span></div>
                                        @endif
                                    @else
                                        {{ $item->authority ?: 'N/A' }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
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

</div>

<script>
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
        // Restore tab
        const savedTab = localStorage.getItem('active_auditor_tab');
        if (savedTab) {
            const btn = Array.from(document.querySelectorAll('.audit-tab-btn')).find(b => b.getAttribute('onclick').includes(savedTab));
            if (btn) btn.click();
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection
