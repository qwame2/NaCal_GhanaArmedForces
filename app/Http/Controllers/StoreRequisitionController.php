<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StoreRequisition;
use App\Models\StoreRequisitionItem;
use App\Models\InventoryItem;
use App\Models\Setting;
use App\Models\SystemLog;
use App\Models\Message;
use App\Models\User;

class StoreRequisitionController extends Controller
{
    /**
     * Personnel: Show the requisition form page.
     */
    public function index(Request $request)
    {
        $ledgeMap = Setting::getCategories();

        // Fetch all available inventory items (grouped by description, with stock > 0)
        $availableItems = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->selectRaw('TRIM(inventory_items.description) as description, MAX(inventory_items.unit) as unit, inventory_batches.ledge_category, SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
            ->groupBy(\DB::raw('TRIM(inventory_items.description)'), 'inventory_batches.ledge_category')
            ->havingRaw('SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) > 0')
            ->orderBy('inventory_items.description')
            ->get();

        // My submitted requisitions (for current user)
        $myRequisitions = StoreRequisition::with('items')
            ->where('requested_by', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('requisitions.index', compact('availableItems', 'ledgeMap', 'myRequisitions'));
    }

    /**
     * Personnel: Show the checkout page.
     */
    public function checkout(Request $request)
    {
        $ledgeMap = Setting::getCategories();
        return view('requisitions.checkout', compact('ledgeMap'));
    }

    /**
     * Personnel: Submit a new requisition.
     */
    public function store(Request $request)
    {
        $request->validate([
            'requester_name'  => 'required|string|max:255',
            'department'      => 'required|string|max:255',
            'rank_or_title'   => 'nullable|string|max:255',
            'purpose'         => 'required|string|max:1000',
            'priority'        => 'required|in:low,normal,urgent',
            'items'           => 'required|array|min:1',
            'items.*.description'        => 'required|string|max:255',
            'items.*.category'           => 'nullable|string|max:10',
            'items.*.unit'               => 'nullable|string|max:100',
            'items.*.quantity_requested' => 'required|numeric|min:0.01',
            'items.*.remarks'            => 'nullable|string|max:500',
        ]);

        $requisition = StoreRequisition::create([
            'requester_name' => $request->requester_name,
            'department'     => $request->department,
            'rank_or_title'  => $request->rank_or_title,
            'requested_by'   => auth()->id(),
            'purpose'        => $request->purpose,
            'priority'       => $request->priority,
            'status'         => 'pending',
        ]);

        foreach ($request->items as $item) {
            if (empty($item['description'])) continue;
            $requisition->items()->create([
                'description'        => $item['description'],
                'category'           => $item['category'] ?? null,
                'unit'               => $item['unit'] ?? 'units',
                'quantity_requested' => $item['quantity_requested'],
                'remarks'            => $item['remarks'] ?? null,
            ]);
        }

        // Log the action
        SystemLog::create([
            'user_id'    => auth()->id(),
            'event_type' => 'REQUISITION',
            'action'     => 'SUBMIT_REQUISITION',
            'description'=> auth()->user()->name . " submitted a store requisition from department: {$request->department}.",
            'severity'   => 'info',
            'metadata'   => ['requisition_id' => $requisition->id],
            'ip_address' => $request->ip(),
        ]);

        // Notify all admins
        $admins = User::where('is_admin', true)->where('is_active', true)->get();
        foreach ($admins as $admin) {
            $priorityLabel = strtoupper($request->priority);
            $itemCount = count($request->items);
            $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #6366f1;border-radius:12px;background:rgba(99,102,241,0.05);'>";
            $msg .= "<b style='color:#4f46e5;'>📋 NEW STORE REQUISITION — {$priorityLabel} PRIORITY</b><br><br>";
            $msg .= "Department <b>{$request->department}</b> has submitted a store requisition with <b>{$itemCount} item(s)</b>.<br><br>";
            $msg .= "<b>Requested by:</b> {$request->requester_name}<br>";
            $msg .= "<b>Purpose:</b> " . e($request->purpose) . "<br><br>";
            $msg .= "<a href='" . route('admin.requisitions') . "' style='display:inline-block;background:#4f46e5;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Review Requisition</a>";
            $msg .= "</div>";

            Message::create([
                'sender_id'    => auth()->id(),
                'receiver_id'  => $admin->id,
                'message'      => $msg,
                'is_automated' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Requisition submitted successfully. The store will review and process your request.',
            'id'      => $requisition->id,
        ]);
    }

    /**
     * Personnel: View status of their own requisitions (API).
     */
    public function myRequisitions()
    {
        $requisitions = StoreRequisition::with('items')
            ->where('requested_by', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($req) {
                return [
                    'id'             => $req->id,
                    'department'     => $req->department,
                    'requester_name' => $req->requester_name,
                    'purpose'        => $req->purpose,
                    'priority'       => $req->priority,
                    'priority_badge' => $req->priority_badge,
                    'status'         => $req->status,
                    'status_badge'   => $req->status_badge,
                    'admin_notes'    => $req->admin_notes,
                    'created_at'     => $req->created_at->format('d/m/Y H:i'),
                    'processed_at'   => $req->processed_at?->format('d/m/Y H:i'),
                    'items'          => $req->items->map(fn($i) => [
                        'description'        => $i->description,
                        'category'           => $i->category,
                        'unit'               => $i->unit,
                        'quantity_requested' => $i->quantity_requested,
                        'quantity_approved'  => $i->quantity_approved,
                        'remarks'            => $i->remarks,
                    ]),
                ];
            });

        return response()->json($requisitions);
    }

    // =====================================================================
    // ADMIN ROUTES
    // =====================================================================

    /**
     * Admin: List all requisitions.
     */
    public function adminIndex(Request $request)
    {
        if (!auth()->user()->is_admin) abort(403);

        $query = StoreRequisition::with(['items', 'requester', 'processor'])
            ->orderByRaw("FIELD(status, 'pending', 'partially_approved', 'approved', 'declined')")
            ->orderByRaw("FIELD(priority, 'urgent', 'normal', 'low')")
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('department')) {
            $query->where('department', 'LIKE', '%' . $request->department . '%');
        }

        $requisitions = $query->paginate(15)->withQueryString();
        $ledgeMap = Setting::getCategories();

        $stats = [
            'pending'            => StoreRequisition::where('status', 'pending')->count(),
            'approved'           => StoreRequisition::where('status', 'approved')->count(),
            'partially_approved' => StoreRequisition::where('status', 'partially_approved')->count(),
            'declined'           => StoreRequisition::where('status', 'declined')->count(),
            'urgent'             => StoreRequisition::where('status', 'pending')->where('priority', 'urgent')->count(),
        ];

        return view('admin.requisitions', compact('requisitions', 'ledgeMap', 'stats'));
    }

    /**
     * Admin: Get single requisition detail (API).
     */
    public function adminShow($id)
    {
        if (!auth()->user()->is_admin) abort(403);
        $req = StoreRequisition::with(['items', 'requester', 'processor'])->findOrFail($id);

        // Enrich items with current stock availability
        $items = $req->items->map(function ($item) {
            $stock = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                ->where('inventory_batches.supplier_status', '!=', 'System Draft')
                ->where(\DB::raw('TRIM(inventory_items.description)'), trim($item->description))
                ->selectRaw('SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
                ->value('total_stock') ?? 0;

            return [
                'id'                 => $item->id,
                'description'        => $item->description,
                'category'           => $item->category,
                'unit'               => $item->unit,
                'quantity_requested' => $item->quantity_requested,
                'quantity_approved'  => $item->quantity_approved,
                'remarks'            => $item->remarks,
                'current_stock'      => (float) $stock,
                'stock_sufficient'   => (float) $stock >= (float) $item->quantity_requested,
            ];
        });

        return response()->json([
            'id'             => $req->id,
            'requester_name' => $req->requester_name,
            'department'     => $req->department,
            'rank_or_title'  => $req->rank_or_title,
            'purpose'        => $req->purpose,
            'priority'       => $req->priority,
            'priority_badge' => $req->priority_badge,
            'status'         => $req->status,
            'status_badge'   => $req->status_badge,
            'admin_notes'    => $req->admin_notes,
            'created_at'     => $req->created_at->format('d M Y, H:i'),
            'processed_at'   => $req->processed_at?->format('d M Y, H:i'),
            'processor'      => $req->processor?->name,
            'items'          => $items,
        ]);
    }

    /**
     * Admin: Process (approve/decline) a requisition.
     */
    public function adminProcess(Request $request, $id)
    {
        if (!auth()->user()->is_admin) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);

        $request->validate([
            'status'      => 'required|in:approved,partially_approved,declined',
            'admin_notes' => 'nullable|string|max:1000',
            'items'       => 'nullable|array',
            'items.*.id'                => 'required|integer',
            'items.*.quantity_approved' => 'required|numeric|min:0',
        ]);

        $req = StoreRequisition::with('items')->findOrFail($id);

        // Update approved quantities per item
        if ($request->filled('items')) {
            foreach ($request->items as $itemData) {
                $reqItem = $req->items->firstWhere('id', $itemData['id']);
                if ($reqItem) {
                    $reqItem->quantity_approved = $itemData['quantity_approved'];
                    if (!empty($itemData['remarks'])) {
                        $reqItem->remarks = $itemData['remarks'];
                    }
                    $reqItem->save();
                }
            }
        }

        $req->status       = $request->status;
        $req->admin_notes  = $request->admin_notes;
        $req->processed_by = auth()->id();
        $req->processed_at = now();
        $req->save();

        // Log
        SystemLog::create([
            'user_id'    => auth()->id(),
            'event_type' => 'REQUISITION',
            'action'     => 'PROCESS_REQUISITION',
            'description'=> "Administrator " . auth()->user()->name . " {$request->status} store requisition #{$req->id} from {$req->department}.",
            'severity'   => 'info',
            'metadata'   => ['requisition_id' => $req->id],
            'ip_address' => $request->ip(),
        ]);

        // Notify the requester
        if ($req->requested_by) {
            $statusLabels = [
                'approved'           => ['label' => 'APPROVED', 'color' => '#10b981'],
                'partially_approved' => ['label' => 'PARTIALLY APPROVED', 'color' => '#f59e0b'],
                'declined'           => ['label' => 'DECLINED',  'color' => '#ef4444'],
            ];
            $sl = $statusLabels[$request->status];
            $msg  = "<div class='personnel-view requisition-status-msg' style='padding:15px;border:1px solid {$sl['color']};border-radius:12px;background:rgba(0,0,0,0.02);'>";
            $msg .= "<b style='color:{$sl['color']};'>📋 REQUISITION {$sl['label']}</b><br><br>";
            $msg .= "Your store requisition (Ref: #{$req->id}) from <b>{$req->department}</b> has been <b>{$sl['label']}</b> by the Store Officer.<br><br>";
            if ($request->admin_notes) {
                $msg .= "<b>Store Notes:</b> " . e($request->admin_notes) . "<br><br>";
            }
            $msg .= "<a href='" . route('requisitions.index') . "' style='display:inline-block;background:{$sl['color']};color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>View My Requisitions</a>";
            $msg .= "</div>";

            Message::create([
                'sender_id'    => auth()->id(),
                'receiver_id'  => $req->requested_by,
                'message'      => $msg,
                'is_automated' => true,
            ]);
        }

        $labels = [
            'approved'           => 'approved',
            'partially_approved' => 'partially approved',
            'declined'           => 'declined',
        ];

        return response()->json([
            'success' => true,
            'message' => "Requisition #{$req->id} has been {$labels[$request->status]}.",
        ]);
    }
}
