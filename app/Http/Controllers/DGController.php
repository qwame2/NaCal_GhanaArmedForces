<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use App\Models\InventoryItem;
use App\Models\IssuedItem;
use App\Models\ReturnedItem;
use App\Models\StoreRequisition;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;

class DGController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'Director General') {
            abort(403, 'Access Restricted: Director General clearance required.');
        }

        // Summary statistics
        $totalLogsCount = SystemLog::count();
        $totalVariance = InventoryItem::sum('variance');
        $activeLoansCount = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->where('issuances.issuance_type', 'Temporary')
            ->where('issued_items.quantity', '>', 0)
            ->count();
        $pendingRequisitionsCount = StoreRequisition::where('status', 'pending')->count();
        $totalActiveUsers = User::where('registration_status', 'approved')->where('is_active', true)->count();

        // 1. System Audit Trail (System Logs)
        $logsQuery = SystemLog::with('user')->orderBy('created_at', 'desc');
        if ($request->filled('log_severity')) {
            $logsQuery->where('severity', $request->log_severity);
        }
        if ($request->filled('log_event')) {
            $logsQuery->where('event_type', $request->log_event);
        }
        if ($request->filled('date_from')) {
            $logsQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $logsQuery->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $logsQuery->where(function ($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('action', 'LIKE', "%{$search}%")
                  ->orWhere('event_type', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('username', 'LIKE', "%{$search}%");
                  });
            });
        }
        $systemLogs = $logsQuery->paginate(10, ['*'], 'logs_page')->withQueryString();

        // 2. Stock Oversight & Registry (Inventory Items)
        $itemsQuery = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->where('inventory_batches.approval_status', '=', 'approved')
            ->select(
                'inventory_items.*',
                'inventory_batches.entry_date',
                'inventory_batches.supplier_name',
                'inventory_batches.ledge_category',
                'inventory_batches.acquisition_type'
            )
            ->orderBy('inventory_batches.entry_date', 'desc');

        if ($request->filled('date_from')) {
            $itemsQuery->whereDate('inventory_batches.entry_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $itemsQuery->whereDate('inventory_batches.entry_date', '<=', $request->date_to);
        }
        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $itemsQuery->where('inventory_items.description', 'LIKE', "%{$search}%");
        }
        $inventoryItems = $itemsQuery->paginate(10, ['*'], 'items_page')->withQueryString();

        // 3. Staff Requisitions Registry (All requisitions & status checks)
        $reqsQuery = StoreRequisition::with('requester')->orderBy('created_at', 'desc');
        if ($request->filled('req_status')) {
            $reqsQuery->where('status', $request->req_status);
        }
        if ($request->filled('date_from')) {
            $reqsQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $reqsQuery->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $reqsQuery->where(function ($q) use ($search) {
                $q->where('requester_name', 'LIKE', "%{$search}%")
                  ->orWhere('department', 'LIKE', "%{$search}%")
                  ->orWhere('purpose', 'LIKE', "%{$search}%");
            });
        }
        $requisitions = $reqsQuery->paginate(10, ['*'], 'reqs_page')->withQueryString();

        // 4. User Presence Overview (Active / online user accounts)
        $usersQuery = User::where('registration_status', 'approved')->orderBy('name', 'asc');
        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('username', 'LIKE', "%{$search}%")
                  ->orWhere('role', 'LIKE', "%{$search}%")
                  ->orWhere('department', 'LIKE', "%{$search}%");
            });
        }
        $users = $usersQuery->paginate(10, ['*'], 'users_page')->withQueryString();

        $ledgeMap = Setting::getCategories();

        return view('dg.index', compact(
            'totalLogsCount',
            'totalVariance',
            'activeLoansCount',
            'pendingRequisitionsCount',
            'totalActiveUsers',
            'systemLogs',
            'inventoryItems',
            'requisitions',
            'users',
            'ledgeMap'
        ));
    }

    public function printReport(Request $request)
    {
        if (auth()->user()->role !== 'Director General') {
            abort(403, 'Access Restricted: Director General clearance required.');
        }

        // Fetch logs and records with filters for the executive printable report
        $logsQuery = SystemLog::with('user')->orderBy('created_at', 'desc');
        $receivedQuery = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->where('inventory_batches.approval_status', '=', 'approved')
            ->select('inventory_items.*', 'inventory_batches.entry_date', 'inventory_batches.supplier_name', 'inventory_batches.ledge_category')
            ->orderBy('inventory_batches.entry_date', 'desc');
        $reqsQuery = StoreRequisition::with('requester')->orderBy('created_at', 'desc');
        $usersQuery = User::where('registration_status', 'approved')->orderBy('name', 'asc');

        if ($request->filled('date_from')) {
            $from = $request->date_from;
            $logsQuery->whereDate('created_at', '>=', $from);
            $receivedQuery->whereDate('inventory_batches.entry_date', '>=', $from);
            $reqsQuery->whereDate('created_at', '>=', $from);
        }
        if ($request->filled('date_to')) {
            $to = $request->date_to;
            $logsQuery->whereDate('created_at', '<=', $to);
            $receivedQuery->whereDate('inventory_batches.entry_date', '<=', $to);
            $reqsQuery->whereDate('created_at', '<=', $to);
        }

        $systemLogs = $logsQuery->limit(200)->get();
        $receivedItems = $receivedQuery->limit(200)->get();
        $requisitions = $reqsQuery->limit(200)->get();
        $users = $usersQuery->limit(200)->get();

        $ledgeMap = Setting::getCategories();
        $dg = auth()->user();

        return view('dg.print', compact(
            'systemLogs',
            'receivedItems',
            'requisitions',
            'users',
            'ledgeMap',
            'dg'
        ));
    }
}
