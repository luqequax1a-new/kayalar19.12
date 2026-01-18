{{-- Modern Notification Widget --}}
<div class="notif-widget">
    <button class="nav-btn" id="notifBtn">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <span class="notif-badge" id="notifBadge" style="display: none;">0</span>
    </button>

    <div class="notif-dropdown" id="notifDropdown">
        <div class="notif-header">
            <h4>Bildirimler</h4>
            <button class="btn-clear" id="btnClear" title="Tümünü okundu işaretle">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </button>
        </div>

        <div class="notif-tabs">
            <button class="notif-tab active" data-tab="all">
                Tümü
                <span class="tab-count" id="countAll">0</span>
            </button>
            <button class="notif-tab" data-tab="new_order">
                Siparişler
                <span class="tab-count" id="countOrders">0</span>
            </button>
            <button class="notif-tab" data-tab="new_customer">
                Müşteriler
                <span class="tab-count" id="countCustomers">0</span>
            </button>
            <button class="notif-tab" data-tab="new_ticket">
                Mesajlar
                <span class="tab-count" id="countTickets">0</span>
            </button>
            <button class="notif-tab" data-tab="new_question">
                Sorular
                <span class="tab-count" id="countQuestions">0</span>
            </button>
            <button class="notif-tab" data-tab="product_review">
                Yorumlar
                <span class="tab-count" id="countReviews">0</span>
            </button>
        </div>

        <div class="notif-list" id="notifList">
            <div class="notif-loading">
                <div class="spinner"></div>
                <p>Yükleniyor...</p>
            </div>
        </div>

        <div class="notif-footer">
            <a href="/admin/notifications">Tümünü Görüntüle</a>
        </div>
    </div>
</div>

<style>
.notif-widget {
    position: relative;
}

.notif-badge {
    position: absolute;
    top: 6px;
    right: 6px;
    min-width: 18px;
    height: 18px;
    background: #ef4444;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 5px;
}

.notif-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 380px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
    display: none;
    z-index: 1000;
}

.notif-dropdown.show {
    display: block;
}

.notif-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notif-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.btn-clear {
    width: 32px;
    height: 32px;
    border: none;
    background: #f9fafb;
    color: #6b7280;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-clear:hover {
    background: #e5e7eb;
}

.notif-tabs {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    border-bottom: 1px solid #f3f4f6;
}

.notif-tab {
    padding: 12px 8px;
    border: none;
    background: none;
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    position: relative;
    transition: all 0.2s;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.notif-tab:hover {
    color: #111827;
    background: #f9fafb;
}

.notif-tab.active {
    color: #111827;
}

.notif-tab.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: #111827;
}

.tab-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    background: #e5e7eb;
    color: #6b7280;
    font-size: 11px;
    font-weight: 600;
    border-radius: 10px;
}

.notif-tab.active .tab-count {
    background: #111827;
    color: #fff;
}

.notif-tab:hover .tab-count {
    background: #d1d5db;
}

.notif-list {
    max-height: 380px;
    overflow-y: auto;
}

.notif-list::-webkit-scrollbar {
    width: 4px;
}

.notif-list::-webkit-scrollbar-track {
    background: #f9fafb;
}

.notif-list::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 2px;
}

.notif-loading,
.notif-empty {
    padding: 48px 20px;
    text-align: center;
    color: #9ca3af;
}

.spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #f3f4f6;
    border-top-color: #111827;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
    margin: 0 auto 12px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.notif-item {
    padding: 14px 20px;
    border-bottom: 1px solid #f9fafb;
    cursor: pointer;
    transition: all 0.15s;
    position: relative;
}

.notif-item:hover {
    background: #f9fafb;
}

.notif-item.unread {
    background: #eff6ff;
}

.notif-item.unread::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: #3b82f6;
}

.notif-content {
    display: flex;
    gap: 12px;
}

.notif-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 18px;
}

.icon-blue { background: #dbeafe; }
.icon-green { background: #d1fae5; }
.icon-orange { background: #fed7aa; }
.icon-red { background: #fee2e2; }
.icon-purple { background: #ede9fe; }

.notif-info {
    flex: 1;
    min-width: 0;
}

.notif-title {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 4px;
}

.notif-message {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 4px;
    line-height: 1.4;
}

.notif-meta {
    font-size: 12px;
    color: #9ca3af;
    margin-bottom: 4px;
    font-weight: 500;
}

.notif-time {
    font-size: 12px;
    color: #9ca3af;
}

.notif-footer {
    padding: 14px 20px;
    border-top: 1px solid #f3f4f6;
    text-align: center;
}

.notif-footer a {
    color: #3b82f6;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
}

.notif-footer a:hover {
    color: #2563eb;
}

/* Toast Notification */
.toast-notification {
    position: fixed;
    top: 80px;
    right: 20px;
    min-width: 320px;
    max-width: 400px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    padding: 16px;
    display: flex;
    align-items: start;
    gap: 12px;
    z-index: 10000;
    animation: slideIn 0.3s ease-out;
    cursor: pointer;
    border-left: 4px solid #10b981;
}

.toast-notification:hover {
    box-shadow: 0 12px 48px rgba(0,0,0,0.2);
}

.toast-notification.hiding {
    animation: slideOut 0.3s ease-in forwards;
}

@keyframes slideIn {
    from {
        transform: translateX(120%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(120%);
        opacity: 0;
    }
}

.toast-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #10b981;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.toast-content {
    flex: 1;
}

.toast-title {
    font-weight: 600;
    font-size: 14px;
    color: #111827;
    margin-bottom: 4px;
}

.toast-message {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.4;
}

.toast-close {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #f3f4f6;
    border: none;
    color: #6b7280;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s;
}

.toast-close:hover {
    background: #e5e7eb;
    color: #111827;
}

</style>

<script>
(function() {
    const btn = document.getElementById('notifBtn');
    const dropdown = document.getElementById('notifDropdown');
    const list = document.getElementById('notifList');
    const badge = document.getElementById('notifBadge');
    const clearBtn = document.getElementById('btnClear');
    const tabs = document.querySelectorAll('.notif-tab');

    let notifications = [];
    let activeTab = 'all';

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('show');
        if (dropdown.classList.contains('show')) load();
    });

    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            activeTab = this.dataset.tab;
            render();
            
            // Mark notifications of this type as read
            markTabAsRead(activeTab);
        });
    });
    
    function markTabAsRead(tabType) {
        const filtered = tabType === 'all' 
            ? notifications 
            : notifications.filter(n => n.type === tabType || (tabType === 'new_order' && n.type === 'cart_recovered'));
        
        const unreadIds = filtered.filter(n => !n.is_read).map(n => n.id);
        
        if (unreadIds.length > 0) {
            unreadIds.forEach(id => {
                fetch(`/admin/api/notifications/${id}/read`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                }).catch(() => {});
            });
            
            // Update local state
            notifications.forEach(n => {
                if (unreadIds.includes(n.id)) {
                    n.is_read = true;
                }
            });
            
            // Refresh badge after a moment
            setTimeout(() => {
                fetch('/admin/api/notifications/unread-count')
                    .then(r => r.json())
                    .then(d => updateBadge(d.count));
            }, 500);
        }
    }

    function load() {
        fetch('/admin/api/notifications?limit=50')
            .then(r => r.json())
            .then(d => {
                notifications = d.notifications;
                updateBadge(d.unread_count);
                updateTabCounts();
                render();
            })
            .catch(() => {
                list.innerHTML = '<div class="notif-empty">Yüklenemedi</div>';
            });
    }
    
    function updateTabCounts() {
        // Count notifications by type
        const counts = {
            all: notifications.length,
            new_order: 0,
            new_customer: 0,
            new_ticket: 0,
            new_question: 0,
            product_review: 0
        };
        
        notifications.forEach(n => {
            if (n.type === 'new_order' || n.type === 'cart_recovered') {
                counts.new_order++;
            } else if (n.type === 'new_customer') {
                counts.new_customer++;
            } else if (n.type === 'new_ticket') {
                counts.new_ticket++;
            } else if (n.type === 'new_question') {
                counts.new_question++;
            } else if (n.type === 'product_review') {
                counts.product_review++;
            }
        });
        
        // Update tab count badges
        document.getElementById('countAll').textContent = counts.all;
        document.getElementById('countOrders').textContent = counts.new_order;
        document.getElementById('countCustomers').textContent = counts.new_customer;
        document.getElementById('countTickets').textContent = counts.new_ticket;
        document.getElementById('countQuestions').textContent = counts.new_question;
        document.getElementById('countReviews').textContent = counts.product_review;
    }

    function render() {
        const filtered = activeTab === 'all' 
            ? notifications 
            : notifications.filter(n => n.type === activeTab || (activeTab === 'new_order' && n.type === 'cart_recovered'));

        if (filtered.length === 0) {
            list.innerHTML = '<div class="notif-empty">Bildirim yok</div>';
            return;
        }

        list.innerHTML = filtered.map(n => {
            // Parse data - it's already an object from JSON response
            const data = n.data || {};
            
            // Build meta lines (each on separate line)
            let metaLines = '';
            
            if (n.type === 'new_order' || n.type === 'cart_recovered') {
                if (data.customer_name && String(data.customer_name) !== 'undefined') {
                    metaLines += `<div class="notif-meta">${data.customer_name}</div>`;
                }
                if (data.payment_method && String(data.payment_method) !== 'undefined') {
                    metaLines += `<div class="notif-meta">${data.payment_method}</div>`;
                }
            } else if (n.type === 'new_customer') {
                if (data.customer_name && String(data.customer_name) !== 'undefined') {
                    metaLines += `<div class="notif-meta">${data.customer_name}</div>`;
                }
                if (data.email && String(data.email) !== 'undefined') {
                    metaLines += `<div class="notif-meta">${data.email}</div>`;
                }
            } else if (n.type === 'new_question') {
                if (data.customer_name && String(data.customer_name) !== 'undefined') {
                    metaLines += `<div class="notif-meta">${data.customer_name}</div>`;
                }
                if (data.product_name && String(data.product_name) !== 'undefined') {
                    metaLines += `<div class="notif-meta">${data.product_name}</div>`;
                }
            } else if (n.type === 'product_review') {
                if (data.customer_name || data.reviewer_name) {
                    metaLines += `<div class="notif-meta">${data.customer_name || data.reviewer_name}</div>`;
                }
                if (data.rating) {
                    metaLines += `<div class="notif-meta">Puan: ⭐ ${data.rating}/5</div>`;
                }
            }
            
            return `
                <div class="notif-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}" data-link="${n.link || ''}">
                    <div class="notif-content">
                        <div class="notif-icon icon-${n.color}">
                            ${getEmoji(n.type)}
                        </div>
                        <div class="notif-info">
                            <div class="notif-title">${n.title || ''}</div>
                            <div class="notif-message">${n.message || ''}</div>
                            ${metaLines}
                            ${n.time_ago ? `<div class="notif-time">${n.time_ago}</div>` : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        document.querySelectorAll('.notif-item').forEach(item => {
            item.addEventListener('click', function() {
                markRead(this.dataset.id, this.dataset.link);
            });
        });
    }

    function getEmoji(type) {
        const emojis = {
            'new_order': '📦',
            'abandoned_cart': '🛒',
            'cart_recovered': '✅',
            'new_customer': '👤',
            'low_stock': '⚠️',
            'new_ticket': '💬',
            'new_question': '❓',
            'product_review': '⭐',
        };
        return emojis[type] || '🔔';
    }

    function updateBadge(count) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    function markRead(id, link) {
        fetch(`/admin/api/notifications/${id}/read`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(r => r.json())
        .then(d => {
            updateBadge(d.unread_count);
            if (link) window.location.href = link;
            else load();
        });
    }

    clearBtn.addEventListener('click', function() {
        fetch('/admin/api/notifications/mark-all-read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(() => {
            updateBadge(0);
            load();
        });
    });

    // Toast notification function (no permission needed!)
    function showToastNotification(notification) {
        // Remove any existing toast
        const existing = document.querySelector('.toast-notification');
        if (existing) {
            existing.remove();
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `
            <div class="toast-icon">📦</div>
            <div class="toast-content">
                <div class="toast-title">${notification.title}</div>
                <div class="toast-message">${notification.message}</div>
            </div>
            <button class="toast-close">×</button>
        `;
        
        // Add to page
        document.body.appendChild(toast);
        
        // Click to navigate
        toast.addEventListener('click', function(e) {
            if (!e.target.classList.contains('toast-close')) {
                if (notification.link) {
                    window.location.href = notification.link;
                }
            }
        });
        
        // Close button
        toast.querySelector('.toast-close').addEventListener('click', function(e) {
            e.stopPropagation();
            closeToast(toast);
        });
        
        // Auto close after 8 seconds
        setTimeout(() => closeToast(toast), 8000);
    }
    
    function closeToast(toast) {
        if (toast && toast.parentNode) {
            toast.classList.add('hiding');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }
    }

    // Smart polling: 3s when expecting new notifications, 30s otherwise
    let lastCount = {{ \FleetCart\Services\NotificationService::getUnreadCount() }};
    let pollInterval = 30000; // Start with 30s
    let hasRecentActivity = false;
    let shownNotifications = new Set(); // Track shown notifications
    
    function checkNotifications() {
        fetch('/admin/api/notifications/unread-count')
            .then(r => r.json())
            .then(d => {
                const newCount = d.count;
                updateBadge(newCount);
                
                // If count increased, we have new notifications
                if (newCount > lastCount) {
                    hasRecentActivity = true;
                    pollInterval = 3000; // Fast polling
                    
                    // Load full notifications to show toast notification
                    fetch('/admin/api/notifications?limit=5')
                        .then(r => r.json())
                        .then(data => {
                            const newNotifs = data.notifications.filter(n => !n.is_read && !shownNotifications.has(n.id));
                            if (newNotifs.length > 0) {
                                // Show only the newest one
                                showToastNotification(newNotifs[0]);
                                // Mark as shown
                                shownNotifications.add(newNotifs[0].id);
                            }
                        });
                    
                    load(); // Refresh list
                } else if (hasRecentActivity && newCount === lastCount) {
                    // No new notifications for a while, slow down
                    hasRecentActivity = false;
                    pollInterval = 30000; // Slow polling
                }
                
                // If dropdown is open, always refresh
                if (dropdown.classList.contains('show')) {
                    load();
                }
                
                lastCount = newCount;
                
                // Schedule next check with current interval
                setTimeout(checkNotifications, pollInterval);
            })
            .catch(() => {
                // On error, try again in 10s
                setTimeout(checkNotifications, 10000);
            });
    }
    
    // Start polling
    setTimeout(checkNotifications, 3000);

    load();
})();
</script>
