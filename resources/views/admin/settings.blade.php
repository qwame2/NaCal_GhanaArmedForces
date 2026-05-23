@extends('layouts.admin')

@section('title', 'System Configuration')

@section('content')
<style>
    /* ── Page Layout ── */
    .settings-shell {
        display: block;
        margin-top: 0.5rem;
        animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }



    /* ── Main Content ── */
    .cfg-main {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    /* ── Search Bar ── */
    .cfg-search-wrap {
        position: relative;
        max-width: 420px;
    }

    .cfg-search-wrap i {
        position: absolute;
        left: 1.1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        width: 18px;
        pointer-events: none;
    }

    .cfg-search-wrap input {
        width: 100%;
        padding: 0.85rem 1.25rem 0.85rem 3rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 20px;
        font-size: 0.88rem;
        font-weight: 700;
        color: #0f172a;
        background: white;
        outline: none;
        transition: 0.25s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }

    .cfg-search-wrap input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(79,70,229,0.1);
    }

    .cfg-search-wrap input::placeholder { color: #94a3b8; font-weight: 500; }

    /* ── Section Cards ── */
    .cfg-card {
        background: white;
        border-radius: 28px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 24px rgba(0,0,0,0.04);
        overflow: hidden;
    }

    .cfg-card-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.75rem 2rem;
        border-bottom: 1px solid #f8fafc;
        background: linear-gradient(to right, #fafbff, white);
    }

    .cfg-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }

    .cfg-icon-box i { width: 22px; height: 22px; }

    .cfg-card-header h3 {
        font-size: 1.1rem;
        font-weight: 900;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.02em;
    }

    /* ── Select2 Custom Styling ── */
    .select2-container--default .select2-selection--single {
        height: 48px !important;
        background: white !important;
        border: 2px solid #edf2f7 !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        transition: 0.2s !important;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #4f46e5 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b !important;
        font-weight: 600 !important;
        font-size: 0.9rem !important;
        padding-left: 12px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
        right: 10px !important;
    }

    .select2-dropdown {
        border: 1px solid #edf2f7 !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important;
        overflow: hidden !important;
        z-index: 9999 !important;
    }

    .select2-results__option {
        padding: 10px 15px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        color: #475569 !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #4f46e5 !important;
        color: white !important;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border-radius: 8px !important;
        border: 1.5px solid #edf2f7 !important;
        padding: 8px 12px !important;
    }

    .cfg-card-header p {
        font-size: 0.78rem;
        color: #94a3b8;
        font-weight: 600;
        margin: 2px 0 0;
    }

    .cfg-card-body { padding: 2rem; }

    /* ── Settings Grid ── */
    .cfg-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.25rem;
    }

    /* ── Setting Item ── */
    .cfg-item {
        background: #f8fafc;
        border: 1.5px solid #f1f5f9;
        border-radius: 20px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .cfg-item:hover {
        background: white;
        border-color: #c7d2fe;
        box-shadow: 0 8px 24px rgba(79,70,229,0.07);
        transform: translateY(-2px);
    }

    .cfg-item-label {
        font-size: 0.88rem;
        font-weight: 900;
        color: #1e293b;
        margin: 0;
        letter-spacing: -0.01em;
    }

    .cfg-item-desc {
        font-size: 0.72rem;
        color: #94a3b8;
        font-weight: 600;
        line-height: 1.5;
        margin: 0;
    }

    /* ── Premium Toggle ── */
    .toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: white;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 0.7rem 1rem;
    }

    .toggle-pill {
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 3px 10px;
        border-radius: 30px;
        transition: 0.3s;
    }

    .toggle-pill.on  { background: #dcfce7; color: #16a34a; }
    .toggle-pill.off { background: #f1f5f9; color: #94a3b8; }

    .cfg-toggle-label { position: relative; display: inline-block; cursor: pointer; }
    .cfg-toggle-label input { display: none; }

    .cfg-toggle-track {
        display: block;
        width: 52px;
        height: 28px;
        background: #e2e8f0;
        border-radius: 30px;
        transition: background 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
    }

    .cfg-toggle-track::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 22px;
        height: 22px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .cfg-toggle-label input:checked + .cfg-toggle-track { background: #4f46e5; }
    .cfg-toggle-label input:checked + .cfg-toggle-track::after { transform: translateX(24px); }

    /* ── Number Input ── */
    .cfg-number-input {
        width: 100%;
        box-sizing: border-box;
        padding: 0.75rem 1rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        font-size: 0.95rem;
        font-weight: 800;
        color: #1e293b;
        outline: none;
        transition: 0.25s;
        background: white;
    }

    .cfg-number-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(79,70,229,0.1);
    }

    /* ── Text Input ── */
    .cfg-text-input {
        width: 100%;
        box-sizing: border-box;
        padding: 0.75rem 1rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e293b;
        outline: none;
        transition: 0.25s;
        background: white;
    }

    .cfg-text-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(79,70,229,0.1);
    }

    /* ── Category Badges ── */
    .cat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 0.75rem;
    }

    .cat-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0.75rem 1rem;
        background: white;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        transition: 0.3s;
    }

    .cat-badge:hover { border-color: #a7f3d0; background: #f0fdf4; }

    .cat-code-pill {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: linear-gradient(135deg, #4f46e5, #3730a3);
        color: white;
        font-weight: 900;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .cat-name {
        flex: 1;
        font-weight: 800;
        font-size: 0.82rem;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cat-del-btn {
        background: none;
        border: none;
        color: #cbd5e1;
        cursor: pointer;
        transition: 0.2s;
        padding: 2px;
        display: flex;
        align-items: center;
    }

    .cat-del-btn:hover { color: #ef4444; transform: scale(1.1); }
    .cat-del-btn i { width: 16px; height: 16px; }

    /* ── New Category Form ── */
    .cat-form-card {
        background: white;
        border: 1.5px solid #f1f5f9;
        border-radius: 22px;
        padding: 1.75rem;
        box-shadow: 0 4px 16px rgba(0,0,0,0.02);
    }

    .cat-form-card h5 {
        font-size: 1rem;
        font-weight: 900;
        color: #0f172a;
        margin: 0 0 0.25rem;
    }

    .cat-form-card p {
        font-size: 0.78rem;
        color: #94a3b8;
        font-weight: 600;
        margin: 0 0 1.5rem;
    }

    /* ── Sticky Save Bar ── */
    .cfg-save-bar {
        position: sticky;
        bottom: 1.5rem;
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(79,70,229,0.12);
        border-radius: 20px;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        box-shadow: 0 12px 40px rgba(0,0,0,0.08);
        z-index: 50;
    }

    .cfg-save-bar span {
        font-size: 0.82rem;
        font-weight: 700;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .cfg-save-bar span i { width: 15px; color: #f59e0b; }

    .btn-cfg-save {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.75rem 2rem;
        background: linear-gradient(135deg, #4f46e5, #3730a3);
        color: white;
        border: none;
        border-radius: 14px;
        font-weight: 900;
        font-size: 0.9rem;
        cursor: pointer;
        transition: 0.25s;
        box-shadow: 0 8px 20px rgba(79,70,229,0.25);
    }

    .btn-cfg-save:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(79,70,229,0.35); }
    .btn-cfg-save:active { transform: scale(0.97); }
    .btn-cfg-save i { width: 18px; }

    .btn-cfg-add {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border: none;
        border-radius: 14px;
        font-weight: 900;
        font-size: 0.88rem;
        cursor: pointer;
        width: 100%;
        justify-content: center;
        transition: 0.25s;
        margin-top: 1rem;
        box-shadow: 0 6px 16px rgba(16,185,129,0.25);
    }

    .btn-cfg-add:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(16,185,129,0.3); }
    .btn-cfg-add i { width: 18px; }

    /* ── OTP Expiry Block ── */
    .otp-preset-btn {
        padding: 5px 12px;
        border-radius: 999px;
        border: 1.5px solid #e2e8f0;
        background: white;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s;
    }
    .otp-preset-btn:hover { background: #f1f5f9; border-color: #cbd5e1; }
    .otp-preset-btn.active { background: #4f46e5; color: white; border-color: #4f46e5; }
</style>

{{-- Page Header --}}
<div class="view-header" style="margin-bottom: 2rem;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <div style="width: 52px; height: 52px; background: linear-gradient(135deg, #4f46e5, #3730a3); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 8px 20px rgba(79,70,229,0.25);">
            <i data-lucide="sliders-horizontal" style="width: 24px;"></i>
        </div>
        <div>
            <h2 style="font-weight: 950; color: #0f172a; font-size: 1.8rem; letter-spacing: -0.04em; margin: 0;">System Configuration</h2>
            <p style="color: #94a3b8; font-weight: 600; font-size: 0.9rem; margin: 2px 0 0;">Manage system settings, limits, and item categories.</p>
        </div>
    </div>
</div>

<div class="settings-shell">

    {{-- Main Content --}}
    <div class="cfg-main">



        @if($settings->isEmpty())
            <div class="cfg-card" style="padding: 5rem 2rem; text-align: center;">
                <div style="width: 96px; height: 96px; background: #f1f5f9; border-radius: 30px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
                    <i data-lucide="database" style="width: 40px; height: 40px; color: #94a3b8;"></i>
                </div>
                <h3 style="font-weight: 900; font-size: 1.4rem; color: #0f172a; margin-bottom: 0.5rem;">No Configuration Found</h3>
                <p style="color: #64748b; font-size: 0.9rem; font-weight: 600; max-width: 360px; margin: 0 auto 1.5rem; line-height: 1.6;">Run <code>php artisan migrate --seed</code> to populate the global settings registry.</p>
            </div>
        @else
            <form action="{{ route('admin.settings.update') }}" method="POST" id="core-configs">
                @csrf

                @foreach($settings as $group => $groupSettings)
                    @php if($group === 'inventory') continue; @endphp

                    @php
                        $colorMap = [
                            'security' => ['color' => '#ef4444', 'bg' => 'linear-gradient(135deg,#ef4444,#dc2626)', 'icon' => 'shield-alert'],
                            'inventory' => ['color' => '#10b981', 'bg' => 'linear-gradient(135deg,#10b981,#059669)', 'icon' => 'package'],
                            'system'   => ['color' => '#f59e0b', 'bg' => 'linear-gradient(135deg,#f59e0b,#d97706)', 'icon' => 'server'],
                        ];
                        $meta = $colorMap[$group] ?? ['color' => '#4f46e5', 'bg' => 'linear-gradient(135deg,#4f46e5,#3730a3)', 'icon' => 'settings'];
                    @endphp

                    <div class="cfg-card" style="margin-bottom: 1.5rem;">
                        <div class="cfg-card-header">
                            <div class="cfg-icon-box" style="background: {{ $meta['bg'] }};">
                                <i data-lucide="{{ $meta['icon'] }}"></i>
                            </div>
                            <div>
                                <h3>{{ ucfirst($group) }} Settings</h3>
                                <p>Manage {{ $group }} settings and system limits.</p>
                            </div>
                        </div>
                        <div class="cfg-card-body">
                            <div class="cfg-grid">
                                @foreach($groupSettings as $setting)
                                    @php if(in_array($setting->key, ['strict_audit_logging','enable_strict_audit_logging','approval_timeout_minutes','item_unit_rules','ledge_categories','reporting_enabled','allow_personnel_registration','low_stock_threshold','item_threshold_rules','otp_expiry_hours','otp_expiry_minutes'])) continue; @endphp
                                    <div class="cfg-item">
                                        <p class="cfg-item-label">{{ ucwords(str_replace('_', ' ', $setting->key)) }}</p>

                                        @if($setting->type === 'boolean')
                                            <div class="toggle-row">
                                                <span class="toggle-pill {{ $setting->value === 'true' ? 'on' : 'off' }}" id="pill_{{ $setting->key }}">
                                                    {{ $setting->value === 'true' ? 'Active' : 'Inactive' }}
                                                </span>
                                                <label class="cfg-toggle-label" title="Toggle {{ ucwords(str_replace('_',' ',$setting->key)) }}">
                                                    <input type="checkbox" name="{{ $setting->key }}" value="true"
                                                        {{ $setting->value === 'true' ? 'checked' : '' }}
                                                        onchange="
                                                            const pill = document.getElementById('pill_{{ $setting->key }}');
                                                            if(this.checked){ pill.textContent='Active'; pill.className='toggle-pill on'; }
                                                            else { pill.textContent='Inactive'; pill.className='toggle-pill off'; }
                                                        ">
                                                    <span class="cfg-toggle-track"></span>
                                                </label>
                                            </div>
                                        @elseif($setting->type === 'integer')
                                            <input type="number" id="setting_{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}" class="cfg-number-input">
                                        @else
                                            <input type="text" id="setting_{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}" class="cfg-text-input">
                                        @endif

                                        @if($setting->description)
                                            <p class="cfg-item-desc">{{ $setting->description }}</p>
                                        @endif
                                    </div>
                                @endforeach

                                {{-- OTP Expiry — inject inside the grid for Security Protocols --}}
                                @if($group === 'security')
                                @php $otpExpiry = \App\Models\Setting::firstOrCreate(
                                    ['key' => 'otp_expiry_minutes'],
                                    ['value' => '1440', 'type' => 'integer', 'group' => 'security', 'label' => 'OTP Expiry (Minutes)', 'description' => 'Number of minutes before a generated recovery OTP expires and becomes invalid.']
                                ); @endphp
                                <div class="cfg-item" style="grid-column: span 2; max-width: 800px;">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                                        <i data-lucide="timer" style="width: 18px; color: #4f46e5;"></i>
                                        <p class="cfg-item-label" style="margin: 0;">Recovery OTP Expiration (Minutes)</p>
                                    </div>
                                    <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px; flex-wrap: wrap;">
                                        <input type="number" name="otp_expiry_minutes" id="setting_otp_expiry_minutes" value="{{ $otpExpiry->value }}" min="3" max="10080" class="cfg-number-input" style="max-width: 120px;" oninput="updateOtpPreview(this.value)">
                                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                            @foreach([3 => '3 mins', 15 => '15 mins', 30 => '30 mins', 60 => '1 hr', 360 => '6 hrs', 1440 => '24 hrs'] as $val => $label)
                                            <button type="button" class="otp-preset-btn {{ $otpExpiry->value == $val ? 'active' : '' }}" onclick="setOtpExpiry({{ $val }}, this)">{{ $label }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                    <p class="cfg-item-desc" id="otp-preview-text">Set how long an admin-issued OTP remains valid. Currently set to expire <strong>{{ $otpExpiry->value }} minute{{ $otpExpiry->value == 1 ? '' : 's' }}</strong> after it has been generated.</p>
                                </div>
                                @endif

                            </div>
                        </div>{{-- /cfg-card-body --}}
                    </div>{{-- /cfg-card --}}
                @endforeach



                {{-- Sticky Save Bar --}}
                <div class="cfg-save-bar">
                    <span><i data-lucide="info"></i> Unsaved changes will be lost on navigation.</span>
                    <button type="submit" class="btn-cfg-save">
                        <i data-lucide="save"></i> Save Settings
                    </button>
                </div>
            </form>

            {{-- Category Management --}}
            <div class="cfg-card" id="category-configs">
                <div class="cfg-card-header">
                    <div class="cfg-icon-box" style="background: linear-gradient(135deg,#10b981,#059669);">
                        <i data-lucide="tags"></i>
                    </div>
                    <div>
                        <h3>Item Categories</h3>
                        <p>Manage category codes for the inventory system.</p>
                    </div>
                </div>
                <div class="cfg-card-body">
                    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 2rem; align-items: start;">

                        {{-- Existing Categories --}}
                        <div>
                            <p style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 1rem; display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="list" style="width: 14px;"></i> Category Codes
                            </p>
                            <div class="cat-grid">
                                @forelse($categories ?? [] as $code => $name)
                                    <div class="cat-badge">
                                        <div class="cat-code-pill">{{ $code }}</div>
                                        <span class="cat-name" title="{{ $name }}">{{ $name }}</span>
                                        <form action="{{ route('admin.settings.category.destroy', $code) }}" method="POST" onsubmit="return confirm('Remove category {{ $code }}?');" style="margin: 0;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="cat-del-btn">
                                                <i data-lucide="x"></i>
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <div style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; padding: 1rem 0;">No categories registered yet.</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Add New Category --}}
                        <div class="cat-form-card">
                            <h5>Add New Category</h5>
                            <p>Register a new ledger code for inventory tracking.</p>
                            <form action="{{ route('admin.settings.category') }}" method="POST">
                                @csrf
                                <div style="display: flex; flex-direction: column; gap: 1rem;">
                                    <div>
                                        <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Code</label>
                                        <input type="text" name="category_code" class="cfg-text-input" placeholder="e.g. M" required maxlength="3" style="text-transform: uppercase; font-size: 1.1rem; font-weight: 900; text-align: center;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Category Name</label>
                                        <input type="text" name="category_name" class="cfg-text-input" placeholder="e.g. Medical Assets" required>
                                    </div>
                                    <button type="submit" class="btn-cfg-add">
                                        <i data-lucide="plus-circle"></i> Register Category
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Item Package Type Rules --}}
    <div class="cfg-card" id="unit-rules" style="margin-top: 2rem;">
        <div class="cfg-card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div class="cfg-icon-box" style="background: linear-gradient(135deg,#f59e0b,#d97706);">
                    <i data-lucide="ruler"></i>
                </div>
                <div>
                    <h3 style="margin: 0 0 0.25rem 0;">Default Package Types</h3>
                    <p style="margin: 0;">Set default units (like Piece or Box) for items to save time during entry.</p>
                </div>
            </div>

            <div class="cfg-search-wrap" style="margin: 0 3.5rem 0 0; width: 260px; position: relative;">
                <i data-lucide="search" style="width: 14px; position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                <input type="text" id="ruleSearch" placeholder="Search rules..." oninput="filterRules()" style="width: 100%; padding: 0.5rem 1rem 0.5rem 2.2rem; font-size: 0.8rem; height: 38px; border: 2px solid #edf2f7; border-radius: 10px; outline: none; transition: 0.2s; background: white;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#edf2f7'">
            </div>
        </div>
        <div class="cfg-card-body">
            <div style="display: grid; grid-template-columns: 1fr 360px; gap: 2rem; align-items: start;">

                {{-- Existing Rules --}}
                <div>
                    <p style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 1rem; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="list" style="width: 14px;"></i> Active Package Type Rules
                    </p>
                    @php
                        $unitRules = json_decode(\App\Models\Setting::where('key','item_unit_rules')->value('value') ?? '{}', true) ?? [];
                        $groupedRules = [];
                        foreach ($unitRules as $keyword => $data) {
                            $cat = is_array($data) ? $data['category'] : 'Uncategorized';
                            $unit = is_array($data) ? $data['unit'] : $data;
                            $loc = is_array($data) ? ($data['location'] ?? 'Not Specified') : 'Not Specified';
                            $groupedRules[$cat][$keyword] = [
                                'unit' => $unit,
                                'location' => $loc
                            ];
                        }
                    @endphp
                    @if(empty($groupedRules))
                        <div style="padding: 2rem; text-align: center; background: #f8fafc; border-radius: 16px; border: 1.5px dashed #e2e8f0;">
                            <i data-lucide="inbox" style="width: 32px; height: 32px; color: #cbd5e1; margin-bottom: 0.75rem;"></i>
                            <p style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; margin: 0;">No unit rules defined yet. Add rules on the right.</p>
                        </div>
                    @else
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;" id="rulesContainer">
                            @foreach($groupedRules as $catCode => $rulesGroup)
                                <div class="unit-rule-group">
                                    <h6 style="font-size: 0.85rem; font-weight: 800; color: #475569; margin: 0 0 0.75rem; display: flex; align-items: center; gap: 8px;">
                                        <span style="background: #e2e8f0; color: #475569; padding: 3px 8px; border-radius: 6px; font-size: 0.7rem;">{{ $catCode }}</span>
                                        {{ $categories[$catCode] ?? 'Uncategorized' }}
                                    </h6>
                                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.75rem;">
                                        @foreach($rulesGroup as $keyword => $ruleData)
                                            @php
                                                $unit = $ruleData['unit'];
                                                $loc = $ruleData['location'];
                                            @endphp
                                            <div class="unit-rule-card" data-keyword="{{ strtolower($keyword) }}" style="display: flex; align-items: center; gap: 10px; padding: 0.75rem 1rem; background: white; border: 1.5px solid #f1f5f9; border-radius: 16px; transition: 0.3s;">
                                                <div style="width: 34px; height: 34px; border-radius: 10px; background: linear-gradient(135deg,#f59e0b,#d97706); color: white; font-weight: 900; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; text-transform: uppercase;">
                                                    {{ strtoupper(substr($keyword, 0, 2)) }}
                                                </div>
                                                <div style="flex: 1; min-width: 0;">
                                                    <div style="font-weight: 800; font-size: 0.82rem; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $keyword }}">{{ $keyword }}</div>
                                                    <div style="font-size: 0.7rem; font-weight: 700; color: #64748b;">→ {{ $unit }}</div>
                                                    <div style="font-size: 0.65rem; font-weight: 600; color: #4f46e5; margin-top: 2px; display: flex; align-items: center; gap: 4px;">
                                                        <i data-lucide="map-pin" style="width: 10px; height: 10px;"></i> {{ $loc }}
                                                    </div>
                                                </div>
                                                <div style="display: flex; gap: 4px;">
                                                    <button type="button" onclick="populateUnitForm('{{ $keyword }}', '{{ $unit }}', '{{ $catCode }}', '{{ $loc }}')" style="background: none; border: none; color: #cbd5e1; cursor: pointer; transition: 0.2s; padding: 2px; display: flex; align-items: center;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#cbd5e1'" title="Edit Rule">
                                                        <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i>
                                                    </button>
                                                    <form action="{{ route('admin.settings.unit-rule.destroy') }}" method="POST" onsubmit="return confirm('Remove rule for \'{{ $keyword }}\'?');" style="margin: 0;">
                                                        @csrf @method('DELETE')
                                                        <input type="hidden" name="keyword" value="{{ $keyword }}">
                                                        <button type="submit" style="background: none; border: none; color: #cbd5e1; cursor: pointer; transition: 0.2s; padding: 2px; display: flex; align-items: center;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#cbd5e1'" title="Remove Rule">
                                                            <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Add New Rule --}}
                <div class="cat-form-card">
                    <h5>Add Package Type Rule</h5>
                    <p>Type a keyword (e.g. "pen") and set its default unit (e.g. "Boxes"). Matching is case-insensitive.</p>
                    <form action="{{ route('admin.settings.unit-rule.store') }}" method="POST">
                        @csrf
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Target Category</label>
                                <select name="category" id="unitCategory" class="cfg-text-input" required style="cursor: pointer;">
                                    <option value="">Select Category...</option>
                                    @foreach($categories ?? [] as $code => $name)
                                        <option value="{{ $code }}">[{{ $code }}] {{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Item Keyword</label>
                                <select name="keyword" id="unitKeyword" class="cfg-text-input select2-unit" required>
                                    <option value="">Select Category First...</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Default Package Type</label>
                                <select name="unit" class="cfg-text-input select2-package" required style="cursor: pointer;">
                                    <option value=""></option>
                                    <option value="Piece(s)">Piece(s)</option>
                                    <option value="Pack">Pack</option>
                                    <option value="Boxes">Boxes</option>
                                    <option value="Carton">Carton</option>
                                    <option value="Bag">Bag</option>
                                    <option value="Roll">Roll</option>
                                    <option value="Set">Set</option>
                                    <option value="Ream">Ream</option>
                                    <option value="Bottle">Bottle</option>
                                    <option value="Package Type">Package Type</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Store Location</label>
                                @php
                                    $defaultLocations = [
                                        'Main Store',
                                        'Pharmacy Store',
                                        'Stationery Store',
                                        'Cleaning Store',
                                        'IT & Equipment Store',
                                        'Safety Store'
                                    ];
                                    $dbLocations = \App\Models\InventoryItem::whereNotNull('location')
                                        ->where('location', '!=', '')
                                        ->where('location', '!=', 'Not Specified')
                                        ->distinct()
                                        ->pluck('location')
                                        ->toArray();
                                    $allLocations = array_unique(array_merge($defaultLocations, $dbLocations));
                                @endphp
                                <select name="location" id="unitLocation" class="cfg-text-input select2-location" required style="cursor: pointer;">
                                    <option value=""></option>
                                    @foreach($allLocations as $loc)
                                        <option value="{{ $loc }}">{{ $loc }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" id="unitSubmitBtn" class="btn-cfg-add" style="flex: 1; background: linear-gradient(135deg,#f59e0b,#d97706); box-shadow: 0 6px 16px rgba(245,158,11,0.25);">
                                    <i data-lucide="plus-circle" id="unitSubmitIcon"></i> <span id="unitSubmitText">Add Rule</span>
                                </button>
                                <button type="button" id="unitResetBtn" onclick="resetUnitForm()" style="display: none; padding: 0.75rem 1rem; background: #f1f5f9; color: #64748b; border: none; border-radius: 14px; font-weight: 800; cursor: pointer; transition: 0.2s; margin-top: 1rem;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    {{-- Item Threshold Rules --}}
    <div class="cfg-card" id="threshold-rules" style="margin-top: 2rem;">
        <div class="cfg-card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div class="cfg-icon-box" style="background: linear-gradient(135deg,#ef4444,#dc2626);">
                    <i data-lucide="alert-triangle"></i>
                </div>
                <div>
                    <h3 style="margin: 0 0 0.25rem 0;">Item Threshold Rules</h3>
                    <p style="margin: 0;">Define specific low stock thresholds for items. Only items matching these rules will trigger alerts.</p>
                </div>
            </div>

            <div class="cfg-search-wrap" style="margin: 0 3.5rem 0 0; width: 260px; position: relative;">
                <i data-lucide="search" style="width: 14px; position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                <input type="text" id="thresholdSearch" placeholder="Search thresholds..." oninput="filterThresholds()" style="width: 100%; padding: 0.5rem 1rem 0.5rem 2.2rem; font-size: 0.8rem; height: 38px; border: 2px solid #edf2f7; border-radius: 10px; outline: none; transition: 0.2s; background: white;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#edf2f7'">
            </div>
        </div>
        <div class="cfg-card-body">
            <div style="display: grid; grid-template-columns: 1fr 360px; gap: 2rem; align-items: start;">

                {{-- Existing Thresholds --}}
                <div>
                    <p style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 1rem; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="list" style="width: 14px;"></i> Active Threshold Rules
                    </p>
                    @php
                        $thresholdRules = json_decode(\App\Models\Setting::where('key','item_threshold_rules')->value('value') ?? '{}', true) ?? [];
                        $groupedThresholds = [];
                        foreach ($thresholdRules as $keyword => $data) {
                            $cat = is_array($data) ? $data['category'] : 'Uncategorized';
                            $threshold = is_array($data) ? $data['threshold'] : $data;
                            $groupedThresholds[$cat][$keyword] = $threshold;
                        }
                    @endphp
                    @if(empty($groupedThresholds))
                        <div style="padding: 2rem; text-align: center; background: #f8fafc; border-radius: 16px; border: 1.5px dashed #e2e8f0;">
                            <i data-lucide="inbox" style="width: 32px; height: 32px; color: #cbd5e1; margin-bottom: 0.75rem;"></i>
                            <p style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; margin: 0;">No threshold rules defined yet. Add rules on the right.</p>
                        </div>
                    @else
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;" id="thresholdsContainer">
                            @foreach($groupedThresholds as $catCode => $thresholdsGroup)
                                <div class="threshold-rule-group">
                                    <h6 style="font-size: 0.85rem; font-weight: 800; color: #475569; margin: 0 0 0.75rem; display: flex; align-items: center; gap: 8px;">
                                        <span style="background: #e2e8f0; color: #475569; padding: 3px 8px; border-radius: 6px; font-size: 0.7rem;">{{ $catCode }}</span>
                                        {{ $categories[$catCode] ?? 'Uncategorized' }}
                                    </h6>
                                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.75rem;">
                                        @foreach($thresholdsGroup as $keyword => $threshold)
                                            <div class="threshold-rule-card" data-keyword="{{ strtolower($keyword) }}" style="display: flex; align-items: center; gap: 10px; padding: 0.75rem 1rem; background: white; border: 1.5px solid #f1f5f9; border-radius: 16px; transition: 0.3s;">
                                                <div style="width: 34px; height: 34px; border-radius: 10px; background: linear-gradient(135deg,#ef4444,#dc2626); color: white; font-weight: 900; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; text-transform: uppercase;">
                                                    <i data-lucide="bell" style="width: 14px;"></i>
                                                </div>
                                                <div style="flex: 1; min-width: 0;">
                                                    <div style="font-weight: 800; font-size: 0.82rem; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $keyword }}">{{ $keyword }}</div>
                                                    @php
                                                        $displayUnit = \App\Models\Setting::getItemUnit($keyword);
                                                    @endphp
                                                    <div style="font-size: 0.7rem; font-weight: 700; color: #64748b;">Min: {{ $threshold }} {{ $displayUnit }}</div>
                                                </div>
                                                <div style="display: flex; gap: 4px;">
                                                    <button type="button" onclick="populateThresholdForm('{{ $keyword }}', {{ $threshold }}, '{{ $catCode }}')" style="background: none; border: none; color: #cbd5e1; cursor: pointer; transition: 0.2s; padding: 2px; display: flex; align-items: center;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#cbd5e1'" title="Edit Rule">
                                                        <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i>
                                                    </button>
                                                    <form action="{{ route('admin.settings.threshold-rule.destroy') }}" method="POST" onsubmit="return confirm('Remove threshold rule for \'{{ $keyword }}\'?');" style="margin: 0;">
                                                        @csrf @method('DELETE')
                                                        <input type="hidden" name="keyword" value="{{ $keyword }}">
                                                        <button type="submit" style="background: none; border: none; color: #cbd5e1; cursor: pointer; transition: 0.2s; padding: 2px; display: flex; align-items: center;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#cbd5e1'" title="Remove Rule">
                                                            <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Add New Threshold Rule --}}
                <div class="cat-form-card">
                    <h5>Add Threshold Rule</h5>
                    <p>Set a specific alert threshold for an item. These help catch low stock before it becomes critical.</p>
                    <form action="{{ route('admin.settings.threshold-rule.store') }}" method="POST">
                        @csrf
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Target Category</label>
                                <select name="category" id="thresholdCategory" class="cfg-text-input" required style="cursor: pointer;">
                                    <option value="">Select Category...</option>
                                    @foreach($categories ?? [] as $code => $name)
                                        <option value="{{ $code }}">[{{ $code }}] {{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Item Keyword</label>
                                <select name="keyword" id="thresholdKeyword" class="cfg-text-input select2-threshold" required>
                                    <option value="">Select Category First...</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Stock Threshold</label>
                                <input type="number" name="threshold" class="cfg-text-input" placeholder="e.g. 10" required min="0">
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" id="thresholdSubmitBtn" class="btn-cfg-add" style="flex: 1; background: linear-gradient(135deg,#ef4444,#dc2626); box-shadow: 0 6px 16px rgba(239,68,68,0.25);">
                                    <i data-lucide="plus-circle" id="thresholdSubmitIcon"></i> <span id="thresholdSubmitText">Add Threshold Rule</span>
                                </button>
                                <button type="button" id="thresholdResetBtn" onclick="resetThresholdForm()" style="display: none; padding: 0.75rem 1rem; background: #f1f5f9; color: #64748b; border: none; border-radius: 14px; font-weight: 800; cursor: pointer; transition: 0.2s; margin-top: 1rem;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    {{-- Item Request Limits --}}
    <div class="cfg-card" id="request-limits" style="margin-top: 2rem;">
        <div class="cfg-card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div class="cfg-icon-box" style="background: linear-gradient(135deg,#6366f1,#4f46e5);">
                    <i data-lucide="ban"></i>
                </div>
                <div>
                    <h3 style="margin: 0 0 0.25rem 0;">Item Request Limits</h3>
                    <p style="margin: 0;">Set maximum request limits on items. The Out of Stock status is shown on the dashboard when limits are met.</p>
                </div>
            </div>

            <div class="cfg-search-wrap" style="margin: 0 3.5rem 0 0; width: 260px; position: relative;">
                <i data-lucide="search" style="width: 14px; position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                <input type="text" id="limitSearch" placeholder="Search limits..." oninput="filterLimits()" style="width: 100%; padding: 0.5rem 1rem 0.5rem 2.2rem; font-size: 0.8rem; height: 38px; border: 2px solid #edf2f7; border-radius: 10px; outline: none; transition: 0.2s; background: white;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#edf2f7'">
            </div>
        </div>
        <div class="cfg-card-body">
            <div style="display: grid; grid-template-columns: 1fr 360px; gap: 2rem; align-items: start;">

                {{-- Existing Request Limits --}}
                <div>
                    <p style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 1rem; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="list" style="width: 14px;"></i> Active Request Limits
                    </p>
                    @php
                        $requestLimits = json_decode(\App\Models\Setting::where('key','item_request_limits')->value('value') ?? '{}', true) ?? [];
                        $groupedLimits = [];
                        foreach ($requestLimits as $keyword => $data) {
                            $cat = is_array($data) ? $data['category'] : 'Uncategorized';
                            $limit = is_array($data) ? $data['limit'] : $data;
                            $groupedLimits[$cat][$keyword] = $limit;
                        }
                    @endphp
                    @if(empty($groupedLimits))
                        <div style="padding: 2rem; text-align: center; background: #f8fafc; border-radius: 16px; border: 1.5px dashed #e2e8f0;">
                            <i data-lucide="inbox" style="width: 32px; height: 32px; color: #cbd5e1; margin-bottom: 0.75rem;"></i>
                            <p style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; margin: 0;">No request limits defined yet. Add rules on the right.</p>
                        </div>
                    @else
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;" id="limitsContainer">
                            @foreach($groupedLimits as $catCode => $limitsGroup)
                                <div class="limit-rule-group">
                                    <h6 style="font-size: 0.85rem; font-weight: 800; color: #475569; margin: 0 0 0.75rem; display: flex; align-items: center; gap: 8px;">
                                        <span style="background: #e2e8f0; color: #475569; padding: 3px 8px; border-radius: 6px; font-size: 0.7rem;">{{ $catCode }}</span>
                                        {{ $categories[$catCode] ?? 'Uncategorized' }}
                                    </h6>
                                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.75rem;">
                                        @foreach($limitsGroup as $keyword => $limit)
                                            <div class="limit-rule-card" data-keyword="{{ strtolower($keyword) }}" style="display: flex; align-items: center; gap: 10px; padding: 0.75rem 1rem; background: white; border: 1.5px solid #f1f5f9; border-radius: 16px; transition: 0.3s;">
                                                <div style="width: 34px; height: 34px; border-radius: 10px; background: linear-gradient(135deg,#6366f1,#4f46e5); color: white; font-weight: 900; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; text-transform: uppercase;">
                                                    <i data-lucide="ban" style="width: 14px;"></i>
                                                </div>
                                                <div style="flex: 1; min-width: 0;">
                                                    <div style="font-weight: 800; font-size: 0.82rem; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $keyword }}">{{ $keyword }}</div>
                                                    @php
                                                        $displayUnit = \App\Models\Setting::getItemUnit($keyword);
                                                        // Calculate given out qty
                                                        $query = \App\Models\StoreRequisitionItem::join('store_requisitions', 'store_requisition_items.requisition_id', '=', 'store_requisitions.id')
                                                            ->whereIn('store_requisitions.status', ['approved', 'partially_approved']);
                                                        $originalSum = (float) $query->clone()
                                                            ->where(\DB::raw('LOWER(store_requisition_items.description)'), 'LIKE', '%' . strtolower(trim($keyword)) . '%')
                                                            ->sum('store_requisition_items.quantity_approved');
                                                        $alternativeSum = (float) $query->clone()
                                                            ->where(\DB::raw('LOWER(store_requisition_items.alternative_description)'), 'LIKE', '%' . strtolower(trim($keyword)) . '%')
                                                            ->sum('store_requisition_items.alternative_quantity_approved');
                                                        $givenOut = $originalSum + $alternativeSum;
                                                    @endphp
                                                    <div style="font-size: 0.7rem; font-weight: 700; color: #64748b;">Limit: {{ $limit }} {{ $displayUnit }}</div>
                                                    <div style="font-size: 0.65rem; font-weight: 600; color: {{ $givenOut >= $limit ? 'var(--danger-color)' : 'var(--success-color)' }}; margin-top: 2px;">Given Out: {{ $givenOut }} / {{ $limit }}</div>
                                                </div>
                                                <div style="display: flex; gap: 4px;">
                                                    <button type="button" onclick="populateLimitForm('{{ $keyword }}', {{ $limit }}, '{{ $catCode }}')" style="background: none; border: none; color: #cbd5e1; cursor: pointer; transition: 0.2s; padding: 2px; display: flex; align-items: center;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#cbd5e1'" title="Edit Rule">
                                                        <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i>
                                                    </button>
                                                    <form action="{{ route('admin.settings.request-limit.destroy') }}" method="POST" onsubmit="return confirm('Remove request limit rule for \'{{ $keyword }}\'?');" style="margin: 0;">
                                                        @csrf @method('DELETE')
                                                        <input type="hidden" name="keyword" value="{{ $keyword }}">
                                                        <button type="submit" style="background: none; border: none; color: #cbd5e1; cursor: pointer; transition: 0.2s; padding: 2px; display: flex; align-items: center;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#cbd5e1'" title="Remove Limit Rule">
                                                            <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Add New Limit Rule --}}
                <div class="cat-form-card">
                    <h5 id="limitFormTitle">Add Request Limit</h5>
                    <p>Specify the keyword (e.g. "pen") and set the max allowed request limit. When this limit is reached, it will display as Out of Stock on the dashboard.</p>
                    <form action="{{ route('admin.settings.request-limit.store') }}" method="POST" id="limitForm">
                        @csrf
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Target Category</label>
                                <select name="category" id="limitCategory" class="cfg-text-input" required style="cursor: pointer;">
                                    <option value="">Select Category...</option>
                                    @foreach($categories ?? [] as $code => $name)
                                        <option value="{{ $code }}">[{{ $code }}] {{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Item Keyword</label>
                                <select name="keyword" id="limitKeyword" class="cfg-text-input select2-limit" required>
                                    <option value="">Select Category First...</option>
                                </select>
                            </div>

                            {{-- Stock info panel (shown after an item is selected) --}}
                            <div id="limitStockInfo" style="display:none; border-radius: 14px; padding: 0.85rem 1.1rem; background: #f8fafc; border: 1.5px solid #e2e8f0; transition: 0.3s;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div id="limitStockIcon" style="width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i data-lucide="package" style="width: 16px; height: 16px; color: white;"></i>
                                    </div>
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-size: 0.72rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Current Stock Balance</div>
                                        <div id="limitStockValue" style="font-size: 1rem; font-weight: 900; color: #0f172a; letter-spacing: -0.02em;">—</div>
                                    </div>
                                    <span id="limitStockBadge" style="font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.07em; padding: 4px 10px; border-radius: 30px;"></span>
                                </div>
                                <p id="limitStockHint" style="font-size: 0.72rem; font-weight: 600; color: #64748b; margin: 8px 0 0; line-height: 1.5;"></p>
                            </div>

                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Maximum Request Limit</label>
                                <input type="number" name="limit" id="limitVal" class="cfg-text-input" placeholder="e.g. 5" required min="0">
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" id="limitSubmitBtn" class="btn-cfg-add" style="flex: 1; background: linear-gradient(135deg,#6366f1,#4f46e5); box-shadow: 0 6px 16px rgba(99,102,241,0.25);">
                                    <i data-lucide="plus-circle" id="limitSubmitIcon"></i> <span id="limitSubmitText">Add Limit Rule</span>
                                </button>
                                <button type="button" id="limitResetBtn" onclick="resetLimitForm()" style="display: none; padding: 0.75rem 1rem; background: #f1f5f9; color: #64748b; border: none; border-radius: 14px; font-weight: 800; cursor: pointer; transition: 0.2s;">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    {{-- Suppliers Info --}}
    <div class="cfg-card" id="suppliers-registry" style="margin-top: 2rem;">
        <div class="cfg-card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div class="cfg-icon-box" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <i data-lucide="truck"></i>
                </div>
                <div>
                    <h3 style="margin: 0 0 0.25rem 0;">Suppliers Info</h3>
                    <p style="margin: 0;">Register pre-defined suppliers and their contact details for users to easily select.</p>
                </div>
            </div>
        </div>
        <div class="cfg-card-body">
            <div style="display: grid; grid-template-columns: 1fr 360px; gap: 2rem; align-items: start;">

                {{-- Existing Suppliers List --}}
                <div>
                    <p style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 1rem; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="list" style="width: 14px;"></i> Registered Suppliers
                    </p>
                    @php
                        $suppliersRegistry = \App\Models\Setting::get('suppliers_registry', []);
                    @endphp
                    @if(empty($suppliersRegistry))
                        <div style="padding: 2rem; text-align: center; background: #f8fafc; border-radius: 16px; border: 1.5px dashed #e2e8f0;">
                            <i data-lucide="inbox" style="width: 32px; height: 32px; color: #cbd5e1; margin-bottom: 0.75rem;"></i>
                            <p style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; margin: 0;">No suppliers registered yet. Register suppliers on the right.</p>
                        </div>
                    @else
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;" id="suppliersRegistryContainer">
                            @foreach($suppliersRegistry as $name => $details)
                                <div class="supplier-card" data-name="{{ strtolower($name) }}" style="padding: 1.25rem; background: white; border: 1.5px solid #f1f5f9; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); display: flex; flex-direction: column; gap: 0.75rem; position: relative;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem;">
                                        <div style="font-weight: 900; font-size: 0.95rem; color: #0f172a; line-height: 1.2;">{{ $name }}</div>
                                        <form action="{{ route('admin.settings.supplier.destroy') }}" method="POST" onsubmit="return confirm('Remove {{ $name }} from the registry?');" style="margin: 0;">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="name" value="{{ $name }}">
                                            <button type="submit" style="width: 28px; height: 28px; border-radius: 8px; background: #fff1f2; border: none; color: #f43f5e; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#ffe4e6'" onmouseout="this.style.background='#fff1f2'">
                                                <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 4px; font-size: 0.78rem; font-weight: 600; color: #475569;">
                                        @if(!empty($details['delivery_person']))
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <i data-lucide="user" style="width: 12px; color: #94a3b8;"></i> <strong>Delivery:</strong> {{ $details['delivery_person'] }}
                                            </div>
                                        @endif
                                        @if(!empty($details['phone']))
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <i data-lucide="phone" style="width: 12px; color: #94a3b8;"></i> <strong>Phone:</strong> {{ $details['phone'] }}
                                            </div>
                                        @endif
                                        @if(!empty($details['email']))
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <i data-lucide="mail" style="width: 12px; color: #94a3b8;"></i> <strong>Email:</strong> {{ $details['email'] }}
                                            </div>
                                        @endif
                                        @if(!empty($details['address']))
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <i data-lucide="map-pin" style="width: 12px; color: #94a3b8;"></i> <strong>Address:</strong> {{ $details['address'] }}
                                            </div>
                                        @endif
                                    </div>
                                    @if(!empty($details['desc']))
                                        <div style="font-size: 0.72rem; font-weight: 500; color: #64748b; background: #f8fafc; padding: 6px 10px; border-radius: 8px; line-height: 1.4;">
                                            {{ $details['desc'] }}
                                        </div>
                                    @endif

                                    {{-- Edit Button --}}
                                    <button type="button" onclick="populateSupplierForm('{{ addslashes($name) }}', '{{ addslashes($details['delivery_person'] ?? '') }}', '{{ addslashes($details['phone'] ?? '') }}', '{{ addslashes($details['email'] ?? '') }}', '{{ addslashes($details['address'] ?? '') }}', '{{ addslashes($details['desc'] ?? '') }}')" style="position: absolute; right: 45px; top: 1.25rem; width: 28px; height: 28px; border-radius: 8px; background: #f1f5f9; border: none; color: #64748b; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                                        <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Add/Edit Form --}}
                <div style="background: #fafbff; border-radius: 20px; border: 1.5px solid #edf2f7; padding: 1.5rem;">
                    <h5 style="font-weight: 900; font-size: 0.95rem; color: #0f172a; margin: 0 0 0.25rem 0;" id="supplierFormTitle">Add Supplier</h5>
                    <p style="font-size: 0.75rem; color: #64748b; margin: 0 0 1.25rem 0;">Register new supplier or update contact details.</p>
                    <form action="{{ route('admin.settings.supplier.store') }}" method="POST" id="supplierForm">
                        @csrf
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Supplier Name (Company)</label>
                                <input type="text" name="name" id="supplierNameInput" class="cfg-text-input" placeholder="e.g. Acme Corp" required>
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Delivery Person Name</label>
                                <input type="text" name="delivery_person" id="supplierDeliveryPersonInput" class="cfg-text-input" placeholder="e.g. John Doe">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Contact Phone Number</label>
                                <input type="text" name="phone" id="supplierPhoneInput" class="cfg-text-input" placeholder="e.g. +233 2400000">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Email Address</label>
                                <input type="email" name="email" id="supplierEmailInput" class="cfg-text-input" placeholder="e.g. orders@acme.com">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Physical Address</label>
                                <input type="text" name="address" id="supplierAddressInput" class="cfg-text-input" placeholder="e.g. Cantonments, Accra">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Description / Notes</label>
                                <textarea name="desc" id="supplierDescInput" class="cfg-text-input" placeholder="e.g. Lead distributor for stationery items" style="min-height: 80px; font-family: inherit; resize: vertical; padding: 0.75rem 1rem; border-radius: 12px;"></textarea>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" id="supplierSubmitBtn" class="btn-cfg-add" style="flex: 1; background: linear-gradient(135deg, #10b981, #059669); margin-top: 0.5rem;">
                                    <i data-lucide="plus-circle" id="supplierSubmitIcon"></i> <span id="supplierSubmitText">Register Supplier</span>
                                </button>
                                <button type="button" id="supplierResetBtn" onclick="resetSupplierForm()" style="display: none; padding: 0.75rem 1rem; background: #f1f5f9; color: #64748b; border: none; border-radius: 14px; font-weight: 800; cursor: pointer; transition: 0.2s; margin-top: 0.5rem;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    const itemsByCategory  = @json($itemsByCategory  ?? []);
    const stockByKeyword   = @json($stockByKeyword   ?? []);



    function filterRules() {
        const term = document.getElementById('ruleSearch').value.toLowerCase();
        document.querySelectorAll('.unit-rule-group').forEach(group => {
            let hasVisible = false;
            group.querySelectorAll('.unit-rule-card').forEach(card => {
                const keyword = card.getAttribute('data-keyword');
                if (keyword.includes(term)) {
                    card.style.display = '';
                    hasVisible = true;
                } else {
                    card.style.display = 'none';
                }
            });
            group.style.display = hasVisible ? '' : 'none';
        });
    }

    function filterThresholds() {
        const term = document.getElementById('thresholdSearch').value.toLowerCase();
        document.querySelectorAll('.threshold-rule-group').forEach(group => {
            let hasVisible = false;
            group.querySelectorAll('.threshold-rule-card').forEach(card => {
                const keyword = card.getAttribute('data-keyword');
                if (keyword.includes(term)) {
                    card.style.display = '';
                    hasVisible = true;
                } else {
                    card.style.display = 'none';
                }
            });
            group.style.display = hasVisible ? '' : 'none';
        });
    }

    function populateUnitForm(keyword, unit, category, location) {
        // Set category first to trigger the keyword dropdown update
        const catSelect = document.getElementById('unitCategory');
        catSelect.value = category;
        $(catSelect).trigger('change');

        // Set unit (wait for Select2)
        setTimeout(() => {
            const pkgSelect = $('.select2-package');
            if (pkgSelect.find("option[value='" + unit + "']").length) {
                pkgSelect.val(unit).trigger('change');
            } else {
                var newOption = new Option(unit, unit, true, true);
                pkgSelect.append(newOption).trigger('change');
            }
        }, 100);

        // Set location (wait for Select2)
        setTimeout(() => {
            const locSelect = $('#unitLocation');
            if (locSelect.find("option[value='" + location + "']").length) {
                locSelect.val(location).trigger('change');
            } else {
                var newOption = new Option(location, location, true, true);
                locSelect.append(newOption).trigger('change');
            }
        }, 100);

        // Set keyword (wait for Select2)
        setTimeout(() => {
            const keywordSelect = $('#unitKeyword');
            if (keywordSelect.find("option[value='" + keyword + "']").length) {
                keywordSelect.val(keyword).trigger('change');
            } else {
                var newOption = new Option(keyword, keyword, true, true);
                keywordSelect.append(newOption).trigger('change');
            }
        }, 100);

        // Update button UI
        document.getElementById('unitSubmitText').innerText = 'Update';
        document.getElementById('unitSubmitBtn').style.background = 'linear-gradient(135deg, #4f46e5, #3730a3)';
        document.getElementById('unitResetBtn').style.display = 'block';
        const icon = document.getElementById('unitSubmitIcon');
        icon.setAttribute('data-lucide', 'refresh-cw');
        if (window.lucide) lucide.createIcons();

        // Scroll to form
        document.getElementById('unit-rules').scrollIntoView({ behavior: 'smooth' });
    }

    function resetUnitForm() {
        document.querySelector('#unit-rules form').reset();
        $('#unitKeyword').val(null).trigger('change');
        $('.select2-package').val(null).trigger('change');
        $('#unitLocation').val(null).trigger('change');
        document.getElementById('unitSubmitText').innerText = 'Add Rule';
        document.getElementById('unitSubmitBtn').style.background = 'linear-gradient(135deg,#f59e0b,#d97706)';
        document.getElementById('unitResetBtn').style.display = 'none';
        const icon = document.getElementById('unitSubmitIcon');
        icon.setAttribute('data-lucide', 'plus-circle');
        if (window.lucide) lucide.createIcons();
    }

    function populateThresholdForm(keyword, threshold, category) {
        // Set category first to trigger the keyword dropdown update
        const catSelect = document.getElementById('thresholdCategory');
        catSelect.value = category;
        $(catSelect).trigger('change');

        // Set threshold
        document.getElementsByName('threshold')[0].value = threshold;

        // Set keyword (we need to wait for Select2 to be populated)
        setTimeout(() => {
            const keywordSelect = $('#thresholdKeyword');
            if (keywordSelect.find("option[value='" + keyword + "']").length) {
                keywordSelect.val(keyword).trigger('change');
            } else {
                // If tag is allowed, we can add it
                var newOption = new Option(keyword, keyword, true, true);
                keywordSelect.append(newOption).trigger('change');
            }
        }, 100);

        // Update button UI
        document.getElementById('thresholdSubmitText').innerText = 'Update';
        document.getElementById('thresholdSubmitBtn').style.background = 'linear-gradient(135deg, #4f46e5, #3730a3)';
        document.getElementById('thresholdResetBtn').style.display = 'block';
        const icon = document.getElementById('thresholdSubmitIcon');
        icon.setAttribute('data-lucide', 'refresh-cw');
        if (window.lucide) lucide.createIcons();

        // Scroll to form
        document.getElementById('threshold-rules').scrollIntoView({ behavior: 'smooth' });
    }

    function resetThresholdForm() {
        document.querySelector('#threshold-rules form').reset();
        $('#thresholdKeyword').val(null).trigger('change');
        document.getElementById('thresholdSubmitText').innerText = 'Add Threshold Rule';
        document.getElementById('thresholdSubmitBtn').style.background = 'linear-gradient(135deg,#ef4444,#dc2626)';
        document.getElementById('thresholdResetBtn').style.display = 'none';
        const icon = document.getElementById('thresholdSubmitIcon');
        icon.setAttribute('data-lucide', 'plus-circle');
        if (window.lucide) lucide.createIcons();
    }

    function populateLimitForm(keyword, limit, category) {
        document.getElementById('limitFormTitle').innerText = 'Update Request Limit';
        document.getElementById('limitCategory').value = category;
        $('#limitCategory').trigger('change.select2');
        
        setTimeout(() => {
            const keywordSelect = $('#limitKeyword');
            if (keywordSelect.find("option[value='" + keyword + "']").length === 0) {
                keywordSelect.append(new Option(keyword, keyword));
            }
            keywordSelect.val(keyword).trigger('change.select2');
            // Manually show the stock panel since we're not using select2:select here
            if (typeof showLimitStock === 'function') showLimitStock(keyword);
        }, 100);

        document.getElementById('limitVal').value = limit;
        document.getElementById('limitSubmitText').innerText = 'Update Limit Rule';
        document.getElementById('limitSubmitBtn').style.background = 'linear-gradient(135deg, #6366f1, #4f46e5)';
        document.getElementById('limitResetBtn').style.display = 'inline-block';
        const icon = document.getElementById('limitSubmitIcon');
        icon.setAttribute('data-lucide', 'refresh-cw');
        if (window.lucide) lucide.createIcons();
        document.getElementById('request-limits').scrollIntoView({ behavior: 'smooth' });
    }

    function resetLimitForm() {
        document.getElementById('limitFormTitle').innerText = 'Add Request Limit';
        document.getElementById('limitForm').reset();
        $('#limitKeyword').val(null).trigger('change.select2');
        document.getElementById('limitStockInfo').style.display = 'none';
        document.getElementById('limitSubmitText').innerText = 'Add Limit Rule';
        document.getElementById('limitSubmitBtn').style.background = 'linear-gradient(135deg,#6366f1,#4f46e5)';
        document.getElementById('limitResetBtn').style.display = 'none';
        const icon = document.getElementById('limitSubmitIcon');
        icon.setAttribute('data-lucide', 'plus-circle');
        if (window.lucide) lucide.createIcons();
    }

    function filterLimits() {
        const query = document.getElementById('limitSearch').value.toLowerCase().trim();
        document.querySelectorAll('#limitsContainer .limit-rule-group').forEach(group => {
            let visibleInGroup = 0;
            group.querySelectorAll('.limit-rule-card').forEach(card => {
                const keyword = card.getAttribute('data-keyword') || '';
                if (!query || keyword.includes(query)) {
                    card.style.display = 'flex';
                    visibleInGroup++;
                } else {
                    card.style.display = 'none';
                }
            });
            group.style.display = visibleInGroup > 0 ? 'block' : 'none';
        });
    }

    $(document).ready(function() {
        if (window.lucide) lucide.createIcons();

        // Initialize Select2 for units
        $('.select2-unit').select2({
            tags: true,
            placeholder: 'Select or type a new item...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#unit-rules')
        });

        // Initialize Select2 for location
        $('.select2-location').select2({
            tags: true,
            placeholder: 'Select or type a store location...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#unit-rules')
        });

        // Initialize Select2 for package type
        $('.select2-package').select2({
            tags: true,
            placeholder: 'Select or type a package type...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#unit-rules')
        });

        // Initialize Select2 for thresholds
        $('.select2-threshold').select2({
            tags: true,
            placeholder: 'Select or type a new item...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#threshold-rules')
        });

        // Handle category change to update item dropdown (Package Types)
        $('#unitCategory').on('change', function() {
            const cat = $(this).val();
            const keywordSelect = $('#unitKeyword');
            keywordSelect.empty().append('<option value="">Select Item...</option>');

            if (cat && itemsByCategory[cat]) {
                itemsByCategory[cat].forEach(item => {
                    keywordSelect.append(new Option(item, item));
                });
            } else if (!cat) {
                keywordSelect.append('<option value="">Select Category First...</option>');
            }

            keywordSelect.trigger('change');
        });

        // Handle category change to update item dropdown (Thresholds)
        $('#thresholdCategory').on('change', function() {
            const cat = $(this).val();
            const keywordSelect = $('#thresholdKeyword');
            keywordSelect.empty().append('<option value="">Select Item...</option>');

            if (cat && itemsByCategory[cat]) {
                itemsByCategory[cat].forEach(item => {
                    keywordSelect.append(new Option(item, item));
                });
            } else if (!cat) {
                keywordSelect.append('<option value="">Select Category First...</option>');
            }

            keywordSelect.trigger('change');
        });

        // Initialize Select2 for limits
        $('.select2-limit').select2({
            tags: true,
            placeholder: 'Select or type a new item...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#request-limits')
        });

        // Handle category change to update item dropdown (Limits)
        $('#limitCategory').on('change', function() {
            const cat = $(this).val();
            const keywordSelect = $('#limitKeyword');
            keywordSelect.empty().append('<option value="">Select Item...</option>');

            if (cat && itemsByCategory[cat]) {
                itemsByCategory[cat].forEach(item => {
                    keywordSelect.append(new Option(item, item));
                });
            } else if (!cat) {
                keywordSelect.append('<option value="">Select Category First...</option>');
            }

            // Re-trigger so Select2 re-renders; DON'T trigger stock lookup here
            keywordSelect.trigger('change.select2');

            // Hide the stock panel when category changes (selection reset)
            document.getElementById('limitStockInfo').style.display = 'none';
        });

        // Trigger stock display when admin actively SELECTS an item via Select2
        $('#limitKeyword').on('select2:select', function(e) {
            showLimitStock(e.params.data.id);
        });

        // Hide when cleared
        $('#limitKeyword').on('select2:clear select2:unselecting', function() {
            document.getElementById('limitStockInfo').style.display = 'none';
        });
    });

    // Global: show stock balance for a given keyword in the Add Request Limit form
    function showLimitStock(keyword) {
        keyword = (keyword || '').toLowerCase().trim();
        const infoPanel = document.getElementById('limitStockInfo');
        if (!keyword) { infoPanel.style.display = 'none'; return; }

        // Find best match: exact first, then substring
        let stock = null;
        if (stockByKeyword[keyword] !== undefined) {
            stock = stockByKeyword[keyword];
        } else {
            for (const [k, v] of Object.entries(stockByKeyword)) {
                if (k.includes(keyword) || keyword.includes(k)) { stock = v; break; }
            }
        }

        const iconEl  = document.getElementById('limitStockIcon');
        const valEl   = document.getElementById('limitStockValue');
        const badgeEl = document.getElementById('limitStockBadge');
        const hintEl  = document.getElementById('limitStockHint');

        if (stock === null) {
            iconEl.style.background  = '#e2e8f0';
            valEl.textContent        = 'No stock data found';
            badgeEl.textContent      = 'Unknown';
            badgeEl.style.background = '#f1f5f9';
            badgeEl.style.color      = '#64748b';
            hintEl.textContent       = 'No inventory records match this keyword. The limit will still be applied once set.';
        } else if (stock <= 0) {
            iconEl.style.background  = 'linear-gradient(135deg,#ef4444,#dc2626)';
            valEl.textContent        = '0 units';
            badgeEl.textContent      = 'Out of Stock';
            badgeEl.style.background = '#fee2e2';
            badgeEl.style.color      = '#dc2626';
            hintEl.textContent       = 'This item is currently out of stock. Setting a limit here will keep it as Unavailable on the dashboard.';
        } else if (stock <= 5) {
            iconEl.style.background  = 'linear-gradient(135deg,#f59e0b,#d97706)';
            valEl.textContent        = stock + ' units available';
            badgeEl.textContent      = 'Very Low';
            badgeEl.style.background = '#fef3c7';
            badgeEl.style.color      = '#d97706';
            hintEl.textContent       = 'Stock is critically low. Consider setting a conservative limit to preserve remaining supply.';
        } else if (stock <= 20) {
            iconEl.style.background  = 'linear-gradient(135deg,#eab308,#ca8a04)';
            valEl.textContent        = stock + ' units available';
            badgeEl.textContent      = 'Low';
            badgeEl.style.background = '#fefce8';
            badgeEl.style.color      = '#ca8a04';
            hintEl.textContent       = 'Stock is running low. Set a limit that reflects the quantity you wish to make available for requisitions.';
        } else {
            iconEl.style.background  = 'linear-gradient(135deg,#10b981,#059669)';
            valEl.textContent        = stock + ' units available';
            badgeEl.textContent      = 'In Stock';
            badgeEl.style.background = '#dcfce7';
            badgeEl.style.color      = '#16a34a';
            hintEl.textContent       = 'Sufficient stock available. Set your desired request limit and it will be enforced on the requisition dashboard.';
        }

        infoPanel.style.display = 'block';
        if (window.lucide) lucide.createIcons();
    }

    function setOtpExpiry(minutes, btn) {
        document.getElementById('setting_otp_expiry_minutes').value = minutes;
        document.querySelectorAll('.otp-preset-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        updateOtpPreview(minutes);
    }

    function updateOtpPreview(minutes) {
        minutes = parseInt(minutes) || 1;
        let label = minutes + ' minute' + (minutes === 1 ? '' : 's');
        if (minutes >= 60) {
            let hours = Math.floor(minutes / 60);
            let mins = minutes % 60;
            label = hours + ' hour' + (hours === 1 ? '' : 's');
            if (mins > 0) label += ' ' + mins + ' minute' + (mins === 1 ? '' : 's');
            if (hours === 24 && mins === 0) label += ' (1 day)';
            if (hours === 48 && mins === 0) label += ' (2 days)';
        }
        document.getElementById('otp-preview-text').innerHTML =
            'Set how long an admin-issued OTP remains valid. Currently set to expire <strong>' + label + '</strong> after it has been generated.';
        // Sync active preset button
        document.querySelectorAll('.otp-preset-btn').forEach(b => {
            b.classList.toggle('active', parseInt(b.textContent) === minutes || b.onclick?.toString().includes('(' + minutes + ','));
        });
    }

    function populateSupplierForm(name, delivery_person, phone, email, address, desc) {
        document.getElementById('supplierFormTitle').innerText = 'Update Supplier';
        document.getElementById('supplierNameInput').value = name;
        document.getElementById('supplierNameInput').readOnly = true;
        document.getElementById('supplierNameInput').style.background = '#f8fafc';

        document.getElementById('supplierDeliveryPersonInput').value = delivery_person;
        document.getElementById('supplierPhoneInput').value = phone;
        document.getElementById('supplierEmailInput').value = email;
        document.getElementById('supplierAddressInput').value = address;
        document.getElementById('supplierDescInput').value = desc;

        document.getElementById('supplierSubmitText').innerText = 'Update';
        document.getElementById('supplierSubmitBtn').style.background = 'linear-gradient(135deg, #4f46e5, #3730a3)';
        document.getElementById('supplierResetBtn').style.display = 'block';

        const icon = document.getElementById('supplierSubmitIcon');
        icon.setAttribute('data-lucide', 'refresh-cw');
        if (window.lucide) lucide.createIcons();

        document.getElementById('suppliers-registry').scrollIntoView({ behavior: 'smooth' });
    }

    function resetSupplierForm() {
        document.getElementById('supplierFormTitle').innerText = 'Add Supplier';
        document.getElementById('supplierForm').reset();
        document.getElementById('supplierNameInput').readOnly = false;
        document.getElementById('supplierNameInput').style.background = '';

        document.getElementById('supplierSubmitText').innerText = 'Register Supplier';
        document.getElementById('supplierSubmitBtn').style.background = 'linear-gradient(135deg, #10b981, #059669)';
        document.getElementById('supplierResetBtn').style.display = 'none';

        const icon = document.getElementById('supplierSubmitIcon');
        icon.setAttribute('data-lucide', 'plus-circle');
        if (window.lucide) lucide.createIcons();
    }
</script>
@endsection
