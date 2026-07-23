@extends('layouts.dashboard')

@section('content')
    @php
        $isOtherHOD = in_array(auth()->user()->role, ['Department Head', 'Dept Head HR', 'Head of Welfare', 'Auditor'])
            && (strcasecmp(auth()->user()->department ?? '', 'Stores') !== 0 && strcasecmp(auth()->user()->department ?? '', 'Store') !== 0);
        $dgCategories = \App\Models\Setting::get('dg_approval_categories', []);
        if (!is_array($dgCategories)) {
            $dgCategories = [];
        }
    @endphp
    <style>
        /* Hide standard top nav to maintain storefront custom header */
        .top-nav {
            display: none !important;
        }
        .content-body {
            padding: 0 !important;
        }
        @if(!$isOtherHOD)
        .sidebar {
            display: none !important;
        }
        .main-wrapper {
            margin-left: 0 !important;
            width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        body:not(.sidebar-collapsed) .main-wrapper {
            margin-left: 0 !important;
            width: 100% !important;
        }
        @endif
        :root {
            --font-display: 'Outfit', sans-serif;
            --font-sans: 'Outfit', sans-serif;

            /* Modern E-commerce Jumia-Inspired Color Palette */
            --store-orange: #881337;
            --store-orange-hover: #4c0519;
            --store-orange-light: rgba(136, 19, 55, 0.08);

            --store-indigo: #881337;
            --store-indigo-hover: #881337;
            --store-indigo-light: rgba(136, 19, 55, 0.08);

            --success-color: #881337;
            --warning-color: #881337;
            --danger-color: #ef4444;

            /* Theme overrides */
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
            max-width: 1750px;
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
            background: #881337;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 12px rgba(136, 19, 55, 0.2);
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
            box-shadow: 0 0 0 4px rgba(136, 19, 55, 0.12);
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
            transform: translateY(-1px);
        }

        .cart-toggle-btn {
            background: var(--store-orange-light);
            border: 1px solid rgba(136, 19, 55, 0.2);
            color: var(--store-orange);
            padding: 0 1.25rem;
            border-radius: 99px;
            height: 44px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .cart-toggle-btn:hover {
            background: var(--store-orange);
            color: white;
            box-shadow: 0 4px 12px rgba(136, 19, 55, 0.2);
            transform: translateY(-1px);
        }

        .cart-badge {
            background: var(--danger-color);
            color: white;
            font-size: 0.7rem;
            font-weight: 900;
            padding: 2px 7px;
            border-radius: 99px;
            border: 2px solid var(--bg-card);
            margin-left: 2px;
            animation: pulse 2s infinite;
        }

        .user-widget {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 4px 12px 4px 6px;
            border-radius: 99px;
            background: var(--bg-main);
            border: 1px solid var(--border-color);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--store-indigo);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.8rem;
            box-shadow: 0 2px 6px rgba(136, 19, 55, 0.2);
        }

        .user-info-name {
            font-size: 0.8rem;
            font-weight: 700;
            max-width: 100px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .logout-link {
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            padding: 4px;
            border-radius: 50%;
            transition: background 0.2s, color 0.2s;
        }

        .logout-link:hover {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        /* --- HERO BANNER --- */
        .store-hero {
            max-width: 1750px;
            margin: 1.5rem auto;
            padding: 0 2rem;
        }

        .hero-banner {
            background: #881337;
            border-radius: 24px;
            padding: 2.5rem 3rem;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-premium);
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 80% 20%, rgba(136, 19, 55, 0.15) 0%, transparent 50%);
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 600px;
        }

        .hero-badge {
            background: rgba(136, 19, 55, 0.2);
            border: 1px solid rgba(136, 19, 55, 0.3);
            color: var(--store-orange);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 99px;
            font-weight: 800;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
        }

        .hero-title {
            font-family: var(--font-display);
            font-size: 2.5rem;
            font-weight: 900;
            line-height: 1.1;
            margin: 0 0 0.75rem 0;
            letter-spacing: -0.04em;
        }

        .hero-desc {
            font-size: 1rem;
            color: rgba(255,255,255,0.7);
            font-weight: 500;
            margin: 0 0 1.5rem 0;
            line-height: 1.5;
        }

        .hero-actions-container {
            display: flex;
            gap: 1rem;
        }

        .hero-btn {
            background: var(--store-orange);
            color: white;
            border: none;
            padding: 0.8rem 1.8rem;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.85rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .hero-btn:hover {
            background: var(--store-orange-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(136, 19, 55, 0.3);
        }

        .hero-btn-secondary {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            color: white;
        }

        .hero-btn-secondary:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-2px);
        }

        .hero-art {
            position: absolute;
            right: 5%;
            top: 50%;
            transform: translateY(-50%);
            width: 320px;
            height: auto;
            opacity: 0.85;
            z-index: 1;
            pointer-events: none;
            animation: float 6s ease-in-out infinite;
        }

        /* --- MAIN STOREFRONT LAYOUT --- */
        .store-layout {
            max-width: 1750px;
            margin: 0 auto;
            padding: 0 2rem 3rem 2rem;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            align-items: start;
        }

        /* --- LEFT SIDEBAR: CATEGORIES --- */
        .store-sidebar {
            position: sticky;
            top: 90px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: var(--shadow-premium);
        }

        .sidebar-title {
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 800;
            margin: 0 0 1rem 0;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-main);
        }

        .category-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .category-item {
            padding: 0.75rem 1rem;
            border-radius: 12px;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease;
            background: transparent;
            border: 1px solid transparent;
        }

        .category-item:hover {
            color: var(--text-main);
            background: var(--bg-main);
        }

        .category-item.active {
            background: var(--store-orange-light);
            border-color: rgba(136, 19, 55, 0.15);
            color: var(--store-orange);
            font-weight: 700;
        }

        .category-item-label {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .category-count {
            background: var(--bg-main);
            color: var(--text-muted);
            font-size: 0.7rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .category-item.active .category-count {
            background: var(--store-orange);
            color: white;
        }

        /* --- MIDDLE CONTENT: PRODUCTS GRID --- */
        .store-products {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .products-grid-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1rem 1.5rem;
            box-shadow: var(--shadow-premium);
        }

        .results-count {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-muted);
        }

        .results-count span {
            color: var(--text-main);
            font-weight: 800;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 1.5rem;
        }

        /* --- PRODUCT CARD --- */
        .product-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-premium);
        }

        .product-card:hover {
            transform: translateY(-5px);
            border-color: var(--store-orange);
            box-shadow: var(--shadow-hover);
        }

        .product-image-container {
            height: 140px;
            background: rgba(136, 19, 55, 0.05);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: all 0.3s;
        }

        .product-card:hover .product-image-container {
            background: rgba(136, 19, 55, 0.08);
        }

        .product-icon-box {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: var(--bg-card);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--store-indigo);
            box-shadow: var(--shadow-premium);
            transition: transform 0.3s;
        }

        .product-card:hover .product-icon-box {
            transform: scale(1.1) rotate(5deg);
            color: var(--store-orange);
        }

        .product-body {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .product-cat-tag {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--store-indigo);
            background: var(--store-indigo-light);
            padding: 2px 8px;
            border-radius: 6px;
            align-self: flex-start;
            margin-bottom: 0.5rem;
        }

        .product-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0 0 0.5rem 0;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 2.7em; /* Consistent height for grid alignment */
        }

        .product-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
            margin-bottom: 1rem;
        }

        .product-unit {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .product-stock {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            border-radius: 99px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .stock-in {
            background: rgba(136, 19, 55, 0.1);
            color: var(--success-color);
        }

        .stock-low {
            background: rgba(136, 19, 55, 0.1);
            color: var(--warning-color);
        }

        .stock-out {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .product-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            overflow: hidden;
            background: var(--bg-main);
        }

        .qty-btn {
            background: transparent;
            border: none;
            width: 28px;
            height: 32px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            transition: background 0.15s;
        }

        .qty-btn:hover {
            background: var(--border-color);
        }

        .qty-val {
            width: 32px;
            text-align: center;
            border: none;
            background: transparent;
            font-family: inherit;
            font-weight: 700;
            font-size: 0.8rem;
            color: var(--text-main);
            outline: none;
        }

        .add-cart-btn {
            flex: 1;
            background: var(--store-orange);
            color: white;
            border: none;
            border-radius: 10px;
            height: 35px;
            font-weight: 800;
            font-size: 0.8rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .add-cart-btn:hover {
            background: var(--store-orange-hover);
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.25);
        }

        .add-cart-btn.added {
            background: var(--success-color);
            box-shadow: 0 4px 10px rgba(136, 19, 55, 0.25);
        }

        /* --- RIGHT SIDEBAR: CART & CHECKOUT --- */
        .store-cart-sidebar {
            position: sticky;
            top: 90px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: var(--shadow-premium);
            max-height: calc(100vh - 120px);
            overflow-y: auto;
        }

        .cart-title {
            font-family: var(--font-display);
            font-size: 1.1rem;
            font-weight: 800;
            margin: 0 0 1.25rem 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--text-main);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.75rem;
        }

        .clear-cart-btn {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--danger-color);
            background: rgba(239, 68, 68, 0.08);
            border: none;
            padding: 4px 10px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }

        .clear-cart-btn:hover {
            background: var(--danger-color);
            color: white;
        }

        .cart-items-container {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            max-height: 220px;
            overflow-y: auto;
            padding-right: 2px;
        }

        .cart-empty-state {
            padding: 2rem 1rem;
            text-align: center;
            color: var(--text-muted);
            border: 2px dashed var(--border-color);
            border-radius: 14px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .cart-empty-state i {
            display: block;
            margin: 0 auto 0.5rem auto;
            opacity: 0.3;
            color: var(--store-orange);
        }

        .cart-item-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.6rem 0.75rem;
            background: var(--bg-main);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            position: relative;
            animation: slideIn 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.3;
            margin-bottom: 4px;
            padding-right: 1.5rem;
        }

        .cart-item-meta {
            font-size: 0.68rem;
            color: var(--text-muted);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .cart-item-qty-input {
            width: 44px;
            padding: 2px 4px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: var(--bg-card);
            color: var(--text-main);
            font-family: inherit;
            font-weight: 700;
            font-size: 0.75rem;
            text-align: center;
            margin: 0 4px;
        }

        .cart-item-delete {
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .cart-item-delete:hover {
            color: var(--danger-color);
            background: rgba(239, 68, 68, 0.08);
        }

        .cart-item-remarks {
            width: 100%;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: var(--bg-card);
            color: var(--text-main);
            font-family: inherit;
            font-size: 0.65rem;
            padding: 4px 6px;
            margin-top: 6px;
            outline: none;
            resize: none;
        }

        .cart-item-remarks:focus {
            border-color: var(--store-orange);
        }

        /* --- CHECKOUT FORM --- */
        .checkout-section-title {
            font-family: var(--font-display);
            font-size: 0.85rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-muted);
            margin: 1.5rem 0 0.75rem 0;
            display: flex;
            align-items: center;
            gap: 6px;
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
        }

        .form-grid {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .form-label {
            display:block;
            font-size: 0.7rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 4px;
        }

        .form-input {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            font-family: inherit;
            background: var(--bg-main);
            color: var(--text-main);
            outline: none;
            box-sizing: border-box;
            transition: all 0.2s;
        }

        .form-input:focus {
            border-color: var(--store-orange);
            background: var(--bg-card);
            box-shadow: 0 0 0 3px rgba(136, 19, 55, 0.08);
        }

        .checkout-btn {
            width: 100%;
            padding: 0.9rem;
            background: #881337;
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 16px rgba(136, 19, 55, 0.2);
            transition: all 0.25s ease;
            margin-top: 1.5rem;
        }

        .checkout-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(136, 19, 55, 0.35);
        }

        .checkout-btn:disabled {
            background: var(--border-color);
            color: var(--text-muted);
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        /* --- HISTORY TIMELINE PANEL --- */
        .history-container {
            max-width: 1440px;
            margin: 1.5rem auto 3rem auto;
            padding: 0 2rem;
        }

        .history-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: var(--shadow-premium);
        }

        .history-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .history-title {
            font-family: var(--font-display);
            font-size: 1.25rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .refresh-btn {
            background: var(--bg-main);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.8rem;
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
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            background: var(--bg-card);
            transition: all 0.2s;
        }

        .history-item-box:hover {
            box-shadow: var(--shadow-premium);
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
        }

        .history-meta-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 600;
            margin-top: 6px;
        }

        .history-status-pills {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* --- VISUAL E-COMMERCE TRACKER TIMELINE --- */
        .order-tracker {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            margin: 1.5rem 0;
            padding: 0 1rem;
        }

        .order-tracker::before {
            content: '';
            position: absolute;
            top: 14px;
            left: 2rem;
            right: 2rem;
            height: 4px;
            background: var(--border-color);
            z-index: 1;
        }

        .tracker-progress-line {
            position: absolute;
            top: 14px;
            left: 2rem;
            height: 4px;
            background: var(--success-color);
            z-index: 2;
            transition: width 0.5s ease;
        }

        .tracker-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 3;
            flex: 1;
            text-align: center;
        }

        .tracker-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--bg-card);
            border: 3px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-weight: 800;
            font-size: 0.75rem;
            transition: all 0.3s ease;
        }

        .tracker-step.completed .tracker-dot {
            background: var(--success-color);
            border-color: var(--success-color);
            color: white;
            box-shadow: 0 0 10px rgba(136, 19, 55, 0.4);
        }

        .tracker-step.active .tracker-dot {
            background: var(--bg-card);
            border-color: var(--store-orange);
            color: var(--store-orange);
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.4);
        }

        .tracker-step.declined .tracker-dot {
            background: var(--danger-color);
            border-color: var(--danger-color);
            color: white;
        }

        .tracker-label {
            font-size: 0.72rem;
            font-weight: 800;
            color: var(--text-muted);
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .tracker-step.completed .tracker-label,
        .tracker-step.active .tracker-label {
            color: var(--text-main);
        }

        .tracker-step.declined .tracker-label {
            color: var(--danger-color);
        }

        .history-items-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .history-item-tag {
            background: var(--bg-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .history-notes-box {
            margin-top: 1rem;
            font-size: 0.78rem;
            background: rgba(136, 19, 55, 0.05);
            border: 1px dashed rgba(136, 19, 55, 0.3);
            border-radius: 12px;
            padding: 10px 14px;
            color: #b45309;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        /* --- UTILITIES & ANIMATIONS --- */
        @keyframes borderGlowPulse {
            0% {
                border-color: var(--border-color);
                box-shadow: var(--shadow-premium);
            }
            30% {
                border-color: var(--store-orange);
                box-shadow: 0 0 0 6px var(--store-orange-light), 0 20px 40px -15px rgba(136, 19, 55, 0.25);
            }
            70% {
                border-color: var(--store-orange);
                box-shadow: 0 0 0 6px var(--store-orange-light), 0 20px 40px -15px rgba(136, 19, 55, 0.25);
            }
            100% {
                border-color: var(--border-color);
                box-shadow: var(--shadow-premium);
            }
        }
        .history-card.highlight-pulse {
            animation: borderGlowPulse 2.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Custom SweetAlert Premium Styling */
        .premium-swal-popup {
            border-radius: 24px !important;
            background: var(--bg-card) !important;
            color: var(--text-main) !important;
            border: 1px solid var(--border-color) !important;
            font-family: var(--font-sans) !important;
            box-shadow: var(--shadow-hover) !important;
        }
        .premium-swal-title {
            font-family: var(--font-display) !important;
            color: var(--text-main) !important;
            font-weight: 800 !important;
        }
        .premium-swal-html {
            color: var(--text-main) !important;
            font-family: var(--font-sans) !important;
        }

        @keyframes float {
            0% { transform: translateY(-50%) translateY(0px); }
            50% { transform: translateY(-50%) translateY(-10px); }
            100% { transform: translateY(-50%) translateY(0px); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.08); }
            100% { transform: scale(1); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Custom Scrollbar for sidebars */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 99px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }

        /* --- RESPONSIVE STYLE --- */
        @media (max-width: 1200px) {
            .store-layout {
                grid-template-columns: 240px 1fr;
            }
            .store-cart-sidebar {
                position: fixed;
                right: -420px;
                top: 0;
                bottom: 0;
                width: 360px;
                z-index: 2000;
                max-height: none;
                height: 100vh;
                border-radius: 0;
                transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .store-cart-sidebar.open {
                right: 0;
            }
            .cart-close-btn {
                display: flex !important;
            }
        }

        @media (max-width: 900px) {
            .store-layout {
                grid-template-columns: 1fr;
            }
            .store-sidebar {
                position: static;
                margin-bottom: 1.5rem;
            }
            .hero-banner {
                flex-direction: column;
                padding: 2rem;
                text-align: center;
            }
            .hero-content {
                max-width: 100%;
            }
            .hero-art {
                display: none;
            }
            .order-tracker::before, .tracker-progress-line {
                display: none;
            }
            .order-tracker {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            .tracker-step {
                flex-direction: row;
                gap: 12px;
                text-align: left;
            }
        }

        /* Action Buttons Hover & Interactions */
        .action-btn-followup:hover {
            background: var(--warning-color) !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(136, 19, 55, 0.2);
            transform: translateY(-1px);
        }
        .action-btn-collect:hover {
            background: var(--success-color) !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(136, 19, 55, 0.2);
            transform: translateY(-1px);
        }
        .action-btn-followup:active, .action-btn-collect:active {
            transform: translateY(1px);
        }

        /* Swal Profile Styles */
        .glass-monolith-popup {
            border-radius: 32px !important;
            padding: 1.75rem 2rem !important;
            box-shadow: 0 40px 100px -20px rgba(0,0,0,0.15) !important;
            border: 1px solid rgba(255,255,255,0.8) !important;
            font-family: 'Outfit', sans-serif !important;
        }
        .premium-swal-btn {
            height: 48px !important;
            padding: 0 30px !important;
            border-radius: 14px !important;
            font-weight: 800 !important;
            font-size: 0.85rem !important;
            letter-spacing: 0.02em !important;
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.2) !important;
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
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.06) !important;
        }
        .swal-field-input[readonly] {
            opacity: 0.65;
            cursor: not-allowed;
            background: #f1f5f9 !important;
            border-color: #e2e8f0 !important;
        }
        .swal-field-wrapper:focus-within .swal-field-icon {
            color: var(--store-orange);
        }

        /* Sticky Cart Bar */
        .sticky-cart-bar {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%) translateY(150%);
            width: calc(100% - 4rem);
            max-width: 600px;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border: 1.5px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
            border-radius: 20px;
            padding: 1rem 1.5rem;
            z-index: 999;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            opacity: 0;
            pointer-events: none;
        }

        .sticky-cart-bar.active {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        .sticky-cart-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
        }

        .sticky-cart-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: white;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .sticky-cart-info strong {
            color: #ffffff;
            font-weight: 800;
            font-size: 1.15rem;
        }

        .cart-icon-pulse {
            color: var(--store-orange);
            width: 20px;
            height: 20px;
            animation: pulse-icon 2s infinite;
        }

        .sticky-cart-btn {
            background: #881337;
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.88rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(136, 19, 55, 0.3);
            transition: all 0.25s ease;
        }

        .sticky-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(136, 19, 55, 0.45);
        }

        @keyframes pulse-icon {
            0% { transform: scale(1); }
            50% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>

    <!-- --- AUTOMATED LOGOUT FORM --- -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- --- STORE HEADER --- -->
    <header class="store-header">
        <div class="header-container">
            <!-- Brand -->
            <a href="{{ route('requisitions.index') }}" class="store-brand">
                <div class="brand-logo-container" style="background: transparent; box-shadow: none; width: 56px; height: 56px;">
                    <img src="{{ asset('img/download-1.webp') }}" alt="Logo" style="width: 56px; height: 56px; object-fit: contain; border-radius: 12px;">
                </div>
                <div>
                    <div class="brand-name">NACOC</div>
                    <div class="brand-subtitle">Stores Inventory Management System<span style="color:#881337;">(NSIMs)</span></div>
                </div>
            </a>

            <!-- Search Bar -->
            <div class="store-search-bar">
                <input type="text" id="storefront-search" class="store-search-input" placeholder="Search available items, desk supplies, electronics...">
                <i data-lucide="search" class="store-search-icon"></i>
            </div>

            <!-- Header Actions -->
            <div class="header-actions">
                @if(auth()->user()->role !== 'Requisitioner')
                <a href="{{ route('dashboard') }}" class="action-btn" title="Admin Dashboard">
                    <i data-lucide="layout-dashboard" style="width: 18px; color: var(--store-indigo);"></i>
                </a>
                @endif



                <a href="{{ route('requisitions.history') }}" class="action-btn" title="View Requisition History" style="text-decoration: none;">
                    <i data-lucide="clock" style="width: 18px;"></i>
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

    <!-- --- HERO BANNER --- -->
    <section class="store-hero">
        <div class="hero-banner">
            <div class="hero-content">
                
                <h2 class="hero-title">Request Store Supplies</h2>
                <p class="hero-desc">Welcome to the NSIMs! Here, all your store requisitions are made easier.</p>
                <div class="hero-actions-container">
                    <button class="hero-btn" id="cart-nav-btn" style="position: relative; display: inline-flex; align-items: center; gap: 8px;">
                        <i data-lucide="shopping-cart" style="width: 16px;"></i>
                        <span>My Request</span>
                        <span class="cart-badge" id="cart-header-count" style="border: none; position: static;">0</span>
                    </button>
                    <a href="{{ route('requisitions.history') }}" class="hero-btn hero-btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                        <i data-lucide="clock" style="width: 16px;"></i> Track Items
                    </a>
                </div>
            </div>
            <!-- Interactive Floating Art (fallback illustration via Lucide graphics) -->
            <div class="hero-art">
                <i data-lucide="package-open" style="width: 260px; height: 260px; color: rgba(136, 19, 55, 0.15); stroke-width: 1;"></i>
            </div>
        </div>
    </section>

    @if($isDepartmentDisabled)
    <div style="max-width: 1400px; margin: 2rem auto 0; padding: 1.5rem 2rem; background: #fef2f2; border: 2px dashed #fca5a5; border-radius: 24px; display: flex; align-items: center; gap: 1.25rem; box-shadow: 0 4px 20px rgba(220, 38, 38, 0.05); animation: slideIn 0.4s ease;">
        <div style="width: 52px; height: 52px; background: #fee2e2; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0;">
            <i data-lucide="shield-alert" style="width: 26px; height: 26px;"></i>
        </div>
        <div>
            <h4 style="font-size: 1.05rem; font-weight: 850; color: #991b1b; margin: 0;">Department Requisition Disabled</h4>
            <p style="font-size: 0.85rem; color: #b91c1c; font-weight: 600; margin: 4px 0 0;">
                Strategic Notice: Requisition access for the <b>{{ auth()->user()->department ?? 'unassigned' }}</b> department has been temporarily suspended by the Head of Stores.
            </p>
        </div>
    </div>
    @endif

    <div id="shop-grid-start"></div>

    <!-- --- MAIN STOREFRONT LAYOUT --- -->
    <main class="store-layout">

        <!-- --- LEFT SIDEBAR: CATEGORIES --- -->
        <aside class="store-sidebar">
            <h3 class="sidebar-title">
                <i data-lucide="layers" style="width: 18px; color: var(--store-orange);"></i>
                Browse Categories
            </h3>

            @php
                // Count items per category locally in PHP to present accurate stats
                $categoryCounts = ['all' => count($availableItems)];
                foreach($availableItems as $item) {
                    $cat = $item->ledge_category ?: 'other';
                    $categoryCounts[$cat] = ($categoryCounts[$cat] ?? 0) + 1;
                }
            @endphp

            <ul class="category-list">
                <li class="category-item active" data-category-id="all">
                    <span class="category-item-label">
                        <i data-lucide="grid" style="width: 16px;"></i> All Items
                    </span>
                    <span class="category-count">{{ $categoryCounts['all'] }}</span>
                </li>
                @foreach($ledgeMap as $key => $label)
                    @if(isset($categoryCounts[$key]))
                    <li class="category-item" data-category-id="{{ $key }}">
                        <span class="category-item-label">
                            <i data-lucide="folder" style="width: 16px;"></i> {{ $label }}
                        </span>
                        <span class="category-count">{{ $categoryCounts[$key] }}</span>
                    </li>
                    @endif
                @endforeach
                @if(isset($categoryCounts['other']))
                <li class="category-item" data-category-id="other">
                    <span class="category-item-label">
                        <i data-lucide="box" style="width: 16px;"></i> Other Supplies
                    </span>
                    <span class="category-count">{{ $categoryCounts['other'] }}</span>
                </li>
                @endif
            </ul>
        </aside>

        <!-- --- MIDDLE CONTENT: PRODUCTS CATALOG --- -->
        <section class="store-products">
            <div class="products-grid-header">
                <div class="results-count">
                    Showing <span id="displayed-products-count">{{ count($availableItems) }}</span> available items
                </div>
                <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700;">
                    Click <b style="color:var(--store-orange)">+ Add to Request</b> to build checkout list
                </div>
            </div>

            <div class="products-grid">
                @forelse($availableItems as $idx => $item)
                    @php
                        $catCode = $item->ledge_category ?: 'other';
                        $catName = $ledgeMap[$item->ledge_category] ?? 'Other';
                        $stockVal = (float) $item->total_stock;
                        $stockStatus = 'stock-in';
                        $stockLabel = 'In Stock';

                        if ($stockVal <= 0) {
                            $stockStatus = 'stock-out';
                            $stockLabel = 'Out of Stock';
                        } elseif ($stockVal <= 10) {
                            $stockStatus = 'stock-low';
                            $stockLabel = 'Low Stock';
                        }
                    @endphp

                    <div class="product-card-wrapper"
                         data-category="{{ $catCode }}"
                         data-title="{{ strtolower($item->description) }}"
                         style="transition: all 0.25s ease;">

                        <div class="product-card">
                            <div class="product-image-container">
                                <div class="product-icon-box">
                                    <i data-lucide="package" style="width: 28px; height: 28px;"></i>
                                </div>
                            </div>

                            <div class="product-body">
                                <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-bottom: 0.5rem;">
                                    <span class="product-cat-tag" style="margin-bottom: 0;">{{ $catName }}</span>
                                    @if(in_array($item->ledge_category, $dgCategories))
                                        <span style="font-size: 0.62rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #9f1239; background: rgba(139, 92, 246, 0.08); padding: 2px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; border: 1px solid rgba(139, 92, 246, 0.18);" title="This item requires Director General's approval prior to collection.">
                                            <i data-lucide="shield-alert" style="width: 11px; height: 11px; color: #9f1239;"></i> Needs DG's Approval
                                        </span>
                                    @endif
                                </div>
                                <h4 class="product-title" title="{{ $item->description }}">{{ $item->description }}</h4>

                                <div class="product-meta-row">
                                    <span class="product-unit">
                                        <i data-lucide="tag" style="width: 12px; color: var(--store-orange);"></i> {{ $item->unit ?: 'pcs' }}
                                    </span>
                                    <span class="product-stock {{ $stockStatus }}">
                                        {{ $stockLabel }}
                                    </span>
                                </div>

                                <div class="product-actions">
                                    @if($isDepartmentDisabled)
                                        <button class="add-cart-btn" style="background:var(--border-color); color:var(--text-muted); cursor:not-allowed;" disabled>
                                            Disabled
                                        </button>
                                    @elseif($stockVal > 0)
                                        <div class="qty-controls">
                                            <button class="qty-btn" onclick="adjustProductQty({{ $idx }}, -1)">
                                                <i data-lucide="minus" style="width: 12px;"></i>
                                            </button>
                                            <input type="number" id="qty-input-{{ $idx }}" class="qty-val" value="1" min="1" max="{{ $stockVal }}" readonly>
                                            <button class="qty-btn" onclick="adjustProductQty({{ $idx }}, 1)">
                                                <i data-lucide="plus" style="width: 12px;"></i>
                                            </button>
                                        </div>

                                        <button class="add-cart-btn" id="add-btn-{{ $idx }}"
                                                onclick="addToCart('{{ addslashes($item->description) }}', '{{ $catCode }}', '{{ addslashes($item->unit) }}', {{ $stockVal }}, {{ $idx }})">
                                            <i data-lucide="plus" style="width: 14px;"></i> Add
                                        </button>
                                    @else
                                        <button class="add-cart-btn" style="background:var(--border-color); color:var(--text-muted); cursor:not-allowed;" disabled>
                                            Unavailable
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1/-1; background: var(--bg-card); border: 2px dashed var(--border-color); border-radius: 20px; padding: 4rem 2rem; text-align: center; color: var(--text-muted);">
                        <i data-lucide="inbox" style="width: 48px; height: 48px; margin: 0 auto 1rem auto; opacity: 0.3; color: var(--store-orange);"></i>
                        <h4 style="font-size: 1.1rem; color: var(--text-main); margin-bottom: 0.5rem;">No items currently in stock</h4>
                        <p style="font-size: 0.85rem; max-width: 400px; margin: 0 auto;">Our central store batches are undergoing restocking audits. Check back shortly.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </main>

    <!-- Autocomplete dropdown container (legacy support) -->
    <div id="itemAutocomplete" style="display:none;position:fixed;background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.12);z-index:9999;max-height:220px;overflow-y:auto;min-width:260px;"></div>

    <!-- Sticky Cart Nav Container -->
    <div id="sticky-cart-bar" class="sticky-cart-bar">
        <div class="sticky-cart-container">
            <div class="sticky-cart-info">
                <i data-lucide="shopping-bag" class="cart-icon-pulse"></i>
                <span>You have selected <strong id="sticky-cart-count">0</strong> items</span>
            </div>
            <button id="sticky-cart-btn" class="sticky-cart-btn">
                <span>View My Request</span>
                <i data-lucide="arrow-right" style="width: 16px;"></i>
            </button>
        </div>
    </div>

    <script>
        // E-COMMERCE CORE STATE & LOGIC
        const availableItems = @json($availableItems);
        const ledgeMap = @json($ledgeMap);
        let cart = [];

        let currentCategoryId = 'all';
        let searchQuery = '';

        function filterCatalog() {
            const query = searchQuery.toLowerCase().trim();
            const cards = document.querySelectorAll('.product-card-wrapper');
            let visibleCount = 0;

            cards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');
                const cardTitle = card.getAttribute('data-title') || '';

                const matchesCategory = (currentCategoryId === 'all') || (cardCategory === currentCategoryId);
                const matchesSearch = !query || cardTitle.includes(query);

                if (matchesCategory && matchesSearch) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const displayedCountEl = document.getElementById('displayed-products-count');
            if (displayedCountEl) {
                displayedCountEl.textContent = visibleCount;
            }
        }

        // Load cart from LocalStorage on page mount to provide gold-standard durability
        function loadCartFromStorage() {
            const savedCart = localStorage.getItem('store_checkout_cart');
            if (savedCart) {
                try {
                    cart = JSON.parse(savedCart);
                    updateCartUI();
                } catch(e) {
                    cart = [];
                }
            }
        }

        function saveCartToStorage() {
            localStorage.setItem('store_checkout_cart', JSON.stringify(cart));
        }

        // Product Card Quantities Adjustment
        function adjustProductQty(idx, amt) {
            const input = document.getElementById(`qty-input-${idx}`);
            const maxVal = parseFloat(input.getAttribute('max')) || 999;
            let current = parseFloat(input.value) || 1;

            current += amt;
            if (current < 1) current = 1;
            if (current > maxVal) current = maxVal;

            input.value = current;
        }

        // Cart Actions
        function addToCart(desc, cat, unit, maxStock, idx) {
            const qtyInput = document.getElementById(`qty-input-${idx}`);
            const qtyToAdd = parseFloat(qtyInput.value) || 1;

            // Check if product already exists in checkout cart
            const existingItem = cart.find(i => i.description.trim().toLowerCase() === desc.trim().toLowerCase());

            if (existingItem) {
                const totalNewQty = existingItem.quantity_requested + qtyToAdd;
                if (totalNewQty > maxStock) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock Limit Exceeded',
                        text: `You have already added ${existingItem.quantity_requested} units of this item. Max available is ${maxStock}.`,
                        confirmButtonColor: 'var(--store-orange)'
                    });
                    return;
                }
                existingItem.quantity_requested = totalNewQty;
            } else {
                cart.push({
                    description: desc,
                    category: cat,
                    unit: unit || 'pcs',
                    total_stock: maxStock,
                    quantity_requested: qtyToAdd,
                    remarks: ''
                });
            }

            // Animate Add to Cart Button Feedback
            const addBtn = document.getElementById(`add-btn-${idx}`);
            const originalHTML = addBtn.innerHTML;
            addBtn.classList.add('added');
            addBtn.innerHTML = '<i data-lucide="check" style="width: 14px;"></i> Added';
            lucide.createIcons();

            setTimeout(() => {
                addBtn.classList.remove('added');
                addBtn.innerHTML = originalHTML;
                lucide.createIcons();
            }, 1200);

            // Re-render & persist
            saveCartToStorage();
            updateCartUI();
        }

        function updateCartUI() {
            const cartCountBadge = document.getElementById('cart-header-count');
            if (cartCountBadge) cartCountBadge.textContent = cart.length;

            const stickyCount = document.getElementById('sticky-cart-count');
            const stickyBar = document.getElementById('sticky-cart-bar');
            if (stickyCount && stickyBar) {
                stickyCount.textContent = cart.length;
                if (cart.length > 0) {
                    stickyBar.classList.add('active');
                } else {
                    stickyBar.classList.remove('active');
                }
            }
        }

        // Event Listeners on Mount
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            loadCartFromStorage();

            // Storefront Search filter binding
            const searchInput = document.getElementById('storefront-search');
            searchInput.addEventListener('input', (e) => {
                searchQuery = e.target.value.trim();
                filterCatalog();
            });

            // Category item selection filter binding
            const categoryItems = document.querySelectorAll('.category-item');
            categoryItems.forEach(item => {
                item.addEventListener('click', () => {
                    categoryItems.forEach(el => el.classList.remove('active'));
                    item.classList.add('active');
                    currentCategoryId = item.getAttribute('data-category-id');
                    filterCatalog();
                });
            });



            // Cart Redirect to checkout page
            const handleCartCheckout = (e) => {
                e.preventDefault();
                const user = {
                    name: '{{ addslashes(auth()->user()->name) }}',
                    username: '{{ addslashes(auth()->user()->username) }}',
                    phone: '{{ auth()->user()->phone ?? "" }}',
                    role: '{{ auth()->user()->role ?? "" }}',
                    service_number: '{{ auth()->user()->service_number ?? "" }}'
                };
                if (!user.name || user.name === user.username || !user.phone || !user.role || !user.service_number) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Profile Incomplete',
                        text: 'You must complete your profile details (Full Name, Phone, Service Number, and Professional Role) before making a request.',
                        confirmButtonText: 'Complete Profile Now',
                        confirmButtonColor: 'var(--store-orange)',
                        showCancelButton: true,
                        cancelButtonText: 'Later'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            openProfileCompletionModal();
                        }
                    });
                    return;
                }
                window.location.href = "{{ route('requisitions.checkout') }}";
            };

            const cartNavBtn = document.getElementById('cart-nav-btn');
            if (cartNavBtn) {
                cartNavBtn.addEventListener('click', handleCartCheckout);
            }
            const stickyCartBtn = document.getElementById('sticky-cart-btn');
            if (stickyCartBtn) {
                stickyCartBtn.addEventListener('click', handleCartCheckout);
            }

            @if(session('show_profile_modal') || empty(auth()->user()->name) || auth()->user()->name === auth()->user()->username || empty(auth()->user()->phone) || empty(auth()->user()->role) || empty(auth()->user()->service_number))
                setTimeout(() => {
                    openProfileCompletionModal();
                }, 800);
            @endif
        });

        function openProfileCompletionModal() {
            const user = {
                name: '{{ addslashes(auth()->user()->name) }}',
                username: '{{ addslashes(auth()->user()->username) }}',
                email: '{{ auth()->user()->email ?? "" }}',
                phone: '{{ auth()->user()->phone ?? "" }}',
                department: '{{ auth()->user()->department ?? "" }}',
                role: '{{ auth()->user()->role ?? "" }}',
                service_number: '{{ auth()->user()->service_number ?? "" }}',
                avatar: '{{ auth()->user()->avatar ? Storage::url(auth()->user()->avatar) : "" }}'
            };

            const displayRole = user.role || 'Requisitioner';

            Swal.fire({
                title: `
                    <div style="display: flex; align-items: center; gap: 15px; text-align: left; width: 100%;">
                        <div style="width: 48px; height: 48px; background: rgba(136, 19, 55, 0.1); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #881337;">
                            <i data-lucide="user-check"></i>
                        </div>
                        <div>
                            <div style="font-size: 1.25rem; font-weight: 950; color: #0f172a;">Complete Profile</div>
                            <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; margin-top: 2px; text-transform: uppercase;">Update contact and verification records</div>
                        </div>
                    </div>
                `,
                html: `
                    <div style="text-align: left; padding: 1rem 0.5rem; font-family: 'Outfit', sans-serif;">
                        
                        <!-- Avatar Upload Area -->
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 1.25rem;">
                            <div style="position: relative; width: 100px; height: 100px;" id="swal-avatar-container">
                                ${user.avatar ? `
                                    <img src="${user.avatar}" id="swal-avatar-preview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                                ` : `
                                    <div id="swal-avatar-placeholder" style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--store-indigo) 0%, #4c0519 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; font-weight: 950; color: white; border: 4px solid white; box-shadow: 0 10px 20px rgba(136,19,55,0.25);">
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
                                    <label style="display: block; font-size: 0.68rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.05em;">Full Name *</label>
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
                                    <label style="display: block; font-size: 0.68rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.05em;">Phone Number *</label>
                                    <div class="swal-field-wrapper">
                                        <div class="swal-field-icon"><i data-lucide="phone"></i></div>
                                        <input type="text" id="swal-prof-phone" value="${user.phone}" class="swal-field-input" placeholder="e.g. +233..." required>
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
                                    <label style="display: block; font-size: 0.68rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.05em;">Service Number *</label>
                                    <div class="swal-field-wrapper">
                                        <div class="swal-field-icon"><i data-lucide="hash"></i></div>
                                        <input type="text" id="swal-prof-service" value="${user.service_number}" class="swal-field-input" placeholder="e.g. SN-8942" required>
                                    </div>
                                </div>
                            </div>

                            <div class="swal-input-group">
                                <label style="display: block; font-size: 0.68rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.05em;">Professional Role *</label>
                                <div class="swal-field-wrapper">
                                    <div class="swal-field-icon"><i data-lucide="shield"></i></div>
                                    <input type="text" id="swal-prof-role" value="${displayRole}" class="swal-field-input" placeholder="e.g. Officer" required>
                                </div>
                            </div>
                        </form>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Save Profile Settings',
                cancelButtonText: 'Close',
                confirmButtonColor: '#881337',
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
                    const name = document.getElementById('swal-prof-name').value.trim();
                    const email = document.getElementById('swal-prof-email').value.trim();
                    const phone = document.getElementById('swal-prof-phone').value.trim();
                    const department = document.getElementById('swal-prof-dept').value.trim();
                    const service_number = document.getElementById('swal-prof-service').value.trim();
                    const role = document.getElementById('swal-prof-role').value.trim();

                    if (!name || name === user.username) {
                        Swal.showValidationMessage('Full Name is required and must not be username');
                        return false;
                    }
                    if (!phone) {
                        Swal.showValidationMessage('Phone Number is required');
                        return false;
                    }
                    if (!service_number) {
                        Swal.showValidationMessage('Service Number is required');
                        return false;
                    }
                    if (!role) {
                        Swal.showValidationMessage('Professional Role is required');
                        return false;
                    }

                    try {
                        const res = await fetch("{{ route('settings.update') }}", {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json', 
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ name, email, phone, department, role, service_number })
                        });
                        const text = await res.text();
                        let data;
                        try {
                            data = JSON.parse(text);
                        } catch (err) {
                            console.error('Non-JSON response:', text);
                            const match = text.match(/<title>(.*?)<\/title>/i);
                            const pageTitle = match ? match[1] : 'Unknown Server Error';
                            Swal.showValidationMessage('Server returned error: ' + pageTitle + ' (Status ' + res.status + ')');
                            return false;
                        }
                        
                        if (!res.ok || !data.success) {
                            Swal.showValidationMessage(data.message || 'Profile sync failed.');
                            return false;
                        }
                        return data;
                    } catch (e) {
                        console.error('Profile Save Error:', e);
                        Swal.showValidationMessage('Network node transmission error: ' + e.message);
                        return false;
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Profile Updated',
                        text: 'Your details have been successfully synchronized.',
                        icon: 'success',
                        confirmButtonColor: '#881337'
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
                        <i data-lucide="loader-2" class="spin" style="width: 24px; color: #881337;"></i>
                    </div>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons();

                const res = await fetch("{{ route('settings.avatar') }}", {
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
@endsection
