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
        .code-input {
            width: 50px;
            height: 64px;
            background: var(--bg-main);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-main);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .code-input:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.1);
            transform: translateY(-2px);
            outline: none;
        }
    </style>

    <div class="auth-vault glass-monolith" style="width: 100%; max-width: 500px; background: rgba(255, 255, 255, 0.95); border: 1px solid rgba(255,255,255,0.8); border-radius: 40px; padding: 3.5rem; backdrop-filter: blur(40px); box-shadow: 0 40px 100px -20px rgba(0,0,0,0.08); position: relative;">
        
        <div style="text-align: center; margin-bottom: 3rem;">
            <div style="width: 64px; height: 64px; background: rgba(99, 102, 241, 0.1); border-radius: 22px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                <i data-lucide="shield-alert" style="width: 32px; color: var(--primary);"></i>
            </div>
            <h2 style="color: var(--text-main); font-size: 1.75rem; font-weight: 950; letter-spacing: -0.04em; margin-bottom: 0.5rem;">Verify Protocol</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 600; line-height: 1.6;">
                A high-security verification code has been generated and sent to your registered email address. Please enter it below to complete your clearance.
            </p>
        </div>

        <form action="{{ route('2fa.verify') }}" method="POST" id="2faForm">
            @csrf
            <input type="hidden" name="code" id="verificationCode">
            
            <div style="display: flex; justify-content: space-between; gap: 0.75rem; margin-bottom: 3rem;">
                <input type="text" maxlength="1" class="code-input" autofocus>
                <input type="text" maxlength="1" class="code-input">
                <input type="text" maxlength="1" class="code-input">
                <input type="text" maxlength="1" class="code-input">
                <input type="text" maxlength="1" class="code-input">
                <input type="text" maxlength="1" class="code-input">
            </div>

            <button type="submit" class="auth-btn-primary" style="background: linear-gradient(135deg, var(--primary) 0%, #4338ca 100%); height: 60px; font-size: 1rem; border-radius: 20px;">
                <span>Confirm Identity</span>
                <i data-lucide="key-round"></i>
            </button>
        </form>

        <div style="text-align: center; margin-top: 2rem;">
            <p style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
                Didn't receive the code? 
                <a href="#" style="color: var(--primary); font-weight: 800; text-decoration: none; margin-left: 5px;">Re-transmit Secret</a>
            </p>
            <a href="{{ route('login') }}" style="display: inline-block; margin-top: 1.5rem; font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-decoration: none; text-transform: uppercase; letter-spacing: 0.05em;">
                ← Return to Login
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();

            const inputs = document.querySelectorAll('.code-input');
            const form = document.getElementById('2faForm');
            const hiddenInput = document.getElementById('verificationCode');

            inputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    if (e.target.value.length === 1 && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                    updateHiddenInput();
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
                        inputs[index - 1].focus();
                    }
                });
            });

            function updateHiddenInput() {
                let code = '';
                inputs.forEach(input => code += input.value);
                hiddenInput.value = code;
                
                // Auto-submit if all 6 digits are entered
                if (code.length === 6) {
                    // form.submit(); // Optional: uncomment for auto-submit
                }
            }
        });
    </script>
@endsection
