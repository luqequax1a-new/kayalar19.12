<!DOCTYPE html>
<html lang="en" style="-ms-text-size-adjust: 100%;
                    -webkit-text-size-adjust: 100%;
                    -webkit-print-color-adjust: exact;"
>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400" rel="stylesheet">
        <style>
            @media screen and (max-width: 600px) {
                .desktop-only { display: none !important; }
                .mobile-only { display: block !important; }
            }

            .mobile-only { display: none; }
        </style>
    </head>

    <body dir="rtl" style="font-family: 'Open Sans', sans-serif;
                        font-size: 15px;
                        min-width: 320px;
                        margin: 0;"
    >
        <table style="border-collapse: collapse; width: 100%;">
            <tbody>
                <tr>
                    <td style="padding: 0;">
                        <table style="border-collapse: collapse; width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="background: {{ mail_theme_color() }}; text-align: center;">
                                        @if (is_null($logo))
                                            <h5 style="font-size: 30px;
                                                    line-height: 36px;
                                                    margin: 0;
                                                    padding: 30px 15px;
                                                    text-align: center;"
                                            >
                                                <a href="{{ route('home') }}" style="font-family: 'Open Sans', sans-serif;
                                                                                    font-weight: 400;
                                                                                    color: #ffffff;
                                                                                    text-decoration: none;"
                                                >
                                                    {{ setting('store_name') }}
                                                </a>
                                            </h5>
                                        @else
                                            <div style="display: flex;
                                                        height: 64px;
                                                        width: 200px;
                                                        align-items: center;
                                                        justify-content: center;
                                                        margin: auto;
                                                        padding: 16px 15px;"
                                            >
                                                <img src="{{ $logo }}" style="max-height: 100%; max-width: 100%;" alt="Logo">
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 40px 15px;">
                        <table style="border-collapse: collapse;
                                    min-width: 320px;
                                    width: 100%;
                                    margin: auto;"
                        >
                            <tr>
                                <td style="padding: 0;">
                                    <span style="font-family: 'Open Sans', sans-serif;
                                                font-weight: 400;
                                                font-size: 16px;
                                                line-height: 26px;
                                                color: #666666;
                                                display: block;"
                                    >
                                        {{ trans('checkout::mail.new_order_text', ['order_id' => $order->displayOrderNumber()]) }}
                                    </span>
                                </td>
                            </tr>

                            <tr class="mobile-only">
                                <td style="padding: 10px 0;">
                                    @foreach ($order->products as $product)
                                        @php
                                            $imagePath = $product->product_variant?->base_image?->path
                                                ?? $product->product?->base_image?->path
                                                ?? $product->product_image_path;
                                        @endphp
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:12px;margin:8px 0;">
                                            <tr>
                                                <td width="84" valign="top" style="padding:10px;">
                                                    @if ($imagePath)
                                                        <img src="{{ $imagePath }}" width="64" height="64" alt="{{ $product->name }}" style="display:block;border-radius:10px;border:1px solid #e5e7eb;object-fit:cover;">
                                                    @endif
                                                </td>
                                                <td valign="top" style="padding:10px 6px 10px 0;">
                                                    <div style="font-size:14px;font-weight:800;color:#0f172a;line-height:1.3;">
                                                        {{ $product->name }}
                                                    </div>
                                                    @if ($product->sku)
                                                        <div style="font-size:12px;color:#64748b;margin-top:2px;">كود: {{ $product->sku }}</div>
                                                    @endif
                                                    @if ($product->hasAnyVariation())
                                                        <div style="font-size:12px;color:#475569;margin-top:6px;">
                                                            @foreach ($product->variations as $variation)
                                                                <span>{{ $variation->name }}: {{ $variation->values()->first()?->label }}</span>@if(!$loop->last)، @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    @if ($product->hasAnyOption())
                                                        <div style="font-size:12px;color:#475569;margin-top:4px;">
                                                            @foreach ($product->options as $option)
                                                                <span>{{ $option->name }}: @if ($option->option->isFieldType()){{ $option->value }}@else{{ $option->values->implode('label', ', ') }}@endif</span>@if(!$loop->last)، @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    <div style="margin-top:10px;">
                                                        <span style="display:inline-block;font-size:12px;font-weight:700;color:#1f2937;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:999px;padding:4px 8px;">
                                                            الكمية: {{ $product->getFormattedQuantityWithUnit() }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td align="left" valign="top" style="padding:10px;white-space:nowrap;">
                                                    <div style="font-size:14px;font-weight:800;color:#16a34a;">
                                                        {{ $product->line_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                                                    </div>
                                                    @if ($product->unit_price)
                                                        <div style="font-size:12px;color:#64748b;margin-top:4px;">للوحدة: {{ $product->unit_price->convert($order->currency, $order->currency_rate)->format($order->currency) }}</div>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    @endforeach
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 6px 0;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="summary-table" style="border-collapse: collapse; width: 100%;">
                                        <tr>
                                            <td class="summary-label" style="font-size: 15px; padding: 5px 0;">{{ trans('storefront::invoice.sub_total') }}</td>
                                            <td class="summary-value" style="font-size: 15px; padding: 5px 0; text-align: left;">{{ $order->sub_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="summary-label" style="font-size: 15px; padding: 5px 0;">{{ trans('storefront::invoice.shipping_cost') }}</td>
                                            <td class="summary-value" style="font-size: 15px; padding: 5px 0; text-align: left;">{{ $order->shipping_cost->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                                        </tr>
                                        @if ($order->discount->value() > 0)
                                            <tr>
                                                <td class="summary-label" style="font-size: 15px; padding: 5px 0;">{{ trans('storefront::invoice.discount') }}</td>
                                                <td class="summary-value" style="font-size: 15px; padding: 5px 0; text-align: left;">-{{ $order->discount->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td class="summary-label" style="font-size: 15px; padding: 5px 0;">{{ trans('storefront::invoice.tax') }}</td>
                                            <td class="summary-value" style="font-size: 15px; padding: 5px 0; text-align: left;">{{ $order->totalTax()->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                                        </tr>
                                        <tr class="summary-total-row">
                                            <td style="border-top: 1px solid #e5e7eb; font-weight: 700; padding-top: 8px;">{{ trans('storefront::invoice.total') }}</td>
                                            <td style="border-top: 1px solid #e5e7eb; font-weight: 700; padding-top: 8px; text-align: left;">{{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 30px 0; text-align: center;">
                                    <a href="{{ route('admin.orders.show', $order) }}" style="font-family: 'Open Sans', sans-serif;
                                                                font-weight: 400;
                                                                text-decoration: none;
                                                                display: inline-block;
                                                                background: {{ mail_theme_color() }};
                                                                color: #fafafa;
                                                                padding: 11px 30px;
                                                                border: none;
                                                                border-radius: 3px;
                                                                outline: 0;"
                                    >
                                        {{ trans('checkout::mail.view_order') }}
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 0;">
                                    <span style="font-family: 'Open Sans', sans-serif;
                                                font-weight: 400;
                                                font-size: 15px;
                                                line-height: 24px;
                                                display: block;
                                                padding: 5px 0 10px;
                                                color: #666666;
                                                border-top: 1px solid #e9e9e9;"
                                    >
                                        {{ trans('checkout::mail.if_you\’re_having_trouble') }}
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 0;">
                                    <a href="{{ route('admin.orders.show', $order) }}" style="font-family: 'Open Sans', sans-serif;
                                                                font-weight: 400;
                                                                font-size: 16px;
                                                                line-height: 26px;
                                                                text-decoration: underline;
                                                                color: #31629f;
                                                                word-break: break-all;"
                                    >
                                        {{ route('admin.orders.show', $order) }}
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 20px 15px; background: #0f172a; text-align: center;">
                        <div style="font-family: 'Open Sans', sans-serif; font-size: 14px; color: #ffffff;">
                            <div style="margin-bottom:8px;">
                                <a target="_blank" href="{{ route('home') }}" style="text-decoration: none; color: #ffffff;">{{ setting('store_name') }}</a>
                            </div>
                            @if (setting('store_phone') && ! setting('store_phone_hide'))
                                <div><a href="tel:{{ setting('store_phone') }}" style="text-decoration: none; color: #ffffff;">{{ setting('store_phone') }}</a></div>
                            @endif
                            @if (setting('store_email') && ! setting('store_email_hide'))
                                <div><a href="mailto:{{ setting('store_email') }}" style="text-decoration: none; color: #ffffff;">{{ setting('store_email') }}</a></div>
                            @endif
                            <div style="margin-top:10px; opacity:0.8;">
                                &copy; {{ date('Y') }} {{ trans('storefront::mail.all_rights_reserved') }}
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
</html>
