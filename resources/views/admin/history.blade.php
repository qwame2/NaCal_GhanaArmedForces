@extends('layouts.admin')

@section('title', 'History')

@section('content')
<div class="animate-slide-up">
    <div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="font-size: 2.25rem; font-weight: 900; letter-spacing: -0.04em; color: var(--text-main); margin-bottom: 0.25rem;">System <span style="color: var(--primary);">History</span></h2>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; display: flex; align-items: center; gap: 0.75rem;">
                Audit log and change tracking of modified inventory items.
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
        <form action="{{ route('admin.history') }}" method="GET" style="display: flex; gap: 1.5rem; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <div style="background: white; border: 1.5px solid #edf2f7; padding: 10px 18px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; background: #eef2ff; color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i data-lucide="user" style="width: 18px;"></i>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <label style="font-size: 0.6rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Staff Member</label>
                        <select name="user_id" onchange="this.form.submit()" style="width: 100%; background: transparent; border: none; padding: 0 20px 0 0; color: var(--text-main); font-weight: 800; font-size: 0.95rem; outline: none; cursor: pointer; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2394a3b8%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right center; background-size: 14px;">
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
                    <a href="{{ route('admin.history') }}" style="padding: 0.95rem 1.5rem; color: #ef4444; background: #fef2f2; border-radius: 16px; text-decoration: none; font-size: 0.9rem; font-weight: 800; transition: all 0.3s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- History Log Section -->
    @if($history->isEmpty())
        <div class="glass-card" style="padding: 4rem 2rem; text-align: center; border-radius: 24px; border: 1px dashed rgba(79, 70, 229, 0.2);">
            <div style="width: 64px; height: 64px; border-radius: 20px; background: rgba(79, 70, 229, 0.05); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                <i data-lucide="history" style="width: 32px; height: 32px;"></i>
            </div>
            <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">No Edit Records Found</h3>
            <p style="color: var(--text-muted); max-width: 400px; margin: 0 auto;">There are no recorded modifications matching your criteria in the system registry.</p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            @foreach($history as $record)
                @if($record instanceof \App\Models\SystemLog)
                    <!-- Display System Log (User Profile / Security / Account changes) -->
                    <div class="glass-card" style="padding: 1.5rem; border-radius: 20px; border: 1px solid rgba(79, 70, 229, 0.08); background: #ffffff; box-shadow: 0 4px 20px rgba(0,0,0,0.01); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 42px; height: 42px; border-radius: 12px;
                                    @if(in_array($record->action, ['CREATE_USER', 'CREATE_TEMP_REQUISITIONER']))
                                        background: rgba(16, 185, 129, 0.08); color: #10b981;
                                    @elseif(in_array($record->action, ['UPDATE_USER', 'UPDATE_PROFILE', 'TOGGLE_USER_STATUS', 'PERMISSION_CHANGE', 'REGENERATE_OTP']))
                                        background: rgba(79, 70, 229, 0.08); color: var(--primary);
                                    @elseif(in_array($record->action, ['CHANGE_PASSWORD', 'PASSWORD_SYNCED', 'AUTHORIZATION']))
                                        background: rgba(245, 158, 11, 0.08); color: #f59e0b;
                                    @else
                                        background: rgba(239, 68, 68, 0.08); color: #ef4444;
                                    @endif
                                    display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.25rem;">

                                    @if(in_array($record->action, ['CREATE_USER', 'CREATE_TEMP_REQUISITIONER']))
                                        <i data-lucide="user-plus" style="width: 20px;"></i>
                                    @elseif(in_array($record->action, ['UPDATE_USER', 'UPDATE_PROFILE']))
                                        <i data-lucide="user-cog" style="width: 20px;"></i>
                                    @elseif(in_array($record->action, ['CHANGE_PASSWORD', 'PASSWORD_SYNCED', 'AUTHORIZATION', 'REGENERATE_OTP']))
                                        <i data-lucide="key-round" style="width: 20px;"></i>
                                    @elseif($record->action === 'PERMISSION_CHANGE')
                                        <i data-lucide="shield-check" style="width: 20px;"></i>
                                    @elseif($record->action === 'TOGGLE_USER_STATUS')
                                        <i data-lucide="user-check" style="width: 20px;"></i>
                                    @elseif(in_array($record->action, ['SELF_DEACTIVATION', 'REVOKE_TEMP_REQUISITIONER']))
                                        <i data-lucide="user-x" style="width: 20px;"></i>
                                    @else
                                        <i data-lucide="shield-alert" style="width: 20px;"></i>
                                    @endif
                                </div>
                                <div>
                                    <h4 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--text-main);">
                                        @if($record->action === 'CREATE_USER')
                                            User Account Registered
                                        @elseif($record->action === 'CREATE_TEMP_REQUISITIONER')
                                            Temporary Account Created
                                        @elseif($record->action === 'UPDATE_USER')
                                            User Account Updated
                                        @elseif($record->action === 'UPDATE_PROFILE')
                                            Profile Details Updated
                                        @elseif($record->action === 'CHANGE_PASSWORD')
                                            Security Credentials Changed
                                        @elseif($record->action === 'PASSWORD_SYNCED')
                                            Security Key Synchronized
                                        @elseif($record->action === 'SELF_DEACTIVATION')
                                            Account Self-Deactivated
                                        @elseif($record->action === 'REVOKE_TEMP_REQUISITIONER')
                                            Temporary Account Revoked
                                        @elseif($record->action === 'REGENERATE_OTP')
                                            Security OTP Regenerated
                                        @elseif($record->action === 'AUTHORIZATION')
                                            Credentials Authorization
                                        @elseif($record->action === 'TOGGLE_USER_STATUS')
                                            Personnel Status Toggled
                                        @elseif($record->action === 'PERMISSION_CHANGE')
                                            Personnel Privilege Modified
                                        @else
                                            System Activity Logged
                                        @endif
                                    </h4>
                                    <p style="margin: 2px 0 0; font-size: 0.8rem; color: var(--text-muted);">
                                        By <b>{{ $record->user->name ?? 'System' }}</b> &bull; {{ $record->updated_at->format('d/m/y @ h:i A') }}
                                    </p>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <span style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 800;
                                    @if($record->severity === 'critical')
                                        background: #fef2f2; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.15);
                                    @elseif($record->severity === 'warning')
                                        background: #fffbeb; color: #d97706; border: 1px solid rgba(217, 119, 6, 0.15);
                                    @else
                                        background: #f0fdf4; color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.15);
                                    @endif">
                                    {{ ucfirst($record->severity) }}
                                </span>
                            </div>
                        </div>

                        <div style="background: #f8fafc; border-left: 4px solid
                            @if(in_array($record->action, ['CREATE_USER', 'CREATE_TEMP_REQUISITIONER']))
                                #10b981
                            @elseif(in_array($record->action, ['UPDATE_USER', 'UPDATE_PROFILE', 'TOGGLE_USER_STATUS', 'PERMISSION_CHANGE', 'REGENERATE_OTP']))
                                var(--primary)
                            @elseif(in_array($record->action, ['CHANGE_PASSWORD', 'PASSWORD_SYNCED', 'AUTHORIZATION']))
                                #f59e0b
                            @else
                                #ef4444
                            @endif; padding: 0.85rem 1.25rem; border-radius: 0 12px 12px 0; font-size: 0.88rem; color: #475569; font-weight: 600;">
                            <span style="font-weight: 800; color: var(--text-main); font-size: 0.75rem; text-transform: uppercase; display: block; margin-bottom: 2px; letter-spacing: 0.05em;">Activity Detail</span>
                            {{ $record->description }}
                        </div>
                    </div>
                @else
                    @php
                        $orig = json_decode($record->original_payload, true);
                        $new = json_decode($record->payload, true);

                        // Diff tracking
                        $batchChanges = [];
                        $itemChanges = [];

                        if (is_array($orig) && is_array($new)) {
                            // Compare batch fields
                            $fields = [
                                'arrival_date' => 'Received Date (Manual)',
                                'ledge_category' => 'Ledge Category',
                                'acquisition_type' => 'Acquisition Type',
                                'supplier_name' => 'Supplier Name',
                                'supplier_status' => 'Delivery Status',
                                'donor_name' => 'Donor Name'
                            ];

                            foreach ($fields as $field => $label) {
                                $oldVal = $orig[$field] ?? '';
                                $newVal = $new[$field] ?? '';
                                if ($oldVal != $newVal) {
                                    // Translate Ledge category if needed
                                    if ($field === 'ledge_category') {
                                        $oldVal = isset($ledgeMap[$oldVal]) ? $ledgeMap[$oldVal] . " ({$oldVal})" : $oldVal;
                                        $newVal = isset($ledgeMap[$newVal]) ? $ledgeMap[$newVal] . " ({$newVal})" : $newVal;
                                    }
                                    $batchChanges[$label] = [
                                        'old' => $oldVal ?: 'None',
                                        'new' => $newVal ?: 'None'
                                    ];
                                }
                            }

                            // Compare items
                            $origItems = collect($orig['items'] ?? [])->keyBy('id');
                            $newItems = collect($new['items'] ?? [])->keyBy('id');

                            foreach ($newItems as $itemId => $newItem) {
                                if ($origItems->has($itemId)) {
                                    $origItem = $origItems->get($itemId);
                                    $itemDiffs = [];
                                    foreach (['description' => 'Description', 'unit' => 'Unit', 'qty' => 'Received Qty', 'stock_balance' => 'Stock Balance', 'variance' => 'Variance', 'remarks' => 'Remarks'] as $fKey => $fLabel) {
                                        $oV = $origItem[$fKey] ?? '';
                                        $nV = $newItem[$fKey] ?? '';
                                        if ($oV != $nV) {
                                            $itemDiffs[$fLabel] = ['old' => $oV, 'new' => $nV];
                                        }
                                    }
                                    if (!empty($itemDiffs)) {
                                        $itemChanges[] = [
                                            'description' => $origItem['description'] ?? $newItem['description'],
                                            'type' => 'modified',
                                            'diffs' => $itemDiffs
                                        ];
                                    }
                                } else {
                                    $itemChanges[] = [
                                        'description' => $newItem['description'],
                                        'type' => 'added',
                                        'item' => $newItem
                                    ];
                                }
                            }

                            foreach ($origItems as $itemId => $origItem) {
                                if (!$newItems->has($itemId)) {
                                    $itemChanges[] = [
                                        'description' => $origItem['description'],
                                        'type' => 'deleted',
                                        'item' => $origItem
                                    ];
                                }
                            }
                        }
                    @endphp

                    <div class="glass-card" style="padding: 1.5rem; border-radius: 20px; border: 1px solid rgba(79, 70, 229, 0.08); background: #ffffff; box-shadow: 0 4px 20px rgba(0,0,0,0.01);">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(79, 70, 229, 0.05); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1rem;">
                                    #{{ $record->item_id }}
                                </div>
                                <div>
                                    <h4 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--text-main);">Inventory Batch Modified</h4>
                                    <p style="margin: 2px 0 0; font-size: 0.8rem; color: var(--text-muted);">
                                        By <b>{{ $record->user->name ?? 'Unknown' }}</b> &bull; {{ $record->updated_at->format('d/m/y @ h:i A') }}
                                    </p>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <span style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 800; background: #eef2ff; color: var(--primary); border: 1px solid rgba(79, 70, 229, 0.15);">
                                    <i data-lucide="check-circle" style="width: 12px;"></i> committed
                                </span>
                                <button class="glass-btn-sm toggle-diff-btn" onclick="toggleDiff({{ $record->id }})" style="padding: 0.45rem 1rem; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.8rem; display: flex; align-items: center; gap: 6px; border: 1.5px solid #edf2f7; background: #ffffff;">
                                    <i data-lucide="eye" style="width: 14px;"></i> Details
                                </button>
                            </div>
                        </div>

                        @if($record->reason)
                            <div style="background: #f8fafc; border-left: 4px solid var(--primary); padding: 0.85rem 1.25rem; border-radius: 0 12px 12px 0; font-size: 0.88rem; color: #475569; margin-bottom: 1rem; font-weight: 600;">
                                <span style="font-weight: 800; color: var(--text-main); font-size: 0.75rem; text-transform: uppercase; display: block; margin-bottom: 2px; letter-spacing: 0.05em;">Justification / Audit Comment</span>
                                "{{ $record->reason }}"
                            </div>
                        @endif

                        <!-- Detail Diffs (Initially Collapsed) -->
                        <div id="diff-details-{{ $record->id }}" style="display: none; border-top: 1px dashed #e2e8f0; padding-top: 1.25rem; margin-top: 1rem;">
                            @if(empty($batchChanges) && empty($itemChanges))
                                <div style="text-align: center; color: var(--text-muted); font-size: 0.9rem; padding: 1rem;">
                                    No field values were changed, or legacy payload layout mismatch.
                                </div>
                            @else
                                <!-- Batch Fields Diff -->
                                @if(!empty($batchChanges))
                                    <div style="margin-bottom: 1.5rem;">
                                        <h5 style="margin: 0 0 0.75rem; font-size: 0.85rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">General Batch Information Changed</h5>
                                        <div class="table-responsive" style="border: 1px solid #edf2f7; border-radius: 16px; overflow: hidden; background: white;">
                                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                                                <thead>
                                                    <tr style="background: #f8fafc; border-bottom: 1px solid #edf2f7;">
                                                        <th style="padding: 0.85rem 1.25rem; font-weight: 800; color: #64748b; font-size: 0.75rem; text-transform: uppercase;">Field</th>
                                                        <th style="padding: 0.85rem 1.25rem; font-weight: 800; color: #ef4444; font-size: 0.75rem; text-transform: uppercase;">Original Value</th>
                                                        <th style="padding: 0.85rem 1.25rem; font-weight: 800; color: #10b981; font-size: 0.75rem; text-transform: uppercase;">Modified Value</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($batchChanges as $lbl => $change)
                                                        <tr style="border-bottom: 1px solid #edf2f7;">
                                                            <td style="padding: 0.85rem 1.25rem; font-weight: 700; color: var(--text-main);">{{ $lbl }}</td>
                                                            <td style="padding: 0.85rem 1.25rem; color: #b91c1c; background: #fff5f5; font-family: monospace; font-weight: 700; text-decoration: line-through;">{{ $change['old'] }}</td>
                                                            <td style="padding: 0.85rem 1.25rem; color: #047857; background: #ecfdf5; font-family: monospace; font-weight: 700;">{{ $change['new'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                <!-- Items Diff -->
                                @if(!empty($itemChanges))
                                    <div>
                                        <h5 style="margin: 0 0 0.75rem; font-size: 0.85rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Associated Inventory Items Changed</h5>
                                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                                            @foreach($itemChanges as $itemChange)
                                                <div style="border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.25rem; background: #fafafb;">
                                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap;">
                                                        <span style="font-weight: 800; color: var(--text-main); font-size: 0.95rem;">
                                                            {{ $itemChange['description'] }}
                                                        </span>
                                                        @if($itemChange['type'] === 'added')
                                                            <span style="background: #d1fae5; color: #065f46; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 8px; text-transform: uppercase;">Added Item</span>
                                                        @elseif($itemChange['type'] === 'deleted')
                                                            <span style="background: #fee2e2; color: #991b1b; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 8px; text-transform: uppercase;">Removed Item</span>
                                                        @else
                                                            <span style="background: #eef2ff; color: var(--primary); font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 8px; text-transform: uppercase;">Modified Item Fields</span>
                                                        @endif
                                                    </div>

                                                    @if($itemChange['type'] === 'added')
                                                        <div style="font-size: 0.85rem; color: #475569; display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.5rem; background: white; padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0;">
                                                            <div><b>Unit:</b> {{ $itemChange['item']['unit'] }}</div>
                                                            <div><b>Received Qty:</b> {{ number_format($itemChange['item']['qty']) }}</div>
                                                            <div><b>Stock Balance:</b> {{ number_format($itemChange['item']['stock_balance']) }}</div>
                                                            <div><b>Variance:</b> {{ number_format($itemChange['item']['variance']) }}</div>
                                                            <div style="grid-column: 1 / -1;"><b>Remarks:</b> {{ $itemChange['item']['remarks'] ?: 'None' }}</div>
                                                        </div>
                                                    @elseif($itemChange['type'] === 'deleted')
                                                        <div style="font-size: 0.85rem; color: #b91c1c; display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.5rem; background: #fff5f5; padding: 0.85rem; border-radius: 12px; border: 1px solid #fecaca; text-decoration: line-through;">
                                                            <div><b>Unit:</b> {{ $itemChange['item']['unit'] }}</div>
                                                            <div><b>Received Qty:</b> {{ number_format($itemChange['item']['qty']) }}</div>
                                                            <div><b>Stock Balance:</b> {{ number_format($itemChange['item']['stock_balance']) }}</div>
                                                            <div><b>Variance:</b> {{ number_format($itemChange['item']['variance']) }}</div>
                                                            <div style="grid-column: 1 / -1;"><b>Remarks:</b> {{ $itemChange['item']['remarks'] ?: 'None' }}</div>
                                                        </div>
                                                    @else
                                                        <!-- Diff view of fields -->
                                                        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; background: white; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
                                                            <thead>
                                                                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: left;">
                                                                    <th style="padding: 0.6rem 1rem; font-weight: 800; color: #64748b; font-size: 0.7rem; text-transform: uppercase;">Field</th>
                                                                    <th style="padding: 0.6rem 1rem; font-weight: 800; color: #ef4444; font-size: 0.7rem; text-transform: uppercase;">Old Value</th>
                                                                    <th style="padding: 0.6rem 1rem; font-weight: 800; color: #10b981; font-size: 0.7rem; text-transform: uppercase;">New Value</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($itemChange['diffs'] as $itemField => $itemChangeVals)
                                                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                                                        <td style="padding: 0.6rem 1rem; font-weight: 700; color: var(--text-main);">{{ $itemField }}</td>
                                                                        <td style="padding: 0.6rem 1rem; color: #b91c1c; background: #fff5f5; font-family: monospace; font-weight: 700; text-decoration: line-through;">{{ $itemChangeVals['old'] }}</td>
                                                                        <td style="padding: 0.6rem 1rem; color: #047857; background: #ecfdf5; font-family: monospace; font-weight: 700;">{{ $itemChangeVals['new'] }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Pagination -->
        <div style="margin-top: 4rem; display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--text-muted); display: flex; align-items: center; gap: 8px; background: white; padding: 0.5rem 1.25rem; border-radius: 100px; border: 1.5px solid #edf2f7; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
                <i data-lucide="info" style="width: 14px; color: var(--primary);"></i>
                Showing <span style="color: var(--text-main);">{{ $history->firstItem() ?? 0 }}</span> to <span style="color: var(--text-main);">{{ $history->lastItem() ?? 0 }}</span> of <span style="color: var(--text-main);">{{ $history->total() }}</span> records
            </div>
            <div class="custom-pagination">
                {{ $history->appends(request()->all())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    @endif
</div>

<style>
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
</style>

<script>
function toggleDiff(recordId) {
    const detailDiv = document.getElementById('diff-details-' + recordId);
    const btn = detailDiv.previousElementSibling.querySelector('.toggle-diff-btn');
    if (detailDiv.style.display === 'none') {
        detailDiv.style.display = 'block';
        detailDiv.classList.add('animate-slide-up');
        if (btn) {
            btn.innerHTML = '<i data-lucide="eye-off" style="width: 14px;"></i> Hide';
            lucide.createIcons();
        }
    } else {
        detailDiv.style.display = 'none';
        if (btn) {
            btn.innerHTML = '<i data-lucide="eye" style="width: 14px;"></i> Details';
            lucide.createIcons();
        }
    }
}
</script>
@endsection
