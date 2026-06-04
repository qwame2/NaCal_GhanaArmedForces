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
            if (\Illuminate\Support\Facades\Schema::hasTable('issuances')) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('issuances', 'requisition_id')) {
                    \Illuminate\Support\Facades\Schema::table('issuances', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->unsignedBigInteger('requisition_id')->nullable();
                    });
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('issued_items')) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('issued_items', 'unit')) {
                    \Illuminate\Support\Facades\Schema::table('issued_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->string('unit')->nullable();
                    });
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('store_requisitions')) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'collector_location')) {
                    \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->string('collector_location')->nullable();
                    });
                }
            }

            if (!\Illuminate\Support\Facades\Schema::hasTable('returned_items')) {
                \Illuminate\Support\Facades\Schema::create('returned_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->foreignId('issued_item_id')->constrained('issued_items')->onDelete('cascade');
                    $table->integer('returned_qty');
                    $table->date('return_date');
                    $table->text('remarks')->nullable();
                    $table->timestamps();
                });
            } else {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('returned_items', 'remarks')) {
                    \Illuminate\Support\Facades\Schema::table('returned_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->text('remarks')->nullable();
                    });
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                if (!\App\Models\Setting::where('key', 'stores_dept_head_approval_categories')->exists()) {
                    \App\Models\Setting::create([
                        'key' => 'stores_dept_head_approval_categories',
                        'value' => '[]',
                        'type' => 'json',
                        'group' => 'inventory',
                        'description' => 'Categories of items that require Department Head (Stores) approval before going to the Head of Stores.'
                    ]);
                }
            }

            if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'service_number')) {
                \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('service_number')->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'signature')) {
                \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('signature')->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'origin_approved_by')) {
                \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('origin_approved_by')->nullable();
                });
            } else {
                try {
                    \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->string('origin_approved_by', 255)->nullable()->change();
                    });
                } catch (\Exception $e) {
                    // Ignore
                }
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'stores_approved_by')) {
                \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('stores_approved_by')->nullable();
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
            if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'alternative_status')) {
                \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('alternative_status')->nullable();
                });
            }

            if (!\Illuminate\Support\Facades\Schema::hasTable('receipts')) {
                \Illuminate\Support\Facades\Schema::create('receipts', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('requisition_id')->unique();
                    $table->string('receipt_number')->unique();
                    $table->string('collector_name');
                    $table->string('collector_contact');
                    $table->string('collector_location');
                    $table->timestamp('collected_at');
                    $table->unsignedBigInteger('issued_by');
                    $table->string('approved_by_dept_head')->nullable();
                    $table->string('approved_by_stores_head')->nullable();
                    $table->text('items_json');
                    $table->timestamps();
                });
            }

            // Backfill origin_approved_by for existing approved requisitions
            try {
                $needsBackfill = \App\Models\StoreRequisition::whereNull('origin_approved_by')
                    ->where('origin_admin_status', 'approved')
                    ->get();
                foreach ($needsBackfill as $req) {
                    $deptHead = \App\Models\User::whereIn('role', ['Main Admin', 'Department Head'])
                        ->where('department', $req->department)
                        ->first();
                    if ($deptHead) {
                        $req->origin_approved_by = $deptHead->name;
                        $req->save();
                    } else {
                        $req->origin_approved_by = $req->department . " Department Head";
                        $req->save();
                    }
                }
            } catch (\Exception $ex) {
                // Ignore backfill errors on migration
            }

            // Backfill stores_approved_by for existing approved requisitions
            try {
                $needsBackfillStores = \App\Models\StoreRequisition::whereNull('stores_approved_by')
                    ->where('main_admin_status', 'approved')
                    ->get();
                foreach ($needsBackfillStores as $req) {
                    $storesHead = \App\Models\User::whereIn('role', ['Main Admin', 'Department Head'])
                        ->where(fn($q) => $q->where('department', 'Stores')->orWhere('department', 'Store'))
                        ->first();
                    if ($storesHead) {
                        $req->stores_approved_by = $storesHead->name;
                        $req->save();
                    } else {
                        $req->stores_approved_by = "Stores Department Head";
                        $req->save();
                    }
                }
            } catch (\Exception $ex) {
                // Ignore backfill errors on migration
            }

            // Ensure all existing Auditor accounts have is_temp_account set to true
            try {
                \App\Models\User::where('role', 'Auditor')->where('is_temp_account', false)->update(['is_temp_account' => true]);
            } catch (\Exception $ex) {
                // Ignore
            }

            // Ensure all existing Store Officer (role: Officer) accounts have department set to 'Store'
            try {
                \App\Models\User::where('role', 'Officer')->where(function($q) {
                    $q->whereNull('department')->orWhere('department', '!=', 'Store');
                })->update(['department' => 'Store']);
            } catch (\Exception $ex) {
                // Ignore
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
                if (auth()->user()->is_admin && auth()->user()->role !== 'Main Admin') {
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
                    if (!$isStoresHead) {
                        $isBackup = (auth()->user()->role === 'Department Head' && in_array(auth()->user()->department, ['Human Resource Management Department', 'Welfare Department']));
                        if ($isBackup) {
                            $primaryOnline = \App\Models\User::where(function($q) {
                                    $q->where('role', 'Main Admin')
                                      ->orWhere('role', 'Dept. Head (Stores)')
                                      ->orWhereIn('department', ['Stores', 'Store']);
                                })
                                ->where('is_online', true)
                                ->where('is_active', true)
                                ->exists();
                            if (!$primaryOnline) {
                                $isStoresHead = true;
                            }
                        }
                    }
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
