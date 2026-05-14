@extends('layouts.admin')

@section('title', 'Global Settings')

@section('content')
<style>
    .settings-wrapper {
        display: flex;
        gap: 2.5rem;
        margin-top: 1rem;
        animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Sidebar Navigation */
    .settings-nav {
        width: 280px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 1.25rem 1.5rem;
        background: white;
        border-radius: 20px;
        border: 1px solid #edf2f7;
        color: #64748b;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .nav-item i { width: 20px; height: 20px; }
    
    .nav-item:hover {
        background: #f8fafc;
        color: var(--primary);
        transform: translateX(5px);
        border-color: var(--primary);
    }

    .nav-item.active {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: white;
        border-color: transparent;
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.25);
    }

    /* Search Bar Styling */
    .search-parameter-vault {
        position: relative;
        margin-bottom: 2rem;
        width: 100%;
        max-width: 450px;
    }

    .search-parameter-vault input {
        width: 100%;
        padding: 1.1rem 1.5rem 1.1rem 3.5rem;
        background: white;
        border: 2px solid #edf2f7;
        border-radius: 20px;
        font-weight: 700;
        color: #0f172a;
        transition: 0.3s;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .search-parameter-vault input:focus {
        border-color: var(--primary);
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.1);
        outline: none;
    }

    .search-parameter-vault i {
        position: absolute;
        left: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        width: 20px;
    }

    /* Content Area */
    .settings-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 2.5rem;
    }

    .btn-save:active {
        transform: scale(0.95);
    }

    .glass-card-premium {
        background: white;
        border-radius: 32px;
        border: 1px solid rgba(0,0,0,0.03);
        box-shadow: 0 30px 60px -12px rgba(0,0,0,0.05);
        overflow: hidden;
        animation: slideIn 0.5s ease-out forwards;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(10px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .config-header {
        padding: 2.5rem;
        background: linear-gradient(to right, #f8fafc, white);
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .icon-box-gradient {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1);
    }

    .config-body {
        padding: 2.5rem;
    }

    .settings-grid-premium {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
    }

    .setting-item-premium {
        background: #fcfdfe;
        border: 1px solid #f1f5f9;
        border-radius: 24px;
        padding: 1.75rem;
        transition: all 0.4s ease;
    }

    .setting-item-premium:hover {
        background: white;
        border-color: var(--primary);
        box-shadow: 0 12px 30px rgba(79, 70, 229, 0.08);
        transform: translateY(-5px);
    }

    .label-premium {
        display: block;
        font-size: 0.95rem;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 1.25rem;
        letter-spacing: -0.01em;
    }

    .desc-premium {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 1.25rem;
        line-height: 1.6;
        font-weight: 600;
    }

    /* Category Management Improvements */
    .ledger-badge {
        background: white;
        border: 1.5px solid #edf2f7;
        border-radius: 16px;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: 0.3s;
    }

    .ledger-badge:hover {
        border-color: #10b981;
        background: #f0fdf4;
    }

    .ledger-code {
        background: var(--primary);
        color: white;
        font-weight: 950;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    /* Form Elements */
    .input-premium {
        width: 100%;
        padding: 1rem 1.25rem;
        border: 2px solid #f1f5f9;
        border-radius: 16px;
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        transition: 0.3s;
    }

    .input-premium:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 5px rgba(79, 70, 229, 0.1);
        background: white;
    }

    .save-action-bar {
        position: sticky;
        bottom: 2rem;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(15px);
        padding: 1.25rem 2rem;
        border-radius: 24px;
        border: 1px solid rgba(79, 70, 229, 0.1);
        display: flex;
        justify-content: flex-end;
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        z-index: 100;
        margin-top: 2rem;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="view-header" style="margin-bottom: 2.5rem;">
    <h2 style="font-weight: 950; color: #0f172a; font-size: 2rem; letter-spacing: -0.04em; margin: 0;">System Settings</h2>
    <p style="color: #64748b; font-weight: 500; font-size: 1.1rem; margin-top: 0.25rem;">Adjust core system mechanics and security thresholds.</p>
</div>

<div class="settings-wrapper">
    <!-- Quick Nav Sidebar -->
    <div class="settings-nav">
        <a href="#core-configs" class="nav-item active">
            <i data-lucide="shield-check"></i> System Core
        </a>
        <a href="#category-configs" class="nav-item">
            <i data-lucide="layers"></i> Item Categories
        </a>
        <div style="margin-top: auto; padding: 2rem 1.5rem; background: #f8fafc; border-radius: 24px; border: 1px solid #edf2f7; text-align: center;">
            <i data-lucide="info" style="color: var(--primary); margin-bottom: 10px;"></i>
            <p style="font-size: 0.7rem; font-weight: 800; color: #64748b; line-height: 1.5; margin: 0;">Changes made here affect all users globally. Proceed with caution.</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="settings-content">
        <div class="search-parameter-vault">
            <i data-lucide="search"></i>
            <input type="text" id="parameterSearch" placeholder="Search parameters (e.g. timeout, threshold)..." oninput="filterParameters()">
        </div>

        @if($settings->isEmpty())
            <div class="glass-card-premium" style="padding: 6rem 2rem; text-align: center;">
                <div style="width: 120px; height: 120px; background: #f1f5f9; border-radius: 40px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2.5rem;">
                    <i data-lucide="database" style="width: 50px; height: 50px; color: #94a3b8;"></i>
                </div>
                <h3 style="font-weight: 950; font-size: 1.75rem; color: #0f172a; letter-spacing: -0.03em;">Registry Initialization Required</h3>
                <p style="color: #64748b; font-weight: 600; max-width: 400px; margin: 1rem auto 2.5rem; line-height: 1.6;">The global configurations table is currently unpopulated. Please sync your migrations to continue.</p>
                <code>php artisan migrate --seed</code>
            </div>
        @else
            <form action="{{ route('admin.settings.update') }}" method="POST" id="core-configs">
                @csrf
                
                @foreach($settings as $group => $groupSettings)
                    @php
                        $color = '#4f46e5';
                        $icon = 'settings';
                        if ($group === 'security') { $color = '#ef4444'; $icon = 'shield-alert'; }
                        elseif ($group === 'inventory') { $color = '#10b981'; $icon = 'package'; }
                        elseif ($group === 'system') { $color = '#f59e0b'; $icon = 'server'; }
                    @endphp
                    <div class="glass-card-premium" style="margin-bottom: 3rem;">
                        <div class="config-header">
                            <div class="icon-box-gradient" style="background: linear-gradient(135deg, {{ $color }} 0%, {{ $color }}dd 100%);">
                                <i data-lucide="{{ $icon }}"></i>
                            </div>
                            <div>
                                <h3 style="font-weight: 950; font-size: 1.4rem; color: #0f172a; letter-spacing: -0.02em; margin: 0; text-transform: capitalize;">{{ $group }} Protocols</h3>
                                <p style="font-size: 0.85rem; color: #64748b; font-weight: 700; margin-top: 4px;">Strategic parameters for {{ $group }} management.</p>
                            </div>
                        </div>
                        
                        <div class="config-body">
                            <div class="settings-grid-premium">
                                @foreach($groupSettings as $setting)
                                    @php if(in_array($setting->key, ['strict_audit_logging', 'enable_strict_audit_logging', 'approval_timeout_minutes'])) continue; @endphp
                                    <div class="setting-item-premium">
                                        <label class="label-premium" for="setting_{{ $setting->key }}">
                                            {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        </label>
                                        
                                        @if($setting->type === 'boolean')
                                            <div class="toggle-wrapper" style="width: 100%; justify-content: space-between; padding: 12px 18px; background: white; border: 2px solid #f1f5f9; border-radius: 18px;">
                                                <span class="toggle-status-text" style="color: {{ $setting->value === 'true' ? $color : '#94a3b8' }}; font-size: 0.75rem;">
                                                    {{ $setting->value === 'true' ? 'STATUS: ACTIVE' : 'STATUS: INACTIVE' }}
                                                </span>
                                                <label class="premium-toggle">
                                                    <input type="checkbox" name="{{ $setting->key }}" value="true" {{ $setting->value === 'true' ? 'checked' : '' }} onchange="this.closest('.toggle-wrapper').querySelector('.toggle-status-text').textContent = this.checked ? 'STATUS: ACTIVE' : 'STATUS: INACTIVE'; this.closest('.toggle-wrapper').querySelector('.toggle-status-text').style.color = this.checked ? '{{ $color }}' : '#94a3b8';">
                                                    <span class="toggle-track" style="{{ $setting->value === 'true' ? 'background-color: ' . $color : '' }}">
                                                        <span class="toggle-thumb"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        @elseif($setting->type === 'integer')
                                            <input type="number" id="setting_{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}" class="input-premium">
                                        @else
                                            <input type="text" id="setting_{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}" class="input-premium">
                                        @endif
                                        
                                        @if($setting->description)
                                            <p class="desc-premium">{{ $setting->description }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="save-action-bar">
                    <button type="submit" class="btn-save" style="padding: 1rem 2.5rem; font-size: 1rem; border-radius: 18px;">
                        <i data-lucide="refresh-cw" style="width: 20px;"></i> Apply Configurations
                    </button>
                </div>
            </form>

            <div class="glass-card-premium" id="category-configs">
                <div class="config-header">
                    <div class="icon-box-gradient" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i data-lucide="tags"></i>
                    </div>
                    <div>
                        <h3 style="font-weight: 950; font-size: 1.4rem; color: #0f172a; letter-spacing: -0.02em; margin: 0;">Item Categories</h3>
                        <p style="font-size: 0.85rem; color: #64748b; font-weight: 700; margin-top: 4px;">Classification system for all system inventory.</p>
                    </div>
                </div>

                <div class="config-body" style="background: #fcfdfe;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 3rem;">
                        <div>
                            <h5 style="font-weight: 900; color: #475569; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2rem; display: flex; align-items: center; gap: 8px;">
                                <i data-lucide="list-tree" style="width: 18px;"></i> Existing Classifications
                            </h5>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                                @foreach($categories ?? [] as $code => $name)
                                    <div class="ledger-badge">
                                        <div class="ledger-code">{{ $code }}</div>
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="font-weight: 800; font-size: 0.85rem; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $name }}</div>
                                        </div>
                                        <form action="{{ route('admin.settings.category.destroy', $code) }}" method="POST" onsubmit="return confirm('Archive classification {{ $code }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background: none; border: none; color: #cbd5e1; cursor: pointer; transition: 0.3s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#cbd5e1'">
                                                <i data-lucide="x-circle" style="width: 18px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div style="background: white; border-radius: 24px; padding: 2rem; border: 1px solid #f1f5f9; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
                            <h5 style="font-weight: 900; color: #0f172a; font-size: 1.1rem; margin-top: 0; margin-bottom: 0.5rem;">New Classification</h5>
                            <p style="font-size: 0.8rem; color: #64748b; font-weight: 500; margin-bottom: 2rem;">Register a new ledger code for inventory tracking.</p>
                            
                            <form action="{{ route('admin.settings.category') }}" method="POST">
                                @csrf
                                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                                    <div>
                                        <label class="label-premium" style="font-size: 0.8rem; margin-bottom: 0.75rem;">Code Identifier</label>
                                        <input type="text" name="category_code" class="input-premium" placeholder="e.g. M" required maxlength="3" style="text-transform: uppercase;">
                                    </div>
                                    <div>
                                        <label class="label-premium" style="font-size: 0.8rem; margin-bottom: 0.75rem;">Official Name</label>
                                        <input type="text" name="category_name" class="input-premium" placeholder="e.g. Medical Assets" required>
                                    </div>
                                    <button type="submit" class="btn-emerald" style="padding: 1rem; border-radius: 16px; margin-top: 1rem;">
                                        <i data-lucide="plus-square" style="width: 18px;"></i> Register in Registry
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function filterParameters() {
        const term = document.getElementById('parameterSearch').value.toLowerCase();
        const items = document.querySelectorAll('.setting-item-premium');
        const groups = document.querySelectorAll('.glass-card-premium');

        items.forEach(item => {
            const label = item.querySelector('.label-premium').textContent.toLowerCase();
            const desc = item.querySelector('.desc-premium') ? item.querySelector('.desc-premium').textContent.toLowerCase() : '';
            
            if (label.includes(term) || desc.includes(term)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });

        // Hide groups if all items inside are hidden
        groups.forEach(group => {
            if (group.id === 'category-configs') return; // Don't hide category management
            
            const visibleItems = group.querySelectorAll('.setting-item-premium[style="display: block;"], .setting-item-premium:not([style*="display: none"])');
            if (visibleItems.length === 0) {
                group.style.display = 'none';
            } else {
                group.style.display = 'block';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) lucide.createIcons();
        
        // Simple scroll spy for side nav
        const navItems = document.querySelectorAll('.nav-item');
        const observerOptions = {
            threshold: 0.2
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    navItems.forEach(nav => {
                        nav.classList.remove('active');
                        if (nav.getAttribute('href') === '#' + entry.target.id) {
                            nav.classList.add('active');
                        }
                    });
                }
            });
        }, observerOptions);

        document.querySelectorAll('.glass-card-premium[id]').forEach(card => {
            observer.observe(card);
        });
    });
</script>
@endsection
