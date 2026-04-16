@extends('layouts.dashboard')

@section('content')
<div class="animate-slide-up" style="max-width: 1600px; margin: 0 auto; padding: 0 1.5rem;">
    
    <!-- Ultra-Modern Operations Header -->
    <div class="glass-card header-mesh" style="padding: 3rem; border-radius: 32px; margin-bottom: 3rem; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.4); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.08);">
        <!-- Decorative background elements -->
        <div style="position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%); z-index: 0;"></div>
        <div style="position: absolute; bottom: -50px; left: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(16, 185, 129, 0.05) 0%, transparent 70%); z-index: 0;"></div>

        <div style="position: relative; z-index: 1;">
            <div class="header-top" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2.5rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
                        <span style="background: var(--primary); color: white; font-size: 0.65rem; font-weight: 900; padding: 0.4rem 1.25rem; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.1em; box-shadow: 0 5px 15px rgba(99, 102, 241, 0.3);">Inventory Control</span>
                        <div style="width: 4px; height: 4px; background: var(--text-muted); border-radius: 50%; opacity: 0.5;"></div>
                        <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="cpu" style="width: 14px;"></i> System Operation
                        </span>
                    </div>
                    <h1 style="margin: 0; font-size: 3rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.05em; line-height: 1;">Issue <span style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Inventory</span></h1>
                    <p style="margin: 12px 0 0; color: var(--text-muted); font-size: 1.1rem; font-weight: 500; opacity: 0.8;">Seamlessly disburse stock items and track recipient allocations in real-time.</p>
                </div>
                
                <div class="header-actions" style="display: flex; gap: 1rem;">
                    <button onclick="window.location.reload()" class="modern-action-btn" title="Sync Catalog">
                        <i data-lucide="refresh-cw" style="width: 20px;"></i>
                        <span>Sync</span>
                    </button>
                    <button class="modern-action-btn secondary" title="View Audit Logs">
                        <i data-lucide="scroll-text" style="width: 20px;"></i>
                    </button>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 2rem; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 2.5rem;">
                <div class="search-cat-container" style="display: flex; gap: 2.5rem; align-items: center; flex-wrap: wrap;">
                    <!-- Elegant Search -->
                    <div class="search-box-wrapper" style="position: relative; flex: 1; min-width: 320px;">
                        <i data-lucide="search" style="position: absolute; left: 1.5rem; top: 50%; transform: translateY(-50%); width: 22px; color: var(--primary); opacity: 0.6;"></i>
                        <input type="text" id="catalogSearch" placeholder="What are you looking for today?" style="width: 100%; padding: 1.35rem 1.5rem 1.35rem 4rem; border-radius: 20px; border: 2px solid transparent; background: rgba(0,0,0,0.03); color: var(--text-main); font-size: 1.05rem; font-weight: 600; outline: none; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);" onfocus="this.style.borderColor='var(--primary)'; this.style.background='var(--bg-card)'; this.style.boxShadow='0 10px 25px rgba(99, 102, 241, 0.08)';" onblur="this.style.borderColor='transparent'; this.style.background='rgba(0,0,0,0.03)';">
                    </div>

                    <!-- Premium Category Selector with Scroll Indicators -->
                    <div style="position: relative; flex: 1; display: flex; align-items: center; min-width: 0;">
                        <button onclick="scrollCats(-1)" class="scroll-arrow prev" id="catLeftArrow" style="display: none;">
                            <i data-lucide="chevron-left" style="width: 18px;"></i>
                        </button>

                        <div id="catList" style="display: flex; gap: 0.85rem; overflow-x: auto; scrollbar-width: none; padding: 0.25rem; scroll-behavior: smooth; flex: 1;">
                            <button class="cat-pill modern active" onclick="filterCategory('all', this)">
                                <i data-lucide="rocket" style="width: 16px;"></i>
                                Everything
                            </button>
                            @foreach($ledgeMap as $code => $name)
                            @php
                                $icon = 'package';
                                if ($code == 'B') $icon = 'trash-2';
                                elseif ($code == 'C') $icon = 'monitor';
                                elseif ($code == 'J') $icon = 'printer';
                                elseif ($code == 'G') $icon = 'activity';
                            @endphp
                            <button class="cat-pill modern" onclick="filterCategory('{{ $code }}', this)">
                                <i data-lucide="{{ $icon }}" style="width: 16px;"></i>
                                {{ $name }}
                            </button>
                            @endforeach
                        </div>

                        <button onclick="scrollCats(1)" class="scroll-arrow next" id="catRightArrow">
                            <i data-lucide="chevron-right" style="width: 18px;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workspace Split -->
    <div class="workspace-grid" style="display: grid; grid-template-columns: 1fr 420px; gap: 3rem; align-items: flex-start; padding-bottom: 5rem;">
        
        <!-- Left Column: Catalog -->
        <div>
            <div id="productGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 1.5rem;">
                @forelse($items as $item)
                @php
                    $colors = [
                        'B' => ['#ef4444', 'rgba(239,68,68,0.05)', 'rgba(239,68,68,0.1)', 'trash-2'],
                        'C' => ['#f59e0b', 'rgba(245,158,11,0.05)', 'rgba(245,158,11,0.1)', 'monitor'],
                        'J' => ['#10b981', 'rgba(16,185,129,0.05)', 'rgba(16,185,129,0.1)', 'printer'],
                        'G' => ['#8b5cf6', 'rgba(139,92,246,0.05)', 'rgba(139,92,246,0.1)', 'activity']
                    ];
                    $config = $colors[$item->ledge_category] ?? ['var(--primary)', 'rgba(99,102,241,0.05)', 'rgba(99,102,241,0.1)', 'package'];
                    $isOutOfStock = $item->total_stock <= 0;
                @endphp

                <div class="product-card glass-card" data-category="{{ $item->ledge_category }}" data-description="{{ strtolower($item->description) }}" style="{{ $isOutOfStock ? 'opacity: 0.7;' : '' }}">
                    <div class="product-badge" style="background: {{ $config[2] }}; color: {{ $config[0] }};">Ledge {{ $item->ledge_category }}</div>
                    <div style="height: 60px; display: flex; align-items: center; justify-content: center; background: {{ $config[1] }}; border-radius: 10px; margin-bottom: 0.75rem;">
                        <i data-lucide="{{ $config[3] }}" style="width: 28px; height: 28px; color: {{ $config[0] }}; opacity: 0.6;"></i>
                    </div>
                    <h4 style="margin: 0 0 0.5rem; font-size: 1rem; font-weight: 850; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $item->description }}</h4>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <span style="font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Stock</span>
                        <span style="font-size: 1.15rem; font-weight: 900; color: {{ $isOutOfStock ? '#ef4444' : '#10b981' }};">{{ number_format($item->total_stock) }}</span>
                    </div>

                    @if($isOutOfStock)
                        <button class="add-to-cart-btn" style="opacity: 0.5; cursor: not-allowed;"><i data-lucide="slash" style="width: 18px;"></i> Unavailable</button>
                    @else
                        <button class="add-to-cart-btn" onclick="addToCart('{{ addslashes($item->description) }}', {{ (int)$item->total_stock }}, '{{ $item->ledge_category }}')">
                            <i data-lucide="plus-circle" style="width: 18px;"></i> Add to List
                        </button>
                    @endif
                </div>
                @empty
                <div style="grid-column: 1 / -1; padding: 5rem; text-align: center;">
                    <i data-lucide="package-x" style="width: 64px; height: 64px; color: var(--text-muted); opacity: 0.3; margin-bottom: 1.5rem;"></i>
                    <h3 style="font-weight: 800; color: var(--text-main);">No Stock Items Available</h3>
                    <p style="color: var(--text-muted);">Please add items to your inventory first.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right Column: Cart Panel -->
        <div class="cart-sticky" style="position: sticky; top: 100px;">
            <div class="glass-card" style="border-radius: 28px; padding: 0.5rem; border: 2px solid var(--border-color);">
                <div style="padding: 1.75rem; border-bottom: 1px solid var(--border-color);">
                    <h3 style="margin: 0 0 1.5rem; font-size: 1.25rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 12px;">
                        <i data-lucide="clipboard-list" style="color: var(--primary);"></i> Disbursement Info
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div>
                            <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Issue Date</label>
                            <input type="date" id="issuanceDate" value="{{ date('Y-m-d') }}" style="width: 100%; padding: 0.85rem; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-weight: 700;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Beneficiary</label>
                            <input type="text" id="beneficiary" placeholder="Recipient / Department" style="width: 100%; padding: 0.85rem; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-weight: 700;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Alloc. Type</label>
                            <div id="issueTypeSlider" style="display: flex; background: var(--bg-main); padding: 0.4rem; border-radius: 14px; border: 1px solid var(--border-color); position: relative;">
                                <div id="sliderPill" style="position: absolute; top: 0.4rem; left: 0.4rem; width: calc(50% - 0.5rem); height: calc(100% - 0.8rem); background: var(--primary); border-radius: 10px; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); z-index: 1;"></div>
                                <button type="button" class="issue-type-btn active" onclick="setIssueType('Permanent', this, 0)" style="flex: 1; padding: 0.75rem; border: none; background: transparent; color: white; font-weight: 800; font-size: 0.8rem; cursor: pointer; position: relative; z-index: 2;">PERMANENT</button>
                                <button type="button" class="issue-type-btn" onclick="setIssueType('Temporary', this, 1)" style="flex: 1; padding: 0.75rem; border: none; background: transparent; color: var(--text-muted); font-weight: 800; font-size: 0.8rem; cursor: pointer; position: relative; z-index: 2;">TEMPORARY</button>
                                <input type="hidden" id="issuanceType" value="Permanent">
                            </div>
                        </div>
                    </div>
                </div>

                <div style="padding: 1.75rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h4 style="margin: 0; font-weight: 900; color: var(--text-main);">Selected Items</h4>
                        <span id="cartItemCount" style="padding: 0.25rem 0.75rem; background: var(--primary); color: white; border-radius: 99px; font-size: 0.75rem; font-weight: 800;">0</span>
                    </div>
                    <div id="cartEmptyState" style="padding: 2.5rem 0; text-align: center; border: 2px dashed var(--border-color); border-radius: 18px; color: var(--text-muted);">
                        <i data-lucide="package-open" style="width: 38px; height: 38px; opacity: 0.3; margin-bottom: 0.75rem;"></i>
                        <p style="font-weight: 700; font-size: 0.9rem;">Select items to disburse</p>
                    </div>
                    <div id="cartItemsContainer" style="display: flex; flex-direction: column; gap: 1rem; max-height: 400px; overflow-y: auto;"></div>
                </div>

                <div style="padding: 1.5rem 1.75rem; border-top: 1px solid var(--border-color); background: rgba(99,102,241,0.02); border-radius: 0 0 28px 28px;">
                    <button id="confirmBtn" class="confirm-btn-final" onclick="confirmIssuance()">
                        <i data-lucide="zap" style="width: 24px; fill: white;"></i> 
                        <span>Confirm Disbursement</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Header Stylings */
    /* Filter Scroll Arrows */
    .scroll-arrow {
        width: 40px; height: 40px; border-radius: 12px;
        background: var(--bg-card); border: 2px solid var(--border-color);
        color: var(--text-main); cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 10;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        flex-shrink: 0;
    }
    .scroll-arrow:hover {
        background: var(--primary); color: white; border-color: var(--primary);
        transform: scale(1.1);
    }
    .scroll-arrow.prev { margin-right: 0.5rem; }
    .scroll-arrow.next { margin-left: 0.5rem; }

    .header-mesh {
        background: radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.05) 0, transparent 50%),
                    radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.05) 0, transparent 50%),
                    var(--bg-card);
        backdrop-filter: blur(20px);
    }

    .modern-action-btn {
        padding: 0.85rem 1.75rem;
        border-radius: 18px;
        border: 2px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-main);
        font-weight: 800;
        font-size: 0.95rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .modern-action-btn:hover {
        transform: translateY(-3px);
        border-color: var(--primary);
        color: var(--primary);
        box-shadow: 0 15px 30px -5px rgba(99, 102, 241, 0.15);
    }

    .modern-action-btn.secondary {
        width: 58px;
        height: 58px;
        padding: 0;
        justify-content: center;
    }

    .cat-pill.modern {
        background: rgba(0,0,0,0.03);
        border: 1px solid transparent;
        padding: 1rem 1.75rem;
        border-radius: 20px;
        font-weight: 800;
        font-size: 0.95rem;
        color: var(--text-muted);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
    }

    .cat-pill.modern:hover {
        background: white;
        color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        border-color: var(--primary-light);
    }

    .cat-pill.modern.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        box-shadow: 0 15px 30px rgba(99, 102, 241, 0.3);
        transform: scale(1.05) translateY(-2px);
    }

    .product-card {
        padding: 1.25rem; border-radius: 18px; display: flex; flex-direction: column;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid var(--border-color);
    }
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--card-shadow-hover);
        border-color: var(--primary-light);
    }
    .product-badge {
        position: absolute; top: 1rem; right: 1rem;
        padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.6rem; font-weight: 900;
    }
    .add-to-cart-btn {
        margin-top: auto; padding: 0.75rem; border-radius: 12px;
        border: 2px solid var(--primary); background: transparent;
        color: var(--primary); font-weight: 900; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        transition: 0.3s;
        font-size: 0.85rem;
    }
    .add-to-cart-btn:hover:not(:disabled) {
        background: var(--primary); color: white;
        box-shadow: 0 8px 20px rgba(99,102,241,0.25);
    }
    .confirm-btn-final {
        width: 100%; padding: 1.15rem; border-radius: 18px;
        border: none; 
        background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
        color: white;
        font-size: 1.1rem; font-weight: 900; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 12px;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
        position: relative;
        overflow: hidden;
    }
    .confirm-btn-final::before {
        content: '';
        position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: 0.5s;
    }
    .confirm-btn-final:hover:not(:disabled) {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 20px 40px rgba(79, 70, 229, 0.45);
        filter: brightness(1.1);
    }
    .confirm-btn-final:hover::before {
        left: 100%;
    }
    .confirm-btn-final:active { transform: translateY(0) scale(0.98); }
    .confirm-btn-final:disabled {
        background: #94a3b8;
        box-shadow: none;
        cursor: not-allowed;
        transform: none;
    }

    @media (max-width: 1024px) {
        .workspace-grid {
            grid-template-columns: 1fr !important;
            gap: 2rem !important;
        }
        .cart-sticky {
            position: relative !important;
            top: 0 !important;
            margin-top: 1rem;
        }
        .header-mesh {
            padding: 1.5rem !important;
            border-radius: 20px !important;
        }
        .header-top {
            flex-direction: column !important;
            gap: 1.5rem !important;
        }
        .header-actions {
            width: 100%;
            justify-content: flex-start;
        }
        .search-cat-container {
            flex-direction: column !important;
            gap: 1.5rem !important;
        }
        .search-box-wrapper {
            max-width: 100% !important;
            min-width: 100% !important;
        }
        h1 { font-size: 2.25rem !important; }
    }
</style>

<script>
    let cart = [];

    function updateCartUI() {
        const container = document.getElementById('cartItemsContainer');
        const emptyState = document.getElementById('cartEmptyState');
        const countBadge = document.getElementById('cartItemCount');

        container.innerHTML = '';
        countBadge.textContent = cart.length;

        if (cart.length === 0) {
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
            cart.forEach((item, index) => {
                const el = document.createElement('div');
                el.style.cssText = 'background: var(--bg-main); padding: 1.25rem; border-radius: 18px; border: 1px solid var(--border-color); margin-bottom: 0.75rem;';
                el.innerHTML = `
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                        <span style="font-weight: 900; font-size: 0.95rem; color: var(--text-main);">${item.description}</span>
                        <button onclick="removeFromCart(${index})" style="background:transparent; border:none; color:#ef4444; cursor:pointer;"><i data-lucide="trash-2" style="width:16px;"></i></button>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;">Avl: ${item.maxStock}</span>
                        <div style="display: flex; align-items: center; gap: 12px; background: var(--bg-card); padding: 0.35rem; border-radius: 10px; border: 1.5px solid var(--border-color);">
                            <button onclick="updateQty(${index}, -1)" style="border:none; background:transparent; cursor:pointer;"><i data-lucide="minus-circle" style="width:18px; color:var(--text-muted);"></i></button>
                            <span style="font-weight: 900; min-width: 30px; text-align: center;">${item.qty}</span>
                            <button onclick="updateQty(${index}, 1)" style="border:none; background:transparent; cursor:pointer;"><i data-lucide="plus-circle" style="width:18px; color:var(--primary);"></i></button>
                        </div>
                    </div>
                `;
                container.appendChild(el);
            });
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    function addToCart(desc, stock, cat) {
        const exists = cart.find(i => i.description === desc);
        if (exists) {
            if (exists.qty < stock) exists.qty++;
            else showToast('Stock Limit', 'Cannot exceed available stock', 'warning');
        } else {
            cart.push({ description: desc, maxStock: stock, category: cat, qty: 1 });
            showToast('Added', desc, 'success');
        }
        updateCartUI();
    }

    function removeFromCart(idx) { cart.splice(idx, 1); updateCartUI(); }
    function updateQty(idx, delta) {
        const item = cart[idx];
        const newQty = item.qty + delta;
        if (newQty > 0 && newQty <= item.maxStock) {
            item.qty = newQty;
            updateCartUI();
        }
    }

    function setIssueType(type, btn, idx) {
        document.getElementById('issuanceType').value = type;
        document.getElementById('sliderPill').style.transform = `translateX(${idx * 100}%)`;
        document.querySelectorAll('.issue-type-btn').forEach(b => b.style.color = 'var(--text-muted)');
        btn.style.color = 'white';
    }

    function filterCategory(cat, btn) {
        document.querySelectorAll('.cat-pill').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(c => c.style.display = (cat === 'all' || c.dataset.category === cat) ? 'flex' : 'none');
    }

    const catList = document.getElementById('catList');
    const leftArrow = document.getElementById('catLeftArrow');
    const rightArrow = document.getElementById('catRightArrow');

    function scrollCats(dir) {
        catList.scrollBy({ left: dir * 300, behavior: 'smooth' });
    }

    catList.addEventListener('scroll', () => {
        leftArrow.style.display = catList.scrollLeft > 20 ? 'flex' : 'none';
        const maxScroll = catList.scrollWidth - catList.clientWidth;
        rightArrow.style.display = catList.scrollLeft < maxScroll - 20 ? 'flex' : 'none';
    });

    // Live Search
    document.getElementById('catalogSearch').addEventListener('input', e => {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.product-card').forEach(c => {
            const desc = c.dataset.description;
            c.style.display = desc.includes(term) ? 'flex' : 'none';
        });
    });

    async function confirmIssuance() {
        if (!cart.length) return showToast('Empty List', 'Add items first', 'info');
        const beneficiary = document.getElementById('beneficiary').value;
        if (!beneficiary) return showToast('Missing Recipient', 'Enter beneficiary name', 'warning');

        const btn = document.getElementById('confirmBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Processing...';

        try {
            const res = await fetch("{{ route('issueitems.store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    issuance_date: document.getElementById('issuanceDate').value,
                    beneficiary: beneficiary,
                    issuance_type: document.getElementById('issuanceType').value,
                    items: cart
                })
            });
            const data = await res.json();
            if (data.success) {
                showToast('Dispatched', data.message, 'success');
                setTimeout(() => window.location.reload(), 2000);
            } else {
                showToast('Error', data.message, 'error');
            }
        } catch (e) {
            showToast('System Error', 'Could not complete request', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection