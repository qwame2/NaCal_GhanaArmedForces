@extends('layouts.admin')

@section('title', 'Rollback & Request Corrections')

@section('content')
@php
    // The EditRequest payload is the raw SRA batch — items live at $data['items']
    $items = $data['items'] ?? [];

    $acqType  = $data['acquisition_type'] ?? 'Supplier';
    $isDonor  = ($acqType === 'Donor'
                 || str_contains($data['supplier_name'] ?? '', '[Donor Action]')
                 || str_contains($data['supplier_name'] ?? '', '[Donation]'));
    $provider = $isDonor
        ? ($data['donor_name'] ?? trim(preg_replace('/\[.*?\]/', '', $data['supplier_name'] ?? '')))
        : trim(preg_replace('/\[.*?\]/', '', $data['supplier_name'] ?? ''));

    $statusLabel  = $data['supplier_status'] ?? 'N/A';
    $receivedDate = !empty($data['arrival_date'])
        ? \Carbon\Carbon::parse($data['arrival_date'])->format('d M Y')
        : 'N/A';

    // Aggregate item details across all items in the batch
    $itemDescriptions = collect($items)->pluck('description')->filter()->values();
    $itemQtys         = collect($items)->pluck('qty')->filter()->values();
    $itemUnits        = collect($items)->pluck('unit')->filter()->unique()->values();

    $itemDesc = $itemDescriptions->isNotEmpty() ? $itemDescriptions->implode(', ') : 'N/A';
    $itemQty  = $itemQtys->isNotEmpty()         ? $itemQtys->implode(', ')         : 'N/A';
    $itemUnit = $itemUnits->isNotEmpty()         ? $itemUnits->implode(' / ')       : '';

    $standardPackages = ['PIECE(S)', 'PACK', 'BOXES', 'CARTON', 'BAG', 'ROLL', 'SET', 'REAM', 'BOTTLE'];
@endphp

<style>
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulseRed {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); }
        50%       { box-shadow: 0 0 0 6px rgba(239,68,68,0.12); }
    }

    .rb-page-wrap {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 2rem;
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 0 4rem;
        animation: fadeSlideUp 0.45s cubic-bezier(0.22, 1, 0.36, 1) both;
    }

    /* ── Left column ── */
    .rb-main-col { display: flex; flex-direction: column; gap: 1.25rem; min-width: 0; }

    /* ── Breadcrumb (above card, plain inline) ── */
    .rb-breadcrumb-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 0.75rem;
    }
    .rb-breadcrumb-pill a {
        color: #94a3b8;
        text-decoration: none;
        font-size: 0.72rem;
        font-weight: 800;
        display: flex; align-items: center; gap: 4px;
        transition: color 0.15s;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .rb-breadcrumb-pill a:hover { color: #059669; }
    .rb-breadcrumb-pill a i { width: 11px; height: 11px; }
    .rb-breadcrumb-sep { color: #d1d5db; font-size: 0.65rem; }
    .rb-breadcrumb-current {
        font-size: 0.72rem; font-weight: 900;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    /* ── Header card ── */
    .rb-header-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 24px -6px rgba(0,0,0,0.07), 0 1px 4px rgba(0,0,0,0.03);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .rb-header-gradient {
        display: flex;
        gap: 0;
        position: relative;
        padding: 0;
        overflow: hidden;
    }
    /* Left accent stripe */
    .rb-header-accent {
        width: 6px;
        flex-shrink: 0;
        background: linear-gradient(180deg, #059669 0%, #047857 100%);
        border-radius: 0;
    }
    /* Main content area */
    .rb-header-content {
        flex: 1;
        padding: 2rem 2rem 1.75rem;
        position: relative;
        overflow: hidden;
    }
    /* Watermark icon */
    .rb-header-content::after {
        content: '';
        position: absolute;
        right: -20px; top: 50%;
        transform: translateY(-50%);
        width: 160px; height: 160px;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23d1fae5' stroke-width='1.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8'/%3E%3Cpath d='M3 3v5h5'/%3E%3C/svg%3E") no-repeat center / contain;
        pointer-events: none;
    }
    .rb-header-body {
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
        position: relative;
        z-index: 2;
    }
    .rb-header-icon {
        width: 52px; height: 52px;
        background: #f0fdf4;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        border: 1.5px solid #bbf7d0;
    }
    .rb-header-icon i { width: 26px; height: 26px; color: #059669; }
    .rb-header-label {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.62rem; font-weight: 900;
        color: #059669;
        text-transform: uppercase; letter-spacing: 0.16em;
        margin-bottom: 6px;
    }
    .rb-header-title {
        margin: 0; font-size: 1.5rem; font-weight: 900;
        color: #0f172a; letter-spacing: -0.025em; line-height: 1.15;
    }
    .rb-header-sub {
        font-size: 0.82rem; color: #94a3b8;
        margin-top: 6px; font-weight: 600; line-height: 1.55;
    }
    /* Stat pills row at the bottom of header */
    .rb-header-footer {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.9rem 2rem 1.1rem calc(2rem + 6px);
        flex-wrap: wrap;
        border-top: 1px solid #f8fafc;
        background: #fafbfd;
    }
    .rb-stat-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.74rem; font-weight: 800;
        color: #475569;
        background: white;
        border: 1px solid #e8eef6;
        border-radius: 999px;
        padding: 5px 12px 5px 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .rb-stat-chip i { width: 13px; height: 13px; color: #94a3b8; }
    .rb-stat-chip strong { color: #0f172a; font-weight: 900; }

    /* ── Section card ── */
    .rb-section-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e8eef6;
        box-shadow: 0 2px 12px -2px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .rb-section-head {
        padding: 1.1rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; gap: 10px;
    }
    .rb-section-icon {
        width: 32px; height: 32px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .rb-section-icon.red   { background: rgba(239,68,68,0.1);  color: #ef4444; }
    .rb-section-icon.amber { background: rgba(245,158,11,0.1); color: #d97706; }
    .rb-section-icon.green { background: rgba(5,150,105,0.1);  color: #059669; }
    .rb-section-icon.blue  { background: rgba(99,102,241,0.1); color: #6366f1; }
    .rb-section-icon i { width: 16px; height: 16px; }
    .rb-section-title { font-size: 0.88rem; font-weight: 900; color: #0f172a; }
    .rb-section-subtitle { font-size: 0.72rem; color: #94a3b8; font-weight: 700; margin-top: 1px; }
    .rb-section-body { padding: 1.25rem 1.5rem; }

    /* ── Flagged items banner ── */
    .rb-flagged-banner {
        background: linear-gradient(135deg, rgba(239,68,68,0.06) 0%, rgba(254,202,202,0.15) 100%);
        border: 1.5px solid #fecaca;
        border-radius: 14px;
        padding: 1rem 1.25rem;
        display: flex; align-items: flex-start; gap: 12px;
    }
    .rb-flagged-banner-icon {
        width: 36px; height: 36px;
        background: rgba(239,68,68,0.12);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .rb-flagged-banner-icon i { width: 18px; height: 18px; color: #ef4444; }
    .rb-flagged-label { font-size: 0.68rem; font-weight: 900; color: #ef4444; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px; }
    .rb-flagged-items { font-size: 0.85rem; font-weight: 700; color: #7f1d1d; line-height: 1.6; }

    /* ── Field rows ── */
    .rb-fields-list { display: flex; flex-direction: column; gap: 10px; }
    .rb-field-row {
        border-radius: 14px;
        border: 1.5px solid #e8eef6;
        background: #fafbfd;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    .rb-field-row:hover { border-color: #d0d9e8; background: #f6f9ff; }
    .rb-field-row.checked-row {
        background: #fff7f7 !important;
        border-color: #fca5a5 !important;
        animation: pulseRed 2s ease-in-out 1;
    }
    .rb-field-label-area {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 16px;
        cursor: pointer; user-select: none;
    }
    .rb-field-label-area label { cursor: pointer; display: contents; }

    /* Custom checkbox */
    .rb-check {
        width: 20px; height: 20px;
        border: 2px solid #d1d5db;
        border-radius: 6px;
        cursor: pointer;
        flex-shrink: 0;
        transition: all 0.2s;
        appearance: none; -webkit-appearance: none;
        background: white;
        position: relative;
    }
    .rb-check:checked {
        background: #ef4444;
        border-color: #ef4444;
    }
    .rb-check:checked::after {
        content: '';
        position: absolute;
        top: 3px; left: 6px;
        width: 5px; height: 9px;
        border: 2px solid white;
        border-top: none; border-left: none;
        transform: rotate(45deg);
    }
    .rb-check:hover:not(:checked) { border-color: #f87171; }

    .rb-field-meta { flex: 1; min-width: 0; }
    .rb-field-name { font-size: 0.9rem; font-weight: 800; color: #1e293b; }
    .rb-field-current { font-size: 0.72rem; color: #94a3b8; font-weight: 700; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 260px; }
    .rb-field-badge {
        font-size: 0.65rem; font-weight: 900; padding: 3px 9px;
        border-radius: 999px; white-space: nowrap; flex-shrink: 0;
        background: #f1f5f9; color: #64748b;
    }
    .rb-field-row.checked-row .rb-field-badge { background: #fee2e2; color: #dc2626; }

    .rb-note-wrap {
        display: none;
        padding: 0 16px 14px;
        animation: fadeSlideUp 0.25s ease both;
    }
    .rb-note-wrap.visible { display: block; }
    .rb-note-input {
        width: 100%; font-size: 0.85rem;
        border: 1.5px solid #fca5a5; border-radius: 10px;
        padding: 10px 14px;
        font-family: inherit; color: #1e293b;
        background: white; outline: none;
        box-sizing: border-box; transition: all 0.2s;
    }
    .rb-note-input:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 4px rgba(239,68,68,0.1);
    }

    /* ── General note ── */
    .rb-general-note {
        width: 100%; font-size: 0.88rem;
        border: 1.5px solid #e2e8f0; border-radius: 12px;
        padding: 12px 16px;
        font-family: inherit; color: #1e293b;
        background: #f8fafc; outline: none;
        resize: vertical; box-sizing: border-box; transition: all 0.2s;
    }
    .rb-general-note:focus {
        border-color: #059669;
        box-shadow: 0 0 0 4px rgba(5,150,105,0.1);
        background: white;
    }

    /* ── Validation warning ── */
    .rb-validation-warning {
        display: none;
        background: #fffbeb;
        border: 1.5px solid #fde68a;
        border-radius: 12px;
        padding: 12px 16px;
        align-items: center; gap: 10px;
        font-size: 0.85rem; font-weight: 700; color: #92400e;
    }
    .rb-validation-warning i { width: 18px; height: 18px; flex-shrink: 0; color: #d97706; }

    /* ── Sticky action bar ── */
    .rb-action-bar {
        position: sticky;
        bottom: 0;
        z-index: 100;
        background: rgba(255,255,255,0.96);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-top: 1px solid #e8eef6;
        border-radius: 0 0 20px 20px;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.06);
        margin-top: -1px;
    }
    .rb-action-bar-left {
        font-size: 0.75rem;
        color: #94a3b8;
        font-weight: 700;
    }
    .rb-action-bar-left strong {
        color: #059669;
        font-weight: 900;
    }
    .rb-form-footer {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .rb-btn-cancel {
        padding: 11px 24px; text-decoration: none;
        font-weight: 800; color: #64748b;
        border-radius: 12px; border: 1.5px solid #e2e8f0;
        background: white; font-size: 0.88rem;
        transition: all 0.2s; display: inline-flex; align-items: center; gap: 7px;
    }
    .rb-btn-cancel:hover { background: #f8fafc; border-color: #cbd5e1; color: #475569; transform: translateY(-1px); }
    .rb-btn-submit {
        padding: 11px 28px; border: none; border-radius: 12px;
        cursor: pointer; font-weight: 900; font-size: 0.88rem;
        display: inline-flex; align-items: center; gap: 8px;
        background: linear-gradient(135deg, #059669, #065f46);
        color: white;
        box-shadow: 0 8px 20px -4px rgba(5,150,105,0.35);
        transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
        position: relative; overflow: hidden;
    }
    .rb-btn-submit::before {
        content: '';
        position: absolute; inset: 0;
        background: rgba(255,255,255,0);
        transition: background 0.2s;
    }
    .rb-btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px -6px rgba(5,150,105,0.45);
    }
    .rb-btn-submit:hover::before { background: rgba(255,255,255,0.08); }
    .rb-btn-submit:active { transform: translateY(0); }
    .rb-btn-submit i { width: 18px; height: 18px; }

    /* ── Right sidebar ── */
    .rb-sidebar-col { position: relative; }
    .rb-sidebar-sticky {
        position: sticky;
        top: 100px;
        display: flex; flex-direction: column; gap: 1rem;
    }
    .rb-sidebar-card {
        background: white;
        border-radius: 18px;
        border: 1px solid #e8eef6;
        box-shadow: 0 2px 10px -2px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .rb-sidebar-card-head {
        padding: 0.9rem 1.15rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; gap: 9px;
        background: #fafbfd;
    }
    .rb-sidebar-card-title { font-size: 0.8rem; font-weight: 900; color: #0f172a; }
    .rb-sidebar-card-body { padding: 1rem 1.15rem; }
    .rb-detail-row {
        display: flex; justify-content: space-between; align-items: flex-start;
        padding: 8px 0;
        border-bottom: 1px solid #f8fafc;
    }
    .rb-detail-row:last-child { border-bottom: none; padding-bottom: 0; }
    .rb-detail-key { font-size: 0.72rem; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; flex-shrink: 0; padding-top: 1px; }
    .rb-detail-val { font-size: 0.82rem; color: #1e293b; font-weight: 800; text-align: right; max-width: 160px; word-break: break-word; }

    /* ── Progress widget ── */
    .rb-progress-widget { padding: 1rem 1.15rem; }
    .rb-progress-label { font-size: 0.72rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px; }
    .rb-progress-bar-wrap { background: #f1f5f9; border-radius: 999px; height: 6px; overflow: hidden; }
    .rb-progress-bar { height: 100%; background: linear-gradient(90deg, #059669, #10b981); border-radius: 999px; transition: width 0.4s ease; width: 0%; }
    .rb-progress-count { font-size: 0.82rem; font-weight: 900; color: #059669; margin-top: 6px; }

    /* ── Tips card ── */
    .rb-tip { display: flex; align-items: flex-start; gap: 9px; padding: 9px 0; border-bottom: 1px solid #f8fafc; }
    .rb-tip:last-child { border-bottom: none; padding-bottom: 0; }
    .rb-tip-dot { width: 7px; height: 7px; border-radius: 50%; background: #059669; margin-top: 5px; flex-shrink: 0; }
    .rb-tip p { margin: 0; font-size: 0.78rem; color: #64748b; font-weight: 600; line-height: 1.45; }

    /* ── Responsive ── */
    @media (max-width: 900px) {
        .rb-page-wrap { grid-template-columns: 1fr; }
        .rb-sidebar-sticky { position: static; }
    }
    @media (max-width: 640px) {
        .rb-page-wrap { padding: 0 0 3rem; }
        .rb-header-gradient { padding: 1.5rem 1.5rem; }
        .rb-section-body { padding: 1rem 1.25rem; }
    }

    /* Date input style */
    input[type="date"].rb-note-input {
        background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23ef4444' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E") no-repeat right 14px center;
        background-size: 17px;
        padding-right: 40px;
        cursor: pointer;
    }
    input[type="date"].rb-note-input::-webkit-calendar-picker-indicator {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        width: 100%; height: 100%; opacity: 0; cursor: pointer;
    }
</style>

<div class="rb-page-wrap">

    {{-- ══════════════ LEFT COLUMN ══════════════ --}}
    <div class="rb-main-col">

        {{-- Breadcrumb --}}
        <div class="rb-breadcrumb-pill">
            <a href="{{ route('sra.preview', $editReq->id) }}">
                <i data-lucide="arrow-left"></i>
                SRA Review
            </a>
            <span class="rb-breadcrumb-sep">/</span>
            <span class="rb-breadcrumb-current">Rollback</span>
        </div>

        {{-- Header card --}}
        <div class="rb-header-card">
            <div class="rb-header-gradient">
                <div class="rb-header-accent"></div>
                <div class="rb-header-content">
                    <div class="rb-header-body">
                        <div class="rb-header-icon">
                            <i data-lucide="rotate-ccw"></i>
                        </div>
                        <div>
                            <div class="rb-header-label">
                                <i data-lucide="shield-alert" style="width:9px;height:9px;"></i>
                                Admin Action
                            </div>
                            <h1 class="rb-header-title">Rollback &amp; Request Corrections</h1>
                            <div class="rb-header-sub">Flag specific fields and send this SRA back to the user for correction.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <form id="rollbackForm" action="{{ route('sra-creation.rollback', $editReq->id) }}" method="POST"
              onsubmit="return validateRollbackForm()">
            @csrf

            {{-- Pre-selected items banner --}}
            @if(!empty($selectedItems))
            <div class="rb-section-card">
                <div class="rb-section-body" style="padding-top: 1.15rem; padding-bottom: 1.15rem;">
                    <div class="rb-flagged-banner">
                        <div class="rb-flagged-banner-icon">
                            <i data-lucide="alert-triangle"></i>
                        </div>
                        <div>
                            <div class="rb-flagged-label">Items Pre-selected for Rollback</div>
                            <div class="rb-flagged-items">
                                @foreach($selectedItems as $item)
                                    <span style="display: inline-flex; align-items: center; gap: 5px; margin-right: 4px;">
                                        <span style="width: 5px; height: 5px; background: #ef4444; border-radius: 50%; display: inline-block;"></span>
                                        {{ $item }}
                                    </span><br>
                                    <input type="hidden" name="flagged_items[]" value="{{ $item }}">
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Fields selection --}}
            <div class="rb-section-card">
                <div class="rb-section-head">
                    <div class="rb-section-icon red">
                        <i data-lucide="flag"></i>
                    </div>
                    <div>
                        <div class="rb-section-title">Flag Fields for Correction</div>
                        <div class="rb-section-subtitle">Check each field the user must correct. Add a note for clarity.</div>
                    </div>
                </div>
                <div class="rb-section-body">
                    <div class="rb-fields-list">

                        {{-- 1. Delivery Status --}}
                        <div class="rb-field-row" id="rbrow-supplier_status">
                            <div class="rb-field-label-area" onclick="toggleFieldRow(document.getElementById('rbcb-supplier_status'))">
                                <input type="checkbox" id="rbcb-supplier_status" name="flagged_fields_keys[]"
                                       value="supplier_status" class="rb-check"
                                       onchange="toggleFieldRow(this)" onclick="event.stopPropagation()">
                                <div class="rb-field-meta">
                                    <div class="rb-field-name">Delivery Status</div>
                                    <div class="rb-field-current">Current: <strong>{{ $statusLabel }}</strong></div>
                                </div>
                                <span class="rb-field-badge">Status</span>
                            </div>
                            <div class="rb-note-wrap" id="rbnote-supplier_status">
                                <input type="text" name="flagged_fields[supplier_status]" class="rb-note-input"
                                       placeholder="e.g. 'Should be Partial Delivery'..." disabled>
                            </div>
                        </div>

                        {{-- 2. Received Date --}}
                        <div class="rb-field-row" id="rbrow-arrival_date">
                            <div class="rb-field-label-area" onclick="toggleFieldRow(document.getElementById('rbcb-arrival_date'))">
                                <input type="checkbox" id="rbcb-arrival_date" name="flagged_fields_keys[]"
                                       value="arrival_date" class="rb-check"
                                       onchange="toggleFieldRow(this)" onclick="event.stopPropagation()">
                                <div class="rb-field-meta">
                                    <div class="rb-field-name">Received Date (Manual)</div>
                                    <div class="rb-field-current">Current: <strong>{{ $receivedDate }}</strong></div>
                                </div>
                                <span class="rb-field-badge">Date</span>
                            </div>
                            <div class="rb-note-wrap" id="rbnote-arrival_date">
                                <input type="date" name="flagged_fields[arrival_date]" class="rb-note-input"
                                       value="{{ !empty($data['arrival_date']) ? \Carbon\Carbon::parse($data['arrival_date'])->format('Y-m-d') : '' }}"
                                       disabled>
                            </div>
                        </div>

                        {{-- 3. Received Qty --}}
                        <div class="rb-field-row" id="rbrow-item_qty">
                            <div class="rb-field-label-area" onclick="toggleFieldRow(document.getElementById('rbcb-item_qty'))">
                                <input type="checkbox" id="rbcb-item_qty" name="flagged_fields_keys[]"
                                       value="item_qty" class="rb-check"
                                       onchange="toggleFieldRow(this)" onclick="event.stopPropagation()">
                                <div class="rb-field-meta">
                                    <div class="rb-field-name">Received Quantity</div>
                                    <div class="rb-field-current">Current: <strong>{{ $itemQty }}</strong></div>
                                </div>
                                <span class="rb-field-badge">Qty</span>
                            </div>
                            <div class="rb-note-wrap" id="rbnote-item_qty">
                                <input type="text" name="flagged_fields[item_qty]" class="rb-note-input"
                                       placeholder="{{ !empty($selectedItems) ? 'Correction required for: ' . implode(', ', $selectedItems) : 'e.g. Correct quantity is 50 pieces...' }}"
                                       disabled>
                            </div>
                        </div>

                        {{-- 4. Package Type --}}
                        <div class="rb-field-row" id="rbrow-item_unit">
                            <div class="rb-field-label-area" onclick="toggleFieldRow(document.getElementById('rbcb-item_unit'))">
                                <input type="checkbox" id="rbcb-item_unit" name="flagged_fields_keys[]"
                                       value="item_unit" class="rb-check"
                                       onchange="toggleFieldRow(this)" onclick="event.stopPropagation()">
                                <div class="rb-field-meta">
                                    <div class="rb-field-name">Package Type</div>
                                    <div class="rb-field-current">Current: <strong>{{ $itemUnit }}</strong></div>
                                </div>
                                <span class="rb-field-badge">Unit</span>
                            </div>
                            <div class="rb-note-wrap" id="rbnote-item_unit">
                                <select name="flagged_fields[item_unit]" class="rb-note-input select2-unit-rollback" disabled style="height: auto;">
                                    <option value="">Select recommended package type...</option>
                                    @foreach($standardPackages as $pkg)
                                        <option value="{{ $pkg }}">{{ $pkg }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- 5. Item Description --}}
                        <div class="rb-field-row" id="rbrow-item_description">
                            <div class="rb-field-label-area" onclick="toggleFieldRow(document.getElementById('rbcb-item_description'))">
                                <input type="checkbox" id="rbcb-item_description" name="flagged_fields_keys[]"
                                       value="item_description" class="rb-check"
                                       onchange="toggleFieldRow(this)" onclick="event.stopPropagation()">
                                <div class="rb-field-meta">
                                    <div class="rb-field-name">Item Description</div>
                                    <div class="rb-field-current">Current: <strong>{{ \Str::limit($itemDesc, 40) }}</strong></div>
                                </div>
                                <span class="rb-field-badge">Desc</span>
                            </div>
                            <div class="rb-note-wrap" id="rbnote-item_description">
                                <input type="text" name="flagged_fields[item_description]" class="rb-note-input"
                                       placeholder="{{ !empty($selectedItems) ? 'Correction required for: ' . implode(', ', $selectedItems) : 'e.g. Correct description is...' }}"
                                       disabled style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>

                        {{-- 6. Supplier / Donor Name --}}
                        <div class="rb-field-row" id="rbrow-supplier_name">
                            <div class="rb-field-label-area" onclick="toggleFieldRow(document.getElementById('rbcb-supplier_name'))">
                                <input type="checkbox" id="rbcb-supplier_name" name="flagged_fields_keys[]"
                                       value="supplier_name" class="rb-check"
                                       onchange="toggleFieldRow(this)" onclick="event.stopPropagation()">
                                <div class="rb-field-meta">
                                    <div class="rb-field-name">{{ $isDonor ? 'Donor Name' : 'Supplier Name' }}</div>
                                    <div class="rb-field-current">Current: <strong>{{ \Str::limit($provider, 35) }}</strong></div>
                                </div>
                                <span class="rb-field-badge">{{ $isDonor ? 'Donor' : 'Supplier' }}</span>
                            </div>
                            <div class="rb-note-wrap" id="rbnote-supplier_name">
                                <input type="text" name="flagged_fields[supplier_name]" class="rb-note-input"
                                       placeholder="e.g. Correct supplier is..." disabled style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- General Note --}}
            <div class="rb-section-card">
                <div class="rb-section-head">
                    <div class="rb-section-icon amber">
                        <i data-lucide="message-square"></i>
                    </div>
                    <div>
                        <div class="rb-section-title">General Note <span style="font-size:0.7rem; font-weight:700; color:#94a3b8; margin-left:5px;">(Optional)</span></div>
                        <div class="rb-section-subtitle">Overall instructions or context for the user.</div>
                    </div>
                </div>
                <div class="rb-section-body">
                    <textarea name="general_note" class="rb-general-note" rows="4"
                              placeholder="e.g. Please review all highlighted fields carefully before resubmitting. Ensure the delivery date matches the actual receipt stamp."></textarea>
                </div>
            </div>

            {{-- Validation warning --}}
            <div class="rb-validation-warning" id="rbValidationWarning">
                <i data-lucide="alert-circle"></i>
                <span>Please select at least one field to flag, or write a general note before submitting.</span>
            </div>

        </form>

        {{-- Sticky action bar (outside form, buttons submit via JS) --}}
        <div class="rb-action-bar">
            <div class="rb-action-bar-left">
                <span id="rbActionCount">0 fields flagged</span>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <a href="{{ route('dashboard') }}" class="rb-btn-cancel">
                    <i data-lucide="x" style="width:15px;height:15px;"></i>
                    Cancel
                </a>
                <button type="button" class="rb-btn-submit" id="rbSubmitBtn" onclick="submitRollbackForm()">
                    <i data-lucide="rotate-ccw"></i>
                    Send Back for Correction
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════ RIGHT SIDEBAR ══════════════ --}}
    <div class="rb-sidebar-col">
        <div class="rb-sidebar-sticky">

            {{-- Progress tracker --}}
            <div class="rb-sidebar-card">
                <div class="rb-sidebar-card-head">
                    <div class="rb-section-icon green" style="width:26px;height:26px; border-radius:8px;">
                        <i data-lucide="check-circle-2" style="width:13px;height:13px;"></i>
                    </div>
                    <span class="rb-sidebar-card-title">Fields Flagged</span>
                </div>
                <div class="rb-progress-widget">
                    <div class="rb-progress-label">Progress</div>
                    <div class="rb-progress-bar-wrap">
                        <div class="rb-progress-bar" id="rbProgressBar"></div>
                    </div>
                    <div class="rb-progress-count" id="rbProgressCount">0 of 6 selected</div>
                </div>
            </div>

            {{-- SRA Summary --}}
            <div class="rb-sidebar-card">
                <div class="rb-sidebar-card-head">
                    <div class="rb-section-icon blue" style="width:26px;height:26px; border-radius:8px;">
                        <i data-lucide="file-text" style="width:13px;height:13px;"></i>
                    </div>
                    <span class="rb-sidebar-card-title">SRA Summary</span>
                </div>
                <div class="rb-sidebar-card-body">
                    <div class="rb-detail-row">
                        <span class="rb-detail-key">SRA ID</span>
                        <span class="rb-detail-val">#{{ str_pad($editReq->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="rb-detail-row">
                        <span class="rb-detail-key">{{ $isDonor ? 'Donor' : 'Supplier' }}</span>
                        <span class="rb-detail-val">{{ $provider ?: '—' }}</span>
                    </div>
                    <div class="rb-detail-row">
                        <span class="rb-detail-key">Status</span>
                        <span class="rb-detail-val">
                            <span style="background: rgba(5,150,105,0.1); color: #059669; padding: 2px 9px; border-radius: 999px; font-size: 0.72rem; font-weight: 900;">
                                {{ $statusLabel }}
                            </span>
                        </span>
                    </div>
                    <div class="rb-detail-row">
                        <span class="rb-detail-key">Date</span>
                        <span class="rb-detail-val">{{ $receivedDate }}</span>
                    </div>

                    @if(count($items) > 0)
                        {{-- Per-item breakdown --}}
                        <div style="padding: 8px 0 4px; border-bottom: 1px solid #f8fafc;">
                            <span style="font-size: 0.68rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em;">
                                Items ({{ count($items) }})
                            </span>
                        </div>
                        @foreach($items as $i => $item)
                        <div style="padding: 7px 0; border-bottom: 1px solid #f8fafc; {{ $loop->last ? 'border-bottom: none;' : '' }}">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 6px;">
                                <span style="font-size: 0.75rem; color: #1e293b; font-weight: 800; flex: 1; line-height: 1.4;">
                                    {{ \Str::limit($item['description'] ?? 'Item ' . ($i+1), 32) }}
                                </span>
                                <span style="font-size: 0.78rem; color: #0f172a; font-weight: 800; white-space: nowrap; flex-shrink: 0;">
                                    {{ $item['qty'] ?? '—' }} {{ $item['unit'] ?? '' }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    @else
                        {{-- Fallback flat display --}}
                        <div class="rb-detail-row">
                            <span class="rb-detail-key">Qty</span>
                            <span class="rb-detail-val">{{ $itemQty }} {{ $itemUnit !== 'N/A' ? $itemUnit : '' }}</span>
                        </div>
                        <div class="rb-detail-row">
                            <span class="rb-detail-key">Item</span>
                            <span class="rb-detail-val">{{ \Str::limit($itemDesc, 38) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tips --}}
            <div class="rb-sidebar-card">
                <div class="rb-sidebar-card-head">
                    <div class="rb-section-icon amber" style="width:26px;height:26px; border-radius:8px;">
                        <i data-lucide="lightbulb" style="width:13px;height:13px;"></i>
                    </div>
                    <span class="rb-sidebar-card-title">Tips</span>
                </div>
                <div class="rb-sidebar-card-body">
                    <div class="rb-tip">
                        <div class="rb-tip-dot"></div>
                        <p>Check every field that doesn't match the physical receipt or delivery note.</p>
                    </div>
                    <div class="rb-tip">
                        <div class="rb-tip-dot"></div>
                        <p>Add a specific correction note so the user knows exactly what to fix.</p>
                    </div>
                    <div class="rb-tip">
                        <div class="rb-tip-dot"></div>
                        <p>Use the General Note for overall context or additional instructions.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') lucide.createIcons();
    updateProgress();
});

function toggleFieldRow(checkbox) {
    const row  = document.getElementById('rbrow-' + checkbox.value);
    const wrap = document.getElementById('rbnote-' + checkbox.value);
    if (!row || !wrap) return;

    const noteInput = wrap.querySelector('.rb-note-input');

    if (checkbox.checked) {
        row.classList.add('checked-row');
        wrap.classList.add('visible');
        if (noteInput) {
            noteInput.removeAttribute('disabled');
            // Select2 for package type
            if (noteInput.classList.contains('select2-unit-rollback')) {
                setTimeout(() => {
                    $(noteInput).select2({ placeholder: 'Select or type package type...', tags: true, width: '100%' });
                }, 60);
            } else {
                setTimeout(() => noteInput.focus(), 60);
            }
        }
    } else {
        row.classList.remove('checked-row');
        wrap.classList.remove('visible');
        if (noteInput) noteInput.setAttribute('disabled', 'true');
    }

    updateProgress();
}

function updateProgress() {
    const total   = 6;
    const checked = document.querySelectorAll('.rb-check:checked').length;
    const pct     = (checked / total) * 100;
    document.getElementById('rbProgressBar').style.width   = pct + '%';
    document.getElementById('rbProgressCount').textContent = checked + ' of ' + total + ' selected';

    // Sync action bar counter
    const countEl = document.getElementById('rbActionCount');
    if (countEl) {
        countEl.innerHTML = checked > 0
            ? '<strong>' + checked + ' field' + (checked > 1 ? 's' : '') + ' flagged</strong>'
            : '0 fields flagged';
    }
}

function submitRollbackForm() {
    const checkedCount = document.querySelectorAll('.rb-check:checked').length;
    const generalNote  = document.querySelector('textarea[name="general_note"]').value.trim();
    const warning      = document.getElementById('rbValidationWarning');

    if (checkedCount === 0 && !generalNote) {
        warning.style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
        warning.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    // Disable button and show loading state
    const btn = document.getElementById('rbSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" style="width:18px;height:18px;" class="spin"></i> Sending...';
    if (typeof lucide !== 'undefined') lucide.createIcons();

    warning.style.display = 'none';
    document.getElementById('rollbackForm').submit();
}

function validateRollbackForm() { return true; }

// Spinner animation
const style = document.createElement('style');
style.textContent = `@keyframes spin { to { transform: rotate(360deg); } } .spin { animation: spin 0.8s linear infinite; }`;
document.head.appendChild(style);
</script>
@endsection
