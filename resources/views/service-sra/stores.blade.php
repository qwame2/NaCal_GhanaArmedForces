@extends('layouts.admin')
@section('content')
@section('title', 'Service SRA – Head of Stores Final Approval')

<style>
    .sra-table-row { border-bottom: 1px solid var(--border-color); transition: background 0.15s; }
    .sra-table-row:hover { background: rgba(16,185,129,0.03); }
    .sra-table-row:last-child { border-bottom: none; }
    .sra-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 99px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }
    .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.5); backdrop-filter: blur(8px); z-index: 10000; display: none; align-items: center; justify-content: center; }
    .modal-box { background: var(--bg-card); border-radius: 24px; padding: 2.5rem; max-width: 680px; width: 95%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 60px rgba(0,0,0,0.2); animation: slideUp 0.3s cubic-bezier(0.34,1.56,0.64,1); }
    @keyframes slideUp { from { opacity:0; transform: translateY(30px); } to { opacity:1; transform: translateY(0); } }
</style>

<div class="animate-slide-up">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(16,185,129,0.1); color: #10b981; font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em;">Head of Stores — Final Review</span>
                @if($pending->count() > 0)
                    <span style="background: #ef4444; color: white; min-width: 22px; height: 22px; padding: 0 6px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 800; animation: reqs-pulse 1.8s infinite;">{{ $pending->count() }}</span>
                @endif
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main); margin: 0;">Service SRA <span style="color: #10b981;">Final Approval</span></h2>
            <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Final review and approval of SRAs already cleared by Admin. Your approval enables receipt download.</p>
        </div>
    </div>

    @if(auth()->user()->role === 'Main Admin' || auth()->user()->is_admin)
        <div style="display: flex; gap: 0.5rem; background: var(--bg-card); padding: 4px; border-radius: 12px; border: 1px solid var(--border-color); width: fit-content; margin-bottom: 1.5rem;">
            <a href="{{ route('admin.service-sra.index') }}" 
               style="padding: 0.5rem 1.25rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 6px; transition: all 0.2s; 
                      background: transparent; 
                      color: var(--text-muted);"
               onmouseover="this.style.background='rgba(22,163,74,0.05)'"
               onmouseout="this.style.background='transparent'">
                <i data-lucide="shield-alert" style="width: 14px; height: 14px;"></i>
                Admin Queue (Stage 1)
            </a>
            <a href="{{ route('stores.service-sra.index') }}" 
               style="padding: 0.5rem 1.25rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 6px; transition: all 0.2s; 
                      background: var(--primary); 
                      color: white;">
                <i data-lucide="shield-check" style="width: 14px; height: 14px;"></i>
                Stores Queue (Stage 2)
            </a>
        </div>
    @endif

    {{-- Pending Table --}}
    <div class="glass-card" style="border-radius: 24px; overflow: hidden; padding: 0; margin-bottom: 2rem;">
        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.75rem;">
            <i data-lucide="shield-check" style="color: #10b981; width: 20px;"></i>
            <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-main);">Awaiting Final Approval ({{ $pending->count() }})</h3>
        </div>
        @if($pending->isEmpty())
            <div style="padding: 3rem 2rem; text-align: center; color: var(--text-muted);">
                <i data-lucide="check-circle" style="width: 48px; height: 48px; opacity: 0.3; display: block; margin: 0 auto 1rem;"></i>
                <p style="margin: 0; font-weight: 600;">No SRAs awaiting final approval.</p>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--bg-main);">
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">SRA No.</th>
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Submitted By</th>
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Supplier</th>
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Admin Approved By</th>
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Date</th>
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pending as $sra)
                        <tr class="sra-table-row" style="cursor: pointer;" onclick="openStoresModal({{ $sra->id }})">
                            <td style="padding: 1rem 1.5rem; font-weight: 800; color: #10b981;">{{ $sra->sra_number }}</td>
                            <td style="padding: 1rem 1.5rem;">
                                <div style="font-weight: 700; color: var(--text-main);">{{ $sra->submitter->name ?? '—' }}</div>
                                <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $sra->dept }}</div>
                            </td>
                            <td style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text-main);">{{ $sra->supplier_name }}</td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">{{ $sra->admin_approved_by ?? '—' }}</td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">{{ $sra->date_of_delivery->format('d M Y') }}</td>
                            <td style="padding: 1rem 1.5rem; text-align: center;">
                                <button onclick="event.stopPropagation(); openStoresModal({{ $sra->id }})" style="background: rgba(16,185,129,0.08); color: #10b981; border: 1px solid rgba(16,185,129,0.2); border-radius: 10px; padding: 0.5rem 1rem; font-size: 0.78rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                    <i data-lucide="shield-check" style="width: 13px;"></i> Final Review
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- History --}}
    <div class="glass-card" style="border-radius: 24px; overflow: hidden; padding: 0;">
        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.75rem;">
            <i data-lucide="history" style="color: var(--text-muted); width: 20px;"></i>
            <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-main);">Final Decisions ({{ $history->count() }})</h3>
        </div>
        @if($history->isEmpty())
            <div style="padding: 2rem; text-align: center; color: var(--text-muted);"><p style="margin: 0; font-weight: 600;">No decisions made yet.</p></div>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--bg-main);">
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">SRA No.</th>
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Submitted By</th>
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Supplier</th>
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Stores Decision</th>
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Decided At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $sra)
                        <tr class="sra-table-row">
                            <td style="padding: 1rem 1.5rem; font-weight: 800; color: #10b981;">{{ $sra->sra_number }}</td>
                            <td style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text-main);">{{ $sra->submitter->name ?? '—' }}</td>
                            <td style="padding: 1rem 1.5rem; color: var(--text-muted);">{{ $sra->supplier_name }}</td>
                            <td style="padding: 1rem 1.5rem;">
                                <span class="sra-badge" style="background: {{ $sra->stores_status === 'approved' ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)' }}; color: {{ $sra->stores_status === 'approved' ? '#10b981' : '#ef4444' }};">
                                    {{ ucfirst($sra->stores_status) }}
                                </span>
                            </td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.82rem; color: var(--text-muted);">{{ $sra->stores_approved_at?->format('d M Y H:i') ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Final Review Modal --}}
<div class="modal-overlay" id="storesModalOverlay" onclick="closeStoresModal(event)">
    <div class="modal-box" id="storesModalBox" onclick="event.stopPropagation()">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <div style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: #10b981; letter-spacing: 0.06em; margin-bottom: 4px;">Final Stores Review</div>
                <h2 id="stores-modal-sra-number" style="font-size: 1.4rem; font-weight: 900; margin: 0; color: var(--text-main);">SRA-000000</h2>
            </div>
            <button onclick="closeStoresModal()" style="background: var(--bg-main); border: 1px solid var(--border-color); width: 36px; height: 36px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="x" style="width: 18px;"></i>
            </button>
        </div>

        <div id="stores-modal-details" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; background: var(--bg-main); border-radius: 14px; padding: 1.25rem;"></div>
        <div id="stores-modal-text" style="margin-bottom: 1.5rem;"></div>

        <div style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Notes / Remarks (optional)</label>
            <textarea id="stores-modal-notes" rows="3" style="width: 100%; border: 1.5px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; resize: vertical; box-sizing: border-box;" placeholder="Final notes for the Store Officer..."></textarea>
            <div style="display: flex; gap: 1rem; margin-top: 1.25rem; justify-content: flex-end; flex-wrap: wrap;">
                <button onclick="processStoresSra('declined')" id="storesBtnDecline" style="padding: 0.85rem 2rem; border: 1px solid rgba(239,68,68,0.3); background: rgba(239,68,68,0.08); color: #ef4444; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="x-circle" style="width: 16px;"></i> Decline
                </button>
                <button onclick="processStoresSra('approved')" id="storesBtnApprove" style="padding: 0.85rem 2rem; border: none; background: linear-gradient(135deg, #10b981, #059669); color: white; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 8px 20px -5px rgba(16,185,129,0.4);">
                    <i data-lucide="shield-check" style="width: 16px;"></i> Final Approve
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
const storesSrasData = @json($pending->keyBy('id'));
let currentStoresSraId = null;

function openStoresModal(id) {
    const sra = storesSrasData[id];
    if (!sra) return;
    currentStoresSraId = id;

    document.getElementById('stores-modal-sra-number').textContent = sra.sra_number;

    document.getElementById('stores-modal-details').innerHTML = `
        <div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Submitted By</div><div style="font-weight:700;color:var(--text-main);">${sra.submitter?.name ?? '—'}</div><div style="font-size:0.75rem;color:var(--text-muted);">${sra.dept ?? ''}</div></div>
        <div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Supplier</div><div style="font-weight:700;color:var(--text-main);">${sra.supplier_name}</div></div>
        <div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Admin Approved By</div><div style="font-weight:700;color:#10b981;">${sra.admin_approved_by ?? '—'}</div></div>
        <div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Delivery</div><span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;font-size:0.7rem;font-weight:800;background:${sra.delivery_type === 'full' ? 'rgba(16,185,129,0.1)' : 'rgba(16,185,129,0.1)'};color:${sra.delivery_type === 'full' ? '#10b981' : '#10b981'};">${sra.delivery_type === 'full' ? 'Full' : 'Part'}</span></div>
        <div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Date</div><div style="font-weight:700;">${sra.date_of_delivery}</div></div>
    `;

    document.getElementById('stores-modal-text').innerHTML = `
        <div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:8px;">Details of Order / Service</div>
        <div style="background:var(--bg-main);border-radius:12px;padding:1rem 1.25rem;font-size:0.88rem;font-weight:500;color:var(--text-main);white-space:pre-wrap;line-height:1.7;border:1px solid var(--border-color);">${sra.details}</div>
    `;

    document.getElementById('stores-modal-notes').value = '';
    document.getElementById('storesModalOverlay').style.display = 'flex';
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeStoresModal(e) {
    if (e && e.target !== document.getElementById('storesModalOverlay')) return;
    document.getElementById('storesModalOverlay').style.display = 'none';
    currentStoresSraId = null;
}

function processStoresSra(action) {
    if (!currentStoresSraId) return;
    const notes = document.getElementById('stores-modal-notes').value.trim();

    Swal.fire({
        title: `${action === 'approved' ? 'Final Approve' : 'Decline'} this SRA?`,
        text: action === 'approved' ? 'The Store Officer will be able to download the SRA receipt.' : 'The SRA will be declined.',
        icon: action === 'approved' ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonText: action === 'approved' ? 'Yes, Final Approve' : 'Decline',
        confirmButtonColor: action === 'approved' ? '#10b981' : '#ef4444',
        cancelButtonColor: '#64748b',
    }).then(result => {
        if (!result.isConfirmed) return;

        const $btn = document.getElementById(action === 'approved' ? 'storesBtnApprove' : 'storesBtnDecline');
        const origHtml = $btn.innerHTML;
        $btn.innerHTML = '<i data-lucide="loader" style="width:16px;"></i> Processing...';
        $btn.disabled = true;
        if (typeof lucide !== 'undefined') lucide.createIcons();

        fetch(`/stores/service-sra/${currentStoresSraId}/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ action, notes }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                document.getElementById('storesModalOverlay').style.display = 'none';
                if (typeof window.showToast === 'function') window.showToast(res.message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                $btn.innerHTML = origHtml;
                $btn.disabled = false;
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Network Error', text: 'Please try again.' });
            $btn.innerHTML = origHtml;
            $btn.disabled = false;
        });
}

jQuery(document).ready(function($) {
    const urlParams = new URLSearchParams(window.location.search);
    const reviewId = urlParams.get('review');
    if (reviewId) {
        openStoresModal(parseInt(reviewId));
    }
});
</script>
@endpush
