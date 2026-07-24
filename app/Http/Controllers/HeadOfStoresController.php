<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StoreRequisition;
use App\Models\EditRequest;
use App\Models\ServiceSra;
use App\Models\InventoryItem;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class HeadOfStoresController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        if ($user->role !== 'Head of Stores' && $user->role !== 'Dept. Head (Stores)' && !$user->isMainAdminOrSub() && strcasecmp($user->department ?? '', 'Stores') !== 0 && strcasecmp($user->department ?? '', 'Store') !== 0) {
            abort(403, 'Access Restricted: Head of Stores clearance required.');
        }

        $isStoresHOD = ($user->role === 'Head of Stores')
            || ($user->role === 'Dept. Head (Stores)')
            || ($user->isDepartmentHead() && in_array($user->department, ['Stores', 'Store']))
            || $user->isMainAdminOrSub();

        // 1. Pending Requisitions awaiting Head of Stores review/approval (matches sidebar badge count)
        $requisitionQuery = StoreRequisition::where('status', 'pending')
            ->where('main_admin_status', 'pending')
            ->where(function($q) use ($isStoresHOD) {
                $q->whereRaw('1 = 0');
                if ($isStoresHOD) {
                    $q->orWhere(function($q2) {
                        $q2->where('origin_admin_status', 'approved')
                           ->where(function($q3) {
                               $q3->where('requires_dg_approval', false)
                                  ->orWhere('dg_status', 'approved');
                           });
                    });
                    $q->orWhere(function($q2) {
                        $q2->where('origin_admin_status', 'pending')
                           ->whereIn('department', ['Stores', 'Store']);
                    });
                }
                $q->orWhere(function($q2) {
                    $q2->where('origin_admin_status', 'pending')
                       ->whereNotIn('department', function($subQuery) {
                           $subQuery->select('department')
                                    ->from('users')
                                    ->where('role', 'Department Head')
                                    ->where('is_active', true)
                                    ->whereNotNull('department');
                       });
                });
            });

        $pendingRequisitionsCount = (clone $requisitionQuery)->count();

        // 2. Pending Item Entry Approvals
        $pendingItemEntryCount = EditRequest::where('item_type', 'batch_creation')
            ->where('status', 'pending')
            ->count();

        // 3. Pending Service SRAs awaiting action
        if ($user->role === 'Head of Stores' || $user->role === 'Dept. Head (Stores)') {
            $pendingServiceSraCount = ServiceSra::where('stores_status', 'pending')->whereNotIn('status', ['approved', 'declined'])->count();
        } else {
            $pendingServiceSraCount = ServiceSra::whereNotIn('status', ['approved', 'declined'])->count();
        }

        // 4. Low stock items count
        $lowStockCount = 0;
        $allItems = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->where('inventory_batches.approval_status', '=', 'approved')
            ->selectRaw('TRIM(inventory_items.description) as description, SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
            ->groupBy(DB::raw('TRIM(inventory_items.description)'))
            ->get();
            
        foreach ($allItems as $item) {
            $threshold = Setting::getItemThreshold($item->description);
            if ($threshold > 0 && (float)$item->total_stock < $threshold) {
                $lowStockCount++;
            }
        }

        // 5. Total Active Inventory Items
        $totalItemsCount = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.approval_status', 'approved')
            ->count();

        // 6. Recent Pending Requisitions for quick preview
        $recentRequisitions = (clone $requisitionQuery)->with(['requester'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 7. Recent Pending Item Entry Requests
        $recentItemEntries = EditRequest::with(['user', 'batch'])
            ->where('item_type', 'batch_creation')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('stores.dashboard', compact(
            'pendingRequisitionsCount',
            'pendingItemEntryCount',
            'pendingServiceSraCount',
            'lowStockCount',
            'totalItemsCount',
            'recentRequisitions',
            'recentItemEntries'
        ));
    }
}
