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
    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem;">
        
        <!-- Left Column: Inventory Catalog -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Catalog Search & Filter -->
            <div class="glass-card" style="padding: 1.5rem; border-radius: 20px;">
                <div style="position: relative; margin-bottom: 1rem;">
                    <i data-lucide="search" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); width: 20px; color: var(--text-muted);"></i>
                    <input type="text" placeholder="Search product name or reference ID..." style="width: 100%; padding: 1.25rem 1.25rem 1.25rem 3.5rem; border-radius: 14px; border: 2px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-size: 1rem; font-weight: 600; outline: none; transition: all 0.3s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'">
                </div>
                
                <div style="display: flex; gap: 0.75rem; overflow-x: auto; padding-bottom: 0.5rem; scrollbar-width: none;">
                    <button class="quick-filter-btn active">All Items</button>
                    <button class="quick-filter-btn">Stationary (A)</button>
                    <button class="quick-filter-btn">Cleaning (B)</button>
                    <button class="quick-filter-btn">IT & Acc. (C)</button>
                    <button class="quick-filter-btn">Equipment (J)</button>
                </div>
            </div>

            <!-- Product Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.25rem;">
                
                <!-- Sample Product Card 1 -->
                <div class="product-card glass-card">
                    <div class="product-badge">Ledge A</div>
                    <div style="height: 120px; display: flex; align-items: center; justify-content: center; background: rgba(99, 102, 241, 0.05); border-radius: 12px; margin-bottom: 1rem;">
                        <i data-lucide="package" style="width: 48px; height: 48px; color: var(--primary); opacity: 0.5;"></i>
                    </div>
                    <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 800; color: var(--text-main);">A4 Paper Box (5 Reams)</h4>
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1rem;">
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Stock Avail.</div>
                        <div style="font-size: 1.25rem; font-weight: 900; color: #10b981;">142</div>
                    </div>
                    <button class="add-to-cart-btn" onclick="showToast('Added', 'A4 Paper Box queued for disbursement', 'info')">
                        <i data-lucide="plus" style="width: 18px;"></i> Select Item
                    </button>
                </div>

                <!-- Sample Product Card 2 -->
                <div class="product-card glass-card">
                    <div class="product-badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.2);">Ledge C</div>
                    <div style="height: 120px; display: flex; align-items: center; justify-content: center; background: rgba(245, 158, 11, 0.05); border-radius: 12px; margin-bottom: 1rem;">
                        <i data-lucide="monitor" style="width: 48px; height: 48px; color: #f59e0b; opacity: 0.5;"></i>
                    </div>
                    <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 800; color: var(--text-main);">UPS Battery 650VA</h4>
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1rem;">
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Stock Avail.</div>
                        <div style="font-size: 1.25rem; font-weight: 900; color: #f59e0b;">12</div>
                    </div>
                    <button class="add-to-cart-btn" onclick="showToast('Added', 'UPS Battery queued for disbursement', 'info')">
                        <i data-lucide="plus" style="width: 18px;"></i> Select Item
                    </button>
                </div>

                <!-- Sample Product Card 3 -->
                <div class="product-card glass-card" style="opacity: 0.7;">
                    <div class="product-badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2);">Ledge B</div>
                    <div style="height: 120px; display: flex; align-items: center; justify-content: center; background: rgba(239, 68, 68, 0.05); border-radius: 12px; margin-bottom: 1rem;">
                        <i data-lucide="trash-2" style="width: 48px; height: 48px; color: #ef4444; opacity: 0.5;"></i>
                    </div>
                    <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 800; color: var(--text-main);">Hand Sanitizer (1L)</h4>
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1rem;">
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Stock Avail.</div>
                        <div style="font-size: 1.25rem; font-weight: 900; color: #ef4444;">0</div>
                    </div>
                    <button class="add-to-cart-btn" style="background: var(--bg-main); color: var(--text-muted); border-color: var(--border-color); cursor: not-allowed;">
                        <i data-lucide="x" style="width: 18px;"></i> Out of Stock
                    </button>
                </div>

                <!-- Sample Product Card 4 -->
                <div class="product-card glass-card">
                    <div class="product-badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2);">Ledge J</div>
                    <div style="height: 120px; display: flex; align-items: center; justify-content: center; background: rgba(16, 185, 129, 0.05); border-radius: 12px; margin-bottom: 1rem;">
                        <i data-lucide="printer" style="width: 48px; height: 48px; color: #10b981; opacity: 0.5;"></i>
                    </div>
                    <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 800; color: var(--text-main);">Printer Toner Cartridge</h4>
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1rem;">
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Stock Avail.</div>
                        <div style="font-size: 1.25rem; font-weight: 900; color: var(--text-main);">45</div>
                    </div>
                    <button class="add-to-cart-btn" onclick="showToast('Added', 'Printer Toner queued for disbursement', 'info')">
                        <i data-lucide="plus" style="width: 18px;"></i> Select Item
                    </button>
                </div>

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
                            <input type="date" value="{{ date('Y-m-d') }}" style="width: 100%; padding: 0.85rem; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-family: inherit;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">Beneficiary / Department</label>
                            <div style="position: relative;">
                                <i data-lucide="building" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 16px; color: var(--text-muted);"></i>
                                <input type="text" placeholder="e.g. IT Dept / John Doe" style="width: 100%; padding: 0.85rem 1rem 0.85rem 2.5rem; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">SIV Reference Number</label>
                            <input type="text" placeholder="SIV-0000" style="width: 100%; padding: 0.85rem; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-weight: 700; font-family: monospace;">
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
                    
                    <!-- Empty State (Hidden for preview) -->
                    <div style="display: none; padding: 2rem 0; text-align: center; color: var(--text-muted); border: 2px dashed var(--border-color); border-radius: 14px; margin-bottom: 1.5rem;">
                        <i data-lucide="package-open" style="width: 32px; height: 32px; opacity: 0.5; margin-bottom: 0.5rem;"></i>
                        <div style="font-size: 0.85rem; font-weight: 600;">No items selected yet.</div>
                    </div>

                    <!-- Cart Item -->
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 250px; overflow-y: auto; padding-right: 5px;">
                        
                        <div style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 14px; padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                <div style="font-weight: 800; color: var(--text-main); font-size: 0.95rem;">A4 Paper Box (5 Reams)</div>
                                <button style="background: transparent; border: none; color: #ef4444; cursor: pointer; padding: 0;">
                                    <i data-lucide="trash-2" style="width: 16px;"></i>
                                </button>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Stock: 142</div>
                                <div style="display: flex; align-items: center; gap: 10px; background: var(--bg-card); padding: 0.25rem; border-radius: 8px; border: 1px solid var(--border-color);">
                                    <button style="border: none; background: transparent; cursor: pointer; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 6px;" onmouseover="this.style.background='rgba(0,0,0,0.05)'" onmouseout="this.style.background='transparent'">
                                        <i data-lucide="minus" style="width: 14px;"></i>
                                    </button>
                                    <input type="text" value="2" style="width: 30px; border: none; background: transparent; text-align: center; font-weight: 800; font-size: 0.9rem; outline: none; color: var(--text-main);">
                                    <button style="border: none; background: transparent; cursor: pointer; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 6px;" onmouseover="this.style.background='rgba(0,0,0,0.05)'" onmouseout="this.style.background='transparent'">
                                        <i data-lucide="plus" style="width: 14px;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div style="padding: 1.5rem; background: rgba(99, 102, 241, 0.03); border-radius: 0 0 24px 24px; border-top: 1px solid var(--border-color);">
                    <button class="btn-primary" style="width: 100%; padding: 1.15rem; border-radius: 16px; border: none; background: var(--primary); color: white; font-size: 1rem; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3); transition: all 0.3s;" onclick="showToast('Success', 'Items formally issued and recorded into system history.', 'success')">
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
        padding: 1.25rem;
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

    @media (max-width: 1024px) {
        div[style*="display: grid; grid-template-columns: 1.5fr 1fr;"] {
            grid-template-columns: 1fr !important;
        }
        
        div[style*="position: sticky;"] {
            position: relative !important;
            top: 0 !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection
