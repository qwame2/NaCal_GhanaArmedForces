@extends('layouts.dashboard')
@section('content')
@php
    $isStoresHead = (strcasecmp(auth()->user()->department, 'Stores') === 0 || strcasecmp(auth()->user()->department, 'Store') === 0);
@endphp
<style>
    .req-stat-card {
        background: var(--bg-card);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.2s;
    }

    .req-stat-card:hover {
        transform: translateY(-2px);
    }

    .req-table-row {
        border-bottom: 1px solid var(--border-color);
        transition: .15s;
    }

    .req-table-row:hover {
        background: rgba(99, 102, 241, .03);
    }

    .req-table-row:last-child {
        border-bottom: none;
    }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 99px;
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(6px);
        z-index: 3000;
        display: none;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .swal2-container {
        z-index: 99999 !important;
    }

    .modal-overlay.open {
        display: flex;
    }

    .modal-box {
        background: var(--bg-card);
        border-radius: 24px;
        width: 100%;
        max-width: 920px;
        max-height: 94vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 30px 80px rgba(15, 23, 42, 0.22);
        animation: fadeInModal .35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 2.25rem;
        scroll-behavior: smooth;
    }

    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: transparent;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 99px;
    }

    @keyframes fadeInModal {
        from {
            opacity: 0;
            transform: scale(.96) translateY(10px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    /* Priority-specific visual accents */
    .modal-box.urgent-priority { border-top: 6px solid #dc2626; }
    .modal-box.normal-priority { border-top: 6px solid #4f46e5; }
    .modal-box.low-priority { border-top: 6px solid #64748b; }

    /* Profile Panel & Grid */
    .profile-card {
        display: flex;
        align-items: center;
        gap: 14px;
        background: var(--bg-main);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.15rem;
        transition: all 0.25s ease;
    }

    .profile-card:hover {
        border-color: rgba(99, 102, 241, 0.25);
        background: rgba(99, 102, 241, 0.02);
        transform: translateY(-1px);
    }

    .profile-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: var(--primary-glow);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.25rem;
        border: 1.5px solid rgba(79, 70, 229, 0.15);
    }

    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.72rem;
        font-weight: 800;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        color: var(--text-main);
    }

    .purpose-quote {
        background: var(--bg-main);
        border-left: 4px solid var(--primary);
        border-radius: 4px 16px 16px 4px;
        padding: 1.25rem 1.5rem;
        font-size: 0.88rem;
        color: var(--text-main);
        line-height: 1.6;
        font-style: italic;
        position: relative;
    }

    .purpose-quote:before {
        content: '“';
        font-size: 3.5rem;
        color: rgba(79, 70, 229, 0.08);
        position: absolute;
        top: -0.8rem;
        left: 0.5rem;
        font-family: Georgia, serif;
    }

    /* Item row card */
    .item-decision-card {
        border-bottom: 1px solid var(--border-color);
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        background: var(--bg-card);
    }

    .item-decision-card:last-child {
        border-bottom: none;
    }

    .item-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        width: 100%;
    }

    .item-card-header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
        min-width: 260px;
    }

    .item-card-panel {
        background: var(--bg-main);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
        border: 1px solid var(--border-color);
        width: 100%;
        box-sizing: border-box;
    }

    /* Filter Cards */
    .filter-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.04);
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .filter-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.75rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.25rem;
    }

    .filter-row {
        display: flex;
        gap: 0.85rem;
        flex-wrap: wrap;
        align-items: center;
        width: 100%;
    }

    .filter-field-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .filter-icon {
        position: absolute;
        left: 14px;
        color: var(--text-muted);
        pointer-events: none;
    }

    .filter-control {
        width: 100%;
        padding: 0.7rem 1rem 0.7rem 2.6rem;
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-main);
        color: var(--text-main);
        font-family: inherit;
        font-weight: 600;
        font-size: 0.85rem;
        outline: none;
        transition: all 0.2s ease;
        cursor: pointer;
        appearance: none;
    }

    select.filter-control {
        padding-right: 2.25rem;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19.5 8.25l-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 14px;
    }

    .filter-control:focus {
        border-color: #10b981;
        background: var(--bg-card);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
    }

    .filter-clear-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0.7rem 1.25rem;
        border: 1.5px solid #ef4444;
        border-radius: 12px;
        background: rgba(239, 68, 68, 0.05);
        color: #ef4444;
        font-weight: 800;
        font-size: 0.82rem;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .filter-clear-btn:hover {
        background: #ef4444;
        color: white;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
    }

    /* Decision block */
    .decision-area {
        background: var(--bg-main);
        border: 1px solid var(--border-color);
        border-radius: 18px;
        padding: 1.75rem;
        margin-top: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .decision-text-area {
        width: 100%;
        height: 90px;
        padding: 1rem;
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-card);
        color: var(--text-main);
        font-family: inherit;
        font-weight: 600;
        font-size: 0.88rem;
        resize: none;
        outline: none;
        transition: 0.2s;
    }

    .decision-text-area:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }
</style>

<div style="padding:2rem;">

    {{-- Header --}}
    <div style="margin-bottom:2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="font-size:.7rem;font-weight:800;color:#10b981;text-transform:uppercase;letter-spacing:.12em;margin-bottom:4px;">{{ strtoupper(auth()->user()->department ?? auth()->user()->role) }} · Department Head Hub</div>
            <h1 style="font-size:1.75rem;font-weight:900;color:var(--text-main);letter-spacing:-.03em;margin:0;">Oversight & Approvals</h1>
            <p style="font-size:.9rem;color:var(--text-muted);margin:6px 0 0;">{{ $isStoresHead ? 'Second-tier review of requisitions approved by originating department heads.' : 'First-tier review and approval of store requisitions from your department (' . (auth()->user()->department ?? 'N/A') . ').' }}</p>
        </div>
        <button onclick="window.location.reload()" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--text-main); border-radius:12px; border: 1px solid var(--border-color);">
            <i data-lucide="refresh-cw" style="width: 18px;"></i>
            Refresh
        </button>
    </div>

    {{-- Stats Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.5rem;margin-bottom:2rem;">
        <div class="req-stat-card">
            <div style="width:48px;height:48px;background:rgba(99,102,241,.1);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="clock" style="width:24px;color:#6366f1;"></i></div>
            <div>
                <div style="font-size:1.75rem;font-weight:950;color:var(--text-main); line-height: 1.1;">{{ $stats['pending'] }}</div>
                <div style="font-size:.75rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-top:2px;">Awaiting My Review</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:48px;height:48px;background:rgba(16,185,129,.1);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="check-circle" style="width:24px;color:#10b981;"></i></div>
            <div>
                <div style="font-size:1.75rem;font-weight:950;color:var(--text-main); line-height: 1.1;">{{ $stats['approved'] }}</div>
                <div style="font-size:.75rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-top:2px;">Approved by Me</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:48px;height:48px;background:rgba(239,68,68,.1);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="x-circle" style="width:24px;color:#ef4444;"></i></div>
            <div>
                <div style="font-size:1.75rem;font-weight:950;color:var(--text-main); line-height: 1.1;">{{ $stats['declined'] }}</div>
                <div style="font-size:.75rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-top:2px;">Declined by Me</div>
            </div>
        </div>
    </div>

    {{-- Filters Toolbar --}}
    <div class="filter-card">
        <div class="filter-header">
            <i data-lucide="sliders-horizontal" style="width: 14px; height: 14px; color: #10b981;"></i>
            <span>Filter Criteria</span>
        </div>
        <form method="GET" class="filter-row" id="filter-form" action="{{ route('main-admin.requisitions') }}">
            <div class="filter-field-wrapper" style="min-width: 220px; flex: 1.5;">
                <i data-lucide="activity" class="filter-icon" style="width: 14px; height: 14px;"></i>
                <select name="status" onchange="this.form.submit()" class="filter-control">
                    <option value="pending" {{ request('status', 'pending')==='pending'?'selected':'' }}>Awaiting My Review</option>
                    <option value="approved" {{ request('status')==='approved'?'selected':'' }}>Approved History</option>
                    <option value="declined" {{ request('status')==='declined'?'selected':'' }}>Declined History</option>
                    <option value="history" {{ request('status')==='history'?'selected':'' }}>Oversight History (All)</option>
                </select>
            </div>

            <div class="filter-field-wrapper" style="min-width: 160px; flex: 1;">
                <i data-lucide="alert-circle" class="filter-icon" style="width: 14px; height: 14px;"></i>
                <select name="priority" onchange="this.form.submit()" class="filter-control">
                    <option value="">All Priorities</option>
                    <option value="urgent" {{ request('priority')==='urgent'?'selected':'' }}>Urgent</option>
                    <option value="normal" {{ request('priority')==='normal'?'selected':'' }}>Normal</option>
                    <option value="low" {{ request('priority')==='low'?'selected':'' }}>Low</option>
                </select>
            </div>

            <div class="filter-field-wrapper" style="min-width: 220px; flex: 1.5;">
                <i data-lucide="building" class="filter-icon" style="width: 15px; height: 15px;"></i>
                <input type="text" name="department" value="{{ request('department') }}" placeholder="Filter by department..." class="filter-control" autocomplete="off" onchange="this.form.submit()">
            </div>

            @if(request()->anyFilled(['status','priority','department']) && !(request()->has('status') && request('status') === 'pending' && !request()->has('priority') && !request()->has('department')))
            <a href="{{ route('main-admin.requisitions') }}" class="filter-clear-btn">
                <i data-lucide="x-circle" style="width:16px; height:16px;"></i>
                <span>Clear Filters</span>
            </a>
            @endif
        </form>
    </div>

    {{-- Tabular Requisition List --}}
    <div style="background:var(--bg-card);border-radius:20px;border:1px solid var(--border-color);overflow:hidden; box-shadow:0 15px 30px rgba(0,0,0,0.02);">
        <table style="width:100%;border-collapse:collapse;">
            <thead style="background:var(--bg-main);">
                <tr>
                    <th style="padding:1.25rem 1.5rem;text-align:left;font-size:.72rem;font-weight:900;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Ref ID</th>
                    <th style="padding:1.25rem 1.5rem;text-align:left;font-size:.72rem;font-weight:900;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Department / Requester</th>
                    <th style="padding:1.25rem 1.5rem;text-align:left;font-size:.72rem;font-weight:900;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Items</th>
                    <th style="padding:1.25rem 1.5rem;text-align:center;font-size:.72rem;font-weight:900;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Priority</th>
                    <th style="padding:1.25rem 1.5rem;text-align:center;font-size:.72rem;font-weight:900;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">My Review Status</th>
                    <th style="padding:1.25rem 1.5rem;text-align:left;font-size:.72rem;font-weight:900;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Collection Details</th>
                    <th style="padding:1.25rem 1.5rem;text-align:left;font-size:.72rem;font-weight:900;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Submission Date</th>
                    <th style="padding:1.25rem 1.5rem;text-align:center;font-size:.72rem;font-weight:900;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em; width: 180px;">Review Control</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requisitions as $req)
                @php
                    $pb = $req->priority_badge;
                    $status = $isStoresHead ? $req->main_admin_status : $req->origin_admin_status;

                    if ($status === 'approved') {
                        $sb = ['label' => 'Approved', 'bg' => 'rgba(16, 185, 129, 0.1)', 'color' => '#10b981'];
                    } elseif ($status === 'declined') {
                        $sb = ['label' => 'Declined', 'bg' => 'rgba(239, 68, 68, 0.1)', 'color' => '#ef4444'];
                    } else {
                        $sb = ['label' => 'Awaiting Me', 'bg' => 'rgba(245, 158, 11, 0.1)', 'color' => '#f59e0b'];
                    }
                @endphp
                <tr class="req-table-row">
                    <td style="padding:1.25rem 1.5rem; font-family: monospace; font-weight: 800; color: var(--primary);">
                        {{ $req->unique_id ?: ('REQ-' . str_pad($req->id, 5, '0', STR_PAD_LEFT)) }}
                    </td>
                    <td style="padding:1.25rem 1.5rem;">
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <div style="font-size:.9rem;font-weight:800;color:var(--text-main);">{{ $req->department }}</div>
                            @php $utb = $req->usage_type_badge; @endphp
                            <span class="pill" style="background:{{ $utb['bg'] }}; color:{{ $utb['color'] }}; font-size: 0.6rem; padding: 2px 6px; border-radius: 6px; font-weight:800; text-transform:none; letter-spacing:0;">{{ $utb['label'] }}</span>
                        </div>
                        <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;">{{ $req->requester_name }}{{ $req->rank_or_title ? ' · '.$req->rank_or_title : '' }}</div>
                    </td>
                    <td style="padding:1.25rem 1.5rem;">
                        <div style="display:flex;flex-wrap:wrap;gap:4px;">
                            @foreach($req->items->take(3) as $item)
                            <span style="font-size:.7rem;font-weight:700;color:var(--text-main);background:var(--bg-main);border:1px solid var(--border-color);padding:2px 8px;border-radius:6px;">
                                {{ Str::limit($item->description, 20) }} ({{ number_format($item->quantity_requested,0) }})
                            </span>
                            @endforeach
                            @if($req->items->count() > 3)
                            <span style="font-size:.7rem;font-weight:700;color:#4f46e5;background:rgba(79,70,229,.1);padding:2px 8px;border-radius:6px;">+{{ $req->items->count()-3 }} more</span>
                            @endif
                        </div>
                    </td>
                    <td style="padding:1.25rem 1.5rem;text-align:center;"><span class="pill" style="background:{{ $pb['bg'] }};color:{{ $pb['color'] }};">{{ $pb['label'] }}</span></td>
                    <td style="padding:1.25rem 1.5rem;text-align:center;"><span class="pill" style="background:{{ $sb['bg'] }};color:{{ $sb['color'] }};">● {{ $sb['label'] }}</span></td>
                    <td style="padding:1.25rem 1.5rem;">
                        @if($req->collected_at)
                            <div style="font-size:0.85rem; font-weight:800; color:var(--text-main); display:flex; align-items:center; gap:6px;">
                                <i data-lucide="user" style="width:12px; height:12px; color:#10b981;"></i>
                                {{ $req->collector_name }}
                            </div>
                            <div style="font-size:0.7rem; color:var(--text-muted); font-weight:600; margin-top:2px;">
                                Issued by: <span style="font-weight:800; color:#4f46e5;">{{ $req->collector->name ?? 'Store Officer' }}</span>
                            </div>
                            <div style="font-size:0.65rem; color:#94a3b8; font-weight:600; margin-top:2px;">
                                {{ $req->collected_at->format('d/m/y H:i') }}
                            </div>
                        @else
                            <span style="font-size:0.7rem; font-weight:700; color:var(--text-muted); background:var(--bg-main); padding:3px 8px; border-radius:6px; border:1px dashed var(--border-color); display:inline-flex; align-items:center; gap:4px;">
                                <i data-lucide="clock" style="width:11px; height:11px;"></i> Awaiting Collection
                            </span>
                        @endif
                    </td>
                    <td style="padding:1.25rem 1.5rem;font-size:.78rem;color:var(--text-muted);font-weight:600;">{{ $req->created_at->format('d/m/y') }}<br>{{ $req->created_at->format('H:i') }}</td>
                    <td style="padding:1.25rem 1.5rem;text-align:center;">
                        @if($status === 'pending')
                            <button onclick="openRequisitionModal({{ $req->id }})"
                                style="background:rgba(16,185,129,.1);color:#10b981;border:none;padding:.55rem 1.25rem;border-radius:12px;font-weight:900;font-size:.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:.15s; border: 1px solid rgba(16, 185, 129, 0.25);" onmouseover="this.style.background='#10b981';this.style.color='white'" onmouseout="this.style.background='rgba(16,185,129,.1)';this.style.color='#10b981'">
                                <i data-lucide="shield-alert" style="width:15px;"></i> Review & Verify
                            </button>
                        @else
                            <button onclick="openRequisitionModal({{ $req->id }})"
                                style="background:rgba(99,102,241,.1);color:#4f46e5;border:none;padding:.55rem 1.25rem;border-radius:12px;font-weight:800;font-size:.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:.15s; border: 1px solid rgba(99, 102, 241, 0.25);" onmouseover="this.style.background='#4f46e5';this.style.color='white'" onmouseout="this.style.background='rgba(99,102,241,.1)';this.style.color='#4f46e5'">
                                <i data-lucide="eye" style="width:15px;"></i> View
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding:4rem;text-align:center;color:var(--text-muted);">
                        <i data-lucide="inbox" style="width:40px;margin-bottom:1rem;opacity:.25; color:#10b981;"></i>
                        <h4 style="font-weight:900;color:var(--text-main); margin:0;">All Caught Up!</h4>
                        <p style="font-size:.85rem; margin-top:6px;">No requisitions matches your current filter.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($requisitions->hasPages())
        <div style="padding: 1.5rem; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; background: var(--bg-card); border-radius: 0 0 20px 20px;">
            <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">
                Showing
                <span style="color: var(--text-main); font-weight: 900;">{{ $requisitions->firstItem() ?? 0 }}</span>
                to
                <span style="color: var(--text-main); font-weight: 900;">{{ $requisitions->lastItem() ?? 0 }}</span>
                of
                <span style="color: var(--text-main); font-weight: 900;">{{ $requisitions->total() }}</span>
                entries
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem;">
                {{-- Previous --}}
                @if($requisitions->onFirstPage())
                    <span style="padding: 0.5rem 1rem; border-radius: 8px; background: var(--bg-main); color: var(--text-muted); font-size: 0.8rem; font-weight: 800; border: 1px solid var(--border-color); opacity: 0.5; cursor: not-allowed;">Prev</span>
                @else
                    <a href="{{ $requisitions->appends(request()->query())->previousPageUrl() }}" style="padding: 0.5rem 1rem; border-radius: 8px; background: var(--bg-card); color: var(--text-main); font-size: 0.8rem; font-weight: 800; border: 1px solid var(--border-color); text-decoration: none; transition: 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)'">Prev</a>
                @endif

                {{-- Page Numbers --}}
                <div style="display: flex; gap: 0.25rem;">
                    @foreach($requisitions->appends(request()->query())->getUrlRange(max(1, $requisitions->currentPage()-2), min($requisitions->lastPage(), $requisitions->currentPage()+2)) as $page => $url)
                        @if($page == $requisitions->currentPage())
                            <span style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background: var(--primary); color: white; font-size: 0.85rem; font-weight: 900; box-shadow: 0 4px 10px rgba(99,102,241,0.2);">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background: var(--bg-card); color: var(--text-main); font-size: 0.85rem; font-weight: 800; border: 1px solid var(--border-color); text-decoration: none; transition: 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)'">{{ $page }}</a>
                        @endif
                    @endforeach
                </div>

                {{-- Next --}}
                @if($requisitions->hasMorePages())
                    <a href="{{ $requisitions->appends(request()->query())->nextPageUrl() }}" style="padding: 0.5rem 1rem; border-radius: 8px; background: var(--bg-card); color: var(--text-main); font-size: 0.8rem; font-weight: 800; border: 1px solid var(--border-color); text-decoration: none; transition: 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)'">Next</a>
                @else
                    <span style="padding: 0.5rem 1rem; border-radius: 8px; background: var(--bg-main); color: var(--text-muted); font-size: 0.8rem; font-weight: 800; border: 1px solid var(--border-color); opacity: 0.5; cursor: not-allowed;">Next</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Oversight Approval & Detail Modal --}}
<div class="modal-overlay" id="reqModal" onclick="if(event.target===this)closeModal()">
    <div class="modal-box">
        <div style="padding:1.5rem 2rem;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:1rem;">
                <div style="width:44px;height:44px;background:rgba(16,185,129,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="shield-check" style="width:20px;color:#10b981;"></i>
                </div>
                <div>
                    <h2 style="margin:0;font-size:1.1rem;font-weight:900;color:var(--text-main);">Strategic Oversight Review</h2>
                    <p id="modalSubtitle" style="margin:0;font-size:.8rem;color:var(--text-muted);font-weight:500;"></p>
                </div>
            </div>
            <button onclick="closeModal()" style="background:var(--bg-main);border:none;width:34px;height:34px;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="x" style="width:18px;color:var(--text-muted);"></i>
            </button>
        </div>
        <div class="modal-body" id="modalBody">
            <div style="text-align:center;padding:2rem;color:var(--text-muted);">Loading...</div>
        </div>
        <div id="modalFooter" style="padding:1.25rem 2rem;border-top:1px solid var(--border-color);display:flex;justify-content:flex-end;gap:.75rem;flex-shrink:0;"></div>
    </div>
</div>

<script>
    const isStoresHead = {{ $isStoresHead ? 'true' : 'false' }};
    let currentReqId = null;

    async function openRequisitionModal(id) {
        currentReqId = id;
        document.getElementById('reqModal').classList.add('open');
        document.getElementById('modalBody').innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted);"><div style="width:24px;height:24px;border:2px solid rgba(0,0,0,.1);border-top-color:var(--primary);border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 10px;"></div>Loading details...</div>';
        document.getElementById('modalFooter').innerHTML = '';
        document.getElementById('modalSubtitle').textContent = 'Loading...';

        const res = await fetch(`{{ url('/admin/requisitions') }}/${id}/show`);
        const data = await res.json();

        // Apply priority border accents
        const modalBox = document.querySelector('.modal-box');
        modalBox.className = 'modal-box';
        modalBox.classList.add(`${data.priority}-priority`);

        document.getElementById('modalSubtitle').textContent = `Requisition Ref: ${data.unique_id || ('REQ-' + String(data.id).padStart(5, '0'))}`;

        const avatarLetter = data.requester_name ? data.requester_name.charAt(0).toUpperCase() : 'R';
        const totalItemsCount = data.items.length;
        const totalQtyRequested = data.items.reduce((sum, item) => sum + parseFloat(item.quantity_requested || 0), 0);

        const profileGridHtml = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.75rem;">
            <div class="profile-card">
                <div class="profile-avatar">${avatarLetter}</div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:2px;letter-spacing:0.04em;">Requesting Officer</div>
                    <div style="font-size:1.05rem;font-weight:900;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${data.requester_name}">${data.requester_name}</div>
                    <div style="font-size:.78rem;color:var(--text-muted);font-weight:600;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <i data-lucide="award" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:3px;"></i>${data.rank_or_title || 'No Rank/Title'}
                    </div>
                </div>
            </div>
            <div class="profile-card">
                <div class="profile-avatar" style="background:rgba(16, 185, 129, 0.08); color:#10b981; border-color:rgba(16,185,129,0.15);"><i data-lucide="building" style="width:20px;height:20px;"></i></div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:2px;letter-spacing:0.04em;">Originating Department</div>
                    <div style="font-size:1.05rem;font-weight:900;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${data.department}">${data.department}</div>
                    <div style="font-size:.78rem;color:var(--text-muted);font-weight:600;margin-top:2px;">
                        <i data-lucide="calendar" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:3px;"></i>Submitted ${data.created_at}
                    </div>
                </div>
            </div>

            <div class="profile-card" style="grid-column: 1 / -1; display:flex; flex-direction:column; align-items:stretch; gap:0.75rem;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.04em;">Requisition Purpose</span>
                    <div class="stat-pill-group">
                        <span class="stat-pill" style="background:${data.usage_type_badge.bg}; color:${data.usage_type_badge.color}; font-weight:800;"><i data-lucide="${data.usage_type === 'temporary' ? 'calendar' : 'package-check'}" style="width:12px;"></i> ${data.usage_type_badge.label}</span>
                        <span class="stat-pill"><i data-lucide="layers" style="width:12px;"></i> ${totalItemsCount} ${totalItemsCount === 1 ? 'Item' : 'Items'}</span>
                        <span class="stat-pill"><i data-lucide="hash" style="width:12px;"></i> Total Qty: ${totalQtyRequested.toLocaleString()}</span>
                    </div>
                </div>
                <div class="purpose-quote">
                    ${data.purpose}
                </div>
            </div>
        </div>
        `;

        // Render Requested Items
        const rows = data.items.map(item => {
            const requested = parseFloat(item.quantity_requested) || 0;
            const stockInfo = item.stock_sufficient ?
                `<span style="color:#10b981;font-size:.7rem;font-weight:700;">✔ Sufficient Stock</span>` :
                `<span style="color:#ef4444;font-size:.7rem;font-weight:700;">⚠ Short Stock</span>`;

            return `
            <div class="item-decision-card">
                <div class="item-card-header">
                    <div class="item-card-header-left">
                        <div>
                            <div style="font-size:.95rem;font-weight:800;color:var(--text-main);">${item.description}</div>
                            <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;margin-top:4px;">
                                Unit: ${item.unit} · Stock: ${parseFloat(item.current_stock).toLocaleString()} (${stockInfo})
                            </div>
                        </div>
                    </div>
                </div>

                <div class="item-card-panel" style="gap:1.5rem;">
                    <div style="flex:1; min-width:80px;">
                        <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;">Requested Quantity</div>
                        <div style="font-size:1.15rem;font-weight:900;color:var(--primary);margin-top:2px;">${requested.toLocaleString()}</div>
                    </div>
                </div>
            </div>`;
        }).join('');

        const itemRowsHtml = `
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:16px;overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.01);">
            ${rows}
        </div>`;

        // Check if processed already
        let isProcessed = isStoresHead ? (data.main_admin_status !== 'pending') : (data.origin_admin_status !== 'pending');
        let decisionHtml = '';

        if (!isProcessed) {
            // Render decision actions
            decisionHtml = `
            <div class="decision-area animate-slide-up">
                <div style="font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="message-square" style="width: 14px; color: var(--primary);"></i>
                    Oversight Decision Form
                </div>
                <textarea id="decisionNotes" class="decision-text-area" placeholder="Enter notes or comments regarding this decision (Optional notes for Head, required reason if declining)..."></textarea>

                <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
                    <button onclick="processDecision('declined')" style="flex:1; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1.5px solid rgba(239, 68, 68, 0.25); padding: 0.75rem; border-radius: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem;" onmouseover="this.style.background='#ef4444'; this.style.color='white';" onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'; this.style.color='#ef4444';">
                        <i data-lucide="x-circle" style="width: 18px;"></i>
                        Decline Request
                    </button>
                    <button onclick="processDecision('approved')" style="flex:1.5; background: #10b981; color: white; border: none; padding: 0.75rem; border-radius: 12px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);" onmouseover="this.style.background='#059669';" onmouseout="this.style.background='#10b981';">
                        <i data-lucide="check-circle" style="width: 18px;"></i>
                        Approve & Escalate
                    </button>
                </div>
            </div>`;
        } else {
            // Render decision log status
            const statusVal = isStoresHead ? data.main_admin_status : data.origin_admin_status;
            let decisionLabel = statusVal === 'approved' ? 'APPROVED & ESCALATED' : 'DECLINED';
            let decisionColor = statusVal === 'approved' ? '#10b981' : '#ef4444';
            let decisionBg = statusVal === 'approved' ? 'rgba(16, 185, 129, 0.05)' : 'rgba(239, 68, 68, 0.05)';
            let decisionBorder = statusVal === 'approved' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)';

            decisionHtml = `
            <div style="background: ${decisionBg}; border: 1.5px dashed ${decisionBorder}; border-radius: 16px; padding: 1.25rem; margin-top: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px dashed ${decisionBorder}; padding-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width:34px; height:34px; background:${decisionColor}15; color:${decisionColor}; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                            <i data-lucide="shield-check" style="width:16px;"></i>
                        </div>
                        <div>
                            <h4 style="margin:0; font-size:0.85rem; font-weight:800; color:var(--text-main); text-transform:uppercase; letter-spacing:0.04em;">My Oversight Log</h4>
                        </div>
                    </div>
                    <span class="pill" style="background:${decisionBg}; color:${decisionColor}; font-weight:800; font-size:0.7rem; padding:4px 10px;">${decisionLabel}</span>
                </div>
                ${data.admin_notes ? `
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.75rem 1rem;">
                    <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Oversight Notes / Feedback</div>
                    <div style="font-size:0.9rem; font-weight:700; color:var(--text-main); font-style: italic;">"${data.admin_notes}"</div>
                </div>` : ''}
                ${data.decline_reason ? `
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.75rem 1rem;">
                    <div style="font-size:0.68rem; font-weight:800; color:#ef4444; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Reason for Decline</div>
                    <div style="font-size:0.9rem; font-weight:700; color:#7f1d1d;">${data.decline_reason}</div>
                </div>` : ''}
            </div>`;
        }

        document.getElementById('modalBody').innerHTML = `
        ${profileGridHtml}

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; margin-top:1.5rem;">
            <h3 style="margin:0; font-size:0.95rem; font-weight:900; color:var(--text-main); display:flex; align-items:center; gap:6px;">
                <i data-lucide="list-checks" style="width:16px; color:#10b981;"></i> Requested Items
            </h3>
        </div>

        ${itemRowsHtml}
        ${decisionHtml}
        `;

        document.getElementById('modalFooter').innerHTML = `
        <button onclick="closeModal()" style="background:var(--bg-main); color:var(--text-main); border:1.5px solid var(--border-color); padding:.75rem 1.5rem; border-radius:12px; font-weight:800; cursor:pointer; font-size:.88rem; transition:0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.03)'" onmouseout="this.style.background='var(--bg-main)'">
            Close Panel
        </button>`;

        lucide.createIcons();
    }

    async function processDecision(decision) {
        const notes = document.getElementById('decisionNotes').value.trim();

        if (decision === 'declined' && !notes) {
            Swal.fire({
                title: 'Strategic Security Alert',
                text: 'A formal decline reason must be recorded in the feedback form before de-activating a requisition request.',
                icon: 'warning',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        Swal.fire({
            title: decision === 'approved' ? 'Approve & Escalate?' : 'Decline Requisition?',
            text: decision === 'approved' ?
                (isStoresHead ?
                'This will verify the request and route it immediately to the Head of Stores for final volume allocations.' :
                'This will verify the request and route it immediately to the Department Head (Stores) for review.') :
                'This will de-activate the requisition and return it as declined to the requesting department.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: decision === 'approved' ? 'Yes, Escalate' : 'Yes, Decline',
            cancelButtonText: 'Abort',
            confirmButtonColor: decision === 'approved' ? '#10b981' : '#ef4444',
            cancelButtonColor: '#ef4444',
            customClass: {
                confirmButton: 'premium-swal-btn',
                cancelButton: 'premium-swal-cancel-btn'
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Syncing Decision...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                try {
                    const res = await fetch(`{{ url('/main-admin/requisitions') }}/${currentReqId}/process`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            status: decision,
                            admin_notes: notes,
                            decline_reason: decision === 'declined' ? notes : null
                        })
                    });

                    const responseData = await res.json();

                    if (responseData.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: responseData.message || 'Requisition processed successfully.',
                            icon: 'success',
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            closeModal();
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Failure!',
                            text: responseData.message || 'An error occurred during submission.',
                            icon: 'error',
                            confirmButtonColor: '#4f46e5'
                        });
                    }
                } catch (e) {
                    Swal.fire({
                        title: 'Failure!',
                        text: 'Critical communication sync error.',
                        icon: 'error',
                        confirmButtonColor: '#4f46e5'
                    });
                }
            }
        });
    }

    function closeModal() {
        document.getElementById('reqModal').classList.remove('open');
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection
