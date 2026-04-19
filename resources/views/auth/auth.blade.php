@extends('layouts.auth')

@section('content')
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top right, #f8fafc, #e2e8f0);
            padding: 2rem;
            margin: 0;
        }
    </style>

    <!-- Main Auth Container -->
        <div class="auth-vault glass-monolith" style="width: 100%; max-width: 700px; background: rgba(255, 255, 255, 0.95); border: 1px solid rgba(255,255,255,0.8); border-radius: 40px; padding: 2.5rem 3.5rem; backdrop-filter: blur(40px); box-shadow: 0 40px 100px -20px rgba(0,0,0,0.08); position: relative; overflow: hidden;">
            
            <!-- Background Accents -->
            <div style="position: absolute; top: -100px; left: -100px; width: 250px; height: 250px; background: var(--primary); opacity: 0.08; filter: blur(80px); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: -100px; right: -100px; width: 250px; height: 250px; background: #6366f1; opacity: 0.08; filter: blur(80px); border-radius: 50%;"></div>

            <!-- Header Section (Compacted) -->
            <div style="text-align: center; margin-bottom: 2rem; position: relative; z-index: 2;">
                <div style="width: 60px; height: 60px; background: white; border: 1px solid rgba(0,0,0,0.05); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
                    <img src="{{ asset('img/NACOC1.png') }}" style="width: 36px;" alt="Logo">
                </div>
                <h2 style="color: var(--text-main); font-size: 1.8rem; font-weight: 950; letter-spacing: -0.04em; margin-bottom: 0.25rem;">Advanced Registry</h2>
                <p style="color: var(--text-muted); font-weight: 700; font-size: 0.8rem; letter-spacing: 0.08em; text-transform: uppercase;">Strategic Inventory Nexus</p>
                
                <!-- Auth Toggle Tabs -->
                <div class="auth-tabs" style="display: inline-flex; background: rgba(0,0,0,0.03); padding: 5px; border-radius: 18px; margin-top: 1.5rem; border: 1px solid rgba(0,0,0,0.05);">
                    <button type="button" class="tab-btn active" id="tab-login" onclick="toggleAuth('login')" style="padding: 0.6rem 1.75rem; border: none; background: transparent; color: var(--text-main); font-weight: 800; font-size: 0.8rem; cursor: pointer; border-radius: 14px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">Secure Login</button>
                    <button type="button" class="tab-btn" id="tab-register" onclick="toggleAuth('register')" style="padding: 0.6rem 1.75rem; border: none; background: transparent; color: var(--text-muted); font-weight: 800; font-size: 0.8rem; cursor: pointer; border-radius: 14px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">Personnel Registry</button>
                </div>
            </div>

            <!-- Dynamic Form Viewport -->
            <div class="auth-viewport" style="overflow: hidden; margin-top: 1rem;">
                <div id="formsSlider" style="display: flex; transition: transform 0.6s cubic-bezier(0.65, 0, 0.35, 1); width: 200%;">
                    <!-- Login Form -->
                    <div id="loginForm" style="width: 50%; flex-shrink: 0; padding-right: 2rem;">
                        <form action="{{ route('login') }}" method="POST" style="display: flex; flex-direction: column; gap: 2rem;">
                            @csrf
                            <div class="input-modern-group">
                                <label>Personnel Callsign</label>
                                <div class="input-wrapper">
                                    <div class="icon-box">
                                        <i data-lucide="user"></i>
                                    </div>
                                    <input type="text" name="username" placeholder="Username" required>
                                </div>
                            </div>

                            <div class="input-modern-group">
                                <label>Security Key</label>
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

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); cursor: pointer;">
                                    <input type="checkbox" style="width: 18px; height: 18px; border-radius: 6px; border: 1px solid var(--border-color); background: transparent;">
                                    Persistent Session
                                </label>
                                <a href="#" style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-decoration: none;">Forgot Access?</a>
                            </div>

                            <button type="submit" class="auth-btn-primary" style="background: linear-gradient(135deg, var(--primary) 0%, #4338ca 100%); height: 56px; font-size: 1rem; border-radius: 18px; margin-top: 1rem;">
                                <span>Initialize Clearance</span>
                                <i data-lucide="shield-check"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Register Form -->
                    <div id="registerForm" style="width: 50%; flex-shrink: 0; padding-left: 2rem;">
                        <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.1rem;">
                            @csrf
                            
                            <div style="display: flex; gap: 1.5rem; align-items: center; background: var(--bg-main); padding: 1.25rem; border-radius: 28px; border: 1px solid var(--border-color);">
                                <div id="avatarPreview" style="width: 72px; height: 72px; background: white; border-radius: 24px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color); overflow: hidden; flex-shrink: 0; cursor: pointer;" onclick="document.getElementById('avatarInput').click()">
                                    <i data-lucide="camera" style="width: 24px; color: var(--text-muted);"></i>
                                </div>
                                <div style="flex: 1;">
                                    <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Identification</label>
                                    <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
                                    <button type="button" onclick="document.getElementById('avatarInput').click()" style="background: var(--text-main); color: white; border: none; padding: 0.5rem 1rem; border-radius: 12px; font-size: 0.75rem; font-weight: 800; cursor: pointer;">Upload Photo</button>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="input-modern-group">
                                    <label>Personnel Name</label>
                                    <div class="input-wrapper">
                                        <div class="icon-box">
                                            <i data-lucide="user"></i>
                                        </div>
                                        <input type="text" name="name" placeholder="John Doe" required>
                                    </div>
                                </div>
                                <div class="input-modern-group">
                                    <label>Username</label>
                                    <div class="input-wrapper">
                                        <div class="icon-box">
                                            <i data-lucide="hash"></i>
                                        </div>
                                        <input type="text" name="username" placeholder="@j_doe" required>
                                    </div>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="input-modern-group">
                                    <label>Password</label>
                                    <div class="input-wrapper" id="pw-wrapper">
                                        <div class="icon-box">
                                            <i data-lucide="lock"></i>
                                        </div>
                                        <input type="password" name="password" id="reg-password" placeholder="••••••••" required minlength="8" oninput="checkPasswordMatch()">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i data-lucide="eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="input-modern-group">
                                    <label>Confirm Password</label>
                                    <div class="input-wrapper" id="pwc-wrapper">
                                        <div class="icon-box">
                                            <i data-lucide="shield-check"></i>
                                        </div>
                                        <input type="password" name="password_confirmation" id="reg-password-confirm" placeholder="••••••••" required minlength="8" oninput="checkPasswordMatch()">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i data-lucide="eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: -0.5rem; padding-left: 0.5rem;">Requirement: Min. 8 characters with a mix of letters & numbers/symbols.</p>

                            <div style="background: rgba(99, 102, 241, 0.05); padding: 1rem; border-radius: 20px; border: 1px solid rgba(99, 102, 241, 0.1);">
                                <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer;">
                                    <input type="checkbox" required style="width: 20px; height: 20px; margin-top: 2px; accent-color: var(--primary);">
                                    <span style="font-size: 0.75rem; color: var(--text-dark); line-height: 1.5; font-weight: 500;">
                                        I confirm my <span style="color: var(--primary); font-weight: 900;">participation liability</span> for all inventory updates and activity.
                                    </span>
                                </label>
                            </div>

                    <button type="submit" class="auth-btn-primary" style="background: var(--primary); color: white; height: 56px; border-radius: 18px; margin-top: 0.5rem;">
                        <span>Initialize Registry</span>
                        <i data-lucide="arrow-right"></i>
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
    function toggleAuth(mode) {
        const slider = document.getElementById('formsSlider');
        const tabLogin = document.getElementById('tab-login');
        const tabRegister = document.getElementById('tab-register');
        
        if (mode === 'register') {
            slider.style.transform = 'translateX(-50%)';
            if (tabLogin) tabLogin.classList.remove('active');
            if (tabRegister) tabRegister.classList.add('active');
        } else {
            slider.style.transform = 'translateX(0)';
            if (tabLogin) tabLogin.classList.add('active');
            if (tabRegister) tabRegister.classList.remove('active');
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function checkPasswordMatch() {
        const password = document.getElementById('reg-password');
        const confirm = document.getElementById('reg-password-confirm');
        const pwWrapper = document.getElementById('pw-wrapper');
        const pwcWrapper = document.getElementById('pwc-wrapper');

        if (confirm.value.length > 0) {
            if (password.value !== confirm.value) {
                pwWrapper.classList.add('error');
                pwcWrapper.classList.add('error');
            } else {
                pwWrapper.classList.remove('error');
                pwcWrapper.classList.remove('error');
            }
        } else {
            pwWrapper.classList.remove('error');
            pwcWrapper.classList.remove('error');
        }
    }

    function previewAvatar(input) {
        const preview = document.getElementById('avatarPreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
            }
            reader.readAsDataURL(input.files[0]);
        }
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

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Flash Messages from Session
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif

        @if(session('error'))
            showToast("{{ session('error') }}", 'error');
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                showToast("{{ $error }}", 'error');
            @endforeach
        @endif

        // Auto-switch to Registration if there are registry-specific errors
        @if ($errors->hasAny(['name', 'password_confirmation', 'avatar']))
            toggleAuth('register');
        @endif

        @if ($errors->has('password') && !$errors->has('username'))
            toggleAuth('register');
        @endif
    });
</script>
@endsection
