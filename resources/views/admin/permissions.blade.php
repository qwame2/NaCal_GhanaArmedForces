
@extends('layouts.admin')

@section('title', 'Premission')

@section('content')
<div class="view-header" style="margin-bottom: 3rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 2rem; width: 100%;">
        <div style="flex: 1; min-width: 300px;">
            <div class="title-group">
                <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin-top: 0.5rem; max-width: 600px;">
                    Configure granular operational permissions and security clearances for system users.
                </p>
            </div>
        </div>

        <div style="flex: 0 1 450px;">
            <div class="search-vault">
                <i data-lucide="search"></i>
                <input type="text" id="personnelSearch" placeholder="Filter users by name or identity..." oninput="filterPersonnel()">
                <div class="search-kicker">⌘ K</div>
            </div>
        </div>
    </div>
</div>

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

<style>
    /* Search Vault Styling */
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

    .search-vault i {
        color: #4f46e5;
        opacity: 0.6;
        margin-right: 1rem;
        width: 20px;
    }

    .search-vault input {
        border: none;
        outline: none;
        padding: 0.75rem 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: #0f172a;
        width: 100%;
        background: transparent;
    }

    .search-vault input::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    .search-kicker {
        font-size: 0.7rem;
        font-weight: 800;
        color: #64748b;
        background: #f1f5f9;
        padding: 4px 8px;
        border-radius: 8px;
        white-space: nowrap;
        border: 1px solid #e2e8f0;
    }

    /* Matrix Table Layout */
    .permissions-matrix-wrapper {
        background: white;
        border-radius: 32px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.03);
        overflow: hidden;
        margin-bottom: 4rem;
        animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .matrix-table {
        display: flex;
        flex-direction: column;
        width: 100%;
        min-width: 900px;
    }

    .m-header {
        display: flex;
        align-items: center;
        background: #f8fafc;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .m-header > div {
        font-size: 0.75rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }

    .m-row {
        display: flex;
        align-items: center;
        padding: 1.25rem 2rem;
        border-bottom: 1px solid #f8fafc;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
    }

    .m-row:hover {
        background: #fdfeff;
        box-shadow: inset 4px 0 0 var(--primary);
    }

    .m-row:last-child {
        border-bottom: none;
    }

    /* Grid Columns */
    .col-id { flex: 0 0 350px; display: flex; align-items: center; gap: 1.25rem; }
    .col-ctrl { flex: 1; display: flex; justify-content: flex-start; align-items: center; }
    .col-stat { flex: 0 0 160px; display: flex; justify-content: flex-end; }

    /* Identity Styling */
    .m-avatar {
        position: relative;
        width: 48px;
        height: 48px;
        border-radius: 16px;
        padding: 3px;
        background: linear-gradient(135deg, #e2e8f0, #f8fafc);
        flex-shrink: 0;
    }

    .m-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 12px;
        object-fit: cover;
    }

    .m-pulse {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 3px solid white;
    }

    .m-pulse.online { background: #10b981; animation: soft-pulse 2s infinite; }
    .m-pulse.offline { background: #cbd5e1; }

    @keyframes soft-pulse {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .m-identity { display: flex; flex-direction: column; gap: 2px; }

    .m-name {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 850;
        color: var(--text-heading);
        letter-spacing: -0.01em;
    }

    .m-handle {
        font-size: 0.75rem;
        color: var(--accent);
        font-weight: 700;
        font-family: 'JetBrains Mono', monospace;
    }

    /* Toggle Group and Text */
    .toggle-group-wrap {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .toggle-text {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .t-main {
        font-size: 0.75rem;
        font-weight: 800;
        color: var(--text-heading);
        line-height: 1.1;
    }

    .t-sub {
        font-size: 0.65rem;
        color: var(--text-muted);
        font-weight: 600;
    }

    /* Normal Toggle Switch */
    .normal-toggle {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        cursor: pointer;
    }

    .normal-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: .25s ease;
        border-radius: 24px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .25s ease;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }

    .normal-toggle input:checked + .toggle-slider {
        background-color: var(--primary);
    }

    .normal-toggle input:checked + .toggle-slider:before {
        transform: translateX(20px);
    }

    /* Badges */
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.05em;
    }

    .badge-status.authorized { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
    .badge-status.revoked { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
    .badge-status i { width: 14px; height: 14px; }

    .syncing-row {
        opacity: 0.5;
        pointer-events: none;
        background: #f8fafc !important;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Horizontal scroll for smaller screens */
    @media (max-width: 1024px) {
        .permissions-matrix-wrapper {
            overflow-x: auto;
        }
    }
</style>

<script>
    function filterPersonnel() {
        const term = document.getElementById('personnelSearch').value.toLowerCase();
        const rows = document.querySelectorAll('.m-row');

        rows.forEach(row => {
            const name = row.querySelector('.m-name').textContent.toLowerCase();
            const username = row.querySelector('.m-handle').textContent.toLowerCase();

            if (name.includes(term) || username.includes(term)) {
                row.style.display = 'flex';
                row.style.animation = 'fadeUp 0.3s ease';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Keyboard shortcut for search
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            if (document.activeElement !== document.getElementById('personnelSearch')) {
                e.preventDefault();
                document.getElementById('personnelSearch').focus();
            }
        }
    });

    function toggleMatrixPermission(checkbox, permission) {
        const row = checkbox.closest('.m-row');
        const userId = row.getAttribute('data-user-id');
        const value = checkbox.checked ? 1 : 0;

        row.classList.add('syncing-row');

        fetch('{{ route("admin.permissions.update", [], false) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                user_id: userId,
                permission: permission,
                value: value
            })
        })
        .then(response => response.json())
        .then(data => {
            row.classList.remove('syncing-row');
            if (!data.success) {
                checkbox.checked = !checkbox.checked; // Revert
                alert('Failed to update permission: ' + data.message);
            }
        })
        .catch(error => {
            row.classList.remove('syncing-row');
            checkbox.checked = !checkbox.checked; // Revert
            alert('A system error occurred.');
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) lucide.createIcons();
    });
</script>
@endsection
