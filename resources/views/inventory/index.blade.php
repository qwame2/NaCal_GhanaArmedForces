@extends('layouts.dashboard')

@section('content')
<div class="animate-slide-up">
    <!-- Header Section -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">Master Database</span>
                <span style="color: var(--text-muted); font-size: 0.85rem;">v2.0 Architecture</span>
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main); margin: 0;">Global <span style="color: var(--primary);">Inventory</span></h2>
            <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Comprehensive overview of all cataloged items across all ledges.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <button class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--text-main); transition: all 0.3s;" onmouseover="this.style.background='rgba(99, 102, 241, 0.05)'" onmouseout="this.style.background='var(--bg-card)'">
                <i data-lucide="printer" style="width: 18px;"></i> Print Ledger
            </button>
            <button class="btn-primary" style="padding: 0.75rem 1.25rem; border: none; background: var(--primary-gradient); color: white; border-radius: 12px; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);">
                <i data-lucide="download-cloud" style="width: 18px;"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Quick Analytics Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        
        <div class="glass-card" style="padding: 1.5rem; border-radius: 20px; display: flex; align-items: center; gap: 1.25rem; border-left: 4px solid var(--primary);">
            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(99, 102, 241, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="boxes" style="width: 28px; height: 28px;"></i>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Total Unique Items</div>
                <div style="color: var(--text-main); font-size: 1.75rem; font-weight: 900; line-height: 1;">842</div>
            </div>
        </div>

        <div class="glass-card" style="padding: 1.5rem; border-radius: 20px; display: flex; align-items: center; gap: 1.25rem; border-left: 4px solid #10b981;">
            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="check-circle" style="width: 28px; height: 28px;"></i>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Healthy Stock Items</div>
                <div style="color: var(--text-main); font-size: 1.75rem; font-weight: 900; line-height: 1;">615</div>
            </div>
        </div>

        <div class="glass-card" style="padding: 1.5rem; border-radius: 20px; display: flex; align-items: center; gap: 1.25rem; border-left: 4px solid #f59e0b;">
            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="alert-triangle" style="width: 28px; height: 28px;"></i>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Low Stock Alerts</div>
                <div style="color: var(--text-main); font-size: 1.75rem; font-weight: 900; line-height: 1;">118</div>
            </div>
        </div>

        <div class="glass-card" style="padding: 1.5rem; border-radius: 20px; display: flex; align-items: center; gap: 1.25rem; border-left: 4px solid #ef4444;">
            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="x-octagon" style="width: 28px; height: 28px;"></i>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Out of Stock</div>
                <div style="color: var(--text-main); font-size: 1.75rem; font-weight: 900; line-height: 1;">109</div>
            </div>
        </div>
    </div>

    <!-- Inventory Table Container -->
    <div class="glass-card" style="border-radius: 24px; padding: 1.5rem; display: flex; flex-direction: column;">
        
        <!-- Toolbar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            
            <div style="display: flex; gap: 1rem; align-items: center;">
                <div style="position: relative; width: 300px;">
                    <i data-lucide="search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 18px; color: var(--text-muted);"></i>
                    <input type="text" placeholder="Search by name, ref, or ledge..." style="width: 100%; padding: 0.85rem 1rem 0.85rem 2.5rem; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); outline: none; transition: border-color 0.3s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'">
                </div>
                
                <div style="display: flex; background: var(--bg-main); padding: 0.25rem; border-radius: 10px; border: 1px solid var(--border-color);">
                    <button style="padding: 0.5rem 1rem; background: var(--bg-card); border-radius: 8px; border: none; font-weight: 700; color: var(--text-main); cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">All</button>
                    <button style="padding: 0.5rem 1rem; background: transparent; border-radius: 8px; border: none; font-weight: 600; color: var(--text-muted); cursor: pointer;">Stationary</button>
                    <button style="padding: 0.5rem 1rem; background: transparent; border-radius: 8px; border: none; font-weight: 600; color: var(--text-muted); cursor: pointer;">Cleaning</button>
                    <button style="padding: 0.5rem 1rem; background: transparent; border-radius: 8px; border: none; font-weight: 600; color: var(--text-muted); cursor: pointer;">Hardware</button>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <button style="border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 10px; display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='var(--bg-card)'" onmouseout="this.style.background='var(--bg-main)'">
                    <i data-lucide="filter" style="width: 16px;"></i> Filter Status
                </button>
            </div>
            
        </div>

        <!-- Table -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0; min-width: 900px;">
                <thead>
                    <tr style="background: rgba(0,0,0,0.02);">
                        <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border-color); border-radius: 12px 0 0 12px;">Item Reference</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border-color);">Product Description</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border-color);">Classification</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border-color);">Total Receipts</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: center; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border-color);">Total Issues</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border-color);">Stock Avail.</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border-color);">System Health</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: right; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border-color); border-radius: 0 12px 12px 0;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Mock Row 1 -->
                    <tr class="inv-row" style="transition: background 0.3s;" onmouseover="this.style.background='var(--bg-main)'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-family: monospace; font-size: 0.9rem;">ITM-1029</td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                            <div style="font-weight: 800; color: var(--text-main); font-size: 1rem;">A4 Paper Box (5 Reams)</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-top: 4px;">Last Refill: 2 days ago</div>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                            <span style="background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 0.3rem 0.6rem; border-radius: 8px; font-size: 0.75rem; font-weight: 700;">Ledge A (Stationary)</span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-weight: 600; color: var(--text-main);">450</td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); text-align: center; font-weight: 600; color: var(--text-muted);">308</td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-weight: 900; color: var(--text-main); font-size: 1.1rem;">142</td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                            <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 0.3rem 0.6rem; border-radius: 8px; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;"><div style="width:6px; height:6px; border-radius:50%; background:#10b981;"></div> IN STOCK</span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); text-align: right;">
                            <button style="background: transparent; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.5rem; cursor: pointer; color: var(--text-main);" data-tooltip="View Details">
                                <i data-lucide="eye" style="width: 18px;"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Mock Row 2 -->
                    <tr class="inv-row" style="transition: background 0.3s;" onmouseover="this.style.background='var(--bg-main)'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-family: monospace; font-size: 0.9rem;">ITM-3144</td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                            <div style="font-weight: 800; color: var(--text-main); font-size: 1rem;">UPS Battery 650VA</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-top: 4px;">Last Refill: 4 months ago</div>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                            <span style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 0.3rem 0.6rem; border-radius: 8px; font-size: 0.75rem; font-weight: 700;">Ledge C (IT)</span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-weight: 600; color: var(--text-main);">20</td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); text-align: center; font-weight: 600; color: var(--text-muted);">8</td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-weight: 900; color: var(--text-main); font-size: 1.1rem;">12</td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                            <span style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 0.3rem 0.6rem; border-radius: 8px; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;"><div style="width:6px; height:6px; border-radius:50%; background:#f59e0b;"></div> LOW STOCK</span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); text-align: right;">
                            <button style="background: transparent; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.5rem; cursor: pointer; color: var(--text-main);" data-tooltip="View Details">
                                <i data-lucide="eye" style="width: 18px;"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Mock Row 3 -->
                    <tr class="inv-row" style="transition: background 0.3s;" onmouseover="this.style.background='var(--bg-main)'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-family: monospace; font-size: 0.9rem;">ITM-5091</td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                            <div style="font-weight: 800; color: var(--text-main); font-size: 1rem;">Hand Sanitizer (1L)</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-top: 4px;">Last Refill: 8 months ago</div>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                            <span style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 0.3rem 0.6rem; border-radius: 8px; font-size: 0.75rem; font-weight: 700;">Ledge B (Cleaning)</span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-weight: 600; color: var(--text-main);">100</td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); text-align: center; font-weight: 600; color: var(--text-muted);">100</td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-weight: 900; color: #ef4444; font-size: 1.1rem;">0</td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                            <span style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 0.3rem 0.6rem; border-radius: 8px; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;"><div style="width:6px; height:6px; border-radius:50%; background:#ef4444;"></div> OUT OF STOCK</span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); text-align: right;">
                            <button style="background: transparent; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.5rem; cursor: pointer; color: var(--text-main);" data-tooltip="View Details">
                                <i data-lucide="eye" style="width: 18px;"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Showing 1 to 3 of 842 records</div>
            <div style="display: flex; gap: 0.5rem;">
                <button style="width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-muted); display: flex; align-items: center; justify-content: center; cursor: not-allowed; opacity: 0.5;">
                    <i data-lucide="chevron-left" style="width: 18px;"></i>
                </button>
                <button style="width: 36px; height: 36px; border-radius: 10px; border: none; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; cursor: pointer;">1</button>
                <button style="width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-weight: 600; cursor: pointer;">2</button>
                <button style="width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-weight: 600; cursor: pointer;">3</button>
                <button style="width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-weight: 600; cursor: pointer;">
                    <i data-lucide="chevron-right" style="width: 18px;"></i>
                </button>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection
