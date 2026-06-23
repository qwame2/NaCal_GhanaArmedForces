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
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');

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

        if ($type === 'logs') {
            $query = SystemLog::with('user')->where('is_archived', true);
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('event_type', 'like', "%{$search}%")
                      ->orWhereHas('user', function($uq) use ($search) {
                          $uq->where('name', 'like', "%{$search}%");
                      });
                });
            }
        } elseif ($type === 'disbursements') {
            $query = \App\Models\IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
                ->select('issued_items.*', 'issuances.issuance_date', 'issuances.beneficiary', 'issuances.authority', 'issuances.issuance_type', 'issuances.created_at');
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('issued_items.description', 'like', "%{$search}%")
                      ->orWhere('issuances.beneficiary', 'like', "%{$search}%")
                      ->orWhere('issuances.authority', 'like', "%{$search}%");
                });
            }
        } else {
            $query = Message::with(['sender', 'receiver'])->where('is_archived', true);
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('message', 'like', "%{$search}%")
                      ->orWhereHas('sender', function($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('receiver', function($rq) use ($search) {
                          $rq->where('name', 'like', "%{$search}%");
                      });
                });
            }
        }

        if ($startDate) {
            if ($type === 'disbursements') {
                $query->whereDate('issuances.issuance_date', '>=', $startDate);
            } else {
                $query->whereDate('created_at', '>=', $startDate);
            }
        }
        if ($endDate) {
            if ($type === 'disbursements') {
                $query->whereDate('issuances.issuance_date', '<=', $endDate);
            } else {
                $query->whereDate('created_at', '<=', $endDate);
            }
        }

        if ($type === 'disbursements') {
            $data = $query->orderBy('issuances.issuance_date', 'desc')
                          ->orderBy('issuances.created_at', 'desc')
                          ->paginate($perPage);
        } else {
            $data = $query->orderBy('created_at', 'desc')->paginate($perPage);
        }

        return view('admin.archive', compact('data', 'type', 'startDate', 'endDate', 'search', 'ledgeMap'));
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
