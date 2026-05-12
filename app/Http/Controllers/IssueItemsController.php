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
        if (auth()->user()->is_admin) {
            return redirect()->route('admin.inventory')->with('info', 'Strategic Oversight required. Redirecting to Command Center.');
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('issued_items', 'unit')) {
            \Illuminate\Support\Facades\Schema::table('issued_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('unit')->nullable();
            });
        }

        // Get unique items by description and sum up their available qty
        $items = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->selectRaw('inventory_items.description, inventory_batches.ledge_category, MAX(inventory_items.unit) as unit, SUM(inventory_items.qty) as total_stock')
            ->groupBy('inventory_items.description', 'inventory_batches.ledge_category')
            ->get();

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

        $adminName = \App\Models\User::where('is_admin', true)->value('name') ?? 'Administrator';
        return view('issue-items.index', compact('items', 'ledgeMap', 'adminName'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'issuance_date' => 'required|date',
            'beneficiary' => 'required|string',
            'authority' => 'required|string',
            'issuance_type' => 'required|string|in:Permanent,Temporary',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.category' => 'required|string',
            'items.*.unit' => 'nullable|string',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        try {
            $itemCount = count($validated['items']);
            $firstfew = collect($validated['items'])->pluck('description')->take(3)->implode(', ');
            if ($itemCount > 3) $firstfew .= ' etc.';

            $editReq = \App\Models\EditRequest::create([
                'user_id' => auth()->id(),
                'item_type' => 'issuance',
                'item_id' => 0, // No specific item ID yet
                'request_type' => 'issue_submission',
                'reason' => "Personnel requested to issue {$itemCount} items to {$validated['beneficiary']} on authority of {$validated['authority']}.",
                'payload' => json_encode($validated),
                'status' => 'pending'
            ]);

            $admins = \App\Models\User::where('is_admin', true)->get();
            if ($admins->count() > 0) {
                // Let's create an informative message for the admins to review
                $msgContent = "
                <div class='edit-req-msg admin-view' style='padding: 24px; background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px -10px rgba(79, 70, 229, 0.15); position: relative; overflow: hidden;'>
                    <div style='position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #f59e0b, #fbbf24);'></div>
                    <div style='display: flex; align-items: center; gap: 10px; margin-bottom: 16px;'>
                        <div style='width: 32px; height: 32px; background: rgba(245, 158, 11, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #f59e0b;'>
                            <svg style='width: 16px; height: 16px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'></path></svg>
                        </div>
                        <b style='font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; color: #f59e0b;'>DISBURSEMENT OVERSIGHT REQUIRED</b>
                    </div>
                    
                    <div style='font-size: 0.95rem; color: #334155; line-height: 1.6; margin-bottom: 20px;'>
                        Personnel <b style='color: #f59e0b;'>{$editReq->user->name}</b> has submitted a request to <b style='color: #f59e0b;'>ISSUE ITEMS</b> to <span style='font-family: monospace; font-weight: 700; color: #0f172a;'>{$validated['beneficiary']}</span>.
                        <br><br>
                        <b>Items requested:</b> {$firstfew}
                    </div>

                    <div style='margin-bottom: 15px;'>
                        <button class='entry-preview-btn' data-entry-req-id='{$editReq->id}' style='width: 100%; background: #e0e7ff; color: #4f46e5; border: 1px solid #c7d2fe; padding: 12px; border-radius: 10px; cursor: pointer; font-size: 0.85rem; font-weight: 800; transition: 0.3s;' onmouseover='this.style.background=\"#c7d2fe\"' onmouseout='this.style.background=\"#e0e7ff\"'>
                            <svg style='width: 16px; height: 16px; display: inline; vertical-align: middle; margin-right: 6px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'/><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'/></svg> Preview Disbursement Details
                        </button>
                    </div>

                    <div style='display: flex; gap: 12px;' id='edit-req-actions-{$editReq->id}'>
                        <button onclick='window.processEditRequest({$editReq->id}, \"approved\", this)' style='flex: 1; background: #10b981; color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-size: 0.85rem; font-weight: 800; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); transition: 0.3s;' onmouseover='this.style.background=\"#059669\"' onmouseout='this.style.background=\"#10b981\"'>Approve</button>
                        <button onclick='window.processEditRequest({$editReq->id}, \"canceled\", this)' style='flex: 1; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: 12px; border-radius: 10px; cursor: pointer; font-size: 0.85rem; font-weight: 800; transition: 0.3s;' onmouseover='this.style.background=\"#e2e8f0\"; this.style.color=\"#0f172a\"' onmouseout='this.style.background=\"#f1f5f9\"; this.style.color=\"#64748b\"'>Decline</button>
                    </div>
                </div>";

                $personnelMsg = "<div class='edit-req-msg personnel-view' style='padding: 15px; border-radius: 12px; background: rgba(245, 158, 11, 0.05); border: 1px dashed #f59e0b;'>
                    <b style='color: #f59e0b;'>DISBURSEMENT REQUEST LOGGED</b><br>
                    Waiting for strategic authorization from Command to issue items to {$validated['beneficiary']}.
                </div>";

                // Message for admins
                foreach ($admins as $admin) {
                    \App\Models\Message::create([
                        'sender_id' => auth()->id(),
                        'receiver_id' => $admin->id,
                        'message' => $msgContent,
                        'is_automated' => true,
                        'edit_request_id' => $editReq->id
                    ]);
                }

                // Message for personnel
                \App\Models\Message::create([
                    'sender_id' => auth()->id(),
                    'receiver_id' => auth()->id(), // self
                    'message' => $personnelMsg,
                    'is_automated' => true,
                    'edit_request_id' => $editReq->id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Disbursement request submitted. Awaiting administrative approval.',
                'pending_approval' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Issuance submission failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function history()
    {
        $issuedItems = IssuedItem::with('issuance')
            ->join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->select('issued_items.*', 'issuances.issuance_date', 'issuances.beneficiary', 'issuances.authority', 'issuances.issuance_type', 'issuances.created_at')
            ->orderBy('issuances.created_at', 'desc')
            ->get();

        return response()->json($issuedItems);
    }
}
