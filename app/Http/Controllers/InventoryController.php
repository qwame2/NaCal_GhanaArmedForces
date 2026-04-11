<?php

namespace App\Http\Controllers;

use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ledge_category' => 'required|string',
            'supplier_name' => 'required|string',
            'entry_date' => 'required',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.folio' => 'required|string',
            'items.*.ledge_balance' => 'required|string',
            'items.*.stock_balance' => 'required|string',
            'items.*.variance' => 'required|string',
            'items.*.remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Create the Batch
            $batch = InventoryBatch::create([
                'ledge_category' => $validated['ledge_category'],
                'supplier_name' => $validated['supplier_name'],
                'entry_date' => $validated['entry_date'],
            ]);

            // Create the Items
            foreach ($validated['items'] as $item) {
                $batch->items()->create($item);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventory records saved successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save records: ' . $e->getMessage()
            ], 500);
        }
    }

    public function globalSearch(Request $request)
    {
        $query = $request->get('q');
        if (!$query) return response()->json([]);

        $ledgeMap = [
            'A' => 'Stationary',
            'B' => 'Cleaning',
            'C' => 'IT & Acc.',
            'D' => 'Transport',
            'E' => 'Safety',
            'G' => 'Pharmacy',
            'J' => 'Equipment'
        ];

        // 1. Check for Category Match (Ledge Name or Code)
        $categoryResults = collect();
        foreach ($ledgeMap as $code => $name) {
            if (stripos($name, $query) !== false || stripos($code, $query) !== false || stripos("Ledge $code", $query) !== false) {
                $categoryResults->push([
                    'title' => "Ledge $code ($name)",
                    'subtitle' => "Major Category Section",
                    'url' => route('receiveditems', ['ledge_category' => $code]),
                    'type' => 'category',
                    'icon' => 'layers'
                ]);
            }
        }

        // 2. Search in specific items
        $items = InventoryItem::where('description', 'LIKE', "%$query%")
            ->orWhere('folio', 'LIKE', "%$query%")
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'title' => $item->description,
                    'subtitle' => "Folio: {$item->folio} • Stock: {$item->stock_balance}",
                    'url' => route('receiveditems', ['search' => $item->description]),
                    'type' => 'item',
                    'icon' => 'package'
                ];
            });

        // 3. Search in log sources (suppliers or batch codes)
        $batches = InventoryBatch::where('supplier_name', 'LIKE', "%$query%")
            ->orWhere('ledge_category', 'LIKE', "%$query%")
            ->limit(3)
            ->get()
            ->map(function($batch) use ($ledgeMap) {
                $catName = $ledgeMap[$batch->ledge_category] ?? $batch->ledge_category;
                return [
                    'title' => $batch->supplier_name,
                    'subtitle' => "{$catName} Source • Recorded " . date('M d, Y', strtotime($batch->entry_date)),
                    'url' => route('receiveditems', ['supplier' => $batch->supplier_name]),
                    'type' => 'source',
                    'icon' => 'truck'
                ];
            });

        return response()->json($categoryResults->merge($items)->merge($batches));
    }
}
