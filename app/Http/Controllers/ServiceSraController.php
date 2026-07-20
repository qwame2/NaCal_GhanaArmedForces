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

        // Notify Authorizers (Main Admin, Sub Main Admin, Head of Stores)
        $authorizers = User::where(function($q) {
            $q->where('is_admin', true)
              ->orWhereIn('role', ['Main Admin', 'Sub Main Admin', 'Head of Stores', 'Dept. Head (Stores)']);
        })->where('is_active', true)->get();

        $reviewLink = route('admin.service-sra.index');
        $msg  = "<div class='admin-view requisition-msg' style='padding:15px;border:1px solid #16a34a;border-radius:12px;background:rgba(22,163,74,0.05);'>";
        $msg .= "<b style='color:#15803d;'>📦 NEW SERVICE SRA SUBMITTED — Ref: #{$sra->sra_number}</b><br><br>";
        $msg .= "Store Officer <b>" . e($user->name) . "</b> has submitted a new Service Stock Received Advice (SRA) for Supplier: <b>" . e($sra->supplier_name) . "</b>.<br><br>";
        $msg .= "<b>Delivery Date:</b> " . e(\Carbon\Carbon::parse($sra->date_of_delivery)->format('d M Y')) . "<br>";
        $msg .= "<b>Details:</b> " . e($sra->details) . "<br><br>";
        $msg .= "<a href='" . $reviewLink . "' style='display:inline-block;background:#16a34a;color:white;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:800;font-size:0.85rem;'>Review & Approve Service SRA</a>";
        $msg .= "</div>";

        foreach ($authorizers as $auth) {
            \App\Models\Message::create([
                'sender_id'    => $user->id,
                'receiver_id'  => $auth->id,
                'message'      => $msg,
                'is_automated' => true,
            ]);
        }

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
    // RECEIPT PREVIEW (for review before approval — no status gate)
    // ─────────────────────────────────────────────────────────────────────────

    public function receiptPreview($id)
    {
        $sra = ServiceSra::with('submitter')->findOrFail($id);

        // Gate: only admin/stores/auditor roles can preview
        $user = auth()->user();
        $allowed = $user->is_admin
            || $user->isMainAdminOrSub()
            || $user->role === 'Head of Stores'
            || $user->role === 'Auditor'
            || in_array(strtoupper($user->department ?? ''), ['STORES', 'STORE'])
            || $user->isDelegatedApprover()
            || $sra->submitted_by === $user->id;

        if (!$allowed) {
            abort(403, 'Unauthorized');
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
        $isAuthorizer = $user->is_admin 
            || $user->isMainAdminOrSub() 
            || in_array($user->role, ['Main Admin', 'Sub Main Admin', 'Head of Stores', 'Dept. Head (Stores)']) 
            || $user->isDelegatedApprover();

        if (!$isAuthorizer) {
            abort(403, 'Unauthorized access.');
        }

        $pending = ServiceSra::with('submitter')
            ->where(function($q) {
                $q->where('admin_status', 'pending')
                  ->orWhere('status', 'pending');
            })
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
        $isAuthorizer = $user->is_admin 
            || $user->isMainAdminOrSub() 
            || in_array($user->role, ['Main Admin', 'Sub Main Admin', 'Head of Stores', 'Dept. Head (Stores)']) 
            || $user->isDelegatedApprover();

        if (!$isAuthorizer) {
            abort(403, 'Unauthorized access.');
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

    public function storesIndex(Request $request)
    {
        $user = auth()->user();
        $isStores = $user->is_admin
            || $user->isMainAdminOrSub()
            || in_array($user->role, ['Main Admin', 'Sub Main Admin', 'Head of Stores', 'Dept. Head (Stores)'])
            || (strcasecmp($user->department ?? '', 'Stores') === 0 || strcasecmp($user->department ?? '', 'Store') === 0)
            || $user->isDelegatedApprover();

        if (!$isStores) {
            abort(403, 'Unauthorized access.');
        }

        $pending = ServiceSra::with('submitter')
            ->where('status', 'admin_approved')
            ->where('stores_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pending_page');

        $history = ServiceSra::with('submitter')
            ->whereIn('stores_status', ['approved', 'declined'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10, ['*'], 'history_page');

        return view('service-sra.stores', compact('pending', 'history'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HEAD OF STORES: Review Board page
    // ─────────────────────────────────────────────────────────────────────────

    public function storesReview($id)
    {
        $user = auth()->user();
        $isStores = $user->is_admin
            || $user->isMainAdminOrSub()
            || in_array($user->role, ['Main Admin', 'Sub Main Admin', 'Head of Stores', 'Dept. Head (Stores)'])
            || (strcasecmp($user->department ?? '', 'Stores') === 0 || strcasecmp($user->department ?? '', 'Store') === 0)
            || $user->isDelegatedApprover();

        if (!$isStores) {
            abort(403, 'Unauthorized access.');
        }

        $sra = ServiceSra::with('submitter')->findOrFail($id);
        $orgName = Setting::get('organization_name', 'NACOC');

        return view('service-sra.review', compact('sra', 'orgName'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HEAD OF STORES: Final process (approve/decline)
    // ─────────────────────────────────────────────────────────────────────────

    public function storesProcess(Request $request, $id)
    {
        $user = auth()->user();
        $isStores = $user->is_admin
            || $user->isMainAdminOrSub()
            || in_array($user->role, ['Main Admin', 'Sub Main Admin', 'Head of Stores', 'Dept. Head (Stores)'])
            || (strcasecmp($user->department ?? '', 'Stores') === 0 || strcasecmp($user->department ?? '', 'Store') === 0)
            || $user->isDelegatedApprover();

        if (!$isStores) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'action' => 'required|in:approved,declined',
            'notes'  => 'nullable|string|max:1000',
        ]);

        $sra = ServiceSra::findOrFail($id);

        if ($sra->status === 'approved' || $sra->status === 'declined') {
            return response()->json(['success' => false, 'message' => 'This SRA has already been finalized.'], 422);
        }

        $sra->stores_status      = $request->action;
        $sra->stores_approved_by = $user->name;
        $sra->stores_approved_at = now();
        $sra->stores_notes       = $request->notes;

        if ($sra->admin_status === 'pending') {
            $sra->admin_status      = $request->action;
            $sra->admin_approved_by = $user->name;
            $sra->admin_approved_at = now();
        }

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
    // AUDITOR: Review Board page
    // ─────────────────────────────────────────────────────────────────────────

    public function auditorReview($id)
    {
        $user = auth()->user();
        if ($user->role !== 'Auditor' && !$user->isMainAdminOrSub() && !$user->is_admin && !$user->isDelegatedApprover()) {
            abort(403, 'Unauthorized access.');
        }

        $sra = ServiceSra::with('submitter')->findOrFail($id);
        $orgName = Setting::get('organization_name', 'NACOC');

        return view('service-sra.auditor_review', compact('sra', 'orgName'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AUDITOR: Process (approve/decline)
    // ─────────────────────────────────────────────────────────────────────────

    public function auditorProcess(Request $request, $id)
    {
        $user = auth()->user();
        if ($user->role !== 'Auditor' && !$user->isMainAdminOrSub() && !$user->is_admin && !$user->isDelegatedApprover()) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'action' => 'required|in:approved,declined',
            'notes'  => 'nullable|string|max:1000',
        ]);

        $sra = ServiceSra::findOrFail($id);

        if ($sra->auditor_status !== 'pending') {
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
