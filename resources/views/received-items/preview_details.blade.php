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
        <a href="{{ str_contains(url()->previous(), 'item-entry-approval') ? route('stores.item-entry-approval') : route('admin.messages') }}" style="display: inline-flex; align-items: center; gap: 8px; color: #16a34a; background: #fff; border: 1.5px solid var(--border-color); padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 0.85rem; font-weight: 800; transition: all 0.2s;" onmouseover="this.style.background='#f8fafc'; this.style.borderColor='var(--primary)';" onmouseout="this.style.background='#fff'; this.style.borderColor='var(--border-color)';">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Back to {{ str_contains(url()->previous(), 'item-entry-approval') ? 'Item Entry Approval' : 'Oversight Board' }}
        </a>
    </div>

    @if($requestType === 'issue_submission')
        <!-- ==================== ISSUE ENTRY DETAILS ==================== -->
        <div style="background: white; padding: 3.5rem 3rem 2.5rem 3rem; border-radius: 24px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; position: relative; box-shadow: var(--shadow-luxe); margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #10b981 0%, #047857 100%); color: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);">
                    <i data-lucide="package-minus" style="width: 32px; height: 32px;"></i>
                </div>
                <div>
                    <div style="font-size: 0.75rem; font-weight: 800; color: #10b981; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 4px;">Disbursement Authorization</div>
                    <h2 style="margin: 0; font-size: 2rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em;">Issuance Details</h2>
                    <p style="margin: 6px 0 0; font-size: 0.95rem; color: #64748b; font-weight: 500;">Initiated by <b>{{ $recordedByName }}</b> on {{ $createdAt }}</p>
                </div>
            </div>

            <div style="display: flex; gap: 2rem; background: white; padding: 1.25rem 2rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f1f5f9;">
                <div>
                    <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">User Department</label>
                    <div style="font-size: 1.1rem; font-weight: 900; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></div>
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
                    <span style="font-size: 0.85rem; font-weight: 900; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 4px 12px; border-radius: 8px; border: 1px dashed rgba(16, 185, 129, 0.3);">{{ $batch['issuance_type'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div style="background: white; padding: 2.5rem 0; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: var(--shadow-luxe); overflow: hidden; margin-bottom: 2rem;">
            <h3 style="font-size: 1rem; font-weight: 900; color: #334155; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 3rem 1.5rem; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="list-checks" style="width: 20px; color: #16a34a;"></i> Items to Disburse ({{ count($items) }})
            </h3>
            <div style="background: white; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.03); margin: 0 3rem;">
                @foreach($items as $idx => $item)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 1.5rem; border-bottom: 1px dashed #e2e8f0; background: {{ $idx % 2 === 0 ? '#ffffff' : '#f8fafc' }}; transition: all 0.3s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='{{ $idx % 2 === 0 ? '#ffffff' : '#f8fafc' }}'">
                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div style="width: 48px; height: 48px; background: rgba(22, 163, 74, 0.1); color: #16a34a; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.1rem; border: 1px solid rgba(22, 163, 74, 0.2);">
                            {{ $idx + 1 }}
                        </div>
                        <div>
                            <div style="font-weight: 900; font-size: 1.1rem; color: #0f172a; margin-bottom: 4px;">{{ $item['description'] ?? '' }}</div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 0.7rem; font-weight: 800; color: #16a34a; background: rgba(22, 163, 74, 0.1); padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05em;">CATEGORY {{ $item['category'] ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: right; background: white; border: 1px solid #e2e8f0; padding: 0.75rem 1.5rem; border-radius: 14px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
                        <span style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px;">Quantity to Issue</span>
                        <div style="font-size: 1.5rem; font-weight: 900; color: #10b981; display: flex; align-items: baseline; gap: 4px; justify-content: flex-end;">
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
            <h3 style="font-size: 0.95rem; font-weight: 900; color: #10b981; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="edit-3" style="width: 18px;"></i> Proposed Changes
            </h3>
        @endif

        <!-- Proposed Items Table -->
        <div style="background: white; border-radius: 24px; border: 1px solid var(--border-color); box-shadow: var(--shadow-luxe); overflow: hidden; margin-bottom: 2rem;">
            <div style="padding: 1.5rem 2rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 900; color: #0f172a;">Items in This Entry ({{ count($items) }})</h3>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button class="sra-rollback-btn-right" onclick="window.rollbackEntry({{ $reqId }})" style="background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 10px; cursor: pointer; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; transition: all 0.2s; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);">
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
                            <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; width: 35%;">Description</th>
                            <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Package Type</th>
                            <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">{{ $isDiscrepancy ? 'Received Qty (Actual)' : 'Received Qty' }}</th>
                            @if($isDiscrepancy)
                                <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Book Qty (Ledger)</th>
                                <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: #ef4444; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Discrepancy</th>
                            @else
                                <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Stock Bal.</th>
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
                            $varianceColor = $varianceVal === 0 ? '#10b981' : ($varianceVal > 0 ? '#10b981' : '#ef4444');
                        @endphp
                        <tr style="border-bottom: 1px solid #f8fafc; transition: 0.2s;" onmouseover="this.style.background='#fcfdff'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 1rem 1.5rem; text-align: center;">
                                <input type="checkbox" class="item-rollback-checkbox" data-desc="{{ $item['description'] ?? '' }}" style="width: 16px; height: 16px; cursor: pointer; accent-color: #ef4444;" onchange="updateRollbackBtn();">
                            </td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 700; color: #0f172a; {!! $isDescChanged ? 'background: rgba(16, 185, 129, 0.1); border-left: 3px solid #10b981;' : '' !!}">
                                <div>{{ $item['description'] ?? '' }}</div>
                                <div class="item-sns-display" data-sns="{{ $item['serial_number'] ?? '' }}"></div>
                                @if($isDescChanged)
                                    <div style="font-size: 0.65rem; color: #10b981; margin-top: 4px;">Modified</div>
                                @endif
                            </td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem; color: #64748b;">
                                {{ $item['unit'] ?? 'Package Types' }}
                                @if(!empty($item['location']))
                                    <div style="font-size: 0.7rem; font-weight: 600; color: #16a34a; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                        <i data-lucide="map-pin" style="width: 10px; height: 10px; color: #16a34a;"></i>
                                        {{ $item['location'] }}
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; text-align: right; color: {{ $isQtyChanged ? '#10b981' : '#0f172a' }}; {!! $isQtyChanged ? 'background: rgba(16, 185, 129, 0.1); border-left: 2px solid #10b981;' : '' !!}">
                                {{ number_format($item['qty'] ?? 0) }}
                            </td>
                            @if($isDiscrepancy)
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #16a34a; text-align: right;">
                                    {{ number_format($item['book_qty'] ?? 0) }}
                                </td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: {{ $varianceColor }}; text-align: right;">
                                    {{ $displayVariance }}
                                </td>
                            @else
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; text-align: right; color: {{ $isStockChanged ? '#10b981' : '#16a34a' }}; {!! $isStockChanged ? 'background: rgba(16, 185, 129, 0.1); border-left: 2px solid #10b981;' : '' !!}">
                                    {{ number_format($item['stock_balance'] ?? 0) }}
                                </td>
                            @endif
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #0284c7; text-align: right;">
                                {{ number_format($item['total_in_system'] ?? 0) }}
                            </td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.8rem; color: #64748b; font-style: italic; max-width: 200px; word-break: break-word; {!! $isRemarksChanged ? 'background: rgba(16, 185, 129, 0.1); border-left: 2px solid #10b981;' : '' !!}">
                                {{ $item['remarks'] ?: '-- No specific notes --' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background: #f8fafc; border-top: 2px solid #e2e8f0;">
                        <tr>
                            <td colspan="3" style="padding: 1rem 1.5rem; font-size: 0.8rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">
                                Total Items in This Entry
                            </td>
                            <td style="padding: 1rem 1.5rem; text-align: right; font-size: 1rem; font-weight: 900; color: #16a34a;">
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
                                    {{ number_format(collect($items)->sum('stock_balance')) }} <span style="font-size: 0.7rem; font-weight: 600;">total bal.</span>
                                </td>
                            @endif
                            <td style="padding: 1rem 1.5rem;"></td>
                            <td style="padding: 1rem 1.5rem;"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Original Entry state block (Only for edit request types) -->
        @if($requestType === 'edit_submission' && isset($data['previous_batch']))
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
                                <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 850; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Received Qty</th>
                                @if($isDiscrepancy)
                                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 850; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Book Qty</th>
                                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 850; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Discrepancy</th>
                                @else
                                    <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 850; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Stock Bal.</th>
                                @endif
                                <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 850; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Total System</th>
                                <th style="padding: 1rem 1.5rem; font-size: 0.72rem; font-weight: 850; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prevItems as $prevItem)
                            @php
                                $prevVarianceVal = floatval($prevItem['qty'] ?? 0) - floatval($prevItem['book_qty'] ?? 0);
                            @endphp
                            <tr style="border-bottom: 1px solid #fee2e2;">
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 700; color: #7f1d1d;">
                                    <div>{{ $prevItem['description'] ?? '' }}</div>
                                    <div class="item-sns-display" data-sns="{{ $prevItem['serial_number'] ?? '' }}"></div>
                                </td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; color: #991b1b;">
                                    {{ $prevItem['unit'] ?? 'Package Types' }}
                                    @if(!empty($prevItem['location']))
                                        <div style="font-size: 0.7rem; font-weight: 600; color: #7f1d1d; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                            <i data-lucide="map-pin" style="width: 10px; height: 10px; color: #7f1d1d;"></i>
                                            {{ $prevItem['location'] }}
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #991b1b; text-align: right;">
                                    {{ number_format($prevItem['qty'] ?? 0) }}
                                </td>
                                @if($isDiscrepancy)
                                    <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #991b1b; text-align: right;">
                                        {{ number_format($prevItem['book_qty'] ?? 0) }}
                                    </td>
                                    <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #991b1b; text-align: right;">--</td>
                                @else
                                    <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #991b1b; text-align: right;">
                                        {{ number_format($prevItem['stock_balance'] ?? 0) }}
                                    </td>
                                @endif
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #991b1b; text-align: right;">
                                    {{ number_format($prevItem['total_in_system'] ?? 0) }}
                                </td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.8rem; color: #b91c1c; font-style: italic; max-width: 200px; word-break: break-word;">
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
            <div style="width: 32px; height: 32px; background: #10b981; color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="info" style="width: 18px;"></i>
            </div>
            <div style="flex: 1;">
                <span style="display: block; font-size: 0.85rem; font-weight: 700; color: #92400e;">Entry Review in Progress</span>
                <span style="font-size: 0.75rem; color: #b45309;">Please check the quantities carefully before you approve this entry.</span>
            </div>
        </div>

        <!-- Administrative Review Actions Panel -->
        @if($status === 'pending')
        <div class="actions-panel" style="background: white; border: 1px solid var(--border-color); padding: 1.75rem 2.5rem; display: flex; justify-content: flex-end; align-items: center; gap: 1rem; border-radius: 24px; box-shadow: var(--shadow-luxe); margin-top: 2rem;">
            <button onclick="window.rollbackEntry({{ $reqId }})" style="margin-right: auto; background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                <i data-lucide="rotate-ccw" style="width: 18px;"></i> Rollback
            </button>
            
            @php
                $approveFn = $requestType === 'edit_submission' ? "processEditRequestApproval($reqId)" : "processApproval('approved')";
            @endphp
            <button id="approveBtn" onclick="{!! $approveFn !!}" style="background: #10b981; color: white; border: none; padding: 12px 28px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                <i data-lucide="check-circle" style="width: 18px;"></i> {{ $requestType === 'edit_submission' ? 'Approve Changes' : 'Approve Entry' }}
            </button>
        </div>
        @else
        <div style="margin-top: 2rem; padding: 1.75rem; background: {{ $status === 'approved' ? 'rgba(16, 185, 129, 0.08)' : 'rgba(239, 68, 68, 0.08)' }}; border-radius: 24px; border: 1.5px solid {{ $status === 'approved' ? '#10b981' : '#ef4444' }}; display: flex; align-items: center; justify-content: center; gap: 1rem; box-shadow: var(--shadow-luxe);">
            <div style="font-weight: 950; color: {{ $status === 'approved' ? '#10b981' : '#ef4444' }}; text-transform: uppercase; font-size: 1rem; display: flex; align-items: center; gap: 8px; letter-spacing: 0.05em;">
                <i data-lucide="{{ $status === 'approved' ? 'check-circle' : 'alert-circle' }}" style="width: 22px; height: 22px;"></i>
                Oversight Decision: {{ $status === 'approved' ? 'APPROVED & SAVED' : 'REJECTED' }}
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
                btn.style.background = '#10b981';
                btn.style.boxShadow = '0 4px 12px rgba(16, 185, 129, 0.25)';
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    function formatSerialNumbersDisplay(serialStr, isProposed = true) {
        if (!serialStr) return '';
        const sns = serialStr.split(',').map(s => s.trim()).filter(Boolean);
        if (sns.length === 0) return '';

        const badgeBg = isProposed ? 'rgba(22, 163, 74, 0.05)' : 'rgba(239, 68, 68, 0.05)';
        const badgeColor = isProposed ? '#16a34a' : '#ef4444';
        const borderColor = isProposed ? 'rgba(22, 163, 74, 0.15)' : 'rgba(239, 68, 68, 0.15)';

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
                            <div style="background: linear-gradient(170deg, #f0f9ff 0%, #e0f2fe 100%); padding: 1.5rem 1.25rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; min-width: 130px; flex-shrink: 0; border-right: 1px solid #bae6fd; position: relative; overflow: hidden;">
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
                                        <div style="background: linear-gradient(170deg, #f0f9ff 0%, #e0f2fe 100%); padding: 1.5rem 1.25rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; min-width: 130px; flex-shrink: 0; border-right: 1px solid #bae6fd; position: relative; overflow: hidden;">
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
                                                <div style="font-size: 2rem; font-weight: 900; color: #16a34a; line-height: 1;">${stats.total_deliveries.toLocaleString()}</div>
                                                <div style="font-size: 0.6rem; font-weight: 800; color: #4ade80; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 4px;">Total Deliveries</div>
                                            </div>
                                            <div style="height: 1px; background: #bbf7d0;"></div>
                                            <div style="text-align: center;">
                                                <div style="font-size: 0.85rem; font-weight: 800; color: #15803d; line-height: 1.2;">${stats.last_delivery}</div>
                                                <div style="font-size: 0.6rem; font-weight: 800; color: #4ade80; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 4px;">Last Delivery</div>
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
        if (typeof Swal === 'undefined') { alert('Cannot open rollback dialog.'); return; }

        const selectedItems = Array.from(document.querySelectorAll('.item-rollback-checkbox:checked')).map(cb => cb.getAttribute('data-desc'));
        const hasSelection = selectedItems.length > 0;
        const selectedText = hasSelection ? `Correction required for: ${selectedItems.join(', ')}` : '';

        const FIELDS = [
            { key: 'supplier_status',  label: 'Delivery Status' },
            { key: 'arrival_date',     label: 'Received Date (Manual)' },
            { key: 'item_qty',         label: 'Received Qty' },
            { key: 'item_unit',        label: 'Package Type' },
            { key: 'item_description', label: 'Item Description' },
            { key: 'supplier_name',    label: 'Donor Name' },
        ];

        const standardPackages = ['PIECE(S)', 'PACK', 'BOXES', 'CARTON', 'BAG', 'ROLL', 'SET', 'REAM', 'BOTTLE'];

        const fieldsHtml = FIELDS.map(f => {
            let inputHtml = '';
            let isChecked = false;
            let placeholderVal = "Correction note for this field (e.g. 'Use XYZ Ltd instead')....";

            if (hasSelection && (f.key === 'item_description' || f.key === 'item_qty')) {
                placeholderVal = selectedText;
            }

            let displayStyle = isChecked ? 'block' : 'none';
            let rowBg = isChecked ? '#fff5f5' : '#f8fafc';
            let borderCol = isChecked ? '#e2e8f0' : '#e2e8f0';

            let onchangeJs = `this.closest('.rb-field-row').querySelector('.rb-note-wrap').style.display = this.checked ? 'block' : 'none'; this.closest('.rb-field-row').style.background = this.checked ? '#fff5f5' : '#f8fafc'; this.closest('.rb-field-row').style.borderColor = this.checked ? '#fca5a5' : '#e2e8f0';`;
            
            if (f.key === 'item_unit') {
                inputHtml = `
                    <select class="rb-field-note select2-unit-rollback" data-key="${f.key}" style="width: 100%;">
                        <option value="">Select recommended package type...</option>
                        ${standardPackages.map(pkg => `<option value="${pkg}">${pkg}</option>`).join('')}
                    </select>
                `;
                onchangeJs += ` if (this.checked) { setTimeout(() => { $(this.closest('.rb-field-row')).find('.select2-unit-rollback').select2({ placeholder: 'Select or type package type...', tags: true, width: '100%', dropdownParent: $('.swal-rollback-popup') }); }, 50); }`;
            } else {
                inputHtml = `
                    <input type="text" class="rb-field-note" data-key="${f.key}" value=""
                           placeholder="${placeholderVal}"
                           style="width: 100%; font-size: 0.82rem; border: 1.5px solid #fca5a5; border-radius: 8px; padding: 7px 10px; font-family: inherit; color: #1e293b; background: white; outline: none; box-sizing: border-box;"
                           onfocus="this.style.borderColor='#ef4444'; this.style.boxShadow='0 0 0 3px rgba(239,68,68,0.12)'"
                           onblur="this.style.borderColor='#fca5a5'; this.style.boxShadow='none'">
                `;
            }

            return `
            <div class="rb-field-row" style="display: flex; flex-direction: column; gap: 6px; padding: 10px; border-radius: 12px; border: 1px solid ${borderCol}; background: ${rowBg}; transition: background 0.2s;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none;">
                    <input type="checkbox" class="rb-field-check" data-key="${f.key}" ${isChecked ? 'checked' : ''}
                           style="width: 16px; height: 16px; accent-color: #ef4444; cursor: pointer; flex-shrink: 0;"
                           onchange="${onchangeJs}">
                    <span style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">${f.label}</span>
                </label>
                <div class="rb-note-wrap" style="display: ${displayStyle}; padding-left: 24px; margin-top: 4px;">
                    ${inputHtml}
                </div>
            </div>
            `;
        }).join('');

        Swal.fire({
            html: `
                <div style="text-align: left;">
                    <div style="background: linear-gradient(135deg, #10b981 0%, #047857 100%); margin: -1.25em -1.25em 1.5em; padding: 2rem 2rem 1.5rem; border-radius: 4px 4px 0 0; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.07); border-radius: 50%;"></div>
                        <div style="display: flex; align-items: center; gap: 14px; position: relative;">
                            <div style="width: 46px; height: 46px; background: rgba(255,255,255,0.15); border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg style="width: 24px; height: 24px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                            <div>
                                <div style="font-size: 0.68rem; font-weight: 800; color: rgba(255,255,255,0.75); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 2px;">Admin Action</div>
                                <div style="font-size: 1.25rem; font-weight: 900; color: white; letter-spacing: -0.02em;">Rollback & Request Corrections</div>
                            </div>
                        </div>
                    </div>

                    <p style="font-size: 0.88rem; color: #64748b; line-height: 1.6; margin-bottom: 1.25rem;">
                        Select the fields that need to be corrected and provide a brief note for each. The user will see these highlighted in <b style="color: #ef4444;">red</b> on their form.
                    </p>

                    ${hasSelection ? `
                    <div style="background: rgba(239, 68, 68, 0.08); border: 1.5px solid #fecaca; border-radius: 12px; padding: 12px 16px; margin-bottom: 1.25rem; display: flex; align-items: flex-start; gap: 10px;">
                        <svg style="width: 18px; height: 18px; color: #ef4444; flex-shrink: 0; margin-top: 2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div style="flex: 1;">
                            <div style="font-size: 0.72rem; font-weight: 800; color: #ef4444; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 3px;">Selected Items for Rollback</div>
                            <div style="font-size: 0.88rem; font-weight: 800; color: #7f1d1d; line-height: 1.45; word-break: break-word;">${selectedItems.map(item => `&bull; ${item}`).join('<br>')}</div>
                        </div>
                    </div>
                    ` : ''}

                    <div style="display: flex; flex-direction: column; gap: 8px; max-height: 340px; overflow-y: auto; padding-right: 4px; margin-bottom: 1.25rem;">
                        ${fieldsHtml}
                    </div>

                    <div style="margin-bottom: 0.25rem;">
                        <label style="font-size: 0.78rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; display: block; margin-bottom: 6px;">General Note (Optional)</label>
                        <textarea id="rb-general-note" placeholder="Overall feedback or instructions for the user..." rows="3"
                            style="width: 100%; font-size: 0.88rem; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 10px 14px; font-family: inherit; color: #1e293b; background: #f8fafc; outline: none; resize: vertical; box-sizing: border-box;"
                            onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 4px rgba(16,185,129,0.1)'; this.style.background='white'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'; this.style.background='#f8fafc'"></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '&#8630;&nbsp; Send Back for Correction',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#94a3b8',
            width: 600,
            customClass: {
                popup: 'swal-rollback-popup',
                confirmButton: 'swal-rollback-confirm-btn',
            },
            didOpen: () => {
                if (!document.getElementById('swal-rollback-styles')) {
                    const st = document.createElement('style');
                    st.id = 'swal-rollback-styles';
                    st.textContent = `
                        .swal2-container {
                            z-index: 100000 !important;
                            backdrop-filter: blur(8px) !important;
                            -webkit-backdrop-filter: blur(8px) !important;
                            background-color: rgba(15, 23, 42, 0.4) !important;
                        }
                        .swal-rollback-popup {
                            border-radius: 24px !important;
                            overflow: hidden !important;
                            padding: 1.25em !important;
                        }
                        .swal-rollback-confirm-btn {
                            border-radius: 10px !important;
                            font-weight: 800 !important;
                            padding: 12px 24px !important;
                            font-size: 0.9rem !important;
                        }
                        .swal-rollback-popup .select2-container--default .select2-selection--single {
                            height: 38px !important;
                            border-radius: 8px !important;
                            border: 1.5px solid #fca5a5 !important;
                            display: flex !important;
                            align-items: center !important;
                            background: white !important;
                            outline: none !important;
                            box-sizing: border-box !important;
                        }
                        .swal-rollback-popup .select2-container--default .select2-selection--single .select2-selection__rendered {
                            color: #1e293b !important;
                            font-size: 0.82rem !important;
                            font-weight: 700 !important;
                            padding-left: 10px !important;
                            line-height: 36px !important;
                        }
                        .swal-rollback-popup .select2-container--default .select2-selection--single .select2-selection__arrow {
                            height: 36px !important;
                        }
                    `;
                    document.head.appendChild(st);
                }
            },
            preConfirm: () => {
                const flaggedFields = {};
                document.querySelectorAll('.rb-field-check:checked').forEach(cb => {
                    const key  = cb.getAttribute('data-key');
                    const note = cb.closest('.rb-field-row').querySelector('.rb-field-note').value.trim();
                    flaggedFields[key] = note || 'Please review and correct this field.';
                });
                const generalNote = document.getElementById('rb-general-note').value.trim();
                if (Object.keys(flaggedFields).length === 0 && !generalNote) {
                    Swal.showValidationMessage('<span style="font-size:0.85rem;">⚠ Please select at least one field to flag or provide a general note.</span>');
                    return false;
                }
                return { flaggedFields, generalNote };
            }
        }).then(result => {
            if (!result.isConfirmed) return;

            const { flaggedFields, generalNote } = result.value;

            Swal.fire({
                title: 'Processing Rollback...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch(`{{ url('/sra-creation') }}/${reqId}/rollback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    flagged_fields: flaggedFields,
                    general_note: generalNote,
                    flagged_items: selectedItems,
                })
            })
            .then(res => {
                if (!res.ok) throw new Error('Server error');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Rolled Back!',
                        text: 'Entry sent back to user for correction.',
                        confirmButtonColor: '#16a34a'
                    }).then(() => {
                        let targetUrl = '{{ route("admin.messages") }}';
                        if (document.referrer && document.referrer.includes('item-entry-approval')) {
                            targetUrl = document.referrer;
                        }
                        window.location.href = targetUrl;
                    });
                } else {
                    Swal.fire('Rollback Failed', data.message || 'Error processing rollback.', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Could not complete rollback.', 'error');
            });
        });
    };

    function processApproval(status) {
        const id = '{{ $reqId }}';
        if (status === 'rejected') {
            Swal.fire({
                html: `
                    <div style="text-align: left;">
                        <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); margin: -1.25em -1.25em 1.5em; padding: 2rem 2rem 1.5rem; border-radius: 4px 4px 0 0; position: relative; overflow: hidden;">
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
            confirmButtonColor: '#10b981',
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Changes Saved!',
                        text: 'The inventory batch edits have been successfully merged.',
                        confirmButtonColor: '#16a34a'
                    }).then(() => {
                        let targetUrl = '{{ route("admin.messages") }}';
                        if (document.referrer && document.referrer.includes('item-entry-approval')) {
                            targetUrl = document.referrer;
                        }
                        window.location.href = targetUrl;
                    });
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
                Swal.fire({
                    icon: 'success',
                    title: 'Action Logged!',
                    text: status === 'approved' ? 'Stock entry successfully approved.' : 'Stock entry submission rejected.',
                    confirmButtonColor: '#16a34a'
                }).then(() => {
                    let targetUrl = '{{ route("admin.messages") }}';
                    if (document.referrer && document.referrer.includes('item-entry-approval')) {
                        targetUrl = document.referrer;
                    }
                    window.location.href = targetUrl;
                });
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
