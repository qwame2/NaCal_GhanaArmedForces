<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'service_number')) {
                \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('service_number')->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'rank')) {
                \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('rank')->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'usage_type')) {
                \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('usage_type')->default('permanent');
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisition_items', 'alternative_description')) {
                \Illuminate\Support\Facades\Schema::table('store_requisition_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('alternative_description')->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisition_items', 'alternative_quantity_approved')) {
                \Illuminate\Support\Facades\Schema::table('store_requisition_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->decimal('alternative_quantity_approved', 15, 2)->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'main_admin_status')) {
                \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('main_admin_status')->default('pending');
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'origin_admin_status')) {
                \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('origin_admin_status')->default('pending');
                });
            }
        } catch (\Exception $e) {
            // Ignore
        }



        view()->composer(['layouts.dashboard', 'layouts.admin'], function ($view) {
            if (auth()->check()) {
                // Fetch acknowledged notifications from Database (Permanent) or Session (Fallback)
                try {
                    $acknowledged = \App\Models\NotificationAcknowledgement::where('user_id', auth()->id())
                        ->pluck('item_description')
                        ->toArray();
                } catch (\Exception $e) {
                    $acknowledged = session()->get('acknowledged_notifications', []);
                }

                // Fetch all unique items (excluding acknowledged)
                $allItems = \App\Models\InventoryItem::selectRaw('description, SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
                    ->whereNotIn(\DB::raw('TRIM(description)'), array_map('trim', $acknowledged))
                    ->groupBy('description')
                    ->get();

                $lowStockNotifications = [];
                foreach ($allItems as $item) {
                    $itemThreshold = \App\Models\Setting::getItemThreshold($item->description);
                    if ($itemThreshold > 0 && (float)$item->total_stock < $itemThreshold) {
                        $lowStockNotifications[] = [
                            'type' => 'warning',
                            'title' => 'Low Stock: ' . $item->description,
                            'message' => "Critical balance detected: " . number_format($item->total_stock, 0) . " " . \App\Models\Setting::getItemUnit($item->description) . " remaining.",
                            'icon' => 'alert-triangle',
                            'route' => auth()->user()->is_admin ? 'admin.index' : 'dashboard'
                        ];
                    }
                }

                // Fetch expired items details
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

                $view->with('globalNotifications', $notifications);
                $view->with('globalNotificationCount', count($notifications));
                
                // Fetch Unread Messages Count
                $unreadMessagesCount = \App\Models\Message::where('receiver_id', auth()->id())
                    ->whereNull('read_at')
                    ->where('is_archived', false)
                    ->count();
                $view->with('unreadMessagesCount', $unreadMessagesCount);
                
                // Fetch Pending Password Requests Count (for Admin)
                if (auth()->user()->is_admin) {
                    $pendingPasswordRequests = \App\Models\PasswordResetRequest::where('status', 'pending')->count();
                    $view->with('pendingPasswordRequests', $pendingPasswordRequests);
                    // Heads only see pending requisitions that have been approved by Main Admin
                    $pendingRequisitionsCount = \App\Models\StoreRequisition::where('status', 'pending')->where('main_admin_status', 'approved')->count();
                    $view->with('pendingRequisitionsCount', $pendingRequisitionsCount);
                    $view->with('mainRequisitionsCount', 0);
                } else {
                    $view->with('pendingPasswordRequests', 0);
                    $view->with('pendingRequisitionsCount', 0);
                    
                    // Main Admin count of pending requisitions awaiting review
                    $isStoresHead = (strcasecmp(auth()->user()->department, 'Stores') === 0 || strcasecmp(auth()->user()->department, 'Store') === 0);
                    if ($isStoresHead) {
                        $mainRequisitionsCount = \App\Models\StoreRequisition::where('status', 'pending')
                            ->where('origin_admin_status', 'approved')
                            ->where('main_admin_status', 'pending')
                            ->count();
                    } else {
                        $mainRequisitionsCount = \App\Models\StoreRequisition::where('status', 'pending')
                            ->where('department', auth()->user()->department)
                            ->where('origin_admin_status', 'pending')
                            ->count();
                    }
                    $view->with('mainRequisitionsCount', $mainRequisitionsCount);

                    if (auth()->user()->role === 'Requisitioner') {
                        // Requisitioners: count their own approved reqs awaiting collection
                        $approvedRequisitionsCount = \App\Models\StoreRequisition::where('requested_by', auth()->id())
                            ->whereIn('status', ['approved', 'partially_approved'])
                            ->whereNull('collected_at')
                            ->count();
                    } else {
                        // Personnel staff: count all approved reqs awaiting collection confirmation
                        $approvedRequisitionsCount = \App\Models\StoreRequisition::whereIn('status', ['approved', 'partially_approved'])
                            ->whereNull('collected_at')
                            ->count();
                    }
                    $view->with('approvedRequisitionsCount', $approvedRequisitionsCount);
                }
            }
        });
    }
}
