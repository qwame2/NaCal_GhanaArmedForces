@extends('layouts.auth')

@php
    $adminUser = \App\Models\User::where('is_admin', true)->where('is_active', true)->first();
    $adminExists = $adminUser ? true : false;
    $adminOnline = $adminUser && $adminUser->is_online ? true : false;
@endphp

@section('content')
    <link rel="stylesheet" href="{{ asset('css/vendor/select2.min.css') }}">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top right, #f8fafc, #e2e8f0);
            padding: 1rem; /* Reduced from 2rem for better mobile fit */
            margin: 0;
        }

        .auth-vault {
            width: 100%;
            max-width: 680px;
            background: rgba(255, 255, 255, 0.97);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 40px;
            padding: 2.5rem 3.5rem;
            backdrop-filter: blur(40px);
            box-shadow: 0 40px 100px -20px rgba(0,0,0,0.1), 0 0 0 1px rgba(136,19,55,0.04);
            position: relative;
            overflow: hidden;
            transition: max-width 0.55s cubic-bezier(0.4, 0, 0.2, 1), padding 0.4s ease, box-shadow 0.4s ease;
        }

        .auth-vault.mode-register {
            max-width: 1000px;
            padding: 2.5rem 4rem 3rem;
            box-shadow: 0 50px 120px -20px rgba(136,19,55,0.12), 0 0 0 1px rgba(136,19,55,0.06);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 1.5rem; /* Reduced from 2rem */
            position: relative;
            z-index: 2;
            transition: all 0.4s ease;
        }

        .auth-vault.mode-register .auth-header {
            margin-bottom: 2.5rem;
        }

        .auth-logo-box {
            width: 55px; /* Slightly smaller */
            height: 55px;
            background: white;
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem; /* Reduced spacing */
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .auth-tabs {
            display: inline-flex;
            background: rgba(0,0,0,0.03);
            padding: 5px;
            border-radius: 18px;
            margin-top: 1rem; /* Reduced from 1.5rem */
            border: 1px solid rgba(0,0,0,0.05);
            width: fit-content;
        }

        .tab-btn {
            padding: 0.6rem 1.75rem;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-weight: 800;
            font-size: 0.8rem;
            cursor: pointer;
            border-radius: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
        }

        .auth-viewport {
            overflow: hidden;
            margin-top: 0.5rem;
            transition: height 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        #formsSlider {
            display: flex;
            align-items: flex-start;
            transition: transform 0.6s cubic-bezier(0.65, 0, 0.35, 1);
            width: 200%;
        }

        .auth-form-side {
            width: 50%;
            flex-shrink: 0;
            transition: opacity 0.4s ease;
        }

        #loginForm {
            padding-right: 2rem;
        }

        #registerForm {
            padding-left: 2rem;
        }

        /* ── Two-column form grid ── */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        /* ── Register form: spacious field stack ── */
        #adminRegisterForm,
        #userSelfRegisterForm {
            gap: 1.6rem !important;
        }

        /* ── Section divider for register forms ── */
        .form-section-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0.25rem 0;
        }
        .form-section-divider .divider-label {
            font-size: 0.68rem;
            font-weight: 900;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.12em;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .form-section-divider .divider-label svg {
            width: 13px;
            height: 13px;
            color: var(--primary);
            opacity: 0.7;
        }
        .form-section-divider .divider-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, rgba(136,19,55,0.15), transparent);
        }
        .form-section-divider .divider-line.right {
            background: linear-gradient(to left, rgba(136,19,55,0.15), transparent);
        }

        /* ── Register field animations ── */
        @keyframes fieldSlideIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        #adminRegisterForm .input-modern-group,
        #userSelfRegisterForm .input-modern-group,
        #adminRegisterForm .form-grid,
        #userSelfRegisterForm .form-grid,
        #adminRegisterForm .form-section-divider,
        #userSelfRegisterForm .form-section-divider {
            animation: fieldSlideIn 0.4s ease both;
        }
        #adminRegisterForm .input-modern-group:nth-child(1),
        #userSelfRegisterForm .input-modern-group:nth-child(1),
        #adminRegisterForm .form-grid:nth-child(1),
        #userSelfRegisterForm .form-grid:nth-child(1) { animation-delay: 0.05s; }
        #adminRegisterForm .input-modern-group:nth-child(2),
        #userSelfRegisterForm .input-modern-group:nth-child(2),
        #adminRegisterForm .form-grid:nth-child(2),
        #userSelfRegisterForm .form-grid:nth-child(2) { animation-delay: 0.10s; }
        #adminRegisterForm .input-modern-group:nth-child(3),
        #userSelfRegisterForm .input-modern-group:nth-child(3),
        #adminRegisterForm .form-grid:nth-child(3),
        #userSelfRegisterForm .form-grid:nth-child(3) { animation-delay: 0.15s; }
        #adminRegisterForm .input-modern-group:nth-child(4),
        #userSelfRegisterForm .input-modern-group:nth-child(4),
        #adminRegisterForm .form-section-divider:nth-child(4),
        #userSelfRegisterForm .form-section-divider:nth-child(4) { animation-delay: 0.20s; }
        #adminRegisterForm .input-modern-group:nth-child(5),
        #userSelfRegisterForm .input-modern-group:nth-child(5) { animation-delay: 0.25s; }
        #adminRegisterForm .input-modern-group:nth-child(6),
        #userSelfRegisterForm .input-modern-group:nth-child(6) { animation-delay: 0.30s; }

        /* ── Taller input icons in register mode ── */
        .auth-vault.mode-register .icon-box {
            height: 60px;
        }
        .auth-vault.mode-register .input-wrapper input,
        .auth-vault.mode-register .input-wrapper select {
            padding: 1.35rem 1.5rem;
        }
        .auth-vault.mode-register .select2-container--default .select2-selection--single {
            height: 60px !important;
        }
        .auth-vault.mode-register .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 58px !important;
        }

        /* ── Register submit button: gradient ── */
        .register-submit-btn {
            background: linear-gradient(135deg, #881337 0%, #4c0519 100%) !important;
            box-shadow: 0 12px 30px rgba(136,19,55,0.35) !important;
            height: 60px !important;
            border-radius: 22px !important;
            font-size: 0.95rem !important;
            letter-spacing: 0.08em !important;
            text-transform: uppercase !important;
        }
        .register-submit-btn:hover {
            background: linear-gradient(135deg, #4c0519 0%, #6d28d9 100%) !important;
            transform: translateY(-4px) !important;
            box-shadow: 0 20px 45px rgba(136,19,55,0.45) !important;
        }

        /* ── Password hint chip ── */
        .pwd-hint-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(136,19,55,0.06);
            border: 1px solid rgba(136,19,55,0.12);
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 0.65rem;
            font-weight: 800;
            color: #881337;
            margin-top: 8px;
            letter-spacing: 0.04em;
        }
        .pwd-hint-chip svg { width: 11px; height: 11px; }

        .avatar-section {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            background: var(--bg-main);
            padding: 1rem;
            border-radius: 28px;
            border: 1px solid var(--border-color);
        }

        /* Mobile & Tablet Responsiveness */
        @media (max-width: 1100px) {
            .auth-vault.mode-register {
                max-width: 860px;
                padding: 2.5rem 3.5rem 3rem;
            }
        }

        @media (max-width: 900px) {
            .auth-vault.mode-register {
                max-width: 680px;
                padding: 2.5rem 3rem 3rem;
            }
        }

        @media (max-width: 768px) {
            .auth-vault {
                padding: 2rem 1.75rem;
                border-radius: 30px;
            }

            .auth-vault.mode-register {
                padding: 2.5rem 1.75rem;
            }

            .auth-header h2 {
                font-size: 1.5rem;
            }

            #loginForm { padding-right: 1rem; }
            #registerForm { padding-left: 1rem; }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }

            .tab-btn {
                padding: 0.5rem 1rem;
                font-size: 0.75rem;
            }

            .avatar-section {
                flex-direction: column;
                text-align: center;
                padding: 1.5rem;
            }

            .avatar-section button {
                width: 100%;
                margin-top: 0.5rem;
            }

            .auth-vault.mode-register .icon-box {
                height: 56px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 0.5rem;
                align-items: flex-start;
                padding-top: 1.5rem;
            }

            .auth-vault {
                padding: 1.75rem 1.25rem;
                border-radius: 24px;
                box-shadow: 0 20px 50px -10px rgba(0,0,0,0.08);
            }

            .auth-vault.mode-register {
                padding: 2rem 1.25rem;
            }

            .auth-tabs {
                width: 100%;
                display: flex;
            }

            .tab-btn {
                flex: 1;
                padding: 0.5rem 0.5rem;
            }

            .auth-btn-primary {
                height: 52px;
                font-size: 0.9rem;
            }

            .auth-vault.mode-register .icon-box {
                height: 54px;
            }
        }

        .interface-selector {
            display: flex;
            background: rgba(0,0,0,0.04);
            padding: 6px;
            border-radius: 20px;
            gap: 6px;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            position: relative;
            z-index: 10;
        }
        .interface-pill {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0.75rem;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            color: var(--text-muted);
            font-weight: 800;
            font-size: 0.8rem;
            user-select: none;
        }
        .interface-pill i {
            width: 16px;
            height: 16px;
            transition: transform 0.3s ease;
        }
        .interface-pill.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
        }
        .interface-pill.active i {
            transform: scale(1.1);
        }
        .interface-pill:hover:not(.active) {
            background: rgba(255,255,255,0.5);
            color: var(--text-main);
        }
        @@keyframes lockOverlayIn {
            from { opacity: 0; transform: scale(0.97); }
            to   { opacity: 1; transform: scale(1); }
        }
        @@keyframes shieldPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.3); }
            50%       { box-shadow: 0 0 0 14px rgba(239, 68, 68, 0); }
        }
        /* Custom selects styles aligned with standard inputs */
    </style>

    {{-- Admin Access Lockout Overlay (fixed, full-screen) --}}
    <div id="adminOnlineOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 9999; flex-direction: column; align-items: center; justify-content: center; text-align: center; animation: lockOverlayIn 0.35s ease;">
        <div style="background: white; padding: 2.5rem 2rem; border-radius: 28px; box-shadow: 0 30px 80px rgba(0,0,0,0.25), 0 0 0 1px rgba(239,68,68,0.12); width: 88%; max-width: 380px;">
            <!-- Pulsing Shield Icon -->
            <div style="width: 72px; height: 72px; background: rgba(239, 68, 68, 0.08); color: #ef4444; border-radius: 22px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; animation: shieldPulse 2.5s infinite;">
                <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <!-- Badge -->
            <div style="display: inline-block; background: #fef2f2; color: #dc2626; font-size: 0.6rem; font-weight: 900; letter-spacing: 0.12em; padding: 4px 12px; border-radius: 999px; border: 1px solid #fecaca; margin-bottom: 1rem; text-transform: uppercase;">Command Access Restricted</div>
            <!-- Title -->
            <h3 style="color: #0f172a; font-size: 1.25rem; font-weight: 950; letter-spacing: -0.03em; margin: 0 0 0.75rem;">System Locked</h3>
            <!-- Message -->
            <p style="color: #64748b; font-size: 0.85rem; font-weight: 600; line-height: 1.65; margin: 0 0 1.75rem;">An Administrator is already actively logged into the system. Concurrent Command Hub access is strictly prohibited for system integrity.</p>
            <!-- Admin Name Badge -->
            @if($adminUser)
            <div style="display: flex; align-items: center; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; text-align: left;">
                <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #0f172a, #334155); color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 0.9rem; flex-shrink: 0;">{{ strtoupper(substr($adminUser->name ?? $adminUser->username, 0, 1)) }}</div>
                <div>
                    <div style="font-size: 0.8rem; font-weight: 900; color: #0f172a;">{{ $adminUser->name ?? $adminUser->username }}</div>
                    <div style="font-size: 0.68rem; color: #94a3b8; font-weight: 700;">Currently Active &middot; Admin</div>
                </div>
                <div style="margin-left: auto; width: 8px; height: 8px; background: #881337; border-radius: 50%; box-shadow: 0 0 0 3px rgba(136,19,55,0.2);"></div>
            </div>
            @endif
            <!-- Dismiss Button -->
            <button onclick="dismissAdminOverlay()" style="width: 100%; padding: 0.9rem; border-radius: 16px; border: none; background: linear-gradient(135deg, #0f172a, #1e293b); color: white; font-weight: 900; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; letter-spacing: 0.04em; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 25px rgba(15,23,42,0.25)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Return to User Access
            </button>
        </div>
    </div>

    <!-- Main Auth Container -->
        <div class="auth-vault glass-monolith">

            <!-- Background Accents -->
            <div style="position: absolute; top: -100px; left: -100px; width: 250px; height: 250px; background: var(--primary); opacity: 0.08; filter: blur(80px); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: -100px; right: -100px; width: 250px; height: 250px; background: #881337; opacity: 0.08; filter: blur(80px); border-radius: 50%;"></div>

            <!-- Header Section (Compacted) -->
            <div class="auth-header">
                <div class="auth-logo-box">
                    <img src="{{ asset('img/NACOC1.png') }}" style="width: 32px;" alt="Logo">
                </div>
                <h2 style="color: var(--text-main); font-size: 1.6rem; font-weight: 950; letter-spacing: -0.04em; margin-bottom: 0.15rem;">NACOC</h2>
                <p style="color: var(--text-muted); font-weight: 700; font-size: 0.75rem; letter-spacing: 0.08em; text-transform: uppercase;">Stores Inventory Management System<span style="color:#881337;">(NSIMs)</span></p>

                <!-- Target Interface Selector (Hidden for security) -->
                <div class="interface-selector" style="display: none !important; margin-top: 1.5rem; width: 100%; max-width: 300px; margin-inline: auto;">
                    <input type="hidden" name="target_interface" id="targetInterfaceInput" value="user">
                    <div class="interface-pill active" onclick="setInterface('user', this)">
                        <i data-lucide="layout-grid"></i>
                        <span>User</span>
                    </div>
                    <div class="interface-pill" onclick="setInterface('admin', this)">
                        <i data-lucide="shield-check"></i>
                        <span>Head</span>
                    </div>
                </div>

                <!-- Dynamic Auth Tabs Container (Hidden) -->
                <div class="auth-tabs" id="authTabsContainer" style="display: none !important; margin-top: 1.5rem; margin-inline: auto; justify-content: center; transition: all 0.3s ease;">
                    <!-- Dynamically populated by setInterface -->
                </div>
            </div>

            <!-- Dynamic Form Viewport -->
            <div class="auth-viewport" style="margin-top: 1.5rem; transition: height 0.5s ease; overflow: hidden; position: relative;">



                <div id="formsSlider" style="display: flex; width: 200%; transition: transform 0.6s cubic-bezier(0.65, 0, 0.35, 1);">
                    <!-- Login Side -->
                    <div id="loginForm" class="auth-form-side" style="width: 50%; padding: 0 10px; opacity: 1;">
                        <form id="loginSubmitForm" action="{{ route('login') }}" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
                            @csrf
                            <div class="input-modern-group">
                                <label id="usernameLabel">Username <span style="color: #ef4444;">*</span></label>
                                <div class="input-wrapper">
                                    <div class="icon-box">
                                        <i data-lucide="user"></i>
                                    </div>
                                    <input type="text" name="username" id="loginUsername" placeholder="Username" required>
                                </div>
                            </div>

                            <div class="input-modern-group">
                                <label>Password <span style="color: #ef4444;">*</span></label>
                                <div class="input-wrapper">
                                    <div class="icon-box">
                                        <i data-lucide="key-round"></i>
                                    </div>
                                    <input type="password" name="password" placeholder="••••••••" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                        <i data-lucide="eye"></i>
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="target_interface" id="loginInterfaceSync" value="user">

                            <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 0.25rem; gap: 10px; flex-wrap: wrap;">
                                <a href="{{ route('password.request') }}" id="forgotLink" style="font-size: 0.8rem; font-weight: 800; color: var(--primary); text-decoration: none;">Forgot Password?</a>
                            </div>

                            <button type="submit" class="auth-btn-primary" style="background: var(--primary) !important; height: 56px; font-size: 1rem; border-radius: 20px; margin-top: 1rem; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 10px 25px rgba(136, 19, 55, 0.3);">
                                <span>Login</span>
                            </button>

                            <div id="registerToggleContainer" style="text-align: center; margin-top: 1rem; font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">
                                Don't have an account? <a href="javascript:void(0)" onclick="toggleAuth('register')" style="color: var(--primary); text-decoration: none;">Register</a>
                            </div>
                        </form>
                    </div>

                    <!-- Register Side -->
                    <div id="registerForm" class="auth-form-side" style="width: 50%; padding: 0 10px; opacity: 0;">
                        <!-- Initial Admin Registration (Admin Only) -->
                        <form id="adminRegisterForm" action="{{ route('register') }}" method="POST" style="display: flex; flex-direction: column; gap: 1.6rem;">
                            @csrf
                            <input type="hidden" name="role" value="Head of Stores">

                            {{-- Section: Identity --}}
                            <div class="form-section-divider">
                                <div class="divider-line"></div>
                                <div class="divider-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    Identity Information
                                </div>
                                <div class="divider-line right"></div>
                            </div>

                            <div class="form-grid">
                                <div class="input-modern-group">
                                    <label>Full Name <span style="color: #ef4444;">*</span></label>
                                    <div class="input-wrapper">
                                        <div class="icon-box"><i data-lucide="user-plus"></i></div>
                                        <input type="text" name="name" placeholder="e.g. John Mensah" required>
                                    </div>
                                </div>

                                <div class="input-modern-group">
                                    <label>Username <span style="color: #ef4444;">*</span></label>
                                    <div class="input-wrapper">
                                        <div class="icon-box"><i data-lucide="at-sign"></i></div>
                                        <input type="text" name="username" placeholder="e.g. jmensah" required>
                                    </div>
                                </div>
                            </div>

                            <div class="input-modern-group">
                                <label>Rank <span style="color: #ef4444;">*</span></label>
                                <div class="input-wrapper">
                                    <div class="icon-box"><i data-lucide="award"></i></div>
                                    <input type="text" name="rank" placeholder="e.g. SNCO, NCO" required>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="input-modern-group">
                                    <label>Role</label>
                                    <div class="input-wrapper" style="background: rgba(0,0,0,0.03); cursor: not-allowed;">
                                        <div class="icon-box"><i data-lucide="shield"></i></div>
                                        <input type="text" value="Head of Stores" readonly style="color: var(--text-muted); cursor: not-allowed;">
                                    </div>
                                </div>

                                <div class="input-modern-group">
                                    <label>Staff ID <span style="color: #ef4444;">*</span></label>
                                    <input type="hidden" name="service_number" id="adminStaffIdHidden">
                                    <div class="staff-id-split-wrapper">
                                        <select id="adminStaffIdPrefix" class="staff-id-prefix-select" onchange="syncStaffId('admin')">
                                            <option value="JD">JD</option>
                                            <option value="SD">SD</option>
                                        </select>
                                        <span class="staff-id-divider"></span>
                                        <input type="text" id="adminStaffIdSuffix" class="staff-id-suffix-input" placeholder="e.g. 8942" pattern="[0-9A-Za-z\-]+" required oninput="syncStaffId('admin')">
                                    </div>
                                    <span class="staff-id-preview" id="adminStaffIdPreview">Preview: <strong>JD-</strong></span>
                                </div>
                            </div>

                            {{-- Section: Security --}}
                            <div class="form-section-divider">
                                <div class="divider-line"></div>
                                <div class="divider-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    Security Credentials
                                </div>
                                <div class="divider-line right"></div>
                            </div>

                            <div class="form-grid">
                                <div class="input-modern-group">
                                    <label>Password <span style="color: #ef4444;">*</span></label>
                                    <div class="input-wrapper">
                                        <div class="icon-box"><i data-lucide="key-round"></i></div>
                                        <input type="password" name="password" placeholder="••••••••" required minlength="8" pattern="(?=.*\d).{8,}" title="Minimum 8 characters, including at least one number">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i data-lucide="eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="input-modern-group">
                                    <label>Confirm Password <span style="color: #ef4444;">*</span></label>
                                    <div class="input-wrapper">
                                        <div class="icon-box"><i data-lucide="check-circle-2"></i></div>
                                        <input type="password" name="password_confirmation" placeholder="••••••••" required minlength="8" pattern="(?=.*\d).{8,}" title="Minimum 8 characters, including at least one number">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i data-lucide="eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: -0.5rem;">
                                <span class="pwd-hint-chip">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                    Min 8 characters &bull; Must include a number &bull; Cannot match username
                                </span>
                            </div>

                            <button type="submit" class="auth-btn-primary register-submit-btn" style="margin-top: 0.5rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                                <span>Register Account</span>
                            </button>

                            <div style="text-align: center; margin-top: 1rem; font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">
                                Already have an account? <a href="javascript:void(0)" onclick="toggleAuth('login')" style="color: var(--primary); text-decoration: none;">Login</a>
                            </div>
                        </form>

                        <!-- User Self Registration -->
                        <form id="userSelfRegisterForm" action="{{ route('self-register') }}" method="POST" style="display: none; flex-direction: column; gap: 1.6rem;">
                            @csrf

                            {{-- Section: Identity --}}
                            <div class="form-section-divider">
                                <div class="divider-line"></div>
                                <div class="divider-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    Personal Information
                                </div>
                                <div class="divider-line right"></div>
                            </div>

                            <div class="form-grid">
                                <div class="input-modern-group">
                                    <label>Full Name <span style="color: #ef4444;">*</span></label>
                                    <div class="input-wrapper">
                                        <div class="icon-box"><i data-lucide="user"></i></div>
                                        <input type="text" name="name" placeholder="e.g. John Mensah" required>
                                    </div>
                                </div>

                                <div class="input-modern-group">
                                    <label>Username <span style="color: #ef4444;">*</span></label>
                                    <div class="input-wrapper">
                                        <div class="icon-box"><i data-lucide="at-sign"></i></div>
                                        <input type="text" name="username" placeholder="e.g. jmensah" required>
                                    </div>
                                </div>
                            </div>

                            <div class="input-modern-group">
                                <label>Phone Number <span style="color: #ef4444;">*</span></label>
                                <div class="input-wrapper">
                                    <div class="icon-box"><i data-lucide="phone"></i></div>
                                    <input type="tel" name="phone" placeholder="e.g. +233241234567" required>
                                </div>
                            </div>

                            {{-- Section: Department & Assignment --}}
                            <div class="form-section-divider">
                                <div class="divider-line"></div>
                                <div class="divider-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                    Department &amp; Assignment
                                </div>
                                <div class="divider-line right"></div>
                            </div>

                            <div class="form-grid" style="margin-top: 0.5rem;">
                                {{-- Requisitioner Toggle Switch --}}
                                <div class="input-modern-group" style="grid-column: span 2; display: flex; align-items: center; justify-content: space-between; background: rgba(136, 19, 55, 0.03); padding: 0.9rem 1.25rem; border: 1px dashed rgba(136, 19, 55, 0.2); border-radius: 20px;">
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <span style="font-size: 0.85rem; font-weight: 800; color: var(--text-main);">Register as Requisitioner</span>
                                        <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600;">Toggle if you are requesting items on behalf of your department</span>
                                    </div>
                                    <label class="switch-container" style="position: relative; display: inline-block; width: 50px; height: 26px; user-select: none;">
                                        <input type="checkbox" name="is_requisitioner" id="isRequisitionerToggle" value="1" style="opacity: 0; width: 0; height: 0;">
                                        <span class="switch-slider round" style="position: absolute; cursor: pointer; inset: 0; background-color: #cbd5e1; transition: .4s; border-radius: 34px;"></span>
                                    </label>
                                </div>

                                <div class="input-modern-group">
                                    <label>Department <span style="color: #ef4444;">*</span></label>
                                    <div class="input-wrapper">
                                        <div class="icon-box"><i data-lucide="network"></i></div>
                                        <select name="department" id="selfDeptSelect" class="premium-select-input" required style="width: 100%; border: none; background: transparent; height: 100%; font-weight: 600; font-size: 1rem; outline: none; padding-left: 0.5rem; color: #0f172a; text-align: left;">
                                            <option value="">-- Select Department --</option>
                                            <optgroup label="INVESTIGATIONS & INTELLIGENCE DIRECTORATE">
                                                <option value="Intelligence Department">Intelligence Department</option>
                                                <option value="Investigations Department">Investigations Department</option>
                                                <option value="Forensic Science Department">Forensic Science Department</option>
                                                <option value="Asset recovery & Management Department">Asset recovery & Management Department</option>
                                                <option value="Strategic Intelligence Oversight Department">Strategic Intelligence Oversight Department</option>
                                            </optgroup>
                                            <optgroup label="LICENSING & REGULATORY DIRECTORATE">
                                                <option value="Cannabis Regulations Department">Cannabis Regulations Department</option>
                                                <option value="Precursor Diversion Department">Precursor Diversion Department</option>
                                            </optgroup>
                                            <optgroup label="DRUG DEMAND REDUCTION DIRECTORATE">
                                                <option value="Drug Education & Prevention Department">Drug Education & Prevention Department</option>
                                                <option value="Rehabilitation & Social Re-integration Department">Rehabilitation & Social Re-integration Department</option>
                                                <option value="Harm Reduction Department">Harm Reduction Department</option>
                                                <option value="Alternative Livelihoods Development Department">Alternative Livelihoods Development Department</option>
                                            </optgroup>
                                            <optgroup label="OPERATIONS AND ENFORCEMENT DIRECTORATE">
                                                <option value="Canine Operations Department">Canine Operations Department</option>
                                            </optgroup>
                                            <optgroup label="FINANCE DIRECTORATE">
                                                <option value="Accounts & Budget Department">Accounts & Budget Department</option>
                                                <option value="Payroll & Pension Department">Payroll & Pension Department</option>
                                            </optgroup>
                                            <optgroup label="RESEARCH POLICY & PLANNING DIRECTORATE">
                                                <option value="Research Policy Planning Monitoring & Evaluation Department">Research Policy Planning Monitoring & Evaluation Department</option>
                                                <option value="Professional Standards Department">Professional Standards Department</option>
                                            </optgroup>
                                            <optgroup label="ADMINISTRATION DIRECTORATE">
                                                <option value="General Services Department">General Services Department</option>
                                                <option value="ICT Department">ICT Department</option>
                                                <option value="Transport Department">Transport Department</option>
                                                <option value="Procurement Department">Procurement Department</option>
                                                <option value="Project Management Department">Project Management Department</option>
                                                <option value="Stores">Stores</option>
                                            </optgroup>
                                            <optgroup label="HUMAN RESOURCE DIRECTORATE">
                                                <option value="Human Resource Management Department">Human Resource Management Department</option>
                                                <option value="Welfare Department">Welfare Department</option>
                                                <option value="Religious Affairs Department">Religious Affairs Department</option>
                                            </optgroup>
                                            <optgroup label="TRAINING & DEVELOPMENT DIRECTORATE">
                                                <option value="Internal & External Training Department">Internal & External Training Department</option>
                                            </optgroup>
                                            <optgroup label="PUBLIC AFFAIRS AND INTERNATIONAL RELATIONS">
                                                <option value="Public Affairs Department">Public Affairs Department</option>
                                                <option value="International Relations Department">International Relations Department</option>
                                                <option value="Material Development Department">Material Development Department</option>
                                                <option value="Client Service Department">Client Service Department</option>
                                            </optgroup>
                                            <optgroup label="AUDIT DEPARTMENT">
                                                <option value="Audit Department">Audit Department</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                </div>

                                <div class="input-modern-group">
                                    <label>Staff ID <span style="color: #ef4444;">*</span></label>
                                    <input type="hidden" name="service_number" id="userStaffIdHidden">
                                    <div class="staff-id-split-wrapper">
                                        <select id="userStaffIdPrefix" class="staff-id-prefix-select" onchange="syncStaffId('user')">
                                            <option value="JD">JD</option>
                                            <option value="SD">SD</option>
                                        </select>
                                        <span class="staff-id-divider"></span>
                                        <input type="text" id="userStaffIdSuffix" class="staff-id-suffix-input" placeholder="e.g. 8942" pattern="[0-9A-Za-z\-]+" required oninput="syncStaffId('user')">
                                    </div>
                                    <span class="staff-id-preview" id="userStaffIdPreview">Preview: <strong>JD-</strong></span>
                                </div>

                                {{-- Department Head display --}}
                                <div class="input-modern-group" id="deptHeadGroup" style="display: none; grid-column: span 2;">
                                    <label>Departmental Head <span style="color: #ef4444;">*</span></label>
                                    <div class="input-wrapper" style="background: rgba(0,0,0,0.03);">
                                        <div class="icon-box"><i data-lucide="user-check"></i></div>
                                        <input type="text" id="deptHeadName" readonly placeholder="Select a department to view HOD" style="cursor: not-allowed; color: var(--text-muted);">
                                    </div>
                                    <div id="deptHeadWarning" style="display: none; margin-top: 0.5rem; padding: 0.75rem 1rem; background: rgba(239, 68, 68, 0.06); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 12px; font-size: 0.75rem; font-weight: 700; color: #ef4444; align-items: center; gap: 8px;">
                                        <i data-lucide="alert-circle" style="width: 14px; height: 14px; flex-shrink: 0; color: #ef4444;"></i>
                                        <span>Strategic Alert: No registered &amp; approved Department Head found for this department. Registration is locked.</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Section: Security --}}
                            <div class="form-section-divider">
                                <div class="divider-line"></div>
                                <div class="divider-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    Security Credentials
                                </div>
                                <div class="divider-line right"></div>
                            </div>

                            <div class="form-grid">
                                <div class="input-modern-group">
                                    <label>Password <span style="color: #ef4444;">*</span></label>
                                    <div class="input-wrapper">
                                        <div class="icon-box"><i data-lucide="key-round"></i></div>
                                        <input type="password" name="password" placeholder="••••••••" required minlength="8" pattern="(?=.*\d).{8,}" title="Minimum 8 characters, including at least one number">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i data-lucide="eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="input-modern-group">
                                    <label>Confirm Password <span style="color: #ef4444;">*</span></label>
                                    <div class="input-wrapper">
                                        <div class="icon-box"><i data-lucide="check-circle-2"></i></div>
                                        <input type="password" name="password_confirmation" placeholder="••••••••" required minlength="8" pattern="(?=.*\d).{8,}" title="Minimum 8 characters, including at least one number">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i data-lucide="eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: -0.5rem;">
                                <span class="pwd-hint-chip">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                    Min 8 characters &bull; Must include a number &bull; Cannot match username
                                </span>
                            </div>

                            <button type="submit" class="auth-btn-primary register-submit-btn" style="margin-top: 0.5rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <span>Submit Registration Request</span>
                            </button>

                            <div style="text-align: center; margin-top: 1rem; font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">
                                Already have an account? <a href="javascript:void(0)" onclick="toggleAuth('login')" style="color: var(--primary); text-decoration: none;">Login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

<style>
    /* ── Staff ID Split Input ── */
    .staff-id-split-wrapper {
        display: flex;
        align-items: center;
        background: var(--bg-main, #f8fafc);
        border: 1.5px solid var(--border-color, #e2e8f0);
        border-radius: 16px;
        overflow: hidden;
        transition: border-color 0.2s, box-shadow 0.2s;
        height: 56px;
    }
    .staff-id-split-wrapper:focus-within {
        border-color: var(--primary, #881337);
        box-shadow: 0 0 0 3px rgba(136, 19, 55, 0.12);
    }
    .staff-id-prefix-select {
        appearance: none;
        -webkit-appearance: none;
        border: none;
        background: linear-gradient(135deg, rgba(136,19,55,0.1), rgba(136,19,55,0.06));
        color: var(--primary, #881337);
        font-weight: 900;
        font-size: 0.95rem;
        padding: 0 18px;
        height: 100%;
        cursor: pointer;
        outline: none;
        letter-spacing: 0.06em;
        min-width: 72px;
        text-align: center;
        transition: background 0.2s;
    }
    .staff-id-prefix-select:hover {
        background: linear-gradient(135deg, rgba(136,19,55,0.18), rgba(136,19,55,0.1));
    }
    .staff-id-divider {
        width: 1.5px;
        height: 60%;
        background: var(--border-color, #e2e8f0);
        flex-shrink: 0;
    }
    .staff-id-suffix-input {
        flex: 1;
        border: none;
        background: transparent;
        outline: none;
        padding: 0 1rem;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-main, #0f172a);
        height: 100%;
        font-family: 'SF Mono', 'Monaco', monospace;
        letter-spacing: 0.04em;
    }
    .staff-id-suffix-input::placeholder {
        color: #94a3b8;
        font-weight: 500;
        font-family: inherit;
    }
    .staff-id-preview {
        display: block;
        margin-top: 6px;
        font-size: 0.68rem;
        font-weight: 700;
        color: var(--text-muted, #64748b);
        letter-spacing: 0.04em;
    }
    .staff-id-preview strong {
        color: var(--primary, #881337);
        font-family: 'SF Mono', 'Monaco', monospace;
        font-size: 0.75rem;
    }
    /* Taller variant in register mode */
    .auth-vault.mode-register .staff-id-split-wrapper {
        height: 60px;
    }

    /* Requisitioner Toggle Switch Styles */
    .switch-container input:checked + .switch-slider {
        background-color: var(--primary) !important;
    }
    .switch-container input:focus + .switch-slider {
        box-shadow: 0 0 1px var(--primary);
    }
    .switch-container input:checked + .switch-slider:before {
        transform: translateX(24px) !important;
    }
    .switch-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    .input-modern-group label {
        display: block;
        font-size: 0.75rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 10px;
        padding-left: 4px;
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        background: var(--bg-main);
        transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        overflow: hidden;
    }

    .input-wrapper:focus-within {
        background: white;
        border-color: var(--primary);
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        transform: translateY(-2px);
    }

    .input-wrapper.error {
        border-color: #ef4444 !important;
        background: rgba(239, 68, 68, 0.02);
    }

    .icon-box {
        padding-left: 1.25rem;
        padding-right: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        height: 56px;
    }

    .icon-box::after {
        content: '';
        position: absolute;
        right: 0;
        height: 20px;
        width: 1px;
        background: var(--border-color);
        transition: all 0.3s ease;
    }

    .input-wrapper:focus-within .icon-box::after {
        background: var(--primary);
        height: 28px;
    }

    .icon-box i,
    .icon-box svg {
        width: 20px;
        height: 20px;
        color: var(--text-muted);
        transition: all 0.4s ease;
    }

    .input-wrapper:focus-within i,
    .input-wrapper:focus-within svg {
        color: var(--primary);
        transform: scale(1.1);
    }

    .input-wrapper input {
        width: 100%;
        padding: 1.25rem 1.5rem;
        border: none;
        background: transparent;
        color: var(--text-main);
        font-weight: 600;
        font-size: 1rem;
        outline: none;
        flex: 1;
    }

    .input-wrapper input::placeholder {
        color: var(--text-muted);
        opacity: 0.5;
    }

    .tab-btn.active {
        background: var(--primary) !important;
        color: white !important;
        box-shadow: 0 10px 20px rgba(136, 19, 55, 0.3);
    }

    .tab-btn:hover:not(.active) {
        color: var(--primary) !important;
        background: rgba(136, 19, 55, 0.08) !important;
        transform: scale(1.02);
    }

    .password-toggle {
        background: transparent;
        border: none;
        padding: 0 1.5rem;
        cursor: pointer;
        color: var(--text-muted);
        opacity: 0.5;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        outline: none;
    }

    .password-toggle:hover {
        opacity: 1;
        color: var(--primary);
        transform: scale(1.1);
    }

    .password-toggle i,
    .password-toggle svg {
        width: 18px;
        height: 18px;
    }

    .auth-btn-primary {
        width: 100%;
        padding: 1.25rem;
        border-radius: 18px;
        border: none;
        background: var(--primary);
        color: white;
        font-weight: 900;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 10px 30px rgba(136, 19, 55, 0.3);
        margin-top: 1rem;
    }

    .auth-btn-primary:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(136, 19, 55, 0.45);
        filter: brightness(1.1);
    }

    .auth-btn-primary i {
        width: 20px;
        transition: transform 0.4s;
    }

    .auth-btn-primary:hover i {
        transform: translateX(5px);
    }

    @media (max-width: 992px) {
        .auth-container {
            flex-direction: column;
            border-radius: 30px;
        }
        .auth-visual {
            padding: 3rem !important;
            height: 300px;
            flex-shrink: 0;
            display: none !important; /* Hide left panel on mobile for cleaner look */
        }
        .auth-form-container {
            padding: 2.5rem !important;
        }
    }

    /* Toast Notifications */
    .toast-container {
        position: fixed;
        top: 2rem;
        right: 2rem;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .toast {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 1.25rem 1.75rem;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 320px;
        transform: translateX(120%);
        transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .toast.show {
        transform: translateX(0);
    }

    .toast-success { border-left: 4px solid var(--primary); }
    .toast-error { border-left: 4px solid #ef4444; }

    /* Keyframes */
    @keyframes shake {
        10%, 90% { transform: translate3d(-1px, 0, 0); }
        20%, 80% { transform: translate3d(2px, 0, 0); }
        30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
        40%, 60% { transform: translate3d(4px, 0, 0); }
    }

    .auth-form-group {
        animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Select2 Premium Override */
    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        height: 52px !important;
        border: none !important;
        background: transparent !important;
        display: flex !important;
        align-items: center !important;
        padding-left: 0 !important;
        font-size: 1rem !important;
        font-weight: 600 !important;
        color: #0f172a !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #0f172a !important;
        padding-left: 1.25rem !important;
        text-align: left !important;
        line-height: inherit !important;
        display: flex !important;
        align-items: center !important;
        height: 100% !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 50px !important;
        right: 12px !important;
    }

    .select2-dropdown {
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: 0 20px 50px rgba(0,0,0,0.1) !important;
        overflow: hidden !important;
        background: white !important;
        padding: 6px !important;
        z-index: 9999999 !important;
    }

    .select2-search__field {
        border-radius: 10px !important;
        padding: 10px 14px !important;
        border: 1px solid #e2e8f0 !important;
        font-weight: 600 !important;
        outline: none !important;
    }

    .select2-results__option {
        padding: 10px 15px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        color: #334155 !important;
        margin-bottom: 2px !important;
    }

    .select2-results__option--highlighted[aria-selected] {
        background-color: #881337 !important;
        color: white !important;
    }

    .select2-results__group {
        font-size: 0.65rem !important;
        font-weight: 900 !important;
        color: #64748b !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        padding: 12px 15px 6px !important;
        background: #f8fafc !important;
        border-radius: 8px !important;
        margin: 6px 0 2px 0 !important;
        display: block !important;
    }

    /* Select2 Parent Focus Synchronization */
    .input-wrapper.select2-focused {
        background: white !important;
        border-color: var(--primary) !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05) !important;
        transform: translateY(-2px);
    }
    
    .input-wrapper.select2-focused .icon-box::after {
        background: var(--primary) !important;
        height: 28px !important;
    }
    
    .input-wrapper.select2-focused i,
    .input-wrapper.select2-focused svg {
        color: var(--primary) !important;
        transform: scale(1.1) !important;
    }
</style>

<div class="toast-container" id="toastContainer"></div>

<script>
    // Server-side state passed to JS
    const ADMIN_EXISTS = {{ $adminExists ? 'true' : 'false' }};
    const ADMIN_ONLINE = {{ $adminOnline ? 'true' : 'false' }};
    const ALLOW_REGISTRATION = {{ \Illuminate\Support\Facades\Schema::hasTable('settings') && !\App\Models\Setting::get('allow_personnel_registration', true) ? 'false' : 'true' }};

    function checkForgotEligibility() {
        const usernameInput = document.getElementById('loginUsername');
        const forgotLink = document.getElementById('forgotLink');
        const interfaceVal = document.getElementById('targetInterfaceInput').value;

        if (interfaceVal !== 'admin') {
            // User interface: always show the link, reset href
            forgotLink.href = '{{ route('password.request') }}';
            forgotLink.style.display = 'block';
            return;
        }

        // Head interface: only show if a valid Head/Admin username is entered
        const username = usernameInput ? usernameInput.value.trim() : '';
        if (!username) {
            forgotLink.style.display = 'none';
            return;
        }

        fetch(`/api/check-forgot-eligibility?username=${encodeURIComponent(username)}`)
            .then(res => res.json())
            .then(data => {
                if (data.eligible) {
                    // Attach username to the link so forgot password page can pre-fill it
                    forgotLink.href = '{{ route('password.request') }}?username=' + encodeURIComponent(username);
                    forgotLink.style.display = 'block';
                } else {
                    forgotLink.style.display = 'none';
                }
            })
            .catch(err => {
                console.error(err);
                forgotLink.style.display = 'none';
            });
    }

    /* ── Staff ID Sync ── */
    function syncStaffId(formType) {
        const prefix  = document.getElementById(formType === 'admin' ? 'adminStaffIdPrefix' : 'userStaffIdPrefix').value;
        const suffix  = (document.getElementById(formType === 'admin' ? 'adminStaffIdSuffix' : 'userStaffIdSuffix').value || '').trim();
        const hidden  = document.getElementById(formType === 'admin' ? 'adminStaffIdHidden' : 'userStaffIdHidden');
        const preview = document.getElementById(formType === 'admin' ? 'adminStaffIdPreview' : 'userStaffIdPreview');
        const value   = suffix ? `${prefix}-${suffix}` : '';
        if (hidden)  hidden.value = value;
        if (preview) preview.innerHTML = `Preview: <strong>${prefix}-${suffix || ''}</strong>`;
    }

    // Ensure hidden service_number is always set before submit for both forms
    document.addEventListener('DOMContentLoaded', function () {
        // Init previews
        syncStaffId('admin');
        syncStaffId('user');

        const adminForm = document.getElementById('adminRegisterForm');
        if (adminForm) {
            adminForm.addEventListener('submit', function (e) {
                syncStaffId('admin');
                const hidden = document.getElementById('adminStaffIdHidden');
                if (!hidden || !hidden.value.trim()) {
                    e.preventDefault();
                    alert('Please enter your Staff ID number.');
                    document.getElementById('adminStaffIdSuffix').focus();
                }
            });
        }

        const userForm = document.getElementById('userSelfRegisterForm');
        if (userForm) {
            userForm.addEventListener('submit', function (e) {
                syncStaffId('user');
                const hidden = document.getElementById('userStaffIdHidden');
                if (!hidden || !hidden.value.trim()) {
                    e.preventDefault();
                    alert('Please enter your Staff ID number.');
                    document.getElementById('userStaffIdSuffix').focus();
                }
            });
        }
    });


    function updateViewportHeight() {
        const slider = document.getElementById('formsSlider');
        const isRegister = slider.style.transform.includes('translateX(-50%)');
        const activeForm = isRegister ? document.getElementById('registerForm') : document.getElementById('loginForm');
        const viewport = document.querySelector('.auth-viewport');

        if (activeForm && viewport) {
            const height = activeForm.scrollHeight;
            viewport.style.height = height + 'px';
        }
    }

    function setInterface(val, el) {

        document.getElementById('targetInterfaceInput').value = val;
        document.getElementById('loginInterfaceSync').value = val;

        if (el) {
            document.querySelectorAll('.interface-pill').forEach(p => p.classList.remove('active'));
            el.classList.add('active');
        }

        const tabsContainer = document.getElementById('authTabsContainer');
        const usernameLabel = document.getElementById('usernameLabel');
        const usernameInput = document.getElementById('loginUsername');

        if (val === 'admin') {
            document.getElementById('forgotLink').style.display = 'none';
            tabsContainer.innerHTML = `
                <button type="button" class="tab-btn active" id="tab-login" onclick="toggleAuth('login')">Login</button>
                @if(!$adminExists)
                <button type="button" class="tab-btn" id="tab-register" onclick="toggleAuth('register')">Register</button>
                @endif
            `;
            tabsContainer.style.background = 'rgba(0,0,0,0.03)';
            tabsContainer.style.border = '1px solid rgba(0,0,0,0.05)';
            tabsContainer.style.padding = '5px';
            usernameLabel.innerHTML = 'Username <span style="color: #ef4444;">*</span>';

            if (usernameInput) {
                usernameInput.value = '';
                usernameInput.removeAttribute('readonly');
                usernameInput.style.opacity = '1';
            }
            if (document.getElementById('adminRegisterForm')) document.getElementById('adminRegisterForm').style.display = 'flex';
            if (document.getElementById('userSelfRegisterForm')) document.getElementById('userSelfRegisterForm').style.display = 'none';
            toggleAuth('login');

            // Show lockout overlay if admin is already online
            // Disabled to allow Main Admin (Department Head) to authenticate through the Head terminal.
            // Concurrent Admin session locks are securely handled by the backend authentication logic.
        } else {
            // Switching back to Personnel — always dismiss lockout overlay
            const overlay = document.getElementById('adminOnlineOverlay');
            if (overlay) overlay.style.display = 'none';
            const slider = document.getElementById('formsSlider');
            if (slider) {
                slider.style.pointerEvents = 'auto';
                slider.removeAttribute('inert');
            }

            document.getElementById('forgotLink').style.display = 'block';

            if (ALLOW_REGISTRATION) {
                tabsContainer.innerHTML = `
                    <button type="button" class="tab-btn active" id="tab-login" onclick="toggleAuth('login')">Login</button>
                    <button type="button" class="tab-btn" id="tab-register" onclick="toggleAuth('register')">Register</button>
                `;
            } else {
                tabsContainer.innerHTML = `
                    <button type="button" class="tab-btn active" id="tab-login" onclick="toggleAuth('login')">Login</button>
                `;
            }
            tabsContainer.style.background = 'rgba(0,0,0,0.03)';
            tabsContainer.style.border = '1px solid rgba(0,0,0,0.05)';
            tabsContainer.style.padding = '5px';
            usernameLabel.innerHTML = 'Username <span style="color: #ef4444;">*</span>';

            if (usernameInput) {
                usernameInput.value = '';
                usernameInput.removeAttribute('readonly');
                usernameInput.style.opacity = '1';
            }
            if (document.getElementById('adminRegisterForm')) document.getElementById('adminRegisterForm').style.display = 'none';
            if (document.getElementById('userSelfRegisterForm')) document.getElementById('userSelfRegisterForm').style.display = 'flex';

            toggleAuth('login');
        }

        const toggleContainer = document.getElementById('registerToggleContainer');
        if (toggleContainer) {
            if (val === 'admin') {
                toggleContainer.style.display = (!ADMIN_EXISTS) ? 'block' : 'none';
            } else {
                toggleContainer.style.display = ALLOW_REGISTRATION ? 'block' : 'none';
            }
        }

        checkForgotEligibility();
        updateViewportHeight();
    }

    function dismissAdminOverlay() {
        // Dismiss the lockout overlay and switch back to Personnel pill
        const overlay = document.getElementById('adminOnlineOverlay');
        if (overlay) overlay.style.display = 'none';
        // Re-activate the Personnel pill
        const pills = document.querySelectorAll('.interface-pill');
        pills.forEach(p => p.classList.remove('active'));
        if (pills[0]) pills[0].classList.add('active');
        setInterface('user', null);
    }

    function toggleAuth(mode) {
        const slider = document.getElementById('formsSlider');
        const tabLogin = document.getElementById('tab-login');
        const tabRegister = document.getElementById('tab-register');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const vault = document.querySelector('.auth-vault');

        if (mode === 'register') {
            slider.style.transform = 'translateX(-50%)';
            if (tabLogin) tabLogin.classList.remove('active');
            if (tabRegister) tabRegister.classList.add('active');
            if (loginForm) loginForm.style.opacity = '0';
            if (registerForm) registerForm.style.opacity = '1';
            if (vault) vault.classList.add('mode-register');
        } else {
            slider.style.transform = 'translateX(0)';
            if (tabLogin) tabLogin.classList.add('active');
            if (tabRegister) tabRegister.classList.remove('active');
            if (loginForm) loginForm.style.opacity = '1';
            if (registerForm) registerForm.style.opacity = '0';
            if (vault) vault.classList.remove('mode-register');
        }

        updateViewportHeight();
        setTimeout(updateViewportHeight, 300);
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function togglePassword(button) {
        const input = button.parentElement.querySelector('input');

        if (input.type === 'password') {
            input.type = 'text';
            button.innerHTML = '<i data-lucide="eye-off"></i>';
        } else {
            input.type = 'password';
            button.innerHTML = '<i data-lucide="eye"></i>';
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        const icon = type === 'success' ? 'check-circle' : 'alert-circle';

        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div style="background: ${type === 'success' ? 'rgba(136, 19, 55, 0.1)' : 'rgba(239, 68, 68, 0.1)'}; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: ${type === 'success' ? 'var(--primary)' : '#ef4444'};">
                <i data-lucide="${icon}"></i>
            </div>
            <div style="flex: 1;">
                <p style="font-weight: 800; font-size: 0.85rem; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">${type === 'success' ? 'Success' : 'Alert'}</p>
                <p style="font-weight: 600; font-size: 0.85rem; color: var(--text-muted); margin: 0;">${message}</p>
            </div>
        `;

        container.appendChild(toast);
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Animate in
        setTimeout(() => toast.classList.add('show'), 100);

        // Auto-remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 500);
        }, 5000);
    }



    // Handle window resize to keep height accurate
    window.addEventListener('resize', updateViewportHeight);

    function checkDepartmentHead() {
        const toggle = document.getElementById('isRequisitionerToggle');
        const deptSelect = document.getElementById('selfDeptSelect');
        const deptHeadGroup = document.getElementById('deptHeadGroup');
        const deptHeadName = document.getElementById('deptHeadName');
        const deptHeadWarning = document.getElementById('deptHeadWarning');
        const submitBtn = document.querySelector('#userSelfRegisterForm button[type="submit"]');

        if (!toggle || !toggle.checked) {
            if (deptHeadGroup) deptHeadGroup.style.display = 'none';
            if (deptHeadWarning) deptHeadWarning.style.display = 'none';
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            }
            return;
        }

        const dept = deptSelect ? deptSelect.value : '';
        if (deptHeadGroup) deptHeadGroup.style.display = 'block';

        if (!dept) {
            if (deptHeadName) deptHeadName.value = '';
            if (deptHeadWarning) deptHeadWarning.style.display = 'none';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
            }
            return;
        }

        // Fetch Department Head via API
        fetch(`/api/get-department-head?department=${encodeURIComponent(dept)}`)
            .then(res => res.json())
            .then(data => {
                if (data.registered) {
                    if (deptHeadName) deptHeadName.value = data.name;
                    if (deptHeadWarning) deptHeadWarning.style.display = 'none';
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.style.opacity = '1';
                        submitBtn.style.cursor = 'pointer';
                    }
                } else {
                    if (deptHeadName) deptHeadName.value = 'NOT REGISTERED YET';
                    if (deptHeadWarning) deptHeadWarning.style.display = 'flex';
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.style.opacity = '0.5';
                        submitBtn.style.cursor = 'not-allowed';
                    }
                }
                updateViewportHeight();
                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(err => {
                console.error(err);
                if (deptHeadName) deptHeadName.value = 'Error fetching details';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.5';
                }
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const loginFormEl = document.getElementById('loginSubmitForm');
        if (loginFormEl) {
            loginFormEl.addEventListener('submit', () => {
                sessionStorage.setItem('just_logged_in', 'true');
            });
        }

        const usernameInput = document.getElementById('loginUsername');
        if (usernameInput) {
            usernameInput.addEventListener('input', checkForgotEligibility);
            usernameInput.addEventListener('change', checkForgotEligibility);
        }

        // Option caching removed since Sponsor is now a text input



        // Initialize Default Interface based on admin existence (hiding pills from view)
        if (!ADMIN_EXISTS) {
            setInterface('admin', document.querySelectorAll('.interface-pill')[1]);
        } else {
            setInterface('user', document.querySelector('.interface-pill.active'));
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Initialize height
        setTimeout(updateViewportHeight, 400);

        // Flash Messages from Session
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif

        @if(session('error'))
            @if(session('error') == 'wrong password or username account has been deactivated see head of stores to activate your account')
                Swal.fire({
                    title: '<span style="font-weight: 900; color: #1e293b;">Strategic Command Alert</span>',
                    text: "{{ session('error') }}",
                    icon: 'warning',
                    iconColor: '#ef4444',
                    background: 'rgba(255, 255, 255, 0.95)',
                    backdrop: `rgba(0,0,123,0.1)`,
                    confirmButtonColor: '#881337',
                    confirmButtonText: 'UNDERSTOOD',
                    customClass: {
                        popup: 'glass-monolith',
                        confirmButton: 'auth-btn-primary'
                    }
                });
            @else
                showToast("{{ session('error') }}", 'error');
            @endif
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                showToast("{{ $error }}", 'error');
            @endforeach
        @endif
    });
</script>

@push('scripts')
<script src="{{ asset('js/vendor/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {



        // ── Department Select2 ──
        if ($('#selfDeptSelect').length) {
            $('#selfDeptSelect').select2({
                placeholder: "-- Select Department --",
                allowClear: true
            });

            // Listen to select change
            $('#selfDeptSelect').on('change', function() {
                checkDepartmentHead();
            });

            // Listen to toggle change
            $('#isRequisitionerToggle').on('change', function() {
                checkDepartmentHead();
            });


            // Focus state visual integration
            $('#selfDeptSelect').on('select2:open', function() {
                $(this).closest('.input-wrapper').addClass('select2-focused');
            });
            $('#selfDeptSelect').on('select2:close', function() {
                $(this).closest('.input-wrapper').removeClass('select2-focused');
            });

            // Initial check on load (in case of cached inputs or redirect back withInput)
            checkDepartmentHead();
        }

        // Sponsor Select2 configuration removed since Sponsor is now a text input
    });
</script>
@endpush
@endsection
