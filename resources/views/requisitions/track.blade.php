@extends('layouts.dashboard')
@section('content')
@php
    $isStoresHead = (auth()->user()->isMainAdminOrSub() || strcasecmp(auth()->user()->department ?? '', 'Stores') === 0 || strcasecmp(auth()->user()->department ?? '', 'Store') === 0);
    if (!$isStoresHead) {
        $isBackup = (auth()->user()->isDepartmentHead() && in_array(auth()->user()->department, ['Human Resource Management Department', 'Welfare Department']));
        if ($isBackup) {
            $primaryOnline = \App\Models\User::where(function($q) {
                    $q->whereIn('role', ['Main Admin', 'Sub Main Admin'])
                      ->orWhere('role', 'Dept. Head (Stores)')
                      ->orWhereIn('department', ['Stores', 'Store']);
                })
                ->where('is_online', true)
                ->where('is_active', true)
                ->exists();
            if (!$primaryOnline) {
                $isStoresHead = true;
            }
        }
    }
@endphp
<style>
    :root {
        --store-orange: #22c55e;
        --store-orange-hover: #4c0519;
        --store-orange-light: rgba(34, 197, 94, 0.08);
        --store-indigo: #881337;
        --store-indigo-hover: #881337;
        --store-indigo-light: rgba(136, 19, 55, 0.08);
        --success-color: #881337;
        --warning-color: #881337;
        --danger-color: #ef4444;
        --info-color: #06b6d4;
        --text-muted: #64748b;
        --shadow-premium: 0 20px 40px -15px rgba(15, 23, 42, 0.05), 0 0 0 1px rgba(15, 23, 42, 0.03);
        --shadow-hover: 0 30px 60px -15px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(15, 23, 42, 0.05);
    }

    .track-stat-card {
        background: var(--bg-card);
        border-radius: 20px;
        border: 1px solid var(--border-color);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.15rem;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .track-stat-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary);
        box-shadow: var(--shadow-hover);
    }

    .track-stat-card.active-filter {
        border-color: var(--primary);
        background: var(--primary-glow);
        box-shadow: 0 10px 20px -10px rgba(136, 19, 55, 0.15);
    }

    .track-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
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

    /* Stepper Styling */
    .stepper-pipeline .stepper-dot {
        background: var(--bg-main);
        border: 2px solid var(--border-color);
        color: var(--text-muted);
    }
    .stepper-node.completed .stepper-dot {
        background: var(--success-color);
        border-color: var(--success-color);
        color: white;
        box-shadow: 0 0 8px rgba(136,19,55,0.25);
    }
    .stepper-node.active .stepper-dot {
        background: var(--store-orange);
        border-color: var(--store-orange);
        color: white;
        box-shadow: 0 0 10px rgba(34,197,94,0.35);
        animation: pulse-orange-dot 2s infinite;
    }
    .stepper-node.declined .stepper-dot {
        background: var(--danger-color);
        border-color: var(--danger-color);
        color: white;
        box-shadow: 0 0 8px rgba(239,68,68,0.25);
    }
    .stepper-node.bypassed .stepper-dot {
        background: #f1f5f9;
        border-color: var(--border-color);
        color: #94a3b8;
    }

    /* Stepper Labels */
    .stepper-node .stepper-label {
        color: var(--text-muted);
    }
    .stepper-node.completed .stepper-label {
        color: var(--success-color);
        font-weight: 800;
    }
    .stepper-node.active .stepper-label {
        color: var(--store-orange);
        font-weight: 800;
    }
    .stepper-node.declined .stepper-label {
        color: var(--danger-color);
        font-weight: 800;
    }
    .stepper-node.bypassed .stepper-label {
        color: #94a3b8;
    }

    /* Connectors */
    .stepper-connector.completed {
        background: var(--success-color) !important;
    }
    .stepper-connector.declined {
        background: var(--danger-color) !important;
    }
    .stepper-connector.active {
        background: linear-gradient(90deg, var(--success-color) 0%, var(--store-orange) 100%) !important;
    }
    .stepper-connector.bypassed {
        background: var(--border-color) !important;
        border-top: 2px dashed var(--border-color);
        height: 0 !important;
    }

    @keyframes pulse-orange-dot {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
        70% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    /* Modal Overlay & Details Drawer styles */
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

    .modal-box.urgent-priority { border-top: 6px solid #dc2626; }
    .modal-box.normal-priority { border-top: 6px solid #881337; }
    .modal-box.low-priority { border-top: 6px solid #64748b; }

    .profile-card {
        display: flex;
        align-items: center;
        gap: 14px;
        background: var(--bg-main);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.15rem;
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
        border: 1.5px solid rgba(136, 19, 55, 0.15);
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
        font-style: italic;
    }

    .item-decision-card {
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.15rem;
        background: var(--bg-card);
        transition: all 0.2s ease;
    }

    .item-card-panel {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: var(--bg-main);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        margin-top: 0.5rem;
    }

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
    }

    .table-item-qty { color: var(--store-orange); font-weight: 800; }
    .table-item-approved { color: var(--success-color); font-weight: 800; }
</style>

<div style="padding: 2rem;">
    {{-- Header Section --}}
    <div style="margin-bottom:2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="font-size:.7rem;font-weight:800;color:#881337;text-transform:uppercase;letter-spacing:.12em;margin-bottom:4px;">{{ strtoupper(auth()->user()->department ?? auth()->user()->role) }} · TRACKING CONTROL</div>
            <h1 style="font-size:1.75rem;font-weight:900;color:var(--text-main);letter-spacing:-.03em;margin:0;">Track Staff Requests</h1>
            <p style="font-size:.9rem;color:var(--text-muted);margin:6px 0 0;">{{ $isStoresHead ? 'Real-time tracking of staff requisitions and approval pipelines across all departments.' : 'Real-time tracking of store requisitions submitted by staff in your department.' }}</p>
        </div>
        <button onclick="window.location.reload()" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--text-main); border-radius:12px; border: 1px solid var(--border-color); background: var(--bg-card);">
            <i data-lucide="refresh-cw" style="width: 18px;"></i>
            Refresh Tracker
        </button>
    </div>

    {{-- Pipeline Status Metrics --}}
    <div id="tracking-stats-container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;margin-bottom:2rem;">
        
        <div class="track-stat-card" data-stage="awaiting_hod">
            <div style="width:40px;height:40px;background:rgba(136,19,55,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="user-check" style="width:20px;color:#881337;"></i></div>
            <div>
                <div id="stat-awaiting-hod" style="font-size:1.5rem;font-weight:950;color:var(--text-main); line-height: 1.1;">{{ $stats['awaiting_hod'] }}</div>
                <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-top:2px;">Awaiting HOD</div>
            </div>
        </div>

        <div class="track-stat-card" data-stage="awaiting_stores">
            <div style="width:40px;height:40px;background:rgba(136,19,55,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="shield-alert" style="width:20px;color:#881337;"></i></div>
            <div>
                <div id="stat-awaiting-stores" style="font-size:1.5rem;font-weight:950;color:var(--text-main); line-height: 1.1;">{{ $stats['awaiting_stores'] }}</div>
                <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-top:2px;">Awaiting Stores</div>
            </div>
        </div>

        <div class="track-stat-card" data-stage="ready_collection">
            <div style="width:40px;height:40px;background:rgba(6,182,212,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="package" style="width:20px;color:#06b6d4;"></i></div>
            <div>
                <div id="stat-ready-collection" style="font-size:1.5rem;font-weight:950;color:var(--text-main); line-height: 1.1;">{{ $stats['ready_collection'] }}</div>
                <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-top:2px;">Ready to Collect</div>
            </div>
        </div>

        <div class="track-stat-card" data-stage="collected">
            <div style="width:40px;height:40px;background:rgba(136,19,55,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="check-circle" style="width:20px;color:#881337;"></i></div>
            <div>
                <div id="stat-collected" style="font-size:1.5rem;font-weight:950;color:var(--text-main); line-height: 1.1;">{{ $stats['collected'] }}</div>
                <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-top:2px;">Collected</div>
            </div>
        </div>

        <div class="track-stat-card" data-stage="declined">
            <div style="width:40px;height:40px;background:rgba(239,68,68,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="x-circle" style="width:20px;color:#ef4444;"></i></div>
            <div>
                <div id="stat-declined" style="font-size:1.5rem;font-weight:950;color:var(--text-main); line-height: 1.1;">{{ $stats['declined'] }}</div>
                <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-top:2px;">Declined</div>
            </div>
        </div>

    </div>

    {{-- Filters Toolbar --}}
    <div class="filter-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:20px;padding:1.25rem 1.5rem;margin-bottom:2rem;box-shadow:var(--shadow-premium);">
        <div class="filter-header" style="display:flex;align-items:center;gap:6px;font-size:0.78rem;font-weight:900;color:var(--text-main);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:1rem;border-bottom:1px solid var(--border-color);padding-bottom:10px;">
            <i data-lucide="sliders-horizontal" style="width:14px;height:14px;color:#881337;"></i>
            <span>Filter Criteria</span>
        </div>
        <form method="GET" class="filter-row" id="filter-form" style="display:flex;gap:1rem;flex-wrap:wrap;">
            {{-- Search input --}}
            <div class="filter-field-wrapper" style="min-width:220px;flex:2;position:relative;display:flex;align-items:center;border:1px solid var(--border-color);border-radius:10px;padding:0 12px;background:var(--bg-main);">
                <i data-lucide="search" style="width:16px;height:16px;color:var(--text-muted);margin-right:8px;flex-shrink:0;"></i>
                <input type="text" name="search" id="filter-search" value="{{ request('search') }}" placeholder="Search by staff name, items, REQ ID..." style="border:none;background:transparent;width:100%;height:38px;font-size:0.85rem;color:var(--text-main);outline:none;">
            </div>

            {{-- Tracking Pipeline Stage Selector --}}
            <div class="filter-field-wrapper" style="min-width:180px;flex:1.5;display:flex;align-items:center;border:1px solid var(--border-color);border-radius:10px;padding:0 12px;background:var(--bg-main);">
                <i data-lucide="activity" style="width:16px;height:16px;color:var(--text-muted);margin-right:8px;"></i>
                <select name="tracking_status" class="filter-control" id="filter-tracking-status" style="border:none;background:transparent;width:100%;height:38px;font-size:0.85rem;color:var(--text-main);outline:none;cursor:pointer;">
                    <option value="">All Pipeline Stages</option>
                    <option value="awaiting_hod" {{ request('tracking_status')==='awaiting_hod'?'selected':'' }}>Awaiting HOD Approval</option>
                    <option value="awaiting_stores" {{ request('tracking_status')==='awaiting_stores'?'selected':'' }}>Awaiting Stores Review</option>
                    <option value="ready_collection" {{ request('tracking_status')==='ready_collection'?'selected':'' }}>Approved & Ready for Collection</option>
                    <option value="collected" {{ request('tracking_status')==='collected'?'selected':'' }}>Collected & Completed</option>
                    <option value="declined" {{ request('tracking_status')==='declined'?'selected':'' }}>Declined / Bypassed</option>
                </select>
            </div>

            {{-- Priority --}}
            <div class="filter-field-wrapper" style="min-width:130px;flex:1;display:flex;align-items:center;border:1px solid var(--border-color);border-radius:10px;padding:0 12px;background:var(--bg-main);">
                <i data-lucide="alert-circle" style="width:16px;height:16px;color:var(--text-muted);margin-right:8px;"></i>
                <select name="priority" class="filter-control" id="filter-priority" style="border:none;background:transparent;width:100%;height:38px;font-size:0.85rem;color:var(--text-main);outline:none;cursor:pointer;">
                    <option value="">All Priorities</option>
                    <option value="urgent" {{ request('priority')==='urgent'?'selected':'' }}>Urgent</option>
                    <option value="normal" {{ request('priority')==='normal'?'selected':'' }}>Normal</option>
                    <option value="low"    {{ request('priority')==='low'   ?'selected':'' }}>Low</option>
                </select>
            </div>

            {{-- Department (Stores Head/Admin only) --}}
            @if($isStoresHead)
            <div class="filter-field-wrapper" style="min-width:180px;flex:1.5;display:flex;align-items:center;border:1px solid var(--border-color);border-radius:10px;padding:0 12px;background:var(--bg-main);">
                <i data-lucide="building" style="width:16px;height:16px;color:var(--text-muted);margin-right:8px;"></i>
                <input type="text" name="department" id="filter-department" value="{{ request('department') }}" placeholder="Filter department..." style="border:none;background:transparent;width:100%;height:38px;font-size:0.85rem;color:var(--text-main);outline:none;">
            </div>
            @endif

            {{-- Date From --}}
            <div class="filter-field-wrapper" style="min-width:140px;flex:1;display:flex;align-items:center;border:1px solid var(--border-color);border-radius:10px;padding:0 12px;background:var(--bg-main);">
                <i data-lucide="calendar" style="width:16px;height:16px;color:var(--text-muted);margin-right:8px;"></i>
                <input type="date" name="date_from" id="filter-date-from" value="{{ request('date_from') }}" style="border:none;background:transparent;width:100%;height:38px;font-size:0.85rem;color:var(--text-main);outline:none;" title="Submission From Date">
            </div>

            {{-- Date To --}}
            <div class="filter-field-wrapper" style="min-width:140px;flex:1;display:flex;align-items:center;border:1px solid var(--border-color);border-radius:10px;padding:0 12px;background:var(--bg-main);">
                <i data-lucide="calendar" style="width:16px;height:16px;color:var(--text-muted);margin-right:8px;"></i>
                <input type="date" name="date_to" id="filter-date-to" value="{{ request('date_to') }}" style="border:none;background:transparent;width:100%;height:38px;font-size:0.85rem;color:var(--text-main);outline:none;" title="Submission To Date">
            </div>

            {{-- Clear Filters --}}
            <button type="button" id="filter-clear-btn" style="display:none;align-items:center;gap:6px;background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.2);padding:0 1.25rem;border-radius:10px;font-size:0.85rem;font-weight:800;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='#ef4444'; this.style.color='white';" onmouseout="this.style.background='rgba(239,68,68,0.1)'; this.style.color='#ef4444';">
                <i data-lucide="x-circle" style="width:16px;height:16px;"></i>
                <span>Clear Filters</span>
            </button>
        </form>
    </div>

    {{-- Grid Content Container --}}
    <div id="tracking-content-wrapper" style="position:relative;">
        
        {{-- Loading overlay --}}
        <div id="grid-loading" style="display:none;position:absolute;inset:0;background:rgba(var(--bg-card-rgb,255,255,255),.75);backdrop-filter:blur(2px);z-index:10;border-radius:20px;align-items:center;justify-content:center;">
            <div style="display:flex;align-items:center;gap:10px;padding:1rem 1.75rem;background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;box-shadow:0 8px 24px rgba(0,0,0,0.06);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#881337" stroke-width="2.5" style="animation:spin 0.7s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                <span style="font-size:0.82rem;font-weight:800;color:var(--text-muted);">Syncing pipeline...</span>
            </div>
        </div>

        {{-- Tracking grid --}}
        <div class="track-grid" id="track-grid">
            @include('requisitions._track_cards')
        </div>

        {{-- Pagination (dynamic) --}}
        <div id="req-pagination-wrap">
            @include('requisitions._req_pagination')
        </div>

    </div>

</div>

@push('scripts')
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
    #grid-loading { display:none; }
    #grid-loading.active { display:flex !important; }
</style>
<script>
(function() {
    const ROUTE = '{{ route('main-admin.track-requests') }}';
    const CSRF  = '{{ csrf_token() }}';
    let currentPage = 1;
    let debounceTimer = null;

    function getFilters() {
        return {
            search:          document.getElementById('filter-search')?.value || '',
            tracking_status: document.getElementById('filter-tracking-status')?.value || '',
            priority:        document.getElementById('filter-priority')?.value || '',
            department:      document.getElementById('filter-department')?.value || '',
            date_from:       document.getElementById('filter-date-from')?.value || '',
            date_to:         document.getElementById('filter-date-to')?.value || '',
            page:            currentPage,
        };
    }

    function hasActiveFilters(f) {
        return f.search || f.priority || f.department || f.date_from || f.date_to || f.tracking_status;
    }

    function showLoading(on) {
        const el = document.getElementById('grid-loading');
        if (el) el.classList.toggle('active', on);
    }

    function updateClearBtn(f) {
        const btn = document.getElementById('filter-clear-btn');
        if (btn) btn.style.display = hasActiveFilters(f) ? 'inline-flex' : 'none';
    }

    function updateStatsHighlight(status) {
        document.querySelectorAll('.track-stat-card').forEach(function(card) {
            if (card.dataset.stage === status) {
                card.classList.add('active-filter');
            } else {
                card.classList.remove('active-filter');
            }
        });
    }

    function fetchGrid(page) {
        currentPage = page || 1;
        const f = getFilters();
        updateClearBtn(f);
        updateStatsHighlight(f.tracking_status);

        const params = new URLSearchParams(f);
        history.replaceState(null, '', ROUTE + '?' + params.toString());

        showLoading(true);
        fetch(ROUTE + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            const grid = document.getElementById('track-grid');
            const pagWrap = document.getElementById('req-pagination-wrap');
            if (grid)    grid.innerHTML    = data.cards;
            if (pagWrap) pagWrap.innerHTML = data.pagination;

            // Update stats counts dynamically
            if (data.stats) {
                document.getElementById('stat-awaiting-hod').textContent = data.stats.awaiting_hod;
                document.getElementById('stat-awaiting-stores').textContent = data.stats.awaiting_stores;
                document.getElementById('stat-ready-collection').textContent = data.stats.ready_collection;
                document.getElementById('stat-collected').textContent = data.stats.collected;
                document.getElementById('stat-declined').textContent = data.stats.declined;
            }

            if (window.lucide) lucide.createIcons();
            bindPaginationClicks();
            showLoading(false);
        })
        .catch(() => showLoading(false));
    }

    function bindPaginationClicks() {
        document.querySelectorAll('.ajax-page-btn').forEach(function(btn) {
            btn.replaceWith(btn.cloneNode(true)); // remove old listeners
        });
        document.querySelectorAll('.ajax-page-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                if (!isNaN(page)) fetchGrid(page);
            });
        });
    }

    function wireFilters() {
        // Change on selector/date inputs
        ['filter-tracking-status', 'filter-priority', 'filter-date-from', 'filter-date-to'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', function() { fetchGrid(1); });
        });

        // Click on metrics card blocks
        document.querySelectorAll('.track-stat-card').forEach(function(card) {
            card.addEventListener('click', function() {
                const stage = this.dataset.stage;
                const selector = document.getElementById('filter-tracking-status');
                if (selector) {
                    if (selector.value === stage) {
                        selector.value = ''; // toggle off
                    } else {
                        selector.value = stage;
                    }
                    fetchGrid(1);
                }
            });
        });

        // Debounced search input
        const searchInput = document.getElementById('filter-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() { fetchGrid(1); }, 400);
            });
        }

        // Department debounced text input
        const deptInput = document.getElementById('filter-department');
        if (deptInput) {
            deptInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() { fetchGrid(1); }, 400);
            });
        }

        // Clear button
        const clearBtn = document.getElementById('filter-clear-btn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (document.getElementById('filter-search')) document.getElementById('filter-search').value = '';
                if (document.getElementById('filter-tracking-status')) document.getElementById('filter-tracking-status').value = '';
                if (document.getElementById('filter-priority')) document.getElementById('filter-priority').value = '';
                if (document.getElementById('filter-department')) document.getElementById('filter-department').value = '';
                if (document.getElementById('filter-date-from')) document.getElementById('filter-date-from').value = '';
                if (document.getElementById('filter-date-to')) document.getElementById('filter-date-to').value = '';
                fetchGrid(1);
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateClearBtn(getFilters());
        updateStatsHighlight(getFilters().tracking_status);
        wireFilters();
        bindPaginationClicks();
    });
})();
@endpush

{{-- Detail Modal Drawer overlay --}}
<div class="modal-overlay" id="reqModal" onclick="if(event.target===this)closeModal()">
    <div class="modal-box">
        <div style="padding:1.5rem 2rem;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:1rem;">
                <div style="width:44px;height:44px;background:rgba(136,19,55,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="compass" style="width:20px;color:#881337;"></i>
                </div>
                <div>
                    <h2 style="margin:0;font-size:1.1rem;font-weight:900;color:var(--text-main);">Requisition Tracking Status</h2>
                    <p id="modalSubtitle" style="margin:0;font-size:.8rem;color:var(--text-muted);font-weight:500;"></p>
                </div>
            </div>
            <button onclick="closeModal()" style="background:var(--bg-main);border:none;width:34px;height:34px;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="x" style="width:18px;color:var(--text-muted);"></i>
            </button>
        </div>
        <div class="modal-body" id="modalBody">
            <div style="text-align:center;padding:2rem;color:var(--text-muted);">Loading details...</div>
        </div>
        <div id="modalFooter" style="padding:1.25rem 2rem;border-top:1px solid var(--border-color);display:flex;justify-content:flex-end;gap:.75rem;flex-shrink:0;"></div>
    </div>
</div>

<script>
    let currentReqId = null;

    function closeModal() {
        const reqModal = document.getElementById('reqModal');
        if (reqModal) {
            reqModal.classList.remove('open');
        }
    }

    async function openRequisitionModal(id) {
        currentReqId = id;
        const reqModal = document.getElementById('reqModal');
        if (!reqModal) {
            console.error('Modal element #reqModal not found in DOM.');
            return;
        }
        reqModal.classList.add('open');
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

        let purposeText = data.purpose || '';
        let returnDateBannerHtml = '';
        const dateMatch = purposeText.match(/\[Expected Return Date:\s*([^\]]+)\]/i);
        if (dateMatch) {
            const rawDate = dateMatch[1].trim();
            let formattedDate = rawDate;
            try {
                const dateParts = rawDate.split('-');
                if (dateParts.length === 3 && dateParts[0].length === 4) {
                    const y = dateParts[0].substring(2);
                    const m = dateParts[1];
                    const d = dateParts[2];
                    formattedDate = `${d}/${m}/${y}`;
                } else {
                    const dateObj = new Date(rawDate);
                    if (!isNaN(dateObj.getTime())) {
                        const day = String(dateObj.getDate()).padStart(2, '0');
                        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                        const year = String(dateObj.getFullYear()).substring(2);
                        formattedDate = `${day}/${month}/${year}`;
                    }
                }
            } catch(e) {}
            returnDateBannerHtml = `
            <div style="background:rgba(136, 19, 55, 0.06); border:1px solid rgba(136, 19, 55, 0.25); border-radius:12px; padding:0.85rem 1.15rem; display:flex; align-items:center; gap:10px; color:#047857; font-weight:800; font-size:0.88rem; margin-top:0.5rem; margin-bottom:0.25rem; box-shadow:0 2px 8px rgba(136, 19, 55, 0.03);">
                <i data-lucide="calendar-clock" style="width:16px; height:16px; color:#047857; flex-shrink:0;"></i>
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
                        <i data-lucide="award" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:3px;"></i>${data.rank_or_title || 'No Rank/Title'}
                    </div>
                </div>
            </div>
            <div class="profile-card">
                <div class="profile-avatar" style="background:rgba(136, 19, 55, 0.08); color:#881337; border-color:rgba(136,19,55,0.15);"><i data-lucide="building" style="width:20px;height:20px;"></i></div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:2px;letter-spacing:0.04em;">Originating Department</div>
                    <div style="font-size:1.05rem;font-weight:900;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${data.department}">${data.department}</div>
                    <div style="font-size:.78rem;color:var(--text-muted);font-weight:600;margin-top:2px;">
                        <i data-lucide="calendar" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:3px;"></i>Submitted ${data.created_at}
                    </div>
                    ${data.origin_approved_by ? `
                    <div style="font-size:.7rem;color:#881337;font-weight:750;margin-top:4px;display:inline-flex;align-items:center;gap:3px;background:rgba(136,19,55,0.06);padding:2px 8px;border-radius:6px;border:1px solid rgba(136,19,55,0.15);width:fit-content;">
                        <i data-lucide="shield-check" style="width:11px;height:11px;"></i>Approved by HOD: ${data.origin_approved_by}
                    </div>
                    ` : ''}
                </div>
            </div>

            <div class="profile-card" style="grid-column: 1 / -1; display:flex; flex-direction:column; align-items:stretch; gap:0.75rem;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.04em;">Requisition Purpose</span>
                    <div class="stat-pill-group" style="display:flex; gap:0.5rem; align-items:center;">
                        <span class="stat-pill" style="background:${data.usage_type_badge.bg}; color:${data.usage_type_badge.color}; font-weight:800;"><i data-lucide="${data.usage_type === 'temporary' ? 'calendar' : 'package-check'}" style="width:12px;"></i> ${data.usage_type_badge.label}</span>
                    </div>
                </div>
                ${returnDateBannerHtml}
                <div class="purpose-quote">
                    ${purposeText}
                </div>
            </div>
        </div>
        `;

        // Render Items table details
        const rows = data.items.map(item => {
            const requested = parseFloat(item.quantity_requested) || 0;
            let approved = item.quantity_approved !== null ? parseFloat(item.quantity_approved) : null;
            const altApproved = item.alternative_quantity_approved !== null ? parseFloat(item.alternative_quantity_approved) : 0;
            
            if (approved === 0 && altApproved > 0 && (data.alternative_status === 'agreed' || data.alternative_status === 'proposed')) {
                approved = Math.max(0, requested - altApproved);
            }
            
            const totalApproved = approved !== null ? (approved + altApproved) : null;

            if (totalApproved !== null) {
                const pct = requested > 0 ? Math.min(Math.round((totalApproved / requested) * 100), 100) : 0;
                let fulfillBadgeBg = 'rgba(136, 19, 55, 0.1)';
                let fulfillBadgeColor = '#881337';
                let fulfillLabel = `${pct}% Fulfill`;

                if (totalApproved === 0) {
                    fulfillBadgeBg = 'rgba(239, 68, 68, 0.1)';
                    fulfillBadgeColor = '#ef4444';
                    fulfillLabel = 'Declined';
                } else if (totalApproved < requested) {
                    fulfillBadgeBg = 'rgba(136, 19, 55, 0.1)';
                    fulfillBadgeColor = '#881337';
                    fulfillLabel = `${pct}% Reduced`;
                }

                return `
                <div class="item-decision-card" style="border-bottom: 1px solid var(--border-color); padding: 1.25rem; display:flex; flex-direction:column; gap:0.75rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; width:100%;">
                        <div>
                            ${item.alternative_description ? `
                                <div style="font-size:.95rem;font-weight:800;color:var(--text-main); display:flex; align-items:center; gap:6px;">
                                    <span>${item.description}</span>
                                    <span style="font-size:0.75rem; font-weight:800; color:#881337;">(Approved: ${approved.toLocaleString()} ${item.unit})</span>
                                </div>
                                <div style="font-size:.92rem;font-weight:800;color:var(--store-orange); display:flex; align-items:center; gap:6px; margin-top:4px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:2px;"><path d="M16 3h5v5"/><path d="M8 21H3v-5"/><path d="M21 3 14 10"/><path d="M3 21 10 14"/></svg>
                                    Alternative: ${item.alternative_description}
                                    <span style="font-size:0.75rem; font-weight:800;">(Approved: ${altApproved.toLocaleString()} ${item.unit})</span>
                                </div>
                            ` : `
                                <div style="font-size:.95rem;font-weight:800;color:var(--text-main);">${item.description}</div>
                            `}
                            <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;margin-top:4px;">
                                Unit: ${item.unit}
                            </div>
                        </div>
                        <span class="pill" style="background:${fulfillBadgeBg}; color:${fulfillBadgeColor}; font-weight:800; font-size:0.68rem; padding:3px 10px; border-radius:99px;">${fulfillLabel}</span>
                    </div>

                    <div class="item-card-panel" style="background:var(--bg-main); border-radius:12px; padding:0.75rem 1rem; display:flex; align-items:center; justify-content:space-between; gap:1.5rem; border:1px solid var(--border-color); width:100%; box-sizing:border-box;">
                        <div style="flex:1;">
                            <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;">Requested</div>
                            <div style="font-size:1.05rem;font-weight:800;color:var(--text-main);margin-top:2px;">${requested.toLocaleString()}</div>
                        </div>

                        <div style="flex:1;">
                            <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;">Approved</div>
                            <div style="font-size:1.1rem;font-weight:900;color:${totalApproved === 0 ? '#ef4444' : '#881337'};margin-top:2px;">${totalApproved.toLocaleString()}</div>
                        </div>

                        <div style="flex:2;">
                            <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;margin-bottom:4px;">Fulfillment Progress</div>
                            <div style="background:rgba(0,0,0,0.05); height:6px; border-radius:10px; overflow:hidden; width:100%;">
                                <div style="height:100%; width: ${pct}%; background:${approved === 0 ? '#ef4444' : (approved < requested ? '#881337' : 'linear-gradient(90deg, #881337 0%, #881337 100%)')}; border-radius:10px;"></div>
                            </div>
                        </div>
                    </div>
                </div>`;
            }

            return `
            <div class="item-decision-card" style="padding: 1.25rem;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div style="font-size:.95rem;font-weight:800;color:var(--text-main);">${item.description}</div>
                        <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;margin-top:4px;">Unit: ${item.unit}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;">Requested Qty</div>
                        <div style="font-size:1.15rem;font-weight:900;color:var(--primary);margin-top:2px;">${requested.toLocaleString()}</div>
                    </div>
                </div>
            </div>`;
        }).join('');

        const itemRowsHtml = `
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:16px;overflow:hidden; display:flex; flex-direction:column; gap:8px;">
            ${rows}
            <div style="background:var(--bg-main); padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; border-radius:0 0 15px 15px;">
                <span style="font-size:0.75rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em;">Requisition Item Payload</span>
                <div class="stat-pill-group" style="display:flex; gap:6px;">
                    <span class="stat-pill"><i data-lucide="layers" style="width:12px;"></i> ${totalItemsCount} ${totalItemsCount === 1 ? 'Item' : 'Items'}</span>
                    <span class="stat-pill"><i data-lucide="hash" style="width:12px;"></i> Total Qty: ${totalQtyRequested.toLocaleString()}</span>
                </div>
            </div>
        </div>`;

        // Render Decision logs
        let decisionHtml = '';
        if (data.origin_admin_status !== 'pending') {
            let label = data.origin_admin_status === 'approved' ? 'HOD APPROVED' : 'HOD DECLINED';
            let color = data.origin_admin_status === 'approved' ? '#881337' : '#ef4444';
            decisionHtml += `
            <div style="background: ${color}04; border: 1.5px dashed ${color}25; border-radius: 16px; padding: 1.15rem; margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px dashed ${color}15; padding-bottom: 6px;">
                    <span style="font-size:0.72rem; font-weight:850; color:${color}; text-transform:uppercase; letter-spacing:0.04em; display:inline-flex; align-items:center; gap:4px;">
                        <i data-lucide="user-check" style="width:14px;height:14px;"></i> Department HOD Review Log
                    </span>
                    <span class="pill" style="background:${color}10; color:${color}; font-weight:850; font-size:0.65rem;">${label}</span>
                </div>
                ${data.decline_reason && data.origin_admin_status === 'declined' ? `
                <div style="font-size:0.86rem; color:var(--text-main); font-weight:700;">Decline Reason: <span style="font-weight:600; color:#7f1d1d;">${data.decline_reason}</span></div>
                ` : ''}
                ${data.admin_notes && data.origin_admin_status === 'approved' ? `
                <div style="font-size:0.84rem; color:var(--text-muted); font-style:italic;">"${data.admin_notes}"</div>
                ` : ''}
            </div>`;
        }

        if (data.main_admin_status !== 'pending') {
            let label = data.main_admin_status === 'approved' ? 'STORES APPROVED' : 'STORES DECLINED';
            let color = data.main_admin_status === 'approved' ? '#881337' : '#ef4444';
            
            if (data.alternative_status === 'agreed') {
                label = 'SUGGESTED QUANTITY AGREED';
                color = '#881337';
            } else if (data.alternative_status === 'declined') {
                label = 'SUGGESTED QUANTITY DECLINED';
                color = '#ef4444';
            }

            decisionHtml += `
            <div style="background: ${color}04; border: 1.5px dashed ${color}25; border-radius: 16px; padding: 1.15rem; margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px dashed ${color}15; padding-bottom: 6px;">
                    <span style="font-size:0.72rem; font-weight:850; color:${color}; text-transform:uppercase; letter-spacing:0.04em; display:inline-flex; align-items:center; gap:4px;">
                        <i data-lucide="shield-check" style="width:14px;height:14px;"></i> Store Head Review Log
                    </span>
                    <span class="pill" style="background:${color}10; color:${color}; font-weight:850; font-size:0.65rem;">${label}</span>
                </div>
                ${data.decline_reason && data.main_admin_status === 'declined' ? `
                <div style="font-size:0.86rem; color:var(--text-main); font-weight:700;">Decline Reason: <span style="font-weight:600; color:#7f1d1d;">${data.decline_reason}</span></div>
                ` : ''}
                ${data.admin_notes && data.main_admin_status === 'approved' ? `
                <div style="font-size:0.84rem; color:var(--text-muted); font-style:italic;">"${data.admin_notes}"</div>
                ` : ''}
            </div>`;
        }

        // Collector Information
        let collectorInfoHtml = '';
        if (data.collected_at) {
            collectorInfoHtml = `
            <div style="background:rgba(136,19,55,0.03); border:1.5px dashed rgba(136,19,55,0.25); border-radius:16px; padding:1.25rem; margin-top:1rem; display:flex; flex-direction:column; gap:0.75rem;">
                <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:1px dashed rgba(136,19,55,0.15); padding-bottom:8px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:34px; height:34px; background:rgba(136,19,55,0.08); color:#881337; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                            <i data-lucide="package-check" style="width:16px;"></i>
                        </div>
                        <div>
                            <h4 style="margin:0; font-size:0.82rem; font-weight:800; color:var(--text-main); text-transform:uppercase; letter-spacing:0.04em;">Physical Collection Voucher</h4>
                        </div>
                    </div>
                    <span class="pill" style="background:rgba(136,19,55,0.1); color:#881337; font-weight:800; font-size:0.7rem; padding:4px 10px;">COLLECTED</span>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.85rem;">
                    <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.6rem 0.85rem;">
                        <div style="font-size:0.65rem; font-weight:800; color:var(--text-muted); text-transform:uppercase;">Collector Name</div>
                        <div style="font-size:0.85rem; font-weight:900; color:var(--text-main);">${data.collector_name || 'N/A'}</div>
                    </div>
                    <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.6rem 0.85rem;">
                        <div style="font-size:0.65rem; font-weight:800; color:var(--text-muted); text-transform:uppercase;">Collector Contact</div>
                        <div style="font-size:0.85rem; font-weight:900; color:var(--text-main);">${data.collector_contact || 'N/A'}</div>
                    </div>
                    <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.6rem 0.85rem; grid-column: span 2;">
                        <div style="font-size:0.65rem; font-weight:800; color:var(--text-muted); text-transform:uppercase;">Collector Location</div>
                        <div style="font-size:0.85rem; font-weight:900; color:var(--text-main);">${data.collector_location || 'N/A'}</div>
                    </div>
                    <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.6rem 0.85rem;">
                        <div style="font-size:0.65rem; font-weight:800; color:var(--text-muted); text-transform:uppercase;">Confirmed By</div>
                        <div style="font-size:0.85rem; font-weight:900; color:var(--text-main);">${data.collected_by_name || 'Store Staff'}</div>
                    </div>
                    <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.6rem 0.85rem;">
                        <div style="font-size:0.65rem; font-weight:800; color:var(--text-muted); text-transform:uppercase;">Collection Time</div>
                        <div style="font-size:0.85rem; font-weight:900; color:var(--text-main);">${data.collected_at || 'N/A'}</div>
                    </div>
                </div>
            </div>`;
        } else if (['approved', 'partially_approved'].includes(data.status)) {
            collectorInfoHtml = `
            <div style="background:rgba(6, 182, 212, 0.03); border:1.5px dashed rgba(6, 182, 212, 0.25); border-radius:16px; padding:1rem 1.25rem; margin-top:1rem; display:flex; align-items:center; gap:10px; color:#0891b2; font-weight:850; font-size:0.82rem;">
                <i data-lucide="clock" style="width:16px; height:16px; color:#0891b2; flex-shrink:0;"></i>
                <span>Requisition approved by stores. Awaiting physical collection by staff.</span>
            </div>`;
        }

        document.getElementById('modalBody').innerHTML = `
        ${profileGridHtml}

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem; margin-top:1rem;">
            <h3 style="margin:0; font-size:0.9rem; font-weight:900; color:var(--text-main); display:flex; align-items:center; gap:6px;">
                <i data-lucide="list-checks" style="width:15px; color:#881337;"></i> Requisition Specification
            </h3>
        </div>

        ${itemRowsHtml}
        ${decisionHtml}
        ${collectorInfoHtml}
        `;

        let footerHtml = `
        <button onclick="closeModal()" style="background:var(--bg-main); color:var(--text-main); border:1.5px solid var(--border-color); padding:.75rem 1.5rem; border-radius:12px; font-weight:800; cursor:pointer; font-size:.88rem; transition:0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.03)'" onmouseout="this.style.background='var(--bg-main)'">
            Close Control
        </button>`;

        if (data.collected_at) {
            footerHtml = `
            <a href="{{ request()->getBasePath() }}/requisitions/receipt/${id}" target="_blank"
                style="background:rgba(136, 19, 55, 0.08); border: 1.5px solid rgba(136, 19, 55, 0.2); color: #881337; padding: .75rem 1.5rem; border-radius: 12px; font-weight: 800; cursor: pointer; font-size: .88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; margin-right: auto;" onmouseover="this.style.background='#881337'; this.style.color='white';" onmouseout="this.style.background='rgba(136, 19, 55, 0.08)'; this.style.color='#881337';">
                <i data-lucide="printer" style="width: 16px;"></i> Print Voucher Receipt
            </a>` + footerHtml;
        }

        document.getElementById('modalFooter').innerHTML = footerHtml;

        lucide.createIcons();
    }
</script>
@endsection
