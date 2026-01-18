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

<div class="address-premium-wrapper">
    <div class="row">
        {{-- Sevkiyat Adresi Kartı --}}
        <div class="col-md-6">
            <div class="address-card-modern">
                <div class="card-head-addr">
                    <div class="c-title" style="padding: 10px 0;">
                        <h5 style="margin: 0; font-weight: 700; color: #1e293b; font-size: 15px;">{{ trans('order::orders.shipping_address') }}</h5>
                    </div>
                </div>

                <div class="card-body-addr" style="border-top: 1px solid #f1f5f9; padding-top: 15px;">
                    <div class="address-details-clean">
                        {{-- Müşteri Adı --}}
                        <div class="clean-addr-item">
                            <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7c0 2.209-1.791 4-4 4s-4-1.791-4-4 1.791-4 4-4 4 1.791 4 4z"></path><path d="M12 14c-3.866 0-7 3.134-7 7h14c0-3.866-3.134-7-7-7z"></path></svg>
                            <span class="addr-val-main"><small class="addr-label">Ad Soyad:</small> {{ ($activeShippingData->first_name ?? null) ?: ($activeShippingData->first_name ?? '-') }} {{ ($activeShippingData->last_name ?? null) ?: ($activeShippingData->last_name ?? '') }}</span>
                        </div>

                        {{-- Adres --}}
                        <div class="clean-addr-item">
                            <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0L6.343 16.657a8 8 0 1111.314 0z"></path><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span class="addr-val-sub"><small class="addr-label">Adres:</small> {{ ($activeShippingData->address_line ?? null) ?: ((($activeShippingData->address_line ?? $activeShippingData->address_1) ?? null) ?: '-') }}</span>
                        </div>

                        {{-- İl / İlçe --}}
                        <div class="clean-addr-item">
                            <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V7.618a1 1 0 011.447-.894L9 9m0 11l6-3m-6 3V9m6 8l4.553 2.276A1 1 0 0021 18.382V9.618a1 1 0 00-.553-.894L15 6m0 11V6m0 0L9 9"></path></svg>
                            <span class="addr-val-text"><small class="addr-label">Bölge:</small> {{ ($activeShippingData->city ?? null) ?: (($activeShippingData->city_title ?? $activeShippingData->city ?? $activeShippingData->city_id) ?? '-') }} • {{ ($activeShippingData->district ?? null) ?: (($activeShippingData->district_title ?? $activeShippingData->state ?? $activeShippingData->district_id) ?? '-') }} • Türkiye</span>
                        </div>

                        {{-- Telefon --}}
                        <div class="clean-addr-item">
                            <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 5C3 3.89543 3.89543 3 5 3H8.27924C8.70967 3 9.09181 3.27543 9.22792 3.68377L10.7257 8.17721C10.8831 8.64932 10.6694 9.16531 10.2243 9.38787L7.96701 10.5165C9.06925 12.9612 11.0388 14.9308 13.4835 16.033L14.6121 13.7757C14.8347 13.3306 15.3507 13.1169 15.8228 13.2743L20.3162 14.7721C20.7246 14.9082 21 15.2903 21 15.7208V19C21 20.1046 20.1046 21 19 21H18C9.71573 21 3 14.2843 3 6V5Z"></path></svg>
                            <span class="addr-val-text"><small class="addr-label">Telefon:</small> {{ $shippingPhone }}</span>
                        </div>

                        {{-- Email --}}
                        <div class="clean-addr-item">
                            <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <span class="addr-val-text"><small class="addr-label">E-Posta:</small> {{ $order->customer_email }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fatura Adresi Kartı --}}
        <div class="col-md-6">
            <div class="address-card-modern">
                <div class="card-head-addr">
                    <div class="c-title" style="padding: 10px 0;">
                        <h5 style="margin: 0; font-weight: 700; color: #1e293b; font-size: 15px;">{{ trans('order::orders.billing_address') }}</h5>
                    </div>
                </div>

                <div class="card-body-addr" style="border-top: 1px solid #f1f5f9; padding-top: 15px;">
                    <div class="address-details-clean">
                        {{-- Müşteri Adı - Kurumsal vs Bireysel ayrımı --}}
                        @if($companyName)
                            <div class="clean-addr-item">
                                <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <span class="addr-val-main"><small class="addr-label">Firma Adı:</small> {{ $companyName }}</span>
                            </div>
                            @if($taxOffice)
                                <div class="clean-addr-item">
                                    <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    <span class="addr-val-sub"><small class="addr-label">Vergi Dairesi:</small> {{ $taxOffice }}</span>
                                </div>
                            @endif
                            @if($taxNumber)
                                <div class="clean-addr-item">
                                    <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm3 0c1.333 0 4 1 4 3v1H5v-1c0-2 2.667-3 4-3z"></path></svg>
                                    <span class="addr-val-sub"><small class="addr-label">Vergi No:</small> VKN: {{ $taxNumber }}</span>
                                </div>
                            @endif
                        @else
                            <div class="clean-addr-item">
                                <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7c0 2.209-1.791 4-4 4s-4-1.791-4-4 1.791-4 4-4 4 1.791 4 4z"></path><path d="M12 14c-3.866 0-7 3.134-7 7h14c0-3.866-3.134-7-7-7z"></path></svg>
                                <span class="addr-val-main"><small class="addr-label">Ad Soyad:</small> {{ ($activeBillingData->first_name ?? null) ?: ($activeBillingData->first_name ?? '-') }} {{ ($activeBillingData->last_name ?? null) ?: ($activeBillingData->last_name ?? '') }}</span>
                            </div>
                        @endif

                        {{-- Adres --}}
                        <div class="clean-addr-item">
                            <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0L6.343 16.657a8 8 0 1111.314 0z"></path><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span class="addr-val-sub"><small class="addr-label">Adres:</small> {{ ($activeBillingData->address_line ?? null) ?: ((($activeBillingData->address_line ?? $activeBillingData->address_1) ?? null) ?: '-') }}</span>
                        </div>

                        {{-- İl / İlçe --}}
                        <div class="clean-addr-item">
                            <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V7.618a1 1 0 011.447-.894L9 9m0 11l6-3m-6 3V9m6 8l4.553 2.276A1 1 0 0021 18.382V9.618a1 1 0 00-.553-.894L15 6m0 11V6m0 0L9 9"></path></svg>
                            <span class="addr-val-text"><small class="addr-label">Bölge:</small> {{ ($activeBillingData->city ?? null) ?: (($activeBillingData->city_title ?? $activeBillingData->city ?? $activeBillingData->city_id) ?? '-') }} • {{ ($activeBillingData->district ?? null) ?: (($activeBillingData->district_title ?? $activeBillingData->state ?? $activeBillingData->district_id) ?? '-') }} • Türkiye</span>
                        </div>

                        {{-- Telefon --}}
                        <div class="clean-addr-item">
                            <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 5C3 3.89543 3.89543 3 5 3H8.27924C8.70967 3 9.09181 3.27543 9.22792 3.68377L10.7257 8.17721C10.8831 8.64932 10.6694 9.16531 10.2243 9.38787L7.96701 10.5165C9.06925 12.9612 11.0388 14.9308 13.4835 16.033L14.6121 13.7757C14.8347 13.3306 15.3507 13.1169 15.8228 13.2743L20.3162 14.7721C20.7246 14.9082 21 15.2903 21 15.7208V19C21 20.1046 20.1046 21 19 21H18C9.71573 21 3 14.2843 3 6V5Z"></path></svg>
                            <span class="addr-val-text"><small class="addr-label">Telefon:</small> {{ $billingPhone }}</span>
                        </div>

                        {{-- Email --}}
                        @if($billingEmail)
                            <div class="clean-addr-item">
                                <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <span class="addr-val-text"><small class="addr-label">E-Posta:</small> {{ $billingEmail }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .address-details-clean {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .clean-addr-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        color: #64748b;
    }
    .addr-svg {
        width: 18px;
        height: 18px;
        color: #94a3b8;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .addr-val-main {
        font-weight: 700;
        color: #1e293b;
        font-size: 15px;
    }
    .addr-val-sub {
        font-weight: 600;
        color: #475569;
        font-size: 14px;
        line-height: 1.4;
    }
    .addr-val-text {
        font-weight: 500;
        color: #64748b;
        font-size: 14px;
    }
    .address-card-modern {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 20px;
        height: 100%;
        transition: all 0.2s;
    }
    .address-card-modern:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
</style>
