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
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        
        if (Auth::check()) {
            $user = Auth::user();
            
            // Sync online status for authenticated sessions
            if (!$user->is_online) {
                $user->update(['is_online' => true]);
            }

            if ($user->is_admin) {
                return redirect()->route('admin.index');
            } elseif (in_array($user->role, ['Main Admin', 'Department Head'])) {
                return redirect()->route('main-admin.requisitions');
            } else {
                return redirect()->route('dashboard');
            }
        }
        return view('auth.auth');
    }

    public function register(Request $request)
    {
        $request->validate([
            'role' => 'required|string',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[0-9]/', // Must contain at least one number
                function ($attribute, $value, $fail) use ($request) {
                    $username = strtolower($request->username ?? '');
                    $fullname = strtolower($request->name ?? '');
                    $password = strtolower($value);
                    
                    if ($username !== '' && str_contains($password, $username)) {
                        $fail('Strategic Security Alert: Password cannot contain your username.');
                    }
                    if ($fullname !== '' && str_contains($password, $fullname)) {
                        $fail('Strategic Security Alert: Password cannot contain your full name.');
                    }
                },
            ],
        ]);

        if ($request->role !== 'Admin') {
            return back()->with('error', 'Strategic Oversight Alert: Personnel registration must be performed by an Administrator through the Command Center.');
        }

        // SECURITY ENFORCEMENT: Only one Active Admin account allowed in the entire system
        if (User::where('is_admin', true)->where('is_active', true)->exists()) {
            return back()->with('error', 'Strategic Oversight Alert: An active Administrative account already exists. Multiple Command nodes are prohibited.')->withInput();
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => 'Admin',
                'is_admin' => true,
                'is_online' => false,
            ]);

            // Log the registration
            \App\Models\SystemLog::create([
                'user_id' => $user->id,
                'event_type' => 'AUTH',
                'action' => 'REGISTRATION',
                'description' => "Administrator registry initialized for {$user->name} (@{$user->username}).",
                'severity' => 'info',
                'ip_address' => $request->ip()
            ]);

            return redirect()->route('login')
                ->with('success', 'Command Authority established successfully. Please authenticate to continue.')
                ->with('target_admin', true);
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

        // TEMP ACCOUNT OTP LOGIN: Bypass normal hashed-password auth for temp requisitioners
        if ($targetUser && $targetUser->is_temp_account && $targetUser->otp_token) {
            if ($request->password !== $targetUser->otp_token) {
                // Wrong OTP — record attempt but do NOT deactivate temp accounts via rate limiter
                \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 3600);
                return back()->withErrors([
                    'username' => 'Invalid access code. Please check your username and OTP with your Department Head.',
                ])->onlyInput('username');
            }

            // OTP matches — log the user in manually
            Auth::login($targetUser, false);
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $targetUser->update([
                'last_login_at' => now(),
                'is_online'     => true,
            ]);

            $request->session()->flash('just_logged_in', true);

            \App\Models\SystemLog::create([
                'user_id'     => $targetUser->id,
                'event_type'  => 'AUTH',
                'action'      => 'TEMP_LOGIN',
                'description' => "Temporary requisitioner @{$targetUser->username} accessed the system via OTP.",
                'severity'    => 'info',
                'ip_address'  => $request->ip(),
            ]);

            return redirect()->route('requisitions.index');
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

        // Persistent sessions are completely disabled for all accounts

        if (Auth::attempt($credentials, false)) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            $user = auth()->user();

            // Check if the user's account is deactivated
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('account.deactivated');
            }

            // FORCE PASSWORD CHANGE: Check if personnel needs to update their temporary key
            // (Skip this for temporary requisitioner accounts — they use OTP flow instead)
            if (!$user->is_admin && $user->must_change_password && !$user->is_temp_account) {
                return redirect()->route('password.change')->with('info', 'Security Synchronization required. Please update your temporary access key.');
            }

            $request->session()->regenerate();
            
            // SECURITY ENFORCEMENT: Block multiple simultaneous Admin sessions
            if ($user->is_admin) {
                // If the "remember" flag was somehow bypassed, force it to false for the session
                $request->session()->put('auth.remember', false);
                
                // ENFORCEMENT: Block different admin accounts from simultaneous sessions.
                // We allow the same admin to re-login (handles accidental tab closures).
                $otherAdminOnline = User::where('is_admin', true)
                    ->where('is_online', true)
                    ->where('id', '!=', $user->id)
                    ->exists();
                
                if ($otherAdminOnline) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')->with('error', 'Strategic Command Alert: A different Administrative session is already established. Concurrent Command access is prohibited.');
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
                } elseif (in_array($user->role, ['Main Admin', 'Department Head'])) {
                    return redirect()->route('main-admin.requisitions');
                } else {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')->with('error', 'Security Violation: Administrative clearance required for this terminal.');
                }
            }

            // SCENARIO 2: User tried to enter PERSONNEL terminal
            if ($target === 'user') {
                if ($user->is_admin) {
                    return redirect()->route('admin.index');
                }
                if ($user->role === 'Requisitioner') {
                    return redirect()->route('requisitions.index');
                }
                if (in_array($user->role, ['Main Admin', 'Department Head'])) {
                    return redirect()->route('main-admin.requisitions');
                }
                return redirect()->route('dashboard');
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
            if ($user->is_admin) {
                return redirect()->route('admin.index');
            } elseif ($user->role === 'Requisitioner') {
                return redirect()->route('requisitions.index');
            } elseif (in_array($user->role, ['Main Admin', 'Department Head'])) {
                return redirect()->route('main-admin.requisitions');
            } else {
                return redirect()->route('dashboard');
            }
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

    public function showChangePassword()
    {
        if (!auth()->user()->must_change_password) {
            return redirect()->route('dashboard');
        }
        return view('auth.change_password');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        // Check if head of stores already entered fullname and department
        $hasNameEntered = !empty($user->name) && $user->name !== $user->username;
        $hasDeptEntered = !empty($user->department);

        $rules = [
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[0-9]/', // Must contain at least one number
                function ($attribute, $value, $fail) use ($request, $user) {
                    $username = strtolower($request->username ?? $user->username);
                    $fullname = strtolower($request->name ?? $user->name);
                    $password = strtolower($value);
                    
                    if (str_contains($password, $username)) {
                        $fail('Strategic Security Alert: Password cannot contain your username.');
                    }
                    if (str_contains($password, $fullname)) {
                        $fail('Strategic Security Alert: Password cannot contain your full name.');
                    }
                },
            ],
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'phone' => 'nullable|string|max:255',
            'service_number' => 'nullable|string|max:255',
            'rank' => 'nullable|string|max:255',
        ];

        if (!$hasNameEntered) {
            $rules['name'] = 'required|string|max:255';
        }
        if (!$hasDeptEntered) {
            $rules['department'] = 'nullable|string|max:255';
        }

        $request->validate($rules);

        $updateData = [
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'username' => $request->username,
            'phone' => $request->phone,
            'service_number' => $request->service_number,
            'rank' => $request->rank,
        ];

        if (!$hasNameEntered) {
            $updateData['name'] = $request->name;
        }
        if (!$hasDeptEntered) {
            $updateData['department'] = $request->department;
        }

        $user->update($updateData);

        // Log the security update
        \App\Models\SystemLog::create([
            'user_id' => $user->id,
            'event_type' => 'SECURITY',
            'action' => 'PASSWORD_SYNCED',
            'description' => "Personnel @{$user->username} successfully updated temporary security key and was prompted for re-authentication.",
            'severity' => 'success',
            'ip_address' => $request->ip()
        ]);

        // CIA SECURITY ENFORCEMENT: Force re-authentication after credential update
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Security Synchronized. Please authenticate with your new master key.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot_password');
    }

    public function sendPasswordRequest(Request $request)
    {
        $request->validate(['username' => 'required|string']);

        $user = User::where('username', $request->username)->first();

        \App\Models\PasswordResetRequest::create([
            'user_id' => $user ? $user->id : null,
            'username' => $request->username,
            'status' => 'pending',
        ]);

        return redirect()->route('password.reset.otp')->with('success', 'Your request has been sent to the Admin. Once you receive your OTP, enter it here along with your new password.');
    }

    public function showResetWithOtp()
    {
        return view('auth.reset_password_otp');
    }

    public function resetWithOtp(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'otp' => 'required|string',
            'password' => [
                'required', 'string', 'min:8', 'confirmed', 'regex:/[0-9]/',
                function ($attribute, $value, $fail) use ($request) {
                    $user = \App\Models\User::where('username', $request->username)->first();
                    if ($user) {
                        $username = strtolower($user->username);
                        $fullname = strtolower($user->name);
                        $password = strtolower($value);
                        
                        if (str_contains($password, $username)) {
                            $fail('Strategic Security Alert: Password cannot contain your username.');
                        }
                        if (str_contains($password, $fullname)) {
                            $fail('Strategic Security Alert: Password cannot contain your full name.');
                        }
                    }
                }
            ],
        ]);

        $resetReq = \App\Models\PasswordResetRequest::where('username', $request->username)
            ->where('otp', $request->otp)
            ->where('status', 'approved')
            ->first();

        if (!$resetReq) {
            return back()->with('error', 'Invalid OTP or username. Please ensure the Admin has approved your request.');
        }

        $user = User::where('username', $resetReq->username)->first();
        if ($user) {
            $user->update([
                'password' => Hash::make($request->password),
                'must_change_password' => false,
                'is_active' => true, // Reactivate if it was deactivated
            ]);

            $resetReq->update(['status' => 'completed']);

            \App\Models\SystemLog::create([
                'user_id' => $user->id,
                'event_type' => 'SECURITY',
                'action' => 'PASSWORD_RESET_OTP',
                'description' => "Personnel @{$user->username} reset password using Admin-provided OTP.",
                'severity' => 'info',
                'ip_address' => $request->ip()
            ]);

            return redirect()->route('login')->with('success', 'Access restored. Please login with your new password.');
        }

        return back()->with('error', 'System Error: Personnel record not found.');
    }

}
