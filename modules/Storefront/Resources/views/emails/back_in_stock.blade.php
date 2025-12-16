<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ürün tekrar stokta</title>
    <style>
        /* Some email clients strip head styles, but most modern clients keep them.
           Layout is still readable without these rules. */
        @media screen and (max-width: 620px) {
            .container { width: 100% !important; }
            .outer-padding { padding: 16px 10px !important; }
            .card { border-radius: 12px !important; }
            .card-pad { padding: 12px !important; }
            .stack { display: block !important; width: 100% !important; }
            .stack + .stack { padding-top: 10px !important; }
            .img-cell { padding-right: 0 !important; text-align: left !important; }
        }
    </style>
</head>
@php
    $storeName = (string) setting('store_name');

    $basePath = '';
    try {
        $basePath = (string) optional($product->base_image)->path;
    } catch (\Throwable $e) {
        $basePath = '';
    }

    if ($basePath !== '') {
        if (preg_match('#^https?://#i', $basePath)) {
            $imageUrl = $basePath;
        } elseif (str_starts_with($basePath, '/')) {
            $imageUrl = url($basePath);
        } elseif (str_starts_with($basePath, 'storage/')) {
            $imageUrl = url('/' . $basePath);
        } else {
            $imageUrl = url('/storage/' . $basePath);
        }
    } else {
        $imageUrl = url('/build/assets/image-placeholder.png');
    }

    $productUrl = url($product->url());
@endphp

<body style="margin:0;padding:0;background:#f6f7f9;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f6f7f9;">
    <tr>
        <td align="center" class="outer-padding" style="padding:28px 12px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" class="container" style="width:100%;max-width:600px;">
                <tr>
                    <td style="padding:0 6px 12px 6px; font-family:Arial,Helvetica,sans-serif;">
                        <div style="font-size:12px; color:#6b7280; line-height:1.6;">
                            {{ $storeName !== '' ? $storeName : 'Mağazamız' }}
                        </div>
                        <div style="font-size:18px; font-weight:700; color:#111827; line-height:1.3;">
                            Stoğa geri geldi
                        </div>
                        <div style="font-size:14px; color:#374151; line-height:1.6; margin-top:6px;">
                            Merhaba, beklediğiniz ürün tekrar stokta. Aşağıdan ürüne hızlıca ulaşabilirsiniz.
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 6px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" class="card" style="background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
                            <tr>
                                <td class="card-pad" style="padding:14px 14px 14px 14px; font-family:Arial,Helvetica,sans-serif;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                        <tr>
                                            <td width="90" valign="top" class="img-cell stack" style="width:90px; padding-right:12px;">
                                                <a href="{{ $productUrl }}" style="text-decoration:none;">
                                                    <img src="{{ $imageUrl }}" alt="{{ $product->name }}" width="90" height="90" style="display:block;width:90px;height:90px;border-radius:10px;border:0;object-fit:cover;" />
                                                </a>
                                            </td>
                                            <td valign="top" class="stack" style="padding-top:2px;">
                                                <div style="font-size:14px;font-weight:700;color:#111827;line-height:1.4;">
                                                    <a href="{{ $productUrl }}" style="color:#111827;text-decoration:none;">{{ $product->name }}</a>
                                                </div>
                                                <div style="margin-top:8px;font-size:13px;line-height:1.6;">
                                                    <a href="{{ $productUrl }}" style="color:#0d6efd;text-decoration:underline;">Ürünü görüntüle</a>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:14px 6px 0 6px; font-family:Arial,Helvetica,sans-serif;">
                        <div style="font-size:12px;color:#6b7280;line-height:1.6;">
                            Bu e-postayı, ilgili ürün için “Stoğa geldiğinde haber ver” talebi oluşturduğunuz için aldınız.
                        </div>
                        <div style="font-size:12px;color:#9ca3af;line-height:1.6;margin-top:6px;">
                            © {{ date('Y') }} {{ $storeName !== '' ? $storeName : 'Mağazamız' }}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
