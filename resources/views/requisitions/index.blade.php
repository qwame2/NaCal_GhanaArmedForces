@extends('layouts.dashboard')
@section('content')
<style>
.req-card{background:var(--bg-card);border-radius:20px;border:1px solid var(--border-color);padding:2rem;margin-bottom:1.5rem;}
.req-input{width:100%;padding:.85rem 1rem;border:1.5px solid var(--border-color);border-radius:12px;font-size:.9rem;font-family:inherit;background:var(--bg-main);color:var(--text-main);outline:none;transition:.2s;}
.req-input:focus{border-color:#4f46e5;box-shadow:0 0 0 4px rgba(79,70,229,.08);}
.req-label{display:block;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;}
.item-row{background:var(--bg-main);border:1.5px solid var(--border-color);border-radius:14px;padding:1.25rem;margin-bottom:1rem;position:relative;transition:.2s;}
.item-row:hover{border-color:#4f46e5;box-shadow:0 4px 20px rgba(79,70,229,.06);}
.badge-priority-urgent{background:rgba(220,38,38,.1);color:#dc2626;border:1px solid rgba(220,38,38,.2);}
.badge-priority-normal{background:rgba(79,70,229,.1);color:#4f46e5;border:1px solid rgba(79,70,229,.2);}
.badge-priority-low{background:rgba(100,116,139,.1);color:#64748b;border:1px solid rgba(100,116,139,.2);}
.status-pill{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:99px;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;}
.req-history-row{padding:1.25rem;border-bottom:1px solid var(--border-color);transition:.2s;}
.req-history-row:hover{background:rgba(99,102,241,.03);}
.req-history-row:last-child{border-bottom:none;}
@keyframes slideUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.animate-in{animation:slideUp .3s ease forwards;}
</style>

<div style="padding:2rem;max-width:1200px;margin:0 auto;">

  {{-- Page Header --}}
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
    <div>
      <div style="font-size:.7rem;font-weight:800;color:#4f46e5;text-transform:uppercase;letter-spacing:.12em;margin-bottom:4px;">Store Management</div>
      <h1 style="font-size:1.75rem;font-weight:900;color:var(--text-main);letter-spacing:-.03em;margin:0;">Store Requisition</h1>
      <p style="font-size:.9rem;color:var(--text-muted);margin:6px 0 0;font-weight:500;">Request items from the central store for your department</p>
    </div>
    <button onclick="document.getElementById('historyPanel').scrollIntoView({behavior:'smooth'})"
      style="background:var(--bg-main);border:1.5px solid var(--border-color);color:var(--text-main);padding:.75rem 1.5rem;border-radius:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:8px;">
      <i data-lucide="clock" style="width:16px;"></i> My Requests
    </button>
  </div>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start;">

    {{-- LEFT: Requisition Form --}}
    <div>
      <form id="requisitionForm">
        @csrf

        {{-- Requester Details --}}
        <div class="req-card animate-in">
          <h2 style="font-size:1rem;font-weight:900;color:var(--text-main);margin:0 0 1.5rem;display:flex;align-items:center;gap:8px;">
            <i data-lucide="user" style="width:18px;color:#4f46e5;"></i> Requester Details
          </h2>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
              <label class="req-label">Full Name *</label>
              <input type="text" name="requester_name" id="requesterName" class="req-input" placeholder="e.g. Sgt. John Mensah" value="{{ auth()->user()->name }}" required>
            </div>
            <div>
              <label class="req-label">Rank / Title</label>
              <input type="text" name="rank_or_title" id="rankTitle" class="req-input" placeholder="e.g. Sergeant, Officer...">
            </div>
            <div>
              <label class="req-label">Department / Unit *</label>
              <input type="text" name="department" id="department" class="req-input" placeholder="e.g. Medical Unit, Operations..." value="{{ auth()->user()->department ?? '' }}" required>
            </div>
            <div>
              <label class="req-label">Priority *</label>
              <select name="priority" id="priority" class="req-input">
                <option value="normal">Normal</option>
                <option value="urgent">Urgent</option>
                <option value="low">Low Priority</option>
              </select>
            </div>
            <div style="grid-column:1/-1;">
              <label class="req-label">Purpose / Justification *</label>
              <textarea name="purpose" id="purpose" class="req-input" rows="3" placeholder="Describe why these items are needed..." style="resize:vertical;" required></textarea>
            </div>
          </div>
        </div>

        {{-- Items List --}}
        <div class="req-card animate-in" style="animation-delay:.05s;">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
            <h2 style="font-size:1rem;font-weight:900;color:var(--text-main);margin:0;display:flex;align-items:center;gap:8px;">
              <i data-lucide="list" style="width:18px;color:#4f46e5;"></i> Items Requested <span id="itemCount" style="font-size:.75rem;font-weight:700;color:#4f46e5;background:rgba(79,70,229,.1);padding:2px 10px;border-radius:99px;margin-left:4px;">1</span>
            </h2>
            <div style="position:relative;flex:1;max-width:280px;">
              <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;color:var(--text-muted);"></i>
              <input type="text" id="itemSearch" placeholder="Quick search inventory..." class="req-input" style="padding-left:36px;">
            </div>
          </div>

          <div id="itemsList"></div>

          <button type="button" onclick="addItemRow()" style="width:100%;padding:1rem;border:2px dashed var(--border-color);border-radius:14px;background:transparent;color:var(--text-muted);font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s;margin-top:.5rem;" onmouseover="this.style.borderColor='#4f46e5';this.style.color='#4f46e5'" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-muted)'">
            <i data-lucide="plus-circle" style="width:18px;"></i> Add Another Item
          </button>
        </div>

        {{-- Submit --}}
        <button type="submit" id="submitBtn" style="width:100%;padding:1.1rem;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:white;border:none;border-radius:14px;font-size:1rem;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;box-shadow:0 8px 25px rgba(79,70,229,.3);transition:.2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
          <i data-lucide="send" style="width:20px;"></i> Submit Requisition
        </button>
      </form>
    </div>

    {{-- RIGHT: Sidebar Info --}}
    <div style="position:sticky;top:1rem;">
      {{-- Available Items Preview --}}
      <div class="req-card" style="margin-bottom:1rem;">
        <h3 style="font-size:.85rem;font-weight:900;color:var(--text-main);margin:0 0 1rem;display:flex;align-items:center;gap:8px;">
          <i data-lucide="package" style="width:16px;color:#10b981;"></i> Available Stock ({{ $availableItems->count() }} items)
        </h3>
        <div id="availableList" style="max-height:300px;overflow-y:auto;" class="no-scrollbar">
          @forelse($availableItems->take(20) as $item)
          <div onclick="quickAddItem('{{ addslashes($item->description) }}','{{ $item->ledge_category }}','{{ addslashes($item->unit) }}')" style="display:flex;align-items:center;justify-content:space-between;padding:.6rem .75rem;border-radius:10px;cursor:pointer;transition:.15s;" onmouseover="this.style.background='rgba(79,70,229,.05)'" onmouseout="this.style.background='transparent'">
            <div>
              <div style="font-size:.82rem;font-weight:700;color:var(--text-main);">{{ $item->description }}</div>
              <div style="font-size:.68rem;color:var(--text-muted);font-weight:600;">{{ $ledgeMap[$item->ledge_category] ?? $item->ledge_category }} · {{ $item->unit }}</div>
            </div>
            <div style="font-size:.75rem;font-weight:800;color:#10b981;background:rgba(16,185,129,.1);padding:2px 8px;border-radius:8px;">{{ number_format($item->total_stock, 0) }}</div>
          </div>
          @empty
          <p style="text-align:center;color:var(--text-muted);font-size:.85rem;padding:1rem;">No items in stock</p>
          @endforelse
        </div>
      </div>

      {{-- Quick Tips --}}
      <div style="background:rgba(79,70,229,.05);border:1px solid rgba(79,70,229,.15);border-radius:16px;padding:1.25rem;">
        <h3 style="font-size:.82rem;font-weight:900;color:#4f46e5;margin:0 0 .75rem;display:flex;align-items:center;gap:6px;">
          <i data-lucide="info" style="width:14px;"></i> How It Works
        </h3>
        <ul style="margin:0;padding:0 0 0 1rem;color:var(--text-muted);font-size:.78rem;line-height:1.9;font-weight:600;">
          <li>Fill in your details and select items from the inventory</li>
          <li>Set priority based on urgency</li>
          <li>Submit for store officer review</li>
          <li>You'll be notified once your request is processed</li>
        </ul>
      </div>
    </div>
  </div>

  {{-- History Panel --}}
  <div id="historyPanel" style="margin-top:2.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
      <h2 style="font-size:1.1rem;font-weight:900;color:var(--text-main);margin:0;display:flex;align-items:center;gap:8px;">
        <i data-lucide="clock" style="width:20px;color:#4f46e5;"></i> My Requisition History
      </h2>
      <button onclick="loadMyRequisitions()" style="background:var(--bg-card);border:1px solid var(--border-color);color:var(--text-muted);padding:6px 14px;border-radius:10px;font-weight:700;font-size:.8rem;cursor:pointer;display:flex;align-items:center;gap:6px;">
        <i data-lucide="refresh-cw" style="width:13px;"></i> Refresh
      </button>
    </div>
    <div class="req-card" style="padding:0;overflow:hidden;">
      <div id="historyList"><div style="padding:2rem;text-align:center;color:var(--text-muted);">Loading...</div></div>
    </div>
  </div>
</div>

{{-- Autocomplete dropdown --}}
<div id="itemAutocomplete" style="display:none;position:fixed;background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.12);z-index:9999;max-height:220px;overflow-y:auto;min-width:260px;"></div>

<script>
const availableItems = @json($availableItems);
const ledgeMap = @json($ledgeMap);
let itemIndex = 0;
let activeAutocompleteInput = null;

function addItemRow(description='', category='', unit='', qty='') {
    const list = document.getElementById('itemsList');
    const idx = itemIndex++;
    const catOptions = Object.entries(ledgeMap).map(([k,v]) => `<option value="${k}" ${category===k?'selected':''}>${v}</option>`).join('');
    const html = `
    <div class="item-row animate-in" id="itemRow${idx}">
        <button type="button" onclick="removeItemRow(${idx})" style="position:absolute;top:1rem;right:1rem;background:rgba(239,68,68,.1);border:none;width:28px;height:28px;border-radius:8px;color:#ef4444;cursor:pointer;display:flex;align-items:center;justify-content:center;">
            <i data-lucide="x" style="width:14px;"></i>
        </button>
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
            <div>
                <label class="req-label">Item Description *</label>
                <input type="text" name="items[${idx}][description]" class="req-input item-desc-input" value="${description}" placeholder="Search or type item name..." autocomplete="off" oninput="onItemDescInput(this)" onblur="hideAutocomplete()" required>
            </div>
            <div>
                <label class="req-label">Category</label>
                <select name="items[${idx}][category]" class="req-input item-cat-select">
                    <option value="">-- Any --</option>
                    ${catOptions}
                </select>
            </div>
            <div>
                <label class="req-label">Qty Requested *</label>
                <input type="number" name="items[${idx}][qty]" class="req-input" min="0.01" step="0.01" value="${qty}" placeholder="0" required>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div>
                <label class="req-label">Unit</label>
                <input type="text" name="items[${idx}][unit]" class="req-input item-unit-input" value="${unit}" placeholder="e.g. boxes, reams, pcs...">
            </div>
            <div>
                <label class="req-label">Remarks (optional)</label>
                <input type="text" name="items[${idx}][remarks]" class="req-input" placeholder="Any specific notes...">
            </div>
        </div>
    </div>`;
    list.insertAdjacentHTML('beforeend', html);
    updateItemCount();
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function removeItemRow(idx) {
    const el = document.getElementById('itemRow' + idx);
    if (el) { el.style.opacity='0'; el.style.transform='scale(.97)'; el.style.transition='.2s'; setTimeout(()=>el.remove(),200); }
    setTimeout(updateItemCount, 250);
}

function updateItemCount() {
    document.getElementById('itemCount').textContent = document.querySelectorAll('.item-row').length;
}

function quickAddItem(desc, cat, unit) {
    const rows = document.querySelectorAll('.item-row');
    const lastRow = rows[rows.length - 1];
    if (lastRow) {
        const descInput = lastRow.querySelector('.item-desc-input');
        const catSelect = lastRow.querySelector('.item-cat-select');
        const unitInput = lastRow.querySelector('.item-unit-input');
        if (descInput && !descInput.value) {
            descInput.value = desc;
            if (catSelect) catSelect.value = cat;
            if (unitInput) unitInput.value = unit;
            descInput.focus();
            return;
        }
    }
    addItemRow(desc, cat, unit);
}

function onItemDescInput(input) {
    activeAutocompleteInput = input;
    const q = input.value.toLowerCase();
    const matches = availableItems.filter(i => i.description.toLowerCase().includes(q)).slice(0, 8);
    const ac = document.getElementById('itemAutocomplete');
    if (!q || matches.length === 0) { ac.style.display='none'; return; }
    const rect = input.getBoundingClientRect();
    ac.style.display = 'block';
    ac.style.top  = (rect.bottom + window.scrollY + 4) + 'px';
    ac.style.left = rect.left + 'px';
    ac.style.width = rect.width + 'px';
    ac.innerHTML = matches.map(m => `
        <div onmousedown="selectAutocomplete('${m.description.replace(/'/g,"\\'")}','${m.ledge_category}','${m.unit}')"
             style="padding:.75rem 1rem;cursor:pointer;border-bottom:1px solid var(--border-color);"
             onmouseover="this.style.background='rgba(79,70,229,.05)'" onmouseout="this.style.background='transparent'">
            <div style="font-weight:700;font-size:.85rem;color:var(--text-main);">${m.description}</div>
            <div style="font-size:.7rem;color:var(--text-muted);">${ledgeMap[m.ledge_category]||m.ledge_category} · Stock: ${parseFloat(m.total_stock).toLocaleString()} ${m.unit}</div>
        </div>`).join('');
}

function selectAutocomplete(desc, cat, unit) {
    if (!activeAutocompleteInput) return;
    activeAutocompleteInput.value = desc;
    const row = activeAutocompleteInput.closest('.item-row');
    if (row) {
        const catSel = row.querySelector('.item-cat-select');
        const unitIn = row.querySelector('.item-unit-input');
        if (catSel) catSel.value = cat;
        if (unitIn) unitIn.value = unit;
    }
    hideAutocomplete();
}

function hideAutocomplete() {
    setTimeout(() => { document.getElementById('itemAutocomplete').style.display='none'; }, 150);
}

// Item search filter in sidebar
document.getElementById('itemSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#availableList > div').forEach(el => {
        el.style.display = el.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

// Form submit
document.getElementById('requisitionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    const rows = document.querySelectorAll('.item-row');
    if (!rows.length) { showToast('Error', 'Please add at least one item.', 'error'); return; }

    const items = [];
    let valid = true;
    rows.forEach(row => {
        const desc = row.querySelector('.item-desc-input')?.value.trim();
        const qty  = row.querySelector('input[name*="[qty]"]')?.value;
        if (!desc || !qty) { valid = false; return; }
        items.push({
            description: desc,
            category:    row.querySelector('.item-cat-select')?.value || '',
            unit:        row.querySelector('.item-unit-input')?.value || 'units',
            quantity_requested: parseFloat(qty),
            remarks:     row.querySelector('input[name*="[remarks]"]')?.value || '',
        });
    });

    if (!valid || !items.length) { showToast('Validation Error', 'Fill in all item descriptions and quantities.', 'error'); return; }

    btn.disabled = true;
    btn.innerHTML = '<svg style="width:20px;animation:spin 1s linear infinite" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg> Submitting...';

    const payload = {
        requester_name: document.getElementById('requesterName').value,
        department:     document.getElementById('department').value,
        rank_or_title:  document.getElementById('rankTitle').value,
        purpose:        document.getElementById('purpose').value,
        priority:       document.getElementById('priority').value,
        items,
        _token: '{{ csrf_token() }}'
    };

    try {
        const res = await fetch('{{ route("requisitions.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            showToast('Submitted!', data.message, 'success');
            document.getElementById('itemsList').innerHTML = '';
            document.getElementById('purpose').value = '';
            itemIndex = 0;
            addItemRow();
            loadMyRequisitions();
        } else {
            showToast('Error', data.message || 'Failed to submit.', 'error');
        }
    } catch(err) {
        showToast('Network Error', 'Could not reach the server.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="send" style="width:20px;"></i> Submit Requisition';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
});

// Load history
async function loadMyRequisitions() {
    const list = document.getElementById('historyList');
    list.innerHTML = '<div style="padding:2rem;text-align:center;color:var(--text-muted);">Loading...</div>';
    const res = await fetch('{{ route("requisitions.my") }}');
    const data = await res.json();
    if (!data.length) {
        list.innerHTML = '<div style="padding:2.5rem;text-align:center;color:var(--text-muted);"><i data-lucide="inbox" style="width:32px;opacity:.3;margin-bottom:.75rem;"></i><p>No requisitions submitted yet.</p></div>';
        lucide.createIcons(); return;
    }
    list.innerHTML = data.map(r => `
    <div class="req-history-row">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
            <div>
                <div style="font-size:.85rem;font-weight:800;color:var(--text-main);margin-bottom:4px;">${r.department} — ${r.requester_name}</div>
                <div style="font-size:.78rem;color:var(--text-muted);max-width:500px;">${r.purpose}</div>
                <div style="margin-top:8px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span class="status-pill" style="background:${r.status_badge.bg};color:${r.status_badge.color};">● ${r.status_badge.label}</span>
                    <span class="status-pill" style="background:${r.priority_badge.bg};color:${r.priority_badge.color};">${r.priority_badge.label}</span>
                    <span style="font-size:.7rem;color:var(--text-muted);font-weight:600;">${r.items.length} item(s) · ${r.created_at}</span>
                </div>
                ${r.admin_notes ? `<div style="margin-top:8px;font-size:.78rem;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:8px;padding:6px 10px;color:#92400e;"><b>Store Notes:</b> ${r.admin_notes}</div>` : ''}
            </div>
            <div style="font-size:.7rem;font-weight:700;color:var(--text-muted);">Ref #${r.id}</div>
        </div>
        <div style="margin-top:.85rem;display:flex;flex-wrap:wrap;gap:.5rem;">
            ${r.items.map(i=>`
            <div style="background:var(--bg-main);border:1px solid var(--border-color);border-radius:8px;padding:4px 10px;font-size:.72rem;font-weight:700;color:var(--text-main);">
                ${i.description} — ${parseFloat(i.quantity_requested).toLocaleString()} ${i.unit}
                ${i.quantity_approved!==null ? ` <span style="color:#10b981;">(Approved: ${parseFloat(i.quantity_approved).toLocaleString()})</span>` : ''}
            </div>`).join('')}
        </div>
    </div>`).join('');
    lucide.createIcons();
}

document.addEventListener('DOMContentLoaded', () => {
    addItemRow();
    loadMyRequisitions();
});
</script>
@endsection
