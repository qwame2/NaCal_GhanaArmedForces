@extends('layouts.dashboard')

@section('content')
<style>
    /* ══════════════════════════════════════════════════════
       VAULT PAGE — Premium Design
    ══════════════════════════════════════════════════════ */
    .vault-page { padding: 1.75rem 2rem; }

    /* ── Stat cards ───────────────────────────────────── */
    .vault-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    .vault-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 18px;
        padding: 1.15rem 1.4rem;
        display: flex; align-items: center; gap: 1rem;
        box-shadow: 0 2px 14px rgba(0,0,0,.04);
        transition: transform .22s, box-shadow .22s;
        position: relative; overflow: hidden;
    }
    .vault-stat-card::before {
        content: ''; position: absolute;
        top: 0; left: 0; right: 0; height: 3px;
        border-radius: 18px 18px 0 0;
    }
    .vault-stat-card.sc-green::before  { background: linear-gradient(90deg,#10b981,#34d399); }
    .vault-stat-card.sc-blue::before   { background: linear-gradient(90deg,#3b82f6,#60a5fa); }
    .vault-stat-card.sc-indigo::before { background: linear-gradient(90deg,#6366f1,#818cf8); }
    .vault-stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,0,0,.08); }
    .vault-stat-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .vault-stat-num { font-size: 1.7rem; font-weight: 950; color: var(--text-main); line-height: 1.1; }
    .vault-stat-lbl { font-size: .7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-top: 3px; letter-spacing: .05em; }

    /* ── Main card ────────────────────────────────────── */
    .vault-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        box-shadow: 0 4px 24px rgba(0,0,0,.05);
        overflow: hidden;
        margin-bottom: 1.75rem;
    }

    /* ── Toolbar ──────────────────────────────────────── */
    .vault-toolbar {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex; justify-content: space-between; align-items: center;
        flex-wrap: wrap; gap: .85rem;
        background: linear-gradient(to right, rgba(248,250,252,.9), rgba(248,250,252,.5));
    }
    .vault-tab-group { display: flex; gap: 5px; flex-wrap: wrap; }
    .vault-tab {
        padding: .5rem 1rem;
        border-radius: 10px; font-weight: 800; font-size: .79rem;
        border: 1.5px solid var(--border-color);
        background: transparent; color: var(--text-muted);
        cursor: pointer; transition: all .2s;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .vault-tab:hover { border-color: var(--primary); color: var(--primary); background: rgba(16,185,129,.05); }
    .vault-tab.active {
        background: var(--primary); color: #fff; border-color: var(--primary);
        box-shadow: 0 4px 14px rgba(16,185,129,.28);
    }
    .vault-search-wrap { position: relative; width: 100%; max-width: 300px; }
    .vault-search-wrap i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 15px; height: 15px; pointer-events: none; }
    .vault-search-input {
        width: 100%; padding: .6rem .9rem .6rem 2.15rem;
        border-radius: 10px; border: 1.5px solid var(--border-color);
        background: var(--bg-card); color: var(--text-main);
        font-size: .82rem; font-weight: 600; outline: none;
        transition: border-color .2s, box-shadow .2s; box-sizing: border-box;
    }
    .vault-search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(16,185,129,.12); }

    /* ── Table ────────────────────────────────────────── */
    .vault-table-wrap { overflow-x: auto; }
    .vault-table { width: 100%; min-width: 920px; border-collapse: collapse; }

    /* Sticky gradient header */
    .vault-table thead {
        position: sticky; top: 0; z-index: 2;
    }
    .vault-table thead tr {
        background: linear-gradient(to bottom, rgba(248,250,252,1), rgba(244,246,250,.95));
        border-bottom: 2px solid var(--border-color);
    }
    .vault-table th {
        padding: .9rem 1.2rem;
        font-size: .67rem; font-weight: 900;
        text-transform: uppercase; letter-spacing: .07em;
        color: var(--text-muted); white-space: nowrap; text-align: left;
    }
    .vault-table th.th-center { text-align: center; }
    .vault-table th:first-child { padding-left: 1.4rem; }
    .vault-table th:last-child  { padding-right: 1.4rem; }

    /* Body rows */
    .vault-table tbody tr {
        border-bottom: 1px solid rgba(226,232,240,.5);
        transition: background .18s, box-shadow .18s;
        position: relative;
    }
    .vault-table tbody tr:last-child { border-bottom: none; }

    /* Rich hover — lifted feel */
    .vault-table tbody tr:hover {
        background: linear-gradient(to right, rgba(16,185,129,.04), transparent);
        box-shadow: inset 0 0 0 1px rgba(16,185,129,.12);
    }
    .vault-table tbody tr.sra-type-service:hover {
        background: linear-gradient(to right, rgba(99,102,241,.04), transparent);
        box-shadow: inset 0 0 0 1px rgba(99,102,241,.12);
    }

    /* Zebra */
    .vault-table tbody tr:nth-child(even) { background: rgba(248,250,252,.45); }

    /* Left accent bar per type */
    .vault-table tbody tr.sra-type-inventory { border-left: 3px solid #10b981; }
    .vault-table tbody tr.sra-type-service   { border-left: 3px solid #6366f1; }

    /* Cells */
    .vault-table td {
        padding: .9rem 1.2rem;
        font-size: .85rem; color: var(--text-main);
        vertical-align: middle;
    }
    .vault-table td:first-child { padding-left: 1.4rem; }
    .vault-table td:last-child  { padding-right: 1.4rem; }
    .vault-table td.td-center   { text-align: center; }

    /* ── Cell helpers ─────────────────────────────────── */
    .v-main { font-weight: 800; font-size: .86rem; color: var(--text-main); line-height: 1.35; }
    .v-sub  { font-size: .7rem;  color: var(--text-muted); font-weight: 600; margin-top: 2px; }

    /* ── Type pills ───────────────────────────────────── */
    .v-pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 9px; border-radius: 99px;
        font-size: .65rem; font-weight: 900;
        text-transform: uppercase; letter-spacing: .04em;
        white-space: nowrap; margin-top: 5px;
    }
    .v-pill-inv { background: rgba(16,185,129,.1);  color: #047857; border: 1px solid rgba(16,185,129,.22); }
    .v-pill-svc { background: rgba(99,102,241,.1);  color: #4338ca; border: 1px solid rgba(99,102,241,.22); }
    .v-pill-don { background: rgba(245,158,11,.1);  color: #b45309; border: 1px solid rgba(245,158,11,.22); }
    .v-badge-full    { display: inline-block; padding: 2px 7px; border-radius: 99px; font-size: .63rem; font-weight: 900; color: #047857; background: rgba(16,185,129,.1); margin-left: 4px; margin-top: 5px; }
    .v-badge-partial { display: inline-block; padding: 2px 7px; border-radius: 99px; font-size: .63rem; font-weight: 900; color: #b45309; background: rgba(245,158,11,.1); margin-left: 4px; margin-top: 5px; }

    /* ── Signatories ──────────────────────────────────── */
    .sig-stack { display: flex; flex-direction: column; gap: 5px; }
    .sig-line  { display: flex; align-items: center; gap: 6px; }
    .sig-check {
        width: 16px; height: 16px; flex-shrink: 0;
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .62rem; font-weight: 900; color: #fff;
        background: linear-gradient(135deg,#10b981,#059669);
        box-shadow: 0 2px 6px rgba(16,185,129,.35);
    }
    .sig-role {
        font-size: .64rem; font-weight: 900; text-transform: uppercase;
        color: var(--text-muted); letter-spacing: .04em;
        min-width: 36px;
    }
    .sig-name {
        font-size: .73rem; font-weight: 700; color: var(--text-main);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        max-width: 115px;
    }

    /* ── Action button ────────────────────────────────── */
    .v-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: .45rem .95rem; border-radius: 9px;
        font-weight: 800; font-size: .74rem; text-decoration: none;
        letter-spacing: .01em;
        transition: transform .15s, box-shadow .15s, opacity .15s;
        white-space: nowrap;
    }
    .v-btn:hover { transform: translateY(-2px); opacity: .9; }

    /* ── Pagination bar ───────────────────────────────── */
    .vault-pager {
        display: flex; align-items: center; justify-content: space-between;
        padding: .85rem 1.5rem; border-top: 1px solid var(--border-color);
        flex-wrap: wrap; gap: .7rem;
        background: rgba(248,250,252,.5);
    }
    .vault-pager-info {
        font-size: .76rem; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; gap: 6px;
    }
    .vault-pager-btns { display: flex; gap: 4px; flex-wrap: wrap; }
    .vpg-btn {
        padding: 5px 11px; border-radius: 8px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-card); color: var(--text-main);
        font-weight: 800; font-size: .74rem;
        cursor: pointer; transition: all .15s; min-width: 34px;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .vpg-btn:hover:not(:disabled) { border-color: var(--primary); color: var(--primary); background: rgba(16,185,129,.07); }
    .vpg-btn.vpg-active { background: var(--primary); color: #fff; border-color: var(--primary); box-shadow: 0 3px 10px rgba(16,185,129,.25); }
    .vpg-btn:disabled { opacity: .35; cursor: default; }

    /* ── Empty state ──────────────────────────────────── */
    .vault-empty { text-align: center; padding: 4rem 1.5rem; }
    .vault-empty-icon {
        width: 52px; height: 52px; border-radius: 50%;
        background: rgba(226,232,240,.5);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 12px; color: var(--text-muted);
    }
</style>

<div class="vault-page">

    {{-- ══ Page Header ══ --}}
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
        <div>
            <div style="display:inline-flex; align-items:center; gap:7px; padding:3px 12px; border-radius:99px; background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.25); color:#047857; font-size:.7rem; font-weight:900; text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px;">
                <i data-lucide="shield-check" style="width:12px;height:12px;"></i> Stores Officers Archive
            </div>
            <h1 style="font-size:1.8rem; font-weight:950; color:var(--text-main); letter-spacing:-.03em; margin:0 0 5px;">Approved Stores SRA Receipts Vault</h1>
            <p style="font-size:.84rem; color:var(--text-muted); font-weight:600; margin:0;">Fully-authorized repository of all verified Inventory and Service SRA receipts.</p>
        </div>
        <button onclick="window.location.reload()" style="padding:.65rem 1.15rem; border-radius:11px; font-weight:800; font-size:.8rem; display:inline-flex; align-items:center; gap:7px; cursor:pointer; background:var(--bg-card); color:var(--text-main); border:1.5px solid var(--border-color); transition:all .2s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)';" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-main)';">
            <i data-lucide="refresh-cw" style="width:15px;height:15px;"></i> Refresh
        </button>
    </div>

    {{-- ══ Stat Cards ══ --}}
    <div class="vault-stat-grid">
        <div class="vault-stat-card sc-green">
            <div class="vault-stat-icon" style="background:rgba(16,185,129,.12); color:#059669;">
                <i data-lucide="file-check-2" style="width:22px;height:22px;"></i>
            </div>
            <div>
                <div class="vault-stat-num">{{ $totalCombinedCount }}</div>
                <div class="vault-stat-lbl">Total Authorized SRAs</div>
            </div>
        </div>
        <div class="vault-stat-card sc-blue">
            <div class="vault-stat-icon" style="background:rgba(59,130,246,.12); color:#2563eb;">
                <i data-lucide="boxes" style="width:22px;height:22px;"></i>
            </div>
            <div>
                <div class="vault-stat-num">{{ $totalInventoryCount }}</div>
                <div class="vault-stat-lbl">Inventory SRAs</div>
            </div>
        </div>
        <div class="vault-stat-card sc-indigo">
            <div class="vault-stat-icon" style="background:rgba(99,102,241,.12); color:#4f46e5;">
                <i data-lucide="receipt" style="width:22px;height:22px;"></i>
            </div>
            <div>
                <div class="vault-stat-num">{{ $totalServiceCount }}</div>
                <div class="vault-stat-lbl">Service SRAs</div>
            </div>
        </div>
    </div>

    {{-- ══ Table Card ══ --}}
    <div class="vault-card">

        {{-- Toolbar --}}
        <div class="vault-toolbar">
            <div class="vault-tab-group">
                <button onclick="filterSraType('all')"       class="vault-tab {{ $type==='all'       ? 'active':'' }}" id="tab-all">
                    <i data-lucide="layers"    style="width:14px;height:14px;"></i> All &nbsp;<span style="opacity:.7;">({{ $totalCombinedCount }})</span>
                </button>
                <button onclick="filterSraType('inventory')" class="vault-tab {{ $type==='inventory' ? 'active':'' }}" id="tab-inventory">
                    <i data-lucide="box"       style="width:14px;height:14px;"></i> Inventory &nbsp;<span style="opacity:.7;">({{ $totalInventoryCount }})</span>
                </button>
                <button onclick="filterSraType('service')"   class="vault-tab {{ $type==='service'   ? 'active':'' }}" id="tab-service">
                    <i data-lucide="file-text" style="width:14px;height:14px;"></i> Service &nbsp;<span style="opacity:.7;">({{ $totalServiceCount }})</span>
                </button>
            </div>
            <div class="vault-search-wrap">
                <i data-lucide="search"></i>
                <input type="text" id="sraSearchInput" class="vault-search-input"
                       value="{{ $search }}" placeholder="Search SRA #, supplier…"
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
                                $hasResults   = true;
                                $isDonor      = ($batch->acquisition_type === 'Donor'
                                             || str_contains(strtolower($batch->supplier_status ?? ''), 'donor')
                                             || str_contains(strtolower($batch->supplier_status ?? ''), 'donation'));
                                $isPartial    = str_contains(strtolower($batch->supplier_status ?? ''), 'partial');
                                $supplierName = trim(preg_replace('/\[.*?\]/', '',
                                    ($isDonor ? ($batch->donor_name ?: $batch->supplier_name) : $batch->supplier_name) ?? 'N/A'));
                                $catLabel     = $ledgeMap[$batch->ledge_category] ?? ('Cat. '.$batch->ledge_category);
                                $itemsCount   = $batch->items->count();
                                $firstItem    = $batch->items->first()?->description ?? 'No items recorded';
                                $sraNo        = 'SRA-'.str_pad($batch->id, 6, '0', STR_PAD_LEFT);
                            @endphp
                            <tr class="sra-row sra-type-inventory">
                                <td>
                                    <div class="v-main" style="font-family:monospace; letter-spacing:.04em; font-size:.9rem;">{{ $sraNo }}</div>
                                    <div>
                                        @if($isDonor)
                                            <span class="v-pill v-pill-don"><i data-lucide="gift" style="width:9px;height:9px;"></i> Donor</span>
                                        @else
                                            <span class="v-pill v-pill-inv"><i data-lucide="package-check" style="width:9px;height:9px;"></i> Inventory</span>
                                        @endif
                                        @if($isPartial)
                                            <span class="v-badge-partial">Partial</span>
                                        @else
                                            <span class="v-badge-full">Full</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="v-main">{{ $supplierName }}</div>
                                    <div class="v-sub">{{ $batch->acquisition_type ?: 'Supplier' }} Delivery</div>
                                </td>
                                <td>
                                    <div class="v-main" style="color:var(--primary);">{{ $catLabel }}</div>
                                    <div class="v-sub">Ledge {{ $batch->ledge_category }}</div>
                                </td>
                                <td>
                                    <div class="v-main" style="font-size:.82rem; font-weight:750;">{{ \Illuminate\Support\Str::limit($firstItem, 12) }}</div>
                                    <div class="v-sub">
                                        @if($itemsCount > 1)+{{ $itemsCount - 1 }} more
                                        @else 1 item
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="v-main" style="font-size:.82rem;">{{ \Carbon\Carbon::parse($batch->arrival_date ?: $batch->entry_date)->format('d M Y') }}</div>
                                    <div class="v-sub">{{ \Carbon\Carbon::parse($batch->updated_at)->format('h:i A') }}</div>
                                </td>
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
                                <td class="td-center">
                                    <a href="{{ route('receiveditems.sra', ['id' => $batch->id]) }}" target="_blank"
                                       class="v-btn" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; box-shadow:0 4px 14px rgba(16,185,129,.28);">
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
                                <td>
                                    <div class="v-main" style="font-family:monospace; letter-spacing:.04em; font-size:.9rem;">{{ $svc->sra_number }}</div>
                                    <div>
                                        <span class="v-pill v-pill-svc"><i data-lucide="wrench" style="width:9px;height:9px;"></i> Service</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="v-main">{{ $svc->supplier_name }}</div>
                                    <div class="v-sub">{{ $svc->supplier_address ?: 'Accra' }}</div>
                                </td>
                                <td>
                                    <div class="v-main" style="color:#4338ca;">{{ $svc->category ?: 'Service / Repairs' }}</div>
                                    <div class="v-sub">{{ $svc->dept ?: 'NACOC' }}</div>
                                </td>
                                <td>
                                    <div class="v-main" style="font-size:.82rem; font-weight:750;">{{ \Illuminate\Support\Str::limit($svc->details, 12) }}</div>
                                    <div class="v-sub">Service Order</div>
                                </td>
                                <td>
                                    <div class="v-main" style="font-size:.82rem;">{{ \Carbon\Carbon::parse($svc->date_of_delivery)->format('d M Y') }}</div>
                                    <div class="v-sub">Completed</div>
                                </td>
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
                                <td class="td-center">
                                    <a href="{{ route('service-sra.receipt', ['id' => $svc->id]) }}" target="_blank"
                                       class="v-btn" style="background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; box-shadow:0 4px 14px rgba(99,102,241,.28);">
                                        <i data-lucide="printer" style="width:13px;height:13px;"></i> View SRA
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- ── Empty state ── --}}
                    @if(!$hasResults)
                        <tr>
                            <td colspan="7">
                                <div class="vault-empty">
                                    <div class="vault-empty-icon">
                                        <i data-lucide="archive-x" style="width:26px;height:26px;stroke-width:1.4;"></i>
                                    </div>
                                    <div style="font-weight:800;font-size:.97rem;color:var(--text-main);">No Approved SRA Receipts Found</div>
                                    <div style="font-size:.81rem;color:var(--text-muted);margin-top:5px;">No fully-authorized SRA receipts matched your current filters.</div>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- ── Pagination bar ── --}}
        <div class="vault-pager">
            <div class="vault-pager-info">
                <i data-lucide="list" style="width:13px;height:13px;"></i>
                <span id="vault-page-info"></span>
            </div>
            <div class="vault-pager-btns" id="vault-page-btns"></div>
        </div>

    </div><!-- /.vault-card -->

</div><!-- /.vault-page -->

<script>
    /* ═══════════════════════════════════════════════════
       Vault — Pagination · Filter · Search
    ═══════════════════════════════════════════════════ */
    const ROWS_PER_PAGE = 10;
    let currentPage = 1;
    let activeType  = '{{ $type }}';

    const allRows   = () => Array.from(document.querySelectorAll('#sraVaultTable .sra-row'));

    function visibleRows() {
        const q = document.getElementById('sraSearchInput').value.toLowerCase().trim();
        return allRows().filter(row => {
            const typeOk   = activeType === 'all' || row.classList.contains('sra-type-' + activeType);
            const searchOk = !q || row.innerText.toLowerCase().includes(q);
            return typeOk && searchOk;
        });
    }

    function renderPage(page) {
        currentPage = page;
        const rows  = visibleRows();
        const total = rows.length;
        const pages = Math.max(1, Math.ceil(total / ROWS_PER_PAGE));
        if (currentPage > pages) currentPage = pages;

        allRows().forEach(r => r.style.display = 'none');
        const start = (currentPage - 1) * ROWS_PER_PAGE;
        rows.slice(start, start + ROWS_PER_PAGE).forEach(r => r.style.display = '');

        /* Info text */
        const from = total === 0 ? 0 : start + 1;
        const to   = Math.min(start + ROWS_PER_PAGE, total);
        document.getElementById('vault-page-info').textContent =
            total === 0 ? 'No records found'
                        : `Showing ${from}–${to} of ${total} record${total !== 1 ? 's' : ''}`;

        /* Buttons */
        const wrap = document.getElementById('vault-page-btns');
        wrap.innerHTML = '';

        const mkBtn = (label, pg, isActive, disabled) => {
            const b = document.createElement('button');
            b.className = 'vpg-btn' + (isActive ? ' vpg-active' : '');
            b.innerHTML = label;
            b.disabled  = disabled;
            if (!disabled) b.onclick = () => renderPage(pg);
            return b;
        };

        wrap.appendChild(mkBtn('&lsaquo;', currentPage - 1, false, currentPage === 1));

        let s = Math.max(1, currentPage - 3);
        let e = Math.min(pages, s + 6);
        if (e - s < 6) s = Math.max(1, e - 6);

        for (let p = s; p <= e; p++) {
            wrap.appendChild(mkBtn(p, p, p === currentPage, false));
        }

        wrap.appendChild(mkBtn('&rsaquo;', currentPage + 1, false, currentPage === pages));
    }

    function filterSraType(type) {
        activeType = type;
        document.querySelectorAll('.vault-tab').forEach(b => b.classList.remove('active'));
        const btn = document.getElementById('tab-' + type);
        if (btn) btn.classList.add('active');
        renderPage(1);
    }

    function handleSraSearch() { renderPage(1); }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        renderPage(1);
    });
</script>
@endsection
