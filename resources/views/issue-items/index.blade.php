@extends('layouts.dashboard')

@section('content')
<div class="animate-slide-up" style="max-width: 1600px; margin: 0 auto; padding: 0 1.5rem;">

    <!-- Operations Header -->
    <div class="glass-card header-mesh" style="padding: 3rem; border-radius: 32px; margin-bottom: 3rem; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.4); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.08);">
        <div style="position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%); z-index: 0;"></div>
        <div style="position: absolute; bottom: -50px; left: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(16, 185, 129, 0.05) 0%, transparent 70%); z-index: 0;"></div>

        <div style="position: relative; z-index: 1;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1.5rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
                        @if(in_array(auth()->user()->role, ['Main Admin', 'Department Head']))
                            <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;">{{ strtoupper(auth()->user()->department) }} · Department Head Hub</span>
                        @else
                            <span class="inventory-badge">Issue</span>
                        @endif
                        <div style="width: 4px; height: 4px; background: var(--text-muted); border-radius: 50%; opacity: 0.5;"></div>
                        <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="shield" style="width: 14px; color: var(--primary);"></i> Operations Verified
                        </span>
                    </div>
                    <h1 style="margin: 0; font-size: 3rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.05em; line-height: 1;">Issue <span class="gradient-text">Inventory</span></h1>
                    <p style="margin: 12px 0 0; color: var(--text-muted); font-size: 1.1rem; font-weight: 500; opacity: 0.8;">Full tracking registry of all inventory items given out (disbursed or collected requisitions) across the logistics network.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistical Insight Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
        <div class="glass-card stat-card" style="padding: 2rem; border-radius: 24px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 1.5rem; transition: transform 0.3s;">
            <div style="width: 64px; height: 64px; border-radius: 20px; background: rgba(99, 102, 241, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                <i data-lucide="package-2" style="width: 32px; height: 32px;"></i>
            </div>
            <div>
                <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Total Disbursed</div>
                <div style="font-size: 2rem; font-weight: 950; color: var(--text-main); line-height: 1;">{{ number_format($stats['total_disbursed']) }} <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">Items</span></div>
            </div>
        </div>

        <div class="glass-card stat-card" style="padding: 2rem; border-radius: 24px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 1.5rem; transition: transform 0.3s;">
            <div style="width: 64px; height: 64px; border-radius: 20px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="check-square" style="width: 32px; height: 32px;"></i>
            </div>
            <div>
                <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Permanent Allocations</div>
                <div style="font-size: 2rem; font-weight: 950; color: var(--text-main); line-height: 1;">{{ number_format($stats['permanent_allocations']) }} <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">Items</span></div>
            </div>
        </div>

        <div class="glass-card stat-card" style="padding: 2rem; border-radius: 24px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 1.5rem; transition: transform 0.3s;">
            <div style="width: 64px; height: 64px; border-radius: 20px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="clock" style="width: 32px; height: 32px;"></i>
            </div>
            <div>
                <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Temporary Loans</div>
                <div style="font-size: 2rem; font-weight: 950; color: var(--text-main); line-height: 1;">{{ number_format($stats['temporary_loans']) }} <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">Items</span></div>
            </div>
        </div>

        <div class="glass-card stat-card" style="padding: 2rem; border-radius: 24px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 1.5rem; transition: transform 0.3s;">
            <div style="width: 64px; height: 64px; border-radius: 20px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="map-pin" style="width: 32px; height: 32px;"></i>
            </div>
            <div>
                <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Active Destinations</div>
                <div style="font-size: 2rem; font-weight: 950; color: var(--text-main); line-height: 1;">{{ $stats['active_destinations'] }} <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">Depts</span></div>
            </div>
        </div>
    </div>

    <!-- Live Filters Container -->
    <div class="glass-card filters-card" style="border-radius: 28px; padding: 2.5rem 3rem; border: 1px solid var(--border-color); margin-bottom: 3rem;">
        <form method="GET" action="{{ route('issueitems') }}" id="filter-form" style="display: flex; align-items: flex-end; flex-wrap: wrap; gap: 1.5rem; width: 100%;">
            <!-- Live Search -->
            <div style="flex: 2; min-width: 300px;">
                <label style="display: block; font-size: 0.72rem; font-weight: 900; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.05em;">Search Logs</label>
                <div style="position: relative;">
                    <i data-lucide="search" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); width: 18px; color: var(--primary); opacity: 0.6;"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by asset, destination department, or authority..." style="width: 100%; padding: 1rem 1rem 1rem 3.25rem; border-radius: 16px; border: 2px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-weight: 700; outline: none; transition: all 0.3s; font-size: 0.95rem;" autocomplete="off">
                </div>
            </div>

            <!-- Category Filter -->
            <div style="flex: 1; min-width: 180px;">
                <label style="display: block; font-size: 0.72rem; font-weight: 900; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.05em;">Category</label>
                <div style="position: relative;">
                    <i data-lucide="layers" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); width: 18px; color: var(--primary); opacity: 0.6; pointer-events: none;"></i>
                    <select name="category" onchange="this.form.submit()" style="width: 100%; padding: 1rem 1.25rem 1rem 3.25rem; border-radius: 16px; border: 2px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-weight: 700; outline: none; cursor: pointer; font-size: 0.95rem; appearance: none; transition: all 0.3s;">
                        <option value="all" {{ request('category', 'all') === 'all' ? 'selected' : '' }}>All Categories</option>
                        @foreach($ledgeMap as $code => $name)
                            <option value="{{ $code }}" {{ request('category') === $code ? 'selected' : '' }}>Category {{ $code }} - {{ $name }}</option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" style="position: absolute; right: 1.25rem; top: 50%; transform: translateY(-50%); width: 16px; color: var(--text-muted); pointer-events: none;"></i>
                </div>
            </div>

            <!-- Allocation Type Filter -->
            <div style="flex: 1; min-width: 180px;">
                <label style="display: block; font-size: 0.72rem; font-weight: 900; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.05em;">Allocation Type</label>
                <div style="position: relative;">
                    <i data-lucide="clipboard-check" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); width: 18px; color: var(--primary); opacity: 0.6; pointer-events: none;"></i>
                    <select name="type" onchange="this.form.submit()" style="width: 100%; padding: 1rem 1.25rem 1rem 3.25rem; border-radius: 16px; border: 2px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-weight: 700; outline: none; cursor: pointer; font-size: 0.95rem; appearance: none; transition: all 0.3s;">
                        <option value="all" {{ request('type', 'all') === 'all' ? 'selected' : '' }}>All Types</option>
                        <option value="Permanent" {{ request('type') === 'Permanent' ? 'selected' : '' }}>Permanent</option>
                        <option value="Temporary" {{ request('type') === 'Temporary' ? 'selected' : '' }}>Temporary</option>
                    </select>
                    <i data-lucide="chevron-down" style="position: absolute; right: 1.25rem; top: 50%; transform: translateY(-50%); width: 16px; color: var(--text-muted); pointer-events: none;"></i>
                </div>
            </div>

            <!-- Date Filter -->
            <div style="flex: 1; min-width: 180px;">
                <label style="display: block; font-size: 0.72rem; font-weight: 900; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.05em;">Disbursement Date</label>
                <div style="position: relative;">
                    <i data-lucide="calendar" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); width: 18px; color: var(--primary); opacity: 0.6; pointer-events: none;"></i>
                    <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()" style="width: 100%; padding: 1.02rem 1.25rem 1.02rem 3.25rem; border-radius: 16px; border: 2px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-weight: 700; outline: none; cursor: pointer; font-size: 0.95rem; transition: all 0.3s;">
                </div>
            </div>

            @if(request()->anyFilled(['search', 'category', 'type', 'date']) && !(request('category') === 'all' && request('type') === 'all' && !request('search') && !request('date')))
            <div style="flex-shrink: 0;">
                <a href="{{ route('issueitems') }}" style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 1.02rem 1.5rem; border: 2px solid #ef4444; border-radius: 16px; background: rgba(239, 68, 68, 0.05); color: #ef4444; font-weight: 800; font-size: 0.95rem; text-decoration: none; transition: all 0.2s ease; cursor: pointer; height: 53.5px; box-sizing: border-box;" onmouseover="this.style.background='#ef4444'; this.style.color='white';" onmouseout="this.style.background='rgba(239, 68, 68, 0.05)'; this.style.color='#ef4444';">
                    <i data-lucide="x-circle" style="width:16px; height:16px;"></i>
                    <span>Clear Filters</span>
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Data Registry View -->
    <div class="glass-card registry-card" style="border-radius: 32px; padding: 3rem 4rem; border: 1px solid var(--border-color); min-height: 500px; margin-bottom: 3rem;">
        <div id="logTableContainer">
            @if($issuedItems->count() > 0)
            <div class="table-scroll-wrapper" style="width: 100%; overflow-x: auto; padding-bottom: 1.5rem;">
                <table class="responsive-log-table" style="width: 100%; min-width: 1100px; border-collapse: separate; border-spacing: 0 1.25rem; table-layout: auto;">
                    <thead>
                        <tr style="text-align: left; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.15em; font-weight: 900; white-space: nowrap;">
                            <th style="padding: 0 1.5rem 0.5rem;">Timeline</th>
                            <th style="padding: 0 1.5rem 0.5rem;">Items</th>
                            <th style="padding: 0 1.5rem 0.5rem;">Storage Location</th>
                            <th style="padding: 0 1.5rem 0.5rem;">Destination</th>
                            <th style="padding: 0 1.5rem 0.5rem;">Collector</th>
                            <th style="padding: 0 1.5rem 0.5rem;">Head of Stores</th>
                            <th style="padding: 0 1.5rem 0.5rem;">Store Officer</th>
                            <th style="padding: 0 1.5rem 0.5rem;">Qty Disbursed</th>
                            <th style="padding: 0 1.5rem 0.5rem; text-align: right;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="logTableBody">
                        @foreach($issuedItems as $item)
                        @php
                            $t = $item->created_at;
                            $dateVal = $t->format('Y-m-d');
                            $dateStr = $t->format('d/m/y');
                            $timeStr = $t->format('H:i');

                            $statusBadge = '';
                            if ($item->quantity === 0 && $item->issuance_type === 'Temporary') {
                                $statusBadge = '<span class="status-badge" style="background: rgba(100, 116, 139, 0.1); color: var(--text-muted); font-size: 0.7rem; padding: 0.4rem 1.15rem; border-radius: 10px; font-weight: 900; letter-spacing: 0.05em; border: 1px dashed rgba(100, 116, 139, 0.3);">RETURNED</span>';
                            } else {
                                $statusColor = $item->issuance_type === 'Temporary' ? '#ea580c' : '#10b981';
                                $statusBg = $item->issuance_type === 'Temporary' ? 'rgba(234,88,12,0.1)' : 'rgba(16,185,129,0.1)';
                                $statusBadge = '<span class="status-badge" style="background: ' . $statusBg . '; color: ' . $statusColor . '; font-size: 0.7rem; padding: 0.4rem 1.15rem; border-radius: 10px; font-weight: 900; letter-spacing: 0.05em;">' . strtoupper($item->issuance_type) . '</span>';
                            }
                        @endphp
                        <tr class="log-row" data-date="{{ $dateVal }}" data-category="{{ $item->ledge_category }}" data-type="{{ $item->issuance_type }}" data-search="{{ strtolower($item->description . ' ' . $item->beneficiary . ' ' . ($item->authority ?? 'Adomako Emmanuel') . ' ' . $item->location . ' ' . ($item->collector_name ?? '') . ' ' . ($item->confirming_officer_name ?? 'Adomako Emmanuel')) }}" style="background: var(--bg-card); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 6px rgba(0,0,0,0.02); border-radius: 20px;">
                            <td data-label="Timeline" style="padding: 1.75rem 1.5rem; border-radius: 20px 0 0 20px;">
                                <div style="font-weight: 800; color: var(--text-main); font-size: 0.95rem;">{{ $dateStr }}</div>
                                <div style="font-weight: 700; color: var(--text-muted); font-size: 0.75rem; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                    <i data-lucide="clock" style="width: 12px;"></i> {{ $timeStr }}
                                </div>
                            </td>
                            <td data-label="Asset Breakdown" style="padding: 1.75rem 1.5rem;">
                                <div style="font-weight: 950; color: var(--primary); font-size: 1.05rem; display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                                    <span>{{ $item->description }}</span>
                                </div>
                                <div style="margin-top: 4px;"><span style="background: rgba(99, 102, 241, 0.08); color: var(--primary); padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.6rem; font-weight: 900; border: 1px solid rgba(99, 102, 241, 0.1); letter-spacing: 0.03em;">CATEGORY {{ $item->ledge_category }}</span></div>
                            </td>
                            <td data-label="Storage Location" style="padding: 1.75rem 1.5rem; font-weight: 800; color: var(--text-main); font-size: 0.95rem;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <i data-lucide="map-pin" style="width: 16px; color: var(--text-muted); opacity: 0.6;"></i>
                                    <span>{{ $item->location }}</span>
                                </div>
                            </td>
                            <td data-label="Destination" style="padding: 1.75rem 1.5rem; font-weight: 900; color: var(--text-main); font-size: 1.05rem; white-space: nowrap;">{{ $item->beneficiary }}</td>
                            <td data-label="Collector" style="padding: 1.75rem 1.5rem;">
                                @if(!empty($item->collector_name))
                                <div style="font-size: 0.95rem; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 6px; white-space: nowrap;">
                                    <i data-lucide="user" style="width: 14px; height: 14px;"></i>
                                    {{ $item->collector_name }}
                                </div>
                                @else
                                <span style="color: var(--text-muted); font-size: 0.9rem;">-</span>
                                @endif
                            </td>
                            <td data-label="Head of Stores" style="padding: 1.75rem 1.5rem; font-weight: 800; color: var(--text-main); font-size: 0.95rem; white-space: nowrap;">{{ $item->authority ?: 'Adomako Emmanuel' }}</td>
                            <td data-label="Store Officer" style="padding: 1.75rem 1.5rem; font-weight: 800; color: var(--text-main); font-size: 0.95rem; white-space: nowrap;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <i data-lucide="shield-check" style="width: 14px; height: 14px; color: #10b981;"></i>
                                    {{ $item->confirming_officer_name ?: 'Adomako Emmanuel' }}
                                </div>
                            </td>
                            <td data-label="Qty Disbursed" style="padding: 1.75rem 1.5rem; font-weight: 900; font-size: 1.35rem; color: var(--text-main); white-space: nowrap;">{{ number_format($item->quantity) }} <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ $item->unit ?: 'Package Types' }}</span></td>
                            <td data-label="Allocation Status" style="padding: 1.75rem 1.5rem; border-radius: 0 20px 20px 0; text-align: right;">
                                {!! $statusBadge !!}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($issuedItems->hasPages())
            <div style="padding: 1.5rem 0 0; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">
                    Showing
                    <span style="color: var(--text-main); font-weight: 900;">{{ $issuedItems->firstItem() ?? 0 }}</span>
                    to
                    <span style="color: var(--text-main); font-weight: 900;">{{ $issuedItems->lastItem() ?? 0 }}</span>
                    of
                    <span style="color: var(--text-main); font-weight: 900;">{{ $issuedItems->total() }}</span>
                    entries
                </div>

                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    {{-- Previous --}}
                    @if($issuedItems->onFirstPage())
                        <span style="padding: 0.5rem 1rem; border-radius: 8px; background: var(--bg-main); color: var(--text-muted); font-size: 0.8rem; font-weight: 800; border: 1px solid var(--border-color); opacity: 0.5; cursor: not-allowed;">Prev</span>
                    @else
                        <a href="{{ $issuedItems->appends(request()->query())->previousPageUrl() }}" style="padding: 0.5rem 1rem; border-radius: 8px; background: var(--bg-card); color: var(--text-main); font-size: 0.8rem; font-weight: 800; border: 1px solid var(--border-color); text-decoration: none; transition: 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)'">Prev</a>
                    @endif

                    {{-- Page Numbers --}}
                    <div style="display: flex; gap: 0.25rem;">
                        @foreach($issuedItems->appends(request()->query())->getUrlRange(max(1, $issuedItems->currentPage()-2), min($issuedItems->lastPage(), $issuedItems->currentPage()+2)) as $page => $url)
                            @if($page == $issuedItems->currentPage())
                                <span style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background: var(--primary); color: white; font-size: 0.85rem; font-weight: 900; box-shadow: 0 4px 10px rgba(99,102,241,0.2);">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background: var(--bg-card); color: var(--text-main); font-size: 0.85rem; font-weight: 800; border: 1px solid var(--border-color); text-decoration: none; transition: 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)'">{{ $page }}</a>
                            @endif
                        @endforeach
                    </div>

                    {{-- Next --}}
                    @if($issuedItems->hasMorePages())
                        <a href="{{ $issuedItems->appends(request()->query())->nextPageUrl() }}" style="padding: 0.5rem 1rem; border-radius: 8px; background: var(--bg-card); color: var(--text-main); font-size: 0.8rem; font-weight: 800; border: 1px solid var(--border-color); text-decoration: none; transition: 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)'">Next</a>
                    @else
                        <span style="padding: 0.5rem 1rem; border-radius: 8px; background: var(--bg-main); color: var(--text-muted); font-size: 0.8rem; font-weight: 800; border: 1px solid var(--border-color); opacity: 0.5; cursor: not-allowed;">Next</span>
                    @endif
                </div>
            </div>
            @endif
            @else
            <div style="padding: 10rem 0; text-align: center; background: rgba(0,0,0,0.01); border-radius: 32px; border: 2px dashed var(--border-color);">
                <i data-lucide="database" style="width: 80px; height: 80px; color: var(--text-muted); opacity: 0.15; margin-bottom: 2rem;"></i>
                <h3 style="font-weight: 900; color: var(--text-main); font-size: 1.5rem;">Log Registry Empty</h3>
                <p style="color: var(--text-muted); font-size: 1.1rem;">No stock disbursements or collections match the current filter criteria.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .inventory-badge {
        background: var(--primary); color: white; font-size: 0.65rem; font-weight: 900;
        padding: 0.4rem 1.25rem; border-radius: 99px; text-transform: uppercase;
        letter-spacing: 0.1em; box-shadow: 0 5px 15px rgba(99, 102, 241, 0.3);
    }
    .gradient-text {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .header-mesh {
        background: radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.05) 0, transparent 50%),
                    radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.05) 0, transparent 50%),
                    var(--bg-card);
        backdrop-filter: blur(20px);
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.05);
    }
    .log-row:hover {
        transform: scale(1.008) translateY(-3px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.05) !important;
        border-color: var(--primary-light) !important;
    }
    .status-badge {
        display: inline-block;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
    }
    @media (max-width: 768px) {
        .header-mesh {
            padding: 2rem 1.5rem !important;
            margin-bottom: 2rem !important;
            border-radius: 24px !important;
        }
        .header-mesh h1 {
            font-size: 2.2rem !important;
        }
        .filters-card {
            padding: 1.5rem !important;
            border-radius: 20px !important;
            margin-bottom: 2rem !important;
        }
        .registry-card {
            padding: 1.5rem !important;
            border-radius: 24px !important;
            margin-bottom: 2rem !important;
        }
        .table-scroll-wrapper {
            overflow-x: visible !important;
            padding: 0.5rem !important;
        }
        .responsive-log-table { min-width: auto !important; border-spacing: 0 1.5rem !important; border-collapse: separate !important; }
        .responsive-log-table thead { display: none; }
        .responsive-log-table tbody { display: block; }
        .responsive-log-table tr {
            display: block;
            margin-bottom: 2rem;
            padding: 2rem !important;
            border-radius: 28px !important;
            background: var(--bg-card) !important;
            box-shadow: 0 12px 35px rgba(0,0,0,0.06) !important;
            border: 1px solid var(--border-color) !important;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .responsive-log-table tr::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 8px;
            background: var(--primary);
            opacity: 0.8;
        }
        .responsive-log-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.1rem 0 !important;
            border-bottom: 1px solid rgba(0,0,0,0.04) !important;
            border-radius: 0 !important;
            width: 100% !important;
        }
        .responsive-log-table td:last-child { border-bottom: none !important; padding-top: 1.5rem !important; }
        .responsive-log-table td::before {
            content: attr(data-label);
            font-weight: 850;
            color: var(--text-muted);
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .responsive-log-table td > div,
        .responsive-log-table td > span { text-align: right; font-weight: 700; font-size: 0.95rem; }
    }
    @keyframes blink-bg-red {
        0% {
            background-color: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
        50% {
            background-color: #ef4444;
            border-color: #ef4444;
            color: #ffffff;
        }
        100% {
            background-color: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Search input debounce for premium user experience
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            let debounceTimer;
            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    searchInput.form.submit();
                }, 600); // 600ms debounce
            });
            
            // Set cursor to the end of the text on load if focused
            if (document.activeElement === searchInput || searchInput.value) {
                const val = searchInput.value;
                searchInput.value = '';
                searchInput.value = val;
                searchInput.focus();
            }
        }
    });
</script>
@endsection
