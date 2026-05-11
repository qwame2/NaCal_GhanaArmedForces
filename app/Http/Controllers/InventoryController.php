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
                    
                    $msgContent = "<div class='sra-approval-card' style='background: white; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); margin: 10px 0;'>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 12px; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;'>";
                    $msgContent .= "<div style='width: 40px; height: 40px; background: #4f46e5; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;'>";
                    $msgContent .= "<i data-lucide='shield-alert' style='width: 20px;'></i>";
                    $msgContent .= "</div><div>";
                    $msgContent .= "<h4 style='margin: 0; color: #0f172a; font-size: 0.95rem; font-weight: 800; letter-spacing: -0.01em;'>SRA APPROVAL REQUIRED</h4>";
                    $msgContent .= "<p style='margin: 0; font-size: 0.75rem; color: #64748b; font-weight: 600;'>Pending Strategic Entry Verification</p>";
                    $msgContent .= "</div></div>";
                    
                    $msgContent .= "<div style='display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;'>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 8px;'><div style='width: 24px; height: 24px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #64748b;'><i data-lucide='user' style='width: 14px;'></i></div><span style='font-size: 0.85rem; color: #475569;'><b style='color: #0f172a;'>Personnel:</b> " . auth()->user()->name . "</span></div>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 8px;'><div style='width: 24px; height: 24px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #64748b;'><i data-lucide='truck' style='width: 14px;'></i></div><span style='font-size: 0.85rem; color: #475569;'><b style='color: #0f172a;'>Source:</b> {$source}</span></div>";
                    $msgContent .= "<div style='display: flex; align-items: flex-start; gap: 8px;'><div style='width: 24px; height: 24px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #64748b; margin-top: 2px;'><i data-lucide='package' style='width: 14px;'></i></div><span style='font-size: 0.85rem; color: #475569; line-height: 1.4;'><b style='color: #0f172a;'>Items:</b> {$itemNames}</span></div>";
                    $msgContent .= "</div>";
                    
                    $msgContent .= "<div id='sra-creation-actions-{$editReq->id}' style='display: flex; flex-direction: column; gap: 8px;'>";
                    $msgContent .= "<a href='".route('sra.preview', ['id' => $editReq->id])."' target='_blank' style='display: flex; align-items: center; justify-content: center; gap: 8px; background: #f8fafc; color: #0f172a; text-decoration: none; padding: 12px; border-radius: 10px; font-weight: 700; font-size: 0.85rem; border: 1px solid #e2e8f0;'>";
                    $msgContent .= "<i data-lucide='eye' style='width: 16px;'></i> Preview Draft SRA</a>";
                    $msgContent .= "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 8px;'>";
                    $msgContent .= "<button onclick='window.processSraCreationApproval({$editReq->id}, \"approved\", this)' style='background: #10b981; color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px;'>";
                    $msgContent .= "<i data-lucide='check-circle' style='width: 16px;'></i> Approve</button>";
                    $msgContent .= "<button onclick='window.processSraCreationApproval({$editReq->id}, \"rejected\", this)' style='background: #ef4444; color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px;'>";
                    $msgContent .= "<i data-lucide='x-circle' style='width: 16px;'></i> Reject</button>";
                    $msgContent .= "</div></div></div>";

                    \App\Models\Message::create([
                        'sender_id' => auth()->id(),
                        'receiver_id' => $admin->id,
                        'message' => $msgContent,
                        'is_automated' => true,
                        'edit_request_id' => $editReq->id
                    ]);
                }

                // Send confirmation to Personnel
                $confirmation = "<div style='padding: 15px; border: 1px solid #4f46e5; border-radius: 16px; background: rgba(79, 70, 229, 0.03); display: flex; align-items: center; gap: 12px;'>";
                $confirmation .= "<div style='width: 32px; height: 32px; background: #4f46e5; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;'><i data-lucide='clock' style='width: 16px;'></i></div>";
                $confirmation .= "<div><b style='color: #4f46e5; font-size: 0.85rem;'>SRA SUBMISSION LOGGED</b><br><span style='font-size: 0.75rem; color: #64748b; font-weight: 600;'>Awaiting Strategic Authorization from HQ.</span></div>";
                $confirmation .= "</div>";

                \App\Models\Message::create([
                    'sender_id' => $admin->id ?? 1,
                    'receiver_id' => auth()->id(),
                    'message' => $confirmation,
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
                    'subtitle' => ($isDonor ? "Donor" : "Supplier") . " • {$catName} Source • Recorded " . date('M d, Y', strtotime($batch->entry_date)),
                    'url' => route('receiveditems', [$isDonor ? 'donor' : 'supplier' => $isDonor ? $batch->donor_name : $batch->supplier_name]),
                    'type' => 'source',
                    'icon' => $isDonor ? 'heart' : 'truck'
                ];
            });

        return response()->json($categoryResults->merge($items)->merge($batches));
    }
}
