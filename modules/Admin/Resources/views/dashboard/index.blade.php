@extends('admin::layout')

@section('title', trans('admin::dashboard.dashboard'))

@section('content_header')
    <h3 class="pull-left">{{ trans('admin::dashboard.dashboard') }}</h3>
@endsection

@section('content')
    <div class="grid clearfix">
        <div class="row">
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
        <div class="col-md-7">
            @hasAccess('admin.orders.index')
                {{-- Satış Analizi (legacy Sales Analytics paneli): Şimdilik gizlendi. Tekrar açmak için aşağıdaki include satırını yorumdan çıkar. --}}
                {{-- @include('admin::dashboard.panels.sales_analytics') --}}
            @endHasAccess

            @hasAccess('admin.orders.index')
                <div class="dashboard-panel dashboard-analytics" data-dashboard-analytics>
                    <div class="grid-header clearfix">
                        <h5 class="pull-left">Analytics</h5>

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
                        <a href="#" class="tab" data-tab="traffic">Kaynak</a>
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
                                            <th>Kaynak</th>
                                            <th class="text-right">Sipariş</th>
                                            <th class="text-right">Ciro</th>
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
                    </div>
                </div>

                @push('globals')
                    <script>
                        FleetCart.data.dashboardAnalyticsUrl = '{{ route('admin.dashboard.analytics.index') }}';
                        FleetCart.data.adminOrdersUrl = '{{ route('admin.orders.index') }}';
                    </script>
                @endpush
            @endHasAccess

            @hasAccess('admin.orders.index')
                @include('admin::dashboard.panels.latest_orders')
            @endHasAccess
        </div>

        <div class="col-md-5">
            @hasAccess('admin.products.index')
                <div class="dashboard-panel dashboard-top-products">
                    <div class="grid-header clearfix">
                        <h5 class="pull-left">En Çok Satanlar</h5>

                        <div class="pull-right dashboard-top-products-limit" data-top-products-limit>
                            <a href="#" class="range" data-limit="5">5</a>
                            <a href="#" class="range active" data-limit="10">10</a>
                            <a href="#" class="range" data-limit="15">15</a>
                            <a href="#" class="range" data-limit="20">20</a>
                        </div>
                    </div>

                    <div class="dashboard-top-products-scroll">
                        <div class="table-responsive dashboard-top-products-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Ürün</th>
                                        <th class="text-right">Adet</th>
                                        <th class="text-right">Ciro</th>
                                    </tr>
                                </thead>
                                <tbody data-top-products-body data-image-placeholder-url="{{ asset('build/assets/image-placeholder.png') }}">
                                    <tr>
                                        <td class="empty" colspan="3">{{ trans('admin::dashboard.no_data') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endHasAccess

            @hasAccess('admin.orders.index')
                {{-- Terkedilen Sepetler paneli: şimdilik devre dışı. Açmak için bu satırı yorumdan çıkar. --}}
                {{-- @include('admin::dashboard.panels.cart_activity') --}}
            @endHasAccess

            @hasAccess('admin.users.index')
                @include('admin::dashboard.panels.top_customers')
            @endHasAccess

            @include('admin::dashboard.panels.latest_searches')

            @hasAccess('admin.reviews.index')
                @include('admin::dashboard.panels.latest_reviews')
            @endHasAccess
        </div>
    </div>
@endsection

@push('globals')
    @vite([
        "modules/Admin/Resources/assets/sass/dashboard.scss",
        "modules/Admin/Resources/assets/js/dashboard.js",
    ])
@endpush
