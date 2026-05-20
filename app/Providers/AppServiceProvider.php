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
            $logPath = base_path('debug_output.txt');
            $data = [];
            
            $data['pending_edit_requests'] = \App\Models\EditRequest::where('status', 'pending')
                ->get(['id', 'request_type', 'status', 'payload'])
                ->map(function($req) {
                    return [
                        'id' => $req->id,
                        'request_type' => $req->request_type,
                        'status' => $req->status,
                        'payload' => json_decode($req->payload, true)
                    ];
                })
                ->toArray();
                
            $data['suppliers_table_exists'] = \Illuminate\Support\Facades\Schema::hasTable('suppliers');
            if ($data['suppliers_table_exists']) {
                $data['suppliers'] = \App\Models\Supplier::all()->toArray();
            }
            
            $data['suppliers_registry'] = \App\Models\Setting::get('suppliers_registry');
            
            file_put_contents($logPath, json_encode($data, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            file_put_contents(base_path('debug_output.txt'), "Error: " . $e->getMessage());
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
            }
        });
    }
}
