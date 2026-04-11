<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceivedItemsController;

$dashboardLogic = function () {
    $existingItems = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
        ->select('inventory_items.description', 'inventory_batches.ledge_category')
        ->distinct()
        ->get();

    // Total Inventory: Sum of stock_balance
    $totalInventory = \App\Models\InventoryItem::get()->sum(function ($item) {
        return is_numeric($item->stock_balance) ? (float)$item->stock_balance : 0;
    });

    // Trend calculation (Month-over-month additions)
    $currentMonthStart = now()->startOfMonth();
    $lastMonthStart = now()->subMonth()->startOfMonth();
    $lastMonthEnd = now()->subMonth()->endOfMonth();

    $currentMonthInvValue = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
        ->where('inventory_batches.entry_date', '>=', $currentMonthStart)
        ->get()->sum(function ($i) {
            return is_numeric($i->stock_balance) ? (float)$i->stock_balance : 0;
        });

    $lastMonthInvValue = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
        ->whereBetween('inventory_batches.entry_date', [$lastMonthStart, $lastMonthEnd])
        ->get()->sum(function ($i) {
            return is_numeric($i->stock_balance) ? (float)$i->stock_balance : 0;
        });

    $trendValue = 0;
    if ($lastMonthInvValue > 0) {
        $trendValue = (($currentMonthInvValue - $lastMonthInvValue) / $lastMonthInvValue) * 100;
    } elseif ($currentMonthInvValue > 0) {
        $trendValue = 100;
    }

    // Daily "Issuance" (Mocked as items added today)
    $dailyIssuance = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
        ->whereDate('inventory_batches.entry_date', now())
        ->count();

    // Stock Value (Mocked calculation: Total Inventory * GHS 50 average)
    $stockValue = $totalInventory * 50;

    // Expired Items (Stock = 0 AND Ledge >= 1)
    $expiredCount = \App\Models\InventoryItem::get()->filter(function ($item) {
        return is_numeric($item->stock_balance) && (float)$item->stock_balance == 0 &&
            is_numeric($item->ledge_balance) && (float)$item->ledge_balance >= 1;
    })->count();

    // Chart Data (Last 12 Months)
    $chartMonths = [];
    $receivedSeries = [];
    $varianceSeries = [];

    $allActivity = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
        ->where('inventory_batches.entry_date', '>=', now()->subMonths(11)->startOfMonth())
        ->select('inventory_items.variance', 'inventory_items.stock_balance', 'inventory_batches.entry_date')
        ->get();

    for ($i = 11; $i >= 0; $i--) {
        $m = now()->subMonths($i);
        $chartMonths[] = $m->format('M');

        $monthItems = $allActivity->filter(function ($item) use ($m) {
            $d = \Carbon\Carbon::parse($item->entry_date);
            return $d->month == $m->month && $d->year == $m->year;
        });

        $receivedSeries[] = (float)$monthItems->sum(fn($item) => is_numeric($item->stock_balance) ? (float)$item->stock_balance : 0);
        $varianceSeries[] = (float)$monthItems->sum(fn($item) => is_numeric($item->variance) ? (float)$item->variance : 0);
    }

    // Total Variance
    $totalVariance = \App\Models\InventoryItem::get()->sum(function ($item) {
        return is_numeric($item->variance) ? (float)$item->variance : 0;
    });

    // Distribution Data (Donut Chart)
    $ledgeMap = [
        'A' => 'Stationary',
        'B' => 'Cleaning',
        'C' => 'IT & Acc.',
        'D' => 'Transport',
        'E' => 'Safety',
        'G' => 'Pharmacy',
        'J' => 'Equipment'
    ];

    $distData = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
        ->get()
        ->groupBy('ledge_category')
        ->map(function ($items) {
            return $items->sum(fn($item) => is_numeric($item->stock_balance) ? (float)$item->stock_balance : 0);
        });

    $distLabels = $distData->keys()->map(fn($key) => $ledgeMap[$key] ?? "Ledge $key")->toArray();
    $distSeries = $distData->values()->toArray();

    $topCategory = 'None';
    if ($distData->count() > 0) {
        $topKey = $distData->sortDesc()->keys()->first();
        $topCategory = $ledgeMap[$topKey] ?? "Ledge $topKey";
    }

    $avgStock = $distData->count() > 0 ? ($totalInventory / $distData->count()) : 0;

    // Weekly Chart Data (Last 12 Weeks)
    $weekLabels = [];
    $weekReceived = [];
    $weekVariance = [];
    $weeklyActivity = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
        ->where('inventory_batches.entry_date', '>=', now()->subWeeks(11)->startOfWeek())
        ->select('inventory_items.variance', 'inventory_items.stock_balance', 'inventory_batches.entry_date')
        ->get();

    for ($i = 11; $i >= 0; $i--) {
        $w = now()->subWeeks($i);
        $weekLabels[] = $w->startOfWeek()->format('M d');
        $itemsInWeek = $weeklyActivity->filter(function ($item) use ($w) {
            $d = \Carbon\Carbon::parse($item->entry_date);
            return $d->between($w->copy()->startOfWeek(), $w->copy()->endOfWeek());
        });
        $weekReceived[] = (float)$itemsInWeek->sum(fn($item) => is_numeric($item->stock_balance) ? (float)$item->stock_balance : 0);
        $weekVariance[] = (float)$itemsInWeek->sum(fn($item) => is_numeric($item->variance) ? (float)$item->variance : 0);
    }

    // Daily Chart Data (Last 14 Days)
    $dayLabels = [];
    $dayReceived = [];
    $dayVariance = [];
    $dailyActivity = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
        ->where('inventory_batches.entry_date', '>=', now()->subDays(13)->startOfDay())
        ->select('inventory_items.variance', 'inventory_items.stock_balance', 'inventory_batches.entry_date')
        ->get();

    for ($i = 13; $i >= 0; $i--) {
        $d = now()->subDays($i);
        $dayLabels[] = $d->format('M d');
        $itemsInDay = $dailyActivity->filter(function ($item) use ($d) {
            $entry = \Carbon\Carbon::parse($item->entry_date);
            return $entry->isSameDay($d);
        });
        $dayReceived[] = (float)$itemsInDay->sum(fn($item) => is_numeric($item->stock_balance) ? (float)$item->stock_balance : 0);
        $dayVariance[] = (float)$itemsInDay->sum(fn($item) => is_numeric($item->variance) ? (float)$item->variance : 0);
    }

    // Recent Transactions
    $recentTransactions = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
        ->select('inventory_items.*', 'inventory_batches.entry_date', 'inventory_batches.ledge_category', 'inventory_batches.supplier_name')
        ->orderBy('inventory_batches.entry_date', 'desc')
        ->limit(4)
        ->get();

    // Fetch unique suppliers for the dropdown
    $allSuppliers = \App\Models\InventoryBatch::select('supplier_name')
        ->distinct()
        ->pluck('supplier_name')
        ->map(function($name) {
            return preg_replace('/\s\[.*\]$/', '', $name);
        })->unique()->values();

    // Handle Empty Donut State
    $isEmptyDist = empty($distSeries);
    if ($isEmptyDist) {
        $distSeries = [100];
        $distLabels = ['No Data Available'];
    }

    return view('dashboard', compact(
        'isEmptyDist',
        'allSuppliers',
        'existingItems',
        'totalInventory',
        'trendValue',
        'totalVariance',
        'dailyIssuance',
        'stockValue',
        'expiredCount',
        'chartMonths',
        'receivedSeries',
        'varianceSeries',
        'distLabels',
        'distSeries',
        'topCategory',
        'avgStock',
        'weekLabels',
        'weekReceived',
        'weekVariance',
        'dayLabels',
        'dayReceived',
        'dayVariance',
        'recentTransactions',
        'ledgeMap'
    ));
};

Route::get('/', $dashboardLogic)->name('dashboard');
Route::get('/dashboard', $dashboardLogic);

Route::post('/inventory/store', [App\Http\Controllers\InventoryController::class, 'store'])->name('inventory.store');
Route::get('/received-items', [ReceivedItemsController::class, 'index'])->name('receiveditems');
Route::get('/received-items/{id}', [ReceivedItemsController::class, 'show'])->name('receiveditems.show');
Route::get('/received-items/{id}/print', [ReceivedItemsController::class, 'print'])->name('receiveditems.print');
Route::get('/api/global-search', [App\Http\Controllers\InventoryController::class, 'globalSearch'])->name('api.search');
