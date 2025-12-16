<div class="order-details-card order-shipping-details">
    <h4>{{ trans('storefront::account.view_order.shipping_address') }}</h4>

    @php($shipping = $order->shippingAddress)
    @if ($shipping)
        <address class="d-flex flex-column cursor-default m-b-0">
            <span>{{ $shipping->first_name }} {{ $shipping->last_name }}</span>
            <span>{{ $shipping->address_line ?? $shipping->address_1 }}</span>
            <span>{{ $shipping->city ?? $shipping->city_id }} / {{ $shipping->state ?? $shipping->district_id }}</span>
            @if ($shipping->phone)
                <span>{{ $shipping->phone }}</span>
            @endif
        </address>
    @endif
</div>
