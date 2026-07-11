<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceSra;
use App\Models\ServiceSraSupplier;
use App\Models\User;
use App\Models\Setting;
use App\Models\SystemLog;

class ServiceSraController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // STORE OFFICER: Create form
    // ─────────────────────────────────────────────────────────────────────────

    public function create()
    {
        $user = auth()->user();

        // Fetch ONLY from the dedicated service SRA suppliers table
        $allSuppliers = ServiceSraSupplier::getActiveList();

        $region = Setting::get('organization_region', 'Greater Accra');

        return view('service-sra.create', compact('user', 'allSuppliers', 'region'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE OFFICER: Submit form
    // ─────────────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'supplier_name'    => 'required|string|max:255',
            'delivery_type'    => 'required|in:full,partial',
            'details'          => 'required|string',
            'date_of_delivery' => 'required|date',
        ]);

        $user = auth()->user();
        $supplierName = trim($request->supplier_name);

        // Auto-save new supplier to the service SRA suppliers table if not already there
        ServiceSraSupplier::firstOrCreate(
            ['name' => $supplierName],
            [
                'address'        => trim($request->supplier_address ?? ''),
                'is_active'      => true,
                'created_by'     => $user->id,
            ]
        );

        $sra = ServiceSra::create([
            'submitted_by'      => $user->id,
            'dept'              => $user->department ?? '',
            'station'           => Setting::get('organization_station', 'Accra'),
            'region'            => Setting::get('organization_region', 'Greater Accra'),
            'date_of_delivery'  => $request->date_of_delivery,
            'supplier_name'     => $supplierName,
            'vehicle_number'    => trim($request->vehicle_number ?? ''),
            'ae_number'         => trim($request->ae_number ?? ''),
            'lpo_number'        => trim($request->lpo_number ?? ''),
            'supplier_address'  => trim($request->supplier_address ?? ''),
            'delivery_type'     => $request->delivery_type,
            'previous_sra_nos'  => $request->delivery_type === 'partial' ? trim($request->previous_sra_nos ?? '') : null,
            'details'           => trim($request->details),
            'status'            => 'pending',
            'admin_status'      => 'pending',
            'stores_status'     => 'pending',
        ]);

        // System log
        SystemLog::create([
            'user_id'     => $user->id,
            'event_type'  => 'SERVICE_SRA',
            'action'      => 'Service SRA Submitted',
            'description' => "Store Officer {$user->name} submitted SRA {$sra->sra_number} for supplier: {$sra->supplier_name}.",
            'severity'    => 'info',
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'success'    => true,
            'message'    => 'SRA submitted successfully and is awaiting Admin approval.',
            'sra_number' => $sra->sra_number,
            'redirect'   => route('service-sra.index'),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // API: Fetch supplier address when a supplier is selected
    // ─────────────────────────────────────────────────────────────────────────

    public function supplierInfo(Request $request)
    {
        $supplier = ServiceSraSupplier::where('name', $request->name)
            ->where('is_active', true)
            ->first();

        if (!$supplier) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'   => true,
            'address' => $supplier->address ?? '',
            'phone'   => $supplier->phone ?? '',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE OFFICER: My SRAs list
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = ServiceSra::where('submitted_by', auth()->id());

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function($q) use ($search) {
                $q->where('sra_number', 'LIKE', '%' . $search . '%')
                  ->orWhere('supplier_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('details', 'LIKE', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('delivery_type')) {
            $query->where('delivery_type', $request->input('delivery_type'));
        }

        $sras = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('service-sra._sra_table', compact('sras'))->render();
        }

        return view('service-sra.index', compact('sras'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE OFFICER: Download printable receipt (only when fully approved)
    // ─────────────────────────────────────────────────────────────────────────

    public function receipt($id)
    {
        $sra = ServiceSra::with('submitter')->findOrFail($id);

        // Gate: only the submitter OR admin/stores staff can view
        $user = auth()->user();
        $isAdmin = $user->is_admin 
            || $user->role === 'Main Admin' 
            || $user->role === 'Head of Stores' 
            || in_array(strtoupper($user->department ?? ''), ['STORES', 'STORE']);
        if (!$isAdmin && $sra->submitted_by !== $user->id) {
            abort(403, 'Unauthorized');
        }

        // Gate: receipt only available when fully approved
        if ($sra->status !== 'approved') {
            return redirect()->route('service-sra.index')
                ->with('warning', 'The SRA receipt is only available after full approval by the Head of Stores.');
        }

        $orgName = Setting::get('organization_name', 'NACOC');

        return view('service-sra.receipt', compact('sra', 'orgName'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HEAD OF ADMIN: Approval queue
    // ─────────────────────────────────────────────────────────────────────────

    public function adminIndex()
    {
        $user = auth()->user();
        if (!$user->is_admin && $user->role !== 'Main Admin') {
            abort(403);
        }

        $pending = ServiceSra::with('submitter')
            ->where('admin_status', 'pending')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $history = ServiceSra::with('submitter')
            ->whereIn('admin_status', ['approved', 'declined'])
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        return view('service-sra.admin', compact('pending', 'history'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HEAD OF ADMIN: Process (approve/decline)
    // ─────────────────────────────────────────────────────────────────────────

    public function adminProcess(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->is_admin && $user->role !== 'Main Admin') {
            abort(403);
        }

        $request->validate([
            'action' => 'required|in:approved,declined',
            'notes'  => 'nullable|string|max:1000',
        ]);

        $sra = ServiceSra::findOrFail($id);

        if ($sra->admin_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This SRA has already been processed.'], 422);
        }

        $sra->admin_status      = $request->action;
        $sra->admin_approved_by = $user->name;
        $sra->admin_approved_at = now();
        $sra->admin_notes       = $request->notes;

        if ($request->action === 'approved') {
            $sra->status = 'auditor_pending'; // goes to Auditor next
        } else {
            $sra->status = 'declined';
        }

        $sra->save();

        SystemLog::create([
            'user_id'     => $user->id,
            'event_type'  => 'SERVICE_SRA',
            'action'      => 'Service SRA Admin ' . ucfirst($request->action),
            'description' => "Admin {$user->name} " . ($request->action === 'approved' ? 'approved' : 'declined') . " SRA {$sra->sra_number}.",
            'severity'    => 'info',
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SRA has been ' . $request->action . ' successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HEAD OF STORES: Final approval queue
    // ─────────────────────────────────────────────────────────────────────────

    public function storesIndex()
    {
        $user = auth()->user();
        $isStores = $user->is_admin
            || $user->role === 'Main Admin'
            || $user->role === 'Head of Stores'
            || $user->role === 'Dept. Head (Stores)';

        if (!$isStores) {
            abort(403);
        }

        $pending = ServiceSra::with('submitter')
            ->where('status', 'admin_approved')
            ->where('stores_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $history = ServiceSra::with('submitter')
            ->whereIn('stores_status', ['approved', 'declined'])
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        return view('service-sra.stores', compact('pending', 'history'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HEAD OF STORES: Final process (approve/decline)
    // ─────────────────────────────────────────────────────────────────────────

    public function storesProcess(Request $request, $id)
    {
        $user = auth()->user();
        $isStores = $user->is_admin
            || $user->role === 'Main Admin'
            || $user->role === 'Head of Stores'
            || $user->role === 'Dept. Head (Stores)';

        if (!$isStores) {
            abort(403);
        }

        $request->validate([
            'action' => 'required|in:approved,declined',
            'notes'  => 'nullable|string|max:1000',
        ]);

        $sra = ServiceSra::findOrFail($id);

        if ($sra->status !== 'admin_approved') {
            return response()->json(['success' => false, 'message' => 'This SRA has not been admin-approved yet or has already been processed.'], 422);
        }

        $sra->stores_status      = $request->action;
        $sra->stores_approved_by = $user->name;
        $sra->stores_approved_at = now();
        $sra->stores_notes       = $request->notes;

        $sra->status = $request->action === 'approved' ? 'approved' : 'declined';
        $sra->save();

        SystemLog::create([
            'user_id'     => $user->id,
            'event_type'  => 'SERVICE_SRA',
            'action'      => 'Service SRA Stores ' . ucfirst($request->action),
            'description' => "Head of Stores {$user->name} " . ($request->action === 'approved' ? 'approved' : 'declined') . " SRA {$sra->sra_number}.",
            'severity'    => 'info',
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SRA has been ' . ($request->action === 'approved' ? 'fully approved. The Store Officer can now download the receipt.' : 'declined.'),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AUDITOR: Review queue
    // ─────────────────────────────────────────────────────────────────────────

    public function auditorIndex()
    {
        $user = auth()->user();
        if ($user->role !== 'Auditor') {
            abort(403);
        }

        $pending = ServiceSra::with('submitter')
            ->where('status', 'auditor_pending')
            ->where('auditor_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $history = ServiceSra::with('submitter')
            ->whereIn('auditor_status', ['approved', 'declined'])
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        return view('service-sra.auditor', compact('pending', 'history'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AUDITOR: Process (approve/decline)
    // ─────────────────────────────────────────────────────────────────────────

    public function auditorProcess(Request $request, $id)
    {
        $user = auth()->user();
        if ($user->role !== 'Auditor') {
            abort(403);
        }

        $request->validate([
            'action' => 'required|in:approved,declined',
            'notes'  => 'nullable|string|max:1000',
        ]);

        $sra = ServiceSra::findOrFail($id);

        if ($sra->status !== 'auditor_pending' || $sra->auditor_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This SRA has already been processed by the Auditor.'], 422);
        }

        $sra->auditor_status      = $request->action;
        $sra->auditor_approved_by = $user->name;
        $sra->auditor_approved_at = now();
        $sra->auditor_notes       = $request->notes;

        if ($request->action === 'approved') {
            $sra->status = 'admin_approved'; // now goes to Head of Stores
        } else {
            $sra->status = 'declined';
        }

        $sra->save();

        SystemLog::create([
            'user_id'     => $user->id,
            'event_type'  => 'SERVICE_SRA',
            'action'      => 'Service SRA Auditor ' . ucfirst($request->action),
            'description' => "Auditor {$user->name} " . ($request->action === 'approved' ? 'approved' : 'declined') . " SRA {$sra->sra_number} for Stores review.",
            'severity'    => 'info',
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service SRA has been ' . ($request->action === 'approved' ? 'auditor-approved and forwarded to Head of Stores.' : 'declined.'),
        ]);
    }

    public function showApi($id)
    {
        $sra = ServiceSra::with('submitter')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data'    => $sra,
        ]);
    }
}
