@extends('layouts.admin')

@section('title', 'Requisition Review')

@section('content')
<style>
    .review-container {
        padding: 2.5rem;
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
        box-sizing: border-box;
    }

    .back-nav-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.6rem 1.25rem;
        border-radius: 12px;
        background: var(--bg-card);
        border: 1.5px solid var(--border-color);
        color: var(--text-main);
        font-weight: 800;
        font-size: 0.85rem;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.015);
    }

    .back-nav-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateX(-4px);
    }

    /* Skeleton Loader Styles */
    .skeleton-wrapper {
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
    }

    .skeleton-shimmer {
        animation: skeletonPulse 1.6s infinite ease-in-out;
        background: linear-gradient(90deg, var(--border-color) 25%, var(--bg-main) 50%, var(--border-color) 75%);
        background-size: 200% 100%;
    }

    @keyframes skeletonPulse {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    .skeleton-header {
        height: 80px;
        border-radius: 20px;
        width: 100%;
    }

    .skeleton-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }

    .skeleton-card {
        height: 110px;
        border-radius: 16px;
    }

    .skeleton-banner {
        height: 140px;
        border-radius: 16px;
        grid-column: span 2;
    }

    .skeleton-table {
        height: 350px;
        border-radius: 20px;
        width: 100%;
    }

    /* Premium Layout CSS */
    .review-card {
        background: var(--bg-card);
        border-radius: 24px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-luxe);
        overflow: hidden;
        margin-top: 1rem;
        transition: border-color 0.3s ease;
    }

    .urgent-priority {
        border-top: 5px solid #dc2626 !important;
    }
    .medium-priority {
        border-top: 5px solid var(--store-orange) !important;
    }
    .normal-priority {
        border-top: 5px solid #3b82f6 !important;
    }

    .profile-card {
        background: var(--bg-main);
        border-radius: 18px;
        border: 1.5px solid var(--border-color);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: var(--transition);
    }

    .profile-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(99, 102, 241, 0.08);
        border: 1.5px solid rgba(99, 102, 241, 0.15);
        color: var(--primary);
        font-size: 1.3rem;
        font-weight: 950;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .purpose-quote {
        background: var(--bg-main);
        border-left: 4.5px solid var(--primary);
        padding: 1.15rem 1.35rem;
        border-radius: 0 14px 14px 0;
        font-size: 0.95rem;
        color: var(--text-main);
        font-weight: 600;
        font-style: italic;
        line-height: 1.6;
        box-shadow: inset 2px 2px 8px rgba(0,0,0,0.01);
    }

    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 99px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--text-main);
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .item-decision-card {
        border-bottom: 1.5px solid var(--border-color);
        padding: 1.5rem 2rem;
        transition: all 0.25s ease;
    }

    .item-decision-card:last-child {
        border-bottom: none;
    }

    .approved-row {
        background: rgba(5, 150, 105, 0.005);
    }

    .declined-row {
        background: rgba(239, 68, 68, 0.015);
    }

    .item-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
    }

    .item-card-header-left {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        flex: 1;
    }

    .switch-wrapper {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }

    .switch-input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .switch-label {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 34px;
    }

    .switch-label:before {
        position: absolute;
        content: "";
        height: 18px; width: 18px;
        left: 4px; bottom: 4px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }

    .switch-input:checked + .switch-label {
        background-color: #059669;
    }

    .switch-input:checked + .switch-label:before {
        transform: translateX(24px);
    }

    .qty-spinner {
        display: inline-flex;
        align-items: center;
        background: var(--bg-card);
        border: 1.5px solid var(--border-color);
        border-radius: 10px;
        overflow: hidden;
        padding: 2px;
    }

    .qty-btn {
        background: transparent;
        border: none;
        width: 32px;
        height: 32px;
        color: var(--text-main);
        font-weight: 800;
        cursor: pointer;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s;
    }

    .qty-btn:hover:not(:disabled) {
        background: var(--bg-main);
    }

    .approved-qty-input {
        border: none;
        background: transparent;
        width: 60px;
        text-align: center;
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--text-main);
        outline: none;
    }

    .approved-qty-input::-webkit-inner-spin-button,
    .approved-qty-input::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .item-card-panel {
        display: flex;
        gap: 1.5rem;
        margin-top: 1.25rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--border-color);
        align-items: center;
    }

    .item-card-spinner-box, .item-card-status-box {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .reason-input {
        width: 280px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-main);
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-family: inherit;
        font-size: 0.82rem;
        font-weight: 600;
        outline: none;
        box-sizing: border-box;
    }

    .reason-input:focus {
        border-color: var(--primary);
    }

    .quick-tag {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 6px;
        background: rgba(239, 68, 68, 0.05);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.15);
        font-size: 0.7rem;
        font-weight: 800;
        cursor: pointer;
        margin-top: 4px;
        transition: all 0.15s;
    }

    .quick-tag:hover {
        background: #ef4444;
        color: white;
    }

    .fulfill-ratio-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        background: rgba(5, 150, 105, 0.1);
        color: #059669;
    }

    .fulfill-ratio-badge.reduced {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
    }

    .fulfill-ratio-badge.declined {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .fulfill-progress-container {
        height: 6px;
        background: var(--border-color);
        border-radius: 10px;
        overflow: hidden;
        margin-top: 6px;
    }

    .fulfill-progress-bar {
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s ease;
    }

    /* Summary Dashboard */
    .summary-dashboard {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--bg-main);
        border: 1.5px solid var(--border-color);
        border-radius: 18px;
        padding: 1.25rem 2rem;
        margin-bottom: 1.75rem;
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
        font-size: 1.4rem;
        font-weight: 950;
    }

    .metric-lbl {
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-top: 2px;
        letter-spacing: 0.04em;
    }

    .summary-badge-status {
        padding: 6px 14px;
        border-radius: 99px;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Legend Indicator */
    .legend-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }

    .status-alert-bar {
        padding: 0.85rem 1.25rem;
        border-radius: 14px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 800;
        font-size: 0.88rem;
    }
</style>

<div class="review-container">
    {{-- Back navigation --}}
    <a href="{{ route('admin.requisitions') }}" class="back-nav-btn">
        <i data-lucide="arrow-left" style="width:16px;"></i>
        Back to Requisitions Command
    </a>

    {{-- 1. Loading Skeleton State --}}
    <div id="skeleton-loader" class="skeleton-wrapper">
        <div class="skeleton-shimmer skeleton-header"></div>
        <div class="skeleton-grid">
            <div class="skeleton-shimmer skeleton-card"></div>
            <div class="skeleton-shimmer skeleton-card"></div>
            <div class="skeleton-shimmer skeleton-banner"></div>
        </div>
        <div class="skeleton-shimmer skeleton-table"></div>
    </div>

    {{-- 2. Loaded Requisition Content Card --}}
    <div id="requisition-content" class="review-card" style="display: none;">
        {{-- Card Header --}}
        <div style="padding:1.75rem 2.25rem; border-bottom:1px solid var(--border-color); display:flex; align-items:center; justify-content:space-between; background:rgba(0,0,0,0.005);">
            <div style="display:flex; align-items:center; gap:1.25rem;">
                <div style="width:46px; height:46px; background:rgba(5, 150, 105, 0.08); border-radius:12px; display:flex; align-items:center; justify-content:center; border:1px solid rgba(5,150,105,0.12);">
                    <i data-lucide="clipboard-list" style="width:22px; color:#059669;"></i>
                </div>
                <div>
                    <h2 style="margin:0; font-size:1.25rem; font-weight:950; color:var(--text-main); letter-spacing:-0.02em;">Requisition Execution & Review</h2>
                    <p id="reqSubtitle" style="margin:0; font-size:.82rem; color:var(--text-muted); font-weight:700; margin-top:2px;"></p>
                </div>
            </div>
            <span class="pill" id="reqPriorityBadge" style="padding: 6px 14px; border-radius: 99px; font-weight: 800; font-size: 0.72rem; text-transform: uppercase;"></span>
        </div>

        {{-- Details Panel Body --}}
        <div style="padding: 2.25rem;" id="reviewBody">
            <!-- Render profile grid, items, and status panels -->
        </div>

        {{-- Footer Action Bar --}}
        <div id="reviewFooter" style="padding:1.5rem 2.25rem; border-top:1px solid var(--border-color); display:flex; justify-content:flex-end; gap:.85rem; background:rgba(0,0,0,0.005); align-items:center;">
            <!-- Buttons dynamically rendered here -->
        </div>
    </div>
</div>

<script>
    const currentReqId = {{ $id }};
    let currentReqData = null;

    document.addEventListener('DOMContentLoaded', () => {
        fetchRequisitionDetails();
    });

    async function fetchRequisitionDetails() {
        try {
            const res = await fetch(`{{ url('/admin/requisitions') }}/${currentReqId}/show`);
            const data = await res.json();
            if (!res.ok || !data || !data.items) {
                Swal.fire('Error', data?.message || 'Failed to load requisition details.', 'error').then(() => {
                    window.location.href = "{{ route('admin.requisitions') }}";
                });
                return;
            }
            currentReqData = data;
            renderRequisition(data);
        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'Network error. Failed to load requisition details.', 'error').then(() => {
                window.location.href = "{{ route('admin.requisitions') }}";
            });
        }
    }

    function renderRequisition(data) {
        // Apply priority border accents to main card
        const mainCard = document.getElementById('requisition-content');
        mainCard.classList.add(`${data.priority}-priority`);

        // Set subtitles & priority badge
        document.getElementById('reqSubtitle').textContent = `Requisition Reference Code: ${data.unique_id || ('REQ-' + String(data.id).padStart(5, '0'))}`;
        const pBadge = document.getElementById('reqPriorityBadge');
        pBadge.textContent = data.priority;
        if (data.priority === 'urgent') {
            pBadge.style.background = 'rgba(220, 38, 38, 0.1)';
            pBadge.style.color = '#dc2626';
            pBadge.style.border = '1px solid rgba(220, 38, 38, 0.2)';
        } else if (data.priority === 'medium') {
            pBadge.style.background = 'rgba(245, 158, 11, 0.1)';
            pBadge.style.color = 'var(--store-orange)';
            pBadge.style.border = '1px solid rgba(245, 158, 11, 0.2)';
        } else {
            pBadge.style.background = 'rgba(59, 130, 246, 0.1)';
            pBadge.style.color = '#3b82f6';
            pBadge.style.border = '1px solid rgba(59, 130, 246, 0.2)';
        }

        const isPending = (data.status === 'pending');
        const isAwaitingPriorApproval = isPending && (
            data.status_badge.label.includes('Awaiting Dept Head') ||
            data.status_badge.label.includes('Awaiting Authorizer') ||
            data.status_badge.label.includes('Awaiting DG')
        );

        // 1. Profile Grid HTML
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
            <div style="background:rgba(5, 150, 105, 0.06); border:1px solid rgba(5, 150, 105, 0.25); border-radius:12px; padding:0.85rem 1.15rem; display:flex; align-items:center; gap:10px; color:#047857; font-weight:800; font-size:0.88rem; margin-top:0.5rem; margin-bottom:0.25rem; box-shadow:0 2px 8px rgba(5, 150, 105, 0.03); width:100%;">
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
                        <i data-lucide="award" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:3px;"></i>${data.rank_or_title || 'No Rank/Title Specified'}
                    </div>
                </div>
            </div>
            <div class="profile-card">
                <div class="profile-avatar" style="background:rgba(5, 150, 105, 0.08); color:#059669; border-color:rgba(5,150,105,0.15);"><i data-lucide="building" style="width:20px;height:20px;"></i></div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:2px;letter-spacing:0.04em;">Requesting Department</div>
                    <div style="font-size:1.05rem;font-weight:900;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${data.department}">${data.department}</div>
                    <div style="font-size:.78rem;color:var(--text-muted);font-weight:600;margin-top:2px;">
                        <i data-lucide="calendar" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:3px;"></i>Submitted ${data.created_at}
                    </div>
                </div>
            </div>

            <div class="profile-card" style="grid-column: 1 / -1; display:flex; flex-direction:column; align-items:stretch; gap:0.75rem;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.04em;">Requisition Intention & Purpose</span>
                    <div style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
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

        // 2. Items List HTML
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

                const stockInfo = item.stock_sufficient ?
                    `<span style="color:#059669;font-size:.72rem;font-weight:800;display:inline-flex;align-items:center;gap:3px;"><i data-lucide="check-circle-2" style="width:12px;height:12px;"></i> Sufficient Stock (${parseFloat(item.current_stock).toLocaleString()} ${item.unit})</span>` :
                    `<span style="color:#059669;font-size:.72rem;font-weight:800;display:inline-flex;align-items:center;gap:3px;"><i data-lucide="alert-triangle" style="width:12px;height:12px;"></i> Critical Stock (${parseFloat(item.current_stock).toLocaleString()} ${item.unit})</span>`;

                const descTextHtml = isAltAgreed ?
                    `<span>${item.description}</span> <span style="color:var(--store-orange); font-weight:800; margin-left:6px;"><i data-lucide="shuffle" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:2px;"></i>Alternative: ${item.alternative_description}</span>` :
                    item.description;

                itemRowsHtml += `
                <div class="item-decision-card approved-row" id="item-row-${i}" data-index="${i}">
                    <div class="item-card-header">
                        <div class="item-card-header-left">
                            <div class="switch-wrapper">
                                <input type="checkbox" class="switch-input approve-toggle" id="chk-${i}"
                                    data-index="${i}"
                                    checked
                                    ${isAwaitingPriorApproval ? 'disabled' : ''}
                                    onchange="toggleItemApproval(${i})">
                                <label for="chk-${i}" class="switch-label" title="Toggle item approval" style="${isAwaitingPriorApproval ? 'cursor: not-allowed; opacity: 0.5;' : ''}"></label>
                            </div>
                            <div>
                                <div style="font-size:.95rem;font-weight:800;color:var(--text-main);" id="item-desc-text-${i}">${descTextHtml}</div>
                                <div style="margin-top:4px;">${stockInfo}</div>
                            </div>
                        </div>

                        <div class="item-card-header-right" style="text-align: right;">
                            <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;">Requested</div>
                            <div style="margin-top: 2px;">
                                <span style="font-size:1.15rem;font-weight:900;color:var(--text-main);">${parseFloat(item.quantity_requested).toLocaleString()}</span>
                                <span style="font-size:.78rem;color:var(--text-muted);font-weight:700;margin-left:2px;">${item.unit}</span>
                            </div>
                        </div>
                    </div>

                    <div class="item-card-panel" style="flex-wrap: wrap;">
                        <div class="item-card-spinner-box">
                            <span style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.02em;">Approved Allocation</span>
                            <div class="qty-spinner">
                                <button type="button" class="qty-btn" onclick="adjustQty(${i}, -1)" ${isAwaitingPriorApproval ? 'disabled' : ''}>−</button>
                                <input type="number" class="approved-qty-input"
                                    id="qty-${i}"
                                    data-item-id="${item.id}"
                                    data-requested="${parseFloat(item.quantity_requested)}"
                                    data-stock="${parseFloat(item.current_stock)}"
                                    data-index="${i}"
                                    value="${defaultOriginalApproved}"
                                    min="0" max="${parseFloat(item.quantity_requested)}" step="0.01"
                                    ${isAwaitingPriorApproval ? 'disabled' : ''}
                                    oninput="onQtyChange(${i})">
                                <button type="button" class="qty-btn" onclick="adjustQty(${i}, 1)" ${isAwaitingPriorApproval ? 'disabled' : ''}>+</button>
                            </div>
                        </div>

                        <div class="item-card-status-box" style="flex: 1; min-width: 250px;">
                            <span style="font-size:0.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.02em;">Evaluation & Notes</span>
                            <div>
                                <textarea class="reason-input item-reason"
                                    id="reason-${i}"
                                    data-index="${i}"
                                    placeholder="Remarks for decline or reduction..."
                                    rows="2"
                                    ${isAwaitingPriorApproval ? 'disabled' : ''}
                                    style="display:${isAltAgreed ? 'block' : 'none'}; width:100%; min-height: 48px; resize: none; margin-bottom: 6px;">${item.remarks||''}</textarea>

                                <span id="reason-ok-${i}" style="font-size:.78rem;color:#059669;font-weight:700;display:${isAltAgreed ? 'none' : 'inline-flex'};align-items:center;gap:4px;">
                                    <i data-lucide="check-circle" style="width:14px;height:14px;"></i> Approved Allocation
                                </span>

                                <div id="quick-tags-${i}" style="display:none;">
                                    <span class="quick-tag" onclick="fillQuickReason(${i}, 'Reduce Allocation')">Reduce Allocation</span>
                                </div>
                            </div>
                        </div>
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
                    `<span style="color:#059669;font-size:.7rem;font-weight:700;">✔ Sufficient</span>` :
                    `<span style="color:#ef4444;font-size:.7rem;font-weight:700;">⚠ Short Stock</span>`;

                return `
                <div class="item-decision-card ${totalApproved === 0 ? 'declined-row' : 'approved-row'}">
                    <div class="item-card-header">
                        <div class="item-card-header-left">
                            <div>
                                ${item.alternative_description ? `
                                    <div style="font-size:.95rem;font-weight:800;color:var(--text-main); display:flex; align-items:center; gap:6px;">
                                        <span>${item.description}</span>
                                        <span style="font-size:0.75rem; font-weight:800; color:#059669;">(Approved: ${approved.toLocaleString()} ${item.unit})</span>
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

                    <div class="item-card-panel" style="gap:1.5rem;">
                        <div style="flex:1; min-width:80px;">
                            <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;">Requested</div>
                            <div style="font-size:1.1rem;font-weight:800;color:var(--text-main);margin-top:2px;">${requested.toLocaleString()}</div>
                        </div>

                        <div style="flex:1; min-width:80px;">
                            <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;">Total Approved</div>
                            <div style="font-size:1.15rem;font-weight:900;color:${totalApproved === 0 ? '#ef4444' : '#059669'};margin-top:2px;">${totalApproved.toLocaleString()}</div>
                        </div>

                        <div style="flex:2; min-width:180px;">
                            <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;margin-bottom:6px;">Fulfillment Progress</div>
                            <div class="fulfill-progress-container" style="margin-top:0;">
                                <div class="fulfill-progress-bar" style="width: ${pct}%; background:${totalApproved === 0 ? '#ef4444' : '#059669'}"></div>
                            </div>
                        </div>
                    </div>

                    ${item.remarks ? `
                    <div style="background:rgba(0,0,0,0.015); border:1.5px dashed var(--border-color); border-radius:10px; padding:0.75rem 1rem; margin-top:0.75rem;">
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

        // 3. Physical Collection Panel
        let collectorInfoHtml = '';
        if (['approved', 'partially_approved'].includes(data.status)) {
            if (data.collected_at) {
                collectorInfoHtml = `
                <div style="background:rgba(5,150,105,0.03); border:1.5px dashed rgba(5,150,105,0.25); border-radius:16px; padding:1.25rem; margin-top:1.25rem; display:flex; flex-direction:column; gap:1rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:1px dashed rgba(5,150,105,0.15); padding-bottom:8px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="width:34px; height:34px; background:rgba(5,150,105,0.08); color:#059669; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                                <i data-lucide="package-check" style="width:16px;"></i>
                            </div>
                            <div>
                                <h4 style="margin:0; font-size:0.85rem; font-weight:800; color:var(--text-main); text-transform:uppercase; letter-spacing:0.04em;">Physical Collection Log</h4>
                                <p style="margin:0; font-size:0.75rem; color:var(--text-muted);">Items have been successfully issued and collected</p>
                            </div>
                        </div>
                        <span class="pill" style="background:rgba(5,150,105,0.1); color:#059669; font-weight:800; font-size:0.7rem; padding:4px 10px;">COLLECTED</span>
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
                <div style="background:rgba(5,150,105,0.03); border:1.5px dashed rgba(5,150,105,0.25); border-radius:16px; padding:1.25rem; margin-top:1.25rem; display:flex; align-items:center; gap:12px;">
                    <div style="width:34px; height:34px; background:rgba(5,150,105,0.08); color:#059669; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i data-lucide="clock" style="width:16px;"></i>
                    </div>
                    <div>
                        <h4 style="margin:0; font-size:0.85rem; font-weight:800; color:var(--text-main); text-transform:uppercase; letter-spacing:0.04em;">Awaiting Collection</h4>
                        <p style="margin:0; font-size:0.75rem; color:var(--text-muted);">This requisition is approved but physical collection has not yet been confirmed by store personnel.</p>
                    </div>
                </div>`;
            }
        }

        // 4. Decision Actions
        let readOnlyBtnHtml = '';
        if (!isPending) {
            if (data.status === 'approved' || data.status === 'partially_approved' || data.alternative_status === 'agreed') {
                readOnlyBtnHtml = `
                <div style="display: flex; gap: 0.75rem; margin-top: 1.25rem;">
                    <button style="flex:1; background: rgba(239, 68, 68, 0.08); color: #ef4444; border: 1.5px solid rgba(239, 68, 68, 0.18); padding: 0.75rem; border-radius: 12px; font-weight: 800; cursor: not-allowed; opacity: 0.45; pointer-events: none; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem;" disabled title="Decision already processed">
                        <i data-lucide="x-circle" style="width: 18px;"></i>
                        Decline Request
                    </button>
                    <button style="flex:1.5; background: #059669; color: white; border: none; padding: 0.75rem; border-radius: 12px; font-weight: 900; cursor: not-allowed; opacity: 0.9; pointer-events: none; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem; box-shadow: 0 4px 14px rgba(5, 150, 105, 0.2);" disabled title="Requisition already approved">
                        <i data-lucide="check-circle" style="width: 18px;"></i>
                        Approve (Processed)
                    </button>
                </div>`;
            } else {
                readOnlyBtnHtml = `
                <div style="display: flex; gap: 0.75rem; margin-top: 1.25rem;">
                    <button style="flex:1.5; background: #ef4444; color: white; border: none; padding: 0.75rem; border-radius: 12px; font-weight: 900; cursor: not-allowed; opacity: 0.9; pointer-events: none; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem;" disabled title="Requisition already declined">
                        <i data-lucide="x-circle" style="width: 18px;"></i>
                        Decline Request (Processed)
                    </button>
                    <button style="flex:1; background: rgba(5, 150, 105, 0.08); color: #059669; border: 1.5px solid rgba(5, 150, 105, 0.18); padding: 0.75rem; border-radius: 12px; font-weight: 800; cursor: not-allowed; opacity: 0.45; pointer-events: none; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem;" disabled title="Decision already processed">
                        <i data-lucide="check-circle" style="width: 18px;"></i>
                        Approve
                    </button>
                </div>`;
            }
        }

        const detailsHtml = `
        <!-- Profile Grid -->
        ${profileGridHtml}

        <!-- Stock Warning Banner -->
        <div id="stockWarningBanner" style="display:none; background:#ecfdf5; border:1px solid #fef3c7; border-left:4px solid #ef4444; padding:12px 16px; border-radius:12px; margin-bottom:1.5rem; align-items:center; gap:12px;">
            <div style="width:32px; height:32px; background:rgba(239, 68, 68, 0.08); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#ef4444; flex-shrink:0;">
                <i data-lucide="alert-triangle" style="width:20px;"></i>
            </div>
            <div style="flex:1;">
                <h4 style="margin:0; font-size:0.85rem; font-weight:800; color:#991b1b; text-transform:uppercase;">Insufficient Stock Blocked</h4>
                <p style="margin:0; font-size:0.75rem; color:#b91c1c; font-weight:600;">One or more items have approved allocations exceeding the available stock in the system. Reduce their quantity or select alternative items to proceed.</p>
            </div>
        </div>

        <!-- Section Title -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; margin-top:1.5rem;">
            <h3 style="margin:0; font-size:0.95rem; font-weight:900; color:var(--text-main); letter-spacing:-0.01em; display:flex; align-items:center; gap:6px;">
                <i data-lucide="list-checks" style="width:16px; color:var(--primary);"></i> Requested Items List
            </h3>
            <span style="font-size:.72rem; color:var(--text-muted); font-weight:700;">Please review stock indicators before committing decisions.</span>
        </div>

        ${isPending ? `
        <!-- Live status bar -->
        <div id="statusBar" class="all-approved status-alert-bar" style="background:rgba(5, 150, 105, .12); color:#065f46; border:1px solid rgba(5,150,105,0.25);">
            <span id="statusBarIcon">✅</span>
            <span id="statusBarText">All items will be <b>Approved</b></span>
        </div>

        <!-- Legend -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.85rem; font-size:.72rem; font-weight:700; color:var(--text-muted); background:var(--bg-main); padding:0.6rem 1rem; border-radius:10px; border:1.5px solid var(--border-color);">
            <div style="display:flex; gap:1.25rem;">
                <span><span class="legend-dot" style="background:#059669;"></span>Active = Approve item</span>
                <span><span class="legend-dot" style="background:#cbd5e1;"></span>Inactive = Decline item</span>
            </div>
            <span style="color:var(--primary);"><i data-lucide="info" style="width:12px; display:inline-block; vertical-align:middle; margin-right:3px;"></i>Use controls to adjust approved quantities</span>
        </div>` : ''}

        <!-- Item Rows -->
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:16px; overflow:hidden; margin-bottom:1.75rem;">
            ${itemRowsHtml}
        </div>

        ${isPending ? `
        <!-- Live summary board -->
        <div class="summary-dashboard" id="summaryDashboard">
            <div class="summary-metrics">
                <div class="metric-box">
                    <span class="metric-val" id="metricApproved" style="color:#059669;">0</span>
                    <span class="metric-lbl">Approved</span>
                </div>
                <div class="metric-box">
                    <span class="metric-val" id="metricReduced" style="color:#059669;">0</span>
                    <span class="metric-lbl">Reduced</span>
                </div>
                <div class="metric-box">
                    <span class="metric-val" id="metricDeclined" style="color:#ef4444;">0</span>
                    <span class="metric-lbl">Declined</span>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:0.65rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em;">Overall Decision Action</div>
                <span class="pill summary-badge-status" id="summaryActionBadge" style="margin-top:4px; display: inline-block;">PENDING REVIEW</span>
            </div>
        </div>

        <!-- Notes -->
        <div>
            <label style="display:block; font-size:.68rem; font-weight:900; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px; display:flex; align-items:center; gap:4px;">
                <i data-lucide="message-square" style="width:14px; color:var(--text-muted);"></i> General Requisition Notes (Add notes visible to the requester...)
            </label>
            <textarea id="adminNotes" rows="3" placeholder="Add central store decision notes, pickup directions, or overall remarks here..." style="width:100%; padding:.85rem 1rem; border:1.5px solid var(--border-color); border-radius:12px; font-family:inherit; font-size:.88rem; background:var(--bg-main); color:var(--text-main); resize:vertical; box-sizing:border-box; transition:all 0.25s ease;" onfocus="this.style.borderColor='var(--primary)'; this.style.background='var(--bg-card)';">${data.admin_notes||''}</textarea>
        </div>

        <!-- Decline Reason Box -->
        <div id="declineReasonBox" style="display:none; margin-top:1rem; background:rgba(239,68,68,0.03); border:1.5px solid rgba(239,68,68,0.25); border-radius:12px; padding:1rem 1.25rem;">
            <label style="display:flex; align-items:center; gap:6px; font-size:.68rem; font-weight:900; color:#dc2626; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">
                <i data-lucide="alert-circle" style="width:14px;"></i> Reason for Declining (required when declining entire requisition)
            </label>
            <textarea id="declineReason" rows="3" placeholder="State the reason for declining this requisition..." style="width:100%; padding:.85rem 1rem; border:1.5px solid rgba(239,68,68,0.3); border-radius:10px; font-family:inherit; font-size:.88rem; background:var(--bg-card); color:var(--text-main); resize:vertical; box-sizing:border-box; transition:all 0.25s ease;" onfocus="this.style.borderColor='#ef4444';" onblur="this.style.borderColor='rgba(239,68,68,0.3)'"></textarea>
        </div>` : `
        ${data.admin_notes?`<div style="background:rgba(5,150,105,.03); border:1px solid rgba(5,150,105,.15); border-radius:16px; padding:1.25rem; margin-top:1rem;"><div style="font-size:.68rem; font-weight:900; color:var(--primary); text-transform:uppercase; letter-spacing:0.05em; display:flex; align-items:center; gap:4px; margin-bottom:4px;"><i data-lucide="message-square" style="width:14px;"></i> Store Officer Notes</div><p style="margin:0; font-size:.9rem; color:var(--text-main); line-height:1.6; font-style:italic;">"${data.admin_notes}"</p></div>`:''}

        ${data.status === 'declined' && data.decline_reason ? `
        <div style="background:rgba(239,68,68,0.04); border:1px solid rgba(239,68,68,0.2); border-radius:16px; padding:1.25rem; margin-top:0.75rem;">
            <div style="font-size:.68rem; font-weight:900; color:#dc2626; text-transform:uppercase; letter-spacing:0.05em; display:flex; align-items:center; gap:4px; margin-bottom:6px;">
                <i data-lucide="alert-circle" style="width:14px;"></i> Reason for Decline
            </div>
            <p style="margin:0; font-size:.9rem; color:#7f1d1d; line-height:1.6;">${data.decline_reason}</p>
        </div>` : ''}

        <div style="background:var(--bg-main); border:1px solid var(--border-color); border-radius:16px; padding:1.15rem; margin-top:1.25rem; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:34px; height:34px; background:rgba(5,150,105,0.08); color:var(--primary); border-radius:10px; display:flex; align-items:center; justify-content:center;">
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

        ${readOnlyBtnHtml}
        ${collectorInfoHtml}
        `}`;

        // Populate Body
        document.getElementById('reviewBody').innerHTML = detailsHtml;

        // Populate Footer Actions
        let footerHtml = `
        <a href="{{ route('admin.requisitions') }}" class="back-nav-btn" style="margin: 0;">
            Close and Return
        </a>`;

        if (isPending) {
            if (isAwaitingPriorApproval) {
                footerHtml = `
                <button id="submitDecisionBtn" disabled
                    style="background:#94a3b8; color:white; border:none; padding:.75rem 2.25rem; border-radius:12px; font-weight:800; cursor:not-allowed; display:flex; align-items:center; gap:8px; font-size:.88rem;" title="Cannot commit decision: requisition is still awaiting prior approvals.">
                    <i data-lucide="send" style="width:16px;"></i> Approve for final Collection
                </button>` + footerHtml;
            } else {
                footerHtml = `
                <button onclick="submitDecision()" id="submitDecisionBtn"
                    style="background:#059669; color:white; border:none; padding:.75rem 2.25rem; border-radius:12px; font-weight:800; cursor:pointer; display:flex; align-items:center; gap:8px; font-size:.88rem; box-shadow:0 8px 20px rgba(5, 150, 105, 0.25); transition:0.2s;" onmouseover="this.style.background='#065f46'" onmouseout="this.style.background='#059669'">
                    <i data-lucide="send" style="width:16px;"></i> Approve for final Collection
                </button>` + footerHtml;
            }
        } else if (data.collected_at) {
            footerHtml = `
            <a href="{{ url('/requisitions/receipt') }}/${data.id}" target="_blank"
                style="background:rgba(5, 150, 105, 0.08); border: 1.5px solid rgba(5, 150, 105, 0.2); color: #059669; padding: .75rem 1.5rem; border-radius: 12px; font-weight: 800; cursor: pointer; font-size: .88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; margin-right: auto;" onmouseover="this.style.background='#059669'; this.style.color='white';" onmouseout="this.style.background='rgba(5, 150, 105, 0.08)'; this.style.color='#059669';">
                <i data-lucide="printer" style="width: 16px;"></i> Print Collection Receipt
            </a>` + footerHtml;
        }

        document.getElementById('reviewFooter').innerHTML = footerHtml;

        // Hide Skeleton, Reveal Content
        document.getElementById('skeleton-loader').style.display = 'none';
        document.getElementById('requisition-content').style.display = 'block';

        if (isPending) {
            updateStatusBar();
        }

        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    // Spinner adjustments
    window.adjustQty = function(idx, dir) {
        const qtyInput = document.getElementById(`qty-${idx}`);
        if (!qtyInput || qtyInput.disabled) return;
        let currentVal = parseFloat(qtyInput.value) || 0;
        let newVal = currentVal + dir;
        if (newVal < 0) newVal = 0;
        const requested = parseFloat(qtyInput.dataset.requested) || 0;
        if (newVal > requested) newVal = requested;
        qtyInput.value = parseFloat(newVal.toFixed(2));
        onQtyChange(idx);
    };

    window.fillQuickReason = function(idx, text) {
        const reasonInput = document.getElementById(`reason-${idx}`);
        if (reasonInput) {
            reasonInput.value = text;
        }
    };

    window.toggleItemApproval = function(idx) {
        const chk = document.getElementById(`chk-${idx}`);
        const row = document.getElementById(`item-row-${idx}`);
        const qtyInput = document.getElementById(`qty-${idx}`);
        const reasonInput = document.getElementById(`reason-${idx}`);
        const reasonOk = document.getElementById(`reason-ok-${idx}`);
        const quickTags = document.getElementById(`quick-tags-${idx}`);

        if (chk.checked) {
            row.className = 'item-decision-card approved-row';
            qtyInput.disabled = false;
            qtyInput.parentElement.style.opacity = '1';
            reasonInput.style.display = 'none';
            reasonOk.style.display = 'inline-flex';
            quickTags.style.display = 'none';
            if (parseFloat(qtyInput.value) === 0) {
                qtyInput.value = qtyInput.dataset.requested;
            }
        } else {
            row.className = 'item-decision-card declined-row';
            qtyInput.value = 0;
            qtyInput.disabled = true;
            qtyInput.parentElement.style.opacity = '.4';
            reasonInput.style.display = 'block';
            reasonOk.style.display = 'none';
            quickTags.style.display = 'block';
        }
        updateStatusBar();
    };

    window.onQtyChange = function(idx) {
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
    };

    window.updateStatusBar = function() {
        const checkboxes = document.querySelectorAll('.approve-toggle');

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

                const row = document.getElementById(`item-row-${i}`);
                if (approvedQty > stockLimit) {
                    hasExceededStock = true;
                    if (row) {
                        row.style.borderColor = '#ef4444';
                        row.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
                    }

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
                summaryBadge.style.background = 'rgba(5,150,105,0.1)';
                summaryBadge.style.color = '#059669';
                summaryBadge.textContent = '✅ Full Approval';
            } else {
                summaryBadge.style.background = 'rgba(5,150,105,0.1)';
                summaryBadge.style.color = '#059669';
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
                const isAwaitingPriorApproval = currentReqData && currentReqData.status_badge && (
                    currentReqData.status_badge.label.includes('Awaiting Dept Head') ||
                    currentReqData.status_badge.label.includes('Awaiting Authorizer') ||
                    currentReqData.status_badge.label.includes('Awaiting DG')
                );
                if (isAwaitingPriorApproval) {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.55';
                    submitBtn.style.cursor = 'not-allowed';
                    submitBtn.title = 'Cannot commit decision: requisition is still awaiting prior approvals.';
                    submitBtn.innerHTML = `<i data-lucide="send" style="width:16px;"></i> Approve for final Collection`;
                } else {
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.style.cursor = 'pointer';
                    submitBtn.title = '';
                    submitBtn.innerHTML = `<i data-lucide="send" style="width:16px;"></i> Approve for final Collection`;
                    submitBtn.style.background = '#059669';
                    submitBtn.style.boxShadow = '0 8px 20px rgba(5, 150, 105, 0.25)';
                    submitBtn.onmouseover = function() { this.style.background = '#065f46'; };
                    submitBtn.onmouseout = function() { this.style.background = '#059669'; };
                }
            }
        }

        const bar = document.getElementById('statusBar');
        const icon = document.getElementById('statusBarIcon');
        const text = document.getElementById('statusBarText');
        if (!bar) return;

        const allDeclined = !anyApproved;

        if (hasExceededStock) {
            bar.className = 'all-declined status-alert-bar';
            bar.style.background = 'rgba(239,68,68,.1)';
            bar.style.color = '#991b1b';
            bar.style.border = '1px solid rgba(239,68,68,.2)';
            icon.textContent = '⛔';
            text.innerHTML = 'Approval blocked — <b>allocation exceeds available stock</b>';
        } else if (allDeclined) {
            bar.className = 'all-declined status-alert-bar';
            bar.style.background = 'rgba(239,68,68,.1)';
            bar.style.color = '#991b1b';
            bar.style.border = '1px solid rgba(239,68,68,.2)';
            icon.textContent = '❌';
            text.innerHTML = 'All items will be <b>Declined</b>';
        } else if (allApproved && !anyPartial && cntReduced === 0) {
            bar.className = 'all-approved status-alert-bar';
            bar.style.background = 'rgba(5,150,105,.12)';
            bar.style.color = '#065f46';
            bar.style.border = '1px solid rgba(5,150,105,.25)';
            icon.textContent = '✅';
            text.innerHTML = 'All items will be <b>Approved</b>';
        } else {
            bar.className = 'partial status-alert-bar';
            bar.style.background = 'rgba(5,150,105,.12)';
            bar.style.color = '#92400e';
            bar.style.border = '1px solid rgba(5,150,105,.25)';
            icon.textContent = '⚠️';
            text.innerHTML = 'Some items differ — will be <b>Partially Approved</b>';
        }

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
    };

    window.computeStatus = function() {
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
    };

    window.submitDecision = async function() {
        const isAwaitingPriorApproval = currentReqData && currentReqData.status_badge && (
            currentReqData.status_badge.label.includes('Awaiting Dept Head') ||
            currentReqData.status_badge.label.includes('Awaiting Authorizer') ||
            currentReqData.status_badge.label.includes('Awaiting DG')
        );
        if (isAwaitingPriorApproval) {
            showToast('Action Blocked', 'Cannot commit decision: requisition is still awaiting prior approvals.', 'error');
            return;
        }
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

        let finalStatus = status;
        let altStatus = null;

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
        btn.innerHTML = '<div style="width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;display:inline-block;vertical-align:middle;margin-right:6px;"></div> Processing Decision...';

        try {
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
                if (typeof window.playNotificationSound === 'function') {
                    window.playNotificationSound('sent');
                }
                showToast('Success', data.message, 'success');
                setTimeout(() => {
                    window.location.href = "{{ route('admin.requisitions') }}";
                }, 1500);
            } else {
                showToast('Error', data.message || 'Failed to process decision.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="send" style="width:16px;"></i> Approve for final Collection';
                lucide.createIcons();
            }
        } catch (e) {
            console.error(e);
            showToast('Error', 'Network error. Please try again.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="send" style="width:16px;"></i> Approve for final Collection';
            lucide.createIcons();
        }
    };
</script>
@endsection
