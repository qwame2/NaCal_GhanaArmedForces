@extends('layouts.admin')

@section('title', 'Registry Oversight')

@section('content')
<div class="command-center">
    <!-- Precision Metrics -->
    <div class="metrics-row">
        <div class="metric-module">
            <div class="metric-visual primary">
                <i data-lucide="users"></i>
            </div>
            <div class="metric-data">
                <span class="m-label">Total Registry</span>
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
                @php
                    $onlineCount = $users->where('is_online', true)->count();
                @endphp
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
                        <h3 class="vault-title">Registry Oversight</h3>
                        <div class="status-indicator-pill">
                            <span class="live-pulse"></span>
                            SECURE & SYNCED
                        </div>
                    </div>
                </div>
                <p class="vault-subtitle">Managing {{ $totalUsers }} Strategic Personnel Records</p>
            </div>
            
            <div class="toolbar-actions">
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
                        <th class="col-identity">OPERATIONAL IDENTITY</th>
                        <th class="col-clearance">CLASSIFICATION</th>
                        <th class="col-sector">SECTOR</th>
                        <th class="col-sync">LOGIN TIME</th>
                        <th class="col-sync">LOGOUT TIME</th>
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
                                    ADMIN-ALPHA
                                </div>
                            @else
                                <div class="clearance-pill standard">
                                    <div class="dot"></div>
                                    PERSONNEL
                                </div>
                            @endif
                        </td>
                        <td><span class="sector-badge">{{ $user->department ?? 'UNASSIGNED' }}</span></td>
                        <td>
                            <span class="sync-time" style="color: #10b981; font-weight: 800;">
                                {{ $user->last_login_at ? $user->last_login_at->format('M d, H:i') : 'NO RECORD' }}
                            </span>
                        </td>
                        <td>
                            <span class="sync-time" style="color: #64748b; font-weight: 800;">
                                {{ $user->last_logout_at ? $user->last_logout_at->format('M d, H:i') : 'NO RECORD' }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div class="ops-cluster" style="justify-content: center; display: flex; align-items: center; gap: 10px;">
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
        font-size: 0.85rem; 
        color: #94a3b8; 
        font-weight: 700; 
        font-family: 'JetBrains Mono', monospace;
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
</style>

<script>
    $(document).ready(function() {
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
</script>
@endsection
