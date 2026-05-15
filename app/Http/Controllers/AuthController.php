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
            
            // Sync online status for authenticated sessions
            if (!$user->is_online) {
                $user->update(['is_online' => true]);
            }

            return $user->is_admin ? redirect()->route('admin.index') : redirect()->route('dashboard');
        }
        return view('auth.auth');
    }

    public function register(Request $request)
    {
        $allowRegistration = \Illuminate\Support\Facades\Schema::hasTable('settings') 
            ? \App\Models\Setting::get('allow_personnel_registration', true) 
            : true;

        if (!$allowRegistration) {
            return back()->with('error', 'Strategic Command has temporarily disabled new personnel registration.');
        }

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
            
            // SECURITY ENFORCEMENT: Only one Admin account allowed in the entire system
            if ($isAdmin && User::where('is_admin', true)->exists()) {
                return back()->with('error', 'Strategic Oversight Alert: An Administrative account already exists. Multiple Command nodes are prohibited.')->withInput();
            }

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

        $maxAttemptsRaw = \Illuminate\Support\Facades\Schema::hasTable('settings') 
            ? \App\Models\Setting::get('max_login_attempts', 5) 
            : 5;
        $maxAttempts = max(1, (int) $maxAttemptsRaw);

        $throttleKey = strtolower($request->input('username')) . '|' . $request->ip();

        $targetUser = User::where('username', $request->username)->first();

        // SECURITY ENFORCEMENT: Block already deactivated accounts immediately
        if ($targetUser && !$targetUser->is_active) {
            return back()->with('error', 'wrong password or username account has been deactivated see admin to activate your account')->withInput();
        }

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            
            // If it's a personnel account and they are already being throttled, ensure they are deactivated
            if ($targetUser && !$targetUser->is_admin) {
                if ($targetUser->is_active) {
                    $targetUser->update(['is_active' => false]);
                    \App\Models\SystemLog::create([
                        'user_id' => $targetUser->id,
                        'event_type' => 'SECURITY',
                        'action' => 'ACCOUNT_AUTO_DEACTIVATED',
                        'description' => "Personnel @{$targetUser->username} deactivated after triggering rate limit.",
                        'severity' => 'danger',
                        'ip_address' => $request->ip()
                    ]);
                }
                return back()->with('error', 'wrong password or username account has been deactivated see admin to activate your account')->withInput();
            }

            return back()->with('error', "Too many login attempts. Please try again in {$seconds} seconds.")->withInput();
        }

        // Administrative accounts are prohibited from using persistent "Remember Me" cookies

        $remember = ($targetUser && $targetUser->is_admin) ? false : $request->remember;

        if (Auth::attempt($credentials, $remember)) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            $user = auth()->user();

            // Check if the user's account is deactivated
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('account.deactivated');
            }

            $request->session()->regenerate();
            
            // SECURITY ENFORCEMENT: Block multiple simultaneous Admin sessions
            if ($user->is_admin) {
                // If the "remember" flag was somehow bypassed, force it to false for the session
                $request->session()->put('auth.remember', false);
                
                // If any admin is already marked as online (including this account from another session), 
                // we deny the new login attempt to enforce singular command authority.
                $anyAdminOnline = User::where('is_admin', true)->where('is_online', true)->exists();
                
                if ($anyAdminOnline) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')->with('error', 'Strategic Command Alert: An active Administrative session is already established. Concurrent access is prohibited.');
                }
            }

            $user->update([
                'last_login_at' => now(),
                'is_online' => true,
            ]);

            // CIA SECURITY ENFORCEMENT: Prime the current tab for security lock
            $request->session()->flash('just_logged_in', true);
            
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

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 3600); // Record hit for 1 hour
        $attempts = \Illuminate\Support\Facades\RateLimiter::attempts($throttleKey);

        // SECURITY ENFORCEMENT: Deactivate personnel accounts exceeding thresholds
        if ($targetUser && !$targetUser->is_admin && $attempts >= $maxAttempts) {
            $targetUser->update(['is_active' => false]);

            \App\Models\SystemLog::create([
                'user_id' => $targetUser->id,
                'event_type' => 'SECURITY',
                'action' => 'ACCOUNT_AUTO_DEACTIVATED',
                'description' => "Personnel registry @{$targetUser->username} auto-deactivated after {$attempts} failed authentication attempts.",
                'severity' => 'danger',
                'ip_address' => $request->ip()
            ]);

            return back()->with('error', 'wrong password or username account has been deactivated see admin to activate your account')->withInput();
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

    /**
     * CIA SECURITY ENFORCEMENT: Mark user as offline immediately.
     * This is called via navigator.sendBeacon when the browser tab closes.
     */
    public function markOffline(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->update(['is_online' => false]);
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'unauthenticated'], 401);
    }
}
