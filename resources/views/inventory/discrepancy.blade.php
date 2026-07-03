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
        box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.05);
    }
    .remove-row-btn:hover {
        background: rgba(239, 68, 68, 0.2) !important;
        color: #ef4444 !important;
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
                            Date Collected <span style="color: #ef4444; margin-left: 2px;">*</span>
                        </label>
                        <input type="date" id="arrivalDate" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600;" required>
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
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                            <i data-lucide="truck" style="width: 14px; color: var(--primary);"></i>
                            Supplier / Source Name (Search or Type) <span style="color: #ef4444; margin-left: 2px;">*</span>
                        </label>
                        <select id="supplierSelect" style="width: 100%;" required>
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
                                        Contact Person Name <span style="color: #ef4444; margin-left: 2px;">*</span>
                                    </label>
                                    <input type="text" id="deliveryPersonInput" class="form-control" placeholder="Contact person name" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;" required>
                                </div>
                                <div>
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                        <i data-lucide="phone" style="width: 12px; color: var(--primary);"></i>
                                        Contact Person Number <span style="color: #ef4444; margin-left: 2px;">*</span>
                                    </label>
                                    <input type="text" id="deliveryPersonPhoneInput" maxlength="10" class="form-control" placeholder="Contact number (10 digits)" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;" required>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                        <i data-lucide="truck" style="width: 12px; color: var(--primary);"></i>
                                        Delivery Person Name <span style="color: #ef4444; margin-left: 2px;">*</span>
                                    </label>
                                    <input type="text" id="driverNameInput" class="form-control" placeholder="Driver name" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;" required>
                                </div>
                                <div>
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                        <i data-lucide="phone" style="width: 12px; color: var(--primary);"></i>
                                        Delivery Person Number <span style="color: #ef4444; margin-left: 2px;">*</span>
                                    </label>
                                    <input type="text" id="driverPhoneInput" maxlength="10" class="form-control" placeholder="Driver phone (10 digits)" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;" required>
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
                    <button type="button" id="addRowBtn" class="glass-card" style="padding: 0.6rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--primary); background: rgba(99, 102, 241, 0.05); transition: all 0.2s;" onmouseover="this.style.background='rgba(99, 102, 241, 0.1)'" onmouseout="this.style.background='rgba(99, 102, 241, 0.05)'">
                        <i data-lucide="plus-circle" style="width: 16px;"></i> Add Item Row
                    </button>
                </div>

                <div id="itemsContainer">
                    <!-- Item Rows will be injected here dynamically -->
                </div>
            </div>

            <!-- Form Footer / Submit -->
            <div id="formFooter" style="display: none; border-top: 1px solid var(--border-color); padding-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
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
</script>

@endsection

@push('scripts')
<script>
    jQuery(document).ready(function($) {
        const ledgeSelect = $('#ledgeSelect');
        const itemDetails = $('#itemDetails');
        const formFooter = $('#formFooter');
        const container = $('#itemsContainer');
        const multiQtyInput = $('#multiQty');

        // Initialize Select2 dropdowns
        ledgeSelect.select2({
            placeholder: 'Select Category',
            width: '100%'
        });

        $('#supplierSelect').select2({
            placeholder: 'Select Supplier/Source',
            width: '100%',
            tags: true
        });

        // Set default date collected
        $('#arrivalDate').val(new Date().toISOString().split('T')[0]);

        // Handle Ledge Selection change
        ledgeSelect.on('change select2:select', function() {
            const selectedLedge = ($(this).val() || '').toUpperCase().trim();
            if (selectedLedge) {
                $('#qtyControl').show().animate({ opacity: 1 }, 400);
                itemDetails.slideDown(400);
                formFooter.fadeIn(400);

                if (container.children().length === 0) {
                    renderItemRows(1);
                    multiQtyInput.val(1);
                } else {
                    // Update any child categories if they exist
                    $('.item-entry-row').each(function() {
                        const $row = $(this);
                        const rowLedge = $row.find('.row-ledge-category');
                        if (rowLedge.val() !== selectedLedge) {
                            rowLedge.val(selectedLedge).trigger('change');
                        }
                    });
                    updateRowBadges();
                }
            } else {
                $('#qtyControl').hide().css('opacity', 0);
                itemDetails.slideUp(400);
                formFooter.fadeOut(400);
                container.empty();
                multiQtyInput.val(1);
            }
        });

        // Supplier Registry autofill
        const suppliersRegistry = @json($suppliersRegistry);
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

            const selectedLedge = (ledgeSelect.val() || '').toUpperCase().trim();
            const categoryText = $('#ledgeSelect option:selected').text().trim();
            const suffix = categoryText ? ' - ' + categoryText : '';

            const standardPackages = ['PIECE(S)', 'PACK', 'BOXES', 'CARTON', 'BAG', 'ROLL', 'SET', 'REAM', 'BOTTLE'];
            const existingUnits = (existingDBItems || []).map(item => (item && item.unit || '').toUpperCase().trim()).filter(Boolean);
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

                        <!-- Discrepancy Explanation -->
                        <div class="form-group" style="margin-top: 1.5rem;">
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                                <i data-lucide="help-circle" style="width: 14px; color: #ef4444;"></i>
                                Discrepancy Explanation
                            </label>
                            <textarea class="row-explanation form-control" placeholder="Explain why the discrepancy occurred..." style="width: 100%; min-height: 80px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; resize: vertical;"></textarea>
                        </div>
                    </div>
                `;

                const $row = $(rowHtml);
                container.append($row);

                const $rowLedgeSelect = $row.find('.row-ledge-select');
                const $itemSelect = $row.find('.row-item-select');

                // Initialize Select2 on row category section
                $rowLedgeSelect.select2({
                    placeholder: 'Search/Select Category',
                    width: '100%'
                });

                // Function to update item options dynamically based on row Category Section
                function updateItemSelectOptions() {
                    const rowLedge = ($rowLedgeSelect.val() || '').toUpperCase().trim();
                    const filteredItems = (existingDBItems || []).filter(item => item && (item.ledge_category || '').toUpperCase().trim() === rowLedge);
                    let optionsHtml = '<option value=""></option>';
                    filteredItems.forEach(item => {
                        optionsHtml += `<option value="${item.description}">${item.description}</option>`;
                    });
                    $itemSelect.html(optionsHtml);
                }

                // Default to header selection and populate
                if (selectedLedge) {
                    $rowLedgeSelect.val(selectedLedge).trigger('change.select2');
                }
                updateItemSelectOptions();

                $rowLedgeSelect.on('change select2:select', function() {
                    updateItemSelectOptions();
                    updateRowBadges();
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
                const $rowUnitSelect = $row.find('.row-unit-select');
                $rowUnitSelect.select2({
                    placeholder: 'Select Package Type',
                    width: '100%',
                    tags: true
                });

                // Auto-fill Package Type if matches existing DB item
                $itemSelect.on('change', function() {
                    const desc = ($(this).val() || '').trim().toUpperCase();
                    const matched = (existingDBItems || []).find(item => item && (item.description || '').toUpperCase().trim() === desc);
                    if (matched && matched.unit) {
                        const unitVal = matched.unit.toUpperCase().trim();
                        if ($rowUnitSelect.find('option[value="' + unitVal + '"]').length === 0) {
                            $rowUnitSelect.append(new Option(unitVal, unitVal, true, true));
                        }
                        $rowUnitSelect.val(unitVal).trigger('change');
                    } else {
                        $rowUnitSelect.val(null).trigger('change');
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
                        $discField.css('color', '#10b981');
                    } else if (discrepancy < 0) {
                        $discField.css('color', '#ef4444');
                    } else {
                        $discField.css('color', 'var(--text-main)');
                    }
                });

                // Remove Row button listener
                $row.find('.remove-row-btn').on('click', function() {
                    $row.remove();
                    updateRowBadges();
                    multiQtyInput.val(container.children('.item-entry-row').length);
                });
            }

            if (typeof lucide !== 'undefined') lucide.createIcons();
            updateRowBadges();
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
            if (!ledge) missing.push("Category Section");
            if (!supplier) missing.push("Supplier / Source");
            if (!dateCollected) missing.push("Date Collected");

            if (!deliveryPerson) missing.push("Contact Person Name");
            if (!deliveryPhone) {
                missing.push("Contact Person Number");
            } else if (deliveryPhone.toUpperCase() !== 'N/A' && !/^\d{10}$/.test(deliveryPhone)) {
                missing.push("Contact Person Number (must be a 10-digit number or N/A)");
            }

            if (!driverName) missing.push("Delivery Person Name");
            if (!driverPhone) {
                missing.push("Delivery Person Number");
            } else if (driverPhone.toUpperCase() !== 'N/A' && !/^\d{10}$/.test(driverPhone)) {
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
                const explanation = $(this).find('.row-explanation').val().trim();

                if (!rowLedge) missing.push(`Item Type #${idx}: Category Section`);
                if (!desc) missing.push(`Item Type #${idx}: Description`);
                if (!unit) missing.push(`Item Type #${idx}: Package Type`);
                if (bookQty === '') missing.push(`Item Type #${idx}: Book Quantity`);
                if (receivedQty === '') missing.push(`Item Type #${idx}: Received Quantity`);

                items.push({
                    ledge_category: rowLedge,
                    description: desc,
                    serial_number: null,
                    unit: unit,
                    qty: receivedQty,
                    stock_balance: receivedQty,
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
                supplier_status: 'Full Delivery',
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
                                confirmButtonColor: '#10b981',
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
    });
</script>
@endpush
