@extends('layouts.admin')

@section('title', 'User Registry')

@section('content')
<div class="command-center">
    <!-- Precision Metrics -->
    <div class="metrics-row">
        <div class="metric-module">
            <div class="metric-visual primary">
                <i data-lucide="users"></i>
            </div>
            <div class="metric-data">
                <span class="m-label">Total Users</span>
                <h3 class="m-value">{{ $totalUsers }}</h3>
                <div class="m-trend"><i data-lucide="database"></i> Records Sync'd</div>
            </div>
        </div>

        <div class="metric-module">
            <div class="metric-visual success">
                <i data-lucide="activity"></i>
            </div>
            <div class="metric-data">
                <span class="m-label">Live Operations</span>
                <h3 class="m-value">{{ $onlineCount }}</h3>
                <div class="m-trend" style="color: #10b981;">
                    <span class="pulse-mini"></span>
                    Concurrent Sessions
                </div>
            </div>
        </div>

        <div class="metric-module">
            <div class="metric-visual warning">
                <i data-lucide="shield-check"></i>
            </div>
            <div class="metric-data">
                <span class="m-label">Admin Clearances</span>
                <h3 class="m-value">{{ $allUsers->where('is_admin', true)->count() }}</h3>
                <span class="m-status">SECURE</span>
            </div>
        </div>
    </div>

    <!-- Precision Registry Module -->
    <div class="registry-vault">
        <div class="vault-toolbar">
            <div class="toolbar-branding">
                <div class="registry-label-group">
                    <div class="registry-icon-box">
                        <i data-lucide="shield-check"></i>
                    </div>
                    <div class="registry-title-stack">
                        <h3 class="vault-title">User Registry</h3>
                        <div class="status-indicator-pill">
                            <span class="live-pulse"></span>
                            SECURE & UPDATED
                        </div>
                    </div>
                </div>
                <p class="vault-subtitle">Managing {{ $totalUsers }} user records</p>
            </div>
            
            <div class="toolbar-actions">
                @if(isset($legacyAdminLogs) && $legacyAdminLogs->count() > 0)
                <button type="button" class="btn-tool" onclick="openLegacyAuditModal()" style="border-radius: 18px; padding: 12px 24px; font-weight: 800; font-size: 0.85rem; gap: 10px; display: flex; align-items: center; background: #fffbeb; color: #d97706; border: 1.5px solid #fde68a; cursor: pointer; transition: all 0.3s ease;">
                    <i data-lucide="history" style="width: 18px;"></i>
                    <span>Legacy Audit</span>
                </button>
                @endif
                <button type="button" class="btn-tool primary" onclick="openAddPersonnelModal()" style="border-radius: 18px; padding: 12px 24px; font-weight: 800; font-size: 0.85rem; gap: 10px; display: flex; align-items: center; background: #0f172a; color: white; border: none; cursor: pointer; transition: all 0.3s ease;">
                    <i data-lucide="user-plus" style="width: 18px;"></i>
                    <span>Register Personnel</span>
                </button>
                <div class="command-search">
                    <div class="search-icon-wrap">
                        <i data-lucide="search"></i>
                    </div>
                    <input type="text" id="registrySearch" placeholder="Search registry...">
                    <div class="shortcut-hint">
                        <span class="key-group">
                            <span class="key">Ctrl</span>
                            <span class="key">⌘</span>
                        </span>
                        <span class="key">K</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="precision-table">
                <thead>
                    <tr>
                        <th class="col-identity">Staff Member</th>
                        <th class="col-clearance">Role</th>
                        <th class="col-sector">Department</th>
                        <th class="col-sync">Login Time</th>
                        <th class="col-sync">Logout Time</th>
                        <th class="col-ops" style="text-align: center;">OPT</th>
                    </tr>
                </thead>
                <tbody id="registryBody">
                    @foreach($users as $user)
                    <tr class="registry-row">
                        <td>
                            <div class="identity-cell">
                                <div class="avatar-capsule">
                                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}">
                                    <span class="status-dot-mini {{ $user->is_online ? 'online' : 'offline' }}"></span>
                                </div>
                                <div class="id-meta">
                                    <span class="full-name">{{ $user->name }}</span>
                                    <span class="callsign">@ {{ $user->username }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($user->is_admin)
                                <div class="clearance-pill admin">
                                    <div class="dot"></div>
                                    Administrator
                                </div>
                            @else
                                <div class="clearance-pill standard">
                                    <div class="dot"></div>
                                    Staff Member
                                </div>
                            @endif
                        </td>
                        <td><span class="sector-badge">{{ $user->department ?? 'UNASSIGNED' }}</span></td>
                        <td>
                            <span class="sync-time" style="color: #10b981; font-weight: 800;">
                                {{ $user->last_login_at ? $user->last_login_at->format('d/m/y H:i') : 'NO RECORD' }}
                            </span>
                        </td>
                        <td>
                            <span class="sync-time" style="color: #64748b; font-weight: 800;">
                                {{ $user->last_logout_at ? $user->last_logout_at->format('d/m/y H:i') : 'NO RECORD' }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div class="ops-cluster" style="justify-content: center; display: flex; align-items: center; gap: 10px;">
                                <button type="button" class="btn-purge" title="Personnel Details" 
                                    onclick="viewUserDetails({
                                        name: '{{ addslashes($user->name) }}',
                                        username: '{{ addslashes($user->username) }}',
                                        email: '{{ $user->email ?? 'Not Provided' }}',
                                        phone: '{{ $user->phone ?? 'Not Provided' }}',
                                        department: '{{ $user->department ?? 'UNASSIGNED' }}',
                                        role: '{{ $user->role }}',
                                        last_login: '{{ $user->last_login_at ? $user->last_login_at->format('d/m/y H:i') : 'No record' }}',
                                        status: '{{ $user->is_active ? 'ACTIVE' : 'DEACTIVATED' }}',
                                        avatar: '{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}'
                                    })"
                                    style="border: 1px solid #e0e7ff; color: #4f46e5; background: #f5f3ff;"
                                    onmouseover="this.style.background='#4f46e5'; this.style.color='white'" 
                                    onmouseout="this.style.background='#f5f3ff'; this.style.color='#4f46e5'">
                                    <i data-lucide="eye" style="width: 16px;"></i>
                                </button>
                                @if(!$user->is_active)
                                <span style="background: #fef2f2; color: #ef4444; border: 1px solid #fecdd3; padding: 4px 8px; border-radius: 8px; font-size: 0.65rem; font-weight: 900; letter-spacing: 0.05em;">INACTIVE</span>
                                @endif
                                
                                @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.toggle_status', $user->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    @if($user->is_active)
                                    <button type="submit" class="btn-purge" title="Deactivate Account" style="border: 1px solid #fef3c7; color: #f59e0b; background: #fffbeb;" onmouseover="this.style.background='#f59e0b'; this.style.color='white'" onmouseout="this.style.background='#fffbeb'; this.style.color='#f59e0b'">
                                        <i data-lucide="power-off"></i>
                                    </button>
                                    @else
                                    <button type="submit" class="btn-purge" title="Reactivate Account" style="border: 1px solid #d1fae5; color: #10b981; background: #ecfdf5;" onmouseover="this.style.background='#10b981'; this.style.color='white'" onmouseout="this.style.background='#ecfdf5'; this.style.color='#10b981'">
                                        <i data-lucide="power"></i>
                                    </button>
                                    @endif
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Precision Pagination Module -->
        <div class="registry-pagination" style="padding: 2rem 3rem; background: #fafcff; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="font-size: 0.85rem; color: #64748b; font-weight: 700; display: flex; align-items: center; gap: 1.5rem;">
                <div>
                    Showing <span style="color: #0f172a; font-weight: 900;">{{ $users->firstItem() ?? 0 }}</span> to 
                    <span style="color: #0f172a; font-weight: 900;">{{ $users->lastItem() ?? 0 }}</span> of 
                    <span style="color: #0f172a; font-weight: 900;">{{ $users->total() }}</span> Personnel
                </div>

                <form action="{{ route('admin.index') }}" method="GET" style="display: flex; align-items: center; gap: 0.75rem; border-left: 2px solid #e2e8f0; padding-left: 1.5rem;">
                    <span style="font-size: 0.7rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em; color: #94a3b8;">Rows per page:</span>
                    <select name="per_page" onchange="this.form.submit()" style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.5rem 2rem 0.5rem 1rem; font-size: 0.8rem; font-weight: 900; color: #4f46e5; outline: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: 0.2s; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%234f46e5%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 8px center; background-size: 14px;">
                        @foreach([10, 25, 50, 100] as $count)
                            <option value="{{ $count }}" {{ request('per_page') == $count ? 'selected' : '' }}>{{ $count }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="custom-pagination">
                @if ($users->onFirstPage())
                    <span class="page-btn disabled">Previous</span>
                @else
                    <a href="{{ $users->appends(request()->query())->previousPageUrl() }}" class="page-btn">Previous</a>
                @endif

                @if ($users->hasMorePages())
                    <a href="{{ $users->appends(request()->query())->nextPageUrl() }}" class="page-btn">Next</a>
                @else
                    <span class="page-btn disabled">Next</span>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    /* Precision Metrics Styles */
    .metrics-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-bottom: 3.5rem; }
    .metric-module { background: white; padding: 2.25rem; border-radius: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 1.75rem; border: 1px solid rgba(0,0,0,0.01); transition: all 0.3s ease; }
    .metric-module:hover { transform: translateY(-8px); box-shadow: 0 20px 60px rgba(0,0,0,0.06); }
    
    .metric-visual { width: 64px; height: 64px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .metric-visual.primary { background: #eef2ff; color: #4f46e5; }
    .metric-visual.success { background: #ecfdf5; color: #10b981; }
    .metric-visual.warning { background: #fffbeb; color: #f59e0b; }
    
    .m-label { font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; display: block; margin-bottom: 4px; }
    .m-value { font-size: 2rem; font-weight: 950; color: #0f172a; margin: 0; letter-spacing: -0.04em; }
    .m-trend { font-size: 0.75rem; font-weight: 800; color: #10b981; display: flex; align-items: center; gap: 4px; margin-top: 6px; }
    .m-sub, .m-status { font-size: 0.75rem; font-weight: 700; color: #64748b; margin-top: 6px; display: block; }
    .m-status { color: #10b981; }

    .pulse-mini {
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
        animation: pulse-active 1.5s infinite;
    }

    @keyframes pulse-active {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    /* Precision Registry Styles */
    .registry-vault { background: white; border-radius: 40px; box-shadow: 0 30px 80px rgba(0,0,0,0.04); overflow: hidden; border: 1px solid rgba(0,0,0,0.01); }
    .vault-toolbar { padding: 2.5rem 3rem; background: #fafcff; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
    .registry-label-group { display: flex; align-items: center; gap: 16px; margin-bottom: 8px; }
    .registry-icon-box { 
        width: 48px; 
        height: 48px; 
        background: #eef2ff; 
        color: #4338ca; 
        border-radius: 14px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        box-shadow: inset 0 0 12px rgba(67, 56, 202, 0.05);
    }
    .registry-icon-box i { width: 24px; height: 24px; }
    
    .registry-title-stack { display: flex; align-items: center; gap: 12px; }
    .vault-title { font-size: 1.5rem; font-weight: 900; color: #0f172a; letter-spacing: -0.04em; }
    
    .status-indicator-pill { 
        background: #ecfdf5; 
        color: #059669; 
        padding: 4px 10px; 
        border-radius: 20px; 
        font-size: 0.65rem; 
        font-weight: 900; 
        letter-spacing: 0.05em; 
        display: flex; 
        align-items: center; 
        gap: 6px; 
        border: 1px solid #d1fae5;
    }
    .live-pulse { 
        width: 6px; 
        height: 6px; 
        background: #10b981; 
        border-radius: 50%; 
        animation: status-glow 2s infinite; 
    }
    @keyframes status-glow { 
        0% { transform: scale(0.95); opacity: 0.5; } 
        50% { transform: scale(1.2); opacity: 1; } 
        100% { transform: scale(0.95); opacity: 0.5; } 
    }

    .vault-subtitle { color: #64748b; font-size: 0.85rem; font-weight: 700; padding-left: 64px; }
    
    .toolbar-actions { display: flex; align-items: center; gap: 1rem; }
    .command-search { position: relative; display: flex; align-items: center; min-width: 340px; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .search-icon-wrap { position: absolute; left: 16px; color: #94a3b8; display: flex; align-items: center; pointer-events: none; transition: 0.3s; }
    .search-icon-wrap i { width: 18px; height: 18px; }
    
    .command-search input { 
        background: #f1f5f9; 
        border: 1px solid transparent; 
        padding: 12px 100px 12px 48px; 
        border-radius: 18px; 
        font-size: 0.95rem; 
        font-weight: 700; 
        color: #0f172a; 
        width: 100%; 
        outline: none; 
        transition: all 0.3s ease;
    }
    
    .command-search input:focus { 
        background: white; 
        border-color: #4f46e5; 
        box-shadow: 0 12px 30px rgba(79, 70, 229, 0.08); 
    }

    .command-search input:focus + .shortcut-hint { opacity: 0; transform: translateX(10px); }
    .command-search input:focus ~ .search-icon-wrap { color: #4f46e5; transform: scale(1.1); }

    .shortcut-hint { 
        position: absolute; 
        right: 12px; 
        display: flex; 
        align-items: center;
        gap: 6px; 
        pointer-events: none; 
        transition: 0.3s ease; 
    }
    .key-group {
        display: flex;
        align-items: center;
        background: #f1f5f9;
        padding: 2px;
        border-radius: 8px;
        gap: 2px;
        border: 1px solid #e2e8f0;
    }
    .shortcut-hint .key { 
        background: white; 
        border: 1px solid #e2e8f0; 
        border-bottom-width: 2px; 
        padding: 3px 6px; 
        border-radius: 6px; 
        font-size: 0.65rem; 
        font-weight: 900; 
        color: #64748b; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        min-width: 18px;
        text-align: center;
    }
    
    .btn-tool { border: none; padding: 12px; border-radius: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
    .btn-tool.primary { background: #0f172a; color: white; padding: 12px 24px; font-weight: 800; font-size: 0.85rem; gap: 10px; }
    .btn-tool.primary:hover { background: #4f46e5; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(79, 70, 229, 0.2); }
    .btn-tool.secondary { background: white; border: 1px solid #e2e8f0; color: #64748b; }
    .btn-tool.secondary:hover { background: #f8fafc; color: #0f172a; }

    /* Table precision Styles */
    .table-container { padding: 0.5rem 0; overflow-x: auto; }
    .precision-table { width: 100%; border-collapse: collapse; table-layout: fixed; min-width: 1000px; }
    
    .precision-table th { 
        text-align: left; 
        padding: 1.75rem 3rem; 
        font-size: 0.75rem; 
        font-weight: 900; 
        color: #94a3b8; 
        text-transform: uppercase; 
        letter-spacing: 0.15em; 
        border-bottom: 1px solid #f1f5f9;
        background: #fafcff;
    }

    /* Column Widths */
    .col-identity { width: 30%; }
    .col-clearance { width: 15%; }
    .col-sector { width: 15%; }
    .col-sync { width: 15%; }
    .col-ops { width: 10%; }
    
    .registry-row { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-bottom: 1px solid #f8fafc; }
    .registry-row:hover { background: #f8fbff; }
    .registry-row td { padding: 1.5rem 3rem; vertical-align: middle; }

    .identity-cell { display: flex; align-items: center; gap: 20px; }
    .avatar-capsule { 
        width: 52px; 
        height: 52px; 
        border-radius: 16px; 
        overflow: visible; 
        border: 4px solid white; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        flex-shrink: 0;
        position: relative;
    }
    .avatar-capsule img { width: 100%; height: 100%; object-fit: cover; border-radius: 12px; }
    
    .status-dot-mini {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 3px solid white;
        z-index: 10;
    }
    .status-dot-mini.online { background: #10b981; box-shadow: 0 0 12px rgba(16, 185, 129, 0.6); }
    .status-dot-mini.offline { background: #cbd5e1; }
    
    .id-meta { display: flex; flex-direction: column; gap: 4px; }
    .full-name { font-weight: 850; color: #0f172a; font-size: 1.05rem; letter-spacing: -0.02em; }
    .callsign { font-size: 0.75rem; color: #4f46e5; font-weight: 800; font-family: 'JetBrains Mono', monospace; opacity: 0.8; }

    .clearance-pill { 
        display: inline-flex; 
        align-items: center; 
        gap: 10px; 
        padding: 8px 16px; 
        border-radius: 12px; 
        font-size: 0.7rem; 
        font-weight: 900; 
        letter-spacing: 0.05em; 
        white-space: nowrap;
    }
    .clearance-pill.admin { background: #eef2ff; color: #4f46e5; border: 1px solid rgba(79, 70, 229, 0.1); }
    .clearance-pill.standard { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
    .clearance-pill .dot { width: 8px; height: 8px; border-radius: 50%; }
    .clearance-pill.admin .dot { background: #4f46e5; box-shadow: 0 0 10px rgba(79, 70, 229, 0.4); }
    .clearance-pill.standard .dot { background: #cbd5e1; }

    .sector-badge { 
        font-weight: 800; 
        color: #1e293b; 
        font-size: 0.9rem; 
        background: #f1f5f9; 
        padding: 6px 12px; 
        border-radius: 10px; 
        display: inline-block;
    }
    
    .sync-time { 
        font-size: 0.82rem; 
        color: #64748b; 
        font-weight: 800; 
        font-family: 'JetBrains Mono', monospace;
        background: #f1f5f9;
        padding: 6px 12px;
        border-radius: 10px;
        display: inline-block;
        border: 1px solid #e2e8f0;
    }

    .btn-purge { 
        width: 44px; 
        height: 44px; 
        border-radius: 14px; 
        border: 1px solid #fee2e2; 
        background: white; 
        color: #ef4444; 
        cursor: pointer; 
        transition: all 0.3s; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.05);
    }
    .btn-purge:hover { background: #ef4444; color: white; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(239, 68, 68, 0.2); }

    /* Custom Pagination Styling */
    .custom-pagination {
        display: flex;
        gap: 8px;
    }
    .page-btn {
        padding: 0.6rem 1.25rem;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        color: #4f46e5;
        font-weight: 800;
        font-size: 0.8rem;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .page-btn:hover:not(.disabled) {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }
    .page-btn.disabled {
        background: #f8fafc;
        color: #94a3b8;
        border-color: #e2e8f0;
        cursor: not-allowed;
        box-shadow: none;
    }
</style>

<script>
    jQuery(document).ready(function($) {
        $("#registrySearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#registryBody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // Hotkey for search
        $(document).keydown(function(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                $('#registrySearch').focus();
            }
        });
    });

    function openAddPersonnelModal() {
        const randomPass = 'Auth' + Math.floor(1000 + Math.random() * 9000);
        
        Swal.fire({
            title: '<div style="display: flex; align-items: center; gap: 15px; text-align: left;"><div style="width: 48px; height: 48px; background: #eef2ff; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #4338ca;"><i data-lucide="user-plus"></i></div><div><div style="font-size: 1.25rem; font-weight: 950; color: #0f172a;">Register Personnel</div><div style="font-size: 0.75rem; color: #64748b; font-weight: 700; margin-top: 2px;">STRATEGIC COMMAND PROVISIONING</div></div></div>',
            html: `
                <form id="addPersonnelForm" action="{{ route('admin.users.store') }}" method="POST" style="text-align: left; padding: 1rem 0.5rem;">
                    @csrf
                    <div style="margin-bottom: 1.5rem;">
                        <div class="swal-input-group">
                            <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px;">Username <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="username" class="swal2-input" placeholder="e.g. j_doe" style="width: 100%; margin: 0; height: 50px; border-radius: 12px; font-size: 0.9rem; font-weight: 700; border: 1px solid #e2e8f0;" required>
                        </div>
                    </div>

                    <div class="swal-input-group" style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px;">Security Access Key (Temporary Password)</label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="swal-password" class="swal2-input" value="${randomPass}" style="width: 100%; margin: 0; height: 50px; border-radius: 12px; font-size: 0.9rem; font-weight: 700; border: 1px solid #e2e8f0; padding-right: 50px;" required minlength="8" pattern="(?=.*\\d).{8,}" title="Minimum 8 characters, including at least one number">
                            <button type="button" onclick="toggleSwalPassword()" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                <i data-lucide="eye" id="swal-password-icon" style="width: 20px;"></i>
                            </button>
                        </div>
                        <p style="font-size: 0.65rem; color: #64748b; font-weight: 600; margin-top: 8px; padding-left: 4px;">Minimum 8 characters, including a number. Personnel will be required to update this key upon first synchronization.</p>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Initialize Registry',
            cancelButtonText: 'Abort',
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#f1f5f9',
            customClass: {
                popup: 'glass-monolith-popup',
                confirmButton: 'premium-swal-btn',
                cancelButton: 'premium-swal-cancel-btn'
            },
            width: '650px',
            didOpen: () => {
                lucide.createIcons();
            },
            preConfirm: () => {
                const form = document.getElementById('addPersonnelForm');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return false;
                }
                return true;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('addPersonnelForm').submit();
            }
        });
    }

    function toggleSwalPassword() {
        const input = document.getElementById('swal-password');
        const icon = document.getElementById('swal-password-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    }
</script>

<style>
    .glass-monolith-popup {
        border-radius: 35px !important;
        padding: 2.5rem !important;
        box-shadow: 0 40px 100px -20px rgba(0,0,0,0.15) !important;
    }
    .premium-swal-btn {
        height: 54px !important;
        padding: 0 40px !important;
        border-radius: 18px !important;
        font-weight: 900 !important;
        font-size: 0.9rem !important;
        letter-spacing: 0.02em !important;
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.25) !important;
    }
    .premium-swal-cancel-btn {
        height: 54px !important;
        padding: 0 30px !important;
        border-radius: 18px !important;
        font-weight: 800 !important;
        font-size: 0.9rem !important;
        color: #64748b !important;
    }

    /* Select2 Premium Styling */
    .select2-container--default .select2-selection--single {
        height: 50px !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        padding-left: 8px !important;
        font-size: 0.9rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        background: white !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 48px !important;
        right: 12px !important;
    }

    .select2-dropdown {
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        box-shadow: 0 20px 50px rgba(0,0,0,0.1) !important;
        overflow: hidden !important;
        z-index: 999999999 !important;
    }

    .select2-search__field {
        border-radius: 8px !important;
        padding: 8px 12px !important;
        border: 1px solid #e2e8f0 !important;
    }

    .select2-results__option {
        padding: 10px 15px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
    }

    .select2-results__option--highlighted[aria-selected] {
        background-color: #4f46e5 !important;
    }
</style>

<!-- Legacy Admin Audit Modal -->
<div class="modal-overlay" id="legacyAuditModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15,23,42,0.6); backdrop-filter: blur(8px); z-index: 99999; justify-content: center; align-items: center;">
    <div class="modal-container" style="background: white; border-radius: 24px; width: 100%; max-width: 900px; padding: 2.5rem; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
            <div style="display: flex; gap: 1rem; align-items: center;">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: #fffbeb; color: #d97706; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="history"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.25rem; font-weight: 900; color: #0f172a;">Legacy Admin Activity</h3>
                    <p style="margin: 4px 0 0; font-size: 0.85rem; color: #64748b; font-weight: 600;">Comprehensive audit trail of deactivated administrative accounts.</p>
                </div>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; justify-content: flex-end;">
                <div style="position: relative;">
                    <i data-lucide="search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #94a3b8;"></i>
                    <input type="text" id="legacySearchFilter" onkeyup="filterLegacyLogs()" placeholder="Search logs..." style="padding: 8px 12px 8px 30px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 0.85rem; font-weight: 600; color: #0f172a; outline: none; width: 200px; background: white;">
                </div>
                <select id="legacyTypeFilter" onchange="filterLegacyLogs()" style="padding: 8px 12px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 0.85rem; font-weight: 600; color: #475569; outline: none; cursor: pointer; background: white;">
                    <option value="all">All Event Types</option>
                    <option value="AUTHORIZATION">Approvals & Authorizations</option>
                    <option value="INVENTORY">Inventory Management</option>
                    <option value="SECURITY">Security / Users</option>
                    <option value="AUTH">Logins & Sessions</option>
                </select>
                @if(isset($legacyAdmins) && $legacyAdmins->count() > 0)
                <select id="legacyAdminFilter" onchange="filterLegacyLogs()" style="padding: 8px 12px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 0.85rem; font-weight: 600; color: #475569; outline: none; cursor: pointer; background: white;">
                    <option value="all">All Previous Admins</option>
                    @foreach($legacyAdmins as $admin)
                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                    @endforeach
                </select>
                @endif
                <button onclick="closeLegacyAuditModal()" style="background: #f1f5f9; border: none; width: 36px; height: 36px; border-radius: 10px; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; margin-left: 0.5rem;">
                    <i data-lucide="x" style="width: 18px;"></i>
                </button>
            </div>
        </div>
        
        <div style="max-height: 60vh; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 16px;">
            <div style="padding: 1.5rem; background: #fafcff; border-bottom: 1px solid #e2e8f0;">
                <h4 style="margin: 0; font-size: 0.9rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="list" style="width: 16px;"></i>
                    Activity Log Audit
                </h4>
            </div>
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f8fafc; position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th style="padding: 14px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem; font-weight: 800;">TIMESTAMP</th>
                        <th style="padding: 14px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem; font-weight: 800;">ADMINISTRATOR</th>
                        <th style="padding: 14px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem; font-weight: 800;">ACTION</th>
                        <th style="padding: 14px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem; font-weight: 800;">DETAILS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($legacyAdminLogs ?? [] as $log)
                    <tr class="legacy-log-row" data-admin-id="{{ $log->user_id }}" data-action="{{ $log->action }}" data-event="{{ $log->event_type }}" style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                        <td style="padding: 14px 16px; font-size: 0.8rem; font-weight: 600; color: #475569;">
                            {{ $log->created_at->format('d/m/y H:i:s') }}
                        </td>
                        <td style="padding: 14px 16px; font-size: 0.85rem; font-weight: 800; color: #0f172a;">
                            {{ $log->user->name ?? 'System' }}
                        </td>
                        <td style="padding: 14px 16px; font-size: 0.75rem; font-weight: 800; color: #4f46e5;">
                            {{ $log->action }}
                        </td>
                        <td style="padding: 14px 16px; font-size: 0.85rem; color: #64748b; line-height: 1.5;">
                            {{ $log->description }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 3rem; color: #94a3b8; font-weight: 600;">No legacy records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function openLegacyAuditModal() {
        document.getElementById('legacyAuditModal').style.display = 'flex';
    }
    function closeLegacyAuditModal() {
        document.getElementById('legacyAuditModal').style.display = 'none';
    }
    
    function viewUserDetails(user) {
        Swal.fire({
            title: `
                <div style="text-align: left; position: relative; margin-bottom: 5px;">
                    <div style="position: absolute; top: -15px; left: -15px; right: -15px; height: 80px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); opacity: 0.05; border-radius: 24px 24px 0 0;"></div>
                    <div style="display: flex; align-items: center; gap: 20px; position: relative; z-index: 1; padding: 5px 0;">
                        <div style="position: relative;">
                            <img src="${user.avatar}" style="width: 72px; height: 72px; border-radius: 22px; border: 4px solid white; box-shadow: 0 15px 30px rgba(79, 70, 229, 0.15); object-fit: cover;">
                            <div style="position: absolute; bottom: -5px; right: -5px; width: 22px; height: 22px; border-radius: 50%; border: 3px solid white; background: ${user.status === 'ACTIVE' ? '#10b981' : '#ef4444'}; box-shadow: 0 4px 10px rgba(0,0,0,0.1);"></div>
                        </div>
                        <div>
                            <div style="font-size: 1.4rem; font-weight: 950; color: #0f172a; letter-spacing: -0.03em; line-height: 1.1;">${user.name}</div>
                            <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                                <span style="font-size: 0.75rem; font-weight: 800; color: #4f46e5; background: #eef2ff; padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.02em;">@${user.username}</span>
                                <span style="font-size: 0.75rem; font-weight: 800; color: #64748b; opacity: 0.6;">•</span>
                                <span style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">ID: 00${Math.floor(Math.random() * 900) + 100}</span>
                            </div>
                        </div>
                    </div>
                </div>`,
            html: `
                <div style="padding: 5px; text-align: left;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <!-- Primary Info Cards -->
                        <div style="grid-column: span 2; background: #f8fafc; padding: 18px; border-radius: 20px; border: 1px solid #f1f5f9; position: relative; overflow: hidden;">
                             <label style="display: flex; align-items: center; gap: 6px; font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.05em;">
                                <i data-lucide="mail" style="width: 12px; height: 12px;"></i> Email Address
                             </label>
                             <div style="font-size: 0.95rem; font-weight: 800; color: #1e293b; word-break: break-all;">${user.email}</div>
                        </div>

                        <div style="background: #f8fafc; padding: 18px; border-radius: 20px; border: 1px solid #f1f5f9;">
                             <label style="display: flex; align-items: center; gap: 6px; font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.05em;">
                                <i data-lucide="phone" style="width: 12px; height: 12px;"></i> Comms Line
                             </label>
                             <div style="font-size: 0.9rem; font-weight: 800; color: #1e293b;">${user.phone}</div>
                        </div>

                        <div style="background: #f8fafc; padding: 18px; border-radius: 20px; border: 1px solid #f1f5f9;">
                             <label style="display: flex; align-items: center; gap: 6px; font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.05em;">
                                <i data-lucide="building" style="width: 12px; height: 12px;"></i> Sector Unit
                             </label>
                             <div style="font-size: 0.9rem; font-weight: 800; color: #4f46e5;">${user.department}</div>
                        </div>

                        <div style="background: #f8fafc; padding: 18px; border-radius: 20px; border: 1px solid #f1f5f9;">
                             <label style="display: flex; align-items: center; gap: 6px; font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.05em;">
                                <i data-lucide="shield-check" style="width: 12px; height: 12px;"></i> Access Level
                             </label>
                             <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="font-size: 0.85rem; font-weight: 950; color: ${user.role === 'Admin' ? '#ef4444' : '#10b981'};">
                                    ${user.role.toUpperCase()}
                                </span>
                             </div>
                        </div>

                        <div style="background: #f8fafc; padding: 18px; border-radius: 20px; border: 1px solid #f1f5f9;">
                             <label style="display: flex; align-items: center; gap: 6px; font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.05em;">
                                <i data-lucide="activity" style="width: 12px; height: 12px;"></i> Account Status
                             </label>
                             <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: ${user.status === 'ACTIVE' ? '#10b981' : '#ef4444'}; box-shadow: 0 0 10px ${user.status === 'ACTIVE' ? 'rgba(16, 185, 129, 0.4)' : 'rgba(239, 68, 68, 0.4)'};"></div>
                                <span style="font-size: 0.85rem; font-weight: 950; color: #1e293b;">${user.status}</span>
                             </div>
                        </div>
                    </div>
                    
                    <div style="background: #f8fafc; padding: 18px 24px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #f1f5f9;">
                        <div>
                            <div style="font-size: 0.6rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.08em;">Last Login Time</div>
                            <div style="font-size: 0.85rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                                <i data-lucide="clock" style="width: 14px; color: #4f46e5;"></i>
                                ${user.last_login}
                            </div>
                        </div>
                        <div style="width: 40px; height: 40px; background: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #4f46e5; border: 1px solid #f1f5f9;">
                            <i data-lucide="terminal" style="width: 20px;"></i>
                        </div>
                    </div>
                </div>
            `,
            didOpen: () => {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            },
            showCloseButton: true,
            showConfirmButton: false,
            width: '680px',
            padding: '1.5rem',
            background: '#ffffff',
            customClass: {
                popup: 'glass-monolith',
            }
        });
    }

    function filterLegacyLogs() {
        const adminFilter = document.getElementById('legacyAdminFilter');
        const adminId = adminFilter ? adminFilter.value : 'all';
        
        const typeFilter = document.getElementById('legacyTypeFilter');
        const typeVal = typeFilter ? typeFilter.value : 'all';
        
        const searchFilter = document.getElementById('legacySearchFilter');
        const searchVal = searchFilter ? searchFilter.value.toLowerCase() : '';

        const rows = document.querySelectorAll('.legacy-log-row');
        rows.forEach(row => {
            const rowAdminId = row.dataset.adminId;
            const rowAction = row.dataset.action;
            const rowEvent = row.dataset.event;
            const textContent = row.textContent.toLowerCase();

            let matchAdmin = (adminId === 'all' || rowAdminId === adminId);
            let matchType = (typeVal === 'all' || rowAction === typeVal || rowEvent === typeVal);
            let matchSearch = (searchVal === '' || textContent.includes(searchVal));

            if (matchAdmin && matchType && matchSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endsection
