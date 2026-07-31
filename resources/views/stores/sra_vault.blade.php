@extends('layouts.dashboard')

@section('content')
<style>
    .sra-vault-container {
        padding: 1.5rem 2rem;
        max-width: 100%;
        width: 100%;
    }
    .sra-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: all 0.25s ease;
    }
    .sra-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
    }
    .sra-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .sra-table-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .sra-type-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 99px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        white-space: nowrap;
    }
    .pill-inventory {
        background: rgba(16, 185, 129, 0.1);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }
    .pill-service {
        background: rgba(99, 102, 241, 0.1);
        color: #4338ca;
        border: 1px solid rgba(99, 102, 241, 0.25);
    }
    .pill-donor {
        background: rgba(245, 158, 11, 0.1);
        color: #b45309;
        border: 1px solid rgba(245, 158, 11, 0.25);
    }
    .sra-tab-btn {
        padding: 0.65rem 1.25rem;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.83rem;
        border: 1.5px solid transparent;
        background: transparent;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .sra-tab-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
    }
    /* Table layout — fixed so columns stay even */
    .sra-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
    }
    /* Column widths */
    .sra-table col.col-sra      { width: 15%; }
    .sra-table col.col-supplier { width: 18%; }
    .sra-table col.col-category { width: 12%; }
    .sra-table col.col-items    { width: 20%; }
    .sra-table col.col-date     { width: 10%; }
    .sra-table col.col-sig      { width: 17%; }
    .sra-table col.col-action   { width: 8%; }

    .sra-table th {
        background: rgba(248, 250, 252, 0.9);
        padding: 0.85rem 1rem;
        font-size: 0.71rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.05em;
        border-bottom: 2px solid var(--border-color);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sra-table td {
        padding: 1rem;
        font-size: 0.86rem;
        color: var(--text-main);
        border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        vertical-align: middle;
        overflow: hidden;
    }
    .sra-table tr:last-child td { border-bottom: none; }
    .sra-table tr:hover td {
        background: rgba(241, 245, 249, 0.5);
    }
    .cell-primary {
        font-weight: 900;
        font-size: 0.92rem;
        color: var(--text-main);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cell-sub {
        font-size: 0.72rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cell-pills {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
        margin-top: 5px;
    }
    .sig-badge {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.68rem;
        font-weight: 800;
        background: rgba(16, 185, 129, 0.08);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.2);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sig-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 120px;
        display: inline-block;
    }
    .badge-partial {
        font-size: 0.67rem;
        font-weight: 800;
        color: #b45309;
        background: rgba(245, 158, 11, 0.12);
        padding: 2px 7px;
        border-radius: 99px;
        white-space: nowrap;
    }
    .badge-full {
        font-size: 0.67rem;
        font-weight: 800;
        color: #047857;
        background: rgba(16, 185, 129, 0.12);
        padding: 2px 7px;
        border-radius: 99px;
        white-space: nowrap;
    }
    .view-sra-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 0.5rem 0.85rem;
        border-radius: 10px;
        font-weight: 800;
        font-size: 0.76rem;
        text-decoration: none;
        transition: opacity 0.2s;
        white-space: nowrap;
    }
    .view-sra-btn:hover { opacity: 0.85; }
</style>

<div class="sra-vault-container">
    {{-- Hero Banner --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 8px; padding: 4px 12px; border-radius: 99px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); color: #047857; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; margin-bottom: 8px;">
                <i data-lucide="shield-check" style="width: 14px; height: 14px;"></i> STORES OFFICERS ARCHIVE
            </div>
            <h1 style="font-size: 1.85rem; font-weight: 950; color: var(--text-main); letter-spacing: -0.03em; margin: 0 0 4px 0;">Approved Stores SRA Receipts Vault</h1>
            <p style="font-size: 0.88rem; color: var(--text-muted); font-weight: 600; margin: 0;">
                Comprehensive, fully-authorized repository of all verified Inventory and Service Stores Received Advice (SRA) receipts.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button onclick="window.location.reload()" style="padding: 0.7rem 1.2rem; border-radius: 12px; font-weight: 800; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; background: var(--bg-card); color: var(--text-main); border: 1.5px solid var(--border-color); transition: all 0.2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
                <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i> Refresh Vault
            </button>
        </div>
    </div>

    {{-- Stats Cards Grid --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.25rem; margin-bottom: 1.75rem;">
        <div class="sra-stat-card">
            <div class="sra-stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                <i data-lucide="file-check-2" style="width: 24px; height: 24px;"></i>
            </div>
            <div>
                <div style="font-size: 1.75rem; font-weight: 950; color: var(--text-main); line-height: 1.1;">{{ $totalCombinedCount }}</div>
                <div style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-top: 2px;">Total Authorized SRAs</div>
            </div>
        </div>

        <div class="sra-stat-card">
            <div class="sra-stat-icon" style="background: rgba(59, 130, 246, 0.12); color: #2563eb;">
                <i data-lucide="boxes" style="width: 24px; height: 24px;"></i>
            </div>
            <div>
                <div style="font-size: 1.75rem; font-weight: 950; color: var(--text-main); line-height: 1.1;">{{ $totalInventoryCount }}</div>
                <div style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-top: 2px;">Inventory SRA Receipts</div>
            </div>
        </div>

        <div class="sra-stat-card">
            <div class="sra-stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #4f46e5;">
                <i data-lucide="receipt" style="width: 24px; height: 24px;"></i>
            </div>
            <div>
                <div style="font-size: 1.75rem; font-weight: 950; color: var(--text-main); line-height: 1.1;">{{ $totalServiceCount }}</div>
                <div style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-top: 2px;">Service SRA Receipts</div>
            </div>
        </div>
    </div>

    {{-- Filter & Search Control Panel --}}
    <div class="sra-table-card" style="margin-bottom: 1.75rem;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; background: rgba(248, 250, 252, 0.5);">
            {{-- Tabs --}}
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button onclick="filterSraType('all')" class="sra-tab-btn {{ $type === 'all' ? 'active' : '' }}" id="tab-all">
                    <i data-lucide="layers" style="width: 16px; height: 16px;"></i> All Receipts ({{ $totalCombinedCount }})
                </button>
                <button onclick="filterSraType('inventory')" class="sra-tab-btn {{ $type === 'inventory' ? 'active' : '' }}" id="tab-inventory">
                    <i data-lucide="box" style="width: 16px; height: 16px;"></i> Inventory SRAs ({{ $totalInventoryCount }})
                </button>
                <button onclick="filterSraType('service')" class="sra-tab-btn {{ $type === 'service' ? 'active' : '' }}" id="tab-service">
                    <i data-lucide="file-text" style="width: 16px; height: 16px;"></i> Service SRAs ({{ $totalServiceCount }})
                </button>
            </div>

            {{-- Live Search Input --}}
            <div style="position: relative; width: 100%; max-width: 360px;">
                <i data-lucide="search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-muted);"></i>
                <input type="text" id="sraSearchInput" value="{{ $search }}" placeholder="Search SRA #, supplier, items..." onkeyup="handleSraSearch(event)" style="width: 100%; padding: 0.65rem 0.85rem 0.65rem 2.25rem; border-radius: 12px; border: 1.5px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-size: 0.85rem; font-weight: 600; outline: none; transition: border-color 0.2s; box-sizing: border-box;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'">
            </div>
        </div>

        {{-- SRA Table --}}
        <div style="overflow-x: auto;">
            <table class="sra-table" id="sraVaultTable">
                <colgroup>
                    <col class="col-sra">
                    <col class="col-supplier">
                    <col class="col-category">
                    <col class="col-items">
                    <col class="col-date">
                    <col class="col-sig">
                    <col class="col-action">
                </colgroup>
                <thead>
                    <tr>
                        <th>SRA Number &amp; Type</th>
                        <th>Supplier / Donor</th>
                        <th>Category</th>
                        <th>Items Summary</th>
                        <th>Delivery Date</th>
                        <th>Approved Signatories</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hasResults = false; @endphp

                    {{-- 1. Render Inventory SRAs --}}
                    @if($type === 'all' || $type === 'inventory')
                        @foreach($inventorySras as $batch)
                            @php
                                $hasResults = true;
                                $isDonor = ($batch->acquisition_type === 'Donor' || str_contains(strtolower($batch->supplier_status ?? ''), 'donor') || str_contains(strtolower($batch->supplier_status ?? ''), 'donation'));
                                $isPartial = str_contains(strtolower($batch->supplier_status ?? ''), 'partial');
                                $supplierName = trim(preg_replace('/\[.*?\]/', '', ($batch->acquisition_type === 'Donor' ? ($batch->donor_name ?: $batch->supplier_name) : $batch->supplier_name) ?? 'N/A'));
                                $categoryLabel = $ledgeMap[$batch->ledge_category] ?? ('Cat. ' . $batch->ledge_category);
                                $itemsCount = $batch->items->count();
                                $firstItemDesc = $batch->items->first()?->description ?? 'No items recorded';
                                $sraNumberFormatted = 'SRA-' . str_pad($batch->id, 6, '0', STR_PAD_LEFT);
                            @endphp
                            <tr class="sra-row sra-type-inventory">
                                {{-- SRA Number & Type --}}
                                <td>
                                    <div class="cell-primary" style="font-family: monospace;">{{ $sraNumberFormatted }}</div>
                                    <div class="cell-pills">
                                        @if($isDonor)
                                            <span class="sra-type-pill pill-donor"><i data-lucide="gift" style="width: 11px; height: 11px;"></i> Donor</span>
                                        @else
                                            <span class="sra-type-pill pill-inventory"><i data-lucide="package-check" style="width: 11px; height: 11px;"></i> Inventory</span>
                                        @endif
                                        @if($isPartial)
                                            <span class="badge-partial">Partial</span>
                                        @else
                                            <span class="badge-full">Full</span>
                                        @endif
                                    </div>
                                </td>
                                {{-- Supplier / Donor --}}
                                <td>
                                    <div class="cell-primary">{{ $supplierName }}</div>
                                    <div class="cell-sub">{{ $batch->acquisition_type ?: 'Supplier' }} Delivery</div>
                                </td>
                                {{-- Category --}}
                                <td>
                                    <div class="cell-primary" style="color: var(--primary); font-size: 0.83rem;">{{ $categoryLabel }}</div>
                                    <div class="cell-sub">Ledge {{ $batch->ledge_category }}</div>
                                </td>
                                {{-- Items Summary --}}
                                <td>
                                    <div class="cell-primary" style="font-size: 0.84rem; font-weight: 750;">{{ \Illuminate\Support\Str::limit($firstItemDesc, 40) }}</div>
                                    <div class="cell-sub">
                                        @if($itemsCount > 1)
                                            + {{ $itemsCount - 1 }} more item(s)
                                        @else
                                            1 item line
                                        @endif
                                    </div>
                                </td>
                                {{-- Delivery Date --}}
                                <td>
                                    <div class="cell-primary" style="font-size: 0.84rem;">{{ \Carbon\Carbon::parse($batch->arrival_date ?: $batch->entry_date)->format('d M Y') }}</div>
                                    <div class="cell-sub">{{ \Carbon\Carbon::parse($batch->updated_at)->format('h:i A') }}</div>
                                </td>
                                {{-- Approved Signatories --}}
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <span class="sig-badge"><i data-lucide="check" style="width: 11px; height: 11px; flex-shrink: 0;"></i> <span>Stores:</span> <span class="sig-name">{{ $batch->storesApprover->name ?? 'Verified' }}</span></span>
                                        <span class="sig-badge"><i data-lucide="check" style="width: 11px; height: 11px; flex-shrink: 0;"></i> <span>Audit:</span> <span class="sig-name">{{ $batch->auditorApprover->name ?? 'Verified' }}</span></span>
                                        <span class="sig-badge"><i data-lucide="check" style="width: 11px; height: 11px; flex-shrink: 0;"></i> <span>Admin:</span> <span class="sig-name">{{ $batch->adminApprover->name ?? 'Authorized' }}</span></span>
                                    </div>
                                </td>
                                {{-- Action --}}
                                <td style="text-align: center;">
                                    <a href="{{ route('receiveditems.sra', ['id' => $batch->id]) }}" target="_blank" class="view-sra-btn" style="background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                                        <i data-lucide="printer" style="width: 14px; height: 14px;"></i> View SRA
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- 2. Render Service SRAs --}}
                    @if($type === 'all' || $type === 'service')
                        @foreach($serviceSras as $serviceSra)
                            @php $hasResults = true; @endphp
                            <tr class="sra-row sra-type-service">
                                {{-- SRA Number & Type --}}
                                <td>
                                    <div class="cell-primary" style="font-family: monospace;">{{ $serviceSra->sra_number }}</div>
                                    <div class="cell-pills">
                                        <span class="sra-type-pill pill-service"><i data-lucide="wrench" style="width: 11px; height: 11px;"></i> Service</span>
                                    </div>
                                </td>
                                {{-- Supplier / Donor --}}
                                <td>
                                    <div class="cell-primary">{{ $serviceSra->supplier_name }}</div>
                                    <div class="cell-sub">{{ $serviceSra->supplier_address ?: 'Accra' }}</div>
                                </td>
                                {{-- Category --}}
                                <td>
                                    <div class="cell-primary" style="color: #4338ca; font-size: 0.83rem;">{{ $serviceSra->category ?: 'Service / Repairs' }}</div>
                                    <div class="cell-sub">{{ $serviceSra->dept ?: 'NACOC' }}</div>
                                </td>
                                {{-- Items Summary --}}
                                <td>
                                    <div class="cell-primary" style="font-size: 0.84rem; font-weight: 750;">{{ \Illuminate\Support\Str::limit($serviceSra->details, 45) }}</div>
                                    <div class="cell-sub">Service Order</div>
                                </td>
                                {{-- Delivery Date --}}
                                <td>
                                    <div class="cell-primary" style="font-size: 0.84rem;">{{ \Carbon\Carbon::parse($serviceSra->date_of_delivery)->format('d M Y') }}</div>
                                    <div class="cell-sub">Completed</div>
                                </td>
                                {{-- Approved Signatories --}}
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <span class="sig-badge"><i data-lucide="check" style="width: 11px; height: 11px; flex-shrink: 0;"></i> <span>Stores:</span> <span class="sig-name">{{ $serviceSra->stores_approved_by ?: 'Verified' }}</span></span>
                                        <span class="sig-badge"><i data-lucide="check" style="width: 11px; height: 11px; flex-shrink: 0;"></i> <span>Audit:</span> <span class="sig-name">{{ $serviceSra->auditor_approved_by ?: 'Verified' }}</span></span>
                                        <span class="sig-badge"><i data-lucide="check" style="width: 11px; height: 11px; flex-shrink: 0;"></i> <span>Admin:</span> <span class="sig-name">{{ $serviceSra->admin_approved_by ?: 'Authorized' }}</span></span>
                                    </div>
                                </td>
                                {{-- Action --}}
                                <td style="text-align: center;">
                                    <a href="{{ route('service-sra.receipt', ['id' => $serviceSra->id]) }}" target="_blank" class="view-sra-btn" style="background: #4f46e5; color: white; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);">
                                        <i data-lucide="printer" style="width: 14px; height: 14px;"></i> View SRA
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    @if(!$hasResults)
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                                <i data-lucide="archive-x" style="width: 42px; height: 42px; stroke-width: 1.5; color: var(--text-muted); margin-bottom: 8px;"></i>
                                <div style="font-weight: 800; font-size: 1rem; color: var(--text-main);">No Approved SRA Receipts Found</div>
                                <div style="font-size: 0.82rem; margin-top: 4px;">No fully-authorized SRA receipts matched your current search filters.</div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function filterSraType(type) {
        document.querySelectorAll('.sra-tab-btn').forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.getElementById('tab-' + type);
        if (activeBtn) activeBtn.classList.add('active');

        const rows = document.querySelectorAll('.sra-row');
        rows.forEach(row => {
            if (type === 'all') {
                row.style.display = '';
            } else if (type === 'inventory') {
                row.style.display = row.classList.contains('sra-type-inventory') ? '' : 'none';
            } else if (type === 'service') {
                row.style.display = row.classList.contains('sra-type-service') ? '' : 'none';
            }
        });
    }

    function handleSraSearch(e) {
        const val = e.target.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.sra-row');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(val) ? '' : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection
