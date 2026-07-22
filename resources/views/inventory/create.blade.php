@extends('layouts.dashboard')

@section('content')
<style>
    .serial-input-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
        background: rgba(136, 19, 55, 0.02);
        border: 1px solid var(--border-color);
        padding: 4px 8px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .serial-input-wrapper:focus-within {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(136, 19, 55, 0.15) !important;
        background: var(--bg-card) !important;
    }
    .btn-bulk-paste:hover {
        background: rgba(136, 19, 55, 0.08) !important;
        color: var(--primary-hover) !important;
    }
</style>
<div class="animate-slide-up" id="newEntryPageContainer">
    <!-- Header Section -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(136, 19, 55, 0.1); color: #881337; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">Stock Entry</span>
                <!-- <span style="color: var(--text-muted); font-size: 0.85rem;"></span> -->
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main); margin: 0;">Add New <span style="color: var(--primary);">Inventory Item</span></h2>
            <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Categorize and record stock balance accurately.</p>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--text-main); text-decoration: none; transition: all 0.3s;" onmouseover="this.style.background='rgba(136, 19, 55, 0.05)'" onmouseout="this.style.background='var(--bg-card)'">
                <i data-lucide="arrow-left" style="width: 18px;"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="glass-card" style="border-radius: 24px; padding: 2rem; display: flex; flex-direction: column; margin-bottom: 2rem;">
        <form id="newEntryForm" novalidate>
            <!-- Batch Information -->
            <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 2rem; margin-bottom: 2rem;">
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin-top: 0; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="info" style="color: var(--primary); width: 22px; height: 22px;"></i>
                    Batch Information
                </h3>

                <div class="form-grid">
                    <div id="ledgeContainer" class="form-group full-width">
                        <label style="display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="layers" style="width: 12px; color: var(--primary);"></i>
                            Category Section (Search & Select) <span style="color: #ef4444; margin-left: 2px;">*</span>
                        </label>
                        <select id="ledgeSelect" class="select2-input" style="width: 100%;" required>
                            <option value=""></option>
                            @foreach($ledgeMap as $code => $name)
                                <option value="{{ $code }}">Category {{ $code }} - {{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="qtyControl" class="form-group" style="display: none; opacity: 0;">
                        <label style="display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="hash" style="width: 12px; color: var(--primary);"></i>
                            Number of different items
                        </label>
                        <input type="number" id="multiQty" min="1" value="1" placeholder="Qty">
                    </div>
                    <div id="supplierControl" class="form-group full-width" style="display: none; opacity: 0; margin-top: 0.75rem;">
                        <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="display: flex; align-items: center; gap: 6px;">
                                    <i data-lucide="truck" style="width: 12px; color: var(--primary);"></i>
                                    Supplier/Donor Name (Search or Type) <span style="color: #ef4444; margin-left: 2px;">*</span>
                                </label>
                                <select id="supplierNameSelect" style="width: 100%;" required>
                                    <option value=""></option>
                                    @foreach($allSuppliers as $supplier)
                                    <option value="{{ $supplier }}">{{ $supplier }}</option>
                                    @endforeach
                                </select>
                                <div id="deliveryPersonGroup" style="margin-top: 0.75rem;">
                                     <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.75rem;">
                                         <div>
                                             <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                                 <i data-lucide="phone" style="width: 14px; color: var(--primary);"></i>
                                                 Contact Number
                                             </label>
                                             <input type="text" id="supplierPhoneInput" class="form-control" placeholder="Enter supplier phone" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                         </div>
                                         <div>
                                             <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                                 <i data-lucide="mail" style="width: 14px; color: var(--primary);"></i>
                                                 Email Address
                                             </label>
                                             <input type="email" id="supplierEmailInput" class="form-control" placeholder="Enter supplier email" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                         </div>
                                     </div>
                                     <div style="margin-bottom: 0.75rem;">
                                         <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                             <i data-lucide="map-pin" style="width: 14px; color: var(--primary);"></i>
                                             Physical Address
                                         </label>
                                         <input type="text" id="supplierAddressInput" class="form-control" placeholder="Enter supplier physical address" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                     </div>
                                     <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                         <div>
                                             <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                                 <i data-lucide="user" style="width: 14px; color: var(--primary);"></i>
                                                 Contact Person Name <span style="color: #ef4444; margin-left: 2px;">*</span>
                                             </label>
                                             <input type="text" id="deliveryPersonInput" class="form-control" placeholder="Enter contact person's name" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                         </div>
                                         <div>
                                             <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                                 <i data-lucide="phone" style="width: 14px; color: var(--primary);"></i>
                                                 Contact Person Number <span style="color: #ef4444; margin-left: 2px;">*</span>
                                             </label>
                                             <input type="text" id="deliveryPersonPhoneInput" maxlength="10" class="form-control" placeholder="Enter phone number" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                         </div>
                                     </div>
                                     <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.75rem;">
                                         <div>
                                             <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                                 <i data-lucide="truck" style="width: 14px; color: var(--primary);"></i>
                                                 Delivery Person Name <span style="color: #ef4444; margin-left: 2px;">*</span>
                                             </label>
                                             <input type="text" id="driverNameInput" class="form-control" placeholder="Enter delivery's name" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                         </div>
                                         <div>
                                             <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                                 <i data-lucide="phone" style="width: 14px; color: var(--primary);"></i>
                                                 Delivery Person Number <span style="color: #ef4444; margin-left: 2px;">*</span>
                                             </label>
                                             <input type="text" id="driverPhoneInput" maxlength="10" class="form-control" placeholder="Enter delivery's number" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                         </div>
                                     </div>
                                 </div>
                                <div style="margin-top: 0.75rem; display: flex; align-items: center; justify-content: space-between; background: var(--bg-main); border: 1px solid var(--border-color); padding: 0.75rem 1rem; border-radius: 12px; transition: all 0.3s ease;">
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
                                            <i data-lucide="gift" style="width: 12px; color: var(--primary);"></i>
                                            Mark as Donor
                                        </span>
                                        <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: none; font-weight: 600; padding-left: 18px;">Check if this is a donation</span>
                                    </div>
                                    <label class="premium-switch" style="position: relative; display: inline-block; width: 44px; height: 24px; margin-bottom: 0; cursor: pointer; user-select: none;">
                                        <input type="checkbox" id="isDonorCheckbox" style="opacity: 0; width: 0; height: 0;">
                                        <span class="slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--border-color); transition: .4s; border-radius: 24px;"></span>
                                    </label>
                                </div>
                            </div>
                            <div id="deliveryStatusGroup">
                                <label style="display: flex; align-items: center; gap: 6px;">
                                    <i data-lucide="activity" style="width: 12px; color: var(--primary);"></i>
                                    Delivery Status <span style="color: #ef4444; margin-left: 2px;">*</span>
                                </label>
                                <select id="supplierStatusSelect" style="width: 100%;" required>
                                    <option value="">Select Status</option>
                                    <option value="Full Delivery" data-icon="check-circle" data-color="#881337">Full Delivery</option>
                                    <option value="Partial Delivery" data-icon="alert-circle" data-color="#881337">Partial Delivery</option>
                                </select>

                                <div id="dateControl" class="form-group" style="display: none; opacity: 0; margin-top: 0.75rem;">
                                    <label style="display: flex; align-items: center; gap: 6px;">
                                        <i data-lucide="calendar" style="width: 12px; color: var(--primary);"></i>
                                        Received Date (Manual) <span style="color: #ef4444; margin-left: 2px;">*</span>
                                    </label>
                                    <input type="date" id="arrivalDate" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit;" required>
                                    <input type="hidden" id="entryDate">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item Details Section -->
            <div id="itemDetails" style="display: none; margin-bottom: 2rem;">
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="package" style="color: var(--primary); width: 22px; height: 22px;"></i>
                    Item Details
                </h3>
                <div id="itemsContainer">
                    <!-- Item Rows will be injected here -->
                </div>
            </div>

            <!-- Form Footer -->
            <div id="formFooter" style="display: none; border-top: 1px solid var(--border-color); padding-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="{{ route('dashboard') }}" class="glass-card" style="padding: 1.15rem 2.5rem; text-decoration: none; font-weight: 700; color: var(--text-muted); display: flex; align-items: center; justify-content: center; border-radius: 14px;">
                    Cancel
                </a>
                <button type="submit" class="btn-primary" style="padding: 1.15rem 3.5rem; border: none; border-radius: 14px; cursor: pointer; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; gap: 0.75rem; box-shadow: 0 10px 20px -5px rgba(136, 19, 55, 0.4);">
                    <i data-lucide="save" style="width: 20px;"></i>
                    Submit for approval
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Premium Switch slider styling */
    .premium-switch input:checked + .slider {
        background-color: var(--primary) !important;
    }
    .premium-switch .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    }
    .premium-switch input:checked + .slider:before {
        transform: translateX(20px);
    }

    /* Light Theme Text Clear and Solid black */
    html:not([data-theme='dark']) #newEntryPageContainer {
        --text-main: #000000 !important;
        --text-muted: #000000 !important;
    }

    html:not([data-theme='dark']) #newEntryPageContainer .glass-card,
    html:not([data-theme='dark']) #newEntryPageContainer label,
    html:not([data-theme='dark']) #newEntryPageContainer h3,
    html:not([data-theme='dark']) #newEntryPageContainer p:not(.unassigned-warning-hint p),
    html:not([data-theme='dark']) #newEntryPageContainer span:not(.existing-indicator):not(.badge):not(.select2-results__option--highlighted span):not(.required-asterisk):not(:last-child),
    html:not([data-theme='dark']) #newEntryPageContainer input:not([type="submit"]):not([type="button"]):not(.row-variance),
    html:not([data-theme='dark']) #newEntryPageContainer select,
    html:not([data-theme='dark']) #newEntryPageContainer .select2-container--default .select2-selection--single .select2-selection__rendered,
    html:not([data-theme='dark']) #newEntryPageContainer .select2-results__option:not(.select2-results__option--highlighted),
    html:not([data-theme='dark']) #newEntryPageContainer .select2-search__field {
        color: #000000 !important;
    }

    /* Placeholders in light theme */
    html:not([data-theme='dark']) #newEntryPageContainer input::placeholder,
    html:not([data-theme='dark']) #newEntryPageContainer textarea::placeholder,
    html:not([data-theme='dark']) #newEntryPageContainer .select2-selection__placeholder {
        color: #475569 !important;
        opacity: 0.8 !important;
        font-weight: 500 !important;
    }

    /* Warning hint styling */
    html:not([data-theme='dark']) #newEntryPageContainer .unassigned-warning-hint,
    html:not([data-theme='dark']) #newEntryPageContainer .unassigned-warning-hint * {
        color: #ef4444 !important;
    }

    /* Required asterisk styling */
    #newEntryPageContainer .required-asterisk,
    #newEntryPageContainer label > span:last-child {
        color: #ef4444 !important;
    }

    /* Premium box shadow for editable inputs and select elements */
    #newEntryPageContainer input:not([type="submit"]):not([type="button"]):not([type="checkbox"]):not([type="hidden"]):not([readonly]),
    #newEntryPageContainer select,
    #newEntryPageContainer .select2-container--default .select2-selection--single {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05) !important;
        transition: all 0.3s ease;
    }
    #newEntryPageContainer input:not([type="submit"]):not([type="button"]):not([type="checkbox"]):not([type="hidden"]):not([readonly]):focus,
    #newEntryPageContainer select:focus,
    #newEntryPageContainer .select2-container--default .select2-selection--single:focus {
        box-shadow: 0 4px 15px rgba(136, 19, 55, 0.15) !important;
        border-color: var(--primary) !important;
    }

    /* Premium styling overrides for select2 fields in unit container */
    .unit-container-sleek .select2-container--default .select2-selection--single,
    .store-location-container-sleek .select2-container--default .select2-selection--single {
        padding-left: 2.5rem !important;
        height: 48px !important;
        border-radius: 12px !important;
        border: 1px solid var(--border-color) !important;
        display: flex !important;
        align-items: center !important;
        background: rgba(136, 19, 55, 0.03) !important;
    }
    .unit-container-sleek .select2-container--default .select2-selection--single .select2-selection__rendered,
    .store-location-container-sleek .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--text-main) !important;
        font-weight: 800 !important;
        font-size: 0.95rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding-left: 0px !important;
    }
    .unit-container-sleek .select2-container--default .select2-selection--single .select2-selection__arrow,
    .store-location-container-sleek .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
    }
</style>

<script id="inventory-data" type="application/json">
    @json($existingItems)
</script>

<script>
jQuery(document).ready(function($) {
    window.originalRollbackPayload = null;
    const categoryOptionsHtml = `@foreach($ledgeMap as $code => $name)<option value="{{ $code }}">Category {{ $code }} - {{ $name }}</option>@endforeach`;

    // Global listener to close and blur select2 when an item is selected
    $(document).on('select2:select', '.item-select-dynamic', function() {
        var $select = $(this);
        setTimeout(function() {
            $select.select2('close');
            var $container = $select.next('.select2-container');
            $container.find('.select2-search__field').blur();
            $container.find('.select2-selection').blur();
        }, 50);
    });

    // Set default dates
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

    $('#entryDate').val(formattedDate);
    $('#arrivalDate').val(new Date().toISOString().split('T')[0]);

    // Check duplicates when Received Date changes
    $('#arrivalDate').on('change', function() {
    });

    function hasRollbackChanges(newPayload, origPayload) {
        if (!origPayload) return true;

        function normalizeName(name) {
            if (!name) return '';
            return name.replace(/\s\[.*\]$/, '').trim().toLowerCase();
        }

        if (newPayload.ledge_category !== origPayload.ledge_category) {
            return true;
        }

        if (normalizeName(newPayload.supplier_name) !== normalizeName(origPayload.supplier_name)) {
            return true;
        }

        if (normalizeName(newPayload.donor_name) !== normalizeName(origPayload.donor_name)) {
            return true;
        }

        if (newPayload.supplier_status !== origPayload.supplier_status) {
            return true;
        }

        if (newPayload.acquisition_type !== origPayload.acquisition_type) {
            return true;
        }

        if (newPayload.arrival_date !== origPayload.arrival_date) {
            return true;
        }

        const newItems = newPayload.items || [];
        const origItems = origPayload.items || [];
        if (newItems.length !== origItems.length) {
            return true;
        }

        for (let i = 0; i < newItems.length; i++) {
            const ni = newItems[i];
            const oi = origItems[i] || {};

            if ((ni.description || '').trim().toUpperCase() !== (oi.description || '').trim().toUpperCase()) {
                return true;
            }
            if ((ni.unit || '').trim().toUpperCase() !== (oi.unit || '').trim().toUpperCase()) {
                return true;
            }

            if (parseFloat(ni.qty || 0) !== parseFloat(oi.qty || 0)) {
                return true;
            }
            if (parseFloat(ni.stock_balance || 0) !== parseFloat(oi.stock_balance || 0)) {
                return true;
            }
            if ((ni.remarks || '').trim().toUpperCase() !== (oi.remarks || '').trim().toUpperCase()) {
                return true;
            }
        }

        return false;
    }

    const ledgeSelect = $('#ledgeSelect');
    const itemDetails = $('#itemDetails');

    function updateSerialInputs($row) {
        const selectedLedge = ($row.find('.row-ledge-category').val() || '').toUpperCase().trim();
        const isSerialCategory = ['C', 'J', 'D'].includes(selectedLedge);
        const $container = $row.find('.serial-inputs-container');
        
        if (!isSerialCategory) {
            $container.empty();
            $row.find('.serial-number-group').hide();
            $row.find('.row-serial-number').val('');
            return;
        }

        const status = $('#supplierStatusSelect').val();
        const qty = parseInt(status === 'Partial Delivery' ? $row.find('.row-stock-balance').val() : $row.find('.row-qty').val()) || 0;

        if (qty <= 0) {
            $container.empty();
            $row.find('.serial-number-group').hide();
            $row.find('.row-serial-number').val('');
            return;
        }

        $row.find('.serial-number-group').show();

        // Check if Tyre in Transport
        const descText = ($row.find('.item-select-dynamic').val() || '').toUpperCase().trim();
        const isTyre = (selectedLedge === 'D' && (descText.includes('TYRE') || descText.includes('TYRES')));

        const $label = $row.find('.serial-number-group label');
        const $bulkBtn = $row.find('.btn-bulk-paste');
        if (isTyre) {
            $label.html(`<i data-lucide="disc" style="width: 12px; color: var(--primary);"></i> Tyre Details (Serial & Rim)`);
            $bulkBtn.hide();
            $container.css('grid-template-columns', 'repeat(auto-fill, minmax(200px, 1fr))');
        } else {
            $label.html(`<i data-lucide="barcode" style="width: 12px; color: var(--primary);"></i> Serial Number(s)`);
            $bulkBtn.show();
            $container.css('grid-template-columns', 'repeat(auto-fill, minmax(130px, 1fr))');
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Get currently typed values from the inputs if any exist
        let currentSns = [];
        let currentRims = [];
        const existingWrappers = $container.find('.serial-input-wrapper');
        if (existingWrappers.length > 0) {
            existingWrappers.each(function() {
                currentSns.push($(this).find('.serial-input-item').val() || '');
                currentRims.push($(this).find('.rim-input-item').val() || '');
            });
        } else {
            // Read from raw joined string
            const rawVal = $row.find('.row-serial-number').val();
            if (rawVal) {
                const parts = rawVal.split(',').map(s => s.trim());
                parts.forEach(part => {
                    const match = part.match(/^(.*?)\s*\(Rim:\s*(\d+)\)$/i);
                    if (match) {
                        currentSns.push(match[1].trim());
                        currentRims.push(match[2].trim());
                    } else if (part.includes('(Rim:')) {
                        const rimMatch = part.match(/\(Rim:\s*(\d+)\)/i);
                        currentRims.push(rimMatch ? rimMatch[1].trim() : '');
                        currentSns.push(part.replace(/\(Rim:\s*\d+\)/i, '').trim());
                    } else {
                        currentSns.push(part.trim());
                        currentRims.push('');
                    }
                });
            }
        }

        $container.empty();
        
        // Create multiple inputs
        for (let i = 0; i < qty; i++) {
            const snVal = currentSns[i] || '';
            const rimVal = currentRims[i] || '';
            const inputHtml = isTyre ? `
                <div class="serial-input-wrapper" style="border: 1px solid var(--border-color); border-radius: 12px; background: var(--bg-card); display: flex; flex-direction: column; padding: 8px; gap: 6px; width: 100%;">
                    <span style="font-size: 0.72rem; font-weight: 800; color: var(--primary); opacity: 0.8; user-select: none;">Tyre #${i+1}</span>
                    <div style="display: flex; gap: 6px; width: 100%;">
                        <input type="text" class="serial-input-item" value="${snVal}" placeholder="Serial Number" style="flex: 2; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); padding: 4px 8px; border-radius: 6px; font-family: inherit; font-size: 0.8rem; font-weight: 600; outline: none; width: 100%;">
                        <input type="number" class="rim-input-item" value="${rimVal}" min="1" placeholder="Rim" style="flex: 1; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); padding: 4px 8px; border-radius: 6px; font-family: inherit; font-size: 0.8rem; font-weight: 600; outline: none; width: 100%;">
                    </div>
                </div>
            ` : `
                <div class="serial-input-wrapper">
                    <span style="font-size: 0.72rem; font-weight: 800; color: var(--primary); opacity: 0.8; user-select: none;">#${i+1}</span>
                    <input type="text" class="serial-input-item" value="${snVal}" placeholder="S/N ${i+1}" style="flex: 1; border: none; background: transparent; color: var(--text-main); padding: 2px 4px; font-family: inherit; font-size: 0.82rem; font-weight: 600; outline: none; width: 100%;">
                </div>
            `;
            $container.append(inputHtml);
        }

        // Update the joined hidden/manifest value
        const values = [];
        if (isTyre) {
            $container.find('.serial-input-wrapper').each(function() {
                const sn = ($(this).find('.serial-input-item').val() || '').trim();
                const rim = ($(this).find('.rim-input-item').val() || '').trim();
                if (sn && rim) {
                    values.push(`${sn} (Rim: ${rim})`);
                } else if (sn) {
                    values.push(sn);
                } else if (rim) {
                    values.push(`(Rim: ${rim})`);
                }
            });
        } else {
            $container.find('.serial-input-item').each(function() {
                const sn = ($(this).val() || '').trim();
                if (sn) values.push(sn);
            });
        }
        $row.find('.row-serial-number').val(values.join(', '));
    }

    $(document).on('input', '.serial-input-item, .rim-input-item', function() {
        const $row = $(this).closest('.item-entry-row');
        const selectedLedge = ($row.find('.row-ledge-category').val() || '').toUpperCase().trim();
        const descText = ($row.find('.item-select-dynamic').val() || '').toUpperCase().trim();
        const isTyre = (selectedLedge === 'D' && (descText.includes('TYRE') || descText.includes('TYRES')));

        const values = [];
        if (isTyre) {
            $row.find('.serial-input-wrapper').each(function() {
                const sn = ($(this).find('.serial-input-item').val() || '').trim();
                const rim = ($(this).find('.rim-input-item').val() || '').trim();
                if (sn && rim) {
                    values.push(`${sn} (Rim: ${rim})`);
                } else if (sn) {
                    values.push(sn);
                } else if (rim) {
                    values.push(`(Rim: ${rim})`);
                }
            });
        } else {
            $row.find('.serial-input-item').each(function() {
                const sn = ($(this).val() || '').trim();
                if (sn) values.push(sn);
            });
        }
        $row.find('.row-serial-number').val(values.join(', '));
    });

    $(document).on('click', '.btn-bulk-paste', function(e) {
        e.preventDefault();
        const $row = $(this).closest('.item-entry-row');
        const $container = $row.find('.serial-inputs-container');
        const numInputs = $container.find('.serial-input-item').length;
        
        if (numInputs === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Input Fields',
                text: 'Please set a valid item quantity first to generate input fields.',
                confirmButtonColor: 'var(--primary)'
            });
            return;
        }

        Swal.fire({
            title: 'Bulk Import Serial Numbers',
            text: `Enter or paste up to ${numInputs} serial numbers (separated by commas or newlines):`,
            input: 'textarea',
            inputPlaceholder: 'Paste here...',
            inputAttributes: {
                autocapitalize: 'off',
                autocomplete: 'off',
                autocorrect: 'off'
            },
            showCancelButton: true,
            confirmButtonColor: 'var(--primary)',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Import',
            preConfirm: (text) => {
                if (!text) {
                    Swal.showValidationMessage('Please enter some serial numbers');
                    return false;
                }
                const sns = text.split(/[\n\r,]+/).map(s => s.trim()).filter(Boolean);
                if (sns.length !== numInputs) {
                    Swal.showValidationMessage(`You must enter exactly ${numInputs} serial numbers (you entered ${sns.length}).`);
                    return false;
                }
                return sns;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const sns = result.value;
                $container.find('.serial-input-item').each((index, input) => {
                    if (index < sns.length) {
                        $(input).val(sns[index]);
                    }
                });
                $container.find('.serial-input-item').first().trigger('input');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Imported!',
                    text: `Successfully imported ${sns.length} serial number(s).`,
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });

    // Database Items from Backend
    let existingDBItems = [];
    try {
        const inventoryDataEl = document.getElementById('inventory-data');
        if (inventoryDataEl) {
            const parsed = JSON.parse(inventoryDataEl.textContent || '[]');
            if (Array.isArray(parsed)) {
                existingDBItems = parsed.filter(Boolean).map(item => {
                    return {
                        ...item,
                        description: (item.description || '').toUpperCase().trim(),
                        unit: (item.unit || '').toUpperCase().trim(),
                        ledge_category: (item.ledge_category || '').toUpperCase().trim()
                    };
                });
            }
        }
    } catch (e) {
        console.error("Error parsing inventory-data:", e);
    }

    // Load admin-defined unit rules for auto-fill
    window._unitRules = {};
    fetch('{{ route("api.unit-rules") }}')
        .then(r => r.json())
        .then(rules => {
            const upperRules = {};
            Object.entries(rules || {}).forEach(([keyword, rule]) => {
                const upperKeyword = keyword.toUpperCase().trim();
                if (typeof rule === 'object' && rule !== null) {
                    upperRules[upperKeyword] = {
                        category: (rule.category || '').toUpperCase().trim(),
                        unit: (rule.unit || '').toUpperCase().trim()
                    };
                } else {
                    upperRules[upperKeyword] = {
                        unit: (rule || '').toUpperCase().trim()
                    };
                }
            });
            window._unitRules = upperRules;
        })
        .catch(() => {});

    // Initialize Select2
    ledgeSelect.select2({
        placeholder: 'Search and Select Category',
        width: '100%'
    });

    // Final Submit Logic
    $('#newEntryForm').on('submit', function(e) {
        e.preventDefault();

        // Client-side validation for required fields
        const missingFields = [];

        // Check Category
        if (!$('#ledgeSelect').val()) {
            missingFields.push("Category Section");
        }

        // Check Supplier/Donor Name
        if (!$('#supplierNameSelect').val()) {
            missingFields.push("Supplier/Donor Name");
        }

        // Check Delivery Person if visible
        if ($('#deliveryPersonGroup').is(':visible')) {
            if (!$('#deliveryPersonInput').val().trim()) {
                missingFields.push("Contact Person Name");
            }
            const phoneVal = ($('#deliveryPersonPhoneInput').val() || '').trim();
            if (!phoneVal) {
                missingFields.push("Contact Person Number");
            } else if (phoneVal.toUpperCase() !== 'N/A' && !/^\d{10}$/.test(phoneVal)) {
                missingFields.push("Contact Person Number (must be a 10-digit number or N/A)");
            }
        }

        // Check Delivery Status (only if not donor)
        const isDonor = $('#isDonorCheckbox').is(':checked');
        if (!isDonor && !$('#supplierStatusSelect').val()) {
            missingFields.push("Delivery Status");
        }

        // Check Received Date
        if (!$('#arrivalDate').val()) {
            missingFields.push("Received Date");
        }

        // Check dynamic item rows
        $('.item-entry-row').each(function(index) {
            const itemIdx = index + 1;
            const desc = ($(this).find('.item-select-dynamic').val() || '').trim();
            const qty = ($(this).find('.row-qty').val() || '').trim();
            const physQty = ($(this).find('.row-stock-balance').val() || '').trim();
            const status = $('#supplierStatusSelect').val();
            const itemCat = ($(this).find('.row-ledge-category').val() || '').trim();
            const unit = ($(this).find('.row-unit').val() || '').trim();

            if (!itemCat) {
                missingFields.push(`Item Type #${itemIdx}: Category`);
            }
            if (!desc) {
                missingFields.push(`Item Type #${itemIdx}: Description`);
            }
            if (!unit) {
                missingFields.push(`Item Type #${itemIdx}: Package Types`);
            }
            const storeLocation = ($(this).find('.row-store-location').val() || '').trim();
            if (!storeLocation) {
                missingFields.push(`Item Type #${itemIdx}: Store Location`);
            }
            if (!qty) {
                missingFields.push(`Item Type #${itemIdx}: Received Qty`);
            }
            if (status === 'Partial Delivery' && !physQty) {
                missingFields.push(`Item Type #${itemIdx}: Physically Received Qty`);
            }
        });

        if (missingFields.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Required Fields Missing',
                html: `<div style="text-align: left; font-size: 0.9rem; font-weight: 600; color: var(--text-main);">
                        Please complete the following required fields before submitting:
                        <ul style="margin-top: 10px; padding-left: 20px; color: #ef4444; line-height: 1.6;">
                            ${missingFields.map(f => `<li>${f}</li>`).join('')}
                        </ul>
                       </div>`,
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        const btn = $(this).find('button[type="submit"]');
        const originalHtml = btn.html();

        // Gather Items & Validate Package Type Rules
        const items = [];
        let validationFailed = false;
        let invalidItemName = '';

        $('.item-entry-row').each(function() {
            const desc = ($(this).find('.item-select-dynamic').val() || '').trim();
            const unit = ($(this).find('.row-unit').val() || '').trim();
            if (unit.indexOf("Confront Admin") !== -1 || unit.indexOf("not assigned") !== -1 || !unit) {
                validationFailed = true;
                invalidItemName = desc || 'Unnamed Item';
            }

            items.push({
                description: desc,
                serial_number: $(this).find('.row-serial-number').val() || null,
                unit: unit,
                store_location: $(this).find('.row-store-location').val() || 'Store A',
                stock_balance: $(this).find('.row-stock-balance').val(),
                qty: $(this).find('.row-qty').val(),
                variance: $(this).find('.row-variance').val() || '0',
                remarks: $(this).find('.row-remarks').val(),
                ledge_category: $(this).find('.row-ledge-category').val()
            });
        });

        if (validationFailed) {
            Swal.fire({
                icon: 'warning',
                title: 'Package Type Missing',
                text: `The item "${invalidItemName}" does not have a valid package type assigned. Please enter or select it before submitting.`,
                confirmButtonColor: '#ef4444'
            });
            return;
        }


        const supplierStatus = isDonor ? 'Donor' : $('#supplierStatusSelect').val();
        const acquisitionType = isDonor ? 'Donor' : 'Supplier';
        const supplierOrDonorName = ($('#supplierNameSelect').val() || '').trim();
        const donorName = isDonor ? supplierOrDonorName : null;
        const supplierName = isDonor ? null : supplierOrDonorName;

        if (window.originalRollbackPayload && window.originalRollbackPayload.items) {
            const currentDescriptions = items.map(i => i.description.trim().toUpperCase());
            window.originalRollbackPayload.items.forEach(origItem => {
                const origDesc = (origItem.description || '').trim().toUpperCase();
                if (!currentDescriptions.includes(origDesc)) {
                    items.push(JSON.parse(JSON.stringify(origItem)));
                }
            });
        }

        const payload = {
            _token: '{{ csrf_token() }}',
            ledge_category: ledgeSelect.val(),
            supplier_name: supplierName || null,
            supplier_status: supplierStatus,
            donor_name: donorName || null,
            acquisition_type: acquisitionType,
            driver_name: $('#deliveryPersonGroup').is(':visible') ? ($('#driverNameInput').val() || '').trim() : null,
            driver_phone: $('#deliveryPersonGroup').is(':visible') ? ($('#driverPhoneInput').val() || '').trim() : null,
            delivery_person: $('#deliveryPersonGroup').is(':visible') ? ($('#deliveryPersonInput').val() || '').trim() : null,
            delivery_phone: $('#deliveryPersonGroup').is(':visible') ? ($('#deliveryPersonPhoneInput').val() || '').trim() : null,
            supplier_phone: $('#deliveryPersonGroup').is(':visible') ? ($('#supplierPhoneInput').val() || '').trim() : null,
            supplier_email: $('#deliveryPersonGroup').is(':visible') ? ($('#supplierEmailInput').val() || '').trim() : null,
            supplier_address: $('#deliveryPersonGroup').is(':visible') ? ($('#supplierAddressInput').val() || '').trim() : null,
            entry_date: $('#entryDate').val(),
            arrival_date: $('#arrivalDate').val(),
            items: items
        };

        const rollbackId = urlParams.get('rollback');
        if (rollbackId) {
            payload.rollback_id = rollbackId;
        }

        if (window.originalRollbackPayload) {
            if (!hasRollbackChanges(payload, window.originalRollbackPayload)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Corrections Made',
                    text: 'You have not made any changes to the flagged fields. Please update the incorrect information before resubmitting.',
                    confirmButtonColor: '#b91c1c'
                });
                return;
            }
        }

        // Loading State
        btn.html('<i class="animate-spin" data-lucide="loader-2"></i> Saving...').prop('disabled', true);
        if (typeof lucide !== 'undefined') lucide.createIcons();

        $.ajax({
            url: '{{ route("inventory.store", [], false) }}',
            method: 'POST',
            data: payload,
            success: function(response) {
                if (response.success) {
                    localStorage.removeItem(getLocalStorageKey());
                    if (response.is_pending) {
                        if (typeof window.playNotificationSound === 'function') {
                            window.playNotificationSound('sent');
                        }
                        Swal.fire({
                            icon: 'info',
                            title: 'REQUEST SUBMITTED',
                            text: 'Your entry is currently pending administrative approval. You will receive a notification once the request is authorized.',
                            confirmButtonColor: '#881337',
                            confirmButtonText: 'Great, Thank you!'
                        }).then(() => {
                            window.location.href = '{{ route("dashboard") }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Inventory records saved successfully!',
                            confirmButtonColor: '#881337',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '{{ route("dashboard") }}';
                        });
                    }
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'A security or protocol error occurred. Please refresh and try again.';
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: errorMsg,
                    confirmButtonColor: '#ef4444'
                });
                btn.html(originalHtml).prop('disabled', false);
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        });
    });

    // Handle Ledge Selection
    ledgeSelect.on('change select2:select', function() {
        try {
            const selectedLedge = ($(this).val() || '').toUpperCase().trim();
            if (selectedLedge) {
                $('#qtyControl').show().animate({
                    opacity: 1
                }, 400);
                $('#supplierControl').show().animate({
                    opacity: 1
                }, 400);
                $('#dateControl').show().animate({
                    opacity: 1
                }, 400);

                itemDetails.slideDown(400);
                $('#formFooter').fadeIn(400);

                // Update any existing row categories to reflect the new master category
                $('.item-entry-row').each(function() {
                    const $row = $(this);
                    const $rowLedgeSelect = $row.find('.row-ledge-category');
                    if ($rowLedgeSelect.val() !== selectedLedge) {
                        $rowLedgeSelect.val(selectedLedge).trigger('change.select2').trigger('change');
                    }
                });

                if ($('#itemsContainer').children().length === 0) {
                    renderItemRows(1);
                } else {
                    updateRowBadges();
                }
            } else {
                $('#qtyControl').hide().css('opacity', 0);
                $('#supplierControl').hide().css('opacity', 0);
                $('#dateControl').hide().css('opacity', 0);
                itemDetails.slideUp(400);
                $('#formFooter').fadeOut(400);
            }
        } catch (err) {
            console.error("Error in ledge selection handler:", err);
        }
    });

    function updateRowBadges() {
        $('#itemsContainer .item-entry-row').each(function(index) {
            const $row = $(this);
            const categoryText = $row.find('.row-ledge-category option:selected').text().trim();
            const suffix = categoryText ? ' - ' + categoryText : '';
            $row.find('.badge-text').text('ITEM TYPE #' + (index + 1) + suffix);
        });
    }

    function renderItemRows(count, append = false) {
        const container = $('#itemsContainer');
        if (!append) container.empty();

        const selectedLedge = ($('#ledgeSelect').val() || '').toUpperCase().trim();

        const standardPackages = ['PIECE(S)', 'PACK', 'BOXES', 'CARTON', 'BAG', 'ROLL', 'SET', 'REAM', 'BOTTLE', 'PACKAGE TYPE'];
        const existingUnits = (existingDBItems || []).map(item => (item && item.unit || '').toUpperCase().trim()).filter(Boolean);
        const allPackages = [...new Set([...standardPackages, ...existingUnits])];
        const packageOptionsHtml = allPackages.map(pkg => `<option value="${pkg}">${pkg}</option>`).join('');

        const categoryText = $('#ledgeSelect option:selected').text().trim();
        const suffix = categoryText ? ' - ' + categoryText : '';

        for (let i = 0; i < count; i++) {
            const currentRows = container.children('.item-entry-row').length;
            const itemIdx = currentRows + 1;
            const rowHtml = `
                <div class="item-entry-row" style="margin-bottom: 2rem; padding: 2rem 1.5rem 1.5rem 1.5rem; border: 1px solid var(--border-color); border-radius: 16px; background: var(--bg-card); position: relative;">
                    <div class="row-badge" style="position: absolute; top: -12px; left: 1rem; background: var(--primary); color: white; padding: 2px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 800;"><span class="badge-text">ITEM TYPE #${itemIdx}${suffix}</span></div>

                    <button type="button" class="remove-row-btn" style="position: absolute; top: 1.25rem; right: 1.25rem; background: rgba(239, 68, 68, 0.1); color: var(--danger); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition);">
                        <i data-lucide="trash-2" style="width: 16px;"></i>
                    </button>

                    <div class="form-grid">
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="layers" style="width: 12px; color: var(--primary);"></i>
                                Category Section (Search & Select) <span style="color: #ef4444; margin-left: 2px;">*</span>
                            </label>
                            <select class="row-ledge-category" style="width: 100%;" required>
                                <option value=""></option>
                                ${categoryOptionsHtml}
                            </select>
                        </div>

                        <div class="form-group">
                             <label style="display: flex; align-items: center; gap: 6px;">
                                 <i data-lucide="search" style="width: 12px; color: var(--primary);"></i>
                                 Item Description (Search & Select) <span style="color: #ef4444; margin-left: 2px;">*</span>
                             </label>
                             <select class="item-select-dynamic" style="width: 100%;" required>
                                 <option value=""></option>
                             </select>
                        </div>

                        <div class="existing-stats full-width" style="display: none; margin-top: 0.85rem; padding: 1rem; background: var(--bg-main); border-radius: 14px; border: 1px dashed var(--border-color); animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);">
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; background: rgba(136, 19, 55, 0.15); color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(136, 19, 55, 0.15);">
                                        <i data-lucide="layers" style="width: 16px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.6rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Previous Balance</div>
                                        <div class="stat-stock-balance" style="font-size: 0.95rem; font-weight: 800; color: var(--text-main);">0</div>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; background: rgba(136, 19, 55, 0.15); color: var(--secondary); border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(136, 19, 55, 0.15);">
                                        <i data-lucide="package" style="width: 16px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.6rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Previously Received</div>
                                        <div class="stat-received-qty" style="font-size: 0.95rem; font-weight: 800; color: var(--text-main);">0</div>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; background: rgba(59, 130, 246, 0.15); color: #881337; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.15);">
                                        <i data-lucide="plus-circle" style="width: 16px;"></i>
                                    </div>
                                    <div>
                                        <div class="lbl-dynamic-stock-balance" style="font-size: 0.6rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Stock Balance</div>
                                        <div class="stat-dynamic-stock-balance" style="font-size: 0.95rem; font-weight: 800; color: #881337;">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="tag" style="width: 12px; color: var(--primary);"></i>
                                Package Types <span style="color: #ef4444; margin-left: 2px;">*</span>
                            </label>
                            <div class="unit-container-sleek" style="position: relative; display: flex; align-items: center; width: 100%;">
                                <select class="row-unit" style="width: 100%;" required>
                                    <option value=""></option>
                                    ${packageOptionsHtml}
                                </select>
                                <div style="position: absolute; left: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary); opacity: 0.8; pointer-events: none; z-index: 5;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><circle cx="7" cy="7" r="1"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="map-pin" style="width: 12px; color: var(--primary);"></i>
                                Store Location <span style="color: #ef4444; margin-left: 2px;">*</span>
                            </label>
                            <div class="store-location-container-sleek" style="position: relative; display: flex; align-items: center; width: 100%;">
                                <select class="row-store-location" style="width: 100%;" required>
                                    <option value="Store A" selected>Store A</option>
                                    <option value="Store B">Store B</option>
                                </select>
                                <div style="position: absolute; left: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary); opacity: 0.8; pointer-events: none; z-index: 5;">
                                    <i data-lucide="building-2" style="width: 16px; height: 16px;"></i>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label class="lbl-received-qty" style="display: flex; align-items: center; gap: 6px;">
                                        <i data-lucide="plus-circle" style="width: 12px; color: var(--primary);"></i>
                                        <span class="lbl-text">Received Qty</span> <span style="color: #ef4444; margin-left: 2px;">*</span>
                                    </label>
                                    <input type="number" class="row-qty" style="border-color: var(--primary-light); width: 100%;" required>
                                </div>
                                <div class="actual-qty-group" style="display: none;">
                                    <label style="display: flex; align-items: center; gap: 6px;">
                                        <i data-lucide="check-circle" style="width: 12px; color: #881337;"></i>
                                        <span style="color: #881337; font-weight: 800;">Physically Received Qty</span> <span style="color: #ef4444; margin-left: 2px;">*</span>
                                    </label>
                                    <input type="number" class="row-stock-balance" value="0" style="border-color: #881337; width: 100%;" required>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" class="row-variance" value="0">

                        <div class="form-group full-width">
                            <div class="remarks-serial-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; width: 100%;">
                                <div class="serial-number-group" style="display: none;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                        <label style="display: flex; align-items: center; gap: 6px; margin-bottom: 0;">
                                            <i data-lucide="barcode" style="width: 12px; color: var(--primary);"></i>
                                            Serial Number(s)
                                        </label>
                                        <button type="button" class="btn-bulk-paste" style="background: none; border: none; color: var(--primary); font-size: 0.72rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 2px 6px; border-radius: 6px; transition: all 0.2s;">
                                            <i data-lucide="clipboard" style="width: 11px; height: 11px;"></i> Bulk Import
                                        </button>
                                    </div>
                                    <input type="hidden" class="row-serial-number">
                                    <div class="serial-inputs-container" style="max-height: 200px; overflow-y: auto; padding: 6px; display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.4rem; border: 1px solid var(--border-color); border-radius: 12px; background: var(--bg-main);"></div>
                                </div>
                                <div class="remarks-group">
                                    <label style="display: flex; align-items: center; gap: 6px;">
                                        <i data-lucide="message-square" style="width: 12px; color: var(--primary);"></i>
                                        Situation Remarks / Specifications
                                    </label>
                                    <input type="text" class="row-remarks" placeholder="Briefly describe the current situation or specification..." style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            const $row = $(rowHtml);
            container.append($row);

            const stockInput = $row.find('.row-stock-balance');
            const qtyInput = $row.find('.row-qty');
            const varianceInput = $row.find('.row-variance');
            const statsPanel = $row.find('.existing-stats');

            // Initialize Select2 for Row Category
            const $rowLedgeSelect = $row.find('.row-ledge-category');
            $rowLedgeSelect.select2({
                placeholder: "Search and Select Category",
                width: '100%'
            });

            // Function to update items dropdown and serials container based on row category
            function updateRowItemsAndSerials() {
                const rowLedge = ($rowLedgeSelect.val() || '').toUpperCase().trim();
                const $select = $row.find('.item-select-dynamic');
                const filtered = (existingDBItems || []).filter(item => item && (item.ledge_category || '').toUpperCase().trim() === rowLedge);

                let optionsHtml = '<option value=""></option>';
                filtered.forEach(item => {
                    optionsHtml += `<option value="${item.description}">${item.description}</option>`;
                });

                $select.html(optionsHtml);
                $select.trigger('change.select2').trigger('change');
                
                // Show/hide serial number inputs based on row category
                const isSerial = ['C', 'J', 'D'].includes(rowLedge);
                if (isSerial) {
                    $row.find('.serial-number-group').show();
                } else {
                    $row.find('.serial-number-group').hide();
                    $row.find('.row-serial-number').val('');
                    $row.find('.serial-inputs-container').empty();
                }
                updateSerialInputs($row);
            }

            $rowLedgeSelect.on('change select2:select', function() {
                updateRowItemsAndSerials();
                updateRowBadges();
            });

            if (selectedLedge) {
                $rowLedgeSelect.val(selectedLedge).trigger('change');
            } else {
                updateRowItemsAndSerials();
            }

            // Initialize Select2 for Item Description
            $row.find('.item-select-dynamic').select2({
                placeholder: "Search, select, or type new item...",
                width: '100%',
                tags: true,
                closeOnSelect: true,
                createTag: function (params) {
                    var term = $.trim(params.term).toUpperCase();
                    if (term === '') {
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                }
            });

            // Close dropdown immediately after an item is selected
            $row.find('.item-select-dynamic').on('select2:select', function() {
                var $select = $(this);
                setTimeout(function() {
                    $select.select2('close');
                    var $container = $select.next('.select2-container');
                    $container.find('.select2-search__field').blur();
                    $container.find('.select2-selection').blur();
                }, 50);
            });

            // Initialize Select2 for Row Unit (Package Type)
            const $unitInput = $row.find('.row-unit');
            $unitInput.select2({
                placeholder: "Select or type package...",
                width: '100%',
                tags: true,
                closeOnSelect: true,
                dropdownParent: $row,
                createTag: function (params) {
                    var term = $.trim(params.term).toUpperCase();
                    if (term === '') {
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                }
            });

            // Initialize Select2 for Store Location
            const $storeLocationInput = $row.find('.row-store-location');
            $storeLocationInput.select2({
                placeholder: "Select Store Location",
                width: '100%',
                minimumResultsForSearch: Infinity,
                dropdownParent: $row
            });

            // Handle Item Selection to show previous data explicitly
            $row.on('change', '.item-select-dynamic', function() {
                var $select = $(this);
                setTimeout(function() {
                    $select.select2('close');
                }, 50);
                const selectedDesc = ($select.val() || '').trim().toUpperCase();
                const prevData = (existingDBItems || []).find(item => item && item.description === selectedDesc);

                if (!selectedDesc) {
                    $unitInput.val(null).trigger('change');
                } else if (window._unitRules) {
                    const descUpper = selectedDesc.toUpperCase().trim();
                    const currentCat = ($row.find('.row-ledge-category').val() || '').toUpperCase().trim();

                    const matchedUnit = Object.entries(window._unitRules).find(([kw, rule]) => {
                        const upperKw = kw.toUpperCase().trim();
                        if (!descUpper.includes(upperKw)) return false;

                        if (typeof rule === 'object' && rule !== null) {
                            const ruleCat = (rule.category || '').toUpperCase().trim();
                            return ruleCat === currentCat;
                        }
                        return true;
                    });

                    if (matchedUnit) {
                        const ruleValue = matchedUnit[1];
                        const unitVal = typeof ruleValue === 'object' ? ruleValue.unit : ruleValue;
                        
                        if ($unitInput.find('option[value="' + unitVal + '"]').length === 0) {
                            $unitInput.append(new Option(unitVal, unitVal, true, true));
                        }
                        $unitInput.val(unitVal).trigger('change');
                    } else if (prevData && prevData.unit) {
                        const unitVal = prevData.unit;
                        
                        if ($unitInput.find('option[value="' + unitVal + '"]').length === 0) {
                            $unitInput.append(new Option(unitVal, unitVal, true, true));
                        }
                        $unitInput.val(unitVal).trigger('change');
                    } else {
                        // Brand new item: clear choices to let officer pick/type
                        $unitInput.val(null).trigger('change');
                    }
                } else if (prevData && prevData.unit) {
                    const unitVal = prevData.unit;
                    
                    if ($unitInput.find('option[value="' + unitVal + '"]').length === 0) {
                        $unitInput.append(new Option(unitVal, unitVal, true, true));
                    }
                    $unitInput.val(unitVal).trigger('change');
                } else {
                    $unitInput.val(null).trigger('change');
                }

                const updateStatsPanel = () => {
                    const unitLabel = ($unitInput.val() || '').toUpperCase();
                    const isUnitValid = unitLabel && !unitLabel.includes('CONFRONT ADMIN');
                    const finalUnit = isUnitValid ? unitLabel : 'PACKAGE TYPES';

                    if (prevData) {
                        const totalStock = parseFloat(prevData.stock_balance) || 0;
                        const lastQty = parseFloat(prevData.qty) || 0;
                        const prevBalance = Math.max(0, totalStock - lastQty);

                        $row.find('.stat-stock-balance').text(prevBalance.toLocaleString() + ' ' + finalUnit);
                        $row.find('.stat-received-qty').text(lastQty.toLocaleString() + ' ' + finalUnit);
                        $row.find('.stat-dynamic-stock-balance').text(totalStock.toLocaleString());
                        statsPanel.slideDown(300);

                        if (!$row.find('.existing-indicator').length) {
                            $row.find('.row-badge').append(' <span class="existing-indicator" style="font-size: 0.6rem; opacity: 0.8; background: rgba(255,255,255,0.2); padding: 1px 6px; border-radius: 4px; margin-left: 4px;">(Restocking)</span>');
                        }
                    } else if (selectedDesc) {
                        $row.find('.stat-stock-balance').text('0 ' + finalUnit);
                        $row.find('.stat-received-qty').text('0 ' + finalUnit);
                        $row.find('.stat-dynamic-stock-balance').text('0');
                        statsPanel.slideDown(300);
                        $row.find('.existing-indicator').remove();
                    } else {
                        statsPanel.slideUp(300);
                    }
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                };

                if (selectedDesc) {
                    stockInput.val(qtyInput.val() || 0);
                    qtyInput.removeAttr('placeholder');
                    varianceInput.attr('placeholder', '0');
                    updateStatsPanel();
                    updateSerialInputs($row);
                } else {
                    statsPanel.slideUp(300);
                    $row.find('.duplicate-warning').remove();
                    $row.removeClass('has-duplicate');
                }
                if (typeof updateSubmitButtonState === 'function') {
                    updateSubmitButtonState();
                }
            });

            // Auto-Calculation Logic
            $row.on('input', '.row-qty, .row-stock-balance', function() {
                const status = $('#supplierStatusSelect').val();
                const qtyVal = parseFloat(qtyInput.val()) || 0;

                if (status !== 'Partial Delivery') {
                    stockInput.val(qtyVal);
                }

                updateSerialInputs($row);

                const stockVal = parseFloat(stockInput.val()) || 0;
                const result = stockVal - qtyVal;
                varianceInput.val(result);

                if (result > 0) {
                    varianceInput.css('color', '#881337');
                } else if (result < 0) {
                    varianceInput.css('color', '#ef4444');
                } else {
                    varianceInput.css('color', '#881337');
                }
            });

            // Apply initial labels based on current status
            const initStatus = $('#supplierStatusSelect').val();
            if (initStatus === 'Partial Delivery') {
                $row.find('.lbl-received-qty .lbl-text').text('Expected / Invoice Qty');
                $row.find('.row-qty').css({'border-color': '#881337', 'background': 'var(--bg-card)'}).prop('readonly', false);
                $row.find('.actual-qty-group').show();
            } else {
                $row.find('.lbl-received-qty .lbl-text').text('Received Qty');
                $row.find('.row-qty').css({'border-color': 'var(--primary-light)', 'background': 'var(--bg-main)'}).prop('readonly', false);
                $row.find('.actual-qty-group').hide();
            }
            updateSerialInputs($row);
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
        updateSubmitButtonState();
    }

    function updateSubmitButtonState() {
        let disabled = false;
        let duplicateFound = false;

        $('#itemsContainer .item-entry-row').each(function() {
            const unitVal = ($(this).find('.row-unit').val() || '').trim();

            if (unitVal.indexOf("Package type not assigned") !== -1 ||
                unitVal.indexOf("Confront Admin") !== -1 ||
                !unitVal) {
                disabled = true;
            }

            if ($(this).hasClass('has-duplicate')) {
                duplicateFound = true;
            }
        });

        const submitBtn = $('#newEntryForm button[type="submit"]');
        if (disabled || duplicateFound) {
            submitBtn.prop('disabled', true);
            submitBtn.css({
                'opacity': '0.5',
                'cursor': 'not-allowed',
                'pointer-events': 'none'
            });
            if (duplicateFound) {
                submitBtn.attr('title', 'Please resolve duplicate entries before submitting.');
            } else {
                submitBtn.attr('title', 'Please assign a package type before submitting.');
            }
        } else {
            submitBtn.prop('disabled', false);
            submitBtn.css({
                'opacity': '1',
                'cursor': 'pointer',
                'pointer-events': 'auto'
            });
            submitBtn.removeAttr('title');
        }
    }


    $(document).on('change', '.row-unit', function() {
        updateSubmitButtonState();
    });

    // Initialize Supplier Status Select2
    function formatStatus(opt) {
        if (!opt.id) return opt.text;
        const icon = $(opt.element).data('icon');
        const color = $(opt.element).data('color');
        return $(`
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: ${color}; box-shadow: 0 0 8px ${color}80;"></div>
                <span style="font-weight: 600; font-size: 0.9rem;">${opt.text}</span>
            </div>
        `);
    }

    $('#supplierStatusSelect').select2({
        placeholder: "Select Status",
        width: '100%',
        minimumResultsForSearch: -1,
        templateResult: formatStatus,
        templateSelection: formatStatus
    });

    const suppliersList = @json($allSuppliers ?? []);
    const donorsList = @json($allDonors ?? []);

    // Toggle Donor Acquisition state
    $('#isDonorCheckbox').on('change', function() {
        const isDonor = $(this).is(':checked');
        const list = isDonor ? donorsList : suppliersList;

        const select = $('#supplierNameSelect');
        const currentVal = select.val();

        select.empty();
        select.append('<option value=""></option>');
        list.forEach(name => {
            select.append(new Option(name, name));
        });

        if (currentVal && !list.includes(currentVal)) {
            select.append(new Option(currentVal, currentVal));
            select.val(currentVal).trigger('change');
        } else if (list.includes(currentVal)) {
            select.val(currentVal).trigger('change');
        } else {
            select.val(null).trigger('change');
        }

        $('#supplierStatusSelect').trigger('change');
    });

    // Toggle Delivery Status and Update Labels
    $('#supplierStatusSelect').on('change', function() {
        const status = $(this).val();

        if (status === 'Partial Delivery') {
            $('.item-entry-row').each(function() {
                $(this).find('.lbl-received-qty .lbl-text').text('Expected / Invoice Qty');
                $(this).find('.row-qty').css({'border-color': '#881337', 'background': 'var(--bg-card)'}).prop('readonly', false);
                $(this).find('.actual-qty-group').slideDown(300);
                updateSerialInputs($(this));
            });
        } else {
            $('.item-entry-row').each(function() {
                $(this).find('.lbl-received-qty .lbl-text').text('Received Qty');
                $(this).find('.row-qty').css({'border-color': 'var(--primary-light)', 'background': 'var(--bg-main)'}).prop('readonly', false);
                $(this).find('.actual-qty-group').slideUp(300);

                const qtyVal = parseFloat($(this).find('.row-qty').val()) || 0;
                $(this).find('.row-stock-balance').val(qtyVal);
                $(this).find('.row-variance').val(0);
                updateSerialInputs($(this));
            });
        }
    });

    // Initialize Supplier Name Select2
    $('#supplierNameSelect').select2({
        placeholder: "Search or type new supplier/donor...",
        width: '100%',
        tags: true
    });

    // Dynamic Supplier Details display
    const suppliersRegistry = @json(\App\Models\Setting::get('suppliers_registry', []));
    $('#supplierNameSelect').on('change', function() {
        const name = $(this).val();
        const deliveryInput = $('#deliveryPersonInput');
        const deliveryPhoneInput = $('#deliveryPersonPhoneInput');
        const supplierPhoneInput = $('#supplierPhoneInput');
        const supplierEmailInput = $('#supplierEmailInput');
        const supplierAddressInput = $('#supplierAddressInput');

        let details = null;
        if (name) {
            const searchKey = name.toLowerCase().trim();
            for (const key in suppliersRegistry) {
                if (key.toLowerCase().trim() === searchKey) {
                    details = suppliersRegistry[key];
                    break;
                }
            }
        }

        if (name && details) {
            deliveryInput.val(details.contact_person || details.delivery_person || '');
            deliveryPhoneInput.val(details.contact_phone || details.delivery_phone || '');
            supplierPhoneInput.val(details.phone || '');
            supplierEmailInput.val(details.email || '');
            supplierAddressInput.val(details.address || '');
        } else {
            deliveryInput.val('');
            deliveryPhoneInput.val('');
            supplierPhoneInput.val('');
            supplierEmailInput.val('');
            supplierAddressInput.val('');
        }
    });

    const multiQtyInput = $('#multiQty');

    // Automatic Generation on Input
    multiQtyInput.on('input', function() {
        const qty = parseInt($(this).val()) || 0;
        if (qty > 0 && qty <= 50) {
            renderItemRows(qty);
        } else if (qty === 0) {
            $('#itemsContainer').empty();
        }
    });

    // Helper to determine active localStorage key
    function getLocalStorageKey() {
        const urlParams = new URLSearchParams(window.location.search);
        const rollbackId = urlParams.get('rollback');
        const continueBatchId = urlParams.get('continue_batch');
        if (rollbackId) {
            return `inventory_create_draft_rollback_${rollbackId}`;
        } else if (continueBatchId) {
            return `inventory_create_draft_continue_${continueBatchId}`;
        }
        return 'inventory_create_draft';
    }

    // Save Form State to LocalStorage
    window.isRestoringDraft = false;
    function saveFormState() {
        if (window.isRestoringDraft) return;

        const items = [];
        $('.item-entry-row').each(function() {
            const desc = $(this).find('.item-select-dynamic').val() || '';
            const unit = $(this).find('.row-unit').val() || '';
            const ledgeCategory = $(this).find('.row-ledge-category').val() || '';
            const qty = $(this).find('.row-qty').val() || '';
            const stockBalance = $(this).find('.row-stock-balance').val() || '';
            const remarks = $(this).find('.row-remarks').val() || '';
            const serialNumber = $(this).find('.row-serial-number').val() || '';

            // Also capture typed values in inputs directly in case not yet joined
            const serials = [];
            const rims = [];
            $(this).find('.serial-input-wrapper').each(function() {
                serials.push($(this).find('.serial-input-item').val() || '');
                rims.push($(this).find('.rim-input-item').val() || '');
            });

            items.push({
                description: desc,
                unit: unit,
                ledge_category: ledgeCategory,
                qty: qty,
                stock_balance: stockBalance,
                remarks: remarks,
                serial_number: serialNumber,
                serials: serials,
                rims: rims
            });
        });

        const state = {
            ledge_category: $('#ledgeSelect').val(),
            multiQty: $('#multiQty').val(),
            supplier_name: $('#supplierNameSelect').val(),
            supplier_phone: $('#supplierPhoneInput').val(),
            supplier_email: $('#supplierEmailInput').val(),
            supplier_address: $('#supplierAddressInput').val(),
            delivery_person: $('#deliveryPersonInput').val(),
            delivery_phone: $('#deliveryPersonPhoneInput').val(),
            driver_name: $('#driverNameInput').val(),
            driver_phone: $('#driverPhoneInput').val(),
            is_donor: $('#isDonorCheckbox').is(':checked'),
            supplier_status: $('#supplierStatusSelect').val(),
            arrival_date: $('#arrivalDate').val(),
            items: items
        };

        localStorage.setItem(getLocalStorageKey(), JSON.stringify(state));
    }

    // Populate a dynamic item row
    function populateRow($row, item) {
        if (!item) return;

        if (item.ledge_category) {
            $row.find('.row-ledge-category').val(item.ledge_category).trigger('change.select2').trigger('change');
        }

        if (item.description) {
            const $descSelect = $row.find('.item-select-dynamic');
            if ($descSelect.find(`option[value="${item.description}"]`).length === 0) {
                $descSelect.append(new Option(item.description, item.description, true, true));
            }
            $descSelect.val(item.description).trigger('change.select2').trigger('change');
        }

        if (item.unit) {
            const $unitSelect = $row.find('.row-unit');
            if ($unitSelect.find(`option[value="${item.unit}"]`).length === 0) {
                $unitSelect.append(new Option(item.unit, item.unit, true, true));
            }
            $unitSelect.val(item.unit).trigger('change.select2').trigger('change');
        }

        if (item.qty) {
            $row.find('.row-qty').val(item.qty).trigger('input');
        }

        if (item.stock_balance) {
            $row.find('.row-stock-balance').val(item.stock_balance).trigger('input');
        }

        if (item.remarks) {
            $row.find('.row-remarks').val(item.remarks);
        }

        if (item.serial_number) {
            $row.find('.row-serial-number').val(item.serial_number);
            updateSerialInputs($row);
            
            // Restore individual serial/rim input boxes if populated
            if (item.serials && item.serials.length > 0) {
                $row.find('.serial-inputs-container .serial-input-wrapper').each(function(idx) {
                    $(this).find('.serial-input-item').val(item.serials[idx] || '');
                    $(this).find('.rim-input-item').val(item.rims[idx] || '');
                });
            }
        }
    }

    // Restore whole form state
    function restoreFormState(state) {
        if (!state) return;
        window.isRestoringDraft = true;

        if (state.ledge_category) {
            $('#ledgeSelect').val(state.ledge_category).trigger('change.select2').trigger('change');
        }
        if (state.multiQty) {
            $('#multiQty').val(state.multiQty);
        }
        if (state.supplier_name) {
            const cleanName = state.supplier_name.replace(/\s\[.*\]$/, '');
            if ($('#supplierNameSelect option[value="' + cleanName + '"]').length === 0) {
                $('#supplierNameSelect').append(new Option(cleanName, cleanName, true, true));
            }
            $('#supplierNameSelect').val(cleanName).trigger('change.select2').trigger('change');
        }
        if (state.supplier_phone) $('#supplierPhoneInput').val(state.supplier_phone);
        if (state.supplier_email) $('#supplierEmailInput').val(state.supplier_email);
        if (state.supplier_address) $('#supplierAddressInput').val(state.supplier_address);
        if (state.delivery_person) $('#deliveryPersonInput').val(state.delivery_person);
        if (state.delivery_phone) $('#deliveryPersonPhoneInput').val(state.delivery_phone);
        if (state.driver_name) $('#driverNameInput').val(state.driver_name);
        if (state.driver_phone) $('#driverPhoneInput').val(state.driver_phone);
        
        if (state.is_donor) {
            $('#isDonorCheckbox').prop('checked', true).trigger('change');
        } else {
            $('#isDonorCheckbox').prop('checked', false).trigger('change');
        }

        if (state.supplier_status) {
            $('#supplierStatusSelect').val(state.supplier_status).trigger('change.select2').trigger('change');
        }
        if (state.arrival_date) $('#arrivalDate').val(state.arrival_date);

        if (state.items && state.items.length > 0) {
            renderItemRows(state.items.length);
            $('#itemsContainer .item-entry-row').each(function(index) {
                populateRow($(this), state.items[index]);
            });
        }

        window.isRestoringDraft = false;
    }

    // Attach listener to form elements to save draft automatically
    $(document).on('change input select2:select select2:unselect', '#newEntryForm input, #newEntryForm select, #newEntryForm textarea', function() {
        saveFormState();
    });

    // Remove Row Logic
    $(document).on('click', '.remove-row-btn', function() {
        $(this).closest('.item-entry-row').fadeOut(300, function() {
            $(this).remove();
            const currentCount = $('#itemsContainer').children('.item-entry-row').length;
            multiQtyInput.val(currentCount);
            const categoryText = $('#ledgeSelect option:selected').text().trim();
            const suffix = categoryText ? ' - ' + categoryText : '';
            $('#itemsContainer .item-entry-row').each(function(index) {
                $(this).find('.badge-text').text('ITEM TYPE #' + (index + 1) + suffix);
            });
            if (typeof updateSubmitButtonState === 'function') {
                updateSubmitButtonState();
            }
            saveFormState();
        });
    });

    // Check for continue_batch parameter
    const urlParams = new URLSearchParams(window.location.search);
    const continueBatchId = urlParams.get('continue_batch');

    if (continueBatchId) {
        $.ajax({
            url: `/received-items/${continueBatchId}?json=1`,
            method: 'GET',
            success: function(response) {
                const batch = response.batch;
                if (batch) {
                    // Check if saved draft exists for this continueBatchId
                    const rawDraft = localStorage.getItem(`inventory_create_draft_continue_${continueBatchId}`);
                    let draftState = null;
                    if (rawDraft) {
                        try { draftState = JSON.parse(rawDraft); } catch(e) {}
                    }

                    if (draftState) {
                        restoreFormState(draftState);
                    } else {
                        // Set Date
                        const now = new Date();
                        const year = now.getFullYear();
                        const month = String(now.getMonth() + 1).padStart(2, '0');
                        const day = String(now.getDate()).padStart(2, '0');
                        const hours = String(now.getHours()).padStart(2, '0');
                        const minutes = String(now.getMinutes()).padStart(2, '0');
                        const seconds = String(now.getSeconds()).padStart(2, '0');
                        const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                        $('#entryDate').val(formattedDate);
                        $('#arrivalDate').val(batch.arrival_date || new Date().toISOString().split('T')[0]);

                        // Set Ledge
                        ledgeSelect.val(batch.ledge_category).trigger('change');

                        const isDonor = batch.acquisition_type === 'Donor';
                        const rawSupplier = isDonor ? batch.donor_name : batch.supplier_name;
                        const cleanSupplier = rawSupplier ? rawSupplier.replace(/\s\[.*\]$/, '') : '';

                        setTimeout(() => {
                            $('#isDonorCheckbox').prop('checked', isDonor).trigger('change');
                            $('#supplierNameSelect').val(cleanSupplier).trigger('change');
                            if (batch.delivery_person) {
                                $('#deliveryPersonInput').val(batch.delivery_person);
                            }
                            if (batch.delivery_phone) {
                                $('#deliveryPersonPhoneInput').val(batch.delivery_phone);
                            }
                            if (!isDonor) {
                                $('#supplierStatusSelect').val(batch.supplier_status || 'Full Delivery').trigger('change');
                            }

                            // Render Rows for items
                            if (batch.items && batch.items.length > 0) {
                                $('#multiQty').val(batch.items.length);
                                renderItemRows(batch.items.length);

                                // Fill row data
                                $('.item-entry-row').each(function(index) {
                                    const item = batch.items[index];
                                    const $row = $(this);
                                    const itemCat = item.ledge_category || batch.ledge_category;
                                    $row.find('.row-ledge-category').val(itemCat).trigger('change.select2');
                                    $row.find('.item-select-dynamic').val(item.description).trigger('change');
                                    $row.find('.row-qty').focus();
                                });
                            }
                        }, 500);
                    }

                    // Clean URL
                    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                    window.history.replaceState({}, '', cleanUrl);
                }
            }
        });
    }

    // Rollback Pre-Fill
    const rollbackId = urlParams.get('rollback');

    if (rollbackId) {
        fetch(`/api/sra-rollback/${rollbackId}`)
            .then(res => {
                if (!res.ok) throw new Error('Could not load rollback data');
                return res.json();
            })
            .then(data => {
                const payload       = data.payload || {};
                const flaggedFields = data.flagged_fields || {};
                const flaggedItems  = data.flagged_items || [];
                const generalNote   = data.general_note || '';

                if (!payload || Object.keys(payload).length === 0) return;

                // Keep the full original payload intact in window.originalRollbackPayload
                window.originalRollbackPayload = JSON.parse(JSON.stringify(payload));

                // Filter items to show only the ones selected by the Head of Stores for rollback
                let renderItems = payload.items || [];
                if (flaggedItems && flaggedItems.length > 0) {
                    renderItems = renderItems.filter(itm => flaggedItems.includes(itm.description));
                }

                // Add a red alert banner above the form controls
                const bannerHtml = `
                    <div id="rollback-alert-banner" style="margin-bottom: 2rem; border-radius: 14px; overflow: hidden; border: 2px solid #fca5a5; box-shadow: 0 4px 16px rgba(239,68,68,0.1);">
                        <div style="background: linear-gradient(135deg, #ef4444, #dc2626); padding: 0.85rem 1.1rem; display: flex; align-items: center; gap: 10px;">
                            <svg style="width: 18px; height: 18px; color: white; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span style="font-size: 0.82rem; font-weight: 900; color: white; text-transform: uppercase; letter-spacing: 0.06em;">Correction Required — Admin Rollback</span>
                        </div>
                        <div style="background: #fff5f5; padding: 0.75rem 1.1rem; font-size: 0.82rem; color: #7f1d1d; line-height: 1.6;">
                            Fields highlighted in <b style="color:#ef4444;">red</b> need to be corrected per the Admin's instructions.
                            ${generalNote ? `<div style="margin-top: 6px; padding: 8px 12px; background: white; border-radius: 8px; border: 1px solid #fecaca;"><b>Admin Note:</b> ${generalNote}</div>` : ''}
                        </div>
                    </div>`;

                const controlsArea = document.querySelector('#newEntryForm');
                if (controlsArea && !document.getElementById('rollback-alert-banner')) {
                    controlsArea.insertAdjacentHTML('afterbegin', bannerHtml);
                }

                // Check if a saved draft exists for this rollback
                const rawDraft = localStorage.getItem(`inventory_create_draft_rollback_${rollbackId}`);
                let draftState = null;
                if (rawDraft) {
                    try { draftState = JSON.parse(rawDraft); } catch(e) {}
                }

                if (draftState) {
                    restoreFormState(draftState);
                    setTimeout(() => {
                        _applyRollbackHighlights(flaggedFields, flaggedItems);
                    }, 500);
                } else {
                    // Set timestamp
                    const now = new Date();
                    const formattedDate = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}-${String(now.getDate()).padStart(2,'0')} ${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}:${String(now.getSeconds()).padStart(2,'0')}`;
                    $('#entryDate').val(formattedDate);

                    if (payload.arrival_date) {
                        $('#arrivalDate').val(payload.arrival_date);
                    }

                    if (payload.ledge_category) {
                        ledgeSelect.val(payload.ledge_category).trigger('change');
                    }

                    setTimeout(() => {
                        const isDonor    = payload.acquisition_type === 'Donor';
                        const supplierVal = isDonor ? (payload.donor_name || '') : (payload.supplier_name || '');
                        const cleanVal   = supplierVal.replace(/\s\[.*\]$/, '');

                        $('#isDonorCheckbox').prop('checked', isDonor).trigger('change');

                        if (cleanVal && $('#supplierNameSelect option[value="' + cleanVal + '"]').length === 0) {
                            $('#supplierNameSelect').append(new Option(cleanVal, cleanVal, true, true));
                        }
                        $('#supplierNameSelect').val(cleanVal).trigger('change');
                        if (payload.delivery_person) {
                            $('#deliveryPersonInput').val(payload.delivery_person);
                        }
                        if (payload.delivery_phone) {
                            $('#deliveryPersonPhoneInput').val(payload.delivery_phone);
                        }

                        if (!isDonor && payload.supplier_status) {
                            $('#supplierStatusSelect').val(payload.supplier_status).trigger('change');
                        }

                        if (renderItems.length > 0) {
                            $('#multiQty').val(renderItems.length);
                            renderItemRows(renderItems.length);

                            setTimeout(() => {
                                $('.item-entry-row').each(function(idx) {
                                    const itm  = renderItems[idx];
                                    const $row = $(this);
                                    if (!itm) return;

                                    const itemCat = itm.ledge_category || payload.ledge_category;
                                    $row.find('.row-ledge-category').val(itemCat).trigger('change.select2');

                                    const descSel = $row.find('.item-select-dynamic');
                                    if (descSel.find('option[value="' + itm.description + '"]').length === 0) {
                                        descSel.append(new Option(itm.description, itm.description, true, true));
                                    }
                                    descSel.val(itm.description).trigger('change');
                                    $row.find('.row-qty').val(itm.qty || '');
                                    if (itm.unit) {
                                        const uSel = $row.find('.row-unit');
                                        if (uSel.find('option[value="' + itm.unit + '"]').length === 0) {
                                            uSel.append(new Option(itm.unit, itm.unit, true, true));
                                        }
                                        uSel.val(itm.unit).trigger('change');
                                    }
                                    if (itm.location) {
                                        const lSel = $row.find('.row-location');
                                        if (lSel.find('option[value="' + itm.location + '"]').length === 0) {
                                            lSel.append(new Option(itm.location, itm.location, true, true));
                                        }
                                        lSel.val(itm.location).trigger('change');
                                    }
                                    $row.find('.row-stock-balance').val(itm.stock_balance || '');
                                    $row.find('.row-variance').val(itm.variance || 0);
                                    $row.find('.row-remarks').val(itm.remarks || '');
                                    $row.find('.row-serial-number').val(itm.serial_number || '');
                                    updateSerialInputs($row);
                                });

                                _applyRollbackHighlights(flaggedFields, flaggedItems);
                            }, 300);
                        } else {
                            setTimeout(() => _applyRollbackHighlights(flaggedFields, flaggedItems), 300);
                        }

                    }, 500);
                }

                window.history.replaceState({}, '', window.location.protocol + '//' + window.location.host + window.location.pathname);
            })
            .catch(err => {
                console.error("Rollback fetch failed:", err);
            });
    }

    function _applyRollbackHighlights(flaggedFields, flaggedItems) {
        if (!flaggedFields || Object.keys(flaggedFields).length === 0) return;

        const RED_BORDER = '2px solid #b91c1c';
        const RED_SHADOW = '0 0 0 4px rgba(185,28,28,0.15)';

        // 1. Highlight global fields (supplier details, dates, etc.)
        const GLOBAL_FIELD_MAP = {
            supplier_name:    () => [$('#supplierNameSelect').parent().find('.select2-selection').length ? $('#supplierNameSelect').parent().find('.select2-selection') : $('#supplierNameSelect').closest('.select2-container')],
            supplier_status:  () => [$('#supplierStatusSelect').parent().find('.select2-selection').length ? $('#supplierStatusSelect').parent().find('.select2-selection') : $('#supplierStatusSelect')],
            arrival_date:     () => [$('#arrivalDate')],
            entry_date:       () => [$('#arrivalDate')],
            ledge_category:   () => [$('#ledgeSelect').parent().find('.select2-selection').length ? $('#ledgeSelect').parent().find('.select2-selection') : $('#ledgeSelect').closest('.select2-container')],
            acquisition_type: () => [$('#isDonorCheckbox').closest('label')],
        };

        Object.keys(GLOBAL_FIELD_MAP).forEach(key => {
            if (flaggedFields[key]) {
                const note   = flaggedFields[key];
                const getEls = GLOBAL_FIELD_MAP[key];
                if (!getEls) return;

                const els = getEls();
                els.forEach(($el, i) => {
                    if (!$el || !$el.length) return;

                    $el.css({ 'border': RED_BORDER, 'box-shadow': RED_SHADOW, 'border-radius': '8px', 'transition': 'all 0.3s' });

                    const hintClass = 'rb-hint-' + key;
                    if ($el.parent().find('.' + hintClass).length === 0) {
                        const hintHtml = `<div class="${hintClass}" style="margin-top:5px; font-size:0.76rem; font-weight:700; color:#ef4444; display:flex; align-items:flex-start; gap:5px; line-height:1.4;">
                            <svg style="width:12px;height:12px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                            <span><b>Admin:</b> ${$('<div>').text(note).html()}</span>
                        </div>`;
                        $el.parent().after(hintHtml);
                    }
                });
            }
        });

        // 2. Highlight row-specific fields (only for rows whose description matches flaggedItems, if flaggedItems is not empty)
        const hasFlaggedItems = flaggedItems && flaggedItems.length > 0;

        $('.item-entry-row').each(function() {
            const $row = $(this);
            const descVal = ($row.find('.item-select-dynamic').val() || '').trim();

            // If flaggedItems list exists, only apply row highlights if this item's description matches
            if (hasFlaggedItems && !flaggedItems.includes(descVal)) {
                return;
            }

            const rowFieldsMap = {
                item_description: () => $row.find('.item-select-dynamic').parent().find('.select2-selection').length ? $row.find('.item-select-dynamic').parent().find('.select2-selection') : $row.find('.item-select-dynamic').closest('.select2-container'),
                item_qty:         () => $row.find('.row-qty'),
                item_unit:        () => $row.find('.row-unit').parent().find('.select2-selection').length ? $row.find('.row-unit').parent().find('.select2-selection') : $row.find('.row-unit').closest('.select2-container'),
                item_remarks:     () => $row.find('.row-remarks'),
                item_serial_number: () => $row.find('.serial-inputs-container'),
            };

            Object.keys(rowFieldsMap).forEach(key => {
                if (flaggedFields[key]) {
                    const note = flaggedFields[key];
                    const $el = rowFieldsMap[key]();
                    if ($el && $el.length) {
                        $el.css({ 'border': RED_BORDER, 'box-shadow': RED_SHADOW, 'border-radius': '8px', 'transition': 'all 0.3s' });

                        const hintClass = 'rb-hint-' + key;
                        if ($el.parent().find('.' + hintClass).length === 0) {
                            const hintHtml = `<div class="${hintClass}" style="margin-top:5px; font-size:0.76rem; font-weight:700; color:#ef4444; display:flex; align-items:flex-start; gap:5px; line-height:1.4;">
                                <svg style="width:12px;height:12px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                                <span><b>Admin:</b> ${$('<div>').text(note).html()}</span>
                            </div>`;
                            $el.parent().after(hintHtml);
                        }
                    }
                }
            });
        });
    }

    // Restore normal draft if no query parameters exist
    if (!rollbackId && !continueBatchId) {
        const rawDraft = localStorage.getItem('inventory_create_draft');
        if (rawDraft) {
            try {
                const draftState = JSON.parse(rawDraft);
                restoreFormState(draftState);
            } catch(e) {
                console.error("Error loading normal draft:", e);
            }
        }
    }
});
</script>
@endsection
