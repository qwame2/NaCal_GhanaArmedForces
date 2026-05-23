@extends('layouts.admin')

@section('title', 'Entry Oversight')

@section('content')
@php
    $acqType = $batch->acquisition_type ?? 'Supplier';
    $isDonor = ($acqType === 'Donor' || str_contains($batch->supplier_name ?? '', '[Donor Action]') || str_contains($batch->supplier_name ?? '', '[Donation]'));
    $provider = $isDonor ? ($batch->donor_name ?: trim(preg_replace('/\[.*?\]/', '', $batch->supplier_name ?? ''))) : trim(preg_replace('/\[.*?\]/', '', $batch->supplier_name ?? ''));
    
    $suppliersRegistry = \App\Models\Setting::get('suppliers_registry', []);
    $deliveryPerson = '';
    foreach ($suppliersRegistry as $k => $v) {
        if (strcasecmp(trim($k), trim($provider)) === 0 || (!empty($batch->supplier_name) && strcasecmp(trim($k), trim($batch->supplier_name)) === 0)) {
            $provider = $k;
            $deliveryPerson = $v['delivery_person'] ?? '';
            break;
        }
    }
@endphp
<div class="preview-container" style="max-width: 1200px; margin: 0 auto; animation: fadeIn 0.5s ease-out;">
    <!-- Strategic Header -->
    <div class="preview-header" style="background: white; padding: 2.5rem; border-radius: 24px; border: 1px solid var(--border-color); box-shadow: var(--shadow-luxe); margin-bottom: 2rem; position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; right: 0; padding: 1.5rem;">
            <div style="background: #fef2f2; color: #ef4444; padding: 6px 16px; border-radius: 99px; font-size: 0.75rem; font-weight: 800; border: 1px solid #fee2e2;">
                DRAFT PREVIEW
            </div>
        </div>
        
        <div style="display: flex; align-items: flex-start; gap: 2rem;">
            <div style="width: 80px; height: 80px; background: var(--primary-glow); color: var(--primary); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="clipboard-check" style="width: 40px; height: 40px;"></i>
            </div>
            <div style="flex: 1;">
                <h1 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; margin: 0 0 0.5rem 0; letter-spacing: -0.02em;">New Stock Entry Verification</h1>
                <p style="color: var(--text-muted); font-size: 1rem; font-weight: 500; margin: 0;">Personnel <b>{{ $batch->recorded_by_name }}</b> is proposing a new inventory batch entry for strategic oversight.</p>
                
                <div style="display: flex; gap: 2rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; flex-wrap: wrap;">
                    <div>
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Sourcing Method</span>
                        <span style="font-size: 0.95rem; font-weight: 700; color: #0f172a;">{{ $batch->acquisition_type }}</span>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">{{ $isDonor ? 'Donor Name' : 'Supplier Name' }}</span>
                        <span style="font-size: 0.95rem; font-weight: 700; color: #0f172a;">{{ $provider ?: 'N/A' }}</span>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Delivery Person</span>
                        <span style="font-size: 0.95rem; font-weight: 700; color: #0f172a;">{{ $deliveryPerson ?: 'N/A' }}</span>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Supply Status</span>
                        <span style="font-size: 0.95rem; font-weight: 700; color: #0f172a;">{{ $batch->supplier_status ?: 'Full Delivery' }}</span>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Received Date</span>
                        <span style="font-size: 0.95rem; font-weight: 700; color: #0f172a;">{{ \Carbon\Carbon::parse($batch->arrival_date)->format('d/m/y') }}</span>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Registry Category</span>
                        <span style="font-size: 0.95rem; font-weight: 700; color: var(--primary); background: var(--primary-glow); padding: 2px 10px; border-radius: 6px;">{{ $ledgeMap[$batch->ledge_category] ?? $batch->ledge_category }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Breakdown Table -->
    <div style="background: white; border-radius: 24px; border: 1px solid var(--border-color); box-shadow: var(--shadow-luxe); overflow: hidden;">
        <div style="padding: 1.5rem 2rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 900; color: #0f172a;">Submitted Item Details</h3>
            <span style="background: #e0f2fe; color: #0369a1; font-size: 0.7rem; font-weight: 800; padding: 4px 12px; border-radius: 99px;">{{ count($batch->items) }} ITEMS DECLARED</span>
        </div>
        
        <div style="padding: 0;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <th style="padding: 1.25rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Description</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Package Type</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Received Qty</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Stock Bal.</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Total in System</th>
                        <th style="padding: 1.25rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batch->items as $item)
                    <tr style="border-bottom: 1px solid #f8fafc; transition: 0.2s;" onmouseover="this.style.background='#fcfdff'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1.25rem 2rem;">
                            <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">{{ $item->description }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 2px;">Registry ID: {{ $item->ledger_id }}</div>
                        </td>
                        <td style="padding: 1.25rem 1.5rem;">
                            <span style="font-size: 0.85rem; font-weight: 600; color: #475569;">{{ $item->unit }}</span>
                            @if($item->location)
                                <div style="font-size: 0.65rem; font-weight: 600; color: #4f46e5; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                    <i data-lucide="map-pin" style="width: 10px; height: 10px;"></i> {{ $item->location }}
                                </div>
                            @endif
                        </td>
                        <td style="padding: 1.25rem 1.5rem; text-align: right;">
                            <span style="font-size: 0.95rem; font-weight: 800; color: #0f172a;">{{ number_format($item->qty) }}</span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; text-align: right;">
                            <span style="font-size: 0.95rem; font-weight: 800; color: var(--primary);">{{ number_format($item->stock_balance) }}</span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; text-align: right;">
                            <span style="font-size: 0.95rem; font-weight: 800; color: #0284c7;">
                                {{ number_format($item->total_in_system ?? 0) }}
                            </span>
                        </td>
                        <td style="padding: 1.25rem 2rem;">
                            <div style="font-size: 0.8rem; color: #64748b; font-style: italic; max-width: 250px; line-height: 1.4;">
                                {{ $item->remarks ?: '-- No specific notes --' }}
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Administrative Actions Footnote -->
    <div style="margin-top: 2rem; padding: 1.5rem; background: #fffbeb; border-radius: 16px; border: 1px solid #fef3c7; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 32px; height: 32px; background: #f59e0b; color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="info" style="width: 18px;"></i>
        </div>
        <div style="flex: 1;">
            <span style="display: block; font-size: 0.85rem; font-weight: 700; color: #92400e;">Entry Review in Progress</span>
            <span style="font-size: 0.75rem; color: #b45309;">Please check the quantities carefully before you approve this entry.</span>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
