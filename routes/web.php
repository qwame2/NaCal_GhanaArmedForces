<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceivedItemsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IssueItemsController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArchiveController;

// Archive Routes
Route::middleware(['auth', 'check_status'])->prefix('admin/archive')->group(function () {
    Route::get('/', [ArchiveController::class, 'index'])->name('admin.archive');
    Route::post('/message/{id}', [ArchiveController::class, 'archiveMessage'])->name('admin.archive.message');
    Route::post('/log/{id}', [ArchiveController::class, 'archiveLog'])->name('admin.archive.log');
    Route::post('/restore/message/{id}', [ArchiveController::class, 'restoreMessage'])->name('admin.archive.restore.message');
    Route::post('/restore/log/{id}', [ArchiveController::class, 'restoreLog'])->name('admin.archive.restore.log');
    Route::post('/bulk/logs', [ArchiveController::class, 'bulkArchiveLogs'])->name('admin.archive.bulk.logs');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showAuth'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/api/user/offline', [AuthController::class, 'markOffline'])->name('api.user.offline');

// Guest Redirection
Route::get('/', function() {
    return redirect()->route('login');
});
Route::get('/register', function() { return redirect()->route('login'); });
Route::get('/account-deactivated', function() { return view('auth.deactivated'); })->name('account.deactivated');

// Protected Routes (Grouped under auth and active status check)
Route::middleware(['auth', 'check_status'])->group(function () {
    
    Route::get('/dashboard', function () {
        // STRICT ROLE ENFORCEMENT: Admins are not allowed in the Personnel Dashboard
        if (auth()->user()->is_admin) {
            return redirect()->route('admin.index')->with('warning', 'Strategic Oversight required. Redirecting to Command Center.');
        }

        $existingItems = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->selectRaw('inventory_items.description, MAX(inventory_items.unit) as unit, MAX(inventory_batches.ledge_category) as ledge_category, SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as stock_balance, SUM(CAST(REPLACE(inventory_items.qty, ",", "") AS DECIMAL(15,2))) as qty, SUM(CAST(REPLACE(inventory_items.variance, ",", "") AS DECIMAL(15,2))) as variance')
            ->groupBy('inventory_items.description')
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
                is_numeric($item->qty) && (float)$item->qty >= 1;
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

        // Ledge mapping for display and calculations
        $ledgeMap = \Illuminate\Support\Facades\Schema::hasTable('settings') 
            ? \App\Models\Setting::getCategories() 
            : [
                'A' => 'Stationary',
                'B' => 'Cleaning',
                'C' => 'IT & Acc.',
                'D' => 'Transport',
                'E' => 'Safety',
                'G' => 'Pharmacy',
                'J' => 'Equipment'
            ];

        // Dynamic Low Stock Threshold from Settings
        $threshold = \Illuminate\Support\Facades\Schema::hasTable('settings') 
            ? (int)\App\Models\Setting::get('low_stock_threshold', 100) 
            : 100;

        // Low Stock Alerts (Stock < Threshold) - Grouped by Description to handle duplicates
        $lowStockCount = \App\Models\InventoryItem::selectRaw('description, SUM(stock_balance) as total_stock')
            ->groupBy('description')
            ->havingRaw('SUM(stock_balance) < ?', [$threshold])
            ->get()
            ->count();

        // 50% Threshold Monitoring for Ledge Categories
        $thresholdLedges = array_keys($ledgeMap);
        $lowStockLedges = [];

        $categoryStats = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->whereIn('inventory_batches.ledge_category', $thresholdLedges)
            ->get()
            ->groupBy('ledge_category');

        foreach ($categoryStats as $code => $items) {
            $avail = $items->sum(fn($i) => is_numeric($i->qty) ? (float)$i->qty : 0);
            $target = $items->sum(fn($i) => is_numeric($i->qty) ? (float)$i->qty : 0);
            
            if ($target > 0) {
                $percentage = round(($avail / $target) * 100);
                $threshold = \Illuminate\Support\Facades\Schema::hasTable('settings') ? (int)\App\Models\Setting::get('low_stock_threshold', 100) : 100;
                $isOverride = $avail < $threshold;
                if ($percentage <= 50 || $isOverride) {
                    $lowStockLedges[] = [
                        'code' => $code,
                        'name' => $ledgeMap[$code] ?? "Category $code",
                        'percentage' => $percentage,
                        'avail' => $avail,
                        'is_override' => $isOverride
                    ];
                }
            }
        }

        // Individual items below threshold for the alerts container (Grouped by Description)
        $lowStockItems = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->selectRaw('inventory_items.description, inventory_batches.ledge_category, SUM(inventory_items.stock_balance) as stock_balance, SUM(inventory_items.qty) as qty')
            ->groupBy('inventory_items.description', 'inventory_batches.ledge_category')
            ->havingRaw('SUM(inventory_items.stock_balance) < ?', [$threshold])
            ->orderBy('stock_balance', 'asc')
            ->limit(10)
            ->get();

        // Distribution Data (Donut Chart) - Dynamic Categories
        $distData = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->select('inventory_batches.ledge_category', 'inventory_items.stock_balance')
            ->get()
            ->groupBy('ledge_category')
            ->map(function ($items) {
                return $items->sum(fn($item) => is_numeric($item->stock_balance) ? (float)$item->stock_balance : 0);
            });

        $distLabels = $distData->keys()->map(fn($key) => $ledgeMap[$key] ?? "Category $key")->toArray();
        $distSeries = $distData->values()->toArray();

        $topCategory = 'None';
        if ($distData->count() > 0 && $distData->max() > 0) {
            $topKey = $distData->sortDesc()->keys()->first();
            $topCategory = $ledgeMap[$topKey] ?? "Category $topKey";
        }

        $avgStock = $distData->count() > 0 ? ($totalInventory / $distData->count()) : 0;

        // Handle Empty Donut State (Array empty OR all values are zero)
        $isEmptyDist = empty($distSeries) || array_sum($distSeries) <= 0;
        if ($isEmptyDist) {
            $distSeries = [100];
            $distLabels = ['No Data Available'];
        }

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
            ->select('inventory_items.*', 'inventory_batches.entry_date', 'inventory_batches.arrival_date', 'inventory_batches.ledge_category', 'inventory_batches.supplier_name', 'inventory_batches.supplier_status', 'inventory_batches.donor_name', 'inventory_batches.acquisition_type')
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

        // Ledge mapping for display and calculations (Category standardization)
        $ledgeMap = \Illuminate\Support\Facades\Schema::hasTable('settings') 
            ? \App\Models\Setting::getCategories() 
            : [
                'A' => 'Stationary',
                'B' => 'Cleaning',
                'C' => 'IT & Acc.',
                'D' => 'Transport',
                'E' => 'Safety',
                'G' => 'Pharmacy',
                'J' => 'Equipment'
            ];

        return view('dashboard', compact(
            'isEmptyDist',
            'allSuppliers',
            'existingItems',
            'totalInventory',
            'trendValue',
            'totalVariance',
            'lowStockCount',
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
            'ledgeMap',
            'lowStockLedges',
            'lowStockItems'
        ));
    })->name('dashboard');

    Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/received-items', [ReceivedItemsController::class, 'index'])->name('receiveditems');
    Route::get('/issue-items', [IssueItemsController::class, 'index'])->name('issueitems');
    Route::post('/issue-items/store', [IssueItemsController::class, 'store'])->name('issueitems.store');
    Route::get('/api/issued-items-history', [IssueItemsController::class, 'history'])->name('api.issued-items-history');
    Route::get('/received-items/{id}', [ReceivedItemsController::class, 'show'])->name('receiveditems.show');
    Route::put('/received-items/{id}', [ReceivedItemsController::class, 'update'])->name('receiveditems.update');
    Route::get('/received-items/{id}/print', [ReceivedItemsController::class, 'print'])->name('receiveditems.print');
    Route::get('/api/global-search', [InventoryController::class, 'globalSearch'])->name('api.search');
    Route::delete('/received-items/{id}', [ReceivedItemsController::class, 'destroy'])->name('receiveditems.destroy');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/update', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/avatar', [SettingsController::class, 'updateAvatar'])->name('settings.avatar');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/stock-check', [\App\Http\Controllers\StockCheckController::class, 'index'])->name('stockcheck.index');
    Route::get('/notifications', function() {
        return view('notifications');
    })->name('notifications.index');
    Route::get('/messages', [SettingsController::class, 'messages'])->name('messages.index');
    Route::get('/api/messages/{userId}', [\App\Http\Controllers\MessageController::class, 'fetchMessages'])->name('api.messages.fetch');
    Route::post('/api/messages/send', [\App\Http\Controllers\MessageController::class, 'sendMessage'])->name('api.messages.send');
    Route::post('/api/messages/{userId}/read', [\App\Http\Controllers\MessageController::class, 'markAsRead'])->name('api.messages.read');
    Route::get('/api/unread-counts', [\App\Http\Controllers\MessageController::class, 'getUnreadCounts'])->name('api.unread-counts');
    Route::get('/api/total-unread', [\App\Http\Controllers\MessageController::class, 'getTotalUnreadCount'])->name('api.total-unread');
    Route::get('/api/online-statuses', [\App\Http\Controllers\MessageController::class, 'getOnlineStatuses'])->name('api.online-statuses');
    Route::get('/api/user/permissions', function() {
        return response()->json([
            'can_generate_reports' => (bool)auth()->user()->can_generate_reports,
            'can_add_inventory' => (bool)auth()->user()->can_add_inventory,
            'can_operate_logistics' => (bool)auth()->user()->can_operate_logistics,
        ]);
    })->name('api.user.permissions');

    Route::get('/api/notifications', function() {
        if (!auth()->check()) return response()->json(['error' => 'Unauthenticated'], 401);

        try {
            $acknowledged = \App\Models\NotificationAcknowledgement::where('user_id', auth()->id())
                ->pluck('item_description')
                ->toArray();
        } catch (\Exception $e) {
            $acknowledged = session()->get('acknowledged_notifications', []);
        }

        $thresholdRules = \Illuminate\Support\Facades\Schema::hasTable('settings') 
            ? json_decode(\App\Models\Setting::where('key', 'item_threshold_rules')->value('value') ?? '{}', true) ?? [] 
            : [];
        $unitRules = \Illuminate\Support\Facades\Schema::hasTable('settings') 
            ? json_decode(\App\Models\Setting::where('key', 'item_unit_rules')->value('value') ?? '{}', true) ?? [] 
            : [];
        $globalThreshold = \Illuminate\Support\Facades\Schema::hasTable('settings') ? (int)\App\Models\Setting::get('low_stock_threshold', 100) : 100;
        
        $items = \App\Models\InventoryItem::selectRaw('description, SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
            ->whereNotIn(\DB::raw('TRIM(description)'), array_map('trim', $acknowledged))
            ->groupBy('description')
            ->get();

        $lowStockNotifications = [];
        foreach ($items as $item) {
            $descLower = strtolower(trim($item->description));
            $threshold = $globalThreshold;
            
            foreach ($thresholdRules as $kw => $rule) {
                if (str_contains($descLower, $kw)) {
                    $threshold = (int)$rule['threshold'];
                    break;
                }
            }

            $unit = 'units';
            foreach ($unitRules as $kw => $rule) {
                if (str_contains($descLower, $kw)) {
                    $unit = $rule['unit'];
                    break;
                }
            }

            if ($item->total_stock < $threshold) {
                $lowStockNotifications[] = [
                    'type' => 'warning',
                    'title' => 'Low Stock: ' . $item->description,
                    'message' => "Stock level (" . number_format($item->total_stock, 0) . " {$unit}) is below threshold (" . $threshold . ").",
                    'icon' => 'alert-triangle',
                    'route' => auth()->user()->is_admin ? 'admin.index' : 'dashboard'
                ];
            }
        }

        $expiredItems = \App\Models\InventoryItem::selectRaw('description, SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock, SUM(CAST(REPLACE(qty, ",", "") AS DECIMAL(15,2))) as total_qty')
            ->whereNotIn(\DB::raw('TRIM(description)'), array_map('trim', $acknowledged))
            ->groupBy('description')
            ->havingRaw('SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) = 0 AND SUM(CAST(REPLACE(qty, ",", "") AS DECIMAL(15,2))) >= 1')
            ->get();

        $notifications = $lowStockNotifications;
        $is_admin = auth()->user()->is_admin;
        
        foreach ($expiredItems as $item) {
            $notifications[] = [
                'type' => 'danger',
                'title' => 'Expired Record: ' . $item->description,
                'message' => "Item registry indicates zero balance but exists in inventory records.",
                'icon' => 'alert-octagon',
                'route' => $is_admin ? 'admin.index' : 'dashboard'
            ];
        }

        return response()->json([
            'notifications' => $notifications,
            'count' => count($notifications)
        ]);
    })->name('api.notifications');

    Route::post('/api/notifications/mark-all-read', function() {
        if (!auth()->check()) return response()->json(['success' => false], 401);

        $thresholdRules = \Illuminate\Support\Facades\Schema::hasTable('settings') 
            ? json_decode(\App\Models\Setting::where('key', 'item_threshold_rules')->value('value') ?? '{}', true) ?? [] 
            : [];
        $globalThreshold = \Illuminate\Support\Facades\Schema::hasTable('settings') 
            ? (int)\App\Models\Setting::get('low_stock_threshold', 100) 
            : 100;

        $items = \App\Models\InventoryItem::selectRaw('description, SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
            ->groupBy('description')
            ->get();

        $lowStockItems = [];
        foreach ($items as $item) {
            $descLower = strtolower(trim($item->description));
            $threshold = $globalThreshold;
            
            foreach ($thresholdRules as $kw => $rule) {
                if (str_contains($descLower, $kw)) {
                    $threshold = (int)$rule['threshold'];
                    break;
                }
            }

            if ($item->total_stock < $threshold) {
                $lowStockItems[] = $item->description;
            }
        }

        $expiredItems = \App\Models\InventoryItem::selectRaw('description')
            ->groupBy('description')
            ->havingRaw('SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) = 0 AND SUM(CAST(REPLACE(qty, ",", "") AS DECIMAL(15,2))) >= 1')
            ->pluck('description')
            ->toArray();

        $allDescs = array_unique(array_merge($lowStockItems, $expiredItems));
        
        // Permanent storage (Database)
        try {
            foreach ($allDescs as $desc) {
                \App\Models\NotificationAcknowledgement::updateOrCreate(
                    ['user_id' => auth()->id(), 'item_description' => $desc, 'alert_type' => 'system'],
                    ['acknowledged_at' => now()]
                );
            }
        } catch (\Exception $e) {
            // Fallback to session
            $acknowledged = session()->get('acknowledged_notifications', []);
            session()->put('acknowledged_notifications', array_unique(array_merge($acknowledged, $allDescs)));
        }

        return response()->json(['success' => true]);
    })->name('api.notifications.mark-all-read');

    Route::post('/api/notifications/dismiss', function(\Illuminate\Http\Request $request) {
        if (!auth()->check()) return response()->json(['success' => false], 401);
        $desc = $request->description;
        if (!$desc) return response()->json(['success' => false], 400);

        try {
            \App\Models\NotificationAcknowledgement::updateOrCreate(
                ['user_id' => auth()->id(), 'item_description' => $desc, 'alert_type' => 'system'],
                ['acknowledged_at' => now()]
            );
        } catch (\Exception $e) {
            $acknowledged = session()->get('acknowledged_notifications', []);
            $acknowledged[] = $desc;
            session()->put('acknowledged_notifications', array_unique($acknowledged));
        }

        return response()->json(['success' => true]);
    })->name('api.notifications.dismiss');

    // Admin Routes
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/logs', [AdminController::class, 'logs'])->name('admin.logs');
    Route::get('/admin/inventory', [AdminController::class, 'viewInventory'])->name('admin.inventory');
    Route::get('/admin/permissions', [AdminController::class, 'permissions'])->name('admin.permissions');
    Route::post('/admin/permissions/update', [AdminController::class, 'updatePermission'])->name('admin.permissions.update');
    Route::post('/admin/logs/delete-multiple', [AdminController::class, 'destroyMultipleLogs'])->name('admin.logs.delete_multiple');
    Route::put('/admin/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::get('/admin/messages', [AdminController::class, 'messages'])->name('admin.messages');
    Route::patch('/admin/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle_status');
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
    Route::post('/admin/settings/category', [AdminController::class, 'addCategory'])->name('admin.settings.category');
    Route::delete('/admin/settings/category/{code}', [AdminController::class, 'deleteCategory'])->name('admin.settings.category.destroy');

    // Item Unit Rules
    Route::post('/admin/settings/unit-rule', function(\Illuminate\Http\Request $request) {
        if (!auth()->user()->is_admin) abort(403);
        $category = trim($request->input('category'));
        $keyword = strtolower(trim($request->input('keyword')));
        $unit    = trim($request->input('unit'));
        if (!$category || !$keyword || !$unit) return back()->with('error', 'Category, keyword, and unit are required.');

        $setting = \App\Models\Setting::firstOrCreate(
            ['key' => 'item_unit_rules'],
            ['value' => '{}', 'type' => 'json', 'group' => 'inventory', 'label' => 'Item Unit Rules', 'description' => 'Keyword-to-unit mapping for auto-filling units in new entries.']
        );
        $rules = json_decode($setting->value ?? '{}', true) ?? [];
        
        if (isset($rules[$keyword])) {
            return back()->with('error', "This item and unit already exist in the system.");
        }

        $rules[$keyword] = [
            'category' => $category,
            'unit' => $unit
        ];
        $setting->value = json_encode($rules);
        $setting->save();
        return back()->with('success', "Unit rule added: [{$category}] \"{$keyword}\" → {$unit}");
    })->name('admin.settings.unit-rule.store');

    Route::delete('/admin/settings/unit-rule', function(\Illuminate\Http\Request $request) {
        if (!auth()->user()->is_admin) abort(403);
        $keyword = strtolower(trim($request->input('keyword')));
        $setting = \App\Models\Setting::where('key', 'item_unit_rules')->first();
        if ($setting) {
            $rules = json_decode($setting->value ?? '{}', true) ?? [];
            unset($rules[$keyword]);
            $setting->value = json_encode($rules);
            $setting->save();
        }
        return back()->with('success', "Unit rule for \"{$keyword}\" removed.");
    })->name('admin.settings.unit-rule.destroy');

    // Public API: serve unit rules as JSON for personnel forms
    Route::get('/api/unit-rules', function() {
        $setting = \App\Models\Setting::where('key', 'item_unit_rules')->first();
        return response()->json(json_decode($setting->value ?? '{}', true) ?? []);
    })->name('api.unit-rules');

    // Item Threshold Rules
    Route::post('/admin/settings/threshold-rule', function(\Illuminate\Http\Request $request) {
        if (!auth()->user()->is_admin) abort(403);
        $category = trim($request->input('category'));
        $keyword = strtolower(trim($request->input('keyword')));
        $threshold = (int)$request->input('threshold');
        if (!$category || !$keyword || $threshold < 0) return back()->with('error', 'Category, keyword, and a valid threshold are required.');

        $setting = \App\Models\Setting::firstOrCreate(
            ['key' => 'item_threshold_rules'],
            ['value' => '{}', 'type' => 'json', 'group' => 'inventory', 'description' => 'Keyword-to-threshold mapping for low stock alerts.']
        );
        $rules = json_decode($setting->value ?? '{}', true) ?? [];
        
        $rules[$keyword] = [
            'category' => $category,
            'threshold' => $threshold
        ];
        $setting->value = json_encode($rules);
        $setting->save();
        return back()->with('success', "Threshold rule added: [{$category}] \"{$keyword}\" → {$threshold} units");
    })->name('admin.settings.threshold-rule.store');

    Route::delete('/admin/settings/threshold-rule', function(\Illuminate\Http\Request $request) {
        if (!auth()->user()->is_admin) abort(403);
        $keyword = strtolower(trim($request->input('keyword')));
        $setting = \App\Models\Setting::where('key', 'item_threshold_rules')->first();
        if ($setting) {
            $rules = json_decode($setting->value ?? '{}', true) ?? [];
            unset($rules[$keyword]);
            $setting->value = json_encode($rules);
            $setting->save();
        }
        return back()->with('success', "Threshold rule for \"{$keyword}\" removed.");
    })->name('admin.settings.threshold-rule.destroy');

    // Edit Request Routes
    Route::post('/edit-requests', [\App\Http\Controllers\EditRequestController::class, 'store'])->name('edit-requests.store');
    Route::post('/edit-requests/{id}/process', [\App\Http\Controllers\EditRequestController::class, 'process'])->name('edit-requests.process');
    Route::get('/sra-preview/{id}', [\App\Http\Controllers\ReceivedItemsController::class, 'preview'])->name('sra.preview');
    Route::get('/api/sra-preview/{id}', [\App\Http\Controllers\ReceivedItemsController::class, 'previewApi'])->name('api.sra.preview');
    Route::post('/sra-creation/{id}/process', [\App\Http\Controllers\EditRequestController::class, 'processSraCreation'])->name('sra-creation.process');
    Route::post('/recovery/{id}/process', [\App\Http\Controllers\EditRequestController::class, 'processRecoveryApproval'])->name('recovery.process');
    Route::get('/received-items/{id}/sra', [\App\Http\Controllers\ReceivedItemsController::class, 'sra'])->name('receiveditems.sra');
    Route::get('/edit-requests/status/{itemId}', [\App\Http\Controllers\EditRequestController::class, 'checkStatus'])->name('edit-requests.checkStatus');
    Route::post('/edit-requests/complete/{itemId}', [\App\Http\Controllers\EditRequestController::class, 'complete'])->name('edit-requests.complete');

    // Remainder Preview API — returns preview data for an edit request
    Route::get('/api/edit-requests/{id}/remainder-preview', function ($id) {
        if (!auth()->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $editReq = \App\Models\EditRequest::with('user')->findOrFail($id);

        if ($editReq->request_type === 'item_recovery') {
            $payload = json_decode($editReq->payload, true);
            $issuedItem = \App\Models\IssuedItem::with('issuance')->find($editReq->item_id);
            return response()->json([
                'personnel' => $editReq->user->name ?? 'Unknown',
                'item' => [
                    'description' => $issuedItem->description ?? 'Unknown Item',
                    'category' => $issuedItem->ledge_category ?? 'N/A',
                    'beneficiary' => $issuedItem->issuance->beneficiary ?? 'N/A',
                    'issued_qty' => $issuedItem->qty ?? 0,
                    'return_qty' => $payload['return_qty'] ?? 0,
                    'remarks' => $payload['remarks'] ?? ''
                ]
            ]);
        }

        if ($editReq->request_type === 'edit_submission' || $editReq->request_type === 'edit') {
            return app(\App\Http\Controllers\ReceivedItemsController::class)->previewApi($id);
        }

        if ($editReq->request_type !== 'remainder_submission') {
            return response()->json(['error' => 'Not a remainder request'], 400);
        }

        $data    = json_decode($editReq->payload, true);
        $updates = $data['updates'] ?? [];

        $items = [];
        foreach ($updates as $u) {
            $invItem = \App\Models\InventoryItem::find($u['item_id']);
            if (!$invItem) continue;
            $items[] = [
                'description' => $invItem->description,
                'unit'        => $invItem->unit,
                'current'     => (float) $invItem->stock_balance,
                'adding'      => (float) $u['incoming_qty'],
                'projected'   => (float) $invItem->stock_balance + (float) $u['incoming_qty'],
            ];
        }

        return response()->json([
            'batchId'   => $editReq->item_id,
            'personnel' => $editReq->user->name ?? 'Unknown',
            'status'    => $editReq->status,
            'items'     => $items,
        ]);
    })->name('api.remainder-preview');

    // Recovery Preview API — returns preview data for an item recovery request
    Route::get('/api/edit-requests/{id}/recovery-preview', function ($id) {
        if (!auth()->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $editReq = \App\Models\EditRequest::with('user')->findOrFail($id);

        if ($editReq->request_type !== 'item_recovery') {
            return response()->json(['error' => 'Not a recovery request'], 400);
        }

        $payload = json_decode($editReq->payload, true);
        $issuedItem = \App\Models\IssuedItem::with('issuance')->find($editReq->item_id);

        if (!$issuedItem) {
            return response()->json(['error' => 'Issued item not found'], 404);
        }

        return response()->json([
            'personnel' => $editReq->user->name ?? 'Unknown',
            'status'    => $editReq->status,
            'item'      => [
                'description' => $issuedItem->description,
                'beneficiary' => $issuedItem->issuance->beneficiary,
                'authority'   => $issuedItem->issuance->authority,
                'issued_qty'  => $issuedItem->quantity + (float)$payload['return_qty'], // original qty before this request
                'return_qty'  => (float)$payload['return_qty'],
                'return_date' => $payload['return_date'],
                'remarks'     => $payload['remarks'],
                'category'    => $issuedItem->ledge_category
            ]
        ]);
    })->name('api.recovery-preview');

    // Returns Routes
    Route::post('/returns/purge', [ReturnController::class, 'purge'])->name('returns.purge');
    Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::post('/returns/store', [ReturnController::class, 'store'])->name('returns.store');
    Route::get('/api/returned-items-history', [ReturnController::class, 'history'])->name('api.returned-items-history');

    Route::get('/api/item-audit-details', function (\Illuminate\Http\Request $request) {
        $description = $request->query('description');
        
        $items = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_items.description', $description)
            ->select('inventory_items.*', 'inventory_batches.entry_date', 'inventory_batches.supplier_name')
            ->orderBy('inventory_batches.entry_date', 'desc')
            ->get();

        // Transparency logic: Calculate active temporary loans
        $onLoan = \App\Models\IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->where('issued_items.description', $description)
            ->where('issuances.issuance_type', 'Temporary')
            ->where('issued_items.quantity', '>', 0)
            ->sum('issued_items.quantity');

        return response()->json([
            'batches' => $items,
            'on_loan' => $onLoan
        ]);
    })->name('api.item-audit-details');

    Route::post('/api/inventory/receive-remainder', function (\Illuminate\Http\Request $request) {
        try {
            $updates = $request->input('updates', []);
            $is_admin = auth()->user()->is_admin;

            if (!$is_admin) {
                $firstItem = \App\Models\InventoryItem::find($updates[0]['item_id']);
                $batchId = $firstItem ? $firstItem->batch_id : 0;
                $batch = \App\Models\InventoryBatch::with('items')->find($batchId);
                
                $editReq = \App\Models\EditRequest::create([
                    'user_id' => auth()->id(),
                    'item_id' => $batchId,
                    'item_type' => 'batch',
                    'request_type' => 'remainder_submission',
                    'reason' => 'Receiving Pending Remainder Items',
                    'status' => 'pending',
                    'payload' => json_encode(['updates' => $updates])
                ]);
                $admins = \App\Models\User::where('is_admin', true)->get();
                if ($admins->count() > 0) {
                    $itemNames = collect($updates)->map(function($u) {
                        $item = \App\Models\InventoryItem::find($u['item_id']);
                        return $item ? $item->description : 'Unknown';
                    })->take(3)->implode(', ');
                    if (count($updates) > 3) $itemNames .= ' etc.';

                    // Build full item details for the preview panel
                    $previewRows = '';
                    foreach ($updates as $u) {
                        $invItem = \App\Models\InventoryItem::find($u['item_id']);
                        if (!$invItem) continue;
                        $incoming = floatval($u['incoming_qty']);
                        $currentStock = floatval($invItem->stock_balance);
                        $projected = $currentStock + $incoming;
                        $deficit = abs(floatval($invItem->variance));
                        $previewRows .= "
                            <tr>
                                <td style='padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; font-weight: 700; color: #0f172a;'>{$invItem->description}</td>
                                <td style='padding: 10px 12px; border-bottom: 1px solid #f1f5f9; text-align: center; font-size: 0.85rem; color: #64748b; font-weight: 600;'>{$currentStock}</td>
                                <td style='padding: 10px 12px; border-bottom: 1px solid #f1f5f9; text-align: center;'>
                                    <span style='background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 3px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 800;'>+{$incoming}</span>
                                </td>
                                <td style='padding: 10px 12px; border-bottom: 1px solid #f1f5f9; text-align: center;'>
                                    <span style='background: rgba(79, 70, 229, 0.08); color: #4f46e5; padding: 3px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 800;'>{$projected}</span>
                                </td>
                                <td style='padding: 10px 12px; border-bottom: 1px solid #f1f5f9; text-align: center;'>
                                    <span style='font-size: 0.8rem; color: #94a3b8; font-weight: 600;'>{$invItem->unit}</span>
                                </td>
                            </tr>
                        ";
                    }

                    $msgContent = "<div class='sra-approval-card' style='background: white; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); margin: 10px 0;'>";
                    
                    // Header
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 12px; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;'>";
                    $msgContent .= "<div style='width: 40px; height: 40px; background: #f59e0b; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;'>";
                    $msgContent .= "<i data-lucide='package-plus' style='width: 20px;'></i>";
                    $msgContent .= "</div><div>";
                    $msgContent .= "<h4 style='margin: 0; color: #0f172a; font-size: 0.95rem; font-weight: 800; letter-spacing: -0.01em;'>REMAINDER APPROVAL</h4>";
                    $msgContent .= "<p style='margin: 0; font-size: 0.75rem; color: #64748b; font-weight: 600;'>Pending Partial Delivery Fulfillment</p>";
                    $msgContent .= "</div></div>";
                    
                    // Meta info
                    $msgContent .= "<div style='display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;'>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 8px;'><div style='width: 24px; height: 24px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #64748b;'><i data-lucide='user' style='width: 14px;'></i></div><span style='font-size: 0.85rem; color: #475569;'><b style='color: #0f172a;'>Personnel:</b> " . auth()->user()->name . "</span></div>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 8px;'><div style='width: 24px; height: 24px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #64748b;'><i data-lucide='hash' style='width: 14px;'></i></div><span style='font-size: 0.85rem; color: #475569;'><b style='color: #0f172a;'>Batch ID:</b> #{$batchId}</span></div>";
                    $msgContent .= "<div style='display: flex; align-items: flex-start; gap: 8px;'><div style='width: 24px; height: 24px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #64748b; margin-top: 2px;'><i data-lucide='package' style='width: 14px;'></i></div><span style='font-size: 0.85rem; color: #475569; line-height: 1.4;'><b style='color: #0f172a;'>Items:</b> {$itemNames}</span></div>";
                    $msgContent .= "</div>";

                    // Preview trigger button — uses data-req-id to fetch preview data via API
                    $msgContent .= "<button class='remainder-preview-btn' data-req-id='{$editReq->id}' style='width: 100%; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 10px; font-weight: 700; font-size: 0.82rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 7px; margin-bottom: 8px; transition: all 0.2s;'>";
                    $msgContent .= "<i data-lucide='eye' style='width:15px; flex-shrink:0;'></i> Preview Changes</button>";

                    // Action buttons
                    $msgContent .= "<div id='sra-creation-actions-{$editReq->id}' style='display: flex; flex-direction: column; gap: 8px;'>";
                    $msgContent .= "<button onclick='window.processSraCreationApproval({$editReq->id}, \"approved\", this)' style='background: #10b981; color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px;'>";
                    $msgContent .= "<i data-lucide='check-circle' style='width: 16px;'></i> Approve & Commit</button>";
                    $msgContent .= "<button onclick='window.processSraCreationApproval({$editReq->id}, \"rejected\", this)' style='background: #ef4444; color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px;'>";
                    $msgContent .= "<i data-lucide='x-circle' style='width: 16px;'></i> Reject</button>";
                    $msgContent .= "</div></div>";


                    foreach ($admins as $admin) {
                        \App\Models\Message::create([
                            'sender_id' => auth()->id(),
                            'receiver_id' => $admin->id,
                            'message' => $msgContent,
                            'is_automated' => true,
                            'edit_request_id' => $editReq->id
                        ]);
                    }

                    $confirmation = "<div style='padding: 15px; border: 1px solid #f59e0b; border-radius: 16px; background: rgba(245, 158, 11, 0.03); display: flex; align-items: center; gap: 12px;'>";
                    $confirmation .= "<div style='width: 32px; height: 32px; background: #f59e0b; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;'><i data-lucide='clock' style='width: 16px;'></i></div>";
                    $confirmation .= "<div><b style='color: #f59e0b; font-size: 0.85rem;'>REMAINDER SUBMITTED</b><br><span style='font-size: 0.75rem; color: #64748b; font-weight: 600;'>Awaiting Admin verification for remainder items.</span></div>";
                    $confirmation .= "</div>";

                    \App\Models\Message::create([
                        'sender_id' => $admins->first()->id ?? 1,
                        'receiver_id' => auth()->id(),
                        'message' => $confirmation,
                        'is_automated' => true,
                        'edit_request_id' => $editReq->id
                    ]);
                }

                return response()->json(['success' => true, 'is_pending' => true, 'message' => 'Remainder submission pending admin approval.']);
            }

            $updatedItemsCount = 0;
            $batchIdsToCheck = [];

            foreach($updates as $update) {
                $item = \App\Models\InventoryItem::find($update['item_id']);
                if ($item) {
                    $incoming = floatval($update['incoming_qty']);
                    if ($incoming <= 0) continue;

                    $expected = floatval($item->stock_balance) - floatval($item->variance);
                    $item->stock_balance += $incoming;
                    $item->variance = $item->stock_balance - $expected;
                    $item->remarks = $item->remarks ? $item->remarks . " | Supplemented with $incoming additional units." : "Supplemented with $incoming additional units.";
                    $item->save();
                    $updatedItemsCount++;
                    
                    if (!in_array($item->batch_id, $batchIdsToCheck)) {
                        $batchIdsToCheck[] = $item->batch_id;
                    }
                }
            }
            
            foreach ($batchIdsToCheck as $batchId) {
                $batch = \App\Models\InventoryBatch::find($batchId);
                if ($batch) {
                    // Re-fetch fresh item data after saves
                    $allItems = \App\Models\InventoryItem::where('batch_id', $batchId)->get();
                    $allDelivered = true;
                    
                    foreach ($allItems as $i) {
                        // Negative variance = items still outstanding (shortfall)
                        if (floatval($i->variance) < 0) {
                            $allDelivered = false;
                            break;
                        }
                    }
                    
                    if ($allDelivered) {
                        $batch->supplier_name = preg_replace('/\[Partial Deliv(.*?)\]/i', '[Full Delivery]', $batch->supplier_name);
                        $batch->supplier_status = 'Full Delivery';
                        $batch->save();
                    }
                }
            }
            
            return response()->json(['success' => true, 'updated' => $updatedItemsCount]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    })->name('api.inventory.receive-remainder');
});
Route::get('/system/migrate', function () {
    try {
        $messages = [];
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'last_logout_at')) {
            \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->timestamp('last_logout_at')->nullable();
            });
            $messages[] = "Column 'last_logout_at' added successfully.";
        }
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_active')) {
            \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->boolean('is_active')->default(true);
            });
            \Illuminate\Support\Facades\DB::table('users')->whereNull('is_active')->update(['is_active' => 1]);
            $messages[] = "Column 'is_active' added and initialized successfully.";
        } else {
            $count = \Illuminate\Support\Facades\DB::table('users')->whereNull('is_active')->orWhere('is_active', 0)->update(['is_active' => 1]);
            if ($count > 0) {
                $messages[] = "Status synchronized for $count personnel accounts.";
            }
        }
        
        if (!empty($messages)) {
            return "System Updates Applied:<br>" . implode("<br>", $messages);
        }
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return "System Registry Updated Successfully: " . \Illuminate\Support\Facades\Artisan::output();
    } catch (\Exception $e) {
        return "Migration Failed: " . $e->getMessage();
    }
});

