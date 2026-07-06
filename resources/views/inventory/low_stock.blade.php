@extends((auth()->user()->is_admin && auth()->user()->role !== 'Main Admin') ? 'layouts.admin' : 'layouts.dashboard')

@section('content')
<div class="animate-slide-up">
    <!-- Header Section -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">Stock Monitor</span>
                <span style="color: var(--text-muted); font-size: 0.85rem;">Safety Threshold Monitoring</span>
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main); margin: 0;">Low Stock <span style="color: var(--primary);">Monitor</span></h2>
            <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Review and replenish items that are running below safety limits.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('notifications.index') }}" class="glass-card" style="padding: 0.75rem 1.25rem; border: 1px solid var(--border-color); border-radius: 12px; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--text-main); background: var(--bg-card); transition: all 0.3s;" onmouseover="this.style.background='rgba(99, 102, 241, 0.05)'" onmouseout="this.style.background='var(--bg-card)'">
                <i data-lucide="bell" style="width: 18px;"></i> Notifications Center
            </a>
            <button onclick="window.print()" class="glass-card" style="padding: 0.75rem 1.25rem; border: 1px solid var(--border-color); border-radius: 12px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--text-main); background: var(--bg-card); transition: all 0.3s;" onmouseover="this.style.background='rgba(99, 102, 241, 0.05)'" onmouseout="this.style.background='var(--bg-card)'">
                <i data-lucide="printer" style="width: 18px;"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Quick Analytics Row -->
    @php
        $totalItems = count($lowStockItems);
        $outOfStock = 0;
        $affectedLedges = [];
        foreach ($lowStockItems as $item) {
            if ((float)$item->stock_balance === 0.0) {
                $outOfStock++;
            }
            $affectedLedges[$item->ledge_category] = true;
        }
        $ledgesCount = count($affectedLedges);
    @endphp
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="glass-card" style="padding: 1.5rem; border-radius: 20px; display: flex; align-items: center; gap: 1.25rem; border-left: 4px solid #f59e0b; background: var(--bg-card); border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="alert-triangle" style="width: 28px; height: 28px;"></i>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Low Stock Items</div>
                <div style="color: var(--text-main); font-size: 1.75rem; font-weight: 900; line-height: 1;">{{ $totalItems }}</div>
            </div>
        </div>

        <div class="glass-card" style="padding: 1.5rem; border-radius: 20px; display: flex; align-items: center; gap: 1.25rem; border-left: 4px solid #ef4444; background: var(--bg-card); border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="x-octagon" style="width: 28px; height: 28px;"></i>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Out of Stock</div>
                <div style="color: var(--text-main); font-size: 1.75rem; font-weight: 900; line-height: 1;">{{ $outOfStock }}</div>
            </div>
        </div>

        <div class="glass-card" style="padding: 1.5rem; border-radius: 20px; display: flex; align-items: center; gap: 1.25rem; border-left: 4px solid var(--primary); background: var(--bg-card); border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(99, 102, 241, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-lucide="layers" style="width: 28px; height: 28px;"></i>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Categories Affected</div>
                <div style="color: var(--text-main); font-size: 1.75rem; font-weight: 900; line-height: 1;">{{ $ledgesCount }}</div>
            </div>
        </div>
    </div>

    <!-- Monitor Card Container -->
    <div class="glass-card" style="border-radius: 24px; padding: 1.5rem; display: flex; flex-direction: column; background: var(--bg-card); border: 1px solid var(--border-color);">
        <!-- Toolbar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div style="position: relative; width: 320px;">
                <i data-lucide="search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 18px; color: var(--text-muted);"></i>
                <input type="text" id="monitor-search" placeholder="Search low stock items..." style="width: 100%; padding: 0.85rem 1rem 0.85rem 2.5rem; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); outline: none; transition: border-color 0.3s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'">
            </div>
            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;" id="monitor-row-count">
                Showing {{ count($lowStockItems) }} item(s) below threshold
            </div>
        </div>

        <!-- Table -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0; min-width: 800px;">
                <thead>
                    <tr style="background: rgba(0,0,0,0.01);">
                        <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border-color); border-radius: 12px 0 0 12px;">Product Description</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border-color);">Classification</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border-color);">Current Stock</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border-color);">Safety Threshold</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border-color);">Status</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: right; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border-color); border-radius: 0 12px 12px 0;">Action</th>
                    </tr>
                </thead>
                <tbody id="monitor-table-body">
                    @php
                        function getLedgeBadgeStyle($category) {
                            $colors = [
                                'A' => ['bg' => 'rgba(99, 102, 241, 0.1)', 'color' => 'var(--primary)'],
                                'B' => ['bg' => 'rgba(239, 68, 68, 0.1)', 'color' => '#ef4444'],
                                'C' => ['bg' => 'rgba(245, 158, 11, 0.1)', 'color' => '#f59e0b'],
                                'D' => ['bg' => 'rgba(16, 185, 129, 0.1)', 'color' => '#10b981'],
                                'E' => ['bg' => 'rgba(59, 130, 246, 0.1)', 'color' => '#3b82f6'],
                                'G' => ['bg' => 'rgba(139, 92, 246, 0.1)', 'color' => '#8b5cf6'],
                                'J' => ['bg' => 'rgba(236, 72, 153, 0.1)', 'color' => '#ec4899'],
                            ];
                            return $colors[strtoupper($category)] ?? ['bg' => 'rgba(100, 116, 139, 0.1)', 'color' => '#64748b'];
                        }
                    @endphp
                    @forelse($lowStockItems as $item)
                        @php
                            $badge = getLedgeBadgeStyle($item->ledge_category);
                            $isOutOfStock = (float)$item->stock_balance === 0.0;
                        @endphp
                        <tr class="monitor-row" data-search-term="{{ strtolower($item->description) }} {{ strtolower($item->category_name) }}" style="transition: background 0.3s;" onmouseover="this.style.background='var(--bg-main)'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-weight: 800; color: var(--text-main); font-size: 0.95rem;">
                                {{ $item->description }}
                            </td>
                            <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                                <span style="background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.75rem; font-weight: 800; white-space: nowrap;">
                                    Ledge {{ $item->ledge_category }} ({{ $item->category_name }})
                                </span>
                            </td>
                            <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-weight: 900; color: {{ $isOutOfStock ? '#ef4444' : '#f59e0b' }}; font-size: 1.1rem;">
                                {{ number_format($item->stock_balance, 0) }} <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">{{ $item->unit }}</span>
                            </td>
                            <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-weight: 700; color: var(--text-main); font-size: 1rem;">
                                {{ number_format($item->threshold, 0) }} <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">{{ $item->unit }}</span>
                            </td>
                            <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                                @if($isOutOfStock)
                                    <span style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 0.3rem 0.6rem; border-radius: 8px; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px;"><div style="width:6px; height:6px; border-radius:50%; background:#ef4444;"></div> OUT OF STOCK</span>
                                @else
                                    <span style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 0.3rem 0.6rem; border-radius: 8px; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px;"><div style="width:6px; height:6px; border-radius:50%; background:#f59e0b;"></div> LOW STOCK</span>
                                @endif
                            </td>
                            <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); text-align: right;">
                                <a href="{{ auth()->user()->is_admin ? route('admin.inventory', ['search' => $item->description, 'from' => 'low-stock']) : route('receiveditems', ['search' => $item->description, 'from' => 'low-stock']) }}" class="glass-card" style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 0.5rem 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; text-decoration: none; color: var(--text-main); font-weight: 700; font-size: 0.75rem; background: var(--bg-card); transition: all 0.3s;" onmouseover="this.style.background='var(--bg-main)'; this.style.borderColor='var(--primary)'" onmouseout="this.style.background='var(--bg-card)'; this.style.borderColor='var(--border-color)'">
                                    <i data-lucide="eye" style="width: 14px; height: 14px;"></i> View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-state-row">
                            <td colspan="6" style="padding: 8rem 2rem; text-align: center;">
                                <div style="background: var(--bg-main); width: 100px; height: 100px; border-radius: 30px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem auto; color: #10b981; border: 1px solid var(--border-color); box-shadow: 0 15px 35px rgba(0,0,0,0.03);">
                                    <i data-lucide="check-circle" style="width: 48px; opacity: 0.8;"></i>
                                </div>
                                <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">All stock levels healthy!</h3>
                                <p style="color: var(--text-muted); font-size: 1rem;">There are currently no items under their safety threshold limits.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Local Table Search Logic
        const searchInput = document.getElementById('monitor-search');
        const rows = document.querySelectorAll('.monitor-row');
        const countDisplay = document.getElementById('monitor-row-count');
        const tbody = document.getElementById('monitor-table-body');

        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase().trim();
                let visibleCount = 0;

                rows.forEach(row => {
                    const term = row.getAttribute('data-search-term');
                    if (term && term.includes(query)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (countDisplay) {
                    countDisplay.textContent = `Showing ${visibleCount} matching item(s) below threshold`;
                }

                // Manage Dynamic Search Empty State
                let localEmptyRow = document.getElementById('local-empty-state-row');
                if (visibleCount === 0 && rows.length > 0) {
                    if (!localEmptyRow) {
                        localEmptyRow = document.createElement('tr');
                        localEmptyRow.id = 'local-empty-state-row';
                        localEmptyRow.innerHTML = `
                            <td colspan="6" style="padding: 6rem 2rem; text-align: center; color: var(--text-muted);">
                                <i data-lucide="search-code" style="width: 32px; height: 32px; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p style="font-size: 0.9rem; font-weight: 700; color: var(--text-main);">No matching items found</p>
                                <p style="font-size: 0.8rem;">Try adjusting your search keywords.</p>
                            </td>
                        `;
                        tbody.appendChild(localEmptyRow);
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                } else if (localEmptyRow) {
                    localEmptyRow.remove();
                }
            });
        }
    });
</script>
@endsection
