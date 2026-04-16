@extends('layouts.dashboard')

@section('content')
<div class="animate-slide-up" style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem;">
    
    <!-- Premium User Settings Header -->
    <div class="glass-card header-mesh" style="padding: 2.5rem 3rem; border-radius: 32px; margin-bottom: 2.5rem; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.4); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.08);">
        <div style="position: absolute; top: -50px; right: -50px; width: 250px; height: 250px; background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%); z-index: 0;"></div>
        
        <div style="position: relative; z-index: 1; display: flex; align-items: center; gap: 2.5rem;">
            <div style="position: relative;">
                <div style="width: 110px; height: 110px; background: {{ $user['avatar_color'] }}; border-radius: 28px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 950; color: white; box-shadow: 0 15px 35px rgba(99,102,241,0.3); border: 4px solid white;">
                    {{ substr($user['name'], 0, 1) }}{{ substr(explode(' ', $user['name'])[1] ?? '', 0, 1) }}
                </div>
                <button style="position: absolute; bottom: -8px; right: -8px; width: 36px; height: 36px; background: white; border-radius: 10px; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 5px 10px rgba(0,0,0,0.1);">
                    <i data-lucide="camera" style="width: 18px; color: var(--primary);"></i>
                </button>
            </div>
            
            <div>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                    <span style="background: rgba(99, 102, 241, 0.1); color: var(--primary); font-size: 0.65rem; font-weight: 950; padding: 0.35rem 1rem; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.1em;">Storekeeper Account</span>
                    <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="shield-check" style="width: 14px; color: #10b981;"></i> Verified Profile
                    </span>
                </div>
                <h1 style="margin: 0; font-size: 2.5rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.05em;">{{ $user['name'] }}</h1>
                <p style="margin: 6px 0 0; color: var(--text-muted); font-size: 1.05rem; font-weight: 600;">{{ $user['role'] }} &bull; {{ $user['department'] }}</p>
            </div>
        </div>
    </div>

    <!-- Settings Workspace -->
    <div style="display: grid; grid-template-columns: 320px 1fr; gap: 2.5rem; align-items: flex-start; padding-bottom: 5rem;" class="settings-grid">
        
        <!-- Sidebar Navigation -->
        <div class="glass-card" style="padding: 1.5rem; border-radius: 24px;">
            <nav style="display: flex; flex-direction: column; gap: 0.65rem;">
                <button class="settings-nav-btn active" onclick="switchSection('profile', this)">
                    <i data-lucide="user"></i>
                    <span>Personal Profile</span>
                </button>
                <button class="settings-nav-btn" onclick="switchSection('security', this)">
                    <i data-lucide="lock"></i>
                    <span>Login & Security</span>
                </button>
                <button class="settings-nav-btn" onclick="switchSection('preferences', this)">
                    <i data-lucide="layers"></i>
                    <span>Display Preferences</span>
                </button>
                <button class="settings-nav-btn" onclick="switchSection('notifications', this)">
                    <i data-lucide="bell"></i>
                    <span>Notification Center</span>
                </button>
            </nav>
            
            <div style="margin-top: 3rem; padding: 1.75rem; background: var(--bg-main); border-radius: 20px; border: 1px solid var(--border-color);">
                <div style="font-size: 0.7rem; font-weight: 900; color: var(--text-muted); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Account Status</div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Last Sign-in</span>
                    <span style="font-size: 0.85rem; font-weight: 800; color: var(--text-main);">2h ago</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Security Level</span>
                    <span style="font-size: 0.85rem; font-weight: 800; color: #10b981;">Strong</span>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div id="settingsContent" class="glass-card" style="padding: 3.5rem; border-radius: 32px; min-height: 600px;">
            
            <!-- Personal Profile Section -->
            <div id="section-profile" class="settings-section active">
                <div style="margin-bottom: 3rem; border-bottom: 1px solid var(--border-color); padding-bottom: 2rem;">
                    <h2 style="font-size: 1.75rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.5rem; letter-spacing: -0.02em;">Personal Information</h2>
                    <p style="color: var(--text-muted); font-size: 1rem;">Update your name, contact email, and professional designation.</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; margin-bottom: 3rem;">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" value="{{ $user['name'] }}" class="modern-input">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" value="{{ $user['email'] }}" class="modern-input">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" value="{{ $user['phone'] }}" class="modern-input">
                    </div>
                    <div class="form-group">
                        <label>Professional Role</label>
                        <input type="text" value="{{ $user['role'] }}" class="modern-input" readonly style="opacity: 0.7; background: rgba(0,0,0,0.02);">
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 6px;">* Contact administration to change role.</p>
                    </div>
                </div>

                <div style="background: rgba(99, 102, 241, 0.05); padding: 2rem; border-radius: 20px; border: 1px solid rgba(99, 102, 241, 0.1);">
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <i data-lucide="info" style="color: var(--primary); width: 24px;"></i>
                        <p style="font-size: 0.9rem; color: var(--text-main); font-weight: 700; margin: 0;">Your profile information is visible to department heads for auditing purposes.</p>
                    </div>
                </div>
            </div>

            <!-- Login & Security Section -->
            <div id="section-security" class="settings-section">
                <div style="margin-bottom: 3rem;">
                    <h2 style="font-size: 1.75rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.5rem; letter-spacing: -0.02em;">Security Settings</h2>
                    <p style="color: var(--text-muted); font-size: 1rem;">Manage your password and active sessions.</p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div class="setting-item">
                        <div style="flex: 1;">
                            <div style="font-weight: 850; color: var(--text-main); margin-bottom: 4px;">Update Password</div>
                            <div style="font-size: 0.85rem; color: var(--text-muted);">Ensure your account is protected with a strong, complex password.</div>
                        </div>
                        <button class="modern-action-btn" style="width: auto; padding: 0.75rem 1.5rem; font-size: 0.85rem;">Change Password</button>
                    </div>

                    <div class="setting-item">
                        <div style="flex: 1;">
                            <div style="font-weight: 850; color: var(--text-main); margin-bottom: 4px;">Two-Factor Authentication</div>
                            <div style="font-size: 0.85rem; color: var(--text-muted);">Add an extra layer of security to your account login.</div>
                        </div>
                        <div class="toggle-switch">
                            <div class="toggle-nob"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preferences Section -->
            <div id="section-preferences" class="settings-section">
                <div style="margin-bottom: 3rem;">
                    <h2 style="font-size: 1.75rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.5rem; letter-spacing: -0.02em;">System Preferences</h2>
                    <p style="color: var(--text-muted); font-size: 1rem;">Customize the visual appearance and behavior of the dashboard.</p>
                </div>

                <div class="setting-item">
                    <div style="flex: 1;">
                        <div style="font-weight: 850; color: var(--text-main); margin-bottom: 4px;">Glassmorphism Visuals</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">Enable semi-transparent surfaces and blur effects across the system.</div>
                    </div>
                    <div class="toggle-switch active">
                        <div class="toggle-nob"></div>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div style="margin-top: 5rem; border-top: 2px solid var(--border-color); padding-top: 2.5rem; display: flex; justify-content: flex-end; gap: 1.5rem;">
                <button class="modern-action-btn secondary" style="width: auto; padding: 1.15rem 2.5rem; background: transparent; border-color: transparent;">Discard Changes</button>
                <button class="save-btn" onclick="saveSettings()">
                    <i data-lucide="save" style="width: 20px;"></i>
                    Update My Profile
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .header-mesh {
        background: radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.04) 0, transparent 50%),
                    var(--bg-card);
        backdrop-filter: blur(20px);
    }
    
    .settings-nav-btn {
        display: flex; align-items: center; gap: 1.15rem;
        width: 100%; padding: 1.35rem 1.75rem; border: none;
        background: transparent; color: var(--text-muted);
        font-weight: 800; font-size: 1rem; cursor: pointer;
        border-radius: 20px; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .settings-nav-btn i { width: 22px; transition: all 0.3s; }
    .settings-nav-btn:hover { background: rgba(99, 102, 241, 0.05); color: var(--primary); transform: translateX(5px); }
    .settings-nav-btn.active {
        background: var(--primary); color: white;
        box-shadow: 0 15px 30px rgba(99, 102, 241, 0.25);
    }
    .settings-nav-btn.active i { color: white; }

    .settings-section { display: none; }
    .settings-section.active { display: block; animation: sectionFade 0.5s cubic-bezier(0.4, 0, 0.2, 1); }

    @keyframes sectionFade {
        from { opacity: 0; transform: translateY(15px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .form-group label {
        display: block; font-size: 0.72rem; font-weight: 900;
        color: var(--text-muted); text-transform: uppercase;
        margin-bottom: 10px; letter-spacing: 0.8px;
    }
    .modern-input {
        width: 100%; padding: 1.15rem 1.4rem; border-radius: 16px;
        border: 2px solid var(--border-color); background: var(--bg-main);
        color: var(--text-main); font-weight: 800; font-size: 1.05rem;
        outline: none; transition: all 0.3s;
    }
    .modern-input:focus { border-color: var(--primary); background: white; box-shadow: 0 12px 25px rgba(99,102,241,0.08); }

    .setting-item {
        display: flex; align-items: center; justify-content: space-between;
        padding: 2.25rem; background: var(--bg-main); border-radius: 24px;
        border: 1px solid var(--border-color);
        transition: 0.3s;
    }
    .setting-item:hover { border-color: var(--primary-light); background: white; }

    .toggle-switch {
        width: 58px; height: 32px; background: #e2e8f0; border-radius: 99px;
        position: relative; cursor: pointer; transition: 0.4s;
    }
    .toggle-nob {
        position: absolute; top: 50%; left: 5px; transform: translateY(-50%); width: 22px; height: 22px;
        background: white; border-radius: 50%; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow: 0 3px 6px rgba(0,0,0,0.15);
    }
    .toggle-switch.active { background: var(--primary); }
    .toggle-switch.active .toggle-nob { left: 31px; }

    .save-btn {
        padding: 1.25rem 3rem; border-radius: 20px; border: none;
        background: linear-gradient(135deg, var(--primary) 0%, #4338ca 100%);
        color: white; font-weight: 950; font-size: 1.05rem; cursor: pointer;
        display: flex; align-items: center; gap: 14px;
        transition: all 0.4s; box-shadow: 0 12px 30px rgba(79, 70, 229, 0.3);
    }
    .save-btn:hover { transform: translateY(-5px); box-shadow: 0 20px 45px rgba(79, 70, 229, 0.45); }

    @media (max-width: 1024px) {
        .settings-grid { grid-template-columns: 1fr; }
        #settingsContent { padding: 2.5rem; }
    }
</style>

<script>
    function switchSection(id, btn) {
        document.querySelectorAll('.settings-nav-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
        const target = document.getElementById('section-' + id);
        if(target) target.classList.add('active');
    }

    async function saveSettings() {
        const btn = document.querySelector('.save-btn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Updating Profile...';

        try {
            const res = await fetch("{{ route('settings.update') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ /* profile data */ })
            });
            const data = await res.json();
            if (data.success) {
                showToast('Profile Updated', data.message, 'success');
            }
        } catch (e) {
            showToast('System Error', 'Failed to update profile', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        
        // Add toggle functionality
        document.querySelectorAll('.toggle-switch').forEach(toggle => {
            toggle.addEventListener('click', () => toggle.classList.toggle('active'));
        });
    });
</script>
@endsection
