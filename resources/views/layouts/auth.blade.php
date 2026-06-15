@php
    $finalRejectedReset = isset($rejected_reset) && $rejected_reset;
    $finalRejectedUsername = isset($rejected_username) ? $rejected_username : session('pending_password_reset_username');
    $finalRejectedMessage = isset($rejected_message) ? $rejected_message : '';

    if (!$finalRejectedReset && $finalRejectedUsername) {
        $latestRequest = \App\Models\PasswordResetRequest::where('username', $finalRejectedUsername)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($latestRequest && $latestRequest->status === 'rejected') {
            $finalRejectedReset = true;
            $finalRejectedMessage = "Alert: Your password reset request has been rejected by the Head of Stores. Please contact Head of Stores for resolution.";
        } else {
            session()->forget('pending_password_reset_username');
        }
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- PWA Manifest & Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="NACOC IMS">
    <link rel="apple-touch-icon" href="{{ asset('img/cropped_circle_image.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('img/cropped_circle_image.png') }}">
    <title>{{ \App\Models\Setting::get('organization_name', 'NACOC') }} | Security Portal</title>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard_theme.css') }}">
    
    <!-- Scripts -->
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>
    <script src="{{ asset('js/lucide.min.js') }}"></script>

    <script>
        // Global Theme initialization
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    
    <style>
        body {
            background-color: var(--bg-main);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        
        .auth-page-wrapper {
            width: 100%;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Glassmorphism background effect */
        .auth-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            background: var(--bg-main);
        }

        .auth-blob {
            position: absolute;
            width: 600px;
            height: 600px;
            background: linear-gradient(135deg, var(--primary) 0%, #a855f7 100%);
            filter: blur(80px);
            opacity: 0.15;
            border-radius: 50%;
            z-index: -1;
        }

        [data-theme="dark"] .auth-blob {
            opacity: 0.1;
        }

        @keyframes lockOverlayIn {
            from { opacity: 0; transform: scale(0.97); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes shieldPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.3); }
            50%       { box-shadow: 0 0 0 14px rgba(239, 68, 68, 0); }
        }
        @keyframes rt-live {
            0%, 100% { box-shadow: 0 0 0 0 rgba(148, 163, 184, 0.5); }
            50% { box-shadow: 0 0 0 5px rgba(148, 163, 184, 0); }
        }
    </style>
</head>

<body>
    @if($finalRejectedReset)
    <div id="passwordResetRejectedOverlay" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); z-index: 10000; flex-direction: column; align-items: center; justify-content: center; text-align: center; display: flex; animation: lockOverlayIn 0.35s ease;">
        <div style="background: white; padding: 3rem 2.5rem; border-radius: 32px; box-shadow: 0 30px 80px rgba(0,0,0,0.3), 0 0 0 1px rgba(239,68,68,0.15); width: 90%; max-width: 440px; position: relative;">
            <!-- Pulsing Warning Icon -->
            <div style="width: 80px; height: 80px; background: rgba(239, 68, 68, 0.08); color: #ef4444; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; animation: shieldPulse 2s infinite;">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <!-- Badge -->
            <div style="display: inline-block; background: #fef2f2; color: #dc2626; font-size: 0.65rem; font-weight: 900; letter-spacing: 0.12em; padding: 6px 14px; border-radius: 999px; border: 1px solid #fecaca; margin-bottom: 1.25rem; text-transform: uppercase;">
                Reset Request Rejected
            </div>
            <!-- Title -->
            <h3 style="color: #0f172a; font-size: 1.5rem; font-weight: 950; letter-spacing: -0.03em; margin: 0 0 0.75rem;">Request Rejected</h3>
            <!-- Message -->
            <p style="color: #475569; font-size: 0.9rem; font-weight: 600; line-height: 1.65; margin: 0 0 1.5rem;">
                {{ $finalRejectedMessage }}
            </p>
        </div>
    </div>
    @endif

    <div class="auth-background">
        <div class="auth-blob" style="top: -200px; right: -200px;"></div>
        <div class="auth-blob" style="bottom: -200px; left: -200px; background: #6366f1;"></div>
    </div>

    <div class="auth-page-wrapper">
        @yield('content')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
            
            // Dynamic Username input status check
            const usernameInputs = document.querySelectorAll('input[name="username"]');
            usernameInputs.forEach(input => {
                let debounceTimer;
                const checkStatus = () => {
                    const username = input.value.trim();
                    if (username.length >= 3) {
                        fetch(`/api/check-reset-status?username=${encodeURIComponent(username)}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.rejected) {
                                    window.location.reload();
                                }
                            })
                            .catch(err => console.error("Error checking reset status:", err));
                    }
                };
                input.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(checkStatus, 700);
                });
                input.addEventListener('blur', checkStatus);
            });
        });

        @if($finalRejectedReset)
        document.addEventListener('DOMContentLoaded', () => {
            const username = "{{ $finalRejectedUsername }}";
            const pollInterval = setInterval(() => {
                fetch(`/api/check-reset-status?username=${encodeURIComponent(username)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.rejected) {
                            clearInterval(pollInterval);
                            window.location.reload();
                        }
                    })
                    .catch(err => console.error("Error polling reset status:", err));
            }, 3000);
        });
        @endif
    </script>
    @stack('scripts')
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker registered successfully:', reg.scope))
                    .catch(err => console.log('Service Worker registration failed:', err));
            });
        }
    </script>
</body>
</html>
