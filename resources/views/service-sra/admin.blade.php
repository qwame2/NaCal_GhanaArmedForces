@php
    $layout = auth()->user()->isMainAdminOrSub() ? 'layouts.dashboard' : 'layouts.admin';
@endphp
@extends($layout)
@section('content')
@section('title', 'Service SRA – Admin Approval')

<style>
    .sra-table-row { border-bottom: 1px solid var(--border-color); transition: background 0.15s; }
    .sra-table-row:hover { background: rgba(22,163,74,0.03); }
    .sra-table-row:last-child { border-bottom: none; }
    .sra-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 99px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }
    .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.5); backdrop-filter: blur(8px); z-index: 10000; display: none; align-items: center; justify-content: center; }
    .modal-box { background: var(--bg-card); border-radius: 24px; padding: 2.5rem; max-width: 680px; width: 95%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 60px rgba(0,0,0,0.2); animation: slideUp 0.3s cubic-bezier(0.34,1.56,0.64,1); }
    @keyframes slideUp { from { opacity:0; transform: translateY(30px); } to { opacity:1; transform: translateY(0); } }
</style>

<div class="animate-slide-up">
    {{-- Header --}}
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(22,163,74,0.1); color: var(--primary); font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em;">Admin Review Queue</span>
                @if($pending->count() > 0)
                    <span style="background: #ef4444; color: white; min-width: 22px; height: 22px; padding: 0 6px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 800; animation: reqs-pulse 1.8s infinite;">{{ $pending->count() }}</span>
                @endif
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; color: var(--text-main); margin: 0;">Service <span style="color: var(--primary);">SRA Approvals</span></h2>
            <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Review and approve Service Received Advice forms submitted by Store Officers.</p>
        </div>
    </div>



    {{-- Pending Table --}}
    <div class="glass-card" style="border-radius: 24px; overflow: hidden; padding: 0; margin-bottom: 2rem;">
        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.75rem;">
            <i data-lucide="clock" style="color: #10b981; width: 20px;"></i>
            <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-main);">Awaiting Your Review ({{ $pending->count() }})</h3>
        </div>
        @if($pending->isEmpty())
            <div style="padding: 3rem 2rem; text-align: center; color: var(--text-muted);">
                <i data-lucide="check-circle" style="width: 48px; height: 48px; opacity: 0.3; display: block; margin: 0 auto 1rem;"></i>
                <p style="margin: 0; font-weight: 600;">No pending SRAs — all caught up!</p>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--bg-main);">
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">SRA No.</th>
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Submitted By</th>
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Supplier</th>
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Delivery</th>
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Date</th>
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pending as $sra)
                        <tr class="sra-table-row" style="cursor: pointer;" onclick="openSraModal({{ $sra->id }})">
                            <td style="padding: 1rem 1.5rem; font-weight: 800; color: var(--primary); font-size: 0.85rem;">{{ $sra->sra_number }}</td>
                            <td style="padding: 1rem 1.5rem;">
                                <div style="font-weight: 700; color: var(--text-main);">{{ $sra->submitter->name ?? '—' }}</div>
                                <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $sra->dept }}</div>
                            </td>
                            <td style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text-main);">{{ $sra->supplier_name }}</td>
                            <td style="padding: 1rem 1.5rem;">
                                <span class="sra-badge" style="background: {{ $sra->delivery_type === 'full' ? 'rgba(16,185,129,0.1)' : 'rgba(16,185,129,0.1)' }}; color: {{ $sra->delivery_type === 'full' ? '#10b981' : '#10b981' }};">
                                    {{ ucfirst($sra->delivery_type) }}
                                </span>
                            </td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">{{ $sra->date_of_delivery->format('d M Y') }}</td>
                            <td style="padding: 1rem 1.5rem; text-align: center;">
                                <button onclick="event.stopPropagation(); openSraModal({{ $sra->id }})" style="background: rgba(22,163,74,0.08); color: var(--primary); border: 1px solid rgba(22,163,74,0.2); border-radius: 10px; padding: 0.5rem 1rem; font-size: 0.78rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                    <i data-lucide="clipboard-check" style="width: 13px;"></i> Review
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- History Table --}}
    <div class="glass-card" style="border-radius: 24px; overflow: hidden; padding: 0;">
        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.75rem;">
            <i data-lucide="history" style="color: var(--text-muted); width: 20px;"></i>
            <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-main);">Recent Decisions ({{ $history->count() }})</h3>
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
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Admin Decision</th>
                            <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Decided At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $sra)
                        <tr class="sra-table-row">
                            <td style="padding: 1rem 1.5rem; font-weight: 800; color: var(--primary);">{{ $sra->sra_number }}</td>
                            <td style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text-main);">{{ $sra->submitter->name ?? '—' }}</td>
                            <td style="padding: 1rem 1.5rem; color: var(--text-muted);">{{ $sra->supplier_name }}</td>
                            <td style="padding: 1rem 1.5rem;">
                                <span class="sra-badge" style="background: {{ $sra->admin_status === 'approved' ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)' }}; color: {{ $sra->admin_status === 'approved' ? '#10b981' : '#ef4444' }};">
                                    {{ ucfirst($sra->admin_status) }}
                                </span>
                            </td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.82rem; color: var(--text-muted);">{{ $sra->admin_approved_at?->format('d M Y H:i') ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- SRA Review Modal --}}
<div class="modal-overlay" id="sraModalOverlay" onclick="closeSraModal(event)">
    <div class="modal-box" id="sraModalBox" onclick="event.stopPropagation()">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <div style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--primary); letter-spacing: 0.06em; margin-bottom: 4px;">Service SRA Review</div>
                <h2 id="modal-sra-number" style="font-size: 1.4rem; font-weight: 900; margin: 0; color: var(--text-main);">SRA-000000</h2>
            </div>
            <button onclick="closeSraModal()" style="background: var(--bg-main); border: 1px solid var(--border-color); width: 36px; height: 36px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="x" style="width: 18px;"></i>
            </button>
        </div>

        <div id="modal-sra-details" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; background: var(--bg-main); border-radius: 14px; padding: 1.25rem;"></div>

        <div id="modal-sra-details-text" style="margin-bottom: 1.5rem;"></div>

        <div id="modal-decision-form" style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                <i data-lucide="message-square" style="width: 14px; color: var(--primary);"></i>
                Notes / Remarks (optional)
            </label>
            <textarea id="modal-notes" rows="3" style="width: 100%; border: 1.5px solid var(--border-color); background: var(--bg-card); color: var(--text-main); padding: 0.75rem 1rem; border-radius: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 600; resize: vertical; box-sizing: border-box;" placeholder="Add any notes or remarks for the Store Officer..."></textarea>
            <div style="display: flex; gap: 1rem; margin-top: 1.25rem; justify-content: flex-end; flex-wrap: wrap;">
                <button onclick="processAdminSra('declined')" id="btnDecline" style="padding: 0.85rem 2rem; border: 1px solid rgba(239,68,68,0.3); background: rgba(239,68,68,0.08); color: #ef4444; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="x-circle" style="width: 16px;"></i> Decline
                </button>
                <button onclick="processAdminSra('approved')" id="btnApprove" style="padding: 0.85rem 2rem; border: none; background: linear-gradient(135deg, #10b981, #059669); color: white; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 8px 20px -5px rgba(16,185,129,0.4);">
                    <i data-lucide="check-circle" style="width: 16px;"></i> Approve
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
window.srasData = @json($pending->keyBy('id'));
window.currentSraId = null;

window.openSraModal = function(id) {
    const sra = window.srasData[id];
    if (!sra) return;
    window.currentSraId = id;

    document.getElementById('modal-sra-number').textContent = sra.sra_number;

    const deliveryLabel = sra.delivery_type === 'full' ? 'Full Delivery' : 'Part Delivery';
    document.getElementById('modal-sra-details').innerHTML = `
        <div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Submitted By</div><div style="font-weight:700;color:var(--text-main);">${sra.submitter?.name ?? '—'}</div><div style="font-size:0.75rem;color:var(--text-muted);">${sra.dept ?? ''}</div></div>
        <div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Supplier</div><div style="font-weight:700;color:var(--text-main);">${sra.supplier_name}</div>${sra.supplier_address ? `<div style="font-size:0.75rem;color:var(--text-muted);">${sra.supplier_address}</div>` : ''}</div>
        <div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Vehicle</div><div style="font-weight:600;color:var(--text-main);">${sra.vehicle_number || '—'}</div></div>
        <div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Date</div><div style="font-weight:700;color:var(--text-main);">${sra.date_of_delivery}</div></div>
        <div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Delivery Type</div><span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;font-size:0.7rem;font-weight:800;background:${sra.delivery_type === 'full' ? 'rgba(16,185,129,0.1)' : 'rgba(16,185,129,0.1)'};color:${sra.delivery_type === 'full' ? '#10b981' : '#10b981'};">${deliveryLabel}</span></div>
        ${sra.ae_number ? `<div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">A&E No.</div><div style="font-weight:600;">${sra.ae_number}</div></div>` : ''}
        ${sra.lpo_number ? `<div><div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">LPO No.</div><div style="font-weight:600;">${sra.lpo_number}</div></div>` : ''}
    `;

    document.getElementById('modal-sra-details-text').innerHTML = `
        <div style="font-size:0.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:8px;">Details of Order / Service</div>
        <div style="background:var(--bg-main);border-radius:12px;padding:1rem 1.25rem;font-size:0.88rem;font-weight:500;color:var(--text-main);white-space:pre-wrap;line-height:1.7;border:1px solid var(--border-color);">${sra.details}</div>
        ${sra.previous_sra_nos ? `<div style="margin-top:0.75rem;font-size:0.72rem;font-weight:800;color:#10b981;">Previous SRA Nos: ${sra.previous_sra_nos}</div>` : ''}
    `;

    document.getElementById('modal-notes').value = '';
    document.getElementById('sraModalOverlay').style.display = 'flex';
    if (typeof lucide !== 'undefined') lucide.createIcons();
};

window.closeSraModal = function(e) {
    if (e && e.target !== document.getElementById('sraModalOverlay')) return;
    document.getElementById('sraModalOverlay').style.display = 'none';
    window.currentSraId = null;
};

window.processAdminSra = function(action) {
    if (!window.currentSraId) return;
    const notes = document.getElementById('modal-notes').value.trim();
    const label = action === 'approved' ? 'Approve' : 'Decline';

    Swal.fire({
        title: `${label} this SRA?`,
        text: action === 'declined' ? 'The Store Officer will be notified of the decline.' : 'The SRA will proceed to the Head of Stores for final approval.',
        icon: action === 'approved' ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonText: label,
        confirmButtonColor: action === 'approved' ? '#10b981' : '#ef4444',
        cancelButtonColor: '#64748b',
    }).then(result => {
        if (!result.isConfirmed) return;

        const $btn = document.getElementById(action === 'approved' ? 'btnApprove' : 'btnDecline');
        const origHtml = $btn.innerHTML;
        $btn.innerHTML = '<i data-lucide="loader" style="width:16px;"></i> Processing...';
        $btn.disabled = true;
        if (typeof lucide !== 'undefined') lucide.createIcons();

        fetch(`/admin/service-sra/${window.currentSraId}/process`, {
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
                document.getElementById('sraModalOverlay').style.display = 'none';
                if (typeof window.showToast === 'function') {
                    window.showToast(res.message, 'success');
                }
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
    });
};

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const reviewId = urlParams.get('review');
    if (reviewId) {
        window.openSraModal(parseInt(reviewId));
    }
});
</script>
@endpush
