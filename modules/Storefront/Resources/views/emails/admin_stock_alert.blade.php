<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="tr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $isOutOfStock ? 'Stok Tükendi' : 'Düşük Stok Uyarısı' }}</title>
    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap');
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: 'Outfit', Arial, sans-serif;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding: 40px 15px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0;">
                    <!-- Header Section -->
                    <tr>
                        <td align="center" valign="top" style="padding: 45px 20px; background-color: {{ $isOutOfStock ? '#ef4444' : '#f59e0b' }};">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <table border="0" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 50%; width: 70px; height: 70px;">
                                            <tr>
                                                <td align="center" valign="middle" style="font-size: 32px; font-weight: bold; color: {{ $isOutOfStock ? '#ef4444' : '#f59e0b' }}; line-height: 70px;">
                                                    {{ $isOutOfStock ? '✕' : '!' }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 25px; font-size: 28px; font-weight: 800; color: #ffffff; text-transform: uppercase; letter-spacing: -0.5px;">
                                        {{ $isOutOfStock ? 'STOK TÜKENDİ' : 'DÜŞÜK STOK' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Section -->
                    <tr>
                        <td style="padding: 40px 35px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="font-size: 18px; font-weight: 700; color: #1e293b; padding-bottom: 12px;">
                                        Merhaba Yönetici,
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size: 15px; line-height: 1.6; color: #64748b; padding-bottom: 35px;">
                                        Mağazanızdaki aşağıdaki ürünün stoğu kritik seviyeye ulaştı. Satış kaybı yaşamamak için stok durumunu kontrol etmenizi öneririz.
                                    </td>
                                </tr>

                                <!-- Product Info Card -->
                                <tr>
                                    <td style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 25px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <!-- Image Column -->
                                                <td align="left" valign="top" style="width: 120px;">
                                                    <img src="{{ $imageUrl }}" alt="{{ $product->name }}" width="120" style="display: block; border-radius: 12px; border: 1px solid #ffffff; background-color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);" />
                                                </td>
                                                <!-- Text Column -->
                                                <td valign="top" style="padding-left: 25px;">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td style="font-size: 20px; font-weight: 700; color: #0f172a; line-height: 1.25;">
                                                                {{ $product->name }}
                                                            </td>
                                                        </tr>
                                                        
                                                        @if(!empty($variationLabels))
                                                            <tr>
                                                                <td style="padding-top: 8px;">
                                                                    <table border="0" cellpadding="0" cellspacing="0">
                                                                        @foreach($variationLabels as $vName => $vValue)
                                                                            <tr>
                                                                                <td style="font-size: 13px; font-weight: 600; color: #64748b; padding-bottom: 3px; white-space: nowrap;">
                                                                                    {{ $vName }}:
                                                                                </td>
                                                                                <td style="font-size: 13px; font-weight: 700; color: #1e293b; padding-bottom: 3px; padding-left: 8px;">
                                                                                    {{ $vValue }}
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        @elseif($variantName)
                                                            <tr>
                                                                <td style="padding-top: 8px;">
                                                                    <span style="display: inline-block; background-color: #e2e8f0; color: #475569; padding: 3px 10px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                                                        {{ $variantName }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endif

                                                        <tr>
                                                            <td style="padding-top: 15px; font-size: 14px; color: #64748b;">
                                                                <span style="font-weight: 600;">Stok Kodu:</span>
                                                                <span style="color: #1e293b; padding-left: 4px;">{{ $sku ?: '-' }}</span>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td style="padding-top: 15px;">
                                                                <table border="0" cellpadding="0" cellspacing="0">
                                                                    <tr>
                                                                        <td align="center" style="background-color: {{ $isOutOfStock ? '#fee2e2' : '#fef3c7' }}; border-radius: 8px; padding: 6px 16px; font-size: 15px; font-weight: 700; color: {{ $isOutOfStock ? '#b91c1c' : '#b45309' }};">
                                                                            @php
                                                                                $formattedQty = fmod((float)$qty, 1) === 0.0 ? (int)$qty : number_format((float)$qty, 2, '.', '');
                                                                                $unitSuffix = $product->unit_suffix ?? null;
                                                                            @endphp
                                                                            {{ $formattedQty }}{{ $unitSuffix ? ' ' . $unitSuffix : '' }} Kaldı
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Action Button -->
                                <tr>
                                    <td align="center" style="padding-top: 45px;">
                                        <table border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="center" style="background-color: #0f172a; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
                                                    <a href="{{ route('admin.products.edit', $product->id) }}" target="_blank" style="display: inline-block; padding: 20px 45px; font-size: 16px; font-weight: 700; color: #ffffff; text-decoration: none;">Stoğu Güncelle</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer Section -->
                    <tr>
                        <td align="center" style="padding: 30px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 13px; color: #94a3b8; line-height: 1.6;">
                            Bu e-posta <strong>{{ setting('store_name') }}</strong> yönetim sistemi tarafından gönderilmiştir.<br />
                            <a href="{{ url('/') }}" style="color: #64748b; text-decoration: underline;">{{ url('/') }}</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
