<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\EditRequest;
use App\Models\Message;
use App\Models\User;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockCheckController extends Controller
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

    public function index(Request $request)
    {
        $ledgeMap = $this->getLedgeMap();

        // Fetch aggregate totals for all items to show a master verification list
        $query = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
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

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'physical_count' => 'required|integer|min:0',
            'condition' => 'required|string',
            'remarks' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $description = $validated['description'];
            $physicalCount = $validated['physical_count'];
            $condition = $validated['condition'];
            $remarks = $validated['remarks'];

            // Find all inventory items with this description, ordered by the latest batch entry
            $items = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                ->where('inventory_items.description', $description)
                ->select('inventory_items.*')
                ->orderBy('inventory_batches.entry_date', 'desc')
                ->get();

            if ($items->isEmpty()) {
                return response()->json(['success' => false, 'message' => "Item '{$description}' not found in registry."], 404);
            }

            // Calculate current total stock balance
            $currentStock = $items->sum('stock_balance');
            $variance = $physicalCount - $currentStock;

            $is_admin = auth()->user()->is_admin;

            if (!$is_admin) {
                // Non-admin: Stage the reconciliation/verification for admin approval
                $editReq = EditRequest::create([
                    'user_id' => auth()->id(),
                    'item_id' => $items->first()->id, // Target the latest item as reference
                    'item_type' => 'inventory_item',
                    'request_type' => 'stock_verification',
                    'reason' => "Stock Verification & Reconciliation: Item '{$description}' condition is '{$condition}'. Physical count: {$physicalCount} (Variance: " . ($variance > 0 ? '+' : '') . "{$variance}).",
                    'status' => 'pending',
                    'payload' => json_encode([
                        'description' => $description,
                        'physical_count' => $physicalCount,
                        'condition' => $condition,
                        'remarks' => $remarks,
                        'variance' => $variance,
                        'current_stock' => $currentStock
                    ])
                ]);

                // Notify Admins
                $admins = User::where('is_admin', true)->where('registration_status', 'approved')->get();
                if ($admins->count() > 0) {
                    $msgContent = "<div class='verification-approval-card' style='background: white; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); margin: 10px 0;'>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 12px; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;'>";
                    $msgContent .= "<div style='width: 40px; height: 40px; background: #3b82f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;'>";
                    $msgContent .= "<svg style='width: 20px; height: 20px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'></path></svg>";
                    $msgContent .= "</div><div>";
                    $msgContent .= "<h4 style='margin: 0; color: #0f172a; font-size: 0.95rem; font-weight: 800; letter-spacing: -0.01em;'>STOCK RECONCILIATION</h4>";
                    $msgContent .= "<p style='margin: 0; font-size: 0.75rem; color: #64748b; font-weight: 600;'>Pending Verification Approval</p>";
                    $msgContent .= "</div></div>";

                    $msgContent .= "<div style='display: flex; flex-direction: column; gap: 10px; margin-bottom: 15px;'>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 8px;'><span style='font-size: 0.85rem; color: #475569;'><b style='color: #0f172a;'>Verifier:</b> " . auth()->user()->name . "</span></div>";
                    $msgContent .= "</div>";

                    $msgContent .= "<div id='verification-actions-{$editReq->id}' style='display: flex; flex-direction: column; gap: 8px;'>";
                    $msgContent .= "<button class='reconciliation-preview-btn' data-reconciliation-req-id='{$editReq->id}' style='width: 100%; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; padding: 12px; border-radius: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;'>";
                    $msgContent .= "<svg style='width: 15px; height: 15px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'></path><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'></path></svg>";
                    $msgContent .= "Preview Details</button>";
                    $msgContent .= "</div></div>";

                    foreach ($admins as $admin) {
                        Message::create([
                            'sender_id' => auth()->id(),
                            'receiver_id' => $admin->id,
                            'message' => $msgContent,
                            'is_automated' => true,
                            'edit_request_id' => $editReq->id
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'is_pending' => true,
                    'message' => 'Stock verification submitted for Admin approval. Balances will update once approved.'
                ]);
            }

            // ADMIN: Reconcile Stock Balances immediately
            $this->reconcileStock($items, $physicalCount, $variance, $condition, $remarks);

            DB::commit();

            return response()->json([
                'success' => true,
                'is_pending' => false,
                'message' => 'Stock verification completed. Stock balances successfully reconciled!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Verification failed: ' . $e->getMessage()], 500);
        }
    }

    private function reconcileStock($items, $physicalCount, $variance, $condition, $remarks)
    {
        $remainingAdjustment = $variance;

        foreach ($items as $item) {
            if ($remainingAdjustment === 0) break;

            if ($remainingAdjustment > 0) {
                // Surplus: add to the newest item
                $item->stock_balance += $remainingAdjustment;
                $item->variance = (float)$item->variance + $remainingAdjustment;
                $item->save();
                $remainingAdjustment = 0;
            } else {
                // Shortage: subtract from items starting from latest
                $subtraction = min(abs($remainingAdjustment), $item->stock_balance);
                $item->stock_balance -= $subtraction;
                $item->variance = (float)$item->variance - $subtraction;
                $item->save();
                $remainingAdjustment += $subtraction;
            }
        }

        // Log the event
        SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'INVENTORY',
            'action' => 'STOCK_VERIFICATION',
            'description' => "Stock verification completed for item '{$items->first()->description}'. Physical count: {$physicalCount}. Condition: {$condition}. Remarks: {$remarks}.",
            'severity' => $variance === 0 ? 'info' : 'warning',
            'ip_address' => request()->ip()
        ]);
    }

    public function verifyBatch(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.physical_count' => 'required|integer|min:0',
            'items.*.condition' => 'required|string',
            'items.*.remarks' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $is_admin = auth()->user()->is_admin;
            $itemsData = $validated['items'];
            $stagedItems = [];
            $reconciledItems = [];

            foreach ($itemsData as $data) {
                $description = $data['description'];
                $physicalCount = $data['physical_count'];
                $condition = $data['condition'];
                $remarks = $data['remarks'];

                $dbItems = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                    ->where('inventory_items.description', $description)
                    ->select('inventory_items.*')
                    ->orderBy('inventory_batches.entry_date', 'desc')
                    ->get();

                if ($dbItems->isEmpty()) {
                    throw new \Exception("Item '{$description}' not found in registry.");
                }

                $currentStock = $dbItems->sum('stock_balance');
                $variance = $physicalCount - $currentStock;

                if (!$is_admin) {
                    $stagedItems[] = [
                        'description' => $description,
                        'physical_count' => $physicalCount,
                        'condition' => $condition,
                        'remarks' => $remarks,
                        'variance' => $variance,
                        'current_stock' => $currentStock,
                        'db_item_id' => $dbItems->first()->id
                    ];
                } else {
                    $this->reconcileStock($dbItems, $physicalCount, $variance, $condition, $remarks);
                    $reconciledItems[] = $description;
                }
            }

            if (!$is_admin) {
                // Non-admin: Stage all in a single batch EditRequest
                $editReq = EditRequest::create([
                    'user_id' => auth()->id(),
                    'item_id' => $stagedItems[0]['db_item_id'], // Reference first item
                    'item_type' => 'inventory_item',
                    'request_type' => 'batch_stock_verification',
                    'reason' => "Batch Stock Verification: " . count($stagedItems) . " items staged.",
                    'status' => 'pending',
                    'payload' => json_encode([
                        'items' => $stagedItems
                    ])
                ]);

                // Notify Admins with a single detailed batch card
                $admins = User::where('is_admin', true)->where('registration_status', 'approved')->get();
                if ($admins->count() > 0) {
                    $msgContent = "<div class='verification-approval-card' style='background: white; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); margin: 10px 0;'>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 12px; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;'>";
                    $msgContent .= "<div style='width: 40px; height: 40px; background: #6366f1; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;'>";
                    $msgContent .= "<svg style='width: 20px; height: 20px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'></path></svg>";
                    $msgContent .= "</div><div>";
                    $msgContent .= "<h4 style='margin: 0; color: #0f172a; font-size: 0.95rem; font-weight: 800; letter-spacing: -0.01em;'>BATCH STOCK RECONCILIATION</h4>";
                    $msgContent .= "<p style='margin: 0; font-size: 0.75rem; color: #64748b; font-weight: 600;'>Pending " . count($stagedItems) . " Items Approval</p>";
                    $msgContent .= "</div></div>";

                    $msgContent .= "<div style='display: flex; flex-direction: column; gap: 10px; margin-bottom: 15px;'>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 8px;'><span style='font-size: 0.85rem; color: #475569;'><b style='color: #0f172a;'>Verifier:</b> " . auth()->user()->name . "</span></div>";
                    $msgContent .= "</div>";

                    $msgContent .= "<div id='verification-actions-{$editReq->id}' style='display: flex; flex-direction: column; gap: 8px;'>";
                    $msgContent .= "<button class='reconciliation-preview-btn' data-reconciliation-req-id='{$editReq->id}' style='width: 100%; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; padding: 12px; border-radius: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;'>";
                    $msgContent .= "<svg style='width: 15px; height: 15px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'></path><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'></path></svg>";
                    $msgContent .= "Preview Batch Details</button>";
                    $msgContent .= "</div></div>";

                    foreach ($admins as $admin) {
                        Message::create([
                            'sender_id' => auth()->id(),
                            'receiver_id' => $admin->id,
                            'message' => $msgContent,
                            'is_automated' => true,
                            'edit_request_id' => $editReq->id
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'is_pending' => true,
                    'message' => 'Batch verification submitted for Admin approval. Stock balances will update once approved.'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'is_pending' => false,
                'message' => 'Batch verification completed. ' . count($reconciledItems) . ' stock balances reconciled successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Batch verification failed: ' . $e->getMessage()], 500);
        }
    }

    public function batchView(Request $request)
    {
        $descriptions = $request->input('descriptions', []);
        if (empty($descriptions)) {
            return redirect()->route('stockcheck.index')->with('error', 'No items selected for batch verification.');
        }

        $ledgeMap = $this->getLedgeMap();

        // Fetch aggregate totals for selected items
        $items = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->whereIn('inventory_items.description', $descriptions)
            ->selectRaw('
                inventory_items.description, 
                inventory_batches.ledge_category,
                MAX(inventory_items.unit) as unit,
                SUM(inventory_items.stock_balance) as total_available,
                SUM(inventory_items.qty) as total_received,
                SUM(inventory_items.variance) as total_variance
            ')
            ->groupBy('inventory_items.description', 'inventory_batches.ledge_category')
            ->orderBy('inventory_items.description', 'asc')
            ->get();

        // Retrieve active temporary loan details
        $loans = \App\Models\IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->leftJoin('store_requisitions', 'issuances.requisition_id', '=', 'store_requisitions.id')
            ->whereIn('issued_items.description', $descriptions)
            ->where('issuances.issuance_type', 'Temporary')
            ->where('issued_items.quantity', '>', 0)
            ->select(
                'issued_items.id as issued_item_id',
                'issued_items.description as item_desc',
                'issued_items.quantity',
                'issued_items.unit',
                'issuances.beneficiary',
                'issuances.authority',
                'issuances.issuance_date',
                'store_requisitions.purpose',
                'store_requisitions.created_at as requisition_created_at',
                'store_requisitions.department'
            )
            ->get()
            ->map(function($loan) {
                $returnDateStr = null;
                if (preg_match('/\[Expected Return Date:\s*([^\]]+)\]/i', $loan->purpose, $matches)) {
                    try {
                        $returnDateStr = \App\Models\Setting::parseExpectedReturnDate(trim($matches[1]))->format('Y-m-d');
                    } catch (\Exception $e) {}
                }
                if (!$returnDateStr && $loan->requisition_created_at) {
                    $returnDateStr = \Carbon\Carbon::parse($loan->requisition_created_at)->format('Y-m-d');
                }
                $loan->is_overdue = $returnDateStr ? (\Carbon\Carbon::now()->format('Y-m-d') >= $returnDateStr) : false;
                $loan->expected_return_date = $returnDateStr ? \Carbon\Carbon::parse($returnDateStr)->format('d/m/Y') : 'N/A';
                return $loan;
            });

        return view('stock-check.batch', compact('items', 'ledgeMap', 'loans'));
    }
}
