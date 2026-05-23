@extends('layouts.dashboard')

@section('content')
<div class="animate-slide-up" style="height: calc(100vh - 180px); display: flex; gap: 2rem; padding: 1.5rem;">
    <!-- Sidebar: Directory -->
    <div class="glass-card" style="width: 380px; display: flex; flex-direction: column; padding: 0; overflow: hidden; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04);">
        <div style="padding: 2rem; border-bottom: 1px solid var(--border-color); background: linear-gradient(145deg, var(--bg-card), var(--bg-main));">
            <h3 style="font-size: 1.25rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.5rem; letter-spacing: -0.02em;">Registry <span style="color: var(--primary);">Network</span></h3>
            <div style="position: relative;">
                <input type="text" placeholder="Search personnel..." style="width: 100%; padding: 0.85rem 1rem 0.85rem 2.75rem; border-radius: 14px; border: 1px solid var(--border-color); background: var(--bg-main); font-size: 0.9rem; outline: none; transition: 0.3s; color: var(--text-main);" onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px var(--primary-glow)'">
                <i data-lucide="search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 18px; color: var(--text-muted);"></i>
            </div>
        </div>
        
        <div style="flex: 1; overflow-y: auto; padding: 1.25rem;">
            <!-- Admins/Overseers -->
            <div style="font-size: 0.75rem; font-weight: 900; color: var(--primary); text-transform: uppercase; letter-spacing: 0.15em; padding: 0 1rem 1rem 1rem; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="shield" style="width: 14px;"></i>
                Overseer Command
            </div>
            @foreach($admins as $admin)
            <div class="network-item" id="user-{{ $admin->id }}" style="display: flex; align-items: center; gap: 14px; padding: 1.25rem; border-radius: 18px; cursor: pointer; transition: 0.3s; margin-bottom: 6px; border: 1px solid transparent;" onclick="selectChat({{ $admin->id }}, '{{ $admin->name }}', 'System Administrator', '{{ $admin->avatar ? Storage::url($admin->avatar) : '' }}')">
                <div style="position: relative;">
                    @if($admin->avatar)
                        <img src="{{ Storage::url($admin->avatar) }}" style="width: 48px; height: 48px; border-radius: 14px; object-fit: cover; border: 2px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    @else
                        <div style="width: 48px; height: 48px; border-radius: 14px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.2rem; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);">{{ substr($admin->name, 0, 1) }}</div>
                    @endif
                    <div style="position: absolute; bottom: -2px; right: -2px; width: 14px; height: 14px; background: {{ $admin->is_online ? '#10b981' : '#94a3b8' }}; border: 3px solid var(--bg-card); border-radius: 50%;"></div>
                </div>
                <div style="flex: 1; overflow: hidden;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px;">
                        <div style="font-weight: 800; color: var(--text-main); font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $admin->name }}</div>
                        <div class="unread-badge" id="badge-{{ $admin->id }}" style="display: none; background: #ef4444; color: white; font-size: 0.65rem; font-weight: 900; min-width: 18px; height: 18px; border-radius: 9px; align-items: center; justify-content: center; padding: 0 5px; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4); border: 1.5px solid white; flex-shrink: 0; margin-left: 8px;">0</div>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; display: flex; align-items: center; gap: 4px;">
                        <span style="color: var(--primary);">●</span> Command Center
                    </div>
                </div>
            </div>
            @endforeach

            <!-- Colleagues -->
            <div style="font-size: 0.75rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.15em; padding: 2rem 1rem 1rem 1rem; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="users" style="width: 14px;"></i>
                Team Personnel
            </div>
            @foreach($colleagues as $colleague)
            <div class="network-item" id="user-{{ $colleague->id }}" style="display: flex; align-items: center; gap: 14px; padding: 1.25rem; border-radius: 18px; cursor: pointer; transition: 0.3s; margin-bottom: 6px; border: 1px solid transparent;" onclick="selectChat({{ $colleague->id }}, '{{ $colleague->name }}', '{{ $colleague->role ?? 'Personnel' }}', '{{ $colleague->avatar ? Storage::url($colleague->avatar) : '' }}')">
                <div style="position: relative;">
                    @if($colleague->avatar)
                        <img src="{{ Storage::url($colleague->avatar) }}" style="width: 48px; height: 48px; border-radius: 14px; object-fit: cover; border: 2px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                    @else
                        <div style="width: 48px; height: 48px; border-radius: 14px; background: var(--bg-main); color: var(--text-main); display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.2rem; border: 1px solid var(--border-color);">{{ substr($colleague->name, 0, 1) }}</div>
                    @endif
                    <div style="position: absolute; bottom: -2px; right: -2px; width: 14px; height: 14px; background: {{ $colleague->is_online ? '#10b981' : '#94a3b8' }}; border: 3px solid var(--bg-card); border-radius: 50%;"></div>
                </div>
                <div style="flex: 1; overflow: hidden;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px;">
                        <div style="font-weight: 800; color: var(--text-main); font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $colleague->name }}</div>
                        <div class="unread-badge" id="badge-{{ $colleague->id }}" style="display: none; background: #ef4444; color: white; font-size: 0.65rem; font-weight: 900; min-width: 18px; height: 18px; border-radius: 9px; align-items: center; justify-content: center; padding: 0 5px; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4); border: 1.5px solid white; flex-shrink: 0; margin-left: 8px;">0</div>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;">{{ $colleague->role ?? 'Personnel' }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Main Communication Terminal -->
    <div class="glass-card" id="commsTerminal" style="flex: 1; display: flex; flex-direction: column; padding: 0; overflow: hidden; border-radius: 24px; position: relative; box-shadow: 0 10px 60px rgba(0,0,0,0.06);">
        <!-- Initial Empty State -->
        <div id="emptyState" style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--bg-card); z-index: 10;">
            <div style="width: 120px; height: 120px; background: var(--primary-glow); border-radius: 35px; display: flex; align-items: center; justify-content: center; margin-bottom: 2.5rem; animation: float-bubble 3s ease-in-out infinite;">
                <i data-lucide="message-circle" style="width: 56px; color: var(--primary);"></i>
            </div>
            <h2 style="font-size: 1.75rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.75rem; letter-spacing: -0.03em;">Secure Communications</h2>
            <p style="color: var(--text-muted); font-size: 1rem; font-weight: 500; text-align: center; max-width: 360px; line-height: 1.6;">Initiate an encrypted data session with the Overseer or team personnel to synchronize registry operations.</p>
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
                <button class="comms-btn" title="Encryption Status"><i data-lucide="shield-check" style="color: #10b981;"></i></button>
                <button class="comms-btn" title="Registry Logs"><i data-lucide="clipboard-list"></i></button>
                <button class="comms-btn" title="End Session"><i data-lucide="more-horizontal"></i></button>
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
                    <button type="button" class="comms-btn" style="width: 50px; height: 50px; background: var(--bg-main);" onclick="document.getElementById('attachment').click()">
                        <i data-lucide="paperclip"></i>
                    </button>
                    <div style="flex: 1; position: relative;">
                        <textarea id="msgContent" placeholder="Transmit secure data..." rows="1" style="width: 100%; padding: 1.1rem 5rem 1.1rem 1.75rem; border-radius: 20px; border: 2px solid var(--border-color); background: var(--bg-main); font-family: inherit; font-size: 1rem; font-weight: 600; outline: none; resize: none; transition: 0.3s; color: var(--text-main);" onfocus="this.style.borderColor='var(--primary)'; this.style.background='var(--bg-card)'; this.style.boxShadow='0 10px 30px var(--primary-glow)'"></textarea>
                        <button type="submit" id="sendBtn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: var(--primary); color: white; border: none; width: 44px; height: 44px; border-radius: 14px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3); transition: 0.3s;" onmouseover="this.style.transform='translateY(-50%) scale(1.05)'" onmouseout="this.style.transform='translateY(-50%) scale(1)'">
                            <i data-lucide="send" style="width: 20px;"></i>
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
    /* Personnel view logic */
    .personnel-view { display: block !important; }

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
</style>

<script>
    let activeUserId = null;
    let pollInterval = null;
    let onlineStatuses = {};

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

        document.querySelectorAll('.network-item').forEach(el => {
            el.classList.remove('active');
        });
        const activeEl = document.getElementById(`user-${userId}`);
        if (activeEl) activeEl.classList.add('active');

        fetchMessages();
        
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(fetchMessages, 3000);

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function fetchMessages() {
        if (!activeUserId) return;
        
        const fetchUrl = `{{ route('api.messages.fetch', ['userId' => 'PLACEHOLDER'], false) }}`.replace('PLACEHOLDER', activeUserId);
        fetch(fetchUrl)
            .then(res => {
                if (!res.ok) throw new Error('Secure line interrupted');
                return res.json();
            })
            .then(data => {
                const container = document.getElementById('terminalOutput');
                const wasAtBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;
                
                let html = '';

                data.forEach(msg => {
                    // Skip admin-only messages — not for personnel view
                    const isDeleteLog = msg.message && (
                        msg.message.includes('DELETE REQUEST LOG') ||
                        msg.message.includes('delete-req-msg') ||
                        msg.message.includes('PERMANENTLY DELETE Batch') ||
                        msg.message.includes('REQUEST CANCELED') ||
                        msg.message.includes('EDIT REQUEST LOG') ||
                        msg.message.includes('edit-req-log') ||
                        msg.message.includes("request to edit Batch") ||
                        msg.message.includes('REQUEST APPROVED') ||
                        msg.message.includes('REQUEST REJECTED')
                    );
                    if (isDeleteLog) return;

                    const isMe = msg.sender_id == {{ auth()->id() }};
                    const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                    let processedMessage = msg.message;
                    
                    // Pre-check for expiration to prevent UI "blinking"
                    if (processedMessage && processedMessage.includes('clearance-container')) {
                        const expiryMatch = processedMessage.match(/data-expires-at='(\d+)'/);
                        if (expiryMatch) {
                            const expiresAt = parseInt(expiryMatch[1]);
                            if (Date.now() >= expiresAt) {
                                // Session is already expired, modify HTML before rendering
                                processedMessage = processedMessage.replace(/class='clearance-action-btn'/g, "class='clearance-action-btn expired' style='display: inline-block; background: #94a3b8; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; pointer-events: none;'");
                                processedMessage = processedMessage.replace(/Open Editor Now/g, "Session Expired");
                                processedMessage = processedMessage.replace(/Delete Batch Permanently/g, "Session Expired");
                                processedMessage = processedMessage.replace(/class='clearance-timer-notice' style='[^']*'/g, "class='clearance-timer-notice' style='background: rgba(239, 68, 68, 0.1); padding: 10px; border-radius: 8px; font-size: 0.85rem; font-weight: 800; color: #ef4444; margin-bottom: 15px;'");
                                processedMessage = processedMessage.replace(/⚠️ SECURITY NOTICE: This clearance expires in <span class='timer-seconds'>\d+<\/span>s./g, "❌ SECURITY NOTICE: This clearance has EXPIRED.");
                            } else {
                                // Session is active, inject correct remaining time to prevent "restart" flicker
                                const timeLeft = Math.floor((expiresAt - Date.now()) / 1000);
                                processedMessage = processedMessage.replace(/<span class='timer-seconds'>\d+<\/span>/, `<span class='timer-seconds'>${timeLeft}</span>`);
                            }
                        }
                    }

                 // Pre-process rollback notifications — strip display:none so they show
                    if (processedMessage && processedMessage.includes('rollback-notification')) {
                        processedMessage = processedMessage.replace(/style='display: none;/g, "style='display: block;");
                    }

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

                    // Rollback cards: transparent bubble so the styled card is the full content
                    const isRollback = processedMessage && processedMessage.includes('rollback-notification');
                    const bubbleStyle = isRollback ? 'padding: 0; background: transparent; border: none; box-shadow: none; overflow: visible;' : '';

                    html += `
                        <div class="comms-group ${isMe ? 'me' : 'recipient'}">
                            <div class="comms-bubble" style="${bubbleStyle}">
                                ${processedMessage ? `<span style="word-break: break-word;">${processedMessage}</span>` : ''}
                                ${msg.attachment ? `
                                    <a href="{{ asset('storage') }}/${msg.attachment}" target="_blank" class="attachment-pill">
                                        <i data-lucide="file-text" style="width: 16px;"></i>
                                        <span>${msg.attachment_name || 'Document'}</span>
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
            .catch(err => /* console print removed */);
            
        // Mark as read
        const readUrl = `{{ route('api.messages.read', ['userId' => 'PLACEHOLDER'], false) }}`.replace('PLACEHOLDER', activeUserId);
        fetch(readUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
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
                /* console print removed */
                alert('Transmission failed. Check network integrity.');
            }
        })
        .catch(err => {
            /* console print removed */
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
                    
                    if (activeUserId == userId) {
                        const statusDot = document.getElementById('statusDot');
                        if (statusDot) statusDot.style.background = isOnline ? '#10b981' : '#94a3b8';
                    }
                });
            });
    }

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
                        actionBtn.innerText = 'Session Expired';
                        
                        let link = container.querySelector('.request-again-link');
                        if (!link) {
                            link = document.createElement('a');
                            link.href = "{{ url('/received-items') }}";
                            link.className = 'request-again-link';
                            link.style.display = 'block';
                            link.style.marginTop = '10px';
                            link.style.fontSize = '0.8rem';
                            link.style.fontWeight = '800';
                            link.style.color = 'var(--primary)';
                            link.style.textDecoration = 'underline';
                            link.innerText = 'Go to console to request again';
                            container.appendChild(link);
                        }
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
    const styleBadge = document.createElement('style');
    styleBadge.innerHTML = `
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
    document.head.appendChild(styleBadge);
</script>
@endsection
