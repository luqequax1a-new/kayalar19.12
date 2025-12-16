@extends('storefront::public.account.layout')

@section('title', trans('storefront::account.view_order.view_order'))

@section('account_breadcrumb')
    <li><a href="{{ route('account.orders.index') }}">{{ trans('storefront::account.pages.my_orders') }}</a></li>
    <li class="active">{{ trans('storefront::account.orders.view_order') }}</li>
@endsection

@section('panel')
    <div class="panel order-details-panel">
        <div class="panel-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap order-details-header">
                <div class="d-flex flex-column">
                    <h4 class="m-b-0">{{ trans('storefront::account.view_order.view_order') }}</h4>
                    <div class="order-details-subtitle">
                        <span class="order-no">#{{ $order->displayOrderNumber() }}</span>
                        <span class="order-date">{{ $order->created_at->format('d.m.Y') }}</span>
                        <span class="order-status badge-status badge-status-{{ \Illuminate\Support\Str::slug($order->status) }}">{{ $order->status() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-body">
            <div class="order-details-wrap">
                <div class="order-details-layout">
                    <div class="order-details-top-full">
                        <div class="order-details-top">
                            <div class="order-details-info-grid">
                                @include('storefront::public.account.orders.show.order_information')
                                @include('storefront::public.account.orders.show.shipping_address')
                                @include('storefront::public.account.orders.show.billing_address')
                            </div>
                        </div>
                    </div>

                    <div class="order-details-main">
                        @include('storefront::public.account.orders.show.items_ordered')
                    </div>

                    <aside class="order-details-sidebar">
                        @include('storefront::public.account.orders.show.order_totals')
                    </aside>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/account/orders/show/main.scss',
    ])
@endpush
