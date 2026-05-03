@extends('layouts.admin')

@section('title', 'Access Control Matrix')

@section('content')
<div class="view-header" style="margin-bottom: 3rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 2rem; width: 100%;">
        <div style="flex: 1; min-width: 300px;">
            <div class="title-group">
                <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin-top: 0.5rem; max-width: 600px;">
                    Configure granular operational permissions and security clearances for system personnel.
                </p>
            </div>
        </div>
        
        <div style="flex: 0 1 450px;">
            <div class="search-vault">
                <i data-lucide="search"></i>
                <input type="text" id="personnelSearch" placeholder="Filter personnel by name or identity..." oninput="filterPersonnel()">
                <div class="search-kicker">⌘ K</div>
            </div>
        </div>
    </div>
</div>

<div class="permissions-container">
    <div class="perm-grid">
        @foreach($users as $user)
        <div class="perm-card" data-user-id="{{ $user->id }}">
            <div class="card-header">
                <div class="user-profile">
                    <div class="avatar-frame">
                        <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random' }}" alt="{{ $user->name }}">
                        <div class="status-indicator {{ $user->is_active ? 'online' : 'offline' }}"></div>
                    </div>
                    <div class="user-info">
                        <h3>{{ $user->name }}</h3>
                        <span class="user-handle">@ {{ $user->username }}</span>
                    </div>
                </div>
                <div class="badge {{ $user->is_active ? 'badge-active' : 'badge-suspended' }}">
                    {{ $user->is_active ? 'ACTIVE' : 'SUSPENDED' }}
                </div>
            </div>

            <div class="permissions-list">
                <!-- Permission: Inventory Item Entry -->
                <div class="perm-row" data-perm="can_add_inventory">
                    <div class="perm-icon">
                        <i data-lucide="package-plus"></i>
                    </div>
                    <div class="perm-content">
                        <div class="perm-label">
                            <span class="perm-title">Inventory Entry</span>
                            <label class="premium-switch">
                                <input type="checkbox" onchange="togglePermission(this, 'can_add_inventory')" {{ $user->can_add_inventory ? 'checked' : '' }}>
                                <span class="premium-slider"></span>
                            </label>
                        </div>
                        <p class="perm-desc">Authorize entry of new inventory into the core registry.</p>
                    </div>
                </div>

                <!-- Permission: Logistics Operations -->
                <div class="perm-row" data-perm="can_operate_logistics">
                    <div class="perm-icon">
                        <i data-lucide="truck"></i>
                    </div>
                    <div class="perm-content">
                        <div class="perm-label">
                            <span class="perm-title">Logistics Ops</span>
                            <label class="premium-switch">
                                <input type="checkbox" onchange="togglePermission(this, 'can_operate_logistics')" {{ $user->can_operate_logistics ? 'checked' : '' }}>
                                <span class="premium-slider"></span>
                            </label>
                        </div>
                        <p class="perm-desc">Enable item issuance and return processing protocols.</p>
                    </div>
                </div>

                <!-- Permission: Report Generation -->
                <div class="perm-row" data-perm="can_generate_reports">
                    <div class="perm-icon">
                        <i data-lucide="bar-chart-3"></i>
                    </div>
                    <div class="perm-content">
                        <div class="perm-label">
                            <span class="perm-title">Analytics Access</span>
                            <label class="premium-switch">
                                <input type="checkbox" onchange="togglePermission(this, 'can_generate_reports')" {{ $user->can_generate_reports ? 'checked' : '' }}>
                                <span class="premium-slider"></span>
                            </label>
                        </div>
                        <p class="perm-desc">Grant access to strategic data exports and analytics.</p>
                    </div>
                </div>
            </div>
            
            <div class="card-footer">
                <span class="last-audit">Last modified: {{ $user->updated_at->diffForHumans() }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
    :root {
        --card-bg: #ffffff;
        --card-shadow: 0 20px 50px rgba(0, 0, 0, 0.03);
        --card-hover-shadow: 0 30px 70px rgba(79, 70, 229, 0.08);
        --row-bg: #f8fafc;
        --row-hover: #f1f5f9;
        --accent: #4f46e5;
    }

    .permissions-container {
        padding-bottom: 5rem;
    }

    .perm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
        gap: 2.5rem;
    }

    /* Search Vault Styling */
    .search-vault {
        position: relative;
        display: flex;
        align-items: center;
        background: white;
        border: 2px solid #f1f5f9;
        border-radius: 20px;
        padding: 0.5rem 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
    }

    .search-vault:focus-within {
        border-color: var(--accent);
        box-shadow: 0 10px 30px rgba(79, 70, 229, 0.1);
        transform: translateY(-2px);
    }

    .search-vault i {
        color: var(--accent);
        opacity: 0.6;
        margin-right: 1rem;
        width: 20px;
    }

    .search-vault input {
        border: none;
        outline: none;
        padding: 0.75rem 0;
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-heading);
        width: 100%;
        background: transparent;
    }

    .search-kicker {
        font-size: 0.7rem;
        font-weight: 800;
        color: var(--text-muted);
        background: #f1f5f9;
        padding: 4px 8px;
        border-radius: 8px;
        white-space: nowrap;
    }

    /* Permission Card Styling */
    .perm-card {
        background: var(--card-bg);
        border-radius: 35px;
        padding: 2.25rem;
        border: 1px solid rgba(0, 0, 0, 0.03);
        box-shadow: var(--card-shadow);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        gap: 2rem;
        position: relative;
        overflow: hidden;
    }

    .perm-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--card-hover-shadow);
        border-color: rgba(79, 70, 229, 0.1);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .avatar-frame {
        position: relative;
        width: 64px;
        height: 64px;
    }

    .avatar-frame img {
        width: 100%;
        height: 100%;
        border-radius: 20px;
        object-fit: cover;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        border: 3px solid white;
    }

    .status-indicator {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 3px solid white;
    }

    .status-indicator.online { background: #10b981; }
    .status-indicator.offline { background: #cbd5e1; }

    .user-info h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 900;
        color: var(--text-heading);
        letter-spacing: -0.02em;
    }

    .user-handle {
        font-size: 0.85rem;
        color: var(--accent);
        font-weight: 800;
        font-family: 'JetBrains Mono', monospace;
    }

    .badge {
        padding: 6px 14px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 900;
        letter-spacing: 0.05em;
    }

    .badge-active { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
    .badge-suspended { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }

    .permissions-list {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .perm-row {
        display: flex;
        gap: 1.25rem;
        background: var(--row-bg);
        padding: 1.25rem;
        border-radius: 24px;
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }

    .perm-row:hover {
        background: var(--row-hover);
        border-color: #e2e8f0;
    }

    .perm-icon {
        width: 48px;
        height: 48px;
        background: white;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        flex-shrink: 0;
    }

    .perm-icon i { width: 22px; height: 22px; }

    .perm-content {
        flex: 1;
    }

    .perm-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .perm-title {
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--text-heading);
    }

    .perm-desc {
        font-size: 0.85rem;
        color: var(--text-muted);
        line-height: 1.5;
        margin: 0;
        font-weight: 500;
    }

    /* Premium Toggle Switch */
    .premium-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }

    .premium-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .premium-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #e2e8f0;
        transition: .4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 24px;
    }

    .premium-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    input:checked + .premium-slider {
        background-color: var(--accent);
    }

    input:checked + .premium-slider:before {
        transform: translateX(20px);
    }

    input:focus + .premium-slider {
        box-shadow: 0 0 1px var(--accent);
    }

    .card-footer {
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: center;
    }

    .last-audit {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
    }

    /* Animations */
    @keyframes row-pulse {
        0% { transform: scale(1); }
        50% { transform: scale(0.98); opacity: 0.7; }
        100% { transform: scale(1); }
    }

    .saving-row {
        animation: row-pulse 1s infinite ease-in-out;
        pointer-events: none;
    }

    @media (max-width: 640px) {
        .perm-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    function filterPersonnel() {
        const term = document.getElementById('personnelSearch').value.toLowerCase();
        const cards = document.querySelectorAll('.perm-card');
        
        cards.forEach(card => {
            const name = card.querySelector('h3').textContent.toLowerCase();
            const username = card.querySelector('.user-handle').textContent.toLowerCase();
            
            if (name.includes(term) || username.includes(term)) {
                card.style.display = 'flex';
                card.style.animation = 'fadeIn 0.4s ease';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Keyboard shortcut for search
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('personnelSearch').focus();
        }
    });

    function togglePermission(checkbox, permission) {
        const card = checkbox.closest('.perm-card');
        const userId = card.getAttribute('data-user-id');
        const value = checkbox.checked ? 1 : 0;
        const row = checkbox.closest('.perm-row');

        // Premium visual feedback
        row.classList.add('saving-row');
        
        fetch('{{ route("admin.permissions.update") }}', {
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
            row.classList.remove('saving-row');
            if (data.success) {
                // Success animation on the icon
                const icon = row.querySelector('.perm-icon');
                icon.style.background = '#f0fdf4';
                icon.style.color = '#16a34a';
                setTimeout(() => {
                    icon.style.background = 'white';
                    icon.style.color = 'var(--accent)';
                }, 1000);
            } else {
                checkbox.checked = !checkbox.checked; // Revert
                alert('Failed to update permission: ' + data.message);
            }
        })
        .catch(error => {
            row.classList.remove('saving-row');
            checkbox.checked = !checkbox.checked; // Revert
            console.error('Error:', error);
            alert('A system error occurred.');
        });
    }

    // Initialize Lucide icons for dynamic content if needed
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) lucide.createIcons();
    });
</script>
@endsection
