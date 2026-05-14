@extends('layouts.admin')

@section('title', 'System Archive')

@section('content')
<div class="animate-slide-up">
    <div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="font-size: 2.25rem; font-weight: 900; letter-spacing: -0.04em; color: var(--text-main); margin-bottom: 0.25rem;">System <span style="color: var(--primary);">Archive</span></h2>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Access archived communications and system activity records.</p>
        </div>
        
        <div style="display: flex; gap: 1rem; background: #fff; padding: 0.5rem; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
            <a href="{{ route('admin.archive', ['type' => 'messages']) }}" 
               style="padding: 0.75rem 1.5rem; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 0.9rem; transition: all 0.3s; {{ $type === 'messages' ? 'background: var(--primary); color: white; box-shadow: 0 8px 20px rgba(79, 70, 229, 0.2);' : 'color: var(--text-muted);' }}">
               <i data-lucide="message-square" style="width: 18px; display: inline-block; vertical-align: middle; margin-right: 8px;"></i> Archived Messages
            </a>
            <a href="{{ route('admin.archive', ['type' => 'logs']) }}" 
               style="padding: 0.75rem 1.5rem; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 0.9rem; transition: all 0.3s; {{ $type === 'logs' ? 'background: var(--primary); color: white; box-shadow: 0 8px 20px rgba(79, 70, 229, 0.2);' : 'color: var(--text-muted);' }}">
               <i data-lucide="activity" style="width: 18px; display: inline-block; vertical-align: middle; margin-right: 8px;"></i> Archived Logs
            </a>
        </div>
    </div>

    @if($type === 'messages')
    <div class="glass-card" style="padding: 0; overflow: hidden; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.05); border: 1px solid rgba(79, 70, 229, 0.08);">
        <div style="max-height: 70vh; overflow-y: auto;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0; text-align: left;">
                <thead style="position: sticky; top: 0; z-index: 20; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(15px); box-shadow: 0 1px 0 var(--border-color);">
                    <tr>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Registry</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Message Details</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Archived At</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Manage</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $msg)
                    <tr style="border-bottom: 1px solid var(--border-color); transition: all 0.3s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1.5rem 2rem;">
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">From:</span>
                                    <span style="font-weight: 900; color: var(--text-main); font-size: 0.85rem;">{{ $msg->sender->name ?? 'System' }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">To:</span>
                                    <span style="font-weight: 900; color: var(--text-main); font-size: 0.85rem;">{{ $msg->receiver->name ?? 'System' }}</span>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 1.5rem 2rem; max-width: 450px;">
                            <div style="font-size: 0.85rem; color: var(--text-main); line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; font-weight: 500;">
                                {!! $msg->message !!}
                            </div>
                            @if($msg->attachment)
                            <div style="margin-top: 8px; display: flex; align-items: center; gap: 6px; color: var(--primary); font-size: 0.7rem; font-weight: 800;">
                                <i data-lucide="paperclip" style="width: 12px;"></i> {{ $msg->attachment_name ?: 'Attachment Attached' }}
                            </div>
                            @endif
                        </td>
                        <td style="padding: 1.5rem 2rem;">
                            @if($msg->is_automated)
                            <span style="font-size: 0.65rem; font-weight: 900; background: #e0f2fe; color: #0369a1; padding: 5px 12px; border-radius: 8px; text-transform: uppercase; letter-spacing: 0.02em;">Automated System</span>
                            @else
                            <span style="font-size: 0.65rem; font-weight: 900; background: #f0fdf4; color: #15803d; padding: 5px 12px; border-radius: 8px; text-transform: uppercase; letter-spacing: 0.02em;">Personnel Manual</span>
                            @endif
                        </td>
                        <td style="padding: 1.5rem 2rem;">
                            <div style="font-weight: 800; color: var(--text-main); font-size: 0.85rem;">{{ $msg->updated_at->format('d/m/y') }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">{{ $msg->updated_at->format('H:i:s') }}</div>
                        </td>
                        <td style="padding: 1.5rem 2rem; text-align: right;">
                            <form action="{{ route('admin.archive.restore.message', $msg->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                <button type="submit" style="background: rgba(16, 185, 129, 0.05); color: #10b981; border: 1.5px solid rgba(16, 185, 129, 0.2); padding: 0.6rem 1.25rem; border-radius: 12px; font-weight: 900; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; font-size: 0.8rem;" onmouseover="this.style.background='#10b981'; this.style.color='white'; this.style.boxShadow='0 4px 12px rgba(16, 185, 129, 0.2)'" onmouseout="this.style.background='rgba(16, 185, 129, 0.05)'; this.style.color='#10b981'; this.style.boxShadow='none'">
                                    <i data-lucide="rotate-ccw" style="width: 14px;"></i> Restore Message
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding: 8rem 2rem; text-align: center; color: var(--text-muted);">
                            <div style="width: 100px; height: 100px; background: #f8fafc; border-radius: 30px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem auto; box-shadow: inset 0 2px 10px rgba(0,0,0,0.02);">
                                <i data-lucide="inbox" style="width: 48px; height: 48px; color: var(--primary); opacity: 0.2;"></i>
                            </div>
                            <h4 style="font-weight: 950; color: var(--text-main); font-size: 1.5rem; margin-bottom: 0.5rem; letter-spacing: -0.02em;">Message Archive Empty</h4>
                            <p style="font-weight: 500; font-size: 1.1rem;">Historical communications have not been logged here yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="glass-card" style="padding: 0; overflow: hidden; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.05); border: 1px solid rgba(79, 70, 229, 0.08);">
        <div style="max-height: 70vh; overflow-y: auto;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0; text-align: left;">
                <thead style="position: sticky; top: 0; z-index: 20; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(15px); box-shadow: 0 1px 0 var(--border-color);">
                    <tr>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Date & Time</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Personnel</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Event Details</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Risk Level</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Manage</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $log)
                    <tr style="border-bottom: 1px solid var(--border-color); transition: all 0.3s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1.5rem 2rem;">
                            <div style="font-weight: 900; color: var(--text-main); font-size: 0.85rem; letter-spacing: -0.01em;">{{ $log->created_at->format('d/m/y') }}</div>
                            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-top: 2px;">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>
                        <td style="padding: 1.5rem 2rem;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                @if($log->user && $log->user->avatar)
                                    <img src="{{ asset('storage/' . $log->user->avatar) }}" style="width: 34px; height: 34px; border-radius: 10px; object-fit: cover; border: 1.5px solid #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
                                @else
                                    <div style="width: 34px; height: 34px; border-radius: 10px; background: {{ $log->user ? 'var(--primary)' : '#94a3b8' }}; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 900; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
                                        {{ $log->user ? substr($log->user->name, 0, 1) : 'S' }}
                                    </div>
                                @endif
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-weight: 900; color: var(--text-main); font-size: 0.85rem; letter-spacing: -0.01em;">{{ $log->user->name ?? 'System Auto' }}</span>
                                    <span style="font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">{{ $log->user ? $log->user->role : 'Strategic AI' }}</span>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 1.5rem 2rem; max-width: 350px;">
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                <span style="font-size: 0.65rem; font-weight: 900; background: white; border: 1px solid #e2e8f0; color: #475569; padding: 4px 10px; border-radius: 8px; width: fit-content; text-transform: uppercase;">
                                    {{ $log->event_type === 'AUDIT' ? 'Activity Record' : $log->event_type }}
                                </span>
                                <div style="font-size: 0.85rem; color: var(--text-main); line-height: 1.5; font-weight: 500;">{{ $log->description }}</div>
                            </div>
                        </td>
                        <td style="padding: 1.5rem 2rem;">
                            @php
                                $severity = strtolower($log->severity ?? 'info');
                                $sevConfig = match($severity) {
                                    'danger', 'critical', 'high' => ['#fef2f2', '#ef4444', 'CRITICAL'],
                                    'warning', 'medium' => ['#fffbeb', '#f59e0b', 'WARNING'],
                                    'success', 'low', 'stable' => ['#f0fdf4', '#10b981', 'STABLE'],
                                    'info' => ['#e0f2fe', '#0ea5e9', 'INFO'],
                                    default => ['#f8fafc', '#64748b', strtoupper($severity)]
                                };
                            @endphp
                            <span style="font-size: 0.65rem; font-weight: 900; background: {{ $sevConfig[0] }}; color: {{ $sevConfig[1] }}; padding: 5px 12px; border-radius: 8px; text-transform: uppercase; border: 1px solid {{ $sevConfig[1] }}20; letter-spacing: 0.02em;">
                                {{ $sevConfig[2] }}
                            </span>
                        </td>
                        <td style="padding: 1.5rem 2rem; text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                                @if($log->metadata)
                                <button type="button" 
                                    data-metadata="{{ json_encode($log->metadata) }}" 
                                    data-event="{{ $log->event_type }}" 
                                    data-action="{{ $log->action }}" 
                                    data-user-name="{{ $log->user ? $log->user->name : 'System Auto' }}"
                                    data-user-avatar="{{ $log->user && $log->user->avatar ? asset('storage/' . $log->user->avatar) : '' }}"
                                    onclick="viewLogDetails(this)" 
                                    style="background: white; border: 1.5px solid #edf2f7; color: var(--text-main); padding: 0.6rem 1rem; border-radius: 12px; font-weight: 800; font-size: 0.8rem; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 6px;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#f8fafc'" onmouseout="this.style.borderColor='#edf2f7'; this.style.background='white'">
                                    <i data-lucide="info" style="width: 14px;"></i> Details
                                </button>
                                @endif
                                <form action="{{ route('admin.archive.restore.log', $log->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" style="background: rgba(16, 185, 129, 0.05); color: #10b981; border: 1.5px solid rgba(16, 185, 129, 0.2); padding: 0.6rem 1.25rem; border-radius: 12px; font-weight: 900; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; font-size: 0.8rem;" onmouseover="this.style.background='#10b981'; this.style.color='white'; this.style.boxShadow='0 4px 12px rgba(16, 185, 129, 0.2)'" onmouseout="this.style.background='rgba(16, 185, 129, 0.05)'; this.style.color='#10b981'; this.style.boxShadow='none'">
                                        <i data-lucide="rotate-ccw" style="width: 14px;"></i> Restore
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding: 8rem 2rem; text-align: center; color: var(--text-muted);">
                            <div style="width: 100px; height: 100px; background: #f8fafc; border-radius: 30px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem auto; box-shadow: inset 0 2px 10px rgba(0,0,0,0.02);">
                                <i data-lucide="activity" style="width: 48px; height: 48px; color: var(--primary); opacity: 0.2;"></i>
                            </div>
                            <h4 style="font-weight: 950; color: var(--text-main); font-size: 1.5rem; margin-bottom: 0.5rem; letter-spacing: -0.02em;">Log Archive Empty</h4>
                            <p style="font-weight: 500; font-size: 1.1rem;">No system activity records have been secured here yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div style="margin-top: 4rem; display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
        <div style="font-size: 0.85rem; font-weight: 800; color: var(--text-muted); display: flex; align-items: center; gap: 8px; background: white; padding: 0.5rem 1.25rem; border-radius: 100px; border: 1.5px solid #edf2f7; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
            <i data-lucide="info" style="width: 14px; color: var(--primary);"></i>
            Showing <span style="color: var(--text-main);">{{ $data->firstItem() }}</span> to <span style="color: var(--text-main);">{{ $data->lastItem() }}</span> of <span style="color: var(--text-main);">{{ $data->total() }}</span> archived records
        </div>
        <div class="custom-pagination">
            {{ $data->appends(['type' => $type])->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Details Modal (Ported from Logs page) -->
<div id="logDetailsModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(10px); z-index: 9999; align-items: center; justify-content: center; padding: 2rem;">
    <div class="glass-card animate-zoom-in" style="width: 100%; max-width: 600px; padding: 0; overflow: hidden; border-radius: 32px; box-shadow: 0 30px 60px rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1);">
        <div class="modal-header" style="padding: 2.5rem; background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: white; display: flex; justify-content: space-between; align-items: center; position: relative;">
            <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
            <div style="display: flex; align-items: center; gap: 1rem; position: relative;">
                <div style="width: 54px; height: 54px; background: rgba(255,255,255,0.15); border-radius: 16px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
                    <i data-lucide="file-text" style="width: 28px; height: 28px;"></i>
                </div>
                <div>
                    <h3 id="modalEventTitle" style="font-size: 1.35rem; font-weight: 950; letter-spacing: -0.03em; margin: 0;">Event Payload</h3>
                    <div id="modalEventAction" style="font-size: 0.75rem; font-weight: 800; background: rgba(0,0,0,0.2); padding: 3px 10px; border-radius: 6px; width: fit-content; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.05em;">Unknown Action</div>
                </div>
            </div>
            <button onclick="closeLogDetails()" style="width: 44px; height: 44px; border-radius: 50%; border: none; background: rgba(255,255,255,0.1); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s; position: relative;" onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='rotate(90deg)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='rotate(0deg)'">
                <i data-lucide="x" style="width: 22px;"></i>
            </button>
        </div>
        <div id="modalMetadataContent" style="padding: 2.5rem; max-height: 60vh; overflow-y: auto; background: white;">
            <!-- Content dynamic via JS -->
        </div>
        <div style="padding: 1.5rem 2.5rem; background: #f8fafc; border-top: 1px solid var(--border-color); text-align: center;">
            <p style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;">Archive Record Persistence • Historical Integrity Verified</p>
        </div>
    </div>
</div>

<style>
    .detail-card { background: white; border: 1.5px solid #edf2f7; border-radius: 20px; padding: 1.25rem 1.5rem; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .detail-label { font-size: 0.65rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px; }
    .detail-value { font-size: 1rem; font-weight: 800; color: var(--text-main); letter-spacing: -0.01em; }
    .detail-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.25rem; }
    
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
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.25);
        transform: scale(1.1);
        z-index: 10;
    }
    .custom-pagination .page-item:not(.active):not(.disabled) .page-link:hover { 
        border-color: var(--primary); 
        color: var(--primary); 
        transform: translateY(-4px);
        background: #f5f3ff;
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.1);
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

<script>
    function viewLogDetails(btn) {
        const metadataStr = btn.getAttribute('data-metadata');
        const eventType = btn.getAttribute('data-event');
        const action = btn.getAttribute('data-action');
        const userName = btn.getAttribute('data-user-name');
        const userAvatar = btn.getAttribute('data-user-avatar');

        document.getElementById('modalEventTitle').textContent = eventType + ' Audit Record';
        document.getElementById('modalEventAction').textContent = action;
        
        const contentDiv = document.getElementById('modalMetadataContent');
        contentDiv.innerHTML = '';

        try {
            const dataObj = typeof metadataStr === 'string' ? JSON.parse(metadataStr) : metadataStr;
            
            let html = `
                <div class="detail-card" style="display: flex; align-items: center; gap: 1.25rem; margin-bottom: 2rem; background: #f8fafc; border: 1px solid var(--border-color); box-shadow: none;">
                    ${userAvatar ? `<img src="${userAvatar}" style="width: 54px; height: 54px; border-radius: 16px; object-fit: cover; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">` : `<div style="width: 54px; height: 54px; border-radius: 16px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 900; box-shadow: 0 4px 10px rgba(79,70,229,0.2);">${userName.charAt(0)}</div>`}
                    <div>
                        <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 3px; letter-spacing: 0.05em;">Originating Personnel</div>
                        <div style="font-size: 1.2rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.02em;">${userName}</div>
                    </div>
                </div>
                <div class="detail-grid">
            `;

            for (const [key, value] of Object.entries(dataObj)) {
                if (key === 'user_id' || key === 'id') continue;
                const cleanKey = key.replace(/_/g, ' ').toUpperCase();
                let displayValue = value;
                if (typeof value === 'object' && value !== null) {
                    displayValue = JSON.stringify(value, null, 2);
                }
                
                html += `
                    <div class="detail-card">
                        <div class="detail-label">${cleanKey}</div>
                        <div class="detail-value">${displayValue}</div>
                    </div>
                `;
            }
            html += `</div>`;
            contentDiv.innerHTML = html;
        } catch (e) {
            contentDiv.innerHTML = `<p style="color: #ef4444; font-weight: 800;">[ARCHIVE ERROR] Metadata integrity check failed or data is corrupted.</p>`;
        }

        document.getElementById('logDetailsModal').style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeLogDetails() {
        document.getElementById('logDetailsModal').style.display = 'none';
    }

    // Close on click outside
    window.onclick = function(event) {
        const modal = document.getElementById('logDetailsModal');
        if (event.target == modal) closeLogDetails();
    }
</script>
@endsection

