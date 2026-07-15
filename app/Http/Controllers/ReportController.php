<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\InventoryBatch;
use App\Models\IssuedItem;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $this->data($request);
        }

        $period = $request->query('period', 'monthly');

        $startDate = Carbon::now();
        $endDate   = Carbon::now();
        $dateLabel = "General Report";

        // Raw strings for repopulating the date-range inputs
        $rawStartDate = $request->query('start_date', '');
        $rawEndDate   = $request->query('end_date', '');

        if ($period === 'custom' && $rawStartDate && $rawEndDate) {
            $startDate = Carbon::parse($rawStartDate)->startOfDay();
            $endDate   = Carbon::parse($rawEndDate)->endOfDay();
            $dateLabel = "Custom Range Report: " . $startDate->format('d M Y') . ' – ' . $endDate->format('d M Y');
        } elseif ($period === 'daily') {
            $startDate = Carbon::now()->startOfDay();
            $endDate   = Carbon::now()->endOfDay();
            $dateLabel = "Daily Activity Report - " . $startDate->format('F j, Y');
        } elseif ($period === 'monthly') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate   = Carbon::now()->endOfMonth();
            $dateLabel = "Monthly Overview Report - " . $startDate->format('F Y');
        } elseif ($period === 'yearly') {
            $startDate = Carbon::now()->startOfYear();
            $endDate   = Carbon::now()->endOfYear();
            $dateLabel = "Annual Summary Report - " . $startDate->format('Y');
        }

        $ledgeMap = \App\Models\Setting::getCategories();

        // Fetch all unique item descriptions in the system with their category to populate the item filter dropdown
        $itemsWithCategories = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->whereNotNull('inventory_items.description')
            ->where('inventory_items.description', '!=', '')
            ->select('inventory_items.description', 'inventory_batches.ledge_category')
            ->distinct()
            ->orderBy('inventory_batches.ledge_category')
            ->orderBy('inventory_items.description')
            ->get();

        $groupedItems = [];
        foreach ($itemsWithCategories as $item) {
            $catCode = $item->ledge_category;
            $catName = $ledgeMap[$catCode] ?? ('Category ' . $catCode);
            $groupedItems[$catName][] = $item->description;
        }

        $selectedItems = $request->query('items', []);
        if (is_string($selectedItems)) {
            $selectedItems = array_filter(array_map('trim', explode(',', $selectedItems)));
        }

        $issuedItemDescriptions = IssuedItem::whereNotNull('description')
            ->where('description', '!=', '')
            ->distinct()
            ->pluck('description')
            ->toArray();

        // Base Queries for Received Metrics
        $receivedQuery = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->whereBetween('inventory_batches.entry_date', [$startDate, $endDate]);

        // Base Queries for Issued Metrics
        $issuedQuery = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->whereBetween('issuances.issuance_date', [$startDate, $endDate]);

        // Apply selected items filter
        if (!empty($selectedItems)) {
            $receivedQuery->whereIn('inventory_items.description', $selectedItems);
            $issuedQuery->whereIn('issued_items.description', $selectedItems);
        }

        $totalReceivedBatches = (clone $receivedQuery)->distinct('inventory_batches.id')->count('inventory_batches.id');
        $totalReceivedQty = $receivedQuery->sum('inventory_items.qty');

        $totalIssuedBatches = (clone $issuedQuery)->distinct('issuances.id')->count('issuances.id');
        $totalIssuedQty = (float) $issuedQuery->sum('issued_items.quantity') + (float) \App\Models\ReturnedItem::whereIn('issued_item_id', (clone $issuedQuery)->pluck('issued_items.id'))->sum('returned_qty');

        // Distributions for donut charts
        $receivedDistribution = (clone $receivedQuery)
            ->select('inventory_items.description', \DB::raw('SUM(inventory_items.qty) as total_qty'))
            ->groupBy('inventory_items.description')
            ->orderBy('total_qty', 'desc')
            ->limit(10)
            ->get();

        $issuedDistribution = (clone $issuedQuery)
            ->select('issued_items.description')
            ->selectRaw('SUM(issued_items.quantity + COALESCE((SELECT SUM(returned_qty) FROM returned_items WHERE returned_items.issued_item_id = issued_items.id), 0)) as total_qty')
            ->groupBy('issued_items.description')
            ->orderBy('total_qty', 'desc')
            ->limit(10)
            ->get();
        
        // Recent Activities
        $recentReceivalsQuery = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->whereBetween('inventory_batches.entry_date', [$startDate, $endDate]);

        $recentIssuesQuery = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->whereBetween('issuances.issuance_date', [$startDate, $endDate]);

        if (!empty($selectedItems)) {
            $recentReceivalsQuery->whereIn('inventory_items.description', $selectedItems);
            $recentIssuesQuery->whereIn('issued_items.description', $selectedItems);
        }

        $recentReceivals = $recentReceivalsQuery
            ->select(
                'inventory_items.*',
                'inventory_batches.entry_date',
                'inventory_batches.supplier_name',
                'inventory_batches.donor_name',
                'inventory_batches.acquisition_type',
                'inventory_batches.ledge_category',
                'inventory_batches.id as batch_id_ref',
                \DB::raw("'Received' as transaction_type")
            )
            ->orderBy('inventory_batches.entry_date', 'desc')
            ->limit(200)
            ->get();

        $hasSivNo = \Illuminate\Support\Facades\Schema::hasColumn('issuances', 'siv_no');
        $hasRequisitionId = \Illuminate\Support\Facades\Schema::hasColumn('issuances', 'requisition_id');

        if ($hasRequisitionId) {
            $recentIssuesQuery->leftJoin('store_requisitions', 'issuances.requisition_id', '=', 'store_requisitions.id');
        }

        $recentIssues = $recentIssuesQuery
            ->select(
                'issued_items.*',
                'issuances.issuance_date as entry_date',
                'issuances.beneficiary',
                'issuances.issuance_type',
                $hasRequisitionId ? 'store_requisitions.department as department' : \DB::raw("NULL as department"),
                $hasSivNo ? 'issuances.siv_no' : \DB::raw("NULL as siv_no"),
                'issued_items.ledge_category',
                \DB::raw("'Issued' as transaction_type")
            )
            ->selectRaw('issued_items.quantity + COALESCE((SELECT SUM(returned_qty) FROM returned_items WHERE returned_items.issued_item_id = issued_items.id), 0) as original_quantity')
            ->orderBy('issuances.issuance_date', 'desc')
            ->limit(200)
            ->get();

        // Match received dates for issued items (Optimized: only fetch matching descriptions)
        $uniqueDescriptions = $recentIssues->pluck('description')->unique()->filter()->toArray();
        $receiptsByDesc = collect();
        if (!empty($uniqueDescriptions)) {
            $receiptsByDesc = \DB::table('inventory_items')
                ->join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                ->whereIn('inventory_items.description', $uniqueDescriptions)
                ->select('inventory_items.description', 'inventory_batches.entry_date')
                ->orderBy('inventory_batches.entry_date', 'desc')
                ->get()
                ->groupBy('description');
        }

        foreach ($recentIssues as $i) {
            $desc = $i->description;
            $issueDate = $i->entry_date;
            
            $receivedDate = null;
            if (isset($receiptsByDesc[$desc])) {
                foreach ($receiptsByDesc[$desc] as $receipt) {
                    if ($receipt->entry_date <= $issueDate) {
                        $receivedDate = $receipt->entry_date;
                        break;
                    }
                }
                if (!$receivedDate) {
                    $receivedDate = $receiptsByDesc[$desc]->last()->entry_date ?? null;
                }
            }
            $i->received_date = $receivedDate;
        }

        return view('reports.index', compact(
            'period',
            'dateLabel',
            'rawStartDate',
            'rawEndDate',
            'totalReceivedBatches',
            'totalReceivedQty',
            'totalIssuedBatches',
            'totalIssuedQty',
            'recentReceivals',
            'recentIssues',
            'startDate',
            'endDate',
            'ledgeMap',
            'groupedItems',
            'selectedItems',
            'receivedDistribution',
            'issuedDistribution',
            'issuedItemDescriptions'
        ));
    }

    /**
     * JSON endpoint for real-time AJAX partial updates (stats + chart + table).
     * Accepts the same query-string params as index().
     */
    public function data(Request $request)
    {
        $period = $request->query('period', 'monthly');

        $startDate = Carbon::now();
        $endDate   = Carbon::now();
        $dateLabel = 'General Report';

        $rawStartDate = $request->query('start_date', '');
        $rawEndDate   = $request->query('end_date', '');

        if ($period === 'custom' && $rawStartDate && $rawEndDate) {
            $startDate = Carbon::parse($rawStartDate)->startOfDay();
            $endDate   = Carbon::parse($rawEndDate)->endOfDay();
            $dateLabel = 'Custom Range Report: ' . $startDate->format('d M Y') . ' – ' . $endDate->format('d M Y');
        } elseif ($period === 'daily') {
            $startDate = Carbon::now()->startOfDay();
            $endDate   = Carbon::now()->endOfDay();
            $dateLabel = 'Daily Activity Report - ' . $startDate->format('F j, Y');
        } elseif ($period === 'monthly') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate   = Carbon::now()->endOfMonth();
            $dateLabel = 'Monthly Overview Report - ' . $startDate->format('F Y');
        } elseif ($period === 'yearly') {
            $startDate = Carbon::now()->startOfYear();
            $endDate   = Carbon::now()->endOfYear();
            $dateLabel = 'Annual Summary Report - ' . $startDate->format('Y');
        }

        $ledgeMap = \App\Models\Setting::getCategories();

        $selectedItems = $request->query('items', []);
        if (is_string($selectedItems)) {
            $selectedItems = array_filter(array_map('trim', explode(',', $selectedItems)));
        }

        // Received
        $receivedQuery = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->whereBetween('inventory_batches.entry_date', [$startDate, $endDate]);

        // Issued
        $issuedQuery = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->whereBetween('issuances.issuance_date', [$startDate, $endDate]);

        if (!empty($selectedItems)) {
            $receivedQuery->whereIn('inventory_items.description', $selectedItems);
            $issuedQuery->whereIn('issued_items.description', $selectedItems);
        }

        $totalReceivedBatches = (clone $receivedQuery)->distinct('inventory_batches.id')->count('inventory_batches.id');
        $totalReceivedQty     = $receivedQuery->sum('inventory_items.qty');
        $totalIssuedBatches   = (clone $issuedQuery)->distinct('issuances.id')->count('issuances.id');
        $totalIssuedQty       = (float) $issuedQuery->sum('issued_items.quantity') + (float) \App\Models\ReturnedItem::whereIn('issued_item_id', (clone $issuedQuery)->pluck('issued_items.id'))->sum('returned_qty');

        $receivedDistribution = (clone $receivedQuery)
            ->select('inventory_items.description', \DB::raw('SUM(inventory_items.qty) as total_qty'))
            ->groupBy('inventory_items.description')
            ->orderBy('total_qty', 'desc')
            ->limit(10)
            ->get();

        $issuedDistribution = (clone $issuedQuery)
            ->select('issued_items.description')
            ->selectRaw('SUM(issued_items.quantity + COALESCE((SELECT SUM(returned_qty) FROM returned_items WHERE returned_items.issued_item_id = issued_items.id), 0)) as total_qty')
            ->groupBy('issued_items.description')
            ->orderBy('total_qty', 'desc')
            ->limit(10)
            ->get();

        // Recent Activities
        $recentReceivalsQuery = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->whereBetween('inventory_batches.entry_date', [$startDate, $endDate]);

        $recentIssuesQuery = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->whereBetween('issuances.issuance_date', [$startDate, $endDate]);

        if (!empty($selectedItems)) {
            $recentReceivalsQuery->whereIn('inventory_items.description', $selectedItems);
            $recentIssuesQuery->whereIn('issued_items.description', $selectedItems);
        }

        $recentReceivals = $recentReceivalsQuery
            ->select(
                'inventory_items.*',
                'inventory_batches.entry_date',
                'inventory_batches.supplier_name',
                'inventory_batches.donor_name',
                'inventory_batches.acquisition_type',
                'inventory_batches.ledge_category',
                \DB::raw("'Received' as transaction_type")
            )
            ->orderBy('inventory_batches.entry_date', 'desc')
            ->limit(200)
            ->get();

        $hasRequisitionId = \Illuminate\Support\Facades\Schema::hasColumn('issuances', 'requisition_id');
        $hasSivNo         = \Illuminate\Support\Facades\Schema::hasColumn('issuances', 'siv_no');

        if ($hasRequisitionId) {
            $recentIssuesQuery->leftJoin('store_requisitions', 'issuances.requisition_id', '=', 'store_requisitions.id');
        }

        $recentIssues = $recentIssuesQuery
            ->select(
                'issued_items.*',
                'issuances.issuance_date as entry_date',
                'issuances.beneficiary',
                'issuances.issuance_type',
                $hasRequisitionId ? 'store_requisitions.department as department' : \DB::raw('NULL as department'),
                $hasSivNo ? 'issuances.siv_no' : \DB::raw('NULL as siv_no'),
                'issued_items.ledge_category',
                \DB::raw("'Issued' as transaction_type")
            )
            ->selectRaw('issued_items.quantity + COALESCE((SELECT SUM(returned_qty) FROM returned_items WHERE returned_items.issued_item_id = issued_items.id), 0) as original_quantity')
            ->orderBy('issuances.issuance_date', 'desc')
            ->limit(200)
            ->get();

        // Match received dates for issued items (Optimized: only fetch matching descriptions)
        $uniqueDescriptions = $recentIssues->pluck('description')->unique()->filter()->toArray();
        $receiptsByDesc = collect();
        if (!empty($uniqueDescriptions)) {
            $receiptsByDesc = \DB::table('inventory_items')
                ->join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                ->whereIn('inventory_items.description', $uniqueDescriptions)
                ->select('inventory_items.description', 'inventory_batches.entry_date')
                ->orderBy('inventory_batches.entry_date', 'desc')
                ->get()
                ->groupBy('description');
        }

        foreach ($recentIssues as $i) {
            $receivedDate = null;
            if (isset($receiptsByDesc[$i->description])) {
                foreach ($receiptsByDesc[$i->description] as $receipt) {
                    if ($receipt->entry_date <= $i->entry_date) {
                        $receivedDate = $receipt->entry_date;
                        break;
                    }
                }
                if (!$receivedDate) {
                    $receivedDate = $receiptsByDesc[$i->description]->last()->entry_date ?? null;
                }
            }
            $i->received_date = $receivedDate;
        }

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

        // Build allTransactions (same mapping as the Blade @php block)
        $rawTransactions = $recentReceivals->map(function ($r) use ($ledgeMap) {
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
                'quantity'      => $r->qty ?? 0,
                'stock_bal'     => $r->stock_balance ?? 0,
                'previous_stock'=> '—',
                'variance'      => $r->variance ?? '—',
                'status'        => '—',
                'department'    => '—',
                'sources'       => null,
            ];
        })->merge($recentIssues->map(function ($i) use ($ledgeMap, $itemSources) {
            return [
                'date_received' => $i->received_date,
                'date_issued'   => $i->entry_date,
                'date_sort'     => $i->entry_date,
                'type'          => 'Issued',
                'category'      => $ledgeMap[$i->ledge_category] ?? ('Category ' . $i->ledge_category),
                'description'   => $i->description,
                'serial_number' => null,
                'ref'           => $i->beneficiary ?? '—',
                'quantity'      => $i->original_quantity ?? $i->quantity ?? 0,
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

        return response()->json([
            'dateLabel'           => $dateLabel,
            'totalReceivedQty'    => (float) $totalReceivedQty,
            'totalReceivedBatches'=> (int) $totalReceivedBatches,
            'totalIssuedQty'      => (float) $totalIssuedQty,
            'totalIssuedBatches'  => (int) $totalIssuedBatches,
            'receivedDistribution'=> $receivedDistribution,
            'issuedDistribution'  => $issuedDistribution,
            'allTransactions'     => $allTransactions->values(),
            'selectedItemsCount'  => count($selectedItems),
        ]);
    }
    public function printReport(Request $request)
    {
        $period = $request->query('period', 'monthly');

        $startDate = Carbon::now();
        $endDate   = Carbon::now();
        $dateLabel = 'General Report';

        $rawStartDate = $request->query('start_date', '');
        $rawEndDate   = $request->query('end_date', '');

        if ($period === 'custom' && $rawStartDate && $rawEndDate) {
            $startDate = Carbon::parse($rawStartDate)->startOfDay();
            $endDate   = Carbon::parse($rawEndDate)->endOfDay();
            $dateLabel = 'Custom Range: ' . $startDate->format('d M Y') . ' – ' . $endDate->format('d M Y');
        } elseif ($period === 'daily') {
            $startDate = Carbon::now()->startOfDay();
            $endDate   = Carbon::now()->endOfDay();
            $dateLabel = 'Daily Activity Report — ' . $startDate->format('F j, Y');
        } elseif ($period === 'monthly') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate   = Carbon::now()->endOfMonth();
            $dateLabel = 'Monthly Overview Report — ' . $startDate->format('F Y');
        } elseif ($period === 'yearly') {
            $startDate = Carbon::now()->startOfYear();
            $endDate   = Carbon::now()->endOfYear();
            $dateLabel = 'Annual Summary Report — ' . $startDate->format('Y');
        }

        $ledgeMap = \App\Models\Setting::getCategories();

        $selectedItems = $request->query('items', []);
        if (is_string($selectedItems)) {
            $selectedItems = array_filter(array_map('trim', explode(',', $selectedItems)));
        }

        $receivedQuery = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->whereBetween('inventory_batches.entry_date', [$startDate, $endDate]);

        $issuedQuery = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->whereBetween('issuances.issuance_date', [$startDate, $endDate]);

        if (!empty($selectedItems)) {
            $receivedQuery->whereIn('inventory_items.description', $selectedItems);
            $issuedQuery->whereIn('issued_items.description', $selectedItems);
        }

        $totalReceivedQty   = $receivedQuery->sum('inventory_items.qty');
        $totalIssuedQty     = (float) $issuedQuery->sum('issued_items.quantity') + (float) \App\Models\ReturnedItem::whereIn('issued_item_id', (clone $issuedQuery)->pluck('issued_items.id'))->sum('returned_qty');
        $totalReceivedBatches = (clone $receivedQuery)->distinct('inventory_batches.id')->count('inventory_batches.id');
        $totalIssuedBatches   = (clone $issuedQuery)->distinct('issuances.id')->count('issuances.id');

        $receivedDistribution = (clone $receivedQuery)
            ->select('inventory_items.description', \DB::raw('SUM(inventory_items.qty) as total_qty'))
            ->groupBy('inventory_items.description')
            ->orderBy('total_qty', 'desc')
            ->get();

        $issuedDistribution = (clone $issuedQuery)
            ->select('issued_items.description')
            ->selectRaw('SUM(issued_items.quantity + COALESCE((SELECT SUM(returned_qty) FROM returned_items WHERE returned_items.issued_item_id = issued_items.id), 0)) as total_qty')
            ->groupBy('issued_items.description')
            ->orderBy('total_qty', 'desc')
            ->get();

        $hasRequisitionId = \Illuminate\Support\Facades\Schema::hasColumn('issuances', 'requisition_id');
        $hasSivNo         = \Illuminate\Support\Facades\Schema::hasColumn('issuances', 'siv_no');

        $recentReceivalsQuery = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->whereBetween('inventory_batches.entry_date', [$startDate, $endDate]);

        $recentIssuesQuery = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->whereBetween('issuances.issuance_date', [$startDate, $endDate]);

        if (!empty($selectedItems)) {
            $recentReceivalsQuery->whereIn('inventory_items.description', $selectedItems);
            $recentIssuesQuery->whereIn('issued_items.description', $selectedItems);
        }

        if ($hasRequisitionId) {
            $recentIssuesQuery->leftJoin('store_requisitions', 'issuances.requisition_id', '=', 'store_requisitions.id');
        }

        $recentReceivals = $recentReceivalsQuery
            ->select('inventory_items.*', 'inventory_batches.entry_date', 'inventory_batches.supplier_name', 'inventory_batches.donor_name', 'inventory_batches.acquisition_type', 'inventory_batches.ledge_category')
            ->orderBy('inventory_batches.entry_date', 'desc')
            ->limit(500)
            ->get();

        $recentIssues = $recentIssuesQuery
            ->select(
                'issued_items.*',
                'issuances.issuance_date as entry_date',
                'issuances.beneficiary',
                'issuances.issuance_type',
                $hasRequisitionId ? 'store_requisitions.department as department' : \DB::raw('NULL as department'),
                $hasSivNo ? 'issuances.siv_no' : \DB::raw('NULL as siv_no'),
                'issued_items.ledge_category'
            )
            ->selectRaw('issued_items.quantity + COALESCE((SELECT SUM(returned_qty) FROM returned_items WHERE returned_items.issued_item_id = issued_items.id), 0) as original_quantity')
            ->orderBy('issuances.issuance_date', 'desc')
            ->limit(500)
            ->get();

        $user = auth()->user();

        return view('reports.print', compact(
            'dateLabel',
            'period',
            'startDate',
            'endDate',
            'totalReceivedQty',
            'totalIssuedQty',
            'totalReceivedBatches',
            'totalIssuedBatches',
            'recentReceivals',
            'recentIssues',
            'receivedDistribution',
            'issuedDistribution',
            'ledgeMap',
            'selectedItems',
            'user'
        ));
    }
}
