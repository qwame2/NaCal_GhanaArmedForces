@extends('layouts.dashboard')

@section('content')
    @php
        $isOtherHOD = in_array(auth()->user()->role, ['Department Head', 'Dept Head HR', 'Head of Welfare'])
            && (strcasecmp(auth()->user()->department ?? '', 'Stores') !== 0 && strcasecmp(auth()->user()->department ?? '', 'Store') !== 0);
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
            --header-blur: rgba(255, 255, 255, 0.8);
        }



        body {
            font-family: var(--font-sans);
            background: var(--bg-main);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            transition: background 0.3s, color 0.3s;
        }

        .store-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: var(--header-blur);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 2rem;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
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
        }

        .brand-subtitle {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--store-orange);
        }

        .back-to-shop-btn {
            background: var(--bg-main);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 0.6rem 1.2rem;
            border-radius: 99px;
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .back-to-shop-btn:hover {
            background: var(--border-color);
            transform: translateX(-2px);
        }

        .checkout-layout {
            max-width: 1200px;
            margin: 2rem auto 4rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 2rem;
            align-items: start;
        }

        .checkout-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: var(--shadow-premium);
        }

        .checkout-title {
            font-family: var(--font-display);
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0 0 1.5rem 0;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .checkout-title span {
            color: var(--store-orange);
        }

        /* --- EMPTY STATE --- */
        .empty-cart-state {
            grid-column: 1 / -1;
            background: var(--bg-card);
            border: 2px dashed var(--border-color);
            border-radius: 24px;
            padding: 5rem 2rem;
            text-align: center;
            box-shadow: var(--shadow-premium);
            max-width: 600px;
            margin: 4rem auto;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: var(--store-orange-light);
            color: var(--store-orange);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
        }

        .empty-title {
            font-family: var(--font-display);
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .empty-desc {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 2rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .empty-btn {
            background: var(--store-orange);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.9rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
            transition: all 0.2s;
        }

        .empty-btn:hover {
            background: var(--store-orange-hover);
            transform: translateY(-1px);
        }

        /* --- ITEMS LIST --- */
        .cart-item-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .cart-item-card {
            background: var(--bg-main);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.5rem;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 4px;
        }

        .cart-item-tag {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--store-indigo);
            background: var(--store-indigo-light);
            padding: 2px 8px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 0.75rem;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            overflow: hidden;
            background: var(--bg-card);
            width: fit-content;
            margin-bottom: 0.75rem;
        }

        .qty-btn {
            background: transparent;
            border: none;
            width: 32px;
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
            width: 44px;
            text-align: center;
            border: none;
            background: transparent;
            font-family: inherit;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--text-main);
            outline: none;
        }

        .cart-item-remarks {
            width: 100%;
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            background: var(--bg-card);
            color: var(--text-main);
            font-family: inherit;
            font-size: 0.8rem;
            padding: 8px 12px;
            outline: none;
            resize: vertical;
            box-sizing: border-box;
        }

        .cart-item-remarks:focus {
            border-color: var(--store-orange);
        }

        .delete-item-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 8px;
            border-radius: 10px;
            transition: all 0.2s;
        }

        .delete-item-btn:hover {
            color: var(--danger-color);
            background: rgba(239, 68, 68, 0.08);
        }

        /* --- DETAILS FORM --- */
        .form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 500;
            font-family: inherit;
            background: var(--bg-main);
            color: var(--text-main);
            outline: none;
            box-sizing: border-box;
            transition: all 0.2s;
            margin-bottom: 1.25rem;
        }

        .form-input:focus {
            border-color: var(--store-orange);
            background: var(--bg-card);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.08);
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--store-orange), #ea580c);
            color: white;
            border: none;
            border-radius: 14px;
            font-weight: 800;
            font-size: 0.95rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 16px rgba(249, 115, 22, 0.2);
            transition: all 0.25s ease;
            margin-top: 1.5rem;
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.35);
        }

        .submit-btn:disabled {
            background: var(--border-color);
            color: var(--text-muted);
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        /* Premium Usage Type Pill Selector */
        .usage-type-pills {
            display: flex;
            gap: 12px;
            margin-bottom: 1.25rem;
        }

        .usage-pill-label {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            background: var(--bg-main);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
            color: var(--text-muted);
        }

        .usage-pill-label:hover {
            border-color: var(--store-orange);
            color: var(--store-orange);
            background: var(--store-orange-light);
            transform: translateY(-1px);
        }

        .usage-pill-label.active {
            border-color: var(--store-orange);
            color: var(--store-orange);
            background: var(--store-orange-light);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.1);
        }

        .usage-pill-label .pill-icon {
            width: 16px;
            height: 16px;
            transition: transform 0.2s;
        }

        .usage-pill-label.active .pill-icon {
            transform: scale(1.1);
        }

        @media (max-width: 900px) {
            .checkout-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Select2 checkout overrides */
        .select2-container--default .select2-selection--single {
            height: 46px !important;
            border: 1.5px solid var(--border-color) !important;
            border-radius: 12px !important;
            background: var(--bg-main) !important;
            color: var(--text-main) !important;
            display: flex !important;
            align-items: center !important;
            padding-left: 8px !important;
            font-size: 0.9rem !important;
            font-weight: 500 !important;
            font-family: inherit !important;
            outline: none !important;
            box-sizing: border-box !important;
            transition: all 0.2s !important;
        }
        .select2-container--default .select2-selection--single:hover {
            border-color: var(--store-orange) !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: var(--store-orange) !important;
            background: var(--bg-card) !important;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.08) !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px !important;
            right: 12px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--text-main) !important;
            font-weight: 600 !important;
        }
        .select2-dropdown {
            border: 1.5px solid var(--border-color) !important;
            border-radius: 12px !important;
            box-shadow: var(--shadow-premium) !important;
            background: var(--bg-card) !important;
            z-index: 99999 !important;
            overflow: hidden !important;
        }
        .select2-search__field {
            border-radius: 8px !important;
            border: 1px solid var(--border-color) !important;
            padding: 8px 12px !important;
            background: var(--bg-main) !important;
            color: var(--text-main) !important;
        }
        .select2-results__option {
            padding: 10px 15px !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
        }
        .select2-results__option--highlighted[aria-selected] {
            background-color: var(--store-orange) !important;
            color: white !important;
        }
        .select2-results__group {
            font-size: 0.65rem !important;
            font-weight: 900 !important;
            color: var(--text-muted) !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            padding: 8px 15px 4px !important;
            background: var(--bg-main) !important;
        }

        /* Flatpickr calendar visibility fix */
        .flatpickr-calendar {
            z-index: 99999 !important;
            position: absolute !important;
        }
        .flatpickr-calendar .flatpickr-months,
        .flatpickr-calendar .flatpickr-innerContainer,
        .flatpickr-calendar .flatpickr-rContainer {
            position: relative;
            z-index: 99999 !important;
        }
        /* Month & year dropdown selects inside the calendar */
        .flatpickr-monthDropdown-months,
        .flatpickr-monthDropdown-month,
        .numInputWrapper input,
        .numInputWrapper {
            z-index: 100000 !important;
            position: relative !important;
        }
        /* Native browser select dropdown rendered by the OS — must be above everything */
        .flatpickr-monthDropdown-months option {
            z-index: 100001 !important;
        }
        .flatpickr-day.selected,
        .flatpickr-day.selected:hover {
            background: var(--store-orange) !important;
            border-color: var(--store-orange) !important;
        }
        .flatpickr-day:hover {
            background: var(--store-orange-light) !important;
        }
        .flatpickr-months .flatpickr-month,
        .flatpickr-weekdays,
        span.flatpickr-weekday {
            background: var(--store-orange) !important;
            color: #fff !important;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months,
        .flatpickr-current-month input.cur-year {
            background: transparent !important;
            color: #1e293b !important;
            font-weight: 700 !important;
        }
    </style>

</head>
<body>

    <!-- --- HEADER --- -->
    <header class="store-header">
        <div class="header-container">
            <a href="{{ route('requisitions.index') }}" class="store-brand">
                <div class="brand-logo-container" style="background: transparent; box-shadow: none; width: 56px; height: 56px;">
                    <img src="{{ asset('img/download-1.webp') }}" alt="Logo" style="width: 56px; height: 56px; object-fit: contain; border-radius: 12px;">
                </div>
                <div>
                    <div class="brand-name">CENTRAL STORE</div>
                    <div class="brand-subtitle">{{ \App\Models\Setting::get('organization_name', 'NACOC') }}</div>
                </div>
            </a>

            <a href="{{ route('requisitions.index') }}" class="back-to-shop-btn">
                <i data-lucide="arrow-left" style="width: 16px;"></i> Return to Shop
            </a>
        </div>
    </header>

    <!-- --- EMPTY STATE (Toggled by JS) --- -->
    <div class="empty-cart-state" id="empty-state" style="display: none;">
        <div class="empty-icon">
            <i data-lucide="shopping-cart" style="width: 38px; height: 38px;"></i>
        </div>
        <h3 class="empty-title">Your Cart is Empty</h3>
        <p class="empty-desc">You haven't selected any items for delivery yet. Go back to our central catalog and load items into your bag.</p>
        <a href="{{ route('requisitions.index') }}" class="empty-btn">
            <i data-lucide="shopping-bag" style="width: 16px;"></i> Browse Store Catalog
        </a>
    </div>

    @if($isDepartmentDisabled)
    <div style="max-width: 1200px; margin: 2rem auto 0; padding: 1.5rem 2rem; background: #fef2f2; border: 2px dashed #fca5a5; border-radius: 24px; display: flex; align-items: center; gap: 1.25rem; box-shadow: 0 4px 20px rgba(220, 38, 38, 0.05); animation: slideIn 0.4s ease; box-sizing: border-box;">
        <div style="width: 52px; height: 52px; background: #fee2e2; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0;">
            <i data-lucide="shield-alert" style="width: 26px; height: 26px;"></i>
        </div>
        <div>
            <h4 style="font-size: 1.05rem; font-weight: 850; color: #991b1b; margin: 0;">Department Requisition Disabled</h4>
            <p style="font-size: 0.85rem; color: #b91c1c; font-weight: 600; margin: 4px 0 0;">
                Strategic Notice: Requisition access for the <b>{{ auth()->user()->department ?? 'unassigned' }}</b> department has been temporarily suspended by the Head of Stores. Submissions are blocked.
            </p>
        </div>
    </div>
    @endif

    <!-- --- MAIN LAYOUT --- -->
    <main class="checkout-layout" id="main-layout">

        <!-- --- COLUMN 1: ITEMS IN BAG --- -->
        <section class="checkout-card">
            <h3 class="checkout-title">
                <span><i data-lucide="shopping-bag" style="width: 24px; vertical-align: middle; margin-right: 4px;"></i></span>
                Items in Requisition Bag (<span id="cart-items-count">0</span>)
            </h3>

            <div class="cart-item-list" id="cart-items-container">
                <!-- Dynamically loaded -->
            </div>
        </section>

        <!-- --- COLUMN 2: IDENTIFICATION FORM --- -->
        <aside class="checkout-card">
            <h3 class="checkout-title" style="border-bottom:none; margin-bottom:0.75rem; padding-bottom:0;">
                <span><i data-lucide="user-check" style="width: 24px; vertical-align: middle; margin-right: 4px;"></i></span>
                Requester Details
            </h3>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                Prefill or complete the fields below to authorize your requisition payload.
            </p>

            <form id="checkout-form" onsubmit="submitRequisition(event)">
                @csrf
                <div>
                    <label class="form-label">Full Name *</label>
                    <input type="text" id="requesterName" class="form-input" value="{{ auth()->user()->name }}" readonly style="background: var(--bg-card); cursor: not-allowed;" required placeholder="Your full name...">
                </div>

                <div>
                    <label class="form-label">Rank / Title</label>
                    <input type="text" id="rankTitle" class="form-input" placeholder="e.g. Sergeant, Staff Officer...">
                </div>


                <div>
                    <label class="form-label">Department / Unit *</label>
                    @php
                        $authUser = auth()->user();

                        // Clean: treat "-- Select Department --" or whitespace-only as empty
                        $rawDept = trim($authUser->department ?? '');
                        $userDepartment = (str_starts_with($rawDept, '--') || $rawDept === '') ? '' : $rawDept;

                        // Fallback: pull from sponsoring Department Head's department
                        if (empty($userDepartment) && !empty($authUser->sponsored_by)) {
                            $sponsor = \App\Models\User::find($authUser->sponsored_by);
                            $userDepartment = trim($sponsor?->department ?? '');
                        }

                        // Auto-heal: save the resolved department back to the user if it was missing
                        if (!empty($userDepartment) && $userDepartment !== $authUser->department) {
                            $authUser->department = $userDepartment;
                            $authUser->save();
                        }
                    @endphp
                    <div style="position: relative;">
                        <input
                            type="text"
                            id="department"
                            class="form-input"
                            value="{{ $userDepartment }}"
                            readonly
                            style="background: var(--bg-card); cursor: not-allowed; color: var(--text-main); padding-right: 2.5rem; font-weight: 700;"
                            title="Department is assigned by your Department Head and cannot be changed."
                            placeholder="Not assigned — contact your Department Head"
                        >
                        <span style="position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                    </div>
                    @if(empty($userDepartment))
                        <p style="font-size: 0.7rem; color: #ef4444; font-weight: 700; margin-top: 4px;">
                            ⚠ No department assigned. Please contact your Department Head.
                        </p>
                    @endif
                </div>

                <div>
                    <label class="form-label">Priority Level *</label>
                    <select id="priority" class="form-input">
                        <option value="normal" selected>Normal Processing</option>
                        <option value="urgent">Urgent Processing</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Usage Type *</label>
                    <div class="usage-type-pills">
                        <label class="usage-pill-label active" data-value="permanent">
                            <input type="radio" name="usage_type" value="permanent" checked style="display: none;">
                            <i data-lucide="package-check" class="pill-icon"></i>
                            <span>Permanent</span>
                        </label>
                        <label class="usage-pill-label" data-value="temporary">
                            <input type="radio" name="usage_type" value="temporary" style="display: none;">
                            <i data-lucide="calendar" class="pill-icon"></i>
                            <span>Loan</span>
                        </label>
                    </div>
                </div>

                <div id="return-date-container" style="display: none;">
                    <label class="form-label">Expected Return Date *</label>
                    <input type="date" id="returnDate" class="form-input" style="background: var(--bg-input, #fff); cursor: pointer;">
                </div>

                <div>
                    <label class="form-label">Purpose / Justification *</label>
                    <textarea id="purpose" class="form-input" rows="4" required placeholder="Describe the operational purpose of these supplies..." style="resize: vertical;"></textarea>
                </div>

                <button type="submit" class="submit-btn" id="submit-checkout-btn" {{ $isDepartmentDisabled ? 'disabled' : '' }}>
                    @if($isDepartmentDisabled)
                        <i data-lucide="shield-alert" style="width: 18px;"></i> Submissions Suspended
                    @else
                        <i data-lucide="send" style="width: 18px;"></i> Submit Requisition
                    @endif
                </button>
            </form>
        </aside>
    </main>

    <script>
        const ledgeMap = @json($ledgeMap);
        let cart = [];

        function loadCart() {
            const savedCart = localStorage.getItem('store_checkout_cart');
            if (savedCart) {
                try {
                    cart = JSON.parse(savedCart);
                } catch(e) {
                    cart = [];
                }
            }
            renderCheckoutUI();
        }

        function saveCart() {
            localStorage.setItem('store_checkout_cart', JSON.stringify(cart));
        }

        function renderCheckoutUI() {
            const container = document.getElementById('cart-items-container');
            const mainLayout = document.getElementById('main-layout');
            const emptyState = document.getElementById('empty-state');
            const countLabel = document.getElementById('cart-items-count');

            if (cart.length === 0) {
                mainLayout.style.display = 'none';
                emptyState.style.display = 'block';
                return;
            }

            mainLayout.style.display = 'grid';
            emptyState.style.display = 'none';
            countLabel.textContent = cart.length;

            container.innerHTML = cart.map((item, idx) => `
                <div class="cart-item-card">
                    <div class="cart-item-info">
                        <span class="cart-item-tag">${ledgeMap[item.category] || 'Other'}</span>
                        <div class="cart-item-title">${item.description}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom: 0.75rem;">
                            Unit of Issue: <b>${item.unit}</b>
                        </div>

                        <div style="display:flex; align-items:center; gap: 8px;">
                            <span style="font-size:0.75rem; font-weight:700; color:var(--text-muted);">Quantity:</span>
                            <div class="qty-controls">
                                <button class="qty-btn" type="button" onclick="adjustQty(${idx}, -1)">
                                    <i data-lucide="minus" style="width: 12px;"></i>
                                </button>
                                <input type="number"
                                       class="qty-val"
                                       value="${item.quantity_requested}"
                                       min="0.1"
                                       step="any"
                                       max="${item.total_stock}"
                                       onchange="changeQty(${idx}, this.value)">
                                <button class="qty-btn" type="button" onclick="adjustQty(${idx}, 1)">
                                    <i data-lucide="plus" style="width: 12px;"></i>
                                </button>
                            </div>

                        </div>

                        <textarea class="cart-item-remarks"
                                  placeholder="Specify remarks, dimensions, color preferences..."
                                  rows="2"
                                  onchange="updateRemarks(${idx}, this.value)">${item.remarks || ''}</textarea>
                    </div>

                    <button class="delete-item-btn" type="button" onclick="removeItem(${idx})" title="Remove item from bag">
                        <i data-lucide="trash-2" style="width: 18px;"></i>
                    </button>
                </div>
            `).join('');

            lucide.createIcons();
        }

        function adjustQty(idx, amt) {
            let current = parseFloat(cart[idx].quantity_requested) || 1;
            const maxVal = parseFloat(cart[idx].total_stock) || 999;

            current += amt;
            if (current < 1) current = 1;
            if (current > maxVal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock Cap Reached',
                    text: `Available stock for this item is restricted to ${maxVal}.`,
                    confirmButtonColor: 'var(--store-orange)'
                });
                current = maxVal;
            }

            cart[idx].quantity_requested = current;
            saveCart();
            renderCheckoutUI();
        }

        function changeQty(idx, val) {
            const qty = parseFloat(val) || 1;
            const maxVal = cart[idx].total_stock;

            if (qty <= 0) {
                removeItem(idx);
                return;
            }

            if (qty > maxVal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Exceeds Stock Balance',
                    text: `Available stock for this item is ${maxVal}. Quantities corrected to limit.`,
                    confirmButtonColor: 'var(--store-orange)'
                });
                cart[idx].quantity_requested = maxVal;
            } else {
                cart[idx].quantity_requested = qty;
            }

            saveCart();
            renderCheckoutUI();
        }

        function updateRemarks(idx, val) {
            cart[idx].remarks = val;
            saveCart();
        }

        function removeItem(idx) {
            cart.splice(idx, 1);
            saveCart();
            renderCheckoutUI();
        }

        async function submitRequisition(e) {
            e.preventDefault();
            if (cart.length === 0) return;

            // Sync current input quantities from DOM to cart
            const qtyInputs = document.querySelectorAll('.qty-val');
            qtyInputs.forEach((input, idx) => {
                const val = parseFloat(input.value) || 0;
                cart[idx].quantity_requested = val;
            });
            saveCart();

            // Validate that quantity does not exceed the remaining limit (total_stock)
            for (let i = 0; i < cart.length; i++) {
                const qty = parseFloat(cart[i].quantity_requested) || 0;
                const maxVal = parseFloat(cart[i].total_stock) || 0;
                if (qty > maxVal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Limit Exceeded',
                        text: `Available stock for this item is restricted to ${maxVal}.`,
                        confirmButtonColor: 'var(--store-orange)'
                    });
                    return;
                }
            }

            const usageType = document.querySelector('input[name="usage_type"]:checked').value;
            let finalPurpose = document.getElementById('purpose').value;

            if (usageType === 'temporary') {
                let retDate = document.getElementById('returnDate').value;
                if (!retDate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Return Date Required',
                        text: 'Please select an expected return date for your temporary requisition.',
                        confirmButtonColor: 'var(--store-orange)'
                    });
                    return;
                }
                const parts = retDate.split('-');
                if (parts.length === 3) {
                    retDate = `${parts[2]}/${parts[1]}/${parts[0]}`;
                }
                finalPurpose += `\n\n[Expected Return Date: ${retDate}]`;
            }

            const btn = document.getElementById('submit-checkout-btn');
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader" style="width:16px; animation:spin 1s linear infinite;"></i> Registering Request...';
            lucide.createIcons();

            const payload = {
                requester_name: document.getElementById('requesterName').value,
                department:     document.getElementById('department').value,
                rank_or_title:  document.getElementById('rankTitle').value,
                purpose:        finalPurpose,
                priority:       document.getElementById('priority').value,
                usage_type:     usageType,
                items:          cart.map(i => ({
                    description: i.description,
                    category:    i.category || '',
                    unit:        i.unit || 'pcs',
                    quantity_requested: i.quantity_requested,
                    remarks:     i.remarks || ''
                })),
                _token: '{{ csrf_token() }}'
            };

            try {
                const response = await fetch('{{ route("requisitions.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();

                if (result.success) {
                    if (typeof window.playNotificationSound === 'function') {
                        window.playNotificationSound('sent');
                    }
                    cart = [];
                    saveCart();
                    const uniqueId = 'REQ-' + String(result.id).padStart(5, '0');
                    Swal.fire({
                        icon: 'success',
                        title: 'Requisition Sent!',
                        html: `${result.message}<br><br><div style="font-size:1.15rem; font-weight:900; background:var(--store-orange-light); border: 1.5px dashed var(--store-orange); padding:10px; border-radius:12px; color:var(--store-orange)">Requisition ID: ${uniqueId}</div><br><small style="color:var(--text-muted); font-weight:700;">Please write down or copy this ID. You will need to present it to confirm collection of your approved items.</small>`,
                        confirmButtonColor: 'var(--store-orange)'
                    }).then(() => {
                        window.location.href = "{{ route('requisitions.index') }}";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Submission Failed',
                        text: result.message || 'Stores was unable to register your checkout request.',
                        confirmButtonColor: 'var(--danger-color)'
                    });
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="send" style="width:18px;"></i> Submit Requisition';
                    lucide.createIcons();
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Unable to communicate with the store management server.',
                    confirmButtonColor: 'var(--danger-color)'
                });
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="send" style="width:18px;"></i> Submit Requisition';
                lucide.createIcons();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadCart();
            lucide.createIcons();

            // Initialize Expected Return Date min date natively
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const yyyy = tomorrow.getFullYear();
            const mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
            const dd = String(tomorrow.getDate()).padStart(2, '0');
            document.getElementById('returnDate').min = `${yyyy}-${mm}-${dd}`;

            // Setup Usage Type pill interactivity
            const returnDateContainer = document.getElementById('return-date-container');
            const returnDateInput = document.getElementById('returnDate');

            document.querySelectorAll('.usage-pill-label').forEach(label => {
                label.addEventListener('click', function() {
                    document.querySelectorAll('.usage-pill-label').forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;

                    if (radio.value === 'temporary') {
                        returnDateContainer.style.display = 'block';
                        returnDateInput.setAttribute('required', 'required');
                    } else {
                        returnDateContainer.style.display = 'none';
                        returnDateInput.removeAttribute('required');
                        returnDateInput.value = '';
                    }
                });
            });
        });
    </script>
@endsection
