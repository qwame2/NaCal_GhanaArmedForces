@extends('layouts.auth')

@section('content')

<style>
    body {
        background: #f0f4ff;
    }

    .sync-wrapper {
        width: 100%;
        max-width: 460px;
    }

    /* === TOP STATUS BAR === */
    .sync-statusbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 10px 18px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .status-live {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.7rem;
        font-weight: 800;
        color: #10b981;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    .live-dot {
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
        animation: livepulse 2s infinite;
    }
    @keyframes livepulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.3); }
    }
    .status-step {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .step-pip {
        width: 24px;
        height: 4px;
        border-radius: 4px;
        background: #e2e8f0;
    }
    .step-pip.done  { background: #10b981; }
    .step-pip.active { background: #6366f1; }

    /* === MAIN CARD === */
    .sync-card {
        background: white;
        border: 1px solid rgba(99,102,241,0.12);
        border-radius: 28px;
        overflow: hidden;
        box-shadow:
            0 4px 6px -1px rgba(0,0,0,0.05),
            0 20px 50px -10px rgba(99,102,241,0.12);
    }

    /* === HERO BANNER === */
    .sync-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
        padding: 2.5rem 2.5rem 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .hero-grid {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
        background-size: 32px 32px;
    }
    .hero-glow {
        position: absolute;
        bottom: -60px;
        right: -60px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(139,92,246,0.4) 0%, transparent 70%);
    }
    .hero-content { position: relative; z-index: 2; }
    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 999px;
        padding: 4px 12px;
        font-size: 0.65rem;
        font-weight: 800;
        color: rgba(255,255,255,0.8);
        letter-spacing: 0.12em;
        text-transform: uppercase;
        margin-bottom: 1.25rem;
    }
    .hero-badge i { width: 12px; height: 12px; }

    .hero-title {
        font-size: 1.85rem;
        font-weight: 900;
        color: white;
        letter-spacing: -0.04em;
        line-height: 1.1;
        margin: 0 0 0.5rem;
    }
    .hero-title span {
        background: linear-gradient(135deg, #a5b4fc, #c4b5fd);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .hero-sub {
        font-size: 0.8rem;
        color: rgba(255,255,255,0.55);
        font-weight: 600;
        margin: 0;
    }

    .hero-icon-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 1.5rem;
        padding-top: 1.25rem;
        border-top: 1px solid rgba(255,255,255,0.1);
    }
    .hero-avatar {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: rgba(255,255,255,0.12);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hero-avatar i { width: 18px; color: rgba(255,255,255,0.85); }
    .hero-user-info { flex: 1; }
    .hero-user-name {
        font-size: 0.8rem;
        font-weight: 800;
        color: white;
        display: block;
    }
    .hero-user-role {
        font-size: 0.65rem;
        color: rgba(255,255,255,0.5);
        font-weight: 700;
        letter-spacing: 0.05em;
    }
    .hero-lock-chip {
        display: flex;
        align-items: center;
        gap: 5px;
        background: rgba(239,68,68,0.15);
        border: 1px solid rgba(239,68,68,0.25);
        border-radius: 8px;
        padding: 5px 10px;
        font-size: 0.6rem;
        font-weight: 800;
        color: #fca5a5;
        letter-spacing: 0.08em;
    }
    .hero-lock-chip i { width: 10px; }

    /* === FORM BODY === */
    .sync-body { padding: 2rem 2.5rem; }

    .info-strip {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        background: #f8f7ff;
        border: 1px solid #ede9fe;
        border-radius: 14px;
        padding: 12px 14px;
        margin-bottom: 1.75rem;
    }
    .info-strip i { width: 15px; color: #7c3aed; flex-shrink: 0; margin-top: 1px; }
    .info-strip p {
        margin: 0;
        font-size: 0.75rem;
        color: #5b21b6;
        font-weight: 600;
        line-height: 1.5;
    }

    /* === FIELDS === */
    .field-block { margin-bottom: 1.25rem; }
    .field-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        padding: 0 2px;
    }
    .field-label label {
        font-size: 0.7rem;
        font-weight: 800;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    .req-badge {
        font-size: 0.58rem;
        font-weight: 800;
        background: #f1f5f9;
        color: #64748b;
        padding: 2px 7px;
        border-radius: 4px;
        letter-spacing: 0.05em;
    }

    .field-input {
        display: flex;
        align-items: center;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        transition: all 0.25s ease;
        overflow: hidden;
    }
    .field-input:focus-within {
        background: white;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .field-icon {
        padding: 0 14px;
        display: flex;
        align-items: center;
    }
    .field-icon i { width: 16px; color: #94a3b8; transition: 0.2s; }
    .field-input:focus-within .field-icon i { color: #6366f1; }

    .field-input input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 14px 4px;
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        outline: none;
    }
    .field-input input::placeholder { color: #cbd5e1; font-weight: 600; }

    .eye-btn {
        padding: 0 14px;
        background: none;
        border: none;
        color: #cbd5e1;
        cursor: pointer;
        transition: 0.2s;
        display: flex;
    }
    .eye-btn:hover { color: #6366f1; }
    .eye-btn i { width: 16px; }

    .divider {
        height: 1px;
        background: #f1f5f9;
        margin: 1.5rem 0;
    }

    /* === SUBMIT BUTTON === */
    .sync-btn {
        width: 100%;
        height: 52px;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        border: none;
        border-radius: 14px;
        color: white;
        font-weight: 900;
        font-size: 0.9rem;
        letter-spacing: 0.03em;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 8px 25px rgba(99,102,241,0.3);
        position: relative;
        overflow: hidden;
    }
    .sync-btn::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.1) 100%);
        opacity: 0;
        transition: 0.3s;
    }
    .sync-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(99,102,241,0.4);
    }
    .sync-btn:hover::before { opacity: 1; }
    .sync-btn:active { transform: translateY(-1px); }
    .sync-btn i { width: 18px; }

    /* === FOOTER === */
    .sync-footer {
        text-align: center;
        padding: 1.25rem;
        border-top: 1px solid #f8fafc;
    }
    .logout-btn {
        background: none;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #94a3b8;
        font-size: 0.72rem;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
        letter-spacing: 0.03em;
    }
    .logout-btn:hover { color: #ef4444; }
    .logout-btn i { width: 13px; }
</style>

<div class="sync-wrapper">

    <!-- Status Bar -->
    <div class="sync-statusbar">
        <div class="status-live">
            <div class="live-dot"></div>
            Secure Connection Active
        </div>
        <div class="status-step">
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
                    Protocol: Mandatory Upgrade
                </div>
                <h1 class="hero-title">Security <span>Sync</span></h1>
                <p class="hero-sub">You need to set a new password before you can use the system.</p>

                <div class="hero-icon-row">
                    <div class="hero-avatar">
                        <i data-lucide="user"></i>
                    </div>
                    <div class="hero-user-info">
                        <span class="hero-user-name">{{ auth()->user()->name }}</span>
                        <span class="hero-user-role">{{ auth()->user()->role }} · {{ auth()->user()->department ?? 'No Sector' }}</span>
                    </div>
                    <div class="hero-lock-chip">
                        <i data-lucide="lock"></i>
                        TEMP KEY
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Body -->
        <div class="sync-body">

            <div class="info-strip">
                <i data-lucide="info"></i>
                <p>Your account was created with a temporary password. Please set a new password (min 8 chars, including a number, and cannot match your username) to continue.</p>
            </div>

            <form action="{{ route('password.update') }}" method="POST">
                @csrf

                @if($errors->any())
                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:12px; padding:12px 14px; margin-bottom:1.25rem; display:flex; gap:8px; align-items:flex-start;">
                    <i data-lucide="alert-circle" style="width:15px; color:#dc2626; flex-shrink:0; margin-top:1px;"></i>
                <span style="font-size:0.75rem; color:#991b1b; font-weight:600;">{{ $errors->first() }}</span>
                </div>
                @endif

                <div class="field-block">
                    <div class="field-label">
                        <label>New Security Key</label>
                        <span class="req-badge">MIN 8 CHARS + NUMBER</span>
                    </div>
                    <div class="field-input">
                        <div class="field-icon"><i data-lucide="key-round"></i></div>
                        <input type="password" name="password" id="pass-field" placeholder="Enter new password" required minlength="8" pattern="(?=.*\d).{8,}" title="Minimum 8 characters, including at least one number" autocomplete="new-password">
                        <button type="button" class="eye-btn" onclick="togglePass('pass-field', this)">
                            <i data-lucide="eye"></i>
                        </button>
                    </div>
                    <p style="font-size: 0.65rem; color: #64748b; font-weight: 700; margin-top: 6px; padding-left: 4px;">Requirement: Min 8 chars including a number. Cannot match username.</p>
                </div>

                <div class="field-block">
                    <div class="field-label">
                        <label>Confirm Key</label>
                        <span class="req-badge">MUST MATCH</span>
                    </div>
                    <div class="field-input">
                        <div class="field-icon"><i data-lucide="shield"></i></div>
                        <input type="password" name="password_confirmation" id="confirm-field" placeholder="Confirm new password" required minlength="8" pattern="(?=.*\d).{8,}" title="Minimum 8 characters, including at least one number" autocomplete="new-password">
                        <button type="button" class="eye-btn" onclick="togglePass('confirm-field', this)">
                            <i data-lucide="eye"></i>
                        </button>
                    </div>
                </div>

                <div class="divider"></div>

                <button type="submit" class="sync-btn">
                    <i data-lucide="refresh-cw"></i>
                    Change Password
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="sync-footer">
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="logout-btn">
                    <i data-lucide="log-out"></i>
                    Sign out instead
                </button>
            </form>
        </div>
    </div>

</div>

<script>
    function togglePass(id, btn) {
        const input = document.getElementById(id);
        const isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        btn.innerHTML = `<i data-lucide="${isPass ? 'eye-off' : 'eye'}"></i>`;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
</script>

@endsection
