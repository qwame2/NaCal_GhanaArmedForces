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
    public function createUser()
    {
        return redirect()->route('admin.index')->with('error', 'Strategic Oversight Alert: Personnel registration must be performed by the users themselves and approved by Admin.');
    }

    public function storeUser(Request $request)
    {
        return redirect()->route('admin.index')->with('error', 'Strategic Oversight Alert: Personnel registration must be performed by the users themselves and approved by Admin.');
    }

    public function index(Request $request)
    {
        if (auth()->user()->role === 'Main Admin') {
            return redirect()->route('main-admin.requisitions');
        }

        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access to Admin Dashboard.');
        }

        // Force synchronization of online status in the registry
        if (!auth()->user()->is_online) {
            auth()->user()->update(['is_online' => true]);
        }

        $perPage = $request->input('per_page', 10);
        $users = User::where('role', '!=', 'Head of Stores')
            ->where('registration_status', 'approved')
            ->paginate($perPage);

        $totalUsers = User::where('role', '!=', 'Head of Stores')
            ->where('registration_status', 'approved')
            ->count();

        $onlineCount = User::where('role', '!=', 'Head of Stores')
            ->where('registration_status', 'approved')
            ->where('is_online', true)
            ->count();

        $allUsers = User::where('registration_status', 'approved')->get(); // Keep for calculating global metrics if needed
        
        $recentLogins = User::where('role', '!=', 'Head of Stores')
            ->where('registration_status', 'approved')
            ->orderBy('last_login_at', 'desc')
            ->limit(100)
            ->get();
        
        // Fetch legacy/previous admin logs for auditing
        $legacyAdminLogs = \App\Models\SystemLog::with('user')
            ->whereHas('user', function($q) {
                $q->where('is_admin', true)
                  ->where('id', '!=', auth()->id())
                  ->where('is_active', false);
            })
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();

        $legacyAdmins = User::where('is_admin', true)
            ->where('id', '!=', auth()->id())
            ->where('is_active', false)
            ->get();

        $pendingUsers = User::where('registration_status', 'pending')->get();

        return view('admin.index', compact('users', 'totalUsers', 'allUsers', 'recentLogins', 'onlineCount', 'legacyAdminLogs', 'legacyAdmins', 'pendingUsers'));
    }

    public function approveRegistration(Request $request, $id)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'role' => 'required|string|in:Officer,Main Admin,Department Head,Auditor,Requisitioner,Director General',
        ]);

        $user = User::findOrFail($id);
        $role = $request->role;

        $user->role = $role;
        $user->registration_status = 'approved';
        $user->is_active = true;

        // Default attributes based on the assigned role:
        if ($role === 'Main Admin') {
            $user->department = 'Stores';
            $user->is_admin = true;
            $user->is_temp_account = false;
            $user->can_make_requisition = false;
            $user->can_approve_requisition = false;
        } elseif ($role === 'Auditor') {
            $user->department = 'Internal Audit';
            $user->is_admin = false;
            $user->is_temp_account = true;
            $user->can_make_requisition = false;
            $user->can_approve_requisition = false;
            $user->can_generate_reports = true;
        } elseif ($role === 'Officer') {
            $user->department = 'Stores';
            $user->is_admin = false;
            $user->is_temp_account = false;
            $user->can_add_inventory = false;
            $user->can_operate_logistics = false;
            $user->can_generate_reports = false;
            $user->can_verify_stock = false;
            $user->can_make_requisition = false;
            $user->can_approve_requisition = false;
        } elseif ($role === 'Requisitioner') {
            $user->is_admin = false;
            $user->is_temp_account = false;
            $user->can_make_requisition = true;
            $user->can_approve_requisition = false;
            
            // Find active/approved Department Head for Requisitioner's department
            $deptHead = User::where('role', 'Department Head')
                ->where('department', $user->department)
                ->where('is_active', true)
                ->where('registration_status', 'approved')
                ->first();
            if ($deptHead) {
                $user->sponsored_by = $deptHead->id;
            } else {
                $user->sponsored_by = null;
            }
        } elseif ($role === 'Director General') {
            $user->department = 'Executive Directorate';
            $user->is_admin = false;
            $user->is_temp_account = false;
            $user->can_generate_reports = true;
            $user->can_make_requisition = false;
            $user->can_approve_requisition = false;
        } else {
            // Department Head
            $user->is_admin = false;
            $user->is_temp_account = false;
            $user->can_make_requisition = false;
            $user->can_approve_requisition = true;
            $user->can_generate_reports = true;
        }

        $user->save();

        // Log the approval
        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'SECURITY',
            'action' => 'APPROVE_USER',
            'description' => "Administrator approved registration request for: {$user->name} (@{$user->username}) as {$user->role}.",
            'severity' => 'info',
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('admin.permissions')
            ->with('success', "Registration approved — {$user->name} ({$user->role}) is now active.")
            ->with('open_tab', 'registrations');
    }

    public function rejectRegistration(Request $request, $id)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $user = User::findOrFail($id);
        $name = $user->name;
        $username = $user->username;
        $user->update([
            'registration_status' => 'rejected',
            'is_active' => false,
        ]);

        // Log the rejection
        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'SECURITY',
            'action' => 'REJECT_USER',
            'description' => "Administrator rejected registration request for: {$name} (@{$username}).",
            'severity' => 'warning',
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('admin.permissions')
            ->with('success', "Registration request for {$name} (@{$username}) has been declined.")
            ->with('open_tab', 'registrations');
    }

    public function passwordRequests()
    {
        $requests = \App\Models\PasswordResetRequest::with('user')->orderBy('created_at', 'desc')->paginate(15);

        // Count how many total requests each username has ever submitted
        $requestCounts = \App\Models\PasswordResetRequest::selectRaw('username, COUNT(*) as total')
            ->groupBy('username')
            ->pluck('total', 'username');

        return view('admin.password_requests', compact('requests', 'requestCounts'));
    }

    public function approvePasswordRequest(Request $request, $id)
    {
        $resetReq = \App\Models\PasswordResetRequest::findOrFail($id);
        
        // Generate a random 6-digit OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Read expiry window from global settings (default: 1440 minutes = 24 hours)
        $expiryMinutes = (int) \App\Models\Setting::get('otp_expiry_minutes', 1440);
        
        $resetReq->update([
            'otp' => $otp,
            'status' => 'approved',
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'SECURITY',
            'action' => 'AUTHORIZATION',
            'description' => "Administrator approved password reset for @{$resetReq->username}.",
            'severity' => 'warning',
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Request for @{$resetReq->username} approved. The code is: {$otp}. It expires in {$expiryMinutes} minute(s). Please provide this to the staff member.");
    }

    public function rejectPasswordRequest(Request $request, $id)
    {
        $resetReq = \App\Models\PasswordResetRequest::findOrFail($id);
        $resetReq->update(['status' => 'rejected']);

        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'SECURITY',
            'action' => 'AUTHORIZATION',
            'description' => "Administrator rejected password reset request for @{$resetReq->username}.",
            'severity' => 'info',
            'ip_address' => $request->ip()
        ]);

        return back();
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

        // SECURITY ENFORCEMENT: Only one active Admin account allowed
        if ($request->role === 'Head of Stores' && !$user->is_admin) {
            if (User::where('is_admin', true)->where('is_active', true)->exists()) {
                return back()->with('error', 'Strategic Oversight Alert: Only one active Administrative account is permitted. Promotion denied.');
            }
        }

        $department = $request->department;
        if ($request->role === 'Main Admin') {
            $department = 'Stores';
        } elseif ($request->role === 'Officer') {
            $department = 'Stores';
        } elseif ($request->role === 'Director General') {
            $department = 'Executive Directorate';
        }

        $user->update([
            'name' => $request->name,
            'role' => $request->role,
            'department' => $department,
            'is_admin' => in_array($request->role, ['Head of Stores', 'Main Admin']),
            'is_temp_account' => $request->role === 'Auditor',
        ]);

        // Log the update
        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'SECURITY',
            'action' => 'UPDATE_USER',
            'description' => "Administrator updated details for staff member: {$user->name} (@{$user->username}). Role set to {$user->role}.",
            'severity' => 'info',
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', "Personnel registry for {$user->name} updated successfully.");
    }

    public function deactivateSelf(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        if (!$user->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Authentication Failure: Incorrect password provided for account deactivation.');
        }

        $user->is_active = false;
        $user->is_online = false;
        $user->save();

        \App\Models\SystemLog::create([
            'user_id' => $user->id,
            'event_type' => 'SECURITY',
            'action' => 'SELF_DEACTIVATION',
            'description' => "Administrator deactivated their own account: {$user->name} (@{$user->username}).",
            'severity' => 'critical',
            'ip_address' => request()->ip()
        ]);

        \Illuminate\Support\Facades\Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/login')->with('success', 'Your administrative account has been deactivated. Session ended.');
    }



    public function toggleUserStatus($id)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return back()->with('error', "You cannot deactivate your own account here.");
        }

        $user->is_active = !$user->is_active;
        if (!$user->is_active) {
            $user->is_online = false;
        } else {
            // If reactivating, clear their failed login attempts so they start fresh
            $throttleKey = strtolower($user->username) . '|' . request()->ip();
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            
            // Also clear for any IP if we want to be thorough, but we only have current IP here.
            // Usually username is enough if we use a different key format, but AuthController uses username|ip.
        }
        $user->save();

        $actionWord = $user->is_active ? 'reactivated' : 'deactivated';

        // Log the status change
        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'SECURITY',
            'action' => 'TOGGLE_USER_STATUS',
            'description' => "Administrator {$actionWord} account for: {$user->name} (@{$user->username}).",
            'severity' => $user->is_active ? 'info' : 'warning',
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', "Staff account has been {$actionWord} successfully.");
    }

    public function permissions()
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $storeOfficers = User::where('role', 'Officer')
            ->where('registration_status', 'approved')
            ->orderBy('name')
            ->get();

        $requisitioners = User::where('role', 'Requisitioner')
            ->where('registration_status', 'approved')
            ->orderBy('name')
            ->get();

        $deptHeads = User::whereIn('role', ['Main Admin', 'Department Head', 'Dept Head HR', 'Head of Welfare'])
            ->where('registration_status', 'approved')
            ->orderBy('name')
            ->get();

        $auditors = User::where('role', 'Auditor')
            ->where('registration_status', 'approved')
            ->orderBy('name')
            ->get();

        $directorGenerals = User::where('role', 'Director General')
            ->where('registration_status', 'approved')
            ->orderBy('name')
            ->get();

        $pendingUsers = User::where('registration_status', 'pending')->orderBy('created_at', 'desc')->get();
        return view('admin.permissions', compact('storeOfficers', 'requisitioners', 'deptHeads', 'auditors', 'directorGenerals', 'pendingUsers'));
    }

    public function getPendingRegistrations()
    {
        if (!auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $pendingUsers = User::where('registration_status', 'pending')->orderBy('created_at', 'desc')->get();
        $html = view('admin.partials.pending_registrations', compact('pendingUsers'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'count' => $pendingUsers->count()
        ]);
    }

    public function getStoreOfficers()
    {
        if (!auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $storeOfficers = User::where('role', 'Officer')
            ->where('registration_status', 'approved')
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'role', 'department', 'avatar',
                   'is_active', 'is_online',
                   'can_add_inventory', 'can_operate_logistics',
                   'can_generate_reports', 'can_verify_stock']);

        return response()->json([
            'success' => true,
            'users'   => $storeOfficers->map(fn($u) => [
                'id'                    => $u->id,
                'name'                  => $u->name,
                'username'              => $u->username,
                'role'                  => $u->role,
                'department'            => $u->department,
                'avatar'                => $u->avatar ? asset('storage/' . $u->avatar) : null,
                'is_active'             => (bool) $u->is_active,
                'is_online'             => (bool) $u->is_online,
                'can_add_inventory'     => (bool) $u->can_add_inventory,
                'can_operate_logistics' => (bool) $u->can_operate_logistics,
                'can_generate_reports'  => (bool) $u->can_generate_reports,
                'can_verify_stock'      => (bool) $u->can_verify_stock,
            ]),
        ]);
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
        $allowed = ['can_add_inventory', 'can_operate_logistics', 'can_generate_reports', 'can_verify_stock', 'can_make_requisition', 'can_approve_requisition'];
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
            'description' => "Administrator {$actionWord} [{$permLabel}] permission for staff member: {$user->name} (@{$user->username}).",
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

        // Enforce strict oversight: Show logs from Standard Personnel and System Auto (hide Admin actions and archived logs)
        $query = \App\Models\SystemLog::with('user')
            ->where('is_archived', false)
            ->where(function($q) {
                $q->whereHas('user', function($sq) {
                    $sq->where('is_admin', false);
                })->orWhereNull('user_id'); // Include automated system logs
            })
            ->where('description', 'NOT LIKE', '%/api/%');

        // Optional filtering
        if ($request->has('severity') && $request->severity) {
            $query->where('severity', $request->severity);
        }

        if ($request->has('event_type') && $request->event_type) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('authority')) {
            if ($request->authority === 'delegated') {
                $query->where('action', 'LIKE', 'DELEGATED_%');
            } elseif ($request->authority === 'standard') {
                $query->where('action', 'NOT LIKE', 'DELEGATED_%');
            }
        }

        $perPage = $request->input('per_page', 15);
        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Global Metrics for administrative transparency
        $activeCount = \App\Models\SystemLog::where('is_archived', false)->count();
        $archivedCount = \App\Models\SystemLog::where('is_archived', true)->count();
        
        return view('admin.logs', compact('logs', 'activeCount', 'archivedCount'));
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

        // Log this action to maintain audit integrity
        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'SECURITY',
            'action' => 'PURGE_SYSTEM_LOGS',
            'description' => "Administrator deleted {$count} system logs.",
            'severity' => 'danger',
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', "Successfully deleted {$count} system logs.");
    }

    public function messages()
    {
        if (!auth()->user()->is_admin && !auth()->user()->isDelegatedApprover()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $users = User::where('is_admin', false)->where('registration_status', 'approved')->get();
        return view('admin.messages', compact('users'));
    }

    public function viewInventory(Request $request)
    {
        try {
            \App\Http\Controllers\ReturnController::selfHealRequisitions();
            \App\Http\Controllers\StoreRequisitionController::checkOverdueTemporaryItems();
        } catch (\Exception $e) {
            // Keep page loading resilient
        }

        // Query builder for Received Items
        $query = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
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

        // Fetch aggregate totals for item status display (System Health metrics)
        $itemAggregates = \Illuminate\Support\Facades\Cache::remember('item_aggregates_list', 600, function() {
            return InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                ->where('inventory_batches.supplier_status', '!=', 'System Draft')
                ->where('inventory_batches.approval_status', '=', 'approved')
                ->selectRaw('inventory_items.description, SUM(inventory_items.qty) as total_received_qty, SUM(inventory_items.stock_balance) as total_available, SUM(inventory_items.variance) as total_variance')
                ->groupBy('inventory_items.description')
                ->get()
                ->keyBy('description');
        });

        $perPage = $request->input('per_page', 15);
        
        if ($request->has('stock_level') && in_array($request->stock_level, ['low', 'in_stock'])) {
            $allItems = $query->orderBy('inventory_batches.entry_date', 'desc')->get();
            $filtered = $allItems->filter(function($item) use ($itemAggregates, $request) {
                $agg = $itemAggregates[$item->description] ?? null;
                $totalStock = $agg ? (float)$agg->total_available : 0;
                $threshold = \App\Models\Setting::getItemThreshold($item->description, $item->ledge_category);
                $isItemLow = $totalStock <= $threshold;

                if ($request->stock_level === 'low') {
                    return $isItemLow;
                } else {
                    return !$isItemLow;
                }
            });

            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $currentItems = $filtered->slice(($currentPage - 1) * $perPage, $perPage)->values()->all();
            
            $receivedItems = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentItems,
                $filtered->count(),
                $perPage,
                $currentPage,
                ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
            );
            $receivedItems->appends($request->all());
        } else {
            $receivedItems = $query->orderBy('inventory_batches.entry_date', 'desc')->paginate($perPage);
        }


        // Fetch and filter Issuances in real time
        $issuancesQuery = IssuedItem::with('issuance')
            ->join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->select('issued_items.*', 'issuances.issuance_date', 'issuances.beneficiary', 'issuances.authority', 'issuances.issuance_type', 'issuances.created_at');

        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $issuancesQuery->where(function($q) use ($searchTerm) {
                $q->where('issued_items.description', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('issued_items.issuance_id', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('issuances.beneficiary', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('issuances.authority', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        if ($request->has('category') && $request->category) {
            $issuancesQuery->where('issued_items.ledge_category', $request->category);
        }

        if ($request->has('date_from') && $request->date_from) {
            $issuancesQuery->whereDate('issuances.issuance_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $issuancesQuery->whereDate('issuances.issuance_date', '<=', $request->date_to);
        }

        $issuances = $issuancesQuery->orderBy('issuances.created_at', 'desc')->get();

        // Fetch and filter Returns Registry in real time
        $returnedQuery = ReturnedItem::with(['issuedItem.issuance']);

        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $returnedQuery->where(function($q) use ($searchTerm) {
                $q->where('remarks', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhereHas('issuedItem', function($sq) use ($searchTerm) {
                      $sq->where('description', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhereHas('issuance', function($ssq) use ($searchTerm) {
                            $ssq->where('beneficiary', 'LIKE', '%' . $searchTerm . '%')
                              ->orWhere('authority', 'LIKE', '%' . $searchTerm . '%');
                        });
                  });
            });
        }

        if ($request->has('category') && $request->category) {
            $returnedQuery->whereHas('issuedItem', function($q) use ($request) {
                $q->where('ledge_category', $request->category);
            });
        }

        if ($request->has('date_from') && $request->date_from) {
            $returnedQuery->whereDate('return_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $returnedQuery->whereDate('return_date', '<=', $request->date_to);
        }

        $returnedItems = \Illuminate\Support\Facades\Schema::hasTable('returned_items')
            ? $returnedQuery->orderBy('return_date', 'desc')->get()
            : collect();

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

        $registryData = \App\Models\Setting::get('suppliers_registry', []);
        if (is_string($registryData)) {
            $registryData = json_decode($registryData, true) ?? [];
        }
        $registrySuppliers = is_array($registryData) ? array_keys($registryData) : [];
        $dbSuppliers = InventoryBatch::where('acquisition_type', 'Supplier')
            ->whereNotNull('supplier_name')
            ->where('supplier_name', '!=', '')
            ->distinct()
            ->pluck('supplier_name')
            ->map(function($name) use ($registrySuppliers) {
                $clean = preg_replace('/\s\[.*\]$/', '', $name);
                foreach ($registrySuppliers as $regName) {
                    if (strcasecmp($regName, $clean) === 0) {
                        return $regName;
                    }
                }
                return $clean;
            })->toArray();
        $allSuppliers = collect(array_merge($registrySuppliers, $dbSuppliers))
            ->filter(function ($item) {
                return strtolower(trim($item)) !== 'system';
            })
            ->unique(function ($item) {
                return strtolower(trim($item));
            })
            ->values();

        $donorNames1 = InventoryBatch::where('acquisition_type', 'Donor')
            ->whereNotNull('donor_name')
            ->where('donor_name', '!=', '')
            ->distinct()
            ->pluck('donor_name');

        $donorNames2 = InventoryBatch::where('acquisition_type', 'Donor')
            ->whereNotNull('supplier_name')
            ->where('supplier_name', '!=', '')
            ->distinct()
            ->pluck('supplier_name');

        $allDonors = $donorNames1->concat($donorNames2)->filter()->unique()->values();

        // Compute Low Stock Items for the monitor card
        $lowStockItems = \Illuminate\Support\Facades\Cache::remember('low_stock_items_list', 600, function() {
            $allItemAggregates = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                ->where('inventory_batches.supplier_status', '!=', 'System Draft')
                ->where('inventory_batches.approval_status', '=', 'approved')
                ->selectRaw('inventory_items.description, inventory_batches.ledge_category, SUM(inventory_items.stock_balance) as total_available')
                ->groupBy('inventory_items.description', 'inventory_batches.ledge_category')
                ->get();

            return $allItemAggregates->filter(function ($item) {
                $threshold = \App\Models\Setting::getItemThreshold($item->description, $item->ledge_category);
                return (float) $item->total_available <= $threshold;
            })->sortBy('total_available')->values();
        });

        return view('admin.inventory.index', compact('receivedItems', 'partialCount', 'itemAggregates', 'issuances', 'returnedItems', 'ledgeMap', 'allSuppliers', 'allDonors', 'lowStockItems'));
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

        $itemsByCategory = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->select('inventory_items.description', 'inventory_batches.ledge_category')
            ->distinct()
            ->get()
            ->groupBy('ledge_category')
            ->map(function($items) {
                return $items->pluck('description')->unique()->values();
            });

        // Build a map of item description → total stock balance for the request-limit form hint
        $stockByKeyword = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->where('inventory_batches.supplier_status', '!=', 'System Draft')
            ->selectRaw('inventory_items.description, SUM(inventory_items.stock_balance) as total_stock')
            ->groupBy('inventory_items.description')
            ->get()
            ->mapWithKeys(function ($item) {
                return [strtolower(trim($item->description)) => (float) $item->total_stock];
            });

        $storeOfficers = \App\Models\User::where('role', 'Officer')
            ->where('is_active', true)
            ->where('registration_status', 'approved')
            ->orderBy('name')
            ->get();

        return view('admin.settings', compact('settings', 'categories', 'itemsByCategory', 'stockByKeyword', 'storeOfficers'));
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
                $oldValue = $setting->value;

                // If the setting is boolean, handle checkbox uncheck logic
                if ($setting->type === 'boolean') {
                    $setting->value = $value ? 'true' : 'false';
                } elseif (is_array($value)) {
                    $setting->value = json_encode($value);
                } else {
                    $setting->value = $value;
                }
                $setting->save();

                // Log delegation activations or revocations
                if ($key === 'delegated_approver_id' && (string)$oldValue !== (string)$value) {
                    if (!empty($value)) {
                        $officer = \App\Models\User::find($value);
                        $officerName = $officer ? $officer->name : "ID {$value}";
                        \App\Models\SystemLog::create([
                            'user_id' => auth()->id(),
                            'event_type' => 'SECURITY',
                            'action' => 'DELEGATE_AUTHORITY',
                            'description' => "Administrator delegated approval authority to Store Officer: {$officerName}.",
                            'severity' => 'warning',
                            'ip_address' => request()->ip()
                        ]);
                    } else {
                        $oldOfficer = \App\Models\User::find($oldValue);
                        $oldOfficerName = $oldOfficer ? $oldOfficer->name : "ID {$oldValue}";
                        \App\Models\SystemLog::create([
                            'user_id' => auth()->id(),
                            'event_type' => 'SECURITY',
                            'action' => 'REVOKE_DELEGATION',
                            'description' => "Administrator revoked delegated approval authority from Store Officer: {$oldOfficerName}.",
                            'severity' => 'warning',
                            'ip_address' => request()->ip()
                        ]);
                    }
                }
            }
        }

        // Handle unchecked checkboxes which don't send any POST data
        // Only target settings that are actually visible on this page (not 'ui' group and not in exclusions)
        if ($request->has('settings_form')) {
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

            // Handle stores_dept_head_approval_categories multi-select clear
            if (!$request->has('stores_dept_head_approval_categories')) {
                $catSetting = \App\Models\Setting::where('key', 'stores_dept_head_approval_categories')->first();
                if ($catSetting) {
                    $catSetting->value = json_encode([]);
                    $catSetting->save();
                }
            }

            // Handle dg_approval_categories multi-select clear
            if (!$request->has('dg_approval_categories')) {
                $dgCatSetting = \App\Models\Setting::where('key', 'dg_approval_categories')->first();
                if ($dgCatSetting) {
                    $dgCatSetting->value = json_encode([]);
                    $dgCatSetting->save();
                }
            }
        }

        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'SECURITY',
            'action' => 'UPDATE_SETTINGS',
            'description' => "Administrator updated system settings.",
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
            'description' => "Administrator added a new category: {$name} ({$code}).",
            'severity' => 'info',
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'New category added successfully.');
    }

    public function updateCategory(\Illuminate\Http\Request $request, $code)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'category_name' => 'required|string|max:100'
        ]);

        $code = strtoupper($code);
        $name = $request->category_name;
        $categories = \App\Models\Setting::getCategories();

        if (!array_key_exists($code, $categories)) {
            return back()->with('error', "Category '{$code}' not found.");
        }

        // Validate that the category name is not already in use by a DIFFERENT category code
        foreach ($categories as $existingCode => $existingName) {
            if ($existingCode !== $code && strtolower($existingName) === strtolower($name)) {
                return back()->with('error', "Category Name '{$name}' is already in use by code '{$existingCode}'.");
            }
        }

        \App\Models\Setting::addCategory($code, $name);

        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'INVENTORY',
            'action' => 'UPDATE_CATEGORY',
            'description' => "Administrator updated category '{$code}' name to: {$name}.",
            'severity' => 'info',
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Category updated successfully.');
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

    public function auditLog(Request $request)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $query = \App\Models\SystemLog::with('user');

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $auditLog = $query->orderBy('created_at', 'desc')->paginate(15);
        $users = User::all();

        return view('admin.audit_log', compact('auditLog', 'users'));
    }

    public function dataHistory(Request $request)
    {
        if (!auth()->user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        // Fetch Stock History
        $stockQuery = \App\Models\StockHistory::with(['inventoryItem', 'user']);
        if ($request->has('user_id') && $request->user_id) {
            $stockQuery->where('user_id', $request->user_id);
        }
        if ($request->has('date_from') && $request->date_from) {
            $stockQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $stockQuery->whereDate('created_at', '<=', $request->date_to);
        }
        $stockHistory = $stockQuery->orderBy('created_at', 'desc')->paginate(15, ['*'], 'stock_page');

        // Fetch User Role History
        $roleQuery = \App\Models\UserRoleHistory::with(['user', 'changer']);
        if ($request->has('user_id') && $request->user_id) {
            $roleQuery->where(function($q) use ($request) {
                $q->where('user_id', $request->user_id)
                  ->orWhere('changed_by', $request->user_id);
            });
        }
        if ($request->has('date_from') && $request->date_from) {
            $roleQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $roleQuery->whereDate('created_at', '<=', $request->date_to);
        }
        $roleHistory = $roleQuery->orderBy('created_at', 'desc')->paginate(15, ['*'], 'role_page');

        $users = User::all();

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

        return view('admin.data_history', compact('stockHistory', 'roleHistory', 'users', 'ledgeMap'));
    }

    public function itemHistory($id)
    {
        if (!auth()->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $history = \App\Models\StockHistory::with('user')
            ->where('inventory_item_id', $id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($record) {
                return [
                    'id' => $record->id,
                    'action' => ucfirst($record->action),
                    'user_name' => $record->user->name ?? 'System',
                    'date' => $record->created_at->format('d/m/y @ h:i A'),
                    'old_description' => $record->old_description,
                    'new_description' => $record->new_description,
                    'old_unit' => $record->old_unit,
                    'new_unit' => $record->new_unit,
                    'old_qty' => $record->old_qty,
                    'new_qty' => $record->new_qty,
                    'old_stock_balance' => $record->old_stock_balance,
                    'new_stock_balance' => $record->new_stock_balance,
                    'old_variance' => $record->old_variance,
                    'new_variance' => $record->new_variance,
                ];
            });

        return response()->json(['success' => true, 'history' => $history]);
    }

    public function suppliers()
    {
        if (!auth()->user()->is_admin && !auth()->user()->isDelegatedApprover() && strcasecmp(auth()->user()->department ?? '', 'Stores') !== 0 && strcasecmp(auth()->user()->department ?? '', 'Store') !== 0) {
            abort(403);
        }
        $suppliersRegistry = \App\Models\Setting::get('suppliers_registry', []);
        
        // Populate stats for each supplier
        foreach ($suppliersRegistry as $name => &$details) {
            $cleanName = trim(preg_replace('/\[.*?\]/', '', $name));
            $batches = \App\Models\InventoryBatch::where(function($q) use ($name, $cleanName) {
                    $q->where('supplier_name', $name)->orWhere('supplier_name', $cleanName);
                })
                ->where('supplier_status', '!=', 'System Draft')
                ->whereNotNull('arrival_date')
                ->orderBy('arrival_date', 'asc')
                ->get();

            $details['total_deliveries'] = $batches->count();
            $details['first_delivery'] = $batches->first() ? \Carbon\Carbon::parse($batches->first()->arrival_date)->format('Y-m-d') : null;
            $details['last_delivery'] = $batches->last() ? \Carbon\Carbon::parse($batches->last()->arrival_date)->format('Y-m-d') : null;
            $details['all_deliveries'] = $batches->pluck('arrival_date')->map(function($d) {
                return \Carbon\Carbon::parse($d)->format('Y-m-d');
            })->toArray();
        }
        
        return view('admin.suppliers', compact('suppliersRegistry'));
    }
}
