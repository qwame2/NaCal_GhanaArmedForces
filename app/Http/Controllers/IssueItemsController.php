<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryBatch;
use App\Models\Issuance;
use App\Models\IssuedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IssueItemsController extends Controller
{
    public function index()
    {
        // Get unique items by description and sum up their stock balance
        $items = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->selectRaw('inventory_items.description, inventory_batches.ledge_category, SUM(inventory_items.stock_balance) as total_stock')
            ->groupBy('inventory_items.description', 'inventory_batches.ledge_category')
            ->get();

        $ledgeMap = [
            'A' => 'Stationary',
            'B' => 'Cleaning',
            'C' => 'IT & Acc.',
            'D' => 'Transport',
            'E' => 'Safety',
            'G' => 'Pharmacy',
            'J' => 'Equipment'
        ];

        return view('issue-items.index', compact('items', 'ledgeMap'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'issuance_date' => 'required|date',
            'beneficiary' => 'required|string',
            'issuance_type' => 'required|string|in:Permanent,Temporary',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.category' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $issuance = Issuance::create([
                'issuance_date' => $validated['issuance_date'],
                'beneficiary' => $validated['beneficiary'],
                'issuance_type' => $validated['issuance_type'],
            ]);

            foreach ($validated['items'] as $cartItem) {
                $qtyToIssue = $cartItem['qty'];

                // Record the line item
                IssuedItem::create([
                    'issuance_id' => $issuance->id,
                    'description' => $cartItem['description'],
                    'ledge_category' => $cartItem['category'],
                    'quantity' => $qtyToIssue,
                ]);

                // FIFO Stock Reduction
                $stockItems = InventoryItem::where('description', $cartItem['description'])
                    ->whereHas('batch', function ($q) use ($cartItem) {
                        $q->where('ledge_category', $cartItem['category']);
                    })
                    ->where('stock_balance', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($stockItems as $inventoryItem) {
                    if ($qtyToIssue <= 0) break;

                    $available = floatval($inventoryItem->stock_balance);
                    $take = min($available, $qtyToIssue);

                    $inventoryItem->stock_balance = $available - $take;
                    $inventoryItem->save();

                    $qtyToIssue -= $take;
                }

                if ($qtyToIssue > 0) {
                    throw new \Exception("Insufficient stock for " . $cartItem['description']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Items issued successfully and inventory updated.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Issuance failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
