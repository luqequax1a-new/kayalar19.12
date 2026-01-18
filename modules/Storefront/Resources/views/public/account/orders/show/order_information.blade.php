<div class="modern-order-data">
    <h5 class="section-subtitle"><i class="las la-info-circle"></i> {{ trans('storefront::account.view_order.order_information') }}</h5>

    <div class="modern-info-grid">
        <div class="info-item">
            <span class="info-label">Sipariş Durumu</span>
            <span class="info-value">
                <span class="order-status-badge badge-{{ \Illuminate\Support\Str::slug($order->status) }}">
                    {{ $order->status() }}
                </span>
            </span>
        </div>

        <div class="info-item">
            <span class="info-label">{{ trans('storefront::account.view_order.id') }}</span>
            <span class="info-value">#{{ $order->displayOrderNumber() }}</span>
        </div>

        <div class="info-item">
            <span class="info-label">{{ trans('storefront::account.view_order.date') }}</span>
            <span class="info-value">{{ $order->created_at->format('d.m.Y H:i') }}</span>
        </div>

        <div class="info-item">
            <span class="info-label">{{ trans('storefront::account.view_order.phone') }}</span>
            <span class="info-value">{{ $order->customer_phone }}</span>
        </div>

        <div class="info-item">
            <span class="info-label">{{ trans('storefront::account.view_order.email') }}</span>
            <span class="info-value">{{ $order->customer_email }}</span>
        </div>

        <div class="info-item">
            <span class="info-label">{{ trans('storefront::account.view_order.shipping_method') }}</span>
            <span class="info-value">{{ $order->shipping_method }}</span>
        </div>

        <div class="info-item">
            <span class="info-label">{{ trans('storefront::account.view_order.payment_method') }}</span>
            <span class="info-value">{{ $order->payment_method }}</span>
        </div>

        @if ($order->payment_method === 'Bank Transfer')
            <div class="info-item">
                <span class="info-label"></span>
                <span class="info-value">{!! setting('bank_transfer_instructions') !!}</span>
            </div>
        @endif

        @if ($order->note)
            <div class="info-item">
                <span class="info-label">{{ trans('storefront::account.view_order.order_note') }}</span>
                <span class="info-value">{{ $order->note }}</span>
            </div>
        @endif

        @if ($order->shipping_carrier_name)
            <div class="info-item">
                <span class="info-label">{{ trans('storefront::account.view_order.carrier_name') }}</span>
                <span class="info-value">{{ $order->shipping_carrier_name }}</span>
            </div>
        @endif

        @if ($order->shipping_tracking_number)
            <div class="info-item">
                <span class="info-label">{{ trans('storefront::account.view_order.tracking_number') }}</span>
                <span class="info-value">
                    {{ $order->shipping_tracking_number }}
                    @if ($order->tracking_reference && filter_var($order->tracking_reference, FILTER_VALIDATE_URL))
                        <a href="{{ $order->tracking_reference }}" class="tracking-link-icon" target="_blank" title="{{ trans('storefront::account.view_order.open_link') }}">
                            <i class="las la-external-link-alt"></i>
                        </a>
                    @endif
                </span>
            </div>
        @endif
    </div>
</div>



