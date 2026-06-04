@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.dashboard')

@section('title', 'Notifications Center')

@section('content')
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
        <div style="padding: 1.5rem 2rem; background: rgba(99, 102, 241, 0.03); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 2rem;">
                <button class="tab-btn active" onclick="setNotifTab('all')" id="tab-btn-all" style="background: transparent; border: none; font-weight: 800; color: var(--primary); font-size: 0.9rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--primary); cursor: pointer; transition: 0.2s;">All Notifications</button>
                <button class="tab-btn" onclick="setNotifTab('alert')" id="tab-btn-alert" style="background: transparent; border: none; font-weight: 700; color: var(--text-muted); font-size: 0.9rem; padding-bottom: 0.5rem; cursor: pointer; transition: 0.2s;">Alerts</button>
                <button class="tab-btn" onclick="setNotifTab('system')" id="tab-btn-system" style="background: transparent; border: none; font-weight: 700; color: var(--text-muted); font-size: 0.9rem; padding-bottom: 0.5rem; cursor: pointer; transition: 0.2s;">System</button>
            </div>
        </div>

        <div class="notifications-list">
                @php
                // Fetch acknowledged notifications from Database or Session
                try {
                    $acknowledged = \App\Models\NotificationAcknowledgement::where('user_id', auth()->id())
                        ->pluck('item_description')
                        ->toArray();
                } catch (\Exception $e) {
                    $acknowledged = session()->get('acknowledged_notifications', []);
                }

                // Fetch all unique items (excluding acknowledged)
                $items = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                    ->where('inventory_batches.supplier_status', '!=', 'System Draft')
                    ->selectRaw('TRIM(inventory_items.description) as description, SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
                    ->whereNotIn(\DB::raw('TRIM(inventory_items.description)'), array_map('trim', $acknowledged))
                    ->groupBy(\DB::raw('TRIM(inventory_items.description)'))
                    ->get();

                $allNotifs = [];
                foreach ($items as $item) {
                    $descLower = strtolower(trim($item->description));
                    $threshold = \App\Models\Setting::getItemThreshold($item->description);

                    if ($threshold > 0 && (float)$item->total_stock < $threshold) {
                        $unit = \App\Models\Setting::getItemUnit($item->description);

                        $allNotifs[] = [
                            'category' => 'alert',
                            'type' => 'warning', 
                            'title' => 'Low Stock: ' . $item->description, 
                            'message' => "Stock level (" . number_format($item->total_stock, 0) . " {$unit}) is below threshold (" . $threshold . ").", 
                            'icon' => 'alert-triangle',
                            'time' => 'Just now'
                        ];
                    }
                }

                // Fetch expired items details (Admins only)
                if (auth()->user()->is_admin) {
                    $expiredItems = \App\Models\InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
                        ->where('inventory_batches.supplier_status', '!=', 'System Draft')
                        ->selectRaw('TRIM(inventory_items.description) as description, SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock, SUM(CAST(REPLACE(inventory_items.qty, ",", "") AS DECIMAL(15,2))) as total_qty')
                        ->whereNotIn(\DB::raw('TRIM(inventory_items.description)'), array_map('trim', $acknowledged))
                        ->groupBy(\DB::raw('TRIM(inventory_items.description)'))
                        ->havingRaw('SUM(CAST(REPLACE(inventory_items.stock_balance, ",", "") AS DECIMAL(15,2))) = 0 AND SUM(CAST(REPLACE(inventory_items.qty, ",", "") AS DECIMAL(15,2))) >= 1')
                        ->get();
                        
                    foreach($expiredItems as $item) {
                        $allNotifs[] = [
                            'category' => 'alert',
                            'type' => 'danger', 
                            'title' => 'Expired Record: ' . $item->description, 
                            'message' => "Item registry indicates zero balance but exists in inventory records.", 
                            'icon' => 'alert-octagon',
                            'time' => 'Just now'
                        ];
                    }
                }

                // Fetch recent system logs
                $systemLogs = \App\Models\SystemLog::orderBy('created_at', 'desc')
                    ->limit(100)
                    ->get();
                    
                foreach($systemLogs as $log) {
                    $isConcerned = false;
                    if (auth()->user()->is_admin) {
                        $isConcerned = true;
                    } else {
                        $nameLower = strtolower(auth()->user()->name);
                        $usernameLower = strtolower(auth()->user()->username);
                        $descLower = strtolower($log->description);
                        
                        if (str_contains($descLower, $nameLower) || str_contains($descLower, $usernameLower)) {
                            $isConcerned = true;
                        } elseif ($log->user_id === auth()->id()) {
                            $action = strtoupper($log->action ?? '');
                            if (in_array($action, ['ADD_INVENTORY', 'EDIT_INVENTORY', 'SUPPLEMENT_INVENTORY', 'ISSUE_ITEM', 'RETURN_ITEM', 'AUTHORIZATION'])) {
                                $isConcerned = true;
                            }
                        }
                    }

                    if (!$isConcerned) {
                        continue;
                    }

                    $allNotifs[] = [
                        'category' => 'system',
                        'type' => 'info',
                        'title' => $log->action ? str_replace('_', ' ', $log->action) : ($log->event_type ? str_replace('_', ' ', $log->event_type) : 'System Event'),
                        'message' => $log->description,
                        'icon' => $log->severity === 'danger' || $log->severity === 'critical' ? 'shield-alert' : 'activity',
                        'time' => $log->created_at->diffForHumans()
                    ];
                }
                @endphp

            @forelse($allNotifs as $notif)
            @php
                $typeColor = '#f59e0b';
                $typeBg = 'rgba(245, 158, 11, 0.1)';
                if ($notif['type'] === 'danger') {
                    $typeColor = '#ef4444';
                    $typeBg = 'rgba(239, 68, 68, 0.1)';
                } elseif ($notif['type'] === 'info') {
                    $typeColor = '#3b82f6';
                    $typeBg = 'rgba(59, 130, 246, 0.1)';
                }
            @endphp
            <div class="notification-item" data-category="{{ $notif['category'] }}" style="display: flex; gap: 1rem; padding: 0.85rem 1.5rem; border-bottom: 1px solid var(--border-color); transition: background 0.2s ease; cursor: pointer; align-items: flex-start;" onmouseover="this.style.background='rgba(99, 102, 241, 0.015)'" onmouseout="this.style.background='transparent'">
                <i data-lucide="{{ $notif['icon'] }}" style="width: 16px; height: 16px; color: {{ $typeColor }}; margin-top: 0.15rem; flex-shrink: 0;"></i>
                <div style="flex: 1; display: flex; flex-direction: column; gap: 0.15rem;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin: 0; text-transform: capitalize; line-height: 1.3;">{{ $notif['title'] }}</h4>
                        <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500; white-space: nowrap; margin-left: 1rem;">{{ $notif['time'] }}</span>
                    </div>
                    <p style="color: var(--text-muted); line-height: 1.4; font-size: 0.8125rem; margin: 0;">
                        {{ $notif['message'] }}
                    </p>
                    <div style="display: flex; gap: 0.6rem; align-items: center; margin-top: 0.35rem; font-size: 0.75rem;">
                        @if($notif['category'] === 'alert')
                            <a href="{{ $notif['type'] === 'warning' ? route('inventory.low-stock') : (auth()->user()->is_admin ? route('admin.index') : route('dashboard')) }}" style="color: var(--primary); text-decoration: none; font-weight: 600; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">{{ $notif['type'] === 'warning' ? 'View Monitor' : 'Audit Now' }}</a>
                            <span style="color: var(--border-color); font-weight: 300;">|</span>
                            <button onclick="dismissNotification('{{ str_replace("'", "\'", explode(': ', $notif['title'])[1] ?? $notif['title']) }}')" style="background: transparent; border: none; padding: 0; color: var(--text-muted); font-weight: 500; cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='var(--text-main)'" onmouseout="this.style.color='var(--text-muted)'">Dismiss</button>
                        @else
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.logs') }}" style="color: var(--primary); text-decoration: none; font-weight: 600; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">View System Logs</a>
                            @endif
                        @endif
                    </div>
                </div>
                <div style="width: 6px; height: 6px; background: {{ $typeColor }}; border-radius: 50%; flex-shrink: 0; margin-top: 0.4rem; margin-left: 0.5rem;"></div>
            </div>
            @empty
            <div style="padding: 8rem 2rem; text-align: center;">
                <div style="background: var(--bg-main); width: 100px; height: 100px; border-radius: 30px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem auto; color: var(--text-muted); border: 1px solid var(--border-color); box-shadow: 0 15px 35px rgba(0,0,0,0.03);">
                    <i data-lucide="bell-off" style="width: 48px; opacity: 0.3;"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">You're all caught up!</h3>
                <p style="color: var(--text-muted); font-size: 1rem;">When you have new notifications, they'll appear here.</p>
                <button onclick="window.location.href='{{ auth()->user()->is_admin ? route('admin.index') : route('dashboard') }}'" class="btn-primary" style="margin-top: 2rem; padding: 0.85rem 2rem; border-radius: 12px; border: none; background: var(--primary); color: white; font-weight: 700; cursor: pointer;">Back to Dashboard</button>
            </div>
            @endforelse
        </div>
    </div>
</div>
    <script>
        let activeTab = 'all';

        function setNotifTab(tab) {
            activeTab = tab;
            
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
            
            applyFilter();
        }

        function applyFilter() {
            const items = document.querySelectorAll('.notification-item');
            let visibleCount = 0;
            
            items.forEach(item => {
                const cat = item.getAttribute('data-category');
                if (activeTab === 'all' || cat === activeTab) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            // Show empty state if no notifications match
            let emptyState = document.getElementById('no-notifs-state');
            if (visibleCount === 0) {
                if (!emptyState) {
                    const list = document.querySelector('.notifications-list');
                    const emptyHTML = `
                        <div id="no-notifs-state" style="padding: 8rem 2rem; text-align: center;">
                            <div style="background: var(--bg-main); width: 100px; height: 100px; border-radius: 30px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem auto; color: var(--text-muted); border: 1px solid var(--border-color); box-shadow: 0 15px 35px rgba(0,0,0,0.03);">
                                <i data-lucide="bell-off" style="width: 48px; opacity: 0.3;"></i>
                            </div>
                            <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">You're all caught up!</h3>
                            <p style="color: var(--text-muted); font-size: 1rem;">No notifications found in this category.</p>
                        </div>
                    `;
                    list.insertAdjacentHTML('beforeend', emptyHTML);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } else {
                    emptyState.style.display = 'block';
                }
            } else {
                if (emptyState) emptyState.remove();
            }
        }

        document.addEventListener('notificationsSynced', (e) => {
            const data = e.detail;
            const list = document.querySelector('.notifications-list');
            if (!list) return;

            if (!data.notifications || data.notifications.length === 0) {
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
            } else {
                let html = '';
                data.notifications.forEach(notif => {
                    let typeColor = '#f59e0b';
                    let typeBg = 'rgba(245, 158, 11, 0.1)';
                    if (notif.type === 'danger') {
                        typeColor = '#ef4444';
                        typeBg = 'rgba(239, 68, 68, 0.1)';
                    } else if (notif.type === 'info') {
                        typeColor = '#3b82f6';
                        typeBg = 'rgba(59, 130, 246, 0.1)';
                    }
                    
                    const timeLabel = notif.created_at || 'Just now';
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
                        <div class="notification-item" data-category="${category}" style="display: flex; gap: 1rem; padding: 0.85rem 1.5rem; border-bottom: 1px solid var(--border-color); transition: background 0.2s ease; cursor: pointer; align-items: flex-start;" onmouseover="this.style.background='rgba(99, 102, 241, 0.015)'" onmouseout="this.style.background='transparent'">
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
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
            applyFilter();
        });

        function markAllAsRead() {
            // Optimistic UI: Hide all items immediately
            const list = document.querySelector('.notifications-list');
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
            // Instant UI feedback: hide elements containing this description
            const items = document.querySelectorAll('.notification-item');
            items.forEach(item => {
                if (item.innerText.includes(description)) {
                    item.style.transition = '0.3s all';
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(20px)';
                    setTimeout(() => {
                        item.remove();
                        applyFilter();
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
