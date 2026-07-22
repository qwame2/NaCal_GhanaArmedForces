@extends('layouts.dashboard')

@section('title', 'Suppliers Registry')

@section('content')
<style>
    .main-wrapper > *:not(header) {
        max-width: 100% !important;
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

    /* Config Cards & Common Elements styling */
    .cfg-card {
        background: white;
        border-radius: 28px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .cfg-card-header {
        padding: 2rem 2.5rem;
        border-bottom: 1px solid #f1f5f9;
        background: #fafbff;
    }
    .cfg-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }
    .cfg-icon-box i {
        width: 22px;
        height: 22px;
    }
    .cfg-card-header h3 {
        font-size: 1.15rem;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -0.02em;
    }
    .cfg-card-header p {
        font-size: 0.8rem;
        font-weight: 500;
        color: #64748b;
    }
    .cfg-card-body {
        padding: 2.5rem;
    }

    /* Form Input Controls styling */
    .cfg-form-stack {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .cfg-text-input {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        font-weight: 700;
        border: 1.5px solid #edf2f7;
        border-radius: 12px;
        background: #f8fafc;
        color: #1e293b;
        outline: none;
        transition: all 0.3s ease;
    }
    .cfg-text-input:focus {
        border-color: #881337;
        background: white;
        box-shadow: 0 8px 20px rgba(136, 19, 55, 0.08);
    }

    /* Supplier Pagination Premium Styling */
    .supplier-pag-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: white;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        outline: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .supplier-pag-btn:hover:not(:disabled) {
        border-color: var(--primary);
        color: var(--primary);
        background: var(--primary-glow);
        transform: translateY(-1px);
    }
    .supplier-pag-btn.active {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
        box-shadow: 0 4px 12px rgba(136, 19, 55, 0.25);
    }
    .supplier-pag-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8fafc;
    }
    .supplier-pag-ellipsis {
        font-size: 0.85rem;
        font-weight: 700;
        color: #94a3b8;
        width: 24px;
        text-align: center;
    }

    /* Custom Add / Edit buttons */
    .btn-cfg-add {
        padding: 0.75rem 1.25rem;
        font-size: 0.85rem;
        font-weight: 800;
        border-radius: 14px;
        border: none;
        color: white;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.2s;
    }
    .btn-cfg-add:hover {
        transform: translateY(-1.5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        filter: brightness(1.05);
    }
</style>

<div class="settings-shell">
    {{-- Supplier Registry Container --}}
    <div class="cfg-card" id="suppliers-registry">
        <div class="cfg-card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div class="cfg-icon-box" style="background: #881337;">
                    <i data-lucide="truck"></i>
                </div>
                <div>
                    <h3 style="margin: 0 0 0.25rem 0;">Suppliers Details</h3>
                    <p style="margin: 0;">Register pre-defined suppliers and their contact details for users to easily select.</p>
                </div>
            </div>

            <div class="cfg-search-wrap" style="width: 300px; position: relative; display: flex; align-items: center;">
                <span style="position: absolute; left: 14px; color: #94a3b8; display: flex; align-items: center; pointer-events: none;">
                    <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                </span>
                <input type="text" id="supplierSearch" placeholder="Search suppliers..." oninput="filterSuppliers()" style="width: 100%; padding: 10px 16px 10px 40px; font-size: 0.85rem; font-weight: 700; height: 42px; border: 1.5px solid transparent; border-radius: 12px; outline: none; transition: all 0.3s ease; background: #f1f5f9; color: #0f172a;" onfocus="this.style.background='white'; this.style.borderColor='#881337'; this.style.boxShadow='0 8px 20px rgba(136, 19, 55, 0.08)';" onblur="this.style.background='#f1f5f9'; this.style.borderColor='transparent'; this.style.boxShadow='none';">
            </div>
        </div>
        <div class="cfg-card-body">
             @php
                 $isHeadOfStores = true;
             @endphp
            <div style="display: grid; grid-template-columns: {{ $isHeadOfStores ? '1fr' : '1fr 360px' }}; gap: 2rem; align-items: start;">

                {{-- Existing Suppliers List --}}
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                        <p style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="list" style="width: 14px;"></i> Registered Suppliers
                        </p>
                        <span id="supplierPaginationInfo" style="font-size: 0.75rem; font-weight: 700; color: #64748b;"></span>
                    </div>
                    
                    <div class="table-responsive custom-scrollbar" style="border: 1px solid #edf2f7; border-radius: 20px; background: white; overflow-x: auto; margin-bottom: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
                        <table class="table" style="width: 100%; border-collapse: collapse; margin: 0; font-size: 0.8rem; text-align: left; table-layout: auto;">
                            <thead>
                                <tr style="border-bottom: 2px solid #edf2f7; background: #fafbff; color: #475569;">
                                     <th style="padding: 1rem 1.25rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.72rem;">Company Name</th>
                                     <th style="padding: 1rem 1.25rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.72rem;">Contact Person</th>
                                     <th style="padding: 1rem 1.25rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.72rem;">Delivery Person</th>
                                     <th style="padding: 1rem 1.25rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.72rem; width: 260px;">Deliveries</th>
                                     <th style="padding: 1rem 1.25rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.72rem;">Company Contacts</th>
                                     <th style="padding: 1rem 1.25rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.72rem;">Address / Notes</th>
                                     @if(!$isHeadOfStores)
                                     <th style="padding: 1rem 1.25rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.72rem; text-align: right;">Actions</th>
                                     @endif
                                 </tr>
                            </thead>
                            <tbody id="suppliersRegistryContainer" style="font-weight: 600; color: #475569;"></tbody>
                        </table>
                    </div>
                    
                    <div id="noSuppliersRegistered" style="display: none; padding: 2rem; text-align: center; background: #f8fafc; border-radius: 16px; border: 1.5px dashed #e2e8f0;">
                        <i data-lucide="inbox" style="width: 32px; height: 32px; color: #cbd5e1; margin-bottom: 0.75rem;"></i>
                        <p style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; margin: 0;">No suppliers registered yet.</p>
                    </div>

                    <div id="noSuppliersFound" style="display: none; padding: 2rem; text-align: center; background: #f8fafc; border-radius: 16px; border: 1.5px dashed #e2e8f0; margin-top: 1rem;">
                        <i data-lucide="search" style="width: 32px; height: 32px; color: #cbd5e1; margin-bottom: 0.75rem;"></i>
                        <p style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; margin: 0;">No matching suppliers found.</p>
                    </div>

                    <div id="suppliersPagination" style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; flex-wrap: wrap;"></div>
                </div>

                {{-- Add / Edit Form Panel (Hidden for Head of Stores) --}}
                @if(!$isHeadOfStores)
                <div style="background: #fafbff; border: 1.5px solid #edf2f7; border-radius: 20px; padding: 2rem;">
                    <h4 id="supplierFormTitle" style="font-size: 0.95rem; font-weight: 900; color: #0f172a; margin: 0 0 1.5rem 0; display: flex; align-items: center; gap: 8px;">
                        Add Supplier
                    </h4>
                    <form action="{{ route('admin.settings.supplier.store') }}" method="POST" id="supplierForm">
                        @csrf
                        <div class="cfg-form-stack">
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Supplier/Donor Name *</label>
                                <input type="text" name="name" id="supplierNameInput" required class="cfg-text-input" placeholder="e.g. Acme Supplies Ltd">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Contact Person Name</label>
                                <input type="text" name="contact_person" id="supplierContactPersonInput" class="cfg-text-input" placeholder="e.g. John Doe">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Contact Person Number</label>
                                <input type="text" name="contact_phone" id="supplierContactPhoneInput" class="cfg-text-input" placeholder="e.g. +233 2400000">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Contact Phone Number</label>
                                <input type="text" name="phone" id="supplierPhoneInput" class="cfg-text-input" placeholder="e.g. +233 2400000">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Email Address</label>
                                <input type="email" name="email" id="supplierEmailInput" class="cfg-text-input" placeholder="e.g. orders@acme.com">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Physical Address</label>
                                <input type="text" name="address" id="supplierAddressInput" class="cfg-text-input" placeholder="e.g. Cantonments, Accra">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Description / Notes</label>
                                <textarea name="desc" id="supplierDescInput" class="cfg-text-input" placeholder="e.g. Lead distributor for stationery items" style="min-height: 80px; font-family: inherit; resize: vertical; padding: 0.75rem 1rem; border-radius: 12px;"></textarea>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" id="supplierSubmitBtn" class="btn-cfg-add" style="flex: 1; background: #881337; margin-top: 0.5rem;">
                                    <i data-lucide="plus-circle" id="supplierSubmitIcon"></i> <span id="supplierSubmitText">Add Supplier</span>
                                </button>
                                <button type="button" id="supplierResetBtn" onclick="resetSupplierForm()" style="display: none; padding: 0.75rem 1rem; background: #f1f5f9; color: #64748b; border: none; border-radius: 14px; font-weight: 800; cursor: pointer; transition: 0.2s; margin-top: 0.5rem;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

<script>
    const isHeadOfStores = @json($isHeadOfStores);
    const rawSuppliers = @json($suppliersRegistry ?? []);
    let suppliersList = [];
    for (const [name, details] of Object.entries(rawSuppliers)) {
        suppliersList.push({
            name: name,
            contact_person: details.contact_person || details.delivery_person || '',
            contact_phone: details.contact_phone || details.delivery_phone || '',
            delivery_person: details.delivery_person || '',
            delivery_phone: details.delivery_phone || '',
            phone: details.phone || '',
            email: details.email || '',
            address: details.address || '',
            desc: details.desc || '',
            total_deliveries: details.total_deliveries || 0,
            first_delivery: details.first_delivery || null,
            last_delivery: details.last_delivery || null,
            all_deliveries: details.all_deliveries || []
        });
    }

    let filteredSuppliers = [...suppliersList];
    let currentSupplierPage = 1;
    const suppliersPerPage = 12;

    const csrfToken = "{{ csrf_token() }}";
    const deleteRoute = "{{ route('admin.settings.supplier.destroy') }}";

    function escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function populateSupplierFormByIndex(idx) {
        const s = filteredSuppliers[idx];
        if (s) {
            populateSupplierForm(s.name, s.contact_person, s.contact_phone, s.phone, s.email, s.address, s.desc);
        }
    }

    function generateMiniCalendar(firstDateStr, lastDateStr, allDatesArr, index) {
        if (!lastDateStr) {
            return '<div style="color: #94a3b8; font-size: 0.7rem; text-align: center; font-weight: 700;">No deliveries</div>';
        }
        
        const renderMonth = (targetDateStr, highlightFirst = false, highlightLast = false) => {
            const parts = targetDateStr.split('-');
            const year = parseInt(parts[0], 10);
            const month = parseInt(parts[1], 10) - 1; // 0-indexed
            
            const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            const dayNames = ["S", "M", "T", "W", "T", "F", "S"];
            
            const firstDayIndex = new Date(year, month, 1).getDay();
            const lastDay = new Date(year, month + 1, 0).getDate();
            
            let html = `
            <div style="width: 194px; background: #fafbff; border: 1.5px solid #edf2f7; border-radius: 14px; padding: 8px; font-family: inherit; margin: 0 auto; box-shadow: 0 4px 10px rgba(0,0,0,0.01);">
                <div style="font-size: 0.78rem; font-weight: 800; color: #1e293b; text-align: center; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; padding: 0 4px;">
                    <span style="color: #881337;">${monthNames[month]} ${year}</span>
                    <span style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">
                        ${highlightFirst && highlightLast ? 'History' : (highlightFirst ? 'First Del.' : 'Last Del.')}
                    </span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center; font-size: 0.68rem; font-weight: 800; color: #cbd5e1; margin-bottom: 6px;">
                    ${dayNames.map(d => `<div>${d}</div>`).join('')}
                </div>
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center; font-size: 0.72rem;">
            `;
            
            for (let i = 0; i < firstDayIndex; i++) {
                html += `<div></div>`;
            }
            
            for (let day = 1; day <= lastDay; day++) {
                const currentDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                
                let cellStyle = 'width: 22px; height: 22px; line-height: 22px; margin: 0 auto; border-radius: 6px; font-weight: 800; display: flex; align-items: center; justify-content: center;';
                let cellTitle = '';
                
                if (currentDateStr === lastDateStr && highlightLast) {
                    cellStyle += ' background: #ef4444; color: white; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);';
                    cellTitle = 'Last Delivery';
                } else if (currentDateStr === firstDateStr && highlightFirst) {
                    cellStyle += ' background: #881337; color: white; box-shadow: 0 2px 4px rgba(136, 19, 55, 0.3);';
                    cellTitle = 'First Delivery';
                } else if (allDatesArr.includes(currentDateStr)) {
                    cellStyle += ' background: #dbeafe; color: #1e40af;';
                    cellTitle = 'Delivery Date';
                } else {
                    cellStyle += ' color: #64748b;';
                }
                
                html += `
                <div style="${cellStyle}" title="${cellTitle ? `${cellTitle}: ${day}/${month+1}/${year}` : ''}">
                    ${day}
                </div>`;
            }
            
            html += `
                </div>
            </div>`;
            
            return html;
        };

        try {
            const hasFirst = !!firstDateStr;
            const isDifferentMonth = hasFirst && (firstDateStr.substring(0, 7) !== lastDateStr.substring(0, 7));
            
            let calendarsHtml = '';
            let tabsHtml = '';
            
            if (isDifferentMonth) {
                tabsHtml = `
                <div style="display: flex; background: #f1f5f9; padding: 3px; border-radius: 10px; margin-bottom: 10px; font-size: 0.68rem; font-weight: 800; border: 1px solid #e2e8f0;">
                    <button type="button" onclick="event.stopPropagation(); document.getElementById('cal-first-${index}').style.display='block'; document.getElementById('cal-last-${index}').style.display='none'; this.style.background='white'; this.style.color='#881337'; this.style.boxShadow='0 2px 6px rgba(136,19,55,0.1)'; this.nextElementSibling.style.background='none'; this.nextElementSibling.style.color='#64748b'; this.nextElementSibling.style.boxShadow='none';" style="flex: 1; border: none; padding: 6px; border-radius: 8px; cursor: pointer; transition: all 0.2s ease; background: none; color: #64748b; font-weight: 900; outline: none;">
                        First Month
                    </button>
                    <button type="button" onclick="event.stopPropagation(); document.getElementById('cal-first-${index}').style.display='none'; document.getElementById('cal-last-${index}').style.display='block'; this.style.background='white'; this.style.color='#ef4444'; this.style.boxShadow='0 2px 6px rgba(239,68,68,0.1)'; this.previousElementSibling.style.background='none'; this.previousElementSibling.style.color='#64748b'; this.previousElementSibling.style.boxShadow='none';" style="flex: 1; border: none; padding: 6px; border-radius: 8px; cursor: pointer; transition: all 0.2s ease; background: white; color: #ef4444; font-weight: 900; box-shadow: 0 2px 6px rgba(239,68,68,0.1); outline: none;">
                        Last Month
                    </button>
                </div>`;
                
                calendarsHtml = `
                <div id="cal-first-${index}" style="display: none;">
                    ${renderMonth(firstDateStr, true, false)}
                </div>
                <div id="cal-last-${index}" style="display: block;">
                    ${renderMonth(lastDateStr, false, true)}
                </div>`;
            } else {
                calendarsHtml = renderMonth(lastDateStr, true, true);
            }
            
            return `
            <div style="display: flex; flex-direction: column; gap: 4px;">
                ${tabsHtml}
                ${calendarsHtml}
                <div style="display: flex; justify-content: center; gap: 10px; margin-top: 10px; border-top: 1px solid #edf2f7; padding-top: 8px;">
                    <span style="display: inline-flex; align-items: center; gap: 6px; background: #eef2ff; color: #881337; padding: 2px 8px; border-radius: 20px; font-size: 0.65rem; font-weight: 800; border: 1px solid rgba(136, 19, 55, 0.15);">
                        <span style="width: 6px; height: 6px; background: #881337; border-radius: 50%;"></span>First
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 6px; background: #fef2f2; color: #ef4444; padding: 2px 8px; border-radius: 20px; font-size: 0.65rem; font-weight: 800; border: 1px solid rgba(239, 68, 68, 0.15);">
                        <span style="width: 6px; height: 6px; background: #ef4444; border-radius: 50%;"></span>Last
                    </span>
                </div>
            </div>`;
        } catch (e) {
            return '<div style="color: #ef4444; font-size: 0.7rem; text-align: center;">Error rendering calendar</div>';
        }
    }

    function renderSupplierCard(supplier, index) {
        let contactPersonHtml = '-';
        if (supplier.contact_person) {
            contactPersonHtml = `
            <div style="display: flex; flex-direction: column; gap: 2px;">
                <span style="font-weight: 800; color: #0f172a;">${escapeHtml(supplier.contact_person)}</span>
                ${supplier.contact_phone ? `<span style="font-size: 0.72rem; color: #64748b; font-family: monospace;">${escapeHtml(supplier.contact_phone)}</span>` : ''}
            </div>`;
        }

        let deliveryPersonHtml = '-';
        if (supplier.delivery_person) {
            deliveryPersonHtml = `
            <div style="display: flex; flex-direction: column; gap: 2px;">
                <span style="font-weight: 800; color: #0f172a;">${escapeHtml(supplier.delivery_person)}</span>
                ${supplier.delivery_phone ? `<span style="font-size: 0.72rem; color: #64748b; font-family: monospace;">${escapeHtml(supplier.delivery_phone)}</span>` : ''}
            </div>`;
        }

        const formatDate = (dateStr) => {
            if (!dateStr) return 'N/A';
            const parts = dateStr.split('-');
            if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
            return dateStr;
        };

        const firstDel = supplier.first_delivery;
        const lastDel = supplier.last_delivery;

        const miniCalendarHtml = generateMiniCalendar(firstDel, lastDel, supplier.all_deliveries, index);

        let deliveriesInfoHtml = `
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; min-width: 220px;">
            <div style="display: flex; flex-direction: column; gap: 2px;">
                <div style="font-size: 0.72rem; color: #475569; display: flex; align-items: center; gap: 4px;">
                    <strong>Total:</strong> <span style="background: #e0e7ff; color: #4c0519; padding: 1px 6px; border-radius: 4px; font-weight: 800; font-size: 0.7rem;">${supplier.total_deliveries}</span>
                </div>
                <div style="font-size: 0.65rem; color: #64748b; margin-top: 2px;">
                    First: <span style="font-family: monospace; font-weight: 700; color: #881337;">${formatDate(firstDel)}</span>
                </div>
                <div style="font-size: 0.65rem; color: #64748b;">
                    Last: <span style="font-family: monospace; font-weight: 700; color: #ef4444;">${formatDate(lastDel)}</span>
                </div>
            </div>
            
            ${firstDel ? `
            <div style="position: relative; display: inline-block;">
                <button type="button" onclick="toggleCalendarPopover(event, this)" style="padding: 4px 8px; background: #f1f5f9; color: #64748b; border: 1px solid #edf2f7; border-radius: 8px; font-weight: 800; font-size: 0.7rem; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                    <i data-lucide="calendar" style="width: 12px; height: 12px;"></i> Calendar
                </button>
                 <div class="calendar-popover" style="display: none; position: fixed; z-index: 9999; background: white; border: 1.5px solid #edf2f7; border-radius: 16px; padding: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); width: 216px;">
                     ${miniCalendarHtml}
                  </div>
            </div>
            ` : ''}
        </div>`;

        let companyContactsHtml = '';
        if (supplier.phone) {
            companyContactsHtml += `<div style="display: flex; align-items: center; gap: 4px;"><i data-lucide="phone" style="width: 12px; height: 12px; color: #94a3b8;"></i> ${escapeHtml(supplier.phone)}</div>`;
        }
        if (supplier.email) {
            companyContactsHtml += `<div style="display: flex; align-items: center; gap: 4px;"><i data-lucide="mail" style="width: 12px; height: 12px; color: #94a3b8;"></i> ${escapeHtml(supplier.email)}</div>`;
        }
        if (!companyContactsHtml) companyContactsHtml = '-';

        let addressNotesHtml = '';
        if (supplier.address) {
            addressNotesHtml += `<div style="display: flex; align-items: flex-start; gap: 4px;"><i data-lucide="map-pin" style="width: 12px; height: 12px; color: #94a3b8; margin-top: 2px; flex-shrink: 0;"></i> <span>${escapeHtml(supplier.address)}</span></div>`;
        }
        if (supplier.desc) {
            addressNotesHtml += `<div style="font-size: 0.72rem; color: #64748b; margin-top: 4px; line-height: 1.3;">${escapeHtml(supplier.desc)}</div>`;
        }
        if (!addressNotesHtml) addressNotesHtml = '-';

        const actionBtnsHtml = isHeadOfStores ? '' : `
            <td style="padding: 0.75rem 1.25rem; text-align: right; vertical-align: middle;">
                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 8px;">
                    <button type="button" onclick="populateSupplierFormByIndex(${index})" style="width: 28px; height: 28px; border-radius: 8px; background: #f1f5f9; border: none; color: #64748b; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                        <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                    </button>
                    <form action="${deleteRoute}" method="POST" onsubmit="return confirm('Remove ${escapeHtml(supplier.name)} from the registry?');" style="margin: 0;">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="name" value="${escapeHtml(supplier.name)}">
                        <button type="submit" style="width: 28px; height: 28px; border-radius: 8px; background: #fff1f2; border: none; color: #f43f5e; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#ffe4e6'" onmouseout="this.style.background='#fff1f2'">
                            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                        </button>
                    </form>
                </div>
            </td>
        `;

        return `
        <tr class="supplier-row" data-name="${escapeHtml(supplier.name.toLowerCase())}" style="border-bottom: 1px solid #f1f5f9; transition: 0.2s;" onmouseover="this.style.background='#f8fbff'" onmouseout="this.style.background='transparent'">
            <td style="padding: 1rem 1.25rem; font-weight: 800; color: #0f172a; vertical-align: middle;">${escapeHtml(supplier.name)}</td>
            <td style="padding: 1rem 1.25rem; vertical-align: middle;">${contactPersonHtml}</td>
            <td style="padding: 1rem 1.25rem; vertical-align: middle;">${deliveryPersonHtml}</td>
            <td style="padding: 1rem 1.25rem; vertical-align: middle;">${deliveriesInfoHtml}</td>
            <td style="padding: 1rem 1.25rem; vertical-align: middle;">
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    ${companyContactsHtml}
                </div>
            </td>
            <td style="padding: 1rem 1.25rem; vertical-align: middle;">
                <div style="display: flex; flex-direction: column; gap: 2px;">
                    ${addressNotesHtml}
                </div>
            </td>
            ${actionBtnsHtml}
        </tr>
        `;
    }

    function renderSupplierPagination(totalPages, currentPage) {
        const pagDiv = document.getElementById('suppliersPagination');
        if (!pagDiv) return;
        pagDiv.innerHTML = '';
        
        if (totalPages <= 1) return;

        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.innerHTML = '<i data-lucide="chevron-left" style="width: 16px; height: 16px;"></i>';
        prevBtn.disabled = currentPage === 1;
        prevBtn.className = 'supplier-pag-btn prev';
        prevBtn.onclick = () => goToSupplierPage(currentPage - 1);
        pagDiv.appendChild(prevBtn);

        const range = 2;
        let pages = [];

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - range && i <= currentPage + range)) {
                pages.push(i);
            } else if (i === currentPage - range - 1 || i === currentPage + range + 1) {
                pages.push('...');
            }
        }

        pages = pages.filter((item, pos, self) => {
            return pos === 0 || !(item === '...' && self[pos - 1] === '...');
        });

        pages.forEach(p => {
            const btn = document.createElement('button');
            btn.type = 'button';
            if (p === '...') {
                btn.textContent = '...';
                btn.disabled = true;
                btn.className = 'supplier-pag-ellipsis';
            } else {
                btn.textContent = p;
                if (p === currentPage) {
                     btn.className = 'supplier-pag-btn active';
                } else {
                     btn.className = 'supplier-pag-btn';
                     btn.onclick = () => goToSupplierPage(p);
                }
            }
            pagDiv.appendChild(btn);
        });

        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.innerHTML = '<i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.className = 'supplier-pag-btn next';
        nextBtn.onclick = () => goToSupplierPage(currentPage + 1);
        pagDiv.appendChild(nextBtn);
        
        if (window.lucide) lucide.createIcons();
    }

    function displaySuppliersPage(page) {
        currentSupplierPage = page;
        const container = document.getElementById('suppliersRegistryContainer');
        if (!container) return;
        
        container.innerHTML = '';
        
        const startIndex = (page - 1) * suppliersPerPage;
        const endIndex = Math.min(startIndex + suppliersPerPage, filteredSuppliers.length);
        
        if (suppliersList.length === 0) {
            const noReg = document.getElementById('noSuppliersRegistered');
            if (noReg) noReg.style.display = '';
            const noResults = document.getElementById('noSuppliersFound');
            if (noResults) noResults.style.display = 'none';
            
            const infoSpan = document.getElementById('supplierPaginationInfo');
            if (infoSpan) infoSpan.textContent = 'Showing 0 - 0 of 0 suppliers';
            
            const pagDiv = document.getElementById('suppliersPagination');
            if (pagDiv) pagDiv.innerHTML = '';
            return;
        } else {
            const noReg = document.getElementById('noSuppliersRegistered');
            if (noReg) noReg.style.display = 'none';
        }

        if (filteredSuppliers.length === 0) {
            const noResults = document.getElementById('noSuppliersFound');
            if (noResults) noResults.style.display = '';
            
            const infoSpan = document.getElementById('supplierPaginationInfo');
            if (infoSpan) infoSpan.textContent = 'Showing 0 - 0 of 0 suppliers';
            
            const pagDiv = document.getElementById('suppliersPagination');
            if (pagDiv) pagDiv.innerHTML = '';
            return;
        } else {
            const noResults = document.getElementById('noSuppliersFound');
            if (noResults) noResults.style.display = 'none';
        }
        
        for (let i = startIndex; i < endIndex; i++) {
            const supplier = filteredSuppliers[i];
            const cardHtml = renderSupplierCard(supplier, i);
            container.insertAdjacentHTML('beforeend', cardHtml);
        }
        
        const infoSpan = document.getElementById('supplierPaginationInfo');
        if (infoSpan) {
            infoSpan.textContent = `Showing ${startIndex + 1} - ${endIndex} of ${filteredSuppliers.length} suppliers`;
        }
        
        const totalPages = Math.ceil(filteredSuppliers.length / suppliersPerPage);
        renderSupplierPagination(totalPages, page);
        
        if (window.lucide) lucide.createIcons();
    }
    
    function goToSupplierPage(page) {
        displaySuppliersPage(page);
    }

    function filterSuppliers() {
        const input = document.getElementById('supplierSearch');
        const term = input ? input.value.toLowerCase().trim() : '';
        
        if (term === '') {
            filteredSuppliers = [...suppliersList];
        } else {
            filteredSuppliers = suppliersList.filter(s => {
                return s.name.toLowerCase().includes(term) ||
                       s.contact_person.toLowerCase().includes(term) ||
                       s.contact_phone.toLowerCase().includes(term) ||
                       s.delivery_person.toLowerCase().includes(term) ||
                       s.delivery_phone.toLowerCase().includes(term) ||
                       s.phone.toLowerCase().includes(term) ||
                       s.email.toLowerCase().includes(term) ||
                       s.address.toLowerCase().includes(term) ||
                       s.desc.toLowerCase().includes(term);
            });
        }
        
        displaySuppliersPage(1);
    }

    function populateSupplierForm(name, contact_person, contact_phone, phone, email, address, desc) {
        document.getElementById('supplierFormTitle').innerText = 'Update Supplier';
        document.getElementById('supplierNameInput').value = name;
        document.getElementById('supplierNameInput').readOnly = true;
        document.getElementById('supplierNameInput').style.background = '#f8fafc';
        document.getElementById('supplierContactPersonInput').value = contact_person || '';
        document.getElementById('supplierContactPhoneInput').value = contact_phone || '';

        document.getElementById('supplierPhoneInput').value = phone;
        document.getElementById('supplierEmailInput').value = email;
        document.getElementById('supplierAddressInput').value = address;
        document.getElementById('supplierDescInput').value = desc;

        document.getElementById('supplierSubmitText').innerText = 'Update';
        document.getElementById('supplierSubmitBtn').style.background = 'linear-gradient(135deg, #881337, #3730a3)';
        document.getElementById('supplierResetBtn').style.display = 'block';

        const icon = document.getElementById('supplierSubmitIcon');
        icon.setAttribute('data-lucide', 'refresh-cw');
        if (window.lucide) lucide.createIcons();

        document.getElementById('suppliers-registry').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function resetSupplierForm() {
        document.getElementById('supplierFormTitle').innerText = 'Add Supplier';
        document.getElementById('supplierForm').reset();
        document.getElementById('supplierNameInput').readOnly = false;
        document.getElementById('supplierNameInput').style.background = '';

        document.getElementById('supplierSubmitText').innerText = 'Add Supplier';
        document.getElementById('supplierSubmitBtn').style.background = '#881337';
        document.getElementById('supplierResetBtn').style.display = 'none';

        const icon = document.getElementById('supplierSubmitIcon');
        icon.setAttribute('data-lucide', 'plus-circle');
        if (window.lucide) lucide.createIcons();
    }

    function toggleCalendarPopover(event, btn) {
        event.stopPropagation();
        const popover = btn.nextElementSibling;
        const isVisible = popover.style.display === 'block';
        
        document.querySelectorAll('.calendar-popover').forEach(p => p.style.display = 'none');
        
        if (!isVisible) {
            const rect = btn.getBoundingClientRect();
            popover.style.top = `${rect.bottom + 6}px`;
            popover.style.left = `${rect.right - 216}px`;
            popover.style.display = 'block';
        }
    }

    window.addEventListener('scroll', () => {
        document.querySelectorAll('.calendar-popover').forEach(p => p.style.display = 'none');
    }, { passive: true });
    
    window.addEventListener('resize', () => {
        document.querySelectorAll('.calendar-popover').forEach(p => p.style.display = 'none');
    }, { passive: true });

    document.addEventListener('click', function(e) {
        const activePopover = document.querySelector('.calendar-popover[style*="display: block"]');
        if (activePopover) {
            const clickedInsidePopover = activePopover.contains(e.target);
            const clickedButton = e.target.closest('button') && e.target.closest('button').onclick && e.target.closest('button').onclick.toString().includes('toggleCalendarPopover');
            if (!clickedInsidePopover && !clickedButton) {
                activePopover.style.display = 'none';
            }
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        filterSuppliers();
    });
</script>
@endsection
