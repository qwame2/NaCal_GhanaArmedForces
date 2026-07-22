@extends('layouts.dashboard')
@section('content')
@php
    $isStoresHead = (auth()->user()->isMainAdminOrSub() || auth()->user()->role === 'Head of Stores' || strcasecmp(auth()->user()->department ?? '', 'Stores') === 0 || strcasecmp(auth()->user()->department ?? '', 'Store') === 0);
    if (!$isStoresHead) {
        $isBackup = (auth()->user()->isDepartmentHead() && in_array(auth()->user()->department, ['Human Resource Management Department', 'Welfare Department']));
        if ($isBackup) {
            $primaryOnline = \App\Models\User::where(function($q) {
                    $q->whereIn('role', ['Main Admin', 'Sub Main Admin'])
                      ->orWhere('role', 'Dept. Head (Stores)')
                      ->orWhereIn('department', ['Stores', 'Store']);
                })
                ->where('is_online', true)
                ->where('is_active', true)
                ->exists();
            if (!$primaryOnline) {
                $isStoresHead = true;
            }
        }
    }
@endphp
<style>
    :root {
        --store-orange: #22c55e;
        --store-orange-hover: #15803d;
        --store-orange-light: rgba(34, 197, 94, 0.08);
        --store-indigo: #16a34a;
        --store-indigo-hover: #16a34a;
        --store-indigo-light: rgba(22, 163, 74, 0.08);
        --success-color: #10b981;
        --warning-color: #10b981;
        --danger-color: #ef4444;
        --text-muted: #64748b;
        --shadow-premium: 0 20px 40px -15px rgba(15, 23, 42, 0.05), 0 0 0 1px rgba(15, 23, 42, 0.03);
        --shadow-hover: 0 30px 60px -15px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(15, 23, 42, 0.05);
    }

    .req-stat-card {
        background: var(--bg-card);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.2s;
    }

    .req-stat-card:hover {
        transform: translateY(-2px);
    }

    .req-table-row {
        border-bottom: 1px solid var(--border-color);
        transition: .15s;
    }

    .req-table-row:hover {
        background: rgba(22, 163, 74, .03);
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
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(6px);
        z-index: 99999 !important;
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
        animation: fadeInModal .35s cubic-bezier(0.16, 1, 0.3, 1);
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

    /* Priority-specific visual accents */
    .modal-box.urgent-priority { border-top: 6px solid #dc2626; }
    .modal-box.normal-priority { border-top: 6px solid #16a34a; }
    .modal-box.low-priority { border-top: 6px solid #64748b; }

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
        border-color: rgba(22, 163, 74, 0.25);
        background: rgba(22, 163, 74, 0.02);
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
        border: 1.5px solid rgba(22, 163, 74, 0.15);
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
        color: rgba(22, 163, 74, 0.08);
        position: absolute;
        top: -0.8rem;
        left: 0.5rem;
        font-family: Georgia, serif;
    }

    /* Item row card */
    .item-decision-card {
        border-bottom: 1px solid var(--border-color);
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        background: var(--bg-card);
    }

    .item-decision-card:last-child {
        border-bottom: none;
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

    /* Filter Cards */
    .filter-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.04);
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
        transition: all 0.2s ease;
        cursor: pointer;
        appearance: none;
    }

    select.filter-control {
        padding-right: 2.25rem;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19.5 8.25l-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 14px;
    }

    .filter-control:focus {
        border-color: #10b981;
        background: var(--bg-card);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
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

    /* Decision block */
    .decision-area {
        background: var(--bg-main);
        border: 1px solid var(--border-color);
        border-radius: 18px;
        padding: 1.75rem;
        margin-top: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .decision-text-area {
        width: 100%;
        height: 90px;
        padding: 1rem;
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-card);
        color: var(--text-main);
        font-family: inherit;
        font-weight: 600;
        font-size: 0.88rem;
        resize: none;
        outline: none;
        transition: 0.2s;
    }

    .decision-text-area:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.15);
    }

    /* --- CARD REQUISITION LIST VIEW --- */
    .history-item-box {
        border: 2px solid #fdba74;
        border-radius: 24px;
        padding: 1.75rem;
        margin-bottom: 1.5rem;
        background: var(--bg-card);
        box-shadow: var(--shadow-premium);
        position: relative;
        transition: all 0.25s ease;
    }

    .history-item-box:hover {
        box-shadow: var(--shadow-hover);
        border-color: var(--store-orange);
    }

    .history-item-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .history-ref {
        font-size: 0.82rem;
        font-weight: 800;
        color: #15803d;
        background: rgba(234, 88, 12, 0.08);
        padding: 5px 12px;
        border-radius: 99px;
        display: inline-block;
    }

    .history-meta-info {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .history-status-pills {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    /* --- STYLISH STEPPER TIMELINE --- */
    .order-tracker {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        background: var(--bg-main);
        padding: 1.25rem 2rem;
        border-radius: 18px;
        margin: 1.25rem 0;
        border: 1px solid var(--border-color);
        overflow: hidden;
    }

    .tracker-progress-line {
        position: absolute;
        top: 50%;
        left: 4rem;
        right: 4rem;
        height: 3px;
        background: var(--border-color);
        z-index: 1;
        transform: translateY(-50%);
    }

    .tracker-step {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 6px;
    }

    .tracker-dot {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--bg-card);
        border: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }

    .tracker-label {
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    /* Timeline Step States */
    .tracker-step.completed .tracker-dot {
        background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
        border-color: var(--success-color);
        color: white;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
    }

    .tracker-step.completed .tracker-label {
        color: var(--success-color);
    }

    .tracker-step.active .tracker-dot {
        background: linear-gradient(135deg, var(--store-orange) 0%, var(--store-orange-hover) 100%);
        border-color: var(--store-orange);
        color: white;
        animation: pulse-orange-stepper 2s infinite;
    }

    .tracker-step.active .tracker-label {
        color: var(--store-orange);
    }

    .tracker-step.declined .tracker-dot {
        background: linear-gradient(135deg, var(--danger-color) 0%, #b91c1c 100%);
        border-color: var(--danger-color);
        color: white;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);
    }

    .tracker-step.declined .tracker-label {
        color: var(--danger-color);
    }

    @keyframes pulse-orange-stepper {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
        70% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    @keyframes alertPulse {
        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        70% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    /* Premium Responsive Table Styles */
    .table-container {
        width: 100%;
        overflow-x: auto;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        box-shadow: var(--shadow-premium);
        margin-bottom: 2rem;
    }

    .oversight-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        min-width: 1100px;
    }

    .oversight-table th {
        padding: 1.25rem 1.5rem;
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        background: rgba(15, 23, 42, 0.01);
        border-bottom: 1px solid var(--border-color);
    }

    .oversight-table td {
        padding: 1.25rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.88rem;
        color: var(--text-main);
    }

    .oversight-table tr:last-child td {
        border-bottom: none;
    }

    .oversight-row {
        transition: all 0.2s ease;
    }

    .oversight-row:hover {
        background: rgba(22, 163, 74, 0.02);
    }

    /* Table Stepper/Tracker */
    .mini-tracker {
        display: flex;
        align-items: center;
        gap: 4px;
        position: relative;
        width: 100%;
        max-width: 160px;
        margin-top: 6px;
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
        border: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        transition: all 0.25s ease;
    }

    .mini-step.completed .mini-dot {
        background: var(--success-color);
        border-color: var(--success-color);
        color: white;
    }

    .mini-step.active .mini-dot {
        background: var(--store-orange);
        border-color: var(--store-orange);
        color: white;
        box-shadow: 0 0 8px rgba(34, 197, 94, 0.35);
    }

    .mini-step.declined .mini-dot {
        background: var(--danger-color);
        border-color: var(--danger-color);
        color: white;
    }

    .mini-line {
        flex: 1;
        height: 2px;
        background: var(--border-color);
        position: relative;
        z-index: 1;
    }

    .mini-line.completed {
        background: var(--success-color);
    }

    .mini-label {
        font-size: 0.6rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-top: 2px;
    }

    .mini-step.completed .mini-label {
        color: var(--success-color);
    }

    .mini-step.active .mini-label {
        color: var(--store-orange);
    }

    .mini-step.declined .mini-label {
        color: var(--danger-color);
    }

    /* Inline Items styling */
    .table-item-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.76rem;
        font-weight: 700;
        color: var(--text-main);
        background: var(--bg-main);
        border: 1px solid var(--border-color);
        padding: 4px 10px;
        border-radius: 8px;
        margin: 2px;
    }

    .table-item-qty {
        color: var(--store-orange);
        font-weight: 800;
    }

    .table-item-approved {
        color: var(--success-color);
        font-weight: 800;
    }

    .purpose-quote-inline {
        font-size: 0.82rem;
        color: var(--text-muted);
        font-weight: 600;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-top: 4px;
    }

    @media(max-width: 768px) {
        .oversight-table, .oversight-table thead, .oversight-table tbody, .oversight-table th, .oversight-table td, .oversight-table tr {
            display: block;
        }
        .oversight-table thead {
            display: none;
        }
        .oversight-table tr {
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            background: var(--bg-card);
            padding: 1.25rem;
            box-shadow: var(--shadow-premium);
        }
        .oversight-table td {
            border: none;
            padding: 0.65rem 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.88rem;
            text-align: right;
            border-bottom: 1px dashed var(--border-color);
        }
        .oversight-table td:last-child {
            border-bottom: none;
            justify-content: center;
            padding-top: 1rem;
        }
        .oversight-table td::before {
            content: attr(data-label);
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.05em;
            text-align: left;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        .mini-tracker {
            max-width: 100% !important;
            justify-content: flex-end;
        }
    }

    @media(max-width: 768px) {
        .order-tracker {
            flex-direction: column;
            align-items: flex-start;
            gap: 1.5rem;
            padding: 1.5rem;
        }
        .tracker-progress-line {
            left: 2.5rem;
            top: 2rem;
            bottom: 2rem;
            width: 3px;
            height: calc(100% - 4rem);
            transform: none;
        }
        .tracker-step {
            flex-direction: row;
            text-align: left;
            gap: 1rem;
        }
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
        border: 1.5px solid var(--border-color);
        box-shadow: 0 10px 30px rgba(22, 163, 74, 0.03);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .workflow-card-modern:hover {
        border-color: #c7d2fe;
        box-shadow: 0 16px 40px rgba(22, 163, 74, 0.06);
    }

    .workflow-cat-grid-modern {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1.25rem;
    }

    .workflow-cat-card-modern {
        background: var(--bg-main);
        border: 2px solid var(--border-color);
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
        background: linear-gradient(145deg, #f5f7ff 0%, #edf1ff 100%);
        border-color: #16a34a;
        box-shadow: 0 8px 24px rgba(22, 163, 74, 0.06);
    }

    .workflow-cat-card-modern.active:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(22, 163, 74, 0.1);
    }

    .workflow-cat-card-modern .corner-glow {
        position: absolute;
        top: -20px;
        right: -20px;
        width: 50px;
        height: 50px;
        background: radial-gradient(circle, rgba(22, 163, 74, 0.2) 0%, transparent 70%);
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
        color: #16a34a;
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
        background: linear-gradient(135deg, #16a34a, #3730a3);
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 4px 8px rgba(22, 163, 74, 0.18);
    }

    .workflow-cat-card-modern .status-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: #64748b;
        margin-top: 2px;
        transition: color 0.25s;
    }

    .workflow-cat-card-modern.active .status-label {
        color: #16a34a;
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
        background: #16a34a;
        border-color: #16a34a;
        box-shadow: 0 2px 6px rgba(22, 163, 74, 0.25);
    }

    .flow-line {
        flex: 1;
        height: 3px;
        transition: all 0.4s ease;
        background: #cbd5e1;
        margin-top: -20px;
    }

    .flow-line.active {
        background: #16a34a;
        box-shadow: 0 0 8px rgba(22, 163, 74, 0.25);
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

<div style="padding:2rem;">

    {{-- Header --}}
    <div style="margin-bottom:2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            @if(auth()->user()->role === 'Sub Main Admin')
                <div style="font-size:.7rem;font-weight:800;color:#10b981;text-transform:uppercase;letter-spacing:.12em;margin-bottom:4px;">{{ strtoupper(auth()->user()->department ?? auth()->user()->getRoleDisplayLabel()) }}</div>
            @elseif(auth()->user()->role === 'Main Admin')
                <div style="font-size:.7rem;font-weight:800;color:#10b981;text-transform:uppercase;letter-spacing:.12em;margin-bottom:4px;">{{ strtoupper(auth()->user()->department ?? auth()->user()->getRoleDisplayLabel()) }} · Head of Administration</div>
            @elseif(auth()->user()->role === 'Auditor')
                <div style="font-size:.7rem;font-weight:800;color:#10b981;text-transform:uppercase;letter-spacing:.12em;margin-bottom:4px;">{{ strtoupper(auth()->user()->department ?? auth()->user()->getRoleDisplayLabel()) }} · Department Head</div>
            @else
                <div style="font-size:.7rem;font-weight:800;color:#10b981;text-transform:uppercase;letter-spacing:.12em;margin-bottom:4px;">{{ strtoupper(auth()->user()->department ?? auth()->user()->getRoleDisplayLabel()) }} · Department Head Hub</div>
            @endif
            <h1 style="font-size:1.75rem;font-weight:900;color:var(--text-main);letter-spacing:-.03em;margin:0;">Oversight & Approvals</h1>
            @php
                $isBackupActive = $isStoresHead && !in_array(strtoupper(auth()->user()->department ?? ''), ['STORES', 'STORE']);
            @endphp
            @if($isBackupActive)
                <div style="margin-top: 8px; display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 99px; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; font-size: 0.75rem; font-weight: 800;">
                    <span>ACTING AS STORES DEPARTMENT HEAD (DELEGATED AUTHORITY)</span>
                </div>
            @endif
        </div>
        <button onclick="window.location.reload()" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--text-main); border-radius:12px; border: 1px solid var(--border-color);">
            <i data-lucide="refresh-cw" style="width: 18px;"></i>
            Refresh
        </button>
    </div>

    {{-- Stats Cards --}}
    <div id="oversight-stats-container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.5rem;margin-bottom:2rem;">
        <div class="req-stat-card">
            <div style="width:48px;height:48px;background:rgba(22,163,74,.1);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="clock" style="width:24px;color:#16a34a;"></i></div>
            <div>
                <div style="font-size:1.75rem;font-weight:950;color:var(--text-main); line-height: 1.1;">{{ $stats['pending'] }}</div>
                <div style="font-size:.75rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-top:2px;">Awaiting My Review</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:48px;height:48px;background:rgba(16,185,129,.1);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="check-circle" style="width:24px;color:#10b981;"></i></div>
            <div>
                <div style="font-size:1.75rem;font-weight:950;color:var(--text-main); line-height: 1.1;">{{ $stats['approved'] }}</div>
                <div style="font-size:.75rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-top:2px;">Approved by Me</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:48px;height:48px;background:rgba(239,68,68,.1);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="x-circle" style="width:24px;color:#ef4444;"></i></div>
            <div>
                <div style="font-size:1.75rem;font-weight:950;color:var(--text-main); line-height: 1.1;">{{ $stats['declined'] }}</div>
                <div style="font-size:.75rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-top:2px;">Declined by Me</div>
            </div>
        </div>
    </div>

    @if(auth()->user()->role === 'Main Admin' && !in_array(auth()->user()->department, ['Human Resource Management Department', 'Welfare Department']))
    @php
        $selectedCats = \App\Models\Setting::get('stores_dept_head_approval_categories', []);
        if (!is_array($selectedCats)) {
            $selectedCats = json_decode($selectedCats, true) ?? [];
        }
        $dgSelectedCats = \App\Models\Setting::get('dg_approval_categories', []);
        if (!is_array($dgSelectedCats)) {
            $dgSelectedCats = json_decode($dgSelectedCats, true) ?? [];
        }
    @endphp
    {{-- Stores Department Head Approval Workflow --}}
    <div class="workflow-card-modern" style="display: none;">
        <div class="cfg-card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); padding: 2.25rem 2.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1.25rem;">
                <div class="cfg-icon-box" style="background: linear-gradient(135deg, #16a34a 0%, #3730a3 100%); box-shadow: 0 8px 20px rgba(22,163,74,0.15); width: 50px; height: 50px; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white;">
                    <i data-lucide="shield-check" style="width: 24px; height: 24px; color: white;"></i>
                </div>
                <div>
                    <h3 style="font-weight: 955; font-size: 1.25rem; color: #0f172a; margin: 0; letter-spacing: -0.03em;">Stores Dept. Head Approval Workflow</h3>
                    <p style="color: #64748b; font-weight: 600; font-size: 0.82rem; margin: 4px 0 0;">Select the specific item categories that require intermediate review by the Department Head (Stores).</p>
                </div>
            </div>
            <span id="workflow-active-badge" style="background: rgba(22,163,74,0.08); color: #16a34a; font-size: 0.72rem; font-weight: 800; padding: 6px 14px; border-radius: 30px; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(22,163,74,0.15); box-shadow: 0 2px 4px rgba(22,163,74,0.02); transition: all 0.3s ease;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: #16a34a; transition: all 0.3s ease;" id="workflow-badge-dot"></span>
                <span id="workflow-badge-text" style="letter-spacing: 0.02em;">Active Categories: {{ count($selectedCats) }}</span>
            </span>
        </div>
        <div class="cfg-card-body" style="padding: 2.5rem; background: #ffffff;">
            <form action="{{ route('admin.settings.update') }}" method="POST" id="core-configs-dashboard">
                @csrf
                <input type="hidden" name="settings_form" value="1">
                <input type="hidden" name="stores_dept_head_approval_categories_present" value="1">

                <!-- Hidden real multi-select to preserve native settings submission -->
                <select name="stores_dept_head_approval_categories[]" id="stores_dept_head_approval_categories" multiple="multiple" style="display: none;">
                    @foreach($ledgeMap ?? [] as $code => $name)
                    <option value="{{ $code }}" {{ in_array($code, $selectedCats) ? 'selected' : '' }}>{{ $code }}</option>
                    @endforeach
                </select>

                <!-- Hidden real multi-select for DG categories to avoid JS failures (Read-Only) -->
                <select id="dg_approval_categories" multiple="multiple" style="display: none;">
                    @foreach($ledgeMap ?? [] as $code => $name)
                    <option value="{{ $code }}" {{ in_array($code, $dgSelectedCats) ? 'selected' : '' }}>{{ $code }}</option>
                    @endforeach
                </select>

                <div style="display: flex; flex-direction: column; gap: 2rem;">

                    <!-- Premium Interactive Card Selection Grid -->
                    <div class="workflow-cat-grid-modern">
                        @foreach($ledgeMap ?? [] as $code => $name)
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
                                <div style="font-weight: 855; font-size: 0.88rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $name }}</div>
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
                        <div style="background: linear-gradient(135deg, rgba(22, 163, 74, 0.03) 0%, rgba(22, 163, 74, 0.01) 100%);
                                        border: 1.5px solid #edf2f7;
                                        border-radius: 24px;
                                        padding: 1.75rem 2rem;
                                        display: flex;
                                        gap: 1.25rem;
                                        align-items: flex-start;">
                            <div style="width: 42px; height: 42px; background: rgba(22,163,74,0.06); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #16a34a; flex-shrink: 0; margin-top: 2px;">
                                <i data-lucide="info" style="width: 20px; height: 20px;"></i>
                            </div>
                            <div style="flex: 1;">
                                <h5 style="margin: 0 0 6px 0; font-size: 0.95rem; font-weight: 855; color: #1e293b; letter-spacing: -0.010em;">Smart Routing Protocol Active</h5>
                                <p style="margin: 0; font-size: 0.8rem; color: #475569; line-height: 1.6; font-weight: 600;">
                                    When item categories are configured above, any submitted requisition containing matching items will be routed for manual review by the <strong>Department Head (Stores)</strong> prior to final confirmation. Requisitions consisting solely of bypassed categories skip the Stores Department Head approval stage completely, saving processing time and avoiding administration bottlenecks.
                                </p>
                            </div>
                        </div>

                        <!-- Dynamic Mini Infographic Visualizer Card -->
                        <div style="background: linear-gradient(to bottom, #fafbff, #ffffff); border: 1.5px solid #edf2f7; border-radius: 24px; padding: 1.75rem 2rem; display: flex; flex-direction: column; justify-content: center; gap: 1.25rem; box-shadow: 0 4px 20px rgba(0,0,0,0.015);">
                            <div style="font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; text-align: center; margin-bottom: 0.25rem;">Live Approval Routing Pathway</div>

                            <div style="display: flex; align-items: center; justify-content: space-between; position: relative; width: 100%; padding: 0.5rem 0;" class="flow-nodes-container">

                                <!-- Origin Node -->
                                <div class="flow-node" style="display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 2; position: relative; width: 68px;">
                                    <div class="flow-node-icon" style="background: linear-gradient(135deg, #16a34a, #3730a3); color: white; box-shadow: 0 4px 12px rgba(22,163,74,0.15); width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                        <i data-lucide="user-check" style="width: 15px; height: 15px;"></i>
                                    </div>
                                    <span style="font-size: 0.65rem; font-weight: 855; color: #1e293b; white-space: nowrap;">Dept. Head</span>
                                    <span class="flow-node-badge" style="background: #e0e7ff; color: #16a34a; font-size: 0.55rem; font-weight: 800; padding: 1px 6px; border-radius: 30px; transition: all 0.3s ease;">Required</span>
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
                                    <div class="flow-node-icon" style="background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 4px 12px rgba(16,185,129,0.15); width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
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

                    <!-- Submit trigger -->
                    <div style="display: flex; justify-content: flex-end; margin-top: 1rem; margin-bottom: 1.5rem;">
                        <button type="submit" style="padding: 0.75rem 2rem; border-radius: 12px; border: none; background: #16a34a; color: white; font-weight: 800; font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;" onmouseover="this.style.background='#3730a3'" onmouseout="this.style.background='#16a34a'">
                            <i data-lucide="save" style="width: 18px; height: 18px;"></i> Save Workflow Changes
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
 
    @endif

    {{-- Staff Access Provisioning (Non-Stores Department Heads only) --}}
    {{-- Staff Access & Registration Approvals (All Department Heads) --}}
    @php
        $isBackupActive = $isStoresHead && !in_array(strtoupper(auth()->user()->department ?? ''), ['STORES', 'STORE']);
        $hideProvisioning = ($isStoresHead && !$isBackupActive) || auth()->user()->role === 'Auditor';
    @endphp
    <div id="provisioningSection" style="background:var(--bg-card);border-radius:20px;border:1px solid var(--border-color);padding:1.75rem;margin-bottom:2rem;box-shadow:0 4px 20px rgba(0,0,0,0.04); display: {{ $hideProvisioning ? 'none' : 'block' }};">
        @if(!empty($hasOverdueReturn))
        <div style="background: linear-gradient(135deg, rgba(254, 242, 242, 0.65) 0%, rgba(254, 226, 226, 0.35) 100%); border-left: 5px solid #ef4444; border-top: 1px solid rgba(239, 68, 68, 0.1); border-right: 1px solid rgba(239, 68, 68, 0.1); border-bottom: 1px solid rgba(239, 68, 68, 0.1); border-radius: 16px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 1.25rem; box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.05); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
            <div style="width: 40px; height: 40px; background: rgba(239, 68, 68, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; animation: alertPulse 2s infinite;">
                <i data-lucide="alert-triangle" style="width: 20px; height: 20px; color: #ef4444;"></i>
            </div>
            <div style="flex: 1; display: flex; flex-direction: column; gap: 0.25rem;">
                <div style="font-family: 'Outfit', sans-serif; font-size: 0.9rem; font-weight: 900; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.03em;">
                    Access Suspended
                </div>
                <div style="font-size: 0.85rem; color: #7f1d1d; font-weight: 600; line-height: 1.5;">
                    Your department currently has overdue temporary assets. Access to provision temporary requisitioner accounts is suspended until all overdue items are officially logged as returned.
                </div>
                <div style="margin-top: 0.85rem;">
                    <a href="{{ route('requisitions.overdue') }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 0.6rem 1.2rem; font-size: 0.78rem; font-weight: 850; color: white; background: #ef4444; border-radius: 10px; text-decoration: none; border: 1px solid rgba(239, 68, 68, 0.2); transition: all 0.25s ease; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.25);" onmouseover="this.style.background='#dc2626'; this.style.boxShadow='0 6px 20px rgba(239, 68, 68, 0.35)'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='#ef4444'; this.style.boxShadow='0 4px 15px rgba(239, 68, 68, 0.25)'; this.style.transform='translateY(0)';">
                        <i data-lucide="eye" style="width: 14px; height: 14px;"></i> View Overdue Assets
                    </a>
                </div>
            </div>
        </div>
        @endif

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
            <div style="display:flex;align-items:center;gap:0.85rem;">
                <div style="width:42px;height:42px;background:linear-gradient(135deg,rgba(16,185,129,0.15),rgba(5,150,105,0.1));border-radius:12px;display:flex;align-items:center;justify-content:center;border:1px solid rgba(16,185,129,0.2);">
                    <i data-lucide="user-plus" style="width:20px;height:20px;color:#10b981;"></i>
                </div>
                <div>
                    <div style="font-size:.68rem;font-weight:800;color:#10b981;text-transform:uppercase;letter-spacing:.1em;">Dept. Access Management</div>
                    <div style="font-size:1rem;font-weight:800;color:var(--text-main);margin-top:1px;">Staff Access &amp; Approvals</div>
                </div>
            </div>
        </div>

        @if(!$hideProvisioning)
        {{-- Department Staff Accounts Table --}}
        <div id="tempAccountsContainer" style="margin-bottom: 2rem;">
            <div style="font-size:.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:0.85rem;">Department Staff Access &amp; Permissions</div>
            <div id="tempAccountsList">
                <div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.85rem;">
                    <i data-lucide="loader" style="width:18px;height:18px;display:inline-block;margin-bottom:6px;opacity:.5;"></i><br>Loading department staff directory...
                </div>
            </div>
        </div>
        @endif

        {{-- Department Pending Registrations Table --}}
        <div id="pendingRegistrationsContainer">
            <div style="font-size:.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:0.85rem;">Pending Staff Registrations</div>
            <div id="pendingRegistrationsList">
                <div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.85rem;">
                    <i data-lucide="loader" style="width:18px;height:18px;display:inline-block;margin-bottom:6px;opacity:.5;"></i><br>Loading pending registrations...
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Toolbar --}}
    <div class="filter-card">
        <div class="filter-header">
            <i data-lucide="sliders-horizontal" style="width:14px;height:14px;color:#10b981;"></i>
            <span>Filter Criteria</span>
        </div>
        <form method="GET" class="filter-row" id="filter-form" action="{{ route('main-admin.requisitions') }}">
            {{-- Status --}}
            @php
                $defaultStatus = (auth()->user()->role === 'Auditor') ? 'approved' : 'pending';
            @endphp
            <div class="filter-field-wrapper" style="min-width:200px;flex:1.5;">
                <i data-lucide="activity" class="filter-icon" style="width:14px;height:14px;"></i>
                <select name="status" class="filter-control" id="filter-status">
                    <option value="pending"  {{ request('status', $defaultStatus)==='pending'  ?'selected':'' }}>Awaiting My Review</option>
                    <option value="approved" {{ request('status', $defaultStatus)==='approved' ?'selected':'' }}>Approved History</option>
                    <option value="declined" {{ request('status', $defaultStatus)==='declined' ?'selected':'' }}>Declined History</option>
                    <option value="history"  {{ request('status', $defaultStatus)==='history'  ?'selected':'' }}>Oversight History (All)</option>
                </select>
            </div>

            {{-- Type / SRA Filter --}}
            <div class="filter-field-wrapper" style="min-width:180px;flex:1.3;">
                <i data-lucide="layers" class="filter-icon" style="width:14px;height:14px;"></i>
                <select name="type" class="filter-control" id="filter-type">
                    <option value="">All Request Types</option>
                    <option value="inventory_sra" {{ request('type')==='inventory_sra' ?'selected':'' }}>Inventory SRA</option>
                    <option value="service_sra"   {{ request('type')==='service_sra'   ?'selected':'' }}>Service SRA</option>
                    <option value="requisition"   {{ request('type')==='requisition'   ?'selected':'' }}>Store Requisition</option>
                </select>
            </div>

            {{-- Department --}}
            <div class="filter-field-wrapper" style="min-width:200px;flex:1.5;">
                <i data-lucide="building" class="filter-icon" style="width:14px;height:14px;"></i>
                <input type="text" name="department" id="filter-department" value="{{ request('department') }}" placeholder="Filter by department..." class="filter-control" autocomplete="off">
            </div>
            {{-- Date From --}}
            <div class="filter-field-wrapper" style="min-width:160px;flex:1;">
                <i data-lucide="calendar" class="filter-icon" style="width:14px;height:14px;"></i>
                <input type="date" name="date_from" id="filter-date-from" value="{{ request('date_from') }}" class="filter-control" title="From date">
            </div>
            {{-- Date To --}}
            <div class="filter-field-wrapper" style="min-width:160px;flex:1;">
                <i data-lucide="calendar" class="filter-icon" style="width:14px;height:14px;"></i>
                <input type="date" name="date_to" id="filter-date-to" value="{{ request('date_to') }}" class="filter-control" title="To date">
            </div>
            {{-- Clear --}}
            <button type="button" id="filter-clear-btn" class="filter-clear-btn" style="display:none;">
                <i data-lucide="x-circle" style="width:16px;height:16px;"></i>
                <span>Clear</span>
            </button>
        </form>
    </div>

    {{-- Requisition Table --}}
    <div id="oversight-table-wrapper" style="position:relative;">

        {{-- Loading overlay --}}
        <div id="table-loading" style="display:none;position:absolute;inset:0;background:rgba(var(--bg-card-rgb,255,255,255),.75);backdrop-filter:blur(2px);z-index:10;border-radius:20px;align-items:center;justify-content:center;">
            <div style="display:flex;align-items:center;gap:10px;padding:1rem 1.75rem;background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;box-shadow:0 8px 24px rgba(0,0,0,0.06);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" style="animation:spin 0.7s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                <span style="font-size:0.82rem;font-weight:800;color:var(--text-muted);">Loading...</span>
            </div>
        </div>

        <div class="table-container">
            <table class="oversight-table">
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Requester &amp; Dept</th>
                        <th>Items Requested</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>Usage</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="req-tbody">
                    @include('requisitions._req_table_rows')
                </tbody>
            </table>
        </div>

        {{-- Pagination (dynamic) --}}
        <div id="req-pagination-wrap">
            @include('requisitions._req_pagination')
        </div>
    </div>
    </div>

    @push('scripts')
    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
        #table-loading { display:none; }
        #table-loading.active { display:flex !important; }
    </style>
    <script>
    (function() {
        const ROUTE   = '{{ route('main-admin.requisitions') }}';
        const CSRF    = '{{ csrf_token() }}';
        let currentPage = 1;
        let debounceTimer = null;

        // --- Helpers ---
        function getFilters() {
            return {
                status:      document.getElementById('filter-status')?.value || '',
                type:        document.getElementById('filter-type')?.value || '',
                department:  document.getElementById('filter-department')?.value || '',
                date_from:   document.getElementById('filter-date-from')?.value || '',
                date_to:     document.getElementById('filter-date-to')?.value || '',
                page:        currentPage,
            };
        }

        function hasActiveFilters(f) {
            return f.department || f.date_from || f.date_to || f.type ||
                   (f.status && f.status !== 'pending');
        }

        function showLoading(on) {
            const el = document.getElementById('table-loading');
            if (el) el.classList.toggle('active', on);
        }

        function updateClearBtn(f) {
            const btn = document.getElementById('filter-clear-btn');
            if (btn) btn.style.display = hasActiveFilters(f) ? 'inline-flex' : 'none';
        }

        // --- Main fetch ---
        function fetchTable(page) {
            currentPage = page || 1;
            const f = getFilters();
            updateClearBtn(f);

            const params = new URLSearchParams();
            for (const [key, val] of Object.entries(f)) {
                if (val !== null && val !== undefined && val.toString().trim() !== '') {
                    if (key === 'page' && parseInt(val) === 1) {
                        continue;
                    }
                    params.append(key, val);
                }
            }
            const queryString = params.toString();
            const url = ROUTE + (queryString ? '?' + queryString : '');

            // Update browser URL without reload
            history.replaceState(null, '', url);

            if (window.renderSkeletonTable) {
                window.renderSkeletonTable('req-tbody', 5, 8);
            }

            showLoading(true);
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                }
            })
            .then(r => r.json())
            .then(data => {
                const tbody = document.getElementById('req-tbody');
                const pagWrap = document.getElementById('req-pagination-wrap');
                if (tbody)   tbody.innerHTML   = data.rows;
                if (pagWrap) pagWrap.innerHTML  = data.pagination;
                // Re-init lucide icons for newly injected HTML
                if (window.lucide) lucide.createIcons();
                // Re-bind pagination clicks
                bindPaginationClicks();
                showLoading(false);
            })
            .catch(() => showLoading(false));
        }

        // --- Bind pagination link clicks ---
        function bindPaginationClicks() {
            document.querySelectorAll('.ajax-page-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = parseInt(this.dataset.page);
                    if (!isNaN(page)) fetchTable(page);
                });
            });
        }

        // --- Wire filters ---
        function wireFilters() {
            // Instant on select/date change
            ['filter-status', 'filter-type', 'filter-date-from', 'filter-date-to'].forEach(function(id) {
                const el = document.getElementById(id);
                if (el) el.addEventListener('change', function() { fetchTable(1); });
            });
            // Debounced on text input
            const deptInput = document.getElementById('filter-department');
            if (deptInput) {
                deptInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function() { fetchTable(1); }, 400);
                });
            }
            // Clear button
            const clearBtn = document.getElementById('filter-clear-btn');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    document.getElementById('filter-status').value    = '{{ $defaultStatus }}';
                    if (document.getElementById('filter-type')) document.getElementById('filter-type').value = '';
                    document.getElementById('filter-department').value = '';
                    document.getElementById('filter-date-from').value = '';
                    document.getElementById('filter-date-to').value   = '';
                    fetchTable(1);
                });
            }
        }

        // --- Init ---
        document.addEventListener('DOMContentLoaded', function() {
            // Clean up empty parameters from the address bar on load
            const urlObj = new URL(window.location.href);
            let hasEmpty = false;
            const cleanParams = new URLSearchParams();
            for (const [key, val] of urlObj.searchParams.entries()) {
                if (val.trim() === '') {
                    hasEmpty = true;
                } else {
                    cleanParams.append(key, val);
                }
            }
            if (hasEmpty) {
                const cleanQuery = cleanParams.toString();
                const cleanUrl = urlObj.pathname + (cleanQuery ? '?' + cleanQuery : '');
                window.history.replaceState(null, '', cleanUrl);
            }

            const form = document.getElementById('filter-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    fetchTable(1);
                });
            }

            // Show clear button if filters already active on load
            updateClearBtn(getFilters());
            wireFilters();
            bindPaginationClicks();
        });
    })();
    </script>
    @endpush

{{-- Oversight Approval & Detail Modal --}}
<div class="modal-overlay" id="reqModal" onclick="if(event.target===this)closeModal()">
    <div class="modal-box">
        <div style="padding:1.5rem 2rem;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:1rem;">
                <div style="width:44px;height:44px;background:rgba(16,185,129,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="shield-check" style="width:20px;color:#10b981;"></i>
                </div>
                <div>
                    <h2 style="margin:0;font-size:1.1rem;font-weight:900;color:var(--text-main);">Strategic Oversight Review</h2>
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

{{-- SRA Review Modal (Admin & Stores unified/adapted) --}}
<div class="modal-overlay" id="sraOversightModal" onclick="if(event.target===this)closeSraOversightModal()">
    <div class="modal-box" style="background: var(--bg-card); border-radius: 24px; padding: 2.5rem; max-width: 680px; width: 95%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 60px rgba(0,0,0,0.2); margin: 30px auto; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <div style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--primary); letter-spacing: 0.06em; margin-bottom: 4px;" id="sra-modal-stage-title">Service SRA Review</div>
                <h2 id="sra-modal-number" style="font-size: 1.4rem; font-weight: 900; margin: 0; color: var(--text-main);">SRA-000000</h2>
            </div>
            <button onclick="closeSraOversightModal()" style="background: var(--bg-main); border: 1px solid var(--border-color); width: 36px; height: 36px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="x" style="width: 18px;"></i>
            </button>
        </div>

        <div id="sra-modal-details" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; background: var(--bg-main); border-radius: 14px; padding: 1.25rem;"></div>

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
                <button onclick="processOversightSra('approved')" id="sraBtnApprove" style="padding: 0.85rem 2rem; border: none; background: linear-gradient(135deg, #10b981, #059669); color: white; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 8px 20px -5px rgba(16,185,129,0.4);">
                    <i data-lucide="check-circle" style="width: 16px;"></i> Approve
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const isStoresHead = {{ $isStoresHead ? 'true' : 'false' }};
    const isBackupActive = {{ ($isStoresHead && !in_array(strtoupper(auth()->user()->department ?? ''), ['STORES', 'STORE'])) ? 'true' : 'false' }};
    let currentReqId = null;

    async function openRequisitionModal(id) {
        currentReqId = id;
        const reqModal = document.getElementById('reqModal');
        if (!reqModal) {
            console.error('Modal element #reqModal not found in DOM.');
            return;
        }
        reqModal.classList.add('open');
        document.getElementById('modalBody').innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted);"><div style="width:24px;height:24px;border:2px solid rgba(0,0,0,.1);border-top-color:var(--primary);border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 10px;"></div>Loading details...</div>';
        document.getElementById('modalFooter').innerHTML = '';
        document.getElementById('modalSubtitle').textContent = 'Loading...';

        try {
            const res = await fetch(`{{ url('/admin/requisitions') }}/${id}/show`);
            const data = await res.json();
            if (!res.ok || !data || !data.items) {
                Swal.fire('Error', data?.message || 'Failed to load requisition details.', 'error');
                closeModal();
                return;
            }
            window.currentReqData = data;

        // Apply priority border accents
        const modalBox = document.querySelector('.modal-box');
        modalBox.className = 'modal-box';
        modalBox.classList.add(`${data.priority}-priority`);

        document.getElementById('modalSubtitle').textContent = `Requisition Ref: ${data.unique_id || ('REQ-' + String(data.id).padStart(5, '0'))}`;

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
                const dateParts = rawDate.split('-');
                if (dateParts.length === 3 && dateParts[0].length === 4) {
                    const y = dateParts[0].substring(2);
                    const m = dateParts[1];
                    const d = dateParts[2];
                    formattedDate = `${d}/${m}/${y}`;
                } else {
                    const dateObj = new Date(rawDate);
                    if (!isNaN(dateObj.getTime())) {
                        const day = String(dateObj.getDate()).padStart(2, '0');
                        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                        const year = String(dateObj.getFullYear()).substring(2);
                        formattedDate = `${day}/${month}/${year}`;
                    }
                }
            } catch(e) {}
            returnDateBannerHtml = `
            <div style="background:rgba(16, 185, 129, 0.06); border:1px solid rgba(16, 185, 129, 0.25); border-radius:12px; padding:0.85rem 1.15rem; display:flex; align-items:center; gap:10px; color:#047857; font-weight:800; font-size:0.88rem; margin-top:0.5rem; margin-bottom:0.25rem; box-shadow:0 2px 8px rgba(16, 185, 129, 0.03);">
                <i data-lucide="calendar-clock" style="width:16px; height:16px; color:#047857; flex-shrink:0;"></i>
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
                        <i data-lucide="award" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:3px;"></i>${data.rank_or_title || 'No Rank/Title'}
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
                    ${isStoresHead && data.origin_approved_by ? `
                    <div style="font-size:.7rem;color:#10b981;font-weight:750;margin-top:4px;display:inline-flex;align-items:center;gap:3px;background:rgba(16,185,129,0.06);padding:2px 8px;border-radius:6px;border:1px solid rgba(16,185,129,0.15);width:fit-content;">
                        <i data-lucide="shield-check" style="width:11px;height:11px;"></i>Approved by: ${data.origin_approved_by}
                    </div>
                    ` : ''}
                </div>
            </div>

            <div class="profile-card" style="grid-column: 1 / -1; display:flex; flex-direction:column; align-items:stretch; gap:0.75rem;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.04em;">Requisition Purpose</span>
                    <div class="stat-pill-group" style="display:flex; gap:0.5rem; align-items:center;">
                        <span class="stat-pill" style="background:${data.usage_type_badge.bg}; color:${data.usage_type_badge.color}; font-weight:800;"><i data-lucide="${data.usage_type === 'temporary' ? 'calendar' : 'package-check'}" style="width:12px;"></i> ${data.usage_type_badge.label}</span>
                    </div>
                </div>
                ${returnDateBannerHtml}
                <div class="purpose-quote">
                    ${purposeText}
                </div>
            </div>
        </div>
        `;

        // Render Requested Items
        const rows = data.items.map(item => {
            const requested = parseFloat(item.quantity_requested) || 0;
            let approved = item.quantity_approved !== null ? parseFloat(item.quantity_approved) : null;
            const altApproved = item.alternative_quantity_approved !== null ? parseFloat(item.alternative_quantity_approved) : 0;
            
            if (approved === 0 && altApproved > 0 && (data.alternative_status === 'agreed' || data.alternative_status === 'proposed')) {
                approved = Math.max(0, requested - altApproved);
            }
            
            const totalApproved = approved !== null ? (approved + altApproved) : null;

            const stockInfo = item.stock_sufficient ?
                `<span style="color:#10b981;font-size:.7rem;font-weight:700;">✔ Sufficient Stock</span>` :
                `<span style="color:#ef4444;font-size:.7rem;font-weight:700;">⚠ Short Stock</span>`;

            const stockLine = isStoresHead && !isBackupActive ?
                ` · Stock: ${parseFloat(item.current_stock).toLocaleString()} (${stockInfo})` :
                '';

            // If stores has approved/processed the requisition items, show full tracking details
            if (totalApproved !== null) {
                const pct = requested > 0 ? Math.min(Math.round((totalApproved / requested) * 100), 100) : 0;
                let fulfillBadgeBg = 'rgba(16, 185, 129, 0.1)';
                let fulfillBadgeColor = '#10b981';
                let fulfillLabel = `${pct}% Fulfill`;

                if (totalApproved === 0) {
                    fulfillBadgeBg = 'rgba(239, 68, 68, 0.1)';
                    fulfillBadgeColor = '#ef4444';
                    fulfillLabel = 'Declined';
                } else if (totalApproved < requested) {
                    fulfillBadgeBg = 'rgba(16, 185, 129, 0.1)';
                    fulfillBadgeColor = '#10b981';
                    fulfillLabel = `${pct}% Reduced`;
                }

                return `
                <div class="item-decision-card" style="border-bottom: 1px solid var(--border-color); padding: 1.5rem; background: var(--bg-card); display:flex; flex-direction:column; gap:1rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; width:100%;">
                        <div>
                            ${item.alternative_description ? `
                                <div style="font-size:.95rem;font-weight:800;color:var(--text-main); display:flex; align-items:center; gap:6px;">
                                    <span>${item.description}</span>
                                    <span style="font-size:0.75rem; font-weight:800; color:#10b981;">(Approved: ${approved.toLocaleString()} ${item.unit})</span>
                                </div>
                                <div style="font-size:.92rem;font-weight:800;color:var(--store-orange); display:flex; align-items:center; gap:6px; margin-top:4px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:2px;"><path d="M16 3h5v5"/><path d="M8 21H3v-5"/><path d="M21 3 14 10"/><path d="M3 21 10 14"/></svg>
                                    Alternative: ${item.alternative_description}
                                    <span style="font-size:0.75rem; font-weight:800;">(${data.alternative_status === 'proposed' ? 'Pending Approval' : 'Approved'}: ${altApproved.toLocaleString()} ${item.unit})</span>
                                </div>
                                ${data.alternative_status === 'proposed' && (requested - approved - altApproved) > 0 ? `
                                <div style="font-size:.92rem;font-weight:800;color:var(--text-muted); display:flex; align-items:center; gap:6px; margin-top:4px; opacity:0.85;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:2px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                    ${item.description}
                                    <span style="font-size:0.75rem; font-weight:800;">(Pending Approval: ${(requested - approved - altApproved).toLocaleString()} ${item.unit})</span>
                                </div>
                                ` : ''}
                            ` : `
                                <div style="font-size:.95rem;font-weight:800;color:var(--text-main);">${item.description}</div>
                            `}
                            <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;margin-top:4px;">
                                Unit: ${item.unit}${stockLine}
                            </div>
                        </div>
                        <span class="pill" style="background:${fulfillBadgeBg}; color:${fulfillBadgeColor}; font-weight:800; font-size:0.68rem; padding:3px 10px; border-radius:99px;">${fulfillLabel}</span>
                    </div>

                    <div class="item-card-panel" style="background:var(--bg-main); border-radius:12px; padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between; gap:1.5rem; flex-wrap:wrap; border:1px solid var(--border-color); width:100%; box-sizing:border-box;">
                        <div style="flex:1; min-width:80px;">
                            <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;">Requested</div>
                            <div style="font-size:1.1rem;font-weight:800;color:var(--text-main);margin-top:2px;">${requested.toLocaleString()}</div>
                        </div>

                        <div style="flex:1; min-width:80px;">
                            <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;">Approved</div>
                            <div style="font-size:1.15rem;font-weight:900;color:${totalApproved === 0 ? '#ef4444' : '#10b981'};margin-top:2px;">${totalApproved.toLocaleString()}</div>
                        </div>

                        <div style="flex:2; min-width:180px;">
                            <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;margin-bottom:6px;">Fulfillment Progress</div>
                            <div style="background:rgba(0,0,0,0.05); height:6px; border-radius:10px; overflow:hidden; width:100%;">
                                <div style="height:100%; width: ${pct}%; background:${approved === 0 ? '#ef4444' : (approved < requested ? '#10b981' : 'linear-gradient(90deg, #16a34a 0%, #10b981 100%)')}; border-radius:10px;"></div>
                            </div>
                        </div>
                    </div>

                    ${item.remarks ? `
                    <div style="background:rgba(0,0,0,0.015); border:1.5px dashed var(--border-color); border-radius:10px; padding:0.75rem 1rem; margin-top:0.25rem;">
                        <span style="font-size:0.65rem; font-weight:900; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:4px; letter-spacing:0.04em;">Store Remarks</span>
                        <p style="margin:0; font-size:0.8rem; color:var(--text-main); font-style:italic; line-height:1.4;">"${item.remarks}"</p>
                    </div>` : ''}
                </div>`;
            }

            // Otherwise, default pending item rows
            return `
            <div class="item-decision-card">
                <div class="item-card-header">
                    <div class="item-card-header-left">
                        <div>
                            <div style="font-size:.95rem;font-weight:800;color:var(--text-main);">${item.description}</div>
                            <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;margin-top:4px;">
                                Unit: ${item.unit}${stockLine}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="item-card-panel" style="gap:1.5rem;">
                    <div style="flex:1; min-width:80px;">
                        <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;">Requested Quantity</div>
                        <div style="font-size:1.15rem;font-weight:900;color:var(--primary);margin-top:2px;">${requested.toLocaleString()}</div>
                    </div>
                </div>
            </div>`;
        }).join('');

        const itemRowsHtml = `
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:16px;overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.01);">
            ${rows}
            <div style="background:var(--bg-main); padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                <span style="font-size:0.75rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em;">Requisition Payload Summary</span>
                <div class="stat-pill-group">
                    <span class="stat-pill"><i data-lucide="layers" style="width:12px;"></i> ${totalItemsCount} ${totalItemsCount === 1 ? 'Item' : 'Items'}</span>
                    <span class="stat-pill"><i data-lucide="hash" style="width:12px;"></i> Total Qty: ${totalQtyRequested.toLocaleString()}</span>
                </div>
            </div>
        </div>`;

        // Check if processed already
        const isActingAsHOD = isBackupActive && (data.department === "{{ auth()->user()->department }}");
        let isProcessed = false;
        if (isStoresHead && !isActingAsHOD) {
            if (data.status !== 'pending') {
                isProcessed = true;
            } else if (data.origin_admin_status === 'pending') {
                isProcessed = true;
            } else if (data.main_admin_status === 'approved' && data.requires_dg_approval && data.dg_status !== 'approved') {
                isProcessed = true;
            }
        } else {
            isProcessed = (data.origin_admin_status !== 'pending' && data.alternative_status !== 'proposed');
        }
        let decisionHtml = '';

        if (isStoresHead && !isActingAsHOD && data.origin_admin_status === 'pending') {
            decisionHtml = `
            <div style="background: rgba(22, 163, 74, 0.05); border: 1.5px dashed rgba(22, 163, 74, 0.25); border-radius: 16px; padding: 1.25rem; margin-top: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width:34px; height:34px; background:rgba(22, 163, 74, 0.1); color:#16a34a; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                        <i data-lucide="clock" style="width:16px; height:16px; color:#16a34a;"></i>
                    </div>
                    <div>
                        <h4 style="margin:0; font-size:0.85rem; font-weight:800; color:var(--text-main); text-transform:uppercase; letter-spacing:0.04em;">Pending HOD Approval</h4>
                        <p style="margin:2px 0 0; font-size:0.75rem; color:var(--text-muted); font-weight:600;">This requisition must be approved by the originating department head first.</p>
                    </div>
                </div>
            </div>`;
        }

        if (isStoresHead && data.main_admin_status === 'approved' && data.requires_dg_approval && data.dg_status !== 'approved') {
            decisionHtml = `
            <div style="background: rgba(139, 92, 246, 0.05); border: 1.5px dashed rgba(139, 92, 246, 0.25); border-radius: 16px; padding: 1.25rem; margin-top: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width:34px; height:34px; background:rgba(139, 92, 246, 0.1); color:#4ade80; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                        <i data-lucide="clock" style="width:16px; height:16px; color:#4ade80;"></i>
                    </div>
                    <div>
                        <h4 style="margin:0; font-size:0.85rem; font-weight:800; color:var(--text-main); text-transform:uppercase; letter-spacing:0.04em;">Pending DG Approval</h4>
                        <p style="margin:2px 0 0; font-size:0.75rem; color:var(--text-muted); font-weight:600;">This requisition must be approved by the Director General (DG) before final checkout.</p>
                    </div>
                </div>
            </div>`;
        }

        if (!isProcessed) {
            if (data.alternative_status === 'proposed' && !isStoresHead) {
                // Render suggested quantity proposal review buttons with Yes/No choices and comment box
                decisionHtml = `
                <div class="decision-area animate-slide-up" style="background: rgba(34, 197, 94, 0.04); border: 1.5px dashed rgba(34, 197, 94, 0.2); border-radius: 16px; padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                    <div style="font-size: 0.72rem; font-weight: 800; color: var(--store-orange); text-transform: uppercase; letter-spacing: 0.05em; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="shuffle" style="width: 14px; color: var(--store-orange);"></i>
                        Suggested Quantity Proposal
                    </div>
                    <div style="font-size: 0.88rem; color: var(--text-main); font-weight: 700; line-height: 1.5;">
                        The Head of Stores has suggested modified quantities for this requisition. Please review the item breakdown above. Do you agree to accept the suggested quantity allocations?
                    </div>
                    
                    <div style="display: flex; gap: 1.5rem; align-items: center; margin-top: 0.25rem; margin-bottom: 0.25rem;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem; font-weight: 800; color: var(--text-main);">
                            <input type="radio" name="altAgreement" id="altAgreeYes" value="yes" onchange="checkAltOptions()" style="width: 18px; height: 18px; accent-color: #10b981; cursor: pointer;">
                            Yes
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem; font-weight: 800; color: var(--text-main);">
                            <input type="radio" name="altAgreement" id="altAgreeNo" value="no" onchange="checkAltOptions()" style="width: 18px; height: 18px; accent-color: #ef4444; cursor: pointer;">
                            No
                        </label>
                    </div>

                    <!-- Options when selecting No -->
                    <div id="noOptionSection" style="display: none; flex-direction: column; gap: 0.75rem; border-left: 3px solid #ef4444; padding-left: 1.25rem; margin-top: 0.25rem; margin-bottom: 0.25rem; transition: all 0.3s ease;">
                        <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em;">Choose an action:</div>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.88rem; font-weight: 700; color: var(--text-main);">
                                <input type="radio" name="noActionType" id="noActionCancel" value="cancel" onchange="checkAltOptions()" style="width: 16px; height: 16px; accent-color: #ef4444; cursor: pointer;">
                                Cancel this Requisition Request
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.88rem; font-weight: 700; color: var(--text-main);">
                                <input type="radio" name="noActionType" id="noActionComment" value="comment" onchange="checkAltOptions()" style="width: 16px; height: 16px; accent-color: #ef4444; cursor: pointer;">
                                Add a Comment / Feedback
                            </label>
                        </div>
                    </div>

                    <textarea id="decisionNotes" class="decision-text-area" style="display: none;" oninput="checkAltOptions()" placeholder="Enter your comments or feedback regarding the suggested quantity decision..."></textarea>

                    <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
                        <button id="declineAltBtn" onclick="processAlternativeResponse('decline')" disabled style="flex:1; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1.5px solid rgba(239, 68, 68, 0.25); padding: 0.75rem; border-radius: 12px; font-weight: 800; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem; opacity: 0.4;" onmouseover="if(!this.disabled){this.style.background='#ef4444'; this.style.color='white';}" onmouseout="if(!this.disabled){this.style.background='rgba(239, 68, 68, 0.1)'; this.style.color='#ef4444';}">
                            <i data-lucide="x-circle" style="width: 18px;"></i>
                            <span id="declineAltBtnText">Decline Suggested Qty</span>
                        </button>
                        <button id="agreeAltBtn" onclick="processAlternativeResponse('agree')" disabled style="flex:1.5; background: #10b981; color: white; border: none; padding: 0.75rem; border-radius: 12px; font-weight: 900; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem; opacity: 0.4; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);" onmouseover="if(!this.disabled)this.style.background='#059669';" onmouseout="if(!this.disabled)this.style.background='#10b981';">
                            <i data-lucide="check-circle" style="width: 18px;"></i>
                            Agree to Suggested Qty
                        </button>
                    </div>
                </div>`;
            } else {
                const isAwaitingDg = !isActingAsHOD && data.requires_dg_approval && (data.dg_status !== 'approved');
                // Render standard decision actions
                decisionHtml = `
                <div class="decision-area animate-slide-up">
                    <div style="font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="message-square" style="width: 14px; color: var(--primary);"></i>
                        Oversight Decision Form
                    </div>
                    <textarea id="decisionNotes" class="decision-text-area" placeholder="Enter notes or comments regarding this decision (Optional notes for Head, required reason if declining)..." ${isAwaitingDg ? 'disabled' : ''}></textarea>

                    <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
                        <button onclick="processDecision('declined')" style="flex:1; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1.5px solid rgba(239, 68, 68, 0.25); padding: 0.75rem; border-radius: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem;" onmouseover="this.style.background='#ef4444'; this.style.color='white';" onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'; this.style.color='#ef4444';">
                            <i data-lucide="x-circle" style="width: 18px;"></i>
                            Decline Request
                        </button>
                        ${isAwaitingDg ? `
                        <button disabled style="flex:1.5; background: #cbd5e1; color: #94a3b8; border: none; padding: 0.75rem; border-radius: 12px; font-weight: 900; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem;" title="Cannot approve: this requisition is awaiting Director General (DG) approval.">
                            <i data-lucide="lock" style="width: 18px;"></i>
                            Approve (Awaiting DG Approval)
                        </button>
                        ` : `
                        <button onclick="processDecision('approved')" style="flex:1.5; background: #10b981; color: white; border: none; padding: 0.75rem; border-radius: 12px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);" onmouseover="this.style.background='#059669';" onmouseout="this.style.background='#10b981';">
                            <i data-lucide="check-circle" style="width: 18px;"></i>
                            Approve
                        </button>
                        `}
                    </div>
                </div>`;
            }
        } else {
            // Render decision log status
            const statusVal = (isStoresHead && !isActingAsHOD) ? data.main_admin_status : data.origin_admin_status;
            let decisionLabel = statusVal === 'approved' ? 'APPROVED & ESCALATED' : 'DECLINED';
            let decisionColor = statusVal === 'approved' ? '#10b981' : '#ef4444';
            
            if (data.alternative_status === 'agreed') {
                decisionLabel = 'SUGGESTED QUANTITY AGREED';
                decisionColor = '#10b981';
            } else if (data.alternative_status === 'declined') {
                decisionLabel = 'SUGGESTED QUANTITY DECLINED';
                decisionColor = '#ef4444';
            }

            let decisionBg = decisionColor === '#10b981' ? 'rgba(16, 185, 129, 0.05)' : 'rgba(239, 68, 68, 0.05)';
            let decisionBorder = decisionColor === '#10b981' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)';

            let readOnlyBtnHtml = '';
            if (statusVal === 'approved' || data.alternative_status === 'agreed') {
                readOnlyBtnHtml = `
                <div style="display: flex; gap: 0.75rem; margin-top: 1rem;">
                    <button style="flex:1; background: #10b981; color: white; border: none; padding: 0.75rem; border-radius: 12px; font-weight: 950; cursor: default; pointer-events: none; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem;" disabled>
                        <i data-lucide="check-circle" style="width: 18px;"></i>
                        Approved
                    </button>
                </div>`;
            } else {
                readOnlyBtnHtml = `
                <div style="display: flex; gap: 0.75rem; margin-top: 1rem;">
                    <button style="flex:1; background: #ef4444; color: white; border: none; padding: 0.75rem; border-radius: 12px; font-weight: 950; cursor: default; pointer-events: none; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem;" disabled>
                        <i data-lucide="x-circle" style="width: 18px;"></i>
                        Declined
                    </button>
                </div>`;
            }

            decisionHtml = `
            <div style="background: ${decisionBg}; border: 1.5px dashed ${decisionBorder}; border-radius: 16px; padding: 1.25rem; margin-top: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px dashed ${decisionBorder}; padding-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width:34px; height:34px; background:${decisionColor}15; color:${decisionColor}; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                            <i data-lucide="shield-check" style="width:16px;"></i>
                        </div>
                        <div>
                            <h4 style="margin:0; font-size:0.85rem; font-weight:800; color:var(--text-main); text-transform:uppercase; letter-spacing:0.04em;">My Oversight Log</h4>
                        </div>
                    </div>
                    <span class="pill" style="background:${decisionBg}; color:${decisionColor}; font-weight:800; font-size:0.7rem; padding:4px 10px;">${decisionLabel}</span>
                </div>
                ${data.admin_notes ? `
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.75rem 1rem;">
                    <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Oversight Notes / Feedback</div>
                    <div style="font-size:0.9rem; font-weight:700; color:var(--text-main); font-style: italic;">"${data.admin_notes}"</div>
                </div>` : ''}
                ${data.decline_reason ? `
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.75rem 1rem;">
                    <div style="font-size:0.68rem; font-weight:800; color:#ef4444; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Reason for Decline</div>
                    <div style="font-size:0.9rem; font-weight:700; color:#7f1d1d;">${data.decline_reason}</div>
                </div>` : ''}
                ${readOnlyBtnHtml}
            </div>`;
        }

        // Stores Feedback Details
        let storesFeedbackHtml = '';
        if (data.status !== 'pending') {
            let storeStatusLabel = 'PROCESSING';
            let storeStatusColor = '#16a34a';
            let storeStatusBg = 'rgba(22, 163, 74, 0.05)';
            let storeStatusBorder = 'rgba(22, 163, 74, 0.2)';

            if (data.status === 'approved') {
                storeStatusLabel = 'STORES APPROVED';
                storeStatusColor = '#10b981';
                storeStatusBg = 'rgba(16, 185, 129, 0.05)';
                storeStatusBorder = 'rgba(16, 185, 129, 0.2)';
            } else if (data.status === 'partially_approved') {
                storeStatusLabel = 'STORES PARTIALLY APPROVED';
                storeStatusColor = '#10b981';
                storeStatusBg = 'rgba(16, 185, 129, 0.05)';
                storeStatusBorder = 'rgba(16, 185, 129, 0.2)';
            } else if (data.status === 'declined') {
                storeStatusLabel = 'STORES DECLINED';
                storeStatusColor = '#ef4444';
                storeStatusBg = 'rgba(239, 68, 68, 0.05)';
                storeStatusBorder = 'rgba(239, 68, 68, 0.2)';
            }

            storesFeedbackHtml = `
            <div style="background: ${storeStatusBg}; border: 1.5px dashed ${storeStatusBorder}; border-radius: 16px; padding: 1.25rem; margin-top: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px dashed ${storeStatusBorder}; padding-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width:34px; height:34px; background:${storeStatusColor}15; color:${storeStatusColor}; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                            <i data-lucide="package" style="width:16px;"></i>
                        </div>
                        <div>
                            <h4 style="margin:0; font-size:0.85rem; font-weight:800; color:var(--text-main); text-transform:uppercase; letter-spacing:0.04em;">Stores Decision</h4>
                        </div>
                    </div>
                    <span class="pill" style="background:${storeStatusBg}; color:${storeStatusColor}; font-weight:800; font-size:0.7rem; padding:4px 10px;">${storeStatusLabel}</span>
                </div>
                ${data.admin_notes ? `
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.75rem 1rem;">
                    <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Store Officer Notes</div>
                    <div style="font-size:0.9rem; font-weight:700; color:var(--text-main); font-style: italic;">"${data.admin_notes}"</div>
                </div>` : ''}
                ${data.decline_reason ? `
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.75rem 1rem;">
                    <div style="font-size:0.68rem; font-weight:800; color:#ef4444; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Reason for Decline</div>
                    <div style="font-size:0.9rem; font-weight:700; color:#7f1d1d;">${data.decline_reason}</div>
                </div>` : ''}
            </div>`;
        }

        // Collector Information
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
                                <p style="margin:0; font-size:0.75rem; color:var(--text-muted);">Items have been physically issued and collected</p>
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
                            <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Location</div>
                            <div style="font-size:0.9rem; font-weight:900; color:var(--text-main);">${data.collector_location || 'N/A'}</div>
                        </div>
                        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.75rem 1rem;">
                            <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Confirmed By (Store Staff)</div>
                            <div style="font-size:0.9rem; font-weight:900; color:var(--text-main);">${data.collected_by_name || 'Store Staff'}</div>
                        </div>
                        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:0.75rem 1rem;">
                            <div style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px;">Collection Date & Time</div>
                            <div style="font-size:0.9rem; font-weight:900; color:var(--text-main);">${data.collected_at || 'N/A'}</div>
                        </div>
                    </div>
                </div>`;
            } else {
                collectorInfoHtml = `
                <div style="background:rgba(16, 185, 129, 0.03); border:1.5px dashed rgba(16, 185, 129, 0.25); border-radius:16px; padding:1.25rem; margin-top:1.25rem; display:flex; align-items:center; gap:10px; color:#047857; font-weight:800; font-size:0.85rem;">
                    <i data-lucide="clock" style="width:16px; height:16px; color:#047857; flex-shrink:0;"></i>
                    <span>Status: Requisition is approved by stores. Awaiting physical collection by staff.</span>
                </div>`;
            }
        }

        document.getElementById('modalBody').innerHTML = `
        ${profileGridHtml}

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; margin-top:1.5rem;">
            <h3 style="margin:0; font-size:0.95rem; font-weight:900; color:var(--text-main); display:flex; align-items:center; gap:6px;">
                <i data-lucide="list-checks" style="width:16px; color:#10b981;"></i> Requested Items
            </h3>
        </div>

        ${itemRowsHtml}
        ${decisionHtml}
        ${storesFeedbackHtml}
        ${collectorInfoHtml}
        `;

        let footerHtml = `
        <button onclick="closeModal()" style="background:var(--bg-main); color:var(--text-main); border:1.5px solid var(--border-color); padding:.75rem 1.5rem; border-radius:12px; font-weight:800; cursor:pointer; font-size:.88rem; transition:0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.03)'" onmouseout="this.style.background='var(--bg-main)'">
            Close Panel
        </button>`;

        if (data.collected_at) {
            footerHtml = `
            <a href="{{ request()->getBasePath() }}/requisitions/receipt/${id}" target="_blank"
                style="background:rgba(22, 163, 74, 0.08); border: 1.5px solid rgba(22, 163, 74, 0.2); color: #16a34a; padding: .75rem 1.5rem; border-radius: 12px; font-weight: 800; cursor: pointer; font-size: .88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; margin-right: auto;" onmouseover="this.style.background='#16a34a'; this.style.color='white';" onmouseout="this.style.background='rgba(22, 163, 74, 0.08)'; this.style.color='#16a34a';">
                <i data-lucide="printer" style="width: 16px;"></i> Print Collection Receipt
            </a>` + footerHtml;
        }

        document.getElementById('modalFooter').innerHTML = footerHtml;

        lucide.createIcons();
        checkAltOptions();
        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'Network error. Failed to load requisition details.', 'error');
            closeModal();
        }
    }

    async function processDecision(decision) {
        const notes = document.getElementById('decisionNotes').value.trim();

        if (decision === 'declined' && !notes) {
            Swal.fire({
                title: 'Strategic Security Alert',
                text: 'A formal decline reason must be recorded in the feedback form before de-activating a requisition request.',
                icon: 'warning',
                confirmButtonColor: '#16a34a'
            });
            return;
        }

        const isActingAsHOD = isBackupActive && (window.currentReqData && window.currentReqData.department === "{{ auth()->user()->department }}");

        Swal.fire({
            title: decision === 'approved' ? 'Approve?' : 'Decline Requisition?',
            text: decision === 'approved' ?
                (isStoresHead && !isActingAsHOD ?
                'This will verify the request and route it immediately to the Head of Stores for final volume allocations.' :
                'This will verify the request and route it immediately to the Department Head (Stores) for review.') :
                'This will de-activate the requisition and return it as declined to the requesting department.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: decision === 'approved' ? 'Yes' : 'Yes, Decline',
            cancelButtonText: 'Abort',
            confirmButtonColor: decision === 'approved' ? '#10b981' : '#ef4444',
            cancelButtonColor: '#ef4444',
            customClass: {
                confirmButton: 'premium-swal-btn',
                cancelButton: 'premium-swal-cancel-btn'
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Syncing Decision...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                try {
                    const url = (window.currentReqData && window.currentReqData.main_admin_status === 'approved')
                        ? `{{ url('/admin/requisitions') }}/${currentReqId}/process`
                        : `{{ url('/main-admin/requisitions') }}/${currentReqId}/process`;
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            status: decision,
                            admin_notes: notes,
                            decline_reason: decision === 'declined' ? notes : null
                        })
                    });

                    const responseData = await res.json();

                    if (responseData.success) {
                        if (typeof window.playNotificationSound === 'function') {
                            window.playNotificationSound('sent');
                        }
                        Swal.fire({
                            title: 'Success!',
                            text: responseData.message || 'Requisition processed successfully.',
                            icon: 'success',
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            closeModal();
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Failure!',
                            text: responseData.message || 'An error occurred during submission.',
                            icon: 'error',
                            confirmButtonColor: '#16a34a'
                        });
                    }
                } catch (e) {
                    Swal.fire({
                        title: 'Failure!',
                        text: 'Critical communication sync error.',
                        icon: 'error',
                        confirmButtonColor: '#16a34a'
                    });
                }
            }
        });
    }

    function closeModal() {
        const reqModal = document.getElementById('reqModal');
        if (reqModal) {
            reqModal.classList.remove('open');
        }
    }

    function checkAltOptions() {
        const radioYes = document.getElementById('altAgreeYes');
        const radioNo = document.getElementById('altAgreeNo');
        const noOptionSection = document.getElementById('noOptionSection');
        const noActionCancel = document.getElementById('noActionCancel');
        const noActionComment = document.getElementById('noActionComment');
        const textarea = document.getElementById('decisionNotes');
        
        const agreeBtn = document.getElementById('agreeAltBtn');
        const declineBtn = document.getElementById('declineAltBtn');
        const declineText = document.getElementById('declineAltBtnText');
        
        if (!agreeBtn || !declineBtn) return;

        if (radioYes && radioYes.checked) {
            // YES selected: Hide 'No' options and textarea
            if (noOptionSection) noOptionSection.style.display = 'none';
            if (textarea) textarea.style.display = 'none';
            
            agreeBtn.disabled = false;
            agreeBtn.style.opacity = '1';
            agreeBtn.style.cursor = 'pointer';
            
            declineBtn.disabled = true;
            declineBtn.style.opacity = '0.4';
            declineBtn.style.cursor = 'not-allowed';
            if (declineText) declineText.textContent = 'Decline Suggested Qty';
        } else if (radioNo && radioNo.checked) {
            // NO selected: Show 'No' options, disable Agree button
            if (noOptionSection) noOptionSection.style.display = 'flex';
            
            agreeBtn.disabled = true;
            agreeBtn.style.opacity = '0.4';
            agreeBtn.style.cursor = 'not-allowed';
            
            if (noActionCancel && noActionCancel.checked) {
                // Cancel selected: Hide comment box, enable Decline/Cancel button
                if (textarea) textarea.style.display = 'none';
                
                declineBtn.disabled = false;
                declineBtn.style.opacity = '1';
                declineBtn.style.cursor = 'pointer';
                if (declineText) declineText.textContent = 'Cancel Requisition Request';
            } else if (noActionComment && noActionComment.checked) {
                // Comment selected: Show comment box, enable Decline only if comment is filled
                if (textarea) textarea.style.display = 'block';
                if (declineText) declineText.textContent = 'Decline & Send Feedback';
                
                const hasComment = textarea && textarea.value.trim().length > 0;
                if (hasComment) {
                    declineBtn.disabled = false;
                    declineBtn.style.opacity = '1';
                    declineBtn.style.cursor = 'pointer';
                } else {
                    declineBtn.disabled = true;
                    declineBtn.style.opacity = '0.4';
                    declineBtn.style.cursor = 'not-allowed';
                }
            } else {
                // No action selected yet: Hide comment box, disable Decline button
                if (textarea) textarea.style.display = 'none';
                
                declineBtn.disabled = true;
                declineBtn.style.opacity = '0.4';
                declineBtn.style.cursor = 'not-allowed';
                if (declineText) declineText.textContent = 'Decline Suggested Qty';
            }
        } else {
            // Nothing selected: Hide everything, disable both buttons
            if (noOptionSection) noOptionSection.style.display = 'none';
            if (textarea) textarea.style.display = 'none';
            
            agreeBtn.disabled = true;
            agreeBtn.style.opacity = '0.4';
            agreeBtn.style.cursor = 'not-allowed';
            
            declineBtn.disabled = true;
            declineBtn.style.opacity = '0.4';
            declineBtn.style.cursor = 'not-allowed';
            if (declineText) declineText.textContent = 'Decline Suggested Qty';
        }
    }

    async function processAlternativeResponse(response) {
        const notes = document.getElementById('decisionNotes').value.trim();
        const noActionCancel = document.getElementById('noActionCancel');
        const noActionComment = document.getElementById('noActionComment');

        const isCancelling = noActionCancel && noActionCancel.checked;
        const isDecliningWithComment = noActionComment && noActionComment.checked;

        if (response === 'decline') {
            if (isDecliningWithComment && !notes) {
                Swal.fire({
                    title: 'Feedback Required',
                    text: 'Please enter a comment or feedback regarding this decision in the text area.',
                    icon: 'warning',
                    confirmButtonColor: '#16a34a'
                });
                return;
            }
        }

        let confirmTitle = 'Agree to Suggested Quantity?';
        let confirmText = 'This will confirm your department\'s agreement to the suggested quantity proposal. Requisition will return to stores for final allocation.';
        let confirmBtnText = 'Yes, Agree';
        let confirmColor = '#10b981';

        if (response === 'decline') {
            confirmColor = '#ef4444';
            if (isCancelling) {
                confirmTitle = 'Cancel Requisition Request?';
                confirmText = 'This will fully cancel and decline the requisition request.';
                confirmBtnText = 'Yes, Cancel Request';
            } else {
                confirmTitle = 'Decline & Send Feedback?';
                confirmText = 'This will decline the suggested quantity proposal and cancel the requisition with your feedback.';
                confirmBtnText = 'Yes, Decline';
            }
        }

        Swal.fire({
            title: confirmTitle,
            text: confirmText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: confirmBtnText,
            cancelButtonText: 'Abort',
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#ef4444',
            customClass: {
                confirmButton: 'premium-swal-btn',
                cancelButton: 'premium-swal-cancel-btn'
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Submitting Response...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                try {
                    const res = await fetch(`{{ url('/main-admin/requisitions') }}/${currentReqId}/alternative-response`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            response: response,
                            notes: notes
                        })
                    });

                    const responseData = await res.json();

                    if (responseData.success) {
                        if (typeof window.playNotificationSound === 'function') {
                            window.playNotificationSound('sent');
                        }
                        Swal.fire({
                            title: 'Success!',
                            text: responseData.message || 'Alternative response processed successfully.',
                            icon: 'success',
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            closeModal();
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Failure!',
                            text: responseData.message || 'An error occurred during submission.',
                            icon: 'error',
                            confirmButtonColor: '#16a34a'
                        });
                    }
                } catch (e) {
                    Swal.fire({
                        title: 'Failure!',
                        text: 'Critical communication sync error.',
                        icon: 'error',
                        confirmButtonColor: '#16a34a'
                    });
                }
            }
        });
    }

    async function loadProvisioningData(isSilent = false) {
        const pendingContainer = document.getElementById('pendingRegistrationsList');
        const tempContainer = document.getElementById('tempAccountsList');
        
        if (!pendingContainer && !tempContainer) return;

        if (!isSilent) {
            if (pendingContainer) {
                pendingContainer.innerHTML = `
                    <div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.85rem;">
                        <i data-lucide="loader" style="width:18px;height:18px;display:inline-block;margin-bottom:6px;opacity:.5;"></i><br>Loading pending registrations...
                    </div>`;
            }
            if (tempContainer) {
                tempContainer.innerHTML = `
                    <div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.85rem;">
                        <i data-lucide="loader" style="width:18px;height:18px;display:inline-block;margin-bottom:6px;opacity:.5;"></i><br>Loading department staff directory...
                    </div>`;
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        try {
            const res = await fetch('{{ route("dept-head.provisioning-dashboard") }}');
            const data = await res.json();

            if (!data.success) return;

            // --- Render Pending Registrations ---
            if (pendingContainer) {
                if (!data.pending || data.pending.length === 0) {
                    const emptyHtml = `
                        <div style="text-align:center;padding:1.5rem 1rem;border:1px dashed var(--border-color);border-radius:12px;">
                            <div style="font-size:1.75rem;margin-bottom:.4rem;">👥</div>
                            <div style="font-size:.82rem;font-weight:700;color:var(--text-muted);">No pending registrations</div>
                            <div style="font-size:.73rem;color:var(--text-muted);margin-top:.2rem;">Any pending staff registrations in your department will appear here.</div>
                        </div>`;
                    if (pendingContainer.innerHTML !== emptyHtml) {
                        pendingContainer.innerHTML = emptyHtml;
                    }
                    if (isStoresHead && !isBackupActive) {
                        const section = document.getElementById('provisioningSection');
                        if (section) section.style.display = 'none';
                    }
                    window._lastPendingRegsString = '';
                } else {
                    const currentDataString = JSON.stringify(data.pending);
                    if (!isSilent || window._lastPendingRegsString !== currentDataString) {
                        window._lastPendingRegsString = currentDataString;

                        // Make sure the section is visible
                        const section = document.getElementById('provisioningSection');
                        if (section) section.style.display = 'block';

                        let rows = data.pending.map(reg => {
                            return `
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:.9rem 1rem;border-bottom:1px solid var(--border-color);gap:1rem;flex-wrap:wrap;">
                                <div style="display:flex;align-items:center;gap:.75rem;">
                                    <div style="width:38px;height:38px;border-radius:10px;background:rgba(22,163,74,0.1);display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:800;color:#16a34a;">
                                        ${(reg.name || reg.username).charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <div style="font-size:.85rem;font-weight:700;color:var(--text-main);">${reg.name}</div>
                                        <div style="font-size:.7rem;color:var(--text-muted);">Requisitioner · @${reg.username} · Phone: ${reg.phone} · Staff ID: ${reg.service_number}</div>
                                    </div>
                                </div>
                                <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                                    <span style="font-size:.65rem;font-weight:800;padding:3px 8px;border-radius:99px;background:rgba(16,185,129,.1);color:#047857;">
                                        PENDING HOD APPROVAL
                                    </span>
                                    <button onclick="approveRegistration(${reg.id}, '${reg.username}')" style="padding:.4rem .7rem;border-radius:8px;font-size:.72rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.3rem;transition:all 0.2s;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#10b981;">
                                        <i data-lucide="user-check" style="width:13px;height:13px;"></i> Approve
                                    </button>
                                    <button onclick="rejectRegistration(${reg.id}, '${reg.username}')" style="padding:.4rem .7rem;border-radius:8px;font-size:.72rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.3rem;transition:all 0.2s;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#ef4444;">
                                        <i data-lucide="user-x" style="width:13px;height:13px;"></i> Decline
                                    </button>
                                </div>
                            </div>
                            `;
                        }).join('');

                        pendingContainer.innerHTML = `<div style="border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">${rows}</div>`;
                    }
                }
            }

            // --- Render Temp Accounts ---
            if (tempContainer) {
                if (!data.accounts || data.accounts.length === 0) {
                    const emptyHtml = `
                        <div style="text-align:center;padding:1.5rem 1rem;border:1px dashed var(--border-color);border-radius:12px;">
                            <div style="font-size:1.75rem;margin-bottom:.4rem;">👥</div>
                            <div style="font-size:.82rem;font-weight:700;color:var(--text-muted);">No department staff found</div>
                            <div style="font-size:.73rem;color:var(--text-muted);margin-top:.2rem;">Any registered staff in your department will appear here.</div>
                        </div>`;
                    if (tempContainer.innerHTML !== emptyHtml) {
                        tempContainer.innerHTML = emptyHtml;
                    }
                    window._lastStaffDataString = '';
                } else {
                    const currentDataString = JSON.stringify(data.accounts);
                    if (!isSilent || window._lastStaffDataString !== currentDataString) {
                        window._lastStaffDataString = currentDataString;

                        let rows = data.accounts.map(acc => {
                            const isAccessActive = acc.can_make_requisition;
                            const badgeStyle = isAccessActive 
                                ? 'background:rgba(16,185,129,.1);color:#10b981;' 
                                : 'background:rgba(239,68,68,.1);color:#ef4444;';
                            const badgeText = isAccessActive ? 'Active Access' : 'Access Suspended';
                            
                            const btnStyle = isAccessActive
                                ? 'background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#ef4444;'
                                : 'background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#10b981;';
                            const btnText = isAccessActive ? 'Suspend Access' : 'Grant Access';
                            const btnIcon = isAccessActive ? 'user-minus' : 'user-check';

                            return `
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:.9rem 1rem;border-bottom:1px solid var(--border-color);gap:1rem;flex-wrap:wrap;">
                                <div style="display:flex;align-items:center;gap:.75rem;">
                                    <div style="width:38px;height:38px;border-radius:10px;background:rgba(22,163,74,0.1);display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:800;color:#16a34a;">
                                        ${(acc.name || acc.username).charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <div style="font-size:.85rem;font-weight:700;color:var(--text-main);">${acc.name || '@' + acc.username}</div>
                                        <div style="font-size:.7rem;color:var(--text-muted);">${acc.role} · @${acc.username}</div>
                                    </div>
                                </div>
                                <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                                    <span style="font-size:.65rem;font-weight:800;padding:3px 8px;border-radius:99px;background:${acc.is_online ? 'rgba(16,185,129,.1)' : 'rgba(100,116,139,.1)'};color:${acc.is_online ? '#10b981' : '#64748b'};">
                                        ${acc.is_online ? '● ONLINE' : '○ OFFLINE'}
                                    </span>
                                    <span style="font-size:.65rem;font-weight:800;padding:3px 8px;border-radius:99px;${badgeStyle}">
                                        ${badgeText}
                                    </span>
                                    <button onclick="toggleStaffAccess(${acc.id}, '${acc.username}', ${isAccessActive})" style="padding:.4rem .7rem;border-radius:8px;font-size:.72rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.3rem;transition:all 0.2s;${btnStyle}">
                                        <i data-lucide="${btnIcon}" style="width:13px;height:13px;"></i> ${btnText}
                                    </button>
                                </div>
                            </div>
                            `;
                        }).join('');

                        tempContainer.innerHTML = `<div style="border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">${rows}</div>`;
                    }
                }
            }

            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (e) {
            console.error('Failed to load provisioning data:', e);
            if (!isSilent) {
                if (pendingContainer) {
                    pendingContainer.innerHTML = `<div style="text-align:center;padding:1rem;color:var(--text-muted);font-size:.8rem;">Failed to load pending registrations list.</div>`;
                }
                if (tempContainer) {
                    tempContainer.innerHTML = `<div style="text-align:center;padding:1rem;color:var(--text-muted);font-size:.8rem;">Failed to load staff list.</div>`;
                }
            }
        }
    }

    async function loadPendingRegistrations(isSilent = false) {
        await loadProvisioningData(isSilent);
    }

    async function approveRegistration(id, username) {
        const confirm = await Swal.fire({
            title: 'Approve Registration?',
            text: `Are you sure you want to approve requisitioner privileges for @${username}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`/dept-head/registration/${id}/approve`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ 
                    title: 'Approved!', 
                    text: data.message, 
                    icon: 'success', 
                    timer: 2000, 
                    showConfirmButton: false 
                });
                loadPendingRegistrations();
                if (typeof loadTempAccounts === 'function') {
                    loadTempAccounts();
                }
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Network error while approving registration.', 'error');
        }
    }

    async function rejectRegistration(id, username) {
        const confirm = await Swal.fire({
            title: 'Decline Registration Request?',
            text: `Are you sure you want to decline registration request for @${username}? This account will be deactivated.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Decline',
            cancelButtonText: 'Cancel'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`/dept-head/registration/${id}/reject`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ 
                    title: 'Declined!', 
                    text: data.message, 
                    icon: 'success', 
                    timer: 2000, 
                    showConfirmButton: false 
                });
                loadPendingRegistrations();
                if (typeof loadTempAccounts === 'function') {
                    loadTempAccounts();
                }
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Network error while declining registration.', 'error');
        }
    }

    // =====================================================================
    // STAFF ACCESS PROVISIONING (Non-Stores Dept Heads only)
    // =====================================================================
    @if(!$hideProvisioning)
    async function loadTempAccounts(isSilent = false) {
        await loadProvisioningData(isSilent);
    }

    async function toggleStaffAccess(id, username, isCurrentlyActive) {
        const actionWord = isCurrentlyActive ? 'Suspend' : 'Grant';
        const actionColor = isCurrentlyActive ? '#ef4444' : '#10b981';
        
        const confirm = await Swal.fire({
            title: `${actionWord} Requisition Access?`,
            text: `Are you sure you want to ${actionWord.toLowerCase()} requisition privileges for @${username}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: actionColor,
            cancelButtonColor: '#64748b',
            confirmButtonText: `Yes, ${actionWord}`,
            cancelButtonText: 'Cancel'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`/dept-head/staff/${id}/toggle-request-access`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ 
                    title: 'Updated!', 
                    text: data.message, 
                    icon: 'success', 
                    timer: 2000, 
                    showConfirmButton: false 
                });
                loadTempAccounts();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Network error while updating access privileges.', 'error');
        }
    }
    @endif

    const sentFollowUps = new Set();

    function applyFollowUpSentStyle(btn) {
        if (!btn) return;
        btn.innerHTML = `<i data-lucide="check" style="width: 14px;"></i> Reminder Sent`;
        btn.style.background = 'rgba(100, 116, 139, 0.05)';
        btn.style.borderColor = 'rgba(100, 116, 139, 0.1)';
        btn.style.color = 'var(--text-muted)';
        btn.style.cursor = 'not-allowed';
        btn.disabled = true;
    }

    async function sendFollowUp(id, btn) {
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<i data-lucide="loader" style="width: 14px; animation: spin 1s linear infinite; vertical-align: middle;"></i> Sending...`;
        lucide.createIcons();

        try {
            const response = await fetch(`/requisitions/${id}/followup`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                if (typeof window.playNotificationSound === 'function') {
                    window.playNotificationSound('sent');
                }
                sentFollowUps.add(id);
                Swal.fire({
                    icon: 'success',
                    title: 'Follow Up Sent!',
                    text: data.message,
                    confirmButtonColor: 'var(--store-orange)'
                });
                applyFollowUpSentStyle(btn);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Unable to Follow Up',
                    text: data.message,
                    confirmButtonColor: 'var(--store-orange)'
                });
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
            lucide.createIcons();
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'An error occurred while sending the follow-up reminder.',
                confirmButtonColor: 'var(--store-orange)'
            });
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            lucide.createIcons();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        loadProvisioningData();

        // Auto-open specific requisition if open_id is present in query parameters
        const urlParams = new URLSearchParams(window.location.search);
        const openId = urlParams.get('open_id');
        if (openId) {
            openRequisitionModal(parseInt(openId));
        }
    });

    // Helper to normalize HTML content for stable comparison (ignores Lucide icon expansions and whitespace differences)
    function getNormalizedHTML(element) {
        if (!element) return '';
        const clone = element.cloneNode(true);
        
        // Remove all icon elements to prevent Lucide translation differences from causing false change detection
        clone.querySelectorAll('svg, i, [data-lucide]').forEach(el => el.remove());
        
        // Normalize all follow-up buttons to prevent differences in "Follow Up" vs "Reminder Sent" states from triggering a false update
        clone.querySelectorAll('button[onclick*="sendFollowUp"]').forEach(btn => {
            const placeholder = document.createElement('button');
            placeholder.className = 'follow-up-placeholder';
            btn.parentNode.replaceChild(placeholder, btn);
        });
        
        return clone.innerHTML.replace(/\s+/g, ' ').trim();
    }

    // Auto silent refresh every 30 seconds (paused when tab is hidden)
    let _mainAdminRefreshPaused = document.hidden;
    document.addEventListener('visibilitychange', () => { _mainAdminRefreshPaused = document.hidden; });
    setInterval(async () => {
        if (_mainAdminRefreshPaused) return;
        const reqModal = document.getElementById('reqModal');
        
        const isModalOpen = (reqModal && reqModal.classList.contains('open'));
                            
        const isSwalOpen = typeof Swal !== 'undefined' && Swal.isVisible();
        
        if (isModalOpen || isSwalOpen) {
            return;
        }
        
        try {
            const response = await fetch(window.location.href);
            if (!response.ok) return;
            const html = await response.text();
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            let updated = false;
            
            // Update stats cards
            const newStats = doc.getElementById('oversight-stats-container');
            const currentStats = document.getElementById('oversight-stats-container');
            if (newStats && currentStats) {
                const normNewStats = getNormalizedHTML(newStats);
                const normCurStats = getNormalizedHTML(currentStats);
                if (normNewStats !== normCurStats) {
                    currentStats.innerHTML = newStats.innerHTML;
                    updated = true;
                }
            }
            
            // Update table & pagination wrapper
            const newTable = doc.getElementById('oversight-table-wrapper');
            const currentTable = document.getElementById('oversight-table-wrapper');
            if (newTable && currentTable) {
                const normNewTable = getNormalizedHTML(newTable);
                const normCurTable = getNormalizedHTML(currentTable);
                if (normNewTable !== normCurTable) {
                    // Remove animate-slide-up class from the fetched table rows to prevent layout flashing/blinking
                    newTable.querySelectorAll('.animate-slide-up').forEach(el => {
                        el.classList.remove('animate-slide-up');
                    });
                    currentTable.innerHTML = newTable.innerHTML;
                    
                    // Reapply local "Reminder Sent" states to the newly loaded buttons if applicable
                    sentFollowUps.forEach(id => {
                        const btn = currentTable.querySelector(`button[onclick*="sendFollowUp(${id},"]`);
                        if (btn) {
                            applyFollowUpSentStyle(btn);
                        }
                    });
                    
                    updated = true;
                }
            }
            
            if (updated && typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            await loadProvisioningData(true);
        } catch (e) {
            console.error('Silent refresh failed:', e);
        }
    }, 30000);


    function toggleWorkflowCategory(code, card) {
        const select = document.getElementById('stores_dept_head_approval_categories');
        if (!select) return;
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

    function updateWorkflowFlowchart() {
        const selectStores = document.getElementById('stores_dept_head_approval_categories');
        if (!selectStores) return;
        const activeCountStores = 1; // Head of Admin is always required

        const selectDG = document.getElementById('dg_approval_categories');
        const activeCountDG = selectDG ? Array.from(selectDG.selectedOptions).length : 0;

        // Update HOD header badge
        const badgeTextStores = document.getElementById('workflow-badge-text');
        const badgeDotStores = document.getElementById('workflow-badge-dot');
        const badgeContainerStores = document.getElementById('workflow-active-badge');
        if (badgeTextStores) badgeTextStores.textContent = `Active Categories: ${activeCountStores}`;
        if (activeCountStores > 0) {
            if (badgeDotStores) badgeDotStores.style.background = '#16a34a';
            if (badgeContainerStores) {
                badgeContainerStores.style.background = 'rgba(22, 163, 74, 0.08)';
                badgeContainerStores.style.color = '#16a34a';
                badgeContainerStores.style.borderColor = 'rgba(22, 163, 74, 0.2)';
            }
        } else {
            if (badgeDotStores) badgeDotStores.style.background = '#64748b';
            if (badgeContainerStores) {
                badgeContainerStores.style.background = 'rgba(100, 116, 139, 0.08)';
                badgeContainerStores.style.color = '#64748b';
                badgeContainerStores.style.borderColor = 'rgba(100, 116, 139, 0.2)';
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
                    iconBox.style.background = 'linear-gradient(135deg, #16a34a, #3730a3)';
                    iconBox.style.color = '#ffffff';
                    iconBox.style.borderColor = 'transparent';
                    iconBox.style.boxShadow = '0 6px 15px rgba(22,163,74,0.2)';
                }
                if (label) {
                    label.style.color = '#1e293b';
                    label.style.textDecoration = 'none';
                }
                if (badge) {
                    badge.textContent = 'Required';
                    badge.style.background = 'rgba(22, 163, 74, 0.1)';
                    badge.style.color = '#16a34a';
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
                    iconBox.style.background = 'linear-gradient(135deg, #4ade80, #6d28d9)';
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
                    badge.style.color = '#4ade80';
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
                line.style.background = '#16a34a';
            } else {
                line.className = 'flow-line flow-line-1 dashed';
                line.style.background = '';
            }
        });

        document.querySelectorAll('.flow-line-2').forEach(line => {
            if (activeCountDG > 0) {
                line.className = 'flow-line flow-line-2 active';
                line.style.background = '#4ade80';
            } else {
                line.className = 'flow-line flow-line-2 dashed';
                line.style.background = '';
            }
        });

        document.querySelectorAll('.flow-line-3').forEach(line => {
            line.className = 'flow-line flow-line-3 active';
            line.style.background = '#10b981';
        });

        // Update hints
        document.querySelectorAll('.workflow-helper-hint').forEach(hint => {
            const isStoresCard = hint.closest('.workflow-card-modern').querySelector('h3').textContent.includes('Stores');
            if (isStoresCard) {
                if (activeCountStores > 0) {
                    hint.innerHTML = `Routing through <strong>Head of Admin(Authorizer)</strong> for <strong style="color: #16a34a;">${activeCountStores}</strong> selected category${activeCountStores == 1 ? '' : 'ies'}.`;
                } else {
                    hint.innerHTML = 'Currently bypassing intermediate Stores Head step due to settings configuration.';
                }
            } else {
                if (activeCountDG > 0) {
                    hint.innerHTML = `Routing through <strong>Director General</strong> for <strong style="color: #4ade80;">${activeCountDG}</strong> selected category${activeCountDG == 1 ? '' : 'ies'}.`;
                } else {
                    hint.innerHTML = 'Currently bypassing intermediate Director General step due to settings configuration.';
                }
            }
        });
    }



    let currentSraId = null;
    let currentSraStage = null;

    window.openSraOversightModal = async function(id, stage) {
        currentSraId = id;
        currentSraStage = stage;

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
            const deliveryColor = sra.delivery_type === 'full' ? '#10b981' : '#10b981';
            const deliveryBg = sra.delivery_type === 'full' ? 'rgba(16,185,129,0.1)' : 'rgba(16,185,129,0.1)';

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
                ${sra.previous_sra_nos ? `<div style="margin-top:0.75rem;font-size:0.72rem;font-weight:800;color:#10b981;">Previous SRA Nos: ${sra.previous_sra_nos}</div>` : ''}
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
                            <button style="flex:1; padding: 0.85rem 2rem; border: none; background: #10b981; color: white; border-radius: 12px; cursor: default; pointer-events: none; font-weight: 950; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" disabled>
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
                        <button onclick="processOversightSra('declined')" id="sraBtnDecline" style="padding: 0.85rem 2rem; border: 1px solid rgba(239,68,68,0.3); background: rgba(239,68,68,0.08); color: #ef4444; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="x-circle" style="width: 16px;"></i> Decline
                        </button>
                        <button onclick="processOversightSra('approved')" id="sraBtnApprove" style="padding: 0.85rem 2rem; border: none; background: linear-gradient(135deg, #10b981, #059669); color: white; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 8px 20px -5px rgba(16,185,129,0.4);">
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
            confirmButtonColor: action === 'approved' ? '#10b981' : '#ef4444',
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

    window.showReceiptNotice = function() {
        Swal.fire({
            icon: 'info',
            title: 'Notice',
            text: 'The SRA receipt is only available after full approval by all required actors.',
            confirmButtonColor: 'var(--primary)'
        });
    };

    window.showDeclinedNotice = function() {
        Swal.fire({
            icon: 'error',
            title: 'Declined',
            text: 'This Service SRA has been declined and does not have an active receipt.',
            confirmButtonColor: 'var(--primary)'
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('stores_dept_head_approval_categories') || document.getElementById('dg_approval_categories')) {
            updateWorkflowFlowchart();
        }
    });
</script>
@endsection