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
        self::checkOverdueTemporaryItems();
        $ledgeMap = Setting::getCategories();

        // Fetch all available inventory items (grouped by description)
        $availableItems = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->where('inventory_batches.approval_status', '=', 'approved')
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

        $user = auth()->user();
        $disabledDepts = Setting::get('disabled_requisition_departments', []);
        if (is_string($disabledDepts)) {
            $disabledDepts = json_decode($disabledDepts, true) ?? [];
        }
        if (!is_array($disabledDepts)) {
            $disabledDepts = [];
        }
        $normalizedDisabledDepts = array_map(function($dept) {
            return strtolower(trim($dept));
        }, $disabledDepts);
        
        $isDepartmentDisabled = in_array(strtolower(trim($user->department ?? '')), $normalizedDisabledDepts);

        return view('requisitions.index', compact('availableItems', 'ledgeMap', 'myRequisitions', 'isDepartmentDisabled'));
    }

    public function checkout(Request $request)
    {
        $user = auth()->user();
        if (empty($user->name) || $user->name === $user->username || empty($user->phone) || empty($user->role) || empty($user->service_number)) {
            return redirect()->route('requisitions.index')->with('show_profile_modal', true);
        }

        $disabledDepts = Setting::get('disabled_requisition_departments', []);
        if (is_string($disabledDepts)) {
            $disabledDepts = json_decode($disabledDepts, true) ?? [];
        }
        if (!is_array($disabledDepts)) {
            $disabledDepts = [];
        }
        $normalizedDisabledDepts = array_map(function($dept) {
            return strtolower(trim($dept));
        }, $disabledDepts);
        
        $isDepartmentDisabled = in_array(strtolower(trim($user->department ?? '')), $normalizedDisabledDepts);

        $ledgeMap = Setting::getCategories();
        return view('requisitions.checkout', compact('ledgeMap', 'isDepartmentDisabled'));
    }

    /**
     * Personnel: Submit a new requisition.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $disabledDepts = Setting::get('disabled_requisition_departments', []);
        if (is_string($disabledDepts)) {
            $disabledDepts = json_decode($disabledDepts, true) ?? [];
        }
        if (!is_array($disabledDepts)) {
            $disabledDepts = [];
        }
        $normalizedDisabledDepts = array_map(function($dept) {
            return strtolower(trim($dept));
        }, $disabledDepts);

        if (in_array(strtolower(trim($user->department ?? '')), $normalizedDisabledDepts) || in_array(strtolower(trim($request->department ?? '')), $normalizedDisabledDepts)) {
            return response()->json([
                'success' => false,
                'message' => 'Your department has been disabled from making requisition requests by the administrator.'
            ], 403);
        }

        $isOtherHOD = in_array($user->role, ['Department Head', 'Dept Head HR', 'Head of Welfare'])
            && (strcasecmp($user->department ?? '', 'Stores') !== 0 && strcasecmp($user->department ?? '', 'Store') !== 0);

        // Permission gate: admin may revoke the ability to submit requests (exempt other HODs)
        if (!$isOtherHOD && isset($user->can_make_requisition) && !$user->can_make_requisition) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to submit requisition requests. Please contact your administrator.'
            ], 403);
        }

        if (empty($user->name) || $user->name === $user->username || empty($user->phone) || empty($user->role) || empty($user->service_number)) {
            return response()->json([
                'success' => false,
                'message' => 'Your profile is incomplete. Please complete your profile details (Full Name, Phone Number, Service Number, and Professional Role) before submitting a request.'
            ], 422);
        }

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

        // Enforce Item Request Limits
        foreach ($request->items as $item) {
            if (empty($item['description'])) continue;
            
            $description = trim($item['description']);
            $category = $item['category'] ?? null;
            $requestedQty = (float)$item['quantity_requested'];

            // Calculate physical stock
            $stockQuery = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                ->where('inventory_batches.supplier_status', '!=', 'System Draft')
                ->where('inventory_batches.approval_status', '=', 'approved')
                ->whereRaw('TRIM(inventory_items.description) = ?', [$description]);
            
            if (is_null($category)) {
                $stockQuery->whereNull('inventory_batches.ledge_category');
            } else {
                $stockQuery->where('inventory_batches.ledge_category', $category);
            }

            $physicalStock = (float) $stockQuery->sum(\DB::raw('CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))'));

            // Calculate available stock applying the limit
            $availableStock = Setting::getAvailableStock($description, $physicalStock, $category);

            if ($requestedQty > $availableStock) {
                return response()->json([
                    'success' => false,
                    'message' => "Available stock for this item is restricted to {$availableStock}."
                ], 422);
            }
        }

        $isStoresDept = (strcasecmp($request->department, 'Stores') === 0 || strcasecmp($request->department, 'Store') === 0);

        $requiresStoresDeptHeadApproval = false;
        if ($isStoresDept) {
            $storesApprovalCategories = \App\Models\Setting::get('stores_dept_head_approval_categories', []);
            if (!is_array($storesApprovalCategories)) {
                $storesApprovalCategories = json_decode($storesApprovalCategories, true) ?? [];
            }
            foreach ($request->items as $item) {
                if (!empty($item['category']) && in_array($item['category'], $storesApprovalCategories)) {
                    $requiresStoresDeptHeadApproval = true;
                    break;
                }
            }
        }

        $requiresDgApproval = false;
        $dgApprovalCategories = \App\Models\Setting::get('dg_approval_categories', []);
        if (!is_array($dgApprovalCategories)) {
            $dgApprovalCategories = json_decode($dgApprovalCategories, true) ?? [];
        }
        foreach ($request->items as $item) {
            if (!empty($item['category']) && in_array($item['category'], $dgApprovalCategories)) {
                $requiresDgApproval = true;
                break;
            }
        }

        $isHODOfThisDept = $isOtherHOD && (strcasecmp($user->department ?? '', $request->department) === 0);

        $requisition = StoreRequisition::create([
            'requester_name' => $request->requester_name,
            'department'     => $request->department,
            'rank_or_title'  => $request->rank_or_title,
            'requested_by'   => auth()->id(),
            'purpose'        => $request->purpose,
            'priority'       => $request->priority,
            'status'         => 'pending',
            'usage_type'     => $request->usage_type,
            'origin_admin_status' => $isHODOfThisDept ? 'approved' : 'pending',
            'origin_approved_by'  => $isHODOfThisDept ? $user->name : null,
            'main_admin_status'   => ($isStoresDept && !$requiresStoresDeptHeadApproval) ? 'approved' : 'pending',
            'requires_dg_approval' => $requiresDgApproval,
            'dg_status' => $requiresDgApproval ? 'pending' : null,
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
        $msg .= "<a href='" . route('main-admin.requisitions') . "?open_id={$requisition->id}' style='display:inline-block;background:#4f46e5;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Review Requisition</a>";
        $msg .= "</div>";

        $notified = false;
        if (strcasecmp($request->department, 'Stores') === 0 || strcasecmp($request->department, 'Store') === 0) {
            $hasActiveStoresHead = User::where('role', 'Head of Stores')->where('is_active', true)->exists();
            if ($hasActiveStoresHead) {
                $deptHeads = User::where('role', 'Head of Stores')->where('is_active', true)->get();
            } else {
                $deptHeads = User::where('role', 'Main Admin')->where('is_active', true)->get();
            }
        } else {
            $deptHeads = User::whereIn('role', ['Main Admin', 'Department Head'])
                ->where('department', $request->department)
                ->where('is_active', true)
                ->get();
        }
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

        if (!$notified) {
            // Notify Stores Department Heads and Admins
            $storesHeads = User::whereIn('role', ['Main Admin', 'Department Head'])
                ->where(fn($q) => $q->where('department', 'Stores')->orWhere('department', 'Store'))
                ->where('is_active', true)
                ->get();
            $admins = User::getApproversQuery()->where('is_active', true)->get();
            $recipients = $storesHeads->concat($admins)->unique('id');

            foreach ($recipients as $recipient) {
                // Link for admins goes to admin.requisitions; link for stores head goes to main-admin.requisitions
                $link = ($recipient->is_admin && $recipient->role !== 'Main Admin' ? route('admin.requisitions') : route('main-admin.requisitions')) . "?open_id={$requisition->id}";
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
    public function myRequisitions(Request $request)
    {
        $query = StoreRequisition::with(['items', 'requester', 'collector']);

        // Requisitioners see only their own. Personnel see all.
        if (auth()->user()->role === 'Requisitioner') {
            $query->where('requested_by', auth()->id());
        }

        // Apply search query
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $parsedId = preg_replace('/[^0-9]/', '', $search);
                if (!empty($parsedId)) {
                    $q->where('id', 'LIKE', '%' . $parsedId . '%');
                }
                
                $q->orWhere('unique_id', 'LIKE', '%' . $search . '%')
                  ->orWhere('department', 'LIKE', '%' . $search . '%')
                  ->orWhere('purpose', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('items', function($ki) use ($search) {
                      $ki->where('description', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        $perPage = (int) $request->input('per_page', 5);
        if ($perPage < 1 || $perPage > 100) {
            $perPage = 5;
        }

        $paginator = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $requisitions = collect($paginator->items())->map(function ($req) {
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
                'origin_admin_status' => $req->origin_admin_status ?? 'pending',
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
                'collector_location' => $req->collector_location,
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

        return response()->json([
            'data'         => $requisitions,
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'from'         => $paginator->firstItem(),
            'to'           => $paginator->lastItem(),
        ]);
    }

    public function collect(Request $request, $id)
    {
        if (!auth()->user()->can_operate_logistics) {
            return response()->json(['success' => false, 'message' => 'Only personnel with Logistics Ops permission are authorized to click the collection button and confirm physical collection.'], 403);
        }

        // Add self-healing schema migration for collector_location if not exists
        if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'collector_location')) {
            try {
                \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('collector_location')->nullable();
                });
            } catch (\Exception $e) {}
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('store_requisitions', 'collector_staff_id')) {
            try {
                \Illuminate\Support\Facades\Schema::table('store_requisitions', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('collector_staff_id')->nullable();
                });
            } catch (\Exception $e) {}
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('receipts', 'collector_staff_id')) {
            try {
                \Illuminate\Support\Facades\Schema::table('receipts', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('collector_staff_id')->nullable();
                });
            } catch (\Exception $e) {}
        }

        $request->validate([
            'collector_name' => 'required|string|max:255',
            'collector_contact' => 'required|string|max:100',
            'collector_location' => 'required|string|max:255',
            'collector_staff_id' => 'required|string|max:100',
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
        $req->collector_location = $request->collector_location;
        $req->collector_staff_id = $request->collector_staff_id;
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
            'description'=> auth()->user()->name . " confirmed physical collection of store requisition #{$req->id} by {$req->collector_name} (Staff ID: {$req->collector_staff_id}, Contact: {$req->collector_contact}) to location {$req->collector_location}.",
            'severity'   => 'info',
            'metadata'   => ['requisition_id' => $req->id],
            'ip_address' => $request->ip(),
        ]);

        // Generate Receipts record
        try {
            $receiptCount = \App\Models\Receipt::count() + 1;
            $receiptNumber = 'RCP-' . date('Y') . '-' . str_pad($receiptCount, 5, '0', STR_PAD_LEFT);

            $itemsSnapshot = $req->items->map(function($item) {
                return [
                    'description' => $item->description,
                    'unit' => $item->unit,
                    'quantity_requested' => (float)$item->quantity_requested,
                    'quantity_approved' => (float)$item->quantity_approved,
                    'alternative_description' => $item->alternative_description,
                    'alternative_quantity_approved' => $item->alternative_quantity_approved !== null ? (float)$item->alternative_quantity_approved : null,
                    'category' => $item->category,
                    'remarks' => $item->remarks,
                ];
            })->toArray();

            $receipt = \App\Models\Receipt::create([
                'requisition_id' => $req->id,
                'receipt_number' => $receiptNumber,
                'collector_name' => $req->collector_name,
                'collector_contact' => $req->collector_contact,
                'collector_location' => $req->collector_location,
                'collector_staff_id' => $req->collector_staff_id,
                'collected_at' => $req->collected_at,
                'issued_by' => auth()->id(),
                'approved_by_dept_head' => $req->origin_approved_by ?? ($req->department . ' Department Head'),
                'approved_by_stores_head' => $req->processor?->name ?? 'Head of Stores',
                'items_json' => json_encode($itemsSnapshot),
            ]);

            $receiptId = $receipt->id;
        } catch (\Exception $e) {
            $receiptId = null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Physical collection confirmed successfully.',
            'receipt_id' => $receiptId,
        ]);
    }

    /**
     * Print Collection Receipt for a requisition.
     */
    public function printReceipt($id)
    {
        $req = StoreRequisition::with(['requester', 'processor', 'collector', 'receipt', 'items'])->findOrFail($id);

        if (!$req->collected_at) {
            abort(404, 'No collection has been confirmed for this requisition.');
        }

        $receipt = $req->receipt;

        // Self-healing legacy fallback: if no receipt record exists, synthesize one transiently
        if (!$receipt) {
            $receiptCount = \App\Models\Receipt::count() + 1;
            $receiptNumber = 'RCP-' . date('Y', strtotime($req->collected_at)) . '-' . str_pad($receiptCount, 5, '0', STR_PAD_LEFT);

            $itemsSnapshot = $req->items->map(function($item) {
                return [
                    'description' => $item->description,
                    'unit' => $item->unit,
                    'quantity_requested' => (float)$item->quantity_requested,
                    'quantity_approved' => (float)$item->quantity_approved,
                    'alternative_description' => $item->alternative_description,
                    'alternative_quantity_approved' => $item->alternative_quantity_approved !== null ? (float)$item->alternative_quantity_approved : null,
                    'category' => $item->category,
                    'remarks' => $item->remarks,
                ];
            })->toArray();

            $receipt = new \App\Models\Receipt([
                'requisition_id' => $req->id,
                'receipt_number' => $receiptNumber,
                'collector_name' => $req->collector_name ?? 'N/A',
                'collector_contact' => $req->collector_contact ?? 'N/A',
                'collector_location' => $req->collector_location ?? 'N/A',
                'collector_staff_id' => $req->collector_staff_id ?? 'N/A',
                'collected_at' => $req->collected_at,
                'issued_by' => $req->collected_by ?? 1,
                'approved_by_dept_head' => $req->origin_approved_by ?? ($req->department . ' Department Head'),
                'approved_by_stores_head' => $req->processor?->name ?? 'Head of Stores',
                'items_json' => json_encode($itemsSnapshot),
            ]);
            
            // Set transient relations if possible
            if ($req->collector) {
                $receipt->setRelation('issuer', $req->collector);
            }
        }

        $items = json_decode($receipt->items_json, true) ?? [];

        // Ledge mapping for display and calculations (Category standardization)
        $ledgeMap = \Illuminate\Support\Facades\Schema::hasTable('settings') 
            ? \App\Models\Setting::getCategories() 
            : [
                'A' => 'Stationary',
                'B' => 'Cleaning',
                'C' => 'IT & Acc.',
                'D' => 'Transport',
                'E' => 'Safety',
                'G' => 'Pharmacy',
                'J' => 'Equipment'
            ];

        return view('requisitions.receipt', compact('req', 'receipt', 'items', 'ledgeMap'));
    }

    /**
     * Personnel: Send follow-up reminder for a pending requisition.
     */
    public function followUp(Request $request, $id)
    {
        $req = StoreRequisition::findOrFail($id);

        $isRequester = ($req->requested_by === auth()->id());
        $isDeptHead = (auth()->user()->role === 'Department Head' && strcasecmp($req->department, auth()->user()->department) === 0);

        if (!$isRequester && !$isDeptHead) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to follow up on this requisition.'], 403);
        }

        // Requisitioners can only follow up AFTER their dept head has reviewed and approved
        if ($isRequester && !$isDeptHead && ($req->origin_admin_status ?? 'pending') === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'You can only follow up after your Department Head has reviewed and approved the request.'
            ], 400);
        }

        $isPendingStore = ($req->status === 'pending');
        $isAwaitingCollection = (in_array($req->status, ['approved', 'partially_approved']) && is_null($req->collected_at));

        if (!$isPendingStore && !$isAwaitingCollection) {
            return response()->json(['success' => false, 'message' => 'You can only follow up on pending or uncollected requisitions.'], 400);
        }

        // Limit follow-ups to once every 5 minutes to prevent spam
        $cacheKey = 'requisition_followup_' . $req->id;
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return response()->json([
                'success' => false, 
                'message' => 'You have recently sent a follow-up reminder for this requisition. Please wait a few minutes before trying again.'
            ], 429);
        }

        $senderName = auth()->user()->name;
        $senderRole = auth()->user()->role;
        $description = "{$senderName} ({$senderRole}) sent a follow-up reminder for pending store requisition #{$req->id}.";

        // Log the action
        SystemLog::create([
            'user_id'    => auth()->id(),
            'event_type' => 'REQUISITION',
            'action'     => 'FOLLOW_UP_REQUISITION',
            'description'=> $description,
            'severity'   => 'info',
            'metadata'   => ['requisition_id' => $req->id],
            'ip_address' => $request->ip(),
        ]);

        // Notify all active admins, stores officers, and delegated approver
        $delegatedId = \App\Models\Setting::get('delegated_approver_id');
        $admins = User::where(function($q) use ($delegatedId) {
            $q->where('is_admin', true)
              ->orWhereIn('role', ['Store Officer', 'Dept. Head (Stores)']);
            if ($delegatedId) {
                $q->orWhere('id', $delegatedId);
            }
        })->where('is_active', true)->get();

        foreach ($admins as $admin) {
            $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #f59e0b;border-radius:12px;background:rgba(245,158,11,0.05);'>";
            $msg .= "<b style='color:#ea580c;'>🔔 REQUISITION FOLLOW-UP REMINDER — Ref: #{$req->id}</b><br><br>";
            if ($isAwaitingCollection) {
                $msg .= "Department Head <b>" . e($senderName) . "</b> has sent a follow-up reminder regarding an approved requisition awaiting physical collection from department: <b>" . e($req->department) . "</b>.<br><br>";
            } elseif ($isDeptHead) {
                $msg .= "Department Head <b>" . e($senderName) . "</b> has sent a follow-up reminder regarding a pending staff requisition from department: <b>" . e($req->department) . "</b>.<br><br>";
            } else {
                $msg .= "Requisitioner <b>" . e($req->requester_name) . "</b> has sent a follow-up reminder regarding their pending requisition from department: <b>" . e($req->department) . "</b>.<br><br>";
            }
            $msg .= "<b>Purpose:</b> " . e($req->purpose) . "<br><br>";
            $recipientLink = ($admin->is_admin && $admin->role !== 'Main Admin' ? route('admin.requisitions') : route('main-admin.requisitions')) . "?open_id={$req->id}";
            $msg .= "<a href='" . $recipientLink . "' style='display:inline-block;background:#f59e0b;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Review Requisition Now</a>";
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
        self::checkOverdueTemporaryItems();
        if (auth()->user()->role === 'Main Admin') {
            return redirect()->route('main-admin.requisitions');
        }

        if (!auth()->user()->is_admin && !auth()->user()->isDelegatedApprover()) abort(403);

        $query = StoreRequisition::with(['items', 'requester', 'processor', 'collector'])
            ->where(function($q) {
                $q->where('status', '!=', 'pending')
                  ->orWhere('main_admin_status', 'approved');
            })
            ->orderByRaw("CASE WHEN status = 'pending' THEN 1 WHEN status = 'partially_approved' THEN 2 WHEN status = 'approved' THEN 3 WHEN status = 'declined' THEN 4 ELSE 5 END")
            ->orderByRaw("CASE WHEN priority = 'urgent' THEN 1 WHEN priority = 'normal' THEN 2 WHEN priority = 'low' THEN 3 ELSE 4 END")
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

        $stats = \Illuminate\Support\Facades\Cache::remember('admin_requisitions_stats', 300, function() {
            $statsData = StoreRequisition::selectRaw("
                SUM(CASE WHEN status = 'pending' AND main_admin_status = 'approved' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'partially_approved' THEN 1 ELSE 0 END) as partially_approved,
                SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) as declined,
                SUM(CASE WHEN status = 'pending' AND main_admin_status = 'approved' AND priority = 'urgent' THEN 1 ELSE 0 END) as urgent
            ")->first();

            return [
                'pending'            => (int) ($statsData->pending ?? 0),
                'approved'           => (int) ($statsData->approved ?? 0),
                'partially_approved' => (int) ($statsData->partially_approved ?? 0),
                'declined'           => (int) ($statsData->declined ?? 0),
                'urgent'             => (int) ($statsData->urgent ?? 0),
            ];
        });

        $chartData = \Illuminate\Support\Facades\Cache::remember('admin_requisitions_chart_data', 300, function() use ($ledgeMap) {
            // 1. Requisitions count by Department
            $deptRequests = StoreRequisition::whereNotNull('department')
                ->where('department', '!=', '')
                ->selectRaw('department, count(*) as count')
                ->groupBy('department')
                ->orderByDesc('count')
                ->get();
            $deptLabels = $deptRequests->pluck('department')->toArray();
            $deptCounts = $deptRequests->pluck('count')->toArray();

            // 2. Category Requests by Department (Grouped Column Chart)
            $deptCategoryData = StoreRequisitionItem::join('store_requisitions', 'store_requisition_items.requisition_id', '=', 'store_requisitions.id')
                ->whereNotNull('store_requisitions.department')
                ->where('store_requisitions.department', '!=', '')
                ->selectRaw('store_requisitions.department, store_requisition_items.category, count(*) as count')
                ->groupBy('store_requisitions.department', 'store_requisition_items.category')
                ->get();

            $uniqueDepts = $deptCategoryData->pluck('department')->unique()->values()->toArray();
            $uniqueCats = $deptCategoryData->pluck('category')->unique()->values()->toArray();
            
            $categorySeries = [];
            foreach ($uniqueCats as $cat) {
                $catName = $ledgeMap[$cat] ?? 'Category ' . $cat;
                $data = [];
                foreach ($uniqueDepts as $dept) {
                    $match = $deptCategoryData->where('department', $dept)->where('category', $cat)->first();
                    $data[] = $match ? $match->count : 0;
                }
                $categorySeries[] = [
                    'name' => $catName,
                    'data' => $data
                ];
            }

            // 3. Top Requested Items by Department
            $topItems = StoreRequisitionItem::selectRaw('TRIM(description) as item_name, count(*) as count')
                ->groupBy(\DB::raw('TRIM(description)'))
                ->orderByDesc('count')
                ->take(8)
                ->get();
            $itemLabels = $topItems->pluck('item_name')->toArray();

            $itemDeptData = StoreRequisitionItem::join('store_requisitions', 'store_requisition_items.requisition_id', '=', 'store_requisitions.id')
                ->whereNotNull('store_requisitions.department')
                ->where('store_requisitions.department', '!=', '')
                ->whereIn(\DB::raw('TRIM(store_requisition_items.description)'), $itemLabels)
                ->selectRaw('TRIM(store_requisition_items.description) as item_name, store_requisitions.department, count(*) as count')
                ->groupBy(\DB::raw('TRIM(store_requisition_items.description)'), 'store_requisitions.department')
                ->get();

            $topItemDepts = $itemDeptData->pluck('department')->unique()->values()->toArray();

            $itemSeries = [];
            foreach ($topItemDepts as $dept) {
                $data = [];
                foreach ($itemLabels as $itemLabel) {
                    $match = $itemDeptData->where('item_name', $itemLabel)->where('department', $dept)->first();
                    $data[] = $match ? $match->count : 0;
                }
                $itemSeries[] = [
                    'name' => $dept,
                    'data' => $data
                ];
            }

            return compact('deptLabels', 'deptCounts', 'categorySeries', 'uniqueDepts', 'itemLabels', 'itemSeries');
        });

        // Unpack cached series
        $deptLabels = $chartData['deptLabels'];
        $deptCounts = $chartData['deptCounts'];
        $categorySeries = $chartData['categorySeries'];
        $uniqueDepts = $chartData['uniqueDepts'];
        $itemLabels = $chartData['itemLabels'];
        $itemSeries = $chartData['itemSeries'];

        return view('admin.requisitions', compact(
            'requisitions', 
            'ledgeMap', 
            'stats',
            'deptLabels',
            'deptCounts',
            'categorySeries',
            'uniqueDepts',
            'itemLabels',
            'itemSeries'
        ));
    }

    /**
     * Admin: Get single requisition detail (API).
     */
    public function adminShow($id)
    {
        $user = auth()->user();
        $req = StoreRequisition::with(['items', 'requester', 'processor', 'collector'])->findOrFail($id);

        $isStoresHead = ($user->role === 'Main Admin' || $user->role === 'Head of Stores' || strcasecmp($user->department ?? '', 'Stores') === 0 || strcasecmp($user->department ?? '', 'Store') === 0);
        if (!$isStoresHead) {
            $isBackup = ($user->role === 'Department Head' && in_array($user->department, ['Human Resource Management Department', 'Welfare Department']));
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

        $canView = $user->is_admin || 
                   $user->isDelegatedApprover() || 
                   $isStoresHead ||
                   ($user->role === 'Department Head' && strcasecmp($user->department, $req->department) === 0) ||
                   ($req->requested_by === $user->id);

        if (!$canView) {
            abort(403, 'Unauthorized');
        }

        // Enrich items with current stock availability
        $items = $req->items->map(function ($item) {
            $stock = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                ->where('inventory_batches.supplier_status', '!=', 'System Draft')
                ->where('inventory_batches.approval_status', '=', 'approved')
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
            ->where('inventory_batches.approval_status', '=', 'approved')
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
            'origin_admin_status' => $req->origin_admin_status,
            'alternative_status' => $req->alternative_status,
            'origin_approved_by' => $req->origin_approved_by,
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
            'collector_location' => $req->collector_location,
            'items'          => $items,
            'alternatives'   => $alternatives,
        ]);
    }

    /**
     * Admin: Process (approve/decline) a requisition.
     */
    public function adminProcess(Request $request, $id)
    {
        if (!auth()->user()->is_admin && !auth()->user()->isDelegatedApprover()) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);

        $request->validate([
            'status'         => 'required|in:approved,partially_approved,declined,pending',
            'alternative_status' => 'nullable|string|in:proposed,agreed,declined',
            'admin_notes'    => 'nullable|string|max:1000',
            'decline_reason' => 'nullable|string|max:2000',
            'items'          => 'nullable|array',
            'items.*.id'                             => 'required|integer',
            'items.*.quantity_approved'              => 'required|numeric|min:0',
            'items.*.alternative_description'        => 'nullable|string|max:255',
            'items.*.alternative_quantity_approved' => 'nullable|numeric|min:0',
        ]);

        if ($request->status === 'pending') {
            try {
                $req = \Illuminate\Support\Facades\DB::transaction(function() use ($request, $id) {
                    $req = StoreRequisition::with('items')->findOrFail($id);
                    if ($req->main_admin_status !== 'approved') {
                        throw new \Exception('This requisition must be verified and approved by the Department Head first.');
                    }
                    
                    if ($request->filled('items')) {
                        foreach ($request->items as $itemData) {
                            $reqItem = $req->items->firstWhere('id', $itemData['id']);
                            if ($reqItem) {
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
                                $reqItem->quantity_approved = isset($itemData['quantity_approved']) ? floatval($itemData['quantity_approved']) : 0;
                                $reqItem->save();
                            }
                        }
                    }

                    $req->alternative_status = 'proposed';
                    if ($request->filled('admin_notes')) {
                        $req->admin_notes = $request->admin_notes;
                    }
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
            $logAction = auth()->user()->isDelegatedApprover() ? 'DELEGATED_PROPOSE_SUGGESTED_QTY' : 'PROPOSE_SUGGESTED_QTY';
            $logDesc = auth()->user()->isDelegatedApprover()
                ? "Store Officer " . auth()->user()->name . " (Delegated) proposed suggested quantities for store requisition #{$req->id} from department: {$req->department} on behalf of Admin."
                : "Administrator " . auth()->user()->name . " proposed suggested quantities for store requisition #{$req->id} from department: {$req->department}.";

            SystemLog::create([
                'user_id'    => auth()->id(),
                'event_type' => 'REQUISITION',
                'action'     => $logAction,
                'description'=> $logDesc,
                'severity'   => 'info',
                'metadata'   => ['requisition_id' => $req->id],
                'ip_address' => $request->ip(),
            ]);

            // Notify originating department head(s)
            $deptHeads = User::whereIn('role', ['Main Admin', 'Department Head'])
                ->where('department', $req->department)
                ->where('is_active', true)
                ->get();
            
            $priorityLabel = strtoupper($req->priority);
            $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #ea580c;border-radius:12px;background:rgba(234,88,12,0.05);'>";
            $msg .= "<b style='color:#ea580c;'>🔔 SUGGESTED QUANTITY PROPOSED — Ref: #{$req->id}</b><br><br>";
            $msg .= "The Head of Stores has suggested modified quantities for your department's store requisition Ref: #<b>{$req->id}</b>.<br><br>";
            $msg .= "<b>Requester:</b> {$req->requester_name}<br>";
            $msg .= "<b>Purpose:</b> " . e($req->purpose) . "<br>";
            if ($request->filled('admin_notes')) {
                $msg .= "<b>Stores Notes:</b> " . e($request->admin_notes) . "<br><br>";
            }
            $msg .= "Please review the suggested quantities and either <b>Agree</b> or <b>Decline</b> the proposal.<br><br>";
            $msg .= "<a href='" . route('main-admin.requisitions') . "?open_id={$req->id}' style='display:inline-block;background:#ea580c;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Review Quantity Suggestion</a>";
            $msg .= "</div>";

            foreach ($deptHeads as $head) {
                Message::create([
                    'sender_id'    => auth()->id(),
                    'receiver_id'  => $head->id,
                    'message'      => $msg,
                    'is_automated' => true,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Suggested quantity proposal sent successfully to the department head of {$req->department}.",
            ]);
        }

        try {
            $req = \Illuminate\Support\Facades\DB::transaction(function() use ($request, $id) {
                $req = StoreRequisition::with('items')->findOrFail($id);
                if ($req->main_admin_status !== 'approved') {
                    throw new \Exception('This requisition must be verified and approved by the Department Head first.');
                }

                $targetStatus = $request->status;
                if (in_array($targetStatus, ['approved', 'partially_approved'])) {
                    if ($req->requires_dg_approval && $req->dg_status !== 'approved') {
                        throw new \Exception('This requisition requires Director General (DG) approval before final store processing.');
                    }
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
                                        ->where('inventory_batches.approval_status', '=', 'approved')
                                        ->where(\DB::raw('TRIM(inventory_items.description)'), trim($origItemName))
                                        ->selectRaw('SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
                                        ->value('total_stock') ?? 0;

                                    if ($approvedQty > $totalStock) {
                                        throw new \Exception("Cannot approve {$approvedQty} for '{$origItemName}'. Only {$totalStock} is available in stock.");
                                    }

                                    $qtyToDeduct = $approvedQty;
                                    $stockItems = InventoryItem::where(\DB::raw('TRIM(description)'), trim($origItemName))
                                        ->whereHas('batch', function ($q) use ($reqItem) {
                                            $q->where('supplier_status', '!=', 'System Draft')
                                                ->where('approval_status', '=', 'approved');
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
                                        ->where('inventory_batches.approval_status', '=', 'approved')
                                        ->where(\DB::raw('TRIM(inventory_items.description)'), trim($altItemName))
                                        ->selectRaw('SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
                                        ->value('total_stock') ?? 0;

                                    if ($altApprovedQty > $totalAltStock) {
                                        throw new \Exception("Cannot approve alternative {$altApprovedQty} for '{$altItemName}'. Only {$totalAltStock} is available in stock.");
                                    }

                                    $qtyToDeduct = $altApprovedQty;
                                    $stockItems = InventoryItem::where(\DB::raw('TRIM(description)'), trim($altItemName))
                                        ->whereHas('batch', function ($q) use ($reqItem) {
                                            $q->where('supplier_status', '!=', 'System Draft')
                                                ->where('approval_status', '=', 'approved');
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
        $logAction = auth()->user()->isDelegatedApprover() ? 'DELEGATED_PROCESS_REQUISITION' : 'PROCESS_REQUISITION';
        $logDesc = auth()->user()->isDelegatedApprover()
            ? "Store Officer " . auth()->user()->name . " (Delegated) {$request->status} store requisition #{$req->id} from {$req->department} on behalf of Admin."
            : "Administrator " . auth()->user()->name . " {$request->status} store requisition #{$req->id} from {$req->department}.";

        SystemLog::create([
            'user_id'    => auth()->id(),
            'event_type' => 'REQUISITION',
            'action'     => $logAction,
            'description'=> $logDesc,
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
            ->orderByRaw("CASE WHEN status = 'pending' THEN 1 WHEN status = 'partially_approved' THEN 2 WHEN status = 'approved' THEN 3 WHEN status = 'declined' THEN 4 ELSE 5 END")
            ->orderByRaw("CASE WHEN priority = 'urgent' THEN 1 WHEN priority = 'normal' THEN 2 WHEN priority = 'low' THEN 3 ELSE 4 END")
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

        $statsData = StoreRequisition::selectRaw("
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'partially_approved' THEN 1 ELSE 0 END) as partially_approved,
            SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) as declined,
            SUM(CASE WHEN status = 'pending' AND priority = 'urgent' THEN 1 ELSE 0 END) as urgent
        ")->first();

        $stats = [
            'pending'            => (int) ($statsData->pending ?? 0),
            'approved'           => (int) ($statsData->approved ?? 0),
            'partially_approved' => (int) ($statsData->partially_approved ?? 0),
            'declined'           => (int) ($statsData->declined ?? 0),
            'urgent'             => (int) ($statsData->urgent ?? 0),
        ];

        // Available inventory items for placing new requisitions from this page
        $availableItems = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->where('inventory_batches.approval_status', '=', 'approved')
            ->selectRaw('TRIM(inventory_items.description) as description, MAX(inventory_items.unit) as unit, inventory_batches.ledge_category, SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
            ->groupBy(\DB::raw('TRIM(inventory_items.description)'), 'inventory_batches.ledge_category')
            ->orderByRaw('TRIM(inventory_items.description)')
            ->get()
            ->map(function ($item) {
                $physicalStock = (float) $item->total_stock;
                $item->total_stock = \App\Models\Setting::getAvailableStock($item->description, $physicalStock, $item->ledge_category);
                return $item;
            });

        return view('requisitions.personnel', compact('requisitions', 'ledgeMap', 'stats', 'availableItems'));
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
        self::checkOverdueTemporaryItems();
        if (!in_array(auth()->user()->role, ['Main Admin', 'Department Head', 'Head of Stores'])) abort(403);

        $isStoresHead = (auth()->user()->role === 'Main Admin' || auth()->user()->role === 'Head of Stores' || strcasecmp(auth()->user()->department ?? '', 'Stores') === 0 || strcasecmp(auth()->user()->department ?? '', 'Store') === 0);
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

        $query = StoreRequisition::with(['items', 'requester', 'processor', 'collector'])
            ->orderByRaw("CASE WHEN priority = 'urgent' THEN 1 WHEN priority = 'normal' THEN 2 WHEN priority = 'low' THEN 3 ELSE 4 END")
            ->orderBy('created_at', 'desc');

        // Apply department scoping for originating heads
        if (!$isStoresHead) {
            $query->where('department', auth()->user()->department);
        }

        $hasActiveStoresHead = \App\Models\User::where('role', 'Head of Stores')->where('is_active', true)->exists()
            || \App\Models\User::where('role', 'Department Head')->whereIn('department', ['Stores', 'Store'])->where('is_active', true)->exists();
        $isStoresHOD = (auth()->user()->role === 'Head of Stores')
            || (auth()->user()->role === 'Department Head' && in_array(auth()->user()->department, ['Stores', 'Store']))
            || (auth()->user()->role === 'Main Admin' && !$hasActiveStoresHead);

        $isBackupActive = $isStoresHead && !in_array(strtoupper(auth()->user()->department ?? ''), ['STORES', 'STORE']);

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                if ($isStoresHead) {
                    $query->where('status', 'pending')
                          ->where(function($q) use ($isStoresHOD, $isBackupActive) {
                              $q->where(function($q2) {
                                  $q2->where('origin_admin_status', 'approved')
                                     ->where('main_admin_status', 'pending');
                              });
                              if ($isStoresHOD) {
                                  $q->orWhere(function($q2) {
                                      $q2->where('origin_admin_status', 'pending')
                                         ->whereIn('department', ['Stores', 'Store']);
                                  });
                              }
                              if ($isBackupActive) {
                                  $q->orWhere(function($q2) {
                                      $q2->where('department', auth()->user()->department)
                                         ->where(function($q3) {
                                             $q3->where('origin_admin_status', 'pending')
                                                ->orWhere('alternative_status', 'proposed');
                                         });
                                  });
                              }
                          });
                } else {
                    $query->where(function($q) {
                        $q->where(function($qp) {
                            $qp->where('status', 'pending')->where('origin_admin_status', 'pending');
                        })->orWhere(function($qp) {
                            $qp->where('status', 'pending')->where('alternative_status', 'proposed');
                        });
                    });
                }
            } elseif ($request->status === 'approved') {
                if ($isStoresHead) {
                    if ($isBackupActive) {
                        $query->where(function($q) {
                            $q->where('main_admin_status', 'approved')
                              ->orWhere(function($q2) {
                                  $q2->where('department', auth()->user()->department)
                                     ->where('origin_admin_status', 'approved');
                              });
                        });
                    } else {
                        $query->where('main_admin_status', 'approved');
                    }
                } else {
                    $query->where('origin_admin_status', 'approved');
                }
            } elseif ($request->status === 'declined') {
                if ($isStoresHead) {
                    if ($isBackupActive) {
                        $query->where(function($q) {
                            $q->where('main_admin_status', 'declined')
                              ->orWhere(function($q2) {
                                  $q2->where('department', auth()->user()->department)
                                     ->where('origin_admin_status', 'declined');
                              });
                        });
                    } else {
                        $query->where('main_admin_status', 'declined');
                    }
                } else {
                    $query->where('origin_admin_status', 'declined');
                }
            } elseif ($request->status === 'history') {
                if ($isStoresHead) {
                    if ($isBackupActive) {
                        $query->where(function($q) {
                            $q->whereIn('main_admin_status', ['approved', 'declined'])
                              ->orWhere(function($q2) {
                                  $q2->where('department', auth()->user()->department)
                                     ->whereIn('origin_admin_status', ['approved', 'declined']);
                              });
                        });
                    } else {
                        $query->whereIn('main_admin_status', ['approved', 'declined']);
                    }
                } else {
                    $query->whereIn('origin_admin_status', ['approved', 'declined']);
                }
            }
        } else {
            if ($isStoresHead) {
                $query->where('status', 'pending')
                      ->where(function($q) use ($isStoresHOD, $isBackupActive) {
                          $q->where(function($q2) {
                              $q2->where('origin_admin_status', 'approved')
                                 ->where('main_admin_status', 'pending');
                          });
                          if ($isStoresHOD) {
                              $q->orWhere(function($q2) {
                                  $q2->where('origin_admin_status', 'pending')
                                     ->whereIn('department', ['Stores', 'Store']);
                              });
                          }
                          if ($isBackupActive) {
                              $q->orWhere(function($q2) {
                                  $q2->where('department', auth()->user()->department)
                                     ->where(function($q3) {
                                         $q3->where('origin_admin_status', 'pending')
                                            ->orWhere('alternative_status', 'proposed');
                                     });
                              });
                          }
                      });
            } else {
                $query->where(function($q) {
                    $q->where(function($qp) {
                        $qp->where('status', 'pending')->where('origin_admin_status', 'pending');
                    })->orWhere(function($qp) {
                        $qp->where('status', 'pending')->where('alternative_status', 'proposed');
                    });
                });
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('department')) {
            $query->where('department', 'LIKE', '%' . $request->department . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requisitions = $query->paginate(15)->withQueryString();

        $ledgeMap = Setting::getCategories();

        // Calculate scoped stats
        if ($isStoresHead) {
            if ($isBackupActive) {
                $statsData = StoreRequisition::selectRaw("
                    SUM(CASE WHEN status = 'pending' AND (
                        (origin_admin_status = 'approved' AND main_admin_status = 'pending')
                        OR (department = ? AND (origin_admin_status = 'pending' OR alternative_status = 'proposed'))
                    ) THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN main_admin_status = 'approved' OR (department = ? AND origin_admin_status = 'approved') THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN main_admin_status = 'declined' OR (department = ? AND origin_admin_status = 'declined') THEN 1 ELSE 0 END) as declined
                ", [auth()->user()->department, auth()->user()->department, auth()->user()->department])->first();
            } else {
                $statsData = StoreRequisition::selectRaw("
                    SUM(CASE WHEN store_requisitions.status = 'pending' AND (
                        (store_requisitions.origin_admin_status = 'approved' AND store_requisitions.main_admin_status = 'pending') " . ($isStoresHOD ? "OR (store_requisitions.origin_admin_status = 'pending' AND store_requisitions.department IN ('Stores', 'Store'))" : "") . "
                    ) THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN store_requisitions.main_admin_status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN store_requisitions.main_admin_status = 'declined' THEN 1 ELSE 0 END) as declined
                ")->first();
            }

            $stats = [
                'pending'  => (int) ($statsData->pending ?? 0),
                'approved' => (int) ($statsData->approved ?? 0),
                'declined' => (int) ($statsData->declined ?? 0),
            ];
        } else {
            $statsData = StoreRequisition::where('department', auth()->user()->department)
                ->selectRaw("
                    SUM(CASE WHEN (status = 'pending' AND origin_admin_status = 'pending') OR (status = 'pending' AND alternative_status = 'proposed') THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN origin_admin_status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN origin_admin_status = 'declined' THEN 1 ELSE 0 END) as declined
                ")->first();

            $stats = [
                'pending'  => (int) ($statsData->pending ?? 0),
                'approved' => (int) ($statsData->approved ?? 0),
                'declined' => (int) ($statsData->declined ?? 0),
            ];
        }

        $hasOverdueReturn = false;
        if (auth()->user()->role === 'Department Head' && !$isStoresHead) {
            $hasOverdueReturn = \App\Http\Controllers\TempRequisitionerController::hasOverdueReturn(auth()->user()->department);
        }

        // AJAX request — return rendered partials as JSON
        if ($request->ajax()) {
            $rowsHtml = view('requisitions._req_table_rows', compact('requisitions'))->render();
            $paginationHtml = $requisitions->hasPages()
                ? view('requisitions._req_pagination', compact('requisitions'))->render()
                : '';
            return response()->json([
                'rows'       => $rowsHtml,
                'pagination' => $paginationHtml,
                'total'      => $requisitions->total(),
                'from'       => $requisitions->firstItem() ?? 0,
                'to'         => $requisitions->lastItem() ?? 0,
            ]);
        }

        return view('requisitions.main_admin', compact('requisitions', 'ledgeMap', 'stats', 'hasOverdueReturn'));

    }

    /**
     * Main Admin: Process (approve/decline) a requisition request first.
     */
    public function mainAdminProcess(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['Main Admin', 'Department Head', 'Head of Stores'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Permission gate: admin may revoke a dept head's ability to approve requisitions
        if (auth()->user()->role === 'Department Head' && isset(auth()->user()->can_approve_requisition) && !auth()->user()->can_approve_requisition) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to approve or decline requisition requests. Please contact your administrator.'], 403);
        }

        $request->validate([
            'status'         => 'required|in:approved,declined',
            'admin_notes'    => 'nullable|string|max:1000',
            'decline_reason' => 'nullable|string|max:2000',
        ]);

        $req = StoreRequisition::findOrFail($id);

        $isStoresHead = (auth()->user()->role === 'Main Admin' || auth()->user()->role === 'Head of Stores' || strcasecmp(auth()->user()->department ?? '', 'Stores') === 0 || strcasecmp(auth()->user()->department ?? '', 'Store') === 0);
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

        $hasActiveStoresHead = \App\Models\User::where('role', 'Head of Stores')->where('is_active', true)->exists()
            || \App\Models\User::where('role', 'Department Head')->whereIn('department', ['Stores', 'Store'])->where('is_active', true)->exists();
        $isStoresHOD = (auth()->user()->role === 'Head of Stores')
            || (auth()->user()->role === 'Department Head' && in_array(auth()->user()->department, ['Stores', 'Store']))
            || (auth()->user()->role === 'Main Admin' && !$hasActiveStoresHead);

        // Check if the stores head is acting as the originating HOD for a Stores department request
        $isActingAsOriginHOD = ($isStoresHOD && (strcasecmp($req->department, 'Stores') === 0 || strcasecmp($req->department, 'Store') === 0) && $req->origin_admin_status === 'pending')
            || (auth()->user()->role === 'Department Head' && $req->department === auth()->user()->department && $req->origin_admin_status === 'pending');

        if (!$isStoresHead || $isActingAsOriginHOD) {
            // Originating department head check
            if (!$isActingAsOriginHOD && (strcasecmp($req->department, 'Stores') === 0 || strcasecmp($req->department, 'Store') === 0)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized Stores Department HOD action.'], 403);
            }
            if (!$isActingAsOriginHOD && $req->department !== auth()->user()->department) {
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
            $isActingAsOriginHOD = ($isStoresHOD && (strcasecmp($req->department, 'Stores') === 0 || strcasecmp($req->department, 'Store') === 0) && $req->origin_admin_status === 'pending')
                || (auth()->user()->role === 'Department Head' && $req->department === auth()->user()->department && $req->origin_admin_status === 'pending');

            $requiresStoresDeptHeadApproval = false;
            if (!$isStoresHead || $isActingAsOriginHOD) {
                $storesApprovalCategories = \App\Models\Setting::get('stores_dept_head_approval_categories', []);
                if (!is_array($storesApprovalCategories)) {
                    $storesApprovalCategories = json_decode($storesApprovalCategories, true) ?? [];
                }
                foreach ($req->items as $item) {
                    if (in_array($item->category, $storesApprovalCategories)) {
                        $requiresStoresDeptHeadApproval = true;
                        break;
                    }
                }
            }

            if (!$isStoresHead || $isActingAsOriginHOD) {
                $req->origin_admin_status = 'approved';
                $req->origin_approved_by = auth()->user()->name;
                if ($requiresStoresDeptHeadApproval) {
                    $actionName = 'DEPT_HEAD_APPROVE';
                    $logDesc = "Department Head " . auth()->user()->name . " approved store requisition #{$req->id} from department: {$req->department}. Escalated to Department Head (Stores).";
                } else {
                    $req->main_admin_status = 'approved';
                    $actionName = 'DEPT_HEAD_APPROVE_BYPASS';
                    $logDesc = "Department Head " . auth()->user()->name . " approved store requisition #{$req->id} from department: {$req->department}. Bypassed Department Head (Stores) (Categories do not require approval) and escalated to Head of Stores.";
                }
            } else {
                $req->main_admin_status = 'approved';
                $req->stores_approved_by = auth()->user()->name;
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
            if (!$isStoresHead || $isActingAsOriginHOD) {
                if ($requiresStoresDeptHeadApproval) {
                    // Notify Department Head (Stores)
                    $storesHeads = User::whereIn('role', ['Main Admin', 'Department Head'])
                        ->where(fn($q) => $q->where('department', 'Stores')->orWhere('department', 'Store'))
                        ->where('is_active', true)
                        ->get();
                    foreach ($storesHeads as $storesHead) {
                        $priorityLabel = strtoupper($req->priority);
                        $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #10b981;border-radius:12px;background:rgba(16,185,129,0.05);'>";
                        $msg .= "<b style='color:#10b981;'>📋 DEPT HEAD APPROVED REQUISITION — {$priorityLabel} PRIORITY</b><br><br>";
                        $msg .= "Department Head <b>" . auth()->user()->name . "</b> has approved store requisition Ref: #<b>{$req->id}</b>.<br><br>";
                        $msg .= "<b>Department:</b> {$req->department}<br>";
                        $msg .= "<b>Requested by:</b> {$req->requester_name}<br>";
                        $msg .= "<b>Purpose:</b> " . e($req->purpose) . "<br>";
                        if ($request->filled('admin_notes')) {
                            $msg .= "<b>Dept Head Notes:</b> " . e($request->admin_notes) . "<br><br>";
                        }
                        $msg .= "<a href='" . route('main-admin.requisitions') . "?open_id={$req->id}' style='display:inline-block;background:#10b981;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Review Requisition</a>";
                        $msg .= "</div>";

                        Message::create([
                            'sender_id'    => auth()->id(),
                            'receiver_id'  => $storesHead->id,
                            'message'      => $msg,
                            'is_automated' => true,
                        ]);
                    }
                } else {
                    // Notify DG or Head of Stores (Admin) directly since Stores Dept Head approval is bypassed!
                    if ($req->requires_dg_approval) {
                        $dgs = User::where('role', 'Director General')->where('is_active', true)->get();
                        foreach ($dgs as $dg) {
                            $priorityLabel = strtoupper($req->priority);
                            $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #8b5cf6;border-radius:12px;background:rgba(139,92,246,0.05);'>";
                            $msg .= "<b style='color:#8b5cf6;'>📋 NEW REQUISITION AWAITING DG APPROVAL — {$priorityLabel} PRIORITY</b><br><br>";
                            $msg .= "Department Head <b>" . auth()->user()->name . "</b> has approved store requisition Ref: #<b>{$req->id}</b> (Stores Dept Head approval bypassed).<br><br>";
                            $msg .= "This requisition requires your command clearance.<br><br>";
                            $msg .= "<b>Department:</b> {$req->department}<br>";
                            $msg .= "<b>Requested by:</b> {$req->requester_name}<br>";
                            $msg .= "<b>Purpose:</b> " . e($req->purpose) . "<br><br>";
                            $msg .= "<a href='" . route('dg.dashboard') . "?open_id={$req->id}' style='display:inline-block;background:#8b5cf6;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Review Requisition</a>";
                            $msg .= "</div>";

                            Message::create([
                                'sender_id'    => auth()->id(),
                                'receiver_id'  => $dg->id,
                                'message'      => $msg,
                                'is_automated' => true,
                            ]);
                        }
                    } else {
                        $admins = User::getApproversQuery()->where('is_active', true)->get();
                        foreach ($admins as $admin) {
                            $priorityLabel = strtoupper($req->priority);
                            $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #10b981;border-radius:12px;background:rgba(16,185,129,0.05);'>";
                            $msg .= "<b style='color:#10b981;'>📋 REQUISITION BYPASSED DEPT. HEAD (STORES) — {$priorityLabel} PRIORITY</b><br><br>";
                            $msg .= "Department Head <b>" . auth()->user()->name . "</b> has approved store requisition Ref: #<b>{$req->id}</b>.<br><br>";
                            $msg .= "This requisition does not require Stores Department Head approval and has been escalated directly to you.<br><br>";
                            $msg .= "<b>Department:</b> {$req->department}<br>";
                            $msg .= "<b>Requested by:</b> {$req->requester_name}<br>";
                            $msg .= "<b>Purpose:</b> " . e($req->purpose) . "<br>";
                            if ($request->filled('admin_notes')) {
                                $msg .= "<b>Dept Head Notes:</b> " . e($request->admin_notes) . "<br><br>";
                            }
                            $msg .= "<a href='" . route('admin.requisitions') . "?open_id={$req->id}' style='display:inline-block;background:#10b981;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Perform Final Head Review</a>";
                            $msg .= "</div>";

                            Message::create([
                                'sender_id'    => auth()->id(),
                                'receiver_id'  => $admin->id,
                                'message'      => $msg,
                                'is_automated' => true,
                            ]);
                        }
                    }
                }
            } else {
                // Notify DG or Head of Stores (Admin)
                if ($req->requires_dg_approval) {
                    $dgs = User::where('role', 'Director General')->where('is_active', true)->get();
                    foreach ($dgs as $dg) {
                        $priorityLabel = strtoupper($req->priority);
                        $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #8b5cf6;border-radius:12px;background:rgba(139,92,246,0.05);'>";
                        $msg .= "<b style='color:#8b5cf6;'>📋 NEW REQUISITION AWAITING DG APPROVAL — {$priorityLabel} PRIORITY</b><br><br>";
                        $msg .= "Stores Department Head <b>" . auth()->user()->name . "</b> has approved store requisition Ref: #<b>{$req->id}</b>.<br><br>";
                        $msg .= "This requisition requires your command clearance.<br><br>";
                        $msg .= "<b>Department:</b> {$req->department}<br>";
                        $msg .= "<b>Requested by:</b> {$req->requester_name}<br>";
                        $msg .= "<b>Purpose:</b> " . e($req->purpose) . "<br><br>";
                        $msg .= "<a href='" . route('dg.dashboard') . "?open_id={$req->id}' style='display:inline-block;background:#8b5cf6;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Review Requisition</a>";
                        $msg .= "</div>";

                        Message::create([
                            'sender_id'    => auth()->id(),
                            'receiver_id'  => $dg->id,
                            'message'      => $msg,
                            'is_automated' => true,
                        ]);
                    }
                } else {
                    $admins = User::getApproversQuery()->where('is_active', true)->get();
                    foreach ($admins as $admin) {
                        $priorityLabel = strtoupper($req->priority);
                        $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #10b981;border-radius:12px;background:rgba(16,185,129,0.05);'>";
                        $msg .= "<b style='color:#10b981;'>📋 MAIN ADMIN APPROVED REQUISITION — {$priorityLabel} PRIORITY</b><br><br>";
                        $msg .= "Stores Department Head <b>" . auth()->user()->name . "</b> has approved store requisition Ref: #<b>{$req->id}</b>.<br><br>";
                        $msg .= "<b>Department:</b> {$req->department}<br>";
                        $msg .= "<b>Requested by:</b> {$req->requester_name}<br>";
                        $msg .= "<b>Purpose:</b> " . e($req->purpose) . "<br>";
                        if ($request->filled('admin_notes')) {
                            $msg .= "<b>Stores Head Notes:</b> " . e($request->admin_notes) . "<br><br>";
                        }
                        $msg .= "<a href='" . route('admin.requisitions') . "?open_id={$req->id}' style='display:inline-block;background:#10b981;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Perform Final Head Review</a>";
                        $msg .= "</div>";

                        Message::create([
                            'sender_id'    => auth()->id(),
                            'receiver_id'  => $admin->id,
                            'message'      => $msg,
                            'is_automated' => true,
                        ]);
                    }
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

    /**
     * Main Admin / Dept Head: Agree or Decline the proposed alternative item.
     */
    public function mainAdminAlternativeResponse(Request $request, $id)
    {
        $request->validate([
            'response' => 'required|in:agree,decline',
            'notes'    => 'nullable|string|max:1000',
        ]);

        $req = StoreRequisition::findOrFail($id);

        if (auth()->user()->role !== 'Department Head' || $req->department !== auth()->user()->department) {
            return response()->json(['success' => false, 'message' => 'Unauthorized department access.'], 403);
        }

        if ($req->alternative_status !== 'proposed') {
            return response()->json(['success' => false, 'message' => 'No active alternative item proposal exists for this requisition.'], 400);
        }

        if ($request->response === 'agree') {
            $req->alternative_status = 'agreed';
            if ($request->filled('notes')) {
                $req->admin_notes = $request->notes;
            }
            $req->save();

            // Log
            SystemLog::create([
                'user_id'    => auth()->id(),
                'event_type' => 'REQUISITION',
                'action'     => 'ALT_ITEM_AGREED',
                'description'=> "Department Head " . auth()->user()->name . " agreed to suggested quantity proposal for requisition #{$req->id}.",
                'severity'   => 'info',
                'metadata'   => ['requisition_id' => $req->id],
                'ip_address' => $request->ip(),
            ]);

            // Notify all admins/head of stores
            $admins = User::getApproversQuery()->where('is_active', true)->get();
            foreach ($admins as $admin) {
                $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #10b981;border-radius:12px;background:rgba(16,185,129,0.05);'>";
                $msg .= "<b style='color:#10b981;'>✅ SUGGESTED QUANTITY AGREED — Ref: #{$req->id}</b><br><br>";
                $msg .= "Department <b>{$req->department}</b> has <b>AGREED</b> to the suggested quantities.<br><br>";
                $msg .= "<b>Department Head Notes:</b> " . e($request->notes ?: 'None') . "<br><br>";
                $msg .= "<a href='" . route('admin.requisitions') . "?open_id={$req->id}' style='display:inline-block;background:#10b981;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Proceed to Commit Decision</a>";
                $msg .= "</div>";

                Message::create([
                    'sender_id'    => auth()->id(),
                    'receiver_id'  => $admin->id,
                    'message'      => $msg,
                    'is_automated' => true,
                ]);
            }
        } else {
            // Determine if the requisition requires Stores Department Head approval
            $storesApprovalCategories = \App\Models\Setting::get('stores_dept_head_approval_categories', []);
            if (!is_array($storesApprovalCategories)) {
                $storesApprovalCategories = json_decode($storesApprovalCategories, true) ?? [];
            }
            $requiresStoresDeptHeadApproval = false;
            foreach ($req->items as $item) {
                if (in_array($item->category, $storesApprovalCategories)) {
                    $requiresStoresDeptHeadApproval = true;
                    break;
                }
            }

            $req->alternative_status = 'declined';
            $req->status = 'declined';
            $req->origin_admin_status = 'declined';
            $req->main_admin_status = 'declined';
            $req->decline_reason = 'Department declined suggested quantity proposal. ' . ($request->notes ? 'Notes: ' . $request->notes : '');
            $req->save();

            // Log
            SystemLog::create([
                'user_id'    => auth()->id(),
                'event_type' => 'REQUISITION',
                'action'     => 'ALT_ITEM_DECLINED',
                'description'=> "Department Head " . auth()->user()->name . " declined suggested quantity proposal for requisition #{$req->id}.",
                'severity'   => 'warning',
                'metadata'   => ['requisition_id' => $req->id],
                'ip_address' => $request->ip(),
            ]);

            // Notify admins and selectively the main admin (Stores Department Head)
            // Head of stores (admin) always gets notified
            $recipients = User::getApproversQuery()->where('is_active', true)->get()->keyBy('id');

            // Stores Department Head (main-admin) gets notified if allowed to approve
            if ($requiresStoresDeptHeadApproval) {
                $storesHeads = User::whereIn('role', ['Main Admin', 'Department Head'])
                    ->where(fn($q) => $q->where('department', 'Stores')->orWhere('department', 'Store'))
                    ->where('is_active', true)
                    ->get();
                foreach ($storesHeads as $head) {
                    $recipients->put($head->id, $head);
                }
            }

            foreach ($recipients as $recipient) {
                $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #ef4444;border-radius:12px;background:rgba(239,68,68,0.05);'>";
                $msg .= "<b style='color:#ef4444;'>❌ SUGGESTED QUANTITY DECLINED — Ref: #{$req->id}</b><br><br>";
                $msg .= "Department <b>{$req->department}</b> has <b>DECLINED</b> the suggested quantities. Requisition has been marked as declined.<br><br>";
                $msg .= "<b>Department Head Notes:</b> " . e($request->notes ?: 'None') . "<br><br>";
                $msg .= "</div>";

                Message::create([
                    'sender_id'    => auth()->id(),
                    'receiver_id'  => $recipient->id,
                    'message'      => $msg,
                    'is_automated' => true,
                ]);
            }

            if ($req->requested_by) {
                $msg  = "<div class='personnel-view requisition-status-msg' style='padding:15px;border:1px solid #ef4444;border-radius:12px;background:rgba(239,68,68,0.02);'>";
                $msg .= "<b style='color:#ef4444;'>📋 REQUISITION DECLINED (Suggested Quantity Declined)</b><br><br>";
                $msg .= "Your store requisition (Ref: #{$req->id}) has been declined because the department head declined the suggested quantity proposed by stores.<br><br>";
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
            'message' => $request->response === 'agree' ? 'Suggested quantity proposal accepted. Requisition returned to stores.' : 'Suggested quantity proposal declined. Requisition marked as declined.',
        ]);
    }

    /**
     * Daily check for overdue and due temporary items, issuing alerts.
     */
    public static function checkOverdueTemporaryItems()
    {
        $cacheKey = 'overdue_temporary_items_checked';
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return;
        }

        // 1. Fetch all active temporary items checking out (using leftJoin and aggregation to check outstanding status)
        $activeItems = \App\Models\IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->join('store_requisitions', 'issuances.requisition_id', '=', 'store_requisitions.id')
            ->leftJoin('returned_items', 'issued_items.id', '=', 'returned_items.issued_item_id')
            ->select(
                'issued_items.*', 
                'store_requisitions.purpose', 
                'store_requisitions.department', 
                'store_requisitions.id as requisition_id', 
                'store_requisitions.requested_by', 
                'store_requisitions.created_at as req_created'
            )
            ->selectRaw('COALESCE(SUM(returned_items.returned_qty), 0) as total_returned')
            ->where('issuances.issuance_type', 'Temporary')
            ->where('issued_items.quantity', '>', 0)
            ->groupBy(
                'issued_items.id',
                'issued_items.issuance_id',
                'issued_items.description',
                'issued_items.ledge_category',
                'issued_items.quantity',
                'issued_items.unit',
                'issued_items.created_at',
                'issued_items.updated_at',
                'store_requisitions.purpose', 
                'store_requisitions.department', 
                'store_requisitions.id', 
                'store_requisitions.requested_by', 
                'store_requisitions.created_at'
            )
            ->havingRaw('issued_items.quantity > COALESCE(SUM(returned_items.returned_qty), 0)')
            ->get();

        if ($activeItems->isEmpty()) {
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, 43200); // Cache for 12 hours
            return;
        }

        // Pre-fetch all active department heads to prevent querying inside loop
        $deptHeads = \App\Models\User::where('role', 'Department Head')
            ->where('is_active', true)
            ->get()
            ->keyBy(fn($u) => strtolower(trim($u->department)));

        // Pre-fetch active stores staff to avoid querying it multiple times
        $storesStaff = \App\Models\User::where(function($q) {
            $q->where('is_admin', true)
              ->orWhereIn('role', ['Main Admin', 'Store Officer', 'Dept. Head (Stores)']);
        })->where('is_active', true)->get();

        // Pre-fetch all other department heads
        $allDeptHeads = \App\Models\User::where('role', 'Department Head')
            ->where('is_active', true)
            ->get();

        // Calculate the earliest requisition creation date among active temporary loan items
        $minDate = $activeItems->min(function($item) {
            return $item->req_created ? \Carbon\Carbon::parse($item->req_created) : null;
        });
        if (!$minDate) {
            $minDate = \Carbon\Carbon::now()->subMonths(3);
        } else {
            $minDate = $minDate->copy()->subDays(5); // Add a small safety buffer of 5 days
        }

        // Fetch all automated alert messages in a single query to run in-memory lookups
        $existingAlerts = \App\Models\Message::where('is_automated', true)
            ->where('created_at', '>=', $minDate)
            ->get();

        foreach ($activeItems as $item) {
            $returnedQty = (float)$item->total_returned;
            if ($item->quantity <= 0 || $returnedQty >= $item->quantity) {
                continue;
            }

            $returnDate = null;
            if (preg_match('/\[Expected Return Date:\s*([^\]]+)\]/i', $item->purpose, $matches)) {
                try {
                    $returnDate = \App\Models\Setting::parseExpectedReturnDate(trim($matches[1]));
                } catch (\Exception $e) {
                    // Ignore
                }
            }

            if (!$returnDate) {
                if ($item->req_created) {
                    $returnDate = \Carbon\Carbon::parse($item->req_created)->startOfDay();
                } else {
                    $returnDate = \Carbon\Carbon::now()->startOfDay();
                }
            }

            try {
                $today = \Carbon\Carbon::now()->startOfDay();
                $diffInDays = $today->diffInDays($returnDate, false); // false allows negative values if past due
            } catch (\Exception $e) {
                continue;
            }

            // Find recipient (Department Head)
            $deptKey = strtolower(trim($item->department));
            $recipient = $deptHeads->get($deptKey);
            $recipientId = $recipient ? $recipient->id : $item->requested_by;

            if ($diffInDays === 3) {
                // A: friendly countdown reminder (due in 3 days)
                $exists = $existingAlerts->contains(function($msg) use ($recipientId, $item) {
                    return $msg->receiver_id == $recipientId &&
                           strpos($msg->message, '⏳ RETURN COUNTDOWN: 3 DAYS REMAINING') !== false &&
                           strpos($msg->message, "Ref: #{$item->requisition_id}") !== false;
                });

                if (!$exists) {
                    // Send reminder to Department Head
                    $msg  = "<div class='department-view reminder-msg' style='padding:15px;border:1px solid #f59e0b;border-radius:12px;background:rgba(245,158,11,0.05);'>";
                    $msg .= "<b style='color:#d97706;'>⏳ RETURN COUNTDOWN: 3 DAYS REMAINING — Ref: #{$item->requisition_id}</b><br><br>";
                    $msg .= "Friendly Reminder: The temporary item <b>" . e($item->description) . "</b> collected by your department is due back in <b>3 days</b> (Expected Return Date: <b>" . e($returnDate->format('d/m/Y')) . "</b>).<br><br>";
                    $msg .= "Please ensure the item is returned to Central Store NACOC on time.";
                    $msg .= "</div>";

                    $newMsg = \App\Models\Message::create([
                        'sender_id' => 1, // System Admin
                        'receiver_id' => $recipientId,
                        'message' => $msg,
                        'is_automated' => true,
                    ]);
                    $existingAlerts->push($newMsg);

                    // Notify Department Head (Stores) & Head of Stores
                    foreach ($storesStaff as $staff) {
                        $adminMsg  = "<div class='admin-view notification-msg' style='padding:15px;border:1px solid #10b981;border-radius:12px;background:rgba(16,185,129,0.05);'>";
                        $adminMsg .= "<b style='color:#10b981;'>🔔 DELIVERY NOTIFICATION: 3-DAY REMINDER SENT</b><br><br>";
                        $adminMsg .= "The 3-day return countdown reminder for temporary item <b>" . e($item->description) . "</b> (Ref: #<b>" . e($item->requisition_id) . "</b>) has been successfully delivered to the <b>" . e($item->department) . "</b> Department Head.";
                        $adminMsg .= "</div>";

                        $newMsg = \App\Models\Message::create([
                            'sender_id' => 1,
                            'receiver_id' => $staff->id,
                            'message' => $adminMsg,
                            'is_automated' => true,
                        ]);
                        $existingAlerts->push($newMsg);
                    }
                }
            } elseif ($diffInDays <= 0) {
                // B: Overdue Return Reminder for Borrower Department Head
                $exists = $existingAlerts->contains(function($msg) use ($recipientId, $item) {
                    return $msg->receiver_id == $recipientId &&
                           strpos($msg->message, '🚨 OVERDUE RETURN NOTICE') !== false &&
                           strpos($msg->message, "Ref: #{$item->requisition_id}") !== false;
                });

                if (!$exists) {
                    $msg  = "<div class='department-view reminder-msg' style='padding:15px;border:1px solid #ef4444;border-radius:12px;background:rgba(239,68,68,0.05);'>";
                    $msg .= "<b style='color:#dc2626;'>🚨 OVERDUE RETURN NOTICE — Ref: #{$item->requisition_id}</b><br><br>";
                    $msg .= "Attention: The expected return date (<b>" . e($returnDate->format('d/m/Y')) . "</b>) for the temporary item <b>" . e($item->description) . "</b> collected by your department has passed, and the item has not yet been returned.<br><br>";
                    $msg .= "<span style='color:#b91c1c; font-weight:800;'>Crucial Access Blocked: Your department's requisition creation privileges have been suspended. Please return the item to the Central Store immediately to restore access.</span>";
                    $msg .= "</div>";

                    $newMsg = \App\Models\Message::create([
                        'sender_id' => 1,
                        'receiver_id' => $recipientId,
                        'message' => $msg,
                        'is_automated' => true,
                    ]);
                    $existingAlerts->push($newMsg);
                }

                // Notify OTHER Department Heads
                $otherDeptHeads = $allDeptHeads->filter(fn($u) => strcasecmp($u->department, $item->department) !== 0);

                foreach ($otherDeptHeads as $otherHead) {
                    $headExists = $existingAlerts->contains(function($msg) use ($otherHead, $item) {
                        return $msg->receiver_id == $otherHead->id &&
                               strpos($msg->message, '⚠️ DEPARTMENT SUSPENSION WARNING') !== false &&
                               strpos($msg->message, "Ref: #{$item->requisition_id}") !== false;
                    });

                    if (!$headExists) {
                        $otherHeadMsg  = "<div class='department-view warning-msg' style='padding:15px;border:1px solid #f59e0b;border-radius:12px;background:rgba(245,158,11,0.05);'>";
                        $otherHeadMsg .= "<b style='color:#d97706;'>⚠️ DEPARTMENT SUSPENSION WARNING — Ref: #{$item->requisition_id}</b><br><br>";
                        $otherHeadMsg .= "Please note: The <b>" . e($item->department) . "</b> department's requisition privileges have been suspended due to an overdue temporary loan for item: <b>" . e($item->description) . "</b>.<br><br>";
                        $otherHeadMsg .= "Ensure your own department's temporary loans are returned within their designated expected dates to prevent similar access lockout.";
                        $otherHeadMsg .= "</div>";

                        $newMsg = \App\Models\Message::create([
                            'sender_id' => 1,
                            'receiver_id' => $otherHead->id,
                            'message' => $otherHeadMsg,
                            'is_automated' => true,
                        ]);
                        $existingAlerts->push($newMsg);
                    }
                }

                // Notify Logistics/Stores Staff
                foreach ($storesStaff as $staff) {
                    $staffExists = $existingAlerts->contains(function($msg) use ($staff, $item) {
                        return $msg->receiver_id == $staff->id &&
                               strpos($msg->message, '🚨 SYSTEM LOCKOUT EXCEPTION') !== false &&
                               strpos($msg->message, "Ref: #{$item->requisition_id}") !== false;
                    });

                    if (!$staffExists) {
                        $staffMsg  = "<div class='admin-view notification-msg' style='padding:15px;border:1px solid #ef4444;border-radius:12px;background:rgba(239,68,68,0.05);'>";
                        $staffMsg .= "<b style='color:#dc2626;'>🚨 SYSTEM LOCKOUT EXCEPTION — Ref: #{$item->requisition_id}</b><br><br>";
                        $staffMsg .= "An overdue return has been detected for temporary item <b>" . e($item->description) . "</b> (Ref: #<b>" . e($item->requisition_id) . "</b>) which was due on <b>" . e($returnDate->format('d/m/Y')) . "</b>.<br><br>";
                        $staffMsg .= "The borrowing department (<b>" . e($item->department) . "</b>) has been locked out of requisition privileges. Please initiate asset recovery procedures immediately.";
                        $staffMsg .= "</div>";

                        $newMsg = \App\Models\Message::create([
                            'sender_id' => 1,
                            'receiver_id' => $staff->id,
                            'message' => $staffMsg,
                            'is_automated' => true,
                        ]);
                        $existingAlerts->push($newMsg);
                    }
                }
            }
        }

        \Illuminate\Support\Facades\Cache::put($cacheKey, true, 43200); // Cache for 12 hours
    }

    public function overdueAssets(Request $request)
    {
        self::checkOverdueTemporaryItems();

        $user = auth()->user();
        $isStoresHead = ($user->role === 'Main Admin' || $user->role === 'Head of Stores' || strcasecmp($user->department ?? '', 'Stores') === 0 || strcasecmp($user->department ?? '', 'Store') === 0);
        if (!$isStoresHead) {
            $isBackup = ($user->role === 'Department Head' && in_array($user->department, ['Human Resource Management Department', 'Welfare Department']));
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

        // Fetch all temporary issued items
        $query = \App\Models\IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->join('store_requisitions', 'issuances.requisition_id', '=', 'store_requisitions.id')
            ->select(
                'issued_items.id as issued_item_id',
                'issued_items.description',
                'issued_items.ledge_category',
                'issued_items.quantity as qty_issued',
                'store_requisitions.purpose',
                'store_requisitions.department',
                'store_requisitions.id as requisition_id',
                'store_requisitions.requester_name',
                'store_requisitions.created_at as req_created',
                'issuances.issuance_date',
                \DB::raw('(SELECT COALESCE(SUM(returned_qty), 0) FROM returned_items WHERE returned_items.issued_item_id = issued_items.id) as total_returned')
            )
            ->where('issuances.issuance_type', 'Temporary')
            ->where('issued_items.quantity', '>', 0);

        // If not stores head, restrict to the user's department
        if (!$isStoresHead) {
            $query->where('store_requisitions.department', $user->department);
        }

        $allIssuedItems = $query->orderBy('issuances.created_at', 'desc')->get();

        $overdueItems = collect();
        $dueSoonItems = collect();

        $ledgeMap = Setting::getCategories();

        foreach ($allIssuedItems as $item) {
            $returnedQty = floatval($item->total_returned);
            $outstandingQty = $item->qty_issued - $returnedQty;

            // Only show items with outstanding quantities to return
            if ($outstandingQty <= 0) {
                continue;
            }

            $returnDate = null;
            if (preg_match('/\[Expected Return Date:\s*([^\]]+)\]/i', $item->purpose, $matches)) {
                try {
                    $returnDate = \App\Models\Setting::parseExpectedReturnDate(trim($matches[1]))->startOfDay();
                } catch (\Exception $e) {
                    // Ignore
                }
            }

            if (!$returnDate) {
                if ($item->req_created) {
                    $returnDate = \Carbon\Carbon::parse($item->req_created)->startOfDay();
                } else {
                    $returnDate = \Carbon\Carbon::now()->startOfDay();
                }
            }

            $today = \Carbon\Carbon::now()->startOfDay();
            $diffInDays = $today->diffInDays($returnDate, false); // Negative if overdue (returnDate < today)

            $itemData = (object) [
                'issued_item_id' => $item->issued_item_id,
                'requisition_id' => $item->requisition_id,
                'description'    => $item->description,
                'category'       => $ledgeMap[$item->ledge_category] ?? "Category {$item->ledge_category}",
                'qty_issued'     => $item->qty_issued,
                'qty_returned'   => $returnedQty,
                'qty_outstanding'=> $outstandingQty,
                'expected_return'=> $returnDate->format('d/m/Y'),
                'department'     => $item->department,
                'requester_name' => $item->requester_name,
                'issuance_date'  => $item->issuance_date ? \Carbon\Carbon::parse($item->issuance_date)->format('d/m/Y') : '-',
                'days_diff'      => $diffInDays,
            ];

            if ($diffInDays < 0) {
                // Return date is in the past -> Overdue!
                $overdueItems->push($itemData);
            } else {
                // Return date is in the future or today -> Due Soon!
                $dueSoonItems->push($itemData);
            }
        }

        // Sort overdue items by severity (most overdue first, which has lowest negative diffInDays)
        $overdueItems = $overdueItems->sortBy('days_diff')->values();
        // Sort due soon items by closest due date first (lowest positive diffInDays)
        $dueSoonItems = $dueSoonItems->sortBy('days_diff')->values();

        return view('requisitions.overdue', compact('overdueItems', 'dueSoonItems', 'isStoresHead'));
    }

    /**
     * Main Admin / Dept Head: Track staff requests pipeline.
     */
    public function trackRequests(Request $request)
    {
        if (!in_array(auth()->user()->role, ['Main Admin', 'Department Head', 'Head of Stores'])) {
            abort(403);
        }

        $isStoresHead = (auth()->user()->role === 'Main Admin' || auth()->user()->role === 'Head of Stores' || strcasecmp(auth()->user()->department ?? '', 'Stores') === 0 || strcasecmp(auth()->user()->department ?? '', 'Store') === 0);
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

        $query = StoreRequisition::with(['items', 'requester', 'processor', 'collector'])
            ->orderBy('created_at', 'desc');

        // Apply department scoping for HODs
        if (!$isStoresHead) {
            $query->where('department', auth()->user()->department);
        }

        // Apply custom status tracking filters
        if ($request->filled('tracking_status')) {
            $ts = $request->tracking_status;
            if ($ts === 'awaiting_hod') {
                $query->where('origin_admin_status', 'pending')->where('status', 'pending');
            } elseif ($ts === 'awaiting_stores') {
                $query->where('origin_admin_status', 'approved')->where('main_admin_status', 'pending')->where('status', 'pending');
            } elseif ($ts === 'ready_collection') {
                $query->whereIn('status', ['approved', 'partially_approved'])->whereNull('collected_at');
            } elseif ($ts === 'collected') {
                $query->whereNotNull('collected_at');
            } elseif ($ts === 'declined') {
                $query->where(function($q) {
                    $q->where('status', 'declined')
                      ->orWhere('origin_admin_status', 'declined')
                      ->orWhere('main_admin_status', 'declined');
                });
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('department')) {
            $query->where('department', 'LIKE', '%' . $request->department . '%');
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $parsedId = preg_replace('/[^0-9]/', '', $search);
                if (!empty($parsedId)) {
                    $q->where('id', $parsedId);
                }
                $q->orWhere('requester_name', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('items', function($ki) use ($search) {
                      $ki->where('description', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requisitions = $query->paginate(12)->withQueryString();
        $ledgeMap = Setting::getCategories();

        // Calculate statistics for the tracking page
        $statsQuery = StoreRequisition::query();
        if (!$isStoresHead) {
            $statsQuery->where('department', auth()->user()->department);
        }

        $statsData = (clone $statsQuery)->selectRaw("
            SUM(CASE WHEN origin_admin_status = 'pending' AND status = 'pending' THEN 1 ELSE 0 END) as awaiting_hod,
            SUM(CASE WHEN origin_admin_status = 'approved' AND main_admin_status = 'pending' AND status = 'pending' THEN 1 ELSE 0 END) as awaiting_stores,
            SUM(CASE WHEN (status = 'approved' OR status = 'partially_approved') AND collected_at IS NULL THEN 1 ELSE 0 END) as ready_collection,
            SUM(CASE WHEN collected_at IS NOT NULL THEN 1 ELSE 0 END) as collected,
            SUM(CASE WHEN status = 'declined' OR origin_admin_status = 'declined' OR main_admin_status = 'declined' THEN 1 ELSE 0 END) as declined
        ")->first();

        $stats = [
            'awaiting_hod'      => (int) ($statsData->awaiting_hod ?? 0),
            'awaiting_stores'   => (int) ($statsData->awaiting_stores ?? 0),
            'ready_collection'  => (int) ($statsData->ready_collection ?? 0),
            'collected'         => (int) ($statsData->collected ?? 0),
            'declined'          => (int) ($statsData->declined ?? 0),
            'total'             => $requisitions->total(),
        ];

        if ($request->ajax()) {
            $cardsHtml = view('requisitions._track_cards', compact('requisitions', 'isStoresHead'))->render();
            $paginationHtml = $requisitions->hasPages()
                ? view('requisitions._req_pagination', compact('requisitions'))->render()
                : '';
            return response()->json([
                'cards'      => $cardsHtml,
                'pagination' => $paginationHtml,
                'total'      => $requisitions->total(),
                'from'       => $requisitions->firstItem() ?? 0,
                'to'         => $requisitions->lastItem() ?? 0,
                'stats'      => $stats
            ]);
        }

        return view('requisitions.track', compact('requisitions', 'ledgeMap', 'stats', 'isStoresHead'));
    }
}

