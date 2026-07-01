<?php

namespace App\Http\Controllers;

use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function create()
    {
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head'])) {
            abort(403, 'Unauthorized: Department Heads are only allowed to view received items and cannot make changes.');
        }

        if (!auth()->user()->is_admin && !auth()->user()->can_add_inventory && !auth()->user()->isDelegatedApprover()) {
            abort(403, 'Unauthorized: Permission Required');
        }

        \App\Models\InventoryBatch::selfHealSchema();

        // Ledge mapping for display and calculations (Category standardization)
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

        // Fetch unique suppliers and donors for the dropdown
        $registryData = \App\Models\Setting::get('suppliers_registry', []);
        if (is_string($registryData)) {
            $registryData = json_decode($registryData, true) ?? [];
        }
        $registrySuppliers = is_array($registryData) ? array_keys($registryData) : [];
        $allSuppliers = collect($registrySuppliers)
            ->filter(function ($item) {
                return strtolower(trim($item)) !== 'system';
            })
            ->unique(function ($item) {
                return strtolower(trim($item));
            })
            ->values();

        $donorNames1 = \App\Models\InventoryBatch::where('acquisition_type', 'Donor')
            ->whereNotNull('donor_name')
            ->distinct()
            ->pluck('donor_name');

        $donorNames2 = \App\Models\InventoryBatch::where('acquisition_type', 'Donor')
            ->whereNotNull('supplier_name')
            ->distinct()
            ->pluck('supplier_name');

        $allDonors = $donorNames1->concat($donorNames2)
            ->map(function($name) {
                return preg_replace('/\s\[.*\]$/', '', $name);
            })
            ->filter()
            ->unique()
            ->values();

        $rawItems = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->select(
                'inventory_items.description',
                'inventory_items.unit',
                'inventory_items.qty',
                'inventory_items.stock_balance',
                'inventory_items.variance',
                'inventory_batches.ledge_category',
                'inventory_batches.supplier_status',
                'inventory_items.id as item_id'
            )
            ->orderBy('inventory_items.id', 'asc')
            ->get();

        $grouped = $rawItems->groupBy(function($item) {
            return trim(strtoupper($item->description));
        });

        $existingItems = $grouped->map(function($group) {
            $nonDraftGroup = $group->filter(fn($item) => $item->supplier_status !== 'System Draft');
            
            $lastItem = $nonDraftGroup->last() ?? $group->last();
            
            $stockBalance = $nonDraftGroup->sum(function($item) {
                return (float) str_replace(',', '', $item->stock_balance);
            });
            
            $variance = $nonDraftGroup->sum(function($item) {
                return (float) str_replace(',', '', $item->variance);
            });

            $qty = $lastItem ? (float) str_replace(',', '', $lastItem->qty) : 0;

            return (object) [
                'description'    => $lastItem->description,
                'unit'           => $lastItem->unit,
                'ledge_category' => $lastItem->ledge_category,
                'stock_balance'  => $stockBalance,
                'qty'            => $qty,
                'variance'       => $variance,
            ];
        })->values();

        $suppliersRegistry = is_array($registryData) ? $registryData : [];
        return view('inventory.create', compact('ledgeMap', 'allSuppliers', 'allDonors', 'existingItems', 'suppliersRegistry'));
    }

    public function store(Request $request)
    {
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized: Department Heads are only allowed to view received items and cannot make changes.'], 403);
        }

        $validated = $request->validate([
            'ledge_category' => 'required|string',
            'supplier_name' => 'nullable|string',
            'supplier_status' => 'required|string',
            'donor_name' => 'nullable|string',
            'acquisition_type' => 'required|string',
            'driver_name' => 'nullable|string',
            'driver_phone' => ['nullable', 'string', 'regex:/^(N\/A|\d{10})$/i'],
            'delivery_person' => 'nullable|string',
            'delivery_phone' => ['nullable', 'string', 'regex:/^(N\/A|\d{10})$/i'],
            'supplier_phone' => 'nullable|string',
            'supplier_email' => 'nullable|email',
            'supplier_address' => 'nullable|string',
            'entry_date' => 'required',
            'arrival_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.serial_number' => 'nullable|string',
            'items.*.unit' => 'required|string',

            'items.*.stock_balance' => 'required|string',
            'items.*.qty' => 'nullable|string',
            'items.*.variance' => 'required|string',

            'items.*.remarks' => 'nullable|string',
        ]);

        $arrivalDate = $validated['arrival_date'];

        try {
            DB::beginTransaction();


            // Create the Batch or Stage it for Approval
            $is_admin = auth()->user()->is_admin || auth()->user()->isDelegatedApprover();
            
            if (!$is_admin) {
                // Divert to staged approval process (Don't save items yet)
                $payloadData = $validated;
                if ($request->has('rollback_id')) {
                    $payloadData['rollback_id'] = $request->rollback_id;
                }

                $editReq = \App\Models\EditRequest::create([
                    'user_id' => auth()->id(),
                    'item_id' => 0, // Fallback for creation requests
                    'item_type' => 'batch_creation',
                    'request_type' => 'sra_creation',
                    'reason' => $request->has('rollback_id') ? 'Correction submission for rolled back entry' : 'New Inventory Entry Submission',
                    'status' => 'pending',
                    'payload' => json_encode($payloadData)
                ]);

                // Send Approval Request to all Admins & Delegated Approver
                $admins = \App\Models\User::getApproversQuery()->where('registration_status', 'approved')->get();
                if ($admins->count() > 0) {
                    $itemNames = collect($validated['items'])->pluck('description')->take(3)->implode(', ');
                    if (count($validated['items']) > 3) $itemNames .= ' etc.';
                    $source = $validated['acquisition_type'] === 'Donor' ? $validated['donor_name'] : $validated['supplier_name'];
                    
                    $msgContent = "<div class='sra-approval-card' style='background: white; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); margin: 10px 0;'>";
                    $msgContent .= "<div style='display: flex; align-items: center; gap: 12px; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;'>";
                    $msgContent .= "<div style='width: 40px; height: 40px; background: #6366f1; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;'>";
                    $msgContent .= "<i data-lucide='package-plus' style='width: 20px;'></i>";
                    $msgContent .= "</div><div>";
                    $msgContent .= "<h4 style='margin: 0; color: #0f172a; font-size: 0.95rem; font-weight: 800; letter-spacing: -0.01em;'>STOCK ENTRY APPROVAL</h4>";
                    $msgContent .= "<p style='margin: 0; font-size: 0.75rem; color: #64748b; font-weight: 600;'>Pending Strategic Entry Verification</p>";
                    $msgContent .= "</div></div>";
                    
                    $msgContent .= "<div id='sra-creation-actions-{$editReq->id}' style='display: flex; flex-direction: column; gap: 8px;'>";
                    $msgContent .= "<button data-entry-req-id='{$editReq->id}' class='entry-preview-btn' style='display: flex; align-items: center; justify-content: center; gap: 8px; background: #f8fafc; color: #0f172a; border: 1px solid #e2e8f0; padding: 12px; border-radius: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: 0.3s;'>";
                    $msgContent .= "<i data-lucide='eye' style='width: 16px;'></i> Preview Entry Details</button>";
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
                }

                // Send confirmation back to the user
                $firstAdmin = \App\Models\User::getApproversQuery()->where('registration_status', 'approved')->first();
                if ($firstAdmin) {
                    $confirmMsg = "<!-- sra_req_id:{$editReq->id} -->"
                        . "<div class='sra-awaiting-msg personnel-view' style='padding: 15px 18px; border: 1.5px solid #c7d2fe; border-radius: 16px; background: rgba(99,102,241,0.04); display: flex; align-items: center; gap: 12px;'>"
                        . "<div style='width: 36px; height: 36px; background: #6366f1; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;'>"
                        . "<svg style='width:18px;height:18px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>"
                        . "</div>"
                        . "<div>"
                        . "<b style='color: #4f46e5; font-size: 0.88rem; display: block; margin-bottom: 2px;'>ENTRY SUBMITTED FOR AUTHORIZATION</b>"
                        . "<span style='font-size: 0.78rem; color: #64748b; font-weight: 600;'>Awaiting Authorization — Your submission is pending review by the Admin.</span>"
                        . "</div></div>";

                    \App\Models\Message::create([
                        'sender_id'       => $firstAdmin->id,
                        'receiver_id'     => auth()->id(),
                        'message'         => $confirmMsg,
                        'is_automated'    => true,
                        'edit_request_id' => $editReq->id,
                    ]);
                }

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
                'delivery_person' => $validated['delivery_person'] ?? null,
                'delivery_phone' => $validated['delivery_phone'] ?? null,
                'driver_name' => $validated['driver_name'] ?? null,
                'driver_phone' => $validated['driver_phone'] ?? null,
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

            // Update the supplier's contact details in the database and registry
            if (!empty($validated['supplier_name'])) {
                $cleanSupplier = trim(preg_replace('/\s\[.*\]$/', '', $validated['supplier_name']));
                
                $updateData = [];
                if (isset($validated['delivery_person'])) {
                    $updateData['contact_person'] = trim($validated['delivery_person']);
                }
                if (isset($validated['driver_name'])) {
                    $updateData['delivery_person'] = trim($validated['driver_name']);
                }
                if (isset($validated['delivery_phone'])) {
                    $updateData['contact_phone'] = trim($validated['delivery_phone']);
                }
                if (isset($validated['driver_phone'])) {
                    $updateData['delivery_phone'] = trim($validated['driver_phone']);
                }
                if (isset($validated['supplier_phone'])) $updateData['phone'] = trim($validated['supplier_phone']);
                if (isset($validated['supplier_email'])) $updateData['email'] = trim($validated['supplier_email']);
                if (isset($validated['supplier_address'])) $updateData['address'] = trim($validated['supplier_address']);
                
                $supplierModel = \App\Models\Supplier::where('name', $cleanSupplier)
                    ->orWhere('name', $validated['supplier_name'])
                    ->first();
                    
                if ($supplierModel) {
                    if (!empty($updateData)) {
                        $supplierModel->update($updateData);
                    }
                } else {
                    $createData = array_merge(['name' => $cleanSupplier], $updateData);
                    \App\Models\Supplier::create($createData);
                }
                
                // Also update in settings registry
                $setting = \App\Models\Setting::where('key', 'suppliers_registry')->first();
                if ($setting) {
                    $registry = json_decode($setting->value ?? '{}', true) ?? [];
                    $found = false;
                    foreach ($registry as $key => &$details) {
                        if (strcasecmp(trim($key), $cleanSupplier) === 0 || strcasecmp(trim($key), trim($validated['supplier_name'])) === 0) {
                            foreach ($updateData as $k => $v) {
                                $details[$k] = $v;
                            }
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $registry[$cleanSupplier] = $updateData;
                    }
                    $setting->value = json_encode($registry);
                    $setting->save();
                }
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
                    'subtitle' => ($isDonor ? "Donor" : "Supplier") . " • {$catName} Source • Recorded " . date('d/m/y', strtotime($batch->entry_date)),
                    'url' => route('receiveditems', [$isDonor ? 'donor' : 'supplier' => $isDonor ? $batch->donor_name : $batch->supplier_name]),
                    'type' => 'source',
                    'icon' => $isDonor ? 'heart' : 'truck'
                ];
            });

        return response()->json($categoryResults->merge($items)->merge($batches));
    }

    public function lowStockMonitor()
    {
        \App\Models\InventoryBatch::selfHealSchema();

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

        $allItems = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->where('inventory_batches.approval_status', '=', 'approved')
            ->selectRaw('TRIM(inventory_items.description) as description, inventory_batches.ledge_category, SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as stock_balance, SUM(CAST(REPLACE(inventory_items.qty, ",", "") AS DECIMAL(15,2))) as qty')
            ->groupBy(\DB::raw('TRIM(inventory_items.description)'), 'inventory_batches.ledge_category')
            ->get();

        $lowStockItems = collect();
        foreach ($allItems as $item) {
            $threshold = \App\Models\Setting::getItemThreshold($item->description, $item->ledge_category);
            $currentStock = (float)$item->stock_balance;
            if ($threshold > 0 && $currentStock < $threshold) {
                $unit = \App\Models\Setting::getItemUnit($item->description);
                $lowStockItems->push((object)[
                    'description' => $item->description,
                    'ledge_category' => $item->ledge_category,
                    'category_name' => $ledgeMap[$item->ledge_category] ?? "Category {$item->ledge_category}",
                    'stock_balance' => $currentStock,
                    'threshold' => $threshold,
                    'unit' => $unit
                ]);
            }
        }

        $lowStockItems = $lowStockItems->sortBy('stock_balance')->values();

        return view('inventory.low_stock', compact('lowStockItems', 'ledgeMap'));
    }

    public function checkDuplicate(Request $request)
    {
        return response()->json(['duplicate' => false]);
    }
}
