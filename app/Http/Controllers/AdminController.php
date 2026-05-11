<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\InventoryBatch;
use App\Models\InventoryItem;
use App\Models\Issuance;
use App\Models\IssuedItem;
use App\Models\ReturnedItem;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access to Command Center.');
        }

        $perPage = $request->input('per_page', 10);
        $users = User::where('is_admin', false)->paginate($perPage);
        $totalUsers = User::where('is_admin', false)->count();
        $onlineCount = User::where('is_admin', false)->where('is_online', true)->count();
        $allUsers = User::all(); // Keep for calculating global metrics if needed
        $recentLogins = User::where('is_admin', false)->orderBy('last_login_at', 'desc')->limit(100)->get();
        
        return view('admin.index', compact('users', 'totalUsers', 'allUsers', 'recentLogins', 'onlineCount'));
    }

    public function updateUser(Request $request, $id)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string',
            'department' => 'nullable|string',
        ]);

        $user->update([
            'name' => $request->name,
            'role' => $request->role,
            'department' => $request->department,
            'is_admin' => $request->role === 'Admin',
        ]);

        // Log the update
        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'SECURITY',
            'action' => 'UPDATE_USER',
            'description' => "Personnel updated registry for personnel: {$user->name} (@{$user->username}). Role set to {$user->role}.",
            'severity' => 'info',
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', "Personnel registry for {$user->name} updated successfully.");
    }

    public function toggleUserStatus($id)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return back()->with('error', "Self-deactivation of admin session is prohibited.");
        }

        $user->is_active = !$user->is_active;
        if (!$user->is_active) {
            $user->is_online = false;
        }
        $user->save();

        $actionWord = $user->is_active ? 'reactivated' : 'deactivated';

        // Log the status change
        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'SECURITY',
            'action' => 'TOGGLE_USER_STATUS',
            'description' => "Personnel {$actionWord} registry for: {$user->name} (@{$user->username}).",
            'severity' => $user->is_active ? 'info' : 'warning',
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', "Personnel account has been {$actionWord} successfully.");
    }

    public function permissions()
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $users = User::where('is_admin', false)->get();
        return view('admin.permissions', compact('users'));
    }

    public function updatePermission(Request $request)
    {
        if (!auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|string',
            'value' => 'required|boolean'
        ]);

        $user = User::findOrFail($request->user_id);
        $field = $request->permission;
        
        // Ensure the field is one of our allowed permission columns
        $allowed = ['can_add_inventory', 'can_operate_logistics', 'can_generate_reports'];
        if (!in_array($field, $allowed)) {
            return response()->json(['success' => false, 'message' => 'Invalid permission field'], 400);
        }

        $user->$field = $request->value;
        $user->save();

        $actionWord = $request->value ? 'GRANTED' : 'REVOKED';
        $permLabel = str_replace('_', ' ', str_replace('can_', '', $field));

        // Log the change
        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'SECURITY',
            'action' => 'PERMISSION_CHANGE',
            'description' => "Administrator {$actionWord} [{$permLabel}] permission for personnel: {$user->name} (@{$user->username}).",
            'severity' => 'warning',
            'ip_address' => $request->ip()
        ]);

        return response()->json(['success' => true, 'message' => 'Permission updated']);
    }

    public function logs(Request $request)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        // Enforce strict oversight: Only show logs from Standard Personnel (hide Admin actions)
        $query = \App\Models\SystemLog::with('user')->whereHas('user', function($q) {
            $q->where('is_admin', false);
        });

        // Optional filtering
        if ($request->has('severity') && $request->severity) {
            $query->where('severity', $request->severity);
        }

        if ($request->has('event_type') && $request->event_type) {
            $query->where('event_type', $request->event_type);
        }

        $perPage = $request->input('per_page', 15);
        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return view('admin.logs', compact('logs'));
    }

    public function destroyMultipleLogs(Request $request)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $logIds = $request->input('log_ids', []);
        
        if (empty($logIds)) {
            return back()->with('error', 'No logs were selected for deletion.');
        }

        $count = \App\Models\SystemLog::whereIn('id', $logIds)->count();
        \App\Models\SystemLog::whereIn('id', $logIds)->delete();

        // Log this destructive action to maintain audit integrity
        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'SECURITY',
            'action' => 'PURGE_SYSTEM_LOGS',
            'description' => "Personnel permanently purged {$count} system audit logs.",
            'severity' => 'danger',
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', "Successfully purged {$count} system audit logs.");
    }

    public function messages()
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $users = User::where('is_admin', false)->get();
        return view('admin.messages', compact('users'));
    }

    public function viewInventory(Request $request)
    {
        // Query builder for Received Items
        $query = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->select(
                'inventory_items.*', 
                'inventory_batches.entry_date', 
                'inventory_batches.arrival_date', 
                'inventory_batches.ledge_category', 
                'inventory_batches.supplier_name', 
                'inventory_batches.supplier_status', 
                'inventory_batches.donor_name', 
                'inventory_batches.acquisition_type'
            );

        // Apply Search Filter
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('inventory_items.description', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('inventory_items.batch_id', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Apply Category Filter
        if ($request->has('category') && $request->category) {
            $query->where('inventory_batches.ledge_category', $request->category);
        }

        // Apply Date Range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('inventory_batches.entry_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('inventory_batches.entry_date', '<=', $request->date_to);
        }

        $partialQuery = clone $query;
        $partialCount = $partialQuery->where('inventory_batches.supplier_name', 'LIKE', '%[Partial Deliv%')->count();

        $perPage = $request->input('per_page', 15);
        $receivedItems = $query->orderBy('inventory_batches.entry_date', 'desc')->paginate($perPage);

        // Fetch aggregate totals for item status display (System Health metrics)
        $itemAggregates = InventoryItem::selectRaw('description, SUM(qty) as total_received_qty, SUM(stock_balance) as total_available, SUM(variance) as total_variance')
            ->groupBy('description')
            ->get()
            ->keyBy('description');

        $issuances = IssuedItem::with('issuance')
            ->join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->select('issued_items.*', 'issuances.issuance_date', 'issuances.beneficiary', 'issuances.authority', 'issuances.issuance_type', 'issuances.created_at')
            ->orderBy('issuances.created_at', 'desc')
            ->get();

        $returnedItems = ReturnedItem::with(['issuedItem.issuance'])->orderBy('return_date', 'desc')->get();

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

        return view('admin.inventory.index', compact('receivedItems', 'partialCount', 'itemAggregates', 'issuances', 'returnedItems', 'ledgeMap'));
    }

    public function settings()
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        // We check if the table exists to prevent crash before migration
        $settings = \Illuminate\Support\Facades\Schema::hasTable('settings') 
            ? \App\Models\Setting::where('group', '!=', 'ui')
                ->whereNotIn('key', [
                    'system_maintenance_mode',
                    'inventory_categories',
                    'require_issuance_approval',
                    'inventory_valuation_method',
                    'allow_negative_stock',
                    'enable_batch_expiration_alerts',
                    'notify_on_low_stock'
                ])
                ->get()
                ->groupBy('group') 
            : collect([]);

        $categories = \Illuminate\Support\Facades\Schema::hasTable('settings') 
            ? \App\Models\Setting::getCategories() 
            : [];

        return view('admin.settings', compact('settings', 'categories'));
    }

    public function updateSettings(\Illuminate\Http\Request $request)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $inputs = $request->except('_token');

        foreach ($inputs as $key => $value) {
            $setting = \App\Models\Setting::where('key', $key)->first();
            if ($setting) {
                // If the setting is boolean, handle checkbox uncheck logic
                if ($setting->type === 'boolean') {
                    $setting->value = $value ? 'true' : 'false';
                } else {
                    $setting->value = $value;
                }
                $setting->save();
            }
        }

        // Handle unchecked checkboxes which don't send any POST data
        // Only target settings that are actually visible on this page (not 'ui' group and not in exclusions)
        $booleanSettings = \App\Models\Setting::where('type', 'boolean')
            ->where('group', '!=', 'ui')
            ->whereNotIn('key', [
                'system_maintenance_mode',
                'inventory_categories',
                'require_issuance_approval',
                'inventory_valuation_method',
                'allow_negative_stock',
                'enable_batch_expiration_alerts',
                'notify_on_low_stock'
            ])
            ->get();
        foreach ($booleanSettings as $boolSetting) {
            if (!$request->has($boolSetting->key)) {
                $boolSetting->value = 'false';
                $boolSetting->save();
            }
        }

        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'SECURITY',
            'action' => 'UPDATE_SETTINGS',
            'description' => "Administrator updated the global system settings.",
            'severity' => 'warning',
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Global settings updated successfully.');
    }

    public function addCategory(\Illuminate\Http\Request $request)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'category_code' => 'required|string|max:10',
            'category_name' => 'required|string|max:100'
        ]);

        $code = strtoupper($request->category_code);
        $name = $request->category_name;
        $existingCategories = \App\Models\Setting::getCategories();

        if (array_key_exists($code, $existingCategories)) {
            return back()->with('error', "Category Code '{$code}' already exists.");
        }

        if (in_array(strtolower($name), array_map('strtolower', $existingCategories))) {
            return back()->with('error', "Category Name '{$name}' is already in use.");
        }

        \App\Models\Setting::addCategory($code, $name);

        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'INVENTORY',
            'action' => 'ADD_CATEGORY',
            'description' => "Administrator added a new inventory category: {$name} ({$code}).",
            'severity' => 'info',
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'New category introduced successfully.');
    }

    public function deleteCategory($code)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $code = strtoupper($code);
        $categories = \App\Models\Setting::getCategories();

        if (!array_key_exists($code, $categories)) {
            return back()->with('error', "Category '{$code}' not found.");
        }

        $name = $categories[$code];
        \App\Models\Setting::removeCategory($code);

        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'INVENTORY',
            'action' => 'DELETE_CATEGORY',
            'description' => "Administrator deleted inventory category: {$name} ({$code}).",
            'severity' => 'warning',
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', "Category '{$code}' deleted successfully.");
    }
}
