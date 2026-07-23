@extends('layouts.auth')

@section('content')
<div class="sync-wrapper">

    <!-- Status Bar -->
    <div class="sync-statusbar">
        <div class="status-live">
            <div class="live-dot"></div>
            <span>Secure Protocol Active</span>
        </div>
        <div class="status-step">
            <span class="step-label">Step 1 of 3</span>
            <div class="step-pip active"></div>
            <div class="step-pip"></div>
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
                    <i data-lucide="shield-alert"></i>
                    <span>Access Recovery</span>
                </div>
                <h1 class="hero-title">Forgot <span>Password?</span></h1>
                <p class="hero-sub">Request a security credentials reset from the Head of Stores.</p>
            </div>
        </div>

        <!-- Form Body -->
        <div class="sync-body">

            <div class="info-strip">
                <div class="info-icon-wrap">
                    <i data-lucide="info"></i>
                </div>
                <p>Enter your personnel callsign (username). The administrator will authorize your request and generate a secure OTP.</p>
            </div>

            <form action="{{ route('password.email') }}" method="POST" id="recoveryForm">
                @csrf

                @if(session('success'))
                <div class="alert-success-wrap">
                    <i data-lucide="check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                <div class="field-block">
                    <div class="field-label">
                        <label for="username">Personnel Username</label>
                    </div>
                    <div class="field-input">
                        <div class="field-icon"><i data-lucide="user-check"></i></div>
                        <input type="text" id="username" name="username" placeholder="e.g., k_addo" required autofocus
                            value="{{ request('username') }}">
                    </div>
                </div>

                <div class="divider"></div>

                <button type="submit" class="sync-btn">
                    <i data-lucide="send-horizontal"></i>
                    <span>Transmit Recovery Request</span>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="sync-footer">
            <a href="{{ route('login') }}" class="logout-btn">
                <i data-lucide="arrow-left-circle"></i>
                <span>Return to Security Login</span>
            </a>
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
        max-width: 620px;
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
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
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
        background: radial-gradient(circle, rgba(136, 19, 55, 0.25) 0%, transparent 70%);
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
        background: rgba(136, 19, 55, 0.2);
        border: 1px solid rgba(136, 19, 55, 0.3);
        border-radius: 99px;
        padding: 5px 14px;
        font-size: 0.65rem;
        font-weight: 800;
        color: #fca5a5;
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
        background: linear-gradient(135deg, #fecaca, #fbcfe8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-sub {
        font-size: 0.88rem;
        color: #94a3b8;
        font-weight: 500;
        margin: 0;
        line-height: 1.45;
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

    /* Input Field Block */
    .field-block {
        margin-bottom: 1.5rem;
    }

    .field-label {
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

    .field-input {
        display: flex;
        align-items: center;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .field-input:focus-within {
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

    .field-input:focus-within .field-icon {
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

    /* Success Alert Wrapper */
    .alert-success-wrap {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 16px;
        padding: 14px 18px;
        margin-bottom: 1.5rem;
        display: flex;
        gap: 10px;
        align-items: center;
        animation: alertSlideIn 0.3s ease forwards;
    }

    @keyframes alertSlideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .alert-success-wrap i {
        width: 18px;
        height: 18px;
        color: #059669;
        flex-shrink: 0;
    }

    .alert-success-wrap span {
        font-size: 0.82rem;
        color: #065f46;
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
        box-shadow: 0 6px 15px rgba(136, 19, 55, 0.2);
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
        text-decoration: none;
        transition: all 0.2s ease;
        padding: 6px 12px;
        border-radius: 8px;
    }

    .logout-btn:hover {
        color: #881337;
        background: rgba(136, 19, 55, 0.05);
    }

    .logout-btn i {
        width: 16px;
        height: 16px;
    }
</style>
@endsection
