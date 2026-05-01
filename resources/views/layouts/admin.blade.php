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
                    <a href="{{ route('admin.logs') }}" class="nav-link {{ request()->routeIs('admin.logs') ? 'active' : '' }}">
                        <i data-lucide="shield-check"></i>
                        <span>System Logs</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.index') ? 'active' : '' }}">
                        <i data-lucide="bell"></i>
                        <span>System Alerts</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.permissions') }}" class="nav-link {{ request()->routeIs('admin.permissions') ? 'active' : '' }}">
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
                <div style="position: relative;" id="admin-notification-wrapper">
                    <button class="nav-icon-btn" id="admin-notification-btn" title="System Notifications">
                        <i data-lucide="bell"></i>
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
                                <i data-lucide="bell-off" style="width: 36px; height: 36px; margin-bottom: 1rem; opacity: 0.3;"></i>
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
        lucide.createIcons();

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
            fetch("{{ route('api.notifications') }}")
                .then(res => res.json())
                .then(data => {
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
                .catch(err => console.error('Admin Notification Sync Error:', err));
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

            fetch("{{ route('api.notifications.dismiss') }}", {
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
    // Global Premium Tooltip Engine
    const initTooltips = () => {
        document.querySelectorAll('[title]').forEach(el => {
            const title = el.getAttribute('title');
            if (title && !el.hasAttribute('data-tooltip')) {
                el.setAttribute('data-tooltip', title);
                el.removeAttribute('title');
            }
        });
    };

    // Initialize on load
    document.addEventListener('DOMContentLoaded', initTooltips);
    
    // Watch for dynamic DOM changes (AJAX results, Modals, etc)
    const tooltipObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length) initTooltips();
        });
    });
    tooltipObserver.observe(document.body, { childList: true, subtree: true });
    </script>
    @stack('scripts')
</body>
</html>
