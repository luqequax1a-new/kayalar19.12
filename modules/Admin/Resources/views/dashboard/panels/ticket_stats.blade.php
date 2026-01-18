<div class="dashboard-panel dashboard-ticket-stats">
    <div class="grid-header clearfix">
        <h5 class="pull-left">Destek Talepleri</h5>
        <div class="pull-right">
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-default btn-sm" style="font-size: 12px; padding: 4px 10px;">Tümünü Gör</a>
        </div>
    </div>

    <div class="ticket-stats-grid">
        <div class="stat-item total">
            <div class="stat-label">Toplam</div>
            <div class="stat-value">{{ $ticketStats['total'] }}</div>
        </div>
        <div class="stat-item waiting">
            <div class="stat-label">Bekleyen</div>
            <div class="stat-value">{{ $ticketStats['waiting'] }}</div>
            @if($ticketStats['waiting'] > 0)
                <div class="stat-indicator pulse"></div>
            @endif
        </div>
        <div class="stat-item open">
            <div class="stat-label">Açık</div>
            <div class="stat-value">{{ $ticketStats['open'] }}</div>
        </div>
        <div class="stat-item closed">
            <div class="stat-label">Kapalı</div>
            <div class="stat-value">{{ $ticketStats['closed'] }}</div>
        </div>
    </div>

    <div class="ticket-categories-list">
        <h6 class="list-title">Kategori Dağılımı</h6>
        @forelse($ticketStats['categories'] as $category)
            <div class="category-item">
                <div class="category-info">
                    <span class="name">{{ $category['name'] }}</span>
                    <span class="count">{{ $category['count'] }} talep</span>
                </div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width: {{ $category['percentage'] }}%"></div>
                </div>
            </div>
        @empty
            <div class="empty-message">Henüz veri yok.</div>
        @endforelse
    </div>
</div>

<style>
    .dashboard-ticket-stats {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 15px rgba(0,0,0,0.04);
        margin-bottom: 20px;
        padding-bottom: 20px;
    }
    .ticket-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        padding: 0 20px 20px;
        border-bottom: 1px solid #f1f1f1;
    }
    .stat-item {
        background: #f9fafb;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        position: relative;
    }
    .stat-item.total { border-left: 3px solid #6366f1; }
    .stat-item.waiting { border-left: 3px solid #f59e0b; background: #fffbeb; }
    .stat-item.open { border-left: 3px solid #10b981; }
    .stat-item.closed { border-left: 3px solid #9ca3af; }
    
    .stat-label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 5px;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        line-height: 1;
    }
    
    .stat-indicator.pulse {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 8px;
        height: 8px;
        background: #f59e0b;
        border-radius: 50%;
        box-shadow: 0 0 0 rgba(245, 158, 11, 0.4);
        animation: pulse-orange 2s infinite;
    }
    
    @keyframes pulse-orange {
        0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
        100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
    }

    .ticket-categories-list {
        padding: 20px 20px 0;
    }
    .list-title {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 15px;
    }
    .category-item {
        margin-bottom: 12px;
    }
    .category-item:last-child { margin-bottom: 0; }
    .category-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 4px;
        font-size: 13px;
    }
    .category-info .name { color: #4b5563; font-weight: 500; }
    .category-info .count { color: #9ca3af; font-size: 12px; }
    
    .progress-bar-bg {
        height: 6px;
        background: #f3f4f6;
        border-radius: 3px;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%;
        background: #6366f1;
        border-radius: 3px;
    }
    .empty-message {
        text-align: center;
        color: #9ca3af;
        font-size: 13px;
        padding: 10px 0;
    }
</style>
