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
                    <div style="font-weight: 800; color: var(--text-main); font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 2px;">{{ $admin->name }}</div>
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
                    <div style="font-weight: 800; color: var(--text-main); font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 2px;">{{ $colleague->name }}</div>
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
                    <h3 id="activeName" style="font-size: 1.2rem; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.02em;">Name</h3>
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
        pollInterval = setInterval(fetchMessages, 5000);

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
                
                let html = `
                    <div class="comms-group system">
                        <div class="comms-bubble">Transmission protocol initialized. Node NA-${activeUserId.toString().padStart(3, '0')} connected.</div>
                        <div class="comms-meta">SYSTEM &bull; SECURE LINE</div>
                    </div>
                `;

                data.forEach(msg => {
                    const isMe = msg.sender_id == {{ auth()->id() }};
                    const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    
                    html += `
                        <div class="comms-group ${isMe ? 'me' : 'recipient'}">
                            <div class="comms-bubble">
                                ${msg.message || ''}
                                ${msg.attachment ? `
                                    <a href="{{ asset('storage') }}/${msg.attachment}" target="_blank" class="attachment-pill">
                                        <i data-lucide="file-text" style="width: 16px;"></i>
                                        <span>${msg.attachment_name || 'Document'}</span>
                                    </a>
                                ` : ''}
                            </div>
                            <div class="comms-meta">${isMe ? 'YOU' : 'SENDER'} &bull; ${time}</div>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
                if (typeof lucide !== 'undefined') lucide.createIcons();
                
                if (wasAtBottom) {
                    container.scrollTop = container.scrollHeight;
                }
            })
            .catch(err => console.error('Comms Error:', err));
            
        // Mark as read
        fetch(`{{ url('/api/messages') }}/${activeUserId}/read`, {
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
                console.error('Transmission failed:', data.message);
                alert('Transmission failed. Check network integrity.');
            }
        })
        .catch(err => {
            console.error('Network Error:', err);
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
        // Sync Online Statuses
        fetch("{{ route('api.online-statuses') }}")
            .then(res => res.json())
            .then(statuses => {
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

    // Initial counts and polling
    updateUnreadCounts();
    setInterval(updateUnreadCounts, 10000);
</script>
@endsection
