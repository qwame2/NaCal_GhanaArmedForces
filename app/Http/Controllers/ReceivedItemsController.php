<?php

namespace App\Http\Controllers;

use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReceivedItemsController extends Controller
{
    private function getLedgeMap()
    {
        return \Illuminate\Support\Facades\Schema::hasTable('settings') 
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
    }

    public function preview($id)
    {
        $response = $this->previewApi($id);
        if ($response->getStatusCode() !== 200) {
            abort($response->getStatusCode());
        }
        $data = json_decode($response->getContent(), true);
        
        $admin = auth()->user();
        $history = collect(); 

        return view('received-items.preview_details', compact('data', 'admin', 'history'));
    }

    public function previewApi($id)
    {
        \App\Models\InventoryBatch::selfHealSchema();
        $isStoresHead = (auth()->user()->role === 'Main Admin' || strcasecmp(auth()->user()->department ?? '', 'Stores') === 0 || strcasecmp(auth()->user()->department ?? '', 'Store') === 0);
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head']) && !$isStoresHead) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $req = \App\Models\EditRequest::with('user')->findOrFail($id);
        $data = json_decode($req->payload, true);
        $ledgeMap = $this->getLedgeMap();
        $suppliersRegistry = \App\Models\Supplier::all();
        
        if ($req->request_type === 'issue_submission') {
            if (isset($data['items']) && is_array($data['items'])) {
                $descriptions = collect($data['items'])->pluck('description')->filter()->unique()->toArray();
                $sums = \App\Models\InventoryItem::whereIn('description', $descriptions)
                    ->selectRaw('description, SUM(stock_balance) as total')
                    ->groupBy('description')
                    ->pluck('total', 'description')
                    ->toArray();

                foreach ($data['items'] as &$issueItem) {
                    $issueItem['total_in_system'] = floatval($sums[$issueItem['description'] ?? ''] ?? 0);
                }
                unset($issueItem);
            }
            $response = [
                'id' => $req->id,
                'status' => $req->status,
                'batch' => $data,
                'recorded_by_name' => $req->user->name ?? 'Personnel',
                'created_at' => $req->created_at->format('d/m/y H:i'),
                'request_type' => $req->request_type
            ];
            return response()->json($response);
        }

        // Attach contact person and driver details to proposed batch
        $cleanSupplier = trim(preg_replace('/\[.*?\]/', '', $data['supplier_name'] ?? ''));
        $deliveryPerson = $data['delivery_person'] ?? '';
        $deliveryPhone = $data['delivery_phone'] ?? '';
        $driverName = $data['driver_name'] ?? '';
        $driverPhone = $data['driver_phone'] ?? '';

        foreach ($suppliersRegistry as $supplier) {
            if (strcasecmp(trim($supplier->name), $cleanSupplier) === 0 || (isset($data['supplier_name']) && strcasecmp(trim($supplier->name), trim($data['supplier_name'])) === 0)) {
                $data['supplier_name'] = $supplier->name;
                if (empty($deliveryPerson)) {
                    $deliveryPerson = $supplier->contact_person ?? $supplier->delivery_person ?? '';
                }
                if (empty($deliveryPhone)) {
                    $deliveryPhone = $supplier->contact_phone ?? $supplier->delivery_phone ?? '';
                }
                if (empty($driverName)) {
                    $driverName = $supplier->delivery_person ?? '';
                }
                if (empty($driverPhone)) {
                    $driverPhone = $supplier->delivery_phone ?? '';
                }
                break;
            }
        }
        $data['delivery_person'] = $deliveryPerson;
        $data['delivery_phone'] = $deliveryPhone;
        $data['driver_name'] = $driverName;
        $data['driver_phone'] = $driverPhone;

        // Add total_in_system to items
        if (isset($data['items']) && is_array($data['items'])) {
            $descriptions = collect($data['items'])->pluck('description')->filter()->unique()->toArray();
            $sums = \App\Models\InventoryItem::whereIn('description', $descriptions)
                ->selectRaw('description, SUM(stock_balance) as total')
                ->groupBy('description')
                ->pluck('total', 'description')
                ->toArray();

            foreach ($data['items'] as &$propItem) {
                $propItem['total_in_system'] = floatval($sums[$propItem['description'] ?? ''] ?? 0);
            }
            unset($propItem);
        }

        $response = [
            'id' => $req->id,
            'status' => $req->status,
            'batch' => $data,
            'ledge_name' => $ledgeMap[$data['ledge_category']] ?? $data['ledge_category'],
            'recorded_by_name' => $req->user->name ?? 'Personnel',
            'created_at' => $req->created_at->format('d/m/y H:i'),
            'request_type' => $req->request_type
        ];

        if ($req->request_type === 'edit_submission') {
            $originalBatch = \App\Models\InventoryBatch::with('items')->find($req->item_id);
            if ($originalBatch) {
                // Attach delivery person to previous batch
                $origClean = trim(preg_replace('/\[.*?\]/', '', $originalBatch->supplier_name ?? ''));
                $origDelivery = '';
                $origPhone = '';
                $origDriver = '';
                $origDriverPhone = '';
                foreach ($suppliersRegistry as $supplier) {
                    if (strcasecmp(trim($supplier->name), $origClean) === 0 || strcasecmp(trim($supplier->name), trim($originalBatch->supplier_name)) === 0) {
                        $originalBatch->supplier_name = $supplier->name;
                        $origDelivery = $supplier->contact_person ?? $supplier->delivery_person ?? '';
                        $origPhone = $supplier->contact_phone ?? $supplier->delivery_phone ?? '';
                        $origDriver = $supplier->delivery_person ?? '';
                        $origDriverPhone = $supplier->delivery_phone ?? '';
                        break;
                    }
                }
                $originalBatch->delivery_person = $origDelivery;
                $originalBatch->delivery_phone = $origPhone;
                $originalBatch->driver_name = $origDriver;
                $originalBatch->driver_phone = $origDriverPhone;

                $origDescriptions = $originalBatch->items->pluck('description')->filter()->unique()->toArray();
                $origSums = \App\Models\InventoryItem::whereIn('description', $origDescriptions)
                    ->selectRaw('description, SUM(stock_balance) as total')
                    ->groupBy('description')
                    ->pluck('total', 'description')
                    ->toArray();

                foreach ($originalBatch->items as $origItem) {
                    $origItem->total_in_system = floatval($origSums[$origItem->description] ?? 0);
                }

                $response['previous_batch'] = $originalBatch;
                $response['previous_ledge_name'] = $ledgeMap[$originalBatch->ledge_category] ?? $originalBatch->ledge_category;
            }
        }
        
        return response()->json($response);
    }

    public function index(Request $request)
    {
        try {
            \App\Http\Controllers\ReturnController::selfHealRequisitions();
            \App\Http\Controllers\StoreRequisitionController::checkOverdueTemporaryItems();
        } catch (\Exception $e) {
            // Keep page loading resilient
        }


        $isStoresHead = (auth()->user()->role === 'Main Admin' || strcasecmp(auth()->user()->department ?? '', 'Stores') === 0 || strcasecmp(auth()->user()->department ?? '', 'Store') === 0);
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head']) && !$isStoresHead) {
            abort(403, 'Unauthorized. Access restricted to Department Head (Stores) and Store Officers.');
        }

        if (auth()->user()->is_admin && auth()->user()->role !== 'Main Admin') {
            return redirect()->route('admin.inventory')->with('info', 'Strategic Oversight required. Redirecting to Command Center.');
        }

        $ledgeMap = $this->getLedgeMap();

        // Shift to querying individual items for a more detailed "Received Items" report
        $query = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->select('inventory_items.*', 'inventory_batches.entry_date', 'inventory_batches.arrival_date', 'inventory_batches.ledge_category', 'inventory_batches.supplier_name', 'inventory_batches.supplier_status', 'inventory_batches.donor_name', 'inventory_batches.acquisition_type');

        // Date filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('inventory_batches.entry_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('inventory_batches.entry_date', '<=', $request->date_to);
        }

        // Supplier filter
        if ($request->has('supplier') && $request->supplier) {
            $query->where('inventory_batches.supplier_name', 'LIKE', '%' . $request->supplier . '%');
        }

        // Donor filter
        if ($request->has('donor') && $request->donor) {
            $query->where('inventory_batches.donor_name', 'LIKE', '%' . $request->donor . '%');
        }

        // Status filter (Partial Delivery)
        if ($request->has('status') && $request->status === 'partial') {
            $query->where(function($q) {
                $q->where('inventory_batches.supplier_status', 'LIKE', '%Partial%')
                  ->orWhere('inventory_batches.supplier_name', 'LIKE', '%[Partial Deliv%');
            });
        } elseif ($request->has('status') && $request->status === 'pending_approval') {
            $query->whereIn('inventory_batches.id', function($q) {
                $q->select('item_id')
                  ->from('edit_requests')
                  ->where('item_type', 'batch')
                  ->where('status', 'pending');
            });
        } elseif ($request->has('status') && $request->status === 'baseline') {
            $query->whereNotNull('inventory_items.book_qty');
        }

        // Ledge Category filter
        if ($request->has('ledge_category') && $request->ledge_category) {
            $query->where('inventory_batches.ledge_category', $request->ledge_category);
        }

        $isSearching = false;
        $searchSum = 0;
        $searchQtySum = 0;
        $searchIssuedQtySum = 0;

        // Search by Product or Batch ID
        if ($request->has('search') && $request->search) {
            $isSearching = true;
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('inventory_items.description', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('inventory_items.batch_id', 'LIKE', '%' . $searchTerm . '%');
            });

            // Calculate exact sum based on search term directly across inventory
            $sumQuery = clone $query;
            $searchSum = $sumQuery->sum('inventory_items.stock_balance');
            $searchQtySum = $sumQuery->sum('inventory_items.qty');

            // Sum all quantities issued through approved/partially_approved requisitions
            $searchIssuedQtySum = \App\Models\StoreRequisitionItem::join('store_requisitions', 'store_requisition_items.requisition_id', '=', 'store_requisitions.id')
                ->whereIn('store_requisitions.status', ['approved', 'partially_approved'])
                ->where('store_requisition_items.description', 'LIKE', '%' . $searchTerm . '%')
                ->selectRaw('SUM(COALESCE(store_requisition_items.alternative_quantity_approved, store_requisition_items.quantity_approved, 0)) as total_issued')
                ->value('total_issued') ?? 0;
        }

        $mockedItems = collect();
        if ($request->has('status') && $request->status === 'pending_approval') {
            $pendingCreations = \App\Models\EditRequest::where('item_type', 'batch_creation')
                ->where('status', 'pending')
                ->get();
                
            foreach ($pendingCreations as $req) {
                $payload = json_decode($req->payload, true);
                if (!$payload) continue;
                
                $items = $payload['items'] ?? [];
                foreach ($items as $index => $itemData) {
                    $mockItem = new \App\Models\InventoryItem();
                    $mockItem->id = 'pending-' . $req->id . '-' . $index;
                    $mockItem->batch_id = 'Pending Approval';
                    $mockItem->description = $itemData['description'] ?? '';
                    $mockItem->unit = $itemData['unit'] ?? '';
                    $mockItem->qty = $itemData['qty'] ?? 0;
                    $mockItem->stock_balance = $itemData['stock_balance'] ?? 0;
                    $mockItem->variance = $itemData['variance'] ?? 0;
                    $mockItem->remarks = $itemData['remarks'] ?? 'Awaiting Admin Approval';
                    
                    $mockItem->entry_date = $payload['entry_date'] ?? $req->created_at;
                    $mockItem->arrival_date = $payload['arrival_date'] ?? $req->created_at;
                    $mockItem->ledge_category = $payload['ledge_category'] ?? '';
                    $mockItem->supplier_name = $payload['supplier_name'] ?? '';
                    $mockItem->supplier_status = 'Pending Approval';
                    $mockItem->donor_name = $payload['donor_name'] ?? null;
                    $mockItem->acquisition_type = $payload['acquisition_type'] ?? '';
                    
                    $mockItem->is_pending_creation = true;
                    $mockItem->edit_request_id = $req->id;
                    
                    $mockedItems->push($mockItem);
                }
            }
        }

        $perPage = $request->input('per_page', 10);
        if ($request->has('status') && $request->status === 'pending_approval') {
            $dbItems = $query->orderBy('inventory_batches.entry_date', 'desc')->get();
            $combined = $mockedItems->merge($dbItems);
            
            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $currentItems = $combined->slice(($currentPage - 1) * $perPage, $perPage)->all();
            
            $receivedItems = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentItems,
                $combined->count(),
                $perPage,
                $currentPage,
                ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
            );
            $receivedItems->appends($request->all());
        } else {
            $receivedItems = $query->orderBy('inventory_batches.entry_date', 'desc')->paginate($perPage);
        }

        // Fetch aggregate totals for item status display in the table
        $itemAggregates = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->where('inventory_batches.approval_status', '=', 'approved')
            ->selectRaw('inventory_items.description, SUM(inventory_items.qty) as total_received_qty, SUM(inventory_items.stock_balance) as total_available, SUM(inventory_items.variance) as total_variance')
            ->groupBy('inventory_items.description')
            ->get()
            ->keyBy('description');

        // Fetch unique suppliers and donors for the dropdowns
        $registryData = \App\Models\Setting::get('suppliers_registry', []);
        if (is_string($registryData)) {
            $registryData = json_decode($registryData, true) ?? [];
        }
        
        $suppliersRegistryWithStats = $registryData;
        if (is_array($suppliersRegistryWithStats)) {
            foreach ($suppliersRegistryWithStats as $name => &$details) {
                $cleanName = trim(preg_replace('/\[.*?\]/', '', $name));
                $batches = \App\Models\InventoryBatch::where(function($q) use ($name, $cleanName) {
                        $q->where('supplier_name', $name)->orWhere('supplier_name', $cleanName);
                    })
                    ->where('supplier_status', '!=', 'System Draft')
                    ->whereNotNull('arrival_date')
                    ->orderBy('arrival_date', 'asc')
                    ->get();

                $details['first_delivery'] = $batches->first() ? \Carbon\Carbon::parse($batches->first()->arrival_date)->format('Y-m-d') : null;
                $details['last_delivery'] = $batches->last() ? \Carbon\Carbon::parse($batches->last()->arrival_date)->format('Y-m-d') : null;
            }
        }

        $registrySuppliers = is_array($registryData) ? array_keys($registryData) : [];
        $allSuppliers = collect($registrySuppliers)
            ->filter(function ($item) {
                return strtolower(trim($item)) !== 'system';
            })
            ->unique()
            ->values();

        $donorNames1 = InventoryBatch::where('acquisition_type', 'Donor')
            ->where('supplier_status', '!=', 'System Draft')
            ->select('donor_name')
            ->distinct()
            ->pluck('donor_name');

        $donorNames2 = InventoryBatch::where('acquisition_type', 'Donor')
            ->where('supplier_status', '!=', 'System Draft')
            ->select('supplier_name')
            ->distinct()
            ->pluck('supplier_name');

        $allDonors = $donorNames1->concat($donorNames2)
            ->filter()
            ->unique()
            ->values();

        // Statistics
        $totalReceived = InventoryBatch::where('supplier_status', '!=', 'System Draft')->count();
        $totalItemsCount = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->count();
        $recentReceived = InventoryBatch::where('supplier_status', '!=', 'System Draft')->whereDate('created_at', '>=', Carbon::now()->subDays(7))->count();

        return view('received-items.index', compact(
            'receivedItems',
            'totalReceived',
            'totalItemsCount',
            'recentReceived',
            'ledgeMap',
            'isSearching',
            'searchSum',
            'searchQtySum',
            'searchIssuedQtySum',
            'itemAggregates',
            'allSuppliers',
            'allDonors',
            'suppliersRegistryWithStats'
        ));
    }

    public function update(Request $request, $id)
    {
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized: Department Heads are only allowed to view received items and cannot make changes.'], 403);
        }

        if (!auth()->user()->is_admin) {
            $editReq = \App\Models\EditRequest::where('user_id', auth()->id())
                ->where('item_id', $id)
                ->where('item_type', 'batch')
                ->where('request_type', 'edit')
                ->where('status', 'approved')
                ->latest()
                ->first();

            if (!$editReq) {
                return response()->json(['success' => false, 'message' => 'Unauthorized: No approved edit request found.'], 403);
            }

            $approvedAt = $editReq->approved_at ?? $editReq->updated_at;
            $timeoutMinutes = 60; // Standard security window
            $timeoutSeconds = $timeoutMinutes * 60;

            if (now()->diffInSeconds($approvedAt) > $timeoutSeconds) {
                return response()->json(['success' => false, 'message' => "Security clearance expired ({$timeoutMinutes}-minute limit exceeded)."], 403);
            }
        }

        $validated = $request->validate([
            'ledge_category' => 'required|string',
            'supplier_name' => 'nullable|string',
            'supplier_status' => 'nullable|string',
            'donor_name' => 'nullable|string',
            'acquisition_type' => 'required|string',
            'arrival_date' => 'required|date',
            'delivery_person' => 'nullable|string',
            'delivery_phone' => ['nullable', 'string', 'regex:/^(N\/A|\d{10})$/i'],
            'items' => 'required|array',
            'items.*.id' => 'required|exists:inventory_items,id',
            'items.*.description' => 'required|string',
            'items.*.serial_number' => 'nullable|string',
            'items.*.unit' => 'required|string',
            'items.*.qty' => 'required|numeric',
            'items.*.stock_balance' => 'required|numeric',
            'items.*.variance' => 'required|numeric',
            'items.*.remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $batch = InventoryBatch::with('items')->findOrFail($id);

            // Capture Original State for Forensic Audit
            $originalBatch = $batch->only(['arrival_date', 'ledge_category', 'acquisition_type', 'supplier_name', 'supplier_status', 'donor_name', 'delivery_person', 'delivery_phone']);
            $originalItems = $batch->items->mapWithKeys(function($item) {
                return [$item->id => $item->only(['description', 'serial_number', 'unit', 'qty', 'stock_balance', 'variance', 'remarks'])];
            });
 
            $origPayloadArray = [
                'arrival_date' => $batch->arrival_date ? explode(' ', $batch->arrival_date)[0] : '',
                'ledge_category' => $batch->ledge_category,
                'acquisition_type' => $batch->acquisition_type,
                'supplier_name' => $batch->supplier_name,
                'supplier_status' => $batch->supplier_status ?? 'Full Delivery',
                'donor_name' => $batch->donor_name,
                'delivery_person' => $batch->delivery_person,
                'delivery_phone' => $batch->delivery_phone,
                'items' => $batch->items->map(function($i) {
                    return [
                        'id' => $i->id,
                        'description' => $i->description,
                        'serial_number' => $i->serial_number,
                        'unit' => $i->unit,
                        'qty' => $i->qty,
                        'stock_balance' => $i->stock_balance,
                        'variance' => $i->variance,
                        'remarks' => $i->remarks
                    ];
                })->toArray()
            ];
 
            $newPayloadArray = [
                'arrival_date' => $validated['arrival_date'],
                'ledge_category' => $validated['ledge_category'],
                'acquisition_type' => $validated['acquisition_type'],
                'supplier_name' => $validated['supplier_name'],
                'supplier_status' => $validated['supplier_status'],
                'donor_name' => $validated['donor_name'],
                'delivery_person' => $validated['delivery_person'] ?? null,
                'delivery_phone' => $validated['delivery_phone'] ?? null,
                'items' => collect($validated['items'])->map(function($i) {
                    return [
                        'id' => $i['id'],
                        'description' => $i['description'],
                        'serial_number' => $i['serial_number'] ?? null,
                        'unit' => $i['unit'],
                        'qty' => $i['qty'],
                        'stock_balance' => $i['stock_balance'],
                        'variance' => $i['variance'],
                        'remarks' => $i['remarks'] ?? null
                    ];
                })->toArray()
            ];
 
            $batch->update([
                'ledge_category' => $validated['ledge_category'],
                'supplier_name' => $validated['supplier_name'],
                'supplier_status' => $validated['supplier_status'],
                'donor_name' => $validated['donor_name'],
                'acquisition_type' => $validated['acquisition_type'],
                'arrival_date' => $validated['arrival_date'],
                'delivery_person' => $validated['delivery_person'] ?? null,
                'delivery_phone' => $validated['delivery_phone'] ?? null,
            ]);

            $itemChanges = [];
            foreach ($validated['items'] as $itemData) {
                $item = $batch->items()->findOrFail($itemData['id']);
                
                $old = $originalItems[$item->id] ?? [];
                $new = [
                    'description' => $itemData['description'],
                    'serial_number' => $itemData['serial_number'] ?? null,
                    'unit' => $itemData['unit'],
                    'qty' => $itemData['qty'],
                    'stock_balance' => $itemData['stock_balance'],
                    'variance' => $itemData['variance'],
                    'remarks' => $itemData['remarks'],
                ];

                // Detect changes
                $diff = [];
                foreach ($new as $key => $val) {
                    if (isset($old[$key]) && $old[$key] != $val) {
                        $diff[$key] = ['old' => $old[$key], 'new' => $val];
                    }
                }

                if (!empty($diff)) {
                    $itemChanges[$item->id] = $diff;
                }

                $item->update($new);
            }

            if (!auth()->user()->is_admin) {
                // For personnel, find the approved request and update it
                $editReq = \App\Models\EditRequest::where('user_id', auth()->id())
                    ->where('item_id', $id)
                    ->where('item_type', 'batch')
                    ->where('status', 'approved')
                    ->latest()
                    ->first();
                if ($editReq) {
                    $editReq->update([
                        'original_payload' => json_encode($origPayloadArray),
                        'payload' => json_encode($newPayloadArray),
                        'request_type' => 'edit_submission',
                        'status' => 'completed'
                    ]);
                }
            } else {
                // For admin, create a completed EditRequest
                \App\Models\EditRequest::create([
                    'user_id' => auth()->id(),
                    'item_type' => 'batch',
                    'item_id' => $id,
                    'request_type' => 'edit_submission',
                    'reason' => 'Direct administrative modification.',
                    'status' => 'completed',
                    'original_payload' => json_encode($origPayloadArray),
                    'payload' => json_encode($newPayloadArray),
                    'approved_at' => now()
                ]);
            }

            DB::commit();

            // Log the activity with Detailed Metadata
            if (auth()->check()) {
                $user = auth()->user();
                \App\Models\SystemLog::create([
                    'user_id' => $user->id,
                    'event_type' => 'INVENTORY',
                    'action' => 'UPDATE_BATCH',
                    'description' => "Personnel modified Inventory Batch #{$id} and its associated items.",
                    'severity' => 'info',
                    'metadata' => [
                        'batch_id' => $id,
                        'batch_changes' => array_diff_assoc($batch->only(array_keys($originalBatch)), $originalBatch),
                        'item_changes' => $itemChanges
                    ],
                    'ip_address' => request()->ip()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Batch updated successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        \App\Models\InventoryBatch::selfHealSchema();
        $isStoresHead = (auth()->user()->role === 'Main Admin' || strcasecmp(auth()->user()->department ?? '', 'Stores') === 0 || strcasecmp(auth()->user()->department ?? '', 'Store') === 0);
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head']) && !$isStoresHead) {
            abort(403, 'Unauthorized.');
        }

        $ledgeMap = $this->getLedgeMap();
        $batch = InventoryBatch::with(['items', 'recorder', 'approver'])->findOrFail($id);
        
        // Fetch history logs for this batch
        $history = \App\Models\SystemLog::where('action', 'UPDATE_BATCH')
            ->where('metadata->batch_id', (int)$id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->has('json')) {
            // Enrich with full supplier profile if available
            $supplierProfile = null;
            $cleanSupplier = trim(preg_replace('/\[.*?\]/', '', $batch->supplier_name ?? ''));
            if ($cleanSupplier) {
                $supplierModel = \App\Models\Supplier::where('name', $cleanSupplier)
                    ->orWhere('name', $batch->supplier_name)
                    ->first();
                if ($supplierModel) {
                    $supplierProfile = $supplierModel->toArray();
                }
            }

            return response()->json([
                'batch' => $batch,
                'history' => $history,
                'supplier_profile' => $supplierProfile,
            ]);
        }

        return view('received-items.show', compact('batch', 'ledgeMap', 'history'));
    }

    public function print($id)
    {
        \App\Models\InventoryBatch::selfHealSchema();
        $isStoresHead = (auth()->user()->role === 'Main Admin' || strcasecmp(auth()->user()->department ?? '', 'Stores') === 0 || strcasecmp(auth()->user()->department ?? '', 'Store') === 0);
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head']) && !$isStoresHead) {
            abort(403, 'Unauthorized.');
        }

        $ledgeMap = $this->getLedgeMap();
        $batch = InventoryBatch::with(['items'])->findOrFail($id);
        
        // Fetch history logs for this batch
        $history = \App\Models\SystemLog::where('action', 'UPDATE_BATCH')
            ->where('metadata->batch_id', (int)$id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('received-items.print', compact('batch', 'ledgeMap', 'history'));
    }

    public function sra($id)
    {
        $isStoresHead = (auth()->user()->role === 'Main Admin' || strcasecmp(auth()->user()->department ?? '', 'Stores') === 0 || strcasecmp(auth()->user()->department ?? '', 'Store') === 0);
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head']) && !$isStoresHead) {
            abort(403, 'Unauthorized.');
        }

        $ledgeMap = $this->getLedgeMap();
        $batch = InventoryBatch::with(['items'])->findOrFail($id);
        
        // Fetch the Admin who approved this specific batch
        $admin = null;
        if ($batch->approval_status === 'approved' && $batch->approved_by) {
            $admin = \App\Models\User::find($batch->approved_by);
        }
        
        // Fallback for legacy approved batches or system defaults
        if (!$admin) {
            $admin = \App\Models\User::where('is_admin', true)->where('registration_status', 'approved')->first();
        }

        // Fetch history logs for this batch
        $history = \App\Models\SystemLog::where('action', 'UPDATE_BATCH')
            ->where('metadata->batch_id', (int)$id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Determine which voucher type to serve
        $isDonation = ($batch->acquisition_type === 'Donor' || 
                       str_contains(strtolower($batch->supplier_status), 'donor') || 
                       str_contains(strtolower($batch->supplier_status), 'donation'));

        $view = $isDonation ? 'received-items.donation_voucher' : 'received-items.sra';

        return view($view, compact('batch', 'ledgeMap', 'history', 'admin'));
    }

    public function destroy($id)
    {
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized: Department Heads are only allowed to view received items and cannot make changes.'], 403);
        }

        if (!auth()->user()->is_admin) {
            $editReq = \App\Models\EditRequest::where('user_id', auth()->id())
                ->where('item_id', $id)
                ->where('item_type', 'batch')
                ->where('request_type', 'delete')
                ->where('status', 'approved')
                ->latest()
                ->first();

            if (!$editReq) {
                return response()->json(['success' => false, 'message' => 'Unauthorized: No approved delete request found.'], 403);
            }

            $approvedAt = $editReq->approved_at ?? $editReq->updated_at;
            $timeoutMinutes = 60; // Standard security window
            $timeoutSeconds = $timeoutMinutes * 60;

            if (now()->diffInSeconds($approvedAt) > $timeoutSeconds) {
                return response()->json(['success' => false, 'message' => "Security clearance expired ({$timeoutMinutes}-minute limit exceeded)."], 403);
            }
        }

        try {
            $batch = InventoryBatch::findOrFail($id);
            $batchId = $batch->id;
            $category = $batch->ledge_category;
            // Delete associated items first to maintain referential integrity if not handled by FK
            $batch->items()->delete();
            $batch->delete();

            // Log the purge activity
            if (auth()->check()) {
                $user = auth()->user();
                \App\Models\SystemLog::create([
                    'user_id' => $user->id,
                    'event_type' => 'SECURITY',
                    'action' => 'DELETE_BATCH',
                    'description' => "Personnel purged Inventory Batch #{$batchId} (Category {$category}) and all its associated items.",
                    'severity' => 'danger',
                    'ip_address' => request()->ip()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Batch and associated records purged successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Purge failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSupplierStats($name)
    {
        $user = auth()->user();
        // Allow Admins, Head of Stores, and any user in the Stores department
        $isStoresPersonnel = (
            $user->is_admin ||
            $user->role === 'Head of Stores' ||
            $user->role === 'Main Admin' ||
            strcasecmp($user->department ?? '', 'Stores') === 0 ||
            strcasecmp($user->department ?? '', 'Store') === 0
        );

        if (!$isStoresPersonnel) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $cleanName = trim(preg_replace('/\[.*?\]/', '', $name));
        $supplier = \App\Models\Supplier::where('name', $cleanName)
            ->orWhere('name', $name)
            ->first();
        
        // Fallback: build a minimal supplier object from the settings registry
        if (!$supplier) {
            $registryData = \App\Models\Setting::get('suppliers_registry', []);
            if (is_string($registryData)) {
                $registryData = json_decode($registryData, true) ?? [];
            }
            $registryEntry = null;
            foreach ($registryData as $key => $details) {
                if (strcasecmp(trim($key), $cleanName) === 0 || strcasecmp(trim($key), trim($name)) === 0) {
                    $registryEntry = array_merge(['name' => $key], is_array($details) ? $details : []);
                    break;
                }
            }
            if ($registryEntry) {
                // Return a synthetic supplier object from registry data
                $supplier = (object) $registryEntry;
            } else {
                return response()->json(['error' => 'Supplier not found'], 404);
            }
        }

        // Calculate stats
        $totalDeliveries = \App\Models\InventoryBatch::where(function($q) use ($name, $cleanName) {
                $q->where('supplier_name', $name)->orWhere('supplier_name', $cleanName);
            })
            ->where('supplier_status', '!=', 'System Draft')
            ->count();

        $totalItemsSupplied = \App\Models\InventoryItem::whereHas('batch', function($q) use ($name, $cleanName) {
            $q->where(function($q2) use ($name, $cleanName) {
                $q2->where('supplier_name', $name)->orWhere('supplier_name', $cleanName);
            })->where('supplier_status', '!=', 'System Draft');
        })->sum('qty');

        $lastDelivery = \App\Models\InventoryBatch::where(function($q) use ($name, $cleanName) {
                $q->where('supplier_name', $name)->orWhere('supplier_name', $cleanName);
            })
            ->where('supplier_status', '!=', 'System Draft')
            ->orderBy('arrival_date', 'desc')
            ->first();

        return response()->json([
            'supplier' => $supplier,
            'stats' => [
                'total_deliveries' => $totalDeliveries,
                'total_items' => $totalItemsSupplied,
                'last_delivery' => $lastDelivery ? \Carbon\Carbon::parse($lastDelivery->arrival_date)->format('d/m/y') : 'N/A'
            ]
        ]);
    }

    public function processSraReview(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,decline',
            'reason' => 'nullable|string'
        ]);

        $user = auth()->user();
        if ($user->role !== 'Auditor' && $user->role !== 'Main Admin') {
            return response()->json(['success' => false, 'message' => 'Clearance Restricted: Action requires Auditor or Head of Admin role.'], 403);
        }

        $batch = InventoryBatch::findOrFail($id);

        if ($batch->approval_status !== 'pending_auditor_admin') {
            return response()->json(['success' => false, 'message' => 'SRA receipt is not currently pending Auditor/Admin review.'], 400);
        }

        $action = $request->action;
        $reason = $request->reason;
        
        DB::beginTransaction();
        try {
            if ($action === 'approve') {
                if ($user->role === 'Auditor') {
                    $batch->auditor_status = 'approved';
                    $batch->auditor_approved_by = $user->id;
                    $batch->auditor_approved_at = now();
                } elseif ($user->role === 'Main Admin') {
                    $batch->admin_status = 'approved';
                    $batch->admin_approved_by = $user->id;
                    $batch->admin_approved_at = now();
                }

                // If both approved, set the batch approval_status to approved (live stock)
                if ($batch->auditor_status === 'approved' && $batch->admin_status === 'approved') {
                    $batch->approval_status = 'approved';
                    $batch->approved_at = now(); // Set overall approved_at

                    // Log fully approved
                    \App\Models\SystemLog::create([
                        'user_id' => $user->id,
                        'event_type' => 'INVENTORY',
                        'action' => 'SRA_FULLY_APPROVED',
                        'description' => "SRA Receipt #" . str_pad($batch->id, 6, '0', STR_PAD_LEFT) . " has been fully approved by Auditor and Head of Admin.",
                        'severity' => 'info',
                        'metadata' => ['batch_id' => $batch->id],
                        'ip_address' => request()->ip()
                    ]);

                    // Send confirmation message to Store Officer & Head of Stores
                    $confirmMsg = "<div class='personnel-view sra-approved-msg' style='padding: 15px; border: 1px solid #10b981; border-radius: 12px; background: rgba(16, 185, 129, 0.05);'>";
                    $confirmMsg .= "  <b style='color: #10b981;'>🔔 SRA RECEIPT FULLY APPROVED</b><br><br>";
                    $confirmMsg .= "  SRA Receipt <b>#" . str_pad($batch->id, 6, '0', STR_PAD_LEFT) . "</b> has been fully verified and approved by the Auditor and the Head of Admin. The items have been moved to live stock and are ready for disbursement.<br><br>";
                    $confirmMsg .= "  <a href='" . route('receiveditems.sra', ['id' => $batch->id]) . "' target='_blank' style='display: inline-block; background: #10b981; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);'>Print / Download SRA</a>";
                    $confirmMsg .= "</div>";

                    $receivers = array_unique([$batch->recorded_by, $batch->approved_by, $batch->stores_approved_by]);
                    foreach ($receivers as $receiverId) {
                        if ($receiverId) {
                            \App\Models\Message::create([
                                'sender_id' => $user->id,
                                'receiver_id' => $receiverId,
                                'message' => $confirmMsg,
                                'is_automated' => true,
                                'read_at' => null,
                            ]);
                        }
                    }
                } else {
                    // Log single role approval
                    \App\Models\SystemLog::create([
                        'user_id' => $user->id,
                        'event_type' => 'INVENTORY',
                        'action' => 'SRA_ROLE_APPROVED',
                        'description' => "SRA Receipt #" . str_pad($batch->id, 6, '0', STR_PAD_LEFT) . " approved by {$user->role} ({$user->name}).",
                        'severity' => 'info',
                        'metadata' => ['batch_id' => $batch->id],
                        'ip_address' => request()->ip()
                    ]);
                }
            } elseif ($action === 'decline') {
                if ($user->role === 'Auditor') {
                    $batch->auditor_status = 'declined';
                } elseif ($user->role === 'Main Admin') {
                    $batch->admin_status = 'declined';
                }
                
                $batch->approval_status = 'declined'; // Rejection of the entry

                \App\Models\SystemLog::create([
                    'user_id' => $user->id,
                    'event_type' => 'INVENTORY',
                    'action' => 'SRA_DECLINED',
                    'description' => "SRA Receipt #" . str_pad($batch->id, 6, '0', STR_PAD_LEFT) . " was declined by {$user->role} ({$user->name}). Reason: {$reason}",
                    'severity' => 'warning',
                    'metadata' => ['batch_id' => $batch->id, 'reason' => $reason],
                    'ip_address' => request()->ip()
                ]);

                // Send decline message back to Store Officer & Head of Stores
                $declineMsg = "<div class='personnel-view sra-declined-msg' style='padding: 15px; border: 1px solid #ef4444; border-radius: 12px; background: rgba(239, 68, 68, 0.05);'>";
                $declineMsg .= "  <b style='color: #ef4444;'>❌ SRA RECEIPT DECLINED</b><br><br>";
                $declineMsg .= "  SRA Receipt <b>#" . str_pad($batch->id, 6, '0', STR_PAD_LEFT) . "</b> has been declined by the {$user->role} ({$user->name}) during verification review.<br><br>";
                if ($reason) {
                    $declineMsg .= "  <div style='padding: 10px 14px; background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; font-size: 0.85rem; color: #7f1d1d; margin-bottom: 12px;'>";
                    $declineMsg .= "    <b>Decline Reason:</b> " . nl2br(e($reason)) . "";
                    $declineMsg .= "  </div>";
                }
                $declineMsg .= "  Please review the entry details and coordinate with the administrative command.";
                $declineMsg .= "</div>";

                $receivers = array_unique([$batch->recorded_by, $batch->approved_by, $batch->stores_approved_by]);
                foreach ($receivers as $receiverId) {
                    if ($receiverId) {
                        \App\Models\Message::create([
                            'sender_id' => $user->id,
                            'receiver_id' => $receiverId,
                            'message' => $declineMsg,
                            'is_automated' => true,
                            'read_at' => null,
                        ]);
                    }
                }
            }

            $batch->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $action === 'approve' 
                    ? 'SRA receipt verification action logged successfully.' 
                    : 'SRA receipt has been declined.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Database exception: ' . $e->getMessage()], 500);
        }
    }
}
