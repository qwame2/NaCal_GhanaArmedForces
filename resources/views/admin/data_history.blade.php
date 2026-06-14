@extends('layouts.admin')

@section('title', 'History')

@section('content')
<div class="animate-slide-up">
    <div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="font-size: 2.25rem; font-weight: 900; letter-spacing: -0.04em; color: var(--text-main); margin-bottom: 0.25rem;"><span style="color: var(--primary);">History</span></h2>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; display: flex; align-items: center; gap: 0.75rem;">
                Detailed record of system state modifications and inventory stock/role mutations.
            </p>
        </div>
    </div>

    <!-- Filter Console -->
    <div class="glass-card" style="padding: 2rem; margin-bottom: 2rem; border-radius: 24px; background: linear-gradient(145deg, #ffffff, #f8fafc); border: 1px solid rgba(79, 70, 229, 0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
            <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(79, 70, 229, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                <i data-lucide="filter" style="width: 18px;"></i>
            </div>
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0; letter-spacing: -0.01em;">Search Filters</h3>
        </div>
        <form action="{{ route('admin.data-history') }}" method="GET" style="display: flex; gap: 1.5rem; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <div style="background: white; border: 1.5px solid #edf2f7; padding: 6px 12px 6px 18px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; background: #eef2ff; color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i data-lucide="user" style="width: 18px;"></i>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column; min-width: 0;">
                        <label style="font-size: 0.6rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Staff Member</label>
                        <select id="historyStaffFilter" name="user_id" style="width: 100%;">
                            <option value="">All Staff</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div style="flex: 1; min-width: 180px;">
                <div style="background: white; border: 1.5px solid #edf2f7; padding: 10px 18px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; background: #eef2ff; color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i data-lucide="calendar" style="width: 18px;"></i>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <label style="font-size: 0.6rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" style="width: 100%; background: transparent; border: none; color: var(--text-main); font-weight: 800; font-size: 0.95rem; outline: none;">
                    </div>
                </div>
            </div>

            <div style="flex: 1; min-width: 180px;">
                <div style="background: white; border: 1.5px solid #edf2f7; padding: 10px 18px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; background: #eef2ff; color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i data-lucide="calendar" style="width: 18px;"></i>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <label style="font-size: 0.6rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" style="width: 100%; background: transparent; border: none; color: var(--text-main); font-weight: 800; font-size: 0.95rem; outline: none;">
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 3px;">
                <button type="submit" class="btn-primary" style="padding: 0.95rem 2rem; border-radius: 16px; border: none; background: var(--primary); color: white; font-weight: 800; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(79,70,229,0.2);">
                    Apply Filter
                </button>
                @if(request()->hasAny(['user_id', 'date_from', 'date_to']) && (request('user_id') != '' || request('date_from') != '' || request('date_to') != ''))
                    <a href="{{ route('admin.data-history') }}" style="padding: 0.95rem 1.5rem; color: #ef4444; background: #fef2f2; border-radius: 16px; text-decoration: none; font-size: 0.9rem; font-weight: 800; transition: all 0.3s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── Tab Navigation ── --}}
    <div class="pager-tabs-wrap" style="flex-wrap: wrap; display: flex; gap: 6px; background: white; padding: 6px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 2rem; width: fit-content;">
        <button class="pager-tab active" id="tab-stock-history" onclick="switchTab('stock-history')" style="display: flex; align-items: center; gap: 8px; padding: 0.65rem 1.25rem; border-radius: 14px; border: none; background: transparent; font-family: inherit; font-size: 0.88rem; font-weight: 700; color: #64748b; cursor: pointer; transition: all 0.25s ease;">
            <i data-lucide="package" style="width: 16px; height: 16px;"></i>
            Stock History
        </button>
        <button class="pager-tab" id="tab-role-history" onclick="switchTab('role-history')" style="display: flex; align-items: center; gap: 8px; padding: 0.65rem 1.25rem; border-radius: 14px; border: none; background: transparent; font-family: inherit; font-size: 0.88rem; font-weight: 700; color: #64748b; cursor: pointer; transition: all 0.25s ease;">
            <i data-lucide="shield-check" style="width: 16px; height: 16px;"></i>
            User Role &amp; Permission History
        </button>
    </div>

    {{-- ── Tab: Stock History ── --}}
    <div id="panel-stock-history" class="pager-panel active">
        @if($stockHistory->isEmpty())
            <div class="glass-card" style="padding: 4rem 2rem; text-align: center; border-radius: 24px; border: 1px dashed rgba(79, 70, 229, 0.2);">
                <div style="width: 64px; height: 64px; border-radius: 20px; background: rgba(79, 70, 229, 0.05); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                    <i data-lucide="package-open" style="width: 32px; height: 32px;"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">No Stock Adjustments Found</h3>
                <p style="color: var(--text-muted); max-width: 400px; margin: 0 auto;">There are no recorded stock adjustments matching your criteria.</p>
            </div>
        @else
            <div class="glass-card" style="border-radius: 24px; overflow: hidden; background: white; border: 1px solid rgba(0,0,0,0.02); box-shadow: 0 10px 30px rgba(0,0,0,0.01);">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #edf2f7;">
                                <th style="padding: 1.25rem 1.5rem; font-weight: 800; color: #64748b; font-size: 0.75rem; text-transform: uppercase;">Date</th>
                                <th style="padding: 1.25rem 1.5rem; font-weight: 800; color: #64748b; font-size: 0.75rem; text-transform: uppercase;">Item Description</th>
                                <th style="padding: 1.25rem 1.5rem; font-weight: 800; color: #64748b; font-size: 0.75rem; text-transform: uppercase;">Action</th>
                                <th style="padding: 1.25rem 1.5rem; font-weight: 800; color: #64748b; font-size: 0.75rem; text-transform: uppercase;">Qty Diff</th>
                                <th style="padding: 1.25rem 1.5rem; font-weight: 800; color: #64748b; font-size: 0.75rem; text-transform: uppercase;">Balance Diff</th>
                                <th style="padding: 1.25rem 1.5rem; font-weight: 800; color: #64748b; font-size: 0.75rem; text-transform: uppercase;">Changed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockHistory as $record)
                                <tr style="border-bottom: 1px solid #edf2f7; transition: background 0.15s;" onmouseover="this.style.background='#fafcff'" onmouseout="this.style.background=''">
                                    <td style="padding: 1.25rem 1.5rem; color: var(--text-muted); font-size: 0.8rem; font-weight: 700; white-space: nowrap;">
                                        {{ $record->created_at->format('d/m/y H:i') }}
                                    </td>
                                    <td style="padding: 1.25rem 1.5rem;">
                                        <div style="font-weight: 800; color: var(--text-main);">
                                            {{ $record->new_description ?? $record->old_description ?? 'Deleted Item' }}
                                        </div>
                                        <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-top: 2px;">
                                            Unit: {{ $record->new_unit ?? $record->old_unit ?? 'Units' }}
                                        </div>
                                    </td>
                                    <td style="padding: 1.25rem 1.5rem;">
                                        <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.60rem; border-radius: 8px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;
                                            @if($record->action === 'create')
                                                background: #ecfdf5; color: #059669;
                                            @elseif($record->action === 'update')
                                                background: #eff6ff; color: #2563eb;
                                            @else
                                                background: #fef2f2; color: #dc2626;
                                            @endif">
                                            {{ $record->action }}
                                        </span>
                                    </td>
                                    <td style="padding: 1.25rem 1.5rem; font-weight: 800;">
                                        @if($record->action === 'create')
                                            <span style="color: #059669;">+{{ number_format($record->new_qty) }}</span>
                                        @elseif($record->action === 'delete')
                                            <span style="color: #dc2626; text-decoration: line-through;">-{{ number_format($record->old_qty) }}</span>
                                        @else
                                            @if($record->old_qty != $record->new_qty)
                                                <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-decoration: line-through; margin-right: 4px;">{{ number_format($record->old_qty) }}</span>
                                                <span style="color: #2563eb;">&rarr; {{ number_format($record->new_qty) }}</span>
                                            @else
                                                <span style="color: var(--text-muted); font-size: 0.85rem;">{{ number_format($record->new_qty) }} (Unchanged)</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td style="padding: 1.25rem 1.5rem; font-weight: 800;">
                                        @if($record->action === 'create')
                                            <span style="color: #059669;">+{{ number_format($record->new_stock_balance) }}</span>
                                        @elseif($record->action === 'delete')
                                            <span style="color: #dc2626; text-decoration: line-through;">-{{ number_format($record->old_stock_balance) }}</span>
                                        @else
                                            @if($record->old_stock_balance != $record->new_stock_balance)
                                                <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-decoration: line-through; margin-right: 4px;">{{ number_format($record->old_stock_balance) }}</span>
                                                <span style="color: #2563eb;">&rarr; {{ number_format($record->new_stock_balance) }}</span>
                                            @else
                                                <span style="color: var(--text-muted); font-size: 0.85rem;">{{ number_format($record->new_stock_balance) }} (Unchanged)</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--text-main);">
                                        {{ $record->user->name ?? 'System' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination for Stock History -->
            <div style="margin-top: 2rem; display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                <div class="custom-pagination">
                    {{ $stockHistory->appends(request()->except('stock_page'))->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    </div>

    {{-- ── Tab: User Role History ── --}}
    <div id="panel-role-history" class="pager-panel">
        @if($roleHistory->isEmpty())
            <div class="glass-card" style="padding: 4rem 2rem; text-align: center; border-radius: 24px; border: 1px dashed rgba(79, 70, 229, 0.2);">
                <div style="width: 64px; height: 64px; border-radius: 20px; background: rgba(79, 70, 229, 0.05); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                    <i data-lucide="shield-alert" style="width: 32px; height: 32px;"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">No Role Modifications Found</h3>
                <p style="color: var(--text-muted); max-width: 400px; margin: 0 auto;">There are no recorded permission changes matching your criteria.</p>
            </div>
        @else
            <div class="glass-card" style="border-radius: 24px; overflow: hidden; background: white; border: 1px solid rgba(0,0,0,0.02); box-shadow: 0 10px 30px rgba(0,0,0,0.01);">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #edf2f7;">
                                <th style="padding: 1.25rem 1.5rem; font-weight: 800; color: #64748b; font-size: 0.75rem; text-transform: uppercase;">Date</th>
                                <th style="padding: 1.25rem 1.5rem; font-weight: 800; color: #64748b; font-size: 0.75rem; text-transform: uppercase;">Target Staff</th>
                                <th style="padding: 1.25rem 1.5rem; font-weight: 800; color: #64748b; font-size: 0.75rem; text-transform: uppercase;">Modification Type</th>
                                <th style="padding: 1.25rem 1.5rem; font-weight: 800; color: #64748b; font-size: 0.75rem; text-transform: uppercase;">Role Evolution</th>
                                <th style="padding: 1.25rem 1.5rem; font-weight: 800; color: #64748b; font-size: 0.75rem; text-transform: uppercase;">Permission Changes</th>
                                <th style="padding: 1.25rem 1.5rem; font-weight: 800; color: #64748b; font-size: 0.75rem; text-transform: uppercase;">Changed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roleHistory as $record)
                                <tr style="border-bottom: 1px solid #edf2f7; transition: background 0.15s;" onmouseover="this.style.background='#fafcff'" onmouseout="this.style.background=''">
                                    <td style="padding: 1.25rem 1.5rem; color: var(--text-muted); font-size: 0.8rem; font-weight: 700; white-space: nowrap;">
                                        {{ $record->created_at->format('d/m/y H:i') }}
                                    </td>
                                    <td style="padding: 1.25rem 1.5rem; font-weight: 800; color: var(--text-main);">
                                        {{ $record->user->name ?? 'Deleted User' }}
                                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500; font-family: monospace; margin-top: 2px;">
                                            @ @if($record->user){{ $record->user->username }}@else{{ 'deleted' }}@endif
                                        </div>
                                    </td>
                                    <td style="padding: 1.25rem 1.5rem;">
                                        <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.60rem; border-radius: 8px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;
                                            @if($record->action === 'created')
                                                background: #ecfdf5; color: #059669;
                                            @elseif($record->action === 'role_changed')
                                                background: #fdf2f8; color: #db2777;
                                            @elseif($record->action === 'status_changed')
                                                background: #fffbeb; color: #d97706;
                                            @else
                                                background: #eff6ff; color: #2563eb;
                                            @endif">
                                            {{ str_replace('_', ' ', $record->action) }}
                                        </span>
                                    </td>
                                    <td style="padding: 1.25rem 1.5rem; font-weight: 700;">
                                        @if($record->action === 'created')
                                            <span style="color: #059669;">{{ $record->new_role }}</span>
                                        @else
                                            @if($record->old_role != $record->new_role)
                                                <span style="color: var(--text-muted); text-decoration: line-through; font-size: 0.85rem; font-weight: 500;">{{ $record->old_role }}</span>
                                                <span style="color: var(--primary); margin: 0 4px;">&rarr;</span>
                                                <span style="color: var(--text-main);">{{ $record->new_role }}</span>
                                            @else
                                                <span style="color: var(--text-muted); font-size: 0.85rem;">{{ $record->new_role }} (Unchanged)</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td style="padding: 1.25rem 1.5rem;">
                                        @if($record->new_permissions)
                                            <div style="display: flex; flex-direction: column; gap: 4px; max-width: 250px;">
                                                @foreach(['can_add_inventory' => 'Inventory Entry', 'can_operate_logistics' => 'Confirm Collection', 'can_generate_reports' => 'View Reports', 'can_make_requisition' => 'Make Requests', 'can_approve_requisition' => 'Approve Requests'] as $key => $label)
                                                    @php
                                                        $oldVal = $record->old_permissions[$key] ?? false;
                                                        $newVal = $record->new_permissions[$key] ?? false;
                                                    @endphp
                                                    @if($record->action === 'created' || $oldVal != $newVal)
                                                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; padding: 2px 6px; background: #f8fafc; border-radius: 6px; border: 1px solid #f1f5f9;">
                                                            <span style="font-weight: 700; color: #475569;">{{ $label }}</span>
                                                            @if($record->action === 'created')
                                                                <span style="font-weight: 800; color: {{ $newVal ? '#10b981' : '#dc2626' }};">{{ $newVal ? 'Allowed' : 'Blocked' }}</span>
                                                            @else
                                                                <span style="font-weight: 800;">
                                                                    <span style="color: {{ $oldVal ? '#10b981' : '#dc2626' }}; text-decoration: line-through; opacity: 0.6;">{{ $oldVal ? 'Allowed' : 'Blocked' }}</span>
                                                                    <span style="color: #64748b; margin: 0 2px;">&rarr;</span>
                                                                    <span style="color: {{ $newVal ? '#10b981' : '#dc2626' }};">{{ $newVal ? 'Allowed' : 'Blocked' }}</span>
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <span style="color: var(--text-muted); font-size: 0.8rem;">No permission details.</span>
                                        @endif
                                    </td>
                                    <td style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--text-main);">
                                        {{ $record->changer->name ?? 'System' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination for User Role History -->
            <div style="margin-top: 2rem; display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                <div class="custom-pagination">
                    {{ $roleHistory->appends(request()->except('role_page'))->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    /* Tab System Layout styling */
    .pager-tab.active {
        background: var(--primary) !important;
        color: white !important;
        box-shadow: 0 4px 14px rgba(79,70,229,0.25);
    }
    .pager-tab:hover:not(.active) {
        background: #f8fafc;
        color: var(--primary);
    }
    .pager-panel {
        display: none;
        animation: fadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .pager-panel.active {
        display: block;
    }

    /* Premium Glassmorphic Pagination */
    .custom-pagination nav { display: flex; justify-content: center; }
    .custom-pagination ul.pagination { display: flex; gap: 0.5rem; list-style: none; padding: 0; margin: 0; align-items: center; }
    .custom-pagination .page-item .page-link {
        display: flex; align-items: center; justify-content: center;
        min-width: 48px; height: 48px; border-radius: 16px;
        background: white; border: 1.5px solid #edf2f7;
        color: var(--text-main); font-weight: 900; text-decoration: none;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.95rem; box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    }
    .custom-pagination .page-item.active .page-link {
        background: var(--primary); color: white;
        border-color: var(--primary);
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.25);
        transform: scale(1.1);
        z-index: 10;
    }
    .custom-pagination .page-item:not(.active):not(.disabled) .page-link:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-4px);
        background: #f5f3ff;
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.1);
    }
    .custom-pagination .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8fafc;
    }
    .custom-pagination .page-item:first-child .page-link,
    .custom-pagination .page-item:last-child .page-link {
        padding: 0 1.25rem;
        min-width: auto;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Select2 overrides for Staff Member filter ── */
    .select2-container--default .select2-selection--single {
        background: transparent !important;
        border: none !important;
        height: 28px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--text-main) !important;
        font-weight: 800 !important;
        font-size: 0.95rem !important;
        padding-left: 0 !important;
        line-height: 28px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #94a3b8 !important;
        font-weight: 600 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 28px !important;
        right: 4px !important;
    }
    .select2-dropdown {
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: 0 20px 50px rgba(0,0,0,0.1) !important;
        overflow: hidden !important;
        background: white !important;
        padding: 6px !important;
        z-index: 9999 !important;
    }
    .select2-search--dropdown .select2-search__field {
        border-radius: 10px !important;
        padding: 8px 12px !important;
        border: 1.5px solid #e2e8f0 !important;
        font-weight: 600 !important;
        font-size: 0.88rem !important;
        outline: none !important;
    }
    .select2-results__option {
        padding: 9px 14px !important;
        font-size: 0.88rem !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        color: #334155 !important;
        margin-bottom: 2px !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: var(--primary) !important;
        color: white !important;
    }
    .select2-results__option[aria-selected="true"] {
        background: #eef2ff !important;
        color: var(--primary) !important;
    }
</style>

<script>
    function switchTab(tab) {
        document.querySelectorAll('.pager-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.pager-panel').forEach(p => p.classList.remove('active'));
        
        document.getElementById('tab-' + tab).classList.add('active');
        document.getElementById('panel-' + tab).classList.add('active');

        // Store active tab in URL hash to preserve layout on pagination clicks
        window.location.hash = tab;
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Auto-switch to tab from hash in URL if present
        const hash = window.location.hash;
        if (hash === '#role-history') {
            switchTab('role-history');
        } else if (hash === '#stock-history') {
            switchTab('stock-history');
        }
        
        // Also support URL queries for pages
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('role_page')) {
            switchTab('role-history');
        } else if (urlParams.has('stock_page')) {
            switchTab('stock-history');
        }
    });
</script>

<script>
$(function () {
    $('#historyStaffFilter').select2({
        placeholder: 'All Staff',
        allowClear: true,
        width: '100%',
        dropdownAutoWidth: true
    });

    // Auto-submit filter form on selection change
    $('#historyStaffFilter').on('change', function () {
        $(this).closest('form').submit();
    });
});
</script>
@endsection
