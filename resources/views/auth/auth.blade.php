@extends('layouts.auth')

@php
    $adminUser = \App\Models\User::where('is_admin', true)->where('is_active', true)->first();
    $adminExists = $adminUser ? true : false;
    $adminOnline = $adminUser && $adminUser->is_online ? true : false;
@endphp

@section('content')
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
            max-width: 700px;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 40px;
            padding: 2rem 3.5rem; /* Reduced base padding for login */
            backdrop-filter: blur(40px);
            box-shadow: 0 40px 100px -20px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .auth-vault.mode-register {
            padding: 3rem 3.5rem; /* Increased padding for registration */
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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

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
        @media (max-width: 768px) {
            .auth-vault {
                padding: 2rem 1.5rem;
                border-radius: 30px;
            }

            .auth-vault.mode-register {
                padding: 2.5rem 1.5rem;
            }

            .auth-header h2 {
                font-size: 1.5rem;
            }

            #loginForm { padding-right: 1rem; }
            #registerForm { padding-left: 1rem; }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
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
        }

        @media (max-width: 480px) {
            body {
                padding: 0.5rem;
                align-items: flex-start; /* Better for long forms on short screens */
                padding-top: 2rem;
            }

            .auth-vault {
                padding: 1.5rem 1rem;
                border-radius: 24px;
                box-shadow: 0 20px 50px -10px rgba(0,0,0,0.05);
            }

            .auth-vault.mode-register {
                padding: 2rem 1rem;
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
                height: 50px;
                font-size: 0.9rem;
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
                <div style="margin-left: auto; width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 0 3px rgba(16,185,129,0.2);"></div>
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
            <div style="position: absolute; bottom: -100px; right: -100px; width: 250px; height: 250px; background: #6366f1; opacity: 0.08; filter: blur(80px); border-radius: 50%;"></div>

            <!-- Header Section (Compacted) -->
            <div class="auth-header">
                <div class="auth-logo-box">
                    <img src="{{ asset('img/NACOC1.png') }}" style="width: 32px;" alt="Logo">
                </div>
                <h2 style="color: var(--text-main); font-size: 1.6rem; font-weight: 950; letter-spacing: -0.04em; margin-bottom: 0.15rem;">NACOC</h2>
                <p style="color: var(--text-muted); font-weight: 700; font-size: 0.75rem; letter-spacing: 0.08em; text-transform: uppercase;">Inventory Management System</p>

                <!-- Target Interface Selector -->
                <div class="interface-selector" style="margin-top: 1.5rem; width: 100%; max-width: 300px; margin-inline: auto;">
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

                <!-- Dynamic Auth Tabs Container -->
                <div class="auth-tabs" id="authTabsContainer" style="margin-top: 1.5rem; margin-inline: auto; display: flex; justify-content: center; transition: all 0.3s ease;">
                    <!-- Dynamically populated by setInterface -->
                </div>
            </div>

            <!-- Dynamic Form Viewport -->
            <div class="auth-viewport" style="margin-top: 1.5rem; transition: height 0.5s ease; overflow: hidden; position: relative;">



                <div id="formsSlider" style="display: flex; width: 200%; transition: transform 0.6s cubic-bezier(0.65, 0, 0.35, 1);">
                    <!-- Login Side -->
                    <div id="loginForm" class="auth-form-side" style="width: 50%; padding: 0 10px; opacity: 1;">
                        <form action="{{ route('login') }}" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
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
                                <a href="{{ route('password.request') }}" id="forgotLink" style="font-size: 0.8rem; font-weight: 800; color: var(--primary); text-decoration: none;">Forgot Access?</a>
                            </div>

                            <button type="submit" class="auth-btn-primary" style="background: var(--primary) !important; height: 56px; font-size: 1rem; border-radius: 20px; margin-top: 1rem; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);">
                                <span>Login</span>
                            </button>
                        </form>
                    </div>

                    <!-- Register Side (Admin Only) -->
                    <div id="registerForm" class="auth-form-side" style="width: 50%; padding: 0 10px; opacity: 0;">
                        <form action="{{ route('register') }}" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
                            @csrf
                            <input type="hidden" name="role" value="Admin">

                            <div class="input-modern-group">
                                <label>Full Name <span style="color: #ef4444;">*</span></label>
                                <div class="input-wrapper">
                                    <div class="icon-box"><i data-lucide="user-plus"></i></div>
                                    <input type="text" name="name" placeholder="Full Name" required>
                                </div>
                            </div>

                            <div class="input-modern-group">
                                <label>Username <span style="color: #ef4444;">*</span></label>
                                <div class="input-wrapper">
                                    <div class="icon-box"><i data-lucide="at-sign"></i></div>
                                    <input type="text" name="username" placeholder="Username" required>
                                </div>
                            </div>

                            <div class="input-modern-group">
                                <label>Password <span style="color: #ef4444;">*</span></label>
                                <div class="input-wrapper">
                                    <div class="icon-box"><i data-lucide="key-round"></i></div>
                                    <input type="password" name="password" placeholder="••••••••" required minlength="8" pattern="(?=.*\d).{8,}" title="Minimum 8 characters, including at least one number">
                                    <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                        <i data-lucide="eye"></i>
                                    </button>
                                </div>
                                <p style="font-size: 0.65rem; color: #64748b; font-weight: 700; margin-top: 6px; padding-left: 4px;">Requirement: Min 8 chars including a number. Cannot match username.</p>
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



                            <button type="submit" class="auth-btn-primary" style="background: var(--primary) !important; height: 56px; font-size: 1rem; border-radius: 20px; margin-top: 1rem; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);">
                                <span>Register Account</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

<style>
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
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
    }

    .tab-btn:hover:not(.active) {
        color: var(--primary) !important;
        background: rgba(99, 102, 241, 0.08) !important;
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
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        margin-top: 1rem;
    }

    .auth-btn-primary:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(99, 102, 241, 0.45);
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
</style>

<div class="toast-container" id="toastContainer"></div>

<script>
    // Server-side state passed to JS
    const ADMIN_EXISTS = {{ $adminExists ? 'true' : 'false' }};
    const ADMIN_ONLINE = {{ $adminOnline ? 'true' : 'false' }};

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
                <button type="button" class="tab-btn" id="tab-register" onclick="toggleAuth('register')">Registry</button>
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
            toggleAuth('login');

            // Show lockout overlay if admin is already online
            if (ADMIN_ONLINE) {
                const lockOverlay = document.getElementById('adminOnlineOverlay');
                if (lockOverlay) lockOverlay.style.display = 'flex';
                const formSlider = document.getElementById('formsSlider');
                if (formSlider) {
                    formSlider.style.pointerEvents = 'none';
                    formSlider.setAttribute('inert', '');
                }
            }
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

            tabsContainer.innerHTML = `
                <button type="button" class="tab-btn active" style="background: rgba(99, 102, 241, 0.1); color: var(--primary); width: 100%; max-width: 250px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Secure Authentication
                </button>
            `;
            tabsContainer.style.background = 'transparent';
            tabsContainer.style.border = 'none';
            tabsContainer.style.padding = '0';
            usernameLabel.innerHTML = 'Username <span style="color: #ef4444;">*</span>';

            if (usernameInput) {
                usernameInput.value = '';
                usernameInput.removeAttribute('readonly');
                usernameInput.style.opacity = '1';
            }

            toggleAuth('login');
        }

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

        if (mode === 'register') {
            slider.style.transform = 'translateX(-50%)';
            if (tabLogin) tabLogin.classList.remove('active');
            if (tabRegister) tabRegister.classList.add('active');
            if (loginForm) loginForm.style.opacity = '0';
            if (registerForm) registerForm.style.opacity = '1';
        } else {
            slider.style.transform = 'translateX(0)';
            if (tabLogin) tabLogin.classList.add('active');
            if (tabRegister) tabRegister.classList.remove('active');
            if (loginForm) loginForm.style.opacity = '1';
            if (registerForm) registerForm.style.opacity = '0';
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
            <div style="background: ${type === 'success' ? 'rgba(99, 102, 241, 0.1)' : 'rgba(239, 68, 68, 0.1)'}; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: ${type === 'success' ? 'var(--primary)' : '#ef4444'};">
                <i data-lucide="${icon}"></i>
            </div>
            <div style="flex: 1;">
                <p style="font-weight: 800; font-size: 0.85rem; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">${type === 'success' ? 'Status Alpha' : 'System Alert'}</p>
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

    document.addEventListener('DOMContentLoaded', () => {
        // Initialize Default Interface based on session flash
        @if(session('target_admin'))
            const adminPill = Array.from(document.querySelectorAll('.interface-pill')).find(el => el.textContent.trim() === 'Command Center');
            setInterface('admin', adminPill);
        @else
            setInterface('user', document.querySelector('.interface-pill.active'));
        @endif

        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Initialize height
        setTimeout(updateViewportHeight, 400);

        // Flash Messages from Session
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif

        @if(session('error'))
            @if(session('error') == 'wrong password or username account has been deactivated see admin to activate your account')
                Swal.fire({
                    title: '<span style="font-weight: 900; color: #1e293b;">Strategic Command Alert</span>',
                    text: "{{ session('error') }}",
                    icon: 'warning',
                    iconColor: '#ef4444',
                    background: 'rgba(255, 255, 255, 0.95)',
                    backdrop: `rgba(0,0,123,0.1)`,
                    confirmButtonColor: '#4f46e5',
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
@endsection
