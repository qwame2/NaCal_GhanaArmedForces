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
                <button class="tab-btn active" style="background: transparent; border: none; font-weight: 800; color: var(--primary); font-size: 0.9rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--primary); cursor: pointer;">All Notifications</button>
                <button class="tab-btn" style="background: transparent; border: none; font-weight: 700; color: var(--text-muted); font-size: 0.9rem; padding-bottom: 0.5rem; cursor: pointer;">Alerts</button>
                <button class="tab-btn" style="background: transparent; border: none; font-weight: 700; color: var(--text-muted); font-size: 0.9rem; padding-bottom: 0.5rem; cursor: pointer;">System</button>
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

                // Fetch low stock items details
                $lowStockItems = \App\Models\InventoryItem::selectRaw('description, SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
                    ->whereNotIn(\DB::raw('TRIM(description)'), array_map('trim', $acknowledged))
                    ->groupBy('description')
                    ->havingRaw('SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) < 100')
                    ->get();

                // Fetch expired items details
                $expiredItems = \App\Models\InventoryItem::selectRaw('description, SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock, SUM(CAST(REPLACE(qty, ",", "") AS DECIMAL(15,2))) as total_qty')
                    ->whereNotIn(\DB::raw('TRIM(description)'), array_map('trim', $acknowledged))
                    ->groupBy('description')
                    ->havingRaw('SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) = 0 AND SUM(CAST(REPLACE(qty, ",", "") AS DECIMAL(15,2))) >= 1')
                    ->get();
                    
                $allNotifs = [];
                foreach($lowStockItems as $item) {
                    $allNotifs[] = ['type' => 'warning', 'title' => 'Low Stock: ' . $item->description, 'message' => "Critical balance detected: " . number_format($item->total_stock, 0) . " units remaining.", 'icon' => 'alert-triangle'];
                }
                foreach($expiredItems as $item) {
                    $allNotifs[] = ['type' => 'danger', 'title' => 'Expired Record: ' . $item->description, 'message' => "Item registry indicates zero balance but exists in inventory records.", 'icon' => 'alert-octagon'];
                }
            @endphp

            @forelse($allNotifs as $notif)
            <div class="notification-item" style="display: flex; gap: 1.5rem; padding: 2rem; border-bottom: 1px solid var(--border-color); transition: var(--transition); cursor: pointer;" onmouseover="this.style.background='rgba(99, 102, 241, 0.02)'" onmouseout="this.style.background='transparent'">
                <div style="width: 52px; height: 52px; border-radius: 16px; background: {{ $notif['type'] === 'warning' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; color: {{ $notif['type'] === 'warning' ? '#f59e0b' : '#ef4444' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 8px 15px {{ $notif['type'] === 'warning' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(239, 68, 68, 0.1)' }};">
                    <i data-lucide="{{ $notif['icon'] }}" style="width: 24px;"></i>
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                        <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0;">{{ $notif['title'] }}</h4>
                        <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Just now</span>
                    </div>
                    <p style="color: var(--text-muted); line-height: 1.6; font-size: 0.95rem; margin-bottom: 1rem;">
                        {{ $notif['message'] }}
                    </p>
                    <div style="display: flex; gap: 1rem;">
                        <a href="{{ auth()->user()->is_admin ? route('admin.index') : route('dashboard') }}" class="btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem; text-decoration: none; border-radius: 8px; background: {{ $notif['type'] === 'warning' ? 'var(--primary)' : '#ef4444' }}; color: white; font-weight: 700;">{{ $notif['type'] === 'warning' ? 'View Monitor' : 'Audit Now' }}</a>
                        <button onclick="dismissNotification('{{ explode(': ', $notif['title'])[1] }}')" class="btn-secondary" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-main); padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; cursor: pointer;">Dismiss</button>
                    </div>
                </div>
                <div style="width: 10px; height: 10px; background: var(--primary); border-radius: 50%; margin-top: 0.5rem;"></div>
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
        document.addEventListener('notificationsSynced', (e) => {
            const data = e.detail;
            const list = document.querySelector('.notifications-list');
            if (!list) return;

            if (data.count === 0) {
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
                    const typeColor = notif.type === 'warning' ? '#f59e0b' : '#ef4444';
                    const typeBg = notif.type === 'warning' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(239, 68, 68, 0.1)';
                    const routeUrl = notif.route === 'admin.index' ? "{{ route('admin.index') }}" : "{{ route('dashboard') }}";
                    
                    html += `
                        <div class="notification-item" style="display: flex; gap: 1.5rem; padding: 2rem; border-bottom: 1px solid var(--border-color); transition: var(--transition); cursor: pointer;" onmouseover="this.style.background='rgba(99, 102, 241, 0.02)'" onmouseout="this.style.background='transparent'">
                            <div style="width: 52px; height: 52px; border-radius: 16px; background: ${typeBg}; color: ${typeColor}; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 8px 15px ${typeBg};">
                                <i data-lucide="${notif.icon}" style="width: 24px;"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                    <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0;">${notif.title}</h4>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Just now</span>
                                </div>
                                <p style="color: var(--text-muted); line-height: 1.6; font-size: 0.95rem; margin-bottom: 1rem;">
                                    ${notif.message}
                                </p>
                            <div style="display: flex; gap: 1rem;">
                                <a href="${routeUrl}" class="btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem; text-decoration: none; border-radius: 8px; background: ${typeColor}; color: white; font-weight: 700;">${notif.type === 'warning' ? 'View Monitor' : 'Audit Now'}</a>
                                <button onclick="dismissNotification('${notif.title.split(': ')[1]}')" class="btn-secondary" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-main); padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; cursor: pointer;">Dismiss</button>
                            </div>
                        </div>
                        <div style="width: 10px; height: 10px; background: var(--primary); border-radius: 50%; margin-top: 0.5rem;"></div>
                    </div>
                `;
            });
            list.innerHTML = html;
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
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

        fetch("{{ route('api.notifications.mark-all-read') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (window.refreshNotifications) window.refreshNotifications();
                // If on dashboard layout, showToast is available
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
                setTimeout(() => item.remove(), 300);
            }
        });

        fetch("{{ route('api.notifications.dismiss') }}", {
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
