<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('img/cropped_circle_image.png') }}">
    <title>NACOC | Security Portal</title>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard_theme.css') }}">
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
    </style>
</head>

<body>
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
        });
    </script>
    @stack('scripts')
</body>

</html>
