
@extends('layouts.admin')

@section('title', 'Permissions & Registrations')

@section('content')
@if(auth()->user()->isDelegatedApprover())
    <!-- Breadcrumb / Back Button for Delegated Approvers -->
    <div style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;">
        <a href="{{ route('dashboard') }}" style="display: inline-flex; align-items: center; gap: 6px; color: #059669; text-decoration: none; font-weight: 800; font-size: 0.82rem; padding: 8px 16px; background: rgba(5,150,105,0.08); border: 1.5px dashed rgba(5,150,105,0.25); border-radius: 12px; transition: all 0.25s;" onmouseover="this.style.background='rgba(5,150,105,0.15)'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='rgba(5,150,105,0.08)'; this.style.transform='translateY(0)'">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            <span>Back to Store Officer Panel</span>
        </a>
    </div>
@endif
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

    <button class="pager-tab" id="tab-all-users" onclick="switchTab('all-users')">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="19" cy="8" r="3"/><path d="M22 14a5 5 0 0 0-5-5"/></svg>
        All Users
        <span class="tab-badge badge-neutral" style="display: inline-block;">{{ $allUsers->count() }}</span>
    </button>
</div>

{{-- ── Panel: Store Officers ── --}}
<div id="panel-store-officers" class="pager-panel active">
    @php
        $otpExpiresAt = \App\Models\Setting::get('delegation_otp_expires_at');
        $delegatedApproverId = \App\Models\Setting::get('delegated_approver_id');
        $delegatedUser = $delegatedApproverId ? \App\Models\User::find($delegatedApproverId) : null;
        $isDelegationActive = $delegatedUser && $delegatedUser->isDelegatedApprover();
    @endphp

    <div class="permissions-matrix-wrapper">
        <div class="matrix-table">
            <div class="m-header">
                <div class="col-id">Users</div>
                <div class="col-ctrl">Item Entry</div>
                <div class="col-ctrl">Confirm Collection</div>
                <div class="col-ctrl">Report Access</div>
                @if(auth()->user()->is_admin && auth()->user()->role === 'Head of Stores')
                <div class="col-ctrl" style="color: #059669; font-weight: 800;">Delegation</div>
                @endif
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

                    @if(auth()->user()->is_admin && auth()->user()->role === 'Head of Stores')
                    @php
                        $isUserDelegated = (int)$user->id === (int)$delegatedApproverId && $user->isDelegatedApprover();
                    @endphp
                    <div class="col-ctrl">
                        <div class="toggle-group-wrap">
                            <label class="normal-toggle" title="Toggle Delegation Authority">
                                <input type="checkbox" 
                                       class="delegation-toggle-checkbox"
                                       data-user-id="{{ $user->id }}"
                                       data-user-name="{{ $user->name }}"
                                       onchange="toggleUserDelegation(this)" 
                                       {{ $isUserDelegated ? 'checked' : '' }}>
                                <div class="toggle-slider" style="{{ $isUserDelegated ? 'background-color: #059669;' : '' }}"></div>
                            </label>
                            <div class="toggle-text">
                                <span class="t-main" style="{{ $isUserDelegated ? 'color: #059669;' : '' }}">
                                    {{ $isUserDelegated ? 'Delegated' : 'Off' }}
                                </span>
                                @if($isUserDelegated && $otpExpiresAt)
                                    <span class="t-sub">
                                        Exp: {{ \Carbon\Carbon::parse($otpExpiresAt)->format('H:i') }}
                                    </span>
                                @else
                                    <span class="t-sub">Approver</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

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
    {{-- Global Default Auto-Approve Timeout --}}
    <div style="background: white; border-radius: 20px; border: 1px solid #e2e8f0; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="background: rgba(79,70,229,0.08); padding: 10px; border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 1px dashed rgba(79,70,229,0.25);">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: #0f172a;">Global HOD Auto-Approval Timeout</h4>
                <p style="margin: 2px 0 0 0; font-size: 0.78rem; font-weight: 600; color: #64748b;">Fallback time used for automatic approvals if a custom timeout is not set below.</p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <input type="number" 
                   id="globalAutoApproveTimeout"
                   value="{{ \App\Models\Setting::get('default_hod_auto_approve_timeout_mins', 5) }}" 
                   min="1" 
                   onchange="updateGlobalAutoApproveTimeout(this)"
                   style="width: 90px; padding: 8px 12px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-weight: 800; font-size: 0.9rem; color: #0f172a; outline: none; transition: border-color 0.2s; text-align: center;"
                   onfocus="this.style.borderColor='#4f46e5'" 
                   onblur="this.style.borderColor='#cbd5e1'">
            <span style="font-size: 0.8rem; font-weight: 700; color: #334155;">Minutes</span>
        </div>
    </div>

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
                <div class="m-row" data-user-id="{{ $user->id }}" data-current-dept="{{ $user->department }}">
                    <div class="col-id">
                        <div class="m-avatar">
                            <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2364748b'><circle cx='12' cy='8' r='4'/><path d='M12 14c-4.42 0-8 3.58-8 8h16c0-4.42-3.58-8-8-8z'/></svg>" }}" alt="">
                            <span class="m-pulse {{ $user->is_active ? 'online' : 'offline' }}"></span>
                        </div>
                        <div class="m-identity">
                            <h4 class="m-name">{{ $user->name }}</h4>
                            <div class="m-handle" style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 2px;">
                                <span>@ {{ $user->username }}</span>
                                <select onchange="changeUserRole(this)" data-current-role="{{ $user->role }}" style="font-size: 0.65rem; background: #eef2ff; color: #4338ca; padding: 2px 8px; border-radius: 6px; font-weight: 800; font-family: sans-serif; border: 1px solid rgba(67, 56, 202, 0.1); cursor: pointer; outline: none; text-transform: uppercase; max-width: 150px;">
                                    <option value="Requisitioner" {{ $user->role === 'Requisitioner' ? 'selected' : '' }}>Requisitioner</option>
                                    <option value="Officer" {{ $user->role === 'Officer' ? 'selected' : '' }}>Store Officer</option>
                                    <option value="Department Head" {{ in_array($user->role, ['Department Head', 'Dept Head HR', 'Head of Welfare']) ? 'selected' : '' }}>Departmental Head</option>
                                    <option value="Main Admin" {{ $user->role === 'Main Admin' ? 'selected' : '' }}>Head of Admin(Authorizer)</option>
                                    <option value="Sub Main Admin" {{ $user->role === 'Sub Main Admin' ? 'selected' : '' }}>Delegator(Authorizer)</option>
                                    <option value="Auditor" {{ $user->role === 'Auditor' ? 'selected' : '' }}>Auditor</option>
                                    <option value="External Auditor" {{ $user->role === 'External Auditor' ? 'selected' : '' }}>External Auditor</option>
                                    <option value="Director General" {{ $user->role === 'Director General' ? 'selected' : '' }}>Director General</option>
                                </select>
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

{{-- ── Panel: All Users ── --}}
<div id="panel-all-users" class="pager-panel">

    {{-- Filter Bar --}}
    <div style="background: white; border-radius: 20px; border: 1px solid #e2e8f0; padding: 1rem 1.5rem; margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">

        {{-- Search --}}
        <div style="position: relative; flex: 1; min-width: 220px;">
            <i data-lucide="search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #4f46e5; opacity: 0.6; width: 14px; pointer-events:none;"></i>
            <input type="text" id="allUsersSearch" placeholder="Search by name or username…" oninput="filterAllUsers()"
                style="width: 100%; padding: 0.55rem 1rem 0.55rem 2.2rem; border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 0.85rem; font-weight: 600; color: #0f172a; outline: none; background: #f8fafc; box-sizing: border-box; transition: border-color 0.2s; font-family: inherit;"
                onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#e2e8f0'">
        </div>

        {{-- Role Filter --}}
        <div style="position: relative;">
            <i data-lucide="shield" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #4f46e5; opacity: 0.55; width: 13px; pointer-events:none;"></i>
            <select id="allUsersRoleFilter" onchange="filterAllUsers()"
                style="padding: 0.55rem 2rem 0.55rem 2rem; border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 0.82rem; font-weight: 700; color: #334155; background: #f8fafc; cursor: pointer; outline: none; appearance: none; min-width: 160px; font-family: inherit; transition: border-color 0.2s;"
                onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#e2e8f0'">
                <option value="">All Roles</option>
                <option value="Officer">Store Officer</option>
                <option value="Requisitioner">Requisitioner</option>
                <option value="Main Admin">Head of Admin</option>
                <option value="Sub Main Admin">Delegator</option>
                <option value="Department Head">Dept. Head</option>
                <option value="Auditor">Auditor</option>
                <option value="External Auditor">External Auditor</option>
                <option value="Director General">Director General</option>
            </select>
            <i data-lucide="chevron-down" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); color: #94a3b8; width: 13px; pointer-events:none;"></i>
        </div>

        {{-- Department Filter --}}
        <div style="position: relative;">
            <i data-lucide="building-2" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #4f46e5; opacity: 0.55; width: 13px; pointer-events:none;"></i>
            <select id="allUsersDeptFilter" onchange="filterAllUsers()"
                style="padding: 0.55rem 2rem 0.55rem 2rem; border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 0.82rem; font-weight: 700; color: #334155; background: #f8fafc; cursor: pointer; outline: none; appearance: none; min-width: 160px; font-family: inherit; transition: border-color 0.2s;"
                onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#e2e8f0'">
                <option value="">All Departments</option>
                @foreach($allDepartments as $dept)
                    <option value="{{ strtolower($dept) }}">{{ $dept }}</option>
                @endforeach
            </select>
            <i data-lucide="chevron-down" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); color: #94a3b8; width: 13px; pointer-events:none;"></i>
        </div>

        {{-- Status Filter --}}
        <div style="position: relative;">
            <i data-lucide="activity" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #4f46e5; opacity: 0.55; width: 13px; pointer-events:none;"></i>
            <select id="allUsersStatusFilter" onchange="filterAllUsers()"
                style="padding: 0.55rem 2rem 0.55rem 2rem; border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 0.82rem; font-weight: 700; color: #334155; background: #f8fafc; cursor: pointer; outline: none; appearance: none; min-width: 140px; font-family: inherit; transition: border-color 0.2s;"
                onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#e2e8f0'">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <i data-lucide="chevron-down" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); color: #94a3b8; width: 13px; pointer-events:none;"></i>
        </div>

        {{-- Results count --}}
        <span id="allUsersCount" style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; margin-left: auto; white-space: nowrap; padding-left: 0.5rem;">{{ $allUsers->count() }} users</span>
    </div>

    {{-- Users Matrix --}}
    <div class="permissions-matrix-wrapper">
        <div class="matrix-table" id="allUsersTable" style="min-width: 860px;">

            {{-- Header --}}
            <div class="m-header">
                <div style="flex: 0 0 320px; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em;">User</div>
                <div style="flex: 1; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em;">Department</div>
                <div style="flex: 1; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em;">Role</div>
                <div style="flex: 0 0 140px; text-align: right; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em;">Status</div>
            </div>

            {{-- Rows --}}
            <div id="allUsersBody">
                @forelse($allUsers as $user)
                <div class="all-users-row m-row"
                    data-user-id="{{ $user->id }}"
                    data-name="{{ strtolower($user->name) }}"
                    data-username="{{ strtolower($user->username) }}"
                    data-role="{{ $user->role }}"
                    data-dept="{{ strtolower($user->department ?? '') }}"
                    data-current-dept="{{ $user->department }}"
                    data-status="{{ $user->is_active ? 'active' : 'inactive' }}">

                    {{-- Identity (same as other panels) --}}
                    <div style="flex: 0 0 320px; display: flex; align-items: center; gap: 1.25rem;">
                        <div class="m-avatar">
                            <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2364748b'><circle cx='12' cy='8' r='4'/><path d='M12 14c-4.42 0-8 3.58-8 8h16c0-4.42-3.58-8-8-8z'/></svg>" }}" alt="">
                            <span class="m-pulse {{ $user->is_active ? 'online' : 'offline' }}"></span>
                        </div>
                        <div class="m-identity">
                            <h4 class="m-name">{{ $user->name }}</h4>
                            <div class="m-handle">@ {{ $user->username }}</div>
                        </div>
                    </div>

                    {{-- Department --}}
                    <div style="flex: 1;">
                        <select data-current-dept="{{ $user->department ?? '' }}" class="select2-assign-dept"
                            style="font-size: 0.75rem; background: #f0fdf4; color: #15803d; padding: 5px 10px; border-radius: 8px; font-weight: 800; font-family: sans-serif; text-transform: uppercase; border: 1px solid rgba(21,128,61,0.12); cursor: pointer; outline: none; max-width: 220px; transition: border-color 0.2s;"
                            onfocus="this.style.borderColor='#15803d'" onblur="this.style.borderColor='rgba(21,128,61,0.12)'">
                            <option value="" {{ is_null($user->department) || $user->department === '' ? 'selected' : '' }}>— No Department —</option>
                            @foreach($allDepartments as $dept)
                                <option value="{{ $dept }}" {{ strcasecmp($user->department, $dept) === 0 ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Role Dropdown --}}
                    <div style="flex: 1; display: flex; align-items: center;">
                        <select onchange="changeUserRole(this)" data-current-role="{{ $user->role }}"
                            style="font-size: 0.75rem; background: #eef2ff; color: #4338ca; padding: 5px 10px; border-radius: 8px; font-weight: 800; font-family: sans-serif; text-transform: uppercase; border: 1px solid rgba(67,56,202,0.12); cursor: pointer; outline: none; max-width: 220px; transition: border-color 0.2s;"
                            onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='rgba(67,56,202,0.12)'">
                            <option value="Requisitioner"    {{ $user->role === 'Requisitioner'    ? 'selected' : '' }}>Requisitioner</option>
                            <option value="Officer"          {{ $user->role === 'Officer'          ? 'selected' : '' }}>Store Officer</option>
                            <option value="Department Head"  {{ in_array($user->role, ['Department Head','Dept Head HR','Head of Welfare']) ? 'selected' : '' }}>Departmental Head</option>
                            <option value="Main Admin"       {{ $user->role === 'Main Admin'       ? 'selected' : '' }}>Head of Admin (Authorizer)</option>
                            <option value="Sub Main Admin"   {{ $user->role === 'Sub Main Admin'   ? 'selected' : '' }}>Delegator (Authorizer)</option>
                            <option value="Auditor"          {{ $user->role === 'Auditor'          ? 'selected' : '' }}>Auditor</option>
                            <option value="External Auditor" {{ $user->role === 'External Auditor' ? 'selected' : '' }}>External Auditor</option>
                            <option value="Director General" {{ $user->role === 'Director General' ? 'selected' : '' }}>Director General</option>
                        </select>
                    </div>

                    {{-- Status --}}
                    <div style="flex: 0 0 140px; display: flex; justify-content: flex-end;">
                        <div class="badge-status {{ $user->is_active ? 'authorized' : 'revoked' }}">
                            <i data-lucide="{{ $user->is_active ? 'shield-check' : 'shield-alert' }}"></i>
                            {{ $user->is_active ? 'ACTIVE' : 'INACTIVE' }}
                        </div>
                    </div>
                </div>
                @empty
                <div style="padding: 3rem; text-align: center; color: #94a3b8; font-weight: 600; background: white;">
                    No approved users found.
                </div>
                @endforelse
            </div>
        </div>
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

    .swal2-html-container .select2-container--default .select2-selection--single {
        height: 42px !important;
        border-radius: 8px !important;
        border: 1.5px solid #cbd5e1 !important;
        display: flex !important;
        align-items: center !important;
        background-color: #ffffff !important;
    }
    .swal2-html-container .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px !important;
        color: #0f172a !important;
        font-weight: 700 !important;
        font-size: 0.88rem !important;
        padding-left: 10px !important;
        text-align: left !important;
    }
    .swal2-html-container .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }

    /* Premium Select2 Delegation Dropdown Styling */
    .delegation-card .select2-container--default .select2-selection--single {
        height: 42px !important;
        border-radius: 10px !important;
        border: 1.5px solid #cbd5e1 !important;
        display: flex !important;
        align-items: center !important;
        background-color: #ffffff !important;
        padding-left: 6px !important;
        min-width: 180px;
        position: relative !important;
    }
    .delegation-card .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--text-main) !important;
        font-weight: 700 !important;
        font-size: 0.82rem !important;
        text-align: center !important;
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 100% !important;
        line-height: inherit !important;
        padding-left: 24px !important;
        padding-right: 24px !important;
    }
    .delegation-card .select2-container--default .select2-selection--single .select2-selection__clear {
        position: absolute !important;
        left: 12px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        color: #ef4444 !important;
        font-weight: 800 !important;
        font-size: 1rem !important;
    }
    .delegation-card .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
    .delegation-card .select2-dropdown {
        border-radius: 12px !important;
        border: 1.5px solid #cbd5e1 !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
        z-index: 9999 !important;
    }
    .delegation-card .select2-results__option {
        font-size: 0.8rem !important;
        font-weight: 700 !important;
        padding: 8px 12px !important;
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
        display: inline-block;
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
    .tab-badge.badge-neutral { background: #f1f5f9; color: #475569; }
    .pager-tab.active .tab-badge.badge-neutral { background: rgba(255,255,255,0.2); color: white; }

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

        /* Custom Green Theme for Department Select2 */
        .select2-container--default.select2-assign-dept-container .select2-selection--single {
            border-radius: 8px !important;
            border: 1px solid rgba(21, 128, 61, 0.12) !important;
            background-color: #f0fdf4 !important;
            height: 30px !important;
            display: flex !important;
            align-items: center !important;
            transition: border-color 0.2s !important;
        }
        .select2-container--default.select2-assign-dept-container .select2-selection--single:focus,
        .select2-container--default.select2-assign-dept-container .select2-selection--single:active {
            border-color: #15803d !important;
        }
        .select2-container--default.select2-assign-dept-container .select2-selection--single .select2-selection__rendered {
            color: #15803d !important;
            font-size: 0.75rem !important;
            font-weight: 800 !important;
            font-family: sans-serif !important;
            text-transform: uppercase !important;
            padding-left: 10px !important;
            padding-right: 20px !important;
        }
        .select2-container--default.select2-assign-dept-container .select2-selection--single .select2-selection__arrow {
            height: 28px !important;
            right: 4px !important;
        }
        .select2-container--default.select2-assign-dept-container .select2-selection--single .select2-selection__arrow b {
            border-color: #15803d transparent transparent transparent !important;
        }
        .select2-container--default.select2-assign-dept-container.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #15803d transparent !important;
        }
    </style>

<script>
    /* ── Initialize select2 for role dropdowns ── */
    function initRoleSelects() {
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('.select2-assign-role').select2({
                placeholder: '-- Assign Role --',
                allowClear: false,
                minimumResultsForSearch: 6,
                width: '360px'
            });
        }
    }

    /* ── Initialize select2 for department dropdowns ── */
    function initDeptSelects() {
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('.select2-assign-dept').select2({
                placeholder: '— No Department —',
                allowClear: true,
                tags: true, // Allow typing new/custom department names
                minimumResultsForSearch: 4,
                width: '240px',
                dropdownCssClass: 'select2-assign-dept-dropdown',
                containerCssClass: 'select2-assign-dept-container'
            }).on('select2:open', function(e) {
                // Pre-populate the search field inside the dropdown with the current value
                const selectElement = this;
                const currentValue = $(selectElement).val();
                if (currentValue) {
                    setTimeout(function() {
                        const searchInput = document.querySelector('.select2-container--open .select2-search__field');
                        if (searchInput) {
                            searchInput.value = currentValue;
                            searchInput.focus();
                            // Trigger input event to let Select2 filter/refresh
                            searchInput.dispatchEvent(new Event('input', { bubbles: true }));

                            // Listen for Enter key to save the name change
                            searchInput.addEventListener('keydown', function(event) {
                                if (event.key === 'Enter') {
                                    const typedVal = $.trim(searchInput.value);
                                    if (typedVal) {
                                        event.preventDefault();
                                        event.stopImmediatePropagation();
                                        
                                        let optionExists = false;
                                        $(selectElement).find('option').each(function() {
                                            if ($(this).val().toLowerCase() === typedVal.toLowerCase()) {
                                                optionExists = true;
                                                $(selectElement).val($(this).val()).trigger('change');
                                            }
                                        });
                                        
                                        if (!optionExists) {
                                            const newOption = new Option(typedVal, typedVal, true, true);
                                            $(selectElement).append(newOption).trigger('change');
                                        }
                                        
                                        $(selectElement).select2('close');
                                    }
                                }
                            }, true);
                        }
                    }, 50);
                }
            }).on('change', function() {
                changeUserDepartment(this);
            });
        }
    }

    /* ── Tab Switcher ── */
    function switchTab(tab) {
        document.querySelectorAll('.pager-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.pager-panel').forEach(p => p.classList.remove('active'));
        
        const tabBtn = document.getElementById('tab-' + tab);
        const panelEl = document.getElementById('panel-' + tab);
        if (tabBtn) tabBtn.classList.add('active');
        if (panelEl) panelEl.classList.add('active');

        // Show/hide search vault (only relevant for matrix tabs, not registrations/all-users)
        const sv = document.getElementById('searchVaultWrap');
        if (sv) sv.style.display = (tab !== 'registrations' && tab !== 'all-users') ? '' : 'none';

        // Persist active tab state
        if (history.replaceState) {
            history.replaceState(null, null, '#' + tab);
        } else {
            window.location.hash = tab;
        }
        sessionStorage.setItem('active_permissions_tab', tab);
    }

    /* ── Personnel Filter (Store Officers / Requisitioners / Dept Heads) ── */
    function filterPersonnel() {
        const term = document.getElementById('personnelSearch').value.toLowerCase();
        // Only filter rows that are NOT in the all-users table
        document.querySelectorAll('#panel-store-officers .m-row, #panel-requisitioners .m-row, #panel-dept-heads .m-row').forEach(row => {
            const name     = (row.querySelector('.m-name')?.textContent || '').toLowerCase();
            const username = (row.querySelector('.m-handle')?.textContent || '').toLowerCase();
            row.style.display = (name.includes(term) || username.includes(term)) ? 'flex' : 'none';
        });
    }

    /* ── All Users Filter ── */
    function filterAllUsers() {
        const search       = (document.getElementById('allUsersSearch')?.value || '').toLowerCase().trim();
        const roleFilter   = (document.getElementById('allUsersRoleFilter')?.value || '').toLowerCase();
        const deptFilter   = (document.getElementById('allUsersDeptFilter')?.value || '').toLowerCase();
        const statusFilter = (document.getElementById('allUsersStatusFilter')?.value || '').toLowerCase();

        let visible = 0;
        document.querySelectorAll('.all-users-row').forEach(row => {
            const name     = row.dataset.name     || '';
            const username = row.dataset.username || '';
            const role     = (row.dataset.role   || '').toLowerCase();
            const dept     = row.dataset.dept    || '';
            const status   = row.dataset.status  || '';

            const matchSearch = !search       || name.includes(search) || username.includes(search);
            const matchRole   = !roleFilter   || role   === roleFilter;
            const matchDept   = !deptFilter   || dept   === deptFilter;
            const matchStatus = !statusFilter || status === statusFilter;

            const show = matchSearch && matchRole && matchDept && matchStatus;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        const countEl = document.getElementById('allUsersCount');
        if (countEl) countEl.textContent = visible + ' user' + (visible !== 1 ? 's' : '');
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



    function updateGlobalAutoApproveTimeout(input) {
        const val = parseInt(input.value);
        if (isNaN(val) || val < 1) {
            alert('Please enter a valid number of minutes (minimum 1).');
            input.value = 5;
            return;
        }

        const inputWrapper = input.parentElement;
        if (inputWrapper) inputWrapper.style.opacity = '0.5';

        fetch('{{ route("admin.permissions.update_global_setting", [], false) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ key: 'default_hod_auto_approve_timeout_mins', value: val })
        })
        .then(r => r.json())
        .then(data => {
            if (inputWrapper) inputWrapper.style.opacity = '1';
            if (!data.success) {
                alert('Failed to update global timeout: ' + data.message);
            }
        })
        .catch(() => {
            if (inputWrapper) inputWrapper.style.opacity = '1';
            alert('A system error occurred.');
        });
    }

    function changeUserRole(selectElement) {
        const row = selectElement.closest('.m-row');
        const userId = row.getAttribute('data-user-id');
        const oldRole = selectElement.getAttribute('data-current-role') || '';
        const newRole = selectElement.value;

        // If they select the same role, do nothing
        if (oldRole === newRole) return;

        const userName = row.querySelector('.m-name').textContent.trim();
        const currentDept = row.getAttribute('data-current-dept') || '';

        // Helper to map values to human-friendly labels
        function getFriendlyRoleName(roleVal) {
            const map = {
                'Officer': 'Store Officer',
                'Requisitioner': 'Requisition Officer',
                'Main Admin': 'Head of Admin',
                'Sub Main Admin': 'Delegator',
                'Department Head': 'Departmental Head',
                'Dept Head HR': 'Departmental Head',
                'Head of Welfare': 'Departmental Head',
                'Auditor': 'Auditor',
                'External Auditor': 'External Auditor',
                'Director General': 'Director General'
            };
            return map[roleVal] || roleVal;
        }

        const oldFriendly = getFriendlyRoleName(oldRole);
        const newFriendly = getFriendlyRoleName(newRole);

        const needsDept = (newRole === 'Requisitioner' || newRole === 'Department Head' || newRole === 'Dept Head HR' || newRole === 'Head of Welfare');
        const allDepartmentsList = @json($allDepartments);

        let deptDropdownHtml = '';
        if (needsDept) {
            let deptOptionsHtml = '<option value="">-- Select Department --</option>';
            allDepartmentsList.forEach(dept => {
                const isSelected = dept.toLowerCase() === currentDept.toLowerCase() ? 'selected' : '';
                deptOptionsHtml += `<option value="${dept}" ${isSelected}>${dept}</option>`;
            });

            deptDropdownHtml = `
                <div style="margin-top: 15px; text-align: left;">
                    <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;">Assign Department</label>
                    <div style="width: 100%;">
                        <select id="swalDeptSelect" style="width: 100%;">
                            ${deptOptionsHtml}
                        </select>
                    </div>
                </div>
            `;
        }

        Swal.fire({
            title: `<span style="font-weight:900;color:#0f172a;">Change Role?</span>`,
            html: `
                <div style="text-align: left; font-size: 0.95rem; color: #334155; line-height: 1.6;">
                    <p style="margin-bottom: 12px; font-weight: 600;">
                        Are you sure you want to change <strong>${userName}</strong>’s role from <strong>${oldFriendly}</strong> to <strong>${newFriendly}</strong>?
                    </p>
                    ${deptDropdownHtml}
                    <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 12px; padding: 12px; color: #b45309; font-size: 0.82rem; font-weight: 600; display: flex; gap: 8px; align-items: flex-start; margin-top: 15px;">
                        <span>⚠️</span>
                        <span><strong>Please note:</strong> This action will be permanently recorded in the audit log and may be reviewed by authorized auditors.</span>
                    </div>
                </div>
            `,
            icon: 'warning',
            iconColor: '#f59e0b',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#f1f5f9',
            confirmButtonText: 'Yes, Change',
            cancelButtonText: 'No, Cancel',
            background: 'white',
            customClass: {
                cancelButton: 'swal-cancel-dark'
            },
            didOpen: () => {
                if (needsDept && typeof $ !== 'undefined' && $.fn.select2) {
                    $('#swalDeptSelect').select2({
                        placeholder: '-- Select Department --',
                        allowClear: false,
                        dropdownParent: $('.swal2-container'),
                        width: '100%'
                    });
                }
            },
            preConfirm: () => {
                if (needsDept) {
                    const deptVal = $('#swalDeptSelect').val();
                    if (!deptVal) {
                        Swal.showValidationMessage('Please select a department');
                        return false;
                    }
                    return { department: deptVal };
                }
                return {};
            }
        }).then((result) => {
            if (result.isConfirmed) {
                row.classList.add('syncing-row');

                const selectedDept = result.value ? result.value.department : null;
                const payload = {
                    user_id: userId,
                    role: newRole
                };
                if (selectedDept) {
                    payload.department = selectedDept;
                }

                fetch('{{ route("admin.permissions.update_role", [], false) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(async r => {
                    const data = await r.json();
                    if (!r.ok) {
                        throw new Error(data.message || 'Failed to update role.');
                    }
                    return data;
                })
                .then(data => {
                    row.classList.remove('syncing-row');
                    selectElement.setAttribute('data-current-role', newRole);
                    if (selectedDept) {
                        row.setAttribute('data-current-dept', selectedDept);
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Role Updated',
                        text: data.message || 'User role has been updated successfully.',
                        confirmButtonColor: '#4f46e5',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                })
                .catch(err => {
                    row.classList.remove('syncing-row');
                    selectElement.value = oldRole;
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: err.message || 'A system error occurred.',
                        confirmButtonColor: '#ef4444'
                    }).then(() => {
                        window.location.reload();
                    });
                });
            } else {
                selectElement.value = oldRole;
            }
        });
    }

    function changeUserDepartment(selectElement) {
        const row = selectElement.closest('.m-row');
        const userId = row.getAttribute('data-user-id');
        const oldDept = selectElement.getAttribute('data-current-dept') || '';
        const newDept = selectElement.value;

        // If they select the same department, do nothing
        if (oldDept.toLowerCase() === newDept.toLowerCase()) return;

        const userName = row.querySelector('.m-name').textContent.trim();

        Swal.fire({
            title: `<span style="font-weight:900;color:#0f172a;">Change Department?</span>`,
            html: `
                <div style="text-align: left; font-size: 0.95rem; color: #334155; line-height: 1.6;">
                    <p style="margin-bottom: 12px; font-weight: 600;">
                        Are you sure you want to change <strong>${userName}</strong>’s department from <strong>${oldDept || 'None'}</strong> to <strong>${newDept || 'None'}</strong>?
                    </p>
                    <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 12px; padding: 12px; color: #b45309; font-size: 0.82rem; font-weight: 600; display: flex; gap: 8px; align-items: flex-start; margin-top: 15px;">
                        <span>⚠️</span>
                        <span><strong>Please note:</strong> This action will be permanently recorded in the audit log. If this user is a Departmental Head, any Requisitioners in their old department will also be automatically moved to the new department.</span>
                    </div>
                </div>
            `,
            icon: 'warning',
            iconColor: '#f59e0b',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#f1f5f9',
            confirmButtonText: 'Yes, Change',
            cancelButtonText: 'No, Cancel',
            background: 'white',
            customClass: {
                cancelButton: 'swal-cancel-dark'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                row.classList.add('syncing-row');

                fetch('{{ route("admin.permissions.update_department", [], false) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ user_id: userId, department: newDept })
                })
                .then(async r => {
                    const data = await r.json();
                    if (!r.ok) {
                        throw new Error(data.message || 'Failed to update department.');
                    }
                    return data;
                })
                .then(data => {
                    row.classList.remove('syncing-row');
                    selectElement.setAttribute('data-current-dept', newDept);
                    $(selectElement).val(newDept).trigger('change.select2');

                    // Dynamically update all select2 dropdowns and page filters in the DOM
                    if (data.old_dept && data.new_dept) {
                        const oldVal = data.old_dept.toLowerCase();
                        const newVal = data.new_dept;

                        // 1. Update matching options in all select2 dropdowns
                        $('.select2-assign-dept').each(function() {
                            const selectEl = $(this);
                            let found = false;
                            selectEl.find('option').each(function() {
                                if ($(this).val().toLowerCase() === oldVal) {
                                    $(this).val(newVal).text(newVal);
                                    found = true;
                                }
                            });
                            if (!found) {
                                selectEl.append(new Option(newVal, newVal));
                            }
                            selectEl.trigger('change.select2');
                        });

                        // 2. Update matching options in all department filter dropdowns
                        const filterSelects = ['#allUsersDeptFilter'];
                        filterSelects.forEach(selector => {
                            const filterEl = $(selector);
                            if (filterEl.length) {
                                let found = false;
                                filterEl.find('option').each(function() {
                                    if ($(this).val().toLowerCase() === oldVal) {
                                        $(this).val(newVal.toLowerCase()).text(newVal);
                                        found = true;
                                    }
                                });
                                if (!found) {
                                    filterEl.append(new Option(newVal, newVal.toLowerCase()));
                                }
                            }
                        });
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Department Updated',
                        text: data.message || 'Department has been updated successfully.',
                        confirmButtonColor: '#10b981',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                })
                .catch(err => {
                    row.classList.remove('syncing-row');
                    $(selectElement).val(oldDept).trigger('change.select2');
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: err.message || 'A system error occurred.',
                        confirmButtonColor: '#ef4444'
                    }).then(() => {
                        window.location.reload();
                    });
                });
            } else {
                $(selectElement).val(oldDept).trigger('change.select2');
            }
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
                // If approving, make sure a role is selected
                if (form.action.includes('approve-registration')) {
                    const roleSelect = form.querySelector('select[name="role"]');
                    if (roleSelect && !roleSelect.value) {
                        if (buttons) {
                            buttons.forEach(btn => btn.disabled = false);
                        }
                        if (typeof showToast === 'function') {
                            showToast('Role Required', 'Please assign a role before approving.', 'warning');
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Role Required',
                                text: 'Please select a role to assign to this user before approving.',
                                confirmButtonColor: '#4f46e5'
                            });
                        }
                        return;
                    }
                }

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    let errMsg = 'Failed to process request.';
                    try {
                        const errData = await response.json();
                        if (errData && errData.message) {
                            errMsg = errData.message;
                        } else if (errData && errData.errors) {
                            errMsg = Object.values(errData.errors).flat().join('\n');
                        }
                    } catch (e) {}
                    throw new Error(errMsg);
                }

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
                    initRoleSelects();
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
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '<span style="font-size: 1.5rem; font-weight: 900; color: #0f172a;">Warning</span>',
                        html: `
                            <div style="color: #64748b; font-size: 0.95rem; font-weight: 600; line-height: 1.6; margin-bottom: 10px; text-align: left;">
                                ${err.message || 'An error occurred while processing the request.'}
                            </div>
                        `,
                        icon: 'warning',
                        iconColor: '#f59e0b',
                        confirmButtonColor: '#4f46e5',
                        confirmButtonText: '<span style="font-weight: 800; padding: 6px 16px;">OK</span>',
                        background: '#ffffff',
                        backdrop: 'rgba(15, 23, 42, 0.6)',
                        padding: '2rem',
                        customClass: {
                            popup: 'premium-popup'
                        }
                    });
                } else if (typeof showToast === 'function') {
                    showToast('Error', err.message || 'An error occurred while processing the request.', 'error');
                } else {
                    alert(err.message || 'An error occurred while processing the request.');
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
                        initRoleSelects();
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

    function initTabsOnLoad() {
        if (window.lucide) lucide.createIcons();
        initRoleSelects();
        initDeptSelects();

        // Auto-open tab from server session, URL hash, or sessionStorage fallback
        const serverTab = '{{ session('open_tab') }}';
        const hash = window.location.hash.replace('#', '');
        const savedTab = sessionStorage.getItem('active_permissions_tab');

        if (serverTab) {
            switchTab(serverTab);
        } else if (hash) {
            switchTab(hash);
        } else if (savedTab) {
            switchTab(savedTab);
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
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTabsOnLoad);
    } else {
        initTabsOnLoad();
    }

    @if(auth()->user()->is_admin && auth()->user()->role === 'Head of Stores')
    async function toggleUserDelegation(el) {
        const userId = el.dataset.userId;
        const userName = el.dataset.userName;
        const checked = el.checked;

        if (checked) {
            // Toggling ON: simple confirm — no time limit
            const confirmed = await Swal.fire({
                title: `<span style="font-weight:900;color:#0f172a;">Delegate Authority</span>`,
                html: `<p style="color:#64748b;font-size:0.88rem;margin:0;">Delegate approval authority to <strong>${userName}</strong>?<br><span style="font-size:0.78rem;color:#94a3b8;margin-top:6px;display:block;">Authority remains active until manually revoked.</span></p>`,
                icon: 'warning',
                iconColor: '#059669',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#f1f5f9',
                confirmButtonText: 'Delegate Authority',
                cancelButtonText: 'Cancel',
                customClass: {
                    cancelButton: 'swal-cancel-dark'
                }
            });

            if (!confirmed.isConfirmed) {
                el.checked = false;
                return;
            }

            const expiryMinutes = '';

            try {
                const res = await fetch('{{ route("admin.delegation.generate-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        user_id: userId,
                        expiry_minutes: expiryMinutes
                    })
                });

                const data = await res.json();
                if (data.success) {
                    if (typeof showToast === 'function') {
                        showToast('Delegation Active', `Delegation authority successfully assigned to ${userName}.`, 'success');
                    } else {
                        Swal.fire('Delegated!', 'Delegation authority has been assigned successfully.', 'success');
                    }
                    
                    window.location.reload();
                } else {
                    el.checked = false;
                    Swal.fire('Error', data.message || 'Failed to delegate authority.', 'error');
                }
            } catch (err) {
                el.checked = false;
                Swal.fire('Error', 'Failed to communicate with delegation subsystem.', 'error');
            }

        } else {
            // Toggling OFF: prompt to revoke
            const confirmed = await Swal.fire({
                title: '<span style="font-weight:900;color:#0f172a;">Revoke Delegation?</span>',
                html: `<p style="color:#64748b;font-size:0.9rem;margin:0;">Are you sure you want to revoke delegated approval authority from <strong>${userName}</strong>?</p>`,
                icon: 'warning',
                iconColor: '#ef4444',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#f1f5f9',
                confirmButtonText: 'Yes, Revoke Access',
                cancelButtonText: 'Cancel',
                customClass: {
                    cancelButton: 'swal-cancel-dark'
                }
            });

            if (!confirmed.isConfirmed) {
                el.checked = true;
                return;
            }

            try {
                const res = await fetch('{{ route("admin.delegation.revoke-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (data.success) {
                    if (typeof showToast === 'function') {
                        showToast('Delegation Revoked', 'Delegation authority revoked.', 'info');
                    } else {
                        Swal.fire('Revoked!', 'Delegation authority has been revoked successfully.', 'success');
                    }
                    
                    window.location.reload();
                } else {
                    el.checked = true;
                    Swal.fire('Error', data.message || 'Failed to revoke delegation.', 'error');
                }
            } catch (err) {
                el.checked = true;
                Swal.fire('Error', 'Failed to communicate with delegation subsystem.', 'error');
            }
        }
    }
    @endif
</script>
@endsection
