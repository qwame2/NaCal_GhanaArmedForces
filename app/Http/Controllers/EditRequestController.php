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
            'request_type' => 'nullable|string|in:edit,delete,edit_submission',
            'payload' => 'nullable|string'
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
            'payload' => $request->payload,
            'status' => 'pending'
        ]);

        $admins = User::where('is_admin', true)->get();
        if ($admins->count() > 0) {
            $typeLabel = $requestType === 'edit_submission' ? 'ENTRY EDIT' : strtoupper($requestType);
            $actionWord = ($requestType === 'edit' || $requestType === 'edit_submission') ? 'edit' : 'PERMANENTLY DELETE';
            $mainText = $requestType === 'edit_submission' 
                ? "Personnel <b style='color: #4f46e5;'>{$editReq->user->name}</b> has submitted <b>Proposed Changes</b> for"
                : "Personnel <b style='color: #4f46e5;'>{$editReq->user->name}</b> is requesting permission to <b style='color: " . ($requestType === 'delete' ? '#ef4444' : '#4f46e5') . ";'>{$actionWord}</b>";

            $msgContent = "
                <div class='edit-req-msg admin-view' style='padding: 24px; background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; font-family: inherit; box-shadow: 0 10px 30px -10px rgba(79, 70, 229, 0.15); position: relative; overflow: hidden;'>
                    <div style='position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #4f46e5, #818cf8);'></div>
                    <div style='display: flex; align-items: center; gap: 10px; margin-bottom: 16px;'>
                        <div style='width: 32px; height: 32px; background: rgba(79, 70, 229, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #4f46e5;'>
                            <svg style='width: 16px; height: 16px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'></path></svg>
                        </div>
                        <b style='font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; color: #4f46e5;'>APPROVAL NEEDED FOR {$typeLabel}</b>
                    </div>
                    
                    <div style='font-size: 0.95rem; color: #334155; line-height: 1.6; margin-bottom: 20px;'>
                        {$mainText} 
                        <span style='background: #f8fafc; padding: 4px 8px; border-radius: 6px; border: 1px solid #e2e8f0; font-family: monospace; font-weight: 700; color: #0f172a;'>{$displayTitle}</span>
                    </div>";

            if ($requestType === 'edit_submission') {
                $msgContent .= "
                    <button data-entry-req-id='{$editReq->id}' class='entry-preview-btn' style='width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; background: #4f46e5; color: white; border: none; padding: 14px; border-radius: 12px; font-weight: 800; font-size: 0.9rem; cursor: pointer; transition: 0.3s; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);' onmouseover='this.style.transform=\"translateY(-2px)\"; this.style.boxShadow=\"0 6px 16px rgba(79, 70, 229, 0.35)\"' onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 4px 12px rgba(79, 70, 229, 0.25)\"'>
                        <svg style='width: 18px; height: 18px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'></path><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'></path></svg> Preview Proposed Changes
                    </button>";
            }

            $msgContent .= "
                    <div style='background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px dashed #cbd5e1; margin-bottom: 20px;'>
                        <div style='font-size: 0.7rem; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;'>
                            <svg style='width: 14px; height: 14px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg> Justification
                        </div>
                        <div style='font-size: 0.9rem; color: #334155; line-height: 1.5; font-style: italic;'>" . e($request->reason) . "</div>
                    </div>

                    <div style='display: flex; gap: 12px;' id='edit-req-actions-{$editReq->id}'>
                        <button onclick='window.processEditRequest({$editReq->id}, \"approved\", this)' style='flex: 1; background: #10b981; color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-size: 0.85rem; font-weight: 800; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); transition: 0.3s;' onmouseover='this.style.background=\"#059669\"' onmouseout='this.style.background=\"#10b981\"'>Approve</button>
                        <button onclick='window.processEditRequest({$editReq->id}, \"canceled\", this)' style='flex: 1; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: 12px; border-radius: 10px; cursor: pointer; font-size: 0.85rem; font-weight: 800; transition: 0.3s;' onmouseover='this.style.background=\"#e2e8f0\"; this.style.color=\"#0f172a\"' onmouseout='this.style.background=\"#f1f5f9\"; this.style.color=\"#64748b\"'>Decline</button>
                    </div>
                </div>";
            
            $personnelLabel = $requestType === 'edit' ? 'EDIT' : 'DELETE';
            $msgContent .= "<div class='edit-req-msg personnel-view' style='display:none; padding: 15px; border-radius: 12px; background: rgba(79, 70, 229, 0.05); border: 1px dashed #4f46e5;'>
                <b style='color: #4f46e5;'>{$personnelLabel} REQUEST LOGGED</b><br>
                Waiting for strategic authorization from Command to {$actionWord} {$displayTitle}.
            </div>";

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

        if (ob_get_length()) ob_clean();
        return response()->json(['success' => true]);
    }

    public function process(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,canceled',
            'decline_reason' => 'nullable|string|max:1000'
        ]);

        $editReq = EditRequest::findOrFail($id);
        if (!auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $editReq->status = $request->status;
        if ($request->status === 'approved') {
            $editReq->approved_at = now();
            
            // IF this was an 'edit_submission' or 'issue_submission', apply it to the DB now!
            if (in_array($editReq->request_type, ['edit_submission', 'issue_submission']) && $editReq->payload) {
                $this->applyPayload($editReq);
            }
        }
        $editReq->save();

        if ($request->status === 'approved') {
            $requestTypeForLog = $editReq->request_type ?? 'edit';
            $typeLabelForLog = $requestTypeForLog === 'edit_submission' ? 'ENTRY EDIT' : ($requestTypeForLog === 'issue_submission' ? 'DISBURSEMENT' : strtoupper($requestTypeForLog));
            \App\Models\SystemLog::create([
                'user_id' => auth()->id(),
                'event_type' => 'SECURITY',
                'action' => 'AUTHORIZATION',
                'description' => "Administrator authorized {$typeLabelForLog} request submitted by {$editReq->user->name}.",
                'severity' => 'info',
                'ip_address' => request()->ip()
            ]);
        }

        $requestType = $editReq->request_type ?? 'edit';
        $typeLabel = $requestType === 'edit_submission' ? 'ENTRY EDIT' : ($requestType === 'issue_submission' ? 'DISBURSEMENT' : strtoupper($requestType));
        $actionWord = ($requestType === 'edit' || $requestType === 'edit_submission') ? 'edit' : ($requestType === 'issue_submission' ? 'ISSUE ITEMS' : 'PERMANENTLY DELETE');
        $statusText = $request->status === 'approved' ? 'APPROVED' : 'CANCELED';
        $color = $request->status === 'approved' ? '#10b981' : '#dc2626';

        // Update the original message that triggered this for Admin's persistence
        // Update all original messages that triggered this for Admin's persistence (sync across multiple admins)
        $originalMsgs = Message::where('message', 'like', "%edit-req-actions-{$editReq->id}%")->get();
        
        if ($originalMsgs->count() > 0) {
            if ($requestType === 'issue_submission') {
                $payloadData = json_decode($editReq->payload, true);
                $batchInfo = "Disbursement to {$payloadData['beneficiary']}";
            } else {
                $item = InventoryBatch::with('items')->find($editReq->item_id);
                $itemNames = $item ? $item->items->pluck('description')->take(3)->implode(', ') : 'Unknown';
                if ($item && $item->items->count() > 3) $itemNames .= ' etc.';
                $batchInfo = $item ? "Batch #{$item->id} ({$itemNames})" : "Batch #{$editReq->item_id}";
            }
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
            
            foreach ($originalMsgs as $originalMsg) {
                $originalMsg->update(['message' => $newContent]);
            }
        }

        // Send a confirmation message to the Personnel
        if ($requestType === 'issue_submission') {
            $payloadData = json_decode($editReq->payload, true);
            $batchInfo = "Disbursement to {$payloadData['beneficiary']}";
            $actionVerb = 'DISBURSE ITEMS';
        } else {
            $item = InventoryBatch::with('items')->find($editReq->item_id);
            $itemNames = $item ? $item->items->pluck('description')->take(3)->implode(', ') : 'Unknown';
            if ($item && $item->items->count() > 3) $itemNames .= ' etc.';
            $batchInfo = $item ? "Batch #{$item->id} ({$itemNames})" : "Batch #{$editReq->item_id}";
            $actionVerb = ($requestType === 'edit' || $requestType === 'edit_submission') ? 'edit' : 'DELETE';
        }
        $declineReason = $request->input('decline_reason');
        $declineBlock = '';
        if ($request->status === 'canceled' && $declineReason) {
            $declineBlock = "<div style='margin-top: 12px; padding: 10px 14px; background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; font-size: 0.85rem; color: #7f1d1d;'><b>Reason:</b> " . e($declineReason) . "</div>";
        }
        $confirmationMsg = "<div class='personnel-view' style='display: none;'><b style='color: {$color}'>{$statusText}</b><br>Your request to {$actionVerb} {$batchInfo} has been {$request->status}.{$declineBlock}</div>";
        
        $timeoutMinutes = \Illuminate\Support\Facades\Schema::hasTable('settings') ? (int)\App\Models\Setting::get('approval_timeout_minutes', 5) : 5;
        $timeoutSeconds = $timeoutMinutes * 60;

        if ($request->status === 'approved' && ($requestType === 'edit' || $requestType === 'edit_submission')) {
            $editUrl = route('receiveditems', ['edit_batch' => $editReq->item_id]);
            $expiry = now()->addSeconds($timeoutSeconds)->getTimestampMs();
            
            if ($requestType === 'edit_submission') {
                $confirmationMsg = "
                    <div class='personnel-view' style='display: none; padding: 20px; background: rgba(16, 185, 129, 0.05); border: 1px solid #10b981; border-radius: 16px;'>
                        <b style='color: #10b981; font-size: 1.1rem;'>EDIT APPROVED & SAVED</b><br>
                        Your proposed changes to <b style='color: #4f46e5;'>{$batchInfo}</b> have been reviewed and committed to the live inventory by the overseer.
                    </div>";
            } else {
                $confirmationMsg = "
                    <div class='clearance-container personnel-view' data-expires-at='{$expiry}' data-req-id='{$editReq->id}' data-type='edit' style='display: none; padding: 20px; background: rgba(16, 185, 129, 0.05); border: 1px solid #10b981; border-radius: 16px;'>
                        <b style='color: #10b981; font-size: 1.1rem;'>APPROVED</b><br>
                        Your request to <b style='color: #4f46e5;'>EDIT</b> {$batchInfo} has been approved.<br><br>
                        <div class='clearance-timer-notice' style='background: rgba(245, 158, 11, 0.1); padding: 10px; border-radius: 8px; font-size: 0.85rem; font-weight: 800; color: #d97706; margin-bottom: 15px;'>
                            ⚠️ SECURITY NOTICE: This clearance expires in <span class='timer-seconds'>{$timeoutSeconds}</span>s.
                        </div>
                        <a href='{$editUrl}' class='clearance-action-btn' style='display: inline-block; background: #4f46e5; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);'>Open Editor Now</a>
                    </div>";
            }
        } elseif ($request->status === 'approved' && $requestType === 'delete') {
            $deleteUrl = route('receiveditems', ['delete_batch' => $editReq->item_id]);
            $expiry = now()->addSeconds($timeoutSeconds)->getTimestampMs();
            $confirmationMsg = "
                <div class='clearance-container personnel-view' data-expires-at='{$expiry}' data-req-id='{$editReq->id}' data-type='delete' style='display: none; padding: 20px; background: rgba(16, 185, 129, 0.05); border: 1px solid #10b981; border-radius: 16px;'>
                    <b style='color: #10b981; font-size: 1.1rem;'>APPROVED</b><br>
                    Your request to <b style='color: #ef4444;'>DELETE</b> {$batchInfo} has been approved.<br><br>
                    You may now proceed to delete this batch from the Received Items console.<br><br>
                    <div class='clearance-timer-notice' style='background: rgba(245, 158, 11, 0.1); padding: 10px; border-radius: 8px; font-size: 0.85rem; font-weight: 800; color: #d97706; margin-bottom: 15px;'>
                        ⚠️ SECURITY NOTICE: This clearance expires in <span class='timer-seconds'>{$timeoutSeconds}</span>s.
                    </div>
                    <a href='{$deleteUrl}' class='clearance-action-btn' style='display: inline-block; background: #ef4444; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);'>Delete Batch Permanently</a>
                </div>";
        } elseif ($request->status === 'approved' && $requestType === 'issue_submission') {
            $confirmationMsg = "
                <div class='personnel-view' style='display: none; padding: 20px; background: rgba(16, 185, 129, 0.05); border: 1px solid #10b981; border-radius: 16px;'>
                    <b style='color: #10b981; font-size: 1.1rem;'>DISBURSEMENT APPROVED & EXECUTED</b><br>
                    Your request for <b style='color: #4f46e5;'>{$batchInfo}</b> has been authorized. The items have been disbursed and the inventory has been automatically updated.
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
            
            $timeoutMinutes = \Illuminate\Support\Facades\Schema::hasTable('settings') ? (int)\App\Models\Setting::get('approval_timeout_minutes', 5) : 5;
            $timeoutSeconds = $timeoutMinutes * 60;

            if ($secondsSinceApproval <= $timeoutSeconds) {
                return response()->json([
                    'allowed' => true, 
                    'status' => 'approved',
                    'expires_in' => $timeoutSeconds - $secondsSinceApproval
                ]);
            } else {
                return response()->json([
                    'allowed' => false, 
                    'status' => 'expired',
                    'message' => "Your {$timeoutSeconds}-second security clearance has expired. Please request a new authorization."
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
            $requestType = $editReq->request_type;
            
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            if (ob_get_length()) ob_clean();

                if ($requestType === 'remainder_submission') {
                    $updates = $data['updates'] ?? [];
                    $batchIdsToCheck = [];
                    foreach ($updates as $update) {
                        $item = \App\Models\InventoryItem::find($update['item_id']);
                        if ($item) {
                            $incoming = floatval($update['incoming_qty']);
                            $expected = floatval($item->stock_balance) - floatval($item->variance);
                            $item->stock_balance += $incoming;
                            $item->variance = $item->stock_balance - $expected;
                            $item->remarks = $item->remarks ? $item->remarks . " | Supplemented with $incoming units (Approved)." : "Supplemented with $incoming units (Approved).";
                            $item->save();
                            if (!in_array($item->batch_id, $batchIdsToCheck)) $batchIdsToCheck[] = $item->batch_id;
                        }
                    }

                    foreach ($batchIdsToCheck as $batchId) {
                        $checkBatch = \App\Models\InventoryBatch::find($batchId);
                        if ($checkBatch && stripos($checkBatch->supplier_status ?? '', 'partial') !== false) {
                            $allItems = \App\Models\InventoryItem::where('batch_id', $batchId)->get();
                            $allDelivered = true;
                            foreach ($allItems as $i) {
                                // Negative variance = items still outstanding
                                if (floatval($i->variance) < 0) { $allDelivered = false; break; }
                            }
                            if ($allDelivered) {
                                $checkBatch->supplier_name = preg_replace('/\[Partial Deliv(.*?)\]/i', '', $checkBatch->supplier_name);
                                $checkBatch->supplier_status = 'Full Delivery';
                                $checkBatch->save();
                            }
                        }
                    }
                    // Set batch for later use in notifications
                    $batch = \App\Models\InventoryBatch::find($editReq->item_id);

                    \App\Models\SystemLog::create([
                        'user_id' => $editReq->user_id,
                        'event_type' => 'INVENTORY',
                        'action' => 'SUPPLEMENT_INVENTORY',
                        'description' => "Personnel added remainder items (Approved by Admin).",
                        'severity' => 'info',
                        'metadata' => ['batch_id' => $editReq->item_id],
                        'ip_address' => request()->ip()
                    ]);

                } else {
                    // CREATE THE ACTUAL RECORDS (SRA Creation)
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

                    \App\Models\SystemLog::create([
                        'user_id' => $editReq->user_id,
                        'event_type' => 'INVENTORY',
                        'action' => 'ADD_INVENTORY',
                        'description' => "Personnel added items (Approved by Admin).",
                        'severity' => 'info',
                        'metadata' => ['batch_id' => $batch->id],
                        'ip_address' => request()->ip()
                    ]);
                }

                \Illuminate\Support\Facades\DB::commit();

                $logDesc = $requestType === 'remainder_submission' 
                    ? "Administrator authorized REMAINDER SUBMISSION submitted by {$editReq->user->name}."
                    : "Administrator authorized STOCK ENTRY submitted by {$editReq->user->name}.";
                    
                \App\Models\SystemLog::create([
                    'user_id' => auth()->id(),
                    'event_type' => 'SECURITY',
                    'action' => 'AUTHORIZATION',
                    'description' => $logDesc,
                    'severity' => 'info',
                    'ip_address' => request()->ip()
                ]);

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Failed to process: ' . $e->getMessage()]);
            }
        }

        // Generate Personnel Notification Content
        $requestType = $editReq->request_type;
        $color = $request->status === 'approved' ? '#10b981' : '#dc2626';
        
        $statusHeader = $request->status === 'approved' ? 'AUTHORIZED & COMMITTED' : 'REQUEST HAS BEEN REJECTED';
        if ($requestType === 'remainder_submission' && $request->status === 'approved') {
            $statusHeader = 'REMAINDER FULFILLMENT AUTHORIZED';
        }
        
        $finalMsg = "<div class='personnel-view' style='padding: 15px; border: 1px solid {$color}; border-radius: 12px; background: " . ($request->status === 'approved' ? 'rgba(16, 185, 129, 0.05)' : 'rgba(220, 38, 38, 0.05)') . ";'>";
        $finalMsg .= "<b style='color: {$color}'>{$statusHeader}</b><br>";
        
        if ($request->status === 'approved' && isset($batch) && $batch) {
            $printUrl = route('receiveditems.sra', ['id' => $batch->id]);
            $desc = $requestType === 'remainder_submission' 
                ? "The remainder items for Batch #{$batch->id} have been authorized and added to stock. You can now download the updated SRA voucher."
                : "Your inventory entry has been authorized. You can now download the official voucher.";
            $finalMsg .= "{$desc}<br><br>";
            $finalMsg .= "<a href='{$printUrl}' target='_blank' style='display: inline-block; background: #4f46e5; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);'>Download / Print SRA</a>";
        } else if ($request->status === 'approved') {
            $finalMsg .= "Authorization was granted, but the associated record could not be identified for printing. Please contact the Administrator.";
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
                  ->orWhere('message', 'like', '%Awaiting Authorization%')
                  ->orWhere('message', 'like', '%Awaiting Admin verification for remainder%');
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
        // 2. Update ALL Admin messages for this request (Collaborative status sync)
        $adminMsgs = Message::whereIn('receiver_id', User::where('is_admin', true)->pluck('id'))
            ->where(function($q) use ($editReq) {
                $q->where('edit_request_id', $editReq->id)
                  ->orWhere('message', 'like', "%sra-creation-actions-{$editReq->id}%");
            })
            ->get();

        foreach ($adminMsgs as $adminMsg) {
            $statusColor = $request->status === 'approved' ? '#10b981' : '#dc2626';
            $statusLabel = $request->status === 'approved' ? 'AUTHORIZED & COMMITTED' : 'REJECTED';
            $logType = $editReq->request_type === 'remainder_submission' ? 'REMAINDER' : 'AUTHORIZATION';
            
            $printLink = "";
            if ($request->status === 'approved' && isset($batch) && $batch) {
                $printUrl = route('receiveditems.sra', ['id' => $batch->id]);
                $printLink = "<br><a href='{$printUrl}' target='_blank' style='display: inline-block; background: #4f46e5; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: 800; font-size: 0.75rem; margin-top: 8px;'>Download / Print SRA</a>";
            }
            
            $newMsg = "<div class='sra-approval-msg' style='padding: 15px; border-left: 4px solid {$statusColor}; background: rgba(0,0,0,0.02);'>";
            $newMsg .= "<b style='color: {$statusColor};'>{$logType} {$statusLabel}</b><br>";
            $newMsg .= "Submission by " . ($editReq->user->name ?? 'Personnel') . " has been {$request->status}.";
            if ($request->status === 'rejected' && $request->reason) {
                $newMsg .= "<div style='margin-top: 5px; font-size: 0.85rem; color: #666;'>Reason: " . e($request->reason) . "</div>";
            }
            $newMsg .= $printLink . "</div>";
            
            $adminMsg->update(['message' => $newMsg, 'is_automated' => true]);
        }

        // For remainder submissions, the batch_id is the existing batch; for new SRA it's the newly created batch
        $returnBatchId = isset($batch) ? $batch->id : ($editReq->item_id ?: null);

        return response()->json([
            'success' => true,
            'batch_id' => $returnBatchId,
            'is_remainder' => ($editReq->request_type === 'remainder_submission')
        ]);
    }

    /**
     * Apply the payload from an edit_submission to the actual database records.
     */
    private function applyPayload(EditRequest $editReq)
    {
        $payload = json_decode($editReq->payload, true);
        if (!$payload) return;

        \DB::transaction(function() use ($editReq, $payload) {
            if ($editReq->request_type === 'issue_submission') {
                $issuance = \App\Models\Issuance::create([
                    'issuance_date' => $payload['issuance_date'],
                    'beneficiary' => $payload['beneficiary'],
                    'authority' => $payload['authority'],
                    'issuance_type' => $payload['issuance_type'],
                ]);

                foreach ($payload['items'] as $cartItem) {
                    $qtyToIssue = $cartItem['qty'];
                    $unit = \App\Models\InventoryItem::where('description', $cartItem['description'])->value('unit');

                    \App\Models\IssuedItem::create([
                        'issuance_id' => $issuance->id,
                        'description' => $cartItem['description'],
                        'ledge_category' => $cartItem['category'],
                        'quantity' => $qtyToIssue,
                        'unit' => $unit
                    ]);

                    $stockItems = \App\Models\InventoryItem::where('description', $cartItem['description'])
                        ->whereHas('batch', function ($q) use ($cartItem) {
                            $q->where('ledge_category', $cartItem['category']);
                        })
                        ->where('qty', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();

                    foreach ($stockItems as $inventoryItem) {
                        if ($qtyToIssue <= 0) break;

                        $available = floatval($inventoryItem->qty);
                        $stockBal = floatval($inventoryItem->stock_balance);
                        $take = min($available, $qtyToIssue);

                        $inventoryItem->qty = $available - $take;
                        if ($payload['issuance_type'] === 'Permanent') {
                            $inventoryItem->stock_balance = $stockBal - $take;
                        }
                        $inventoryItem->save();

                        $qtyToIssue -= $take;
                    }

                    if ($qtyToIssue > 0) {
                        throw new \Exception("Insufficient stock for " . $cartItem['description']);
                    }
                }

                \App\Models\SystemLog::create([
                    'user_id' => $editReq->user_id,
                    'event_type' => 'INVENTORY',
                    'action' => 'ISSUE_ITEM',
                    'description' => "Admin approved disbursement of items to {$payload['beneficiary']} on authority of {$payload['authority']}.",
                    'severity' => $payload['issuance_type'] === 'Permanent' ? 'warning' : 'info',
                    'metadata' => [
                        'beneficiary' => $payload['beneficiary'],
                        'authority' => $payload['authority'],
                        'issuance_type' => $payload['issuance_type'],
                        'items_issued' => $payload['items']
                    ],
                    'ip_address' => request()->ip()
                ]);

            } else {
                $batch = InventoryBatch::find($editReq->item_id);
                if (!$batch) return;

                // Update Batch
                $batch->update([
                    'arrival_date' => $payload['arrival_date'],
                    'ledge_category' => $payload['ledge_category'],
                    'acquisition_type' => $payload['acquisition_type'],
                    'supplier_name' => $payload['supplier_name'],
                    'supplier_status' => $payload['supplier_status'],
                    'donor_name' => $payload['donor_name'] ?? null,
                ]);

                // Update Items
                if (isset($payload['items'])) {
                    foreach ($payload['items'] as $itemData) {
                        $item = \App\Models\InventoryItem::find($itemData['id']);
                        if ($item && $item->batch_id == $batch->id) {
                            $item->update([
                                'description' => $itemData['description'],
                                'unit' => $itemData['unit'],
                                'qty' => $itemData['qty'],
                                'stock_balance' => $itemData['stock_balance'],
                                'variance' => $itemData['variance'],
                                'remarks' => $itemData['remarks'] ?? null,
                            ]);
                        }
                    }
                }
            }
        });
    }

    public function processRecoveryApproval(Request $request, $id)
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

        $statusHeader = $request->status === 'approved' ? 'RECOVERY AUTHORIZED' : 'RECOVERY REJECTED';
        $color = $request->status === 'approved' ? '#10b981' : '#dc2626';

        if ($request->status === 'approved') {
            $payload = json_decode($editReq->payload, true);
            
            \Illuminate\Support\Facades\DB::beginTransaction();
            try {
                $issuedItem = \App\Models\IssuedItem::findOrFail($editReq->item_id);
                $qtyToReturn = floatval($payload['return_qty']);
                
                // Find all matching inventory items (batches) for this specific asset
                $inventoryItems = \App\Models\InventoryItem::where('description', $issuedItem->description)
                    ->whereHas('batch', function($q) use ($issuedItem) {
                        $q->where('ledge_category', $issuedItem->ledge_category);
                    })
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->get();

                if ($inventoryItems->isEmpty()) {
                    throw new \Exception("Could not find a valid inventory destination for this item.");
                }

                $remainingToRefill = $qtyToReturn;

                // Phase 1: Refill depleted batches
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

                // Phase 2: Overflow to latest batch
                if ($remainingToRefill > 0) {
                    $latestItem = $inventoryItems->last();
                    $latestItem->qty = floatval($latestItem->qty) + $remainingToRefill;
                    $latestItem->save();
                }

                // Update issued item quantity
                $issuedItem->quantity -= $qtyToReturn;
                $issuedItem->save();

                // Create record in returned_items
                \App\Models\ReturnedItem::create([
                    'issued_item_id' => $issuedItem->id,
                    'returned_qty' => $qtyToReturn,
                    'return_date' => $payload['return_date'],
                    'remarks' => $payload['remarks'] ?? null,
                ]);

                \App\Models\SystemLog::create([
                    'user_id' => $editReq->user_id,
                    'event_type' => 'INVENTORY',
                    'action' => 'RETURN_ITEM',
                    'description' => "Personnel logged return of {$qtyToReturn} {$issuedItem->description}(s) (Approved by Admin).",
                    'severity' => 'info',
                    'metadata' => [
                        'item_description' => $issuedItem->description,
                        'return_qty' => $qtyToReturn,
                        'return_date' => $payload['return_date']
                    ],
                    'ip_address' => request()->ip()
                ]);

                \Illuminate\Support\Facades\DB::commit();

                \App\Models\SystemLog::create([
                    'user_id' => auth()->id(),
                    'event_type' => 'SECURITY',
                    'action' => 'AUTHORIZATION',
                    'description' => "Administrator authorized ASSET RECOVERY request submitted by {$editReq->user->name}.",
                    'severity' => 'info',
                    'ip_address' => request()->ip()
                ]);

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Failed to process: ' . $e->getMessage()]);
            }
        }

        // Notification logic
        $finalMsg = "<div class='personnel-view' style='padding: 15px; border: 1px solid {$color}; border-radius: 12px; background: " . ($request->status === 'approved' ? 'rgba(16, 185, 129, 0.05)' : 'rgba(220, 38, 38, 0.05)') . ";'>";
        $finalMsg .= "<b style='color: {$color}'>{$statusHeader}</b><br>";
        
        if ($request->status === 'approved') {
            $finalMsg .= "Your recovery request for the registry assets has been authorized and stock balances updated.";
        } else {
            $finalMsg .= "Your recovery request was not authorized.<br>";
            if ($request->reason) {
                $finalMsg .= "<div style='margin-top: 5px; color: #b91c1c;'>" . nl2br(e($request->reason)) . "</div>";
            }
        }
        $finalMsg .= "</div>";

        // Update Personnel Message
        $personnelMsg = Message::where('receiver_id', $editReq->user_id)
            ->where('edit_request_id', $editReq->id)
            ->where('message', 'like', '%RECOVERY SUBMITTED%')
            ->first();

        if ($personnelMsg) {
            $personnelMsg->update(['message' => $finalMsg, 'is_automated' => true]);
        } else {
            Message::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $editReq->user_id,
                'message' => $finalMsg,
                'is_automated' => true,
                'edit_request_id' => $editReq->id
            ]);
        }

        // Update Admin Messages
        $adminMsgs = Message::whereIn('receiver_id', User::where('is_admin', true)->pluck('id'))
            ->where(function($q) use ($editReq) {
                $q->where('edit_request_id', $editReq->id)
                  ->orWhere('message', 'like', "%recovery-actions-{$editReq->id}%");
            })
            ->get();

        foreach ($adminMsgs as $adminMsg) {
            $statusLabel = $request->status === 'approved' ? 'AUTHORIZED' : 'REJECTED';
            $newMsg = "<div style='padding: 15px; border-left: 4px solid {$color}; background: rgba(0,0,0,0.02);'>";
            $newMsg .= "<b style='color: {$color};'>RECOVERY {$statusLabel}</b><br>";
            $newMsg .= "Submission by " . ($editReq->user->name ?? 'Personnel') . " has been {$request->status}.";
            if ($request->status === 'rejected' && $request->reason) {
                $newMsg .= "<div style='margin-top: 5px; font-size: 0.85rem; color: #666;'>Reason: " . e($request->reason) . "</div>";
            }
            $newMsg .= "</div>";
            $adminMsg->update(['message' => $newMsg, 'is_automated' => true]);
        }

        return response()->json(['success' => true]);
    }

    public function processVerificationApproval(Request $request, $id)
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

        $statusHeader = $request->status === 'approved' ? 'VERIFICATION AUTHORIZED & RECONCILED' : 'VERIFICATION REJECTED';
        $color = $request->status === 'approved' ? '#10b981' : '#dc2626';

        if ($request->status === 'approved') {
            $payload = json_decode($editReq->payload, true);
            
            \Illuminate\Support\Facades\DB::beginTransaction();
            try {
                $itemsToProcess = [];
                if (isset($payload['items'])) {
                    $itemsToProcess = $payload['items'];
                } else {
                    $itemsToProcess[] = $payload;
                }

                foreach ($itemsToProcess as $itemData) {
                    $description = $itemData['description'];
                    $physicalCount = $itemData['physical_count'];
                    $condition = $itemData['condition'];
                    $remarks = $itemData['remarks'];
                    $variance = $itemData['variance'];

                    // Reconcile stock
                    $items = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                        ->where('inventory_items.description', $description)
                        ->select('inventory_items.*')
                        ->orderBy('inventory_batches.entry_date', 'desc')
                        ->get();

                    if ($items->isNotEmpty()) {
                        $remainingAdjustment = $variance;

                        foreach ($items as $item) {
                            if ($remainingAdjustment === 0) break;

                            if ($remainingAdjustment > 0) {
                                $item->stock_balance += $remainingAdjustment;
                                $item->variance = (float)$item->variance + $remainingAdjustment;
                                $item->save();
                                $remainingAdjustment = 0;
                            } else {
                                $subtraction = min(abs($remainingAdjustment), $item->stock_balance);
                                $item->stock_balance -= $subtraction;
                                $item->variance = (float)$item->variance - $subtraction;
                                $item->save();
                                $remainingAdjustment += $subtraction;
                            }
                        }
                    }

                    \App\Models\SystemLog::create([
                        'user_id' => $editReq->user_id,
                        'event_type' => 'INVENTORY',
                        'action' => 'STOCK_VERIFICATION',
                        'description' => "Stock verification & reconciliation approved for item '{$description}'. Physical: {$physicalCount}. Condition: {$condition}. Remarks: {$remarks}.",
                        'severity' => $variance === 0 ? 'info' : 'warning',
                        'ip_address' => request()->ip()
                    ]);
                }

                \Illuminate\Support\Facades\DB::commit();

                \App\Models\SystemLog::create([
                    'user_id' => auth()->id(),
                    'event_type' => 'SECURITY',
                    'action' => 'AUTHORIZATION',
                    'description' => "Administrator authorized STOCK RECONCILIATION request submitted by {$editReq->user->name}.",
                    'severity' => 'info',
                    'ip_address' => request()->ip()
                ]);

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Failed to process reconciliation: ' . $e->getMessage()]);
            }
        }

        // Notification logic
        $finalMsg = "<div class='personnel-view' style='padding: 15px; border: 1px solid {$color}; border-radius: 12px; background: " . ($request->status === 'approved' ? 'rgba(16, 185, 129, 0.05)' : 'rgba(220, 38, 38, 0.05)') . ";'>";
        $finalMsg .= "<b style='color: {$color}'>{$statusHeader}</b><br>";
        
        if ($request->status === 'approved') {
            $payload = json_decode($editReq->payload, true);
            if (isset($payload['items'])) {
                $finalMsg .= "Your batch stock verification for <b style='color: #4f46e5;'>" . count($payload['items']) . " items</b> has been authorized. Registry balances have been successfully reconciled.";
            } else {
                $finalMsg .= "Your physical stock verification for <b style='color: #4f46e5;'>" . e($payload['description']) . "</b> (Physical Count: " . e($payload['physical_count']) . ") has been authorized. Registry balances have been successfully reconciled.";
            }
        } else {
            $finalMsg .= "Your stock verification and reconciliation request was not authorized.<br>";
            if ($request->reason) {
                $finalMsg .= "<div style='margin-top: 5px; color: #b91c1c;'>" . nl2br(e($request->reason)) . "</div>";
            }
        }
        $finalMsg .= "</div>";

        // Update Personnel Message
        $personnelMsg = Message::where('receiver_id', $editReq->user_id)
            ->where('edit_request_id', $editReq->id)
            ->where(function($q) {
                $q->where('message', 'like', '%STOCK RECONCILIATION%')
                  ->orWhere('message', 'like', '%VERIFICATION%')
                  ->orWhere('message', 'like', '%BATCH%');
            })
            ->first();

        if ($personnelMsg) {
            $personnelMsg->update(['message' => $finalMsg, 'is_automated' => true]);
        } else {
            Message::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $editReq->user_id,
                'message' => $finalMsg,
                'is_automated' => true,
                'edit_request_id' => $editReq->id
            ]);
        }

        // Update Admin Messages
        $adminMsgs = Message::whereIn('receiver_id', User::where('is_admin', true)->pluck('id'))
            ->where(function($q) use ($editReq) {
                $q->where('edit_request_id', $editReq->id)
                  ->orWhere('message', 'like', "%verification-actions-{$editReq->id}%");
            })
            ->get();

        foreach ($adminMsgs as $adminMsg) {
            $statusLabel = $request->status === 'approved' ? 'AUTHORIZED & RECONCILED' : 'REJECTED';
            $newMsg = "<div style='padding: 15px; border-left: 4px solid {$color}; background: rgba(0,0,0,0.02);'>";
            $newMsg .= "<b style='color: {$color};'>VERIFICATION {$statusLabel}</b><br>";
            $newMsg .= "Submission by " . ($editReq->user->name ?? 'Personnel') . " has been {$request->status}.";
            if ($request->status === 'rejected' && $request->reason) {
                $newMsg .= "<div style='margin-top: 5px; font-size: 0.85rem; color: #666;'>Reason: " . e($request->reason) . "</div>";
            }
            $newMsg .= "</div>";
            $adminMsg->update(['message' => $newMsg, 'is_automated' => true]);
        }

        return response()->json(['success' => true]);
    }
}
