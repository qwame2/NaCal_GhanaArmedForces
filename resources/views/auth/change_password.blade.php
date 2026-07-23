@extends('layouts.auth')

@section('content')

<!-- Select2 CSS -->
<link href="{{ asset('css/vendor/select2.min.css') }}" rel="stylesheet" />

<div class="sync-wrapper">

    <!-- Status Bar -->
    <div class="sync-statusbar">
        <div class="status-live">
            <div class="live-dot"></div>
            <span>Secure Connection Active</span>
        </div>
        <div class="status-step">
            <span class="step-label">Step 2 of 3</span>
            <div class="step-pip done"></div>
            <div class="step-pip active"></div>
            <div class="step-pip"></div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="sync-card">

        <!-- Hero Banner -->
        <div class="sync-hero">
            <div class="hero-grid"></div>
            <div class="hero-glow"></div>
            <div class="hero-content">
                <div class="hero-badge">
                    <i data-lucide="shield-check"></i>
                    <span>Protocol: Mandatory Upgrade</span>
                </div>
                <h1 class="hero-title">Security <span>Sync</span></h1>
                <p class="hero-sub">You need to set a new password before accessing the system.</p>

                <div class="hero-icon-row">
                    <div class="hero-avatar">
                        <i data-lucide="user"></i>
                    </div>
                    <div class="hero-user-info">
                        <span class="hero-user-name">{{ auth()->user()->name }}</span>
                        <span class="hero-user-role">{{ auth()->user()->role === 'Main Admin' ? 'Head of Admin (Authorizer)' : auth()->user()->role }} · {{ auth()->user()->department ?? 'No Sector' }}</span>
                    </div>
                    <div class="hero-lock-chip">
                        <i data-lucide="lock"></i>
                        <span>TEMP KEY</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Body -->
        <div class="sync-body">

            <div class="info-strip">
                <div class="info-icon-wrap">
                    <i data-lucide="info"></i>
                </div>
                <p>Your account was created with a temporary password. Please update your details and choose a new password (min 8 chars, including a number, and cannot match your username).</p>
            </div>

            <form action="{{ route('password.update') }}" method="POST">
                @csrf

                @if($errors->any())
                <div class="alert-error-wrap">
                    <i data-lucide="alert-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                @php
                    $user = auth()->user();
                    $hasNameEntered = !empty($user->name) && $user->name !== $user->username;
                    $hasDeptEntered = !empty($user->department);
                @endphp

                <!-- Username and Full Name -->
                <div class="sync-grid">
                    @if($user->guid)
                    <div class="field-block">
                        <div class="field-label">
                            <label>Username</label>
                            <span class="req-badge locked-badge">LOCKED (AD)</span>
                        </div>
                        <div class="field-input field-disabled">
                            <div class="field-icon"><i data-lucide="lock"></i></div>
                            <input type="text" name="username" value="{{ $user->username }}" readonly tabindex="-1">
                        </div>
                    </div>
                    @else
                    <div class="field-block">
                        <div class="field-label">
                            <label for="username-input">Username</label>
                            <span class="req-badge">REQUIRED</span>
                        </div>
                        <div class="field-input">
                            <div class="field-icon"><i data-lucide="user"></i></div>
                            <input type="text" id="username-input" name="username" value="{{ old('username', $user->username) }}" placeholder="e.g. j_doe" required autocomplete="username">
                        </div>
                    </div>
                    @endif

                    @if($hasNameEntered)
                    <div class="field-block">
                        <div class="field-label">
                            <label>Full Name</label>
                            <span class="req-badge locked-badge">LOCKED</span>
                        </div>
                        <div class="field-input field-disabled">
                            <div class="field-icon"><i data-lucide="lock"></i></div>
                            <input type="text" value="{{ $user->name }}" readonly tabindex="-1">
                        </div>
                    </div>
                    @else
                    <div class="field-block">
                        <div class="field-label">
                            <label for="name-input">Full Name</label>
                            <span class="req-badge">REQUIRED</span>
                        </div>
                        <div class="field-input">
                            <div class="field-icon"><i data-lucide="user-check"></i></div>
                            <input type="text" id="name-input" name="name" value="{{ old('name', $user->name === $user->username ? '' : $user->name) }}" placeholder="e.g. John Doe" required autocomplete="name">
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Contact Number & Service Number -->
                <div class="sync-grid">
                    <div class="field-block">
                        <div class="field-label">
                            <label for="phone-input">Contact Number</label>
                            <span class="req-badge optional-badge">OPTIONAL</span>
                        </div>
                        <div class="field-input">
                            <div class="field-icon"><i data-lucide="phone"></i></div>
                            <input type="text" id="phone-input" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="e.g. +2332400000">
                        </div>
                    </div>

                    <div class="field-block">
                        <div class="field-label">
                            <label for="sn-input">Service Number</label>
                            <span class="req-badge optional-badge">OPTIONAL</span>
                        </div>
                        <div class="field-input">
                            <div class="field-icon"><i data-lucide="hash"></i></div>
                            <input type="text" id="sn-input" name="service_number" value="{{ old('service_number', $user->service_number) }}" placeholder="e.g. SN-8942">
                        </div>
                    </div>
                </div>

                <!-- Department & Role -->
                <div class="sync-grid">
                    @if($hasDeptEntered)
                    <div class="field-block">
                        <div class="field-label">
                            <label>Department</label>
                            <span class="req-badge locked-badge">LOCKED</span>
                        </div>
                        <div class="field-input field-disabled">
                            <div class="field-icon"><i data-lucide="lock"></i></div>
                            <input type="text" value="{{ $user->department }}" readonly tabindex="-1">
                        </div>
                    </div>
                    @else
                    <div class="field-block">
                        <div class="field-label">
                            <label for="sync-department-select">Department</label>
                            <span class="req-badge">REQUIRED</span>
                        </div>
                        <div class="field-input">
                            <div class="field-icon"><i data-lucide="building"></i></div>
                            <select name="department" id="sync-department-select" style="width: 100%; border: none; background: transparent; padding: 12px 4px; font-weight: 700; color: #0f172a; outline: none;" required>
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
                    @endif

                    <div class="field-block">
                        <div class="field-label">
                            <label>Role</label>
                            <span class="req-badge locked-badge">LOCKED</span>
                        </div>
                        <div class="field-input field-disabled">
                            <div class="field-icon"><i data-lucide="shield-check"></i></div>
                            <input type="text" value="{{ $user->role === 'Main Admin' ? 'Head of Admin (Authorizer)' : $user->role }}" readonly tabindex="-1">
                        </div>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="sync-grid">
                    <div class="field-block">
                        <div class="field-label">
                            <label for="pass-field">New Security Key</label>
                            <span class="req-badge">MIN 8 CHARS + NUMBER</span>
                        </div>
                        <div class="field-input">
                            <div class="field-icon"><i data-lucide="key-round"></i></div>
                            <input type="password" name="password" id="pass-field" placeholder="Enter new password" required minlength="8" pattern="(?=.*\d).{8,}" title="Minimum 8 characters, including at least one number" autocomplete="new-password">
                            <button type="button" class="eye-btn" onclick="togglePass('pass-field', this)">
                                <i data-lucide="eye"></i>
                            </button>
                        </div>
                        <p class="field-hint">Requirement: Min 8 chars including a number. Cannot match username.</p>
                    </div>

                    <div class="field-block">
                        <div class="field-label">
                            <label for="confirm-field">Confirm Key</label>
                            <span class="req-badge match-badge">MUST MATCH</span>
                        </div>
                        <div class="field-input">
                            <div class="field-icon"><i data-lucide="shield"></i></div>
                            <input type="password" name="password_confirmation" id="confirm-field" placeholder="Confirm new password" required minlength="8" pattern="(?=.*\d).{8,}" title="Minimum 8 characters, including at least one number" autocomplete="new-password">
                            <button type="button" class="eye-btn" onclick="togglePass('confirm-field', this)">
                                <i data-lucide="eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="divider"></div>

                <button type="submit" class="sync-btn">
                    <i data-lucide="refresh-cw"></i>
                    <span>Update Credentials & Continue</span>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="sync-footer">
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="logout-btn">
                    <i data-lucide="log-out"></i>
                    <span>Sign out instead</span>
                </button>
            </form>
        </div>
    </div>

</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');

    body {
        background: radial-gradient(circle at 10% 20%, rgba(136, 19, 55, 0.05) 0%, rgba(15, 23, 42, 0.05) 90.1%), #f8fafc;
        font-family: 'Outfit', sans-serif;
    }

    .sync-wrapper {
        width: 100%;
        max-width: 850px;
        margin: 0 auto;
        padding: 10px;
        animation: cardAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes cardAppear {
        from { opacity: 0; transform: translateY(30px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Status Bar Styling */
    .sync-statusbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 20px;
        padding: 12px 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
    }

    .status-live {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.72rem;
        font-weight: 800;
        color: #881337;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }

    .live-dot {
        width: 8px;
        height: 8px;
        background: #881337;
        border-radius: 50%;
        box-shadow: 0 0 0 0 rgba(136, 19, 55, 0.4);
        animation: livePulse 2s infinite;
    }

    @keyframes livePulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(136, 19, 55, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(136, 19, 55, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(136, 19, 55, 0); }
    }

    .status-step {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .step-label {
        font-size: 0.72rem;
        font-weight: 700;
        color: #64748b;
        margin-right: 4px;
    }

    .step-pip {
        width: 20px;
        height: 5px;
        border-radius: 10px;
        background: #e2e8f0;
        transition: all 0.3s ease;
    }

    .step-pip.done {
        background: #881337;
    }

    .step-pip.active {
        background: #881337;
        width: 30px;
        box-shadow: 0 1px 4px rgba(136, 19, 55, 0.3);
    }

    /* Main Card Styling */
    .sync-card {
        background: #ffffff;
        border: 1px solid rgba(136, 19, 55, 0.08);
        border-radius: 32px;
        overflow: hidden;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.03), 0 30px 60px -15px rgba(136, 19, 55, 0.12);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .sync-card:hover {
        box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.04), 0 35px 70px -10px rgba(136, 19, 55, 0.15);
    }

    /* Hero Banner Styling */
    .sync-hero {
        padding: 3rem 2.5rem 2rem;
        position: relative;
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4c0519 100%);
        overflow: hidden;
    }

    .hero-grid {
        position: absolute;
        inset: 0;
        background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        background-size: 24px 24px;
        mask-image: radial-gradient(ellipse at center, black, transparent 80%);
        -webkit-mask-image: radial-gradient(ellipse at center, black, transparent 80%);
    }

    .hero-glow {
        position: absolute;
        bottom: -50px;
        right: -30px;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.35) 0%, transparent 70%);
        filter: blur(20px);
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 99px;
        padding: 5px 14px;
        font-size: 0.65rem;
        font-weight: 800;
        color: #e0e7ff;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        margin-bottom: 1.25rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .hero-badge i {
        width: 13px;
        height: 13px;
    }

    .hero-title {
        font-size: 2.15rem;
        font-weight: 900;
        color: #ffffff;
        letter-spacing: -0.03em;
        line-height: 1.15;
        margin: 0 0 0.75rem;
    }

    .hero-title span {
        background: linear-gradient(135deg, #a5b4fc, #fbcfe8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-sub {
        font-size: 0.88rem;
        color: #c7d2fe;
        font-weight: 500;
        margin: 0;
        line-height: 1.45;
        opacity: 0.9;
    }

    .hero-icon-row {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-top: 1.75rem;
        padding-top: 1.25rem;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
    }

    .hero-avatar {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .hero-avatar i {
        width: 20px;
        height: 20px;
        color: #ffffff;
    }

    .hero-user-info {
        flex: 1;
    }

    .hero-user-name {
        font-size: 0.9rem;
        font-weight: 800;
        color: #ffffff;
        display: block;
    }

    .hero-user-role {
        font-size: 0.72rem;
        color: rgba(255, 255, 255, 0.65);
        font-weight: 600;
    }

    .hero-lock-chip {
        display: flex;
        align-items: center;
        gap: 6px;
        background: rgba(239, 68, 68, 0.2);
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-radius: 10px;
        padding: 6px 12px;
        font-size: 0.65rem;
        font-weight: 800;
        color: #fca5a5;
        letter-spacing: 0.08em;
    }

    .hero-lock-chip i {
        width: 12px;
        height: 12px;
    }

    /* Form Body Styling */
    .sync-body {
        padding: 2.5rem 2.5rem 1.75rem;
    }

    /* Info Strip Styling */
    .info-strip {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        background: rgba(136, 19, 55, 0.03);
        border: 1px solid rgba(136, 19, 55, 0.08);
        border-radius: 18px;
        padding: 14px 18px;
        margin-bottom: 2rem;
    }

    .info-icon-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: rgba(136, 19, 55, 0.1);
        color: #881337;
        flex-shrink: 0;
    }

    .info-icon-wrap i {
        width: 16px;
        height: 16px;
    }

    .info-strip p {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569;
        line-height: 1.6;
    }

    .sync-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }

    @media (max-width: 540px) {
        .sync-grid {
            grid-template-columns: 1fr;
            gap: 0;
        }
    }

    /* Input Field Block */
    .field-block {
        margin-bottom: 1.5rem;
    }

    .field-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        padding-left: 2px;
    }

    .field-label label {
        font-size: 0.72rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .req-badge {
        font-size: 0.6rem;
        font-weight: 800;
        background: #f1f5f9;
        color: #64748b;
        padding: 3px 8px;
        border-radius: 6px;
        letter-spacing: 0.05em;
        transition: all 0.3s ease;
    }

    .locked-badge {
        background: #e0f2fe;
        color: #0284c7;
    }

    .optional-badge {
        background: #f1f5f9;
        color: #94a3b8;
    }

    .field-hint {
        font-size: 0.7rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 6px;
        padding-left: 4px;
    }

    .field-input {
        display: flex;
        align-items: center;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .field-disabled {
        background: #f1f5f9 !important;
        border-color: #e2e8f0 !important;
        opacity: 0.85;
    }

    .field-disabled input {
        color: #64748b !important;
        cursor: not-allowed !important;
    }

    .field-input:focus-within:not(.field-disabled) {
        background: #ffffff;
        border-color: #881337;
        box-shadow: 0 0 0 4px rgba(136, 19, 55, 0.08);
    }

    .field-icon {
        padding: 0 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        transition: color 0.2s ease;
    }

    .field-icon i {
        width: 18px;
        height: 18px;
    }

    .field-input:focus-within:not(.field-disabled) .field-icon {
        color: #881337;
    }

    .field-input input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 16px 8px;
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        outline: none;
    }

    .field-input input::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    .eye-btn {
        padding: 0 16px;
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        transition: color 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .eye-btn:hover {
        color: #881337;
    }

    /* Alerts */
    .alert-error-wrap {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 16px;
        padding: 14px 18px;
        margin-bottom: 1.5rem;
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .alert-error-wrap i {
        width: 18px;
        height: 18px;
        color: #dc2626;
        flex-shrink: 0;
    }

    .alert-error-wrap span {
        font-size: 0.82rem;
        color: #991b1b;
        font-weight: 700;
    }

    /* Divider */
    .divider {
        height: 1px;
        background: linear-gradient(to right, rgba(226, 232, 240, 0.2), #e2e8f0, rgba(226, 232, 240, 0.2));
        margin: 1.75rem 0;
    }

    /* Action Button Styling */
    .sync-btn {
        width: 100%;
        height: 54px;
        border: none;
        border-radius: 16px;
        background: linear-gradient(135deg, #881337 0%, #580c24 100%);
        color: #ffffff;
        font-weight: 800;
        font-size: 0.95rem;
        letter-spacing: 0.02em;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 8px 25px rgba(136, 19, 55, 0.25);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.15);
    }

    .sync-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(136, 19, 55, 0.35);
        background: linear-gradient(135deg, #991b1b 0%, #6b0c2a 100%);
    }

    .sync-btn:active {
        transform: translateY(0);
    }

    /* Footer Styling */
    .sync-footer {
        text-align: center;
        padding: 1.5rem;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
    }

    .logout-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        border: none;
        background: transparent;
        text-decoration: none;
        transition: all 0.2s ease;
        padding: 6px 12px;
        border-radius: 8px;
    }

    .logout-btn:hover {
        color: #dc2626;
        background: rgba(220, 38, 38, 0.05);
    }

    .logout-btn i {
        width: 16px;
        height: 16px;
    }

    /* Select2 customization */
    .select2-container {
        flex: 1 !important;
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        background: transparent !important;
        border: none !important;
        height: 52px !important;
        display: flex !important;
        align-items: center !important;
        font-size: 0.95rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        padding-left: 4px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 50px !important;
        right: 12px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #0f172a !important;
        font-weight: 700 !important;
    }
    .select2-dropdown {
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: 0 10px 30px rgba(136, 19, 55, 0.08) !important;
        z-index: 999999 !important;
    }
</style>

<script>
    function togglePass(id, btn) {
        const input = document.getElementById(id);
        const isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        btn.innerHTML = `<i data-lucide="${isPass ? 'eye-off' : 'eye'}"></i>`;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const pass = document.getElementById('pass-field');
        const confirm = document.getElementById('confirm-field');
        const badge = document.querySelector('.match-badge');

        if (pass && confirm && badge) {
            function checkMatch() {
                const p = pass.value;
                const c = confirm.value;
                
                if (p && c && p === c) {
                    badge.style.background = '#ecfdf5';
                    badge.style.color = '#059669';
                    badge.textContent = 'MATCHED';
                } else if (c) {
                    badge.style.background = '#fef2f2';
                    badge.style.color = '#dc2626';
                    badge.textContent = 'MISMATCH';
                } else {
                    badge.style.background = '#f1f5f9';
                    badge.style.color = '#64748b';
                    badge.textContent = 'MUST MATCH';
                }
            }

            pass.addEventListener('input', checkMatch);
            confirm.addEventListener('input', checkMatch);
        }
    });
</script>

@push('scripts')
<script src="{{ asset('js/vendor/select2.min.js') }}"></script>
<script>
    jQuery(document).ready(function($) {
        $('#sync-department-select').select2({
            width: '100%',
            placeholder: '-- Select Department --',
            allowClear: true
        });
    });
</script>
@endpush

@endsection
