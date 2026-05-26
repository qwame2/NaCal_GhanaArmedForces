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
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized: Department Heads are only allowed to view received items and cannot make changes.'], 403);
        }

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
            'items.*.location' => 'nullable|string',
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

                // Send Approval Request to all Admins
                $admins = \App\Models\User::where('is_admin', true)->get();
                if ($admins->count() > 0) {
                    $itemNames = collect($validated['items'])->pluck('description')->take(3)->implode(', ');
                    if (count($validated['items']) > 3) $itemNames .= ' etc.';
                    $source = $validated['acquisition_type'] === 'Donor' ? $validated['donor_name'] : $validated['supplier_name'];
                    
                    $msgContent = "<div class='sra-approval-card' style='background: white; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); margin: 10px 0;'>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 12px; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;'>";
                    $msgContent .= "<div style='width: 40px; height: 40px; background: #6366f1; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;'>";
                    $msgContent .= "<i data-lucide='package-plus' style='width: 20px;'></i>";
                    $msgContent .= "</div><div>";
                    $msgContent .= "<h4 style='margin: 0; color: #0f172a; font-size: 0.95rem; font-weight: 800; letter-spacing: -0.01em;'>STOCK ENTRY APPROVAL</h4>";
                    $msgContent .= "<p style='margin: 0; font-size: 0.75rem; color: #64748b; font-weight: 600;'>Pending Strategic Entry Verification</p>";
                    $msgContent .= "</div></div>";
                    
                    $msgContent .= "<div id='sra-creation-actions-{$editReq->id}' style='display: flex; flex-direction: column; gap: 8px;'>";
                    $msgContent .= "<button data-entry-req-id='{$editReq->id}' class='entry-preview-btn' style='display: flex; align-items: center; justify-content: center; gap: 8px; background: #f8fafc; color: #0f172a; border: 1px solid #e2e8f0; padding: 12px; border-radius: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: 0.3s;'>";
                    $msgContent .= "<i data-lucide='eye' style='width: 16px;'></i> Preview Entry Details</button>";
                    $msgContent .= "</div></div>";
 
                    foreach ($admins as $admin) {
                        \App\Models\Message::create([
                            'sender_id' => auth()->id(),
                            'receiver_id' => $admin->id,
                            'message' => $msgContent,
                            'is_automated' => true,
                            'edit_request_id' => $editReq->id
                        ]);
                    }
                }

                // Send confirmation back to the user
                $firstAdmin = \App\Models\User::where('is_admin', true)->first();
                if ($firstAdmin) {
                    $confirmMsg = "<!-- sra_req_id:{$editReq->id} -->"
                        . "<div class='sra-awaiting-msg personnel-view' style='padding: 15px 18px; border: 1.5px solid #c7d2fe; border-radius: 16px; background: rgba(99,102,241,0.04); display: flex; align-items: center; gap: 12px;'>"
                        . "<div style='width: 36px; height: 36px; background: #6366f1; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;'>"
                        . "<svg style='width:18px;height:18px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>"
                        . "</div>"
                        . "<div>"
                        . "<b style='color: #4f46e5; font-size: 0.88rem; display: block; margin-bottom: 2px;'>ENTRY SUBMITTED FOR AUTHORIZATION</b>"
                        . "<span style='font-size: 0.78rem; color: #64748b; font-weight: 600;'>Awaiting Authorization — Your submission is pending review by the Admin.</span>"
                        . "</div></div>";

                    \App\Models\Message::create([
                        'sender_id'       => $firstAdmin->id,
                        'receiver_id'     => auth()->id(),
                        'message'         => $confirmMsg,
                        'is_automated'    => true,
                        'edit_request_id' => $editReq->id,
                    ]);
                }

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

        $ledgeMap = \Illuminate\Support\Facades\Schema::hasTable('settings') 
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
                    'subtitle' => ($isDonor ? "Donor" : "Supplier") . " • {$catName} Source • Recorded " . date('d/m/y', strtotime($batch->entry_date)),
                    'url' => route('receiveditems', [$isDonor ? 'donor' : 'supplier' => $isDonor ? $batch->donor_name : $batch->supplier_name]),
                    'type' => 'source',
                    'icon' => $isDonor ? 'heart' : 'truck'
                ];
            });

        return response()->json($categoryResults->merge($items)->merge($batches));
    }
}
