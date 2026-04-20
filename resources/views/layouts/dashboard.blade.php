<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NACOC | Advanced Inventory Dashboard</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">

    <!-- Base CSS (comprehensive responsive structure) -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #818cf8;
            --bg-body: #f8fafc;
            --bg-main: #ffffff;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #475569;
            --border-color: #e2e8f0;
            --sidebar-bg: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --transition: all 0.25s ease;
            --radius-card: 20px;
            --radius-element: 14px;
        }

        [data-theme="dark"] {
            --bg-body: #0f172a;
            --bg-main: #1e293b;
            --bg-card: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --sidebar-bg: #0f172a;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.3);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.4);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            line-height: 1.5;
            transition: background 0.2s, color 0.2s;
            overflow-x: hidden;
        }

        /* === RESPONSIVE LAYOUT SYSTEM === */
        .dashboard-layout {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* SIDEBAR - DESKTOP COLLAPSIBLE + MOBILE OVERLAY */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            transition: var(--transition);
            z-index: 100;
            flex-shrink: 0;
            padding: 1.5rem 1rem;
        }

        .sidebar.collapsed {
            width: 88px;
        }

        .sidebar.collapsed .logo-container h1,
        .sidebar.collapsed .sidebar-branding-subtitle,
        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .nav-section-title,
        .sidebar.collapsed .user-name,
        .sidebar.collapsed .user-profile-card form button~div,
        .sidebar.collapsed .user-profile-card div:last-child:not(button) {
            display: none;
        }

        .sidebar.collapsed .logo-container {
            justify-content: center;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 0.75rem;
        }

        .sidebar.collapsed .user-profile-card {
            justify-content: center;
            padding: 0.5rem;
        }

        /* Mobile sidebar (off-canvas) */
        @media (max-width: 1024px) {
            .sidebar {
                position: fixed;
                left: -300px;
                top: 0;
                height: 100vh;
                z-index: 1050;
                transition: left 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
                box-shadow: var(--shadow-lg);
            }

            .sidebar.active {
                left: 0;
            }

            .sidebar.collapsed {
                width: 280px;
                /* full width on mobile when active */
            }

            .sidebar.collapsed .nav-link span,
            .sidebar.collapsed .user-name {
                display: inline-block;
                /* restore on mobile */
            }

            .sidebar.collapsed .logo-container h1 {
                display: block;
            }
        }

        /* MAIN CONTENT AREA */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            width: 100%;
            transition: var(--transition);
        }

        /* TOP NAVBAR - RESPONSIVE */
        .top-navbar {
            background: var(--bg-main);
            border-bottom: 1px solid var(--border-color);
            padding: 0.875rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 90;
            backdrop-filter: blur(8px);
            background: rgba(var(--bg-main-rgb, 255, 255, 255), 0.85);
        }

        [data-theme="dark"] .top-navbar {
            background: rgba(30, 41, 59, 0.9);
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .icon-btn {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .icon-btn:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #ef4444;
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            border-radius: 30px;
            padding: 2px 6px;
            line-height: 1;
        }

        .search-wrapper {
            position: relative;
            min-width: 260px;
            flex: 1;
            max-width: 400px;
        }

        .search-wrapper input {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.5rem;
            border-radius: 40px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-main);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .search-wrapper input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* CONTENT BODY - FULLY RESPONSIVE GRID */
        .content-body {
            padding: 1.75rem;
            flex: 1;
        }

        /* Dashboard grid - fluid & responsive */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-card);
            border-radius: var(--radius-card);
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .card-title {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            font-weight: 600;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 800;
            margin: 0.5rem 0;
            line-height: 1.2;
        }

        .trend {
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .chart-container {
            background: var(--bg-card);
            border-radius: var(--radius-card);
            padding: 1.25rem;
            border: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
        }

        .recent-table {
            background: var(--bg-card);
            border-radius: var(--radius-card);
            border: 1px solid var(--border-color);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        @media (max-width: 640px) {
            .content-body {
                padding: 1rem;
            }

            .stat-value {
                font-size: 1.6rem;
            }

            .top-navbar {
                padding: 0.75rem 1rem;
            }
        }

        /* Toast & utilities */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .toast {
            background: var(--bg-card);
            border-left: 4px solid;
            border-radius: 16px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-lg);
            min-width: 280px;
            backdrop-filter: blur(10px);
            transform: translateX(120%);
            transition: transform 0.3s ease;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast-success {
            border-left-color: #10b981;
        }

        .toast-error {
            border-left-color: #ef4444;
        }

        .toast-info {
            border-left-color: #3b82f6;
        }

        .toast-close {
            cursor: pointer;
            background: none;
            border: none;
            margin-left: auto;
            color: var(--text-muted);
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(3px);
            z-index: 1040;
            display: none;
        }

        .mobile-toggle-btn,
        .desktop-collapse-btn {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        @media (min-width: 1025px) {
            .mobile-toggle-btn {
                display: none;
            }
        }

        @media (max-width: 1024px) {
            .desktop-collapse-btn {
                display: none;
            }
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .logo-3d {
            width: 48px;
            height: 48px;
            object-fit: contain;
            transition: transform 0.3s;
        }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            margin: 1.5rem 0 0.75rem 0.75rem;
        }

        .nav-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.7rem 1rem;
            border-radius: 14px;
            color: var(--text-main);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--primary);
            color: white;
        }

        .user-profile-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--bg-card);
            border-radius: 18px;
            padding: 0.75rem;
            margin-top: 1rem;
            border: 1px solid var(--border-color);
        }

        .avatar-placeholder {
            width: 44px;
            height: 44px;
            background: var(--primary);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: white;
        }

        .loader {
            width: 24px;
            height: 24px;
            border: 3px solid var(--border-color);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .search-results-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: var(--bg-card);
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            z-index: 1200;
            max-height: 380px;
            overflow-y: auto;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="app-sidebar">
            <div class="logo-container">
                <img src="https://placehold.co/400x400/4f46e5/white?text=N" alt="NACOC Logo" class="logo-3d" style="background:#4f46e5; border-radius: 14px; padding: 6px;">
                <div>
                    <h1 style="font-size: 1.6rem; font-weight: 800;">NACOC</h1>
                    <div style="font-size: 0.6rem; color: var(--text-muted); font-weight: 600;">Inventory Management</div>
                </div>
            </div>
            <div class="nav-section-title">MAIN MENU</div>
            <ul class="nav-menu">
                <li><a href="#" class="nav-link active"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a></li>
                <li><a href="#" class="nav-link"><i data-lucide="package-plus"></i><span>Received Items</span></a></li>
                <li><a href="#" class="nav-link"><i data-lucide="package-minus"></i><span>Issue Items</span></a></li>
            </ul>
            <div class="nav-section-title">OPERATIONS</div>
            <ul class="nav-menu">
                <li><a href="#" class="nav-link"><i data-lucide="file-chart-column"></i><span>Reports</span></a></li>
                <li><a href="#" class="nav-link"><i data-lucide="user-cog"></i><span>User Settings</span></a></li>
            </ul>
            <div class="user-profile-card" style="margin-top: auto;">
                <div class="avatar-placeholder">JD</div>
                <div style="flex:1; overflow:hidden;">
                    <div style="font-weight:700;">John Doe</div>
                    <div style="font-size:0.7rem; color:var(--text-muted);">@admin</div>
                </div>
                <button id="logout-sim" style="background:transparent; border:none; cursor:pointer;"><i data-lucide="log-out" style="width:18px;"></i></button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="main-content">
            <nav class="top-navbar">
                <div class="nav-left">
                    <button class="mobile-toggle-btn" id="mobileSidebarToggle"><i data-lucide="menu"></i></button>
                    <button class="desktop-collapse-btn" id="desktopCollapseToggle"><i data-lucide="panel-left-close"></i></button>
                    <div class="search-wrapper">
                        <span class="search-icon"><i data-lucide="search" style="width: 16px;"></i></span>
                        <input type="text" id="globalSearchInput" placeholder="Search inventory, reports, transactions...">
                        <div id="globalSearchResults" class="search-results-dropdown" style="display: none;"></div>
                    </div>
                </div>
                <div class="top-actions">
                    <div class="icon-btn"><i data-lucide="message-square"></i></div>
                    <div class="icon-btn"><i data-lucide="bell"></i><span class="badge">3</span></div>
                    <div class="icon-btn" id="themeToggleBtn"><i data-lucide="moon"></i></div>
                </div>
            </nav>

            <div class="content-body" id="dynamicContent">
                <!-- Dynamic Dashboard Content (fully responsive) -->
                <div class="dashboard-grid">
                    <div class="stat-card">
                        <div class="card-title">Total Items</div>
                        <div class="stat-value">1,284</div>
                        <div class="trend">↑ 12% this month</div>
                    </div>
                    <div class="stat-card">
                        <div class="card-title">Issued Items</div>
                        <div class="stat-value">342</div>
                        <div class="trend">↓ 5% vs last week</div>
                    </div>
                    <div class="stat-card">
                        <div class="card-title">Low Stock Alerts</div>
                        <div class="stat-value">8</div>
                        <div class="trend">Critical: 3 items</div>
                    </div>
                    <div class="stat-card">
                        <div class="card-title">Active Users</div>
                        <div class="stat-value">24</div>
                        <div class="trend">+2 new</div>
                    </div>
                </div>
                <div class="chart-container" id="inventoryChart" style="min-height: 320px;"></div>
                <div class="recent-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ITEM NAME</th>
                                <th>CATEGORY</th>
                                <th>STOCK</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Evidence Bags Large</td>
                                <td>Forensics</td>
                                <td>245</td>
                                <td>In Stock</td>
                            </tr>
                            <tr>
                                <td>Nitrile Gloves</td>
                                <td>PPE</td>
                                <td>82</td>
                                <td>Low Stock</td>
                            </tr>
                            <tr>
                                <td>Digital Scales</td>
                                <td>Equipment</td>
                                <td>12</td>
                                <td>OK</td>
                            </tr>
                            <tr>
                                <td>Seal Tape Rolls</td>
                                <td>Consumables</td>
                                <td>560</td>
                                <td>In Stock</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="sidebarOverlay" class="sidebar-overlay"></div>
    <div id="toastContainer" class="toast-container"></div>

    <script>
        // Helper functions & Global State
        let inventoryChart = null;
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);

        function updateThemeIcon() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            const themeBtn = document.getElementById('themeToggleBtn');
            if (themeBtn) {
                themeBtn.innerHTML = isDark ? '<i data-lucide="sun"></i>' : '<i data-lucide="moon"></i>';
                lucide.createIcons();
            }
        }

        function showToast(title, message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
            <i data-lucide="${type === 'success' ? 'check-circle' : 'alert-circle'}" style="width:22px; color:${type==='success'?'#10b981':'#ef4444'}"></i>
            <div style="flex:1"><strong>${title}</strong><div style="font-size:0.8rem;">${message}</div></div>
            <button class="toast-close">✕</button>
        `;
            container.appendChild(toast);
            lucide.createIcons();
            setTimeout(() => toast.classList.add('show'), 20);
            const timer = setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
            toast.querySelector('.toast-close').onclick = () => {
                clearTimeout(timer);
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 200);
            };
        }

        // render apex chart with responsive config
        function renderChart(theme) {
            const options = {
                series: [{
                    name: 'Stock Level',
                    data: [320, 450, 280, 540, 380, 610, 490]
                }],
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: {
                        show: false
                    },
                    background: 'transparent',
                    zoom: {
                        enabled: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                    colors: ['#4f46e5']
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.1
                    }
                },
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                    labels: {
                        style: {
                            colors: theme === 'dark' ? '#cbd5e1' : '#334155'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: theme === 'dark' ? '#cbd5e1' : '#334155'
                        }
                    }
                },
                grid: {
                    borderColor: theme === 'dark' ? '#334155' : '#e2e8f0'
                },
                tooltip: {
                    theme: theme
                },
            };
            if (inventoryChart) inventoryChart.destroy();
            inventoryChart = new ApexCharts(document.querySelector("#inventoryChart"), options);
            inventoryChart.render();
        }

        // responsive search simulation (mock api)
        const searchInput = document.getElementById('globalSearchInput');
        const resultsDiv = document.getElementById('globalSearchResults');
        let searchTimeout;

        function performMockSearch(query) {
            if (query.length < 2) {
                resultsDiv.style.display = 'none';
                return;
            }
            resultsDiv.innerHTML = '<div style="padding:1rem; text-align:center;"><div class="loader"></div> Searching...</div>';
            resultsDiv.style.display = 'block';
            setTimeout(() => {
                const mockData = [{
                        title: "Evidence Bags (Large)",
                        subtitle: "Stock: 245 units",
                        url: "#",
                        icon: "package"
                    },
                    {
                        title: "Nitrile Gloves - Box",
                        subtitle: "Low stock alert",
                        url: "#",
                        icon: "alert-triangle"
                    },
                    {
                        title: "Monthly Report Q2",
                        subtitle: "Inventory analysis",
                        url: "#",
                        icon: "file-text"
                    }
                ].filter(i => i.title.toLowerCase().includes(query.toLowerCase()) || i.subtitle.toLowerCase().includes(query.toLowerCase()));
                if (mockData.length === 0) {
                    resultsDiv.innerHTML = `<div style="padding:2rem;text-align:center;color:var(--text-muted)">No results for "${query}"</div>`;
                } else {
                    let html = '';
                    mockData.forEach(item => {
                        html += `<a href="#" style="display:flex; gap:12px; padding:12px 16px; border-bottom:1px solid var(--border-color); text-decoration:none; color:var(--text-main);">
                                <div style="background:var(--primary-light); width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center;"><i data-lucide="${item.icon}" style="width:18px;"></i></div>
                                <div><div style="font-weight:600;">${item.title}</div><div style="font-size:0.75rem; color:var(--text-muted)">${item.subtitle}</div></div>
                            </a>`;
                    });
                    resultsDiv.innerHTML = html;
                    lucide.createIcons();
                }
            }, 300);
        }
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => performMockSearch(e.target.value.trim()), 250);
        });
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) resultsDiv.style.display = 'none';
        });
        searchInput.addEventListener('focus', () => {
            if (searchInput.value.trim().length >= 2) resultsDiv.style.display = 'block';
        });

        // Sidebar logic (collapsible & responsive)
        const sidebar = document.getElementById('app-sidebar');
        const desktopToggle = document.getElementById('desktopCollapseToggle');
        const mobileToggle = document.getElementById('mobileSidebarToggle');
        const overlay = document.getElementById('sidebarOverlay');
        const closeSidebarMobile = () => {
            sidebar.classList.remove('active');
            overlay.style.display = 'none';
        };
        const openSidebarMobile = () => {
            sidebar.classList.add('active');
            overlay.style.display = 'block';
        };
        const isDesktop = () => window.innerWidth >= 1025;
        if (desktopToggle) {
            desktopToggle.addEventListener('click', () => {
                if (isDesktop()) {
                    sidebar.classList.toggle('collapsed');
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                    const icon = sidebar.classList.contains('collapsed') ? 'panel-left-open' : 'panel-left-close';
                    desktopToggle.innerHTML = `<i data-lucide="${icon}"></i>`;
                    lucide.createIcons();
                }
            });
        }
        mobileToggle.addEventListener('click', openSidebarMobile);
        overlay.addEventListener('click', closeSidebarMobile);
        if (window.innerWidth <= 1024) {
            sidebar.classList.remove('collapsed');
        }
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1025) {
                overlay.style.display = 'none';
                sidebar.classList.remove('active');
                const saved = localStorage.getItem('sidebarCollapsed') === 'true';
                if (saved) sidebar.classList.add('collapsed');
                else sidebar.classList.remove('collapsed');
                if (desktopToggle) desktopToggle.innerHTML = sidebar.classList.contains('collapsed') ? '<i data-lucide="panel-left-open"></i>' : '<i data-lucide="panel-left-close"></i>';
                lucide.createIcons();
            } else {
                sidebar.classList.remove('collapsed');
                if (desktopToggle) desktopToggle.innerHTML = '<i data-lucide="panel-left-close"></i>';
            }
        });
        // load saved desktop collapse state
        if (isDesktop() && localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
            if (desktopToggle) desktopToggle.innerHTML = '<i data-lucide="panel-left-open"></i>';
        } else if (isDesktop()) {
            sidebar.classList.remove('collapsed');
        }
        lucide.createIcons();

        // Theme toggle + chart update
        const themeBtn = document.getElementById('themeToggleBtn');
        themeBtn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            const newTheme = current === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon();
            const chartTheme = newTheme;
            if (inventoryChart) {
                inventoryChart.updateOptions({
                    grid: {
                        borderColor: chartTheme === 'dark' ? '#334155' : '#e2e8f0'
                    },
                    xaxis: {
                        labels: {
                            style: {
                                colors: chartTheme === 'dark' ? '#cbd5e1' : '#334155'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: chartTheme === 'dark' ? '#cbd5e1' : '#334155'
                            }
                        }
                    },
                    tooltip: {
                        theme: chartTheme
                    }
                });
            }
            showToast('Theme Changed', `Switched to ${newTheme} mode`, 'info');
        });
        updateThemeIcon();

        // Simulate initial chart render with resize observer responsiveness
        renderChart(document.documentElement.getAttribute('data-theme'));
        window.addEventListener('resize', () => {
            if (inventoryChart) inventoryChart.updateOptions({
                chart: {
                    width: '100%'
                }
            });
        });

        // Logout simulation toast
        document.getElementById('logout-sim')?.addEventListener('click', () => showToast('Logged out', 'Demo session ended', 'info'));

        // Global responsiveness: ensure all charts and containers reflow on resize
        window.dispatchEvent(new Event('resize'));

        // Optional: dynamic content previews for nav items (soft demo)
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                if (link.getAttribute('href') === '#') e.preventDefault();
                showToast('Navigation', `Demo: ${link.innerText.trim()}`, 'info');
            });
        });

        // Auto adjust toast container position on small screens (just style)
        console.log("Fully responsive dashboard ready");
    </script>
</body>

</html>