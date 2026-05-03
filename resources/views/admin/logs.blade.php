@extends('layouts.admin')

@section('title', 'System Logs')

@section('content')
<div class="animate-slide-up">
    <div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="font-size: 2.25rem; font-weight: 900; letter-spacing: -0.04em; color: var(--text-main); margin-bottom: 0.25rem;">System <span style="color: var(--primary);">Logs</span></h2>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Administrative audit trail and system event monitoring.</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button type="button" onclick="confirmBulkDelete()" style="background: #fef2f2; border: 1px solid #fecdd3; padding: 0.75rem 1.5rem; border-radius: 12px; color: #ef4444; font-weight: 800; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='#ef4444'; this.style.color='white'; this.style.boxShadow='0 4px 12px rgba(239, 68, 68, 0.2)'" onmouseout="this.style.background='#fef2f2'; this.style.color='#ef4444'; this.style.boxShadow='none'">
                <i data-lucide="trash-2" style="width: 18px;"></i>
                Delete Selected
            </button>
            <button onclick="window.print()" class="btn-secondary" style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 0.75rem 1.5rem; border-radius: 12px; color: var(--text-main); font-weight: 700; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; transition: var(--transition);">
                <i data-lucide="printer" style="width: 18px;"></i>
                Export Logs
            </button>
        </div>
    </div>

    <!-- Premium Filter Console -->
    <div class="glass-card" style="padding: 2rem; margin-bottom: 2rem; border-radius: 24px; background: linear-gradient(145deg, #ffffff, #f8fafc); border: 1px solid rgba(79, 70, 229, 0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
            <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(79, 70, 229, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                <i data-lucide="filter" style="width: 18px;"></i>
            </div>
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0; letter-spacing: -0.01em;">Audit Trail Filters</h3>
        </div>
        <form action="{{ route('admin.logs') }}" method="GET" style="display: flex; gap: 1.5rem; align-items: flex-end; flex-wrap: wrap;">
            @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">Severity Level</label>
                <select name="severity" style="width: 100%; background: white; border: 1px solid #e2e8f0; padding: 0.85rem 1rem; border-radius: 12px; color: var(--text-main); font-weight: 600; outline: none; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: all 0.3s;" onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 3px rgba(79,70,229,0.1)'" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)'">
                    <option value="">All Severities</option>
                    <option value="info" {{ request('severity') == 'info' ? 'selected' : '' }}>Information</option>
                    <option value="warning" {{ request('severity') == 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="danger" {{ request('severity') == 'danger' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">Event Category</label>
                <select name="event_type" style="width: 100%; background: white; border: 1px solid #e2e8f0; padding: 0.85rem 1rem; border-radius: 12px; color: var(--text-main); font-weight: 600; outline: none; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: all 0.3s;" onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 3px rgba(79,70,229,0.1)'" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)'">
                    <option value="">All Categories</option>
                    <option value="SECURITY" {{ request('event_type') == 'SECURITY' ? 'selected' : '' }}>Security</option>
                    <option value="INVENTORY" {{ request('event_type') == 'INVENTORY' ? 'selected' : '' }}>Inventory</option>
                    <option value="AUTH" {{ request('event_type') == 'AUTH' ? 'selected' : '' }}>Authentication</option>
                    <option value="SYSTEM" {{ request('event_type') == 'SYSTEM' ? 'selected' : '' }}>System</option>
                </select>
            </div>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <button type="submit" class="btn-primary" style="padding: 0.85rem 2rem; border-radius: 12px; border: none; background: var(--primary); color: white; font-weight: 800; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(79,70,229,0.2);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 15px rgba(79,70,229,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(79,70,229,0.2)'">
                    <i data-lucide="search" style="width: 16px; display: inline-block; vertical-align: text-bottom; margin-right: 4px;"></i> Scan Logs
                </button>
                @if(request()->hasAny(['severity', 'event_type']) && (request('severity') != '' || request('event_type') != ''))
                <a href="{{ route('admin.logs') }}" style="padding: 0.85rem 1.5rem; color: #ef4444; background: #fef2f2; border-radius: 12px; text-decoration: none; font-size: 0.85rem; font-weight: 800; transition: all 0.3s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="glass-card" style="padding: 0; overflow: hidden; border-radius: 24px;">
        <form id="deleteLogsForm" method="POST" action="{{ route('admin.logs.delete_multiple') }}">
        @csrf
        <div style="max-height: 65vh; overflow-y: auto; overflow-x: hidden;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0; text-align: left;">
                <thead style="position: sticky; top: 0; z-index: 20; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 1px 0 var(--border-color);">
                    <tr>
                    <th style="padding: 1.25rem 2rem; width: 50px; text-align: center;">
                        <input type="checkbox" id="selectAllLogs" style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--primary);">
                    </th>
                    <th style="padding: 1.25rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Timestamp</th>
                    <th style="padding: 1.25rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Event</th>
                    <th style="padding: 1.25rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">User</th>
                    <th style="padding: 1.25rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Description</th>
                    <th style="padding: 1.25rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Severity</th>
                    <th style="padding: 1.25rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr style="border-bottom: 1px solid var(--border-color); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: default; position: relative; z-index: 1;" onmouseover="this.style.background='#f8fafc'; this.style.transform='scale(1.005)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.03)'; this.style.zIndex='10'" onmouseout="this.style.background='transparent'; this.style.transform='scale(1)'; this.style.boxShadow='none'; this.style.zIndex='1'">
                    <td style="padding: 1.25rem 2rem; text-align: center;">
                        <input type="checkbox" name="log_ids[]" value="{{ $log->id }}" class="log-checkbox" style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--primary);">
                    </td>
                    <td style="padding: 1.25rem 2rem;">
                        <div style="font-weight: 800; color: var(--text-heading); font-size: 0.85rem;">{{ $log->created_at->format('M d, Y') }}</div>
                        <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-top: 2px;">{{ $log->created_at->format('H:i:s') }}</div>
                    </td>
                    <td style="padding: 1.25rem 2rem;">
                        @php
                            $eventIcons = [
                                'SECURITY' => 'shield-alert',
                                'INVENTORY' => 'package',
                                'AUTH' => 'key',
                                'SYSTEM' => 'server'
                            ];
                            $icon = $eventIcons[$log->event_type] ?? 'activity';
                        @endphp
                        <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.65rem; font-weight: 900; background: white; border: 1px solid #e2e8f0; color: #475569; padding: 4px 10px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <i data-lucide="{{ $icon }}" style="width: 12px; height: 12px; color: var(--primary);"></i>
                            {{ $log->event_type }}
                        </span>
                        <div style="font-size: 0.85rem; font-weight: 800; color: var(--text-heading); margin-top: 6px; letter-spacing: -0.01em;">{{ str_replace('_', ' ', $log->action) }}</div>
                    </td>
                    <td style="padding: 1.25rem 2rem;">
                        @if($log->user)
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            @if($log->user->avatar)
                                <img src="{{ asset('storage/' . $log->user->avatar) }}" alt="{{ $log->user->name }}" style="width: 32px; height: 32px; border-radius: 8px; object-fit: cover; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                            @else
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.75rem;">
                                    {{ substr($log->user->name, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <div style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $log->user->name }}</div>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $log->ip_address }}</div>
                            </div>
                        </div>
                        @else
                        <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600;">System Auto</div>
                        @endif
                    </td>
                    <td style="padding: 1.25rem 2rem; max-width: 400px;">
                        <p style="font-size: 0.85rem; color: var(--text-main); line-height: 1.5; margin: 0;">{{ $log->description }}</p>
                    </td>
                    <td style="padding: 1.25rem 2rem;">
                        @php
                            $colors = [
                                'info' => ['bg' => '#f0f9ff', 'text' => '#0369a1'],
                                'warning' => ['bg' => '#fffbeb', 'text' => '#b45309'],
                                'danger' => ['bg' => '#fef2f2', 'text' => '#b91c1c']
                            ];
                            $c = $colors[$log->severity] ?? $colors['info'];
                        @endphp
                        <span style="background: {{ $c['bg'] }}; color: {{ $c['text'] }}; padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">
                            {{ $log->severity }}
                        </span>
                    </td>
                    <td style="padding: 1.25rem 2rem; text-align: right;">
                        <div style="display: flex; gap: 0.5rem; justify-content: flex-end; align-items: center;">
                            @if($log->metadata)
                            <button type="button" 
                                data-metadata="{{ json_encode($log->metadata) }}" 
                                data-event="{{ $log->event_type }}" 
                                data-action="{{ $log->action }}" 
                                data-user-name="{{ $log->user ? $log->user->name : 'System Auto' }}"
                                data-user-avatar="{{ $log->user && $log->user->avatar ? asset('storage/' . $log->user->avatar) : '' }}"
                                onclick="viewLogDetails(this)" 
                                class="btn-details">
                                <i data-lucide="info"></i> Details
                            </button>
                            @else
                            <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; padding: 0.4rem 0.85rem;">No Details</span>
                            @endif
                            <button type="button" onclick="singleDelete('{{ $log->id }}')" style="background: rgba(239, 68, 68, 0.05); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); padding: 0.4rem 0.65rem; border-radius: 10px; font-weight: 800; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='#ef4444'; this.style.color='white'" onmouseout="this.style.background='rgba(239, 68, 68, 0.05)'; this.style.color='#ef4444'">
                                <i data-lucide="trash-2" style="width: 14px;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding: 6rem 2rem; text-align: center; color: var(--text-muted); background: #f8fafc;">
                        <div style="width: 80px; height: 80px; background: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid rgba(79,70,229,0.1);">
                            <i data-lucide="shield-check" style="width: 40px; height: 40px; color: var(--primary); opacity: 0.9;"></i>
                        </div>
                        <h4 style="font-weight: 900; color: var(--text-heading); font-size: 1.25rem; margin-bottom: 0.5rem; letter-spacing: -0.02em;">Audit Category is Clean</h4>
                        <p style="font-size: 0.95rem; max-width: 400px; margin: 0 auto; line-height: 1.5;">No system events match your current filter criteria. The system is operating securely within normal parameters.</p>
                        @if(request()->hasAny(['severity', 'event_type']))
                        <a href="{{ route('admin.logs') }}" style="display: inline-flex; align-items: center; gap: 8px; margin-top: 1.5rem; background: white; padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 800; text-decoration: none; color: var(--primary); border: 1px solid rgba(79,70,229,0.2); box-shadow: 0 4px 10px rgba(0,0,0,0.02); transition: all 0.3s;" onmouseover="this.style.background='var(--primary)'; this.style.color='white'" onmouseout="this.style.background='white'; this.style.color='var(--primary)'">
                            <i data-lucide="rotate-ccw" style="width: 16px;"></i> Clear All Filters
                        </a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        </form>
        <div style="padding: 1.5rem 2rem; background: rgba(0,0,0,0.015); border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap;">
                <div>Showing <span style="font-weight: 800; color: var(--text-main);">{{ $logs->firstItem() ?? 0 }}</span> to <span style="font-weight: 800; color: var(--text-main);">{{ $logs->lastItem() ?? 0 }}</span> of <span style="font-weight: 800; color: var(--text-main);">{{ $logs->total() }}</span> events</div>
                
                <form action="{{ route('admin.logs') }}" method="GET" style="display: flex; align-items: center; gap: 0.75rem; border-left: 2px solid #e2e8f0; padding-left: 1.25rem;">
                    @if(request('severity')) <input type="hidden" name="severity" value="{{ request('severity') }}"> @endif
                    @if(request('event_type')) <input type="hidden" name="event_type" value="{{ request('event_type') }}"> @endif
                    
                    <span style="font-size: 0.7rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em; color: var(--text-muted);">Rows per page:</span>
                    <select name="per_page" onchange="this.form.submit()" style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.35rem 1rem 0.35rem 0.5rem; font-size: 0.8rem; font-weight: 800; color: var(--primary); outline: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: all 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#e2e8f0'">
                        <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </form>
            </div>
            <div class="custom-pagination">
                @if ($logs->onFirstPage())
                    <span class="page-btn disabled">Previous</span>
                @else
                    <a href="{{ $logs->appends(request()->query())->previousPageUrl() }}" class="page-btn">Previous</a>
                @endif

                @if ($logs->hasMorePages())
                    <a href="{{ $logs->appends(request()->query())->nextPageUrl() }}" class="page-btn">Next</a>
                @else
                    <span class="page-btn disabled">Next</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Bottom Sheet CSS -->
<style>
    .bottom-sheet-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(4px);
        z-index: 1000;
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .bottom-sheet-overlay.active {
        opacity: 1;
    }
    
    .bottom-sheet {
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%) translateY(100%);
        width: 95%;
        max-width: 1100px;
        background: #ffffff;
        border-radius: 32px 32px 0 0;
        box-shadow: 0 -20px 80px rgba(0, 0, 0, 0.15), 0 -4px 20px rgba(0, 0, 0, 0.05);
        z-index: 1001;
        transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        max-height: 90vh;
    }
    .bottom-sheet.active {
        transform: translateX(-50%) translateY(0);
    }

    .bottom-sheet-header {
        padding: 2.5rem 3rem 1.5rem 3rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #ffffff;
        border-radius: 32px 32px 0 0;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .bottom-sheet-body {
        padding: 2rem 3rem 4rem 3rem;
        overflow-y: auto;
    }

    /* Beautiful Details Formatting */
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .detail-card {
        background: var(--bg-card);
        padding: 1rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
    }
    .detail-label {
        font-size: 0.7rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 0.4rem;
        letter-spacing: 0.5px;
    }
    .detail-value {
        font-size: 1rem;
        font-weight: 800;
        color: var(--text-main);
    }
    .items-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--bg-card);
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
    }
    .items-table th, .items-table td {
        padding: 1rem 1.25rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    .items-table th {
        background: rgba(0,0,0,0.015);
        font-size: 0.75rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
    }
    .items-table td {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-main);
    }

    /* Premium Details Button */
    .btn-details {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: white;
        color: var(--primary);
        border: 1px solid rgba(79, 70, 229, 0.2);
        padding: 0.4rem 0.85rem;
        border-radius: 10px;
        font-weight: 800;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(79, 70, 229, 0.05);
    }
    .btn-details:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(79, 70, 229, 0.25);
    }
    .btn-details i {
        width: 14px;
        transition: transform 0.3s ease;
    }
    .btn-details:hover i {
        transform: scale(1.2);
    }

    /* Premium Custom Pagination */
    .custom-pagination {
        display: flex;
        gap: 8px;
    }
    .page-btn {
        padding: 0.5rem 1rem;
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        color: var(--primary);
        font-weight: 800;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .page-btn:hover:not(.disabled) {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }
    .page-btn.disabled {
        background: #f8fafc;
        color: #94a3b8;
        border-color: #e2e8f0;
        cursor: not-allowed;
        box-shadow: none;
    }
</style>

<!-- Bottom Sheet -->
<div id="logBottomSheetOverlay" class="bottom-sheet-overlay" onclick="closeLogModal()"></div>
<div id="logBottomSheet" class="bottom-sheet">
    <div style="position: absolute; top: 12px; left: 50%; transform: translateX(-50%); width: 60px; height: 6px; background: rgba(0,0,0,0.1); border-radius: 10px; z-index: 20;"></div>
    <div class="bottom-sheet-header">
        <div>
            <h3 id="modalEventTitle" style="font-size: 1.85rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.5rem; letter-spacing: -0.02em;">Event Details</h3>
            <p id="modalEventAction" style="font-size: 0.8rem; background: var(--primary); color: white; padding: 4px 12px; border-radius: 8px; font-weight: 800; display: inline-block; letter-spacing: 0.05em; box-shadow: 0 4px 10px rgba(79,70,229,0.2);">ACTION</p>
        </div>
        <button onclick="closeLogModal()" style="background: rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.05); width: 44px; height: 44px; border-radius: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--text-muted); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);" onmouseover="this.style.background='#fef2f2'; this.style.color='#ef4444'; this.style.borderColor='#fecdd3'" onmouseout="this.style.background='rgba(0,0,0,0.04)'; this.style.color='var(--text-muted)'; this.style.borderColor='rgba(0,0,0,0.05)'">
            <i data-lucide="x" style="width: 20px;"></i>
        </button>
    </div>
    <div class="bottom-sheet-body" id="modalMetadataContent">
        <!-- Dynamic Content Here -->
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Select All Logic
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAllLogs');
        const checkboxes = document.querySelectorAll('.log-checkbox');
        
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (!this.checked) selectAll.checked = false;
                if (document.querySelectorAll('.log-checkbox:checked').length === checkboxes.length && checkboxes.length > 0) {
                    selectAll.checked = true;
                }
            });
        });
    });

    function confirmBulkDelete() {
        const checked = document.querySelectorAll('.log-checkbox:checked');
        if (checked.length === 0) {
            alert('Please select at least one log to delete.');
            return;
        }
        if (confirm(`Are you sure you want to permanently delete ${checked.length} system logs?\nThis action will be recorded in the audit ledger.`)) {
            document.getElementById('deleteLogsForm').submit();
        }
    }

    function singleDelete(id) {
        if (confirm('Are you sure you want to permanently delete this log?')) {
            document.querySelectorAll('.log-checkbox').forEach(cb => cb.checked = false);
            const targetCb = document.querySelector(`.log-checkbox[value="${id}"]`);
            if (targetCb) {
                targetCb.checked = true;
                document.getElementById('deleteLogsForm').submit();
            }
        }
    }

    function viewLogDetails(btn) {
        const metadataStr = btn.getAttribute('data-metadata');
        const eventType = btn.getAttribute('data-event');
        const action = btn.getAttribute('data-action');
        const userName = btn.getAttribute('data-user-name');
        const userAvatar = btn.getAttribute('data-user-avatar');

        document.getElementById('modalEventTitle').textContent = eventType + ' Event Details';
        document.getElementById('modalEventAction').textContent = action;
        
        const contentDiv = document.getElementById('modalMetadataContent');
        contentDiv.innerHTML = '';

        try {
            const dataObj = typeof metadataStr === 'string' ? JSON.parse(metadataStr) : metadataStr;
            
            // Render Personnel Card at the top
            let html = `
                <div class="detail-card" style="display: flex; align-items: center; gap: 1.25rem; margin-bottom: 2rem; background: rgba(0,0,0,0.015); border: 1px solid var(--border-color); box-shadow: none;">
                    ${userAvatar ? `<img src="${userAvatar}" style="width: 48px; height: 48px; border-radius: 14px; object-fit: cover; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">` : `<div style="width: 48px; height: 48px; border-radius: 14px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 900; box-shadow: 0 4px 10px rgba(79,70,229,0.2);">${userName.charAt(0)}</div>`}
                    <div>
                        <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 2px; letter-spacing: 0.05em;">Action Initiated By Personnel</div>
                        <div style="font-size: 1.15rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.02em;">${userName}</div>
                    </div>
                </div>
                <div class="detail-grid">
            `;
            
            if (action === 'ADD_INVENTORY') {
                html += `
                    <div class="detail-card">
                        <div class="detail-label">Ledge Category</div>
                        <div class="detail-value">Category ${dataObj.ledge_category || 'N/A'}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Acquisition Type</div>
                        <div class="detail-value">${dataObj.acquisition_type || 'N/A'}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Source Name</div>
                        <div class="detail-value">${dataObj.source_name || 'N/A'}</div>
                    </div>
                </div>`;
                
                if (dataObj.items_added && Array.isArray(dataObj.items_added)) {
                    html += '<h4 style="font-size: 0.85rem; font-weight: 800; color: var(--text-muted); margin-bottom: 1rem; text-transform: uppercase;">Items Processed in this Event</h4>';
                    html += '<table class="items-table"><thead><tr><th>Description</th><th>Unit</th><th>Stock Bal.</th><th>Rec. Qty</th><th>Variance</th></tr></thead><tbody>';
                    dataObj.items_added.forEach(item => {
                        const variance = parseFloat(item.variance || 0);
                        const varColor = variance > 0 ? '#10b981' : (variance < 0 ? '#ef4444' : 'inherit');
                        const varPrefix = variance > 0 ? '+' : '';
                        
                        html += `<tr>
                            <td style="font-weight: 800; color: var(--primary);">${item.description}</td>
                            <td>${item.unit || '-'}</td>
                            <td>${item.stock_balance || '-'}</td>
                            <td>${item.qty || '-'}</td>
                            <td style="color: ${varColor}; font-weight: 800;">${varPrefix}${variance}</td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                }
            } 
            else if (action === 'ISSUE_ITEM') {
                html += `
                    <div class="detail-card">
                        <div class="detail-label">Beneficiary</div>
                        <div class="detail-value">${dataObj.beneficiary || 'N/A'}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Authorizing Officer</div>
                        <div class="detail-value">${dataObj.authority || 'N/A'}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Issuance Type</div>
                        <div class="detail-value">${dataObj.issuance_type || 'N/A'}</div>
                    </div>
                </div>`;
                
                if (dataObj.items_issued && Array.isArray(dataObj.items_issued)) {
                    html += '<h4 style="font-size: 0.85rem; font-weight: 800; color: var(--text-muted); margin-bottom: 1rem; text-transform: uppercase;">Items Issued</h4>';
                    html += '<table class="items-table"><thead><tr><th>Category</th><th>Description</th><th>Quantity Issued</th></tr></thead><tbody>';
                    dataObj.items_issued.forEach(item => {
                        html += `<tr>
                            <td><span style="background: rgba(99,102,241,0.1); color: var(--primary); padding: 2px 8px; border-radius: 6px; font-weight: 700; font-size: 0.75rem;">${item.category || '-'}</span></td>
                            <td style="font-weight: 800; color: var(--text-main);">${item.description}</td>
                            <td style="font-weight: 900; color: var(--primary);">${item.qty || '-'}</td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                }
            }
            else if (action === 'RETURN_ITEM') {
                html += `
                    <div class="detail-card">
                        <div class="detail-label">Beneficiary Returning</div>
                        <div class="detail-value">${dataObj.beneficiary || 'N/A'}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Original Issue Date</div>
                        <div class="detail-value">${dataObj.original_issuance_date ? new Date(dataObj.original_issuance_date).toLocaleDateString() : 'N/A'}</div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-label">Return Date</div>
                        <div class="detail-value">${dataObj.return_date ? new Date(dataObj.return_date).toLocaleDateString() : 'N/A'}</div>
                    </div>
                </div>`;
                
                html += '<h4 style="font-size: 0.85rem; font-weight: 800; color: var(--text-muted); margin-bottom: 1rem; text-transform: uppercase;">Return Processing Log</h4>';
                html += '<table class="items-table"><thead><tr><th>Item Description</th><th>Returned Qty</th><th>Condition / Remarks</th></tr></thead><tbody>';
                html += `<tr>
                    <td style="font-weight: 800; color: var(--primary);">${dataObj.item_description || '-'}</td>
                    <td style="font-weight: 900; font-size: 1.1rem;">${dataObj.return_qty || '-'}</td>
                    <td style="font-style: italic;">${dataObj.remarks || '-'}</td>
                </tr>`;
                html += '</tbody></table>';
            }
            else if (action === 'UPDATE_BATCH') {
                html += `
                    <div class="detail-card">
                        <div class="detail-label">Batch ID</div>
                        <div class="detail-value">#${dataObj.batch_id || 'N/A'}</div>
                    </div>
                `;

                if (dataObj.batch_changes && Object.keys(dataObj.batch_changes).length > 0) {
                    html += `
                        <div class="detail-card" style="border-color: #f59e0b; background: rgba(245, 158, 11, 0.02);">
                            <div class="detail-label" style="color: #d97706;">Batch Header Changes</div>
                            <div style="font-size: 0.85rem;">
                                ${Object.entries(dataObj.batch_changes).map(([field, val]) => `
                                    <div style="margin-bottom: 4px;">
                                        <span style="font-weight: 800; text-transform: capitalize;">${field.replace('_', ' ')}:</span> 
                                        <span style="color: var(--primary); font-weight: 900;">${val}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }
                html += `</div>`; // close detail-grid

                if (dataObj.item_changes && Object.keys(dataObj.item_changes).length > 0) {
                    html += '<h4 style="font-size: 0.85rem; font-weight: 800; color: var(--text-muted); margin-bottom: 1rem; text-transform: uppercase; display: flex; align-items: center; gap: 8px;"><i data-lucide="edit-3" style="width: 14px; color: #f59e0b;"></i> Granular Item Revisions</h4>';
                    html += '<table class="items-table"><thead><tr><th>Item ID</th><th>Field</th><th>Original State</th><th>New State</th></tr></thead><tbody>';
                    
                    Object.entries(dataObj.item_changes).forEach(([itemId, changes]) => {
                        Object.entries(changes).forEach(([field, vals], fIdx) => {
                            html += `<tr>
                                ${fIdx === 0 ? `<td rowspan="${Object.keys(changes).length}" style="background: rgba(0,0,0,0.02); font-weight: 900; color: var(--text-muted); text-align: center; border-right: 1px solid var(--border-color);">#${itemId}</td>` : ''}
                                <td style="text-transform: capitalize; font-weight: 700;">${field.replace('_', ' ')}</td>
                                <td style="color: #ef4444; font-weight: 700; text-decoration: line-through;">${vals.old}</td>
                                <td style="color: #10b981; font-weight: 900; background: rgba(16, 185, 129, 0.05);">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i data-lucide="arrow-right" style="width: 12px;"></i>
                                        ${vals.new}
                                    </div>
                                </td>
                            </tr>`;
                        });
                    });
                    html += '</tbody></table>';
                } else {
                    html += '<div style="padding: 2rem; text-align: center; color: var(--text-muted); font-style: italic;">No specific item attributes were modified.</div>';
                }
            }
            else {
                html += `</div>`; // close detail-grid
                html += `<pre style="background: var(--bg-card); padding: 1rem; border-radius: 12px; border: 1px solid var(--border-color); color: var(--text-main); font-size: 0.8rem; overflow-x: auto; white-space: pre-wrap; font-family: monospace;">${JSON.stringify(dataObj, null, 4)}</pre>`;
            }

            contentDiv.innerHTML = html;
        } catch(e) {
            contentDiv.innerHTML = `<pre style="background: rgba(239, 68, 68, 0.1); padding: 1rem; border-radius: 12px; border: 1px solid rgba(239, 68, 68, 0.2); color: var(--danger); font-size: 0.8rem; overflow-x: auto; white-space: pre-wrap; font-family: monospace;">Error parsing details data:\n\n${metadataStr}</pre>`;
        }
        
        // Show Bottom Sheet safely
        const overlay = document.getElementById('logBottomSheetOverlay');
        const sheet = document.getElementById('logBottomSheet');
        
        overlay.style.display = 'block';
        // Force browser to acknowledge the display block before animating opacity
        void overlay.offsetWidth; 
        
        overlay.classList.add('active');
        sheet.classList.add('active');
        
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function closeLogModal() {
        const overlay = document.getElementById('logBottomSheetOverlay');
        const sheet = document.getElementById('logBottomSheet');
        
        overlay.classList.remove('active');
        sheet.classList.remove('active');
        
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 300);
    }
</script>
@endpush
