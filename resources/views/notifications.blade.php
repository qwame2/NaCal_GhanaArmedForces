@extends((auth()->user()->is_admin && !auth()->user()->isMainAdminOrSub()) ? 'layouts.admin' : 'layouts.dashboard')

@section('title', 'Notifications Center')

@section('content')
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
<div class="animate-slide-up">
    <div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="font-size: 2.25rem; font-weight: 900; letter-spacing: -0.04em; color: var(--text-main); margin-bottom: 0.25rem;">Notifications <span style="color: var(--primary);">Center</span></h2>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Stay updated with inventory alerts and system activities.</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button onclick="markAllAsRead()" class="btn-secondary" style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 0.75rem 1.5rem; border-radius: 12px; color: var(--text-main); font-weight: 700; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; transition: var(--transition);">
                <i data-lucide="check-check" style="width: 18px;"></i>
                Mark All as Read
            </button>
        </div>
    </div>

    <div class="glass-card" style="padding: 0; overflow: hidden; border-radius: 24px;">
        <div style="padding: 1.5rem 2rem; background: rgba(22, 163, 74, 0.03); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 2rem;">
                <button class="tab-btn active" onclick="setNotifTab('all')" id="tab-btn-all" style="background: transparent; border: none; font-weight: 800; color: var(--primary); font-size: 0.9rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--primary); cursor: pointer; transition: 0.2s;">All Notifications</button>
                <button class="tab-btn" onclick="setNotifTab('alert')" id="tab-btn-alert" style="background: transparent; border: none; font-weight: 700; color: var(--text-muted); font-size: 0.9rem; padding-bottom: 0.5rem; cursor: pointer; transition: 0.2s;">Alerts</button>
                <button class="tab-btn" onclick="setNotifTab('system')" id="tab-btn-system" style="background: transparent; border: none; font-weight: 700; color: var(--text-muted); font-size: 0.9rem; padding-bottom: 0.5rem; cursor: pointer; transition: 0.2s;">System</button>
            </div>
        </div>

        <div class="notifications-list">
            <div id="notif-loading-state" style="padding: 4rem 2rem; text-align: center; color: var(--text-muted);">
                <div class="loading-spinner" style="margin: 0 auto 1rem auto; width: 32px; height: 32px; border: 3px solid var(--border-color); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="font-size: 0.875rem; font-weight: 500;">Loading notifications...</p>
            </div>
        </div>
        <!-- Pagination Footer -->
        <div id="notifications-pagination" style="padding: 1.25rem 2rem; border-top: 1px solid var(--border-color); display: none; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; background: rgba(22, 163, 74, 0.01); border-radius: 0 0 24px 24px;">
            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
                <span id="notif-page-info">Showing 0 - 0 of 0 notifications</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.4rem;" id="notif-pagination-nav">
                <!-- Navigation buttons injected dynamically -->
            </div>
        </div>
    </div>
</div>

<script>
    let activeTab = 'all';
    let currentPage = 1;
    const pageSize = 10;
    let totalItems = 0;
    let lastPage = 1;

    function setNotifTab(tab) {
        activeTab = tab;
        currentPage = 1;
        
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.color = 'var(--text-muted)';
            btn.style.fontWeight = '700';
            btn.style.borderBottom = 'none';
        });
        
        const btn = document.getElementById(`tab-btn-${tab}`);
        if (btn) {
            btn.classList.add('active');
            btn.style.color = 'var(--primary)';
            btn.style.fontWeight = '800';
            btn.style.borderBottom = '2px solid var(--primary)';
        }
        
        loadNotifications(currentPage);
    }

    function loadNotifications(page) {
        currentPage = page;
        const list = document.querySelector('.notifications-list');
        const paginationFooter = document.getElementById('notifications-pagination');
        
        if (list) {
            list.innerHTML = `
                <div id="notif-loading-state" style="padding: 4rem 2rem; text-align: center; color: var(--text-muted);">
                    <div class="loading-spinner" style="margin: 0 auto 1rem auto; width: 32px; height: 32px; border: 3px solid var(--border-color); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <p style="font-size: 0.875rem; font-weight: 500;">Loading notifications...</p>
                </div>
            `;
        }

        fetch(`{{ route('api.notifications', [], false) }}?tab=${activeTab}&page=${page}&per_page=${pageSize}`)
        .then(res => res.json())
        .then(data => {
            totalItems = data.total;
            lastPage = data.last_page;

            // Handle empty state
            if (!data.notifications || data.notifications.length === 0) {
                if (paginationFooter) paginationFooter.style.display = 'none';
                list.innerHTML = `
                    <div id="no-notifs-state" style="padding: 8rem 2rem; text-align: center;">
                        <div style="background: var(--bg-main); width: 100px; height: 100px; border-radius: 30px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem auto; color: var(--text-muted); border: 1px solid var(--border-color); box-shadow: 0 15px 35px rgba(0,0,0,0.03);">
                            <i data-lucide="bell-off" style="width: 48px; opacity: 0.3;"></i>
                        </div>
                        <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">You're all caught up!</h3>
                        <p style="color: var(--text-muted); font-size: 1rem;">No notifications found in this category.</p>
                    </div>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            } else {
                if (paginationFooter) paginationFooter.style.display = 'flex';
                
                let html = '';
                data.notifications.forEach(notif => {
                    let typeColor = '#10b981';
                    let typeBg = 'rgba(16, 185, 129, 0.1)';
                    if (notif.type === 'danger') {
                        typeColor = '#ef4444';
                        typeBg = 'rgba(239, 68, 68, 0.1)';
                    } else if (notif.type === 'info') {
                        typeColor = '#16a34a';
                        typeBg = 'rgba(59, 130, 246, 0.1)';
                    }
                    
                    const timeLabel = notif.time || 'Just now';
                    const category = notif.category || 'alert';
                    
                    let actionButtons = '';
                    if (category === 'alert') {
                        let routeUrl = "{{ route('dashboard') }}";
                        if (notif.route === 'inventory.low-stock') {
                            routeUrl = "{{ route('inventory.low-stock') }}";
                        } else if (notif.route === 'admin.index') {
                            routeUrl = "{{ route('admin.index') }}";
                        } else if (notif.route === 'admin.logs') {
                            routeUrl = "{{ route('admin.logs') }}";
                        }
                        const cleanTitle = (notif.title.includes(': ') ? notif.title.split(': ')[1] : notif.title).replace(/'/g, "\\'");
                        actionButtons = `
                            <div style="display: flex; gap: 0.6rem; align-items: center; margin-top: 0.35rem; font-size: 0.75rem;">
                                <a href="${routeUrl}" style="color: var(--primary); text-decoration: none; font-weight: 600; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">${notif.type === 'warning' ? 'View Monitor' : 'Audit Now'}</a>
                                <span style="color: var(--border-color); font-weight: 300;">|</span>
                                <button onclick="dismissNotification('${cleanTitle}')" style="background: transparent; border: none; padding: 0; color: var(--text-muted); font-weight: 500; cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='var(--text-main)'" onmouseout="this.style.color='var(--text-muted)'">Dismiss</button>
                            </div>
                        `;
                    } else {
                        const isAdmin = {{ auth()->user()->is_admin ? 'true' : 'false' }};
                        if (isAdmin) {
                            actionButtons = `
                                <div style="display: flex; gap: 0.6rem; align-items: center; margin-top: 0.35rem; font-size: 0.75rem;">
                                    <a href="{{ route('admin.logs') }}" style="color: var(--primary); text-decoration: none; font-weight: 600; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">View System Logs</a>
                                </div>
                            `;
                        }
                    }

                    html += `
                        <div class="notification-item" data-category="${category}" style="display: flex; gap: 1rem; padding: 0.85rem 1.5rem; border-bottom: 1px solid var(--border-color); transition: background 0.2s ease; cursor: pointer; align-items: flex-start;" onmouseover="this.style.background='rgba(22, 163, 74, 0.015)'" onmouseout="this.style.background='transparent'">
                            <i data-lucide="${notif.icon}" style="width: 16px; height: 16px; color: ${typeColor}; margin-top: 0.15rem; flex-shrink: 0;"></i>
                            <div style="flex: 1; display: flex; flex-direction: column; gap: 0.15rem;">
                                <div style="display: flex; justify-content: space-between; align-items: baseline;">
                                    <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin: 0; text-transform: capitalize; line-height: 1.3;">${notif.title}</h4>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500; white-space: nowrap; margin-left: 1rem;">${timeLabel}</span>
                                </div>
                                <p style="color: var(--text-muted); line-height: 1.4; font-size: 0.8125rem; margin: 0;">
                                    ${notif.message}
                                </p>
                                ${actionButtons}
                            </div>
                            <div style="width: 6px; height: 6px; background: ${typeColor}; border-radius: 50%; flex-shrink: 0; margin-top: 0.4rem; margin-left: 0.5rem;"></div>
                        </div>
                    `;
                });
                list.innerHTML = html;
                if (typeof lucide !== 'undefined') lucide.createIcons();
                
                // Update Info Badge
                const startIndex = (page - 1) * pageSize;
                const endIndex = Math.min(startIndex + data.notifications.length, totalItems);
                const infoText = document.getElementById('notif-page-info');
                if (infoText) {
                    infoText.textContent = `Showing ${startIndex + 1} - ${endIndex} of ${totalItems} notifications`;
                }

                // Update Navigation Buttons
                const navContainer = document.getElementById('notif-pagination-nav');
                if (navContainer) {
                    let navHTML = '';
                    
                    // Prev button
                    navHTML += `
                        <button onclick="goToPage(${page - 1})" ${page === 1 ? 'disabled' : ''} style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid var(--border-color); background: ${page === 1 ? 'var(--bg-main)' : 'var(--bg-card)'}; color: ${page === 1 ? 'var(--text-muted)' : 'var(--text-main)'}; cursor: ${page === 1 ? 'not-allowed' : 'pointer'}; opacity: ${page === 1 ? '0.5' : '1'}; transition: all 0.2s;" onmouseover="if(${page !== 1}){this.style.borderColor='var(--primary)'; this.style.color='var(--primary)';}" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">
                            <i data-lucide="chevron-left" style="width: 14px; height: 14px;"></i>
                        </button>
                    `;

                    // Page buttons
                    const startPage = Math.max(1, page - 2);
                    const endPage = Math.min(lastPage, page + 2);

                    if (startPage > 1) {
                        navHTML += `
                            <button onclick="goToPage(1)" style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">1</button>
                        `;
                        if (startPage > 2) {
                            navHTML += `<span style="color: var(--text-muted); font-size: 0.85rem; padding: 0 4px;">...</span>`;
                        }
                    }

                    for (let i = startPage; i <= endPage; i++) {
                        const isActive = (i === page);
                        navHTML += `
                            <button onclick="goToPage(${i})" style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid ${isActive ? 'var(--primary)' : 'var(--border-color)'}; background: ${isActive ? 'var(--primary)' : 'var(--bg-card)'}; color: ${isActive ? 'white' : 'var(--text-main)'}; font-weight: 800; font-size: 0.8rem; cursor: pointer; transition: all 0.2s;" onmouseover="if(!${isActive}){this.style.borderColor='var(--primary)'; this.style.color='var(--primary)';}" onmouseout="if(!${isActive}){this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';}">${i}</button>
                        `;
                    }

                    if (endPage < lastPage) {
                        if (endPage < lastPage - 1) {
                            navHTML += `<span style="color: var(--text-muted); font-size: 0.85rem; padding: 0 4px;">...</span>`;
                        }
                        navHTML += `
                            <button onclick="goToPage(${lastPage})" style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">${lastPage}</button>
                        `;
                    }

                    // Next button
                    navHTML += `
                        <button onclick="goToPage(${page + 1})" ${page === lastPage ? 'disabled' : ''} style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid var(--border-color); background: ${page === lastPage ? 'var(--bg-main)' : 'var(--bg-card)'}; color: ${page === lastPage ? 'var(--text-muted)' : 'var(--text-main)'}; cursor: ${page === lastPage ? 'not-allowed' : 'pointer'}; opacity: ${page === lastPage ? '0.5' : '1'}; transition: all 0.2s;" onmouseover="if(${page !== lastPage}){this.style.borderColor='var(--primary)'; this.style.color='var(--primary)';}" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">
                            <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i>
                        </button>
                    `;

                    navContainer.innerHTML = navHTML;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            }
        });
    }

    function goToPage(page) {
        if (page < 1 || page > lastPage) return;
        loadNotifications(page);
        const card = document.querySelector('.glass-card');
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadNotifications(1);
    });

    document.addEventListener('notificationsSynced', (e) => {
        loadNotifications(currentPage);
    });

    function markAllAsRead() {
        const list = document.querySelector('.notifications-list');
        const paginationFooter = document.getElementById('notifications-pagination');
        if (list) {
            list.style.transition = '0.3s all';
            list.style.opacity = '0';
            setTimeout(() => {
                list.innerHTML = `
                    <div style="padding: 8rem 2rem; text-align: center;">
                        <div style="background: var(--bg-main); width: 100px; height: 100px; border-radius: 30px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem auto; color: var(--text-muted); border: 1px solid var(--border-color); box-shadow: 0 15px 35px rgba(0,0,0,0.03);">
                            <i data-lucide="bell-off" style="width: 48px; opacity: 0.3;"></i>
                        </div>
                        <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">You're all caught up!</h3>
                        <p style="color: var(--text-muted); font-size: 1rem;">When you have new notifications, they'll appear here.</p>
                        <button onclick="window.location.href='{{ auth()->user()->is_admin ? route('admin.index') : route('dashboard') }}'" class="btn-primary" style="margin-top: 2rem; padding: 0.85rem 2rem; border-radius: 12px; border: none; background: var(--primary); color: white; font-weight: 700; cursor: pointer;">Back to Dashboard</button>
                    </div>
                `;
                list.style.opacity = '1';
                if (paginationFooter) paginationFooter.style.display = 'none';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }, 300);
        }

        fetch("{{ route('api.notifications.mark-all-read', [], false) }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (window.refreshNotifications) window.refreshNotifications();
                if (typeof showToast === 'function') showToast('Synchronized', 'All notifications cleared.', 'success');
            }
        });
    }

    function dismissNotification(description) {
        const items = document.querySelectorAll('.notification-item');
        items.forEach(item => {
            if (item.innerText.includes(description)) {
                item.style.transition = '0.3s all';
                item.style.opacity = '0';
                item.style.transform = 'translateX(20px)';
                setTimeout(() => {
                    item.remove();
                    loadNotifications(currentPage);
                }, 300);
            }
        });

        fetch("{{ route('api.notifications.dismiss', [], false) }}", {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
            },
            body: JSON.stringify({ description: description })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (window.refreshNotifications) window.refreshNotifications();
            }
        });
    }
</script>
@endsection
