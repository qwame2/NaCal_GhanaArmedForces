@extends('layouts.dashboard')

@section('content')
<style>
    .pr-page { padding: 1.75rem 2rem; background: #f8fafc; min-height: 100vh; }

    /* Header bar */
    .pr-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;
    }
    .pr-header-left { display: flex; align-items: center; gap: 14px; }
    .pr-icon-wrap {
        width: 48px; height: 48px; border-radius: 14px;
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
        display: flex; align-items: center; justify-content: center; color: #fff;
        box-shadow: 0 6px 18px rgba(37,99,235,0.3);
    }
    .pr-title   { font-size: 1.55rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0; }
    .pr-subtitle{ font-size: 0.76rem; color: #64748b; font-weight: 700; margin-top: 2px; }

    /* Results Table Card */
    .pr-section-card {
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px;
        overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 1.5rem;
    }
    .pr-table-wrap { overflow-x: auto; }
    .pr-table { width: 100%; border-collapse: collapse; text-align: left; }
    .pr-table th {
        padding: 0.85rem 1.25rem; font-size: 0.7rem; font-weight: 800; color: #64748b;
        text-transform: uppercase; letter-spacing: 0.06em; background: #f8fafc; border-bottom: 1px solid #edf2f7;
    }
    .pr-table td {
        padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; font-size: 0.83rem; color: #0f172a;
        vertical-align: middle;
    }
    .pr-table tr:hover { background: #fafbfc; }

    /* Badges */
    .pr-badge {
        font-size: 0.65rem; font-weight: 900; padding: 3px 8px; border-radius: 99px;
        display: inline-block; text-transform: uppercase; letter-spacing: 0.02em;
    }
    .pr-badge-pending  { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .pr-badge-approved { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .pr-badge-completed { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
    .pr-badge-rejected  { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

    /* OTP Code display */
    .pr-otp-box {
        font-family: monospace; font-size: 1.15rem; font-weight: 900; color: #2563eb;
        background: rgba(37, 99, 235, 0.06); padding: 4px 10px; border-radius: 8px;
        border: 1px dashed rgba(37, 99, 235, 0.25); display: inline-flex; align-items: center; gap: 6px;
    }

    /* Buttons */
    .pr-btn {
        display: inline-flex; align-items: center; gap: 6px; padding: 0.5rem 1rem; border-radius: 10px;
        font-weight: 800; font-size: 0.78rem; cursor: pointer; border: none; transition: all 0.2s ease;
    }
    .pr-btn-primary { background: #2563eb; color: #fff; }
    .pr-btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); }
    .pr-btn-secondary { background: #f1f5f9; color: #475569; }
    .pr-btn-secondary:hover { background: #e2e8f0; }
    .pr-btn-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .pr-btn-danger:hover { background: rgba(239, 68, 68, 0.2); }

    /* Feedback message toaster */
    .pr-toast {
        position: fixed; bottom: 20px; right: 20px; padding: 1rem 1.25rem; border-radius: 12px;
        background: #0f172a; color: white; font-weight: 700; font-size: 0.82rem; z-index: 100000;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 8px;
        transform: translateY(100px); opacity: 0; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .pr-toast.active { transform: translateY(0); opacity: 1; }
</style>

<div class="pr-page">

    {{-- PAGE HEADER --}}
    <div class="pr-header">
        <div class="pr-header-left">
            <a href="{{ route('it-hub.dashboard') }}"
               style="width:36px; height:36px; border-radius:10px; background:#fff; border:1px solid #e2e8f0;
                      display:flex; align-items:center; justify-content:center; color:#64748b;
                      text-decoration:none; box-shadow:0 2px 6px rgba(0,0,0,0.06); transition: 0.2s;"
                      onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                <i data-lucide="arrow-left" style="width:17px;"></i>
            </a>
            <div class="pr-icon-wrap">
                <i data-lucide="key-round" style="width:24px;"></i>
            </div>
            <div>
                <h1 class="pr-title">Admin recovery Center</h1>
                <div class="pr-subtitle">Authorization OTPs for Head of Stores &amp; Administration Password Reset</div>
            </div>
        </div>
    </div>

    {{-- RESULTS TABLE CARD --}}
    <div class="pr-section-card">
        <div style="padding: 1.25rem; border-bottom: 1px solid #edf2f7; background: #fafbfc;">
            <div style="font-size: 0.8rem; font-weight: 800; color: #475569; display: flex; align-items: center; gap: 6px;">
                <i data-lucide="shield-check" style="width: 16px; color: #2563eb;"></i>
                Administrative Credentials Recovery Console (Only logs password resets for Head of Stores)
            </div>
        </div>
        <div class="pr-table-wrap">
            <table class="pr-table">
                <thead>
                    <tr>
                        <th>Personnel callsign</th>
                        <th>Current Status</th>
                        <th>Requested Time</th>
                        <th>Recovery OTP Code</th>
                        <th style="text-align: right;">Authorization Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr>
                        <td>
                            <div style="font-weight: 800; color: #0f172a; font-size: 0.88rem;">{{ $req->user ? $req->user->name : $req->username }}</div>
                            <div style="font-size: 0.72rem; color: #64748b; font-family: monospace; font-weight: 600;">@ {{ $req->username }} &bull; {{ $req->user ? $req->user->role : 'Admin' }}</div>
                        </td>
                        <td>
                            <span class="pr-badge pr-badge-{{ $req->status }}">
                                {{ $req->status }}
                            </span>
                        </td>
                        <td style="color: #64748b; font-size: 0.76rem; font-weight: 600;">
                            {{ $req->created_at ? $req->created_at->diffForHumans() : 'Unknown' }}
                        </td>
                        <td>
                            @if($req->status === 'approved' && $req->otp)
                            <div class="pr-otp-box">
                                <i data-lucide="lock" style="width: 14px; color: #2563eb;"></i>
                                <span style="letter-spacing: 1px;">{{ $req->otp }}</span>
                            </div>
                            @elseif($req->status === 'pending')
                            <span style="color: #94a3b8; font-size: 0.76rem; font-weight: 700; font-style: italic;">Awaiting OTP generation</span>
                            @elseif($req->status === 'completed')
                            <span style="color: #10b981; font-size: 0.76rem; font-weight: 800;">✓ Code Used</span>
                            @else
                            <span style="color: #ef4444; font-size: 0.76rem; font-weight: 700; font-style: italic;">Revoked</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                @if($req->status === 'pending')
                                <button type="button" class="pr-btn pr-btn-primary" onclick="approveRequest('{{ $req->id }}')">
                                    <i data-lucide="zap" style="width: 14px;"></i> Generate OTP
                                </button>
                                <button type="button" class="pr-btn pr-btn-danger" onclick="rejectRequest('{{ $req->id }}')">
                                    Decline
                                </button>
                                @elseif($req->status === 'approved')
                                <button type="button" class="pr-btn pr-btn-secondary" onclick="rejectRequest('{{ $req->id }}')" style="color: #ef4444; border: 1px solid rgba(239,68,68,0.25);">
                                    <i data-lucide="shield-off" style="width: 14px;"></i> Revoke OTP
                                </button>
                                @else
                                <span style="color: #cbd5e1; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Archived</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: #94a3b8;">
                            <div style="font-size: 2.2rem; margin-bottom: 0.5rem; opacity: 0.5;">🔑</div>
                            <div style="font-weight: 800; font-size: 0.95rem; color: #475569;">No Recovery Requests</div>
                            <div style="font-size: 0.76rem; color: #94a3b8; margin-top: 2px;">When a Head of Stores submits a password reset, it will appear here.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
        <div style="padding: 1rem; border-top: 1px solid #edf2f7; display: flex; justify-content: flex-end;">
            {{ $requests->links() }}
        </div>
        @endif
    </div>

</div>

{{-- TOASTER NOTIFICATION --}}
<div id="toastNotification" class="pr-toast">
    <i data-lucide="check-circle-2" style="width: 16px; color: #10b981;"></i>
    <span id="toastMessage">Success message</span>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        
        // Poll every 5 seconds to get the latest OTP statuses silently
        setInterval(pollRequests, 5000);
    });

    async function pollRequests() {
        try {
            const res = await fetch('{{ route("it-hub.password-reset") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json();
            if (data.success) {
                updateTableBody(data.requests);
            }
        } catch (error) {
            console.error('Polling failed:', error);
        }
    }

    function updateTableBody(requests) {
        const tbody = document.querySelector('.pr-table tbody');
        if (!tbody) return;

        if (requests.length === 0) {
            tbody.innerHTML = `<tr>
                <td colspan="5" style="text-align: center; padding: 3rem; color: #94a3b8;">
                    <div style="font-size: 2.2rem; margin-bottom: 0.5rem; opacity: 0.5;">🔑</div>
                    <div style="font-weight: 800; font-size: 0.95rem; color: #475569;">No Recovery Requests</div>
                    <div style="font-size: 0.76rem; color: #94a3b8; margin-top: 2px;">When a Head of Stores submits a password reset, it will appear here.</div>
                </td>
            </tr>`;
            return;
        }

        let html = '';
        requests.forEach(req => {
            let badgeClass = 'pr-badge-pending';
            if (req.status === 'approved') badgeClass = 'pr-badge-approved';
            else if (req.status === 'completed') badgeClass = 'pr-badge-completed';
            else if (req.status === 'rejected') badgeClass = 'pr-badge-rejected';

            let otpCol = '';
            if (req.status === 'approved' && req.otp) {
                otpCol = `<div class="pr-otp-box">
                    <i data-lucide="lock" style="width: 14px; color: #2563eb;"></i>
                    <span style="letter-spacing: 1px;">${req.otp}</span>
                </div>`;
            } else if (req.status === 'pending') {
                otpCol = `<span style="color: #94a3b8; font-size: 0.76rem; font-weight: 700; font-style: italic;">Awaiting OTP generation</span>`;
            } else if (req.status === 'completed') {
                otpCol = `<span style="color: #10b981; font-size: 0.76rem; font-weight: 800;">✓ Code Used</span>`;
            } else {
                otpCol = `<span style="color: #ef4444; font-size: 0.76rem; font-weight: 700; font-style: italic;">Revoked</span>`;
            }

            let actionCol = '';
            if (req.status === 'pending') {
                actionCol = `<button type="button" class="pr-btn pr-btn-primary" onclick="approveRequest('${req.id}')">
                    <i data-lucide="zap" style="width: 14px;"></i> Generate OTP
                </button>
                <button type="button" class="pr-btn pr-btn-danger" onclick="rejectRequest('${req.id}')">
                    Decline
                </button>`;
            } else if (req.status === 'approved') {
                actionCol = `<button type="button" class="pr-btn pr-btn-secondary" onclick="rejectRequest('${req.id}')" style="color: #ef4444; border: 1px solid rgba(239,68,68,0.25);">
                    <i data-lucide="shield-off" style="width: 14px;"></i> Revoke OTP
                </button>`;
            } else {
                actionCol = `<span style="color: #cbd5e1; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Archived</span>`;
            }

            html += `<tr>
                <td>
                    <div style="font-weight: 800; color: #0f172a; font-size: 0.88rem;">${req.name}</div>
                    <div style="font-size: 0.72rem; color: #64748b; font-family: monospace; font-weight: 600;">@ ${req.username} &bull; ${req.role}</div>
                </td>
                <td>
                    <span class="pr-badge ${badgeClass}">
                        ${req.status}
                    </span>
                </td>
                <td style="color: #64748b; font-size: 0.76rem; font-weight: 600;">
                    ${req.time_ago}
                </td>
                <td>
                    ${otpCol}
                </td>
                <td style="text-align: right;">
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        ${actionCol}
                    </div>
                </td>
            </tr>`;
        });

        tbody.innerHTML = html;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    async function approveRequest(id) {
        if (!confirm('Authorize this password reset and generate a secure OTP?')) return;

        try {
            const res = await fetch(`{{ url('/it-hub/password-reset/approve') }}/${id}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            
            if (data.success) {
                showToast(data.message);
                pollRequests();
            } else {
                alert(data.message || 'Error occurred while approving request.');
            }
        } catch (error) {
            alert('Request failed. Check network connectivity.');
        }
    }

    async function rejectRequest(id) {
        if (!confirm('Decline / Revoke recovery credentials for this request?')) return;

        try {
            const res = await fetch(`{{ url('/it-hub/password-reset/reject') }}/${id}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            
            if (data.success) {
                showToast(data.message);
                pollRequests();
            } else {
                alert(data.message || 'Error occurred while updating request.');
            }
        } catch (error) {
            alert('Request failed. Check network connectivity.');
        }
    }

    function showToast(message) {
        const toast = document.getElementById('toastNotification');
        document.getElementById('toastMessage').textContent = message;
        toast.classList.add('active');
        setTimeout(() => {
            toast.classList.remove('active');
        }, 3000);
    }
</script>
@endpush
@endsection
