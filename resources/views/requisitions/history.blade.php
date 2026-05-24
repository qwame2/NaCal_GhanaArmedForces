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

        /* Swal Profile Styles */
        .glass-monolith-popup {
            border-radius: 32px !important;
            padding: 1.75rem 2rem !important;
            box-shadow: 0 40px 100px -20px rgba(0,0,0,0.15) !important;
            border: 1px solid rgba(255,255,255,0.8) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .premium-swal-btn {
            height: 48px !important;
            padding: 0 30px !important;
            border-radius: 14px !important;
            font-weight: 800 !important;
            font-size: 0.85rem !important;
            letter-spacing: 0.02em !important;
            box-shadow: 0 10px 20px rgba(249, 115, 22, 0.2) !important;
        }
        .premium-swal-cancel-btn {
            height: 48px !important;
            padding: 0 25px !important;
            border-radius: 14px !important;
            font-weight: 800 !important;
            font-size: 0.85rem !important;
            color: #64748b !important;
        }
        .swal-input-group {
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }
        .swal-input-group label {
            text-align: left;
        }
        .spin {
            animation: spin 1.2s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Profile Modal Redesigned Fields */
        .swal-field-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }
        .swal-field-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            pointer-events: none;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .swal-field-input {
            width: 100%;
            height: 44px;
            padding: 0 1rem 0 2.6rem !important;
            border-radius: 14px !important;
            border: 2px solid #e2e8f0 !important;
            background: #f8fafc !important;
            color: #0f172a !important;
            font-size: 0.88rem !important;
            font-weight: 700 !important;
            outline: none !important;
            box-shadow: none !important;
            margin: 0 !important;
            transition: all 0.3s !important;
        }
        .swal-field-input:focus {
            border-color: var(--store-orange) !important;
            background: white !important;
            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.06) !important;
        }
        .swal-field-input[readonly] {
            opacity: 0.65;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

    <!-- --- HEADER BAR --- -->
    <header class="store-header">
        <div class="header-container">
            <a href="{{ route('requisitions.index') }}" class="store-brand">
                <div class="brand-logo-container" style="background: transparent; box-shadow: none; width: 56px; height: 56px;">
                    <img src="{{ asset('img/download-1.webp') }}" alt="Logo" style="width: 56px; height: 56px; object-fit: contain; border-radius: 12px;">
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


                <!-- Back to Catalog Storefront -->
                <a href="{{ route('requisitions.index') }}" class="cart-toggle-btn">
                    <i data-lucide="shopping-bag" style="width: 16px;"></i>
                    <span>Back to Catalog</span>
                </a>

                <!-- User Profile Info -->
                <div class="user-widget" onclick="openProfileCompletionModal()" style="cursor: pointer; transition: 0.2s;" onmouseover="this.style.borderColor='var(--store-orange)';" onmouseout="this.style.borderColor='var(--border-color)';" title="My Profile Details">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                    @else
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', auth()->user()->name)[1] ?? '', 0, 1)) }}
                        </div>
                    @endif
                    <div class="user-info-name">{{ auth()->user()->name }}</div>
                    <div style="color: var(--text-muted); display: flex; align-items: center; padding: 2px;">
                        <i data-lucide="user-cog" style="width: 14px;"></i>
                    </div>
                </div>
                <div class="logout-link" onclick="event.stopPropagation(); document.getElementById('logout-form').submit();" title="Sign Out" style="margin-left: 0.5rem; border: 1px solid var(--border-color); width: 32px; height: 32px; justify-content: center; display: flex; align-items: center;">
                    <i data-lucide="log-out" style="width: 14px;"></i>
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
        let currentPage = 1;
        const itemsPerPage = 5;

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
                        decline_reason: req.decline_reason || '',
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

                        if (prev.decline_reason !== (req.decline_reason || '')) {
                            reqMoved = true;
                            if (req.decline_reason) {
                                details.push(`Decline Reason: <span style="color:var(--danger-color); font-weight:700;">"${req.decline_reason}"</span>`);
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
                            const uniqueId = req.unique_id || ('REQ-' + String(req.id).padStart(5, '0'));
                            movements.push({ id: req.id, unique_id: uniqueId, statusLabel: req.status_badge.label, statusBg: req.status_badge.bg, statusColor: req.status_badge.color, details });
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
                                    <span style="font-weight: 800; font-size: 0.9rem;">Requisition ${m.unique_id}</span>
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
                /* console print removed */
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
                const uniqueId = req.unique_id || ('REQ-' + String(req.id).padStart(5, '0'));
                const matchId = req.id.toString().includes(q);
                const matchUniqueId = uniqueId.toLowerCase().includes(q);
                const matchDept = req.department.toLowerCase().includes(q);
                const matchPurpose = req.purpose.toLowerCase().includes(q);
                const matchItems = req.items.some(i => i.description.toLowerCase().includes(q));
                return matchId || matchUniqueId || matchDept || matchPurpose || matchItems;
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

            const totalItems = filtered.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            if (currentPage > totalPages) {
                currentPage = totalPages;
            }
            if (currentPage < 1) {
                currentPage = 1;
            }

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
            const paginated = filtered.slice(startIndex, endIndex);

            let itemsHtml = paginated.map(req => {
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
                                <span class="history-ref">Requisition Ref: ${req.unique_id || ('REQ-' + String(req.id).padStart(5, '0'))}</span>
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

                        ${req.decline_reason ? `
                            <div class="history-notes-box" style="margin-bottom: 0.75rem; border: 1px dashed rgba(239, 68, 68, 0.25); background: rgba(239, 68, 68, 0.03);">
                                <i data-lucide="x-circle" style="width: 15px; flex-shrink:0; margin-top:2px; color: var(--danger-color);"></i>
                                <div>
                                    <b style="color: var(--danger-color);">Decline Reason:</b> ${req.decline_reason}
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

            // Pagination Controls
            let paginationHtml = '';
            if (totalPages > 1) {
                paginationHtml = `
                    <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:2rem; padding-top:1.5rem; border-top:1px solid var(--border-color); flex-wrap:wrap; gap:1rem;">
                        <div style="font-size:0.85rem; color:var(--text-muted); font-weight:600;">
                            Showing <span style="color:var(--text-main); font-weight:800;">${startIndex + 1}</span> to <span style="color:var(--text-main); font-weight:800;">${endIndex}</span> of <span style="color:var(--text-main); font-weight:800;">${totalItems}</span> requisitions
                        </div>
                        <div style="display:flex; gap:0.5rem; align-items:center;">
                            <button onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} class="pagination-btn prev-btn" style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px; border:1.5px solid var(--border-color); background:${currentPage === 1 ? 'var(--bg-main)' : 'var(--bg-card)'}; color:${currentPage === 1 ? 'var(--text-muted)' : 'var(--text-main)'}; cursor:${currentPage === 1 ? 'not-allowed' : 'pointer'}; opacity:${currentPage === 1 ? '0.5' : '1'}; transition:all 0.2s;" onmouseover="if(${currentPage !== 1}){this.style.borderColor='var(--store-orange)'; this.style.color='var(--store-orange)';}" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">
                                <i data-lucide="chevron-left" style="width:16px;"></i>
                            </button>
                `;

                for (let i = 1; i <= totalPages; i++) {
                    const isActive = i === currentPage;
                    paginationHtml += `
                        <button onclick="goToPage(${i})" class="pagination-btn page-num-btn ${isActive ? 'active' : ''}" style="display:inline-flex; align-items:center; justify-content:center; min-width:38px; height:38px; padding:0 8px; border-radius:10px; border:1.5px solid ${isActive ? 'var(--store-orange)' : 'var(--border-color)'}; background:${isActive ? 'var(--store-orange)' : 'var(--bg-card)'}; color:${isActive ? 'white' : 'var(--text-main)'}; font-weight:800; font-size:0.85rem; cursor:pointer; transition:all 0.2s;" onmouseover="if(!${isActive}){this.style.borderColor='var(--store-orange)'; this.style.color='var(--store-orange)';}" onmouseout="if(!${isActive}){this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';}">
                            ${i}
                        </button>
                    `;
                }

                paginationHtml += `
                            <button onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} class="pagination-btn next-btn" style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px; border:1.5px solid var(--border-color); background:${currentPage === totalPages ? 'var(--bg-main)' : 'var(--bg-card)'}; color:${currentPage === totalPages ? 'var(--text-muted)' : 'var(--text-main)'}; cursor:${currentPage === totalPages ? 'not-allowed' : 'pointer'}; opacity:${currentPage === totalPages ? '0.5' : '1'}; transition:all 0.2s;" onmouseover="if(${currentPage !== totalPages}){this.style.borderColor='var(--store-orange)'; this.style.color='var(--store-orange)';}" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">
                                <i data-lucide="chevron-right" style="width:16px;"></i>
                            </button>
                        </div>
                    </div>
                `;
            }

            container.innerHTML = itemsHtml + paginationHtml;
            lucide.createIcons();
        }

        function goToPage(page) {
            currentPage = page;
            renderHistoryList();
            document.querySelector('.history-card').scrollIntoView({ behavior: 'smooth' });
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
                /* console print removed */
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
                currentPage = 1;
                renderHistoryList();
            });
        });

        function openProfileCompletionModal() {
            const user = {
                name: '{{ addslashes(auth()->user()->name) }}',
                username: '{{ addslashes(auth()->user()->username) }}',
                email: '{{ auth()->user()->email ?? "" }}',
                phone: '{{ auth()->user()->phone ?? "" }}',
                department: '{{ auth()->user()->department ?? "" }}',
                role: '{{ auth()->user()->role ?? "" }}',
                avatar: '{{ auth()->user()->avatar ? Storage::url(auth()->user()->avatar) : "" }}'
            };

            Swal.fire({
                title: `
                    <div style="display: flex; align-items: center; gap: 15px; text-align: left; width: 100%;">
                        <div style="width: 48px; height: 48px; background: rgba(249, 115, 22, 0.1); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #f97316;">
                            <i data-lucide="user-check"></i>
                        </div>
                        <div>
                            <div style="font-size: 1.25rem; font-weight: 950; color: #0f172a;">Complete Profile</div>
                            <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; margin-top: 2px; text-transform: uppercase;">Update contact and verification records</div>
                        </div>
                    </div>
                `,
                html: `
                    <div style="text-align: left; padding: 1rem 0.5rem; font-family: 'Plus Jakarta Sans', sans-serif;">
                        
                        <!-- Avatar Upload Area -->
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 1.25rem;">
                            <div style="position: relative; width: 100px; height: 100px;" id="swal-avatar-container">
                                ${user.avatar ? `
                                    <img src="${user.avatar}" id="swal-avatar-preview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                                ` : `
                                    <div id="swal-avatar-placeholder" style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--store-indigo) 0%, #4338ca 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; font-weight: 950; color: white; border: 4px solid white; box-shadow: 0 10px 20px rgba(99,102,241,0.25);">
                                        ${user.name.substring(0, 1).toUpperCase()}
                                    </div>
                                `}
                                <!-- Floating Upload Button -->
                                <button type="button" onclick="document.getElementById('swal-avatar-file').click()" style="position: absolute; bottom: 0; right: 0; width: 32px; height: 32px; background: white; border-radius: 50%; border: 1.5px solid #cbd5e1; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 8px rgba(0,0,0,0.12); transition: 0.2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'" title="Upload Photo">
                                    <i data-lucide="camera" style="width: 15px; color: var(--store-orange);"></i>
                                </button>
                                <input type="file" id="swal-avatar-file" accept="image/*" style="display: none;" onchange="previewAndUploadSwalAvatar(this)">
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b; font-weight: 700; margin-top: 8px;">JPEG/PNG image, maximum size 5MB</div>
                        </div>

                        <form id="profileForm" style="display: flex; flex-direction: column; gap: 0.85rem;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="swal-input-group">
                                    <label style="display: block; font-size: 0.68rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.05em;">Full Name</label>
                                    <div class="swal-field-wrapper">
                                        <div class="swal-field-icon"><i data-lucide="user"></i></div>
                                        <input type="text" id="swal-prof-name" value="${user.name}" class="swal-field-input" placeholder="e.g. John Doe" required>
                                    </div>
                                </div>
                                <div class="swal-input-group">
                                    <label style="display: block; font-size: 0.68rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.05em;">Username</label>
                                    <div class="swal-field-wrapper">
                                        <div class="swal-field-icon"><i data-lucide="fingerprint"></i></div>
                                        <input type="text" value="${user.username}" class="swal-field-input" placeholder="Username" readonly>
                                    </div>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="swal-input-group">
                                    <label style="display: block; font-size: 0.68rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.05em;">Email Address</label>
                                    <div class="swal-field-wrapper">
                                        <div class="swal-field-icon"><i data-lucide="mail"></i></div>
                                        <input type="email" id="swal-prof-email" value="${user.email}" class="swal-field-input" placeholder="e.g. email@domain.com">
                                    </div>
                                </div>
                                <div class="swal-input-group">
                                    <label style="display: block; font-size: 0.68rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.05em;">Phone Number</label>
                                    <div class="swal-field-wrapper">
                                        <div class="swal-field-icon"><i data-lucide="phone"></i></div>
                                        <input type="text" id="swal-prof-phone" value="${user.phone}" class="swal-field-input" placeholder="e.g. +233...">
                                    </div>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="swal-input-group">
                                    <label style="display: block; font-size: 0.68rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.05em;">Department (Unit)</label>
                                    <div class="swal-field-wrapper">
                                        <div class="swal-field-icon"><i data-lucide="building"></i></div>
                                        <input type="text" id="swal-prof-dept" value="${user.department}" class="swal-field-input" readonly placeholder="e.g. IT, Security">
                                    </div>
                                </div>
                                <div class="swal-input-group">
                                    <label style="display: block; font-size: 0.68rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.05em;">Professional Role</label>
                                    <div class="swal-field-wrapper">
                                        <div class="swal-field-icon"><i data-lucide="shield"></i></div>
                                        <input type="text" id="swal-prof-role" value="${user.role}" class="swal-field-input" placeholder="e.g. Officer">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Save Profile Settings',
                cancelButtonText: 'Close',
                confirmButtonColor: '#f97316',
                cancelButtonColor: '#f1f5f9',
                customClass: {
                    popup: 'glass-monolith-popup',
                    confirmButton: 'premium-swal-btn',
                    cancelButton: 'premium-swal-cancel-btn'
                },
                width: '760px',
                didOpen: () => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                },
                preConfirm: async () => {
                    const name = document.getElementById('swal-prof-name').value;
                    const email = document.getElementById('swal-prof-email').value;
                    const phone = document.getElementById('swal-prof-phone').value;
                    const department = document.getElementById('swal-prof-dept').value;
                    const role = document.getElementById('swal-prof-role').value;

                    if (!name) {
                        Swal.showValidationMessage('Full Name is required');
                        return false;
                    }

                    try {
                        const res = await fetch("{{ route('settings.update', [], false) }}", {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json', 
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ name, email, phone, department, role })
                        });
                        const data = await res.json();
                        if (!res.ok || !data.success) {
                            Swal.showValidationMessage(data.message || 'Profile sync failed.');
                            return false;
                        }
                        return data;
                    } catch (e) {
                        Swal.showValidationMessage('Network node transmission error.');
                        return false;
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Profile Updated',
                        text: 'Your details have been successfully synchronized.',
                        icon: 'success',
                        confirmButtonColor: '#f97316'
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        }

        async function previewAndUploadSwalAvatar(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];

            // Validate file size limit
            const maxMB = 5;
            if (file.size > maxMB * 1024 * 1024) {
                Swal.showValidationMessage(`Selected file size must be less than ${maxMB}MB.`);
                return;
            }

            const formData = new FormData();
            formData.append('avatar', file);

            try {
                // Show upload loading indicator in container
                const container = document.getElementById('swal-avatar-container');
                const originalHTML = container.innerHTML;
                container.innerHTML = `
                    <div style="width: 100px; height: 100px; border-radius: 50%; background: rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: center; border: 4px solid white; box-shadow: 0 10px 20px rgba(0,0,0,0.05);">
                        <i data-lucide="loader-2" class="spin" style="width: 24px; color: #f97316;"></i>
                    </div>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons();

                const res = await fetch("{{ route('settings.avatar', [], false) }}", {
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json' 
                    },
                    body: formData
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    container.innerHTML = `
                        <img src="${data.url}?t=${new Date().getTime()}" id="swal-avatar-preview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                        <!-- Floating Upload Button -->
                        <button type="button" onclick="document.getElementById('swal-avatar-file').click()" style="position: absolute; bottom: 0; right: 0; width: 32px; height: 32px; background: white; border-radius: 50%; border: 1.5px solid #cbd5e1; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 8px rgba(0,0,0,0.12); transition: 0.2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'" title="Upload Photo">
                            <i data-lucide="camera" style="width: 15px; color: var(--store-orange);"></i>
                        </button>
                    `;
                    // Also update user storefront widget image if present
                    const widgetAvatar = document.querySelector('.user-widget img');
                    if (widgetAvatar) {
                        widgetAvatar.src = data.url + '?t=' + new Date().getTime();
                    }
                } else {
                    container.innerHTML = originalHTML;
                    Swal.showValidationMessage(data.message || 'Avatar upload failed.');
                }
            } catch (e) {
                Swal.showValidationMessage('Failed to upload avatar.');
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    </script>
</body>
</html>
