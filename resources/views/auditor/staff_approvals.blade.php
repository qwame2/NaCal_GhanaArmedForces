@extends('layouts.dashboard')

@section('content')
<style>
    :root {
        --audit-primary: #10b981;
        --audit-primary-hover: #059669;
        --shadow-premium: 0 20px 40px -15px rgba(15, 23, 42, 0.05), 0 0 0 1px rgba(15, 23, 42, 0.03);
    }
</style>

<div style="padding: 2rem;">
    {{-- Header Section --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
        <div>
            <div style="font-size:0.8rem; font-weight:800; text-transform:uppercase; color:var(--audit-primary); letter-spacing:0.08em; margin-bottom:4px;">
                Auditor Management
            </div>
            <h1 style="font-size:1.75rem; font-weight:900; color:var(--text-main); margin:0; letter-spacing:-0.03em;">
                Staff Access &amp; Approvals
            </h1>
            <p style="font-size:0.88rem; color:var(--text-muted); font-weight:600; margin:4px 0 0;">
                Review pending registration requests and manage permissions for audit staff.
            </p>
        </div>
    </div>

    {{-- Provisioning Section --}}
    <div id="provisioningSection" style="background:var(--bg-card); border-radius:20px; border:1px solid var(--border-color); padding:2rem; box-shadow:var(--shadow-premium);">
        @if(!empty($hasOverdueReturn))
        <div style="background: linear-gradient(135deg, rgba(254, 242, 242, 0.65) 0%, rgba(254, 226, 226, 0.35) 100%); border-left: 5px solid #ef4444; border-top: 1px solid rgba(239, 68, 68, 0.1); border-right: 1px solid rgba(239, 68, 68, 0.1); border-bottom: 1px solid rgba(239, 68, 68, 0.1); border-radius: 16px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 1.25rem; box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.05); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
            <div style="width: 40px; height: 40px; background: rgba(239, 68, 68, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; animation: alertPulse 2s infinite;">
                <i data-lucide="alert-triangle" style="width: 20px; height: 20px; color: #ef4444;"></i>
            </div>
            <div style="flex: 1; display: flex; flex-direction: column; gap: 0.25rem;">
                <div style="font-family: 'Outfit', sans-serif; font-size: 0.9rem; font-weight: 900; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.03em;">
                    Access Suspended
                </div>
                <div style="font-size: 0.85rem; color: #7f1d1d; font-weight: 600; line-height: 1.5;">
                    Your department currently has overdue temporary assets. Access to provision temporary requisitioner accounts is suspended until all overdue items are officially logged as returned.
                </div>
                <div style="margin-top: 0.85rem;">
                    <a href="{{ route('requisitions.overdue') }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 0.6rem 1.2rem; font-size: 0.78rem; font-weight: 850; color: white; background: #ef4444; border-radius: 10px; text-decoration: none; border: 1px solid rgba(239, 68, 68, 0.2); transition: all 0.25s ease; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.25);">
                        <i data-lucide="eye" style="width: 14px; height: 14px;"></i> View Overdue Assets
                    </a>
                </div>
            </div>
        </div>
        @endif

        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
            <div style="display:flex; align-items:center; gap:0.85rem;">
                <div style="width:42px; height:42px; background:linear-gradient(135deg,rgba(16,185,129,0.15),rgba(5,150,105,0.1)); border-radius:12px; display:flex; align-items:center; justify-content:center; border:1px solid rgba(16,185,129,0.2);">
                    <i data-lucide="user-plus" style="width:20px; height:20px; color:#10b981;"></i>
                </div>
                <div>
                    <div style="font-size:.68rem; font-weight:800; color:#10b981; text-transform:uppercase; letter-spacing:.1em;">Dept. Access Management</div>
                    <div style="font-size:1rem; font-weight:800; color:var(--text-main); margin-top:1px;">Staff Access &amp; Approvals</div>
                </div>
            </div>
        </div>

        {{-- Department Staff Accounts Table --}}
        <div id="tempAccountsContainer" style="margin-bottom: 2.5rem;">
            <div style="font-size:.72rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:0.85rem;">Department Staff Access &amp; Permissions</div>
            <div id="tempAccountsList">
                <div style="text-align:center; padding:1.5rem; color:var(--text-muted); font-size:.85rem;">
                    <i data-lucide="loader" style="width:18px; height:18px; display:inline-block; margin-bottom:6px; opacity:.5; animation: spin 1s linear infinite;"></i><br>Loading department staff directory...
                </div>
            </div>
        </div>

        {{-- Department Pending Registrations Table --}}
        <div id="pendingRegistrationsContainer">
            <div style="font-size:.72rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:0.85rem;">Pending Staff Registrations</div>
            <div id="pendingRegistrationsList">
                <div style="text-align:center; padding:1.5rem; color:var(--text-muted); font-size:.85rem;">
                    <i data-lucide="loader" style="width:18px; height:18px; display:inline-block; margin-bottom:6px; opacity:.5; animation: spin 1s linear infinite;"></i><br>Loading pending registrations...
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadProvisioningData();

        // Auto-refresh every 30 seconds (paused when tab is hidden)
        let _auditorStaffPollPaused = document.hidden;
        document.addEventListener('visibilitychange', () => { _auditorStaffPollPaused = document.hidden; });
        setInterval(() => {
            if (!_auditorStaffPollPaused) loadProvisioningData(true);
        }, 30000);
    });

    // ── Staff Provisioning (AJAX wrappers) ──────────────────────────────────────
    async function loadProvisioningData(isSilent = false) {
        const tempContainer = document.getElementById('tempAccountsList');
        const pendingContainer = document.getElementById('pendingRegistrationsList');
        
        if (!tempContainer && !pendingContainer) return;

        if (!isSilent) {
            const skeletonStaffList = `
                <div style="border:1px solid var(--border-color); border-radius:14px; padding: 1rem; margin-bottom: 0.5rem; display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap: 12px; width: 60%;">
                        <div class="skeleton-avatar" style="width:36px; height:36px; border-radius:50%;"></div>
                        <div style="flex:1;">
                            <div class="skeleton-line" style="width:140px; margin-bottom:4px;"></div>
                            <div class="skeleton-line" style="width:80px; height:10px;"></div>
                        </div>
                    </div>
                    <div class="skeleton-badge" style="width:90px;"></div>
                </div>
            `.repeat(3);

            if (tempContainer) {
                tempContainer.innerHTML = skeletonStaffList;
            }
            if (pendingContainer) {
                pendingContainer.innerHTML = skeletonStaffList;
            }
        }

        try {
            const res = await fetch('{{ route("dept-head.provisioning-dashboard") }}');
            const data = await res.json();

            if (!data.success) return;

            // Render active accounts
            if (tempContainer) {
                if (!data.accounts || data.accounts.length === 0) {
                    const emptyHtml = `
                        <div style="text-align:center;padding:1.5rem 1rem;border:1px dashed var(--border-color);border-radius:12px;">
                            <div style="font-size:1.75rem;margin-bottom:.4rem;">👥</div>
                            <div style="font-size:.82rem;font-weight:700;color:var(--text-muted);">No department staff found</div>
                            <div style="font-size:.73rem;color:var(--text-muted);margin-top:.2rem;">Any registered staff in your department will appear here.</div>
                        </div>`;
                    if (tempContainer.innerHTML !== emptyHtml) {
                        tempContainer.innerHTML = emptyHtml;
                    }
                    window._lastStaffDataString = '';
                } else {
                    const currentDataString = JSON.stringify(data.accounts);
                    if (!isSilent || window._lastStaffDataString !== currentDataString) {
                        window._lastStaffDataString = currentDataString;

                        let rows = data.accounts.map(acc => {
                            const isAccessActive = acc.can_make_requisition;
                            const badgeStyle = isAccessActive 
                                ? 'background:rgba(16,185,129,.1);color:#10b981;' 
                                : 'background:rgba(239,68,68,.1);color:#ef4444;';
                            const badgeText = isAccessActive ? 'Active Access' : 'Access Suspended';
                            
                            const btnStyle = isAccessActive
                                ? 'background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#ef4444;'
                                : 'background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#10b981;';
                            const btnText = isAccessActive ? 'Suspend Access' : 'Grant Access';
                            const btnIcon = isAccessActive ? 'user-minus' : 'user-check';

                            return `
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:.9rem 1rem;border-bottom:1px solid var(--border-color);gap:1rem;flex-wrap:wrap;">
                                <div style="display:flex;align-items:center;gap:.75rem;">
                                    <div style="width:38px;height:38px;border-radius:10px;background:rgba(22,163,74,0.1);display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:800;color:#16a34a;">
                                        ${(acc.name || acc.username).charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <div style="font-size:.85rem;font-weight:700;color:var(--text-main);">${acc.name || '@' + acc.username}</div>
                                        <div style="font-size:.7rem;color:var(--text-muted);">${acc.role} · @${acc.username}</div>
                                    </div>
                                </div>
                                <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                                    <span style="font-size:.65rem;font-weight:800;padding:3px 8px;border-radius:99px;background:${acc.is_online ? 'rgba(16,185,129,.1)' : 'rgba(100,116,139,.1)'};color:${acc.is_online ? '#10b981' : '#64748b'};">
                                        ${acc.is_online ? '● ONLINE' : '○ OFFLINE'}
                                    </span>
                                    <span style="font-size:.65rem;font-weight:800;padding:3px 8px;border-radius:99px;${badgeStyle}">
                                        ${badgeText}
                                    </span>
                                    <button onclick="toggleStaffAccess(${acc.id}, '${acc.username}', ${isAccessActive})" style="padding:.4rem .7rem;border-radius:8px;font-size:.72rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.3rem;transition:all 0.2s;${btnStyle}">
                                        <i data-lucide="${btnIcon}" style="width:13px;height:13px;"></i> ${btnText}
                                    </button>
                                </div>
                            </div>
                            `;
                        }).join('');

                        tempContainer.innerHTML = `<div style="border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">${rows}</div>`;
                    }
                }
            }

            // Render pending registrations
            if (pendingContainer) {
                if (!data.pending || data.pending.length === 0) {
                    const emptyHtml = `
                        <div style="text-align:center;padding:1.5rem 1rem;border:1px dashed var(--border-color);border-radius:12px;">
                            <div style="font-size:1.75rem;margin-bottom:.4rem;">📝</div>
                            <div style="font-size:.82rem;font-weight:700;color:var(--text-muted);">No pending registrations</div>
                            <div style="font-size:.73rem;color:var(--text-muted);margin-top:.2rem;">New staff registration requests will show up here.</div>
                        </div>`;
                    if (pendingContainer.innerHTML !== emptyHtml) {
                        pendingContainer.innerHTML = emptyHtml;
                    }
                    window._lastPendingString = '';
                } else {
                    const currentPendingString = JSON.stringify(data.pending);
                    if (!isSilent || window._lastPendingString !== currentPendingString) {
                        window._lastPendingString = currentPendingString;

                        let rows = data.pending.map(req => {
                            return `
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:.9rem 1rem;border-bottom:1px solid var(--border-color);gap:1rem;flex-wrap:wrap;">
                                <div>
                                    <div style="font-size:.85rem;font-weight:700;color:var(--text-main);">${req.name}</div>
                                    <div style="font-size:.72rem;color:var(--text-muted);margin-top:2px;">
                                        Username: <b>@${req.username}</b> · Service No: <b>${req.service_number || 'N/A'}</b> · Phone: <b>${req.phone || 'N/A'}</b>
                                    </div>
                                    <div style="font-size:.65rem;color:var(--text-muted);margin-top:4px;">Requested: ${req.created_at}</div>
                                </div>
                                <div style="display:flex;align-items:center;gap:.75rem;">
                                    <button onclick="approveRegistration(${req.id}, '${req.username}')" style="padding:.45rem .9rem;border:none;background:rgba(16,185,129,.1);color:#10b981;border-radius:8px;font-size:.72rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.3rem;transition:all 0.2s;" onmouseover="this.style.background='#10b981';this.style.color='white';" onmouseout="this.style.background='rgba(16,185,129,.1)';this.style.color='#10b981';">
                                        <i data-lucide="user-check" style="width:13px;height:13px;"></i> Approve
                                    </button>
                                    <button onclick="declineRegistration(${req.id}, '${req.username}')" style="padding:.45rem .9rem;border:none;background:rgba(239,68,68,.08);color:#ef4444;border-radius:8px;font-size:.72rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.3rem;transition:all 0.2s;" onmouseover="this.style.background='#ef4444';this.style.color='white';" onmouseout="this.style.background='rgba(239,68,68,.08)';this.style.color='#ef4444';">
                                        <i data-lucide="user-x" style="width:13px;height:13px;"></i> Decline
                                    </button>
                                </div>
                            </div>
                            `;
                        }).join('');

                        pendingContainer.innerHTML = `<div style="border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">${rows}</div>`;
                    }
                }
            }

            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (e) {
            console.error('Failed to load provisioning data:', e);
            if (!isSilent) {
                if (tempContainer) {
                    tempContainer.innerHTML = `<div style="text-align:center;padding:1rem;color:var(--text-muted);font-size:.8rem;">Failed to load staff list.</div>`;
                }
                if (pendingContainer) {
                    pendingContainer.innerHTML = `<div style="text-align:center;padding:1rem;color:var(--text-muted);font-size:.8rem;">Failed to load registration requests.</div>`;
                }
            }
        }
    }

    function loadTempAccounts(isSilent = false) {
        loadProvisioningData(isSilent);
    }

    async function toggleStaffAccess(id, username, isCurrentlyActive) {
        const actionWord = isCurrentlyActive ? 'Suspend' : 'Grant';
        const actionColor = isCurrentlyActive ? '#ef4444' : '#10b981';
        
        const confirm = await Swal.fire({
            title: `${actionWord} Requisition Access?`,
            text: `Are you sure you want to ${actionWord.toLowerCase()} requisition privileges for @${username}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: actionColor,
            cancelButtonColor: '#64748b',
            confirmButtonText: `Yes, ${actionWord}`,
            cancelButtonText: 'Cancel'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`/dept-head/staff/${id}/toggle-request-access`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ 
                    title: 'Updated!', 
                    text: data.message, 
                    icon: 'success', 
                    timer: 2000, 
                    showConfirmButton: false 
                });
                loadTempAccounts();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Network error while updating access privileges.', 'error');
        }
    }

    function loadPendingRegistrations(isSilent = false) {
        loadProvisioningData(isSilent);
    }

    async function approveRegistration(id, username) {
        const confirm = await Swal.fire({
            title: 'Approve Registration?',
            text: `Are you sure you want to approve user account registration for @${username}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`/dept-head/registration/${id}/approve`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ 
                    title: 'Approved!', 
                    text: data.message, 
                    icon: 'success', 
                    timer: 2000, 
                    showConfirmButton: false 
                });
                loadPendingRegistrations();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Network error while approving registration.', 'error');
        }
    }

    async function declineRegistration(id, username) {
        const confirm = await Swal.fire({
            title: 'Decline Registration?',
            text: `Are you sure you want to decline registration for @${username}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Decline',
            cancelButtonText: 'Cancel'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`/dept-head/registration/${id}/reject`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ 
                    title: 'Declined!', 
                    text: data.message, 
                    icon: 'success', 
                    timer: 2000, 
                    showConfirmButton: false 
                });
                loadPendingRegistrations();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Network error while declining registration.', 'error');
        }
    }
</script>
@endpush
@endsection
