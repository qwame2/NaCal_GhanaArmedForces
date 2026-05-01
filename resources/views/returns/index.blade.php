@extends('layouts.dashboard')

@section('content')
<!-- Hidden Purge Form for Absolute Reliability -->
<form id="purgeForm" action="{{ route('returns.purge') }}" method="POST" style="display: none;">
    @csrf
    <div id="purgeFormInputs"></div>
</form>

<!-- Premium Returns Header -->
<div class="header-mesh" style="background: #ffffff; padding: 3.5rem; border-radius: 32px; margin-bottom: 3rem; position: relative; overflow: hidden; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
    <div style="position: absolute; top: -50px; right: -50px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(245, 158, 11, 0.1) 0%, transparent 70%); z-index: 0;"></div>

    <div style="position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 2rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <span class="status-badge-premium">
                    <i data-lucide="refresh-cw" style="width: 12px;"></i>
                    Registry Recovery Node
                </span>
                <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="shield" style="width: 14px; color: #f59e0b;"></i> System Verified
                </span>
            </div>
            <h1 style="margin: 0; font-size: 3.5rem; font-weight: 950; color: var(--text-main); letter-spacing: -0.06em; line-height: 1;">Return <span style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Registry</span></h1>
            <p style="margin: 15px 0 0; color: var(--text-muted); font-size: 1.15rem; font-weight: 600; max-width: 600px; line-height: 1.6;">Re-integrate issued assets back into the primary store. Monitor outstanding allocations in real-time.</p>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button onclick="openHistorySheet()" class="modern-action-btn" style="border-radius: 20px; padding: 1.15rem 1.75rem; border: 1px solid var(--border-color); background: #f8fafc; box-shadow: 0 4px 10px rgba(0,0,0,0.02); cursor: pointer; color: var(--text-main); font-weight: 800; display: flex; align-items: center; gap: 10px; transition: all 0.2s ease;">
                <i data-lucide="history" style="width: 22px; color: #f59e0b;"></i>
                <span>Return History</span>
            </button>
        </div>
    </div>
</div>

<!-- Statistical Insight Row -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
    <div class="glass-card" style="padding: 2rem; border-radius: 24px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 1.5rem;">
        <div style="width: 64px; height: 64px; border-radius: 20px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="package-2" style="width: 32px;"></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Outstanding Assets</div>
            <div style="font-size: 2rem; font-weight: 950; color: var(--text-main); line-height: 1;">{{ $issuedItems->sum('quantity') }} <span style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted);">Units</span></div>
        </div>
    </div>

    <div class="glass-card" style="padding: 2rem; border-radius: 24px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 1.5rem;">
        <div style="width: 64px; height: 64px; border-radius: 20px; background: rgba(99, 102, 241, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center;">
            <i data-lucide="users" style="width: 32px;"></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Active Holders</div>
            <div style="font-size: 2rem; font-weight: 950; color: var(--text-main); line-height: 1;">{{ $issuedItems->where('quantity', '>', 0)->pluck('beneficiary')->unique()->count() }} <span style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted);">Depts</span></div>
        </div>
    </div>

    <div class="glass-card" style="padding: 2rem; border-radius: 24px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 1.5rem;">
        <div style="width: 64px; height: 64px; border-radius: 20px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-circle" style="width: 32px;"></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Registry Integrity</div>
            <div style="font-size: 2rem; font-weight: 950; color: var(--text-main); line-height: 1;">100% <span style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted);">Verified</span></div>
        </div>
    </div>
</div>

<!-- Interface Workspace -->
<div class="glass-card" style="border-radius: 32px; padding: 3rem 4rem; border: 1px solid var(--border-color); min-height: 500px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4rem; flex-wrap: wrap; gap: 2rem;" class="returns-controls">
        <div>
            <h3 style="margin: 0; font-size: 2rem; font-weight: 950; color: var(--text-main); letter-spacing: -0.02em;">Outstanding Allocations</h3>
            <p style="margin: 8px 0 0; color: var(--text-muted); font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; box-shadow: 0 0 10px #f59e0b;"></span>
                Tracking {{ $issuedItems->where('quantity', '>', 0)->count() }} active holdings across the logistics network
            </p>
        </div>

        <div class="search-container-premium" style="min-width: 400px; flex: 1; max-width: 500px;">
            <i data-lucide="search" class="search-icon"></i>
            <input type="text" id="returnSearch" placeholder="Search by recipient, item, or category..." oninput="filterReturns()" style="padding: 1.25rem 1.5rem 1.25rem 3.5rem; font-size: 1.05rem; border-radius: 20px;">
            <div class="search-accent"></div>
        </div>
    </div>

    @if($issuedItems->count() > 0)
    <!-- Desktop Table View -->
    <div class="desktop-only">
        <div class="table-scroll-wrapper" style="width: 100%; padding-bottom: 1.5rem;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0 1.25rem;">
                <thead>
                    <tr style="text-align: left; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.15em; font-weight: 900; border-bottom: 2px solid var(--bg-main);">
                        <th style="padding: 0 1.5rem 1rem;">Asset Breakdown</th>
                        <th style="padding: 0 1.5rem 1rem;">Holder Information</th>
                        <th style="padding: 0 1.5rem 1rem;">Classification</th>
                        <th style="padding: 0 1.5rem 1rem;">Allocation Balance</th>
                        <th style="padding: 0 1.5rem 1rem; text-align: right;">Action Control</th>
                    </tr>
                </thead>
                <tbody id="returnsTableBody">
                    @foreach($issuedItems as $item)
                    @if($item->quantity > 0)
                    <tr class="return-row" data-search="{{ strtolower($item->beneficiary . ' ' . $item->description . ' ' . $item->ledge_category) }}">
                        <td style="padding: 1.75rem 1.5rem; border-radius: 24px 0 0 24px;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div style="width: 48px; height: 48px; border-radius: 14px; background: #f8fafc; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                    <i data-lucide="package" style="width: 24px;"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 950; color: var(--text-main); font-size: 1.15rem; letter-spacing: -0.01em;">{{ $item->description }}</div>
                                    <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-top: 2px; display: flex; align-items: center; gap: 5px;">
                                        <i data-lucide="calendar" style="width: 12px;"></i>
                                        Issued {{ date('M d, Y', strtotime($item->issuance_date)) }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 1.75rem 1.5rem;">
                            <div style="font-weight: 850; color: var(--text-main); font-size: 1.05rem;">{{ $item->beneficiary }}</div>
                            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); margin-top: 2px;">Ref: {{ $item->authority ?: 'General Auth' }}</div>
                        </td>
                        <td style="padding: 1.75rem 1.5rem;">
                            <span class="ledge-badge-premium" style="padding: 0.5rem 1.25rem; border-radius: 12px; font-size: 0.7rem;">CATEGORY {{ $item->ledge_category }}</span>
                        </td>
                        <td style="padding: 1.75rem 1.5rem;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="padding: 0.75rem 1.25rem; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 16px;">
                                    <span style="font-weight: 950; font-size: 1.4rem; color: #d97706;">{{ $item->quantity }}</span>
                                    <span style="color: #f59e0b; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; margin-left: 4px;">{{ $item->unit ?: 'Units' }}</span>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 1.75rem 1.5rem; border-radius: 0 24px 24px 0; text-align: right;">
                            @if(auth()->user()->can_operate_logistics)
                            <button onclick="openReturnModal({{ json_encode($item) }})" class="recover-btn-premium" style="padding: 1rem 1.5rem; border-radius: 16px; font-weight: 900; background: #0f172a; color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);">
                                <i data-lucide="corner-up-left" style="width: 18px;"></i>
                                <span>Recovery</span>
                            </button>
                            @else
                            <button disabled title="Unauthorized: Logistics Permission Required" class="recover-btn-premium" style="padding: 1rem 1.5rem; border-radius: 16px; font-weight: 900; background: #cbd5e1; color: white; border: none; cursor: not-allowed; display: inline-flex; align-items: center; gap: 10px; box-shadow: none;">
                                <i data-lucide="lock" style="width: 18px;"></i>
                                <span>Locked</span>
                            </button>
                            @endif
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
            <div class="return-card-mobile" data-search="{{ strtolower($item->beneficiary . ' ' . $item->description . ' ' . $item->ledge_category) }}" style="padding: 2rem; background: #ffffff; border-radius: 28px; border: 1px solid var(--border-color); box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                    <span class="ledge-badge-premium" style="font-size: 0.65rem;">CAT {{ $item->ledge_category }}</span>
                    <div style="text-align: right;">
                        <div style="color: var(--text-muted); font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">Issued On</div>
                        <div style="color: var(--text-main); font-size: 0.8rem; font-weight: 900;">{{ date('d/m/y', strtotime($item->issuance_date)) }}</div>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1.25rem;">
                    <div style="width: 40px; height: 40px; border-radius: 12px; background: #f8fafc; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                        <i data-lucide="package" style="width: 20px;"></i>
                    </div>
                    <h4 style="margin: 0; color: var(--text-main); font-size: 1.25rem; font-weight: 950; letter-spacing: -0.01em;">{{ $item->description }}</h4>
                </div>

                <div style="background: #f8fafc; border-radius: 16px; padding: 1.25rem; margin-bottom: 1.5rem;">
                    <div style="font-size: 0.65rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Holder</div>
                    <div style="color: var(--text-main); font-weight: 850; font-size: 1.05rem;">{{ $item->beneficiary }}</div>
                    <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-top: 2px;">Ref: {{ $item->authority ?: 'General' }}</div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1.5rem; border-top: 1px dashed var(--border-color);">
                    <div>
                        <div style="font-size: 0.65rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Balance</div>
                        <div style="font-size: 1.75rem; font-weight: 950; color: #d97706;">{{ $item->quantity }} <span style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted);">{{ $item->unit ?: 'Units' }}</span></div>
                    </div>
                    @if(auth()->user()->can_operate_logistics)
                    <button onclick="openReturnModal({{ json_encode($item) }})" class="mobile-recover-btn" style="width: 56px; height: 56px; border-radius: 18px; background: #0f172a; color: white; border: none; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <i data-lucide="corner-up-left" style="width: 24px;"></i>
                    </button>
                    @else
                    <button disabled title="Unauthorized" style="width: 56px; height: 56px; border-radius: 18px; background: #cbd5e1; color: white; border: none; display: flex; align-items: center; justify-content: center; cursor: not-allowed;">
                        <i data-lucide="lock" style="width: 24px;"></i>
                    </button>
                    @endif
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
        <h3 style="font-weight: 950; color: var(--text-main); font-size: 1.5rem;">Clean Slate</h3>
        <p style="color: var(--text-muted); font-weight: 600;">All issued assets have been accounted for and returned.</p>
    </div>
    @endif
</div>
</div>

<!-- Return Overlay Modal -->
<div id="returnModal" class="modal-backdrop-premium" onclick="handleOutsideClick(event)">
    <div class="glass-card modal-container-premium animate-pop-in" id="modalContainer">
        <div class="samsung-drag-handle"></div>
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
                            <div id="modal_item_authority" style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; margin-top: 2px;"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-grid-premium">
                    <div class="form-group-premium">
                        <label>Quantity to Recover Control</label>
                        <div class="stepper-component-premium">
                            <button type="button" onclick="adjustStepper(-1)" class="stepper-btn">
                                <i data-lucide="minus"></i>
                            </button>
                            <div style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 10px;">
                                <input type="number" name="return_qty" id="modal_return_qty" min="1" required class="premium-qty-input-stepper" oninput="validateReturnQty()" style="width: 100px; padding: 0; text-align: right;">
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
                    <div class="form-group-premium" style="grid-column: span 2;">
                        <label>Item Status / State Description <span style="color: #ef4444;">*</span></label>
                        <textarea name="remarks" required placeholder="Describe the physical condition or operational state of the item upon recovery..." style="width: 100%; height: 100px; padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 18px; background: var(--bg-main); color: var(--text-main); font-family: inherit; font-weight: 600; font-size: 0.95rem; resize: none; outline: none; transition: all 0.3s;" onfocus="this.style.borderColor='var(--primary)'"></textarea>
                    </div>
                </div>

                <div class="audit-warning" style="display: flex; align-items: center; gap: 12px; background: rgba(245, 158, 11, 0.05); padding: 1.25rem; border-radius: 16px; border: 1px dashed rgba(245, 158, 11, 0.3);">
                    <i data-lucide="shield-check" style="width: 20px; color: #f59e0b;"></i>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); font-weight: 600; line-height: 1.5;">This action will automatically update current stock balances and mark this allocation as returned.</p>
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

<!-- History Bottom Sheet with Modern Professional Report Design -->
<div id="historySheet" class="modal-backdrop-premium" onclick="handleHistoryOutsideClick(event)">
    <div class="modal-container-premium animate-pop-in sheet-content" style="max-width: 1400px; width: 98%; max-height: 90vh; display: flex; flex-direction: column; background: #ffffff !important; backdrop-filter: none !important; -webkit-backdrop-filter: none !important;">
        <div class="samsung-drag-handle"></div>
        <div class="modal-header-premium" style="display: flex; justify-content: space-between; align-items: center; padding: 2rem 3rem;">
            <div>
                <h3 style="margin: 0; font-size: 2rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.02em;">Return <span style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">History</span></h3>
                <p style="margin: 6px 0 0; color: var(--text-muted); font-size: 1rem; font-weight: 600;">Tracking history of all recovered assets</p>
            </div>
            <button type="button" onclick="closeHistorySheet()" class="close-btn-premium modern-action-btn secondary" style="width: 54px; height: 54px; border-radius: 18px; border-color: rgba(239, 68, 68, 0.2); color: #ef4444;">
                <i data-lucide="x" style="width: 24px;"></i>
            </button>
        </div>

        <div class="modal-content-premium" style="flex: 1; overflow-y: auto; padding: 1rem 3rem 3rem;">
            <div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; align-items: center;">
                <input type="text" id="historySearchInput" placeholder="Search item or recipient..." oninput="filterHistory()" style="flex: 2; min-width: 200px; padding: 1rem 1.25rem; border-radius: 16px; border: 2px solid var(--border-color); background: var(--bg-main); color: var(--text-main); outline: none;">
                <input type="date" id="historyDateFilter" onchange="filterHistory()" style="flex: 1; min-width: 150px; padding: 1rem 1.25rem; border-radius: 16px; border: 2px solid var(--border-color); background: var(--bg-main); color: var(--text-main); outline: none;">
                <div id="purgeActions" style="display: none; align-items: center; gap: 1rem;">
                    <button onclick="generatePurgeReport()" class="modern-action-btn" style="background: var(--primary); color: white; border: none; padding: 1rem 1.5rem; border-radius: 14px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="file-text" style="width: 18px;"></i>
                        Generate Audit Report
                    </button>
                    <button id="finalizePurgeBtn" onclick="confirmPurge()" disabled class="modern-action-btn" style="background: #ef4444; color: white; border: none; padding: 1rem 1.5rem; border-radius: 14px; font-weight: 800; display: flex; align-items: center; gap: 8px; opacity: 0.5; cursor: not-allowed;">
                        <i data-lucide="trash-2" style="width: 18px;"></i>
                        Purge Selected
                    </button>
                </div>
            </div>
            <div id="historyTableContainer">
                <div style="padding: 5rem 0; text-align: center;">
                    <div class="loader" style="width: 40px; height: 40px; border: 4px solid var(--border-color); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Additional Professional Report Print Styles */
    .professional-report {
        font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        color: #1e293b;
    }

    .report-header-official {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 3px solid #d97706;
    }

    .report-header-official h1 {
        font-size: 1.8rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin: 0;
        color: #0f172a;
    }

    .report-header-official h2 {
        font-size: 1rem;
        font-weight: 600;
        color: #475569;
        margin: 0.25rem 0 0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .report-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        background: #f8fafc;
        padding: 1rem;
        border-radius: 12px;
        margin: 1.5rem 0;
    }

    .report-meta-item {
        display: flex;
        flex-direction: column;
    }

    .report-meta-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.05em;
    }

    .report-meta-value {
        font-size: 0.9rem;
        font-weight: 800;
        color: #0f172a;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
        font-size: 0.8rem;
    }

    .report-table th {
        background: #f1f5f9;
        padding: 0.75rem;
        text-align: left;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e2e8f0;
    }

    .report-table td {
        padding: 0.75rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .report-signature {
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
    }

    .signature-line {
        width: 250px;
        text-align: center;
    }

    .signature-line .line {
        border-top: 1px solid #0f172a;
        margin: 1rem 0 0.25rem;
    }

    @media print {

        .modal-backdrop-premium,
        .close-btn-premium,
        .modern-action-btn,
        .search-container-premium,
        #purgeActions {
            display: none !important;
        }

        .sheet-content {
            all: initial !important;
            display: block !important;
            position: relative !important;
            overflow: visible !important;
        }

        .professional-report {
            margin: 0;
            padding: 0;
        }
    }

    .header-mesh {
        background: radial-gradient(at 0% 0%, rgba(245, 158, 11, 0.06) 0, transparent 50%),
            var(--bg-card);
        backdrop-filter: blur(24px);
    }

    .status-badge-premium {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
        font-size: 0.7rem;
        font-weight: 950;
        padding: 0.5rem 1.25rem;
        border-radius: 99px;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(245, 158, 11, 0.1);
    }

    .search-container-premium {
        position: relative;
        width: 400px;
    }

    .search-icon {
        position: absolute;
        left: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        color: var(--text-muted);
        z-index: 2;
        transition: 0.3s;
    }

    .search-container-premium input {
        width: 100%;
        padding: 1.15rem 1.5rem 1.15rem 3.5rem;
        border-radius: 20px;
        border: 2px solid var(--border-color);
        background: var(--bg-main);
        color: var(--text-main);
        font-weight: 700;
        font-size: 1rem;
        outline: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        z-index: 1;
    }

    .search-container-premium:focus-within input {
        border-color: #f59e0b;
        background: var(--bg-card);
        box-shadow: 0 15px 30px rgba(245, 158, 11, 0.08);
    }

    .search-container-premium:focus-within .search-icon {
        color: #f59e0b;
    }

    .return-row {
        background: var(--bg-card);
        border-radius: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.01), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        cursor: pointer;
    }

    .return-row:hover {
        transform: scale(1.008) translateY(-4px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06);
        border-color: rgba(245, 158, 11, 0.2);
    }

    .ledge-badge-premium {
        background: var(--bg-main);
        color: var(--text-muted);
        padding: 0.45rem 1rem;
        border-radius: 12px;
        font-size: 0.72rem;
        font-weight: 850;
        border: 1px solid var(--border-color);
        letter-spacing: 0.02em;
    }

    .recover-btn-premium {
        padding: 0.85rem 1.5rem;
        border-radius: 14px;
        border: none;
        background: rgba(245, 158, 11, 0.08);
        color: #f59e0b;
        font-weight: 850;
        font-size: 0.85rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .recover-btn-premium:hover {
        background: #f59e0b;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(245, 158, 11, 0.25);
    }

    .recover-btn-premium i {
        width: 16px;
        transition: 0.3s;
    }

    .recover-btn-premium:hover i {
        transform: rotate(-45deg) scale(1.1);
    }

    /* Modal Styling */
    .modal-backdrop-premium {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(12px);
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: 0.4s;
    }

    .modal-backdrop-premium.active {
        display: flex;
        opacity: 1;
    }

    /* Ensure SweetAlert is always on top of premium modals */
    .swal2-container {
        z-index: 10000 !important;
    }

    .modal-container-premium {
        width: 750px;
        max-width: 95%;
        border-radius: 36px;
        padding: 0;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 40px 100px rgba(0, 0, 0, 0.4);
    }

    .modal-header-premium {
        padding: 3rem 3rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .modal-content-premium {
        padding: 0 3rem 3rem;
    }

    .modal-footer-premium {
        padding: 2.5rem 3rem;
        background: var(--bg-main);
        display: flex;
        gap: 1.5rem;
        border-top: 1px solid var(--border-color);
    }

    .close-btn-premium {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        border: none;
        background: var(--bg-main);
        color: var(--text-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    .close-btn-premium:hover {
        background: #fee2e2;
        color: #ef4444;
        transform: rotate(90deg);
    }

    .item-preview-card {
        background: var(--bg-main);
        padding: 1.75rem;
        border-radius: 24px;
        border: 2px solid var(--border-color);
        border-style: dashed;
    }

    .item-icon-circle {
        width: 56px;
        height: 56px;
        background: white;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #f59e0b;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    }

    .form-group-premium label {
        display: block;
        font-size: 0.72rem;
        font-weight: 950;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 12px;
        letter-spacing: 0.05em;
    }

    .premium-qty-input-stepper {
        width: 100%;
        height: 64px;
        padding: 0 1.5rem;
        border: none;
        background: transparent;
        color: var(--text-main);
        font-weight: 950;
        font-size: 1.5rem;
        outline: none;
        text-align: center;
    }

    .premium-qty-input-stepper::-webkit-inner-spin-button,
    .premium-qty-input-stepper::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .stepper-component-premium {
        display: flex;
        align-items: center;
        background: var(--bg-main);
        border: 2px solid var(--border-color);
        border-radius: 20px;
        overflow: hidden;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .stepper-component-premium:focus-within {
        border-color: #f59e0b;
        box-shadow: 0 12px 25px rgba(245, 158, 11, 0.1);
        transform: translateY(-2px);
    }

    .stepper-btn {
        width: 64px;
        height: 64px;
        border: none;
        background: transparent;
        color: var(--text-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }

    .stepper-btn:hover {
        background: rgba(0, 0, 0, 0.02);
        color: #f59e0b;
    }

    .stepper-btn:active {
        transform: scale(0.9);
    }

    .stepper-btn i {
        width: 22px;
        stroke-width: 2.5;
    }

    .qty-max-label-stepper {
        font-weight: 850;
        color: var(--text-muted);
        font-size: 1rem;
        opacity: 0.4;
        margin-top: 4px;
        pointer-events: none;
    }

    .premium-date-input {
        width: 100%;
        padding: 1.25rem 1.5rem;
        border-radius: 18px;
        border: 2px solid var(--border-color);
        background: var(--bg-main);
        color: var(--text-main);
        font-weight: 850;
        font-size: 1.1rem;
        outline: none;
        transition: 0.3s;
    }

    .audit-warning {
        margin-top: 2rem;
        padding: 1.5rem;
        background: rgba(245, 158, 11, 0.05);
        border-radius: 20px;
        border: 1px solid rgba(245, 158, 11, 0.1);
        display: flex;
        gap: 1.25rem;
        align-items: center;
    }

    .audit-warning p {
        margin: 0;
        font-size: 0.85rem;
        color: var(--text-main);
        font-weight: 700;
        line-height: 1.5;
    }

    /* Premium Samsung-Style Action Buttons */
    .modern-btn-cancel {
        flex: 1;
        height: 64px;
        border-radius: 999px;
        border: none;
        background: rgba(100, 116, 139, 0.08);
        color: var(--text-main);
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s cubic-bezier(0.2, 0.9, 0.3, 1.1);
        text-transform: none !important;
        letter-spacing: 0;
    }

    .modern-btn-cancel:hover {
        background: rgba(100, 116, 139, 0.15);
        transform: translateY(-2px);
    }

    .modern-btn-cancel:active {
        transform: translateY(0) scale(0.97);
    }

    .modern-btn-cancel i {
        width: 18px;
        color: var(--text-muted);
        stroke-width: 2.5;
    }

    .modern-btn-finalize {
        flex: 1.5;
        height: 64px;
        border-radius: 999px;
        border: none;
        background: #f59e0b;
        color: white;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s cubic-bezier(0.2, 0.9, 0.3, 1.1);
        text-transform: none !important;
        letter-spacing: 0;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
    }

    .modern-btn-finalize:hover {
        background: #fbbf24;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(245, 158, 11, 0.25);
    }

    .modern-btn-finalize:active {
        transform: translateY(0) scale(0.97);
    }

    .modern-btn-finalize i {
        width: 18px;
        stroke-width: 2.5;
    }

    /* Premium Error Banner */
    .error-banner-premium {
        margin-top: 12px;
        background: #fef2f2;
        border: 1px solid #fee2e2;
        padding: 0.75rem 1rem;
        border-radius: 14px;
        color: #991b1b;
        display: none;
        align-items: center;
        gap: 12px;
        animation: slideDownIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .error-icon-box {
        width: 32px;
        height: 32px;
        background: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ef4444;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.1);
    }

    @keyframes slideDownIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Shortcut Button */
    .shortcut-btn-premium {
        background: transparent;
        border: none;
        color: #f59e0b;
        font-size: 0.75rem;
        font-weight: 950;
        cursor: pointer;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 8px;
        transition: 0.2s;
    }

    .shortcut-btn-premium:hover {
        background: rgba(245, 158, 11, 0.08);
        transform: translateX(4px);
    }

    .shortcut-btn-premium i {
        width: 14px;
    }

    /* Visibility Controls */
    .mobile-only {
        display: none;
    }

    @media (max-width: 1100px) {
        .search-container-premium {
            width: 300px;
        }

        .desktop-only {
            display: none;
        }

        .mobile-only {
            display: block;
        }

        .returns-controls {
            flex-direction: column;
            align-items: flex-start;
            gap: 2rem;
        }

        .search-container-premium {
            width: 100%;
        }
    }

    .return-card-mobile {
        background: var(--bg-card);
        padding: 2rem;
        border-radius: 24px;
        border: 1px solid var(--border-color);
        transition: 0.3s;
    }

    .mobile-recover-btn {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        border: none;
        background: #f59e0b;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 20px rgba(245, 158, 11, 0.2);
    }

    .modal-grid-premium {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .samsung-drag-handle {
        display: none;
    }

    @media (max-width: 768px) {
        .modal-backdrop-premium {
            align-items: flex-end !important;
            padding: 0 !important;
        }

        .modal-container-premium {
            width: 100% !important;
            max-width: 100% !important;
            border-radius: 36px 36px 0 0 !important;
            border: none !important;
            border-top: 1px solid rgba(255, 255, 255, 0.15) !important;
            animation: slideUpSamsung 0.4s cubic-bezier(0.2, 0.9, 0.3, 1.1) forwards !important;
            padding-bottom: env(safe-area-inset-bottom) !important;
            margin: 0 !important;
            margin-top: auto !important;
            overflow: visible !important;
        }

        .samsung-drag-handle {
            display: block;
            width: 44px;
            height: 5px;
            border-radius: 5px;
            background: rgba(150, 150, 150, 0.3);
            margin: 16px auto -10px;
        }

        .modal-header-premium {
            padding: 2rem 1.75rem 1.5rem !important;
        }

        .modal-content-premium {
            padding: 0 1.75rem 2rem !important;
        }

        .modal-footer-premium {
            padding: 1.5rem 1.75rem 2.5rem !important;
            flex-direction: row !important;
            gap: 1rem !important;
            border-top: none !important;
            background: transparent !important;
        }

        .modal-grid-premium {
            grid-template-columns: 1fr !important;
            gap: 1.25rem !important;
            margin-top: 1.5rem !important;
        }

        .modern-btn-cancel,
        .modern-btn-finalize {
            height: 64px !important;
            border-radius: 20px !important;
            font-size: 1rem !important;
            flex: 1 !important;
            width: auto !important;
        }

        .modern-btn-cancel {
            background: var(--bg-card) !important;
            border-color: transparent !important;
        }

        .item-preview-card {
            border-radius: 24px !important;
            border: none !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
            background: var(--bg-card) !important;
        }

        .close-btn-premium {
            display: none !important;
        }
    }

    @keyframes slideUpSamsung {
        from {
            transform: translateY(100%);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Desktop animation remains popIn */
    @media (min-width: 769px) {
        .animate-pop-in {
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
    }

    @keyframes popIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(20px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
</style>

<script>
    function openReturnModal(item) {
        document.getElementById('modal_item_id').value = item.id;
        document.getElementById('modal_item_desc').innerText = item.description;
        document.getElementById('modal_item_beneficiary').innerText = 'Held by: ' + item.beneficiary;
        document.getElementById('modal_item_authority').innerText = 'Approval: ' + (item.authority ? item.authority : 'N/A');
        document.getElementById('modal_return_qty').value = item.quantity;
        document.getElementById('modal_return_qty').max = item.quantity;
        document.getElementById('modal_max_qty').innerText = item.quantity + ' ' + (item.unit || 'Units');

        const modal = document.getElementById('returnModal');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeReturnModal() {
        const modal = document.getElementById('returnModal');
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';

        const container = document.getElementById('modalContainer');
        if (container) {
            setTimeout(() => {
                container.style.transition = '';
                container.style.transform = '';
                container.style.animation = '';
                modal.style.opacity = '';
                modal.style.transition = '';
            }, 400);
        }
    }

    function handleOutsideClick(e) {
        if (e.target.id === 'returnModal') closeReturnModal();
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

    // Return History Logic
    let historyData = [];

    async function openHistorySheet() {
        const sheet = document.getElementById('historySheet');
        sheet.classList.add('active');
        document.body.style.overflow = 'hidden';

        try {
            const res = await fetch("{{ route('api.returned-items-history') }}");
            historyData = await res.json();
            renderHistory(historyData);
        } catch (e) {
            document.getElementById('historyTableContainer').innerHTML = '<p style="text-align: center; color: red;">Failed to load history.</p>';
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function filterHistory() {
        const term = document.getElementById('historySearchInput').value.toLowerCase();
        const date = document.getElementById('historyDateFilter').value;

        const filtered = historyData.filter(i => {
            const matchSearch = i.description.toLowerCase().includes(term) ||
                i.beneficiary.toLowerCase().includes(term) ||
                (i.authority && i.authority.toLowerCase().includes(term)) ||
                i.ledge_category.toLowerCase().includes(term);
            const itemDate = i.return_date || new Date(i.created_at).toISOString().split('T')[0];
            const matchDate = !date || itemDate === date;
            return matchSearch && matchDate;
        });

        renderHistory(filtered);
    }

    function renderHistory(data) {
        const container = document.getElementById('historyTableContainer');
        if (data.length === 0) {
            container.innerHTML = `
                <div style="padding: 6rem 0; text-align: center;">
                    <i data-lucide="inbox" style="width: 64px; height: 64px; color: var(--text-muted); opacity: 0.2; margin-bottom: 1.5rem;"></i>
                    <h4 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0 0 0.5rem;">No Recovered Items</h4>
                    <p style="color: var(--text-muted);">Try adjusting your search filters.</p>
                </div>`;
            if (typeof lucide !== 'undefined') lucide.createIcons();
            return;
        }

        let desktopHtml = `
            <div class="desktop-only">
                <div class="table-scroll-wrapper" style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: separate; border-spacing: 0 1rem; min-width: 1100px;">
                        <thead>
                            <tr style="text-align: left; color: var(--text-muted); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.12em;">
                                <th style="padding: 0 1rem 0.5rem; width: 40px;">
                                    <input type="checkbox" id="selectAllHistory" onchange="toggleSelectAllHistory(this)" style="width: 18px; height: 18px; border-radius: 6px; cursor: pointer;">
                                </th>
                                <th style="padding: 0 1rem 0.5rem;">Return Date</th>
                                <th style="padding: 0 1rem 0.5rem;">Original Issue Date</th>
                                <th style="padding: 0 1rem 0.5rem;">Asset Description</th>
                                <th style="padding: 0 1rem 0.5rem;">Classification</th>
                                <th style="padding: 0 1rem 0.5rem;">Recipient</th>
                                <th style="padding: 0 1rem 0.5rem;">Approval</th>
                                <th style="padding: 0 1rem 0.5rem;">Returned Qty</th>
                                <th style="padding: 0 1rem 0.5rem;">Status / State</th>
                                <th style="padding: 0 1rem 0.5rem;">Outstanding Balance</th>
                            </tr>
                        </thead>
                        <tbody>
        `;

        let mobileHtml = `
            <div class="mobile-only">
                <div style="display: grid; gap: 1.5rem;">
        `;

        data.forEach(item => {
            const d = new Date(item.created_at);
            const dateStr = d.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
            const timeStr = d.toLocaleTimeString('en-GB', {
                hour: '2-digit',
                minute: '2-digit'
            });

            const issueDate = new Date(item.issuance_date);
            const issueDateStr = issueDate.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });

            desktopHtml += `
                <tr style="background: var(--bg-main); border-radius: 16px;">
                    <td style="padding: 1.25rem 1rem; border-radius: 16px 0 0 16px; vertical-align: middle;">
                        <input type="checkbox" class="history-checkbox" value="${item.id}" onchange="updatePurgeSelection()" style="width: 18px; height: 18px; border-radius: 6px; cursor: pointer;">
                    </td>
                    <td style="padding: 1.25rem 1rem;">
                        <div style="font-weight: 800; color: var(--text-main); font-size: 0.95rem;">${dateStr}</div>
                        <div style="color: var(--text-muted); font-size: 0.75rem; margin-top: 4px; display: flex; align-items: center; gap: 4px;"><i data-lucide="clock" style="width: 12px;"></i> ${timeStr}</div>
                    </td>
                    <td style="padding: 1.25rem 1rem; color: var(--text-muted); font-weight: 700;">${issueDateStr}</td>
                    <td style="padding: 1.25rem 1rem; font-weight: 800; color: var(--text-main); font-size: 1.05rem;">${item.description}</td>
                    <td style="padding: 1.25rem 1rem;">
                        <span style="background: rgba(99, 102, 241, 0.08); color: var(--primary); padding: 0.4rem 0.8rem; border-radius: 10px; font-size: 0.65rem; font-weight: 900; border: 1px solid rgba(99, 102, 241, 0.1);">CATEGORY ${item.ledge_category}</span>
                    </td>
                    <td style="padding: 1.25rem 1rem; color: var(--text-muted); font-weight: 700;">${item.beneficiary}</td>
                    <td style="padding: 1.25rem 1rem; color: var(--text-muted); font-weight: 700;">${item.authority || '-'}</td>
                    <td style="padding: 1.25rem 1rem; font-weight: 900; font-size: 1.2rem; color: #10b981;">${item.returned_qty} <span style="font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">${item.unit || 'Units'}</span></td>
                    <td style="padding: 1.25rem 1rem; color: var(--text-muted); font-weight: 600; font-size: 0.85rem; max-width: 200px;">
                        <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${item.remarks || 'No remarks'}">${item.remarks || '-'}</div>
                    </td>
                    <td style="padding: 1.25rem 1rem; border-radius: 0 16px 16px 0; font-weight: 900; font-size: 1.1rem; color: ${item.current_balance > 0 ? '#ef4444' : '#10b981'};">
                        ${item.current_balance} <span style="font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">${item.unit || 'Units'}</span>
                        <div style="font-size: 0.6rem; font-weight: 950; text-transform: uppercase; margin-top: 4px; color: ${item.current_balance > 0 ? '#ef4444' : '#10b981'}; opacity: 0.8;">
                            ${item.current_balance > 0 ? 'Pending' : 'Cleared'}
                        </div>
                    </td>
                </tr>
            `;

            mobileHtml += `
                <div class="return-card-mobile" style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 1.75rem; position: relative;">
                    <div style="position: absolute; top: 1.75rem; right: 1.75rem;">
                        <input type="checkbox" class="history-checkbox" value="${item.id}" onchange="updatePurgeSelection()" style="width: 24px; height: 24px; border-radius: 8px; cursor: pointer;">
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; padding-right: 3rem;">
                        <span style="background: rgba(99, 102, 241, 0.08); color: var(--primary); padding: 0.4rem 0.8rem; border-radius: 10px; font-size: 0.65rem; font-weight: 900;">CATEGORY ${item.ledge_category}</span>
                        <div style="text-align: right;">
                            <div style="color: var(--text-main); font-size: 0.75rem; font-weight: 800;">${dateStr}</div>
                            <div style="color: var(--text-muted); font-size: 0.65rem; font-weight: 700; margin-top: 2px; display: flex; align-items: center; justify-content: flex-end; gap: 4px;">
                                <i data-lucide="clock" style="width: 10px;"></i> ${timeStr}
                            </div>
                        </div>
                    </div>
                    <h4 style="margin: 0; color: var(--text-main); font-size: 1.1rem; font-weight: 900;">${item.description}</h4>
                    <p style="margin: 6px 0 0; color: var(--text-muted); font-weight: 700; font-size: 0.9rem;">Recipient: ${item.beneficiary}</p>
                    <p style="margin: 2px 0 0.5rem; color: var(--text-muted); font-weight: 700; font-size: 0.85rem;">Approval: ${item.authority || '-'}</p>
                    <p style="margin: 0 0 1.25rem; color: var(--text-main); font-weight: 600; font-size: 0.8rem; background: rgba(0,0,0,0.02); padding: 8px; border-radius: 8px;">State: ${item.remarks || 'No description'}</p>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1.25rem; border-top: 1px dashed var(--border-color);">
                        <div>
                            <div style="font-size: 0.6rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase;">Returned</div>
                            <div style="font-size: 1.35rem; font-weight: 950; color: #10b981;">${item.returned_qty} <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); opacity: 0.7;">${item.unit || 'Units'}</span></div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.6rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase;">Current Balance</div>
                            <div style="font-size: 1.35rem; font-weight: 950; color: ${item.current_balance > 0 ? '#ef4444' : '#10b981'};">${item.current_balance} <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); opacity: 0.7;">${item.unit || 'Units'}</span></div>
                        </div>
                    </div>
                    <div style="margin-top: 0.75rem; text-align: center; font-size: 0.65rem; font-weight: 900; color: ${item.current_balance > 0 ? '#ef4444' : '#10b981'}; text-transform: uppercase; letter-spacing: 0.05em;">
                        ${item.current_balance > 0 ? 'Pending Collection' : 'Fully Recovered'}
                    </div>
                </div>
            `;
        });

        desktopHtml += `</tbody></table></div></div>`;
        mobileHtml += `</div></div>`;

        container.innerHTML = desktopHtml + mobileHtml;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeHistorySheet() {
        const sheet = document.getElementById('historySheet');
        const content = sheet.querySelector('.sheet-content');
        sheet.classList.remove('active');
        document.body.style.overflow = 'auto';

        if (content) {
            setTimeout(() => {
                content.style.transition = '';
                content.style.transform = '';
                sheet.style.opacity = '';
                sheet.style.transition = '';
            }, 400);
        }
    }

    function handleHistoryOutsideClick(e) {
        if (e.target.id === 'historySheet') closeHistorySheet();
    }

    // Samsung Drag Logic for History Sheet
    function initHistoryDrag() {
        const sheetBackdrop = document.getElementById('historySheet');
        const sheetContent = sheetBackdrop ? sheetBackdrop.querySelector('.sheet-content') : null;

        if (sheetContent && sheetBackdrop) {
            let startY = 0;
            let currentY = 0;
            let isDragging = false;
            let windowHeight = window.innerHeight;

            sheetContent.addEventListener('touchstart', (e) => {
                if (e.target.closest('.modal-header-premium') || e.target.closest('.samsung-drag-handle')) {
                    startY = e.touches[0].clientY;
                    isDragging = true;
                    windowHeight = window.innerHeight;

                    sheetContent.style.setProperty('transition', 'none', 'important');
                    sheetBackdrop.style.setProperty('transition', 'none', 'important');
                }
            }, {
                passive: true
            });

            sheetContent.addEventListener('touchmove', (e) => {
                if (!isDragging) return;
                currentY = e.touches[0].clientY;
                const diff = currentY - startY;

                if (diff > 0) {
                    sheetContent.style.transform = `translateY(${diff}px)`;
                    let fade = 1 - (diff / (windowHeight * 0.8));
                    sheetBackdrop.style.opacity = fade > 0 ? fade : 0;
                    e.preventDefault();
                } else {
                    let resistance = diff * 0.15;
                    sheetContent.style.transform = `translateY(${resistance}px)`;
                    e.preventDefault();
                }
            }, {
                passive: false
            });

            sheetContent.addEventListener('touchend', () => {
                if (!isDragging) return;
                isDragging = false;
                const diff = currentY - startY;

                sheetContent.style.setProperty('transition', 'transform 0.4s cubic-bezier(0.32, 0.72, 0, 1)', 'important');
                sheetBackdrop.style.setProperty('transition', 'opacity 0.4s ease', 'important');

                if (diff > 150 || diff > windowHeight * 0.25) {
                    sheetContent.style.transform = 'translateY(100%)';
                    sheetBackdrop.style.opacity = '0';
                    setTimeout(() => closeHistorySheet(), 350);
                } else {
                    sheetContent.style.transform = 'translateY(0)';
                    sheetBackdrop.style.opacity = '1';
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        initHistoryDrag();

        const modalContainer = document.getElementById('modalContainer');
        const modalBackdrop = document.getElementById('returnModal');
        if (modalContainer && modalBackdrop) {
            let startY = 0;
            let currentY = 0;
            let isDragging = false;
            let windowHeight = window.innerHeight;

            modalContainer.addEventListener('touchstart', (e) => {
                if (e.target.closest('.modal-header-premium') || e.target.closest('.samsung-drag-handle')) {
                    startY = e.touches[0].clientY;
                    isDragging = true;
                    windowHeight = window.innerHeight; // refresh height

                    // Forcefully override CSS animations that lock the transform
                    modalContainer.style.setProperty('animation', 'none', 'important');
                    modalContainer.style.setProperty('transition', 'none', 'important');
                    modalBackdrop.style.setProperty('transition', 'none', 'important');
                }
            }, {
                passive: true
            });

            modalContainer.addEventListener('touchmove', (e) => {
                if (!isDragging) return;
                currentY = e.touches[0].clientY;
                const diff = currentY - startY;

                if (diff > 0) {
                    // Exact 1:1 tracking when pulling down
                    modalContainer.style.transform = `translateY(${diff}px)`;
                    // Smoothly fade out the dark background just like iOS
                    let fade = 1 - (diff / (windowHeight * 0.8));
                    modalBackdrop.style.opacity = fade > 0 ? fade : 0;
                    e.preventDefault();
                } else {
                    // Apple's signature "rubber band" resistance when pulling up
                    let resistance = diff * 0.15;
                    modalContainer.style.transform = `translateY(${resistance}px)`;
                    e.preventDefault();
                }
            }, {
                passive: false
            });

            modalContainer.addEventListener('touchend', () => {
                if (!isDragging) return;
                isDragging = false;
                const diff = currentY - startY;

                // Re-enable smooth transitions for the snap/close physics
                modalContainer.style.setProperty('transition', 'transform 0.4s cubic-bezier(0.32, 0.72, 0, 1)', 'important');
                modalBackdrop.style.setProperty('transition', 'opacity 0.4s ease', 'important');

                if (diff > 150 || diff > windowHeight * 0.25) {
                    // Dragged far enough: Close it fully
                    modalContainer.style.transform = 'translateY(100%)';
                    modalBackdrop.style.opacity = '0';
                    setTimeout(() => closeReturnModal(), 350);
                } else {
                    // Didn't drag far enough: Snap back instantly
                    modalContainer.style.transform = 'translateY(0)';
                    modalBackdrop.style.opacity = '1';
                }
            });
        }
    });
    let selectedHistoryIds = [];
    let reportGenerated = false;

    function toggleSelectAllHistory(el) {
        const checkboxes = document.querySelectorAll('.history-checkbox');
        checkboxes.forEach(cb => cb.checked = el.checked);
        updatePurgeSelection();
    }

    function updatePurgeSelection() {
        const checkboxes = document.querySelectorAll('.history-checkbox:checked');
        selectedHistoryIds = Array.from(checkboxes).map(cb => cb.value);

        const actions = document.getElementById('purgeActions');
        if (selectedHistoryIds.length > 0) {
            actions.style.display = 'flex';
        } else {
            actions.style.display = 'none';
            reportGenerated = false;
            document.getElementById('finalizePurgeBtn').disabled = true;
            document.getElementById('finalizePurgeBtn').style.opacity = '0.5';
            document.getElementById('finalizePurgeBtn').style.cursor = 'not-allowed';
        }
    }

    function generatePurgeReport() {
        const logoUrl = "{{ asset('img/NACOC.png') }}";
        if (selectedHistoryIds.length === 0) return;

        const selectedItems = historyData.filter(item => selectedHistoryIds.includes(item.id.toString()));
        const userName = "{{ auth()->user()->name ?? 'System Administrator' }}";
        const now = new Date().toLocaleString('en-GB', {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        const auditId = "NACOC-PRG-" + Math.random().toString(36).substr(2, 6).toUpperCase() + "-" + new Date().getFullYear();

        const totalItems = selectedItems.length;
        const totalQuantity = selectedItems.reduce((sum, item) => sum + (parseInt(item.returned_qty) || 0), 0);

        const reportHtml = `
            <div class="professional-report">
                <div class="report-header-official">
                    <img src="${logoUrl}" alt="NACOC Logo" style="height: 100px; margin-bottom: 1rem;">
                    <h1>NARCOTICS CONTROL COMMISSION</h1>
                    <h2>GOVERNMENT OF GHANA • LOGISTICS & ASSET MANAGEMENT DIVISION</h2>
                    <div style="margin-top: 1rem;">
                        <span style="background: #d97706; color: white; padding: 0.25rem 1rem; border-radius: 20px; font-size: 0.7rem; font-weight: 700;">CERTIFICATE OF REGISTRY PURGE</span>
                    </div>
                </div>

                <div class="report-meta-grid">
                    <div class="report-meta-item">
                        <span class="report-meta-label">Document ID</span>
                        <span class="report-meta-value">${auditId}</span>
                    </div>
                    <div class="report-meta-item">
                        <span class="report-meta-label">Authorization Date</span>
                        <span class="report-meta-value">${now}</span>
                    </div>
                    <div class="report-meta-item">
                        <span class="report-meta-label">Authorizing Official</span>
                        <span class="report-meta-value">${userName}</span>
                    </div>
                    <div class="report-meta-item">
                        <span class="report-meta-label">Security Clearance</span>
                        <span class="report-meta-value">LEVEL 4 (ADMINISTRATIVE)</span>
                    </div>
                </div>

                <div style="background: #fef9e6; padding: 1rem; border-left: 4px solid #d97706; margin: 1rem 0;">
                    <p style="margin: 0; font-size: 0.85rem;"><strong>OFFICIAL DECLARATION:</strong> In accordance with the NACOC Secure Registry Protocol and the Data Protection Act, the undersigned hereby certifies the permanent erasure of the following digital records from the Centralized Inventory Database. These assets have been physically verified for disposal or recovery completion.</p>
                </div>

                <div style="line-height: 1.8; font-size: 1rem; color: #334155; margin-top: 2rem; text-align: justify;">
                    <p>
                        This audit confirms the successful recovery and verification of the following assets.
                        ${selectedItems.map((item, index) => {
                            const issueDate = new Date(item.issuance_date).toLocaleDateString('en-GB');
                            const issueTime = new Date(item.issuance_timestamp).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
                            const returnDate = new Date(item.created_at).toLocaleDateString('en-GB');
                            const returnTime = new Date(item.created_at).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

                            return `
                                The item identified as <strong>${item.description}</strong> (Ref ID: #${item.id}), 
                                categorized under <strong>LEDGE ${item.ledge_category}</strong>, was officially recovered from 
                                <strong>${item.beneficiary}${item.authority ? ' (Approval: ' + item.authority + ')' : ''}</strong>. 
                                Upon recovery, the asset was documented in the following state: <em>"${item.remarks || 'No specific condition noted'}"</em>.
                                This asset, originally issued on <strong>${issueDate} at ${issueTime}</strong>, involved the return of <strong>${item.returned_qty} unit(s)</strong> 
                                to the central registry. The recovery process was formally completed and verified on 
                                <strong>${returnDate} at ${returnTime}</strong>. ${index < selectedItems.length - 1 ? '<br><br>' : ''}
                            `;
                        }).join('')}
                    </p>

                    <div style="margin-top: 3rem; padding: 1.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; font-weight: 800; text-align: center;">
                        TOTAL AGGREGATE QUANTITY RECOVERED: ${totalQuantity} UNITS
                    </div>
                </div>

                <div class="report-signature" style="justify-content: center; margin-top: 4rem;">
                    <div class="signature-line">
                        <div><strong>${userName}</strong></div>
                    </div>
                </div>

            </div>
        `;

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
                <head>
                    <title>NACOC | Official Purge Report</title>
                    <meta charset="UTF-8">
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body {
                            font-family: 'Times New Roman', 'Inter', 'Segoe UI', serif;
                            background: white;
                            padding: 20mm;
                            color: #1e293b;
                            line-height: 1.4;
                        }
                        @page {
                            size: A4;
                            margin: 15mm;
                        }
                        @media print {
                            body {
                                padding: 0;
                                margin: 0;
                            }
                            .professional-report {
                                max-width: 100%;
                            }
                        }
                        .professional-report {
                            max-width: 1100px;
                            margin: 0 auto;
                        }
                        .report-header-official {
                            text-align: center;
                            margin-bottom: 2rem;
                            padding-bottom: 1rem;
                            border-bottom: 3px solid #d97706;
                        }
                        .report-header-official h1 {
                            font-size: 1.8rem;
                            font-weight: 800;
                            letter-spacing: -0.02em;
                            margin: 0;
                            color: #0f172a;
                        }
                        .report-header-official h2 {
                            font-size: 1rem;
                            font-weight: 600;
                            color: #475569;
                            margin: 0.25rem 0 0;
                            text-transform: uppercase;
                            letter-spacing: 0.05em;
                        }
                        .report-meta-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                            gap: 1rem;
                            background: #f8fafc;
                            padding: 1rem;
                            border-radius: 8px;
                            margin: 1.5rem 0;
                        }
                        .report-meta-item {
                            display: flex;
                            flex-direction: column;
                        }
                        .report-meta-label {
                            font-size: 0.65rem;
                            font-weight: 700;
                            text-transform: uppercase;
                            color: #64748b;
                            letter-spacing: 0.05em;
                        }
                        .report-meta-value {
                            font-size: 0.85rem;
                            font-weight: 800;
                            color: #0f172a;
                        }
                        .report-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin: 1.5rem 0;
                            font-size: 0.8rem;
                        }
                        .report-table th {
                            background: #f1f5f9;
                            padding: 0.75rem;
                            text-align: left;
                            font-weight: 800;
                            text-transform: uppercase;
                            font-size: 0.7rem;
                            letter-spacing: 0.05em;
                            border-bottom: 2px solid #e2e8f0;
                        }
                        .report-table td {
                            padding: 0.75rem;
                            border-bottom: 1px solid #e2e8f0;
                        }
                        .report-table tfoot td {
                            background: #f8fafc;
                            font-weight: 800;
                            border-top: 2px solid #e2e8f0;
                        }
                        .report-signature {
                            margin-top: 2rem;
                            padding-top: 1rem;
                            border-top: 1px solid #e2e8f0;
                            display: flex;
                            justify-content: space-between;
                        }
                        .signature-line {
                            width: 250px;
                            text-align: center;
                        }
                        .signature-line .line {
                            border-top: 1px solid #0f172a;
                            margin: 1rem 0 0.25rem;
                        }
                    </style>
                </head>
                <body onload="setTimeout(() => { window.print(); window.close(); }, 600);">
                    ${reportHtml}
                </body>
            </html>
        `);
        printWindow.document.close();
        reportGenerated = true;
        const btn = document.getElementById('finalizePurgeBtn');
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';

        Swal.fire({
            title: 'Official Report Ready',
            text: 'The audit certificate has been generated. Please save/print it before proceeding with the purge.',
            icon: 'success',
            confirmButtonColor: '#d97706'
        });
    }

    function confirmPurge() {
        if (!reportGenerated) {
            Swal.fire('Authorization Required', 'You must generate and save the official audit report before purging records.', 'error');
            return;
        }

        Swal.fire({
            title: 'Permanent Data Purge',
            html: `You are about to permanently delete <strong>${selectedHistoryIds.length}</strong> record(s) from the registry. This action cannot be reversed.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Purge Permanently',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('purgeForm');
                const inputsContainer = document.getElementById('purgeFormInputs');
                inputsContainer.innerHTML = '';

                selectedHistoryIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    inputsContainer.appendChild(input);
                });

                form.submit();
            }
        });
    }

    @if(session('reopen_history'))
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(openHistorySheet, 500);
    });
    @endif
</script>
@endsection
