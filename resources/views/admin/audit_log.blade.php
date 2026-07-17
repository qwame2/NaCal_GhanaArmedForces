@extends('layouts.admin')

@section('title', 'System Activities')

@section('content')
<div class="animate-slide-up">
    <div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="font-size: 2.25rem; font-weight: 900; letter-spacing: -0.04em; color: var(--text-main); margin-bottom: 0.25rem;">System <span style="color: var(--primary);">Activities</span></h2>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; display: flex; align-items: center; gap: 0.75rem;">
                Chronological list of user activities and security updates.
            </p>
        </div>
    </div>

    <!-- Filter Console -->
    <div class="glass-card" style="padding: 2rem; margin-bottom: 2rem; border-radius: 24px; background: linear-gradient(145deg, #ffffff, #f8fafc); border: 1px solid rgba(22, 163, 74, 0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
            <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(22, 163, 74, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                <i data-lucide="filter" style="width: 18px;"></i>
            </div>
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0; letter-spacing: -0.01em;">Search Filters</h3>
        </div>
        <form action="{{ route('admin.audit-log') }}" method="GET" style="display: flex; gap: 1.5rem; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <div style="background: white; border: 1.5px solid #edf2f7; padding: 6px 12px 6px 18px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; background: #eef2ff; color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i data-lucide="user" style="width: 18px;"></i>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column; min-width: 0;">
                        <label style="font-size: 0.6rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Staff Member</label>
                        <select id="staffMemberFilter" name="user_id" style="width: 100%;">
                            <option value="">All Staff</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div style="flex: 1; min-width: 180px;">
                <div style="background: white; border: 1.5px solid #edf2f7; padding: 10px 18px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; background: #eef2ff; color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i data-lucide="calendar" style="width: 18px;"></i>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <label style="font-size: 0.6rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" style="width: 100%; background: transparent; border: none; color: var(--text-main); font-weight: 800; font-size: 0.95rem; outline: none;">
                    </div>
                </div>
            </div>

            <div style="flex: 1; min-width: 180px;">
                <div style="background: white; border: 1.5px solid #edf2f7; padding: 10px 18px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; background: #eef2ff; color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i data-lucide="calendar" style="width: 18px;"></i>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <label style="font-size: 0.6rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" style="width: 100%; background: transparent; border: none; color: var(--text-main); font-weight: 800; font-size: 0.95rem; outline: none;">
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 3px;">
                <button type="submit" class="btn-primary" style="padding: 0.95rem 2rem; border-radius: 16px; border: none; background: var(--primary); color: white; font-weight: 800; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(22,163,74,0.2);">
                    Apply Filter
                </button>
                @if(request()->hasAny(['user_id', 'date_from', 'date_to']) && (request('user_id') != '' || request('date_from') != '' || request('date_to') != ''))
                    <a href="{{ route('admin.audit-log') }}" style="padding: 0.95rem 1.5rem; color: #ef4444; background: #fef2f2; border-radius: 16px; text-decoration: none; font-size: 0.9rem; font-weight: 800; transition: all 0.3s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- System Activities List -->
    @if($auditLog->isEmpty())
        <div class="glass-card" style="padding: 4rem 2rem; text-align: center; border-radius: 24px; border: 1px dashed rgba(22, 163, 74, 0.2);">
            <div style="width: 64px; height: 64px; border-radius: 20px; background: rgba(22, 163, 74, 0.05); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                <i data-lucide="history" style="width: 32px; height: 32px;"></i>
            </div>
            <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">No Activities Found</h3>
            <p style="color: var(--text-muted); max-width: 400px; margin: 0 auto;">There are no recorded activities matching your criteria in the system registry.</p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            @foreach($auditLog as $record)
                <div class="glass-card" style="padding: 1.5rem; border-radius: 20px; border: 1px solid rgba(22, 163, 74, 0.08); background: #ffffff; box-shadow: 0 4px 20px rgba(0,0,0,0.01); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';" id="log-card-{{ $record->id }}">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 42px; height: 42px; border-radius: 12px;
                                @if(in_array($record->action, ['CREATE_USER', 'CREATE_TEMP_REQUISITIONER', 'APPROVE_USER', 'APPROVE_REQUISITION']))
                                    background: rgba(16, 185, 129, 0.08); color: #10b981;
                                @elseif(in_array($record->action, ['UPDATE_USER', 'UPDATE_PROFILE', 'TOGGLE_USER_STATUS', 'PERMISSION_CHANGE', 'REGENERATE_OTP']))
                                    background: rgba(22, 163, 74, 0.08); color: var(--primary);
                                @elseif(in_array($record->action, ['CHANGE_PASSWORD', 'PASSWORD_SYNCED', 'AUTHORIZATION', 'LOGIN']))
                                    background: rgba(16, 185, 129, 0.08); color: #10b981;
                                @else
                                    background: rgba(239, 68, 68, 0.08); color: #ef4444;
                                @endif
                                display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.25rem;">

                                @if(in_array($record->action, ['CREATE_USER', 'CREATE_TEMP_REQUISITIONER']))
                                    <i data-lucide="user-plus" style="width: 20px;"></i>
                                @elseif(in_array($record->action, ['UPDATE_USER', 'UPDATE_PROFILE']))
                                    <i data-lucide="user-cog" style="width: 20px;"></i>
                                @elseif(in_array($record->action, ['CHANGE_PASSWORD', 'PASSWORD_SYNCED', 'AUTHORIZATION', 'REGENERATE_OTP']))
                                    <i data-lucide="key-round" style="width: 20px;"></i>
                                @elseif($record->action === 'PERMISSION_CHANGE')
                                    <i data-lucide="shield-check" style="width: 20px;"></i>
                                @elseif($record->action === 'TOGGLE_USER_STATUS')
                                    <i data-lucide="user-check" style="width: 20px;"></i>
                                @elseif(in_array($record->action, ['SELF_DEACTIVATION', 'REVOKE_TEMP_REQUISITIONER']))
                                    <i data-lucide="user-x" style="width: 20px;"></i>
                                @elseif($record->action === 'LOGIN')
                                    <i data-lucide="log-in" style="width: 20px;"></i>
                                @elseif($record->action === 'LOGOUT')
                                    <i data-lucide="log-out" style="width: 20px;"></i>
                                @else
                                    <i data-lucide="activity" style="width: 20px;"></i>
                                @endif
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--text-main);">
                                    {{ str_replace('_', ' ', $record->action) }}
                                </h4>
                                <p style="margin: 2px 0 0; font-size: 0.8rem; color: var(--text-muted);">
                                    By <b>{{ $record->user->name ?? 'System / Automated' }}</b> &bull; {{ $record->created_at->format('d/m/y @ h:i A') }}
                                </p>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 800;
                                @if($record->severity === 'critical' || $record->severity === 'danger')
                                    background: #fef2f2; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.15);
                                @elseif($record->severity === 'warning')
                                    background: #ecfdf5; color: #047857; border: 1px solid rgba(217, 119, 6, 0.15);
                                @else
                                    background: #f0fdf4; color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.15);
                                @endif">
                                {{ ucfirst($record->severity) }}
                            </span>
                            @if($record->ip_address)
                                <span style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">
                                    IP: {{ $record->ip_address }}
                                </span>
                            @endif
                            {{-- Archive Button --}}
                            <form action="{{ route('admin.archive.log', $record->id) }}" method="POST" style="margin: 0;" onsubmit="archiveLog(event, this, {{ $record->id }})">
                                @csrf
                                <button type="submit" title="Archive this activity" style="background: transparent; border: 1.5px solid #e2e8f0; border-radius: 10px; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #94a3b8; transition: all 0.25s; padding: 0; flex-shrink: 0;" onmouseover="this.style.background='#f0fdf4';this.style.borderColor='#22c55e';this.style.color='#22c55e';" onmouseout="this.style.background='transparent';this.style.borderColor='#e2e8f0';this.style.color='#94a3b8';">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="5" x="2" y="3" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border-left: 4px solid
                        @if(in_array($record->action, ['CREATE_USER', 'CREATE_TEMP_REQUISITIONER', 'APPROVE_USER', 'APPROVE_REQUISITION']))
                            #10b981
                        @elseif(in_array($record->action, ['UPDATE_USER', 'UPDATE_PROFILE', 'TOGGLE_USER_STATUS', 'PERMISSION_CHANGE', 'REGENERATE_OTP']))
                            var(--primary)
                        @elseif(in_array($record->action, ['CHANGE_PASSWORD', 'PASSWORD_SYNCED', 'AUTHORIZATION', 'LOGIN']))
                            #10b981
                        @else
                            #ef4444
                        @endif; padding: 0.85rem 1.25rem; border-radius: 0 12px 12px 0; font-size: 0.88rem; color: #475569; font-weight: 600;">
                        <span style="font-weight: 800; color: var(--text-main); font-size: 0.75rem; text-transform: uppercase; display: block; margin-bottom: 2px; letter-spacing: 0.05em;">Activity Detail</span>
                        {{ $record->friendly_description }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div style="margin-top: 4rem; display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--text-muted); display: flex; align-items: center; gap: 8px; background: white; padding: 0.5rem 1.25rem; border-radius: 100px; border: 1.5px solid #edf2f7; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
                <i data-lucide="info" style="width: 14px; color: var(--primary);"></i>
                Showing <span style="color: var(--text-main);">{{ $auditLog->firstItem() ?? 0 }}</span> to <span style="color: var(--text-main);">{{ $auditLog->lastItem() ?? 0 }}</span> of <span style="color: var(--text-main);">{{ $auditLog->total() }}</span> records
            </div>
            <div class="custom-pagination">
                {{ $auditLog->appends(request()->all())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    @endif
</div>

<style>
    .main-wrapper > *:not(header) {
        max-width: 2000px !important;
    }
    /* Premium Glassmorphic Pagination */
    .custom-pagination nav { display: flex; justify-content: center; }
    .custom-pagination ul.pagination { display: flex; gap: 0.5rem; list-style: none; padding: 0; margin: 0; align-items: center; }
    .custom-pagination .page-item .page-link {
        display: flex; align-items: center; justify-content: center;
        min-width: 48px; height: 48px; border-radius: 16px;
        background: white; border: 1.5px solid #edf2f7;
        color: var(--text-main); font-weight: 900; text-decoration: none;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.95rem; box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    }
    .custom-pagination .page-item.active .page-link {
        background: var(--primary); color: white;
        border-color: var(--primary);
        box-shadow: 0 10px 25px rgba(22, 163, 74, 0.25);
        transform: scale(1.1);
        z-index: 10;
    }
    .custom-pagination .page-item:not(.active):not(.disabled) .page-link:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-4px);
        background: #f5f3ff;
        box-shadow: 0 8px 20px rgba(22, 163, 74, 0.1);
    }
    .custom-pagination .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8fafc;
    }
    .custom-pagination .page-item:first-child .page-link,
    .custom-pagination .page-item:last-child .page-link {
        padding: 0 1.25rem;
        min-width: auto;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    /* ── Select2 overrides for Staff Member filter ── */
    #staffMemberFilter + .select2-container { width: 100% !important; }
    #staffMemberFilter ~ .select2-container,
    .select2-container#select2-staffMemberFilter-container { width: 100% !important; }

    .select2-container--default .select2-selection--single {
        background: transparent !important;
        border: none !important;
        height: 28px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--text-main) !important;
        font-weight: 800 !important;
        font-size: 0.95rem !important;
        padding-left: 0 !important;
        line-height: 28px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #94a3b8 !important;
        font-weight: 600 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 28px !important;
        right: 4px !important;
    }
    .select2-dropdown {
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: 0 20px 50px rgba(0,0,0,0.1) !important;
        overflow: hidden !important;
        background: white !important;
        padding: 6px !important;
        z-index: 9999 !important;
    }
    .select2-search--dropdown .select2-search__field {
        border-radius: 10px !important;
        padding: 8px 12px !important;
        border: 1.5px solid #e2e8f0 !important;
        font-weight: 600 !important;
        font-size: 0.88rem !important;
        outline: none !important;
    }
    .select2-results__option {
        padding: 9px 14px !important;
        font-size: 0.88rem !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        color: #334155 !important;
        margin-bottom: 2px !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: var(--primary) !important;
        color: white !important;
    }
    .select2-results__option[aria-selected="true"] {
        background: #eef2ff !important;
        color: var(--primary) !important;
    }
</style>

<script>
function archiveLog(event, form, id) {
    event.preventDefault();
    const card = document.getElementById('log-card-' + id);
    const btn  = form.querySelector('button');

    // Spin the icon to indicate loading
    btn.style.opacity = '0.5';
    btn.style.pointerEvents = 'none';

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
            'Accept': 'application/json'
        }
    })
    .then(res => {
        if (res.ok || res.status === 302) {
            // Fade out and remove card
            card.style.transition = 'opacity 0.4s ease, transform 0.4s ease, max-height 0.4s ease';
            card.style.opacity    = '0';
            card.style.transform  = 'translateY(-8px)';
            setTimeout(() => card.remove(), 420);

            // Toast
            const toast = document.createElement('div');
            toast.textContent = 'Activity archived';
            toast.style.cssText = 'position:fixed;bottom:2rem;right:2rem;background:#1e293b;color:#fff;padding:0.75rem 1.5rem;border-radius:12px;font-weight:700;font-size:0.9rem;box-shadow:0 8px 30px rgba(0,0,0,0.15);z-index:9999;opacity:0;transition:opacity 0.3s;';
            document.body.appendChild(toast);
            requestAnimationFrame(() => toast.style.opacity = '1');
            setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 2500);
        } else {
            btn.style.opacity = '1';
            btn.style.pointerEvents = 'auto';
        }
    })
    .catch(() => {
        btn.style.opacity = '1';
        btn.style.pointerEvents = 'auto';
    });
}
</script>

<script>
$(function () {
    $('#staffMemberFilter').select2({
        placeholder: 'All Staff',
        allowClear: true,
        width: '100%',
        dropdownAutoWidth: true
    });

    // Auto-submit filter form on selection change
    $('#staffMemberFilter').on('change', function () {
        $(this).closest('form').submit();
    });
});
</script>
@endsection

