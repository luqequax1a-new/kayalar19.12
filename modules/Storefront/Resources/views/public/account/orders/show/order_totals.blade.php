<ul class="order-summary-list list-unstyled m-b-0">
    <li>
        <span>{{ trans('storefront::account.view_order.subtotal') }}:</span>
        <span>{{ $order->sub_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
    </li>

    @if ($order->hasShippingMethod())
        <li>
            <span>{{ trans('storefront::account.view_order.shipping_cost') }}:</span>
            <span>{{ $order->shipping_cost->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
        </li>
    @endif

    @if ($order->hasTax())
        <li>
            <span>{{ trans('storefront::account.view_order.tax') }}:</span>
            <span>{{ $order->tax->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
        </li>
    @endif

    @if ($order->hasCoupon())
        <li>
            <span>{{ trans('storefront::account.view_order.coupon') }} ({{ $order->coupon_code }}):</span>
            <span>-{{ $order->discount->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
        </li>
    @endif

    @if ($order->isCodPayment() && $order->cod_fee->amount() > 0)
        <li>
            <span>{{ trans('storefront::account.view_order.cod_fee') }}:</span>
            <span>{{ $order->cod_fee->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
        </li>
    @endif
</ul>

<div class="order-summary-total">
    <label>{{ trans('storefront::account.view_order.total') }}</label>
    <span class="total-price">{{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
</div>
