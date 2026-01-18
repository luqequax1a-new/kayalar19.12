<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.6; color: #1e293b; background: #f8fafc; }
        .main-wrapper { width: 100%; padding: 40px 0; background-color: #f8fafc; }
        .container { max-width: 650px; margin: 0 auto; background: #fff; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.04); border: 1px solid #e2e8f0; }
        
        .header { background: linear-gradient(135deg, {{ mail_theme_color() }} 0%, #334155 100%); padding: 50px 30px; text-align: center; color: #fff; }
        .header h1 { margin: 10px 0 0; font-size: 26px; font-weight: 800; letter-spacing: -0.8px; }
        .header p { margin: 8px 0 0; font-size: 16px; opacity: 0.85; font-weight: 500; }

        .section { padding: 35px; border-bottom: 1px solid #f1f5f9; }
        .section:last-child { border-bottom: none; }
        
        .section-title { font-size: 20px; font-weight: 800; margin-bottom: 24px; color: #0f172a; text-align: center; }
        
        .info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 25px; }
        .info-row { display: table; width: 100%; margin-bottom: 10px; }
        .info-label { display: table-cell; font-weight: 600; color: #64748b; width: 150px; }
        .info-value { display: table-cell; color: #1e293b; font-weight: 700; }

        .address-container { border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; background: #fff; }
        .address-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .address-cell { padding: 25px; vertical-align: top; }
        .address-cell.border-r { border-right: 1px solid #e2e8f0; }
        .address-heading { font-weight: 800; color: #0f172a; margin-bottom: 15px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid {{ mail_theme_color() }}; display: inline-block; padding-bottom: 4px; }
        .address-text { font-size: 13px; color: #475569; line-height: 1.8; }
        .address-text b { color: #0f172a; }

        .product-list-block { background: #f8fafc; border-radius: 20px; padding: 30px; border: 1px solid #e2e8f0; }
        .product-item { padding: 15px 0; border-bottom: 1px solid #e2e8f0; }
        .product-item:last-child { border-bottom: none; }
        .product-image { width: 85px; height: 85px; border-radius: 12px; object-fit: cover; border: 1px solid #e2e8f0; display: block; background: #fff; }
        .product-name { font-weight: 700; color: #1e293b; font-size: 15px; margin-bottom: 2px; display: block; text-decoration: none; }
        .product-meta { font-size: 12px; color: #64748b; line-height: 1.4; }

        .totals-wrapper { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 20px; padding: 30px; margin-top: 15px; }
        .totals-table { width: 100%; }
        .total-row td { padding: 7px 0; font-size: 14px; color: #475569; }
        .total-row.grand-total td { padding-top: 20px; font-size: 24px; font-weight: 800; color: #0f172a; border-top: 1px solid #e2e8f0; }
        
        .note-box { background: #fffbeb; border: 1px solid #fef3c7; border-radius: 16px; padding: 20px; margin-top: 25px; text-align: center; }
        .note-title { font-weight: 700; color: #92400e; font-size: 14px; margin-bottom: 8px; }
        .note-content { font-size: 13px; color: #b45309; font-style: italic; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word; }

        .footer { padding: 40px 30px; text-align: center; background: #0f172a; color: #fff; }
        .footer a { color: #fff; text-decoration: none; font-weight: 600; }
        .copyright { font-size: 12px; opacity: 0.6; margin-top: 20px; }

        @media screen and (max-width: 600px) {
            .address-cell { display: block !important; width: 100% !important; border-right: none !important; border-bottom: 1px solid #e2e8f0; }
            .address-cell:last-child { border-bottom: none; }
            .section { padding: 30px 20px; }
        }
    </style>
</head>
<body>
<table class="main-wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <div class="container">
                
                <div class="header">
                    @if($logo)
                        <img src="{{ $logo }}" style="max-height:60px; margin-bottom:15px">
                    @endif
                    <h1>Yeni Sipariş Geldi! 🚀</h1>
                    <p>Sipariş No: #{{ $order->order_number }}</p>
                </div>

                <div class="section">
                    <div class="section-title">📋 Sipariş Özeti</div>
                    <div class="info-card">
                        <div class="info-row">
                            <div class="info-label">Müşteri:</div>
                            <div class="info-value">{{ $order->customer_full_name }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">E-posta:</div>
                            <div class="info-value">{{ $order->customer_email }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Tarih:</div>
                            <div class="info-value">{{ $order->created_at->translatedFormat('d F Y H:i') }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Ödeme:</div>
                            <div class="info-value">{{ $order->payment_method }}</div>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">📍 Adres Bilgileri</div>
                    <div class="address-container">
                        <table class="address-table" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="address-cell border-r" valign="top">
                                    <div class="address-heading">Teslimat Adresi</div>
                                    <div class="address-text">
                                        @php $shipping = $order->shippingAddress; @endphp
                                        @php $shippingSnapshot = $order->shippingSnapshot; @endphp
                                        @if ($shippingSnapshot || $shipping)
                                            <b>Ad Soyad:</b> {{ ($shippingSnapshot->first_name ?? null) ?: ($shipping->first_name ?? '-') }} {{ ($shippingSnapshot->last_name ?? null) ?: ($shipping->last_name ?? '') }}<br>
                                            <b>Telefon:</b> {{ ($shippingSnapshot->phone ?? null) ?: (($shipping->phone ?? null) ?: ($order->customer_phone ?: '-')) }}<br>
                                            <b>Adres:</b> {{ ($shippingSnapshot->address_line ?? null) ?: ((($shipping->address_line ?? $shipping->address_1) ?? null) ?: '-') }}<br>
                                            <b>İl / İlçe:</b> {{ ($shippingSnapshot->district ?? null) ?: (($shipping->district_title ?? $shipping->state ?? $shipping->district_id) ?? '-') }} / {{ ($shippingSnapshot->city ?? null) ?: (($shipping->city_title ?? $shipping->city ?? $shipping->city_id) ?? '-') }}
                                        @endif
                                    </div>
                                </td>
                                <td class="address-cell" valign="top">
                                    <div class="address-heading">Fatura Bilgileri</div>
                                    <div class="address-text">
                                        @php 
                                            $shipping = $order->shippingAddress; 
                                            $shippingSnapshot = $order->shippingSnapshot;
                                            $billing = $order->billingAddress;
                                            $billingSnapshot = $order->billingSnapshot;
                                            $isBillingDifferent = ($order->shipping_address_id !== $order->billing_address_id);
                                        @endphp
                                        
                                        @if (($billingSnapshot || $billing) && $isBillingDifferent)
                                            <b>Firma Adı:</b> {{ $billingSnapshot->company_name ?? (($billing->invoice_title ?? null) ?: (($billing->company_name ?? null) ?: '-')) }}<br>
                                            <b>Vergi No:</b> {{ $billingSnapshot->tax_number ?? (($billing->invoice_tax_number ?? null) ?: (($billing->tax_number ?? null) ?: '-')) }}<br>
                                            <b>Vergi Dairesi:</b> {{ $billingSnapshot->tax_office ?? (($billing->invoice_tax_office ?? null) ?: (($billing->tax_office ?? null) ?: '-')) }}<br>
                                            <b>Email:</b> {{ $billingSnapshot->billing_email ?? (($billing->billing_email ?? null) ?: ($order->customer_email ?: '-')) }}<br>
                                            <b>Telefon:</b> {{ $billingSnapshot->phone ?? (($billing->phone ?? null) ?: ($order->customer_phone ?: '-')) }}<br>
                                            <b>Adres:</b> {{ $billingSnapshot->address_line ?? ((($billing->address_line ?? $billing->address_1) ?? null) ?: '-') }}<br>
                                            <b>İl / İlçe:</b> {{ $billingSnapshot->district ?? (($billing->district_title ?? $billing->state ?? $billing->district_id) ?? '-') }} / {{ $billingSnapshot->city ?? (($billing->city_title ?? $billing->city ?? $billing->city_id) ?? '-') }}
                                        @else
                                            @if (($billingSnapshot->company_name ?? null) || ($billingSnapshot->tax_number ?? null) || ($billingSnapshot->tax_office ?? null))
                                                <b>Firma Adı:</b> {{ $billingSnapshot->company_name ?? '-' }}<br>
                                                <b>Vergi No:</b> {{ $billingSnapshot->tax_number ?? '-' }}<br>
                                                <b>Vergi Dairesi:</b> {{ $billingSnapshot->tax_office ?? '-' }}<br>
                                                <b>Email:</b> {{ ($billingSnapshot->billing_email ?? null) ?: ($order->customer_email ?: '-') }}<br>
                                            @endif
                                            <b>Ad Soyad:</b> {{ ($shippingSnapshot->first_name ?? null) ?: ($shipping->first_name ?? '-') }} {{ ($shippingSnapshot->last_name ?? null) ?: ($shipping->last_name ?? '') }}<br>
                                            <b>Telefon:</b> {{ ($shippingSnapshot->phone ?? null) ?: (($shipping->phone ?? null) ?: ($order->customer_phone ?: '-')) }}<br>
                                            <b>Adres:</b> {{ ($shippingSnapshot->address_line ?? null) ?: (($shipping->address_line ?? null) ?: ((($shipping->address_line ?? $shipping->address_1) ?? null) ?: '-')) }}<br>
                                            <b>İl / İlçe:</b> {{ ($shippingSnapshot->district ?? null) ?: (($shippingSnapshot->city ?? null) ?: (($shipping->district_title ?? $shipping->state ?? $shipping->district_id) ?? '-')) }} / {{ ($shippingSnapshot->city ?? null) ?: (($shippingSnapshot->city ?? null) ?: (($shipping->city_title ?? $shipping->city ?? $shipping->city_id) ?? '-')) }}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">🛒 Sipariş Verilen Ürünler</div>
                    <div class="product-list-block">
                        @foreach($order->products as $product)
                            @php
                                $image = optional($product->product_variant?->base_image)->path
                                    ?? optional($product->product?->base_image)->path
                                    ?? $product->product_image_path;
                                $prodUrl = $product->product_variant?->url() ?? $product->url();
                            @endphp
                            <div class="product-item">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="95" valign="top">
                                            <a href="{{ $prodUrl }}" target="_blank">
                                                @if($image)
                                                    <img src="{{ $image }}" class="product-image">
                                                @else
                                                    <div style="width:85px; height:85px; background:#fff; border-radius:12px; border:1px solid #e2e8f0;"></div>
                                                @endif
                                            </a>
                                        </td>
                                        <td valign="top" style="padding-left: 15px;">
                                            @if ($product->is_upsell)
                                                <div style="margin-bottom: 5px;">
                                                    <span style="background-color: #fef3c7; color: #92400e; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px;">
                                                        {{ trans('storefront::upsell.offer_badge') }}
                                                    </span>
                                                </div>
                                            @endif
                                            <a href="{{ $prodUrl }}" target="_blank" class="product-name">{{ $product->name }}</a>
                                            <div class="product-meta">
                                                <div style="margin-bottom: 2px;">Stok Kodu: {{ $product->sku ?: '-' }}</div>
                                                @if($product->hasAnyVariation())
                                                    @foreach($product->variations as $variation)
                                                        <div style="margin-bottom: 2px;">{{ $variation->name }}: {{ $variation->values->pluck('label')->implode(', ') }}</div>
                                                    @endforeach
                                                @endif
                                                @if($product->hasAnyOption())
                                                    @foreach($product->options as $option)
                                                        <div style="margin-bottom: 2px;">{{ $option->name }}: {{ $option->isFieldType() ? $option->value : $option->values->pluck('label')->implode(', ') }}</div>
                                                    @endforeach
                                                @endif
                                                
                                                <div style="margin-top: 5px; font-weight: 800; color: #0f172a; font-size: 14px;">
                                                    {{ $product->getFormattedQuantityWithUnit() }}
                                                </div>
                                            </div>
                                        </td>
                                        <td align="right" valign="top" style="font-weight: 800; color: #0f172a; white-space: nowrap;">
                                            @if ($product->is_upsell && $product->original_price)
                                                <div style="color: #94a3b8; font-size: 11px; text-decoration: line-through; margin-bottom: 2px;">
                                                    {{ $product->original_price->multiply($product->qty)->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                                                </div>
                                            @endif
                                            {{ $product->line_total->convert($order->currency,$order->currency_rate)->format($order->currency) }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        @endforeach
                    </div>

                    @if($order->note)
                        <div class="note-box">
                            <div class="note-title">📝 Müşteri Notu:</div>
                            <div class="note-content">"{{ $order->note }}"</div>
                        </div>
                    @endif
                </div>

                <div class="section">
                    <div class="totals-wrapper">
                        <table class="totals-table" width="100%">
                            <tr class="total-row">
                                <td>Sepet Toplamı</td>
                                <td align="right">{{ $order->sub_total->convert($order->currency,$order->currency_rate)->format($order->currency) }}</td>
                            </tr>
                            <tr class="total-row">
                                <td>{{ $order->shipping_method }}</td>
                                <td align="right">{{ $order->shipping_cost->convert($order->currency,$order->currency_rate)->format($order->currency) }}</td>
                            </tr>

                            @php
                                $codFeeForOrder = null;
                                if ($order->isCodPayment()) {
                                    $codFee = \Modules\Shipping\SmartShippingCod::codFeeForSubtotal($order->sub_total);
                                    if (!$codFee->isZero()) {
                                        $codFeeForOrder = $codFee->convert($order->currency, $order->currency_rate);
                                    }
                                }
                            @endphp

                            @if ($codFeeForOrder)
                                <tr class="total-row">
                                    <td>{{ trans('storefront::checkout.cod_fee') }}</td>
                                    <td align="right">{{ $codFeeForOrder->format($order->currency) }}</td>
                                </tr>
                            @endif

                            @if($order->discount->amount() > 0)
                                <tr class="total-row">
                                    <td>
                                        @if($order->coupon_code)
                                            Kupon Kodu ({{ $order->coupon_code }})
                                            @if($order->coupon && $order->coupon->name)
                                                <br><span style="font-size: 11px; color: #64748b;">{{ $order->coupon->name }}</span>
                                            @endif
                                        @else
                                            İndirim
                                        @endif
                                    </td>
                                    <td align="right">-{{ $order->discount->convert($order->currency,$order->currency_rate)->format($order->currency) }}</td>
                                </tr>
                            @endif

                            @foreach($order->taxes as $tax)
                                <tr class="total-row">
                                    <td>{{ $tax->name }}</td>
                                    <td align="right">{{ $tax->order_tax->amount->convert($order->currency,$order->currency_rate)->format($order->currency) }}</td>
                                </tr>
                            @endforeach

                            <tr class="total-row grand-total">
                                <td>Toplam</td>
                                <td align="right">{{ $order->total->convert($order->currency,$order->currency_rate)->format($order->currency) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="footer">
                    <a href="{{ route('admin.orders.show', $order->id) }}">Siparişi Admin Panelinde Görüntüle</a>
                    <div class="copyright">
                        &copy; {{ date('Y') }} {{ setting('store_name') }}
                    </div>
                </div>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
