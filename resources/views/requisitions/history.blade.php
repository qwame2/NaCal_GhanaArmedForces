<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Requisition History | {{ \App\Models\Setting::get('organization_name', 'NACOC') }} Central Store</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Assets -->
    <link rel="stylesheet" href="{{ asset('css/dashboard_theme.css') }}?v={{ filemtime(public_path('css/dashboard_theme.css')) }}">
    
    <!-- Scripts -->
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>

    <script>
        // Pre-initialize theme to prevent flash
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    
    <style>
        :root {
            --font-display: 'Outfit', sans-serif;
            --font-sans: 'Plus Jakarta Sans', sans-serif;
            
            --store-orange: #f97316;
            --store-orange-hover: #ea580c;
            --store-orange-light: rgba(249, 115, 22, 0.08);
            
            --store-indigo: #6366f1;
            --store-indigo-hover: #4f46e5;
            --store-indigo-light: rgba(99, 102, 241, 0.08);
            
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow-premium: 0 20px 40px -15px rgba(15, 23, 42, 0.05), 0 0 0 1px rgba(15, 23, 42, 0.03);
            --shadow-hover: 0 30px 60px -15px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(15, 23, 42, 0.05);
            --header-blur: rgba(255, 255, 255, 0.8);
        }

        [data-theme="dark"] {
            --bg-main: #090d16;
            --bg-card: #111827;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #1f2937;
            --shadow-premium: 0 20px 40px -15px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.02);
            --shadow-hover: 0 30px 60px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.04);
            --header-blur: rgba(17, 24, 39, 0.8);
        }

        body {
            font-family: var(--font-sans);
            background: var(--bg-main);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* --- HEADER & NAVIGATION --- */
        .store-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: var(--header-blur);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 2rem;
            transition: all 0.3s ease;
        }

        .header-container {
            max-width: 1440px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
        }

        .store-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--text-main);
        }

        .brand-logo-container {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--store-orange), var(--store-indigo));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
        }

        .brand-name {
            font-family: var(--font-display);
            font-weight: 900;
            font-size: 1.25rem;
            letter-spacing: -0.03em;
            line-height: 1.1;
        }

        .brand-subtitle {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--store-orange);
            margin-top: 1px;
        }

        .store-search-bar {
            flex: 1;
            max-width: 600px;
            position: relative;
        }

        .store-search-input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 3rem;
            border: 2px solid var(--border-color);
            border-radius: 99px;
            background: var(--bg-main);
            color: var(--text-main);
            font-family: inherit;
            font-weight: 500;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .store-search-input:focus {
            border-color: var(--store-orange);
            background: var(--bg-card);
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.12);
        }

        .store-search-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            width: 18px;
            transition: color 0.25s;
        }

        .store-search-input:focus + .store-search-icon {
            color: var(--store-orange);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .action-btn {
            background: var(--bg-main);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            background: var(--border-color);
        }

        .cart-toggle-btn {
            background: var(--store-orange);
            border: none;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 99px;
            font-weight: 800;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(249, 115, 22, 0.3);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
        }

        .cart-toggle-btn:hover {
            background: var(--store-orange-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
        }

        .user-widget {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 4px 12px 4px 4px;
            background: var(--bg-main);
            border: 1px solid var(--border-color);
            border-radius: 99px;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            background: var(--store-indigo);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.85rem;
        }

        .user-info-name {
            font-size: 0.82rem;
            font-weight: 700;
            max-width: 110px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .logout-link {
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: color 0.15s;
        }

        .logout-link:hover {
            color: var(--danger-color);
        }

        /* --- HERO BANNER --- */
        .store-hero {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(249, 115, 22, 0.05) 100%);
            padding: 3rem 2rem 2.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .hero-banner {
            max-width: 1440px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 3rem;
            position: relative;
        }

        .hero-content {
            max-width: 680px;
            z-index: 2;
        }

        .hero-badge {
            background: var(--store-orange-light);
            color: var(--store-orange);
            font-size: 0.75rem;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 99px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.25rem;
            border: 1px solid rgba(249, 115, 22, 0.12);
        }

        .hero-title {
            font-family: var(--font-display);
            font-size: 2.25rem;
            font-weight: 900;
            letter-spacing: -0.03em;
            margin: 0 0 1rem 0;
            line-height: 1.15;
            background: linear-gradient(135deg, var(--text-main) 30%, #475569 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin: 0 0 2rem 0;
            max-width: 580px;
        }

        .hero-actions-container {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .hero-btn {
            background: var(--text-main);
            color: var(--bg-card);
            border: none;
            padding: 0.9rem 2rem;
            border-radius: 99px;
            font-weight: 800;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.1);
            transition: all 0.25s ease;
            text-decoration: none;
        }

        .hero-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15);
            background: #1e293b;
        }

        /* --- HISTORY TIMELINE PANEL --- */
        .history-container {
            max-width: 1440px;
            margin: 2rem auto 4rem auto;
            padding: 0 2rem;
        }

        .history-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 2.25rem;
            box-shadow: var(--shadow-premium);
        }

        .history-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .history-title {
            font-family: var(--font-display);
            font-size: 1.45rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            letter-spacing: -0.02em;
        }

        .refresh-btn {
            background: var(--bg-main);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 0.6rem 1.25rem;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.82rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .refresh-btn:hover {
            background: var(--border-color);
        }

        .history-item-box {
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.25rem;
            background: var(--bg-card);
            transition: all 0.25s ease;
        }

        .history-item-box:hover {
            box-shadow: var(--shadow-hover);
            border-color: var(--store-orange);
        }

        .history-item-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .history-ref {
            font-size: 0.8rem;
            font-weight: 800;
            color: var(--store-orange);
            background: var(--store-orange-light);
            padding: 4px 10px;
            border-radius: 8px;
            display: inline-block;
        }

        .history-meta-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 6px;
            flex-wrap: wrap;
        }

        .history-status-pills {
            display: flex;
            gap: 6px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* --- STYLISH STEPPER TIMELINE --- */
        .order-tracker {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            background: var(--bg-main);
            padding: 1rem 1.75rem;
            border-radius: 16px;
            margin: 1.25rem 0;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .tracker-progress-line {
            position: absolute;
            top: 50%;
            left: 3rem;
            right: 3rem;
            height: 3px;
            background: var(--border-color);
            z-index: 1;
            transform: translateY(-50%);
        }

        .tracker-step {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 6px;
        }

        .tracker-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .tracker-label {
            font-size: 0.7rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* Timeline Step States */
        .tracker-step.completed .tracker-dot {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
            border-color: var(--success-color);
            color: white;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
        }

        .tracker-step.completed .tracker-label {
            color: var(--success-color);
        }

        .tracker-step.active .tracker-dot {
            background: linear-gradient(135deg, var(--store-orange) 0%, var(--store-orange-hover) 100%);
            border-color: var(--store-orange);
            color: white;
            animation: pulse-orange 2s infinite;
        }

        .tracker-step.active .tracker-label {
            color: var(--store-orange);
        }

        .tracker-step.declined .tracker-dot {
            background: linear-gradient(135deg, var(--danger-color) 0%, #b91c1c 100%);
            border-color: var(--danger-color);
            color: white;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);
        }

        .tracker-step.declined .tracker-label {
            color: var(--danger-color);
        }

        @keyframes pulse-orange {
            0% { box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.4); }
            70% { box-shadow: 0 0 0 8px rgba(249, 115, 22, 0); }
            100% { box-shadow: 0 0 0 0 rgba(249, 115, 22, 0); }
        }

        /* --- SUPPLIES LIST GRID --- */
        .history-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .history-item-tag {
            background: var(--bg-main);
            border: 1px solid var(--border-color);
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 0.8rem;
            color: var(--text-main);
            line-height: 1.4;
            transition: all 0.2s ease;
        }

        .history-item-tag:hover {
            border-color: var(--store-indigo);
            background: var(--bg-card);
        }

        .history-notes-box {
            background: rgba(99, 102, 241, 0.03);
            border: 1px dashed rgba(99, 102, 241, 0.25);
            border-radius: 14px;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.82rem;
            color: var(--text-main);
            display: flex;
            gap: 8px;
            line-height: 1.5;
        }

        .history-notes-box i {
            color: var(--store-indigo);
        }

        .highlight-pulse {
            animation: card-pulse 1.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes card-pulse {
            0% { box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.4); border-color: var(--store-orange); }
            100% { box-shadow: var(--shadow-premium); border-color: var(--border-color); }
        }

        /* Responsive */
        @media(max-width: 768px) {
            .order-tracker {
                flex-direction: column;
                align-items: flex-start;
                gap: 1.5rem;
                padding: 1.5rem;
            }
            .tracker-progress-line {
                left: 2.25rem;
                top: 2rem;
                bottom: 2rem;
                width: 3px;
                height: calc(100% - 4rem);
                transform: none;
            }
            .tracker-step {
                flex-direction: row;
                text-align: left;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>

    <!-- --- HEADER BAR --- -->
    <header class="store-header">
        <div class="header-container">
            <a href="{{ route('requisitions.index') }}" class="store-brand">
                <div class="brand-logo-container">
                    <i data-lucide="shield" style="width: 22px; height: 22px;"></i>
                </div>
                <div>
                    <div class="brand-name">{{ \App\Models\Setting::get('organization_name', 'NACOC') }}</div>
                    <div class="brand-subtitle">Central Store</div>
                </div>
            </a>

            <!-- Dedicated History Search Bar -->
            <div class="store-search-bar">
                <input type="text" id="history-search" class="store-search-input" placeholder="Search by requisition #, department, purpose or items...">
                <i data-lucide="search" class="store-search-icon"></i>
            </div>

            <div class="header-actions">
                <!-- Theme Toggle Button -->
                <button class="action-btn" id="theme-toggle" title="Toggle System Theme">
                    <i data-lucide="moon" style="width: 18px;"></i>
                </button>

                <!-- Back to Catalog Storefront -->
                <a href="{{ route('requisitions.index') }}" class="cart-toggle-btn">
                    <i data-lucide="shopping-bag" style="width: 16px;"></i>
                    <span>Back to Catalog</span>
                </a>

                <!-- User Profile Info -->
                <div class="user-widget">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', auth()->user()->name)[1] ?? '', 0, 1)) }}
                    </div>
                    <div class="user-info-name">{{ auth()->user()->name }}</div>
                    <div class="logout-link" onclick="document.getElementById('logout-form').submit();" title="Sign Out">
                        <i data-lucide="log-out" style="width: 14px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- --- HERO BANNER --- -->
    <section class="store-hero">
        <div class="hero-banner">
            <div class="hero-content">
                <div class="hero-badge">
                    <i data-lucide="clock" style="width: 13px;"></i> Requisition Logs & Tracking
                </div>
                <h2 class="hero-title">Track Requisition Pipelines in Real Time</h2>
                <p class="hero-desc">Monitor approval states, check central store notes, and view physical pick-up metadata for all store supply requests originating from your profile account.</p>
                <div class="hero-actions-container">
                    <a href="{{ route('requisitions.index') }}" class="hero-btn">
                        <i data-lucide="shopping-bag" style="width: 16px;"></i> Request New Supplies
                    </a>
                </div>
            </div>
            <div class="hero-art">
                <i data-lucide="history" style="width: 240px; height: 240px; color: rgba(99, 102, 241, 0.15); stroke-width: 1;"></i>
            </div>
        </div>
    </section>

    <!-- --- HISTORY SECTION --- -->
    <section class="history-container">
        <div class="history-card">
            <div class="history-header">
                <h3 class="history-title">
                    <i data-lucide="clock" style="width: 22px; color: var(--store-orange);"></i>
                    My Requisition Log History
                </h3>
                <button class="refresh-btn" onclick="loadMyRequisitions()">
                    <i data-lucide="refresh-cw" style="width: 14px;"></i> Refresh Log
                </button>
            </div>

            <div id="history-items-list">
                <div style="padding: 3rem 1.5rem; text-align: center; color: var(--text-muted);">
                    <i data-lucide="loader" style="width: 32px; height: 32px; margin: 0 auto 0.75rem auto; animation: spin 1s linear infinite; opacity: 0.5;"></i>
                    <p>Loading historical requisition logs...</p>
                </div>
            </div>
        </div>
    </section>

    <script>
        let allRequisitions = [];
        let searchQuery = '';

        // Dynamic API loader for user requisitions
        async function loadMyRequisitions() {
            const container = document.getElementById('history-items-list');
            try {
                const response = await fetch('{{ route("requisitions.my") }}');
                allRequisitions = await response.json();
                
                // Track state transitions/changes locally
                const snapshotKey = 'requisitions_status_snapshot';
                const savedSnapshot = localStorage.getItem(snapshotKey);
                let previousData = {};
                if (savedSnapshot) {
                    try { previousData = JSON.parse(savedSnapshot); } catch(e) {}
                }

                const movements = [];
                const currentSnapshot = {};

                allRequisitions.forEach(req => {
                    currentSnapshot[req.id] = {
                        id: req.id,
                        status: req.status,
                        status_label: req.status_badge.label,
                        status_badge_bg: req.status_badge.bg,
                        status_badge_color: req.status_badge.color,
                        admin_notes: req.admin_notes || '',
                        items: req.items.map(item => ({
                            description: item.description,
                            quantity_requested: item.quantity_requested,
                            quantity_approved: item.quantity_approved,
                            alternative_description: item.alternative_description,
                            alternative_quantity_approved: item.alternative_quantity_approved,
                            remarks: item.remarks || ''
                        }))
                    };

                    if (savedSnapshot && previousData[req.id]) {
                        const prev = previousData[req.id];
                        let reqMoved = false;
                        let details = [];

                        if (prev.status !== req.status) {
                            reqMoved = true;
                            details.push(`Status transitioned from <span style="background:${prev.status_badge_bg}; color:${prev.status_badge_color}; padding:2px 8px; border-radius:12px; font-size:0.75rem; font-weight:700;">● ${prev.status_label}</span> to <span style="background:${req.status_badge.bg}; color:${req.status_badge.color}; padding:2px 8px; border-radius:12px; font-size:0.75rem; font-weight:700;">● ${req.status_badge.label}</span>`);
                        }

                        if (prev.admin_notes !== (req.admin_notes || '')) {
                            reqMoved = true;
                            if (req.admin_notes) {
                                details.push(`Remarks: <span style="color:var(--store-orange); font-style:italic;">"${req.admin_notes}"</span>`);
                            }
                        }

                        req.items.forEach(currItem => {
                            const prevItem = prev.items.find(pi => pi.description === currItem.description);
                            const prevApproved = prevItem ? prevItem.quantity_approved : null;
                            const currApproved = currItem.quantity_approved;
                            if (prevApproved !== currApproved && currApproved !== null) {
                                reqMoved = true;
                                details.push(`<b>${currItem.description}</b> approved allocation: <b>${parseFloat(currApproved).toLocaleString()}</b>`);
                            }

                            const prevAltApproved = prevItem ? prevItem.alternative_quantity_approved : null;
                            const currAltApproved = currItem.alternative_quantity_approved;
                            if (prevAltApproved !== currAltApproved && currAltApproved !== null && currAltApproved > 0) {
                                reqMoved = true;
                                details.push(`<b>${currItem.description}</b> alternative approved (${currItem.alternative_description || 'Alternative'}): <b>${parseFloat(currAltApproved).toLocaleString()}</b>`);
                            }
                        });

                        if (reqMoved) {
                            movements.push({ id: req.id, statusLabel: req.status_badge.label, statusBg: req.status_badge.bg, statusColor: req.status_badge.color, details });
                        }
                    }
                });

                localStorage.setItem(snapshotKey, JSON.stringify(currentSnapshot));

                if (movements.length > 0) {
                    let alertHtml = `<div style="text-align: left; max-height: 380px; overflow-y: auto;">`;
                    movements.forEach(m => {
                        alertHtml += `
                            <div style="border: 1px solid var(--border-color); border-left: 4px solid var(--store-orange); border-radius: 12px; padding: 1rem; margin-bottom: 0.75rem; background: var(--bg-main);">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                                    <span style="font-weight: 800; font-size: 0.9rem;">Requisition #${m.id}</span>
                                    <span style="background:${m.statusBg}; color:${m.statusColor}; padding:2px 8px; border-radius:12px; font-size:0.68rem; font-weight:800; text-transform:uppercase;">
                                        ${m.statusLabel}
                                    </span>
                                </div>
                                <ul style="margin: 0; padding-left: 1.1rem; font-size: 0.82rem; line-height: 1.5;">
                                    ${m.details.map(d => `<li style="margin-bottom: 4px;">${d}</li>`).join('')}
                                </ul>
                            </div>
                        `;
                    });
                    alertHtml += `</div>`;

                    Swal.fire({
                        title: '🔔 Requisition Updates!',
                        html: alertHtml,
                        icon: 'info',
                        confirmButtonText: 'OK',
                        confirmButtonColor: 'var(--store-orange)'
                    });
                }

                renderHistoryList();
            } catch(e) {
                console.error(e);
                container.innerHTML = `
                    <div style="padding: 3rem 1.5rem; text-align: center; color: var(--danger-color);">
                        <i data-lucide="alert-triangle" style="width: 32px; height: 32px; margin: 0 auto 0.75rem auto;"></i>
                        <p>Failed to retrieve central store requisition histories. Check connection.</p>
                    </div>
                `;
                lucide.createIcons();
            }
        }

        // Live Javascript history filters
        function renderHistoryList() {
            const container = document.getElementById('history-items-list');
            const q = searchQuery.toLowerCase().trim();

            const filtered = allRequisitions.filter(req => {
                if (!q) return true;
                const matchId = req.id.toString().includes(q);
                const matchDept = req.department.toLowerCase().includes(q);
                const matchPurpose = req.purpose.toLowerCase().includes(q);
                const matchItems = req.items.some(i => i.description.toLowerCase().includes(q));
                return matchId || matchDept || matchPurpose || matchItems;
            });

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div style="padding: 4rem 1.5rem; text-align: center; color: var(--text-muted);">
                        <i data-lucide="search" style="width: 42px; height: 42px; margin: 0 auto 1rem auto; opacity: 0.3; color: var(--store-orange);"></i>
                        <h4 style="font-size: 1.05rem; color: var(--text-main); margin-bottom: 0.3rem;">No matching requisitions found</h4>
                        <p style="font-size: 0.82rem; max-width: 350px; margin: 0 auto;">Try adjusting your query filter, requisition reference number or status terms.</p>
                    </div>
                `;
                lucide.createIcons();
                return;
            }

            container.innerHTML = filtered.map(req => {
                let isCollected = req.collected_at !== null && req.collected_at !== undefined;
                let completedSteps = 1;
                let activeStep = 2;
                let isDeclined = req.status === 'declined';
                let isPartially = req.status === 'partially_approved';
                let isApproved = req.status === 'approved';
                
                if (isCollected) {
                    completedSteps = 4;
                } else if (isApproved || isPartially) {
                    completedSteps = 3;
                    activeStep = 4;
                } else if (isDeclined) {
                    completedSteps = 1;
                    activeStep = 2;
                }

                let progressWidth = '0%';
                if (completedSteps === 1 && !isDeclined) progressWidth = '33%';
                if (completedSteps === 3) progressWidth = '66%';
                if (completedSteps === 4) progressWidth = '100%';

                return `
                    <div class="history-item-box">
                        <div class="history-item-top">
                            <div>
                                <span class="history-ref">Requisition Ref: #${req.id}</span>
                                <div class="history-meta-info">
                                    <span><i data-lucide="calendar" style="width:12px; vertical-align:middle;"></i> ${req.created_at}</span>
                                    <span>·</span>
                                    <span>Department: <b>${req.department}</b></span>
                                    <span>·</span>
                                    <span>Priority: <b style="color:${req.priority === 'urgent' ? 'var(--danger-color)' : 'var(--store-indigo)'}; text-transform:uppercase;">${req.priority}</b></span>
                                </div>
                            </div>
                            <div class="history-status-pills">
                                <span class="status-pill" style="background:${req.usage_type_badge.bg}; color:${req.usage_type_badge.color}; border: 1px solid rgba(0,0,0,0.02);">
                                    ${req.usage_type_badge.label}
                                </span>
                                <span class="status-pill" style="background:${req.status_badge.bg}; color:${req.status_badge.color};">
                                    ● ${req.status_badge.label}
                                </span>
                                <span class="status-pill" style="background:${req.priority_badge.bg}; color:${req.priority_badge.color};">
                                    ${req.priority_badge.label}
                                </span>
                            </div>
                        </div>

                        <p style="font-size:0.85rem; color:var(--text-main); margin: 0.5rem 0 1rem 0; font-weight:600; line-height:1.4;">
                            <i data-lucide="quote" style="width:12px; color:var(--store-orange); transform:scaleX(-1); vertical-align:top; margin-right:4px;"></i>
                            ${req.purpose}
                        </p>

                        <div class="order-tracker">
                            <div class="tracker-progress-line" style="width: ${progressWidth};"></div>
                            
                            <div class="tracker-step completed">
                                <div class="tracker-dot"><i data-lucide="file-text" style="width: 14px;"></i></div>
                                <span class="tracker-label">Submitted</span>
                            </div>

                            <div class="tracker-step ${completedSteps >= 2 ? 'completed' : (isDeclined ? 'declined' : 'active')}">
                                <div class="tracker-dot">
                                    <i data-lucide="${isDeclined ? 'x-circle' : 'activity'}" style="width: 14px;"></i>
                                </div>
                                <span class="tracker-label">${isDeclined ? 'Declined' : 'Under Review'}</span>
                            </div>

                            <div class="tracker-step ${completedSteps >= 3 ? 'completed' : ''}">
                                <div class="tracker-dot">
                                    <i data-lucide="${isPartially ? 'alert-triangle' : 'check-circle'}" style="width: 14px;"></i>
                                </div>
                                <span class="tracker-label">${isPartially ? 'Partially Approved' : 'Approved'}</span>
                            </div>

                            <div class="tracker-step ${completedSteps >= 4 ? 'completed' : ''}">
                                <div class="tracker-dot"><i data-lucide="package-open" style="width: 14px;"></i></div>
                                <span class="tracker-label">Collection</span>
                            </div>
                        </div>

                        <div class="history-items-grid">
                            ${req.items.map(item => {
                                const approved = item.quantity_approved !== null ? parseFloat(item.quantity_approved) : 0;
                                const altApproved = item.alternative_quantity_approved !== null ? parseFloat(item.alternative_quantity_approved) : 0;
                                return `
                                <div class="history-item-tag" style="${item.alternative_description ? 'grid-column: 1 / -1; max-width: 100%;' : ''}">
                                    <b>${item.description}</b> 
                                    <span style="color:var(--store-orange)">—</span> ${parseFloat(item.quantity_requested).toLocaleString()} ${item.unit}
                                    ${item.quantity_approved !== null ? `
                                        <span style="color:var(--success-color); font-weight:800; margin-left:4px;">
                                            (Approved: ${approved.toLocaleString()} ${item.unit})
                                        </span>
                                    ` : ''}
                                    ${item.alternative_description ? `
                                        <div style="font-size:0.8rem; font-weight:800; color:var(--store-orange); display:flex; align-items:center; gap:4px; margin-top:6px;">
                                            <i data-lucide="shuffle" style="width:14px;height:14px;display:inline-block;vertical-align:middle;margin-right:2px;"></i>Note: Alternative Approved: ${item.alternative_description} (ALT-${altApproved.toLocaleString()} ${item.unit})
                                        </div>
                                    ` : ''}
                                    ${item.remarks && !item.remarks.includes('Alternative Approved:') ? `<br><small style="color:var(--text-muted); font-style:italic;">Note: ${item.remarks}</small>` : ''}
                                </div>
                            `; }).join('')}
                        </div>

                        ${req.admin_notes ? `
                            <div class="history-notes-box" style="margin-bottom: 0.75rem;">
                                <i data-lucide="message-square" style="width: 15px; flex-shrink:0; margin-top:2px;"></i>
                                <div>
                                    <b>Store Officer Remark:</b> ${req.admin_notes}
                                </div>
                            </div>
                        ` : ''}

                        <div class="requisition-actions-row" style="margin-top: 1rem; display: flex; gap: 0.75rem; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
                            ${req.status === 'pending' ? `
                                <button class="action-btn-followup" onclick="sendFollowUp(${req.id}, this)" style="display: inline-flex; align-items: center; gap: 6px; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2); color: var(--warning-color); padding: 8px 16px; border-radius: 10px; font-weight: 800; font-size: 0.78rem; cursor: pointer; transition: all 0.2s;">
                                    <i data-lucide="bell" style="width: 14px;"></i> Follow Up
                                </button>
                            ` : ''}
                            ${req.collected_at ? `
                                <div class="collection-status-indicator" style="display: inline-flex; align-items: center; gap: 6px; background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15); color: var(--success-color); padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 0.78rem;">
                                    <i data-lucide="check-circle" style="width: 14px; color: var(--success-color);"></i> Collected on ${req.collected_at} ${req.collected_by_name ? `by ${req.collected_by_name}` : ''}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            }).join('');
            lucide.createIcons();
        }

        async function sendFollowUp(id, btn) {
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader" style="width: 14px; animation: spin 1s linear infinite; vertical-align: middle;"></i> Sending...`;
            lucide.createIcons();

            try {
                const response = await fetch(`/requisitions/${id}/followup`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Follow Up Sent!',
                        text: data.message,
                        confirmButtonColor: 'var(--store-orange)'
                    });
                    btn.innerHTML = `<i data-lucide="check" style="width: 14px;"></i> Reminder Sent`;
                    btn.style.background = 'rgba(100, 116, 139, 0.05)';
                    btn.style.borderColor = 'rgba(100, 116, 139, 0.1)';
                    btn.style.color = 'var(--text-muted)';
                    btn.style.cursor = 'not-allowed';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to Follow Up',
                        text: data.message,
                        confirmButtonColor: 'var(--store-orange)'
                    });
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }
                lucide.createIcons();
            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'An error occurred while sending the follow-up reminder.',
                    confirmButtonColor: 'var(--store-orange)'
                });
                btn.disabled = false;
                btn.innerHTML = originalHTML;
                lucide.createIcons();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            loadMyRequisitions();

            // Search filter binding
            const searchInput = document.getElementById('history-search');
            searchInput.addEventListener('input', (e) => {
                searchQuery = e.target.value;
                renderHistoryList();
            });

            // Theme Toggle logic
            const themeToggleBtn = document.getElementById('theme-toggle');
            function updateThemeIcon(theme) {
                themeToggleBtn.innerHTML = theme === 'dark' ? 
                    '<i data-lucide="sun" style="width: 18px;"></i>' : 
                    '<i data-lucide="moon" style="width: 18px;"></i>';
                lucide.createIcons();
            }
            updateThemeIcon(savedTheme);

            themeToggleBtn.addEventListener('click', () => {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeIcon(newTheme);
            });
        });
    </script>
</body>
</html>
