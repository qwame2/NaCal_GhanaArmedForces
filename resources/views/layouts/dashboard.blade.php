<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Content Security Policy: Essential for ApexCharts and Select2 functionality -->
    <style>
        :root {
            --system-zoom: 1;
        }
        
        body {
            zoom: var(--system-zoom);
            min-height: 100vh;
            background-attachment: fixed;
            background-size: cover;
        }

        /* Responsive Fix for Zoom-out: Allow content to expand */
        .content-body > div:first-child {
            max-width: none !important;
        }

        @media (max-width: 576px) {
            .zoom-controls {
                display: none !important;
            }
        }
    </style>
    <title>NACOC | Advanced Inventory System</title>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard_theme.css') }}?v={{ filemtime(public_path('css/dashboard_theme.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/vendor/select2.min.css') }}" />

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('js/vendor/select2.min.js') }}"></script>
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    <script src="{{ asset('js/apexcharts.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Pre-initialization to prevent flash
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>

<body>
    <div class="toast-container" id="toast-container"></div>
    <div id="sidebar-overlay" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 950; display: none; backdrop-filter: blur(4px);"></div>

    <div class="sidebar">
        <button id="sidebar-close" style="position: absolute; top: 1.5rem; right: 1.5rem; background: var(--bg-main); border: none; width: 36px; height: 36px; border-radius: 10px; display: none; align-items: center; justify-content: center; cursor: pointer;">
            <i data-lucide="x" style="width: 20px; color: var(--text-main);"></i>
        </button>
        <div class="logo-container">
            <div class="logo-icon" style="background: transparent; box-shadow: none; perspective: 1000px;">
                <img src="{{ asset('img/NACOC1.png') }}"
                    alt="NACOC Logo"
                    class="logo-3d"
                    style="
                 width: 48px;
                 height: 48px;
                 object-fit: contain;
                 filter: drop-shadow(5px 10px 15px rgba(0,0,0,0.2));
                 transform: rotateY(-15deg) rotateX(5deg) translateZ(10px);
                 transition: transform 0.4s ease;
                 transform-style: preserve-3d;
             "
                    onmouseover="this.style.transform='rotateY(0deg) rotateX(0deg) translateZ(20px) scale(1.1)'"
                    onmouseout="this.style.transform='rotateY(-15deg) rotateX(5deg) translateZ(10px)'">
            </div>
            <div>
                <h1 style="font-size: 1.75rem; font-weight: 900; letter-spacing: -0.04em; line-height: 1;">NACOC</h1>
                <div class="sidebar-branding-subtitle" style="font-size: 0.6rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; opacity: 0.8;">Inventory Management System</div>
            </div>
        </div>

        <div class="nav-section-title">Main Menu</div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') || request()->is('/') ? 'active' : '' }}" data-tooltip="Dashboard View">
                    <i data-lucide="layout-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('receiveditems') }}" class="nav-link {{ request()->routeIs('receiveditems') ? 'active' : '' }}" data-tooltip="Received Items Log">
                    <i data-lucide="package-plus"></i>
                    <span>Received Items</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('issueitems') }}" class="nav-link {{ request()->routeIs('issueitems') ? 'active' : '' }}" data-tooltip="Issue Items Out">
                    <i data-lucide="package-minus"></i>
                    <span>Issue Items</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('returns.index') }}" class="nav-link {{ request()->routeIs('returns.index') ? 'active' : '' }}" data-tooltip="Process Returns">
                    <i data-lucide="refresh-cw"></i>
                    <span>Returns</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('stockcheck.index') }}" class="nav-link {{ request()->routeIs('stockcheck.index') ? 'active' : '' }}" data-tooltip="Verify Category & Physical Stock">
                    <i data-lucide="clipboard-check"></i>
                    <span>Stock Check</span>
                </a>
            </li>
        </ul>

        <div class="nav-section-title">Operations</div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}" data-tooltip="Analytical Reports">
                    <i data-lucide="file-bar-chart"></i>
                    <span>Reports</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.index') ? 'active' : '' }}" data-tooltip="Manage your profile & security">
                    <i data-lucide="user-cog"></i>
                    <span>User Settings</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.index') ? 'active' : '' }}">
                    <i data-lucide="bell"></i>
                    <span>Notifications</span>
                </a>
            </li>

        </ul>

        <div class="sidebar-footer" style="margin-top: auto;">
            <div class="user-profile-card">
                @if(auth()->user()->avatar)
                <img src="{{ Storage::url(auth()->user()->avatar) }}" style="width: 44px; height: 44px; border-radius: 12px; object-fit: cover; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 2px solid white;">
                @else
                <div style="width: 44px; height: 44px; background: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; color: white; box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', auth()->user()->name)[1] ?? '', 0, 1)) }}
                </div>
                @endif
                <div style="overflow: hidden; flex: 1;">
                    <div class="user-name" style="font-size: 0.95rem; font-weight: 700; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: var(--transition);">{{ auth()->user()->name }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">@ {{ auth()->user()->username }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: transparent; border: none; cursor: pointer; padding: 0.5rem; border-radius: 8px; transition: var(--transition); display: flex; align-items: center; justify-content: center;" title="Sign Out">
                        <i data-lucide="log-out" style="width: 18px; color: #94a3b8;"></i>
                    </button>
                </form>

            </div>
        </div>
    </div>

    <div class="main-wrapper">
        <nav class="top-nav">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button id="mobile-toggle" style="display: none; background: var(--bg-main); border: none; width: 44px; height: 44px; border-radius: 12px; align-items: center; justify-content: center; cursor: pointer;">
                    <i data-lucide="menu" style="width: 22px; color: var(--text-main);"></i>
                </button>
                <button id="sidebar-toggle" style="background: var(--bg-main); border: none; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: var(--transition);">
                    <i data-lucide="panel-left-close" style="width: 20px; color: var(--text-main);"></i>
                </button>
                <div class="search-bar" style="position: relative;">
                    <i data-lucide="search" style="color: var(--text-muted); width: 18px;"></i>
                    <input type="text" id="global-search-input" placeholder="Search inventory, reports, transactions..." autocomplete="off">

                    <!-- Search Results Portal -->
                    <div id="global-search-results" style="display: none; position: absolute; top: calc(100% + 10px); left: 0; right: 0; background: var(--bg-card); border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); border: 1px solid var(--border-color); z-index: 2000; overflow: hidden; max-height: 400px; overflow-y: auto;">
                    </div>
                </div>
            </div>

            <div class="top-nav-actions">
                <!-- Zoom Controls -->
                <div class="zoom-controls" style="display: flex; align-items: center; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 12px; padding: 2px; margin-right: 0.5rem; transition: var(--transition);">
                    <button id="zoom-out-btn" class="icon-btn" style="width: 32px; height: 32px; border: none; background: transparent;" title="Zoom Out">
                        <i data-lucide="minus" style="width: 14px;"></i>
                    </button>
                    <span id="zoom-display" style="font-size: 0.7rem; font-weight: 800; min-width: 38px; text-align: center; color: var(--text-muted); cursor: pointer;" title="Reset Zoom">100%</span>
                    <button id="zoom-in-btn" class="icon-btn" style="width: 32px; height: 32px; border: none; background: transparent;" title="Zoom In">
                        <i data-lucide="plus" style="width: 14px;"></i>
                    </button>
                </div>

                <a href="{{ route('messages.index') }}" class="icon-btn" title="Secure Communication Hub" style="position: relative;">
                    <i data-lucide="message-square" style="width: 20px;"></i>
                    <span id="global-unread-badge" style="display: none; position: absolute; top: -5px; right: -5px; background: var(--primary); color: white; font-size: 0.65rem; font-weight: 800; padding: 2px 6px; border-radius: 99px; border: 2px solid var(--bg-main); transition: var(--transition);">0</span>
                </a>
                <div style="height: 32px; width: 1px; background: var(--border-color);"></div>
                <div class="icon-btn" id="notification-btn" style="position: relative; cursor: pointer; transition: var(--transition);">
                    <i data-lucide="bell" style="width: 20px;"></i>
                    @if($globalNotificationCount > 0)
                    <span class="badge" style="position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; font-size: 0.65rem; font-weight: 800; padding: 2px 6px; border-radius: 99px; border: 2px solid var(--bg-main); transition: var(--transition);">{{ $globalNotificationCount }}</span>
                    @endif

                    <!-- Notification Dropdown -->
                    <div id="notification-dropdown" style="display: none; position: absolute; top: calc(100% + 15px); right: 0; width: 320px; z-index: 2100; padding: 0; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.15); border: 1px solid #edf2f7; background: white; overflow: hidden; animation: popIn 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);">
                        <div style="padding: 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: rgba(99, 102, 241, 0.03);">
                            <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--text-main); margin: 0;">Notifications</h3>
                            @if($globalNotificationCount > 0)
                            <span style="font-size: 0.7rem; background: var(--primary); color: white; padding: 0.2rem 0.6rem; border-radius: 99px; font-weight: 700;">{{ $globalNotificationCount }} New</span>
                            @endif
                        </div>
                        <div style="max-height: 380px; overflow-y: auto;" class="no-scrollbar">
                            @forelse($globalNotifications as $notif)
                            <a href="{{ route($notif['route']) }}" style="display: flex; gap: 1rem; padding: 1.25rem; text-decoration: none; border-bottom: 1px solid var(--border-color); transition: all 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.02)'" onmouseout="this.style.background='transparent'">
                                <div style="width: 40px; height: 40px; border-radius: 12px; background: {{ $notif['type'] === 'warning' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; color: {{ $notif['type'] === 'warning' ? '#f59e0b' : '#ef4444' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i data-lucide="{{ $notif['icon'] }}" style="width: 20px;"></i>
                                </div>
                                <div style="flex: 1; text-align: left;">
                                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.85rem; margin-bottom: 0.25rem;">{{ $notif['title'] }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.4;">{{ $notif['message'] }}</div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 0.5rem; font-weight: 600;">Just now</div>
                                </div>
                            </a>
                            @empty
                            <div style="padding: 3rem 1.5rem; text-align: center; color: var(--text-muted);">
                                <i data-lucide="bell-off" style="width: 32px; height: 32px; margin-bottom: 1rem; opacity: 0.3;"></i>
                                <p style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">All caught up!</p>
                                <p style="font-size: 0.75rem;">No new notifications at this time.</p>
                            </div>
                            @endforelse
                        </div>
                        <div style="padding: 1rem; text-align: center; background: rgba(0,0,0,0.01); border-top: 1px solid var(--border-color);">
                            <a href="{{ route('notifications.index') }}" style="font-size: 0.75rem; font-weight: 800; color: var(--primary); text-decoration: none;">View All Notifications</a>
                        </div>
                    </div>
                </div>
                <div class="icon-btn" id="theme-toggle" style="border: none; background: transparent;">
                    <i data-lucide="moon" style="width: 20px;"></i>
                </div>
            </div>
        </nav>

        <div class="content-body">
            @yield('content')
        </div>
    </div>

    <script>
        // Global Zoom Logic
        window.currentZoom = parseFloat(localStorage.getItem('system-zoom')) || 1;

        window.applyZoom = function() {
            // Apply zoom using CSS variable and direct style for compatibility
            document.documentElement.style.setProperty('--system-zoom', window.currentZoom);
            document.body.style.zoom = window.currentZoom;
            
            // For Firefox fallback
            if (navigator.userAgent.indexOf('Firefox') !== -1) {
                document.body.style.transform = `scale(${window.currentZoom})`;
                document.body.style.transformOrigin = 'top center';
                document.body.style.width = `${100 / window.currentZoom}%`;
                document.body.style.minHeight = `${100 / window.currentZoom}vh`;
            }

            const display = document.getElementById('zoom-display');
            if (display) display.innerText = Math.round(window.currentZoom * 100) + '%';
            
            // Update settings UI if present
            if (typeof updateSettingsZoomUI === 'function') updateSettingsZoomUI();
            
            localStorage.setItem('system-zoom', window.currentZoom);
        }

        document.getElementById('zoom-in-btn')?.addEventListener('click', () => {
            window.currentZoom = Math.min(window.currentZoom + 0.1, 2);
            window.applyZoom();
        });

        document.getElementById('zoom-out-btn')?.addEventListener('click', () => {
            window.currentZoom = Math.max(window.currentZoom - 0.1, 0.5);
            window.applyZoom();
        });

        document.getElementById('zoom-display')?.addEventListener('click', () => {
            window.currentZoom = 1;
            window.applyZoom();
        });

        // Theme Toggle Logic
        function updateThemeIcon(theme) {
            const themeToggle = document.getElementById('theme-toggle');
            if (!themeToggle) return;

            // Lucide replaces the <i> or <svg> so we just empty and add a new <i>
            themeToggle.innerHTML = theme === 'dark' ?
                '<i data-lucide="sun" style="width: 20px;"></i>' :
                '<i data-lucide="moon" style="width: 20px;"></i>';

            lucide.createIcons();
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            window.applyZoom();
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            updateThemeIcon(currentTheme);

            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const currentTheme = document.documentElement.getAttribute('data-theme');
                    const newTheme = currentTheme === 'light' ? 'dark' : 'light';

                    document.documentElement.setAttribute('data-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    updateThemeIcon(newTheme);

                    // Dispatch event for other listeners (like charts)
                    window.dispatchEvent(new CustomEvent('themeChanged', {
                        detail: {
                            theme: newTheme
                        }
                    }));
                });
            }

            const mobileToggle = document.getElementById('mobile-toggle');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebarClose = document.getElementById('sidebar-close');
            const sidebar = document.querySelector('.sidebar');
            const mainWrapper = document.querySelector('.main-wrapper');
            const overlay = document.getElementById('sidebar-overlay');

            // Apply saved sidebar state
            const isSidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            const isMobile = window.innerWidth <= 1024;

            if (isSidebarCollapsed && !isMobile) {
                sidebar.classList.add('collapsed');
                mainWrapper.classList.add('expanded');
                if (sidebarToggle) sidebarToggle.innerHTML = '<i data-lucide="panel-left-open" style="width: 20px; color: var(--text-main);"></i>';
                lucide.createIcons();
            }

            // Mobile/Tablet Default: Keep hidden on load to avoid layout shifts
            if (isMobile) {
                toggleSidebar(false);
            }

            function toggleSidebar(show) {
                if (show) {
                    sidebar.classList.add('active');
                    overlay.style.display = 'block';
                    sidebarClose.style.display = 'flex';
                } else {
                    sidebar.classList.remove('active');
                    overlay.style.display = 'none';
                }
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    const isNowCollapsed = sidebar.classList.toggle('collapsed');
                    mainWrapper.classList.toggle('expanded');
                    localStorage.setItem('sidebar-collapsed', isNowCollapsed);

                    // Update Icon
                    sidebarToggle.innerHTML = isNowCollapsed ?
                        '<i data-lucide="panel-left-open" style="width: 20px; color: var(--text-main);"></i>' :
                        '<i data-lucide="panel-left-close" style="width: 20px; color: var(--text-main);"></i>';
                    lucide.createIcons();
                });
            }

            if (mobileToggle) mobileToggle.addEventListener('click', () => toggleSidebar(true));
            if (sidebarClose) sidebarClose.addEventListener('click', () => toggleSidebar(false));
            if (overlay) overlay.addEventListener('click', () => toggleSidebar(false));

            // Global Real-time Search Logic with Results Dropdown
            const globalSearchInput = document.getElementById('global-search-input');
            const searchResults = document.getElementById('global-search-results');
            let globalSearchTimeout;

            if (globalSearchInput && searchResults) {
                globalSearchInput.addEventListener('input', (e) => {
                    const query = e.target.value.trim();

                    if (query.length < 2) {
                        searchResults.style.display = 'none';
                        return;
                    }

                    clearTimeout(globalSearchTimeout);
                    globalSearchTimeout = setTimeout(() => {
                        // Show searching state
                        searchResults.innerHTML = '<div style="padding: 1rem; text-align: center; color: var(--text-muted);"><div class="loader" style="width: 20px; height: 20px; border-width: 2px; margin: 0 auto 0.5rem auto;"></div>Searching...</div>';
                        searchResults.style.display = 'block';

                        // Fetch real results from backend using relative route helper
                        fetch("{{ route('api.search') }}?q=" + encodeURIComponent(query))
                            .then(res => {
                                if (!res.ok) throw new Error('Network response was not ok');
                                return res.json();
                            })
                            .then(data => {
                                if (data.length === 0) {
                                    searchResults.innerHTML = `
                                        <div style="padding: 2rem; text-align: center; color: var(--text-muted);">
                                            <i data-lucide="search-x" style="width: 24px; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                            <p style="font-size: 0.85rem; font-weight: 600;">No matches found for "${query}"</p>
                                        </div>
                                    `;
                                } else {
                                    let html = '';
                                    data.forEach(item => {
                                        html += `
                                            <a href="${item.url}" style="padding: 1rem 1.25rem; display: flex; align-items: center; gap: 1rem; text-decoration: none; border-bottom: 1px solid var(--border-color); transition: background 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.03)'" onmouseout="this.style.background='transparent'">
                                                <div style="width: 36px; height: 36px; background: rgba(99, 102, 241, 0.1); color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    <i data-lucide="${item.icon || 'arrow-right'}" style="width: 18px;"></i>
                                                </div>
                                                <div style="flex: 1; overflow: hidden;">
                                                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.title}</div>
                                                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">${item.subtitle}</div>
                                                </div>
                                                <i data-lucide="chevron-right" style="width: 14px; color: var(--text-muted); opacity: 0.5;"></i>
                                            </a>
                                        `;
                                    });
                                    searchResults.innerHTML = html;
                                }
                                searchResults.style.display = 'block';
                                if (typeof lucide !== 'undefined') lucide.createIcons();
                            })
                            .catch(error => {
                                console.error('Global search error:', error);
                                searchResults.innerHTML = '<div style="padding: 1.5rem; text-align: center; color: #ef4444; font-size: 0.85rem;">Failed to fetch results. Check connection.</div>';
                            });

                        // Local Sync (for inventory pages)
                        const localSearchInput = document.getElementById('searchInput');
                        if (localSearchInput) {
                            localSearchInput.value = query;
                            if (typeof performSearch === 'function') performSearch();
                        }
                    }, 300);
                });

                // Hide results when clicking outside
                document.addEventListener('click', (e) => {
                    if (!globalSearchInput.contains(e.target) && !searchResults.contains(e.target)) {
                        searchResults.style.display = 'none';
                    }
                });

                // Show results on focus if query is present
                globalSearchInput.addEventListener('focus', () => {
                    if (globalSearchInput.value.length >= 2) {
                        searchResults.style.display = 'block';
                    }
                });
            }

            // Notification Dropdown Toggle
            const notificationBtn = document.getElementById('notification-btn');
            const notificationDropdown = document.getElementById('notification-dropdown');

            if (notificationBtn && notificationDropdown) {
                notificationBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isVisible = notificationDropdown.style.display === 'block';
                    
                    // Close other dropdowns if any
                    if (typeof searchResults !== 'undefined') searchResults.style.display = 'none';
                    
                    notificationDropdown.style.display = isVisible ? 'none' : 'block';
                    
                    if (!isVisible) {
                        notificationBtn.style.background = 'var(--bg-card)';
                        notificationBtn.style.transform = 'scale(0.95)';
                    } else {
                        notificationBtn.style.background = 'transparent';
                        notificationBtn.style.transform = 'scale(1)';
                    }
                });

                document.addEventListener('click', (e) => {
                    if (!notificationBtn.contains(e.target)) {
                        notificationDropdown.style.display = 'none';
                        notificationBtn.style.background = 'transparent';
                        notificationBtn.style.transform = 'scale(1)';
                    }
                });
            }

            // Real-time Notification Refresh Logic
            window.refreshNotifications = function() {
                fetch("{{ route('api.notifications') }}")
                    .then(res => res.json())
                    .then(data => {
                        // Update Navbar Bell Badge
                        const btn = document.getElementById('notification-btn');
                        if (btn) {
                            let bellBadge = btn.querySelector('.badge');
                            if (data.count > 0) {
                                if (!bellBadge) {
                                    bellBadge = document.createElement('span');
                                    bellBadge.className = 'badge';
                                    bellBadge.style.cssText = 'position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; font-size: 0.65rem; font-weight: 800; padding: 2px 6px; border-radius: 99px; border: 2px solid var(--bg-main); transition: var(--transition);';
                                    btn.appendChild(bellBadge);
                                }
                                bellBadge.innerText = data.count;
                                bellBadge.style.display = 'block';
                            } else if (bellBadge) {
                                bellBadge.style.display = 'none';
                            }
                        }

                        // Update Dropdown Content if it exists
                        const dropdown = document.getElementById('notification-dropdown');
                        if (dropdown) {
                            const list = dropdown.querySelector('.no-scrollbar');
                            if (list) {
                                if (data.notifications.length === 0) {
                                    list.innerHTML = `
                                        <div style="padding: 3rem 1.5rem; text-align: center; color: var(--text-muted);">
                                            <i data-lucide="bell-off" style="width: 32px; height: 32px; margin-bottom: 1rem; opacity: 0.3;"></i>
                                            <p style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">All caught up!</p>
                                            <p style="font-size: 0.75rem;">No new notifications at this time.</p>
                                        </div>
                                    `;
                                } else {
                                    let html = '';
                                    data.notifications.forEach(notif => {
                                         const routeUrl = notif.route === 'admin.index' ? "{{ route('admin.index') }}" : "{{ route('dashboard') }}";
                                         const cleanDesc = notif.title.includes(': ') ? notif.title.split(': ')[1] : notif.title;
                                         html += `
                                             <div style="position: relative; border-bottom: 1px solid var(--border-color);">
                                                 <a href="${routeUrl}" style="display: flex; gap: 1rem; padding: 1.25rem; padding-right: 3rem; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.02)'" onmouseout="this.style.background='transparent'">
                                                     <div style="width: 40px; height: 40px; border-radius: 12px; background: ${notif.type === 'warning' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(239, 68, 68, 0.1)'}; color: ${notif.type === 'warning' ? '#f59e0b' : '#ef4444'}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                         <i data-lucide="${notif.icon}" style="width: 20px;"></i>
                                                     </div>
                                                     <div style="flex: 1; text-align: left;">
                                                         <div style="font-weight: 700; color: var(--text-main); font-size: 0.85rem; margin-bottom: 0.25rem;">${notif.title}</div>
                                                         <div style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.4;">${notif.message}</div>
                                                     </div>
                                                 </a>
                                                 <button onclick="event.stopPropagation(); window.dismissNotification('${cleanDesc}')" style="position: absolute; top: 1.25rem; right: 1rem; background: transparent; border: none; color: var(--text-muted); cursor: pointer; padding: 4px; border-radius: 6px; transition: var(--transition);" onmouseover="this.style.background='rgba(239, 68, 68, 0.1)'; this.style.color='#ef4444'" onmouseout="this.style.background='transparent'; this.style.color='var(--text-muted)'">
                                                     <i data-lucide="x" style="width: 14px; height: 14px;"></i>
                                                 </button>
                                             </div>
                                         `;
                                    });
                                    list.innerHTML = html;
                                }
                                if (typeof lucide !== 'undefined') lucide.createIcons();
                            }
                            
                            const headerBadge = dropdown.querySelector('span[style*="background: var(--primary)"]');
                            if (headerBadge) {
                                headerBadge.innerText = data.count + ' New';
                                headerBadge.style.display = data.count > 0 ? 'block' : 'none';
                            }
                        }

                        // Sync to notifications page if active
                        window.dispatchEvent(new CustomEvent('notificationsSynced', { detail: data }));
                    })
                    .catch(err => console.error('Notification Sync Error:', err));
            };

            // Start polling (every 30 seconds)
            setInterval(window.refreshNotifications, 30000);

            window.dismissNotification = function(description) {
                // Optimistic UI Update: Instantly remove/hide elements containing this description
                const allItems = document.querySelectorAll('.notification-item, .notif-item, [style*="position: relative; border-bottom: 1px solid var(--border-color)"]');
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

            // Global Message Badge Sync
            window.refreshUnreadMessages = function() {
                const isMessagePage = window.location.href.includes('/messages');
                if (isMessagePage) {
                    const badge = document.getElementById('global-unread-badge');
                    if (badge) badge.style.display = 'none';
                    return;
                }

                fetch("{{ route('api.total-unread') }}")
                    .then(res => res.json())
                    .then(data => {
                        const badge = document.getElementById('global-unread-badge');
                        if (badge) {
                            if (data.count > 0) {
                                badge.textContent = data.count;
                                badge.style.display = 'block';
                            } else {
                                badge.style.display = 'none';
                            }
                        }
                    });
            };

            setInterval(window.refreshUnreadMessages, 10000);
            window.refreshUnreadMessages();
        });

        // Toast Notification System Logic
        function showToast(title, message, type = 'success', duration = 5000) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;

            const icons = {
                success: 'check-circle',
                error: 'alert-circle',
                info: 'info'
            };

            toast.innerHTML = `
                <div class="toast-icon">
                    <i data-lucide="${icons[type]}"></i>
                </div>
                <div class="toast-content">
                    <span class="toast-title">${title}</span>
                    <p class="toast-message">${message}</p>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">
                    <i data-lucide="x"></i>
                </button>
                <div class="toast-progress">
                    <div class="toast-progress-bar" style="animation-duration: ${duration}ms"></div>
                </div>
            `;

            container.appendChild(toast);
            lucide.createIcons();

            // Trigger animation
            setTimeout(() => toast.classList.add('show'), 10);

            // Auto remove
            const timeout = setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 600);
            }, duration);

            // Close button override
            toast.querySelector('.toast-close').onclick = () => {
                clearTimeout(timeout);
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 600);
            };
        }

        @if(session('success'))
        document.addEventListener('DOMContentLoaded', () => {
            showToast('Success', "{{ session('success') }}", 'success');
        });
        @endif

        @if(session('error'))
        document.addEventListener('DOMContentLoaded', () => {
            showToast('Action Failed', "{{ session('error') }}", 'error');
        });
        @endif
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
        
        // Watch for dynamic DOM changes (AJAX results, Modals, etc)
        const tooltipObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length) initTooltips();
            });
        });
        tooltipObserver.observe(document.body, { childList: true, subtree: true });
    </script>
    
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
    </style>
    @stack('scripts')
</body>

</html>