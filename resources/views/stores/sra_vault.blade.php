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
        gap: 6px;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
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
    .sra-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .sra-table th {
        background: rgba(248, 250, 252, 0.8);
        padding: 1rem 1.25rem;
        font-size: 0.73rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.04em;
        border-bottom: 1px solid var(--border-color);
    }
    .sra-table td {
        padding: 1.1rem 1.25rem;
        font-size: 0.88rem;
        color: var(--text-main);
        border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        vertical-align: middle;
    }
    .sra-table tr:hover td {
        background: rgba(241, 245, 249, 0.5);
    }
    .sig-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.68rem;
        font-weight: 800;
        background: rgba(16, 185, 129, 0.1);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
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
            <div style="display: flex; gap: 10px; align-items: center; width: 100%; max-width: 380px;">
                <div style="position: relative; width: 100%;">
                    <i data-lucide="search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-muted);"></i>
                    <input type="text" id="sraSearchInput" value="{{ $search }}" placeholder="Search SRA #, supplier, items..." onkeyup="handleSraSearch(event)" style="width: 100%; padding: 0.65rem 0.85rem 0.65rem 2.25rem; border-radius: 12px; border: 1.5px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-size: 0.85rem; font-weight: 600; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'">
                </div>
            </div>
        </div>

        {{-- SRA Table --}}
        <div style="overflow-x: auto;">
            <table class="sra-table" id="sraVaultTable">
                <thead>
                    <tr>
                        <th>SRA Number &amp; Type</th>
                        <th>Supplier / Donor</th>
                        <th>Category / Details</th>
                        <th>Items Summary</th>
                        <th>Delivery Date</th>
                        <th>Approved Signatories</th>
                        <th style="text-align: right;">Action</th>
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
                                $categoryLabel = $ledgeMap[$batch->ledge_category] ?? ('Category ' . $batch->ledge_category);
                                $itemsCount = $batch->items->count();
                                $firstItemDesc = $batch->items->first()?->description ?? 'No items recorded';
                                $sraNumberFormatted = 'SRA-' . str_pad($batch->id, 6, '0', STR_PAD_LEFT);
                            @endphp
                            <tr class="sra-row sra-type-inventory">
                                <td>
                                    <div style="font-weight: 900; font-size: 0.95rem; color: var(--text-main); font-family: monospace;">{{ $sraNumberFormatted }}</div>
                                    <div style="margin-top: 4px; display: flex; gap: 6px; flex-wrap: wrap;">
                                        @if($isDonor)
                                            <span class="sra-type-pill pill-donor"><i data-lucide="gift" style="width: 12px;"></i> Donor Voucher</span>
                                        @else
                                            <span class="sra-type-pill pill-inventory"><i data-lucide="package-check" style="width: 12px;"></i> Inventory SRA</span>
                                        @endif

                                        @if($isPartial)
                                            <span style="font-size: 0.68rem; font-weight: 800; color: #b45309; background: rgba(245, 158, 11, 0.12); padding: 2px 8px; border-radius: 99px;">Partial</span>
                                        @else
                                            <span style="font-size: 0.68rem; font-weight: 800; color: #047857; background: rgba(16, 185, 129, 0.12); padding: 2px 8px; border-radius: 99px;">Full</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; color: var(--text-main);">{{ $supplierName }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; margin-top: 2px;">{{ $batch->acquisition_type ?: 'Supplier' }} Delivery</div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; font-size: 0.82rem; color: var(--primary);">{{ $categoryLabel }}</div>
                                    <div style="font-size: 0.73rem; color: var(--text-muted); margin-top: 2px;">Ledge {{ $batch->ledge_category }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 750; font-size: 0.85rem; color: var(--text-main);">{{ \Illuminate\Support\Str::limit($firstItemDesc, 35) }}</div>
                                    @if($itemsCount > 1)
                                        <div style="font-size: 0.73rem; font-weight: 800; color: var(--text-muted); margin-top: 2px;">+ {{ $itemsCount - 1 }} additional item(s)</div>
                                    @else
                                        <div style="font-size: 0.73rem; color: var(--text-muted); margin-top: 2px;">1 item line</div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 800; font-size: 0.83rem;">{{ \Carbon\Carbon::parse($batch->arrival_date ?: $batch->entry_date)->format('d M Y') }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">{{ \Carbon\Carbon::parse($batch->updated_at)->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <span class="sig-badge"><i data-lucide="check" style="width: 12px;"></i> Stores: {{ $batch->storesApprover->name ?? 'Verified' }}</span>
                                        <span class="sig-badge"><i data-lucide="check" style="width: 12px;"></i> Audit: {{ $batch->auditorApprover->name ?? 'Verified' }}</span>
                                        <span class="sig-badge"><i data-lucide="check" style="width: 12px;"></i> Admin: {{ $batch->adminApprover->name ?? 'Authorized' }}</span>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('receiveditems.sra', ['id' => $batch->id]) }}" target="_blank" style="padding: 0.55rem 1rem; border-radius: 10px; font-weight: 800; font-size: 0.78rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); transition: all 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                        <i data-lucide="printer" style="width: 14px; height: 14px;"></i> View SRA
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- 2. Render Service SRAs --}}
                    @if($type === 'all' || $type === 'service')
                        @foreach($serviceSras as $serviceSra)
                            @php
                                $hasResults = true;
                            @endphp
                            <tr class="sra-row sra-type-service">
                                <td>
                                    <div style="font-weight: 900; font-size: 0.95rem; color: var(--text-main); font-family: monospace;">{{ $serviceSra->sra_number }}</div>
                                    <div style="margin-top: 4px;">
                                        <span class="sra-type-pill pill-service"><i data-lucide="wrench" style="width: 12px;"></i> Service SRA</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; color: var(--text-main);">{{ $serviceSra->supplier_name }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; margin-top: 2px;">{{ $serviceSra->supplier_address ?: 'Accra' }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; font-size: 0.82rem; color: #4338ca;">{{ $serviceSra->category ?: 'Service / Repairs' }}</div>
                                    <div style="font-size: 0.73rem; color: var(--text-muted); margin-top: 2px;">{{ $serviceSra->dept ?: 'NACOC' }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 750; font-size: 0.85rem; color: var(--text-main);">{{ \Illuminate\Support\Str::limit($serviceSra->details, 40) }}</div>
                                    <div style="font-size: 0.73rem; color: var(--text-muted); margin-top: 2px;">Service Order</div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; font-size: 0.83rem;">{{ \Carbon\Carbon::parse($serviceSra->date_of_delivery)->format('d M Y') }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">Completed</div>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <span class="sig-badge"><i data-lucide="check" style="width: 12px;"></i> Stores: {{ $serviceSra->stores_approved_by ?: 'Verified' }}</span>
                                        <span class="sig-badge"><i data-lucide="check" style="width: 12px;"></i> Audit: {{ $serviceSra->auditor_approved_by ?: 'Verified' }}</span>
                                        <span class="sig-badge"><i data-lucide="check" style="width: 12px;"></i> Admin: {{ $serviceSra->admin_approved_by ?: 'Authorized' }}</span>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('service-sra.receipt', ['id' => $serviceSra->id]) }}" target="_blank" style="padding: 0.55rem 1rem; border-radius: 10px; font-weight: 800; font-size: 0.78rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; background: #4f46e5; color: white; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2); transition: all 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
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
