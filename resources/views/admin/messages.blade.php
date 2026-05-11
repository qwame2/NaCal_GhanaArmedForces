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
        
        fetch(`{{ url('/api/messages') }}/${activeUserId}`)
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
                initClearanceTimers();
                
                if (wasAtBottom) {
                    container.scrollTop = container.scrollHeight;
                }
            })
            .catch(err => console.error('Comms Error:', err));
            
        // Mark as read
        fetch(`{{ url('/api/messages') }}/${activeUserId}/read`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(() => updateUnreadCounts());
    }

    function updateUnreadCounts() {
        fetch("{{ route('api.unread-counts') }}")
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
        fetch("{{ route('api.online-statuses') }}")
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
        
        fetch("{{ route('api.messages.send') }}", {
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

        fetch(`{{ url('/sra-creation') }}/${id}/process`, {
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
                    const text = status === 'approved' ? 'APPROVED & SAVED' : 'REJECTED';
                    let html = `<div style="padding: 12px 20px; border-radius: 12px; background: ${bgColor}; color: ${color}; font-weight: 900; border: 1.5px solid ${color}; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.85rem; letter-spacing: 0.02em;">
                        <i data-lucide="${status === 'approved' ? 'check-circle' : 'alert-circle'}" style="width: 16px;"></i> ${text}
                    </div>`;
                    
                    if (status === 'approved' && data.batch_id) {
                        const printUrl = `{{ url('/received-items') }}/${data.batch_id}/sra`;
                        html += `<br><a href="${printUrl}" target="_blank" style="display: inline-block; background: #4f46e5; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: 800; font-size: 0.75rem; margin-top: 8px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);">Download / Print SRA</a>`;
                    }
                    
                    actionsDiv.innerHTML = html;

                    showToast('Success', `SRA request ${status}.`, 'success');
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

        fetch(`{{ url('/edit-requests') }}/${id}/process`, {
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
</script>
@endsection
