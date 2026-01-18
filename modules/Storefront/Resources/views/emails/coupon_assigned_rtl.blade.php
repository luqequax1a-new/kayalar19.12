<!DOCTYPE html>
<html lang="tr" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.6; color: #1e293b; background: #f1f5f9; direction: rtl; }
        .main-wrapper { width: 100%; padding: 40px 15px; background-color: #f1f5f9; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        
        /* Preheader text for better inbox preview */
        .preheader { display: none; max-width: 0; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #fff; opacity: 0; }

        .header { 
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); 
            padding: 50px 30px; 
            text-align: center; 
            color: #fff; 
            position: relative;
        }
        .header-icon { font-size: 56px; margin-bottom: 15px; }
        .header h1 { margin: 10px 0 0; font-size: 32px; font-weight: 800; letter-spacing: -0.5px; }
        .header p { margin: 15px 0 0; font-size: 18px; opacity: 0.95; font-weight: 600; }

        .content { padding: 40px 35px; text-align: center; }
        .main-text { font-size: 16px; color: #475569; line-height: 1.8; margin-bottom: 30px; }
        
        /* Coupon Card */
        .coupon-card {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            margin: 30px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .coupon-header {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            padding: 30px;
            text-align: center;
            color: #fff;
            position: relative;
        }
        .coupon-discount {
            font-size: 48px;
            font-weight: 900;
            letter-spacing: -2px;
            margin-bottom: 8px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .coupon-subtitle {
            font-size: 16px;
            font-weight: 600;
            opacity: 0.95;
        }
        .coupon-body {
            padding: 30px;
            background: #f8fafc;
        }
        .coupon-code-wrapper {
            background: #fff;
            border: 2px dashed #6366f1;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        .coupon-code-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .coupon-code {
            font-size: 32px;
            font-weight: 900;
            color: #6366f1;
            letter-spacing: 3px;
            font-family: 'Courier New', monospace;
        }
        .coupon-details {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
        }
        .detail-row {
            display: table;
            width: 100%;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            display: table-cell;
            font-size: 14px;
            color: #64748b;
            font-weight: 600;
            width: 45%;
            text-align: right;
        }
        .detail-value {
            display: table-cell;
            font-size: 14px;
            color: #1e293b;
            font-weight: 700;
            text-align: left;
        }

        .cta-wrapper { text-align: center; padding: 40px 30px; background: #fcfcfc; }
        .btn { 
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); 
            color: #fff !important; 
            padding: 18px 45px; 
            border-radius: 50px; 
            text-decoration: none; 
            font-weight: 800; 
            font-size: 16px; 
            display: inline-block; 
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3); 
        }
        
        .footer { padding: 40px 30px; text-align: center; background: #f8fafc; font-size: 12px; color: #94a3b8; }
        .footer-links { margin-bottom: 20px; }
        .footer-links a { color: #6366f1; text-decoration: none; margin: 0 10px; font-weight: 600; }

        @media screen and (max-width: 480px) {
            .header h1 { font-size: 26px; }
            .content { padding: 30px 20px; }
            .coupon-discount { font-size: 36px; }
            .coupon-code { font-size: 24px; letter-spacing: 2px; }
            .detail-label, .detail-value { display: block; width: 100%; text-align: right; }
            .detail-value { margin-top: 5px; }
        }
    </style>
</head>
<body>
@php
    $discountText = $coupon->is_percent ? '%' . (int)$coupon->value : $coupon->value->convertToCurrentCurrency()->format();
@endphp
<span class="preheader">Size özel {{ $discountText }} indirim kuponu tanımlandı! 🎉</span>
<table class="main-wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <div class="container">
                <div class="header">
                    <div class="header-icon">🎁</div>
                    <h1>Size Özel İndirim Kuponu!</h1>
                    <p>Hesabınıza özel bir indirim tanımlandı</p>
                </div>

                <div class="content">
                    <div class="main-text">
                        Merhaba <strong>{{ $customer->first_name }}</strong>,<br><br>
                        Size özel olarak hazırladığımız indirim kuponunu kullanarak alışverişlerinizde tasarruf edebilirsiniz! 🎉<br>
                        Kuponunuz hemen kullanıma hazır.
                    </div>

                    <div class="coupon-card">
                        <div class="coupon-header">
                            <div class="coupon-discount">
                                @if ($coupon->is_percent)
                                    %{{ (int) $coupon->value }}
                                @else
                                    {{ $coupon->value->convertToCurrentCurrency()->format() }}
                                @endif
                            </div>
                            <div class="coupon-subtitle">İndirim Kuponu</div>
                        </div>
                        
                        <div class="coupon-body">
                            <div class="coupon-code-wrapper">
                                <div class="coupon-code-label">Kupon Kodunuz</div>
                                <div class="coupon-code">{{ $coupon->code }}</div>
                            </div>

                            <div class="coupon-details">
                                <div class="detail-row">
                                    <div class="detail-label">Kupon Adı</div>
                                    <div class="detail-value">{{ $coupon->name }}</div>
                                </div>
                                
                                @if ($coupon->end_date)
                                    <div class="detail-row">
                                        <div class="detail-label">Son Kullanım Tarihi</div>
                                        <div class="detail-value">{{ $coupon->end_date->translatedFormat('d F Y') }}</div>
                                    </div>
                                @endif

                                @if ($coupon->usage_limit_per_customer)
                                    <div class="detail-row">
                                        <div class="detail-label">Kullanım Limiti</div>
                                        <div class="detail-value">{{ $coupon->usage_limit_per_customer }} Kullanım</div>
                                    </div>
                                @endif

                                @if ($coupon->minimum_spend)
                                    <div class="detail-row">
                                        <div class="detail-label">Minimum Sepet Tutarı</div>
                                        <div class="detail-value">{{ $coupon->minimum_spend->convertToCurrentCurrency()->format() }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cta-wrapper">
                    <a href="{{ route('account.coupons.index') }}" class="btn">Kuponlarımı Görüntüle 🎉</a>
                    <p style="margin-top: 25px; color: #64748b; font-size: 13px;">
                        Kuponunuzu sepet sayfasında kullanabilir, anında indirimden faydalanabilirsiniz!
                    </p>
                </div>

                <div class="footer">
                    <div style="font-weight: 800; font-size: 16px; color: #64748b; margin-bottom: 5px;">{{ setting('store_name') }}</div>
                    <div style="margin-bottom: 20px;">{{ setting('store_address') }}</div>
                    <div class="footer-links">
                        <a href="{{ url('/') }}">Mağazayı Ziyaret Et</a>
                        <a href="{{ route('account.coupons.index') }}">Kuponlarım</a>
                        <a href="{{ route('account.dashboard.index') }}">Hesabım</a>
                    </div>
                    <p>&copy; {{ date('Y') }} {{ setting('store_name') }}. Tüm hakları saklıdır.</p>
                </div>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
