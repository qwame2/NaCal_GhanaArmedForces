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
            'supplier_name' => 'nullable|string',
            'supplier_status' => 'required|string',
            'donor_name' => 'nullable|string',
            'acquisition_type' => 'required|string',
            'entry_date' => 'required',
            'arrival_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'required|string',

            'items.*.stock_balance' => 'required|string',
            'items.*.qty' => 'nullable|string',
            'items.*.variance' => 'required|string',

            'items.*.remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Create the Batch or Stage it for Approval
            $is_admin = auth()->user()->is_admin;
            
            if (!$is_admin) {
                // Divert to staged approval process (Don't save items yet)
                $editReq = \App\Models\EditRequest::create([
                    'user_id' => auth()->id(),
                    'item_id' => 0, // Fallback for creation requests
                    'item_type' => 'batch_creation',
                    'request_type' => 'sra_creation',
                    'reason' => 'New Inventory Entry Submission',
                    'status' => 'pending',
                    'payload' => json_encode($validated)
                ]);

                // Send Approval Request to Admin
                $admin = \App\Models\User::where('is_admin', true)->first();
                if ($admin) {
                    $itemNames = collect($validated['items'])->pluck('description')->take(3)->implode(', ');
                    if (count($validated['items']) > 3) $itemNames .= ' etc.';
                    $source = $validated['acquisition_type'] === 'Donor' ? $validated['donor_name'] : $validated['supplier_name'];
                    
                    $msgContent = "<div class='sra-approval-msg' style='padding: 15px; border-left: 4px solid #4f46e5; background: rgba(79, 70, 229, 0.05);'>";
                    $msgContent .= "<b style='color: #4f46e5;'>NEW SRA APPROVAL REQUIRED</b><br>";
                    $msgContent .= "Personnel <b>" . auth()->user()->name . "</b> has recorded a new batch from <b>{$source}</b>.<br>";
                    $msgContent .= "<b>Items:</b> {$itemNames}<br><br>";
                    $msgContent .= "<div id='sra-creation-actions-{$editReq->id}'>";
                    $msgContent .= "<a href='".route('sra.preview', ['id' => $editReq->id])."' target='_blank' style='display: inline-block; background: #6366f1; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; margin-bottom: 12px; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);'>Preview Draft SRA</a><br>";
                    $msgContent .= "<button onclick='window.processSraCreationApproval({$editReq->id}, \"approved\", this)' style='background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 800; font-size: 0.85rem; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);'>Approve & Save</button> ";
                    $msgContent .= "<button onclick='window.processSraCreationApproval({$editReq->id}, \"rejected\", this)' style='background: #dc2626; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 800; font-size: 0.85rem;'>Reject</button>";
                    $msgContent .= "</div></div>";

                    \App\Models\Message::create([
                        'sender_id' => auth()->id(),
                        'receiver_id' => $admin->id,
                        'message' => $msgContent,
                        'is_automated' => true,
                        'edit_request_id' => $editReq->id
                    ]);
                }

                // Send confirmation to Personnel
                \App\Models\Message::create([
                    'sender_id' => $admin->id ?? 1,
                    'receiver_id' => auth()->id(),
                    'message' => "<div style='padding: 15px; border: 1px solid #4f46e5; border-radius: 12px; background: rgba(79, 70, 229, 0.05);'><b style='color: #4f46e5'>SRA SUBMISSION LOGGED</b><br>Awaiting SRA Approval from Admin for your recent entry.</div>",
                    'is_automated' => true,
                    'edit_request_id' => $editReq->id
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'is_pending' => true,
                    'message' => 'Submission pending admin approval. The record will be saved once authorized.'
                ]);
            }

            // IF ADMIN: Create the Batch Immediately
            $batch = InventoryBatch::create([
                'ledge_category' => $validated['ledge_category'],
                'supplier_name' => $validated['supplier_name'],
                'supplier_status' => $validated['supplier_status'],
                'donor_name' => $validated['donor_name'],
                'acquisition_type' => $validated['acquisition_type'],
                'entry_date' => $validated['entry_date'],
                'arrival_date' => $validated['arrival_date'],
                'approval_status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Create the Items
            foreach ($validated['items'] as $item) {
                $itemData = $item;
                unset($itemData['ledge_balance']);
                $batch->items()->create($itemData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventory records saved successfully!',
                'batch_id' => $batch->id
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
                if (stripos($name, $query) !== false || stripos($code, $query) !== false || stripos("Category $code", $query) !== false) {
                    $categoryResults->push([
                        'title' => "Category $code ($name)",
                    'subtitle' => "Major Category Section",
                    'url' => route('receiveditems', ['ledge_category' => $code]),
                    'type' => 'category',
                    'icon' => 'layers'
                ]);
            }
        }

        // 2. Search in specific items
        $items = InventoryItem::where('description', 'LIKE', "%$query%")
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'title' => $item->description,
                    'subtitle' => "Stock: {$item->stock_balance}",
                    'url' => route('receiveditems', ['search' => $item->description]),
                    'type' => 'item',
                    'icon' => 'package'
                ];
            });

        // 3. Search in log sources (suppliers or donors or batch codes)
        $batches = InventoryBatch::where('supplier_name', 'LIKE', "%$query%")
            ->orWhere('donor_name', 'LIKE', "%$query%")
            ->orWhere('ledge_category', 'LIKE', "%$query%")
            ->limit(3)
            ->get()
            ->map(function($batch) use ($ledgeMap) {
                $catName = $ledgeMap[$batch->ledge_category] ?? $batch->ledge_category;
                $isDonor = $batch->acquisition_type === 'Donor';
                return [
                    'title' => $isDonor ? $batch->donor_name : $batch->supplier_name,
                    'subtitle' => ($isDonor ? "Donor" : "Supplier") . " • {$catName} Source • Recorded " . date('M d, Y', strtotime($batch->entry_date)),
                    'url' => route('receiveditems', [$isDonor ? 'donor' : 'supplier' => $isDonor ? $batch->donor_name : $batch->supplier_name]),
                    'type' => 'source',
                    'icon' => $isDonor ? 'heart' : 'truck'
                ];
            });

        return response()->json($categoryResults->merge($items)->merge($batches));
    }
}
