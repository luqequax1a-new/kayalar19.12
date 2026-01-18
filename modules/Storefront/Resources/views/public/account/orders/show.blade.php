@extends('storefront::public.account.layout')

@section('title', trans('storefront::account.view_order.view_order'))

@section('account_breadcrumb')
    <li><a href="{{ route('account.orders.index') }}">{{ trans('storefront::account.pages.my_orders') }}</a></li>
    <li class="active">{{ trans('storefront::account.orders.view_order') }}</li>
@endsection

@section('panel')
    @php
        $statusSlug = \Illuminate\Support\Str::slug($order->status);
        $steps = [
            'pending_payment' => 1,
            'pending' => 1,
            'processing' => 2,
            'on_hold' => 2,
            'shipped' => 3,
            'on_the_way' => 3,
            'out_for_delivery' => 3,
            'completed' => 4,
            'canceled' => 0,
            'refunded' => 0,
        ];
        $currentStep = $steps[$statusSlug] ?? 1;
    @endphp

    <div class="back-to-account-wrapper">
        <a href="{{ route('account.dashboard.index') }}" class="btn-back-to-account">
            <i class="las la-arrow-left"></i>
            <span>Hesabıma Geri Dön</span>
        </a>
    </div>

    <div class="order-details-container">
        <div class="order-content-full">
            <!-- Info Section -->
            <div class="order-info-grid-unified">
                <div class="info-block">
                    @include('storefront::public.account.orders.show.order_information')
                </div>
                <div class="info-block">
                    @include('storefront::public.account.orders.show.shipping_address')
                </div>
                <div class="info-block">
                    @include('storefront::public.account.orders.show.billing_address')
                </div>
            </div>

            <!-- Products Section -->
            <div class="order-section-item">
                <div class="order-products-unified">
                    @include('storefront::public.account.orders.show.items_ordered')
                </div>
            </div>

            <!-- Totals Section -->
            <div class="order-section-item">
                <div class="order-totals-unified-full">
                    <h5 class="section-subtitle">{{ trans('storefront::account.view_order.order_totals') }}</h5>
                    @include('storefront::public.account.orders.show.order_totals')
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

