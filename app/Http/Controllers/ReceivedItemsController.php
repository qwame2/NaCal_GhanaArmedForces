<?php

namespace App\Http\Controllers;

use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReceivedItemsController extends Controller
{
    private $ledgeMap = [
        'A' => 'Stationary',
        'B' => 'Cleaning',
        'C' => 'IT & Acc.',
        'D' => 'Transport',
        'E' => 'Safety',
        'G' => 'Pharmacy',
        'J' => 'Equipment'
    ];

    public function index(Request $request)
    {
        $ledgeMap = $this->ledgeMap;

        // Shift to querying individual items for a more detailed "Received Items" report
        $query = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->select('inventory_items.*', 'inventory_batches.entry_date', 'inventory_batches.arrival_date', 'inventory_batches.ledge_category', 'inventory_batches.supplier_name', 'inventory_batches.donor_name', 'inventory_batches.acquisition_type');

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
            $query->where('inventory_batches.supplier_name', 'LIKE', '%[Partial Deliv%');
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

        $perPage = $request->input('per_page', 10);
        $receivedItems = $query->orderBy('inventory_batches.entry_date', 'desc')->paginate($perPage);

        // Fetch aggregate totals for item status display in the table
        $itemAggregates = InventoryItem::selectRaw('description, SUM(qty) as total_received_qty, SUM(stock_balance) as total_available, SUM(variance) as total_variance')
            ->groupBy('description')
            ->get()
            ->keyBy('description');

        // Fetch unique suppliers and donors for the dropdowns
        $allSuppliers = InventoryBatch::where('acquisition_type', 'Supplier')
            ->select('supplier_name')
            ->distinct()
            ->pluck('supplier_name')
            ->map(function($name) {
                return preg_replace('/\s\[.*\]$/', '', $name);
            })->unique()->values();

        $allDonors = InventoryBatch::where('acquisition_type', 'Donor')
            ->select('donor_name')
            ->distinct()
            ->pluck('donor_name')
            ->unique()->values();

        // Statistics
        $totalReceived = InventoryBatch::count();
        $totalItemsCount = InventoryItem::count();
        $recentReceived = InventoryBatch::whereDate('created_at', '>=', Carbon::now()->subDays(7))->count();

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
        $validated = $request->validate([
            'ledge_category' => 'required|string',
            'supplier_name' => 'nullable|string',
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
            $originalBatch = $batch->only(['arrival_date', 'ledge_category', 'acquisition_type', 'supplier_name', 'donor_name']);
            $originalItems = $batch->items->mapWithKeys(function($item) {
                return [$item->id => $item->only(['description', 'unit', 'qty', 'stock_balance', 'variance', 'remarks'])];
            });

            $batch->update([
                'ledge_category' => $validated['ledge_category'],
                'supplier_name' => $validated['supplier_name'],
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
        $ledgeMap = $this->ledgeMap;
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
        $ledgeMap = $this->ledgeMap;
        $batch = InventoryBatch::with(['items'])->findOrFail($id);
        
        // Fetch history logs for this batch
        $history = \App\Models\SystemLog::where('action', 'UPDATE_BATCH')
            ->where('metadata->batch_id', (int)$id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('received-items.print', compact('batch', 'ledgeMap', 'history'));
    }

    public function destroy($id)
    {
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
}
