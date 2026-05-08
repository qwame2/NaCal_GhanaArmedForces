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
            
            $msgContent = "<style>.admin-only { display: none !important; } .admin-view { display: block !important; } .personnel-view { display: none !important; }</style>";
            $msgContent .= "<div class='edit-req-msg admin-view' style='display:none;'><b>{$typeLabel} REQUEST</b><br>Personnel " . auth()->user()->name . " has requested to {$actionWord} {$displayTitle}.<br><br><b>Reason:</b> {$request->reason}<br><br>";
            $msgContent .= "<div style='margin-top: 10px; display: flex; gap: 10px;' id='edit-req-actions-{$editReq->id}'>";
            $msgContent .= "<button onclick='window.processEditRequest({$editReq->id}, \"approved\", this)' style='background: #10b981; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: bold;'>Approve</button>";
            $msgContent .= "<button onclick='window.processEditRequest({$editReq->id}, \"canceled\", this)' style='background: #dc2626; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: bold;'>Cancel</button>";
            $msgContent .= "</div></div>";
            
            $personnelLabel = $requestType === 'edit' ? 'EDIT' : 'DELETE';
            $msgContent .= "<div class='edit-req-msg personnel-view' style='display:none;'><b>{$personnelLabel} REQUEST SUBMITTED</b><br>Waiting for approval from admin to {$actionWord} {$displayTitle}.<br><br><b>Your Reason:</b> {$request->reason}</div>";

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
        $editReq->save();

        $requestType = $editReq->request_type ?? 'edit';
        $statusText = $request->status === 'approved' ? 'APPROVED' : 'CANCELED';
        $color = $request->status === 'approved' ? '#10b981' : '#dc2626';

        // Update the original message that triggered this for Admin's persistence
        $originalMsg = Message::where('message', 'like', "%edit-req-actions-{$editReq->id}%")->first();
        if ($originalMsg) {
            $item = InventoryBatch::with('items')->find($editReq->item_id);
            $itemNames = $item ? $item->items->pluck('description')->take(3)->implode(', ') : 'Unknown';
            if ($item && $item->items->count() > 3) $itemNames .= ' etc.';
            
            $batchInfo = $item ? "Batch #{$item->id} ({$itemNames})" : "Batch #{$editReq->item_id}";
            $typeLabel = strtoupper($requestType);
            $actionWord = $requestType === 'edit' ? 'edit' : 'PERMANENTLY DELETE';
            
            $newContent = "<div class='edit-req-msg admin-view'><b>{$typeLabel} REQUEST</b><br>Personnel " . $editReq->user->name . " requested to {$actionWord} {$batchInfo}.<br><br><b>Reason:</b> {$editReq->reason}<br><br><div style='padding: 8px 12px; border-radius: 8px; background: " . ($request->status === 'approved' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(220, 38, 38, 0.1)') . "; color: {$color}; font-weight: 800; border: 1px solid {$color}; display: inline-block;'>Request {$statusText}</div></div>";
            $originalMsg->update(['message' => $newContent]);
        }

        // Send a confirmation message to the Personnel
        $item = InventoryBatch::with('items')->find($editReq->item_id);
        $itemNames = $item ? $item->items->pluck('description')->take(3)->implode(', ') : 'Unknown';
        if ($item && $item->items->count() > 3) $itemNames .= ' etc.';
        $batchInfo = $item ? "Batch #{$item->id} ({$itemNames})" : "Batch #{$editReq->item_id}";
        $confirmationMsg = "<b style='color: {$color}'>{$statusText}</b><br>Your request to " . ($requestType === 'edit' ? 'edit' : 'DELETE') . " {$batchInfo} has been {$request->status}.";
        
        if ($request->status === 'approved' && $requestType === 'edit') {
            $editUrl = route('receiveditems', ['edit_batch' => $editReq->item_id]);
            $confirmationMsg .= "<div class='personnel-view' style='display:none;'><br><a href='{$editUrl}' style='display: inline-block; background: #4f46e5; color: white; text-decoration: none; padding: 8px 16px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);'>Open Editor Now</a></div>";
        } elseif ($request->status === 'approved' && $requestType === 'delete') {
            $confirmationMsg .= "<br><br><i style='color: #ef4444; font-size: 0.85rem; font-weight: 700;'>You may now proceed to delete this batch from the Received Items console.</i>";
        }

        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $editReq->user_id,
            'message' => $confirmationMsg
        ]);

        if (ob_get_length()) ob_clean();
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
            return response()->json(['allowed' => true, 'status' => 'approved']);
        }

        if (ob_get_length()) ob_clean();
        return response()->json(['allowed' => false, 'status' => $editReq->status]);
    }
}
