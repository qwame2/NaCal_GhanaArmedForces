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

        // Fetch all available inventory items (grouped by description)
        $availableItems = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->selectRaw('TRIM(inventory_items.description) as description, MAX(inventory_items.unit) as unit, inventory_batches.ledge_category, SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
            ->groupBy(\DB::raw('TRIM(inventory_items.description)'), 'inventory_batches.ledge_category')
            ->orderByRaw('TRIM(inventory_items.description)')
            ->get()
            ->map(function ($item) {
                $physicalStock = (float) $item->total_stock;
                $item->total_stock = \App\Models\Setting::getAvailableStock($item->description, $physicalStock, $item->ledge_category);
                return $item;
            });

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
            'usage_type'      => 'required|in:permanent,temporary',
            'items'           => 'required|array|min:1',
            'items.*.description'        => 'required|string|max:255',
            'items.*.category'           => 'nullable|string|max:10',
            'items.*.unit'               => 'nullable|string|max:100',
            'items.*.quantity_requested' => 'required|numeric|min:0.01',
            'items.*.remarks'            => 'nullable|string|max:500',
        ]);

        $isStoresDept = (strcasecmp($request->department, 'Stores') === 0 || strcasecmp($request->department, 'Store') === 0);
        $requisition = StoreRequisition::create([
            'requester_name' => $request->requester_name,
            'department'     => $request->department,
            'rank_or_title'  => $request->rank_or_title,
            'requested_by'   => auth()->id(),
            'purpose'        => $request->purpose,
            'priority'       => $request->priority,
            'status'         => 'pending',
            'usage_type'     => $request->usage_type,
            'origin_admin_status' => $isStoresDept ? 'approved' : 'pending',
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

        // Notify appropriate approvers based on workflow stage
        $priorityLabel = strtoupper($request->priority);
        $itemCount = count($request->items);
        $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #6366f1;border-radius:12px;background:rgba(99,102,241,0.05);'>";
        $msg .= "<b style='color:#4f46e5;'>📋 NEW STORE REQUISITION — {$priorityLabel} PRIORITY</b><br><br>";
        $msg .= "Department <b>{$request->department}</b> has submitted a store requisition with <b>{$itemCount} item(s)</b>.<br><br>";
        $msg .= "<b>Requested by:</b> {$request->requester_name}<br>";
        $msg .= "<b>Purpose:</b> " . e($request->purpose) . "<br><br>";
        $msg .= "<a href='" . route('main-admin.requisitions') . "' style='display:inline-block;background:#4f46e5;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Review Requisition</a>";
        $msg .= "</div>";

        $notified = false;
        if (!$isStoresDept) {
            $deptHeads = User::whereIn('role', ['Main Admin', 'Department Head'])
                ->where('department', $request->department)
                ->where('is_active', true)
                ->get();
            if ($deptHeads->isNotEmpty()) {
                foreach ($deptHeads as $head) {
                    Message::create([
                        'sender_id'    => auth()->id(),
                        'receiver_id'  => $head->id,
                        'message'      => $msg,
                        'is_automated' => true,
                    ]);
                }
                $notified = true;
            }
        }

        if (!$notified) {
            // Notify Stores Department Heads and Admins
            $storesHeads = User::whereIn('role', ['Main Admin', 'Department Head'])
                ->where(fn($q) => $q->where('department', 'Stores')->orWhere('department', 'Store'))
                ->where('is_active', true)
                ->get();
            $admins = User::where('is_admin', true)->where('is_active', true)->get();
            $recipients = $storesHeads->concat($admins)->unique('id');

            foreach ($recipients as $recipient) {
                // Link for admins goes to admin.requisitions; link for stores head goes to main-admin.requisitions
                $link = $recipient->is_admin ? route('admin.requisitions') : route('main-admin.requisitions');
                $customMsg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #6366f1;border-radius:12px;background:rgba(99,102,241,0.05);'>";
                $customMsg .= "<b style='color:#4f46e5;'>📋 NEW STORE REQUISITION — {$priorityLabel} PRIORITY</b><br><br>";
                $customMsg .= "Department <b>{$request->department}</b> has submitted a store requisition with <b>{$itemCount} item(s)</b>.<br><br>";
                $customMsg .= "<b>Requested by:</b> {$request->requester_name}<br>";
                $customMsg .= "<b>Purpose:</b> " . e($request->purpose) . "<br><br>";
                $customMsg .= "<a href='" . $link . "' style='display:inline-block;background:#4f46e5;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Review Requisition</a>";
                $customMsg .= "</div>";

                Message::create([
                    'sender_id'    => auth()->id(),
                    'receiver_id'  => $recipient->id,
                    'message'      => $customMsg,
                    'is_automated' => true,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Requisition submitted successfully. The department head/store will review and process your request.',
            'id'      => $requisition->id,
        ]);
    }

    /**
     * Personnel: View status of requisitions (API).
     */
    public function myRequisitions()
    {
        $query = StoreRequisition::with(['items', 'requester', 'collector']);

        // Requisitioners see only their own. Personnel see all.
        if (auth()->user()->role === 'Requisitioner') {
            $query->where('requested_by', auth()->id());
        }

        $requisitions = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($req) {
                return [
                    'id'             => $req->id,
                    'unique_id'      => $req->unique_id,
                    'department'     => $req->department,
                    'requester_name' => $req->requester_name,
                    'requester_phone'=> $req->requester?->phone ?? 'N/A',
                    'requester_email'=> $req->requester?->email ?? 'N/A',
                    'purpose'        => $req->purpose,
                    'priority'       => $req->priority,
                    'priority_badge' => $req->priority_badge,
                    'status'         => $req->status,
                    'status_badge'   => $req->status_badge,
                    'usage_type'     => $req->usage_type,
                    'usage_type_badge' => $req->usage_type_badge,
                    'admin_notes'    => $req->admin_notes,
                    'decline_reason' => $req->decline_reason,
                    'created_at'     => $req->created_at->format('d/m/y H:i'),
                    'processed_at'   => $req->processed_at?->format('d/m/y H:i'),
                    'collected_at'   => $req->collected_at?->format('d/m/y H:i'),
                    'collected_by_name' => $req->collector?->name,
                    'collector_name' => $req->collector_name,
                    'collector_contact' => $req->collector_contact,
                    'items'          => $req->items->map(fn($i) => [
                        'description'        => $i->description,
                        'category'           => $i->category,
                        'unit'               => $i->unit,
                        'quantity_requested' => $i->quantity_requested,
                        'quantity_approved'  => $i->quantity_approved,
                        'alternative_description'       => $i->alternative_description,
                        'alternative_quantity_approved' => $i->alternative_quantity_approved !== null ? (float)$i->alternative_quantity_approved : null,
                        'remarks'            => $i->remarks,
                    ]),
                ];
            });

        return response()->json($requisitions);
    }

    public function collect(Request $request, $id)
    {
        if (auth()->user()->role !== 'Officer') {
            return response()->json(['success' => false, 'message' => 'Only Store Officers are authorized to click the collection button and confirm physical collection.'], 403);
        }

        $request->validate([
            'collector_name' => 'required|string|max:255',
            'collector_contact' => 'required|string|max:100',
        ]);

        $req = StoreRequisition::findOrFail($id);

        if ($req->main_admin_status !== 'approved') {
            return response()->json(['success' => false, 'message' => 'No physical collection can be made: This requisition must be verified and approved by the Department Head first.'], 400);
        }

        if (!in_array($req->status, ['approved', 'partially_approved'])) {
            return response()->json(['success' => false, 'message' => 'No physical collection can be made: The Head of Stores must confirm and approve this requisition first.'], 400);
        }

        if ($req->collected_at) {
            return response()->json(['success' => false, 'message' => 'This requisition has already been marked as collected.'], 400);
        }

        $req->collected_at = now();
        $req->collected_by = auth()->id();
        $req->collector_name = $request->collector_name;
        $req->collector_contact = $request->collector_contact;
        $req->save();

        // Create an Issuance and IssuedItems to represent this given out/collected asset
        if (!\Illuminate\Support\Facades\Schema::hasColumn('issuances', 'requisition_id')) {
            try {
                \Illuminate\Support\Facades\Schema::table('issuances', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->unsignedBigInteger('requisition_id')->nullable();
                });
            } catch (\Exception $e) {
                // Ignore if already created
            }
        }

        $issuance = \App\Models\Issuance::create([
            'issuance_date' => $req->collected_at->format('Y-m-d'),
            'beneficiary' => $req->department . ' (' . $req->requester_name . ')',
            'authority' => $req->processor?->name ?? 'Requisition Approved',
            'issuance_type' => $req->usage_type === 'temporary' ? 'Temporary' : 'Permanent',
            'requisition_id' => $req->id,
        ]);

        foreach ($req->items as $item) {
            if ($item->quantity_approved > 0) {
                \App\Models\IssuedItem::create([
                    'issuance_id' => $issuance->id,
                    'description' => $item->description,
                    'ledge_category' => $item->category ?? 'A',
                    'quantity' => $item->quantity_approved,
                    'unit' => $item->unit,
                ]);
            }
            if ($item->alternative_quantity_approved > 0 && !empty($item->alternative_description)) {
                \App\Models\IssuedItem::create([
                    'issuance_id' => $issuance->id,
                    'description' => $item->alternative_description,
                    'ledge_category' => $item->category ?? 'A',
                    'quantity' => $item->alternative_quantity_approved,
                    'unit' => $item->unit,
                ]);
            }
        }

        SystemLog::create([
            'user_id'    => auth()->id(),
            'event_type' => 'REQUISITION',
            'action'     => 'COLLECT_REQUISITION',
            'description'=> auth()->user()->name . " confirmed physical collection of store requisition #{$req->id} by {$req->collector_name} ({$req->collector_contact}).",
            'severity'   => 'info',
            'metadata'   => ['requisition_id' => $req->id],
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Physical collection confirmed successfully.',
        ]);
    }

    /**
     * Personnel: Send follow-up reminder for a pending requisition.
     */
    public function followUp(Request $request, $id)
    {
        $req = StoreRequisition::findOrFail($id);

        if ($req->requested_by !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to follow up on this requisition.'], 403);
        }

        if ($req->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'You can only follow up on pending requisitions.'], 400);
        }

        // Limit follow-ups to once every 5 minutes to prevent spam
        $cacheKey = 'requisition_followup_' . $req->id;
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return response()->json([
                'success' => false, 
                'message' => 'You have recently sent a follow-up reminder for this requisition. Please wait a few minutes before trying again.'
            ], 429);
        }

        // Log the action
        SystemLog::create([
            'user_id'    => auth()->id(),
            'event_type' => 'REQUISITION',
            'action'     => 'FOLLOW_UP_REQUISITION',
            'description'=> auth()->user()->name . " sent a follow-up reminder for pending store requisition #{$req->id}.",
            'severity'   => 'info',
            'metadata'   => ['requisition_id' => $req->id],
            'ip_address' => $request->ip(),
        ]);

        // Notify all active admins
        $admins = User::where('is_admin', true)->where('is_active', true)->get();
        foreach ($admins as $admin) {
            $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #f59e0b;border-radius:12px;background:rgba(245,158,11,0.05);'>";
            $msg .= "<b style='color:#ea580c;'>🔔 REQUISITION FOLLOW-UP REMINDER — Ref: #{$req->id}</b><br><br>";
            $msg .= "Requisitioner <b>" . e($req->requester_name) . "</b> has sent a follow-up reminder regarding their pending requisition from department: <b>" . e($req->department) . "</b>.<br><br>";
            $msg .= "<b>Purpose:</b> " . e($req->purpose) . "<br><br>";
            $msg .= "<a href='" . route('admin.requisitions') . "' style='display:inline-block;background:#f59e0b;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Review Requisition Now</a>";
            $msg .= "</div>";

            Message::create([
                'sender_id'    => auth()->id(),
                'receiver_id'  => $admin->id,
                'message'      => $msg,
                'is_automated' => true,
            ]);
        }

        // Store in cache for 5 minutes (300 seconds)
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, 300);

        return response()->json([
            'success' => true,
            'message' => 'Follow-up reminder sent successfully to the store administrators.',
        ]);
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

        $query = StoreRequisition::with(['items', 'requester', 'processor', 'collector'])
            ->where(function($q) {
                $q->where('status', '!=', 'pending')
                  ->orWhere('main_admin_status', 'approved');
            })
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
        if ($request->filled('search_id')) {
            $search = trim($request->input('search_id'));
            $query->where(function($q) use ($search) {
                $parsedId = preg_replace('/[^0-9]/', '', $search);
                if (!empty($parsedId)) {
                    $q->where('id', $parsedId);
                }
                $q->orWhereHas('items', function($ki) use ($search) {
                    $ki->where('description', 'LIKE', '%' . $search . '%');
                });
            });
        }

        $requisitions = $query->paginate(5)->withQueryString();
        $ledgeMap = Setting::getCategories();

        $stats = [
            'pending'            => StoreRequisition::where('status', 'pending')->where('main_admin_status', 'approved')->count(),
            'approved'           => StoreRequisition::where('status', 'approved')->count(),
            'partially_approved' => StoreRequisition::where('status', 'partially_approved')->count(),
            'declined'           => StoreRequisition::where('status', 'declined')->count(),
            'urgent'             => StoreRequisition::where('status', 'pending')->where('main_admin_status', 'approved')->where('priority', 'urgent')->count(),
        ];

        return view('admin.requisitions', compact('requisitions', 'ledgeMap', 'stats'));
    }

    /**
     * Admin: Get single requisition detail (API).
     */
    public function adminShow($id)
    {
        if (!auth()->user()->is_admin && auth()->user()->role === 'Requisitioner') {
            abort(403, 'Unauthorized');
        }
        $req = StoreRequisition::with(['items', 'requester', 'processor', 'collector'])->findOrFail($id);

        // Enrich items with current stock availability
        $items = $req->items->map(function ($item) {
            $stock = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                ->where('inventory_batches.supplier_status', '!=', 'System Draft')
                ->where(\DB::raw('TRIM(inventory_items.description)'), trim($item->description))
                ->selectRaw('SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
                ->value('total_stock') ?? 0;

            return [
                'id'                            => $item->id,
                'description'                   => $item->description,
                'alternative_description'       => $item->alternative_description,
                'alternative_quantity_approved' => $item->alternative_quantity_approved !== null ? (float)$item->alternative_quantity_approved : null,
                'category'                      => $item->category,
                'unit'                          => $item->unit,
                'quantity_requested'            => $item->quantity_requested,
                'quantity_approved'             => $item->quantity_approved !== null ? (float)$item->quantity_approved : null,
                'remarks'                       => $item->remarks,
                'current_stock'                 => (float) $stock,
                'stock_sufficient'              => (float) $stock >= (float) $item->quantity_requested,
            ];
        });

        // Query unique in-stock inventory items for alternatives
        $alternatives = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->selectRaw('TRIM(inventory_items.description) as description, MAX(inventory_items.unit) as unit, SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock, inventory_batches.ledge_category as category')
            ->groupBy(\DB::raw('TRIM(inventory_items.description)'), 'inventory_batches.ledge_category')
            ->orderByRaw('TRIM(inventory_items.description)')
            ->get()
            ->map(function($item) {
                $physicalStock = (float) $item->total_stock;
                $avail = \App\Models\Setting::getAvailableStock($item->description, $physicalStock, $item->category);
                return [
                    'description' => $item->description,
                    'unit'        => $item->unit,
                    'total_stock' => $avail,
                    'category'    => $item->category,
                ];
            })
            ->filter(fn($item) => $item['total_stock'] > 0)
            ->values();

        return response()->json([
            'id'             => $req->id,
            'unique_id'      => $req->unique_id,
            'requester_name' => $req->requester_name,
            'department'     => $req->department,
            'rank_or_title'  => $req->rank_or_title,
            'purpose'        => $req->purpose,
            'priority'       => $req->priority,
            'priority_badge' => $req->priority_badge,
            'status'         => $req->status,
            'status_badge'   => $req->status_badge,
            'main_admin_status' => $req->main_admin_status,
            'usage_type'     => $req->usage_type,
            'usage_type_badge' => $req->usage_type_badge,
            'admin_notes'    => $req->admin_notes,
            'decline_reason' => $req->decline_reason,
            'created_at'     => $req->created_at->format('d/m/y H:i'),
            'processed_at'   => $req->processed_at?->format('d/m/y H:i'),
            'processor'      => $req->processor?->name,
            'collected_at'   => $req->collected_at?->format('d/m/y H:i'),
            'collected_by_name' => $req->collector?->name,
            'collector_name' => $req->collector_name,
            'collector_contact' => $req->collector_contact,
            'items'          => $items,
            'alternatives'   => $alternatives,
        ]);
    }

    /**
     * Admin: Process (approve/decline) a requisition.
     */
    public function adminProcess(Request $request, $id)
    {
        if (!auth()->user()->is_admin) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);

        $request->validate([
            'status'         => 'required|in:approved,partially_approved,declined',
            'admin_notes'    => 'nullable|string|max:1000',
            'decline_reason' => 'nullable|string|max:2000',
            'items'          => 'nullable|array',
            'items.*.id'                             => 'required|integer',
            'items.*.quantity_approved'              => 'required|numeric|min:0',
            'items.*.alternative_description'        => 'nullable|string|max:255',
            'items.*.alternative_quantity_approved' => 'nullable|numeric|min:0',
        ]);

        try {
            $req = \Illuminate\Support\Facades\DB::transaction(function() use ($request, $id) {
                $req = StoreRequisition::with('items')->findOrFail($id);
                if ($req->main_admin_status !== 'approved') {
                    throw new \Exception('This requisition must be verified and approved by the Department Head first.');
                }
                $isPending = ($req->status === 'pending');
                $targetStatus = $request->status;

                if ($request->filled('items')) {
                    foreach ($request->items as $itemData) {
                        $reqItem = $req->items->firstWhere('id', $itemData['id']);
                        if ($reqItem) {
                            $approvedQty = floatval($itemData['quantity_approved']);
                            $reqItem->quantity_approved = $approvedQty;
                            
                            $altApprovedQty = isset($itemData['alternative_quantity_approved']) ? floatval($itemData['alternative_quantity_approved']) : 0;
                            
                            if (!empty($itemData['remarks'])) {
                                $reqItem->remarks = $itemData['remarks'];
                            }
                            if (!empty($itemData['alternative_description']) && $altApprovedQty > 0) {
                                $reqItem->alternative_description = $itemData['alternative_description'];
                                $reqItem->alternative_quantity_approved = $altApprovedQty;
                            } else {
                                $reqItem->alternative_description = null;
                                $reqItem->alternative_quantity_approved = null;
                            }
                            $reqItem->save();

                            // Deduct from inventory if transitioning to approved/partially_approved
                            if ($isPending && in_array($targetStatus, ['approved', 'partially_approved'])) {
                                // 1. Original Item FIFO Deduction
                                if ($approvedQty > 0) {
                                    $origItemName = $reqItem->description;
                                    $totalStock = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                                        ->where('inventory_batches.supplier_status', '!=', 'System Draft')
                                        ->where(\DB::raw('TRIM(inventory_items.description)'), trim($origItemName))
                                        ->selectRaw('SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
                                        ->value('total_stock') ?? 0;

                                    if ($approvedQty > $totalStock) {
                                        throw new \Exception("Cannot approve {$approvedQty} for '{$origItemName}'. Only {$totalStock} is available in stock.");
                                    }

                                    $qtyToDeduct = $approvedQty;
                                    $stockItems = InventoryItem::where(\DB::raw('TRIM(description)'), trim($origItemName))
                                        ->whereHas('batch', function ($q) use ($reqItem) {
                                            $q->where('supplier_status', '!=', 'System Draft');
                                            if ($reqItem->category) {
                                                $q->where('ledge_category', $reqItem->category);
                                            }
                                        })
                                        ->where(function($query) {
                                            $query->where('qty', '>', 0)
                                                ->orWhere('stock_balance', '>', 0);
                                        })
                                        ->orderBy('created_at', 'asc')
                                        ->orderBy('id', 'asc')
                                        ->get();

                                    foreach ($stockItems as $inventoryItem) {
                                        if ($qtyToDeduct <= 0) break;

                                        $availableQty = floatval(str_replace(',', '', $inventoryItem->qty));
                                        $availableStock = floatval(str_replace(',', '', $inventoryItem->stock_balance));
                                        
                                        $takeQty = min($availableQty, $qtyToDeduct);
                                        $takeStock = min($availableStock, $qtyToDeduct);

                                        $inventoryItem->qty = max(0, $availableQty - $takeQty);
                                        $inventoryItem->stock_balance = max(0, $availableStock - $takeStock);
                                        $inventoryItem->save();

                                        $qtyToDeduct -= max($takeQty, $takeStock);
                                    }
                                }

                                // 2. Alternative Item FIFO Deduction
                                if ($altApprovedQty > 0 && !empty($reqItem->alternative_description)) {
                                    $altItemName = $reqItem->alternative_description;
                                    $totalAltStock = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                                        ->where('inventory_batches.supplier_status', '!=', 'System Draft')
                                        ->where(\DB::raw('TRIM(inventory_items.description)'), trim($altItemName))
                                        ->selectRaw('SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
                                        ->value('total_stock') ?? 0;

                                    if ($altApprovedQty > $totalAltStock) {
                                        throw new \Exception("Cannot approve alternative {$altApprovedQty} for '{$altItemName}'. Only {$totalAltStock} is available in stock.");
                                    }

                                    $qtyToDeduct = $altApprovedQty;
                                    $stockItems = InventoryItem::where(\DB::raw('TRIM(description)'), trim($altItemName))
                                        ->whereHas('batch', function ($q) use ($reqItem) {
                                            $q->where('supplier_status', '!=', 'System Draft');
                                            if ($reqItem->category) {
                                                $q->where('ledge_category', $reqItem->category);
                                            }
                                        })
                                        ->where(function($query) {
                                            $query->where('qty', '>', 0)
                                                ->orWhere('stock_balance', '>', 0);
                                        })
                                        ->orderBy('created_at', 'asc')
                                        ->orderBy('id', 'asc')
                                        ->get();

                                    foreach ($stockItems as $inventoryItem) {
                                        if ($qtyToDeduct <= 0) break;

                                        $availableQty = floatval(str_replace(',', '', $inventoryItem->qty));
                                        $availableStock = floatval(str_replace(',', '', $inventoryItem->stock_balance));
                                        
                                        $takeQty = min($availableQty, $qtyToDeduct);
                                        $takeStock = min($availableStock, $qtyToDeduct);

                                        $inventoryItem->qty = max(0, $availableQty - $takeQty);
                                        $inventoryItem->stock_balance = max(0, $availableStock - $takeStock);
                                        $inventoryItem->save();

                                        $qtyToDeduct -= max($takeQty, $takeStock);
                                    }
                                }
                            }
                        }
                    }
                }

                $req->status        = $targetStatus;
                $req->admin_notes   = $request->admin_notes;
                $req->decline_reason = ($targetStatus === 'declined') ? $request->decline_reason : null;
                $req->processed_by  = auth()->id();
                $req->processed_at  = now();
                $req->save();

                return $req;
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }

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
            if ($request->status === 'declined' && $request->decline_reason) {
                $msg .= "<div style='margin-top:8px;padding:10px 14px;background:rgba(239,68,68,0.05);border:1px solid rgba(239,68,68,0.2);border-radius:10px;font-size:0.85rem;color:#7f1d1d;'><b>Reason for Decline:</b> " . e($request->decline_reason) . "</div><br>";
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

    /**
     * Personnel: List all requisitions for management.
     */
    public function personnelIndex(Request $request)
    {
        if (auth()->user()->role === 'Requisitioner') {
            abort(403, 'Unauthorized.');
        }

        $query = StoreRequisition::with(['items', 'requester', 'processor', 'collector'])
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
        if ($request->filled('search_id')) {
            $search = trim($request->input('search_id'));
            $query->where(function($q) use ($search) {
                $parsedId = preg_replace('/[^0-9]/', '', $search);
                if (!empty($parsedId)) {
                    $q->where('id', $parsedId);
                }
                $q->orWhereHas('items', function($ki) use ($search) {
                    $ki->where('description', 'LIKE', '%' . $search . '%');
                });
            });
        }

        $requisitions = $query->paginate(5)->withQueryString();
        $ledgeMap = Setting::getCategories();

        $stats = [
            'pending'            => StoreRequisition::where('status', 'pending')->count(),
            'approved'           => StoreRequisition::where('status', 'approved')->count(),
            'partially_approved' => StoreRequisition::where('status', 'partially_approved')->count(),
            'declined'           => StoreRequisition::where('status', 'declined')->count(),
            'urgent'             => StoreRequisition::where('status', 'pending')->where('priority', 'urgent')->count(),
        ];

        return view('requisitions.personnel', compact('requisitions', 'ledgeMap', 'stats'));
    }

    /**
     * Requisitioner: Show standalone Requisition History page.
     */
    public function history(Request $request)
    {
        $ledgeMap = Setting::getCategories();
        return view('requisitions.history', compact('ledgeMap'));
    }

    /**
     * Main Admin: List requisitions requiring Main Admin approval and history.
     */
    public function mainAdminIndex(Request $request)
    {
        if (!in_array(auth()->user()->role, ['Main Admin', 'Department Head'])) abort(403);

        $isStoresHead = (strcasecmp(auth()->user()->department, 'Stores') === 0 || strcasecmp(auth()->user()->department, 'Store') === 0);

        $query = StoreRequisition::with(['items', 'requester', 'processor', 'collector'])
            ->orderByRaw("FIELD(priority, 'urgent', 'normal', 'low')")
            ->orderBy('created_at', 'desc');

        // Apply department scoping for originating heads
        if (!$isStoresHead) {
            $query->where('department', auth()->user()->department);
        }

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                if ($isStoresHead) {
                    $query->where('status', 'pending')->where('origin_admin_status', 'approved')->where('main_admin_status', 'pending');
                } else {
                    $query->where('status', 'pending')->where('origin_admin_status', 'pending');
                }
            } elseif ($request->status === 'approved') {
                if ($isStoresHead) {
                    $query->where('main_admin_status', 'approved');
                } else {
                    $query->where('origin_admin_status', 'approved');
                }
            } elseif ($request->status === 'declined') {
                if ($isStoresHead) {
                    $query->where('main_admin_status', 'declined');
                } else {
                    $query->where('origin_admin_status', 'declined');
                }
            } elseif ($request->status === 'history') {
                if ($isStoresHead) {
                    $query->whereIn('main_admin_status', ['approved', 'declined']);
                } else {
                    $query->whereIn('origin_admin_status', ['approved', 'declined']);
                }
            }
        } else {
            if ($isStoresHead) {
                $query->where('status', 'pending')->where('origin_admin_status', 'approved')->where('main_admin_status', 'pending');
            } else {
                $query->where('status', 'pending')->where('origin_admin_status', 'pending');
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('department')) {
            $query->where('department', 'LIKE', '%' . $request->department . '%');
        }

        $requisitions = $query->paginate(5)->withQueryString();
        $ledgeMap = Setting::getCategories();

        // Calculate scoped stats
        if ($isStoresHead) {
            $stats = [
                'pending'  => StoreRequisition::where('status', 'pending')->where('origin_admin_status', 'approved')->where('main_admin_status', 'pending')->count(),
                'approved' => StoreRequisition::where('main_admin_status', 'approved')->count(),
                'declined' => StoreRequisition::where('main_admin_status', 'declined')->count(),
            ];
        } else {
            $stats = [
                'pending'  => StoreRequisition::where('status', 'pending')->where('department', auth()->user()->department)->where('origin_admin_status', 'pending')->count(),
                'approved' => StoreRequisition::where('department', auth()->user()->department)->where('origin_admin_status', 'approved')->count(),
                'declined' => StoreRequisition::where('department', auth()->user()->department)->where('origin_admin_status', 'declined')->count(),
            ];
        }

        return view('requisitions.main_admin', compact('requisitions', 'ledgeMap', 'stats'));
    }

    /**
     * Main Admin: Process (approve/decline) a requisition request first.
     */
    public function mainAdminProcess(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['Main Admin', 'Department Head'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status'         => 'required|in:approved,declined',
            'admin_notes'    => 'nullable|string|max:1000',
            'decline_reason' => 'nullable|string|max:2000',
        ]);

        $req = StoreRequisition::findOrFail($id);

        $isStoresHead = (strcasecmp(auth()->user()->department, 'Stores') === 0 || strcasecmp(auth()->user()->department, 'Store') === 0);

        if (!$isStoresHead) {
            // Originating department head check
            if ($req->department !== auth()->user()->department) {
                return response()->json(['success' => false, 'message' => 'Unauthorized department access.'], 403);
            }
            if ($req->status !== 'pending' || $req->origin_admin_status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Requisition has already been processed.'], 400);
            }
        } else {
            // Stores department head check
            if ($req->origin_admin_status !== 'approved') {
                return response()->json(['success' => false, 'message' => 'This requisition must be approved by the originating department head first.'], 400);
            }
            if ($req->status !== 'pending' || $req->main_admin_status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Requisition has already been processed.'], 400);
            }
        }

        if ($request->status === 'approved') {
            if (!$isStoresHead) {
                $req->origin_admin_status = 'approved';
                $actionName = 'DEPT_HEAD_APPROVE';
                $logDesc = "Department Head " . auth()->user()->name . " approved store requisition #{$req->id} from department: {$req->department}. Escalated to Department Head (Stores).";
            } else {
                $req->main_admin_status = 'approved';
                $actionName = 'MAIN_ADMIN_APPROVE';
                $logDesc = "Stores Department Head " . auth()->user()->name . " approved store requisition #{$req->id} from {$req->department}. Passed to Head of Stores (Admin) for final processing.";
            }

            if ($request->filled('admin_notes')) {
                $req->admin_notes = $request->admin_notes;
            }
            $req->save();

            // Log
            SystemLog::create([
                'user_id'    => auth()->id(),
                'event_type' => 'REQUISITION',
                'action'     => $actionName,
                'description'=> $logDesc,
                'severity'   => 'info',
                'metadata'   => ['requisition_id' => $req->id],
                'ip_address' => $request->ip(),
            ]);

            // Notifications
            if (!$isStoresHead) {
                // Notify Department Head (Stores)
                $storesHeads = User::whereIn('role', ['Main Admin', 'Department Head'])
                    ->where(fn($q) => $q->where('department', 'Stores')->orWhere('department', 'Store'))
                    ->where('is_active', true)
                    ->get();
                foreach ($storesHeads as $storesHead) {
                    $priorityLabel = strtoupper($req->priority);
                    $itemCount = $req->items()->count();
                    $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #10b981;border-radius:12px;background:rgba(16,185,129,0.05);'>";
                    $msg .= "<b style='color:#10b981;'>📋 DEPT HEAD APPROVED REQUISITION — {$priorityLabel} PRIORITY</b><br><br>";
                    $msg .= "Department Head <b>" . auth()->user()->name . "</b> has approved store requisition Ref: #<b>{$req->id}</b>.<br><br>";
                    $msg .= "<b>Department:</b> {$req->department}<br>";
                    $msg .= "<b>Requested by:</b> {$req->requester_name}<br>";
                    $msg .= "<b>Purpose:</b> " . e($req->purpose) . "<br>";
                    if ($request->filled('admin_notes')) {
                        $msg .= "<b>Dept Head Notes:</b> " . e($request->admin_notes) . "<br><br>";
                    }
                    $msg .= "<a href='" . route('main-admin.requisitions') . "' style='display:inline-block;background:#10b981;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Review Requisition</a>";
                    $msg .= "</div>";

                    Message::create([
                        'sender_id'    => auth()->id(),
                        'receiver_id'  => $storesHead->id,
                        'message'      => $msg,
                        'is_automated' => true,
                    ]);
                }
            } else {
                // Notify Head of Stores (Admin)
                $admins = User::where('is_admin', true)->where('is_active', true)->get();
                foreach ($admins as $admin) {
                    $priorityLabel = strtoupper($req->priority);
                    $itemCount = $req->items()->count();
                    $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #10b981;border-radius:12px;background:rgba(16,185,129,0.05);'>";
                    $msg .= "<b style='color:#10b981;'>📋 MAIN ADMIN APPROVED REQUISITION — {$priorityLabel} PRIORITY</b><br><br>";
                    $msg .= "Stores Department Head <b>" . auth()->user()->name . "</b> has approved store requisition Ref: #<b>{$req->id}</b>.<br><br>";
                    $msg .= "<b>Department:</b> {$req->department}<br>";
                    $msg .= "<b>Requested by:</b> {$req->requester_name}<br>";
                    $msg .= "<b>Purpose:</b> " . e($req->purpose) . "<br>";
                    if ($request->filled('admin_notes')) {
                        $msg .= "<b>Stores Head Notes:</b> " . e($request->admin_notes) . "<br><br>";
                    }
                    $msg .= "<a href='" . route('admin.requisitions') . "' style='display:inline-block;background:#10b981;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Perform Final Head Review</a>";
                    $msg .= "</div>";

                    Message::create([
                        'sender_id'    => auth()->id(),
                        'receiver_id'  => $admin->id,
                        'message'      => $msg,
                        'is_automated' => true,
                    ]);
                }
            }
        } else {
            // Requisition Declined
            if (!$isStoresHead) {
                $req->origin_admin_status = 'declined';
                $actionName = 'DEPT_HEAD_DECLINE';
                $logDesc = "Department Head " . auth()->user()->name . " declined store requisition #{$req->id} from department: {$req->department}.";
                $notifyTitle = "📋 REQUISITION DECLINED BY DEPARTMENT HEAD";
                $notifyBody = "Your store requisition (Ref: #{$req->id}) from <b>{$req->department}</b> has been <b>DECLINED</b> by your Department Head.";
            } else {
                $req->main_admin_status = 'declined';
                $actionName = 'MAIN_ADMIN_DECLINE';
                $logDesc = "Stores Department Head " . auth()->user()->name . " declined store requisition #{$req->id} from department: {$req->department}.";
                $notifyTitle = "📋 REQUISITION DECLINED BY STORES DEPT HEAD";
                $notifyBody = "Your store requisition (Ref: #{$req->id}) from <b>{$req->department}</b> has been <b>DECLINED</b> by the Stores Department Head.";
            }

            $req->status = 'declined';
            if ($request->filled('decline_reason')) {
                $req->decline_reason = $request->decline_reason;
            }
            if ($request->filled('admin_notes')) {
                $req->admin_notes = $request->admin_notes;
            }
            $req->save();

            // Log
            SystemLog::create([
                'user_id'    => auth()->id(),
                'event_type' => 'REQUISITION',
                'action'     => $actionName,
                'description'=> $logDesc,
                'severity'   => 'warning',
                'metadata'   => ['requisition_id' => $req->id],
                'ip_address' => $request->ip(),
            ]);

            // Notify requester
            if ($req->requested_by) {
                $msg  = "<div class='personnel-view requisition-status-msg' style='padding:15px;border:1px solid #ef4444;border-radius:12px;background:rgba(239,68,68,0.02);'>";
                $msg .= "<b style='color:#ef4444;'>{$notifyTitle}</b><br><br>";
                $msg .= "{$notifyBody}<br><br>";
                if ($request->decline_reason) {
                    $msg .= "<div style='margin-top:8px;padding:10px 14px;background:rgba(239,68,68,0.05);border:1px solid rgba(239,68,68,0.2);border-radius:10px;font-size:0.85rem;color:#7f1d1d;'><b>Reason for Decline:</b> " . e($request->decline_reason) . "</div><br>";
                }
                $msg .= "<a href='" . route('requisitions.index') . "' style='display:inline-block;background:#ef4444;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>View My Requisitions</a>";
                $msg .= "</div>";

                Message::create([
                    'sender_id'    => auth()->id(),
                    'receiver_id'  => $req->requested_by,
                    'message'      => $msg,
                    'is_automated' => true,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Requisition processed successfully.',
        ]);
    }
}
