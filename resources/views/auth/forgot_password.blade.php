@extends('layouts.auth')

@section('content')
<div class="sync-wrapper">

    <!-- Status Bar -->
    <div class="sync-statusbar">
        <div class="status-live">
            <div class="live-dot"></div>
            Secure Protocol Active
        </div>
        <div class="status-step">
            <div class="step-pip active"></div>
            <div class="step-pip"></div>
            <div class="step-pip"></div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="sync-card">

        <!-- Hero Banner -->
        <div class="sync-hero" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
            <div class="hero-grid"></div>
            <div class="hero-glow" style="background: radial-gradient(circle, rgba(245,158,11,0.2) 0%, transparent 70%);"></div>
            <div class="hero-content">
                <div class="hero-badge" style="background: rgba(245,158,11,0.1); border-color: rgba(245,158,11,0.2); color: #f59e0b;">
                    <i data-lucide="help-circle"></i>
                    Access Recovery
                </div>
                <h1 class="hero-title">Forgot <span>Password?</span></h1>
                <p class="hero-sub">Submit a request to the Head to reset your security credentials.</p>
            </div>
        </div>

        <!-- Form Body -->
        <div class="sync-body">

            <div class="info-strip" style="background: #fffbeb; border-color: #fef3c7;">
                <i data-lucide="info" style="color: #d97706;"></i>
                <p style="color: #92400e;">Enter your username. The Administrator will review your request and provide a one-time security code (OTP).</p>
            </div>

            <form action="{{ route('password.email') }}" method="POST">
                @csrf

                @if(session('success'))
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:12px 14px; margin-bottom:1.25rem; display:flex; gap:8px; align-items:flex-start;">
                    <i data-lucide="check-circle" style="width:15px; color:#16a34a; flex-shrink:0; margin-top:1px;"></i>
                    <span style="font-size:0.75rem; color:#166534; font-weight:600;">{{ session('success') }}</span>
                </div>
                @endif

                <div class="field-block">
                    <div class="field-label">
                        <label>Personnel Callsign (Username)</label>
                    </div>
                    <div class="field-input">
                        <div class="field-icon"><i data-lucide="user"></i></div>
                        <input type="text" name="username" placeholder="e.g. j_doe" required autofocus>
                    </div>
                </div>

                <div class="divider"></div>

                <button type="submit" class="sync-btn" style="background: #0f172a;">
                    <i data-lucide="send"></i>
                    Send Recovery Request
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="sync-footer">
            <a href="{{ route('login') }}" class="logout-btn">
                <i data-lucide="arrow-left"></i>
                Return to Login
            </a>
        </div>
    </div>

</div>

<style>
    body { background: #f0f4ff; font-family: 'Inter', sans-serif; }
    .sync-wrapper { width: 100%; max-width: 460px; }
    .sync-statusbar { display: flex; align-items: center; justify-content: space-between; background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 10px 18px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
    .status-live { display: flex; align-items: center; gap: 8px; font-size: 0.7rem; font-weight: 800; color: #10b981; text-transform: uppercase; letter-spacing: 0.08em; }
    .live-dot { width: 8px; height: 8px; background: #10b981; border-radius: 50%; animation: livepulse 2s infinite; }
    @keyframes livepulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.3); } }
    .status-step { display: flex; align-items: center; gap: 6px; }
    .step-pip { width: 24px; height: 4px; border-radius: 4px; background: #e2e8f0; }
    .step-pip.active { background: #6366f1; }
    .sync-card { background: white; border: 1px solid rgba(99,102,241,0.12); border-radius: 28px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 20px 50px -10px rgba(99,102,241,0.12); }
    .sync-hero { padding: 2.5rem 2.5rem 1.5rem; position: relative; overflow: hidden; }
    .hero-grid { position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px); background-size: 32px 32px; }
    .hero-glow { position: absolute; bottom: -60px; right: -60px; width: 200px; height: 200px; }
    .hero-content { position: relative; z-index: 2; }
    .hero-badge { display: inline-flex; align-items: center; gap: 6px; border: 1px solid rgba(255,255,255,0.15); border-radius: 999px; padding: 4px 12px; font-size: 0.65rem; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 1.25rem; }
    .hero-badge i { width: 12px; height: 12px; }
    .hero-title { font-size: 1.85rem; font-weight: 900; color: white; letter-spacing: -0.04em; line-height: 1.1; margin: 0 0 0.5rem; }
    .hero-title span { background: linear-gradient(135deg, #f59e0b, #fbbf24); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .hero-sub { font-size: 0.8rem; color: rgba(255,255,255,0.55); font-weight: 600; margin: 0; }
    .sync-body { padding: 2rem 2.5rem; }
    .info-strip { display: flex; gap: 10px; align-items: flex-start; border-radius: 14px; padding: 12px 14px; margin-bottom: 1.75rem; }
    .info-strip i { width: 15px; flex-shrink: 0; margin-top: 1px; }
    .info-strip p { margin: 0; font-size: 0.75rem; font-weight: 600; line-height: 1.5; }
    .field-block { margin-bottom: 1.25rem; }
    .field-label { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; padding: 0 2px; }
    .field-label label { font-size: 0.7rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.08em; }
    .field-input { display: flex; align-items: center; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; transition: all 0.25s ease; overflow: hidden; }
    .field-input:focus-within { background: white; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
    .field-icon { padding: 0 14px; display: flex; align-items: center; }
    .field-icon i { width: 16px; color: #94a3b8; transition: 0.2s; }
    .field-input:focus-within .field-icon i { color: #6366f1; }
    .field-input input { flex: 1; border: none; background: transparent; padding: 14px 4px; font-size: 0.9rem; font-weight: 700; color: #0f172a; outline: none; }
    .divider { height: 1px; background: #f1f5f9; margin: 1.5rem 0; }
    .sync-btn { width: 100%; height: 52px; border: none; border-radius: 14px; color: white; font-weight: 900; font-size: 0.9rem; letter-spacing: 0.03em; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    .sync-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(0,0,0,0.2); }
    .sync-footer { text-align: center; padding: 1.25rem; border-top: 1px solid #f8fafc; }
    .logout-btn { background: none; border: none; display: inline-flex; align-items: center; gap: 6px; color: #94a3b8; font-size: 0.72rem; font-weight: 700; cursor: pointer; text-decoration: none; transition: 0.2s; letter-spacing: 0.03em; }
    .logout-btn:hover { color: #6366f1; }
    .logout-btn i { width: 13px; }
</style>
@endsection
