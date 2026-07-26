<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use App\Models\InventoryItem;
use App\Models\IssuedItem;
use App\Models\ReturnedItem;
use App\Models\InventoryBatch;
use App\Models\StoreRequisition;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;

class ExternalAuditorController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'External Auditor') {
            abort(403, 'Access Restricted: External Auditor clearance required.');
        }

        // 1. Summary Statistics for External Auditor Dashboard
        $totalLogsCount = SystemLog::count();
        $totalVariance  = InventoryItem::sum('variance');

        $activeLoansCount = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->where('issuances.issuance_type', 'Temporary')
            ->where('issued_items.quantity', '>', 0)
            ->count();

        // 2. Fetch Audit Trail (System Logs)
        $logsQuery = SystemLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('log_severity')) {
            $logsQuery->where('severity', $request->log_severity);
        }
        if ($request->filled('log_event')) {
            $logsQuery->where('event_type', $request->log_event);
        }
        if ($request->filled('user_id')) {
            $u = User::find($request->user_id);
            $userName = $u ? $u->name : null;
            $userUsername = $u ? $u->username : null;

            $logsQuery->where(function($q) use ($request, $userName, $userUsername) {
                $q->where('user_id', $request->user_id);
                if ($userName) {
                    $q->orWhere('description', 'LIKE', "%{$userName}%")
                      ->orWhere('action', 'LIKE', "%{$userName}%");
                }
                if ($userUsername) {
                    $q->orWhere('description', 'LIKE', "%{$userUsername}%");
                }
            });
        }
        if ($request->filled('date_from')) {
            $logsQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $logsQuery->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $logsQuery->where(function($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('action', 'LIKE', "%{$search}%")
                  ->orWhere('event_type', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('username', 'LIKE', "%{$search}%");
                  });
            });
        }

        $systemLogs = $logsQuery->paginate(15, ['*'], 'logs_page')->withQueryString();

        // 3. Fetch Received Items Logs
        $receivedQuery = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->select(
                'inventory_items.*',
                'inventory_batches.entry_date',
                'inventory_batches.supplier_name',
                'inventory_batches.donor_name',
                'inventory_batches.delivery_person',
                'inventory_batches.delivery_phone',
                'inventory_batches.ledge_category',
                'inventory_batches.acquisition_type'
            )
            ->orderBy('inventory_batches.entry_date', 'desc');

        if ($request->filled('date_from')) {
            $receivedQuery->whereDate('inventory_batches.entry_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $receivedQuery->whereDate('inventory_batches.entry_date', '<=', $request->date_to);
        }
        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $receivedQuery->where(function($q) use ($search) {
                $q->where('inventory_items.description', 'LIKE', "%{$search}%")
                  ->orWhere('inventory_batches.id', 'LIKE', "%{$search}%")
                  ->orWhere('inventory_batches.supplier_name', 'LIKE', "%{$search}%")
                  ->orWhere('inventory_batches.donor_name', 'LIKE', "%{$search}%")
                  ->orWhere('inventory_batches.delivery_person', 'LIKE', "%{$search}%");
            });
        }
        if ($request->filled('user_id')) {
            $receivedQuery->where(function($q) use ($request) {
                $q->where('inventory_batches.recorded_by', $request->user_id)
                  ->orWhere('inventory_batches.approved_by', $request->user_id)
                  ->orWhere('inventory_batches.stores_approved_by', $request->user_id)
                  ->orWhere('inventory_batches.admin_approved_by', $request->user_id)
                  ->orWhere('inventory_batches.auditor_approved_by', $request->user_id);
            });
        }

        $receivedItems = $receivedQuery->paginate(15, ['*'], 'received_page')->withQueryString();

        // 4. Fetch Issued Items Logs
        $issuedQuery = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->leftJoin('store_requisitions', 'issuances.requisition_id', '=', 'store_requisitions.id')
            ->leftJoin('users as processors', 'store_requisitions.processed_by', '=', 'processors.id')
            ->leftJoin('users as officers', 'store_requisitions.collected_by', '=', 'officers.id')
            ->select(
                'issued_items.*',
                'issuances.issuance_date',
                'issuances.beneficiary',
                'issuances.authority',
                'issuances.issuance_type',
                'issuances.requisition_id',
                'store_requisitions.origin_approved_by',
                'store_requisitions.stores_approved_by',
                'store_requisitions.dg_approved_by',
                'processors.name as final_approved_by',
                'officers.name as store_officer_name'
            )
            ->selectRaw('(SELECT COALESCE(SUM(returned_qty), 0) FROM returned_items WHERE returned_items.issued_item_id = issued_items.id) as total_returned')
            ->orderBy('issuances.issuance_date', 'desc');

        if ($request->filled('date_from')) {
            $issuedQuery->whereDate('issuances.issuance_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $issuedQuery->whereDate('issuances.issuance_date', '<=', $request->date_to);
        }
        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $issuedQuery->where(function($q) use ($search) {
                $q->where('issued_items.description', 'LIKE', "%{$search}%")
                  ->orWhere('issuances.beneficiary', 'LIKE', "%{$search}%")
                  ->orWhere('issuances.authority', 'LIKE', "%{$search}%")
                  ->orWhere('issuances.issuance_type', 'LIKE', "%{$search}%");
            });
        }
        if ($request->filled('user_id')) {
            $issuedQuery->where(function($q) use ($request) {
                $q->where('store_requisitions.user_id', $request->user_id)
                  ->orWhere('store_requisitions.processed_by', $request->user_id)
                  ->orWhere('store_requisitions.collected_by', $request->user_id)
                  ->orWhere('store_requisitions.origin_approved_by', $request->user_id)
                  ->orWhere('store_requisitions.stores_approved_by', $request->user_id)
                  ->orWhere('store_requisitions.dg_approved_by', $request->user_id);
            });
        }

        $issuedItems = $issuedQuery->paginate(15, ['*'], 'issued_page')->withQueryString();

        // 5. Fetch Returned Items Logs
        $returnedQuery = ReturnedItem::join('issued_items', 'returned_items.issued_item_id', '=', 'issued_items.id')
            ->join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->select(
                'returned_items.*',
                'issued_items.description',
                'issued_items.ledge_category',
                'issuances.beneficiary'
            )
            ->orderBy('returned_items.return_date', 'desc');

        if ($request->filled('date_from')) {
            $returnedQuery->whereDate('returned_items.return_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $returnedQuery->whereDate('returned_items.return_date', '<=', $request->date_to);
        }
        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $returnedQuery->where(function($q) use ($search) {
                $q->where('issued_items.description', 'LIKE', "%{$search}%")
                  ->orWhere('issuances.beneficiary', 'LIKE', "%{$search}%")
                  ->orWhere('returned_items.remarks', 'LIKE', "%{$search}%");
            });
        }
        if ($request->filled('user_id')) {
            $returnedQuery->where(function($q) use ($request) {
                $q->where('returned_items.returned_by', $request->user_id)
                  ->orWhere('returned_items.received_by', $request->user_id);
            });
        }

        $returnedItems = $returnedQuery->paginate(15, ['*'], 'returned_page')->withQueryString();

        // 6. Fetch Requisitions Logs
        $requisitionsQuery = StoreRequisition::with(['requester'])->orderBy('created_at', 'desc');

        if ($request->filled('date_from')) {
            $requisitionsQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $requisitionsQuery->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $requisitionsQuery->where(function($q) use ($search) {
                $q->where('purpose', 'LIKE', "%{$search}%")
                  ->orWhere('id', 'LIKE', "%{$search}%")
                  ->orWhere('requester_name', 'LIKE', "%{$search}%")
                  ->orWhere('department', 'LIKE', "%{$search}%")
                  ->orWhere('status', 'LIKE', "%{$search}%");
            });
        }
        if ($request->filled('user_id')) {
            $requisitionsQuery->where(function($q) use ($request) {
                $q->where('user_id', $request->user_id)
                  ->orWhere('processed_by', $request->user_id)
                  ->orWhere('collected_by', $request->user_id)
                  ->orWhere('origin_approved_by', $request->user_id)
                  ->orWhere('stores_approved_by', $request->user_id)
                  ->orWhere('dg_approved_by', $request->user_id);
            });
        }

        $requisitions = $requisitionsQuery->paginate(15, ['*'], 'requisitions_page')->withQueryString();

        $ledgeMap = Setting::getCategories();
        $auditUsers = User::orderBy('name')->get();

        if ($request->input('format') === 'json' || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'total_logs'        => number_format(SystemLog::count()),
                'total_variance'    => number_format($totalVariance),
                'active_loans'      => number_format($activeLoansCount),
                'tabs' => [
                    'audit_trail'    => [
                        'tbody' => view('auditor._tab_audit_trail', compact('systemLogs'))->render(),
                        'pager' => view('auditor._tab_pager', ['items' => $systemLogs, 'param' => 'logs_page'])->render(),
                        'total' => $systemLogs->total(),
                    ],
                    'received_items' => [
                        'tbody' => view('auditor._tab_received_items', compact('receivedItems', 'ledgeMap'))->render(),
                        'pager' => view('auditor._tab_pager', ['items' => $receivedItems, 'param' => 'received_page'])->render(),
                        'total' => $receivedItems->total(),
                    ],
                    'issued_items'   => [
                        'tbody' => view('auditor._tab_issued_items', compact('issuedItems', 'ledgeMap'))->render(),
                        'pager' => view('auditor._tab_pager', ['items' => $issuedItems, 'param' => 'issued_page'])->render(),
                        'total' => $issuedItems->total(),
                    ],
                    'returned_items' => [
                        'tbody' => view('auditor._tab_returned_items', compact('returnedItems', 'ledgeMap'))->render(),
                        'pager' => view('auditor._tab_pager', ['items' => $returnedItems, 'param' => 'returned_page'])->render(),
                        'total' => $returnedItems->total(),
                    ],
                    'requisitions'   => [
                        'tbody' => view('auditor._tab_requisitions', compact('requisitions'))->render(),
                        'pager' => view('auditor._tab_pager', ['items' => $requisitions, 'param' => 'requisitions_page'])->render(),
                        'total' => $requisitions->total(),
                    ],
                ],
            ]);
        }

        return view('auditor.external', compact(
            'totalLogsCount',
            'totalVariance',
            'activeLoansCount',
            'systemLogs',
            'receivedItems',
            'issuedItems',
            'returnedItems',
            'requisitions',
            'ledgeMap',
            'auditUsers'
        ));
    }
}
