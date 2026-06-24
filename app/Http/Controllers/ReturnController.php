<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Issuance;
use App\Models\IssuedItem;
use App\Models\ReturnedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        \App\Http\Controllers\StoreRequisitionController::checkOverdueTemporaryItems();
        $isStoresHead = (auth()->user()->role === 'Main Admin' || strcasecmp(auth()->user()->department, 'Stores') === 0 || strcasecmp(auth()->user()->department, 'Store') === 0);
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head']) && !$isStoresHead) {
            abort(403, 'Unauthorized. Access restricted to Department Head (Stores) and Store Officers.');
        }

        if (auth()->user()->is_admin && auth()->user()->role !== 'Main Admin') {
            return redirect()->route('admin.inventory')->with('info', 'Strategic Oversight required. Redirecting to Command Center.');
        }

        // Run self-healing to convert collected requisitions
        $this->selfHealRequisitions();

        if (!Schema::hasTable('returned_items')) {
            Schema::create('returned_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('issued_item_id')->constrained('issued_items')->onDelete('cascade');
                $table->integer('returned_qty');
                $table->date('return_date');
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('returned_items', 'remarks')) {
            Schema::table('returned_items', function (Blueprint $table) {
                $table->text('remarks')->nullable();
            });
        }

        // Global DB stats for returns card aggregates (optimized to run in one aggregate query, cached)
        $stats = \Illuminate\Support\Facades\Cache::remember('temporary_returns_stats', 600, function() {
            $statsData = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
                ->where('issuances.issuance_type', 'Temporary')
                ->selectRaw("
                    SUM(issued_items.quantity) as total_outstanding,
                    COUNT(DISTINCT CASE WHEN issued_items.quantity > 0 THEN issuances.beneficiary END) as active_holders,
                    SUM(CASE WHEN issued_items.quantity > 0 THEN 1 ELSE 0 END) as total_active_holdings
                ")
                ->first();

            return [
                'total_outstanding' => (float) ($statsData->total_outstanding ?? 0),
                'active_holders' => (int) ($statsData->active_holders ?? 0),
                'total_active_holdings' => (int) ($statsData->total_active_holdings ?? 0),
            ];
        });

        // Build search & paginated query
        $query = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->leftJoin('store_requisitions', 'issuances.requisition_id', '=', 'store_requisitions.id')
            ->leftJoin('users as confirming_officers', 'store_requisitions.collected_by', '=', 'confirming_officers.id')
            ->leftJoin(DB::raw('(SELECT description, MAX(unit) as unit FROM inventory_items GROUP BY description) as inv_units'), 'issued_items.description', '=', 'inv_units.description')
            ->select(
                'issued_items.*', 
                'issuances.beneficiary', 
                'issuances.authority', 
                'issuances.issuance_date', 
                'issuances.issuance_type',
                'store_requisitions.collector_name',
                'store_requisitions.purpose',
                'confirming_officers.name as confirming_officer_name',
                DB::raw('COALESCE(NULLIF(issued_items.unit, ""), inv_units.unit) as actual_unit')
            )
            ->addSelect([
                'pending_recovery' => \App\Models\EditRequest::select('id')
                    ->whereColumn('item_id', 'issued_items.id')
                    ->where('item_type', 'issued_item')
                    ->where('request_type', 'item_recovery')
                    ->where('status', 'pending')
                    ->limit(1)
            ])
            ->where('issuances.issuance_type', 'Temporary')
            ->where('issued_items.quantity', '>', 0);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('issued_items.description', 'LIKE', "%{$search}%")
                  ->orWhere('issuances.beneficiary', 'LIKE', "%{$search}%")
                  ->orWhere('issued_items.ledge_category', 'LIKE', "%{$search}%")
                  ->orWhere('store_requisitions.collector_name', 'LIKE', "%{$search}%")
                  ->orWhere('confirming_officers.name', 'LIKE', "%{$search}%");
            });
        }

        $issuedItems = $query->orderBy('issuances.issuance_date', 'desc')
            ->paginate(5)
            ->withQueryString();

        return view('returns.index', compact('issuedItems', 'stats'));
    }

    public function store(Request $request)
    {
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head'])) {
            return redirect()->back()->with('error', 'Unauthorized: Department Heads are only allowed to view returned items and cannot make changes.');
        }

        $validated = $request->validate([
            'issued_item_id' => 'required|exists:issued_items,id',
            'return_qty' => 'required|integer|min:1',
            'return_date' => 'required|date',
            'remarks' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $issuedItem = IssuedItem::findOrFail($validated['issued_item_id']);
            
            if ($issuedItem->issuance->issuance_type === 'Permanent') {
                throw new \Exception("Permanently issued items cannot be returned.");
            }
            
            if ($validated['return_qty'] > $issuedItem->quantity) {
                throw new \Exception("Return quantity cannot exceed issued quantity.");
            }

            // Create an EditRequest for Administrative Review
            $editReq = \App\Models\EditRequest::create([
                'user_id' => auth()->id(),
                'item_type' => 'issued_item',
                'item_id' => $issuedItem->id,
                'request_type' => 'item_recovery',
                'reason' => 'Registry Asset Recovery: ' . ($validated['remarks'] ?: 'No additional remarks provided'),
                'status' => 'pending',
                'payload' => json_encode($validated)
            ]);

            $admins = \App\Models\User::getApproversQuery()->where('registration_status', 'approved')->get();
            if ($admins->count() > 0) {
                $msgContent = "<div class='recovery-approval-card' style='background: white; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); margin: 10px 0;'>";
                $msgContent .= "<div style='display: flex; align-items: center; gap: 12px; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;'>";
                $msgContent .= "<div style='width: 40px; height: 40px; background: #f59e0b; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;'>";
                $msgContent .= "<i data-lucide='refresh-cw' style='width: 20px;'></i>";
                $msgContent .= "</div><div>";
                $msgContent .= "<h4 style='margin: 0; color: #0f172a; font-size: 0.95rem; font-weight: 800; letter-spacing: -0.01em;'>RECOVERY APPROVAL</h4>";
                $msgContent .= "<p style='margin: 0; font-size: 0.75rem; color: #64748b; font-weight: 600;'>Asset Re-integration Request</p>";
                $msgContent .= "</div></div>";
                
                $msgContent .= "<div id='recovery-actions-{$editReq->id}' style='display: flex; flex-direction: column; gap: 8px;'>";
                $msgContent .= "<button class='recovery-preview-btn' data-recovery-req-id='{$editReq->id}' style='width: 100%; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; padding: 12px; border-radius: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;'>";
                $msgContent .= "<i data-lucide='eye' style='width:16px; flex-shrink:0;'></i> Preview Recovery Details</button>";
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

                $confirmation = "
                    <div class='personnel-view' style='padding: 15px; border: 1px solid #f59e0b; border-radius: 16px; background: rgba(245, 158, 11, 0.03); display: flex; align-items: center; gap: 12px;'>
                        <div style='width: 32px; height: 32px; background: #f59e0b; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;'><i data-lucide='clock' style='width: 16px;'></i></div>
                        <div><b style='color: #f59e0b; font-size: 0.85rem;'>RECOVERY SUBMITTED</b><br><span style='font-size: 0.75rem; color: #64748b; font-weight: 600;'>Awaiting Admin authorization for asset re-integration.</span></div>
                    </div>";

                \App\Models\Message::create([
                    'sender_id' => $admins->first()->id ?? 1,
                    'receiver_id' => auth()->id(),
                    'message' => $confirmation,
                    'is_automated' => true,
                    'edit_request_id' => $editReq->id
                ]);
                
                // Log this submission automatically to System Archive
                \App\Models\SystemLog::create([
                    'user_id' => auth()->id(),
                    'event_type' => 'INVENTORY',
                    'action' => 'SUBMIT_RECOVERY_REQUEST',
                    'description' => auth()->user()->name . " submitted a return recovery request for {$validated['return_qty']} {$issuedItem->description}(s).",
                    'severity' => 'info',
                    'is_archived' => true,
                    'metadata' => [
                        'item_description' => $issuedItem->description,
                        'return_qty' => $validated['return_qty'],
                        'return_date' => $validated['return_date']
                    ]
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Recovery request submitted for administrative approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Recovery submission failed: ' . $e->getMessage());
        }
    }

    public function history()
    {
        $isStoresHead = (auth()->user()->role === 'Main Admin' || strcasecmp(auth()->user()->department, 'Stores') === 0 || strcasecmp(auth()->user()->department, 'Store') === 0);
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head']) && !$isStoresHead) {
            return response()->json([], 403);
        }

        if (!Schema::hasTable('returned_items')) {
            return response()->json([]);
        }

        $returnedItems = ReturnedItem::join('issued_items', 'returned_items.issued_item_id', '=', 'issued_items.id')
            ->join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->leftJoin('store_requisitions', 'issuances.requisition_id', '=', 'store_requisitions.id')
            ->leftJoin('users as confirming_officers', 'store_requisitions.collected_by', '=', 'confirming_officers.id')
            ->leftJoin(DB::raw('(SELECT description, MAX(unit) as unit FROM inventory_items GROUP BY description) as inv_units'), 'issued_items.description', '=', 'inv_units.description')
            ->select(
                'returned_items.id', 
                'returned_items.returned_qty',
                'returned_items.return_date',
                'returned_items.remarks',
                'returned_items.created_at',
                'issued_items.description', 
                'issued_items.ledge_category',
                'issued_items.quantity as current_balance',
                'issuances.beneficiary',
                'issuances.authority',
                'issuances.issuance_date',
                'issuances.created_at as issuance_timestamp',
                'store_requisitions.collector_name',
                'confirming_officers.name as confirming_officer_name',
                DB::raw('COALESCE(NULLIF(issued_items.unit, ""), inv_units.unit) as actual_unit')
            )
            ->orderBy('returned_items.created_at', 'desc')
            ->get();

        return response()->json($returnedItems);
    }

    public function purge(Request $request)
    {
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head'])) {
            return redirect()->back()->with('error', 'Unauthorized: Department Heads are only allowed to view returned items and cannot make changes.');
        }

        // Force strict administrator authorization check
        if (!auth()->check() || !auth()->user()->is_admin) {
            return redirect()->back()->with('error', 'Unauthorized Action: Purging return history is restricted to Administrators only.');
        }

        try {
            $ids = $request->input('ids');
            
            if (!$ids || !is_array($ids)) {
                return redirect()->back()->with('error', 'Audit Error: No valid recovery IDs detected for purge.');
            }

            // Direct SQL for maximum reliability
            $idString = implode(',', array_map('intval', $ids));
            \Illuminate\Support\Facades\DB::statement("DELETE FROM returned_items WHERE id IN ($idString)");

            // Log the purge activity
            if (auth()->check()) {
                $user = auth()->user();
                $count = count($ids);
                
                \App\Models\SystemLog::create([
                    'user_id' => $user->id,
                    'event_type' => 'SECURITY',
                    'action' => 'PURGE_RECORDS',
                    'description' => "Administrator purged {$count} recovery logs from the active database.",
                    'severity' => 'danger',
                    'is_archived' => true,
                    'ip_address' => request()->ip()
                ]);
            }

            return redirect()->back()->with([
                'success' => count($ids) . ' records successfully purged from NACOC logs.',
                'reopen_history' => true
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Purge Protocol Failed: ' . $e->getMessage());
        }
    }

    public static function selfHealRequisitions()
    {
        if (!Schema::hasColumn('issuances', 'requisition_id')) {
            try {
                Schema::table('issuances', function (Blueprint $table) {
                    $table->unsignedBigInteger('requisition_id')->nullable();
                });
            } catch (\Exception $e) {
                // Ignore if already created
            }
        }

        // Clean up duplicates from the previous mass-assignment bug
        Issuance::whereNull('requisition_id')
            ->where(function($q) {
                $q->where('authority', 'Requisition Approved')
                  ->orWhere('beneficiary', 'like', '%(%');
            })
            ->delete();

        // Use whereNotExists to only fetch collected requisitions that DO NOT have an issuance yet
        $collectedReqs = \App\Models\StoreRequisition::with(['items', 'processor'])
            ->whereIn('status', ['approved', 'partially_approved'])
            ->whereNotNull('collected_at')
            ->whereNotExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                      ->from('issuances')
                      ->whereColumn('issuances.requisition_id', 'store_requisitions.id');
            })
            ->get();

        foreach ($collectedReqs as $req) {
            $issuance = Issuance::create([
                'issuance_date' => $req->collected_at->format('Y-m-d'),
                'beneficiary' => $req->department . ' (' . $req->requester_name . ')',
                'authority' => $req->processor?->name ?? 'Requisition Approved',
                'issuance_type' => $req->usage_type === 'temporary' ? 'Temporary' : 'Permanent',
                'requisition_id' => $req->id,
            ]);

            foreach ($req->items as $item) {
                if ($item->quantity_approved > 0) {
                    IssuedItem::create([
                        'issuance_id' => $issuance->id,
                        'description' => $item->description,
                        'ledge_category' => $item->category ?? 'A',
                        'quantity' => $item->quantity_approved,
                        'unit' => $item->unit,
                    ]);
                }
                if ($item->alternative_quantity_approved > 0 && !empty($item->alternative_description)) {
                    IssuedItem::create([
                        'issuance_id' => $issuance->id,
                        'description' => $item->alternative_description,
                        'ledge_category' => $item->category ?? 'A',
                        'quantity' => $item->alternative_quantity_approved,
                        'unit' => $item->unit,
                    ]);
                }
            }
        }
    }
}
