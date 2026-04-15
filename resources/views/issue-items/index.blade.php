@extends('layouts.dashboard')

@section('content')
<div class="animate-slide-up">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">Disbursement Portal</span>
                <span style="color: var(--text-muted); font-size: 0.85rem;">Active Operation</span>
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main);">Issue <span style="color: var(--primary);">Items</span></h2>
            <p style="color: var(--text-muted);">Select products and assign them to departments or personnel.</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button onclick="window.location.reload()" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--text-main);">
                <i data-lucide="refresh-cw" style="width: 18px;"></i>
                Refresh Catalog
            </button>
        </div>
    </div>

    <!-- Main Workspace -->
    <div id="issuanceWorkspace" style="display: grid; grid-template-columns: minmax(0, 1fr) 310px; gap: 1rem;">
        
        <!-- Left Column: Inventory Catalog -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Catalog Search & Filter -->
            <div class="glass-card" style="padding: 2rem; border-radius: 20px;">
                <div style="position: relative; margin-bottom: 1rem;">
                    <i data-lucide="search" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); width: 20px; color: var(--text-muted);"></i>
                    <input type="text" placeholder="Search product name or reference ID..." style="width: 100%; padding: 1.25rem 1.25rem 1.25rem 3.5rem; border-radius: 14px; border: 2px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-size: 1rem; font-weight: 600; outline: none; transition: all 0.3s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'">
                </div>
                
                <div style="display: flex; gap: 0.75rem; overflow-x: auto; padding-bottom: 0.5rem; scrollbar-width: none;">
                    <button class="quick-filter-btn active" onclick="filterCategory('all', this)">All Items</button>
                    @foreach($ledgeMap as $code => $name)
                    <button class="quick-filter-btn" onclick="filterCategory('{{ $code }}', this)">{{ $name }} ({{ $code }})</button>
                    @endforeach
                </div>
            </div>

            <!-- Product Grid -->
            <div id="productGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.75rem;">
                
                @forelse($items as $item)
                @php
                    $icon = 'package';
                    $color = 'var(--primary)';
                    $bgColor = 'rgba(99, 102, 241, 0.05)';
                    $badgeBg = 'rgba(99, 102, 241, 0.1)';
                    $badgeBorder = 'rgba(99, 102, 241, 0.2)';

                    if ($item->ledge_category == 'B') {
                        $icon = 'trash-2';
                        $color = '#ef4444';
                        $bgColor = 'rgba(239, 68, 68, 0.05)';
                        $badgeBg = 'rgba(239, 68, 68, 0.1)';
                        $badgeBorder = 'rgba(239, 68, 68, 0.2)';
                    } elseif ($item->ledge_category == 'C') {
                        $icon = 'monitor';
                        $color = '#f59e0b';
                        $bgColor = 'rgba(245, 158, 11, 0.05)';
                        $badgeBg = 'rgba(245, 158, 11, 0.1)';
                        $badgeBorder = 'rgba(245, 158, 11, 0.2)';
                    } elseif ($item->ledge_category == 'J') {
                        $icon = 'printer';
                        $color = '#10b981';
                        $bgColor = 'rgba(16, 185, 129, 0.05)';
                        $badgeBg = 'rgba(16, 185, 129, 0.1)';
                        $badgeBorder = 'rgba(16, 185, 129, 0.2)';
                    } elseif ($item->ledge_category == 'G') {
                        $icon = 'activity';
                        $color = '#8b5cf6';
                        $bgColor = 'rgba(139, 92, 246, 0.05)';
                        $badgeBg = 'rgba(139, 92, 246, 0.1)';
                        $badgeBorder = 'rgba(139, 92, 246, 0.2)';
                    }

                    $isOutOfStock = $item->total_stock <= 0;
                @endphp
                
                <div class="product-card glass-card" data-category="{{ $item->ledge_category }}" data-description="{{ strtolower($item->description) }}" style="{{ $isOutOfStock ? 'opacity: 0.7;' : '' }}">
                    <div class="product-badge" style="background: {{ $badgeBg }}; color: {{ $color }}; border-color: {{ $badgeBorder }};">Ledge {{ $item->ledge_category }}</div>
                    <div style="height: 120px; display: flex; align-items: center; justify-content: center; background: {{ $bgColor }}; border-radius: 12px; margin-bottom: 1rem;">
                        <i data-lucide="{{ $icon }}" style="width: 48px; height: 48px; color: {{ $color }}; opacity: 0.5;"></i>
                    </div>
                    <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 800; color: var(--text-main);">{{ $item->description }}</h4>
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1rem;">
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Stock Avail.</div>
                        <div style="font-size: 1.25rem; font-weight: 900; color: {{ $isOutOfStock ? '#ef4444' : ($item->total_stock < 100 ? '#f59e0b' : '#10b981') }};">
                            {{ number_format($item->total_stock) }}
                        </div>
                    </div>
                    
                    @if($isOutOfStock)
                    <button class="add-to-cart-btn" style="background: var(--bg-main); color: var(--text-muted); border-color: var(--border-color); cursor: not-allowed;">
                        <i data-lucide="x" style="width: 18px;"></i> Out of Stock
                    </button>
                    @else
                    <button class="add-to-cart-btn" onclick="addToCart('{{ addslashes($item->description) }}', {{ (int)$item->total_stock }}, '{{ $item->ledge_category }}')">
                        <i data-lucide="plus" style="width: 18px;"></i> Select Item
                    </button>
                    @endif
                </div>
                @empty
                <div style="grid-column: 1 / -1; padding: 4rem; text-align: center; color: var(--text-muted);">
                    <i data-lucide="package-search" style="width: 48px; height: 48px; opacity: 0.3; margin-bottom: 1rem;"></i>
                    <h3 style="margin: 0; font-weight: 700;">No inventory items found.</h3>
                    <p>Stock must be added before it can be issued.</p>
                </div>
                @endforelse

            </div>
        </div>

        <!-- Right Column: Issuance Cart -->
        <div>
            <div class="glass-card" style="border-radius: 24px; position: sticky; top: 100px; padding: 0.5rem;">
                
                <!-- Beneficiary Details -->
                <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color);">
                    <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                        <i data-lucide="user-check" style="color: var(--primary);"></i> Recipient Details
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Issuance Date</label>
                            <input type="date" id="issuanceDate" value="{{ date('Y-m-d') }}" style="width: 100%; padding: 0.85rem; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-family: inherit;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Beneficiary / Department</label>
                            <div style="position: relative;">
                                <i data-lucide="building" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 16px; color: var(--text-muted);"></i>
                                <input type="text" id="beneficiary" placeholder="e.g. IT Dept / John Doe" style="width: 100%; padding: 0.85rem 1rem 0.85rem 2.5rem; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Issuance Type</label>
                            <div id="issueTypeSlider" style="display: flex; background: var(--bg-main); padding: 0.4rem; border-radius: 12px; border: 1px solid var(--border-color); position: relative; gap: 5px;">
                                <div id="sliderPill" style="position: absolute; top: 0.4rem; left: 0.4rem; width: calc(50% - 0.5rem); height: calc(100% - 0.8rem); background: var(--primary); border-radius: 8px; transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); z-index: 1;"></div>
                                <button type="button" class="issue-type-btn active" onclick="setIssueType('Permanent', this, 0)" style="flex: 1; padding: 0.75rem; border: none; border-radius: 8px; font-weight: 800; font-size: 0.8rem; cursor: pointer; transition: all 0.3s; background: transparent; color: white; position: relative; z-index: 2;">PERMANENT</button>
                                <button type="button" class="issue-type-btn" onclick="setIssueType('Temporary', this, 1)" style="flex: 1; padding: 0.75rem; border: none; border-radius: 8px; font-weight: 800; font-size: 0.8rem; cursor: pointer; transition: all 0.3s; background: transparent; color: var(--text-muted); position: relative; z-index: 2;">TEMPORARY</button>
                                <input type="hidden" id="issuanceType" value="Permanent">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected Items -->
                <div style="padding: 1.5rem;">
                    <h3 style="margin: 0 0 1rem; font-size: 1.1rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i data-lucide="shopping-cart" style="color: var(--primary);"></i> Disbursement List
                        </div>
                        <span style="background: var(--primary); color: white; padding: 2px 8px; border-radius: 20px; font-size: 0.8rem;">1 Item</span>
                    </h3>
                    
                    <!-- Empty State -->
                    <div id="cartEmptyState" style="padding: 2rem 0; text-align: center; color: var(--text-muted); border: 2px dashed var(--border-color); border-radius: 14px; margin-bottom: 1.5rem;">
                        <i data-lucide="package-open" style="width: 32px; height: 32px; opacity: 0.5; margin-bottom: 0.5rem;"></i>
                        <div style="font-size: 0.85rem; font-weight: 600;">No items selected yet.</div>
                    </div>

                    <!-- Selected Items Container -->
                    <div id="cartItemsContainer" style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 250px; overflow-y: auto; padding-right: 5px;">
                        <!-- Cart Items will be injected here -->
                    </div>
                </div>

                <div style="padding: 1.5rem; background: rgba(99, 102, 241, 0.03); border-radius: 0 0 24px 24px; border-top: 1px solid var(--border-color);">
                    <button id="confirmBtn" class="btn-primary" style="width: 100%; padding: 1.15rem; border-radius: 16px; border: none; background: var(--primary); color: white; font-size: 1rem; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3); transition: all 0.3s;" onclick="confirmIssuance()">
                        <i data-lucide="send" style="width: 20px;"></i> Confirm Issuance
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .quick-filter-btn {
        padding: 0.6rem 1.25rem;
        border-radius: 999px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-main);
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.3s;
    }
    
    .quick-filter-btn:hover {
        background: rgba(99, 102, 241, 0.05);
        border-color: rgba(99, 102, 241, 0.3);
    }
    
    .quick-filter-btn.active {
        background: var(--primary);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
    }

    .product-card {
        padding: 1.75rem;
        border-radius: 20px;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .product-badge {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        background: rgba(99, 102, 241, 0.1);
        color: var(--primary);
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.65rem;
        font-weight: 900;
        letter-spacing: 0.5px;
        border: 1px solid rgba(99, 102, 241, 0.2);
    }

    .add-to-cart-btn {
        margin-top: auto;
        width: 100%;
        padding: 0.85rem;
        border-radius: 12px;
        border: 2px solid var(--primary);
        background: transparent;
        color: var(--primary);
        font-weight: 800;
        font-size: 0.9rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .add-to-cart-btn:hover:not(:disabled) {
        background: var(--primary);
        color: white;
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.2);
    }

    .content-body {
        padding: 1.5rem 2rem !important;
    }

    @media (max-width: 1366px) {
        #issuanceWorkspace {
            gap: 1rem !important;
            grid-template-columns: minmax(0, 1fr) 280px !important;
        }
    }

    @media (max-width: 1200px) {
        #issuanceWorkspace {
            grid-template-columns: 1fr !important;
            gap: 2rem !important;
        }
    }

    @media (max-width: 1024px) {
        #issuanceWorkspace {
            grid-template-columns: 1fr !important;
        }
        
        div[style*="position: sticky;"] {
            position: relative !important;
            top: 0 !important;
        }
    }
</style>

<script>
    let cart = [];

    function updateCartUI() {
        const container = document.getElementById('cartItemsContainer');
        const emptyState = document.getElementById('cartEmptyState');
        const cartBadge = document.querySelector('h3 span[style*="background: var(--primary)"]');
        
        container.innerHTML = '';
        
        if (cart.length === 0) {
            emptyState.style.display = 'block';
            cartBadge.style.display = 'none';
        } else {
            emptyState.style.display = 'none';
            cartBadge.style.display = 'inline-block';
            cartBadge.textContent = `${cart.length} ${cart.length === 1 ? 'Item' : 'Items'}`;
            
            cart.forEach((item, index) => {
                const itemEl = document.createElement('div');
                itemEl.className = 'animate-slide-up';
                itemEl.style.cssText = 'background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 14px; padding: 1rem; margin-bottom: 0.75rem;';
                itemEl.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                        <div style="font-weight: 800; color: var(--text-main); font-size: 0.95rem;">${item.description}</div>
                        <button onclick="removeFromCart(${index})" style="background: transparent; border: none; color: #ef4444; cursor: pointer; padding: 0;">
                            <i data-lucide="trash-2" style="width: 16px;"></i>
                        </button>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Stock: ${item.maxStock}</div>
                        <div style="display: flex; align-items: center; gap: 10px; background: var(--bg-card); padding: 0.25rem; border-radius: 8px; border: 1px solid var(--border-color);">
                            <button onclick="updateQty(${index}, -1)" style="border: none; background: transparent; cursor: pointer; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 6px;" onmouseover="this.style.background='rgba(0,0,0,0.05)'" onmouseout="this.style.background='transparent'">
                                <i data-lucide="minus" style="width: 14px;"></i>
                            </button>
                            <input type="number" value="${item.qty}" min="1" max="${item.maxStock}" onchange="manualUpdateQty(${index}, this.value)" style="width: 45px; border: none; background: transparent; text-align: center; font-weight: 800; font-size: 0.9rem; outline: none; color: var(--text-main); -moz-appearance: textfield;">
                            <button onclick="updateQty(${index}, 1)" style="border: none; background: transparent; cursor: pointer; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 6px;" onmouseover="this.style.background='rgba(0,0,0,0.05)'" onmouseout="this.style.background='transparent'">
                                <i data-lucide="plus" style="width: 14px;"></i>
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(itemEl);
            });
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    function addToCart(description, maxStock, category) {
        const existing = cart.find(i => i.description === description);
        if (existing) {
            if (existing.qty < maxStock) {
                existing.qty++;
                showToast('Updated', `Incremented quantity for ${description}`, 'info');
            } else {
                showToast('Limit Reached', `Only ${maxStock} units available in stock.`, 'warning');
            }
        } else {
            cart.push({ description, maxStock, category, qty: 1 });
            showToast('Added', `${description} added to disbursement list.`, 'success');
        }
        updateCartUI();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        updateCartUI();
    }

    function updateQty(index, delta) {
        const item = cart[index];
        const newQty = parseInt(item.qty) + delta;
        if (newQty > 0 && newQty <= item.maxStock) {
            item.qty = newQty;
            updateCartUI();
        } else if (newQty > item.maxStock) {
            showToast('Stock Limit', `Only ${item.maxStock} units available.`, 'warning');
        }
    }

    function manualUpdateQty(index, value) {
        let newQty = parseInt(value);
        const item = cart[index];
        
        if (isNaN(newQty) || newQty < 1) {
            newQty = 1;
            showToast('Invalid Input', 'Quantity must be at least 1.', 'warning');
        } else if (newQty > item.maxStock) {
            newQty = item.maxStock;
            showToast('Stock Limit', `Adjusted to maximum available (${item.maxStock}).`, 'warning');
        }
        
        item.qty = newQty;
        updateCartUI();
    }

    async function confirmIssuance() {
        if (cart.length === 0) {
            showToast('Empty Cart', 'Please add items to the disbursement list first.', 'info');
            return;
        }

        const date = document.getElementById('issuanceDate').value;
        const beneficiary = document.getElementById('beneficiary').value;
        const type = document.getElementById('issuanceType').value;

        if (!beneficiary) {
            showToast('Missing Info', 'Please provide beneficiary details.', 'warning');
            return;
        }

        const confirmBtn = document.getElementById('confirmBtn');
        const originalContent = confirmBtn.innerHTML;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<div class="loader" style="width: 20px; height: 20px; border-width: 2px;"></div> Processing...';

        try {
            const response = await fetch("{{ route('issueitems.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    issuance_date: date,
                    beneficiary: beneficiary,
                    issuance_type: type,
                    items: cart
                })
            });

            const result = await response.json();

            if (result.success) {
                showToast('Success', result.message, 'success');
                cart = [];
                updateCartUI();
                document.getElementById('beneficiary').value = '';
                
                // Refresh catalog after a short delay to see updated stock
                setTimeout(() => window.location.reload(), 2000);
            } else {
                showToast('Error', result.message, 'error');
            }
        } catch (error) {
            console.error('Issuance error:', error);
            showToast('Error', 'A system error occurred. Please try again.', 'error');
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalContent;
        }
    }

    function filterCategory(category, btn) {
        document.querySelectorAll('.quick-filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function setIssueType(type, btn, index) {
        document.getElementById('issuanceType').value = type;
        
        // Update Pill Position
        const pill = document.getElementById('sliderPill');
        pill.style.transform = `translateX(calc(${index * 100}% + ${index * 5}px))`;
        
        // Update Buttons
        document.querySelectorAll('.issue-type-btn').forEach(b => {
            b.style.color = 'var(--text-muted)';
            b.classList.remove('active');
        });
        btn.style.color = 'white';
        btn.classList.add('active');
        
        showToast('Mode Switched', `Issuance set to ${type} mode.`, 'info');
    }

    // Search Logic
    const searchInput = document.querySelector('input[placeholder*="Search product"]');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.product-card');
            cards.forEach(card => {
                const desc = card.dataset.description;
                if (desc.includes(term)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        updateCartUI();
    });
</script>
@endsection
