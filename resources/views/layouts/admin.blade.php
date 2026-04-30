<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Strategic Registry</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-glow: rgba(79, 70, 229, 0.1);
            --bg-body: #f8fafc;
            --sidebar-bg: #ffffff;
            --text-heading: #0f172a;
            --text-body: #475569;
            --text-muted: #94a3b8;
            --shadow-luxe: 0 10px 40px rgba(0, 0, 0, 0.04), 0 2px 10px rgba(0, 0, 0, 0.02);
            --shadow-sidebar: 20px 0 60px rgba(0, 0, 0, 0.03);
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-body);
            font-family: 'Outfit', sans-serif;
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
            height: 100vh;
            z-index: 1000;
            box-shadow: var(--shadow-sidebar);
            border-right: 1px solid rgba(0,0,0,0.02);
        }

        .sidebar-brand {
            padding: 3rem 2rem;
            display: flex;
            align-items: center;
            gap: 14px;
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
            padding: 3rem 4rem;
        }

        .view-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4rem;
        }

        .title-group h2 {
            font-size: 1.85rem;
            font-weight: 900;
            color: var(--text-heading);
            margin: 0;
            letter-spacing: -0.04em;
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
            background: #ef4444;
            border: 2px solid white;
            border-radius: 50%;
        }

        .pulse-dot { width: 8px; height: 8px; background: #10b981; border-radius: 50%; animation: shadow-pulse 2s infinite; }
        @keyframes shadow-pulse { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i data-lucide="shield-check" style="width: 22px;"></i>
            </div>
            <div class="brand-text">
                <h1>ADMIN CORE</h1>
                <span>Strategic Access</span>
            </div>
        </div>

        <div class="nav-scroller">
            <span class="nav-label">Management</span>
            <ul class="nav-list">
                <li>
                    <a href="{{ route('admin.index') }}" class="nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                        <i data-lucide="users"></i>
                        <span>Personnel Registry</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link">
                        <i data-lucide="activity"></i>
                        <span>System Logs</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link">
                        <i data-lucide="lock"></i>
                        <span>Permissions</span>
                    </a>
                </li>
            </ul>

            <span class="nav-label">Parameters</span>
            <ul class="nav-list">
                <li>
                    <a href="#" class="nav-link">
                        <i data-lucide="settings"></i>
                        <span>Global Settings</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link">
                        <i data-lucide="database"></i>
                        <span>Data Terminal</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-footer">
            <div class="profile-pill">
                <div class="avatar-wrap">
                    <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) }}" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div class="user-meta">
                    <span class="u-name">{{ auth()->user()->name }}</span>
                    <span class="u-role">Overseer</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="exit-btn">
                        <i data-lucide="power" style="width: 14px;"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="main-wrapper">
        <header class="view-header">
            <div class="title-group">
                <div class="title-capsule">
                    <div class="capsule-prefix">
                        <i data-lucide="shield"></i>
                    </div>
                    <h2>@yield('title')</h2>
                    <span class="capsule-tag">ADMIN</span>
                </div>
            </div>
            <div class="header-actions">
                <button class="nav-icon-btn" title="System Notifications">
                    <i data-lucide="bell"></i>
                    <span class="alert-dot"></span>
                </button>
                <div class="live-status">
                    <div class="pulse-dot"></div>
                    <span>SYSTEM ONLINE</span>
                </div>
            </div>
        </header>

        @yield('content')
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
