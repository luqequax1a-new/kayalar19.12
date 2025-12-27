<!DOCTYPE html>
<html lang="en"
    style="-ms-text-size-adjust: 100%;
                    -webkit-text-size-adjust: 100%;
                    -webkit-print-color-adjust: exact;">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        td {
            vertical-align: top;
        }

        .address-block {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px;
            background: #ffffff;
        }

        

        @media screen and (max-width: 767px) {
            .order-details {
                width: 100% !important;
            }

            .shipping-address {
                width: 100% !important;
            }

            .billing-address {
                width: 100% !important;
            }

            .address-column + .address-column {
                margin-top: 12px;
            }

            
        }
    </style>
</head>

<body dir="rtl"
    style="font-family: 'Poppins', Arial, sans-serif;
                        font-size: 17px;
                        min-width: 320px;
                        color: #555555;
                        margin: 0;">
    <table
        style="border-collapse: collapse;
                    min-width: 320px;
                    max-width: 900px;
                    width: 100%;
                    margin: auto;
                    border-bottom: 2px solid {{ mail_theme_color() }};">
        <tbody>
            <tr>
                <td style="padding: 0;">
                    <table
                        style="border-collapse: collapse;
                                    width: 100%;
                                    background: {{ mail_theme_color() }};">
                        <tbody>
                            <tr>
                                <td style="padding: 10px 15px 0; text-align: center;">
                                    @if (is_null($logo))
                                        <h1
                                            style="font-family: 'Poppins', Arial, sans-serif;
                                                font-weight: 700;
                                                font-size: 34px;
                                                line-height: 39px;
                                                display: inline-block;
                                                color: #fafafa;
                                                margin: 17px 0 0;">
                                            {{ setting('store_name') }}
                                        </h1>
                                    @else
                                        <div
                                            style="display: flex;
                                                    align-items: center;
                                                    justify-content: center;
                                                    height: 64px;
                                                    width: 200px;
                                                    align-items: center;
                                                    justify-content: center;
                                                    margin: auto;">
                                            <img src="{{ $logo }}" style="max-height: 100%; max-width: 100%;"
                                                alt="Logo">
                                        </div>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 0 15px; text-align: center;">
                                    <span
                                        style="font-family: 'Poppins', Arial, sans-serif;
                                                font-size: 28px;
                                                line-height: 34px;
                                                font-weight: 800;
                                                display: inline-block;
                                                color: #fafafa;
                                                margin: 0;">
                                        تم إنشاء طلبك 🎉
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 0 15px;">
                                    <table
                                        style="border-collapse: collapse;
                                                width: 230px;
                                                margin: 0 auto 20px; display:none;">
                                        <tbody>
                                            <tr>
                                                <td
                                                    style="font-family: 'Open Sans', sans-serif;
                                                    font-size: 16px;
                                                    font-weight: 400;
                                                    color: #fafafa;
                                                    padding: 0;">
                                                    <span style="float: right; font-weight: bold;">
                                                        {{ trans('storefront::invoice.order_id') }}:
                                                    </span>

                                                    <span style="float: left;">
                                                        #{{ $order->displayOrderNumber() }}
                                                    </span>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td
                                                    style="font-family: 'Open Sans', sans-serif;
                                                    font-size: 16px;
                                                    font-weight: 400;
                                                    color: #fafafa;
                                                    padding: 0;">
                                                    <span style="float: right; font-weight: bold;">
                                                        {{ trans('storefront::invoice.date') }}:
                                                    </span>

                                                    <span style="float: left;">
                                                        {{ $order->created_at->toFormattedDateString() }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>

            <tr>
                <td style="padding: 30px 15px;">
                    <table
                        style="border-collapse: collapse;
                                    min-width: 320px;
                                    max-width: 760px;
                                    width: 100%;
                                    margin: auto;">
                        <tbody>
                            <tr>
                                <td style="padding: 0; width: 50%;">
                                    <table style="border-collapse: collapse; width: 100%;">
                                        <tbody>
                                            <tr>
                                                <td style="padding: 0;">
                                                    <h5
                                                        style="font-family: 'Poppins', Arial, sans-serif;
                                                            font-weight: 700;
                                                            font-size: 20px;
                                                            line-height: 22px;
                                                            margin: 0 0 8px;
                                                            color: #111827;">
                                                        {{ trans('storefront::invoice.order_details') }}
                                                    </h5>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="padding: 0;">
                                                    <table class="order-details"
                                                        style="border-collapse: collapse; width: 50%;">
                                                        <tbody>
                                                            <tr>
                                                                <td style="font-family: 'Poppins', Arial, sans-serif; font-size: 17px; font-weight: 700; color:#000; padding: 4px 0; white-space:nowrap;">{{ trans('storefront::invoice.order_id') }}:</td>
                                                                <td style="font-family: 'Poppins', Arial, sans-serif; font-size: 17px; padding: 4px 0; word-break: break-all;">#{{ $order->displayOrderNumber() }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="font-family: 'Poppins', Arial, sans-serif; font-size: 17px; font-weight: 700; color:#000; padding: 4px 0; white-space:nowrap;">{{ trans('storefront::invoice.date') }}:</td>
                                                                <td style="font-family: 'Poppins', Arial, sans-serif; font-size: 17px; padding: 4px 0; word-break: break-all;">{{ $order->created_at->toFormattedDateString() }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="font-family: 'Poppins', Arial, sans-serif; font-size: 17px; font-weight: 700; color:#000; padding: 4px 0; white-space:nowrap;">{{ trans('storefront::invoice.total') }}:</td>
                                                                <td style="font-family: 'Poppins', Arial, sans-serif; font-size: 17px; padding: 4px 0; word-break: break-all;">{{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td
                                                                    style="font-family: 'Poppins', Arial, sans-serif;
                                                                    font-size: 17px;
                                                                    font-weight: 700;
                                                                    color:#000;
                                                                    padding: 4px 0; white-space:nowrap;">
                                                                    {{ trans('storefront::invoice.email') }}:
                                                                </td>

                                                                <td
                                                                    style="font-family: 'Poppins', Arial, sans-serif;
                                                                    font-size: 17px;
                                                                    padding: 4px 0;
                                                                    word-break: break-all;">
                                                                    {{ $order->customer_email }}
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td
                                                                    style="font-family: 'Poppins', Arial, sans-serif;
                                                                    font-size: 17px;
                                                                    font-weight: 700;
                                                                    color:#000;
                                                                    padding: 4px 0; white-space:nowrap;">
                                                                    {{ trans('storefront::invoice.phone') }}:
                                                                </td>

                                                                <td
                                                                    style="font-family: 'Poppins', Arial, sans-serif;
                                                                    font-size: 17px;
                                                                    padding: 4px 0;
                                                                    word-break: break-all;">
                                                                    {{ $order->customer_phone }}
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td
                                                                    style="font-family: 'Poppins', Arial, sans-serif;
                                                                    font-size: 17px;
                                                                    font-weight: 700;
                                                                    color:#000;
                                                                    padding: 4px 20px 4px 0;
                                                                    white-space: nowrap;">
                                                                    {{ trans('storefront::invoice.payment_method') }}:
                                                                </td>

                                                                <td
                                                                    style="font-family: 'Poppins', Arial, sans-serif;
                                                                    font-size: 17px;
                                                                    padding: 4px 0;
                                                                    word-break: break-all;">
                                                                    {{ $order->payment_method }}

                                                                    @if ($order->payment_method === 'Bank Transfer')
                                                                        <br>
                                                                        <span style="color: #999; font-size: 15px; margin-top: 5px; display: block;">
                                                                            {!! setting('bank_transfer_instructions') !!}
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 0;">
                                    <table class="shipping-address"
                                        style="border-collapse: collapse; width: 50%; float: left; margin-top: 25px;">
                                        <tbody>
                                            <tr>
                                                <td style="padding: 0;">
                                                    <h5 style="font-family: 'Poppins', Arial, sans-serif; font-weight: 700; font-size: 20px; line-height: 22px; margin: 0 0 8px; color: #111827; text-align:center;">🚚 {{ trans('storefront::invoice.shipping_address') }}</h5>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="font-family: 'Open Sans', sans-serif; font-weight: 400; font-size: 15px; padding: 0;">
                                                    <div class="address-block" style="text-align:center; font-size:15px;">
                                                        @php($ss = $order->shippingSnapshot)
                                                        @php($sa = $order->shippingAddress)
                                                        @if ($ss || $sa)
                                                            <span style="display: block; padding: 4px 0;">{{ trim((string) (($ss->first_name ?? $sa->first_name ?? '') . ' ' . ($ss->last_name ?? $sa->last_name ?? ''))) }}</span>
                                                            <span style="display: block; padding: 4px 0;">{{ $ss->phone ?? ($sa->phone ?? '') }}</span>
                                                            <span style="display: block; padding: 4px 0;">{{ $ss->address_line ?? ($sa->address_line ?? $sa->address_1 ?? '') }}</span>
                                                            <span style="display: block; padding: 4px 0;">
                                                                {{ $ss->district ?? ($sa->district_title ?? '') }}
                                                                @if ((($ss->district ?? null) && ($ss->city ?? null)) || (($sa->district_title ?? null) && ($sa->city_title ?? null)))
                                                                    ,
                                                                @endif
                                                                {{ $ss->city ?? ($sa->city_title ?? '') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <table class="billing-address"
                                        style="border-collapse: collapse; width: 50%; float: left; margin-top: 25px;">
                                        <tbody>
                                            <tr>
                                                <td style="padding: 0;">
                                                    <h5 style="font-family: 'Poppins', Arial, sans-serif; font-weight: 700; font-size: 20px; line-height: 22px; margin: 0 0 8px; color: #111827; text-align:center;">📄 {{ trans('storefront::invoice.billing_address') }}</h5>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="font-family: 'Open Sans', sans-serif; font-weight: 400; font-size: 15px; padding: 0;">
                                                    <div class="address-block" style="text-align:center; font-size:15px;">
                                                        @php($bs = $order->billingSnapshot)
                                                        @php($ba = $order->billingAddress)
                                                        @php($same = $order->billing_address_id === $order->shipping_address_id)
                                                        @if (!$same && ($bs || $ba))
                                                            @if (($bs->company_name ?? null) || ($ba->company_name ?? null))
                                                                <span style="display: block; padding: 4px 0;">{{ $bs->company_name ?? $ba->company_name }}</span>
                                                            @endif
                                                            @if (($bs->tax_office ?? null) || ($ba->tax_office ?? null) || ($bs->tax_number ?? null) || ($ba->tax_number ?? null))
                                                                <span style="display: block; padding: 4px 0;">{{ trans('storefront::invoice.tax_office') }}: {{ $bs->tax_office ?? $ba->tax_office }}</span>
                                                                <span style="display: block; padding: 4px 0;">{{ trans('storefront::invoice.tax_number') }}: {{ $bs->tax_number ?? $ba->tax_number }}</span>
                                                            @endif
                                                            @if ($ba && $ba->billing_email)
                                                                <span style="display: block; padding: 4px 0; word-break:break-all;">{{ trans('storefront::invoice.billing_email') }}: {{ $ba->billing_email }}</span>
                                                            @endif
                                                        @endif
                                                        @if ($same)
                                                            @php($bs = $order->shippingSnapshot)
                                                            @php($ba = $order->shippingAddress)
                                                        @endif
                                                        @if ($bs || $ba)
                                                            <span style="display: block; padding: 4px 0;">{{ $bs->phone ?? ($ba->phone ?? '') }}</span>
                                                            <span style="display: block; padding: 4px 0;">{{ $bs->address_line ?? ($ba->address_line ?? $ba->address_1 ?? '') }}</span>
                                                            <span style="display: block; padding: 4px 0;">
                                                                {{ $bs->district ?? ($ba->district_title ?? '') }}
                                                                @if ((($bs->district ?? null) && ($bs->city ?? null)) || (($ba->district_title ?? null) && ($ba->city_title ?? null)))
                                                                    ,
                                                                @endif
                                                                {{ $bs->city ?? ($ba->city_title ?? '') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 30px 0 0;">
                                    @foreach ($order->products as $product)
                                            @php
                                                $imagePath = $product->product_variant?->base_image?->path
                                                    ?? $product->product?->base_image?->path
                                                    ?? $product->product_image_path;
                                            @endphp
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:12px;margin:8px 0;background:#ffffff;">
                                                <tr>
                                                    <td width="84" valign="top" style="padding:10px;">
                                                        @if ($imagePath)
                                                            <img src="{{ $imagePath }}" width="64" height="64" alt="{{ $product->name }}" style="display:block;border-radius:10px;border:1px solid #e5e7eb;object-fit:cover;">
                                                        @endif
                                                    </td>
                                                    <td valign="top" style="padding:10px 6px 10px 0; width:100%;">
                                                        <div style="font-size:16px;font-weight:800;color:#0f172a;line-height:1.3;">
                                                            {{ $product->name }}
                                                        </div>
                                                        @if ($product->sku)
                                                            <div style="font-size:14px;color:#0f172a;margin-top:2px;font-weight:700;">كودالمخزون:{{ $product->sku }}</div>
                                                        @endif
                                                        @if ($product->hasAnyVariation())
                                                            <div style="font-size:14px;color:#0f172a;margin-top:6px;font-weight:700;">
                                                                @foreach ($product->variations as $variation)
                                                                    <span>{{ $variation->name }}:{{ $variation->values()->first()?->label }}</span>@if(!$loop->last)، @endif
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @if ($product->hasAnyOption())
                                                            <div style="font-size:14px;color:#0f172a;margin-top:4px;font-weight:700;">
                                                                @foreach ($product->options as $option)
                                                                    <span>{{ $option->name }}:@if ($option->option->isFieldType()){{ $option->value }}@else{{ $option->values->implode('label', ', ') }}@endif</span>@if(!$loop->last)، @endif
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @if ($product->unit_price)
                                                            <div style="font-size:14px;color:#0f172a;margin-top:8px;font-weight:700;">
                                                                <span style="font-weight:700;">{{ $product->unit_price->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
                                                                × <span style="font-weight:700;">{{ $product->getFormattedQuantityWithUnit() }}</span>
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
                                <td style="padding: 0;">
                                    <h3 style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 12px; text-align: center;">💳 ملخص الدفع</h3>
                                    <table
                                        style="border-collapse: collapse;
                                                    width: 100%;
                                                    margin-top: 10px;
                                                    border:1px solid #e5e7eb; border-radius:12px; background:#ffffff;">
                                        <tbody>
                                            <tr>
                                                <td style="padding: 10px 12px; font-size: 16px; color:#334155;">{{ trans('storefront::invoice.subtotal') }}</td>
                                                <td style="padding: 10px 12px; font-size: 16px; color:#334155; text-align: left;">{{ $order->sub_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                                            </tr>

                                            @if ($order->hasShippingMethod())
                                                <tr>
                                                    <td style="padding: 10px 12px; font-size: 16px; color:#334155;">{{ $order->shipping_method }}</td>
                                                    <td style="padding: 10px 12px; font-size: 16px; color:#334155; text-align: left;">{{ $order->shipping_cost->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                                                </tr>
                                            @endif

                                            @if ($order->hasCoupon())
                                                <tr>
                                                    <td style="padding: 10px 12px; font-size: 16px; color:#334155;">{{ trans('storefront::invoice.coupon') }} (<span style="color:#64748b;">{{ $order->coupon->code }}</span>)</td>
                                                    <td style="padding: 10px 12px; font-size: 16px; color:#334155; text-align: left;">{{ $order->discount->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                                                </tr>
                                            @endif

                                            @foreach ($order->taxes as $tax)
                                                <tr>
                                                    <td style="padding: 10px 12px; font-size: 14px; color:#334155;">{{ $tax->name }}</td>
                                                    <td style="padding: 10px 12px; font-size: 14px; color:#334155; text-align: left;">{{ $tax->order_tax->amount->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                                                </tr>
                                            @endforeach

                                            <tr>
                                                <td style="padding: 12px 12px; font-size: 18px; font-weight:700; color:#0f172a; border-top:1px solid #e5e7eb;">{{ trans('storefront::invoice.total') }}</td>
                                                <td style="padding: 12px 12px; font-size: 18px; font-weight:800; color:#16a34a; text-align: left; border-top:1px solid #e5e7eb;">{{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <table style="border-collapse: collapse; width: 100%;">
        <tbody>
            <tr>
                <td style="padding: 20px 0; background: {{ mail_theme_color() }}; text-align: center; color:#ffffff;">
                    <div style="font-family: 'Poppins', Arial, sans-serif; font-weight: 800; font-size: 20px; line-height: 24px; color: #ffffff; padding: 0 15px;">
                        {{ setting('store_name') }}
                    </div>
                    <div style="font-family: 'Poppins', Arial, sans-serif; font-weight: 400; font-size: 16px; line-height: 22px; color: #e5e7eb; padding: 6px 15px;">
                        @if (setting('store_phone') && ! setting('store_phone_hide'))
                            <a href="tel:{{ setting('store_phone') }}" style="text-decoration: none; color: #ffffff;">{{ setting('store_phone') }}</a>
                        @endif
                        @if (setting('store_email') && ! setting('store_email_hide'))
                            <span style="margin: 0 6px; color:#cbd5e1;">•</span>
                            <a href="mailto:{{ setting('store_email') }}" style="text-decoration: none; color: #ffffff;">{{ setting('store_email') }}</a>
                        @endif
                        @if (setting('storefront_address'))
                            <span style="margin: 0 6px; color:#cbd5e1;">•</span>
                            <span style="color:#ffffff;">{{ setting('storefront_address') }}</span>
                        @endif
                    </div>
                    <div style="font-family: 'Poppins', Arial, sans-serif; font-weight: 600; font-size: 15px; line-height: 22px; color: #ffffff; padding: 6px 15px;">
                        <a href="{{ route('categories.index') }}" style="text-decoration: none; color: #ffffff;">{{ trans('storefront::layouts.categories') }}</a>
                        <span style="margin: 0 10px; color:#cbd5e1;">•</span>
                        <a href="{{ route('home') }}#flash-sale" style="text-decoration: none; color: #ffffff;">Discounted Products</a>
                        <span style="margin: 0 10px; color:#cbd5e1;">•</span>
                        <a href="{{ route('account.dashboard.index') }}" style="text-decoration: none; color: #ffffff;">{{ trans('storefront::layouts.my_account') }}</a>
                        <span style="margin: 0 10px; color:#cbd5e1;">•</span>
                        <a href="{{ route('register') }}" style="text-decoration: none; color: #ffffff;">{{ trans('storefront::layouts.login_register') }}</a>
                    </div>
                    <div style="font-family: 'Poppins', Arial, sans-serif; font-weight: 400; font-size: 14px; line-height: 20px; color: #e5e7eb; padding: 0 15px;">
                        &copy; {{ date('Y') }}
                        <a target="_blank" href="{{ route('home') }}" style="text-decoration: none; color: #ffffff;">{{ setting('store_name') }}</a>
                        {{ trans('storefront::mail.all_rights_reserved') }}
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
