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

                // Fetch low stock items details
                $lowStockItems = \App\Models\InventoryItem::selectRaw('description, SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
                    ->whereNotIn(\DB::raw('TRIM(description)'), array_map('trim', $acknowledged))
                    ->groupBy('description')
                    ->havingRaw('SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) < 100')
                    ->get();

                // Fetch expired items details
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

                $view->with('globalNotifications', $notifications);
                $view->with('globalNotificationCount', count($notifications));
            }
        });
    }
}
