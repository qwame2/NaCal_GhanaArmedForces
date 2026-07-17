@extends('layouts.dashboard')
@section('content')

<style>
    .sra-header-section {
        padding: 1.75rem !important;
        background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-main) 100%) !important;
        border-radius: 0 0 32px 32px !important;
        margin: -24px -24px 2rem -24px !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.04) !important;
        border-bottom: 1px solid var(--border-color) !important;
    }
    .sra-form-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        padding: 2rem;
    }
    .sra-field-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 6px;
    }
    .sra-input {
        width: 100%;
        border: 1.5px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-main);
        padding: 0.75rem 1rem;
        border-radius: 12px;
        font-family: inherit;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }
    .sra-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(22,163,74,0.12);
    }
    .delivery-toggle {
        display: flex;
        gap: 1rem;
    }
    .delivery-option {
        flex: 1;
        border: 2px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem;
        cursor: pointer;
        transition: all 0.25s;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: var(--bg-main);
    }
    .delivery-option:hover {
        border-color: var(--primary);
        background: rgba(22,163,74,0.04);
    }
    .delivery-option.selected {
        border-color: var(--primary);
        background: rgba(22,163,74,0.07);
    }
    .delivery-option input[type="radio"] {
        accent-color: var(--primary);
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    .delivery-option-label {
        font-weight: 800;
        font-size: 0.9rem;
        color: var(--text-main);
    }
    .delivery-option-desc {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 2px;
    }
    .section-divider {
        border: none;
        border-top: 1px dashed var(--border-color);
        margin: 2rem 0;
    }
</style>

<div class="animate-slide-up">
    {{-- Header --}}
    <div class="page-header sra-header-section" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(22,163,74,0.1); color: var(--primary); font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em;">Stores Division</span>
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main); margin: 0;">Service <span style="color: var(--primary);">SRA</span></h2>
            <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Stores / Service Received Advice — Submit for administrative approval.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('service-sra.index') }}" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--text-main); text-decoration: none; transition: all 0.3s;" onmouseover="this.style.background='rgba(22,163,74,0.05)'" onmouseout="this.style.background='var(--bg-card)'">
                <i data-lucide="list" style="width: 16px;"></i> My SRAs
            </a>
            <a href="{{ route('dashboard') }}" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--text-main); text-decoration: none; transition: all 0.3s;" onmouseover="this.style.background='rgba(22,163,74,0.05)'" onmouseout="this.style.background='var(--bg-card)'">
                <i data-lucide="arrow-left" style="width: 16px;"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="sra-form-card">
        <form id="sraForm" novalidate>
            @csrf

            {{-- Section 1: Header Fields --}}
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0 0 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i data-lucide="file-text" style="color: var(--primary); width: 20px;"></i>
                SRA Header Information
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                {{-- Dept (auto) --}}
                <div class="form-group">
                    <label class="sra-field-label">
                        <i data-lucide="building-2" style="width: 13px; color: var(--primary);"></i>
                        Department
                    </label>
                    <input type="text" class="sra-input" value="{{ auth()->user()->department ?? 'N/A' }}" readonly style="background: var(--bg-main); cursor: not-allowed; opacity: 0.75;">
                </div>

                {{-- Station (auto) --}}
                <div class="form-group">
                    <label class="sra-field-label">
                        <i data-lucide="map-pin" style="width: 13px; color: var(--primary);"></i>
                        Station
                    </label>
                    <input type="text" class="sra-input" value="{{ \App\Models\Setting::get('organization_station', 'Accra') }}" readonly style="background: var(--bg-main); cursor: not-allowed; opacity: 0.75;">
                </div>

                {{-- Region (auto) --}}
                <div class="form-group">
                    <label class="sra-field-label">
                        <i data-lucide="globe" style="width: 13px; color: var(--primary);"></i>
                        Region
                    </label>
                    <input type="text" class="sra-input" value="{{ $region }}" readonly style="background: var(--bg-main); cursor: not-allowed; opacity: 0.75;">
                </div>

                {{-- Date of Delivery --}}
                <div class="form-group">
                    <label class="sra-field-label" for="date_of_delivery">
                        <i data-lucide="calendar" style="width: 13px; color: var(--primary);"></i>
                        Date of Delivery <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" id="date_of_delivery" name="date_of_delivery" class="sra-input" required>
                </div>
            </div>

            <hr class="section-divider">

            {{-- Section 2: Supplier --}}
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0 0 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i data-lucide="truck" style="color: var(--primary); width: 20px;"></i>
                Supplier / Delivery Details
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                {{-- Supplier Name --}}
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="sra-field-label" for="supplier_name_select">
                        <i data-lucide="wrench" style="width: 13px; color: var(--primary);"></i>
                        Service / Vehicle Supplier <span style="color: #ef4444;">*</span>
                        <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; margin-left: auto;">Type a new name to add a supplier</span>
                    </label>
                    <select id="supplier_name_select" name="supplier_name" style="width: 100%;" required>
                        <option value=""></option>
                        @foreach($allSuppliers as $sup)
                            <option value="{{ $sup->name }}" data-address="{{ $sup->address }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Supplier Address --}}
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="sra-field-label" for="supplier_address">
                        <i data-lucide="map" style="width: 13px; color: var(--primary);"></i>
                        Supplier Address
                    </label>
                    <input type="text" id="supplier_address" name="supplier_address" class="sra-input" placeholder="Physical address of supplier">
                </div>

                {{-- Vehicle Number --}}
                <div class="form-group">
                    <label class="sra-field-label" for="vehicle_number">
                        <i data-lucide="car" style="width: 13px; color: var(--primary);"></i>
                        Vehicle Number
                    </label>
                    <input type="text" id="vehicle_number" name="vehicle_number" class="sra-input" placeholder="e.g. GH-1234-20">
                </div>

                {{-- A&E No --}}
                <div class="form-group">
                    <label class="sra-field-label" for="ae_number">
                        <i data-lucide="hash" style="width: 13px; color: var(--primary);"></i>
                        A &amp; E No.
                    </label>
                    <input type="text" id="ae_number" name="ae_number" class="sra-input" placeholder="A&E reference number">
                </div>

                {{-- LPO No --}}
                <div class="form-group">
                    <label class="sra-field-label" for="lpo_number">
                        <i data-lucide="clipboard-list" style="width: 13px; color: var(--primary);"></i>
                        LPO No.
                    </label>
                    <input type="text" id="lpo_number" name="lpo_number" class="sra-input" placeholder="Local Purchase Order number">
                </div>
            </div>

            <hr class="section-divider">

            {{-- Section 3: Delivery Type --}}
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0 0 1rem; display: flex; align-items: center; gap: 10px;">
                <i data-lucide="check-square" style="color: var(--primary); width: 20px;"></i>
                Delivery / Performance Type
            </h3>

            <div class="delivery-toggle" style="margin-bottom: 1.5rem;">
                <label class="delivery-option selected" id="opt-full">
                    <input type="radio" name="delivery_type" value="full" checked>
                    <div>
                        <div class="delivery-option-label">Full Delivery</div>
                        <div class="delivery-option-desc">All items/services delivered in full</div>
                    </div>
                </label>
                <label class="delivery-option" id="opt-partial">
                    <input type="radio" name="delivery_type" value="partial">
                    <div>
                        <div class="delivery-option-label">Part Delivery</div>
                        <div class="delivery-option-desc">Partial delivery — previous SRA Nos required</div>
                    </div>
                </label>
            </div>

            {{-- Previous SRA Nos (shown only for partial) --}}
            <div id="previousSraGroup" style="display: none; margin-bottom: 1.5rem; background: rgba(16,185,129,0.06); border: 1px dashed rgba(16,185,129,0.4); border-radius: 14px; padding: 1.25rem;">
                <label class="sra-field-label" for="previous_sra_nos">
                    <i data-lucide="link" style="width: 13px; color: #10b981;"></i>
                    Previous SRA Numbers (if part delivery) <span style="color: #ef4444;">*</span>
                </label>
                <textarea id="previous_sra_nos" name="previous_sra_nos" class="sra-input" rows="3" placeholder="Enter previous SRA numbers, separated by commas or new lines&#10;e.g. SRA-000001, SRA-000002"></textarea>
            </div>

            <hr class="section-divider">

            {{-- Section 4: Details of Order/Service --}}
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0 0 1rem; display: flex; align-items: center; gap: 10px;">
                <i data-lucide="align-left" style="color: var(--primary); width: 20px;"></i>
                Details of Order / Service
            </h3>
            <div class="form-group" style="margin-bottom: 2rem;">
                <label class="sra-field-label" for="details">
                    <i data-lucide="edit-3" style="width: 13px; color: var(--primary);"></i>
                    Description of goods received / service performed <span style="color: #ef4444;">*</span>
                </label>
                <textarea id="details" name="details" class="sra-input" rows="8" placeholder="Being the servicing of a car&#10;Engine Oil&#10;Oil filter" required style="resize: vertical; min-height: 160px;"></textarea>
                <div style="margin-top: 6px; font-size: 0.75rem; color: var(--text-muted);">For vehicle servicing, list the service type and parts used (e.g. Engine Oil, Oil Filter, Air Filter).</div>
            </div>

            {{-- Footer / Submit --}}
            <div style="border-top: 1px solid var(--border-color); padding-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <a href="{{ route('service-sra.index') }}" class="glass-card" style="padding: 1rem 2rem; text-decoration: none; font-weight: 700; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; border-radius: 14px;">
                    Cancel
                </a>
                <button type="submit" id="sraSubmitBtn" class="btn-primary" style="padding: 1rem 3rem; border: none; border-radius: 14px; cursor: pointer; background: linear-gradient(135deg, var(--primary), #16a34a); color: white; font-weight: 800; font-size: 1rem; display: flex; align-items: center; gap: 0.75rem; box-shadow: 0 10px 25px -5px rgba(22,163,74,0.4);">
                    <i data-lucide="send" style="width: 18px;"></i>
                    Submit for Approval
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
@push('scripts')
<script>
jQuery(document).ready(function($) {
    // Set default date
    $('#date_of_delivery').val(new Date().toISOString().split('T')[0]);

    // Init Select2 — service-only supplier list (separate from inventory suppliers)
    $('#supplier_name_select').select2({
        placeholder: 'Search or type a service/vehicle supplier name',
        width: '100%',
        tags: true,
        createTag: function(params) {
            const term = $.trim(params.term);
            if (!term) return null;
            return { id: term, text: term, newTag: true };
        }
    });

    // Auto-fill address when an existing supplier is selected
    $('#supplier_name_select').on('select2:select', function(e) {
        const selectedOpt = $(this).find('option[value="' + CSS.escape(e.params.data.id) + '"]');
        const address = selectedOpt.attr('data-address') || '';
        if (address) { $('#supplier_address').val(address); }
        saveFormState();
    });

    // Delivery type toggle
    $('input[name="delivery_type"]').on('change', function() {
        const isPartial = $(this).val() === 'partial';
        $('#opt-full').toggleClass('selected', !isPartial);
        $('#opt-partial').toggleClass('selected', isPartial);
        if (isPartial) {
            $('#previousSraGroup').slideDown(250);
            $('#previous_sra_nos').attr('required', true);
        } else {
            $('#previousSraGroup').slideUp(250);
            $('#previous_sra_nos').removeAttr('required').val('');
        }
        saveFormState();
    });

    $('label.delivery-option').on('click', function() {
        $('label.delivery-option').removeClass('selected');
        $(this).addClass('selected');
    });

    // ── Draft persistence ──────────────────────────────────────────────────────
    function saveFormState() {
        const state = {
            supplier_name:     $('#supplier_name_select').val(),
            supplier_address:  $('#supplier_address').val(),
            vehicle_number:    $('#vehicle_number').val(),
            ae_number:         $('#ae_number').val(),
            lpo_number:        $('#lpo_number').val(),
            delivery_type:     $('input[name="delivery_type"]:checked').val(),
            previous_sra_nos:  $('#previous_sra_nos').val(),
            details:           $('#details').val(),
            date_of_delivery:  $('#date_of_delivery').val(),
        };
        localStorage.setItem('service_sra_draft', JSON.stringify(state));
    }

    function restoreFormState() {
        const raw = localStorage.getItem('service_sra_draft');
        if (!raw) return;
        try {
            const s = JSON.parse(raw);
            if (s.date_of_delivery) $('#date_of_delivery').val(s.date_of_delivery);
            if (s.supplier_name) {
                if ($('#supplier_name_select option[value="' + s.supplier_name + '"]').length === 0) {
                    $('#supplier_name_select').append(new Option(s.supplier_name, s.supplier_name, true, true));
                }
                $('#supplier_name_select').val(s.supplier_name).trigger('change.select2');
            }
            if (s.supplier_address) $('#supplier_address').val(s.supplier_address);
            if (s.vehicle_number) $('#vehicle_number').val(s.vehicle_number);
            if (s.ae_number) $('#ae_number').val(s.ae_number);
            if (s.lpo_number) $('#lpo_number').val(s.lpo_number);
            if (s.delivery_type) {
                $('input[name="delivery_type"][value="' + s.delivery_type + '"]').prop('checked', true).trigger('change');
            }
            if (s.previous_sra_nos) $('#previous_sra_nos').val(s.previous_sra_nos);
            if (s.details) $('#details').val(s.details);
        } catch(e) { console.error('SRA draft restore failed', e); }
    }

    // Auto-save on any change
    $(document).on('change input', '#sraForm input, #sraForm select, #sraForm textarea', saveFormState);
    $('#supplier_name_select').on('select2:select select2:unselect', saveFormState);

    restoreFormState();

    // ── Form submission ────────────────────────────────────────────────────────
    $('#sraForm').on('submit', function(e) {
        e.preventDefault();

        const supplierName = $('#supplier_name_select').val()?.trim();
        const details      = $('#details').val()?.trim();
        const deliveryDate = $('#date_of_delivery').val();
        const deliveryType = $('input[name="delivery_type"]:checked').val();

        if (!supplierName) {
            Swal.fire({ icon: 'warning', title: 'Missing Supplier', text: 'Please enter or select the supplier name.', confirmButtonColor: 'var(--primary)' });
            return;
        }
        if (!details) {
            Swal.fire({ icon: 'warning', title: 'Missing Details', text: 'Please enter the details of the order or service.', confirmButtonColor: 'var(--primary)' });
            return;
        }

        const isPartial = deliveryType === 'partial';
        const prevSraNos = isPartial ? $('#previous_sra_nos').val()?.trim() : '';
        if (isPartial && !prevSraNos) {
            Swal.fire({ icon: 'warning', title: 'Missing Info', text: 'Please enter the previous SRA numbers for part delivery.', confirmButtonColor: 'var(--primary)' });
            return;
        }

        const payload = {
            _token:           $('meta[name="csrf-token"]').attr('content'),
            supplier_name:    supplierName,
            supplier_address: $('#supplier_address').val()?.trim(),
            vehicle_number:   $('#vehicle_number').val()?.trim(),
            ae_number:        $('#ae_number').val()?.trim(),
            lpo_number:       $('#lpo_number').val()?.trim(),
            delivery_type:    deliveryType,
            previous_sra_nos: prevSraNos,
            details:          details,
            date_of_delivery: deliveryDate,
        };

        const $btn = $('#sraSubmitBtn');
        const origHtml = $btn.html();
        $btn.html('<i data-lucide="loader" style="width:18px;"></i> Submitting...').prop('disabled', true);
        if (typeof lucide !== 'undefined') lucide.createIcons();

        $.ajax({
            url: '{{ route("service-sra.store") }}',
            method: 'POST',
            data: payload,
            success: function(res) {
                if (res.success) {
                    localStorage.removeItem('service_sra_draft');
                    if (typeof window.playNotificationSound === 'function') window.playNotificationSound('sent');
                    Swal.fire({
                        icon: 'success',
                        title: 'SRA Submitted!',
                        html: `<b>${res.sra_number}</b> has been submitted and is awaiting Admin approval.`,
                        confirmButtonColor: 'var(--primary)',
                        confirmButtonText: 'View My SRAs'
                    }).then(() => { window.location.href = res.redirect; });
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                Swal.fire({ icon: 'error', title: 'Submission Failed', text: msg, confirmButtonColor: '#ef4444' });
                $btn.html(origHtml).prop('disabled', false);
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        });
    });
});
</script>
@endpush
