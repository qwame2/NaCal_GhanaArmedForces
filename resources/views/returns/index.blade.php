@extends('layouts.dashboard')

@section('content')
<div class="animate-slide-up" style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem;">
    
    <!-- Modern Returns Header -->
    <div class="glass-card header-mesh" style="padding: 3rem; border-radius: 32px; margin-bottom: 3rem; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.4);">
        <div style="position: relative; z-index: 1;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem;">
                <span style="background: #f59e0b; color: white; font-size: 0.65rem; font-weight: 900; padding: 0.4rem 1.25rem; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.1em;">Stock Recovery</span>
                <div style="width: 4px; height: 4px; background: var(--text-muted); border-radius: 50%; opacity: 0.5;"></div>
                <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">Inventory Lifecycle</span>
            </div>
            <h1 style="margin: 0; font-size: 3rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.05em; line-height: 1;">Return <span style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Items</span></h1>
            <p style="margin: 12px 0 0; color: var(--text-muted); font-size: 1.1rem; font-weight: 500; opacity: 0.8;">Re-integrate temporary issuances back into active inventory store.</p>
        </div>
    </div>

    <!-- Active Temporary Issuances Grid -->
    <div class="glass-card" style="border-radius: 28px; padding: 2.5rem; border: 1px solid var(--border-color);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
            <div>
                <h3 style="margin: 0; font-size: 1.5rem; font-weight: 900; color: var(--text-main);">Pending Returns</h3>
                <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 0.9rem; font-weight: 600;">Items currently on temporary allocation</p>
            </div>
            <div style="position: relative; width: 300px;">
                <i data-lucide="search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 18px; color: var(--text-muted);"></i>
                <input type="text" id="returnSearch" placeholder="Search beneficiary or item..." style="width: 100%; padding: 0.85rem 1rem 0.85rem 3rem; border-radius: 14px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-weight: 700;" oninput="filterReturns()">
            </div>
        </div>

        @if($issuedItems->count() > 0)
        <div class="table-scroll-wrapper" style="width: 100%; overflow-x: auto; padding-bottom: 1.5rem;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0 1rem; min-width: 1000px;">
                <thead>
                    <tr style="text-align: left; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 900;">
                        <th style="padding: 0 1.5rem 0.5rem;">Issue Date</th>
                        <th style="padding: 0 1.5rem 0.5rem;">Beneficiary</th>
                        <th style="padding: 0 1.5rem 0.5rem;">Allocated Item</th>
                        <th style="padding: 0 1.5rem 0.5rem;">Ledge</th>
                        <th style="padding: 0 1.5rem 0.5rem;">Outstanding Qty</th>
                        <th style="padding: 0 1.5rem 0.5rem; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody id="returnsTableBody">
                    @foreach($issuedItems as $item)
                    @if($item->quantity > 0)
                    <tr class="return-row" data-search="{{ strtolower($item->beneficiary . ' ' . $item->description) }}" style="background: var(--bg-card); border-radius: 18px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                        <td style="padding: 1.5rem; border-radius: 18px 0 0 18px; font-weight: 700; color: var(--text-main);">
                            {{ date('d M Y', strtotime($item->issuance_date)) }}
                        </td>
                        <td style="padding: 1.5rem; font-weight: 850; color: var(--text-main);">{{ $item->beneficiary }}</td>
                        <td style="padding: 1.5rem; font-weight: 850; color: #f59e0b;">{{ $item->description }}</td>
                        <td style="padding: 1.5rem;">
                            <span style="background: rgba(0,0,0,0.03); padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.7rem; font-weight: 800;">LEDGE {{ $item->ledge_category }}</span>
                        </td>
                        <td style="padding: 1.5rem; font-weight: 900; font-size: 1.25rem;">{{ $item->quantity }}</td>
                        <td style="padding: 1.5rem; border-radius: 0 18px 18px 0; text-align: right;">
                            <button onclick="openReturnModal({{ json_encode($item) }})" class="recover-btn">
                                <i data-lucide="corner-up-left" style="width: 16px;"></i> Recover Stock
                            </button>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="padding: 10rem 0; text-align: center;">
            <i data-lucide="refresh-cw" style="width: 80px; height: 80px; color: var(--text-muted); opacity: 0.1; margin-bottom: 2rem;"></i>
            <h3 style="font-weight: 900; color: var(--text-main);">No Temporary Allocations</h3>
            <p style="color: var(--text-muted);">There are no items currently out on temporary issuance.</p>
        </div>
        @endif
    </div>
</div>

<!-- Return Modal -->
<div id="returnModal" class="modal-backdrop">
    <div class="glass-card" style="width: 450px; padding: 2.5rem; border-radius: 28px; position: relative;">
        <h3 style="margin: 0 0 0.5rem; font-size: 1.75rem; font-weight: 900; color: var(--text-main);">Process Return</h3>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 2rem;">Restoring stock to the central registry.</p>
        
        <form action="{{ route('returns.store') }}" method="POST">
            @csrf
            <input type="hidden" name="issued_item_id" id="modal_item_id">
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.7rem; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Item to Recover</label>
                <div id="modal_item_desc" style="padding: 1rem; background: var(--bg-main); border-radius: 12px; font-weight: 800; color: #f59e0b; border: 1px solid var(--border-color);"></div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 2.5rem;">
                <div>
                    <label style="display: block; font-size: 0.7rem; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Qty to Return</label>
                    <input type="number" name="return_qty" id="modal_return_qty" min="1" required style="width: 100%; padding: 1rem; border-radius: 12px; border: 2px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-weight: 800;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.7rem; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Recovery Date</label>
                    <input type="date" name="return_date" value="{{ date('Y-m-d') }}" required style="width: 100%; padding: 1rem; border-radius: 12px; border: 2px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-weight: 800;">
                </div>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="button" onclick="closeReturnModal()" class="modern-action-btn secondary" style="flex: 1; height: auto;">Cancel</button>
                <button type="submit" class="confirm-btn-final" style="flex: 2; height: auto; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    Confirm Recovery
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .header-mesh {
        background: radial-gradient(at 0% 0%, rgba(245, 158, 11, 0.05) 0, transparent 50%),
                    var(--bg-card);
        backdrop-filter: blur(20px);
    }
    .recover-btn {
        padding: 0.75rem 1.25rem; border-radius: 12px; border: 2px solid #f59e0b;
        background: transparent; color: #f59e0b; font-weight: 900; cursor: pointer;
        display: inline-flex; align-items: center; gap: 8px; transition: 0.3s;
        font-size: 0.85rem;
    }
    .recover-btn:hover { background: #f59e0b; color: white; box-shadow: 0 8px 20px rgba(245, 158, 11, 0.2); }
    
    .modal-backdrop {
        position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px);
        z-index: 2000; display: none; align-items: center; justify-content: center; opacity: 0; transition: 0.3s;
    }
    .modal-backdrop.active { display: flex; opacity: 1; }

    .return-row:hover { transform: scale(1.005) translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
</style>

<script>
    function openReturnModal(item) {
        document.getElementById('modal_item_id').value = item.id;
        document.getElementById('modal_item_desc').innerText = item.description + ' (' + item.beneficiary + ')';
        document.getElementById('modal_return_qty').value = item.quantity;
        document.getElementById('modal_return_qty').max = item.quantity;
        document.getElementById('returnModal').classList.add('active');
    }

    function closeReturnModal() {
        document.getElementById('returnModal').classList.remove('active');
    }

    function filterReturns() {
        const term = document.getElementById('returnSearch').value.toLowerCase();
        document.querySelectorAll('.return-row').forEach(row => {
            row.style.display = row.dataset.search.includes(term) ? 'table-row' : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection
