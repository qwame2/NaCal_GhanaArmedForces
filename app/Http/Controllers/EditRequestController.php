<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EditRequest;
use App\Models\Message;
use App\Models\User;
use App\Models\InventoryBatch;

class EditRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'reason' => 'required|string',
            'request_type' => 'nullable|string|in:edit,delete'
        ]);

        $item = InventoryBatch::with('items')->find($request->item_id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Batch not found']);
        }

        $itemNames = $item->items->pluck('description')->take(3)->implode(', ');
        if ($item->items->count() > 3) $itemNames .= ' etc.';
        $displayTitle = "Batch #{$item->id} ({$itemNames})";

        $requestType = $request->get('request_type', 'edit');

        $editReq = EditRequest::create([
            'user_id' => auth()->id(),
            'item_type' => 'batch',
            'item_id' => $request->item_id,
            'request_type' => $requestType,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        $admin = User::where('is_admin', true)->first();
        if ($admin) {
            $typeLabel = strtoupper($requestType);
            $actionWord = $requestType === 'edit' ? 'edit' : 'PERMANENTLY DELETE';
            
            $msgContent = "
                <div class='edit-req-msg admin-view' style='padding: 20px; background: rgba(79, 70, 229, 0.03); border-radius: 16px; border: 1px solid rgba(79, 70, 229, 0.1); font-family: inherit;'>
                    <div style='display: flex; align-items: center; gap: 8px; margin-bottom: 15px;'>
                        <div style='width: 8px; height: 8px; background: #4f46e5; border-radius: 50%;'></div>
                        <b style='font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: #4f46e5;'>{$typeLabel} AUTHORIZATION REQUIRED</b>
                    </div>
                    
                    <div style='font-size: 0.9rem; color: #1e293b; line-height: 1.6; margin-bottom: 15px;'>
                        Personnel <b style='color: #4f46e5;'>{$editReq->user->name}</b> is requesting permission to 
                        <b style='color: " . ($requestType === 'delete' ? '#ef4444' : '#4f46e5') . ";'>{$actionWord}</b> 
                        <span style='background: rgba(0,0,0,0.05); padding: 2px 6px; border-radius: 4px; font-family: monospace; font-weight: 700;'>{$displayTitle}</span>
                    </div>

                    <div style='background: white; padding: 12px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.05); margin-bottom: 20px;'>
                        <div style='font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 4px;'>Justification</div>
                        <div style='font-size: 0.85rem; color: #334155; line-height: 1.5;'>" . e($request->reason) . "</div>
                    </div>

                    <div style='display: flex; gap: 10px;' id='edit-req-actions-{$editReq->id}'>
                        <button onclick='window.processEditRequest({$editReq->id}, \"approved\", this)' style='flex: 1; background: #10b981; color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; font-size: 0.8rem; font-weight: 800; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); transition: 0.3s;'>Approve</button>
                        <button onclick='window.processEditRequest({$editReq->id}, \"canceled\", this)' style='background: #f1f5f9; color: #64748b; border: none; padding: 10px 15px; border-radius: 8px; cursor: pointer; font-size: 0.8rem; font-weight: 800; transition: 0.3s;'>Decline</button>
                    </div>
                </div>";
            
            $personnelLabel = $requestType === 'edit' ? 'EDIT' : 'DELETE';
            $msgContent .= "<div class='edit-req-msg personnel-view' style='display:none; padding: 15px; border-radius: 12px; background: rgba(79, 70, 229, 0.05); border: 1px dashed #4f46e5;'>
                <b style='color: #4f46e5;'>{$personnelLabel} REQUEST LOGGED</b><br>
                Waiting for strategic authorization from Command to {$actionWord} {$displayTitle}.
            </div>";

            Message::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $admin->id,
                'message' => $msgContent
            ]);
        }

        if (ob_get_length()) ob_clean();
        return response()->json(['success' => true]);
    }

    public function process(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,canceled'
        ]);

        $editReq = EditRequest::findOrFail($id);
        if (!auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $editReq->status = $request->status;
        if ($request->status === 'approved') {
            $editReq->approved_at = now();
        }
        $editReq->save();

        $requestType = $editReq->request_type ?? 'edit';
        $typeLabel = strtoupper($requestType);
        $actionWord = $requestType === 'edit' ? 'edit' : 'PERMANENTLY DELETE';
        $statusText = $request->status === 'approved' ? 'APPROVED' : 'CANCELED';
        $color = $request->status === 'approved' ? '#10b981' : '#dc2626';

        // Update the original message that triggered this for Admin's persistence
        $originalMsg = Message::where('message', 'like', "%edit-req-actions-{$editReq->id}%")->first();
        if ($originalMsg) {
            $item = InventoryBatch::with('items')->find($editReq->item_id);
            $itemNames = $item ? $item->items->pluck('description')->take(3)->implode(', ') : 'Unknown';
            if ($item && $item->items->count() > 3) $itemNames .= ' etc.';
            
            $batchInfo = $item ? "Batch #{$item->id} ({$itemNames})" : "Batch #{$editReq->item_id}";
            $statusColor = $request->status === 'approved' ? '#10b981' : '#ef4444';
            $statusBg = $request->status === 'approved' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)';
            
            $newContent = "
                <div class='edit-req-msg admin-view' style='padding: 20px; background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0;'>
                    <div style='display: flex; align-items: center; gap: 8px; margin-bottom: 12px;'>
                        <div style='width: 8px; height: 8px; background: #64748b; border-radius: 50%;'></div>
                        <b style='font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: #64748b;'>{$typeLabel} REQUEST LOG</b>
                    </div>
                    
                    <div style='font-size: 0.9rem; color: #475569; line-height: 1.6; margin-bottom: 12px;'>
                        Personnel <b>{$editReq->user->name}</b>'s request to 
                        <b style='color: " . ($requestType === 'delete' ? '#ef4444' : '#4f46e5') . ";'>{$actionWord}</b> 
                        <span style='font-family: monospace; font-weight: 700;'>{$batchInfo}</span>.
                    </div>

                    <div style='background: white; padding: 10px; border-radius: 8px; border: 1px solid #f1f5f9; margin-bottom: 15px; font-size: 0.85rem; color: #64748b;'>
                        <b>Reason:</b> " . e($editReq->reason) . "
                    </div>

                    <div style='padding: 10px 15px; border-radius: 8px; background: {$statusBg}; color: {$statusColor}; font-weight: 800; border: 1px solid {$statusColor}; display: inline-flex; align-items: center; gap: 8px; font-size: 0.8rem;'>
                        <div style='width: 6px; height: 6px; background: {$statusColor}; border-radius: 50%;'></div>
                        REQUEST " . strtoupper($statusText) . "
                    </div>
                </div>";
            
            $originalMsg->update(['message' => $newContent]);
        }

        // Send a confirmation message to the Personnel
        $item = InventoryBatch::with('items')->find($editReq->item_id);
        $itemNames = $item ? $item->items->pluck('description')->take(3)->implode(', ') : 'Unknown';
        if ($item && $item->items->count() > 3) $itemNames .= ' etc.';
        $batchInfo = $item ? "Batch #{$item->id} ({$itemNames})" : "Batch #{$editReq->item_id}";
        $confirmationMsg = "<div class='personnel-view' style='display: none;'><b style='color: {$color}'>{$statusText}</b><br>Your request to " . ($requestType === 'edit' ? 'edit' : 'DELETE') . " {$batchInfo} has been {$request->status}.</div>";
        
        if ($request->status === 'approved' && $requestType === 'edit') {
            $editUrl = route('receiveditems', ['edit_batch' => $editReq->item_id]);
            $expiry = now()->addSeconds(62)->getTimestampMs();
            $confirmationMsg = "
                <div class='clearance-container personnel-view' data-expires-at='{$expiry}' data-req-id='{$editReq->id}' data-type='edit' style='display: none; padding: 20px; background: rgba(16, 185, 129, 0.05); border: 1px solid #10b981; border-radius: 16px;'>
                    <b style='color: #10b981; font-size: 1.1rem;'>APPROVED</b><br>
                    Your request to <b style='color: #4f46e5;'>EDIT</b> {$batchInfo} has been approved.<br><br>
                    <div class='clearance-timer-notice' style='background: rgba(245, 158, 11, 0.1); padding: 10px; border-radius: 8px; font-size: 0.85rem; font-weight: 800; color: #d97706; margin-bottom: 15px;'>
                        ⚠️ SECURITY NOTICE: This clearance expires in <span class='timer-seconds'>62</span>s.
                    </div>
                    <a href='{$editUrl}' class='clearance-action-btn' style='display: inline-block; background: #4f46e5; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);'>Open Editor Now</a>
                </div>";
        } elseif ($request->status === 'approved' && $requestType === 'delete') {
            $deleteUrl = route('receiveditems', ['delete_batch' => $editReq->item_id]);
            $expiry = now()->addSeconds(62)->getTimestampMs();
            $confirmationMsg = "
                <div class='clearance-container personnel-view' data-expires-at='{$expiry}' data-req-id='{$editReq->id}' data-type='delete' style='display: none; padding: 20px; background: rgba(16, 185, 129, 0.05); border: 1px solid #10b981; border-radius: 16px;'>
                    <b style='color: #10b981; font-size: 1.1rem;'>APPROVED</b><br>
                    Your request to <b style='color: #ef4444;'>DELETE</b> {$batchInfo} has been approved.<br><br>
                    You may now proceed to delete this batch from the Received Items console.<br><br>
                    <div class='clearance-timer-notice' style='background: rgba(245, 158, 11, 0.1); padding: 10px; border-radius: 8px; font-size: 0.85rem; font-weight: 800; color: #d97706; margin-bottom: 15px;'>
                        ⚠️ SECURITY NOTICE: This clearance expires in <span class='timer-seconds'>62</span>s.
                    </div>
                    <a href='{$deleteUrl}' class='clearance-action-btn' style='display: inline-block; background: #ef4444; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);'>Delete Batch Permanently</a>
                </div>";
        }

        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $editReq->user_id,
            'message' => $confirmationMsg,
            'is_automated' => true
        ]);

        if (ob_get_length()) ob_clean();
        return response()->json(['success' => true]);
    }

    public function complete($itemId)
    {
        EditRequest::where('user_id', auth()->id())
            ->where('item_id', $itemId)
            ->where('item_type', 'batch')
            ->where('status', 'approved')
            ->update(['status' => 'completed', 'approved_at' => null]);

        return response()->json(['success' => true]);
    }

    public function checkStatus(Request $request, $itemId)
    {
        $type = $request->get('type', 'edit');

        if (auth()->user()->is_admin) {
            return response()->json(['allowed' => true]);
        }

        $editReq = EditRequest::where('user_id', auth()->id())
            ->where('item_id', $itemId)
            ->where('item_type', 'batch')
            ->where('request_type', $type)
            ->orderBy('id', 'desc')
            ->first();

        if (!$editReq) {
            return response()->json(['allowed' => false, 'status' => 'none']);
        }

        if ($editReq->status === 'approved') {
            $approvedAt = $editReq->approved_at ?? $editReq->updated_at;
            $secondsSinceApproval = now()->diffInSeconds($approvedAt);
            
            if ($secondsSinceApproval <= 62) {
                return response()->json([
                    'allowed' => true, 
                    'status' => 'approved',
                    'expires_in' => 62 - $secondsSinceApproval
                ]);
            } else {
                return response()->json([
                    'allowed' => false, 
                    'status' => 'expired',
                    'message' => 'Your 62-second security clearance has expired. Please request a new authorization.'
                ]);
            }
        }

        if (ob_get_length()) ob_clean();
        return response()->json(['allowed' => false, 'status' => $editReq->status]);
    }


    public function processSraCreation(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'reason' => 'nullable|string'
        ]);

        if (!auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $editReq = EditRequest::findOrFail($id);
        $editReq->status = $request->status;
        $editReq->save();

        $statusText = $request->status === 'approved' ? 'APPROVED & SAVED' : 'REJECTED';
        $color = $request->status === 'approved' ? '#10b981' : '#dc2626';

        if ($request->status === 'approved') {
            $data = json_decode($editReq->payload, true);
            
            \Illuminate\Support\Facades\DB::beginTransaction();
            try {
                // CREATE THE ACTUAL RECORDS
                $batch = InventoryBatch::create([
                    'ledge_category' => $data['ledge_category'],
                    'supplier_name' => $data['supplier_name'],
                    'supplier_status' => $data['supplier_status'],
                    'donor_name' => $data['donor_name'] ?? null,
                    'acquisition_type' => $data['acquisition_type'],
                    'entry_date' => $data['entry_date'],
                    'arrival_date' => $data['arrival_date'],
                    'recorded_by' => $editReq->user_id,
                    'approval_status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                foreach ($data['items'] as $item) {
                    $itemData = $item;
                    unset($itemData['ledge_balance']);
                    $batch->items()->create($itemData);
                }

                // Log it
                \App\Models\SystemLog::create([
                    'user_id' => $editReq->user_id,
                    'event_type' => 'INVENTORY',
                    'action' => 'ADD_INVENTORY',
                    'description' => "Personnel added items (Approved by Admin).",
                    'severity' => 'info',
                    'metadata' => ['batch_id' => $batch->id],
                    'ip_address' => request()->ip()
                ]);

                \Illuminate\Support\Facades\DB::commit();

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Failed to save: ' . $e->getMessage()]);
            }
        }

        // Generate Personnel Notification Content
        $color = $request->status === 'approved' ? '#10b981' : '#dc2626';
        $statusHeader = $request->status === 'approved' ? 'SRA AUTHORIZED & COMMITTED' : 'REQUEST HAS BEEN REJECTED';
        
        $finalMsg = "<div style='padding: 15px; border: 1px solid {$color}; border-radius: 12px; background: " . ($request->status === 'approved' ? 'rgba(16, 185, 129, 0.05)' : 'rgba(220, 38, 38, 0.05)') . ";'>";
        $finalMsg .= "<b style='color: {$color}'>{$statusHeader}</b><br>";
        
        if ($request->status === 'approved') {
            $printUrl = route('receiveditems.sra', ['id' => $batch->id]);
            $finalMsg .= "Your inventory entry has been authorized. You can now download the official voucher.<br><br>";
            $finalMsg .= "<a href='{$printUrl}' target='_blank' style='display: inline-block; background: #4f46e5; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);'>Download / Print SRA</a>";
        } else {
            // Rejection format as requested
            if ($request->reason) {
                $finalMsg .= "<div style='margin-top: 5px; line-height: 1.6; color: #b91c1c;'>";
                $finalMsg .= nl2br(e($request->reason));
                $finalMsg .= "</div>";
            } else {
                $finalMsg .= "Your submission for new inventory items was not authorized by the Administrator.";
            }
        }
        $finalMsg .= "</div>";

        // 1. Update the original "Awaiting" message for the Personnel (Highly Resilient Search)
        $personnelOriginalMsg = Message::where('receiver_id', $editReq->user_id)
            ->where(function($q) use ($editReq) {
                $q->where('edit_request_id', $editReq->id)
                  ->orWhere('message', 'like', "%<!-- sra_req_id:{$editReq->id} -->%")
                  ->orWhere('message', 'like', '%Awaiting SRA Approval%');
            })
            ->orderBy('created_at', 'desc')
            ->first();

        if ($personnelOriginalMsg) {
            $personnelOriginalMsg->update([
                'message' => $finalMsg,
                'is_automated' => true,
                'edit_request_id' => $editReq->id // Ensure it's tagged for future
            ]);
        } else {
            // Fallback if not found
            Message::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $editReq->user_id,
                'message' => $finalMsg,
                'is_automated' => true,
                'edit_request_id' => $editReq->id
            ]);
        }

        // 2. Update the Admin's message (this view)
        $adminMsg = Message::where('receiver_id', auth()->id())
            ->where(function($q) use ($editReq) {
                $q->where('edit_request_id', $editReq->id)
                  ->orWhere('message', 'like', "%<!-- sra_req_id:{$editReq->id} -->%")
                  ->orWhere('message', 'like', "%sra-creation-actions-{$editReq->id}%");
            })
            ->first();

        if ($adminMsg) {
            $statusColor = $request->status === 'approved' ? '#10b981' : '#dc2626';
            $statusLabel = $request->status === 'approved' ? 'AUTHORIZED & COMMITTED' : 'REJECTED';
            
            $printLink = "";
            if ($request->status === 'approved' && isset($batch)) {
                $printUrl = route('receiveditems.sra', ['id' => $batch->id]);
                $printLink = "<br><a href='{$printUrl}' target='_blank' style='display: inline-block; background: #4f46e5; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: 800; font-size: 0.75rem; margin-top: 8px;'>Download / Print SRA</a>";
            }
            
            $newMsg = "<div class='sra-approval-msg' style='padding: 15px; border-left: 4px solid {$statusColor}; background: rgba(0,0,0,0.02);'>";
            $newMsg .= "<b style='color: {$statusColor};'>SRA {$statusLabel}</b><br>";
            $newMsg .= "Submission by " . ($editReq->user->name ?? 'Personnel') . " has been {$request->status}.";
            if ($request->status === 'rejected' && $request->reason) {
                $newMsg .= "<div style='margin-top: 5px; font-size: 0.85rem; color: #666;'>Reason: " . e($request->reason) . "</div>";
            }
            $newMsg .= $printLink . "</div>";
            
            $adminMsg->update(['message' => $newMsg, 'is_automated' => true]);
        }

        return response()->json([
            'success' => true,
            'batch_id' => isset($batch) ? $batch->id : null
        ]);
    }
}
