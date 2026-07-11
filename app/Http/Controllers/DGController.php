<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use App\Models\InventoryItem;
use App\Models\IssuedItem;
use App\Models\ReturnedItem;
use App\Models\StoreRequisition;
use App\Models\User;
use App\Models\Setting;
use App\Models\Message;
use Illuminate\Http\Request;

class DGController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'Director General') {
            abort(403, 'Access Restricted: Director General clearance required.');
        }

        // Summary statistics
        $totalItemsCount = InventoryItem::count();
        $totalVariance = InventoryItem::sum('variance');
        $activeLoansCount = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->where('issuances.issuance_type', 'Temporary')
            ->where('issued_items.quantity', '>', 0)
            ->count();
        $pendingRequisitionsCount = StoreRequisition::where('status', 'pending')->count();
        $totalActiveUsers = User::where('registration_status', 'approved')->where('is_active', true)->count();

        // 2. Stock Oversight & Registry (Inventory Items)
        $itemsQuery = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->where('inventory_batches.approval_status', '=', 'approved')
            ->select(
                'inventory_items.*',
                'inventory_batches.entry_date',
                'inventory_batches.supplier_name',
                'inventory_batches.donor_name',
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

        // 3. Staff Requisitions (All requisitions & status checks)
        $reqsQuery = StoreRequisition::with(['items', 'requester'])->orderBy('created_at', 'desc');
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
            'totalItemsCount',
            'totalVariance',
            'activeLoansCount',
            'pendingRequisitionsCount',
            'totalActiveUsers',
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

        // Fetch records with filters for the executive printable report
        $receivedQuery = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->where('inventory_batches.approval_status', '=', 'approved')
            ->select(
                'inventory_items.*',
                'inventory_batches.entry_date',
                'inventory_batches.supplier_name',
                'inventory_batches.donor_name',
                'inventory_batches.ledge_category',
                'inventory_batches.acquisition_type'
            )
            ->orderBy('inventory_batches.entry_date', 'desc');
        $reqsQuery = StoreRequisition::with('requester')->orderBy('created_at', 'desc');
        $usersQuery = User::where('registration_status', 'approved')->orderBy('name', 'asc');

        if ($request->filled('date_from')) {
            $from = $request->date_from;
            $receivedQuery->whereDate('inventory_batches.entry_date', '>=', $from);
            $reqsQuery->whereDate('created_at', '>=', $from);
        }
        if ($request->filled('date_to')) {
            $to = $request->date_to;
            $receivedQuery->whereDate('inventory_batches.entry_date', '<=', $to);
            $reqsQuery->whereDate('created_at', '<=', $to);
        }

        $receivedItems = $receivedQuery->limit(200)->get();
        $requisitions = $reqsQuery->limit(200)->get();
        $users = $usersQuery->limit(200)->get();

        $ledgeMap = Setting::getCategories();
        $dg = auth()->user();

        return view('dg.print', compact(
            'receivedItems',
            'requisitions',
            'users',
            'ledgeMap',
            'dg'
        ));
    }

    public function processRequisition(Request $request, $id)
    {
        if (auth()->user()->role !== 'Director General') {
            abort(403, 'Access Restricted: Director General clearance required.');
        }

        $request->validate([
            'status' => 'required|in:approved,declined',
            'decline_reason' => 'required_if:status,declined|nullable|string|max:2000',
        ]);

        $req = StoreRequisition::findOrFail($id);
        if (!$req->requires_dg_approval) {
            return response()->json(['success' => false, 'message' => 'This requisition does not require DG approval.'], 400);
        }
        if ($req->dg_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Requisition has already been processed by DG.'], 400);
        }
        if (($req->origin_admin_status ?? 'pending') !== 'approved') {
            return response()->json(['success' => false, 'message' => 'Requisition must be approved by Department Head first.'], 400);
        }

        if ($request->status === 'approved') {
            $req->dg_status = 'approved';
            $req->dg_approved_by = auth()->user()->name;
            $req->dg_approved_at = now();
            $req->save();

            // Log
            SystemLog::create([
                'user_id' => auth()->id(),
                'event_type' => 'REQUISITION',
                'action' => 'DG_APPROVE',
                'description' => "Director General " . auth()->user()->name . " approved store requisition #{$req->id} from department: {$req->department}.",
                'severity' => 'info',
                'metadata' => ['requisition_id' => $req->id],
                'ip_address' => $request->ip(),
            ]);

            // Notify Head of Stores (Admin)
            $admins = User::getApproversQuery()->where('is_active', true)->get();
            foreach ($admins as $admin) {
                $priorityLabel = strtoupper($req->priority);
                $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #10b981;border-radius:12px;background:rgba(16,185,129,0.05);'>";
                $msg .= "<b style='color:#10b981;'>✅ DG APPROVED REQUISITION — {$priorityLabel} PRIORITY</b><br><br>";
                $msg .= "Director General <b>" . auth()->user()->name . "</b> has approved store requisition Ref: #<b>{$req->id}</b>.<br><br>";
                $msg .= "This requisition has been cleared for final processing and stock checkout.<br><br>";
                $msg .= "<b>Department:</b> {$req->department}<br>";
                $msg .= "<b>Requested by:</b> {$req->requester_name}<br>";
                $msg .= "<b>Purpose:</b> " . e($req->purpose) . "<br><br>";
                $msg .= "<a href='" . route('admin.requisitions') . "?open_id={$req->id}' style='display:inline-block;background:#10b981;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Perform Final Head Review</a>";
                $msg .= "</div>";

                Message::create([
                    'sender_id' => auth()->id(),
                    'receiver_id' => $admin->id,
                    'message' => $msg,
                    'is_automated' => true,
                ]);
            }

            // Notify HOD
            $hods = User::where('role', 'Department Head')->where('department', $req->department)->where('is_active', true)->get();
            foreach ($hods as $hod) {
                $msg = "<div class='personnel-view requisition-status-msg' style='padding:15px;border:1px solid #10b981;border-radius:12px;background:rgba(16,185,129,0.05);'>";
                $msg .= "<b style='color:#10b981;'>📋 DG APPROVED REQUISITION</b><br><br>";
                $msg .= "Director General has approved requisition Ref: #<b>{$req->id}</b>.<br><br>";
                $msg .= "</div>";
                Message::create([
                    'sender_id' => auth()->id(),
                    'receiver_id' => $hod->id,
                    'message' => $msg,
                    'is_automated' => true,
                ]);
            }

            $message = "Requisition #{$req->id} approved successfully.";
        } else {
            $req->dg_status = 'declined';
            $req->status = 'declined';
            $req->dg_decline_reason = $request->decline_reason;
            $req->dg_approved_by = auth()->user()->name;
            $req->dg_approved_at = now();
            $req->save();

            // Log
            SystemLog::create([
                'user_id' => auth()->id(),
                'event_type' => 'REQUISITION',
                'action' => 'DG_DECLINE',
                'description' => "Director General " . auth()->user()->name . " declined store requisition #{$req->id} from department: {$req->department}.",
                'severity' => 'warning',
                'metadata' => ['requisition_id' => $req->id],
                'ip_address' => $request->ip(),
            ]);

            // Notify Requester
            if ($req->requested_by) {
                $msg  = "<div class='personnel-view requisition-status-msg' style='padding:15px;border:1px solid #ef4444;border-radius:12px;background:rgba(239,68,68,0.02);'>";
                $msg .= "<b style='color:#ef4444;'>📋 REQUISITION DECLINED BY DIRECTOR GENERAL</b><br><br>";
                $msg .= "Your store requisition (Ref: #{$req->id}) from <b>{$req->department}</b> has been <b>DECLINED</b> by the Director General.<br><br>";
                if ($request->decline_reason) {
                    $msg .= "<div style='margin-top:8px;padding:10px 14px;background:rgba(239,68,68,0.05);border:1px solid rgba(239,68,68,0.2);border-radius:10px;font-size:0.85rem;color:#7f1d1d;'><b>Reason for Decline:</b> " . e($request->decline_reason) . "</div><br>";
                }
                $msg .= "<a href='" . route('requisitions.index') . "' style='display:inline-block;background:#ef4444;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>View My Requisitions</a>";
                $msg .= "</div>";

                Message::create([
                    'sender_id' => auth()->id(),
                    'receiver_id' => $req->requested_by,
                    'message' => $msg,
                    'is_automated' => true,
                ]);
            }

            // Notify Stores Head and HOD
            $recipients = User::where(function($q) use ($req) {
                $q->where('role', 'Main Admin')
                  ->orWhere(fn($sq) => $sq->where('role', 'Department Head')->where('department', $req->department));
            })->where('is_active', true)->get();

            foreach ($recipients as $recipient) {
                $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #ef4444;border-radius:12px;background:rgba(239,68,68,0.05);'>";
                $msg .= "<b style='color:#ef4444;'>❌ DG DECLINED REQUISITION — Ref: #{$req->id}</b><br><br>";
                $msg .= "Director General has <b>DECLINED</b> store requisition Ref: #<b>{$req->id}</b>.<br><br>";
                if ($request->decline_reason) {
                    $msg .= "<b>Reason:</b> " . e($request->decline_reason) . "<br><br>";
                }
                $msg .= "</div>";

                Message::create([
                    'sender_id' => auth()->id(),
                    'receiver_id' => $recipient->id,
                    'message' => $msg,
                    'is_automated' => true,
                ]);
            }

            $message = "Requisition #{$req->id} declined successfully.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}
