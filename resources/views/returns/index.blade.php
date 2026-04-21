@extends('layouts.dashboard')

@section('content')
    
    <!-- Premium Returns Header -->
    <div class="glass-card header-mesh" style="padding: 3.5rem; border-radius: 32px; margin-bottom: 3rem; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.4); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.08);">
        <div style="position: absolute; top: -50px; right: -50px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(245, 158, 11, 0.1) 0%, transparent 70%); z-index: 0;"></div>
        
        <div style="position: relative; z-index: 1;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <span class="status-badge-premium">
                    <i data-lucide="refresh-cw" style="width: 12px;"></i>
                    Stock Recovery Node
                </span>
                <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="shield" style="width: 14px; color: #f59e0b;"></i> Audit Verified
                </span>
            </div>
            <h1 style="margin: 0; font-size: 3.5rem; font-weight: 950; color: var(--text-main); letter-spacing: -0.06em; line-height: 1;">Return <span style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Registry</span></h1>
            <p style="margin: 15px 0 0; color: var(--text-muted); font-size: 1.15rem; font-weight: 600; max-width: 600px; line-height: 1.6;">Re-integrate issued assets back into the primary logistics store. Monitor outstanding allocations in real-time.</p>
        </div>
    </div>

    <!-- Interface Workspace -->
    <div class="glass-card" style="border-radius: 32px; padding: 3rem; border: 1px solid var(--border-color); min-height: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 4rem;" class="returns-controls">
            <div>
                <h3 style="margin: 0; font-size: 1.75rem; font-weight: 950; color: var(--text-main); letter-spacing: -0.02em;">Outstanding Allocations</h3>
                <p style="margin: 6px 0 0; color: var(--text-muted); font-size: 0.95rem; font-weight: 600;">Manage recovery for all active departmental holdings</p>
            </div>
            
            <div class="search-container-premium">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="returnSearch" placeholder="Filter by personnel or item..." oninput="filterReturns()">
                <div class="search-accent"></div>
            </div>
        </div>

        @if($issuedItems->count() > 0)
        <!-- Desktop Table View -->
        <div class="desktop-only">
            <div class="table-scroll-wrapper" style="width: 100%; padding-bottom: 1.5rem;">
                <table style="width: 100%; border-collapse: separate; border-spacing: 0 1.25rem;">
                    <thead>
                        <tr style="text-align: left; color: var(--text-muted); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.12em; font-weight: 950;">
                            <th style="padding: 0 1.5rem 0.75rem;">Timelog</th>
                            <th style="padding: 0 1.5rem 0.75rem;">Recipient / Holder</th>
                            <th style="padding: 0 1.5rem 0.75rem;">Asset Description</th>
                            <th style="padding: 0 1.5rem 0.75rem;">Classification</th>
                            <th style="padding: 0 1.5rem 0.75rem;">Balance</th>
                            <th style="padding: 0 1.5rem 0.75rem; text-align: right;">Operations</th>
                        </tr>
                    </thead>
                    <tbody id="returnsTableBody">
                        @foreach($issuedItems as $item)
                        @if($item->quantity > 0)
                        <tr class="return-row" data-search="{{ strtolower($item->beneficiary . ' ' . $item->description) }}">
                            <td style="padding: 1.75rem 1.5rem; border-radius: 20px 0 0 20px; font-weight: 700; color: var(--text-muted);">
                                {{ date('M d, Y', strtotime($item->issuance_date)) }}
                            </td>
                            <td style="padding: 1.75rem 1.5rem;">
                                <div style="font-weight: 850; color: var(--text-main); font-size: 1.05rem;">{{ $item->beneficiary }}</div>
                            </td>
                            <td style="padding: 1.75rem 1.5rem;">
                                <div style="font-weight: 900; color: #f59e0b; font-size: 1.1rem; letter-spacing: -0.01em;">{{ $item->description }}</div>
                            </td>
                            <td style="padding: 1.75rem 1.5rem;">
                                <span class="ledge-badge-premium">Ledge {{ $item->ledge_category }}</span>
                            </td>
                            <td style="padding: 1.75rem 1.5rem;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-weight: 950; font-size: 1.4rem; color: var(--text-main);">{{ $item->quantity }}</span>
                                    <span style="color: var(--text-muted); font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">Units</span>
                                </div>
                            </td>
                            <td style="padding: 1.75rem 1.5rem; border-radius: 0 20px 20px 0; text-align: right;">
                                <button onclick="openReturnModal({{ json_encode($item) }})" class="recover-btn-premium">
                                    <i data-lucide="corner-up-left"></i>
                                    <span>Process Return</span>
                                </button>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="mobile-only">
            <div style="display: grid; gap: 1.5rem;" id="returnsMobileBody">
                @foreach($issuedItems as $item)
                @if($item->quantity > 0)
                <div class="return-card-mobile" data-search="{{ strtolower($item->beneficiary . ' ' . $item->description) }}">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                        <span class="ledge-badge-premium">Ledge {{ $item->ledge_category }}</span>
                        <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 700;">{{ date('d/m/y', strtotime($item->issuance_date)) }}</span>
                    </div>
                    <h4 style="margin: 0; color: var(--text-main); font-size: 1.2rem; font-weight: 900;">{{ $item->description }}</h4>
                    <p style="margin: 4px 0 1.5rem; color: var(--text-muted); font-weight: 700; font-size: 0.95rem;">Recipient: {{ $item->beneficiary }}</p>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                        <div>
                            <div style="font-size: 0.65rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Balance</div>
                            <div style="font-size: 1.5rem; font-weight: 950; color: var(--text-main);">{{ $item->quantity }}</div>
                        </div>
                        <button onclick="openReturnModal({{ json_encode($item) }})" class="mobile-recover-btn">
                            <i data-lucide="corner-up-left"></i>
                        </button>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @else
        <div style="padding: 10rem 0; text-align: center;">
            <div style="position: relative; display: inline-block; margin-bottom: 2.5rem;">
                <i data-lucide="package-check" style="width: 100px; height: 100px; color: var(--primary); opacity: 0.08;"></i>
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                    <i data-lucide="refresh-cw" style="width: 40px; height: 40px; color: var(--primary); opacity: 0.2;"></i>
                </div>
            </div>
            <h3 style="font-weight: 950; color: var(--text-main); font-size: 1.5rem;">Clean Ledger State</h3>
            <p style="color: var(--text-muted); font-weight: 600;">All issued assets have been accounted for and returned.</p>
        </div>
        @endif
    </div>
</div>

<!-- Return Overlay Modal -->
<div id="returnModal" class="modal-backdrop-premium" onclick="handleOutsideClick(event)">
    <div class="glass-card modal-container-premium animate-pop-in">
        <div class="modal-header-premium">
            <div>
                <h3 style="margin: 0; font-size: 1.75rem; font-weight: 950; color: var(--text-main); letter-spacing: -0.02em;">Process Recovery</h3>
                <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 0.9rem; font-weight: 600;">Confirming stock re-integration into inventory.</p>
            </div>
            <button onclick="closeReturnModal()" class="close-btn-premium">
                <i data-lucide="x"></i>
            </button>
        </div>
        
        <form action="{{ route('returns.store') }}" method="POST">
            @csrf
            <input type="hidden" name="issued_item_id" id="modal_item_id">
            
            <div class="modal-content-premium">
                <div class="item-preview-card">
                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div class="item-icon-circle">
                            <i data-lucide="box"></i>
                        </div>
                        <div>
                            <div id="modal_item_desc" style="font-size: 1.2rem; font-weight: 950; color: var(--text-main);"></div>
                            <div id="modal_item_beneficiary" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 700;"></div>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 2rem;">
                    <div class="form-group-premium">
                        <label>Quantity to Recover Control</label>
                        <div class="stepper-component-premium">
                            <button type="button" onclick="adjustStepper(-1)" class="stepper-btn">
                                <i data-lucide="minus"></i>
                            </button>
                            <div style="flex: 1; position: relative;">
                                <input type="number" name="return_qty" id="modal_return_qty" min="1" required class="premium-qty-input-stepper" oninput="validateReturnQty()">
                                <span class="qty-max-label-stepper">/ <span id="modal_max_qty">0</span></span>
                            </div>
                            <button type="button" onclick="adjustStepper(1)" class="stepper-btn">
                                <i data-lucide="plus"></i>
                            </button>
                        </div>
                        
                        <!-- Premium Error State -->
                        <div id="qty_error_banner" class="error-banner-premium">
                            <div class="error-icon-box">
                                <i data-lucide="alert-octagon" style="width: 16px;"></i>
                            </div>
                            <div>
                                <div style="font-weight: 900; font-size: 0.75rem;">Limit Exceeded</div>
                                <div style="font-size: 0.65rem; opacity: 0.8; font-weight: 700;">Value cannot surpass outstanding registry balance.</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group-premium">
                        <label>Recovery Date</label>
                        <input type="date" name="return_date" value="{{ date('Y-m-d') }}" required class="premium-date-input">
                        <div style="margin-top: 10px;">
                            <button type="button" onclick="setFullReturn()" class="shortcut-btn-premium">
                                <i data-lucide="check-square"></i> 
                                Set to Balance Max
                            </button>
                        </div>
                    </div>
                </div>

                <div class="audit-warning">
                    <i data-lucide="info" style="width: 20px; color: #f59e0b;"></i>
                    <p>This action will automatically update current stock balances and mark this allocation as returned.</p>
                </div>
            </div>

            <div class="modal-footer-premium">
                <button type="button" onclick="closeReturnModal()" class="modern-btn-cancel">
                    <i data-lucide="x-circle"></i>
                    Discard
                </button>
                <button type="submit" id="submit_return_btn" class="modern-btn-finalize">
                    <i data-lucide="check-circle"></i>
                    Finalize Return
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .header-mesh {
        background: radial-gradient(at 0% 0%, rgba(245, 158, 11, 0.06) 0, transparent 50%),
                    var(--bg-card);
        backdrop-filter: blur(24px);
    }

    .status-badge-premium {
        background: rgba(245, 158, 11, 0.1); color: #f59e0b; font-size: 0.7rem; font-weight: 950;
        padding: 0.5rem 1.25rem; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.1em;
        display: flex; align-items: center; gap: 8px; border: 1px solid rgba(245, 158, 11, 0.1);
    }

    .search-container-premium {
        position: relative; width: 400px;
    }
    .search-icon {
        position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%);
        width: 20px; color: var(--text-muted); z-index: 2; transition: 0.3s;
    }
    .search-container-premium input {
        width: 100%; padding: 1.15rem 1.5rem 1.15rem 3.5rem; border-radius: 20px;
        border: 2px solid var(--border-color); background: var(--bg-main);
        color: var(--text-main); font-weight: 700; font-size: 1rem;
        outline: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative; z-index: 1;
    }
    .search-container-premium:focus-within input {
        border-color: #f59e0b; background: var(--bg-card);
        box-shadow: 0 15px 30px rgba(245, 158, 11, 0.08);
    }
    .search-container-premium:focus-within .search-icon { color: #f59e0b; }

    .return-row {
        background: var(--bg-card); border-radius: 20px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01), 0 2px 4px -1px rgba(0,0,0,0.01);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        cursor: pointer;
    }
    .return-row:hover {
        transform: scale(1.008) translateY(-4px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.06);
        border-color: rgba(245, 158, 11, 0.2);
    }

    .ledge-badge-premium {
        background: var(--bg-main); color: var(--text-muted);
        padding: 0.45rem 1rem; border-radius: 12px; font-size: 0.72rem;
        font-weight: 850; border: 1px solid var(--border-color);
        letter-spacing: 0.02em;
    }

    .recover-btn-premium {
        padding: 0.85rem 1.5rem; border-radius: 14px; border: none;
        background: rgba(245, 158, 11, 0.08); color: #f59e0b;
        font-weight: 850; font-size: 0.85rem; cursor: pointer;
        display: inline-flex; align-items: center; gap: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .recover-btn-premium:hover {
        background: #f59e0b; color: white;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(245, 158, 11, 0.25);
    }
    .recover-btn-premium i { width: 16px; transition: 0.3s; }
    .recover-btn-premium:hover i { transform: rotate(-45deg) scale(1.1); }

    /* Modal Styling */
    .modal-backdrop-premium {
        position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(12px);
        z-index: 2000; display: none; align-items: center; justify-content: center; opacity: 0; transition: 0.4s;
    }
    .modal-backdrop-premium.active { display: flex; opacity: 1; }

    .modal-container-premium {
        width: 550px; max-width: 95%; border-radius: 36px; padding: 0; overflow: hidden;
        border: 1px solid rgba(255,255,255,0.4); box-shadow: 0 40px 100px rgba(0,0,0,0.4);
    }
    .modal-header-premium {
        padding: 3rem 3rem 2rem; display: flex; justify-content: space-between; align-items: flex-start;
    }
    .modal-content-premium { padding: 0 3rem 3rem; }
    .modal-footer-premium {
        padding: 2.5rem 3rem; background: var(--bg-main);
        display: flex; gap: 1.5rem; border-top: 1px solid var(--border-color);
    }

    .close-btn-premium {
        width: 44px; height: 44px; border-radius: 12px; border: none;
        background: var(--bg-main); color: var(--text-muted);
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        transition: all 0.3s;
    }
    .close-btn-premium:hover { background: #fee2e2; color: #ef4444; transform: rotate(90deg); }

    .item-preview-card {
        background: var(--bg-main); padding: 1.75rem; border-radius: 24px;
        border: 2px solid var(--border-color); border-style: dashed;
    }
    .item-icon-circle {
        width: 56px; height: 56px; background: white; border-radius: 18px;
        display: flex; align-items: center; justify-content: center; color: #f59e0b;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }

    .form-group-premium label {
        display: block; font-size: 0.72rem; font-weight: 950; color: var(--text-muted);
        text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.05em;
    }
    .premium-qty-input-stepper {
        width: 100%; height: 64px; padding: 0 1.5rem; border: none;
        background: transparent; color: var(--text-main); font-weight: 950; font-size: 1.5rem;
        outline: none; text-align: center;
    }
    .premium-qty-input-stepper::-webkit-inner-spin-button,
    .premium-qty-input-stepper::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    
    .stepper-component-premium {
        display: flex; align-items: center; background: var(--bg-main);
        border: 2px solid var(--border-color); border-radius: 20px;
        overflow: hidden; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stepper-component-premium:focus-within {
        border-color: #f59e0b; box-shadow: 0 12px 25px rgba(245,158,11,0.1);
        transform: translateY(-2px);
    }
    .stepper-btn {
        width: 64px; height: 64px; border: none; background: transparent;
        color: var(--text-muted); cursor: pointer; display: flex;
        align-items: center; justify-content: center; transition: 0.2s;
    }
    .stepper-btn:hover { background: rgba(0,0,0,0.02); color: #f59e0b; }
    .stepper-btn:active { transform: scale(0.9); }
    .stepper-btn i { width: 22px; stroke-width: 2.5; }

    .qty-max-label-stepper {
        position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
        font-weight: 850; color: var(--text-muted); font-size: 0.8rem;
        pointer-events: none; opacity: 0.5;
    }

    .premium-date-input {
        width: 100%; padding: 1.25rem 1.5rem; border-radius: 18px; border: 2px solid var(--border-color);
        background: var(--bg-main); color: var(--text-main); font-weight: 850; font-size: 1.1rem;
        outline: none; transition: 0.3s;
    }

    .audit-warning {
        margin-top: 2rem; padding: 1.5rem; background: rgba(245, 158, 11, 0.05);
        border-radius: 20px; border: 1px solid rgba(245, 158, 11, 0.1);
        display: flex; gap: 1.25rem; align-items: center;
    }
    .audit-warning p { margin: 0; font-size: 0.85rem; color: var(--text-main); font-weight: 700; line-height: 1.5; }

    /* Modern Action Buttons */
    .modern-btn-cancel {
        flex: 1; height: 56px; border-radius: 18px; border: 2px solid var(--border-color);
        background: transparent; color: var(--text-muted); font-weight: 850; font-size: 0.9rem;
        cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .modern-btn-cancel:hover {
        background: #f8fafc; color: #64748b; border-color: #cbd5e1;
        transform: translateY(-2px);
    }
    .modern-btn-cancel i { width: 18px; }

    .modern-btn-finalize {
        flex: 1.5; height: 56px; border-radius: 18px; border: none;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white; font-weight: 900; font-size: 1rem; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        box-shadow: 0 12px 24px rgba(245, 158, 11, 0.3);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .modern-btn-finalize:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 20px 40px rgba(245, 158, 11, 0.4);
    }
    .modern-btn-finalize:active { transform: translateY(0) scale(0.98); }
    .modern-btn-finalize i { width: 22px; }

    /* Premium Error Banner */
    .error-banner-premium {
        margin-top: 12px; background: #fef2f2; border: 1px solid #fee2e2;
        padding: 0.75rem 1rem; border-radius: 14px; color: #991b1b;
        display: none; align-items: center; gap: 12px;
        animation: slideDownIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .error-icon-box {
        width: 32px; height: 32px; background: white; border-radius: 8px;
        display: flex; align-items: center; justify-content: center; color: #ef4444;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.1);
    }
    @keyframes slideDownIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* Shortcut Button */
    .shortcut-btn-premium {
        background: transparent; border: none; color: #f59e0b; font-size: 0.75rem; 
        font-weight: 950; cursor: pointer; text-transform: uppercase; 
        display: flex; align-items: center; gap: 8px; padding: 6px 12px;
        border-radius: 8px; transition: 0.2s;
    }
    .shortcut-btn-premium:hover { background: rgba(245, 158, 11, 0.08); transform: translateX(4px); }
    .shortcut-btn-premium i { width: 14px; }

    /* Visibility Controls */
    .mobile-only { display: none; }
    
    @media (max-width: 1100px) {
        .search-container-premium { width: 300px; }
        .desktop-only { display: none; }
        .mobile-only { display: block; }
        .returns-controls { flex-direction: column; align-items: flex-start; gap: 2rem; }
        .search-container-premium { width: 100%; }
    }

    .return-card-mobile {
        background: var(--bg-card); padding: 2rem; border-radius: 24px;
        border: 1px solid var(--border-color);
        transition: 0.3s;
    }
    .mobile-recover-btn {
        width: 56px; height: 56px; border-radius: 16px; border: none;
        background: #f59e0b; color: white; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 10px 20px rgba(245, 158, 11, 0.2);
    }

    .animate-pop-in { animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    @keyframes popIn { from { opacity: 0; transform: scale(0.9) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
</style>

<script>
    function openReturnModal(item) {
        document.getElementById('modal_item_id').value = item.id;
        document.getElementById('modal_item_desc').innerText = item.description;
        document.getElementById('modal_item_beneficiary').innerText = 'Held by: ' + item.beneficiary;
        document.getElementById('modal_return_qty').value = item.quantity;
        document.getElementById('modal_return_qty').max = item.quantity;
        document.getElementById('modal_max_qty').innerText = item.quantity;
        
        const modal = document.getElementById('returnModal');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeReturnModal() {
        const modal = document.getElementById('returnModal');
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    function handleOutsideClick(e) {
        if(e.target.id === 'returnModal') closeReturnModal();
    }

    function adjustStepper(delta) {
        const input = document.getElementById('modal_return_qty');
        let val = parseInt(input.value) || 0;
        val = Math.max(1, val + delta);
        input.value = val;
        validateReturnQty();
    }

    function filterReturns() {
        const term = document.getElementById('returnSearch').value.toLowerCase();
        
        // Filter Table
        document.querySelectorAll('.return-row').forEach(row => {
            row.style.display = row.dataset.search.includes(term) ? 'table-row' : 'none';
        });

        // Filter Cards
        document.querySelectorAll('.return-card-mobile').forEach(card => {
            card.style.display = card.dataset.search.includes(term) ? 'block' : 'none';
        });
    }

    function validateReturnQty() {
        const input = document.getElementById('modal_return_qty');
        const submitBtn = document.getElementById('submit_return_btn');
        const errorBanner = document.getElementById('qty_error_banner');
        const max = parseInt(input.max);
        const val = parseInt(input.value);

        if (val > max) {
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
            errorBanner.style.display = 'flex';
            input.style.borderColor = '#ef4444';
            input.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';
        } else {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
            errorBanner.style.display = 'none';
            input.style.borderColor = val > 0 ? '#f59e0b' : 'var(--border-color)';
            input.style.backgroundColor = 'var(--bg-main)';
        }
    }

    function setFullReturn() {
        const input = document.getElementById('modal_return_qty');
        input.value = input.max;
        validateReturnQty();
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection
