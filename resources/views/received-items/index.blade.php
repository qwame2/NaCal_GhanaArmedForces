@extends('layouts.dashboard')

@section('content')
<div class="animate-slide-up">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">Inventory Log</span>
                <span style="color: var(--text-muted); font-size: 0.85rem;">Historical Records</span>
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main);">Received <span style="color: var(--primary);">Items</span></h2>
            <p style="color: var(--text-muted);">View all items received into the inventory system.</p>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button onclick="window.location.reload()" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--text-main);">
                <i data-lucide="refresh-cw" style="width: 18px;"></i>
                Refresh
            </button>
            <button onclick="window.print()" class="glass-card" style="padding: 0.75rem 1.25rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--text-main);">
                <i data-lucide="printer" style="width: 18px;"></i>
                Export Report
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="glass-card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 48px; height: 48px; background: rgba(99, 102, 241, 0.1); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="package" style="width: 24px;"></i>
            </div>
            <div>
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Total Batches</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);">{{ $totalReceived }}</div>
            </div>
        </div>
        <div class="glass-card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="layers" style="width: 24px;"></i>
            </div>
            <div>
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Total Items Recorded</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);">{{ $totalItemsCount }}</div>
            </div>
        </div>
        <div class="glass-card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="trending-up" style="width: 24px;"></i>
            </div>
            <div>
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Recent Batches</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);">{{ $recentReceived }}</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-card search-container-mobile" style="padding: 1.5rem; margin-bottom: 2rem;">
        <form id="filterForm" action="{{ route('receiveditems') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: flex-end;">
            <input type="hidden" name="ledge_category" id="ledgeCategoryInput" value="{{ request('ledge_category') }}">
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase;">Search Items</label>
                <div style="position: relative;">
                    <i data-lucide="search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 16px; color: var(--text-muted);"></i>
                    <input type="text" name="search" id="searchInput" value="{{ request('search') }}" placeholder="Description or Batch ID..." style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-main); color: var(--text-main);">
                </div>
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase;">Supplier</label>
                <input type="text" name="supplier" id="supplierInput" value="{{ request('supplier') }}" placeholder="Supplier name..." style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-main); color: var(--text-main);">
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase;">Date From</label>
                <input type="date" name="date_from" id="dateInput" value="{{ request('date_from') }}" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-main); color: var(--text-main);">
            </div>
            <div class="filter-buttons-mobile" style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn-primary" style="flex: 1; padding: 0.75rem; border-radius: 10px; border: none; background: var(--primary); color: white; cursor: pointer; font-weight: 600;">Filter</button>
                <a href="{{ route('receiveditems') }}" class="glass-card" style="padding: 0.75rem; border-radius: 10px; color: var(--text-main); display: flex; align-items: center; justify-content: center; width: 44px; text-decoration: none;">
                    <i data-lucide="x" style="width: 18px;"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Quick Ledge Filters Carousel -->
    <div class="category-carousel-wrapper" style="position: relative; margin-bottom: 3rem;">
        <!-- Left Nav Arrow -->
        <button type="button" id="prevLedge" style="position: absolute; left: -10px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; border-radius: 50%; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); display: flex; align-items: center; justify-content: center; z-index: 10; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.1); opacity: 0; transition: all 0.3s; pointer-events: none;">
            <i data-lucide="chevron-left" style="width: 18px;"></i>
        </button>

        <div class="quick-filters-container" id="ledgeScroll" style="display: flex; gap: 0.75rem; overflow-x: auto; padding: 0.5rem 0.25rem; white-space: nowrap; scroll-behavior: smooth;">
            <button type="button" class="quick-ledge-btn {{ !request('ledge_category') ? 'active' : '' }}" data-ledge="" style="padding: 0.65rem 1.4rem; border-radius: 999px; border: 1.5px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-weight: 800; cursor: pointer; transition: all 0.3s; font-size: 0.85rem;">All Groups</button>
            @foreach($ledgeMap as $code => $name)
            <button type="button" class="quick-ledge-btn {{ request('ledge_category') == $code ? 'active' : '' }}" data-ledge="{{ $code }}" style="padding: 0.65rem 1.4rem; border-radius: 999px; border: 1.5px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-weight: 700; cursor: pointer; transition: all 0.3s; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                <span style="width: 8px; height: 8px; background: #94a3b8; border-radius: 50%;"></span> Ledge {{ $code }} ({{ $name }})
            </button>
            @endforeach
        </div>

        <!-- Right Nav Arrow -->
        <button type="button" id="nextLedge" style="position: absolute; right: -10px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; border-radius: 50%; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); display: flex; align-items: center; justify-content: center; z-index: 10; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.3s;">
            <i data-lucide="chevron-right" style="width: 18px;"></i>
        </button>
    </div>

    <style>
        .quick-filters-container {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }

        .quick-filters-container::-webkit-scrollbar {
            display: none !important;
        }

        .quick-ledge-btn.active {
            border-color: var(--primary) !important;
            background: rgba(99, 102, 241, 0.1) !important;
            color: var(--primary) !important;
        }

        .quick-ledge-btn.active span {
            background: var(--primary) !important;
        }
    </style>

    <!-- Results Table -->
    <div id="resultsContainer" class="glass-card" style="overflow: visible; position: relative; border-radius: 20px;">
        <div class="table-scroll-wrapper">
            <table class="activity-table" style="width: 100%; min-width: 800px; border-collapse: collapse;">
                <thead>
                    <tr style="background: rgba(0,0,0,0.02); text-align: left;">
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Date</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Description</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Category</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Supplier</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Status</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Folio</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Ledge</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Stock</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Variance</th>
                        <th style="padding: 1.25rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receivedItems as $item)
                    <tr class="activity-row" style="border-top: 1px solid var(--border-color);">
                        <td data-label="Date" style="padding: 1.25rem 1.5rem; color: var(--text-muted);">{{ \Carbon\Carbon::parse($item->entry_date)->format('M d, Y') }}</td>
                        <td data-label="Description" style="padding: 1.25rem 1.5rem;">
                            <div style="font-weight: 700; color: var(--text-main);">{{ $item->description }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Batch #{{ $item->batch_id }}</div>
                        </td>
                        <td data-label="Category" style="padding: 1.25rem 1.5rem;">
                            <span style="font-size: 0.75rem; background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 600;">
                                {{ $ledgeMap[$item->ledge_category] ?? "Ledge " . $item->ledge_category }}
                            </span>
                        </td>
                        @php
                        $rawSupplier = $item->supplier_name;
                        $cleanSupplier = preg_replace('/\s\[.*\]$/', '', $rawSupplier);
                        $status = 'N/A';
                        if (preg_match('/\[(.*)\]/', $rawSupplier, $matches)) {
                        $status = $matches[1];
                        }
                        $statusColor = '#94a3b8';
                        if ($status === 'Donor Action') $statusColor = '#3b82f6';
                        elseif ($status === 'Full Delivery') $statusColor = '#10b981';
                        elseif ($status === 'Partial Delivery') $statusColor = '#ef4444';
                        @endphp
                        <td data-label="Supplier" style="padding: 1.25rem 1.5rem; color: var(--text-main); font-weight: 500;">{{ $cleanSupplier }}</td>
                        <td data-label="Status" style="padding: 1.25rem 1.5rem;">
                            <span style="font-size: 0.7rem; font-weight: 800; color: white; background: {{ $statusColor }}; padding: 0.35rem 0.8rem; border-radius: 8px; text-transform: uppercase; box-shadow: 0 4px 10px {{ $statusColor }}30;">
                                {{ $status }}
                            </span>
                        </td>
                        <td data-label="Folio" style="padding: 1.25rem 1.5rem; font-family: monospace; color: var(--text-muted);">{{ $item->folio }}</td>
                        <td data-label="ledge_balance" style="padding: 1.25rem 1.5rem;">
                            <span style="font-weight: 800; color: {{ (float)$item->ledge_balance > 0 ? '#3b82f6' : '#1e40af' }};">
                                {{ $item->ledge_balance }}
                            </span>
                        </td>
                        <td data-label="Balance" style="padding: 1.25rem 1.5rem; color: var(--text-main); font-weight: 700;">{{ $item->stock_balance }}</td>
                        <td data-label="Qty" style="padding: 1.25rem 1.5rem;">
                            <span style="font-weight: 800; color: {{ is_numeric($item->variance) && (float)$item->variance > 0 ? '#10b981' : (is_numeric($item->variance) && (float)$item->variance < 0 ? '#ef4444' : '#94a3b8') }};">
                                {{ is_numeric($item->variance) && (float)$item->variance > 0 ? '+' : '' }}{{ $item->variance }}
                            </span>
                        </td>

                        <td data-label="Action" style="padding: 1.25rem 1.5rem; text-align: right; display: flex; justify-content: flex-end;">
                            <div class="action-dropdown-wrapper">
                                <button type="button" class="glass-btn-sm" title="Actions" onclick="toggleActionMenu('{{ $item->batch_id }}')" style="padding: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                    <i data-lucide="more-vertical" style="width: 18px;"></i>
                                </button>
                                <div id="actionMenu-{{ $item->batch_id }}" class="action-menu">
                                    @if($status === 'Partial Delivery')
                                    <button onclick="continueDelivery('{{ $item->batch_id }}')" class="menu-item" style="color: #f59e0b;">
                                        <i data-lucide="package-plus"></i>
                                        Continue Delivery
                                    </button>
                                    @endif
                                    <button onclick="openModal('{{ $item->batch_id }}')" class="menu-item">
                                        <i data-lucide="eye"></i>
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="padding: 7rem 2rem; text-align: center;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
                                <div style="background: var(--bg-main); width: 84px; height: 84px; border-radius: 24px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); box-shadow: 0 10px 40px rgba(0,0,0,0.04); border: 1px solid var(--border-color);">
                                    <i data-lucide="package-search" style="width: 38px; opacity: 0.6;"></i>
                                </div>
                                <div style="max-width: 450px; margin: 0 auto;">
                                    <h4 style="font-size: 1.35rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.75rem; letter-spacing: -0.02em;">No Records Discovered</h4>
                                    <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.6;">Your inventory ledger is currently empty or no items match your current search filters. Try broadening your criteria or record a new batch.</p>

                                    <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                                        <a href="{{ route('receiveditems') }}" class="glass-card" style="padding: 0.75rem 1.5rem; border-radius: 12px; text-decoration: none; font-size: 0.9rem; color: var(--text-main); font-weight: 700; transition: all 0.3s; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.5rem;">
                                            <i data-lucide="refresh-ccw" style="width: 16px;"></i>
                                            Reset Filters
                                        </a>
                                        <button onclick="window.location.href='/'" class="btn-primary" style="padding: 0.75rem 1.5rem; border-radius: 12px; border: none; font-size: 0.9rem; background: var(--primary); color: white; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);">
                                            <i data-lucide="plus-circle" style="width: 18px;"></i>
                                            New Inventory Entry
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Advanced Pagination Footer -->
        <div class="pagination-footer">
            <div class="pagination-container">
                <div class="pagination-info">
                    <span class="pagination-info-badge">
                        <i data-lucide="layers" style="width: 14px; height: 14px; display: inline; margin-right: 6px;"></i>
                        {{ $receivedItems->total() }} Total Records
                    </span>
                    <span class="pagination-stats">
                        Showing {{ $receivedItems->firstItem() ?? 0 }} - {{ $receivedItems->lastItem() ?? 0 }}
                    </span>
                </div>

                <div class="pagination-nav">
                    @if ($receivedItems->onFirstPage())
                    <span class="pagination-arrow disabled">
                        <i data-lucide="chevron-left" style="width: 20px;"></i>
                    </span>
                    @else
                    <a href="{{ $receivedItems->previousPageUrl() }}" class="pagination-arrow">
                        <i data-lucide="chevron-left" style="width: 20px;"></i>
                    </a>
                    @endif

                    <div class="pagination-numbers">
                        @php
                        $currentPage = $receivedItems->currentPage();
                        $lastPage = $receivedItems->lastPage();
                        $start = max($currentPage - 2, 1);
                        $end = min($currentPage + 2, $lastPage);
                        @endphp

                        @if($start > 1)
                        <a href="{{ $receivedItems->url(1) }}" class="pagination-number">1</a>
                        @if($start > 2)
                        <span class="pagination-dots">...</span>
                        @endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            <a href="{{ $receivedItems->url($i) }}"
                            class="pagination-number {{ $currentPage == $i ? 'active' : '' }}">
                            {{ $i }}
                            </a>
                            @endfor

                            @if($end < $lastPage)
                                @if($end < $lastPage - 1)
                                <span class="pagination-dots">...</span>
                                @endif
                                <a href="{{ $receivedItems->url($lastPage) }}" class="pagination-number">{{ $lastPage }}</a>
                                @endif
                    </div>

                    @if ($receivedItems->hasMorePages())
                    <a href="{{ $receivedItems->nextPageUrl() }}" class="pagination-arrow">
                        <i data-lucide="chevron-right" style="width: 20px;"></i>
                    </a>
                    @else
                    <span class="pagination-arrow disabled">
                        <i data-lucide="chevron-right" style="width: 20px;"></i>
                    </span>
                    @endif
                </div>

                <div class="pagination-per-page">
                    <select class="per-page-select" onchange="window.location.href=this.value">
                        @foreach([10, 25, 50, 100] as $perPage)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $perPage]) }}"
                            {{ request('per_page', 15) == $perPage ? 'selected' : '' }}>
                            {{ $perPage }} per page
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Detail Modal -->
<div id="detailModal" class="modal-backdrop">
    <div class="modal-content glass-card animate-scale-up">
        <div class="modal-header">
            <div>
                <h3 id="modalTitle" style="font-size: 1.5rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.25rem;">Batch Details</h3>
                <p id="modalSubtitle" style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">#BATCH-0000</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <button onclick="printModal()" class="btn-icon" title="Print Details">
                    <i data-lucide="printer" style="width: 18px;"></i>
                </button>
                <button onclick="closeModal()" class="btn-icon danger" title="Close">
                    <i data-lucide="x" style="width: 18px;"></i>
                </button>
            </div>
        </div>

        <div class="modal-body" id="modalBody">
            <div class="loader-container">
                <div class="loader"></div>
                <p>Retrieving transaction data...</p>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Design System */
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    [data-theme='dark'] {
        --glass-bg: rgba(30, 41, 59, 0.7);
        --glass-border: rgba(255, 255, 255, 0.1);
        --bg-main: #020617;
    }

    .glass-card {
        background: var(--glass-bg) !important;
        backdrop-filter: blur(16px) saturate(180%);
        border: 1px solid var(--glass-border) !important;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05) !important;
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    [data-theme='dark'] .glass-card {
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3) !important;
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.8)) !important;
    }

    .glass-card:hover {
        transform: translateY(-5px);
    }

    .table-scroll-wrapper {
        width: 100%;
        overflow: visible;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .table-scroll-wrapper::-webkit-scrollbar {
        display: none;
    }

    /* Modal Architecture */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(2, 6, 23, 0.7);
        backdrop-filter: blur(12px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 2rem;
    }

    .modal-content {
        width: 100%;
        max-width: 800px;
        max-height: 90vh;
        background: var(--bg-card) !important;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4) !important;
        border-radius: 28px;
        overflow: hidden;
        border: 1px solid var(--glass-border);
        transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    [data-theme='dark'] .modal-content {
        background: linear-gradient(135deg, #0f172a, #020617) !important;
    }

    .modal-header {
        padding: 1.75rem 2.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-body {
        padding: 2.5rem;
        overflow-y: auto;
        flex: 1;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .modal-body::-webkit-scrollbar {
        display: none;
    }

    @keyframes slideUpIn {
        from {
            transform: translateY(100%);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes slideDownOut {
        from {
            transform: translateY(0);
            opacity: 1;
        }

        to {
            transform: translateY(100%);
            opacity: 0;
        }
    }

    .modal-content.slide-in {
        animation: slideUpIn 0.6s cubic-bezier(0.32, 1, 0.23, 1) forwards;
    }

    .modal-content.slide-out {
        animation: slideDownOut 0.6s cubic-bezier(0.32, 1, 0.23, 1) forwards;
    }

    @media (max-width: 768px) {
        .modal-backdrop {
            align-items: flex-end !important;
            justify-content: flex-end !important;
            padding: 0 !important;
        }

        .modal-content {
            width: 100% !important;
            height: auto !important;
            max-height: 92vh !important;
            border-radius: 32px 32px 0 0 !important;
            margin: 0 !important;
            border: none !important;
        }

        .modal-content::before {
            content: '';
            position: sticky;
            top: 12px;
            left: 50%;
            transform: translateX(-50%);
            width: 44px;
            height: 5px;
            background: var(--text-muted);
            opacity: 0.2;
            border-radius: 10px;
            z-index: 1000;
            display: block;
            margin: 0 auto -5px auto;
        }

        .modal-header {
            padding: 3rem 1.5rem 1.25rem 1.5rem !important;
            flex-direction: column !important;
            align-items: center !important;
            text-align: center !important;
            gap: 1.5rem !important;
            position: relative !important;
        }

        .modal-header>div:last-child {
            position: absolute !important;
            top: 1rem !important;
            right: 1rem !important;
        }

        .modal-body {
            padding: 1.5rem !important;
        }
    }

    .btn-icon {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-main);
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-icon:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-3px) rotate(8deg);
        border-color: var(--primary);
    }

    .glass-btn-sm {
        display: inline-flex;
        align-items: center;
        padding: 0.6rem 1.25rem;
        background: var(--bg-card);
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        color: var(--primary);
        font-weight: 800;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .glass-btn-sm:hover {
        background: var(--primary);
        color: white !important;
        border-color: var(--primary);
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
    }

    /* Advanced Pagination */
    .pagination-footer {
        padding: 2rem 2.5rem;
        background: rgba(0, 0, 0, 0.02);
        border-top: 1px solid var(--border-color);
    }

    .pagination-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .pagination-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .pagination-info-badge {
        background: var(--primary-gradient);
        color: white;
        padding: 0.6rem 1.25rem;
        border-radius: 14px;
        font-size: 0.85rem;
        font-weight: 800;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
    }

    .pagination-nav {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .pagination-arrow {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: var(--bg-card);
        border: 1.5px solid var(--border-color);
        color: var(--text-main);
        transition: all 0.3s;
        text-decoration: none;
    }

    .pagination-arrow:hover {
        background: var(--primary);
        color: white;
        transform: scale(1.1);
        border-color: var(--primary);
    }

    .pagination-number {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        height: 48px;
        border-radius: 16px;
        background: var(--bg-card);
        border: 1.5px solid var(--border-color);
        color: var(--text-main);
        font-weight: 800;
        text-decoration: none;
        transition: all 0.3s;
    }

    .pagination-number.active {
        background: var(--primary-gradient);
        color: white;
        border-color: transparent;
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
    }

    .per-page-select {
        padding: 0.75rem 2.5rem 0.75rem 1.25rem;
        border-radius: 14px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-main);
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
    }

    [data-theme='dark'] .per-page-select {
        background-color: #1e293b;
    }

    /* Table Logic */
    .activity-row {
        position: relative;
        transition: all 0.3s ease;
    }

    .activity-row:hover {
        background: rgba(99, 102, 241, 0.05) !important;
        box-shadow: inset 4px 0 0 var(--primary);
    }

    /* Action Dropdown Styles */
    .action-dropdown-wrapper {
        position: relative;
        display: inline-block;
    }

    .action-menu {
        position: absolute;
        right: 0;
        top: calc(100% + 10px);
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        min-width: 200px;
        z-index: 1000;
        display: none;
        flex-direction: column;
        padding: 0.75rem;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(12px);
        animation: dropdownIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    .action-menu.active {
        display: flex;
    }

    .menu-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-radius: 10px;
        color: var(--text-main);
        font-weight: 700;
        font-size: 0.85rem;
        text-align: left;
        background: transparent;
        border: none;
        width: 100%;
        cursor: pointer;
        transition: all 0.2s;
    }

    .menu-item:hover {
        background: rgba(99, 102, 241, 0.1);
        color: var(--primary) !important;
        transform: translateX(5px);
    }

    .menu-item i {
        width: 18px;
        height: 18px;
    }

    @keyframes dropdownIn {
        from {
            opacity: 0;
            transform: translateY(15px) scale(0.95);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @media (max-width: 1024px) {
        .activity-table {
            min-width: unset !important;
        }

        .activity-table thead {
            display: none !important;
        }

        .activity-table,
        .activity-table tbody,
        .activity-table tr,
        .activity-table td {
            display: block !important;
            width: 100% !important;
        }

        .activity-row {
            margin-bottom: 2rem !important;
            border-radius: 20px !important;
            background: var(--bg-card) !important;
            padding: 1.5rem !important;
            border: 1px solid var(--border-color) !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
            position: relative;
        }

        .activity-row td {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 0.75rem 0 !important;
            border: none !important;
            background: transparent !important;
        }

        .activity-row td::before {
            content: attr(data-label);
            font-weight: 800;
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .activity-row td[data-label="Description"] {
            display: block !important;
            border-bottom: 2px solid var(--border-color) !important;
            padding-bottom: 1.5rem !important;
            margin-bottom: 1.25rem !important;
        }

        .activity-row td[data-label="Description"]::before {
            display: inline-block !important;
            margin-bottom: 0.75rem !important;
            margin-right: 1rem !important;
            /* Added margin right */
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            padding: 0.35rem 0.85rem;
            border-radius: 8px;
            font-size: 0.65rem !important;
            font-weight: 900 !important;
            letter-spacing: 1.5px !important;
            border: 1px solid rgba(99, 102, 241, 0.15);
            content: 'PRODUCT SPECIFICATION' !important;
        }

        .activity-row td[data-label="Action"] {
            border-top: 1px solid var(--border-color) !important;
            margin-top: 1rem !important;
            padding-top: 1.25rem !important;
            text-align: center !important;
            justify-content: center !important;
            flex-direction: row !important;
            flex-wrap: wrap !important;
            gap: 0.75rem !important;
            height: auto !important;
        }

        .desktop-only {
            display: none !important;
        }

        .glass-btn-sm {
            width: 100%;
            justify-content: center;
        }
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes scaleUp {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .animate-scale-up {
        animation: scaleUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    .loader {
        width: 50px;
        height: 50px;
        border: 4px solid var(--border-color);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    /* Mobile Responsive Overrides */
    @media (max-width: 1024px) {
        .page-header {
            flex-direction: column;
            align-items: stretch !important;
            gap: 1.5rem;
            padding: 1.75rem !important;
            background: linear-gradient(180deg, var(--bg-card) 0%, var(--bg-main) 100%) !important;
            border-radius: 0 0 28px 28px !important;
            margin: -24px -24px 2.5rem -24px !important;
            /* Bleed to edges */
            border: none !important;
            border-bottom: 1px solid var(--border-color) !important;
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.03) !important;
        }

        .page-header h2 {
            font-size: 1.75rem !important;
            margin-top: 0.5rem !important;
        }

        .page-header p {
            font-size: 0.95rem !important;
            opacity: 0.8;
        }

        .page-header>div:last-child {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 0.75rem !important;
            width: 100% !important;
        }

        .page-header>div:last-child button {
            width: 100% !important;
            justify-content: center !important;
            padding: 0.85rem !important;
            font-size: 0.85rem !important;
        }

        .stats-grid {
            grid-template-columns: 1fr !important;
        }
    }

    @media (max-width: 768px) {

        /* Stats stacking */
        div[style*="grid-template-columns: repeat(auto-fit"] {
            grid-template-columns: repeat(2, 1fr) !important;
        }

        div[style*="grid-template-columns: repeat(auto-fit"]>div {
            padding: 1rem !important;
            gap: 0.75rem !important;
        }

        /* Filter stacking */
        form[style*="display: grid;"] {
            grid-template-columns: 1fr !important;
            gap: 1.25rem !important;
        }

        #filterForm input {
            height: 50px !important;
            font-size: 0.95rem !important;
            border-color: var(--border-color) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02) !important;
        }

        #filterForm label {
            color: var(--primary) !important;
            font-size: 0.7rem !important;
            margin-left: 0.25rem !important;
        }

        .search-container-mobile {
            border-radius: 24px !important;
            padding: 1.25rem !important;
            background: var(--bg-card) !important;
            border: 1px solid var(--primary) !important;
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.1) !important;
        }

        .filter-buttons-mobile {
            display: grid !important;
            grid-template-columns: 1fr 60px !important;
            gap: 0.75rem !important;
            margin-top: 0.5rem !important;
        }

        .filter-buttons-mobile button {
            height: 50px !important;
            border-radius: 12px !important;
        }

        /* Transforming Table into Cards for Mobile */
        .activity-table thead {
            display: none;
            /* Hide headers on mobile */
        }

        .activity-table,
        .activity-table tbody,
        .activity-table tr,
        .activity-table td {
            display: block;
            width: 100%;
        }

        .activity-table tr {
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            background: var(--glass-bg);
            padding: 1rem;
            position: relative;
        }

        .activity-table td {
            text-align: right;
            padding: 0.75rem 0.5rem !important;
            border-top: 1px solid rgba(0, 0, 0, 0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-table td:first-child {
            border-top: none;
        }

        /* Add labels before data */
        .activity-table td::before {
            content: attr(data-label);
            float: left;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        .activity-table td[data-label="Description"] {
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }

        .activity-table td[data-label="Description"]::before {
            margin-bottom: 0.5rem;
        }

        .desktop-only {
            display: none !important;
        }

        .glass-btn-sm {
            padding: 0.6rem !important;
            border-radius: 12px;
        }

        /* Pagination adjustments */
        .pagination-container {
            flex-direction: column;
            align-items: stretch;
            padding: 1.5rem 1rem;
        }

        .pagination-info {
            justify-content: center;
            flex-direction: column;
            text-align: center;
        }

        .pagination-nav {
            justify-content: center;
            margin: 1rem 0;
        }

        .pagination-number {
            min-width: 38px;
            height: 38px;
            font-size: 0.85rem;
        }

        .pagination-per-page {
            justify-content: center;
            width: 100%;
        }

        .per-page-select {
            width: 100%;
            text-align: center;
        }
    }

    @media print {

        .sidebar,
        .top-nav,
        .glass-card form,
        .page-header button,
        .top-nav-actions,
        .pagination-footer {
            display: none !important;
        }

        .main-wrapper {
            margin-left: 0 !important;
        }

        body {
            background: white !important;
        }

        .glass-card {
            box-shadow: none !important;
            border: 1px solid #eee !important;
        }
    }
</style>

<script>
    const ledgeMap = @json($ledgeMap);

    let currentBatchId = null;

    function openModal(batchId) {
        currentBatchId = batchId;
        const modal = document.getElementById('detailModal');
        const modalContent = modal.querySelector('.modal-content');
        const modalBody = document.getElementById('modalBody');
        const modalSubtitle = document.getElementById('modalSubtitle');

        modal.style.display = 'flex';
        modalContent.classList.remove('slide-out');
        modalContent.classList.add('slide-in');
        modalSubtitle.innerText = `#BATCH-${batchId}`;

        // Show loader
        modalBody.innerHTML = `
            <div class="loader-container">
                <div class="loader"></div>
                <p>Retrieving transaction data...</p>
            </div>
        `;

        // Fetch data
        fetch(`/received-items/${batchId}?json=true`)
            .then(response => response.json())
            .then(data => {
                const batch = data.batch;
                let itemsHtml = '';

                batch.items.forEach((item, index) => {
                    const openingBalance = (parseFloat(item.stock_balance) - parseFloat(item.variance)).toFixed(0);
                    const isLast = index === batch.items.length - 1;
                    itemsHtml += `
                        <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem; ${!isLast ? 'border-bottom: 1px solid var(--border-color);' : ''}">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div style="font-weight: 800; color: var(--text-main); font-size: 1rem;">${item.description}</div>
                                <div style="font-family: monospace; background: var(--bg-card); padding: 2px 8px; border-radius: 6px; font-size: 0.75rem; border: 1px solid var(--border-color);">${item.folio || 'N/A'}</div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.02); padding: 0.75rem 1rem; border-radius: 12px;">
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800;">Opening</span>
                                    <span style="font-weight: 700; color: var(--text-main);">${openingBalance}</span>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: center;">
                                    <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800;">Received</span>
                                    <span style="font-weight: 900; color: #10b981;">+${item.variance}</span>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                    <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 800;">Closing</span>
                                    <span style="font-weight: 900; color: var(--primary); font-size: 1.1rem;">${item.stock_balance}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });

                modalBody.innerHTML = `
                    <!-- Inset Grouped Style: Batch Info -->
                    <div style="background: var(--bg-main); border-radius: 20px; border: 1px solid var(--border-color); overflow: hidden; margin-bottom: 2rem;">
                        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted); font-weight: 700; font-size: 0.8rem; text-transform: uppercase;">Logistics Source</span>
                            <span style="color: var(--text-main); font-weight: 800;">${batch.supplier_name}</span>
                        </div>
                        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted); font-weight: 700; font-size: 0.8rem; text-transform: uppercase;">Transaction Date</span>
                            <span style="color: var(--text-main); font-weight: 700;">${new Date(batch.entry_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</span>
                        </div>
                        <div style="padding: 1.15rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted); font-weight: 700; font-size: 0.8rem; text-transform: uppercase;">Allocation</span>
                            <span style="color: var(--primary); font-weight: 900;">${ledgeMap[batch.ledge_category] || 'Ledge ' + batch.ledge_category}</span>
                        </div>
                    </div>

                    <!-- Inset Grouped Style: Items -->
                    <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); margin-left: 1rem; margin-bottom: 0.75rem; font-weight: 800; letter-spacing: 0.5px;">Product Specification</h4>
                    <div style="background: var(--bg-main); border-radius: 20px; border: 1px solid var(--border-color); overflow: hidden; margin-bottom: 2rem;">
                        ${itemsHtml}
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); margin-left: 1rem; margin-bottom: 0.75rem; font-weight: 800; letter-spacing: 0.5px;">Audit Remarks</label>
                        <textarea id="batchRemarks" placeholder="Enter notes..."
                            style="width: 100%; padding: 1.25rem; border: 1px solid var(--border-color); border-radius: 20px; background: var(--bg-main); color: var(--text-main); font-size: 0.95rem; resize: none; min-height: 100px; transition: all 0.3s;"></textarea>
                    </div>

                    <div style="margin-top: 2.5rem; display: flex; flex-direction: column; gap: 1rem;">
                        <button onclick="printModal()" style="width: 100%; background: var(--primary); color: white; border: none; padding: 1.15rem; border-radius: 20px; font-weight: 800; font-size: 1rem; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);">
                            <i data-lucide="printer" style="width: 20px;"></i>
                            Print Official Voucher
                        </button>
                        <div style="text-align: center; color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">
                            Certified for System Audit • ${batch.items.length} Items
                        </div>
                    </div>
                `;

                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(error => {
                modalBody.innerHTML = `
                    <div class="loader-container">
                        <i data-lucide="alert-circle" style="width: 48px; color: #ef4444;"></i>
                        <p style="color: #ef4444;">Failed to load data. Please try again.</p>
                    </div>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
    }

    function closeModal() {
        const modal = document.getElementById('detailModal');
        const modalContent = modal.querySelector('.modal-content');

        modalContent.classList.remove('slide-in');
        modalContent.classList.add('slide-out');

        setTimeout(() => {
            modal.style.display = 'none';
            modalContent.classList.remove('slide-out');
            currentBatchId = null;
        }, 600);
    }

    function printModal() {
        if (currentBatchId) {
            const remarks = encodeURIComponent(document.getElementById('batchRemarks').value);
            window.open(`/received-items/${currentBatchId}/print?remarks=${remarks}`, '_blank');
        }
    }

    function continueDelivery(batchId) {
        // Redirect to dashboard with continue_batch parameter
        window.location.href = `/dashboard?continue_batch=${batchId}`;
    }

    // Close on backdrop click
    window.onclick = function(event) {
        const modal = document.getElementById('detailModal');
        if (event.target == modal) {
            closeModal();
        }
    }

    // Real-time Search Logic
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const supplierInput = document.getElementById('supplierInput');
    const dateInput = document.getElementById('dateInput');
    const resultsContainer = document.getElementById('resultsContainer');

    let debounceTimer;

    function performSearch() {
        // Instant Clean URL on search start
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({}, '', cleanUrl);

        resultsContainer.style.opacity = '0.6';
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData).toString();
        const url = `${window.location.pathname}?${params}`;

        fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('resultsContainer').innerHTML;
                resultsContainer.innerHTML = newContent;
                resultsContainer.style.opacity = '1';

                // Re-initialize icons and listeners for new content
                if (typeof lucide !== 'undefined') lucide.createIcons();

                // Final Clean URL check
                window.history.replaceState({}, '', cleanUrl);
            })
            .catch(error => {
                console.error('Search error:', error);
                resultsContainer.style.opacity = '1';
            });
    }

    if (searchInput) {
        [searchInput, supplierInput, dateInput].forEach(input => {
            if (input) {
                input.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(performSearch, 400);
                });
            }
        });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            performSearch();
        });
    }

    // Quick Ledge Filter Logic
    const ledgeBtns = document.querySelectorAll('.quick-ledge-btn');
    const ledgeInput = document.getElementById('ledgeCategoryInput');

    ledgeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            ledgeBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            ledgeInput.value = btn.dataset.ledge;
            performSearch();
        });
    });

    // Carousel Navigation Logic
    const ledgeScroll = document.getElementById('ledgeScroll');
    const prevBtn = document.getElementById('prevLedge');
    const nextBtn = document.getElementById('nextLedge');

    if (ledgeScroll && prevBtn && nextBtn) {
        const updateArrows = () => {
            const scrollLeft = ledgeScroll.scrollLeft;
            const maxScroll = ledgeScroll.scrollWidth - ledgeScroll.clientWidth;

            prevBtn.style.opacity = scrollLeft > 10 ? '1' : '0';
            prevBtn.style.pointerEvents = scrollLeft > 10 ? 'auto' : 'none';

            nextBtn.style.opacity = scrollLeft < maxScroll - 10 ? '1' : '0';
            nextBtn.style.pointerEvents = scrollLeft < maxScroll - 10 ? 'auto' : 'none';
        };

        prevBtn.addEventListener('click', () => {
            ledgeScroll.scrollBy({
                left: -250,
                behavior: 'smooth'
            });
        });

        nextBtn.addEventListener('click', () => {
            ledgeScroll.scrollBy({
                left: 250,
                behavior: 'smooth'
            });
        });

        ledgeScroll.addEventListener('scroll', updateArrows);
        window.addEventListener('resize', updateArrows);
        // Initial check
        setTimeout(updateArrows, 500);
    }

    // Initialize Lucide icons after page load
    document.addEventListener('DOMContentLoaded', function() {
        // Instant Clean URL on load if params exist
        if (window.location.search) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({}, '', cleanUrl);
        }

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Action Dropdown Close Listener
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.action-dropdown-wrapper')) {
                document.querySelectorAll('.action-menu.active').forEach(menu => {
                    menu.classList.remove('active');
                    const row = menu.closest('.activity-row');
                    if (row) row.style.zIndex = '';
                });
            }
        });
    });

    // Toggle Action Menu Logic
    function toggleActionMenu(batchId) {
        // Close all other active menus
        document.querySelectorAll('.action-menu.active').forEach(menu => {
            if (menu.id !== `actionMenu-${batchId}`) {
                menu.classList.remove('active');
                const row = menu.closest('.activity-row');
                if (row) row.style.zIndex = '';
            }
        });
        
        const menu = document.getElementById(`actionMenu-${batchId}`);
        if (menu) {
            menu.classList.toggle('active');
            const row = menu.closest('.activity-row');
            if (row) {
                if (menu.classList.contains('active')) {
                    row.style.zIndex = '50';
                } else {
                    row.style.zIndex = '';
                }
            }
        }
    }
</script>
@endsection
