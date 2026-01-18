<div class="modern-address-block">
    <h5 class="section-subtitle"><i class="las la-truck"></i> {{ trans('storefront::account.view_order.shipping_address') }}</h5>

    @php
        $shipping = $order->shippingAddress;
        $shippingSnapshot = $order->shippingSnapshot;
        $activeShippingData = $shippingSnapshot ?: $shipping;
        $shippingPhone = ($shippingSnapshot->phone ?? null) ?: (($shipping->phone ?? null) ?: ($order->customer_phone ?: '-'));
    @endphp

    @if ($activeShippingData)
        <div class="modern-info-grid">
            <div class="info-item">
                <span class="info-label">Ad-Soyad</span>
                <span class="info-value">{{ ($activeShippingData->first_name ?? '-') }} {{ ($activeShippingData->last_name ?? '') }}</span>
            </div>

            <div class="info-item">
                <span class="info-label">Adres</span>
                <span class="info-value">{{ ($activeShippingData->address_line ?? null) ?: ((($activeShippingData->address_line ?? $activeShippingData->address_1) ?? null) ?: '-') }}</span>
            </div>

            <div class="info-item">
                <span class="info-label">İl / İlçe</span>
                <span class="info-value">
                     {{ ($activeShippingData->city_title ?? $activeShippingData->city ?? '-') }} / 
                     {{ ($activeShippingData->state ?? $activeShippingData->district_title ?? $activeShippingData->district ?? '-') }}
                </span>
            </div>

            @if ($shippingPhone && $shippingPhone !== '-')
                <div class="info-item">
                    <span class="info-label">Telefon</span>
                    <span class="info-value">{{ $shippingPhone }}</span>
                </div>
            @endif
        </div>
    @endif
</div>
