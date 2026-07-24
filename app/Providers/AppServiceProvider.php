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
        if (app()->environment() !== 'testing') {
            try {
            \Illuminate\Support\Facades\Cache::remember('schema_healed_v13', 86400, function () {
                // Ensure can_make_requisition column exists for requisitioner permission gating
                if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'can_make_requisition')) {
                        \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                            $table->boolean('can_make_requisition')->default(true)->after('can_generate_reports');
                        });
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'can_approve_requisition')) {
                        \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                            $table->boolean('can_approve_requisition')->default(true)->after('can_make_requisition');
                        });
                    }
                }

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
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'requires_dg_approval')) {
                        \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                            $table->boolean('requires_dg_approval')->default(false);
                        });
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'dg_status')) {
                        \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                            $table->string('dg_status')->nullable();
                        });
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'dg_approved_by')) {
                        \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                            $table->string('dg_approved_by')->nullable();
                        });
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'dg_approved_at')) {
                        \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                            $table->timestamp('dg_approved_at')->nullable();
                        });
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'dg_decline_reason')) {
                        \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                            $table->text('dg_decline_reason')->nullable();
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
                    if (!\App\Models\Setting::where('key', 'dg_approval_categories')->exists()) {
                        \App\Models\Setting::create([
                            'key' => 'dg_approval_categories',
                            'value' => '[]',
                            'type' => 'json',
                            'group' => 'inventory',
                            'description' => 'Categories of items that require Director General (DG) approval before going to the Head of Stores.'
                        ]);
                    }
                    if (!\App\Models\Setting::where('key', 'delegated_approver_id')->exists()) {
                        \App\Models\Setting::create([
                            'key' => 'delegated_approver_id',
                            'value' => '',
                            'type' => 'integer',
                            'group' => 'general',
                            'description' => 'User ID of the Store Officer delegated to stand in and perform approvals.'
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
                            ->where('registration_status', 'approved')
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
                            ->where('registration_status', 'approved')
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

                // Ensure all existing Auditor accounts have is_temp_account set to true and can_approve_requisition set to true
                try {
                    \App\Models\User::where('role', 'Auditor')->update([
                        'is_temp_account' => true,
                        'can_approve_requisition' => true
                    ]);
                } catch (\Exception $ex) {
                    // Ignore
                }

                // Ensure all existing Store Officer (role: Officer) accounts have department set to 'Stores'
                try {
                    \App\Models\User::where('role', 'Officer')->where(function($q) {
                        $q->whereNull('department')->orWhere('department', '!=', 'Stores');
                    })->update(['department' => 'Stores']);
                } catch (\Exception $ex) {
                    // Ignore
                }
                // Ensure Non Departmental is updated to Audit Department
                try {
                    \App\Models\User::where('department', 'Non Departmental')->update(['department' => 'Audit Department']);
                } catch (\Exception $ex) {
                    // Ignore
                }

                // Ensure the registration_status column exists in users table
                try {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'registration_status')) {
                        \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                            $table->string('registration_status')->default('approved');
                        });
                    }
                } catch (\Exception $ex) {
                    // Ignore
                }
                
                return true;
            });
        } catch (\Exception $e) {
            // Ignore
        }
    }

        view()->composer(['layouts.dashboard', 'layouts.admin'], function ($view) {
            if (auth()->check()) {
                // Check SRA batch creation approvals for this user
                try {
                    $approvedCreations = \App\Models\EditRequest::where('user_id', auth()->id())
                        ->where('item_type', 'batch_creation')
                        ->where('status', 'approved')
                        ->get();
                    if ($approvedCreations->isNotEmpty()) {
                        $view->with('customToastMessage', 'Stock entry request successfully authorized and added to live stock.');
                        foreach ($approvedCreations as $ac) {
                            $ac->status = 'completed';
                            $ac->save();
                        }
                    }
                } catch (\Exception $e) {
                    // Fail silently
                }

                // Fetch acknowledged notifications from Database (Permanent) or Session (Fallback)
                try {
                    $acknowledged = \App\Models\NotificationAcknowledgement::where('user_id', auth()->id())
                        ->pluck('item_description')
                        ->toArray();
                } catch (\Exception $e) {
                    $acknowledged = session()->get('acknowledged_notifications', []);
                }

                // Fetch all unique items (excluding acknowledged)
                $allItems = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                    ->where('inventory_batches.supplier_status', '!=', 'System Draft')
                    ->where('inventory_batches.approval_status', '=', 'approved')
                    ->selectRaw('TRIM(inventory_items.description) as description, SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
                    ->whereNotIn(\DB::raw('TRIM(inventory_items.description)'), array_map('trim', $acknowledged))
                    ->groupBy(\DB::raw('TRIM(inventory_items.description)'))
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
                $expiredItems = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                    ->where('inventory_batches.supplier_status', '!=', 'System Draft')
                    ->where('inventory_batches.approval_status', '=', 'approved')
                    ->selectRaw('TRIM(inventory_items.description) as description, SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock, SUM(CAST(REPLACE(inventory_items.qty, ",", "") AS DECIMAL(15,2))) as total_qty')
                    ->whereNotIn(\DB::raw('TRIM(inventory_items.description)'), array_map('trim', $acknowledged))
                    ->groupBy(\DB::raw('TRIM(inventory_items.description)'))
                    ->havingRaw('SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) = 0 AND SUM(CAST(REPLACE(inventory_items.qty, ",", "") AS DECIMAL(15,2))) >= 1')
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
                $view->with('globalNotificationCount', count($lowStockNotifications));
                
                // Fetch Unread Messages Count (peer-to-peer user chats only)
                $unreadMessagesCount = \App\Models\Message::where('receiver_id', auth()->id())
                    ->whereNull('read_at')
                    ->where('is_archived', false)
                    ->where('is_automated', false)
                    ->count();
                $view->with('unreadMessagesCount', $unreadMessagesCount);

                $pendingItemEntryApprovalsCount = \App\Models\EditRequest::where('item_type', 'batch_creation')
                    ->where('status', 'pending')
                    ->count();
                $view->with('pendingItemEntryApprovalsCount', $pendingItemEntryApprovalsCount);
                
                // Fetch Pending Password Requests Count (for Admin)
                if ((auth()->user()->is_admin || auth()->user()->isDelegatedApprover()) && !auth()->user()->isMainAdminOrSub() && !in_array(auth()->user()->role, ['Head of Stores', 'Department Head', 'Auditor'])) {
                    $pendingPasswordRequests = \App\Models\PasswordResetRequest::where('status', 'pending')->count();
                    $view->with('pendingPasswordRequests', $pendingPasswordRequests);
                    // Heads only see pending requisitions that have been approved by Main Admin
                    $pendingRequisitionsCount = \App\Models\StoreRequisition::where('status', 'pending')->where('main_admin_status', 'approved')->count();
                    $view->with('pendingRequisitionsCount', $pendingRequisitionsCount);
                    $view->with('mainRequisitionsCount', 0);
                } else {
                    $view->with('pendingPasswordRequests', 0);
                    
                    // Main Admin / Sub Main Admin count of pending requisitions awaiting review
                    $isStoresHead = (auth()->user()->isMainAdminOrSub() || auth()->user()->role === 'Head of Stores' || strcasecmp(auth()->user()->department ?? '', 'Stores') === 0 || strcasecmp(auth()->user()->department ?? '', 'Store') === 0);
                    if (!$isStoresHead) {
                        $isBackup = (auth()->user()->isDepartmentHead() && in_array(auth()->user()->department, ['Human Resource Management Department', 'Welfare Department']));
                        if ($isBackup) {
                            if (!\App\Models\User::isPrimaryStoresHeadOnline()) {
                                $isStoresHead = true;
                            }
                        }
                    }
                    $hasActiveStoresHead = \App\Models\User::where('role', 'Head of Stores')->where('is_active', true)->exists()
                        || \App\Models\User::whereIn('role', ['Department Head', 'Dept Head HR', 'Head of Welfare'])->whereIn('department', ['Stores', 'Store'])->where('is_active', true)->exists();
                    $isStoresHOD = (auth()->user()->role === 'Head of Stores')
                        || (auth()->user()->isDepartmentHead() && in_array(auth()->user()->department, ['Stores', 'Store']))
                        || auth()->user()->isMainAdminOrSub();

                    $isBackupActive = $isStoresHead && !in_array(strtoupper(auth()->user()->department ?? ''), ['STORES', 'STORE']);

                    $dept = auth()->user()->department;
                    $depts = [$dept];
                    $dLower = strtolower(trim($dept ?? ''));
                    if (in_array($dLower, ['hr', 'human resource', 'human resource management department', 'human resources'])) {
                        $depts = ['HR', 'Human Resource', 'Human Resource Management Department', 'Human Resources'];
                    } elseif (in_array($dLower, ['welfare', 'welfare department'])) {
                        $depts = ['Welfare', 'Welfare Department'];
                    } elseif (in_array($dLower, ['stores', 'store', 'stores department', 'store department'])) {
                        $depts = ['Stores', 'Store', 'Stores Department', 'Store Department'];
                    }

                    if ($isStoresHead) {
                        // Badge shows pending requisitions awaiting Stores Head action
                        $mainStoreRequisitionsCount = \App\Models\StoreRequisition::awaitingHeadOfStoresReview()->count();
                    } else {
                        $mainStoreRequisitionsCount = \App\Models\StoreRequisition::where('status', 'pending')
                            ->where(function($q) use ($depts) {
                                $q->whereIn('department', $depts)
                                  ->orWhereIn('department', ['Audit Department', 'Non Departmental'])
                                  ->orWhereHas('requester', function($sq) {
                                      $sq->where('sponsored_by', auth()->id());
                                  });
                            })
                            ->where(function($q) {
                                $q->where('origin_admin_status', 'pending')
                                  ->orWhere('alternative_status', 'proposed');
                            })
                            ->count();
                    }
                    $view->with('pendingRequisitionsCount', $mainStoreRequisitionsCount);

                    // Count Service SRAs still in the workflow (not yet fully approved/declined)
                    // Badge stays until the request is fully resolved
                    if (auth()->user()->role === 'Auditor') {
                        $pendingSrasCount = \App\Models\ServiceSra::where('auditor_status', 'pending')
                            ->whereNotIn('status', ['approved', 'declined'])
                            ->count();
                    } else {
                        // For admin roles: count all ServiceSRAs not yet fully resolved
                        $pendingSrasCount = \App\Models\ServiceSra::whereNotIn('status', ['approved', 'declined'])->count();
                        // For inventory SRAs: only count ones where admin action is still needed
                        // (once admin approves, it moves to Auditor's queue — don't inflate admin badge)
                        $isAdminUser = auth()->user()->isMainAdminOrSub() || auth()->user()->role === 'Main Admin';
                        if ($isAdminUser) {
                            $pendingSrasCount += \App\Models\InventoryBatch::where('approval_status', 'pending_auditor_admin')
                                ->where('supplier_status', '!=', 'System Draft')
                                ->where('admin_status', 'pending')
                                ->count();
                        } else {
                            $pendingSrasCount += \App\Models\InventoryBatch::where('approval_status', 'pending_auditor_admin')
                                ->where('supplier_status', '!=', 'System Draft')
                                ->count();
                        }
                    }

                    $mainRequisitionsCount = $mainStoreRequisitionsCount;
                    $view->with('mainRequisitionsCount', $mainRequisitionsCount);
                    $view->with('pendingServiceSraBadgeCount', $pendingSrasCount);

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
