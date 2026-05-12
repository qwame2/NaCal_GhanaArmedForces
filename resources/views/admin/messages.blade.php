@extends('layouts.admin')

@section('title', 'Communication Hub')

@section('content')
<div class="animate-slide-up" style="height: calc(100vh - 180px); display: flex; gap: 2rem; padding: 1.5rem;">
    <!-- Sidebar: Directory -->
    <div class="glass-card" style="width: 380px; display: flex; flex-direction: column; padding: 0; overflow: hidden; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04);">
        <div style="padding: 2rem; border-bottom: 1px solid var(--border-color); background: linear-gradient(145deg, var(--bg-card), var(--bg-main));">
            <h3 style="font-size: 1.25rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.5rem; letter-spacing: -0.02em;">Registry <span style="color: #4f46e5;">Network</span></h3>
            <div class="search-vault">
                <i data-lucide="search"></i>
                <input type="text" id="networkSearch" placeholder="Search personnel..." oninput="filterNetwork()">
            </div>
        </div>
        
        <div style="flex: 1; overflow-y: auto; padding: 1.25rem;">
            <div style="font-size: 0.75rem; font-weight: 900; color: var(--primary); text-transform: uppercase; letter-spacing: 0.15em; padding: 0 1rem 1rem 1rem; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="users" style="width: 14px;"></i>
                Operational Units
            </div>
            @forelse($users as $user)
            <div class="network-item" id="user-{{ $user->id }}" style="display: flex; align-items: center; gap: 14px; padding: 1.25rem; border-radius: 18px; cursor: pointer; transition: 0.3s; margin-bottom: 6px; border: 1px solid transparent; position: relative;" onclick="selectChat({{ $user->id }}, '{{ $user->name }}', '{{ $user->role }}', '{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}')">
                <div style="position: relative;">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" style="width: 48px; height: 48px; border-radius: 14px; object-fit: cover; border: 2px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    @else
                        <div style="width: 48px; height: 48px; border-radius: 14px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.2rem; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);">{{ substr($user->name, 0, 1) }}</div>
                    @endif
                    <div style="position: absolute; bottom: -2px; right: -2px; width: 14px; height: 14px; background: {{ $user->is_online ? '#10b981' : '#94a3b8' }}; border: 3px solid var(--bg-card); border-radius: 50%;"></div>
                </div>
                <div style="flex: 1; overflow: hidden;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px;">
                        <div style="font-weight: 800; color: var(--text-main); font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $user->name }}</div>
                        <div class="unread-badge" id="badge-{{ $user->id }}" style="display: none; background: #ef4444; color: white; font-size: 0.65rem; font-weight: 900; min-width: 18px; height: 18px; border-radius: 9px; align-items: center; justify-content: center; padding: 0 5px; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4); border: 1.5px solid white; flex-shrink: 0; margin-left: 8px;">0</div>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;">{{ $user->role }}</div>
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                <i data-lucide="users-2" style="width: 32px; opacity: 0.2; margin-bottom: 1rem;"></i>
                <p style="font-size: 0.85rem; font-weight: 700;">No Personnel Records</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Main Communication Terminal -->
    <div class="glass-card" id="commsTerminal" style="flex: 1; display: flex; flex-direction: column; padding: 0; overflow: hidden; border-radius: 24px; position: relative; box-shadow: 0 10px 60px rgba(0,0,0,0.06);">
        <!-- Initial Empty State -->
        <div id="emptyState" style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--bg-card); z-index: 10;">
            <div style="width: 120px; height: 120px; background: var(--primary-glow); border-radius: 35px; display: flex; align-items: center; justify-content: center; margin-bottom: 2.5rem; animation: float-bubble 3s ease-in-out infinite;">
                <i data-lucide="shield-check" style="width: 56px; color: var(--primary);"></i>
            </div>
            <h2 style="font-size: 1.75rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.75rem; letter-spacing: -0.03em;">Overseer Command</h2>
            <p style="color: var(--text-muted); font-size: 1rem; font-weight: 500; text-align: center; max-width: 360px; line-height: 1.6;">Select a personnel node from the network to establish a secure synchronization session.</p>
        </div>

        <!-- Session Header -->
        <div id="sessionHeader" style="padding: 1.5rem 3rem; border-bottom: 1px solid var(--border-color); display: none; justify-content: space-between; align-items: center; background: var(--bg-card); z-index: 5; backdrop-filter: blur(10px);">
            <div style="display: flex; align-items: center; gap: 1.25rem;">
                <div id="activeAvatar"></div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 id="activeName" style="font-size: 1.2rem; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.02em;">Name</h3>
                        <span id="activeUnreadBadge" style="display: none; background: #ef4444; color: white; font-size: 0.7rem; font-weight: 900; padding: 2px 8px; border-radius: 20px; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3); border: 2px solid white;">0</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span id="statusDot" style="width: 10px; height: 10px; background: #10b981; border-radius: 50%;"></span>
                        <span id="activeRole" style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700;">Role</span>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button class="comms-btn" title="Encryption Status"><i data-lucide="lock" style="color: #10b981;"></i></button>
                <button class="comms-btn" title="Audit Records"><i data-lucide="file-text"></i></button>
                <button class="comms-btn" title="Session Options"><i data-lucide="more-horizontal"></i></button>
            </div>
        </div>

        <!-- Terminal Output (Messages) -->
        <div id="terminalOutput" style="flex: 1; overflow-y: auto; padding: 3rem; display: none; background: var(--bg-main); background-image: radial-gradient(var(--border-color) 0.5px, transparent 0.5px); background-size: 24px 24px; flex-direction: column;">
            <!-- Content populated via JS -->
        </div>

        <!-- Command Input -->
        <div id="commandInput" style="padding: 2rem 3rem 3rem 3rem; border-top: 1px solid var(--border-color); display: none; background: var(--bg-card); z-index: 5;">
            <form id="msgForm" onsubmit="return handleSend(event)" enctype="multipart/form-data">
                <div style="position: relative; display: flex; align-items: center; gap: 1.25rem;">
                    <input type="file" id="attachment" style="display: none;" onchange="handleFileSelect(this)">
                    <button type="button" class="comms-btn" style="width: 50px; height: 50px; background: var(--bg-main); flex-shrink: 0;" onclick="document.getElementById('attachment').click()">
                        <i data-lucide="paperclip"></i>
                    </button>
                    <div class="msg-input-vault">
                        <textarea id="msgContent" placeholder="Transmit secure command..." rows="1"></textarea>
                        <button type="submit" id="sendBtn" class="send-action-btn">
                            <i data-lucide="send" style="width: 18px;"></i>
                        </button>
                    </div>
                </div>
                <div id="filePreview" style="margin-top: 12px; display: none; font-size: 0.85rem; color: var(--primary); font-weight: 800; align-items: center; gap: 10px;">
                    <i data-lucide="file" style="width: 16px;"></i>
                    <span id="fileName"></span>
                    <i data-lucide="x" style="width: 16px; cursor: pointer;" onclick="clearFile()"></i>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- ====== Remainder Preview Popover ====== -->
<div id="remainderPreviewSheet" style="
    position: fixed; inset: 0; z-index: 9999;
    display: none; align-items: center; justify-content: center;
    background: rgba(15,23,42,0.5); backdrop-filter: blur(6px);
    padding: 1.5rem;
" onclick="if(event.target===this) window.closeRemainderPreview()">
    <div id="remainderPreviewContent" style="
        background: #ffffff;
        width: 100%; max-width: 620px;
        border-radius: 24px;
        padding: 0;
        box-shadow: 0 25px 80px rgba(0,0,0,0.22), 0 0 0 1px rgba(0,0,0,0.06);
        transform: scale(0.92) translateY(-12px);
        opacity: 0;
        transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.2s ease;
        max-height: 85vh;
        display: flex; flex-direction: column;
        overflow: hidden;
    ">
        <!-- Popover Header -->
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px 24px 16px; border-bottom: 1px solid #f1f5f9;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0; box-shadow: 0 4px 12px rgba(245,158,11,0.35);">
                    <i data-lucide="package-plus" style="width: 18px;"></i>
                </div>
                <div>
                    <div style="font-size: 1rem; font-weight: 900; color: #0f172a; letter-spacing: -0.01em;">Remainder Change Preview</div>
                    <div id="remainderPreviewMeta" style="font-size: 0.75rem; color: #64748b; font-weight: 600; margin-top: 1px;"></div>
                </div>
            </div>
            <button onclick="window.closeRemainderPreview()" style="width: 34px; height: 34px; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #64748b; transition: all 0.15s; flex-shrink:0;" onmouseover="this.style.background='#f1f5f9'; this.style.color='#0f172a'" onmouseout="this.style.background='#f8fafc'; this.style.color='#64748b'">
                <i data-lucide="x" style="width: 15px;"></i>
            </button>
        </div>

        <!-- Popover Body (scrollable) -->
        <div id="remainderPreviewBody" style="padding: 20px 24px 24px; overflow-y: auto; flex: 1;"></div>
    </div>
</div>

<style>
    #remainderPreviewSheet.open { display: flex !important; }
    #remainderPreviewSheet.open #remainderPreviewContent {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
</style>

<style>
    /* Admin view logic */
    .admin-view { display: block !important; }
    .personnel-view { display: none !important; }

    /* Search Vault Styling */
    .search-vault {
        position: relative;
        display: flex;
        align-items: center;
        background: white;
        border: 2px solid #f1f5f9;
        border-radius: 16px;
        padding: 0.5rem 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        margin-top: 1rem;
    }

    .search-vault:focus-within {
        border-color: #4f46e5;
        box-shadow: 0 10px 30px rgba(79, 70, 229, 0.1);
    }

    .search-vault i {
        color: #4f46e5;
        opacity: 0.6;
        margin-right: 0.75rem;
        width: 18px;
    }

    .search-vault input {
        border: none;
        outline: none;
        padding: 0.5rem 0;
        font-size: 0.9rem;
        font-weight: 600;
        color: #0f172a;
        width: 100%;
        background: transparent;
    }

    .search-vault input::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    /* Message Input Vault */
    .msg-input-vault {
        position: relative;
        display: flex;
        align-items: center;
        background: white;
        border: 2px solid #f1f5f9;
        border-radius: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        flex: 1;
        padding-right: 8px;
    }

    .msg-input-vault:focus-within {
        border-color: #4f46e5;
        box-shadow: 0 10px 30px rgba(79, 70, 229, 0.1);
        transform: translateY(-2px);
    }

    .msg-input-vault textarea {
        border: none;
        outline: none;
        padding: 1.1rem 1.5rem;
        font-size: 0.95rem;
        font-weight: 600;
        color: #0f172a;
        width: 100%;
        background: transparent;
        resize: none;
        font-family: inherit;
    }

    .msg-input-vault textarea::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    .send-action-btn {
        background: #4f46e5;
        color: white;
        border: none;
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 8px 16px rgba(79, 70, 229, 0.3);
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        flex-shrink: 0;
    }
    
    .send-action-btn:hover {
        transform: scale(1.08);
        box-shadow: 0 10px 20px rgba(79, 70, 229, 0.4);
    }

    .network-item:hover {
        background: var(--bg-main);
        transform: translateX(8px);
    }
    .network-item.active {
        background: var(--primary-glow);
        border: 1px solid var(--primary-glow);
    }
    
    .comms-btn {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        cursor: pointer;
        transition: 0.3s;
    }
    .comms-btn:hover {
        background: var(--primary-glow);
        color: var(--primary);
        border-color: var(--primary-glow);
        transform: translateY(-2px);
    }
    .comms-btn i { width: 20px; }

    .comms-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 1.5rem;
        max-width: 80%;
    }
    .comms-group.me {
        align-self: flex-end;
        align-items: flex-end;
    }
    .comms-group.recipient {
        align-self: flex-start;
        align-items: flex-start;
    }
    .comms-group.system {
        align-self: center;
        align-items: center;
        opacity: 0.6;
        margin-bottom: 2rem;
    }
    
    .comms-bubble {
        padding: 1.1rem 1.4rem;
        border-radius: 20px;
        font-size: 0.95rem;
        font-weight: 600;
        line-height: 1.5;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        word-break: break-word;
    }
    
    .me .comms-bubble {
        background: var(--primary);
        color: white;
        border-bottom-right-radius: 6px;
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.2);
    }
    
    .recipient .comms-bubble {
        background: var(--bg-card);
        color: var(--text-main);
        border-bottom-left-radius: 6px;
        border: 1px solid var(--border-color);
    }
    
    .system .comms-bubble {
        background: transparent;
        color: var(--text-muted);
        border: 1px dashed var(--border-color);
        font-size: 0.85rem;
        padding: 0.75rem 1.25rem;
    }
    
    .comms-meta {
        font-size: 0.7rem;
        font-weight: 900;
        color: var(--text-muted);
        margin-top: 10px;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }

    .attachment-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.1);
        padding: 10px 14px;
        border-radius: 12px;
        margin-top: 10px;
        text-decoration: none;
        color: inherit;
        font-size: 0.85rem;
    }
    .recipient .attachment-pill { background: var(--bg-main); }

    @keyframes float-bubble {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-15px); }
    }

    .loader-mini {
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
    let activeUserId = null;
    let pollInterval = null;
    let countInterval = null;
    let onlineStatuses = {};

    function filterNetwork() {
        const term = document.getElementById('networkSearch').value.toLowerCase();
        const items = document.querySelectorAll('.network-item');
        
        items.forEach(item => {
            const name = item.querySelector('div[style*="font-weight: 800"]').textContent.toLowerCase();
            const role = item.querySelector('div[style*="font-size: 0.75rem"]').textContent.toLowerCase();
            if (name.includes(term) || role.includes(term)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function selectChat(userId, name, role, avatar) {
        activeUserId = userId;
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('sessionHeader').style.display = 'flex';
        document.getElementById('terminalOutput').style.display = 'flex';
        document.getElementById('commandInput').style.display = 'block';

        document.getElementById('activeName').textContent = name;
        document.getElementById('activeRole').textContent = role;
        
        const avatarDiv = document.getElementById('activeAvatar');
        if (avatar) {
            avatarDiv.innerHTML = `<img src="${avatar}" style="width: 52px; height: 52px; border-radius: 16px; object-fit: cover; border: 2px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">`;
        } else {
            avatarDiv.innerHTML = `<div style="width: 52px; height: 52px; border-radius: 16px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.4rem; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);">${name.charAt(0)}</div>`;
        }

        document.querySelectorAll('.network-item').forEach(el => el.classList.remove('active'));
        const activeEl = document.getElementById(`user-${userId}`);
        if (activeEl) activeEl.classList.add('active');

        fetchMessages();
        
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(fetchMessages, 3000);

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function fetchMessages() {
        if (!activeUserId) return;
        
        fetch(`{{ route('api.messages.fetch', ['userId' => $user->id], false) }}`.replace('{{ $user->id }}', activeUserId))
            .then(res => {
                if (!res.ok) throw new Error('Secure line interrupted');
                return res.json();
            })
            .then(data => {
                const container = document.getElementById('terminalOutput');
                const wasAtBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;
                
                let html = '';

                data.forEach(msg => {
                    // Skip messages that are automated OR marked strictly for personnel
                    // ONLY skip automated messages that are strictly for personnel OR general system noise
                    const isStrictlyPersonnel = msg.message && msg.message.includes('personnel-view') && !msg.message.includes('admin-view');
                    const isSraApproval = msg.message && (msg.message.includes('sra-approval-msg') || msg.message.includes('sra-approval-card') || msg.message.includes('SRA APPROVAL REQUIRED'));
                    const isEditReq = msg.message && (msg.message.includes('edit-req-msg') || msg.message.includes('AUTHORIZATION REQUIRED'));
                    
                    if (msg.is_automated && !isSraApproval && !isEditReq && isStrictlyPersonnel) {
                        return;
                    }

                    const isMe = msg.sender_id == {{ auth()->id() }};
                    const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    
                    let ticksHtml = '';
                    if (isMe) {
                        const isRead = msg.read_at != null;
                        const isRecipientOnline = onlineStatuses[activeUserId];
                        
                        if (isRead) {
                            ticksHtml = '<i data-lucide="check-check" style="color: #10b981; width: 14px; height: 14px; margin-left: 4px; vertical-align: -3px;"></i>';
                        } else if (isRecipientOnline) {
                            ticksHtml = '<i data-lucide="check-check" style="color: #94a3b8; width: 14px; height: 14px; margin-left: 4px; vertical-align: -3px;"></i>';
                        } else {
                            ticksHtml = '<i data-lucide="check" style="color: #94a3b8; width: 14px; height: 14px; margin-left: 4px; vertical-align: -3px;"></i>';
                        }
                    }
                    
                    html += `
                        <div class="comms-group ${isMe ? 'me' : 'recipient'}">
                            <div class="comms-bubble">
                                ${msg.message ? `<span style="word-break: break-word;">${msg.message}</span>` : ''}
                                ${msg.attachment ? `
                                    <a href="{{ asset('storage') }}/${msg.attachment}" target="_blank" class="attachment-pill">
                                        <i data-lucide="file-text" style="width: 16px;"></i>
                                        <span>${msg.attachment_name || 'Document Record'}</span>
                                    </a>
                                ` : ''}
                                ${ticksHtml ? `<span style="display: inline-block; margin-left: 8px; vertical-align: bottom;">${ticksHtml}</span>` : ''}
                            </div>
                            <div class="comms-meta">${isMe ? 'YOU' : 'SENDER'} &bull; ${time}</div>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
                if (typeof lucide !== 'undefined') lucide.createIcons();
                _patchRemainderPreviewButtons();
                initClearanceTimers();

                
                if (wasAtBottom) {
                    container.scrollTop = container.scrollHeight;
                }
            })
            .catch(err => console.error('Comms Error:', err));
            
        // Mark as read
        fetch(`{{ route('api.messages.read', ['userId' => $user->id], false) }}`.replace('{{ $user->id }}', activeUserId), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(() => updateUnreadCounts());
    }

    function updateUnreadCounts() {
        fetch("{{ route('api.unread-counts', [], false) }}")
            .then(res => res.json())
            .then(counts => {
                let activeCount = 0;
                document.querySelectorAll('.unread-badge').forEach(badge => {
                    const userId = badge.id.replace('badge-', '');
                    const count = counts[userId] || 0;
                    
                    if (count > 0) {
                        badge.textContent = count;
                        // Only hide in sidebar if it's the active chat
                        if (userId == activeUserId) {
                            badge.style.display = 'none';
                            activeCount = count;
                        } else {
                            badge.style.display = 'flex';
                        }
                    } else {
                        badge.style.display = 'none';
                    }
                });

                // Update Header Badge
                const headerBadge = document.getElementById('activeUnreadBadge');
                if (headerBadge) {
                    if (activeCount > 0) {
                        headerBadge.textContent = activeCount;
                        headerBadge.style.display = 'inline-block';
                    } else {
                        headerBadge.style.display = 'none';
                    }
                }
            });

        // Sync Online Statuses
        fetch("{{ route('api.online-statuses', [], false) }}")
            .then(res => res.json())
            .then(statuses => {
                onlineStatuses = statuses;
                Object.keys(statuses).forEach(userId => {
                    const isOnline = statuses[userId];
                    const dot = document.querySelector(`#user-${userId} div[style*="border-radius: 50%"]`);
                    if (dot) {
                        dot.style.background = isOnline ? '#10b981' : '#94a3b8';
                    }
                    
                    // Also update header dot if active
                    if (activeUserId == userId) {
                        const statusDot = document.getElementById('statusDot');
                        if (statusDot) statusDot.style.background = isOnline ? '#10b981' : '#94a3b8';
                    }
                });
            });
    }

    function handleSend(e) {
        e.preventDefault();
        const content = document.getElementById('msgContent').value;
        const file = document.getElementById('attachment').files[0];
        
        if (!content && !file) return;
        
        const formData = new FormData();
        formData.append('receiver_id', activeUserId);
        formData.append('message', content);
        if (file) formData.append('attachment', file);
        
        const sendBtn = document.getElementById('sendBtn');
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<div class="loader-mini"></div>';
        
        fetch("{{ route('api.messages.send', [], false) }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(async res => {
            const isJson = res.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await res.json() : null;

            if (!res.ok) {
                throw new Error(data?.message || `Terminal Error: ${res.status}`);
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                document.getElementById('msgContent').value = '';
                clearFile();
                fetchMessages();
            } else {
                alert('Transmission failed: ' + (data.message || 'Unknown protocol violation'));
            }
        })
        .catch(err => {
            console.error('Transmission Error:', err);
            alert('Security Alert: ' + err.message);
        })
        .finally(() => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i data-lucide="send" style="width: 20px;"></i>';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    }

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            document.getElementById('fileName').textContent = input.files[0].name;
            document.getElementById('filePreview').style.display = 'flex';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    function clearFile() {
        document.getElementById('attachment').value = '';
        document.getElementById('filePreview').style.display = 'none';
    }

    // Process SRA Creation Approval logic
    window.processSraCreationApproval = function(id, status, btnElement) {
        if (status === 'rejected') {
            const bubble = btnElement.closest('.comms-bubble');
            const bubbleHtml = bubble ? bubble.innerHTML : '';
            const pMatch = bubbleHtml.match(/Personnel\s+<b>(.*?)<\/b>/i);
            const personnel = (pMatch && pMatch[1]) ? pMatch[1].trim() : 'Personnel';
            
            Swal.fire({
                title: 'Deny SRA Submission',
                text: `Provide a reason for rejecting the submission from ${personnel}:`,
                input: 'textarea',
                inputPlaceholder: 'e.g., Incorrect quantity specified, missing documentation...',
                inputAttributes: {
                    'aria-label': 'Type your reason here'
                },
                showCancelButton: true,
                confirmButtonText: 'Confirm Rejection',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                preConfirm: (reason) => {
                    if (!reason) {
                        Swal.showValidationMessage('A justification is required to reject this request.');
                    }
                    return reason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    executeSraProcess_v2(id, status, result.value, btnElement);
                }
            });
        } else {
            executeSraProcess_v2(id, status, '', btnElement);
        }
    };

    function executeSraProcess_v2(id, status, reason, btnElement) {
        const actionsDiv = document.getElementById(`sra-creation-actions-${id}`);
        if (actionsDiv) {
            const btns = actionsDiv.querySelectorAll('button');
            btns.forEach(b => b.disabled = true);
        }
        
        btnElement.innerHTML = '<i data-lucide="loader" class="animate-spin" style="width:14px;"></i> Processing...';
        if (typeof lucide !== 'undefined') lucide.createIcons();

        fetch(`{{ url('/sra-creation', [], false) }}/${id}/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                status: status,
                reason: reason
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('Server error');
            return res.json();
        })
        .then(data => {
            if(data.success) {
                const actionsDiv = document.getElementById(`sra-creation-actions-${id}`);
                if (actionsDiv) {
                    const color = status === 'approved' ? '#10b981' : '#dc2626';
                    const bgColor = status === 'approved' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(220, 38, 38, 0.1)';
                    const text = status === 'approved' 
                        ? (data.is_remainder ? 'REMAINDER COMMITTED' : 'APPROVED & SAVED') 
                        : 'REJECTED';
                    let html = `<div style="padding: 12px 20px; border-radius: 12px; background: ${bgColor}; color: ${color}; font-weight: 900; border: 1.5px solid ${color}; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.85rem; letter-spacing: 0.02em;">
                        <i data-lucide="${status === 'approved' ? 'check-circle' : 'alert-circle'}" style="width: 16px;"></i> ${text}
                    </div>`;
                    
                    if (status === 'approved' && data.batch_id) {
                        const printUrl = `{{ url('/received-items') }}/${data.batch_id}/sra`;
                        html += `<br><a href="${printUrl}" target="_blank" style="display: inline-block; background: #4f46e5; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: 800; font-size: 0.75rem; margin-top: 8px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);">Download / Print SRA</a>`;
                    }
                    
                    actionsDiv.innerHTML = html;
                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    const toastMsg = data.is_remainder ? 'Remainder items committed to stock.' : `SRA request ${status}.`;
                    showToast('Success', toastMsg, 'success');
                }
            } else {
                showToast('Action Failed', data.message || 'Error processing request', 'error');
                btnElement.innerText = status === 'approved' ? 'Approve & Save' : 'Reject';
                btnElement.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            showToast('System Error', 'An unexpected error occurred during SRA authorization.', 'error');
            btnElement.innerText = status === 'approved' ? 'Approve & Save' : 'Reject';
            btnElement.disabled = false;
        });
    };



    // Process Edit Request logic
    window.processEditRequest = function(id, status, btnElement) {
        const actionsDiv = document.getElementById(`edit-req-actions-${id}`);
        if (actionsDiv) {
            const btns = actionsDiv.querySelectorAll('button');
            btns.forEach(b => b.disabled = true);
        }
        
        btnElement.innerHTML = '<i data-lucide="loader" class="animate-spin" style="width:14px;"></i> Processing...';

        fetch(`{{ url('/edit-requests', [], false) }}/${id}/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: status })
        })
        .then(res => {
            if (!res.ok) throw new Error('Server error');
            return res.json();
        })
        .then(data => {
            if(data.success) {
                const actionsDiv = document.getElementById(`edit-req-actions-${id}`);
                if (actionsDiv) {
                    const color = status === 'approved' ? '#10b981' : '#dc2626';
                    const bgColor = status === 'approved' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(220, 38, 38, 0.1)';
                    actionsDiv.innerHTML = `<div style="padding: 8px 12px; border-radius: 8px; background: ${bgColor}; color: ${color}; font-weight: 800; border: 1px solid ${color}; display: inline-block;">Request ${status.toUpperCase()}</div>`;
                }
            } else {
                showToast('Update Failed', data.message || 'Error processing request', 'error');
                btnElement.innerText = status === 'approved' ? 'Approve' : 'Cancel';
                btnElement.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Connection Error', 'An unexpected error occurred. Please check system logs.', 'error');
            btnElement.innerText = status === 'approved' ? 'Approve' : 'Cancel';
            btnElement.disabled = false;
        });
    };

    function initClearanceTimers() {
        if (window.clearanceInterval) clearInterval(window.clearanceInterval);
        
        window.clearanceInterval = setInterval(() => {
            const containers = document.querySelectorAll('.clearance-container');
            if (containers.length === 0) {
                clearInterval(window.clearanceInterval);
                return;
            }

            containers.forEach(container => {
                const expiresAt = parseInt(container.getAttribute('data-expires-at'));
                const now = Date.now();
                const timeLeft = Math.max(0, Math.floor((expiresAt - now) / 1000));
                
                const timerSpan = container.querySelector('.timer-seconds');
                const actionBtn = container.querySelector('.clearance-action-btn');
                const notice = container.querySelector('.clearance-timer-notice');

                if (timeLeft <= 0) {
                    if (timerSpan) timerSpan.innerText = '0';
                    if (notice) {
                        notice.style.background = 'rgba(239, 68, 68, 0.1)';
                        notice.style.color = '#ef4444';
                        notice.innerHTML = '❌ SECURITY NOTICE: This clearance has EXPIRED.';
                    }
                    if (actionBtn && !actionBtn.classList.contains('expired')) {
                        actionBtn.classList.add('expired');
                        actionBtn.style.background = '#94a3b8';
                        actionBtn.style.boxShadow = 'none';
                        actionBtn.style.pointerEvents = 'none';
                        actionBtn.innerText = 'SESSION EXPIRED';
                    }
                } else {
                    if (timerSpan) timerSpan.innerText = timeLeft;
                }
            });
        }, 1000);
    }

    // Initial counts and polling
    updateUnreadCounts();
    setInterval(updateUnreadCounts, 3000);

    // Add animation for badges
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes badge-pop {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .network-item.active .unread-badge {
            opacity: 0.5;
        }
        .unread-badge[style*="display: flex"] {
            display: flex !important;
        }
        #activeUnreadBadge[style*="display: inline-block"] {
            display: inline-block !important;
        }
    `;
    document.head.appendChild(style);

    // ====== Remainder Preview Bottom Sheet Logic ======

    // Patch all existing "Preview Changes" buttons in the DOM after messages render.
    // Handles old messages that have broken onclicks or no data-req-id.
    function _patchRemainderPreviewButtons() {
        document.querySelectorAll('.sra-approval-card').forEach(function(card) {
            // Find the edit request ID from the actions div inside this card
            const actionsDiv = card.querySelector('[id^="sra-creation-actions-"]');
            if (!actionsDiv) return;
            const reqId = actionsDiv.id.replace('sra-creation-actions-', '');
            if (!reqId) return;

            // Find any button in this card that looks like a preview button
            card.querySelectorAll('button').forEach(function(btn) {
                const text = btn.textContent.trim();
                if (text.includes('Preview Changes') || text.includes('Preview')) {
                    // Already patched? Skip.
                    if (btn.getAttribute('data-req-id') === reqId) return;

                    // Replace with a clean version: remove broken onclick, set data-req-id
                    btn.removeAttribute('onclick');
                    btn.setAttribute('data-req-id', reqId);
                    btn.classList.add('remainder-preview-btn');

                    // Remove any sibling hidden div (old broken preview panel)
                    const next = btn.nextElementSibling;
                    if (next && next.tagName === 'DIV' && next.style.display === 'none') {
                        next.remove();
                    }
                }
            });
        });
    }

    // ---- Init: move the sheet to <body> so position:fixed works correctly ----
    // CSS transforms on parent containers break position:fixed — moving to body escapes this.
    (function() {
        const sheet = document.getElementById('remainderPreviewSheet');
        if (sheet && sheet.parentElement !== document.body) {
            document.body.appendChild(sheet);
        }
    })();

    // Helper to render the bottom sheet given preview data from the API
    function _renderRemainderSheet(data) {
        const items = data.items || [];

        let bodyHtml = '';

        if (items.length === 0) {
            bodyHtml = `<p style="color:#94a3b8; text-align:center; padding: 1.5rem 0;">No item details found.</p>`;
        } else {
            const totalAdding = items.reduce((s, i) => s + i.adding, 0);

            // Summary stats
            bodyHtml += `
            <div style="display:flex; gap:10px; margin-bottom:18px;">
                <div style="flex:1; background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.2); border-radius:12px; padding:12px 16px;">
                    <div style="font-size:0.68rem; font-weight:800; color:#92400e; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:3px;">Items Affected</div>
                    <div style="font-size:1.5rem; font-weight:900; color:#b45309; line-height:1;">${items.length}</div>
                </div>
                <div style="flex:1; background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.2); border-radius:12px; padding:12px 16px;">
                    <div style="font-size:0.68rem; font-weight:800; color:#065f46; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:3px;">Total Units Adding</div>
                    <div style="font-size:1.5rem; font-weight:900; color:#10b981; line-height:1;">+${totalAdding}</div>
                </div>
            </div>`;

            // Items table
            bodyHtml += `<div style="border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; margin-bottom:14px;">`;
            bodyHtml += `<div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:6px; padding:9px 14px; background:#f8fafc;">
                <span style="font-size:0.68rem; font-weight:900; color:#64748b; text-transform:uppercase; letter-spacing:0.06em;">Item</span>
                <span style="font-size:0.68rem; font-weight:900; color:#64748b; text-transform:uppercase; text-align:center;">Current</span>
                <span style="font-size:0.68rem; font-weight:900; color:#10b981; text-transform:uppercase; text-align:center;">+ Adding</span>
                <span style="font-size:0.68rem; font-weight:900; color:#4f46e5; text-transform:uppercase; text-align:center;">New Total</span>
            </div>`;

            items.forEach((item, idx) => {
                bodyHtml += `
                <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:6px; padding:12px 14px; border-top:1px solid #f1f5f9; background:${idx%2===0?'#fff':'#fafafa'}; align-items:center;">
                    <div>
                        <div style="font-size:0.88rem; font-weight:800; color:#0f172a;">${item.description}</div>
                        <div style="font-size:0.72rem; color:#94a3b8; font-weight:600;">${item.unit}</div>
                    </div>
                    <div style="text-align:center; font-size:0.88rem; font-weight:700; color:#475569;">${item.current}</div>
                    <div style="text-align:center;">
                        <span style="background:rgba(16,185,129,0.1); color:#10b981; padding:3px 10px; border-radius:7px; font-size:0.82rem; font-weight:900;">+${item.adding}</span>
                    </div>
                    <div style="text-align:center;">
                        <span style="background:rgba(79,70,229,0.08); color:#4f46e5; padding:3px 10px; border-radius:7px; font-size:0.82rem; font-weight:900;">${item.projected}</span>
                    </div>
                </div>`;
            });
            bodyHtml += `</div>`;

            bodyHtml += `<div style="padding:10px 14px; background:rgba(245,158,11,0.06); border:1px solid rgba(245,158,11,0.18); border-radius:10px; font-size:0.78rem; color:#92400e; font-weight:600;">
                ⚠️ &nbsp;These changes will only apply to inventory <strong>after you approve</strong> the request.
            </div>`;
        }

        Swal.fire({
            title: `<div style="display:flex; align-items:center; gap:10px; justify-content:center;">
                        <div style="width:36px; height:36px; background:linear-gradient(135deg,#f59e0b,#d97706); border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 4px 12px rgba(245,158,11,0.3);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/><path d="m7.5 4.27 9 5.15"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" x2="12" y1="22" y2="12"/><circle cx="18.5" cy="15.5" r="2.5"/><path d="M20.27 17.27 22 19"/></svg>
                        </div>
                        <div style="text-align:left;">
                            <div style="font-size:1rem; font-weight:900; color:#0f172a;">Remainder Change Preview</div>
                            <div style="font-size:0.75rem; color:#64748b; font-weight:600;">Batch #${data.batchId} &nbsp;•&nbsp; ${data.personnel}</div>
                        </div>
                    </div>`,
            html: bodyHtml,
            showConfirmButton: false,
            showCloseButton: true,
            width: 580,
            padding: '1.25rem',
            customClass: {
                popup: 'remainder-preview-popup',
                title: 'remainder-preview-title',
                htmlContainer: 'remainder-preview-body',
                closeButton: 'remainder-preview-close',
            }
        });
    }


    // Called two ways:
    //  1. Old messages: onclick='window.showRemainderPreview(this)' — receives the button element
    //  2. New messages: event delegation below passes (reqId, btnEl)
    window.showRemainderPreview = function(reqIdOrBtn, btnEl) {
        let reqId, btn;

        if (reqIdOrBtn && typeof reqIdOrBtn === 'object' && reqIdOrBtn.nodeType) {
            // Old-style call: showRemainderPreview(this) — first arg is the button DOM element
            btn   = reqIdOrBtn;
            reqId = btn.getAttribute('data-req-id');

            // If old button doesn't have data-req-id, try to find the edit request ID
            // from the nearby sra-creation-actions div (id="sra-creation-actions-{reqId}")
            if (!reqId) {
                const card = btn.closest('.sra-approval-card');
                if (card) {
                    const actionsDiv = card.querySelector('[id^="sra-creation-actions-"]');
                    if (actionsDiv) {
                        reqId = actionsDiv.id.replace('sra-creation-actions-', '');
                    }
                }
            }
        } else {
            // New-style call: showRemainderPreview(reqId, btn)
            reqId = reqIdOrBtn;
            btn   = btnEl || null;
        }

        if (!reqId) {
            alert('Could not identify the request. Please try again.');
            return;
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<svg style="width:14px;height:14px;animation:spin 1s linear infinite;display:inline;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg> Loading...`;
        }

        fetch(`{{ url('/api/edit-requests') }}/${reqId}/remainder-preview`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => {
            if (!r.ok) throw new Error('Server error ' + r.status);
            return r.json();
        })
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<i data-lucide="eye" style="width:15px;"></i> Preview Changes`;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
            _renderRemainderSheet(data);
        })
        .catch(err => {
            console.error('Preview fetch error:', err);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<i data-lucide="eye" style="width:15px;"></i> Preview Changes`;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
            alert('Could not load preview: ' + err.message);
        });
    };

    window.closeRemainderPreview = function() {
        if (typeof Swal !== 'undefined') Swal.close();
    };

    // Event delegation — catches new messages with data-req-id
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('button.remainder-preview-btn, button[data-req-id]');
        if (!btn) return;
        const reqId = btn.getAttribute('data-req-id');
        if (!reqId) return;
        e.stopPropagation();
        window.showRemainderPreview(reqId, btn);
    });

    // Spin keyframe for loading indicator
    if (!document.getElementById('spin-keyframe')) {
        const s = document.createElement('style');
        s.id = 'spin-keyframe';
        s.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(s);
    }

    // Close sheet on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') window.closeRemainderPreview();
    });

</script>
@endsection
