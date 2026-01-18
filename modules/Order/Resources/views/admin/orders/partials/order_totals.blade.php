@php
    $codFee = $order->cod_fee;
    $codFeeForOrder = null;

    if ($codFee && !$codFee->isZero()) {
        if ($order->currency !== setting('default_currency')) {
            $codFeeForOrder = $codFee->convert($order->currency, $order->currency_rate);
        } else {
            $codFeeForOrder = $codFee;
        }
    }
@endphp

<div class="order-totals-wrapper">
    <div class="order-totals">
        <div class="table-responsive">
            <table class="table order-totals-table">
                    <tbody>
                        <tr>
                            <td>{{ trans('order::orders.subtotal') }}</td>
                            <td class="text-right">{{ $order->sub_total->format() }}</td>
                        </tr>

                        @if ($order->hasShippingMethod())
                            <tr>
                                <td>{{ $order->shipping_method }}</td>
                                <td class="text-right">
                                    @if ($order->shipping_cost->amount() == 0)
                                        {{ trans('storefront::checkout.free') }}
                                    @else
                                        {{ $order->shipping_cost->format() }}
                                    @endif
                                </td>
                            </tr>
                        @endif

                        @if ($codFeeForOrder)
                            <tr>
                                <td>{{ trans('storefront::checkout.cod_fee') }}</td>
                                <td class="text-right">{{ $codFeeForOrder->format($order->currency) }}</td>
                            </tr>
                        @endif

                        @foreach ($order->taxes as $tax)
                            <tr>
                                <td>{{ $tax->name }}</td>
                                <td class="text-right">{{ $tax->order_tax->amount->format() }}</td>
                            </tr>
                        @endforeach

                        @if ($order->coupon_code)
                            <tr>
                                <td>{{ trans('order::orders.coupon') }} (<span class="coupon-code">{{ $order->coupon_code }}</span>)</td>
                                <td class="text-right">&#8211;{{ $order->discount->format() }}</td>
                            </tr>
                        @endif

                        <tr>
                            <td>{{ trans('order::orders.total') }}</td>
                            <td class="text-right">{{ $order->total->format() }}</td>
                        </tr>
                    </tbody>
                </table>
        </div>
    </div>
</div>
