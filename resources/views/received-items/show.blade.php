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
                <span style="background: rgba(99, 102, 241, 0.1); color: var(--primary); font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">Batch Details</span>
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
                <div style="width: 44px; height: 44px; background: rgba(99, 102, 241, 0.1); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="info" style="width: 20px;"></i>
                </div>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0;">General Information</h3>
            </div>
            <div style="display: grid; gap: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">Receiving Date</span>
                    <span style="color: var(--text-main); font-weight: 700;">{{ \Carbon\Carbon::parse($batch->entry_date)->format('F d, Y') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">Ledge Account</span>
                    <span style="background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 6px; font-weight: 800; font-size: 0.8rem;">
                        {{ $ledgeMap[$batch->ledge_category] ?? "Ledge " . $batch->ledge_category }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">Transaction ID</span>
                    <span style="color: var(--text-main); font-weight: 700; font-family: monospace;">#NB-{{ date('Y') }}-{{ str_pad($batch->id, 4, '0', STR_PAD_LEFT) }}</span>
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
            <div style="display: grid; gap: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">Supplier Name</span>
                    <span style="color: var(--text-main); font-weight: 700;">{{ $batch->supplier_name }}</span>
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
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Folio</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Category</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Qty Received</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Record Status</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; text-align: right;">Unit Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batch->items as $item)
                    <tr style="border-top: 1px solid var(--border-color);">
                        <td style="padding: 1.25rem 1.5rem;">
                            <div style="font-weight: 700; color: var(--text-main);">{{ $item->description }}</div>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-family: monospace; color: var(--text-muted);">{{ $item->folio }}</td>
                        <td style="padding: 1.25rem 1.5rem;">
                            <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $ledgeMap[$batch->ledge_category] ?? $batch->ledge_category }}</span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem;">
                            <span style="font-weight: 800; color: #10b981;">+{{ $item->variance }}</span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; color: #10b981; font-weight: 600; font-size: 0.85rem;">
                                <i data-lucide="check-circle-2" style="width: 14px;"></i>
                                Recorded
                            </div>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; text-align: right; color: var(--text-main); font-weight: 700;">{{ $item->stock_balance }}</td>
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
