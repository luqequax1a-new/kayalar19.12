<div class="order-details-card order-billing-details">
    <h4>{{ trans('storefront::account.view_order.billing_address') }}</h4>

    @php($billing = $order->billingAddress)
    @if ($billing)
        <address class="d-flex flex-column cursor-default m-b-0">
            @if ($billing->company_name)
                <span>{{ $billing->company_name }}</span>
            @endif
            @if ($billing->tax_number)
                <span>Vergi No: {{ $billing->tax_number }}</span>
            @endif
            @if ($billing->tax_office)
                <span>Vergi Dairesi: {{ $billing->tax_office }}</span>
            @endif
            @if ($billing->billing_email)
                <span>{{ $billing->billing_email }}</span>
            @endif
            <span>{{ $billing->address_line ?? $billing->address_1 }}</span>
            <span>{{ $billing->city ?? $billing->city_id }} / {{ $billing->state ?? $billing->district_id }}</span>
            @if ($billing->phone)
                <span>{{ $billing->phone }}</span>
            @endif
        </address>
    @endif
</div>
