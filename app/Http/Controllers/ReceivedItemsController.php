<?php

namespace App\Http\Controllers;

use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
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
            ->select('inventory_items.*', 'inventory_batches.entry_date', 'inventory_batches.ledge_category', 'inventory_batches.supplier_name', 'inventory_batches.donor_name', 'inventory_batches.acquisition_type');

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
        $itemAggregates = InventoryItem::selectRaw('description, SUM(qty) as total_received_qty, SUM(stock_balance) as total_available, SUM(ledge_balance) as total_book, SUM(variance) as total_variance')
            ->groupBy('description')
            ->get()
            ->keyBy('description');

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
            'itemAggregates'
        ));
    }

    public function show(Request $request, $id)
    {
        $ledgeMap = $this->ledgeMap;
        $batch = InventoryBatch::with(['items'])->findOrFail($id);
        
        if ($request->has('json')) {
            return response()->json(['batch' => $batch]);
        }

        return view('received-items.show', compact('batch', 'ledgeMap'));
    }

    public function print($id)
    {
        $ledgeMap = $this->ledgeMap;
        $batch = InventoryBatch::with(['items'])->findOrFail($id);
        return view('received-items.print', compact('batch', 'ledgeMap'));
    }

    public function destroy($id)
    {
        try {
            $batch = InventoryBatch::findOrFail($id);
            // Delete associated items first to maintain referential integrity if not handled by FK
            $batch->items()->delete();
            $batch->delete();

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
