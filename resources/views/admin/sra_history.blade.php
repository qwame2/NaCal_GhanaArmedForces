@php
    $sraLayout = auth()->user()->isMainAdminOrSub() ? 'layouts.dashboard' : 'layouts.admin';
@endphp

@extends($sraLayout)

@section('title', 'Service SRA Approvals')

@section('content')
<style>
    .main-wrapper > *:not(header) {
        max-width: 2000px !important;
    }

    .sra-stat-card {
        background: var(--bg-card);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: var(--shadow-luxe);
    }

    .sra-table-row {
        border-bottom: 1px solid var(--border-color);
        transition: .15s;
    }

    .sra-table-row:hover {
        background: rgba(22, 163, 74, .03);
    }

    .sra-table-row:last-child {
        border-bottom: none;
    }

    /* Tab Controls */
    .sra-tabs-container {
        display: flex;
        gap: 0.5rem;
        border-bottom: 2px solid var(--border-color);
        margin-bottom: 1.5rem;
        padding-bottom: 2px;
    }

    .sra-tab-btn {
        padding: 0.75rem 1.5rem;
        font-weight: 700;
        font-size: 0.88rem;
        color: var(--text-muted);
        text-decoration: none;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sra-tab-btn:hover {
        color: var(--primary);
    }

    .sra-tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    .sra-badge {
        font-size: 0.72rem;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 99px;
        background: #f1f5f9;
        color: #475569;
    }

    .sra-tab-btn.active .sra-badge {
        background: var(--primary-glow);
        color: var(--primary);
    }

    /* Mini Tracker Timeline */
    .mini-tracker {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        position: relative;
        margin-top: 4px;
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
        border: 2.5px solid var(--border-color);
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
        background: #15803d;
        border-color: #15803d;
        color: white;
        box-shadow: 0 0 8px rgba(22, 163, 74, 0.3);
    }

    .mini-step.declined .mini-dot {
        background: #ef4444;
        border-color: #ef4444;
        color: white;
    }

    .mini-step.bypassed .mini-dot {
        background: #cbd5e1;
        border-color: #cbd5e1;
        color: #94a3b8;
    }

    .mini-line {
        width: 18px;
        height: 2.5px;
        background: var(--border-color);
        position: relative;
        z-index: 1;
        margin: 0 -2px;
    }

    .mini-line.completed {
        background: #10b981;
    }

    .mini-label {
        font-size: 0.58rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-top: 2px;
        letter-spacing: 0.02em;
    }

    .mini-step.completed .mini-label {
        color: #10b981;
    }

    .mini-step.active .mini-label {
        color: #15803d;
        font-weight: 900;
    }

    .mini-step.declined .mini-label {
        color: #ef4444;
    }

    .mini-step.bypassed .mini-label {
        color: #94a3b8;
    }

    .filter-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-luxe);
    }

    .filter-row {
        display: flex;
        gap: 0.85rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-field-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        flex: 1;
        min-width: 200px;
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
        transition: all 0.2s;
    }

    .filter-control:focus {
        border-color: var(--primary);
        background: var(--bg-card);
        box-shadow: 0 0 0 4px var(--primary-glow);
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
    }

    .filter-clear-btn:hover {
        background: #ef4444;
        color: white;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
    }

    .sra-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(22, 163, 74, 0.1);
        color: var(--primary);
        border: 1px solid rgba(22, 163, 74, 0.2);
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 0.8rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .sra-action-btn:hover {
        background: var(--primary);
        color: white;
        box-shadow: 0 4px 10px var(--primary-glow);
    }

    /* Premium Glassmorphic Pagination */
    .custom-pagination nav { display: flex; justify-content: center; }
    .custom-pagination ul.pagination { display: flex; gap: 0.5rem; list-style: none; padding: 0; margin: 0; align-items: center; }
    .custom-pagination .page-item .page-link {
        display: flex; align-items: center; justify-content: center;
        min-width: 48px; height: 48px; border-radius: 16px;
        background: white; border: 1.5px solid #edf2f7;
        color: var(--text-main); font-weight: 900; text-decoration: none;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.95rem; box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    }
    .custom-pagination .page-item.active .page-link {
        background: var(--primary); color: white;
        border-color: var(--primary);
        box-shadow: 0 10px 25px rgba(22, 163, 74, 0.25);
        transform: scale(1.1);
        z-index: 10;
    }
    .custom-pagination .page-item:not(.active):not(.disabled) .page-link:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-4px);
        background: #f5f3ff;
        box-shadow: 0 8px 20px rgba(22, 163, 74, 0.1);
    }
    .custom-pagination .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8fafc;
    }
    .custom-pagination .page-item:first-child .page-link,
    .custom-pagination .page-item:last-child .page-link {
        padding: 0 1.25rem;
        min-width: auto;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
</style>

<div style="padding: 2rem; width: 100%; box-sizing: border-box;">
    
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h3 style="font-size: 1.5rem; font-weight: 900; color: var(--text-heading); margin: 0;">Service SRA Approvals</h3>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin: 4px 0 0;">Track, review, and print Stock Receipt Advice (SRA) documents.</p>
        </div>
    </div>

    <!-- Quick Metrics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="sra-stat-card">
            <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Pending Approval</div>
                <div style="font-size: 1.5rem; font-weight: 900; color: var(--text-main);">{{ $totalPendingCount }}</div>
            </div>
        </div>
        <div class="sra-stat-card">
            <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Fully Approved</div>
                <div style="font-size: 1.5rem; font-weight: 900; color: var(--text-main);">{{ $totalApprovedCount }}</div>
            </div>
        </div>
        <div class="sra-stat-card">
            <div style="width: 48px; height: 48px; background: rgba(220, 38, 38, 0.1); color: #dc2626; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Declined Entries</div>
                <div style="font-size: 1.5rem; font-weight: 900; color: var(--text-main);">{{ $totalDeclinedCount }}</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form action="{{ route('admin.sra-history') }}" method="GET" class="filter-row">
            <input type="hidden" name="status" value="{{ $status }}">
            
            <div class="filter-field-wrapper" style="flex: 1.5;">
                <svg class="filter-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                <input type="text" name="supplier" value="{{ request('supplier') }}" placeholder="Search supplier / donor..." class="filter-control">
            </div>

            <div class="filter-field-wrapper">
                <svg class="filter-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="filter-control" style="padding-left: 2.6rem;">
            </div>

            <div class="filter-field-wrapper">
                <svg class="filter-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="filter-control" style="padding-left: 2.6rem;">
            </div>

            <button type="submit" style="background: var(--primary); color: white; border: none; padding: 0.7rem 1.5rem; border-radius: 12px; font-weight: 800; font-size: 0.85rem; cursor: pointer; transition: 0.2s;">Filter Logs</button>
            
            @if(request()->filled('supplier') || request()->filled('date_from') || request()->filled('date_to'))
                <a href="{{ route('admin.sra-history', ['status' => $status]) }}" class="filter-clear-btn">Clear</a>
            @endif
        </form>
    </div>

    <!-- Status Tabs -->
    <div class="sra-tabs-container">
        <a href="{{ route('admin.sra-history', array_merge(request()->except('status'), ['status' => 'all'])) }}" class="sra-tab-btn {{ $status === 'all' ? 'active' : '' }}">
            All SRAs <span class="sra-badge">{{ $totalAllCount }}</span>
        </a>
        <a href="{{ route('admin.sra-history', array_merge(request()->except('status'), ['status' => 'pending'])) }}" class="sra-tab-btn {{ $status === 'pending' ? 'active' : '' }}">
            Pending Final Review <span class="sra-badge" style="background: #fef3c7; color: #d97706;">{{ $totalPendingCount }}</span>
        </a>
        <a href="{{ route('admin.sra-history', array_merge(request()->except('status'), ['status' => 'approved'])) }}" class="sra-tab-btn {{ $status === 'approved' ? 'active' : '' }}">
            Fully Approved <span class="sra-badge" style="background: #d1fae5; color: #065f46;">{{ $totalApprovedCount }}</span>
        </a>
        <a href="{{ route('admin.sra-history', array_merge(request()->except('status'), ['status' => 'declined'])) }}" class="sra-tab-btn {{ $status === 'declined' ? 'active' : '' }}">
            Declined <span class="sra-badge" style="background: #fee2e2; color: #991b1b;">{{ $totalDeclinedCount }}</span>
        </a>
    </div>

    <!-- SRA Table Logs -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-luxe);">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 1000px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid var(--border-color);">
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">SRA #</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Date Logged</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Ledge Category</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Supplier / Donor</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Line Items</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Verification Flow</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Overall Status</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        <tr class="sra-table-row">
                            <!-- SRA # -->
                            <td style="padding: 1.25rem 1.5rem; font-weight: 900; color: var(--text-main); font-size: 0.95rem;">
                                SRA-{{ str_pad($batch->id, 6, '0', STR_PAD_LEFT) }}
                            </td>
                            
                            <!-- Date Logged -->
                            <td style="padding: 1.25rem 1.5rem; font-size: 0.85rem; color: var(--text-muted); font-weight: 700;">
                                {{ \Carbon\Carbon::parse($batch->entry_date)->format('d M Y, h:i A') }}
                            </td>
                            
                            <!-- Category -->
                            <td style="padding: 1.25rem 1.5rem; font-size: 0.85rem;">
                                <span style="background: rgba(22, 163, 74, 0.08); color: var(--primary); padding: 4px 10px; border-radius: 6px; font-weight: 800; text-transform: uppercase;">
                                    Category {{ $batch->ledge_category }}
                                </span>
                            </td>
                            
                            <!-- Supplier / Donor -->
                            <td style="padding: 1.25rem 1.5rem; font-size: 0.9rem; font-weight: 700; color: var(--text-main);">
                                @if($batch->acquisition_type === 'Donor')
                                    <span style="color: #10b981;">{{ $batch->donor_name ?: $batch->supplier_name }}</span>
                                    <span style="font-size: 0.65rem; background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-weight: 800; margin-left: 4px; text-transform: uppercase;">Donor</span>
                                @else
                                    <span>{{ $batch->supplier_name }}</span>
                                @endif
                            </td>
                            
                            <!-- Items Count -->
                            <td style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--text-main); font-size: 0.95rem;">
                                {{ $batch->items->count() }} Item(s)
                            </td>

                            <!-- Verification Timeline Tracker -->
                            <td style="padding: 1.25rem 1.5rem;">
                                <div class="mini-tracker">
                                    <!-- Step 1: Stores -->
                                    <div class="mini-step completed" title="Recorded / Approved by Stores Department">
                                        <div class="mini-dot">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                        <span class="mini-label">Stores</span>
                                    </div>
                                    <div class="mini-line completed"></div>

                                    <!-- Step 2: Auditor -->
                                    @php
                                        $audStatus = $batch->auditor_status;
                                        $audClass = $audStatus === 'approved' ? 'completed' : ($audStatus === 'declined' ? 'declined' : 'active');
                                    @endphp
                                    <div class="mini-step {{ $audClass }}" title="Auditor: {{ ucfirst($audStatus ?: 'pending') }}">
                                        <div class="mini-dot">
                                            @if($audStatus === 'approved')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            @elseif($audStatus === 'declined')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                            @endif
                                        </div>
                                        <span class="mini-label">Audit</span>
                                    </div>
                                    <div class="mini-line {{ $audStatus === 'approved' ? 'completed' : '' }}"></div>

                                    <!-- Step 3: Head of Admin -->
                                    @php
                                        $admStatus = $batch->admin_status;
                                        $admClass = $admStatus === 'approved' ? 'completed' : ($admStatus === 'declined' ? 'declined' : ($audStatus === 'approved' ? 'active' : 'bypassed'));
                                    @endphp
                                    <div class="mini-step {{ $admClass }}" title="Head of Admin: {{ ucfirst($admStatus ?: 'pending') }}">
                                        <div class="mini-dot">
                                            @if($admStatus === 'approved')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            @elseif($admStatus === 'declined')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                            @endif
                                        </div>
                                        <span class="mini-label">Admin</span>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Overall Status -->
                            <td style="padding: 1.25rem 1.5rem;">
                                @if($batch->approval_status === 'approved')
                                    <span style="font-size: 0.72rem; font-weight: 800; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 10px; border-radius: 6px; text-transform: uppercase;">
                                        Approved
                                    </span>
                                @elseif($batch->approval_status === 'declined')
                                    <span style="font-size: 0.72rem; font-weight: 800; background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 4px 10px; border-radius: 6px; text-transform: uppercase;">
                                        Declined
                                    </span>
                                @else
                                    <span style="font-size: 0.72rem; font-weight: 800; background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 4px 10px; border-radius: 6px; text-transform: uppercase;">
                                        Pending Review
                                    </span>
                                @endif
                            </td>
                            
                            <!-- Actions -->
                            <td style="padding: 1.25rem 1.5rem; text-align: right;">
                                <div style="display: inline-flex; gap: 8px; align-items: center;">
                                    <a href="{{ route('receiveditems.sra', $batch->id) }}" target="_blank" class="sra-action-btn" title="View SRA Receipt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <span>View Receipt</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding: 3rem; text-align: center; color: var(--text-muted); font-size: 0.95rem; font-weight: 700;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 0.5rem; opacity: 0.4;"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14.5 2 14.5 7.5 20 7.5"/></svg>
                                <div>No SRA entries matching search query.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($batches->hasPages())
        <div style="margin-top: 2rem; display: flex; flex-direction: column; align-items: center; gap: 1.5rem; padding: 1.5rem;">
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--text-muted); display: flex; align-items: center; gap: 8px; background: white; padding: 0.5rem 1.25rem; border-radius: 100px; border: 1.5px solid #edf2f7; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                Showing <span style="color: var(--text-main);">{{ $batches->firstItem() ?? 0 }}</span> to <span style="color: var(--text-main);">{{ $batches->lastItem() ?? 0 }}</span> of <span style="color: var(--text-main);">{{ $batches->total() }}</span> records
            </div>
            <div class="custom-pagination">
                {{ $batches->appends(request()->all())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    @endif
</div>
</div>
@endsection
