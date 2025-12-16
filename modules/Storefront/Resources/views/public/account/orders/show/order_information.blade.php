<div class="order-details-card order-information">
    <h4>{{ trans('storefront::account.view_order.order_information') }}</h4>

    <ul class="order-information-list list-unstyled m-b-0 cursor-default">
        <li>
            <label>{{ rtrim(trans('storefront::account.view_order.id'), ':') }}:</label>
            <span>#{{ $order->displayOrderNumber() }}</span>
        </li>
        <li>
            <label>{{ rtrim(trans('storefront::account.view_order.date'), ':') }}:</label>
            <span>{{ $order->created_at->format('d.m.Y') }}</span>
        </li>
        <li>
            <label>{{ rtrim(trans('storefront::account.view_order.phone'), ':') }}:</label>
            <span>{{ $order->customer_phone }}</span>
        </li>
        <li>
            <label>{{ rtrim(trans('storefront::account.view_order.email'), ':') }}:</label>
            <span>{{ $order->customer_email }}</span>
        </li>
        <li>
            <label>{{ rtrim(trans('storefront::account.view_order.shipping_method'), ':') }}:</label>
            <span>{{ $order->shipping_method }}</span>
        </li>
        <li>
            <label>{{ rtrim(trans('storefront::account.view_order.payment_method'), ':') }}:</label>
            <span>{{ $order->payment_method }}</span>
        </li>

        @if ($order->payment_method === 'Bank Transfer')
            <li>
                <label></label>
                <span>{!! setting('bank_transfer_instructions') !!}</span>
            </li>
        @endif

        @if ($order->note)
            <li>
                <label>{{ rtrim(trans('storefront::account.view_order.order_note'), ':') }}:</label>
                <span>{{ $order->note }}</span>
            </li>
        @endif

        @if ($order->shipping_carrier_name)
            <li>
                <label>{{ rtrim(trans('storefront::account.view_order.carrier_name'), ':') }}:</label>
                <span>{{ $order->shipping_carrier_name }}</span>
            </li>
        @endif

        @if ($order->shipping_tracking_number)
            <li>
                <label>{{ rtrim(trans('storefront::account.view_order.tracking_number'), ':') }}:</label>
                <span>{{ $order->shipping_tracking_number }}</span>
            </li>
        @endif

        @if ($order->tracking_reference)
            <li x-data="{ tracking: '{{ $order->tracking_reference }}' }">
                <label>{{ rtrim(trans('storefront::account.view_order.tracking_reference'), ':') }}:</label>

                <span class="d-flex align-items-center flex-wrap">
                    <span class="m-r-5" x-text="tracking.length > 30 ? tracking.slice(0, 30) + '...' : tracking"></span>

                    <span class="d-inline-flex align-items-center">
                        <button type="button" @click="navigator.clipboard.writeText(tracking).then(() => { notify(@js(trans('storefront::account.view_order.copied_to_clipboard'))); });" class="btn-track-order m-r-5" title="{{ trans('storefront::account.view_order.copy') }}">
                            <i class="lar la-copy"></i>
                        </button>

                        @if (filter_var($order->tracking_reference, FILTER_VALIDATE_URL))
                            <a href="{{ $order->tracking_reference }}" class="btn-track-order" target="_blank" title="{{ trans('storefront::account.view_order.open_link') }}"><i class="las la-external-link-alt"></i></a>
                        @endif
                    </span>
                </span>
            </li>
        @endif
    </ul>
</div>
