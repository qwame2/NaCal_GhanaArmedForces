@extends('layouts.admin')

@section('title', 'Register Users')

@section('content')
<style>
    /* General shell and container */
    .register-users-shell {
        animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(16px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Header styling with modern glow */
    .premium-page-header {
        background: linear-gradient(135deg, #ffffff, #fafbff);
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 1.5rem 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .premium-page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 6px;
        height: 100%;
        background: linear-gradient(to bottom, #16a34a, #4ade80);
    }

    /* Cards container */
    .user-row-card.premium-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .user-row-card.premium-card:hover {
        border-color: #c7d2fe;
        box-shadow: 0 20px 40px rgba(22, 163, 74, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02);
        transform: translateY(-2px);
    }

    /* Card Header */
    .card-header-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        border-bottom: 1.5px solid #f8fafc;
        padding-bottom: 1.25rem;
    }

    .user-card-title {
        font-weight: 900;
        font-size: 0.9rem;
        color: #16a34a;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .remove-user-row-btn {
        background: #fef2f2;
        border: 1px solid #fee2e2;
        border-radius: 12px;
        color: #ef4444;
        font-size: 0.78rem;
        font-weight: 800;
        padding: 0.5rem 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .remove-user-row-btn:hover {
        background: #fee2e2;
        border-color: #fecaca;
        color: #b91c1c;
        transform: translateY(-1px);
    }

    /* Grid layout inside card */
    .card-fields-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    @media (max-width: 768px) {
        .card-fields-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Modern input group design */
    .input-modern-group {
        display: flex;
        flex-direction: column;
    }

    .input-modern-group label {
        font-size: 0.75rem;
        font-weight: 850;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 8px;
        padding-left: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .required-star {
        color: #ef4444;
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        background: #f8fafc;
        transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
        overflow: hidden;
        width: 100%;
        height: 52px;
    }

    .input-wrapper:focus-within {
        background: white;
        border-color: #16a34a;
        box-shadow: 0 10px 25px rgba(22, 163, 74, 0.05), 0 0 0 4px rgba(22, 163, 74, 0.08);
        transform: translateY(-1px);
    }

    .icon-box {
        padding-left: 1.25rem;
        padding-right: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        height: 100%;
    }

    .icon-box::after {
        content: '';
        position: absolute;
        right: 0;
        height: 20px;
        width: 1px;
        background: #e2e8f0;
        transition: all 0.3s ease;
    }

    .input-wrapper:focus-within .icon-box::after {
        background: #16a34a;
        height: 26px;
    }

    .icon-box i,
    .icon-box svg {
        width: 18px;
        height: 18px;
        color: #94a3b8;
        transition: all 0.3s ease;
    }

    .input-wrapper:focus-within i,
    .input-wrapper:focus-within svg {
        color: #16a34a;
        transform: scale(1.05);
    }

    .premium-text-input {
        width: 100%;
        padding: 0 1.25rem;
        border: none !important;
        background: transparent !important;
        color: #0f172a;
        font-weight: 700;
        font-size: 0.92rem;
        outline: none !important;
        height: 100%;
        box-shadow: none !important;
    }

    .premium-text-input::placeholder {
        color: #94a3b8;
        opacity: 0.6;
        font-weight: 500;
    }

    .premium-select-input {
        width: 100% !important;
        height: 100% !important;
        border: none !important;
        background-color: transparent !important;
        font-size: 0.92rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        outline: none !important;
        padding: 0 45px 0 1.25rem !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        cursor: pointer !important;
        box-shadow: none !important;
        background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 18px center !important;
        background-size: 14px !important;
    }

    .input-wrapper:focus-within .premium-select-input {
        background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%234f46e5%22%20stroke-width%3D%222.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E") !important;
    }

    /* Security Key Card box styling */
    .security-key-card {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border: 1px dashed #cbd5e1;
        border-radius: 20px;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    @media (max-width: 600px) {
        .security-key-card {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }
    }

    .key-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .key-icon {
        width: 20px;
        height: 20px;
        color: #16a34a;
        background: rgba(22, 163, 74, 0.08);
        padding: 8px;
        border-radius: 12px;
        flex-shrink: 0;
    }

    .key-title {
        display: block;
        font-weight: 850;
        font-size: 0.85rem;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .key-desc {
        display: block;
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 2px;
    }

    .key-input-container {
        display: flex;
        align-items: center;
        background: white;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        height: 46px;
        width: 280px;
        max-width: 100%;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.01);
    }

    @media (max-width: 600px) {
        .key-input-container {
            width: 100%;
        }
    }

    .key-input {
        border: none;
        background: transparent;
        padding: 0 1rem;
        font-weight: 850;
        font-size: 0.95rem;
        color: #0f172a;
        width: 100%;
        outline: none;
        letter-spacing: 0.05em;
        text-align: center;
    }

    .key-actions {
        display: flex;
        height: 100%;
        border-left: 1.5px solid #e2e8f0;
    }

    .key-btn {
        background: transparent;
        border: none;
        width: 44px;
        height: 100%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        transition: all 0.2s;
    }

    .key-btn:hover {
        color: #16a34a;
        background: #f8fafc;
    }

    .key-btn:first-of-type {
        border-right: 1px solid #f1f5f9;
    }

    /* Add Account button style */
    .add-account-dashed-btn {
        width: 100%;
        background: white;
        border: 2px dashed #cbd5e1;
        border-radius: 20px;
        padding: 1.25rem;
        font-weight: 900;
        font-size: 0.95rem;
        color: #16a34a;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        margin-top: 1rem;
        margin-bottom: 4rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.01);
    }

    .add-account-dashed-btn:hover {
        border-color: #16a34a;
        background: #f5f6ff;
        color: #3730a3;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(22, 163, 74, 0.08);
    }

    .add-account-dashed-btn:active {
        transform: scale(0.99);
    }

    /* Sticky action bar at the bottom */
    .bottom-glass-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-top: 1px solid rgba(226, 232, 240, 0.8);
        padding: 1.25rem 2rem;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        z-index: 999;
        box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.03);
    }

    .bottom-bar-content {
        max-width: 900px;
        width: 100%;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
    }

    .counter-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #e0e7ff;
        color: #16a34a;
        font-size: 0.8rem;
        font-weight: 900;
        padding: 6px 16px;
        border-radius: 999px;
        border: 1px solid #c7d2fe;
    }

    .bar-buttons {
        display: flex;
        gap: 1rem;
    }

    .action-btn-abort {
        height: 50px;
        padding: 0 24px;
        border-radius: 14px;
        font-weight: 800;
        font-size: 0.9rem;
        color: #64748b;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.2s;
        cursor: pointer;
    }

    .action-btn-abort:hover {
        background: #e2e8f0;
        color: #334155;
        border-color: #cbd5e1;
    }

    .action-btn-submit {
        height: 50px;
        padding: 0 32px;
        border-radius: 14px;
        font-weight: 900;
        font-size: 0.9rem;
        color: white;
        background: linear-gradient(135deg, #16a34a, #3730a3);
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(22, 163, 74, 0.25);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .action-btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(22, 163, 74, 0.35);
    }

    .action-btn-submit:active {
        transform: scale(0.98);
    }

    /* Tooltip styles */
    .tooltip {
        position: relative;
    }

    .tooltip::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%) translateY(4px);
        background: #0f172a;
        color: white;
        font-size: 0.65rem;
        font-weight: 800;
        padding: 4px 8px;
        border-radius: 6px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        pointer-events: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .tooltip:hover::after {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }

    /* Slide in animation for new user cards */
    @keyframes cardFadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Select2 Premium Override */
    .select2-container--default .select2-selection--single {
        height: 52px !important;
        border: none !important;
        background: transparent !important;
        display: flex !important;
        align-items: center !important;
        padding-left: 0 !important;
        font-size: 0.92rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #0f172a !important;
        padding-left: 1.25rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 50px !important;
        right: 12px !important;
    }

    .select2-dropdown {
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: 0 20px 50px rgba(0,0,0,0.1) !important;
        overflow: hidden !important;
        background: white !important;
        padding: 6px !important;
        z-index: 9999999 !important;
    }

    .select2-search__field {
        border-radius: 10px !important;
        padding: 10px 14px !important;
        border: 1px solid #e2e8f0 !important;
        font-weight: 600 !important;
        outline: none !important;
    }

    .select2-results__option {
        padding: 10px 15px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        color: #334155 !important;
        margin-bottom: 2px !important;
    }

    .select2-results__option--highlighted[aria-selected] {
        background-color: #16a34a !important;
        color: white !important;
    }

    .select2-results__group {
        font-size: 0.65rem !important;
        font-weight: 900 !important;
        color: #64748b !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        padding: 12px 15px 6px !important;
        background: #f8fafc !important;
        border-radius: 8px !important;
        margin: 6px 0 2px 0 !important;
        display: block !important;
    }
</style>

<div class="register-users-shell">
    <div class="premium-page-header">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="{{ route('admin.index') }}" class="btn-tool" style="width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: white; border: 1px solid #e2e8f0; color: #64748b; cursor: pointer; transition: all 0.3s; text-decoration: none;" onmouseover="this.style.borderColor='#cbd5e1'; this.style.color='#0f172a'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b'">
                <i data-lucide="arrow-left" style="width: 20px; height: 20px;"></i>
            </a>
            <div>
                <h3 style="margin: 0; font-size: 1.5rem; font-weight: 950; color: #0f172a; display: flex; align-items: center; gap: 10px; letter-spacing: -0.02em;">
                    Register Users
                </h3>
                <p style="margin: 4px 0 0; font-size: 0.85rem; color: #64748b; font-weight: 600;">
                    Configure and deploy multiple personnel credentials simultaneously
                </p>
            </div>
        </div>
    </div>

    <div style="max-width: 900px; margin: 0 auto; padding-bottom: 6rem;">
        @if ($errors->any())
            <div style="background: #fef2f2; border: 1.5px solid #fee2e2; border-radius: 20px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.03);">
                <div style="display: flex; gap: 10px; align-items: center; color: #dc2626; font-weight: 850; font-size: 0.9rem; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                    <i data-lucide="shield-alert" style="width: 20px; height: 20px;"></i>
                    Registry Validation Alert
                </div>
                <ul style="margin: 0; padding-left: 1.5rem; color: #b91c1c; font-size: 0.85rem; font-weight: 600; line-height: 1.7;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="addPersonnelForm" action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            
            <div id="users-cards-container">
                @php
                    $oldUsers = old('users', [[]]);
                @endphp
                
                @foreach ($oldUsers as $index => $oldUser)
                    @php
                        $randomPass = old("users.{$index}.password", 'Auth' . rand(1000, 9999));
                        $selectedRole = old("users.{$index}.role", 'Department Head');
                        $selectedDept = old("users.{$index}.department", '');
                        $selectedRank = old("users.{$index}.rank", '');
                        $isCustomDept = false;
                        
                        $standardDepts = [
                            'Intelligence Department', 'Investigations Department', 'Forensic Science Department',
                            'Asset recovery & Management Department', 'Strategic Intelligence Oversight Department',
                            'Cannabis Regulations Department', 'Precursor Diversion Department',
                            'Drug Education & Prevention Department', 'Rehabilitation & Social Re-integration Department',
                            'Harm Reduction Department', 'Alternative Livelihoods Development Department',
                            'Canine Operations Department', 'Accounts & Budget Department', 'Payroll & Pension Department',
                            'Research Policy Planning Monitoring & Evaluation Department', 'Professional Standards Department',
                            'General Services Department', 'ICT Department', 'Transport Department', 'Procurement Department',
                            'Project Management Department', 'Human Resource Management Department', 'Welfare Department',
                            'Religious Affairs Department', 'Internal & External Training Department', 'Public Affairs Department',
                            'International Relations Department', 'Material Development Department', 'Client Service Department',
                            'Stores', 'Store', 'Internal Audit', 'Audit Department'
                        ];
                        
                        if (!empty($selectedDept) && !in_array($selectedDept, $standardDepts)) {
                            $isCustomDept = true;
                        }
                    @endphp
                    <div class="user-row-card premium-card" id="user-row-{{ $index }}" data-index="{{ $index }}">
                        <div class="card-header-inner">
                            <span class="user-card-title">
                                <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i>
                                User #{{ $index + 1 }}
                            </span>
                            <button type="button" class="remove-user-row-btn" onclick="removeUserRow({{ $index }})" style="display: {{ count($oldUsers) > 1 ? 'flex' : 'none' }};">
                                <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Remove
                            </button>
                        </div>
                        
                        <div class="card-fields-grid">
                            <div class="input-modern-group">
                                <label>Full Name</label>
                                <div class="input-wrapper">
                                    <div class="icon-box"><i data-lucide="user"></i></div>
                                    <input type="text" name="users[{{ $index }}][name]" value="{{ old("users.{$index}.name") }}" placeholder="e.g. John Doe" class="premium-text-input">
                                </div>
                            </div>
                            
                            <div class="input-modern-group">
                                <label>Username <span class="required-star">*</span></label>
                                <div class="input-wrapper">
                                    <div class="icon-box"><i data-lucide="at-sign"></i></div>
                                    <input type="text" name="users[{{ $index }}][username]" value="{{ old("users.{$index}.username") }}" placeholder="e.g. j_doe" class="premium-text-input" required autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="card-fields-grid">
                            <div class="input-modern-group">
                                <label>Role <span class="required-star">*</span></label>
                                <div class="input-wrapper">
                                    <div class="icon-box"><i data-lucide="shield-check"></i></div>
                                    <select name="users[{{ $index }}][role]" id="role-select-{{ $index }}" class="premium-select-input" required onchange="handleRowRoleChange({{ $index }})">
                                        <option value="Department Head" {{ $selectedRole === 'Department Head' ? 'selected' : '' }}>Dept. Head</option>
                                        <option value="Main Admin" {{ $selectedRole === 'Main Admin' ? 'selected' : '' }}>Dept. Head (Stores)</option>
                                        <option value="Dept Head HR" {{ $selectedRole === 'Dept Head HR' ? 'selected' : '' }}>Dept. Head (HR)</option>
                                        <option value="Head of Welfare" {{ $selectedRole === 'Head of Welfare' ? 'selected' : '' }}>Dept. Head (Welfare)</option>
                                        <option value="Auditor" {{ $selectedRole === 'Auditor' ? 'selected' : '' }}>Auditor</option>
                                        <option value="Officer" {{ $selectedRole === 'Officer' ? 'selected' : '' }}>Store Officer</option>
                                        <option value="Requisitioner" {{ $selectedRole === 'Requisitioner' ? 'selected' : '' }}>Requisitioner</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="input-modern-group" id="department-group-{{ $index }}" style="display: {{ in_array($selectedRole, ['Department Head', 'Requisitioner']) ? 'block' : 'none' }};">
                                <label>Department <span class="required-star">*</span></label>
                                <div class="input-wrapper">
                                    <div class="icon-box"><i data-lucide="network"></i></div>
                                    <select id="department-select-{{ $index }}" class="premium-select-input dept-dropdown" style="width: 100%;">
                                        <option value="">-- Select Department --</option>
                                        <optgroup label="INVESTIGATIONS & INTELLIGENCE DIRECTORATE">
                                            <option value="Intelligence Department" {{ $selectedDept === 'Intelligence Department' ? 'selected' : '' }}>Intelligence Department</option>
                                            <option value="Investigations Department" {{ $selectedDept === 'Investigations Department' ? 'selected' : '' }}>Investigations Department</option>
                                            <option value="Forensic Science Department" {{ $selectedDept === 'Forensic Science Department' ? 'selected' : '' }}>Forensic Science Department</option>
                                            <option value="Asset recovery & Management Department" {{ $selectedDept === 'Asset recovery & Management Department' ? 'selected' : '' }}>Asset recovery & Management Department</option>
                                            <option value="Strategic Intelligence Oversight Department" {{ $selectedDept === 'Strategic Intelligence Oversight Department' ? 'selected' : '' }}>Strategic Intelligence Oversight Department</option>
                                        </optgroup>
                                        <optgroup label="LICENSING & REGULATORY DIRECTORATE">
                                            <option value="Cannabis Regulations Department" {{ $selectedDept === 'Cannabis Regulations Department' ? 'selected' : '' }}>Cannabis Regulations Department</option>
                                            <option value="Precursor Diversion Department" {{ $selectedDept === 'Precursor Diversion Department' ? 'selected' : '' }}>Precursor Diversion Department</option>
                                        </optgroup>
                                        <optgroup label="DRUG DEMAND REDUCTION DIRECTORATE">
                                            <option value="Drug Education & Prevention Department" {{ $selectedDept === 'Drug Education & Prevention Department' ? 'selected' : '' }}>Drug Education & Prevention Department</option>
                                            <option value="Rehabilitation & Social Re-integration Department" {{ $selectedDept === 'Rehabilitation & Social Re-integration Department' ? 'selected' : '' }}>Rehabilitation & Social Re-integration Department</option>
                                            <option value="Harm Reduction Department" {{ $selectedDept === 'Harm Reduction Department' ? 'selected' : '' }}>Harm Reduction Department</option>
                                            <option value="Alternative Livelihoods Development Department" {{ $selectedDept === 'Alternative Livelihoods Development Department' ? 'selected' : '' }}>Alternative Livelihoods Development Department</option>
                                        </optgroup>
                                        <optgroup label="OPERATIONS AND ENFORCEMENT DIRECTORATE">
                                            <option value="Canine Operations Department" {{ $selectedDept === 'Canine Operations Department' ? 'selected' : '' }}>Canine Operations Department</option>
                                        </optgroup>
                                        <optgroup label="FINANCE DIRECTORATE">
                                            <option value="Accounts & Budget Department" {{ $selectedDept === 'Accounts & Budget Department' ? 'selected' : '' }}>Accounts & Budget Department</option>
                                            <option value="Payroll & Pension Department" {{ $selectedDept === 'Payroll & Pension Department' ? 'selected' : '' }}>Payroll & Pension Department</option>
                                        </optgroup>
                                        <optgroup label="RESEARCH POLICY & PLANNING DIRECTORATE">
                                            <option value="Research Policy Planning Monitoring & Evaluation Department" {{ $selectedDept === 'Research Policy Planning Monitoring & Evaluation Department' ? 'selected' : '' }}>Research Policy Planning Monitoring & Evaluation Department</option>
                                            <option value="Professional Standards Department" {{ $selectedDept === 'Professional Standards Department' ? 'selected' : '' }}>Professional Standards Department</option>
                                        </optgroup>
                                        <optgroup label="ADMINISTRATION DIRECTORATE">
                                            <option value="General Services Department" {{ $selectedDept === 'General Services Department' ? 'selected' : '' }}>General Services Department</option>
                                            <option value="ICT Department" {{ $selectedDept === 'ICT Department' ? 'selected' : '' }}>ICT Department</option>
                                            <option value="Transport Department" {{ $selectedDept === 'Transport Department' ? 'selected' : '' }}>Transport Department</option>
                                            <option value="Procurement Department" {{ $selectedDept === 'Procurement Department' ? 'selected' : '' }}>Procurement Department</option>
                                            <option value="Project Management Department" {{ $selectedDept === 'Project Management Department' ? 'selected' : '' }}>Project Management Department</option>
                                        </optgroup>
                                        <optgroup label="HUMAN RESOURCE DIRECTORATE">
                                            <option value="Human Resource Management Department" {{ $selectedDept === 'Human Resource Management Department' ? 'selected' : '' }}>Human Resource Management Department</option>
                                            <option value="Welfare Department" {{ $selectedDept === 'Welfare Department' ? 'selected' : '' }}>Welfare Department</option>
                                            <option value="Religious Affairs Department" {{ $selectedDept === 'Religious Affairs Department' ? 'selected' : '' }}>Religious Affairs Department</option>
                                        </optgroup>
                                        <optgroup label="TRAINING & DEVELOPMENT DIRECTORATE">
                                            <option value="Internal & External Training Department" {{ $selectedDept === 'Internal & External Training Department' ? 'selected' : '' }}>Internal & External Training Department</option>
                                        </optgroup>
                                        <optgroup label="PUBLIC AFFAIRS AND INTERNATIONAL RELATIONS">
                                            <option value="Public Affairs Department" {{ $selectedDept === 'Public Affairs Department' ? 'selected' : '' }}>Public Affairs Department</option>
                                            <option value="International Relations Department" {{ $selectedDept === 'International Relations Department' ? 'selected' : '' }}>International Relations Department</option>
                                            <option value="Material Development Department" {{ $selectedDept === 'Material Development Department' ? 'selected' : '' }}>Material Development Department</option>
                                            <option value="Client Service Department" {{ $selectedDept === 'Client Service Department' ? 'selected' : '' }}>Client Service Department</option>
                                        </optgroup>
                                        <optgroup label="AUDIT DEPARTMENT">
                                            <option value="Audit Department" {{ $selectedDept === 'Audit Department' ? 'selected' : '' }}>Audit Department</option>
                                        </optgroup>
                                        <option value="custom" {{ $isCustomDept ? 'selected' : '' }}>-- Custom / Other Department --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card-fields-grid">
                            <div class="input-modern-group" id="rank-group-{{ $index }}" style="display: {{ in_array($selectedRole, ['Dept Head HR', 'Head of Welfare', 'Main Admin']) ? 'block' : 'none' }};">
                                <label>Rank <span class="required-star">*</span></label>
                                <div class="input-wrapper">
                                    <div class="icon-box"><i data-lucide="award"></i></div>
                                    <select name="users[{{ $index }}][rank]" id="rank-select-{{ $index }}" class="premium-select-input">
                                        <option value="">-- Select Rank --</option>
                                        <option value="SNCO" {{ $selectedRank === 'SNCO' ? 'selected' : '' }}>SNCO</option>
                                        <option value="NCO" {{ $selectedRank === 'NCO' ? 'selected' : '' }}>NCO</option>
                                    </select>
                                </div>
                            </div>

                            <div class="input-modern-group" id="custom-dept-group-{{ $index }}" style="display: {{ $isCustomDept ? 'block' : 'none' }};">
                                <label>Custom Department Name <span class="required-star">*</span></label>
                                <div class="input-wrapper">
                                    <div class="icon-box"><i data-lucide="edit-3"></i></div>
                                    <input type="text" id="custom-dept-input-{{ $index }}" class="premium-text-input" value="{{ $isCustomDept ? $selectedDept : '' }}" placeholder="e.g. Signals, Engineering">
                                </div>
                            </div>
                        </div>

                        <div class="security-key-card" style="margin-top: 1.5rem;">
                            <div class="key-info">
                                <div class="key-icon"><i data-lucide="lock" style="width: 100%; height: 100%;"></i></div>
                                <div>
                                    <span class="key-title">Security Access Key</span>
                                    <span class="key-desc">Temporary password generated for initial authentication.</span>
                                </div>
                            </div>
                            <div class="key-input-container">
                                <input type="password" name="users[{{ $index }}][password]" id="password-{{ $index }}" value="{{ $randomPass }}" class="key-input" readonly>
                                <div class="key-actions">
                                    <button type="button" class="key-btn tooltip" onclick="copyRowPassword({{ $index }})" data-tooltip="Copy Key">
                                        <i data-lucide="copy" id="copy-icon-{{ $index }}" style="width: 18px; height: 18px;"></i>
                                    </button>
                                    <button type="button" class="key-btn tooltip" onclick="toggleRowPassword({{ $index }})" data-tooltip="Show Key">
                                        <i data-lucide="eye" id="password-icon-{{ $index }}" style="width: 18px; height: 18px;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="hidden-inputs-{{ $index }}"></div>
                    </div>
                @endforeach
            </div>
            
            <button type="button" id="add-user-row-btn" class="add-account-dashed-btn">
                <i data-lucide="plus-circle" style="width: 20px; height: 20px;"></i>
                Add Another Account Form
            </button>

            <div class="bottom-glass-bar">
                <div class="bottom-bar-content">
                    <span class="counter-badge-pill" id="users-counter-label">
                        <i data-lucide="users" style="width: 16px; height: 16px;"></i>
                        Total Users: {{ count($oldUsers) }}
                    </span>
                    <div class="bar-buttons">
                        <a href="{{ route('admin.index') }}" class="action-btn-abort">Abort</a>
                        <button type="submit" class="action-btn-submit">
                            <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                            Register All Accounts
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.userRowIndex = {{ count($oldUsers) - 1 }};

    function generateUserRowHtml(index) {
        const randomPass = 'Auth' + Math.floor(1000 + Math.random() * 9000);
        return `
            <div class="user-row-card premium-card" id="user-row-${index}" data-index="${index}" style="animation: cardFadeIn 0.3s ease-out;">
                <div class="card-header-inner">
                    <span class="user-card-title">
                        <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i>
                        User #${index + 1}
                    </span>
                    <button type="button" class="remove-user-row-btn" onclick="removeUserRow(${index})">
                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Remove
                    </button>
                </div>
                
                <div class="card-fields-grid">
                    <div class="input-modern-group">
                        <label>Full Name</label>
                        <div class="input-wrapper">
                            <div class="icon-box"><i data-lucide="user"></i></div>
                            <input type="text" name="users[${index}][name]" class="premium-text-input" placeholder="e.g. John Doe">
                        </div>
                    </div>
                    <div class="input-modern-group">
                        <label>Username <span class="required-star">*</span></label>
                        <div class="input-wrapper">
                            <div class="icon-box"><i data-lucide="at-sign"></i></div>
                            <input type="text" name="users[${index}][username]" class="premium-text-input" placeholder="e.g. j_doe" required autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="card-fields-grid">
                    <div class="input-modern-group">
                        <label>Role <span class="required-star">*</span></label>
                        <div class="input-wrapper">
                            <div class="icon-box"><i data-lucide="shield-check"></i></div>
                            <select name="users[${index}][role]" id="role-select-${index}" class="premium-select-input" required onchange="handleRowRoleChange(${index})">
                                <option value="Department Head">Dept. Head</option>
                                <option value="Main Admin">Dept. Head (Stores)</option>
                                <option value="Dept Head HR">Dept. Head (HR)</option>
                                <option value="Head of Welfare">Dept. Head (Welfare)</option>
                                <option value="Auditor">Auditor</option>
                                <option value="Officer">Store Officer</option>
                                <option value="Requisitioner">Requisitioner</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="input-modern-group" id="department-group-${index}">
                        <label>Department <span class="required-star">*</span></label>
                        <div class="input-wrapper">
                            <div class="icon-box"><i data-lucide="network"></i></div>
                            <select id="department-select-${index}" class="premium-select-input dept-dropdown" style="width: 100%;">
                                <option value="">-- Select Department --</option>
                                <optgroup label="INVESTIGATIONS & INTELLIGENCE DIRECTORATE">
                                    <option value="Intelligence Department">Intelligence Department</option>
                                    <option value="Investigations Department">Investigations Department</option>
                                    <option value="Forensic Science Department">Forensic Science Department</option>
                                    <option value="Asset recovery & Management Department">Asset recovery & Management Department</option>
                                    <option value="Strategic Intelligence Oversight Department">Strategic Intelligence Oversight Department</option>
                                </optgroup>
                                <optgroup label="LICENSING & REGULATORY DIRECTORATE">
                                    <option value="Cannabis Regulations Department">Cannabis Regulations Department</option>
                                    <option value="Precursor Diversion Department">Precursor Diversion Department</option>
                                </optgroup>
                                <optgroup label="DRUG DEMAND REDUCTION DIRECTORATE">
                                    <option value="Drug Education & Prevention Department">Drug Education & Prevention Department</option>
                                    <option value="Rehabilitation & Social Re-integration Department">Rehabilitation & Social Re-integration Department</option>
                                    <option value="Harm Reduction Department">Harm Reduction Department</option>
                                    <option value="Alternative Livelihoods Development Department">Alternative Livelihoods Development Department</option>
                                </optgroup>
                                <optgroup label="OPERATIONS AND ENFORCEMENT DIRECTORATE">
                                    <option value="Canine Operations Department">Canine Operations Department</option>
                                </optgroup>
                                <optgroup label="FINANCE DIRECTORATE">
                                    <option value="Accounts & Budget Department">Accounts & Budget Department</option>
                                    <option value="Payroll & Pension Department">Payroll & Pension Department</option>
                                </optgroup>
                                <optgroup label="RESEARCH POLICY & PLANNING DIRECTORATE">
                                    <option value="Research Policy Planning Monitoring & Evaluation Department">Research Policy Planning Monitoring & Evaluation Department</option>
                                    <option value="Professional Standards Department">Professional Standards Department</option>
                                </optgroup>
                                <optgroup label="ADMINISTRATION DIRECTORATE">
                                    <option value="General Services Department">General Services Department</option>
                                    <option value="ICT Department">ICT Department</option>
                                    <option value="Transport Department">Transport Department</option>
                                    <option value="Procurement Department">Procurement Department</option>
                                    <option value="Project Management Department">Project Management Department</option>
                                </optgroup>
                                <optgroup label="HUMAN RESOURCE DIRECTORATE">
                                    <option value="Human Resource Management Department">Human Resource Management Department</option>
                                    <option value="Welfare Department">Welfare Department</option>
                                    <option value="Religious Affairs Department">Religious Affairs Department</option>
                                </optgroup>
                                <optgroup label="TRAINING & DEVELOPMENT DIRECTORATE">
                                    <option value="Internal & External Training Department">Internal & External Training Department</option>
                                </optgroup>
                                <optgroup label="PUBLIC AFFAIRS AND INTERNATIONAL RELATIONS">
                                    <option value="Public Affairs Department">Public Affairs Department</option>
                                    <option value="International Relations Department">International Relations Department</option>
                                    <option value="Material Development Department">Material Development Department</option>
                                    <option value="Client Service Department">Client Service Department</option>
                                </optgroup>
                                <optgroup label="AUDIT DEPARTMENT">
                                    <option value="Audit Department">Audit Department</option>
                                </optgroup>
                                <option value="custom">-- Custom / Other Department --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-fields-grid">
                    <div class="input-modern-group" id="rank-group-${index}" style="display: none;">
                        <label>Rank <span class="required-star">*</span></label>
                        <div class="input-wrapper">
                            <div class="icon-box"><i data-lucide="award"></i></div>
                            <select name="users[${index}][rank]" id="rank-select-${index}" class="premium-select-input">
                                <option value="">-- Select Rank --</option>
                                <option value="SNCO">SNCO</option>
                                <option value="NCO">NCO</option>
                            </select>
                        </div>
                    </div>

                    <div class="input-modern-group" id="custom-dept-group-${index}" style="display: none;">
                        <label>Custom Department Name <span class="required-star">*</span></label>
                        <div class="input-wrapper">
                            <div class="icon-box"><i data-lucide="edit-3"></i></div>
                            <input type="text" id="custom-dept-input-${index}" class="premium-text-input" placeholder="e.g. Signals, Engineering">
                        </div>
                    </div>
                </div>

                <div class="security-key-card" style="margin-top: 1.5rem;">
                    <div class="key-info">
                        <div class="key-icon"><i data-lucide="lock" style="width: 100%; height: 100%;"></i></div>
                        <div>
                            <span class="key-title">Security Access Key</span>
                            <span class="key-desc">Temporary password generated for initial authentication.</span>
                        </div>
                    </div>
                    <div class="key-input-container">
                        <input type="password" name="users[${index}][password]" id="password-${index}" value="${randomPass}" class="key-input" readonly>
                        <div class="key-actions">
                            <button type="button" class="key-btn tooltip" onclick="copyRowPassword(${index})" data-tooltip="Copy Key">
                                <i data-lucide="copy" id="copy-icon-${index}" style="width: 18px; height: 18px;"></i>
                            </button>
                            <button type="button" class="key-btn tooltip" onclick="toggleRowPassword(${index})" data-tooltip="Show Key">
                                <i data-lucide="eye" id="password-icon-${index}" style="width: 18px; height: 18px;"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs placeholder for specific role-based departments -->
                <div id="hidden-inputs-${index}"></div>
            </div>
        `;
    }

    function initRow(index) {
        const selectEl = $(`#department-select-${index}`);
        if (selectEl.length) {
            selectEl.select2({
                width: '100%',
                placeholder: '-- Select Department --',
                allowClear: true
            }).on('change', function() {
                handleRowDeptSelectChange(this, index);
            });
        }
        handleRowRoleChange(index);
    }

    function handleRowRoleChange(index) {
        const roleSelect = document.getElementById(`role-select-${index}`);
        if (!roleSelect) return;
        const role = roleSelect.value;
        
        const deptGroup = document.getElementById(`department-group-${index}`);
        const deptSelect = document.getElementById(`department-select-${index}`);
        const customGroup = document.getElementById(`custom-dept-group-${index}`);
        const rankGroup = document.getElementById(`rank-group-${index}`);
        const rankSelect = document.getElementById(`rank-select-${index}`);
        const hiddenInputs = document.getElementById(`hidden-inputs-${index}`);

        if (hiddenInputs) hiddenInputs.innerHTML = '';

        // Rank field conditional visibility & validation rules
        if (role === 'Dept Head HR' || role === 'Head of Welfare' || role === 'Main Admin') {
            if (rankGroup) rankGroup.style.display = 'block';
            if (rankSelect) rankSelect.required = true;
        } else {
            if (rankGroup) rankGroup.style.display = 'none';
            if (rankSelect) {
                rankSelect.required = false;
                rankSelect.value = '';
            }
        }

        // Department field conditional visibility & validation rules
        if (role === 'Department Head' || role === 'Requisitioner') {
            if (deptGroup) deptGroup.style.display = 'block';
            if (deptSelect) {
                if (deptSelect.value === 'custom') {
                    if (deptSelect.hasAttribute('name')) deptSelect.removeAttribute('name');
                    const customInput = document.getElementById(`custom-dept-input-${index}`);
                    if (customInput) customInput.setAttribute('name', `users[${index}][department]`);
                } else {
                    deptSelect.setAttribute('name', `users[${index}][department]`);
                    const customInput = document.getElementById(`custom-dept-input-${index}`);
                    if (customInput) customInput.removeAttribute('name');
                }
            }
        } else {
            if (deptGroup) deptGroup.style.display = 'none';
            if (customGroup) customGroup.style.display = 'none';
            if (deptSelect) {
                if (deptSelect.data && deptSelect.data('select2')) {
                    $(deptSelect).val('').trigger('change.select2');
                } else {
                    deptSelect.value = '';
                }
                deptSelect.removeAttribute('name');
            }
            const customInput = document.getElementById(`custom-dept-input-${index}`);
            if (customInput) customInput.removeAttribute('name');
            
            let deptVal = '';
            if (role === 'Main Admin') {
                deptVal = 'Stores';
            } else if (role === 'Dept Head HR') {
                deptVal = 'Human Resource Management Department';
            } else if (role === 'Head of Welfare') {
                deptVal = 'Welfare Department';
            } else if (role === 'Auditor') {
                deptVal = 'Internal Audit';
            } else if (role === 'Officer') {
                deptVal = 'Stores';
            }

            if (deptVal && hiddenInputs) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = `users[${index}][department]`;
                hidden.value = deptVal;
                hiddenInputs.appendChild(hidden);
            }
        }
    }

    function handleRowDeptSelectChange(select, index) {
        const customGroup = document.getElementById(`custom-dept-group-${index}`);
        const customInput = document.getElementById(`custom-dept-input-${index}`);
        if (select.value === 'custom') {
            if (customGroup) customGroup.style.display = 'block';
            if (customInput) {
                customInput.required = true;
                customInput.setAttribute('name', `users[${index}][department]`);
            }
            select.removeAttribute('name');
        } else {
            if (customGroup) customGroup.style.display = 'none';
            if (customInput) {
                customInput.required = false;
                customInput.value = '';
                customInput.removeAttribute('name');
            }
            select.setAttribute('name', `users[${index}][department]`);
        }
    }

    function toggleRowPassword(index) {
        const input = document.getElementById(`password-${index}`);
        const icon = document.getElementById(`password-icon-${index}`);
        if (!input || !icon) return;
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        if (window.lucide) lucide.createIcons();
    }

    function copyRowPassword(index) {
        const input = document.getElementById(`password-${index}`);
        if (!input) return;
        
        navigator.clipboard.writeText(input.value).then(() => {
            const icon = document.getElementById(`copy-icon-${index}`);
            if (icon) {
                icon.setAttribute('data-lucide', 'check');
                if (window.lucide) lucide.createIcons();
                setTimeout(() => {
                    icon.setAttribute('data-lucide', 'copy');
                    if (window.lucide) lucide.createIcons();
                }, 1500);
            }
            
            // Show toast using Swal
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Access Key copied to clipboard!',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true
            });
        }).catch(err => {
            console.error('Could not copy text: ', err);
        });
    }

    function removeUserRow(index) {
        const cards = document.querySelectorAll('.user-row-card');
        if (cards.length <= 1) {
            Swal.fire({
                icon: 'error',
                title: 'Operation Denied',
                text: 'At least one user must be registered.',
                confirmButtonColor: '#16a34a'
            });
            return;
        }

        const rowEl = document.getElementById(`user-row-${index}`);
        if (rowEl) {
            const selectEl = $(`#department-select-${index}`);
            if (selectEl.length && selectEl.data('select2')) {
                selectEl.select2('destroy');
            }
            rowEl.style.animation = 'cardFadeOut 0.25s ease-in forwards';
            setTimeout(() => {
                rowEl.remove();
                updateCardSequentialTitles();
                updateRemoveButtons();
                updateCounterLabel();
            }, 250);
        }
    }

    function updateCardSequentialTitles() {
        const cards = document.querySelectorAll('.user-row-card');
        cards.forEach((card, idx) => {
            const titleSpan = card.querySelector('.user-card-title');
            if (titleSpan) {
                titleSpan.innerHTML = `<i data-lucide="user-plus" style="width: 18px; height: 18px;"></i> User #${idx + 1}`;
            }
        });
        if (window.lucide) lucide.createIcons();
    }

    function updateRemoveButtons() {
        const cards = document.querySelectorAll('.user-row-card');
        cards.forEach((card) => {
            const btn = card.querySelector('.remove-user-row-btn');
            if (btn) {
                btn.style.display = cards.length > 1 ? 'flex' : 'none';
            }
        });
    }

    function updateCounterLabel() {
        const cards = document.querySelectorAll('.user-row-card');
        const counterLabel = document.getElementById('users-counter-label');
        if (counterLabel) {
            counterLabel.innerHTML = `<i data-lucide="users" style="width: 16px; height: 16px;"></i> Total Users: ${cards.length}`;
        }
    }

    document.getElementById('add-user-row-btn').addEventListener('click', function() {
        window.userRowIndex++;
        const container = document.getElementById('users-cards-container');
        if (container) {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = generateUserRowHtml(window.userRowIndex);
            const rowNode = tempDiv.firstElementChild;
            container.appendChild(rowNode);

            initRow(window.userRowIndex);
            updateRemoveButtons();
            updateCardSequentialTitles();
            updateCounterLabel();
            
            if (window.lucide) lucide.createIcons();
        }
    });

    // Form submit validation
    document.getElementById('addPersonnelForm').addEventListener('submit', function(e) {
        const cards = document.querySelectorAll('.user-row-card');
        let isValid = true;
        let validationMsg = '';

        for (let i = 0; i < cards.length; i++) {
            const card = cards[i];
            const idx = parseInt(card.getAttribute('data-index'));

            const usernameInput = card.querySelector(`input[name="users[${idx}][username]"]`);
            const roleSelect = document.getElementById(`role-select-${idx}`);
            const deptSelect = document.getElementById(`department-select-${idx}`);
            const customInput = document.getElementById(`custom-dept-input-${idx}`);
            const rankSelect = document.getElementById(`rank-select-${idx}`);

            const username = usernameInput ? usernameInput.value.trim() : '';
            const role = roleSelect ? roleSelect.value : '';

            if (!username) {
                validationMsg = `User #${i + 1}: Username is required.`;
                isValid = false;
                break;
            }

            // Department validation
            if (role === 'Department Head' || role === 'Requisitioner') {
                if (!deptSelect || !deptSelect.value) {
                    validationMsg = `User #${i + 1}: Department is required.`;
                    isValid = false;
                    break;
                }
                if (deptSelect.value === 'custom') {
                    const customVal = customInput ? customInput.value.trim() : '';
                    if (!customVal) {
                        validationMsg = `User #${i + 1}: Please enter a custom department name.`;
                        isValid = false;
                        break;
                    }
                }
            }

            // Rank validation
            if (role === 'Dept Head HR' || role === 'Head of Welfare' || role === 'Main Admin') {
                if (!rankSelect || !rankSelect.value) {
                    validationMsg = `User #${i + 1}: Please select a Rank (SNCO / NCO).`;
                    isValid = false;
                    break;
                }
            }
        }

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: validationMsg,
                confirmButtonColor: '#16a34a'
            });
        }
    });

    // Initialize all existing rows on load
    document.addEventListener('DOMContentLoaded', () => {
        const cards = document.querySelectorAll('.user-row-card');
        cards.forEach(card => {
            const idx = parseInt(card.getAttribute('data-index'));
            initRow(idx);
            
            // For old inputs, re-check custom department selects
            const deptSelect = document.getElementById(`department-select-${idx}`);
            if (deptSelect && deptSelect.value === 'custom') {
                handleRowDeptSelectChange(deptSelect, idx);
            }
        });
        updateRemoveButtons();
        updateCounterLabel();
    });
</script>

<style>
    @keyframes cardFadeOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.95); }
    }
</style>
@endpush
