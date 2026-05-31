@extends('layouts.admin')
@section('content')
<style>
    .req-stat-card {
        background: var(--bg-card);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .req-table-row {
        border-bottom: 1px solid var(--border-color);
        transition: .15s;
    }

    .req-table-row:hover {
        background: rgba(99, 102, 241, .03);
    }

    .req-table-row:last-child {
        border-bottom: none;
    }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 99px;
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(6px);
        z-index: 3000;
        display: none;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .swal2-container {
        z-index: 99999 !important;
    }

    .modal-overlay.open {
        display: flex;
    }

    .modal-box {
        background: var(--bg-card);
        border-radius: 24px;
        width: 100%;
        max-width: 920px;
        max-height: 94vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 30px 80px rgba(15, 23, 42, 0.22);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 2.25rem;
        scroll-behavior: smooth;
    }

    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: transparent;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 99px;
    }

    .modal-body:hover::-webkit-scrollbar-thumb {
        background: var(--text-muted);
        opacity: 0.6;
    }

    @keyframes fadeInModal {
        from {
            opacity: 0;
            transform: scale(.96) translateY(10px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .modal-box {
        animation: fadeInModal .35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Priority-specific visual accents */
    .modal-box.urgent-priority {
        border-top: 6px solid #dc2626;
    }

    .modal-box.normal-priority {
        border-top: 6px solid #4f46e5;
    }

    .modal-box.low-priority {
        border-top: 6px solid #64748b;
    }

    /* Horizontal Stepper Timeline */
    .stepper-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        margin-bottom: 2rem;
        background: var(--bg-main);
        padding: 1.25rem 2rem;
        border-radius: 16px;
        border: 1px solid var(--border-color);
    }

    .stepper-line {
        position: absolute;
        top: 50%;
        left: 4rem;
        right: 4rem;
        height: 3px;
        background: var(--border-color);
        z-index: 1;
        transform: translateY(-50%);
    }

    .stepper-progress {
        position: absolute;
        top: 50%;
        left: 4rem;
        height: 3px;
        background: linear-gradient(90deg, var(--primary) 0%, #10b981 100%);
        z-index: 1;
        transform: translateY(-50%);
        transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        width: 33%;
    }

    .stepper-step {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: transform 0.25s ease;
    }

    .stepper-step:hover {
        transform: translateY(-2px);
    }

    .stepper-bubble {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--bg-card);
        border: 3px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.85rem;
        color: var(--text-muted);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    }

    .stepper-label {
        font-size: 0.72rem;
        font-weight: 900;
        color: var(--text-muted);
        margin-top: 8px;
        transition: color 0.3s;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .stepper-step.completed .stepper-bubble {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-color: #10b981;
        color: white;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
    }

    .stepper-step.completed .stepper-label {
        color: #10b981;
    }

    @keyframes activePulse {
        0% {
            box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4);
        }

        70% {
            box-shadow: 0 0 0 8px rgba(79, 70, 229, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(79, 70, 229, 0);
        }
    }

    .stepper-step.active .stepper-bubble {
        background: linear-gradient(135deg, var(--primary) 0%, #4338ca 100%);
        border-color: var(--primary);
        color: white;
        animation: activePulse 2s infinite;
    }

    .stepper-step.active .stepper-label {
        color: var(--primary);
    }

    .stepper-step.declined-step .stepper-bubble {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border-color: #ef4444;
        color: white;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);
    }

    .stepper-step.declined-step .stepper-label {
        color: #ef4444;
    }

    /* Responsive Vertical Stepper for Mobile viewports */
    @media (max-width: 640px) {
        .stepper-container {
            flex-direction: column;
            align-items: flex-start;
            gap: 1.75rem;
            padding: 1.5rem 1.5rem 1.5rem 2rem;
        }

        .stepper-line {
            top: 1.5rem;
            bottom: 1.5rem;
            left: 3.2rem;
            width: 3px;
            height: calc(100% - 3rem);
            right: auto;
            transform: translateX(-50%);
        }

        .stepper-progress {
            top: 1.5rem;
            left: 3.2rem;
            width: 3px;
            right: auto;
            transform: translateX(-50%);
            transition: height 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .stepper-step {
            flex-direction: row;
            align-items: center;
            text-align: left;
            gap: 1.15rem;
            width: 100%;
        }

        .stepper-step:hover {
            transform: translateX(3px);
        }

        .stepper-label {
            margin-top: 0;
            font-size: 0.75rem;
        }
    }

    /* Profile Panel & Grid */
    .profile-card {
        display: flex;
        align-items: center;
        gap: 14px;
        background: var(--bg-main);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.15rem;
        transition: all 0.25s ease;
    }

    .profile-card:hover {
        border-color: rgba(79, 70, 229, 0.25);
        background: rgba(99, 102, 241, 0.02);
        transform: translateY(-1px);
    }

    .profile-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: var(--primary-glow);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.25rem;
        border: 1.5px solid rgba(79, 70, 229, 0.15);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    }

    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.72rem;
        font-weight: 800;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
    }

    .purpose-quote {
        background: var(--bg-main);
        border-left: 4px solid var(--primary);
        border-radius: 4px 16px 16px 4px;
        padding: 1.25rem 1.5rem;
        font-size: 0.88rem;
        color: var(--text-main);
        line-height: 1.6;
        font-style: italic;
        position: relative;
    }

    .purpose-quote:before {
        content: '“';
        font-size: 3.5rem;
        color: rgba(79, 70, 229, 0.08);
        position: absolute;
        top: -0.8rem;
        left: 0.5rem;
        font-family: Georgia, serif;
    }

    /* Custom iOS Switch Toggle */
    .switch-wrapper {
        display: inline-flex;
        align-items: center;
    }

    .switch-input {
        display: none;
    }

    .switch-label {
        position: relative;
        display: block;
        width: 44px;
        height: 24px;
        background: #cbd5e1;
        border-radius: 99px;
        cursor: pointer;
        transition: background 0.25s ease;
    }

    .switch-label:after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 20px;
        height: 20px;
        background: white;
        border-radius: 50%;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), width 0.15s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);
    }

    .switch-input:checked+.switch-label {
        background: #10b981;
    }

    .switch-input:checked+.switch-label:after {
        transform: translateX(20px);
    }

    .switch-label:active:after {
        width: 24px;
    }

    /* Custom Quantity Spinners */
    .qty-spinner {
        display: inline-flex;
        align-items: center;
        background: var(--bg-main);
        border: 1.5px solid var(--border-color);
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.25s ease;
    }

    .qty-spinner:focus-within {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-glow);
        background: var(--bg-card);
    }

    .qty-btn {
        background: none;
        border: none;
        width: 28px;
        height: 32px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        font-weight: 900;
        font-size: 0.85rem;
        transition: background 0.15s;
        user-select: none;
    }

    .qty-btn:hover {
        background: rgba(0, 0, 0, 0.04);
        color: var(--text-main);
    }

    .qty-spinner input {
        width: 54px;
        border: none;
        background: none;
        text-align: center;
        font-weight: 800;
        color: var(--text-main);
        font-size: 0.88rem;
        padding: 0;
        outline: none;
    }

    .qty-spinner input::-webkit-outer-spin-button,
    .qty-spinner input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .qty-spinner input[type=number] {
        -moz-appearance: textfield;
    }

    /* Item decision row/card */
    .item-decision-card {
        border-bottom: 1px solid var(--border-color);
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        transition: all 0.2s ease;
        background: var(--bg-card);
    }

    .item-decision-card:last-child {
        border-bottom: none;
    }

    .item-decision-card.declined-row {
        background: rgba(239, 68, 68, 0.015);
    }

    .item-decision-card.approved-row {
        background: rgba(16, 185, 129, 0.008);
    }

    .item-decision-card:hover {
        background: rgba(99, 102, 241, 0.012);
    }

    .item-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        width: 100%;
    }

    .item-card-header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
        min-width: 260px;
    }

    .item-card-header-right {
        text-align: right;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .item-card-panel {
        background: var(--bg-main);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
        border: 1px solid var(--border-color);
        width: 100%;
        box-sizing: border-box;
    }

    .item-card-spinner-box {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .item-card-status-box {
        flex: 1;
        min-width: 260px;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }


    /* Quick Remarks Pills */
    .quick-tag {
        background: var(--bg-main);
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.65rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-block;
        margin-right: 4px;
        margin-top: 4px;
        user-select: none;
    }

    .quick-tag:hover {
        background: var(--primary-glow);
        color: var(--primary);
        border-color: var(--primary);
    }

    /* Live Summary Board */
    .summary-dashboard {
        background: linear-gradient(135deg, var(--bg-main) 0%, rgba(99, 102, 241, 0.02) 100%);
        border: 1.5px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.01);
    }

    .summary-metrics {
        display: flex;
        gap: 2rem;
    }

    .metric-box {
        display: flex;
        flex-direction: column;
    }

    .metric-val {
        font-size: 1.45rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .metric-lbl {
        font-size: 0.68rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-top: 2px;
    }

    /* Visual Progress Fulfill Bar */
    .fulfill-progress-container {
        width: 100%;
        background: var(--border-color);
        height: 6px;
        border-radius: 99px;
        overflow: hidden;
        margin-top: 6px;
    }

    .fulfill-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%);
        border-radius: 99px;
        transition: width 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .fulfill-ratio-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 6px;
        border-radius: 6px;
        font-size: 0.68rem;
        font-weight: 800;
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .fulfill-ratio-badge.reduced {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .fulfill-ratio-badge.declined {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    /* Status Bar styling */
    #statusBar {
        padding: 1rem 1.25rem;
        border-radius: 14px;
        display: flex;
        align-items: center;
        gap: .75rem;
        font-weight: 800;
        font-size: .88rem;
        margin-bottom: 1.5rem;
        transition: all .3s ease;
    }

    #statusBar.all-approved {
        background: rgba(16, 185, 129, .12);
        color: #065f46;
        border: 1px solid rgba(16, 185, 129, .25);
    }

    #statusBar.partial {
        background: rgba(245, 158, 11, .12);
        color: #92400e;
        border: 1px solid rgba(245, 158, 11, .25);
    }

    #statusBar.all-declined {
        background: rgba(239, 68, 68, .1);
        color: #991b1b;
        border: 1px solid rgba(239, 68, 68, .2);
    }

    .reason-input {
        width: 100%;
        padding: .6rem .8rem;
        border: 1.5px solid var(--border-color);
        border-radius: 10px;
        font-family: inherit;
        font-size: .8rem;
        background: var(--bg-main);
        color: var(--text-main);
        resize: vertical;
        box-sizing: border-box;
    }

    .reason-input:focus {
        border-color: var(--primary);
        outline: none;
        background: var(--bg-card);
    }

    .qty-input {
        width: 80px;
        padding: .4rem .65rem;
        border: 1.5px solid var(--border-color);
        border-radius: 8px;
        font-weight: 800;
        font-size: .85rem;
        text-align: right;
        background: var(--bg-main);
        color: var(--text-main);
    }

    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 4px;
    }

    /* Alternative item select styling */
    .alternative-item-select {
        transition: all 0.25s ease !important;
    }
    .alternative-item-select:hover {
        border-color: var(--store-orange) !important;
        background: var(--bg-card) !important;
    }
    .alternative-item-select:focus {
        border-color: var(--store-orange) !important;
        background: var(--bg-card) !important;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.12) !important;
    }

    /* Modern Premium Filter Card Section */
    .filter-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.04), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .filter-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.75rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.25rem;
    }

    .filter-row {
        display: flex;
        gap: 0.85rem;
        flex-wrap: wrap;
        align-items: center;
        width: 100%;
        margin: 0;
    }

    .filter-field-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .filter-icon {
        position: absolute;
        left: 14px;
        color: var(--text-muted);
        pointer-events: none;
        transition: color 0.2s ease;
    }

    .filter-control {
        width: 100%;
        padding: 0.7rem 1rem 0.7rem 2.6rem;
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-main);
        color: var(--text-main);
        font-family: inherit;
        font-weight: 600;
        font-size: 0.85rem;
        outline: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
    }

    select.filter-control {
        padding-right: 2.25rem;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19.5 8.25l-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 14px;
    }

    .filter-control:focus {
        border-color: #4f46e5;
        background: var(--bg-card);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
    }

    .filter-control:focus + .filter-icon {
        color: #4f46e5;
    }

    .filter-control::placeholder {
        color: var(--text-muted);
        opacity: 0.75;
    }

    .filter-clear-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0.7rem 1.25rem;
        border: 1.5px solid #ef4444;
        border-radius: 12px;
        background: rgba(239, 68, 68, 0.05);
        color: #ef4444;
        font-weight: 800;
        font-size: 0.82rem;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .filter-clear-btn:hover {
        background: #ef4444;
        color: white;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
    }
</style>

<div style="padding:2rem;">

    {{-- Header --}}
    <div style="margin-bottom:2rem;">
        <div style="font-size:.7rem;font-weight:800;color:#4f46e5;text-transform:uppercase;letter-spacing:.12em;margin-bottom:4px;">Store Management</div>
        <h1 style="font-size:1.75rem;font-weight:900;color:var(--text-main);letter-spacing:-.03em;margin:0;">Store Requisitions</h1>
        <p style="font-size:.9rem;color:var(--text-muted);margin:6px 0 0;">Review and process department item requests</p>
    </div>

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:2rem;">
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(99,102,241,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="clock" style="width:20px;color:#6366f1;"></i></div>
            <div>
                <div style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['pending'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Pending</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(220,38,38,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="alert-triangle" style="width:20px;color:#dc2626;"></i></div>
            <div>
                <div style="font-size:1.5rem;font-weight:900;color:#dc2626;">{{ $stats['urgent'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Urgent</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(16,185,129,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="check-circle" style="width:20px;color:#10b981;"></i></div>
            <div>
                <div style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['approved'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Approved</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(245,158,11,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="git-merge" style="width:20px;color:#f59e0b;"></i></div>
            <div>
                <div style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['partially_approved'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Partial</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(239,68,68,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="x-circle" style="width:20px;color:#ef4444;"></i></div>
            <div>
                <div style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['declined'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Declined</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-card">
        <div class="filter-header">
            <i data-lucide="sliders-horizontal" style="width: 14px; height: 14px; color: #4f46e5;"></i>
            <span>Filter Options</span>
        </div>
        <form method="GET" class="filter-row" id="filter-form" action="{{ route('admin.requisitions') }}">
            <div class="filter-field-wrapper" style="flex: 1.2; min-width: 220px;">
                <i data-lucide="search" class="filter-icon" style="width: 16px; height: 16px;"></i>
                <input type="text" name="search_id" id="search_id_input" class="filter-control" value="{{ request('search_id') }}" placeholder="Search by ID or Item name..." autocomplete="off">
            </div>

            <div class="filter-field-wrapper" style="min-width: 160px; flex: 1;">
                <i data-lucide="activity" class="filter-icon" style="width: 14px; height: 14px;"></i>
                <select name="status" onchange="updateFilters()" class="filter-control">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status')==='pending'?'selected':'' }}>Pending</option>
                    <option value="approved" {{ request('status')==='approved'?'selected':'' }}>Approved</option>
                    <option value="partially_approved" {{ request('status')==='partially_approved'?'selected':'' }}>Partial</option>
                    <option value="declined" {{ request('status')==='declined'?'selected':'' }}>Declined</option>
                </select>
            </div>

            <div class="filter-field-wrapper" style="min-width: 160px; flex: 1;">
                <i data-lucide="alert-circle" class="filter-icon" style="width: 14px; height: 14px;"></i>
                <select name="priority" onchange="updateFilters()" class="filter-control">
                    <option value="">All Priorities</option>
                    <option value="urgent" {{ request('priority')==='urgent'?'selected':'' }}>Urgent</option>
                    <option value="normal" {{ request('priority')==='normal'?'selected':'' }}>Normal</option>
                    <option value="low" {{ request('priority')==='low'?'selected':'' }}>Low</option>
                </select>
            </div>

            <div class="filter-field-wrapper" style="flex: 1.2; min-width: 220px;">
                <i data-lucide="building" class="filter-icon" style="width: 15px; height: 15px;"></i>
                <input type="text" name="department" id="dept_input" value="{{ request('department') }}" placeholder="Filter by department..." class="filter-control" autocomplete="off">
            </div>

            @if(request()->anyFilled(['status','priority','department','search_id']))
            <a href="{{ route('admin.requisitions') }}" class="filter-clear-btn">
                <i data-lucide="x-circle" style="width:16px; height:16px;"></i>
                <span>Clear Filters</span>
            </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div id="requisitions-table-container" style="background:var(--bg-card);border-radius:20px;border:1px solid var(--border-color);overflow:hidden; transition: opacity 0.2s ease;">
        <table style="width:100%;border-collapse:collapse;">
            <thead style="background:var(--bg-main);">
                <tr>
                    <th style="padding:1rem 1.5rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Department / Requester</th>
                    <th style="padding:1rem 1.5rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Items</th>
                    <th style="padding:1rem 1.5rem;text-align:center;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Priority</th>
                    <th style="padding:1rem 1.5rem;text-align:center;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Status</th>
                    <th style="padding:1rem 1.5rem;text-align:center;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Collection</th>
                    <th style="padding:1rem 1.5rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Date</th>
                    <th style="padding:1rem 1.5rem;text-align:center;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requisitions as $req)
                @php $sb = $req->status_badge; $pb = $req->priority_badge; @endphp
                <tr class="req-table-row">
                    <td style="padding:1rem 1.5rem;">
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <div style="font-size:.9rem;font-weight:800;color:var(--text-main);">{{ $req->department }}</div>
                            @php $utb = $req->usage_type_badge; @endphp
                            <span class="pill" style="background:{{ $utb['bg'] }}; color:{{ $utb['color'] }}; font-size: 0.6rem; padding: 2px 6px; border-radius: 6px; font-weight:800; text-transform:none; letter-spacing:0;">{{ $utb['label'] }}</span>
                        </div>
                        <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;">{{ $req->requester_name }}{{ $req->rank_or_title ? ' · '.$req->rank_or_title : '' }}</div>
                    </td>
                    <td style="padding:1rem 1.5rem;">
                        <div style="display:flex;flex-wrap:wrap;gap:4px;">
                            @foreach($req->items->take(3) as $item)
                            <span style="font-size:.7rem;font-weight:700;color:var(--text-main);background:var(--bg-main);border:1px solid var(--border-color);padding:2px 8px;border-radius:6px;">
                                {{ Str::limit($item->description, 20) }} ({{ number_format($item->quantity_requested,0) }})
                            </span>
                            @endforeach
                            @if($req->items->count() > 3)
                            <span style="font-size:.7rem;font-weight:700;color:#4f46e5;background:rgba(79,70,229,.1);padding:2px 8px;border-radius:6px;">+{{ $req->items->count()-3 }} more</span>
                            @endif
                        </div>
                    </td>
                    <td style="padding:1rem 1.5rem;text-align:center;"><span class="pill" style="background:{{ $pb['bg'] }};color:{{ $pb['color'] }};">{{ $pb['label'] }}</span></td>
                    <td style="padding:1rem 1.5rem;text-align:center;"><span class="pill" style="background:{{ $sb['bg'] }};color:{{ $sb['color'] }};">● {{ $sb['label'] }}</span></td>
                    <td style="padding:1rem 1.5rem;text-align:center;">
                        @if(in_array($req->status, ['approved', 'partially_approved']))
                            @if($req->collected_at)
                                <span style="font-size:.78rem;color:#10b981;font-weight:800;display:inline-flex;align-items:center;gap:4px;" title="Collected on {{ $req->collected_at->format('d/m/y, H:i') }}{{ $req->collector ? ' by '.$req->collector->name : '' }}">
                                    <i data-lucide="check-circle" style="width:14px;"></i> Collected
                                </span>
                            @else
                                <span style="font-size:.78rem;color:#f59e0b;font-weight:800;display:inline-flex;align-items:center;gap:4px;">
                                    <i data-lucide="clock" style="width:14px;"></i> Awaiting Collection
                                </span>
                            @endif
                        @else
                            <span style="font-size:.75rem;color:var(--text-muted);font-style:italic;">—</span>
                        @endif
                    </td>
                    <td style="padding:1rem 1.5rem;font-size:.78rem;color:var(--text-muted);font-weight:600;">{{ $req->created_at->format('d/m/y') }}<br>{{ $req->created_at->format('H:i') }}</td>
                    <td style="padding:1rem 1.5rem;text-align:center;">
                        <button onclick="openRequisitionModal({{ $req->id }})"
                            style="background:rgba(79,70,229,.1);color:#4f46e5;border:none;padding:.5rem 1rem;border-radius:10px;font-weight:800;font-size:.78rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:.15s;" onmouseover="this.style.background='#4f46e5';this.style.color='white'" onmouseout="this.style.background='rgba(79,70,229,.1)';this.style.color='#4f46e5'">
                            <i data-lucide="eye" style="width:14px;"></i> Review
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding:3rem;text-align:center;color:var(--text-muted);">
                        <i data-lucide="inbox" style="width:32px;margin-bottom:.75rem;opacity:.3;"></i>
                        <p style="font-weight:700;color:var(--text-main);">No requisitions found</p>
                        <p style="font-size:.85rem;">Department requests will appear here.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($requisitions->hasPages())
        <div style="padding:1rem 1.5rem; border-top:1px solid var(--border-color); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem;">
            <div style="font-size:.75rem; font-weight:700; color:var(--text-muted);">
                Showing
                <span style="font-weight:900; color:var(--text-main);">{{ $requisitions->firstItem() }}</span>
                &ndash;
                <span style="font-weight:900; color:var(--text-main);">{{ $requisitions->lastItem() }}</span>
                of
                <span style="font-weight:900; color:var(--text-main);">{{ $requisitions->total() }}</span>
                requisitions
            </div>
            <div style="display:flex; align-items:center; gap:.35rem;">
                {{-- Previous --}}
                @if($requisitions->onFirstPage())
                <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:10px;background:var(--bg-main);border:1.5px solid var(--border-color);color:var(--text-muted);opacity:.45;cursor:not-allowed;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </span>
                @else
                <a href="{{ $requisitions->previousPageUrl() }}" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:10px;background:var(--bg-card);border:1.5px solid var(--border-color);color:var(--text-main);text-decoration:none;transition:.15s;" onmouseover="this.style.background='var(--primary)';this.style.color='white';this.style.borderColor='var(--primary)';" onmouseout="this.style.background='var(--bg-card)';this.style.color='var(--text-main)';this.style.borderColor='var(--border-color)';">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </a>
                @endif

                {{-- Page Numbers --}}
                @foreach($requisitions->getUrlRange(max(1, $requisitions->currentPage()-2), min($requisitions->lastPage(), $requisitions->currentPage()+2)) as $page => $url)
                    @if($page == $requisitions->currentPage())
                    <span style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 10px;border-radius:10px;background:var(--primary);color:white;font-weight:900;font-size:.82rem;border:1.5px solid var(--primary);box-shadow:0 4px 12px rgba(99,102,241,.3);">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 10px;border-radius:10px;background:var(--bg-card);color:var(--text-main);font-weight:700;font-size:.82rem;border:1.5px solid var(--border-color);text-decoration:none;transition:.15s;" onmouseover="this.style.background='rgba(99,102,241,.08)';this.style.borderColor='rgba(99,102,241,.3)';this.style.color='var(--primary)';" onmouseout="this.style.background='var(--bg-card)';this.style.borderColor='var(--border-color)';this.style.color='var(--text-main)';">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($requisitions->hasMorePages())
                <a href="{{ $requisitions->nextPageUrl() }}" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:10px;background:var(--bg-card);border:1.5px solid var(--border-color);color:var(--text-main);text-decoration:none;transition:.15s;" onmouseover="this.style.background='var(--primary)';this.style.color='white';this.style.borderColor='var(--primary)';" onmouseout="this.style.background='var(--bg-card)';this.style.color='var(--text-main)';this.style.borderColor='var(--border-color)';">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </a>
                @else
                <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:10px;background:var(--bg-main);border:1.5px solid var(--border-color);color:var(--text-muted);opacity:.45;cursor:not-allowed;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Review Modal --}}
<div class="modal-overlay" id="reqModal" onclick="if(event.target===this)closeModal()">
    <div class="modal-box">
        <div style="padding:1.5rem 2rem;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:1rem;">
                <div style="width:44px;height:44px;background:rgba(79,70,229,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="clipboard-list" style="width:20px;color:#4f46e5;"></i>
                </div>
                <div>
                    <h2 style="margin:0;font-size:1.1rem;font-weight:900;color:var(--text-main);">Requisition Review</h2>
                    <p id="modalSubtitle" style="margin:0;font-size:.8rem;color:var(--text-muted);font-weight:500;"></p>
                </div>
            </div>
            <button onclick="closeModal()" style="background:var(--bg-main);border:none;width:34px;height:34px;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="x" style="width:18px;color:var(--text-muted);"></i>
            </button>
        </div>
        <div class="modal-body" id="modalBody">
            <div style="text-align:center;padding:2rem;color:var(--text-muted);">Loading...</div>
        </div>
        <div id="modalFooter" style="padding:1.25rem 2rem;border-top:1px solid var(--border-color);display:flex;justify-content:flex-end;gap:.75rem;flex-shrink:0;"></div>
    </div>
</div>

<script>
    let currentReqId = null;
    let currentReqData = null;

    async function openRequisitionModal(id) {
        currentReqId = id;
        document.getElementById('reqModal').classList.add('open');
        document.getElementById('modalBody').innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted);"><div style="width:24px;height:24px;border:2px solid rgba(0,0,0,.1);border-top-color:var(--primary);border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 10px;"></div>Loading details...</div>';
        document.getElementById('modalFooter').innerHTML = '';
        document.getElementById('modalSubtitle').textContent = 'Loading...';

        const res = await fetch(`{{ url('/admin/requisitions') }}/${id}/show`);
        const data = await res.json();
        currentReqData = data;

        // Apply priority border accents
        const modalBox = document.querySelector('.modal-box');
        modalBox.className = 'modal-box'; // reset
        modalBox.classList.add(`${data.priority}-priority`);

        document.getElementById('modalSubtitle').textContent = `Requisition Ref: ${data.unique_id || ('REQ-' + String(data.id).padStart(5, '0'))}`;

        const isPending = data.status === 'pending';



        // ── 2. Profile Grid ──────────────────────────────────────────────
        const avatarLetter = data.requester_name ? data.requester_name.charAt(0).toUpperCase() : 'R';
        const totalItemsCount = data.items.length;
        const totalQtyRequested = data.items.reduce((sum, item) => sum + parseFloat(item.quantity_requested || 0), 0);

        let purposeText = data.purpose || '';
        let returnDateBannerHtml = '';
        const dateMatch = purposeText.match(/\[Expected Return Date:\s*([^\]]+)\]/i);
        if (dateMatch) {
            const rawDate = dateMatch[1].trim();
            let formattedDate = rawDate;
            try {
                const dateObj = new Date(rawDate);
                if (!isNaN(dateObj.getTime())) {
                    formattedDate = dateObj.toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' });
                }
            } catch(e) {}
            returnDateBannerHtml = `
            <div style="background:rgba(245, 158, 11, 0.06); border:1px solid rgba(245, 158, 11, 0.25); border-radius:12px; padding:0.85rem 1.15rem; display:flex; align-items:center; gap:10px; color:#d97706; font-weight:800; font-size:0.88rem; margin-top:0.5rem; margin-bottom:0.25rem; box-shadow:0 2px 8px rgba(245, 158, 11, 0.03); width:100%;">
                <i data-lucide="calendar-clock" style="width:16px; height:16px; color:#d97706; flex-shrink:0;"></i>
                <span>Expected Return Date: <strong style="color:#b45309; font-size:0.95rem; font-weight:950; text-decoration: underline;">${formattedDate}</strong></span>
            </div>`;
            purposeText = purposeText.replace(/\[Expected Return Date:\s*[^\]]+\]/i, '').trim();
        }

        const profileGridHtml = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.75rem;">
        <div class="profile-card">
            <div class="profile-avatar">${avatarLetter}</div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:2px;letter-spacing:0.04em;">Requesting Officer</div>
                <div style="font-size:1.05rem;font-weight:900;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${data.requester_name}">${data.requester_name}</div>
                <div style="font-size:.78rem;color:var(--text-muted);font-weight:600;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <i data-lucide="award" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:3px;"></i>${data.rank_or_title || 'No Rank/Title Specified'}
                </div>
            </div>
        </div>
        <div class="profile-card">
            <div class="profile-avatar" style="background:rgba(16, 185, 129, 0.08); color:#10b981; border-color:rgba(16,185,129,0.15);"><i data-lucide="building" style="width:20px;height:20px;"></i></div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:2px;letter-spacing:0.04em;">Originating Department</div>
                <div style="font-size:1.05rem;font-weight:900;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${data.department}">${data.department}</div>
                <div style="font-size:.78rem;color:var(--text-muted);font-weight:600;margin-top:2px;">
                    <i data-lucide="calendar" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:3px;"></i>Submitted ${data.created_at}
                </div>
            </div>
        </div>

        <div class="profile-card" style="grid-column: 1 / -1; display:flex; flex-direction:column; align-items:stretch; gap:0.75rem;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.04em;">Requisition Intention & Purpose</span>
                <div class="stat-pill-group" style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                    <span class="stat-pill" style="background:${data.usage_type_badge.bg}; color:${data.usage_type_badge.color}; border-color:rgba(0,0,0,0.05); font-weight:800;"><i data-lucide="${data.usage_type === 'temporary' ? 'calendar' : 'package-check'}" style="width:12px;"></i> ${data.usage_type_badge.label}</span>
                    <span class="stat-pill"><i data-lucide="layers" style="width:12px;"></i> ${totalItemsCount} ${totalItemsCount === 1 ? 'Item Type' : 'Item Types'}</span>
                    <span class="stat-pill"><i data-lucide="hash" style="width:12px;"></i> Total Qty: ${totalQtyRequested.toLocaleString()}</span>
                </div>
            </div>
            ${returnDateBannerHtml}
            <div class="purpose-quote">
                ${purposeText}
            </div>
        </div>
    </div>
    `;

        let itemRowsHtml = '';
        if (isPending) {
            data.items.forEach((item, i) => {
                const itemCategory = item.category ? item.category.trim().toLowerCase() : '';
                const isAltAgreed = (data.alternative_status === 'agreed' && item.alternative_description);
                const altApprovedQty = item.alternative_quantity_approved !== null ? parseFloat(item.alternative_quantity_approved) : 0;
                
                let defaultOriginalApproved = parseFloat(item.quantity_requested);
                if (item.quantity_approved !== null) {
                    defaultOriginalApproved = parseFloat(item.quantity_approved);
                }
                
                if (isAltAgreed && defaultOriginalApproved === 0 && altApprovedQty > 0) {
                    defaultOriginalApproved = Math.max(0, parseFloat(item.quantity_requested) - altApprovedQty);
                }
                
                let altStock = 0;
                let altUnit = item.unit;
                if (isAltAgreed && data.alternatives) {
                    const matchedAlt = data.alternatives.find(a => a.description.trim() === item.alternative_description.trim());
                    if (matchedAlt) {
                        altStock = matchedAlt.total_stock;
                        altUnit = matchedAlt.unit;
                    }
                }

                let alternativeOptions = `<option value="">-- Select Alternative Item --</option>`;
                if (data.alternatives && data.alternatives.length > 0) {
                    data.alternatives.forEach(alt => {
                        const altCategory = alt.category ? alt.category.trim().toLowerCase() : '';
                        if (altCategory === itemCategory) {
                            const isSelected = (isAltAgreed && alt.description.trim() === item.alternative_description.trim()) ? 'selected' : '';
                            alternativeOptions += `<option value="${alt.description}" data-unit="${alt.unit}" data-stock="${alt.total_stock}" ${isSelected}>${alt.description} (Stock: ${alt.total_stock} ${alt.unit})</option>`;
                        }
                    });
                }

                const stockInfo = item.stock_sufficient ?
                    `<span style="color:#10b981;font-size:.72rem;font-weight:800;display:inline-flex;align-items:center;gap:3px;"><i data-lucide="check-circle-2" style="width:12px;height:12px;"></i> Sufficient Stock (${parseFloat(item.current_stock).toLocaleString()} ${item.unit})</span>` :
                    `<span style="color:#f59e0b;font-size:.72rem;font-weight:800;display:inline-flex;align-items:center;gap:3px;"><i data-lucide="alert-triangle" style="width:12px;height:12px;"></i> Critical Stock (${parseFloat(item.current_stock).toLocaleString()} ${item.unit})</span>`;

                const descTextHtml = isAltAgreed ?
                    `<span>${item.description}</span> <span style="color:var(--store-orange); font-weight:800; margin-left:6px;"><i data-lucide="shuffle" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:2px;"></i>Alternative: ${item.alternative_description}</span>` :
                    item.description;

                itemRowsHtml += `
            <div class="item-decision-card approved-row" id="item-row-${i}" data-index="${i}">
                <!-- Top Row: Toggle, Details, and Requested Qty -->
                <div class="item-card-header">
                    <div class="item-card-header-left">
                        <div class="switch-wrapper">
                            <input type="checkbox" class="switch-input approve-toggle" id="chk-${i}"
                                data-index="${i}"
                                checked
                                onchange="toggleItemApproval(${i})">
                            <label for="chk-${i}" class="switch-label" title="Toggle item approval"></label>
                        </div>
                        <div>
                            <div style="font-size:.95rem;font-weight:800;color:var(--text-main);" id="item-desc-text-${i}">${descTextHtml}</div>
                            <div style="margin-top:4px;">${stockInfo}</div>
                        </div>
                    </div>

                    <div class="item-card-header-right">
                        <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;">Requested</div>
                        <div>
                            <span style="font-size:1.05rem;font-weight:800;color:var(--text-main);">${parseFloat(item.quantity_requested).toLocaleString()}</span>
                            <span style="font-size:.78rem;color:var(--text-muted);font-weight:700;margin-left:2px;">${item.unit}</span>
                        </div>
                    </div>
                </div>

                <!-- Bottom Panel: Spinner controls & Remarks quick-tags -->
                <div class="item-card-panel" style="flex-wrap: wrap;">
                    <div class="item-card-spinner-box">
                        <span style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.02em;">Approved Allocation</span>
                        <div class="qty-spinner">
                            <button type="button" class="qty-btn" onclick="adjustQty(${i}, -1)">−</button>
                            <input type="number" class="approved-qty-input"
                                id="qty-${i}"
                                data-item-id="${item.id}"
                                data-requested="${parseFloat(item.quantity_requested)}"
                                data-stock="${parseFloat(item.current_stock)}"
                                data-index="${i}"
                                value="${defaultOriginalApproved}"
                                min="0" max="${parseFloat(item.quantity_requested)}" step="0.01"
                                oninput="onQtyChange(${i})">
                            <button type="button" class="qty-btn" onclick="adjustQty(${i}, 1)">+</button>
                        </div>
                    </div>

                    <div class="item-card-status-box">
                        <span style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.02em;">Evaluation & Notes</span>
                        <div>
                            <textarea class="reason-input item-reason"
                                id="reason-${i}"
                                data-index="${i}"
                                placeholder="Remarks for decline or reduction..."
                                rows="2"
                                style="display:${isAltAgreed ? 'block' : 'none'}; margin-bottom: 6px;">${item.remarks||''}</textarea>

                            <span id="reason-ok-${i}" style="font-size:.78rem;color:#10b981;font-weight:700;display:${isAltAgreed ? 'none' : 'inline-flex'};align-items:center;gap:4px;">
                                <i data-lucide="check-circle" style="width:14px;height:14px;"></i> Approved Allocation
                            </span>

                            <div id="quick-tags-${i}" style="display:none;">
                                <span class="quick-tag" onclick="fillQuickReason(${i}, 'Reduce Allocation')">Reduce Allocation</span>
                            </div>
                        </div>
                    </div>

                    <!-- Alternative Item Selector Box Removed -->
                </div>
            </div>`;
            });
        } else {
            // Read-only view for already processed
            const rows = data.items.map(item => {
                const requested = parseFloat(item.quantity_requested) || 0;
                const approved = item.quantity_approved !== null ? parseFloat(item.quantity_approved) : 0;
                const altApproved = item.alternative_quantity_approved !== null ? parseFloat(item.alternative_quantity_approved) : 0;
                const totalApproved = approved + altApproved;
                const pct = requested > 0 ? Math.min(Math.round((totalApproved / requested) * 100), 100) : 0;

                let fulfillBadgeClass = 'fulfill-ratio-badge';
                let fulfillLabel = `${pct}% Fulfill`;
                if (totalApproved === 0) {
                    fulfillBadgeClass += ' declined';
                    fulfillLabel = 'Declined';
                } else if (totalApproved < requested) {
                    fulfillBadgeClass += ' reduced';
                    fulfillLabel = `${pct}% Reduced`;
                }

                const stockInfo = item.stock_sufficient ?
                    `<span style="color:#10b981;font-size:.7rem;font-weight:700;">✔ Sufficient</span>` :
                    `<span style="color:#ef4444;font-size:.7rem;font-weight:700;">⚠ Short Stock</span>`;

                return `
            <div class="item-decision-card ${totalApproved === 0 ? 'declined-row' : 'approved-row'}">
                <!-- Top Row: Description & Fulfillment status badge -->
                <div class="item-card-header">
                    <div class="item-card-header-left">
                        <div>
                            ${item.alternative_description ? `
                                <div style="font-size:.95rem;font-weight:800;color:var(--text-main); display:flex; align-items:center; gap:6px;">
                                    <span>${item.description}</span>
                                    <span style="font-size:0.75rem; font-weight:800; color:var(--success-color);">(Approved: ${approved.toLocaleString()} ${item.unit})</span>
                                </div>
                                <div style="font-size:.92rem;font-weight:800;color:var(--store-orange); display:flex; align-items:center; gap:6px; margin-top:4px;">
                                    <i data-lucide="shuffle" style="width:14px;height:14px;display:inline-block;vertical-align:middle;margin-right:2px;"></i>Alternative: ${item.alternative_description}
                                    <span style="font-size:0.75rem; font-weight:800;">(Approved: ${altApproved.toLocaleString()} ${item.unit})</span>
                                </div>
                            ` : `
                                <div style="font-size:.95rem;font-weight:800;color:var(--text-main);">${item.description}</div>
                            `}
                            <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;margin-top:4px;">
                                Unit: ${item.unit} · Stock: ${parseFloat(item.current_stock).toLocaleString()} (${stockInfo})
                            </div>
                        </div>
                    </div>
                    <div class="item-card-header-right">
                        <span class="${fulfillBadgeClass}">${fulfillLabel}</span>
                    </div>
                </div>

                <!-- Bottom Panel: Allocation Board -->
                <div class="item-card-panel" style="gap:1.5rem;">
                    <!-- Requested -->
                    <div style="flex:1; min-width:80px;">
                        <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;">Requested</div>
                        <div style="font-size:1.1rem;font-weight:800;color:var(--text-main);margin-top:2px;">${requested.toLocaleString()}</div>
                    </div>

                    <!-- Approved -->
                    <div style="flex:1; min-width:80px;">
                        <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;">Total Approved</div>
                        <div style="font-size:1.15rem;font-weight:900;color:${totalApproved === 0 ? '#ef4444' : '#10b981'};margin-top:2px;">${totalApproved.toLocaleString()}</div>
                    </div>

                    <!-- Fulfillment Progress -->
                    <div style="flex:2; min-width:180px;">
                        <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;margin-bottom:6px;">Fulfillment Progress</div>
                        <div class="fulfill-progress-container" style="margin-top:0;">
                            <div class="fulfill-progress-bar" style="width: ${pct}%; background:${totalApproved === 0 ? '#ef4444' : (totalApproved < requested ? '#f59e0b' : 'linear-gradient(90deg, #4f46e5 0%, #10b981 100%)')}"></div>
                        </div>
                    </div>
                </div>

                <!-- Officer Remarks (If any) -->
                ${item.remarks ? `
                <div style="background:rgba(0,0,0,0.015); border:1.5px dashed var(--border-color); border-radius:10px; padding:0.75rem 1rem; margin-top:0.25rem;">
                    <span style="font-size:0.65rem; font-weight:900; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:4px; letter-spacing:0.04em;">Officer Decision Remarks</span>
                    <p style="margin:0; font-size:0.8rem; color:var(--text-main); font-style:italic; line-height:1.4;">"${item.remarks}"</p>
                </div>` : ''}
            </div>`;
            }).join('');

            itemRowsHtml = `
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:16px;overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.01);">
            ${rows}
        </div>`;
        }

        let collectorInfoHtml = '';
        if (['approved', 'partially_approved'].includes(data.status)) {
            if (data.collected_at) {
                collectorInfoHtml = `
                <div style="background:rgba(16,185,129,0.03); border:1.5px dashed rgba(16,185,129,0.25); border-radius:16px; padding:1.25rem; margin-top:1.25rem; display:flex; flex-direction:column; gap:1rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:1px dashed rgba(16,185,129,0.15); padding-bottom:8px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="width:34px; height:34px; background:rgba(16,185,129,0.08); color:#10b981; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                                <i data-lucide="package-check" style="width:16px;"></i>
                            </div>
                            <div>
                                <h4 style="margin:0; font-size:0.85rem; font-weight:800; color:var(--text-main); text-transform:uppercase; letter-spacing:0.04em;">Physical Collection Log</h4>
                                <p style="margin:0; font-size:0.75rem; color:var(--text-muted);">Items have been successfully issued and collected</p>
                            </div>
                        </div>
                        <span class="pill" style="background:rgba(16,185,129,0.1); color:#10b981; font-weight:800; font-size:0.7rem; padding:4px 10px;">COLLECTED</span>
                    </div>
                    
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.75rem 1rem;">
                            <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Collector Name</div>
                            <div style="font-size:0.9rem; font-weight:900; color:var(--text-main);">${data.collector_name || 'N/A'}</div>
                        </div>
                        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.75rem 1rem;">
                            <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Collector Contact</div>
                            <div style="font-size:0.9rem; font-weight:900; color:var(--text-main);">${data.collector_contact || 'N/A'}</div>
                        </div>
                        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.75rem 1rem; grid-column: span 2;">
                            <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Collection Destination / Location</div>
                            <div style="font-size:0.9rem; font-weight:900; color:var(--text-main);">${data.collector_location || 'N/A'}</div>
                        </div>
                        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.75rem 1rem;">
                            <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Confirmed By (Store Staff)</div>
                            <div style="font-size:0.9rem; font-weight:900; color:var(--text-main);">${data.collected_by_name || 'N/A'}</div>
                        </div>
                        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.75rem 1rem;">
                            <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Collection Date & Time</div>
                            <div style="font-size:0.9rem; font-weight:900; color:var(--text-main);">${data.collected_at || 'N/A'}</div>
                        </div>
                    </div>
                </div>`;
            } else {
                collectorInfoHtml = `
                <div style="background:rgba(245,158,11,0.03); border:1.5px dashed rgba(245,158,11,0.25); border-radius:16px; padding:1.25rem; margin-top:1.25rem; display:flex; align-items:center; gap:12px;">
                    <div style="width:34px; height:34px; background:rgba(245,158,11,0.08); color:#f59e0b; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i data-lucide="clock" style="width:16px;"></i>
                    </div>
                    <div>
                        <h4 style="margin:0; font-size:0.85rem; font-weight:800; color:var(--text-main); text-transform:uppercase; letter-spacing:0.04em;">Awaiting Collection</h4>
                        <p style="margin:0; font-size:0.75rem; color:var(--text-muted);">This requisition is approved but physical collection has not yet been confirmed by store personnel.</p>
                    </div>
                </div>`;
            }
        }

        document.getElementById('modalBody').innerHTML = `
    <!-- profile grid and metadata -->
    ${profileGridHtml}

    <!-- Stock Warning Banner -->
    <div id="stockWarningBanner" style="display:none; background:#fffbeb; border:1px solid #fef3c7; border-left:4px solid #ef4444; padding:12px 16px; border-radius:12px; margin-bottom:1.5rem; align-items:center; gap:12px;">
        <div style="width:32px; height:32px; background:rgba(239, 68, 68, 0.08); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#ef4444; flex-shrink:0;">
            <i data-lucide="alert-triangle" style="width:20px;"></i>
        </div>
        <div style="flex:1;">
            <h4 style="margin:0; font-size:0.85rem; font-weight:800; color:#991b1b; text-transform:uppercase;">Insufficient Stock Blocked</h4>
            <p style="margin:0; font-size:0.75rem; color:#b91c1c; font-weight:600;">One or more items have approved allocations exceeding the available stock in the system. Reduce their quantity or select alternative items to proceed.</p>
        </div>
    </div>

    <!-- decision header / list -->
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; margin-top:1.5rem;">
        <h3 style="margin:0; font-size:0.95rem; font-weight:900; color:var(--text-main); letter-spacing:-0.01em; display:flex; align-items:center; gap:6px;">
            <i data-lucide="list-checks" style="width:16px; color:var(--primary);"></i> Requested Items
        </h3>
        <span style="font-size:.72rem; color:var(--text-muted); font-weight:700;">Please review stock indicators before committing decisions.</span>
    </div>

    ${isPending ? `
    <!-- Live status bar -->
    <div id="statusBar" class="all-approved">
        <span id="statusBarIcon">✅</span>
        <span id="statusBarText">All items will be <b>Approved</b></span>
    </div>

    <!-- Legend -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.85rem; font-size:.72rem; font-weight:700; color:var(--text-muted); background:var(--bg-main); padding:0.6rem 1rem; border-radius:10px; border:1.5px solid var(--border-color);">
        <div style="display:flex; gap:1.25rem;">
            <span><span class="legend-dot" style="background:#10b981;"></span>Active = Approve item</span>
            <span><span class="legend-dot" style="background:#cbd5e1;"></span>Inactive = Decline item</span>
        </div>
        <span style="color:var(--primary);"><i data-lucide="info" style="width:12px; display:inline-block; vertical-align:middle; margin-right:3px;"></i>Use − / + controls to adjust approved quantities</span>
    </div>` : ''}

    <!-- Item Decision Card Table -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:16px;overflow:hidden;margin-bottom:1.75rem; box-shadow:0 4px 12px rgba(0,0,0,0.01);">
        ${itemRowsHtml}
    </div>

    ${isPending ? `
    <!-- Live action summary dashboard -->
    <div class="summary-dashboard" id="summaryDashboard">
        <div class="summary-metrics">
            <div class="metric-box">
                <span class="metric-val" id="metricApproved" style="color:#10b981;">0</span>
                <span class="metric-lbl">Approved</span>
            </div>
            <div class="metric-box">
                <span class="metric-val" id="metricReduced" style="color:#f59e0b;">0</span>
                <span class="metric-lbl">Reduced</span>
            </div>
            <div class="metric-box">
                <span class="metric-val" id="metricDeclined" style="color:#ef4444;">0</span>
                <span class="metric-lbl">Declined</span>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:0.65rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em;">Overall Decision Action</div>
            <span class="pill" id="summaryActionBadge" style="margin-top:4px;">PENDING REVIEW</span>
        </div>
    </div>

    <div>
        <label style="display:block;font-size:.68rem;font-weight:900;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px; display:flex; align-items:center; gap:4px;">
            <i data-lucide="message-square" style="width:14px;color:var(--text-muted);"></i> General Requisition Notes (Add notes visible to the requester...)
        </label>
        <textarea id="adminNotes" rows="3" placeholder="Add central store decision notes, pickup directions, or overall remarks here..." style="width:100%;padding:.85rem 1rem;border:1.5px solid var(--border-color);border-radius:12px;font-family:inherit;font-size:.88rem;background:var(--bg-main);color:var(--text-main);resize:vertical;box-sizing:border-box;transition:all 0.25s ease;" onfocus="this.style.borderColor='var(--primary)';this.style.background='var(--bg-card)';">${data.admin_notes||''}</textarea>
    </div>

    <!-- Decline Reason Box (shown only when all items are declined) -->
    <div id="declineReasonBox" style="display:none; margin-top:1rem; background:rgba(239,68,68,0.03); border:1.5px solid rgba(239,68,68,0.25); border-radius:12px; padding:1rem 1.25rem;">
        <label style="display:flex; align-items:center; gap:6px; font-size:.68rem; font-weight:900; color:#dc2626; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">
            <i data-lucide="alert-circle" style="width:14px;"></i> Reason for Declining (required when declining entire requisition)
        </label>
        <textarea id="declineReason" rows="3" placeholder="State the reason for declining this requisition. The requester will see this message..." style="width:100%;padding:.85rem 1rem;border:1.5px solid rgba(239,68,68,0.3);border-radius:10px;font-family:inherit;font-size:.88rem;background:var(--bg-card);color:var(--text-main);resize:vertical;box-sizing:border-box;transition:all 0.25s ease;" onfocus="this.style.borderColor='#ef4444';" onblur="this.style.borderColor='rgba(239,68,68,0.3)'"></textarea>
    </div>` : `
    ${data.admin_notes?`<div style="background:rgba(79,70,229,.03);border:1px solid rgba(79,70,229,.15);border-radius:16px;padding:1.25rem; margin-top: 1rem;"><div style="font-size:.68rem;font-weight:900;color:var(--primary);text-transform:uppercase;letter-spacing:0.05em;display:flex;align-items:center;gap:4px;margin-bottom:4px;"><i data-lucide="message-square" style="width:14px;"></i> Store Officer Notes</div><p style="margin:0;font-size:.9rem;color:var(--text-main);line-height:1.6;font-style:italic;">"${data.admin_notes}"</p></div>`:''}

    ${data.status === 'declined' && data.decline_reason ? `
    <div style="background:rgba(239,68,68,0.04); border:1px solid rgba(239,68,68,0.2); border-radius:16px; padding:1.25rem; margin-top:0.75rem;">
        <div style="font-size:.68rem;font-weight:900;color:#dc2626;text-transform:uppercase;letter-spacing:0.05em;display:flex;align-items:center;gap:4px;margin-bottom:6px;">
            <i data-lucide="alert-circle" style="width:14px;"></i> Reason for Decline
        </div>
        <p style="margin:0;font-size:.9rem;color:#7f1d1d;line-height:1.6;">${data.decline_reason}</p>
    </div>` : ''}

    <div style="background:var(--bg-main); border:1px solid var(--border-color); border-radius:16px; padding:1.15rem; margin-top:1.25rem; display:flex; justify-content:space-between; align-items:center;">
        <div style="display:flex; align-items:center; gap:8px;">
            <div style="width:34px; height:34px; background:rgba(79,70,229,0.08); color:var(--primary); border-radius:10px; display:flex; align-items:center; justify-content:center;">
                <i data-lucide="user-check" style="width:16px;"></i>
            </div>
            <div>
                <div style="font-size:.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em;">Processor Authority</div>
                <div style="font-size:.85rem; font-weight:900; color:var(--text-main);">${data.processor ? data.processor : 'Automated System Authority'}</div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em;">Processing Timestamp</div>
            <div style="font-size:.85rem; font-weight:900; color:var(--text-main);">${data.processed_at ? data.processed_at : 'Pending'}</div>
        </div>
    </div>

    ${collectorInfoHtml}
    `}`;

        if (isPending) {
            document.getElementById('modalFooter').innerHTML = `
        <button onclick="closeModal()" style="background:var(--bg-main); color:var(--text-main); border:1.5px solid var(--border-color); padding:.75rem 1.5rem; border-radius:12px; font-weight:800; cursor:pointer; font-size:.88rem; transition:0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.03)'" onmouseout="this.style.background='var(--bg-main)'">
            Cancel
        </button>
        <button onclick="submitDecision()" id="submitDecisionBtn"
            style="background:#4f46e5;color:white;border:none;padding:.75rem 2.25rem;border-radius:12px;font-weight:800;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:.88rem;box-shadow:0 8px 20px rgba(79, 70, 229, 0.25);transition:0.2s;" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
            <i data-lucide="send" style="width:16px;"></i> Commit Requisition Decision
        </button>`;
            updateStatusBar();
        } else {
            let footerHtml = `
        <button onclick="closeModal()" style="background:var(--bg-main); color:var(--text-main); border:1.5px solid var(--border-color); padding:.75rem 1.5rem; border-radius:12px; font-weight:800; cursor:pointer; font-size:.88rem; transition:0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.03)'" onmouseout="this.style.background='var(--bg-main)'">
            Close Window
        </button>`;
            if (data.collected_at) {
                footerHtml = `
                <a href="{{ request()->getBasePath() }}/requisitions/receipt/${id}" target="_blank"
                    style="background:rgba(99, 102, 241, 0.08); border: 1.5px solid rgba(99, 102, 241, 0.2); color: #4f46e5; padding: .75rem 1.5rem; border-radius: 12px; font-weight: 800; cursor: pointer; font-size: .88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; margin-right: auto;" onmouseover="this.style.background='#4f46e5'; this.style.color='white';" onmouseout="this.style.background='rgba(99, 102, 241, 0.08)'; this.style.color='#4f46e5';">
                    <i data-lucide="printer" style="width: 16px;"></i> Print Collection Receipt
                </a>` + footerHtml;
            }
            document.getElementById('modalFooter').innerHTML = footerHtml;
        }

        lucide.createIcons();
    }

    // Custom function to adjust quantity in custom spinner
    function adjustQty(idx, dir) {
        const qtyInput = document.getElementById(`qty-${idx}`);
        if (!qtyInput || qtyInput.disabled) return;
        let currentVal = parseFloat(qtyInput.value) || 0;
        let newVal = currentVal + dir;
        if (newVal < 0) newVal = 0;
        const requested = parseFloat(qtyInput.dataset.requested) || 0;
        if (newVal > requested) newVal = requested;
        qtyInput.value = parseFloat(newVal.toFixed(2));
        onQtyChange(idx);
    }

    // Custom function to fill remarks quickly
    function fillQuickReason(idx, text) {
        const reasonInput = document.getElementById(`reason-${idx}`);
        if (reasonInput) {
            reasonInput.value = text;
        }
    }

    // Toggle item approval on checkbox/switch change
    function toggleItemApproval(idx) {
        const chk = document.getElementById(`chk-${idx}`);
        const row = document.getElementById(`item-row-${idx}`);
        const qtyInput = document.getElementById(`qty-${idx}`);
        const reasonInput = document.getElementById(`reason-${idx}`);
        const reasonOk = document.getElementById(`reason-ok-${idx}`);
        const quickTags = document.getElementById(`quick-tags-${idx}`);

        if (chk.checked) {
            // Approve
            row.className = 'item-decision-card approved-row';
            qtyInput.disabled = false;
            qtyInput.parentElement.style.opacity = '1';
            reasonInput.style.display = 'none';
            reasonOk.style.display = 'inline-flex';
            quickTags.style.display = 'none';
            // Restore qty to requested if it was 0
            if (parseFloat(qtyInput.value) === 0) {
                qtyInput.value = qtyInput.dataset.requested;
            }
        } else {
            // Decline
            row.className = 'item-decision-card declined-row';
            qtyInput.value = 0;
            qtyInput.disabled = true;
            qtyInput.parentElement.style.opacity = '.4';
            reasonInput.style.display = 'block';
            reasonOk.style.display = 'none';
            quickTags.style.display = 'block';
        }
        updateStatusBar();
    }

    // When quantity changes, update indication
    function onQtyChange(idx) {
        const qtyInput = document.getElementById(`qty-${idx}`);
        const reasonInput = document.getElementById(`reason-${idx}`);
        const reasonOk = document.getElementById(`reason-ok-${idx}`);
        const quickTags = document.getElementById(`quick-tags-${idx}`);
        const requested = parseFloat(qtyInput.dataset.requested);
        let approved = parseFloat(qtyInput.value) || 0;

        if (approved > requested) {
            approved = requested;
            qtyInput.value = requested;
        }

        if (approved < requested && approved > 0) {
            reasonInput.style.display = 'block';
            reasonInput.placeholder = 'Reason for reduced quantity allocation...';
            reasonOk.style.display = 'none';
            quickTags.style.display = 'block';
        } else if (approved >= requested) {
            reasonInput.style.display = 'none';
            reasonOk.style.display = 'inline-flex';
            quickTags.style.display = 'none';
        }
        updateStatusBar();
    }

    // Compute overall decision status and update the status bar & summary dashboard
    function updateStatusBar() {
        const checkboxes = document.querySelectorAll('.approve-toggle');
        const qtyInputs = document.querySelectorAll('.approved-qty-input');

        let cntApproved = 0;
        let cntReduced = 0;
        let cntDeclined = 0;
        let allApproved = true;
        let anyApproved = false;
        let anyPartial = false;
        let hasExceededStock = false;

        checkboxes.forEach((chk, i) => {
            const qtyEl = document.getElementById(`qty-${i}`);
            const requested = parseFloat(qtyEl?.dataset.requested || 0);
            const originalStock = parseFloat(qtyEl?.dataset.stock || 0);

            let stockLimit = originalStock;
            let approvedQty = 0;

            if (chk.checked) {
                approvedQty = parseFloat(qtyEl?.value || 0);

                // Validate
                const row = document.getElementById(`item-row-${i}`);
                if (approvedQty > stockLimit) {
                    hasExceededStock = true;
                    // Add red border or warning outline
                    if (row) {
                        row.style.borderColor = '#ef4444';
                        row.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
                    }

                    // Show a warning text next to description/stockInfo
                    let warningSpan = document.getElementById(`stock-exceeded-warning-${i}`);
                    if (!warningSpan) {
                        warningSpan = document.createElement('div');
                        warningSpan.id = `stock-exceeded-warning-${i}`;
                        warningSpan.style.color = '#ef4444';
                        warningSpan.style.fontSize = '0.72rem';
                        warningSpan.style.fontWeight = '900';
                        warningSpan.style.marginTop = '4px';
                        warningSpan.style.display = 'flex';
                        warningSpan.style.alignItems = 'center';
                        warningSpan.style.gap = '4px';

                        const parent = document.getElementById(`item-desc-text-${i}`)?.parentNode;
                        if (parent) {
                            parent.appendChild(warningSpan);
                        }
                    }
                    warningSpan.innerHTML = `⚠️ Blocked: Allocation (${approvedQty}) exceeds available stock (${stockLimit})`;
                } else {
                    if (row) {
                        row.style.borderColor = '';
                        row.style.boxShadow = '';
                    }
                    const warningSpan = document.getElementById(`stock-exceeded-warning-${i}`);
                    if (warningSpan) {
                        warningSpan.remove();
                    }
                }
            } else {
                // If not approved, clear warning
                const row = document.getElementById(`item-row-${i}`);
                if (row) {
                    row.style.borderColor = '';
                    row.style.boxShadow = '';
                }
                const warningSpan = document.getElementById(`stock-exceeded-warning-${i}`);
                if (warningSpan) {
                    warningSpan.remove();
                }
            }

            const approved = chk.checked ? approvedQty : 0;
            if (!chk.checked || approved === 0) {
                cntDeclined++;
                allApproved = false;
            } else {
                anyApproved = true;
                if (approved < requested) {
                    cntReduced++;
                    anyPartial = true;
                    allApproved = false;
                } else {
                    cntApproved++;
                }
            }
        });

        // Update live metrics on summary board
        const metricApp = document.getElementById('metricApproved');
        const metricRed = document.getElementById('metricReduced');
        const metricDec = document.getElementById('metricDeclined');
        const summaryBadge = document.getElementById('summaryActionBadge');

        if (metricApp) metricApp.textContent = cntApproved;
        if (metricRed) metricRed.textContent = cntReduced;
        if (metricDec) metricDec.textContent = cntDeclined;

        if (summaryBadge) {
            if (cntDeclined === checkboxes.length) {
                summaryBadge.style.background = 'rgba(239,68,68,0.1)';
                summaryBadge.style.color = '#ef4444';
                summaryBadge.textContent = '❌ Full Decline';
            } else if (cntApproved === checkboxes.length) {
                summaryBadge.style.background = 'rgba(16,185,129,0.1)';
                summaryBadge.style.color = '#10b981';
                summaryBadge.textContent = '✅ Full Approval';
            } else {
                summaryBadge.style.background = 'rgba(245,158,11,0.1)';
                summaryBadge.style.color = '#f59e0b';
                summaryBadge.textContent = '⚠️ Partial Approval';
            }
        }

        const banner = document.getElementById('stockWarningBanner');
        const submitBtn = document.getElementById('submitDecisionBtn');

        if (hasExceededStock) {
            if (banner) banner.style.display = 'flex';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
                submitBtn.title = 'Cannot approve: allocation exceeds available stock';
            }
        } else {
            if (banner) banner.style.display = 'none';
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
                submitBtn.title = '';
            }
        }

        if (submitBtn) {
            if (cntReduced > 0) {
                if (currentReqData && currentReqData.alternative_status === 'proposed') {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.5';
                    submitBtn.style.cursor = 'not-allowed';
                    submitBtn.innerHTML = `<i data-lucide="clock" style="width:16px;"></i> Awaiting Response from ${currentReqData.department}`;
                } else if (currentReqData && currentReqData.alternative_status === 'agreed') {
                    submitBtn.innerHTML = `<i data-lucide="send" style="width:16px;"></i> Commit Requisition Decision`;
                } else {
                    submitBtn.innerHTML = `<i data-lucide="send" style="width:16px;"></i> Send Suggested Qty to ${currentReqData.department}`;
                }
            } else {
                submitBtn.innerHTML = `<i data-lucide="send" style="width:16px;"></i> Commit Requisition Decision`;
            }
        }

        const bar = document.getElementById('statusBar');
        const icon = document.getElementById('statusBarIcon');
        const text = document.getElementById('statusBarText');
        if (!bar) return;

        const allDeclined = !anyApproved;

        if (hasExceededStock) {
            bar.className = 'all-declined';
            bar.style.background = 'rgba(239,68,68,.1)';
            bar.style.color = '#991b1b';
            bar.style.border = '1px solid rgba(239,68,68,.2)';
            icon.textContent = '⛔';
            text.innerHTML = 'Approval blocked — <b>allocation exceeds available stock</b>';
        } else if (currentReqData && currentReqData.alternative_status === 'agreed' && cntReduced > 0) {
            bar.className = 'all-approved';
            bar.style.background = 'rgba(16,185,129,.12)';
            bar.style.color = '#065f46';
            bar.style.border = '1px solid rgba(16,185,129,.25)';
            icon.textContent = '✅';
            text.innerHTML = 'Department has <b>agreed</b> to suggested quantity proposal. Proceed to Commit.';
        } else if (currentReqData && currentReqData.alternative_status === 'proposed' && cntReduced > 0) {
            bar.className = 'partial';
            bar.style.background = 'rgba(245,158,11,.12)';
            bar.style.color = '#92400e';
            bar.style.border = '1px solid rgba(245,158,11,.25)';
            icon.textContent = '⏳';
            text.innerHTML = 'Suggested quantity proposed. <b>Awaiting department response...</b>';
        } else if (cntReduced > 0) {
            bar.className = 'partial';
            bar.style.background = 'rgba(245,158,11,.12)';
            bar.style.color = '#92400e';
            bar.style.border = '1px solid rgba(245,158,11,.25)';
            icon.textContent = '🔀';
            text.innerHTML = 'Quantity reduced. <b>Click button below to suggest new quantity to department</b>';
        } else if (allDeclined) {
            bar.className = 'all-declined';
            bar.style.background = 'rgba(239,68,68,.1)';
            bar.style.color = '#991b1b';
            bar.style.border = '1px solid rgba(239,68,68,.2)';
            icon.textContent = '❌';
            text.innerHTML = 'All items will be <b>Declined</b>';
        } else if (allApproved && !anyPartial) {
            bar.className = 'all-approved';
            bar.style.background = 'rgba(16,185,129,.12)';
            bar.style.color = '#065f46';
            bar.style.border = '1px solid rgba(16,185,129,.25)';
            icon.textContent = '✅';
            text.innerHTML = 'All items will be <b>Approved</b>';
        } else {
            bar.className = 'partial';
            bar.style.background = 'rgba(245,158,11,.12)';
            bar.style.color = '#92400e';
            bar.style.border = '1px solid rgba(245,158,11,.25)';
            icon.textContent = '⚠️';
            text.innerHTML = 'Some items differ — will be <b>Partially Approved</b>';
        }

        // Show / hide decline reason box based on whether all items are declined
        const declineReasonBox = document.getElementById('declineReasonBox');
        if (declineReasonBox) {
            if (allDeclined && !hasExceededStock) {
                declineReasonBox.style.display = 'block';
            } else {
                declineReasonBox.style.display = 'none';
                const reasonTextarea = document.getElementById('declineReason');
                if (reasonTextarea) {
                    reasonTextarea.style.borderColor = 'rgba(239,68,68,0.3)';
                }
            }
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // Compute auto status from item selections
    function computeStatus() {
        const checkboxes = document.querySelectorAll('.approve-toggle');
        let allDeclined = true;
        let allFullApproval = true;
        checkboxes.forEach((chk, i) => {
            const qtyEl = document.getElementById(`qty-${i}`);
            const requested = parseFloat(qtyEl?.dataset.requested || 0);
            const approved = chk.checked ? (parseFloat(qtyEl?.value) || 0) : 0;
            if (chk.checked && approved > 0) allDeclined = false;
            if (!chk.checked || approved < requested) allFullApproval = false;
        });
        if (allDeclined) return 'declined';
        if (allFullApproval) return 'approved';
        return 'partially_approved';
    }

    async function submitDecision() {
        const status = computeStatus();
        const items = [];
        let cntReduced = 0;
        
        document.querySelectorAll('.approved-qty-input').forEach((inp, i) => {
            const chk = document.getElementById(`chk-${i}`);
            const reason = document.getElementById(`reason-${i}`)?.value || '';
            const requested = parseFloat(inp.dataset.requested || 0);
            const approved = chk && !chk.checked ? 0 : (parseFloat(inp.value) || 0);
            
            if (chk && chk.checked && approved > 0 && approved < requested) {
                cntReduced++;
            }
            
            items.push({
                id: parseInt(inp.dataset.itemId),
                quantity_approved: approved,
                remarks: reason,
                alternative_description: null,
                alternative_quantity_approved: 0
            });
        });

        const notes = document.getElementById('adminNotes')?.value || '';
        const declineReason = document.getElementById('declineReason')?.value || '';
        const btn = document.getElementById('submitDecisionBtn');

        // Propose suggested quantity flow
        let finalStatus = status;
        let altStatus = null;
        if (cntReduced > 0 && (!currentReqData || currentReqData.alternative_status !== 'agreed')) {
            finalStatus = 'pending';
            altStatus = 'proposed';
        }

        // Validate decline reason is provided when declining everything
        if (finalStatus === 'declined' && !declineReason.trim()) {
            const box = document.getElementById('declineReason');
            if (box) {
                box.style.borderColor = '#ef4444';
                box.focus();
                box.placeholder = '⚠ Please provide a reason for declining this requisition...';
            }
            showToast('Required', 'Please enter a reason for declining the requisition.', 'error');
            return;
        }
        btn.disabled = true;
        btn.innerHTML = '<div style="width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;"></div> Processing Decision...';

        const res = await fetch(`{{ url('/admin/requisitions') }}/${currentReqId}/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: finalStatus,
                alternative_status: altStatus,
                admin_notes: notes,
                decline_reason: declineReason,
                items
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Success', data.message, 'success');
            closeModal();
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast('Error', data.message || 'Failed to process decision.', 'error');
            btn.disabled = false;
            if (cntReduced > 0 && (!currentReqData || currentReqData.alternative_status !== 'agreed')) {
                btn.innerHTML = `<i data-lucide="send" style="width:16px;"></i> Send Suggested Qty to ${currentReqData.department}`;
            } else {
                btn.innerHTML = '<i data-lucide="send" style="width:16px;"></i> Commit Requisition Decision';
            }
            lucide.createIcons();
        }
    }

    function closeModal() {
        document.getElementById('reqModal').classList.remove('open');
    }

    let debounceTimer = null;

    function triggerFilterUpdate() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            updateFilters();
        }, 300); // 300ms debounce
    }

    async function updateFilters() {
        const form = document.getElementById('filter-form');
        const container = document.getElementById('requisitions-table-container');
        if (!form || !container) return;

        container.style.opacity = '0.5';

        const formData = new FormData(form);
        const searchParams = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value.trim() !== '') {
                searchParams.append(key, value);
            }
        }

        const url = form.action + '?' + searchParams.toString();

        try {
            const response = await fetch(url);
            const html = await response.text();

            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTable = doc.getElementById('requisitions-table-container');
            
            if (newTable) {
                container.innerHTML = newTable.innerHTML;
            }
            container.style.opacity = '1';

            if (window.lucide) {
                window.lucide.createIcons();
            }
            bindPaginationClicks();

            window.history.pushState(null, '', url);
        } catch (e) {
            console.error(e);
            container.style.opacity = '1';
        }
    }

    function bindPaginationClicks() {
        const container = document.getElementById('requisitions-table-container');
        if (!container) return;

        const links = container.querySelectorAll('.pagination-container a, td a, th a, div a');
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (!href || href.startsWith('javascript:') || href === '#') return;

            if (href.includes('page=') || href.includes('requisitions')) {
                link.addEventListener('click', async function(e) {
                    e.preventDefault();
                    container.style.opacity = '0.5';
                    try {
                        const response = await fetch(href);
                        const html = await response.text();

                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newTable = doc.getElementById('requisitions-table-container');
                        
                        if (newTable) {
                            container.innerHTML = newTable.innerHTML;
                        }
                        container.style.opacity = '1';

                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                        bindPaginationClicks();

                        window.history.pushState(null, '', href);
                    } catch (err) {
                        console.error(err);
                        container.style.opacity = '1';
                    }
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('search_id_input');
        const deptInput = document.getElementById('dept_input');
        if (searchInput) {
            if (searchInput.value) {
                searchInput.focus();
                const len = searchInput.value.length;
                searchInput.setSelectionRange(len, len);
            }

            searchInput.addEventListener('input', triggerFilterUpdate);
        }
        if (deptInput) {
            deptInput.addEventListener('input', triggerFilterUpdate);
        }
        bindPaginationClicks();
        if (window.lucide) {
            window.lucide.createIcons();
        }

        // Auto-open specific requisition if open_id is present in query parameters
        const urlParams = new URLSearchParams(window.location.search);
        const openId = urlParams.get('open_id');
        if (openId) {
            openRequisitionModal(parseInt(openId));
        }
    });
</script>
@endsection
