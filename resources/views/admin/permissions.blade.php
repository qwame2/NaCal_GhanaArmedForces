
@extends('layouts.admin')

@section('title', 'Permissions & Registrations')

@section('content')
<div class="view-header" style="margin-bottom: 3rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 2rem; width: 100%;">
        <div style="flex: 1; min-width: 300px;">
            <div class="title-group">
                <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin-top: 0.5rem; max-width: 600px;">
                    Manage user permissions, clearances, and new registration requests.
                </p>
            </div>
        </div>

        <div style="flex: 0 1 450px;">
            <div class="search-vault" id="searchVaultWrap">
                <i data-lucide="search"></i>
                <input type="text" id="personnelSearch" placeholder="Filter users by name or identity..." oninput="filterPersonnel()">
                <div class="search-kicker">⌘ K</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Tab Navigation ── --}}
<div class="pager-tabs-wrap" style="flex-wrap: wrap;">
    <button class="pager-tab active" id="tab-store-officers" onclick="switchTab('store-officers')">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Store Officers
    </button>
    <button class="pager-tab" id="tab-requisitioners" onclick="switchTab('requisitioners')">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        Requisitioners
    </button>
    <button class="pager-tab" id="tab-dept-heads" onclick="switchTab('dept-heads')">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Other Dept. Heads
    </button>

    <button class="pager-tab" id="tab-registrations" onclick="switchTab('registrations')">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
        Registration Requests
        <span class="tab-badge" id="reg-badge" style="display: {{ $pendingUsers->count() > 0 ? 'inline-block' : 'none' }}">{{ $pendingUsers->count() }}</span>
    </button>
    <button class="pager-tab" id="tab-role-history" onclick="switchTab('role-history')">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
        Role &amp; Privilege History
    </button>
</div>

{{-- ── Panel: Store Officers ── --}}
<div id="panel-store-officers" class="pager-panel active">
    <div class="permissions-matrix-wrapper">
        <div class="matrix-table">
            <div class="m-header">
                <div class="col-id">Users</div>
                <div class="col-ctrl">Item Entry</div>
                <div class="col-ctrl">Confirm Collection</div>
                <div class="col-ctrl">Report Access</div>
                <div class="col-stat">Clearance Status</div>
            </div>

            <div class="m-body" id="storeOfficersBody">
                @forelse($storeOfficers as $user)
                <div class="m-row" data-user-id="{{ $user->id }}">
                    <div class="col-id">
                        <div class="m-avatar">
                            <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2364748b'><circle cx='12' cy='8' r='4'/><path d='M12 14c-4.42 0-8 3.58-8 8h16c0-4.42-3.58-8-8-8z'/></svg>" }}" alt="">
                            <span class="m-pulse {{ $user->is_active ? 'online' : 'offline' }}"></span>
                        </div>
                        <div class="m-identity">
                            <h4 class="m-name">{{ $user->name }}</h4>
                            <div class="m-handle" style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 2px;">
                                <span>@ {{ $user->username }}</span>
                                <span class="badge-role" style="font-size: 0.65rem; background: #eef2ff; color: #4338ca; padding: 2px 8px; border-radius: 6px; font-weight: 800; font-family: sans-serif; text-transform: uppercase; border: 1px solid rgba(67, 56, 202, 0.1);">
                                    @if($user->role === 'Main Admin')
                                        Head of Admin
                                    @elseif($user->role === 'Officer')
                                        Store Officer
                                    @elseif($user->role === 'Dept Head HR')
                                        Dept Head HR
                                    @elseif($user->role === 'Head of Welfare')
                                        Head of Welfare
                                    @else
                                        {{ $user->role }}
                                    @endif
                                </span>
                                @if($user->department)
                                <span class="badge-dept" style="font-size: 0.65rem; background: #f0fdf4; color: #15803d; padding: 2px 8px; border-radius: 6px; font-weight: 800; font-family: sans-serif; text-transform: uppercase; border: 1px solid rgba(21, 128, 61, 0.1); max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $user->department }}">
                                    {{ $user->department }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-ctrl">
                        <div class="toggle-group-wrap">
                            <label class="normal-toggle" title="Toggle Inventory Entry">
                                <input type="checkbox" onchange="toggleMatrixPermission(this, 'can_add_inventory')" {{ $user->can_add_inventory ? 'checked' : '' }}>
                                <div class="toggle-slider"></div>
                            </label>
                            <div class="toggle-text">
                                <span class="t-main">Add/Edit Items</span>
                                <span class="t-sub"></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-ctrl">
                        <div class="toggle-group-wrap">
                            <label class="normal-toggle" title="Toggle Logistics Operations">
                                <input type="checkbox" onchange="toggleMatrixPermission(this, 'can_operate_logistics')" {{ $user->can_operate_logistics ? 'checked' : '' }}>
                                <div class="toggle-slider"></div>
                            </label>
                            <div class="toggle-text">
                                <span class="t-main">Confirm Collection</span>
                                <span class="t-sub"></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-ctrl">
                        <div class="toggle-group-wrap">
                            <label class="normal-toggle" title="Toggle Analytics Access">
                                <input type="checkbox" onchange="toggleMatrixPermission(this, 'can_generate_reports')" {{ $user->can_generate_reports ? 'checked' : '' }}>
                                <div class="toggle-slider"></div>
                            </label>
                            <div class="toggle-text">
                                <span class="t-main">View Reports</span>
                                <span class="t-sub"></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-stat">
                        <div class="badge-status {{ $user->is_active ? 'authorized' : 'revoked' }}">
                            <i data-lucide="{{ $user->is_active ? 'shield-check' : 'shield-alert' }}"></i>
                            {{ $user->is_active ? 'AUTHORIZED' : 'SUSPENDED' }}
                        </div>
                    </div>
                </div>
                @empty
                <div style="padding: 3rem; text-align: center; color: #94a3b8; font-weight: 600; background: white;">
                    No store officers registered.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ── Panel: Requisitioners ── --}}
<div id="panel-requisitioners" class="pager-panel">
    <div class="permissions-matrix-wrapper">
        <div class="matrix-table">
            <div class="m-header">
                <div class="col-id">Personnel</div>
                <div class="col-req-ctrl">Make Requests</div>
                <div class="col-req-ctrl">Report Access</div>
                <div class="col-stat">Clearance Status</div>
            </div>

            <div class="m-body" id="requisitionersBody">
                @forelse($requisitioners as $user)
                <div class="m-row" data-user-id="{{ $user->id }}">
                    <div class="col-id">
                        <div class="m-avatar">
                            <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2364748b'><circle cx='12' cy='8' r='4'/><path d='M12 14c-4.42 0-8 3.58-8 8h16c0-4.42-3.58-8-8-8z'/></svg>" }}" alt="">
                            <span class="m-pulse {{ $user->is_active ? 'online' : 'offline' }}"></span>
                        </div>
                        <div class="m-identity">
                            <h4 class="m-name">{{ $user->name }}</h4>
                            <div class="m-handle" style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 2px;">
                                <span>@ {{ $user->username }}</span>
                                @if($user->department)
                                <span class="badge-dept" style="font-size: 0.65rem; background: #f0fdf4; color: #15803d; padding: 2px 8px; border-radius: 6px; font-weight: 800; font-family: sans-serif; text-transform: uppercase; border: 1px solid rgba(21, 128, 61, 0.1); max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $user->department }}">
                                    {{ $user->department }}
                                </span>
                                @endif
                                @if($user->sponsor)
                                <span style="font-size: 0.65rem; background: #f5f3ff; color: #6d28d9; padding: 2px 8px; border-radius: 6px; font-weight: 700; font-family: sans-serif; border: 1px solid rgba(109,40,217,0.1); white-space: nowrap;">
                                    via {{ $user->sponsor->name }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Make Requests toggle --}}
                    <div class="col-req-ctrl">
                        <div class="toggle-group-wrap">
                            <label class="normal-toggle" title="Allow or block this user from submitting requisition requests">
                                <input type="checkbox" onchange="toggleMatrixPermission(this, 'can_make_requisition')" {{ ($user->can_make_requisition ?? true) ? 'checked' : '' }}>
                                <div class="toggle-slider"></div>
                            </label>
                            <div class="toggle-text">
                                <span class="t-main">{{ ($user->can_make_requisition ?? true) ? 'Allowed' : 'Blocked' }}</span>
                                <span class="t-sub">Submit requests</span>
                            </div>
                        </div>
                    </div>

                    {{-- Report Access toggle --}}
                    <div class="col-req-ctrl">
                        <div class="toggle-group-wrap">
                            <label class="normal-toggle" title="Toggle Report Access">
                                <input type="checkbox" onchange="toggleMatrixPermission(this, 'can_generate_reports')" {{ $user->can_generate_reports ? 'checked' : '' }}>
                                <div class="toggle-slider"></div>
                            </label>
                            <div class="toggle-text">
                                <span class="t-main">View Reports</span>
                                <span class="t-sub"></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-stat">
                        <div class="badge-status {{ $user->is_active ? 'authorized' : 'revoked' }}">
                            <i data-lucide="{{ $user->is_active ? 'shield-check' : 'shield-alert' }}"></i>
                            {{ $user->is_active ? 'AUTHORIZED' : 'SUSPENDED' }}
                        </div>
                    </div>
                </div>
                @empty
                <div style="padding: 3rem; text-align: center; color: #94a3b8; font-weight: 600; background: white;">
                    No requisitioners registered.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ── Panel: Other Dept. Heads ── --}}
<div id="panel-dept-heads" class="pager-panel">
    <div class="permissions-matrix-wrapper">
        <div class="matrix-table">
            <div class="m-header">
                <div class="col-id">Personnel</div>
                <div class="col-req-ctrl">Approve Requests</div>
                <div class="col-req-ctrl">Report Access</div>
                <div class="col-stat">Clearance Status</div>
            </div>

            <div class="m-body" id="deptHeadsBody">
                @forelse($deptHeads as $user)
                <div class="m-row" data-user-id="{{ $user->id }}">
                    <div class="col-id">
                        <div class="m-avatar">
                            <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2364748b'><circle cx='12' cy='8' r='4'/><path d='M12 14c-4.42 0-8 3.58-8 8h16c0-4.42-3.58-8-8-8z'/></svg>" }}" alt="">
                            <span class="m-pulse {{ $user->is_active ? 'online' : 'offline' }}"></span>
                        </div>
                        <div class="m-identity">
                            <h4 class="m-name">{{ $user->name }}</h4>
                            <div class="m-handle" style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 2px;">
                                <span>@ {{ $user->username }}</span>
                                <span class="badge-role" style="font-size: 0.65rem; background: #eef2ff; color: #4338ca; padding: 2px 8px; border-radius: 6px; font-weight: 800; font-family: sans-serif; text-transform: uppercase; border: 1px solid rgba(67, 56, 202, 0.1);">
                                    @if($user->role === 'Main Admin')
                                        Head of Admin
                                    @else
                                        {{ $user->role }}
                                    @endif
                                </span>
                                @if($user->department)
                                <span class="badge-dept" style="font-size: 0.65rem; background: #f0fdf4; color: #15803d; padding: 2px 8px; border-radius: 6px; font-weight: 800; font-family: sans-serif; text-transform: uppercase; border: 1px solid rgba(21, 128, 61, 0.1); max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $user->department }}">
                                    {{ $user->department }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Approve Requests toggle --}}
                    <div class="col-req-ctrl">
                        <div class="toggle-group-wrap">
                            <label class="normal-toggle" title="Allow or block this department head from approving requisition requests from their staff">
                                <input type="checkbox" onchange="toggleMatrixPermission(this, 'can_approve_requisition')" {{ ($user->can_approve_requisition ?? true) ? 'checked' : '' }}>
                                <div class="toggle-slider"></div>
                            </label>
                            <div class="toggle-text">
                                <span class="t-main">{{ ($user->can_approve_requisition ?? true) ? 'Allowed' : 'Blocked' }}</span>
                                <span class="t-sub">Approve requests</span>
                            </div>
                        </div>
                    </div>

                    {{-- Report Access toggle --}}
                    <div class="col-req-ctrl">
                        <div class="toggle-group-wrap">
                            <label class="normal-toggle" title="Toggle Report Access">
                                <input type="checkbox" onchange="toggleMatrixPermission(this, 'can_generate_reports')" {{ $user->can_generate_reports ? 'checked' : '' }}>
                                <div class="toggle-slider"></div>
                            </label>
                            <div class="toggle-text">
                                <span class="t-main">View Reports</span>
                                <span class="t-sub"></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-stat">
                        <div class="badge-status {{ $user->is_active ? 'authorized' : 'revoked' }}">
                            <i data-lucide="{{ $user->is_active ? 'shield-check' : 'shield-alert' }}"></i>
                            {{ $user->is_active ? 'AUTHORIZED' : 'SUSPENDED' }}
                        </div>
                    </div>
                </div>
                @empty
                <div style="padding: 3rem; text-align: center; color: #94a3b8; font-weight: 600; background: white;">
                    No other department heads registered.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ── Panel: Registration Requests ── --}}
<div id="panel-registrations" class="pager-panel">
    @include('admin.partials.pending_registrations')
</div>

{{-- ── Panel: Role & Privilege History ── --}}
<div id="panel-role-history" class="pager-panel">
    <div class="permissions-matrix-wrapper" style="background: white; border-radius: 32px; box-shadow: 0 20px 50px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.03); overflow: hidden; margin-bottom: 4rem; padding: 2rem;">
        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
            Role &amp; Privilege Modifications
        </h3>
        
        @php
            $embeddedRoleHistory = \App\Models\UserRoleHistory::with(['user', 'changer'])->orderBy('created_at', 'desc')->take(100)->get();
        @endphp

        @if($embeddedRoleHistory->isEmpty())
            <div style="padding: 3rem; text-align: center; color: #94a3b8; font-weight: 600; background: white;">
                No role or privilege updates recorded.
            </div>
        @else
            <div class="table-responsive" style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #edf2f7;">
                            <th style="padding: 1rem 1.25rem; font-weight: 800; color: #64748b; font-size: 0.72rem; text-transform: uppercase;">Date</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 800; color: #64748b; font-size: 0.72rem; text-transform: uppercase;">Staff Name</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 800; color: #64748b; font-size: 0.72rem; text-transform: uppercase;">Action</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 800; color: #64748b; font-size: 0.72rem; text-transform: uppercase;">Role Evolution</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 800; color: #64748b; font-size: 0.72rem; text-transform: uppercase;">Privilege Details</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 800; color: #64748b; font-size: 0.72rem; text-transform: uppercase;">Changed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($embeddedRoleHistory as $historyRecord)
                            <tr style="border-bottom: 1px solid #edf2f7; transition: background 0.15s;" onmouseover="this.style.background='#fafcff'" onmouseout="this.style.background=''">
                                <td style="padding: 1rem 1.25rem; color: var(--text-muted); font-size: 0.78rem; font-weight: 700; white-space: nowrap;">
                                    {{ $historyRecord->created_at->format('d/m/y H:i') }}
                                </td>
                                <td style="padding: 1rem 1.25rem; font-weight: 800; color: var(--text-main);">
                                    {{ $historyRecord->user->name ?? 'Deleted User' }}
                                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 500; font-family: monospace; margin-top: 1px;">
                                        @ @if($historyRecord->user){{ $historyRecord->user->username }}@else{{ 'deleted' }}@endif
                                    </div>
                                </td>
                                <td style="padding: 1rem 1.25rem;">
                                    <span style="display: inline-flex; align-items: center; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase;
                                        @if($historyRecord->action === 'created')
                                            background: #ecfdf5; color: #059669;
                                        @elseif($historyRecord->action === 'role_changed')
                                            background: #fdf2f8; color: #db2777;
                                        @elseif($historyRecord->action === 'status_changed')
                                            background: #fffbeb; color: #d97706;
                                        @else
                                            background: #eff6ff; color: #2563eb;
                                        @endif">
                                        {{ str_replace('_', ' ', $historyRecord->action) }}
                                    </span>
                                </td>
                                <td style="padding: 1rem 1.25rem; font-weight: 700;">
                                    @if($historyRecord->action === 'created')
                                        <span style="color: #059669;">{{ $historyRecord->new_role }}</span>
                                    @else
                                        @if($historyRecord->old_role != $historyRecord->new_role)
                                            <span style="color: var(--text-muted); text-decoration: line-through; font-size: 0.78rem; font-weight: 500;">{{ $historyRecord->old_role }}</span>
                                            <span style="color: var(--primary); margin: 0 4px;">&rarr;</span>
                                            <span style="color: var(--text-main);">{{ $historyRecord->new_role }}</span>
                                        @else
                                            <span style="color: var(--text-muted); font-size: 0.78rem;">{{ $historyRecord->new_role }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td style="padding: 1rem 1.25rem;">
                                    @if($historyRecord->new_permissions)
                                        <div style="display: flex; flex-direction: column; gap: 3px; max-width: 220px;">
                                            @foreach(['can_add_inventory' => 'Inventory Entry', 'can_operate_logistics' => 'Confirm Collection', 'can_generate_reports' => 'View Reports', 'can_make_requisition' => 'Make Requests', 'can_approve_requisition' => 'Approve Requests'] as $key => $label)
                                                @php
                                                    $oldVal = $historyRecord->old_permissions[$key] ?? false;
                                                    $newVal = $historyRecord->new_permissions[$key] ?? false;
                                                @endphp
                                                @if($historyRecord->action === 'created' || $oldVal != $newVal)
                                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.7rem; padding: 1px 4px; background: #f8fafc; border-radius: 4px; border: 1px solid #f1f5f9;">
                                                        <span style="font-weight: 700; color: #475569;">{{ $label }}</span>
                                                        @if($historyRecord->action === 'created')
                                                            <span style="font-weight: 800; color: {{ $newVal ? '#10b981' : '#dc2626' }};">{{ $newVal ? 'Allowed' : 'Blocked' }}</span>
                                                        @else
                                                            <span style="font-weight: 800;">
                                                                <span style="color: {{ $oldVal ? '#10b981' : '#dc2626' }}; text-decoration: line-through; opacity: 0.6;">{{ $oldVal ? 'Allowed' : 'Blocked' }}</span>
                                                                <span style="color: #64748b; margin: 0 1px;">&rarr;</span>
                                                                <span style="color: {{ $newVal ? '#10b981' : '#dc2626' }};">{{ $newVal ? 'Allowed' : 'Blocked' }}</span>
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="padding: 1rem 1.25rem; font-weight: 700; color: var(--text-main);">
                                    {{ $historyRecord->changer->name ?? 'System' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<style>
    .swal-cancel-dark {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        border: 1px solid #cbd5e1 !important;
        font-weight: 700 !important;
        transition: all 0.2s ease !important;
    }
    .swal-cancel-dark:hover {
        background-color: #e2e8f0 !important;
        color: #0f172a !important;
    }

    /* ── Search Vault ── */
    .search-vault {
        position: relative;
        display: flex;
        align-items: center;
        background: white;
        border: 2px solid #f1f5f9;
        border-radius: 20px;
        padding: 0.5rem 1.25rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
    }
    .search-vault:focus-within {
        border-color: #4f46e5;
        box-shadow: 0 10px 30px rgba(79, 70, 229, 0.1);
        transform: translateY(-2px);
    }
    .search-vault i { color: #4f46e5; opacity: 0.6; margin-right: 1rem; width: 20px; }
    .search-vault input { border: none; outline: none; padding: 0.75rem 0; font-size: 0.95rem; font-weight: 600; color: #0f172a; width: 100%; background: transparent; }
    .search-vault input::placeholder { color: #94a3b8; font-weight: 500; }
    .search-kicker { font-size: 0.7rem; font-weight: 800; color: #64748b; background: #f1f5f9; padding: 4px 8px; border-radius: 8px; white-space: nowrap; border: 1px solid #e2e8f0; }

    /* ── Pager Tabs ── */
    .pager-tabs-wrap {
        display: flex;
        gap: 6px;
        background: white;
        padding: 6px;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        margin-bottom: 2rem;
        width: fit-content;
    }
    .pager-tab {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.65rem 1.25rem;
        border-radius: 14px;
        border: none;
        background: transparent;
        font-family: inherit;
        font-size: 0.88rem;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
    }
    .pager-tab svg { opacity: 0.7; flex-shrink: 0; }
    .pager-tab:hover { background: #f8fafc; color: #4f46e5; }
    .pager-tab.active { background: #4f46e5; color: white; box-shadow: 0 4px 14px rgba(79,70,229,0.25); }
    .pager-tab.active svg { opacity: 1; }
    .tab-badge {
        background: #ef4444;
        color: white;
        font-size: 0.65rem;
        font-weight: 900;
        padding: 2px 7px;
        border-radius: 99px;
        margin-left: 2px;
        line-height: 1.4;
    }
    .pager-tab.active .tab-badge { background: rgba(255,255,255,0.25); }

    /* ── Panel visibility ── */
    .pager-panel { display: none; animation: fadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1); }
    .pager-panel.active { display: block; }

    /* ── Matrix Table ── */
    .permissions-matrix-wrapper {
        background: white;
        border-radius: 32px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.03);
        overflow: hidden;
        margin-bottom: 4rem;
    }
    .matrix-table { display: flex; flex-direction: column; width: 100%; min-width: 900px; }
    .m-header { display: flex; align-items: center; background: #f8fafc; padding: 1.5rem 2rem; border-bottom: 1px solid #f1f5f9; }
    .m-header > div { font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; }
    .m-row { display: flex; align-items: center; padding: 1.25rem 2rem; border-bottom: 1px solid #f8fafc; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: white; }
    .m-row:hover { background: #fdfeff; box-shadow: inset 4px 0 0 var(--primary); }
    .m-row:last-child { border-bottom: none; }
    .col-id { flex: 0 0 350px; display: flex; align-items: center; gap: 1.25rem; }
    .col-ctrl { flex: 1; display: flex; justify-content: flex-start; align-items: center; }
    .col-req-ctrl { flex: 0 0 200px; display: flex; justify-content: flex-start; align-items: center; }
    .col-stat { flex: 0 0 160px; display: flex; justify-content: flex-end; }
    .m-avatar { position: relative; width: 48px; height: 48px; border-radius: 16px; padding: 3px; background: linear-gradient(135deg, #e2e8f0, #f8fafc); flex-shrink: 0; }
    .m-avatar img { width: 100%; height: 100%; border-radius: 12px; object-fit: cover; }
    .m-pulse { position: absolute; bottom: -2px; right: -2px; width: 14px; height: 14px; border-radius: 50%; border: 3px solid white; }
    .m-pulse.online { background: #10b981; animation: soft-pulse 2s infinite; }
    .m-pulse.offline { background: #cbd5e1; }
    @keyframes soft-pulse { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); } 70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }
    .m-identity { display: flex; flex-direction: column; gap: 2px; }
    .m-name { margin: 0; font-size: 0.95rem; font-weight: 850; color: var(--text-heading); letter-spacing: -0.01em; }
    .m-handle { font-size: 0.75rem; color: var(--accent); font-weight: 700; font-family: 'JetBrains Mono', monospace; }
    .toggle-group-wrap { display: flex; align-items: center; gap: 0.75rem; }
    .toggle-text { display: flex; flex-direction: column; justify-content: center; }
    .t-main { font-size: 0.75rem; font-weight: 800; color: var(--text-heading); line-height: 1.1; }
    .t-sub { font-size: 0.65rem; color: var(--text-muted); font-weight: 600; }
    .normal-toggle { position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer; }
    .normal-toggle input { opacity: 0; width: 0; height: 0; }
    .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .25s ease; border-radius: 24px; }
    .toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .25s ease; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
    .normal-toggle input:checked + .toggle-slider { background-color: var(--primary); }
    .normal-toggle input:checked + .toggle-slider:before { transform: translateX(20px); }
    .badge-status { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 12px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.05em; }
    .badge-status.authorized { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
    .badge-status.revoked { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
    .badge-status i { width: 14px; height: 14px; }
    .syncing-row { opacity: 0.5; pointer-events: none; background: #f8fafc !important; }

    /* ── Registration Requests ── */
    .reg-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 4rem;
    }

    .reg-card {
        background: white;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        gap: 2rem;
        flex-wrap: wrap;
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        animation: fadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reg-card:hover {
        border-color: rgba(79,70,229,0.2);
        box-shadow: 0 8px 28px rgba(79,70,229,0.08);
        transform: translateY(-2px);
    }

    .reg-identity {
        display: flex;
        align-items: center;
        gap: 1.1rem;
        flex: 0 0 280px;
    }

    .reg-avatar {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        font-weight: 900;
        flex-shrink: 0;
        box-shadow: 0 6px 16px rgba(99,102,241,0.25);
    }

    .reg-name { font-size: 0.97rem; font-weight: 850; color: #0f172a; letter-spacing: -0.01em; }
    .reg-username { font-size: 0.75rem; color: #4f46e5; font-weight: 700; font-family: 'JetBrains Mono', monospace; margin-top: 2px; }
    .reg-time { font-size: 0.72rem; color: #94a3b8; font-weight: 600; margin-top: 4px; }

    .reg-details {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        flex: 1;
        min-width: 0;
    }

    .reg-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 99px;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .reg-pill.role { background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; }
    .reg-pill.dept { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; max-width: 240px; overflow: hidden; text-overflow: ellipsis; }
    .reg-pill.rank { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

    .reg-actions {
        display: flex;
        gap: 0.6rem;
        flex-shrink: 0;
    }

    .reg-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.55rem 1.2rem;
        border-radius: 12px;
        border: none;
        font-family: inherit;
        font-size: 0.82rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.25s ease;
        letter-spacing: 0.01em;
    }
    .reg-btn.approve {
        background: #10b981;
        color: white;
        box-shadow: 0 4px 12px rgba(16,185,129,0.25);
    }
    .reg-btn.approve:hover {
        background: #059669;
        box-shadow: 0 6px 20px rgba(16,185,129,0.35);
        transform: translateY(-1px);
    }
    .reg-btn.decline {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .reg-btn.decline:hover {
        background: #ef4444;
        color: white;
        border-color: #ef4444;
        box-shadow: 0 6px 20px rgba(239,68,68,0.25);
        transform: translateY(-1px);
    }

    /* ── Empty State ── */
    .reg-empty-state {
        text-align: center;
        padding: 5rem 2rem;
        background: white;
        border-radius: 32px;
        border: 1px solid #f1f5f9;
        margin-bottom: 4rem;
    }
    .reg-empty-icon {
        width: 80px;
        height: 80px;
        background: #f0fdf4;
        color: #10b981;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }
    .reg-empty-state h3 { font-size: 1.3rem; font-weight: 900; color: #0f172a; margin: 0 0 0.5rem; }
    .reg-empty-state p { color: #94a3b8; font-weight: 500; margin: 0; }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @media (max-width: 1024px) {
        .permissions-matrix-wrapper { overflow-x: auto; }
    }
    @media (max-width: 768px) {
        .reg-card { flex-direction: column; align-items: flex-start; }
        .reg-identity { flex: none; width: 100%; }
        .reg-actions { width: 100%; }
        .reg-btn { flex: 1; justify-content: center; }
    }
</style>

<script>
    /* ── Tab Switcher ── */
    function switchTab(tab) {
        document.querySelectorAll('.pager-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.pager-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        document.getElementById('panel-' + tab).classList.add('active');

        // Show/hide search vault (only relevant for matrix tabs)
        const sv = document.getElementById('searchVaultWrap');
        if (sv) sv.style.display = (tab !== 'registrations') ? '' : 'none';
    }

    /* ── Personnel Filter ── */
    function filterPersonnel() {
        const term = document.getElementById('personnelSearch').value.toLowerCase();
        document.querySelectorAll('.m-row').forEach(row => {
            const name     = row.querySelector('.m-name').textContent.toLowerCase();
            const username = row.querySelector('.m-handle').textContent.toLowerCase();
            row.style.display = (name.includes(term) || username.includes(term)) ? 'flex' : 'none';
        });
    }

    /* ── Keyboard Shortcut ── */
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            if (document.activeElement !== document.getElementById('personnelSearch')) {
                e.preventDefault();
                switchTab('store-officers');
                document.getElementById('personnelSearch').focus();
            }
        }
    });

    /* ── Permission Toggle (AJAX) ── */
    function toggleMatrixPermission(checkbox, permission) {
        const row    = checkbox.closest('.m-row');
        const userId = row.getAttribute('data-user-id');
        const value  = checkbox.checked ? 1 : 0;
        row.classList.add('syncing-row');

        fetch('{{ route("admin.permissions.update", [], false) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ user_id: userId, permission: permission, value: value })
        })
        .then(r => r.json())
        .then(data => {
            row.classList.remove('syncing-row');
            if (!data.success) {
                checkbox.checked = !checkbox.checked;
                alert('Failed to update permission: ' + data.message);
            } else if (permission === 'can_make_requisition' || permission === 'can_approve_requisition') {
                // Live-update the Allowed/Blocked label text
                const label = checkbox.closest('.toggle-group-wrap')?.querySelector('.t-main');
                if (label) label.textContent = checkbox.checked ? 'Allowed' : 'Blocked';
            }
        })
        .catch(() => {
            row.classList.remove('syncing-row');
            checkbox.checked = !checkbox.checked;
            alert('A system error occurred.');
        });
    }

    /* ── Decline Confirmation ── */
    function confirmDecline(btn) {
        const form = btn.closest('.decline-form');
        Swal.fire({
            title: '<span style="font-weight:900;color:#0f172a;">Decline Registration?</span>',
            html: '<p style="color:#64748b;font-size:0.9rem;margin:0;">This will permanently delete the registration record. The person will need to re-register if this was a mistake.</p>',
            icon: 'warning',
            iconColor: '#ef4444',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#f1f5f9',
            confirmButtonText: 'Yes, Decline',
            cancelButtonText: 'Cancel',
            background: 'white',
            customClass: {
                cancelButton: 'swal-cancel-dark'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    const submitEvent = new Event('submit', { cancelable: true, bubbles: true });
                    form.dispatchEvent(submitEvent);
                    if (!submitEvent.defaultPrevented) {
                        form.submit();
                    }
                }
            }
        });
    }

    // Intercept Approve and Reject form submissions to avoid page blinking
    document.addEventListener('submit', async function(e) {
        const form = e.target;
        if (form.action && (form.action.includes('approve-registration') || form.action.includes('reject-registration'))) {
            e.preventDefault();

            // Disable buttons inside the card to prevent double clicks
            const card = form.closest('.reg-card');
            const buttons = card ? card.querySelectorAll('.reg-btn') : null;
            if (buttons) {
                buttons.forEach(btn => btn.disabled = true);
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Swap out the Requisitioners matrix body
                const newReqs = doc.getElementById('requisitionersBody');
                const currentReqs = document.getElementById('requisitionersBody');
                if (newReqs && currentReqs) {
                    currentReqs.innerHTML = newReqs.innerHTML;
                }

                // Swap out the Dept Heads matrix body
                const newDept = doc.getElementById('deptHeadsBody');
                const currentDept = document.getElementById('deptHeadsBody');
                if (newDept && currentDept) {
                    currentDept.innerHTML = newDept.innerHTML;
                }

                // Swap out the pending registrations list
                const newRegs = doc.getElementById('panel-registrations');
                const currentRegs = document.getElementById('panel-registrations');
                if (newRegs && currentRegs) {
                    currentRegs.innerHTML = newRegs.innerHTML;
                }

                // Update tab badges
                const tabKeys = ['reg-badge', 'sidebar-badge-registrations'];
                tabKeys.forEach(key => {
                    const newEl = doc.getElementById(key);
                    const oldEl = document.getElementById(key);
                    if (oldEl && newEl) {
                        oldEl.style.display = newEl.style.display;
                        oldEl.textContent = newEl.textContent;
                    } else if (oldEl && !newEl) {
                        oldEl.style.display = 'none';
                    }
                });

                if (window.lucide) {
                    window.lucide.createIcons();
                }

                // Show dynamic success toast
                const isApprove = form.action.includes('approve-registration');
                const userName = card ? card.querySelector('.reg-name')?.textContent || 'User' : 'User';
                const message = isApprove 
                    ? `Registration approved — ${userName} is now active.` 
                    : `Registration request for ${userName} has been declined.`;

                if (typeof showToast === 'function') {
                    showToast('Success', message, 'success');
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: message,
                        confirmButtonColor: '#4f46e5'
                    });
                }
            } catch (err) {
                console.error(err);
                if (typeof showToast === 'function') {
                    showToast('Error', 'An error occurred while processing the request.', 'error');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'System Error',
                        text: 'An error occurred while processing the request.',
                        confirmButtonColor: '#4f46e5'
                    });
                }
                if (buttons) {
                    buttons.forEach(btn => btn.disabled = false);
                }
            }
        }
    });

    function pollPendingRegistrations() {
        fetch('{{ route("api.admin.pending-registrations", [], false) }}')
            .then(res => {
                const contentType = res.headers.get("content-type");
                if (res.status === 200 && contentType && contentType.indexOf("application/json") !== -1) {
                    return res.json();
                }
                return null;
            })
            .then(data => {
                if (!data) return;
                
                const panel = document.getElementById('panel-registrations');
                if (panel) {
                    const temp = document.createElement('div');
                    temp.innerHTML = data.html;
                    const currentIds = Array.from(panel.querySelectorAll('.reg-card')).map(card => card.id).join(',');
                    const newIds = Array.from(temp.querySelectorAll('.reg-card')).map(card => card.id).join(',');
                    
                    if (currentIds !== newIds) {
                        panel.innerHTML = data.html;
                        if (window.lucide) {
                            lucide.createIcons();
                        }
                    }
                }
                
                const badge = document.getElementById('reg-badge');
                if (badge) {
                    if (data.count > 0) {
                        badge.style.display = 'inline-block';
                        badge.textContent = data.count;
                    } else {
                        badge.style.display = 'none';
                    }
                }

                const sidebarBadge = document.getElementById('sidebar-badge-registrations');
                if (sidebarBadge) {
                    if (data.count > 0) {
                        sidebarBadge.style.display = 'inline-block';
                        sidebarBadge.textContent = data.count;
                    } else {
                        sidebarBadge.style.display = 'none';
                    }
                }
            })
            .catch(() => {});
    }

    // Start polling every 10 seconds
    setInterval(pollPendingRegistrations, 10000);

    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) lucide.createIcons();

        // Auto-open tab from server session (after approve/decline redirect)
        const serverTab = '{{ session('open_tab') }}';
        if (serverTab === 'registrations') {
            switchTab('registrations');
        } else {
            // Fallback: URL hash
            const hash = window.location.hash;
            if (hash === '#registrations') switchTab('registrations');
        }

        // Show server-side flash messages as toasts
        @if(session('success'))
            if (typeof showToast === 'function') {
                showToast('{{ addslashes(session('success')) }}', 'success');
            }
        @endif
        @if(session('error'))
            if (typeof showToast === 'function') {
                showToast('{{ addslashes(session('error')) }}', 'error');
            }
        @endif
    });
</script>
@endsection
