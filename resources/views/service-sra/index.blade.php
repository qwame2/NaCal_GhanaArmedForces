@extends('layouts.dashboard')
@section('content')
<style>
    .sra-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .sra-table-row {
        border-bottom: 1px solid var(--border-color);
        transition: background 0.15s;
    }
    .sra-table-row:hover { background: rgba(99,102,241,0.03); }
    .sra-table-row:last-child { border-bottom: none; }

    /* Modern Premium Filter Card Section */
    .filter-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.04), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .filter-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.75rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.25rem;
    }

    .filter-row {
        display: flex;
        gap: 0.85rem;
        flex-wrap: wrap;
        align-items: center;
        width: 100%;
        margin: 0;
    }

    .filter-field-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .filter-icon {
        position: absolute;
        left: 14px;
        color: var(--text-muted);
        pointer-events: none;
        transition: color 0.2s ease;
    }

    .filter-control {
        width: 100%;
        padding: 0.7rem 1rem 0.7rem 2.6rem;
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-main);
        color: var(--text-main);
        font-family: inherit;
        font-weight: 600;
        font-size: 0.85rem;
        outline: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
    }

    select.filter-control {
        padding-right: 2.25rem;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19.5 8.25l-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 14px;
    }

    .filter-control:focus {
        border-color: var(--primary);
        background: var(--bg-card);
        box-shadow: 0 0 0 4px var(--primary-glow);
    }

    .filter-control:focus + .filter-icon {
        color: var(--primary);
    }

    .filter-control::placeholder {
        color: var(--text-muted);
        opacity: 0.75;
    }

    .filter-clear-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0.7rem 1.25rem;
        border: 1.5px solid #ef4444;
        border-radius: 12px;
        background: rgba(239, 68, 68, 0.05);
        color: #ef4444;
        font-weight: 800;
        font-size: 0.82rem;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .filter-clear-btn:hover {
        background: #ef4444;
        color: white;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
    }
</style>

<div class="animate-slide-up">
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(99,102,241,0.1); color: var(--primary); font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em;">Stores Division</span>
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main); margin: 0;">My <span style="color: var(--primary);">Service SRAs</span></h2>
            <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Track the status of your submitted Stores / Service Received Advice forms.</p>
        </div>
        <a href="{{ route('service-sra.create') }}" class="btn-primary" style="padding: 0.85rem 1.75rem; border: none; border-radius: 14px; cursor: pointer; background: linear-gradient(135deg, var(--primary), #4f46e5); color: white; font-weight: 800; display: flex; align-items: center; gap: 0.5rem; text-decoration: none; box-shadow: 0 8px 20px -5px rgba(99,102,241,0.4);">
            <i data-lucide="plus-circle" style="width: 18px;"></i> New SRA
        </a>
    </div>

    {{-- Filters --}}
    <div class="filter-card">
        <div class="filter-header">
            <i data-lucide="sliders-horizontal" style="width: 14px; height: 14px; color: var(--primary);"></i>
            <span>Filter Options</span>
        </div>
        <form method="GET" class="filter-row" id="sra-filter-form" action="{{ route('service-sra.index') }}">
            <div class="filter-field-wrapper" style="flex: 1.5; min-width: 240px;">
                <i data-lucide="search" class="filter-icon" style="width: 16px; height: 16px;"></i>
                <input type="text" name="search" id="sra_search_input" class="filter-control" value="{{ request('search') }}" placeholder="Search by SRA #, Supplier, or Details..." autocomplete="off">
            </div>

            <div class="filter-field-wrapper" style="min-width: 180px; flex: 1;">
                <i data-lucide="activity" class="filter-icon" style="width: 14px; height: 14px;"></i>
                <select name="status" onchange="updateSraFilters()" class="filter-control">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status')==='pending'?'selected':'' }}>Awaiting Admin Review</option>
                    <option value="admin_approved" {{ request('status')==='admin_approved'?'selected':'' }}>Awaiting Stores Review</option>
                    <option value="approved" {{ request('status')==='approved'?'selected':'' }}>Fully Approved</option>
                    <option value="declined" {{ request('status')==='declined'?'selected':'' }}>Declined</option>
                </select>
            </div>

            <div class="filter-field-wrapper" style="min-width: 180px; flex: 1;">
                <i data-lucide="truck" class="filter-icon" style="width: 14px; height: 14px;"></i>
                <select name="delivery_type" onchange="updateSraFilters()" class="filter-control">
                    <option value="">All Deliveries</option>
                    <option value="full" {{ request('delivery_type')==='full'?'selected':'' }}>Full Delivery</option>
                    <option value="part" {{ request('delivery_type')==='part'?'selected':'' }}>Part Delivery</option>
                </select>
            </div>

            @if(request()->anyFilled(['status','delivery_type','search']))
            <a href="{{ route('service-sra.index') }}" class="filter-clear-btn" style="text-decoration: none;">
                <i data-lucide="x-circle" style="width:16px; height:16px;"></i>
                <span>Clear Filters</span>
            </a>
            @endif
        </form>
    </div>

    <div id="sra-table-container">
        @include('service-sra._sra_table')
    </div>
</div>

<script>
    let sraDebounceTimer = null;

    function triggerSraFilterUpdate() {
        clearTimeout(sraDebounceTimer);
        sraDebounceTimer = setTimeout(() => {
            updateSraFilters();
        }, 300); // 300ms debounce
    }

    async function updateSraFilters() {
        const form = document.getElementById('sra-filter-form');
        const container = document.getElementById('sra-table-container');
        if (!form || !container) return;

        container.style.opacity = '0.5';

        const formData = new FormData(form);
        const searchParams = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value.trim() !== '') {
                searchParams.append(key, value);
            }
        }

        const url = form.action + '?' + searchParams.toString();

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const html = await response.text();

            container.innerHTML = html;
            container.style.opacity = '1';

            if (window.lucide) {
                window.lucide.createIcons();
            }
            bindSraPaginationClicks();

            window.history.pushState(null, '', url);
        } catch (e) {
            console.error(e);
            container.style.opacity = '1';
        }
    }

    function bindSraPaginationClicks() {
        const container = document.getElementById('sra-table-container');
        if (!container) return;

        const links = container.querySelectorAll('.ajax-sra-page-btn');
        links.forEach(link => {
            link.addEventListener('click', async function(e) {
                e.preventDefault();
                const page = this.dataset.page;
                if (!page) return;

                container.style.opacity = '0.5';
                
                // Construct URL preserving current filter settings
                const form = document.getElementById('sra-filter-form');
                const searchParams = new URLSearchParams();
                if (form) {
                    const formData = new FormData(form);
                    for (const [key, value] of formData.entries()) {
                        if (value.trim() !== '') {
                            searchParams.append(key, value);
                        }
                    }
                }
                searchParams.set('page', page);
                
                const newUrl = '{{ route("service-sra.index") }}' + '?' + searchParams.toString();

                try {
                    const response = await fetch(newUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const html = await response.text();

                    container.innerHTML = html;
                    container.style.opacity = '1';

                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                    bindSraPaginationClicks();

                    window.history.pushState(null, '', newUrl);
                } catch (err) {
                    console.error(err);
                    container.style.opacity = '1';
                }
            });
        });
    }

    async function pollServiceSras() {
        const container = document.getElementById('sra-table-container');
        if (!container) return;

        // Preserve current page and filter parameters
        const searchParams = new URLSearchParams(window.location.search);
        const url = '{{ route("service-sra.index") }}' + '?' + searchParams.toString();

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const html = await response.text();

            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const currentTbody = container.querySelector('tbody');
            const newTbody = doc.querySelector('tbody');
            
            if (currentTbody && newTbody) {
                const currentRows = currentTbody.querySelectorAll('.sra-table-row');
                const newRows = newTbody.querySelectorAll('.sra-table-row');
                
                if (currentRows.length === newRows.length) {
                    // Compare row by row and update only changed rows to prevent visual blinking
                    for (let i = 0; i < currentRows.length; i++) {
                        const cRow = currentRows[i];
                        const nRow = newRows[i];
                        
                        const cKey = `${cRow.dataset.sraId}-${cRow.dataset.status}-${cRow.dataset.adminStatus}`;
                        const nKey = `${nRow.dataset.sraId}-${nRow.dataset.status}-${nRow.dataset.adminStatus}`;
                        
                        if (cKey !== nKey) {
                            cRow.innerHTML = nRow.innerHTML;
                            cRow.dataset.status = nRow.dataset.status;
                            cRow.dataset.adminStatus = nRow.dataset.adminStatus;
                            
                            if (window.lucide) {
                                window.lucide.createIcons({
                                    node: cRow
                                });
                            }
                        }
                    }
                } else {
                    // Row count changed, update tbody
                    currentTbody.innerHTML = newTbody.innerHTML;
                    if (window.lucide) {
                        window.lucide.createIcons({
                            node: currentTbody
                        });
                    }
                }

                // Update pagination if changed
                const currentPagination = document.getElementById('sra-pagination');
                const newPagination = doc.getElementById('sra-pagination');

                if (currentPagination && newPagination) {
                    if (currentPagination.innerHTML !== newPagination.innerHTML) {
                        currentPagination.innerHTML = newPagination.innerHTML;
                    }
                } else if (newPagination && !currentPagination) {
                    container.appendChild(newPagination.cloneNode(true));
                } else if (!newPagination && currentPagination) {
                    currentPagination.remove();
                }

                bindSraPaginationClicks();
            } else if (newTbody && !currentTbody) {
                // It was empty and now has items
                container.innerHTML = html;
                if (window.lucide) {
                    window.lucide.createIcons();
                }
                bindSraPaginationClicks();
            } else if (!newTbody && currentTbody) {
                // It became empty (e.g. SRA deleted or filtered to empty)
                container.innerHTML = html;
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            }
        } catch (e) {
            console.error('SRA polling error:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('sra_search_input');
        if (searchInput) {
            if (searchInput.value) {
                searchInput.focus();
                const len = searchInput.value.length;
                searchInput.setSelectionRange(len, len);
            }
            searchInput.addEventListener('input', triggerSraFilterUpdate);
        }

        const form = document.getElementById('sra-filter-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                updateSraFilters();
            });
        }
        
        bindSraPaginationClicks();
        if (window.lucide) {
            window.lucide.createIcons();
        }
        // Poll every 10 seconds for silent refresh
        setInterval(pollServiceSras, 10000);
    });
</script>

@endsection
