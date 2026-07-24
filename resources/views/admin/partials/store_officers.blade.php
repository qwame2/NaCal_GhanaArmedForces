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
                <span class="badge-role" style="font-size: 0.65rem; background: #eef2ff; color: #065f46; padding: 2px 8px; border-radius: 6px; font-weight: 800; font-family: sans-serif; text-transform: uppercase; border: 1px solid rgba(67, 56, 202, 0.1);">
                    @if($user->role === 'Main Admin')
                        Head of Admin(Authorizer)
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
                <span class="badge-dept" style="font-size: 0.65rem; background: #f0fdf4; color: #065f46; padding: 2px 8px; border-radius: 6px; font-weight: 800; font-family: sans-serif; text-transform: uppercase; border: 1px solid rgba(76, 5, 25, 0.1); max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $user->department }}">
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

    <div class="col-ctrl">
        <div class="toggle-group-wrap">
            <label class="normal-toggle" title="Toggle Stock Verification">
                <input type="checkbox" onchange="toggleMatrixPermission(this, 'can_verify_stock')" {{ $user->can_verify_stock ? 'checked' : '' }}>
                <div class="toggle-slider"></div>
            </label>
            <div class="toggle-text">
                <span class="t-main">Stock Checks</span>
                <span class="t-sub"></span>
            </div>
        </div>
    </div>

    <div class="col-ctrl">
        <div class="toggle-group-wrap">
            <label class="normal-toggle" title="Allow this store officer to place requisition requests">
                <input type="checkbox" onchange="toggleMatrixPermission(this, 'can_make_requisition')" {{ ($user->can_make_requisition ?? false) ? 'checked' : '' }}>
                <div class="toggle-slider"></div>
            </label>
            <div class="toggle-text">
                <span class="t-main">{{ ($user->can_make_requisition ?? false) ? 'Enabled' : 'Disabled' }}</span>
                <span class="t-sub">Place requests</span>
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
<div class="m-empty-state" style="padding: 3rem; text-align: center; color: #94a3b8; font-weight: 600; background: white;">
    No store officers registered.
</div>
@endforelse
