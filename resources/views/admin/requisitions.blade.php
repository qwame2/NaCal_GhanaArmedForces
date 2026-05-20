@extends('layouts.admin')
@section('content')
<style>
.req-stat-card{background:var(--bg-card);border-radius:16px;border:1px solid var(--border-color);padding:1.5rem;display:flex;align-items:center;gap:1rem;}
.req-table-row{border-bottom:1px solid var(--border-color);transition:.15s;}
.req-table-row:hover{background:rgba(99,102,241,.03);}
.req-table-row:last-child{border-bottom:none;}
.pill{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);z-index:3000;display:none;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal-box{background:var(--bg-card);border-radius:24px;width:100%;max-width:780px;max-height:92vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 30px 80px rgba(0,0,0,.25);}
.modal-body{flex:1;overflow-y:auto;padding:2rem;}
@keyframes fadeIn{from{opacity:0;transform:scale(.97)}to{opacity:1;transform:scale(1)}}
.modal-box{animation:fadeIn .25s ease;}
</style>

<div style="padding:2rem;">

  {{-- Header --}}
  <div style="margin-bottom:2rem;">
    <div style="font-size:.7rem;font-weight:800;color:#4f46e5;text-transform:uppercase;letter-spacing:.12em;margin-bottom:4px;">Store Management</div>
    <h1 style="font-size:1.75rem;font-weight:900;color:var(--text-main);letter-spacing:-.03em;margin:0;">Store Requisitions</h1>
    <p style="font-size:.9rem;color:var(--text-muted);margin:6px 0 0;">Review and process department item requests</p>
  </div>

  {{-- Stats --}}
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:2rem;">
    <div class="req-stat-card">
      <div style="width:44px;height:44px;background:rgba(99,102,241,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i data-lucide="clock" style="width:20px;color:#6366f1;"></i>
      </div>
      <div>
        <div style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['pending'] }}</div>
        <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Pending</div>
      </div>
    </div>
    <div class="req-stat-card">
      <div style="width:44px;height:44px;background:rgba(220,38,38,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i data-lucide="alert-triangle" style="width:20px;color:#dc2626;"></i>
      </div>
      <div>
        <div style="font-size:1.5rem;font-weight:900;color:#dc2626;">{{ $stats['urgent'] }}</div>
        <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Urgent</div>
      </div>
    </div>
    <div class="req-stat-card">
      <div style="width:44px;height:44px;background:rgba(16,185,129,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i data-lucide="check-circle" style="width:20px;color:#10b981;"></i>
      </div>
      <div>
        <div style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['approved'] }}</div>
        <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Approved</div>
      </div>
    </div>
    <div class="req-stat-card">
      <div style="width:44px;height:44px;background:rgba(245,158,11,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i data-lucide="git-merge" style="width:20px;color:#f59e0b;"></i>
      </div>
      <div>
        <div style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['partially_approved'] }}</div>
        <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Partial</div>
      </div>
    </div>
    <div class="req-stat-card">
      <div style="width:44px;height:44px;background:rgba(239,68,68,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i data-lucide="x-circle" style="width:20px;color:#ef4444;"></i>
      </div>
      <div>
        <div style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['declined'] }}</div>
        <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Declined</div>
      </div>
    </div>
  </div>

  {{-- Filters --}}
  <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.5rem;">
    <select name="status" onchange="this.form.submit()" style="padding:.6rem 1rem;border:1.5px solid var(--border-color);border-radius:10px;background:var(--bg-card);color:var(--text-main);font-weight:700;font-size:.85rem;cursor:pointer;">
      <option value="">All Status</option>
      <option value="pending" {{ request('status')==='pending'?'selected':'' }}>Pending</option>
      <option value="approved" {{ request('status')==='approved'?'selected':'' }}>Approved</option>
      <option value="partially_approved" {{ request('status')==='partially_approved'?'selected':'' }}>Partial</option>
      <option value="declined" {{ request('status')==='declined'?'selected':'' }}>Declined</option>
    </select>
    <select name="priority" onchange="this.form.submit()" style="padding:.6rem 1rem;border:1.5px solid var(--border-color);border-radius:10px;background:var(--bg-card);color:var(--text-main);font-weight:700;font-size:.85rem;cursor:pointer;">
      <option value="">All Priority</option>
      <option value="urgent" {{ request('priority')==='urgent'?'selected':'' }}>Urgent</option>
      <option value="normal" {{ request('priority')==='normal'?'selected':'' }}>Normal</option>
      <option value="low" {{ request('priority')==='low'?'selected':'' }}>Low</option>
    </select>
    <input type="text" name="department" value="{{ request('department') }}" placeholder="Filter by department..." onchange="this.form.submit()" style="padding:.6rem 1rem;border:1.5px solid var(--border-color);border-radius:10px;background:var(--bg-card);color:var(--text-main);font-weight:600;font-size:.85rem;min-width:220px;">
    @if(request()->anyFilled(['status','priority','department']))
    <a href="{{ route('admin.requisitions') }}" style="padding:.6rem 1rem;border:1.5px solid var(--border-color);border-radius:10px;background:var(--bg-card);color:var(--text-muted);font-weight:700;font-size:.85rem;text-decoration:none;display:flex;align-items:center;gap:6px;">
      <i data-lucide="x" style="width:14px;"></i> Clear
    </a>
    @endif
  </form>

  {{-- Table --}}
  <div style="background:var(--bg-card);border-radius:20px;border:1px solid var(--border-color);overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
      <thead style="background:var(--bg-main);">
        <tr>
          <th style="padding:1rem 1.5rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">#</th>
          <th style="padding:1rem 1.5rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Department / Requester</th>
          <th style="padding:1rem 1.5rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Items</th>
          <th style="padding:1rem 1.5rem;text-align:center;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Priority</th>
          <th style="padding:1rem 1.5rem;text-align:center;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Status</th>
          <th style="padding:1rem 1.5rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Date</th>
          <th style="padding:1rem 1.5rem;text-align:center;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($requisitions as $req)
        @php $sb = $req->status_badge; $pb = $req->priority_badge; @endphp
        <tr class="req-table-row">
          <td style="padding:1rem 1.5rem;font-size:.8rem;font-weight:700;color:var(--text-muted);">#{{ $req->id }}</td>
          <td style="padding:1rem 1.5rem;">
            <div style="font-size:.9rem;font-weight:800;color:var(--text-main);">{{ $req->department }}</div>
            <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;">{{ $req->requester_name }}{{ $req->rank_or_title ? ' · '.$req->rank_or_title : '' }}</div>
          </td>
          <td style="padding:1rem 1.5rem;">
            <div style="display:flex;flex-wrap:wrap;gap:4px;">
              @foreach($req->items->take(3) as $item)
              <span style="font-size:.7rem;font-weight:700;color:var(--text-main);background:var(--bg-main);border:1px solid var(--border-color);padding:2px 8px;border-radius:6px;">
                {{ Str::limit($item->description, 20) }} ({{ number_format($item->quantity_requested,0) }})
              </span>
              @endforeach
              @if($req->items->count() > 3)
              <span style="font-size:.7rem;font-weight:700;color:#4f46e5;background:rgba(79,70,229,.1);padding:2px 8px;border-radius:6px;">+{{ $req->items->count()-3 }} more</span>
              @endif
            </div>
          </td>
          <td style="padding:1rem 1.5rem;text-align:center;">
            <span class="pill" style="background:{{ $pb['bg'] }};color:{{ $pb['color'] }};">{{ $pb['label'] }}</span>
          </td>
          <td style="padding:1rem 1.5rem;text-align:center;">
            <span class="pill" style="background:{{ $sb['bg'] }};color:{{ $sb['color'] }};">● {{ $sb['label'] }}</span>
          </td>
          <td style="padding:1rem 1.5rem;font-size:.78rem;color:var(--text-muted);font-weight:600;">{{ $req->created_at->format('d M Y') }}<br>{{ $req->created_at->format('H:i') }}</td>
          <td style="padding:1rem 1.5rem;text-align:center;">
            <button onclick="openRequisitionModal({{ $req->id }})"
              style="background:rgba(79,70,229,.1);color:#4f46e5;border:none;padding:.5rem 1rem;border-radius:10px;font-weight:800;font-size:.78rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:.15s;" onmouseover="this.style.background='#4f46e5';this.style.color='white'" onmouseout="this.style.background='rgba(79,70,229,.1)';this.style.color='#4f46e5'">
              <i data-lucide="eye" style="width:14px;"></i> Review
            </button>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" style="padding:3rem;text-align:center;color:var(--text-muted);">
            <i data-lucide="inbox" style="width:32px;margin-bottom:.75rem;opacity:.3;"></i>
            <p style="font-weight:700;color:var(--text-main);">No requisitions found</p>
            <p style="font-size:.85rem;">Department requests will appear here.</p>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
    @if($requisitions->hasPages())
    <div style="padding:1rem 1.5rem;border-top:1px solid var(--border-color);">{{ $requisitions->links() }}</div>
    @endif
  </div>
</div>

{{-- Review Modal --}}
<div class="modal-overlay" id="reqModal" onclick="if(event.target===this)closeModal()">
  <div class="modal-box">
    <div style="padding:1.5rem 2rem;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
      <div style="display:flex;align-items:center;gap:1rem;">
        <div style="width:44px;height:44px;background:rgba(79,70,229,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;">
          <i data-lucide="clipboard-list" style="width:20px;color:#4f46e5;"></i>
        </div>
        <div>
          <h2 style="margin:0;font-size:1.1rem;font-weight:900;color:var(--text-main);">Requisition Review</h2>
          <p id="modalSubtitle" style="margin:0;font-size:.8rem;color:var(--text-muted);font-weight:500;"></p>
        </div>
      </div>
      <button onclick="closeModal()" style="background:var(--bg-main);border:none;width:34px;height:34px;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
        <i data-lucide="x" style="width:18px;color:var(--text-muted);"></i>
      </button>
    </div>
    <div class="modal-body" id="modalBody">
      <div style="text-align:center;padding:2rem;color:var(--text-muted);">Loading...</div>
    </div>
    <div id="modalFooter" style="padding:1.25rem 2rem;border-top:1px solid var(--border-color);display:flex;justify-content:flex-end;gap:.75rem;flex-shrink:0;"></div>
  </div>
</div>

<script>
let currentReqId = null;
let currentReqData = null;

async function openRequisitionModal(id) {
    currentReqId = id;
    document.getElementById('reqModal').classList.add('open');
    document.getElementById('modalBody').innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted);">Loading details...</div>';
    document.getElementById('modalFooter').innerHTML = '';
    document.getElementById('modalSubtitle').textContent = 'Loading...';

    const res = await fetch(`{{ url('/admin/requisitions') }}/${id}/show`);
    const data = await res.json();
    currentReqData = data;

    document.getElementById('modalSubtitle').textContent = `Dept: ${data.department} · ${data.created_at}`;

    const isPending = data.status === 'pending';
    const stockRows = data.items.map((item, i) => `
    <tr style="border-bottom:1px solid var(--border-color);">
        <td style="padding:.85rem 1rem;font-size:.85rem;font-weight:700;color:var(--text-main);">${item.description}</td>
        <td style="padding:.85rem 1rem;font-size:.8rem;color:var(--text-muted);">${item.unit}</td>
        <td style="padding:.85rem 1rem;text-align:right;font-size:.85rem;font-weight:800;color:var(--text-main);">${parseFloat(item.quantity_requested).toLocaleString()}</td>
        <td style="padding:.85rem 1rem;text-align:right;">
            <span style="font-size:.85rem;font-weight:800;color:${item.stock_sufficient?'#10b981':'#ef4444'};">${parseFloat(item.current_stock).toLocaleString()}</span>
            ${!item.stock_sufficient?'<div style="font-size:.65rem;color:#ef4444;font-weight:700;">⚠ Low stock</div>':''}
        </td>
        <td style="padding:.85rem 1rem;text-align:right;">
            ${isPending
                ? `<input type="number" class="approved-qty-input" data-item-id="${item.id}" value="${parseFloat(item.quantity_requested)}" min="0" step="0.01" style="width:90px;padding:.4rem .6rem;border:1.5px solid var(--border-color);border-radius:8px;font-weight:800;font-size:.85rem;text-align:right;background:var(--bg-main);color:var(--text-main);">`
                : `<span style="font-size:.85rem;font-weight:800;color:#10b981;">${item.quantity_approved!==null?parseFloat(item.quantity_approved).toLocaleString():'—'}</span>`
            }
        </td>
    </tr>`).join('');

    document.getElementById('modalBody').innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
        <div style="background:var(--bg-main);border-radius:12px;padding:1rem;">
            <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">Requester</div>
            <div style="font-size:.95rem;font-weight:800;color:var(--text-main);">${data.requester_name}</div>
            ${data.rank_or_title?`<div style="font-size:.75rem;color:var(--text-muted);">${data.rank_or_title}</div>`:''}
        </div>
        <div style="background:var(--bg-main);border-radius:12px;padding:1rem;">
            <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">Department</div>
            <div style="font-size:.95rem;font-weight:800;color:var(--text-main);">${data.department}</div>
        </div>
        <div style="background:var(--bg-main);border-radius:12px;padding:1rem;grid-column:1/-1;">
            <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:6px;">Purpose</div>
            <div style="font-size:.88rem;color:var(--text-main);line-height:1.6;">${data.purpose}</div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
        <span style="padding:4px 12px;border-radius:99px;font-size:.7rem;font-weight:800;background:${data.priority_badge.bg};color:${data.priority_badge.color};">${data.priority_badge.label} PRIORITY</span>
        <span style="padding:4px 12px;border-radius:99px;font-size:.7rem;font-weight:800;background:${data.status_badge.bg};color:${data.status_badge.color};">● ${data.status_badge.label}</span>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;overflow:hidden;margin-bottom:1.5rem;">
        <table style="width:100%;border-collapse:collapse;">
            <thead style="background:var(--bg-main);">
                <tr>
                    <th style="padding:.85rem 1rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;">Item</th>
                    <th style="padding:.85rem 1rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;">Unit</th>
                    <th style="padding:.85rem 1rem;text-align:right;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;">Requested</th>
                    <th style="padding:.85rem 1rem;text-align:right;font-size:.7rem;font-weight:800;color:#4f46e5;text-transform:uppercase;">Current Stock</th>
                    <th style="padding:.85rem 1rem;text-align:right;font-size:.7rem;font-weight:800;color:#10b981;text-transform:uppercase;">Qty to Approve</th>
                </tr>
            </thead>
            <tbody>${stockRows}</tbody>
        </table>
    </div>
    ${isPending ? `
    <div>
        <label style="display:block;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Admin / Store Notes (optional)</label>
        <textarea id="adminNotes" rows="2" placeholder="Add any notes for the requester..." style="width:100%;padding:.85rem 1rem;border:1.5px solid var(--border-color);border-radius:12px;font-family:inherit;font-size:.88rem;background:var(--bg-main);color:var(--text-main);resize:vertical;box-sizing:border-box;">${data.admin_notes||''}</textarea>
    </div>` : `
    ${data.admin_notes?`<div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:12px;padding:1rem;"><b style="font-size:.75rem;color:#92400e;">Store Notes:</b><p style="margin:.25rem 0 0;font-size:.88rem;color:var(--text-main);">${data.admin_notes}</p></div>`:''}
    ${data.processor?`<p style="font-size:.78rem;color:var(--text-muted);margin-top:.75rem;">Processed by <b>${data.processor}</b> on ${data.processed_at}</p>`:''}`}`;

    if (isPending) {
        document.getElementById('modalFooter').innerHTML = `
        <button onclick="processRequisition('declined')" style="background:rgba(239,68,68,.1);color:#ef4444;border:1.5px solid rgba(239,68,68,.2);padding:.75rem 1.5rem;border-radius:12px;font-weight:800;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:.88rem;">
            <i data-lucide="x-circle" style="width:16px;"></i> Decline
        </button>
        <button onclick="processRequisition('partially_approved')" style="background:rgba(245,158,11,.1);color:#d97706;border:1.5px solid rgba(245,158,11,.2);padding:.75rem 1.5rem;border-radius:12px;font-weight:800;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:.88rem;">
            <i data-lucide="git-merge" style="width:16px;"></i> Partial Approve
        </button>
        <button onclick="processRequisition('approved')" style="background:#10b981;color:white;border:none;padding:.75rem 1.5rem;border-radius:12px;font-weight:800;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:.88rem;">
            <i data-lucide="check-circle" style="width:16px;"></i> Approve All
        </button>`;
    }
    lucide.createIcons();
}

async function processRequisition(status) {
    const items = [];
    document.querySelectorAll('.approved-qty-input').forEach(inp => {
        items.push({ id: parseInt(inp.dataset.itemId), quantity_approved: parseFloat(inp.value) || 0 });
    });
    const notes = document.getElementById('adminNotes')?.value || '';

    const btns = document.querySelectorAll('#modalFooter button');
    btns.forEach(b => b.disabled = true);

    const res = await fetch(`{{ url('/admin/requisitions') }}/${currentReqId}/process`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ status, admin_notes: notes, items })
    });
    const data = await res.json();
    if (data.success) {
        showToast('Success', data.message, 'success');
        closeModal();
        setTimeout(() => location.reload(), 1200);
    } else {
        showToast('Error', data.message || 'Failed to process.', 'error');
        btns.forEach(b => b.disabled = false);
    }
}

function closeModal() { document.getElementById('reqModal').classList.remove('open'); }
</script>
@endsection
