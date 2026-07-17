@extends('layouts.dashboard')

@section('content')
<div class="animate-slide-up">
    <!-- Back Header -->
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('receiveditems') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; color: var(--text-muted); font-weight: 700; transition: color 0.3s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
            <i data-lucide="arrow-left" style="width: 18px;"></i>
            Back to Inventory Log
        </a>
    </div>

    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                @if(in_array(auth()->user()->role, ['Main Admin', 'Department Head', 'Auditor']))
                    <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">{{ strtoupper(auth()->user()->department) }} · {{ auth()->user()->role === 'Auditor' ? 'Department Head' : 'Department Head Hub' }}</span>
                @else
                    <span style="background: rgba(22, 163, 74, 0.1); color: var(--primary); font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">Batch Details</span>
                @endif
                <span style="color: var(--text-muted); font-size: 0.85rem;">#BATCH-{{ $batch->id }}</span>
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main);">Entry <span style="color: var(--primary);">Specifications</span></h2>
            <p style="color: var(--text-muted);">Comprehensive breakdown of items received in this specific transaction.</p>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button onclick="window.print()" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--text-main);">
                <i data-lucide="printer" style="width: 18px;"></i>
                Print Voucher
            </button>
        </div>
    </div>

    <!-- Batch Summary Info -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="glass-card" style="padding: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="width: 44px; height: 44px; background: rgba(22, 163, 74, 0.1); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="info" style="width: 20px;"></i>
                </div>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0;">General Information</h3>
            </div>
            <div style="display: grid; gap: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">Received Date</span>
                    <span style="color: var(--text-main); font-weight: 700;">{{ $batch->arrival_date ? \Carbon\Carbon::parse($batch->arrival_date)->format('d/m/y') : 'N/A' }}</span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">Entry Date</span>
                    <span style="color: var(--text-main); font-weight: 700;">{{ $batch->entry_date ? \Carbon\Carbon::parse($batch->entry_date)->format('d/m/y H:i') : 'N/A' }}</span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">Transaction ID</span>
                    <span style="color: var(--text-main); font-weight: 700; font-family: monospace;">#NB-{{ date('Y') }}-{{ str_pad($batch->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">Category</span>
                    <span style="color: var(--text-main); font-weight: 700;">{{ $ledgeMap[$batch->ledge_category] ?? $batch->ledge_category }}</span>
                </div>
            </div>
        </div>

        <div class="glass-card" style="padding: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="truck" style="width: 20px;"></i>
                </div>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0;">Source Details</h3>
            </div>
            @php
                $acqType = $batch->acquisition_type ?? 'Supplier';
                $supplierNameStr = $batch->supplier_name ?? '';
                $isDonor = ($acqType === 'Donor' || str_contains($supplierNameStr, '[Donor Action]') || str_contains($supplierNameStr, '[Donation]'));
                $provider = $isDonor ? ($batch->donor_name ?: trim(preg_replace('/\[.*?\]/', '', $supplierNameStr))) : trim(preg_replace('/\[.*?\]/', '', $supplierNameStr));

                $deliveryPerson = $batch->delivery_person ?? '';
                $deliveryPhone = $batch->delivery_phone ?? '';
                if (empty($deliveryPerson)) {
                    $suppliersRegistry = \App\Models\Setting::get('suppliers_registry', []);
                    foreach ($suppliersRegistry as $k => $v) {
                        if (strcasecmp(trim($k), trim($provider)) === 0 || ($batch->supplier_name && strcasecmp(trim($k), trim($batch->supplier_name)) === 0)) {
                            $provider = $k;
                            $deliveryPerson = $v['contact_person'] ?? $v['delivery_person'] ?? '';
                            $deliveryPhone = $v['contact_phone'] ?? $v['delivery_phone'] ?? '';
                            break;
                        }
                    }
                }

                $isBatchIssuedOut = false;
                foreach ($batch->items as $it) {
                    if ($it->hasActiveTemporaryLoan()) {
                        $isBatchIssuedOut = true;
                        break;
                    }
                }
            @endphp
            <div style="display: grid; gap: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">Sourcing Method</span>
                    <span style="color: var(--text-main); font-weight: 700;">{{ $acqType }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">{{ $isDonor ? 'Donor Name' : 'Supplier Name' }}</span>
                    <span style="color: var(--text-main); font-weight: 700;">{{ $provider }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">Contact Person</span>
                    <span style="color: var(--text-main); font-weight: 700;">{{ $deliveryPerson ?: 'N/A' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">Contact Person Number</span>
                    <span style="color: var(--text-main); font-weight: 700;">{{ $deliveryPhone ?: 'N/A' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">Supply Status</span>
                    @if($isBatchIssuedOut)
                        <span style="color: #10b981; font-weight: 800; font-size: 0.9rem; text-transform: uppercase;">Issued Out</span>
                    @else
                        <span style="color: var(--primary); font-weight: 800; font-size: 0.9rem;">{{ $batch->supplier_status ?: 'Full Delivery' }}</span>
                    @endif
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">Total Line Items</span>
                    <span style="color: var(--text-main); font-weight: 700;">{{ $batch->items->count() }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">Status</span>
                    <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 0.25rem 0.75rem; border-radius: 6px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase;">Verified</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="glass-card" style="overflow: hidden;">
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); background: rgba(0,0,0,0.01);">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0;">Batch Items</h3>
        </div>
        <div class="table-scroll-wrapper">
            <table class="activity-table" style="width: 100%; min-width: 800px; border-collapse: collapse;">
                <thead>
                    <tr style="background: rgba(0,0,0,0.02); text-align: left;">
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Description</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Package Type</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; text-align: right;">Received Qty</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; text-align: right;">Stock Bal.</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; text-align: right;">Variance</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Remarks</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; text-align: right;">Record Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batch->items as $item)
                    <tr style="border-top: 1px solid var(--border-color);">
                        <td style="padding: 1.25rem 1.5rem;">
                            <div style="font-weight: 700; color: var(--text-main);">{{ $item->description }}</div>
                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">Registry ID: {{ $item->ledger_id ?? '-' }}</div>
                            @if(!empty($item->serial_number))
                                @php
                                    $snList = array_filter(array_map('trim', explode(',', $item->serial_number)));
                                    $count = count($snList);
                                @endphp
                                @if($count > 0)
                                    <div class="serial-numbers-wrapper" style="margin-top: 4px; display: inline-flex; flex-wrap: wrap; align-items: center; gap: 4px;">
                                        <div style="display: inline-flex; align-items: center; flex-wrap: wrap; gap: 4px; background: rgba(22, 163, 74, 0.08); color: #16a34a; font-size: 0.72rem; padding: 2px 8px; border-radius: 6px; font-weight: 800; word-break: break-word; white-space: normal; max-width: 250px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2z"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg>
                                            S/N: {{ implode(', ', array_slice($snList, 0, 3)) }}@if($count > 3)<span class="dots">...</span><span class="more-sns" style="display: none;">, {{ implode(', ', array_slice($snList, 3)) }}</span>@endif
                                        </div>
                                        @if($count > 3)
                                            <button type="button" class="toggle-sns-btn" onclick="let container = this.previousElementSibling; let more = container.querySelector('.more-sns'); let dots = container.querySelector('.dots'); let isHidden = more.style.display === 'none'; more.style.display = isHidden ? 'inline' : 'none'; dots.style.display = isHidden ? 'none' : 'inline'; this.querySelector('.chevron-icon').style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';" style="background: transparent; border: none; padding: 2px; cursor: pointer; display: inline-flex; align-items: center; color: #16a34a; outline: none; transition: all 0.2s; border-radius: 4px;" onmouseover="this.style.background='rgba(22, 163, 74, 0.15)';" onmouseout="this.style.background='transparent';" title="Show more serial numbers">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="chevron-icon" style="transition: transform 0.2s;"><polyline points="6 9 12 15 18 9"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </td>
                        <td style="padding: 1.25rem 1.5rem;">
                            <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-main);">{{ $item->unit ?? 'Package Types' }}</span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; text-align: right; font-weight: 700; color: var(--text-main);">{{ number_format($item->qty) }}</td>
                        <td style="padding: 1.25rem 1.5rem; text-align: right; color: var(--text-main); font-weight: 700;">{{ number_format(!is_null($item->book_qty) ? $item->book_qty : $item->stock_balance) }}</td>
                        <td style="padding: 1.25rem 1.5rem; text-align: right;">
                            <span style="font-weight: 800; color: {{ (float)$item->variance > 0 ? '#10b981' : ((float)$item->variance < 0 ? '#ef4444' : '#94a3b8') }};">
                                {{ (float)$item->variance > 0 ? '+' : '' }}{{ number_format($item->variance) }}
                            </span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem;">
                            <div style="font-size: 0.8rem; color: var(--text-muted); font-style: italic; max-width: 250px; line-height: 1.4; word-break: break-word;">
                                {{ $item->remarks ?: '-- No specific notes --' }}
                            </div>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 0.5rem; color: #10b981; font-weight: 600; font-size: 0.85rem;">
                                <i data-lucide="check-circle-2" style="width: 14px;"></i>
                                Recorded
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .table-scroll-wrapper {
        width: 100%;
        overflow-x: auto;
        -ms-overflow-style: none; /* IE and Edge */
        scrollbar-width: none;    /* Firefox */
    }

    .table-scroll-wrapper::-webkit-scrollbar {
        display: none; /* Chrome, Safari and Opera */
    }

    @media (max-width: 768px) {
        .page-header { flex-direction: column; align-items: flex-start !important; gap: 1.5rem; }
        .page-header button { width: 100%; }

        .activity-table thead { display: none; }
        .activity-table tr { display: block; margin: 1rem; border: 1px solid var(--border-color); border-radius: 12px; }
        .activity-table td { display: flex; justify-content: space-between; padding: 0.75rem 1rem !important; text-align: right; }
        .activity-table td:not(:last-child) { border-bottom: 1px solid rgba(0,0,0,0.03); }
        .activity-table td::before { content: attr(style); /* This is a hack, better to use data-label if possible */ }
    }

    @media print {
        .sidebar, .top-nav, .glass-card button, .top-nav-actions, a[href*="receiveditems"] { display: none !important; }
        .main-wrapper { margin-left: 0 !important; }
        .glass-card { box-shadow: none !important; border: 1px solid #eee !important; }
        body { background: white !important; }
    }
</style>
@endsection
