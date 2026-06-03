<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Head of Stores | Strategic Registry</title>
    <link href="{{ asset('css/css2.css') }}" rel="stylesheet">
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <link href="{{ asset('css/vendor/select2.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('js/vendor/select2.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-glow: rgba(79, 70, 229, 0.1);
            --primary-hover: #4338ca;
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --text-heading: #0f172a;
            --border-color: #e2e8f0;
            --shadow-luxe: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius-luxe: 16px;
        }

        /* Toast System */
        .toast-container {
            position: fixed;
            top: 2rem;
            right: 2rem;
            z-index: 999999;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            pointer-events: none;
        }

        .toast {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            min-width: 320px;
            max-width: 400px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            gap: 1rem;
            transform: translateX(120%);
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            pointer-events: auto;
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .toast.show { transform: translateX(0); }

        .toast-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .toast-success { border-left: 4px solid #10b981; }
        .toast-success .toast-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }

        .toast-error { border-left: 4px solid #ef4444; }
        .toast-error .toast-icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

        .toast-warning { border-left: 4px solid #f59e0b; }
        .toast-warning .toast-icon { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }

        .toast-content { flex: 1; }
        .toast-title { display: block; font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 2px; }
        .toast-message { font-size: 0.75rem; color: #64748b; margin: 0; line-height: 1.4; }

        .toast-close {
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .toast-close:hover { background: #f1f5f9; color: #64748b; }

        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(0,0,0,0.05);
            width: 100%;
        }

        .toast-progress-bar {
            height: 100%;
            background: currentColor;
            width: 100%;
            transform-origin: left;
            animation: toast-progress linear forwards;
        }

        @keyframes toast-progress {
            from { transform: scaleX(1); }
            to { transform: scaleX(0); }
        }

        :root {
            --bg-body: #f8fafc;
            --sidebar-bg: #ffffff;
            --text-heading: #0f172a;
            --text-body: #475569;
            --text-muted: #94a3b8;
            --shadow-luxe: 0 10px 40px rgba(0, 0, 0, 0.04), 0 2px 10px rgba(0, 0, 0, 0.02);
            --shadow-sidebar: 10px 0 30px rgba(0, 0, 0, 0.1);
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-body);
            font-family: "outfit", serif;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        /* Luminous Light Sidebar with Depth */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1000;
            box-shadow: var(--shadow-sidebar);
            border-right: 1px solid rgba(0,0,0,0.02);
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-brand {
            padding: 3rem 2rem;
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
        }

        .mobile-sidebar-close {
            display: none;
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            align-items: center;
            justify-content: center;
            color: #64748b;
            cursor: pointer;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.2);
        }

        .brand-text h1 {
            font-size: 1.15rem;
            font-weight: 900;
            color: var(--text-heading);
            margin: 0;
            letter-spacing: -0.02em;
        }

        .brand-text span {
            font-size: 0.65rem;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            display: block;
        }

        .nav-scroller {
            flex: 1;
            padding: 0 1rem;
            overflow-y: auto;
        }

        /* Custom Scrollbar for nav-scroller */
        .nav-scroller::-webkit-scrollbar {
            width: 4px;
        }
        .nav-scroller::-webkit-scrollbar-track {
            background: transparent;
        }
        .nav-scroller::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 4px;
        }
        .nav-scroller:hover::-webkit-scrollbar-thumb {
            background: #cbd5e1;
        }

        .nav-label {
            font-size: 0.7rem;
            font-weight: 900;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.15em;
            padding: 0 1.5rem;
            margin-bottom: 1.25rem;
            display: block;
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0 0 3rem 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0.85rem 1.25rem;
            color: var(--text-body);
            text-decoration: none;
            border-radius: 16px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 4px;
        }

        .nav-link:hover {
            background: #f1f5f9;
            color: var(--primary);
            transform: translateX(4px);
        }

        .nav-link.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(79, 70, 229, 0.1);
        }

        .nav-link i {
            width: 18px;
            height: 18px;
            opacity: 0.7;
        }

        .nav-link.active i { opacity: 1; }

        /* Bottom Profile Module */
        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid #f1f5f9;
        }

        .profile-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px;
            background: #f8fafc;
            border-radius: 16px;
            border: 1px solid #edf2f7;
            transition: 0.3s;
        }

        .profile-pill:hover { background: white; box-shadow: var(--shadow-luxe); }

        .avatar-wrap {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .user-meta { flex: 1; overflow: hidden; }
        .u-name { display: block; font-size: 0.85rem; font-weight: 800; color: var(--text-heading); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .u-role { font-size: 0.65rem; color: var(--text-muted); font-weight: 700; }

        .exit-btn {
            background: #fff1f2;
            color: #e11d48;
            border: 1px solid #fecdd3;
            padding: 6px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        .exit-btn:hover { background: #e11d48; color: white; transform: scale(1.1); }

        /* Main Viewport */
        .main-wrapper {
            flex: 1;
            margin-left: 280px;
            padding: 0 2.5rem 4rem 2.5rem;
            min-width: 0;
            position: relative;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Content width cap — prevents extreme stretching on ultrawide monitors */
        .main-wrapper > *:not(header) {
            max-width: 1600px;
            width: 100%;
        }

        /* Mobile Trigger */
        .mobile-nav-toggle {
            display: none;
            width: 44px;
            height: 44px;
            background: white;
            border: 1px solid #edf2f7;
            border-radius: 12px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow-luxe);
            color: var(--text-heading);
            z-index: 1100;
        }

        /* Responsive Breakpoints */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 20px 0 60px rgba(0, 0, 0, 0.1);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
                padding: 0 1.25rem 2rem 1.25rem;
            }
            .mobile-nav-toggle {
                display: flex;
            }
            .mobile-sidebar-close {
                display: flex;
            }
            .view-header {
                padding: 1rem 0;
                margin-bottom: 2rem;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(15, 23, 42, 0.4);
                backdrop-filter: blur(4px);
                z-index: 999;
            }
            .sidebar-overlay.active {
                display: block;
            }
        }

        @media (max-width: 640px) {
            .view-header {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
                height: auto;
                position: relative;
                backdrop-filter: none;
                background: var(--bg-body);
                border: none;
            }
            .header-actions {
                justify-content: space-between;
                width: 100%;
            }
            .title-group {
                width: 100%;
            }
            .title-capsule {
                width: 100%;
            }
        }

        .view-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 0;
            margin-bottom: 3rem;
            position: sticky;
            top: 0;
            background: rgba(248, 250, 252, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 900;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .live-status {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            background: white;
            border-radius: 14px;
            border: 1px solid #edf2f7;
            font-size: 0.75rem;
            font-weight: 800;
            color: #10b981;
            box-shadow: var(--shadow-luxe);
        }

        .title-capsule {
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid #e2e8f0;
            padding: 4px 16px 4px 6px;
            border-radius: 16px;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            transition: all 0.3s;
        }

        .title-capsule:hover {
            border-color: var(--primary);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.08);
        }

        .capsule-prefix {
            width: 34px;
            height: 34px;
            background: var(--primary-glow);
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .capsule-prefix i { width: 16px; height: 16px; }

        .title-capsule h2 {
            font-size: 1.1rem;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin: 0;
        }

        .capsule-tag {
            font-size: 0.6rem;
            font-weight: 900;
            background: #f1f5f9;
            color: #64748b;
            padding: 2px 8px;
            border-radius: 6px;
            letter-spacing: 0.05em;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-icon-btn {
            background: white;
            border: 1px solid #edf2f7;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-body);
            position: relative;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: var(--shadow-luxe);
        }

        .nav-icon-btn:hover {
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            border-color: var(--primary-glow);
        }

        .nav-icon-btn i { width: 20px; height: 20px; }

        .alert-dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 10px;
            height: 10px;
            border: 2px solid white;
            border-radius: 50%;
        }
        #global-unread-badge[style*="display: block"] {
            display: block !important;
        }

        .pulse-dot { width: 8px; height: 8px; background: #10b981; border-radius: 50%; animation: shadow-pulse 2s infinite; }
        @keyframes shadow-pulse { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }

        /* Minimize button styling */
        .sidebar-minimize-btn {
            display: none !important;
        }

        /* Sidebar Minimized State */
        @media (min-width: 1025px) {
            .sidebar-minimize-btn {
                display: flex !important;
                position: absolute;
                right: -14px;
                top: 50%;
                transform: translateY(-50%);
                background: white !important;
                border: 1px solid var(--border-color) !important;
                width: 28px !important;
                height: 28px !important;
                border-radius: 50% !important;
                align-items: center;
                justify-content: center;
                color: #64748b !important;
                cursor: pointer;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
                z-index: 1001;
            }
            .sidebar-minimize-btn:hover {
                color: var(--primary) !important;
                background: #f8fafc !important;
                border-color: var(--primary-glow) !important;
                transform: translateY(-50%) scale(1.05);
            }
            .sidebar.minimized {
                width: 80px !important;
            }
            .sidebar.minimized .brand-text,
            .sidebar.minimized .nav-label,
            .sidebar.minimized .nav-link span,
            .sidebar.minimized .sidebar-footer .user-meta,
            .sidebar.minimized .sidebar-footer form {
                display: none !important;
            }
            .sidebar.minimized .sidebar-brand {
                padding: 2rem 0.5rem !important;
                justify-content: center !important;
            }
            .sidebar.minimized .brand-icon {
                margin: 0 !important;
            }
            .sidebar.minimized .nav-scroller {
                padding: 0 0.5rem !important;
            }
            .sidebar.minimized .nav-link {
                padding: 0.85rem !important;
                justify-content: center !important;
                gap: 0 !important;
            }
            .sidebar.minimized .sidebar-footer {
                padding: 1.5rem 0.5rem !important;
            }
            .sidebar.minimized .profile-pill {
                padding: 0 !important;
                background: transparent !important;
                border: none !important;
                box-shadow: none !important;
                justify-content: center !important;
            }
            .sidebar.minimized .profile-pill a {
                justify-content: center !important;
                gap: 0 !important;
                flex: none !important;
                width: 100% !important;
            }
            .sidebar.minimized .avatar-wrap {
                margin: 0 !important;
                width: 36px !important;
                height: 36px !important;
            }
            .sidebar.minimized .nav-link span[id^="sidebar-badge-"] {
                display: none !important;
            }
            .main-wrapper.sidebar-minimized {
                margin-left: 80px !important;
                padding-left: 2.5rem !important;
                padding-right: 2.5rem !important;
            }
        }

        /* Fullscreen Pop-up Backdrop Blur and Overlay covering the entire screen */
        .modal-overlay,
        .bottom-sheet-overlay,
        #legacyAuditModal,
        #logDetailsModal,
        #messageDetailModal {
            z-index: 1000000 !important;
            position: fixed !important;
            inset: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: rgba(15, 23, 42, 0.5) !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
        }
        .bottom-sheet {
            z-index: 1000001 !important;
        }
        .swal2-container {
            z-index: 1000005 !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>
    <aside class="sidebar" id="sidebar">
        <script>
            if (localStorage.getItem('sidebar-minimized') === 'true') {
                document.getElementById('sidebar').classList.add('minimized');
            }
        </script>
        <div class="sidebar-brand">
            <div class="brand-icon" style="background: transparent; box-shadow: none;">
                <img src="{{ asset('img/NACOC.png') }}" alt="{{ \App\Models\Setting::get('organization_name', 'NACOC') }} Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <div class="brand-text" style="flex: 1; min-width: 0;">
                <h1 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ \App\Models\Setting::get('organization_name', 'ADMIN CORE') }}</h1>
                <span>Inventory Management System(NSIMs)</span>
            </div>
            <button type="button" class="sidebar-minimize-btn" id="sidebar-minimize-btn" title="Minimize Sidebar">
                <i data-lucide="chevron-left" id="minimize-icon" style="width: 16px; height: 16px;"></i>
            </button>
            <button class="mobile-sidebar-close" id="mobile-sidebar-close">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <div class="nav-scroller">
            <span class="nav-label">Management</span>
            <ul class="nav-list">
                <li>
                    <a href="{{ route('admin.index') }}" class="nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>User Details</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.logs') }}" class="nav-link {{ request()->routeIs('admin.logs') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                        <span>System Activities</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.inventory') }}" class="nav-link {{ request()->routeIs('admin.inventory') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.27 6.96 8.73 5.05 8.73-5.05"/><path d="M12 22.08V12"/></svg>
                        <span>Inventory Oversight</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14.5 2 14.5 7.5 20 7.5"/><path d="M12 13v5"/><path d="M16 13v5"/><path d="M8 13v5"/></svg>
                        <span>Report Generation</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.requisitions') }}" class="nav-link {{ request()->routeIs('admin.requisitions') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        <span>Store Requisitions</span>
                        <span id="sidebar-badge-requisitions" style="background: #ef4444; color: white; padding: 2px 6px; border-radius: 99px; font-size: 0.65rem; font-weight: 800; margin-left: auto; {{ (!isset($pendingRequisitionsCount) || $pendingRequisitionsCount <= 0) ? 'display: none;' : '' }}">
                            {{ $pendingRequisitionsCount ?? 0 }}
                        </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.index') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        <span>System Alerts</span>
                        <span id="sidebar-badge-alerts" style="background: #ef4444; color: white; padding: 2px 6px; border-radius: 99px; font-size: 0.65rem; font-weight: 800; margin-left: auto; {{ (!isset($globalNotificationCount) || $globalNotificationCount <= 0) ? 'display: none;' : '' }}">
                            {{ $globalNotificationCount ?? 0 }}
                        </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.permissions') }}" class="nav-link {{ request()->routeIs('admin.permissions') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span>Permissions</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.password.requests') }}" class="nav-link {{ request()->routeIs('admin.password.requests') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4.1"/><path d="m10.5 12.5 2.8 2.8a1 1 0 0 0 1.4 0l2.8-2.8"/><circle cx="7" cy="17" r="5"/></svg>
                        <span>Password Requests</span>
                        <span id="sidebar-badge-password" style="background: #ef4444; color: white; padding: 2px 6px; border-radius: 99px; font-size: 0.65rem; font-weight: 800; margin-left: auto; {{ (!isset($pendingPasswordRequests) || $pendingPasswordRequests <= 0) ? 'display: none;' : '' }}">
                            {{ $pendingPasswordRequests ?? 0 }}
                        </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.archive') }}" class="nav-link {{ request()->routeIs('admin.archive') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="5" x="2" y="3" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/></svg>
                        <span>System Archive</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.history') }}" class="nav-link {{ request()->routeIs('admin.history') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
                        <span>History</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.messages') }}" class="nav-link {{ request()->routeIs('admin.messages') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <span>Staff Messages</span>
                        <span id="sidebar-badge-messages" style="background: #ef4444; color: white; padding: 2px 6px; border-radius: 99px; font-size: 0.65rem; font-weight: 800; margin-left: auto; {{ (!isset($unreadMessagesCount) || $unreadMessagesCount <= 0) ? 'display: none;' : '' }}">
                            {{ $unreadMessagesCount ?? 0 }}
                        </span>
                    </a>
                </li>
            </ul>

            <span class="nav-label">Parameters</span>
            <ul class="nav-list">
                <li>
                    <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span>Settings</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Personal Settings</span>
                    </a>
                </li>
                <li style="margin-top: 2.5rem;">
                    <a href="#" onclick="confirmSelfDeactivation(event)" class="nav-link" style="color: #ef4444;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="12" x="3" y="10" rx="2"/><path d="M7 10V7a5 5 0 0 1 10 0v3"/></svg>
                        <span>Deactivate Account</span>
                    </a>
                    <form id="selfDeactivateForm" action="{{ route('admin.self_deactivate') }}" method="POST" style="display: none;">
                        @csrf
                        <input type="hidden" name="password" id="selfDeactivatePassword">
                    </form>
                </li>
            </ul>
        </div>

        <div class="sidebar-footer">
            <div class="profile-pill" style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                <a href="{{ route('settings.index') }}" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; flex: 1; min-width: 0;">
                    <div class="avatar-wrap">
                        <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2364748b'><circle cx='12' cy='8' r='4'/><path d='M12 14c-4.42 0-8 3.58-8 8h16c0-4.42-3.58-8-8-8z'/></svg>" }}" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="user-meta">
                        <span class="u-name">{{ auth()->user()->name }}</span>
                        <span class="u-role">Head of Stores</span>
                    </div>
                </a>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0; flex-shrink: 0;">
                    @csrf
                    <button type="submit" class="exit-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="main-wrapper" id="main-wrapper">
        <script>
            if (localStorage.getItem('sidebar-minimized') === 'true') {
                document.getElementById('main-wrapper').classList.add('sidebar-minimized');
            }
        </script>
        <header class="view-header">
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="mobile-nav-toggle" id="mobile-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                </button>
                <div class="title-group">
                    <div class="title-capsule">
                        <div class="capsule-prefix">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <h2>@yield('title')</h2>
                        <span class="capsule-tag">HEAD</span>
                    </div>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.messages') }}" class="nav-icon-btn" id="admin-message-btn" title="Internal Communications" style="position: relative;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span id="global-unread-badge" style="display: none; position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; font-size: 0.6rem; font-weight: 800; padding: 2px 6px; border-radius: 99px; border: 2px solid white; transition: 0.3s;">0</span>
                </a>
                <div style="position: relative;" id="admin-notification-wrapper">
                    <button class="nav-icon-btn" id="admin-notification-btn" title="System Notifications">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        @if($globalNotificationCount > 0)
                        <span class="alert-dot"></span>
                        @endif
                    </button>

                    <!-- Notification Dropdown -->
                    <div id="admin-notification-dropdown" style="display: none; position: absolute; top: calc(100% + 15px); right: 0; width: 340px; background: white; z-index: 2000; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.1); border: 1px solid #edf2f7; overflow: hidden; animation: slideDown 0.3s ease;">
                        <style>
                            @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
                            .notif-item:hover { background: #f8fafc; }
                            .notif-item { transition: 0.2s; }
                        </style>
                        <div style="padding: 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="font-size: 1rem; font-weight: 900; color: #0f172a; margin: 0;">System Alerts</h3>
                            @if($globalNotificationCount > 0)
                            <span style="font-size: 0.65rem; background: #fef2f2; color: #ef4444; padding: 4px 10px; border-radius: 8px; font-weight: 800;">{{ $globalNotificationCount }} ACTIVE</span>
                            @endif
                        </div>
                        <div style="max-height: 400px; overflow-y: auto;">
                            @forelse($globalNotifications as $notif)
                            <a href="{{ route($notif['route']) }}" class="notif-item" style="display: flex; gap: 1rem; padding: 1.25rem 1.5rem; text-decoration: none; border-bottom: 1px solid #f1f5f9;">
                                <div style="width: 44px; height: 44px; border-radius: 12px; background: {{ $notif['type'] === 'warning' ? '#fffbeb' : '#fef2f2' }}; color: {{ $notif['type'] === 'warning' ? '#f59e0b' : '#ef4444' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i data-lucide="{{ $notif['icon'] }}" style="width: 20px;"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 800; color: #0f172a; font-size: 0.85rem; margin-bottom: 4px;">{{ $notif['title'] }}</div>
                                    <div style="font-size: 0.75rem; color: #64748b; line-height: 1.5;">{{ $notif['message'] }}</div>
                                </div>
                            </a>
                            @empty
                            <div style="padding: 3.5rem 2rem; text-align: center; color: #94a3b8;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 1rem; opacity: 0.3;"><path d="M18.4 12c.4 3.8 2.6 5 2.6 5H3s3-2 3-9c0-1.2.3-2.3.9-3.3"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
                                <p style="font-size: 0.85rem; font-weight: 700; color: #475569;">No system alerts</p>
                                <p style="font-size: 0.7rem;">System is running within normal parameters.</p>
                            </div>
                            @endforelse
                        </div>
                        <div style="padding: 1.25rem; text-align: center; background: #f8fafc;">
                            <a href="{{ route('notifications.index') }}" style="font-size: 0.75rem; font-weight: 800; color: var(--primary); text-decoration: none;">Launch Intelligence Center</a>
                        </div>
                    </div>
                </div>
                <div class="live-status">
                    <div class="pulse-dot"></div>
                    <span>SYSTEM ONLINE</span>
                </div>
            </div>
        </header>

        @yield('content')
    </main>

    <script>
        // Sidebar Minimize Toggle
        const minimizeBtn = document.getElementById('sidebar-minimize-btn');
        const mainWrapper = document.getElementById('main-wrapper');
        const minimizeIcon = document.getElementById('minimize-icon');

        // Set initial icon based on state
        if (localStorage.getItem('sidebar-minimized') === 'true') {
            if (minimizeIcon) {
                minimizeIcon.setAttribute('data-lucide', 'chevron-right');
            }
        }

        if (minimizeBtn && mainWrapper) {
            minimizeBtn.addEventListener('click', () => {
                const sidebarEl = document.getElementById('sidebar');
                const isMinimized = sidebarEl.classList.toggle('minimized');
                mainWrapper.classList.toggle('sidebar-minimized', isMinimized);
                localStorage.setItem('sidebar-minimized', isMinimized ? 'true' : 'false');

                if (minimizeIcon) {
                    minimizeIcon.setAttribute('data-lucide', isMinimized ? 'chevron-right' : 'chevron-left');
                    if (window.lucide) lucide.createIcons();
                }
            });
        }

        lucide.createIcons();

        // Mobile Sidebar Toggle
        const mobileToggle = document.getElementById('mobile-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const mobileClose = document.getElementById('mobile-sidebar-close');

        if (mobileToggle && sidebar && overlay) {
            mobileToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });

            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });

            if (mobileClose) {
                mobileClose.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                });
            }
        }

        // Admin Notification Toggle
        const adminNotifBtn = document.getElementById('admin-notification-btn');
        const adminNotifDropdown = document.getElementById('admin-notification-dropdown');

        if (adminNotifBtn && adminNotifDropdown) {
            adminNotifBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isVisible = adminNotifDropdown.style.display === 'block';
                adminNotifDropdown.style.display = isVisible ? 'none' : 'block';
            });

            document.addEventListener('click', (e) => {
                if (!adminNotifBtn.contains(e.target) && !adminNotifDropdown.contains(e.target)) {
                    adminNotifDropdown.style.display = 'none';
                }
            });
        }

        // Real-time Admin Notification Refresh Logic
        window.refreshNotifications = function() {
            fetch("{{ route('api.notifications', [], false) }}")
                .then(res => {
                    const contentType = res.headers.get("content-type");
                    if (res.status === 200 && contentType && contentType.indexOf("application/json") !== -1) {
                        return res.json();
                    }
                    return null;
                })
                .then(data => {
                    if (!data) return;
                    // Update Navbar Bell Dot
                    const btn = document.getElementById('admin-notification-btn');
                    if (btn) {
                        let alertDot = btn.querySelector('.alert-dot');
                        if (data.count > 0) {
                            if (!alertDot) {
                                alertDot = document.createElement('span');
                                alertDot.className = 'alert-dot';
                                btn.appendChild(alertDot);
                            }
                            alertDot.style.display = 'block';
                        } else if (alertDot) {
                            alertDot.style.display = 'none';
                        }
                    }

                    // Update Dropdown Content
                    const dropdown = document.getElementById('admin-notification-dropdown');
                    if (dropdown) {
                        const list = dropdown.querySelector('div[style*="max-height: 400px"]');
                        if (list) {
                            if (data.notifications.length === 0) {
                                list.innerHTML = `
                                    <div style="padding: 3.5rem 2rem; text-align: center; color: #94a3b8;">
                                        <i data-lucide="bell-off" style="width: 36px; height: 36px; margin-bottom: 1rem; opacity: 0.3;"></i>
                                        <p style="font-size: 0.85rem; font-weight: 700; color: #475569;">No system alerts</p>
                                        <p style="font-size: 0.7rem;">System is running within normal parameters.</p>
                                    </div>
                                `;
                            } else {
                                let html = '';
                                data.notifications.forEach(notif => {
                                    const routeUrl = notif.route === 'admin.index' ? "{{ route('admin.index') }}" : "{{ route('dashboard') }}";
                                    const cleanDesc = notif.title.includes(': ') ? notif.title.split(': ')[1] : notif.title;
                                    html += `
                                        <div style="position: relative; border-bottom: 1px solid #f1f5f9;">
                                            <a href="${routeUrl}" class="notif-item" style="display: flex; gap: 1rem; padding: 1.25rem 1.5rem; padding-right: 3.5rem; text-decoration: none;">
                                                <div style="width: 44px; height: 44px; border-radius: 12px; background: ${notif.type === 'warning' ? '#fffbeb' : '#fef2f2'}; color: ${notif.type === 'warning' ? '#f59e0b' : '#ef4444'}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    <i data-lucide="${notif.icon}" style="width: 20px;"></i>
                                                </div>
                                                <div style="flex: 1;">
                                                    <div style="font-weight: 800; color: #0f172a; font-size: 0.85rem; margin-bottom: 4px;">${notif.title}</div>
                                                    <div style="font-size: 0.75rem; color: #64748b; line-height: 1.5;">${notif.message}</div>
                                                </div>
                                            </a>
                                            <button onclick="event.stopPropagation(); window.dismissNotification('${cleanDesc}')" style="position: absolute; top: 1.25rem; right: 1rem; background: transparent; border: none; color: #94a3b8; cursor: pointer; padding: 6px; border-radius: 8px; transition: 0.2s;" onmouseover="this.style.background='#fef2f2'; this.style.color='#ef4444'" onmouseout="this.style.background='transparent'; this.style.color='#94a3b8'">
                                                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                                            </button>
                                        </div>
                                    `;
                                });
                                list.innerHTML = html;
                            }
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        }

                        const headerBadge = dropdown.querySelector('span[style*="background: #fef2f2"]');
                        if (headerBadge) {
                            headerBadge.innerText = data.count + ' ACTIVE';
                            headerBadge.style.display = data.count > 0 ? 'inline-block' : 'none';
                        }
                    }

                    // Sync to notifications page if active
                    window.dispatchEvent(new CustomEvent('notificationsSynced', { detail: data }));
                })
                .catch(err => {});
        };

        // Start polling (every 30 seconds)
        setInterval(window.refreshNotifications, 30000);

        window.dismissNotification = function(description) {
            // Optimistic UI Update: Instantly remove/hide elements containing this description
            const allItems = document.querySelectorAll('.notification-item, .notif-item, [style*="position: relative; border-bottom: 1px solid #f1f5f9"]');
            allItems.forEach(item => {
                if (item.innerText.includes(description)) {
                    item.style.transition = '0.3s all';
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(20px)';
                    setTimeout(() => item.remove(), 300);
                }
            });

            fetch("{{ route('api.notifications.dismiss', [], false) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ description: description })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.refreshNotifications();
                }
            });
        };

        // Global Message Badge Sync
        window.refreshUnreadMessages = function() {
            const isMessagePage = window.location.href.includes('/admin/messages');
            if (isMessagePage) {
                const badge = document.getElementById('global-unread-badge');
                if (badge) badge.style.display = 'none';
                return;
            }

            fetch("{{ route('api.total-unread') }}")
                .then(res => {
                    const contentType = res.headers.get("content-type");
                    if (res.status === 200 && contentType && contentType.indexOf("application/json") !== -1) {
                        return res.json();
                    }
                    return null;
                })
                .then(data => {
                    if (!data) return;
                    const badge = document.getElementById('global-unread-badge');
                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.style.display = 'block';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                })
                .catch(err => {});
        };

        setInterval(window.refreshUnreadMessages, 10000);
        window.refreshUnreadMessages();
    // Global Premium Tooltip Engine
    const initTooltips = () => {
        // Convert title attributes to data-tooltip to avoid default browser tooltips
        document.querySelectorAll('[title]').forEach(el => {
            const title = el.getAttribute('title');
            if (title && !el.hasAttribute('data-tooltip')) {
                el.setAttribute('data-tooltip', title);
                el.removeAttribute('title');
            }
        });
    };

    // Global Tooltip DOM Element
    const tooltipEl = document.createElement('div');
    tooltipEl.className = 'global-premium-tooltip';
    document.body.appendChild(tooltipEl);

    // Tooltip Interaction Logic
    let tooltipTimeout;

    document.addEventListener('mouseover', (e) => {
        const target = e.target.closest('[data-tooltip]');
        if (target) {
            const text = target.getAttribute('data-tooltip');
            if (!text) return;

            tooltipEl.textContent = text;
            tooltipEl.classList.add('visible');

            // Position calculation
            const rect = target.getBoundingClientRect();
            const tooltipRect = tooltipEl.getBoundingClientRect();

            let top = rect.top - tooltipRect.height - 12;
            let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);

            // Boundary checks
            tooltipEl.classList.remove('place-bottom');
            if (top < 10) {
                top = rect.bottom + 12;
                tooltipEl.classList.add('place-bottom');
            }

            if (left < 10) left = 10;
            if (left + tooltipRect.width > window.innerWidth - 10) {
                left = window.innerWidth - tooltipRect.width - 10;
            }

            tooltipEl.style.top = top + 'px';
            tooltipEl.style.left = left + 'px';
        }
    });

    document.addEventListener('mouseout', (e) => {
        if (e.target.closest('[data-tooltip]')) {
            tooltipEl.classList.remove('visible');
        }
    });

    // Initialize on load
    document.addEventListener('DOMContentLoaded', initTooltips);

    // Watch for dynamic DOM changes
    const tooltipObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length) initTooltips();
        });
    });
    tooltipObserver.observe(document.body, { childList: true, subtree: true });
    // Toast Notification System
    window.showToast = function(title, message, type = 'success', duration = 5000) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        const icons = {
            success: 'check-circle',
            error: 'alert-circle',
            warning: 'alert-triangle',
            info: 'info'
        };

        toast.innerHTML = `
            <div class="toast-icon">
                <i data-lucide="${icons[type] || 'info'}"></i>
            </div>
            <div class="toast-content">
                <span class="toast-title">${title}</span>
                <p class="toast-message">${message}</p>
            </div>
            <button class="toast-close">
                <i data-lucide="x" style="width: 14px;"></i>
            </button>
            <div class="toast-progress">
                <div class="toast-progress-bar" style="animation-duration: ${duration}ms; color: ${type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#f59e0b')}"></div>
            </div>
        `;

        container.appendChild(toast);
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);

        // Auto remove
        const timeout = setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 600);
        }, duration);

        // Close button
        toast.querySelector('.toast-close').onclick = () => {
            clearTimeout(timeout);
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 600);
        };
    };

    document.addEventListener('DOMContentLoaded', () => {
        @if(session('success'))
            showToast('Success', "{!! addslashes(session('success')) !!}", 'success');
        @endif
        @if(session('error'))
            showToast('Error', "{!! addslashes(session('error')) !!}", 'error');
        @endif
        @if(session('warning'))
            showToast('Warning', "{!! addslashes(session('warning')) !!}", 'warning');
        @endif
    });
    </script>
    <div id="toast-container" class="toast-container"></div>

    <style>
        .global-premium-tooltip {
            position: fixed;
            z-index: 99999999;
            background: #0f172a;
            color: #ffffff;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 700;
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s cubic-bezier(0.4, 0, 0.2, 1), transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3), 0 4px 10px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
            transform: translateY(6px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            letter-spacing: 0.02em;
        }

        .global-premium-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: #0f172a;
        }

        .global-premium-tooltip.place-bottom::after {
            top: auto;
            bottom: 100%;
            border-top-color: transparent;
            border-bottom-color: #0f172a;
        }

        .global-premium-tooltip.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        [data-tooltip] {
            /* Removed help cursor as requested */
        }

        /* Premium SweetAlert Button Engine */
        .premium-swal-btn {
            height: 54px !important;
            padding: 0 35px !important;
            border-radius: 18px !important;
            font-weight: 900 !important;
            font-size: 0.9rem !important;
            letter-spacing: 0.02em !important;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 10px !important;
            box-shadow: 0 10px 25px rgba(67, 56, 202, 0.25) !important;
        }
        .premium-swal-btn:hover {
            transform: translateY(-2px) scale(1.02) !important;
            box-shadow: 0 15px 35px rgba(67, 56, 202, 0.35) !important;
        }
        .premium-swal-cancel-btn {
            height: 54px !important;
            padding: 0 30px !important;
            border-radius: 18px !important;
            font-weight: 800 !important;
            font-size: 0.9rem !important;
            color: #64748b !important;
            background: #f1f5f9 !important;
            transition: all 0.3s !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
        }
        .premium-swal-cancel-btn:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
        }

        .glass-monolith-popup {
            border-radius: 35px !important;
            padding: 2.5rem !important;
            box-shadow: 0 40px 100px -20px rgba(0,0,0,0.15) !important;
            border: 1px solid rgba(255,255,255,0.8) !important;
        }
    </style>
    <script>
        // CIA SECURITY ENFORCEMENT: Tab-Scoped Authentication Lock
        // This ensures that closing a tab effectively logs the user out for that tab.
        // Opening a new tab with the same URL will force a re-login.
        (function() {
            const tabLockKey = 'tab_auth_lock_{{ auth()->id() }}';
            const isJustLoggedIn = {{ session('just_logged_in') ? 'true' : 'false' }};
            const logoutUrl = "{{ route('logout') }}";

            if (!sessionStorage.getItem(tabLockKey)) {
                if (isJustLoggedIn) {
                    // Initializing the security lock for this tab
                    sessionStorage.setItem(tabLockKey, 'active');
                } else {
                    // Unauthorized tab entry detected (SessionStorage was wiped or never set)
                    // We must terminate the session to maintain CIA integrity
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = logoutUrl;
                    const token = document.createElement('input');
                    token.type = 'hidden';
                    token.name = '_token';
                    token.value = "{{ csrf_token() }}";
                    form.appendChild(token);
                    document.body.appendChild(form);
                    form.submit();
                }
            }

            const offlineUrl = "{{ route('api.user.offline') }}";
            const csrfToken = "{{ csrf_token() }}";

            const handleExit = () => {
                if (navigator.sendBeacon) {
                    const formData = new FormData();
                    formData.append('_token', csrfToken);
                    navigator.sendBeacon(offlineUrl, formData);
                }
            };

            window.addEventListener('pagehide', handleExit);
            window.addEventListener('beforeunload', handleExit);
        })();

        // Polling for Sidebar Badges Update
        (function() {
            function updateBadge(id, count) {
                const badge = document.getElementById(id);
                if (!badge) return;

                if (count > 0) {
                    if (badge.style.display === 'none' || badge.textContent.trim() !== count.toString()) {
                        badge.style.display = 'inline-block';
                        badge.textContent = count;
                    }
                } else {
                    badge.style.display = 'none';
                    badge.textContent = '0';
                }
            }

            function pollSidebarCounts() {
                fetch("{{ route('api.admin.sidebar-counts') }}", {
                    headers: { 'Accept': 'application/json' }
                })
                .then(response => {
                    const contentType = response.headers.get("content-type");
                    if (response.status === 200 && contentType && contentType.indexOf("application/json") !== -1) {
                        return response.json();
                    }
                    return null;
                })
                .then(data => {
                    if (!data || data.error) return; // Unauthorized or something else
                    updateBadge('sidebar-badge-messages', data.messages);
                    updateBadge('sidebar-badge-password', data.password_requests);
                    updateBadge('sidebar-badge-alerts', data.alerts);
                    updateBadge('sidebar-badge-requisitions', data.pending_requisitions);

                    // Also update the global header message badge if it exists
                    const globalUnreadBadge = document.getElementById('global-unread-badge');
                    if (globalUnreadBadge) {
                        if (data.messages > 0) {
                            globalUnreadBadge.style.display = 'block';
                            globalUnreadBadge.textContent = data.messages;
                        } else {
                            globalUnreadBadge.style.display = 'none';
                        }
                    }
                })
                .catch(err => {});
            }

            // Start polling every 15 seconds
            setInterval(pollSidebarCounts, 15000);

            // Wait a few seconds to do the first poll
            setTimeout(pollSidebarCounts, 3000);
        })();
    </script>
    <script>
        function confirmSelfDeactivation(e) {
            e.preventDefault();
            Swal.fire({
                title: '<span style="font-size: 1.5rem; font-weight: 900; color: #0f172a;">Deactivate Account?</span>',
                html: `
                    <div style="color: #64748b; font-size: 0.95rem; font-weight: 600; line-height: 1.6; margin-bottom: 20px;">
                        You will be immediately logged out and your account will be disabled.<br><br>
                        <span style="color: #ef4444; font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Enter your password to confirm this action</span>
                    </div>
                `,
                icon: 'warning',
                input: 'password',
                inputPlaceholder: '',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off',
                    autocomplete: 'new-password',
                    style: 'border-radius: 14px; border: 2px solid #e2e8f0; font-weight: 800; text-align: center; font-size: 1.1rem; color: #0f172a; padding: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); outline: none;'
                },
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#f1f5f9',
                confirmButtonText: '<span style="font-weight: 800; padding: 6px 16px;">Deactivate</span>',
                cancelButtonText: '<span style="color: #64748b; font-weight: 800; padding: 6px 16px;">Cancel</span>',
                background: '#ffffff',
                backdrop: 'rgba(15, 23, 42, 0.7)',
                padding: '2rem',
                preConfirm: (password) => {
                    if (!password) {
                        Swal.showValidationMessage('Authorization required: Password cannot be empty.');
                    }
                    return password;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('selfDeactivatePassword').value = result.value;
                    document.getElementById('selfDeactivateForm').submit();
                }
            });
        }


    </script>
    @stack('modals')
    @stack('scripts')
</body>
</html>
