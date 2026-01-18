<div class="dashboard-panel dashboard-notifs" id="dashboardNotifs">
    <div class="db-flow-header">
        <div class="db-flow-top">
            <h5 class="db-flow-title"><i class="fa fa-history"></i> Mağaza Akışı</h5>
            <div class="db-flow-periods">
                <button class="flow-p-btn" data-period="today">Bugün</button>
                <button class="flow-p-btn active" data-period="weekly">Bu Hafta</button>
                <button class="flow-p-btn" data-period="all">Tümü</button>
            </div>
        </div>
        
        <div class="db-flow-tabs-wrapper">
            <div class="db-flow-tabs">
                <button class="flow-tab active" data-tab="all">Hepsi</button>
                <button class="flow-tab" data-tab="new_order">Siparişler</button>
                <button class="flow-tab" data-tab="new_customer">Yeni Üyeler</button>
                <button class="flow-tab" data-tab="new_question">Sorular</button>
                <button class="flow-tab" data-tab="product_review">Yorumlar</button>
                <button class="flow-tab" data-tab="new_ticket">Mesajlar</button>
            </div>
        </div>
    </div>
    
    <div class="dashboard-notif-content">
        <div class="notif-scroll-area" id="dbNotifList">
            <div class="notif-loading-state">
                <div class="db-loader"></div>
                <span>Yükleniyor...</span>
            </div>
        </div>
    </div>
    
    <div class="dashboard-panel-footer">
        <a href="/admin/notifications" class="view-all-link">
            Tüm Bildirimleri Yönet <i class="fa fa-angle-right"></i>
        </a>
    </div>
</div>

<style>
.dashboard-notifs {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    margin-bottom: 30px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #f1f5f9;
}

.db-flow-header {
    padding: 20px 20px 15px;
    background: #fff;
    border-bottom: 1px solid #f1f5f9;
}

.db-flow-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.db-flow-title {
    font-size: 18px;
    font-weight: 800;
    color: #111827;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.db-flow-title i {
    color: #6366f1;
    font-size: 16px;
}

.db-flow-periods {
    display: flex;
    background: #f3f4f6;
    padding: 4px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}

.flow-p-btn {
    font-size: 12px;
    font-weight: 700;
    padding: 6px 16px;
    border: none;
    background: transparent;
    color: #4b5563;
    border-radius: 7px;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.flow-p-btn.active {
    background: #fff;
    color: #111827;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.db-flow-tabs-wrapper {
    overflow-x: auto;
    padding-bottom: 5px;
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.db-flow-tabs-wrapper::-webkit-scrollbar {
    display: none;
}

.db-flow-tabs {
    display: flex;
    gap: 8px;
    min-width: max-content;
}

.flow-tab {
    font-size: 12px;
    font-weight: 600;
    padding: 7px 15px;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #4b5563;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.flow-tab:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.flow-tab.active {
    background: #111827;
    color: #fff;
    border-color: #111827;
}

.notif-scroll-area {
    max-height: 500px;
    overflow-y: auto;
    padding: 0;
    background: #fafafa;
}

/* Custom Scrollbar */
.notif-scroll-area::-webkit-scrollbar {
    width: 4px;
}
.notif-scroll-area::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

.notif-loading-state, .notif-empty-state {
    padding: 80px 20px;
    text-align: center;
    color: #94a3b8;
}

.db-loader {
    width: 30px;
    height: 30px;
    border: 3px solid #f1f5f9;
    border-top: 3px solid #0ea5e9;
    border-radius: 50%;
    margin: 0 auto 15px;
    animation: db-spin 0.8s linear infinite;
}

@keyframes db-spin {
    to { transform: rotate(360deg); }
}

.db-notif-item {
    display: flex;
    gap: 16px;
    padding: 16px 20px;
    border-bottom: 1px solid #f8fafc;
    transition: all 0.2s;
    cursor: pointer;
    text-decoration: none !important;
    position: relative;
    background: #fff;
}

.db-notif-item:hover {
    background: #fafbfc;
}

.db-notif-item.is-unread {
    background: rgba(14, 165, 233, 0.03);
}

.db-notif-item::before {
    content: '';
    position: absolute;
    left: 41px;
    top: 60px;
    bottom: -16px;
    width: 2px;
    background: #f1f5f9;
    z-index: 1;
}

.db-notif-item:last-child::before {
    display: none;
}

.db-notif-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    z-index: 2;
    background: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
}

.db-notif-info {
    flex: 1;
    min-width: 0;
}

.db-notif-title {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 3px;
}

.is-unread .db-notif-title::after {
    content: 'Yeni';
    font-size: 9px;
    background: #0ea5e9;
    color: #fff;
    padding: 1px 6px;
    border-radius: 10px;
    margin-left: 8px;
    text-transform: uppercase;
}

.db-notif-msg {
    font-size: 13px;
    color: #475569;
    line-height: 1.5;
    margin-bottom: 8px;
}

.db-notif-meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 8px;
}

.db-notif-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 10px;
    background: #f8fafc;
    color: #64748b;
    border-radius: 20px;
    border: 1px solid #edf2f7;
}

.db-notif-time {
    font-size: 11px;
    color: #94a3b8;
    font-weight: 700;
}

.dashboard-panel-footer {
    padding: 15px;
    background: #f8fafc;
    text-align: center;
}

.view-all-link {
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
    text-decoration: none;
    transition: color 0.2s;
}

.view-all-link:hover {
    color: #1e293b;
}

.view-all-link i {
    margin-left: 5px;
}
</style>

<script>
(function() {
    const list = document.getElementById('dbNotifList');
    const tabs = document.querySelectorAll('#dashboardNotifs .flow-tab');
    const periods = document.querySelectorAll('#dashboardNotifs .flow-p-btn');
    let notifications = [];
    let activeTab = 'all';
    let activePeriod = 'weekly';

    function loadNotifs() {
        console.log('Loading notifications for:', activePeriod);
        fetch(`/admin/api/notifications?limit=50&period=${activePeriod}`)
            .then(r => r.json())
            .then(d => {
                console.log('Notifications loaded:', d.notifications.length);
                notifications = d.notifications;
                renderNotifs();
            })
            .catch((err) => {
                console.error('Fetch error:', err);
                list.innerHTML = '<div class="notif-empty-state">Bağlantı hatası oluştu.</div>';
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

    function getColor(color) {
        const colors = {
            'green': '#dcfce7',
            'blue': '#e0f2fe',
            'orange': '#fff7ed',
            'red': '#fef2f2',
            'purple': '#f5f3ff',
        };
        return colors[color] || '#f8fafc';
    }

    function renderNotifs() {
        const filtered = activeTab === 'all' 
            ? notifications 
            : notifications.filter(n => n.type === activeTab || (activeTab === 'new_order' && n.type === 'cart_recovered'));

        if (filtered.length === 0) {
            list.innerHTML = `<div class="notif-empty-state">Bu kategoride akış bulunmuyor.</div>`;
            return;
        }

        list.innerHTML = filtered.map(n => {
            const data = n.data || {};
            let metaHtml = '';
            
            if (n.type === 'new_order' || n.type === 'cart_recovered') {
                if (data.customer_name) metaHtml += `<span class="db-notif-badge">${data.customer_name}</span>`;
                if (data.payment_method) metaHtml += `<span class="db-notif-badge">${data.payment_method}</span>`;
            } else if (n.type === 'new_customer') {
                if (data.customer_name) metaHtml += `<span class="db-notif-badge">${data.customer_name}</span>`;
                if (data.email) metaHtml += `<span class="db-notif-badge">${data.email}</span>`;
            } else if (n.type === 'new_question') {
                if (data.customer_name) metaHtml += `<span class="db-notif-badge">${data.customer_name}</span>`;
                if (data.product_name) metaHtml += `<span class="db-notif-badge">${data.product_name}</span>`;
            } else if (n.type === 'product_review') {
                if (data.customer_name || data.reviewer_name) {
                    metaHtml += `<span class="db-notif-badge">${data.customer_name || data.reviewer_name}</span>`;
                }
                if (data.rating) {
                    metaHtml += `<span class="db-notif-badge">Puan: ⭐ ${data.rating}/5</span>`;
                }
            }

            return `
                <a href="${n.link || '#'}" class="db-notif-item ${n.is_read ? '' : 'is-unread'}">
                    <div class="db-notif-icon" style="background: ${getColor(n.color)}">
                        ${getEmoji(n.type)}
                    </div>
                    <div class="db-notif-info">
                        <div class="db-notif-title">${n.title}</div>
                        <div class="db-notif-msg">${n.message}</div>
                        <div class="db-notif-meta-row">${metaHtml}</div>
                        <div class="db-notif-time">${n.time_ago}</div>
                    </div>
                </a>
            `;
        }).join('');
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            activeTab = this.dataset.tab;
            renderNotifs();
        });
    });

    periods.forEach(p => {
        p.addEventListener('click', function(e) {
            e.preventDefault();
            periods.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            activePeriod = this.dataset.period;
            list.innerHTML = '<div class="notif-loading-state"><div class="db-loader"></div><span>Akış yükleniyor...</span></div>';
            loadNotifs();
        });
    });

    // Refresh every 60 seconds
    setInterval(loadNotifs, 60000);
    loadNotifs();
})();
</script>
