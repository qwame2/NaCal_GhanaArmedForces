@extends('layouts.dashboard')

@section('content')
<style>
    .discrepancy-header-mobile {
        padding: 1.75rem !important;
        background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-main) 100%) !important;
        border-radius: 0 0 32px 32px !important;
        margin: -24px -24px 2rem -24px !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.04) !important;
        border-bottom: 1px solid var(--border-color) !important;
    }
    .item-entry-row {
        margin-bottom: 2rem;
        padding: 2rem 1.5rem 1.5rem 1.5rem;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        background: var(--bg-card);
        position: relative;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .item-entry-row:hover {
        border-color: var(--primary-light);
        box-shadow: 0 10px 25px -5px rgba(136, 19, 55, 0.05);
    }
    .remove-row-btn:hover {
        background: rgba(239, 68, 68, 0.2) !important;
        color: #ef4444 !important;
    }
    #toggleSupplierSection:hover label {
        color: var(--primary) !important;
    }
    #toggleSupplierSection:hover #supplierToggleArrow {
        color: var(--primary) !important;
    }
    .rotate-180 {
        transform: rotate(180deg) !important;
    }

    /* Premium Select2 Dropdown Visibility & Theme Styling Overrides */
    .select2-container {
        z-index: 100000 !important;
    }
    .select2-dropdown {
        z-index: 9999999 !important;
        background-color: var(--bg-card) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 14px !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25) !important;
        overflow: hidden !important;
    }
    .select2-container--default .select2-selection--single {
        background-color: var(--bg-card) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 12px !important;
        height: 48px !important;
        display: flex !important;
        align-items: center !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05) !important;
        transition: all 0.3s ease !important;
    }
    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #ef4444 !important;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--text-main) !important;
        font-weight: 700 !important;
        font-size: 0.9rem !important;
        line-height: 46px !important;
        padding-left: 1rem !important;
        padding-right: 2rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
        right: 10px !important;
    }
    .select2-container--default .select2-results__option {
        color: var(--text-main) !important;
        font-weight: 600 !important;
        font-size: 0.9rem !important;
        padding: 10px 14px !important;
    }
    html:not([data-theme='dark']) .select2-container--default .select2-results__option:not(.select2-results__option--highlighted) {
        color: #0f172a !important;
        background-color: #ffffff !important;
    }
    html:not([data-theme='dark']) .select2-dropdown {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
    }
    html:not([data-theme='dark']) .select2-dropdown .select2-search__field {
        color: #0f172a !important;
        background-color: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #ef4444 !important;
        color: #ffffff !important;
    }
    .select2-container--default .select2-search--dropdown {
        padding: 8px !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border-radius: 8px !important;
        padding: 8px 12px !important;
    }
</style>

<div class="animate-slide-up" id="discrepancyPageContainer">
    <!-- Header Section -->
    <div class="page-header discrepancy-header-mobile" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(239, 68, 68, 0.1); color: #ef4444; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em;">Logistics Control</span>
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main); margin: 0;">Record Existing <span style="color: #ef4444;">Items</span></h2>
            <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Record physical shortages or surpluses compared to ledger book balances.</p>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--text-main); text-decoration: none; transition: all 0.3s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.05)'" onmouseout="this.style.background='var(--bg-card)'">
                <i data-lucide="arrow-left" style="width: 18px;"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="glass-card" style="border-radius: 24px; padding: 2rem; display: flex; flex-direction: column; margin-bottom: 2rem; background: var(--bg-card); border: 1px solid var(--border-color);">
        <form id="discrepancyForm" novalidate>
            @csrf
            
            <!-- Batch Header Information -->
            <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 2rem; margin-bottom: 2rem;">
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin-top: 0; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="info" style="color: #ef4444; width: 22px; height: 22px;"></i>
                    Record Existing Item Header
                </h3>

                <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <!-- Category Section -->
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                            <i data-lucide="layers" style="width: 14px; color: var(--primary);"></i>
                            Category Section <span style="color: #ef4444; margin-left: 2px;">*</span>
                        </label>
                        <select id="ledgeSelect" style="width: 100%;" required>
                            <option value=""></option>
                            @foreach($ledgeMap as $code => $name)
                                <option value="{{ $code }}">Category {{ $code }} - {{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Collected -->
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                            <i data-lucide="calendar" style="width: 14px; color: var(--primary);"></i>
                            Received On<span style="color: #ef4444; margin-left: 2px;">*</span>
                        </label>
                        <input type="date" id="arrivalDate" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600;" required>
                    </div>

                    <!-- Delivery Status -->
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                            <i data-lucide="activity" style="width: 14px; color: var(--primary);"></i>
                            Delivery Status <span style="color: #ef4444; margin-left: 2px;">*</span>
                        </label>
                        <select id="supplierStatusSelect" style="width: 100%;" required>
                            <option value=""></option>
                            <option value="Full Delivery">Full Delivery</option>
                            <option value="Partial Delivery">Partial Delivery</option>
                        </select>
                    </div>

                    <!-- Number of Items -->
                    <div id="qtyControl" class="form-group" style="display: none; opacity: 0;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                            <i data-lucide="hash" style="width: 14px; color: var(--primary);"></i>
                            Number of different items
                        </label>
                        <input type="number" id="multiQty" min="1" max="50" value="1" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600;">
                    </div>

                    <!-- Supplier / Source (Full Info) -->
                    <div class="form-group" style="grid-column: 1 / -1; border: 1px solid var(--border-color); border-radius: 16px; padding: 1.5rem; background: var(--bg-card); transition: all 0.3s ease;">
                        <div id="toggleSupplierSection" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none;">
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 750; color: var(--text-main); margin-bottom: 0; cursor: pointer;">
                                <i data-lucide="truck" style="width: 16px; color: var(--primary);"></i>
                                Supplier / Source Name (Search or Type)
                            </label>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span id="supplierSummaryBadge" style="font-size: 0.75rem; background: rgba(136, 19, 55, 0.1); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 700; display: none;"></span>
                                <i data-lucide="chevron-down" id="supplierToggleArrow" style="width: 18px; height: 18px; color: var(--text-muted); transition: transform 0.3s ease;"></i>
                            </div>
                        </div>

                        <div id="supplierSectionContent" style="display: none; margin-top: 1.25rem; border-top: 1px dashed var(--border-color); padding-top: 1.25rem;">
                            <select id="supplierSelect" style="width: 100%;">
                                <option value=""></option>
                                @foreach($allSuppliers as $supplier)
                                    <option value="{{ $supplier }}">{{ $supplier }}</option>
                                @endforeach
                            </select>

                            <!-- Supplier Contact & Delivery Details -->
                            <div id="deliveryPersonGroup" style="margin-top: 1.25rem; background: rgba(0,0,0,0.01); border: 1px solid var(--border-color); padding: 1.5rem; border-radius: 16px;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.75rem;">
                                    <div>
                                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 6px;">
                                            <i data-lucide="phone" style="width: 12px; color: var(--primary);"></i>
                                            Supplier Phone
                                        </label>
                                        <input type="text" id="supplierPhoneInput" class="form-control" placeholder="Enter supplier phone number" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                    </div>
                                    <div>
                                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 6px;">
                                            <i data-lucide="mail" style="width: 12px; color: var(--primary);"></i>
                                            Supplier Email
                                        </label>
                                        <input type="email" id="supplierEmailInput" class="form-control" placeholder="Enter supplier email address" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                    </div>
                                </div>
                                <div style="margin-bottom: 0.75rem;">
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 6px;">
                                        <i data-lucide="map-pin" style="width: 12px; color: var(--primary);"></i>
                                        Supplier Physical Address
                                    </label>
                                    <input type="text" id="supplierAddressInput" class="form-control" placeholder="Enter supplier physical address" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.75rem;">
                                    <div>
                                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                            <i data-lucide="user" style="width: 12px; color: var(--primary);"></i>
                                            Contact Person Name
                                        </label>
                                        <input type="text" id="deliveryPersonInput" class="form-control" placeholder="Contact person name" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                    </div>
                                    <div>
                                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                            <i data-lucide="phone" style="width: 12px; color: var(--primary);"></i>
                                            Contact Person Number
                                        </label>
                                        <input type="text" id="deliveryPersonPhoneInput" maxlength="10" class="form-control" placeholder="Contact number (10 digits)" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                    </div>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div>
                                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                            <i data-lucide="truck" style="width: 12px; color: var(--primary);"></i>
                                            Delivery Person Name
                                        </label>
                                        <input type="text" id="driverNameInput" class="form-control" placeholder="Enter delivery's name" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                    </div>
                                    <div>
                                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                            <i data-lucide="phone" style="width: 12px; color: var(--primary);"></i>
                                            Delivery Person Number
                                        </label>
                                        <input type="text" id="driverPhoneInput" maxlength="10" class="form-control" placeholder="Enter delivery's number" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item Details Section -->
            <div id="itemDetails" style="display: none; margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 10px;">
                        <i data-lucide="package" style="color: #ef4444; width: 22px; height: 22px;"></i>
                        Discrepant Items List
                    </h3>
                    <button type="button" id="addRowBtn" class="glass-card" style="padding: 0.6rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--primary); background: rgba(136, 19, 55, 0.05); transition: all 0.2s;" onmouseover="this.style.background='rgba(136, 19, 55, 0.1)'" onmouseout="this.style.background='rgba(136, 19, 55, 0.05)'">
                        <i data-lucide="plus-circle" style="width: 16px;"></i> Add Item Row
                    </button>
                </div>

                <div id="itemsContainer">
                    <!-- Item Rows will be injected here dynamically -->
                </div>
            </div>

            <!-- Form Footer / Submit -->
            <div id="formFooter" style="display: none; border-top: 1px solid var(--border-color); padding-top: 2rem; gap: 1rem; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
                <a href="{{ route('dashboard') }}" class="glass-card" style="padding: 1.15rem 2.5rem; text-decoration: none; font-weight: 700; color: var(--text-muted); display: flex; align-items: center; justify-content: center; border-radius: 14px;">
                    Cancel
                </a>
                <button type="submit" class="btn-primary" style="padding: 1.15rem 3.5rem; border: none; border-radius: 14px; cursor: pointer; background: #ef4444; color: white; display: flex; align-items: center; justify-content: center; gap: 0.75rem; box-shadow: 0 10px 20px -5px rgba(239, 68, 68, 0.4); font-weight: 800;">
                    <i data-lucide="save" style="width: 20px;"></i>
                    Submit Record
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const existingDBItems = @json($existingItems);
    const ledgeMap = @json($ledgeMap);
    const suppliersRegistry = @json($suppliersRegistry);

    jQuery(document).ready(function($) {
        const ledgeSelect = $('#ledgeSelect');
        const itemDetails = $('#itemDetails');
        const formFooter = $('#formFooter');
        const container = $('#itemsContainer');
        const multiQtyInput = $('#multiQty');

        const supplierSelect = $('#supplierSelect');
        const supplierContent = $('#supplierSectionContent');
        const toggleSupplierSection = $('#toggleSupplierSection');
        const supplierToggleArrow = $('#supplierToggleArrow');
        const supplierSummaryBadge = $('#supplierSummaryBadge');

        // Ensure hidden state is tracked by jQuery
        formFooter.hide();
        itemDetails.hide();

        // Initialize Select2 dropdowns
        ledgeSelect.select2({
            placeholder: 'Select Category',
            width: '100%'
        });

        supplierSelect.select2({
            placeholder: 'Select Supplier/Source',
            width: '100%',
            tags: true
        });

        // Toggle Supplier Section
        toggleSupplierSection.on('click', function() {
            supplierContent.slideToggle(300);
            supplierToggleArrow.toggleClass('rotate-180');
        });

        // Update supplier summary badge dynamically
        function updateSupplierSummary() {
            const val = supplierSelect.val();
            if (val) {
                supplierSummaryBadge.text(val).show();
            } else {
                supplierSummaryBadge.hide();
            }
        }
        supplierSelect.on('change', updateSupplierSummary);

        $('#supplierStatusSelect').select2({
            placeholder: 'Select Status',
            width: '100%'
        });

        // Toggle Delivery Status and Update Labels
        $('#supplierStatusSelect').on('change', function() {
            const status = $(this).val();
            if (status === 'Partial Delivery') {
                container.children('.item-entry-row').each(function() {
                    const $row = $(this);
                    $row.find('.row-book-qty').closest('.form-group').find('label').html(
                        `<i data-lucide="book-open" style="width: 14px; color: var(--primary);"></i> Expected / Invoice Qty <span style="color: #ef4444; margin-left: 2px;">*</span>`
                    );
                    $row.find('.row-received-qty').closest('.form-group').find('label').html(
                        `<i data-lucide="check-square" style="width: 14px; color: var(--primary);"></i> Actual Received Qty <span style="color: #ef4444; margin-left: 2px;">*</span>`
                    );
                    $row.find('.row-calculated-discrepancy').closest('.form-group').find('label').html(
                        `<i data-lucide="alert-triangle" style="width: 14px; color: #ef4444;"></i> Calculated Variance`
                    );
                    $row.find('.row-explanation').closest('.form-group').find('label').html(
                        `<i data-lucide="help-circle" style="width: 14px; color: #ef4444;"></i> Explanation / Remarks`
                    );
                });
                $('#itemDetails h3').html(
                    `<i data-lucide="package" style="color: #ef4444; width: 22px; height: 22px;"></i> Deliveries List`
                );
            } else {
                container.children('.item-entry-row').each(function() {
                    const $row = $(this);
                    $row.find('.row-book-qty').closest('.form-group').find('label').html(
                        `<i data-lucide="book-open" style="width: 14px; color: var(--primary);"></i> Book Quantity <span style="color: #ef4444; margin-left: 2px;">*</span>`
                    );
                    $row.find('.row-received-qty').closest('.form-group').find('label').html(
                        `<i data-lucide="check-square" style="width: 14px; color: var(--primary);"></i> Received Quantity <span style="color: #ef4444; margin-left: 2px;">*</span>`
                    );
                    $row.find('.row-calculated-discrepancy').closest('.form-group').find('label').html(
                        `<i data-lucide="alert-triangle" style="width: 14px; color: #ef4444;"></i> Calculated Discrepancy`
                    );
                    $row.find('.row-explanation').closest('.form-group').find('label').html(
                        `<i data-lucide="help-circle" style="width: 14px; color: #ef4444;"></i> Discrepancy Explanation`
                    );
                });
                $('#itemDetails h3').html(
                    `<i data-lucide="package" style="color: #ef4444; width: 22px; height: 22px;"></i> Discrepant Items List`
                );
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });

        // Set default date collected
        $('#arrivalDate').val(new Date().toISOString().split('T')[0]);

        // Handle Ledge Selection change
        function handleLedgeChange(e) {
            try {
                let val = ledgeSelect.val();
                if (e && e.params && e.params.data && e.params.data.id !== undefined) {
                    val = e.params.data.id;
                }
                const selectedLedge = String(val || '').toUpperCase().trim();
                if (selectedLedge) {
                    $('#qtyControl').show().animate({ opacity: 1 }, 400);
                    itemDetails.show().css('display', 'block');
                    formFooter.css('display', 'flex');

                    if (container.children().length === 0) {
                        renderItemRows(1);
                        multiQtyInput.val(1);
                    } else {
                        // Update any child categories if they exist
                        $('.item-entry-row').each(function() {
                            const $row = $(this);
                            const rowLedge = $row.find('.row-ledge-select');
                            if (rowLedge.val() !== selectedLedge) {
                                rowLedge.val(selectedLedge).trigger('change.select2').trigger('change');
                            }
                        });
                        updateRowBadges();
                    }
                } else {
                    $('#qtyControl').hide().css('opacity', 0);
                    itemDetails.hide();
                    formFooter.hide();
                    container.empty();
                    multiQtyInput.val(1);
                }
            } catch (err) {
                console.error("Error in master category select handler:", err);
            }
        }

        ledgeSelect.on('change select2:select', handleLedgeChange);
        ledgeSelect.on('select2:unselect', handleLedgeChange);

        // Supplier Registry autofill
        $('#supplierSelect').on('change', function() {
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

        // Add Row Button
        $('#addRowBtn').on('click', function() {
            renderItemRows(1, true);
            multiQtyInput.val(container.children('.item-entry-row').length);
        });

        // Multi Qty listener
        multiQtyInput.on('input', function() {
            const val = parseInt($(this).val()) || 0;
            if (val > 0 && val <= 50) {
                renderItemRows(val);
            } else if (val === 0) {
                container.empty();
            }
        });

        // Render rows function
        function renderItemRows(count, append = false) {
            if (!append) container.empty();

            const selectedLedge = String(ledgeSelect.val() || '').toUpperCase().trim();
            const categoryText = $('#ledgeSelect option:selected').text().trim();
            const suffix = categoryText ? ' - ' + categoryText : '';

            const standardPackages = ['PIECE(S)', 'PACK', 'BOXES', 'CARTON', 'BAG', 'ROLL', 'SET', 'REAM', 'BOTTLE'];
            const existingUnits = (existingDBItems || []).map(item => String(item && item.unit || '').toUpperCase().trim()).filter(Boolean);
            const allPackages = [...new Set([...standardPackages, ...existingUnits])];
            const packageOptionsHtml = allPackages.map(pkg => `<option value="${pkg}">${pkg}</option>`).join('');

            const ledgeOptionsHtml = Object.entries(ledgeMap || {}).map(([code, name]) => 
                `<option value="${code}">Category ${code} - ${name}</option>`
            ).join('');

            for (let i = 0; i < count; i++) {
                const currentRows = container.children('.item-entry-row').length;
                const itemIdx = currentRows + 1;

                const rowHtml = `
                    <div class="item-entry-row">
                        <div class="row-badge" style="position: absolute; top: -12px; left: 1rem; background: #ef4444; color: white; padding: 2px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 800;">
                            <span class="badge-text">ITEM TYPE #${itemIdx}${suffix}</span>
                        </div>

                        <button type="button" class="remove-row-btn" style="position: absolute; top: 1.25rem; right: 1.25rem; background: rgba(239, 68, 68, 0.08); color: #ef4444; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                            <i data-lucide="trash-2" style="width: 16px;"></i>
                        </button>

                        <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 0.5rem;">
                            <!-- Category Section -->
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                    <i data-lucide="layers" style="width: 14px; color: var(--primary);"></i>
                                    Category Section <span style="color: #ef4444; margin-left: 2px;">*</span>
                                </label>
                                <select class="row-ledge-select" style="width: 100%;" required>
                                    <option value=""></option>
                                    ${ledgeOptionsHtml}
                                </select>
                            </div>

                            <!-- Item Name -->
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                    <i data-lucide="tag" style="width: 14px; color: var(--primary);"></i>
                                    Item Description <span style="color: #ef4444; margin-left: 2px;">*</span>
                                </label>
                                <select class="row-item-select" style="width: 100%;" required>
                                    <option value=""></option>
                                </select>
                            </div>

                            <!-- Package Type -->
                            <div class="form-group">
                                 <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                     <i data-lucide="package" style="width: 14px; color: var(--primary);"></i>
                                     Package Type <span style="color: #ef4444; margin-left: 2px;">*</span>
                                 </label>
                                 <select class="row-unit-select" style="width: 100%;" required>
                                     <option value=""></option>
                                     ${packageOptionsHtml}
                                 </select>
                             </div>

                            <!-- Store Location -->
                            <div class="form-group">
                                 <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                     <i data-lucide="map-pin" style="width: 14px; color: var(--primary);"></i>
                                     Store Location <span style="color: #ef4444; margin-left: 2px;">*</span>
                                 </label>
                                 <div class="store-location-container-sleek" style="position: relative; display: flex; align-items: center; width: 100%;">
                                     <select class="row-store-location-select" style="width: 100%;" required>
                                         <option value="Store A" selected>Store A</option>
                                         <option value="Store B">Store B</option>
                                     </select>
                                     <div style="position: absolute; left: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary); opacity: 0.8; pointer-events: none; z-index: 5;">
                                         <i data-lucide="building-2" style="width: 16px; height: 16px;"></i>
                                     </div>
                                 </div>
                             </div>
                        </div>

                        <!-- Quantity Analysis Row -->
                        <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.5rem; margin-top: 1.5rem; border-top: 1px dashed var(--border-color); padding-top: 1.5rem;">
                            <!-- Book Quantity -->
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                    <i data-lucide="book-open" style="width: 14px; color: var(--primary);"></i>
                                    Book Quantity <span style="color: #ef4444; margin-left: 2px;">*</span>
                                </label>
                                <input type="number" class="row-book-qty form-control" placeholder="Ledger count" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600;" required>
                            </div>

                            <!-- Received Quantity -->
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                    <i data-lucide="check-square" style="width: 14px; color: var(--primary);"></i>
                                    Received Quantity <span style="color: #ef4444; margin-left: 2px;">*</span>
                                </label>
                                <input type="number" class="row-received-qty form-control" placeholder="Actual count" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600;" required>
                            </div>

                            <!-- Calculated Discrepancy -->
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                    <i data-lucide="alert-triangle" style="width: 14px; color: #ef4444;"></i>
                                    Calculated Discrepancy
                                </label>
                                <input type="text" class="row-calculated-discrepancy form-control" readonly value="0" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 1rem; font-weight: 900; outline: none; cursor: not-allowed;">
                            </div>
                        </div>

                        <!-- Serial / Rim Sizes Group -->
                        <div class="serial-number-group" style="display: none; margin-top: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 0;">
                                    <i data-lucide="barcode" style="width: 14px; color: var(--primary);"></i>
                                    Serial Number(s)
                                </label>
                                <button type="button" class="btn-bulk-paste" style="background: none; border: none; color: var(--primary); font-size: 0.72rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 2px 6px; border-radius: 6px; transition: all 0.2s;">
                                    <i data-lucide="clipboard" style="width: 11px; height: 11px;"></i> Bulk Import
                                </button>
                            </div>
                            <input type="hidden" class="row-serial-number">
                            <div class="serial-inputs-container" style="max-height: 200px; overflow-y: auto; padding: 6px; display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.4rem; border: 1px solid var(--border-color); border-radius: 12px; background: var(--bg-main);"></div>
                        </div>

                        <!-- Discrepancy Explanation -->
                        <div class="form-group" style="margin-top: 1.5rem;">
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                <i data-lucide="help-circle" style="width: 14px; color: #ef4444;"></i>
                                Discrepancy Explanation
                            </label>
                            <select class="row-explanation form-control" style="width: 100%;">
                                <option value=""></option>
                                <option value="Counting Error">Counting Error</option>
                                <option value="Data Entry Error">Data Entry Error</option>
                                <option value="Damaged Items">Damaged Items</option>
                                <option value="Lost Items">Lost Items</option>
                                <option value="Theft/Shrinkage">Theft/Shrinkage</option>
                                <option value="Expired Items">Expired Items</option>
                                <option value="Issued but Not Recorded">Issued but Not Recorded</option>
                                <option value="Received but Not Recorded">Received but Not Recorded</option>
                                <option value="Wrong Item Recorded">Wrong Item Recorded</option>
                                <option value="Packaging Difference">Packaging Difference</option>
                                <option value="Supplier Short Delivery">Supplier Short Delivery</option>
                                <option value="Supplier Over Delivery">Supplier Over Delivery</option>
                                <option value="Stock Adjustment">Stock Adjustment</option>
                                <option value="Transfer Between Stores">Transfer Between Stores</option>
                                <option value="System Synchronization Error">System Synchronization Error</option>
                                <option value="Others">Others</option>
                            </select>
                            <div class="others-textarea-wrapper" style="display: none; margin-top: 0.75rem;">
                                <textarea class="row-explanation-custom form-control" placeholder="Please specify other reason..." style="width: 100%; min-height: 80px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; resize: vertical;"></textarea>
                            </div>
                        </div>
                    </div>
                `;

                const $row = $(rowHtml);
                container.append($row);

                const $rowLedgeSelect = $row.find('.row-ledge-select');
                const $itemSelect = $row.find('.row-item-select');
                const $rowUnitSelect = $row.find('.row-unit-select');
                const $rowStoreLocationSelect = $row.find('.row-store-location-select');
                const $rowExplanationSelect = $row.find('.row-explanation');
                const $othersWrapper = $row.find('.others-textarea-wrapper');

                // Initialize Select2 on row category section
                $rowLedgeSelect.select2({
                    placeholder: 'Search/Select Category',
                    width: '100%'
                });

                // Initialize Select2 on the item dropdown
                $itemSelect.select2({
                    placeholder: 'Search/Select or Type Item Name',
                    width: '100%',
                    tags: true,
                    createTag: function (params) {
                        var term = $.trim(params.term).toUpperCase();
                        if (term === '') return null;
                        return {
                            id: term,
                            text: term,
                            newTag: true
                        };
                    }
                });

                // Initialize Select2 on package type dropdown
                $rowUnitSelect.select2({
                    placeholder: 'Select Package Type',
                    width: '100%',
                    tags: true
                });

                // Initialize Select2 on store location dropdown
                $rowStoreLocationSelect.select2({
                    placeholder: 'Select Store Location',
                    width: '100%',
                    minimumResultsForSearch: Infinity
                });

                // Initialize Select2 on discrepancy explanation dropdown
                $rowExplanationSelect.select2({
                    placeholder: 'Select Discrepancy Explanation',
                    width: '100%'
                });

                // Function to update item options dynamically based on row Category Section
                function updateItemSelectOptions() {
                    const rowLedge = String($rowLedgeSelect.val() || '').toUpperCase().trim();
                    const filteredItems = (existingDBItems || []).filter(item => item && String(item.ledge_category || '').toUpperCase().trim() === rowLedge);
                    let optionsHtml = '<option value=""></option>';
                    filteredItems.forEach(item => {
                        optionsHtml += `<option value="${item.description}">${item.description}</option>`;
                    });
                    $itemSelect.html(optionsHtml).trigger('change.select2').trigger('change');
                }

                $rowLedgeSelect.on('change select2:select', function() {
                    updateItemSelectOptions();
                    updateRowBadges();
                });

                // Default to header selection and populate
                if (selectedLedge) {
                    $rowLedgeSelect.val(selectedLedge).trigger('change.select2').trigger('change');
                } else {
                    updateItemSelectOptions();
                }

                $rowExplanationSelect.on('change select2:select', function() {
                    const val = $(this).val();
                    if (val === 'Others') {
                        $othersWrapper.slideDown(200);
                        $row.find('.row-explanation-custom').attr('required', true);
                    } else {
                        $othersWrapper.slideUp(200);
                        $row.find('.row-explanation-custom').removeAttr('required').val('');
                    }
                });

                // Auto-fill Package Type if matches existing DB item
                $itemSelect.on('change', function() {
                    const desc = String($(this).val() || '').trim().toUpperCase();
                    const matched = (existingDBItems || []).find(item => item && String(item.description || '').toUpperCase().trim() === desc);
                    if (matched && matched.unit) {
                        const unitVal = String(matched.unit).toUpperCase().trim();
                        if ($rowUnitSelect.find('option[value="' + unitVal + '"]').length === 0) {
                            $rowUnitSelect.append(new Option(unitVal, unitVal, true, true));
                        }
                        $rowUnitSelect.val(unitVal).trigger('change.select2').trigger('change');
                    } else {
                        $rowUnitSelect.val(null).trigger('change.select2').trigger('change');
                    }
                });

                // Auto-calculation of discrepancy for this row
                const $bookQtyInput = $row.find('.row-book-qty');
                const $receivedQtyInput = $row.find('.row-received-qty');
                const $discField = $row.find('.row-calculated-discrepancy');

                $bookQtyInput.add($receivedQtyInput).on('input', function() {
                    const bookQty = parseFloat($bookQtyInput.val()) || 0;
                    const receivedQty = parseFloat($receivedQtyInput.val()) || 0;
                    const discrepancy = receivedQty - bookQty;

                    $discField.val((discrepancy > 0 ? '+' : '') + discrepancy);

                    if (discrepancy > 0) {
                        $discField.css('color', '#881337');
                    } else if (discrepancy < 0) {
                        $discField.css('color', '#ef4444');
                    } else {
                        $discField.css('color', 'var(--text-main)');
                    }
                });

                // Serial Numbers / Rim Sizes handler
                function updateSerialInputs() {
                    const selectedLedge = String($rowLedgeSelect.val() || '').toUpperCase().trim();
                    const isSerialCategory = ['C', 'J', 'D'].includes(selectedLedge);
                    const $container = $row.find('.serial-inputs-container');
                    const $group = $row.find('.serial-number-group');

                    if (!isSerialCategory) {
                        $container.empty();
                        $group.hide();
                        $row.find('.row-serial-number').val('');
                        return;
                    }

                    const qty = parseInt($receivedQtyInput.val()) || 0;
                    if (qty <= 0) {
                        $container.empty();
                        $group.hide();
                        $row.find('.row-serial-number').val('');
                        return;
                    }

                    $group.show();

                    // Check if Tyre in Transport
                    const descText = String($itemSelect.val() || '').toUpperCase().trim();
                    const isTyre = (selectedLedge === 'D' && (descText.includes('TYRE') || descText.includes('TYRES')));

                    const $label = $row.find('.serial-number-group label');
                    const $bulkBtn = $row.find('.btn-bulk-paste');
                    if (isTyre) {
                        $label.html(`<i data-lucide="disc" style="width: 14px; color: var(--primary);"></i> Tyre Details (Serial & Rim)`);
                        $bulkBtn.hide();
                        $container.css('grid-template-columns', 'repeat(auto-fill, minmax(200px, 1fr))');
                    } else {
                        $label.html(`<i data-lucide="barcode" style="width: 14px; color: var(--primary);"></i> Serial Number(s)`);
                        $bulkBtn.show();
                        $container.css('grid-template-columns', 'repeat(auto-fill, minmax(130px, 1fr))');
                    }
                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    // Get current values
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

                $rowLedgeSelect.add($itemSelect).on('change select2:select', function() {
                    updateSerialInputs();
                });

                $receivedQtyInput.on('input', function() {
                    updateSerialInputs();
                });

                // Remove Row button listener
                $row.find('.remove-row-btn').on('click', function() {
                    $row.remove();
                    updateRowBadges();
                    multiQtyInput.val(container.children('.item-entry-row').length);
                    saveFormState();
                });
            }

            if (typeof lucide !== 'undefined') lucide.createIcons();
            updateRowBadges();
            $('#supplierStatusSelect').trigger('change');
        }

        // Update badges suffix
        function updateRowBadges() {
            container.children('.item-entry-row').each(function(index) {
                const itemIdx = index + 1;
                const $row = $(this);
                const $rowLedgeSelect = $row.find('.row-ledge-select');
                const categoryText = $rowLedgeSelect.find('option:selected').text().trim();
                const suffix = categoryText ? ' - ' + categoryText : '';
                $row.find('.badge-text').text('ITEM TYPE #' + itemIdx + suffix);
            });
        }

        // Form submission handling
        $('#discrepancyForm').on('submit', function(e) {
            e.preventDefault();

            // Client-side validations
            const ledge = $('#ledgeSelect').val();
            const supplier = $('#supplierSelect').val();
            const dateCollected = $('#arrivalDate').val();

            // Delivery Details validations
            const deliveryPerson = $('#deliveryPersonInput').val().trim();
            const deliveryPhone = $('#deliveryPersonPhoneInput').val().trim();
            const driverName = $('#driverNameInput').val().trim();
            const driverPhone = $('#driverPhoneInput').val().trim();

            const missing = [];
            const deliveryStatus = $('#supplierStatusSelect').val();
            if (!ledge) missing.push("Category Section");
            if (!dateCollected) missing.push("Date Collected");
            if (!deliveryStatus) missing.push("Delivery Status");

            if (deliveryPhone && deliveryPhone.toUpperCase() !== 'N/A' && !/^\d{10}$/.test(deliveryPhone)) {
                missing.push("Contact Person Number (must be a 10-digit number or N/A)");
            }

            if (driverPhone && driverPhone.toUpperCase() !== 'N/A' && !/^\d{10}$/.test(driverPhone)) {
                missing.push("Delivery Person Number (must be a 10-digit number or N/A)");
            }

            const items = [];
            container.children('.item-entry-row').each(function(index) {
                const idx = index + 1;
                const rowLedge = $(this).find('.row-ledge-select').val();
                const desc = ($(this).find('.row-item-select').val() || '').trim();
                const unit = ($(this).find('.row-unit-select').val() || '').trim();
                const bookQty = $(this).find('.row-book-qty').val();
                const receivedQty = $(this).find('.row-received-qty').val();
                let explanation = ($(this).find('.row-explanation').val() || '').trim();
                if (explanation === 'Others') {
                    explanation = ($(this).find('.row-explanation-custom').val() || '').trim();
                }
                const serialNum = $(this).find('.row-serial-number').val();

                const isTyre = (rowLedge === 'D' && (desc.toUpperCase().includes('TYRE') || desc.toUpperCase().includes('TYRES')));

                const storeLocation = ($(this).find('.row-store-location-select').val() || '').trim();

                if (!rowLedge) missing.push(`Item Type #${idx}: Category Section`);
                if (!desc) missing.push(`Item Type #${idx}: Description`);
                if (!unit) missing.push(`Item Type #${idx}: Package Type`);
                if (!storeLocation) missing.push(`Item Type #${idx}: Store Location`);
                if (bookQty === '') missing.push(`Item Type #${idx}: Book Quantity`);
                if (receivedQty === '') missing.push(`Item Type #${idx}: Received Quantity`);

                if (isTyre) {
                    const parsedQty = parseInt(receivedQty) || 0;
                    let rimsCount = 0;
                    if (serialNum) {
                        const parts = serialNum.split(',').map(s => s.trim());
                        parts.forEach(part => {
                            if (part.includes('(Rim:')) {
                                rimsCount++;
                            }
                        });
                    }
                    if (rimsCount < parsedQty) {
                        missing.push(`Item Type #${idx}: Rim Size for all ${parsedQty} Tyres must be specified`);
                    }
                }

                items.push({
                    ledge_category: rowLedge,
                    description: desc,
                    serial_number: serialNum || null,
                    unit: unit,
                    store_location: storeLocation || 'Store A',
                    qty: receivedQty,
                    stock_balance: bookQty,
                    variance: (parseFloat(receivedQty) || 0) - (parseFloat(bookQty) || 0),
                    remarks: explanation,
                    book_qty: bookQty,
                    discrepancy_explanation: explanation
                });
            });

            if (items.length === 0) {
                missing.push("At least one item type row is required");
            }

            if (missing.length > 0) {
                const hasSupplierErrors = missing.some(m => 
                    m.includes("Contact Person Number") || 
                    m.includes("Delivery Person Number")
                );
                if (hasSupplierErrors) {
                    $('#supplierSectionContent').slideDown(300);
                    $('#supplierToggleArrow').addClass('rotate-180');
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Required Fields Missing',
                    html: `<div style="text-align: left; font-size: 0.9rem; font-weight: 600; color: var(--text-main);">
                            Please complete the following required fields before submitting:
                            <ul style="margin-top: 10px; padding-left: 20px; color: #ef4444; line-height: 1.6;">
                                ${missing.map(f => `<li>${f}</li>`).join('')}
                            </ul>
                           </div>`,
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            const btn = $(this).find('button[type="submit"]');
            const originalHtml = btn.html();
            btn.html('<i class="animate-spin" data-lucide="loader-2"></i> Saving...').prop('disabled', true);
            if (typeof lucide !== 'undefined') lucide.createIcons();

            // Format entry date
            const now = new Date();
            const formattedEntryDate = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}-${String(now.getDate()).padStart(2,'0')} ${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}:${String(now.getSeconds()).padStart(2,'0')}`;

            const payload = {
                _token: '{{ csrf_token() }}',
                ledge_category: ledge,
                supplier_name: supplier,
                supplier_status: deliveryStatus,
                donor_name: null,
                acquisition_type: 'Supplier',
                entry_date: formattedEntryDate,
                arrival_date: dateCollected,
                driver_name: driverName,
                driver_phone: driverPhone,
                delivery_person: deliveryPerson,
                delivery_phone: deliveryPhone,
                supplier_phone: $('#supplierPhoneInput').val().trim() || null,
                supplier_email: $('#supplierEmailInput').val().trim() || null,
                supplier_address: $('#supplierAddressInput').val().trim() || null,
                items: items
            };

            $.ajax({
                url: '{{ route("inventory.discrepancy.store", [], false) }}',
                method: 'POST',
                data: payload,
                success: function(response) {
                    if (response.success) {
                        localStorage.removeItem('inventory_discrepancy_draft');
                        if (response.is_pending) {
                            if (typeof window.playNotificationSound === 'function') {
                                window.playNotificationSound('sent');
                            }
                            Swal.fire({
                                icon: 'info',
                                title: 'REQUEST SUBMITTED',
                                text: 'Your discrepancy reports have been submitted to the Admin for approval.',
                                confirmButtonColor: '#ef4444',
                                confirmButtonText: 'Great, Thank you!'
                            }).then(() => {
                                window.location.href = '{{ route("dashboard") }}';
                            });
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Discrepancy records saved successfully!',
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

        $(document).on('input', '.serial-input-item, .rim-input-item', function() {
            const $row = $(this).closest('.item-entry-row');
            const selectedLedge = String($row.find('.row-ledge-select').val() || '').toUpperCase().trim();
            const descText = String($row.find('.row-item-select').val() || '').toUpperCase().trim();
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
        }); // end .btn-bulk-paste click handler

        // Save Form State to LocalStorage
        window.isRestoringDraft = false;
        function saveFormState() {
            if (window.isRestoringDraft) return;

            const items = [];
            container.children('.item-entry-row').each(function() {
                const ledgeCategory = $(this).find('.row-ledge-select').val() || '';
                const desc = $(this).find('.row-item-select').val() || '';
                const unit = $(this).find('.row-unit-select').val() || '';
                const storeLocation = $(this).find('.row-store-location-select').val() || 'Store A';
                const bookQty = $(this).find('.row-book-qty').val() || '';
                const qty = $(this).find('.row-received-qty').val() || '';
                const calculatedDiscrepancy = $(this).find('.row-calculated-discrepancy').val() || '';
                const serialNumber = $(this).find('.row-serial-number').val() || '';
                const explanation = $(this).find('.row-explanation').val() || '';
                const explanationCustom = $(this).find('.row-explanation-custom').val() || '';

                // Individual serials/rims
                const serials = [];
                const rims = [];
                $(this).find('.serial-input-wrapper').each(function() {
                    serials.push($(this).find('.serial-input-item').val() || '');
                    rims.push($(this).find('.rim-input-item').val() || '');
                });

                items.push({
                    ledge_category: ledgeCategory,
                    description: desc,
                    unit: unit,
                    store_location: storeLocation,
                    book_qty: bookQty,
                    qty: qty,
                    calculated_discrepancy: calculatedDiscrepancy,
                    serial_number: serialNumber,
                    explanation: explanation,
                    explanation_custom: explanationCustom,
                    serials: serials,
                    rims: rims
                });
            });

            const state = {
                ledge_category: $('#ledgeSelect').val(),
                arrival_date: $('#arrivalDate').val(),
                supplier_status: $('#supplierStatusSelect').val(),
                multiQty: $('#multiQty').val(),
                supplier_name: $('#supplierSelect').val(),
                supplier_phone: $('#supplierPhoneInput').val(),
                supplier_email: $('#supplierEmailInput').val(),
                supplier_address: $('#supplierAddressInput').val(),
                delivery_person: $('#deliveryPersonInput').val(),
                delivery_phone: $('#deliveryPersonPhoneInput').val(),
                driver_name: $('#driverNameInput').val(),
                driver_phone: $('#driverPhoneInput').val(),
                items: items
            };

            localStorage.setItem('inventory_discrepancy_draft', JSON.stringify(state));
        }

        // Populate a dynamic item row
        function populateRow($row, item) {
            if (!item) return;

            if (item.ledge_category) {
                $row.find('.row-ledge-select').val(item.ledge_category).trigger('change.select2').trigger('change');
            }

            if (item.description) {
                const $descSelect = $row.find('.row-item-select');
                if ($descSelect.find(`option[value="${item.description}"]`).length === 0) {
                    $descSelect.append(new Option(item.description, item.description, true, true));
                }
                $descSelect.val(item.description).trigger('change.select2').trigger('change');
            }

            if (item.unit) {
                const $unitSelect = $row.find('.row-unit-select');
                if ($unitSelect.find(`option[value="${item.unit}"]`).length === 0) {
                    $unitSelect.append(new Option(item.unit, item.unit, true, true));
                }
                $unitSelect.val(item.unit).trigger('change.select2').trigger('change');
            }

            if (item.store_location) {
                $row.find('.row-store-location-select').val(item.store_location).trigger('change.select2').trigger('change');
            }

            if (item.book_qty) {
                $row.find('.row-book-qty').val(item.book_qty).trigger('input');
            }

            if (item.qty) {
                $row.find('.row-received-qty').val(item.qty).trigger('input');
            }

            if (item.explanation) {
                $row.find('.row-explanation').val(item.explanation).trigger('change.select2').trigger('change');
            }

            if (item.explanation_custom) {
                $row.find('.row-explanation-custom').val(item.explanation_custom);
            }

            if (item.serial_number) {
                $row.find('.row-serial-number').val(item.serial_number);
                $row.find('.row-received-qty').trigger('input');

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
            if (state.arrival_date) {
                $('#arrivalDate').val(state.arrival_date);
            }
            if (state.supplier_status) {
                $('#supplierStatusSelect').val(state.supplier_status).trigger('change.select2').trigger('change');
            }
            if (state.multiQty) {
                $('#multiQty').val(state.multiQty);
            }
            if (state.supplier_name) {
                const cleanName = state.supplier_name.replace(/\s\[.*\]$/, '');
                if ($('#supplierSelect option[value="' + cleanName + '"]').length === 0) {
                    $('#supplierSelect').append(new Option(cleanName, cleanName, true, true));
                }
                $('#supplierSelect').val(cleanName).trigger('change.select2').trigger('change');
            }
            if (state.supplier_phone) $('#supplierPhoneInput').val(state.supplier_phone);
            if (state.supplier_email) $('#supplierEmailInput').val(state.supplier_email);
            if (state.supplier_address) $('#supplierAddressInput').val(state.supplier_address);
            if (state.delivery_person) $('#deliveryPersonInput').val(state.delivery_person);
            if (state.delivery_phone) $('#deliveryPersonPhoneInput').val(state.delivery_phone);
            if (state.driver_name) $('#driverNameInput').val(state.driver_name);
            if (state.driver_phone) $('#driverPhoneInput').val(state.driver_phone);

            if (state.supplier_name || state.supplier_phone || state.supplier_email || state.supplier_address || state.delivery_person || state.delivery_phone || state.driver_name || state.driver_phone) {
                $('#supplierSectionContent').show();
                $('#supplierToggleArrow').addClass('rotate-180');
                updateSupplierSummary();
            }

            if (state.items && state.items.length > 0) {
                renderItemRows(state.items.length);
                container.children('.item-entry-row').each(function(index) {
                    populateRow($(this), state.items[index]);
                });
            }

            window.isRestoringDraft = false;
        }

        // Attach listener to form elements to save draft automatically
        $(document).on('change input select2:select select2:unselect', '#discrepancyForm input, #discrepancyForm select, #discrepancyForm textarea', function() {
            saveFormState();
        });

        // Check if normal draft exists and restore it
        const rawDraft = localStorage.getItem('inventory_discrepancy_draft');
        if (rawDraft) {
            try {
                const draftState = JSON.parse(rawDraft);
                restoreFormState(draftState);
            } catch(e) {
                console.error("Error loading discrepancy draft:", e);
            }
        }
    });
</script>
@endsection
