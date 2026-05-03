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
        
        $messages = Message::where(function($q) use ($authId, $userId) {
            $q->where('sender_id', $authId)->where('receiver_id', $userId);
        })->orWhere(function($q) use ($authId, $userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $authId);
        })->orderBy('created_at', 'asc')->get();

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
        $counts = Message::where('receiver_id', $authId)
            ->whereNull('read_at')
            ->selectRaw('sender_id, count(*) as count')
            ->groupBy('sender_id')
            ->get()
            ->pluck('count', 'sender_id');

        if (ob_get_length()) ob_clean();
        return response()->json($counts);
    }

    public function getTotalUnreadCount()
    {
        $count = Message::where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        if (ob_get_length()) ob_clean();
        return response()->json(['count' => $count]);
    }
    public function getOnlineStatuses()
    {
        $statuses = \App\Models\User::pluck('is_online', 'id');
        if (ob_get_length()) ob_clean();
        return response()->json($statuses);
    }
}
