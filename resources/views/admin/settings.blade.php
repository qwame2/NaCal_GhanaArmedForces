@extends('layouts.admin')

@section('title', 'System Configuration')

@section('content')
<style>
    .main-wrapper > *:not(header) {
        max-width: 2000px !important;
    }
    /* ── Page Layout ── */
    .settings-shell {
        display: block;
        margin-top: 0.5rem;
        animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
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

    /* Premium Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
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
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .cfg-search-wrap input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(136, 19, 55, 0.1);
    }

    .cfg-search-wrap input::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    /* ── Section Cards ── */
    .cfg-card {
        background: white;
        border-radius: 28px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
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

    .cfg-icon-box i {
        width: 22px;
        height: 22px;
    }

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
        border-color: #881337 !important;
    }

    /* ── Select2 Multiple Selection Styling ── */
    .select2-container--default .select2-selection--multiple {
        min-height: 48px !important;
        background: white !important;
        border: 2px solid #edf2f7 !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        padding: 4px 10px !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01) !important;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #881337 !important;
        box-shadow: 0 0 0 4px rgba(136, 19, 55, 0.1) !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        width: 100% !important;
        padding: 0 !important;
        gap: 6px !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: rgba(136, 19, 55, 0.1) !important;
        border: 1px solid rgba(136, 19, 55, 0.25) !important;
        border-radius: 8px !important;
        color: #881337 !important;
        font-weight: 700 !important;
        font-size: 0.8rem !important;
        padding: 4px 10px !important;
        margin: 2px 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        transition: all 0.15s ease !important;
        box-shadow: 0 1px 2px rgba(136, 19, 55, 0.05) !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice:hover {
        background: #881337 !important;
        border-color: #881337 !important;
        color: white !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #881337 !important;
        border: none !important;
        background: transparent !important;
        font-weight: 800 !important;
        font-size: 0.85rem !important;
        margin-right: 6px !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice:hover .select2-selection__choice__remove {
        color: white !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #f43f5e !important;
        transform: scale(1.15) !important;
    }

    .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
        margin: 2px 0 !important;
        font-family: inherit !important;
        font-size: 0.88rem !important;
        font-weight: 600 !important;
        color: #1e293b !important;
        height: auto !important;
        line-height: inherit !important;
        background: transparent !important;
        border: none !important;
        outline: none !important;
    }

    .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder {
        color: #94a3b8 !important;
        font-weight: 500 !important;
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
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05) !important;
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
        background-color: #881337 !important;
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

    .cfg-card-body {
        padding: 2rem;
    }

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
        border-color: #881337;
        box-shadow: 0 8px 24px rgba(136, 19, 55, 0.07);
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

    .toggle-pill.on {
        background: #dcfce7;
        color: #881337;
    }

    .toggle-pill.off {
        background: #f1f5f9;
        color: #94a3b8;
    }

    .cfg-toggle-label {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }

    .cfg-toggle-label input {
        display: none;
    }

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
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .cfg-toggle-label input:checked+.cfg-toggle-track {
        background: #881337;
    }

    .cfg-toggle-label input:checked+.cfg-toggle-track::after {
        transform: translateX(24px);
    }

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
        box-shadow: 0 0 0 4px rgba(136, 19, 55, 0.1);
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
        box-shadow: 0 0 0 4px rgba(136, 19, 55, 0.1);
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

    .cat-badge:hover {
        border-color: #a7f3d0;
        background: #f0fdf4;
    }

    .cat-code-pill {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #881337;
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

    .cat-del-btn:hover {
        color: #ef4444;
        transform: scale(1.1);
    }

    .cat-del-btn i,
    .cat-del-btn svg {
        width: 15px !important;
        height: 15px !important;
    }

    .cat-edit-btn {
        background: none;
        border: none;
        color: #cbd5e1;
        cursor: pointer;
        transition: 0.2s;
        padding: 2px;
        display: flex;
        align-items: center;
    }

    .cat-edit-btn:hover {
        color: var(--primary);
        transform: scale(1.1);
    }

    .cat-edit-btn i,
    .cat-edit-btn svg {
        width: 15px !important;
        height: 15px !important;
    }

    /* ── New Category Form ── */
    .cat-form-card {
        background: white;
        border: 1.5px solid #f1f5f9;
        border-radius: 22px;
        padding: 1.75rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
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
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(136, 19, 55, 0.12);
        border-radius: 20px;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
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

    .cfg-save-bar span i {
        width: 15px;
        color: #881337;
    }

    .btn-cfg-save {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.75rem 2rem;
        background: #881337;
        color: white;
        border: none;
        border-radius: 14px;
        font-weight: 900;
        font-size: 0.9rem;
        cursor: pointer;
        transition: 0.25s;
        box-shadow: 0 8px 20px rgba(136, 19, 55, 0.25);
    }

    .btn-cfg-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(136, 19, 55, 0.35);
    }

    .btn-cfg-save:active {
        transform: scale(0.97);
    }

    .btn-cfg-save i {
        width: 18px;
    }

    .btn-cfg-add {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.75rem 1.5rem;
        background: #881337;
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
        box-shadow: 0 6px 16px rgba(136, 19, 55, 0.25);
    }

    .btn-cfg-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(136, 19, 55, 0.3);
    }

    .btn-cfg-add i {
        width: 18px;
    }

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

    .otp-preset-btn:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .otp-preset-btn.active {
        background: #881337;
        color: white;
    }

    @media(max-width: 1024px) {
        .workflow-info-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* ── Stores Dept Head Workflow Redesign ── */
    .workflow-card-modern {
        background: white;
        border-radius: 28px;
        border: 1.5px solid #e2e8f0;
        box-shadow: 0 10px 30px rgba(136, 19, 55, 0.03);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .workflow-card-modern:hover {
        border-color: #881337;
        box-shadow: 0 16px 40px rgba(136, 19, 55, 0.06);
    }

    .workflow-cat-grid-modern {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1.25rem;
    }

    .workflow-cat-card-modern {
        background: #f8fafc;
        border: 2px solid #edf2f7;
        border-radius: 20px;
        padding: 1.25rem 1.5rem;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 14px;
        position: relative;
        overflow: hidden;
        user-select: none;
    }

    .workflow-cat-card-modern:hover {
        border-color: #cbd5e1;
        transform: translateY(-2px);
        background: #ffffff;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.02);
    }

    .workflow-cat-card-modern.active {
        background: rgba(136, 19, 55, 0.06);
        border-color: #881337;
        box-shadow: 0 8px 24px rgba(136, 19, 55, 0.06);
    }

    .workflow-cat-card-modern.active:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(136, 19, 55, 0.1);
    }

    .workflow-cat-card-modern .corner-glow {
        position: absolute;
        top: -20px;
        right: -20px;
        width: 50px;
        height: 50px;
        background: radial-gradient(circle, rgba(136, 19, 55, 0.2) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.25s ease;
        pointer-events: none;
    }

    .workflow-cat-card-modern.active .corner-glow {
        opacity: 1;
    }

    .workflow-cat-card-modern .cat-circle {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: #ffffff;
        color: #881337;
        font-weight: 900;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #e2e8f0;
        transition: all 0.25s ease;
        flex-shrink: 0;
    }

    .workflow-cat-card-modern.active .cat-circle {
        background: #881337;
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 4px 8px rgba(136, 19, 55, 0.18);
    }

    .workflow-cat-card-modern .status-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: #64748b;
        margin-top: 2px;
        transition: color 0.25s;
    }

    .workflow-cat-card-modern.active .status-label {
        color: #881337;
    }

    .workflow-cat-card-modern .indicator-dot {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s ease;
        flex-shrink: 0;
        margin-left: auto;
    }

    .workflow-cat-card-modern.active .indicator-dot {
        background: #881337;
        border-color: #881337;
        box-shadow: 0 2px 6px rgba(136, 19, 55, 0.25);
    }

    /* Special theme colors for the DG workflow cards */
    .dg-workflow-container .workflow-cat-card-modern.active {
        background: rgba(136, 19, 55, 0.08);
        border-color: #881337;
        box-shadow: 0 8px 24px rgba(136, 19, 55, 0.06);
    }
    .dg-workflow-container .workflow-cat-card-modern.active:hover {
        box-shadow: 0 12px 28px rgba(136, 19, 55, 0.1);
    }
    .dg-workflow-container .workflow-cat-card-modern.active .cat-circle {
        background: #881337;
        box-shadow: 0 4px 8px rgba(136, 19, 55, 0.18);
        color: #ffffff;
        border-color: transparent;
    }
    .dg-workflow-container .workflow-cat-card-modern.active .status-label {
        color: #881337;
    }
    .dg-workflow-container .workflow-cat-card-modern.active .indicator-dot {
        background: #881337;
        border-color: #881337;
        box-shadow: 0 2px 6px rgba(136, 19, 55, 0.25);
    }

    .flow-line {
        flex: 1;
        height: 3px;
        transition: all 0.4s ease;
        background: #cbd5e1;
        margin-top: -20px;
    }

    .flow-line.active {
        background: #881337;
        box-shadow: 0 0 8px rgba(136, 19, 55, 0.25);
    }

    .flow-line.dashed {
        background: repeating-linear-gradient(to right, #cbd5e1 0px, #cbd5e1 6px, transparent 6px, transparent 12px);
    }

    .flow-node-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .flow-node-badge {
        font-size: 0.6rem;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 30px;
        transition: all 0.3s ease;
    }
</style>

{{-- Page Header --}}
<div class="view-header" style="margin-bottom: 2rem;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <div style="width: 52px; height: 52px; background: #881337; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 8px 20px rgba(136,19,55,0.25);">
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
            <input type="hidden" name="settings_form" value="1">

            @foreach($settings as $group => $groupSettings)
            @php if($group === 'inventory') continue; @endphp

            @php
            $colorMap = [
            'security' => ['color' => '#ef4444', 'bg' => '#ef4444', 'icon' => 'shield-alert'],
            'inventory' => ['color' => '#881337', 'bg' => '#881337', 'icon' => 'package'],
            'system' => ['color' => '#881337', 'bg' => '#881337', 'icon' => 'server'],
            ];
            $meta = $colorMap[$group] ?? ['color' => '#881337', 'bg' => '#881337', 'icon' => 'settings'];
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
                        @php if(in_array($setting->key, ['strict_audit_logging','enable_strict_audit_logging','approval_timeout_minutes','item_unit_rules','ledge_categories','reporting_enabled','low_stock_threshold','item_threshold_rules','otp_expiry_hours','otp_expiry_minutes'])) continue; @endphp
                        <div class="cfg-item">
                            @if($setting->key === 'allow_personnel_registration')
                            <p class="cfg-item-label">Allow User Registration</p>
                            @elseif($setting->key === 'allow_record_existing_item')
                            <p class="cfg-item-label">Allow Record Existing Item</p>
                            @else
                            <p class="cfg-item-label">{{ ucwords(str_replace('_', ' ', $setting->key)) }}</p>
                            @endif

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
                            <p class="cfg-item-desc">
                                {{ $setting->key === 'allow_personnel_registration' ? 'Allow new users to register accounts (requires admin approval later).' : ($setting->key === 'allow_record_existing_item' ? 'Enable or disable the Record Existing Item button for Store Officers on their dashboard.' : $setting->description) }}
                            </p>
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
                                <i data-lucide="timer" style="width: 18px; color: #881337;"></i>
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

            @if(auth()->user()->role === 'Main Admin' && !in_array(auth()->user()->department, ['Human Resource Management Department', 'Welfare Department']))
            {{-- Stores Department Head Approval Workflow --}}
            <div class="workflow-card-modern" style="display: none;">
                @php
                $selectedCats = \App\Models\Setting::get('stores_dept_head_approval_categories', []);
                if (!is_array($selectedCats)) {
                    $selectedCats = json_decode($selectedCats, true) ?? [];
                }
                @endphp
                <div class="cfg-card-header" style="background: #ffffff; padding: 2.25rem 2.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div class="cfg-icon-box" style="background: #881337; box-shadow: 0 8px 20px rgba(136,19,55,0.15); width: 50px; height: 50px; border-radius: 16px;">
                            <i data-lucide="shield-check" style="width: 24px; height: 24px; color: white;"></i>
                        </div>
                        <div>
                            <h3 style="font-weight: 950; font-size: 1.25rem; color: #0f172a; margin: 0; letter-spacing: -0.03em;">Stores Dept. Head Approval Workflow</h3>
                            <p style="color: #64748b; font-weight: 600; font-size: 0.82rem; margin: 4px 0 0;">Select the specific item categories that require intermediate review by the Department Head (Stores).</p>
                        </div>
                    </div>
                    <span id="workflow-active-badge" style="background: rgba(136,19,55,0.08); color: #881337; font-size: 0.72rem; font-weight: 800; padding: 6px 14px; border-radius: 30px; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(136,19,55,0.15); box-shadow: 0 2px 4px rgba(136,19,55,0.02); transition: all 0.3s ease;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #881337; transition: all 0.3s ease;" id="workflow-badge-dot"></span>
                        <span id="workflow-badge-text" style="letter-spacing: 0.02em;">Active Categories: {{ count($selectedCats) }}</span>
                    </span>
                </div>
                <div class="cfg-card-body" style="padding: 2.5rem; background: #ffffff;">
                    <input type="hidden" name="stores_dept_head_approval_categories_present" value="1">

                    <!-- Hidden real multi-select to preserve native settings submission -->
                    <select name="stores_dept_head_approval_categories[]" id="stores_dept_head_approval_categories" multiple="multiple" style="display: none;">
                        @foreach($categories ?? [] as $code => $name)
                        <option value="{{ $code }}" {{ in_array($code, $selectedCats) ? 'selected' : '' }}>{{ $code }}</option>
                        @endforeach
                    </select>

                    <div style="display: flex; flex-direction: column; gap: 2rem;">

                        <!-- Premium Interactive Card Selection Grid -->
                        <div class="workflow-cat-grid-modern">
                            @foreach($categories ?? [] as $code => $name)
                            @php $isActive = in_array($code, $selectedCats); @endphp
                            <div class="workflow-cat-card-modern {{ $isActive ? 'active' : '' }}"
                                onclick="toggleWorkflowCategory('{{ $code }}', this)">

                                <!-- Glowing corner accent for active state -->
                                <div class="corner-glow"></div>

                                <!-- Category Code Circle -->
                                <div class="cat-circle">
                                    {{ $code }}
                                </div>

                                <!-- Name & Status -->
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 850; font-size: 0.88rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $name }}</div>
                                    <div class="status-label">
                                        {{ $isActive ? 'Requires Stores Head' : 'Bypasses Stores Head' }}
                                    </div>
                                </div>

                                <!-- Indicator Circle -->
                                <div class="indicator-dot">
                                    <i data-lucide="check" style="width: 11px; height: 11px; color: white; display: {{ $isActive ? 'block' : 'none' }};"></i>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Workflow Explainer Graphic and Logic Info Card -->
                        <div style="display: grid; grid-template-columns: 1fr 480px; gap: 2rem; align-items: stretch; margin-top: 0.5rem;" class="workflow-info-grid">

                            <!-- Sleek Gradient Alert Card -->
                            <div style="background: rgba(136, 19, 55, 0.03);
                                             border: 1.5px solid #edf2f7;
                                             border-radius: 24px;
                                             padding: 1.75rem 2rem;
                                             display: flex;
                                             gap: 1.25rem;
                                             align-items: flex-start;">
                                <div style="width: 42px; height: 42px; background: rgba(136,19,55,0.06); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #881337; flex-shrink: 0; margin-top: 2px;">
                                    <i data-lucide="info" style="width: 20px; height: 20px;"></i>
                                </div>
                                <div style="flex: 1;">
                                    <h5 style="margin: 0 0 6px 0; font-size: 0.95rem; font-weight: 850; color: #1e293b; letter-spacing: -0.010em;">Smart Routing Protocol Active</h5>
                                    <p style="margin: 0; font-size: 0.8rem; color: #475569; line-height: 1.6; font-weight: 600;">
                                        When item categories are configured above, any submitted requisition containing matching items will be routed for manual review by the <strong>Department Head (Stores)</strong> prior to final confirmation. Requisitions consisting solely of bypassed categories skip the Stores Department Head approval stage completely, saving processing time and avoiding administration bottlenecks.
                                    </p>
                                </div>
                            </div>

                            <!-- Dynamic Mini Infographic Visualizer Card -->
                            <div style="background: #ffffff; border: 1.5px solid #edf2f7; border-radius: 24px; padding: 1.75rem 2rem; display: flex; flex-direction: column; justify-content: center; gap: 1.25rem; box-shadow: 0 4px 20px rgba(0,0,0,0.015);">
                                <div style="font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; text-align: center; margin-bottom: 0.25rem;">Live Approval Routing Pathway</div>

                                <div style="display: flex; align-items: center; justify-content: space-between; position: relative; width: 100%; padding: 0.5rem 0;" class="flow-nodes-container">

                                    <!-- Origin Node -->
                                    <div class="flow-node" style="display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 2; position: relative; width: 68px;">
                                        <div class="flow-node-icon" style="background: #881337; color: white; box-shadow: 0 4px 12px rgba(136,19,55,0.15); width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                            <i data-lucide="user-check" style="width: 15px; height: 15px;"></i>
                                        </div>
                                        <span style="font-size: 0.65rem; font-weight: 855; color: #1e293b; white-space: nowrap;">Dept. Head</span>
                                        <span class="flow-node-badge" style="background: #dcfce7; color: #881337; font-size: 0.55rem; font-weight: 800; padding: 1px 6px; border-radius: 30px; transition: all 0.3s ease;">Required</span>
                                    </div>800; padding: 1px 6px; border-radius: 30px; transition: all 0.3s ease;">Required</span>
                                    </div>

                                    <!-- Connector 1 (Now connects to DG Node, so controlled by DG active state) -->
                                    <div class="flow-line flow-line-2" style="flex: 1; height: 3px; transition: all 0.4s ease; background: #cbd5e1; margin-top: -16px;"></div>

                                    <!-- DG Node (Director Gen.) -->
                                    <div class="flow-node flow-node-dg" style="display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 2; position: relative; width: 68px;">
                                        <div class="flow-node-icon" style="width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                            <i data-lucide="user-cog" style="width: 15px; height: 15px;"></i>
                                        </div>
                                        <span class="flow-node-label" style="font-size: 0.65rem; font-weight: 855; white-space: nowrap;">Director Gen.</span>
                                        <span class="flow-node-badge" style="font-size: 0.55rem; font-weight: 800; padding: 1px 6px; border-radius: 30px; transition: all 0.3s ease;"></span>
                                    </div>

                                    <!-- Connector 2 (Now connects to Stores Head, so controlled by Stores Head active state) -->
                                    <div class="flow-line flow-line-1" style="flex: 1; height: 3px; transition: all 0.4s ease; background: #cbd5e1; margin-top: -16px;"></div>

                                    <!-- Stores Head Node (Head of Admin) -->
                                    <div class="flow-node flow-node-stores" style="display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 2; position: relative; width: 68px;">
                                        <div class="flow-node-icon" style="width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                            <i data-lucide="package" style="width: 15px; height: 15px;"></i>
                                        </div>
                                        <span class="flow-node-label" style="font-size: 0.65rem; font-weight: 855; white-space: nowrap;">Head of Admin(Authorizer)</span>
                                        <span class="flow-node-badge" style="font-size: 0.55rem; font-weight: 800; padding: 1px 6px; border-radius: 30px; transition: all 0.3s ease;"></span>
                                    </div>

                                    <!-- Connector 3 (Connects to Head of Stores, always active) -->
                                    <div class="flow-line flow-line-3" style="flex: 1; height: 3px; transition: all 0.4s ease; background: #cbd5e1; margin-top: -16px;"></div>

                                    <!-- Head of Stores Node -->
                                    <div class="flow-node" style="display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 2; position: relative; width: 68px;">
                                        <div class="flow-node-icon" style="background: #881337; color: white; box-shadow: 0 4px 12px rgba(136,19,55,0.15); width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                            <i data-lucide="shield-check" style="width: 15px; height: 15px;"></i>
                                        </div>
                                        <span style="font-size: 0.65rem; font-weight: 855; color: #1e293b; white-space: nowrap;">Head of Stores</span>
                                        <span class="flow-node-badge" style="background: #d1fae5; color: #065f46; font-size: 0.55rem; font-weight: 800; padding: 1px 6px; border-radius: 30px; transition: all 0.3s ease;">Final Sign</span>
                                    </div>

                                </div>

                                <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; line-height: 1.45; text-align: center; background: #f8fafc; border-radius: 12px; padding: 8px 12px; border: 1px solid #f1f5f9; transition: all 0.3s ease;" class="workflow-helper-hint">
                                </div>
                            </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(auth()->user()->role === 'Main Admin' && !in_array(auth()->user()->department, ['Human Resource Management Department', 'Welfare Department']))
            {{-- Director General's Approval Workflow --}}
            <div class="workflow-card-modern dg-workflow-container" style="margin-top: 1.5rem;">
                @php
                $dgSelectedCats = \App\Models\Setting::get('dg_approval_categories', []);
                if (!is_array($dgSelectedCats)) {
                    $dgSelectedCats = json_decode($dgSelectedCats, true) ?? [];
                }
                @endphp
                <div class="cfg-card-header" style="background: #ffffff; padding: 2.25rem 2.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div class="cfg-icon-box" style="background: #881337; box-shadow: 0 8px 20px rgba(136, 19, 55, 0.15); width: 50px; height: 50px; border-radius: 16px;">
                            <i data-lucide="user-cog" style="width: 24px; height: 24px; color: white;"></i>
                        </div>
                        <div>
                            <h3 style="font-weight: 955; font-size: 1.25rem; color: #0f172a; margin: 0; letter-spacing: -0.03em;">Director General's Approval Workflow</h3>
                            <p style="color: #64748b; font-weight: 600; font-size: 0.82rem; margin: 4px 0 0;">Select the specific item categories that require intermediate review and sign-off by the Director General.</p>
                        </div>
                    </div>
                    <span id="dg-workflow-active-badge" style="background: rgba(136,19,55,0.08); color: #881337; font-size: 0.72rem; font-weight: 800; padding: 6px 14px; border-radius: 30px; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(136,19,55,0.15); box-shadow: 0 2px 4px rgba(136,19,55,0.02); transition: all 0.3s ease;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #881337; transition: all 0.3s ease;" id="dg-workflow-badge-dot"></span>
                        <span id="dg-workflow-badge-text" style="letter-spacing: 0.02em;">Active Categories: {{ count($dgSelectedCats) }}</span>
                    </span>
                </div>
                <div class="cfg-card-body" style="padding: 2.5rem; background: #ffffff;">
                    <input type="hidden" name="dg_approval_categories_present" value="1">

                    <!-- Hidden real multi-select to preserve native settings submission -->
                    <select name="dg_approval_categories[]" id="dg_approval_categories" multiple="multiple" style="display: none;">
                        @foreach($categories ?? [] as $code => $name)
                        <option value="{{ $code }}" {{ in_array($code, $dgSelectedCats) ? 'selected' : '' }}>{{ $code }}</option>
                        @endforeach
                    </select>

                    <div style="display: flex; flex-direction: column; gap: 2rem;">

                        <!-- Premium Interactive Card Selection Grid -->
                        <div class="workflow-cat-grid-modern">
                            @foreach($categories ?? [] as $code => $name)
                            @php $isActive = in_array($code, $dgSelectedCats); @endphp
                            <div class="workflow-cat-card-modern {{ $isActive ? 'active' : '' }}"
                                onclick="toggleDGWorkflowCategory('{{ $code }}', this)">

                                <!-- Glowing corner accent for active state -->
                                <div class="corner-glow"></div>

                                <!-- Category Code Circle -->
                                <div class="cat-circle">
                                    {{ $code }}
                                </div>

                                <!-- Name & Status -->
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 855; font-size: 0.88rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $name }}</div>
                                    <div class="status-label">
                                        {{ $isActive ? 'Requires DG' : 'Bypasses DG' }}
                                    </div>
                                </div>

                                <!-- Indicator Circle -->
                                <div class="indicator-dot">
                                    <i data-lucide="check" style="width: 11px; height: 11px; color: white; display: {{ $isActive ? 'block' : 'none' }};"></i>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Workflow Explainer Graphic and Logic Info Card -->
                        <div style="display: grid; grid-template-columns: 1fr 480px; gap: 2rem; align-items: stretch; margin-top: 0.5rem;" class="workflow-info-grid">

                            <!-- Sleek Gradient Alert Card -->
                            <div style="background: rgba(136, 19, 55, 0.03);
                                             border: 1.5px solid #edf2f7;
                                             border-radius: 24px;
                                             padding: 1.75rem 2rem;
                                             display: flex;
                                             gap: 1.25rem;
                                             align-items: flex-start;">
                                <div style="width: 42px; height: 42px; background: rgba(136,19,55,0.06); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #881337; flex-shrink: 0; margin-top: 2px;">
                                    <i data-lucide="info" style="width: 20px; height: 20px;"></i>
                                </div>
                                <div style="flex: 1;">
                                    <h5 style="margin: 0 0 6px 0; font-size: 0.95rem; font-weight: 855; color: #1e293b; letter-spacing: -0.010em;">DG Smart Routing Protocol Active</h5>
                                    <p style="margin: 0; font-size: 0.8rem; color: #475569; line-height: 1.6; font-weight: 600;">
                                        When item categories are configured above, any submitted requisition containing matching items will be routed for manual review by the <strong>Director General</strong> prior to final confirmation and stock deduction. Requisitions consisting solely of bypassed categories skip the DG approval stage completely.
                                    </p>
                                </div>
                            </div>

                            <!-- Dynamic Mini Infographic Visualizer Card -->
                            <div style="background: #ffffff; border: 1.5px solid #edf2f7; border-radius: 24px; padding: 1.75rem 2rem; display: flex; flex-direction: column; justify-content: center; gap: 1.25rem; box-shadow: 0 4px 20px rgba(0,0,0,0.015);">
                                <div style="font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; text-align: center; margin-bottom: 0.25rem;">Live Approval Routing Pathway</div>

                                <div style="display: flex; align-items: center; justify-content: space-between; position: relative; width: 100%; padding: 0.5rem 0;" class="flow-nodes-container">

                                    <!-- Origin Node -->
                                    <div class="flow-node" style="display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 2; position: relative; width: 68px;">
                                        <div class="flow-node-icon" style="background: #881337; color: white; box-shadow: 0 4px 12px rgba(136,19,55,0.15); width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                            <i data-lucide="user-check" style="width: 15px; height: 15px;"></i>
                                        </div>
                                        <span style="font-size: 0.65rem; font-weight: 855; color: #1e293b; white-space: nowrap;">Dept. Head</span>
                                        <span class="flow-node-badge" style="background: #dcfce7; color: #881337; font-size: 0.55rem; font-weight: 800; padding: 1px 6px; border-radius: 30px; transition: all 0.3s ease;">Required</span>
                                    </div>

                                    <!-- Connector 1 (Now connects to DG Node, so controlled by DG active state) -->
                                    <div class="flow-line flow-line-2" style="flex: 1; height: 3px; transition: all 0.4s ease; background: #cbd5e1; margin-top: -16px;"></div>

                                    <!-- DG Node (Director Gen.) -->
                                    <div class="flow-node flow-node-dg" style="display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 2; position: relative; width: 68px;">
                                        <div class="flow-node-icon" style="width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                            <i data-lucide="user-cog" style="width: 15px; height: 15px;"></i>
                                        </div>
                                        <span class="flow-node-label" style="font-size: 0.65rem; font-weight: 855; white-space: nowrap;">Director Gen.</span>
                                        <span class="flow-node-badge" style="font-size: 0.55rem; font-weight: 800; padding: 1px 6px; border-radius: 30px; transition: all 0.3s ease;"></span>
                                    </div>

                                    <!-- Connector 2 (Now connects to Stores Head, so controlled by Stores Head active state) -->
                                    <div class="flow-line flow-line-1" style="flex: 1; height: 3px; transition: all 0.4s ease; background: #cbd5e1; margin-top: -16px;"></div>

                                    <!-- Stores Head Node (Head of Admin) -->
                                    <div class="flow-node flow-node-stores" style="display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 2; position: relative; width: 68px;">
                                        <div class="flow-node-icon" style="width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                            <i data-lucide="package" style="width: 15px; height: 15px;"></i>
                                        </div>
                                        <span class="flow-node-label" style="font-size: 0.65rem; font-weight: 855; white-space: nowrap;">Head of Admin(Authorizer)</span>
                                        <span class="flow-node-badge" style="font-size: 0.55rem; font-weight: 800; padding: 1px 6px; border-radius: 30px; transition: all 0.3s ease;"></span>
                                    </div>

                                    <!-- Connector 3 (Connects to Head of Stores, always active) -->
                                    <div class="flow-line flow-line-3" style="flex: 1; height: 3px; transition: all 0.4s ease; background: #cbd5e1; margin-top: -16px;"></div>

                                    <!-- Head of Stores Node -->
                                    <div class="flow-node" style="display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 2; position: relative; width: 68px;">
                                        <div class="flow-node-icon" style="background: #881337; color: white; box-shadow: 0 4px 12px rgba(136,19,55,0.15); width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                            <i data-lucide="shield-check" style="width: 15px; height: 15px;"></i>
                                        </div>
                                        <span style="font-size: 0.65rem; font-weight: 855; color: #1e293b; white-space: nowrap;">Head of Stores</span>
                                        <span class="flow-node-badge" style="background: #d1fae5; color: #065f46; font-size: 0.55rem; font-weight: 800; padding: 1px 6px; border-radius: 30px; transition: all 0.3s ease;">Final Sign</span>
                                    </div>

                                </div>

                                <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; line-height: 1.45; text-align: center; background: #f8fafc; border-radius: 12px; padding: 8px 12px; border: 1px solid #f1f5f9; transition: all 0.3s ease;" class="workflow-helper-hint">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Sticky Save Bar --}}
            <div class="cfg-save-bar">
                <span><i data-lucide="info"></i> Unsaved changes will be lost on navigation.</span>
                <button type="submit" class="btn-cfg-save">
                    <i data-lucide="save"></i> Save Settings
                </button>
            </div>
        </form>
        @endif




        {{-- Category Management --}}
        <div class="cfg-card" id="category-configs">
            <div class="cfg-card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div class="cfg-icon-box" style="background: #881337;">
                        <i data-lucide="tags"></i>
                    </div>
                    <div>
                        <h3>Item Categories</h3>
                        <p>Manage category codes for the inventory system.</p>
                    </div>
                </div>

                <div class="cfg-search-wrap" style="margin: 0 3.5rem 0 0; width: 260px; position: relative;">
                    <i data-lucide="search" style="width: 14px; position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="text" id="categorySearch" placeholder="Search categories..." oninput="filterCategories()" style="width: 100%; padding: 0.5rem 1rem 0.5rem 2.2rem; font-size: 0.8rem; height: 38px; border: 2px solid #edf2f7; border-radius: 10px; outline: none; transition: 0.2s; background: white;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#edf2f7'">
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
                                <div style="display: flex; gap: 4px; align-items: center;">
                                    <button type="button" onclick="populateCategoryForm('{{ $code }}', '{{ addslashes($name) }}')" class="cat-edit-btn" title="Edit Category">
                                        <i data-lucide="edit-3"></i>
                                    </button>
                                    <form action="{{ route('admin.settings.category.destroy', $code) }}" method="POST" onsubmit="return confirm('Remove category {{ $code }}?');" style="margin: 0; display: inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="cat-del-btn">
                                            <i data-lucide="x"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @empty
                            <div style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; padding: 1rem 0;">No categories added yet.</div>
                            @endforelse
                        </div>
                        
                        <div id="noCategoriesFound" style="display: none; padding: 2rem; text-align: center; background: #f8fafc; border-radius: 16px; border: 1.5px dashed #e2e8f0; margin-top: 1rem;">
                            <i data-lucide="search" style="width: 32px; height: 32px; color: #cbd5e1; margin-bottom: 0.75rem;"></i>
                            <p style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; margin: 0;">No matching categories found.</p>
                        </div>
                    </div>

                    {{-- Add New Category --}}
                    <div class="cat-form-card">
                        <h5 id="categoryFormTitle">Add New Category</h5>
                        <p id="categoryFormSub">Add a new ledger code for inventory tracking.</p>
                        <form id="categoryForm" action="{{ route('admin.settings.category') }}" method="POST">
                            @csrf
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div>
                                    <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Code</label>
                                    <input type="text" id="categoryCodeInput" name="category_code" class="cfg-text-input" placeholder="e.g. M" required maxlength="3" style="text-transform: uppercase; font-size: 1.1rem; font-weight: 900; text-align: center;">
                                </div>
                                <div>
                                    <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Category Name</label>
                                    <input type="text" id="categoryNameInput" name="category_name" class="cfg-text-input" placeholder="e.g. Medical Assets" required>
                                </div>
                                <div style="display: flex; gap: 10px; margin-top: 1rem;">
                                    <button type="submit" id="categorySubmitBtn" class="btn-cfg-add" style="margin-top: 0; flex: 1;">
                                        <i data-lucide="plus-circle" id="categorySubmitIcon"></i> <span id="categorySubmitText">Add Category</span>
                                    </button>
                                    <button type="button" id="categoryResetBtn" onclick="resetCategoryForm()" style="display: none; padding: 0.75rem 1.5rem; background: #f1f5f9; color: #64748b; border: none; border-radius: 14px; font-weight: 800; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
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



    {{-- Item Thresholds --}}
    <div class="cfg-card" id="threshold-rules" style="margin-top: 2rem;">
        <div class="cfg-card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div class="cfg-icon-box" style="background: linear-gradient(135deg,#ef4444,#dc2626);">
                    <i data-lucide="alert-triangle"></i>
                </div>
                <div>
                    <h3 style="margin: 0 0 0.25rem 0;">Item Thresholds</h3>
                    <p style="margin: 0;">Define specific low stock thresholds for items. Only items matching these will trigger alerts.</p>
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
                        <i data-lucide="list" style="width: 14px;"></i> Active Thresholds
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
                        <p style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; margin: 0;">No stock thresholds defined yet. Add thresholds on the right.</p>
                    </div>
                    @else
                    <div class="custom-scrollbar" style="display: flex; flex-direction: column; gap: 1.5rem; max-height: 400px; overflow-y: auto; padding-right: 0.5rem;" id="thresholdsContainer">
                        @foreach($groupedThresholds as $catCode => $thresholdsGroup)
                        <div class="threshold-rule-group">
                            <h6 style="font-size: 0.85rem; font-weight: 800; color: #475569; margin: 0 0 0.75rem; display: flex; align-items: center; gap: 8px;">
                                <span style="background: #e2e8f0; color: #475569; padding: 3px 8px; border-radius: 6px; font-size: 0.7rem;">{{ $catCode }}</span>
                                {{ $categories[$catCode] ?? 'Uncategorized' }}
                            </h6>
                            <div class="custom-scrollbar" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.75rem; max-height: 280px; overflow-y: auto; padding-right: 0.25rem;">
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
                                        <form action="{{ route('admin.settings.threshold-rule.destroy') }}" method="POST" onsubmit="return confirm('Remove threshold for \'{{ $keyword }}\'?');" style="margin: 0;">
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

                {{-- Add New Threshold --}}
                <div class="cat-form-card">
                    <h5>Add Threshold</h5>
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
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Item Keyword(s)</label>
                                <select name="keywords[]" id="thresholdKeyword" class="cfg-text-input select2-threshold" required multiple="multiple">
                                    <option value="">Select Category First...</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Stock Threshold</label>
                                <input type="number" name="threshold" class="cfg-text-input" placeholder="e.g. 10" required min="0">
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" id="thresholdSubmitBtn" class="btn-cfg-add" style="flex: 1; background: linear-gradient(135deg,#ef4444,#dc2626); box-shadow: 0 6px 16px rgba(239,68,68,0.25);">
                                    <i data-lucide="plus-circle" id="thresholdSubmitIcon"></i> <span id="thresholdSubmitText">Add Threshold</span>
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
                <div class="cfg-icon-box" style="background: linear-gradient(135deg,#881337,#881337);">
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
                    <div class="custom-scrollbar" style="display: flex; flex-direction: column; gap: 1.5rem; max-height: 400px; overflow-y: auto; padding-right: 0.5rem;" id="limitsContainer">
                        @foreach($groupedLimits as $catCode => $limitsGroup)
                        <div class="limit-rule-group">
                            <h6 style="font-size: 0.85rem; font-weight: 800; color: #475569; margin: 0 0 0.75rem; display: flex; align-items: center; gap: 8px;">
                                <span style="background: #e2e8f0; color: #475569; padding: 3px 8px; border-radius: 6px; font-size: 0.7rem;">{{ $catCode }}</span>
                                {{ $categories[$catCode] ?? 'Uncategorized' }}
                            </h6>
                            <div class="custom-scrollbar" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.75rem; max-height: 280px; overflow-y: auto; padding-right: 0.25rem;">
                                @foreach($limitsGroup as $keyword => $limit)
                                <div class="limit-rule-card" data-keyword="{{ strtolower($keyword) }}" style="display: flex; align-items: center; gap: 10px; padding: 0.75rem 1rem; background: white; border: 1.5px solid #f1f5f9; border-radius: 16px; transition: 0.3s;">
                                    <div style="width: 34px; height: 34px; border-radius: 10px; background: linear-gradient(135deg,#881337,#881337); color: white; font-weight: 900; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; text-transform: uppercase;">
                                        <i data-lucide="ban" style="width: 14px;"></i>
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-weight: 800; font-size: 0.82rem; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $keyword }}">{{ $keyword }}</div>
                                        @php
                                        $displayUnit = \App\Models\Setting::getItemUnit($keyword);
                                        // Calculate given out qty using word boundaries
                                        $query = \App\Models\StoreRequisitionItem::join('store_requisitions', 'store_requisition_items.requisition_id', '=', 'store_requisitions.id')
                                        ->whereIn('store_requisitions.status', ['approved', 'partially_approved']);
                                        $items = $query->select(
                                        'store_requisition_items.description',
                                        'store_requisition_items.quantity_approved',
                                        'store_requisition_items.alternative_description',
                                        'store_requisition_items.alternative_quantity_approved'
                                        )->get();

                                        $pattern = '/\b' . preg_quote(strtolower(trim($keyword)), '/') . '\b/i';
                                        $originalSum = 0.0;
                                        $alternativeSum = 0.0;

                                        foreach ($items as $dbItem) {
                                        if ($dbItem->description && preg_match($pattern, $dbItem->description)) {
                                        $originalSum += (float) $dbItem->quantity_approved;
                                        }
                                        if ($dbItem->alternative_description && preg_match($pattern, $dbItem->alternative_description)) {
                                        $alternativeSum += (float) $dbItem->alternative_quantity_approved;
                                        }
                                        }
                                        $givenOut = $originalSum + $alternativeSum;
                                        @endphp
                                        <div style="font-size: 0.7rem; font-weight: 700; color: #64748b;">Limit: {{ $limit }} {{ $displayUnit }}</div>
                                        <div style="font-size: 0.65rem; font-weight: 600; color: {{ $givenOut >= $limit ? 'var(--danger-color)' : 'var(--success-color)' }}; margin-top: 2px;">Given Out: {{ $givenOut }} / {{ $limit }}</div>
                                    </div>
                                    <div style="display: flex; gap: 4px;">
                                        <button type="button" onclick="populateLimitForm('{{ $keyword }}', {{ $limit }}, '{{ $catCode }}')" style="background: none; border: none; color: #cbd5e1; cursor: pointer; transition: 0.2s; padding: 2px; display: flex; align-items: center;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#cbd5e1'" title="Edit Rule">
                                            <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i>
                                        </button>
                                        <form action="{{ route('admin.settings.request-limit.destroy') }}" method="POST" onsubmit="return confirm('Remove request limit for \'{{ $keyword }}\'?');" style="margin: 0;">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="keyword" value="{{ $keyword }}">
                                            <button type="submit" style="background: none; border: none; color: #cbd5e1; cursor: pointer; transition: 0.2s; padding: 2px; display: flex; align-items: center;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#cbd5e1'" title="Remove Limit">
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

                {{-- Add New Limit --}}
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
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Item Keyword(s)</label>
                                <select name="keywords[]" id="limitKeyword" class="cfg-text-input select2-limit" required multiple="multiple">
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
                                <button type="submit" id="limitSubmitBtn" class="btn-cfg-add" style="flex: 1; background: linear-gradient(135deg,#881337,#881337); box-shadow: 0 6px 16px rgba(136,19,55,0.25);">
                                    <i data-lucide="plus-circle" id="limitSubmitIcon"></i> <span id="limitSubmitText">Add Limit</span>
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


</div>

<script>
    const isHeadOfStores = {{ auth()->user()->role === 'Head of Stores' ? 'true' : 'false' }};
    const itemsByCategory = @json($itemsByCategory ?? []);
    const stockByKeyword = @json($stockByKeyword ?? []);



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

    function filterCategories() {
        const input = document.getElementById('categorySearch');
        const term = input ? input.value.toLowerCase().trim() : '';
        const badges = document.querySelectorAll('#category-configs .cat-badge');
        let visibleCount = 0;
        
        badges.forEach(badge => {
            const codeEl = badge.querySelector('.cat-code-pill');
            const nameEl = badge.querySelector('.cat-name');
            const code = codeEl ? codeEl.textContent.toLowerCase() : '';
            const name = nameEl ? nameEl.textContent.toLowerCase() : '';
            
            if (code.includes(term) || name.includes(term)) {
                badge.style.display = '';
                visibleCount++;
            } else {
                badge.style.display = 'none';
            }
        });

        const noResults = document.getElementById('noCategoriesFound');
        if (noResults) {
            noResults.style.display = (visibleCount === 0 && term !== '') ? '' : 'none';
        }
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
                keywordSelect.val([keyword]).trigger('change');
            } else {
                // If tag is allowed, we can add it
                var newOption = new Option(keyword, keyword, true, true);
                keywordSelect.append(newOption).val([keyword]).trigger('change');
            }
        }, 100);

        // Update button UI
        document.getElementById('thresholdSubmitText').innerText = 'Update';
        document.getElementById('thresholdSubmitBtn').style.background = 'linear-gradient(135deg, #881337, #3730a3)';
        document.getElementById('thresholdResetBtn').style.display = 'block';
        const icon = document.getElementById('thresholdSubmitIcon');
        icon.setAttribute('data-lucide', 'refresh-cw');
        if (window.lucide) lucide.createIcons();

        // Scroll to form
        document.getElementById('threshold-rules').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function resetThresholdForm() {
        document.querySelector('#threshold-rules form').reset();
        $('#thresholdKeyword').val(null).trigger('change');
        document.getElementById('thresholdSubmitText').innerText = 'Add Threshold';
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
            keywordSelect.val([keyword]).trigger('change.select2');
            // Manually show the stock panel since we're not using select2:select here
            if (typeof showLimitStock === 'function') showLimitStock(keyword);
        }, 100);

        document.getElementById('limitVal').value = limit;
        document.getElementById('limitSubmitText').innerText = 'Update Limit';
        document.getElementById('limitSubmitBtn').style.background = 'linear-gradient(135deg, #881337, #881337)';
        document.getElementById('limitResetBtn').style.display = 'inline-block';
        const icon = document.getElementById('limitSubmitIcon');
        icon.setAttribute('data-lucide', 'refresh-cw');
        if (window.lucide) lucide.createIcons();
        document.getElementById('request-limits').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function resetLimitForm() {
        document.getElementById('limitFormTitle').innerText = 'Add Request Limit';
        document.getElementById('limitForm').reset();
        $('#limitKeyword').val(null).trigger('change.select2');
        document.getElementById('limitStockInfo').style.display = 'none';
        document.getElementById('limitSubmitText').innerText = 'Add Limit';
        document.getElementById('limitSubmitBtn').style.background = 'linear-gradient(135deg,#881337,#881337)';
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

        // Initialize interactive category cards and flowchart state
        if (typeof updateWorkflowFlowchart === 'function') {
            updateWorkflowFlowchart();
        }



        // Initialize Select2 for thresholds
        $('.select2-threshold').select2({
            tags: true,
            placeholder: 'Select or type a new item...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#threshold-rules')
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
            placeholder: 'Select or type one or more items...',
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

        // Handle unselecting or clearing
        $('#limitKeyword').on('change', function() {
            const selected = $(this).val();
            if (!selected || selected.length === 0) {
                document.getElementById('limitStockInfo').style.display = 'none';
            } else {
                // Show stock for the last selected item in the list
                const lastSelected = selected[selected.length - 1];
                showLimitStock(lastSelected);
            }
        });

        // Validate request limit against stock balance on submit
        $('#limitForm').on('submit', function(e) {
            const limit = parseFloat(document.getElementById('limitVal').value) || 0;
            const keywords = $('#limitKeyword').val() || [];

            for (let i = 0; i < keywords.length; i++) {
                const keyword = keywords[i];
                let stock = null;
                const kwClean = keyword.toLowerCase().trim();

                if (stockByKeyword[kwClean] !== undefined) {
                    stock = stockByKeyword[kwClean];
                } else {
                    for (const [k, v] of Object.entries(stockByKeyword)) {
                        if (k.includes(kwClean) || kwClean.includes(k)) {
                            stock = v;
                            break;
                        }
                    }
                }

                if (stock !== null && limit > stock) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Limit Exceeds Stock Balance',
                        text: `The request limit (${limit}) cannot exceed the current stock balance of "${keyword}" (${stock} units).`,
                        confirmButtonColor: '#881337'
                    });
                    return false;
                }
            }
        });
    });

    // Global: show stock balance for a given keyword in the Add Request Limit form
    function showLimitStock(keyword) {
        keyword = (keyword || '').toLowerCase().trim();
        const infoPanel = document.getElementById('limitStockInfo');
        if (!keyword) {
            infoPanel.style.display = 'none';
            return;
        }

        // Find best match: exact first, then substring
        let stock = null;
        if (stockByKeyword[keyword] !== undefined) {
            stock = stockByKeyword[keyword];
        } else {
            for (const [k, v] of Object.entries(stockByKeyword)) {
                if (k.includes(keyword) || keyword.includes(k)) {
                    stock = v;
                    break;
                }
            }
        }

        const iconEl = document.getElementById('limitStockIcon');
        const valEl = document.getElementById('limitStockValue');
        const badgeEl = document.getElementById('limitStockBadge');
        const hintEl = document.getElementById('limitStockHint');

        if (stock === null) {
            iconEl.style.background = '#e2e8f0';
            valEl.textContent = 'No stock data found';
            badgeEl.textContent = 'Unknown';
            badgeEl.style.background = '#f1f5f9';
            badgeEl.style.color = '#64748b';
            hintEl.textContent = 'No inventory records match this keyword. The limit will still be applied once set.';
        } else if (stock <= 0) {
            iconEl.style.background = 'linear-gradient(135deg,#ef4444,#dc2626)';
            valEl.textContent = '0 units';
            badgeEl.textContent = 'Out of Stock';
            badgeEl.style.background = '#fee2e2';
            badgeEl.style.color = '#dc2626';
            hintEl.textContent = 'This item is currently out of stock. Setting a limit here will keep it as Unavailable on the dashboard.';
        } else if (stock <= 5) {
            iconEl.style.background = '#881337';
            valEl.textContent = stock + ' units available';
            badgeEl.textContent = 'Very Low';
            badgeEl.style.background = '#fef3c7';
            badgeEl.style.color = '#047857';
            hintEl.textContent = 'Stock is critically low. Consider setting a conservative limit to preserve remaining supply.';
        } else if (stock <= 20) {
            iconEl.style.background = 'linear-gradient(135deg,#eab308,#ca8a04)';
            valEl.textContent = stock + ' units available';
            badgeEl.textContent = 'Low';
            badgeEl.style.background = '#fefce8';
            badgeEl.style.color = '#ca8a04';
            hintEl.textContent = 'Stock is running low. Set a limit that reflects the quantity you wish to make available for requisitions.';
        } else {
            iconEl.style.background = '#881337';
            valEl.textContent = stock + ' units available';
            badgeEl.textContent = 'In Stock';
            badgeEl.style.background = '#dcfce7';
            badgeEl.style.color = '#881337';
            hintEl.textContent = 'Sufficient stock available. Set your desired request limit and it will be enforced on the requisition dashboard.';
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



    function toggleWorkflowCategory(code, card) {
        const select = document.getElementById('stores_dept_head_approval_categories');
        const option = select.querySelector(`option[value="${code}"]`);

        if (!option) return;

        const isCurrentlyActive = card.classList.contains('active');

        if (isCurrentlyActive) {
            // Deactivate
            card.classList.remove('active');

            const label = card.querySelector('.status-label');
            if (label) {
                label.textContent = 'Bypasses Stores Head';
            }

            const dot = card.querySelector('.indicator-dot');
            if (dot) {
                const checkIcon = dot.querySelector('i, svg');
                if (checkIcon) checkIcon.style.display = 'none';
            }

            option.selected = false;
        } else {
            // Activate
            card.classList.add('active');

            const label = card.querySelector('.status-label');
            if (label) {
                label.textContent = 'Requires Stores Head';
            }

            const dot = card.querySelector('.indicator-dot');
            if (dot) {
                const checkIcon = dot.querySelector('i, svg');
                if (checkIcon) checkIcon.style.display = 'block';
            }

            option.selected = true;
        }

        // Trigger change event on select to ensure any listeners match
        select.dispatchEvent(new Event('change'));

        // Update the visual flowchart in real-time
        updateWorkflowFlowchart();
    }

    function toggleDGWorkflowCategory(code, card) {
        const select = document.getElementById('dg_approval_categories');
        const option = select.querySelector(`option[value="${code}"]`);

        if (!option) return;

        const isCurrentlyActive = card.classList.contains('active');

        if (isCurrentlyActive) {
            // Deactivate
            card.classList.remove('active');

            const label = card.querySelector('.status-label');
            if (label) {
                label.textContent = 'Bypasses DG';
            }

            const dot = card.querySelector('.indicator-dot');
            if (dot) {
                const checkIcon = dot.querySelector('i, svg');
                if (checkIcon) checkIcon.style.display = 'none';
            }

            option.selected = false;
        } else {
            // Activate
            card.classList.add('active');

            const label = card.querySelector('.status-label');
            if (label) {
                label.textContent = 'Requires DG';
            }

            const dot = card.querySelector('.indicator-dot');
            if (dot) {
                const checkIcon = dot.querySelector('i, svg');
                if (checkIcon) checkIcon.style.display = 'block';
            }

            option.selected = true;
        }

        // Trigger change event on select to ensure any listeners match
        select.dispatchEvent(new Event('change'));

        // Update the visual flowchart in real-time
        updateWorkflowFlowchart();
    }

    function updateWorkflowFlowchart() {
        const selectStores = document.getElementById('stores_dept_head_approval_categories');
        const activeCountStores = 1; // Head of Admin is always required

        const selectDG = document.getElementById('dg_approval_categories');
        const activeCountDG = selectDG ? Array.from(selectDG.selectedOptions).length : 0;

        // Update HOD header badge
        const badgeTextStores = document.getElementById('workflow-badge-text');
        const badgeDotStores = document.getElementById('workflow-badge-dot');
        const badgeContainerStores = document.getElementById('workflow-active-badge');
        if (badgeTextStores) badgeTextStores.textContent = `Active Categories: ${activeCountStores}`;
        if (activeCountStores > 0) {
            if (badgeDotStores) badgeDotStores.style.background = '#881337';
            if (badgeContainerStores) {
                badgeContainerStores.style.background = 'rgba(136, 19, 55, 0.08)';
                badgeContainerStores.style.color = '#881337';
                badgeContainerStores.style.borderColor = 'rgba(136, 19, 55, 0.2)';
            }
        } else {
            if (badgeDotStores) badgeDotStores.style.background = '#64748b';
            if (badgeContainerStores) {
                badgeContainerStores.style.background = 'rgba(100, 116, 139, 0.08)';
                badgeContainerStores.style.color = '#64748b';
                badgeContainerStores.style.borderColor = 'rgba(100, 116, 139, 0.2)';
            }
        }

        // Update DG header badge
        const badgeTextDG = document.getElementById('dg-workflow-badge-text');
        const badgeDotDG = document.getElementById('dg-workflow-badge-dot');
        const badgeContainerDG = document.getElementById('dg-workflow-active-badge');
        if (badgeTextDG) badgeTextDG.textContent = `Active Categories: ${activeCountDG}`;
        if (activeCountDG > 0) {
            if (badgeDotDG) badgeDotDG.style.background = '#9f1239';
            if (badgeContainerDG) {
                badgeContainerDG.style.background = 'rgba(139, 92, 246, 0.08)';
                badgeContainerDG.style.color = '#9f1239';
                badgeContainerDG.style.borderColor = 'rgba(139, 92, 246, 0.2)';
            }
        } else {
            if (badgeDotDG) badgeDotDG.style.background = '#64748b';
            if (badgeContainerDG) {
                badgeContainerDG.style.background = 'rgba(100, 116, 139, 0.08)';
                badgeContainerDG.style.color = '#64748b';
                badgeContainerDG.style.borderColor = 'rgba(100, 116, 139, 0.2)';
            }
        }

        // Update ALL Stores Head flow nodes
        document.querySelectorAll('.flow-node-stores').forEach(node => {
            const iconBox = node.querySelector('.flow-node-icon');
            const label = node.querySelector('.flow-node-label');
            const badge = node.querySelector('.flow-node-badge');

            if (activeCountStores > 0) {
                node.className = 'flow-node flow-node-stores active';
                if (iconBox) {
                    iconBox.style.background = 'linear-gradient(135deg, #881337, #3730a3)';
                    iconBox.style.color = '#ffffff';
                    iconBox.style.borderColor = 'transparent';
                    iconBox.style.boxShadow = '0 6px 15px rgba(136,19,55,0.2)';
                }
                if (label) {
                    label.style.color = '#1e293b';
                    label.style.textDecoration = 'none';
                }
                if (badge) {
                    badge.textContent = 'Required';
                    badge.style.background = 'rgba(136, 19, 55, 0.1)';
                    badge.style.color = '#881337';
                    badge.style.borderColor = 'transparent';
                }
            } else {
                node.className = 'flow-node flow-node-stores bypass';
                if (iconBox) {
                    iconBox.style.background = '#f8fafc';
                    iconBox.style.color = '#64748b';
                    iconBox.style.borderColor = '#cbd5e1';
                    iconBox.style.boxShadow = 'none';
                }
                if (label) {
                    label.style.color = '#94a3b8';
                    label.style.textDecoration = 'line-through';
                }
                if (badge) {
                    badge.textContent = 'Bypassed';
                    badge.style.background = '#fef2f2';
                    badge.style.color = '#ef4444';
                    badge.style.borderColor = 'rgba(239, 68, 68, 0.1)';
                }
            }
        });

        // Update ALL DG flow nodes
        document.querySelectorAll('.flow-node-dg').forEach(node => {
            const iconBox = node.querySelector('.flow-node-icon');
            const label = node.querySelector('.flow-node-label');
            const badge = node.querySelector('.flow-node-badge');

            if (activeCountDG > 0) {
                node.className = 'flow-node flow-node-dg active';
                if (iconBox) {
                    iconBox.style.background = 'linear-gradient(135deg, #9f1239, #6d28d9)';
                    iconBox.style.color = '#ffffff';
                    iconBox.style.borderColor = 'transparent';
                    iconBox.style.boxShadow = '0 6px 15px rgba(139,92,246,0.2)';
                }
                if (label) {
                    label.style.color = '#1e293b';
                    label.style.textDecoration = 'none';
                }
                if (badge) {
                    badge.textContent = 'Required';
                    badge.style.background = 'rgba(139, 92, 246, 0.1)';
                    badge.style.color = '#9f1239';
                    badge.style.borderColor = 'transparent';
                }
            } else {
                node.className = 'flow-node flow-node-dg bypass';
                if (iconBox) {
                    iconBox.style.background = '#f8fafc';
                    iconBox.style.color = '#64748b';
                    iconBox.style.borderColor = '#cbd5e1';
                    iconBox.style.boxShadow = 'none';
                }
                if (label) {
                    label.style.color = '#94a3b8';
                    label.style.textDecoration = 'line-through';
                }
                if (badge) {
                    badge.textContent = 'Bypassed';
                    badge.style.background = '#fef2f2';
                    badge.style.color = '#ef4444';
                    badge.style.borderColor = 'rgba(239, 68, 68, 0.1)';
                }
            }
        });

        // Update lines
        document.querySelectorAll('.flow-line-1').forEach(line => {
            if (activeCountStores > 0) {
                line.className = 'flow-line flow-line-1 active';
                line.style.background = '#881337';
            } else {
                line.className = 'flow-line flow-line-1 dashed';
                line.style.background = '';
            }
        });

        document.querySelectorAll('.flow-line-2').forEach(line => {
            if (activeCountDG > 0) {
                line.className = 'flow-line flow-line-2 active';
                line.style.background = '#9f1239';
            } else {
                line.className = 'flow-line flow-line-2 dashed';
                line.style.background = '';
            }
        });

        document.querySelectorAll('.flow-line-3').forEach(line => {
            // Since Head of Stores is always required:
            line.className = 'flow-line flow-line-3 active';
            line.style.background = '#881337';
        });

        // Update hints
        document.querySelectorAll('.workflow-helper-hint').forEach(hint => {
            const isStoresCard = hint.closest('.workflow-card-modern').querySelector('h3').textContent.includes('Stores');
            if (isStoresCard) {
                if (activeCountStores > 0) {
                    hint.innerHTML = `Routing through <strong>Head of Admin</strong> for <strong style="color: #881337;">${activeCountStores}</strong> selected category${activeCountStores == 1 ? '' : 'ies'}.`;
                } else {
                    hint.innerHTML = 'Currently bypassing intermediate Stores Head step due to settings configuration.';
                }
            } else {
                if (activeCountDG > 0) {
                    hint.innerHTML = `Routing through <strong>Director General</strong> for <strong style="color: #9f1239;">${activeCountDG}</strong> selected category${activeCountDG == 1 ? '' : 'ies'}.`;
                } else {
                    hint.innerHTML = 'Currently bypassing intermediate Director General step due to settings configuration.';
                }
            }
        });
    }






    function populateCategoryForm(code, name) {
        document.getElementById('categoryFormTitle').innerText = 'Update Category';
        document.getElementById('categoryFormSub').innerText = 'Update the category details.';
        
        const form = document.getElementById('categoryForm');
        form.action = `{{ url('/admin/settings/category') }}/${code}/update`;

        const codeInput = document.getElementById('categoryCodeInput');
        codeInput.value = code;
        codeInput.readOnly = true;
        codeInput.style.background = '#f8fafc';
        
        document.getElementById('categoryNameInput').value = name;
        
        document.getElementById('categorySubmitText').innerText = 'Update Category';
        document.getElementById('categorySubmitBtn').style.background = '#881337';
        document.getElementById('categorySubmitBtn').style.boxShadow = '0 6px 16px rgba(136, 19, 55, 0.25)';
        
        document.getElementById('categoryResetBtn').style.display = 'block';

        const icon = document.getElementById('categorySubmitIcon');
        icon.setAttribute('data-lucide', 'refresh-cw');
        if (window.lucide) lucide.createIcons();

        document.getElementById('category-configs').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function resetCategoryForm() {
        document.getElementById('categoryFormTitle').innerText = 'Add New Category';
        document.getElementById('categoryFormSub').innerText = 'Add a new ledger code for inventory tracking.';
        
        const form = document.getElementById('categoryForm');
        form.action = `{{ route('admin.settings.category') }}`;

        const codeInput = document.getElementById('categoryCodeInput');
        codeInput.value = '';
        codeInput.readOnly = false;
        codeInput.style.background = '';
        
        document.getElementById('categoryNameInput').value = '';
        
        document.getElementById('categorySubmitText').innerText = 'Add Category';
        document.getElementById('categorySubmitBtn').style.background = '';
        document.getElementById('categorySubmitBtn').style.boxShadow = '';
        
        document.getElementById('categoryResetBtn').style.display = 'none';

        const icon = document.getElementById('categorySubmitIcon');
        icon.setAttribute('data-lucide', 'plus-circle');
        if (window.lucide) lucide.createIcons();
    }
</script>
@endsection
