<!DOCTYPE html>
<html lang="tr" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;-webkit-print-color-adjust:exact;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400" rel="stylesheet">
    <style>
        body { margin:0; padding:0; }
        @media screen and (max-width:600px) {
            .desktop-only { display:none !important; }
            .mobile-only  { display:block !important; }
            .wrapper      { width:100% !important; max-width:100% !important; }
        }
        .mobile-only { display:none; }
    </style>
</head>
<body style="font-family:'Open Sans',sans-serif;font-size:15px;margin:0;padding:0;background:#f3f4f6;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;background:#f3f4f6;">
    <tr>
        <td align="center" style="padding:24px 8px;">

            <!-- ORTA KUTU (MAX 720PX) -->
            <table role="presentation" cellpadding="0" cellspacing="0" class="wrapper"
                   style="border-collapse:collapse;width:100%;max-width:720px;background:#ffffff;border-radius:12px;overflow:hidden;">
                <tr>
                    <td style="padding:0;">
                        <!-- ÜST BANNER -->
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                            <tr>
                                <td style="background:{{ mail_theme_color() }};text-align:center;">
                                    @if (is_null($logo))
                                        <h5 style="font-size:30px;line-height:36px;margin:0;padding:30px 15px;">
                                            <a href="{{ route('home') }}" style="font-family:'Open Sans',sans-serif;font-weight:400;color:#ffffff;text-decoration:none;">
                                                {{ setting('store_name') }}
                                            </a>
                                        </h5>
                                    @else
                                        <div style="height:64px;width:200px;display:flex;align-items:center;justify-content:center;margin:auto;padding:16px 15px;">
                                            <img src="{{ $logo }}" style="max-height:100%;max-width:100%;" alt="Logo">
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- İÇERİK -->
                <tr>
                    <td style="padding:24px 16px 8px 16px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;">
                            <tr>
                                <td style="text-align:center;padding:0 0 12px 0;">
                                    <div style="font-weight:800;font-size:22px;line-height:30px;color:#111827;">
                                        Yeni Sipariş Alındı
                                    </div>
                                </td>
                            </tr>

                            <!-- SİPARİŞ ÖZETİ KUTUSU (MINIMAL) -->
                            <tr>
                                <td style="padding:8px 0 16px 0;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                           style="border-collapse:collapse;width:100%;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;">
                                        <tr>
                                            <td style="padding:14px 18px;">
                                                <div style="font-size:15px;font-weight:700;margin:0 0 8px 0;color:#111827;">
                                                    Sipariş Özeti
                                                </div>

                                                <p style="margin:3px 0;font-size:13px;color:#111827;">
                                                    <strong>Sipariş:</strong> #{{ $order->displayOrderNumber() }}
                                                </p>

                                                <p style="margin:3px 0;font-size:13px;color:#111827;">
                                                    <strong>Tarih:</strong> {{ $order->created_at->toDateString() }}
                                                </p>

                                                <p style="margin:6px 0 3px;font-size:13px;color:#111827;">
                                                    <strong>Ödeme Yöntemi:</strong> {{ $order->payment_method }}
                                                </p>

                                                <p style="margin:8px 0 0;font-size:13px;color:#111827;">
                                                    <strong>Toplam Sipariş Tutarı:</strong>
                                                    {{ $order->total->convert($order->currency,$order->currency_rate)->format($order->currency) }}
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- ÜRÜN LİSTESİ BAŞLIK (GRİ BAR) -->
                            <tr>
                                <td style="padding:12px 0 4px 0;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="background:#f3f4f6;border-radius:10px;text-align:center;padding:10px;">
                                                <span style="font-weight:800;font-size:18px;color:#111827;">
                                                    Sipariş Verilen Ürünler
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- ÜRÜN LİSTESİ (MINIMAL, TEK YAPI) -->
                            <tr>
                                <td style="padding:8px 0 4px 0;">
                                    @foreach ($order->products as $product)
                                        @php
                                            $imagePath = $product->product_variant?->base_image?->path
                                                ?? $product->product?->base_image?->path
                                                ?? $product->product_image_path;
                                        @endphp

                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                               style="border:1px solid #e5e7eb;border-radius:10px;margin:8px 0;background:#ffffff;">
                                            <tr>
                                                <td width="96" valign="top" style="padding:10px;">
                                                    @if ($imagePath)
                                                        <img src="{{ $imagePath }}"
                                                             width="80" height="80"
                                                             alt="{{ $product->name }}"
                                                             style="display:block;border-radius:8px;border:1px solid #e5e7eb;object-fit:cover;">
                                                    @endif
                                                </td>

                                                <td valign="top" style="padding:10px 14px 10px 0;">
                                                    <div style="font-size:14px;font-weight:700;color:#111827;line-height:1.3;margin-bottom:4px;">
                                                        {{ $product->name }}
                                                    </div>
                                                    @php
                                                        $attributes = [];
                                                        if ($product->hasAnyVariation()) {
                                                            foreach ($product->variations as $variation) {
                                                                $label = $variation->values()->first()?->label;
                                                                if ($label) {
                                                                    $attributes[] = $variation->name . ': ' . $label;
                                                                }
                                                            }
                                                        }
                                                        if ($product->hasAnyOption()) {
                                                            foreach ($product->options as $option) {
                                                                $val = $option->isFieldType() ? $option->value : $option->values->implode('label', ', ');
                                                                if ($val) {
                                                                    $attributes[] = $option->name . ': ' . $val;
                                                                }
                                                            }
                                                        }
                                                        $attributesText = implode(' • ', $attributes);
                                                    @endphp
                                                    @if (!empty($attributesText))
                                                        <div style="font-size:12px;color:#4b5563;margin:2px 0;">
                                                            {{ $attributesText }}
                                                        </div>
                                                    @endif

                                                    @if ($product->sku)
                                                        <div style="font-size:12px;color:#4b5563;margin:2px 0;">
                                                            <strong>Stok Kodu:</strong> {{ $product->sku }}
                                                        </div>
                                                    @endif

                                                    <div style="font-size:12px;color:#4b5563;margin:2px 0;">
                                                        <strong>Miktar:</strong> {{ $product->getFormattedQuantityWithUnit() }}
                                                    </div>

                                                    <div style="font-size:12px;color:#4b5563;margin:2px 0;">
                                                        <strong>Fiyat:</strong>
                                                        {{ $product->line_total->convert($order->currency,$order->currency_rate)->format($order->currency) }}
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    @endforeach
                                </td>
                            </tr>

                            <!-- SİPARİŞ ÖZETİ TABLOSU (ARA TOPLAM / KARGO / TOPLAM) -->
                            <tr>
                                <td style="padding:8px 0 4px 0;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                           style="border-collapse:collapse;width:100%;margin-top:4px;">
                                        <tr>
                                            <td style="font-size:15px;padding:4px 0;">Ara Toplam</td>
                                            <td style="font-size:15px;padding:4px 0;text-align:right;">
                                                {{ $order->sub_total->convert($order->currency,$order->currency_rate)->format($order->currency) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:15px;padding:4px 0;">Kargo Ücreti</td>
                                            <td style="font-size:15px;padding:4px 0;text-align:right;">
                                                {{ $order->shipping_cost->convert($order->currency,$order->currency_rate)->format($order->currency) }}
                                            </td>
                                        </tr>
                                        @if ($order->discount->amount() > 0)
                                            <tr>
                                                <td style="font-size:15px;padding:4px 0;">İndirim</td>
                                                <td style="font-size:15px;padding:4px 0;text-align:right;">
                                                    -{{ $order->discount->convert($order->currency,$order->currency_rate)->format($order->currency) }}
                                                </td>
                                            </tr>
                                        @endif
                                     
                                        <tr>
                                            <td style="border-top:1px solid #e5e7eb;font-weight:700;padding-top:6px;">Toplam</td>
                                            <td style="border-top:1px solid #e5e7eb;font-weight:700;padding-top:6px;text-align:right;">
                                                {{ $order->total->convert($order->currency,$order->currency_rate)->format($order->currency) }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- BUTON -->
                            <tr>
                                <td style="padding:20px 0 10px 0;text-align:center;">
                                    <a href="{{ route('admin.orders.show',$order) }}"
                                       style="font-family:'Open Sans',sans-serif;font-weight:400;text-decoration:none;display:inline-block;background:{{ mail_theme_color() }};color:#fafafa;padding:11px 30px;border-radius:4px;">
                                        {{ trans('checkout::mail.view_order') }}
                                    </a>
                                </td>
                            </tr>

                            <!-- LİNK FALLBACK -->
                            <tr>
                                <td style="padding:6px 0 0 0;border-top:1px solid #e9e9e9;">
                                    <span style="font-size:14px;line-height:22px;display:block;color:#666666;padding:6px 0 4px 0;">
                                        {{ trans('checkout::mail.if_you\’re_having_trouble') }}
                                    </span>
                                    <a href="{{ route('admin.orders.show',$order) }}"
                                       style="font-size:15px;line-height:24px;text-decoration:underline;color:#31629f;word-break:break-all;">
                                        {{ route('admin.orders.show',$order) }}
                                    </a>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>

                <!-- ALT FOOTER -->
                <tr>
                    <td style="padding:14px 16px;background:#0f172a;text-align:center;">
                        <div style="font-size:14px;color:#ffffff;">
                            <div style="margin-bottom:6px;">
                                <a target="_blank" href="{{ route('home') }}" style="text-decoration:none;color:#ffffff;">
                                    {{ setting('store_name') }}
                                </a>
                            </div>
                            @if (setting('store_phone') && ! setting('store_phone_hide'))
                                <div>
                                    <a href="tel:{{ setting('store_phone') }}" style="text-decoration:none;color:#ffffff;">
                                        {{ setting('store_phone') }}
                                    </a>
                                </div>
                            @endif
                            @if (setting('store_email') && ! setting('store_email_hide'))
                                <div>
                                    <a href="mailto:{{ setting('store_email') }}" style="text-decoration:none;color:#ffffff;">
                                        {{ setting('store_email') }}
                                    </a>
                                </div>
                            @endif
                            <div style="margin-top:8px;opacity:0.8;">
                                &copy; {{ date('Y') }} {{ trans('storefront::mail.all_rights_reserved') }}
                            </div>
                        </div>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
