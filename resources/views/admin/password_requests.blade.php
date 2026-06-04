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
        <div class="rt-flash-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
        </div>
        <div class="rt-flash-body">
            <span class="rt-flash-title">Success</span>
            <p class="rt-flash-msg">{{ session('success') }}</p>
        </div>
        <button onclick="document.getElementById('rt-flash').remove()" class="rt-flash-close">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
    </div>
    @endif

    {{-- Metrics --}}
    <div class="rt-metrics">
        <div class="rt-metric">
            <div class="rt-metric-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="rt-metric-body">
                <span class="rt-metric-label">Awaiting Review</span>
                <span class="rt-metric-val">{{ $pending }}</span>
            </div>
            <div class="rt-metric-glow"></div>
        </div>
        <div class="rt-metric">
            <div class="rt-metric-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
            </div>
            <div class="rt-metric-body">
                <span class="rt-metric-label">Awaiting Completion</span>
                <span class="rt-metric-val">{{ $approved }}</span>
            </div>
            <div class="rt-metric-glow"></div>
        </div>
        <div class="rt-metric">
            <div class="rt-metric-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
            </div>
            <div class="rt-metric-body">
                <span class="rt-metric-label">Access Restored</span>
                <span class="rt-metric-val">{{ $completed }}</span>
            </div>
            <div class="rt-metric-glow"></div>
        </div>
        <div class="rt-metric">
            <div class="rt-metric-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            </div>
            <div class="rt-metric-body">
                <span class="rt-metric-label">Total Requests</span>
                <span class="rt-metric-val">{{ $requests->count() }}</span>
            </div>
            <div class="rt-metric-glow"></div>
        </div>
    </div>

    {{-- Main Panel --}}
    <div class="rt-panel">

        {{-- Toolbar --}}
        <div class="rt-toolbar">
            <div class="rt-toolbar-brand">
                <div class="rt-brand-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/></svg>
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
                                    <span class="rt-avatar-ring {{ $req->status === 'pending' ? 'ring-active' : 'ring-inactive' }}"></span>
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
                                <div class="rt-status active">
                                    <div class="rt-status-dot active-dot"></div>
                                    <span>Pending</span>
                                </div>
                            @elseif($req->status === 'approved')
                                <div class="rt-status active">
                                    <div class="rt-status-dot active-dot"></div>
                                    <span>Code Issued</span>
                                </div>
                            @elseif($req->status === 'completed')
                                <div class="rt-status completed">
                                    <div class="rt-status-dot completed-dot"></div>
                                    <span>Restored</span>
                                </div>
                            @else
                                <div class="rt-status completed">
                                    <div class="rt-status-dot completed-dot"></div>
                                    <span>{{ ucfirst($req->status) }}</span>
                                </div>
                            @endif
                        </td>

                        {{-- Date --}}
                        <td>
                            <div class="rt-date">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
                                <span>{{ $req->created_at->format('d/m/y') }}</span>
                            </div>
                            <div class="rt-date" style="margin-top:3px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <span style="color:#94a3b8;">{{ $req->created_at->format('H:i') }}</span>
                            </div>
                        </td>

                        {{-- OTP --}}
                        <td>
                            @if($req->otp)
                                <div class="rt-otp-wrap">
                                    <div class="rt-otp-code" id="otp-{{ $req->id }}" style="{{ $req->expires_at && $req->expires_at->isPast() && $req->status === 'approved' ? 'text-decoration:line-through; opacity:0.5;' : '' }}">{{ $req->otp }}</div>
                                    <button class="rt-copy-btn" onclick="copyOtp('{{ $req->otp }}', this)" title="Copy OTP">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                    </button>
                                </div>
                                @if($req->status === 'approved' && $req->expires_at)
                                <div class="rt-countdown" data-expires="{{ $req->expires_at->toIso8601String() }}" data-id="{{ $req->id }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-right:3px;"><line x1="10" y1="2" x2="14" y2="2"/><line x1="12" y1="14" x2="15" y2="11"/><circle cx="12" cy="14" r="8"/></svg>
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
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        Approve &amp; Issue OTP
                                    </button>
                                </form>
                                <form action="{{ route('admin.password.requests.reject', $req->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="rt-btn rt-btn-reject">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        Reject
                                    </button>
                                </form>
                            </div>
                            @else
                                <span class="rt-done-tag" id="status-tag-{{ $req->id }}">
                                    @if($req->expires_at && $req->expires_at->isPast() && $req->status === 'approved')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Expired
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;">
                                            @if($req->status === 'completed')
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                                            @elseif($req->status === 'approved')
                                                <line x1="22" y1="2" x2="11" y2="13"/><polyline points="22 2 15 22 11 13 2 9 22 2"/>
                                            @else
                                                <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                            @endif
                                        </svg>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
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
    background: rgba(99, 102, 241, 0.05);
    border: 1px solid rgba(99, 102, 241, 0.15);
}
.rt-flash-icon {
    width: 40px; height: 40px; border-radius: 12px;
    background: var(--primary); color: white; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
}
.rt-flash-icon i { width: 18px; }
.rt-flash-body { flex: 1; }
.rt-flash-title { display: block; font-size: 0.7rem; font-weight: 900; color: var(--primary); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px; }
.rt-flash-msg { margin: 0; font-size: 0.85rem; font-weight: 700; color: #312e81; line-height: 1.5; }
.rt-flash-close { background: none; border: none; color: var(--primary); opacity: 0.6; cursor: pointer; padding: 4px; border-radius: 8px; display: flex; transition: 0.2s; }
.rt-flash-close:hover { background: rgba(99, 102, 241, 0.1); opacity: 1; color: var(--primary); }

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
    background: rgba(99, 102, 241, 0.08); color: var(--primary);
}
.rt-metric-icon i { width: 22px; height: 22px; }
.rt-metric-label { display: block; font-size: 0.68rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px; color: #94a3b8; }
.rt-metric-val { display: block; font-size: 2.2rem; font-weight: 950; letter-spacing: -0.05em; line-height: 1; color: #0f172a; }
.rt-metric-glow { position: absolute; width: 120px; height: 120px; border-radius: 50%; right: -30px; top: -30px; opacity: 0.06; background: var(--primary); }

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
    background: linear-gradient(135deg, var(--primary) 0%, #4338ca 100%);
    color: white; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.35); flex-shrink: 0;
}
.rt-brand-icon i { width: 24px; height: 24px; }
.rt-brand-heading { display: flex; align-items: center; gap: 10px; margin-bottom: 4px; }
.rt-brand-heading h3 { margin: 0; font-size: 1.4rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; }
.rt-pending-badge {
    font-size: 0.6rem; font-weight: 900; letter-spacing: 0.12em;
    background: rgba(99, 102, 241, 0.1); color: var(--primary);
    border: 1px solid rgba(99, 102, 241, 0.2); padding: 3px 10px; border-radius: 999px;
    animation: pulse-badge 2s infinite;
}
@keyframes pulse-badge {
    0%, 100% { box-shadow: 0 0 0 0 rgba(99,102,241,0.3); }
    50% { box-shadow: 0 0 0 6px rgba(99,102,241,0); }
}
.rt-clear-badge {
    font-size: 0.6rem; font-weight: 900; letter-spacing: 0.12em;
    background: #f1f5f9; color: #64748b;
    border: 1px solid #e2e8f0; padding: 3px 10px; border-radius: 999px;
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
    width: 8px; height: 8px; border-radius: 50%; background: var(--primary);
    animation: rt-live 2s infinite;
}
@keyframes rt-live {
    0%, 100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.5); }
    50% { box-shadow: 0 0 0 5px rgba(99, 102, 241, 0); }
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
.rt-row-highlight td { background: rgba(99, 102, 241, 0.02); }
.rt-row-highlight:hover td { background: rgba(99, 102, 241, 0.05); }
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
.ring-active { border-color: var(--primary); animation: spin-ring 3s linear infinite; }
.ring-inactive { border-color: #e2e8f0; }
@keyframes spin-ring {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
.rt-name  { display: block; font-size: 0.88rem; font-weight: 800; color: #0f172a; }
.rt-role  { display: block; font-size: 0.72rem; font-weight: 600; color: #94a3b8; margin-top: 1px; }

/* Callsign */
.rt-callsign {
    font-size: 0.8rem; font-weight: 800; color: var(--primary);
    background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.15);
    padding: 4px 10px; border-radius: 8px; font-family: monospace;
    letter-spacing: 0.02em;
}

/* Status pill */
.rt-status {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 5px 12px; border-radius: 999px; font-size: 0.7rem; font-weight: 800;
}
.rt-status.active { background: rgba(99, 102, 241, 0.08); color: var(--primary); border: 1px solid rgba(99, 102, 241, 0.15); }
.rt-status.completed { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
.rt-status-dot { width: 7px; height: 7px; border-radius: 50%; }
.active-dot { background: var(--primary); animation: rt-live 2s infinite; }
.completed-dot { background: #94a3b8; }

/* Date */
.rt-date { display: flex; align-items: center; gap: 5px; font-size: 0.78rem; font-weight: 700; color: #475569; }

/* OTP */
.rt-otp-wrap { display: flex; align-items: center; gap: 8px; }
.rt-otp-code {
    font-family: 'Courier New', monospace;
    font-size: 1rem; font-weight: 900; letter-spacing: 0.2em;
    background: rgba(99, 102, 241, 0.08);
    color: var(--primary); padding: 6px 14px; border-radius: 10px;
    border: 1px solid rgba(99, 102, 241, 0.15);
}
.rt-copy-btn {
    background: #f1f5f9; border: 1px solid #e2e8f0; color: #64748b;
    padding: 6px 8px; border-radius: 8px; cursor: pointer; display: flex;
    transition: 0.2s;
}
.rt-copy-btn:hover { background: var(--primary); color: white; border-color: var(--primary); }
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
    background: linear-gradient(135deg, var(--primary) 0%, #4338ca 100%);
    color: white; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
}
.rt-btn-approve:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(99, 102, 241, 0.35); }
.rt-btn-reject {
    background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
}
.rt-btn-reject:hover { background: #e2e8f0; color: #0f172a; border-color: #e2e8f0; transform: translateY(-2px); }

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
    color: var(--primary);
    display: flex;
    align-items: center;
}
.rt-countdown.expired { color: #94a3b8; }

/* Empty state */
.rt-empty { padding: 5rem 2rem; text-align: center; }
.rt-empty-icon {
    width: 80px; height: 80px; border-radius: 24px;
    background: rgba(99, 102, 241, 0.08);
    color: var(--primary); margin: 0 auto 1.5rem;
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
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
        btn.style.background = 'var(--primary)';
        btn.style.color = 'white';
        btn.style.borderColor = 'var(--primary)';
        setTimeout(() => {
            btn.innerHTML = orig;
            btn.style.background = '';
            btn.style.color = '';
            btn.style.borderColor = '';
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
                    el.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-right:3px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span>Expired</span>';
                    el.classList.add('expired');
                    
                    const tag = document.getElementById('status-tag-' + id);
                    if(tag) {
                        tag.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Expired';
                        tag.style.color = '#64748b';
                        tag.style.background = '#f1f5f9';
                        tag.style.borderColor = '#e2e8f0';
                    }
                    
                    const otp = document.getElementById('otp-' + id);
                    if(otp) {
                        otp.style.textDecoration = 'line-through';
                        otp.style.opacity = '0.5';
                    }
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
