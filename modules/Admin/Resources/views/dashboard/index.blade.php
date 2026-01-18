@extends('admin::layout')

@section('title', trans('admin::dashboard.dashboard'))

@section('content_header')
    <div class="dashboard-header-wrapper">
        <div class="dashboard-header-left">
            <h3>{{ trans('admin::dashboard.dashboard') }}</h3>
        </div>
        <div class="dashboard-quick-actions">
            @hasAccess('admin.orders.index')
                <a href="{{ route('admin.orders.index') }}" class="btn btn-default btn-actions">
                    <i class="fa fa-shopping-cart"></i> Siparişler
                </a>
            @endHasAccess
            @hasAccess('admin.products.create')
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-actions">
                    <i class="fa fa-plus"></i> Yeni Ürün
                </a>
            @endHasAccess
        </div>
    </div>
@endsection

@section('content')
    <div class="grid clearfix">
        <div class="row dashboard-kpi-row">
            @hasAccess('admin.orders.index')
                @include('admin::dashboard.grids.total_sales')
                @include('admin::dashboard.grids.total_orders')
            @endHasAccess

            @hasAccess('admin.products.index')
                @include('admin::dashboard.grids.total_products')
            @endHasAccess

            @hasAccess('admin.users.index')
                @include('admin::dashboard.grids.total_customers')
            @endHasAccess
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            @hasAccess('admin.orders.index')
                {{-- Satış Analizi (legacy Sales Analytics paneli): Şimdilik gizlendi. Tekrar açmak için aşağıdaki include satırını yorumdan çıkar. --}}
                {{-- @include('admin::dashboard.panels.sales_analytics') --}}
            @endHasAccess

            @hasAccess('admin.orders.index')
                <div class="dashboard-panel dashboard-analytics" data-dashboard-analytics>
                    <div class="grid-header clearfix">
                        <h5 class="text-center">Analytics</h5>

                        <div class="pull-right dashboard-range-selector" data-dashboard-range-selector>
                            <a href="#" class="range" data-range="today">Bugün</a>
                            <a href="#" class="range" data-range="yesterday">Dün</a>
                            <a href="#" class="range" data-range="7">7 gün</a>
                            <a href="#" class="range" data-range="14">14 gün</a>
                            <a href="#" class="range active" data-range="30">1 ay</a>
                            <a href="#" class="range" data-range="all">Tüm zamanlar</a>
                        </div>
                    </div>

                    <div class="dashboard-analytics-kpis" data-dashboard-analytics-kpis>
                        <div class="kpi">
                            <span class="label">Ciro</span>
                            <span class="value" data-kpi-revenue>—</span>
                        </div>
                        <div class="kpi">
                            <span class="label">Sipariş</span>
                            <span class="value" data-kpi-orders>—</span>
                        </div>
                        <div class="kpi">
                            <span class="label">Ortalama Sepet</span>
                            <span class="value" data-kpi-aov>—</span>
                        </div>
                        <div class="kpi">
                            <span class="label">Tekrar Oranı</span>
                            <span class="value" data-kpi-repeat-rate>—</span>
                        </div>
                    </div>

                    <div class="dashboard-analytics-tabs" data-dashboard-analytics-tabs>
                        <a href="#" class="tab active" data-tab="trend">Trend</a>
                        <a href="#" class="tab" data-tab="customers">Müşteriler</a>
                        <a href="#" class="tab" data-tab="traffic"><i class="fa fa-share-alt"></i> Kaynak</a>
                        <a href="#" class="tab" data-tab="categories"><i class="fa fa-folder-open"></i> Kategoriler</a>
                        <a href="#" class="tab" data-tab="brands"><i class="fa fa-tag"></i> Markalar</a>
                        <a href="#" class="tab" data-tab="conversion"><i class="fa fa-shopping-cart"></i> Dönüşüm</a>
                        <a href="#" class="tab" data-tab="live">Canlı</a>
                    </div>

                    <div class="dashboard-analytics-tab-panels">
                        <div class="tab-panel active" data-panel="trend">
                            <div class="canvas">
                                <canvas class="chart" data-chart-trend height="280"></canvas>
                            </div>
                        </div>
                        <div class="tab-panel" data-panel="customers">
                            <div class="canvas">
                                <canvas class="chart" data-chart-customers height="280"></canvas>
                            </div>
                        </div>
                        <div class="tab-panel" data-panel="traffic">
                            <div class="canvas">
                                <canvas class="chart" data-chart-traffic height="280"></canvas>
                            </div>
                            <div class="dashboard-traffic-controls" data-traffic-controls>
                                <a href="#" class="btn btn-default btn-sm active" data-traffic-metric="orders">Sipariş</a>
                                <a href="#" class="btn btn-default btn-sm" data-traffic-metric="revenue">Ciro</a>
                            </div>

                            <div class="table-responsive dashboard-traffic-table">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Satış Kaynağı</th>
                                            <th class="text-right">Sipariş</th>
                                            <th class="text-right">Toplam Ciro</th>
                                        </tr>
                                    </thead>
                                    <tbody data-traffic-body>
                                        <tr>
                                            <td class="empty" colspan="3">{{ trans('admin::dashboard.no_data') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-panel" data-panel="categories">
                            <div class="canvas">
                                <canvas class="chart" data-chart-categories height="280"></canvas>
                            </div>
                        </div>
                        <div class="tab-panel" data-panel="brands">
                            <div class="canvas">
                                <canvas class="chart" data-chart-brands height="280"></canvas>
                            </div>
                        </div>
                        <div class="tab-panel" data-panel="conversion">
                            <div class="canvas conversion-chart-container">
                                <canvas class="chart" data-chart-conversion height="280"></canvas>
                            </div>
                            <div class="conversion-metrics">
                                <div class="metric">
                                    <span class="label">Toplam Ziyaret</span>
                                    <span class="value" data-conversion-visits>—</span>
                                </div>
                                <div class="metric">
                                    <span class="label">Toplam Sipariş</span>
                                    <span class="value" data-conversion-orders>—</span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-panel" data-panel="live">
                            <div class="dashboard-analytics-live" data-instant-tracking>
                                <div class="dashboard-analytics-live__controls" data-instant-tracking-range>
                                    <a href="#" class="range active" data-range="last_30_min">30 dk</a>
                                    <a href="#" class="range" data-range="last_60_min">1 saat</a>
                                    <a href="#" class="range" data-range="today">Bugün</a>
                                </div>

                                <div class="dashboard-analytics-live__cards">
                                    <div class="live-metric">
                                        <span class="label">Ziyaret</span>
                                        <span class="value" data-it-visits-count>—</span>
                                    </div>
                                    <div class="live-metric">
                                        <span class="label">Aktif Sepet</span>
                                        <span class="value" data-it-carts-count>—</span>
                                    </div>
                                    <div class="live-metric">
                                        <span class="label">Sepet Değeri</span>
                                        <span class="value" data-it-carts-amount>—</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @push('globals')
                    <script>
                        FleetCart.data.dashboardAnalyticsUrl = '{{ route('admin.dashboard.analytics.index') }}';
                        FleetCart.data.adminOrdersUrl = '{{ route('admin.orders.index') }}';
                        FleetCart.data.cartActivityUrl = '{{ route('admin.dashboard.cart_activity.index') }}';
                    </script>
                @endpush
            @endHasAccess

            @hasAccess('admin.orders.index')
                @include('admin::dashboard.panels.latest_orders')
            @endHasAccess
        </div>

        <div class="col-md-6">
            @include('admin::dashboard.panels.notifications')

            {{-- Birleşik İçgörüler Widget'ı --}}
            @include('admin::dashboard.panels.insights_hub')

            {{-- Abandoned Cart --}}
            @hasAccess('admin.orders.index')
                @include('admin::dashboard.panels.abandoned_cart_widget')
            @endHasAccess
        </div>
    </div>
@endsection

@push('globals')
    @vite([
        "modules/Admin/Resources/assets/sass/dashboard.scss",
        "modules/Admin/Resources/assets/sass/enhanced_dashboard.scss",
        "modules/Admin/Resources/assets/js/enhanced_dashboard.js",
        "modules/Admin/Resources/assets/js/enhanced_dashboard_part2.js",
        "modules/Admin/Resources/assets/js/instant_tracking.js",
        "modules/Admin/Resources/assets/js/low_stock_widget.js",
        "modules/Admin/Resources/assets/js/insights_hub.js",
    ])
@endpush
