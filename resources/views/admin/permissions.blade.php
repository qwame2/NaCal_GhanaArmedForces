
@extends('layouts.admin')

@section('title', 'Permissions & Registrations')

@section('content')
<div class="view-header" style="margin-bottom: 3rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 2rem; width: 100%;">
        <div style="flex: 1; min-width: 300px;">
            <div class="title-group">
                <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin-top: 0.5rem; max-width: 600px;">
                    Manage granular permissions, security clearances, and review incoming registration requests.
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
<div class="pager-tabs-wrap">
    <button class="pager-tab active" id="tab-permissions" onclick="switchTab('permissions')">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Permissions Matrix
    </button>
    <button class="pager-tab" id="tab-registrations" onclick="switchTab('registrations')">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
        Registration Requests
        @if($pendingUsers->count() > 0)
            <span class="tab-badge">{{ $pendingUsers->count() }}</span>
        @endif
    </button>
</div>

{{-- ── Panel: Permissions Matrix ── --}}
<div id="panel-permissions" class="pager-panel active">
    <div class="permissions-matrix-wrapper">
        <div class="matrix-table">
            <div class="m-header">
                <div class="col-id">Users</div>
                <div class="col-ctrl">Item Entry</div>
                <div class="col-ctrl">Logistics Ops</div>
                <div class="col-ctrl">Report Access</div>
                <div class="col-stat">Clearance Status</div>
            </div>

            <div class="m-body" id="matrixBody">
                @foreach($users as $user)
                <div class="m-row" data-user-id="{{ $user->id }}">
                    <div class="col-id">
                        <div class="m-avatar">
                            <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2364748b'><circle cx='12' cy='8' r='4'/><path d='M12 14c-4.42 0-8 3.58-8 8h16c0-4.42-3.58-8-8-8z'/></svg>" }}" alt="">
                            <span class="m-pulse {{ $user->is_active ? 'online' : 'offline' }}"></span>
                        </div>
                        <div class="m-identity">
                            <h4 class="m-name">{{ $user->name }}</h4>
                            <span class="m-handle">@ {{ $user->username }}</span>
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
                                <span class="t-main">Issue & Return</span>
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
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ── Panel: Registration Requests ── --}}
<div id="panel-registrations" class="pager-panel">

    @if($pendingUsers->count() === 0)
        <div class="reg-empty-state">
            <div class="reg-empty-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <h3>No Pending Requests</h3>
            <p>All registration requests have been processed. New submissions will appear here.</p>
        </div>
    @else
        <div class="reg-list">
            @foreach($pendingUsers as $req)
            <div class="reg-card" id="reg-card-{{ $req->id }}">
                {{-- Avatar + Identity --}}
                <div class="reg-identity">
                    <div class="reg-avatar">
                        {{ strtoupper(substr($req->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="reg-name">{{ $req->name }}</div>
                        <div class="reg-username">@ {{ $req->username }}</div>
                        <div class="reg-time">Submitted {{ $req->created_at->diffForHumans() }}</div>
                    </div>
                </div>

                {{-- Detail Pills --}}
                <div class="reg-details">
                    <div class="reg-pill role">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        {{ $req->role === 'Main Admin' ? 'Head of Admin' : $req->role }}
                    </div>
                    @if($req->department)
                    <div class="reg-pill dept">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>
                        {{ $req->department }}
                    </div>
                    @endif
                    @if($req->rank)
                    <div class="reg-pill rank">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                        {{ $req->rank }}
                    </div>
                    @endif
                    @if($req->sponsor)
                    <div class="reg-pill sponsor" style="background: #f5f3ff; color: #6d28d9; border: 1px solid rgba(109, 40, 217, 0.1);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Sponsor: {{ $req->sponsor->name }}
                    </div>
                    @endif
                    @if($req->service_number)
                    <div class="reg-pill service-number" style="background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>
                        Service: {{ $req->service_number }}
                    </div>
                    @endif
                    @if($req->phone)
                    <div class="reg-pill phone" style="background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        {{ $req->phone }}
                    </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="reg-actions">
                    <form action="{{ route('admin.users.approve_registration', $req->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="reg-btn approve" title="Approve registration">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Approve
                        </button>
                    </form>

                    <form action="{{ route('admin.users.reject_registration', $req->id) }}" method="POST" style="display:inline;" class="decline-form">
                        @csrf
                        <button type="button" class="reg-btn decline" title="Decline registration" onclick="confirmDecline(this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Decline
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
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

        // Show/hide search vault (only relevant for permissions)
        const sv = document.getElementById('searchVaultWrap');
        if (sv) sv.style.display = (tab === 'permissions') ? '' : 'none';
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
                switchTab('permissions');
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
                form.submit();
            }
        });
    }

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
