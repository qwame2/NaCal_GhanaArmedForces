@extends('layouts.admin')

@section('title', 'Global Settings')

@section('content')
<style>
    .settings-container, .settings-container * {
        box-sizing: border-box;
    }
    .settings-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .settings-card {
        background: white;
        border-radius: 16px;
        box-shadow: var(--shadow-luxe);
        border: 1px solid rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .settings-header {
        padding: 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .settings-icon {
        width: 40px;
        height: 40px;
        background: rgba(79, 70, 229, 0.1);
        color: var(--primary);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .settings-title {
        font-weight: 800;
        color: #0f172a;
        font-size: 1.1rem;
        margin: 0;
    }
    .settings-subtitle {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 500;
        margin: 0;
    }
    .settings-body {
        padding: 1.5rem;
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 0.95rem;
        color: #1e293b;
        transition: 0.3s;
    }
    .form-control:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }
    .btn-save {
        background: var(--primary);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-save:hover {
        background: var(--primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(79, 70, 229, 0.25);
    }

    /* Premium Toggle Switch */
    .premium-toggle {
        position: relative;
        display: inline-block;
        width: 52px;
        height: 28px;
        flex-shrink: 0;
    }
    .premium-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-track {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 30px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);
    }
    .toggle-thumb {
        position: absolute;
        content: "";
        height: 22px;
        width: 22px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15), 0 1px 2px rgba(0,0,0,0.1);
    }
    .premium-toggle input:checked + .toggle-track {
        background-color: var(--primary);
    }
    .premium-toggle input:checked + .toggle-track .toggle-thumb {
        transform: translateX(24px);
    }
    .premium-toggle input:focus + .toggle-track {
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }
    .toggle-wrapper {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: #f8fafc;
        padding: 8px 14px;
        border-radius: 12px;
        border: 1px solid #edf2f7;
        box-sizing: border-box;
    }
    .toggle-status-text {
        font-size: 0.85rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        min-width: 70px;
    }

    /* Category Management Styles */
    .category-management-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    @media(min-width: 900px) {
        .category-management-layout {
            grid-template-columns: 3fr 2fr;
        }
    }
    .category-badge-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
    }
    .category-badge-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .category-badge-card:hover {
        border-color: var(--primary);
        box-shadow: 0 6px 15px rgba(79, 70, 229, 0.1);
        transform: translateY(-2px);
    }
    .category-badge-content {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
        overflow: hidden;
    }
    .category-delete-btn {
        background: rgba(239, 68, 68, 0.05);
        color: #ef4444;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.3s;
        opacity: 0.5;
        flex-shrink: 0;
    }
    .category-badge-card:hover .category-delete-btn {
        opacity: 1;
    }
    .category-delete-btn:hover {
        background: #ef4444;
        color: white;
        transform: scale(1.05);
    }
    .category-code-pill {
        background: rgba(79, 70, 229, 0.1);
        color: var(--primary);
        font-weight: 900;
        font-size: 0.9rem;
        height: 36px;
        width: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .category-name-text {
        font-size: 0.9rem;
        font-weight: 800;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .new-category-panel {
        background: linear-gradient(145deg, #f8fafc, #f1f5f9);
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .btn-emerald {
        background: #10b981;
        color: white;
        border: none;
        padding: 0.85rem 1.5rem;
        border-radius: 10px;
        font-weight: 800;
        cursor: pointer;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
    }
    .btn-emerald:hover {
        background: #059669;
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(16, 185, 129, 0.3);
    }

    /* Premium Grid & Cards */
    .settings-group-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .settings-group-card:hover {
        box-shadow: 0 15px 35px rgba(0,0,0,0.06);
        border-color: #cbd5e1;
        transform: translateY(-2px);
    }
    .settings-group-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px dashed #f1f5f9;
    }
    .settings-group-icon {
        width: 48px;
        height: 48px;
        background: rgba(79, 70, 229, 0.05);
        color: var(--primary);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .settings-group-icon i {
        width: 24px;
        height: 24px;
    }
    .settings-group-title {
        margin: 0;
        font-weight: 900;
        color: #0f172a;
        font-size: 1.3rem;
        text-transform: capitalize;
        letter-spacing: -0.02em;
    }
    .settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1.5rem;
    }
    .setting-item {
        background: #f8fafc;
        border-radius: 14px;
        padding: 1.5rem;
        border: 1px solid #edf2f7;
        transition: 0.3s;
    }
    .setting-item:hover {
        background: white;
        border-color: #e2e8f0;
        box-shadow: 0 10px 25px rgba(0,0,0,0.03);
    }
    .setting-item-label {
        display: block;
        font-size: 0.9rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 1rem;
        letter-spacing: -0.01em;
    }
    .setting-item-desc {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 1rem;
        margin-bottom: 0;
        line-height: 1.6;
        font-weight: 500;
    }
    .premium-input {
        width: 100%;
        padding: 0.85rem 1.25rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        background: white;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-sizing: border-box;
    }
    .premium-input:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.15);
        transform: translateY(-2px);
    }
</style>

<div class="settings-container">
    <div class="settings-card">
        <div class="settings-header">
            <div class="settings-icon">
                <i data-lucide="settings-2"></i>
            </div>
            <div>
                <h3 class="settings-title">System Configurations</h3>
                <p class="settings-subtitle">Manage global application settings and parameters.</p>
            </div>
        </div>
        <div class="settings-body" style="background: #f8fafc;">
            @if($settings->isEmpty())
                <div style="padding: 3rem; text-align: center; color: #94a3b8;">
                    <i data-lucide="database" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <h4 style="color: #0f172a; font-weight: 800; font-size: 1.25rem;">Migration Required</h4>
                    <p>The global settings table has not been migrated yet.</p>
                    <p style="font-size: 0.85rem; margin-top: 10px;">Please run <code>php artisan migrate</code> to initialize the default settings.</p>
                </div>
            @else
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    
                    @foreach($settings as $group => $groupSettings)
                        @php
                            $icon = 'settings';
                            if ($group === 'security') $icon = 'shield-alert';
                            elseif ($group === 'inventory') $icon = 'package';
                            elseif ($group === 'system') $icon = 'server';
                            elseif ($group === 'ui') $icon = 'layout';
                        @endphp
                        <div class="settings-group-card">
                            <div class="settings-group-header">
                                <div class="settings-group-icon">
                                    <i data-lucide="{{ $icon }}"></i>
                                </div>
                                <div>
                                    <h4 class="settings-group-title">{{ $group }} Core</h4>
                                    <p style="margin: 0; font-size: 0.8rem; color: #64748b; margin-top: 4px; font-weight: 600;">Manage {{ $group }} parameters and thresholds.</p>
                                </div>
                            </div>
                            
                            <div class="settings-grid">
                                @foreach($groupSettings as $setting)
                                    <div class="setting-item">
                                        <label class="setting-item-label" for="setting_{{ $setting->key }}">
                                            {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        </label>
                                        
                                        @if($setting->type === 'boolean')
                                            <div class="toggle-wrapper" style="width: 100%; justify-content: space-between;">
                                                <span class="toggle-status-text" style="color: {{ $setting->value === 'true' ? 'var(--primary)' : '#64748b' }}; min-width: auto;">
                                                    {{ $setting->value === 'true' ? 'ENABLED' : 'DISABLED' }}
                                                </span>
                                                <label class="premium-toggle" style="margin: 0;">
                                                    <input type="checkbox" name="{{ $setting->key }}" value="true" {{ $setting->value === 'true' ? 'checked' : '' }} onchange="this.closest('.toggle-wrapper').querySelector('.toggle-status-text').textContent = this.checked ? 'ENABLED' : 'DISABLED'; this.closest('.toggle-wrapper').querySelector('.toggle-status-text').style.color = this.checked ? 'var(--primary)' : '#64748b';">
                                                    <span class="toggle-track">
                                                        <span class="toggle-thumb"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        @elseif($setting->type === 'integer')
                                            <input type="number" id="setting_{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}" class="premium-input">
                                        @else
                                            <input type="text" id="setting_{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}" class="premium-input">
                                        @endif
                                        
                                        @if($setting->description)
                                            <p class="setting-item-desc">{{ $setting->description }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div style="display: flex; justify-content: flex-end; padding-top: 1rem;">
                        <button type="submit" class="btn-save">
                            <i data-lucide="save" style="width: 18px;"></i>
                            Commit Configurations
                        </button>
                    </div>
                </form>

                <hr style="border: 0; border-top: 2px dashed #e2e8f0; margin: 3rem 0;">

                <div class="settings-group-card">
                    <div class="settings-group-header">
                        <div class="settings-group-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i data-lucide="tags"></i>
                        </div>
                        <div>
                            <h4 class="settings-group-title">Category Management</h4>
                            <p style="margin: 0; font-size: 0.8rem; color: #64748b; margin-top: 4px; font-weight: 600;">Introduce and manage global inventory categories.</p>
                        </div>
                    </div>

                    <div class="category-management-layout">
                        <!-- List Existing Categories -->
                        <div style="background: #f8fafc; border: 1px solid #edf2f7; border-radius: 16px; padding: 1.5rem;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 1.5rem;">
                                <i data-lucide="layers" style="color: #64748b; width: 20px; height: 20px;"></i>
                                <h5 style="margin: 0; font-size: 1.05rem; font-weight: 900; color: #1e293b;">Active Categories</h5>
                            </div>
                            <div class="category-badge-grid">
                                @foreach($categories ?? [] as $code => $name)
                                    <div class="category-badge-card">
                                        <div class="category-badge-content">
                                            <div class="category-code-pill">{{ $code }}</div>
                                            <div class="category-name-text" title="{{ $name }}">{{ $name }}</div>
                                        </div>
                                        <form action="{{ route('admin.settings.category.destroy', $code) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete category {{ $code }}? This will not retroactively alter old records.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="category-delete-btn" title="Delete Category">
                                                <i data-lucide="trash-2" style="width: 16px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Add New Category Form -->
                        <div class="new-category-panel">
                            <div>
                                <h5 style="margin-top: 0; font-size: 1.05rem; font-weight: 900; color: #1e293b; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 8px;">
                                    <i data-lucide="plus-circle" style="color: #10b981; width: 20px; height: 20px;"></i>
                                    Introduce New Category
                                </h5>
                                <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Add a new classification to the global system.</p>
                            </div>
                            
                            <form action="{{ route('admin.settings.category') }}" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                                @csrf
                                <div>
                                    <label class="setting-item-label" for="category_code" style="margin-bottom: 0.5rem;">Category Code</label>
                                    <input type="text" id="category_code" name="category_code" class="premium-input" placeholder="e.g. F" required style="text-transform: uppercase;">
                                    <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.5rem; margin-bottom: 0; font-weight: 500;">A short unique identifier (1-3 chars).</p>
                                </div>
                                <div>
                                    <label class="setting-item-label" for="category_name" style="margin-bottom: 0.5rem;">Category Name</label>
                                    <input type="text" id="category_name" name="category_name" class="premium-input" placeholder="e.g. Kitchen Supplies" required>
                                    <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.5rem; margin-bottom: 0; font-weight: 500;">The official display name.</p>
                                </div>
                                <div style="margin-top: 0.5rem;">
                                    <button type="submit" class="btn-emerald">
                                        <i data-lucide="shield-plus" style="width: 18px;"></i>
                                        Register Category
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        

    </div>
</div>
@endsection
