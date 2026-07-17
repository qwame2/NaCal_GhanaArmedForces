@extends('layouts.admin')

@section('title', 'System Archive')

@section('content')
<div class="animate-slide-up">
    <div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="font-size: 2.25rem; font-weight: 900; letter-spacing: -0.04em; color: var(--text-main); margin-bottom: 0.25rem;">System <span style="color: var(--primary);">Archive</span></h2>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Access archived communications and system activity records.</p>
        </div>

        <div style="display: flex; gap: 1rem; background: #fff; padding: 0.5rem; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 4px 12px rgba(0,0,0,0.02); flex-wrap: wrap;">
            <a href="{{ route('admin.archive', ['type' => 'messages']) }}"
               style="padding: 0.75rem 1.5rem; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 0.9rem; transition: all 0.3s; {{ $type === 'messages' ? 'background: var(--primary); color: white; box-shadow: 0 8px 20px rgba(22, 163, 74, 0.2);' : 'color: var(--text-muted);' }}">
               <i data-lucide="message-square" style="width: 18px; display: inline-block; vertical-align: middle; margin-right: 8px;"></i> Archived Messages
            </a>
            <a href="{{ route('admin.archive', ['type' => 'logs']) }}"
               style="padding: 0.75rem 1.5rem; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 0.9rem; transition: all 0.3s; {{ $type === 'logs' ? 'background: var(--primary); color: white; box-shadow: 0 8px 20px rgba(22, 163, 74, 0.2);' : 'color: var(--text-muted);' }}">
               <i data-lucide="activity" style="width: 18px; display: inline-block; vertical-align: middle; margin-right: 8px;"></i> Archived Logs
            </a>
            <a href="{{ route('admin.archive', ['type' => 'disbursements']) }}"
               style="padding: 0.75rem 1.5rem; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 0.9rem; transition: all 0.3s; {{ $type === 'disbursements' ? 'background: var(--primary); color: white; box-shadow: 0 8px 20px rgba(22, 163, 74, 0.2);' : 'color: var(--text-muted);' }}">
               <i data-lucide="package-2" style="width: 18px; display: inline-block; vertical-align: middle; margin-right: 8px;"></i> Archived Issuance
            </a>
        </div>
    </div>

    <!-- Strategic Command Strip (Redesigned Unified Filter) -->
    <div style="margin-bottom: 3.5rem; display: flex; justify-content: center;">
        <div style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(25px); padding: 0.75rem 1rem; border-radius: 100px; border: 1px solid rgba(255, 255, 255, 0.5); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08), inset 0 0 0 1px rgba(255,255,255,0.6); display: flex; align-items: center; gap: 0.5rem; width: fit-content; max-width: 95%;">
            <form action="{{ route('admin.archive') }}" method="GET" style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="hidden" name="type" value="{{ $type }}">

                <!-- Search Zone -->
                <div style="display: flex; align-items: center; background: #fff; padding: 0.6rem 1.25rem; border-radius: 100px; border: 1px solid #f1f5f9; box-shadow: 0 2px 5px rgba(0,0,0,0.02); min-width: 280px; transition: all 0.3s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='#f1f5f9'">
                    <i data-lucide="search" style="width: 16px; color: var(--primary); margin-right: 12px;"></i>
                    <input type="text" name="search" id="archiveSearchInput" value="{{ $search }}" placeholder="Search archive records..." oninput="window.debounceSearch()"
                           style="border: none; outline: none; background: transparent; font-weight: 800; color: var(--text-main); font-size: 0.85rem; width: 100%;" onfocus="this.parentElement.style.boxShadow='0 0 0 4px rgba(22, 163, 74, 0.1)'" onblur="this.parentElement.style.boxShadow='0 2px 5px rgba(0,0,0,0.02)'">
                </div>

                <div style="width: 1px; height: 24px; background: #e2e8f0; margin: 0 0.5rem;"></div>

                <!-- Date Range Bridge -->
                <div style="display: flex; align-items: center; gap: 8px; background: rgba(248, 250, 252, 0.8); padding: 0.4rem 1rem; border-radius: 100px; border: 1px solid #f1f5f9;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="calendar" style="width: 14px; color: #94a3b8;"></i>
                        <input type="date" name="start_date" value="{{ $startDate }}" onchange="this.form.submit()"
                               style="border: none; outline: none; background: transparent; font-weight: 900; color: var(--text-main); font-size: 0.8rem; cursor: pointer;">
                    </div>

                    <div style="color: #cbd5e1;"><i data-lucide="arrow-right" style="width: 12px;"></i></div>

                    <div style="display: flex; align-items: center; gap: 6px;">
                        <input type="date" name="end_date" value="{{ $endDate }}" onchange="this.form.submit()"
                               style="border: none; outline: none; background: transparent; font-weight: 900; color: var(--text-main); font-size: 0.8rem; cursor: pointer;">
                        <i data-lucide="calendar" style="width: 14px; color: #94a3b8;"></i>
                    </div>
                </div>

                <div style="width: 1px; height: 24px; background: #e2e8f0; margin: 0 0.5rem;"></div>

                <!-- Action Hub -->
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" style="background: var(--primary); color: white; border: none; width: 46px; height: 46px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; box-shadow: 0 8px 15px rgba(22, 163, 74, 0.2);" onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 12px 20px rgba(22, 163, 74, 0.3)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 8px 15px rgba(22, 163, 74, 0.2)'">
                        <i data-lucide="arrow-right-circle" style="width: 22px;"></i>
                    </button>

                    @if($startDate || $endDate || $search)
                    <a href="{{ route('admin.archive', ['type' => $type]) }}" style="background: #fff; color: #ef4444; border: 1px solid #fee2e2; width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s;" onmouseover="this.style.background='#fef2f2'; this.style.transform='rotate(90deg)'" onmouseout="this.style.background='#fff'; this.style.transform='rotate(0deg)'">
                        <i data-lucide="refresh-cw" style="width: 18px;"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div style="background: linear-gradient(145deg, #f8fafc 0%, #ffffff 100%); padding: 2.5rem; border-radius: 40px; border: 1px solid rgba(0,0,0,0.03); box-shadow: inset 0 2px 10px rgba(0,0,0,0.02), 0 40px 80px rgba(0,0,0,0.03); margin-bottom: 3rem;">
        @if($type === 'messages')
    <div class="glass-card" style="padding: 0; overflow: hidden; border-radius: 28px; box-shadow: 0 25px 70px rgba(0,0,0,0.07); border: 1px solid rgba(22, 163, 74, 0.1);">
        <div style="max-height: 72vh; overflow: auto;" class="custom-scrollbar">
            <table style="width: 100%; min-width: 1100px; border-collapse: separate; border-spacing: 0; text-align: left;">
                <thead style="position: sticky; top: 0; z-index: 30; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border-bottom: 1px solid var(--border-color);">
                    <tr>
                        <th style="padding: 1.75rem 2rem; font-size: 0.75rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; white-space: nowrap;">Sender / Receiver</th>
                        <th style="padding: 1.75rem 2rem; font-size: 0.75rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; white-space: nowrap;">Message Content</th>
                        <th style="padding: 1.75rem 2rem; font-size: 0.75rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; white-space: nowrap;">Message Type</th>
                        <th style="padding: 1.75rem 2rem; font-size: 0.75rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; white-space: nowrap;">Archived Date</th>
                        <th style="padding: 1.75rem 2rem; font-size: 0.75rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; text-align: right; white-space: nowrap;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $msg)
                    <tr class="archive-row" style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-bottom: 1px solid rgba(0,0,0,0.03);">
                        <td style="padding: 1.75rem 2rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <!-- Sender -->
                                <div style="position: relative;" title="Sender: {{ $msg->sender->name ?? 'System' }}">
                                    @if($msg->sender && $msg->sender->avatar)
                                        <img src="{{ asset('storage/' . $msg->sender->avatar) }}" style="width: 42px; height: 42px; border-radius: 14px; object-fit: cover; border: 2px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                    @else
                                        <div style="width: 42px; height: 42px; border-radius: 14px; background: linear-gradient(135deg, var(--primary) 0%, #312e81 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: 900; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.2);">
                                            {{ $msg->sender ? substr($msg->sender->name, 0, 1) : 'S' }}
                                        </div>
                                    @endif
                                </div>

                                <div style="color: #94a3b8;">
                                    <i data-lucide="arrow-right" style="width: 14px;"></i>
                                </div>

                                <!-- Receiver -->
                                <div style="position: relative;" title="Receiver: {{ $msg->receiver->name ?? 'System' }}">
                                    @if($msg->receiver && $msg->receiver->avatar)
                                        <img src="{{ asset('storage/' . $msg->receiver->avatar) }}" style="width: 42px; height: 42px; border-radius: 14px; object-fit: cover; border: 2px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                    @else
                                        <div style="width: 42px; height: 42px; border-radius: 14px; background: #f8fafc; color: #64748b; border: 2px solid #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: 900;">
                                            {{ $msg->receiver ? substr($msg->receiver->name, 0, 1) : 'R' }}
                                        </div>
                                    @endif
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 2px; margin-left: 0.5rem;">
                                    <span style="font-weight: 900; color: var(--text-main); font-size: 0.85rem; letter-spacing: -0.01em;">{{ $msg->sender->name ?? 'System' }}</span>
                                    <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700;">to {{ $msg->receiver->name ?? 'Global' }}</span>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 1.75rem 2rem; max-width: 400px;">
                            <div class="message-preview-box" style="background: rgba(248, 250, 252, 0.5); padding: 1rem; border-radius: 16px; border: 1px solid #f1f5f9;">
                                <div style="font-size: 0.85rem; color: #334155; line-height: 1.6; font-weight: 500; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;">
                                    {!! strip_tags($msg->message, '<b><strong><i><em><br>') !!}
                                </div>
                                @if($msg->attachment)
                                <div style="margin-top: 10px; display: flex; align-items: center; gap: 8px; color: var(--primary); font-size: 0.75rem; font-weight: 800; background: white; padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(22, 163, 74, 0.1); width: fit-content;">
                                    <i data-lucide="paperclip" style="width: 13px;"></i>
                                    {{ $msg->attachment_name ?: 'Secured Asset' }}
                                </div>
                                @endif
                            </div>
                        </td>
                        <td style="padding: 1.75rem 2rem;">
                            @php
                                $isSystem = $msg->is_automated;
                                $msgLower = strtolower($msg->message);
                                $context = 'General Communication';
                                $icon = 'message-square';
                                $color = '#64748b';
                                $bg = '#f8fafc';

                                if ($isSystem) {
                                    $context = 'System Protocol';
                                    $icon = 'cpu';
                                    $color = '#0369a1';
                                    $bg = '#e0f2fe';

                                    if (str_contains($msgLower, 'sra')) {
                                        $context = 'Authorization';
                                        $icon = 'shield-check';
                                        $color = '#15803d';
                                        $bg = '#f0fdf4';
                                    } elseif (str_contains($msgLower, 'remainder')) {
                                        $context = 'Remainder Log';
                                        $icon = 'package-plus';
                                        $color = '#b45309';
                                        $bg = '#fffbe3';
                                    } elseif (str_contains($msgLower, 'recovery')) {
                                        $context = 'Asset Recovery';
                                        $icon = 'refresh-cw';
                                        $color = '#15803d';
                                        $bg = '#f5f3ff';
                                    }
                                }
                            @endphp
                            <div style="display: flex; align-items: center; gap: 10px; background: {{ $bg }}; color: {{ $color }}; padding: 8px 14px; border-radius: 12px; width: fit-content; border: 1px solid {{ $color }}20;">
                                <i data-lucide="{{ $icon }}" style="width: 15px;"></i>
                                <span style="font-size: 0.75rem; font-weight: 850; text-transform: uppercase; letter-spacing: 0.02em;">{{ $context }}</span>
                            </div>
                        </td>
                        <td style="padding: 1.75rem 2rem;">
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #94a3b8;"></div>
                                    <span style="font-weight: 900; color: var(--text-main); font-size: 0.85rem;">{{ $msg->updated_at->format('d/m/y') }}</span>
                                </div>
                                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; padding-left: 16px;">
                                    <i data-lucide="clock" style="width: 11px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i>
                                    {{ $msg->updated_at->format('H:i:s') }}
                                </span>
                            </div>
                        </td>
                        <td style="padding: 1.75rem 2rem; text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <button type="button"
                                        onclick="showArchivedMessageDetail(`{!! addslashes($msg->message) !!}`, '{{ $msg->sender->name ?? 'System' }}', '{{ $msg->updated_at->format('d/m/y H:i') }}', '{{ $context }}')"
                                        style="background: white; color: var(--text-main); border: 1.5px solid #e2e8f0; padding: 0.6rem 1rem; border-radius: 12px; font-weight: 800; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; font-size: 0.8rem;"
                                        onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#f8fafc'"
                                        onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='white'">
                                    <i data-lucide="eye" style="width: 15px;"></i> View
                                </button>
                                <form action="{{ route('admin.archive.restore.message', $msg->id) }}" method="POST" style="display: inline-block;">
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
                        <td colspan="5" style="padding: 10rem 2rem; text-align: center; color: var(--text-muted);">
                            <div style="width: 110px; height: 110px; background: #f8fafc; border-radius: 35px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2.5rem auto; box-shadow: inset 0 2px 15px rgba(0,0,0,0.03); border: 1px solid #f1f5f9;">
                                <i data-lucide="inbox" style="width: 54px; height: 54px; color: var(--primary); opacity: 0.25;"></i>
                            </div>
                            <h4 style="font-weight: 950; color: var(--text-main); font-size: 1.75rem; margin-bottom: 0.75rem; letter-spacing: -0.03em;">No Messages Archived</h4>
                            <p style="font-weight: 600; font-size: 1.1rem; color: #94a3b8; max-width: 400px; margin: 0 auto;">There are currently no archived messages in the system.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @elseif($type === 'logs')
    <div class="glass-card" style="padding: 0; overflow: hidden; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.05); border: 1px solid rgba(22, 163, 74, 0.08);">
        <div style="max-height: 70vh; overflow: auto;" class="custom-scrollbar">
            <table style="width: 100%; min-width: 1100px; border-collapse: separate; border-spacing: 0; text-align: left;">
                <thead style="position: sticky; top: 0; z-index: 20; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(15px); box-shadow: 0 1px 0 var(--border-color);">
                    <tr>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Activity Date</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Staff Member</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Activity Details</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Severity</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right; white-space: nowrap;">Actions</th>
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
                                    'warning', 'medium' => ['#ecfdf5', '#10b981', 'WARNING'],
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
                            <h4 style="font-weight: 950; color: var(--text-main); font-size: 1.5rem; margin-bottom: 0.5rem; letter-spacing: -0.02em;">No Logs Archived</h4>
                            <p style="font-weight: 500; font-size: 1.1rem;">There are currently no archived activity logs in the system.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @elseif($type === 'disbursements')
    <div class="glass-card" style="padding: 0; overflow: hidden; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.05); border: 1px solid rgba(22, 163, 74, 0.08);">
        <div style="max-height: 70vh; overflow: auto;" class="custom-scrollbar">
            <table style="width: 100%; min-width: 1100px; border-collapse: separate; border-spacing: 0; text-align: left;">
                <thead style="position: sticky; top: 0; z-index: 20; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(15px); box-shadow: 0 1px 0 var(--border-color);">
                    <tr>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Timeline</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Items</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Destination</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Qty Issued</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Approval</th>
                        <th style="padding: 1.5rem 2rem; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right; white-space: nowrap;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                    @php
                        $t = $item->created_at;
                        $dateStr = $t ? $t->format('d/m/y') : '';
                        $timeStr = $t ? $t->format('H:i') : '';

                        $statusBadge = '';
                        if ($item->quantity === 0 && $item->issuance_type === 'Temporary') {
                            $statusBadge = '<span class="status-badge" style="background: rgba(100, 116, 139, 0.1); color: var(--text-muted); font-size: 0.7rem; padding: 0.4rem 1.15rem; border-radius: 10px; font-weight: 900; letter-spacing: 0.05em; border: 1px dashed rgba(100, 116, 139, 0.3);">RETURNED</span>';
                        } else {
                            $statusColor = $item->issuance_type === 'Temporary' ? '#15803d' : '#10b981';
                            $statusBg = $item->issuance_type === 'Temporary' ? 'rgba(234,88,12,0.1)' : 'rgba(16,185,129,0.1)';
                            $statusBadge = '<span class="status-badge" style="background: ' . $statusBg . '; color: ' . $statusColor . '; font-size: 0.7rem; padding: 0.4rem 1.15rem; border-radius: 10px; font-weight: 900; letter-spacing: 0.05em;">' . strtoupper($item->issuance_type) . '</span>';
                        }
                    @endphp
                    <tr style="border-bottom: 1px solid var(--border-color); transition: all 0.3s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1.5rem 2rem;">
                            <div style="font-weight: 900; color: var(--text-main); font-size: 0.85rem; letter-spacing: -0.01em;">{{ $dateStr }}</div>
                            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-top: 2px;">{{ $timeStr }}</div>
                        </td>
                        <td style="padding: 1.5rem 2rem;">
                            <div style="font-weight: 950; color: var(--primary); font-size: 1.05rem;">{{ $item->description }}</div>
                            <div style="margin-top: 4px;">
                                <span style="background: rgba(22, 163, 74, 0.08); color: var(--primary); padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.6rem; font-weight: 900; border: 1px solid rgba(22, 163, 74, 0.1); letter-spacing: 0.03em;">
                                    CATEGORY {{ $item->ledge_category }}
                                </span>
                            </div>
                        </td>

                        <td style="padding: 1.5rem 2rem; font-weight: 900; color: var(--text-main); font-size: 1.05rem; white-space: nowrap;">
                            {{ $item->beneficiary }}
                        </td>
                        <td style="padding: 1.5rem 2rem; font-weight: 900; font-size: 1.35rem; color: var(--text-main);">
                            {{ number_format($item->quantity) }}
                            <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
                                {{ $item->unit ?: 'Package Types' }}
                            </span>
                        </td>
                        <td style="padding: 1.5rem 2rem; font-weight: 700; color: var(--text-muted); font-size: 0.95rem; white-space: nowrap;">
                            {{ $item->authority ?: 'N/A' }}
                        </td>
                        <td style="padding: 1.5rem 2rem; text-align: right;">
                            {!! $statusBadge !!}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 10rem 2rem; text-align: center; color: var(--text-muted);">
                            <div style="width: 110px; height: 110px; background: #f8fafc; border-radius: 35px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2.5rem auto; box-shadow: inset 0 2px 15px rgba(0,0,0,0.03); border: 1px solid #f1f5f9;">
                                <i data-lucide="database-zap" style="width: 54px; height: 54px; color: var(--primary); opacity: 0.25;"></i>
                            </div>
                            <h4 style="font-weight: 950; color: var(--text-main); font-size: 1.75rem; margin-bottom: 0.75rem; letter-spacing: -0.03em;">No Disbursements Logged</h4>
                            <p style="font-weight: 600; font-size: 1.1rem; color: #94a3b8; max-width: 400px; margin: 0 auto;">There are currently no items logged as given out or disbursed.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
    </div>

    <div style="margin-top: 4rem; display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
        <div style="font-size: 0.85rem; font-weight: 800; color: var(--text-muted); display: flex; align-items: center; gap: 8px; background: white; padding: 0.5rem 1.25rem; border-radius: 100px; border: 1.5px solid #edf2f7; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
            <i data-lucide="info" style="width: 14px; color: var(--primary);"></i>
            Showing <span style="color: var(--text-main);">{{ $data->firstItem() ?? 0 }}</span> to <span style="color: var(--text-main);">{{ $data->lastItem() ?? 0 }}</span> of <span style="color: var(--text-main);">{{ $data->total() }}</span> records
        </div>
        <div class="custom-pagination">
            {{ $data->appends(['type' => $type, 'search' => $search, 'start_date' => $startDate, 'end_date' => $endDate])->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

@push('modals')
<!-- Details Modal (Ported from Logs page) -->
<div id="logDetailsModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(10px); z-index: 9999; align-items: center; justify-content: center; padding: 2rem;">
    <div class="glass-card animate-zoom-in" style="width: 100%; max-width: 600px; padding: 0; overflow: hidden; border-radius: 32px; box-shadow: 0 30px 60px rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1);">
        <div class="modal-header" style="padding: 2.5rem; background: linear-gradient(135deg, #16a34a 0%, #3730a3 100%); color: white; display: flex; justify-content: space-between; align-items: center; position: relative;">
            <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
            <div style="display: flex; align-items: center; gap: 1rem; position: relative;">
                <div style="width: 54px; height: 54px; background: rgba(255,255,255,0.15); border-radius: 16px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
                    <i data-lucide="file-text" style="width: 28px; height: 28px;"></i>
                </div>
                <div>
                    <h3 id="modalEventTitle" style="font-size: 1.35rem; font-weight: 950; letter-spacing: -0.03em; margin: 0;">Activity Details</h3>
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
@endpush

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
    /* Premium Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.02);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(22, 163, 74, 0.2);
        border-radius: 10px;
        border: 2px solid transparent;
        background-clip: content-box;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: var(--primary);
    }

    .main-wrapper > *:not(header) {
        max-width: 2000px !important;
    }
</style>

<script>
    function showArchivedMessageDetail(content, sender, date, context) {
        document.getElementById('modalMessageSender').textContent = sender;
        document.getElementById('modalMessageDate').textContent = date;
        document.getElementById('modalMessageContext').textContent = context;
        document.getElementById('modalMessageBody').innerHTML = content;

        document.getElementById('messageDetailModal').style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeMessageDetail() {
        document.getElementById('messageDetailModal').style.display = 'none';
    }

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
                    ${userAvatar ? `<img src="${userAvatar}" style="width: 54px; height: 54px; border-radius: 16px; object-fit: cover; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">` : `<div style="width: 54px; height: 54px; border-radius: 16px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 900; box-shadow: 0 4px 10px rgba(22,163,74,0.2);">${userName.charAt(0)}</div>`}
                    <div>
                        <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 3px; letter-spacing: 0.05em;">Originating User</div>
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
        const logModal = document.getElementById('logDetailsModal');
        const msgModal = document.getElementById('messageDetailModal');
        if (event.target == logModal) closeLogDetails();
        if (event.target == msgModal) closeMessageDetail();
    }
</script>

@push('modals')
<!-- Message Detail Modal -->
<div id="messageDetailModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(10px); z-index: 9999; align-items: center; justify-content: center; padding: 2rem;">
    <div class="glass-card animate-zoom-in" style="width: 100%; max-width: 700px; padding: 0; overflow: hidden; border-radius: 32px; box-shadow: 0 30px 60px rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1);">
        <div style="padding: 2.5rem; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="mail-open" style="width: 24px;"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.25rem; font-weight: 950; margin: 0; letter-spacing: -0.02em;">Archived Communication</h3>
                    <div id="modalMessageContext" style="font-size: 0.7rem; font-weight: 800; background: rgba(255,255,255,0.15); padding: 3px 10px; border-radius: 6px; width: fit-content; margin-top: 4px; text-transform: uppercase;">Context</div>
                </div>
            </div>
            <button onclick="closeMessageDetail()" style="background: rgba(255,255,255,0.1); border: none; width: 40px; height: 40px; border-radius: 50%; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="x" style="width: 20px;"></i>
            </button>
        </div>
        <div style="padding: 2.5rem; background: white; max-height: 60vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <div style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px;">Originator</div>
                    <div id="modalMessageSender" style="font-weight: 900; color: #1e293b; font-size: 1.1rem;">-</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px;">Timestamp</div>
                    <div id="modalMessageDate" style="font-weight: 900; color: #1e293b; font-size: 1.1rem;">-</div>
                </div>
            </div>
            <div id="modalMessageBody" style="font-size: 1rem; line-height: 1.8; color: #334155; font-weight: 500;">
                <!-- Content -->
            </div>
        </div>
        <div style="padding: 1.5rem; background: #f8fafc; text-align: center; border-top: 1px solid #f1f5f9;">
            <button onclick="closeMessageDetail()" style="background: #1e293b; color: white; border: none; padding: 10px 24px; border-radius: 12px; font-weight: 800; font-size: 0.85rem; cursor: pointer;">Close Inspection</button>
        </div>
    </div>
</div>
@endpush
<script>
    // Search Debounce Engine
    let searchTimeout;
    window.debounceSearch = function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('archiveSearchInput').form.submit();
        }, 500); // 500ms delay for high-performance typing
    };

    // Cursor Persistence logic
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('archiveSearchInput');
        if (searchInput && "{{ $search }}") {
            // Move cursor to end of text
            const len = searchInput.value.length;
            searchInput.focus();
            searchInput.setSelectionRange(len, len);
        }
    });
</script>

@endsection

