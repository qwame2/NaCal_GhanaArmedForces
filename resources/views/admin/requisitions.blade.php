@extends('layouts.admin')
@section('content')

@section('title', 'Store Requisitions')
<style>
    .main-wrapper > *:not(header) {
        max-width: 2000px !important;
    }

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
        background: rgba(5, 150, 105, .03);
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
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(15, 23, 42, 0.65) !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
        z-index: 1000000 !important;
        display: none;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .swal2-container {
        z-index: 1000005 !important;
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
        border-top: 6px solid #059669;
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
        background: var(--primary);
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
        background: #059669;
        border-color: #0ea5e9;
        color: white;
        box-shadow: 0 4px 10px rgba(5, 150, 105, 0.25);
    }

    .stepper-step.completed .stepper-label {
        color: #059669;
    }

    @keyframes activePulse {
        0% {
            box-shadow: 0 0 0 0 rgba(14, 165, 233, 0.4);
        }

        70% {
            box-shadow: 0 0 0 8px rgba(14, 165, 233, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(14, 165, 233, 0);
        }
    }

    .stepper-step.active .stepper-bubble {
        background: #0ea5e9;
        border-color: #0ea5e9;
        color: white;
        animation: activePulse 2s infinite;
    }

    .stepper-step.active .stepper-label {
        color: #0ea5e9;
    }

    .stepper-step.declined-step .stepper-bubble {
        background: #ef4444;
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
        border-color: rgba(5, 150, 105, 0.25);
        background: rgba(5, 150, 105, 0.02);
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
        border: 1.5px solid rgba(5, 150, 105, 0.15);
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
        color: rgba(5, 150, 105, 0.08);
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
        background: #059669;
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
        background: rgba(5, 150, 105, 0.008);
    }

    .item-decision-card:hover {
        background: rgba(5, 150, 105, 0.012);
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
        background: var(--bg-main);
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
        background: #059669;
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
        background: rgba(5, 150, 105, 0.1);
        color: #059669;
    }

    .fulfill-ratio-badge.reduced {
        background: rgba(5, 150, 105, 0.1);
        color: #059669;
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
        background: rgba(5, 150, 105, .12);
        color: #065f46;
        border: 1px solid rgba(5, 150, 105, .25);
    }

    #statusBar.partial {
        background: rgba(5, 150, 105, .12);
        color: #92400e;
        border: 1px solid rgba(5, 150, 105, .25);
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
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.12) !important;
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
        border-color: #0ea5e9;
        background: var(--bg-card);
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
    }

    .filter-control:focus + .filter-icon {
        color: #0ea5e9;
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

    /* Table Stepper/Tracker */
    .mini-tracker {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        position: relative;
        margin-top: 8px;
    }

    .mini-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    .mini-dot {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: var(--bg-main);
        border: 2.5px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        transition: all 0.25s ease;
    }

    .mini-step.completed .mini-dot {
        background: #059669;
        border-color: #0ea5e9;
        color: white;
    }

    .mini-step.active .mini-dot {
        background: #0ea5e9;
        border-color: #0ea5e9;
        color: white;
        box-shadow: 0 0 8px rgba(14, 165, 233, 0.35);
    }

    .mini-step.declined .mini-dot {
        background: #ef4444;
        border-color: #ef4444;
        color: white;
    }

    .mini-step.bypassed .mini-dot {
        background: #cbd5e1;
        border-color: #cbd5e1;
        color: #94a3b8;
    }

    .mini-line {
        width: 18px;
        height: 2.5px;
        background: var(--border-color);
        position: relative;
        z-index: 1;
        margin: 0 -2px;
    }

    .mini-line.completed {
        background: #059669;
    }

    .mini-label {
        font-size: 0.58rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-top: 4px;
        letter-spacing: 0.02em;
    }

    .mini-step.completed .mini-label {
        color: #059669;
    }

    .mini-step.active .mini-label {
        color: #0ea5e9;
        font-weight: 900;
    }

    .mini-step.declined .mini-label {
        color: #ef4444;
    }

    .mini-step.bypassed .mini-label {
        color: #94a3b8;
    }
</style>

<div style="padding:2rem; width:100%; box-sizing:border-box; overflow-x:hidden;">

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:2rem;">
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(5,150,105,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="clock" style="width:20px;color:#059669;"></i></div>
            <div>
                <div id="stats-pending" style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['pending'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Pending</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(220,38,38,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="alert-triangle" style="width:20px;color:#dc2626;"></i></div>
            <div>
                <div id="stats-urgent" style="font-size:1.5rem;font-weight:900;color:#dc2626;">{{ $stats['urgent'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Urgent</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(5,150,105,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="check-circle" style="width:20px;color:#059669;"></i></div>
            <div>
                <div id="stats-approved" style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['approved'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Approved</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(5,150,105,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="git-merge" style="width:20px;color:#059669;"></i></div>
            <div>
                <div id="stats-partially-approved" style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['partially_approved'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Partial</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(239,68,68,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="x-circle" style="width:20px;color:#ef4444;"></i></div>
            <div>
                <div id="stats-declined" style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['declined'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Declined</div>
            </div>
        </div>
    </div>

    {{-- Requisition Analytical Intelligence --}}
    @if(count($deptLabels) > 0)
    <div class="charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        {{-- Department Requisition Volume --}}
        <div class="glass-card" style="background: var(--bg-card); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-luxe);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="font-size: 0.9rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">
                    <i data-lucide="building" style="color: var(--primary); width: 18px; height: 18px;"></i>
                    Department Volume
                </h3>
            </div>
            <div id="deptRequisitionsChart" style="min-height: 280px;"></div>
        </div>

        {{-- Category Requests by Department --}}
        <div class="glass-card" style="background: var(--bg-card); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-luxe);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="font-size: 0.9rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">
                    <i data-lucide="bar-chart-3" style="color: var(--primary); width: 18px; height: 18px;"></i>
                    Categories by Department
                </h3>
            </div>
            <div id="deptCategoryChart" style="min-height: 280px;"></div>
        </div>

        {{-- Top Requested Items --}}
        <div class="glass-card" style="background: var(--bg-card); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-luxe);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="font-size: 0.9rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">
                    <i data-lucide="package" style="color: var(--primary); width: 18px; height: 18px;"></i>
                    Top Requested Items
                </h3>
            </div>
            <div id="topItemsChart" style="min-height: 280px;"></div>
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <div class="filter-card">
        <div class="filter-header">
            <i data-lucide="sliders-horizontal" style="width: 14px; height: 14px; color: #059669;"></i>
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



            <div class="filter-field-wrapper" style="flex: 1.2; min-width: 220px;">
                <i data-lucide="building" class="filter-icon" style="width: 15px; height: 15px;"></i>
                <input type="text" name="department" id="dept_input" value="{{ request('department') }}" placeholder="Filter by department..." class="filter-control" autocomplete="off">
            </div>

            @if(request()->anyFilled(['status','department','search_id']))
            <a href="{{ route('admin.requisitions') }}" class="filter-clear-btn">
                <i data-lucide="x-circle" style="width:16px; height:16px;"></i>
                <span>Clear Filters</span>
            </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div id="requisitions-table-container" style="background:var(--bg-card);border-radius:20px;border:1px solid var(--border-color);overflow:hidden;overflow-x:auto; transition: opacity 0.2s ease;">
        @include('admin._requisitions_table')
    </div>
</div>


{{-- SRA Review Modal (Admin & Stores unified/adapted) --}}
<div class="modal-overlay" id="sraOversightModal" onclick="if(event.target===this)closeSraOversightModal()">
    <div class="modal-box" style="background: var(--bg-card); border-radius: 24px; padding: 2.5rem; max-width: 960px; width: 95%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 60px rgba(0,0,0,0.2); margin: 30px auto; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <div style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--primary); letter-spacing: 0.06em; margin-bottom: 4px;" id="sra-modal-stage-title">Service SRA Review</div>
                <h2 id="sra-modal-number" style="font-size: 1.4rem; font-weight: 900; margin: 0; color: var(--text-main);">SRA-000000</h2>
            </div>
            <button onclick="closeSraOversightModal()" style="background: var(--bg-main); border: 1px solid var(--border-color); width: 36px; height: 36px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="x" style="width: 18px;"></i>
            </button>
        </div>

        <div id="sra-modal-details" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; background: var(--bg-main); border-radius: 16px; padding: 1.25rem 1.5rem; border: 1px solid var(--border-color);"></div>

        <div id="sra-modal-details-text" style="margin-bottom: 1.5rem;"></div>

        <div id="sra-modal-decision-form" style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                <i data-lucide="message-square" style="width: 14px; color: var(--primary);"></i>
                Notes / Remarks (optional)
            </label>
            <textarea id="sra-modal-notes" rows="3" style="width: 100%; border: 1.5px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; resize: vertical; box-sizing: border-box;" placeholder="Add notes..."></textarea>
            <div style="display: flex; gap: 1rem; margin-top: 1.25rem; justify-content: flex-end; flex-wrap: wrap;">
                <button onclick="processOversightSra('declined')" id="sraBtnDecline" style="padding: 0.85rem 2rem; border: 1px solid rgba(239,68,68,0.3); background: rgba(239,68,68,0.08); color: #ef4444; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="x-circle" style="width: 16px;"></i> Decline
                </button>
                <button onclick="processOversightSra('approved')" id="sraBtnApprove" style="padding: 0.85rem 2rem; border: none; background: #059669; color: white; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 8px 20px -5px rgba(5,150,105,0.4);">
                    <i data-lucide="check-circle" style="width: 16px;"></i> Approve
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentReqId = null;
    let currentReqData = null;

    let currentSraId = null;
    let currentSraStage = null;

    window.openSraOversightModal = async function(id, stage) {
        currentSraId = id;
        currentSraStage = stage;

        const sraModal = document.getElementById('sraOversightModal');
        if (sraModal && sraModal.parentElement !== document.body) {
            document.body.appendChild(sraModal);
        }
        document.getElementById('sra-modal-notes').value = '';
        document.getElementById('sraOversightModal').classList.add('open');
        document.getElementById('sra-modal-stage-title').textContent = stage === 'stores' ? 'Final Stores Review' : 'Admin SRA Review';
        document.getElementById('sra-modal-number').textContent = 'Loading...';
        document.getElementById('sra-modal-details').innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:1.5rem;color:var(--text-muted);">Fetching details...</div>';
        document.getElementById('sra-modal-details-text').innerHTML = '';

        try {
            const res = await fetch(`{{ url('/api/service-sra') }}/${id}`);
            const json = await res.json();
            if (!json.success) {
                Swal.fire('Error', 'Failed to fetch details.', 'error');
                closeSraOversightModal();
                return;
            }

            const sra = json.data;
            document.getElementById('sra-modal-number').textContent = sra.sra_number;

            const deliveryLabel = sra.delivery_type === 'full' ? 'Full Delivery' : 'Part Delivery';
            const deliveryColor = sra.delivery_type === 'full' ? '#059669' : '#059669';
            const deliveryBg = sra.delivery_type === 'full' ? 'rgba(5,150,105,0.1)' : 'rgba(5,150,105,0.1)';

            document.getElementById('sra-modal-details').innerHTML = `
                <div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Submitted By</div><div style="font-weight:700;color:var(--text-main);">${sra.submitter ? sra.submitter.name : '—'}</div><div style="font-size:0.75rem;color:var(--text-muted);">${sra.dept || ''}</div></div>
                <div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Supplier</div><div style="font-weight:700;color:var(--text-main);">${sra.supplier_name}</div>${sra.supplier_address ? `<div style="font-size:0.75rem;color:var(--text-muted);">${sra.supplier_address}</div>` : ''}</div>
                <div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Vehicle</div><div style="font-weight:600;color:var(--text-main);">${sra.vehicle_number || '—'}</div></div>
                <div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Date</div><div style="font-weight:700;color:var(--text-main);">${new Date(sra.date_of_delivery).toLocaleDateString()}</div></div>
                <div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Delivery Type</div><span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;font-size:0.7rem;font-weight:800;background:${deliveryBg};color:${deliveryColor};">${deliveryLabel}</span></div>
                ${sra.ae_number ? `<div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">A&E No.</div><div style="font-weight:600;">${sra.ae_number}</div></div>` : ''}
                ${sra.lpo_number ? `<div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">LPO No.</div><div style="font-weight:600;">${sra.lpo_number}</div></div>` : ''}
            `;

            document.getElementById('sra-modal-details-text').innerHTML = `
                <div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:8px;">Details of Order / Service</div>
                <div style="background:var(--bg-main);border-radius:12px;padding:1rem 1.25rem;font-size:0.88rem;font-weight:500;color:var(--text-main);white-space:pre-wrap;line-height:1.7;border:1px solid var(--border-color);">${sra.details}</div>
                ${sra.previous_sra_nos ? `<div style="margin-top:0.75rem;font-size:0.72rem;font-weight:800;color:#059669;">Previous SRA Nos: ${sra.previous_sra_nos}</div>` : ''}
            `;

            const isSraProcessed = sra.status === 'approved' || sra.status === 'declined' || (currentSraStage === 'admin' ? (sra.admin_status && sra.admin_status !== 'pending') : (sra.stores_status && sra.stores_status !== 'pending'));
            const isSraApproved = sra.status === 'approved' || (currentSraStage === 'admin' ? sra.admin_status === 'approved' : sra.stores_status === 'approved');
            const sraDecisionForm = document.getElementById('sra-modal-decision-form');
            if (isSraProcessed) {
                let noteVal = sra.stores_notes || sra.admin_notes || sra.auditor_notes || '';
                sraDecisionForm.innerHTML = `
                    ${noteVal ? `
                    <div style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.75rem 1rem; margin-bottom: 1.25rem;">
                        <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:4px;">Oversight Notes / Remarks</div>
                        <div style="font-size:0.9rem; font-weight:700; color:var(--text-main); font-style: italic;">"${noteVal}"</div>
                    </div>` : ''}
                    <div style="display: flex; gap: 1rem; margin-top: 1.25rem;">
                        ${isSraApproved ? `
                            <button style="flex:1; padding: 0.85rem 2rem; border: none; background: #059669; color: white; border-radius: 12px; cursor: default; pointer-events: none; font-weight: 950; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" disabled>
                                <i data-lucide="check-circle" style="width: 16px;"></i> Approved
                            </button>
                        ` : `
                            <button style="flex:1; padding: 0.85rem 2rem; border: none; background: #ef4444; color: white; border-radius: 12px; cursor: default; pointer-events: none; font-weight: 950; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" disabled>
                                <i data-lucide="x-circle" style="width: 16px;"></i> Declined
                            </button>
                        `}
                    </div>
                `;
            } else {
                sraDecisionForm.innerHTML = `
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                        <i data-lucide="message-square" style="width: 14px; color: var(--primary);"></i>
                        Notes / Remarks (optional)
                    </label>
                    <textarea id="sra-modal-notes" rows="3" style="width: 100%; border: 1.5px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; resize: vertical; box-sizing: border-box;" placeholder="Add notes..."></textarea>
                    <div style="display: flex; gap: 1rem; margin-top: 1.25rem; justify-content: flex-end; flex-wrap: wrap;">
                        <button onclick="processOversightSra('approved')" id="sraBtnApprove" style="padding: 0.85rem 2rem; border: none; background: #059669; color: white; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 8px 20px -5px rgba(5,150,105,0.4);">
                            <i data-lucide="check-circle" style="width: 16px;"></i> Approve
                        </button>
                    </div>
                `;
            }

            if (window.lucide) lucide.createIcons();
        } catch (e) {
            console.error(e);
            Swal.fire('Error', 'Network error. Please try again.', 'error');
            closeSraOversightModal();
        }
    };

    window.closeSraOversightModal = function() {
        document.getElementById('sraOversightModal').classList.remove('open');
        currentSraId = null;
        currentSraStage = null;
    };

    window.processOversightSra = function(action) {
        if (!currentSraId || !currentSraStage) return;
        const notes = document.getElementById('sra-modal-notes').value.trim();
        const label = action === 'approved' ? 'Approve' : 'Decline';

        Swal.fire({
            title: `${label} SRA?`,
            text: currentSraStage === 'admin' && action === 'approved' ? 'It will proceed to stores for final approval.' : 'This will record your decision immediately.',
            icon: action === 'approved' ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonText: label,
            confirmButtonColor: action === 'approved' ? '#059669' : '#ef4444',
            cancelButtonColor: '#64748b',
        }).then(async result => {
            if (!result.isConfirmed) return;

            const endpoint = currentSraStage === 'stores' 
                ? `{{ url('/stores/service-sra') }}/${currentSraId}/process`
                : `{{ url('/admin/service-sra') }}/${currentSraId}/process`;

            const $btn = document.getElementById(action === 'approved' ? 'sraBtnApprove' : 'sraBtnDecline');
            const origHtml = $btn.innerHTML;
            $btn.innerHTML = '<i data-lucide="loader" style="width:16px;"></i> Processing...';
            $btn.disabled = true;
            if (window.lucide) lucide.createIcons();

            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ action, notes }),
                });
                const json = await res.json();
                if (json.success) {
                    closeSraOversightModal();
                    Swal.fire('Success', json.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', json.message, 'error');
                    $btn.innerHTML = origHtml;
                    $btn.disabled = false;
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Network error. Please try again.', 'error');
                $btn.innerHTML = origHtml;
                $btn.disabled = false;
            }
        });
    };
    }

    let debounceTimer = null;

    function triggerFilterUpdate() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            updateFilters();
        }, 300); // 300ms debounce
    }

    function updateStats(stats) {
        const keys = ['pending', 'urgent', 'approved', 'partially_approved', 'declined'];
        keys.forEach(key => {
            const statsId = key === 'partially_approved' ? 'stats-partially-approved' : `stats-${key}`;
            const el = document.getElementById(statsId);
            if (el && stats[key] !== undefined) {
                el.textContent = stats[key];
            }
        });
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
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();

            // Controller returns rows + pagination (not a single html key)
            const html = data.html || ((data.rows || '') + (data.pagination || ''));
            if (html) {
                container.innerHTML = html;
            }
            if (data.stats) {
                updateStats(data.stats);
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

        const links = container.querySelectorAll('.ajax-req-page-btn');
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (!href || href.startsWith('javascript:') || href === '#') return;

            link.addEventListener('click', async function(e) {
                e.preventDefault();
                container.style.opacity = '0.5';
                try {
                    const response = await fetch(href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();

                    // Controller returns rows + pagination (not a single html key)
                    const pageHtml = data.html || ((data.rows || '') + (data.pagination || ''));
                    if (pageHtml) {
                        container.innerHTML = pageHtml;
                    }
                    if (data.stats) {
                        updateStats(data.stats);
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
        });
    }

    function cleanHtmlForComparison(element) {
        if (!element) return '';
        const clone = element.cloneNode(true);
        // Remove all icon elements and SVG expansions to prevent false change detection
        clone.querySelectorAll('svg, i, [data-lucide]').forEach(el => el.remove());
        return clone.innerHTML.replace(/\s+/g, ' ').trim();
    }

    async function pollStoreRequisitions() {
        const modal = document.getElementById('reqModal');
        if (modal && modal.classList.contains('open')) return;

        const sraModal = document.getElementById('sraOversightModal');
        if (sraModal && sraModal.classList.contains('open')) return;

        const form = document.getElementById('filter-form');
        const container = document.getElementById('requisitions-table-container');
        if (!form || !container) return;

        const formData = new FormData(form);
        const searchParams = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (key !== '_token' && value.trim() !== '') {
                searchParams.append(key, value);
            }
        }

        // Merge existing window location search params (like page, sorting, etc.)
        const urlParams = new URLSearchParams(window.location.search);
        for (const [key, value] of urlParams.entries()) {
            if (!searchParams.has(key)) {
                searchParams.append(key, value);
            }
        }

        const url = form.action + '?' + searchParams.toString();

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();

            // Controller returns rows + pagination keys (not a single html key)
            const newHtml = data.html || ((data.rows || '') + (data.pagination || ''));
            if (!newHtml) return;

            const parser = new DOMParser();
            const doc = parser.parseFromString(newHtml, 'text/html');

            const currentRows = Array.from(container.querySelectorAll('.req-table-row'));
            const newRows = Array.from(doc.querySelectorAll('.req-table-row'));

            const currentIds = currentRows.map(r => r.getAttribute('data-type') + '-' + r.getAttribute('data-id')).join(',');
            const newIds = newRows.map(r => r.getAttribute('data-type') + '-' + r.getAttribute('data-id')).join(',');

            const currentPagination = container.querySelector('.ajax-req-page-btn');
            const newPagination = doc.querySelector('.ajax-req-page-btn');
            const paginationMismatch = (currentPagination && !newPagination) || (!currentPagination && newPagination);

            if (currentIds !== newIds || paginationMismatch) {
                container.innerHTML = newHtml;
                if (window.lucide) {
                    window.lucide.createIcons();
                }
                bindPaginationClicks();
            } else {
                newRows.forEach((newRow, idx) => {
                    const currentRow = currentRows[idx];
                    if (!currentRow) return;

                    const newStatus = newRow.getAttribute('data-status');
                    const currentStatus = currentRow.getAttribute('data-status');
                    const newCollected = newRow.getAttribute('data-collected');
                    const currentCollected = currentRow.getAttribute('data-collected');

                    if (newStatus !== currentStatus || newCollected !== currentCollected || cleanHtmlForComparison(newRow) !== cleanHtmlForComparison(currentRow)) {
                        currentRow.innerHTML = newRow.innerHTML;
                        currentRow.className = newRow.className;
                        currentRow.setAttribute('data-status', newStatus);
                        if (newRow.hasAttribute('data-collected')) {
                            currentRow.setAttribute('data-collected', newCollected);
                        } else {
                            currentRow.removeAttribute('data-collected');
                        }
                        currentRow.setAttribute('style', newRow.getAttribute('style') || '');
                        
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    }
                });

                // Update pagination if it exists and changed
                const currentPag = container.querySelector('.ajax-req-page-btn')?.closest('div');
                const newPag = doc.querySelector('.ajax-req-page-btn')?.closest('div');
                if (currentPag && newPag) {
                    if (currentPag.innerText.replace(/\s+/g, ' ').trim() !== newPag.innerText.replace(/\s+/g, ' ').trim()) {
                        currentPag.innerHTML = newPag.innerHTML;
                        bindPaginationClicks();
                    }
                }
            }

            if (data.stats) {
                updateStats(data.stats);
            }
        } catch (e) {
            console.error('Requisitions polling error:', e);
        }
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

        const form = document.getElementById('filter-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                updateFilters();
            });
        }

        bindPaginationClicks();
        if (window.lucide) {
            window.lucide.createIcons();
        }

        // Auto-redirect to specific requisition review if open_id is present in query parameters
        const urlParams = new URLSearchParams(window.location.search);
        const openId = urlParams.get('open_id');
        if (openId) {
            window.location.href = `{{ url('/admin/requisitions') }}/${openId}/review`;
        }

        // Start polling every 10 seconds
        setInterval(pollStoreRequisitions, 10000);
    });
</script>
<script src="{{ asset('js/apexcharts.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        @if(count($deptLabels) > 0)
        // 1. Department Volume Chart (Donut)
        var deptOptions = {
            chart: {
                type: 'donut',
                height: 280,
                fontFamily: 'Outfit, sans-serif',
                background: 'transparent'
            },
            theme: {
                mode: document.documentElement.getAttribute('data-theme') || 'light'
            },
            series: @json($deptCounts),
            labels: @json($deptLabels),
            colors: ['#059669', '#0ea5e9', '#06b6d4', '#047857', '#3b82f6', '#10b981', '#0284c7', '#14b8a6', '#22c55e'],
            dataLabels: {
                enabled: false
            },
            legend: {
                position: 'bottom',
                fontSize: '11px',
                markers: {
                    radius: 12
                }
            },
            stroke: {
                width: 4,
                colors: [document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#ffffff']
            }
        };
        var deptChart = new ApexCharts(document.querySelector("#deptRequisitionsChart"), deptOptions);
        deptChart.render();

        // 2. Categories by Department (Stacked Column)
        var catOptions = {
            chart: {
                type: 'bar',
                height: 280,
                stacked: true,
                toolbar: {
                    show: false
                },
                fontFamily: 'Outfit, sans-serif',
                background: 'transparent'
            },
            theme: {
                mode: document.documentElement.getAttribute('data-theme') || 'light'
            },
            series: @json($categorySeries),
            xaxis: {
                categories: @json($uniqueDepts),
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            grid: {
                borderColor: 'var(--border-color)',
                strokeDashArray: 4
            },
            legend: {
                position: 'bottom',
                fontSize: '11px'
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 6
                }
            },
            colors: ['#059669', '#0ea5e9', '#06b6d4', '#047857', '#3b82f6', '#10b981', '#0284c7', '#14b8a6', '#22c55e']
        };
        var catChart = new ApexCharts(document.querySelector("#deptCategoryChart"), catOptions);
        catChart.render();

        // 3. Top Requested Items (Horizontal Bar stacked by Department)
        var itemsOptions = {
            chart: {
                type: 'bar',
                height: 280,
                stacked: true,
                toolbar: {
                    show: false
                },
                fontFamily: 'Outfit, sans-serif',
                background: 'transparent'
            },
            theme: {
                mode: document.documentElement.getAttribute('data-theme') || 'light'
            },
            series: @json($itemSeries),
            plotOptions: {
                bar: {
                    barHeight: '70%',
                    horizontal: true,
                    borderRadius: 4
                }
            },
            colors: ['#0ea5e9', '#059669', '#06b6d4', '#047857', '#3b82f6', '#10b981', '#0284c7', '#14b8a6', '#22c55e'],
            xaxis: {
                categories: @json($itemLabels),
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            grid: {
                borderColor: 'var(--border-color)',
                strokeDashArray: 4
            },
            legend: {
                position: 'bottom',
                fontSize: '11px'
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '10px'
                }
            }
        };
        var itemsChart = new ApexCharts(document.querySelector("#topItemsChart"), itemsOptions);
        itemsChart.render();

        // Theme toggle updates
        window.addEventListener('themeChanged', function(e) {
            const mode = e.detail.theme;
            deptChart.updateOptions({ theme: { mode: mode } });
            catChart.updateOptions({ theme: { mode: mode } });
            itemsChart.updateOptions({ theme: { mode: mode } });
        });
        @endif
    });
</script>
@endsection
