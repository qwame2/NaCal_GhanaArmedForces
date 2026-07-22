@php
    $sraLayout = auth()->user()->isMainAdminOrSub() ? 'layouts.dashboard' : 'layouts.admin';
@endphp

@extends($sraLayout)

@section('title', 'Service SRA Approvals')

@section('content')
<style>
    .main-wrapper > *:not(header) {
        max-width: 2000px !important;
    }

    .sra-stat-card {
        background: var(--bg-card);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: var(--shadow-luxe);
    }

    .sra-table-row {
        border-bottom: 1px solid var(--border-color);
        transition: .15s;
    }

    .sra-table-row:hover {
        background: rgba(136, 19, 55, .03);
    }

    .sra-table-row:last-child {
        border-bottom: none;
    }

    /* Tab Controls */
    .sra-tabs-container {
        display: flex;
        gap: 0.5rem;
        border-bottom: 2px solid var(--border-color);
        margin-bottom: 1.5rem;
        padding-bottom: 2px;
    }

    .sra-tab-btn {
        padding: 0.75rem 1.5rem;
        font-weight: 700;
        font-size: 0.88rem;
        color: var(--text-muted);
        text-decoration: none;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sra-tab-btn:hover {
        color: var(--primary);
    }

    .sra-tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    .sra-badge {
        font-size: 0.72rem;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 99px;
        background: #f1f5f9;
        color: #475569;
    }

    .sra-tab-btn.active .sra-badge {
        background: var(--primary-glow);
        color: var(--primary);
    }

    /* Mini Tracker Timeline */
    .mini-tracker {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        position: relative;
        margin-top: 4px;
    }

    .mini-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    .mini-dot {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: var(--bg-main);
        border: 2.5px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        transition: all 0.25s ease;
    }

    .mini-step.completed .mini-dot {
        background: #881337;
        border-color: #881337;
        color: white;
    }

    .mini-step.active .mini-dot {
        background: #4c0519;
        border-color: #4c0519;
        color: white;
        box-shadow: 0 0 8px rgba(136, 19, 55, 0.3);
    }

    .mini-step.declined .mini-dot {
        background: #ef4444;
        border-color: #ef4444;
        color: white;
    }

    .mini-step.bypassed .mini-dot {
        background: #cbd5e1;
        border-color: #cbd5e1;
        color: #94a3b8;
    }

    .mini-line {
        width: 18px;
        height: 2.5px;
        background: var(--border-color);
        position: relative;
        z-index: 1;
        margin: 0 -2px;
    }

    .mini-line.completed {
        background: #881337;
    }

    .mini-label {
        font-size: 0.58rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-top: 2px;
        letter-spacing: 0.02em;
    }

    .mini-step.completed .mini-label {
        color: #881337;
    }

    .mini-step.active .mini-label {
        color: #4c0519;
        font-weight: 900;
    }

    .mini-step.declined .mini-label {
        color: #ef4444;
    }

    .mini-step.bypassed .mini-label {
        color: #94a3b8;
    }

    .filter-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-luxe);
    }

    .filter-row {
        display: flex;
        gap: 0.85rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-field-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        flex: 1;
        min-width: 200px;
    }

    .filter-icon {
        position: absolute;
        left: 14px;
        color: var(--text-muted);
        pointer-events: none;
    }

    .filter-control {
        width: 100%;
        padding: 0.7rem 1rem 0.7rem 2.6rem;
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-main);
        color: var(--text-main);
        font-family: inherit;
        font-weight: 600;
        font-size: 0.85rem;
        outline: none;
        transition: all 0.2s;
    }

    .filter-control:focus {
        border-color: var(--primary);
        background: var(--bg-card);
        box-shadow: 0 0 0 4px var(--primary-glow);
    }

    .filter-clear-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0.7rem 1.25rem;
        border: 1.5px solid #ef4444;
        border-radius: 12px;
        background: rgba(239, 68, 68, 0.05);
        color: #ef4444;
        font-weight: 800;
        font-size: 0.82rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .filter-clear-btn:hover {
        background: #ef4444;
        color: white;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
    }

    .sra-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(136, 19, 55, 0.1);
        color: var(--primary);
        border: 1px solid rgba(136, 19, 55, 0.2);
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 0.8rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .sra-action-btn:hover {
        background: var(--primary);
        color: white;
        box-shadow: 0 4px 10px var(--primary-glow);
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
        box-shadow: 0 10px 25px rgba(136, 19, 55, 0.25);
        transform: scale(1.1);
        z-index: 10;
    }
    .custom-pagination .page-item:not(.active):not(.disabled) .page-link:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-4px);
        background: #f5f3ff;
        box-shadow: 0 8px 20px rgba(136, 19, 55, 0.1);
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
</style>

<div style="padding: 2rem; width: 100%; box-sizing: border-box;">
    
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 style="font-size: 1.5rem; font-weight: 900; color: var(--text-heading); margin: 0;">Service SRA Approvals</h3>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin: 4px 0 0;">Track, review, and print Stock Receipt Advice (SRA) documents.</p>
        </div>
    </div>


    {{-- ── Service SRA Table ─────────────────────────────────────────────────── --}}

    <div style="margin-top: 2.5rem;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1.25rem;">
            <div style="width: 38px; height: 38px; background: rgba(139,92,246,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14.5 2 14.5 7.5 20 7.5"/></svg>
            </div>
            <div>
                <div style="font-size: 0.72rem; font-weight: 800; color: #8b5cf6; text-transform: uppercase; letter-spacing: 0.08em;">Service Contracts</div>
                <h4 style="margin: 0; font-size: 1.1rem; font-weight: 900; color: var(--text-main);">Service SRA Requests
                    @if($pendingServiceSrasCount > 0)
                        <span style="font-size: 0.72rem; background: rgba(139,92,246,0.12); color: #8b5cf6; padding: 3px 10px; border-radius: 99px; font-weight: 800; margin-left: 8px;">{{ $pendingServiceSrasCount }} Pending</span>
                    @endif
                </h4>
            </div>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-luxe);">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 900px;">
                    <thead>
                        <tr style="background: rgba(139,92,246,0.04); border-bottom: 2px solid var(--border-color);">
                            <th style="padding: 1.1rem 1.5rem; font-size: 0.78rem; text-transform: uppercase; color: #8b5cf6; font-weight: 800;">SRA #</th>
                            <th style="padding: 1.1rem 1.5rem; font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Date</th>
                            <th style="padding: 1.1rem 1.5rem; font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Supplier</th>
                            <th style="padding: 1.1rem 1.5rem; font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Submitted By</th>
                            <th style="padding: 1.1rem 1.5rem; font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Approval Flow</th>
                            <th style="padding: 1.1rem 1.5rem; font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Status</th>
                            <th style="padding: 1.1rem 1.5rem; font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($serviceSras as $sra)
                            @php
                                $badge = $sra->status_badge;
                                $pipeline = $sra->approval_pipeline;
                                $canReview = $sra->status === 'admin_approved' && $sra->stores_status === 'pending';
                            @endphp
                            <tr class="sra-table-row">
                                {{-- SRA # --}}
                                <td style="padding: 1.1rem 1.5rem; font-weight: 900; color: #8b5cf6; font-family: monospace; font-size: 0.95rem;">
                                    {{ $sra->sra_number }}
                                </td>
                                {{-- Date --}}
                                <td style="padding: 1.1rem 1.5rem; font-size: 0.85rem; color: var(--text-muted); font-weight: 700;">
                                    {{ \Carbon\Carbon::parse($sra->created_at)->format('d M Y') }}
                                </td>
                                {{-- Supplier --}}
                                <td style="padding: 1.1rem 1.5rem; font-size: 0.9rem; font-weight: 700; color: var(--text-main);">
                                    {{ $sra->supplier_name ?? '—' }}
                                </td>
                                {{-- Submitted By --}}
                                <td style="padding: 1.1rem 1.5rem; font-size: 0.85rem; color: var(--text-muted); font-weight: 700;">
                                    {{ $sra->submitter->name ?? '—' }}
                                </td>
                                {{-- Approval Flow tracker --}}
                                <td style="padding: 1.1rem 1.5rem;">
                                    <div class="mini-tracker">
                                        {{-- Step 1: Admin --}}
                                        @php $aS = $sra->admin_status; $aCls = $aS === 'approved' ? 'completed' : ($aS === 'declined' ? 'declined' : 'active'); @endphp
                                        <div class="mini-step {{ $aCls }}" title="Authorizer: {{ ucfirst($aS ?: 'pending') }}">
                                            <div class="mini-dot">
                                                @if($aS === 'approved') <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                @elseif($aS === 'declined') <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                @else <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                @endif
                                            </div>
                                            <span class="mini-label">Auth</span>
                                        </div>
                                        <div class="mini-line {{ $aS === 'approved' ? 'completed' : '' }}"></div>
                                        {{-- Step 2: Auditor --}}
                                        @php $auS = $sra->auditor_status; $auCls = $auS === 'approved' ? 'completed' : ($auS === 'declined' ? 'declined' : ($aS === 'approved' ? 'active' : 'bypassed')); @endphp
                                        <div class="mini-step {{ $auCls }}" title="Auditor: {{ ucfirst($auS ?: 'pending') }}">
                                            <div class="mini-dot">
                                                @if($auS === 'approved') <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                @elseif($auS === 'declined') <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                @else <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                @endif
                                            </div>
                                            <span class="mini-label">Audit</span>
                                        </div>
                                        <div class="mini-line {{ $auS === 'approved' ? 'completed' : '' }}"></div>
                                        {{-- Step 3: Stores --}}
                                        @php $stS = $sra->stores_status; $stCls = $stS === 'approved' ? 'completed' : ($stS === 'declined' ? 'declined' : ($auS === 'approved' ? 'active' : 'bypassed')); @endphp
                                        <div class="mini-step {{ $stCls }}" title="Stores: {{ ucfirst($stS ?: 'pending') }}">
                                            <div class="mini-dot">
                                                @if($stS === 'approved') <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                @elseif($stS === 'declined') <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                @else <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                @endif
                                            </div>
                                            <span class="mini-label">Stores</span>
                                        </div>
                                    </div>
                                </td>
                                {{-- Status --}}
                                <td style="padding: 1.1rem 1.5rem;">
                                    <span style="font-size: 0.72rem; font-weight: 800; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; padding: 4px 10px; border-radius: 6px; text-transform: uppercase;">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>
                                <!-- Action -->
                                <td style="padding: 1.1rem 1.5rem; text-align: right;">
                                    @if($canReview)
                                        <a href="{{ route('stores.service-sra.review', $sra->id) }}" target="_blank"
                                            style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; background: rgba(139,92,246,0.1); color: #8b5cf6; border: 1px solid rgba(139,92,246,0.25); font-size: 0.78rem; font-weight: 800; text-decoration: none; transition: all 0.2s;"
                                            onmouseover="this.style.background='#8b5cf6';this.style.color='white';"
                                            onmouseout="this.style.background='rgba(139,92,246,0.1)';this.style.color='#8b5cf6';">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            Review & Approve
                                        </a>
                                    @else
                                        <span style="font-size: 0.72rem; font-weight: 800; color: var(--text-muted); padding: 4px 10px; border-radius: 6px; background: var(--bg-main); border: 1px solid var(--border-color);">
                                            {{ $sra->status === 'approved' ? 'Approved' : ($sra->status === 'declined' ? 'Declined' : 'Awaiting Prior Steps') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 3rem; text-align: center; color: var(--text-muted); font-weight: 700;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 0.5rem; opacity: 0.3;"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14.5 2 14.5 7.5 20 7.5"/></svg>
                                    <div>No Service SRA requests found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>



