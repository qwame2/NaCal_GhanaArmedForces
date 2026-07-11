<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('settings.index', compact('user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . auth()->id(),
            'phone' => 'required|string|max:20',
            'role' => 'required|string|max:100',
            'service_number' => 'required|string|max:100',
            'department' => 'nullable|string|max:100',
        ]);

        $user = auth()->user();
        $user->update($request->only(['name', 'email', 'phone', 'role', 'department', 'service_number']));

        // Log the profile update
        \App\Models\SystemLog::create([
            'user_id' => $user->id,
            'event_type' => 'SYSTEM',
            'action' => 'UPDATE_PROFILE',
            'description' => "Personnel updated their account profile details.",
            'severity' => 'info',
            'ip_address' => request()->ip()
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Personnel Records Synchronized'
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        auth()->user()->update([
            'password' => $request->password
        ]);

        // Log the password change
        \App\Models\SystemLog::create([
            'user_id' => auth()->id(),
            'event_type' => 'SECURITY',
            'action' => 'CHANGE_PASSWORD',
            'description' => "Personnel updated their security credentials (password).",
            'severity' => 'warning',
            'ip_address' => request()->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Security Credentials Updated'
        ]);
    }



    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $user = auth()->user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Profile Photo Uploaded Successfully',
                'url' => \Illuminate\Support\Facades\Storage::url($path)
            ]);
        }
        
        return response()->json(['success' => false, 'message' => 'Upload failed.']);
    }

    public function updateSignature(Request $request)
    {
        if (!in_array(auth()->user()->role, ['Head of Stores', 'Auditor', 'Director General', 'Main Admin', 'Sub Main Admin']) && !auth()->user()->isDelegatedApprover()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only Head of Stores, Auditors and Delegated Store Officers can perform this action.'], 403);
        }

        $request->validate([
            'signature' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $user = auth()->user();

        if ($request->hasFile('signature')) {
            if ($user->signature) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->signature);
            }

            $tempPath = $request->file('signature')->getRealPath();

            try {
                $client = new \GuzzleHttp\Client();
                $res = $client->post('https://api.remove.bg/v1.0/removebg', [
                    'multipart' => [
                        [
                            'name'     => 'image_file',
                            'contents' => fopen($tempPath, 'r')
                        ],
                        [
                            'name'     => 'size',
                            'contents' => 'auto'
                        ]
                    ],
                    'headers' => [
                        'X-Api-Key' => 'KZY2LfD7ZMZAmqLdDpeweVUd'
                    ],
                    'verify' => false // Disable SSL verification to prevent 'cURL error 60: SSL certificate problem' in local environment
                ]);

                $body = $res->getBody()->getContents();
                $filename = 'signatures/' . uniqid() . '.png';
                \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $body);
                $path = $filename;
            } catch (\Exception $e) {
                // Fallback to storing original file if API fails (e.g., rate limits or API key limitations)
                $path = $request->file('signature')->store('signatures', 'public');
            }

            $user->update(['signature' => $path]);

            // Log signature upload activity
            $logAction = auth()->user()->isDelegatedApprover() ? 'DELEGATED_UPLOAD_SIGNATURE' : 'UPLOAD_SIGNATURE';
            $logDesc = auth()->user()->isDelegatedApprover()
                ? "Delegated Store Officer uploaded a new digital signature."
                : "User updated their digital signature.";

            \App\Models\SystemLog::create([
                'user_id' => auth()->id(),
                'event_type' => 'SECURITY',
                'action' => $logAction,
                'description' => $logDesc,
                'severity' => 'info',
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Signature Image Uploaded Successfully',
                'url' => \Illuminate\Support\Facades\Storage::url($path)
            ]);
        }
        
        return response()->json(['success' => false, 'message' => 'Upload failed.']);
    }

    public function removeSignature(Request $request)
    {
        if (!in_array(auth()->user()->role, ['Head of Stores', 'Auditor', 'Director General', 'Main Admin', 'Sub Main Admin']) && !auth()->user()->isDelegatedApprover()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only Head of Stores, Auditors and Delegated Store Officers can perform this action.'], 403);
        }

        $user = auth()->user();
        if ($user->signature) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->signature);
            $user->update(['signature' => null]);

            // Log signature removal activity
            $logAction = auth()->user()->isDelegatedApprover() ? 'DELEGATED_REMOVE_SIGNATURE' : 'REMOVE_SIGNATURE';
            $logDesc = auth()->user()->isDelegatedApprover()
                ? "Delegated Store Officer removed their digital signature."
                : "User removed their digital signature.";

            \App\Models\SystemLog::create([
                'user_id' => auth()->id(),
                'event_type' => 'SECURITY',
                'action' => $logAction,
                'description' => $logDesc,
                'severity' => 'warning',
                'ip_address' => $request->ip()
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Signature Image Removed Successfully'
        ]);
    }


    public function messages()
    {
        $admins = \App\Models\User::getApproversQuery()
            ->where('id', '!=', auth()->id())
            ->where('registration_status', 'approved')
            ->get();
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head'])) {
            $colleagues = collect();
        } else {
            $delegatedId = \App\Models\Setting::get('delegated_approver_id');
            $colleagues = \App\Models\User::where('is_admin', false)
                ->where(function($q) use ($delegatedId) {
                    if ($delegatedId) {
                        $q->where('id', '!=', $delegatedId);
                    }
                })
                ->where('id', '!=', auth()->id())
                ->where('registration_status', 'approved')
                ->get();
        }
        return view('messages.index', compact('admins', 'colleagues'));
    }
}
