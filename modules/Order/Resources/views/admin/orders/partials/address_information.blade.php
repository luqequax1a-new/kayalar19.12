<div class="address-information-wrapper">
    <h4 class="section-title">{{ trans('order::orders.address_information') }}</h4>

    <div class="row">
        <div class="col-md-6">
            <div class="shipping-address">
                <h5 class="pull-left">{{ trans('order::orders.shipping_address') }}</h5>

                @php($billing = $order->billingAddress)
                @php($shipping = $order->shippingAddress)
                @php($shippingSnapshot = $order->shippingSnapshot)
                @php($billingSnapshot = $order->billingSnapshot)
                @if ($shippingSnapshot || $shipping)
                    <span>
                        {{ ($shippingSnapshot->first_name ?? null) ?: ($shipping->first_name ?? '-') }} {{ ($shippingSnapshot->last_name ?? null) ?: ($shipping->last_name ?? '') }}<br>
                        Telefon: {{ ($shippingSnapshot->phone ?? null) ?: (($shipping->phone ?? null) ?: ($order->customer_phone ?: '-')) }}<br>
                        Adres: {{ ($shippingSnapshot->address_line ?? null) ?: ((($shipping->address_line ?? $shipping->address_1) ?? null) ?: '-') }}<br>
                        {{ ($shippingSnapshot->district ?? null) ?: (($shipping->district_title ?? $shipping->state ?? $shipping->district_id) ?? '-') }} / {{ ($shippingSnapshot->city ?? null) ?: (($shipping->city_title ?? $shipping->city ?? $shipping->city_id) ?? '-') }}
                    </span>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <div class="billing-address">
                <h5 class="pull-left">{{ trans('order::orders.billing_address') }}</h5>

                @if (($billingSnapshot || $billing) && $order->shipping_address_id !== $order->billing_address_id)
                    <span>
                        Firma Adı: {{ $billingSnapshot->company_name ?? (($billing->invoice_title ?? null) ?: (($billing->company_name ?? null) ?: '-')) }}<br>
                        Vergi No: {{ $billingSnapshot->tax_number ?? (($billing->invoice_tax_number ?? null) ?: (($billing->tax_number ?? null) ?: '-')) }}<br>
                        Vergi Dairesi: {{ $billingSnapshot->tax_office ?? (($billing->invoice_tax_office ?? null) ?: (($billing->tax_office ?? null) ?: '-')) }}<br>
                        Email: {{ $billingSnapshot->billing_email ?? (($billing->billing_email ?? null) ?: ($order->customer_email ?: '-')) }}<br>
                        Telefon: {{ $billingSnapshot->phone ?? (($billing->phone ?? null) ?: ($order->customer_phone ?: '-')) }}<br>
                        Adres: {{ $billingSnapshot->address_line ?? ((($billing->address_line ?? $billing->address_1) ?? null) ?: '-') }}<br>
                        {{ $billingSnapshot->district ?? (($billing->district_title ?? $billing->state ?? $billing->district_id) ?? '-') }} / {{ $billingSnapshot->city ?? (($billing->city_title ?? $billing->city ?? $billing->city_id) ?? '-') }}
                    </span>
                @elseif ($shippingSnapshot || $shipping)
                    <span>
                        Firma Adı: {{ ($billingSnapshot->company_name ?? null) ?: '-' }}<br>
                        Vergi No: {{ ($billingSnapshot->tax_number ?? null) ?: '-' }}<br>
                        Vergi Dairesi: {{ ($billingSnapshot->tax_office ?? null) ?: '-' }}<br>
                        Email: {{ ($billingSnapshot->billing_email ?? null) ?: ($order->customer_email ?: '-') }}<br>
                        {{ ($billingSnapshot->first_name ?? null) ?: ($shippingSnapshot->first_name ?? ($shipping->first_name ?? '-')) }} {{ ($billingSnapshot->last_name ?? null) ?: ($shippingSnapshot->last_name ?? ($shipping->last_name ?? '')) }}<br>
                        Telefon: {{ ($billingSnapshot->phone ?? null) ?: (($shippingSnapshot->phone ?? null) ?: (($shipping->phone ?? null) ?: ($order->customer_phone ?: '-'))) }}<br>
                        Adres: {{ ($billingSnapshot->address_line ?? null) ?: (($shippingSnapshot->address_line ?? null) ?: ((($shipping->address_line ?? $shipping->address_1) ?? null) ?: '-')) }}<br>
                        {{ ($billingSnapshot->district ?? null) ?: (($shippingSnapshot->district ?? null) ?: (($shipping->district_title ?? $shipping->state ?? $shipping->district_id) ?? '-')) }} / {{ ($billingSnapshot->city ?? null) ?: (($shippingSnapshot->city ?? null) ?: (($shipping->city_title ?? $shipping->city ?? $shipping->city_id) ?? '-')) }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
