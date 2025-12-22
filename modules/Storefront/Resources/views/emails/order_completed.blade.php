<!DOCTYPE html>
<html lang="{{ locale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ setting('store_name') }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;">
        <tr>
            <td align="center" style="padding:24px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background: {{ setting('storefront_mail_theme_color', '#111827') }}; padding: 20px; text-align:center;">
                            @if ($logo)
                                <img src="{{ $logo }}" alt="{{ setting('store_name') }}" style="height:40px;display:inline-block;vertical-align:middle;" />
                            @endif
                            <div style="font-family:'Open Sans',sans-serif;font-size:24px;line-height:32px;font-weight:800;color:#fafafa;margin-top:10px;">Siparişiniz Teslim Edildi 🎁</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px;">
                            <div style="font-family:'Open Sans',sans-serif;font-size:14px;line-height:22px;color:#374151;">
                                Siparişiniz (#{{ $order->displayOrderNumber() }}) teslim edilmiştir.
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 20px 10px;">
                            @foreach ($order->products as $product)
                                @php
                                    $imagePath = $product->product_variant?->base_image?->path
                                        ?? $product->product?->base_image?->path
                                        ?? $product->product_image_path;
                                    $attributes = [];
                                    if ($product->hasAnyVariation()) {
                                        foreach ($product->variations as $variation) {
                                            $label = $variation->values()->first()?->label;
                                            if ($label) { $attributes[] = $variation->name . ': ' . $label; }
                                        }
                                    }
                                    if ($product->hasAnyOption()) {
                                        foreach ($product->options as $option) {
                                            $val = $option->option->isFieldType() ? $option->value : $option->values->implode('label', ', ');
                                            if ($val) { $attributes[] = $option->name . ': ' . $val; }
                                        }
                                    }
                                    $attributesText = implode(' • ', $attributes);
                                @endphp
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:12px;margin:8px 0;background:#ffffff;">
                                    <tr>
                                        <td width="84" valign="top" style="padding:10px;">
                                            @if ($imagePath)
                                                <img src="{{ $imagePath }}" width="64" height="64" alt="{{ $product->name }}" style="display:block;border-radius:10px;border:1px solid #e5e7eb;object-fit:cover;">
                                            @endif
                                        </td>
                                        <td valign="top" style="padding:10px 6px 10px 0; width:100%;">
                                            <div style="font-size:14px;font-weight:800;color:#0f172a;line-height:1.3;">
                                                {{ $product->name }}
                                            </div>
                                            @if ($product->sku)
                                                <div style="font-size:12px;color:#64748b;margin-top:2px;">Stok Kodu: {{ $product->sku }}</div>
                                            @endif
                                            @if ($attributesText)
                                                <div style="font-size:12px;color:#475569;margin-top:6px;">
                                                    {{ $attributesText }}
                                                </div>
                                            @endif
                                            @if ($product->unit_price)
                                                <div style="font-size:12px;color:#475569;margin-top:8px;">
                                                    {{ $product->unit_price->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                                                    × {{ $product->getFormattedQuantityWithUnit() }}
                                                    = <span style="font-weight:800; color:#16a34a;">{{ $product->line_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            @endforeach
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:10px 20px 20px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <tbody>
                                    <tr>
                                        <td style="font-size:15px;padding:5px 0;color:#111827;">Ara toplam</td>
                                        <td style="font-size:15px;padding:5px 0;text-align:right;color:#111827;">{{ $order->sub_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:15px;padding:5px 0;color:#111827;">Kargo ücreti</td>
                                        <td style="font-size:15px;padding:5px 0;text-align:right;color:#111827;">{{ $order->shipping_cost->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                                    </tr>
                                    @if ($order->discount->amount() > 0)
                                        <tr>
                                            <td style="font-size:15px;padding:5px 0;color:#111827;">İndirim</td>
                                            <td style="font-size:15px;padding:5px 0;text-align:right;color:#111827;">-{{ $order->discount->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td style="font-size:18px;padding:8px 0;color:#111827;font-weight:800;">Toplam</td>
                                        <td style="font-size:18px;padding:8px 0;text-align:right;color:#16a34a;font-weight:800;">{{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;margin-top:12px;background:#f9fafb;border-radius:12px;">
                    <tr>
                        <td style="padding:14px 20px;text-align:center;font-family:'Open Sans',sans-serif;font-size:13px;line-height:20px;color:#6b7280;">
                            Teşekkürler, {{ setting('store_name') }}
                            • <a href="{{ route('home') }}" style="color:#6b7280;text-decoration:underline;">Mağazayı ziyaret et</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
