@extends('layouts.dashboard')

@section('content')
<style>
    /* ─── Page Container ─────────────────────────────── */
    .vault-page { padding: 1.75rem 2rem; }

    /* ─── Stat Cards ─────────────────────────────────── */
    .vault-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.1rem;
        margin-bottom: 1.75rem;
    }
    .vault-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.1rem 1.4rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
        transition: transform .22s, box-shadow .22s;
    }
    .vault-stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.07); }
    .vault-stat-icon {
        width: 46px; height: 46px;
        border-radius: 13px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .vault-stat-num { font-size: 1.65rem; font-weight: 950; color: var(--text-main); line-height: 1.1; }
    .vault-stat-lbl { font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-top: 2px; letter-spacing: .04em; }

    /* ─── Card Wrapper ───────────────────────────────── */
    .vault-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        overflow: hidden;
        margin-bottom: 1.75rem;
    }

    /* ─── Toolbar (tabs + search) ────────────────────── */
    .vault-toolbar {
        padding: 1rem 1.4rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: .85rem;
        background: rgba(248,250,252,.55);
    }
    .vault-tab-group { display: flex; gap: 6px; flex-wrap: wrap; }
    .vault-tab {
        padding: .55rem 1.1rem;
        border-radius: 10px;
        font-weight: 800;
        font-size: .8rem;
        border: 1.5px solid transparent;
        background: transparent;
        color: var(--text-muted);
        cursor: pointer;
        transition: all .2s;
        display: inline-flex; align-items: center; gap: 7px;
    }
    .vault-tab.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(16,185,129,.22);
    }
    .vault-search-wrap { position: relative; width: 100%; max-width: 320px; }
    .vault-search-wrap i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 15px; height: 15px; }
    .vault-search-input {
        width: 100%;
        padding: .6rem .85rem .6rem 2.1rem;
        border-radius: 10px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-main);
        font-size: .83rem;
        font-weight: 600;
        outline: none;
        transition: border-color .2s;
        box-sizing: border-box;
    }
    .vault-search-input:focus { border-color: var(--primary); }

    /* ─── Table ──────────────────────────────────────── */
    .vault-table-wrap { overflow-x: auto; }
    .vault-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
    }
    /* Header */
    .vault-table thead tr {
        background: rgba(248,250,252,.9);
        border-bottom: 2px solid var(--border-color);
    }
    .vault-table th {
        padding: .8rem 1.1rem;
        font-size: .69rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--text-muted);
        white-space: nowrap;
        text-align: left;
    }
    .vault-table th.th-center { text-align: center; }

    /* Body rows */
    .vault-table tbody tr {
        border-bottom: 1px solid rgba(226,232,240,.65);
        transition: background .15s;
    }
    .vault-table tbody tr:last-child { border-bottom: none; }
    .vault-table tbody tr:hover td { background: rgba(241,245,249,.55); }

    /* Zebra */
    .vault-table tbody tr:nth-child(even) td { background: rgba(248,250,252,.35); }

    /* Cells */
    .vault-table td {
        padding: .85rem 1.1rem;
        font-size: .85rem;
        color: var(--text-main);
        vertical-align: middle;
    }
    .vault-table td.td-center { text-align: center; }

    /* Row type accent */
    .vault-table tr.sra-type-inventory td:first-child {
        border-left: 3px solid #10b981;
    }
    .vault-table tr.sra-type-service td:first-child {
        border-left: 3px solid #6366f1;
    }

    /* ─── Cell helpers ───────────────────────────────── */
    .v-main {
        font-weight: 800;
        font-size: .87rem;
        color: var(--text-main);
        line-height: 1.3;
    }
    .v-sub {
        font-size: .71rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 2px;
        line-height: 1.3;
    }

    /* ─── Type pills ─────────────────────────────────── */
    .v-pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px;
        border-radius: 99px;
        font-size: .67rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .03em;
        white-space: nowrap;
        margin-top: 4px;
    }
    .v-pill-inv  { background: rgba(16,185,129,.1);  color: #047857; border: 1px solid rgba(16,185,129,.25); }
    .v-pill-svc  { background: rgba(99,102,241,.1);  color: #4338ca; border: 1px solid rgba(99,102,241,.25); }
    .v-pill-don  { background: rgba(245,158,11,.1);  color: #b45309; border: 1px solid rgba(245,158,11,.25); }
    .v-badge-full    { display: inline-block; padding: 1px 7px; border-radius: 99px; font-size: .65rem; font-weight: 900; color: #047857; background: rgba(16,185,129,.1); margin-top: 4px; margin-left: 4px; }
    .v-badge-partial { display: inline-block; padding: 1px 7px; border-radius: 99px; font-size: .65rem; font-weight: 900; color: #b45309; background: rgba(245,158,11,.1); margin-top: 4px; margin-left: 4px; }

    /* ─── Signatories — three stacked micro-lines ─────── */
    .sig-stack { display: flex; flex-direction: column; gap: 3px; }
    .sig-line {
        display: flex; align-items: center; gap: 5px;
        font-size: .72rem; font-weight: 700; color: var(--text-main);
    }
    .sig-check {
        width: 14px; height: 14px; flex-shrink: 0;
        background: rgba(16,185,129,.15);
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        color: #047857;
        font-size: .62rem;
        font-weight: 900;
    }
    .sig-role {
        font-size: .67rem; font-weight: 900; text-transform: uppercase;
        color: var(--text-muted); letter-spacing: .03em; min-width: 38px;
    }
    .sig-name {
        font-size: .75rem; font-weight: 700; color: var(--text-main);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        max-width: 130px;
    }

    /* ─── Action button ──────────────────────────────── */
    .v-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: .45rem .9rem;
        border-radius: 9px;
        font-weight: 800; font-size: .75rem;
        text-decoration: none;
        transition: opacity .2s, transform .15s;
        white-space: nowrap;
    }
    .v-btn:hover { opacity: .88; transform: translateY(-1px); }

    /* ─── Empty state ────────────────────────────────── */
    .vault-empty {
        text-align: center;
        padding: 3.5rem 1.5rem;
    }
</style>

<div class="vault-page">

    {{-- ── Page Header ── --}}
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
        <div>
            <div style="display:inline-flex; align-items:center; gap:7px; padding:3px 11px; border-radius:99px; background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.25); color:#047857; font-size:.72rem; font-weight:900; text-transform:uppercase; letter-spacing:.04em; margin-bottom:7px;">
                <i data-lucide="shield-check" style="width:13px; height:13px;"></i> Stores Officers Archive
            </div>
            <h1 style="font-size:1.75rem; font-weight:950; color:var(--text-main); letter-spacing:-.03em; margin:0 0 4px;">Approved Stores SRA Receipts Vault</h1>
            <p style="font-size:.85rem; color:var(--text-muted); font-weight:600; margin:0;">Fully-authorized repository of all verified Inventory and Service SRA receipts.</p>
        </div>
        <button onclick="window.location.reload()" style="padding:.65rem 1.1rem; border-radius:11px; font-weight:800; font-size:.8rem; display:inline-flex; align-items:center; gap:7px; cursor:pointer; background:var(--bg-card); color:var(--text-main); border:1.5px solid var(--border-color); transition:border-color .2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
            <i data-lucide="refresh-cw" style="width:15px; height:15px;"></i> Refresh
        </button>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="vault-stat-grid">
        <div class="vault-stat-card">
            <div class="vault-stat-icon" style="background:rgba(16,185,129,.12); color:#059669;">
                <i data-lucide="file-check-2" style="width:22px;height:22px;"></i>
            </div>
            <div>
                <div class="vault-stat-num">{{ $totalCombinedCount }}</div>
                <div class="vault-stat-lbl">Total Authorized SRAs</div>
            </div>
        </div>
        <div class="vault-stat-card">
            <div class="vault-stat-icon" style="background:rgba(59,130,246,.12); color:#2563eb;">
                <i data-lucide="boxes" style="width:22px;height:22px;"></i>
            </div>
            <div>
                <div class="vault-stat-num">{{ $totalInventoryCount }}</div>
                <div class="vault-stat-lbl">Inventory SRAs</div>
            </div>
        </div>
        <div class="vault-stat-card">
            <div class="vault-stat-icon" style="background:rgba(99,102,241,.12); color:#4f46e5;">
                <i data-lucide="receipt" style="width:22px;height:22px;"></i>
            </div>
            <div>
                <div class="vault-stat-num">{{ $totalServiceCount }}</div>
                <div class="vault-stat-lbl">Service SRAs</div>
            </div>
        </div>
    </div>

    {{-- ── Table Card ── --}}
    <div class="vault-card">

        {{-- Toolbar --}}
        <div class="vault-toolbar">
            <div class="vault-tab-group">
                <button onclick="filterSraType('all')"       class="vault-tab {{ $type==='all'       ? 'active':'' }}" id="tab-all">
                    <i data-lucide="layers"    style="width:15px;height:15px;"></i> All ({{ $totalCombinedCount }})
                </button>
                <button onclick="filterSraType('inventory')" class="vault-tab {{ $type==='inventory' ? 'active':'' }}" id="tab-inventory">
                    <i data-lucide="box"       style="width:15px;height:15px;"></i> Inventory ({{ $totalInventoryCount }})
                </button>
                <button onclick="filterSraType('service')"   class="vault-tab {{ $type==='service'   ? 'active':'' }}" id="tab-service">
                    <i data-lucide="file-text" style="width:15px;height:15px;"></i> Service ({{ $totalServiceCount }})
                </button>
            </div>
            <div class="vault-search-wrap">
                <i data-lucide="search"></i>
                <input type="text" id="sraSearchInput" class="vault-search-input"
                       value="{{ $search }}" placeholder="Search SRA #, supplier, items…"
                       onkeyup="handleSraSearch(event)">
            </div>
        </div>

        {{-- Table --}}
        <div class="vault-table-wrap">
            <table class="vault-table" id="sraVaultTable">
                <thead>
                    <tr>
                        <th>SRA No. &amp; Type</th>
                        <th>Supplier / Donor</th>
                        <th>Category</th>
                        <th>Items</th>
                        <th>Delivery Date</th>
                        <th>Approved By</th>
                        <th class="th-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hasResults = false; @endphp

                    {{-- ── Inventory SRAs ── --}}
                    @if($type === 'all' || $type === 'inventory')
                        @foreach($inventorySras as $batch)
                            @php
                                $hasResults  = true;
                                $isDonor     = ($batch->acquisition_type === 'Donor'
                                             || str_contains(strtolower($batch->supplier_status ?? ''), 'donor')
                                             || str_contains(strtolower($batch->supplier_status ?? ''), 'donation'));
                                $isPartial   = str_contains(strtolower($batch->supplier_status ?? ''), 'partial');
                                $supplierName= trim(preg_replace('/\[.*?\]/', '',
                                    ($isDonor ? ($batch->donor_name ?: $batch->supplier_name) : $batch->supplier_name) ?? 'N/A'));
                                $categoryLabel = $ledgeMap[$batch->ledge_category] ?? ('Cat. '.$batch->ledge_category);
                                $itemsCount    = $batch->items->count();
                                $firstItemDesc = $batch->items->first()?->description ?? 'No items recorded';
                                $sraNo = 'SRA-'.str_pad($batch->id, 6, '0', STR_PAD_LEFT);
                            @endphp
                            <tr class="sra-row sra-type-inventory">
                                {{-- SRA No. & Type --}}
                                <td>
                                    <div class="v-main" style="font-family:monospace; letter-spacing:.03em;">{{ $sraNo }}</div>
                                    <div>
                                        @if($isDonor)
                                            <span class="v-pill v-pill-don"><i data-lucide="gift" style="width:10px;height:10px;"></i> Donor</span>
                                        @else
                                            <span class="v-pill v-pill-inv"><i data-lucide="package-check" style="width:10px;height:10px;"></i> Inventory</span>
                                        @endif
                                        @if($isPartial)
                                            <span class="v-badge-partial">Partial</span>
                                        @else
                                            <span class="v-badge-full">Full</span>
                                        @endif
                                    </div>
                                </td>
                                {{-- Supplier --}}
                                <td>
                                    <div class="v-main">{{ $supplierName }}</div>
                                    <div class="v-sub">{{ $batch->acquisition_type ?: 'Supplier' }} Delivery</div>
                                </td>
                                {{-- Category --}}
                                <td>
                                    <div class="v-main" style="color:var(--primary);">{{ $categoryLabel }}</div>
                                    <div class="v-sub">Ledge {{ $batch->ledge_category }}</div>
                                </td>
                                {{-- Items --}}
                                <td>
                                    <div class="v-main" style="font-size:.83rem; font-weight:750;">{{ \Illuminate\Support\Str::limit($firstItemDesc, 12) }}</div>
                                    <div class="v-sub">
                                        @if($itemsCount > 1) +{{ $itemsCount - 1 }} more item(s) @else 1 item line @endif
                                    </div>
                                </td>
                                {{-- Date --}}
                                <td>
                                    <div class="v-main" style="font-size:.83rem;">{{ \Carbon\Carbon::parse($batch->arrival_date ?: $batch->entry_date)->format('d M Y') }}</div>
                                    <div class="v-sub">{{ \Carbon\Carbon::parse($batch->updated_at)->format('h:i A') }}</div>
                                </td>
                                {{-- Signatories --}}
                                <td>
                                    <div class="sig-stack">
                                        <div class="sig-line">
                                            <span class="sig-check">&#10003;</span>
                                            <span class="sig-role">Stores</span>
                                            <span class="sig-name">{{ $batch->storesApprover->name ?? 'Verified' }}</span>
                                        </div>
                                        <div class="sig-line">
                                            <span class="sig-check">&#10003;</span>
                                            <span class="sig-role">Audit</span>
                                            <span class="sig-name">{{ $batch->auditorApprover->name ?? 'Verified' }}</span>
                                        </div>
                                        <div class="sig-line">
                                            <span class="sig-check">&#10003;</span>
                                            <span class="sig-role">Admin</span>
                                            <span class="sig-name">{{ $batch->adminApprover->name ?? 'Authorized' }}</span>
                                        </div>
                                    </div>
                                </td>
                                {{-- Action --}}
                                <td class="td-center">
                                    <a href="{{ route('receiveditems.sra', ['id' => $batch->id]) }}" target="_blank"
                                       class="v-btn" style="background:var(--primary); color:#fff; box-shadow:0 4px 12px rgba(16,185,129,.2);">
                                        <i data-lucide="printer" style="width:13px;height:13px;"></i> View SRA
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- ── Service SRAs ── --}}
                    @if($type === 'all' || $type === 'service')
                        @foreach($serviceSras as $svc)
                            @php $hasResults = true; @endphp
                            <tr class="sra-row sra-type-service">
                                {{-- SRA No. & Type --}}
                                <td>
                                    <div class="v-main" style="font-family:monospace; letter-spacing:.03em;">{{ $svc->sra_number }}</div>
                                    <div>
                                        <span class="v-pill v-pill-svc"><i data-lucide="wrench" style="width:10px;height:10px;"></i> Service</span>
                                    </div>
                                </td>
                                {{-- Supplier --}}
                                <td>
                                    <div class="v-main">{{ $svc->supplier_name }}</div>
                                    <div class="v-sub">{{ $svc->supplier_address ?: 'Accra' }}</div>
                                </td>
                                {{-- Category --}}
                                <td>
                                    <div class="v-main" style="color:#4338ca;">{{ $svc->category ?: 'Service / Repairs' }}</div>
                                    <div class="v-sub">{{ $svc->dept ?: 'NACOC' }}</div>
                                </td>
                                {{-- Items --}}
                                <td>
                                    <div class="v-main" style="font-size:.83rem; font-weight:750;">{{ \Illuminate\Support\Str::limit($svc->details, 12) }}</div>
                                    <div class="v-sub">Service Order</div>
                                </td>
                                {{-- Date --}}
                                <td>
                                    <div class="v-main" style="font-size:.83rem;">{{ \Carbon\Carbon::parse($svc->date_of_delivery)->format('d M Y') }}</div>
                                    <div class="v-sub">Completed</div>
                                </td>
                                {{-- Signatories --}}
                                <td>
                                    <div class="sig-stack">
                                        <div class="sig-line">
                                            <span class="sig-check">&#10003;</span>
                                            <span class="sig-role">Stores</span>
                                            <span class="sig-name">{{ $svc->stores_approved_by ?: 'Verified' }}</span>
                                        </div>
                                        <div class="sig-line">
                                            <span class="sig-check">&#10003;</span>
                                            <span class="sig-role">Audit</span>
                                            <span class="sig-name">{{ $svc->auditor_approved_by ?: 'Verified' }}</span>
                                        </div>
                                        <div class="sig-line">
                                            <span class="sig-check">&#10003;</span>
                                            <span class="sig-role">Admin</span>
                                            <span class="sig-name">{{ $svc->admin_approved_by ?: 'Authorized' }}</span>
                                        </div>
                                    </div>
                                </td>
                                {{-- Action --}}
                                <td class="td-center">
                                    <a href="{{ route('service-sra.receipt', ['id' => $svc->id]) }}" target="_blank"
                                       class="v-btn" style="background:#4f46e5; color:#fff; box-shadow:0 4px 12px rgba(99,102,241,.2);">
                                        <i data-lucide="printer" style="width:13px;height:13px;"></i> View SRA
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- ── Empty state ── --}}
                    @if(!$hasResults)
                        <tr>
                            <td colspan="7" class="vault-empty">
                                <i data-lucide="archive-x" style="width:40px;height:40px;stroke-width:1.4;color:var(--text-muted);display:block;margin:0 auto 10px;"></i>
                                <div style="font-weight:800;font-size:.97rem;color:var(--text-main);">No Approved SRA Receipts Found</div>
                                <div style="font-size:.81rem;color:var(--text-muted);margin-top:4px;">No fully-authorized SRA receipts matched your current filters.</div>
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
        document.querySelectorAll('.vault-tab').forEach(b => b.classList.remove('active'));
        const btn = document.getElementById('tab-' + type);
        if (btn) btn.classList.add('active');
        document.querySelectorAll('.sra-row').forEach(row => {
            if (type === 'all') { row.style.display = ''; }
            else { row.style.display = row.classList.contains('sra-type-' + type) ? '' : 'none'; }
        });
    }
    function handleSraSearch(e) {
        const val = e.target.value.toLowerCase().trim();
        document.querySelectorAll('.sra-row').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
        });
    }
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection
