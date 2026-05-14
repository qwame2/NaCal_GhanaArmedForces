<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $type = $request->get('type', 'messages');
        $perPage = $request->get('per_page', 15);

        if ($type === 'logs') {
            $data = SystemLog::with('user')
                ->where('is_archived', true)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } else {
            $data = Message::with(['sender', 'receiver'])
                ->where('is_archived', true)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        }

        return view('admin.archive', compact('data', 'type'));
    }

    public function archiveMessage(Request $request, $id)
    {
        if (!auth()->user()->is_admin) return response()->json(['success' => false], 403);
        
        $message = Message::findOrFail($id);
        $message->is_archived = true;
        $message->save();

        return response()->json(['success' => true]);
    }

    public function archiveLog(Request $request, $id)
    {
        if (!auth()->user()->is_admin) return response()->json(['success' => false], 403);

        $log = SystemLog::findOrFail($id);
        $log->is_archived = true;
        $log->save();

        return response()->json(['success' => true]);
    }

    public function restoreMessage($id)
    {
        if (!auth()->user()->is_admin) return redirect()->route('dashboard')->with('error', 'Unauthorized');

        $message = Message::findOrFail($id);
        $message->is_archived = false;
        $message->save();

        return back()->with('success', 'Message restored from archive.');
    }

    public function restoreLog($id)
    {
        if (!auth()->user()->is_admin) return redirect()->route('dashboard')->with('error', 'Unauthorized');

        $log = SystemLog::findOrFail($id);
        $log->is_archived = false;
        $log->save();

        return back()->with('success', 'Log restored from archive.');
    }

    public function bulkArchiveLogs(Request $request)
    {
        if (!auth()->user()->is_admin) return back()->with('error', 'Unauthorized access.');

        $ids = $request->input('log_ids', []);
        if (empty($ids)) return back()->with('error', 'Please select records to archive.');

        // Update database with strict persistence
        $count = SystemLog::whereIn('id', $ids)->update(['is_archived' => true]);

        return redirect()->route('admin.logs')->with('success', "Archival successful: {$count} records moved to system archive.");
    }
}
