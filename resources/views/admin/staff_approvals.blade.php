@extends('layouts.dashboard')

@section('content')
<style>
    :root {
        --primary: #6366f1;
        --primary-light: #818cf8;
        --primary-dark: #4f46e5;
        --primary-glow: rgba(99, 102, 241, 0.12);
        --secondary: #10b981;
        --accent: #f59e0b;
        --danger: #ef4444;
        --bg-main: #f3f4f6;
        --bg-sidebar: #ffffff;
        --bg-card: #ffffff;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --border-color: #f1f5f9;
        --radius-xl: 1.5rem;
        --radius-lg: 1rem;
        --shadow-premium: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
    }

    @keyframes adminFadeInUp {
        0% {
            opacity: 0;
            transform: translateY(18px) scale(0.99);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .admin-page-banner {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(15, 23, 42, 0.03) 50%, rgba(16, 185, 129, 0.05) 100%);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-xl);
        padding: 1.75rem 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-premium);
        animation: adminFadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
</style>

<div style="padding: 2rem;">
    {{-- Header Banner --}}
    <div class="admin-page-banner" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 4px;">
                <span style="background: rgba(99, 102, 241, 0.12); color: var(--primary); font-size: 0.7rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em;">
                    Authorizer &amp; Delegators Command
                </span>
            </div>
            <h1 style="font-size:1.85rem; font-weight:950; color:var(--text-main); margin:0; letter-spacing:-0.03em;">
                Staff Access &amp; Approvals
            </h1>
            <p style="font-size:0.88rem; color:var(--text-muted); font-weight:600; margin:4px 0 0;">
                Manage pending and approved department staff and control requisition authority permissions.
            </p>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
            <button onclick="loadProvisioningData()" style="background: var(--bg-card); border: 1.5px solid var(--border-color); color: var(--text-main); padding: 0.65rem 1.25rem; border-radius: 12px; font-weight: 800; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)'">
                <i data-lucide="refresh-cw" style="width:16px; height:16px;"></i> Refresh Directory
            </button>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div style="background:var(--bg-card); border-radius:24px; border:1px solid var(--border-color); padding:2rem; box-shadow:var(--shadow-premium); animation: adminFadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;">
        <div style="font-size:.75rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:1.25rem; display:flex; align-items:center; gap:8px;">
            <i data-lucide="users" style="width:16px; height:16px; color:var(--primary);"></i>
            Pending and Approved Department Staff
        </div>
        <div id="tempAccountsList">
            <div style="text-align:center; padding:2rem; color:var(--text-muted); font-size:.85rem;">
                <i data-lucide="loader" style="width:20px; height:20px; display:inline-block; margin-bottom:8px; opacity:.5; animation: spin 1s linear infinite;"></i><br>Loading department staff directory...
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    window.loadProvisioningData = async function(isSilent = false) {
        const dirContainer = document.getElementById('tempAccountsList');
        if (!dirContainer) return;

        if (!isSilent) {
            dirContainer.innerHTML = `
                <div style="text-align:center; padding:2rem; color:var(--text-muted); font-size:.85rem;">
                    <i data-lucide="loader" style="width:20px; height:20px; display:inline-block; margin-bottom:8px; opacity:.5; animation: spin 1s linear infinite;"></i><br>Loading department staff directory...
                </div>`;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        try {
            const dirRes = await fetch('{{ route("dept-head.provisioning-dashboard") }}');
            if (!dirRes.ok) return;

            const dirData = await dirRes.json();
            if (!dirData.success) return;

            const pendingList = (dirData.pending || []).map(p => ({
                id: p.id,
                name: p.name,
                username: p.username,
                role: 'Requisitioner',
                department: '{{ auth()->user()->department }}',
                registration_status: 'pending',
                is_active: false,
                is_online: false,
                can_make_requisition: false
            }));

            const approvedList = (dirData.accounts || []).map(a => ({
                ...a,
                registration_status: 'approved'
            }));

            const allStaff = [...pendingList, ...approvedList];

            if (allStaff.length === 0) {
                dirContainer.innerHTML = `
                    <div style="text-align:center; padding:2.5rem 1rem; border:1px dashed var(--border-color); border-radius:16px;">
                        <div style="font-size:2rem; margin-bottom:.5rem;">👥</div>
                        <div style="font-size:.9rem; font-weight:800; color:var(--text-main);">No Department Requisitioners Found</div>
                        <div style="font-size:.78rem; color:var(--text-muted); margin-top:.2rem;">Pending or approved requisitioners from your department will appear here.</div>
                    </div>`;
            } else {
                let rows = allStaff.map(acc => {
                    const isPending = (acc.registration_status === 'pending' || acc.registration_status === 'pending_hod');
                    const isAccessActive = acc.can_make_requisition;

                    const regBadge = isPending 
                        ? '<span style="font-size:.65rem; font-weight:800; padding:3px 10px; border-radius:99px; background:rgba(245, 158, 11, 0.12); color:#d97706; border:1px solid rgba(245, 158, 11, 0.3);">⏳ PENDING APPROVAL</span>'
                        : '<span style="font-size:.65rem; font-weight:800; padding:3px 10px; border-radius:99px; background:rgba(5, 150, 105, 0.12); color:#059669; border:1px solid rgba(5, 150, 105, 0.3);">✓ APPROVED</span>';

                    const accessBadge = isPending 
                        ? ''
                        : (isAccessActive 
                            ? '<span style="font-size:.65rem; font-weight:800; padding:3px 10px; border-radius:99px; background:rgba(5, 150, 105, 0.1); color:#059669;">Active Access</span>' 
                            : '<span style="font-size:.65rem; font-weight:800; padding:3px 10px; border-radius:99px; background:rgba(239, 68, 68, 0.1); color:#ef4444;">Access Suspended</span>');

                    const actionBtn = isPending ? `
                        <button onclick="approveUserRegistration(${acc.id}, '${acc.username}')" style="padding:.45rem .85rem; border-radius:10px; font-size:.75rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px; background:rgba(5, 150, 105, 0.12); border:1px solid rgba(5, 150, 105, 0.3); color:#059669;">
                            <i data-lucide="user-check" style="width:14px; height:14px;"></i> Approve
                        </button>
                        <button onclick="rejectUserRegistration(${acc.id}, '${acc.username}')" style="padding:.45rem .85rem; border-radius:10px; font-size:.75rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px; background:rgba(239, 68, 68, 0.08); border:1px solid rgba(239, 68, 68, 0.25); color:#ef4444;">
                            <i data-lucide="user-x" style="width:14px; height:14px;"></i> Decline
                        </button>
                    ` : `
                        <button onclick="toggleStaffRequestAccess(${acc.id})" style="padding:.45rem .85rem; border-radius:10px; font-size:.75rem; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px; transition:all 0.2s; ${isAccessActive ? 'background:rgba(239, 68, 68, 0.08); border:1px solid rgba(239, 68, 68, 0.25); color:#ef4444;' : 'background:rgba(5, 150, 105, 0.12); border:1px solid rgba(5, 150, 105, 0.3); color:#059669;'}">
                            <i data-lucide="${isAccessActive ? 'user-minus' : 'user-check'}" style="width:14px; height:14px;"></i> ${isAccessActive ? 'Suspend Access' : 'Grant Access'}
                        </button>
                    `;

                    return `
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-bottom:1px solid var(--border-color); gap:1rem; flex-wrap:wrap;">
                        <div style="display:flex; align-items:center; gap:.85rem;">
                            <div style="width:40px; height:40px; border-radius:12px; background:rgba(99, 102, 241, 0.1); display:flex; align-items:center; justify-content:center; font-size:.9rem; font-weight:900; color:var(--primary);">
                                ${(acc.name || acc.username).charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <div style="font-size:.88rem; font-weight:800; color:var(--text-main);">${acc.name}</div>
                                <div style="font-size:.72rem; color:var(--text-muted); font-weight:600;">${acc.role} · @${acc.username} · ${acc.department || 'General'}</div>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; margin-left:auto;">
                            <span style="font-size:.65rem; font-weight:800; padding:3px 8px; border-radius:99px; background:${acc.is_online ? 'rgba(5, 150, 105,.1)' : 'rgba(100,116,139,.1)'}; color:${acc.is_online ? '#059669' : '#64748b'};">
                                ${acc.is_online ? '&#9679; ONLINE' : '&#9675; OFFLINE'}
                            </span>
                            ${regBadge}
                            ${accessBadge}
                            ${actionBtn}
                        </div>
                    </div>
                    `;
                }).join('');

                dirContainer.innerHTML = `<div style="border:1px solid var(--border-color); border-radius:16px; overflow:hidden;">${rows}</div>`;
            }

            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (err) {
            console.error('Failed to load staff directory:', err);
        }
    };

    window.approveUserRegistration = function(userId, username) {
        Swal.fire({
            title: 'Approve User Registration?',
            text: `Approve requisitioner registration for @${username}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve User',
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b'
        }).then((result) => {
            if (!result.isConfirmed) return;

            fetch(`/dept-head/registration/${userId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (typeof window.showToast === 'function') {
                        window.showToast(data.message, 'success');
                    } else {
                        Swal.fire('Approved!', data.message, 'success');
                    }
                    loadProvisioningData(true);
                } else {
                    Swal.fire('Error', data.message || 'Failed to approve user.', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Network communication failed.', 'error'));
        });
    };

    window.rejectUserRegistration = function(userId, username) {
        Swal.fire({
            title: 'Decline Registration?',
            text: `Are you sure you want to decline registration for @${username}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Decline',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b'
        }).then((result) => {
            if (!result.isConfirmed) return;

            fetch(`/dept-head/registration/${userId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (typeof window.showToast === 'function') {
                        window.showToast(data.message, 'info');
                    } else {
                        Swal.fire('Declined', data.message, 'info');
                    }
                    loadProvisioningData(true);
                } else {
                    Swal.fire('Error', data.message || 'Failed to decline user.', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Network communication failed.', 'error'));
        });
    };

    window.toggleStaffRequestAccess = function(userId) {
        fetch(`/dept-head/staff/${userId}/toggle-request-access`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (typeof window.showToast === 'function') {
                    window.showToast(data.message, 'success');
                }
                loadProvisioningData(true);
            } else {
                Swal.fire('Error', data.message || 'Failed to toggle permission.', 'error');
                loadProvisioningData(true);
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Network error.', 'error');
            loadProvisioningData(true);
        });
    };

    document.addEventListener('DOMContentLoaded', function() {
        loadProvisioningData();

        // Auto silent refresh every 20 seconds
        let _staffPollPaused = document.hidden;
        document.addEventListener('visibilitychange', () => { _staffPollPaused = document.hidden; });
        setInterval(() => {
            if (!_staffPollPaused && !Swal.isVisible()) loadProvisioningData(true);
        }, 20000);
    });
</script>
@endpush
