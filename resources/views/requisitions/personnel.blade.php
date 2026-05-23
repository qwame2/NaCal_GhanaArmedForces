@extends('layouts.dashboard')
@section('content')
<style>
    .req-stat-card {
        background: var(--bg-card);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .req-table-row {
        border-bottom: 1px solid var(--border-color);
        transition: .15s;
    }

    .req-table-row:hover {
        background: rgba(99, 102, 241, .03);
    }

    .req-table-row:last-child {
        border-bottom: none;
    }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 99px;
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(6px);
        z-index: 3000;
        display: none;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .modal-overlay.open {
        display: flex;
    }

    .modal-box {
        background: var(--bg-card);
        border-radius: 24px;
        width: 100%;
        max-width: 920px;
        max-height: 94vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 30px 80px rgba(15, 23, 42, 0.22);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 2.25rem;
        scroll-behavior: smooth;
    }

    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: transparent;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 99px;
    }

    .modal-body:hover::-webkit-scrollbar-thumb {
        background: var(--text-muted);
        opacity: 0.6;
    }

    @keyframes fadeInModal {
        from {
            opacity: 0;
            transform: scale(.96) translateY(10px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .modal-box {
        animation: fadeInModal .35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Priority-specific visual accents */
    .modal-box.urgent-priority {
        border-top: 6px solid #dc2626;
    }

    .modal-box.normal-priority {
        border-top: 6px solid #4f46e5;
    }

    .modal-box.low-priority {
        border-top: 6px solid #64748b;
    }

    /* Horizontal Stepper Timeline */
    .stepper-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        margin-bottom: 2rem;
        background: var(--bg-main);
        padding: 1.25rem 2rem;
        border-radius: 16px;
        border: 1px solid var(--border-color);
    }

    .stepper-line {
        position: absolute;
        top: 50%;
        left: 4rem;
        right: 4rem;
        height: 3px;
        background: var(--border-color);
        z-index: 1;
        transform: translateY(-50%);
    }

    .stepper-progress {
        position: absolute;
        top: 50%;
        left: 4rem;
        height: 3px;
        background: linear-gradient(90deg, var(--primary) 0%, #10b981 100%);
        z-index: 1;
        transform: translateY(-50%);
        transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        width: 33%;
    }

    .stepper-step {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: transform 0.25s ease;
    }

    .stepper-step:hover {
        transform: translateY(-2px);
    }

    .stepper-bubble {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--bg-card);
        border: 3px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.85rem;
        color: var(--text-muted);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    }

    .stepper-label {
        font-size: 0.72rem;
        font-weight: 900;
        color: var(--text-muted);
        margin-top: 8px;
        transition: color 0.3s;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .stepper-step.completed .stepper-bubble {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-color: #10b981;
        color: white;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
    }

    .stepper-step.completed .stepper-label {
        color: #10b981;
    }

    @keyframes activePulse {
        0% {
            box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4);
        }

        70% {
            box-shadow: 0 0 0 8px rgba(79, 70, 229, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(79, 70, 229, 0);
        }
    }

    .stepper-step.active .stepper-bubble {
        background: linear-gradient(135deg, var(--primary) 0%, #4338ca 100%);
        border-color: var(--primary);
        color: white;
        animation: activePulse 2s infinite;
    }

    .stepper-step.active .stepper-label {
        color: var(--primary);
    }

    .stepper-step.declined-step .stepper-bubble {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border-color: #ef4444;
        color: white;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);
    }

    .stepper-step.declined-step .stepper-label {
        color: #ef4444;
    }

    /* Profile Panel & Grid */
    .profile-card {
        display: flex;
        align-items: center;
        gap: 14px;
        background: var(--bg-main);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.15rem;
        transition: all 0.25s ease;
    }

    .profile-card:hover {
        border-color: rgba(79, 70, 229, 0.25);
        background: rgba(99, 102, 241, 0.02);
        transform: translateY(-1px);
    }

    .profile-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: var(--primary-glow);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.25rem;
        border: 1.5px solid rgba(79, 70, 229, 0.15);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    }

    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.72rem;
        font-weight: 800;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
    }

    .purpose-quote {
        background: var(--bg-main);
        border-left: 4px solid var(--primary);
        border-radius: 4px 16px 16px 4px;
        padding: 1.25rem 1.5rem;
        font-size: 0.88rem;
        color: var(--text-main);
        line-height: 1.6;
        font-style: italic;
        position: relative;
    }

    .purpose-quote:before {
        content: '“';
        font-size: 3.5rem;
        color: rgba(79, 70, 229, 0.08);
        position: absolute;
        top: -0.8rem;
        left: 0.5rem;
        font-family: Georgia, serif;
    }

    /* Item decision row/card */
    .item-decision-card {
        border-bottom: 1px solid var(--border-color);
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        transition: all 0.2s ease;
        background: var(--bg-card);
    }

    .item-decision-card:last-child {
        border-bottom: none;
    }

    .item-decision-card.declined-row {
        background: rgba(239, 68, 68, 0.015);
    }

    .item-decision-card.approved-row {
        background: rgba(16, 185, 129, 0.008);
    }

    .item-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        width: 100%;
    }

    .item-card-header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
        min-width: 260px;
    }

    .item-card-header-right {
        text-align: right;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .item-card-panel {
        background: var(--bg-main);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
        border: 1px solid var(--border-color);
        width: 100%;
        box-sizing: border-box;
    }

    /* Visual Progress Fulfill Bar */
    .fulfill-progress-container {
        width: 100%;
        background: var(--border-color);
        height: 6px;
        border-radius: 99px;
        overflow: hidden;
        margin-top: 6px;
    }

    .fulfill-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%);
        border-radius: 99px;
        transition: width 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .fulfill-ratio-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 6px;
        border-radius: 6px;
        font-size: 0.68rem;
        font-weight: 800;
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .fulfill-ratio-badge.reduced {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .fulfill-ratio-badge.declined {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 4px;
    }
</style>

<div style="padding:2rem;">

    {{-- Header --}}
    <div style="margin-bottom:2rem;">
        <div style="font-size:.7rem;font-weight:800;color:#4f46e5;text-transform:uppercase;letter-spacing:.12em;margin-bottom:4px;">Store Operations</div>
        <h1 style="font-size:1.75rem;font-weight:900;color:var(--text-main);letter-spacing:-.03em;margin:0;">Store Requisitions Management</h1>
        <p style="font-size:.9rem;color:var(--text-muted);margin:6px 0 0;">Track, review, and confirm physical collection of department items</p>
    </div>

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:2rem;">
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(99,102,241,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="clock" style="width:20px;color:#6366f1;"></i></div>
            <div>
                <div style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['pending'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Pending</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(220,38,38,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="alert-triangle" style="width:20px;color:#dc2626;"></i></div>
            <div>
                <div style="font-size:1.5rem;font-weight:900;color:#dc2626;">{{ $stats['urgent'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Urgent</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(16,185,129,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="check-circle" style="width:20px;color:#10b981;"></i></div>
            <div>
                <div style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['approved'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Approved</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(245,158,11,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="git-merge" style="width:20px;color:#f59e0b;"></i></div>
            <div>
                <div style="font-size:1.5rem;font-weight:900;color:var(--text-main);">{{ $stats['partially_approved'] }}</div>
                <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);">Partial</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div style="width:44px;height:44px;background:rgba(239,68,68,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="x-circle" style="width:20px;color:#ef4444;"></i></div>
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
        <a href="{{ route('personnel.requisitions') }}" style="padding:.6rem 1rem;border:1.5px solid var(--border-color);border-radius:10px;background:var(--bg-card);color:var(--text-muted);font-weight:700;font-size:.85rem;text-decoration:none;display:flex;align-items:center;gap:6px;">
            <i data-lucide="x" style="width:14px;"></i> Clear
        </a>
        @endif
    </form>

    {{-- Table --}}
    <div style="background:var(--bg-card);border-radius:20px;border:1px solid var(--border-color);overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead style="background:var(--bg-main);">
                <tr>
                    <th style="padding:1rem 1.5rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Department / Requester</th>
                    <th style="padding:1rem 1.5rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Items</th>
                    <th style="padding:1rem 1.5rem;text-align:center;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Priority</th>
                    <th style="padding:1rem 1.5rem;text-align:center;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Status</th>
                    <th style="padding:1rem 1.5rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Date</th>
                    <th style="padding:1rem 1.5rem;text-align:center;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Collection Action</th>
                    <th style="padding:1rem 1.5rem;text-align:center;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Review</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requisitions as $req)
                @php $sb = $req->status_badge; $pb = $req->priority_badge; @endphp
                <tr class="req-table-row">
                    <td style="padding:1rem 1.5rem;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="font-size:.9rem;font-weight:800;color:var(--text-main);">{{ $req->department }}</div>
                            @php $utb = $req->usage_type_badge; @endphp
                            <span class="pill" style="background:{{ $utb['bg'] }}; color:{{ $utb['color'] }}; font-size: 0.6rem; padding: 2px 6px; border-radius: 6px; font-weight:800; text-transform:none; letter-spacing:0;">{{ $utb['label'] }}</span>
                        </div>
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
                    <td style="padding:1rem 1.5rem;text-align:center;"><span class="pill" style="background:{{ $pb['bg'] }};color:{{ $pb['color'] }};">{{ $pb['label'] }}</span></td>
                    <td style="padding:1rem 1.5rem;text-align:center;"><span class="pill" style="background:{{ $sb['bg'] }};color:{{ $sb['color'] }};">● {{ $sb['label'] }}</span></td>
                    <td style="padding:1rem 1.5rem;font-size:.78rem;color:var(--text-muted);font-weight:600;">{{ $req->created_at->format('d/m/y') }}<br>{{ $req->created_at->format('H:i') }}</td>
                    <td style="padding:1rem 1.5rem;text-align:center;">
                        @if(in_array($req->status, ['approved', 'partially_approved']))
                        @if($req->collected_at)
                        <span style="font-size:.78rem;color:#10b981;font-weight:800;display:inline-flex;align-items:center;gap:4px;">
                            <i data-lucide="check-circle" style="width:14px;"></i> Collected
                        </span>
                        @else
                        <button onclick="confirmCollection({{ $req->id }}, this)"
                            style="background:rgba(16,185,129,.1);color:#10b981;border:none;padding:.5rem 1rem;border-radius:10px;font-weight:800;font-size:.78rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:.15s;" onmouseover="this.style.background='#10b981';this.style.color='white'" onmouseout="this.style.background='rgba(16,185,129,.1)';this.style.color='#10b981'">
                            <i data-lucide="package-check" style="width:14px;"></i> Confirm Collection
                        </button>
                        @endif
                        @elseif($req->status === 'declined')
                        <span style="font-size:.75rem;color:#ef4444;font-weight:800;display:inline-flex;align-items:center;gap:4px;">
                            <i data-lucide="x-circle" style="width:13px;"></i> Declined
                        </span>
                        @else
                        <span style="font-size:.75rem;color:var(--text-muted);font-style:italic;">Awaiting Approval</span>
                        @endif
                    </td>
                    <td style="padding:1rem 1.5rem;text-align:center;">
                        <button onclick="openRequisitionModal({{ $req->id }})"
                            style="background:rgba(99,102,241,.1);color:#4f46e5;border:none;padding:.5rem 1rem;border-radius:10px;font-weight:800;font-size:.78rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:.15s;" onmouseover="this.style.background='#4f46e5';this.style.color='white'" onmouseout="this.style.background='rgba(99,102,241,.1)';this.style.color='#4f46e5'">
                            <i data-lucide="eye" style="width:14px;"></i> View Detail
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding:3rem;text-align:center;color:var(--text-muted);">
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
                    <h2 style="margin:0;font-size:1.1rem;font-weight:900;color:var(--text-main);">Requisition Summary</h2>
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
        document.getElementById('modalBody').innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted);"><div style="width:24px;height:24px;border:2px solid rgba(0,0,0,.1);border-top-color:var(--primary);border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 10px;"></div>Loading details...</div>';
        document.getElementById('modalFooter').innerHTML = '';
        document.getElementById('modalSubtitle').textContent = 'Loading...';

        // Call the Admin Show endpoint (fully compatible for detailing)
        const res = await fetch(`{{ url('/admin/requisitions') }}/${id}/show`);
        const data = await res.json();
        currentReqData = data;

        // Apply priority border accents
        const modalBox = document.querySelector('.modal-box');
        modalBox.className = 'modal-box'; // reset
        modalBox.classList.add(`${data.priority}-priority`);

        document.getElementById('modalSubtitle').textContent = `Requisition Ref: #${data.id}`;

        // Profile Grid
        const avatarLetter = data.requester_name ? data.requester_name.charAt(0).toUpperCase() : 'R';
        const totalItemsCount = data.items.length;
        const totalQtyRequested = data.items.reduce((sum, item) => sum + parseFloat(item.quantity_requested || 0), 0);

        const profileGridHtml = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.75rem;">
        <div class="profile-card">
            <div class="profile-avatar">${avatarLetter}</div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:2px;letter-spacing:0.04em;">Requesting Officer</div>
                <div style="font-size:1.05rem;font-weight:900;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${data.requester_name}">${data.requester_name}</div>
                <div style="font-size:.78rem;color:var(--text-muted);font-weight:600;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <i data-lucide="award" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:3px;"></i>${data.rank_or_title || 'No Rank/Title Specified'}
                </div>
            </div>
        </div>
        <div class="profile-card">
            <div class="profile-avatar" style="background:rgba(16, 185, 129, 0.08); color:#10b981; border-color:rgba(16,185,129,0.15);"><i data-lucide="building" style="width:20px;height:20px;"></i></div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:2px;letter-spacing:0.04em;">Originating Department</div>
                <div style="font-size:1.05rem;font-weight:900;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${data.department}">${data.department}</div>
                <div style="font-size:.78rem;color:var(--text-muted);font-weight:600;margin-top:2px;">
                    <i data-lucide="calendar" style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:3px;"></i>Submitted ${data.created_at}
                </div>
            </div>
        </div>

        <div class="profile-card" style="grid-column: 1 / -1; display:flex; flex-direction:column; align-items:stretch; gap:0.75rem;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:.68rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.04em;">Requisition Intention & Purpose</span>
                <div class="stat-pill-group">
                    <span class="stat-pill" style="background:${data.usage_type_badge.bg}; color:${data.usage_type_badge.color}; border-color:rgba(0,0,0,0.05); font-weight:800;"><i data-lucide="${data.usage_type === 'temporary' ? 'calendar' : 'package-check'}" style="width:12px;"></i> ${data.usage_type_badge.label}</span>
                    <span class="stat-pill"><i data-lucide="layers" style="width:12px;"></i> ${totalItemsCount} ${totalItemsCount === 1 ? 'Item Type' : 'Item Types'}</span>
                    <span class="stat-pill"><i data-lucide="hash" style="width:12px;"></i> Total Qty: ${totalQtyRequested.toLocaleString()}</span>
                </div>
            </div>
            <div class="purpose-quote">
                ${data.purpose}
            </div>
        </div>
    </div>
    `;

        // Item rows in read-only format
        const rows = data.items.map(item => {
            const requested = parseFloat(item.quantity_requested) || 0;
            const approved = item.quantity_approved !== null ? parseFloat(item.quantity_approved) : 0;
            const altApproved = item.alternative_quantity_approved !== null ? parseFloat(item.alternative_quantity_approved) : 0;
            const totalApproved = approved + altApproved;
            const pct = requested > 0 ? Math.min(Math.round((totalApproved / requested) * 100), 100) : 0;

            let fulfillBadgeClass = 'fulfill-ratio-badge';
            let fulfillLabel = `${pct}% Fulfill`;
            if (totalApproved === 0) {
                fulfillBadgeClass += ' declined';
                fulfillLabel = 'Declined';
            } else if (totalApproved < requested) {
                fulfillBadgeClass += ' reduced';
                fulfillLabel = `${pct}% Reduced`;
            }

            const stockInfo = item.stock_sufficient ?
                `<span style="color:#10b981;font-size:.7rem;font-weight:700;">✔ Sufficient</span>` :
                `<span style="color:#ef4444;font-size:.7rem;font-weight:700;">⚠ Short Stock</span>`;

            return `
        <div class="item-decision-card ${totalApproved === 0 ? 'declined-row' : 'approved-row'}">
            <div class="item-card-header">
                <div class="item-card-header-left">
                    <div>
                        ${item.alternative_description ? `
                            <div style="font-size:.95rem;font-weight:800;color:var(--text-main); display:flex; align-items:center; gap:6px;">
                                <span>${item.description}</span>
                                <span style="font-size:0.75rem; font-weight:800; color:var(--success-color);">(Approved: ${approved.toLocaleString()} ${item.unit})</span>
                            </div>
                            <div style="font-size:.92rem;font-weight:800;color:var(--store-orange); display:flex; align-items:center; gap:6px; margin-top:4px;">
                                <i data-lucide="shuffle" style="width:14px;height:14px;display:inline-block;vertical-align:middle;margin-right:2px;"></i>Alternative: ${item.alternative_description}
                                <span style="font-size:0.75rem; font-weight:800;">(Approved: ${altApproved.toLocaleString()} ${item.unit})</span>
                            </div>
                        ` : `
                            <div style="font-size:.95rem;font-weight:800;color:var(--text-main);">${item.description}</div>
                        `}
                        <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;margin-top:4px;">
                            Unit: ${item.unit} · Stock: ${parseFloat(item.current_stock).toLocaleString()} (${stockInfo})
                        </div>
                    </div>
                </div>
                <div class="item-card-header-right">
                    <span class="${fulfillBadgeClass}">${fulfillLabel}</span>
                </div>
            </div>

            <div class="item-card-panel" style="gap:1.5rem;">
                <div style="flex:1; min-width:80px;">
                    <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;">Requested</div>
                    <div style="font-size:1.1rem;font-weight:800;color:var(--text-main);margin-top:2px;">${requested.toLocaleString()}</div>
                </div>

                <div style="flex:1; min-width:80px;">
                    <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;">Total Approved</div>
                    <div style="font-size:1.15rem;font-weight:900;color:${totalApproved === 0 ? '#ef4444' : '#10b981'};margin-top:2px;">${totalApproved.toLocaleString()}</div>
                </div>

                <div style="flex:2; min-width:180px;">
                    <div style="font-size:.65rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.02em;margin-bottom:6px;">Fulfillment Progress</div>
                    <div class="fulfill-progress-container" style="margin-top:0;">
                        <div class="fulfill-progress-bar" style="width: ${pct}%; background:${approved === 0 ? '#ef4444' : (approved < requested ? '#f59e0b' : 'linear-gradient(90deg, #4f46e5 0%, #10b981 100%)')}"></div>
                    </div>
                </div>
            </div>

            ${item.remarks ? `
            <div style="background:rgba(0,0,0,0.015); border:1.5px dashed var(--border-color); border-radius:10px; padding:0.75rem 1rem; margin-top:0.25rem;">
                <span style="font-size:0.65rem; font-weight:900; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:4px; letter-spacing:0.04em;">Decision Remarks</span>
                <p style="margin:0; font-size:0.8rem; color:var(--text-main); font-style:italic; line-height:1.4;">"${item.remarks}"</p>
            </div>` : ''}
        </div>`;
        }).join('');

        const itemRowsHtml = `
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:16px;overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.01);">
        ${rows}
    </div>`;

        document.getElementById('modalBody').innerHTML = `
    ${profileGridHtml}

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; margin-top:1.5rem;">
        <h3 style="margin:0; font-size:0.95rem; font-weight:900; color:var(--text-main); display:flex; align-items:center; gap:6px;">
            <i data-lucide="list-checks" style="width:16px; color:var(--primary);"></i> Requested Items Summary
        </h3>
    </div>

    ${itemRowsHtml}

    ${data.admin_notes ? `
        <div style="background:rgba(79,70,229,.03);border:1px solid rgba(79,70,229,.15);border-radius:16px;padding:1.25rem; margin-top: 1.25rem;">
            <div style="font-size:.68rem;font-weight:900;color:var(--primary);text-transform:uppercase;letter-spacing:0.05em;display:flex;align-items:center;gap:4px;margin-bottom:4px;"><i data-lucide="message-square" style="width:14px;"></i> Store Officer Notes</div>
            <p style="margin:0;font-size:.9rem;color:var(--text-main);line-height:1.6;font-style:italic;">"${data.admin_notes}"</p>
        </div>
    ` : ''}

    ${data.status === 'declined' && data.decline_reason ? `
        <div style="background:rgba(239,68,68,0.04); border:1px solid rgba(239,68,68,0.2); border-radius:16px; padding:1.25rem; margin-top:0.75rem;">
            <div style="font-size:.68rem;font-weight:900;color:#dc2626;text-transform:uppercase;letter-spacing:0.05em;display:flex;align-items:center;gap:4px;margin-bottom:6px;">
                <i data-lucide="alert-circle" style="width:14px;"></i> Reason for Decline
            </div>
            <p style="margin:0;font-size:.9rem;color:#7f1d1d;line-height:1.6;">${data.decline_reason}</p>
        </div>
    ` : ''}

    <div style="background:var(--bg-main); border:1px solid var(--border-color); border-radius:16px; padding:1.15rem; margin-top:1.25rem; display:flex; justify-content:space-between; align-items:center;">
        <div style="display:flex; align-items:center; gap:8px;">
            <div style="width:34px; height:34px; background:rgba(79,70,229,0.08); color:var(--primary); border-radius:10px; display:flex; align-items:center; justify-content:center;">
                <i data-lucide="user-check" style="width:16px;"></i>
            </div>
            <div>
                <div style="font-size:.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em;">Processor Authority</div>
                <div style="font-size:.85rem; font-weight:900; color:var(--text-main);">${data.processor ? data.processor : 'Automated System Authority'}</div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:.68rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em;">Processing Timestamp</div>
            <div style="font-size:.85rem; font-weight:900; color:var(--text-main);">${data.processed_at ? data.processed_at : 'Pending'}</div>
        </div>
    </div>`;

        let footerHtml = `
    <button onclick="closeModal()" style="background:var(--bg-main); color:var(--text-main); border:1.5px solid var(--border-color); padding:.75rem 1.5rem; border-radius:12px; font-weight:800; cursor:pointer; font-size:.88rem; transition:0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.03)'" onmouseout="this.style.background='var(--bg-main)'">
        Close Window
    </button>`;

        // If approved or partially approved but not collected, render "Confirm Collection" button in modal too
        if (['approved', 'partially_approved'].includes(data.status)) {
            // Need to check if collected from outer element or data
            const row = document.querySelector(`tr[class*="req-table-row"] button[onclick="openRequisitionModal(${id})"]`).closest('tr');
            const isAlreadyCollected = row.innerHTML.includes('Collected');

            if (!isAlreadyCollected) {
                footerHtml += `
            <button onclick="confirmCollection(${id}, this)"
                style="background:#10b981;color:white;border:none;padding:.75rem 2.25rem;border-radius:12px;font-weight:800;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:.88rem;box-shadow:0 8px 20px rgba(16, 185, 129, 0.25);transition:0.2s;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                <i data-lucide="package-check" style="width:16px;"></i> Confirm Collection
            </button>`;
            }
        }

        document.getElementById('modalFooter').innerHTML = footerHtml;
        lucide.createIcons();
    }

    function closeModal() {
        document.getElementById('reqModal').classList.remove('open');
    }

    async function confirmCollection(id, btn) {
        Swal.fire({
            title: 'Confirm Physical Collection',
            text: 'Are you sure you want to confirm the physical collection of items for store requisition #' + id + '?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Confirm Collection',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#94a3b8'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const originalHTML = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<div style="width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;display:inline-block;vertical-align:middle;margin-right:6px;"></div> Processing...';

                try {
                    const response = await fetch(`/requisitions/${id}/collect`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Collection Confirmed',
                            text: data.message,
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Confirmation Failed',
                            text: data.message,
                            confirmButtonColor: 'var(--primary)'
                        });
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;
                    }
                } catch (error) {
                    /* console print removed */
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'An error occurred while confirming physical collection.',
                        confirmButtonColor: 'var(--primary)'
                    });
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }
            }
        });
    }
</script>
@endsection