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

// Authentication Routes
Route::get('/login', [AuthController::class, 'showAuth'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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
            ->select('inventory_items.description', 'inventory_batches.ledge_category', 'inventory_items.stock_balance', 'inventory_items.qty', 'inventory_items.variance')
            ->whereIn('inventory_items.id', function($query) {
                $query->selectRaw('MAX(id)')
                    ->from('inventory_items')
                    ->groupBy('description');
            })
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
        $ledgeMap = [
            'A' => 'Stationary',
            'B' => 'Cleaning',
            'C' => 'IT & Acc.',
            'D' => 'Transport',
            'E' => 'Safety',
            'G' => 'Pharmacy',
            'J' => 'Equipment'
        ];

        // Low Stock Alerts (Stock < 100) - Grouped by Description to handle duplicates
        $lowStockCount = \App\Models\InventoryItem::selectRaw('description, SUM(stock_balance) as total_stock')
            ->groupBy('description')
            ->havingRaw('SUM(stock_balance) < 100')
            ->get()
            ->count();

        // 50% Threshold Monitoring for Ledge Categories
        $thresholdLedges = ['A', 'B', 'C', 'D', 'E', 'G', 'J'];
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
                // COMMON SENSE OVERRIDE: Low stock if percentage <= 50% OR absolute qty < 100 units
                $isOverride = $avail < 100;
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
            ->havingRaw('SUM(inventory_items.stock_balance) < 100')
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
            ->select('inventory_items.*', 'inventory_batches.entry_date', 'inventory_batches.ledge_category', 'inventory_batches.supplier_name', 'inventory_batches.donor_name', 'inventory_batches.acquisition_type')
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
        $ledgeMap = [
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

    Route::get('/api/notifications', function() {
        if (!auth()->check()) return response()->json(['error' => 'Unauthenticated'], 401);

        try {
            $acknowledged = \App\Models\NotificationAcknowledgement::where('user_id', auth()->id())
                ->pluck('item_description')
                ->toArray();
        } catch (\Exception $e) {
            $acknowledged = session()->get('acknowledged_notifications', []);
        }

        $lowStockItems = \App\Models\InventoryItem::selectRaw('description, SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
            ->whereNotIn(\DB::raw('TRIM(description)'), array_map('trim', $acknowledged))
            ->groupBy('description')
            ->havingRaw('SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) < 100')
            ->get();

        $expiredItems = \App\Models\InventoryItem::selectRaw('description, SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock, SUM(CAST(REPLACE(qty, ",", "") AS DECIMAL(15,2))) as total_qty')
            ->whereNotIn(\DB::raw('TRIM(description)'), array_map('trim', $acknowledged))
            ->groupBy('description')
            ->havingRaw('SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) = 0 AND SUM(CAST(REPLACE(qty, ",", "") AS DECIMAL(15,2))) >= 1')
            ->get();

        $notifications = [];
        $is_admin = auth()->user()->is_admin;
        
        foreach ($lowStockItems as $item) {
            $notifications[] = [
                'type' => 'warning',
                'title' => 'Low Stock: ' . $item->description,
                'message' => "Critical balance detected: " . number_format($item->total_stock, 0) . " units remaining.",
                'icon' => 'alert-triangle',
                'route' => $is_admin ? 'admin.index' : 'dashboard'
            ];
        }

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

        $lowStockItems = \App\Models\InventoryItem::selectRaw('description')
            ->groupBy('description')
            ->havingRaw('SUM(stock_balance) < 100')
            ->pluck('description')
            ->toArray();

        $expiredItems = \App\Models\InventoryItem::selectRaw('description')
            ->groupBy('description')
            ->havingRaw('SUM(stock_balance) = 0 AND SUM(qty) >= 1')
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
    Route::get('/admin/permissions', [AdminController::class, 'permissions'])->name('admin.permissions');
    Route::post('/admin/permissions/update', [AdminController::class, 'updatePermission'])->name('admin.permissions.update');
    Route::post('/admin/logs/delete-multiple', [AdminController::class, 'destroyMultipleLogs'])->name('admin.logs.delete_multiple');
    Route::put('/admin/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::get('/admin/messages', [AdminController::class, 'messages'])->name('admin.messages');
    Route::patch('/admin/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle_status');

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
            $updatedItemsCount = 0;
            $batchIdsToCheck = [];

            foreach($updates as $update) {
                $item = \App\Models\InventoryItem::find($update['item_id']);
                if ($item) {
                    $incoming = floatval($update['incoming_qty']);
                    if ($incoming <= 0) continue;

                    // Calculate expected amount based on original stock and variance
                    // This works for both new data (where variance = stock - qty) and old data
                    $expected = floatval($item->stock_balance) - floatval($item->variance);
                    
                    $item->stock_balance += $incoming;
                    // Variance is actual physical stock minus the expected invoice quantity
                    $item->variance = $item->stock_balance - $expected;
                    
                    // Keep the remarks updated to note the remainder was entered
                    $item->remarks = $item->remarks ? $item->remarks . " | Supplemented with $incoming additional units." : "Supplemented with $incoming additional units.";
                    
                    $item->save();
                    $updatedItemsCount++;
                    
                    if (!in_array($item->batch_id, $batchIdsToCheck)) {
                        $batchIdsToCheck[] = $item->batch_id;
                    }
                }
            }
            
            // Evaluate batch status logic for Partial -> Full Delivery
            foreach ($batchIdsToCheck as $batchId) {
                $batch = \App\Models\InventoryBatch::find($batchId);
                if ($batch && preg_match('/\[Partial Deliv(.*?)\]/i', $batch->supplier_name)) {
                    $allItems = \App\Models\InventoryItem::where('batch_id', $batchId)->get();
                    $allDelivered = true;
                    
                    foreach ($allItems as $i) {
                        $expected = floatval($i->stock_balance) - floatval($i->variance);
                        if (floatval($i->stock_balance) < $expected) {
                            $allDelivered = false;
                            break;
                        }
                    }
                    
                    if ($allDelivered) {
                        // Update supplier_name to Full Delivery
                        $batch->supplier_name = preg_replace('/\[Partial Deliv(.*?)\]/i', '[Full Delivery]', $batch->supplier_name);
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
