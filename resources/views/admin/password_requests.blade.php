@extends('layouts.admin')

@section('title', 'Password Resets')

@section('content')

@php
    $pending   = $requests->where('status', 'pending')->count();
    $approved  = $requests->where('status', 'approved')->count();
    $completed = $requests->where('status', 'completed')->count();
@endphp

<div class="command-center">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="rt-flash success" id="rt-flash">
        <div class="rt-flash-icon"><i data-lucide="shield-check"></i></div>
        <div class="rt-flash-body">
            <span class="rt-flash-title">Success</span>
            <p class="rt-flash-msg">{{ session('success') }}</p>
        </div>
        <button onclick="document.getElementById('rt-flash').remove()" class="rt-flash-close"><i data-lucide="x"></i></button>
    </div>
    @endif

    {{-- Metrics --}}
    <div class="rt-metrics">
        <div class="rt-metric amber">
            <div class="rt-metric-icon"><i data-lucide="clock"></i></div>
            <div class="rt-metric-body">
                <span class="rt-metric-label">Awaiting Review</span>
                <span class="rt-metric-val">{{ $pending }}</span>
            </div>
            <div class="rt-metric-glow amber-glow"></div>
        </div>
        <div class="rt-metric indigo">
            <div class="rt-metric-icon"><i data-lucide="send-horizontal"></i></div>
            <div class="rt-metric-body">
                <span class="rt-metric-label">Awaiting Completion</span>
                <span class="rt-metric-val">{{ $approved }}</span>
            </div>
            <div class="rt-metric-glow indigo-glow"></div>
        </div>
        <div class="rt-metric green">
            <div class="rt-metric-icon"><i data-lucide="shield-check"></i></div>
            <div class="rt-metric-body">
                <span class="rt-metric-label">Access Restored</span>
                <span class="rt-metric-val">{{ $completed }}</span>
            </div>
            <div class="rt-metric-glow green-glow"></div>
        </div>
        <div class="rt-metric slate">
            <div class="rt-metric-icon"><i data-lucide="list"></i></div>
            <div class="rt-metric-body">
                <span class="rt-metric-label">Total Requests</span>
                <span class="rt-metric-val">{{ $requests->count() }}</span>
            </div>
            <div class="rt-metric-glow slate-glow"></div>
        </div>
    </div>

    {{-- Main Panel --}}
    <div class="rt-panel">

        {{-- Toolbar --}}
        <div class="rt-toolbar">
            <div class="rt-toolbar-brand">
                <div class="rt-brand-icon">
                    <i data-lucide="shield-alert"></i>
                </div>
                <div>
                    <div class="rt-brand-heading">
                        <h3>Password Reset Center</h3>
                        @if($pending > 0)
                        <span class="rt-pending-badge">{{ $pending }} PENDING</span>
                        @else
                        <span class="rt-clear-badge">ALL CLEAR</span>
                        @endif
                    </div>
                    <p class="rt-brand-sub">Manage staff password reset requests with a clear history</p>
                </div>
            </div>

            <div class="rt-toolbar-status">
                <div class="rt-live-dot"></div>
                <span>System Active</span>
            </div>
        </div>

        {{-- Table --}}
        <div class="rt-table-wrap">
            <table class="rt-table">
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Username</th>
                        <th>Status</th>
                        <th>Request Date</th>
                        <th>Security Code</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr class="rt-row {{ $req->status === 'pending' ? 'rt-row-highlight' : '' }}">
                        {{-- Personnel --}}
                        <td>
                            <div class="rt-identity">
                                <div class="rt-avatar">
                                    {{ strtoupper(substr($req->user->name ?? $req->username, 0, 1)) }}
                                    <span class="rt-avatar-ring {{ $req->status === 'pending' ? 'ring-amber' : ($req->status === 'approved' ? 'ring-indigo' : 'ring-green') }}"></span>
                                </div>
                                <div>
                                    <span class="rt-name">{{ $req->user->name ?? 'Unregistered User' }}</span>
                                    <span class="rt-role">{{ $req->user->role ?? 'No role assigned' }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Username --}}
                        <td>
                            <span class="rt-callsign">@ {{ $req->username }}</span>
                        </td>

                        {{-- Status --}}
                        <td>
                            @if($req->status === 'pending')
                                <div class="rt-status amber">
                                    <div class="rt-status-dot amber-dot"></div>
                                    <span>Pending</span>
                                </div>
                            @elseif($req->status === 'approved')
                                <div class="rt-status indigo">
                                    <div class="rt-status-dot indigo-dot"></div>
                                    <span>Code Issued</span>
                                </div>
                            @elseif($req->status === 'completed')
                                <div class="rt-status green">
                                    <div class="rt-status-dot green-dot"></div>
                                    <span>Restored</span>
                                </div>
                            @else
                                <div class="rt-status red">
                                    <div class="rt-status-dot red-dot"></div>
                                    <span>{{ ucfirst($req->status) }}</span>
                                </div>
                            @endif
                        </td>

                        {{-- Date --}}
                        <td>
                            <div class="rt-date">
                                <i data-lucide="calendar" style="width:12px; height:12px; color:#94a3b8;"></i>
                                <span>{{ $req->created_at->format('d/m/y') }}</span>
                            </div>
                            <div class="rt-date" style="margin-top:3px;">
                                <i data-lucide="clock-3" style="width:12px; height:12px; color:#94a3b8;"></i>
                                <span style="color:#94a3b8;">{{ $req->created_at->format('H:i') }}</span>
                            </div>
                        </td>

                        {{-- OTP --}}
                        <td>
                            @if($req->otp)
                                <div class="rt-otp-wrap">
                                    <div class="rt-otp-code" id="otp-{{ $req->id }}" style="{{ $req->expires_at && $req->expires_at->isPast() && $req->status === 'approved' ? 'text-decoration:line-through; opacity:0.5;' : '' }}">{{ $req->otp }}</div>
                                    <button class="rt-copy-btn" onclick="copyOtp('{{ $req->otp }}', this)" title="Copy OTP">
                                        <i data-lucide="copy" style="width:13px;"></i>
                                    </button>
                                </div>
                                @if($req->status === 'approved' && $req->expires_at)
                                <div class="rt-countdown" data-expires="{{ $req->expires_at->toIso8601String() }}" data-id="{{ $req->id }}">
                                    <i data-lucide="timer" style="width:12px; height:12px; margin-right:3px;"></i>
                                    <span class="rt-countdown-text">Calculating...</span>
                                </div>
                                @endif
                            @else
                                <span class="rt-no-otp">Not generated</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td style="text-align:right;">
                            @if($req->status === 'pending')
                            <div style="display:flex; gap:8px; justify-content:flex-end; align-items:center;">
                                <form action="{{ route('admin.password.requests.approve', $req->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="rt-btn rt-btn-approve">
                                        <i data-lucide="check"></i>
                                        Approve &amp; Issue OTP
                                    </button>
                                </form>
                                <form action="{{ route('admin.password.requests.reject', $req->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="rt-btn rt-btn-reject">
                                        <i data-lucide="x"></i>
                                        Reject
                                    </button>
                                </form>
                            </div>
                            @else
                                <span class="rt-done-tag" id="status-tag-{{ $req->id }}">
                                    @if($req->expires_at && $req->expires_at->isPast() && $req->status === 'approved')
                                        <i data-lucide="alert-circle" style="width:12px;"></i> Expired
                                    @else
                                        <i data-lucide="{{ $req->status === 'completed' ? 'check-circle-2' : ($req->status === 'approved' ? 'send' : 'ban') }}" style="width:12px;"></i>
                                        {{ ucfirst($req->status) }}
                                    @endif
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="rt-empty">
                                <div class="rt-empty-icon">
                                    <i data-lucide="shield-check"></i>
                                </div>
                                <h4>All Clear — No Recovery Requests</h4>
                                <p>There are no pending password reset requests. System security is nominal.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="rt-footer">
            <span>{{ $requests->total() }} total request(s) on record</span>
            
            <div class="rt-pagination-wrapper">
                {{ $requests->links('pagination::bootstrap-4') }}
            </div>

            <span>Last checked: {{ now()->format('d/m/y H:i') }}</span>
        </div>
    </div>

</div>

<style>
/* ─── Root ─────────────────────────────── */
.command-center { display: flex; flex-direction: column; gap: 2rem; }

/* ─── Flash ─────────────────────────────── */
.rt-flash {
    display: flex; align-items: flex-start; gap: 14px;
    padding: 16px 20px; border-radius: 18px;
    animation: slideInFlash 0.4s ease;
}
@keyframes slideInFlash {
    from { opacity: 0; transform: translateY(-10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.rt-flash.success {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border: 1px solid #bbf7d0;
}
.rt-flash-icon {
    width: 40px; height: 40px; border-radius: 12px;
    background: #10b981; color: white; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
}
.rt-flash-icon i { width: 18px; }
.rt-flash-body { flex: 1; }
.rt-flash-title { display: block; font-size: 0.7rem; font-weight: 900; color: #059669; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px; }
.rt-flash-msg { margin: 0; font-size: 0.85rem; font-weight: 700; color: #166534; line-height: 1.5; }
.rt-flash-close { background: none; border: none; color: #6ee7b7; cursor: pointer; padding: 4px; border-radius: 8px; display: flex; transition: 0.2s; }
.rt-flash-close:hover { background: rgba(16,185,129,0.15); color: #059669; }

/* ─── Metrics ───────────────────────────── */
.rt-metrics { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
.rt-metric {
    position: relative; overflow: hidden;
    background: white; border-radius: 24px;
    padding: 1.75rem 2rem; border: 1px solid #f1f5f9;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    display: flex; align-items: center; gap: 16px;
    transition: all 0.3s ease;
}
.rt-metric:hover { transform: translateY(-4px); box-shadow: 0 12px 35px rgba(0,0,0,0.07); }
.rt-metric-icon {
    width: 52px; height: 52px; border-radius: 16px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
}
.rt-metric-icon i { width: 22px; height: 22px; }
.rt-metric-label { display: block; font-size: 0.68rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px; color: #94a3b8; }
.rt-metric-val { display: block; font-size: 2.2rem; font-weight: 950; letter-spacing: -0.05em; line-height: 1; }
.rt-metric-glow { position: absolute; width: 120px; height: 120px; border-radius: 50%; right: -30px; top: -30px; opacity: 0.06; }

/* Metric Colors */
.rt-metric.amber .rt-metric-icon { background: #fffbeb; color: #f59e0b; }
.rt-metric.amber .rt-metric-val { color: #d97706; }
.amber-glow { background: #f59e0b; }

.rt-metric.indigo .rt-metric-icon { background: #eef2ff; color: #6366f1; }
.rt-metric.indigo .rt-metric-val { color: #4f46e5; }
.indigo-glow { background: #6366f1; }

.rt-metric.green .rt-metric-icon { background: #f0fdf4; color: #10b981; }
.rt-metric.green .rt-metric-val { color: #059669; }
.green-glow { background: #10b981; }

.rt-metric.slate .rt-metric-icon { background: #f8fafc; color: #64748b; }
.rt-metric.slate .rt-metric-val { color: #0f172a; }
.slate-glow { background: #64748b; }

/* ─── Main Panel ─────────────────────────── */
.rt-panel {
    background: white; border-radius: 32px;
    border: 1px solid rgba(0,0,0,0.04);
    box-shadow: 0 20px 60px rgba(0,0,0,0.04);
    overflow: hidden;
}

/* ─── Toolbar ────────────────────────────── */
.rt-toolbar {
    padding: 2rem 2.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; justify-content: space-between;
    position: relative; overflow: hidden;
}
.rt-toolbar::before {
    content: '';
    position: absolute; inset: 0;
    background-image: linear-gradient(rgba(0,0,0,0.015) 1px, transparent 1px),
                      linear-gradient(90deg, rgba(0,0,0,0.015) 1px, transparent 1px);
    background-size: 28px 28px;
}
.rt-toolbar-brand { display: flex; align-items: center; gap: 18px; position: relative; z-index: 1; }
.rt-brand-icon {
    width: 52px; height: 52px; border-radius: 16px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 24px rgba(245,158,11,0.35); flex-shrink: 0;
}
.rt-brand-icon i { width: 24px; height: 24px; }
.rt-brand-heading { display: flex; align-items: center; gap: 10px; margin-bottom: 4px; }
.rt-brand-heading h3 { margin: 0; font-size: 1.4rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; }
.rt-pending-badge {
    font-size: 0.6rem; font-weight: 900; letter-spacing: 0.12em;
    background: #fffbeb; color: #d97706;
    border: 1px solid #fde68a; padding: 3px 10px; border-radius: 999px;
    animation: pulse-badge 2s infinite;
}
@keyframes pulse-badge {
    0%, 100% { box-shadow: 0 0 0 0 rgba(245,158,11,0.3); }
    50% { box-shadow: 0 0 0 6px rgba(245,158,11,0); }
}
.rt-clear-badge {
    font-size: 0.6rem; font-weight: 900; letter-spacing: 0.12em;
    background: #f0fdf4; color: #15803d;
    border: 1px solid #bbf7d0; padding: 3px 10px; border-radius: 999px;
}
.rt-brand-sub { margin: 0; font-size: 0.78rem; color: #64748b; font-weight: 600; }
.rt-toolbar-status {
    position: relative; z-index: 1;
    display: flex; align-items: center; gap: 8px;
    background: white; border: 1px solid #e2e8f0;
    padding: 8px 16px; border-radius: 999px;
    font-size: 0.65rem; font-weight: 900; color: #94a3b8;
    letter-spacing: 0.1em; text-transform: uppercase;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.rt-live-dot {
    width: 8px; height: 8px; border-radius: 50%; background: #10b981;
    animation: rt-live 2s infinite;
}
@keyframes rt-live {
    0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.5); }
    50% { box-shadow: 0 0 0 5px rgba(16,185,129,0); }
}

/* ─── Table ──────────────────────────────── */
.rt-table-wrap { overflow-x: auto; }
.rt-table { width: 100%; border-collapse: collapse; }
.rt-table thead th {
    background: #fafbff; padding: 12px 20px;
    font-size: 0.62rem; font-weight: 900; color: #94a3b8;
    text-transform: uppercase; letter-spacing: 0.1em;
    border-bottom: 1px solid #f1f5f9; white-space: nowrap;
}
.rt-table thead th:first-child { padding-left: 2.5rem; }
.rt-table thead th:last-child  { padding-right: 2.5rem; }

.rt-row td {
    padding: 18px 20px; border-bottom: 1px solid #f8fafc;
    vertical-align: middle;
    transition: background 0.2s;
}
.rt-row td:first-child { padding-left: 2.5rem; }
.rt-row td:last-child  { padding-right: 2.5rem; }
.rt-row:hover td { background: #fafbff; }
.rt-row-highlight td { background: #fffdf5; }
.rt-row-highlight:hover td { background: #fffbeb; }
.rt-row:last-child td { border-bottom: none; }

/* Identity */
.rt-identity { display: flex; align-items: center; gap: 12px; }
.rt-avatar {
    position: relative;
    width: 42px; height: 42px; border-radius: 14px;
    background: linear-gradient(135deg, #0f172a, #334155);
    color: white; font-size: 1rem; font-weight: 900;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.rt-avatar-ring {
    position: absolute; inset: -3px; border-radius: 17px;
    border: 2px solid transparent;
}
.ring-amber { border-color: #f59e0b; animation: spin-ring 3s linear infinite; }
.ring-indigo { border-color: #6366f1; }
.ring-green  { border-color: #10b981; }
@keyframes spin-ring {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
.rt-name  { display: block; font-size: 0.88rem; font-weight: 800; color: #0f172a; }
.rt-role  { display: block; font-size: 0.72rem; font-weight: 600; color: #94a3b8; margin-top: 1px; }

/* Callsign */
.rt-callsign {
    font-size: 0.8rem; font-weight: 800; color: #4f46e5;
    background: #eef2ff; border: 1px solid #e0e7ff;
    padding: 4px 10px; border-radius: 8px; font-family: monospace;
    letter-spacing: 0.02em;
}

/* Status pill */
.rt-status {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 5px 12px; border-radius: 999px; font-size: 0.7rem; font-weight: 800;
}
.rt-status.amber  { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.rt-status.indigo { background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; }
.rt-status.green  { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.rt-status.red    { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.rt-status-dot { width: 7px; height: 7px; border-radius: 50%; }
.amber-dot  { background: #f59e0b; animation: rt-live 2s infinite; }
.indigo-dot { background: #6366f1; }
.green-dot  { background: #10b981; }
.red-dot    { background: #ef4444; }

/* Date */
.rt-date { display: flex; align-items: center; gap: 5px; font-size: 0.78rem; font-weight: 700; color: #475569; }

/* OTP */
.rt-otp-wrap { display: flex; align-items: center; gap: 8px; }
.rt-otp-code {
    font-family: 'Courier New', monospace;
    font-size: 1rem; font-weight: 900; letter-spacing: 0.2em;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: #fbbf24; padding: 6px 14px; border-radius: 10px;
}
.rt-copy-btn {
    background: #f1f5f9; border: 1px solid #e2e8f0; color: #64748b;
    padding: 6px 8px; border-radius: 8px; cursor: pointer; display: flex;
    transition: 0.2s;
}
.rt-copy-btn:hover { background: #6366f1; color: white; border-color: #6366f1; }
.rt-no-otp { font-size: 0.75rem; font-weight: 600; color: #cbd5e1; font-style: italic; }

/* Action buttons */
.rt-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 16px; border-radius: 12px; font-size: 0.75rem; font-weight: 800;
    cursor: pointer; border: none; transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    white-space: nowrap;
}
.rt-btn i { width: 13px; height: 13px; }
.rt-btn-approve {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white; box-shadow: 0 4px 14px rgba(16,185,129,0.25);
}
.rt-btn-approve:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(16,185,129,0.35); }
.rt-btn-reject {
    background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;
}
.rt-btn-reject:hover { background: #ef4444; color: white; border-color: #ef4444; transform: translateY(-2px); }

.rt-done-tag {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.7rem; font-weight: 800; color: #94a3b8; background: #f8fafc;
    border: 1px solid #e2e8f0; padding: 5px 12px; border-radius: 999px; letter-spacing: 0.04em;
}
.rt-done-tag i { width: 12px; height: 12px; }

.rt-countdown {
    margin-top: 6px;
    font-size: 0.72rem;
    font-weight: 700;
    color: #f59e0b;
    display: flex;
    align-items: center;
}
.rt-countdown.expired { color: #ef4444; }

/* Empty state */
.rt-empty { padding: 5rem 2rem; text-align: center; }
.rt-empty-icon {
    width: 80px; height: 80px; border-radius: 24px;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    color: #10b981; margin: 0 auto 1.5rem;
    display: flex; align-items: center; justify-content: center;
}
.rt-empty-icon i { width: 36px; height: 36px; }
.rt-empty h4 { font-size: 1rem; font-weight: 900; color: #0f172a; margin: 0 0 8px; }
.rt-empty p  { font-size: 0.82rem; color: #94a3b8; font-weight: 600; margin: 0; }

/* Footer */
.rt-footer {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1.25rem 2.5rem; background: #fafbff; border-top: 1px solid #f1f5f9;
    font-size: 0.72rem; font-weight: 700; color: #94a3b8;
}

/* Pagination */
.rt-pagination-wrapper nav { margin: 0; }
.rt-pagination-wrapper ul.pagination {
    display: flex; gap: 4px; padding: 0; margin: 0; list-style: none; align-items: center;
}
.rt-pagination-wrapper .page-item .page-link {
    display: flex; align-items: center; justify-content: center;
    min-width: 32px; height: 32px; padding: 0 8px; border-radius: 8px;
    font-size: 0.75rem; font-weight: 700; color: #64748b; background: white;
    border: 1px solid #e2e8f0; text-decoration: none; transition: 0.2s;
}
.rt-pagination-wrapper .page-item.active .page-link {
    background: #4f46e5; color: white; border-color: #4f46e5;
}
.rt-pagination-wrapper .page-item .page-link:hover:not(.active) {
    background: #f1f5f9;
}
.rt-pagination-wrapper .page-item.disabled .page-link {
    opacity: 0.5; cursor: not-allowed;
}
</style>

<script>
function copyOtp(otp, btn) {
    navigator.clipboard.writeText(otp).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="check" style="width:13px;"></i>';
        btn.style.background = '#10b981';
        btn.style.color = 'white';
        btn.style.borderColor = '#10b981';
        if (typeof lucide !== 'undefined') lucide.createIcons();
        setTimeout(() => {
            btn.innerHTML = orig;
            btn.style.background = '';
            btn.style.color = '';
            btn.style.borderColor = '';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }, 2000);
    });
}

// Real-Time OTP Expiration Countdown Logic
function initCountdowns() {
    const countdowns = document.querySelectorAll('.rt-countdown');
    if(countdowns.length === 0) return;

    setInterval(() => {
        const now = new Date().getTime();
        countdowns.forEach(el => {
            const expires = new Date(el.dataset.expires).getTime();
            const id = el.dataset.id;
            const distance = expires - now;

            if (distance < 0) {
                // Mark as expired in UI
                if (!el.classList.contains('expired')) {
                    el.innerHTML = '<i data-lucide="alert-circle" style="width:12px; height:12px; margin-right:3px;"></i><span>Expired</span>';
                    el.classList.add('expired');
                    
                    const tag = document.getElementById('status-tag-' + id);
                    if(tag) {
                        tag.innerHTML = '<i data-lucide="alert-circle" style="width:12px; height:12px;"></i> Expired';
                        tag.style.color = '#ef4444';
                        tag.style.background = '#fef2f2';
                        tag.style.borderColor = '#fecaca';
                    }
                    
                    const otp = document.getElementById('otp-' + id);
                    if(otp) {
                        otp.style.textDecoration = 'line-through';
                        otp.style.opacity = '0.5';
                    }
                    if(typeof lucide !== 'undefined') lucide.createIcons();
                }
            } else {
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                let text = '';
                if(hours > 0) text += hours + 'h ';
                text += minutes + 'm ' + seconds + 's';
                
                const span = el.querySelector('.rt-countdown-text');
                if(span) span.innerText = text + ' left';
            }
        });
    }, 1000);
}
document.addEventListener("DOMContentLoaded", initCountdowns);
</script>

@endsection
