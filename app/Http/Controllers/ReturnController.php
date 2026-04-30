<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Issuance;
use App\Models\IssuedItem;
use App\Models\ReturnedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ReturnController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('returned_items')) {
            Schema::create('returned_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('issued_item_id')->constrained('issued_items')->onDelete('cascade');
                $table->integer('returned_qty');
                $table->date('return_date');
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('returned_items', 'remarks')) {
            Schema::table('returned_items', function (Blueprint $table) {
                $table->text('remarks')->nullable();
            });
        }

        // Get all items that were issued and haven't been fully returned
        $issuedItems = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->select('issued_items.*', 'issuances.beneficiary', 'issuances.authority', 'issuances.issuance_date', 'issuances.issuance_type')
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
            'remarks' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $issuedItem = IssuedItem::findOrFail($validated['issued_item_id']);
            
            if ($issuedItem->issuance->issuance_type === 'Permanent') {
                throw new \Exception("Permanently issued items cannot be returned.");
            }
            
            if ($validated['return_qty'] > $issuedItem->quantity) {
                throw new \Exception("Return quantity cannot exceed issued quantity.");
            }

            // Restore stock to the most recent batch of this item+ledge
            // In a real system, we might want to return to the specific batches it came from, 
            // but for simplicity we'll just add it back to the first available batch for that description/category.
            $qtyToReturn = floatval($validated['return_qty']);
            
            // Find all matching inventory items (batches) for this specific asset
            $inventoryItems = InventoryItem::where('description', $issuedItem->description)
                ->whereHas('batch', function($q) use ($issuedItem) {
                    $q->where('ledge_category', $issuedItem->ledge_category);
                })
                ->orderBy('created_at', 'desc') // Fill newer/partially-used batches first as requested
                ->orderBy('id', 'desc')
                ->get();

            if ($inventoryItems->isEmpty()) {
                throw new \Exception("Could not find a valid inventory destination for this item in the registry.");
            }

            $remainingToRefill = $qtyToReturn;

            // Phase 1: Refill depleted batches back to their original stock_balance levels
            foreach ($inventoryItems as $invItem) {
                if ($remainingToRefill <= 0) break;
                
                $stockLimit = floatval($invItem->stock_balance);
                $currentQty = floatval($invItem->qty);
                
                $room = $stockLimit - $currentQty;
                
                if ($room > 0) {
                    $refill = min($room, $remainingToRefill);
                    $invItem->qty = $currentQty + $refill;
                    $invItem->save();
                    $remainingToRefill -= $refill;
                }
            }

            // Phase 2: If there's still quantity left (e.g., returned more than originally issued from these batches),
            // add the overflow to the most recent batch.
            if ($remainingToRefill > 0) {
                $latestItem = $inventoryItems->last();
                $latestItem->qty = floatval($latestItem->qty) + $remainingToRefill;
                $latestItem->save();
            }

            // Update issued item quantity or mark as returned
            // For now, let's just reduce the issued quantity
            $issuedItem->quantity -= $validated['return_qty'];
            $issuedItem->save();

            ReturnedItem::create([
                'issued_item_id' => $issuedItem->id,
                'returned_qty' => $validated['return_qty'],
                'return_date' => $validated['return_date'],
                'remarks' => $validated['remarks'] ?? null,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Item returned successfully to inventory.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Return failed: ' . $e->getMessage());
        }
    }

    public function history()
    {
        if (!Schema::hasTable('returned_items')) {
            return response()->json([]);
        }

        $returnedItems = ReturnedItem::join('issued_items', 'returned_items.issued_item_id', '=', 'issued_items.id')
            ->join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->select(
                'returned_items.id', 
                'returned_items.returned_qty',
                'returned_items.return_date',
                'returned_items.remarks',
                'returned_items.created_at',
                'issued_items.description', 
                'issued_items.ledge_category',
                'issued_items.quantity as current_balance',
                'issuances.beneficiary',
                'issuances.authority',
                'issuances.issuance_date',
                'issuances.created_at as issuance_timestamp',
                'issued_items.unit'
            )
            ->orderBy('returned_items.created_at', 'desc')
            ->get();

        return response()->json($returnedItems);
    }

    public function purge(Request $request)
    {
        try {
            $ids = $request->input('ids');
            
            if (!$ids || !is_array($ids)) {
                return redirect()->back()->with('error', 'Audit Error: No valid recovery IDs detected for purge.');
            }

            // Direct SQL for maximum reliability
            $idString = implode(',', array_map('intval', $ids));
            \Illuminate\Support\Facades\DB::statement("DELETE FROM returned_items WHERE id IN ($idString)");

            return redirect()->back()->with([
                'success' => count($ids) . ' records successfully purged from NACOC logs.',
                'reopen_history' => true
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Purge Protocol Failed: ' . $e->getMessage());
        }
    }
}
