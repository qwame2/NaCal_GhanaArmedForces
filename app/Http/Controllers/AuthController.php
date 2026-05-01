<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function showAuth()
    {
        if (Auth::check()) {
            $user = Auth::user();
            return $user->is_admin ? redirect()->route('admin.index') : redirect()->route('dashboard');
        }
        return view('auth.auth');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-zA-Z])(?=.*[\d\W_]).+$/'
            ],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ], [
            'username.unique' => 'The personnel callsign "@' . $request->username . '" has already been registered in the database.',
            'password.regex' => 'The password must contain at least one letter and one number or symbol.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        try {
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            $isAdmin = $request->role === 'Admin';
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'avatar' => $avatarPath,
                'role' => $request->role ?? 'Personnel',
                'is_admin' => $isAdmin,
                'is_online' => true,
            ]);

            Auth::login($user);
            $user->update(['last_login_at' => now()]);

            // Log the registration
            \App\Models\SystemLog::create([
                'user_id' => $user->id,
                'event_type' => 'AUTH',
                'action' => 'REGISTRATION',
                'description' => "New personnel registry created for {$user->name} (@{$user->username}).",
                'severity' => 'info',
                'ip_address' => $request->ip()
            ]);

            $redirectRoute = $isAdmin ? 'admin.index' : 'dashboard';
            return redirect()->route($redirectRoute)->with('success', 'Registry initialized. Welcome, ' . $user->name);
        } catch (\Exception $e) {
            return back()->with('error', 'Critical System Failure: ' . $e->getMessage())->withInput();
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $user = auth()->user();

            // Check if the user's account is deactivated
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('account.deactivated');
            }

            $request->session()->regenerate();
            $user->update([
                'last_login_at' => now(),
                'is_online' => true,
            ]);
            
            $target = $request->target_interface; // 'admin' or 'personnel'

            // SCENARIO 1: User tried to enter ADMIN terminal
            if ($target === 'admin') {
                if ($user->is_admin) {
                    return redirect()->route('admin.index');
                } else {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')->with('error', 'Security Violation: Administrative clearance required for this terminal.');
                }
            }

            // SCENARIO 2: User tried to enter PERSONNEL terminal
            if ($target === 'user') {
                if (!$user->is_admin) {
                    return redirect()->intended('dashboard');
                } else {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')->with('error', 'Access Denied: Administrative accounts must use the Command Center terminal.');
                }
            }

            // Log the login
            \App\Models\SystemLog::create([
                'user_id' => $user->id,
                'event_type' => 'AUTH',
                'action' => 'LOGIN',
                'description' => "User authenticated and entered " . ($target === 'admin' ? 'Administrative' : 'Personnel') . " terminal.",
                'severity' => 'info',
                'ip_address' => $request->ip()
            ]);

            // Default fallback based on role
            return $user->is_admin ? redirect()->route('admin.index') : redirect()->route('dashboard');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Log the logout
            \App\Models\SystemLog::create([
                'user_id' => $user->id,
                'event_type' => 'AUTH',
                'action' => 'LOGOUT',
                'description' => "User session terminated.",
                'severity' => 'info',
                'ip_address' => $request->ip()
            ]);
            $user->update([
                'is_online' => false,
                'last_logout_at' => now()
            ]);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
