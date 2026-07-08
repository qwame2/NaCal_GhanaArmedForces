@extends('layouts.dashboard')
@section('content')
<style>
    .req-stat-card {
        background: var(--bg-card);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
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
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(6px);
        z-index: 99999 !important;
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
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
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

    .modal-body:hover::-webkit-scrollbar-thumb {
        background: var(--text-muted);
        opacity: 0.6;
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

    .modal-box {
        animation: fadeInModal .35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Priority-specific visual accents */
    .modal-box.urgent-priority {
        border-top: 6px solid #dc2626;
    }

    .modal-box.normal-priority {
        border-top: 6px solid #4f46e5;
    }

    .modal-box.low-priority {
        border-top: 6px solid #64748b;
    }

    /* Horizontal Stepper Timeline */
    .stepper-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        margin-bottom: 2rem;
        background: var(--bg-main);
        padding: 1.25rem 2rem;
        border-radius: 16px;
        border: 1px solid var(--border-color);
    }

    .stepper-line {
        position: absolute;
        top: 50%;
        left: 4rem;
        right: 4rem;
        height: 3px;
        background: var(--border-color);
        z-index: 1;
        transform: translateY(-50%);
    }

    .stepper-progress {
        position: absolute;
        top: 50%;
        left: 4rem;
        height: 3px;
        background: linear-gradient(90deg, var(--primary) 0%, #10b981 100%);
        z-index: 1;
        transform: translateY(-50%);
        transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        width: 33%;
    }

    .stepper-step {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: transform 0.25s ease;
    }

    .stepper-step:hover {
        transform: translateY(-2px);
    }

    .stepper-bubble {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--bg-card);
        border: 3px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.85rem;
        color: var(--text-muted);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    }

    .stepper-label {
        font-size: 0.72rem;
        font-weight: 900;
        color: var(--text-muted);
        margin-top: 8px;
        transition: color 0.3s;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .stepper-step.completed .stepper-bubble {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-color: #10b981;
        color: white;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
    }

    .stepper-step.completed .stepper-label {
        color: #10b981;
    }

    @keyframes activePulse {
        0% {
            box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4);
        }

        70% {
            box-shadow: 0 0 0 8px rgba(79, 70, 229, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(79, 70, 229, 0);
        }
    }

    .stepper-step.active .stepper-bubble {
        background: linear-gradient(135deg, var(--primary) 0%, #4338ca 100%);
        border-color: var(--primary);
        color: white;
        animation: activePulse 2s infinite;
    }

    .stepper-step.active .stepper-label {
        color: var(--primary);
    }

    .stepper-step.declined-step .stepper-bubble {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border-color: #ef4444;
        color: white;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);
    }

    .stepper-step.declined-step .stepper-label {
        color: #ef4444;
    }

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
        border-color: rgba(79, 70, 229, 0.25);
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
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
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
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
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

    /* Item decision row/card */
    .item-decision-card {
        border-bottom: 1px solid var(--border-color);
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        transition: all 0.2s ease;
        background: var(--bg-card);
    }

    .item-decision-card:last-child {
        border-bottom: none;
    }

    .item-decision-card.declined-row {
        background: rgba(239, 68, 68, 0.015);
    }

    .item-decision-card.approved-row {
        background: rgba(16, 185, 129, 0.008);
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

    .item-card-header-right {
        text-align: right;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
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

    /* Visual Progress Fulfill Bar */
    .fulfill-progress-container {
        width: 100%;
        background: var(--border-color);
        height: 6px;
        border-radius: 99px;
        overflow: hidden;
        margin-top: 6px;
    }

    .fulfill-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%);
        border-radius: 99px;
        transition: width 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .fulfill-ratio-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 6px;
        border-radius: 6px;
        font-size: 0.68rem;
        font-weight: 800;
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .fulfill-ratio-badge.reduced {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .fulfill-ratio-badge.declined {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 4px;
    }

    /* Modern Premium Filter Card Section */
    .filter-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.04), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
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
        margin: 0;
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
        transition: color 0.2s ease;
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
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
    }

    select.filter-control {
        padding-right: 2.25rem;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19.5 8.25l-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 14px;
    }

    .filter-control:focus {
        border-color: #4f46e5;
        background: var(--bg-card);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
    }

    .filter-control:focus + .filter-icon {
        color: #4f46e5;
    }

    .filter-control::placeholder {
        color: var(--text-muted);
        opacity: 0.75;
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

    /* Table Stepper/Tracker */
    .mini-tracker {
        display: flex;
        align-items: center;
        gap: 4px;
        position: relative;
        width: 100%;
        max-width: 160px;
        margin: 6px auto 0;
    }

    .mini-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    .mini-dot {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: var(--bg-main);
        border: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        transition: all 0.25s ease;
    }

    .mini-step.completed .mini-dot {
        background: #10b981;
        border-color: #10b981;
        color: white;
    }

    .mini-step.active .mini-dot {
        background: #f97316;
        border-color: #f97316;
        color: white;
        box-shadow: 0 0 8px rgba(249, 115, 22, 0.35);
    }

    .mini-step.declined .mini-dot {
        background: #ef4444;
        border-color: #ef4444;
        color: white;
    }

    .mini-step.bypassed .mini-dot {
        background: #f1f5f9;
        border-color: var(--border-color);
        color: #94a3b8;
    }

    .mini-line {
        flex: 1;
        height: 2px;
        background: var(--border-color);
        position: relative;
        z-index: 1;
    }

    .mini-line.completed {
        background: #10b981;
    }

    .mini-label {
        font-size: 0.6rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-top: 2px;
    }

    .mini-step.completed .mini-label {
        color: #10b981;
    }

    .mini-step.active .mini-label {
        color: #f97316;
    }

    .mini-step.declined .mini-label {
        color: #ef4444;
    }

    .mini-step.bypassed .mini-label {
        color: #94a3b8;
    }

    /* Inline Items styling */
    .table-item-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.76rem;
        font-weight: 700;
        color: var(--text-main);
        background: var(--bg-main);
        border: 1px solid var(--border-color);
        padding: 4px 10px;
        border-radius: 8px;
        margin: 2px;
    }

    .table-item-qty {
        color: #f97316;
        font-weight: 800;
    }

    .table-item-approved {
        color: #10b981;
        font-weight: 800;
    }
</style>

<div style="padding:2rem;">

    {{-- Header --}}
    <div style="margin-bottom:2rem; display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
        <div>
            <div style="font-size:.7rem;font-weight:800;color:#4f46e5;text-transform:uppercase;letter-spacing:.12em;margin-bottom:4px;">Store Management</div>
            <h1 style="font-size:1.75rem;font-weight:900;color:var(--text-main);letter-spacing:-.03em;margin:0;">Store Requisitions</h1>
            <p style="font-size:.9rem;color:var(--text-muted);margin:6px 0 0;">Review and process department item requests</p>
        </div>
        @if(auth()->user()->can_make_requisition)
        <button onclick="openNewReqPanel()" id="new-req-btn"
            style="display:inline-flex;align-items:center;gap:8px;padding:.75rem 1.5rem;background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);color:white;border:none;border-radius:14px;font-weight:800;font-size:.875rem;cursor:pointer;box-shadow:0 4px 15px rgba(79,70,229,.3);transition:all .25s cubic-bezier(.16,1,.3,1);flex-shrink:0;"
            onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 25px rgba(79,70,229,.4)'"
            onmouseout="this.style.transform='';this.style.boxShadow='0 4px 15px rgba(79,70,229,.3)'">
            <i data-lucide="plus-circle" style="width:17px;height:17px;"></i>
            New Requisition
        </button>
        @endif
    </div>

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:2rem;">
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(99,102,241,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="clock" style="width:20px;color:#6366f1;"></i></div>
            <div>
                <div id="stats-pending" style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['pending'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Pending</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(220,38,38,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="alert-triangle" style="width:20px;color:#dc2626;"></i></div>
            <div>
                <div id="stats-urgent" style="font-size:1.5rem;font-weight:900;color:#dc2626;">{{ $stats['urgent'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Urgent</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(16,185,129,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="check-circle" style="width:20px;color:#10b981;"></i></div>
            <div>
                <div id="stats-approved" style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['approved'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Approved</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(245,158,11,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="git-merge" style="width:20px;color:#f59e0b;"></i></div>
            <div>
                <div id="stats-partially-approved" style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['partially_approved'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Partial</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(239,68,68,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="x-circle" style="width:20px;color:#ef4444;"></i></div>
            <div>
                <div id="stats-declined" style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['declined'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Declined</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-card">
        <div class="filter-header">
            <i data-lucide="sliders-horizontal" style="width: 14px; height: 14px; color: #4f46e5;"></i>
            <span>Filter Options</span>
        </div>
        <form method="GET" class="filter-row" id="filter-form" action="{{ route('personnel.requisitions') }}">
            <div class="filter-field-wrapper" style="flex: 1.2; min-width: 220px;">
                <i data-lucide="search" class="filter-icon" style="width: 16px; height: 16px;"></i>
                <input type="text" name="search_id" id="search_id_input" class="filter-control" value="{{ request('search_id') }}" placeholder="Search by ID or Item name..." autocomplete="off">
            </div>

            <div class="filter-field-wrapper" style="min-width: 160px; flex: 1;">
                <i data-lucide="activity" class="filter-icon" style="width: 14px; height: 14px;"></i>
                <select name="status" onchange="updateFilters()" class="filter-control">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status')==='pending'?'selected':'' }}>Pending</option>
                    <option value="approved" {{ request('status')==='approved'?'selected':'' }}>Approved</option>
                    <option value="partially_approved" {{ request('status')==='partially_approved'?'selected':'' }}>Partial</option>
                    <option value="declined" {{ request('status')==='declined'?'selected':'' }}>Declined</option>
                </select>
            </div>

            <div class="filter-field-wrapper" style="min-width: 160px; flex: 1;">
                <i data-lucide="alert-circle" class="filter-icon" style="width: 14px; height: 14px;"></i>
                <select name="priority" onchange="updateFilters()" class="filter-control">
                    <option value="">All Priorities</option>
                    <option value="urgent" {{ request('priority')==='urgent'?'selected':'' }}>Urgent</option>
                    <option value="normal" {{ request('priority')==='normal'?'selected':'' }}>Normal</option>
                    <option value="low" {{ request('priority')==='low'?'selected':'' }}>Low</option>
                </select>
            </div>

            <div class="filter-field-wrapper" style="flex: 1.2; min-width: 220px;">
                <i data-lucide="building" class="filter-icon" style="width: 15px; height: 15px;"></i>
                <input type="text" name="department" id="dept_input" value="{{ request('department') }}" placeholder="Filter by department..." class="filter-control" autocomplete="off">
            </div>

            @if(request()->anyFilled(['status','priority','department','search_id']))
            <a href="{{ route('personnel.requisitions') }}" class="filter-clear-btn">
                <i data-lucide="x-circle" style="width:16px; height:16px;"></i>
                <span>Clear Filters</span>
            </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div id="requisitions-table-container" style="background:var(--bg-card);border-radius:20px;border:1px solid var(--border-color);overflow:hidden; transition: opacity 0.2s ease;">
        <table style="width:100%;border-collapse:collapse;">
            <thead style="background:var(--bg-main);">
                <tr>
                    <th style="padding:.9rem 1.25rem;text-align:left;font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;white-space:nowrap;">Ref</th>
                    <th style="padding:.9rem 1.25rem;text-align:left;font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Requester &amp; Dept</th>
                    <th style="padding:.9rem 1.25rem;text-align:left;font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Items Requested</th>
                    <th style="padding:.9rem 1.25rem;text-align:left;font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Purpose</th>
                    <th style="padding:.9rem 1.25rem;text-align:left;font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Priority</th>
                    <th style="padding:.9rem 1.25rem;text-align:left;font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Status</th>
                    <th style="padding:.9rem 1.25rem;text-align:left;font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Usage</th>
                    <th style="padding:.9rem 1.25rem;text-align:left;font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Submitted</th>
                    <th style="padding:.9rem 1.25rem;text-align:center;font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requisitions as $req)
                @php
                    $sb  = $req->status_badge;
                    $pb  = $req->priority_badge;
                    $utb = $req->usage_type_badge;
                    $purposeText = trim(preg_replace('/\[Expected Return Date:\s*[^\]]+\]/i', '', $req->purpose));
                @endphp
                <tr class="req-table-row" data-req-id="{{ $req->id }}" data-status="{{ $req->status }}" data-collected="{{ $req->collected_at ? '1' : '0' }}">
                    {{-- REF --}}
                    <td style="padding:.9rem 1.25rem;white-space:nowrap;">
                        <span style="font-size:0.78rem;font-weight:900;color:#f97316;letter-spacing:-.01em;">
                            {{ $req->unique_id ?: ('REQ-'.str_pad($req->id,5,'0',STR_PAD_LEFT)) }}
                        </span>
                    </td>

                    {{-- REQUESTER & DEPT --}}
                    <td style="padding:.9rem 1.25rem;">
                        <div style="font-weight:800;color:var(--text-main);font-size:0.85rem;white-space:nowrap;">
                            {{ $req->requester_name }}{{ $req->rank_or_title ? ' ('.$req->rank_or_title.')' : '' }}
                        </div>
                        <div style="font-size:0.75rem;color:#4f46e5;margin-top:2px;font-weight:600;">
                            {{ $req->department }}
                        </div>
                    </td>

                    {{-- ITEMS REQUESTED --}}
                    <td style="padding:.9rem 1.25rem;">
                        <div style="display:flex;flex-wrap:wrap;gap:4px;">
                            @foreach($req->items as $item)
                                @php
                                    $approvedVal = $item->quantity_approved !== null ? (float)$item->quantity_approved : null;
                                    $altApproved = $item->alternative_quantity_approved !== null ? (float)$item->alternative_quantity_approved : 0;
                                @endphp
                                <span class="table-item-pill" title="{{ $item->description }}">
                                    {{ Str::limit($item->description, 20) }}
                                    <span class="table-item-qty">×{{ number_format($item->quantity_requested,0) }}</span>
                                    @if($approvedVal !== null)
                                        <span class="table-item-approved">(✓{{ number_format($approvedVal+$altApproved,0) }})</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </td>

                    {{-- PURPOSE --}}
                    <td style="padding:.9rem 1.25rem;max-width:160px;">
                        <div style="font-size:0.8rem;color:var(--text-muted);font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:150px;" title="{{ $purposeText }}">
                            {{ $purposeText }}
                        </div>
                    </td>

                    {{-- PRIORITY --}}
                    <td style="padding:.9rem 1.25rem;white-space:nowrap;">
                        <span class="pill" style="background:{{ $pb['bg'] }};color:{{ $pb['color'] }};font-size:0.65rem;">{{ $pb['label'] }}</span>
                    </td>

                    {{-- STATUS --}}
                    <td style="padding:.9rem 1.25rem;">
                        <span class="pill" style="background:{{ $sb['bg'] }};color:{{ $sb['color'] }};font-size:0.65rem;white-space:nowrap;">● {{ $sb['label'] }}</span>

                        @if(auth()->user()->role === 'Head of Stores')
                            @php
                                $pipeline = $req->tracking_pipeline;
                                $step1 = $pipeline['hod'];
                                $step2 = $pipeline['stores_hod'];
                                $step3 = $pipeline['dg'];
                                $step4 = $pipeline['head_of_stores'];
                            @endphp
                            <div class="mini-tracker" style="max-width: 190px; gap: 2px; margin-top: 8px;">
                                <div class="mini-step {{ $step1['status'] }}" title="{{ $step1['label'] }} (Reviewer: {{ $step1['user'] }})">
                                    <div class="mini-dot"><i data-lucide="{{ $step1['icon'] }}" style="width:10px;height:10px;"></i></div>
                                    <span class="mini-label">HOD</span>
                                </div>
                                <div class="mini-line {{ in_array($step2['status'], ['completed','active','declined']) && $step2['status'] !== 'bypassed' ? 'completed' : '' }}"></div>
                                <div class="mini-step {{ $step2['status'] }}" title="{{ $step2['label'] }} (Reviewer: {{ $step2['user'] }})">
                                    <div class="mini-dot"><i data-lucide="{{ $step2['icon'] }}" style="width:10px;height:10px;"></i></div>
                                    <span class="mini-label">Stores HOD</span>
                                </div>
                                <div class="mini-line {{ in_array($step3['status'], ['completed','active','declined']) && $step3['status'] !== 'bypassed' ? 'completed' : '' }}"></div>
                                <div class="mini-step {{ $step3['status'] }}" title="{{ $step3['label'] }} (Reviewer: {{ $step3['user'] }})">
                                    <div class="mini-dot"><i data-lucide="{{ $step3['icon'] }}" style="width:10px;height:10px;"></i></div>
                                    <span class="mini-label">DG</span>
                                </div>
                                <div class="mini-line {{ in_array($step4['status'], ['completed','active','declined']) && $step4['status'] !== 'bypassed' ? 'completed' : '' }}"></div>
                                <div class="mini-step {{ $step4['status'] }}" title="{{ $step4['label'] }} (Reviewer: {{ $step4['user'] }})">
                                    <div class="mini-dot"><i data-lucide="{{ $step4['icon'] }}" style="width:10px;height:10px;"></i></div>
                                    <span class="mini-label">Stores Final</span>
                                </div>
                            </div>
                            @if($req->status === 'pending')
                                <div style="font-size:0.7rem;color:var(--text-muted);margin-top:6px;font-weight:600;">
                                    Next: <span style="color:var(--text-main);font-weight:800;">{{ $req->approver_name }}</span>
                                </div>
                            @endif
                        @elseif($req->status === 'pending')
                            <div style="font-size:0.7rem;color:var(--text-muted);margin-top:4px;font-weight:600;">
                                Next: <span style="color:var(--text-main);font-weight:800;">{{ $req->approver_name }}</span>
                            </div>
                        @endif
                    </td>

                    {{-- USAGE --}}
                    <td style="padding:.9rem 1.25rem;white-space:nowrap;">
                        <span class="pill" style="background:{{ $utb['bg'] }};color:{{ $utb['color'] }};font-size:0.65rem;">{{ $utb['label'] }}</span>
                    </td>

                    {{-- SUBMITTED --}}
                    <td style="padding:.9rem 1.25rem;white-space:nowrap;">
                        <div style="font-size:0.78rem;color:var(--text-muted);font-weight:600;">{{ $req->created_at->format('d/m/Y') }}</div>
                        <div style="font-size:0.7rem;color:var(--text-muted);">{{ $req->created_at->format('H:i') }}</div>
                    </td>

                    {{-- ACTIONS --}}
                    <td style="padding:.9rem 1.25rem;text-align:center;white-space:nowrap;">
                        <div style="display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;">
                            {{-- Collection action --}}
                            @if(in_array($req->status, ['approved', 'partially_approved']))
                                @if($req->collected_at)
                                    <span style="font-size:.75rem;color:#10b981;font-weight:800;display:inline-flex;align-items:center;gap:4px;">
                                        <i data-lucide="check-circle" style="width:13px;"></i> Collected
                                    </span>
                                @elseif(auth()->user()->can_operate_logistics)
                                    <button onclick="confirmCollection({{ $req->id }}, this)"
                                        style="background:rgba(16,185,129,.1);color:#10b981;border:1.5px solid rgba(16,185,129,.25);padding:.4rem .85rem;border-radius:10px;font-weight:800;font-size:.72rem;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:.15s;white-space:nowrap;"
                                        onmouseover="this.style.background='#10b981';this.style.color='white'"
                                        onmouseout="this.style.background='rgba(16,185,129,.1)';this.style.color='#10b981'">
                                        <i data-lucide="package-check" style="width:13px;"></i> Collect
                                    </button>
                                @else
                                    <span style="font-size:.72rem;color:#ef4444;font-style:italic;font-weight:700;display:inline-flex;align-items:center;gap:3px;">
                                        <i data-lucide="lock" style="width:12px;height:12px;"></i> No Access
                                    </span>
                                @endif
                            @endif
                            {{-- Review button --}}
                            <button onclick="openRequisitionModal({{ $req->id }})"
                                style="background:rgba(99,102,241,0.08);color:#4f46e5;border:1.5px solid rgba(99,102,241,0.2);padding:.4rem .85rem;border-radius:10px;font-weight:800;font-size:.72rem;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:all .2s;white-space:nowrap;"
                                onmouseover="this.style.background='#4f46e5';this.style.color='white';this.style.borderColor='#4f46e5';"
                                onmouseout="this.style.background='rgba(99,102,241,0.08)';this.style.color='#4f46e5';this.style.borderColor='rgba(99,102,241,0.2)';">
                                <i data-lucide="clipboard-check" style="width:13px;height:13px;"></i> Review
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="padding:3rem;text-align:center;color:var(--text-muted);">
                        <i data-lucide="inbox" style="width:32px;margin-bottom:.75rem;opacity:.3;"></i>
                        <p style="font-weight:700;color:var(--text-main);">No requisitions found</p>
                        <p style="font-size:.85rem;">Department requests will appear here.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($requisitions->hasPages())
        <div style="padding: 1.5rem; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; background: var(--bg-card); border-radius: 0 0 16px 16px;">
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

@push('modals')
{{-- Review Modal --}}
<div class="modal-overlay" id="reqModal" onclick="if(event.target===this)closeModal()">
    <div class="modal-box">
        <div style="padding:1.5rem 2rem;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:1rem;">
                <div style="width:44px;height:44px;background:rgba(79,70,229,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="clipboard-list" style="width:20px;color:#4f46e5;"></i>
                </div>
                <div>
                    <h2 style="margin:0;font-size:1.1rem;font-weight:900;color:var(--text-main);">Requisition Summary</h2>
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
@endpush

{{-- ════════════════════════════════════════════════════════
     NEW REQUISITION SLIDE-OVER PANEL
     ════════════════════════════════════════════════════════ --}}
@push('modals')
<div id="newReqOverlay" onclick="if(event.target===this)closeNewReqPanel()" style="position:fixed;inset:0;width:100vw;height:100vh;background:rgba(15,23,42,.5);backdrop-filter:blur(6px);z-index:99998;display:none;align-items:flex-start;justify-content:flex-end;">
    <div id="newReqPanel" style="background:var(--bg-card);width:100%;max-width:1100px;height:100vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:-30px 0 80px rgba(15,23,42,.18);transform:translateX(100%);transition:transform .4s cubic-bezier(.16,1,.3,1);">

        {{-- Panel Header --}}
        <div style="padding:1.5rem 2rem;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;background:linear-gradient(135deg,rgba(79,70,229,.04) 0%,transparent 100%);">
            <div style="display:flex;align-items:center;gap:1rem;">
                <div style="width:44px;height:44px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:12px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(79,70,229,.3);">
                    <i data-lucide="clipboard-plus" style="width:20px;color:white;"></i>
                </div>
                <div>
                    <h2 style="margin:0;font-size:1.1rem;font-weight:900;color:var(--text-main);">New Requisition</h2>
                    <p style="margin:0;font-size:.78rem;color:var(--text-muted);font-weight:500;">Fill in the details to place a store request</p>
                </div>
            </div>
            <button onclick="closeNewReqPanel()" style="background:var(--bg-main);border:none;width:36px;height:36px;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s;" onmouseover="this.style.background='rgba(239,68,68,.1)'" onmouseout="this.style.background='var(--bg-main)'">
                <i data-lucide="x" style="width:18px;color:var(--text-muted);"></i>
            </button>
        </div>

        {{-- Step Progress Bar --}}
        <div style="padding:1rem 2rem;border-bottom:1px solid var(--border-color);flex-shrink:0;">
            <div style="display:flex;align-items:center;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div class="nr-step-bubble active" id="nr-bubble-1">1</div>
                    <span class="nr-step-label active" id="nr-label-1">Details</span>
                </div>
                <div style="flex:1;height:2px;background:var(--border-color);margin:0 .75rem;border-radius:2px;position:relative;">
                    <div id="nr-progress-1" style="position:absolute;left:0;top:0;height:100%;width:0;background:linear-gradient(90deg,#4f46e5,#7c3aed);border-radius:2px;transition:width .4s;"></div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <div class="nr-step-bubble" id="nr-bubble-2">2</div>
                    <span class="nr-step-label" id="nr-label-2">Items</span>
                </div>
                <div style="flex:1;height:2px;background:var(--border-color);margin:0 .75rem;border-radius:2px;position:relative;">
                    <div id="nr-progress-2" style="position:absolute;left:0;top:0;height:100%;width:0;background:linear-gradient(90deg,#4f46e5,#7c3aed);border-radius:2px;transition:width .4s;"></div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <div class="nr-step-bubble" id="nr-bubble-3">3</div>
                    <span class="nr-step-label" id="nr-label-3">Review</span>
                </div>
            </div>
        </div>

        {{-- Panel Body (scrollable) --}}
        <div style="flex:1;overflow-y:auto;padding:1.75rem 2rem;" class="modal-body">

            {{-- STEP 1: REQUESTER DETAILS --}}
            <div id="nr-step-1">
                <div style="margin-bottom:1.5rem;">
                    <div style="font-size:.7rem;font-weight:800;color:#4f46e5;text-transform:uppercase;letter-spacing:.1em;margin-bottom:1rem;">Requester Information</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <label style="font-size:.78rem;font-weight:800;color:var(--text-muted);">Full Name <span style="color:#ef4444;">*</span></label>
                            <input type="text" id="nr-requester-name" value="{{ auth()->user()->name }}" readonly placeholder="Full name of requester" style="padding:.75rem 1rem;border:1.5px solid var(--border-color);border-radius:10px;background:var(--bg-card);color:var(--text-main);font-family:inherit;font-size:.88rem;font-weight:600;outline:none;transition:.2s;cursor:not-allowed;opacity:0.85;">
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <label style="font-size:.78rem;font-weight:800;color:var(--text-muted);">Department <span style="color:#ef4444;">*</span></label>
                            <input type="text" id="nr-department" value="{{ auth()->user()->department ?? '' }}" readonly placeholder="e.g. Stores, Logistics" style="padding:.75rem 1rem;border:1.5px solid var(--border-color);border-radius:10px;background:var(--bg-card);color:var(--text-main);font-family:inherit;font-size:.88rem;font-weight:600;outline:none;transition:.2s;cursor:not-allowed;opacity:0.85;">
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <label style="font-size:.78rem;font-weight:800;color:var(--text-muted);">Rank</label>
                            <input type="text" id="nr-rank" value="{{ auth()->user()->rank_or_title ?? '' }}" placeholder="e.g. Sergeant, Officer" style="padding:.75rem 1rem;border:1.5px solid var(--border-color);border-radius:10px;background:var(--bg-main);color:var(--text-main);font-family:inherit;font-size:.88rem;font-weight:600;outline:none;transition:.2s;" onfocus="this.style.borderColor='#4f46e5';this.style.boxShadow='0 0 0 4px rgba(79,70,229,.12)'" onblur="this.style.borderColor='var(--border-color)';this.style.boxShadow=''">
                        </div>
                        <input type="hidden" id="nr-priority" value="normal">
                    </div>
                </div>
                <div style="margin-bottom:1.5rem;">
                    <div style="font-size:.7rem;font-weight:800;color:#4f46e5;text-transform:uppercase;letter-spacing:.1em;margin-bottom:1rem;">Usage Type <span style="color:#ef4444;">*</span></div>
                    <div style="display:flex;gap:1rem;">
                        <label style="flex:1;display:flex;align-items:center;gap:.75rem;padding:1rem;border:1.5px solid #4f46e5;background:rgba(79,70,229,.04);border-radius:12px;cursor:pointer;transition:.2s;" id="nr-usage-perm-label" onclick="selectNrUsage('permanent')">
                            <input type="radio" name="nr_usage" id="nr-usage-permanent" value="permanent" checked style="accent-color:#4f46e5;width:16px;height:16px;">
                            <div>
                                <div style="font-weight:800;font-size:.88rem;color:var(--text-main);">Permanent</div>
                                <div style="font-size:.72rem;color:var(--text-muted);">Item will not be returned</div>
                            </div>
                        </label>
                        <label style="flex:1;display:flex;align-items:center;gap:.75rem;padding:1rem;border:1.5px solid var(--border-color);border-radius:12px;cursor:pointer;transition:.2s;" id="nr-usage-temp-label" onclick="selectNrUsage('temporary')">
                            <input type="radio" name="nr_usage" id="nr-usage-temporary" value="temporary" style="accent-color:#f59e0b;width:16px;height:16px;">
                            <div>
                                <div style="font-weight:800;font-size:.88rem;color:var(--text-main);">Temporary</div>
                                <div style="font-size:.72rem;color:var(--text-muted);">Item will be returned after use</div>
                            </div>
                        </label>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:6px;">
                    <label style="font-size:.78rem;font-weight:800;color:var(--text-muted);">Purpose / Justification <span style="color:#ef4444;">*</span></label>
                    <textarea id="nr-purpose" rows="3" placeholder="State the reason for this requisition..." style="padding:.75rem 1rem;border:1.5px solid var(--border-color);border-radius:10px;background:var(--bg-main);color:var(--text-main);font-family:inherit;font-size:.88rem;font-weight:600;outline:none;resize:vertical;transition:.2s;" onfocus="this.style.borderColor='#4f46e5';this.style.boxShadow='0 0 0 4px rgba(79,70,229,.12)'" onblur="this.style.borderColor='var(--border-color)';this.style.boxShadow=''"></textarea>
                </div>
            </div>

            {{-- STEP 2: ITEM SELECTION --}}
            <div id="nr-step-2" style="display:none;">
                <div style="font-size:.7rem;font-weight:800;color:#4f46e5;text-transform:uppercase;letter-spacing:.1em;margin-bottom:.75rem;">Select Items to Request</div>

                {{-- Layout: Catalog (left) | Cart (right) --}}
                <div style="display:grid;grid-template-columns:1fr 420px;gap:1.25rem;height:calc(100vh - 320px);min-height:420px;">

                    {{-- LEFT: Item Catalog --}}
                    <div style="display:flex;flex-direction:column;gap:.75rem;overflow:hidden;">
                        {{-- Search + Category Filter --}}
                        <div style="display:flex;gap:.5rem;align-items:center;">
                            <div style="position:relative;flex:1;">
                                <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:14px;color:var(--text-muted);pointer-events:none;"></i>
                                <input type="text" id="nr-item-search" placeholder="Search items..." autocomplete="off"
                                    style="width:100%;box-sizing:border-box;padding:.6rem 1rem .6rem 2.2rem;border:1.5px solid var(--border-color);border-radius:10px;background:var(--bg-main);color:var(--text-main);font-family:inherit;font-size:.83rem;font-weight:600;outline:none;transition:.2s;"
                                    onfocus="this.style.borderColor='#4f46e5'"
                                    onblur="this.style.borderColor='var(--border-color)'"
                                    oninput="nrFilterCatalog(this.value)">
                            </div>
                        </div>

                        {{-- Category Tabs --}}
                        <div id="nr-cat-tabs" style="display:flex;gap:.4rem;flex-wrap:wrap;"></div>

                        {{-- Items Grid (scrollable) --}}
                        <div id="nr-catalog-grid" style="flex:1;overflow-y:auto;display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;align-content:start;padding-right:6px;"></div>
                        <div id="nr-catalog-empty" style="display:none;text-align:center;padding:2rem;color:var(--text-muted);">
                            <i data-lucide="search-x" style="width:24px;opacity:.3;display:block;margin:0 auto .5rem;"></i>
                            <p style="margin:0;font-size:.82rem;font-weight:600;">No items match your search.</p>
                        </div>
                    </div>

                    {{-- RIGHT: Selected Cart --}}
                    <div style="display:flex;flex-direction:column;gap:.5rem;background:var(--bg-main);border:1.5px solid var(--border-color);border-radius:14px;padding:.85rem;overflow:hidden;">
                        <div style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;display:flex;align-items:center;justify-content:space-between;">
                            <span>Selected Items</span>
                            <span id="nr-cart-count" style="background:#4f46e5;color:white;font-size:.65rem;font-weight:900;padding:2px 7px;border-radius:8px;">0</span>
                        </div>
                        <div id="nr-items-list" style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:.5rem;"></div>
                        <div id="nr-items-empty" style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;color:var(--text-muted);padding:1rem;">
                            <i data-lucide="shopping-cart" style="width:22px;opacity:.25;margin-bottom:.5rem;"></i>
                            <p style="margin:0;font-size:.75rem;font-weight:600;line-height:1.5;">Click any item<br>on the left to add it</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- STEP 3: REVIEW --}}
            <div id="nr-step-3" style="display:none;">
                <div style="font-size:.7rem;font-weight:800;color:#4f46e5;text-transform:uppercase;letter-spacing:.1em;margin-bottom:1rem;">Review Your Requisition</div>
                <div id="nr-review-content"></div>
            </div>

        </div>

        {{-- Panel Footer --}}
        <div style="padding:1.25rem 2rem;border-top:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-shrink:0;background:var(--bg-card);">
            <button id="nr-btn-back" onclick="nrGoBack()" style="display:none;padding:.75rem 1.5rem;border:1.5px solid var(--border-color);border-radius:12px;background:var(--bg-main);color:var(--text-muted);font-weight:800;font-size:.85rem;cursor:pointer;align-items:center;gap:8px;transition:.2s;">
                <i data-lucide="arrow-left" style="width:15px;"></i> Back
            </button>
            <div style="flex:1;"></div>
            <button id="nr-btn-cancel" onclick="closeNewReqPanel()" style="padding:.75rem 1.5rem;border:1.5px solid var(--border-color);border-radius:12px;background:var(--bg-main);color:var(--text-muted);font-weight:800;font-size:.85rem;cursor:pointer;transition:.2s;">
                Cancel
            </button>
            <button id="nr-btn-next" onclick="nrGoNext()" style="padding:.75rem 1.75rem;border:none;border-radius:12px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:white;font-weight:800;font-size:.85rem;cursor:pointer;display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(79,70,229,.25);transition:.2s;">
                Next <i data-lucide="arrow-right" style="width:15px;"></i>
            </button>
            <button id="nr-btn-submit" onclick="submitNewReq()" style="display:none;padding:.75rem 1.75rem;border:none;border-radius:12px;background:linear-gradient(135deg,#10b981,#059669);color:white;font-weight:800;font-size:.85rem;cursor:pointer;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(16,185,129,.25);transition:.2s;">
                <i data-lucide="send" style="width:15px;"></i> Submit Requisition
            </button>
        </div>
    </div>
</div>
@endpush

<style>
.nr-step-bubble {
    width:30px;height:30px;border-radius:50%;background:var(--bg-main);border:2px solid var(--border-color);
    display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.8rem;color:var(--text-muted);
    transition:all .3s cubic-bezier(.16,1,.3,1);
}
.nr-step-bubble.active {
    background:linear-gradient(135deg,#4f46e5,#7c3aed);border-color:#4f46e5;color:white;
    box-shadow:0 4px 10px rgba(79,70,229,.3);
}
.nr-step-bubble.done {
    background:linear-gradient(135deg,#10b981,#059669);border-color:#10b981;color:white;
    box-shadow:0 4px 10px rgba(16,185,129,.25);
}
.nr-step-label { font-size:.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;transition:color .3s; }
.nr-step-label.active { color:#4f46e5; }
.nr-step-label.done { color:#10b981; }
</style>

<script>
    const loggedInUserRole = "{{ auth()->user()->role }}";
    const canOperateLogistics = {{ auth()->user()->can_operate_logistics ? 'true' : 'false' }};
    let currentReqId = null;
    let currentReqData = null;

    async function openRequisitionModal(id) {
        currentReqId = id;
        document.getElementById('reqModal').classList.add('open');
        document.getElementById('modalBody').innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted);"><div style="width:24px;height:24px;border:2px solid rgba(0,0,0,.1);border-top-color:var(--primary);border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 10px;"></div>Loading details...</div>';
        document.getElementById('modalFooter').innerHTML = '';
        document.getElementById('modalSubtitle').textContent = 'Loading...';

        // Call the Admin Show endpoint (fully compatible for detailing)
        const res = await fetch(`{{ url('/admin/requisitions') }}/${id}/show`);
        const data = await res.json();
        currentReqData = data;

        // Apply priority border accents
        const modalBox = document.querySelector('.modal-box');
        modalBox.className = 'modal-box'; // reset
        modalBox.classList.add(`${data.priority}-priority`);

        document.getElementById('modalSubtitle').textContent = `Requisition Ref: ${data.unique_id || ('REQ-' + String(data.id).padStart(5, '0'))}`;

        // Profile Grid
        const avatarLetter = data.requester_name ? data.requester_name.charAt(0).toUpperCase() : 'R';
        const totalItemsCount = data.items.length;
        const totalQtyRequested = data.items.reduce((sum, item) => sum + parseFloat(item.quantity_requested || 0), 0);

        let purposeText = data.purpose || '';
        let returnDateBannerHtml = '';
        const dateMatch = purposeText.match(/\[Expected Return Date:\s*([^\]]+)\]/i);
        if (dateMatch) {
            const rawDate = dateMatch[1].trim();
            let formattedDate = rawDate;
            try {
                const dateObj = new Date(rawDate);
                if (!isNaN(dateObj.getTime())) {
                    formattedDate = dateObj.toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' });
                }
            } catch(e) {}
            returnDateBannerHtml = `
            <div style="background:rgba(245, 158, 11, 0.06); border:1px solid rgba(245, 158, 11, 0.25); border-radius:12px; padding:0.85rem 1.15rem; display:flex; align-items:center; gap:10px; color:#d97706; font-weight:800; font-size:0.88rem; margin-top:0.5rem; margin-bottom:0.25rem; box-shadow:0 2px 8px rgba(245, 158, 11, 0.03); width:100%;">
                <i data-lucide="calendar-clock" style="width:16px; height:16px; color:#d97706; flex-shrink:0;"></i>
                <span>Expected Return Date: <strong style="color:#b45309; font-size:0.95rem; font-weight:950; text-decoration: underline;">${formattedDate}</strong></span>
            </div>`;
            purposeText = purposeText.replace(/\[Expected Return Date:\s*[^\]]+\]/i, '').trim();
        }

        const profileGridHtml = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.75rem;">
        <div class="profile-card">
            <div class="profile-avatar">${avatarLetter}</div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:2px;letter-spacing:0.04em;">Requesting Officer</div>
                <div style="font-size:1.05rem;font-weight:900;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${data.requester_name}">${data.requester_name}</div>
                <div style="font-size:.78rem;color:var(--text-muted);font-weight:600;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <i data-lucide="award" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:3px;"></i>${data.rank_or_title || 'No Rank/Title Specified'}
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
                <span style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.04em;">Requisition Intention & Purpose</span>
                <div class="stat-pill-group" style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                    <span class="stat-pill" style="background:${data.usage_type_badge.bg}; color:${data.usage_type_badge.color}; border-color:rgba(0,0,0,0.05); font-weight:800;"><i data-lucide="${data.usage_type === 'temporary' ? 'calendar' : 'package-check'}" style="width:12px;"></i> ${data.usage_type_badge.label}</span>
                    <span class="stat-pill"><i data-lucide="layers" style="width:12px;"></i> ${totalItemsCount} ${totalItemsCount === 1 ? 'Item Type' : 'Item Types'}</span>
                    <span class="stat-pill"><i data-lucide="hash" style="width:12px;"></i> Total Qty: ${totalQtyRequested.toLocaleString()}</span>
                </div>
            </div>
            ${returnDateBannerHtml}
            <div class="purpose-quote">
                ${purposeText}
            </div>
        </div>
    </div>
    `;

        // Item rows in read-only format
        const rows = data.items.map(item => {
            const requested = parseFloat(item.quantity_requested) || 0;
            const approved = item.quantity_approved !== null ? parseFloat(item.quantity_approved) : 0;
            const altApproved = item.alternative_quantity_approved !== null ? parseFloat(item.alternative_quantity_approved) : 0;
            const totalApproved = approved + altApproved;
            const pct = requested > 0 ? Math.min(Math.round((totalApproved / requested) * 100), 100) : 0;

            let fulfillBadgeClass = 'fulfill-ratio-badge';
            let fulfillLabel = `${pct}% Fulfill`;
            if (totalApproved === 0) {
                fulfillBadgeClass += ' declined';
                fulfillLabel = 'Declined';
            } else if (totalApproved < requested) {
                fulfillBadgeClass += ' reduced';
                fulfillLabel = `${pct}% Reduced`;
            }

            const stockInfo = item.stock_sufficient ?
                `<span style="color:#10b981;font-size:.7rem;font-weight:700;">✔ Sufficient</span>` :
                `<span style="color:#ef4444;font-size:.7rem;font-weight:700;">⚠ Short Stock</span>`;

            return `
        <div class="item-decision-card ${totalApproved === 0 ? 'declined-row' : 'approved-row'}">
            <div class="item-card-header">
                <div class="item-card-header-left">
                    <div>
                        ${item.alternative_description ? `
                            <div style="font-size:.95rem;font-weight:800;color:var(--text-main); display:flex; align-items:center; gap:6px;">
                                <span>${item.description}</span>
                                <span style="font-size:0.75rem; font-weight:800; color:var(--success-color);">(Approved: ${approved.toLocaleString()} ${item.unit})</span>
                            </div>
                            <div style="font-size:.92rem;font-weight:800;color:var(--store-orange); display:flex; align-items:center; gap:6px; margin-top:4px;">
                                <i data-lucide="shuffle" style="width:14px;height:14px;display:inline-block;vertical-align:middle;margin-right:2px;"></i>Alternative: ${item.alternative_description}
                                <span style="font-size:0.75rem; font-weight:800;">(Approved: ${altApproved.toLocaleString()} ${item.unit})</span>
                            </div>
                        ` : `
                            <div style="font-size:.95rem;font-weight:800;color:var(--text-main);">${item.description}</div>
                        `}
                        <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;margin-top:4px;">
                            Unit: ${item.unit} · Stock: ${parseFloat(item.current_stock).toLocaleString()} (${stockInfo})
                        </div>
                    </div>
                </div>
                <div class="item-card-header-right">
                    <span class="${fulfillBadgeClass}">${fulfillLabel}</span>
                </div>
            </div>

            <div class="item-card-panel" style="gap:1.5rem;">
                <div style="flex:1; min-width:80px;">
                    <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;">Requested</div>
                    <div style="font-size:1.1rem;font-weight:800;color:var(--text-main);margin-top:2px;">${requested.toLocaleString()}</div>
                </div>

                <div style="flex:1; min-width:80px;">
                    <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;">Total Approved</div>
                    <div style="font-size:1.15rem;font-weight:900;color:${totalApproved === 0 ? '#ef4444' : '#10b981'};margin-top:2px;">${totalApproved.toLocaleString()}</div>
                </div>

                <div style="flex:2; min-width:180px;">
                    <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;margin-bottom:6px;">Fulfillment Progress</div>
                    <div class="fulfill-progress-container" style="margin-top:0;">
                        <div class="fulfill-progress-bar" style="width: ${pct}%; background:${approved === 0 ? '#ef4444' : (approved < requested ? '#f59e0b' : 'linear-gradient(90deg, #4f46e5 0%, #10b981 100%)')}"></div>
                    </div>
                </div>
            </div>

            ${item.remarks ? `
            <div style="background:rgba(0,0,0,0.015); border:1.5px dashed var(--border-color); border-radius:10px; padding:0.75rem 1rem; margin-top:0.25rem;">
                <span style="font-size:0.65rem; font-weight:900; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:4px; letter-spacing:0.04em;">Decision Remarks</span>
                <p style="margin:0; font-size:0.8rem; color:var(--text-main); font-style:italic; line-height:1.4;">"${item.remarks}"</p>
            </div>` : ''}
        </div>`;
        }).join('');

        const itemRowsHtml = `
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:16px;overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.01);">
        ${rows}
    </div>`;

        let collectorInfoHtml = '';
        if (['approved', 'partially_approved'].includes(data.status)) {
            if (data.collected_at) {
                collectorInfoHtml = `
                <div style="background:rgba(16,185,129,0.03); border:1.5px dashed rgba(16,185,129,0.25); border-radius:16px; padding:1.25rem; margin-top:1.25rem; display:flex; flex-direction:column; gap:1rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:1px dashed rgba(16,185,129,0.15); padding-bottom:8px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="width:34px; height:34px; background:rgba(16,185,129,0.08); color:#10b981; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                                <i data-lucide="package-check" style="width:16px;"></i>
                            </div>
                            <div>
                                <h4 style="margin:0; font-size:0.85rem; font-weight:800; color:var(--text-main); text-transform:uppercase; letter-spacing:0.04em;">Physical Collection Log</h4>
                                <p style="margin:0; font-size:0.75rem; color:var(--text-muted);">Items have been successfully issued and collected</p>
                            </div>
                        </div>
                        <span class="pill" style="background:rgba(16,185,129,0.1); color:#10b981; font-weight:800; font-size:0.7rem; padding:4px 10px;">COLLECTED</span>
                    </div>
                    
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.75rem 1rem;">
                            <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Collector Name</div>
                            <div style="font-size:0.9rem; font-weight:900; color:var(--text-main);">${data.collector_name || 'N/A'}</div>
                        </div>
                        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.75rem 1rem;">
                            <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Collector Staff ID</div>
                            <div style="font-size:0.9rem; font-weight:900; color:var(--text-main);">${data.collector_staff_id || 'N/A'}</div>
                        </div>
                        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.75rem 1rem; grid-column: span 2;">
                            <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Collector Contact</div>
                            <div style="font-size:0.9rem; font-weight:900; color:var(--text-main);">${data.collector_contact || 'N/A'}</div>
                        </div>
                        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.75rem 1rem; grid-column: span 2;">
                            <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Collection Destination / Location</div>
                            <div style="font-size:0.9rem; font-weight:900; color:var(--text-main);">${data.collector_location || 'N/A'}</div>
                        </div>
                        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.75rem 1rem;">
                            <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Confirmed By (Store Staff)</div>
                            <div style="font-size:0.9rem; font-weight:900; color:var(--text-main);">${data.collected_by_name || 'N/A'}</div>
                        </div>
                        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.75rem 1rem;">
                            <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Collection Date & Time</div>
                            <div style="font-size:0.9rem; font-weight:900; color:var(--text-main);">${data.collected_at || 'N/A'}</div>
                        </div>
                    </div>
                </div>`;
            } else {
                collectorInfoHtml = `
                <div style="background:var(--bg-main); border:1.5px solid var(--border-color); border-radius:16px; padding:1.25rem; margin-top:1.25rem; display:flex; flex-direction:column; gap:1rem;">
                    <div style="display:flex; align-items:center; gap:8px; border-bottom:1px solid var(--border-color); padding-bottom:8px;">
                        <div style="width:34px; height:34px; background:rgba(16,185,129,0.08); color:#10b981; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                            <i data-lucide="package-check" style="width:16px;"></i>
                        </div>
                        <div>
                            <h4 style="margin:0; font-size:0.85rem; font-weight:800; color:var(--text-main); text-transform:uppercase; letter-spacing:0.04em;">Collector Information</h4>
                            <p style="margin:0; font-size:0.75rem; color:var(--text-muted);">Details of the person physically collecting the physical item(s)</p>
                        </div>
                    </div>
                    
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div style="position:relative; display:flex; align-items:center;">
                            <i data-lucide="user" style="position:absolute; left:12px; color:var(--text-muted); width:16px; height:16px; pointer-events:none;"></i>
                            <input type="text" id="modalCollectorName" oninput="validateModalCollectorInputs()" placeholder="Collector Full Name *" style="width:100%; padding:10px 12px 10px 36px; height:44px; border-radius:12px; font-weight:700; font-family:inherit; font-size:0.85rem; border:1.5px solid var(--border-color); background:var(--bg-card); color:var(--text-main); outline:none; transition:all 0.25s ease;" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 4px rgba(16, 185, 129, 0.15)';" onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none';">
                        </div>
                        <div style="position:relative; display:flex; align-items:center;">
                            <i data-lucide="user" style="position:absolute; left:12px; color:var(--text-muted); width:16px; height:16px; pointer-events:none;"></i>
                            <input type="text" id="modalCollectorStaffId" oninput="validateModalCollectorInputs()" placeholder="Collector Staff ID *" style="width:100%; padding:10px 12px 10px 36px; height:44px; border-radius:12px; font-weight:700; font-family:inherit; font-size:0.85rem; border:1.5px solid var(--border-color); background:var(--bg-card); color:var(--text-main); outline:none; transition:all 0.25s ease;" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 4px rgba(16, 185, 129, 0.15)';" onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none';">
                        </div>
                        <div style="position:relative; display:flex; align-items:center; grid-column: span 2;">
                            <i data-lucide="phone" style="position:absolute; left:12px; color:var(--text-muted); width:16px; height:16px; pointer-events:none;"></i>
                            <input type="text" id="modalCollectorContact" oninput="validateModalCollectorInputs()" placeholder="Collector Contact Number *" style="width:100%; padding:10px 12px 10px 36px; height:44px; border-radius:12px; font-weight:700; font-family:inherit; font-size:0.85rem; border:1.5px solid var(--border-color); background:var(--bg-card); color:var(--text-main); outline:none; transition:all 0.25s ease;" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 4px rgba(16, 185, 129, 0.15)';" onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none';">
                        </div>
                        <div style="position:relative; display:flex; align-items:center; grid-column: span 2;">
                            <i data-lucide="map-pin" style="position:absolute; left:12px; color:var(--text-muted); width:16px; height:16px; pointer-events:none;"></i>
                            <input type="text" id="modalCollectorLocation" oninput="validateModalCollectorInputs()" placeholder="Collection Destination / Location *" style="width:100%; padding:10px 12px 10px 36px; height:44px; border-radius:12px; font-weight:700; font-family:inherit; font-size:0.85rem; border:1.5px solid var(--border-color); background:var(--bg-card); color:var(--text-main); outline:none; transition:all 0.25s ease;" onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 4px rgba(16, 185, 129, 0.15)';" onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none';">
                        </div>
                    </div>
                </div>`;
            }
        }

        document.getElementById('modalBody').innerHTML = `
    ${profileGridHtml}

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; margin-top:1.5rem;">
        <h3 style="margin:0; font-size:0.95rem; font-weight:900; color:var(--text-main); display:flex; align-items:center; gap:6px;">
            <i data-lucide="list-checks" style="width:16px; color:var(--primary);"></i> Requested Items Summary
        </h3>
    </div>

    ${itemRowsHtml}

    ${data.admin_notes ? `
        <div style="background:rgba(79,70,229,.03);border:1px solid rgba(79,70,229,.15);border-radius:16px;padding:1.25rem; margin-top: 1.25rem;">
            <div style="font-size:.68rem;font-weight:900;color:var(--primary);text-transform:uppercase;letter-spacing:0.05em;display:flex;align-items:center;gap:4px;margin-bottom:4px;"><i data-lucide="message-square" style="width:14px;"></i> Store Officer Notes</div>
            <p style="margin:0;font-size:.9rem;color:var(--text-main);line-height:1.6;font-style:italic;">"${data.admin_notes}"</p>
        </div>
    ` : ''}

    ${data.status === 'declined' && data.decline_reason ? `
        <div style="background:rgba(239,68,68,0.04); border:1px solid rgba(239,68,68,0.2); border-radius:16px; padding:1.25rem; margin-top:0.75rem;">
            <div style="font-size:.68rem;font-weight:900;color:#dc2626;text-transform:uppercase;letter-spacing:0.05em;display:flex;align-items:center;gap:4px;margin-bottom:6px;">
                <i data-lucide="alert-circle" style="width:14px;"></i> Reason for Decline
            </div>
            <p style="margin:0;font-size:.9rem;color:#7f1d1d;line-height:1.6;">${data.decline_reason}</p>
        </div>
    ` : ''}

    <div style="background:var(--bg-main); border:1px solid var(--border-color); border-radius:16px; padding:1.15rem; margin-top:1.25rem; display:flex; justify-content:space-between; align-items:center;">
        <div style="display:flex; align-items:center; gap:8px;">
            <div style="width:34px; height:34px; background:rgba(79,70,229,0.08); color:var(--primary); border-radius:10px; display:flex; align-items:center; justify-content:center;">
                <i data-lucide="user-check" style="width:16px;"></i>
            </div>
            <div>
                <div style="font-size:.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em;">Processor Authority</div>
                <div style="font-size:.85rem; font-weight:900; color:var(--text-main);">${data.processor ? data.processor : 'Automated System Authority'}</div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em;">Processing Timestamp</div>
            <div style="font-size:.85rem; font-weight:900; color:var(--text-main);">${data.processed_at ? data.processed_at : 'Pending'}</div>
        </div>
    </div>
    
    ${collectorInfoHtml}`;

        let footerHtml = `
    <button onclick="closeModal()" style="background:var(--bg-main); color:var(--text-main); border:1.5px solid var(--border-color); padding:.75rem 1.5rem; border-radius:12px; font-weight:800; cursor:pointer; font-size:.88rem; transition:0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.03)'" onmouseout="this.style.background='var(--bg-main)'">
        Close Window
    </button>`;

        // If approved or partially approved but not collected, render "Confirm Collection" button in modal too
        if (['approved', 'partially_approved'].includes(data.status)) {
            const isAlreadyCollected = !!data.collected_at;

            if (!isAlreadyCollected) {
                if (canOperateLogistics) {
                    footerHtml += `
                <button onclick="confirmCollection(${id}, this)" id="modalConfirmBtn" disabled
                    style="background:#cbd5e1;color:#64748b;border:none;padding:.75rem 2.25rem;border-radius:12px;font-weight:800;cursor:not-allowed;display:flex;align-items:center;gap:8px;font-size:.88rem;box-shadow:none;transition:0.2s;">
                    <i data-lucide="package-check" style="width:16px;"></i> Confirm Collection
                </button>`;
                } else {
                    footerHtml = `<span style="font-size:0.8rem; font-weight:800; color:#ef4444; display:inline-flex; align-items:center; gap:6px; margin-right:auto; padding-left:1rem;">
                        <i data-lucide="info" style="width:14px; height:14px;"></i> Not Permitted to Confirm Collection
                    </span>` + footerHtml;
                }
            } else {
                footerHtml = `
                <a href="{{ request()->getBasePath() }}/requisitions/receipt/${id}" target="_blank"
                    style="background:rgba(99, 102, 241, 0.08); border: 1.5px solid rgba(99, 102, 241, 0.2); color: #4f46e5; padding: .75rem 1.5rem; border-radius: 12px; font-weight: 800; cursor: pointer; font-size: .88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; margin-right: auto;" onmouseover="this.style.background='#4f46e5'; this.style.color='white';" onmouseout="this.style.background='rgba(99, 102, 241, 0.08)'; this.style.color='#4f46e5';">
                    <i data-lucide="printer" style="width: 16px;"></i> Print Collection Receipt
                </a>` + footerHtml;
            }
        }

        document.getElementById('modalFooter').innerHTML = footerHtml;
        lucide.createIcons();
    }

    function validateModalCollectorInputs() {
        const nameInput = document.getElementById('modalCollectorName');
        const contactInput = document.getElementById('modalCollectorContact');
        const locationInput = document.getElementById('modalCollectorLocation');
        const staffIdInput = document.getElementById('modalCollectorStaffId');
        const confirmBtn = document.getElementById('modalConfirmBtn');
        
        if (!nameInput || !contactInput || !locationInput || !staffIdInput || !confirmBtn) return;
        
        const nameVal = nameInput.value.trim();
        const contactVal = contactInput.value.trim();
        const locationVal = locationInput.value.trim();
        const staffIdVal = staffIdInput.value.trim();
        
        if (nameVal && contactVal && locationVal && staffIdVal) {
            confirmBtn.disabled = false;
            confirmBtn.style.background = '#10b981';
            confirmBtn.style.color = 'white';
            confirmBtn.style.cursor = 'pointer';
            confirmBtn.style.boxShadow = '0 8px 20px rgba(16, 185, 129, 0.25)';
            confirmBtn.onmouseover = function() { this.style.background = '#059669'; };
            confirmBtn.onmouseout = function() { this.style.background = '#10b981'; };
        } else {
            confirmBtn.disabled = true;
            confirmBtn.style.background = '#cbd5e1';
            confirmBtn.style.color = '#64748b';
            confirmBtn.style.cursor = 'not-allowed';
            confirmBtn.style.boxShadow = 'none';
            confirmBtn.onmouseover = null;
            confirmBtn.onmouseout = null;
        }
    }

    function closeModal() {
        document.getElementById('reqModal').classList.remove('open');
    }

    async function confirmCollection(id, btn) {
        // 1. Try to read from the modal inputs first:
        const modal = document.getElementById('reqModal');
        const isModalOpen = modal && modal.classList.contains('open');
        const modalNameInput = document.getElementById('modalCollectorName');
        const modalContactInput = document.getElementById('modalCollectorContact');
        const modalLocationInput = document.getElementById('modalCollectorLocation');
        const modalStaffIdInput = document.getElementById('modalCollectorStaffId');

        let collector_name = '';
        let collector_contact = '';
        let collector_location = '';
        let collector_staff_id = '';

        if (isModalOpen && modalNameInput && modalContactInput && modalLocationInput && modalStaffIdInput) {
            // Triggered from modal
            collector_name = modalNameInput.value.trim();
            collector_contact = modalContactInput.value.trim();
            collector_location = modalLocationInput.value.trim();
            collector_staff_id = modalStaffIdInput.value.trim();

            if (!collector_name || !collector_contact || !collector_location || !collector_staff_id) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required Fields Missing',
                    text: 'Please enter the Collector Name, Staff ID, Contact Number, and Location inside the Collector Information section before confirming collection.',
                    confirmButtonColor: '#10b981'
                });
                if (!collector_name) modalNameInput.style.borderColor = '#ef4444';
                if (!collector_staff_id) modalStaffIdInput.style.borderColor = '#ef4444';
                if (!collector_contact) modalContactInput.style.borderColor = '#ef4444';
                if (!collector_location) modalLocationInput.style.borderColor = '#ef4444';
                return;
            }

            Swal.fire({
                title: 'Confirm Physical Collection',
                html: `Confirm physical collection of items for store requisition <b>#${id}</b>?<br><br>` +
                      `<div style="text-align:left; background:var(--bg-main); border:1px solid var(--border-color); border-radius:12px; padding:12px; font-size:0.85rem;">` +
                      `<b>Collector Name:</b> ${collector_name}<br>` +
                      `<b>Collector Staff ID:</b> ${collector_staff_id}<br>` +
                      `<b>Collector Contact:</b> ${collector_contact}<br>` +
                      `<b>Collection Location/Destination:</b> ${collector_location}` +
                      `</div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Confirm Collection',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#94a3b8'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await executeCollectionFetch(id, btn, collector_name, collector_contact, collector_location, collector_staff_id);
                }
            });

        } else {
            // Triggered from table row. Show SweetAlert2 popup with inputs.
            Swal.fire({
                title: 'Physical Collection Details',
                html:
                    '<p style="font-size:0.85rem; margin-bottom:15px; color:var(--text-muted);">Please enter the details of the person physically collecting the items.</p>' +
                    '<input id="swal-input-name" class="swal2-input" placeholder="Collector Full Name" style="margin-top:0; margin-bottom:12px; width:80%; font-family:inherit; font-size:0.88rem; font-weight:700;">' +
                    '<input id="swal-input-staff-id" class="swal2-input" placeholder="Collector Staff ID" style="margin-top:0; margin-bottom:12px; width:80%; font-family:inherit; font-size:0.88rem; font-weight:700;">' +
                    '<input id="swal-input-contact" class="swal2-input" placeholder="Collector Contact Number" style="margin-top:0; margin-bottom:12px; width:80%; font-family:inherit; font-size:0.88rem; font-weight:700;">' +
                    '<input id="swal-input-location" class="swal2-input" placeholder="Collection Location/Destination" style="margin-top:0; width:80%; font-family:inherit; font-size:0.88rem; font-weight:700;">',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Confirm Collection',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#94a3b8',
                preConfirm: () => {
                    const name = document.getElementById('swal-input-name').value.trim();
                    const staff_id = document.getElementById('swal-input-staff-id').value.trim();
                    const contact = document.getElementById('swal-input-contact').value.trim();
                    const location = document.getElementById('swal-input-location').value.trim();
                    if (!name || !staff_id || !contact || !location) {
                        Swal.showValidationMessage('Please fill out Collector Name, Staff ID, Contact Number, and Location');
                        return false;
                    }
                    return { name: name, staff_id: staff_id, contact: contact, location: location };
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await executeCollectionFetch(id, btn, result.value.name, result.value.contact, result.value.location, result.value.staff_id);
                }
            });
        }
    }

    async function executeCollectionFetch(id, btn, collector_name, collector_contact, collector_location, collector_staff_id) {
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<div style="width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;display:inline-block;vertical-align:middle;margin-right:6px;"></div> Processing...';

        try {
            const response = await fetch(`{{ request()->getBasePath() }}/requisitions/${id}/collect`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    collector_name: collector_name,
                    collector_contact: collector_contact,
                    collector_location: collector_location,
                    collector_staff_id: collector_staff_id
                })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Collection Confirmed',
                    text: data.message + ' Would you like to view or print the official physical collection receipt now?',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Print Receipt',
                    cancelButtonText: 'Dismiss',
                    confirmButtonColor: '#4f46e5',
                    cancelButtonColor: '#94a3b8'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(`{{ request()->getBasePath() }}/requisitions/receipt/${id}`, '_blank');
                    }
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Confirmation Failed',
                    text: data.message,
                    confirmButtonColor: 'var(--primary)'
                });
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'An error occurred while confirming physical collection.',
                confirmButtonColor: 'var(--primary)'
            });
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
    }

    let debounceTimer = null;

    function triggerFilterUpdate() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            updateFilters();
        }, 300); // 300ms debounce
    }

    async function updateFilters() {
        const form = document.getElementById('filter-form');
        const container = document.getElementById('requisitions-table-container');
        if (!form || !container) return;

        container.style.opacity = '0.5';

        const formData = new FormData(form);
        const searchParams = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value.trim() !== '') {
                searchParams.append(key, value);
            }
        }

        const url = form.action + '?' + searchParams.toString();

        try {
            const response = await fetch(url);
            const html = await response.text();

            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTable = doc.getElementById('requisitions-table-container');
            
            if (newTable) {
                container.innerHTML = newTable.innerHTML;
            }
            container.style.opacity = '1';

            if (window.lucide) {
                window.lucide.createIcons();
            }
            bindPaginationClicks();

            window.history.pushState(null, '', url);
        } catch (e) {
            console.error(e);
            container.style.opacity = '1';
        }
    }

    function bindPaginationClicks() {
        const container = document.getElementById('requisitions-table-container');
        if (!container) return;

        const links = container.querySelectorAll('.pagination-container a, td a, th a, div a');
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (!href || href.startsWith('javascript:') || href === '#') return;

            if (href.includes('page=') || href.includes('requisitions')) {
                link.addEventListener('click', async function(e) {
                    e.preventDefault();
                    container.style.opacity = '0.5';
                    try {
                        const response = await fetch(href);
                        const html = await response.text();

                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newTable = doc.getElementById('requisitions-table-container');
                        
                        if (newTable) {
                            container.innerHTML = newTable.innerHTML;
                        }
                        container.style.opacity = '1';

                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                        bindPaginationClicks();

                        window.history.pushState(null, '', href);
                    } catch (err) {
                        console.error(err);
                        container.style.opacity = '1';
                    }
                });
            }
        });
    }

    async function pollStoreRequisitions() {
        const modal = document.getElementById('reqModal');
        if (modal && modal.classList.contains('open')) return;

        const form = document.getElementById('filter-form');
        const container = document.getElementById('requisitions-table-container');
        if (!form || !container) return;

        const formData = new FormData(form);
        const searchParams = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value.trim() !== '') {
                searchParams.append(key, value);
            }
        }

        // Preserve current page pagination parameter
        const currentUrlParams = new URLSearchParams(window.location.search);
        if (currentUrlParams.has('page')) {
            searchParams.append('page', currentUrlParams.get('page'));
        }

        const url = form.action + '?' + searchParams.toString();

        try {
            const response = await fetch(url);
            const html = await response.text();

            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const newTable = doc.getElementById('requisitions-table-container');
            if (newTable) {
                const currentTbody = container.querySelector('tbody');
                const newTbody = newTable.querySelector('tbody');
                
                if (currentTbody && newTbody) {
                    const currentRows = currentTbody.querySelectorAll('.req-table-row');
                    const newRows = newTbody.querySelectorAll('.req-table-row');
                    
                    if (currentRows.length === newRows.length) {
                        // Compare row by row and update only changed rows to prevent visual blinking
                        for (let i = 0; i < currentRows.length; i++) {
                            const cRow = currentRows[i];
                            const nRow = newRows[i];
                            
                            const cKey = `${cRow.dataset.reqId}-${cRow.dataset.status}-${cRow.dataset.collected}`;
                            const nKey = `${nRow.dataset.reqId}-${nRow.dataset.status}-${nRow.dataset.collected}`;
                            
                            if (cKey !== nKey) {
                                cRow.innerHTML = nRow.innerHTML;
                                cRow.dataset.status = nRow.dataset.status;
                                cRow.dataset.collected = nRow.dataset.collected;
                                
                                if (window.lucide) {
                                    window.lucide.createIcons({
                                        node: cRow
                                    });
                                }
                            }
                        }
                    } else {
                        // If row count changed, update only tbody
                        currentTbody.innerHTML = newTbody.innerHTML;
                        if (window.lucide) {
                            window.lucide.createIcons({
                                node: currentTbody
                            });
                        }
                    }

                    // Update pagination if changed without blinking the main structure
                    const currentTable = container.querySelector('table');
                    const newTableEl = newTable.querySelector('table');
                    const currentPagination = currentTable ? currentTable.nextElementSibling : null;
                    const newPagination = newTableEl ? newTableEl.nextElementSibling : null;

                    if (currentPagination && newPagination) {
                        if (currentPagination.innerHTML !== newPagination.innerHTML) {
                            currentPagination.innerHTML = newPagination.innerHTML;
                            if (window.lucide) {
                                window.lucide.createIcons({
                                    node: currentPagination
                                });
                            }
                        }
                    } else if (newPagination && !currentPagination) {
                        container.appendChild(newPagination.cloneNode(true));
                        if (window.lucide) {
                            window.lucide.createIcons({
                                node: container.lastElementChild
                            });
                        }
                    } else if (!newPagination && currentPagination) {
                        currentPagination.remove();
                    }
                    
                    bindPaginationClicks();
                }
            }

            const statsKeys = ['pending', 'urgent', 'approved', 'partially-approved', 'declined'];
            statsKeys.forEach(key => {
                const oldEl = document.getElementById(`stats-${key}`);
                const newEl = doc.getElementById(`stats-${key}`);
                if (oldEl && newEl && oldEl.textContent !== newEl.textContent) {
                    oldEl.textContent = newEl.textContent;
                }
            });
        } catch (e) {
            console.error('Requisitions polling error:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('search_id_input');
        const deptInput = document.getElementById('dept_input');
        if (searchInput) {
            if (searchInput.value) {
                searchInput.focus();
                const len = searchInput.value.length;
                searchInput.setSelectionRange(len, len);
            }
            searchInput.addEventListener('input', triggerFilterUpdate);
        }
        if (deptInput) {
            deptInput.addEventListener('input', triggerFilterUpdate);
        }
        bindPaginationClicks();
        if (window.lucide) {
            window.lucide.createIcons();
        }

        // Start polling every 10 seconds
        setInterval(pollStoreRequisitions, 10000);
    });

    // ════════════════════════════════════════════════════════
    // NEW REQUISITION SLIDE-OVER LOGIC
    // ════════════════════════════════════════════════════════
    const nrAvailableItems = @json($availableItems);
    const nrLedgeMap = @json($ledgeMap);
    let nrCurrentStep = 1;
    let nrCartItems = [];
    let nrSelectedPriority = 'normal';
    let nrSelectedUsage = 'permanent';

    function openNewReqPanel() {
        nrCurrentStep = 1;
        nrCartItems = [];
        nrSelectedPriority = 'normal';
        nrSelectedUsage = 'permanent';
        nrActiveCat = 'all';
        nrCatalogSearch = '';
        resetNrForm();
        nrUpdateStep();
        const overlay = document.getElementById('newReqOverlay');
        const panel   = document.getElementById('newReqPanel');
        overlay.style.display = 'flex';
        setTimeout(() => {
            panel.style.transform = 'translateX(0)';
            // Build catalog after panel is visible
            nrBuildCatalogTabs();
            nrRenderCatalog();
            if (window.lucide) lucide.createIcons();
        }, 15);
        document.body.style.overflow = 'hidden';
    }

    function closeNewReqPanel() {
        const overlay = document.getElementById('newReqOverlay');
        const panel   = document.getElementById('newReqPanel');
        panel.style.transform = 'translateX(100%)';
        setTimeout(() => { overlay.style.display = 'none'; }, 400);
        document.body.style.overflow = '';
    }

    function resetNrForm() {
        document.getElementById('nr-requester-name').value = '{{ addslashes(auth()->user()->name) }}';
        document.getElementById('nr-department').value = '{{ addslashes(auth()->user()->department ?? "") }}';
        document.getElementById('nr-rank').value = '{{ addslashes(auth()->user()->rank_or_title ?? "") }}';
        document.getElementById('nr-purpose').value = '';
        const srch = document.getElementById('nr-item-search');
        if (srch) srch.value = '';
        nrRenderCartPanel();
        selectNrPriority('normal');
        selectNrUsage('permanent');
        document.getElementById('nr-review-content').innerHTML = '';
    }

    function nrUpdateStep() {
        [1,2,3].forEach(s => {
            document.getElementById(`nr-step-${s}`).style.display = (s === nrCurrentStep) ? 'block' : 'none';
            const bubble = document.getElementById(`nr-bubble-${s}`);
            const label  = document.getElementById(`nr-label-${s}`);
            if (s < nrCurrentStep) {
                bubble.className = 'nr-step-bubble done';
                bubble.innerHTML = '✓';
                label.className = 'nr-step-label done';
            } else if (s === nrCurrentStep) {
                bubble.className = 'nr-step-bubble active';
                bubble.innerHTML = s;
                label.className = 'nr-step-label active';
            } else {
                bubble.className = 'nr-step-bubble';
                bubble.innerHTML = s;
                label.className = 'nr-step-label';
            }
        });

        document.getElementById('nr-progress-1').style.width = (nrCurrentStep > 1 ? '100%' : '0');
        document.getElementById('nr-progress-2').style.width = (nrCurrentStep > 2 ? '100%' : '0');

        const btnBack   = document.getElementById('nr-btn-back');
        const btnNext   = document.getElementById('nr-btn-next');
        const btnSubmit = document.getElementById('nr-btn-submit');

        if (nrCurrentStep === 1) {
            btnBack.style.display   = 'none';
            btnNext.style.display   = 'inline-flex';
            btnSubmit.style.display = 'none';
        } else if (nrCurrentStep === 2) {
            btnBack.style.display   = 'inline-flex';
            btnNext.style.display   = 'inline-flex';
            btnSubmit.style.display = 'none';
        } else {
            btnBack.style.display   = 'inline-flex';
            btnNext.style.display   = 'none';
            btnSubmit.style.display = 'inline-flex';
        }

        if (window.lucide) lucide.createIcons();
    }

    function nrGoNext() {
        if (nrCurrentStep === 1) {
            const name    = document.getElementById('nr-requester-name').value.trim();
            const dept    = document.getElementById('nr-department').value.trim();
            const purpose = document.getElementById('nr-purpose').value.trim();
            if (!name || !dept || !purpose) {
                Swal.fire({ icon:'warning', title:'Missing Fields', text:'Please fill in Full Name, Department, and Purpose.', confirmButtonColor:'#4f46e5' });
                return;
            }
        } else if (nrCurrentStep === 2) {
            if (nrCartItems.length === 0) {
                Swal.fire({ icon:'warning', title:'No Items Added', text:'Please add at least one item to your requisition.', confirmButtonColor:'#4f46e5' });
                return;
            }
            const invalidQty = nrCartItems.find(i => !i.qty || parseFloat(i.qty) <= 0);
            if (invalidQty) {
                Swal.fire({ icon:'warning', title:'Invalid Quantity', text:`Please enter a valid quantity for: ${invalidQty.description}`, confirmButtonColor:'#4f46e5' });
                return;
            }
            nrBuildReview();
        }
        nrCurrentStep++;
        nrUpdateStep();
    }

    function nrGoBack() {
        nrCurrentStep--;
        nrUpdateStep();
    }

    function selectNrPriority(val) {
        nrSelectedPriority = val;
        document.getElementById('nr-priority').value = val;
        document.querySelectorAll('.nr-priority-btn').forEach(btn => {
            const v = btn.dataset.val;
            if (v === val) {
                if (v === 'urgent')      { btn.style.borderColor='#dc2626'; btn.style.background='rgba(220,38,38,.08)'; btn.style.color='#dc2626'; }
                else if (v === 'normal') { btn.style.borderColor='#4f46e5'; btn.style.background='rgba(79,70,229,.08)'; btn.style.color='#4f46e5'; }
                else                     { btn.style.borderColor='#64748b'; btn.style.background='rgba(100,116,139,.08)'; btn.style.color='#64748b'; }
            } else {
                btn.style.borderColor='var(--border-color)'; btn.style.background='var(--bg-card)'; btn.style.color='var(--text-muted)';
            }
        });
    }

    function selectNrUsage(val) {
        nrSelectedUsage = val;
        document.getElementById('nr-usage-permanent').checked = (val === 'permanent');
        document.getElementById('nr-usage-temporary').checked = (val === 'temporary');
        const permLabel = document.getElementById('nr-usage-perm-label');
        const tempLabel = document.getElementById('nr-usage-temp-label');
        if (val === 'permanent') {
            permLabel.style.borderColor = '#4f46e5'; permLabel.style.background = 'rgba(79,70,229,.04)';
            tempLabel.style.borderColor = 'var(--border-color)'; tempLabel.style.background = '';
        } else {
            tempLabel.style.borderColor = '#f59e0b'; tempLabel.style.background = 'rgba(245,158,11,.04)';
            permLabel.style.borderColor = 'var(--border-color)'; permLabel.style.background = '';
        }
    }

    // ── Item Catalog (Step 2) ──────────────────────────────
    let nrActiveCat = 'all';
    let nrCatalogSearch = '';

    // Build category tabs from available items
    function nrBuildCatalogTabs() {
        const tabsEl = document.getElementById('nr-cat-tabs');
        if (!tabsEl) return;
        const cats = {};
        nrAvailableItems.forEach(i => {
            const key = i.ledge_category || '';
            cats[key] = (cats[key] || 0) + 1;
        });
        const allCount = nrAvailableItems.length;
        let html = `<button onclick="nrSetCat('all')" id="nr-tab-all"
            style="padding:.35rem .85rem;border-radius:999px;border:1.5px solid #4f46e5;background:rgba(79,70,229,.1);color:#4f46e5;font-weight:800;font-size:.7rem;cursor:pointer;transition:.2s;white-space:nowrap;">
            All <span style="opacity:.7;">(${allCount})</span>
        </button>`;
        Object.entries(cats).sort((a,b) => b[1]-a[1]).forEach(([cat, cnt]) => {
            const label = (cat && nrLedgeMap[cat]) ? nrLedgeMap[cat] : (cat || 'Uncategorised');
            html += `<button onclick="nrSetCat('${cat}')" id="nr-tab-${cat}"
                style="padding:.35rem .85rem;border-radius:999px;border:1.5px solid var(--border-color);background:var(--bg-card);color:var(--text-muted);font-weight:800;font-size:.7rem;cursor:pointer;transition:.2s;white-space:nowrap;">
                ${label} <span style="opacity:.7;">(${cnt})</span>
            </button>`;
        });
        tabsEl.innerHTML = html;
    }

    function nrSetCat(cat) {
        nrActiveCat = cat;
        // Update tab styles
        document.querySelectorAll('[id^="nr-tab-"]').forEach(btn => {
            const isSel = btn.id === `nr-tab-${cat}`;
            btn.style.borderColor  = isSel ? '#4f46e5' : 'var(--border-color)';
            btn.style.background   = isSel ? 'rgba(79,70,229,.1)' : 'var(--bg-card)';
            btn.style.color        = isSel ? '#4f46e5' : 'var(--text-muted)';
        });
        nrRenderCatalog();
    }

    function nrFilterCatalog(query) {
        nrCatalogSearch = (query || '').toLowerCase().trim();
        nrRenderCatalog();
    }

    // Keeps track of the currently filtered items so index refs stay valid
    let nrFilteredItems = [];

    function nrRenderCatalog() {
        const grid  = document.getElementById('nr-catalog-grid');
        const empty = document.getElementById('nr-catalog-empty');
        if (!grid) return;

        let items = nrAvailableItems;
        if (nrActiveCat !== 'all') {
            items = items.filter(i => (i.ledge_category || '') === nrActiveCat);
        }
        if (nrCatalogSearch) {
            items = items.filter(i => i.description.toLowerCase().includes(nrCatalogSearch));
        }
        nrFilteredItems = items; // save so click handler can look up by index

        if (items.length === 0) {
            grid.style.display  = 'none';
            empty.style.display = 'block';
            return;
        }
        grid.style.display  = 'grid';
        empty.style.display = 'none';

        grid.innerHTML = items.map((item, idx) => {
            const catName    = (item.ledge_category && nrLedgeMap[item.ledge_category]) ? nrLedgeMap[item.ledge_category] : (item.ledge_category || '');
            const stock      = parseFloat(item.total_stock) || 0;
            const stockColor = stock > 10 ? '#10b981' : stock > 0 ? '#f59e0b' : '#ef4444';
            const stockLabel = stock > 10 ? 'In Stock' : stock > 0 ? 'Low Stock' : 'Out of Stock';
            const inCart     = !!nrCartItems.find(c => c.description === item.description && c.category === item.ledge_category);

            return `<div class="nr-catalog-card" data-nr-idx="${idx}"
                style="background:var(--bg-card);border:1.5px solid ${inCart ? '#4f46e5' : 'var(--border-color)'};border-radius:12px;padding:.85rem;cursor:pointer;transition:all .2s;display:flex;flex-direction:column;gap:.4rem;position:relative;${inCart ? 'background:rgba(79,70,229,.04);' : ''}">
                ${inCart ? `<div style="position:absolute;top:8px;right:8px;width:18px;height:18px;background:#4f46e5;border-radius:50%;display:flex;align-items:center;justify-content:center;"><svg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'><polyline points='20 6 9 17 4 12'/></svg></div>` : ''}
                <div style="font-size:.78rem;font-weight:800;color:var(--text-main);line-height:1.3;${inCart ? 'padding-right:22px;' : ''}">${item.description}</div>
                ${catName ? `<div style="font-size:.65rem;font-weight:700;color:#4f46e5;background:rgba(79,70,229,.08);padding:1px 6px;border-radius:5px;align-self:flex-start;">${catName}</div>` : ''}
                <div style="margin-top:auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:4px;">
                    <span style="font-size:.65rem;font-weight:800;color:${stockColor};background:${stockColor}18;padding:2px 6px;border-radius:5px;">${stockLabel}</span>
                    <span style="font-size:.65rem;font-weight:700;color:var(--text-muted);">${stock} ${item.unit || 'units'}</span>
                </div>
            </div>`;
        }).join('');

        // Single delegated click listener on the grid
        grid.onclick = function(e) {
            const card = e.target.closest('.nr-catalog-card');
            if (!card) return;
            const idx  = parseInt(card.dataset.nrIdx, 10);
            const item = nrFilteredItems[idx];
            if (!item) return;
            nrCatalogToggleItem(item);
        };

        // Hover effects via delegation
        grid.onmouseover = function(e) {
            const card = e.target.closest('.nr-catalog-card');
            if (!card) return;
            card.style.boxShadow = '0 4px 14px rgba(79,70,229,.12)';
            if (!nrCartItems.find(c => c.description === nrFilteredItems[parseInt(card.dataset.nrIdx,10)]?.description)) {
                card.style.borderColor = 'rgba(79,70,229,.4)';
            }
        };
        grid.onmouseout = function(e) {
            const card = e.target.closest('.nr-catalog-card');
            if (!card) return;
            card.style.boxShadow = '';
            const item = nrFilteredItems[parseInt(card.dataset.nrIdx, 10)];
            if (item && nrCartItems.find(c => c.description === item.description && c.category === item.ledge_category)) {
                card.style.borderColor = '#4f46e5';
            } else {
                card.style.borderColor = 'var(--border-color)';
            }
        };
    }

    function nrCatalogToggleItem(item) {
        const desc = item.description;
        const cat  = item.ledge_category;
        const existIdx = nrCartItems.findIndex(c => c.description === desc && c.category === cat);
        if (existIdx !== -1) {
            nrCartItems.splice(existIdx, 1); // toggle off
        } else {
            nrCartItems.push({ description: desc, category: cat, unit: item.unit || 'units', stock: parseFloat(item.total_stock) || 0, qty: 1, remarks: '' });
        }
        nrRenderCatalog();
        nrRenderCartPanel();
    }

    // Legacy alias kept for removeNrItem calls inside cart panel
    function nrCatalogAddItem(itemJson) {
        const itemObj = typeof itemJson === 'string' ? JSON.parse(itemJson) : itemJson;
        nrCatalogToggleItem(itemObj);
    }

    function removeNrItem(idx) {
        nrCartItems.splice(idx, 1);
        nrRenderCatalog();   // update card checkmarks
        nrRenderCartPanel();
    }

    // Re-use legacy alias for reset path
    function nrRenderItemsEmpty() { nrRenderCartPanel(); }
    function nrRenderItemsList()  { nrRenderCartPanel(); }

    function nrRenderCartPanel() {
        const list  = document.getElementById('nr-items-list');
        const empty = document.getElementById('nr-items-empty');
        const count = document.getElementById('nr-cart-count');
        if (count) count.textContent = nrCartItems.length;

        if (!list) return;

        if (nrCartItems.length === 0) {
            list.innerHTML = '';
            if (empty) empty.style.display = 'flex';
        } else {
            if (empty) empty.style.display = 'none';
            list.innerHTML = nrCartItems.map((item, idx) => {
                const stockColor = item.stock > 10 ? '#10b981' : item.stock > 0 ? '#f59e0b' : '#ef4444';
                return `<div style="background:var(--bg-card);border:1.5px solid var(--border-color);border-radius:10px;padding:.65rem .85rem;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;margin-bottom:.5rem;">
                        <div style="font-size:.78rem;font-weight:800;color:var(--text-main);line-height:1.3;flex:1;">${item.description}</div>
                        <button onclick="removeNrItem(${idx})" style="width:22px;height:22px;flex-shrink:0;border:none;border-radius:6px;background:rgba(239,68,68,.08);color:#ef4444;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s;" onmouseover="this.style.background='#ef4444';this.style.color='white'" onmouseout="this.style.background='rgba(239,68,68,.08)';this.style.color='#ef4444'">
                            <svg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><line x1='18' y1='6' x2='6' y2='18'/><line x1='6' y1='6' x2='18' y2='18'/></svg>
                        </button>
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <div style="display:flex;align-items:center;border:1.5px solid var(--border-color);border-radius:7px;overflow:hidden;background:var(--bg-main);flex-shrink:0;">
                            <button type="button" onclick="adjustNrQty(${idx},-1)" style="padding:.2rem .5rem;border:none;background:transparent;cursor:pointer;color:var(--text-muted);font-weight:900;font-size:.9rem;transition:.15s;" onmouseover="this.style.background='var(--border-color)'" onmouseout="this.style.background='transparent'">−</button>
                            <input type="number" min="0.01" step="0.01" value="${item.qty}" onchange="updateNrQty(${idx},this.value)" style="width:38px;text-align:center;border:none;background:transparent;font-family:inherit;font-weight:700;font-size:.8rem;color:var(--text-main);outline:none;padding:.2rem 0;">
                            <button type="button" onclick="adjustNrQty(${idx},1)" style="padding:.2rem .5rem;border:none;background:transparent;cursor:pointer;color:var(--text-muted);font-weight:900;font-size:.9rem;transition:.15s;" onmouseover="this.style.background='var(--border-color)'" onmouseout="this.style.background='transparent'">+</button>
                        </div>
                        <input type="text" value="${item.remarks}" placeholder="Remarks..." oninput="updateNrRemarks(${idx},this.value)" style="flex:1;min-width:0;padding:.3rem .5rem;border:1.5px solid var(--border-color);border-radius:7px;background:var(--bg-main);color:var(--text-main);font-family:inherit;font-size:.72rem;font-weight:600;outline:none;transition:.2s;" onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='var(--border-color)'">
                    </div>
                </div>`;
            }).join('');
        }
        if (window.lucide) lucide.createIcons();
    }

    function adjustNrQty(idx, delta) {
        let val = parseFloat(nrCartItems[idx].qty) + delta;
        if (val < 0.01) val = 0.01;
        nrCartItems[idx].qty = Math.round(val * 100) / 100;
        nrRenderCartPanel();
    }
    function updateNrQty(idx, val) {
        nrCartItems[idx].qty = parseFloat(val) || 0.01;
    }
    function updateNrRemarks(idx, val) {
        nrCartItems[idx].remarks = val;
    }

    // ── Step 3: Review Builder ─────────────────────────────
    function nrBuildReview() {
        const name    = document.getElementById('nr-requester-name').value.trim();
        const dept    = document.getElementById('nr-department').value.trim();
        const rank    = document.getElementById('nr-rank').value.trim();
        const purpose = document.getElementById('nr-purpose').value.trim();
        const priorityColors = { urgent:'#dc2626', normal:'#4f46e5', low:'#64748b' };
        const usageColors    = { permanent:'#4f46e5', temporary:'#f59e0b' };

        let html = `
        <div style="background:var(--bg-main);border:1.5px solid var(--border-color);border-radius:14px;padding:1.25rem;margin-bottom:1rem;">
            <div style="font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.75rem;">Requester Details</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem .5rem;font-size:.85rem;">
                <div><span style="font-weight:700;color:var(--text-muted);font-size:.75rem;">Name</span><br><span style="font-weight:800;color:var(--text-main);">${name}</span></div>
                <div><span style="font-weight:700;color:var(--text-muted);font-size:.75rem;">Department</span><br><span style="font-weight:800;color:var(--text-main);">${dept}</span></div>
                ${rank ? `<div><span style="font-weight:700;color:var(--text-muted);font-size:.75rem;">Rank</span><br><span style="font-weight:800;color:var(--text-main);">${rank}</span></div>` : ''}
                <div><span style="font-weight:700;color:var(--text-muted);font-size:.75rem;">Usage Type</span><br><span style="font-weight:800;color:${usageColors[nrSelectedUsage]};text-transform:capitalize;">${nrSelectedUsage}</span></div>
            </div>
            <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--border-color);">
                <span style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Purpose:</span>
                <p style="margin:4px 0 0;font-size:.85rem;font-weight:600;color:var(--text-main);font-style:italic;">&ldquo;${purpose}&rdquo;</p>
            </div>
        </div>
        <div style="background:var(--bg-main);border:1.5px solid var(--border-color);border-radius:14px;overflow:hidden;margin-bottom:1rem;">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-color);">
                <div style="font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Items Requested (${nrCartItems.length})</div>
            </div>
            ${nrCartItems.map(item => {
                const catName = (item.category && nrLedgeMap[item.category]) ? nrLedgeMap[item.category] : (item.category || '');
                return `<div style="padding:.85rem 1.25rem;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;gap:1rem;">
                    <div>
                        <div style="font-size:.88rem;font-weight:800;color:var(--text-main);">${item.description}</div>
                        ${catName ? `<div style="font-size:.7rem;color:#4f46e5;font-weight:700;">${catName}</div>` : ''}
                        ${item.remarks ? `<div style="font-size:.72rem;color:var(--text-muted);font-style:italic;">${item.remarks}</div>` : ''}
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <span style="font-size:1rem;font-weight:900;color:var(--text-main);">${item.qty}</span>
                        <span style="font-size:.78rem;color:var(--text-muted);"> ${item.unit}</span>
                    </div>
                </div>`;
            }).join('')}
        </div>
        <div style="background:rgba(16,185,129,.05);border:1.5px solid rgba(16,185,129,.2);border-radius:12px;padding:1rem 1.25rem;display:flex;align-items:flex-start;gap:.75rem;">
            <i data-lucide="info" style="width:16px;color:#10b981;flex-shrink:0;margin-top:2px;"></i>
            <p style="margin:0;font-size:.8rem;font-weight:600;color:var(--text-muted);">Please review all details carefully. Once submitted, this requisition will be routed to the department head and stores admin for approval.</p>
        </div>`;

        document.getElementById('nr-review-content').innerHTML = html;
        if (window.lucide) lucide.createIcons();
    }

    // ── Submit ─────────────────────────────────────────────
    async function submitNewReq() {
        const btn = document.getElementById('nr-btn-submit');
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.style.opacity = '0.8';
        btn.innerHTML = '<div style="width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;display:inline-block;vertical-align:middle;margin-right:6px;"></div> Submitting...';

        const payload = {
            requester_name: document.getElementById('nr-requester-name').value.trim(),
            department:     document.getElementById('nr-department').value.trim(),
            rank_or_title:  document.getElementById('nr-rank').value.trim(),
            priority:       nrSelectedPriority,
            usage_type:     nrSelectedUsage,
            purpose:        document.getElementById('nr-purpose').value.trim(),
            items: nrCartItems.map(i => ({
                description:        i.description,
                category:           i.category,
                unit:               i.unit,
                quantity_requested: parseFloat(i.qty),
                remarks:            i.remarks || null,
            }))
        };

        try {
            const resp = await fetch('{{ route("requisitions.store") }}', {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await resp.json();

            if (data.success) {
                closeNewReqPanel();
                Swal.fire({
                    icon:'success',
                    title:'Requisition Submitted!',
                    html:`Your store requisition <b>#${data.id}</b> has been submitted successfully and the relevant approvers have been notified.`,
                    confirmButtonColor:'#4f46e5'
                }).then(() => location.reload());
            } else {
                Swal.fire({ icon:'error', title:'Submission Failed', text: data.message || 'An error occurred.', confirmButtonColor:'#4f46e5' });
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.innerHTML = originalHTML;
            }
        } catch (err) {
            Swal.fire({ icon:'error', title:'Network Error', text:'Could not connect to the server. Please try again.', confirmButtonColor:'#4f46e5' });
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.innerHTML = originalHTML;
        }
    }
</script>
@endsection