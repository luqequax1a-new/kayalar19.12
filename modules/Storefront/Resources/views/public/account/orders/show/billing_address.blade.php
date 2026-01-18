<div class="modern-address-block">
    <h5 class="section-subtitle"><i class="las la-file-invoice"></i> {{ trans('storefront::account.view_order.billing_address') }}</h5>

    @php
        $billing = $order->billingAddress;
        $shipping = $order->shippingAddress;
        $shippingSnapshot = $order->shippingSnapshot;
        $billingSnapshot = $order->billingSnapshot;

        $isBillingDifferent = ($order->shipping_address_id !== $order->billing_address_id);
        
        $activeShippingData = $shippingSnapshot ?: $shipping;
        $activeBillingData = $isBillingDifferent ? ($billingSnapshot ?: $billing) : $activeShippingData;

        $shippingPhone = ($shippingSnapshot->phone ?? null) ?: (($shipping->phone ?? null) ?: ($order->customer_phone ?: '-'));
        $billingPhone = $isBillingDifferent ? (($billingSnapshot->phone ?? null) ?: (($billing->phone ?? null) ?: ($order->customer_phone ?: '-'))) : $shippingPhone;

        $companyName = $billingSnapshot->company_name ?? (($billing->invoice_title ?? null) ?: (($billing->company_name ?? null) ?: ''));
        $taxNumber = $billingSnapshot->tax_number ?? (($billing->invoice_tax_number ?? null) ?: (($billing->tax_number ?? null) ?: ''));
        $taxOffice = $billingSnapshot->tax_office ?? (($billing->invoice_tax_office ?? null) ?: (($billing->tax_office ?? null) ?: ''));
        $billingEmail = $billingSnapshot->billing_email ?? (($billing->billing_email ?? null) ?: ($order->customer_email ?: ''));
    @endphp

    @if ($activeBillingData)
        <div class="modern-info-grid">
            @if ($companyName)
                <div class="info-item">
                    <span class="info-label">Firma Adı</span>
                    <span class="info-value">{{ $companyName }}</span>
                </div>
                @if ($taxOffice)
                    <div class="info-item">
                        <span class="info-label">Vergi Dairesi</span>
                        <span class="info-value">{{ $taxOffice }}</span>
                    </div>
                @endif
                @if ($taxNumber)
                    <div class="info-item">
                        <span class="info-label">Vergi No</span>
                        <span class="info-value">{{ $taxNumber }}</span>
                    </div>
                @endif
            @else
                <div class="info-item">
                    <span class="info-label">Ad-Soyad</span>
                    <span class="info-value">{{ ($activeBillingData->first_name ?? '-') }} {{ ($activeBillingData->last_name ?? '') }}</span>
                </div>
            @endif

            <div class="info-item">
                <span class="info-label">Adres</span>
                <span class="info-value">{{ ($activeBillingData->address_line ?? null) ?: ((($activeBillingData->address_line ?? $activeBillingData->address_1) ?? null) ?: '-') }}</span>
            </div>

            <div class="info-item">
                <span class="info-label">İl / İlçe</span>
                <span class="info-value">
                    {{ ($activeBillingData->city_title ?? $activeBillingData->city ?? '-') }} / 
                    {{ ($activeBillingData->state ?? $activeBillingData->district_title ?? $activeBillingData->district ?? '-') }}
                </span>
            </div>
            
            @if ($billingPhone && $billingPhone !== '-')
                <div class="info-item">
                    <span class="info-label">Telefon</span>
                    <span class="info-value">{{ $billingPhone }}</span>
                </div>
            @endif

            @if ($billingEmail)
                <div class="info-item">
                    <span class="info-label">E-posta</span>
                    <span class="info-value">{{ $billingEmail }}</span>
                </div>
            @endif
        </div>
    @endif
</div>
