<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Issuance;
use App\Models\IssuedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function index()
    {
        // Get all items that were issued as 'Temporary' and haven't been fully returned
        // For simplicity, let's just show all issued items that can be returned
        $issuedItems = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->select('issued_items.*', 'issuances.beneficiary', 'issuances.issuance_date', 'issuances.issuance_type')
            ->where('issuances.issuance_type', 'Temporary')
            ->orderBy('issuances.issuance_date', 'desc')
            ->get();

        return view('returns.index', compact('issuedItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'issued_item_id' => 'required|exists:issued_items,id',
            'return_qty' => 'required|integer|min:1',
            'return_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $issuedItem = IssuedItem::findOrFail($validated['issued_item_id']);
            
            if ($validated['return_qty'] > $issuedItem->quantity) {
                throw new \Exception("Return quantity cannot exceed issued quantity.");
            }

            // Restore stock to the most recent batch of this item+ledge
            // In a real system, we might want to return to the specific batches it came from, 
            // but for simplicity we'll just add it back to the first available batch for that description/category.
            $inventoryItem = InventoryItem::where('description', $issuedItem->description)
                ->whereHas('batch', function($q) use ($issuedItem) {
                    $q->where('ledge_category', $issuedItem->ledge_category);
                })
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$inventoryItem) {
                throw new \Exception("Could not find a valid inventory destination for this item.");
            }

            $inventoryItem->stock_balance += $validated['return_qty'];
            $inventoryItem->save();

            // Update issued item quantity or mark as returned
            // For now, let's just reduce the issued quantity
            $issuedItem->quantity -= $validated['return_qty'];
            $issuedItem->save();

            DB::commit();

            return redirect()->back()->with('success', 'Item returned successfully to inventory.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Return failed: ' . $e->getMessage());
        }
    }
}
