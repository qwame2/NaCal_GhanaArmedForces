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
        $req = \App\Models\EditRequest::findOrFail($id);
        $data = json_decode($req->payload, true);
        
        // Mock a batch object for the view
        $batch = (object)[
            'id' => 'DRAFT',
            'ledge_category' => $data['ledge_category'],
            'supplier_name' => $data['supplier_name'],
            'supplier_status' => $data['supplier_status'],
            'donor_name' => $data['donor_name'] ?? null,
            'acquisition_type' => $data['acquisition_type'],
            'entry_date' => $data['entry_date'],
            'arrival_date' => $data['arrival_date'],
            'approval_status' => 'pending',
            'approved_at' => null,
            'created_at' => $req->created_at, // Use the request creation date as mock
            'recorded_by_name' => $req->user->name ?? 'Personnel',
            'recorder' => (object)['name' => $req->user->name ?? 'Personnel'],
            'items' => collect($data['items'])->map(function($i) {
                $item = (object)$i;
                $item->ledger_id = $item->ledger_id ?? '-';
                $item->remarks = $item->remarks ?? '';
                $item->stock_balance = $item->stock_balance ?? 0;
                $item->variance = $item->variance ?? 0;
                $item->total_in_system = \App\Models\InventoryItem::where('description', $item->description ?? '')
                    ->sum('stock_balance');
                return $item;
            })
        ];

        $admin = auth()->user();
        $ledgeMap = $this->getLedgeMap();
        $history = collect(); 

        return view('received-items.preview_details', compact('batch', 'admin', 'ledgeMap', 'history'));
    }

    public function previewApi($id)
    {
        $req = \App\Models\EditRequest::with('user')->findOrFail($id);
        $data = json_decode($req->payload, true);
        $ledgeMap = $this->getLedgeMap();
        $suppliersRegistry = \App\Models\Supplier::all();
        
        if ($req->request_type === 'issue_submission') {
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as &$item) {
                    $item['total_in_system'] = \App\Models\InventoryItem::where('description', $item['description'] ?? '')
                        ->sum('stock_balance');
                }
            }
            $response = [
                'id' => $req->id,
                'status' => $req->status,
                'batch' => $data,
                'recorded_by_name' => $req->user->name ?? 'Personnel',
                'created_at' => $req->created_at->format('d/m/Y H:i'),
                'request_type' => $req->request_type
            ];
            return response()->json($response);
        }

        // Attach delivery person to proposed batch
        $cleanSupplier = trim(preg_replace('/\[.*?\]/', '', $data['supplier_name'] ?? ''));
        $deliveryPerson = '';
        foreach ($suppliersRegistry as $supplier) {
            if (strcasecmp(trim($supplier->name), $cleanSupplier) === 0 || (isset($data['supplier_name']) && strcasecmp(trim($supplier->name), trim($data['supplier_name'])) === 0)) {
                $data['supplier_name'] = $supplier->name;
                $deliveryPerson = $supplier->delivery_person ?? '';
                break;
            }
        }
        $data['delivery_person'] = $deliveryPerson;

        // Add total_in_system to items
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as &$item) {
                $item['total_in_system'] = \App\Models\InventoryItem::where('description', $item['description'] ?? '')
                    ->sum('stock_balance');
            }
        }

        $response = [
            'id' => $req->id,
            'status' => $req->status,
            'batch' => $data,
            'ledge_name' => $ledgeMap[$data['ledge_category']] ?? $data['ledge_category'],
            'recorded_by_name' => $req->user->name ?? 'Personnel',
            'created_at' => $req->created_at->format('d/m/Y H:i'),
            'request_type' => $req->request_type
        ];

        if ($req->request_type === 'edit_submission') {
            $originalBatch = \App\Models\InventoryBatch::with('items')->find($req->item_id);
            if ($originalBatch) {
                // Attach delivery person to previous batch
                $origClean = trim(preg_replace('/\[.*?\]/', '', $originalBatch->supplier_name ?? ''));
                $origDelivery = '';
                foreach ($suppliersRegistry as $supplier) {
                    if (strcasecmp(trim($supplier->name), $origClean) === 0 || strcasecmp(trim($supplier->name), trim($originalBatch->supplier_name)) === 0) {
                        $originalBatch->supplier_name = $supplier->name;
                        $origDelivery = $supplier->delivery_person ?? '';
                        break;
                    }
                }
                $originalBatch->delivery_person = $origDelivery;

                foreach ($originalBatch->items as $item) {
                    $item->total_in_system = \App\Models\InventoryItem::where('description', $item->description)
                        ->sum('stock_balance');
                }

                $response['previous_batch'] = $originalBatch;
                $response['previous_ledge_name'] = $ledgeMap[$originalBatch->ledge_category] ?? $originalBatch->ledge_category;
            }
        }
        
        return response()->json($response);
    }

    public function index(Request $request)
    {
        if (auth()->user()->is_admin) {
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
        }

        // Ledge Category filter
        if ($request->has('ledge_category') && $request->ledge_category) {
            $query->where('inventory_batches.ledge_category', $request->ledge_category);
        }

        $isSearching = false;
        $searchSum = 0;
        $searchQtySum = 0;

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
            ->selectRaw('inventory_items.description, SUM(inventory_items.qty) as total_received_qty, SUM(inventory_items.stock_balance) as total_available, SUM(inventory_items.variance) as total_variance')
            ->groupBy('inventory_items.description')
            ->get()
            ->keyBy('description');

        // Fetch unique suppliers and donors for the dropdowns
        $registryData = \App\Models\Setting::get('suppliers_registry', []);
        if (is_string($registryData)) {
            $registryData = json_decode($registryData, true) ?? [];
        }
        $registrySuppliers = is_array($registryData) ? array_keys($registryData) : [];
        $dbSuppliers = InventoryBatch::where('acquisition_type', 'Supplier')
            ->where('supplier_status', '!=', 'System Draft')
            ->select('supplier_name')
            ->distinct()
            ->pluck('supplier_name')
            ->map(function($name) {
                return preg_replace('/\s\[.*\]$/', '', $name);
            })->toArray();
        $allSuppliers = collect(array_merge($registrySuppliers, $dbSuppliers))
            ->filter()
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
            'itemAggregates',
            'allSuppliers',
            'allDonors'
        ));
    }

    public function update(Request $request, $id)
    {
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
            'items' => 'required|array',
            'items.*.id' => 'required|exists:inventory_items,id',
            'items.*.description' => 'required|string',
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
            $originalBatch = $batch->only(['arrival_date', 'ledge_category', 'acquisition_type', 'supplier_name', 'supplier_status', 'donor_name']);
            $originalItems = $batch->items->mapWithKeys(function($item) {
                return [$item->id => $item->only(['description', 'unit', 'qty', 'stock_balance', 'variance', 'remarks'])];
            });

            $origPayloadArray = [
                'arrival_date' => $batch->arrival_date ? explode(' ', $batch->arrival_date)[0] : '',
                'ledge_category' => $batch->ledge_category,
                'acquisition_type' => $batch->acquisition_type,
                'supplier_name' => $batch->supplier_name,
                'supplier_status' => $batch->supplier_status ?? 'Full Delivery',
                'donor_name' => $batch->donor_name,
                'items' => $batch->items->map(function($i) {
                    return [
                        'id' => $i->id,
                        'description' => $i->description,
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
                'items' => collect($validated['items'])->map(function($i) {
                    return [
                        'id' => $i['id'],
                        'description' => $i['description'],
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
            ]);

            $itemChanges = [];
            foreach ($validated['items'] as $itemData) {
                $item = $batch->items()->findOrFail($itemData['id']);
                
                $old = $originalItems[$item->id] ?? [];
                $new = [
                    'description' => $itemData['description'],
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
        $ledgeMap = $this->getLedgeMap();
        $batch = InventoryBatch::with(['items'])->findOrFail($id);
        
        // Fetch history logs for this batch
        $history = \App\Models\SystemLog::where('action', 'UPDATE_BATCH')
            ->where('metadata->batch_id', (int)$id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->has('json')) {
            return response()->json([
                'batch' => $batch,
                'history' => $history
            ]);
        }

        return view('received-items.show', compact('batch', 'ledgeMap', 'history'));
    }

    public function print($id)
    {
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
        $ledgeMap = $this->getLedgeMap();
        $batch = InventoryBatch::with(['items'])->findOrFail($id);
        
        // Fetch the Admin who approved this specific batch
        $admin = null;
        if ($batch->approval_status === 'approved' && $batch->approved_by) {
            $admin = \App\Models\User::find($batch->approved_by);
        }
        
        // Fallback for legacy approved batches or system defaults
        if (!$admin) {
            $admin = \App\Models\User::where('is_admin', true)->first();
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
        $cleanName = trim(preg_replace('/\[.*?\]/', '', $name));
        $supplier = \App\Models\Supplier::where('name', $cleanName)->first();
        
        if (!$supplier) {
            return response()->json(['error' => 'Supplier not found'], 404);
        }

        // Calculate stats
        $totalDeliveries = \App\Models\InventoryBatch::where('supplier_name', $name)
            ->where('supplier_status', '!=', 'System Draft')
            ->count();

        $totalItemsSupplied = \App\Models\InventoryItem::whereHas('batch', function($q) use ($name) {
            $q->where('supplier_name', $name)->where('supplier_status', '!=', 'System Draft');
        })->sum('qty');

        $lastDelivery = \App\Models\InventoryBatch::where('supplier_name', $name)
            ->where('supplier_status', '!=', 'System Draft')
            ->orderBy('arrival_date', 'desc')
            ->first();

        return response()->json([
            'supplier' => $supplier,
            'stats' => [
                'total_deliveries' => $totalDeliveries,
                'total_items' => $totalItemsSupplied,
                'last_delivery' => $lastDelivery ? \Carbon\Carbon::parse($lastDelivery->arrival_date)->format('M d, Y') : 'N/A'
            ]
        ]);
    }
}
