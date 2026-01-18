{{-- Modern Abandoned Cart Widget --}}
<div class="abandoned-cart-widget">
    <div class="widget-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fa fa-shopping-cart"></i>
            </div>
            <div class="header-text">
                <h4>Terk Edilmiş Sepetler</h4>
                <p>Son {{ $abandonedCartStats['days'] }} günlük özet</p>
            </div>
        </div>
        <a href="{{ route('admin.abandoned_carts.index') }}" class="view-all-link">
            Tümünü Gör <i class="fa fa-arrow-right"></i>
        </a>
    </div>

    <div class="widget-body">
        <div class="stats-grid">
            <div class="stat-item stat-abandoned">
                <div class="stat-header">
                    <span class="stat-icon">
                        <i class="fa fa-shopping-cart"></i>
                    </span>
                    <span class="stat-label">Terk Edildi</span>
                </div>
                <div class="stat-value">{{ number_format($abandonedCartStats['total_abandoned']) }}</div>
            </div>

            <div class="stat-item stat-recovered">
                <div class="stat-header">
                    <span class="stat-icon">
                        <i class="fa fa-check-circle"></i>
                    </span>
                    <span class="stat-label">Kurtarıldı</span>
                </div>
                <div class="stat-value">{{ number_format($abandonedCartStats['total_recovered']) }}</div>
            </div>

            <div class="stat-item stat-rate">
                <div class="stat-header">
                    <span class="stat-icon">
                        <i class="fa fa-line-chart"></i>
                    </span>
                    <span class="stat-label">Başarı Oranı</span>
                </div>
                <div class="stat-value">%{{ $abandonedCartStats['recovery_rate'] }}</div>
            </div>

            <div class="stat-item stat-revenue">
                <div class="stat-header">
                    <span class="stat-icon">
                        <i class="fa fa-money"></i>
                    </span>
                    <span class="stat-label">Kurtarılan Ciro</span>
                </div>
                <div class="stat-value">{{ \Modules\Support\Money::inDefaultCurrency($abandonedCartStats['recovered_revenue'])->format() }}</div>
            </div>
        </div>
    </div>
</div>

<style>
.abandoned-cart-widget {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 20px;
    border: 1px solid #e5e7eb;
}

.widget-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 15px;
}

.header-icon {
    width: 48px;
    height: 48px;
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #fff;
}

.header-text h4 {
    margin: 0;
    color: #fff;
    font-size: 18px;
    font-weight: 700;
}

.header-text p {
    margin: 4px 0 0 0;
    color: rgba(255,255,255,0.9);
    font-size: 13px;
}

.view-all-link {
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    padding: 8px 16px;
    background: rgba(255,255,255,0.15);
    border-radius: 6px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.view-all-link:hover {
    background: rgba(255,255,255,0.25);
    color: #fff;
    text-decoration: none;
}

.widget-body {
    padding: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.stat-item {
    background: #f9fafb;
    border-radius: 10px;
    padding: 16px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.stat-item:hover {
    border-color: var(--stat-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.stat-abandoned {
    --stat-color: #f59e0b;
}

.stat-recovered {
    --stat-color: #10b981;
}

.stat-rate {
    --stat-color: #3b82f6;
}

.stat-revenue {
    --stat-color: #8b5cf6;
}

.stat-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}

.stat-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    background: var(--stat-color);
    color: #fff;
}

.stat-label {
    font-size: 12px;
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 28px;
    font-weight: 800;
    color: #111827;
    line-height: 1;
}

.stat-revenue .stat-value {
    font-size: 22px;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .widget-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
}
</style>
