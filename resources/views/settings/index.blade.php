@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.dashboard')

@section('title', 'System Settings')

@section('content')
<div class="animate-slide-up" style="width: 100%; margin: 0 auto; padding: 0;">
    
    <!-- Premium User Settings Header -->
    <div class="glass-card header-mesh" style="padding: 2.5rem 3rem; border-radius: 32px; margin-bottom: 2.5rem; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.4); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.08);">
        <div style="position: absolute; top: -50px; right: -50px; width: 250px; height: 250px; background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%); z-index: 0;"></div>
        
        <div style="position: relative; z-index: 1; display: flex; align-items: center; gap: 2.5rem;">
            <div style="position: relative;" id="avatar-preview-container">
                @if(auth()->user()->avatar)
                    <img src="{{ Storage::url(auth()->user()->avatar) }}" style="width: 110px; height: 110px; border-radius: 28px; object-fit: cover; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border: 4px solid white;" id="user-avatar-img">
                @else
                    <div id="user-avatar-placeholder" style="width: 110px; height: 110px; background: var(--primary); border-radius: 28px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 950; color: white; box-shadow: 0 15px 35px rgba(99,102,241,0.3); border: 4px solid white;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', auth()->user()->name)[1] ?? '', 0, 1)) }}
                    </div>
                @endif
                <button onclick="document.getElementById('avatar-upload').click()" style="position: absolute; bottom: -8px; right: -8px; width: 36px; height: 36px; background: white; border-radius: 10px; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 5px 10px rgba(0,0,0,0.1); transition: 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" title="Upload new photo">
                    <i data-lucide="camera" style="width: 18px; color: var(--primary);"></i>
                </button>
                <input type="file" id="avatar-upload" accept="image/*" style="display: none;" onchange="uploadAvatarFile(this)">
            </div>
            
            <div>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                    <span style="background: rgba(99, 102, 241, 0.1); color: var(--primary); font-size: 0.65rem; font-weight: 950; padding: 0.35rem 1rem; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.1em;">Authenticated Personnel</span>
                    <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="shield-check" style="width: 14px; color: #10b981;"></i> Security Verified
                    </span>
                </div>
                <h1 style="margin: 0; font-size: 2.5rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.05em;">{{ auth()->user()->name }}</h1>
                <p style="margin: 6px 0 0; color: var(--text-muted); font-size: 1.05rem; font-weight: 600;">@ {{ auth()->user()->username }} &bull; Inventory Management</p>
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
                <button class="settings-nav-btn" onclick="switchSection('interface', this)">
                    <i data-lucide="monitor"></i>
                    <span>Display & Interface</span>
                </button>
            </nav>
            
            <div style="margin-top: 3rem; padding: 1.75rem; background: var(--bg-main); border-radius: 20px; border: 1px solid var(--border-color);">
                <div style="font-size: 0.7rem; font-weight: 900; color: var(--text-muted); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Account Status</div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Last Sign-in</span>
                    <span style="font-size: 0.85rem; font-weight: 800; color: var(--text-main);">
                        {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->diffForHumans() : 'Just now' }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Security Level</span>
                    @php $sec = auth()->user()->getSecurityStatus(); @endphp
                    <span id="security-level-label" style="font-size: 0.85rem; font-weight: 800; color: {{ $sec['color'] }};">
                        {{ $sec['label'] }}
                    </span>
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
                        <input type="text" id="prof-name" value="{{ auth()->user()->name }}" class="modern-input">
                    </div>
                    <div class="form-group">
                        <label>Identification Username</label>
                        <input type="text" value="{{ auth()->user()->username }}" class="modern-input" readonly style="opacity: 0.7;">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" id="prof-email" value="{{ auth()->user()->email }}" class="modern-input">
                    </div>
                    <div class="form-group">
                        <label>Contact Phone</label>
                        <input type="text" id="prof-phone" value="{{ auth()->user()->phone }}" class="modern-input" placeholder="+233 ...">
                    </div>
                    <div class="form-group">
                        <label>Professional Role</label>
                        <input type="text" id="prof-role" value="{{ auth()->user()->role }}" class="modern-input" placeholder="e.g. Storekeeper">
                    </div>
                    <div class="form-group">
                        <label>Assigned Department</label>
                        <input type="text" id="prof-dept" value="{{ auth()->user()->department }}" class="modern-input" placeholder="e.g. Logistics">
                    </div>
                </div>

                <div style="background: rgba(99, 102, 241, 0.05); padding: 2rem; border-radius: 20px; border: 1px solid rgba(99, 102, 241, 0.1);">
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <i data-lucide="info" style="color: var(--primary); width: 24px;"></i>
                        <p style="font-size: 0.9rem; color: var(--text-main); font-weight: 700; margin: 0;">Your profile information is verified and visible for internal auditing and logistical tracking.</p>
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
                        <button class="security-action-btn" onclick="openPasswordModal()">
                            <i data-lucide="shield-keyhole"></i>
                            <span>Configure Password</span>
                        </button>
                    </div>

                </div>
            </div>

            <!-- Preferences Section (Renamed/Moved to Interface) -->
            <div id="section-interface" class="settings-section">
                <div style="margin-bottom: 3rem;">
                    <h2 style="font-size: 1.75rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.5rem; letter-spacing: -0.02em;">Display & Interface</h2>
                    <p style="color: var(--text-muted); font-size: 1rem;">Customize the visual appearance and scaling of the system.</p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <!-- Zoom Control Item -->
                    <div class="setting-item" style="padding: 2.5rem;">
                        <div style="flex: 1;">
                            <div style="font-weight: 850; color: var(--text-main); margin-bottom: 4px; display: flex; align-items: center; gap: 10px;">
                                <i data-lucide="zoom-in" style="width: 20px; color: var(--primary);"></i>
                                System-wide UI Scaling
                            </div>
                            <div style="font-size: 0.85rem; color: var(--text-muted);">Adjust the overall size of the interface elements. This affects menus, text, and charts.</div>
                        </div>
                        
                        <div style="display: flex; align-items: center; background: var(--bg-card); border: 2px solid var(--border-color); border-radius: 16px; padding: 6px; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
                            <button onclick="settingsAdjustZoom(-0.1)" class="security-action-btn" style="padding: 0.75rem; background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border-color); width: 44px; height: 44px; justify-content: center;" title="Decrease Size">
                                <i data-lucide="minus"></i>
                            </button>
                            <div style="padding: 0 1.5rem; text-align: center;">
                                <div id="settings-zoom-val" style="font-size: 1.25rem; font-weight: 950; color: var(--primary);">100%</div>
                                <div style="font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-top: 2px;">Standard</div>
                            </div>
                            <button onclick="settingsAdjustZoom(0.1)" class="security-action-btn" style="padding: 0.75rem; background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border-color); width: 44px; height: 44px; justify-content: center;" title="Increase Size">
                                <i data-lucide="plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div style="flex: 1;">
                            <div style="font-weight: 850; color: var(--text-main); margin-bottom: 4px; display: flex; align-items: center; gap: 10px;">
                                <i data-lucide="sparkles" style="width: 20px; color: #a855f7;"></i>
                                Glassmorphism Visuals
                            </div>
                            <div style="font-size: 0.85rem; color: var(--text-muted);">Enable semi-transparent surfaces and blur effects across the system.</div>
                        </div>
                        <div class="toggle-switch active">
                            <div class="toggle-nob"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div style="margin-top: 5rem; border-top: 2px solid var(--border-color); padding-top: 2.5rem; display: flex; justify-content: flex-end; gap: 1.5rem;">
                <button class="modern-action-btn secondary" style="width: auto; padding: 1.15rem 2.5rem;" onclick="location.reload()">Discard Changes</button>
                <button class="save-btn" onclick="saveSettings()">
                    <i data-lucide="save" style="width: 20px;"></i>
                    Update My Profile
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Password Change Modal -->
<div id="passwordModal" class="modal-overlay" style="display: none;">
    <div class="glass-card modal-content animate-pop-in" style="max-width: 500px; width: 90%; border-radius: 32px; padding: 3rem;">
        <div style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.75rem; font-weight: 950; color: var(--text-main); margin-bottom: 0.5rem; letter-spacing: -0.02em;">Security Credentials</h2>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Please enter your current and new password below.</p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1.75rem;">
            <div class="form-group">
                <label>Current Password</label>
                <div class="password-wrapper">
                    <input type="password" id="current_password" class="modern-input" placeholder="••••••••">
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility(this)">
                        <i data-lucide="eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label>New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="new_password" class="modern-input" placeholder="••••••••" oninput="validatePassword()">
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility(this)">
                        <i data-lucide="eye"></i>
                    </button>
                </div>
                <div id="password-strength" style="margin-top: 8px; height: 4px; background: #e2e8f0; border-radius: 2px; overflow: hidden; display: none;">
                    <div id="strength-bar" style="height: 100%; width: 0%; transition: 0.3s;"></div>
                </div>
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" class="modern-input" placeholder="••••••••" oninput="validatePassword()">
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility(this)">
                        <i data-lucide="eye"></i>
                    </button>
                </div>
                <div id="password-match-tag" style="font-size: 0.7rem; font-weight: 800; margin-top: 8px; display: none;"></div>
            </div>
        </div>

        <div style="margin-top: 3.5rem; display: flex; gap: 1rem;">
            <button class="modern-action-btn secondary" style="flex: 1;" onclick="closePasswordModal()">Cancel</button>
            <button class="save-btn" style="flex: 2; justify-content: center; padding: 1rem;" onclick="performPasswordUpdate()">
                <i data-lucide="lock" style="width: 18px;"></i>
                Update Password
            </button>
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
    .modern-input:focus { border-color: var(--primary); background: var(--bg-card); box-shadow: 0 12px 25px rgba(99,102,241,0.08); }

    .setting-item {
        display: flex; align-items: center; justify-content: space-between;
        padding: 2.25rem; background: var(--bg-main); border-radius: 24px;
        border: 1px solid var(--border-color);
        transition: 0.3s;
    }
    .setting-item:hover { border-color: var(--primary); background: rgba(99, 102, 241, 0.02); }

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

    .modern-action-btn.secondary {
        background: var(--bg-main);
        color: var(--text-muted);
        border: 2px solid var(--border-color);
        border-radius: 20px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.3s;
    }

    .modern-action-btn.secondary:hover {
        background: var(--bg-card);
        color: var(--text-main);
        border-color: var(--primary);
    }

    .save-btn {
        padding: 1.25rem 3rem; border-radius: 20px; border: none;
        background: linear-gradient(135deg, var(--primary) 0%, #4338ca 100%);
        color: white; font-weight: 950; font-size: 1.05rem; cursor: pointer;
        display: flex; align-items: center; gap: 14px;
        transition: all 0.4s; box-shadow: 0 12px 30px rgba(79, 70, 229, 0.3);
    }
    .save-btn:hover { transform: translateY(-5px); box-shadow: 0 20px 45px rgba(79, 70, 229, 0.45); }

    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); backdrop-filter: blur(10px);
        display: flex; align-items: center; justify-content: center; z-index: 1000;
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes popIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
    .animate-pop-in { animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }

    .password-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .password-toggle {
        position: absolute;
        right: 1.25rem;
        background: transparent;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        border-radius: 8px;
    }
    .password-toggle:hover {
        color: var(--primary);
        background: rgba(99, 102, 241, 0.05);
    }
    .password-toggle i {
        width: 18px;
        height: 18px;
    }

    .security-action-btn {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.85rem 1.75rem;
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white;
        border: none;
        border-radius: 14px;
        font-weight: 850;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .security-action-btn i {
        width: 18px;
        color: #94a3b8;
        transition: 0.3s;
    }
    .security-action-btn:hover {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    .security-action-btn:hover i {
        color: #3b82f6;
        transform: rotate(15deg);
    }

    @media (max-width: 1024px) {
        .settings-grid { 
            grid-template-columns: 1fr !important; 
            gap: 1.5rem !important;
        }
        #settingsContent { 
            padding: 2rem !important; 
            border-radius: 28px !important;
            min-height: auto !important;
        }
        .header-mesh {
            padding: 2rem !important;
            flex-direction: column !important;
            text-align: center !important;
        }
        .header-mesh > div {
            flex-direction: column !important;
            gap: 1.5rem !important;
        }
        .header-mesh h1 {
            font-size: 2rem !important;
        }
    }

    @media (max-width: 768px) {
        #section-profile div[style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
            gap: 1.5rem !important;
        }
        .setting-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 1.5rem;
            padding: 1.75rem !important;
            border-radius: 24px !important;
        }
        .setting-item > div:last-child {
            width: 100%;
            justify-content: flex-start;
        }
        .security-action-btn {
            width: 100%;
            justify-content: center;
        }
        .save-btn {
            width: 100%;
            justify-content: center;
            padding: 1.25rem 1.5rem !important;
        }
        .header-mesh {
            padding: 2.5rem 1.5rem !important;
            margin-bottom: 1.5rem !important;
        }
        .settings-nav-btn {
            padding: 1.15rem 1.5rem !important;
            font-size: 0.9rem !important;
        }
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
                body: JSON.stringify({
                    name: document.getElementById('prof-name').value,
                    email: document.getElementById('prof-email').value,
                    phone: document.getElementById('prof-phone').value,
                    role: document.getElementById('prof-role').value,
                    department: document.getElementById('prof-dept').value
                })
            });
            const data = await res.json();
            if (data.success) {
                showToast('Synchronization Complete', data.message, 'success');
                // Refresh the page after a short delay to allow the toast to be seen
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast('Update Failed', data.message || 'Generic error', 'error');
            }
        } catch (e) {
            showToast('Connection Error', 'Failed to reach synchronization node', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }

    function openPasswordModal() {
        document.getElementById('passwordModal').style.display = 'flex';
    }

    function closePasswordModal() {
        document.getElementById('passwordModal').style.display = 'none';
        // Clear fields
        document.getElementById('current_password').value = '';
        document.getElementById('new_password').value = '';
        document.getElementById('confirm_password').value = '';
    }

    function validatePassword() {
        const pass = document.getElementById('new_password').value;
        const confirm = document.getElementById('confirm_password').value;
        const strengthBar = document.getElementById('strength-bar');
        const strengthCont = document.getElementById('password-strength');
        const matchTag = document.getElementById('password-match-tag');

        if (pass.length > 0) {
            strengthCont.style.display = 'block';
            let strength = 0;
            if (pass.length >= 8) strength += 25;
            if (/[A-Z]/.test(pass)) strength += 25;
            if (/[0-9]/.test(pass)) strength += 25;
            if (/[^A-Za-z0-9]/.test(pass)) strength += 25;

            strengthBar.style.width = strength + '%';
            if (strength <= 25) strengthBar.style.background = '#ef4444';
            else if (strength <= 50) strengthBar.style.background = '#f59e0b';
            else if (strength <= 75) strengthBar.style.background = '#3b82f6';
            else strengthBar.style.background = '#10b981';
        } else {
            strengthCont.style.display = 'none';
        }

        if (confirm.length > 0) {
            matchTag.style.display = 'block';
            if (pass === confirm) {
                matchTag.innerText = '✓ Passwords Match';
                matchTag.style.color = '#10b981';
            } else {
                matchTag.innerText = '✗ Passwords do not match';
                matchTag.style.color = '#ef4444';
            }
        } else {
            matchTag.style.display = 'none';
        }
    }

    async function performPasswordUpdate() {
        const current = document.getElementById('current_password').value;
        const pass = document.getElementById('new_password').value;
        const confirm = document.getElementById('confirm_password').value;

        if (!current || !pass || !confirm) {
            showToast('Incomplete Fields', 'Please fill in all password fields', 'warning');
            return;
        }

        if (pass !== confirm) {
            showToast('Mismatch', 'New passwords do not match', 'error');
            return;
        }

        try {
            const res = await fetch("{{ route('settings.password') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    current_password: current,
                    password: pass,
                    password_confirmation: confirm
                })
            });
            const data = await res.json();
            if (data.success) {
                showToast('Security Updated', data.message, 'success');
                closePasswordModal();
            } else {
                showToast('Authentication Error', data.message || 'Verification failed', 'error');
            }
        } catch (e) {
            showToast('Gateway Error', 'Could not verify credentials with security node', 'error');
        }
    }

    function togglePasswordVisibility(btn) {
        const input = btn.parentElement.querySelector('input');
        const icon = btn.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function settingsAdjustZoom(delta) {
        if (window.currentZoom !== undefined && typeof window.applyZoom === 'function') {
            window.currentZoom = Math.min(Math.max(window.currentZoom + delta, 0.5), 2);
            window.applyZoom();
            updateSettingsZoomUI();
        }
    }

    function updateSettingsZoomUI() {
        const valDisplay = document.getElementById('settings-zoom-val');
        if (valDisplay && window.currentZoom !== undefined) {
            const percentage = Math.round(window.currentZoom * 100);
            valDisplay.innerText = percentage + '%';
            
            const label = valDisplay.nextElementSibling;
            if (label) {
                if (percentage === 100) label.innerText = 'Standard';
                else if (percentage < 100) label.innerText = 'Compact';
                else label.innerText = 'Enlarged';
            }
        }
    }

    async function uploadAvatarFile(input) {
        if (!input.files || !input.files[0]) return;
        
        const file = input.files[0];
        
        // --- Client-Side Limit Fix: Block files > 5MB directly to avoid PHP runtime crash ---
        const maxSizeMB = 5; 
        if (file.size > maxSizeMB * 1024 * 1024) {
            showToast('File Too Large', `Please select an image smaller than ${maxSizeMB}MB.`, 'error');
            input.value = ''; // Reset input
            return;
        }

        const formData = new FormData();
        formData.append('avatar', file);
        
        const btn = input.previousElementSibling;
        const orgHtml = btn.innerHTML;
        btn.innerHTML = `<i data-lucide="upload" style="width: 18px; color: var(--primary);"></i>`;
        if (typeof lucide !== 'undefined') lucide.createIcons();

        try {
            const res = await fetch("{{ route('settings.avatar') }}", {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json' 
                },
                body: formData
            });

            // Read raw text first to avoid breaking if PHP throws an HTML fatal crash page
            const textResponse = await res.text();
            let data;
            try {
                // Intelligently find the JSON block in case PHP notices are prepended
                const jsonMatch = textResponse.match(/\{.*\}/s);
                const jsonClean = jsonMatch ? jsonMatch[0] : textResponse;
                data = JSON.parse(jsonClean);
            } catch (err) {
                const snippet = textResponse.substring(0, 100).replace(/<[^>]*>/g, '');
                showToast('Server Output Conflict', `The server sent extra debug info that broke the upload. Snippet: ${snippet}`, 'error');
                btn.innerHTML = orgHtml;
                if (typeof lucide !== 'undefined') lucide.createIcons();
                return;
            }
            
            if (res.ok && data.success) {
                showToast('Upload Successful', data.message, 'success');
                
                // Dynamically update the image
                const container = document.getElementById('avatar-preview-container');
                const existingImg = document.getElementById('user-avatar-img');
                const placeholder = document.getElementById('user-avatar-placeholder');
                
                if (existingImg) {
                    existingImg.src = data.url + '?t=' + new Date().getTime(); 
                } else if (placeholder) {
                    const img = document.createElement('img');
                    img.src = data.url;
                    img.id = 'user-avatar-img';
                    img.style.cssText = 'width: 110px; height: 110px; border-radius: 28px; object-fit: cover; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border: 4px solid white;';
                    container.insertBefore(img, placeholder);
                    placeholder.remove();
                }

                const globalNavAvatars = document.querySelectorAll(`img[src*="${data.url.split('?')[0]}"]`);
                globalNavAvatars.forEach(av => av.src = data.url + '?t=' + new Date().getTime());

                btn.innerHTML = orgHtml;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            } else {
                // Intelligently catch Laravel's deep nested validation errors
                let serverError = data.message || 'Image rejected by validation node.';
                if (data.errors && data.errors.avatar) {
                    serverError = data.errors.avatar[0];
                }
                showToast('Upload Failed', serverError, 'error');
                btn.innerHTML = orgHtml;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        } catch (e) {
            showToast('Connection Error', 'Could not transmit image array.', 'error');
            btn.innerHTML = orgHtml;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        updateSettingsZoomUI();
        
        // Listen for global zoom changes (from top nav)
        window.addEventListener('storage', (e) => {
            if (e.key === 'system-zoom') {
                updateSettingsZoomUI();
            }
        });
    });
</script>
@endsection
