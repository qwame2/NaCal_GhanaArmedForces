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

        if (strcasecmp($request->role, 'Requisitioner') === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Professional Role cannot be Requisitioner.'
            ], 422);
        }

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
            'password' => \Hash::make($request->password)
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
    public function messages()
    {
        // Self-heal: Mark all unread messages for the logged-in user as read when they visit the Comms Hub
        // to prevent any phantom badges or database mismatch issues.
        \App\Models\Message::where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $admins = \App\Models\User::where('is_admin', true)->get();
        if (in_array(auth()->user()->role, ['Main Admin', 'Department Head'])) {
            $colleagues = collect();
        } else {
            $colleagues = \App\Models\User::where('is_admin', false)->where('id', '!=', auth()->id())->get();
        }
        return view('messages.index', compact('admins', 'colleagues'));
    }
}
