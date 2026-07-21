<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use App\Models\InventoryItem;
use App\Models\IssuedItem;
use App\Models\ReturnedItem;
use App\Models\InventoryBatch;
use App\Models\ServiceSra;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuditorController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'Auditor') {
            abort(403, 'Access Restricted: Auditor clearance required.');
        }
        // 1. Gather Summary Statistics
        $totalLogsCount = SystemLog::count();
        $totalVariance  = InventoryItem::sum('variance');
        
        $activeLoansCount = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->where('issuances.issuance_type', 'Temporary')
            ->where('issued_items.quantity', '>', 0)
            ->count();

        // 2. Fetch Audit Trail (System Logs) with filters
        $logsQuery = SystemLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('log_severity')) {
            $logsQuery->where('severity', $request->log_severity);
        }
        if ($request->filled('log_event')) {
            $logsQuery->where('event_type', $request->log_event);
        }
        if ($request->filled('user_id')) {
            $logsQuery->where('user_id', $request->user_id);
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
            $receivedQuery->where('inventory_items.description', 'LIKE', "%{$search}%");
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
            $issuedQuery->where('issued_items.description', 'LIKE', "%{$search}%")
                ->orWhere('issuances.beneficiary', 'LIKE', "%{$search}%");
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
            $returnedQuery->where('issued_items.description', 'LIKE', "%{$search}%")
                ->orWhere('issuances.beneficiary', 'LIKE', "%{$search}%");
        }

        $returnedItems = $returnedQuery->paginate(15, ['*'], 'returned_page')->withQueryString();

        // 6. Fetch Requisitions Logs
        $requisitionsQuery = \App\Models\StoreRequisition::with(['requester'])->orderBy('created_at', 'desc');

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
                  ->orWhere('department', 'LIKE', "%{$search}%");
            });
        }

        $requisitions = $requisitionsQuery->paginate(15, ['*'], 'requisitions_page')->withQueryString();

        $ledgeMap = Setting::getCategories();
        $auditUsers = \App\Models\User::where('role', '!=', 'Auditor')->orderBy('name')->get();

        $pendingSras = InventoryBatch::with('storesApprover')
            ->where('approval_status', 'pending_auditor_admin')
            ->where('auditor_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingServiceSras = ServiceSra::with('submitter')
            ->where('auditor_status', 'pending')
            ->whereNotIn('status', ['approved', 'declined'])
            ->orderBy('created_at', 'desc')
            ->get();

         $pendingStaffRegistrationsCount = \App\Models\User::where(function($q) {
                $q->where('department', auth()->user()->department)
                  ->orWhere('sponsored_by', auth()->id());
            })
            ->where('registration_status', 'pending_hod')
            ->where('role', 'Requisitioner')
            ->count();

        $departmentRequisitions = \App\Models\StoreRequisition::where(function($q) {
                $q->where('department', auth()->user()->department)
                  ->orWhereHas('requester', function($sq) {
                      $sq->where('sponsored_by', auth()->id());
                  });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'dept_reqs_page')
            ->withQueryString();

        if ($request->input('format') === 'json' && $request->ajax()) {
            return response()->json([
                'pending_count'     => $pendingSras->count() + $pendingServiceSras->count(),
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
                    'pending_sra'    => [
                        'tbody' => view('auditor._tab_pending_sra', compact('pendingSras', 'pendingServiceSras'))->render(),
                        'total' => $pendingSras->count() + $pendingServiceSras->count(),
                    ],
                ],
            ]);
        }

        return view('auditor.index', compact(
            'totalLogsCount',
            'totalVariance',
            'activeLoansCount',
            'systemLogs',
            'receivedItems',
            'issuedItems',
            'returnedItems',
            'requisitions',
            'departmentRequisitions',
            'ledgeMap',
            'auditUsers',
            'pendingSras',
            'pendingServiceSras',
            'pendingStaffRegistrationsCount'
        ));
    }

    public function printReport(Request $request)
    {
        if (auth()->user()->role !== 'Auditor') {
            abort(403, 'Access Restricted: Auditor clearance required.');
        }

        // Fetch logs and transactions with filters for the printable ledger
        $logsQuery = SystemLog::with('user')->orderBy('created_at', 'desc');
        $receivedQuery = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->select('inventory_items.*', 'inventory_batches.entry_date', 'inventory_batches.supplier_name', 'inventory_batches.ledge_category')
            ->orderBy('inventory_batches.entry_date', 'desc');
         $issuedQuery = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->leftJoin('store_requisitions', 'issuances.requisition_id', '=', 'store_requisitions.id')
            ->leftJoin('users as processors', 'store_requisitions.processed_by', '=', 'processors.id')
            ->leftJoin('users as officers', 'store_requisitions.collected_by', '=', 'officers.id')
            ->select(
                'issued_items.*', 
                'issuances.issuance_date', 
                'issuances.beneficiary', 
                'issuances.issuance_type', 
                'issuances.authority',
                'store_requisitions.origin_approved_by',
                'store_requisitions.stores_approved_by',
                'store_requisitions.dg_approved_by',
                'processors.name as final_approved_by',
                'officers.name as store_officer_name'
            )
            ->selectRaw('(SELECT COALESCE(SUM(returned_qty), 0) FROM returned_items WHERE returned_items.issued_item_id = issued_items.id) as total_returned')
            ->orderBy('issuances.issuance_date', 'desc');
        $returnedQuery = ReturnedItem::join('issued_items', 'returned_items.issued_item_id', '=', 'issued_items.id')
            ->join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->select('returned_items.*', 'issued_items.description', 'issuances.beneficiary')
            ->orderBy('returned_items.return_date', 'desc');

        if ($request->filled('user_id')) {
            $logsQuery->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $from = $request->date_from;
            $logsQuery->whereDate('created_at', '>=', $from);
            $receivedQuery->whereDate('inventory_batches.entry_date', '>=', $from);
            $issuedQuery->whereDate('issuances.issuance_date', '>=', $from);
            $returnedQuery->whereDate('returned_items.return_date', '>=', $from);
        }
        if ($request->filled('date_to')) {
            $to = $request->date_to;
            $logsQuery->whereDate('created_at', '<=', $to);
            $receivedQuery->whereDate('inventory_batches.entry_date', '<=', $to);
            $issuedQuery->whereDate('issuances.issuance_date', '<=', $to);
            $returnedQuery->whereDate('returned_items.return_date', '<=', $to);
        }

        $systemLogs = $logsQuery->limit(200)->get();
        $receivedItems = $receivedQuery->limit(200)->get();
        $issuedItems = $issuedQuery->limit(200)->get();
        $returnedItems = $returnedQuery->limit(200)->get();

        $ledgeMap = Setting::getCategories();
        $auditor  = auth()->user();

        return view('auditor.print', compact(
            'systemLogs',
            'receivedItems',
            'issuedItems',
            'returnedItems',
            'ledgeMap',
            'auditor'
        ));
    }

    public function getSupplierInfo(Request $request)
    {
        if (auth()->user()->role !== 'Auditor') {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        $name = $request->query('name');
        
        $supplier = \App\Models\Supplier::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        
        $firstDelivery = \App\Models\InventoryBatch::where(function($q) use ($name) {
                $q->whereRaw('LOWER(supplier_name) = ?', [strtolower($name)])
                  ->orWhereRaw('LOWER(donor_name) = ?', [strtolower($name)]);
            })
            ->where('supplier_status', '!=', 'System Draft')
            ->min('entry_date');
            
        $lastDelivery = \App\Models\InventoryBatch::where(function($q) use ($name) {
                $q->whereRaw('LOWER(supplier_name) = ?', [strtolower($name)])
                  ->orWhereRaw('LOWER(donor_name) = ?', [strtolower($name)]);
            })
            ->where('supplier_status', '!=', 'System Draft')
            ->max('entry_date');

        $firstDeliveryFormatted = $firstDelivery ? \Carbon\Carbon::parse($firstDelivery)->format('d M Y') : 'N/A';
        $lastDeliveryFormatted = $lastDelivery ? \Carbon\Carbon::parse($lastDelivery)->format('d M Y') : 'N/A';
        
        return response()->json([
            'success' => true,
            'supplier' => $supplier,
            'first_delivery' => $firstDeliveryFormatted,
            'last_delivery' => $lastDeliveryFormatted
        ]);
    }

    public function staffApprovals(Request $request)
    {
        if (auth()->user()->role !== 'Auditor') {
            abort(403, 'Access Restricted: Auditor clearance required.');
        }

        $hasOverdueReturn = \App\Http\Controllers\TempRequisitionerController::hasOverdueReturn(auth()->user()->department);

        return view('auditor.staff_approvals', compact('hasOverdueReturn'));
    }
}
