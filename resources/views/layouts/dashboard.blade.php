<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Content Security Policy: Essential for ApexCharts and Select2 functionality -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' blob: https://cdn.jsdelivr.net https://code.jquery.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data:; connect-src 'self';">
    <link rel="icon" type="image/png" href="{{ asset('img/cropped_circle_image.png') }}">
    <title>NACOC | Advanced Inventory System</title>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard_theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vendor/select2.min.css') }}" />

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('js/vendor/select2.min.js') }}"></script>
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    <script src="{{ asset('js/apexcharts.js') }}"></script>

    <style>
        /* ===== RESPONSIVE OVERRIDES - Preserving original design ===== */
        /* Mobile & Tablet Responsive Fixes - keeps exact same visual design */

        @media (max-width: 1024px) {

            /* Sidebar becomes off-canvas overlay */
            .sidebar {
                position: fixed !important;
                left: -300px !important;
                top: 0 !important;
                height: 100vh !important;
                z-index: 1050 !important;
                transition: left 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1) !important;
                box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.2) !important;
            }

            .sidebar.active {
                left: 0 !important;
            }

            /* Sidebar close button visible on mobile */
            #sidebar-close {
                display: flex !important;
            }

            /* Main wrapper takes full width */
            .main-wrapper {
                width: 100% !important;
                margin-left: 0 !important;
            }

            /* Top nav adjustments */
            #mobile-toggle {
                display: flex !important;
            }

            #sidebar-toggle {
                display: none !important;
            }

            /* Search bar responsive */
            .search-bar {
                width: 100% !important;
                max-width: 300px !important;
            }

            .search-bar input {
                width: 100% !important;
            }

            /* Content padding */
            .content-body {
                padding: 1rem !important;
            }
        }

        @media (max-width: 768px) {

            /* Top nav stacking for very small screens */
            .top-nav {
                flex-wrap: wrap !important;
                gap: 0.75rem !important;
                padding: 0.75rem 1rem !important;
            }

            .top-nav>div:first-child {
                flex: 1 !important;
                min-width: 0 !important;
            }

            .search-bar {
                max-width: 100% !important;
            }

            .top-nav-actions {
                gap: 0.5rem !important;
            }

            .icon-btn {
                width: 36px !important;
                height: 36px !important;
            }

            /* Toast positioning */
            .toast-container {
                right: 10px !important;
                left: 10px !important;
                bottom: 10px !important;
            }

            .toast {
                min-width: auto !important;
                width: calc(100% - 20px) !important;
            }
        }

        @media (min-width: 1025px) {

            /* Desktop normal behavior */
            #sidebar-close {
                display: none !important;
            }

            #mobile-toggle {
                display: none !important;
            }

            .sidebar-overlay {
                display: none !important;
            }
        }

        /* Sidebar overlay styling */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1040;
            display: none;
        }

        .sidebar-overlay.active {
            display: block !important;
        }

        /* Ensure tables scroll horizontally on mobile */
        .content-body {
            overflow-x: auto;
        }

        /* Responsive table container */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Keep original card styles but allow wrapping */
        .dashboard-grid,
        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        /* Preserve original spacing on all devices */
        @media (max-width: 640px) {
            .logo-container h1 {
                font-size: 1.5rem !important;
            }

            .user-name {
                font-size: 0.85rem !important;
            }
        }
    </style>

    <script>
        // Pre-initialization to prevent flash
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>

<body>
    <div class="toast-container" id="toast-container"></div>
    <div id="sidebar-overlay" class="sidebar-overlay"></div>

    <div class="sidebar">
        <button id="sidebar-close" style="position: absolute; top: 1.5rem; right: 1.5rem; background: var(--bg-main); border: none; width: 36px; height: 36px; border-radius: 10px; display: none; align-items: center; justify-content: center; cursor: pointer; z-index: 1060;">
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
        </ul>

        <div class="nav-section-title">Operations</div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="#" class="nav-link" data-tooltip="Analytical Reports">
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
                    <button type="submit" style="background: transparent; border: none; cursor: pointer; padding: 0.5rem; border-radius: 8px; transition: var(--transition); display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="log-out" style="width: 18px; color: #94a3b8;"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="main-wrapper">
        <nav class="top-nav">
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <button id="mobile-toggle" style="display: none; background: var(--bg-main); border: none; width: 44px; height: 44px; border-radius: 12px; align-items: center; justify-content: center; cursor: pointer;">
                    <i data-lucide="menu" style="width: 22px; color: var(--text-main);"></i>
                </button>
                <button id="sidebar-toggle" style="background: var(--bg-main); border: none; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: var(--transition);">
                    <i data-lucide="panel-left-close" style="width: 20px; color: var(--text-main);"></i>
                </button>
                <div class="search-bar" style="position: relative; flex: 1; min-width: 200px;">
                    <i data-lucide="search" style="color: var(--text-muted); width: 18px; position: absolute; left: 12px; top: 50%; transform: translateY(-50%);"></i>
                    <input type="text" id="global-search-input" placeholder="Search inventory, reports, transactions..." autocomplete="off" style="padding-left: 38px; width: 100%;">

                    <!-- Search Results Portal -->
                    <div id="global-search-results" style="display: none; position: absolute; top: calc(100% + 10px); left: 0; right: 0; background: var(--bg-card); border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); border: 1px solid var(--border-color); z-index: 2000; overflow: hidden; max-height: 400px; overflow-y: auto;">
                    </div>
                </div>
            </div>

            <div class="top-nav-actions" style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="icon-btn">
                    <i data-lucide="message-square" style="width: 20px;"></i>
                </div>
                <div style="height: 32px; width: 1px; background: var(--border-color);"></div>
                <div class="icon-btn" style="position: relative;">
                    <i data-lucide="bell" style="width: 20px;"></i>
                    <span class="badge">3</span>
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

            // Function to check if mobile view
            function isMobileView() {
                return window.innerWidth <= 1024;
            }

            // Apply saved sidebar state for desktop only
            const isSidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';

            function applyDesktopState() {
                if (!isMobileView()) {
                    if (isSidebarCollapsed) {
                        sidebar.classList.add('collapsed');
                        mainWrapper.classList.add('expanded');
                        if (sidebarToggle) sidebarToggle.innerHTML = '<i data-lucide="panel-left-open" style="width: 20px; color: var(--text-main);"></i>';
                    } else {
                        sidebar.classList.remove('collapsed');
                        mainWrapper.classList.remove('expanded');
                        if (sidebarToggle) sidebarToggle.innerHTML = '<i data-lucide="panel-left-close" style="width: 20px; color: var(--text-main);"></i>';
                    }
                    lucide.createIcons();
                }
            }

            // Toggle sidebar for mobile (show/hide)
            function toggleMobileSidebar(show) {
                if (show) {
                    sidebar.classList.add('active');
                    overlay.classList.add('active');
                } else {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                }
            }

            // Handle responsive behavior
            function handleResponsive() {
                if (isMobileView()) {
                    // Mobile mode: hide desktop toggle, ensure sidebar is not collapsed visually
                    sidebar.classList.remove('collapsed');
                    mainWrapper.classList.remove('expanded');
                    // Close mobile sidebar by default on resize if it was open? Better to keep state
                    // But ensure overlay and sidebar state consistent
                    if (!sidebar.classList.contains('active')) {
                        overlay.classList.remove('active');
                    }
                } else {
                    // Desktop mode: close mobile overlay if open
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    applyDesktopState();
                }
            }

            // Desktop sidebar collapse toggle
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    if (!isMobileView()) {
                        const isNowCollapsed = sidebar.classList.toggle('collapsed');
                        mainWrapper.classList.toggle('expanded');
                        localStorage.setItem('sidebar-collapsed', isNowCollapsed);

                        // Update Icon
                        sidebarToggle.innerHTML = isNowCollapsed ?
                            '<i data-lucide="panel-left-open" style="width: 20px; color: var(--text-main);"></i>' :
                            '<i data-lucide="panel-left-close" style="width: 20px; color: var(--text-main);"></i>';
                        lucide.createIcons();
                    }
                });
            }

            // Mobile menu toggle
            if (mobileToggle) {
                mobileToggle.addEventListener('click', () => toggleMobileSidebar(true));
            }

            // Sidebar close button (mobile)
            if (sidebarClose) {
                sidebarClose.addEventListener('click', () => toggleMobileSidebar(false));
            }

            // Overlay click to close
            if (overlay) {
                overlay.addEventListener('click', () => toggleMobileSidebar(false));
            }

            // Handle window resize
            window.addEventListener('resize', () => {
                handleResponsive();
            });

            // Initial responsive setup
            handleResponsive();

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
    </script>
    @stack('scripts')
</body>

</html>