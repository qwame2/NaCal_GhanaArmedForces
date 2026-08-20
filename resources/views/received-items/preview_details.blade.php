@extends('layouts.admin')

@section('title', 'SRA Receipt Review & Oversight')

@section('content')
@php
    $reqId = $data['id'] ?? 0;
    $status = $data['status'] ?? 'pending';
    $requestType = $data['request_type'] ?? 'creation_submission';
    $batch = $data['batch'] ?? [];
    $recordedByName = $data['recorded_by_name'] ?? 'Personnel';
    $createdAt = $data['created_at'] ?? 'N/A';
    $ledgeName = $data['ledge_name'] ?? ($batch['ledge_category'] ?? 'N/A');
    $items = $batch['items'] ?? [];

    // Filter items in the main table to only show modified items for rollback/resubmitted reviews
    $isRollbackFlow = in_array($status, ['rollback', 'resubmitted']);
    if ($isRollbackFlow && isset($data['previous_batch']['items'])) {
        $filteredItems = [];
        foreach ($items as $idx => $item) {
            $isQtyChanged = false;
            $isStockChanged = false;
            $isDescChanged = false;
            $isRemarksChanged = false;

            $prevItem = collect($data['previous_batch']['items'])->firstWhere('id', $item['id'] ?? null);
            if (!$prevItem && isset($data['previous_batch']['items'][$idx])) {
                $prevItem = $data['previous_batch']['items'][$idx];
            }

            if ($prevItem) {
                if (floatval($item['qty'] ?? 0) !== floatval($prevItem['qty'] ?? 0)) $isQtyChanged = true;
                if (floatval($item['stock_balance'] ?? 0) !== floatval($prevItem['stock_balance'] ?? 0)) $isStockChanged = true;
                if (trim($item['description'] ?? '') !== trim($prevItem['description'] ?? '')) $isDescChanged = true;
                if (trim($item['remarks'] ?? '') !== trim($prevItem['remarks'] ?? '')) $isRemarksChanged = true;
            }

            if ($isQtyChanged || $isStockChanged || $isDescChanged || $isRemarksChanged) {
                $filteredItems[] = $item;
            }
        }
        $items = $filteredItems;
    }

    // Check if it has any discrepancies (book_qty differs from qty or is set)
    $isDiscrepancy = false;
    foreach ($items as $item) {
        if (isset($item['book_qty']) && !is_null($item['book_qty'])) {
            $isDiscrepancy = true;
            break;
        }
    }

    $acqType = $batch['acquisition_type'] ?? 'Supplier';
    $isDonor = ($acqType === 'Donor' || str_contains($batch['supplier_name'] ?? '', '[Donor Action]') || str_contains($batch['supplier_name'] ?? '', '[Donation]'));
    $provider = $isDonor ? ($batch['donor_name'] ?? trim(preg_replace('/\[.*?\]/', '', $batch['supplier_name'] ?? ''))) : trim(preg_replace('/\[.*?\]/', '', $batch['supplier_name'] ?? ''));

    $deliveryPerson = $batch['delivery_person'] ?? '';
    $deliveryPhone = $batch['delivery_phone'] ?? '';
    $driverName = $batch['driver_name'] ?? '';
    $driverPhone = $batch['driver_phone'] ?? '';
@endphp

<div class="preview-container" style="width: 99%; max-width: 100%; margin: 0 auto; padding: 1rem 2rem 3rem; animation: fadeIn 0.5s ease-out;">
    
    <!-- Back Navigation -->
    <div style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;">
        <a href="{{ str_contains(url()->previous(), 'item-entry-approval') ? route('stores.item-entry-approval') : route('admin.messages') }}" style="display: inline-flex; align-items: center; gap: 8px; color: #059669; background: #fff; border: 1.5px solid var(--border-color); padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 0.85rem; font-weight: 800; transition: all 0.2s;" onmouseover="this.style.background='#f8fafc'; this.style.borderColor='var(--primary)';" onmouseout="this.style.background='#fff'; this.style.borderColor='var(--border-color)';">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Back to {{ str_contains(url()->previous(), 'item-entry-approval') ? 'Item Entry Approval' : 'Oversight Board' }}
        </a>
    </div>

    @if($requestType === 'issue_submission')
        <!-- ==================== ISSUE ENTRY DETAILS ==================== -->
        <div style="background: white; padding: 3.5rem 3rem 2.5rem 3rem; border-radius: 24px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; position: relative; box-shadow: var(--shadow-luxe); margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div style="width: 64px; height: 64px; background: #059669; color: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 25px rgba(5,150,105, 0.3);">
                    <i data-lucide="package-minus" style="width: 32px; height: 32px;"></i>
                </div>
                <div>
                    <div style="font-size: 0.75rem; font-weight: 800; color: #059669; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 4px;">Disbursement Authorization</div>
                    <h2 style="margin: 0; font-size: 2rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em;">Issuance Details</h2>
                    <p style="margin: 6px 0 0; font-size: 0.95rem; color: #64748b; font-weight: 500;">Initiated by <b>{{ $recordedByName }}</b> on {{ $createdAt }}</p>
                </div>
            </div>

            <div style="display: flex; gap: 2rem; background: white; padding: 1.25rem 2rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f1f5f9;">
                <div>
                    <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">User Department</label>
                    <div style="font-size: 1.1rem; font-weight: 900; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: #059669;"></div>
                        {{ $batch['beneficiary'] ?? 'N/A' }}
                    </div>
                </div>
                <div style="width: 1px; height: 40px; background: #e2e8f0; align-self: center;"></div>
                <div>
                    <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Approving Authority</label>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #1e293b;">{{ $batch['authority'] ?? 'N/A' }}</div>
                </div>
                <div style="width: 1px; height: 40px; background: #e2e8f0; align-self: center;"></div>
                <div>
                    <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Issuance Type</label>
                    <span style="font-size: 0.85rem; font-weight: 900; color: #059669; background: rgba(5,150,105, 0.1); padding: 4px 12px; border-radius: 8px; border: 1px dashed rgba(5,150,105, 0.3);">{{ $batch['issuance_type'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div style="background: white; padding: 2.5rem 0; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: var(--shadow-luxe); overflow: hidden; margin-bottom: 2rem;">
            <h3 style="font-size: 1rem; font-weight: 900; color: #334155; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 3rem 1.5rem; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="list-checks" style="width: 20px; color: #059669;"></i> Items to Disburse ({{ count($items) }})
            </h3>
            <div style="background: white; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.03); margin: 0 3rem;">
                @foreach($items as $idx => $item)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 1.5rem; border-bottom: 1px dashed #e2e8f0; background: {{ $idx % 2 === 0 ? '#ffffff' : '#f8fafc' }}; transition: all 0.3s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='{{ $idx % 2 === 0 ? '#ffffff' : '#f8fafc' }}'">
                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div style="width: 48px; height: 48px; background: rgba(5,150,105, 0.1); color: #059669; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.1rem; border: 1px solid rgba(5,150,105, 0.2);">
                            {{ $idx + 1 }}
                        </div>
                        <div>
                            <div style="font-weight: 900; font-size: 1.1rem; color: #0f172a; margin-bottom: 4px;">{{ $item['description'] ?? '' }}</div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 0.7rem; font-weight: 800; color: #059669; background: rgba(5,150,105, 0.1); padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05em;">CATEGORY {{ $item['category'] ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: right; background: white; border: 1px solid #e2e8f0; padding: 0.75rem 1.5rem; border-radius: 14px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
                        <span style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px;">Quantity to Issue</span>
                        <div style="font-size: 1.5rem; font-weight: 900; color: #059669; display: flex; align-items: baseline; gap: 4px; justify-content: flex-end;">
                            {{ number_format($item['qty'] ?? 0) }}
                            <span style="font-size: 0.85rem; color: #64748b; font-weight: 700;">{{ $item['unit'] ?? 'Package Types' }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div style="background: white; border: 1px solid var(--border-color); padding: 1.5rem 3rem; display: flex; justify-content: flex-end; align-items: center; gap: 1rem; border-radius: 24px; box-shadow: var(--shadow-luxe);">
            <a href="{{ str_contains(url()->previous(), 'item-entry-approval') ? route('stores.item-entry-approval') : route('admin.messages') }}" style="background: #f1f5f9; color: #0f172a; text-decoration: none; padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 0.9rem;">Close</a>
        </div>

    @else
        <!-- ==================== STOCK ENTRY DETAILS ==================== -->
        <div class="preview-header" style="background: white; padding: 2.5rem; border-radius: 24px; border: 1px solid var(--border-color); box-shadow: var(--shadow-luxe); margin-bottom: 2rem; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; right: 0; padding: 1.5rem;">
                <div style="background: #fef2f2; color: #ef4444; padding: 6px 16px; border-radius: 99px; font-size: 0.75rem; font-weight: 800; border: 1px solid #fee2e2; letter-spacing: 0.05em;">
                    DRAFT PREVIEW
                </div>
            </div>

            <div style="display: flex; align-items: flex-start; gap: 2rem;">
                <div style="width: 80px; height: 80px; background: var(--primary-glow); color: var(--primary); border-radius: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i data-lucide="package-search" style="width: 40px; height: 40px;"></i>
                </div>
                <div style="flex: 1;">
                    <h1 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; margin: 0 0 0.5rem 0; letter-spacing: -0.02em;">Stock Entry Details</h1>
                    <p style="color: var(--text-muted); font-size: 1rem; font-weight: 500; margin: 0;">Personnel <b>{{ $recordedByName }}</b> is proposing a new inventory batch entry.</p>

                    <div style="display: flex; gap: 2rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; flex-wrap: wrap;">
                        <div>
                            <span style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Supply Status</span>
                            <span style="font-size: 0.95rem; font-weight: 700; color: #0f172a;">{{ $batch['supplier_status'] ?? 'Full Delivery' }}</span>
                        </div>
                        <div>
                            <span style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Received Date</span>
                            <span style="font-size: 0.95rem; font-weight: 700; color: #0f172a;">
                                @if(!empty($batch['arrival_date']))
                                    {{ \Carbon\Carbon::parse($batch['arrival_date'])->format('d/m/y') }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div>
                            <span style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Entry Date</span>
                            <span style="font-size: 0.95rem; font-weight: 700; color: #0f172a;">
                                @if(!empty($batch['entry_date']))
                                    {{ \Carbon\Carbon::parse($batch['entry_date'])->format('d/m/y H:i') }}
                                @else
                                    {{ $createdAt }}
                                @endif
                            </span>
                        </div>
                        <div>
                            <span style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Category</span>
                            <span style="font-size: 0.95rem; font-weight: 700; color: var(--primary); background: var(--primary-glow); padding: 2px 10px; border-radius: 6px;">{{ $ledgeName }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Supplier / Donor Details Stats (populated via AJAX) -->
        <div id="supplier-stats-inline"></div>

        <!-- Proposed Changes Section (Only for edit request types) -->
        @if($requestType === 'edit_submission' && isset($data['previous_batch']))
            <h3 style="font-size: 0.95rem; font-weight: 900; color: #059669; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="edit-3" style="width: 18px;"></i> Proposed Changes
            </h3>
        @endif

        <!-- Proposed Items Table -->
        <div style="background: white; border-radius: 24px; border: 1px solid var(--border-color); box-shadow: var(--shadow-luxe); overflow: hidden; margin-bottom: 2rem;">
            <div style="padding: 1.5rem 2rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 900; color: #0f172a;">Items in This Entry ({{ count($items) }})</h3>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button class="sra-rollback-btn-right" onclick="window.rollbackEntry({{ $reqId }})" style="background: #059669; color: white; border: none; padding: 8px 16px; border-radius: 10px; cursor: pointer; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; transition: all 0.2s; box-shadow: 0 4px 12px rgba(5,150,105, 0.25);">
                        <i data-lucide="rotate-ccw" style="width: 14px; height: 14px;"></i> Rollback Group
                    </button>
                    <span style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem; font-weight: 800; padding: 6px 16px; border-radius: 99px;">{{ count($items) }} ITEMS</span>
                </div>
            </div>

            <div style="padding: 0; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 900px;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e2e8f0; background: #fafbfc;">
                            <th style="padding: 1rem 1.5rem; width: 40px; text-align: center;">
                                <input type="checkbox" id="rollback-select-all" style="width: 16px; height: 16px; cursor: pointer; accent-color: #ef4444;" onclick="let boxes = document.querySelectorAll('.item-rollback-checkbox'); boxes.forEach(b => b.checked = this.checked); updateRollbackBtn();">
                            </th>
                            <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; width: 30%;">Description</th>
                            <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Package Type</th>
                            <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Store Location</th>
                            @php
                                $isPartial = ($batch['supplier_status'] ?? '') === 'Partial Delivery';
                            @endphp
                            <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">
                                @if($isPartial)
                                    Expected / Invoice Qty
                                @elseif($isDiscrepancy)
                                    Received Qty (Actual)
                                @else
                                    Received Qty
                                @endif
                            </th>
                            @if($isDiscrepancy)
                                <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Book Qty (Ledger)</th>
                                <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: #ef4444; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Discrepancy</th>
                            @else
                                <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">
                                    @if($isPartial)
                                        Physically Received Qty
                                    @else
                                        Stock Bal.
                                    @endif
                                </th>
                            @endif
                            <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Total System</th>
                            <th style="padding: 1.25rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; width: 20%;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        @php
                            $isQtyChanged = false;
                            $isStockChanged = false;
                            $isDescChanged = false;
                            $isRemarksChanged = false;

                            if (isset($data['previous_batch']['items'])) {
                                $prevItem = collect($data['previous_batch']['items'])->firstWhere('id', $item['id'] ?? null);
                                if ($prevItem) {
                                    if (floatval($item['qty'] ?? 0) !== floatval($prevItem['qty'] ?? 0)) $isQtyChanged = true;
                                    if (floatval($item['stock_balance'] ?? 0) !== floatval($prevItem['stock_balance'] ?? 0)) $isStockChanged = true;
                                    if (trim($item['description'] ?? '') !== trim($prevItem['description'] ?? '')) $isDescChanged = true;
                                    if (trim($item['remarks'] ?? '') !== trim($prevItem['remarks'] ?? '')) $isRemarksChanged = true;
                                }
                            }

                            $varianceVal = floatval($item['qty'] ?? 0) - floatval($item['book_qty'] ?? 0);
                            $displayVariance = ($varianceVal > 0 ? '+' : '') . $varianceVal;
                            $varianceColor = $varianceVal === 0 ? '#059669' : ($varianceVal > 0 ? '#059669' : '#ef4444');
                        @endphp
                        <tr style="border-bottom: 1px solid #f8fafc; transition: 0.2s;" onmouseover="this.style.background='#fcfdff'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 1rem 1.5rem; text-align: center;">
                                <input type="checkbox" class="item-rollback-checkbox" data-desc="{{ $item['description'] ?? '' }}" style="width: 16px; height: 16px; cursor: pointer; accent-color: #ef4444;" onchange="updateRollbackBtn();">
                            </td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 700; color: #0f172a; {!! $isDescChanged ? 'background: rgba(59, 130, 246, 0.08); border-left: 3px solid #2563eb;' : '' !!}">
                                <div>{{ $item['description'] ?? '' }}</div>
                                <div class="item-sns-display" data-sns="{{ $item['serial_number'] ?? '' }}"></div>
                                @if($isDescChanged)
                                    <div style="font-size: 0.65rem; color: #2563eb; margin-top: 4px; font-weight: 800;">Modified</div>
                                @endif
                            </td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem; color: #64748b;">
                                {{ $item['unit'] ?? 'Package Types' }}
                            </td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem;">
                                @php
                                    $rawLoc = $item['store_location'] ?? ($item['location'] ?? 'Store A');
                                    $stLoc = str_replace('Stores', 'Store', $rawLoc);
                                    $isStoreB = str_contains($stLoc, 'B');
                                @endphp
                                <span style="font-size: 0.75rem; font-weight: 800; color: {{ $isStoreB ? '#3b82f6' : '#059669' }}; background: {{ $isStoreB ? 'rgba(59, 130, 246, 0.1)' : 'rgba(5,150,105, 0.1)' }}; padding: 3px 10px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                    <i data-lucide="map-pin" style="width: 12px; height: 12px;"></i>
                                    {{ $stLoc }}
                                </span>
                            </td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; text-align: right; color: {{ $isQtyChanged ? '#2563eb' : '#0f172a' }}; {!! $isQtyChanged ? 'background: rgba(59, 130, 246, 0.08); border-left: 2px solid #2563eb;' : '' !!}">
                                {{ number_format($item['qty'] ?? 0) }}
                            </td>
                            @if($isDiscrepancy)
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #059669; text-align: right;">
                                    {{ number_format($item['book_qty'] ?? 0) }}
                                </td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: {{ $varianceColor }}; text-align: right;">
                                    {{ $displayVariance }}
                                </td>
                            @else
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; text-align: right; color: {{ $isStockChanged ? '#2563eb' : '#0f172a' }}; {!! $isStockChanged ? 'background: rgba(59, 130, 246, 0.08); border-left: 2px solid #2563eb;' : '' !!}">
                                    {{ number_format($item['stock_balance'] ?? 0) }}
                                </td>
                            @endif
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #0284c7; text-align: right;">
                                {{ number_format($item['total_in_system'] ?? 0) }}
                            </td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.8rem; color: #64748b; font-style: italic; max-width: 200px; word-break: break-word; {!! $isRemarksChanged ? 'background: rgba(59, 130, 246, 0.08); border-left: 2px solid #2563eb;' : '' !!}">
                                {{ $item['remarks'] ?: '-- No specific notes --' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background: #f8fafc; border-top: 2px solid #e2e8f0;">
                        <tr>
                            <td colspan="4" style="padding: 1rem 1.5rem; font-size: 0.8rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">
                                Total Items in This Entry
                            </td>
                            <td style="padding: 1rem 1.5rem; text-align: right; font-size: 1rem; font-weight: 900; color: #059669;">
                                {{ number_format(collect($items)->sum('qty')) }}
                            </td>
                            @if($isDiscrepancy)
                                <td style="padding: 1rem 1.5rem; text-align: right; font-size: 0.85rem; font-weight: 800; color: #94a3b8;">
                                    {{ number_format(collect($items)->sum('book_qty')) }} <span style="font-size: 0.7rem; font-weight: 600;">book count</span>
                                </td>
                                <td style="padding: 1rem 1.5rem; text-align: right; font-size: 0.85rem; font-weight: 800; color: #94a3b8;">
                                    {{ number_format(collect($items)->sum(fn($i) => floatval($i['qty'] ?? 0) - floatval($i['book_qty'] ?? 0))) }} <span style="font-size: 0.7rem; font-weight: 600;">discrepancy</span>
                                </td>
                            @else
                                <td style="padding: 1rem 1.5rem; text-align: right; font-size: 0.85rem; font-weight: 800; color: #94a3b8;">
                                    {{ number_format(collect($items)->sum('stock_balance')) }} <span style="font-size: 0.7rem; font-weight: 600;">{{ $isPartial ? 'phys. received' : 'total bal.' }}</span>
                                </td>
                            @endif
                            <td style="padding: 1rem 1.5rem;"></td>
                            <td style="padding: 1rem 1.5rem;"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Original Entry state block (Only for edit request types or rollback flows) -->
        @if(($requestType === 'edit_submission' || in_array($status, ['rollback', 'resubmitted'])) && isset($data['previous_batch']))
            @php
                $prevItems = $data['previous_batch']['items'] ?? [];
            @endphp
            <div style="margin-top: 2rem; margin-bottom: 2rem;">
                <h3 style="font-size: 0.95rem; font-weight: 900; color: #ef4444; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="history" style="width: 18px;"></i> Original / Prior State
                </h3>
                <div style="background: white; border-radius: 20px; border: 1px solid #fee2e2; overflow: hidden; box-shadow: 0 4px 20px rgba(239, 68, 68, 0.01);">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid #fee2e2; background: #fff5f5;">
                                <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 850; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em;">Description</th>
                                <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 850; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em;">Package Type</th>
                                <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 850; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em;">Store Location</th>
                                <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 850; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">
                                    @if($isPartial)
                                        Expected / Invoice Qty
                                    @else
                                        Received Qty
                                    @endif
                                </th>
                                @if($isDiscrepancy)
                                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 850; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Book Qty</th>
                                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 850; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Discrepancy</th>
                                @else
                                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 850; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">
                                        @if($isPartial)
                                            Physically Received Qty
                                        @else
                                            Stock Bal.
                                        @endif
                                    </th>
                                @endif
                                <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 850; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Total System</th>
                                <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 850; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prevItems as $prevIdx => $prevItem)
                            @php
                                $prevVarianceVal = floatval($prevItem['qty'] ?? 0) - floatval($prevItem['book_qty'] ?? 0);
                                
                                $isPrevQtyChanged = false;
                                $isPrevStockChanged = false;
                                $isPrevDescChanged = false;
                                $isPrevRemarksChanged = false;

                                // Match by ID or by index
                                $matchingCorrItem = null;
                                if (isset($prevItem['id'])) {
                                    $matchingCorrItem = collect($items)->firstWhere('id', $prevItem['id']);
                                }
                                if (!$matchingCorrItem && isset($items[$prevIdx])) {
                                    $matchingCorrItem = $items[$prevIdx];
                                }

                                if ($matchingCorrItem) {
                                    if (floatval($matchingCorrItem['qty'] ?? 0) !== floatval($prevItem['qty'] ?? 0)) $isPrevQtyChanged = true;
                                    if (floatval($matchingCorrItem['stock_balance'] ?? 0) !== floatval($prevItem['stock_balance'] ?? 0)) $isPrevStockChanged = true;
                                    if (trim($matchingCorrItem['description'] ?? '') !== trim($prevItem['description'] ?? '')) $isPrevDescChanged = true;
                                    if (trim($matchingCorrItem['remarks'] ?? '') !== trim($prevItem['remarks'] ?? '')) $isPrevRemarksChanged = true;
                                }
                            @endphp
                            <tr style="border-bottom: 1px solid #fee2e2;">
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 700; color: #7f1d1d; {!! $isPrevDescChanged ? 'background: rgba(239, 68, 68, 0.08); border-left: 3px solid #dc2626;' : '' !!}">
                                    <div>{{ $prevItem['description'] ?? '' }}</div>
                                    <div class="item-sns-display" data-sns="{{ $prevItem['serial_number'] ?? '' }}"></div>
                                    @if($isPrevDescChanged)
                                        <div style="font-size: 0.65rem; color: #dc2626; margin-top: 4px; font-weight: 800;">Original Value</div>
                                    @endif
                                </td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; color: #991b1b;">
                                    {{ $prevItem['unit'] ?? 'Package Types' }}
                                </td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem;">
                                    @php
                                        $rawPrevLoc = $prevItem['store_location'] ?? ($prevItem['location'] ?? 'Store A');
                                        $stPrevLoc = str_replace('Stores', 'Store', $rawPrevLoc);
                                        $isPrevStoreB = str_contains($stPrevLoc, 'B');
                                    @endphp
                                    <span style="font-size: 0.75rem; font-weight: 800; color: {{ $isPrevStoreB ? '#3b82f6' : '#b91c1c' }}; background: {{ $isPrevStoreB ? 'rgba(59, 130, 246, 0.1)' : '#fff5f5' }}; padding: 3px 10px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; border: 1px solid {{ $isPrevStoreB ? 'rgba(59, 130, 246, 0.2)' : '#fee2e2' }};">
                                        <i data-lucide="map-pin" style="width: 12px; height: 12px;"></i>
                                        {{ $stPrevLoc }}
                                    </span>
                                </td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: {{ $isPrevQtyChanged ? '#dc2626' : '#991b1b' }}; text-align: right; {!! $isPrevQtyChanged ? 'background: rgba(239, 68, 68, 0.08); border-left: 2px solid #dc2626;' : '' !!}">
                                    {{ number_format($prevItem['qty'] ?? 0) }}
                                </td>
                                @if($isDiscrepancy)
                                    <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #991b1b; text-align: right;">
                                        {{ number_format($prevItem['book_qty'] ?? 0) }}
                                    </td>
                                    <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #991b1b; text-align: right;">--</td>
                                @else
                                    <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: {{ $isPrevStockChanged ? '#dc2626' : '#991b1b' }}; text-align: right; {!! $isPrevStockChanged ? 'background: rgba(239, 68, 68, 0.08); border-left: 2px solid #dc2626;' : '' !!}">
                                        {{ number_format($prevItem['stock_balance'] ?? 0) }}
                                    </td>
                                @endif
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #991b1b; text-align: right;">
                                    {{ number_format($prevItem['total_in_system'] ?? 0) }}
                                </td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.8rem; color: #b91c1c; font-style: italic; max-width: 200px; word-break: break-word; {!! $isPrevRemarksChanged ? 'background: rgba(239, 68, 68, 0.08); border-left: 2px solid #dc2626;' : '' !!}">
                                    {{ $prevItem['remarks'] ?: '-- No specific notes --' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Warning Review Banner -->
        <div style="margin-top: 2rem; padding: 1.5rem; background: #ecfdf5; border-radius: 16px; border: 1px solid #fef3c7; display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
            <div style="width: 32px; height: 32px; background: #059669; color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="info" style="width: 18px;"></i>
            </div>
            <div style="flex: 1;">
                <span style="display: block; font-size: 0.85rem; font-weight: 700; color: #92400e;">Entry Review in Progress</span>
                <span style="font-size: 0.75rem; color: #b45309;">Please check the quantities carefully before you approve this entry.</span>
            </div>
        </div>

        <!-- Administrative Review Actions Panel -->
        @if($status === 'pending' || $status === 'resubmitted')
        <div class="actions-panel" style="background: white; border: 1px solid var(--border-color); padding: 1.75rem 2.5rem; display: flex; justify-content: flex-end; align-items: center; gap: 1rem; border-radius: 24px; box-shadow: var(--shadow-luxe); margin-top: 2rem;">
            <button onclick="window.rollbackEntry({{ $reqId }})" style="margin-right: auto; background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                <i data-lucide="rotate-ccw" style="width: 18px;"></i> Rollback
            </button>
            
            @php
                $approveFn = $requestType === 'edit_submission' ? "processEditRequestApproval($reqId)" : "processApproval('approved')";
            @endphp
            <button id="approveBtn" onclick="{!! $approveFn !!}" style="background: #059669; color: white; border: none; padding: 12px 28px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#065f46'" onmouseout="this.style.background='#059669'">
                <i data-lucide="check-circle" style="width: 18px;"></i> {{ $requestType === 'edit_submission' ? 'Approve Changes' : 'Approve Entry' }}
            </button>
        </div>
        @else
        @php
            $boxBg = 'rgba(239, 68, 68, 0.08)';
            $boxBorder = '#ef4444';
            $boxText = '#ef4444';
            $boxIcon = 'alert-circle';
            $decisionText = 'REJECTED';

            if ($status === 'approved' || $status === 'completed') {
                $boxBg = 'rgba(5, 150, 105, 0.08)';
                $boxBorder = '#059669';
                $boxText = '#059669';
                $boxIcon = 'check-circle';
                $decisionText = 'APPROVED & SAVED';
            } elseif ($status === 'rollback') {
                $boxBg = 'rgba(245, 158, 11, 0.08)';
                $boxBorder = '#f59e0b';
                $boxText = '#d97706';
                $boxIcon = 'rotate-ccw';
                $decisionText = 'ROLLED BACK FOR CORRECTION';
            }
        @endphp
        <div style="margin-top: 2rem; padding: 1.75rem; background: {{ $boxBg }}; border-radius: 24px; border: 1.5px solid {{ $boxBorder }}; display: flex; align-items: center; justify-content: center; gap: 1rem; box-shadow: var(--shadow-luxe);">
            <div style="font-weight: 950; color: {{ $boxText }}; text-transform: uppercase; font-size: 1rem; display: flex; align-items: center; gap: 8px; letter-spacing: 0.05em;">
                <i data-lucide="{{ $boxIcon }}" style="width: 22px; height: 22px;"></i>
                Oversight Decision: {{ $decisionText }}
            </div>
        </div>
        @endif
    @endif

</div>

<script>
    function updateRollbackBtn() {
        const checkedCount = document.querySelectorAll('.item-rollback-checkbox:checked').length;
        const btn = document.querySelector('.sra-rollback-btn-right');
        if (btn) {
            if (checkedCount > 0) {
                btn.innerHTML = `<i data-lucide="rotate-ccw" style="width: 14px; height: 14px;"></i> Rollback Selected (${checkedCount})`;
                btn.style.background = '#ef4444';
                btn.style.boxShadow = '0 4px 12px rgba(239, 68, 68, 0.25)';
            } else {
                btn.innerHTML = `<i data-lucide="rotate-ccw" style="width: 14px; height: 14px;"></i> Rollback Group`;
                btn.style.background = '#059669';
                btn.style.boxShadow = '0 4px 12px rgba(5,150,105, 0.25)';
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    function formatSerialNumbersDisplay(serialStr, isProposed = true) {
        if (!serialStr) return '';
        const sns = serialStr.split(',').map(s => s.trim()).filter(Boolean);
        if (sns.length === 0) return '';

        const badgeBg = isProposed ? 'rgba(5,150,105, 0.05)' : 'rgba(239, 68, 68, 0.05)';
        const badgeColor = isProposed ? '#059669' : '#ef4444';
        const borderColor = isProposed ? 'rgba(5,150,105, 0.15)' : 'rgba(239, 68, 68, 0.15)';

        function makeChipHtml(sn) {
            const match = sn.match(/^(.*?)\s*\(Rim:\s*(\d+)\)$/i);
            if (match) {
                const snPart = match[1].trim();
                const rimPart = match[2].trim();
                return `
                    <div style="display: inline-flex; align-items: center; gap: 6px; background: ${badgeBg}; color: ${badgeColor}; border: 1px solid ${borderColor}; font-size: 0.76rem; padding: 4px 10px; border-radius: 8px; font-weight: 800; margin: 2px 0;">
                        <i data-lucide="disc" style="width: 12px; height: 12px; color: ${badgeColor}; flex-shrink: 0;"></i>
                        <span>S/N: <strong style="color: #0f172a;">${snPart || 'N/A'}</strong></span>
                        <span style="color: #cbd5e1; font-weight: 300;">|</span>
                        <span>Rim: <strong style="color: #0f172a;">${rimPart}"</strong></span>
                    </div>
                `;
            } else {
                return `
                    <div style="display: inline-flex; align-items: center; gap: 6px; background: ${badgeBg}; color: ${badgeColor}; border: 1px solid ${borderColor}; font-size: 0.76rem; padding: 4px 10px; border-radius: 8px; font-weight: 800; margin: 2px 0;">
                        <i data-lucide="barcode" style="width: 12px; height: 12px; color: ${badgeColor}; flex-shrink: 0;"></i>
                        <span>S/N: <strong style="color: #0f172a;">${sn}</strong></span>
                    </div>
                `;
            }
        }

        const showSns = sns.slice(0, 3);
        const hiddenSns = sns.slice(3);

        const firstThreeHtml = showSns.map(makeChipHtml).join(' ');

        if (hiddenSns.length > 0) {
            const remainingHtml = hiddenSns.map(makeChipHtml).join(' ');
            return `
                <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 4px;">
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                        ${firstThreeHtml}
                        <button type="button" onclick="let next = this.nextElementSibling; let isHidden = next.style.display === 'none'; next.style.display = isHidden ? 'flex' : 'none'; this.innerHTML = isHidden ? 'Show Less' : 'Show More (+${hiddenSns.length})';" style="background: transparent; border: 1.5px dashed ${borderColor}; color: ${badgeColor}; font-size: 0.72rem; padding: 4px 10px; border-radius: 8px; font-weight: 800; cursor: pointer; transition: 0.2s; outline: none; margin: 2px 0; display: inline-flex; align-items: center; gap: 4px;" onmouseover="this.style.background='${badgeBg}'" onmouseout="this.style.background='transparent'">
                            Show More (+${hiddenSns.length})
                        </button>
                        <div class="hidden-sns-list" style="display: none; flex-wrap: wrap; gap: 6px; align-items: center; width: 100%;">
                            ${remainingHtml}
                        </div>
                    </div>
                </div>
            `;
        } else {
            return `
                <div style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                    ${firstThreeHtml}
                </div>
            `;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.item-sns-display').forEach(el => {
            const sns = el.getAttribute('data-sns');
            el.innerHTML = formatSerialNumbersDisplay(sns, true);
        });
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Load supplier/donor stats asynchronously
        const providerName = "{{ $provider }}";
        if (providerName && providerName !== 'N/A') {
            const cleanProviderName = providerName.replace(/\s\[.*\]$/, '').trim();
            const inlineDiv = document.getElementById('supplier-stats-inline');
            if (inlineDiv) {
                // Populate initial info first
                inlineDiv.innerHTML = `
                    <div style="margin-bottom: 2rem; background: #f8fafc; border-radius: 18px; border: 1px solid #e2e8f0; overflow: hidden;">
                        <div style="display: flex; align-items: stretch; gap: 0;">
                            <div style="background: #f0f9ff; padding: 1.5rem 1.25rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; min-width: 130px; flex-shrink: 0; border-right: 1px solid #bae6fd; position: relative; overflow: hidden;">
                                <div style="position: absolute; top: -18px; right: -18px; width: 80px; height: 80px; border-radius: 50%; background: rgba(14, 165, 233, 0.08);"></div>
                                <div style="width: 52px; height: 52px; border-radius: 50%; background: white; border: 3px solid #7dd3fc; display: flex; align-items: center; justify-content: center; z-index: 1;">
                                    <i data-lucide="building-2" style="width: 24px; height: 24px; color: #0284c7;"></i>
                                </div>
                                <div style="background: #e0f2fe; border: 1px solid #bae6fd; color: #0369a1; font-size: 0.6rem; font-weight: 800; padding: 3px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.1em; z-index: 1;">{{ $batch['acquisition_type'] ?? 'Supplier' }}</div>
                            </div>
                            <div style="flex: 1; background: white; padding: 1.25rem 1.75rem; border-left: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: center; gap: 0.85rem;">
                                <div>
                                    <div style="font-size: 0.6rem; font-weight: 800; color: #06b6d4; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 3px;">{{ $isDonor ? 'Donor Name' : 'Company Name' }}</div>
                                    <div style="font-size: 1.15rem; font-weight: 900; color: #0f172a; letter-spacing: -0.02em;">${cleanProviderName}</div>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem 1.5rem;">
                                    <div>
                                        <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Contact Person</div>
                                        <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">{{ $deliveryPerson ?: 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Contact Person Number</div>
                                        <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">{{ $deliveryPhone ?: 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Delivery Person Name</div>
                                        <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">{{ $driverName ?: 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Delivery Person Number</div>
                                        <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">{{ $driverPhone ?: 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons();

                fetch(`/api/supplier-stats/${encodeURIComponent(cleanProviderName)}`)
                    .then(r => r.json())
                    .then(sData => {
                        if (!sData.error) {
                            const s = sData.supplier;
                            const stats = sData.stats;
                            inlineDiv.innerHTML = `
                                <div style="margin-bottom: 2rem; background: #f8fafc; border-radius: 18px; border: 1px solid #e2e8f0; overflow: hidden;">
                                    <div style="display: flex; align-items: stretch; gap: 0;">
                                        <div style="background: #f0f9ff; padding: 1.5rem 1.25rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; min-width: 130px; flex-shrink: 0; border-right: 1px solid #bae6fd; position: relative; overflow: hidden;">
                                            <div style="position: absolute; top: -18px; right: -18px; width: 80px; height: 80px; border-radius: 50%; background: rgba(14, 165, 233, 0.08);"></div>
                                            <div style="width: 52px; height: 52px; border-radius: 50%; background: white; border: 3px solid #7dd3fc; display: flex; align-items: center; justify-content: center; z-index: 1;">
                                                <i data-lucide="building-2" style="width: 24px; height: 24px; color: #0284c7;"></i>
                                            </div>
                                            <div style="background: #e0f2fe; border: 1px solid #bae6fd; color: #0369a1; font-size: 0.6rem; font-weight: 800; padding: 3px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.1em; z-index: 1;">{{ $batch['acquisition_type'] ?? 'Supplier' }}</div>
                                        </div>
                                        <div style="flex: 1; background: white; padding: 1.25rem 1.75rem; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: center; gap: 0.85rem;">
                                            <div>
                                                <div style="font-size: 0.6rem; font-weight: 800; color: #06b6d4; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 3px;">{{ $isDonor ? 'Donor Name' : 'Company Name' }}</div>
                                                <div style="font-size: 1.15rem; font-weight: 900; color: #0f172a; letter-spacing: -0.02em;">${s.name}</div>
                                            </div>
                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem 1.5rem;">
                                                <div>
                                                    <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Contact Person</div>
                                                    <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">${s.contact_person || s.delivery_person || 'N/A'}</div>
                                                </div>
                                                <div>
                                                    <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Contact Person Number</div>
                                                    <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">${s.contact_phone || s.delivery_phone || 'N/A'}</div>
                                                </div>
                                                <div>
                                                    <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Delivery Person Name</div>
                                                    <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">{{ $driverName ?: 'N/A' }}</div>
                                                </div>
                                                <div>
                                                    <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Delivery Person Number</div>
                                                    <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">{{ $driverPhone ?: 'N/A' }}</div>
                                                </div>
                                                <div>
                                                    <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Company Phone</div>
                                                    <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">${s.phone || 'N/A'}</div>
                                                </div>
                                                <div>
                                                    <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Email</div>
                                                    <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">${s.email || 'N/A'}</div>
                                                </div>
                                                <div>
                                                    <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Address</div>
                                                    <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">${s.address || 'N/A'}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="background: #f0fdf4; padding: 1.25rem 1.5rem; display: flex; flex-direction: column; justify-content: center; gap: 1rem; min-width: 140px; flex-shrink: 0;">
                                            <div style="text-align: center;">
                                                <div style="font-size: 2rem; font-weight: 900; color: #059669; line-height: 1;">${stats.total_deliveries.toLocaleString()}</div>
                                                <div style="font-size: 0.6rem; font-weight: 800; color: #047857; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 4px;">Total Deliveries</div>
                                            </div>
                                            <div style="height: 1px; background: #bbf7d0;"></div>
                                            <div style="text-align: center;">
                                                <div style="font-size: 0.85rem; font-weight: 800; color: #065f46; line-height: 1.2;">${stats.last_delivery}</div>
                                                <div style="font-size: 0.6rem; font-weight: 800; color: #047857; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 4px;">Last Delivery</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        }
                    });
            }
        }
    });

    window.rollbackEntry = function(reqId) {
        const selectedItems = Array.from(document.querySelectorAll('.item-rollback-checkbox:checked')).map(cb => cb.getAttribute('data-desc'));
        let url = `{{ url('/sra-creation') }}/${reqId}/rollback`;
        if (selectedItems.length > 0) {
            const params = new URLSearchParams();
            selectedItems.forEach(item => params.append('selected_items[]', item));
            url += '?' + params.toString();
        }
        window.location.href = url;
    };

    function processApproval(status) {
        const id = '{{ $reqId }}';
        if (status === 'rejected') {
            Swal.fire({
                html: `
                    <div style="text-align: left;">
                        <div style="background: #ef4444; margin: -1.25em -1.25em 1.5em; padding: 2rem 2rem 1.5rem; border-radius: 4px 4px 0 0; position: relative; overflow: hidden;">
                            <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: rgba(255,255,255,0.06); border-radius: 50%;"></div>
                            <div style="position: absolute; bottom: -30px; left: -10px; width: 80px; height: 80px; background: rgba(255,255,255,0.04); border-radius: 50%;"></div>
                            <div style="display: flex; align-items: center; gap: 14px; position: relative;">
                                <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.15); border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg style="width: 26px; height: 26px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </div>
                                <div>
                                    <div style="font-size: 0.7rem; font-weight: 800; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 3px;">Admin Action Required</div>
                                    <div style="font-size: 1.3rem; font-weight: 900; color: white; letter-spacing: -0.02em;">Reject Stock Entry</div>
                                </div>
                            </div>
                        </div>

                        <p style="font-size: 0.9rem; color: #64748b; line-height: 1.6; margin-bottom: 1.25rem; padding: 0 0.25rem;">
                            Provide a clear reason for rejecting the submission. This will be sent to the user immediately.
                        </p>

                        <textarea id="swal-reject-reason" placeholder="e.g., Incorrect quantity specified, missing documentation..." style="width: 100%; min-height: 110px; font-size: 0.9rem; border-radius: 14px; border: 2px solid #f1f5f9; padding: 1rem 1.25rem; font-family: inherit; resize: vertical; outline: none; transition: border-color 0.3s; box-sizing: border-box; color: #0f172a; background: #f8fafc;" onfocus="this.style.borderColor='#ef4444'; this.style.boxShadow='0 0 0 4px rgba(239,68,68,0.08)'" onblur="this.style.borderColor='#f1f5f9'; this.style.boxShadow='none'"></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '&#10005; &nbsp;Confirm Rejection',
                cancelButtonText: 'Go Back',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                focusConfirm: false,
                customClass: {
                    popup: 'swal-decline-popup',
                    confirmButton: 'swal-decline-confirm-btn',
                    cancelButton: 'swal-decline-cancel-btn',
                },
                didOpen: () => {
                    const style = document.createElement('style');
                    style.textContent = `.swal-decline-popup { border-radius: 24px !important; overflow: hidden !important; padding: 1.25em !important; } .swal-decline-confirm-btn { border-radius: 10px !important; font-weight: 800 !important; padding: 12px 24px !important; font-size: 0.9rem !important; } .swal-decline-cancel-btn { border-radius: 10px !important; font-weight: 700 !important; padding: 12px 24px !important; font-size: 0.9rem !important; }`;
                    document.head.appendChild(style);
                },
                preConfirm: () => {
                    const reason = document.getElementById('swal-reject-reason').value.trim();
                    if (!reason) {
                        Swal.showValidationMessage('<span style="font-size:0.85rem;">⚠ A justification is required to reject this entry.</span>');
                        return false;
                    }
                    return reason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitProcess(status, result.value);
                }
            });
        } else {
            submitProcess(status, '');
        }
    }

    function processEditRequestApproval(reqId) {
        if (typeof Swal === 'undefined') return;

        Swal.fire({
            title: 'Approve Changes?',
            text: 'This will replace the original inventory batch data with the proposed edits.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: '&#10003; Approve & Save Changes'
        }).then(result => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Updating Inventory...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch(`{{ url('/sra-creation') }}/${reqId}/process`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: 'approved',
                    reason: ''
                })
            })
            .then(res => {
                if (!res.ok) throw new Error('Server error');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    sessionStorage.setItem('flash_toast', JSON.stringify({
                        title: 'Edits Authorized & Merged',
                        message: 'The proposed inventory batch edits have been successfully authorized and merged into live stock.',
                        type: 'success',
                        duration: 300000
                    }));
                    let targetUrl = '{{ route("admin.messages") }}';
                    if (document.referrer && document.referrer.includes('item-entry-approval')) {
                        targetUrl = document.referrer;
                    }
                    window.location.href = targetUrl;
                } else {
                    Swal.fire('Error', data.message || 'Could not save changes.', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'A server connection issue occurred.', 'error');
            });
        });
    }

    function submitProcess(status, reason) {
        const id = '{{ $reqId }}';
        const approveBtn = document.getElementById('approveBtn');
        const rejectBtn = document.getElementById('rejectBtn');
        
        if (approveBtn) approveBtn.disabled = true;
        if (rejectBtn) rejectBtn.disabled = true;
        
        Swal.fire({
            title: 'Processing Request...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`{{ url('/sra-creation') }}/${id}/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: status,
                reason: reason
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('Server error');
            return res.json();
        })
        .then(data => {
            if (data.success) {
                const isApproved = (status === 'approved');
                sessionStorage.setItem('flash_toast', JSON.stringify({
                    title: isApproved ? 'Item Entry Authorized' : 'Item Entry Rejected',
                    message: isApproved ? 'Stock entry request successfully authorized and added to live stock.' : 'Stock entry submission has been rejected.',
                    type: isApproved ? 'success' : 'error',
                    duration: 300000
                }));
                let targetUrl = '{{ route("admin.messages") }}';
                if (document.referrer && document.referrer.includes('item-entry-approval')) {
                    targetUrl = document.referrer;
                }
                window.location.href = targetUrl;
            } else {
                Swal.fire('Action Failed', data.message || 'Error processing request', 'error');
                if (approveBtn) approveBtn.disabled = false;
                if (rejectBtn) rejectBtn.disabled = false;
            }
        })
        .catch(err => {
            Swal.fire('Action Failed', 'A connection error occurred.', 'error');
            if (approveBtn) approveBtn.disabled = false;
            if (rejectBtn) rejectBtn.disabled = false;
        });
    }
</script>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
