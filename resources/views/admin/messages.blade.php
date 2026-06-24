@extends('layouts.admin')

@section('title', 'Communication Hub')

@section('content')
<div class="animate-slide-up" style="height: calc(100vh - 180px); display: flex; gap: 2rem; padding: 1.5rem;">
    <!-- Sidebar: Directory -->
    <div class="glass-card" style="width: 380px; display: flex; flex-direction: column; padding: 0; overflow: hidden; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04);">
        <div style="padding: 2rem; border-bottom: 1px solid var(--border-color); background: linear-gradient(145deg, var(--bg-card), var(--bg-main));">
            <h3 style="font-size: 1.25rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.5rem; letter-spacing: -0.02em;">Staff <span style="color: #4f46e5;">Directory</span></h3>
            <div class="search-vault">
                <i data-lucide="search"></i>
                <input type="text" id="networkSearch" placeholder="Search users..." oninput="filterNetwork()">
            </div>
        </div>

        <div style="flex: 1; overflow-y: auto; padding: 1.25rem;">
            <div style="font-size: 0.75rem; font-weight: 900; color: var(--primary); text-transform: uppercase; letter-spacing: 0.15em; padding: 0 1rem 1rem 1rem; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="users" style="width: 14px;"></i>
                Operational Units
            </div>
            @forelse($users as $user)
            <div class="network-item" id="user-{{ $user->id }}"
                data-user-id="{{ $user->id }}"
                data-user-name="{{ $user->name }}"
                data-user-role="{{ $user->role }}"
                data-user-avatar="{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}"
                onclick="selectChat({{ $user->id }})"
                style="display: flex; align-items: center; gap: 14px; padding: 1.25rem; border-radius: 18px; cursor: pointer; transition: 0.3s; margin-bottom: 6px; border: 1px solid transparent; position: relative;">
                <div style="position: relative;">
                    @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" style="width: 48px; height: 48px; border-radius: 14px; object-fit: cover; border: 2px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    @else
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.2rem; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);">{{ substr($user->name, 0, 1) }}</div>
                    @endif
                    <div style="position: absolute; bottom: -2px; right: -2px; width: 14px; height: 14px; background: {{ $user->is_online ? '#10b981' : '#94a3b8' }}; border: 3px solid var(--bg-card); border-radius: 50%;"></div>
                </div>
                <div style="flex: 1; overflow: hidden;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 2px; min-width: 0;">
                        <div style="font-weight: 800; color: var(--text-main); font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex-shrink: 1; min-width: 0;">{{ $user->name }}</div>
                        <div class="unread-badge" id="badge-{{ $user->id }}" style="display: none; background: #ef4444; color: white; font-size: 0.65rem; font-weight: 900; min-width: 18px; height: 18px; border-radius: 9px; align-items: center; justify-content: center; padding: 0 5px; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4); border: 1.5px solid white; flex-shrink: 0;">0</div>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;">{{ $user->role }}</div>
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                <i data-lucide="users-2" style="width: 32px; opacity: 0.2; margin-bottom: 1rem;"></i>
                <p style="font-size: 0.85rem; font-weight: 700;">No User Records</p>
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
            <h2 style="font-size: 1.75rem; font-weight: 900; color: var(--text-main); margin-bottom: 0.75rem; letter-spacing: -0.03em;">Messaging Hub</h2>
            <p style="color: var(--text-muted); font-size: 1rem; font-weight: 500; text-align: center; max-width: 360px; line-height: 1.6;">Select a staff member from the directory to start a secure chat session.</p>
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
                        <textarea id="msgContent" placeholder="Type your message here..." rows="1"></textarea>
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

<!-- Strategic Oversight Side Terminal -->
<div id="oversightOverlay" onclick="window.closeOversightPanel()"></div>
<div id="oversightSidePanel">
    <div id="oversightPanelContent" style="height: 100%; display: flex; flex-direction: column;">
        <!-- Dynamically Populated -->
    </div>
</div>

<style>
    /* Strategic Oversight Floating Popover */
    #oversightSidePanel {
        position: fixed;
        top: 52%;
        left: 50%;
        width: 95%;
        max-width: 1240px;
        height: 85vh;
        background: #f8fafc;
        z-index: 100000 !important;
        box-shadow: 0 30px 100px rgba(15, 23, 42, 0.3), 0 0 0 1px rgba(15, 23, 42, 0.05);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        border-radius: 28px;
        transform: translate(-50%, -47%) scale(0.95);
        opacity: 0;
        visibility: hidden;
        overflow: hidden;
    }

    #oversightSidePanel.open {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
        visibility: visible;
    }

    #oversightOverlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        z-index: 99999 !important;
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    #oversightOverlay.show {
        display: block;
        opacity: 1;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    /* Admin view logic */
    .admin-view {
        display: block !important;
    }

    .personnel-view {
        display: none !important;
    }

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

    .comms-btn i {
        width: 20px;
    }

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
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
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
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .archive-trigger {
        cursor: pointer;
        color: var(--primary);
        opacity: 0;
        transition: 0.3s;
        text-transform: none;
        letter-spacing: normal;
        font-weight: 800;
        font-size: 0.65rem;
    }

    .comms-group:hover .archive-trigger {
        opacity: 0.6;
    }

    .archive-trigger:hover {
        opacity: 1 !important;
    }

    .attachment-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.1);
        padding: 10px 14px;
        border-radius: 12px;
        margin-top: 10px;
        text-decoration: none;
        color: inherit;
        font-size: 0.85rem;
    }

    .recipient .attachment-pill {
        background: var(--bg-main);
    }

    @keyframes float-bubble {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-15px);
        }
    }

    .loader-mini {
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Bottom Sheet Style for Previews */
    .preview-bottom-sheet {
        align-items: flex-end !important;
        padding-bottom: 0 !important;
    }

    .preview-bottom-sheet-popup {
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        margin-bottom: 0 !important;
        animation: sheet-slide-up 0.5s cubic-bezier(0.4, 0, 0.2, 1) !important;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 -20px 60px rgba(0, 0, 0, 0.15) !important;
    }

    @keyframes sheet-slide-up {
        from {
            transform: translateY(100%);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<script>
    let activeUserId = null;
    let pollInterval = null;
    let countInterval = null;
    let onlineStatuses = {};
    let previousMessageCount = null;

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
        if (!name) {
            const item = document.getElementById(`user-${userId}`);
            if (item) {
                name = item.getAttribute('data-user-name') || '';
                role = item.getAttribute('data-user-role') || '';
                avatar = item.getAttribute('data-user-avatar') || '';
            }
        }
        activeUserId = userId;
        previousMessageCount = null;
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

        const fetchUrl = `{{ route('api.messages.fetch', ['userId' => 'PLACEHOLDER'], false) }}`.replace('PLACEHOLDER', activeUserId) + '?_t=' + Date.now();
        fetch(fetchUrl)
            .then(res => {
                if (!res.ok) throw new Error('Secure line interrupted');
                return res.json();
            })
            .then(data => {
                const container = document.getElementById('terminalOutput');
                const wasAtBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;

                // Self-heal: If we fetched messages and there are unread ones from the active user,
                // immediately mark them as read so the db status updates and the alarm ceases in other tabs.
                const hasUnread = data.some(msg => msg.sender_id == activeUserId && !msg.read_at);
                if (hasUnread) {
                    const readUrl = `{{ route('api.messages.read', ['userId' => 'PLACEHOLDER'], false) }}`.replace('PLACEHOLDER', activeUserId);
                    fetch(readUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(() => updateUnreadCounts());
                }

                if (previousMessageCount !== null && data.length > previousMessageCount) {
                    const lastMsg = data[data.length - 1];
                    if (lastMsg && lastMsg.sender_id != {{ auth()->id() }}) {
                        if (typeof window.playNotificationSound === 'function') {
                            window.playNotificationSound('receive');
                        }
                    }
                }
                previousMessageCount = data.length;

                let html = '';

                data.forEach(msg => {
                    // Skip messages that are automated OR marked strictly for personnel
                    // ONLY skip automated messages that are strictly for personnel OR general system noise
                    const isStrictlyPersonnel = msg.message && msg.message.includes('personnel-view') && !msg.message.includes('admin-view');
                    const isSraApproval = msg.message && (msg.message.includes('sra-approval-msg') || msg.message.includes('sra-approval-card') || msg.message.includes('SRA APPROVAL REQUIRED'));
                    const isEditReq = msg.message && (msg.message.includes('edit-req-msg') || msg.message.includes('AUTHORIZATION REQUIRED'));
                    const isReconciliation = msg.message && (msg.message.includes('STOCK RECONCILIATION') || msg.message.includes('BATCH STOCK RECONCILIATION') || msg.message.includes('verification-approval-card'));
                    const isSubmissionStatus = msg.message && (msg.message.includes('ENTRY SUBMISSION LOGGED') || msg.message.includes('RECOVERY SUBMITTED') || msg.message.includes('REMAINDER SUBMITTED') || msg.message.includes('DISBURSEMENT REQUEST LOGGED'));
                    const isAltProposal = msg.message && (
                        msg.message.includes('SUGGESTED QUANTITY PROPOSED') || 
                        msg.message.includes('ALTERNATIVE ITEM PROPOSED') || 
                        msg.message.includes('suggested quantities') ||
                        msg.message.includes('alternative items')
                    );

                    if (msg.is_automated && (isAltProposal || (!isSraApproval && !isEditReq && !isReconciliation && (isStrictlyPersonnel || isSubmissionStatus)))) {
                        return;
                    }

                    // Dynamically simplify Stock Reconciliation approval cards for previous legacy messages in the database
                    if (isReconciliation && msg.message) {
                        try {
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = msg.message;

                            const actionsDiv = tempDiv.querySelector('[id^="verification-actions-"]');
                            if (actionsDiv) {
                                const reqId = actionsDiv.id.replace('verification-actions-', '');

                                // Remove details sections
                                tempDiv.querySelectorAll('table').forEach(tbl => {
                                    const parentTbl = tbl.closest('div');
                                    if (parentTbl) parentTbl.remove();
                                    else tbl.remove();
                                });

                                const detailsContainer = tempDiv.querySelector('div[style*="flex-direction: column"][style*="gap: 10px"]');
                                if (detailsContainer) {
                                    const lines = detailsContainer.querySelectorAll('div');
                                    lines.forEach((line, idx) => {
                                        if (idx > 0) { // Keep only Verifier (first item)
                                            line.remove();
                                        }
                                    });
                                    detailsContainer.style.marginBottom = '15px';
                                }

                                const hasStatusBadge = actionsDiv.querySelector('div[style*="font-weight: 900"], div[style*="font-weight: 950"]') || 
                                                       actionsDiv.textContent.includes('VERIFICATION APPROVED') || 
                                                       actionsDiv.textContent.includes('VERIFICATION REJECTED');

                                if (!hasStatusBadge) {
                                    const isBatch = msg.message.includes('BATCH');
                                    const btnLabel = isBatch ? 'Preview Batch Details' : 'Preview Details';
                                    actionsDiv.innerHTML = `
                                        <button class="reconciliation-preview-btn" data-reconciliation-req-id="${reqId}" style="width: 100%; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; padding: 12px; border-radius: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;">
                                            <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'></path><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'></path></svg>
                                            ${btnLabel}
                                        </button>
                                    `;
                                }
                            }
                            msg.message = tempDiv.innerHTML;
                        } catch (e) {
                            /* console print removed */
                        }
                    }

                    // Dynamically simplify SRA/Remainder approval cards for previous legacy messages in the database
                    if (isSraApproval && msg.message) {
                        try {
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = msg.message;

                            // Remove metadata details section (excluding the actions div)
                            tempDiv.querySelectorAll('.sra-approval-card > div[style*="flex-direction: column"]').forEach(div => {
                                if (!div.id.startsWith('sra-creation-actions-')) {
                                    div.remove();
                                }
                            });

                            // Handle sra-creation-actions cleanups
                            const actionsDiv = tempDiv.querySelector('[id^="sra-creation-actions-"]');
                            if (actionsDiv) {
                                const previewBtn = tempDiv.querySelector('.entry-preview-btn, .remainder-preview-btn');

                                // Check if this card has already been approved/rejected (has status badges)
                                const hasStatusBadge = actionsDiv.querySelector('div[style*="font-weight: 950"], div[style*="font-weight: 900"], a[href*="sra"]');
                                if (!hasStatusBadge) {
                                    // If not approved/rejected, empty the actionsDiv and put the preview button inside it
                                    actionsDiv.innerHTML = '';
                                    if (previewBtn) {
                                        previewBtn.style.marginBottom = '0';
                                        actionsDiv.appendChild(previewBtn);
                                    }
                                } else {
                                    // If already approved/rejected, keep the badge, but remove any preview buttons outside it
                                    if (previewBtn) previewBtn.remove();
                                }
                            }
                            msg.message = tempDiv.innerHTML;
                        } catch (e) {
                            /* console print removed */
                        }
                    }

                    const isMe = msg.sender_id == {{ auth()->id() }};
                    const time = new Date(msg.created_at).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

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
                            <div class="comms-meta">
                                <span>${isMe ? 'YOU' : 'SENDER'} &bull; ${time}</span>
                                <span onclick="window.archiveMessage(${msg.id})" class="archive-trigger">
                                    <i data-lucide="archive" style="width: 10px; height: 10px; display: inline-block; vertical-align: -1px;"></i> Archive
                                </span>
                            </div>
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
            .catch(err => { /* console print removed */ });


        // Mark as read
        const readUrl = `{{ route('api.messages.read', ['userId' => 'PLACEHOLDER'], false) }}`.replace('PLACEHOLDER', activeUserId);
        fetch(readUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => updateUnreadCounts());
    }

    function updateUnreadCounts() {
        fetch("{{ route('api.unread-counts', [], false) }}?_t=" + Date.now())
            .then(res => res.json())
            .then(counts => {
                let activeCount = 0;
                document.querySelectorAll('.unread-badge').forEach(badge => {
                    const userId = badge.id.replace('badge-', '');
                    const count = counts[userId] || 0;

                    if (count > 0) {
                        badge.textContent = count;
                        badge.style.display = 'flex';
                        if (userId == activeUserId) {
                            activeCount = count;
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
        fetch("{{ route('api.online-statuses', [], false) }}?_t=" + Date.now())
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
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
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
                    if (typeof window.playNotificationSound === 'function') {
                        window.playNotificationSound('sent');
                    }
                    fetchMessages();
                } else {
                    alert('Transmission failed: ' + (data.message || 'Unknown protocol violation'));
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

    // Process SRA Creation Approval logic
    window.processSraCreationApproval = function(id, status, btnElement) {
        if (status === 'rejected') {
            let personnel = 'User';
            const userB = document.querySelector('#oversightPanelContent p b');
            if (userB) {
                personnel = userB.textContent.trim();
            } else {
                const bubble = btnElement ? btnElement.closest('.comms-bubble') : null;
                const bubbleHtml = bubble ? bubble.innerHTML : '';
                const pMatch = bubbleHtml.match(/(?:Personnel|User)\s+<b>(.*?)<\/b>/i);
                personnel = (pMatch && pMatch[1]) ? pMatch[1].trim() : 'User';
            }

            Swal.fire({
                html: `
                    <div style="text-align: left;">
                        <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); margin: -1.25em -1.25em 1.5em; padding: 2rem 2rem 1.5rem; border-radius: 4px 4px 0 0; position: relative; overflow: hidden;">
                            <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: rgba(255,255,255,0.06); border-radius: 50%;"></div>
                            <div style="position: absolute; bottom: -30px; left: -10px; width: 80px; height: 80px; background: rgba(255,255,255,0.04); border-radius: 50%;"></div>
                            <div style="display: flex; align-items: center; gap: 14px; position: relative;">
                                <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.15); border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg style="width: 26px; height: 26px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </div>
                                <div>
                                    <div style="font-size: 0.7rem; font-weight: 800; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 3px;">Admin Action Required</div>
                                    <div style="font-size: 1.3rem; font-weight: 900; color: white; letter-spacing: -0.02em;">Reject Stock Entry</div>
                                </div>
                            </div>
                        </div>

                        <p style="font-size: 0.9rem; color: #64748b; line-height: 1.6; margin-bottom: 1.25rem; padding: 0 0.25rem;">
                            Provide a clear reason for rejecting the submission from <b style="color: #0f172a;">${personnel}</b>. This will be sent to the user immediately.
                        </p>

                        <textarea id="swal-reject-reason" placeholder="e.g., Incorrect quantity specified, missing documentation, requires further verification..." style="width: 100%; min-height: 110px; font-size: 0.9rem; border-radius: 14px; border: 2px solid #f1f5f9; padding: 1rem 1.25rem; font-family: inherit; resize: vertical; outline: none; transition: border-color 0.3s; box-sizing: border-box; color: #0f172a; background: #f8fafc;" onfocus="this.style.borderColor='#ef4444'; this.style.boxShadow='0 0 0 4px rgba(239,68,68,0.08)'" onblur="this.style.borderColor='#f1f5f9'; this.style.boxShadow='none'"></textarea>

                        <div style="margin-top: 1rem; padding: 10px 14px; background: rgba(245, 158, 11, 0.07); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 10px; display: flex; align-items: center; gap: 10px;">
                            <svg style="width: 16px; height: 16px; color: #d97706; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span style="font-size: 0.78rem; font-weight: 700; color: #92400e;">A reason is mandatory and will be permanently logged in the audit system.</span>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '&#10005; &nbsp;Confirm Rejection',
                cancelButtonText: 'Go Back',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                focusConfirm: false,
                customClass: {
                    popup: 'swal-decline-popup',
                    confirmButton: 'swal-decline-confirm-btn',
                    cancelButton: 'swal-decline-cancel-btn',
                },
                didOpen: () => {
                    if (!document.getElementById('swal-decline-styles')) {
                        const style = document.createElement('style');
                        style.id = 'swal-decline-styles';
                        style.textContent = `.swal-decline-popup { border-radius: 24px !important; overflow: hidden !important; padding: 1.25em !important; } .swal-decline-confirm-btn { border-radius: 10px !important; font-weight: 800 !important; padding: 12px 24px !important; font-size: 0.9rem !important; } .swal-decline-cancel-btn { border-radius: 10px !important; font-weight: 700 !important; padding: 12px 24px !important; font-size: 0.9rem !important; } .swal2-actions { gap: 10px !important; margin-top: 1.5rem !important; }`;
                        document.head.appendChild(style);
                    }
                },
                preConfirm: () => {
                    const reason = document.getElementById('swal-reject-reason').value.trim();
                    if (!reason) {
                        Swal.showValidationMessage('<span style="font-size:0.85rem;">⚠ A justification is required to reject this entry.</span>');
                        return false;
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
        const sideActionsDiv = document.getElementById(`oversight-actions-${id}`);
        if (sideActionsDiv) {
            const btns = sideActionsDiv.querySelectorAll('button');
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
                if (data.success) {
                    if (typeof window.playNotificationSound === 'function') {
                        window.playNotificationSound('sent');
                    }
                    const color = status === 'approved' ? '#10b981' : '#dc2626';
                    const bgColor = status === 'approved' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(220, 38, 38, 0.1)';
                    const text = status === 'approved' ?
                        (data.is_remainder ? 'REMAINDER COMMITTED' : 'APPROVED & SAVED') :
                        'REJECTED';

                    let html = `<div style="padding: 12px 20px; border-radius: 12px; background: ${bgColor}; color: ${color}; font-weight: 900; border: 1.5px solid ${color}; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.85rem; letter-spacing: 0.02em;">
                    <i data-lucide="${status === 'approved' ? 'check-circle' : 'alert-circle'}" style="width: 16px;"></i> ${text}
                </div>`;

                    if (status === 'approved' && data.batch_id) {
                        const printUrl = `{{ url('/received-items') }}/${data.batch_id}/sra`;
                        html += `<br><a href="${printUrl}" target="_blank" style="display: inline-block; background: #4f46e5; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: 800; font-size: 0.75rem; margin-top: 8px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);">Download / Print SRA</a>`;
                    }

                    if (actionsDiv) {
                        actionsDiv.innerHTML = html;
                    }

                    if (sideActionsDiv) {
                        sideActionsDiv.innerHTML = html;
                    }

                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    const toastMsg = data.is_remainder ? 'Remainder items committed to stock.' : `Stock entry ${status}.`;
                    showToast('Success', toastMsg, 'success');

                    if (sideActionsDiv) {
                        setTimeout(() => {
                            window.closeOversightPanel();
                        }, 1200);
                    }
                } else {
                    showToast('Action Failed', data.message || 'Error processing request', 'error');
                    if (actionsDiv) {
                        const btns = actionsDiv.querySelectorAll('button');
                        btns.forEach(b => b.disabled = false);
                    }
                    if (sideActionsDiv) {
                        const btns = sideActionsDiv.querySelectorAll('button');
                        btns.forEach(b => b.disabled = false);
                    }
                    btnElement.innerText = status === 'approved' ? 'Approve' : 'Reject';
                }
            })
            .catch(err => {
                /* console print removed */
                const errMsg = err.message || 'An unexpected error occurred during SRA authorization.';
                showToast('System Error', errMsg, 'error');
                btnElement.innerText = status === 'approved' ? 'Approve & Save' : 'Reject';
                btnElement.disabled = false;
            });
    };



    window.archiveMessage = function(id) {
        Swal.fire({
            title: 'Move to Archive?',
            text: 'This message will be transferred to the secure archive and hidden from this active conversation.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: 'var(--primary)',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Yes, Archive',
            cancelButtonText: 'Cancel',
            background: '#ffffff',
            borderRadius: '24px'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('/admin/archive/message') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        if (typeof showToast === 'function') {
                            showToast('Archived', 'Message successfully moved to archive.', 'success');
                        }
                        fetchMessages();
                    }
                });
            }
        });
    }

    // Process Edit Request logic
    window.processRecoveryApproval = function(id, status, btnElement) {
        if (status === 'rejected') {
            Swal.fire({
                html: `
                    <div style="text-align: left;">
                        <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); margin: -1.25em -1.25em 1.5em; padding: 2rem 2rem 1.5rem; border-radius: 4px 4px 0 0; position: relative; overflow: hidden;">
                            <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: rgba(255,255,255,0.06); border-radius: 50%;"></div>
                            <div style="position: absolute; bottom: -30px; left: -10px; width: 80px; height: 80px; background: rgba(255,255,255,0.04); border-radius: 50%;"></div>
                            <div style="display: flex; align-items: center; gap: 14px; position: relative;">
                                <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.15); border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg style="width: 26px; height: 26px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </div>
                                <div>
                                    <div style="font-size: 0.7rem; font-weight: 800; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 3px;">Oversight Action</div>
                                    <div style="font-size: 1.3rem; font-weight: 900; color: white; letter-spacing: -0.02em;">Reject Recovery</div>
                                </div>
                            </div>
                        </div>

                        <p style="font-size: 0.9rem; color: #64748b; line-height: 1.6; margin-bottom: 1.25rem; padding: 0 0.25rem;">
                            State the reason for rejecting this asset re-integration. This will be transmitted to the user.
                        </p>

                        <textarea id="swal-recovery-reject-reason" placeholder="e.g., Return quantity discrepancy, incorrect documentation, item not verified..." style="width: 100%; min-height: 110px; font-size: 0.9rem; border-radius: 14px; border: 2px solid #f1f5f9; padding: 1rem 1.25rem; font-family: inherit; resize: vertical; outline: none; transition: border-color 0.3s; box-sizing: border-box; color: #0f172a; background: #f8fafc;" onfocus="this.style.borderColor='#ef4444'; this.style.boxShadow='0 0 0 4px rgba(239,68,68,0.08)'" onblur="this.style.borderColor='#f1f5f9'; this.style.boxShadow='none'"></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '&#10005; &nbsp;Confirm Rejection',
                cancelButtonText: 'Go Back',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                preConfirm: () => {
                    const reason = document.getElementById('swal-recovery-reject-reason').value.trim();
                    if (!reason) {
                        Swal.showValidationMessage('<span style="font-size:0.85rem;">⚠ A justification is required.</span>');
                        return false;
                    }
                    return reason;
                }
            }).then(result => {
                if (result.isConfirmed) {
                    _doProcessRecovery(id, status, btnElement, result.value);
                }
            });
        } else {
            _doProcessRecovery(id, status, btnElement, null);
        }
    };

    function _doProcessRecovery(id, status, btnElement, reason) {
        const actionsDiv = document.getElementById(`recovery-actions-${id}`);
        if (actionsDiv) {
            const btns = actionsDiv.querySelectorAll('button');
            btns.forEach(b => b.disabled = true);
        }
        const sideActionsDiv = document.getElementById(`oversight-recovery-actions-${id}`);
        if (sideActionsDiv) {
            const btns = sideActionsDiv.querySelectorAll('button');
            btns.forEach(b => b.disabled = true);
        }

        btnElement.innerHTML = '<i data-lucide="loader" class="animate-spin" style="width:14px;"></i> Processing...';
        if (typeof lucide !== 'undefined') lucide.createIcons();

        fetch(`{{ url('/recovery') }}/${id}/process`, {
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
                if (!res.ok) throw new Error('Server protocol violation');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof window.playNotificationSound === 'function') {
                        window.playNotificationSound('sent');
                    }
                    const badgeHtml = `<div style="padding: 12px 20px; border-radius: 12px; background: ${status === 'approved' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(220, 38, 38, 0.1)'}; color: ${status === 'approved' ? '#10b981' : '#dc2626'}; font-weight: 900; border: 1.5px solid ${status === 'approved' ? '#10b981' : '#dc2626'}; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.85rem;">
                        <i data-lucide="${status === 'approved' ? 'check-circle' : 'alert-circle'}" style="width: 16px;"></i> RECOVERY ${status.toUpperCase()}
                    </div>`;

                    const actionsDiv = document.getElementById(`recovery-actions-${id}`);
                    if (actionsDiv) {
                        actionsDiv.innerHTML = badgeHtml;
                    }
                    const sideActionsDiv = document.getElementById(`oversight-recovery-actions-${id}`);
                    if (sideActionsDiv) {
                        sideActionsDiv.innerHTML = badgeHtml;
                    }
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    showToast('Recovery Updated', `Recovery has been ${status}.`, 'success');
                } else {
                    showToast('Update Failed', data.message || 'Error processing recovery', 'error');
                    btnElement.innerText = status === 'approved' ? 'Approve Re-integration' : 'Reject';
                    btnElement.disabled = false;
                }
            })
            .catch(err => {
                /* console print removed */
                showToast('System Error', 'Could not complete the re-integration process.', 'error');
                btnElement.innerText = status === 'approved' ? 'Approve Re-integration' : 'Reject';
                btnElement.disabled = false;
            });
    }

    window.processVerificationApproval = function(id, status, btnElement) {
        if (status === 'rejected') {
            Swal.fire({
                html: `
                    <div style="text-align: left;">
                        <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); margin: -1.25em -1.25em 1.5em; padding: 2rem 2rem 1.5rem; border-radius: 4px 4px 0 0; position: relative; overflow: hidden;">
                            <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: rgba(255,255,255,0.06); border-radius: 50%;"></div>
                            <div style="position: absolute; bottom: -30px; left: -10px; width: 80px; height: 80px; background: rgba(255,255,255,0.04); border-radius: 50%;"></div>
                            <div style="display: flex; align-items: center; gap: 14px; position: relative;">
                                <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.15); border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg style="width: 26px; height: 26px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </div>
                                <div>
                                    <div style="font-size: 0.7rem; font-weight: 800; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 3px;">Oversight Action</div>
                                    <div style="font-size: 1.3rem; font-weight: 900; color: white; letter-spacing: -0.02em;">Reject Verification</div>
                                </div>
                            </div>
                        </div>

                        <p style="font-size: 0.9rem; color: #64748b; line-height: 1.6; margin-bottom: 1.25rem; padding: 0 0.25rem;">
                            State the reason for rejecting this stock verification and reconciliation. This will be transmitted to the user.
                        </p>

                        <textarea id="swal-verification-reject-reason" placeholder="e.g., Physical count does not match batch checks, verification requires double sign-off..." style="width: 100%; min-height: 110px; font-size: 0.9rem; border-radius: 14px; border: 2px solid #f1f5f9; padding: 1rem 1.25rem; font-family: inherit; resize: vertical; outline: none; transition: border-color 0.3s; box-sizing: border-box; color: #0f172a; background: #f8fafc;" onfocus="this.style.borderColor='#ef4444'; this.style.boxShadow='0 0 0 4px rgba(239,68,68,0.08)'" onblur="this.style.borderColor='#f1f5f9'; this.style.boxShadow='none'"></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '&#10005; &nbsp;Confirm Rejection',
                cancelButtonText: 'Go Back',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                preConfirm: () => {
                    const reason = document.getElementById('swal-verification-reject-reason').value.trim();
                    if (!reason) {
                        Swal.showValidationMessage('<span style="font-size:0.85rem;">⚠ A justification is required.</span>');
                        return false;
                    }
                    return reason;
                }
            }).then(result => {
                if (result.isConfirmed) {
                    _doProcessVerification(id, status, btnElement, result.value);
                }
            });
        } else {
            _doProcessVerification(id, status, btnElement, null);
        }
    };

    function _doProcessVerification(id, status, btnElement, reason) {
        const actionsDiv = document.getElementById(`verification-actions-${id}`);
        if (actionsDiv) {
            const btns = actionsDiv.querySelectorAll('button');
            btns.forEach(b => b.disabled = true);
        }
        const sideActionsDiv = document.getElementById(`oversight-verification-actions-${id}`);
        if (sideActionsDiv) {
            const btns = sideActionsDiv.querySelectorAll('button');
            btns.forEach(b => b.disabled = true);
        }

        btnElement.innerHTML = '<i data-lucide="loader" class="animate-spin" style="width:14px;"></i> Processing...';
        if (typeof lucide !== 'undefined') lucide.createIcons();

        fetch(`{{ url('/verification') }}/${id}/process`, {
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
                if (!res.ok) throw new Error('Server protocol violation');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof window.playNotificationSound === 'function') {
                        window.playNotificationSound('sent');
                    }
                    const color = status === 'approved' ? '#10b981' : '#dc2626';
                    const bgColor = status === 'approved' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(220, 38, 38, 0.1)';
                    const htmlBadge = `<div style="padding: 12px 20px; border-radius: 12px; background: ${bgColor}; color: ${color}; font-weight: 900; border: 1.5px solid ${color}; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.85rem;">
                        <i data-lucide="${status === 'approved' ? 'check-circle' : 'alert-circle'}" style="width: 16px;"></i> VERIFICATION ${status.toUpperCase()}
                    </div>`;

                    if (actionsDiv) {
                        actionsDiv.innerHTML = htmlBadge;
                    }
                    if (sideActionsDiv) {
                        sideActionsDiv.innerHTML = htmlBadge;
                    }
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    showToast('Inventory Reconciled', `Stock verification has been ${status}.`, 'success');

                    if (sideActionsDiv) {
                        setTimeout(() => {
                            window.closeOversightPanel();
                        }, 1200);
                    }
                } else {
                    showToast('Update Failed', data.message || 'Error processing verification', 'error');
                    if (actionsDiv) {
                        const btns = actionsDiv.querySelectorAll('button');
                        btns.forEach(b => b.disabled = false);
                    }
                    if (sideActionsDiv) {
                        const btns = sideActionsDiv.querySelectorAll('button');
                        btns.forEach(b => b.disabled = false);
                    }
                    btnElement.innerText = status === 'approved' ? 'Approve' : 'Reject';
                }
            })
            .catch(err => {
                /* console print removed */
                showToast('System Error', 'Could not complete the verification process.', 'error');
                if (actionsDiv) {
                    const btns = actionsDiv.querySelectorAll('button');
                    btns.forEach(b => b.disabled = false);
                }
                if (sideActionsDiv) {
                    const btns = sideActionsDiv.querySelectorAll('button');
                    btns.forEach(b => b.disabled = false);
                }
                btnElement.innerText = status === 'approved' ? 'Approve' : 'Reject';
            });
    }

    function _renderRecoverySheet(data, reqId) {
        const item = data.item;

        let footerHtml = '';
        if (data.status === 'pending') {
            footerHtml = `
                <div id="oversight-recovery-actions-${reqId}" style="background: white; border-top: 1px solid #e2e8f0; padding: 1.5rem 3rem; display: flex; justify-content: flex-end; align-items: center; gap: 1rem; border-radius: 0 0 28px 28px; flex-shrink: 0;">
                    <button onclick="window.processRecoveryApproval(${reqId}, 'rejected', this)" style="background: #ef4444; color: white; border: none; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                        <i data-lucide="x-circle" style="width: 18px;"></i> Reject
                    </button>
                    <button onclick="window.processRecoveryApproval(${reqId}, 'approved', this)" style="background: #10b981; color: white; border: none; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                        <i data-lucide="check-circle" style="width: 18px;"></i> Approve Re-integration
                    </button>
                </div>
            `;
        } else {
            const isApproved = data.status === 'approved';
            const color = isApproved ? '#10b981' : '#dc2626';
            const bgColor = isApproved ? 'rgba(16, 185, 129, 0.1)' : 'rgba(220, 38, 38, 0.1)';
            const labelText = isApproved ? 'RECOVERY APPROVED' : 'RECOVERY REJECTED';
            footerHtml = `
                <div style="background: white; border-top: 1px solid #e2e8f0; padding: 1.5rem 3rem; display: flex; justify-content: center; align-items: center; gap: 1rem; border-radius: 0 0 28px 28px; flex-shrink: 0;">
                    <div style="padding: 12px 24px; border-radius: 12px; background: ${bgColor}; color: ${color}; font-weight: 950; border: 1.5px solid ${color}; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase;">
                        <i data-lucide="${isApproved ? 'check-circle' : 'alert-circle'}" style="width: 18px;"></i> ${labelText}
                    </div>
                </div>
            `;
        }

        const html = `
            <div style="background: white; padding: 3.5rem 3rem 2.5rem 3rem; border-bottom: 1px solid #e2e8f0; position: relative;">
                <button onclick="window.closeOversightPanel()" style="position: absolute; top: 1.5rem; right: 1.5rem; background: #f1f5f9; border: none; width: 36px; height: 36px; border-radius: 50%; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a'" onmouseout="this.style.background='#f1f5f9'; this.style.color='#64748b'">
                    <i data-lucide="x" style="width: 18px;"></i>
                </button>

                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 25px rgba(245, 158, 11, 0.3);">
                        <i data-lucide="refresh-cw" style="width: 32px; height: 32px;"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 800; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 4px;">Item Recovery Oversight</div>
                        <h2 style="margin: 0; font-size: 2rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em;">Return Verification</h2>
                        <p style="margin: 4px 0 0; font-size: 0.95rem; color: #64748b; font-weight: 500;">Submitted by <b>${data.personnel}</b></p>
                    </div>
                </div>
            </div>

            <div style="padding: 2.5rem 3rem; flex: 1; overflow-y: auto; background: #f8fafc;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; background: #fff; padding: 2rem; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
                    <div style="background: #f8fafc; padding: 1.25rem; border-radius: 16px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Item Description</span>
                        <span style="font-size: 1.1rem; font-weight: 900; color: #0f172a; line-height: 1.4;">${item.description}</span>
                    </div>
                    <div style="background: #f8fafc; padding: 1.25rem; border-radius: 16px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Category</span>
                        <span style="font-size: 1rem; font-weight: 800; color: #4f46e5; background: rgba(79, 70, 229, 0.1); padding: 4px 12px; border-radius: 8px; display: inline-block;">${item.category}</span>
                    </div>
                    <div style="background: #f8fafc; padding: 1.25rem; border-radius: 16px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Beneficiary</span>
                        <span style="font-size: 1.1rem; font-weight: 800; color: #0f172a;">${item.beneficiary}</span>
                    </div>
                    <div style="background: #f8fafc; padding: 1.25rem; border-radius: 16px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: center;">
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Return Quantity</span>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: nowrap;">
                            <span style="font-size: 2.25rem; font-weight: 900; color: #10b981; line-height: 1;">${item.return_qty}</span>
                            <span style="font-size: 1rem; font-weight: 800; color: #64748b;">of ${item.issued_qty} <span style="text-transform: uppercase; font-size: 0.85rem;">${item.unit || 'units'}</span></span>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 2rem; padding: 1.5rem; background: #fff; border-radius: 20px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="message-square" style="width: 14px;"></i> User Remarks
                    </div>
                    <div style="font-size: 0.95rem; color: #475569; font-weight: 500; font-style: italic; line-height: 1.6; border-left: 3px solid #f59e0b; padding-left: 15px;">
                        ${item.remarks || '-- No specific notes provided --'}
                    </div>
                </div>

                <div style="margin-top: 2rem; padding: 1.25rem; background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 16px; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 32px; height: 32px; background: #10b981; color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i data-lucide="shield-check" style="width: 18px;"></i>
                    </div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: #065f46;">
                        Approval will automatically re-integrate ${item.return_qty} ${item.unit || 'units'} into the stock balances.
                    </div>
                </div>
            </div>

            ${footerHtml}
        `;

        document.getElementById('oversightPanelContent').innerHTML = html;
        document.getElementById('oversightOverlay').style.display = 'block';
        setTimeout(() => {
            document.getElementById('oversightOverlay').classList.add('show');
            document.getElementById('oversightSidePanel').classList.add('open');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }, 10);
    }

    window.showRecoveryPreview = function(reqId, btn) {
        if (typeof window.ensureOversightElementsRelocated === 'function') window.ensureOversightElementsRelocated();
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader" class="animate-spin" style="width:14px;"></i> Loading...`;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        // Immediately open the overlay with a loading skeleton
        window.showOversightPanelLoading('Loading Recovery Details...', 'Fetching the requested recovery information...');

        fetch(`{{ url('/api/edit-requests') }}/${reqId}/recovery-preview`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => {
                if (!res.ok) throw new Error(`Recovery fetch failed: ${res.status}`);
                return res.json();
            })
            .then(data => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `<i data-lucide="eye" style="width:15px;"></i> Preview Recovery Details`;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
                _renderRecoverySheet(data, reqId);
            })
            .catch(err => {
                window.closeOversightPanel();
                /* console print removed */
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `<i data-lucide="eye" style="width:15px;"></i> Preview Recovery Details`;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
                showToast('Error', 'Could not fetch recovery details.', 'error');
            });
    };

    window.showReconciliationPreview = function(reqId, btn) {
        if (typeof window.ensureOversightElementsRelocated === 'function') window.ensureOversightElementsRelocated();
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader" class="animate-spin" style="width:14px;"></i> Loading...`;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        // Immediately open the overlay with a loading skeleton
        window.showOversightPanelLoading('Loading Reconciliation Details...', 'Fetching the requested reconciliation information...');

        fetch(`{{ url('/api/edit-requests') }}/${reqId}/reconciliation-preview`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => {
                if (!res.ok) throw new Error(`Preview fetch failed: ${res.status}`);
                return res.json();
            })
            .then(data => {
                if (btn) {
                    btn.disabled = false;
                    const isBatch = data.request_type === 'batch_stock_verification';
                    btn.innerHTML = `<i data-lucide="eye" style="width:15px;"></i> ${isBatch ? 'Preview Batch Details' : 'Preview Details'}`;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
                _renderReconciliationSheet(data, reqId);
            })
            .catch(err => {
                window.closeOversightPanel();
                showToast('Error', 'Could not fetch reconciliation details.', 'error');
                if (btn) {
                    btn.disabled = false;
                    const isBatch = btn.textContent.includes('Batch');
                    btn.innerHTML = `<i data-lucide="eye" style="width:15px;"></i> ${isBatch ? 'Preview Batch Details' : 'Preview Details'}`;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            });
    };

    function _renderReconciliationSheet(data, reqId) {
        let footerHtml = '';
        if (data.status === 'pending') {
            footerHtml = `
                <div id="oversight-verification-actions-${reqId}" style="background: white; border-top: 1px solid #e2e8f0; padding: 1.5rem 3rem; display: flex; justify-content: flex-end; align-items: center; gap: 1rem; border-radius: 0 0 28px 28px; flex-shrink: 0;">
                    <button onclick="window.processVerificationApproval(${reqId}, 'rejected', this)" style="background: #ef4444; color: white; border: none; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                        <i data-lucide="x-circle" style="width: 18px;"></i> Reject
                    </button>
                    <button onclick="window.processVerificationApproval(${reqId}, 'approved', this)" style="background: #10b981; color: white; border: none; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                        <i data-lucide="check-circle" style="width: 18px;"></i> Approve Reconciliation
                    </button>
                </div>
            `;
        } else {
            const isApproved = data.status === 'approved';
            const color = isApproved ? '#10b981' : '#dc2626';
            const bgColor = isApproved ? 'rgba(16, 185, 129, 0.1)' : 'rgba(220, 38, 38, 0.1)';
            const labelText = isApproved ? 'VERIFICATION APPROVED' : 'VERIFICATION REJECTED';
            footerHtml = `
                <div style="background: white; border-top: 1px solid #e2e8f0; padding: 1.5rem 3rem; display: flex; justify-content: center; align-items: center; gap: 1rem; border-radius: 0 0 28px 28px; flex-shrink: 0;">
                    <div style="padding: 12px 24px; border-radius: 12px; background: ${bgColor}; color: ${color}; font-weight: 950; border: 1.5px solid ${color}; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase;">
                        <i data-lucide="${isApproved ? 'check-circle' : 'alert-circle'}" style="width: 18px;"></i> ${labelText}
                    </div>
                </div>
            `;
        }

        let bodyHtml = '';
        if (data.request_type === 'stock_verification') {
            const payload = data.payload;
            const variance = parseFloat(payload.variance);
            const varColor = variance === 0 ? '#10b981' : (variance > 0 ? '#4f46e5' : '#ef4444');
            const varBg = variance === 0 ? 'rgba(16, 185, 129, 0.06)' : (variance > 0 ? 'rgba(79, 70, 229, 0.06)' : 'rgba(239, 68, 68, 0.06)');
            const varBorder = variance === 0 ? 'rgba(16, 185, 129, 0.2)' : (variance > 0 ? 'rgba(79, 70, 229, 0.2)' : 'rgba(239, 68, 68, 0.2)');
            const varSign = variance > 0 ? '+' : '';

            bodyHtml = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; background: #fff; padding: 2rem; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
                    <div style="background: #f8fafc; padding: 1.25rem; border-radius: 16px; border: 1px solid #e2e8f0; grid-column: span 2;">
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Item Description</span>
                        <span style="font-size: 1.1rem; font-weight: 900; color: #0f172a; line-height: 1.4;">${payload.description}</span>
                    </div>
                    <div style="background: #f8fafc; padding: 1.25rem; border-radius: 16px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Condition</span>
                        <span style="font-size: 1rem; font-weight: 800; color: #4f46e5; background: rgba(79, 70, 229, 0.1); padding: 4px 12px; border-radius: 8px; display: inline-block;">${payload.condition}</span>
                    </div>
                    <div style="background: #f8fafc; padding: 1.25rem; border-radius: 16px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">System Stock</span>
                        <span style="font-size: 1.5rem; font-weight: 900; color: #64748b;">${payload.current_stock}</span>
                    </div>
                    <div style="background: #f8fafc; padding: 1.25rem; border-radius: 16px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Physical Count</span>
                        <span style="font-size: 1.5rem; font-weight: 900; color: #0f172a;">${payload.physical_count}</span>
                    </div>
                    <div style="background: ${varBg}; padding: 1.25rem; border-radius: 16px; border: 1px solid ${varBorder};">
                        <span style="display: block; font-size: 0.65rem; font-weight: 800; color: ${varColor}; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Discrepancy / Variance</span>
                        <span style="font-size: 1.5rem; font-weight: 900; color: ${varColor};">${varSign}${payload.variance}</span>
                    </div>
                </div>

                <div style="margin-top: 2rem; padding: 1.5rem; background: #fff; border-radius: 20px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="message-square" style="width: 14px;"></i> Verifier Remarks
                    </div>
                    <div style="font-size: 0.95rem; color: #475569; font-weight: 500; font-style: italic; line-height: 1.6; border-left: 3px solid #f59e0b; padding-left: 15px;">
                        ${payload.remarks || '-- No specific notes provided --'}
                    </div>
                </div>
            `;
        } else {
            const items = data.payload.items || [];
            let rowsHtml = '';
            items.forEach(si => {
                const variance = parseFloat(si.variance);
                const varStyle = variance === 0 ? 'color:#10b981;' : (variance > 0 ? 'color:#4f46e5;' : 'color:#ef4444;');
                const varSign = variance > 0 ? '+' : '';
                rowsHtml += `
                    <tr style="border-bottom: 1px solid #e2e8f0; color: #475569;">
                        <td style="padding: 12px; font-weight: 700; color: #0f172a;">${si.description}</td>
                        <td style="padding: 12px;">${si.current_stock}</td>
                        <td style="padding: 12px; font-weight: 700; color: #0f172a;">${si.physical_count}</td>
                        <td style="padding: 12px; font-weight: 800; ${varStyle}">${varSign}${si.variance}</td>
                        <td style="padding: 12px;"><span style="font-size: 0.8rem; font-weight: 700; color: #4f46e5; background: rgba(79, 70, 229, 0.08); padding: 4px 10px; border-radius: 6px;">${si.condition}</span></td>
                        <td style="padding: 12px; font-style: italic; font-size: 0.85rem; max-width: 250px; word-break: break-word;">${si.remarks || '--'}</td>
                    </tr>
                `;
            });

            bodyHtml = `
                <div style="background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.02); overflow: hidden;">
                    <div style="max-height: 450px; overflow-y: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; text-align: left;">
                            <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 10;">
                                <tr style="color: #475569; font-weight: 800; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">
                                    <th style="padding: 16px 12px;">Item</th>
                                    <th style="padding: 16px 12px;">System Stock</th>
                                    <th style="padding: 16px 12px;">Physical Count</th>
                                    <th style="padding: 16px 12px;">Variance</th>
                                    <th style="padding: 16px 12px;">Condition</th>
                                    <th style="padding: 16px 12px;">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rowsHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        const isBatch = data.request_type === 'batch_stock_verification';
        const title = isBatch ? 'Batch Stock Reconciliation' : 'Stock Reconciliation';
        const iconName = isBatch ? 'clipboard-list' : 'check-square';
        const iconGradient = isBatch ? 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)' : 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)';
        const iconShadow = '0 10px 25px rgba(59, 130, 246, 0.3)';

        const html = `
            <div style="background: white; padding: 3.5rem 3rem 2.5rem 3rem; border-bottom: 1px solid #e2e8f0; position: relative;">
                <button onclick="window.closeOversightPanel()" style="position: absolute; top: 1.5rem; right: 1.5rem; background: #f1f5f9; border: none; width: 36px; height: 36px; border-radius: 50%; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a'" onmouseout="this.style.background='#f1f5f9'; this.style.color='#64748b'">
                    <i data-lucide="x" style="width: 18px;"></i>
                </button>

                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="width: 64px; height: 64px; background: ${iconGradient}; color: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; box-shadow: ${iconShadow};">
                        <i data-lucide="${iconName}" style="width: 32px; height: 32px;"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 800; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 4px;">Strategic Stock Oversight</div>
                        <h2 style="margin: 0; font-size: 2rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em;">${title}</h2>
                        <p style="margin: 4px 0 0; font-size: 0.95rem; color: #64748b; font-weight: 500;">Submitted by <b>${data.personnel}</b></p>
                    </div>
                </div>
            </div>

            <div style="padding: 2.5rem 3rem; flex: 1; overflow-y: auto; background: #f8fafc;">
                ${bodyHtml}
            </div>

            ${footerHtml}
        `;

        document.getElementById('oversightPanelContent').innerHTML = html;
        document.getElementById('oversightOverlay').style.display = 'block';
        setTimeout(() => {
            document.getElementById('oversightOverlay').classList.add('show');
            document.getElementById('oversightSidePanel').classList.add('open');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }, 10);
    }


    window.processEditRequest = function(id, status, btnElement) {
        if (status === 'canceled') {
            Swal.fire({
                html: `
                    <div style="text-align: left;">
                        <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); margin: -1.25em -1.25em 1.5em; padding: 2rem 2rem 1.5rem; border-radius: 4px 4px 0 0; position: relative; overflow: hidden;">
                            <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: rgba(255,255,255,0.06); border-radius: 50%;"></div>
                            <div style="position: absolute; bottom: -30px; left: -10px; width: 80px; height: 80px; background: rgba(255,255,255,0.04); border-radius: 50%;"></div>
                            <div style="display: flex; align-items: center; gap: 14px; position: relative;">
                                <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.15); border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg style="width: 26px; height: 26px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </div>
                                <div>
                                    <div style="font-size: 0.7rem; font-weight: 800; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 3px;">Admin Action Required</div>
                                    <div style="font-size: 1.3rem; font-weight: 900; color: white; letter-spacing: -0.02em;">Decline Disbursement</div>
                                </div>
                            </div>
                        </div>

                        <p style="font-size: 0.9rem; color: #64748b; line-height: 1.6; margin-bottom: 1.25rem; padding: 0 0.25rem;">
                            Provide a clear reason for declining this request. The user will be notified immediately with your justification.
                        </p>

                        <div style="position: relative;">
                            <textarea id="swal-decline-reason" placeholder="e.g. Insufficient justification provided, items currently out of stock, request lacks proper authority sign-off..." style="width: 100%; min-height: 110px; font-size: 0.9rem; border-radius: 14px; border: 2px solid #f1f5f9; padding: 1rem 1.25rem; font-family: inherit; resize: vertical; outline: none; transition: border-color 0.3s; box-sizing: border-box; color: #0f172a; background: #f8fafc;" onfocus="this.style.borderColor='#ef4444'; this.style.boxShadow='0 0 0 4px rgba(239,68,68,0.08)'" onblur="this.style.borderColor='#f1f5f9'; this.style.boxShadow='none'"></textarea>
                        </div>

                        <div style="margin-top: 1rem; padding: 10px 14px; background: rgba(245, 158, 11, 0.07); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 10px; display: flex; align-items: center; gap: 10px;">
                            <svg style="width: 16px; height: 16px; color: #d97706; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span style="font-size: 0.78rem; font-weight: 700; color: #92400e;">A reason is mandatory and will be logged in the system.</span>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '&#10005; &nbsp;Decline Request',
                cancelButtonText: 'Go Back',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                focusConfirm: false,
                customClass: {
                    popup: 'swal-decline-popup',
                    confirmButton: 'swal-decline-confirm-btn',
                    cancelButton: 'swal-decline-cancel-btn',
                },
                didOpen: () => {
                    const style = document.createElement('style');
                    style.id = 'swal-decline-styles';
                    style.textContent = `
                        .swal-decline-popup { border-radius: 24px !important; overflow: hidden !important; padding: 1.25em !important; }
                        .swal-decline-confirm-btn { border-radius: 10px !important; font-weight: 800 !important; padding: 12px 24px !important; font-size: 0.9rem !important; letter-spacing: 0.02em !important; }
                        .swal-decline-cancel-btn { border-radius: 10px !important; font-weight: 700 !important; padding: 12px 24px !important; font-size: 0.9rem !important; }
                        .swal2-actions { gap: 10px !important; margin-top: 1.5rem !important; }
                    `;
                    if (!document.getElementById('swal-decline-styles')) document.head.appendChild(style);
                },
                preConfirm: () => {
                    const reason = document.getElementById('swal-decline-reason').value.trim();
                    if (!reason) {
                        Swal.showValidationMessage('<span style="font-size:0.85rem;">⚠ A reason is required before declining.</span>');
                        return false;
                    }
                    return reason;
                }
            }).then(result => {
                if (result.isConfirmed && result.value) {
                    _doProcessRequest(id, status, btnElement, result.value);
                }
            });
        } else {
            _doProcessRequest(id, status, btnElement, null);
        }
    };

    function _doProcessRequest(id, status, btnElement, declineReason) {
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
                body: JSON.stringify({
                    status: status,
                    decline_reason: declineReason
                })
            })
            .then(res => {
                if (!res.ok) throw new Error('Server error');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof window.playNotificationSound === 'function') {
                        window.playNotificationSound('sent');
                    }
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
                /* console print removed */
                showToast('Connection Error', 'An unexpected error occurred. Please check system logs.', 'error');
                btnElement.innerText = status === 'approved' ? 'Approve' : 'Cancel';
                btnElement.disabled = false;
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
        document.querySelectorAll('.sra-approval-card, .recovery-approval-card, [id^="sra-creation-actions-"], [id^="recovery-actions-"]').forEach(function(container) {
            // Find the edit request ID
            let reqId = '';
            let actionsDiv = container.id.startsWith('sra-creation-actions-') || container.id.startsWith('recovery-actions-') ?
                container :
                container.querySelector('[id^="sra-creation-actions-"], [id^="recovery-actions-"]');

            if (actionsDiv) {
                reqId = actionsDiv.id.replace('sra-creation-actions-', '').replace('recovery-actions-', '');
            }
            if (!reqId) return;

            // Patch Links to Buttons
            container.querySelectorAll('a, button').forEach(function(el) {
                const text = el.textContent.trim();

                // Case 1: Entry Preview (New Stock Entry Oversight / Proposed Changes)
                if (text.includes('Preview Entry Details') || text.includes('Preview Proposed Changes')) {
                    if (el.tagName === 'A') {
                        // Convert link to button
                        const btn = document.createElement('button');
                        btn.className = el.className || 'entry-preview-btn';
                        btn.setAttribute('style', el.getAttribute('style'));
                        btn.innerHTML = el.innerHTML;
                        btn.setAttribute('data-entry-req-id', reqId);
                        el.parentNode.replaceChild(btn, el);
                    } else if (!el.getAttribute('data-entry-req-id')) {
                        el.setAttribute('data-entry-req-id', reqId);
                        el.classList.add('entry-preview-btn');
                    }
                }
                // Case 3: Item Recovery Preview
                else if (text.includes('Preview Recovery Details') || text.includes('Recovery')) {
                    if (el.tagName === 'A') {
                        const btn = document.createElement('button');
                        btn.className = el.className || 'recovery-preview-btn';
                        btn.setAttribute('style', el.getAttribute('style'));
                        btn.innerHTML = el.innerHTML;
                        btn.setAttribute('data-recovery-req-id', reqId);
                        el.parentNode.replaceChild(btn, el);
                    } else if (!el.getAttribute('data-recovery-req-id')) {
                        el.setAttribute('data-recovery-req-id', reqId);
                        el.classList.add('recovery-preview-btn');
                    }
                }
                // Case 2: Remainder Preview (Legacy SRA style)
                else if (text.includes('Preview Changes') || (text.includes('Preview') && !text.includes('Entry Details'))) {
                    if (el.tagName === 'A') {
                        const btn = document.createElement('button');
                        btn.className = el.className || 'remainder-preview-btn';
                        btn.setAttribute('style', el.getAttribute('style'));
                        btn.innerHTML = el.innerHTML;
                        btn.setAttribute('data-req-id', reqId);
                        el.parentNode.replaceChild(btn, el);
                    } else if (!el.getAttribute('data-req-id')) {
                        el.setAttribute('data-req-id', reqId);
                        el.classList.add('remainder-preview-btn');
                    }
                }
            });
        });
    }

    // ---- Init: move the sheets to <body> so position:fixed works correctly ----
    // CSS transforms on parent containers break position:fixed — moving to body escapes this.
    window.ensureOversightElementsRelocated = function() {
        const overlay = document.getElementById('oversightOverlay');
        const panel = document.getElementById('oversightSidePanel');
        if (overlay && panel && overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
            document.body.appendChild(panel);
        }
    };

    (function() {
        const sheet = document.getElementById('remainderPreviewSheet');
        if (sheet && sheet.parentElement !== document.body) {
            document.body.appendChild(sheet);
        }
        window.ensureOversightElementsRelocated();
    })();

    // Helper to render the bottom sheet given preview data from the API
    // Helper to render the side panel given preview data from the API
    function _renderRemainderSheet(data) {
        const items = data.items || [];
        const reqId = data.reqId;
        const totalExpected = items.reduce((s, i) => s + (parseFloat(i.expected) || 0), 0);
        const totalCurrent = items.reduce((s, i) => s + (parseFloat(i.current) || 0), 0);
        const totalAdding = items.reduce((s, i) => s + (parseFloat(i.adding) || 0), 0);
        const totalProjected = items.reduce((s, i) => s + (parseFloat(i.projected) || 0), 0);

        let tableRows = '';
        if (items.length === 0) {
            tableRows = `
                <tr>
                    <td colspan="6" style="padding: 2rem; text-align: center; color: #94a3b8; font-weight: 600;">
                        No item details found.
                    </td>
                </tr>
            `;
            items.forEach((item, idx) => {
                let serialHtml = '';
                if (item.serial_number) {
                    const sns = item.serial_number.split(',').map(s => s.trim()).filter(Boolean);
                    if (sns.length > 0) {
                        const showSns = sns.slice(0, 3).join(', ');
                        if (sns.length > 3) {
                            const hiddenSns = sns.slice(3).join(', ');
                            serialHtml = `
                                <div class="serial-numbers-wrapper" style="margin-top: 4px; display: inline-flex; flex-wrap: wrap; align-items: center; gap: 4px;">
                                    <div style="display: inline-flex; align-items: center; flex-wrap: wrap; gap: 4px; background: rgba(99, 102, 241, 0.08); color: #4f46e5; font-size: 0.72rem; padding: 2px 8px; border-radius: 6px; font-weight: 800; word-break: break-word; white-space: normal; max-width: 250px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2z"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg>
                                        S/N: ${showSns}<span class="dots">...</span><span class="more-sns" style="display: none;">, ${hiddenSns}</span>
                                    </div>
                                    <button type="button" class="toggle-sns-btn" onclick="let container = this.previousElementSibling; let more = container.querySelector('.more-sns'); let dots = container.querySelector('.dots'); let isHidden = more.style.display === 'none'; more.style.display = isHidden ? 'inline' : 'none'; dots.style.display = isHidden ? 'none' : 'inline'; this.querySelector('.chevron-icon').style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';" style="background: transparent; border: none; padding: 2px; cursor: pointer; display: inline-flex; align-items: center; color: #4f46e5; outline: none; transition: all 0.2s; border-radius: 4px;" onmouseover="this.style.background='rgba(99, 102, 241, 0.15)';" onmouseout="this.style.background='transparent';" title="Show more serial numbers">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="chevron-icon" style="transition: transform 0.2s;"><polyline points="6 9 12 15 18 9"/></svg>
                                    </button>
                                </div>
                            `;
                        } else {
                            serialHtml = `
                                <div style="margin-top: 4px; display: inline-flex; align-items: center; gap: 4px; background: rgba(99, 102, 241, 0.08); color: #4f46e5; font-size: 0.72rem; padding: 2px 8px; border-radius: 6px; font-weight: 800;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2z"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg> S/N: ${item.serial_number}
                                </div>
                            `;
                        }
                    }
                }

                tableRows += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 700; color: #0f172a;">
                        <div>${item.description}</div>
                        ${serialHtml}
                    </td>
                    <td style="padding: 1rem 1.5rem; font-size: 0.85rem; color: #64748b;">
                        ${item.unit || 'Package Types'}
                    </td>
                    <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #f59e0b; text-align: right; background: rgba(245, 158, 11, 0.02); border-left: 2px solid #f59e0b;">${(parseFloat(item.expected) || 0).toLocaleString()}</td>
                    <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #0f172a; text-align: right;">${(parseFloat(item.current) || 0).toLocaleString()}</td>
                    <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #10b981; text-align: right; background: rgba(16, 185, 129, 0.05); border-left: 2px solid #10b981;">+${(parseFloat(item.adding) || 0).toLocaleString()}</td>
                    <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #4f46e5; text-align: right; background: rgba(79, 70, 229, 0.03); border-left: 2px solid #4f46e5;">${(parseFloat(item.projected) || 0).toLocaleString()}</td>
                </tr>
                `;
            });
        }

        let footerHtml = '';
        if (data.status === 'pending') {
            footerHtml = `
                <div id="oversight-actions-${reqId}" style="background: white; border-top: 1px solid #e2e8f0; padding: 1.5rem 3rem; display: flex; justify-content: flex-end; align-items: center; gap: 1rem; border-radius: 0 0 28px 28px; flex-shrink: 0;">
                    <button onclick="typeof window.rollbackEntry === 'function' ? window.rollbackEntry(${reqId}) : alert('Rollback functionality pending implementation')" style="margin-right: auto; background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                        <i data-lucide="rotate-ccw" style="width: 18px;"></i> Rollback
                    </button>
                    <button onclick="window.processSraCreationApproval(${reqId}, 'rejected', this)" style="background: #ef4444; color: white; border: none; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                        <i data-lucide="x-circle" style="width: 18px;"></i> Reject
                    </button>
                    <button onclick="window.processSraCreationApproval(${reqId}, 'approved', this)" style="background: #10b981; color: white; border: none; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                        <i data-lucide="check-circle" style="width: 18px;"></i> Approve Entry
                    </button>
                </div>
            `;
        } else {
            const isApproved = data.status === 'approved';
            const color = isApproved ? '#10b981' : '#dc2626';
            const bgColor = isApproved ? 'rgba(16, 185, 129, 0.1)' : 'rgba(220, 38, 38, 0.1)';
            const labelText = isApproved ? 'REMAINDER COMMITTED' : 'REJECTED';
            footerHtml = `
                <div style="background: white; border-top: 1px solid #e2e8f0; padding: 1.5rem 3rem; display: flex; justify-content: center; align-items: center; gap: 1rem; border-radius: 0 0 28px 28px; flex-shrink: 0; position: relative;">
                    <button onclick="typeof window.rollbackEntry === 'function' ? window.rollbackEntry(${reqId}) : alert('Rollback functionality pending implementation')" style="position: absolute; left: 3rem; background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                        <i data-lucide="rotate-ccw" style="width: 18px;"></i> Rollback
                    </button>
                    <div style="padding: 12px 24px; border-radius: 12px; background: ${bgColor}; color: ${color}; font-weight: 950; border: 1.5px solid ${color}; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase;">
                        <i data-lucide="${isApproved ? 'check-circle' : 'alert-circle'}" style="width: 18px;"></i> ${labelText}
                    </div>
                </div>
            `;
        }

        const content = `
            <!-- Header Section -->
            <div style="background: white; padding: 3.5rem 3rem 2.5rem 3rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; position: relative;">
                <button onclick="window.closeOversightPanel()" style="position: absolute; top: 1.5rem; right: 1.5rem; background: #f1f5f9; border: none; width: 36px; height: 36px; border-radius: 50%; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; z-index: 10;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a'" onmouseout="this.style.background='#f1f5f9'; this.style.color='#64748b'">
                    <i data-lucide="x" style="width: 18px;"></i>
                </button>

                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="width: 56px; height: 56px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="package-search" style="width: 28px; height: 28px;"></i>
                    </div>
                    <div>
                        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 900; color: #0f172a; letter-spacing: -0.02em;">Remainder Approval</h2>
                        <p style="margin: 0; font-size: 0.9rem; color: #64748b; font-weight: 500;">User: <b>${data.personnel}</b> &nbsp;&bull;&nbsp; Batch #${data.batchId}</p>
                    </div>
                </div>

                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                    <div style="text-align: right;">
                        <label style="display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Supply Status</label>
                        <span style="font-size: 0.95rem; font-weight: 700; color: #1e293b;">Pending Partial Delivery Fulfillment</span>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div style="padding: 2.5rem 3rem; flex: 1; overflow-y: auto; background: #f8fafc;">
                <!-- Summary Stats -->
                <div style="display:flex; gap:1.5rem; margin-bottom:1.5rem;">
                    <div style="flex:1; background: white; border: 1px solid #e2e8f0; border-radius: 20px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.01); display: flex; align-items: center; gap: 1.25rem;">
                        <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i data-lucide="layers" style="width: 24px; height: 24px;"></i>
                        </div>
                        <div>
                            <div style="font-size:0.7rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:3px;">Items Affected</div>
                            <div style="font-size:1.75rem; font-weight:900; color:#0f172a; line-height:1;">${items.length}</div>
                        </div>
                    </div>
                    <div style="flex:1; background: white; border: 1px solid #e2e8f0; border-radius: 20px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.01); display: flex; align-items: center; gap: 1.25rem;">
                        <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.15); color: #d97706; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i data-lucide="package" style="width: 24px; height: 24px;"></i>
                        </div>
                        <div>
                            <div style="font-size:0.7rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:3px;">Total Expected</div>
                            <div style="font-size:1.75rem; font-weight:900; color:#d97706; line-height:1;">${totalExpected.toLocaleString()}</div>
                        </div>
                    </div>
                    <div style="flex:1; background: white; border: 1px solid #e2e8f0; border-radius: 20px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.01); display: flex; align-items: center; gap: 1.25rem;">
                        <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i data-lucide="package-plus" style="width: 24px; height: 24px;"></i>
                        </div>
                        <div>
                            <div style="font-size:0.7rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:3px;">Total Qty Adding</div>
                            <div style="font-size:1.75rem; font-weight:900; color:#10b981; line-height:1;">+${totalAdding.toLocaleString()}</div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <h3 style="font-size: 1rem; font-weight: 900; color: #334155; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="list-checks" style="width: 20px; color: #4f46e5;"></i> Remainder Item Details
                </h3>
                <div style="background: white; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 2rem;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <tr>
                                <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Item Description</th>
                                <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Package Type</th>
                                <th style="padding: 1.25rem 1.5rem; text-align: right; font-size: 0.75rem; font-weight: 800; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.05em;">Total Expected</th>
                                <th style="padding: 1.25rem 1.5rem; text-align: right; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Current Stock</th>
                                <th style="padding: 1.25rem 1.5rem; text-align: right; font-size: 0.75rem; font-weight: 800; color: #10b981; text-transform: uppercase; letter-spacing: 0.05em;">+ Adding</th>
                                <th style="padding: 1.25rem 1.5rem; text-align: right; font-size: 0.75rem; font-weight: 800; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.05em;">New Projected</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tableRows}
                        </tbody>
                        <tfoot style="background: #f8fafc; border-top: 2px solid #e2e8f0;">
                            <tr>
                                <td colspan="2" style="padding: 1rem 1.5rem; font-size: 0.8rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Total Remainder Quantities
                                </td>
                                <td style="padding: 1rem 1.5rem; text-align: right; font-size: 1rem; font-weight: 900; color: #f59e0b; background: rgba(245, 158, 11, 0.02); border-left: 2px solid #f59e0b;">
                                    ${totalExpected.toLocaleString()}
                                </td>
                                <td style="padding: 1rem 1.5rem; text-align: right; font-size: 0.85rem; font-weight: 800; color: #0f172a;">
                                    ${totalCurrent.toLocaleString()}
                                </td>
                                <td style="padding: 1rem 1.5rem; text-align: right; font-size: 1rem; font-weight: 900; color: #10b981; background: rgba(16, 185, 129, 0.05); border-left: 2px solid #10b981;">
                                    +${totalAdding.toLocaleString()}
                                </td>
                                <td style="padding: 1rem 1.5rem; text-align: right; font-size: 1rem; font-weight: 900; color: #4f46e5; background: rgba(79, 70, 229, 0.03); border-left: 2px solid #4f46e5;">
                                    ${totalProjected.toLocaleString()}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div style="padding: 1.5rem; background: #fffbeb; border-radius: 16px; border: 1px solid #fef3c7; display: flex; align-items: center; gap: 1.25rem;">
                    <div style="width: 40px; height: 40px; background: #f59e0b; color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i data-lucide="shield-alert" style="width: 20px;"></i>
                    </div>
                    <div style="flex: 1;">
                        <span style="display: block; font-size: 0.9rem; font-weight: 800; color: #92400e;">Remainder Review in Progress</span>
                        <span style="font-size: 0.8rem; color: #b45309;">These changes will only apply to inventory after you approve the request.</span>
                    </div>
                </div>
            </div>

            <!-- Footer Section -->
            ${footerHtml}
        `;

        document.getElementById('oversightPanelContent').innerHTML = content;
        document.getElementById('oversightOverlay').style.display = 'block';
        setTimeout(() => {
            document.getElementById('oversightOverlay').classList.add('show');
            document.getElementById('oversightSidePanel').classList.add('open');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }, 10);
    }


    // Called two ways:
    //  1. Old messages: onclick='window.showRemainderPreview(this)' — receives the button element
    //  2. New messages: event delegation below passes (reqId, btnEl)
    window.showRemainderPreview = function(reqIdOrBtn, btnEl) {
        if (typeof window.ensureOversightElementsRelocated === 'function') window.ensureOversightElementsRelocated();
        let reqId, btn;

        if (reqIdOrBtn && typeof reqIdOrBtn === 'object' && reqIdOrBtn.nodeType) {
            // Old-style call: showRemainderPreview(this) — first arg is the button DOM element
            btn = reqIdOrBtn;
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
            btn = btnEl || null;
        }

        if (!reqId) {
            alert('Could not identify the request. Please try again.');
            return;
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<svg style="width:14px;height:14px;animation:spin 1s linear infinite;display:inline;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg> Loading...`;
        }

        // Immediately open the overlay with a loading skeleton
        window.showOversightPanelLoading('Loading Remainder Details...', 'Fetching the remainder items information...');

        fetch(`{{ url('/api/edit-requests') }}/${reqId}/remainder-preview`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
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
                data.reqId = reqId;
                _renderRemainderSheet(data);
            })
            .catch(err => {
                window.closeOversightPanel();
                /* console print removed */
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `<i data-lucide="eye" style="width:15px;"></i> Preview Changes`;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
                alert('Could not load preview: ' + err.message);
            });
    };

    window.showOversightPanelLoading = function(title, subtitle) {
        const skeletonHtml = `
            <div style="background: white; padding: 3.5rem 3rem 2.5rem 3rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; position: relative;">
                <button onclick="window.closeOversightPanel()" style="position: absolute; top: 1.5rem; right: 1.5rem; background: #f1f5f9; border: none; width: 36px; height: 36px; border-radius: 50%; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; z-index: 10;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a'" onmouseout="this.style.background='#f1f5f9'; this.style.color='#64748b'">
                    <i data-lucide="x" style="width: 18px;"></i>
                </button>
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="width: 56px; height: 56px; background: rgba(79, 70, 229, 0.05); color: #4f46e5; border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                        <svg class="animate-spin" style="width:28px;height:28px;color:#6366f1;animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.2"></circle><path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" fill="currentColor"></path></svg>
                    </div>
                    <div>
                        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 900; color: #0f172a; letter-spacing: -0.02em;">${title || 'Loading Details...'}</h2>
                        <p style="margin: 0; font-size: 0.9rem; color: #64748b; font-weight: 500;">${subtitle || 'Fetching the requested information...'}</p>
                    </div>
                </div>
            </div>
            <div style="padding: 2.5rem 3rem; flex: 1; overflow-y: auto; background: #f8fafc; display: flex; flex-direction: column; gap: 2rem;">
                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; background: white; padding: 2rem; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.01);">
                    <div style="flex: 1; min-width: 200px; display: flex; flex-direction: column; gap: 8px;">
                        <div style="width: 80px; height: 12px; background: #e2e8f0; border-radius: 4px; animation: pulse 1.5s infinite ease-in-out;"></div>
                        <div style="width: 150px; height: 20px; background: #f1f5f9; border-radius: 6px; animation: pulse 1.5s infinite ease-in-out;"></div>
                    </div>
                    <div style="width: 1px; height: 40px; background: #e2e8f0;"></div>
                    <div style="flex: 1; min-width: 200px; display: flex; flex-direction: column; gap: 8px;">
                        <div style="width: 80px; height: 12px; background: #e2e8f0; border-radius: 4px; animation: pulse 1.5s infinite ease-in-out;"></div>
                        <div style="width: 120px; height: 20px; background: #f1f5f9; border-radius: 6px; animation: pulse 1.5s infinite ease-in-out;"></div>
                    </div>
                </div>
                <div style="background: white; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.01);">
                    <div style="padding: 1.25rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between;">
                        <div style="width: 120px; height: 14px; background: #e2e8f0; border-radius: 4px; animation: pulse 1.5s infinite ease-in-out;"></div>
                        <div style="width: 80px; height: 14px; background: #e2e8f0; border-radius: 4px; animation: pulse 1.5s infinite ease-in-out;"></div>
                    </div>
                    <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 8px; animation: pulse 1.5s infinite ease-in-out;"></div>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <div style="width: 200px; height: 16px; background: #f1f5f9; border-radius: 4px; animation: pulse 1.5s infinite ease-in-out;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('oversightPanelContent').innerHTML = skeletonHtml;
        document.getElementById('oversightOverlay').style.display = 'block';
        setTimeout(() => {
            document.getElementById('oversightOverlay').classList.add('show');
            document.getElementById('oversightSidePanel').classList.add('open');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }, 10);
    };

    window.closeOversightPanel = function() {
        const panel = document.getElementById('oversightSidePanel');
        const overlay = document.getElementById('oversightOverlay');
        if (panel) panel.classList.remove('open');
        if (overlay) {
            overlay.classList.remove('show');
            setTimeout(() => {
                if (!overlay.classList.contains('show')) overlay.style.display = 'none';
            }, 300);
        }
    };

    window.showEntryPreview = function(reqId, btn) {
        if (typeof window.ensureOversightElementsRelocated === 'function') window.ensureOversightElementsRelocated();
        if (window._entryPreviewLoading) return;

        window._entryPreviewLoading = true;

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<svg style="width:14px;height:14px;animation:spin 1s linear infinite;display:inline;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg> Loading...`;
        }

        // Immediately open the overlay with a loading skeleton
        window.showOversightPanelLoading('Loading Entry Details...', 'Fetching the requested stock entry information...');

        fetch(`{{ url('/api/sra-preview') }}/${reqId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `<i data-lucide="eye" style="width:16px;"></i> Preview Entry Details`;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }

                window._entryPreviewLoading = false;

                const batch = data.batch || { items: [] };
                let content = '';

                if (data.request_type === 'issue_submission') {
                    const itemsHtml = batch.items.map((item, idx) => {
                        return `
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 1.5rem; border-bottom: 1px dashed #e2e8f0; background: ${idx % 2 === 0 ? '#ffffff' : '#f8fafc'}; transition: all 0.3s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='${idx % 2 === 0 ? '#ffffff' : '#f8fafc'}'">
                            <div style="display: flex; align-items: center; gap: 1.25rem;">
                                <div style="width: 48px; height: 48px; background: rgba(99, 102, 241, 0.1); color: #4f46e5; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.1rem; border: 1px solid rgba(99, 102, 241, 0.2);">
                                    ${idx + 1}
                                </div>
                                <div>
                                    <div style="font-weight: 900; font-size: 1.1rem; color: #0f172a; margin-bottom: 4px;">${item.description}</div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 0.7rem; font-weight: 800; color: #4f46e5; background: rgba(79, 70, 229, 0.1); padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05em;">CATEGORY ${item.category || '-'}</span>
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: right; background: white; border: 1px solid #e2e8f0; padding: 0.75rem 1.5rem; border-radius: 14px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
                                <div style="font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 2px;">Quantity to Issue</div>
                                <div style="font-size: 1.5rem; font-weight: 900; color: #f59e0b; display: flex; align-items: baseline; gap: 4px; justify-content: flex-end;">
                                    ${parseFloat(item.qty).toLocaleString()}
                                    <span style="font-size: 0.85rem; color: #64748b; font-weight: 700;">${item.unit || 'Package Types'}</span>
                                </div>
                            </div>
                        </div>
                    `;
                    }).join('');

                    content = `
                    <div style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); padding: 3.5rem 3rem 2.5rem 3rem; border-bottom: 1px solid #e2e8f0; position: relative;">
                        <button onclick="window.closeOversightPanel()" style="position: absolute; top: 1.5rem; right: 1.5rem; background: #f1f5f9; border: none; width: 36px; height: 36px; border-radius: 50%; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; z-index: 10;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a'" onmouseout="this.style.background='#f1f5f9'; this.style.color='#64748b'">
                            <i data-lucide="x" style="width: 18px;"></i>
                        </button>

                        <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 2rem;">
                            <div style="display: flex; align-items: center; gap: 1.5rem;">
                                <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 25px rgba(245, 158, 11, 0.3);">
                                    <i data-lucide="package-minus" style="width: 32px; height: 32px;"></i>
                                </div>
                                <div>
                                    <div style="font-size: 0.75rem; font-weight: 800; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 4px;">Disbursement Authorization</div>
                                    <h2 style="margin: 0; font-size: 2rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em;">Issuance Details</h2>
                                    <p style="margin: 6px 0 0; font-size: 0.95rem; color: #64748b; font-weight: 500;">Initiated by <b>${data.recorded_by_name}</b> on ${data.created_at}</p>
                                </div>
                            </div>

                            <div style="display: flex; gap: 2rem; background: white; padding: 1.25rem 2rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f1f5f9;">
                                <div>
                                    <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">User Department</label>
                                    <div style="font-size: 1.1rem; font-weight: 900; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></div>
                                        ${batch.beneficiary}
                                    </div>
                                </div>
                                <div style="width: 1px; height: 40px; background: #e2e8f0; align-self: center;"></div>
                                <div>
                                    <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Approving Authority</label>
                                    <div style="font-size: 1.1rem; font-weight: 800; color: #1e293b;">${batch.authority}</div>
                                </div>
                                <div style="width: 1px; height: 40px; background: #e2e8f0; align-self: center;"></div>
                                <div>
                                    <label style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Issuance Type</label>
                                    <span style="font-size: 0.85rem; font-weight: 900; color: #f59e0b; background: rgba(245, 158, 11, 0.1); padding: 4px 12px; border-radius: 8px; border: 1px dashed rgba(245, 158, 11, 0.3);">${batch.issuance_type}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="padding: 2.5rem 3rem; flex: 1; overflow-y: auto; background: #f8fafc;">
                        <h3 style="font-size: 1rem; font-weight: 900; color: #334155; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                            <i data-lucide="list-checks" style="width: 20px; color: #4f46e5;"></i> Items to Disburse (${batch.items.length})
                        </h3>
                        <div style="background: white; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.03); margin-bottom: 2rem;">
                            ${itemsHtml}
                        </div>
                    </div>
                `;
                } else {
                    const prevBatch = data.previous_batch || null;
                    const formattedArrival = (() => {
                        if (!batch.arrival_date) return 'N/A';
                        try {
                            const parts = batch.arrival_date.split('-');
                            if (parts.length === 3) {
                                return `${parts[2]}/${parts[1]}/${parts[0].slice(-2)}`;
                            }
                        } catch(e) {}
                        return batch.arrival_date;
                    })();

                    const formattedEntry = (() => {
                        const entryRaw = batch.entry_date || data.created_at;
                        if (!entryRaw) return 'N/A';
                        try {
                            if (entryRaw.includes('/') && entryRaw.includes(':')) {
                                return entryRaw;
                            }
                            const spaceParts = entryRaw.split(' ');
                            if (spaceParts.length === 2) {
                                const dateParts = spaceParts[0].split('-');
                                const timeParts = spaceParts[1].split(':');
                                if (dateParts.length === 3 && timeParts.length >= 2) {
                                    return `${dateParts[2]}/${dateParts[1]}/${dateParts[0].slice(-2)} ${timeParts[0]}:${timeParts[1]}`;
                                }
                            }
                        } catch(e) {}
                        return entryRaw;
                    })();
                    const itemsHtml = batch.items.map(item => {
                        let isQtyChanged = false;
                        let isStockChanged = false;
                        let isDescChanged = false;
                        let isRemarksChanged = false;

                        if (prevBatch && prevBatch.items) {
                            const prevItem = prevBatch.items.find(i => i.id == item.id);
                            if (prevItem) {
                                if (parseFloat(item.qty || 0) !== parseFloat(prevItem.qty || 0)) isQtyChanged = true;
                                if (parseFloat(item.stock_balance || 0) !== parseFloat(prevItem.stock_balance || 0)) isStockChanged = true;
                                if ((item.description || '').trim() !== (prevItem.description || '').trim()) isDescChanged = true;
                                if ((item.remarks || '').trim() !== (prevItem.remarks || '').trim()) isRemarksChanged = true;
                            }
                        }

                        let serialHtml = '';
                        if (item.serial_number) {
                            const sns = item.serial_number.split(',').map(s => s.trim()).filter(Boolean);
                            if (sns.length > 0) {
                                const showSns = sns.slice(0, 3).join(', ');
                                if (sns.length > 3) {
                                    const hiddenSns = sns.slice(3).join(', ');
                                    serialHtml = `
                                        <div class="serial-numbers-wrapper" style="margin-top: 4px; display: inline-flex; flex-wrap: wrap; align-items: center; gap: 4px;">
                                            <div style="display: inline-flex; align-items: center; flex-wrap: wrap; gap: 4px; background: rgba(99, 102, 241, 0.08); color: #4f46e5; font-size: 0.72rem; padding: 2px 8px; border-radius: 6px; font-weight: 800; word-break: break-word; white-space: normal; max-width: 250px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2z"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg>
                                                S/N: ${showSns}<span class="dots">...</span><span class="more-sns" style="display: none;">, ${hiddenSns}</span>
                                            </div>
                                            <button type="button" class="toggle-sns-btn" onclick="let container = this.previousElementSibling; let more = container.querySelector('.more-sns'); let dots = container.querySelector('.dots'); let isHidden = more.style.display === 'none'; more.style.display = isHidden ? 'inline' : 'none'; dots.style.display = isHidden ? 'none' : 'inline'; this.querySelector('.chevron-icon').style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';" style="background: transparent; border: none; padding: 2px; cursor: pointer; display: inline-flex; align-items: center; color: #4f46e5; outline: none; transition: all 0.2s; border-radius: 4px;" onmouseover="this.style.background='rgba(99, 102, 241, 0.15)';" onmouseout="this.style.background='transparent';" title="Show more serial numbers">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="chevron-icon" style="transition: transform 0.2s;"><polyline points="6 9 12 15 18 9"/></svg>
                                            </button>
                                        </div>
                                    `;
                                } else {
                                    serialHtml = `
                                        <div style="margin-top: 4px; display: inline-flex; align-items: center; gap: 4px; background: rgba(99, 102, 241, 0.08); color: #4f46e5; font-size: 0.72rem; padding: 2px 8px; border-radius: 6px; font-weight: 800;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2z"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg> S/N: ${item.serial_number}
                                        </div>
                                    `;
                                }
                            }
                        }

                        return `
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 700; color: #0f172a; ${isDescChanged ? 'background: rgba(16, 185, 129, 0.1); border-left: 3px solid #10b981;' : ''}">
                                <div>${item.description}</div>
                                ${serialHtml}
                                ${isDescChanged ? '<div style="font-size: 0.65rem; color: #10b981; margin-top: 4px;">Modified</div>' : ''}
                            </td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem; color: #64748b;">
                                ${item.unit || 'Package Types'}
                                ${item.location ? `
                                    <div style="font-size: 0.7rem; font-weight: 600; color: #4f46e5; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin" style="color: #4f46e5;"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                        ${item.location}
                                    </div>
                                ` : ''}
                            </td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: ${isQtyChanged ? '#10b981' : '#0f172a'}; text-align: right; ${isQtyChanged ? 'background: rgba(16, 185, 129, 0.1); border-left: 2px solid #10b981;' : ''}">${(parseFloat(item.qty) || 0).toLocaleString()}</td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: ${isStockChanged ? '#10b981' : '#4f46e5'}; text-align: right; ${isStockChanged ? 'background: rgba(16, 185, 129, 0.1); border-left: 2px solid #10b981;' : ''}">${(parseFloat(item.stock_balance) || 0).toLocaleString()}</td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #0284c7; text-align: right;">${(parseFloat(item.total_in_system) || 0).toLocaleString()}</td>
                            <td style="padding: 1rem 1.5rem; font-size: 0.8rem; color: #64748b; font-style: italic; max-width: 200px; word-break: break-word; ${isRemarksChanged ? 'background: rgba(16, 185, 129, 0.1); border-left: 2px solid #10b981;' : ''}">${item.remarks || '-- No specific notes --'}</td>
                        </tr>
                    `;
                    }).join('');

                    let previousHtml = '';
                    let proposedTitle = '';

                    if (data.request_type === 'edit_submission' && data.previous_batch) {
                        proposedTitle = `
                        <h3 style="font-size: 0.95rem; font-weight: 900; color: #10b981; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                            <i data-lucide="edit-3" style="width: 18px;"></i> Proposed Changes
                        </h3>
                    `;

                        const prevBatch = data.previous_batch;
                        const prevItemsHtml = prevBatch.items.map(item => {
                            let serialHtml = '';
                            if (item.serial_number) {
                                const sns = item.serial_number.split(',').map(s => s.trim()).filter(Boolean);
                                if (sns.length > 0) {
                                    const showSns = sns.slice(0, 3).join(', ');
                                    if (sns.length > 3) {
                                        const hiddenSns = sns.slice(3).join(', ');
                                        serialHtml = `
                                            <div class="serial-numbers-wrapper" style="margin-top: 4px; display: inline-flex; flex-wrap: wrap; align-items: center; gap: 4px;">
                                                <div style="display: inline-flex; align-items: center; flex-wrap: wrap; gap: 4px; background: rgba(220, 38, 38, 0.08); color: #dc2626; font-size: 0.72rem; padding: 2px 8px; border-radius: 6px; font-weight: 800; word-break: break-word; white-space: normal; max-width: 250px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2z"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg>
                                                    S/N: ${showSns}<span class="dots">...</span><span class="more-sns" style="display: none;">, ${hiddenSns}</span>
                                                </div>
                                                <button type="button" class="toggle-sns-btn" onclick="let container = this.previousElementSibling; let more = container.querySelector('.more-sns'); let dots = container.querySelector('.dots'); let isHidden = more.style.display === 'none'; more.style.display = isHidden ? 'inline' : 'none'; dots.style.display = isHidden ? 'none' : 'inline'; this.querySelector('.chevron-icon').style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';" style="background: transparent; border: none; padding: 2px; cursor: pointer; display: inline-flex; align-items: center; color: #dc2626; outline: none; transition: all 0.2s; border-radius: 4px;" onmouseover="this.style.background='rgba(220, 38, 38, 0.15)';" onmouseout="this.style.background='transparent';" title="Show more serial numbers">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="chevron-icon" style="transition: transform 0.2s;"><polyline points="6 9 12 15 18 9"/></svg>
                                                </button>
                                            </div>
                                        `;
                                    } else {
                                        serialHtml = `
                                            <div style="margin-top: 4px; display: inline-flex; align-items: center; gap: 4px; background: rgba(220, 38, 38, 0.08); color: #dc2626; font-size: 0.72rem; padding: 2px 8px; border-radius: 6px; font-weight: 800;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2z"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg> S/N: ${item.serial_number}
                                            </div>
                                        `;
                                    }
                                }
                            }
                            return `
                            <tr style="border-bottom: 1px solid #fee2e2;">
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 700; color: #7f1d1d;">
                                    <div>${item.description}</div>
                                    ${serialHtml}
                                </td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; color: #991b1b;">
                                    ${item.unit || 'Package Types'}
                                    ${item.location ? `
                                        <div style="font-size: 0.7rem; font-weight: 600; color: #7f1d1d; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin" style="color: #7f1d1d;"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                            ${item.location}
                                        </div>
                                    ` : ''}
                                </td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #7f1d1d; text-align: right;">${parseFloat(item.qty).toLocaleString()}</td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #7f1d1d; text-align: right;">${parseFloat(item.stock_balance).toLocaleString()}</td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 800; color: #7f1d1d; text-align: right;">${(parseFloat(item.total_in_system) || 0).toLocaleString()}</td>
                                <td style="padding: 1rem 1.5rem; font-size: 0.8rem; color: #991b1b; font-style: italic; max-width: 200px; word-break: break-word;">${item.remarks || '-- No specific notes --'}</td>
                            </tr>
                        `;
                        }).join('');

                        previousHtml = `
                    <div style="margin-bottom: 2.5rem;">
                        <h3 style="font-size: 0.95rem; font-weight: 900; color: #ef4444; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                            <i data-lucide="history" style="width: 18px;"></i> Original Entry (Before Edit)
                        </h3>
                        <div style="background: #fffafa; border-radius: 20px; border: 1px solid #fecaca; overflow: hidden; box-shadow: 0 4px 20px rgba(239, 68, 68, 0.05);">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead style="background: #fef2f2; border-bottom: 1px solid #fca5a5;">
                                    <tr>
                                        <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; font-weight: 800; color: #991b1b; text-transform: uppercase; letter-spacing: 0.05em;">Item Description</th>
                                        <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; font-weight: 800; color: #991b1b; text-transform: uppercase; letter-spacing: 0.05em;">Package Type</th>
                                        <th style="padding: 1.25rem 1.5rem; text-align: right; font-size: 0.75rem; font-weight: 800; color: #991b1b; text-transform: uppercase; letter-spacing: 0.05em;">Received Qty</th>
                                        <th style="padding: 1.25rem 1.5rem; text-align: right; font-size: 0.75rem; font-weight: 800; color: #991b1b; text-transform: uppercase; letter-spacing: 0.05em;">Stock Bal.</th>
                                        <th style="padding: 1.25rem 1.5rem; text-align: right; font-size: 0.75rem; font-weight: 800; color: #991b1b; text-transform: uppercase; letter-spacing: 0.05em;">Total in System</th>
                                        <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; font-weight: 800; color: #991b1b; text-transform: uppercase; letter-spacing: 0.05em;">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${prevItemsHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
                    }

                    let footerHtml = '';
                    const isEditSubmission = data.request_type === 'edit_submission';
                    if (data.status === 'pending') {
                        const rejectFn = isEditSubmission
                            ? `window.processEditRequest(${reqId}, 'canceled', this)`
                            : `window.processSraCreationApproval(${reqId}, 'rejected', this)`;
                        const approveFn = isEditSubmission
                            ? `window.processEditRequest(${reqId}, 'approved', this)`
                            : `window.processSraCreationApproval(${reqId}, 'approved', this)`;
                        footerHtml = `
                    <div id="oversight-actions-${reqId}" style="background: white; border-top: 1px solid #e2e8f0; padding: 1.5rem 3rem; display: flex; justify-content: flex-end; align-items: center; gap: 1rem; border-radius: 0 0 28px 28px; flex-shrink: 0;">
                        <button onclick="typeof window.rollbackEntry === 'function' ? window.rollbackEntry(${reqId}) : alert('Rollback functionality pending implementation')" style="margin-right: auto; background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                            <i data-lucide="rotate-ccw" style="width: 18px;"></i> Rollback
                        </button>
                        <button onclick="${rejectFn}" style="background: #ef4444; color: white; border: none; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                            <i data-lucide="x-circle" style="width: 18px;"></i> Reject
                        </button>
                        <button onclick="${approveFn}" style="background: #10b981; color: white; border: none; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                            <i data-lucide="check-circle" style="width: 18px;"></i> ${isEditSubmission ? 'Approve Changes' : 'Approve Entry'}
                        </button>
                    </div>
                `;
                    } else {
                        const isApproved = data.status === 'approved';
                        const color = isApproved ? '#10b981' : '#dc2626';
                        const bgColor = isApproved ? 'rgba(16, 185, 129, 0.1)' : 'rgba(220, 38, 38, 0.1)';
                        const labelText = isApproved ? 'APPROVED & SAVED' : 'REJECTED';
                        footerHtml = `
                    <div style="background: white; border-top: 1px solid #e2e8f0; padding: 1.5rem 3rem; display: flex; justify-content: center; align-items: center; gap: 1rem; border-radius: 0 0 28px 28px; flex-shrink: 0; position: relative;">
                        <button onclick="typeof window.rollbackEntry === 'function' ? window.rollbackEntry(${reqId}) : alert('Rollback functionality pending implementation')" style="position: absolute; left: 3rem; background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                            <i data-lucide="rotate-ccw" style="width: 18px;"></i> Rollback
                        </button>
                        <div style="padding: 12px 24px; border-radius: 12px; background: ${bgColor}; color: ${color}; font-weight: 950; border: 1.5px solid ${color}; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase;">
                            <i data-lucide="${isApproved ? 'check-circle' : 'alert-circle'}" style="width: 18px;"></i> ${labelText}
                        </div>
                    </div>
                `;
                    }

                    content = `
                <div style="background: white; padding: 3.5rem 3rem 2.5rem 3rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; position: relative;">
                    <button onclick="window.closeOversightPanel()" style="position: absolute; top: 1.5rem; right: 1.5rem; background: #f1f5f9; border: none; width: 36px; height: 36px; border-radius: 50%; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; z-index: 10;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a'" onmouseout="this.style.background='#f1f5f9'; this.style.color='#64748b'">
                        <i data-lucide="x" style="width: 18px;"></i>
                    </button>

                    <div style="display: flex; align-items: center; gap: 1.5rem;">
                        <div style="width: 56px; height: 56px; background: rgba(79, 70, 229, 0.1); color: #4f46e5; border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="package-search" style="width: 28px; height: 28px;"></i>
                        </div>
                        <div>
                            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 900; color: #0f172a; letter-spacing: -0.02em;">Stock Entry Details</h2>
                            <p style="margin: 0; font-size: 0.9rem; color: #64748b; font-weight: 500;">User: <b>${data.recorded_by_name}</b> &nbsp;&bull;&nbsp; Submission: ${data.created_at}</p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                        <div style="text-align: right;">
                            <label style="display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Received Date</label>
                            <span style="font-size: 0.95rem; font-weight: 700; color: #0f172a;">${formattedArrival}</span>
                        </div>
                        <div style="width: 1px; height: 35px; background: #e2e8f0; align-self: center;"></div>
                        <div style="text-align: right;">
                            <label style="display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Entry Date</label>
                            <span style="font-size: 0.95rem; font-weight: 700; color: #0f172a;">${formattedEntry}</span>
                        </div>
                        <div style="width: 1px; height: 35px; background: #e2e8f0; align-self: center;"></div>
                        <div style="text-align: right;">
                            <label style="display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Supply Status</label>
                            <span style="font-size: 0.95rem; font-weight: 700; color: #1e293b;">${batch.supplier_status || 'Full Delivery'}</span>
                        </div>
                        <div style="width: 1px; height: 35px; background: #e2e8f0; align-self: center;"></div>
                        <div style="text-align: right;">
                            <label style="display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Category</label>
                            <span style="font-size: 0.95rem; font-weight: 800; color: #4f46e5; background: rgba(79, 70, 229, 0.1); padding: 2px 10px; border-radius: 6px;">${data.ledge_name}</span>
                        </div>
                    </div>
                </div>

                <div style="padding: 2.5rem 3rem; flex: 1; overflow-y: auto;">
                    <div id="supplier-stats-inline-${reqId}"></div>
                    ${previousHtml}
                    ${proposedTitle}
                    <div style="background: white; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 2rem;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <tr>
                                    <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Item Description</th>
                                    <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Package Type</th>
                                    <th style="padding: 1.25rem 1.5rem; text-align: right; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Received Qty</th>
                                    <th style="padding: 1.25rem 1.5rem; text-align: right; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Stock Bal.</th>
                                    <th style="padding: 1.25rem 1.5rem; text-align: right; font-size: 0.75rem; font-weight: 800; color: #10b981; text-transform: uppercase; letter-spacing: 0.05em;">Total in System</th>
                                    <th style="padding: 1.25rem 1.5rem; text-align: left; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsHtml}
                            </tbody>
                            <tfoot style="background: #f8fafc; border-top: 2px solid #e2e8f0;">
                                <tr>
                                    <td colspan="2" style="padding: 1rem 1.5rem; font-size: 0.8rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">
                                        Total Items in This Entry
                                    </td>
                                    <td style="padding: 1rem 1.5rem; text-align: right; font-size: 1rem; font-weight: 900; color: #4f46e5;">
                                        ${batch.items.reduce((sum, i) => sum + (parseFloat(i.qty) || 0), 0).toLocaleString()}
                                    </td>
                                    <td style="padding: 1rem 1.5rem; text-align: right; font-size: 0.85rem; font-weight: 800; color: #94a3b8;">
                                        ${batch.items.reduce((sum, i) => sum + (parseFloat(i.stock_balance) || 0), 0).toLocaleString()} <span style="font-size: 0.7rem; font-weight: 600;">total bal.</span>
                                    </td>
                                    <td style="padding: 1rem 1.5rem;"></td>
                                    <td style="padding: 1rem 1.5rem;"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div style="padding: 1.5rem; background: #fffbeb; border-radius: 16px; border: 1px solid #fef3c7; display: flex; align-items: center; gap: 1.25rem;">
                        <div style="width: 40px; height: 40px; background: #f59e0b; color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i data-lucide="shield-alert" style="width: 20px;"></i>
                        </div>
                        <div style="flex: 1;">
                            <span style="display: block; font-size: 0.9rem; font-weight: 800; color: #92400e;">Entry Review in Progress</span>
                            <span style="font-size: 0.8rem; color: #b45309;">Please check the quantities carefully before you approve this entry.</span>
                        </div>
                    </div>
                </div>
                ${footerHtml}
            `;
                }

                document.getElementById('oversightPanelContent').innerHTML = content;
                document.getElementById('oversightOverlay').style.display = 'block';
                setTimeout(() => {
                    document.getElementById('oversightOverlay').classList.add('show');
                    document.getElementById('oversightSidePanel').classList.add('open');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }, 10);

                const providerName = batch ? ((batch.acquisition_type === 'Donor' ? (batch.donor_name || batch.supplier_name) : batch.supplier_name) || '') : '';
                const cleanProviderName = providerName.replace(/\s\[.*\]$/, '').trim();

                if (cleanProviderName && cleanProviderName !== 'N/A') {
                    fetch(`/api/supplier-stats/${encodeURIComponent(cleanProviderName)}`)
                        .then(r => r.json())
                        .then(sData => {
                            const inlineDiv = document.getElementById(`supplier-stats-inline-${reqId}`);
                            if (!sData.error && inlineDiv) {
                                const s = sData.supplier;
                                const stats = sData.stats;
                                inlineDiv.innerHTML = `
                                    <div style="margin-bottom: 2rem; background: #f8fafc; border-radius: 18px; border: 1px solid #e2e8f0; overflow: hidden;">

                                        <!-- Top: Avatar + Name + Stats row -->
                                        <div style="display: flex; align-items: stretch; gap: 0;">

                                            <!-- Avatar Block -->
                                            <div style="background: linear-gradient(170deg, #f0f9ff 0%, #e0f2fe 100%); padding: 1.5rem 1.25rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; min-width: 130px; flex-shrink: 0; border-right: 1px solid #bae6fd; position: relative; overflow: hidden;">
                                                <!-- Decorative ring -->
                                                <div style="position: absolute; top: -18px; right: -18px; width: 80px; height: 80px; border-radius: 50%; background: rgba(14, 165, 233, 0.08);"></div>
                                                <div style="position: absolute; bottom: -10px; left: -10px; width: 50px; height: 50px; border-radius: 50%; background: rgba(14, 165, 233, 0.06);"></div>

                                                <!-- Icon -->
                                                <div style="width: 52px; height: 52px; border-radius: 50%; background: white; border: 3px solid #7dd3fc; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 16px rgba(14, 165, 233, 0.2); position: relative; z-index: 1;">
                                                    <i data-lucide="building-2" style="width: 24px; height: 24px; color: #0284c7;"></i>
                                                </div>
                                                <!-- Initial badge -->
                                                <div style="background: #0284c7; color: white; font-size: 0.75rem; font-weight: 900; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; position: absolute; top: 50%; right: calc(50% - 38px); transform: translateY(-32px); border: 2px solid white; box-shadow: 0 2px 6px rgba(2,132,199,0.3); z-index: 2;">
                                                    ${s.name ? s.name.charAt(0).toUpperCase() : '?'}
                                                </div>
                                                <!-- Label -->
                                                <div style="background: #e0f2fe; border: 1px solid #bae6fd; color: #0369a1; font-size: 0.6rem; font-weight: 800; padding: 3px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.1em; position: relative; z-index: 1;">${batch.acquisition_type === 'Donor' ? 'Donor' : 'Supplier'}</div>
                                            </div>

                                            <!-- Name + Contact Info -->
                                            <div style="flex: 1; background: white; padding: 1.25rem 1.75rem; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: center; gap: 0.85rem;">
                                                <div>
                                                    <div style="font-size: 0.6rem; font-weight: 800; color: #06b6d4; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 3px;">${batch.acquisition_type === 'Donor' ? 'Donor Name' : 'Company Name'}</div>
                                                    <div style="font-size: 1.15rem; font-weight: 900; color: #0f172a; letter-spacing: -0.02em;">${s.name}</div>
                                                </div>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem 1.5rem;">
                                                    <div>
                                                        <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Contact Person</div>
                                                        <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">${batch.delivery_person || s.delivery_person || 'N/A'}</div>
                                                    </div>
                                                    <div>
                                                        <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Contact Person Number</div>
                                                        <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">${batch.delivery_phone || s.delivery_phone || 'N/A'}</div>
                                                    </div>
                                                    <div>
                                                        <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Company Phone</div>
                                                        <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">${s.phone || 'N/A'}</div>
                                                    </div>
                                                    <div>
                                                        <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Email</div>
                                                        <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">${s.email || 'N/A'}</div>
                                                    </div>
                                                    <div>
                                                        <div style="font-size: 0.58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">Address</div>
                                                        <div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">${s.address || 'N/A'}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Stats Panel -->
                                            <div style="background: #f0fdf4; padding: 1.25rem 1.5rem; display: flex; flex-direction: column; justify-content: center; gap: 1rem; min-width: 140px; flex-shrink: 0;">
                                                <div style="text-align: center;">
                                                    <div style="font-size: 2rem; font-weight: 900; color: #16a34a; line-height: 1;">${stats.total_deliveries.toLocaleString()}</div>
                                                    <div style="font-size: 0.6rem; font-weight: 800; color: #4ade80; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 4px;">Total Deliveries</div>
                                                </div>
                                                <div style="height: 1px; background: #bbf7d0;"></div>
                                                <div style="text-align: center;">
                                                    <div style="font-size: 0.85rem; font-weight: 800; color: #15803d; line-height: 1.2;">${stats.last_delivery}</div>
                                                    <div style="font-size: 0.6rem; font-weight: 800; color: #4ade80; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 4px;">Last Delivery</div>
                                                </div>
                                            </div>

                                        </div>

                                        <!-- Notes Footer -->
                                        ${s.desc ? `
                                        <div style="padding: 0.9rem 1.75rem; border-top: 1px solid #e2e8f0; display: flex; align-items: flex-start; gap: 10px; background: white;">
                                            <i data-lucide="info" style="width: 14px; height: 14px; color: #94a3b8; flex-shrink: 0; margin-top: 2px;"></i>
                                            <p style="margin: 0; font-size: 0.83rem; color: #64748b; line-height: 1.55;">${s.desc}</p>
                                        </div>` : ''}

                                    </div>
                                `;
                                if (typeof lucide !== 'undefined') lucide.createIcons();
                            }
                        })
                        .catch(e => { /* console print removed */ });
                }
            })
            .catch(err => {
                window._entryPreviewLoading = false;
                window.closeOversightPanel();
                /* console print removed */
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `<i data-lucide="eye" style="width:16px;"></i> Preview Entry Details`;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'Could not load entry details.', 'error');
                else alert('Could not load entry details.');
            });
    };

    window.closeRemainderPreview = function() {
        if (typeof Swal !== 'undefined') Swal.close();
    };

    window.showSupplierDetails = function(supplierName) {
        if (typeof Swal === 'undefined') return;

        Swal.fire({
            title: 'Loading Supplier Details...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch(`/api/supplier-stats/${encodeURIComponent(supplierName)}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.error) throw new Error(data.error);

                const s = data.supplier;
                const stats = data.stats;

                const html = `
                    <div style="text-align: left; padding: 10px;">
                        <div style="display: flex; gap: 15px; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                            <div style="width: 50px; height: 50px; background: rgba(79, 70, 229, 0.1); color: #4f46e5; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i data-lucide="building-2" style="width: 24px; height: 24px;"></i>
                            </div>
                            <div>
                                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0f172a;">${s.name}</h3>
                                <p style="margin: 4px 0 0; font-size: 0.85rem; color: #64748b;">Supplier / Partner</p>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                            <div style="background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0;">
                                <div style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Total Deliveries</div>
                                <div style="font-size: 1.5rem; font-weight: 900; color: #4f46e5;">${stats.total_deliveries.toLocaleString()}</div>
                            </div>
                            <div style="background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0;">
                                <div style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Total Items Supplied</div>
                                <div style="font-size: 1.5rem; font-weight: 900; color: #10b981;">${stats.total_items.toLocaleString()}</div>
                            </div>
                        </div>

                        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px;">
                            <h4 style="margin: 0 0 15px 0; font-size: 0.85rem; font-weight: 800; color: #1e293b; text-transform: uppercase; letter-spacing: 0.05em;">Contact Information</h4>

                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <i data-lucide="user" style="width: 16px; color: #94a3b8; margin-top: 2px;"></i>
                                    <div>
                                        <div style="font-size: 0.7rem; font-weight: 700; color: #94a3b8;">Contact Person</div>
                                        <div style="font-size: 0.9rem; font-weight: 600; color: #334155;">${s.delivery_person || 'N/A'}</div>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <i data-lucide="phone" style="width: 16px; color: #94a3b8; margin-top: 2px;"></i>
                                    <div>
                                        <div style="font-size: 0.7rem; font-weight: 700; color: #94a3b8;">Contact Person Number</div>
                                        <div style="font-size: 0.9rem; font-weight: 600; color: #334155;">${s.delivery_phone || 'N/A'}</div>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <i data-lucide="phone" style="width: 16px; color: #94a3b8; margin-top: 2px;"></i>
                                    <div>
                                        <div style="font-size: 0.7rem; font-weight: 700; color: #94a3b8;">Company Phone</div>
                                        <div style="font-size: 0.9rem; font-weight: 600; color: #334155;">${s.phone || 'N/A'}</div>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <i data-lucide="mail" style="width: 16px; color: #94a3b8; margin-top: 2px;"></i>
                                    <div>
                                        <div style="font-size: 0.7rem; font-weight: 700; color: #94a3b8;">Email</div>
                                        <div style="font-size: 0.9rem; font-weight: 600; color: #334155;">${s.email || 'N/A'}</div>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <i data-lucide="map-pin" style="width: 16px; color: #94a3b8; margin-top: 2px;"></i>
                                    <div>
                                        <div style="font-size: 0.7rem; font-weight: 700; color: #94a3b8;">Address</div>
                                        <div style="font-size: 0.9rem; font-weight: 600; color: #334155;">${s.address || 'N/A'}</div>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <i data-lucide="calendar" style="width: 16px; color: #94a3b8; margin-top: 2px;"></i>
                                    <div>
                                        <div style="font-size: 0.7rem; font-weight: 700; color: #94a3b8;">Last Delivery</div>
                                        <div style="font-size: 0.9rem; font-weight: 600; color: #334155;">${stats.last_delivery}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                Swal.fire({
                    html: html,
                    showConfirmButton: true,
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#4f46e5',
                    width: 500,
                    customClass: {
                        popup: 'rounded-xl shadow-2xl border border-slate-100',
                        confirmButton: 'px-6 py-2.5 rounded-lg font-bold shadow-md'
                    },
                    didOpen: () => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                });
            })
            .catch(err => {
                /* console print removed */
                Swal.fire('Error', 'Could not load supplier details.', 'error');
            });
    };

    // Event delegation — catches comms preview clicks
    document.addEventListener('click', function(e) {
        const entryBtn = e.target.closest('button.entry-preview-btn, a.entry-preview-btn, button[data-entry-req-id], a[href*="sra-preview"]');
        if (entryBtn) {
            e.preventDefault();
            const reqId = entryBtn.getAttribute('data-entry-req-id') || entryBtn.href?.split('/').pop();
            if (reqId) {
                e.stopPropagation();
                window.showEntryPreview(reqId, entryBtn);
                return;
            }
        }

        const btn = e.target.closest('button.remainder-preview-btn, a.remainder-preview-btn, button[data-req-id], a[href*="remainder-preview"]');
        if (btn) {
            e.preventDefault();
            const reqId = btn.getAttribute('data-req-id') || btn.href?.split('/').pop();
            if (reqId) {
                e.stopPropagation();
                window.showRemainderPreview(reqId, btn);
                return;
            }
        }

        const rBtn = e.target.closest('button.recovery-preview-btn, a.recovery-preview-btn, [data-recovery-req-id]');
        if (rBtn) {
            e.preventDefault();
            const reqId = rBtn.getAttribute('data-recovery-req-id');
            if (reqId) {
                e.stopPropagation();
                window.showRecoveryPreview(reqId, rBtn);
                return;
            }
        }

        const recBtn = e.target.closest('button.reconciliation-preview-btn, a.reconciliation-preview-btn, [data-reconciliation-req-id]');
        if (recBtn) {
            e.preventDefault();
            const reqId = recBtn.getAttribute('data-reconciliation-req-id');
            if (reqId) {
                e.stopPropagation();
                window.showReconciliationPreview(reqId, recBtn);
                return;
            }
        }
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

    // ─────────────────────────────────────────────
    // Admin: Rollback Entry with Field-Level Notes
    // ─────────────────────────────────────────────
    window.rollbackEntry = function(reqId) {
        if (typeof Swal === 'undefined') { alert('Cannot open rollback dialog.'); return; }

        const FIELDS = [
            { key: 'supplier_status',  label: 'Delivery Status' },
            { key: 'arrival_date',     label: 'Received Date (Manual)' },
            { key: 'item_qty',         label: 'Received Qty' },
            { key: 'item_unit',        label: 'Package Type' },
            { key: 'item_description', label: 'Item Description' },
            { key: 'supplier_name',    label: 'Donor Name' },
        ];

        const standardPackages = ['PIECE(S)', 'PACK', 'BOXES', 'CARTON', 'BAG', 'ROLL', 'SET', 'REAM', 'BOTTLE'];

        const fieldsHtml = FIELDS.map(f => {
            let inputHtml = '';
            let onchangeJs = `this.closest('.rb-field-row').querySelector('.rb-note-wrap').style.display = this.checked ? 'block' : 'none'; this.closest('.rb-field-row').style.background = this.checked ? '#fff5f5' : '#f8fafc'; this.closest('.rb-field-row').style.borderColor = this.checked ? '#fca5a5' : '#e2e8f0';`;
            
            if (f.key === 'item_unit') {
                inputHtml = `
                    <select class="rb-field-note select2-unit-rollback" data-key="${f.key}" style="width: 100%;">
                        <option value="">Select recommended package type...</option>
                        ${standardPackages.map(pkg => `<option value="${pkg}">${pkg}</option>`).join('')}
                    </select>
                `;
                onchangeJs += ` if (this.checked) { setTimeout(() => { $(this.closest('.rb-field-row')).find('.select2-unit-rollback').select2({ placeholder: 'Select or type package type...', tags: true, width: '100%', dropdownParent: $('.swal-rollback-popup') }); }, 50); }`;
            } else {
                inputHtml = `
                    <input type="text" class="rb-field-note" data-key="${f.key}"
                           placeholder="Correction note for this field (e.g. 'Use XYZ Ltd instead')..."
                           style="width: 100%; font-size: 0.82rem; border: 1.5px solid #fca5a5; border-radius: 8px; padding: 7px 10px; font-family: inherit; color: #1e293b; background: white; outline: none; box-sizing: border-box;"
                           onfocus="this.style.borderColor='#ef4444'; this.style.boxShadow='0 0 0 3px rgba(239,68,68,0.12)'"
                           onblur="this.style.borderColor='#fca5a5'; this.style.boxShadow='none'">
                `;
            }

            return `
            <div class="rb-field-row" style="display: flex; flex-direction: column; gap: 6px; padding: 10px; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc; transition: background 0.2s;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none;">
                    <input type="checkbox" class="rb-field-check" data-key="${f.key}"
                           style="width: 16px; height: 16px; accent-color: #ef4444; cursor: pointer; flex-shrink: 0;"
                           onchange="${onchangeJs}">
                    <span style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">${f.label}</span>
                </label>
                <div class="rb-note-wrap" style="display: none; padding-left: 24px; margin-top: 4px;">
                    ${inputHtml}
                </div>
            </div>
            `;
        }).join('');

        Swal.fire({
            html: `
                <div style="text-align: left;">
                    <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); margin: -1.25em -1.25em 1.5em; padding: 2rem 2rem 1.5rem; border-radius: 4px 4px 0 0; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.07); border-radius: 50%;"></div>
                        <div style="display: flex; align-items: center; gap: 14px; position: relative;">
                            <div style="width: 46px; height: 46px; background: rgba(255,255,255,0.15); border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg style="width: 24px; height: 24px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                            <div>
                                <div style="font-size: 0.68rem; font-weight: 800; color: rgba(255,255,255,0.75); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 2px;">Admin Action</div>
                                <div style="font-size: 1.25rem; font-weight: 900; color: white; letter-spacing: -0.02em;">Rollback & Request Corrections</div>
                            </div>
                        </div>
                    </div>

                    <p style="font-size: 0.88rem; color: #64748b; line-height: 1.6; margin-bottom: 1.25rem;">
                        Select the fields that need to be corrected and provide a brief note for each. The user will see these highlighted in <b style="color: #ef4444;">red</b> on their form.
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 8px; max-height: 340px; overflow-y: auto; padding-right: 4px; margin-bottom: 1.25rem;">
                        ${fieldsHtml}
                    </div>

                    <div style="margin-bottom: 0.25rem;">
                        <label style="font-size: 0.78rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; display: block; margin-bottom: 6px;">General Note (Optional)</label>
                        <textarea id="rb-general-note" placeholder="Overall feedback or instructions for the user..." rows="3"
                            style="width: 100%; font-size: 0.88rem; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 10px 14px; font-family: inherit; color: #1e293b; background: #f8fafc; outline: none; resize: vertical; box-sizing: border-box;"
                            onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 4px rgba(245,158,11,0.1)'; this.style.background='white'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'; this.style.background='#f8fafc'"></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '&#8630;&nbsp; Send Back for Correction',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#94a3b8',
            width: 600,
            customClass: {
                popup: 'swal-rollback-popup',
                confirmButton: 'swal-rollback-confirm-btn',
            },
            didOpen: () => {
                if (!document.getElementById('swal-rollback-styles')) {
                    const st = document.createElement('style');
                    st.id = 'swal-rollback-styles';
                    st.textContent = `
                        .swal2-container {
                            z-index: 100000 !important;
                            backdrop-filter: blur(8px) !important;
                            -webkit-backdrop-filter: blur(8px) !important;
                            background-color: rgba(15, 23, 42, 0.4) !important;
                        }
                        .swal-rollback-popup {
                            border-radius: 24px !important;
                            overflow: hidden !important;
                            padding: 1.25em !important;
                        }
                        .swal-rollback-confirm-btn {
                            border-radius: 10px !important;
                            font-weight: 800 !important;
                            padding: 12px 24px !important;
                            font-size: 0.9rem !important;
                        }
                        .swal-rollback-popup .select2-container--default .select2-selection--single {
                            height: 38px !important;
                            border-radius: 8px !important;
                            border: 1.5px solid #fca5a5 !important;
                            display: flex !important;
                            align-items: center !important;
                            background: white !important;
                            outline: none !important;
                            box-sizing: border-box !important;
                        }
                        .swal-rollback-popup .select2-container--default .select2-selection--single .select2-selection__rendered {
                            color: #1e293b !important;
                            font-size: 0.82rem !important;
                            font-weight: 700 !important;
                            padding-left: 10px !important;
                            line-height: 36px !important;
                        }
                        .swal-rollback-popup .select2-container--default .select2-selection--single .select2-selection__arrow {
                            height: 36px !important;
                        }
                    `;
                    document.head.appendChild(st);
                }
            },
            preConfirm: () => {
                const flaggedFields = {};
                document.querySelectorAll('.rb-field-check:checked').forEach(cb => {
                    const key  = cb.getAttribute('data-key');
                    const note = cb.closest('.rb-field-row').querySelector('.rb-field-note').value.trim();
                    flaggedFields[key] = note || 'Please review and correct this field.';
                });
                const generalNote = document.getElementById('rb-general-note').value.trim();
                if (Object.keys(flaggedFields).length === 0 && !generalNote) {
                    Swal.showValidationMessage('<span style="font-size:0.85rem;">⚠ Please select at least one field to flag or provide a general note.</span>');
                    return false;
                }
                return { flaggedFields, generalNote };
            }
        }).then(result => {
            if (!result.isConfirmed) return;

            const { flaggedFields, generalNote } = result.value;

            // Find and disable the rollback button that triggered this
            const rollbackBtns = document.querySelectorAll(`button[onclick*="rollbackEntry(${reqId})"]`);
            rollbackBtns.forEach(b => { b.disabled = true; b.innerHTML = '<i data-lucide="loader" class="animate-spin" style="width:14px;"></i> Rolling back...'; });
            if (typeof lucide !== 'undefined') lucide.createIcons();

            fetch(`{{ url('/sra-creation') }}/${reqId}/rollback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    flagged_fields: flaggedFields,
                    general_note: generalNote,
                })
            })
            .then(res => {
                if (!res.ok) throw new Error('Server error');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    // Close oversight panel
                    if (typeof window.closeOversightPanel === 'function') window.closeOversightPanel();

                    // Update the action area in chat bubble
                    const actionsDiv = document.getElementById(`sra-creation-actions-${reqId}`);
                    if (actionsDiv) {
                        actionsDiv.innerHTML = `<div style="padding: 12px 20px; border-radius: 12px; background: rgba(245,158,11,0.1); color: #d97706; font-weight: 900; border: 1.5px solid #f59e0b; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.85rem;">
                            <i data-lucide="rotate-ccw" style="width: 16px;"></i> ROLLED BACK — AWAITING CORRECTION
                        </div>`;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }

                    if (typeof window.playNotificationSound === 'function') {
                        window.playNotificationSound('sent');
                    }
                    showToast('Rolled Back', 'Entry sent back to user for correction.', 'success');
                } else {
                    showToast('Rollback Failed', data.message || 'Error processing rollback.', 'error');
                    rollbackBtns.forEach(b => { b.disabled = false; b.innerHTML = '<i data-lucide="rotate-ccw" style="width:18px;"></i> Rollback'; });
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            })
            .catch(err => {
                /* console print removed */
                showToast('System Error', 'Could not complete rollback.', 'error');
                rollbackBtns.forEach(b => { b.disabled = false; b.innerHTML = '<i data-lucide="rotate-ccw" style="width:18px;"></i> Rollback'; });
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        });
    };

    // Relocate oversight elements to body to ensure fixed overlay covers viewport without parent transforms clipping it
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('oversightOverlay');
        const panel = document.getElementById('oversightSidePanel');
        if (overlay && panel) {
            document.body.appendChild(overlay);
            document.body.appendChild(panel);
        }
    });
</script>
@endsection
