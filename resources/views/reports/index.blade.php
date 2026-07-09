@extends((auth()->user()->is_admin && auth()->user()->role !== 'Main Admin') ? 'layouts.admin' : 'layouts.dashboard')

@section('title', 'Reports')

@section('content')


<div class="animate-slide-up report-container" style="position: relative;">
    <div class="page-header rpt-hero" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; padding: 2.5rem; border-radius: 28px; border: 1px solid var(--border-color); position: relative; overflow: hidden; background: var(--bg-card);">
        <!-- Decorative orbs -->
        <div class="rpt-orb rpt-orb-1"></div>
        <div class="rpt-orb rpt-orb-2"></div>
        <div class="rpt-orb rpt-orb-3"></div>
        <div style="position: relative; z-index: 1;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                <span class="rpt-engine-badge">Reporting Engine</span>
                <span class="rpt-divider-dot"></span>
                <span style="color: var(--text-muted); font-size: 0.82rem; font-weight: 700; letter-spacing: 0.01em;">Verification &amp; Validation Tool</span>
            </div>
            <h2 class="rpt-hero-title"><span class="rpt-hero-accent">Reports</span></h2>
            <p class="rpt-hero-sub">Collect data from different time periods for official review.</p>
        </div>
        <div style="display: flex; gap: 1rem; position: relative; z-index: 1; flex-wrap: wrap; align-items: center;">
            <div class="period-toggle-group {{ !(auth()->user()->is_admin || auth()->user()->role === 'Main Admin' || auth()->user()->role === 'Auditor' || auth()->user()->can_generate_reports) ? 'restricted-btn' : '' }}">
                <a href="{{ (auth()->user()->is_admin || auth()->user()->role === 'Main Admin' || auth()->user()->role === 'Auditor' || auth()->user()->can_generate_reports) ? route('reports.index', ['period' => 'daily']) : 'javascript:void(0)' }}" class="period-btn {{ $period === 'daily' ? 'active' : '' }}">Daily</a>
                <a href="{{ (auth()->user()->is_admin || auth()->user()->role === 'Main Admin' || auth()->user()->role === 'Auditor' || auth()->user()->can_generate_reports) ? route('reports.index', ['period' => 'monthly']) : 'javascript:void(0)' }}" class="period-btn {{ $period === 'monthly' ? 'active' : '' }}">Monthly</a>
                <a href="{{ (auth()->user()->is_admin || auth()->user()->role === 'Main Admin' || auth()->user()->role === 'Auditor' || auth()->user()->can_generate_reports) ? route('reports.index', ['period' => 'yearly']) : 'javascript:void(0)' }}" class="period-btn {{ $period === 'yearly' ? 'active' : '' }}">Yearly</a>
                <a href="javascript:void(0)" id="custom-period-btn" onclick="toggleCustomDateBar()" class="period-btn {{ $period === 'custom' ? 'active' : '' }}">Choose dates</a>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="report-filter-card glass-card hide-in-print">
        <form method="GET" action="{{ route('reports.index') }}" id="report-filter-form">
            <input type="hidden" name="period" id="form-period-input" value="{{ $period }}">

            <!-- Custom Date Range Panel -->
            <div id="custom-date-bar" class="date-range-panel {{ $period === 'custom' ? 'is-open' : '' }}">
                <div class="date-range-inner">
                    <div class="date-range-header">
                        <div class="date-range-header-left">
                            <span class="date-range-icon-wrap">
                                <i data-lucide="calendar-range" style="width:18px;height:18px;"></i>
                            </span>
                            <div>
                                <div class="date-range-title">Custom Date Range</div>
                                <div class="date-range-subtitle">Filter report data between any two dates</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <button type="button" id="reset-date-btn" onclick="resetCustomDates()" class="date-reset-btn" style="display: {{ ($rawStartDate || $rawEndDate) ? 'inline-flex' : 'none' }};">
                                <i data-lucide="rotate-ccw" style="width:11px;height:11px;"></i> Reset Dates
                            </button>
                            <div class="date-range-badge">CUSTOM</div>
                        </div>
                    </div>
                    <div class="date-inputs-row">
                        <div class="date-input-group">
                            <label for="start-date-input" class="date-input-label">
                                <i data-lucide="play-circle" style="width:12px;height:12px;"></i> From
                            </label>
                            <div class="date-input-wrapper">
                                <i data-lucide="calendar" class="date-input-icon"></i>
                                <input type="date" id="start-date-input" name="start_date" value="{{ $rawStartDate }}" class="date-input-field">
                            </div>
                        </div>
                        <div class="date-range-arrow">
                            <div class="arrow-line"></div>
                            <div class="arrow-dot">
                                <i data-lucide="arrow-right" style="width:14px;height:14px;color:#6366f1;"></i>
                            </div>
                            <div class="arrow-line"></div>
                        </div>
                        <div class="date-input-group">
                            <label for="end-date-input" class="date-input-label">
                                <i data-lucide="stop-circle" style="width:12px;height:12px;"></i> To
                            </label>
                            <div class="date-input-wrapper">
                                <i data-lucide="calendar" class="date-input-icon"></i>
                                <input type="date" id="end-date-input" name="end_date" value="{{ $rawEndDate }}" class="date-input-field">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item Filter Row -->
            <div class="item-filter-panel">
                <!-- Panel Header -->
                <div class="item-filter-header">
                    <div class="item-filter-header-left">
                        <span class="item-filter-icon-wrap">
                            <i data-lucide="package-search" style="width:17px;height:17px;"></i>
                        </span>
                        <div>
                            <div class="item-filter-title">Filter by Item(s)</div>
                            <div class="item-filter-subtitle">Select one or multiple items — leave blank to include all inventory</div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <button type="button" id="select-issued-items-btn" class="glass-btn-sm" style="display: inline-flex; align-items: center; gap: 6px; padding: 0.4rem 0.8rem; border-radius: 10px; font-weight: 800; font-size: 0.75rem; cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); transition: 0.2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
                            <i data-lucide="package-minus" style="width: 13px; height: 13px; color: #f59e0b;"></i>
                            Select Issued Items
                        </button>
                        @if(!empty($selectedItems))
                            <span class="item-filter-count-badge">{{ count($selectedItems) }} selected</span>
                        @else
                            <span class="item-filter-all-badge">All Items</span>
                        @endif
                    </div>
                </div>

                <!-- Selector + Actions -->
                <div class="item-filter-body">
                    <div class="item-selector-wrap">
                        <select name="items[]" id="items-select" class="select2" multiple style="width: 100%;">
                            @foreach($groupedItems as $groupName => $descriptions)
                                <optgroup label="{{ $groupName }}">
                                    @foreach($descriptions as $desc)
                                        <option value="{{ $desc }}" {{ in_array($desc, $selectedItems) ? 'selected' : '' }}>{{ $desc }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="filter-apply-btn {{ !(auth()->user()->is_admin || auth()->user()->role === 'Main Admin' || auth()->user()->role === 'Auditor' || auth()->user()->can_generate_reports) ? 'restricted-btn' : '' }}">
                            <i data-lucide="bar-chart-2" style="width:16px;height:16px;"></i>
                            <span>Generate Report</span>
                        </button>
                        @if(!empty($selectedItems) || $period === 'custom')
                            <a href="{{ route('reports.index', ['period' => 'monthly']) }}" class="filter-clear-btn">
                                <i data-lucide="rotate-ccw" style="width:15px;height:15px;"></i>
                                <span>Reset</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>


    <!-- Generated Report Title Bar -->
    <div class="print-header" style="display: none; margin-bottom: 2rem; border-bottom: 4px solid #1e3a8a; padding-bottom: 20px; text-align: center;">
        <img src="{{ asset('img/NACOC1.png') }}" style="width: 110px; margin-bottom: 12px;">
        <h1 style="font-size: 26pt; font-family: 'Times New Roman', serif; margin: 0; letter-spacing: 1.5px; color: #1e3a8a;">NARCOTICS CONTROL COMMISSION</h1>
        <h3 style="font-size: 15pt; font-family: 'Arial', sans-serif; font-weight: bold; margin: 8px 0 0; color: #3b82f6; text-transform: uppercase;">Official Inventory Operations Report</h3>
        <p style="font-size: 12pt; font-family: 'Times New Roman', serif; margin: 10px 0 0; color: #64748b; font-weight: bold;">{{ strtoupper($dateLabel) }}</p>

        <div style="display: flex; justify-content: center; gap: 40px; margin-top: 15px; font-family: 'Courier New', monospace; font-size: 10pt; color: #333;">
            <div><strong>REF NO:</strong> NACOC/INV/{{ date('Y/m') }}/{{ str_pad(rand(1,999), 3, '0', STR_PAD_LEFT) }}</div>
            <div><strong>DATE PRINTED:</strong> {{ date('d M Y') }}</div>
            <div><strong>STATUS:</strong> <span style="color: #dc2626; font-weight: bold;">CERTIFIED CLASSIFIED</span></div>
        </div>
    </div>

    <div class="print-actions-bar" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
        <h3 class="print-date-label" style="font-size: 1.25rem; font-weight: 900; color: var(--text-main); margin: 0;">{{ $dateLabel }}</h3>
        <button onclick="triggerPrintMode()" id="print-report-btn" class="btn-primary {{ !(auth()->user()->is_admin || auth()->user()->role === 'Main Admin' || auth()->user()->role === 'Auditor' || auth()->user()->can_generate_reports) ? 'restricted-btn' : '' }}" {{ !(auth()->user()->is_admin || auth()->user()->role === 'Main Admin' || auth()->user()->role === 'Auditor' || auth()->user()->can_generate_reports) ? 'disabled' : '' }} style="padding: 0.75rem 1.5rem; border-radius: 14px; border: none; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.25);">
            <i data-lucide="printer" style="width: 18px;"></i> Export or Print
        </button>
    </div>

    <!-- ═══════════════ LIVE-UPDATED CONTENT AREA ═══════════════ -->
    <div id="report-content-area" style="position: relative;">

    <div class="stats-charts-print-layout">
    <!-- Quick Stats -->
    <div class="stats-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
        <div class="glass-card stat-card stat-card-received">
            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(6,182,212,0.1)); color: #10b981; box-shadow: 0 8px 20px rgba(16,185,129,0.18);">
                <i data-lucide="package-plus"></i>
            </div>
            <div style="flex: 1;">
                <div class="stat-label">Total Received</div>
                <div class="stat-value" style="color: #10b981;">{{ number_format((float)$totalReceivedQty) }} <span class="stat-unit">Item(s)</span></div>
                <div class="stat-subtitle">{{ $totalReceivedBatches }} Received Batches</div>
            </div>
        </div>

        <div class="glass-card stat-card stat-card-issued">
            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(245,158,11,0.15), rgba(251,191,36,0.1)); color: #f59e0b; box-shadow: 0 8px 20px rgba(245,158,11,0.18);">
                <i data-lucide="package-minus"></i>
            </div>
            <div style="flex: 1;">
                <div class="stat-label">Total Issued</div>
                <div class="stat-value" style="color: #f59e0b;">{{ number_format((float)$totalIssuedQty) }} <span class="stat-unit">Item(s)</span></div>
                <div class="stat-subtitle">{{ $totalIssuedBatches }} Issued Records</div>
            </div>
        </div>

        <div class="glass-card stat-card stat-card-net">
            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(139,92,246,0.1)); color: #6366f1; box-shadow: 0 8px 20px rgba(99,102,241,0.18);">
                <i data-lucide="activity"></i>
            </div>
            <div style="flex: 1;">
                <div class="stat-label">Stock Balance</div>
                <div class="stat-value" style="color: #6366f1;">{{ number_format(max(0, (float)$totalReceivedQty - (float)$totalIssuedQty)) }} <span class="stat-unit">Item(s)</span></div>
                <div class="stat-subtitle">Period Surplus (Received − Issued)</div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Section - Stock Receipts / Single Item -->
    <div class="glass-card print-chart-card rpt-chart-card received-chart-card" style="padding: 2rem; border-radius: 24px; border: 1px solid var(--border-color); margin-bottom: 2.5rem; background: var(--bg-card); {{ ($totalReceivedQty > 0 || $totalIssuedQty > 0) ? '' : 'display: none;' }}">
        <div class="rpt-section-header" style="margin-bottom: 1.75rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span class="rpt-section-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <i data-lucide="bar-chart-2" style="width:17px;height:17px;"></i>
                </span>
                <div>
                    <div style="font-size: 1rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.01em;">Stock Receipts Visualization</div>
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 2.5rem;">
            <div id="single-item-chart-wrap" style="{{ count($selectedItems) === 1 ? '' : 'display: none;' }}">
                <div class="rpt-chart-group">
                    <div class="rpt-chart-label" style="color: var(--text-muted);">Operations Volume — {{ count($selectedItems) === 1 ? head($selectedItems) : '' }}</div>
                    <div id="single-item-bar-chart" style="width: 100%;"></div>
                </div>
            </div>

            <div id="received-chart-wrap" style="{{ (count($selectedItems) !== 1 && $totalReceivedQty > 0 && $receivedDistribution->count() > 0) ? '' : 'display: none;' }}">
                <div class="rpt-chart-group">
                    <div class="rpt-chart-label" style="color: #10b981;">
                        <span class="rpt-chart-dot" style="background:#10b981;"></span>
                        Stock Receipts — Top {{ $receivedDistribution->count() }} Items
                    </div>
                    <div id="received-bar-chart" style="width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
    </div> {{-- /.stats-charts-print-layout --}}

    <!-- Analytics Charts Section - Issuance -->
    <div class="glass-card print-chart-card rpt-chart-card issued-chart-card" style="padding: 2rem; border-radius: 24px; border: 1px solid var(--border-color); margin-bottom: 2.5rem; background: var(--bg-card); {{ (count($selectedItems) !== 1 && $totalIssuedQty > 0 && $issuedDistribution->count() > 0) ? '' : 'display: none;' }}">
        <div class="rpt-section-header" style="margin-bottom: 1.75rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span class="rpt-section-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <i data-lucide="bar-chart-2" style="width:17px;height:17px;"></i>
                </span>
                <div>
                    <div style="font-size: 1rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.01em;">Issuance Visualization</div>
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 2.5rem;">
            <div id="issued-chart-wrap" style="width: 100%;">
                <div class="rpt-chart-group">
                    <div class="rpt-chart-label" style="color: #f59e0b;">
                        <span class="rpt-chart-dot" style="background:#f59e0b;"></span>
                        Issuance — Top {{ $issuedDistribution->count() }} Items
                    </div>
                    <div id="issued-bar-chart" style="width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>



    <!-- Combined Inventory Movement Ledger -->
    @php
        // Fetch current stock balances per item to calculate historical values backwards
        $currentBalances = [];
        $balancesQuery = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->where('inventory_batches.approval_status', '=', 'approved')
            ->selectRaw('TRIM(inventory_items.description) as description, SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
            ->groupBy(\DB::raw('TRIM(inventory_items.description)'))
            ->get();
        foreach ($balancesQuery as $b) {
            $currentBalances[trim($b->description)] = (float)$b->total_stock;
        }

        // Fetch all unique item descriptions to construct the supplier/donor mapping
        $uniqueDescs = collect()
            ->concat($recentReceivals->pluck('description'))
            ->concat($recentIssues->pluck('description'))
            ->unique()
            ->filter()
            ->toArray();

        $itemSources = [];
        if (!empty($uniqueDescs)) {
            $rawSources = \DB::table('inventory_items')
                ->join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                ->whereIn('inventory_items.description', $uniqueDescs)
                ->select('inventory_items.description', 'inventory_batches.supplier_name', 'inventory_batches.donor_name', 'inventory_batches.acquisition_type')
                ->get();
            foreach ($rawSources as $rs) {
                $desc = trim($rs->description);
                $src = $rs->acquisition_type === 'Donor' ? ($rs->donor_name ?: $rs->supplier_name) : $rs->supplier_name;
                $src = preg_replace('/\s\[.*\]$/', '', $src ?: '');
                if ($src && strtolower($src) !== 'system') {
                    if (!isset($itemSources[$desc])) {
                        $itemSources[$desc] = [];
                    }
                    if (!in_array($src, $itemSources[$desc])) {
                        $itemSources[$desc][] = $src;
                    }
                }
            }
            $itemSources = array_map(function($srcs) {
                return implode(', ', $srcs);
            }, $itemSources);
        }

        // Merge both collections, normalise fields, then sort by date desc
        $rawTransactions = $recentReceivals->map(function($r) use ($ledgeMap) {
            $source = $r->acquisition_type === 'Donor' ? ($r->donor_name ?: $r->supplier_name) : $r->supplier_name;
            return [
                'date_received' => $r->entry_date,
                'date_issued'   => null,
                'date_sort'     => $r->entry_date,
                'type'          => 'Received',
                'category'      => $ledgeMap[$r->ledge_category] ?? ('Category ' . $r->ledge_category),
                'description'   => $r->description,
                'serial_number' => $r->serial_number,
                'ref'           => preg_replace('/\s\[.*\]$/', '', $source ?: 'System'),
                'ref_label'     => 'Supplier / Source',
                'quantity'      => $r->qty ?? 0,
                'stock_bal'     => !is_null($r->book_qty) ? $r->book_qty : ($r->stock_balance ?? 0),
                'previous_stock'=> '—',
                'variance'      => $r->variance ?? '—',
                'status'        => '—',
                'department'    => '—',
                'sources'       => null,
            ];
        })->merge($recentIssues->map(function($i) use ($ledgeMap, $itemSources) {
            return [
                'date_received' => $i->received_date,
                'date_issued'   => $i->entry_date,
                'date_sort'     => $i->entry_date,
                'type'          => 'Issued',
                'category'      => $ledgeMap[$i->ledge_category] ?? ('Category ' . $i->ledge_category),
                'description'   => $i->description,
                'serial_number' => null,
                'ref'           => $i->beneficiary ?? '—',
                'ref_label'     => 'Beneficiary / Dept.',
                'quantity'      => $i->quantity ?? 0,
                'stock_bal'     => 0,
                'previous_stock'=> '—',
                'variance'      => '—',
                'status'        => $i->issuance_type ?? 'Permanent',
                'department'    => $i->department ?? '—',
                'sources'       => $itemSources[trim($i->description)] ?? null,
            ];
        }));

        $transactionsByItem = $rawTransactions->groupBy(function($t) {
            return trim($t['description']);
        });

        $processedTransactions = collect();

        foreach ($transactionsByItem as $desc => $group) {
            // Sort by date_sort descending (newest to oldest)
            $sortedGroup = $group->sortByDesc(function($t) {
                return $t['date_sort'];
            });

            $runningBalance = $currentBalances[$desc] ?? 0.0;

            foreach ($sortedGroup as $key => $t) {
                $qty = (float)str_replace(',', '', $t['quantity']);
                if ($t['type'] === 'Received') {
                    $t['stock_bal'] = $runningBalance;
                    $t['previous_stock'] = $runningBalance - $qty;
                    $runningBalance -= $qty;
                } else { // Issued
                    $t['stock_bal'] = $runningBalance;
                    $t['previous_stock'] = $runningBalance + $qty;
                    $runningBalance += $qty;
                }
                $sortedGroup[$key] = $t;
            }
            $processedTransactions = $processedTransactions->merge($sortedGroup);
        }

        $allTransactions = $processedTransactions->sortByDesc(function($item) {
            return $item['date_sort'];
        })->values();
    @endphp

    <div class="glass-card print-table-wrapper unified-ledger-card rpt-log-card" style="border-radius: 24px; border: 1px solid var(--border-color); overflow: hidden;">

        {{-- Card Header --}}
        <div class="unified-ledger-header">
            <div class="unified-ledger-header-left">
                <span class="unified-ledger-icon">
                    <i data-lucide="book-open" style="width:18px;height:18px;"></i>
                </span>
                <div>
                    <div class="unified-ledger-title">Item(s) Report</div>
                    <div class="unified-ledger-subtitle"> Received &amp; Issued items in order of date</div>
                </div>
            </div>
            <div class="unified-ledger-meta">
                <span class="unified-ledger-meta-item received-meta">
                    <i data-lucide="arrow-down-circle" style="width:12px;height:12px;"></i>
                    {{ $recentReceivals->count() }} Receipts
                </span>
                <span class="unified-ledger-meta-item issued-meta">
                    <i data-lucide="arrow-up-circle" style="width:12px;height:12px;"></i>
                    {{ $recentIssues->count() }} Issued
                </span>
                <span class="unified-ledger-meta-item total-meta">
                    {{ $allTransactions->count() }} Total Records
                </span>
            </div>
        </div>

        {{-- Table --}}
        <div id="table-container" class="custom-scroll table-responsive" style="overflow-x: auto; {{ $allTransactions->count() > 0 ? '' : 'display: none;' }}">
            <table class="formal-table unified-table rpt-unified-table" style="width: 100%; min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 10%; padding-left: 600px;">Date</th>
                        <th style="width: 10%; text-align:center;">Type</th>
                        <th style="width: 10%;">Item / Category</th>
                        <th style="width: 10%;">Supplier / Department</th>
                        <th style="text-align:right; width: 10%;">Qty</th>
                        <th style="text-align:right; width: 10%;">Prev. Stock</th>
                        <th style="text-align:right; width: 10%;">Stock Bal.</th>
                        <th style="text-align:right; width: 10%; padding-right: 600px;">Variance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allTransactions as $row)
                    <tr class="ledger-row ledger-row-{{ strtolower($row['type']) }}">
                        <td data-label="Date" class="ledger-date">
                            {{ $row['date_sort'] ? \Carbon\Carbon::parse($row['date_sort'])->format('d M Y') : '—' }}
                        </td>
                        <td data-label="Type" style="text-align:center;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                @if($row['type'] === 'Received')
                                    <span class="type-badge received-badge">
                                        <i data-lucide="arrow-down-circle" style="width:12px;height:12px;"></i>
                                        Received
                                    </span>
                                @else
                                    <span class="type-badge issued-badge">
                                        <i data-lucide="arrow-up-circle" style="width:12px;height:12px;"></i>
                                        Issued
                                    </span>
                                    @if($row['status'] === 'Temporary')
                                        <span style="font-size: 0.65rem; font-weight: 800; color: #b45309; background: rgba(245,158,11,0.12); padding: 1px 6px; border-radius: 4px; border: 1px solid rgba(245,158,11,0.2); text-transform: uppercase;">
                                            Temporary
                                        </span>
                                    @else
                                        <span style="font-size: 0.65rem; font-weight: 800; color: #1e3a8a; background: rgba(99,102,241,0.08); padding: 1px 6px; border-radius: 4px; border: 1px solid rgba(99,102,241,0.15); text-transform: uppercase;">
                                            Permanent
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td data-label="Item / Category">
                            <div>
                                <span class="item-desc" style="font-weight: 800; display: block; margin-bottom: 2px; color: var(--text-main);">{{ $row['description'] }}</span>
                                @if(!empty($row['serial_number']))
                                    @php
                                        $snList = array_filter(array_map('trim', explode(',', $row['serial_number'])));
                                        $count = count($snList);
                                    @endphp
                                    @if($count > 0)
                                        <div class="serial-numbers-wrapper" style="margin-top: 4px; display: inline-flex; flex-wrap: wrap; align-items: center; gap: 4px;">
                                            <div style="display: inline-flex; align-items: center; flex-wrap: wrap; gap: 4px; background: rgba(99, 102, 241, 0.08); color: var(--primary); font-size: 0.72rem; padding: 2px 8px; border-radius: 6px; font-weight: 800; word-break: break-word; white-space: normal; max-width: 250px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; margin-right:2px;"><path d="M3 5v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2z"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg>
                                                S/N: {{ implode(', ', array_slice($snList, 0, 3)) }}@if($count > 3)<span class="dots">...</span><span class="more-sns" style="display: none;">, {{ implode(', ', array_slice($snList, 3)) }}</span>@endif
                                            </div>
                                            @if($count > 3)
                                                <button type="button" class="toggle-sns-btn" onclick="let container = this.previousElementSibling; let more = container.querySelector('.more-sns'); let dots = container.querySelector('.dots'); let isHidden = more.style.display === 'none'; more.style.display = isHidden ? 'inline' : 'none'; dots.style.display = isHidden ? 'none' : 'inline'; this.querySelector('.chevron-icon').style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';" style="background: transparent; border: none; padding: 2px; cursor: pointer; display: inline-flex; align-items: center; color: var(--primary); outline: none; transition: all 0.2s; border-radius: 4px;" onmouseover="this.style.background='rgba(99, 102, 241, 0.15)';" onmouseout="this.style.background='transparent';" title="Show more serial numbers">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="chevron-icon" style="transition: transform 0.2s;"><polyline points="6 9 12 15 18 9"/></svg>
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                                <span class="cat-badge" style="font-size: 0.7rem; padding: 2px 6px; border-radius: 6px;">{{ $row['category'] }}</span>
                            </div>
                        </td>
                        <td data-label="Supplier / Department">
                            @if($row['type'] === 'Received')
                                <span style="font-weight: 600; color: var(--text-main);">{{ $row['ref'] }}</span>
                            @else
                                <span style="font-weight: 800; color: var(--text-main); text-transform: uppercase; font-size: 0.76rem; letter-spacing: 0.02em;">{{ $row['department'] }}</span>
                                @if(!empty($row['sources']))
                                    <span style="display: block; font-size: 0.7rem; color: var(--text-muted); font-weight: 600; margin-top: 2px; text-transform: none;">(Sourced from: {{ $row['sources'] }})</span>
                                @endif
                            @endif
                        </td>
                        <td data-label="Qty" style="text-align:right;" class="qty-cell qty-{{ strtolower($row['type']) }}">
                            {{ is_numeric(str_replace(',', '', $row['quantity'])) ? number_format((float)str_replace(',', '', $row['quantity']), 0) : $row['quantity'] }}
                        </td>
                        <td data-label="Prev. Stock" class="prev-cell" style="text-align:right; font-weight:700; color: var(--text-main);">
                            {{ is_numeric(str_replace(',', '', $row['previous_stock'])) ? number_format((float)str_replace(',', '', $row['previous_stock']), 0) : $row['previous_stock'] }}
                        </td>
                        <td data-label="Stock Bal." class="bal-cell" style="text-align:right;">
                            {{ is_numeric(str_replace(',', '', $row['stock_bal'])) ? number_format((float)str_replace(',', '', $row['stock_bal']), 0) : $row['stock_bal'] }}
                        </td>
                        <td data-label="Variance" class="variance-cell" style="text-align:right;">
                            @if($row['type'] === 'Received')
                                @if(is_numeric(str_replace(',', '', $row['variance'])))
                                    @php
                                        $varVal = (float)str_replace(',', '', $row['variance']);
                                    @endphp
                                    {{ $varVal > 0 ? '+' : '' }}{{ number_format($varVal, 0) }}
                                @else
                                    {{ $row['variance'] }}
                                @endif
                            @else
                                <span style="color: var(--text-muted);">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="ledger-totals-row">
                        <td colspan="4" style="font-weight:900; font-size:0.82rem; text-transform:uppercase; letter-spacing:0.05em; padding: 14px 20px;">
                            Period Totals
                        </td>
                        <td style="text-align:right; font-weight:900; color: var(--primary); padding: 14px 20px;" colspan="4">
                            ↓ {{ number_format((float)$totalReceivedQty) }} received &nbsp;|&nbsp;
                            ↑ {{ number_format((float)$totalIssuedQty) }} issued
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div id="no-transactions-placeholder" style="padding: 3rem; text-align: center; color: var(--text-muted); {{ $allTransactions->count() > 0 ? 'display: none;' : '' }}">
            <i data-lucide="inbox" style="width: 40px; height: 40px; margin: 0 auto 1rem; display: block; opacity: 0.3;"></i>
            <em>No transactions recorded for this period.</em>
        </div>
    </div> {{-- /.unified-ledger-card --}}

    </div> {{-- /#report-content-area --}}
    <div class="print-only print-footer" style="display: none; margin-top: 30px;">
        <div style="text-align: center; font-size: 8pt; color: #555;">
            Generated from NACOC Secure Framework at {{ date('H:i T, d/m/y') }}
        </div>
    </div>

</div>

<style>
    .main-wrapper > *:not(header) {
        max-width: 2000px !important;
    }

    /* ══════════════════════════════════════════════
       HERO HEADER
    ══════════════════════════════════════════════ */
    .rpt-hero { box-shadow: 0 8px 40px rgba(99,102,241,0.06), 0 2px 8px rgba(0,0,0,0.03); }
    .rpt-orb {
        position: absolute; border-radius: 50%; pointer-events: none; z-index: 0;
        animation: rptOrbFloat 8s ease-in-out infinite;
    }
    .rpt-orb-1 {
        width: 320px; height: 320px; top: -140px; right: -60px;
        background: radial-gradient(circle, rgba(99,102,241,0.13) 0%, transparent 65%);
        animation-delay: 0s;
    }
    .rpt-orb-2 {
        width: 200px; height: 200px; bottom: -80px; right: 200px;
        background: radial-gradient(circle, rgba(16,185,129,0.09) 0%, transparent 65%);
        animation-delay: 3s;
    }
    .rpt-orb-3 {
        width: 150px; height: 150px; top: -40px; left: 300px;
        background: radial-gradient(circle, rgba(139,92,246,0.08) 0%, transparent 65%);
        animation-delay: 5s;
    }
    @keyframes rptOrbFloat {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50%       { transform: translate(8px, -12px) scale(1.03); }
    }
    .rpt-engine-badge {
        background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(139,92,246,0.1));
        color: #6366f1;
        font-size: 0.68rem; font-weight: 900;
        padding: 0.3rem 0.9rem; border-radius: 99px;
        text-transform: uppercase; letter-spacing: 0.08em;
        border: 1px solid rgba(99,102,241,0.2);
    }
    .rpt-divider-dot {
        width: 4px; height: 4px; border-radius: 50%;
        background: var(--text-muted); opacity: 0.4; flex-shrink: 0;
    }
    .rpt-hero-title {
        font-size: 2.6rem; font-weight: 950;
        color: var(--text-main); margin: 0;
        letter-spacing: -0.045em; line-height: 1.1;
    }
    .rpt-hero-accent {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .rpt-hero-sub {
        color: var(--text-muted); font-size: 0.95rem;
        font-weight: 600; margin-top: 8px;
    }

    /* ══════════════════════════════════════════════
       STAT CARDS
    ══════════════════════════════════════════════ */
    .stat-card-received { border-top: 3px solid rgba(16,185,129,0.5) !important; }
    .stat-card-issued   { border-top: 3px solid rgba(245,158,11,0.5) !important; }
    .stat-card-net      { border-top: 3px solid rgba(99,102,241,0.5) !important; }
    .stat-card {
        position: relative; overflow: hidden;
        transition: transform 0.3s cubic-bezier(0.4,0,0.2,1), box-shadow 0.3s ease;
    }
    .stat-card::after {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.02) 100%);
        pointer-events: none;
    }
    .stat-card:hover {
        transform: translateY(-5px) scale(1.01) !important;
        box-shadow: 0 20px 50px rgba(0,0,0,0.08) !important;
    }

    /* ══════════════════════════════════════════════
       SECTION HEADER SHARED
    ══════════════════════════════════════════════ */
    .rpt-section-icon {
        width: 38px; height: 38px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: white; flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(99,102,241,0.3);
    }

    /* ══════════════════════════════════════════════
       CHART CARD
    ══════════════════════════════════════════════ */
    .rpt-chart-card {
        background: linear-gradient(180deg, var(--bg-card) 0%, var(--bg-main) 100%) !important;
    }
    .rpt-chart-group { position: relative; }
    .rpt-chart-label {
        display: inline-flex; align-items: center; gap: 8px;
        font-size: 0.76rem; font-weight: 900;
        text-transform: uppercase; letter-spacing: 0.06em;
        margin-bottom: 1rem;
        padding: 0.3rem 0.9rem 0.3rem 0.4rem;
        border-radius: 99px;
        background: rgba(255,255,255,0.04);
    }
    .rpt-chart-dot {
        display: inline-block; width: 8px; height: 8px;
        border-radius: 50%; flex-shrink: 0;
    }
    .rpt-chart-divider {
        border: none;
        border-top: 1px dashed var(--border-color);
        margin: 0.5rem 0;
    }

    /* ══════════════════════════════════════════════
       UNIFIED INVENTORY LOG
    ══════════════════════════════════════════════ */
    .unified-ledger-card { margin-bottom: 2.5rem; }
    .rpt-log-card { box-shadow: 0 4px 30px rgba(0,0,0,0.04) !important; }
    .unified-ledger-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.75rem;
        background: linear-gradient(135deg, rgba(99,102,241,0.07) 0%, rgba(16,185,129,0.04) 100%);
        border-bottom: 1px solid var(--border-color);
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .unified-ledger-header-left { display: flex; align-items: center; gap: 0.85rem; }
    .unified-ledger-icon {
        width: 42px; height: 42px; border-radius: 13px;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        display: flex; align-items: center; justify-content: center;
        color: white; box-shadow: 0 6px 16px rgba(99,102,241,0.32); flex-shrink: 0;
    }
    .unified-ledger-title { font-size: 1rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.01em; }
    .unified-ledger-subtitle { font-size: 0.72rem; font-weight: 600; color: var(--text-muted); margin-top: 2px; }
    .unified-ledger-meta { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
    .unified-ledger-meta-item {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 0.7rem; font-weight: 800; padding: 0.3rem 0.8rem;
        border-radius: 99px; letter-spacing: 0.03em;
    }
    .received-meta  { background: rgba(16,185,129,0.1);  color: #059669; border: 1px solid rgba(16,185,129,0.25); }
    .issued-meta    { background: rgba(245,158,11,0.1);  color: #b45309; border: 1px solid rgba(245,158,11,0.25); }
    .total-meta     { background: rgba(99,102,241,0.08); color: #4f46e5; border: 1px solid rgba(99,102,241,0.2);  }

    /* TABLE */
    .rpt-unified-table thead tr th {
        background: linear-gradient(180deg, rgba(99,102,241,0.07) 0%, rgba(99,102,241,0.03) 100%) !important;
        font-size: 0.7rem !important; padding: 13px 14px !important; white-space: nowrap;
        border-bottom: 2px solid rgba(99,102,241,0.12) !important;
    }
    .ledger-row td { padding: 11px 14px !important; font-size: 0.87rem !important; vertical-align: middle; }
    /* Zebra striping */
    .rpt-unified-table tbody tr:nth-child(even) td { background: rgba(99,102,241,0.018) !important; }
    .ledger-row-received { border-left: 3px solid #10b981 !important; }
    .ledger-row-received:hover td { background: rgba(16,185,129,0.05) !important; }
    .ledger-row-issued   { border-left: 3px solid #f59e0b !important; }
    .ledger-row-issued:hover td   { background: rgba(245,158,11,0.05) !important; }

    .type-badge {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 0.73rem; font-weight: 800; letter-spacing: 0.02em;
        padding: 4px 11px; border-radius: 99px; white-space: nowrap;
    }
    .received-badge { background: rgba(16,185,129,0.12); color: #059669; border: 1px solid rgba(16,185,129,0.28); }
    .issued-badge   { background: rgba(245,158,11,0.12);  color: #b45309; border: 1px solid rgba(245,158,11,0.28);  }

    .cat-badge {
        display: inline-block; font-size: 0.67rem; font-weight: 800;
        background: rgba(99,102,241,0.09); color: #4f46e5;
        border: 1px solid rgba(99,102,241,0.18); padding: 2px 9px; border-radius: 7px; white-space: nowrap;
    }
    .ledger-date { font-weight: 700; font-size: 0.79rem !important; white-space: nowrap; color: var(--text-muted); }
    .item-desc  { font-weight: 700 !important; color: var(--text-main) !important; }
    .mono-cell  { font-family: 'Courier New', monospace !important; font-size: 0.8rem !important; color: var(--text-muted); }
    .qty-cell   { font-weight: 900 !important; font-size: 0.9rem !important; }
    .qty-received { color: #10b981 !important; }
    .qty-issued   { color: #d97706 !important; }
    .bal-cell   { font-weight: 700; font-size: 0.82rem !important; color: var(--text-muted); }
    .variance-cell { font-weight: 700; font-size: 0.82rem !important; }
    .ledger-totals-row td {
        background: linear-gradient(135deg, rgba(99,102,241,0.06) 0%, rgba(16,185,129,0.04) 100%) !important;
        border-top: 2px solid rgba(99,102,241,0.12) !important;
        font-size: 0.85rem; color: var(--text-main);
    }

    /* ══════════════════════════════════════════════
       PREMIUM FILTER CARD
    ══════════════════════════════════════════════ */
    .report-filter-card {
        padding: 1.75rem;
        border-radius: 24px;
        border: 1px solid var(--border-color);
        margin-bottom: 2rem;
        background: var(--bg-card);
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    /* ── Date Range Panel ── */
    .date-range-panel {
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transform: translateY(-8px);
        transition: max-height 0.45s cubic-bezier(0.4,0,0.2,1),
                    opacity 0.3s ease,
                    transform 0.3s ease,
                    margin-bottom 0.3s ease;
        margin-bottom: 0;
    }
    .date-range-panel.is-open {
        max-height: 300px;
        opacity: 1;
        transform: translateY(0);
        margin-bottom: 1.5rem;
    }
    .date-range-inner {
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid rgba(99,102,241,0.2);
        box-shadow: 0 4px 24px rgba(99,102,241,0.08), inset 0 0 0 1px rgba(99,102,241,0.06);
    }
    .date-range-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, rgba(99,102,241,0.12) 0%, rgba(139,92,246,0.08) 100%);
        border-bottom: 1px solid rgba(99,102,241,0.15);
    }
    .date-range-header-left {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }
    .date-range-icon-wrap {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 12px rgba(99,102,241,0.35);
        flex-shrink: 0;
    }
    .date-range-title {
        font-size: 0.95rem;
        font-weight: 900;
        color: var(--text-main);
        letter-spacing: -0.01em;
    }
    .date-range-subtitle {
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-top: 1px;
    }
    .date-range-badge {
        font-size: 0.62rem;
        font-weight: 900;
        letter-spacing: 0.1em;
        color: #6366f1;
        background: rgba(99,102,241,0.1);
        border: 1px solid rgba(99,102,241,0.25);
        padding: 0.3rem 0.75rem;
        border-radius: 99px;
    }

    /* ── Date Inputs Row ── */
    .date-inputs-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: var(--bg-card);
        flex-wrap: wrap;
    }
    .date-input-group {
        flex: 1;
        min-width: 160px;
    }
    .date-input-label {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--primary);
        letter-spacing: 0.06em;
        margin-bottom: 8px;
    }
    .date-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .date-input-icon {
        position: absolute;
        left: 14px;
        width: 16px;
        height: 16px;
        color: var(--text-muted);
        pointer-events: none;
        z-index: 1;
    }
    .date-input-field {
        width: 100%;
        padding: 0.7rem 1rem 0.7rem 2.5rem;
        border-radius: 14px;
        border: 2px solid var(--border-color);
        background: var(--bg-main);
        color: var(--text-main);
        font-weight: 700;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        box-sizing: border-box;
    }
    .date-input-field:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99,102,241,0.12);
        background: var(--bg-card);
    }
    .date-reset-btn {
        background: transparent;
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #ef4444;
        border-radius: 99px;
        padding: 4px 12px;
        font-size: 0.68rem;
        font-weight: 800;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .date-reset-btn:hover {
        background: rgba(239, 68, 68, 0.08) !important;
        border-color: rgba(239, 68, 68, 0.45) !important;
        transform: translateY(-1px);
    }
    .date-reset-btn:active {
        transform: translateY(0);
    }
    .date-range-arrow {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
        padding-top: 22px; /* align with inputs after label */
    }
    .arrow-line {
        width: 24px;
        height: 2px;
        background: linear-gradient(90deg, rgba(99,102,241,0.2), rgba(99,102,241,0.5));
        border-radius: 2px;
    }
    .arrow-dot {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: rgba(99,102,241,0.1);
        border: 1.5px solid rgba(99,102,241,0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* ── Item Filter Row ── */
    .filter-row {
        display: flex;
        align-items: flex-end;
        gap: 1.25rem;
        flex-wrap: wrap;
    }
    .filter-item-selector {
        min-width: 280px;
        flex: 1;
    }
    .filter-label {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.73rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.05em;
        margin-bottom: 8px;
    }
    .filter-actions {
        display: flex;
        gap: 0.75rem;
        flex-shrink: 0;
    }
    .filter-apply-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.75rem 1.6rem;
        border-radius: 14px;
        border: none;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        font-weight: 800;
        font-size: 0.9rem;
        cursor: pointer;
        box-shadow: 0 6px 20px rgba(99,102,241,0.3);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 44px;
    }
    .filter-apply-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(99,102,241,0.38);
    }
    .filter-apply-btn:active {
        transform: translateY(0);
    }
    .filter-clear-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0.75rem 1.25rem;
        border-radius: 14px;
        border: 1.5px solid var(--border-color);
        background: transparent;
        color: var(--text-muted);
        font-weight: 800;
        font-size: 0.88rem;
        text-decoration: none;
        transition: all 0.2s ease;
        height: 44px;
        box-sizing: border-box;
    }
    .filter-clear-btn:hover {
        border-color: #ef4444;
        color: #ef4444;
        background: rgba(239,68,68,0.06);
    }

    /* ── Responsive ── */
    @media (max-width: 700px) {
        .date-range-arrow { display: none; }
        .date-inputs-row { gap: 0.75rem; }
        .date-range-badge { display: none; }
        .filter-apply-btn span { display: none; }
        .filter-clear-btn span { display: none; }
    }

    /* Unique Localized CSS for Reports */
    .period-toggle-group {
        display: flex;
        background: var(--bg-main);
        border-radius: 14px;
        padding: 4px;
        border: 1px solid var(--border-color);
        box-shadow: inset 0 2px 6px rgba(0,0,0,0.03);
    }

    .period-btn {
        padding: 0.6rem 1.6rem;
        border-radius: 10px;
        font-weight: 800;
        font-size: 0.88rem;
        color: var(--text-muted);
        text-decoration: none;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
    }

    .period-btn.active {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: white !important;
        box-shadow: 0 4px 14px rgba(99,102,241,0.3);
        border: none;
    }

    .period-btn:not(.active):hover {
        color: var(--text-main);
        background: rgba(99, 102, 241, 0.07);
    }

    .stat-card {
        padding: 2rem;
        border-radius: 24px;
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .stat-icon {
        width: 62px;
        height: 62px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon i {
        width: 28px;
        height: 28px;
    }

    .stat-label {
        font-size: 0.73rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.06em;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 2.1rem;
        font-weight: 950;
        line-height: 1;
    }

    .stat-unit {
        font-size: 1rem;
        color: var(--text-muted);
        font-weight: 700;
    }

    .stat-subtitle {
        font-size: 0.73rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 6px;
    }



    .activity-row-modern {
        display: flex;
        justify-content: space-between;
        padding: 1.25rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .activity-row-modern:last-child {
        border-bottom: none;
    }

    .formal-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
    }

    .formal-table th {
        padding: 12px 14px;
        background: rgba(99, 102, 241, 0.06);
        color: var(--text-main);
        font-weight: 800;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border-color);
        text-align: left;
        white-space: nowrap;
    }

    .formal-table td {
        padding: 11px 14px;
        border-bottom: 1px dashed var(--border-color);
        color: var(--text-main);
        font-size: 0.88rem;
        transition: background 0.2s ease;
        display: table-cell !important;
    }

    .formal-table tbody tr:last-child td {
        border-bottom: none;
    }

    .formal-table tbody tr:hover td {
        background: rgba(99, 102, 241, 0.04);
    }

    /* PREMIUM MOBILE OPTIMIZATIONS */
    @media (max-width: 1024px) {
        .page-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 2rem !important;
            padding: 2rem !important;
        }
        .page-header h2 { font-size: 2rem !important; }
        .period-toggle-group { width: 100% !important; justify-content: space-between; }
        .period-btn { flex: 1; text-align: center; padding: 0.65rem 0.5rem !important; font-size: 0.8rem !important; }
    }

    @media (max-width: 768px) {
        .report-container { padding: 0.5rem !important; }
        .page-header { padding: 1.5rem !important; margin-bottom: 1.5rem !important; }

        .print-actions-bar {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 1rem !important;
        }
        .print-actions-bar button { width: 100% !important; justify-content: center; }

        .stat-card { padding: 1.5rem !important; flex-direction: column; text-align: center; }
        .stat-icon { margin: 0 auto 1rem; }
        .stat-value { font-size: 1.8rem !important; }

        /* Table stays tabular — horizontal scroll on small screens */
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .formal-table { min-width: 600px; }
        .unified-table { min-width: 900px; }

    /* ═══════════════════════════════════════════
       PRINT STYLES — Clean Formal Document
    ═══════════════════════════════════════════ */
    @media print {
        @page { size: A4 landscape; margin: 15mm; }

        /* ── Reset body ── */
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

        body {
            background: white !important;
            color: #0f172a !important;
            font-family: 'Arial', sans-serif !important;
            font-size: 9.5pt !important;
            padding: 0 !important;
            box-sizing: border-box !important;
        }

        /* ── Hide all UI chrome ── */
        .sidebar, .top-nav, .view-header, .period-toggle-group,
        button, .zoom-controls, .report-filter-card,
        .hide-in-print, .print-actions-bar, .unified-ledger-meta { display: none !important; }

        /* ── Layout reset ── */
        .main-wrapper, .report-container {
            margin: 0 !important; padding: 0 !important;
            box-shadow: none !important; background: transparent !important;
            width: 100% !important; max-width: none !important;
        }

        /* ── Print header (document title block) ── */
        .print-header { display: block !important; margin-bottom: 12px; }
        .page-header   { display: none !important; }

        .print-date-label {
            font-size: 13pt !important;
            color: #1e3a8a !important;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* ── Stats and Charts Stacked on Page 1 ── */
        .stats-charts-print-layout {
            display: flex !important;
            flex-direction: column !important;
            gap: 20px !important;
            align-items: stretch !important;
            margin-bottom: 20px !important;
            page-break-inside: avoid !important;
            page-break-after: always !important;
            break-after: page !important;
        }
        .stats-container {
            width: 100% !important;
            display: flex !important;
            flex-direction: row !important;
            border: 1.5pt solid #1e3a8a !important;
            background: #f8fafc !important;
            border-radius: 0 !important;
            gap: 0 !important;
            margin-bottom: 0 !important;
        }
        .stat-card {
            flex: 1 !important;
            padding: 12px 10px !important;
            border: none !important;
            border-right: 1pt solid #cbd5e1 !important;
            box-shadow: none !important;
            background: transparent !important;
            border-radius: 0 !important;
            text-align: center !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
        }
        .stat-card:last-child { border-right: none !important; }
        .stat-card .stat-icon, .stat-subtitle { display: none !important; }
        .stat-label { color: #475569 !important; font-size: 8pt !important; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .stat-value { color: #1e3a8a !important; font-size: 16pt !important; font-weight: 900; }

        /* ── Print charts ── */
        .print-chart-card {
            width: 100% !important;
            border: 1.5pt solid #1e3a8a !important;
            padding: 15px 20px !important;
            border-radius: 0 !important;
            margin-bottom: 0 !important;
            background: #f8fafc !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
        }
        .issued-chart-card {
            margin-top: 30px !important;
            margin-bottom: 25px !important;
            page-break-inside: avoid !important;
        }

        /* ── Unified ledger card ── */
        .unified-ledger-card {
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            margin-bottom: 0 !important;
            background: transparent !important;
        }

        .unified-ledger-header {
            background: #1e3a8a !important;
            color: white !important;
            padding: 8px 10px !important;
            border: none !important;
            border-radius: 0 !important;
            page-break-after: avoid;
        }
        .unified-ledger-icon { display: none !important; }
        .unified-ledger-title {
            color: white !important;
            font-size: 11pt !important;
            font-weight: 900 !important;
        }
        .unified-ledger-subtitle { color: #bfdbfe !important; font-size: 8pt !important; }

        /* ── The table itself ── */
        .table-responsive { overflow: visible !important; }

        .unified-table, .formal-table {
            width: 100% !important;
            min-width: 0 !important;
            border-collapse: collapse !important;
            border: 1.5pt solid #1e3a8a !important;
            font-size: 8.5pt !important;
            table-layout: auto;
            page-break-inside: auto;
        }

        .unified-table thead, .formal-table thead {
            display: table-header-group !important; /* repeat on every printed page */
        }

        .unified-table thead tr th, .formal-table thead tr th {
            background: #e8eef8 !important;
            color: #1e3a8a !important;
            font-weight: 900 !important;
            font-size: 7.5pt !important;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 7px 6px !important;
            border: 1pt solid #94a3b8 !important;
            text-align: left !important;
            white-space: nowrap;
        }

        .unified-table tbody tr, .formal-table tbody tr { page-break-inside: avoid; }

        .ledger-row td, .formal-table td {
            padding: 6px 6px !important;
            border: 0.5pt solid #cbd5e1 !important;
            color: #0f172a !important;
            font-size: 8.5pt !important;
            background: white !important;
            display: table-cell !important;
            vertical-align: middle !important;
        }

        .ledger-row-received td { background: #f0fdf4 !important; }
        .ledger-row-issued   td { background: #fffbeb !important; }

        /* Remove coloured left borders that look odd on print */
        .ledger-row-received, .ledger-row-issued { border-left: none !important; }

        /* Type badges — simplified for print */
        .type-badge {
            font-size: 8.5pt !important;
            padding: 2px 6px !important;
            border-radius: 4px !important;
            border: 0.5pt solid currentColor !important;
            background: transparent !important;
        }
        .received-badge { color: #166534 !important; }
        .issued-badge   { color: #92400e !important; }

        .cat-badge {
            font-size: 7pt !important;
            padding: 1px 5px !important;
            border-radius: 3px !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
            border: 0.5pt solid #bfdbfe !important;
        }

        /* Totals footer */
        .ledger-totals-row td {
            background: #e8eef8 !important;
            border: 1pt solid #94a3b8 !important;
            font-weight: 900 !important;
            color: #1e3a8a !important;
            font-size: 9pt !important;
            padding: 8px 6px !important;
        }

        /* Quantity colours survive print */
        .qty-received { color: #166534 !important; }
        .qty-issued   { color: #92400e !important; }

        /* Muted helper cells */
        .ledger-date, .mono-cell, .bal-cell { color: #334155 !important; }

        /* Remove glass card styling */
        .glass-card, .print-table-wrapper {
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            background: transparent !important;
            padding: 0 !important;
            margin-bottom: 20px !important;
        }
        .custom-scroll { max-height: none !important; overflow: visible !important; }

        /* ── Signature footer ── */
        .print-footer { display: block !important; page-break-inside: avoid; margin-top: 40px !important; }
    }
</style>

<script>
    function toggleCustomDateBar() {
        const bar = document.getElementById('custom-date-bar');
        const periodInput = document.getElementById('form-period-input');
        const customBtn = document.getElementById('custom-period-btn');

        const isOpen = bar.classList.contains('is-open');
        if (isOpen) {
            bar.classList.remove('is-open');
            periodInput.value = 'monthly';
            customBtn.classList.remove('active');
        } else {
            bar.classList.add('is-open');
            periodInput.value = 'custom';
            customBtn.classList.add('active');

            // Remove active from other period buttons
            document.querySelectorAll('.period-toggle-group .period-btn').forEach(btn => {
                if (btn !== customBtn) btn.classList.remove('active');
            });

            // Focus start date for UX after animation
            setTimeout(() => document.getElementById('start-date-input').focus(), 350);
        }
    }

    function triggerPrintMode() {
        // Build a URL to the dedicated print view, preserving current query params
        const params = new URLSearchParams(window.location.search);
        const printUrl = '{{ route("reports.print") }}?' + params.toString();
        window.open(printUrl, '_blank');
    }

    /* ─────────────────────────────────────────────────────────────
       REAL-TIME CUSTOM DATE RANGE — auto-submit on both dates set
    ───────────────────────────────────────────────────────────── */
    (function () {
        const startInput  = document.getElementById('start-date-input');
        const endInput    = document.getElementById('end-date-input');
        const form        = document.getElementById('report-filter-form');
        const periodInput = document.getElementById('form-period-input');

        if (!startInput || !endInput || !form) return;

        let debounceTimer = null;

        // ── Tiny "Updating…" pill appended inside the date panel
        const pill = document.createElement('div');
        pill.id = 'date-update-pill';
        pill.innerHTML = `
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                 style="animation: dateSpinAnim 0.7s linear infinite;">
                <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
            Updating report…`;
        Object.assign(pill.style, {
            display: 'none', alignItems: 'center', gap: '7px',
            fontSize: '0.78rem', fontWeight: '800', color: '#6366f1',
            background: 'rgba(99,102,241,0.08)', border: '1px solid rgba(99,102,241,0.2)',
            borderRadius: '99px', padding: '5px 13px', marginTop: '10px',
            width: 'fit-content', marginLeft: 'auto', marginRight: 'auto'
        });

        // Insert pill below the date inputs row
        const dateInputsRow = document.querySelector('.date-inputs-row');
        if (dateInputsRow) dateInputsRow.insertAdjacentElement('afterend', pill);

        function isValidRange() {
            const s = startInput.value, e = endInput.value;
            return s && e && new Date(s) <= new Date(e);
        }

        function setInputState(valid) {
            const s = startInput, e = endInput;
            if (valid) {
                s.style.borderColor = '#10b981'; s.style.boxShadow = '0 0 0 3px rgba(16,185,129,0.12)';
                e.style.borderColor = '#10b981'; e.style.boxShadow = '0 0 0 3px rgba(16,185,129,0.12)';
            } else if (s.value && e.value) {
                e.style.borderColor = '#ef4444'; e.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.12)';
            } else {
                [s, e].forEach(el => { el.style.borderColor = ''; el.style.boxShadow = ''; });
            }
        }

        function trySubmit() {
            if (!isValidRange()) return;
            periodInput.value = 'custom';
            pill.style.display = 'inline-flex';

            const params = new URLSearchParams({
                period:     'custom',
                start_date: startInput.value,
                end_date:   endInput.value,
            });

            // Collect selected items from Select2
            const itemsSelect = document.getElementById('items-select');
            if (itemsSelect) {
                Array.from(itemsSelect.selectedOptions).forEach(opt => {
                    params.append('items[]', opt.value);
                });
            }

            // Show loading shimmer over content area
            const contentArea = document.getElementById('report-content-area');
            if (contentArea) {
                contentArea.style.opacity   = '0.45';
                contentArea.style.pointerEvents = 'none';
                contentArea.style.transition = 'opacity 0.25s ease';
            }

            fetch('{{ route("reports.index") }}?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => {
                if (!r.ok) throw new Error('Response error');
                return r.json();
            })
            .then(data => applyUpdate(data))
            .catch(() => {
                // On error, fall back to full page submit
                form.submit();
            })
            .finally(() => {
                pill.style.display = 'none';
                if (contentArea) {
                    contentArea.style.opacity      = '1';
                    contentArea.style.pointerEvents = '';
                }
            });
        }

        /* ── Apply the JSON data to the DOM ── */
        function applyUpdate(d) {
            const fmt = n => Number(n).toLocaleString();

            // 1. Date label
            const lbl = document.querySelector('.print-date-label');
            if (lbl) lbl.textContent = d.dateLabel;

            // 2. Stat cards
            const statVals = document.querySelectorAll('.stat-value');
            if (statVals[0]) statVals[0].innerHTML = fmt(d.totalReceivedQty) + ' <span class="stat-unit">Item(s)</span>';
            if (statVals[1]) statVals[1].innerHTML = fmt(d.totalIssuedQty)   + ' <span class="stat-unit">Item(s)</span>';
            if (statVals[2]) statVals[2].innerHTML = fmt(Math.max(0, d.totalReceivedQty - d.totalIssuedQty)) + ' <span class="stat-unit">Item(s)</span>';

            const statSubs = document.querySelectorAll('.stat-subtitle');
            if (statSubs[0]) statSubs[0].textContent = d.totalReceivedBatches + ' Received Batches';
            if (statSubs[1]) statSubs[1].textContent = d.totalIssuedBatches   + ' Issued Records';

            // 3. Ledger meta badges
            const metas = document.querySelectorAll('.unified-ledger-meta-item');
            if (metas[0]) metas[0].innerHTML = iconSvg('arrow-down-circle') + ' ' + d.allTransactions.filter(t => t.type === 'Received').length + ' Received';
            if (metas[1]) metas[1].innerHTML = iconSvg('arrow-up-circle')   + ' ' + d.allTransactions.filter(t => t.type === 'Issued').length   + ' Issued';
            if (metas[2]) metas[2].innerHTML = d.allTransactions.length + ' Total Records';

            // 4. Charts — destroy + recreate
            rebuildCharts(d);

            // 5. Table body
            rebuildTable(d.allTransactions, d.totalReceivedQty, d.totalIssuedQty);
        }

        function iconSvg(name) {
            const paths = {
                'arrow-down-circle': '<circle cx="12" cy="12" r="10"/><path d="M8 12l4 4 4-4"/><path d="M12 8v8"/>',
                'arrow-up-circle':   '<circle cx="12" cy="12" r="10"/><path d="M16 12l-4-4-4 4"/><path d="M12 16V8"/>',
            };
            return `<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                     ${paths[name] || ''}</svg>`;
        }

        /* Destroy and recreate ApexCharts with new data */
        window._reportCharts = window._reportCharts || [];
        function rebuildCharts(d) {
            (window._reportCharts || []).forEach(c => { try { c.destroy(); } catch(e) {} });
            window._reportCharts = [];

            const theme   = localStorage.getItem('theme') || 'light';
            const isDark  = theme === 'dark';
            const txtColor = isDark ? '#f8fafc' : '#0f172a';

            const barDefaults = {
                chart: { type: 'bar', background: 'transparent', foreColor: txtColor, toolbar: { show: false } },
                plotOptions: { bar: { horizontal: true, borderRadius: 8, borderRadiusApplication: 'end', barHeight: '60%', distributed: true } },
                dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 800, fontFamily: 'Inter, sans-serif', colors: ['#fff'] }, formatter: v => v.toLocaleString() },
                grid: { borderColor: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)', xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
                xaxis: { labels: { style: { fontSize: '11px', fontWeight: 700, fontFamily: 'Inter, sans-serif' }, formatter: v => v.toLocaleString() }, axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: { labels: { style: { fontSize: '11px', fontWeight: 700, fontFamily: 'Inter, sans-serif', colors: txtColor }, maxWidth: 180 } },
                legend: { show: false },
                tooltip: { theme: isDark ? 'dark' : 'light', y: { formatter: v => v.toLocaleString() + ' Item(s)' } }
            };

            const recCard = document.querySelector('.received-chart-card');
            const issCard = document.querySelector('.issued-chart-card');

            const singleWrap = document.getElementById('single-item-chart-wrap');
            const recWrap    = document.getElementById('received-chart-wrap');

            const hasReceived = d.receivedDistribution && d.receivedDistribution.length > 0;
            const hasIssued   = d.issuedDistribution && d.issuedDistribution.length > 0;

            if (d.selectedItemsCount === 1) {
                if (recCard) recCard.style.display = '';
                if (issCard) issCard.style.display = 'none';

                if (singleWrap) singleWrap.style.display = '';
                if (recWrap)    recWrap.style.display = 'none';

                const singleEl = document.getElementById('single-item-bar-chart');
                if (singleEl && typeof ApexCharts !== 'undefined') {
                    singleEl.innerHTML = '';
                    const c = new ApexCharts(singleEl, Object.assign({}, barDefaults, {
                        chart: Object.assign({}, barDefaults.chart, { height: 160 }),
                        series: [{ name: 'Quantity', data: [parseFloat(d.totalReceivedQty), parseFloat(d.totalIssuedQty)] }],
                        xaxis: Object.assign({}, barDefaults.xaxis, { categories: ['Received', 'Issued'] }),
                        colors: ['#10b981', '#f59e0b'],
                        plotOptions: Object.assign({}, barDefaults.plotOptions, {
                            bar: Object.assign({}, barDefaults.plotOptions.bar, { barHeight: '55%' })
                        })
                    }));
                    c.render();
                    window._reportCharts.push(c);
                }
            } else {
                if (singleWrap) singleWrap.style.display = 'none';
                if (recWrap)    recWrap.style.display = '';

                if (hasReceived) {
                    if (recCard) recCard.style.display = '';
                    const recEl = document.getElementById('received-bar-chart');
                    if (recEl && typeof ApexCharts !== 'undefined') {
                        recEl.innerHTML = '';
                        const recLabels = d.receivedDistribution.map(r => r.description);
                        const recData   = d.receivedDistribution.map(r => parseFloat(r.total_qty));
                        const recColors = ['#6366f1','#818cf8','#4f46e5','#7c3aed','#8b5cf6','#a78bfa','#4338ca','#3730a3','#06b6d4','#0ea5e9'];
                        const c = new ApexCharts(recEl, Object.assign({}, barDefaults, {
                            chart: Object.assign({}, barDefaults.chart, { height: Math.max(220, recLabels.length * 42) }),
                            series: [{ name: 'Received', data: recData }],
                            xaxis: Object.assign({}, barDefaults.xaxis, { categories: recLabels }),
                            colors: recColors.slice(0, recLabels.length),
                        }));
                        c.render();
                        window._reportCharts.push(c);
                    }
                } else {
                    if (recCard) recCard.style.display = 'none';
                }

                if (hasIssued) {
                    if (issCard) issCard.style.display = '';
                    const issEl = document.getElementById('issued-bar-chart');
                    if (issEl && typeof ApexCharts !== 'undefined') {
                        issEl.innerHTML = '';
                        const issLabels = d.issuedDistribution.map(r => r.description);
                        const issData   = d.issuedDistribution.map(r => parseFloat(r.total_qty));
                        const issColors = ['#f59e0b','#fbbf24','#d97706','#b45309','#ef4444','#f87171','#dc2626','#10b981','#06b6d4','#ec4899'];
                        const c = new ApexCharts(issEl, Object.assign({}, barDefaults, {
                            chart: Object.assign({}, barDefaults.chart, { height: Math.max(220, issLabels.length * 42) }),
                            series: [{ name: 'Issued', data: issData }],
                            xaxis: Object.assign({}, barDefaults.xaxis, { categories: issLabels }),
                            colors: issColors.slice(0, issLabels.length),
                        }));
                        c.render();
                        window._reportCharts.push(c);
                    }
                } else {
                    if (issCard) issCard.style.display = 'none';
                }
            }
        }

        /* Rebuild the movement log table rows from JSON */
        function rebuildTable(rows, totalRec, totalIss) {
            const tableContainer = document.getElementById('table-container');
            const placeholder    = document.getElementById('no-transactions-placeholder');
            const tbody          = document.querySelector('.rpt-unified-table tbody');
            const tfoot          = document.querySelector('.rpt-unified-table tfoot .ledger-totals-row');

            if (!tbody) return;

            if (!rows || rows.length === 0) {
                if (tableContainer) tableContainer.style.display = 'none';
                if (placeholder) placeholder.style.display = 'block';
            } else {
                if (tableContainer) tableContainer.style.display = '';
                if (placeholder) placeholder.style.display = 'none';

                tbody.innerHTML = rows.map((row, idx) => {
                    const date = row.date_sort ? fmtDate(row.date_sort) : '—';
                    const isRec = row.type === 'Received';
                    
                    let typeHtml = '';
                    if (isRec) {
                        typeHtml = `<span class="type-badge received-badge">${iconSvg('arrow-down-circle')} Received</span>`;
                    } else {
                        const statusBadge = row.status === 'Temporary'
                            ? `<span style="font-size:0.65rem;font-weight:800;color:#b45309;background:rgba(245,158,11,0.12);padding:1px 6px;border-radius:4px;border:1px solid rgba(245,158,11,0.2);text-transform:uppercase;">Temporary</span>`
                            : `<span style="font-size:0.65rem;font-weight:800;color:#1e3a8a;background:rgba(99,102,241,0.08);padding:1px 6px;border-radius:4px;border:1px solid rgba(99,102,241,0.15);text-transform:uppercase;">Permanent</span>`;
                        typeHtml = `<div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                            <span class="type-badge issued-badge">${iconSvg('arrow-up-circle')} Issued</span>
                            ${statusBadge}
                        </div>`;
                    }

                    let serialHtml = '';
                    if (row.serial_number) {
                        const sns = row.serial_number.split(',').map(s => s.trim()).filter(Boolean);
                        if (sns.length > 0) {
                            serialHtml = `
                                <div class="serial-numbers-wrapper" style="margin-top: 4px; display: inline-flex; flex-wrap: wrap; align-items: center; gap: 4px;">
                                    <div style="display: inline-flex; align-items: center; flex-wrap: wrap; gap: 4px; background: rgba(99, 102, 241, 0.08); color: var(--primary); font-size: 0.72rem; padding: 2px 8px; border-radius: 6px; font-weight: 800; word-break: break-word; white-space: normal; max-width: 250px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; margin-right:2px;"><path d="M3 5v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2z"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg>
                                        S/N: ${sns.slice(0, 3).join(', ')}${sns.length > 3 ? `<span class="dots">...</span><span class="more-sns" style="display: none;">, ${sns.slice(3).join(', ')}</span>` : ''}
                                    </div>
                                    ${sns.length > 3 ? `
                                        <button type="button" class="toggle-sns-btn" onclick="let container = this.previousElementSibling; let more = container.querySelector('.more-sns'); let dots = container.querySelector('.dots'); let isHidden = more.style.display === 'none'; more.style.display = isHidden ? 'inline' : 'none'; dots.style.display = isHidden ? 'none' : 'inline'; this.querySelector('.chevron-icon').style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';" style="background: transparent; border: none; padding: 2px; cursor: pointer; display: inline-flex; align-items: center; color: var(--primary); outline: none; transition: all 0.2s; border-radius: 4px;" onmouseover="this.style.background='rgba(99, 102, 241, 0.15)';" onmouseout="this.style.background='transparent';" title="Show more serial numbers">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="chevron-icon" style="transition: transform 0.2s;"><polyline points="6 9 12 15 18 9"/></svg>
                                        </button>
                                    ` : ''}
                                </div>
                            `;
                        }
                    }

                    const itemHtml = `<div>
                        <span class="item-desc" style="font-weight:800;display:block;margin-bottom:2px;color:var(--text-main);">${esc(row.description)}</span>
                        ${serialHtml}
                        <span class="cat-badge" style="font-size:0.7rem;padding:2px 6px;border-radius:6px;">${esc(row.category)}</span>
                    </div>`;

                    let entityHtml = '';
                    if (isRec) {
                        entityHtml = `<span style="font-weight:600;color:var(--text-main);">${esc(row.ref)}</span>`;
                    } else {
                        entityHtml = `<span style="font-weight:800;color:var(--text-main);text-transform:uppercase;font-size:0.76rem;letter-spacing:0.02em;">${esc(row.department)}</span>`;
                        if (row.sources) {
                            entityHtml += `<span style="display: block; font-size: 0.7rem; color: var(--text-muted); font-weight: 600; margin-top: 2px; text-transform: none;">(Sourced from: ${esc(row.sources)})</span>`;
                        }
                    }

                    const qtyClass = isRec ? 'qty-received' : 'qty-issued';
                    const qtyVal = Number(String(row.quantity || 0).replace(/,/g, ''));
                    const qty = isNaN(qtyVal) ? row.quantity : Math.round(qtyVal).toLocaleString();

                    const prevVal = Number(String(row.previous_stock || 0).replace(/,/g, ''));
                    const prevStock = (row.previous_stock === null || row.previous_stock === undefined || row.previous_stock === '—' || isNaN(prevVal)) ? '—' : Math.round(prevVal).toLocaleString();

                    const balVal = Number(String(row.stock_bal || 0).replace(/,/g, ''));
                    const bal = (row.stock_bal === null || row.stock_bal === undefined || row.stock_bal === '—' || isNaN(balVal)) ? '—' : Math.round(balVal).toLocaleString();

                    let varText = '—';
                    if (isRec) {
                        const varVal = Number(String(row.variance || 0).replace(/,/g, ''));
                        if (row.variance !== null && row.variance !== undefined && row.variance !== '—' && !isNaN(varVal)) {
                            const roundedVar = Math.round(varVal);
                            varText = (roundedVar > 0 ? '+' : '') + roundedVar.toLocaleString();
                        }
                    }

                    return `<tr class="ledger-row ledger-row-${isRec ? 'received' : 'issued'}">
                        <td class="ledger-date">${date}</td>
                        <td style="text-align:center;">${typeHtml}</td>
                        <td>${itemHtml}</td>
                        <td>${entityHtml}</td>
                        <td class="qty-cell ${qtyClass}" style="text-align:right;">${qty}</td>
                        <td style="text-align:right;font-weight:700;color:var(--text-main);">${prevStock}</td>
                        <td class="bal-cell" style="text-align:right;">${esc(bal)}</td>
                        <td class="variance-cell" style="text-align:right;">${esc(varText)}</td>
                    </tr>`;
                }).join('');
            }

            // Update totals footer
            if (tfoot) {
                tfoot.cells[1].innerHTML = `↓ ${Math.round(Number(totalRec)).toLocaleString()} received &nbsp;|&nbsp; ↑ ${Math.round(Number(totalIss)).toLocaleString()} issued`;
            }

            // Re-run lucide icon replacement on new SVGs
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function fmtDate(str) {
            if (!str) return '—';
            try {
                const d = new Date(str);
                return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            } catch(e) { return str; }
        }

        function esc(s) {
            return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }

        function onDateChange() {
            clearTimeout(debounceTimer);
            const valid = isValidRange();
            setInputState(valid);

            const resetBtn = document.getElementById('reset-date-btn');
            if (resetBtn) {
                if (startInput.value || endInput.value) {
                    resetBtn.style.display = 'inline-flex';
                } else {
                    resetBtn.style.display = 'none';
                }
            }

            if (valid) {
                debounceTimer = setTimeout(trySubmit, 600);
            }
        }

        window.resetCustomDates = function() {
            if (startInput) startInput.value = '';
            if (endInput) endInput.value = '';

            setInputState(false);

            const resetBtn = document.getElementById('reset-date-btn');
            if (resetBtn) resetBtn.style.display = 'none';

            periodInput.value = 'monthly';

            document.querySelectorAll('.period-toggle-group .period-btn').forEach(btn => {
                if (btn.textContent.trim() === 'Monthly') {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            const bar = document.getElementById('custom-date-bar');
            if (bar) bar.classList.remove('is-open');

            const params = new URLSearchParams({
                period: 'monthly'
            });

            const itemsSelect = document.getElementById('items-select');
            if (itemsSelect) {
                Array.from(itemsSelect.selectedOptions).forEach(opt => {
                    params.append('items[]', opt.value);
                });
            }

            const contentArea = document.getElementById('report-content-area');
            if (contentArea) {
                contentArea.style.opacity   = '0.45';
                contentArea.style.pointerEvents = 'none';
                contentArea.style.transition = 'opacity 0.25s ease';
            }

            fetch('{{ route("reports.index") }}?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => {
                if (!r.ok) throw new Error('Response error');
                return r.json();
            })
            .then(data => applyUpdate(data))
            .catch(() => {
                form.submit();
            })
            .finally(() => {
                if (contentArea) {
                    contentArea.style.opacity      = '1';
                    contentArea.style.pointerEvents = '';
                }
            });
        };

        startInput.addEventListener('change', onDateChange);
        endInput.addEventListener('change',   onDateChange);
        startInput.addEventListener('input',  onDateChange);
        endInput.addEventListener('input',    onDateChange);

        // Real-Time Select2 Interceptor
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#items-select').on('change', function() {
                if (periodInput.value === 'custom' && isValidRange()) {
                    onDateChange();
                }
            });
        }
    })();


    // Real-Time Analytics Lockdown Engine
    function syncPermissionState() {
        fetch('{{ route("api.user.permissions") }}', {
            headers: { 'Accept': 'application/json' }
        })
            .then(response => response.json())
            .then(data => {
                const canReport = data.can_generate_reports;

                if (!canReport) {
                    /* console print removed */
                    // Disable all interaction points and add tooltip
                    document.querySelectorAll('.period-btn, .btn-primary, .btn-secondary').forEach(btn => {
                        btn.classList.add('restricted-btn');
                        btn.setAttribute('title', 'Access Restricted: Administrative clearance required.');
                        if (btn.tagName === 'BUTTON') btn.disabled = true;
                        if (btn.tagName === 'A') btn.onclick = (e) => e.preventDefault();
                    });
                } else {
                    document.querySelectorAll('.restricted-btn').forEach(btn => {
                        btn.classList.remove('restricted-btn');
                        btn.removeAttribute('title');
                        if (btn.tagName === 'BUTTON') btn.disabled = false;
                        if (btn.tagName === 'A') btn.onclick = null;
                    });
                }
            })
            .catch(err => { /* console print removed */ });

    }

    // Run sync pulse every 5 seconds for real-time reactivity
    setInterval(syncPermissionState, 5000);

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        syncPermissionState(); // Initial check
    });
</script>

<style>
    .restricted-btn {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
        filter: saturate(0) !important;
    }

    /* Spinner used by the real-time date update pill */
    @keyframes dateSpinAnim {
        to { transform: rotate(360deg); }
    }

    /* ══════════════════════════════════════════════
       ITEM FILTER PANEL
    ══════════════════════════════════════════════ */
    .item-filter-panel {
        border-radius: 20px;
        border: 1px solid rgba(99,102,241,0.18);
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(99,102,241,0.07);
    }
    .item-filter-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, rgba(16,185,129,0.08) 0%, rgba(6,182,212,0.06) 100%);
        border-bottom: 1px solid rgba(99,102,241,0.12);
        gap: 1rem;
        flex-wrap: wrap;
    }
    .item-filter-header-left {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }
    .item-filter-icon-wrap {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: linear-gradient(135deg, #10b981, #06b6d4);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        flex-shrink: 0;
    }
    .item-filter-title {
        font-size: 0.95rem;
        font-weight: 900;
        color: var(--text-main);
        letter-spacing: -0.01em;
    }
    .item-filter-subtitle {
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-top: 1px;
    }
    .item-filter-count-badge {
        font-size: 0.7rem;
        font-weight: 900;
        letter-spacing: 0.06em;
        color: #10b981;
        background: rgba(16,185,129,0.1);
        border: 1px solid rgba(16,185,129,0.25);
        padding: 0.3rem 0.85rem;
        border-radius: 99px;
        white-space: nowrap;
    }
    .item-filter-all-badge {
        font-size: 0.7rem;
        font-weight: 900;
        letter-spacing: 0.06em;
        color: var(--text-muted);
        background: var(--bg-main);
        border: 1px solid var(--border-color);
        padding: 0.3rem 0.85rem;
        border-radius: 99px;
        white-space: nowrap;
    }
    .item-filter-body {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        padding: 1.25rem 1.5rem;
        background: var(--bg-card);
        flex-wrap: wrap;
    }
    .item-selector-wrap {
        flex: 1;
        min-width: 260px;
    }

    /* ════════════════════════════════════════════════════════
       PREMIUM SELECT2 — with Category Group Styling
    ════════════════════════════════════════════════════════ */

    /* ── Input shell (the multi-select box) ── */
    .select2-container--default .select2-selection--multiple {
        border: 2px solid var(--border-color) !important;
        background: var(--bg-main) !important;
        border-radius: 14px !important;
        padding: 6px 10px !important;
        min-height: 52px !important;
        transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease !important;
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 5px !important;
        cursor: text !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple,
    .select2-container--default.select2-container--open .select2-selection--multiple {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 4px rgba(16,185,129,0.12) !important;
        background: var(--bg-card) !important;
    }

    /* ── Selected tag chips ── */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        background: linear-gradient(135deg, rgba(16,185,129,0.14), rgba(6,182,212,0.09)) !important;
        border: 1.5px solid rgba(16,185,129,0.32) !important;
        color: #059669 !important;
        border-radius: 10px !important;
        padding: 4px 10px 4px 8px !important;
        font-weight: 800 !important;
        font-size: 0.77rem !important;
        letter-spacing: 0.01em !important;
        margin: 0 !important;
        transition: all 0.2s ease !important;
        line-height: 1.5 !important;
        box-shadow: 0 2px 6px rgba(16,185,129,0.1) !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice:hover {
        background: rgba(16,185,129,0.2) !important;
        border-color: rgba(16,185,129,0.5) !important;
        box-shadow: 0 3px 10px rgba(16,185,129,0.18) !important;
    }

    /* ── Remove × on chips ── */
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: rgba(5,150,105,0.5) !important;
        font-weight: 900 !important;
        font-size: 0.9rem !important;
        border: none !important;
        background: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
        line-height: 1 !important;
        order: -1 !important;
        transition: color 0.15s ease !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ef4444 !important;
        background: transparent !important;
    }

    /* ── Placeholder text ── */
    .select2-container--default .select2-selection--multiple .select2-selection__placeholder {
        color: var(--text-muted) !important;
        font-weight: 600 !important;
        font-size: 0.9rem !important;
    }

    /* ── Inline search field ── */
    .select2-container--default .select2-search--inline .select2-search__field {
        color: var(--text-main) !important;
        font-family: 'Inter', sans-serif !important;
        font-weight: 600 !important;
        font-size: 0.88rem !important;
        background: transparent !important;
        margin: 0 !important;
        padding: 0 4px !important;
    }
    .select2-container--default .select2-search--inline .select2-search__field::placeholder {
        color: var(--text-muted) !important;
        opacity: 0.7;
    }

    /* ══════════════════════════════════════════════
       DROPDOWN PANEL
    ══════════════════════════════════════════════ */
    .select2-dropdown {
        border: 1.5px solid rgba(16,185,129,0.2) !important;
        border-radius: 18px !important;
        box-shadow: 0 20px 60px rgba(0,0,0,0.14),
                    0 4px 16px rgba(0,0,0,0.06),
                    0 0 0 1px rgba(16,185,129,0.05) !important;
        overflow: hidden !important;
        background: var(--bg-card) !important;
        margin-top: 8px !important;
    }

    /* ── Search bar at top of dropdown ── */
    .select2-container--default .select2-search--dropdown {
        padding: 12px 12px 10px !important;
        border-bottom: 1px solid var(--border-color) !important;
        background: var(--bg-main) !important;
        position: relative !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 2px solid var(--border-color) !important;
        border-radius: 11px !important;
        padding: 9px 12px 9px 38px !important;
        background: var(--bg-card) !important;
        color: var(--text-main) !important;
        font-family: 'Inter', sans-serif !important;
        font-weight: 600 !important;
        font-size: 0.88rem !important;
        outline: none !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
        width: 100% !important;
        box-sizing: border-box !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.3-4.3'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: 12px center !important;
        background-size: 14px !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16,185,129,0.12) !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2310b981' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.3-4.3'/%3E%3C/svg%3E") !important;
    }

    /* ══════════════════════════════════════════════
       CATEGORY GROUP HEADERS (optgroup labels)
    ══════════════════════════════════════════════ */
    .select2-results__options {
        padding: 6px 6px 8px !important;
        max-height: 280px !important;
        overflow-y: auto !important;
    }

    /* The category group label row */
    .select2-results__group {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        padding: 10px 10px 6px 12px !important;
        font-size: 0.67rem !important;
        font-weight: 900 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.1em !important;
        color: #6366f1 !important;
        background: transparent !important;
        border-radius: 0 !important;
        cursor: default !important;
        margin-top: 4px !important;
        position: relative !important;
    }
    /* Left accent bar on category labels */
    .select2-results__group::before {
        content: '' !important;
        display: inline-block !important;
        width: 3px !important;
        height: 14px !important;
        background: linear-gradient(180deg, #6366f1, #8b5cf6) !important;
        border-radius: 2px !important;
        flex-shrink: 0 !important;
    }
    /* Horizontal rule after the label text */
    .select2-results__group::after {
        content: '' !important;
        flex: 1 !important;
        height: 1px !important;
        background: linear-gradient(90deg, rgba(99,102,241,0.2), transparent) !important;
        margin-left: 4px !important;
    }

    /* ══════════════════════════════════════════════
       ITEM ROWS inside groups
    ══════════════════════════════════════════════ */
    /* Items nested under a group */
    .select2-results__option[role="option"] {
        border-radius: 9px !important;
        padding: 9px 12px 9px 22px !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        color: var(--text-main) !important;
        transition: background 0.15s ease, color 0.15s ease, padding-left 0.15s ease !important;
        margin-bottom: 1px !important;
        position: relative !important;
    }

    /* Hover state */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(6,182,212,0.06)) !important;
        color: #059669 !important;
        padding-left: 26px !important;
    }

    /* Selected state */
    .select2-container--default .select2-results__option[aria-selected="true"] {
        background: linear-gradient(135deg, rgba(16,185,129,0.13), rgba(6,182,212,0.08)) !important;
        color: #059669 !important;
        font-weight: 800 !important;
    }
    /* Checkmark for selected items */
    .select2-container--default .select2-results__option[aria-selected="true"]::after {
        content: '✓' !important;
        position: absolute !important;
        right: 12px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        font-size: 0.78rem !important;
        color: #10b981 !important;
        font-weight: 900 !important;
    }

    /* Hover+selected */
    .select2-container--default .select2-results__option--highlighted[aria-selected="true"] {
        background: linear-gradient(135deg, rgba(16,185,129,0.18), rgba(6,182,212,0.1)) !important;
    }

    /* ── "No results" state ── */
    .select2-container--default .select2-results__option--disabled,
    .select2-results__message {
        color: var(--text-muted) !important;
        font-style: italic !important;
        text-align: center !important;
        padding: 1.5rem !important;
        font-size: 0.85rem !important;
    }

    /* ── Custom scrollbar inside dropdown ── */
    .select2-results__options::-webkit-scrollbar { width: 5px; }
    .select2-results__options::-webkit-scrollbar-track { background: transparent; }
    .select2-results__options::-webkit-scrollbar-thumb {
        background: rgba(99,102,241,0.2); border-radius: 99px;
    }
    .select2-results__options::-webkit-scrollbar-thumb:hover {
        background: rgba(99,102,241,0.4);
    }
</style>
@endsection

@push('scripts')
    @if(auth()->user()->is_admin || auth()->user()->role === 'Main Admin' || auth()->user()->role === 'Auditor' || auth()->user()->can_generate_reports)
        <script src="{{ asset('js/apexcharts.js') }}"></script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Select2 Initialization — with category + item search
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#items-select').select2({
                    placeholder: "Search by category or item name...",
                    allowClear: true,
                    closeOnSelect: false,

                    // Custom matcher: search both item text AND optgroup label
                    matcher: function(params, data) {
                        // No search term → show everything
                        if (!params.term || $.trim(params.term) === '') {
                            return data;
                        }

                        const term = params.term.toLowerCase();
                        const text = (data.text || '').toLowerCase();

                        // ── Match on the optgroup label itself
                        //    → return the group with ALL its children visible
                        if (data.children && data.children.length > 0) {
                            if (text.indexOf(term) > -1) {
                                // Group name matches → show entire group
                                return data;
                            }
                            // Filter children individually
                            const matchedChildren = data.children.filter(function(child) {
                                return (child.text || '').toLowerCase().indexOf(term) > -1;
                            });
                            if (matchedChildren.length > 0) {
                                // Return a copy with only matched children
                                return $.extend({}, data, { children: matchedChildren });
                            }
                            return null;
                        }

                        // ── Match on individual item text
                        if (text.indexOf(term) > -1) {
                            return data;
                        }

                        return null;
                    }
                });

                const selectIssuedBtn = document.getElementById('select-issued-items-btn');
                if (selectIssuedBtn) {
                    const issuedItems = @json($issuedItemDescriptions ?? []);
                    selectIssuedBtn.addEventListener('click', () => {
                        $('#items-select').val(issuedItems).trigger('change');
                    });
                }
            }

            // ApexCharts Rendering
            const theme = localStorage.getItem('theme') || 'light';
            const isDark = theme === 'dark';
            const textColor = isDark ? '#f8fafc' : '#0f172a';
            const chartColors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#a855f7', '#ec4899', '#3b82f6', '#14b8a6', '#f43f5e'];

            @if($totalReceivedQty > 0 || $totalIssuedQty > 0)

                // Shared bar chart defaults
                const barDefaults = {
                    chart: {
                        type: 'bar',
                        background: 'transparent',
                        foreColor: textColor,
                        toolbar: { show: false },
                        animations: { enabled: true, speed: 600, animateGradually: { enabled: true, delay: 60 } }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 8,
                            borderRadiusApplication: 'end',
                            barHeight: '60%',
                            distributed: true,
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        style: { fontSize: '11px', fontWeight: 800, fontFamily: 'Inter, sans-serif', colors: ['#fff'] },
                        formatter: (val) => val.toLocaleString(),
                        dropShadow: { enabled: false }
                    },
                    grid: {
                        borderColor: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)',
                        xaxis: { lines: { show: true } },
                        yaxis: { lines: { show: false } }
                    },
                    xaxis: {
                        labels: {
                            style: { fontSize: '11px', fontWeight: 700, fontFamily: 'Inter, sans-serif' },
                            formatter: (val) => val.toLocaleString()
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        labels: {
                            style: { fontSize: '11px', fontWeight: 700, fontFamily: 'Inter, sans-serif', colors: textColor },
                            maxWidth: 180
                        }
                    },
                    legend: { show: false },
                    tooltip: {
                        theme: isDark ? 'dark' : 'light',
                        y: { formatter: (val) => val.toLocaleString() + ' Item(s)' }
                    }
                };

                if (typeof ApexCharts !== 'undefined') {
                    window._reportCharts = window._reportCharts || [];

                    @if(count($selectedItems) === 1)
                        const singleBarOptions = Object.assign({}, barDefaults, {
                            chart: Object.assign({}, barDefaults.chart, { height: 160 }),
                            series: [{ name: 'Quantity', data: [{{ (float)$totalReceivedQty }}, {{ (float)$totalIssuedQty }}] }],
                            xaxis: Object.assign({}, barDefaults.xaxis, { categories: ['Received', 'Issued'] }),
                            colors: ['#10b981', '#f59e0b'],
                            plotOptions: Object.assign({}, barDefaults.plotOptions, {
                                bar: Object.assign({}, barDefaults.plotOptions.bar, { barHeight: '55%' })
                            })
                        });
                        const singleChart = new ApexCharts(document.querySelector('#single-item-bar-chart'), singleBarOptions);
                        singleChart.render();
                        window._reportCharts.push(singleChart);
                    @else
                        @if($totalReceivedQty > 0 && $receivedDistribution->count() > 0)
                            const recLabels = @json($receivedDistribution->pluck('description'));
                            const recData   = @json($receivedDistribution->pluck('total_qty')->map(fn($q) => (float)$q));
                            const recBarH   = Math.max(220, recLabels.length * 42);
                            const recColors = ['#6366f1','#818cf8','#4f46e5','#7c3aed','#8b5cf6','#a78bfa','#4338ca','#3730a3','#06b6d4','#0ea5e9'];
                            const recOptions = Object.assign({}, barDefaults, {
                                chart: Object.assign({}, barDefaults.chart, { height: recBarH }),
                                series: [{ name: 'Received', data: recData }],
                                xaxis: Object.assign({}, barDefaults.xaxis, { categories: recLabels }),
                                colors: recColors.slice(0, recLabels.length),
                            });
                            const recChart = new ApexCharts(document.querySelector('#received-bar-chart'), recOptions);
                            recChart.render();
                            window._reportCharts.push(recChart);
                        @endif

                        @if($totalIssuedQty > 0 && $issuedDistribution->count() > 0)
                            const issLabels = @json($issuedDistribution->pluck('description'));
                            const issData   = @json($issuedDistribution->pluck('total_qty')->map(fn($q) => (float)$q));
                            const issBarH   = Math.max(220, issLabels.length * 42);
                            const issColors = ['#f59e0b','#fbbf24','#d97706','#b45309','#ef4444','#f87171','#dc2626','#10b981','#06b6d4','#ec4899'];
                            const issOptions = Object.assign({}, barDefaults, {
                                chart: Object.assign({}, barDefaults.chart, { height: issBarH }),
                                series: [{ name: 'Issued', data: issData }],
                                xaxis: Object.assign({}, barDefaults.xaxis, { categories: issLabels }),
                                colors: issColors.slice(0, issLabels.length),
                            });
                            const issChart = new ApexCharts(document.querySelector('#issued-bar-chart'), issOptions);
                            issChart.render();
                            window._reportCharts.push(issChart);
                        @endif
                    @endif

                    // Handle dark-to-light theme changes during printing for chart visibility
                    window.addEventListener('beforeprint', () => {
                        (window._reportCharts || []).forEach(chart => {
                            try {
                                chart.updateOptions({
                                    chart: { foreColor: '#0f172a' },
                                    grid: { borderColor: 'rgba(0,0,0,0.06)' },
                                    yaxis: { labels: { style: { colors: '#0f172a' } } }
                                }, false, false);
                            } catch(e) {}
                        });
                    });

                    window.addEventListener('afterprint', () => {
                        (window._reportCharts || []).forEach(chart => {
                            try {
                                chart.updateOptions({
                                    chart: { foreColor: textColor },
                                    grid: { borderColor: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)' },
                                    yaxis: { labels: { style: { colors: textColor } } }
                                }, false, false);
                            } catch(e) {}
                        });
                    });
                }
            @endif
        });
    </script>
@endpush
