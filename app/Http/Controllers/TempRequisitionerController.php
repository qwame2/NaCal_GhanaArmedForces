<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class TempRequisitionerController extends Controller
{
    protected static $overdueReturnCache = [];

    /**
     * Generate a department-prefix OTP from the department name.
     * Examples: "Information Technology" → "IT", "Human Resources" → "HR",
     *           "Procurement" → "PR", "Finance" → "FI"
     */
    private function generateOtpPrefix(string $department): string
    {
        $words = preg_split('/\s+/', trim($department));

        if (count($words) >= 2) {
            // Multi-word: take first letter of each word, uppercase
            $prefix = '';
            foreach ($words as $word) {
                $prefix .= strtoupper($word[0] ?? '');
            }
            return substr($prefix, 0, 4); // cap at 4 chars
        }

        // Single word: first 2 uppercase letters
        return strtoupper(substr($department, 0, 2));
    }

    /**
     * Generate a unique OTP (prefix + 4-digit random number).
     */
    private function generateOtp(string $prefix): string
    {
        do {
            $otp = $prefix . str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        } while (User::where('otp_token', $otp)->exists());

        return $otp;
    }

    /**
     * Check if a department has any overdue temporary loans.
     */
    public static function hasOverdueReturn($department)
    {
        if (empty($department)) {
            return false;
        }

        $deptClean = trim($department);
        if (array_key_exists($deptClean, self::$overdueReturnCache)) {
            return self::$overdueReturnCache[$deptClean];
        }

        // Short-lived Laravel cache (10 seconds) to avoid database joins across concurrent request loops
        $cacheKey = 'dept_overdue_return_' . md5($deptClean);
        $hasOverdue = \Illuminate\Support\Facades\Cache::remember($cacheKey, 10, function() use ($deptClean) {
            $activeItems = \App\Models\IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
                ->join('store_requisitions', 'issuances.requisition_id', '=', 'store_requisitions.id')
                ->select(
                    'issued_items.id',
                    'issued_items.quantity',
                    'store_requisitions.purpose',
                    'store_requisitions.created_at',
                    \DB::raw('(SELECT COALESCE(SUM(returned_qty), 0) FROM returned_items WHERE returned_items.issued_item_id = issued_items.id) as total_returned')
                )
                ->where('issuances.issuance_type', 'Temporary')
                ->where('store_requisitions.department', $deptClean)
                ->where('issued_items.quantity', '>', 0)
                ->get();

            foreach ($activeItems as $item) {
                // Check if fully returned under either remaining-qty or original-qty system
                $returnedQty = floatval($item->total_returned);
                if ($item->quantity <= 0 || $returnedQty >= $item->quantity) {
                    continue;
                }

                $returnDate = null;
                if (preg_match('/\[Expected Return Date:\s*([^\]]+)\]/i', $item->purpose, $matches)) {
                    try {
                        $returnDate = \App\Models\Setting::parseExpectedReturnDate(trim($matches[1]))->format('Y-m-d');
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                if (!$returnDate) {
                    $returnDate = \Carbon\Carbon::parse($item->created_at)->format('Y-m-d');
                }

                $today = \Carbon\Carbon::now()->format('Y-m-d');
                if ($today >= $returnDate) {
                    return true;
                }
            }

            return false;
        });

        self::$overdueReturnCache[$deptClean] = $hasOverdue;
        return $hasOverdue;
    }

    /**
     * GET /api/dept-head/temp-requisitioners
     * List all staff in the same department as the logged-in Department Head.
     */
    public function index()
    {
        $head = auth()->user();

        if (!in_array($head->role, ['Department Head', 'Main Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $accounts = User::where('department', $head->department)
            ->where('id', '!=', $head->id)
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'username'   => $u->username,
                'role'       => $u->role,
                'department' => $u->department,
                'is_active'  => $u->is_active,
                'is_online'  => $u->is_online,
                'can_make_requisition' => (bool)($u->can_make_requisition ?? true),
                'created_at' => $u->created_at ? $u->created_at->format('d/m/y H:i') : 'N/A',
            ]);

        return response()->json(['success' => true, 'accounts' => $accounts]);
    }

    /**
     * POST /dept-head/staff/{id}/toggle-request-access
     * Toggle the requisition privilege (can_make_requisition) for a staff member of the department.
     */
    public function toggleRequestAccess(Request $request, int $id)
    {
        $head = auth()->user();

        if (!in_array($head->role, ['Department Head', 'Main Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $staff = User::where('id', $id)
            ->where('department', $head->department)
            ->where('id', '!=', $head->id)
            ->first();

        if (!$staff) {
            return response()->json(['success' => false, 'message' => 'Staff member not found or not in your department.'], 404);
        }

        $staff->can_make_requisition = !$staff->can_make_requisition;
        $staff->save();

        $statusWord = $staff->can_make_requisition ? 'enabled' : 'disabled';

        SystemLog::create([
            'user_id'     => $head->id,
            'event_type'  => 'SECURITY',
            'action'      => 'TOGGLE_STAFF_REQUEST_ACCESS',
            'description' => "{$head->name} {$statusWord} requisition access for @{$staff->username}.",
            'severity'    => 'info',
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Requisition access for @{$staff->username} has been {$statusWord} successfully.",
            'can_make_requisition' => $staff->can_make_requisition
        ]);
    }
}
