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
                            <div style="font-family:'Open Sans',sans-serif;font-size:24px;line-height:32px;font-weight:800;color:#fafafa;margin-top:10px;">Siparişiniz İptal Edildi ❌</div>
                            <div style="font-family:'Open Sans',sans-serif;font-size:15px;line-height:22px;font-weight:600;color:#e5e7eb;margin-top:6px;">
                                #{{ $order->displayOrderNumber() }}
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px;">
                            <div style="font-family:'Open Sans',sans-serif;font-size:14px;line-height:22px;color:#374151;">
                                Siparişiniz (#{{ $order->displayOrderNumber() }}) iptal edilmiştir. Eğer bir hata olduğunu düşünüyorsanız bizimle iletişime geçebilirsiniz.
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 20px 20px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <tbody>
                                    <tr>
                                        <td style="font-size:14px;padding:4px 0;color:#111827;white-space:nowrap;font-weight:600;">Sipariş No:</td>
                                        <td style="font-size:14px;padding:4px 0;color:#111827;">#{{ $order->displayOrderNumber() }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:14px;padding:4px 0;color:#111827;white-space:nowrap;font-weight:600;">Tarih:</td>
                                        <td style="font-size:14px;padding:4px 0;color:#111827;">{{ $order->created_at->toFormattedDateString() }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:14px;padding:4px 0;color:#111827;white-space:nowrap;font-weight:600;">Ödeme:</td>
                                        <td style="font-size:14px;padding:4px 0;color:#111827;">{{ $order->payment_method }}</td>
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
