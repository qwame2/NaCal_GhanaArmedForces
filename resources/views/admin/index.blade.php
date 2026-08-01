@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
<div class="command-center">
    <!-- Premium Precision Metrics Grid -->
    <div class="metrics-grid">
        <div class="metric-card-premium primary">
            <div class="metric-glow"></div>
            <div class="metric-header">
                <span class="metric-title">Total Registry</span>
                <div class="metric-icon-box">
                    <i data-lucide="users"></i>
                </div>
            </div>
            <div class="metric-body">
                <h3 class="metric-number">{{ $totalUsers }}</h3>
                <span class="metric-badge"><i data-lucide="database"></i> Records Synced</span>
            </div>
        </div>

        <div class="metric-card-premium success">
            <div class="metric-glow"></div>
            <div class="metric-header">
                <span class="metric-title">Active Sessions</span>
                <div class="metric-icon-box pulsing">
                    <i data-lucide="activity"></i>
                </div>
            </div>
            <div class="metric-body">
                <h3 class="metric-number">{{ $onlineCount }}</h3>
                <span class="metric-badge live"><span class="pulse-dot"></span> Live Now</span>
            </div>
        </div>

        <div class="metric-card-premium info">
            <div class="metric-glow"></div>
            <div class="metric-header">
                <span class="metric-title">Head Clearances</span>
                <div class="metric-icon-box">
                    <i data-lucide="shield-check"></i>
                </div>
            </div>
            <div class="metric-body">
                <h3 class="metric-number">{{ $allUsers->where('is_admin', true)->count() }}</h3>
                <span class="metric-badge status-secure">SECURE ACCESS</span>
            </div>
        </div>
    </div>

    <!-- Redesigned Security Registry Vault -->
    <div class="registry-vault-card">
        <div class="vault-header">
            <div class="vault-branding">
                <div class="brand-group">
                    <div class="brand-icon-wrapper">
                        <i data-lucide="fingerprint"></i>
                    </div>
                    <div>
                        <h2 class="vault-headline">Personnel Access Registry</h2>
                        <p class="vault-tagline">Managing {{ $totalUsers }} active security credentials</p>
                    </div>
                </div>
            </div>

            <!-- Toolbar actions containing filters and toggles -->
            <form method="GET" action="{{ route('admin.index') }}" class="vault-controls" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;margin:0;">
                <!-- Keep existing per_page if present -->
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

                @if(isset($legacyAdmins) && $legacyAdmins->count() > 0)
                <button type="button" class="btn-action-legacy" onclick="openLegacyAuditModal()">
                    <i data-lucide="history"></i>
                    <span>Legacy Audit</span>
                </button>
                @endif

                <div class="search-field-wrapper">
                    <i data-lucide="search" class="search-field-icon"></i>
                    <input type="text" name="search" id="registrySearch" value="{{ request('search') }}" placeholder="Search name, username, department...">
                    <div class="shortcut-pill">
                        <span>Enter</span>
                    </div>
                </div>

                <div class="filter-field-wrapper-admin" style="position:relative; display:flex; align-items:center; border:1px solid rgba(0,0,0,0.08); border-radius:14px; padding:0 12px; background:#f1f5f9; height: 42px; gap: 8px;">
                    <i data-lucide="filter" style="width:16px; height:16px; color:var(--text-slate-muted);"></i>
                    <select name="role_filter" id="registryRoleFilter" onchange="this.form.submit()" style="border:none; background:transparent; font-size:0.85rem; font-weight:700; color:var(--text-slate-dark); outline:none; cursor:pointer; padding-right: 8px; font-family: inherit;">
                        <option value="all" {{ request('role_filter') === 'all' ? 'selected' : '' }}>All Roles</option>
                        <option value="requisitioner" {{ request('role_filter') === 'requisitioner' ? 'selected' : '' }}>Requisitioner</option>
                        <option value="heads" {{ request('role_filter') === 'heads' ? 'selected' : '' }}>Heads</option>
                        <option value="authorizers" {{ request('role_filter') === 'authorizers' ? 'selected' : '' }}>Authorizers</option>
                        <option value="store-officers" {{ request('role_filter') === 'store-officers' ? 'selected' : '' }}>Store Officers</option>
                    </select>
                </div>

                @if(request('search') || (request('role_filter') && request('role_filter') !== 'all'))
                <a href="{{ route('admin.index', ['per_page' => request('per_page', 10)]) }}" class="btn-action-legacy" style="background:#f1f5f9; border-color:#cbd5e1; color:var(--text-slate-dark); text-decoration:none; padding:10px 15px; font-size:0.8rem; border-radius:12px; display:inline-flex; align-items:center; gap:5px; height:42px; box-sizing:border-box;">
                    <i data-lucide="refresh-cw" style="width:14px;height:14px;"></i> Clear Filters
                </a>
                @endif

                <div class="view-toggle-capsule">
                    <button type="button" class="toggle-btn active" id="btnViewTable" onclick="toggleLayout('table')" title="Table View">
                        <i data-lucide="list"></i>
                    </button>
                    <button type="button" class="toggle-btn" id="btnViewGrid" onclick="toggleLayout('grid')" title="Grid View">
                        <i data-lucide="grid-3x3"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- 1. Redesigned Table View -->
        <div id="tableLayoutView" class="layout-container">
            <div class="table-scroller">
                <table class="premium-table-layout">
                    <thead>
                        <tr>
                            <th class="w-identity">Personnel Name</th>
                            <th class="w-clearance">Access Level</th>
                            <th class="w-sector">Department Unit</th>
                            <th class="w-sync">Last Login</th>
                            <th class="w-sync">Last Logout</th>
                            <th class="w-ops">Access Control</th>
                        </tr>
                    </thead>
                    <tbody id="registryBody">
                        @foreach($users as $user)
                        @php
                            $roleLower = strtolower($user->role ?? '');
                            $roleCat = 'other';
                            if ($roleLower === 'requisitioner' || $roleLower === 'requisitioners') {
                                $roleCat = 'requisitioner';
                            } elseif ($roleLower === 'department head' || $roleLower === 'dept head' || $roleLower === 'it head') {
                                $roleCat = 'heads';
                            } elseif (in_array($roleLower, ['main admin', 'sub main admin', 'auditor', 'external auditor', 'director general'])) {
                                $roleCat = 'authorizers';
                            } elseif ($roleLower === 'officer' || $roleLower === 'head of stores') {
                                $roleCat = 'store-officers';
                            }
                        @endphp
                        <tr class="user-row-item" data-role-category="{{ $roleCat }}">
                            <td>
                                <div class="personnel-cell">
                                    <div class="avatar-container">
                                        <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2364748b'><circle cx='12' cy='8' r='4'/><path d='M12 14c-4.42 0-8 3.58-8 8h16c0-4.42-3.58-8-8-8z'/></svg>" }}" class="user-avatar-img">
                                        <span class="user-status-indicator {{ $user->is_online ? 'online' : 'offline' }}"></span>
                                    </div>
                                    <div class="personnel-names">
                                        <span class="p-name">{{ $user->name }}</span>
                                        <span class="p-username">@ {{ $user->username }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->role === 'Main Admin')
                                    <span class="badge-role main-admin">
                                        <span class="badge-dot"></span> Head of Admin (Auth) {{ $user->rank ? '(' . $user->rank . ')' : '' }}
                                    </span>
                                @elseif($user->role === 'Sub Main Admin')
                                    <span class="badge-role sub-admin">
                                        <span class="badge-dot"></span> Delegators (Auth) {{ $user->rank ? '(' . $user->rank . ')' : '' }}
                                    </span>
                                @elseif($user->is_admin)
                                    <span class="badge-role admin">
                                        <span class="badge-dot"></span> Head of Admin
                                    </span>
                                @elseif($user->role === 'Department Head')
                                    <span class="badge-role dept-head">
                                        <span class="badge-dot"></span>
                                        @if($user->department === 'Human Resource Management Department')
                                            Dept Head HR {{ $user->rank ? '(' . $user->rank . ')' : '' }}
                                        @elseif($user->department === 'Welfare Department')
                                            Head of Welfare {{ $user->rank ? '(' . $user->rank . ')' : '' }}
                                        @else
                                            Dept Head {{ $user->rank ? '(' . $user->rank . ')' : '' }}
                                        @endif
                                    </span>
                                @elseif($user->role === 'Officer')
                                    <span class="badge-role store-officer">
                                        <span class="badge-dot"></span> Store Officer
                                    </span>
                                @elseif($user->role === 'Requisitioner' || $user->role === 'Requisitioners')
                                    <span class="badge-role requisitioner">
                                        <span class="badge-dot"></span> Requisitioner
                                    </span>
                                @else
                                    <span class="badge-role standard">
                                        <span class="badge-dot"></span> {{ $user->role ?? 'Staff' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="dept-badge-pill">{{ $user->department ?? 'UNASSIGNED' }}</span>
                            </td>
                            <td>
                                <span class="time-stamp-badge login">
                                    {{ $user->last_login_at ? $user->last_login_at->format('d/m/y H:i') : 'NO RECORD' }}
                                </span>
                            </td>
                            <td>
                                <span class="time-stamp-badge logout">
                                    {{ $user->last_logout_at ? $user->last_logout_at->format('d/m/y H:i') : 'NO RECORD' }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons-wrap">
                                    <button type="button" class="btn-icon-action view" title="View Profile Credentials"
                                        onclick="viewUserDetails({
                                            id: '{{ $user->id }}',
                                            name: '{{ addslashes($user->name) }}',
                                            username: '{{ addslashes($user->username) }}',
                                            email: '{{ addslashes($user->email ?? 'Not Provided') }}',
                                            phone: '{{ addslashes($user->phone ?? 'Not Provided') }}',
                                            department: '{{ addslashes($user->department ?? 'UNASSIGNED') }}',
                                            role: '{{ addslashes($user->role) }}',
                                            rank: '{{ addslashes($user->rank ?? '') }}',
                                            last_login: '{{ $user->last_login_at ? $user->last_login_at->format('d/m/y H:i') : 'No record' }}',
                                            last_logout: '{{ $user->last_logout_at ? $user->last_logout_at->format('d/m/y H:i') : 'No record' }}',
                                            status: '{{ $user->is_active ? 'ACTIVE' : 'DEACTIVATED' }}',
                                            avatar: '{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}'
                                        })">
                                        <i data-lucide="eye"></i>
                                    </button>

                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.toggle_status', $user->id) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('PATCH')
                                        @if($user->is_active)
                                        <button type="submit" class="btn-icon-action deactivate" title="Deactivate Access">
                                            <i data-lucide="power"></i>
                                        </button>
                                        @else
                                        <button type="submit" class="btn-icon-action activate" title="Reactivate Access">
                                            <i data-lucide="play-circle"></i>
                                        </button>
                                        @endif
                                    </form>
                                    @endif

                                    @if(!$user->is_active)
                                    <span class="tag-inactive">BLOCKED</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Premium Grid View (Card Mode) -->
        <div id="gridLayoutView" class="layout-container" style="display: none;">
            <div class="cards-grid-layout" id="registryGrid">
                @foreach($users as $user)
                @php
                    $roleLower = strtolower($user->role ?? '');
                    $roleCat = 'other';
                    if ($roleLower === 'requisitioner' || $roleLower === 'requisitioners') {
                        $roleCat = 'requisitioner';
                    } elseif ($roleLower === 'department head' || $roleLower === 'dept head' || $roleLower === 'it head') {
                        $roleCat = 'heads';
                    } elseif (in_array($roleLower, ['main admin', 'sub main admin', 'auditor', 'external auditor', 'director general'])) {
                        $roleCat = 'authorizers';
                    } elseif ($roleLower === 'officer' || $roleLower === 'head of stores') {
                        $roleCat = 'store-officers';
                    }
                @endphp
                <div class="personnel-card-item {{ !$user->is_active ? 'inactive-mode' : '' }}" data-role-category="{{ $roleCat }}">
                    <div class="card-status-indicator {{ $user->is_online ? 'online' : 'offline' }}"></div>
                    
                    <div class="card-profile-header">
                        <div class="card-avatar-wrapper">
                            <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2364748b'><circle cx='12' cy='8' r='4'/><path d='M12 14c-4.42 0-8 3.58-8 8h16c0-4.42-3.58-8-8-8z'/></svg>" }}" class="card-avatar">
                        </div>
                        <div class="card-title-stack">
                            <h4 class="card-display-name">{{ $user->name }}</h4>
                            <span class="card-display-username">@ {{ $user->username }}</span>
                        </div>
                    </div>

                    <div class="card-profile-details">
                        <div class="detail-row">
                            <i data-lucide="shield"></i>
                            <span class="detail-text">
                                @if($user->role === 'Main Admin') Head of Admin (Auth)
                                @elseif($user->role === 'Sub Main Admin') Delegators (Auth)
                                @elseif($user->is_admin) Head of Admin
                                @elseif($user->role === 'Department Head') Dept Head
                                @elseif($user->role === 'Officer') Store Officer
                                @elseif($user->role === 'Requisitioner') Requisitioner
                                @else {{ $user->role }} @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <i data-lucide="building"></i>
                            <span class="detail-text truncate-text" title="{{ $user->department }}">{{ $user->department ?? 'UNASSIGNED' }}</span>
                        </div>
                        <div class="detail-row">
                            <i data-lucide="log-in"></i>
                            <span class="detail-text text-mono">{{ $user->last_login_at ? $user->last_login_at->format('d/m/y H:i') : 'No login record' }}</span>
                        </div>
                    </div>

                    <div class="card-actions-wrapper">
                        <button type="button" class="btn-card-action view" onclick="viewUserDetails({
                            id: '{{ $user->id }}',
                            name: '{{ addslashes($user->name) }}',
                            username: '{{ addslashes($user->username) }}',
                            email: '{{ addslashes($user->email ?? 'Not Provided') }}',
                            phone: '{{ addslashes($user->phone ?? 'Not Provided') }}',
                            department: '{{ addslashes($user->department ?? 'UNASSIGNED') }}',
                            role: '{{ addslashes($user->role) }}',
                            rank: '{{ addslashes($user->rank ?? '') }}',
                            last_login: '{{ $user->last_login_at ? $user->last_login_at->format('d/m/y H:i') : 'No record' }}',
                            last_logout: '{{ $user->last_logout_at ? $user->last_logout_at->format('d/m/y H:i') : 'No record' }}',
                            status: '{{ $user->is_active ? 'ACTIVE' : 'DEACTIVATED' }}',
                            avatar: '{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}'
                        })">
                            <i data-lucide="eye"></i> Details
                        </button>

                        @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.toggle_status', $user->id) }}" method="POST" class="inline-form flex-grow">
                            @csrf
                            @method('PATCH')
                            @if($user->is_active)
                            <button type="submit" class="btn-card-action block">
                                <i data-lucide="power"></i> Suspend
                            </button>
                            @else
                            <button type="submit" class="btn-card-action unblock">
                                <i data-lucide="play-circle"></i> Activate
                            </button>
                            @endif
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Dynamic Registry Pagination controls -->
        <div class="registry-pagination-footer">
            <div class="pagination-meta-info">
                <div>
                    Showing <span class="val-highlight">{{ $users->firstItem() ?? 0 }}</span> to
                    <span class="val-highlight">{{ $users->lastItem() ?? 0 }}</span> of
                    <span class="val-highlight">{{ $users->total() }}</span> Personnel Records
                </div>

                <form action="{{ route('admin.index') }}" method="GET" class="per-page-form">
                    <span class="rows-label">Rows per page:</span>
                    <select name="per_page" onchange="this.form.submit()" class="select-per-page">
                        @foreach([10, 25, 50, 100] as $count)
                            <option value="{{ $count }}" {{ request('per_page') == $count ? 'selected' : '' }}>{{ $count }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            
            <div class="pagination-nav-buttons">
                @if ($users->onFirstPage())
                <span class="btn-page-nav disabled"><i data-lucide="chevron-left"></i> Previous</span>
                @else
                <a href="{{ $users->appends(request()->query())->previousPageUrl() }}" class="btn-page-nav"><i data-lucide="chevron-left"></i> Previous</a>
                @endif

                @if ($users->hasMorePages())
                <a href="{{ $users->appends(request()->query())->nextPageUrl() }}" class="btn-page-nav">Next <i data-lucide="chevron-right"></i></a>
                @else
                <span class="btn-page-nav disabled">Next <i data-lucide="chevron-right"></i></span>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom Variables & Aesthetic Colors */
    :root {
        --primary: #6366f1;
        --primary-light: #818cf8;
        --primary-dark: #4f46e5;
        --primary-glow: rgba(99, 102, 241, 0.12);
        --secondary: #10b981;
        --accent: #f59e0b;
        --danger: #ef4444;
        --bg-main: #f3f4f6;
        --bg-sidebar: #ffffff;
        --bg-card: #ffffff;
        --bg-nav: rgba(255, 255, 255, 0.8);
        --text-main: #0f172a;
        --text-muted: #64748b;
        --border-color: #f1f5f9;
        --sidebar-width: 320px;
        --sidebar-mini-width: 88px;
        --header-height: 80px;
        --radius-xl: 1.5rem;
        --radius-lg: 1rem;
        --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --card-shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);

        --nococ-emerald: var(--primary);
        --nococ-emerald-dark: var(--primary-dark);
        --nococ-emerald-light: var(--primary-glow);
        --nococ-emerald-glow: var(--primary-glow);
        --text-slate-dark: #0f172a;
        --text-slate-muted: #64748b;
        --border-slate-light: #f1f5f9;
        --radius-luxe: 24px;
        --radius-card: 16px;
        --transition-swift: var(--transition);
    }

    @keyframes adminFadeInUp {
        0% {
            opacity: 0;
            transform: translateY(22px) scale(0.985);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes adminRowFade {
        0% {
            opacity: 0;
            transform: translateY(6px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .command-center {
        max-width: 100% !important;
        padding: 0 1rem;
        display: flex;
        flex-direction: column;
        gap: 2.25rem;
    }

    /* Metrics Grid Redesign */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }

    @media (max-width: 1024px) {
        .metrics-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 640px) {
        .metrics-grid {
            grid-template-columns: 1fr;
        }
        .vault-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 1.5rem !important;
            padding: 1.5rem !important;
        }
        .vault-controls {
            width: 100%;
            flex-direction: column;
            align-items: stretch !important;
        }
        .search-field-wrapper {
            max-width: 100% !important;
            width: 100%;
        }
    }

    .metric-card-premium {
        background: #ffffff;
        border-radius: var(--radius-luxe);
        border: 1px solid var(--border-slate-light);
        padding: 1.75rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        transition: var(--transition-swift);
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .metric-card-premium:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 45px rgba(5, 150, 105, 0.08);
        border-color: rgba(5, 150, 105, 0.2);
    }

    .metric-glow {
        position: absolute;
        top: -50%;
        right: -50%;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(5, 150, 105, 0.08) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .metric-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .metric-title {
        font-size: 0.8rem;
        font-weight: 800;
        color: var(--text-slate-muted);
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .metric-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--nococ-emerald-light);
        color: var(--nococ-emerald);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition-swift);
    }

    .metric-icon-box i {
        width: 20px;
        height: 20px;
    }

    .metric-card-premium:hover .metric-icon-box {
        background: var(--nococ-emerald);
        color: #ffffff;
        transform: rotate(5deg) scale(1.05);
    }

    .metric-number {
        font-size: 2.25rem;
        font-weight: 950;
        color: var(--text-slate-dark);
        margin: 0;
        letter-spacing: -0.04em;
        line-height: 1.1;
    }

    .metric-badge {
        font-size: 0.75rem;
        font-weight: 800;
        color: var(--nococ-emerald);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .metric-badge.live {
        color: #059669;
    }

    .metric-badge.status-secure {
        background: var(--nococ-emerald-light);
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.65rem;
        border: 1px solid rgba(5, 150, 105, 0.12);
        letter-spacing: 0.04em;
        width: fit-content;
    }

    .pulse-dot {
        width: 8px;
        height: 8px;
        background: var(--nococ-emerald);
        border-radius: 50%;
        animation: pulse-active 1.5s infinite;
    }

    @keyframes pulse-active {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(5, 150, 105, 0.7); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 6px rgba(5, 150, 105, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(5, 150, 105, 0); }
    }

    /* Registry Vault Card */
    .registry-vault-card {
        background: #ffffff;
        border-radius: var(--radius-luxe);
        border: 1px solid var(--border-slate-light);
        box-shadow: 0 15px 40px rgba(0,0,0,0.02);
        overflow: hidden;
    }

    .vault-header {
        padding: 2rem 2.25rem;
        background: #fafcff;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .brand-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: var(--nococ-emerald-light);
        color: var(--nococ-emerald);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 0 12px rgba(5, 150, 105, 0.02);
    }

    .brand-icon-wrapper i {
        width: 22px;
        height: 22px;
    }

    .brand-group {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .vault-headline {
        font-size: 1.3rem;
        font-weight: 900;
        color: var(--text-slate-dark);
        margin: 0;
        letter-spacing: -0.03em;
    }

    .vault-tagline {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-slate-muted);
        margin: 2px 0 0;
    }

    .vault-controls {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-action-legacy {
        background: #ecfdf5;
        color: #047857;
        border: 1.5px solid #a7f3d0;
        border-radius: 14px;
        padding: 10px 20px;
        font-size: 0.82rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: var(--transition-swift);
    }

    .btn-action-legacy:hover {
        background: #047857;
        color: #ffffff;
        border-color: #047857;
        transform: translateY(-2px);
    }

    .search-field-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        max-width: 320px;
        flex-grow: 1;
    }

    .search-field-icon {
        position: absolute;
        left: 14px;
        color: var(--text-slate-muted);
        width: 18px;
        height: 18px;
        pointer-events: none;
    }

    .search-field-wrapper input {
        background: #f1f5f9;
        border: 1px solid transparent;
        border-radius: 14px;
        padding: 10px 80px 10px 42px;
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-slate-dark);
        width: 100%;
        outline: none;
        transition: var(--transition-swift);
    }

    .search-field-wrapper input:focus {
        background: #ffffff;
        border-color: var(--nococ-emerald);
        box-shadow: 0 10px 25px rgba(5, 150, 105, 0.05);
    }

    .shortcut-pill {
        position: absolute;
        right: 12px;
        background: #ffffff;
        border: 1px solid var(--border-slate-light);
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.65rem;
        font-weight: 800;
        color: var(--text-slate-muted);
        pointer-events: none;
    }

    .view-toggle-capsule {
        background: #f1f5f9;
        border-radius: 12px;
        padding: 4px;
        display: flex;
        gap: 4px;
    }

    .toggle-btn {
        background: transparent;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-slate-muted);
        cursor: pointer;
        transition: var(--transition-swift);
    }

    .toggle-btn.active {
        background: #ffffff;
        color: var(--nococ-emerald);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    /* Premium Table Layout Styling */
    .table-scroller {
        overflow-x: auto;
    }

    .premium-table-layout {
        width: 100%;
        border-collapse: collapse;
        min-width: 950px;
    }

    .premium-table-layout th {
        background: #fafcff;
        padding: 1.25rem 2rem;
        font-size: 0.72rem;
        font-weight: 900;
        color: var(--text-slate-muted);
        text-transform: uppercase;
        letter-spacing: 0.12em;
        text-align: left;
        border-bottom: 1.5px solid var(--border-slate-light);
    }

    /* Column Width Limits */
    .w-identity { width: 28%; }
    .w-clearance { width: 20%; }
    .w-sector { width: 18%; }
    .w-sync { width: 15%; }
    .w-ops { width: 19%; }

    .user-row-item {
        border-bottom: 1px solid #f8fafc;
        transition: var(--transition-swift);
    }

    .user-row-item:hover {
        background: #fdfefe;
    }

    .user-row-item td {
        padding: 1.25rem 2rem;
        vertical-align: middle;
    }

    .personnel-cell {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .avatar-container {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        position: relative;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .user-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 12px;
    }

    .user-status-indicator {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2.5px solid #ffffff;
    }

    .user-status-indicator.online { background: #10b981; }
    .user-status-indicator.offline { background: #cbd5e1; }

    .personnel-names {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .p-name {
        font-weight: 850;
        color: var(--text-slate-dark);
        font-size: 0.95rem;
    }

    .p-username {
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--nococ-emerald);
    }

    /* Badges & Pills */
    .badge-role {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 900;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }

    .badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .badge-role.main-admin { background: #ecfdf5; color: #047857; border: 1px solid rgba(4, 120, 87, 0.12); }
    .badge-role.main-admin .badge-dot { background: #059669; }

    .badge-role.sub-admin { background: #ecfdf5; color: #047857; border: 1px solid rgba(4, 120, 87, 0.12); }
    .badge-role.sub-admin .badge-dot { background: #059669; }

    .badge-role.admin { background: #ecfdf5; color: #047857; border: 1px solid rgba(4, 120, 87, 0.12); }
    .badge-role.admin .badge-dot { background: #059669; }

    .badge-role.dept-head { background: #ecfdf5; color: #047857; border: 1px solid rgba(4, 120, 87, 0.12); }
    .badge-role.dept-head .badge-dot { background: #059669; }

    .badge-role.store-officer { background: #fff7ed; color: #c2410c; border: 1px solid rgba(194, 65, 12, 0.08); }
    .badge-role.store-officer .badge-dot { background: #f97316; }

    .badge-role.requisitioner { background: #faf5ff; color: #6d28d9; border: 1px solid rgba(109, 40, 217, 0.08); }
    .badge-role.requisitioner .badge-dot { background: #8b5cf6; }

    .badge-role.standard { background: #f8fafc; color: #64748b; border: 1px solid var(--border-slate-light); }
    .badge-role.standard .badge-dot { background: #94a3b8; }

    .dept-badge-pill {
        font-weight: 800;
        color: #334155;
        font-size: 0.8rem;
        background: #f1f5f9;
        padding: 5px 12px;
        border-radius: 8px;
        display: inline-block;
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .time-stamp-badge {
        font-size: 0.78rem;
        font-family: monospace;
        font-weight: 800;
        padding: 5px 10px;
        border-radius: 8px;
        display: inline-block;
        border: 1px solid var(--border-slate-light);
    }

    .time-stamp-badge.login { background: #f0fdf4; color: #16a34a; border-color: rgba(22, 163, 74, 0.08); }
    .time-stamp-badge.logout { background: #f8fafc; color: var(--text-slate-muted); }

    .action-buttons-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-icon-action {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        border: 1px solid var(--border-slate-light);
        background: #ffffff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition-swift);
        box-shadow: 0 2px 6px rgba(0,0,0,0.01);
    }

    .btn-icon-action i {
        width: 16px;
        height: 16px;
    }

    .btn-icon-action.view { color: var(--nococ-emerald); background: var(--nococ-emerald-light); border-color: rgba(5, 150, 105, 0.12); }
    .btn-icon-action.view:hover { background: var(--nococ-emerald); color: #ffffff; transform: translateY(-2px); }

    .btn-icon-action.deactivate { color: #f97316; background: #fff7ed; border-color: rgba(249, 115, 22, 0.12); }
    .btn-icon-action.deactivate:hover { background: #f97316; color: #ffffff; transform: translateY(-2px); }

    .btn-icon-action.activate { color: var(--nococ-emerald); background: var(--nococ-emerald-light); border-color: rgba(5, 150, 105, 0.12); }
    .btn-icon-action.activate:hover { background: var(--nococ-emerald); color: #ffffff; transform: translateY(-2px); }

    .tag-inactive {
        background: #fef2f2;
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.15);
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.62rem;
        font-weight: 900;
        letter-spacing: 0.05em;
    }

    /* Cards Grid View Styles */
    .cards-grid-layout {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        padding: 2rem 2.25rem;
    }

    .personnel-card-item {
        background: #ffffff;
        border-radius: var(--radius-card);
        border: 1px solid var(--border-slate-light);
        padding: 1.25rem;
        position: relative;
        transition: var(--transition-swift);
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.01);
    }

    .personnel-card-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 30px rgba(5, 150, 105, 0.05);
        border-color: rgba(5, 150, 105, 0.18);
    }

    .personnel-card-item.inactive-mode {
        background: #fafafb;
        opacity: 0.85;
    }

    .card-status-indicator {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .card-status-indicator.online {
        background: #10b981;
        box-shadow: 0 0 8px rgba(16, 185, 129, 0.4);
    }

    .card-status-indicator.offline {
        background: #cbd5e1;
    }

    .card-profile-header {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .card-avatar-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        overflow: hidden;
        border: 2.5px solid white;
        box-shadow: 0 3px 10px rgba(0,0,0,0.06);
    }

    .card-avatar {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .card-title-stack {
        display: flex;
        flex-direction: column;
        gap: 1px;
    }

    .card-display-name {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 850;
        color: var(--text-slate-dark);
    }

    .card-display-username {
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--nococ-emerald);
    }

    .card-profile-details {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .detail-row {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-slate-muted);
    }

    .detail-row i {
        width: 14px;
        height: 14px;
        color: #94a3b8;
    }

    .detail-text {
        font-size: 0.78rem;
        font-weight: 700;
    }

    .detail-text.text-mono {
        font-family: monospace;
    }

    .truncate-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 180px;
    }

    .card-actions-wrapper {
        display: flex;
        gap: 8px;
        border-top: 1px dashed var(--border-slate-light);
        padding-top: 1rem;
    }

    .btn-card-action {
        flex: 1;
        padding: 8px 12px;
        border-radius: 10px;
        border: 1px solid var(--border-slate-light);
        background: #ffffff;
        font-size: 0.75rem;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: var(--transition-swift);
    }

    .btn-card-action i {
        width: 14px;
        height: 14px;
    }

    .btn-card-action.view {
        color: var(--nococ-emerald);
        background: var(--nococ-emerald-light);
        border-color: rgba(5, 150, 105, 0.1);
    }
    .btn-card-action.view:hover {
        background: var(--nococ-emerald);
        color: #ffffff;
    }

    .btn-card-action.block {
        color: #f97316;
        background: #fff7ed;
        border-color: rgba(249, 115, 22, 0.1);
    }
    .btn-card-action.block:hover {
        background: #f97316;
        color: #ffffff;
    }

    .btn-card-action.unblock {
        color: var(--nococ-emerald);
        background: var(--nococ-emerald-light);
        border-color: rgba(5, 150, 105, 0.1);
    }
    .btn-card-action.unblock:hover {
        background: var(--nococ-emerald);
        color: #ffffff;
    }

    /* Registry Footer Pagination Styling */
    .registry-pagination-footer {
        padding: 1.5rem 2.25rem;
        background: #fafcff;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .pagination-meta-info {
        font-size: 0.82rem;
        color: var(--text-slate-muted);
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .val-highlight {
        color: var(--text-slate-dark);
        font-weight: 900;
    }

    .per-page-form {
        display: flex;
        align-items: center;
        gap: 8px;
        border-left: 2.5px solid #e2e8f0;
        padding-left: 1.5rem;
    }

    .rows-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.04em;
        color: #94a3b8;
    }

    .select-per-page {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 4px 28px 4px 10px;
        font-size: 0.8rem;
        font-weight: 800;
        color: var(--nococ-emerald);
        outline: none;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.01);
        transition: var(--transition-swift);
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23059669%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
        background-size: 12px;
    }

    .pagination-nav-buttons {
        display: flex;
        gap: 6px;
    }

    .btn-page-nav {
        padding: 8px 16px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        color: var(--nococ-emerald);
        font-weight: 800;
        font-size: 0.78rem;
        text-decoration: none;
        transition: var(--transition-swift);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.01);
    }

    .btn-page-nav i {
        width: 14px;
        height: 14px;
    }

    .btn-page-nav:hover:not(.disabled) {
        background: var(--nococ-emerald);
        color: #ffffff;
        border-color: var(--nococ-emerald);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.15);
    }

    .btn-page-nav.disabled {
        background: #f8fafc;
        color: #94a3b8;
        border-color: #e2e8f0;
        cursor: not-allowed;
        box-shadow: none;
    }

    /* Modal Interior Details Custom Styles (Inject for Swal structure) */
    .user-details-card {
        padding: 0.25rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        font-family: 'Outfit', sans-serif;
    }

    .profile-header-banner {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        padding: 1.25rem;
        background: linear-gradient(135deg, rgba(5,150,105,0.05) 0%, rgba(5,150,105,0.01) 100%);
        border: 1px solid rgba(5, 150, 105, 0.12);
        border-radius: 20px;
        position: relative;
        overflow: hidden;
    }

    .profile-avatar-wrapper {
        position: relative;
        width: 72px;
        height: 72px;
        flex-shrink: 0;
    }

    .profile-avatar {
        width: 100%;
        height: 100%;
        border-radius: 16px;
        object-fit: cover;
        border: 3px solid white;
        box-shadow: 0 8px 20px rgba(5, 150, 105, 0.1);
    }

    .profile-status-ring {
        position: absolute;
        bottom: -3px;
        right: -3px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2.5px solid white;
    }
    .profile-status-ring.online { background: #10b981; }
    .profile-status-ring.offline { background: #ef4444; }

    .profile-title-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        text-align: left;
    }

    .profile-name {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 900;
        color: var(--text-slate-dark);
        letter-spacing: -0.03em;
    }

    .profile-badges {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .profile-username-badge {
        font-size: 0.7rem;
        font-weight: 800;
        color: #047857;
        background: #ecfdf5;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid rgba(4, 120, 87, 0.1);
    }

    .profile-id-badge {
        font-size: 0.7rem;
        font-weight: 800;
        color: var(--text-slate-muted);
        background: #f1f5f9;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }

    .details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .details-card {
        background: white;
        border: 1px solid var(--border-slate-light);
        border-radius: 16px;
        padding: 1rem;
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        transition: var(--transition-swift);
    }

    .details-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.03);
        border-color: rgba(5, 150, 105, 0.2);
    }

    .card-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .card-icon-box i {
        width: 16px;
        height: 16px;
    }

    .email-icon { background: #ecfdf5; color: #059669; }
    .phone-icon { background: #ecfdf5; color: #059669; }
    .dept-icon { background: #ecfdf5; color: #059669; }
    .role-icon { background: #ecfdf5; color: #059669; }

    .card-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 2px;
        text-align: left;
        min-width: 0;
    }

    .card-label {
        font-size: 0.65rem;
        font-weight: 800;
        color: var(--text-slate-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .card-value {
        font-size: 0.85rem;
        font-weight: 800;
        color: var(--text-slate-dark);
        word-break: break-all;
    }

    .card-value.highlighted-text {
        color: var(--nococ-emerald-dark);
    }

    .card-value-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
    }

    .btn-copy-action, .btn-call-action {
        background: #f1f5f9;
        border: none;
        width: 26px;
        height: 26px;
        border-radius: 6px;
        color: var(--text-slate-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: var(--transition-swift);
    }

    .btn-copy-action:hover, .btn-call-action:hover {
        background: var(--nococ-emerald);
        color: white;
    }

    .btn-copy-action i, .btn-call-action i {
        width: 12px;
        height: 12px;
    }

    .role-badge-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.02em;
        width: fit-content;
    }

    .role-badge-pill.admin { background: #fef2f2; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.1); }
    .role-badge-pill.staff { background: #ecfdf5; color: #047857; border: 1px solid rgba(4, 120, 87, 0.1); }

    .session-timeline-card {
        background: #f8fafc;
        border: 1px solid var(--border-slate-light);
        border-radius: 20px;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .timeline-title {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 900;
        color: var(--text-slate-muted);
        display: flex;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-align: left;
    }

    .timeline-title i {
        width: 14px;
        height: 14px;
        color: var(--nococ-emerald);
    }

    .timeline-flow {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        gap: 1rem;
    }

    .timeline-node {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        text-align: left;
    }

    .node-icon-box {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: white;
        border: 1.5px solid var(--border-slate-light);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-slate-muted);
    }

    .node-icon-box i { width: 14px; height: 14px; }
    .login-node .node-icon-box { color: var(--nococ-emerald); border-color: rgba(5, 150, 105, 0.15); }

    .node-details {
        display: flex;
        flex-direction: column;
        gap: 1px;
    }

    .node-label {
        font-size: 0.6rem;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .node-time {
        font-size: 0.8rem;
        font-weight: 850;
        color: var(--text-slate-dark);
        font-family: monospace;
    }

    .timeline-connector {
        flex: 1;
        height: 2px;
        background: var(--border-slate-light);
        max-width: 60px;
    }

    .status-summary-bar {
        display: flex;
        align-items: center;
        gap: 6px;
        padding-top: 10px;
        border-top: 1px solid var(--border-slate-light);
        font-size: 0.75rem;
        color: var(--text-slate-muted);
        text-align: left;
    }

    .status-indicator-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .status-indicator-dot.active { background: #10b981; box-shadow: 0 0 8px rgba(16, 185, 129, 0.4); }
    .status-indicator-dot.inactive { background: #ef4444; box-shadow: 0 0 8px rgba(239, 68, 68, 0.4); }

    /* SweetAlert Glass Redesign Overrides */
    .glass-monolith-popup {
        border-radius: var(--radius-luxe) !important;
        border: 1px solid var(--border-slate-light) !important;
        box-shadow: 0 25px 60px -12px rgba(0,0,0,0.15) !important;
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Hotkey trigger: Ctrl/Cmd + K to focus search
        $(document).keydown(function(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                $('#registrySearch').focus();
            }
        });

        // Load visual layout selection state
        const layoutPreference = localStorage.getItem('registry-layout-pref') || 'table';
        toggleLayout(layoutPreference);
    });

    // Layout toggling mechanism
    function toggleLayout(type) {
        const tableBtn = document.getElementById('btnViewTable');
        const gridBtn = document.getElementById('btnViewGrid');
        const tableView = document.getElementById('tableLayoutView');
        const gridView = document.getElementById('gridLayoutView');

        if (type === 'grid') {
            tableBtn.classList.remove('active');
            gridBtn.classList.add('active');
            tableView.style.display = 'none';
            gridView.style.display = 'block';
            localStorage.setItem('registry-layout-pref', 'grid');
        } else {
            gridBtn.classList.remove('active');
            tableBtn.classList.add('active');
            gridView.style.display = 'none';
            tableView.style.display = 'block';
            localStorage.setItem('registry-layout-pref', 'table');
        }
    }
</script>

@push('modals')
<!-- Legacy Admin Audit Modal -->
<div class="modal-overlay" id="legacyAuditModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15,23,42,0.6); backdrop-filter: blur(8px); z-index: 99999; justify-content: center; align-items: center;">
    <div class="modal-container" style="background: white; border-radius: 24px; width: 95%; max-width: 950px; padding: 2.5rem; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 1.5rem; max-height: 85vh;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; gap: 1rem; align-items: center;">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: #ecfdf5; color: #047857; display: flex; align-items: center; justify-content: center; box-shadow: inset 0 0 12px rgba(5,150,105,0.05);">
                    <i data-lucide="history"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.25rem; font-weight: 900; color: #0f172a;">Legacy Head Activity</h3>
                    <p style="margin: 4px 0 0; font-size: 0.85rem; color: #64748b; font-weight: 600;">Comprehensive audit trail of deactivated Head accounts.</p>
                </div>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; justify-content: flex-end;">
                <div style="position: relative; min-width: 180px;">
                    <i data-lucide="search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #94a3b8;"></i>
                    <input type="text" id="legacySearchFilter" onkeyup="filterLegacyLogs()" placeholder="Search logs..." style="padding: 8px 12px 8px 30px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 0.85rem; font-weight: 600; color: #0f172a; outline: none; width: 100%; background: white;">
                </div>
                <select id="legacyTypeFilter" onchange="filterLegacyLogs()" style="padding: 8px 12px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 0.85rem; font-weight: 600; color: #475569; outline: none; cursor: pointer; background: white;">
                    <option value="all">All Event Types</option>
                    <option value="AUTHORIZATION">Approvals & Authorizations</option>
                    <option value="INVENTORY">Inventory Management</option>
                    <option value="SECURITY">Security / Users</option>
                    <option value="AUTH">Logins & Sessions</option>
                </select>
                @if(isset($legacyAdmins) && $legacyAdmins->count() > 0)
                <select id="legacyAdminFilter" onchange="filterLegacyLogs()" style="padding: 8px 12px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 0.85rem; font-weight: 600; color: #475569; outline: none; cursor: pointer; background: white;">
                    <option value="all">All Previous Heads</option>
                    @foreach($legacyAdmins as $admin)
                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                    @endforeach
                </select>
                @endif
                <button onclick="closeLegacyAuditModal()" style="background: #f1f5f9; border: none; width: 36px; height: 36px; border-radius: 10px; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="x" style="width: 18px;"></i>
                </button>
            </div>
        </div>

        <div style="overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 16px; flex: 1;">
            <table style="width: 100%; border-collapse: collapse; min-width: 700px;">
                <thead style="background: #f8fafc; position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th style="padding: 14px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem; font-weight: 900; letter-spacing: 0.05em; text-transform: uppercase;">TIMESTAMP</th>
                        <th style="padding: 14px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem; font-weight: 900; letter-spacing: 0.05em; text-transform: uppercase;">ADMINISTRATOR</th>
                        <th style="padding: 14px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem; font-weight: 900; letter-spacing: 0.05em; text-transform: uppercase;">ACTION</th>
                        <th style="padding: 14px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem; font-weight: 900; letter-spacing: 0.05em; text-transform: uppercase;">DETAILS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($legacyAdminLogs ?? [] as $log)
                    <tr class="legacy-log-row" data-admin-id="{{ $log->user_id }}" data-action="{{ $log->action }}" data-event="{{ $log->event_type }}" style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                        <td style="padding: 14px 16px; font-size: 0.8rem; font-weight: 700; color: #64748b; font-family: monospace;">
                            {{ $log->created_at->format('d/m/y H:i:s') }}
                        </td>
                        <td style="padding: 14px 16px; font-size: 0.85rem; font-weight: 800; color: #0f172a;">
                            {{ $log->user->name ?? 'System' }}
                        </td>
                        <td style="padding: 14px 16px; font-size: 0.75rem; font-weight: 800; color: #059669;">
                            {{ $log->action }}
                        </td>
                        <td style="padding: 14px 16px; font-size: 0.85rem; color: #475569; line-height: 1.5; font-weight: 600;">
                            {{ $log->friendly_description }}
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
@endpush

<script>
    function openLegacyAuditModal() {
        document.getElementById('legacyAuditModal').style.display = 'flex';
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
    function closeLegacyAuditModal() {
        document.getElementById('legacyAuditModal').style.display = 'none';
    }

    function viewUserDetails(user) {
        const defaultAvatar = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2364748b'><circle cx='12' cy='8' r='4'/><path d='M12 14c-4.42 0-8 3.58-8 8h16c0-4.42-3.58-8-8-8z'/></svg>";
        const avatarUrl = user.avatar ? user.avatar : defaultAvatar;
        Swal.fire({
            html: `
                <div class="user-details-card">
                    <!-- Header Banner -->
                    <div class="profile-header-banner">
                        <div class="profile-avatar-wrapper">
                            <img src="${avatarUrl}" class="profile-avatar" alt="${user.name}">
                            <span class="profile-status-ring ${user.status === 'ACTIVE' ? 'online' : 'offline'}"></span>
                        </div>
                        <div class="profile-title-group">
                            <h2 class="profile-name">${user.name}</h2>
                            <div class="profile-badges">
                                <span class="profile-username-badge">@${user.username}</span>
                                <span class="profile-id-badge">ID: ${(user.id || '').toString().padStart(5, '0')}</span>
                                ${user.rank ? `<span class="profile-id-badge" style="background: #ecfdf5; color: #047857; border-color: #a7f3d0;">Rank: ${user.rank}</span>` : ''}
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="details-grid">
                        <!-- Email Card -->
                        <div class="details-card email-card">
                            <div class="card-icon-box email-icon">
                                <i data-lucide="mail"></i>
                            </div>
                            <div class="card-content">
                                <span class="card-label">Email Address</span>
                                <div class="card-value-wrap">
                                    <span class="card-value text-break">${user.email}</span>
                                    ${user.email !== 'Not Provided' ? `
                                    <button class="btn-copy-action" onclick="copyValue('${user.email}', this)" title="Copy Email">
                                        <i data-lucide="copy" class="copy-icon"></i>
                                    </button>` : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Phone Card -->
                        <div class="details-card phone-card">
                            <div class="card-icon-box phone-icon">
                                <i data-lucide="phone"></i>
                            </div>
                            <div class="card-content">
                                <span class="card-label">Comms Line</span>
                                <div class="card-value-wrap">
                                    <span class="card-value">${user.phone}</span>
                                    ${user.phone !== 'Not Provided' ? `
                                    <a href="tel:${user.phone}" class="btn-call-action" title="Call User">
                                        <i data-lucide="phone-call"></i>
                                    </a>` : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Department Card -->
                        <div class="details-card dept-card">
                            <div class="card-icon-box dept-icon">
                                <i data-lucide="building"></i>
                            </div>
                            <div class="card-content">
                                <span class="card-label">Sector Unit</span>
                                <span class="card-value highlighted-text">${user.department}</span>
                            </div>
                        </div>

                        <!-- Role Card -->
                        <div class="details-card role-card">
                            <div class="card-icon-box role-icon">
                                <i data-lucide="shield-check"></i>
                            </div>
                            <div class="card-content">
                                <span class="card-label">Access Level</span>
                                <span class="role-badge-pill ${user.role.toLowerCase() === 'admin' ? 'admin' : 'staff'}">
                                    ${user.role === 'Main Admin' ? 'HEAD OF ADMIN(AUTHORIZER)' : ((user.role === 'Department Head' && user.department === 'Human Resource Management Department') ? 'DEPT HEAD HR' : ((user.role === 'Department Head' && user.department === 'Welfare Department') ? 'HEAD OF WELFARE' : user.role.toUpperCase()))}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline/Session Section -->
                    <div class="session-timeline-card">
                        <h4 class="timeline-title">
                            <i data-lucide="activity"></i> Session Synchronizations
                        </h4>
                        <div class="timeline-flow">
                            <div class="timeline-node login-node">
                                <div class="node-icon-box">
                                    <i data-lucide="log-in"></i>
                                </div>
                                <div class="node-details">
                                    <span class="node-label">Last Login</span>
                                    <span class="node-time">${user.last_login}</span>
                                </div>
                            </div>
                            <div class="timeline-connector"></div>
                            <div class="timeline-node logout-node">
                                <div class="node-icon-box">
                                    <i data-lucide="log-out"></i>
                                </div>
                                <div class="node-details">
                                    <span class="node-label">Last Logout</span>
                                    <span class="node-time">${user.last_logout}</span>
                                </div>
                            </div>
                        </div>
                        <div class="status-summary-bar">
                            <span class="status-indicator-dot ${user.status === 'ACTIVE' ? 'active' : 'inactive'}"></span>
                            <span class="status-text">Account is currently <strong>${user.status}</strong></span>
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
                popup: 'glass-monolith-popup',
            }
        });
    }

    function copyValue(text, element) {
        navigator.clipboard.writeText(text).then(() => {
            const icon = element.querySelector('i');
            const originalLucide = icon.getAttribute('data-lucide');
            icon.setAttribute('data-lucide', 'check');
            element.style.background = '#059669';
            element.style.color = 'white';
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            setTimeout(() => {
                icon.setAttribute('data-lucide', originalLucide);
                element.style.background = '';
                element.style.color = '';
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }, 1500);
        }).catch(err => {
            console.error('Could not copy text: ', err);
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
