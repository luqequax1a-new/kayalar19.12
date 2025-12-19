<div class="dashboard-panel dashboard-cart-activity" data-dashboard-cart-activity>
    <div class="grid-header clearfix">
        <h5 class="pull-left">Terkedilen Sepetler</h5>
    </div>

    <div class="dashboard-cart-activity-kpis" data-cart-activity-kpis>
        <div class="kpi">
            <div class="kpi-title">Son 30 dk</div>
            <div class="kpi-main"><span data-cart-activity-last-30-count>—</span> sepet</div>
            <div class="kpi-sub" data-cart-activity-last-30-amount>—</div>
        </div>
        <div class="kpi">
            <div class="kpi-title">Son 1 saat</div>
            <div class="kpi-main"><span data-cart-activity-last-60-count>—</span> sepet</div>
            <div class="kpi-sub" data-cart-activity-last-60-amount>—</div>
        </div>
        <div class="kpi">
            <div class="kpi-title">Bugün</div>
            <div class="kpi-main"><span data-cart-activity-today-count>—</span> sepet</div>
            <div class="kpi-sub" data-cart-activity-today-amount>—</div>
        </div>
    </div>

    <div class="dashboard-cart-activity-list" data-cart-activity-list>
        <div class="empty" data-cart-activity-empty>{{ trans('admin::dashboard.no_data') }}</div>
    </div>
</div>
