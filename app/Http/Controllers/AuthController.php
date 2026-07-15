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
        $username = session('pending_password_reset_username');
        $departmentHeads = \App\Models\User::where('role', 'Department Head')
            ->where('is_active', true)
            ->where('registration_status', 'approved')
            ->get();

        if ($username) {
            $latestRequest = \App\Models\PasswordResetRequest::where('username', $username)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($latestRequest && $latestRequest->status === 'rejected') {
                return view('auth.auth', [
                    'rejected_reset' => true,
                    'rejected_username' => $username,
                    'rejected_message' => "Alert: Your password reset request has been rejected by the Head of Stores. Please contact Head of Stores for resolution.",
                    'departmentHeads' => $departmentHeads
                ]);
            } else {
                session()->forget('pending_password_reset_username');
            }
        }

        // Handle pending approval notification
        $pendingApprovalUsername = session('pending_approval_username');
        $showApprovedNotification = false;
        if ($pendingApprovalUsername) {
            $pendingUser = \App\Models\User::where('username', $pendingApprovalUsername)->first();
            if ($pendingUser && $pendingUser->registration_status === 'approved' && $pendingUser->is_active) {
                // Account just approved — show notification then clear session
                $showApprovedNotification = true;
                session()->forget('pending_approval_username');
            } elseif (!$pendingUser) {
                // User no longer exists (deleted/rejected permanently)
                session()->forget('pending_approval_username');
                $pendingApprovalUsername = null;
            }
        }

        if (Auth::check()) {
            $user = Auth::user();
            
            // Sync online status for authenticated sessions
            if (!$user->is_online) {
                $user->update(['is_online' => true]);
            }

            if (in_array($user->role, ['Main Admin', 'Department Head'])) {
                return redirect()->route('main-admin.requisitions');
            } elseif ($user->role === 'Auditor') {
                return redirect()->route('auditor.dashboard');
            } elseif ($user->role === 'Director General') {
                return redirect()->route('dg.dashboard');
            } elseif ($user->is_admin) {
                return redirect()->route('admin.index');
            } else {
                return redirect()->route('dashboard');
            }
        }
        return view('auth.auth', compact('departmentHeads', 'pendingApprovalUsername', 'showApprovedNotification'));
    }


    public function register(Request $request)
    {
        // Delete existing rejected user to allow registration
        $existingRejected = User::where('username', $request->username)
            ->where('registration_status', 'rejected')
            ->first();
        if ($existingRejected) {
            $existingRejected->delete();
        }

        $request->validate([
            'role' => 'required|string',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'rank' => 'required|string|max:255',
            'service_number' => 'required|string|max:255',
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

        if ($request->role !== 'Head of Stores') {
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
                'password' => $request->password,
                'role' => 'Head of Stores',
                'is_admin' => true,
                'is_online' => false,
                'rank' => $request->rank,
                'service_number' => $request->service_number,
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

        $usernameInput = $request->username;
        $samAccountName = $usernameInput;
        if (str_contains($usernameInput, '@')) {
            $samAccountName = explode('@', $usernameInput)[0];
        }

        $latestRequest = \App\Models\PasswordResetRequest::where('username', $samAccountName)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($latestRequest && $latestRequest->status === 'rejected') {
            session(['pending_password_reset_username' => $samAccountName]);
            return back()->with('error', "Alert: Your password reset request has been rejected by the Head of Stores. Please contact Head of Stores for resolution.")->withInput();
        }

        $maxAttemptsRaw = \Illuminate\Support\Facades\Schema::hasTable('settings') 
            ? \App\Models\Setting::get('max_login_attempts', 5) 
            : 5;
        $maxAttempts = max(1, (int) $maxAttemptsRaw);

        $throttleKey = strtolower($samAccountName) . '|' . $request->ip();

        $targetUser = User::where('username', $samAccountName)->first();

        // SECURITY ENFORCEMENT: Block pending self-registrations
        if ($targetUser && in_array($targetUser->registration_status, ['pending', 'pending_hod'])) {
            $errorMsg = $targetUser->registration_status === 'pending_hod'
                ? 'Your registration request is pending approval by your Department Head. Please try again later.'
                : 'Your registration request is pending approval by the Admin. Please try again later.';
            return back()->with('error', $errorMsg)->withInput();
        }

        // SECURITY ENFORCEMENT: Block rejected self-registrations
        if ($targetUser && $targetUser->registration_status === 'rejected') {
            return back()->with('error', 'Your account has been declined. Please register again or contact Admin.')->withInput();
        }

        // SECURITY ENFORCEMENT: Block already deactivated accounts immediately
        if ($targetUser && !$targetUser->is_active) {
            if ($targetUser->is_temp_account && \App\Http\Controllers\TempRequisitionerController::hasOverdueReturn($targetUser->department)) {
                return back()->with('error', 'Access Suspended: Your department currently has overdue temporary assets. Active temporary accounts are suspended.')->withInput();
            }
            return back()->with('error', 'wrong password or username account has been deactivated see head of stores to activate your account')->withInput();
        }

        // TEMP ACCOUNT OTP LOGIN: Bypass normal hashed-password auth for temp requisitioners
        if ($targetUser && $targetUser->is_temp_account && $targetUser->otp_token) {
            if ($request->password !== $targetUser->otp_token) {
                // Wrong OTP — record attempt and handle possible rate limiting lockout
                return $this->handleFailedAttempt(
                    $request,
                    $throttleKey,
                    $maxAttempts,
                    $targetUser,
                    $samAccountName,
                    'Invalid access code. Please check your username and OTP with your Department Head.'
                );
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
            return back()->with('error', "Too many login attempts. Please try again in {$seconds} seconds.")->withInput();
        }

        // Persistent sessions are completely disabled for all accounts

        $ldapAuthenticated = false;

        // Try AD Auth first
        try {
            if (config('ldap.default') && !empty(config('ldap.connections.default.hosts')) && config('ldap.connections.default.hosts')[0] !== '127.0.0.1') {
                $ldapUser = \LdapRecord\Models\ActiveDirectory\User::where('samaccountname', '=', $samAccountName)
                    ->orWhere('userprincipalname', '=', $usernameInput)
                    ->first();

                if ($ldapUser) {
                    $connection = \LdapRecord\Container::getConnection('default');
                    if ($connection->auth()->attempt($ldapUser->getDn(), $request->password)) {
                        // AD Authentication Success!
                        $adSamName = $ldapUser->getFirstAttribute('samaccountname') ?? $samAccountName;
                        $cn = $ldapUser->getFirstAttribute('cn') ?? $adSamName;
                        $mail = $ldapUser->getFirstAttribute('mail');
                        $department = $ldapUser->getFirstAttribute('department') ?? 'General';
                        
                        // Map roles and permissions based on group membership
                        $groups = $ldapUser->groups()->get()->map(fn($g) => strtolower($g->getName()))->toArray();
                        
                        $role = 'Requisitioner'; // Default fallback role
                        $isAdmin = false;
                        $isTempAccount = false;
                        
                        $canMakeReq = false;
                        $canApproveReq = false;
                        $canAddInventory = false;
                        $canOperateLogistics = false;
                        $canGenerateReports = false;

                        // Check groups
                        foreach ($groups as $group) {
                            // Dynamic department resolution from AD group suffixes
                            $deptSuffixes = [
                                '_intelligence' => 'Intelligence Department',
                                '_investigations' => 'Investigations Department',
                                '_forensic' => 'Forensic Science Department',
                                '_asset_recovery' => 'Asset recovery & Management Department',
                                '_strategic_intel' => 'Strategic Intelligence Oversight Department',
                                '_cannabis' => 'Cannabis Regulations Department',
                                '_precursor' => 'Precursor Diversion Department',
                                '_drug_education' => 'Drug Education & Prevention Department',
                                '_rehab' => 'Rehabilitation & Social Re-integration Department',
                                '_harm_reduction' => 'Harm Reduction Department',
                                '_alt_livelihood' => 'Alternative Livelihoods Development Department',
                                '_canine' => 'Canine Operations Department',
                                '_accounts' => 'Accounts & Budget Department',
                                '_budget' => 'Accounts & Budget Department',
                                '_finance' => 'Accounts & Budget Department',
                                '_payroll' => 'Payroll & Pension Department',
                                '_pension' => 'Payroll & Pension Department',
                                '_research' => 'Research Policy Planning Monitoring & Evaluation Department',
                                '_m_e' => 'Research Policy Planning Monitoring & Evaluation Department',
                                '_professional_standards' => 'Professional Standards Department',
                                '_standards' => 'Professional Standards Department',
                                '_general_services' => 'General Services Department',
                                '_ict' => 'ICT Department',
                                '_it' => 'ICT Department',
                                '_transport' => 'Transport Department',
                                '_procurement' => 'Procurement Department',
                                '_project' => 'Project Management Department',
                                '_hr' => 'Human Resource Management Department',
                                '_welfare' => 'Welfare Department',
                                '_religious' => 'Religious Affairs Department',
                                '_training' => 'Internal & External Training Department',
                                '_public_affairs' => 'Public Affairs Department',
                                '_international' => 'International Relations Department',
                                '_material_dev' => 'Material Development Department',
                                '_client_service' => 'Client Service Department',
                                '_stores' => 'Stores',
                                '_store' => 'Stores',
                            ];

                            foreach ($deptSuffixes as $suffix => $deptName) {
                                if (str_contains($group, $suffix)) {
                                    $department = $deptName;
                                    break;
                                }
                            }

                            if (str_contains($group, 'nacoc_admins')) {
                                $role = 'Head of Stores';
                                $isAdmin = true;
                                $canAddInventory = true;
                                $canOperateLogistics = true;
                                $canGenerateReports = true;
                            } elseif (str_contains($group, 'nacoc_stores_head')) {
                                $role = 'Main Admin';
                                $isAdmin = true;
                                $department = 'Stores';
                                $canAddInventory = true;
                                $canOperateLogistics = true;
                                $canGenerateReports = true;
                            } elseif (str_contains($group, 'nacoc_dept_heads')) {
                                $role = 'Department Head';
                                $canApproveReq = true;
                                $canGenerateReports = true;
                            } elseif (str_contains($group, 'nacoc_auditors')) {
                                $role = 'Auditor';
                                $isTempAccount = true;
                                $department = 'Internal Audit';
                                $canGenerateReports = true;
                            } elseif (str_contains($group, 'nacoc_requisitioners')) {
                                $role = 'Requisitioner';
                                $canMakeReq = true;
                            } elseif (str_contains($group, 'nacoc_officers')) {
                                $role = 'Officer';
                                $department = 'Stores';
                                $canAddInventory = true;
                                $canOperateLogistics = true;
                            }
                        }

                        // Check if the user already exists locally and is deactivated
                        if ($targetUser && !$targetUser->is_active) {
                            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 3600);
                            return back()->with('error', 'wrong password or username account has been deactivated see head of stores to activate your account')->withInput();
                        }

                        // Create or update local user safely with standard attributes and password placeholder on creation
                        $localUser = User::where('username', $samAccountName)->first();
                        if (!$localUser) {
                            $localUser = new User();
                            $localUser->username = $samAccountName;
                            $localUser->password = \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32));
                            $localUser->can_add_inventory = false;
                            $localUser->can_operate_logistics = false;
                            $localUser->can_generate_reports = false;
                            $localUser->can_verify_stock = false;
                        }

                        $localUser->guid = $ldapUser->getConvertedGuid();
                        $localUser->domain = 'default';
                        $localUser->name = $cn;
                        $localUser->email = $mail;
                        $localUser->department = $department;
                        $localUser->role = $role;
                        $localUser->is_admin = $isAdmin;
                        $localUser->is_temp_account = $isTempAccount;
                        $localUser->is_active = true;
                        $localUser->registration_status = 'approved';
                        $localUser->must_change_password = $this->isDefaultPassword($request->password);
                        $localUser->can_make_requisition = $canMakeReq;
                        $localUser->can_approve_requisition = $canApproveReq;
                        $localUser->save();

                        Auth::login($localUser, false);
                        $ldapAuthenticated = true;
                    } else {
                        // AD User exists, but incorrect password entered
                        return $this->handleFailedAttempt(
                            $request,
                            $throttleKey,
                            $maxAttempts,
                            $targetUser,
                            $samAccountName,
                            'The provided credentials do not match our Active Directory records.'
                        );
                    }
                }
            }
        } catch (\LdapRecord\ConnectionException $e) {
            \Illuminate\Support\Facades\Log::warning("Active Directory Connection Failed: " . $e->getMessage() . ". Falling back to local database authentication.");
            
            \App\Models\SystemLog::create([
                'user_id' => null,
                'event_type' => 'SECURITY',
                'action' => 'LDAP_CONNECTION_FAILURE',
                'description' => "Active Directory Connection Failure during login check: " . $e->getMessage() . ". Falling back to local database authentication.",
                'severity' => 'warning',
                'ip_address' => $request->ip()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("LDAP General Error: " . $e->getMessage());
        }

        $localCredentials = [
            'username' => $samAccountName,
            'password' => $request->password,
        ];

        if ($ldapAuthenticated || Auth::attempt($localCredentials, false)) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            $user = auth()->user();

            if ($this->isDefaultPassword($request->password)) {
                $user->must_change_password = true;
                $user->save();
            }

            // Check if the user's account is deactivated
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('account.deactivated');
            }

            // FORCE PASSWORD CHANGE: Check if personnel or main admin needs to update their temporary key
            // For AD synced accounts, they are also forced to change their password if must_change_password is true.
            if ($user->guid && $user->must_change_password) {
                return redirect()->route('password.change')->with('info', 'Security Synchronization required. Please change your default Active Directory password.');
            }

            if (($user->isMainAdminOrSub() || !$user->is_admin) && $user->must_change_password && !$user->is_temp_account) {
                return redirect()->route('password.change')->with('info', 'Security Synchronization required. Please update your temporary access key.');
            }

            $request->session()->regenerate();
            
            // SECURITY ENFORCEMENT: Block multiple simultaneous Admin sessions
            if ($user->is_admin) {
                // If the "remember" flag was somehow bypassed, force it to false for the session
                $request->session()->put('auth.remember', false);
                
                // Clean up stale admin "is_online" statuses by checking database session activity.
                // If an admin has no session activity within the last 5 minutes, reset their status to offline.
                try {
                    $activeSessionUserIds = \Illuminate\Support\Facades\DB::table('sessions')
                        ->where('last_activity', '>=', now()->subMinutes(5)->timestamp)
                        ->whereNotNull('user_id')
                        ->pluck('user_id')
                        ->toArray();

                    User::where('is_admin', true)
                        ->where('is_online', true)
                        ->whereNotIn('id', $activeSessionUserIds)
                        ->update(['is_online' => false]);
                } catch (\Exception $e) {
                    // Fallback in case the sessions table is not available or configured
                }

                // Concurrent session restriction disabled per requirements.
            }

            $user->update([
                'last_login_at' => now(),
                'is_online' => true,
            ]);

            // CIA SECURITY ENFORCEMENT: Prime the current tab for security lock
            $request->session()->flash('just_logged_in', true);
            
            // Log the login
            \App\Models\SystemLog::create([
                'user_id' => $user->id,
                'event_type' => 'AUTH',
                'action' => 'LOGIN',
                'description' => "User authenticated and entered system. (Auth Source: " . ($user->guid ? 'Active Directory' : 'Local Database') . ")",
                'severity' => 'info',
                'ip_address' => $request->ip()
            ]);

            // Route user based on their specific role
            if ($user->isMainAdminOrSub() || $user->isDepartmentHead()) {
                return redirect()->route('main-admin.requisitions');
            } elseif ($user->role === 'Auditor') {
                return redirect()->route('auditor.dashboard');
            } elseif ($user->role === 'Director General') {
                return redirect()->route('dg.dashboard');
            } elseif ($user->role === 'Requisitioner') {
                return redirect()->route('requisitions.index');
            } elseif ($user->is_admin) {
                return redirect()->route('admin.index');
            } else {
                return redirect()->route('dashboard');
            }
        }

        return $this->handleFailedAttempt(
            $request,
            $throttleKey,
            $maxAttempts,
            $targetUser,
            $samAccountName,
            'The provided credentials do not match our records.'
        );
    }

    protected function isDefaultPassword($password)
    {
        $defaultPassword = env('AD_DEFAULT_PASSWORD');
        if (empty($defaultPassword)) {
            return false;
        }
        return strcasecmp($password, $defaultPassword) === 0;
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
            Auth::user()->update(['is_online' => false]);
        }
        return response()->json(['status' => 'success']);
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

        // Active Directory Password Writeback
        if ($user->guid) {
            try {
                // Find user in AD
                $ldapUser = \LdapRecord\Models\ActiveDirectory\User::where('samaccountname', '=', $user->username)->first();
                if (!$ldapUser) {
                    $ldapUser = \LdapRecord\Models\ActiveDirectory\User::where('guid', '=', $user->guid)->first();
                }

                if (!$ldapUser) {
                    return back()->withErrors(['password' => 'Unable to locate your Active Directory account to update the password.'])->withInput();
                }

                // Update the password in Active Directory
                $ldapUser->setPassword($request->password);
                $ldapUser->save();

            } catch (\LdapRecord\ConnectionException $e) {
                \Illuminate\Support\Facades\Log::error("AD Password Update Connection Failure: " . $e->getMessage());
                return back()->withErrors(['password' => 'Active Directory connection failure. Could not update password. Details: ' . $e->getMessage()])->withInput();
            } catch (\LdapRecord\LdapRecordException $e) {
                \Illuminate\Support\Facades\Log::error("AD Password Update LDAP Error: " . $e->getMessage());
                $errorMsg = $e->getMessage();
                if (str_contains($errorMsg, 'attribute conversion') || str_contains($errorMsg, '00000057') || str_contains($errorMsg, 'Operations error')) {
                    $errorMsg = 'Active Directory password changes require a secure SSL/TLS connection. Please verify your server configuration or contact your system administrator.';
                }
                return back()->withErrors(['password' => 'Active Directory rejected password change. Details: ' . $errorMsg])->withInput();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("AD Password Update General Exception: " . $e->getMessage());
                return back()->withErrors(['password' => 'System error updating Active Directory password: ' . $e->getMessage()])->withInput();
            }
        }

        $updateData = [
            'password' => $request->password,
            'must_change_password' => false,
            'phone' => $request->phone,
            'service_number' => $request->service_number,
            'rank' => $request->rank,
        ];

        if (!$user->guid) {
            $updateData['username'] = $request->username;
        }

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
        $username = session('pending_password_reset_username');
        if ($username) {
            $latestRequest = \App\Models\PasswordResetRequest::where('username', $username)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($latestRequest && $latestRequest->status === 'rejected') {
                return redirect()->route('password.reset.otp');
            }
        }
        return view('auth.forgot_password');
    }

    public function sendPasswordRequest(Request $request)
    {
        $request->validate(['username' => 'required|string']);

        $latestRequest = \App\Models\PasswordResetRequest::where('username', $request->username)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($latestRequest && $latestRequest->status === 'rejected') {
            session(['pending_password_reset_username' => $request->username]);
            return back()->with('error', "Alert: Your password reset request has been rejected by the Head of Stores. Please contact Head of Stores for resolution.")->withInput();
        }

        $user = User::where('username', $request->username)->first();

        \App\Models\PasswordResetRequest::create([
            'user_id' => $user ? $user->id : null,
            'username' => $request->username,
            'status' => 'pending',
        ]);

        session(['pending_password_reset_username' => $request->username]);

        return redirect()->route('password.reset.otp')->with('success', 'Your request has been sent to the Admin. Once you receive your OTP, enter it here along with your new password.');
    }

    public function showResetWithOtp()
    {
        $username = session('pending_password_reset_username');
        if ($username) {
            $latestRequest = \App\Models\PasswordResetRequest::where('username', $username)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($latestRequest && $latestRequest->status === 'rejected') {
                return view('auth.reset_password_otp', [
                    'rejected_reset' => true,
                    'rejected_username' => $username,
                    'rejected_message' => "Alert: Your password reset request has been rejected by the Head of Stores. Please contact Head of Stores for resolution."
                ]);
            } else {
                session()->forget('pending_password_reset_username');
            }
        }
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

        $latestRequest = \App\Models\PasswordResetRequest::where('username', $request->username)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($latestRequest && $latestRequest->status === 'rejected') {
            session(['pending_password_reset_username' => $request->username]);
            return back()->with('error', "Alert: Your password reset request has been rejected by the Head of Stores. Please contact Head of Stores for resolution.")->withInput();
        }

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
                'password' => $request->password,
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

    public function checkResetStatus(Request $request)
    {
        $username = $request->query('username');
        if (!$username) {
            return response()->json(['rejected' => false]);
        }

        $latestRequest = \App\Models\PasswordResetRequest::where('username', $username)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestRequest && $latestRequest->status === 'rejected') {
            session(['pending_password_reset_username' => $username]);
            return response()->json([
                'rejected' => true,
                'message' => "Alert: Your password reset request has been rejected by the Head of Stores. Please contact Head of Stores for resolution."
            ]);
        }

        if (session('pending_password_reset_username') === $username) {
            session()->forget('pending_password_reset_username');
        }

        return response()->json(['rejected' => false]);
    }

    public function checkForgotEligibility(Request $request)
    {
        $username = $request->query('username');
        if (!$username) {
            return response()->json(['eligible' => false]);
        }

        $user = User::where('username', $username)->first();
        if ($user && ($user->isDepartmentHead() || in_array($user->role, ['Head of Stores', 'Main Admin', 'Sub Main Admin']))) {
            return response()->json(['eligible' => true]);
        }

        return response()->json(['eligible' => false]);
    }

    public function checkApprovalStatus(Request $request)
    {
        $username = $request->query('username');
        if (!$username) {
            return response()->json(['status' => 'unknown']);
        }

        $user = User::where('username', $username)->first();
        if (!$user) {
            return response()->json(['status' => 'not_found']);
        }

        return response()->json([
            'status'   => $user->registration_status,
            'approved' => $user->registration_status === 'approved' && $user->is_active,
        ]);
    }

    public function getDepartmentHead(Request $request)
    {
        $department = $request->query('department');
        if (!$department) {
            return response()->json(['registered' => false]);
        }

        $deptHead = User::whereIn('role', ['Department Head', 'Dept Head HR', 'Head of Welfare', 'Main Admin', 'Sub Main Admin'])
            ->where('department', $department)
            ->where('is_active', true)
            ->where('registration_status', 'approved')
            ->first();

        if ($deptHead) {
            return response()->json([
                'registered' => true,
                'name' => $deptHead->name,
                'id' => $deptHead->id,
            ]);
        }

        return response()->json(['registered' => false]);
    }

    public function selfRegister(Request $request)
    {
        $allowRegistration = \Illuminate\Support\Facades\Schema::hasTable('settings')
            ? \App\Models\Setting::get('allow_personnel_registration', true)
            : true;
        if (!$allowRegistration) {
            return redirect()->route('login')->with('error', 'Self-registration is currently disabled by the Administrator.');
        }

        // Delete existing rejected user to allow re-registration
        $existingRejected = User::where('username', $request->username)
            ->where('registration_status', 'rejected')
            ->first();
        if ($existingRejected) {
            $existingRejected->delete();
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'phone' => 'required|string|max:20',
            'service_number' => 'required|string|max:100',
            'department' => 'required|string|max:255',
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
        ], [
            'username.unique' => 'This username has already been registered.',
            'password.min' => 'Passwords must be at least 8 characters long.',
            'password.regex' => 'Passwords must contain at least one number.',
        ]);

        $isRequisitioner = $request->has('is_requisitioner');
        $deptHeadId = null;

        if ($isRequisitioner) {
            $deptHead = User::whereIn('role', ['Department Head', 'Dept Head HR', 'Head of Welfare', 'Main Admin', 'Sub Main Admin'])
                ->where('department', $request->department)
                ->where('is_active', true)
                ->where('registration_status', 'approved')
                ->first();

            if (!$deptHead) {
                return back()->with('error', 'Strategic Alert: No registered Department Head found for this department. Registration is locked.')->withInput();
            }
            $deptHeadId = $deptHead->id;
        }

        try {
            $user = new User([
                'name' => $request->name,
                'username' => $request->username,
                'password' => $request->password,
                'role' => $isRequisitioner ? 'Requisitioner' : null,
                'department' => $request->department,
                'rank' => null,
                'phone' => $request->phone,
                'service_number' => $request->service_number,
                'sponsored_by' => $deptHeadId,
                'is_admin' => false,
                'is_temp_account' => false,
                'is_active' => false,
                'registration_status' => $isRequisitioner ? 'pending_hod' : 'pending',
                'must_change_password' => false,
            ]);

            $user->save();

            // Log the self-registration
            \App\Models\SystemLog::create([
                'user_id' => null, // Guest action
                'event_type' => 'SECURITY',
                'action' => 'SELF_REGISTER',
                'description' => "Self-registration request submitted by {$user->name} (@{$user->username}).",
                'severity' => 'info',
                'ip_address' => $request->ip()
            ]);

            $successMsg = $isRequisitioner
                ? 'Your registration request has been submitted successfully. Please wait for your Departmental Head\'s approval.'
                : 'Your registration request has been submitted successfully. Please wait for Admin approval.';

            // Persist username so the login page can poll for approval status
            session(['pending_approval_username' => $user->username]);

            return redirect()->route('login')
                ->with('success', $successMsg);
        } catch (\Exception $e) {
            return back()->with('error', 'Critical System Failure: ' . $e->getMessage())->withInput();
        }
    }

    protected function handleFailedAttempt(Request $request, $throttleKey, $maxAttempts, $targetUser, $samAccountName, $errorMessage)
    {
        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 3600);
        $attempts = \Illuminate\Support\Facades\RateLimiter::attempts($throttleKey);

        if ($attempts >= $maxAttempts) {
            if ($targetUser && !$targetUser->is_admin) {
                // Deactivate personnel account
                $targetUser->update(['is_active' => false]);

                \App\Models\SystemLog::create([
                    'user_id' => $targetUser->id,
                    'event_type' => 'SECURITY',
                    'action' => 'ACCOUNT_AUTO_DEACTIVATED',
                    'description' => "Personnel registry @{$targetUser->username} auto-deactivated after {$attempts} failed authentication attempts.",
                    'severity' => 'danger',
                    'ip_address' => $request->ip()
                ]);

                return back()->with('error', 'wrong password or username account has been deactivated see head of stores to activate your account')->withInput();
            }

            $userType = 'Unknown';
            $userId = null;
            if ($targetUser) {
                $userType = $targetUser->is_admin ? 'Admin' : 'Personnel';
                $userId = $targetUser->id;
            }

            \App\Models\SystemLog::create([
                'user_id' => $userId,
                'event_type' => 'SECURITY',
                'action' => 'LOGIN_ATTEMPTS_EXCEEDED',
                'description' => "{$userType} registry @{$samAccountName} exceeded login attempts ({$attempts} attempts). Account temporarily locked by rate limiter.",
                'severity' => 'warning',
                'ip_address' => $request->ip()
            ]);

            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return back()->with('error', "Too many login attempts. Please try again in {$seconds} seconds.")->withInput();
        }

        return back()->with('error', $errorMessage)->withInput();
    }

}
