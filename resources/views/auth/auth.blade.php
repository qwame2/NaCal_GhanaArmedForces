@extends('layouts.dashboard')

@section('content')
<div class="auth-wrapper" style="min-height: calc(100vh - 150px); display: flex; align-items: center; justify-content: center; padding: 2rem 0;">
    <div class="auth-container glass-card" style="width: 100%; max-width: 1000px; padding: 0; display: flex; overflow: hidden; border-radius: 40px; border: 1px solid rgba(255,255,255,0.4); box-shadow: 0 40px 100px -20px rgba(0,0,0,0.15); position: relative;">
        
        <!-- Left Visual Panel -->
        <div class="auth-visual" style="flex: 1; background: linear-gradient(135deg, var(--primary) 0%, #a855f7 100%); padding: 4rem; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
            <div style="position: absolute; top: -10%; left: -10%; width: 50%; height: 50%; background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);"></div>
            <div style="position: absolute; bottom: -10%; right: -10%; width: 40%; height: 40%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);"></div>
            
            <div style="position: relative; z-index: 2;">
                <div style="width: 64px; height: 64px; background: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin-bottom: 2rem; box-shadow: 0 15px 35px rgba(0,0,0,0.1);">
                    <img src="{{ asset('img/NACOC1.png') }}" style="width: 40px;" alt="Logo">
                </div>
                <h2 style="color: white; font-size: 2.5rem; font-weight: 900; letter-spacing: -0.04em; line-height: 1.1; margin-bottom: 1.5rem;">Access the <br><span style="color: rgba(255,255,255,0.7);">Inventory Vault.</span></h2>
                <p style="color: rgba(255,255,255,0.85); font-size: 1.1rem; line-height: 1.6; font-weight: 500;">Secure, authenticated access for personnel only. Monitor stock movement and disubursement with precision.</p>
            </div>

            <div style="position: relative; z-index: 2;">
                <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                    <div style="width: 40px; height: 4px; background: white; border-radius: 2px;"></div>
                    <div style="width: 20px; height: 4px; background: rgba(255,255,255,0.3); border-radius: 2px;"></div>
                    <div style="width: 20px; height: 4px; background: rgba(255,255,255,0.3); border-radius: 2px;"></div>
                </div>
                <span style="color: white; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">© 2026 NACOC Systems</span>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="auth-form-container" style="flex: 1.2; background: white; padding: 4rem; position: relative; min-height: 700px;">
            <div id="loginForm" class="auth-form-group">
                <div style="margin-bottom: 3rem;">
                    <h3 style="font-size: 2rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.02em;">Welcome Back</h3>
                    <p style="color: var(--text-muted); font-weight: 600;">Sign in to continue your operations.</p>
                </div>

                <form action="{{ route('login') }}" method="POST" style="display: flex; flex-direction: column; gap: 1.75rem;">
                    @csrf
                    <div class="input-modern-group">
                        <label>Email Address</label>
                        <div class="input-wrapper">
                            <i data-lucide="mail"></i>
                            <input type="email" name="email" placeholder="name@company.com" required>
                        </div>
                    </div>

                    <div class="input-modern-group">
                        <label>Secure Password</label>
                        <div class="input-wrapper">
                            <i data-lucide="lock-keyhole"></i>
                            <input type="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: -0.5rem;">
                        <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); cursor: pointer;">
                            <input type="checkbox" style="width: 18px; height: 18px; border-radius: 6px; border: 2px solid var(--border-color);">
                            Stay Logged In
                        </label>
                        <a href="#" style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-decoration: none;">Reset Password</a>
                    </div>

                    <button type="submit" class="auth-btn-primary">
                        <span>Initialize Session</span>
                        <i data-lucide="chevron-right"></i>
                    </button>
                </form>

                <div style="text-align: center; margin-top: 3rem; pt-3rem; border-top: 1px solid var(--border-color); padding-top: 2rem;">
                    <p style="color: var(--text-muted); font-weight: 600;">Authorized personnel only? <a href="javascript:void(0)" onclick="toggleAuth('register')" style="color: var(--primary); font-weight: 800; text-decoration: none;">Request Account</a></p>
                </div>
            </div>

            <div id="registerForm" class="auth-form-group" style="display: none;">
                <div style="margin-bottom: 2.5rem;">
                    <h3 style="font-size: 2rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.02em;">Account Request</h3>
                    <p style="color: var(--text-muted); font-weight: 600;">Complete the secure registration form.</p>
                </div>

                <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    @csrf
                    <!-- Avatar Upload -->
                    <div style="display: flex; align-items: center; gap: 1.5rem; background: var(--bg-main); padding: 1.25rem; border-radius: 20px; border: 1.5px dashed var(--border-color);">
                        <div id="avatarPreview" style="width: 72px; height: 72px; background: white; border-radius: 18px; display: flex; align-items: center; justify-content: center; border: 2px solid var(--border-color); overflow: hidden; flex-shrink: 0;">
                            <i data-lucide="user" style="width: 32px; color: var(--text-muted);"></i>
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px; text-transform: uppercase;">Official Image</label>
                            <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
                            <button type="button" onclick="document.getElementById('avatarInput').click()" style="background: var(--primary); color: white; border: none; padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.75rem; font-weight: 700; cursor: pointer;">Upload Photo</button>
                        </div>
                    </div>

                    <div class="input-modern-group">
                        <label>Full Personnel Name</label>
                        <div class="input-wrapper">
                            <i data-lucide="user-plus"></i>
                            <input type="text" name="name" placeholder="Johnathan Doe" required>
                        </div>
                    </div>

                    <div class="input-modern-group">
                        <label>Official Email</label>
                        <div class="input-wrapper">
                            <i data-lucide="mail"></i>
                            <input type="email" name="email" placeholder="name@nacoc.org" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                        <div class="input-modern-group">
                            <label>Password</label>
                            <div class="input-wrapper">
                                <i data-lucide="lock"></i>
                                <input type="password" name="password" placeholder="••••••••" required>
                            </div>
                        </div>
                        <div class="input-modern-group">
                            <label>Confirm</label>
                            <div class="input-wrapper">
                                <i data-lucide="shield-check"></i>
                                <input type="password" name="password_confirmation" placeholder="••••••••" required>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div style="background: rgba(239, 68, 68, 0.03); padding: 1.25rem; border-radius: 18px; border: 1px solid rgba(239, 68, 68, 0.1);">
                        <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer;">
                            <input type="checkbox" required style="width: 20px; height: 20px; margin-top: 4px; accent-color: var(--danger);">
                            <span style="font-size: 0.85rem; color: var(--text-dark); line-height: 1.5; font-weight: 600;">
                                I agree to the <a href="#" style="color: var(--danger);">Terms & Conditions</a> and understand that I am fully responsible for all actions performed under this account. <span style="font-weight: 900; color: var(--danger);">If any discrepancies, database tampering, or unauthorized stock movements are detected, I will be held personally and legally responsible for the consequences.</span>
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="auth-btn-primary" style="background: var(--text-main);">
                        <span>Registry Personnel</span>
                        <i data-lucide="user-check"></i>
                    </button>
                </form>

                <div style="text-align: center; margin-top: 2.5rem; pt-2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                    <p style="color: var(--text-muted); font-weight: 600;">Already registered? <a href="javascript:void(0)" onclick="toggleAuth('login')" style="color: var(--primary); font-weight: 800; text-decoration: none;">Secure Login</a></p>
                </div>
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
        letter-spacing: 0.05em;
        margin-bottom: 8px;
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-wrapper i {
        position: absolute;
        left: 1.25rem;
        width: 18px;
        color: var(--primary);
        opacity: 0.5;
    }

    .input-wrapper input {
        width: 100%;
        padding: 1.15rem 1.25rem 1.15rem 3.5rem;
        border-radius: 16px;
        border: 2px solid var(--border-color);
        background: var(--bg-main);
        color: var(--text-main);
        font-weight: 700;
        font-size: 0.95rem;
        outline: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .input-wrapper input:focus {
        border-color: var(--primary);
        background: white;
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.08);
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

    .auth-form-group {
        animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    function toggleAuth(mode) {
        const login = document.getElementById('loginForm');
        const register = document.getElementById('registerForm');
        
        if (mode === 'register') {
            login.style.display = 'none';
            register.style.display = 'block';
        } else {
            login.style.display = 'block';
            register.style.display = 'none';
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
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

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection
