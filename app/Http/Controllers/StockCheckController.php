<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockCheckController extends Controller
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

        // Fetch aggregate totals for all items to show a master verification list
        $query = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->selectRaw('
                inventory_items.description, 
                inventory_batches.ledge_category,
                MAX(inventory_items.unit) as unit,
                SUM(inventory_items.stock_balance) as total_available,
                SUM(inventory_items.qty) as total_received,
                SUM(inventory_items.variance) as total_variance
            ')
            ->groupBy('inventory_items.description', 'inventory_batches.ledge_category');

        // Search filter
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where('inventory_items.description', 'LIKE', '%' . $searchTerm . '%');
        }

        // Category filter
        if ($request->has('category') && $request->category) {
            $query->where('inventory_batches.ledge_category', $request->category);
        }

        $items = $query->orderBy('inventory_items.description', 'asc')->get();

        return view('stock-check.index', compact('items', 'ledgeMap'));
    }
}
