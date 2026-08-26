@extends('layouts.dashboard')

@php
    $isAdmin = auth()->user()->is_admin;
    $batchId = $batch->id;
    $batchIdPadded = str_pad($batchId, 4, '0', STR_PAD_LEFT);
    $supplierStatus = $batch->supplier_status ?? 'Full Delivery';
    $isDonor = $supplierStatus === 'Donor';
    $supplierName = $isDonor ? ($batch->donor_name ?? $batch->supplier_name ?? '') : ($batch->supplier_name ?? '');
@endphp

@section('title', 'Edit Inventory Entry #' . $batchIdPadded)

@section('content')
<style>
    .edit-page-wrapper {
        max-width: 1600px;
        margin: 0 auto;
        padding: 2rem 2rem 4rem;
    }

    .edit-section-card {
        background: var(--bg-card, #fff);
        border: 1.5px solid var(--border-color, #f1f5f9);
        border-radius: 20px;
        padding: 2rem 2.5rem;
        margin-bottom: 1.75rem;
        box-shadow: 0 2px 16px rgba(0,0,0,0.04);
        transition: box-shadow 0.3s;
    }
    .edit-section-card:hover {
        box-shadow: 0 4px 24px rgba(0,0,0,0.07);
    }

    .edit-section-label {
        font-size: 0.72rem;
        font-weight: 900;
        color: #059669;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .edit-section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-color, #f1f5f9);
    }

    .edit-field-label {
        display: block;
        font-size: 0.68rem;
        font-weight: 900;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 8px;
    }
    .edit-field-label.required::after {
        content: ' *';
        color: #ef4444;
    }

    .edit-input {
        width: 100%;
        padding: 0.85rem 1rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.9rem;
        color: #1e293b;
        background: #f8fafc;
        transition: all 0.2s;
        font-family: inherit;
        box-sizing: border-box;
    }
    .edit-input:focus {
        outline: none;
        border-color: #059669;
        box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.08);
        background: #fff;
    }
    .edit-input:disabled {
        color: #94a3b8;
        cursor: not-allowed;
        background: #f1f5f9;
    }
    .edit-input[readonly] {
        cursor: default;
    }

    .item-edit-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        padding: 1.75rem;
        position: relative;
        transition: box-shadow 0.25s, border-color 0.25s;
        overflow: hidden;
    }
    .item-edit-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border-color: #e2e8f0;
    }
    .item-edit-card .item-accent {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        background: #059669;
        border-radius: 16px 0 0 16px;
    }

    .action-bar {
        position: sticky;
        bottom: 0;
        background: var(--bg-card, #fff);
        border-top: 1.5px solid var(--border-color, #f1f5f9);
        padding: 1.25rem 2.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        z-index: 100;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.06);
        border-radius: 0 0 20px 20px;
        margin-top: 2rem;
    }

    .btn-discard {
        padding: 0.85rem 2rem;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.9rem;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        color: #475569;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-discard:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .btn-submit-edit {
        padding: 0.85rem 2.5rem;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.9rem;
        background: #059669;
        border: none;
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 10px 20px rgba(5, 150, 105, 0.25);
        transition: all 0.2s;
    }
    .btn-submit-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(5, 150, 105, 0.35);
    }
    .btn-submit-edit:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
    }

    .select2-container--default .select2-selection--single {
        height: 48px;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        background: #f8fafc !important;
        display: flex;
        align-items: center;
        padding: 0 0.5rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        font-weight: 700;
        color: #1e293b;
        line-height: 46px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px;
        right: 10px;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #059669 !important;
        box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.08) !important;
        background: #fff !important;
    }
    .select2-dropdown {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important;
        overflow: hidden;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #059669 !important;
        color: #ffffff !important;
    }
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: rgba(5, 150, 105, 0.08);
        color: #059669;
        font-weight: 800;
    }
    .select2-search--dropdown .select2-search__field {
        border-radius: 8px !important;
        border: 1.5px solid #e2e8f0 !important;
        padding: 8px 12px !important;
    }
    .select2-search--dropdown .select2-search__field:focus {
        border-color: #059669 !important;
        outline: none;
    }

    @media (max-width: 768px) {
        .edit-page-wrapper { padding: 1rem 0.75rem 3rem; }
        .edit-section-card { padding: 1.25rem; }
        .action-bar { padding: 1rem; flex-wrap: wrap; }
    }

    .loader-spin {
        width: 18px;
        height: 18px;
        border: 2.5px solid rgba(255,255,255,0.4);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="edit-page-wrapper">

    {{-- ── Back Navigation ──────────────────────────────────── --}}
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('receiveditems') }}" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: var(--text-muted); font-weight: 700; font-size: 0.85rem; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Received Items Log
        </a>
    </div>

    {{-- ── Page Header ─────────────────────────────────────── --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 6px;">
                <div style="background: #059669; width: 44px; height: 44px; border-radius: 14px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 16px rgba(5,150,105,0.3);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </div>
                <div>
                    <h1 style="font-size: 1.6rem; font-weight: 950; color: var(--text-main); margin: 0; letter-spacing: -0.03em;">Edit Inventory Entry</h1>
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-top: 2px; text-transform: uppercase; letter-spacing: 0.05em;">BATCH #{{ $batchIdPadded }}</div>
                </div>
            </div>
        </div>

        <div style="background: rgba(5,150,105,0.05); border: 1px dashed rgba(5,150,105,0.25); border-radius: 14px; padding: 1rem 1.5rem; font-size: 0.8rem; color: var(--text-muted);">
            <div style="font-weight: 700; margin-bottom: 4px; color: var(--text-main);">Entry Information</div>
            <div>Recorded by: <strong>{{ $batch->recorder?->name ?? 'Unknown' }}</strong></div>
            <div>Entry date: <strong>{{ \Carbon\Carbon::parse($batch->entry_date)->format('d M Y, H:i') }}</strong></div>
            @if(!$isAdmin)
                <div style="margin-top: 8px; color: #d97706; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Changes will be sent for admin approval
                </div>
            @endif
        </div>
    </div>

    <form id="editBatchPageForm" onsubmit="event.preventDefault(); submitEditPage();">

        {{-- ── Section 1: Received Details ─────────────────────── --}}
        <div class="edit-section-card">
            <div class="edit-section-label">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
                Received Details
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                {{-- Received Date --}}
                <div>
                    <label class="edit-field-label">Received Date</label>
                    <div style="position: relative;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
                        <input type="date" id="edit_arrival_date" name="arrival_date" class="edit-input"
                               value="{{ $batch->arrival_date ? \Carbon\Carbon::parse($batch->arrival_date)->format('Y-m-d') : '' }}"
                               style="padding-left: 2.5rem;">
                    </div>
                </div>

                {{-- Category --}}
                <div>
                    <label class="edit-field-label">Category</label>
                    <select id="edit_category" name="ledge_category" class="edit-input">
                        @foreach($ledgeMap as $code => $name)
                            <option value="{{ $code }}" {{ $batch->ledge_category == $code ? 'selected' : '' }}>CAT {{ $code }} | {{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Delivery Status --}}
                <div>
                    <label class="edit-field-label">Delivery Status</label>
                    <select id="edit_acquisition_status" name="acquisition_status" class="edit-input" onchange="toggleEditPageSourceFields()">
                        <option value="Full Delivery"     {{ $supplierStatus === 'Full Delivery'     ? 'selected' : '' }}>Full Delivery</option>
                        <option value="Partial Delivery"  {{ $supplierStatus === 'Partial Delivery'  ? 'selected' : '' }}>Partial Delivery</option>
                        <option value="Donor"             {{ $supplierStatus === 'Donor'             ? 'selected' : '' }}>Donation</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- ── Section 2: Supplier / Donor ─────────────────────── --}}
        <div class="edit-section-card">
            <div class="edit-section-label">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Supplier / Donor Information
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;">
                {{-- Supplier Field --}}
                <div id="edit_supplier_field" style="{{ $isDonor ? 'display:none;' : '' }}">
                    <label class="edit-field-label">Supplier Name</label>
                    <select id="edit_supplier_name" name="supplier_name" class="select2-page-edit" style="width: 100%;">
                        <option value="">Select or type supplier...</option>
                        @foreach($allSuppliers as $sup)
                            <option value="{{ $sup }}" {{ $batch->supplier_name == $sup ? 'selected' : '' }}>{{ $sup }}</option>
                        @endforeach
                        @if($batch->supplier_name && !$allSuppliers->contains($batch->supplier_name) && !$isDonor)
                            <option value="{{ $batch->supplier_name }}" selected>{{ $batch->supplier_name }}</option>
                        @endif
                    </select>
                </div>

                {{-- Donor Field --}}
                <div id="edit_donor_field" style="{{ !$isDonor ? 'display:none;' : '' }}">
                    <label class="edit-field-label">Donor Name</label>
                    <select id="edit_donor_name" name="donor_name" class="select2-page-edit" style="width: 100%;">
                        <option value="">Select or type donor...</option>
                        @foreach($allDonors as $donor)
                            <option value="{{ $donor }}" {{ ($batch->donor_name == $donor || $batch->supplier_name == $donor) ? 'selected' : '' }}>{{ $donor }}</option>
                        @endforeach
                        @if($isDonor && $batch->donor_name && !$allDonors->contains($batch->donor_name))
                            <option value="{{ $batch->donor_name }}" selected>{{ $batch->donor_name }}</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>

        {{-- ── Section 3: Supplier & Delivery Details ──────────── --}}
        <div class="edit-section-card">
            <div class="edit-section-label">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Supplier &amp; Delivery Details
            </div>

            {{-- Sub-header: Supplier Information --}}
            <div style="font-size: 0.68rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Supplier Information
                <div style="flex:1; height:1px; background:#f1f5f9;"></div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                {{-- Contact Number --}}
                <div>
                    <label class="edit-field-label">Contact Number</label>
                    <div style="position: relative;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13 19.79 19.79 0 0 1 1.61 4.35 2 2 0 0 1 3.58 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.06 6.06l1.17-1.17a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <input type="text" id="edit_supplier_phone" name="supplier_phone" class="edit-input"
                               placeholder="Enter supplier phone"
                               value="{{ $supplierProfile?->phone ?? '' }}"
                               style="padding-left: 2.5rem;">
                    </div>
                </div>

                {{-- Email Address --}}
                <div>
                    <label class="edit-field-label">Email Address</label>
                    <div style="position: relative;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        <input type="email" id="edit_supplier_email" name="supplier_email" class="edit-input"
                               placeholder="Enter supplier email"
                               value="{{ $supplierProfile?->email ?? '' }}"
                               style="padding-left: 2.5rem;">
                    </div>
                </div>

                {{-- Physical Address --}}
                <div>
                    <label class="edit-field-label">Physical Address</label>
                    <div style="position: relative;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        <input type="text" id="edit_supplier_address" name="supplier_address" class="edit-input"
                               placeholder="Enter supplier physical address"
                               value="{{ $supplierProfile?->address ?? '' }}"
                               style="padding-left: 2.5rem;">
                    </div>
                </div>

                {{-- Contact Person Name --}}
                <div>
                    <label class="edit-field-label">Contact Person Name</label>
                    <input type="text" id="edit_contact_person" name="contact_person" class="edit-input"
                           placeholder="Enter contact person's name"
                           value="{{ $supplierProfile?->contact_person ?? '' }}">
                </div>

                {{-- Contact Person Number --}}
                <div>
                    <label class="edit-field-label">Contact Person Number</label>
                    <div style="position: relative;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13 19.79 19.79 0 0 1 1.61 4.35 2 2 0 0 1 3.58 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.06 6.06l1.17-1.17a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <input type="text" id="edit_contact_phone" name="contact_phone" class="edit-input"
                               placeholder="Enter phone number"
                               value="{{ $supplierProfile?->contact_phone ?? '' }}"
                               style="padding-left: 2.5rem;">
                    </div>
                </div>
            </div>

            {{-- Sub-header: Delivery Information --}}
            <div style="font-size: 0.68rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect width="7" height="7" x="14" y="10" rx="1"/><path d="M10 14v1a1 1 0 0 0 1 1h3"/></svg>
                Delivery Information
                <div style="flex:1; height:1px; background:#f1f5f9;"></div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                {{-- Delivery Person Name --}}
                <div>
                    <label class="edit-field-label">Delivery Person Name</label>
                    <input type="text" id="edit_delivery_person" name="delivery_person" class="edit-input"
                           placeholder="Enter delivery's name"
                           value="{{ $batch->delivery_person ?? ($supplierProfile?->delivery_person ?? '') }}">
                </div>

                {{-- Delivery Person Number --}}
                <div>
                    <label class="edit-field-label">Delivery Person Number</label>
                    <div style="position: relative;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13 19.79 19.79 0 0 1 1.61 4.35 2 2 0 0 1 3.58 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.06 6.06l1.17-1.17a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <input type="text" id="edit_delivery_phone" name="delivery_phone" class="edit-input"
                               placeholder="Enter phone number"
                               value="{{ $batch->delivery_phone ?? ($supplierProfile?->delivery_phone ?? '') }}"
                               style="padding-left: 2.5rem;">
                    </div>
                </div>
            </div>
        </div>


        {{-- ── Section 4: Item Details ─────────────────────────── --}}
        <div class="edit-section-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem;">
                <div class="edit-section-label" style="margin-bottom: 0; flex: 1;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    Item Information
                </div>
                <span style="background: #f1f5f9; color: #475569; padding: 4px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 800;">{{ $batch->items->count() }} Item{{ $batch->items->count() !== 1 ? 's' : '' }}</span>
            </div>

            <div id="editItemsList" style="display: flex; flex-direction: column; gap: 1.25rem;">
                @foreach($batch->items as $index => $item)
                @php
                    $itemStoreLocation = strtoupper(trim($item->store_location ?? 'STORE A'));
                @endphp
                <div class="item-edit-card" data-item-id="{{ $item->id }}">
                    <div class="item-accent"></div>
                    <input type="hidden" class="item-id-field" value="{{ $item->id }}">
                    <input type="hidden" class="item-stock-balance-field" value="{{ $item->stock_balance }}">

                    {{-- Item header --}}
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 1.25rem; padding-left: 8px;">
                        <div style="background: rgba(5,150,105,0.08); color: #059669; width: 26px; height: 26px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 900; flex-shrink: 0;">{{ $index + 1 }}</div>
                        <input type="text" class="item-description-field" value="{{ $item->description }}" placeholder="Item Description"
                               style="flex: 1; border: none; background: transparent; font-size: 1rem; font-weight: 800; color: var(--text-main); outline: none; padding: 4px 0; border-bottom: 2px solid transparent; transition: border-color 0.2s;"
                               onfocus="this.style.borderBottomColor='#059669'" onblur="this.style.borderBottomColor='transparent'">
                    </div>

                    {{-- Fields Grid Row 1 --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1.25rem; margin-bottom: 1.25rem; padding-left: 8px;">
                        <div>
                            <label class="edit-field-label">Package Type</label>
                            <input type="text" class="item-unit-field edit-input" value="{{ $item->unit }}">
                        </div>
                        <div>
                            <label class="edit-field-label">Qty Received</label>
                            <input type="number" class="item-qty-field edit-input" value="{{ $item->qty }}" min="0"
                                   oninput="recalcPageVariance(this)">
                        </div>
                        <div>
                            <label class="edit-field-label">Variance</label>
                            <input type="number" class="item-variance-field edit-input" value="{{ $item->variance }}"
                                   style="text-align: center; font-weight: 900; color: {{ (float)($item->variance ?? 0) < 0 ? '#ef4444' : '#059669' }}; background: {{ (float)($item->variance ?? 0) < 0 ? 'rgba(239,68,68,0.06)' : 'rgba(5,150,105,0.06)' }}">
                        </div>
                    </div>

                    {{-- Fields Grid Row 2 --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; padding-left: 8px;">
                        {{-- Store Location --}}
                        <div>
                            <label class="edit-field-label">Store Location</label>
                            <select class="item-store-location-field select2-store-loc" style="width: 100%;">
                                @foreach($storeLocations as $loc)
                                    <option value="{{ $loc }}" {{ $itemStoreLocation === $loc ? 'selected' : '' }}>{{ $loc }}</option>
                                @endforeach
                                @if(!in_array($itemStoreLocation, $storeLocations))
                                    <option value="{{ $itemStoreLocation }}" selected>{{ $itemStoreLocation }}</option>
                                @endif
                            </select>
                        </div>
                        {{-- Remarks --}}
                        <div>
                            <label class="edit-field-label">Remarks</label>
                            <textarea class="item-remarks-field edit-input" rows="1"
                                      style="resize: none; height: 48px; padding-top: 0.75rem;">{{ $item->remarks ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── Action Bar ────────────────────────────────────────── --}}
        <div class="action-bar">
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">
                @if($isAdmin)
                    <span style="color: #059669; font-weight: 800;">✓ Admin</span> — Changes will be saved immediately
                @else
                    <span style="color: #d97706; font-weight: 800;">⚠</span> — Changes require admin approval
                @endif
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="{{ route('receiveditems') }}" class="btn-discard">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Discard
                </a>
                <button type="submit" id="saveEditPageBtn" class="btn-submit-edit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19 7-7-7-7"/><path d="M5 19l7-7-7-7"/></svg>
                    {{ $isAdmin ? 'Save Changes' : 'Submit for Approval' }}
                </button>
            </div>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Supplier/donor Select2
    $('.select2-page-edit').select2({
        tags: true,
        width: '100%',
        placeholder: 'Search or type...',
        allowClear: true
    });

    // Store location Select2 (tags allowed — can add new)
    $('.select2-store-loc').select2({
        tags: true,
        width: '100%',
        placeholder: 'Select or type location...',
        createTag: function(params) {
            var term = $.trim(params.term).toUpperCase();
            if (!term) return null;
            return { id: term, text: term, newTag: true };
        }
    });

    // Pre-set supplier / donor values
    @if($isDonor)
        var donorVal = @json($batch->donor_name ?? $batch->supplier_name ?? '');
        if (donorVal) {
            var $sel = $('#edit_donor_name');
            if ($sel.find('option[value="' + donorVal + '"]').length === 0) {
                $sel.append(new Option(donorVal, donorVal, true, true));
            }
            $sel.val(donorVal).trigger('change');
        }
    @else
        var supplierVal = @json($batch->supplier_name ?? '');
        if (supplierVal) {
            var $sel = $('#edit_supplier_name');
            if ($sel.find('option[value="' + supplierVal + '"]').length === 0) {
                $sel.append(new Option(supplierVal, supplierVal, true, true));
            }
            $sel.val(supplierVal).trigger('change');
        }
    @endif
});

function toggleEditPageSourceFields() {
    var status = document.getElementById('edit_acquisition_status').value;
    var sup = document.getElementById('edit_supplier_field');
    var don = document.getElementById('edit_donor_field');
    if (status === 'Donor') {
        sup.style.display = 'none';
        don.style.display = 'block';
    } else {
        sup.style.display = 'block';
        don.style.display = 'none';
    }
}

function recalcPageVariance(input) {
    var card = input.closest('.item-edit-card');
    var qty = parseFloat(input.value) || 0;
    var stockInput = card.querySelector('.item-stock-balance-field');
    if (stockInput) stockInput.value = qty;

    var variance = 0; // qty adjusted is the new baseline
    var varInput = card.querySelector('.item-variance-field');
    varInput.value = variance;
    varInput.style.color = '#059669';
    varInput.style.background = 'rgba(5, 150, 105, 0.06)';
}

function buildItemsPayload() {
    var items = [];
    document.querySelectorAll('#editItemsList .item-edit-card').forEach(function(card) {
        var locSelect = card.querySelector('.item-store-location-field');
        var locVal = locSelect ? ($(locSelect).val() || 'STORE A') : 'STORE A';
        items.push({
            id: card.querySelector('.item-id-field').value,
            description: card.querySelector('.item-description-field').value,
            unit: card.querySelector('.item-unit-field').value,
            qty: card.querySelector('.item-qty-field').value,
            stock_balance: card.querySelector('.item-stock-balance-field').value,
            variance: card.querySelector('.item-variance-field').value,
            store_location: locVal,
            remarks: card.querySelector('.item-remarks-field').value
        });
    });
    return items;
}

async function submitEditPage() {
    var btn = document.getElementById('saveEditPageBtn');
    var isAdmin = {{ $isAdmin ? 'true' : 'false' }};
    var batchId = {{ $batchId }};

    var supplierStatus = document.getElementById('edit_acquisition_status').value;
    var acqType = supplierStatus === 'Donor' ? 'Donor' : 'Supplier';
    var supplierName = supplierStatus === 'Donor' ? '' : ($('#edit_supplier_name').val() || '');
    var donorName   = supplierStatus === 'Donor' ? ($('#edit_donor_name').val() || '') : '';

    var deliveryPerson = document.getElementById('edit_delivery_person').value.trim();
    var deliveryPhone  = document.getElementById('edit_delivery_phone').value.trim();
    var contactPerson  = document.getElementById('edit_contact_person').value.trim();
    var contactPhone   = document.getElementById('edit_contact_phone').value.trim();
    var supplierPhone  = document.getElementById('edit_supplier_phone').value.trim();
    var supplierEmail  = document.getElementById('edit_supplier_email').value.trim();
    var supplierAddr   = document.getElementById('edit_supplier_address').value.trim();

    var items = buildItemsPayload();

    var payload = {
        arrival_date: document.getElementById('edit_arrival_date').value,
        ledge_category: document.getElementById('edit_category').value,
        acquisition_type: acqType,
        supplier_name: supplierName || null,
        supplier_status: supplierStatus,
        donor_name: donorName || null,
        delivery_person: deliveryPerson || null,
        delivery_phone: deliveryPhone || null,
        contact_person: contactPerson || null,
        contact_phone: contactPhone || null,
        supplier_phone: supplierPhone || null,
        supplier_email: supplierEmail || null,
        supplier_address: supplierAddr || null,
        items: items,
        _token: '{{ csrf_token() }}'
    };

    // Validate required fields
    if (!payload.arrival_date) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Validation Error', 'Please enter a received date.', 'warning');
        } else { alert('Please enter a received date.'); }
        return;
    }

    // Staff officers: prompt for justification
    var reason = 'Direct administrative modification.';
    if (!isAdmin) {
        var result = await Swal.fire({
            html: `
                <div style="text-align: left;">
                    <div style="background: #059669; margin: -1.25em -1.25em 1.5em; padding: 2rem 2rem 1.5rem; border-radius: 4px 4px 0 0; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: rgba(255,255,255,0.06); border-radius: 50%;"></div>
                        <div style="display: flex; align-items: center; gap: 14px; position: relative;">
                            <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.15); border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg style="width: 26px; height: 26px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <div>
                                <div style="font-size: 0.7rem; font-weight: 800; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 3px;">Oversight Protocol</div>
                                <div style="font-size: 1.3rem; font-weight: 900; color: white; letter-spacing: -0.02em;">Edit Justification</div>
                            </div>
                        </div>
                    </div>

                    <p style="font-size: 0.9rem; color: #64748b; line-height: 1.6; margin-bottom: 1.25rem; padding: 0 0.25rem;">
                        Explain why you are modifying this record. Your justification will be reviewed by an administrator before changes take effect.
                    </p>

                    <textarea id="swal-edit-page-justification" placeholder="e.g. Correcting a quantity typo, updating supplier info after delivery confirmation..."
                              style="width: 100%; min-height: 110px; font-size: 0.9rem; border-radius: 14px; border: 2px solid #f1f5f9; padding: 1rem 1.25rem; font-family: inherit; resize: vertical; outline: none; transition: all 0.3s; box-sizing: border-box; color: #0f172a; background: #f8fafc;"
                              onfocus="this.style.borderColor='#059669'; this.style.boxShadow='0 0 0 4px rgba(5,150,105,0.08)'"
                              onblur="this.style.borderColor='#f1f5f9'; this.style.boxShadow='none'"></textarea>

                    <div style="margin-top: 1rem; padding: 10px 14px; background: rgba(5,150,105,0.07); border: 1px solid rgba(5,150,105,0.2); border-radius: 10px; display: flex; align-items: center; gap: 10px;">
                        <svg style="width: 16px; height: 16px; color: #047857; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span style="font-size: 0.78rem; font-weight: 700; color: #92400e;">This justification is mandatory and will be permanently logged in the audit trail.</span>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '&#10003;&nbsp; Submit for Approval',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#059669',
            cancelButtonColor: '#94a3b8',
            focusConfirm: false,
            didOpen: function() {
                if (!document.getElementById('swal-page-styles')) {
                    var s = document.createElement('style');
                    s.id = 'swal-page-styles';
                    s.textContent = '.swal2-popup { border-radius: 24px !important; overflow: hidden !important; padding: 1.25em !important; }';
                    document.head.appendChild(s);
                }
            },
            preConfirm: function() {
                var val = document.getElementById('swal-edit-page-justification').value.trim();
                if (!val) {
                    Swal.showValidationMessage('<span style="font-size:0.85rem;">⚠ Justification is mandatory for audit trails!</span>');
                    return false;
                }
                return val;
            }
        });
        if (!result.isConfirmed) return;
        reason = result.value;
    }

    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<div class="loader-spin"></div> ' + (isAdmin ? 'Saving...' : 'Submitting...');

    var url, method, finalPayload;
    if (isAdmin) {
        url = '{{ url("/received-items") }}/' + batchId;
        method = 'PUT';
        finalPayload = payload;
    } else {
        url = '{{ url("/edit-requests") }}';
        method = 'POST';
        finalPayload = {
            item_id: batchId,
            request_type: 'edit_submission',
            reason: reason,
            payload: JSON.stringify(payload)
        };
    }

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(finalPayload)
    })
    .then(function(res) {
        if (!res.ok) throw new Error('Server error ' + res.status);
        return res.json();
    })
    .then(function(data) {
        if (data.success) {
            if (typeof window.playNotificationSound === 'function') {
                window.playNotificationSound(isAdmin ? 'success' : 'sent');
            }
            Swal.fire({
                title: isAdmin ? 'Changes Saved' : 'Request Submitted',
                text: isAdmin ? 'The inventory batch has been updated successfully.' : 'Your edit request has been sent to the head of stores for approval.',
                icon: 'success',
                confirmButtonColor: '#059669'
            }).then(function() {
                window.location.href = '{{ route("receiveditems") }}';
            });
        } else {
            Swal.fire('Error', data.message || 'An unexpected error occurred.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19 7-7-7-7"/><path d="M5 19l7-7-7-7"/></svg> {{ $isAdmin ? "Save Changes" : "Submit for Approval" }}';
        }
    })
    .catch(function(err) {
        Swal.fire('Network Error', 'Could not reach the server. Please try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19 7-7-7-7"/><path d="M5 19l7-7-7-7"/></svg> {{ $isAdmin ? "Save Changes" : "Submit for Approval" }}';
    });
}
</script>
@endsection
