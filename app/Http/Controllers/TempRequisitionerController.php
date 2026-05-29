<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class TempRequisitionerController extends Controller
{
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

        $activeItems = \App\Models\IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->join('store_requisitions', 'issuances.requisition_id', '=', 'store_requisitions.id')
            ->select('issued_items.id', 'issued_items.quantity', 'store_requisitions.purpose', 'store_requisitions.created_at')
            ->where('issuances.issuance_type', 'Temporary')
            ->where('store_requisitions.department', $department)
            ->where('issued_items.quantity', '>', 0)
            ->get();

        foreach ($activeItems as $item) {
            // Check if fully returned under either remaining-qty or original-qty system
            $returnedQty = \App\Models\ReturnedItem::where('issued_item_id', $item->id)->sum('returned_qty') ?? 0;
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
    }

    /**
     * POST /dept-head/temp-requisitioners
     * Create a temporary requisitioner account for a staff member.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Only non-Stores Department Heads can create temp accounts
        if (!in_array($user->role, ['Department Head', 'Main Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only Department Heads can provision temporary accounts.'], 403);
        }

        $isStoresHead = (strcasecmp($user->department ?? '', 'Stores') === 0 || strcasecmp($user->department ?? '', 'Store') === 0);
        if ($isStoresHead) {
            return response()->json(['success' => false, 'message' => 'Stores Department Head cannot provision temporary requisitioner accounts.'], 403);
        }

        if (self::hasOverdueReturn($user->department)) {
            return response()->json([
                'success' => false,
                'message' => 'Provisioning suspended: Your department has an overdue temporary requisition return. Please return the outstanding assets to Central Store to restore access.'
            ], 403);
        }

        $request->validate([
            'username' => 'required|string|max:50|unique:users,username|alpha_num',
        ], [
            'username.unique'    => 'This username is already taken. Please choose another.',
            'username.alpha_num' => 'Username may only contain letters and numbers (no spaces or symbols).',
        ]);

        $department  = $user->department ?? 'UNKNOWN';
        $otpPrefix   = $this->generateOtpPrefix($department);
        $otp         = $this->generateOtp($otpPrefix);

        $tempUser = User::create([
            'name'                => $request->username,
            'username'            => $request->username,
            'password'            => bcrypt($otp), // hashed fallback
            'otp_token'           => $otp,         // plain for display & login comparison
            'role'                => 'Requisitioner',
            'department'          => $department,
            'is_temp_account'     => true,
            'is_active'           => true,
            'is_online'           => false,
            'must_change_password' => false,
            'sponsored_by'        => $user->id,
        ]);

        // Log the creation
        SystemLog::create([
            'user_id'     => $user->id,
            'event_type'  => 'SECURITY',
            'action'      => 'CREATE_TEMP_REQUISITIONER',
            'description' => "{$user->name} ({$user->department} Dept. Head) provisioned a temporary requisitioner account for @{$tempUser->username}.",
            'severity'    => 'info',
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'success'  => true,
            'otp'      => $otp,
            'username' => $tempUser->username,
            'message'  => "Temporary account created successfully. Share the OTP with the staff member — it will not be shown again.",
        ]);
    }

    /**
     * DELETE /dept-head/temp-requisitioners/{id}
     * Revoke (delete) a temporary requisitioner account.
     */
    public function destroy(Request $request, int $id)
    {
        $head = auth()->user();

        if (!in_array($head->role, ['Department Head', 'Main Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $tempUser = User::where('id', $id)
            ->where('is_temp_account', true)
            ->where('sponsored_by', $head->id)
            ->first();

        if (!$tempUser) {
            return response()->json(['success' => false, 'message' => 'Temporary account not found or you are not the sponsor.'], 404);
        }

        $username = $tempUser->username;
        $tempUser->delete();

        SystemLog::create([
            'user_id'     => $head->id,
            'event_type'  => 'SECURITY',
            'action'      => 'REVOKE_TEMP_REQUISITIONER',
            'description' => "{$head->name} revoked temporary requisitioner account @{$username}.",
            'severity'    => 'warning',
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Temporary account @{$username} has been revoked successfully.",
        ]);
    }

    /**
     * GET /api/dept-head/temp-requisitioners
     * List all temporary accounts sponsored by the logged-in Department Head.
     */
    public function index()
    {
        $head = auth()->user();

        if (!in_array($head->role, ['Department Head', 'Main Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $accounts = User::where('is_temp_account', true)
            ->where('sponsored_by', $head->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'username'   => $u->username,
                'department' => $u->department,
                'is_active'  => $u->is_active,
                'is_online'  => $u->is_online,
                'created_at' => $u->created_at->format('d/m/y H:i'),
            ]);

        return response()->json(['success' => true, 'accounts' => $accounts]);
    }

    /**
     * POST /dept-head/temp-requisitioners/{id}/regenerate-otp
     * Regenerate a new OTP for an existing temp account.
     */
    public function regenerateOtp(Request $request, int $id)
    {
        $head = auth()->user();

        if (!in_array($head->role, ['Department Head', 'Main Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $tempUser = User::where('id', $id)
            ->where('is_temp_account', true)
            ->where('sponsored_by', $head->id)
            ->first();

        if (!$tempUser) {
            return response()->json(['success' => false, 'message' => 'Temporary account not found.'], 404);
        }

        $otpPrefix = $this->generateOtpPrefix($tempUser->department ?? $head->department ?? 'ST');
        $newOtp    = $this->generateOtp($otpPrefix);

        $tempUser->update([
            'otp_token' => $newOtp,
            'password'  => bcrypt($newOtp),
        ]);

        SystemLog::create([
            'user_id'     => $head->id,
            'event_type'  => 'SECURITY',
            'action'      => 'REGENERATE_OTP',
            'description' => "{$head->name} regenerated OTP for temporary requisitioner @{$tempUser->username}.",
            'severity'    => 'info',
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'otp'     => $newOtp,
            'message' => "New OTP generated for @{$tempUser->username}.",
        ]);
    }
}
