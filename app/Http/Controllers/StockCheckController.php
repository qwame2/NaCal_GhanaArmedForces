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
                $admins = User::where('is_admin', true)->get();
                if ($admins->count() > 0) {
                    $msgContent = "<div class='verification-approval-card' style='background: white; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); margin: 10px 0;'>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 12px; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;'>";
                    $msgContent .= "<div style='width: 40px; height: 40px; background: #3b82f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;'>";
                    $msgContent .= "<svg style='width: 20px; height: 20px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'></path></svg>";
                    $msgContent .= "</div><div>";
                    $msgContent .= "<h4 style='margin: 0; color: #0f172a; font-size: 0.95rem; font-weight: 800; letter-spacing: -0.01em;'>STOCK RECONCILIATION</h4>";
                    $msgContent .= "<p style='margin: 0; font-size: 0.75rem; color: #64748b; font-weight: 600;'>Pending Verification Approval</p>";
                    $msgContent .= "</div></div>";

                    $msgContent .= "<div style='display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;'>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 8px;'><span style='font-size: 0.85rem; color: #475569;'><b style='color: #0f172a;'>Verifier:</b> " . auth()->user()->name . "</span></div>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 8px;'><span style='font-size: 0.85rem; color: #475569;'><b style='color: #0f172a;'>Item:</b> {$description}</span></div>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 8px;'><span style='font-size: 0.85rem; color: #475569;'><b style='color: #0f172a;'>Physical Count:</b> {$physicalCount}</span></div>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 8px;'><span style='font-size: 0.85rem; color: #475569;'><b style='color: #0f172a;'>System Stock:</b> {$currentStock}</span></div>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 8px;'><span style='font-size: 0.85rem; color: " . ($variance < 0 ? '#ef4444' : '#10b981') . ";'><b style='color: #0f172a;'>Discrepancy:</b> " . ($variance > 0 ? '+' : '') . "{$variance}</span></div>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 8px;'><span style='font-size: 0.85rem; color: #475569;'><b style='color: #0f172a;'>Condition:</b> {$condition}</span></div>";
                    $msgContent .= "<div style='display: flex; align-items: flex-start; gap: 8px;'><span style='font-size: 0.85rem; color: #475569;'><b style='color: #0f172a;'>Remarks:</b> {$remarks}</span></div>";
                    $msgContent .= "</div>";

                    $msgContent .= "<div id='verification-actions-{$editReq->id}' style='display: flex; flex-direction: column; gap: 8px;'>";
                    $msgContent .= "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 8px;'>";
                    $msgContent .= "<button onclick='window.processVerificationApproval({$editReq->id}, \"approved\", this)' style='background: #10b981; color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px;'>Approve</button>";
                    $msgContent .= "<button onclick='window.processVerificationApproval({$editReq->id}, \"rejected\", this)' style='background: #ef4444; color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px;'>Reject</button>";
                    $msgContent .= "</div></div></div>";

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
                $admins = User::where('is_admin', true)->get();
                if ($admins->count() > 0) {
                    $msgContent = "<div class='verification-approval-card' style='background: white; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); margin: 10px 0;'>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 12px; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;'>";
                    $msgContent .= "<div style='width: 40px; height: 40px; background: #6366f1; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;'>";
                    $msgContent .= "<svg style='width: 20px; height: 20px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'></path></svg>";
                    $msgContent .= "</div><div>";
                    $msgContent .= "<h4 style='margin: 0; color: #0f172a; font-size: 0.95rem; font-weight: 800; letter-spacing: -0.01em;'>BATCH STOCK RECONCILIATION</h4>";
                    $msgContent .= "<p style='margin: 0; font-size: 0.75rem; color: #64748b; font-weight: 600;'>Pending " . count($stagedItems) . " Items Approval</p>";
                    $msgContent .= "</div></div>";

                    $msgContent .= "<div style='margin-bottom: 20px; max-height: 250px; overflow-y: auto; border: 1px solid #f1f5f9; border-radius: 10px;'>";
                    $msgContent .= "<table style='width:100%; border-collapse:collapse; font-size:0.8rem; text-align:left;'>";
                    $msgContent .= "<thead style='background:#f8fafc; border-bottom:1px solid #e2e8f0;'><tr style='color:#475569;'>";
                    $msgContent .= "<th style='padding:8px;'>Item</th><th style='padding:8px;'>Sys</th><th style='padding:8px;'>Phys</th><th style='padding:8px;'>Var</th><th style='padding:8px;'>Cond</th>";
                    $msgContent .= "</tr></thead><tbody>";

                    foreach ($stagedItems as $si) {
                        $varStyle = $si['variance'] === 0 ? 'color:#10b981;' : ($si['variance'] > 0 ? 'color:#6366f1;' : 'color:#ef4444;');
                        $msgContent .= "<tr style='border-bottom:1px solid #f1f5f9; color:#475569;'>";
                        $msgContent .= "<td style='padding:8px; font-weight:700; color:#0f172a;'>{$si['description']}</td>";
                        $msgContent .= "<td style='padding:8px;'>{$si['current_stock']}</td>";
                        $msgContent .= "<td style='padding:8px; font-weight:700; color:#0f172a;'>{$si['physical_count']}</td>";
                        $msgContent .= "<td style='padding:8px; font-weight:800; {$varStyle}'>" . ($si['variance'] > 0 ? '+' : '') . "{$si['variance']}</td>";
                        $msgContent .= "<td style='padding:8px;'>{$si['condition']}</td>";
                        $msgContent .= "</tr>";
                    }
                    $msgContent .= "</tbody></table></div>";

                    $msgContent .= "<div id='verification-actions-{$editReq->id}' style='display: flex; flex-direction: column; gap: 8px;'>";
                    $msgContent .= "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 8px;'>";
                    $msgContent .= "<button onclick='window.processVerificationApproval({$editReq->id}, \"approved\", this)' style='background: #10b981; color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px;'>Approve Batch</button>";
                    $msgContent .= "<button onclick='window.processVerificationApproval({$editReq->id}, \"rejected\", this)' style='background: #ef4444; color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px;'>Reject Batch</button>";
                    $msgContent .= "</div></div></div>";

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
}
