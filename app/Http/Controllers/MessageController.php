<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function fetchMessages($userId)
    {
        $authId = auth()->id();
        
        $messages = Message::where('is_archived', false)
            ->where('is_automated', false)
            ->where(function($q) use ($authId, $userId) {
                $q->where(function($sq) use ($authId, $userId) {
                    $sq->where('sender_id', $authId)->where('receiver_id', $userId);
                })->orWhere(function($sq) use ($authId, $userId) {
                    $sq->where('sender_id', $userId)->where('receiver_id', $authId);
                });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        if (ob_get_length()) ob_clean();
        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'message' => 'nullable|string',
                'attachment' => 'nullable|file|max:10240' // 10MB max
            ]);

            $data = [
                'sender_id' => auth()->id(),
                'receiver_id' => $request->receiver_id,
                'message' => $request->message
            ];

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('attachments', 'public');
                $data['attachment'] = $path;
                $data['attachment_name'] = $file->getClientOriginalName();
            }

            $message = Message::create($data);

            // Clear any accidental output (like PHP notices) that would corrupt JSON
            if (ob_get_length()) ob_clean();

            return response()->json([
                'success' => true,
                'message' => $message->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead($userId)
    {
        Message::where('sender_id', $userId)
            ->where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function getUnreadCounts()
    {
        $authId = auth()->id();
        $query = Message::where('is_archived', false)->where('receiver_id', $authId)
            ->whereNull('read_at')
            ->where('is_automated', false);

        // Exclude suggested quantities / alternative proposals for everyone
        $query->where(function($q) {
            $q->where('is_automated', false)
              ->orWhere(function($sq) {
                  $sq->where('is_automated', true)
                     ->where('message', 'not like', '%SUGGESTED QUANTITY PROPOSED%')
                     ->where('message', 'not like', '%ALTERNATIVE ITEM PROPOSED%')
                     ->where('message', 'not like', '%suggested quantities%')
                     ->where('message', 'not like', '%alternative items%');
              });
        });

        // If the user is not an administrator or delegated approver, exclude skipped administrative logs
        if (auth()->check() && !auth()->user()->is_admin && !auth()->user()->isDelegatedApprover()) {
            $query->where(function($q) {
                $q->where('is_automated', false)
                  ->orWhere(function($sq) {
                      $sq->where('is_automated', true)
                         ->where('message', 'not like', '%DELETE REQUEST LOG%')
                         ->where('message', 'not like', '%delete-req-msg%')
                         ->where('message', 'not like', '%PERMANENTLY DELETE Batch%')
                         ->where('message', 'not like', '%REQUEST CANCELED%')
                         ->where('message', 'not like', '%EDIT REQUEST LOG%')
                         ->where('message', 'not like', '%edit-req-log%')
                         ->where('message', 'not like', '%request to edit Batch%')
                         ->where('message', 'not like', '%REQUEST APPROVED%')
                         ->where('message', 'not like', '%REQUEST REJECTED%');
                  });
            });
        }

        $counts = $query->selectRaw('sender_id, count(*) as count')
            ->groupBy('sender_id')
            ->get()
            ->pluck('count', 'sender_id')
            ->toArray();

        // If the logged in user is a store officer (non-admin) and not a delegated approver, add active rollback requests count to admins
        if (auth()->check() && !auth()->user()->is_admin && !auth()->user()->isDelegatedApprover()) {
            $rollbackCount = \App\Models\EditRequest::where('user_id', $authId)
                ->where('status', 'rollback')
                ->count();
                
            if ($rollbackCount > 0) {
                $adminIds = User::getApproversQuery()->pluck('id')->toArray();
                foreach ($adminIds as $adminId) {
                    $counts[$adminId] = ($counts[$adminId] ?? 0) + $rollbackCount;
                }
            }
        }

        if (ob_get_length()) ob_clean();
        return response()->json($counts);
    }

    public function getTotalUnreadCount()
    {
        $query = Message::where('is_archived', false)->where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->where('is_automated', false);

        // Exclude suggested quantities / alternative proposals for everyone
        $query->where(function($q) {
            $q->where('is_automated', false)
              ->orWhere(function($sq) {
                  $sq->where('is_automated', true)
                     ->where('message', 'not like', '%SUGGESTED QUANTITY PROPOSED%')
                     ->where('message', 'not like', '%ALTERNATIVE ITEM PROPOSED%')
                     ->where('message', 'not like', '%suggested quantities%')
                     ->where('message', 'not like', '%alternative items%');
              });
        });

        // If the user is not an administrator or delegated approver, exclude skipped administrative logs
        if (auth()->check() && !auth()->user()->is_admin && !auth()->user()->isDelegatedApprover()) {
            $query->where(function($q) {
                $q->where('is_automated', false)
                  ->orWhere(function($sq) {
                      $sq->where('is_automated', true)
                         ->where('message', 'not like', '%DELETE REQUEST LOG%')
                         ->where('message', 'not like', '%delete-req-msg%')
                         ->where('message', 'not like', '%PERMANENTLY DELETE Batch%')
                         ->where('message', 'not like', '%REQUEST CANCELED%')
                         ->where('message', 'not like', '%EDIT REQUEST LOG%')
                         ->where('message', 'not like', '%edit-req-log%')
                         ->where('message', 'not like', '%request to edit Batch%')
                         ->where('message', 'not like', '%REQUEST APPROVED%')
                         ->where('message', 'not like', '%REQUEST REJECTED%');
                  });
            });
        }

        // Exclude approval notifications and requests from the persistent looping count
        $query->where(function($q) {
            $q->where(function($sq) {
                $sq->where('message', 'not like', '%sra-approved-msg%')
                  ->where('message', 'not like', '%sra-approval-card%')
                  ->where('message', 'not like', '%edit-req-actions-%')
                  ->where('message', 'not like', '%edit-req-msg%')
                  ->where('message', 'not like', '%verification-approval-card%')
                  ->where('message', 'not like', '%recovery-approval-card%')
                  ->where('message', 'not like', '%requisition-msg%')
                  ->where('message', 'not like', '%requisition-status-msg%');
            })->orWhereNull('message');
        });

        $count = $query->count();

        $approvalsCount = Message::where('is_archived', false)
            ->where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->where(function($q) {
                $q->where('message', 'like', '%sra-approved-msg%')
                  ->orWhere('message', 'like', '%requisition-status-msg%');
            })
            ->count();

        $requestedApprovalsCount = Message::where('is_archived', false)
            ->where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->where(function($q) {
                $q->where('message', 'like', '%sra-approval-card%')
                  ->orWhere('message', 'like', '%edit-req-actions-%')
                  ->orWhere('message', 'like', '%edit-req-msg%')
                  ->orWhere('message', 'like', '%verification-approval-card%')
                  ->orWhere('message', 'like', '%recovery-approval-card%')
                  ->orWhere('message', 'like', '%requisition-msg%');
            })
            ->count();

        if (ob_get_length()) ob_clean();
        return response()->json([
            'count' => $count,
            'approvals_count' => $approvalsCount,
            'requested_approvals_count' => $requestedApprovalsCount
        ]);
    }
    public function getOnlineStatuses()
    {
        $statuses = \App\Models\User::where('registration_status', 'approved')->pluck('is_online', 'id');
        if (ob_get_length()) ob_clean();
        return response()->json($statuses);
    }
}
